<?php
/**
 * UNIME-ACQUE - Gestione Sessioni
 * 
 * File per la gestione sicura delle sessioni utente.
 * Include funzioni per autenticazione, controllo ruoli e protezione CSRF.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

// Impedisce l'accesso diretto al file
if (!defined('UNIME_ACQUE')) {
    die('Accesso non consentito.');
}

// Configurazione sessione sicura
ini_set('session.cookie_httponly', 1);    // Cookie non accessibile via JavaScript
ini_set('session.use_only_cookies', 1);   // Usa solo cookie per la sessione
ini_set('session.cookie_samesite', 'Strict'); // Protezione CSRF

// Timeout sessione (30 minuti di inattività)
define('SESSION_TIMEOUT', 1800);

/**
 * Avvia la sessione in modo sicuro.
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('UNIME_ACQUE_SESSION');
        session_start();
    }
    
    // Controlla timeout sessione
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            // Sessione scaduta, effettua logout
            destroySession();
            return false;
        }
    }
    
    // Aggiorna timestamp ultima attività
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Effettua il login dell'utente.
 * 
 * @param int $userId ID dell'utente
 * @param string $email Email dell'utente
 * @param string $nome Nome dell'utente
 * @param string $cognome Cognome dell'utente
 * @param string $ruolo Ruolo dell'utente (cittadino, admin, tecnico, sysadmin)
 * @return bool True se login riuscito
 */
function loginUser($userId, $email, $nome, $cognome, $ruolo) {
    // Rigenera ID sessione per prevenire session fixation
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_nome'] = $nome;
    $_SESSION['user_cognome'] = $cognome;
    $_SESSION['user_ruolo'] = $ruolo;
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    
    // Genera token CSRF
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    return true;
}

/**
 * Effettua il logout dell'utente.
 */
function logoutUser() {
    destroySession();
}

/**
 * Distrugge completamente la sessione.
 */
function destroySession() {
    // Svuota l'array sessione
    $_SESSION = array();
    
    // Elimina il cookie di sessione
    if (isset($_COOKIE[session_name()])) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    // Distrugge la sessione
    session_destroy();
}

/**
 * Verifica se l'utente è autenticato.
 * 
 * @return bool True se l'utente è loggato
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Ottiene l'ID dell'utente corrente.
 * 
 * @return int|null ID utente o null se non loggato
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Ottiene il ruolo dell'utente corrente.
 * 
 * @return string|null Ruolo utente o null se non loggato
 */
function getCurrentUserRole() {
    return $_SESSION['user_ruolo'] ?? null;
}

/**
 * Ottiene il nome completo dell'utente corrente.
 * 
 * @return string Nome completo o stringa vuota
 */
function getCurrentUserName() {
    if (isLoggedIn()) {
        return trim($_SESSION['user_nome'] . ' ' . $_SESSION['user_cognome']);
    }
    return '';
}

/**
 * Verifica se l'utente ha un determinato ruolo.
 * 
 * @param string|array $roles Ruolo o array di ruoli da verificare
 * @return bool True se l'utente ha il ruolo specificato
 */
function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = getCurrentUserRole();
    
    if (is_array($roles)) {
        return in_array($userRole, $roles);
    }
    
    return $userRole === $roles;
}

/**
 * Richiede che l'utente sia autenticato.
 * Reindirizza alla pagina di login se non loggato.
 * 
 * @param string $redirectUrl URL di redirect dopo il login
 */
function requireLogin($redirectUrl = null) {
    if (!isLoggedIn()) {
        $redirect = $redirectUrl ?? $_SERVER['REQUEST_URI'];
        $_SESSION['redirect_after_login'] = $redirect;
        header('Location: /auth/login.php');
        exit;
    }
}

/**
 * Richiede che l'utente abbia un determinato ruolo.
 * Reindirizza se non autorizzato.
 * 
 * @param string|array $roles Ruolo o array di ruoli richiesti
 */
function requireRole($roles) {
    requireLogin();
    
    if (!hasRole($roles)) {
        // Utente loggato ma non autorizzato
        header('HTTP/1.0 403 Forbidden');
        header('Location: /error/403.php');
        exit;
    }
}

/**
 * Genera un nuovo token CSRF.
 * 
 * @return string Token CSRF
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica il token CSRF.
 * 
 * @param string $token Token da verificare
 * @return bool True se il token è valido
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Restituisce il campo hidden per il token CSRF da inserire nei form.
 * 
 * @return string HTML del campo hidden
 */
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Imposta un messaggio flash da mostrare nella prossima pagina.
 * 
 * @param string $type Tipo di messaggio (success, danger, warning, info)
 * @param string $message Contenuto del messaggio
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Recupera e cancella il messaggio flash.
 * 
 * @return array|null Messaggio flash o null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Mostra il messaggio flash come HTML.
 * 
 * @return string HTML del messaggio o stringa vuota
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $type = htmlspecialchars($flash['type']);
        $message = htmlspecialchars($flash['message']);
        return "<div class=\"alert alert-{$type}\">{$message}</div>";
    }
    return '';
}
?>
