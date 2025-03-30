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
	$nom_matiere= str_replace(array("/", "&", "\'"), "-",GetSQLValueString($_POST['nom_matiere'], "text") );
	$updateSQL = sprintf("UPDATE cdt_matiere SET nom_matiere=%s WHERE ID_matiere=%s",
		$nom_matiere,
		GetSQLValueString($_POST['ID_matiere'], "int"));
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$updateGoTo = "matiere_ajout.php";
	if (isset($_SERVER['QUERY_STRING'])) {
		$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
		$updateGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $updateGoTo));
}

$IDmat_RsModifMat = "0";
if (isset($_GET['ID_matiere'])) {
	$IDmat_RsModifMat = (get_magic_quotes_gpc()) ? $_GET['ID_matiere'] : addslashes($_GET['ID_matiere']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifMat = sprintf("SELECT * FROM cdt_matiere WHERE cdt_matiere.ID_matiere=%s", $IDmat_RsModifMat);
$RsModifMat = mysqli_query($conn_cahier_de_texte, $query_RsModifMat) or die(mysqli_error($conn_cahier_de_texte));
$row_RsModifMat = mysqli_fetch_assoc($RsModifMat);
$totalRows_RsModifMat = mysqli_num_rows($RsModifMat);
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
$header_description="Gestion des mati&egrave;res";
require_once "../templates/default/header.php";
?>


<form method="post" name="form1" action="<?php echo $editFormAction; ?>">
<p>&nbsp;</p>
<p align="center"><strong>Modification du nom de la mati&egrave;re </strong></p>
<table align="center" class="Style5">
<tr valign="baseline">
<td height="36"><br>
<div align="center">
<input type="text" name="nom_matiere" value="<?php echo $row_RsModifMat['nom_matiere']; ?>" size="32">
<br>
</div></td></tr>
<tr valign="middle">
<td height="34"><div align="center">
<input type="submit" value="Enregistrer le nouveau nom de la mati&egrave;re">
</div></td>
</tr>
</table>
<input type="hidden" name="MM_update" value="form1">
<input type="hidden" name="ID_matiere" value="<?php echo $row_RsModifMat['ID_matiere']; ?>">
</form>
<p>&nbsp;</p>
<p align="center"><a href="matiere_ajout.php">Annuler</a></p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsModifMat);
?>
