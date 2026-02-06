<?php
/**
 * UNIME-ACQUE - Process Inserimento Lettura Consumi (DEBUG CON MESSAGGI)
 * 
 * Gestisce l'inserimento di una nuova lettura consumi.
 * Mostra debug nei messaggi flash invece dei log.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

// Imposta timezone italiano
date_default_timezone_set('Europe/Rome');

require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

startSecureSession();
requireRole('TECNICO');

if (!isPostRequest()) {
    header('Location: tecnico-letture.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Richiesta non valida.');
    header('Location: tecnico-letture.php');
    exit;
}

// Recupera dati form
$idContatore = isset($_POST['id_contatore']) ? (int)$_POST['id_contatore'] : 0;
$dataRif = isset($_POST['data_rif']) ? trim($_POST['data_rif']) : '';
$volConsumato = isset($_POST['vol_consumato']) ? (int)$_POST['vol_consumato'] : 0;

// ID tecnico
$idTecnico = getCurrentUserId();
$nomeTecnico = getCurrentUserName();

// DEBUG: Mostra cosa arriva
$oggi = date('Y-m-d');
$debugMsg = "🔍 DEBUG:\n";
$debugMsg .= "Data ricevuta: '$dataRif'\n";
$debugMsg .= "Data server (oggi): '$oggi'\n";
$debugMsg .= "Lunghezza data: " . strlen($dataRif) . " caratteri\n";
$debugMsg .= "Tipo dato: " . gettype($dataRif) . "\n";
$debugMsg .= "Confronto '$dataRif' > '$oggi': " . ($dataRif > $oggi ? 'TRUE (è futura!)' : 'FALSE (ok)') . "\n";

// Validazione
$errors = [];

if ($idContatore <= 0) {
    $errors[] = 'Contatore non valido.';
}

if (empty($dataRif)) {
    $errors[] = 'Data lettura obbligatoria.';
}

// Valida formato data
if (!empty($dataRif) && !validateDate($dataRif, 'ymd')) {
    $debugMsg .= "❌ Formato data NON valido come YYYY-MM-DD\n";
    $errors[] = 'Formato data non valido.';
} else {
    $debugMsg .= "✅ Formato data valido\n";
}

// Verifica che la data non sia futura
if (!empty($dataRif)) {
    if ($dataRif > $oggi) {
        $errors[] = 'La data della lettura non può essere futura.';
        $debugMsg .= "❌ BLOCCO: Data considerata futura!\n";
    } else {
        $debugMsg .= "✅ Data OK (non futura)\n";
    }
}

if ($volConsumato < 0) {
    $errors[] = 'Volume consumato deve essere >= 0.';
}

if ($volConsumato > 99999) {
    $errors[] = 'Volume consumato troppo alto (max 99999 m³).';
}

// Se ci sono errori, mostra il debug
if (!empty($errors)) {
    setFlashMessage('danger', $debugMsg . "\n\nERRORI:\n" . implode("\n", $errors));
    header('Location: tecnico-letture.php');
    exit;
}

$conn = getDbConnection();

if ($conn === null) {
    setFlashMessage('danger', 'Errore di connessione al database.');
    header('Location: tecnico-letture.php');
    exit;
}

// Verifica che il contatore esista ed sia ATTIVO
$queryCheckContatore = "SELECT cnt.IdContatore, cnt.matricola_contatore, cnt.stato_contatore,
                              f.IdFornitura, f.indirizzo_fornitura,
                              c.IdContratto,
                              u.nome, u.cognome
                       FROM CONTATORE cnt
                       INNER JOIN FORNITURA f ON cnt.IdFornitura = f.IdFornitura
                       INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
                       INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
                       WHERE cnt.IdContatore = ?
                       LIMIT 1";

$stmtCheck = mysqli_prepare($conn, $queryCheckContatore);
mysqli_stmt_bind_param($stmtCheck, 'i', $idContatore);
mysqli_stmt_execute($stmtCheck);
$resultCheck = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($resultCheck) === 0) {
    mysqli_stmt_close($stmtCheck);
    setFlashMessage('danger', 'Contatore non trovato.');
    header('Location: tecnico-letture.php');
    exit;
}

$contatore = mysqli_fetch_assoc($resultCheck);
mysqli_stmt_close($stmtCheck);

if ($contatore['stato_contatore'] !== 'ATTIVO') {
    setFlashMessage('danger', 'Il contatore selezionato non è attivo.');
    header('Location: tecnico-letture.php');
    exit;
}

// Verifica che non esista già una lettura per questo contatore in questa data
$queryCheckDuplicato = "SELECT COUNT(*) as count 
                        FROM LETTURA_CONSUMI 
                        WHERE IdContatore = ? AND data_rif = ?";

$stmtCheckDup = mysqli_prepare($conn, $queryCheckDuplicato);
mysqli_stmt_bind_param($stmtCheckDup, 'is', $idContatore, $dataRif);
mysqli_stmt_execute($stmtCheckDup);
$resultCheckDup = mysqli_stmt_get_result($stmtCheckDup);
$rowDup = mysqli_fetch_assoc($resultCheckDup);
mysqli_stmt_close($stmtCheckDup);

if ($rowDup['count'] > 0) {
    setFlashMessage('danger', 'Esiste già una lettura per questo contatore in questa data. Per modificarla, contatta un amministratore.');
    header('Location: tecnico-letture.php');
    exit;
}

// Inserisci la lettura
$queryInsert = "INSERT INTO LETTURA_CONSUMI (IdContatore, data_rif, vol_consumato, vol_rettificato, data_rettifica)
                VALUES (?, ?, ?, NULL, NULL)";

$stmt = mysqli_prepare($conn, $queryInsert);

if ($stmt === false) {
    setFlashMessage('danger', 'Errore preparazione query: ' . mysqli_error($conn));
    header('Location: tecnico-letture.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 'isi', 
    $idContatore,
    $dataRif,
    $volConsumato
);

if (mysqli_stmt_execute($stmt)) {
    // Log operazione (se la cartella logs esiste)
    $clienteNome = $contatore['nome'] . ' ' . $contatore['cognome'];
    $matricola = $contatore['matricola_contatore'];
    $indirizzo = $contatore['indirizzo_fornitura'];
    
    setFlashMessage('success', "Lettura inserita con successo! Contatore #$idContatore - Data: " . date('d/m/Y', strtotime($dataRif)) . " - Volume: $volConsumato m³");
    header('Location: tecnico.php');
} else {
    $error = mysqli_stmt_error($stmt);
    setFlashMessage('danger', 'Errore durante l\'inserimento della lettura: ' . $error);
    header('Location: tecnico-letture.php');
}

mysqli_stmt_close($stmt);
exit;
?>
