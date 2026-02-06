<?php
/**
 * UNIME-ACQUE - Password Dimenticata
 * 
 * Pagina per il recupero della password tramite email e codice fiscale.
 * L'utente può reimpostare la password se fornisce email e CF corretti.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

// Include dipendenze
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Avvia la sessione
startSecureSession();

// Se già loggato, reindirizza
if (isLoggedIn()) {
    header('Location: ../dashboard/');
    exit;
}

// Recupera messaggio flash (se presente)
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recupera la tua password UNIME-ACQUE.">
    <title>Password Dimenticata | UNIME-ACQUE - Ecelesti S.p.A.</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
    
    <style>
        .forgot-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .forgot-container {
            width: 100%;
            max-width: 500px;
        }
        
        .forgot-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 3rem;
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-strong);
        }
        
        .forgot-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .forgot-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .forgot-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: var(--color-text-light);
        }
        
        .forgot-header .subtitle {
            color: var(--color-text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
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
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--color-text-light);
            font-size: 0.9rem;
        }
        
        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            font-family: var(--font-body);
            font-size: 1rem;
            color: var(--color-text-light);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            transition: all var(--transition-fast);
        }
        
        .form-input:focus {
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
        
        .btn-reset {
            width: 100%;
            padding: 1.25rem;
            font-size: 1rem;
            margin-top: 0.5rem;
        }
        
        .forgot-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--glass-border);
        }
        
        .back-login {
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }
        
        .back-login a {
            color: var(--color-accent);
            font-weight: 600;
        }
        
        .back-login a:hover {
            color: var(--color-primary-light);
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle-btn {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--color-text-muted);
            cursor: pointer;
            font-size: 1.25rem;
            padding: 0.5rem;
            transition: color var(--transition-fast);
        }
        
        .password-toggle-btn:hover {
            color: var(--color-text-light);
        }
    </style>
</head>
<body>
    <div class="forgot-page">
        <div class="forgot-container">
            <div class="forgot-card">
                <!-- Header -->
                <div class="forgot-header">
                    <div class="forgot-icon">🔑</div>
                    <h1>Password Dimenticata?</h1>
                    <p class="subtitle">
                        Inserisci la tua email e il tuo codice fiscale per reimpostare la password.
                    </p>
                </div>
                
                <!-- Info Box -->
                <div class="info-box">
                    <p>
                        ℹ️ Per motivi di sicurezza, ti verra' di inserire il codice fiscale o la partita IVA 
                        associata al tuo account prima di poter modificare la password.
                    </p>
                </div>
                
                <!-- Flash Message -->
                <?php if ($flashMessage): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                        <?php echo htmlspecialchars($flashMessage['message']); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Reset Form -->
                <form action="process-forgot-password.php" method="POST" id="forgotForm">
                    <!-- CSRF Token -->
                    <?php echo csrfField(); ?>
                    
                    <!-- Email -->
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="mario.rossi@email.com"
                            required
                            autofocus
                        >
                    </div>
                    
                    <!-- Codice Fiscale -->
                    <div class="form-group">
                        <label class="form-label" for="codice_fiscale">Codice Fiscale / Partita IVA</label>
                        <input 
                            type="text" 
                            id="codice_fiscale" 
                            name="codice_fiscale" 
                            class="form-input" 
                            placeholder="RSSMRA80A01F205X"
                            maxlength="16"
                            required
                        >
                        <p class="form-hint">Inserisci il codice fiscale (16 caratteri) o la partita IVA (11 cifre).</p>
                    </div>
                    
                    <!-- Nuova Password -->
                    <div class="form-group">
                        <label class="form-label" for="new_password">Nuova Password</label>
                        <div class="password-toggle">
                            <input 
                                type="password" 
                                id="new_password" 
                                name="new_password" 
                                class="form-input" 
                                placeholder="••••••••"
                                minlength="8"
                                required
                            >
                            <button type="button" class="password-toggle-btn" id="togglePassword1" aria-label="Mostra password">
                                👁️
                            </button>
                        </div>
                        <p class="form-hint">Minimo 8 caratteri, almeno una maiuscola, una minuscola e un numero.</p>
                    </div>
                    
                    <!-- Conferma Password -->
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Conferma Nuova Password</label>
                        <div class="password-toggle">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                placeholder="••••••••"
                                minlength="8"
                                required
                            >
                            <button type="button" class="password-toggle-btn" id="togglePassword2" aria-label="Mostra password">
                                👁️
                            </button>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-reset">
                        Reimposta Password
                    </button>
                </form>
                
                <!-- Footer -->
                <div class="forgot-footer">
                    <p class="back-login">
                        <a href="login.php">← Torna al Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        function setupPasswordToggle(toggleBtnId, passwordInputId) {
            const toggleBtn = document.getElementById(toggleBtnId);
            const passwordInput = document.getElementById(passwordInputId);
            
            toggleBtn.addEventListener('click', function() {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                this.textContent = type === 'password' ? '👁️' : '🙈';
            });
        }
        
        setupPasswordToggle('togglePassword1', 'new_password');
        setupPasswordToggle('togglePassword2', 'confirm_password');
        
        // Form validation
        const forgotForm = document.getElementById('forgotForm');
        
        forgotForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const codiceFiscale = document.getElementById('codice_fiscale').value.trim().toUpperCase();
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Inserisci un indirizzo email valido.');
                return false;
            }
            
            // Codice Fiscale validation (16 chars alphanumeric or 11 numeric)
            const cfRegex = /^[A-Z0-9]{11,16}$/;
            if (!cfRegex.test(codiceFiscale)) {
                e.preventDefault();
                alert('Il codice fiscale deve essere di 16 caratteri o la partita IVA di 11 cifre.');
                return false;
            }
            
            // Password match validation
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Le password non corrispondono.');
                return false;
            }
            
            // Password strength validation
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('La password deve contenere almeno 8 caratteri.');
                return false;
            }
            
            if (!/[A-Z]/.test(newPassword)) {
                e.preventDefault();
                alert('La password deve contenere almeno una lettera maiuscola.');
                return false;
            }
            
            if (!/[a-z]/.test(newPassword)) {
                e.preventDefault();
                alert('La password deve contenere almeno una lettera minuscola.');
                return false;
            }
            
            if (!/[0-9]/.test(newPassword)) {
                e.preventDefault();
                alert('La password deve contenere almeno un numero.');
                return false;
            }
        });
        
        // Auto-uppercase codice fiscale
        const codiceFiscaleInput = document.getElementById('codice_fiscale');
        codiceFiscaleInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>
