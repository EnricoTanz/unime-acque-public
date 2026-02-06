<?php
define('UNIME_ACQUE', true);
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
startSecureSession();
requireRole('CLIENTE');
if(!isPostRequest()){header('Location:cliente-carte.php');exit;}
if(!verifyCsrfToken($_POST['csrf_token']??'')){setFlashMessage('danger','Richiesta non valida.');header('Location:cliente-carte.php');exit;}
$userId=getCurrentUserId();
$numeroCarta=trim($_POST['numero_carta']??'');
$cvv=trim($_POST['cvv']??'');
$dataScadenzaInput=trim($_POST['data_scadenza']??'');
$errors=[];
if(!preg_match('/^[0-9]{16}$/',$numeroCarta)){$errors[]='Numero carta non valido (16 cifre).';}
if(!preg_match('/^[0-9]{3}$/',$cvv)){$errors[]='CVV non valido (3 cifre).';}
if(empty($dataScadenzaInput)){$errors[]='Data scadenza obbligatoria.';}else{
    $dataScadenza=date('Y-m-d',strtotime($dataScadenzaInput.'-01'));
    $ultimoGiorno=date('Y-m-t',strtotime($dataScadenza));
    if($ultimoGiorno<=date('Y-m-d')){$errors[]='La carta deve avere una data di scadenza futura.';}
}
if(!empty($errors)){setFlashMessage('danger',implode(' ',$errors));header('Location:cliente-carte.php');exit;}
$conn=getDbConnection();
if($conn===null){setFlashMessage('danger','Errore di connessione.');header('Location:cliente-carte.php');exit;}
$queryCheck="SELECT COUNT(*) as count FROM CARTA_DI_CREDITO WHERE IdUtente=? AND numero_carta=?";
$stmtCheck=mysqli_prepare($conn,$queryCheck);
mysqli_stmt_bind_param($stmtCheck,'is',$userId,$numeroCarta);
mysqli_stmt_execute($stmtCheck);
$resultCheck=mysqli_stmt_get_result($stmtCheck);
$row=mysqli_fetch_assoc($resultCheck);
mysqli_stmt_close($stmtCheck);
if($row['count']>0){setFlashMessage('danger','Questa carta è già registrata.');header('Location:cliente-carte.php');exit;}
$query="INSERT INTO CARTA_DI_CREDITO(IdUtente,numero_carta,cvv,data_scadenza)VALUES(?,?,?,?)";
$stmt=mysqli_prepare($conn,$query);
mysqli_stmt_bind_param($stmt,'isss',$userId,$numeroCarta,$cvv,$ultimoGiorno);
if(mysqli_stmt_execute($stmt)){
    $idCarta=mysqli_insert_id($conn);
    logError("CLIENTE (ID:$userId) ha registrato carta #$idCarta",'INFO');
    setFlashMessage('success','Carta registrata con successo!');
}else{
    setFlashMessage('danger','Errore durante la registrazione.');
}
mysqli_stmt_close($stmt);
header('Location:cliente-carte.php');
exit;
?>
