<?php
define('UNIME_ACQUE', true);
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
startSecureSession();
requireRole('CLIENTE');
if(!isPostRequest()){header('Location:cliente-fatture.php');exit;}
if(!verifyCsrfToken($_POST['csrf_token']??'')){setFlashMessage('danger','Richiesta non valida.');header('Location:cliente-fatture.php');exit;}
$userId=getCurrentUserId();
$userName=getCurrentUserName();
$idFattura=isset($_POST['id_fattura'])?(int)$_POST['id_fattura']:0;
$idCarta=isset($_POST['id_carta'])?(int)$_POST['id_carta']:0;
$errors=[];
if($idFattura<=0){$errors[]='Fattura non valida.';}
if($idCarta<=0){$errors[]='Carta non valida.';}
if(!empty($errors)){setFlashMessage('danger',implode(' ',$errors));header('Location:cliente-fatture.php');exit;}
$conn=getDbConnection();
if($conn===null){setFlashMessage('danger','Errore di connessione.');header('Location:cliente-fatture.php');exit;}
$queryVerify="SELECT f.IdFattura,f.data_pagamento,c.IdUtente FROM FATTURA f INNER JOIN CONTRATTO c ON f.IdContratto=c.IdContratto WHERE f.IdFattura=? LIMIT 1";
$stmtV=mysqli_prepare($conn,$queryVerify);
mysqli_stmt_bind_param($stmtV,'i',$idFattura);
mysqli_stmt_execute($stmtV);
$resultV=mysqli_stmt_get_result($stmtV);
if(mysqli_num_rows($resultV)===0){setFlashMessage('danger','Fattura non trovata.');header('Location:cliente-fatture.php');exit;}
$fattura=mysqli_fetch_assoc($resultV);
mysqli_stmt_close($stmtV);
if($fattura['IdUtente']!=$userId){setFlashMessage('danger','Non autorizzato.');header('Location:cliente-fatture.php');exit;}
if($fattura['data_pagamento']){setFlashMessage('warning','Fattura già pagata.');header('Location:cliente-fatture.php');exit;}
$queryCarta="SELECT * FROM CARTA_DI_CREDITO WHERE IdCarta=? AND IdUtente=? AND data_scadenza>=CURRENT_DATE";
$stmtC=mysqli_prepare($conn,$queryCarta);
mysqli_stmt_bind_param($stmtC,'ii',$idCarta,$userId);
mysqli_stmt_execute($stmtC);
$resultC=mysqli_stmt_get_result($stmtC);
if(mysqli_num_rows($resultC)===0){setFlashMessage('danger','Carta non valida o scaduta.');header('Location:cliente-fatture.php');exit;}
mysqli_stmt_close($stmtC);
$query="CALL registra_pagamento_fattura(?,?)";
$stmt=mysqli_prepare($conn,$query);
if($stmt===false){setFlashMessage('danger','Errore preparazione query.');header('Location:cliente-fatture.php');exit;}
mysqli_stmt_bind_param($stmt,'ii',$idFattura,$idCarta);
if(mysqli_stmt_execute($stmt)){
    logError("CLIENTE $userName (ID:$userId) ha pagato fattura #$idFattura con carta #$idCarta",'INFO');
    setFlashMessage('success',"Pagamento effettuato con successo! Fattura #$idFattura pagata.");
    header('Location:cliente-fatture.php');
}else{
    $error=mysqli_stmt_error($stmt);
    if(strpos($error,'già pagata')!==false){
        setFlashMessage('warning','Fattura già pagata.');
    }elseif(strpos($error,'scaduta')!==false){
        setFlashMessage('danger','Carta di credito scaduta.');
    }else{
        setFlashMessage('danger','Errore durante il pagamento: '.$error);
    }
    header('Location:cliente-paga-fattura.php?id='.$idFattura);
}
mysqli_stmt_close($stmt);
exit;
?>
