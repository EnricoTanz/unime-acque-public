<?php
/**
 * UNIME-ACQUE - Rettifica Consumo
 * 
 * Aggiorna il campo vol_rettificato di una lettura consumo.
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
    header('Location: admin-consumi.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Richiesta non valida.');
    header('Location: admin-consumi.php');
    exit;
}

$idContatore = isset($_POST['id_contatore']) ? (int)$_POST['id_contatore'] : 0;
$dataRif = $_POST['data_rif'] ?? '';
$volRettificato = isset($_POST['vol_rettificato']) ? (int)$_POST['vol_rettificato'] : null;
$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$contractId = isset($_POST['contract_id']) ? (int)$_POST['contract_id'] : 0;

// Validazione
$errors = [];

if ($idContatore <= 0) {
    $errors[] = 'ID contatore non valido.';
}

if (empty($dataRif) || !validateDate($dataRif, 'ymd')) {
    $errors[] = 'Data riferimento non valida.';
}

if ($volRettificato === null || $volRettificato < 0) {
    $errors[] = 'Il volume rettificato deve essere un numero positivo.';
}

if (!empty($errors)) {
    setFlashMessage('danger', implode(' ', $errors));
    header("Location: admin-consumi.php?user_id=$userId&contract_id=$contractId");
    exit;
}

$conn = getDbConnection();

// Aggiorna la lettura
$query = "UPDATE LETTURA_CONSUMI 
          SET vol_rettificato = ?, 
              data_rettifica = CURRENT_TIMESTAMP 
          WHERE IdContatore = ? AND data_rif = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'iis', $volRettificato, $idContatore, $dataRif);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($success) {
    $adminName = getCurrentUserName();
    $adminId = getCurrentUserId();
    logError("ADMIN $adminName (ID: $adminId) ha rettificato consumo: Contatore #$idContatore, Data: $dataRif, Nuovo valore: $volRettificato m³", 'INFO');
    setFlashMessage('success', 'Consumo rettificato con successo!');
} else {
    setFlashMessage('danger', 'Errore durante la rettifica del consumo.');
}

header("Location: admin-consumi.php?user_id=$userId&contract_id=$contractId");
exit;
?>
