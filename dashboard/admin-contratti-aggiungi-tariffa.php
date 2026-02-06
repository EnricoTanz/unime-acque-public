<?php
/**
 * UNIME-ACQUE - Aggiungi Tariffa a Contratto
 * 
 * Form per aggiungere una nuova tariffa a un contratto esistente.
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

// Recupera ID contratto dalla query string
$idContratto = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idContratto <= 0) {
    setFlashMessage('danger', 'ID contratto non valido.');
    header('Location: admin-contratti.php');
    exit;
}

$conn = getDbConnection();

// Recupera informazioni del contratto
$queryContratto = "SELECT c.*, u.nome, u.cognome, u.email
                   FROM CONTRATTO c
                   INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
                   WHERE c.IdContratto = ?
                   LIMIT 1";

$stmtContratto = mysqli_prepare($conn, $queryContratto);
mysqli_stmt_bind_param($stmtContratto, 'i', $idContratto);
mysqli_stmt_execute($stmtContratto);
$resultContratto = mysqli_stmt_get_result($stmtContratto);

if (mysqli_num_rows($resultContratto) === 0) {
    mysqli_stmt_close($stmtContratto);
    setFlashMessage('danger', 'Contratto non trovato.');
    header('Location: admin-contratti.php');
    exit;
}

$contratto = mysqli_fetch_assoc($resultContratto);
mysqli_stmt_close($stmtContratto);

// Recupera tariffe già associate al contratto
$queryTariffeEsistenti = "SELECT at.*, t.nome_tariffa, t.tariffa_applicata
                          FROM ABBINAMENTO_TARIFFA at
                          INNER JOIN TARIFFA t ON at.IdTariffa = t.IdTariffa
                          WHERE at.IdContratto = ?
                          ORDER BY at.data_inizio DESC";

$stmtTariffeEsistenti = mysqli_prepare($conn, $queryTariffeEsistenti);
mysqli_stmt_bind_param($stmtTariffeEsistenti, 'i', $idContratto);
mysqli_stmt_execute($stmtTariffeEsistenti);
$resultTariffeEsistenti = mysqli_stmt_get_result($stmtTariffeEsistenti);

$tariffeEsistenti = [];
while ($row = mysqli_fetch_assoc($resultTariffeEsistenti)) {
    $tariffeEsistenti[] = $row;
}
mysqli_stmt_close($stmtTariffeEsistenti);

// Recupera tutte le tariffe disponibili
$queryTariffe = "SELECT IdTariffa, nome_tariffa, tariffa_applicata 
                 FROM TARIFFA 
                 ORDER BY nome_tariffa";
$resultTariffe = mysqli_query($conn, $queryTariffe);

$tariffe = [];
while ($row = mysqli_fetch_assoc($resultTariffe)) {
    $tariffe[] = $row;
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Tariffa al Contratto | UNIME-ACQUE</title>
    
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
            margin-bottom: 3rem;
        }
        
        .page-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .info-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .info-card h3 {
            color: var(--color-accent);
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .info-item {
            padding: 1rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: var(--color-text-muted);
            margin-bottom: 0.3rem;
        }
        
        .info-value {
            font-weight: 600;
            color: var(--color-text);
        }
        
        .form-card {
            max-width: 800px;
            margin: 0 auto;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 3rem;
        }
        
        .form-section {
            margin-bottom: 2.5rem;
        }
        
        .form-section h3 {
            color: var(--color-accent);
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--color-accent);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--color-text);
        }
        
        .form-input,
        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--color-text);
            font-size: 1rem;
            transition: all var(--transition-fast);
        }
        
        .form-select option {
            background: #ffffff;
            color: #1a1a2e;
            padding: 0.5rem;
        }
        
        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--color-accent);
            background: rgba(255, 255, 255, 0.08);
        }
        
        .form-hint {
            font-size: 0.85rem;
            color: var(--color-text-muted);
            margin-top: 0.3rem;
        }
        
        .required {
            color: #ff4757;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--glass-border);
        }
        
        .btn {
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            border: none;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--color-text);
            border: 1px solid var(--glass-border);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .tariffe-esistenti {
            margin-bottom: 2rem;
        }
        
        .tariffa-row {
            background: rgba(255, 255, 255, 0.03);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .tariffa-info {
            flex: 1;
        }
        
        .tariffa-nome {
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 0.3rem;
        }
        
        .tariffa-dettagli {
            font-size: 0.85rem;
            color: var(--color-text-muted);
        }
        
        .badge-attiva {
            background: #2ecc71;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-scaduta {
            background: #e74c3c;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
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
            
            <a href="admin-contratti.php" class="back-link">← Torna a Gestione Contratti</a>
            
            <div class="page-header">
                <h1>➕ Aggiungi Tariffa al Contratto</h1>
                <p class="subtitle">Contratto #<?php echo htmlspecialchars($idContratto); ?></p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Info Contratto -->
            <div class="info-card">
                <h3>📋 Informazioni Contratto</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Cliente</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($contratto['nome'] . ' ' . $contratto['cognome']); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($contratto['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tipo Contratto</div>
                        <div class="info-value"><?php echo htmlspecialchars($contratto['tipo_contratto']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Stato</div>
                        <div class="info-value"><?php echo htmlspecialchars($contratto['stato_contratto']); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Tariffe Esistenti -->
            <?php if (!empty($tariffeEsistenti)): ?>
            <div class="info-card tariffe-esistenti">
                <h3>📊 Tariffe Attualmente Associate</h3>
                <?php foreach ($tariffeEsistenti as $tariffaEsistente): 
                    $isAttiva = (is_null($tariffaEsistente['data_fine']) || strtotime($tariffaEsistente['data_fine']) > time());
                ?>
                    <div class="tariffa-row">
                        <div class="tariffa-info">
                            <div class="tariffa-nome">
                                <?php echo htmlspecialchars($tariffaEsistente['nome_tariffa']); ?>
                            </div>
                            <div class="tariffa-dettagli">
                                € <?php echo number_format($tariffaEsistente['tariffa_applicata'], 4, ',', '.'); ?>/m³ • 
                                Dal <?php echo date('d/m/Y', strtotime($tariffaEsistente['data_inizio'])); ?>
                                <?php if (!is_null($tariffaEsistente['data_fine'])): ?>
                                    al <?php echo date('d/m/Y', strtotime($tariffaEsistente['data_fine'])); ?>
                                <?php else: ?>
                                    (nessuna scadenza)
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <?php if ($isAttiva): ?>
                                <span class="badge-attiva">ATTIVA</span>
                            <?php else: ?>
                                <span class="badge-scaduta">SCADUTA</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Form Nuova Tariffa -->
            <div class="form-card">
                <form action="admin-contratti-aggiungi-tariffa-process.php" method="POST" id="tariffeForm">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="id_contratto" value="<?php echo $idContratto; ?>">
                    
                    <div class="form-section">
                        <h3>Nuova Tariffa</h3>
                        
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label class="form-label" for="id_tariffa">
                                    Seleziona Tariffa <span class="required">*</span>
                                </label>
                                <select id="id_tariffa" name="id_tariffa" class="form-select" required>
                                    <option value="">-- Seleziona Tariffa --</option>
                                    <?php foreach ($tariffe as $tariffa): ?>
                                        <option value="<?php echo $tariffa['IdTariffa']; ?>">
                                            <?php echo htmlspecialchars($tariffa['nome_tariffa']); ?> 
                                            (€ <?php echo number_format($tariffa['tariffa_applicata'], 4, ',', '.'); ?>/m³)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="form-hint">La tariffa verrà applicata dalla data di inizio specificata</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="data_inizio">
                                    Data Inizio Validità <span class="required">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="data_inizio" 
                                    name="data_inizio" 
                                    class="form-input"
                                    value="<?php echo date('Y-m-d'); ?>"
                                    required
                                >
                                <p class="form-hint">Da quando la tariffa sarà valida</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="data_fine">
                                    Data Fine Validità
                                </label>
                                <input 
                                    type="date" 
                                    id="data_fine" 
                                    name="data_fine" 
                                    class="form-input"
                                    min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                >
                                <p class="form-hint">Se non specificata, la tariffa non avrà scadenza</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pulsanti Azione -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='admin-contratti.php'">
                            Annulla
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Aggiungi Tariffa
                        </button>
                    </div>
                    
                </form>
            </div>
            
        </div>
    </main>
    
    <script>
        // Validazione form
        const form = document.getElementById('tariffeForm');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const idTariffa = document.getElementById('id_tariffa').value;
                const dataInizio = document.getElementById('data_inizio').value;
                const dataFine = document.getElementById('data_fine').value;
                
                if (!idTariffa) {
                    e.preventDefault();
                    alert('Seleziona una tariffa.');
                    return false;
                }
                
                if (!dataInizio) {
                    e.preventDefault();
                    alert('Specifica la data di inizio validità.');
                    return false;
                }
                
                // Validazione date se entrambe presenti
                if (dataFine) {
                    const inizio = new Date(dataInizio);
                    const fine = new Date(dataFine);
                    
                    if (fine <= inizio) {
                        e.preventDefault();
                        alert('La data di fine validità deve essere successiva alla data di inizio.');
                        return false;
                    }
                }
                
                return confirm('Confermi l\'aggiunta della tariffa al contratto?');
            });
            
            // Sincronizza il campo min di data_fine con data_inizio
            document.getElementById('data_inizio').addEventListener('change', function() {
                const dataInizio = this.value;
                const dataFineInput = document.getElementById('data_fine');
                
                if (dataInizio) {
                    const minDataFine = new Date(dataInizio);
                    minDataFine.setDate(minDataFine.getDate() + 1);
                    dataFineInput.min = minDataFine.toISOString().split('T')[0];
                }
            });
        }
    </script>
</body>
</html>
