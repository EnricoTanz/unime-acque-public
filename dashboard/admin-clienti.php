<?php
/**
 * UNIME-ACQUE - Gestione Clienti (AMMINISTRATORE)
 * 
 * Registra nuovi utenti di tipo CLIENTE.
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

// Recupera lista clienti esistenti
$query = "SELECT IdUtente, nome, cognome, email, codice_fiscale, telefono, ragione_sociale, data_nascita, data_creazione
          FROM UTENTE
          WHERE ruolo = 'CLIENTE'
          ORDER BY data_creazione DESC, cognome, nome";
$result = mysqli_query($conn, $query);

$clienti = [];
while ($row = mysqli_fetch_assoc($result)) {
    $clienti[] = $row;
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Clienti | UNIME-ACQUE</title>
    
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
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .btn-new-cliente {
            background: linear-gradient(135deg, var(--color-success) 0%, #00b881 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 2rem;
            transition: all var(--transition-fast);
        }
        
        .btn-new-cliente:hover {
            transform: translateY(-2px);
            color: white;
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
            font-size: 0.9rem;
        }
        
        thead {
            background: rgba(5, 191, 219, 0.1);
        }
        
        th {
            padding: 1rem 0.75rem;
            text-align: left;
            font-weight: 600;
            color: var(--color-accent);
            border-bottom: 2px solid var(--glass-border);
            white-space: nowrap;
        }
        
        td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid var(--glass-border);
            color: var(--color-text-light);
        }
        
        tr:hover {
            background: rgba(5, 191, 219, 0.05);
        }
        
        .badge-business {
            background: rgba(255, 193, 7, 0.2);
            color: var(--color-warning);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
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
        
        @media screen and (max-width: 1200px) {
            table {
                font-size: 0.8rem;
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
                <h1>👥 Gestione Clienti</h1>
                <p class="subtitle">Registra nuovi clienti per poter creare contratti</p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>" style="margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <a href="admin-clienti-nuovo.php" class="btn-new-cliente">
                ➕ Registra Nuovo Cliente
            </a>
            
            <div class="table-container">
                <?php if (empty($clienti)): ?>
                    <div class="empty-state">
                        <div class="icon">📭</div>
                        <h3>Nessun Cliente Registrato</h3>
                        <p>Registra il primo cliente per poter creare contratti.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome Completo</th>
                                <th>Ragione Sociale</th>
                                <th>Codice Fiscale</th>
                                <th>Email</th>
                                <th>Telefono</th>
                                <th>Data Nascita</th>
                                <th>Data Registrazione</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clienti as $cliente): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($cliente['IdUtente']); ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($cliente['nome'] . ' ' . $cliente['cognome']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($cliente['ragione_sociale']): ?>
                                            <span class="badge-business">
                                                <?php echo htmlspecialchars($cliente['ragione_sociale']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: var(--color-text-muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($cliente['codice_fiscale']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['telefono'] ?? '-'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($cliente['data_nascita'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($cliente['data_creazione'])); ?></td>
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
