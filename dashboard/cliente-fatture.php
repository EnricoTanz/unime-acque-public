<?php
define('UNIME_ACQUE', true);
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
startSecureSession();
requireRole('CLIENTE');
$userId = getCurrentUserId();
$conn = getDbConnection();
$query = "SELECT f.*, c.IdContratto
          FROM FATTURA f
          INNER JOIN CONTRATTO c ON f.IdContratto = c.IdContratto
          WHERE c.IdUtente = ?
          ORDER BY f.data_emissione DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$fatture = [];
while ($row = mysqli_fetch_assoc($result)) {
    $fatture[] = $row;
}
mysqli_stmt_close($stmt);
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Le Mie Fatture | UNIME-ACQUE</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-content{padding-top:100px;padding-bottom:3rem;min-height:100vh;}
        .back-link{display:inline-block;color:var(--color-accent);margin-bottom:2rem;text-decoration:none;font-weight:600;}
        .table-container{background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:16px;padding:1.5rem;overflow-x:auto;}
        table{width:100%;border-collapse:collapse;}
        thead{background:rgba(5,191,219,0.1);}
        th{padding:1rem;text-align:left;font-weight:600;color:var(--color-accent);border-bottom:2px solid var(--glass-border);white-space:nowrap;}
        td{padding:1rem;border-bottom:1px solid var(--glass-border);color:var(--color-text-light);}
        tr:hover{background:rgba(5,191,219,0.05);}
        .badge{padding:0.25rem 0.75rem;border-radius:20px;font-size:0.75rem;font-weight:600;}
        .badge-pagata{background:rgba(46,204,113,0.2);color:#2ecc71;}
        .badge-non-pagata{background:rgba(231,76,60,0.2);color:#e74c3c;}
        .badge-scaduta{background:rgba(220,53,69,0.2);color:#dc3545;}
        .badge-sconto{background:rgba(0,200,151,0.2);color:#00c897;}
        .badge-sovrapprezzo{background:rgba(230,126,34,0.2);color:#e67e22;}
        .btn-paga{background:linear-gradient(135deg,#2ecc71 0%,#27ae60 100%);color:white;padding:0.5rem 1rem;border-radius:8px;border:none;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block;font-size:0.9rem;}
        .btn-paga:hover{transform:translateY(-2px);}
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
            <h1>💰 Le Mie Fatture</h1>
            <p style="color:var(--color-text-muted);margin-bottom:2rem;">Visualizza e paga le tue fatture</p>
            <?php if($flashMessage):?>
                <div class="alert alert-<?php echo htmlspecialchars($flashMessage['type']);?>">
                    <?php echo htmlspecialchars($flashMessage['message']);?>
                </div>
            <?php endif;?>
            <div class="table-container">
                <?php if(empty($fatture)):?>
                    <div class="empty-state">
                        <div style="font-size:4rem;margin-bottom:1rem;">💰</div>
                        <h3>Nessuna Fattura</h3>
                        <p>Non ci sono fatture associate ai tuoi contratti.</p>
                    </div>
                <?php else:?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Contratto</th>
                                <th>Importo</th>
                                <th>Sconto</th>
                                <th>Sovrapprezzo</th>
                                <th>Data Emissione</th>
                                <th>Scadenza</th>
                                <th>Stato</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($fatture as $f):
                                $isPagata=$f['data_pagamento']!==null;
                                $isScaduta=!$isPagata && strtotime($f['data_scadenza'])<time();
                                $hasSconto=$f['sconto']>0;
                                $hasSovrapprezzo=$f['sovrapprezzo']>0;
                            ?>
                                <tr>
                                    <td><strong>#<?php echo $f['IdFattura'];?></strong></td>
                                    <td>#<?php echo $f['IdContratto'];?></td>
                                    <td><strong style="color:var(--color-accent);">€ <?php echo number_format($f['importo_fattura'],2,',','.');?></strong></td>
                                    <td>
                                        <?php if($hasSconto):?>
                                            <span class="badge badge-sconto">-<?php echo number_format($f['sconto'],0);?>%</span>
                                        <?php else:?>
                                            -
                                        <?php endif;?>
                                    </td>
                                    <td>
                                        <?php if($hasSovrapprezzo):?>
                                            <span class="badge badge-sovrapprezzo">+<?php echo number_format($f['sovrapprezzo'],0);?>%</span>
                                        <?php else:?>
                                            -
                                        <?php endif;?>
                                    </td>
                                    <td><?php echo date('d/m/Y',strtotime($f['data_emissione']));?></td>
                                    <td>
                                        <?php echo date('d/m/Y',strtotime($f['data_scadenza']));?>
                                        <?php if($isScaduta):?>
                                            <br><span class="badge badge-scaduta">SCADUTA</span>
                                        <?php endif;?>
                                    </td>
                                    <td>
                                        <?php if($isPagata):?>
                                            <span class="badge badge-pagata">PAGATA</span><br>
                                            <small style="color:var(--color-text-muted);"><?php echo date('d/m/Y',strtotime($f['data_pagamento']));?></small>
                                        <?php else:?>
                                            <span class="badge badge-non-pagata">NON PAGATA</span>
                                        <?php endif;?>
                                    </td>
                                    <td>
                                        <?php if(!$isPagata):?>
                                            <a href="cliente-paga-fattura.php?id=<?php echo $f['IdFattura'];?>" class="btn-paga">Paga Ora</a>
                                        <?php else:?>
                                            -
                                        <?php endif;?>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
                <?php endif;?>
            </div>
        </div>
    </main>
</body>
</html>
