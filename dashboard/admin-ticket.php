<?php
/**
 * UNIME-ACQUE - Gestione Ticket (AMMINISTRATORE)
 * 
 * Visualizza segnalazioni non assegnate o assegnate all'amministratore.
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

$conn = getDbConnection();

// Recupera ticket NON assegnati o assegnati a questo amministratore
$query = "SELECT s.IdSegnalazione, s.motivo_richiesta, s.data_apertura, s.data_chiusura,
                 s.IdUtente_segnalante, s.IdUtente_presa_in_carico,
                 u.nome, u.cognome, u.email
          FROM SEGNALAZIONE s
          INNER JOIN UTENTE u ON s.IdUtente_segnalante = u.IdUtente
          WHERE s.IdUtente_presa_in_carico IS NULL 
             OR s.IdUtente_presa_in_carico = ?
          ORDER BY 
            CASE WHEN s.data_chiusura IS NULL THEN 0 ELSE 1 END,
            s.data_apertura DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$tickets = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tickets[] = $row;
}
mysqli_stmt_close($stmt);

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Ticket | UNIME-ACQUE</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
    
    <style>
        .page-content {
            padding-top: 100px;
            padding-bottom: 3rem;
            min-height: 100vh;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .page-header .subtitle {
            color: var(--color-text-muted);
        }
        
        .table-container {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.5rem;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: rgba(5, 191, 219, 0.1);
        }
        
        th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--color-accent);
            border-bottom: 2px solid var(--glass-border);
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid var(--glass-border);
            color: var(--color-text-light);
        }
        
        tr:hover {
            background: rgba(5, 191, 219, 0.05);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-aperto {
            background: rgba(255, 193, 7, 0.2);
            color: var(--color-warning);
        }
        
        .status-chiuso {
            background: rgba(0, 200, 151, 0.2);
            color: var(--color-success);
        }
        
        .status-assegnato {
            background: rgba(5, 191, 219, 0.2);
            color: var(--color-primary-light);
        }
        
        .btn-view {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all var(--transition-fast);
        }
        
        .btn-view:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--color-text-muted);
        }
        
        .empty-state .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .back-link {
            display: inline-block;
            color: var(--color-accent);
            margin-bottom: 2rem;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            color: var(--color-primary-light);
        }
        
        @media screen and (max-width: 992px) {
            table {
                font-size: 0.85rem;
            }
            
            th, td {
                padding: 0.75rem 0.5rem;
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
                        <li><a href="amministratore.php">Dashboard</a></li>
                        <li><a href="../auth/logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="page-content">
        <div class="container">
            
            <a href="amministratore.php" class="back-link">← Torna alla Dashboard</a>
            
            <div class="page-header">
                <h1>🎫 Gestione Ticket</h1>
                <p class="subtitle">Visualizza e gestisci le segnalazioni dei clienti</p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>" style="margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <div class="table-container">
                <?php if (empty($tickets)): ?>
                    <div class="empty-state">
                        <div class="icon">📭</div>
                        <h3>Nessun Ticket Disponibile</h3>
                        <p>Non ci sono segnalazioni non assegnate o assegnate a te al momento.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Segnalante</th>
                                <th>Motivo</th>
                                <th>Data Apertura</th>
                                <th>Stato</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($ticket['IdSegnalazione']); ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($ticket['nome'] . ' ' . $ticket['cognome']); ?><br>
                                        <small style="color: var(--color-text-muted);"><?php echo htmlspecialchars($ticket['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($ticket['motivo_richiesta']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($ticket['data_apertura'])); ?></td>
                                    <td>
                                        <?php if ($ticket['data_chiusura']): ?>
                                            <span class="status-badge status-chiuso">Chiuso</span>
                                        <?php elseif ($ticket['IdUtente_presa_in_carico']): ?>
                                            <span class="status-badge status-assegnato">Assegnato a te</span>
                                        <?php else: ?>
                                            <span class="status-badge status-aperto">Non Assegnato</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="admin-ticket-dettaglio.php?id=<?php echo $ticket['IdSegnalazione']; ?>" class="btn-view">
                                            Visualizza
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
        </div>
    </main>
</body>
</html>
