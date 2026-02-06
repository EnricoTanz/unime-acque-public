<?php
/**
 * UNIME-ACQUE - Process Creazione Fornitura
 * 
 * Crea una nuova fornitura associata a un contratto.
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
    header('Location: admin-forniture-nuova.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Richiesta non valida.');
    header('Location: admin-forniture-nuova.php');
    exit;
}

// Recupera dati form
$idContratto = isset($_POST['id_contratto']) ? (int)$_POST['id_contratto'] : 0;
$idArea = isset($_POST['id_area']) ? (int)$_POST['id_area'] : 0;

// Validazione
$errors = [];

if ($idContratto <= 0) {
    $errors[] = 'Contratto non valido.';
}

if ($idArea <= 0) {
    $errors[] = 'Area geografica non valida.';
}

if (!empty($errors)) {
    setFlashMessage('danger', implode(' ', $errors));
    header('Location: admin-forniture-nuova.php');
    exit;
}

$conn = getDbConnection();

if ($conn === null) {
    setFlashMessage('danger', 'Errore di connessione al database.');
    header('Location: admin-forniture-nuova.php');
    exit;
}

// Verifica che il contratto esista e sia ATTIVO
$queryCheckContratto = "SELECT c.IdContratto, c.stato_contratto, c.IdUtente,
                               u.nome, u.cognome
                        FROM CONTRATTO c
                        INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
                        WHERE c.IdContratto = ? 
                        LIMIT 1";

$stmtCheck = mysqli_prepare($conn, $queryCheckContratto);
mysqli_stmt_bind_param($stmtCheck, 'i', $idContratto);
mysqli_stmt_execute($stmtCheck);
$resultCheck = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($resultCheck) === 0) {
    mysqli_stmt_close($stmtCheck);
    setFlashMessage('danger', 'Contratto non trovato.');
    header('Location: admin-forniture-nuova.php');
    exit;
}

$contratto = mysqli_fetch_assoc($resultCheck);
mysqli_stmt_close($stmtCheck);

if ($contratto['stato_contratto'] !== 'ATTIVO') {
    setFlashMessage('danger', 'Il contratto selezionato non è attivo.');
    header('Location: admin-forniture-nuova.php');
    exit;
}

// Verifica che il contratto non abbia già una fornitura
$queryCheckFornitura = "SELECT COUNT(*) as count FROM FORNITURA WHERE IdContratto = ?";
$stmtCheckF = mysqli_prepare($conn, $queryCheckFornitura);
mysqli_stmt_bind_param($stmtCheckF, 'i', $idContratto);
mysqli_stmt_execute($stmtCheckF);
$resultCheckF = mysqli_stmt_get_result($stmtCheckF);
$rowF = mysqli_fetch_assoc($resultCheckF);
mysqli_stmt_close($stmtCheckF);

if ($rowF['count'] > 0) {
    setFlashMessage('danger', 'Questo contratto ha già una fornitura associata.');
    header('Location: admin-forniture-nuova.php');
    exit;
}

// Verifica che l'area geografica esista
$queryCheckArea = "SELECT ag.IdArea, ag.nome_area, ag.CAP, l.citta
                   FROM AREA_GEOGRAFICA ag
                   INNER JOIN LOCALITA l ON ag.CAP = l.CAP
                   WHERE ag.IdArea = ? 
                   LIMIT 1";

$stmtCheckA = mysqli_prepare($conn, $queryCheckArea);
mysqli_stmt_bind_param($stmtCheckA, 'i', $idArea);
mysqli_stmt_execute($stmtCheckA);
$resultCheckA = mysqli_stmt_get_result($stmtCheckA);

if (mysqli_num_rows($resultCheckA) === 0) {
    mysqli_stmt_close($stmtCheckA);
    setFlashMessage('danger', 'Area geografica non trovata.');
    header('Location: admin-forniture-nuova.php');
    exit;
}

$area = mysqli_fetch_assoc($resultCheckA);
mysqli_stmt_close($stmtCheckA);

// Costruisci indirizzo fornitura completo
$indirizzoFornitura = $area['nome_area'] . ', ' . $area['citta'] . ' (' . $area['CAP'] . ')';

// Dati fornitura
$statoFornitura = 'IN ATTESA DI ATTIVAZIONE';
$dataAttivazione = null; // Verrà impostata dal trigger quando viene creato il contatore
$dataDisattivazione = null;

// Inserisci fornitura
$queryInsert = "INSERT INTO FORNITURA (IdContratto, IdArea_fornitura, stato_fornitura, indirizzo_fornitura, data_attivazione, data_disattivazione)
                VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $queryInsert);

if ($stmt === false) {
    setFlashMessage('danger', 'Errore preparazione query: ' . mysqli_error($conn));
    header('Location: admin-forniture-nuova.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 'iissss', 
    $idContratto, 
    $idArea, 
    $statoFornitura, 
    $indirizzoFornitura,
    $dataAttivazione,
    $dataDisattivazione
);

if (mysqli_stmt_execute($stmt)) {
    $idFornitura = mysqli_insert_id($conn);
    
    // Log operazione
    $adminId = getCurrentUserId();
    $adminName = getCurrentUserName();
    $clienteNome = $contratto['nome'] . ' ' . $contratto['cognome'];
    
    logError("ADMIN $adminName (ID: $adminId) ha creato fornitura #$idFornitura per contratto #$idContratto (Cliente: $clienteNome) - Indirizzo: $indirizzoFornitura", 'INFO');
    
    setFlashMessage('success', "Fornitura #$idFornitura creata con successo! Stato: IN ATTESA DI ATTIVAZIONE. La fornitura verrà attivata quando verrà installato un contatore.");
    header('Location: admin-forniture.php');
} else {
    $error = mysqli_stmt_error($stmt);
    setFlashMessage('danger', 'Errore durante la creazione della fornitura: ' . $error);
    logError('Errore insert FORNITURA per contratto #' . $idContratto . ': ' . $error);
    header('Location: admin-forniture-nuova.php');
}

mysqli_stmt_close($stmt);
exit;
?>
