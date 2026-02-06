<?php
/**
 * UNIME-ACQUE - Chi Siamo
 * 
 * Pagina di presentazione di Ecelesti S.p.A. e del fondatore.
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
    <meta name="description" content="Scopri chi è Ecelesti S.p.A. - Una società innovativa nel settore della gestione delle risorse idriche fondata da Enrico Celesti.">
    <meta name="keywords" content="Ecelesti, Enrico Celesti, gestione acqua, data engineering, Messina, UNIME">
    <meta name="author" content="Ecelesti S.p.A.">
    
    <title>Chi Siamo | Ecelesti S.p.A. - UNIME-ACQUE</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
    
    <style>
        /* Stili specifici per la pagina Chi Siamo */
        .page-hero {
            padding: 10rem 1.5rem 4rem;
            text-align: center;
            position: relative;
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
        
        .about-section {
            padding: 4rem 0;
        }
        
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }
        
        .about-image {
            position: relative;
        }
        
        .about-image-placeholder {
            width: 100%;
            aspect-ratio: 1;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8rem;
            box-shadow: var(--shadow-strong);
        }
        
        .about-content h2 {
            color: var(--color-accent);
            margin-bottom: 1.5rem;
        }
        
        .about-content p {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }
        
        .founder-section {
            background: var(--glass-bg);
            border-top: 1px solid var(--glass-border);
            border-bottom: 1px solid var(--glass-border);
            padding: 5rem 0;
        }
        
        .founder-card {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        
        .founder-avatar {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary-light) 100%);
            margin: 0 auto 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            border: 4px solid var(--color-accent);
            box-shadow: 0 0 40px rgba(0, 255, 202, 0.3);
        }
        
        .founder-card h3 {
            font-size: 2rem;
            color: var(--color-text-light);
            margin-bottom: 0.5rem;
        }
        
        .founder-card .role {
            color: var(--color-accent);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }
        
        .founder-card .bio {
            font-size: 1.1rem;
            line-height: 1.9;
            text-align: left;
        }
        
        .values-section {
            padding: 5rem 0;
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }
        
        .value-card {
            text-align: center;
            padding: 2.5rem 2rem;
        }
        
        .value-card .icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
        }
        
        .value-card h4 {
            color: var(--color-text-light);
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }
        
        @media screen and (max-width: 992px) {
            .about-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .about-image {
                order: -1;
                max-width: 400px;
                margin: 0 auto;
            }
            
            .values-grid {
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
                        <li><a href="chi-siamo.php" style="color: var(--color-accent);">Chi Siamo</a></li>
                        <li><a href="cosa-facciamo.php">Cosa Facciamo</a></li>
                        <li><a href="contatti.php">Contatti</a></li>
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
                <h1>Chi Siamo</h1>
                <p>Innovazione e passione al servizio della gestione delle risorse idriche.</p>
            </div>
        </section>

        <!-- ABOUT SECTION -->
        <section class="about-section">
            <div class="container">
                <div class="about-grid">
                    <div class="about-content">
                        <h2>Ecelesti S.p.A.</h2>
                        <p>
                            <strong>Ecelesti S.p.A.</strong> è una società a socio unico che nasce dalla visione di trasformare 
                            la gestione delle risorse idriche attraverso soluzioni tecnologiche innovative e data-driven.
                        </p>
                        <p>
                            Fondata con l'obiettivo di digitalizzare e ottimizzare i processi di gestione delle forniture idriche, 
                            Ecelesti combina competenze avanzate in ingegneria dei dati con una profonda conoscenza 
                            del settore delle utilities, offrendo soluzioni scalabili e orientate al futuro.
                        </p>
                        <p>
                            La nostra missione è rendere la gestione dell'acqua più efficiente, trasparente e accessibile, 
                            contribuendo a un uso responsabile di questa preziosa risorsa per le comunità che serviamo.
                        </p>
                    </div>
                    <div class="about-image">
                        <div class="about-image-placeholder">💧</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOUNDER SECTION -->
        <section class="founder-section">
            <div class="container">
                <div class="section-title">
                    <h2>Il Fondatore</h2>
                </div>
                
                <div class="founder-card">
                    <div class="founder-avatar">👨‍💻</div>
                    <h3>Enrico Celesti</h3>
                    <p class="role">Fondatore & CEO</p>
                    <p class="bio">
                        Enrico Celesti è uno studente di Informatica presso l'Università degli Studi di Messina 
                        (matricola 460896), con un solido background professionale come <strong>Data Engineer</strong>. 
                        La sua esperienza nel campo dell'ingegneria dei dati gli ha permesso di sviluppare 
                        competenze avanzate nella progettazione di architetture dati scalabili, nell'ottimizzazione 
                        di pipeline ETL e nell'implementazione di soluzioni di business intelligence.
                    </p>
                    <p class="bio">
                        Appassionato di programmazione e di soluzioni innovative per la gestione delle risorse 
                        naturali, Enrico ha fondato Ecelesti S.p.A. con l'obiettivo di applicare le più moderne 
                        tecniche di data engineering al settore idrico. La sua visione unisce rigore tecnico 
                        e sensibilità ambientale, puntando a creare sistemi che non solo ottimizzino i processi 
                        aziendali, ma contribuiscano anche a una gestione più sostenibile dell'acqua.
                    </p>
                    <p class="bio">
                        Il progetto UNIME-ACQUE rappresenta la sintesi delle sue competenze accademiche 
                        e professionali: un sistema completo di gestione delle forniture idriche che dimostra 
                        come la tecnologia possa essere messa al servizio delle comunità locali.
                    </p>
                </div>
            </div>
        </section>

        <!-- VALUES SECTION -->
        <section class="values-section">
            <div class="container">
                <div class="section-title">
                    <h2>I Nostri Valori</h2>
                </div>
                
                <div class="values-grid">
                    <div class="card value-card">
                        <div class="icon">🎯</div>
                        <h4>Innovazione</h4>
                        <p>Ricerchiamo costantemente soluzioni tecnologiche all'avanguardia per migliorare i nostri servizi e anticipare le esigenze del mercato.</p>
                    </div>
                    
                    <div class="card value-card">
                        <div class="icon">🌱</div>
                        <h4>Sostenibilità</h4>
                        <p>Crediamo in una gestione responsabile delle risorse idriche, promuovendo pratiche che rispettino l'ambiente e le generazioni future.</p>
                    </div>
                    
                    <div class="card value-card">
                        <div class="icon">🤝</div>
                        <h4>Trasparenza</h4>
                        <p>Offriamo ai nostri utenti piena visibilità sui loro consumi e costi, costruendo rapporti basati sulla fiducia e sull'integrità.</p>
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
    </script>
</body>
</html>
