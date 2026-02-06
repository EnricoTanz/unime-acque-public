<?php
define('UNIME_ACQUE', true);
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
startSecureSession();
requireRole('CLIENTE');
$userId = getCurrentUserId();
$userName = getCurrentUserName();
$conn = getDbConnection();
$query = "SELECT 
            c.IdContratto,
            c.tipo_contratto,
            c.stato_contratto,
            c.data_stipula,
            c.data_inizio_validita,
            c.data_fine_validita,
            f.IdFornitura,
            f.indirizzo_fornitura,
            f.stato_fornitura,
            f.data_attivazione,
            f.data_disattivazione,
            ag.IdArea,
            ag.nome_area
          FROM CONTRATTO c
          LEFT JOIN FORNITURA f ON c.IdContratto = f.IdContratto
          LEFT JOIN AREA_GEOGRAFICA ag ON f.IdArea_fornitura = ag.IdArea
          WHERE c.IdUtente = ?
          ORDER BY c.stato_contratto DESC, c.data_stipula DESC, f.stato_fornitura DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$contratti = [];
while ($row = mysqli_fetch_assoc($result)) {
    $idContratto = $row['IdContratto'];
    if (!isset($contratti[$idContratto])) {
        $contratti[$idContratto] = [
            'IdContratto' => $row['IdContratto'],
            'tipo_contratto' => $row['tipo_contratto'],
            'stato_contratto' => $row['stato_contratto'],
            'data_stipula' => $row['data_stipula'],
            'data_inizio_validita' => $row['data_inizio_validita'],
            'data_fine_validita' => $row['data_fine_validita'],
            'forniture' => []
        ];
    }
    if ($row['IdFornitura']) {
        $contratti[$idContratto]['forniture'][] = [
            'IdFornitura' => $row['IdFornitura'],
            'indirizzo_fornitura' => $row['indirizzo_fornitura'],
            'stato_fornitura' => $row['stato_fornitura'],
            'data_attivazione' => $row['data_attivazione'],
            'data_disattivazione' => $row['data_disattivazione'],
            'nome_area' => $row['nome_area']
        ];
    }
}
mysqli_stmt_close($stmt);
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>I Miei Contratti | UNIME-ACQUE</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-content{padding-top:100px;padding-bottom:3rem;min-height:100vh;}
        .back-link{display:inline-block;color:var(--color-accent);margin-bottom:2rem;text-decoration:none;font-weight:600;}
        .contratto-card{background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:16px;padding:2rem;margin-bottom:2rem;}
        .contratto-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;}
        .contratto-title{display:flex;align-items:center;gap:1rem;}
        .contratto-title h3{font-size:1.5rem;color:var(--color-accent);}
        .badge{padding:0.5rem 1rem;border-radius:20px;font-size:0.85rem;font-weight:600;}
        .badge-attivo{background:rgba(46,204,113,0.2);color:#2ecc71;}
        .badge-cessato{background:rgba(149,165,166,0.2);color:#95a5a6;}
        .badge-domestica{background:rgba(52,152,219,0.2);color:#3498db;}
        .badge-business{background:rgba(155,89,182,0.2);color:#9b59b6;}
        .info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;margin-bottom:2rem;}
        .info-item{display:flex;flex-direction:column;gap:0.5rem;}
        .info-label{color:var(--color-text-muted);font-size:0.9rem;}
        .info-value{color:var(--color-text-light);font-weight:600;font-size:1.1rem;}
        .forniture-section h4{color:var(--color-text-light);margin-bottom:1rem;font-size:1.2rem;}
        .fornitura-list{display:grid;gap:1rem;}
        .fornitura-item{background:rgba(5,191,219,0.05);border:1px solid var(--glass-border);border-radius:10px;padding:1.5rem;}
        .fornitura-header{display:flex;justify-content:space-between;align-items:start;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;}
        .fornitura-indirizzo{font-size:1.1rem;font-weight:600;color:var(--color-text-light);}
        .fornitura-info{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;font-size:0.9rem;}
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
            <div style="margin-bottom:2rem;">
                <h1>📄 I Miei Contratti e Forniture</h1>
                <p style="color:var(--color-text-muted);">Visualizza i dettagli dei tuoi contratti e delle relative forniture</p>
            </div>
            <?php if($flashMessage):?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']);?>">
                    <?php echo htmlspecialchars($flashMessage['message']);?>
                </div>
            <?php endif;?>
            <?php if(empty($contratti)):?>
                <div class="contratto-card">
                    <div class="empty-state">
                        <div style="font-size:4rem;margin-bottom:1rem;">📋</div>
                        <h3>Nessun Contratto</h3>
                        <p>Non hai ancora contratti attivi nel sistema.</p>
                    </div>
                </div>
            <?php else:?>
                <?php foreach($contratti as $c):?>
                    <div class="contratto-card">
                        <div class="contratto-header">
                            <div class="contratto-title">
                                <h3>Contratto #<?php echo htmlspecialchars($c['IdContratto']);?></h3>
                                <span class="badge badge-<?php echo strtolower($c['tipo_contratto']);?>">
                                    <?php echo htmlspecialchars($c['tipo_contratto']);?>
                                </span>
                                <span class="badge badge-<?php echo strtolower($c['stato_contratto']);?>">
                                    <?php echo htmlspecialchars($c['stato_contratto']);?>
                                </span>
                            </div>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Data Stipula</div>
                                <div class="info-value"><?php echo date('d/m/Y',strtotime($c['data_stipula']));?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Inizio Validità</div>
                                <div class="info-value"><?php echo date('d/m/Y',strtotime($c['data_inizio_validita']));?></div>
                            </div>
                            <?php if($c['data_fine_validita']):?>
                                <div class="info-item">
                                    <div class="info-label">Fine Validità</div>
                                    <div class="info-value"><?php echo date('d/m/Y',strtotime($c['data_fine_validita']));?></div>
                                </div>
                            <?php endif;?>
                            <div class="info-item">
                                <div class="info-label">Numero Forniture</div>
                                <div class="info-value"><?php echo count($c['forniture']);?></div>
                            </div>
                        </div>
                        <?php if(!empty($c['forniture'])):?>
                            <div class="forniture-section">
                                <h4>🏠 Forniture Associate</h4>
                                <div class="fornitura-list">
                                    <?php foreach($c['forniture'] as $f):?>
                                        <div class="fornitura-item">
                                            <div class="fornitura-header">
                                                <div class="fornitura-indirizzo">
                                                    📍 <?php echo htmlspecialchars($f['indirizzo_fornitura']);?>
                                                </div>
                                                <span class="badge badge-<?php echo strtolower(str_replace(' ','-',$f['stato_fornitura']));?>">
                                                    <?php echo htmlspecialchars($f['stato_fornitura']);?>
                                                </span>
                                            </div>
                                            <div class="fornitura-info">
                                                <div>
                                                    <div class="info-label">Area</div>
                                                    <div class="info-value"><?php echo htmlspecialchars($f['nome_area']??'-');?></div>
                                                </div>
                                                <?php if($f['data_attivazione']):?>
                                                    <div>
                                                        <div class="info-label">Attivata il</div>
                                                        <div class="info-value"><?php echo date('d/m/Y',strtotime($f['data_attivazione']));?></div>
                                                    </div>
                                                <?php endif;?>
                                                <?php if($f['data_disattivazione']):?>
                                                    <div>
                                                        <div class="info-label">Disattivata il</div>
                                                        <div class="info-value"><?php echo date('d/m/Y',strtotime($f['data_disattivazione']));?></div>
                                                    </div>
                                                <?php endif;?>
                                            </div>
                                        </div>
                                    <?php endforeach;?>
                                </div>
                            </div>
                        <?php else:?>
                            <div class="empty-state">
                                <p>Nessuna fornitura associata a questo contratto</p>
                            </div>
                        <?php endif;?>
                    </div>
                <?php endforeach;?>
            <?php endif;?>
        </div>
    </main>
</body>
</html>
