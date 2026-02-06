<?php
/**
 * UNIME-ACQUE - Crea Nuovo Contratto
 * 
 * Form per creare un contratto e abbinare una tariffa.
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

// Recupera clienti disponibili
$queryClienti = "SELECT IdUtente, nome, cognome, email, ragione_sociale FROM UTENTE WHERE ruolo = 'CLIENTE' ORDER BY cognome, nome";
$resultClienti = mysqli_query($conn, $queryClienti);

$clienti = [];
while ($row = mysqli_fetch_assoc($resultClienti)) {
    $clienti[] = $row;
}

// Recupera tariffe disponibili
$queryTariffe = "SELECT IdTariffa, nome_tariffa, tariffa_applicata FROM TARIFFA ORDER BY nome_tariffa";
$resultTariffe = mysqli_query($conn, $queryTariffe);

$tariffe = [];
while ($row = mysqli_fetch_assoc($resultTariffe)) {
    $tariffe[] = $row;
}

// Cliente preselezionato (da redirect dopo registrazione)
$selectedCliente = isset($_GET['cliente']) ? (int)$_GET['cliente'] : 0;

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea Nuovo Contratto | UNIME-ACQUE</title>
    
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
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--glass-border);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
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
            color: var(--color-text-light);
            font-size: 0.9rem;
        }
        
        .form-label .required {
            color: var(--color-danger);
        }
        
        .form-select,
        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            font-family: var(--font-body);
            font-size: 1rem;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            transition: all var(--transition-fast);
        }
        
        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2305bfdb' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 3rem;
        }
        
        .form-select option,
        .form-input:focus,
        .form-select:focus {
            background: #0a4d68;
            color: #ffffff;
        }
        
        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--color-primary-light);
            box-shadow: 0 0 0 3px rgba(5, 191, 219, 0.15);
        }
        
        .form-hint {
            font-size: 0.85rem;
            color: var(--color-text-muted);
            margin-top: 0.5rem;
        }
        
        .info-box {
            background: rgba(5, 191, 219, 0.1);
            border: 1px solid var(--color-primary-light);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 2rem;
        }
        
        .info-box p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--color-text-light);
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--glass-border);
        }
        
        .btn {
            flex: 1;
            padding: 1.25rem;
            font-size: 1rem;
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--color-text-muted);
            border: 1px solid var(--glass-border);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--color-text-muted);
            color: var(--color-text-light);
        }
        
        @media screen and (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column-reverse;
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
            
            <a href="admin-contratti.php" class="back-link">← Torna ai Contratti</a>
            
            <div class="page-header">
                <h1>📄 Crea Nuovo Contratto</h1>
                <p class="subtitle">Compila i dati del contratto e abbina una tariffa</p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>" style="max-width: 800px; margin: 0 auto 2rem;">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($clienti)): ?>
                <div class="form-card">
                    <div style="text-align: center; padding: 2rem;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">⚠️</div>
                        <h3 style="color: var(--color-warning); margin-bottom: 1rem;">Nessun Cliente Disponibile</h3>
                        <p style="color: var(--color-text-muted); margin-bottom: 2rem;">
                            Prima di creare un contratto, devi registrare almeno un cliente.
                        </p>
                        <a href="admin-clienti-nuovo.php" class="btn btn-primary" style="display: inline-block; text-decoration: none;">
                            Registra Nuovo Cliente
                        </a>
                    </div>
                </div>
            <?php elseif (empty($tariffe)): ?>
                <div class="form-card">
                    <div style="text-align: center; padding: 2rem;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">⚠️</div>
                        <h3 style="color: var(--color-warning); margin-bottom: 1rem;">Nessuna Tariffa Disponibile</h3>
                        <p style="color: var(--color-text-muted); margin-bottom: 2rem;">
                            Prima di creare un contratto, devi censire almeno una tariffa.
                        </p>
                        <a href="admin-tariffe.php" class="btn btn-primary" style="display: inline-block; text-decoration: none;">
                            Crea Nuova Tariffa
                        </a>
                    </div>
                </div>
            <?php else: ?>
            
            <div class="form-card">
                
                <div class="info-box">
                    <p>
                        ℹ️ <strong>Nota:</strong> La data di stipula e la data di inizio validità saranno impostate automaticamente alla data corrente. 
                        La tariffa selezionata verrà abbinata al contratto con decorrenza immediata.
                    </p>
                </div>
                
                <form action="admin-contratti-process.php" method="POST" id="contrattoForm">
                    <?php echo csrfField(); ?>
                    
                    <!-- Selezione Cliente -->
                    <div class="form-section">
                        <h3>Cliente</h3>
                        
                        <div class="form-group full-width">
                            <label class="form-label" for="id_cliente">
                                Seleziona Cliente <span class="required">*</span>
                            </label>
                            <select id="id_cliente" name="id_cliente" class="form-select" required>
                                <option value="">-- Seleziona un cliente --</option>
                                <?php foreach ($clienti as $cliente): ?>
                                    <option value="<?php echo $cliente['IdUtente']; ?>"
                                            <?php echo $selectedCliente == $cliente['IdUtente'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cliente['cognome'] . ' ' . $cliente['nome']); ?>
                                        <?php if ($cliente['ragione_sociale']): ?>
                                            (<?php echo htmlspecialchars($cliente['ragione_sociale']); ?>)
                                        <?php endif; ?>
                                        - <?php echo htmlspecialchars($cliente['email']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Dati Contratto -->
                    <div class="form-section">
                        <h3>Dati Contratto</h3>
                        
                        <div class="form-group full-width">
                            <label class="form-label" for="tipo_contratto">
                                Tipo Contratto <span class="required">*</span>
                            </label>
                            <select id="tipo_contratto" name="tipo_contratto" class="form-select" required>
                                <option value="">-- Seleziona tipo --</option>
                                <option value="DOMESTICA">Domestica</option>
                                <option value="BUSINESS">Business</option>
                            </select>
                            <p class="form-hint">
                                <strong>Domestica:</strong> per abitazioni private | 
                                <strong>Business:</strong> per attività commerciali
                            </p>
                        </div>
                    </div>
                    
                    <!-- Abbinamento Tariffa -->
                    <div class="form-section">
                        <h3>Abbinamento Tariffa</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="id_tariffa">
                                    Tariffa <span class="required">*</span>
                                </label>
                                <select id="id_tariffa" name="id_tariffa" class="form-select" required>
                                    <option value="">-- Seleziona tariffa --</option>
                                    <?php foreach ($tariffe as $tariffa): ?>
                                        <option value="<?php echo $tariffa['IdTariffa']; ?>">
                                            <?php echo htmlspecialchars($tariffa['nome_tariffa']); ?> 
                                            (€ <?php echo number_format($tariffa['tariffa_applicata'], 4, ',', '.'); ?>/m³)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="form-hint">La tariffa verrà applicata da subito</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="tariffa_data_fine">
                                    Scadenza Tariffa (opzionale)
                                </label>
                                <input 
                                    type="date" 
                                    id="tariffa_data_fine" 
                                    name="tariffa_data_fine" 
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
                            Crea Contratto
                        </button>
                    </div>
                    
                </form>
                
            </div>
            
            <?php endif; ?>
            
        </div>
    </main>
    
    <script>
        // Validazione form
        const form = document.getElementById('contrattoForm');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const idCliente = document.getElementById('id_cliente').value;
                const tipoContratto = document.getElementById('tipo_contratto').value;
                const idTariffa = document.getElementById('id_tariffa').value;
                const tariffaDataFine = document.getElementById('tariffa_data_fine').value;
                
                if (!idCliente) {
                    e.preventDefault();
                    alert('Seleziona un cliente.');
                    return false;
                }
                
                if (!tipoContratto) {
                    e.preventDefault();
                    alert('Seleziona il tipo di contratto.');
                    return false;
                }
                
                if (!idTariffa) {
                    e.preventDefault();
                    alert('Seleziona una tariffa.');
                    return false;
                }
                
                // Validazione data fine tariffa (se presente, deve essere futura)
                if (tariffaDataFine) {
                    const dataFine = new Date(tariffaDataFine);
                    const oggi = new Date();
                    oggi.setHours(0, 0, 0, 0);
                    
                    if (dataFine <= oggi) {
                        e.preventDefault();
                        alert('La data di scadenza della tariffa deve essere futura.');
                        return false;
                    }
                }
                
                return confirm('Confermi la creazione del contratto?');
            });
        }
    </script>
</body>
</html>
