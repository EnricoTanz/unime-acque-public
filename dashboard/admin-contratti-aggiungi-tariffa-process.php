<?php
/**
 * UNIME-ACQUE - Process Aggiungi Tariffa a Contratto
 * 
 * Inserisce una nuova tariffa in ABBINAMENTO_TARIFFA per un contratto esistente.
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
    header('Location: admin-contratti.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Richiesta non valida.');
    header('Location: admin-contratti.php');
    exit;
}

// Recupera dati form
$idContratto = isset($_POST['id_contratto']) ? (int)$_POST['id_contratto'] : 0;
$idTariffa = isset($_POST['id_tariffa']) ? (int)$_POST['id_tariffa'] : 0;
$dataInizio = trim($_POST['data_inizio'] ?? '');
$dataFine = trim($_POST['data_fine'] ?? '');

// Validazione
$errors = [];

if ($idContratto <= 0) {
    $errors[] = 'Contratto non valido.';
}

if ($idTariffa <= 0) {
    $errors[] = 'Tariffa non valida.';
}

if (empty($dataInizio) || !validateDate($dataInizio, 'ymd')) {
    $errors[] = 'Data inizio non valida.';
}

// Validazione data fine (opzionale ma se presente deve essere successiva a data_inizio)
if (!empty($dataFine)) {
    if (!validateDate($dataFine, 'ymd')) {
        $errors[] = 'Data fine non valida.';
    } else {
        $inizio = new DateTime($dataInizio);
        $fine = new DateTime($dataFine);
        
        if ($fine <= $inizio) {
            $errors[] = 'La data di fine validità deve essere successiva alla data di inizio.';
        }
    }
}

if (!empty($errors)) {
    setFlashMessage('danger', implode(' ', $errors));
    header('Location: admin-contratti-aggiungi-tariffa.php?id=' . $idContratto);
    exit;
}

$conn = getDbConnection();

if ($conn === null) {
    setFlashMessage('danger', 'Errore di connessione al database.');
    header('Location: admin-contratti-aggiungi-tariffa.php?id=' . $idContratto);
    exit;
}

// Verifica che il contratto esista e sia ATTIVO
$queryCheckContratto = "SELECT IdContratto, stato_contratto, IdUtente 
                        FROM CONTRATTO 
                        WHERE IdContratto = ? 
                        LIMIT 1";
$stmtCheck = mysqli_prepare($conn, $queryCheckContratto);
mysqli_stmt_bind_param($stmtCheck, 'i', $idContratto);
mysqli_stmt_execute($stmtCheck);
$resultCheck = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($resultCheck) === 0) {
    mysqli_stmt_close($stmtCheck);
    setFlashMessage('danger', 'Contratto non trovato.');
    header('Location: admin-contratti.php');
    exit;
}

$contratto = mysqli_fetch_assoc($resultCheck);
mysqli_stmt_close($stmtCheck);

// Verifica che la tariffa esista
$queryCheckTariffa = "SELECT IdTariffa, nome_tariffa 
                      FROM TARIFFA 
                      WHERE IdTariffa = ? 
                      LIMIT 1";
$stmtCheckT = mysqli_prepare($conn, $queryCheckTariffa);
mysqli_stmt_bind_param($stmtCheckT, 'i', $idTariffa);
mysqli_stmt_execute($stmtCheckT);
$resultCheckT = mysqli_stmt_get_result($stmtCheckT);

if (mysqli_num_rows($resultCheckT) === 0) {
    mysqli_stmt_close($stmtCheckT);
    setFlashMessage('danger', 'Tariffa non trovata.');
    header('Location: admin-contratti-aggiungi-tariffa.php?id=' . $idContratto);
    exit;
}

$tariffa = mysqli_fetch_assoc($resultCheckT);
mysqli_stmt_close($stmtCheckT);

// Verifica se esiste già questo abbinamento (stesso IdContratto e IdTariffa)
// Nota: la primary key è (IdContratto, IdTariffa), quindi non possiamo avere duplicati
$queryCheckAbbinamento = "SELECT COUNT(*) as count 
                          FROM ABBINAMENTO_TARIFFA 
                          WHERE IdContratto = ? AND IdTariffa = ?";
$stmtCheckAbb = mysqli_prepare($conn, $queryCheckAbbinamento);
mysqli_stmt_bind_param($stmtCheckAbb, 'ii', $idContratto, $idTariffa);
mysqli_stmt_execute($stmtCheckAbb);
$resultCheckAbb = mysqli_stmt_get_result($stmtCheckAbb);
$rowAbb = mysqli_fetch_assoc($resultCheckAbb);
mysqli_stmt_close($stmtCheckAbb);

if ($rowAbb['count'] > 0) {
    setFlashMessage('danger', 'Questa tariffa è già associata al contratto. Per modificare le date, elimina prima l\'abbinamento esistente.');
    header('Location: admin-contratti-aggiungi-tariffa.php?id=' . $idContratto);
    exit;
}

// Prepara data_fine per l'insert
$dataFineFinal = !empty($dataFine) ? $dataFine : null;

// Inserisci abbinamento tariffa
$queryInsert = "INSERT INTO ABBINAMENTO_TARIFFA (IdContratto, IdTariffa, data_inizio, data_fine) 
                VALUES (?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $queryInsert);

if ($stmt === false) {
    setFlashMessage('danger', 'Errore preparazione query: ' . mysqli_error($conn));
    header('Location: admin-contratti-aggiungi-tariffa.php?id=' . $idContratto);
    exit;
}

mysqli_stmt_bind_param($stmt, 'iiss', $idContratto, $idTariffa, $dataInizio, $dataFineFinal);

if (mysqli_stmt_execute($stmt)) {
    // Log operazione
    $adminId = getCurrentUserId();
    $adminName = getCurrentUserName();
    $tariffaNome = $tariffa['nome_tariffa'];
    
    $dataFineStr = !empty($dataFine) ? "fino al $dataFine" : "senza scadenza";
    logError("ADMIN $adminName (ID: $adminId) ha aggiunto la tariffa '$tariffaNome' (ID: $idTariffa) al contratto #$idContratto dal $dataInizio $dataFineStr", 'INFO');
    
    setFlashMessage('success', "Tariffa '{$tariffaNome}' aggiunta con successo al contratto #$idContratto!");
    header('Location: admin-contratti.php');
} else {
    $error = mysqli_stmt_error($stmt);
    setFlashMessage('danger', 'Errore durante l\'inserimento della tariffa: ' . $error);
    logError('Errore insert ABBINAMENTO_TARIFFA per contratto #' . $idContratto . ': ' . $error);
    header('Location: admin-contratti-aggiungi-tariffa.php?id=' . $idContratto);
}

mysqli_stmt_close($stmt);
exit;
?>
