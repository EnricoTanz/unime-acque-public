<?php
/**
 * UNIME-ACQUE - Gestione Fatture (AMMINISTRATORE)
 * 
 * Visualizza fatture non pagate, applica sconti, genera fatture e applica sovrapprezzo.
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

// Recupera fatture non pagate
$query = "SELECT f.*, 
                 u.nome, u.cognome, u.email,
                 c.IdContratto
          FROM FATTURA f
          INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
          INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
          WHERE f.data_pagamento IS NULL
          ORDER BY f.data_scadenza ASC, f.IdFattura DESC";

$result = mysqli_query($conn, $query);

$fatture = [];
while ($row = mysqli_fetch_assoc($result)) {
    $fatture[] = $row;
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Fatture | UNIME-ACQUE</title>
    
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .page-header-content h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .page-header-content .subtitle {
            color: var(--color-text-muted);
        }
        
        .page-header-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-genera-fatture {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }
        
        .btn-genera-fatture:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
        }
        
        .btn-genera-fatture:active {
            transform: translateY(0);
        }
        
        .btn-sovrapprezzo {
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }
        
        .btn-sovrapprezzo:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(230, 126, 34, 0.4);
        }
        
        .btn-sovrapprezzo:active {
            transform: translateY(0);
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
        
        .badge-scaduta {
            background: rgba(220, 53, 69, 0.2);
            color: var(--color-danger);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-sconto {
            background: rgba(0, 200, 151, 0.2);
            color: var(--color-success);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-sovrapprezzo {
            background: rgba(230, 126, 34, 0.2);
            color: #e67e22;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .btn-sconto {
            background: linear-gradient(135deg, var(--color-success) 0%, #00b881 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .btn-sconto:hover {
            transform: translateY(-2px);
        }
        
        .btn-sconto:disabled {
            background: #666;
            cursor: not-allowed;
            opacity: 0.5;
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
        
        .alert-warning {
            background: rgba(241, 196, 15, 0.2);
            color: #f1c40f;
            border: 1px solid #f1c40f;
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
        
        @media screen and (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .page-header-actions {
                width: 100%;
                flex-direction: column;
            }
            
            .btn-genera-fatture,
            .btn-sovrapprezzo {
                width: 100%;
                justify-content: center;
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
                <div class="page-header-content">
                    <h1>💰 Gestione Fatture</h1>
                    <p class="subtitle">Visualizza fatture non pagate, applica sconti e genera fatture</p>
                </div>
                <div class="page-header-actions">
                    <form action="admin-genera-fatture.php" method="POST" id="generaFattureForm" style="display: inline;">
                        <?php echo csrfField(); ?>
                        <button type="submit" class="btn-genera-fatture" onclick="return confirm('Confermi la generazione delle fatture mensili?\n\nVerranno generate fatture per tutti i contratti attivi con forniture attive.');">
                            <span>📊</span>
                            <span>Genera Fatture</span>
                        </button>
                    </form>
                    
                    <form action="admin-applica-sovrapprezzo.php" method="POST" id="applicaSovrapprezzoForm" style="display: inline;">
                        <?php echo csrfField(); ?>
                        <button type="submit" class="btn-sovrapprezzo" onclick="return confirm('Confermi l\'applicazione del sovrapprezzo alle fatture scadute?\n\nVerrà applicato un sovrapprezzo del 10% a tutte le fatture scadute non pagate.');">
                            <span>⚠️</span>
                            <span>Applica Sovrapprezzo</span>
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <div class="table-container">
                <?php if (empty($fatture)): ?>
                    <div class="empty-state">
                        <div class="icon">✅</div>
                        <h3>Nessuna Fattura Non Pagata</h3>
                        <p>Tutte le fatture sono state pagate o non ci sono fatture nel sistema.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID Fattura</th>
                                <th>Cliente</th>
                                <th>Importo</th>
                                <th>Sconto</th>
                                <th>Sovrapprezzo</th>
                                <th>Data Emissione</th>
                                <th>Scadenza</th>
                                <th>Periodo Rif.</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fatture as $fattura): 
                                $isScaduta = strtotime($fattura['data_scadenza']) < time();
                                $hasSconto = $fattura['sconto'] > 0;
                                $hasSovrapprezzo = $fattura['sovrapprezzo'] > 0;
                            ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($fattura['IdFattura']); ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($fattura['nome'] . ' ' . $fattura['cognome']); ?><br>
                                        <small style="color: var(--color-text-muted);"><?php echo htmlspecialchars($fattura['email']); ?></small>
                                    </td>
                                    <td>
                                        <strong style="color: var(--color-accent);">
                                            € <?php echo number_format($fattura['importo_fattura'], 2, ',', '.'); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php if ($hasSconto): ?>
                                            <span class="badge-sconto">-<?php echo number_format($fattura['sconto'], 0); ?>%</span>
                                        <?php else: ?>
                                            <span style="color: var(--color-text-muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($hasSovrapprezzo): ?>
                                            <span class="badge-sovrapprezzo">+<?php echo number_format($fattura['sovrapprezzo'], 0); ?>%</span>
                                        <?php else: ?>
                                            <span style="color: var(--color-text-muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($fattura['data_emissione'])); ?></td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($fattura['data_scadenza'])); ?>
                                        <?php if ($isScaduta): ?>
                                            <br><span class="badge-scaduta">SCADUTA</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo date('d/m/Y', strtotime($fattura['periodo_rif_inizio'])); ?>
                                            <br>
                                            <?php echo date('d/m/Y', strtotime($fattura['periodo_rif_fine'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <button class="btn-sconto" 
                                                onclick="openScontoModal(<?php echo $fattura['IdFattura']; ?>, 
                                                                        <?php echo $fattura['importo_fattura']; ?>,
                                                                        <?php echo $fattura['sconto']; ?>)"
                                                <?php echo $hasSconto ? 'disabled' : ''; ?>>
                                            <?php echo $hasSconto ? 'Sconto Già Applicato' : 'Applica Sconto'; ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
        </div>
    </main>
    
    <!-- Modal Sconto -->
    <div id="scontoModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 10000; align-items: center; justify-content: center;">
        <div style="background: var(--color-bg); padding: 2rem; border-radius: 16px; max-width: 500px; width: 90%;">
            <h3 style="margin-bottom: 1.5rem; color: var(--color-accent);">Applica Sconto alla Fattura</h3>
            
            <form action="admin-fatture-sconto.php" method="POST" id="formSconto">
                <?php echo csrfField(); ?>
                <input type="hidden" name="id_fattura" id="modal_id_fattura">
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Fattura #<span id="modal_fattura_display"></span></label>
                    <p style="color: var(--color-text-muted); font-size: 0.9rem;">
                        Importo attuale: € <span id="modal_importo_display"></span>
                    </p>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label for="percentuale_sconto" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                        Percentuale Sconto (%)
                    </label>
                    <input 
                        type="number" 
                        id="percentuale_sconto" 
                        name="percentuale_sconto" 
                        min="0" 
                        max="100" 
                        step="0.01"
                        required
                        style="width: 100%; padding: 0.75rem; border: 1px solid var(--glass-border); border-radius: 8px; background: rgba(255,255,255,0.05); color: var(--color-text);"
                    >
                    <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-top: 0.3rem;">
                        Inserisci un valore tra 0 e 100
                    </p>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="closeScontoModal()" style="padding: 0.75rem 1.5rem; background: rgba(255,255,255,0.1); color: var(--color-text); border: 1px solid var(--glass-border); border-radius: 8px; cursor: pointer; font-weight: 600;">
                        Annulla
                    </button>
                    <button type="submit" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, var(--color-success) 0%, #00b881 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        Applica Sconto
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openScontoModal(idFattura, importo, scontoEsistente) {
            if (scontoEsistente > 0) {
                alert('Questa fattura ha già uno sconto applicato.');
                return;
            }
            
            document.getElementById('modal_id_fattura').value = idFattura;
            document.getElementById('modal_fattura_display').textContent = idFattura;
            document.getElementById('modal_importo_display').textContent = importo.toFixed(2).replace('.', ',');
            document.getElementById('percentuale_sconto').value = '';
            
            const modal = document.getElementById('scontoModal');
            modal.style.display = 'flex';
        }
        
        function closeScontoModal() {
            document.getElementById('scontoModal').style.display = 'none';
        }
        
        // Chiudi modal cliccando fuori
        document.getElementById('scontoModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeScontoModal();
            }
        });
        
        // Validazione form sconto
        document.getElementById('formSconto').addEventListener('submit', function(e) {
            const percentuale = parseFloat(document.getElementById('percentuale_sconto').value);
            
            if (isNaN(percentuale) || percentuale < 0 || percentuale > 100) {
                e.preventDefault();
                alert('La percentuale di sconto deve essere tra 0 e 100.');
                return false;
            }
            
            return confirm(`Confermi l'applicazione dello sconto del ${percentuale}%?`);
        });
    </script>
</body>
</html>
