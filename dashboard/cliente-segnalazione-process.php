<?php
define('UNIME_ACQUE', true);
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
startSecureSession();
requireRole('CLIENTE');
if(!isPostRequest()){header('Location: cliente-segnalazioni.php');exit;}
if(!verifyCsrfToken($_POST['csrf_token']??'')){setFlashMessage('danger','Richiesta non valida.');header('Location:cliente-segnalazioni.php');exit;}
$userId=getCurrentUserId();
$userName=getCurrentUserName();
$tipo=trim($_POST['tipo']??'');
$descrizione=trim($_POST['descrizione']??'');
$errors=[];
if(!in_array($tipo,['TECNICA','COMMERCIALE'])){$errors[]='Tipo segnalazione non valido.';}
if(empty($descrizione)){$errors[]='La descrizione è obbligatoria.';}elseif(strlen($descrizione)>1000){$errors[]='La descrizione è troppo lunga (max 1000 caratteri).';}
if(!empty($errors)){setFlashMessage('danger',implode(' ',$errors));header('Location:cliente-segnalazione-nuova.php');exit;}
$conn=getDbConnection();
if($conn===null){setFlashMessage('danger','Errore di connessione al database.');header('Location:cliente-segnalazione-nuova.php');exit;}
$query="INSERT INTO SEGNALAZIONE(IdUtente_segnalante,IdUtente_presa_in_carico,motivo_richiesta,contenuto_richiesta,data_apertura,data_chiusura)VALUES(?,NULL,?,?,CURRENT_DATE,NULL)";
$stmt=mysqli_prepare($conn,$query);
if($stmt===false){setFlashMessage('danger','Errore preparazione query.');header('Location:cliente-segnalazione-nuova.php');exit;}
mysqli_stmt_bind_param($stmt,'iss',$userId,$tipo,$descrizione);
if(mysqli_stmt_execute($stmt)){
    $idSegnalazione=mysqli_insert_id($conn);
    logError("CLIENTE $userName (ID: $userId) ha aperto segnalazione #$idSegnalazione di tipo $tipo",'INFO');
    setFlashMessage('success',"Segnalazione #$idSegnalazione aperta con successo! Verrai contattato al più presto.");
    header('Location:cliente-segnalazioni.php');
}else{
    setFlashMessage('danger','Errore durante l\'apertura della segnalazione: '.mysqli_stmt_error($stmt));
    header('Location:cliente-segnalazione-nuova.php');
}
mysqli_stmt_close($stmt);
exit;
?>
