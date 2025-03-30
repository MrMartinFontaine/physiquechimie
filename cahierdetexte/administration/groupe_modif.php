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
	$groupe= str_replace(array("/", "&", "\'"), "-",GetSQLValueString($_POST['groupe'], "text") );
	$groupe = strtr($groupe,'@ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',
'aAAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
	$code_groupe=$groupe; //par defaut
	$updateSQL = sprintf("UPDATE cdt_groupe SET groupe=%s,code_groupe=%s WHERE ID_groupe=%u",
		$groupe,$code_groupe,
		GetSQLValueString($_POST['ID_groupe'], "int"));
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
	header("Location:groupe_ajout.php");
}

$IDmat_RsModifMat = "0";
if (isset($_GET['ID_groupe'])) {
	$IDmat_RsModifMat = (get_magic_quotes_gpc()) ? $_GET['ID_groupe'] : addslashes($_GET['ID_groupe']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifMat = sprintf("SELECT * FROM cdt_groupe WHERE cdt_groupe.ID_groupe=%u", $IDmat_RsModifMat);
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
$header_description="Modification du nom d'un groupe";
require_once "../templates/default/header.php";
?><p>&nbsp;</p>
<form method="post" name="form1" action="<?php echo $editFormAction; ?>">
<table align="center" class="Style5">
<tr valign="baseline">
<td height="36"><div align="center">
<p>&nbsp;</p>
<input type="text" name="groupe" value="<?php echo $row_RsModifMat['groupe']; ?>" size="32">
<br />
</div></td>
</tr>
<tr valign="middle">
<td height="34"><div align="center">
<input type="submit" value="Enregistrer le nouveau nom du groupe">
</div></td>
</tr>
</table>
<input type="hidden" name="MM_update" value="form1">
<input type="hidden" name="ID_groupe" value="<?php echo $row_RsModifMat['ID_groupe']; ?>">
</form>
<p>&nbsp;</p>
<p align="center"><a href="groupe_ajout.php">Annuler</a></p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsModifMat);
?>
