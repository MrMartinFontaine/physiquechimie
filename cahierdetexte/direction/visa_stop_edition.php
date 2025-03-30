<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>4) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');


$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

	$vse=GetSQLValueString(isset($_POST['visa_stop_edition']) ? 'true' : '', 'defined','"Oui"','"Non"');
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
    $updateSQL = sprintf("UPDATE `cdt_params` SET `param_val`=%s WHERE `param_nom`='visa_stop_edition';",$vse); 
	$result_update = mysqli_query($conn_cahier_de_texte, $updateSQL);


 $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
header(sprintf("Location: %s", $updateGoTo));
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsvisa_stop_edition = "SELECT param_val FROM cdt_params WHERE param_nom='visa_stop_edition' LIMIT 1";
$Rsvisa_stop_edition = mysqli_query($conn_cahier_de_texte, $query_Rsvisa_stop_edition) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsvisa_stop_edition = mysqli_fetch_assoc($Rsvisa_stop_edition);

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
$header_description="Modification des saisies ant&eacute;rieures au visa";
require_once "../templates/default/header.php";
?>
  <blockquote>
    <blockquote>
      <blockquote>
        <p style="color:red;">&nbsp;</p>
        <p align="left">&nbsp;</p>
        <p>
        <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
          <div align="center">
          <fieldset style="width : 90%">
          <legend align="top">Gestion stricte du visa pos&eacute; par le Responsable Etablissement </legend>
          <p align="left">&nbsp;</p>
          <p align="left">En cochant cette option, vous interdirez syst&eacute;matiquement la modification des saisies d&eacute;j&agrave; r&eacute;alis&eacute;es par les utilisateurs si elles sont ant&eacute;rieures &agrave; la date du visa pos&eacute; par le Responsable Etablissement. </p>
          <p align="left">&nbsp;</p>
          <p>
            <label>Interdire les modifications 
              <input type="checkbox" name="visa_stop_edition" value="" 
			  <?php if (!(strcmp($row_Rsvisa_stop_edition['param_val'],'Oui'))) {echo "checked=checked";} ?>>
			  
            </label>
          </p>
          <p>&nbsp;</p>
          <p>
            <input type="submit" value="Enregistrer ce choix">
          </p>
          </fieldset>
          <input type="hidden" name="MM_update" value="form1">
        </form>
        </p>
        <p align="left">&nbsp;</p>
      </blockquote>
    </blockquote>
  </blockquote>
  <p align="center"><a href="index.php">Retour au Menu Responsable Etablissement</a></p>
  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
if (isset($Rsvisa_stop_edition)){mysqli_free_result($Rsvisa_stop_edition);};
?>
