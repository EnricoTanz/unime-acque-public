<?php
define('UNIME_ACQUE', true);
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
startSecureSession();
requireRole('CLIENTE');
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Nuova Segnalazione | UNIME-ACQUE</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-content{padding-top:100px;padding-bottom:3rem;min-height:100vh;}
        .back-link{display:inline-block;color:var(--color-accent);margin-bottom:2rem;text-decoration:none;font-weight:600;}
        .form-card{background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:16px;padding:2rem;}
        .form-group{margin-bottom:1.5rem;}
        .form-label{display:block;margin-bottom:0.5rem;font-weight:600;color:var(--color-text-light);}
        .required{color:#e74c3c;}
        .form-input,.form-select,.form-textarea{width:100%;padding:0.75rem;border:1px solid var(--glass-border);border-radius:8px;background:rgba(255,255,255,0.05);color:var(--color-text);font-size:1rem;}
        .form-select option{background:#0a1929;color:#e0e7ef;padding:0.5rem;}
        .form-textarea{min-height:150px;resize:vertical;font-family:inherit;}
        .form-hint{color:var(--color-text-muted);font-size:0.9rem;margin-top:0.3rem;}
        .form-actions{display:flex;gap:1rem;justify-content:flex-end;margin-top:2rem;}
        .btn{padding:0.75rem 1.5rem;border-radius:10px;font-weight:600;border:none;cursor:pointer;font-size:1rem;text-decoration:none;display:inline-block;}
        .btn-primary{background:linear-gradient(135deg,#2ecc71 0%,#27ae60 100%);color:white;}
        .btn-secondary{background:rgba(255,255,255,0.1);color:var(--color-text);border:1px solid var(--glass-border);}
        .alert{padding:1rem 1.5rem;border-radius:10px;margin-bottom:1.5rem;font-weight:500;}
        .alert-success{background:rgba(46,204,113,0.2);color:#2ecc71;border:1px solid #2ecc71;}
        .alert-danger{background:rgba(231,76,60,0.2);color:#e74c3c;border:1px solid #e74c3c;}
    </style>
</head>
<body>
    <header class="main-header scrolled" style="position:fixed;">
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="logo">
                    <span class="logo-company">Ecelesti S.p.A.</span>
                    <span class="logo-brand">UNIME<span>-ACQUE</span></span>
                </a>
                <nav class="main-nav">
                    <ul class="nav-menu">
                        <li><a href="cliente.php">Dashboard</a></li>
                        <li><a href="../auth/logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <main class="page-content">
        <div class="container">
            <a href="cliente-segnalazioni.php" class="back-link">← Torna alle Segnalazioni</a>
            <h1>➕ Nuova Segnalazione</h1>
            <p style="color:var(--color-text-muted);margin-bottom:2rem;">Apri una segnalazione per richiedere assistenza</p>
            <?php if($flashMessage):?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']);?>">
                    <?php echo htmlspecialchars($flashMessage['message']);?>
                </div>
            <?php endif;?>
            <div class="form-card">
                <form action="cliente-segnalazione-process.php" method="POST">
                    <?php echo csrfField();?>
                    <div class="form-group">
                        <label class="form-label" for="tipo">Tipo Segnalazione <span class="required">*</span></label>
                        <select id="tipo" name="tipo" class="form-select" required>
                            <option value="">-- Seleziona Tipo --</option>
                            <option value="TECNICA">Tecnica (Guasti, perdite, problemi contatore)</option>
                            <option value="COMMERCIALE">Commerciale (Fatturazione, contratti, pagamenti)</option>
                        </select>
                        <p class="form-hint">Scegli il tipo di assistenza di cui hai bisogno</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="descrizione">Descrizione <span class="required">*</span></label>
                        <textarea id="descrizione" name="descrizione" class="form-textarea" required maxlength="1000" placeholder="Descrivi dettagliatamente il problema o la richiesta..."></textarea>
                        <p class="form-hint">Massimo 1000 caratteri</p>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='cliente-segnalazioni.php'">Annulla</button>
                        <button type="submit" class="btn btn-primary">Invia Segnalazione</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
