<?php
/**
 * UNIME-ACQUE - Gestione Forniture (AMMINISTRATORE)
 * 
 * Visualizza forniture esistenti e permette di crearne di nuove o cessarle.
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

// Recupera tutte le forniture
$query = "SELECT f.*, 
                 c.IdUtente, c.tipo_contratto, c.stato_contratto,
                 u.nome, u.cognome, u.email,
                 ag.nome_area, l.citta, l.CAP,
                 cnt.matricola_contatore, cnt.stato_contatore
          FROM FORNITURA f
          INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
          INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
          INNER JOIN AREA_GEOGRAFICA ag ON f.IdArea_fornitura = ag.IdArea
          INNER JOIN LOCALITA l ON ag.CAP = l.CAP
          LEFT JOIN CONTATORE cnt ON f.IdFornitura = cnt.IdFornitura AND cnt.stato_contatore = 'ATTIVO'
          ORDER BY f.IdFornitura DESC";

$result = mysqli_query($conn, $query);

$forniture = [];
while ($row = mysqli_fetch_assoc($result)) {
    $forniture[] = $row;
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Forniture | UNIME-ACQUE</title>
    
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
        
        .actions-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            justify-content: center;
        }
        
        .btn-action {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all var(--transition-fast);
            text-align: center;
        }
        
        .btn-new {
            background: var(--gradient-primary);
            color: white;
        }
        
        .btn-new:hover {
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
        
        .badge-attiva {
            background: rgba(46, 204, 113, 0.2);
            color: var(--color-success);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-attesa {
            background: rgba(241, 196, 15, 0.2);
            color: #f1c40f;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-disattivata {
            background: rgba(231, 76, 60, 0.2);
            color: var(--color-danger);
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
        
        .btn-cessazione {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }
        
        .btn-cessazione:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
        }
        
        .btn-cessazione:disabled {
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
                <h1>🏠 Gestione Forniture</h1>
                <p class="subtitle">Crea nuove forniture e gestisci quelle esistenti</p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <div class="actions-bar">
                <a href="admin-forniture-nuova.php" class="btn-action btn-new">
                    ➕ Crea Nuova Fornitura
                </a>
            </div>
            
            <div class="table-container">
                <?php if (empty($forniture)): ?>
                    <div class="empty-state">
                        <div class="icon">📭</div>
                        <h3>Nessuna Fornitura Presente</h3>
                        <p>Crea la prima fornitura per iniziare.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Contratto</th>
                                <th>Indirizzo</th>
                                <th>Località</th>
                                <th>Stato</th>
                                <th>Data Attivazione</th>
                                <th>Contatore</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($forniture as $fornitura): 
                                $isAttiva = $fornitura['stato_fornitura'] === 'ATTIVA';
                                $isAttesa = $fornitura['stato_fornitura'] === 'IN ATTESA DI ATTIVAZIONE';
                                $puoCessare = $isAttiva || $isAttesa;
                            ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($fornitura['IdFornitura']); ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($fornitura['nome'] . ' ' . $fornitura['cognome']); ?><br>
                                        <small style="color: var(--color-text-muted);"><?php echo htmlspecialchars($fornitura['email']); ?></small>
                                    </td>
                                    <td>
                                        <strong>#<?php echo htmlspecialchars($fornitura['IdContratto']); ?></strong><br>
                                        <small style="color: var(--color-text-muted);"><?php echo htmlspecialchars($fornitura['tipo_contratto']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($fornitura['indirizzo_fornitura']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($fornitura['citta']); ?><br>
                                        <small style="color: var(--color-text-muted);">CAP: <?php echo htmlspecialchars($fornitura['CAP']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($isAttiva): ?>
                                            <span class="badge-attiva">ATTIVA</span>
                                        <?php elseif ($isAttesa): ?>
                                            <span class="badge-attesa">IN ATTESA</span>
                                        <?php else: ?>
                                            <span class="badge-disattivata"><?php echo htmlspecialchars($fornitura['stato_fornitura']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($fornitura['data_attivazione']): ?>
                                            <?php echo date('d/m/Y', strtotime($fornitura['data_attivazione'])); ?>
                                        <?php else: ?>
                                            <span style="color: var(--color-text-muted);">Non attivata</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($fornitura['matricola_contatore']): ?>
                                            <?php echo htmlspecialchars($fornitura['matricola_contatore']); ?><br>
                                            <small style="color: var(--color-text-muted);"><?php echo htmlspecialchars($fornitura['stato_contatore']); ?></small>
                                        <?php else: ?>
                                            <span style="color: var(--color-text-muted);">Nessuno</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn-small btn-cessazione" 
                                                onclick="confermaCessazione(<?php echo $fornitura['IdFornitura']; ?>, '<?php echo htmlspecialchars($fornitura['indirizzo_fornitura'], ENT_QUOTES); ?>')"
                                                <?php echo !$puoCessare ? 'disabled' : ''; ?>>
                                            <?php echo $puoCessare ? '🛑 Cessa' : 'Già Cessata'; ?>
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
    
    <!-- Form nascosto per cessazione -->
    <form id="cessazioneForm" action="admin-forniture-cessa.php" method="POST" style="display: none;">
        <?php echo csrfField(); ?>
        <input type="hidden" name="id_fornitura" id="cessazione_id_fornitura">
    </form>
    
    <script>
        function confermaCessazione(idFornitura, indirizzo) {
            if (confirm(`ATTENZIONE!\n\nStai per cessare la fornitura:\n"${indirizzo}"\n\nQuesta operazione:\n- Disattiverà la fornitura\n- Cesserà il contratto associato\n- Disattiverà il contatore attivo\n\nConfermi la cessazione?`)) {
                document.getElementById('cessazione_id_fornitura').value = idFornitura;
                document.getElementById('cessazioneForm').submit();
            }
        }
    </script>
</body>
</html>
