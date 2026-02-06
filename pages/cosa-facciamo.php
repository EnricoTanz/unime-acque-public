<?php
/**
 * UNIME-ACQUE - Cosa Facciamo
 * 
 * Pagina che descrive i progetti e le attività di Ecelesti S.p.A.
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
    <meta name="description" content="Scopri i progetti di Ecelesti S.p.A. - Soluzioni innovative per la gestione delle risorse idriche e data engineering.">
    <meta name="keywords" content="progetti, gestione acqua, data engineering, UNIME-ACQUE, soluzioni idriche">
    <meta name="author" content="Ecelesti S.p.A.">
    
    <title>Cosa Facciamo | Ecelesti S.p.A. - UNIME-ACQUE</title>
    
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
        
        .featured-project {
            padding: 4rem 0;
            background: var(--glass-bg);
            border-top: 1px solid var(--glass-border);
            border-bottom: 1px solid var(--glass-border);
        }
        
        .featured-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--color-accent) 0%, var(--color-primary-light) 100%);
            color: var(--color-bg-dark);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1.5rem;
        }
        
        .featured-content {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
        }
        
        .featured-content h2 {
            font-size: 2.5rem;
            color: var(--color-text-light);
            margin-bottom: 1.5rem;
        }
        
        .featured-content h2 span {
            color: var(--color-primary-light);
        }
        
        .featured-content .description {
            font-size: 1.15rem;
            line-height: 1.9;
            margin-bottom: 2rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-top: 3rem;
            text-align: left;
        }
        
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            border: 1px solid var(--glass-border);
        }
        
        .feature-item .icon {
            font-size: 2rem;
            flex-shrink: 0;
        }
        
        .feature-item h4 {
            color: var(--color-text-light);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        
        .feature-item p {
            font-size: 0.9rem;
            margin: 0;
        }
        
        .projects-section {
            padding: 5rem 0;
        }
        
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }
        
        .project-card {
            position: relative;
            overflow: hidden;
        }
        
        .project-card .status {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .status.completed {
            background: var(--color-success);
            color: var(--color-bg-dark);
        }
        
        .status.ongoing {
            background: var(--color-warning);
            color: var(--color-bg-dark);
        }
        
        .project-card .icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }
        
        .project-card h3 {
            color: var(--color-text-light);
            margin-bottom: 0.5rem;
        }
        
        .project-card .client {
            color: var(--color-accent);
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .project-card .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }
        
        .project-card .tag {
            background: rgba(5, 191, 219, 0.15);
            color: var(--color-primary-light);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
        }
        
        .services-section {
            padding: 5rem 0;
            background: var(--glass-bg);
            border-top: 1px solid var(--glass-border);
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }
        
        .service-card {
            text-align: center;
            padding: 2rem 1.5rem;
        }
        
        .service-card .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .service-card h4 {
            color: var(--color-text-light);
            font-size: 1rem;
            margin-bottom: 0.75rem;
        }
        
        .service-card p {
            font-size: 0.9rem;
        }
        
        @media screen and (max-width: 992px) {
            .features-grid,
            .projects-grid {
                grid-template-columns: 1fr;
            }
            
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media screen and (max-width: 576px) {
            .services-grid {
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
                        <li><a href="cosa-facciamo.php" style="color: var(--color-accent);">Cosa Facciamo</a></li>
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
                <h1>Cosa Facciamo</h1>
                <p>Progettiamo soluzioni innovative per la gestione delle risorse idriche e l'analisi dei dati.</p>
            </div>
        </section>

        <!-- FEATURED PROJECT -->
        <section class="featured-project">
            <div class="container">
                <div class="featured-content">
                    <span class="featured-badge">🌟 Progetto in Evidenza</span>
                    <h2>UNIME<span>-ACQUE</span></h2>
                    <p class="description">
                        UNIME-ACQUE è il nostro progetto di punta: un portale completo per la gestione delle forniture 
                        idriche sviluppato per la città di Messina. Il sistema consente a cittadini e aziende di consultare 
                        i propri contratti, monitorare i consumi in tempo reale, visualizzare e pagare le bollette online, 
                        e aprire segnalazioni per assistenza tecnica o commerciale.
                    </p>
                    <p class="description">
                        L'architettura a tre livelli (frontend HTML/CSS, backend PHP, database MySQL) garantisce 
                        scalabilità, sicurezza e facilità di manutenzione. Il sistema è progettato per gestire oltre 
                        95.000 utenti, 100.000 contratti e 36 milioni di letture annuali dei consumi, con una struttura 
                        pronta per la futura integrazione di dispositivi IoT per la lettura automatizzata dei contatori.
                    </p>
                    
                    <div class="features-grid">
                        <div class="feature-item">
                            <span class="icon">📊</span>
                            <div>
                                <h4>Monitoraggio Consumi</h4>
                                <p>Letture giornaliere con storico dettagliato e grafici interattivi per tenere sotto controllo i propri consumi.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <span class="icon">💳</span>
                            <div>
                                <h4>Pagamenti Online</h4>
                                <p>Gestione sicura delle carte di credito e pagamento delle fatture direttamente dal portale.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <span class="icon">📋</span>
                            <div>
                                <h4>Gestione Contratti</h4>
                                <p>Visualizzazione completa di contratti, forniture e tariffe applicate con massima trasparenza.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <span class="icon">🎫</span>
                            <div>
                                <h4>Sistema di Ticketing</h4>
                                <p>Apertura e monitoraggio di segnalazioni tecniche e commerciali con tracking dello stato.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- OTHER PROJECTS -->
        <section class="projects-section">
            <div class="container">
                <div class="section-title">
                    <h2>Altri Progetti Realizzati</h2>
                </div>
                
                <div class="projects-grid">
                    <!-- Progetto 1 -->
                    <div class="card project-card">
                        <span class="status completed">Completato</span>
                        <div class="icon">🏭</div>
                        <h3>AcquaPura Analytics</h3>
                        <p class="client">Consorzio Idrico Calabria</p>
                        <p>
                            Piattaforma di Business Intelligence per l'analisi predittiva delle perdite idriche. 
                            Sistema di machine learning che identifica anomalie nelle reti di distribuzione, 
                            riducendo gli sprechi del 23% nel primo anno di utilizzo.
                        </p>
                        <div class="tags">
                            <span class="tag">Data Analytics</span>
                            <span class="tag">ML</span>
                            <span class="tag">Python</span>
                        </div>
                    </div>
                    
                    <!-- Progetto 2 -->
                    <div class="card project-card">
                        <span class="status completed">Completato</span>
                        <div class="icon">📱</div>
                        <h3>HydroMeter Mobile</h3>
                        <p class="client">Acquedotto Siciliano S.r.l.</p>
                        <p>
                            Applicazione mobile per tecnici sul campo che consente la registrazione 
                            delle letture dei contatori tramite scansione QR code e geolocalizzazione. 
                            Sincronizzazione in tempo reale con il sistema centrale.
                        </p>
                        <div class="tags">
                            <span class="tag">Mobile App</span>
                            <span class="tag">React Native</span>
                            <span class="tag">API REST</span>
                        </div>
                    </div>
                    
                    <!-- Progetto 3 -->
                    <div class="card project-card">
                        <span class="status completed">Completato</span>
                        <div class="icon">📈</div>
                        <h3>WaterFlow Dashboard</h3>
                        <p class="client">Comune di Taormina</p>
                        <p>
                            Dashboard interattiva per il monitoraggio in tempo reale dei flussi idrici 
                            comunali. Visualizzazione geografica delle infrastrutture e alert automatici 
                            in caso di anomalie o superamento delle soglie critiche.
                        </p>
                        <div class="tags">
                            <span class="tag">Dashboard</span>
                            <span class="tag">GIS</span>
                            <span class="tag">Real-time</span>
                        </div>
                    </div>
                    
                    <!-- Progetto 4 -->
                    <div class="card project-card">
                        <span class="status ongoing">In Corso</span>
                        <div class="icon">🌐</div>
                        <h3>IoT Water Grid</h3>
                        <p class="client">Provincia di Ragusa</p>
                        <p>
                            Rete di sensori IoT per il monitoraggio distribuito della qualità dell'acqua 
                            e dei livelli nei serbatoi. Integrazione con piattaforma cloud per analisi 
                            centralizzata e reportistica automatica agli enti regolatori.
                        </p>
                        <div class="tags">
                            <span class="tag">IoT</span>
                            <span class="tag">Cloud</span>
                            <span class="tag">Sensori</span>
                        </div>
                    </div>
                    
                    <!-- Progetto 5 -->
                    <div class="card project-card">
                        <span class="status completed">Completato</span>
                        <div class="icon">🔄</div>
                        <h3>DataFlow ETL Suite</h3>
                        <p class="client">Multiservizi Catania S.p.A.</p>
                        <p>
                            Pipeline ETL automatizzata per la migrazione e normalizzazione di dati 
                            storici da sistemi legacy. Elaborazione di oltre 15 milioni di record 
                            con validazione e deduplicazione intelligente.
                        </p>
                        <div class="tags">
                            <span class="tag">ETL</span>
                            <span class="tag">Data Migration</span>
                            <span class="tag">SQL</span>
                        </div>
                    </div>
                    
                    <!-- Progetto 6 -->
                    <div class="card project-card">
                        <span class="status ongoing">In Corso</span>
                        <div class="icon">🤖</div>
                        <h3>SmartBilling AI</h3>
                        <p class="client">Utilities Network Italia</p>
                        <p>
                            Sistema di fatturazione intelligente con rilevamento automatico di 
                            consumi anomali e suggerimenti personalizzati per il risparmio idrico. 
                            Chatbot integrato per assistenza clienti 24/7.
                        </p>
                        <div class="tags">
                            <span class="tag">AI</span>
                            <span class="tag">Chatbot</span>
                            <span class="tag">Billing</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SERVICES -->
        <section class="services-section">
            <div class="container">
                <div class="section-title">
                    <h2>I Nostri Servizi</h2>
                </div>
                
                <div class="services-grid">
                    <div class="card service-card">
                        <div class="icon">💾</div>
                        <h4>Data Engineering</h4>
                        <p>Progettazione e implementazione di architetture dati scalabili, pipeline ETL e data warehouse.</p>
                    </div>
                    
                    <div class="card service-card">
                        <div class="icon">🌊</div>
                        <h4>Gestione Idrica</h4>
                        <p>Soluzioni complete per la digitalizzazione dei processi di gestione delle forniture idriche.</p>
                    </div>
                    
                    <div class="card service-card">
                        <div class="icon">📊</div>
                        <h4>Business Intelligence</h4>
                        <p>Dashboard interattive, reportistica automatica e analisi predittive per decisioni data-driven.</p>
                    </div>
                    
                    <div class="card service-card">
                        <div class="icon">🔧</div>
                        <h4>Consulenza Tecnica</h4>
                        <p>Supporto nella scelta delle tecnologie più adatte e nell'ottimizzazione dei sistemi esistenti.</p>
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
