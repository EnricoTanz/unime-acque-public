<?php
/**
 * UNIME-ACQUE - Process Creazione Contratto
 * 
 * Crea contratto e abbinamento tariffa.
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
    header('Location: admin-contratti-nuovo.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Richiesta non valida.');
    header('Location: admin-contratti-nuovo.php');
    exit;
}

// Recupera dati form
$idCliente = isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : 0;
$tipoContratto = strtoupper(trim($_POST['tipo_contratto'] ?? ''));
$idTariffa = isset($_POST['id_tariffa']) ? (int)$_POST['id_tariffa'] : 0;
$tariffaDataFine = trim($_POST['tariffa_data_fine'] ?? '');

// Validazione
$errors = [];

if ($idCliente <= 0) {
    $errors[] = 'Cliente non valido.';
}

if (!in_array($tipoContratto, ['DOMESTICA', 'BUSINESS'])) {
    $errors[] = 'Tipo contratto non valido.';
}

if ($idTariffa <= 0) {
    $errors[] = 'Tariffa non valida.';
}

// Validazione data fine tariffa (opzionale ma se presente deve essere futura)
if (!empty($tariffaDataFine)) {
    if (!validateDate($tariffaDataFine, 'ymd')) {
        $errors[] = 'Data scadenza tariffa non valida.';
    } else {
        $dataFine = new DateTime($tariffaDataFine);
        $oggi = new DateTime();
        
        if ($dataFine <= $oggi) {
            $errors[] = 'La data di scadenza della tariffa deve essere futura.';
        }
    }
}

if (!empty($errors)) {
    setFlashMessage('danger', implode(' ', $errors));
    header('Location: admin-contratti-nuovo.php');
    exit;
}

$conn = getDbConnection();

if ($conn === null) {
    setFlashMessage('danger', 'Errore di connessione al database.');
    header('Location: admin-contratti-nuovo.php');
    exit;
}

// Verifica che il cliente esista
$queryCheckCliente = "SELECT IdUtente, nome, cognome FROM UTENTE WHERE IdUtente = ? AND ruolo = 'CLIENTE' LIMIT 1";
$stmtCheck = mysqli_prepare($conn, $queryCheckCliente);
mysqli_stmt_bind_param($stmtCheck, 'i', $idCliente);
mysqli_stmt_execute($stmtCheck);
$resultCheck = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($resultCheck) === 0) {
    mysqli_stmt_close($stmtCheck);
    setFlashMessage('danger', 'Cliente non trovato.');
    header('Location: admin-contratti-nuovo.php');
    exit;
}

$cliente = mysqli_fetch_assoc($resultCheck);
mysqli_stmt_close($stmtCheck);

// Verifica che la tariffa esista
$queryCheckTariffa = "SELECT IdTariffa, nome_tariffa FROM TARIFFA WHERE IdTariffa = ? LIMIT 1";
$stmtCheckT = mysqli_prepare($conn, $queryCheckTariffa);
mysqli_stmt_bind_param($stmtCheckT, 'i', $idTariffa);
mysqli_stmt_execute($stmtCheckT);
$resultCheckT = mysqli_stmt_get_result($stmtCheckT);

if (mysqli_num_rows($resultCheckT) === 0) {
    mysqli_stmt_close($stmtCheckT);
    setFlashMessage('danger', 'Tariffa non trovata.');
    header('Location: admin-contratti-nuovo.php');
    exit;
}

$tariffa = mysqli_fetch_assoc($resultCheckT);
mysqli_stmt_close($stmtCheckT);

// Date automatiche
$dataStipula = date('Y-m-d');
$dataInizioValidita = date('Y-m-d');
$statoContratto = 'ATTIVO';

// Inizia transazione
mysqli_begin_transaction($conn);

try {
    // 1. Inserisci il contratto
    $queryInsertContratto = "INSERT INTO CONTRATTO (IdUtente, data_stipula, data_inizio_validita, stato_contratto, tipo_contratto) 
                             VALUES (?, ?, ?, ?, ?)";
    
    $stmtContratto = mysqli_prepare($conn, $queryInsertContratto);
    
    if ($stmtContratto === false) {
        throw new Exception('Errore preparazione insert contratto: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmtContratto, 'issss', $idCliente, $dataStipula, $dataInizioValidita, $statoContratto, $tipoContratto);
    
    if (!mysqli_stmt_execute($stmtContratto)) {
        throw new Exception('Errore insert contratto: ' . mysqli_stmt_error($stmtContratto));
    }
    
    // Recupera ID contratto appena creato
    $idContratto = mysqli_insert_id($conn);
    mysqli_stmt_close($stmtContratto);
    
    if ($idContratto <= 0) {
        throw new Exception('ID contratto non valido dopo insert.');
    }
    
    // 2. Inserisci abbinamento tariffa
    $dataInizioTariffa = date('Y-m-d');
    $dataFineTariffaFinal = !empty($tariffaDataFine) ? $tariffaDataFine : null;
    
    $queryInsertAbbinamento = "INSERT INTO ABBINAMENTO_TARIFFA (IdContratto, IdTariffa, data_inizio, data_fine) 
                               VALUES (?, ?, ?, ?)";
    
    $stmtAbbinamento = mysqli_prepare($conn, $queryInsertAbbinamento);
    
    if ($stmtAbbinamento === false) {
        throw new Exception('Errore preparazione insert abbinamento tariffa: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmtAbbinamento, 'iiss', $idContratto, $idTariffa, $dataInizioTariffa, $dataFineTariffaFinal);
    
    if (!mysqli_stmt_execute($stmtAbbinamento)) {
        throw new Exception('Errore insert abbinamento tariffa: ' . mysqli_stmt_error($stmtAbbinamento));
    }
    
    mysqli_stmt_close($stmtAbbinamento);
    
    // Commit transazione
    mysqli_commit($conn);
    
    // Log operazione
    $adminId = getCurrentUserId();
    $adminName = getCurrentUserName();
    $clienteNome = $cliente['nome'] . ' ' . $cliente['cognome'];
    $tariffaNome = $tariffa['nome_tariffa'];
    
    logError("ADMIN $adminName (ID: $adminId) ha creato contratto #$idContratto per cliente $clienteNome (ID: $idCliente) con tariffa $tariffaNome (ID: $idTariffa)", 'INFO');
    
    setFlashMessage('success', "Contratto #$idContratto creato con successo per $clienteNome!");
    header('Location: admin-contratti.php');
    exit;
    
} catch (Exception $e) {
    // Rollback in caso di errore
    mysqli_rollback($conn);
    
    logError('Errore creazione contratto: ' . $e->getMessage());
    setFlashMessage('danger', 'Errore durante la creazione del contratto: ' . $e->getMessage());
    header('Location: admin-contratti-nuovo.php');
    exit;
}
?>
