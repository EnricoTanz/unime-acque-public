<?php
/**
 * UNIME-ACQUE - Get Aree per CAP (AJAX)
 * 
 * Endpoint per recuperare le aree geografiche filtrate per CAP.
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

header('Content-Type: application/json');

// Recupera CAP dalla query string
$cap = isset($_GET['cap']) ? trim($_GET['cap']) : '';

if (empty($cap)) {
    echo json_encode(['success' => false, 'error' => 'CAP non specificato']);
    exit;
}

// Validazione CAP (5 cifre)
if (!preg_match('/^[0-9]{5}$/', $cap)) {
    echo json_encode(['success' => false, 'error' => 'CAP non valido']);
    exit;
}

$conn = getDbConnection();

if ($conn === null) {
    echo json_encode(['success' => false, 'error' => 'Errore di connessione al database']);
    exit;
}

// Recupera aree geografiche per il CAP specificato
$query = "SELECT IdArea, nome_area, costo_acqua 
          FROM AREA_GEOGRAFICA 
          WHERE CAP = ? 
          ORDER BY nome_area";

$stmt = mysqli_prepare($conn, $query);

if ($stmt === false) {
    echo json_encode(['success' => false, 'error' => 'Errore nella preparazione della query']);
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $cap);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$aree = [];
while ($row = mysqli_fetch_assoc($result)) {
    $aree[] = [
        'IdArea' => $row['IdArea'],
        'nome_area' => $row['nome_area'],
        'costo_acqua' => $row['costo_acqua']
    ];
}

mysqli_stmt_close($stmt);

echo json_encode([
    'success' => true,
    'aree' => $aree,
    'count' => count($aree)
]);
?>
