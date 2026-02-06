<?php
/**
 * UNIME-ACQUE - Dicono di Noi
 * 
 * Pagina con testimonianze e recensioni dei clienti.
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
    <meta name="description" content="Leggi le testimonianze dei clienti soddisfatti di Ecelesti S.p.A. e UNIME-ACQUE.">
    <meta name="keywords" content="recensioni, testimonianze, opinioni, clienti, UNIME-ACQUE">
    <meta name="author" content="Ecelesti S.p.A.">
    
    <title>Dicono di Noi | Ecelesti S.p.A. - UNIME-ACQUE</title>
    
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
        
        .stats-section {
            padding: 3rem 0;
            background: var(--glass-bg);
            border-top: 1px solid var(--glass-border);
            border-bottom: 1px solid var(--glass-border);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            text-align: center;
        }
        
        .stat-item .number {
            font-family: var(--font-heading);
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--color-accent) 0%, var(--color-primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-item .label {
            color: var(--color-text-muted);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .testimonials-section {
            padding: 5rem 0;
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }
        
        .testimonial-card {
            position: relative;
            padding: 2.5rem;
        }
        
        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 1rem;
            left: 1.5rem;
            font-size: 5rem;
            font-family: Georgia, serif;
            color: var(--color-primary-light);
            opacity: 0.2;
            line-height: 1;
        }
        
        .testimonial-content {
            position: relative;
            z-index: 1;
        }
        
        .stars {
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }
        
        .testimonial-text {
            font-size: 1.05rem;
            line-height: 1.8;
            font-style: italic;
            margin-bottom: 1.5rem;
            color: var(--color-text-light);
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
        }
        
        .author-info h4 {
            color: var(--color-text-light);
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }
        
        .author-info .role {
            color: var(--color-accent);
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .featured-testimonial {
            padding: 4rem 0;
            background: var(--glass-bg);
            border-top: 1px solid var(--glass-border);
            border-bottom: 1px solid var(--glass-border);
        }
        
        .featured-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
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
            margin-bottom: 2rem;
        }
        
        .featured-quote {
            font-size: 1.5rem;
            line-height: 1.8;
            font-style: italic;
            color: var(--color-text-light);
            margin-bottom: 2rem;
        }
        
        .featured-author {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        
        .featured-author .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            border: 3px solid var(--color-accent);
        }
        
        .featured-author h4 {
            color: var(--color-text-light);
            font-size: 1.25rem;
            margin-bottom: 0;
        }
        
        .featured-author .role {
            color: var(--color-accent);
        }
        
        .partners-section {
            padding: 5rem 0;
        }
        
        .partners-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }
        
        .partner-card {
            text-align: center;
            padding: 2rem 1.5rem;
        }
        
        .partner-card .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .partner-card h4 {
            color: var(--color-text-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .partner-card p {
            font-size: 0.8rem;
            margin: 0;
        }
        
        @media screen and (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
            
            .partners-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media screen and (max-width: 576px) {
            .stats-grid,
            .partners-grid {
                grid-template-columns: 1fr;
            }
            
            .featured-quote {
                font-size: 1.25rem;
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
                        <li><a href="contatti.php">Contatti</a></li>
                        <li><a href="dicono-di-noi.php" style="color: var(--color-accent);">Dicono di Noi</a></li>
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
                <h1>Dicono di Noi</h1>
                <p>Le testimonianze di chi ha scelto i nostri servizi e ha migliorato la gestione delle proprie risorse idriche.</p>
            </div>
        </section>

        <!-- STATS SECTION -->
        <section class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="number">98%</div>
                        <div class="label">Clienti Soddisfatti</div>
                    </div>
                    <div class="stat-item">
                        <div class="number">4.9</div>
                        <div class="label">Valutazione Media</div>
                    </div>
                    <div class="stat-item">
                        <div class="number">15+</div>
                        <div class="label">Progetti Completati</div>
                    </div>
                    <div class="stat-item">
                        <div class="number">50K+</div>
                        <div class="label">Utenti Attivi</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FEATURED TESTIMONIAL -->
        <section class="featured-testimonial">
            <div class="container">
                <div class="featured-content">
                    <span class="featured-badge">⭐ Testimonianza in Evidenza</span>
                    <p class="featured-quote">
                        "La collaborazione con Ecelesti S.p.A. ha trasformato completamente il modo in cui gestiamo 
                        le nostre forniture idriche. Il sistema UNIME-ACQUE ci ha permesso di ridurre i tempi di 
                        gestione del 40% e di offrire ai nostri cittadini un servizio moderno e trasparente. 
                        La professionalità e la competenza tecnica del team sono davvero eccezionali."
                    </p>
                    <div class="featured-author">
                        <div class="avatar">👨‍💼</div>
                        <div>
                            <h4>Dott. Giuseppe Ferrara</h4>
                            <p class="role">Direttore Generale - Acquedotto Comunale di Messina</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- TESTIMONIALS -->
        <section class="testimonials-section">
            <div class="container">
                <div class="section-title">
                    <h2>Le Voci dei Nostri Clienti</h2>
                </div>
                
                <div class="testimonials-grid">
                    <!-- Testimonial 1 -->
                    <div class="card testimonial-card">
                        <div class="testimonial-content">
                            <div class="stars">⭐⭐⭐⭐⭐</div>
                            <p class="testimonial-text">
                                Finalmente riesco a controllare i miei consumi in tempo reale! Prima dovevo 
                                aspettare la bolletta per scoprire quanto avevo consumato. Ora ho tutto sotto 
                                controllo e ho anche imparato a risparmiare acqua. L'interfaccia è semplicissima 
                                da usare, anche per chi non è pratico di tecnologia.
                            </p>
                            <div class="testimonial-author">
                                <div class="author-avatar">👩</div>
                                <div class="author-info">
                                    <h4>Maria Concetta Romano</h4>
                                    <p class="role">Cittadina - Messina Centro</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 2 -->
                    <div class="card testimonial-card">
                        <div class="testimonial-content">
                            <div class="stars">⭐⭐⭐⭐⭐</div>
                            <p class="testimonial-text">
                                Come imprenditore, avevo bisogno di un sistema che mi permettesse di gestire 
                                le forniture idriche di tutte le mie attività commerciali da un'unica piattaforma. 
                                UNIME-ACQUE ha superato ogni aspettativa. Il sistema di fatturazione è chiaro 
                                e il supporto clienti è sempre disponibile.
                            </p>
                            <div class="testimonial-author">
                                <div class="author-avatar">👨‍💼</div>
                                <div class="author-info">
                                    <h4>Salvatore Mangiafico</h4>
                                    <p class="role">Titolare - Ristoranti Mangiafico S.r.l.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 3 -->
                    <div class="card testimonial-card">
                        <div class="testimonial-content">
                            <div class="stars">⭐⭐⭐⭐⭐</div>
                            <p class="testimonial-text">
                                Da quando abbiamo implementato il sistema di Ecelesti, le segnalazioni dei cittadini 
                                vengono gestite in modo molto più efficiente. Il ticketing è intuitivo e ci permette 
                                di tracciare ogni richiesta dall'apertura alla risoluzione. Un vero passo avanti 
                                nella digitalizzazione dei servizi pubblici.
                            </p>
                            <div class="testimonial-author">
                                <div class="author-avatar">👩‍💻</div>
                                <div class="author-info">
                                    <h4>Ing. Francesca Todaro</h4>
                                    <p class="role">Responsabile IT - Comune di Taormina</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 4 -->
                    <div class="card testimonial-card">
                        <div class="testimonial-content">
                            <div class="stars">⭐⭐⭐⭐⭐</div>
                            <p class="testimonial-text">
                                Ho avuto un problema con una lettura anomala e ho aperto un ticket tramite il 
                                portale. In meno di 48 ore hanno verificato e corretto l'errore. Servizio 
                                impeccabile! Apprezzo molto la possibilità di pagare online, mi fa risparmiare 
                                tempo prezioso evitando code agli sportelli.
                            </p>
                            <div class="testimonial-author">
                                <div class="author-avatar">👴</div>
                                <div class="author-info">
                                    <h4>Antonio Santoro</h4>
                                    <p class="role">Pensionato - Villaggio Santo</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 5 -->
                    <div class="card testimonial-card">
                        <div class="testimonial-content">
                            <div class="stars">⭐⭐⭐⭐⭐</div>
                            <p class="testimonial-text">
                                La dashboard di analisi che ci hanno sviluppato è fenomenale. Riusciamo a 
                                identificare le perdite nella rete di distribuzione con una precisione che 
                                prima era impensabile. Nel primo anno abbiamo recuperato il 23% dell'acqua 
                                che andava dispersa. Un investimento che si ripaga da solo.
                            </p>
                            <div class="testimonial-author">
                                <div class="author-avatar">👨‍🔧</div>
                                <div class="author-info">
                                    <h4>Ing. Marco Bellini</h4>
                                    <p class="role">Direttore Tecnico - Consorzio Idrico Calabria</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 6 -->
                    <div class="card testimonial-card">
                        <div class="testimonial-content">
                            <div class="stars">⭐⭐⭐⭐⭐</div>
                            <p class="testimonial-text">
                                Come amministratore di condominio, gestisco le forniture idriche di 12 stabili. 
                                Prima era un incubo tenere traccia di tutto. Ora con UNIME-ACQUE ho una visione 
                                chiara di ogni singola utenza, posso confrontare i consumi tra i diversi immobili 
                                e identificare subito eventuali anomalie.
                            </p>
                            <div class="testimonial-author">
                                <div class="author-avatar">👩‍💼</div>
                                <div class="author-info">
                                    <h4>Dott.ssa Lucia Pelligra</h4>
                                    <p class="role">Amministratore Condominiale</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PARTNERS -->
        <section class="partners-section">
            <div class="container">
                <div class="section-title">
                    <h2>I Nostri Partner</h2>
                </div>
                
                <div class="partners-grid">
                    <div class="card partner-card">
                        <div class="icon">🏛️</div>
                        <h4>Comune di Messina</h4>
                        <p>Partner Istituzionale</p>
                    </div>
                    
                    <div class="card partner-card">
                        <div class="icon">🎓</div>
                        <h4>Università di Messina</h4>
                        <p>Partner Accademico</p>
                    </div>
                    
                    <div class="card partner-card">
                        <div class="icon">💧</div>
                        <h4>Acquedotto Siciliano</h4>
                        <p>Partner Operativo</p>
                    </div>
                    
                    <div class="card partner-card">
                        <div class="icon">🌿</div>
                        <h4>Legambiente Sicilia</h4>
                        <p>Partner Ambientale</p>
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
