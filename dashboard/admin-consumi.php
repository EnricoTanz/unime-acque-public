<?php
/**
 * UNIME-ACQUE - Gestione Consumi (AMMINISTRATORE)
 * 
 * Seleziona un contratto e visualizza/rettifica i consumi.
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

$userId = getCurrentUserId();
$userName = getCurrentUserName();

$conn = getDbConnection();

// Recupera lista utenti con contratti
$queryUtenti = "SELECT DISTINCT u.IdUtente, u.nome, u.cognome, u.email
                FROM UTENTE u
                INNER JOIN CONTRATTO c ON u.IdUtente = c.IdUtente
                WHERE u.ruolo = 'CLIENTE'
                ORDER BY u.cognome, u.nome";
$resultUtenti = mysqli_query($conn, $queryUtenti);

$utenti = [];
while ($row = mysqli_fetch_assoc($resultUtenti)) {
    $utenti[] = $row;
}

// Variabili per dati selezionati
$selectedUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$selectedContractId = isset($_GET['contract_id']) ? (int)$_GET['contract_id'] : 0;

$contratti = [];
$consumi = [];
$fornituraInfo = null;
$contatoreInfo = null;

// Se è selezionato un utente, carica i suoi contratti
if ($selectedUserId > 0) {
    $queryContratti = "SELECT c.IdContratto, c.data_stipula, c.tipo_contratto, c.stato_contratto,
                              f.IdFornitura, f.indirizzo_fornitura, f.stato_fornitura
                       FROM CONTRATTO c
                       LEFT JOIN FORNITURA f ON c.IdContratto = f.IdContratto
                       WHERE c.IdUtente = ?
                       ORDER BY c.IdContratto DESC";
    $stmtContratti = mysqli_prepare($conn, $queryContratti);
    mysqli_stmt_bind_param($stmtContratti, 'i', $selectedUserId);
    mysqli_stmt_execute($stmtContratti);
    $resultContratti = mysqli_stmt_get_result($stmtContratti);
    
    while ($row = mysqli_fetch_assoc($resultContratti)) {
        $contratti[] = $row;
    }
    mysqli_stmt_close($stmtContratti);
}

// Se è selezionato un contratto, carica i consumi
if ($selectedContractId > 0 && $selectedUserId > 0) {
    // Recupera info fornitura
    $queryFornitura = "SELECT f.*, cnt.IdContatore, cnt.matricola_contatore
                       FROM FORNITURA f
                       LEFT JOIN CONTATORE cnt ON f.IdFornitura = cnt.IdFornitura AND cnt.stato_contatore = 'ATTIVO'
                       WHERE f.IdContratto = ?
                       LIMIT 1";
    $stmtFornitura = mysqli_prepare($conn, $queryFornitura);
    mysqli_stmt_bind_param($stmtFornitura, 'i', $selectedContractId);
    mysqli_stmt_execute($stmtFornitura);
    $resultFornitura = mysqli_stmt_get_result($stmtFornitura);
    $fornituraInfo = mysqli_fetch_assoc($resultFornitura);
    mysqli_stmt_close($stmtFornitura);
    
    // Se c'è un contatore attivo, carica i consumi
    if ($fornituraInfo && $fornituraInfo['IdContatore']) {
        $queryConsumi = "SELECT * FROM LETTURA_CONSUMI 
                         WHERE IdContatore = ?
                         ORDER BY data_rif DESC
                         LIMIT 50";
        $stmtConsumi = mysqli_prepare($conn, $queryConsumi);
        mysqli_stmt_bind_param($stmtConsumi, 'i', $fornituraInfo['IdContatore']);
        mysqli_stmt_execute($stmtConsumi);
        $resultConsumi = mysqli_stmt_get_result($stmtConsumi);
        
        while ($row = mysqli_fetch_assoc($resultConsumi)) {
            $consumi[] = $row;
        }
        mysqli_stmt_close($stmtConsumi);
    }
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Consumi | UNIME-ACQUE</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
    
    <style>
        .page-content {
            padding-top: 100px;
            padding-bottom: 3rem;
            min-height: 100vh;
        }
        
        .back-link {
            display: inline-block;
            color: var(--color-accent);
            margin-bottom: 2rem;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            color: var(--color-primary-light);
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .page-header .subtitle {
            color: var(--color-text-muted);
        }
        
        .filter-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        
        .form-group {
            margin: 0;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--color-text-light);
            font-size: 0.9rem;
        }
        
        .form-select {
            width: 100%;
            padding: 1rem 1.25rem;
            font-family: var(--font-body);
            font-size: 1rem;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2305bfdb' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 3rem;
        }
        
        .form-select option {
            background: #0a4d68;
            color: #ffffff;
            padding: 1rem;
        }
        
        .btn-filter {
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .btn-filter:hover {
            transform: translateY(-2px);
        }
        
        .info-box {
            background: rgba(5, 191, 219, 0.1);
            border: 1px solid var(--color-primary-light);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-box h3 {
            color: var(--color-primary-light);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }
        
        .info-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 0.75rem;
            border-radius: 8px;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: var(--color-text-muted);
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            color: var(--color-text-light);
            font-weight: 600;
        }
        
        .table-container {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.5rem;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: rgba(5, 191, 219, 0.1);
        }
        
        th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--color-accent);
            border-bottom: 2px solid var(--glass-border);
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid var(--glass-border);
            color: var(--color-text-light);
        }
        
        tr:hover {
            background: rgba(5, 191, 219, 0.05);
        }
        
        .badge-rettificato {
            background: rgba(255, 193, 7, 0.2);
            color: var(--color-warning);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all var(--transition-fast);
            border: none;
            cursor: pointer;
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--color-text-muted);
        }
        
        .empty-state .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        @media screen and (max-width: 992px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
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
                        <li><a href="amministratore.php">Dashboard</a></li>
                        <li><a href="../auth/logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="page-content">
        <div class="container">
            
            <a href="amministratore.php" class="back-link">← Torna alla Dashboard</a>
            
            <div class="page-header">
                <h1>📊 Gestione Consumi</h1>
                <p class="subtitle">Visualizza e rettifica i consumi dei contratti</p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>" style="margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Filtri -->
            <div class="filter-card">
                <form method="GET" action="admin-consumi.php">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label class="form-label" for="user_id">Cliente</label>
                            <select id="user_id" name="user_id" class="form-select" required onchange="this.form.submit()">
                                <option value="">-- Seleziona Cliente --</option>
                                <?php foreach ($utenti as $utente): ?>
                                    <option value="<?php echo $utente['IdUtente']; ?>" 
                                            <?php echo $selectedUserId == $utente['IdUtente'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($utente['cognome'] . ' ' . $utente['nome'] . ' (' . $utente['email'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="contract_id">Contratto</label>
                            <select id="contract_id" name="contract_id" class="form-select" 
                                    <?php echo empty($contratti) ? 'disabled' : ''; ?>>
                                <option value="">-- Seleziona Contratto --</option>
                                <?php foreach ($contratti as $contratto): ?>
                                    <option value="<?php echo $contratto['IdContratto']; ?>"
                                            <?php echo $selectedContractId == $contratto['IdContratto'] ? 'selected' : ''; ?>>
                                        Contratto #<?php echo $contratto['IdContratto']; ?> - 
                                        <?php echo htmlspecialchars($contratto['indirizzo_fornitura'] ?? 'Nessuna fornitura'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn-filter">Visualizza</button>
                    </div>
                </form>
            </div>
            
            <!-- Info Fornitura/Contatore -->
            <?php if ($fornituraInfo): ?>
                <div class="info-box">
                    <h3>ℹ️ Informazioni Fornitura</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Indirizzo</div>
                            <div class="info-value"><?php echo htmlspecialchars($fornituraInfo['indirizzo_fornitura']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Stato Fornitura</div>
                            <div class="info-value"><?php echo htmlspecialchars($fornituraInfo['stato_fornitura']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Contatore</div>
                            <div class="info-value">
                                <?php echo $fornituraInfo['matricola_contatore'] ? 
                                    htmlspecialchars($fornituraInfo['matricola_contatore']) : 
                                    'Nessun contatore attivo'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Tabella Consumi -->
            <?php if ($selectedContractId > 0): ?>
                <div class="table-container">
                    <?php if (empty($consumi)): ?>
                        <div class="empty-state">
                            <div class="icon">📭</div>
                            <h3>Nessun Consumo Registrato</h3>
                            <p>Non ci sono letture disponibili per questo contratto.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Data Riferimento</th>
                                    <th>Consumo (m³)</th>
                                    <th>Consumo Rettificato</th>
                                    <th>Data Rettifica</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($consumi as $consumo): ?>
                                    <tr>
                                        <td><strong><?php echo date('d/m/Y', strtotime($consumo['data_rif'])); ?></strong></td>
                                        <td><?php echo number_format($consumo['vol_consumato'], 0); ?> m³</td>
                                        <td>
                                            <?php if ($consumo['vol_rettificato']): ?>
                                                <span class="badge-rettificato">
                                                    <?php echo number_format($consumo['vol_rettificato'], 0); ?> m³
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--color-text-muted);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $consumo['data_rettifica'] ? 
                                                date('d/m/Y H:i', strtotime($consumo['data_rettifica'])) : 
                                                '<span style="color: var(--color-text-muted);">-</span>'; ?>
                                        </td>
                                        <td>
                                            <button class="btn-edit" 
                                                    onclick="openEditModal(<?php echo $fornituraInfo['IdContatore']; ?>, 
                                                                          '<?php echo $consumo['data_rif']; ?>', 
                                                                          <?php echo $consumo['vol_consumato']; ?>, 
                                                                          <?php echo $consumo['vol_rettificato'] ?? 'null'; ?>)">
                                                Rettifica
                                            </button>
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
    
    <!-- Modal Rettifica -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: var(--color-bg-dark); padding: 2rem; border-radius: 16px; max-width: 500px; width: 90%; border: 1px solid var(--glass-border);">
            <h2 style="color: var(--color-text-light); margin-bottom: 1.5rem;">Rettifica Consumo</h2>
            
            <form method="POST" action="admin-consumi-rettifica.php">
                <?php echo csrfField(); ?>
                <input type="hidden" name="id_contatore" id="modal_id_contatore">
                <input type="hidden" name="data_rif" id="modal_data_rif">
                <input type="hidden" name="user_id" value="<?php echo $selectedUserId; ?>">
                <input type="hidden" name="contract_id" value="<?php echo $selectedContractId; ?>">
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--color-text-light); font-weight: 600;">
                        Data Riferimento
                    </label>
                    <input type="text" id="modal_data_display" readonly 
                           style="width: 100%; padding: 1rem; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: 8px; color: var(--color-text-light);">
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--color-text-light); font-weight: 600;">
                        Consumo Originale (m³)
                    </label>
                    <input type="text" id="modal_vol_originale" readonly 
                           style="width: 100%; padding: 1rem; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: 8px; color: var(--color-text-light);">
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--color-text-light); font-weight: 600;">
                        Nuovo Consumo Rettificato (m³) <span style="color: var(--color-danger);">*</span>
                    </label>
                    <input type="number" name="vol_rettificato" id="modal_vol_rettificato" required min="0" step="1"
                           style="width: 100%; padding: 1rem; background: rgba(255,255,255,0.08); border: 1px solid var(--color-primary-light); border-radius: 8px; color: var(--color-text-light); font-size: 1rem;">
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="button" onclick="closeEditModal()" 
                            style="flex: 1; padding: 1rem; background: transparent; border: 1px solid var(--glass-border); border-radius: 8px; color: var(--color-text-muted); font-weight: 600; cursor: pointer;">
                        Annulla
                    </button>
                    <button type="submit" 
                            style="flex: 1; padding: 1rem; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%); border: none; border-radius: 8px; color: white; font-weight: 600; cursor: pointer;">
                        Salva Rettifica
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openEditModal(idContatore, dataRif, volOriginale, volRettificato) {
            document.getElementById('modal_id_contatore').value = idContatore;
            document.getElementById('modal_data_rif').value = dataRif;
            document.getElementById('modal_data_display').value = new Date(dataRif).toLocaleDateString('it-IT');
            document.getElementById('modal_vol_originale').value = volOriginale;
            document.getElementById('modal_vol_rettificato').value = volRettificato || volOriginale;
            
            const modal = document.getElementById('editModal');
            modal.style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Chiudi modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
