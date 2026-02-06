<?php
/**
 * UNIME-ACQUE - Dettaglio Ticket (AMMINISTRATORE)
 * 
 * Visualizza il contenuto completo di una segnalazione e permette di chiuderla.
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

// Recupera ID ticket
$ticketId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($ticketId <= 0) {
    setFlashMessage('danger', 'ID ticket non valido.');
    header('Location: admin-ticket.php');
    exit;
}

$conn = getDbConnection();

// Recupera dettagli ticket
$query = "SELECT s.*, 
                 u1.nome as segnalante_nome, u1.cognome as segnalante_cognome, 
                 u1.email as segnalante_email, u1.telefono as segnalante_telefono,
                 u2.nome as operatore_nome, u2.cognome as operatore_cognome
          FROM SEGNALAZIONE s
          INNER JOIN UTENTE u1 ON s.IdUtente_segnalante = u1.IdUtente
          LEFT JOIN UTENTE u2 ON s.IdUtente_presa_in_carico = u2.IdUtente
          WHERE s.IdSegnalazione = ?
            AND (s.IdUtente_presa_in_carico IS NULL OR s.IdUtente_presa_in_carico = ?)
          LIMIT 1";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $ticketId, $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    setFlashMessage('danger', 'Ticket non trovato o non hai i permessi per visualizzarlo.');
    header('Location: admin-ticket.php');
    exit;
}

$ticket = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettaglio Ticket #<?php echo $ticketId; ?> | UNIME-ACQUE</title>
    
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
        
        .ticket-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--glass-border);
        }
        
        .ticket-id {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--color-text-light);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-aperto {
            background: rgba(255, 193, 7, 0.2);
            color: var(--color-warning);
            border: 1px solid var(--color-warning);
        }
        
        .status-chiuso {
            background: rgba(0, 200, 151, 0.2);
            color: var(--color-success);
            border: 1px solid var(--color-success);
        }
        
        .status-assegnato {
            background: rgba(5, 191, 219, 0.2);
            color: var(--color-primary-light);
            border: 1px solid var(--color-primary-light);
        }
        
        .info-section {
            margin-bottom: 2rem;
        }
        
        .info-section h3 {
            color: var(--color-accent);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .info-item {
            background: rgba(255, 255, 255, 0.03);
            padding: 1rem;
            border-radius: 8px;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: var(--color-text-muted);
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            color: var(--color-text-light);
            font-weight: 600;
        }
        
        .content-section {
            background: rgba(255, 255, 255, 0.03);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            margin-bottom: 2rem;
        }
        
        .content-section h3 {
            color: var(--color-accent);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        
        .content-text {
            color: var(--color-text-light);
            line-height: 1.8;
            white-space: pre-wrap;
        }
        
        .actions {
            display: flex;
            gap: 1rem;
            padding-top: 2rem;
            border-top: 1px solid var(--glass-border);
        }
        
        .btn {
            flex: 1;
            padding: 1.25rem;
            font-size: 1rem;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all var(--transition-fast);
        }
        
        .btn-close-ticket {
            background: var(--color-success);
            color: white;
            border: none;
            cursor: pointer;
        }
        
        .btn-close-ticket:hover {
            background: #00b881;
            transform: translateY(-2px);
        }
        
        .btn-close-ticket:disabled {
            background: #666;
            cursor: not-allowed;
            opacity: 0.5;
        }
        
        .btn-take {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            border: none;
            cursor: pointer;
        }
        
        .btn-take:hover {
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--color-text-muted);
            border: 1px solid var(--glass-border);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--color-text-light);
        }
        
        @media screen and (max-width: 768px) {
            .ticket-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
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
            
            <a href="admin-ticket.php" class="back-link">← Torna ai Ticket</a>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>" style="max-width: 900px; margin: 0 auto 2rem;">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <div class="ticket-card">
                <!-- Header -->
                <div class="ticket-header">
                    <div class="ticket-id">Ticket #<?php echo htmlspecialchars($ticket['IdSegnalazione']); ?></div>
                    <div>
                        <?php if ($ticket['data_chiusura']): ?>
                            <span class="status-badge status-chiuso">✓ Chiuso</span>
                        <?php elseif ($ticket['IdUtente_presa_in_carico']): ?>
                            <span class="status-badge status-assegnato">Assegnato a te</span>
                        <?php else: ?>
                            <span class="status-badge status-aperto">Non Assegnato</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Informazioni Segnalante -->
                <div class="info-section">
                    <h3>👤 Informazioni Segnalante</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Nome Completo</div>
                            <div class="info-value"><?php echo htmlspecialchars($ticket['segnalante_nome'] . ' ' . $ticket['segnalante_cognome']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($ticket['segnalante_email']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Telefono</div>
                            <div class="info-value"><?php echo htmlspecialchars($ticket['segnalante_telefono'] ?? 'N/D'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">ID Utente</div>
                            <div class="info-value">#<?php echo htmlspecialchars($ticket['IdUtente_segnalante']); ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Informazioni Ticket -->
                <div class="info-section">
                    <h3>📋 Dettagli Ticket</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Motivo Richiesta</div>
                            <div class="info-value"><?php echo htmlspecialchars($ticket['motivo_richiesta']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Data Apertura</div>
                            <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($ticket['data_apertura'])); ?></div>
                        </div>
                        <?php if ($ticket['data_chiusura']): ?>
                        <div class="info-item">
                            <div class="info-label">Data Chiusura</div>
                            <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($ticket['data_chiusura'])); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($ticket['operatore_nome']): ?>
                        <div class="info-item">
                            <div class="info-label">Operatore Assegnato</div>
                            <div class="info-value"><?php echo htmlspecialchars($ticket['operatore_nome'] . ' ' . $ticket['operatore_cognome']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Contenuto Richiesta -->
                <div class="content-section">
                    <h3>📄 Contenuto Richiesta</h3>
                    <div class="content-text"><?php echo htmlspecialchars($ticket['contenuto_richiesta']); ?></div>
                </div>
                
                <!-- Azioni -->
                <div class="actions">
                    <?php if (!$ticket['data_chiusura']): ?>
                        <?php if (!$ticket['IdUtente_presa_in_carico']): ?>
                            <!-- Ticket non assegnato: può prenderlo in carico -->
                            <form action="admin-ticket-prendi-carico.php" method="POST" style="flex: 1;">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="ticket_id" value="<?php echo $ticketId; ?>">
                                <button type="submit" class="btn btn-take">
                                    Prendi in Carico
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if ($ticket['IdUtente_presa_in_carico'] == $userId): ?>
                            <!-- Ticket assegnato a questo admin: può chiuderlo -->
                            <form action="admin-ticket-chiudi.php" method="POST" style="flex: 1;" onsubmit="return confirm('Confermi la chiusura del ticket?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="ticket_id" value="<?php echo $ticketId; ?>">
                                <button type="submit" class="btn btn-close-ticket">
                                    ✓ Chiudi Ticket
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Ticket chiuso -->
                        <button type="button" class="btn btn-close-ticket" disabled>
                            ✓ Ticket Chiuso
                        </button>
                    <?php endif; ?>
                    
                    <a href="admin-ticket.php" class="btn btn-secondary">
                        Torna alla Lista
                    </a>
                </div>
                
            </div>
            
        </div>
    </main>
</body>
</html>
