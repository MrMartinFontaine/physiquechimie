<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 

require_once('../inc/functions_inc.php');

$editFormAction = '#';

if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {

  $insertSQL = sprintf("INSERT INTO cdt_progression (prof_ID,titre_progression,contenu_progression) VALUES (%s,%s,%s)",
                       GetSQLValueString($_SESSION['ID_prof'], "int"),
                       GetSQLValueString($_POST['titre_progression'], "text"),
					   GetSQLValueString($_POST['contenu_progression'], "text")  );

  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));

  $insertGoTo = "progression.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php
if($_SESSION['xinha_equation']=="O"){ ?><script type="text/javascript" src="xinha/plugins/Equation/ASCIIMathML.js"></script>
<?php };?>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV >
<table width="100%" border="0" cellspacing="0">
  <tr class="lire_cellule_4">
    <td width="93%" align="center"><?php if (isset($_SESSION['identite'])){echo '<img src="../images/identite.gif" >&nbsp;'.$_SESSION['identite'].'&nbsp;&nbsp;&nbsp; Carnet de bord - Progressions';}?>	</td>
	<td width="7%"><a href="progression.php"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0"></a>	</td>
  </tr>
</table>
  <script language="JavaScript" type="text/JavaScript">

function formfocus() {
document.form1.titre_progression.focus()
document.form1.titre_progression.select()
}

function valider(){
  if(document.form1.titre_progression.value != "") {
    return true;
  }
  else {
    alert("Saisissez un titre pour votre fiche");
    return false;
  }
}
</script>
  <form onLoad= "formfocus()" method="POST"  name="form1" action="<?php echo $editFormAction; ?>" onsubmit="return valider()">

    <p>&nbsp;</p>
    <p><strong>Titre de ma fiche </strong>(Ex : Secondes - Sc. Physiques)
      &nbsp;:
      <input name="titre_progression" id="titre_progression" type="text" size="50" >
      &nbsp;
      <input type="submit" name="Submit" value="Enregistrer">
      <br>
      <br>
      <br>
      <em>NB : Les fiches vous seront propos&eacute;es en saisie de s&eacute;ance dans un menu d&eacute;roulant class&eacute;s par ordre<strong> Alphab&eacute;tique</strong> sur le titre.<br>
Adoptez donc une strat&eacute;gie pour le nommage de vos diff&eacute;rentes fiches. </em></p>
    <p align="center">
	<?php
	if ((isset($_SESSION['affiche_xinha']))&&($_SESSION['affiche_xinha']==1)){
		include('area_progression.php');}
	else { 
		include('area_progression_tiny.php');};
	?>

<textarea name="contenu_progression" cols="120" rows="40" id="contenu_progression"  height= "80"><?php 
if ((isset($_SERVER['HTTP_USER_AGENT']))&&(preg_match('/Firefox/i',$_SERVER['HTTP_USER_AGENT']))){echo '<br />';}; 
?></textarea>
    </p>
    <p> </p>
    <input type="hidden" name="MM_insert" value="form1">
  </form>
  <p align="center"><a href="enseignant.php"><br>
    Retour au Menu Enseignant</a>

</DIV>
</body>
</html>
