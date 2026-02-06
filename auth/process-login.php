<?php
/**
 * UNIME-ACQUE - Process Login
 * 
 * Elabora le credenziali di login e autentica l'utente.
 * Verifica email e password, quindi reindirizza alla dashboard appropriata.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

// Include dipendenze
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Avvia la sessione
startSecureSession();

// Verifica che sia una richiesta POST
if (!isPostRequest()) {
    header('Location: login.php');
    exit;
}

// Verifica token CSRF
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    setFlashMessage('danger', 'Richiesta non valida. Riprova.');
    header('Location: login.php');
    exit;
}

// Recupera e valida i dati dal form
$email = postParam('email', '');
$password = postParam('password', '');
$rememberMe = isset($_POST['remember_me']);

// Validazione input
$errors = [];

// Validazione email
if (empty($email)) {
    $errors[] = 'L\'email è obbligatoria.';
} elseif (!validateEmail($email)) {
    $errors[] = 'Inserisci un indirizzo email valido.';
} else {
    $email = sanitizeEmail($email);
}

// Validazione password
if (empty($password)) {
    $errors[] = 'La password è obbligatoria.';
}

// Se ci sono errori di validazione, torna al login
if (!empty($errors)) {
    setFlashMessage('danger', implode(' ', $errors));
    header('Location: login.php');
    exit;
}

// Connessione al database
$conn = getDbConnection();

if ($conn === null) {
    setFlashMessage('danger', 'Errore di connessione al database. Riprova più tardi.');
    logError('Errore connessione DB durante login');
    header('Location: login.php');
    exit;
}

// Query per recuperare l'utente
$query = "SELECT IdUtente, nome, cognome, email, password_hash, ruolo 
          FROM UTENTE 
          WHERE email = ? 
          LIMIT 1";

$stmt = mysqli_prepare($conn, $query);

if ($stmt === false) {
    setFlashMessage('danger', 'Errore interno del server. Riprova più tardi.');
    logError('Errore preparazione query login: ' . mysqli_error($conn));
    header('Location: login.php');
    exit;
}

// Bind parametri ed esegui
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Verifica se l'utente esiste
if ($result === false || mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    
    // Log tentativo di accesso fallito
    logError("Tentativo di login fallito per email: {$email} - Utente non trovato");
    
    // Messaggio generico per sicurezza (non rivelare se l'email esiste)
    setFlashMessage('danger', 'Email o password non corretti.');
    header('Location: login.php');
    exit;
}

// Recupera dati utente
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Verifica password
if (!verifyPassword($password, $user['password_hash'])) {
    // Log tentativo di accesso fallito
    logError("Tentativo di login fallito per email: {$email} - Password errata");
    
    setFlashMessage('danger', 'Email o password non corretti.');
    header('Location: login.php');
    exit;
}

// Verifica che il ruolo sia valido
$validRoles = ['AMMINISTRATORE', 'TECNICO', 'CLIENTE', 'SYSADMIN'];
if (!in_array($user['ruolo'], $validRoles)) {
    logError("Ruolo non valido per utente ID {$user['IdUtente']}: {$user['ruolo']}");
    setFlashMessage('danger', 'Errore di autorizzazione. Contatta l\'assistenza.');
    header('Location: login.php');
    exit;
}

// Login riuscito - Salva dati in sessione
loginUser(
    $user['IdUtente'],
    $user['email'],
    $user['nome'],
    $user['cognome'],
    $user['ruolo']
);

// Se "Ricordami" è selezionato, estendi la durata del cookie di sessione
if ($rememberMe) {
    // Imposta cookie di sessione per 30 giorni
    $cookieParams = session_get_cookie_params();
    setcookie(
        session_name(),
        session_id(),
        time() + (30 * 24 * 60 * 60), // 30 giorni
        $cookieParams['path'],
        $cookieParams['domain'],
        $cookieParams['secure'],
        $cookieParams['httponly']
    );
}

// Log accesso riuscito
logError("Login riuscito per utente ID {$user['IdUtente']} ({$user['email']}) - Ruolo: {$user['ruolo']}", 'INFO');

// Reindirizza alla dashboard appropriata in base al ruolo
switch ($user['ruolo']) {
    case 'AMMINISTRATORE':
        $redirectUrl = '../dashboard/amministratore.php';
        break;
    
    case 'TECNICO':
        $redirectUrl = '../dashboard/tecnico.php';
        break;
    
    case 'CLIENTE':
        $redirectUrl = '../dashboard/cliente.php';
        break;
    
    case 'SYSADMIN':
        $redirectUrl = '../dashboard/sysadmin.php';
        break;
    
    default:
        // Fallback (non dovrebbe mai succedere)
        logError("Errore reindirizzamento: ruolo sconosciuto {$user['ruolo']}");
        logoutUser();
        setFlashMessage('danger', 'Errore di autenticazione. Riprova.');
        header('Location: login.php');
        exit;
}

// Se c'era un URL di redirect salvato, usalo
if (isset($_SESSION['redirect_after_login'])) {
    $redirectUrl = $_SESSION['redirect_after_login'];
    unset($_SESSION['redirect_after_login']);
}

// Reindirizza
header('Location: ' . $redirectUrl);
exit;
?>
