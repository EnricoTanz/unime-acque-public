<?php
/**
 * UNIME-ACQUE - Consumi Cliente
 * 
 * Visualizza i consumi delle forniture del cliente.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

startSecureSession();
requireRole('CLIENTE');

$userId = getCurrentUserId();
$conn = getDbConnection();

// Recupera le forniture attive dell'utente per il filtro
$queryForniture = "SELECT DISTINCT f.IdFornitura, f.indirizzo_fornitura, cnt.matricola_contatore
                   FROM FORNITURA f
                   INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
                   LEFT JOIN CONTATORE cnt ON f.IdFornitura = cnt.IdFornitura AND cnt.stato_contatore = 'ATTIVO'
                   WHERE c.IdUtente = ? AND f.stato_fornitura = 'ATTIVA'
                   ORDER BY f.indirizzo_fornitura";
$stmtForniture = mysqli_prepare($conn, $queryForniture);
mysqli_stmt_bind_param($stmtForniture, 'i', $userId);
mysqli_stmt_execute($stmtForniture);
$resultForniture = mysqli_stmt_get_result($stmtForniture);
$forniture = [];
while ($row = mysqli_fetch_assoc($resultForniture)) {
    $forniture[] = $row;
}
mysqli_stmt_close($stmtForniture);

$selectedFornitura = isset($_GET['fornitura']) ? (int)$_GET['fornitura'] : (count($forniture) > 0 ? $forniture[0]['IdFornitura'] : 0);

$consumi = [];
if ($selectedFornitura > 0) {
    $queryConsumi = "SELECT lc.*, cnt.matricola_contatore
                     FROM LETTURA_CONSUMI lc
                     INNER JOIN CONTATORE cnt ON lc.IdContatore = cnt.IdContatore
                     INNER JOIN FORNITURA f ON cnt.IdFornitura = f.IdFornitura
                     INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
                     WHERE c.IdUtente = ? AND f.IdFornitura = ?
                     ORDER BY lc.data_rif DESC
                     LIMIT 50";
    $stmtConsumi = mysqli_prepare($conn, $queryConsumi);
    mysqli_stmt_bind_param($stmtConsumi, 'ii', $userId, $selectedFornitura);
    mysqli_stmt_execute($stmtConsumi);
    $resultConsumi = mysqli_stmt_get_result($stmtConsumi);
    while ($row = mysqli_fetch_assoc($resultConsumi)) {
        $consumi[] = $row;
    }
    mysqli_stmt_close($stmtConsumi);
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Miei Consumi | UNIME-ACQUE</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
    
    <style>
        .page-content { padding-top: 100px; padding-bottom: 3rem; min-height: 100vh; }
        .back-link { display: inline-block; color: var(--color-accent); margin-bottom: 2rem; text-decoration: none; font-weight: 600; }
        .back-link:hover { color: var(--color-primary-light); }
        .page-header { margin-bottom: 2rem; }
        .filter-section { background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--color-text-light); }
        .form-select { width: 100%; padding: 0.75rem; border: 1px solid var(--glass-border); border-radius: 8px; background: rgba(255,255,255,0.05); color: var(--color-text); font-size: 1rem; }
        .table-container { background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 16px; padding: 1.5rem; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: rgba(5, 191, 219, 0.1); }
        th { padding: 1rem; text-align: left; font-weight: 600; color: var(--color-accent); border-bottom: 2px solid var(--glass-border); }
        td { padding: 1rem; border-bottom: 1px solid var(--glass-border); color: var(--color-text-light); }
        tr:hover { background: rgba(5, 191, 219, 0.05); }
        .empty-state { text-align: center; padding: 3rem; color: var(--color-text-muted); }
        .empty-state .icon { font-size: 4rem; margin-bottom: 1rem; }
        .badge-rettificato { background: rgba(241, 196, 15, 0.2); color: #f1c40f; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
    </style>
</head>
<body>
    <header class="main-header scrolled" style="position: fixed;">
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="logo">
                    <span class="logo-company">Ecelesti S.p.A.</span>
                    <span class="logo-brand">UNIME<span>-ACQUE</span></span>
                </a>
                <nav class="main-nav">
                    <ul class="nav-menu">
                        <li><a href="cliente.php">Dashboard</a></li>
                        <li><a href="../auth/logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="page-content">
        <div class="container">
            <a href="cliente.php" class="back-link">← Torna alla Dashboard</a>
            
            <div class="page-header">
                <h1>📊 I Miei Consumi</h1>
                <p class="subtitle">Consulta lo storico dei consumi delle tue forniture</p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($forniture)): ?>
                <div class="table-container">
                    <div class="empty-state">
                        <div class="icon">🏠</div>
                        <h3>Nessuna Fornitura Attiva</h3>
                        <p>Non hai forniture attive al momento.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="filter-section">
                    <form method="GET" id="filterForm">
                        <div class="form-group">
                            <label for="fornitura">Seleziona Fornitura</label>
                            <select id="fornitura" name="fornitura" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                <?php foreach ($forniture as $f): ?>
                                    <option value="<?php echo $f['IdFornitura']; ?>" <?php echo $f['IdFornitura'] == $selectedFornitura ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($f['indirizzo_fornitura']); ?>
                                        <?php if ($f['matricola_contatore']): ?>
                                            - Contatore: <?php echo htmlspecialchars($f['matricola_contatore']); ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                
                <div class="table-container">
                    <?php if (empty($consumi)): ?>
                        <div class="empty-state">
                            <div class="icon">📊</div>
                            <h3>Nessun Consumo Registrato</h3>
                            <p>Non ci sono letture disponibili per questa fornitura.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Data Lettura</th>
                                    <th>Contatore</th>
                                    <th>Consumo (m³)</th>
                                    <th>Consumo Rettificato</th>
                                    <th>Data Rettifica</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($consumi as $consumo): ?>
                                    <tr>
                                        <td><strong><?php echo date('d/m/Y', strtotime($consumo['data_rif'])); ?></strong></td>
                                        <td><?php echo htmlspecialchars($consumo['matricola_contatore']); ?></td>
                                        <td><?php echo number_format($consumo['vol_consumato'], 0); ?> m³</td>
                                        <td>
                                            <?php if ($consumo['vol_rettificato']): ?>
                                                <span class="badge-rettificato"><?php echo number_format($consumo['vol_rettificato'], 0); ?> m³</span>
                                            <?php else: ?>
                                                <span style="color: var(--color-text-muted);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $consumo['data_rettifica'] ? date('d/m/Y H:i', strtotime($consumo['data_rettifica'])) : '-'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
