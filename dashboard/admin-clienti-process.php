<?php
/**
 * UNIME-ACQUE - Process Registrazione Cliente
 * 
 * Elabora il form di registrazione cliente.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

startSecureSession();
requireRole('AMMINISTRATORE');

if (!isPostRequest()) {
    header('Location: admin-clienti-nuovo.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Richiesta non valida.');
    header('Location: admin-clienti-nuovo.php');
    exit;
}

// Recupera dati form
$nome = trim($_POST['nome'] ?? '');
$cognome = trim($_POST['cognome'] ?? '');
$codiceFiscale = strtoupper(trim($_POST['codice_fiscale'] ?? ''));
$dataNascita = $_POST['data_nascita'] ?? '';
$ragioneSociale = trim($_POST['ragione_sociale'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');

// Array errori
$errors = [];

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
    $errors[] = 'Il codice fiscale deve essere di 16 caratteri alfanumerici o 11 cifre numeriche.';
}

// Validazione data di nascita
if (empty($dataNascita)) {
    $errors[] = 'La data di nascita è obbligatoria.';
} elseif (!validateDate($dataNascita, 'ymd')) {
    $errors[] = 'Data di nascita non valida.';
} else {
    $birthDate = new DateTime($dataNascita);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    
    if ($age < 18) {
        $errors[] = 'Il cliente deve essere maggiorenne (almeno 18 anni).';
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

// Validazione ragione sociale (opzionale)
if (!empty($ragioneSociale) && strlen($ragioneSociale) > 100) {
    $errors[] = 'La ragione sociale non può superare 100 caratteri.';
}

// Se ci sono errori, torna indietro
if (!empty($errors)) {
    setFlashMessage('danger', implode(' ', $errors));
    header('Location: admin-clienti-nuovo.php');
    exit;
}

$conn = getDbConnection();

if ($conn === null) {
    setFlashMessage('danger', 'Errore di connessione al database.');
    logError('Errore connessione DB durante registrazione cliente');
    header('Location: admin-clienti-nuovo.php');
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
    header('Location: admin-clienti-nuovo.php');
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
    header('Location: admin-clienti-nuovo.php');
    exit;
}
mysqli_stmt_close($stmtCheckCF);

// Genera password temporanea
$passwordTemp = generateRandomPassword(12);
$passwordHash = hashPassword($passwordTemp);

// Ruolo fisso CLIENTE
$ruolo = 'CLIENTE';

// Ragione sociale può essere NULL
$ragioneSocialeFinal = !empty($ragioneSociale) ? $ragioneSociale : null;

// Inserisci cliente nel database
$queryInsert = "INSERT INTO UTENTE (nome, cognome, codice_fiscale, email, telefono, ragione_sociale, data_nascita, ruolo, password_hash) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmtInsert = mysqli_prepare($conn, $queryInsert);

if ($stmtInsert === false) {
    setFlashMessage('danger', 'Errore interno del server.');
    logError('Errore preparazione query insert cliente: ' . mysqli_error($conn));
    header('Location: admin-clienti-nuovo.php');
    exit;
}

mysqli_stmt_bind_param(
    $stmtInsert,
    'sssssssss',
    $nome,
    $cognome,
    $codiceFiscale,
    $email,
    $telefono,
    $ragioneSocialeFinal,
    $dataNascita,
    $ruolo,
    $passwordHash
);

$insertSuccess = mysqli_stmt_execute($stmtInsert);

if (!$insertSuccess) {
    mysqli_stmt_close($stmtInsert);
    setFlashMessage('danger', 'Errore durante la registrazione del cliente.');
    logError('Errore insert cliente: ' . mysqli_error($conn));
    header('Location: admin-clienti-nuovo.php');
    exit;
}

// Recupera ID cliente appena creato
$newClienteId = mysqli_insert_id($conn);
mysqli_stmt_close($stmtInsert);

// Log operazione
$adminId = getCurrentUserId();
$adminName = getCurrentUserName();
logError("ADMIN $adminName (ID: $adminId) ha registrato cliente: $nome $cognome (ID: $newClienteId, Email: $email)", 'INFO');

// Salva password temporanea in sessione per mostrarla
$_SESSION['new_cliente_data'] = [
    'id' => $newClienteId,
    'nome' => $nome,
    'cognome' => $cognome,
    'email' => $email,
    'password_temp' => $passwordTemp
];

// Reindirizza a pagina di successo
header('Location: admin-clienti-creato.php');
exit;
?>
