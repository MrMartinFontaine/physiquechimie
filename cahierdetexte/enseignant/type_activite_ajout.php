<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMax = "SELECT MAX(pos_typ) AS resultat FROM cdt_type_activite ";
$RsMax = mysqli_query($conn_cahier_de_texte, $query_RsMax) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMax = mysqli_fetch_assoc($RsMax);
$position=$row_RsMax['resultat']+1;

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") &&($_POST['activite']<>'')) {
	$insertSQL = sprintf("INSERT INTO cdt_type_activite (ID_prof,activite,couleur_activite,pos_typ) VALUES (%u,%s,%s,%u)",
		GetSQLValueString($_SESSION['ID_prof'], "int"),
		GetSQLValueString($_POST['activite'], "text"),
		GetSQLValueString($_POST['couleur2police'], "text"),
		GetSQLValueString($position, "int")
		);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$insertGoTo = "type_activite_ajout.php";
	if (isset($_SERVER['QUERY_STRING'])) {
		$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
		$insertGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $insertGoTo));
}

$choix_prof_RsActivite = "0";
if (isset($_SESSION['ID_prof'])) {
        $choix_prof_RsActivite = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsActivite = sprintf("SELECT * FROM cdt_type_activite WHERE ID_prof=%u ORDER BY cdt_type_activite.pos_typ", $choix_prof_RsActivite);
$RsActivite = mysqli_query($conn_cahier_de_texte, $query_RsActivite) or die(mysqli_error($conn_cahier_de_texte));
$row_RsActivite = mysqli_fetch_assoc($RsActivite);
$totalRows_RsActivite = mysqli_num_rows($RsActivite);
?>



<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<script type="text/javascript" src="../jscripts/jquery.colorbox.js"></script>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/CP_Class.js"></script>
<script type="text/JavaScript">
<!--
function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}

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
//-->
</script>

<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<link media="screen" rel="stylesheet" href="../styles/colorbox.css" />
<link rel="stylesheet" type="text/css" href="../styles/colorpicker.css" />
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Types d'activit&eacute;s";
require_once "../templates/default/header.php";
?>
<HR>
<blockquote><blockquote><blockquote>
</blockquote>
<fieldset>
<legend>Types d'activit&eacute;s existants</legend>
<br />
<table width="350" border="0" align="center" cellpadding="0" cellspacing="0" class="bordure">
<tr class="Style6">
<td height="20">&nbsp;</td>
<td height="20"><div align="center">Types d'activit&eacute;s </div></td>
<td height="20">&nbsp;</td>
<td height="20">&nbsp;</td>
<td height="20">&nbsp;</td>
<td height="20">&nbsp;</td>
</tr>
<?php 

$x=0;
do {
	$x=$x+1;
	$tabpos1[$x]=$row_RsActivite['pos_typ'];
	$tabid1[$x]=$row_RsActivite['ID_activite'];
	$tabactiv1[$x]=$row_RsActivite['activite'];
	$tabcoul1[$x]=$row_RsActivite['couleur_activite'];
} while ($row_RsActivite = mysqli_fetch_assoc($RsActivite));


$t1=$x;
$x=0; 
do { 
	
	$x=$x+1; 
	?>
	<tr class="tab_detail_bleu">
	<td class="bordure"><div align="center"><?php echo $x.'&nbsp;' ;  ?></div></td>
	<td class="bordure" style='color:<?php echo $tabcoul1[$x]; ?>'><div align="center"><strong><?php echo $tabactiv1[$x]; ?>&nbsp;&nbsp;</strong></div></td>
	<td class="menu_detail"><?php if($x!=1) { ?>            
		<?php echo '<form style="margin:0px" name="Remonter" method="post" action="type_up.php';?>	
		<?php echo '"><input name="ID_activite" type="hidden" id="ID_activite" value="';?>
		<?php echo $tabid1[$x]?>
		<?php echo '"><input name="ID_precedent" type="hidden" id="ID_precedent" value="';?>
		<?php echo $tabid1[$x-1] ?>
		<?php echo '"><input name="pos_precedent" type="hidden" id="pos_precedent" value="';?>
		<?php echo $tabpos1[$x-1] ?>
		<?php echo '"><input name="Remonter" type="hidden" value="Remonter"><input type="image" src="../images/up.gif" alt="Remonter ce type d\'activit&eacute;s "></div>';?>
		<?php echo ' </form>';
	} 
	else {echo '&nbsp;';};
	?></td>
	<td class="menu_detail"> 
	
	
	<?php if($x!=$t1) { ?>            
		<?php echo '<form style="margin:0px" name="Descendre" method="post" action="type_down.php';?>
		<?php echo '"><input name="ID_activite" type="hidden" id="ID_activite" value="';?>
		<?php echo $tabid1[$x]?>
		<?php echo '"><input name="ID_suivant" type="hidden" id="ID_suivant" value="';?>
		<?php echo $tabid1[$x+1] ?>
		<?php echo '"><input name="pos_suivant" type="hidden" id="pos_suivant" value="';?>
		<?php echo $tabpos1[$x+1] ?>
		<?php echo '"><input name="Descendre" type="hidden" value="Descendre"><input type="image" src="../images/down.gif" alt="Descendre ce type d\'activit&eacute;s "></div>';?>
		<?php echo ' </form>';
	} 
	else {echo '&nbsp;';};
	?>	  </td>
	<td class="menu_detail"><img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','type_activite_modif.php?ID_activite=<?php echo $tabid1[$x]; ?>');return document.MM_returnValue" /></td>
	<td class="menu_detail"><img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick="MM_goToURL('window','type_activite_supprime.php?ID_activite=<?php echo $tabid1[$x]; ?>');return document.MM_returnValue" /></td>
	</tr>
<?php } 
while ($x < $totalRows_RsActivite); 

?>
</table>
<br/>
</fieldset>
<br/>
<p align="center">Saisissez successivement vos diff&eacute;rents types d'activit&eacute;s (Exemples : Cours, TP, TD, Evaluation...) et choisissez sa couleur ad&eacute;quate pour la consultation. </p>
<p align="center">Le premier type du tableau sera celui s&eacute;lectionn&eacute; par d&eacute;faut.</p>
<br/>
<fieldset>
<legend>Ins&eacute;rer un nouveau type d'activit&eacute;</legend>
<br />
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
<table width="350" align="center" class="bordure">
<tr valign="baseline">
<td bgcolor="#EBEBEB">
<div align="center"><br />
<input name="activite" type="text"  id="activite" style="background-color:#BBCEDE;color:#000066;text-align:center;font-weight: bold;font-size:xx-small;font-family: Verdana, Arial, Helvetica;" value="" size="15" maxlength="14">
<input type="hidden" size="10" name="couleur2police" value="#000066" maxlength="7" style="font-family:Tahoma;font-size:x-small">
<img src="../images/color.gif" width="21" height="20" border="0" align="absmiddle" onClick="fctShow(document.form1.couleur2police);" style="cursor:pointer">
<br />
</div></td></tr>
<tr valign="baseline">
<td bgcolor="#EBEBEB"><div align="center"> 
<p>
<input name="submit" type="submit" value="Ajouter ce type d'activit&eacute;" />
<br />
</p>
</div></td>
</tr>
</table>
<br />
<input type="hidden" name="MM_insert" value="form1" />
</form>
</fieldset>
</blockquote></blockquote></blockquote>
<p align="center">
<a href="enseignant.php">Retour au Menu Enseignant</a>
</p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsActivite);
?>
