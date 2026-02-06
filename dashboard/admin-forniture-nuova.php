<?php
/**
 * UNIME-ACQUE - Crea Nuova Fornitura
 * 
 * Form per creare una nuova fornitura associata a un contratto.
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

$conn = getDbConnection();

// Recupera contratti ATTIVI senza fornitura
$queryContratti = "SELECT c.*, u.nome, u.cognome, u.email
                   FROM CONTRATTO c
                   INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
                   LEFT JOIN FORNITURA f ON c.IdContratto = f.IdContratto
                   WHERE c.stato_contratto = 'ATTIVO'
                     AND f.IdFornitura IS NULL
                   ORDER BY c.IdContratto DESC";

$resultContratti = mysqli_query($conn, $queryContratti);

$contratti = [];
while ($row = mysqli_fetch_assoc($resultContratti)) {
    $contratti[] = $row;
}

// Recupera tutte le località per il primo menu a tendina
$queryLocalita = "SELECT DISTINCT CAP, citta, provincia 
                  FROM LOCALITA 
                  ORDER BY citta";

$resultLocalita = mysqli_query($conn, $queryLocalita);

$localita = [];
while ($row = mysqli_fetch_assoc($resultLocalita)) {
    $localita[] = $row;
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea Nuova Fornitura | UNIME-ACQUE</title>
    
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
            
            <a href="admin-forniture.php" class="back-link">← Torna a Gestione Forniture</a>
            
            <div class="page-header">
                <h1>➕ Crea Nuova Fornitura</h1>
                <p class="subtitle">Associa una fornitura a un contratto attivo</p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($contratti)): ?>
                <div class="alert alert-warning">
                    ⚠️ Non ci sono contratti attivi senza fornitura. Tutti i contratti attivi hanno già una fornitura associata.
                </div>
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="admin-forniture.php" class="btn btn-secondary">Torna alla Gestione Forniture</a>
                </div>
            <?php else: ?>
            
            <div class="form-card">
                <form action="admin-forniture-process.php" method="POST" id="fornituraForm">
                    <?php echo csrfField(); ?>
                    
                    <!-- Selezione Contratto -->
                    <div class="form-section">
                        <h3>Contratto</h3>
                        
                        <div class="form-group">
                            <label class="form-label" for="id_contratto">
                                Seleziona Contratto <span class="required">*</span>
                            </label>
                            <select id="id_contratto" name="id_contratto" class="form-select" required>
                                <option value="">-- Seleziona Contratto --</option>
                                <?php foreach ($contratti as $contratto): ?>
                                    <option value="<?php echo $contratto['IdContratto']; ?>">
                                        #<?php echo $contratto['IdContratto']; ?> - 
                                        <?php echo htmlspecialchars($contratto['nome'] . ' ' . $contratto['cognome']); ?> 
                                        (<?php echo htmlspecialchars($contratto['tipo_contratto']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="form-hint">Seleziona il contratto a cui associare la fornitura</p>
                        </div>
                    </div>
                    
                    <!-- Selezione Località -->
                    <div class="form-section">
                        <h3>Località e Indirizzo</h3>
                        
                        <div class="form-group">
                            <label class="form-label" for="cap">
                                CAP - Località <span class="required">*</span>
                            </label>
                            <select id="cap" name="cap" class="form-select" required>
                                <option value="">-- Seleziona Località --</option>
                                <?php foreach ($localita as $loc): ?>
                                    <option value="<?php echo htmlspecialchars($loc['CAP']); ?>">
                                        <?php echo htmlspecialchars($loc['CAP'] . ' - ' . $loc['citta'] . ' (' . $loc['provincia'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="form-hint">Prima seleziona la località</p>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="id_area">
                                Indirizzo / Area <span class="required">*</span>
                            </label>
                            <select id="id_area" name="id_area" class="form-select" required disabled>
                                <option value="">-- Prima seleziona una località --</option>
                            </select>
                            <p class="form-hint">Seleziona l'indirizzo/area specifica</p>
                        </div>
                    </div>
                    
                    <!-- Pulsanti Azione -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='admin-forniture.php'">
                            Annulla
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Crea Fornitura
                        </button>
                    </div>
                    
                </form>
            </div>
            
            <?php endif; ?>
            
        </div>
    </main>
    
    <script>
        // Menu a tendina in cascata: CAP -> Aree
        const capSelect = document.getElementById('cap');
        const areaSelect = document.getElementById('id_area');
        
        capSelect.addEventListener('change', function() {
            const cap = this.value;
            
            // Reset area select
            areaSelect.innerHTML = '<option value="">Caricamento...</option>';
            areaSelect.disabled = true;
            
            if (!cap) {
                areaSelect.innerHTML = '<option value="">-- Prima seleziona una località --</option>';
                return;
            }
            
            // Fetch aree per CAP selezionato
            fetch(`admin-forniture-get-aree.php?cap=${encodeURIComponent(cap)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.aree.length > 0) {
                        areaSelect.innerHTML = '<option value="">-- Seleziona Indirizzo/Area --</option>';
                        data.aree.forEach(area => {
                            const option = document.createElement('option');
                            option.value = area.IdArea;
                            option.textContent = area.nome_area;
                            areaSelect.appendChild(option);
                        });
                        areaSelect.disabled = false;
                    } else {
                        areaSelect.innerHTML = '<option value="">Nessuna area disponibile per questa località</option>';
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    areaSelect.innerHTML = '<option value="">Errore nel caricamento delle aree</option>';
                });
        });
        
        // Validazione form
        const form = document.getElementById('fornituraForm');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const idContratto = document.getElementById('id_contratto').value;
                const cap = document.getElementById('cap').value;
                const idArea = document.getElementById('id_area').value;
                
                if (!idContratto) {
                    e.preventDefault();
                    alert('Seleziona un contratto.');
                    return false;
                }
                
                if (!cap) {
                    e.preventDefault();
                    alert('Seleziona una località (CAP).');
                    return false;
                }
                
                if (!idArea) {
                    e.preventDefault();
                    alert('Seleziona un indirizzo/area.');
                    return false;
                }
                
                return confirm('Confermi la creazione della fornitura?\n\nLa fornitura sarà creata in stato "IN ATTESA DI ATTIVAZIONE".');
            });
        }
    </script>
</body>
</html>
