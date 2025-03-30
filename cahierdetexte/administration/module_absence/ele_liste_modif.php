<?php 
include "../../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>3)) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$nom_ele= str_replace(array("/", "&", "\'"), "-",GetSQLValueString($_POST['nom_ele'], "text") );
	$prenom_ele= str_replace(array("/", "&", "\'"), "-",GetSQLValueString($_POST['prenom_ele'], "text") );
	$updateSQL = sprintf("UPDATE ele_liste SET nom_ele=%s,prenom_ele =%s WHERE ID_ele=%s",
		$nom_ele,$prenom_ele,GetSQLValueString($_POST['ID_classe'], "int"));
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$updateGoTo = "ele_liste_affiche.php?code_classe=".$_POST['code_classe'];
	if (isset($_SERVER['QUERY_STRING'])) {
		$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
		$updateGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $updateGoTo));
}

$IDele_RsModifeleve = "0";
if (isset($_GET['ID_ele'])) {
	$IDele_RsModifeleve = (get_magic_quotes_gpc()) ? $_GET['ID_ele'] : addslashes($_GET['ID_ele']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifeleve = sprintf("SELECT * FROM ele_liste WHERE ele_liste.ID_ele=%s", $IDele_RsModifeleve);
$RsModifeleve = mysqli_query($conn_cahier_de_texte, $query_RsModifeleve) or die(mysqli_error($conn_cahier_de_texte));
$row_RsModifeleve = mysqli_fetch_assoc($RsModifeleve);


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
$header_description="Modification du nom d'un &eacute;l&egrave;ve";
require_once "../../templates/default/header.php";
?>


<HR> 
<p align="center">&nbsp;</p>
<form method="post" name="form1" action="<?php echo $editFormAction; ?>">
<p align="center">&nbsp;</p>
<p align="center"><strong>Modifier le nom de l'&eacute;l&egrave;ve </strong></p>
<table align="center" class="Style5">
<tr valign="baseline">
<td class="Style55"><div align="center">Nom  
<input type="text" name="nom_ele" value="<?php echo $row_RsModifeleve['nom_ele']; ?>" size="32">
Pr&eacute;nom  
<input type="text" name="prenom_ele" value="<?php echo $row_RsModifeleve['prenom_ele']; ?>"size="32">
</p>
</div></td>
</tr>
<tr valign="baseline">
<td class="Style55"><div align="center">
<p>
<input type="submit" value="Enregistrer les modifications">
</p>
</div></td>
</tr>
</table>
<input type="hidden" name="MM_update" value="form1">
<input type="hidden" name="ID_classe" value="<?php echo $row_RsModifeleve['ID_ele']; ?>">

</form>  <p align="center">&nbsp;</p>
<p align="center"><a href="ele_liste_affiche.php">Annuler</a></p>  
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsModifeleve);
?>
