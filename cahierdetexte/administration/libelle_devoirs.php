<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');


$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
if(isset($_POST['libelle_devoir']))
	{
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
	$libelle_devoir = GetSQLValueString($_POST['libelle_devoir'], "text");
	$query_update = "UPDATE `cdt_params` SET `param_val`=$libelle_devoir WHERE `param_nom`='libelle_devoir';";
	$result_update = mysqli_query($conn_cahier_de_texte, $query_update);
	}

 $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsLibelle_devoir = "SELECT param_val FROM cdt_params WHERE param_nom='libelle_devoir'";
$RsLibelle_devoir = mysqli_query($conn_cahier_de_texte, $query_RsLibelle_devoir) or die(mysqli_error($conn_cahier_de_texte));
$row_RsLibelle_devoir = mysqli_fetch_assoc($RsLibelle_devoir);

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
$header_description="Libell&eacute; caract&eacute;risant les devoirs ou contr&ocirc;les effectu&eacute;s en dehors des heures de cours
";
require_once "../templates/default/header.php";
?>
  <blockquote>
    <blockquote>
      <blockquote>
        <p align="left">&nbsp;</p>
        <p>
        <form method="post" name="form1" action="<?php echo $editFormAction; ?>">
          <div align="center">
          <fieldset style="width : 90%">
          <p align="left">Vous pouvez programmer des &quot;Devoirs&quot; ou &quot;Contr&ocirc;les&quot; effectu&eacute;s en dehors des heures de cours.</p>
          <p align="left">Il vous appartient de d&eacute;finir le terme g&eacute;n&eacute;rique pour ce type de travail qui appara&icirc;tra dans votre cahier de textes (exemple : Devoir, Contr&ocirc;le, DS, DST...).</p>
          <p align="left">Le terme utilis&eacute; actuellement par d&eacute;faut est <strong><?php echo $row_RsLibelle_devoir['param_val'];?></strong>.</p>
          <p align="left">&nbsp;</p>
          <p>
            <label>
            <input name="libelle_devoir" type="text" id="libelle_devoir " value="<?php echo $row_RsLibelle_devoir['param_val'];?>" size="20" maxlength="20">
            </label>
          </p>
          <p>&nbsp;</p>
          <p>
            <input type="submit" value="Enregistrer le nouveau libell&eacute;">
          </p>
          </fieldset>
          <input type="hidden" name="MM_update" value="form1">
        </form>
        </p>
        <p align="left">&nbsp;</p>
      </blockquote>
    </blockquote>
  </blockquote>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
