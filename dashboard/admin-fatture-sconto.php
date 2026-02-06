<?php
/**
 * UNIME-ACQUE - Applica Sconto Fattura
 * 
 * Chiama la stored procedure applica_sconto_fattura.
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

$idFattura = isset($_POST['id_fattura']) ? (int)$_POST['id_fattura'] : 0;
$percentualeSconto = isset($_POST['percentuale_sconto']) ? (float)$_POST['percentuale_sconto'] : 0;

// Validazione
if ($idFattura <= 0) {
    setFlashMessage('danger', 'ID fattura non valido.');
    header('Location: admin-fatture.php');
    exit;
}

if ($percentualeSconto < 0 || $percentualeSconto > 100) {
    setFlashMessage('danger', 'La percentuale di sconto deve essere tra 0 e 100.');
    header('Location: admin-fatture.php');
    exit;
}

$conn = getDbConnection();

if ($conn === null) {
    setFlashMessage('danger', 'Errore di connessione al database.');
    header('Location: admin-fatture.php');
    exit;
}

// Chiama la stored procedure applica_sconto_fattura
$query = "CALL applica_sconto_fattura(?, ?)";
$stmt = mysqli_prepare($conn, $query);

if ($stmt === false) {
    setFlashMessage('danger', 'Errore nella preparazione della query.');
    logError('Errore preparazione stored procedure applica_sconto_fattura: ' . mysqli_error($conn));
    header('Location: admin-fatture.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 'id', $idFattura, $percentualeSconto);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    // La procedura restituisce un result set, lo leggiamo
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $adminName = getCurrentUserName();
        $adminId = getCurrentUserId();
        logError("ADMIN $adminName (ID: $adminId) ha applicato sconto del {$percentualeSconto}% alla fattura #$idFattura", 'INFO');
        setFlashMessage('success', 'Sconto applicato con successo! Nuovo importo: € ' . number_format($row['Nuovo importo (€)'], 2, ',', '.'));
    } else {
        setFlashMessage('success', 'Sconto applicato con successo!');
    }
} else {
    $error = mysqli_stmt_error($stmt);
    
    // Gestisci errori specifici dalla stored procedure
    if (strpos($error, 'Fattura non trovata') !== false) {
        setFlashMessage('danger', 'Fattura non trovata.');
    } elseif (strpos($error, 'già pagata') !== false) {
        setFlashMessage('danger', 'Impossibile applicare sconto a fattura già pagata.');
    } elseif (strpos($error, 'già uno sconto') !== false) {
        setFlashMessage('danger', 'La fattura ha già uno sconto applicato.');
    } else {
        setFlashMessage('danger', 'Errore durante l\'applicazione dello sconto: ' . $error);
    }
    
    logError('Errore applicazione sconto fattura #' . $idFattura . ': ' . $error);
}

mysqli_stmt_close($stmt);

header('Location: admin-fatture.php');
exit;
?>
