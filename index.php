<?php
/**
 * UNIME-ACQUE - Homepage
 * 
 * Pagina principale pubblica del portale di gestione delle forniture idriche.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UNIME-ACQUE - Portale di gestione delle forniture idriche per i cittadini di Messina. Consulta contratti, consumi e bollette online.">
    <meta name="keywords" content="acqua, forniture idriche, Messina, bollette, consumi, UNIME">
    <meta name="author" content="Ecelesti S.p.A.">
    
    <title>UNIME-ACQUE | Ecelesti S.p.A. - Gestione Forniture Idriche</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
    
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
    
    <style>
        /* Cookie Banner Styles */
        .cookie-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(4, 20, 33, 0.98);
            border-top: 1px solid var(--glass-border);
            padding: 1.5rem;
            z-index: 9999;
            transform: translateY(100%);
            transition: transform 0.4s ease;
            backdrop-filter: blur(10px);
        }
        
        .cookie-banner.show {
            transform: translateY(0);
        }
        
        .cookie-banner.hidden {
            display: none;
        }
        
        .cookie-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .cookie-text {
            flex: 1;
            min-width: 300px;
        }
        
        .cookie-text h4 {
            color: var(--color-text-light);
            font-size: 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .cookie-text p {
            font-size: 0.9rem;
            margin: 0;
            color: var(--color-text-muted);
        }
        
        .cookie-text a {
            color: var(--color-accent);
        }
        
        .cookie-buttons {
            display: flex;
            gap: 1rem;
            flex-shrink: 0;
        }
        
        .cookie-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-family: var(--font-heading);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }
        
        .cookie-btn.accept {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: var(--color-text-light);
        }
        
        .cookie-btn.accept:hover {
            background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-accent) 100%);
            transform: translateY(-2px);
        }
        
        .cookie-btn.reject {
            background: transparent;
            color: var(--color-text-muted);
            border: 1px solid var(--glass-border);
        }
        
        .cookie-btn.reject:hover {
            border-color: var(--color-text-muted);
            color: var(--color-text-light);
        }
        
        @media screen and (max-width: 768px) {
            .cookie-content {
                flex-direction: column;
                text-align: center;
            }
            
            .cookie-buttons {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="main-header" id="mainHeader">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <span class="logo-company">Ecelesti S.p.A.</span>
                    <span class="logo-brand">UNIME<span>-ACQUE</span></span>
                </a>
                
                <nav class="main-nav" id="mainNav">
                    <ul class="nav-menu">
                        <li><a href="pages/chi-siamo.php">Chi Siamo</a></li>
                        <li><a href="pages/cosa-facciamo.php">Cosa Facciamo</a></li>
                        <li><a href="pages/contatti.php">Contatti</a></li>
                        <li><a href="pages/dicono-di-noi.php">Dicono di Noi</a></li>
                        <li><a href="auth/login.php" class="btn-login">Effettua il Login</a></li>
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
        <section class="hero">
            <svg width="0" height="0" style="position:absolute;">
                <defs>
                    <linearGradient id="water-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#05bfdb;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#00ffca;stop-opacity:1" />
                    </linearGradient>
                </defs>
            </svg>
            
            <div class="hero-content">
                <div class="hero-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" fill="url(#water-gradient)"/>
                    </svg>
                </div>
                
                <p class="hero-company">Ecelesti S.p.A.</p>
                <h1 class="hero-title">UNIME<span>-ACQUE</span></h1>
                <p class="hero-subtitle">
                    Il portale dedicato alla gestione delle forniture idriche per i cittadini e le aziende di Messina. 
                    Consulta contratti, monitora i consumi e gestisci le tue bollette online.
                </p>
                
                <div class="hero-buttons">
                    <a href="auth/login.php" class="btn btn-primary">Accedi all'Area Riservata</a>
                </div>
            </div>
            
            <div class="scroll-indicator">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M7 13l5 5 5-5M7 6l5 5 5-5"/>
                </svg>
            </div>
        </section>

        <section class="section" id="servizi">
            <div class="container">
                <div class="section-title">
                    <h2>I Nostri Servizi</h2>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;">
                    <div class="card">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                        <h3>Monitora i Consumi</h3>
                        <p>Visualizza lo storico delle tue letture giornaliere e tieni sotto controllo i tuoi consumi idrici in tempo reale.</p>
                    </div>
                    
                    <div class="card">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
                        <h3>Paga Online</h3>
                        <p>Gestisci le tue fatture e effettua pagamenti in modo sicuro direttamente dal portale.</p>
                    </div>
                    
                    <div class="card">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
                        <h3>Gestisci Contratti</h3>
                        <p>Accedi ai tuoi contratti attivi e visualizza tutti i dettagli delle tue forniture.</p>
                    </div>
                    
                    <div class="card">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🎫</div>
                        <h3>Assistenza Dedicata</h3>
                        <p>Apri segnalazioni per problemi tecnici o commerciali e monitora lo stato delle tue richieste.</p>
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
                    <li><a href="pages/chi-siamo.php">Chi Siamo</a></li>
                    <li><a href="pages/cosa-facciamo.php">Cosa Facciamo</a></li>
                    <li><a href="pages/contatti.php">Contatti</a></li>
                    <li><a href="pages/privacy.php">Privacy Policy</a></li>
                </ul>
                
                <p class="footer-copyright">
                    &copy; <?php echo date('Y'); ?> Ecelesti S.p.A. - Tutti i diritti riservati<br>
                    <small>Progetto accademico UNIME - Enrico Celesti (Mat. 460896)</small>
                </p>
            </div>
        </div>
    </footer>

    <!-- COOKIE BANNER -->
    <div class="cookie-banner" id="cookieBanner">
        <div class="cookie-content">
            <div class="cookie-text">
                <h4>🍪 Utilizziamo i Cookie</h4>
                <p>
                    Questo sito utilizza cookie tecnici necessari per il corretto funzionamento dei servizi. 
                    Proseguendo la navigazione accetti l'utilizzo dei cookie. 
                    <a href="pages/privacy.php">Maggiori informazioni</a>
                </p>
            </div>
            <div class="cookie-buttons">
                <button class="cookie-btn reject" id="cookieReject">Rifiuta</button>
                <button class="cookie-btn accept" id="cookieAccept">Accetta</button>
            </div>
        </div>
    </div>

    <script>
        // Toggle menu mobile
        const menuToggle = document.getElementById('menuToggle');
        const mainNav = document.getElementById('mainNav');
        
        menuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
            this.classList.toggle('active');
        });
        
        // Header scroll effect
        const header = document.getElementById('mainHeader');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Chiudi menu mobile quando si clicca su un link
        const navLinks = document.querySelectorAll('.nav-menu a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                mainNav.classList.remove('active');
                menuToggle.classList.remove('active');
            });
        });
        
        // Cookie Banner Management
        const cookieBanner = document.getElementById('cookieBanner');
        const cookieAccept = document.getElementById('cookieAccept');
        const cookieReject = document.getElementById('cookieReject');
        
        // Check if user has already made a choice
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }
        
        function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = `expires=${date.toUTCString()}`;
            document.cookie = `${name}=${value};${expires};path=/;SameSite=Strict`;
        }
        
        // Show banner if no choice has been made
        const cookieConsent = getCookie('cookie_consent');
        if (!cookieConsent) {
            setTimeout(function() {
                cookieBanner.classList.add('show');
            }, 1000);
        }
        
        // Accept cookies
        cookieAccept.addEventListener('click', function() {
            setCookie('cookie_consent', 'accepted', 365);
            cookieBanner.classList.remove('show');
            setTimeout(function() {
                cookieBanner.classList.add('hidden');
            }, 400);
        });
        
        // Reject cookies
        cookieReject.addEventListener('click', function() {
            setCookie('cookie_consent', 'rejected', 365);
            cookieBanner.classList.remove('show');
            setTimeout(function() {
                cookieBanner.classList.add('hidden');
            }, 400);
        });
    </script>
</body>
</html>
