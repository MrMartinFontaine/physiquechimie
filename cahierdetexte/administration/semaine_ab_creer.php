<?php include "../authentification/authcheck.php"; ?>
<?php if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 
require_once('../inc/functions_inc.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<?php
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_debut_annee' LIMIT 1";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$date_debut_annee = $row[0];
mysqli_free_result($result_read);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_fin_annee' LIMIT 1";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$date_fin_annee = $row[0];
mysqli_free_result($result_read);
?>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Programmation des semaines en alternance";
require_once "../templates/default/header.php";
?>
<HR>
<p align="center">&nbsp;</p>
<p align="center">&nbsp;</p>
<?php 
if (($date_debut_annee=='')|| ($date_fin_annee=='')){
	?>
	<blockquote>
	<fieldset style="width : 98%">
	<p>Veuillez pr&eacute;alablement d&eacute;finir les dates de d&eacute;but et de fin de l'ann&eacute;e scolaire <a href="dates_annee_scol_param.php">ici</a>.</p>
	<p>&nbsp;</p>
	</fieldset>
	</blockquote>
	<?php 
} 
else 
{ 
	$date1=substr($date_debut_annee,6,4).'/'.substr($date_debut_annee,3,2).'/'.substr($date_debut_annee,0,2);
	$date2=substr($date_fin_annee,6,4).'/'.substr($date_fin_annee,3,2).'/'.substr($date_fin_annee,0,2);
	$num_sem1=date('W',strtotime($date1));
	$num_sem2=date('W',strtotime($date2));
	if (substr($date_debut_annee,6,4)==substr($date_fin_annee,6,4)){$nbsem=$num_sem2-$num_sem1+1;} else {$nbsem=53-$num_sem1+$num_sem2;};
	
	?>
	<form id="form1" name="form1" onLoad= "formfocus()"  method="POST" action="semaine_ab_creer2.php">
	<p>&nbsp;</p>
	<p>Vous allez g&eacute;n&eacute;rer une programmation des semaines en alternances pour l'ann&eacute;e scolaire </p>
	<p>d&eacute;butant le <strong><?php echo $date_debut_annee . ' (Semaine '. $num_sem1.')';
	?></strong> et se terminant le <strong><?php echo $date_fin_annee;?></strong></p>
	<p>&nbsp;</p>
	<p>Si ces dates sont incorrectes, veuillez les <a href="dates_annee_scol_param.php">red&eacute;finir ici</a>. </p>
	<p>&nbsp; </p>
	<input type="hidden" name="annee_rentree" type="text" id="annee_rentree" value="<?php echo substr($date_debut_annee,6,4);?>" />
	<input type="hidden" name="annee_sortie" type="text" id="annee_sortie" value="<?php echo substr($date_fin_annee,6,4);?>" />
	<input type="hidden" name="sem_rentree" type="text" id="sem_rentree" value="<?php echo $num_sem1;?>" />
	<input type="hidden" name="sem_sortie" type="text" id="sem_sortie" value="<?php echo $num_sem2;?>" />
	<input type="hidden" name="nbsem" type="text" id="nbsem" value="<?php echo $nbsem;?>" />
	<input type="submit" name="Submit" value="Continuer" />
	<input type="hidden" name="MM_insert" value="form1">
	<p>&nbsp;</p>
	</form>
	<?php 
}; ?>
<p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
<p align="center">&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
