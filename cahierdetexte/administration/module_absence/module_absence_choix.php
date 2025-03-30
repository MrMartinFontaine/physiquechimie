<?php
include "../../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte)or die(mysqli_error($conn_cahier_de_texte));
if(isset($_POST['choice']))
	{
	$choice = GetSQLValueString($_POST['choice'], "int");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice WHERE `param_nom`='choix_module_absence';";
	$result_write = mysqli_query($conn_cahier_de_texte,$query_write);
	$_SESSION['choix_module_absence']=$_POST['choice'];
	}

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='choix_module_absence' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte,$query_read);
$row = mysqli_fetch_row($result_read);
$access = $row[0];

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Choix du mod&egrave;le de déclaration d'absence - feuille d'appel et de suivi";
require_once "../../templates/default/header.php";
?>

  <blockquote>
    <blockquote>
      <blockquote>
        <p style="color:red;">Etat actuel - Choix du mod&egrave;le : <?php
		if ($access==1){echo 'Module simplifi&eacute';} else {echo 'Module &eacute;labor&eacute;';};
		 ?></p>
        <fieldset style="width : 100%">
        <p align="left"><img src="../../images/lightbulb.png" width="16" height="16"><strong>Choix du mod&egrave;le de déclaration d'absence - feuille d'appel et de suivi</strong></p>
        <p align="left">Il existe deux mod&egrave;les :</p>
        <p align="left"><strong>Le mod&egrave;le simplifi&eacute;</strong> repose uniquement sur la d&eacute;claration d'absence, enregistrement  des retards, oubli de carnet et mat&eacute;riel. C'&eacute;tait le mod&egrave;le par d&eacute;faut propos&eacute; jusqu'&agrave; la version 4948.</p>
        <p align="left">L'autre mod&egrave;le  que nous appellerons <strong>module &eacute;labor&eacute;</strong> est beaucoup plus pertinent pour g&eacute;rer  les interactions entre vie scolaire et la classe, la gestion des incidents de cours, sanctions, carnets en coll&egrave;ge. <br>
        </p>
        <p align="left">&nbsp;</p>
        <form method="post">
<?php 
if($access=="2") echo "<input type=\"hidden\" name=\"choice\" value=\"1\"/><input type=\"submit\" value=\"S&eacute;lectionner le Module simplifi&eacute; \"/>";
else echo "<input type=\"hidden\" name=\"choice\" value=\"2\"/><input type=\"submit\" value=\"S&eacute;lectionner le Module &eacute;labor&eacute; \"/>";
?> 		
       
        </form>
        </p>
	</fieldset>
        <p align="left">&nbsp;</p>
      </blockquote>
    </blockquote>
  </blockquote>
  <p align="center"><a href="module_absence.php">Retour au Menu Gestion du module Absence</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>

