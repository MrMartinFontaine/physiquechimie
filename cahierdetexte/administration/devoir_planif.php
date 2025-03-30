<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

if(isset($_POST['choice']))
	{
	$choice = GetSQLValueString($_POST['choice'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice WHERE `param_nom`='devoir_planif';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	}

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='devoir_planif' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access = $row[0];
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
$header_description="D&eacute;sactiver la possibilit&eacute; de planifier des devoirs en dehors des heures de cours";
require_once "../templates/default/header.php";
?>

  <blockquote>
    <blockquote>
      <blockquote>
        <p style="color:red;">Possibilit&eacute; de planifier des devoirs en dehors des heures de cours  : <?php echo $access; ?></p>
	<fieldset style="width : 100%">
        <p align="left">Les devoirs planifi&eacute;s hors des heures de cours n'ont pas usage dans tous les &eacute;tablissements scolaires. Si tel est le cas dans votre &eacute;tablissement, vous pouvez d&eacute;sactiver cette fonction afin de simplifier l'interface de saisie des enseignants.</p>
        <p align="left">&nbsp;</p>
        <p><form method="post">
<?php 
if($access=="Oui") echo "<input type=\"hidden\" name=\"choice\" value=\"Non\"/><input type=\"submit\" value=\"D&eacute;sactiver la possibilit&eacute; de planifier des devoirs en dehors des heures de cours\"/>";
else echo "<input type=\"hidden\" name=\"choice\" value=\"Oui\"/><input type=\"submit\" value=\"Activer la possibilit&eacute; de planifier des devoirs en dehors des heures de cours\"/>";
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
