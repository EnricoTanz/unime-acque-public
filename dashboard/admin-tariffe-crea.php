<?php
/**
 * UNIME-ACQUE - Crea Tariffa
 * 
 * Inserisce una nuova tariffa nel database.
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
    header('Location: admin-tariffe.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Richiesta non valida.');
    header('Location: admin-tariffe.php');
    exit;
}

$nomeTariffa = trim($_POST['nome_tariffa'] ?? '');
$tariffaApplicata = $_POST['tariffa_applicata'] ?? '';
$descrizione = trim($_POST['descrizione'] ?? '');

// Validazione
$errors = [];

if (empty($nomeTariffa)) {
    $errors[] = 'Il nome della tariffa è obbligatorio.';
} elseif (strlen($nomeTariffa) > 50) {
    $errors[] = 'Il nome della tariffa non può superare 50 caratteri.';
}

if (empty($tariffaApplicata)) {
    $errors[] = 'La tariffa applicata è obbligatoria.';
} elseif (!is_numeric($tariffaApplicata) || (float)$tariffaApplicata < 0) {
    $errors[] = 'La tariffa applicata deve essere un numero positivo.';
} elseif ((float)$tariffaApplicata > 99.9999) {
    $errors[] = 'La tariffa applicata non può superare 99.9999 €/m³.';
}

if (!empty($descrizione) && strlen($descrizione) > 200) {
    $errors[] = 'La descrizione non può superare 200 caratteri.';
}

if (!empty($errors)) {
    setFlashMessage('danger', implode(' ', $errors));
    header('Location: admin-tariffe.php');
    exit;
}

$conn = getDbConnection();

if ($conn === null) {
    setFlashMessage('danger', 'Errore di connessione al database.');
    header('Location: admin-tariffe.php');
    exit;
}

// Verifica che il nome della tariffa non esista già (UNIQUE)
$queryCheck = "SELECT IdTariffa FROM TARIFFA WHERE nome_tariffa = ? LIMIT 1";
$stmtCheck = mysqli_prepare($conn, $queryCheck);
mysqli_stmt_bind_param($stmtCheck, 's', $nomeTariffa);
mysqli_stmt_execute($stmtCheck);
$resultCheck = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($resultCheck) > 0) {
    mysqli_stmt_close($stmtCheck);
    setFlashMessage('danger', 'Esiste già una tariffa con questo nome.');
    header('Location: admin-tariffe.php');
    exit;
}
mysqli_stmt_close($stmtCheck);

// Inserisci la tariffa
$queryInsert = "INSERT INTO TARIFFA (nome_tariffa, tariffa_applicata, descrizione) 
                VALUES (?, ?, ?)";

$stmtInsert = mysqli_prepare($conn, $queryInsert);

if ($stmtInsert === false) {
    setFlashMessage('danger', 'Errore nella preparazione della query.');
    logError('Errore preparazione insert tariffa: ' . mysqli_error($conn));
    header('Location: admin-tariffe.php');
    exit;
}

// Gestisci descrizione null
$descrizioneFinal = !empty($descrizione) ? $descrizione : null;

mysqli_stmt_bind_param($stmtInsert, 'sds', $nomeTariffa, $tariffaApplicata, $descrizioneFinal);
$success = mysqli_stmt_execute($stmtInsert);

if ($success) {
    $newTariffaId = mysqli_insert_id($conn);
    $adminName = getCurrentUserName();
    $adminId = getCurrentUserId();
    
    logError("ADMIN $adminName (ID: $adminId) ha creato la tariffa #$newTariffaId: $nomeTariffa (€ $tariffaApplicata/m³)", 'INFO');
    
    setFlashMessage('success', "Tariffa \"$nomeTariffa\" creata con successo!");
} else {
    setFlashMessage('danger', 'Errore durante la creazione della tariffa.');
    logError('Errore insert tariffa: ' . mysqli_error($conn));
}

mysqli_stmt_close($stmtInsert);

header('Location: admin-tariffe.php');
exit;
?>
