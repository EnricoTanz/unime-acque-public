<?php
/**
 * UNIME-ACQUE - Dashboard Cliente
 * 
 * Dashboard principale per utenti con ruolo CLIENTE.
 * 
 * @author Enrico Celesti (460896)
 * @project UNIME-ACQUE
 */

define('UNIME_ACQUE', true);

require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

startSecureSession();
requireRole('CLIENTE');

$userId = getCurrentUserId();
$userName = getCurrentUserName();
$userEmail = getCurrentUserEmail();

$conn = getDbConnection();

// Statistiche cliente
$stats = [
    'contratti_attivi' => 0,
    'forniture_attive' => 0,
    'fatture_non_pagate' => 0,
    'segnalazioni_aperte' => 0
];

// Conta contratti attivi
$queryContratti = "SELECT COUNT(*) as count FROM CONTRATTO WHERE IdUtente = ? AND stato_contratto = 'ATTIVO'";
$stmtContratti = mysqli_prepare($conn, $queryContratti);
mysqli_stmt_bind_param($stmtContratti, 'i', $userId);
mysqli_stmt_execute($stmtContratti);
$resultContratti = mysqli_stmt_get_result($stmtContratti);
if ($row = mysqli_fetch_assoc($resultContratti)) {
    $stats['contratti_attivi'] = $row['count'];
}
mysqli_stmt_close($stmtContratti);

// Conta forniture attive
$queryForniture = "SELECT COUNT(*) as count 
                   FROM FORNITURA f
                   INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
                   WHERE c.IdUtente = ? AND f.stato_fornitura = 'ATTIVA'";
$stmtForniture = mysqli_prepare($conn, $queryForniture);
mysqli_stmt_bind_param($stmtForniture, 'i', $userId);
mysqli_stmt_execute($stmtForniture);
$resultForniture = mysqli_stmt_get_result($stmtForniture);
if ($row = mysqli_fetch_assoc($resultForniture)) {
    $stats['forniture_attive'] = $row['count'];
}
mysqli_stmt_close($stmtForniture);

// Conta fatture non pagate
$queryFatture = "SELECT COUNT(*) as count 
                 FROM FATTURA f
                 INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
                 WHERE c.IdUtente = ? AND f.data_pagamento IS NULL";
$stmtFatture = mysqli_prepare($conn, $queryFatture);
mysqli_stmt_bind_param($stmtFatture, 'i', $userId);
mysqli_stmt_execute($stmtFatture);
$resultFatture = mysqli_stmt_get_result($stmtFatture);
if ($row = mysqli_fetch_assoc($resultFatture)) {
    $stats['fatture_non_pagate'] = $row['count'];
}
mysqli_stmt_close($stmtFatture);

// Conta segnalazioni aperte
$querySegnalazioni = "SELECT COUNT(*) as count FROM SEGNALAZIONE WHERE IdUtente_segnalante = ? AND data_chiusura IS NULL";
$stmtSegnalazioni = mysqli_prepare($conn, $querySegnalazioni);
mysqli_stmt_bind_param($stmtSegnalazioni, 'i', $userId);
mysqli_stmt_execute($stmtSegnalazioni);
$resultSegnalazioni = mysqli_stmt_get_result($stmtSegnalazioni);
if ($row = mysqli_fetch_assoc($resultSegnalazioni)) {
    $stats['segnalazioni_aperte'] = $row['count'];
}
mysqli_stmt_close($stmtSegnalazioni);

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cliente | UNIME-ACQUE</title>
    
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
            gap: 1.5rem;
        }
        
        .dashboard-title h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--color-accent) 0%, var(--color-primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .dashboard-title .subtitle {
            color: var(--color-text-muted);
            font-size: 1.1rem;
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
            color: var(--color-accent);
            margin-bottom: 0.5rem;
        }
        
        .stat-card .label {
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }
        
        .section-title {
            margin-bottom: 2rem;
        }
        
        .section-title h2 {
            font-size: 1.75rem;
            color: var(--color-text-light);
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
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid #2ecc71;
        }
        
        .alert-warning {
            background: rgba(241, 196, 15, 0.2);
            color: #f1c40f;
            border: 1px solid #f1c40f;
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
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media screen and (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header-content {
                flex-direction: column;
            }
            
            .dashboard-user {
                text-align: left;
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
                        <li><a href="cliente.php" style="color: var(--color-accent);">Dashboard</a></li>
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
                        <h1>👋 Benvenuto, <?php echo htmlspecialchars(explode(' ', $userName)[0]); ?>!</h1>
                        <p class="subtitle">Ecco una panoramica del tuo account UNIME-ACQUE</p>
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
                        <div class="icon">📋</div>
                        <div class="value"><?php echo number_format($stats['contratti_attivi']); ?></div>
                        <div class="label">Contratti Attivi</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon">🏠</div>
                        <div class="value"><?php echo number_format($stats['forniture_attive']); ?></div>
                        <div class="label">Forniture Attive</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon">💰</div>
                        <div class="value"><?php echo number_format($stats['fatture_non_pagate']); ?></div>
                        <div class="label">Fatture da Pagare</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon">🎫</div>
                        <div class="value"><?php echo number_format($stats['segnalazioni_aperte']); ?></div>
                        <div class="label">Segnalazioni Aperte</div>
                    </div>
                </div>
                
                <div class="section-title">
                    <h2>I tuoi Servizi</h2>
                </div>
                
                <div class="actions-grid">
                    <div class="action-card" onclick="window.location.href='cliente-consumi.php'">
                        <div class="icon">📊</div>
                        <h3>Visualizza Consumi</h3>
                        <p>Consulta lo storico dei tuoi consumi idrici</p>
                    </div>
                    
                    <div class="action-card" onclick="window.location.href='cliente-fatture.php'">
                        <div class="icon">💳</div>
                        <h3>Fatture</h3>
                        <p>Visualizza e paga le tue fatture</p>
                    </div>
                    
                    <div class="action-card" onclick="window.location.href='cliente-contratti.php'">
                        <div class="icon">📄</div>
                        <h3>Contratti e Forniture</h3>
                        <p>Visualizza i dettagli dei tuoi contratti</p>
                    </div>
                    
                    <div class="action-card" onclick="window.location.href='cliente-segnalazioni.php'">
                        <div class="icon">🎫</div>
                        <h3>Segnalazioni</h3>
                        <p>Apri una nuova segnalazione o consulta quelle esistenti</p>
                    </div>
                    
                    <div class="action-card" onclick="window.location.href='cliente-carte.php'">
                        <div class="icon">💳</div>
                        <h3>Gestisci Carte</h3>
                        <p>Registra i tuoi metodi di pagamento</p>
                    </div>
                </div>
                
            </div>
        </section>
    </main>
</body>
</html>
