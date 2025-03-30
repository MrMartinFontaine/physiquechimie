<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

if(isset($_POST['choice']))
	{
	$choice = GetSQLValueString($_POST['choice'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice WHERE `param_nom`='menu_deroul';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	}

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='menu_deroul' LIMIT 1;";
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
$header_description="Signifier le mode d'authentification des personnels sur la page d'authentification";
require_once "../templates/default/header.php";
?>

  <blockquote>
    <blockquote>
      <blockquote>
        <p style="color:red;">Mode d'authentification des personnels  : <?php if($access=="Oui") {echo "Par menu d&eacute;roulant";} else {echo "Par zone de texte";}; ?></p>
	<fieldset style="width : 100%">
        <p align="left">Par d&eacute;faut, les membres des personnels se connectent en choisissant leur nom dans une liste (un menu d&eacute;roulant). </p>
        <p align="left">Dans certains  &eacute;tablissements cependant, la liste du personnel peut &ecirc;tre longue. Le choix dans une telle liste devient alors mal ais&eacute;.</p>
        <p align="left">Dans ce cas de figure, il vous est possible de remplacer ce menu d&eacute;roulant par une zone de saisie o&ugrave; chaque membre du personnel devra cette fois saisir son identifiant. </p>
        <p align="left">Cela permet &eacute;galement de renforcer la s&eacute;curit&eacute; &agrave; l'authentification et garantir une certaine confidentialit&eacute;. </p>
        <form method="post">
<?php 
if($access=="Oui") echo "<input type=\"hidden\" name=\"choice\" value=\"Non\"/><input type=\"submit\" value=\"Choisir une zone de saisie comme mode d'authentification\"/>";
else echo "<input type=\"hidden\" name=\"choice\" value=\"Oui\"/><input type=\"submit\" value=\"Choisir une liste d&eacute;roulante comme mode d'authentification\"/>";
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
