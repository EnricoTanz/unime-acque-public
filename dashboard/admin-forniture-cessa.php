<?php
/**
 * UNIME-ACQUE - Cessa Fornitura
 * 
 * Cessa una fornitura esistente (trigger cesserà anche contratto e contatore).
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
    header('Location: admin-forniture.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Richiesta non valida.');
    header('Location: admin-forniture.php');
    exit;
}

// Recupera ID fornitura
$idFornitura = isset($_POST['id_fornitura']) ? (int)$_POST['id_fornitura'] : 0;

if ($idFornitura <= 0) {
    setFlashMessage('danger', 'ID fornitura non valido.');
    header('Location: admin-forniture.php');
    exit;
}

$conn = getDbConnection();

if ($conn === null) {
    setFlashMessage('danger', 'Errore di connessione al database.');
    header('Location: admin-forniture.php');
    exit;
}

// Recupera informazioni fornitura
$queryCheck = "SELECT f.*, c.IdContratto, u.nome, u.cognome
               FROM FORNITURA f
               INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
               INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
               WHERE f.IdFornitura = ?
               LIMIT 1";

$stmtCheck = mysqli_prepare($conn, $queryCheck);
mysqli_stmt_bind_param($stmtCheck, 'i', $idFornitura);
mysqli_stmt_execute($stmtCheck);
$resultCheck = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($resultCheck) === 0) {
    mysqli_stmt_close($stmtCheck);
    setFlashMessage('danger', 'Fornitura non trovata.');
    header('Location: admin-forniture.php');
    exit;
}

$fornitura = mysqli_fetch_assoc($resultCheck);
mysqli_stmt_close($stmtCheck);

// Verifica che la fornitura non sia già cessata
if (strpos($fornitura['stato_fornitura'], 'DISATTIVATA') !== false) {
    setFlashMessage('danger', 'La fornitura è già stata cessata.');
    header('Location: admin-forniture.php');
    exit;
}

// Aggiorna stato fornitura
// Il trigger after_fornitura_cessazione si occuperà di:
// 1. Cessare il contratto (stato = CESSATO, data_fine_validita = CURRENT_DATE)
// 2. Cessare il contatore (stato = CESSATO PER DISATTIVAZIONE FORNITURA, data_dismissione = CURRENT_DATE)

$queryUpdate = "UPDATE FORNITURA 
                SET stato_fornitura = 'DISATTIVATA PER CESSAZIONE',
                    data_disattivazione = CURRENT_DATE
                WHERE IdFornitura = ?";

$stmt = mysqli_prepare($conn, $queryUpdate);

if ($stmt === false) {
    setFlashMessage('danger', 'Errore preparazione query: ' . mysqli_error($conn));
    header('Location: admin-forniture.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $idFornitura);

if (mysqli_stmt_execute($stmt)) {
    // Log operazione
    $adminId = getCurrentUserId();
    $adminName = getCurrentUserName();
    $clienteNome = $fornitura['nome'] . ' ' . $fornitura['cognome'];
    $indirizzo = $fornitura['indirizzo_fornitura'];
    
    logError("ADMIN $adminName (ID: $adminId) ha cessato fornitura #$idFornitura (Contratto #" . $fornitura['IdContratto'] . ", Cliente: $clienteNome) - Indirizzo: $indirizzo", 'INFO');
    
    setFlashMessage('success', "Fornitura #$idFornitura cessata con successo! Il contratto e il contatore associati sono stati automaticamente cessati.");
    header('Location: admin-forniture.php');
} else {
    $error = mysqli_stmt_error($stmt);
    setFlashMessage('danger', 'Errore durante la cessazione della fornitura: ' . $error);
    logError('Errore cessazione fornitura #' . $idFornitura . ': ' . $error);
    header('Location: admin-forniture.php');
}

mysqli_stmt_close($stmt);
exit;
?>
