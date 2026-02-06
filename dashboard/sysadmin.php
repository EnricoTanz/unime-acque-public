<?php
/**
 * UNIME-ACQUE - Dashboard SysAdmin
 * 
 * Dashboard per utenti con ruolo SYSADMIN.
 * Permette di gestire il sistema, configurazioni, backup e monitoraggio.
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

$userId = getCurrentUserId();
$userName = getCurrentUserName();
$userEmail = $_SESSION['user_email'];

$conn = getDbConnection();

// Statistiche di sistema
$stats = [
    'totale_tabelle' => 0,
    'totale_record' => 0,
    'spazio_db' => 0,
    'ultimo_backup' => 'Mai'
];

// Conta tabelle nel database
$queryTables = "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()";
$resultTables = mysqli_query($conn, $queryTables);
if ($row = mysqli_fetch_assoc($resultTables)) {
    $stats['totale_tabelle'] = $row['count'];
}

// Calcola totale record (somma di tutte le tabelle principali)
$mainTables = ['UTENTE', 'CONTRATTO', 'FORNITURA', 'CONTATORE', 'FATTURA', 'LETTURA_CONSUMI', 'SEGNALAZIONE'];
$totalRecords = 0;
foreach ($mainTables as $table) {
    $queryCount = "SELECT COUNT(*) as count FROM $table";
    $resultCount = mysqli_query($conn, $queryCount);
    if ($row = mysqli_fetch_assoc($resultCount)) {
        $totalRecords += $row['count'];
    }
}
$stats['totale_record'] = $totalRecords;

// Dimensione database (approssimativa)
$querySize = "SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb 
              FROM information_schema.tables 
              WHERE table_schema = DATABASE()";
$resultSize = mysqli_query($conn, $querySize);
if ($row = mysqli_fetch_assoc($resultSize)) {
    $stats['spazio_db'] = round($row['size_mb'], 2);
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SysAdmin | UNIME-ACQUE</title>
    
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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
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
            color: #dc3545;
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
        
        .warning-box {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid var(--color-danger);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .warning-box h3 {
            color: var(--color-danger);
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
        }
        
        .warning-box p {
            margin: 0;
            color: var(--color-text-light);
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
                        <li><a href="sysadmin.php" style="color: var(--color-accent);">Dashboard</a></li>
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
                            ⚙️ Pannello System Administrator
                            <span class="badge">SysAdmin</span>
                        </h1>
                        <p class="subtitle">Gestisci sistema, database, backup e configurazioni avanzate</p>
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
                
                <!-- Warning Box -->
                <div class="warning-box">
                    <h3>⚠️ Attenzione - Accesso Privilegiato</h3>
                    <p>
                        Hai accesso completo al sistema. Le operazioni effettuate da questo pannello possono 
                        modificare o eliminare dati critici. Procedi con cautela e assicurati di effettuare 
                        backup regolari prima di qualsiasi operazione di manutenzione.
                    </p>
                </div>
                
                <?php if ($flashMessage): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>" style="margin-bottom: 2rem;">
                        <?php echo htmlspecialchars($flashMessage['message']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="icon">🗄️</div>
                        <div class="value"><?php echo number_format($stats['totale_tabelle']); ?></div>
                        <div class="label">Tabelle Database</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon">📊</div>
                        <div class="value"><?php echo number_format($stats['totale_record']); ?></div>
                        <div class="label">Record Totali</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon">💾</div>
                        <div class="value"><?php echo $stats['spazio_db']; ?> MB</div>
                        <div class="label">Spazio Database</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon">🔄</div>
                        <div class="value" style="font-size: 1.5rem;"><?php echo htmlspecialchars($stats['ultimo_backup']); ?></div>
                        <div class="label">Ultimo Backup</div>
                    </div>
                </div>
                
                <div class="section-title">
                    <h2>Gestione Utenze di Sistema</h2>
                </div>
                
                <div style="max-width: 600px; margin: 0 auto;">
                    <div class="action-card" onclick="window.location.href='sysadmin-crea-utente.php'" style="cursor: pointer;">
                        <div class="icon">👥</div>
                        <h3>Crea Utenze Amministrative o Tecniche</h3>
                        <p>Aggiungi nuovi utenti con ruolo AMMINISTRATORE o TECNICO al sistema</p>
                    </div>
                </div>
                
            </div>
        </section>
    </main>
</body>
</html>
