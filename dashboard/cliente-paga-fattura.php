<?php
define('UNIME_ACQUE', true);
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
startSecureSession();
requireRole('CLIENTE');
$userId=getCurrentUserId();
$idFattura=isset($_GET['id'])?(int)$_GET['id']:0;
if($idFattura<=0){setFlashMessage('danger','Fattura non valida.');header('Location:cliente-fatture.php');exit;}
$conn=getDbConnection();
$queryFattura="SELECT f.*,c.IdContratto FROM FATTURA f INNER JOIN CONTRATTO c ON f.IdContratto=c.IdContratto WHERE f.IdFattura=? AND c.IdUtente=? LIMIT 1";
$stmtF=mysqli_prepare($conn,$queryFattura);
mysqli_stmt_bind_param($stmtF,'ii',$idFattura,$userId);
mysqli_stmt_execute($stmtF);
$resultF=mysqli_stmt_get_result($stmtF);
if(mysqli_num_rows($resultF)===0){setFlashMessage('danger','Fattura non trovata.');header('Location:cliente-fatture.php');exit;}
$fattura=mysqli_fetch_assoc($resultF);
mysqli_stmt_close($stmtF);
if($fattura['data_pagamento']){setFlashMessage('warning','Fattura già pagata.');header('Location:cliente-fatture.php');exit;}
$queryCarte="SELECT * FROM CARTA_DI_CREDITO WHERE IdUtente=? AND data_scadenza>=CURRENT_DATE ORDER BY data_scadenza DESC";
$stmtC=mysqli_prepare($conn,$queryCarte);
mysqli_stmt_bind_param($stmtC,'i',$userId);
mysqli_stmt_execute($stmtC);
$resultC=mysqli_stmt_get_result($stmtC);
$carte=[];
while($row=mysqli_fetch_assoc($resultC)){$carte[]=$row;}
mysqli_stmt_close($stmtC);
$flashMessage=getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Paga Fattura #<?php echo $idFattura;?> | UNIME-ACQUE</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-content{padding-top:100px;padding-bottom:3rem;min-height:100vh;}
        .back-link{display:inline-block;color:var(--color-accent);margin-bottom:2rem;text-decoration:none;font-weight:600;}
        .info-card{background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:16px;padding:2rem;margin-bottom:2rem;}
        .info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;margin-top:1.5rem;}
        .info-item{display:flex;flex-direction:column;gap:0.5rem;}
        .info-label{color:var(--color-text-muted);font-size:0.9rem;}
        .info-value{color:var(--color-text-light);font-weight:600;font-size:1.1rem;}
        .form-card{background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:16px;padding:2rem;}
        .form-group{margin-bottom:1.5rem;}
        .form-label{display:block;margin-bottom:0.5rem;font-weight:600;color:var(--color-text-light);}
        .form-select{width:100%;padding:0.75rem;border:1px solid var(--glass-border);border-radius:8px;background:rgba(255,255,255,0.05);color:var(--color-text);font-size:1rem;}
        .form-select option{background:#0a1929;color:#e0e7ef;padding:0.5rem;}
        .btn{padding:0.75rem 1.5rem;border-radius:10px;font-weight:600;border:none;cursor:pointer;font-size:1rem;}
        .btn-primary{background:linear-gradient(135deg,#2ecc71 0%,#27ae60 100%);color:white;}
        .btn-secondary{background:rgba(255,255,255,0.1);color:var(--color-text);border:1px solid var(--glass-border);}
        .warning-box{background:rgba(241,196,15,0.1);border:1px solid #f1c40f;border-radius:10px;padding:1.5rem;margin-bottom:2rem;}
        .alert{padding:1rem 1.5rem;border-radius:10px;margin-bottom:1.5rem;font-weight:500;}
        .alert-success{background:rgba(46,204,113,0.2);color:#2ecc71;border:1px solid #2ecc71;}
        .alert-danger{background:rgba(231,76,60,0.2);color:#e74c3c;border:1px solid #e74c3c;}
        .alert-warning{background:rgba(241,196,15,0.2);color:#f1c40f;border:1px solid #f1c40f;}
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
            <a href="cliente-fatture.php" class="back-link">← Torna alle Fatture</a>
            <h1>💳 Paga Fattura #<?php echo $idFattura;?></h1>
            <?php if($flashMessage):?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']);?>">
                    <?php echo htmlspecialchars($flashMessage['message']);?>
                </div>
            <?php endif;?>
            <div class="info-card">
                <h3>Dettagli Fattura</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Importo</div>
                        <div class="info-value" style="color:var(--color-accent);font-size:2rem;">€ <?php echo number_format($fattura['importo_fattura'],2,',','.');?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Data Emissione</div>
                        <div class="info-value"><?php echo date('d/m/Y',strtotime($fattura['data_emissione']));?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Data Scadenza</div>
                        <div class="info-value"><?php echo date('d/m/Y',strtotime($fattura['data_scadenza']));?></div>
                    </div>
                    <?php if($fattura['sconto']>0):?>
                    <div class="info-item">
                        <div class="info-label">Sconto Applicato</div>
                        <div class="info-value" style="color:#2ecc71;">-<?php echo number_format($fattura['sconto'],0);?>%</div>
                    </div>
                    <?php endif;?>
                    <?php if($fattura['sovrapprezzo']>0):?>
                    <div class="info-item">
                        <div class="info-label">Sovrapprezzo</div>
                        <div class="info-value" style="color:#e67e22;">+<?php echo number_format($fattura['sovrapprezzo'],0);?>%</div>
                    </div>
                    <?php endif;?>
                </div>
            </div>
            <?php if(empty($carte)):?>
                <div class="warning-box">
                    <h3 style="color:#f1c40f;margin-bottom:1rem;">⚠️ Nessuna Carta Registrata</h3>
                    <p style="margin-bottom:1rem;">Per effettuare il pagamento devi prima registrare una carta di credito valida.</p>
                    <a href="cliente-carte.php" class="btn btn-primary">Registra una Carta</a>
                </div>
            <?php else:?>
                <div class="form-card">
                    <h3 style="margin-bottom:1.5rem;">Seleziona Metodo di Pagamento</h3>
                    <form action="cliente-paga-fattura-process.php" method="POST" onsubmit="return confirm('Confermi il pagamento di € <?php echo number_format($fattura['importo_fattura'],2,',','.');?>?');">
                        <?php echo csrfField();?>
                        <input type="hidden" name="id_fattura" value="<?php echo $idFattura;?>">
                        <div class="form-group">
                            <label class="form-label">Carta di Credito <span style="color:#e74c3c;">*</span></label>
                            <select name="id_carta" class="form-select" required>
                                <option value="">-- Seleziona Carta --</option>
                                <?php foreach($carte as $c):?>
                                    <option value="<?php echo $c['IdCarta'];?>">
                                        •••• •••• •••• <?php echo substr($c['numero_carta'],-4);?> 
                                        (Scad. <?php echo date('m/Y',strtotime($c['data_scadenza']));?>)
                                    </option>
                                <?php endforeach;?>
                            </select>
                        </div>
                        <div style="display:flex;gap:1rem;justify-content:flex-end;">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='cliente-fatture.php'">Annulla</button>
                            <button type="submit" class="btn btn-primary">Paga € <?php echo number_format($fattura['importo_fattura'],2,',','.');?></button>
                        </div>
                    </form>
                </div>
            <?php endif;?>
        </div>
    </main>
</body>
</html>
