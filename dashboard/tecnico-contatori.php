<?php
/**
 * UNIME-ACQUE - Aggiungi Contatore (TECNICO)
 * 
 * Form per installare un nuovo contatore su una fornitura.
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

// Recupera forniture attive senza contatore attivo
$queryForniture = "SELECT f.IdFornitura, f.indirizzo_fornitura, f.stato_fornitura,
                          c.IdContratto, c.tipo_contratto,
                          u.nome, u.cognome, u.email
                   FROM FORNITURA f
                   INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
                   INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
                   LEFT JOIN CONTATORE cnt ON f.IdFornitura = cnt.IdFornitura AND cnt.stato_contatore = 'ATTIVO'
                   WHERE f.stato_fornitura IN ('ATTIVA', 'IN ATTESA DI ATTIVAZIONE')
                     AND cnt.IdContatore IS NULL
                   ORDER BY f.IdFornitura DESC";

$resultForniture = mysqli_query($conn, $queryForniture);

$forniture = [];
while ($row = mysqli_fetch_assoc($resultForniture)) {
    $forniture[] = $row;
}

// Recupera contatori attivi (per eventuale sostituzione)
$queryContatoriAttivi = "SELECT cnt.IdContatore, cnt.matricola_contatore, cnt.marca_contatore,
                                f.indirizzo_fornitura, f.IdFornitura,
                                u.nome, u.cognome
                         FROM CONTATORE cnt
                         INNER JOIN FORNITURA f ON cnt.IdFornitura = f.IdFornitura
                         INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
                         INNER JOIN UTENTE u ON c.IdUtente = u.IdUtente
                         WHERE cnt.stato_contatore = 'ATTIVO'
                         ORDER BY cnt.IdContatore DESC";

$resultContatoriAttivi = mysqli_query($conn, $queryContatoriAttivi);

$contatoriAttivi = [];
while ($row = mysqli_fetch_assoc($resultContatoriAttivi)) {
    $contatoriAttivi[] = $row;
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Contatore | UNIME-ACQUE</title>
    
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
            max-width: 900px;
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
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--color-text);
            font-size: 1rem;
            transition: all var(--transition-fast);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }
        
        .form-select option {
            background: #ffffff;
            color: #1a1a2e;
            padding: 0.5rem;
        }
        
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
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
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            border: 1px solid var(--glass-border);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .checkbox-group:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            cursor: pointer;
            font-weight: 600;
        }
        
        .sostituzione-section {
            display: none;
            background: rgba(241, 196, 15, 0.1);
            padding: 1.5rem;
            border-radius: 12px;
            border: 2px solid rgba(241, 196, 15, 0.3);
            margin-top: 1rem;
        }
        
        .sostituzione-section.visible {
            display: block;
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
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
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
                <h1>⚙️ Installa Nuovo Contatore</h1>
                <p class="subtitle">Registra l'installazione di un contatore su una fornitura</p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($forniture)): ?>
                <div class="alert alert-warning">
                    ⚠️ Non ci sono forniture disponibili per l'installazione di un nuovo contatore. Tutte le forniture attive hanno già un contatore installato. 
                    <br><br>
                    <strong>Puoi comunque installare un contatore in sostituzione di uno esistente</strong> spuntando l'opzione "Questo contatore sostituisce un contatore esistente" qui sotto.
                </div>
            <?php endif; ?>
            
            <div class="form-card">
                <form action="tecnico-contatori-process.php" method="POST" id="contatoreForm">
                    <?php echo csrfField(); ?>
                    
                    <!-- Selezione Fornitura -->
                    <div class="form-section">
                        <h3>Fornitura</h3>
                        
                        <div class="form-group">
                            <label class="form-label" for="id_fornitura">
                                Seleziona Fornitura <?php echo !empty($forniture) ? '<span class="required">*</span>' : '(opzionale se in sostituzione)'; ?>
                            </label>
                            <select id="id_fornitura" name="id_fornitura" class="form-select" <?php echo !empty($forniture) ? 'required' : ''; ?>>
                                <option value="">-- Seleziona Fornitura --</option>
                                <?php foreach ($forniture as $fornitura): ?>
                                    <option value="<?php echo $fornitura['IdFornitura']; ?>">
                                        #<?php echo $fornitura['IdFornitura']; ?> - 
                                        <?php echo htmlspecialchars($fornitura['nome'] . ' ' . $fornitura['cognome']); ?> - 
                                        <?php echo htmlspecialchars($fornitura['indirizzo_fornitura']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="form-hint">
                                <?php if (!empty($forniture)): ?>
                                    Seleziona la fornitura su cui installare il contatore
                                <?php else: ?>
                                    Nessuna fornitura disponibile. Puoi installare un contatore in sostituzione selezionando l'opzione sotto.
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Dati Contatore -->
                    <div class="form-section">
                        <h3>Dati Contatore</h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="matricola_contatore">
                                    Matricola Contatore <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="matricola_contatore" 
                                    name="matricola_contatore" 
                                    class="form-input"
                                    maxlength="50"
                                    required
                                >
                                <p class="form-hint">Numero di serie univoco del contatore</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="marca_contatore">
                                    Marca Contatore <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="marca_contatore" 
                                    name="marca_contatore" 
                                    class="form-input"
                                    maxlength="50"
                                    required
                                >
                                <p class="form-hint">Marca/produttore del contatore</p>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label class="form-label" for="note">
                                Note (opzionale)
                            </label>
                            <textarea 
                                id="note" 
                                name="note" 
                                class="form-textarea"
                                maxlength="250"
                                placeholder="Eventuali note sull'installazione..."
                            ></textarea>
                            <p class="form-hint">Massimo 250 caratteri</p>
                        </div>
                    </div>
                    
                    <!-- Sostituzione Contatore -->
                    <div class="form-section">
                        <h3>Sostituzione</h3>
                        
                        <div class="checkbox-group" onclick="document.getElementById('is_sostituzione').click();">
                            <input 
                                type="checkbox" 
                                id="is_sostituzione" 
                                name="is_sostituzione" 
                                value="1"
                                onclick="event.stopPropagation();"
                            >
                            <label for="is_sostituzione">Questo contatore sostituisce un contatore esistente</label>
                        </div>
                        
                        <div id="sostituzione-section" class="sostituzione-section">
                            <div class="form-group">
                                <label class="form-label" for="sostituisce_id_contatore">
                                    Seleziona Contatore da Sostituire
                                </label>
                                <select id="sostituisce_id_contatore" name="sostituisce_id_contatore" class="form-select">
                                    <option value="">-- Seleziona Contatore --</option>
                                    <?php foreach ($contatoriAttivi as $contatore): ?>
                                        <option value="<?php echo $contatore['IdContatore']; ?>">
                                            #<?php echo $contatore['IdContatore']; ?> - 
                                            Matricola: <?php echo htmlspecialchars($contatore['matricola_contatore']); ?> - 
                                            <?php echo htmlspecialchars($contatore['nome'] . ' ' . $contatore['cognome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="form-hint">Il contatore selezionato verrà marcato come "SOSTITUITO"</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pulsanti Azione -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='tecnico.php'">
                            Annulla
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Installa Contatore
                        </button>
                    </div>
                    
                </form>
            </div>
            
        </div>
    </main>
    
    <script>
        // Toggle sezione sostituzione
        const checkboxSostituzione = document.getElementById('is_sostituzione');
        const sezioneSostituzione = document.getElementById('sostituzione-section');
        const selectSostituisce = document.getElementById('sostituisce_id_contatore');
        
        checkboxSostituzione.addEventListener('change', function() {
            if (this.checked) {
                sezioneSostituzione.classList.add('visible');
                selectSostituisce.required = true;
            } else {
                sezioneSostituzione.classList.remove('visible');
                selectSostituisce.required = false;
                selectSostituisce.value = '';
            }
        });
        
        // Validazione form
        const form = document.getElementById('contatoreForm');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const idFornitura = document.getElementById('id_fornitura').value;
                const matricola = document.getElementById('matricola_contatore').value.trim();
                const marca = document.getElementById('marca_contatore').value.trim();
                const isSostituzione = checkboxSostituzione.checked;
                const sostituisceId = selectSostituisce.value;
                
                // Se non è una sostituzione, la fornitura è obbligatoria
                if (!isSostituzione && !idFornitura) {
                    e.preventDefault();
                    alert('Seleziona una fornitura o spunta "Questo contatore sostituisce un contatore esistente".');
                    return false;
                }
                
                if (!matricola) {
                    e.preventDefault();
                    alert('Inserisci la matricola del contatore.');
                    return false;
                }
                
                if (!marca) {
                    e.preventDefault();
                    alert('Inserisci la marca del contatore.');
                    return false;
                }
                
                if (isSostituzione && !sostituisceId) {
                    e.preventDefault();
                    alert('Seleziona il contatore da sostituire.');
                    return false;
                }
                
                let confermaMsg = 'Confermi l\'installazione del contatore?';
                if (isSostituzione) {
                    confermaMsg = 'Confermi l\'installazione del nuovo contatore in sostituzione?\n\nIl vecchio contatore verrà marcato come SOSTITUITO.';
                }
                
                return confirm(confermaMsg);
            });
        }
    </script>
</body>
</html>
