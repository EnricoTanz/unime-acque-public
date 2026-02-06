<?php
/**
 * UNIME-ACQUE - Inserisci Letture Consumi (TECNICO)
 * 
 * Form per registrare letture consumi su contatori attivi.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

startSecureSession();
requireRole('TECNICO');

$conn = getDbConnection();

// Recupera tutti i contatori attivi con informazioni fornitura/cliente
$queryContatori = "SELECT cnt.IdContatore, cnt.matricola_contatore, cnt.marca_contatore,
                          f.IdFornitura, f.indirizzo_fornitura,
                          c.IdContratto,
                          u.nome, u.cognome, u.email
                   FROM CONTATORE cnt
                   INNER JOIN FORNITURA f ON cnt.IdFornitura = f.IdFornitura
                   INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
                   INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
                   WHERE cnt.stato_contatore = 'ATTIVO'
                   ORDER BY cnt.IdContatore DESC";

$resultContatori = mysqli_query($conn, $queryContatori);

$contatori = [];
while ($row = mysqli_fetch_assoc($resultContatori)) {
    $contatori[] = $row;
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserisci Letture | UNIME-ACQUE</title>
    
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
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--color-text);
            border: 1px solid var(--glass-border);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
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
        
        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid #2ecc71;
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
                        <li><a href="tecnico.php">Dashboard</a></li>
                        <li><a href="../auth/logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="page-content">
        <div class="container">
            
            <a href="tecnico.php" class="back-link">← Torna alla Dashboard</a>
            
            <div class="page-header">
                <h1>📊 Inserisci Lettura Consumi</h1>
                <p class="subtitle">Registra una nuova lettura consumi per un contatore</p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($contatori)): ?>
                <div class="alert alert-warning">
                    ⚠️ Non ci sono contatori attivi nel sistema. Installa un contatore prima di inserire letture.
                </div>
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="tecnico.php" class="btn btn-secondary">Torna alla Dashboard</a>
                </div>
            <?php else: ?>
            
            <div class="form-card">
                <form action="tecnico-letture-process.php" method="POST" id="letturaForm">
                    <?php echo csrfField(); ?>
                    
                    <!-- Selezione Contatore -->
                    <div class="form-section">
                        <h3>Contatore</h3>
                        
                        <div class="form-group">
                            <label class="form-label" for="id_contatore">
                                Seleziona Contatore <span class="required">*</span>
                            </label>
                            <select id="id_contatore" name="id_contatore" class="form-select" required>
                                <option value="">-- Seleziona Contatore --</option>
                                <?php foreach ($contatori as $contatore): ?>
                                    <option value="<?php echo $contatore['IdContatore']; ?>">
                                        #<?php echo $contatore['IdContatore']; ?> - 
                                        Matricola: <?php echo htmlspecialchars($contatore['matricola_contatore']); ?> - 
                                        <?php echo htmlspecialchars($contatore['nome'] . ' ' . $contatore['cognome']); ?> - 
                                        <?php echo htmlspecialchars($contatore['indirizzo_fornitura']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="form-hint">Seleziona il contatore per cui registrare la lettura</p>
                        </div>
                    </div>
                    
                    <!-- Dati Lettura -->
                    <div class="form-section">
                        <h3>Dati Lettura</h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="data_rif">
                                    Data Lettura <span class="required">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="data_rif" 
                                    name="data_rif" 
                                    class="form-input"
                                    value="<?php echo date('Y-m-d'); ?>"
                                    max="<?php echo date('Y-m-d'); ?>"
                                    required
                                >
                                <p class="form-hint">Data della rilevazione (non può essere futura)</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="vol_consumato">
                                    Volume Consumato (m³) <span class="required">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    id="vol_consumato" 
                                    name="vol_consumato" 
                                    class="form-input"
                                    min="0"
                                    max="99999"
                                    step="1"
                                    required
                                >
                                <p class="form-hint">Volume in metri cubi (0-99999)</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pulsanti Azione -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='tecnico.php'">
                            Annulla
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Inserisci Lettura
                        </button>
                    </div>
                    
                </form>
            </div>
            
            <?php endif; ?>
            
        </div>
    </main>
    
    <script>
        // Validazione form
        const form = document.getElementById('letturaForm');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const idContatore = document.getElementById('id_contatore').value;
                const dataRif = document.getElementById('data_rif').value;
                const volConsumato = document.getElementById('vol_consumato').value;
                
                if (!idContatore) {
                    e.preventDefault();
                    alert('Seleziona un contatore.');
                    return false;
                }
                
                if (!dataRif) {
                    e.preventDefault();
                    alert('Seleziona la data della lettura.');
                    return false;
                }
                
                // Verifica che la data non sia futura
                const oggi = new Date();
                oggi.setHours(0, 0, 0, 0);
                const dataScelta = new Date(dataRif + 'T00:00:00'); 
                
                if (dataScelta > oggi) {
                    e.preventDefault();
                    alert('La data della lettura non può essere futura.');
                    return false;
                }
                
                if (!volConsumato || volConsumato < 0) {
                    e.preventDefault();
                    alert('Inserisci un volume consumato valido (≥ 0).');
                    return false;
                }
                
                const vol = parseInt(volConsumato);
                if (vol > 99999) {
                    e.preventDefault();
                    alert('Volume consumato troppo alto (max 99999 m³).');
                    return false;
                }
                
                return confirm('Confermi l\'inserimento della lettura?');
            });
        }
    </script>
</body>
</html>
