<?php
define('UNIME_ACQUE', true);
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
startSecureSession();
requireRole('CLIENTE');
$userId = getCurrentUserId();
$conn = getDbConnection();
$query = "SELECT s.*, u_op.nome as operatore_nome, u_op.cognome as operatore_cognome
          FROM SEGNALAZIONE s
          LEFT JOIN UTENTE u_op ON s.IdUtente_presa_in_carico = u_op.IdUtente
          WHERE s.IdUtente_segnalante = ?
          ORDER BY s.data_apertura DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$segnalazioni = [];
while ($row = mysqli_fetch_assoc($result)) {
    $segnalazioni[] = $row;
}
mysqli_stmt_close($stmt);
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Le Mie Segnalazioni | UNIME-ACQUE</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-content{padding-top:100px;padding-bottom:3rem;min-height:100vh;}
        .back-link{display:inline-block;color:var(--color-accent);margin-bottom:2rem;text-decoration:none;font-weight:600;}
        .btn-primary{background:linear-gradient(135deg,#2ecc71 0%,#27ae60 100%);color:white;padding:0.75rem 1.5rem;border-radius:10px;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-block;}
        .ticket-card{background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:16px;padding:1.5rem;margin-bottom:1rem;}
        .ticket-header{display:flex;justify-content:space-between;align-items:start;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;}
        .badge{padding:0.5rem 1rem;border-radius:20px;font-size:0.85rem;font-weight:600;}
        .badge-aperta{background:rgba(52,152,219,0.2);color:#3498db;}
        .badge-chiusa{background:rgba(149,165,166,0.2);color:#95a5a6;}
        .badge-tecnica{background:rgba(230,126,34,0.2);color:#e67e22;}
        .badge-commerciale{background:rgba(155,89,182,0.2);color:#9b59b6;}
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
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
                <div>
                    <h1>🎫 Le Mie Segnalazioni</h1>
                    <p style="color:var(--color-text-muted);">Visualizza le tue segnalazioni o aprine una nuova</p>
                </div>
                <a href="cliente-segnalazione-nuova.php" class="btn-primary">➕ Nuova Segnalazione</a>
            </div>
            <?php if($flashMessage):?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']);?>">
                    <?php echo htmlspecialchars($flashMessage['message']);?>
                </div>
            <?php endif;?>
            <?php if(empty($segnalazioni)):?>
                <div class="ticket-card">
                    <div class="empty-state">
                        <div style="font-size:4rem;margin-bottom:1rem;">🎫</div>
                        <h3>Nessuna Segnalazione</h3>
                        <p>Non hai ancora aperto segnalazioni.</p>
                    </div>
                </div>
            <?php else:?>
                <?php foreach($segnalazioni as $s):?>
                    <div class="ticket-card">
                        <div class="ticket-header">
                            <div>
                                <h3 style="color:var(--color-accent);margin-bottom:0.5rem;">Segnalazione #<?php echo $s['IdSegnalazione'];?></h3>
                                <p style="color:var(--color-text-muted);font-size:0.9rem;">Aperta il <?php echo date('d/m/Y',strtotime($s['data_apertura']));?></p>
                            </div>
                            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                                <span class="badge badge-<?php echo strtolower($s['motivo_richiesta']);?>"><?php echo htmlspecialchars($s['motivo_richiesta']);?></span>
                                <span class="badge badge-<?php echo $s['data_chiusura']?'chiusa':'aperta';?>"><?php echo $s['data_chiusura']?'CHIUSA':'APERTA';?></span>
                            </div>
                        </div>
                        <div style="margin-bottom:1rem;">
                            <div style="font-weight:600;margin-bottom:0.5rem;">Contenuto:</div>
                            <p style="color:var(--color-text-light);"><?php echo nl2br(htmlspecialchars($s['contenuto_richiesta']));?></p>
                        </div>
                        <?php if($s['IdUtente_presa_in_carico']):?>
                            <div style="color:var(--color-text-muted);font-size:0.9rem;">
                                Assegnata a: <?php echo htmlspecialchars($s['operatore_nome'].' '.$s['operatore_cognome']);?>
                            </div>
                        <?php endif;?>
                        <?php if($s['data_chiusura']):?>
                            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--glass-border);">
                                <div style="font-weight:600;color:#2ecc71;margin-bottom:0.5rem;">✅ Chiusa il <?php echo date('d/m/Y',strtotime($s['data_chiusura']));?></div>
                            </div>
                        <?php endif;?>
                    </div>
                <?php endforeach;?>
            <?php endif;?>
        </div>
    </main>
</body>
</html>
