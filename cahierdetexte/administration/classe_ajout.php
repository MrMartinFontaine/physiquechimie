<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") && (isset($_POST['nom_classe']))) {
	
	$nom_classe= str_replace(array("/", "&", "\'"), "-",GetSQLValueString($_POST['nom_classe'], "text") );
	$insertSQL = sprintf("INSERT INTO cdt_classe (nom_classe,passe_classe) VALUES (%s,%s)",
		$nom_classe,
		GetSQLValueString(md5($_POST['passe_classe']), "text")
		);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$insertGoTo = "classe_ajout.php";
	if (isset($_SERVER['QUERY_STRING'])) {
		$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
		$insertGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $insertGoTo));
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);
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
<style type="text/css">
<!--
.Style70 {color: #0E2127}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
  <?php 
$header_description="Gestion des classes";
require_once "../templates/default/header.php";
?>
  <script language="JavaScript" type="text/JavaScript">

function formfocus() {
	document.form1.nom_classe.focus()
	document.form1.nom_classe.select()
}
</script>
  <br />
  <form method="post" onLoad= "formfocus()" name="form1" action="<?php echo $editFormAction; ?>">
    <table width="95%" align="center" cellpadding="0" cellspacing="0" >
      <tr valign="baseline">
        <td class="tab_detail_gris"><p>
          <div align="right" style="float:right"><br/>
            <a href="index.php"><img src="../images/home-menu.gif" border="0" align="top"></a>&nbsp;&nbsp;</div>
          <br/>
          Classe
          <input type="text" name="nom_classe" value="" size="32">
          &nbsp;Mot de passe (facultatif)
          <input name="passe_classe" type="password" maxlength="8">
          </p>
          <p align="center">
            <input name="submit" type="submit" value="Ajouter cette classe">
          </p>
          <p align="left"><img src="../images/lightbulb.png" width="16" height="16"> Si un mot de passe est d&eacute;fini, celui-ci devra obligatoirement &ecirc;tre fourni pour toute consultation du cahier de textes par les &eacute;l&egrave;ves, parents ou toute autre personne ext&eacute;rieure &agrave; l'&eacute;tablissement.</p>
          <p align="left">Lors d'un import depuis un fichier texte, l'acc&egrave;s en consultation poss&egrave;de l'attribut interdit par d&eacute;faut si le champ mot de passe n'est pas renseign&eacute;. Vous pouvez modifier ci-dessous le param&eacute;trage de l'acc&egrave;s en consultation. </p></td>
      </tr>
    </table>
    <input type="hidden" name="MM_insert" value="form1">
  </form>
  <hr />
  <p align="center"><a href="import_classe.php"><br>
    Importation des classes depuis un fichier CSV ou txt</a> </p>
  <p align="center"><a href="classe_modif.php?ID_classe=0">Modifier l'acc&egrave;s pour l'ensemble des classes</a></p>
  <p align="center"><br>
  </p>
  <script> formfocus(); </script>
  </p>
  <?php if ($totalRows_RsClasse > 0) { ?>
  <table border="0" align="center">
    <tr>
      <td class="Style6"><div align="center">R&eacute;f&nbsp;&nbsp;</div></td>
      <td class="Style6"><div align="center">NOM DE LA CLASSE&nbsp;&nbsp;</div></td>
      <td class="Style6"><div align="center">Acc&egrave;s en consultation &nbsp;&nbsp;</div></td>
      <td class="Style6"><div align="center">Editer&nbsp;&nbsp;</div></td>
      <td class="Style6"><div align="center">Supprimer&nbsp;&nbsp;</div></td>
    </tr>
    <?php do { ?>
      <tr>
        <td class="tab_detail_gris"><div align="right"><?php echo $row_RsClasse['ID_classe']; ?></div></td>
        <td class="tab_detail_gris"><div align="left"><?php echo $row_RsClasse['nom_classe']; ?></div></td>
        <td class="tab_detail_gris"><div align="center" class="Style70"><?php echo empty($row_RsClasse['passe_classe']) ? "<span style='color:#FF0000'>Interdit</span>" : ($row_RsClasse['passe_classe']==md5("") ? "<span style='color:#006633'>Libre sans mot de passe</span>" : "<span class='tab_detail_gris'>Prot&eacute;g&eacute; par mot de passe</span>"); ?></div></td>
        <td class="tab_detail_gris"><div align="center" ><img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','classe_modif.php?ID_classe=<?php echo $row_RsClasse['ID_classe']; ?>');return document.MM_returnValue"></div></td>
        <td class="tab_detail_gris"><div align="center" ><img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="12" height="13" onClick="if(confirm('Voulez-vous r\351ellement supprimer la classe de <?php echo $row_RsClasse['nom_classe']; ?> ?')){MM_goToURL('window','classe_supprime.php?ID_classe=<?php echo $row_RsClasse['ID_classe']; ?>');return document.MM_returnValue}"></div></td>
      </tr>
      <?php } while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)); ?>
  </table>
  <?php } ?>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsClasse);
?>
