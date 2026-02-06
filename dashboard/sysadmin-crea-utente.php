<?php
/**
 * UNIME-ACQUE - Creazione Utente Sistema (SYSADMIN)
 * 
 * Form per creare nuovi utenti AMMINISTRATORE o TECNICO.
 * Accessibile solo da SYSADMIN.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

startSecureSession();
requireRole('SYSADMIN');

$userId = getCurrentUserId();
$userName = getCurrentUserName();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea Utente Sistema | UNIME-ACQUE</title>
    
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
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .page-header .subtitle {
            color: var(--color-text-muted);
        }
        
        .form-card {
            max-width: 800px;
            margin: 0 auto;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 3rem;
            backdrop-filter: blur(10px);
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
        
        .form-input,
        .form-select {
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
        
        .form-select option {
            background: #0a4d68;
            color: #ffffff;
            padding: 1rem;
        }
        
        .form-select option:hover,
        .form-select option:focus,
        .form-select option:checked {
            background: #088395;
            color: #ffffff;
        }
        
        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--color-primary-light);
            box-shadow: 0 0 0 3px rgba(5, 191, 219, 0.15);
            background: rgba(255, 255, 255, 0.08);
        }
        
        .form-input::placeholder {
            color: var(--color-text-muted);
            opacity: 0.5;
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
                        <li><a href="sysadmin.php">Dashboard</a></li>
                        <li><a href="../auth/logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="page-content">
        <div class="container">
            
            <div class="page-header">
                <h1>👥 Crea Nuovo Utente Sistema</h1>
                <p class="subtitle">Aggiungi un nuovo utente con ruolo AMMINISTRATORE o TECNICO</p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>" style="max-width: 800px; margin: 0 auto 2rem;">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-card">
                
                <div class="info-box">
                    <p>
                        ℹ️ <strong>Nota:</strong> Tutti i campi contrassegnati con <span style="color: var(--color-danger);">*</span> 
                        sono obbligatori. Una password temporanea verrà generata automaticamente e mostrata dopo la creazione.
                    </p>
                </div>
                
                <form action="sysadmin-process-crea-utente.php" method="POST" id="createUserForm">
                    <?php echo csrfField(); ?>
                    
                    <!-- Tipo Utente -->
                    <div class="form-section">
                        <h3>Tipo di Utenza</h3>
                        
                        <div class="form-group full-width">
                            <label class="form-label" for="ruolo">
                                Ruolo Utente <span class="required">*</span>
                            </label>
                            <select id="ruolo" name="ruolo" class="form-select" required>
                                <option value="">-- Seleziona Ruolo --</option>
                                <option value="AMMINISTRATORE">Amministratore</option>
                                <option value="TECNICO">Tecnico</option>
                            </select>
                            <p class="form-hint">
                                <strong>Amministratore:</strong> Gestisce utenti, contratti e fatture.<br>
                                <strong>Tecnico:</strong> Gestisce contatori, letture e segnalazioni tecniche.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Dati Anagrafici -->
                    <div class="form-section">
                        <h3>Dati Anagrafici</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="nome">
                                    Nome <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="nome" 
                                    name="nome" 
                                    class="form-input" 
                                    placeholder="Mario"
                                    required
                                    minlength="2"
                                    maxlength="50"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="cognome">
                                    Cognome <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="cognome" 
                                    name="cognome" 
                                    class="form-input" 
                                    placeholder="Rossi"
                                    required
                                    minlength="2"
                                    maxlength="50"
                                >
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="codice_fiscale">
                                    Codice Fiscale <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="codice_fiscale" 
                                    name="codice_fiscale" 
                                    class="form-input" 
                                    placeholder="RSSMRA80A01H501Z"
                                    required
                                    maxlength="16"
                                    pattern="[A-Z0-9]{16}"
                                    style="text-transform: uppercase;"
                                >
                                <p class="form-hint">16 caratteri alfanumerici</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="data_nascita">
                                    Data di Nascita <span class="required">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="data_nascita" 
                                    name="data_nascita" 
                                    class="form-input"
                                    required
                                    max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>"
                                >
                                <p class="form-hint">Deve essere maggiorenne</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dati di Contatto -->
                    <div class="form-section">
                        <h3>Dati di Contatto</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="email">
                                    Email <span class="required">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="form-input" 
                                    placeholder="mario.rossi@ecelesti.it"
                                    required
                                    maxlength="100"
                                >
                                <p class="form-hint">Verrà usata per l'accesso al sistema</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="telefono">
                                    Telefono <span class="required">*</span>
                                </label>
                                <input 
                                    type="tel" 
                                    id="telefono" 
                                    name="telefono" 
                                    class="form-input" 
                                    placeholder="3331234567"
                                    required
                                    pattern="[0-9]{10}"
                                    maxlength="10"
                                >
                                <p class="form-hint">10 cifre senza spazi</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pulsanti Azione -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='sysadmin.php'">
                            Annulla
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Crea Utente
                        </button>
                    </div>
                    
                </form>
                
            </div>
            
        </div>
    </main>
    
    <script>
        // Auto-uppercase per codice fiscale
        const cfInput = document.getElementById('codice_fiscale');
        cfInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Validazione form
        const form = document.getElementById('createUserForm');
        
        form.addEventListener('submit', function(e) {
            const ruolo = document.getElementById('ruolo').value;
            const nome = document.getElementById('nome').value.trim();
            const cognome = document.getElementById('cognome').value.trim();
            const cf = document.getElementById('codice_fiscale').value.trim();
            const dataNascita = document.getElementById('data_nascita').value;
            const email = document.getElementById('email').value.trim();
            const telefono = document.getElementById('telefono').value.trim();
            
            // Verifica campi obbligatori
            if (!ruolo || !nome || !cognome || !cf || !dataNascita || !email || !telefono) {
                e.preventDefault();
                alert('Per favore, compila tutti i campi obbligatori.');
                return false;
            }
            
            // Validazione codice fiscale
            const cfRegex = /^[A-Z0-9]{16}$/;
            if (!cfRegex.test(cf)) {
                e.preventDefault();
                alert('Il codice fiscale deve essere di 16 caratteri alfanumerici.');
                return false;
            }
            
            // Validazione email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Inserisci un indirizzo email valido.');
                return false;
            }
            
            // Validazione telefono
            const telRegex = /^[0-9]{10}$/;
            if (!telRegex.test(telefono)) {
                e.preventDefault();
                alert('Il telefono deve essere di 10 cifre.');
                return false;
            }
            
            // Validazione maggiore età
            const birthDate = new Date(dataNascita);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (age < 18 || (age === 18 && monthDiff < 0)) {
                e.preventDefault();
                alert('L\'utente deve essere maggiorenne (almeno 18 anni).');
                return false;
            }
            
            // Conferma creazione
            const ruoloText = ruolo === 'AMMINISTRATORE' ? 'Amministratore' : 'Tecnico';
            if (!confirm(`Confermi la creazione dell'utente ${nome} ${cognome} con ruolo ${ruoloText}?`)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
