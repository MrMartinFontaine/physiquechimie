<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 

require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") &&($_POST['activite']<>'')) {
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$querySQL = sprintf("SELECT activite FROM cdt_type_activite WHERE ID_activite=%u",
		GetSQLValueString($_POST['ID_activite'], "int")
		);
    $Rsactiv = mysqli_query($conn_cahier_de_texte, $querySQL) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsactiv = mysqli_fetch_assoc($Rsactiv);
    mysqli_free_result($Rsactiv);
	
	$updateSQL1 = sprintf("UPDATE cdt_type_activite SET activite=%s, pos_typ=%u, couleur_activite=%s WHERE ID_activite=%u AND ID_prof=%u",
		GetSQLValueString($_POST['activite'], "text"),
		GetSQLValueString($_POST['pos_typ'], "int"),
		GetSQLValueString($_POST['couleur2police'], "text"),
		GetSQLValueString($_POST['ID_activite'], "int"),
		GetSQLValueString($_SESSION['ID_prof'], "int")
		);
	
	$updateSQL2 = sprintf("UPDATE cdt_agenda SET type_activ=%s, couleur_activ=%s WHERE type_activ=%s AND prof_ID=%u",
		GetSQLValueString($_POST['activite'], "text"),
		GetSQLValueString($_POST['couleur2police'], "text"),
		GetSQLValueString($row_Rsactiv['activite'], "text"),
		GetSQLValueString($_SESSION['ID_prof'], "int")
		);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL1) or die(mysqli_error($conn_cahier_de_texte));
	$Result2 = mysqli_query($conn_cahier_de_texte, $updateSQL2) or die(mysqli_error($conn_cahier_de_texte));
	
	$updateGoTo = "type_activite_ajout.php";
	if (isset($_SERVER['QUERY_STRING'])) {
		$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
		$updateGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $updateGoTo));
}

$choix_prof_RsActivite = "0";
if (isset($_SESSION['ID_prof'])) {
	$choix_prof_RsActivite = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
}

$ID_RsModifActivite = "0";
if (isset($_GET['ID_activite'])) {
	$ID_RsModifActivite = (get_magic_quotes_gpc()) ? $_GET['ID_activite'] : addslashes($_GET['ID_activite']);
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifActivite = sprintf("SELECT * FROM cdt_type_activite WHERE ID_activite=%u AND ID_prof=%u", $ID_RsModifActivite,$choix_prof_RsActivite);
$RsModifActivite = mysqli_query($conn_cahier_de_texte, $query_RsModifActivite) or die(mysqli_error($conn_cahier_de_texte));
$row_RsModifActivite = mysqli_fetch_assoc($RsModifActivite);
$totalRows_RsModifActivite = mysqli_num_rows($RsModifActivite);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<link media="screen" rel="stylesheet" href="../styles/colorbox.css" />
<link rel="stylesheet" type="text/css" href="../styles/colorpicker.css" />
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Modification du libell&eacute; d'un type d'activit&eacute;";
require_once "../templates/default/header.php";
?>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery.colorbox.js"></script>
<script type="text/javascript" src="../jscripts/CP_Class.js"></script>
<script type="text/JavaScript">
window.onload = function()
{
	fctLoad();
}
window.onscroll = function()
{
	fctShow();
}
window.onresize = function()
{
	fctShow();
}
</script>
<HR>
<p>&nbsp;</p>
<form method="post" name="form1" action="<?php echo $editFormAction; ?>">
<table width="400" height="92" align="center" class="bordure">
<tr valign="baseline">
<td valign="middle"  bgcolor="#EBEBEB"><div align="center">
<input type="text" name="activite" id="activite" style="background-color:#BBCEDE;color:<?php echo $row_RsModifActivite['couleur_activite']; ?>;text-align:center;font-weight: bold;" value="<?php echo $row_RsModifActivite['activite']; ?>" size="32">
<input type="hidden" size="10" name="couleur2police" value="<?php echo $row_RsModifActivite['couleur_activite']; ?>" maxlength="7" style="font-family:Tahoma;font-size:x-small">
<img src="../images/color.gif" width="21" height="20" border="0" align="absmiddle" onClick="fctShow(document.form1.couleur2police);" style="cursor:pointer"> </div></td>
</div></td>
</tr>
<tr valign="baseline">
<td bgcolor="#EBEBEB"><div align="center">
<input name="submit" type="submit" value="Mettre &agrave; jour le nom et/ou la couleur de l'activit&eacute;">
</div></td>
</tr>
</table>
<p>
<input type="hidden" name="MM_update" value="form1">
<input type="hidden" name="ID_activite" value="<?php echo $row_RsModifActivite['ID_activite']; ?>">
<input type="hidden" name="pos_typ" value="<?php echo $row_RsModifActivite['pos_typ']; ?>">
</p>
</form>
<p align="center"><a href="type_activite_ajout.php">Annuler</a><a href="../index.php"></a></p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsModifActivite);
?>
