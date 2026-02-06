<?php
/**
 * UNIME-ACQUE - Chiudi Ticket
 * 
 * Chiude un ticket assegnato all'amministratore corrente.
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
    header('Location: admin-ticket.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Richiesta non valida.');
    header('Location: admin-ticket.php');
    exit;
}

$ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
$userId = getCurrentUserId();

if ($ticketId <= 0) {
    setFlashMessage('danger', 'ID ticket non valido.');
    header('Location: admin-ticket.php');
    exit;
}

$conn = getDbConnection();

// Verifica che il ticket sia assegnato all'amministratore e non sia già chiuso
$queryCheck = "SELECT IdSegnalazione FROM SEGNALAZIONE 
               WHERE IdSegnalazione = ? AND IdUtente_presa_in_carico = ? AND data_chiusura IS NULL";
$stmtCheck = mysqli_prepare($conn, $queryCheck);
mysqli_stmt_bind_param($stmtCheck, 'ii', $ticketId, $userId);
mysqli_stmt_execute($stmtCheck);
$resultCheck = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($resultCheck) === 0) {
    mysqli_stmt_close($stmtCheck);
    setFlashMessage('danger', 'Ticket non disponibile per la chiusura.');
    header('Location: admin-ticket.php');
    exit;
}
mysqli_stmt_close($stmtCheck);

// Chiude il ticket
$queryUpdate = "UPDATE SEGNALAZIONE SET data_chiusura = CURRENT_DATE WHERE IdSegnalazione = ?";
$stmtUpdate = mysqli_prepare($conn, $queryUpdate);
mysqli_stmt_bind_param($stmtUpdate, 'i', $ticketId);
$success = mysqli_stmt_execute($stmtUpdate);
mysqli_stmt_close($stmtUpdate);

if ($success) {
    logError("ADMIN " . getCurrentUserName() . " (ID: $userId) ha chiuso ticket #$ticketId", 'INFO');
    setFlashMessage('success', 'Ticket chiuso con successo!');
} else {
    setFlashMessage('danger', 'Errore durante la chiusura del ticket.');
}

header('Location: admin-ticket-dettaglio.php?id=' . $ticketId);
exit;
?>
