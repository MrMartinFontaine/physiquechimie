<?php 
include "../../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');



if ((isset($_POST["MM_update2"])) && ($_POST["MM_update2"] == "form2")) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.0.0.0 Standard' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4000' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '".date("d/m/Y, g:i a")."' WHERE `param_nom` ='date_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);

require_once "maj_sql_inc.php";

 //$updateGoTo = "../../index.php";
// header(sprintf("Location: %s", $updateGoTo));
  }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style70 {font-size: 12}
-->
</style>
</HEAD>
<BODY>
<p>&nbsp;</p>
<DIV id=page>
  <p>
    <?php 
$header_description="Mises &agrave; jour de la base de donn&eacute;es";
require_once "../../templates/default/header.php";
?>
    </p>
  </p>
  <p>&nbsp;</p>
  <p>Suite &agrave; un probl&egrave;me de mise &agrave; jour (incident serveur),</p>
  <p>il est  n&eacute;cessaire de reprendre la mise &agrave; jour de la base de donn&eacute;es.</p>
  <p>Le script va affecter &agrave; l'application un indice d'une ancienne version ( 4.0.0.0 dans la table cdt_params),</p>
  <p>    puis ex&eacute;cutera les  mises &agrave; jour n&eacute;cessaires pour atteindre la version actuelle.  </p>
  <p> </p>
  <form action="maj_forcee.php" method="post" name="form2" id="form2">
    <p>
      <input type="hidden" name="MM_update2" value="form2">
    </p>
    <p>&nbsp;</p>
    <p>
      <input name="submit2" type="submit"  value="Reprendre la mise &agrave; jour">
      </p>
  </form>
  <p>&nbsp;</p>
  <p><a href="../controle_tables_cdt.php">Voir les tables </a></p>
  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
