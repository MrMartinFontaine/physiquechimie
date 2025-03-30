<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

if(isset($_POST['choice']))
	{
	$choice = GetSQLValueString($_POST['choice'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice WHERE `param_nom`='libelle_semaine';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	}

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='libelle_semaine' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
if ($row[0]==1){$libelle = 'Semaine Paire et Impaire';} else {$libelle = 'Semaine A et B';};
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
$header_description="Libell&eacute; de vos semaines en alternance";
require_once "../templates/default/header.php";
?>

  <blockquote>
    <blockquote>
      <blockquote>
        <p>&nbsp;</p>
        <p style="color:red;">Libell&eacute; actuel  de vos semaines en alternance :</p>
        <p style="color:red;"><strong> <?php echo $libelle; ?></strong></p>
        <p align="left">&nbsp;</p>
        <p align="left">&nbsp;</p>
        <form method="post">
<?php 
if($row[0]=="0") echo "<input type=\"hidden\" name=\"choice\" value=\"1\"/><input type=\"submit\" value=\"Modifier en Semaine Paire et Impaire \"/>";
else echo "<input type=\"hidden\" name=\"choice\" value=\"0\"/><input type=\"submit\" value=\"Modifier en Semaine A et B \"/>";
?>       
        </form></p>
        <p align="left">&nbsp;</p>
      </blockquote>
    </blockquote>
  </blockquote>
  <p align="center"><a href="semaine_ab_menu.php">Retour &agrave; la gestion de mes semaines en alternance </a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
