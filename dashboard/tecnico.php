<?php
/**
 * UNIME-ACQUE - Dashboard Tecnico
 * 
 * Dashboard per utenti con ruolo TECNICO.
 * Permette di gestire contatori e inserire letture consumi.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

startSecureSession();
requireRole('TECNICO');

$userId = getCurrentUserId();
$userName = getCurrentUserName();
$userEmail = $_SESSION['user_email'];

$conn = getDbConnection();

// Statistiche tecniche
$stats = [
    'contatori_attivi' => 0,
    'letture_oggi' => 0,
    'segnalazioni_assegnate' => 0,
    'installazioni_mese' => 0
];

// Contatori attivi
$queryContatori = "SELECT COUNT(*) as count FROM CONTATORE WHERE stato_contatore = 'ATTIVO'";
$resultContatori = mysqli_query($conn, $queryContatori);
if ($row = mysqli_fetch_assoc($resultContatori)) {
    $stats['contatori_attivi'] = $row['count'];
}

// Letture effettuate oggi
$queryLetture = "SELECT COUNT(*) as count FROM LETTURA_CONSUMI WHERE data_rif = CURRENT_DATE";
$resultLetture = mysqli_query($conn, $queryLetture);
if ($row = mysqli_fetch_assoc($resultLetture)) {
    $stats['letture_oggi'] = $row['count'];
}

// Segnalazioni assegnate al tecnico
$querySegnalazioni = "SELECT COUNT(*) as count FROM SEGNALAZIONE WHERE IdUtente_presa_in_carico = ? AND data_chiusura IS NULL";
$stmtSegnalazioni = mysqli_prepare($conn, $querySegnalazioni);
mysqli_stmt_bind_param($stmtSegnalazioni, 'i', $userId);
mysqli_stmt_execute($stmtSegnalazioni);
$resultSegnalazioni = mysqli_stmt_get_result($stmtSegnalazioni);
if ($row = mysqli_fetch_assoc($resultSegnalazioni)) {
    $stats['segnalazioni_assegnate'] = $row['count'];
}
mysqli_stmt_close($stmtSegnalazioni);

// Installazioni questo mese
$queryInstallazioni = "SELECT COUNT(*) as count FROM CONTATORE WHERE MONTH(data_installazione) = MONTH(CURRENT_DATE) AND YEAR(data_installazione) = YEAR(CURRENT_DATE)";
$resultInstallazioni = mysqli_query($conn, $queryInstallazioni);
if ($row = mysqli_fetch_assoc($resultInstallazioni)) {
    $stats['installazioni_mese'] = $row['count'];
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tecnico | UNIME-ACQUE</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
    
    <style>
        .dashboard-page {
            padding-top: 80px;
            min-height: 100vh;
        }
        
        .dashboard-header {
            background: var(--glass-bg);
            border-bottom: 1px solid var(--glass-border);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .dashboard-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .dashboard-title h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .dashboard-title .subtitle {
            color: var(--color-text-muted);
        }
        
        .dashboard-title .badge {
            display: inline-block;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-left: 0.75rem;
        }
        
        .dashboard-user {
            text-align: right;
        }
        
        .dashboard-user .name {
            font-weight: 600;
            color: var(--color-text-light);
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
            color: #4CAF50;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .label {
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            max-width: 900px;
            margin: 0 auto;
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
            background: rgba(76, 175, 80, 0.05);
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
                        <li><a href="tecnico.php" style="color: var(--color-accent);">Dashboard</a></li>
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
                            🔧 Pannello Tecnico
                            <span class="badge">Tecnico</span>
                        </h1>
                        <p class="subtitle">Gestisci contatori e inserisci letture consumi</p>
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
                        <div class="icon">⚙️</div>
                        <div class="value"><?php echo number_format($stats['contatori_attivi']); ?></div>
                        <div class="label">Contatori Attivi</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon">📊</div>
                        <div class="value"><?php echo number_format($stats['letture_oggi']); ?></div>
                        <div class="label">Letture Oggi</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon">🎫</div>
                        <div class="value"><?php echo number_format($stats['segnalazioni_assegnate']); ?></div>
                        <div class="label">Segnalazioni Assegnate</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon">🔨</div>
                        <div class="value"><?php echo number_format($stats['installazioni_mese']); ?></div>
                        <div class="label">Installazioni Mese</div>
                    </div>
                </div>
                
                <div class="section-title">
                    <h2>Strumenti Operativi</h2>
                </div>
                
                <div class="actions-grid">
                    <div class="action-card" onclick="window.location.href='tecnico-contatori.php'">
                        <div class="icon">⚙️</div>
                        <h3>Gestisci Contatori</h3>
                        <p>Installa nuovi contatori e gestisci sostituzioni</p>
                    </div>
                    
                    <div class="action-card" onclick="window.location.href='tecnico-letture.php'">
                        <div class="icon">📊</div>
                        <h3>Inserisci Letture</h3>
                        <p>Registra le letture dei consumi dai contatori</p>
                    </div>
                </div>
                
            </div>
        </section>
    </main>
</body>
</html>
