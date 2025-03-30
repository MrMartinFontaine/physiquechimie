<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>4) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE cdt_prof SET path_fichier_perso=%s WHERE ID_prof=%u",
					   GetSQLValueString($_POST['chemin_fichier_perso'], "text"),
                       GetSQLValueString($_SESSION['ID_prof'], "int"));
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));



  $updateGoTo = "direction.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
header(sprintf("Location: %s", $updateGoTo));
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProf = sprintf("SELECT * FROM cdt_prof WHERE ID_prof=%u",
					   GetSQLValueString($_SESSION['ID_prof'], "text")
					   );
$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);
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
$header_description="Acc&egrave;s &agrave; mes fichiers personnels sur le serveur";
require_once "../templates/default/header.php";
?>
  <HR>
  <p>Ces informations vous permettront de cr&eacute;er de liens hypertextes vers vos fichiers personnels d&eacute;j&agrave; pr&eacute;sents sur le serveur. <br>
    En cas de probl&egrave;me, contactez votre administrateur pour effectuer ce param&eacute;trage. </p>
  <form name="form1" method="POST" action="chemin_fichier_perso.php">
    <p>&nbsp;</p>
    <p>Chemin d'acc&egrave;s &agrave; mes dossiers depuis la racine  &gt; <em>exemple : /mon_nom/mes_cours/ </em> </p>
    <p>
      <input name="chemin_fichier_perso" type="text" id="chemin_fichier_perso" size="100" value="<?php echo $row_RsProf['path_fichier_perso'] ;?>">
    </p>
    <p>
      <input type="hidden" name="MM_update" value="form1">
    </p>
    <p class="erreur">D&eacute;connectez vous puis reconnectez vous pour que cette modification soit prise en compte.</p>
    <p align="center"><br>
      <input type="submit" name="Submit" value="Enregistrer ces param&egrave;tres">
  </form>
  <p align="center">&nbsp;</p>
  <p align="center"> </p>
  <p align="center">&nbsp;</p>
  <p align="center"><a href="direction.php">Annuler </a></p>
  <DIV id=footer>
    <p class="auteur"><a href="contribution.php" class="auteur">Pierre Lemaitre 
      - St L&ocirc; (France)  <br />
    </a></p>
  </DIV>
</DIV>
</body>
</html>
