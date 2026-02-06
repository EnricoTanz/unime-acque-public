<?php
/**
 * UNIME-ACQUE - Process Forgot Password
 * 
 * Elabora la richiesta di reset password.
 * Verifica email e codice fiscale, quindi aggiorna la password.
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
    header('Location: forgot-password.php');
    exit;
}

// Verifica token CSRF
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    setFlashMessage('danger', 'Richiesta non valida. Riprova.');
    header('Location: forgot-password.php');
    exit;
}

// Recupera e valida i dati dal form
$email = postParam('email', '');
$codiceFiscale = strtoupper(trim(postParam('codice_fiscale', '')));
$newPassword = postParam('new_password', '');
$confirmPassword = postParam('confirm_password', '');

// Array per raccogliere errori di validazione
$errors = [];

// Validazione email
if (empty($email)) {
    $errors[] = 'L\'email è obbligatoria.';
} elseif (!validateEmail($email)) {
    $errors[] = 'Inserisci un indirizzo email valido.';
} else {
    $email = sanitizeEmail($email);
}

// Validazione codice fiscale
if (empty($codiceFiscale)) {
    $errors[] = 'Il codice fiscale / partita IVA è obbligatorio.';
} elseif (!validateCodiceFiscale($codiceFiscale)) {
    $errors[] = 'Il codice fiscale deve essere di 16 caratteri o la partita IVA di 11 cifre.';
}

// Validazione password
if (empty($newPassword)) {
    $errors[] = 'La nuova password è obbligatoria.';
} else {
    $passwordValidation = validatePassword($newPassword);
    if (!$passwordValidation['valid']) {
        $errors = array_merge($errors, $passwordValidation['errors']);
    }
}

// Validazione conferma password
if (empty($confirmPassword)) {
    $errors[] = 'La conferma password è obbligatoria.';
} elseif ($newPassword !== $confirmPassword) {
    $errors[] = 'Le password non corrispondono.';
}

// Se ci sono errori di validazione, torna alla pagina
if (!empty($errors)) {
    setFlashMessage('danger', implode(' ', $errors));
    header('Location: forgot-password.php');
    exit;
}

// Connessione al database
$conn = getDbConnection();

if ($conn === null) {
    setFlashMessage('danger', 'Errore di connessione al database. Riprova più tardi.');
    logError('Errore connessione DB durante reset password');
    header('Location: forgot-password.php');
    exit;
}

// Query per verificare che email e codice fiscale corrispondano
$query = "SELECT IdUtente, nome, cognome, email 
          FROM UTENTE 
          WHERE email = ? AND codice_fiscale = ? 
          LIMIT 1";

$stmt = mysqli_prepare($conn, $query);

if ($stmt === false) {
    setFlashMessage('danger', 'Errore interno del server. Riprova più tardi.');
    logError('Errore preparazione query reset password: ' . mysqli_error($conn));
    header('Location: forgot-password.php');
    exit;
}

// Bind parametri ed esegui
mysqli_stmt_bind_param($stmt, 'ss', $email, $codiceFiscale);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Verifica se l'utente esiste con quella combinazione
if ($result === false || mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    
    // Log tentativo fallito
    logError("Tentativo di reset password fallito - Email: {$email}, CF non corrispondente");
    
    // Messaggio generico per sicurezza
    setFlashMessage('danger', 'Email e codice fiscale non corrispondono. Verifica i dati inseriti.');
    header('Location: forgot-password.php');
    exit;
}

// Recupera dati utente
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Genera hash della nuova password
$newPasswordHash = hashPassword($newPassword);

// Query per aggiornare la password
$updateQuery = "UPDATE UTENTE 
                SET password_hash = ? 
                WHERE IdUtente = ?";

$updateStmt = mysqli_prepare($conn, $updateQuery);

if ($updateStmt === false) {
    setFlashMessage('danger', 'Errore interno del server. Riprova più tardi.');
    logError('Errore preparazione query update password: ' . mysqli_error($conn));
    header('Location: forgot-password.php');
    exit;
}

// Bind parametri ed esegui update
mysqli_stmt_bind_param($updateStmt, 'si', $newPasswordHash, $user['IdUtente']);
$updateSuccess = mysqli_stmt_execute($updateStmt);
mysqli_stmt_close($updateStmt);

if (!$updateSuccess) {
    setFlashMessage('danger', 'Errore durante l\'aggiornamento della password. Riprova.');
    logError("Errore update password per utente ID {$user['IdUtente']}: " . mysqli_error($conn));
    header('Location: forgot-password.php');
    exit;
}

// Log successo
logError("Password reimpostata con successo per utente ID {$user['IdUtente']} ({$user['email']})", 'INFO');

// Messaggio di successo
setFlashMessage('success', 'Password reimpostata con successo! Ora puoi effettuare il login con la nuova password.');

// Reindirizza al login
header('Location: login.php');
exit;
?>
