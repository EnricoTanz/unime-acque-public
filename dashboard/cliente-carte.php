<?php
define('UNIME_ACQUE', true);
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
startSecureSession();
requireRole('CLIENTE');
$userId = getCurrentUserId();
$conn = getDbConnection();
$query = "SELECT * FROM CARTA_DI_CREDITO WHERE IdUtente = ? ORDER BY data_scadenza DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$carte = [];
while ($row = mysqli_fetch_assoc($result)) {
    $carte[] = $row;
}
mysqli_stmt_close($stmt);
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Le Mie Carte | UNIME-ACQUE</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-content{padding-top:100px;padding-bottom:3rem;min-height:100vh;}
        .back-link{display:inline-block;color:var(--color-accent);margin-bottom:2rem;text-decoration:none;font-weight:600;}
        .btn-primary{background:linear-gradient(135deg,#2ecc71 0%,#27ae60 100%);color:white;padding:0.75rem 1.5rem;border-radius:10px;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-block;}
        .form-card{background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:16px;padding:2rem;margin-bottom:2rem;}
        .form-group{margin-bottom:1.5rem;}
        .form-label{display:block;margin-bottom:0.5rem;font-weight:600;color:var(--color-text-light);}
        .form-input,.form-select{width:100%;padding:0.75rem;border:1px solid var(--glass-border);border-radius:8px;background:rgba(255,255,255,0.05);color:var(--color-text);font-size:1rem;}
        .carte-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.5rem;margin-top:2rem;}
        .carta-card{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:16px;padding:2rem;color:white;position:relative;overflow:hidden;}
        .carta-card::before{content:'';position:absolute;top:-50%;right:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,transparent 70%);pointer-events:none;}
        .carta-numero{font-size:1.5rem;font-weight:600;letter-spacing:2px;margin:1.5rem 0;}
        .carta-info{display:flex;justify-content:space-between;align-items:end;}
        .badge-scaduta{background:rgba(231,76,60,0.3);color:#fff;padding:0.25rem 0.75rem;border-radius:20px;font-size:0.75rem;font-weight:600;}
        .badge-valida{background:rgba(46,204,113,0.3);color:#fff;padding:0.25rem 0.75rem;border-radius:20px;font-size:0.75rem;font-weight:600;}
        .empty-state{text-align:center;padding:3rem;color:var(--color-text-muted);}
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
            <a href="cliente.php" class="back-link">← Torna alla Dashboard</a>
            <h1>💳 Le Mie Carte di Credito</h1>
            <p style="color:var(--color-text-muted);margin-bottom:2rem;">Gestisci i tuoi metodi di pagamento</p>
            <?php if($flashMessage):?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']);?>">
                    <?php echo htmlspecialchars($flashMessage['message']);?>
                </div>
            <?php endif;?>
            <div class="form-card">
                <h3 style="margin-bottom:1.5rem;">➕ Registra Nuova Carta</h3>
                <form action="cliente-carte-process.php" method="POST">
                    <?php echo csrfField();?>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Numero Carta <span style="color:#e74c3c;">*</span></label>
                            <input type="text" name="numero_carta" class="form-input" required maxlength="16" pattern="[0-9]{16}" placeholder="1234567890123456">
                        </div>
                        <div class="form-group">
                            <label class="form-label">CVV <span style="color:#e74c3c;">*</span></label>
                            <input type="text" name="cvv" class="form-input" required maxlength="3" pattern="[0-9]{3}" placeholder="123">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Data Scadenza <span style="color:#e74c3c;">*</span></label>
                            <input type="month" name="data_scadenza" class="form-input" required min="<?php echo date('Y-m');?>">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Registra Carta</button>
                </form>
            </div>
            <?php if(empty($carte)):?>
                <div class="form-card">
                    <div class="empty-state">
                        <div style="font-size:4rem;margin-bottom:1rem;">💳</div>
                        <h3>Nessuna Carta Registrata</h3>
                        <p>Registra una carta per effettuare pagamenti online.</p>
                    </div>
                </div>
            <?php else:?>
                <h3 style="margin:2rem 0 1rem;">Le Tue Carte</h3>
                <div class="carte-grid">
                    <?php foreach($carte as $c):
                        $isScaduta = strtotime($c['data_scadenza']) < time();
                    ?>
                        <div class="carta-card">
                            <div style="font-size:0.9rem;opacity:0.8;">CARTA DI CREDITO</div>
                            <div class="carta-numero">•••• •••• •••• <?php echo substr($c['numero_carta'],-4);?></div>
                            <div class="carta-info">
                                <div>
                                    <div style="font-size:0.8rem;opacity:0.7;">Scadenza</div>
                                    <div style="font-weight:600;"><?php echo date('m/Y',strtotime($c['data_scadenza']));?></div>
                                </div>
                                <span class="badge-<?php echo $isScaduta?'scaduta':'valida';?>">
                                    <?php echo $isScaduta?'SCADUTA':'VALIDA';?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>
            <?php endif;?>
        </div>
    </main>
</body>
</html>
