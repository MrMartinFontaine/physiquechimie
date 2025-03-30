<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") && (isset($_POST['nom_matiere']))) {
	$nom_matiere= str_replace(array("/", "&", "\'"), "-",GetSQLValueString($_POST['nom_matiere'], "text") );
	$insertSQL = sprintf("INSERT INTO cdt_matiere (nom_matiere) VALUES (%s)", $nom_matiere);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$insertGoTo = "matiere_ajout.php";
	if (isset($_SERVER['QUERY_STRING'])) {
		$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
		$insertGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $insertGoTo));
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMatiere = "SELECT * FROM cdt_matiere ORDER BY nom_matiere ASC";
$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
$totalRows_RsMatiere = mysqli_num_rows($RsMatiere);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
//-->
</script>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
  <?php 
$header_description="Gestion des mati&egrave;res";
require_once "../templates/default/header.php";
?>
<br/>
<script language="JavaScript" type="text/JavaScript">

function formfocus() {
	document.form1.nom_matiere.focus()
	document.form1.nom_matiere.select()
}
</script>
  <form onLoad= "formfocus()" method="post"  name="form1" action="<?php echo $editFormAction; ?>">
    <table width="95%" align="center" class="tab_detail_gris" cellpadding="0" cellspacing="0" >
      <tr valign="baseline">
        <td ><br /><div align="right" style="float:right"><br/>
            <a href="index.php"><img src="../images/home-menu.gif" border="0" align="top"></a>&nbsp;&nbsp;</div><div align="center" style="float:center" >&nbsp;&nbsp;<strong>Nouvelle mati&egrave;re &nbsp;</strong>
          <input name="nom_matiere" type="text"  value="" size="32">&nbsp;<input type="submit" value="Ajouter cette mati&egrave;re"><input type="hidden" name="MM_insert" value="form1"></div> <br />       
		</td>
      </tr>
    </table>
  </form>
  <p>
    <script> formfocus(); </script>
  </p>
  <p>&nbsp;</p>
  <p><a href="import_matiere.php">Importation des mati&egrave;res depuis un fichier CSV ou txt</a> </p>
  <p><a href="matiere_fusion.php">Fusionner deux mati&egrave;res</a></p>
  <p><a href="matiere_maj.php">Visualiser les mati&egrave;res non encore affect&eacute;es &agrave; des contenus </a></p>
  <p><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp;</p>
  <table border="0" align="center" width ="70 %">
    <tr class="Style6">
      <td>Ref &nbsp;</td>
      <td><div align="center"><?php echo $totalRows_RsMatiere;?> mati&egrave;re(s)</div></td>
      <td>Editer &nbsp;</td>
      <td>Supprimer &nbsp;</td>
    </tr>
    <?php do { ?>
      <tr>
        <td class="tab_detail_gris"><div align="center"><?php echo $row_RsMatiere['ID_matiere']; ?></div></td>
        <td class="tab_detail_gris"><?php echo $row_RsMatiere['nom_matiere']; ?></td>
        <td class="tab_detail_gris"><div align="center"><img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','matiere_modif.php?ID_matiere=<?php echo $row_RsMatiere['ID_matiere']; ?>');return document.MM_returnValue"></div></td>
        <td class="tab_detail_gris"><div align="center"><img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick="MM_goToURL('window','matiere_supprime.php?ID_matiere=<?php echo $row_RsMatiere['ID_matiere']?>&nom_matiere=<?php echo $row_RsMatiere['nom_matiere']?>');return document.MM_returnValue"></div></td>
      </tr>
      <?php } while ($row_RsMatiere = mysqli_fetch_assoc($RsMatiere)); ?>
  </table>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsMatiere);
?>
