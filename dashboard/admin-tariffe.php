<?php
/**
 * UNIME-ACQUE - Gestione Tariffe (AMMINISTRATORE)
 * 
 * Visualizza tariffe esistenti e permette di crearne di nuove.
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

// Recupera tutte le tariffe
$query = "SELECT * FROM TARIFFA ORDER BY IdTariffa DESC";
$result = mysqli_query($conn, $query);

$tariffe = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tariffe[] = $row;
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Tariffe | UNIME-ACQUE</title>
    
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
        
        .page-header .subtitle {
            color: var(--color-text-muted);
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 2rem;
        }
        
        .form-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2rem;
            height: fit-content;
        }
        
        .form-card h3 {
            color: var(--color-accent);
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--color-text-light);
            font-size: 0.9rem;
        }
        
        .form-label .required {
            color: var(--color-danger);
        }
        
        .form-input,
        .form-textarea {
            width: 100%;
            padding: 1rem 1.25rem;
            font-family: var(--font-body);
            font-size: 1rem;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            transition: all var(--transition-fast);
        }
        
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--color-primary-light);
            box-shadow: 0 0 0 3px rgba(5, 191, 219, 0.15);
        }
        
        .form-hint {
            font-size: 0.85rem;
            color: var(--color-text-muted);
            margin-top: 0.5rem;
        }
        
        .btn-create {
            width: 100%;
            padding: 1.25rem;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .btn-create:hover {
            transform: translateY(-2px);
        }
        
        .table-container {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.5rem;
            overflow-x: auto;
        }
        
        .table-container h3 {
            color: var(--color-text-light);
            margin-bottom: 1.5rem;
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
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--color-text-muted);
        }
        
        .empty-state .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        @media screen and (max-width: 992px) {
            .content-grid {
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
                <h1>💳 Gestione Tariffe</h1>
                <p class="subtitle">Crea e visualizza le tariffe del sistema</p>
            </div>
            
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']); ?>" style="margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
            <?php endif; ?>
            
            <div class="content-grid">
                
                <!-- Form Creazione Tariffa -->
                <div class="form-card">
                    <h3>➕ Crea Nuova Tariffa</h3>
                    
                    <form method="POST" action="admin-tariffe-crea.php" id="createTariffaForm">
                        <?php echo csrfField(); ?>
                        
                        <div class="form-group">
                            <label class="form-label" for="nome_tariffa">
                                Nome Tariffa <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="nome_tariffa" 
                                name="nome_tariffa" 
                                class="form-input" 
                                placeholder="es: Tariffa Domestica Base"
                                required
                                maxlength="50"
                            >
                            <p class="form-hint">Nome identificativo della tariffa (max 50 caratteri)</p>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="tariffa_applicata">
                                Tariffa Applicata (€/m³) <span class="required">*</span>
                            </label>
                            <input 
                                type="number" 
                                id="tariffa_applicata" 
                                name="tariffa_applicata" 
                                class="form-input" 
                                placeholder="es: 1.5000"
                                required
                                min="0"
                                max="99.9999"
                                step="0.0001"
                            >
                            <p class="form-hint">Prezzo per metro cubo (max 4 decimali)</p>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="descrizione">
                                Descrizione
                            </label>
                            <textarea 
                                id="descrizione" 
                                name="descrizione" 
                                class="form-textarea" 
                                placeholder="Descrizione della tariffa (opzionale)"
                                maxlength="200"
                            ></textarea>
                            <p class="form-hint">Max 200 caratteri</p>
                        </div>
                        
                        <button type="submit" class="btn-create">
                            Crea Tariffa
                        </button>
                    </form>
                </div>
                
                <!-- Lista Tariffe -->
                <div class="table-container">
                    <h3>📋 Tariffe Esistenti</h3>
                    
                    <?php if (empty($tariffe)): ?>
                        <div class="empty-state">
                            <div class="icon">📭</div>
                            <h3>Nessuna Tariffa Censita</h3>
                            <p>Crea la prima tariffa utilizzando il form a sinistra.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Tariffa (€/m³)</th>
                                    <th>Descrizione</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tariffe as $tariffa): ?>
                                    <tr>
                                        <td><strong>#<?php echo htmlspecialchars($tariffa['IdTariffa']); ?></strong></td>
                                        <td><strong><?php echo htmlspecialchars($tariffa['nome_tariffa']); ?></strong></td>
                                        <td>
                                            <span style="color: var(--color-accent); font-weight: 600;">
                                                € <?php echo number_format($tariffa['tariffa_applicata'], 4, ',', '.'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($tariffa['descrizione']): ?>
                                                <?php echo htmlspecialchars($tariffa['descrizione']); ?>
                                            <?php else: ?>
                                                <span style="color: var(--color-text-muted);">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
            </div>
            
        </div>
    </main>
    
    <script>
        // Validazione form
        document.getElementById('createTariffaForm').addEventListener('submit', function(e) {
            const nomeTariffa = document.getElementById('nome_tariffa').value.trim();
            const tariffaApplicata = parseFloat(document.getElementById('tariffa_applicata').value);
            
            if (!nomeTariffa) {
                e.preventDefault();
                alert('Il nome della tariffa è obbligatorio.');
                return false;
            }
            
            if (isNaN(tariffaApplicata) || tariffaApplicata < 0) {
                e.preventDefault();
                alert('La tariffa applicata deve essere un numero positivo.');
                return false;
            }
            
            if (tariffaApplicata > 99.9999) {
                e.preventDefault();
                alert('La tariffa applicata non può superare 99.9999 €/m³.');
                return false;
            }
            
            return confirm('Confermi la creazione della nuova tariffa?');
        });
    </script>
</body>
</html>
