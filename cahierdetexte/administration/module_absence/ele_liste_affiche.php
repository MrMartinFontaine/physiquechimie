<?php 
include "../../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>3)) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2") && (isset($_GET['code_classe']))) {
	
	$nom_ele= str_replace(array("/", "&", "\'"), "-",GetSQLValueString($_POST['nom_ele'], "text") );
	$prenom_ele= str_replace(array("/", "&", "\'"), "-",GetSQLValueString($_POST['prenom_ele'], "text") );
	$classe_ele= str_replace(array("/", "&", "\'"), "-",GetSQLValueString($_GET['code_classe'], "text") );
	$insertSQL = sprintf("INSERT INTO ele_liste (nom_ele,prenom_ele,classe_ele) VALUES (%s,%s,%s)",$nom_ele,$prenom_ele,$classe_ele);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
	$insertGoTo = "ele_liste_affiche.php?code_classe=".$classe_ele;
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
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
  <p>
    <?php 
$header_description="Gestion de la liste des &eacute;l&egrave;ves";
require_once "../../templates/default/header.php";
?>
  </p>
  

<form method="GET"  name="form1" action="ele_liste_affiche.php">
    <table width="95%" align="center">
      <tr valign="baseline">
        <td class="tab_detail_gris">
		<div style="float:left;display:inline;width:95%">

            <div align="center">
              <select name="code_classe" id="code_classe">
                <option value="value">S&eacute;lectionner la classe</option>
                <?php  do { ?>
                <option value="<?php echo $row_RsClasse['code_classe']?>"
			  <?php 
			  if ((isset($_GET['code_classe']))&&($row_RsClasse["code_classe"]==$_GET['code_classe'])){echo 'selected=" selected"';};
			  ?>><?php echo $row_RsClasse['nom_classe']?></option>
                <?php	} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)); ?>
              </select>
              <input name="submit" type="submit" value="S&eacute;lectionner">
                </div>
		</div>
<div style="width:5%;float:right;">
<?php 
if ($_SESSION['droits']==1){?>
<a href="index.php"><img src="../../images/home-menu.gif">&nbsp;&nbsp;</a>
<?php };

if ($_SESSION['droits']==3){?>
<a href="../../vie_scolaire/vie_scolaire.php"><img src="../../images/home-menu.gif">&nbsp;&nbsp;</a>
<?php };?>
</div>
		  
		  
		  
	    </td>
      </tr>
    </table>
  </form>
 <br/><br/>
  <?php 
if (isset($_GET['code_classe'])) {
	
$choix_classe=$_GET['code_classe'];


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsele_liste = sprintf("SELECT * FROM ele_liste WHERE classe_ele= '%s' ORDER BY nom_ele,prenom_ele ASC",$choix_classe);
$Rsele_liste = mysqli_query($conn_cahier_de_texte, $query_Rsele_liste) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsele_liste = mysqli_fetch_assoc($Rsele_liste);
$totalRows_Rsele_liste = mysqli_num_rows($Rsele_liste);


?>
<script language="JavaScript" type="text/JavaScript">
function formfocus() {
	document.form2.nom_ele.focus()
	document.form2.nom_ele.select()
}
</script>
<form method="post" onLoad= "formfocus()" name="form2" action="ele_liste_affiche.php?code_classe=<?php echo $choix_classe;?>">
<table width="95%" align="center">
<tr valign="baseline">
<td class="tab_detail_gris"><p align="center"><strong>Ajouter un &eacute;l&egrave;ve  dans cette classe </strong></p>
  <p align="center">Nom
    <input type="text" name="nom_ele" value="" size="32">
  &nbsp;Pr&eacute;nom
  <input type="text" name="prenom_ele" value="" size="32">
  &nbsp; &nbsp;
  <input name="submit" type="submit" value="Ajouter cet &eacute;l&egrave;ve">
  </p></td>
</tr>
</table>
<input type="hidden" name="MM_insert" value="form2">
</form>

<?php if($totalRows_Rsele_liste>0){?>
<?php echo '<br />'.$totalRows_Rsele_liste. '&nbsp;&eacute;l&egrave;ves ';?><br/><br/>
</form>
  <table border="0" align="center">
    <tr>
      <td class="Style6">Ref</td>
      <td class="Style6"><div align="center">Nom </div></td>
      <td class="Style6"><div align="center" >Pr&eacute;nom</div></td>
      <td class="Style6">Editer</td>
      <td class="Style6">Supprimer</td>
    </tr>
    <?php do { ?>
      <tr>
        <td class="tab_detail_gris"><?php echo $row_Rsele_liste['ID_ele']; ?></td>
        <td class="tab_detail_gris"><div align="left"><?php echo $row_Rsele_liste['nom_ele']; ?></div></td>
        <td class="tab_detail_gris"><div align="left"><?php echo $row_Rsele_liste['prenom_ele']; ?></div></td>
        <td class="tab_detail_gris"><img src="../../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','ele_liste_modif.php?code_classe=<?php echo $choix_classe; ?>&ID_ele=<?php echo $row_Rsele_liste['ID_ele']; ?>');return document.MM_returnValue"></td>
        <td class="tab_detail_gris"><img src="../../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick="MM_goToURL('window','ele_liste_supprime.php?code_classe=<?php echo $choix_classe; ?>&ID_ele=<?php echo $row_Rsele_liste['ID_ele']; ?>');return document.MM_returnValue"></td>
      </tr>
      <?php } while ($row_Rsele_liste = mysqli_fetch_assoc($Rsele_liste)); ?>
  </table>
  <?php 
  }
  } 
  
  if ($_SESSION['droits']==1){?><p align="center"><a href="index.php">Retour au Menu Administrateur</a></p><?php };
  if ($_SESSION['droits']==3){?><p align="center"><a href="../../vie_scolaire/vie_scolaire.php">Retour au Menu Vie scolaire</a></p>  
  <?php };
  ?>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsClasse);
?>
