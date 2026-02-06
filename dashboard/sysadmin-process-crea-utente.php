<?php
/**
 * UNIME-ACQUE - Process Creazione Utente (SYSADMIN)
 * 
 * Elabora il form di creazione utente AMMINISTRATORE o TECNICO.
 * Genera password temporanea e inserisce l'utente nel database.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

startSecureSession();
requireRole('SYSADMIN');

// Verifica che sia POST
if (!isPostRequest()) {
    header('Location: sysadmin-crea-utente.php');
    exit;
}

// Verifica CSRF
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    setFlashMessage('danger', 'Richiesta non valida. Riprova.');
    header('Location: sysadmin-crea-utente.php');
    exit;
}

// Recupera dati form
$ruolo = postParam('ruolo', '');
$nome = trim(postParam('nome', ''));
$cognome = trim(postParam('cognome', ''));
$codiceFiscale = strtoupper(trim(postParam('codice_fiscale', '')));
$dataNascita = postParam('data_nascita', '');
$email = trim(postParam('email', ''));
$telefono = trim(postParam('telefono', ''));

// Array errori
$errors = [];

// Validazione ruolo
if (empty($ruolo)) {
    $errors[] = 'Il ruolo è obbligatorio.';
} elseif (!in_array($ruolo, ['AMMINISTRATORE', 'TECNICO'])) {
    $errors[] = 'Ruolo non valido. Solo AMMINISTRATORE o TECNICO sono permessi.';
}

// Validazione nome
if (empty($nome)) {
    $errors[] = 'Il nome è obbligatorio.';
} elseif (strlen($nome) < 2 || strlen($nome) > 50) {
    $errors[] = 'Il nome deve essere tra 2 e 50 caratteri.';
}

// Validazione cognome
if (empty($cognome)) {
    $errors[] = 'Il cognome è obbligatorio.';
} elseif (strlen($cognome) < 2 || strlen($cognome) > 50) {
    $errors[] = 'Il cognome deve essere tra 2 e 50 caratteri.';
}

// Validazione codice fiscale
if (empty($codiceFiscale)) {
    $errors[] = 'Il codice fiscale è obbligatorio.';
} elseif (!validateCodiceFiscale($codiceFiscale)) {
    $errors[] = 'Il codice fiscale deve essere di 16 caratteri alfanumerici.';
}

// Validazione data di nascita
if (empty($dataNascita)) {
    $errors[] = 'La data di nascita è obbligatoria.';
} elseif (!validateDate($dataNascita, 'ymd')) {
    $errors[] = 'Data di nascita non valida.';
} else {
    // Verifica maggiore età (18 anni)
    $birthDate = new DateTime($dataNascita);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    
    if ($age < 18) {
        $errors[] = 'L\'utente deve essere maggiorenne (almeno 18 anni).';
    }
}

// Validazione email
if (empty($email)) {
    $errors[] = 'L\'email è obbligatoria.';
} elseif (!validateEmail($email)) {
    $errors[] = 'Inserisci un indirizzo email valido.';
} else {
    $email = sanitizeEmail($email);
}

// Validazione telefono
if (empty($telefono)) {
    $errors[] = 'Il telefono è obbligatorio.';
} elseif (!preg_match('/^[0-9]{10}$/', $telefono)) {
    $errors[] = 'Il telefono deve essere di 10 cifre.';
}

// Se ci sono errori, torna indietro
if (!empty($errors)) {
    setFlashMessage('danger', implode(' ', $errors));
    header('Location: sysadmin-crea-utente.php');
    exit;
}

// Connessione database
$conn = getDbConnection();

if ($conn === null) {
    setFlashMessage('danger', 'Errore di connessione al database. Riprova più tardi.');
    logError('Errore connessione DB durante creazione utente');
    header('Location: sysadmin-crea-utente.php');
    exit;
}

// Verifica che email non esista già
$queryCheckEmail = "SELECT IdUtente FROM UTENTE WHERE email = ? LIMIT 1";
$stmtCheck = mysqli_prepare($conn, $queryCheckEmail);
mysqli_stmt_bind_param($stmtCheck, 's', $email);
mysqli_stmt_execute($stmtCheck);
$resultCheck = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($resultCheck) > 0) {
    mysqli_stmt_close($stmtCheck);
    setFlashMessage('danger', 'Esiste già un utente con questa email.');
    header('Location: sysadmin-crea-utente.php');
    exit;
}
mysqli_stmt_close($stmtCheck);

// Verifica che codice fiscale non esista già
$queryCheckCF = "SELECT IdUtente FROM UTENTE WHERE codice_fiscale = ? LIMIT 1";
$stmtCheckCF = mysqli_prepare($conn, $queryCheckCF);
mysqli_stmt_bind_param($stmtCheckCF, 's', $codiceFiscale);
mysqli_stmt_execute($stmtCheckCF);
$resultCheckCF = mysqli_stmt_get_result($stmtCheckCF);

if (mysqli_num_rows($resultCheckCF) > 0) {
    mysqli_stmt_close($stmtCheckCF);
    setFlashMessage('danger', 'Esiste già un utente con questo codice fiscale.');
    header('Location: sysadmin-crea-utente.php');
    exit;
}
mysqli_stmt_close($stmtCheckCF);

// Genera password temporanea
$passwordTemp = generateRandomPassword(12);
$passwordHash = hashPassword($passwordTemp);

// Inserisci utente nel database
$queryInsert = "INSERT INTO UTENTE (nome, cognome, codice_fiscale, email, telefono, data_nascita, ruolo, password_hash) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmtInsert = mysqli_prepare($conn, $queryInsert);

if ($stmtInsert === false) {
    setFlashMessage('danger', 'Errore interno del server. Riprova più tardi.');
    logError('Errore preparazione query insert utente: ' . mysqli_error($conn));
    header('Location: sysadmin-crea-utente.php');
    exit;
}

// Bind parametri
mysqli_stmt_bind_param(
    $stmtInsert,
    'ssssssss',
    $nome,
    $cognome,
    $codiceFiscale,
    $email,
    $telefono,
    $dataNascita,
    $ruolo,
    $passwordHash
);

// Esegui insert
$insertSuccess = mysqli_stmt_execute($stmtInsert);

if (!$insertSuccess) {
    mysqli_stmt_close($stmtInsert);
    setFlashMessage('danger', 'Errore durante la creazione dell\'utente. Riprova.');
    logError('Errore insert utente: ' . mysqli_error($conn));
    header('Location: sysadmin-crea-utente.php');
    exit;
}

// Recupera ID utente appena creato
$newUserId = mysqli_insert_id($conn);
mysqli_stmt_close($stmtInsert);

// Log operazione
$sysadminId = getCurrentUserId();
$sysadminName = getCurrentUserName();
logError("SYSADMIN {$sysadminName} (ID: {$sysadminId}) ha creato utente {$ruolo}: {$nome} {$cognome} (ID: {$newUserId}, Email: {$email})", 'INFO');

// Salva password temporanea in sessione per mostrarla
$_SESSION['new_user_data'] = [
    'id' => $newUserId,
    'nome' => $nome,
    'cognome' => $cognome,
    'email' => $email,
    'ruolo' => $ruolo,
    'password_temp' => $passwordTemp
];

// Reindirizza a pagina di successo
header('Location: sysadmin-utente-creato.php');
exit;
?>
