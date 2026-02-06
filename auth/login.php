<?php
/**
 * UNIME-ACQUE - Login
 * 
 * Pagina di autenticazione per accedere all'area riservata.
 * L'utente si autentica con email e password.
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

// Se già loggato, reindirizza alla dashboard appropriata
if (isLoggedIn()) {
    $role = getCurrentUserRole();
    switch ($role) {
        case 'AMMINISTRATORE':
            header('Location: ../dashboard/amministratore.php');
            break;
        case 'TECNICO':
            header('Location: ../dashboard/tecnico.php');
            break;
        case 'CLIENTE':
            header('Location: ../dashboard/cliente.php');
            break;
        case 'SYSADMIN':
            header('Location: ../dashboard/sysadmin.php');
            break;
        default:
            logoutUser();
            break;
    }
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
    <meta name="description" content="Accedi all'area riservata UNIME-ACQUE per gestire contratti, consumi e fatture.">
    <title>Login | UNIME-ACQUE - Ecelesti S.p.A.</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
    
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
        }
        
        .login-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 3rem;
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-strong);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .login-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .login-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--color-text-light);
        }
        
        .login-header h1 span {
            color: var(--color-primary-light);
        }
        
        .login-header .subtitle {
            color: var(--color-text-muted);
            font-size: 0.95rem;
        }
        
        .login-form {
            margin-top: 2rem;
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
        
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--color-text-muted);
        }
        
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .forgot-password {
            color: var(--color-accent);
            font-weight: 500;
        }
        
        .forgot-password:hover {
            color: var(--color-primary-light);
        }
        
        .btn-login {
            width: 100%;
            padding: 1.25rem;
            font-size: 1rem;
            margin-top: 1rem;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--glass-border);
        }
        
        .back-home {
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }
        
        .back-home a {
            color: var(--color-accent);
            font-weight: 600;
        }
        
        .back-home a:hover {
            color: var(--color-primary-light);
        }
        
        /* Alert message */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border: 1px solid;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border-color: var(--color-danger);
            color: var(--color-danger);
        }
        
        .alert-success {
            background: rgba(0, 200, 151, 0.1);
            border-color: var(--color-success);
            color: var(--color-success);
        }
        
        .alert-warning {
            background: rgba(255, 193, 7, 0.1);
            border-color: var(--color-warning);
            color: var(--color-warning);
        }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-card">
                <!-- Header -->
                <div class="login-header">
                    <div class="login-icon">💧</div>
                    <h1>UNIME<span>-ACQUE</span></h1>
                    <p class="subtitle">Accedi alla tua area riservata</p>
                </div>
                
                <!-- Flash Message -->
                <?php if ($flashMessage): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                        <?php echo htmlspecialchars($flashMessage['message']); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form action="process-login.php" method="POST" class="login-form" id="loginForm">
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
                    
                    <!-- Password -->
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="password-toggle">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="••••••••"
                                required
                            >
                            <button type="button" class="password-toggle-btn" id="togglePassword" aria-label="Mostra password">
                                👁️
                            </button>
                        </div>
                    </div>
                    
                    <!-- Footer: Remember Me + Forgot Password -->
                    <div class="form-footer">
                        <label class="remember-me">
                            <input type="checkbox" name="remember_me" id="remember_me">
                            <span>Ricordami</span>
                        </label>
                        <a href="forgot-password.php" class="forgot-password">Password dimenticata?</a>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-login">
                        Accedi
                    </button>
                </form>
                
                <!-- Footer -->
                <div class="login-footer">
                    <p class="back-home">
                        <a href="../index.php">← Torna alla Homepage</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });
        
        // Form validation
        const loginForm = document.getElementById('loginForm');
        
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Per favore, compila tutti i campi.');
                return false;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Inserisci un indirizzo email valido.');
                return false;
            }
        });
    </script>
</body>
</html>
