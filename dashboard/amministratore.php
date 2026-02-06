<?php
/**
 * UNIME-ACQUE - Dashboard Amministratore
 * 
 * Dashboard per utenti con ruolo AMMINISTRATORE.
 * Permette di gestire utenti, contratti, fatture e visualizzare statistiche globali.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

startSecureSession();
requireRole('AMMINISTRATORE');

$userId = getCurrentUserId();
$userName = getCurrentUserName();
$userEmail = $_SESSION['user_email'];

$conn = getDbConnection();

// Statistiche globali
$stats = [
    'totale_utenti' => 0,
    'contratti_attivi' => 0,
    'fatture_mese' => 0,
    'segnalazioni_aperte' => 0
];

// Totale utenti
$queryUtenti = "SELECT COUNT(*) as count FROM UTENTE WHERE ruolo = 'CLIENTE'";
$resultUtenti = mysqli_query($conn, $queryUtenti);
if ($row = mysqli_fetch_assoc($resultUtenti)) {
    $stats['totale_utenti'] = $row['count'];
}

// Contratti attivi
$queryContratti = "SELECT COUNT(*) as count FROM CONTRATTO WHERE stato_contratto = 'ATTIVO'";
$resultContratti = mysqli_query($conn, $queryContratti);
if ($row = mysqli_fetch_assoc($resultContratti)) {
    $stats['contratti_attivi'] = $row['count'];
}

// Fatture emesse questo mese
$queryFatture = "SELECT COUNT(*) as count FROM FATTURA WHERE MONTH(data_emissione) = MONTH(CURRENT_DATE) AND YEAR(data_emissione) = YEAR(CURRENT_DATE)";
$resultFatture = mysqli_query($conn, $queryFatture);
if ($row = mysqli_fetch_assoc($resultFatture)) {
    $stats['fatture_mese'] = $row['count'];
}

// Segnalazioni aperte
$querySegnalazioni = "SELECT COUNT(*) as count FROM SEGNALAZIONE WHERE data_chiusura IS NULL";
$resultSegnalazioni = mysqli_query($conn, $querySegnalazioni);
if ($row = mysqli_fetch_assoc($resultSegnalazioni)) {
    $stats['segnalazioni_aperte'] = $row['count'];
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Amministratore | UNIME-ACQUE</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
    
    <style>
        .dashboard-page {
            min-height: 100vh;
            padding-top: 80px;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, rgba(5, 191, 219, 0.1) 0%, rgba(52, 152, 219, 0.1) 100%);
            padding: 3rem 0 2rem;
        }
        
        .dashboard-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
        }
        
        .dashboard-title h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .badge {
            background: var(--gradient-primary);
            color: white;
            padding: 0.25rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .subtitle {
            color: var(--color-text-muted);
            font-size: 1.1rem;
        }
        
        .dashboard-user {
            text-align: right;
        }
        
        .dashboard-user .name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .dashboard-user .email {
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }
        
        .dashboard-content {
            padding-bottom: 3rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all var(--transition-fast);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--color-primary-light);
        }
        
        .stat-card .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .stat-card .value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--color-warning);
            margin-bottom: 0.5rem;
        }
        
        .stat-card .label {
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
        
        .action-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2rem;
            transition: all var(--transition-fast);
            cursor: pointer;
            text-align: center;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            border-color: var(--color-primary-light);
            background: rgba(5, 191, 219, 0.05);
        }
        
        .action-card .icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }
        
        .action-card h3 {
            color: var(--color-text-light);
            margin-bottom: 0.75rem;
            font-size: 1.25rem;
        }
        
        .action-card p {
            color: var(--color-text-muted);
            margin: 0;
            font-size: 0.95rem;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }
        
        .alert-danger {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }
        
        @media screen and (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
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
                        <li><a href="amministratore.php" style="color: var(--color-accent);">Dashboard</a></li>
                        <li><a href="../auth/logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="dashboard-page">
        <section class="dashboard-header">
            <div class="container">
                <div class="dashboard-header-content">
                    <div class="dashboard-title">
                        <h1>
                            🛡️ Pannello Amministratore
                            <span class="badge">Admin</span>
                        </h1>
                        <p class="subtitle">Gestisci utenti, contratti e monitora le attività del sistema</p>
                    </div>
                    <div class="dashboard-user">
                        <p class="name"><?php echo htmlspecialchars($userName); ?></p>
                        <p class="email"><?php echo htmlspecialchars($userEmail); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-content">
            <div class="container">
                
                <?php if ($flashMessage): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>">
                        <?php echo htmlspecialchars($flashMessage['message']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="icon">👥</div>
                        <div class="value"><?php echo number_format($stats['totale_utenti']); ?></div>
                        <div class="label">Utenti Registrati</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon">📋</div>
                        <div class="value"><?php echo number_format($stats['contratti_attivi']); ?></div>
                        <div class="label">Contratti Attivi</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon">💰</div>
                        <div class="value"><?php echo number_format($stats['fatture_mese']); ?></div>
                        <div class="label">Fatture questo Mese</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon">🎫</div>
                        <div class="value"><?php echo number_format($stats['segnalazioni_aperte']); ?></div>
                        <div class="label">Segnalazioni Aperte</div>
                    </div>
                </div>
                
                <div class="section-title">
                    <h2>Strumenti Amministrativi</h2>
                </div>
                
                <div class="actions-grid">
                    <div class="action-card" onclick="window.location.href='admin-clienti.php'">
                        <div class="icon">👥</div>
                        <h3>Gestisci Clienti</h3>
                        <p>Registra nuovi clienti e visualizza quelli esistenti</p>
                    </div>
                    
                    <div class="action-card" onclick="window.location.href='admin-contratti.php'">
                        <div class="icon">📄</div>
                        <h3>Gestisci Contratti</h3>
                        <p>Crea nuovi contratti e abbina tariffe ai clienti</p>
                    </div>
                    
                    <div class="action-card" onclick="window.location.href='admin-forniture.php'">
                        <div class="icon">🏠</div>
                        <h3>Gestisci Forniture</h3>
                        <p>Crea nuove forniture e gestisci cessazioni</p>
                    </div>
                    
                    <div class="action-card" onclick="window.location.href='admin-ticket.php'">
                        <div class="icon">🎫</div>
                        <h3>Gestisci Ticket</h3>
                        <p>Visualizza e gestisci le segnalazioni dei clienti</p>
                    </div>
                    
                    <div class="action-card" onclick="window.location.href='admin-consumi.php'">
                        <div class="icon">📊</div>
                        <h3>Gestisci Consumi</h3>
                        <p>Visualizza e rettifica i consumi dei contratti</p>
                    </div>
                    
                    <div class="action-card" onclick="window.location.href='admin-fatture.php'">
                        <div class="icon">💰</div>
                        <h3>Gestisci Fatture</h3>
                        <p>Visualizza fatture non pagate e applica sconti</p>
                    </div>
                    
                    <div class="action-card" onclick="window.location.href='admin-tariffe.php'">
                        <div class="icon">💳</div>
                        <h3>Gestisci Tariffe</h3>
                        <p>Crea e gestisci le tariffe del sistema</p>
                    </div>
                </div>
                
            </div>
        </section>
    </main>
</body>
</html>
