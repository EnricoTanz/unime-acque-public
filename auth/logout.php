<?php
/**
 * UNIME-ACQUE - Logout
 * 
 * Termina la sessione utente e reindirizza alla homepage.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

// Include dipendenze
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Avvia la sessione
startSecureSession();

// Log del logout (se loggato)
if (isLoggedIn()) {
    $userId = getCurrentUserId();
    $userEmail = $_SESSION['user_email'] ?? 'unknown';
    logError("Logout utente ID {$userId} ({$userEmail})", 'INFO');
}

// Effettua il logout
logoutUser();

// Imposta messaggio di conferma
session_start();
setFlashMessage('success', 'Logout effettuato con successo. A presto!');

// Reindirizza alla homepage
header('Location: ../index.php');
exit;
?>
