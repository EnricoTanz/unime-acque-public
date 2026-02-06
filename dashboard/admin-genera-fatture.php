<?php
/**
 * UNIME-ACQUE - Genera Fatture Mensili Manualmente
 * 
 * Chiama la stored procedure genera_fatture_mensili().
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
    header('Location: admin-fatture.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Richiesta non valida.');
    header('Location: admin-fatture.php');
    exit;
}

$conn = getDbConnection();

if ($conn === null) {
    setFlashMessage('danger', 'Errore di connessione al database.');
    header('Location: admin-fatture.php');
    exit;
}

// Chiama la stored procedure genera_fatture_mensili
$query = "CALL genera_fatture_mensili()";
$result = mysqli_query($conn, $query);

if ($result) {
    // Conta quante fatture sono state generate (quelle emesse oggi)
    $queryCount = "SELECT COUNT(*) as count 
                   FROM FATTURA 
                   WHERE data_emissione = CURRENT_DATE";
    $resultCount = mysqli_query($conn, $queryCount);
    
    $fattureGenerate = 0;
    if ($row = mysqli_fetch_assoc($resultCount)) {
        $fattureGenerate = $row['count'];
    }
    
    // Log operazione
    $adminName = getCurrentUserName();
    $adminId = getCurrentUserId();
    logError("ADMIN $adminName (ID: $adminId) ha generato manualmente {$fattureGenerate} fatture mensili", 'INFO');
    
    if ($fattureGenerate > 0) {
        setFlashMessage('success', "Fatture generate con successo! Totale: {$fattureGenerate} fatture emesse.");
    } else {
        setFlashMessage('warning', 'Procedura eseguita ma nessuna fattura è stata generata. Verifica che ci siano contratti attivi con forniture attive e consumi registrati.');
    }
} else {
    $error = mysqli_error($conn);
    setFlashMessage('danger', 'Errore durante la generazione delle fatture: ' . $error);
    logError('Errore generazione fatture mensili: ' . $error);
}

header('Location: admin-fatture.php');
exit;
?>
