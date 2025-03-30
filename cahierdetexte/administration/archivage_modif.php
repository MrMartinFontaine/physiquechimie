<?php include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');


$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form_arch")) {
 $nom_archive= str_replace(array("/", "&"), "-",GetSQLValueString($_POST['nom_archiv'], "text") );
  $updateSQL = sprintf("UPDATE cdt_archive SET NomArchive=%s WHERE NumArchive=%s",
                       $nom_archive,
                       GetSQLValueString($_POST['archID'], "int"));
             
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Resultarch = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));

 $updateGoTo = "archivage.php";
 if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$IDArchive = "0";
if (isset($_GET['archID'])) {
  $IDArchive = (get_magic_quotes_gpc()) ? $_GET['archID'] : addslashes($_GET['archID']);
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsArchive3 = sprintf("SELECT * FROM cdt_archive WHERE NumArchive=%s",$IDArchive);
$RsArchive3 = mysqli_query($conn_cahier_de_texte, $query_RsArchive3) or die(mysqli_error($conn_cahier_de_texte));
$row_RsArchive3 = mysqli_fetch_assoc($RsArchive3);
?>

<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style70 {font-size: small;color: #000066;}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Modification du nom d'une archive";
require_once "../templates/default/header.php";
?>

<HR>
<form name="form_arch" method="post" action="<?php echo $editFormAction; ?>">
<br>
<table width="95%" align="center" class="lire_cellule_22" >
    <tr > 
      <td width="70%" class="lire_cellule_22" > <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><i>Nouveau nom 
          de l'archive  </i><b><?php echo $row_RsArchive3['NomArchive']; ?></b><i> : </i></font></p>
        <td width="25%" class="lire_cellule_22" > <input name="nom_archiv" type="text" id="nom_archiv"  size="30" maxlength="30" value="<?php echo $row_RsArchive3['NomArchive']; ?>"></td>
    </tr>
    <tr> 
      <td colspan="2" class="lire_cellule_22" >
        <p>&nbsp;</p>
        <p align="center"><input type="submit" name="verif" value="Valider la modification du nom de l'archive" ></p>
        <p>&nbsp; </p>        </td>
    </tr>
  </table>
  
  <input type="hidden" name="MM_update" value="form_arch">
  <input type="hidden" name="archID" value="<?php echo $row_RsArchive3['NumArchive']; ?>">
</form>
  <p>&nbsp;</p>
  <p><a href="archivage.php">Retour &agrave; l'archivage</a>  </p>

<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsArchive3);
?>
