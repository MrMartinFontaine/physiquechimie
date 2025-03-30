<?php
include "../../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');


$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
        $query_update = "TRUNCATE TABLE `ele_liste` ";
        $result_update = mysqli_query($conn_cahier_de_texte, $query_update);

        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
        $query_update = "TRUNCATE TABLE `ele_absent` ";
        $result_update = mysqli_query($conn_cahier_de_texte, $query_update);
        
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
        $query_update = "TRUNCATE TABLE `ele_gic` ";
        $result_update = mysqli_query($conn_cahier_de_texte, $query_update);

        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
        $query_update = "TRUNCATE TABLE `ele_present` ";
        $result_update = mysqli_query($conn_cahier_de_texte, $query_update);
		
 $updateGoTo = "module_absence.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}


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
$header_description="Vidage de certaines tables relatives &agrave; la gestion des &eacute;l&egrave;ves absents";
require_once "../../templates/default/header.php";
?>
  <blockquote>
    <blockquote>
      <blockquote>
        <p align="left">&nbsp;</p>
        <p>
        <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
          <div align="center">
          <fieldset style="width : 90%">
          <p align="left">Dans le cas d'une nouvelle ann&eacute;e, il est n&eacute;cessaire de vider la liste des &eacute;l&egrave;ves (table ele_liste), leurs absences (table ele_absent ) et leur affectation au sein de regroupements (table ele_gic).</p>
          <p align="left">&nbsp;</p>
          <p>
            <label>
            
            </label>
          </p>
          <p>&nbsp;</p>
          <p>
            <input type="submit" value="Confirmer le vidage de ces tables">
          </p>
          </fieldset>
          <input type="hidden" name="MM_update" value="form1">
        </form>
        </p>
        <p align="left">&nbsp;</p>
      </blockquote>
    </blockquote>
  </blockquote>
  <p align="center"><a href="module_absence.php">Retour au Menu Gestion des absences </a></p>
  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
