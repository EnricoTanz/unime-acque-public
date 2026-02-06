<?php
/**
 * UNIME-ACQUE - Utente Creato con Successo
 * 
 * Pagina di conferma creazione utente con password temporanea.
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

// Verifica che ci siano dati utente appena creato
if (!isset($_SESSION['new_user_data'])) {
    header('Location: sysadmin.php');
    exit;
}

$userData = $_SESSION['new_user_data'];

// Rimuovi i dati dalla sessione (one-time view)
unset($_SESSION['new_user_data']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utente Creato | UNIME-ACQUE</title>
    
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
        
        .success-card {
            max-width: 700px;
            margin: 0 auto;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            backdrop-filter: blur(10px);
        }
        
        .success-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            animation: bounce 1s ease infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .success-card h1 {
            color: var(--color-success);
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .success-card .subtitle {
            color: var(--color-text-muted);
            font-size: 1.1rem;
            margin-bottom: 2.5rem;
        }
        
        .user-info {
            background: rgba(0, 200, 151, 0.1);
            border: 1px solid var(--color-success);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .user-info h3 {
            color: var(--color-success);
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 200, 151, 0.2);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--color-text-muted);
        }
        
        .info-value {
            color: var(--color-text-light);
            font-weight: 500;
        }
        
        .password-box {
            background: rgba(255, 193, 7, 0.1);
            border: 2px solid var(--color-warning);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .password-box h3 {
            color: var(--color-warning);
            font-size: 1.25rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .password-display {
            background: rgba(0, 0, 0, 0.3);
            padding: 1.5rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-accent);
            letter-spacing: 3px;
            margin-bottom: 1rem;
            word-break: break-all;
            position: relative;
        }
        
        .copy-btn {
            background: var(--color-warning);
            color: var(--color-bg-dark);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .copy-btn:hover {
            background: #ffb300;
            transform: translateY(-2px);
        }
        
        .warning-text {
            color: var(--color-warning);
            font-size: 0.9rem;
            margin-top: 1rem;
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            flex: 1;
            padding: 1.25rem;
            font-size: 1rem;
        }
        
        @media screen and (max-width: 768px) {
            .info-row {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
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
            
            <div class="success-card">
                <div class="success-icon">✅</div>
                
                <h1>Utente Creato con Successo!</h1>
                <p class="subtitle">
                    L'utente è stato aggiunto al sistema e può ora accedere con le credenziali generate.
                </p>
                
                <!-- Informazioni Utente -->
                <div class="user-info">
                    <h3>📋 Dettagli Utente</h3>
                    
                    <div class="info-row">
                        <span class="info-label">Nome Completo:</span>
                        <span class="info-value"><?php echo htmlspecialchars($userData['nome'] . ' ' . $userData['cognome']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($userData['email']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Ruolo:</span>
                        <span class="info-value"><?php echo htmlspecialchars($userData['ruolo']); ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">ID Utente:</span>
                        <span class="info-value">#<?php echo htmlspecialchars($userData['id']); ?></span>
                    </div>
                </div>
                
                <!-- Password Temporanea -->
                <div class="password-box">
                    <h3>🔑 Password Temporanea</h3>
                    
                    <div class="password-display" id="passwordDisplay">
                        <?php echo htmlspecialchars($userData['password_temp']); ?>
                    </div>
                    
                    <button class="copy-btn" onclick="copyPassword()">
                        📋 Copia Password
                    </button>
                    
                    <p class="warning-text">
                        ⚠️ <strong>IMPORTANTE:</strong> Questa password verrà mostrata solo una volta. 
                        Copiala e comunicala all'utente in modo sicuro.
                    </p>
                </div>
                
                <!-- Istruzioni -->
                <div style="text-align: left; background: rgba(5, 191, 219, 0.1); border: 1px solid var(--color-primary-light); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
                    <h4 style="color: var(--color-primary-light); margin-bottom: 1rem;">📝 Prossimi Passi</h4>
                    <ol style="margin: 0; padding-left: 1.5rem; color: var(--color-text-light);">
                        <li style="margin-bottom: 0.5rem;">Copia la password temporanea</li>
                        <li style="margin-bottom: 0.5rem;">Comunica le credenziali all'utente in modo sicuro</li>
                        <li style="margin-bottom: 0.5rem;">L'utente potrà accedere con email e password temporanea</li>
                        <li>Si consiglia di cambiare la password al primo accesso</li>
                    </ol>
                </div>
                
                <!-- Pulsanti Azione -->
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="window.location.href='sysadmin-crea-utente.php'">
                        Crea Altro Utente
                    </button>
                    <button class="btn btn-secondary" onclick="window.location.href='sysadmin.php'" style="background: rgba(255, 255, 255, 0.05); color: var(--color-text-light); border: 1px solid var(--glass-border);">
                        Torna alla Dashboard
                    </button>
                </div>
            </div>
            
        </div>
    </main>
    
    <script>
        function copyPassword() {
            const passwordText = document.getElementById('passwordDisplay').textContent.trim();
            const btn = document.querySelector('.copy-btn');
            
            // Copia negli appunti
            navigator.clipboard.writeText(passwordText).then(function() {
                // Feedback visivo
                const originalText = btn.innerHTML;
                btn.innerHTML = '✓ Password Copiata!';
                btn.style.background = 'var(--color-success)';
                
                setTimeout(function() {
                    btn.innerHTML = originalText;
                    btn.style.background = 'var(--color-warning)';
                }, 2000);
            }).catch(function(err) {
                alert('Errore durante la copia: ' + err);
            });
        }
        
        // Avviso se l'utente cerca di lasciare la pagina
        let warningShown = false;
        window.addEventListener('beforeunload', function(e) {
            if (!warningShown) {
                warningShown = true;
                e.preventDefault();
                e.returnValue = 'Hai copiato la password temporanea? Non sarà più possibile visualizzarla.';
                return e.returnValue;
            }
        });
    </script>
</body>
</html>
