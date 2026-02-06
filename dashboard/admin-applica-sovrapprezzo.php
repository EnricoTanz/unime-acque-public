<?php
/**
 * UNIME-ACQUE - Applica Sovrapprezzo a Fatture Scadute
 * 
 * Chiama la stored procedure applica_sovrapprezzo_fatture_scadute().
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

// Chiama la stored procedure applica_sovrapprezzo_fatture_scadute
$query = "CALL applica_sovrapprezzo_fatture_scadute()";
$result = mysqli_query($conn, $query);

if ($result !== false) {
    // Conta quante fatture hanno ricevuto il sovrapprezzo
    // Verifichiamo manualmente dal database
    $queryCount = "SELECT COUNT(*) as count 
                   FROM FATTURA 
                   WHERE data_scadenza < CURRENT_DATE 
                     AND data_pagamento IS NULL 
                     AND sovrapprezzo > 0";
    $resultCount = mysqli_query($conn, $queryCount);
    
    $fattureAggiornate = 0;
    if ($resultCount && $row = mysqli_fetch_assoc($resultCount)) {
        $fattureAggiornate = $row['count'];
    }
    
    // Log operazione
    $adminName = getCurrentUserName();
    $adminId = getCurrentUserId();
    logError("ADMIN $adminName (ID: $adminId) ha applicato sovrapprezzo alle fatture scadute. Fatture con sovrapprezzo: $fattureAggiornate", 'INFO');
    
    if ($fattureAggiornate > 0) {
        setFlashMessage('success', "Sovrapprezzo applicato con successo! Totale fatture scadute con sovrapprezzo: {$fattureAggiornate}");
    } else {
        setFlashMessage('warning', 'Procedura eseguita ma nessuna fattura scaduta trovata o il sovrapprezzo è già stato applicato a tutte.');
    }
} else {
    $error = mysqli_error($conn);
    setFlashMessage('danger', 'Errore durante l\'applicazione del sovrapprezzo: ' . $error);
    logError('Errore applicazione sovrapprezzo fatture scadute: ' . $error);
}

header('Location: admin-fatture.php');
exit;
?>
