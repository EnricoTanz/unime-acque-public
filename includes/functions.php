<?php
/**
 * UNIME-ACQUE - Funzioni di Utilità
 * 
 * Contiene funzioni helper riutilizzabili per validazione input,
 * sanitizzazione dati e operazioni comuni.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

// Impedisce l'accesso diretto al file
if (!defined('UNIME_ACQUE')) {
    die('Accesso non consentito.');
}

/* ============================================
   FUNZIONI DI VALIDAZIONE
   ============================================ */

/**
 * Valida un indirizzo email.
 * 
 * @param string $email Email da validare
 * @return bool True se l'email è valida
 */
function validateEmail($email) {
    $email = trim($email);
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida un numero intero con range opzionale.
 * 
 * @param mixed $value Valore da validare
 * @param int|null $min Valore minimo (opzionale)
 * @param int|null $max Valore massimo (opzionale)
 * @return bool True se il valore è un intero valido nel range
 */
function validateInt($value, $min = null, $max = null) {
    $options = [];
    
    if ($min !== null) {
        $options['min_range'] = $min;
    }
    if ($max !== null) {
        $options['max_range'] = $max;
    }
    
    if (empty($options)) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    return filter_var($value, FILTER_VALIDATE_INT, ['options' => $options]) !== false;
}

/**
 * Valida un numero decimale.
 * 
 * @param mixed $value Valore da validare
 * @return bool True se il valore è un float valido
 */
function validateFloat($value) {
    return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
}

/**
 * Valida una data nel formato GG/MM/AAAA o AAAA-MM-GG.
 * 
 * @param string $date Data da validare
 * @param string $format Formato atteso ('dmy' o 'ymd')
 * @return bool True se la data è valida
 */
function validateDate($date, $format = 'ymd') {
    $date = trim($date);
    
    if ($format === 'dmy') {
        // Formato italiano GG/MM/AAAA
        if (!preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $matches)) {
            return false;
        }
        $day = (int)$matches[1];
        $month = (int)$matches[2];
        $year = (int)$matches[3];
    } else {
        // Formato ISO AAAA-MM-GG
        if (!preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $date, $matches)) {
            return false;
        }
        $year = (int)$matches[1];
        $month = (int)$matches[2];
        $day = (int)$matches[3];
    }
    
    return checkdate($month, $day, $year);
}

/**
 * Valida un codice fiscale italiano o partita IVA.
 * 
 * @param string $cf Codice fiscale (16 caratteri) o Partita IVA (11 cifre) da validare
 * @return bool True se il codice fiscale o partita IVA è formalmente valido
 */
function validateCodiceFiscale($cf) {
    $cf = strtoupper(trim($cf));
    
    // Controllo lunghezza
    $len = strlen($cf);
    
    if ($len === 16) {
        // Codice fiscale persona fisica
        // Pattern: 6 lettere + 2 numeri + 1 lettera + 2 numeri + 1 lettera + 3 numeri + 1 lettera
        $pattern = '/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/';
        return preg_match($pattern, $cf) === 1;
        
    } elseif ($len === 11) {
        // Partita IVA (persona giuridica/azienda)
        // Deve essere composta da 11 cifre numeriche
        $pattern = '/^[0-9]{11}$/';
        return preg_match($pattern, $cf) === 1;
        
    } else {
        return false;
    }
}

/**
 * Valida un numero di telefono italiano.
 * 
 * @param string $phone Numero di telefono da validare
 * @return bool True se il numero è valido
 */
function validatePhone($phone) {
    // Rimuove spazi, trattini e punti
    $phone = preg_replace('/[\s\-\.]/', '', $phone);
    
    // Accetta formati: +39..., 0039..., 3..., 0...
    $pattern = '/^(\+39|0039)?[0-9]{9,11}$/';
    
    return preg_match($pattern, $phone) === 1;
}

/**
 * Valida un CAP italiano (5 cifre).
 * 
 * @param string $cap CAP da validare
 * @return bool True se il CAP è valido
 */
function validateCAP($cap) {
    $cap = trim($cap);
    return preg_match('/^[0-9]{5}$/', $cap) === 1;
}

/**
 * Valida un numero di carta di credito (basic check).
 * 
 * @param string $cardNumber Numero carta da validare
 * @return bool True se il numero ha un formato valido
 */
function validateCreditCard($cardNumber) {
    // Rimuove spazi e trattini
    $cardNumber = preg_replace('/[\s\-]/', '', $cardNumber);
    
    // Deve essere di 16 cifre
    if (!preg_match('/^[0-9]{16}$/', $cardNumber)) {
        return false;
    }
    
    // Algoritmo di Luhn (checksum)
    $sum = 0;
    $length = strlen($cardNumber);
    
    for ($i = 0; $i < $length; $i++) {
        $digit = (int)$cardNumber[$length - 1 - $i];
        
        if ($i % 2 === 1) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        
        $sum += $digit;
    }
    
    return $sum % 10 === 0;
}

/**
 * Valida un CVV (3 cifre).
 * 
 * @param string $cvv CVV da validare
 * @return bool True se il CVV è valido
 */
function validateCVV($cvv) {
    $cvv = trim($cvv);
    return preg_match('/^[0-9]{3}$/', $cvv) === 1;
}

/**
 * Valida la robustezza di una password.
 * 
 * @param string $password Password da validare
 * @param int $minLength Lunghezza minima (default 8)
 * @return array Array con 'valid' e 'errors'
 */
function validatePassword($password, $minLength = 8) {
    $errors = [];
    
    if (strlen($password) < $minLength) {
        $errors[] = "La password deve essere di almeno {$minLength} caratteri.";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "La password deve contenere almeno una lettera maiuscola.";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "La password deve contenere almeno una lettera minuscola.";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "La password deve contenere almeno un numero.";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/* ============================================
   FUNZIONI DI SANITIZZAZIONE
   ============================================ */

/**
 * Sanitizza una stringa rimuovendo tag HTML e spazi.
 * 
 * @param string $string Stringa da sanitizzare
 * @return string Stringa pulita
 */
function sanitizeString($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitizza un'email.
 * 
 * @param string $email Email da sanitizzare
 * @return string Email pulita
 */
function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Sanitizza un intero.
 * 
 * @param mixed $value Valore da sanitizzare
 * @return int Intero sanitizzato
 */
function sanitizeInt($value) {
    return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * Sanitizza un float.
 * 
 * @param mixed $value Valore da sanitizzare
 * @return float Float sanitizzato
 */
function sanitizeFloat($value) {
    return (float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

/* ============================================
   FUNZIONI DI UTILITÀ
   ============================================ */

/**
 * Formatta una data da formato ISO a formato italiano.
 * 
 * @param string $date Data in formato YYYY-MM-DD
 * @return string Data in formato DD/MM/YYYY
 */
function formatDateItalian($date) {
    if (empty($date)) {
        return '';
    }
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

/**
 * Formatta una data da formato italiano a formato ISO.
 * 
 * @param string $date Data in formato DD/MM/YYYY
 * @return string Data in formato YYYY-MM-DD
 */
function formatDateISO($date) {
    if (empty($date)) {
        return '';
    }
    $parts = explode('/', $date);
    if (count($parts) !== 3) {
        return '';
    }
    return sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);
}

/**
 * Formatta un importo in formato valuta italiana.
 * 
 * @param float $amount Importo
 * @param bool $showSymbol Mostra simbolo €
 * @return string Importo formattato
 */
function formatCurrency($amount, $showSymbol = true) {
    $formatted = number_format($amount, 2, ',', '.');
    return $showSymbol ? '€ ' . $formatted : $formatted;
}

/**
 * Genera una password casuale sicura.
 * 
 * @param int $length Lunghezza della password
 * @return string Password generata
 */
function generateRandomPassword($length = 12) {
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $special = '!@#$%^&*';
    
    $all = $uppercase . $lowercase . $numbers . $special;
    
    // Garantisce almeno un carattere per tipo
    $password = $uppercase[random_int(0, strlen($uppercase) - 1)];
    $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
    $password .= $numbers[random_int(0, strlen($numbers) - 1)];
    $password .= $special[random_int(0, strlen($special) - 1)];
    
    // Completa con caratteri casuali
    for ($i = 4; $i < $length; $i++) {
        $password .= $all[random_int(0, strlen($all) - 1)];
    }
    
    // Mescola i caratteri
    return str_shuffle($password);
}
/**
 * Ottiene l'email dell'utente corrente.
 * 
 * @return string|null Email utente o null se non loggato
 */
function getCurrentUserEmail() {
    return $_SESSION['user_email'] ?? null;
}
/**
 * Genera un hash sicuro per la password.
 * 
 * @param string $password Password in chiaro
 * @return string Hash della password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verifica una password contro il suo hash.
 * 
 * @param string $password Password in chiaro
 * @param string $hash Hash memorizzato
 * @return bool True se la password corrisponde
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Reindirizza a un URL con exit.
 * 
 * @param string $url URL di destinazione
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Verifica se la richiesta è POST.
 * 
 * @return bool True se è una richiesta POST
 */
function isPostRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Verifica se la richiesta è GET.
 * 
 * @return bool True se è una richiesta GET
 */
function isGetRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Ottiene un valore da $_GET con default.
 * 
 * @param string $key Chiave del parametro
 * @param mixed $default Valore di default
 * @return mixed Valore del parametro o default
 */
function getParam($key, $default = null) {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

/**
 * Ottiene un valore da $_POST con default.
 * 
 * @param string $key Chiave del parametro
 * @param mixed $default Valore di default
 * @return mixed Valore del parametro o default
 */
function postParam($key, $default = null) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

/**
 * Logga un errore nel file di log.
 * 
 * @param string $message Messaggio da loggare
 * @param string $level Livello di log (INFO, WARNING, ERROR)
 */
function logError($message, $level = 'ERROR') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    error_log($logMessage, 3, __DIR__ . '/../logs/app.log');
}
?>
