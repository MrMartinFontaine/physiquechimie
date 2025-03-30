<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

if(isset($_POST['choice1']))
	{
	$choice = GetSQLValueString($_POST['choice1'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice WHERE `param_nom`='pp_diffusion';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	}

if(isset($_POST['choice2']))
	{
	$choice = GetSQLValueString($_POST['choice2'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice WHERE `param_nom`='pp_multiclass';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	}

if(isset($_POST['choice3']))
	{
	$choice = GetSQLValueString($_POST['choice3'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice WHERE `param_nom`='pp_groupe';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	}

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='pp_diffusion' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access1 = $row[0];
mysqli_free_result($result_read);

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='pp_multiclass' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access2 = $row[0];
mysqli_free_result($result_read);

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='pp_groupe' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access3 = $row[0];
mysqli_free_result($result_read);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Professeurs principaux";
require_once "../templates/default/header.php";
?>

  <blockquote>
    <blockquote>
      <blockquote>
	<fieldset style="width : 100%">
        <legend style="color:red;">Autorisation actuelle de publier des messages  : <b><?php echo $access1; ?></b>&nbsp;</legend>
        <p align="left">Les professeurs principaux ont la possibilit&eacute; de publier des messages aux coll&egrave;gues intervenant dans la classe dont ils sont responsables.</p>
        <p align="left">Ces informations apparaissent dans le premier &eacute;cran de remplissage du cahier de textes. </p>
        <p><form method="post">
<?php 
if($access1=="Oui") echo "<input type=\"hidden\" name=\"choice1\" value=\"Non\"/><input type=\"submit\" value=\"Interdire de publier \"/>";
else echo "<input type=\"hidden\" name=\"choice1\" value=\"Oui\"/><input type=\"submit\" value=\"Autoriser &agrave; publier \"/>";
?>       
        </form></p>
        </fieldset>
        <p align="left">&nbsp;</p>
        <fieldset style="width : 100%">
        <legend style="color:red;">Autorisation actuelle d'&ecirc;tre professeur principal sur plusieurs classes  : <b><?php echo $access2; ?></b>&nbsp;</legend>
        <p align="left">Il est possible de permettre aux professeurs d'&ecirc;tre professeur principal de plusieurs classes.</p>
        <p><form method="post">
<?php 
if($access2=="Oui") echo "<input type=\"hidden\" name=\"choice2\" value=\"Non\"/><input type=\"submit\" value=\"N'autoriser &agrave; &ecirc;tre professeur principal que sur une seule classe \"/>";
else echo "<input type=\"hidden\" name=\"choice2\" value=\"Oui\"/><input type=\"submit\" value=\"Autoriser &agrave; &ecirc;tre professeur principal sur plusieurs classes \"/>";
?>       
        </form></p>
        </fieldset>
        <p align="left"></p>
        <fieldset style="width : 100%">
        <legend style="color:red;">Autorisation actuelle d'&ecirc;tre professeur principal sur un groupe d'une classe  : <b><?php echo $access3; ?></b>&nbsp;</legend>
        <p align="left">Il est possible de permettre aux professeurs d'&ecirc;tre professeur principal uniquement d'un groupe d'une classe s'ils le souhaitent et non de la classe enti&egrave;re.</p>
        <p><form method="post">
<?php 
if($access3=="Oui") echo "<input type=\"hidden\" name=\"choice3\" value=\"Non\"/><input type=\"submit\" value=\"N'autoriser &agrave; &ecirc;tre professeur principal que sur des classes enti&egrave;res\"/>";
else echo "<input type=\"hidden\" name=\"choice3\" value=\"Oui\"/><input type=\"submit\" value=\"Autoriser &agrave; &ecirc;tre professeur principal sur un groupe d'une classe \"/>";
?>       
        </form></p>
	</fieldset>
        <p align="left">&nbsp;</p>
      </blockquote>
    </blockquote>
  </blockquote>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
