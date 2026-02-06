<?php
/**
 * UNIME-ACQUE - Contatti
 * 
 * Pagina di contatto con form per inviare messaggi.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Gestione form di contatto
$formSubmitted = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validazione dei campi
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $messaggio = isset($_POST['messaggio']) ? trim($_POST['messaggio']) : '';
    
    // Validazione nome
    if (empty($nome)) {
        $errors[] = 'Il nome è obbligatorio.';
    } elseif (strlen($nome) < 2) {
        $errors[] = 'Il nome deve contenere almeno 2 caratteri.';
    }
    
    // Validazione email con filter_var come richiesto nei requisiti
    if (empty($email)) {
        $errors[] = 'L\'email è obbligatoria.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Inserisci un indirizzo email valido.';
    }
    
    // Validazione messaggio
    if (empty($messaggio)) {
        $errors[] = 'Il messaggio è obbligatorio.';
    } elseif (strlen($messaggio) < 10) {
        $errors[] = 'Il messaggio deve contenere almeno 10 caratteri.';
    }
    
    // Se non ci sono errori, considera il form come inviato
    if (empty($errors)) {
        $formSubmitted = true;
        // In una implementazione reale, qui si invierebbe l'email
        // Per ora mostriamo solo la conferma
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contatta Ecelesti S.p.A. per informazioni sui nostri servizi di gestione delle risorse idriche.">
    <meta name="keywords" content="contatti, Ecelesti, assistenza, supporto, UNIME-ACQUE">
    <meta name="author" content="Ecelesti S.p.A.">
    
    <title>Contatti | Ecelesti S.p.A. - UNIME-ACQUE</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
    
    <style>
        .page-hero {
            padding: 10rem 1.5rem 4rem;
            text-align: center;
        }
        
        .page-hero h1 {
            font-size: clamp(2.5rem, 6vw, 4rem);
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--color-text-light) 0%, var(--color-primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .page-hero p {
            font-size: 1.25rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .contact-section {
            padding: 4rem 0 5rem;
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 4rem;
            align-items: start;
        }
        
        .contact-info h2 {
            color: var(--color-accent);
            margin-bottom: 2rem;
            font-size: 1.75rem;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            transition: all var(--transition-fast);
        }
        
        .contact-item:hover {
            border-color: var(--color-primary-light);
            transform: translateX(5px);
        }
        
        .contact-item .icon {
            font-size: 2rem;
            flex-shrink: 0;
        }
        
        .contact-item h4 {
            color: var(--color-text-light);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        
        .contact-item p {
            margin: 0;
            font-size: 0.95rem;
        }
        
        .contact-item a {
            color: var(--color-accent);
        }
        
        .contact-form-wrapper {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2.5rem;
        }
        
        .contact-form-wrapper h2 {
            color: var(--color-text-light);
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }
        
        .contact-form-wrapper .subtitle {
            color: var(--color-text-muted);
            margin-bottom: 2rem;
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
        .form-textarea {
            width: 100%;
            padding: 1rem 1.25rem;
            font-family: var(--font-body);
            font-size: 1rem;
            color: var(--color-text-light);
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            transition: all var(--transition-fast);
        }
        
        .form-textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--color-primary-light);
            box-shadow: 0 0 0 3px rgba(5, 191, 219, 0.15);
            background: rgba(255, 255, 255, 0.05);
        }
        
        .form-input::placeholder,
        .form-textarea::placeholder {
            color: var(--color-text-muted);
            opacity: 0.5;
        }
        
        .btn-submit {
            width: 100%;
            padding: 1.25rem;
            font-size: 1rem;
            margin-top: 1rem;
        }
        
        .success-message {
            text-align: center;
            padding: 3rem 2rem;
        }
        
        .success-message .icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            animation: bounce 1s ease infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .success-message h3 {
            color: var(--color-success);
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }
        
        .success-message p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .error-list {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid var(--color-danger);
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .error-list ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .error-list li {
            color: var(--color-danger);
            padding: 0.25rem 0;
            font-size: 0.9rem;
        }
        
        .error-list li::before {
            content: '⚠️ ';
        }
        
        .map-placeholder {
            margin-top: 2rem;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
        }
        
        .map-placeholder .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .map-placeholder p {
            margin: 0;
        }
        
        @media screen and (max-width: 992px) {
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="main-header" id="mainHeader">
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="logo">
                    <span class="logo-company">Ecelesti S.p.A.</span>
                    <span class="logo-brand">UNIME<span>-ACQUE</span></span>
                </a>
                
                <nav class="main-nav" id="mainNav">
                    <ul class="nav-menu">
                        <li><a href="chi-siamo.php">Chi Siamo</a></li>
                        <li><a href="cosa-facciamo.php">Cosa Facciamo</a></li>
                        <li><a href="contatti.php" style="color: var(--color-accent);">Contatti</a></li>
                        <li><a href="dicono-di-noi.php">Dicono di Noi</a></li>
                        <li><a href="../auth/login.php" class="btn-login">Effettua il Login</a></li>
                    </ul>
                </nav>
                
                <button class="menu-toggle" id="menuToggle" aria-label="Apri menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <main>
        <!-- PAGE HERO -->
        <section class="page-hero">
            <div class="container">
                <h1>Contattaci</h1>
                <p>Siamo qui per rispondere alle tue domande e fornirti tutte le informazioni di cui hai bisogno.</p>
            </div>
        </section>

        <!-- CONTACT SECTION -->
        <section class="contact-section">
            <div class="container">
                <div class="contact-grid">
                    <!-- Contact Info -->
                    <div class="contact-info">
                        <h2>Come Raggiungerci</h2>
                        
                        <div class="contact-item">
                            <span class="icon">📧</span>
                            <div>
                                <h4>Email</h4>
                                <p><a href="mailto:enrico.celesti@studenti.unime.it">enrico.celesti@studenti.unime.it</a></p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <span class="icon">🏛️</span>
                            <div>
                                <h4>Sede Operativa</h4>
                                <p>Università degli Studi di Messina<br>Dipartimento di Scienze Matematiche e Informatiche<br>98166 Messina (ME)</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <span class="icon">🕐</span>
                            <div>
                                <h4>Orari di Assistenza</h4>
                                <p>Lunedì - Venerdì: 9:00 - 18:00<br>Sabato: 9:00 - 13:00<br>Domenica: Chiuso</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <span class="icon">📱</span>
                            <div>
                                <h4>Social</h4>
                                <p>Seguici sui nostri canali social per rimanere aggiornato sulle novità di UNIME-ACQUE.</p>
                            </div>
                        </div>
                        
                        <div class="map-placeholder">
                            <div class="icon">🗺️</div>
                            <p>Università degli Studi di Messina</p>
                        </div>
                    </div>
                    
                    <!-- Contact Form -->
                    <div class="contact-form-wrapper">
                        <?php if ($formSubmitted): ?>
                            <!-- Success Message -->
                            <div class="success-message">
                                <div class="icon">✅</div>
                                <h3>Messaggio Inviato!</h3>
                                <p>
                                    Grazie per averci contattato. Abbiamo ricevuto la tua richiesta 
                                    e ti risponderemo il prima possibile all'indirizzo email che hai fornito.
                                </p>
                                <a href="contatti.php" class="btn btn-primary">Invia un altro messaggio</a>
                            </div>
                        <?php else: ?>
                            <h2>Inviaci un Messaggio</h2>
                            <p class="subtitle">Compila il form sottostante e ti risponderemo al più presto.</p>
                            
                            <?php if (!empty($errors)): ?>
                                <div class="error-list">
                                    <ul>
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="contatti.php">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label" for="nome">
                                            Nome e Cognome <span class="required">*</span>
                                        </label>
                                        <input 
                                            type="text" 
                                            id="nome" 
                                            name="nome" 
                                            class="form-input" 
                                            placeholder="Mario Rossi"
                                            value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>"
                                            required
                                        >
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label" for="email">
                                            Email <span class="required">*</span>
                                        </label>
                                        <input 
                                            type="email" 
                                            id="email" 
                                            name="email" 
                                            class="form-input" 
                                            placeholder="mario.rossi@email.com"
                                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                            required
                                        >
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="oggetto">
                                        Oggetto
                                    </label>
                                    <input 
                                        type="text" 
                                        id="oggetto" 
                                        name="oggetto" 
                                        class="form-input" 
                                        placeholder="Di cosa hai bisogno?"
                                        value="<?php echo isset($_POST['oggetto']) ? htmlspecialchars($_POST['oggetto']) : ''; ?>"
                                    >
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="messaggio">
                                        Messaggio <span class="required">*</span>
                                    </label>
                                    <textarea 
                                        id="messaggio" 
                                        name="messaggio" 
                                        class="form-textarea" 
                                        placeholder="Scrivi qui il tuo messaggio..."
                                        required
                                    ><?php echo isset($_POST['messaggio']) ? htmlspecialchars($_POST['messaggio']) : ''; ?></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-submit">
                                    Invia Richiesta
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <span class="company">ECELESTI S.P.A.</span>
                    <span class="brand">UNIME-ACQUE</span>
                </div>
                
                <ul class="footer-links">
                    <li><a href="chi-siamo.php">Chi Siamo</a></li>
                    <li><a href="cosa-facciamo.php">Cosa Facciamo</a></li>
                    <li><a href="contatti.php">Contatti</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                </ul>
                
                <p class="footer-copyright">
                    &copy; <?php echo date('Y'); ?> Ecelesti S.p.A. - Tutti i diritti riservati<br>
                    <small>Progetto accademico UNIME - Enrico Celesti (Mat. 460896)</small>
                </p>
            </div>
        </div>
    </footer>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const mainNav = document.getElementById('mainNav');
        
        menuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
            this.classList.toggle('active');
        });
        
        const header = document.getElementById('mainHeader');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        const navLinks = document.querySelectorAll('.nav-menu a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                mainNav.classList.remove('active');
                menuToggle.classList.remove('active');
            });
        });
    </script>
</body>
</html>
