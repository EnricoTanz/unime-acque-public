<?php
/**
 * UNIME-ACQUE - Prendi in Carico Ticket
 * 
 * Assegna un ticket non assegnato all'amministratore corrente.
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

// Verifica che il ticket esista e non sia già assegnato
$queryCheck = "SELECT IdSegnalazione FROM SEGNALAZIONE 
               WHERE IdSegnalazione = ? AND IdUtente_presa_in_carico IS NULL AND data_chiusura IS NULL";
$stmtCheck = mysqli_prepare($conn, $queryCheck);
mysqli_stmt_bind_param($stmtCheck, 'i', $ticketId);
mysqli_stmt_execute($stmtCheck);
$resultCheck = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($resultCheck) === 0) {
    mysqli_stmt_close($stmtCheck);
    setFlashMessage('danger', 'Ticket non disponibile per la presa in carico.');
    header('Location: admin-ticket.php');
    exit;
}
mysqli_stmt_close($stmtCheck);

// Assegna il ticket all'amministratore
$queryUpdate = "UPDATE SEGNALAZIONE SET IdUtente_presa_in_carico = ? WHERE IdSegnalazione = ?";
$stmtUpdate = mysqli_prepare($conn, $queryUpdate);
mysqli_stmt_bind_param($stmtUpdate, 'ii', $userId, $ticketId);
$success = mysqli_stmt_execute($stmtUpdate);
mysqli_stmt_close($stmtUpdate);

if ($success) {
    logError("ADMIN " . getCurrentUserName() . " (ID: $userId) ha preso in carico ticket #$ticketId", 'INFO');
    setFlashMessage('success', 'Ticket preso in carico con successo!');
} else {
    setFlashMessage('danger', 'Errore durante la presa in carico del ticket.');
}

header('Location: admin-ticket-dettaglio.php?id=' . $ticketId);
exit;
?>
