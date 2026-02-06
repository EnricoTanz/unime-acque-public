<?php
/**
 * UNIME-ACQUE - Gestione Contratti (AMMINISTRATORE) - MODIFICATO
 * 
 * Visualizza contratti esistenti, permette di crearne di nuovi e aggiungere tariffe.
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

// Recupera tutti i contratti ATTIVI con informazioni utente e tariffa più bassa attiva
$queryAttivi = "SELECT c.*, 
                 u.nome, u.cognome, u.email,
                 f.IdFornitura, f.indirizzo_fornitura, f.stato_fornitura,
                 (SELECT MIN(t.tariffa_applicata)
                  FROM ABBINAMENTO_TARIFFA at
                  INNER JOIN TARIFFA t ON at.IdTariffa = t.IdTariffa
                  WHERE at.IdContratto = c.IdContratto
                    AND at.data_inizio <= CURRENT_DATE
                    AND (at.data_fine IS NULL OR at.data_fine > CURRENT_DATE)
                 ) as tariffa_piu_bassa,
                 (SELECT t2.nome_tariffa
                  FROM ABBINAMENTO_TARIFFA at2
                  INNER JOIN TARIFFA t2 ON at2.IdTariffa = t2.IdTariffa
                  WHERE at2.IdContratto = c.IdContratto
                    AND at2.data_inizio <= CURRENT_DATE
                    AND (at2.data_fine IS NULL OR at2.data_fine > CURRENT_DATE)
                  ORDER BY t2.tariffa_applicata ASC
                  LIMIT 1
                 ) as nome_tariffa_piu_bassa,
                 (SELECT COUNT(*)
                  FROM ABBINAMENTO_TARIFFA at3
                  WHERE at3.IdContratto = c.IdContratto
                 ) as totale_tariffe
          FROM CONTRATTO c
          INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
          LEFT JOIN FORNITURA f ON c.IdContratto = f.IdContratto
          WHERE c.stato_contratto = 'ATTIVO'
          ORDER BY c.IdContratto DESC";

$resultAttivi = mysqli_query($conn, $queryAttivi);

$contrattiAttivi = [];
while ($row = mysqli_fetch_assoc($resultAttivi)) {
    $contrattiAttivi[] = $row;
}

// Recupera tutti i contratti CESSATI
$queryCessati = "SELECT c.*, 
                 u.nome, u.cognome, u.email,
                 f.IdFornitura, f.indirizzo_fornitura, f.stato_fornitura,
                 (SELECT MIN(t.tariffa_applicata)
                  FROM ABBINAMENTO_TARIFFA at
                  INNER JOIN TARIFFA t ON at.IdTariffa = t.IdTariffa
                  WHERE at.IdContratto = c.IdContratto
                 ) as tariffa_piu_bassa,
                 (SELECT t2.nome_tariffa
                  FROM ABBINAMENTO_TARIFFA at2
                  INNER JOIN TARIFFA t2 ON at2.IdTariffa = t2.IdTariffa
                  WHERE at2.IdContratto = c.IdContratto
                  ORDER BY t2.tariffa_applicata ASC
                  LIMIT 1
                 ) as nome_tariffa_piu_bassa,
                 (SELECT COUNT(*)
                  FROM ABBINAMENTO_TARIFFA at3
                  WHERE at3.IdContratto = c.IdContratto
                 ) as totale_tariffe
          FROM CONTRATTO c
          INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
          LEFT JOIN FORNITURA f ON c.IdContratto = f.IdContratto
          WHERE c.stato_contratto = 'CESSATO'
          ORDER BY c.data_fine_validita DESC";

$resultCessati = mysqli_query($conn, $queryCessati);

$contrattiCessati = [];
while ($row = mysqli_fetch_assoc($resultCessati)) {
    $contrattiCessati[] = $row;
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Contratti | UNIME-ACQUE</title>
    
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
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .page-header .subtitle {
            color: var(--color-text-muted);
        }
        
        .btn-new-contratto {
            display: inline-block;
            background: var(--gradient-primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 2rem;
            transition: all var(--transition-fast);
        }
        
        .btn-new-contratto:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow);
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
            white-space: nowrap;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid var(--glass-border);
            color: var(--color-text-light);
        }
        
        tr:hover {
            background: rgba(5, 191, 219, 0.05);
        }
        
        .badge-attivo {
            background: rgba(46, 204, 113, 0.2);
            color: var(--color-success);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-cessato {
            background: rgba(231, 76, 60, 0.2);
            color: var(--color-danger);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-count {
            background: rgba(5, 191, 219, 0.2);
            color: var(--color-accent);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .btn-small {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all var(--transition-fast);
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-aggiungi-tariffa {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        
        .btn-aggiungi-tariffa:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
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
        
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--glass-border);
        }
        
        .tab {
            padding: 1rem 2rem;
            background: transparent;
            border: none;
            color: var(--color-text-muted);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            border-bottom: 3px solid transparent;
        }
        
        .tab:hover {
            color: var(--color-text);
        }
        
        .tab.active {
            color: var(--color-accent);
            border-bottom-color: var(--color-accent);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }
        
        .alert-danger {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }
        
        @media screen and (max-width: 1200px) {
            table {
                font-size: 0.85rem;
            }
            
            th, td {
                padding: 0.75rem 0.5rem;
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
                <h1>📄 Gestione Contratti</h1>
                <p class="subtitle">Crea nuovi contratti e abbina tariffe ai clienti</p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <a href="admin-contratti-nuovo.php" class="btn-new-contratto">
                ➕ Crea Nuovo Contratto
            </a>
            
            <!-- Tabs -->
            <div class="tabs">
                <button class="tab active" onclick="showTab('attivi')">
                    Contratti Attivi (<?php echo count($contrattiAttivi); ?>)
                </button>
                <button class="tab" onclick="showTab('cessati')">
                    Contratti Cessati (<?php echo count($contrattiCessati); ?>)
                </button>
            </div>
            
            <!-- Tab Contratti Attivi -->
            <div id="tab-attivi" class="tab-content active">
                <div class="table-container">
                    <?php if (empty($contrattiAttivi)): ?>
                        <div class="empty-state">
                            <div class="icon">📭</div>
                            <h3>Nessun Contratto Attivo</h3>
                            <p>Non ci sono contratti attivi al momento.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Stato</th>
                                    <th>Data Stipula</th>
                                    <th>Validità</th>
                                    <th>Tariffa Applicata</th>
                                    <th>Fornitura</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contrattiAttivi as $contratto): ?>
                                    <tr>
                                        <td><strong>#<?php echo htmlspecialchars($contratto['IdContratto']); ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($contratto['nome'] . ' ' . $contratto['cognome']); ?><br>
                                            <small style="color: var(--color-text-muted);"><?php echo htmlspecialchars($contratto['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($contratto['tipo_contratto']); ?></td>
                                        <td>
                                            <span class="badge-attivo">ATTIVO</span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($contratto['data_stipula'])); ?></td>
                                        <td>
                                            <small>
                                                Dal <?php echo date('d/m/Y', strtotime($contratto['data_inizio_validita'])); ?><br>
                                                Illimitata
                                            </small>
                                        </td>
                                        <td>
                                            <?php if (!is_null($contratto['tariffa_piu_bassa'])): ?>
                                                <strong style="color: var(--color-accent);">
                                                    € <?php echo number_format($contratto['tariffa_piu_bassa'], 4, ',', '.'); ?>/m³
                                                </strong><br>
                                                <small style="color: var(--color-text-muted);">
                                                    <?php echo htmlspecialchars($contratto['nome_tariffa_piu_bassa']); ?>
                                                </small><br>
                                                <span class="badge-count">
                                                    <?php echo $contratto['totale_tariffe']; ?> tariff<?php echo $contratto['totale_tariffe'] == 1 ? 'a' : 'e'; ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--color-text-muted);">Nessuna tariffa attiva</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!is_null($contratto['IdFornitura'])): ?>
                                                <strong>#<?php echo htmlspecialchars($contratto['IdFornitura']); ?></strong><br>
                                                <?php if ($contratto['stato_fornitura'] === 'ATTIVA'): ?>
                                                    <span class="badge-attivo">ATTIVA</span>
                                                <?php else: ?>
                                                    <span class="badge-cessato">NON ATTIVA</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span style="color: var(--color-text-muted);">Nessuna</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="admin-contratti-aggiungi-tariffa.php?id=<?php echo $contratto['IdContratto']; ?>" 
                                               class="btn-small btn-aggiungi-tariffa">
                                                ➕ Tariffa
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Tab Contratti Cessati -->
            <div id="tab-cessati" class="tab-content">
                <div class="table-container">
                    <?php if (empty($contrattiCessati)): ?>
                        <div class="empty-state">
                            <div class="icon">✅</div>
                            <h3>Nessun Contratto Cessato</h3>
                            <p>Non ci sono contratti cessati.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Data Stipula</th>
                                    <th>Data Cessazione</th>
                                    <th>Tariffa</th>
                                    <th>Fornitura</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contrattiCessati as $contratto): ?>
                                    <tr>
                                        <td><strong>#<?php echo htmlspecialchars($contratto['IdContratto']); ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($contratto['nome'] . ' ' . $contratto['cognome']); ?><br>
                                            <small style="color: var(--color-text-muted);"><?php echo htmlspecialchars($contratto['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($contratto['tipo_contratto']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($contratto['data_stipula'])); ?></td>
                                        <td>
                                            <?php if ($contratto['data_fine_validita']): ?>
                                                <strong style="color: var(--color-danger);">
                                                    <?php echo date('d/m/Y', strtotime($contratto['data_fine_validita'])); ?>
                                                </strong>
                                            <?php else: ?>
                                                <span style="color: var(--color-text-muted);">N/D</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!is_null($contratto['tariffa_piu_bassa'])): ?>
                                                € <?php echo number_format($contratto['tariffa_piu_bassa'], 4, ',', '.'); ?>/m³<br>
                                                <small style="color: var(--color-text-muted);">
                                                    <?php echo htmlspecialchars($contratto['nome_tariffa_piu_bassa']); ?>
                                                </small>
                                            <?php else: ?>
                                                <span style="color: var(--color-text-muted);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!is_null($contratto['IdFornitura'])): ?>
                                                #<?php echo htmlspecialchars($contratto['IdFornitura']); ?><br>
                                                <small style="color: var(--color-text-muted);"><?php echo htmlspecialchars($contratto['stato_fornitura']); ?></small>
                                            <?php else: ?>
                                                <span style="color: var(--color-text-muted);">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </main>
    
    <script>
        function showTab(tabName) {
            // Nascondi tutti i contenuti
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Rimuovi classe active da tutti i tab
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Mostra contenuto selezionato
            document.getElementById('tab-' + tabName).classList.add('active');
            
            // Attiva tab selezionato
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
