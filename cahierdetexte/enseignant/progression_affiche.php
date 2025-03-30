<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 

$colname_RsProgression = "-1";
if (isset($_GET['ID_progression'])) {
  $colname_RsProgression = (get_magic_quotes_gpc()) ? $_GET['ID_progression'] : addslashes($_GET['ID_progression']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProgression = sprintf("SELECT * FROM cdt_progression WHERE ID_progression=%u AND prof_ID=%u", $colname_RsProgression,$_SESSION['ID_prof']);
$RsProgression = mysqli_query($conn_cahier_de_texte, $query_RsProgression) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProgression = mysqli_fetch_assoc($RsProgression);
$totalRows_RsProgression = mysqli_num_rows($RsProgression);
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
<DIV>
<table width="100%" border="0" cellspacing="0">
  <tr class="lire_cellule_4">
    <td width="93%" align="center"><?php if (isset($_SESSION['identite'])){echo '<img src="../images/identite.gif" >&nbsp;'.$_SESSION['identite'].'&nbsp;&nbsp;&nbsp; PROGRESSIONS &nbsp;&nbsp;&nbsp;'.$row_RsProgression['titre_progression'];}?>	</td>
	<td width="7%"><a href="progression.php"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0"></a>	</td>
  </tr>
</table>

<div align="center"><a href="progression.php"></a><br>

<?php echo $row_RsProgression['contenu_progression']; ?>
    </p>
  
</div>
<p>&nbsp;</p>
  <p align="center"><a href="enseignant.php">
  Retour au Menu Enseignant</a> - <a href="ecrire.php?date=<?php echo date('Ymd');?>">Retour en saisie de s&eacute;ance</a></p> 
  
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsProgression);
?>

