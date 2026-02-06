<?php
/**
 * UNIME-ACQUE - Privacy Policy
 * 
 * Pagina contenente l'informativa sulla privacy.
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
    <meta name="description" content="Informativa sulla Privacy di Ecelesti S.p.A. e UNIME-ACQUE.">
    <meta name="author" content="Ecelesti S.p.A.">
    
    <title>Privacy Policy | Ecelesti S.p.A. - UNIME-ACQUE</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
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
        
        .privacy-section {
            padding: 4rem 0;
        }
        
        .privacy-content {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .privacy-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        
        .privacy-card h2 {
            color: var(--color-accent);
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .privacy-card h3 {
            color: var(--color-text-light);
            font-size: 1.1rem;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        
        .privacy-card p {
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        
        .privacy-card ul {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }
        
        .privacy-card ul li {
            padding: 0.5rem 0 0.5rem 1.5rem;
            position: relative;
            color: var(--color-text-muted);
        }
        
        .privacy-card ul li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: var(--color-primary-light);
        }
        
        .last-update {
            text-align: center;
            padding: 2rem;
            background: var(--glass-bg);
            border-radius: 12px;
            margin-top: 2rem;
        }
        
        .last-update p {
            margin: 0;
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
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
        <section class="page-hero">
            <div class="container">
                <h1>Privacy Policy</h1>
                <p>Informativa sul trattamento dei dati personali ai sensi del Regolamento UE 2016/679 (GDPR).</p>
            </div>
        </section>

        <section class="privacy-section">
            <div class="container">
                <div class="privacy-content">
                    
                    <div class="privacy-card">
                        <h2>🏢 1. Titolare del Trattamento</h2>
                        <p>Il Titolare del trattamento dei dati personali è:</p>
                        <p><strong>Ecelesti S.p.A.</strong><br>
                        Sede legale: c/o Università degli Studi di Messina<br>
                        Email: enrico.celesti@studenti.unime.it<br>
                        P.IVA: [Progetto Accademico]</p>
                    </div>
                    
                    <div class="privacy-card">
                        <h2>📋 2. Dati Raccolti</h2>
                        <p>Nell'ambito dell'erogazione dei servizi UNIME-ACQUE, raccogliamo le seguenti categorie di dati personali:</p>
                        
                        <h3>Dati identificativi</h3>
                        <ul>
                            <li>Nome e cognome</li>
                            <li>Codice fiscale o Partita IVA</li>
                            <li>Indirizzo di residenza/sede legale</li>
                            <li>Indirizzo email e numero di telefono</li>
                        </ul>
                        
                        <h3>Dati contrattuali</h3>
                        <ul>
                            <li>Informazioni sui contratti di fornitura idrica</li>
                            <li>Dati relativi ai consumi idrici</li>
                            <li>Storico delle fatture e dei pagamenti</li>
                        </ul>
                        
                        <h3>Dati di pagamento</h3>
                        <ul>
                            <li>Dati delle carte di credito (memorizzati in forma criptata)</li>
                            <li>Storico delle transazioni</li>
                        </ul>
                        
                        <h3>Dati tecnici</h3>
                        <ul>
                            <li>Indirizzo IP e dati di navigazione</li>
                            <li>Cookie tecnici e di sessione</li>
                            <li>Log di accesso al sistema</li>
                        </ul>
                    </div>
                    
                    <div class="privacy-card">
                        <h2>🎯 3. Finalità del Trattamento</h2>
                        <p>I dati personali sono trattati per le seguenti finalità:</p>
                        <ul>
                            <li>Gestione del rapporto contrattuale per la fornitura idrica</li>
                            <li>Fatturazione e gestione dei pagamenti</li>
                            <li>Monitoraggio dei consumi e comunicazioni relative al servizio</li>
                            <li>Gestione delle richieste di assistenza e segnalazioni</li>
                            <li>Adempimenti di legge e obblighi normativi</li>
                            <li>Miglioramento dei servizi offerti</li>
                        </ul>
                    </div>
                    
                    <div class="privacy-card">
                        <h2>⚖️ 4. Base Giuridica</h2>
                        <p>Il trattamento dei dati è basato su:</p>
                        <ul>
                            <li><strong>Esecuzione del contratto:</strong> per la gestione delle forniture idriche</li>
                            <li><strong>Obblighi di legge:</strong> per adempimenti fiscali e normativi</li>
                            <li><strong>Consenso:</strong> per comunicazioni promozionali (ove applicabile)</li>
                            <li><strong>Legittimo interesse:</strong> per migliorare i servizi e prevenire frodi</li>
                        </ul>
                    </div>
                    
                    <div class="privacy-card">
                        <h2>🔒 5. Sicurezza dei Dati</h2>
                        <p>Adottiamo misure tecniche e organizzative adeguate per proteggere i dati personali:</p>
                        <ul>
                            <li>Crittografia dei dati sensibili (password, dati carte di credito)</li>
                            <li>Protezione contro SQL injection e attacchi informatici</li>
                            <li>Accesso ai dati limitato al personale autorizzato</li>
                            <li>Backup regolari e procedure di disaster recovery</li>
                            <li>Utilizzo di connessioni sicure (HTTPS)</li>
                            <li>Gestione sicura delle sessioni utente</li>
                        </ul>
                    </div>
                    
                    <div class="privacy-card">
                        <h2>⏱️ 6. Conservazione dei Dati</h2>
                        <p>I dati personali sono conservati per il tempo necessario alle finalità per cui sono stati raccolti:</p>
                        <ul>
                            <li>Dati contrattuali: per tutta la durata del contratto e per 10 anni successivi</li>
                            <li>Dati di fatturazione: 10 anni come previsto dalla normativa fiscale</li>
                            <li>Dati di navigazione: massimo 12 mesi</li>
                            <li>Dati delle segnalazioni: 5 anni dalla chiusura del ticket</li>
                        </ul>
                    </div>
                    
                    <div class="privacy-card">
                        <h2>🍪 7. Cookie Policy</h2>
                        <p>Il sito utilizza cookie per garantire il corretto funzionamento dei servizi:</p>
                        
                        <h3>Cookie Tecnici (necessari)</h3>
                        <p>Essenziali per il funzionamento del sito e la gestione delle sessioni utente. Non richiedono consenso.</p>
                        
                        <h3>Cookie di Sessione</h3>
                        <p>Utilizzati per mantenere l'autenticazione dell'utente. Vengono eliminati alla chiusura del browser.</p>
                        
                        <p>Non utilizziamo cookie di profilazione o di terze parti per finalità pubblicitarie.</p>
                    </div>
                    
                    <div class="privacy-card">
                        <h2>👤 8. Diritti dell'Interessato</h2>
                        <p>In qualità di interessato, hai diritto di:</p>
                        <ul>
                            <li><strong>Accesso:</strong> ottenere conferma del trattamento e copia dei dati</li>
                            <li><strong>Rettifica:</strong> correggere dati inesatti o incompleti</li>
                            <li><strong>Cancellazione:</strong> richiedere l'eliminazione dei dati (diritto all'oblio)</li>
                            <li><strong>Limitazione:</strong> limitare il trattamento in determinati casi</li>
                            <li><strong>Portabilità:</strong> ricevere i dati in formato strutturato</li>
                            <li><strong>Opposizione:</strong> opporsi al trattamento per motivi legittimi</li>
                            <li><strong>Revoca del consenso:</strong> revocare il consenso prestato</li>
                        </ul>
                        <p>Per esercitare i tuoi diritti, contattaci all'indirizzo: enrico.celesti@studenti.unime.it</p>
                    </div>
                    
                    <div class="privacy-card">
                        <h2>📤 9. Comunicazione dei Dati</h2>
                        <p>I dati personali possono essere comunicati a:</p>
                        <ul>
                            <li>Personale autorizzato di Ecelesti S.p.A.</li>
                            <li>Fornitori di servizi tecnici (hosting, manutenzione)</li>
                            <li>Istituti bancari per la gestione dei pagamenti</li>
                            <li>Autorità competenti su richiesta</li>
                        </ul>
                        <p>I dati non vengono trasferiti al di fuori dell'Unione Europea.</p>
                    </div>
                    
                    <div class="privacy-card">
                        <h2>📝 10. Modifiche alla Privacy Policy</h2>
                        <p>Ci riserviamo il diritto di modificare questa informativa in qualsiasi momento. Le modifiche saranno pubblicate su questa pagina con indicazione della data di ultimo aggiornamento. Ti invitiamo a consultare periodicamente questa pagina.</p>
                    </div>
                    
                    <div class="last-update">
                        <p>📅 Ultimo aggiornamento: <?php echo date('d/m/Y'); ?></p>
                    </div>
                    
                </div>
            </div>
        </section>
    </main>

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
