<?php
/**
 * UNIME-ACQUE - Process Installazione Contatore
 * 
 * Gestisce l'installazione di un nuovo contatore su una fornitura.
 * In caso di sostituzione, invoca la stored procedure imposta_contatore_sostituito.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

startSecureSession();
requireRole('TECNICO');

if (!isPostRequest()) {
    header('Location: tecnico-contatori.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Richiesta non valida.');
    header('Location: tecnico-contatori.php');
    exit;
}

// Recupera dati form
$idFornitura = isset($_POST['id_fornitura']) ? (int)$_POST['id_fornitura'] : 0;
$matricolaContatore = isset($_POST['matricola_contatore']) ? trim($_POST['matricola_contatore']) : '';
$marcaContatore = isset($_POST['marca_contatore']) ? trim($_POST['marca_contatore']) : '';
$note = isset($_POST['note']) ? trim($_POST['note']) : '';
$isSostituzione = isset($_POST['is_sostituzione']) && $_POST['is_sostituzione'] == '1';
$sostituisceIdContatore = isset($_POST['sostituisce_id_contatore']) ? (int)$_POST['sostituisce_id_contatore'] : null;

// ID tecnico installatore
$idUtenteInstallatore = getCurrentUserId();
$nomeInstallatore = getCurrentUserName();

// Validazione
$errors = [];

// Se è una sostituzione, la fornitura può essere omessa (verrà presa dal contatore sostituito)
if (!$isSostituzione && $idFornitura <= 0) {
    $errors[] = 'Fornitura non valida. Seleziona una fornitura o spunta "sostituzione".';
}

if (empty($matricolaContatore)) {
    $errors[] = 'Matricola contatore obbligatoria.';
}

if (strlen($matricolaContatore) > 50) {
    $errors[] = 'Matricola contatore troppo lunga (max 50 caratteri).';
}

if (empty($marcaContatore)) {
    $errors[] = 'Marca contatore obbligatoria.';
}

if (strlen($marcaContatore) > 50) {
    $errors[] = 'Marca contatore troppo lunga (max 50 caratteri).';
}

if (!empty($note) && strlen($note) > 250) {
    $errors[] = 'Note troppo lunghe (max 250 caratteri).';
}

if ($isSostituzione && (!$sostituisceIdContatore || $sostituisceIdContatore <= 0)) {
    $errors[] = 'Seleziona il contatore da sostituire.';
}

if (!empty($errors)) {
    setFlashMessage('danger', implode(' ', $errors));
    header('Location: tecnico-contatori.php');
    exit;
}

$conn = getDbConnection();

if ($conn === null) {
    setFlashMessage('danger', 'Errore di connessione al database.');
    header('Location: tecnico-contatori.php');
    exit;
}

// Se è una sostituzione e non è stata selezionata una fornitura, la recuperiamo dal contatore da sostituire
if ($isSostituzione && $idFornitura <= 0 && $sostituisceIdContatore > 0) {
    $queryGetFornitura = "SELECT IdFornitura FROM CONTATORE WHERE IdContatore = ? LIMIT 1";
    $stmtGetFornitura = mysqli_prepare($conn, $queryGetFornitura);
    mysqli_stmt_bind_param($stmtGetFornitura, 'i', $sostituisceIdContatore);
    mysqli_stmt_execute($stmtGetFornitura);
    $resultGetFornitura = mysqli_stmt_get_result($stmtGetFornitura);
    
    if ($rowFornitura = mysqli_fetch_assoc($resultGetFornitura)) {
        $idFornitura = $rowFornitura['IdFornitura'];
    } else {
        mysqli_stmt_close($stmtGetFornitura);
        setFlashMessage('danger', 'Impossibile recuperare la fornitura dal contatore da sostituire.');
        header('Location: tecnico-contatori.php');
        exit;
    }
    
    mysqli_stmt_close($stmtGetFornitura);
}

// Verifica che ora abbiamo una fornitura valida
if ($idFornitura <= 0) {
    setFlashMessage('danger', 'Fornitura non valida.');
    header('Location: tecnico-contatori.php');
    exit;
}

// Verifica che la fornitura esista
$queryCheckFornitura = "SELECT f.*, c.IdContratto, u.nome, u.cognome
                        FROM FORNITURA f
                        INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
                        INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
                        WHERE f.IdFornitura = ?
                        LIMIT 1";

$stmtCheck = mysqli_prepare($conn, $queryCheckFornitura);
mysqli_stmt_bind_param($stmtCheck, 'i', $idFornitura);
mysqli_stmt_execute($stmtCheck);
$resultCheck = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($resultCheck) === 0) {
    mysqli_stmt_close($stmtCheck);
    setFlashMessage('danger', 'Fornitura non trovata.');
    header('Location: tecnico-contatori.php');
    exit;
}

$fornitura = mysqli_fetch_assoc($resultCheck);
mysqli_stmt_close($stmtCheck);

// Verifica che la fornitura non abbia già un contatore attivo
// SOLO se NON è una sostituzione (in caso di sostituzione, è normale che ci sia già un contatore)
if (!$isSostituzione) {
    $queryCheckContatoreAttivo = "SELECT COUNT(*) as count 
                                  FROM CONTATORE 
                                  WHERE IdFornitura = ? 
                                    AND stato_contatore = 'ATTIVO'";

    $stmtCheckCnt = mysqli_prepare($conn, $queryCheckContatoreAttivo);
    mysqli_stmt_bind_param($stmtCheckCnt, 'i', $idFornitura);
    mysqli_stmt_execute($stmtCheckCnt);
    $resultCheckCnt = mysqli_stmt_get_result($stmtCheckCnt);
    $rowCnt = mysqli_fetch_assoc($resultCheckCnt);
    mysqli_stmt_close($stmtCheckCnt);

    if ($rowCnt['count'] > 0) {
        setFlashMessage('danger', 'La fornitura selezionata ha già un contatore attivo. Per sostituirlo, spunta l\'opzione "Questo contatore sostituisce un contatore esistente".');
        header('Location: tecnico-contatori.php');
        exit;
    }
}

// Verifica che la matricola non sia già usata
$queryCheckMatricola = "SELECT COUNT(*) as count FROM CONTATORE WHERE matricola_contatore = ?";
$stmtCheckMat = mysqli_prepare($conn, $queryCheckMatricola);
mysqli_stmt_bind_param($stmtCheckMat, 's', $matricolaContatore);
mysqli_stmt_execute($stmtCheckMat);
$resultCheckMat = mysqli_stmt_get_result($stmtCheckMat);
$rowMat = mysqli_fetch_assoc($resultCheckMat);
mysqli_stmt_close($stmtCheckMat);

if ($rowMat['count'] > 0) {
    setFlashMessage('danger', 'La matricola contatore è già stata utilizzata.');
    header('Location: tecnico-contatori.php');
    exit;
}

// Se è una sostituzione, verifica che il contatore da sostituire esista ed sia ATTIVO
$contatoresostituito = null;
if ($isSostituzione) {
    $queryCheckSostituito = "SELECT IdContatore, matricola_contatore, stato_contatore
                             FROM CONTATORE
                             WHERE IdContatore = ?
                             LIMIT 1";
    
    $stmtCheckSost = mysqli_prepare($conn, $queryCheckSostituito);
    mysqli_stmt_bind_param($stmtCheckSost, 'i', $sostituisceIdContatore);
    mysqli_stmt_execute($stmtCheckSost);
    $resultCheckSost = mysqli_stmt_get_result($stmtCheckSost);
    
    if (mysqli_num_rows($resultCheckSost) === 0) {
        mysqli_stmt_close($stmtCheckSost);
        setFlashMessage('danger', 'Contatore da sostituire non trovato.');
        header('Location: tecnico-contatori.php');
        exit;
    }
    
    $contatoresostituito = mysqli_fetch_assoc($resultCheckSost);
    mysqli_stmt_close($stmtCheckSost);
    
    if ($contatoresostituito['stato_contatore'] !== 'ATTIVO') {
        setFlashMessage('danger', 'Il contatore da sostituire non è ATTIVO.');
        header('Location: tecnico-contatori.php');
        exit;
    }
}

// INIZIO TRANSAZIONE
mysqli_begin_transaction($conn);

try {
    // Inserisci il nuovo contatore
    // data_installazione verrà gestita automaticamente dal DEFAULT CURRENT_TIMESTAMP
    $statoContatore = 'ATTIVO';
    
    // Gestione NULL per sostituisce_IdContatore
    if ($sostituisceIdContatore === null || $sostituisceIdContatore === 0) {
        // Quando non c'è sostituzione
        $queryInsert = "INSERT INTO CONTATORE 
                        (matricola_contatore, marca_contatore, stato_contatore, IdFornitura, 
                         IdUtente_installatore, note, sostituisce_IdContatore)
                        VALUES (?, ?, ?, ?, ?, ?, NULL)";
        
        $stmt = mysqli_prepare($conn, $queryInsert);
        
        if ($stmt === false) {
            throw new Exception('Errore preparazione query: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, 'sssiis', 
            $matricolaContatore,
            $marcaContatore,
            $statoContatore,
            $idFornitura,
            $idUtenteInstallatore,
            $note
        );
    } else {
        // Quando c'è sostituzione
        $queryInsert = "INSERT INTO CONTATORE 
                        (matricola_contatore, marca_contatore, stato_contatore, IdFornitura, 
                         IdUtente_installatore, note, sostituisce_IdContatore)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $queryInsert);
        
        if ($stmt === false) {
            throw new Exception('Errore preparazione query: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, 'sssiisi', 
            $matricolaContatore,
            $marcaContatore,
            $statoContatore,
            $idFornitura,
            $idUtenteInstallatore,
            $note,
            $sostituisceIdContatore
        );
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Errore inserimento contatore: ' . mysqli_stmt_error($stmt));
    }
    
    $idContatore = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    // Se è una sostituzione, invoca la stored procedure per marcare il contatore vecchio
    if ($isSostituzione && $sostituisceIdContatore > 0) {
        $queryCallProc = "CALL imposta_contatore_sostituito(?)";
        $stmtProc = mysqli_prepare($conn, $queryCallProc);
        
        if ($stmtProc === false) {
            throw new Exception('Errore preparazione stored procedure: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmtProc, 'i', $sostituisceIdContatore);
        
        if (!mysqli_stmt_execute($stmtProc)) {
            throw new Exception('Errore esecuzione stored procedure: ' . mysqli_stmt_error($stmtProc));
        }
        
        // Recupera il risultato della stored procedure (opzionale, per logging)
        $resultProc = mysqli_stmt_get_result($stmtProc);
        if ($resultProc) {
            mysqli_free_result($resultProc);
        }
        
        mysqli_stmt_close($stmtProc);
    }
    
    // COMMIT TRANSAZIONE
    mysqli_commit($conn);
    
    // Log operazione
    $clienteNome = $fornitura['nome'] . ' ' . $fornitura['cognome'];
    $indirizzo = $fornitura['indirizzo_fornitura'];
    
    if ($isSostituzione) {
        $matricolaSostituito = $contatoresostituito['matricola_contatore'];
        logError("TECNICO $nomeInstallatore (ID: $idUtenteInstallatore) ha installato contatore #$idContatore (Matricola: $matricolaContatore) in SOSTITUZIONE del contatore #$sostituisceIdContatore (Matricola: $matricolaSostituito) - Fornitura #$idFornitura (Cliente: $clienteNome, Indirizzo: $indirizzo)", 'INFO');
        
        $messaggio = "Contatore #$idContatore installato con successo in sostituzione del contatore #$sostituisceIdContatore!";
    } else {
        logError("TECNICO $nomeInstallatore (ID: $idUtenteInstallatore) ha installato contatore #$idContatore (Matricola: $matricolaContatore) - Fornitura #$idFornitura (Cliente: $clienteNome, Indirizzo: $indirizzo)", 'INFO');
        
        $messaggio = "Contatore #$idContatore installato con successo!";
    }
    
    // Nota: il trigger after_contatore_insert attiverà automaticamente la fornitura se era "IN ATTESA DI ATTIVAZIONE"
    
    setFlashMessage('success', $messaggio . " La fornitura è stata attivata automaticamente.");
    header('Location: tecnico.php');
    
} catch (Exception $e) {
    // ROLLBACK in caso di errore
    mysqli_rollback($conn);
    
    setFlashMessage('danger', 'Errore durante l\'installazione del contatore: ' . $e->getMessage());
    logError('Errore installazione contatore per fornitura #' . $idFornitura . ': ' . $e->getMessage());
    header('Location: tecnico-contatori.php');
}

exit;
?>
