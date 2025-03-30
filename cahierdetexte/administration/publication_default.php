<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$updateSQL = sprintf("UPDATE cdt_prof SET publier_cdt=%s, publier_travail=%s WHERE droits=2",
		GetSQLValueString(isset($_POST['publier_cdt']) ? "true" : "", "defined","'O'","'N'"),
		GetSQLValueString(isset($_POST['publier_travail']) ? "true" : "", "defined","'O'","'N'"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	
	
	$updateGoTo = "index.php";
	if (isset($_SERVER['QUERY_STRING'])) {
		$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
		$updateGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $updateGoTo));
}


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
$header_description="Visibilit&eacute; par d&eacute;faut du cahier de textes";
require_once "../templates/default/header.php";
?>
<HR>

<p align="center">&nbsp;</p>
<p align="center">Pour l'ensemble des enseignants, le cahier de textes est visible par d&eacute;faut. <br>
Chaque enseignant peut revenir sur ce choix individuellement dans son menu Enseignant.
<form name="form1" method="POST" action="publication_default.php">
Visibilit&eacute; <strong>en ligne</strong> du cahier de textes
<input type="checkbox" name="publier_cdt" value=""  checked=checked>
</p>
<p align="center"><br>
Visibilit&eacute; <strong>en ligne</strong> du travail &agrave; faire
<input type="checkbox" name="publier_travail" value=""  checked=checked>


<input type="hidden" name="MM_update" value="form1">
</p>
<p align="center"><br>
<input type="submit" name="Submit" value="Modifier">
</form>
</p>
<p align="center">&nbsp;</p>
<p align="center"><a href="index.php">Retour au Menu Administrateur </a></p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
