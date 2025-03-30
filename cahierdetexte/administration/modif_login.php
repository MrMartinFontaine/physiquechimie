<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

if(isset($_POST['choice']))
	{
	$choice = GetSQLValueString($_POST['choice'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice WHERE `param_nom`='modif_login';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	}

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='modif_login' LIMIT 1;";
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
$header_description="Autoriser les enseignants &agrave; modifier leur identifiant";
require_once "../templates/default/header.php";
?>

  <blockquote>
    <blockquote>
      <blockquote>
        <p style="color:red;">Etat actuel &gt; Modification par les enseignants de leur identifiant  :  <?php echo $access; ?></p>
        <p align="left">&nbsp;</p>
        <p align="left">Vous pouvez autoriser ou interdire aux utilisateurs la modification de leur identifiant (login). Il est &agrave; noter qu'ils conserveront la possibilit&eacute; de modifier le libell&eacute; de leur identit&eacute; (nom long) dans leur menu Enseignant &gt; Mes param&egrave;tres.</p>
        <p align="left">&nbsp;</p>
        <form method="post">
<?php 
if($access=="Oui") echo "<input type=\"hidden\" name=\"choice\" value=\"Non\"/><input type=\"submit\" value=\"Interdire la modification \"/>";
else echo "<input type=\"hidden\" name=\"choice\" value=\"Oui\"/><input type=\"submit\" value=\"Autoriser la modification \"/>";
?>       
        </form></p>
        <p align="left">&nbsp;</p>
      </blockquote>
    </blockquote>
  </blockquote>
  <p align="center"><a href="index.php">Retour au Menu Administrateur  (Saisie des Enseignants, mati&egrave;res, classes... )</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>

