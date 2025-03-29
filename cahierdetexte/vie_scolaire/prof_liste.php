<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>7)&&($_SESSION['droits']<>1)){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

$query_RsProf = "SELECT * FROM cdt_prof WHERE droits='2' AND ancien_prof='N' ORDER BY identite,nom_prof ASC";
$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);
$totalRows_RsProf = mysqli_num_rows($RsProf);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

$query_RsProf2 = "SELECT * FROM cdt_prof WHERE droits<>'2' AND ancien_prof='N' ORDER BY identite,nom_prof ASC";
$RsProf2 = mysqli_query($conn_cahier_de_texte, $query_RsProf2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf2 = mysqli_fetch_assoc($RsProf2);
$totalRows_RsProf2 = mysqli_num_rows($RsProf2);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsOldProf = "SELECT * FROM cdt_prof WHERE ancien_prof='O' ORDER BY nom_prof ASC";
$RsOldProf = mysqli_query($conn_cahier_de_texte, $query_RsOldProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsOldProf = mysqli_fetch_assoc($RsOldProf);
$totalRows_RsOldProf = mysqli_num_rows($RsOldProf);

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
$header_description="Liste du personnel";
require_once "../templates/default/header.php";
?>
<table width="90%" border="0" align="center">
<tr>
<td><div align="center"><br>
<?php echo $totalRows_RsProf.' Enseignants';?><br>
<i>Les enseignants dont le nom est en italique sont ceux qui n'ont pas saisi leur identit&eacute; dans leur interface.<br>
Les enseignants dont le nom est en noir sont ceux qui n'ont encore rien saisi dans leurs cahiers de textes.
</i></div></td>
<td valign="top"><div align="right"><a href="
<?php 
if (($_SESSION['droits']==2)||($_SESSION['droits']==8)){echo '../enseignant/enseignant.php';};
if ($_SESSION['droits']==3){echo 'vie_scolaire.php';};
if ($_SESSION['droits']==4){echo '../direction/direction.php';};
if ($_SESSION['droits']==1){echo '../administration/index.php';};
?>
"><br />
<img src="../images/home-menu.gif" border="0" align="top"></a>&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
</tr>
</table>

<br />
<table border="0" align="center" width="90%" >
<tr> 
<td class="Style6"><div align="center">NOM</div></td>
<td class="Style6">M&eacute;l</td>
</tr>
<?php do { 
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsAncienProf = sprintf("SELECT * FROM cdt_agenda WHERE `prof_ID`=%s",
		GetSQLValueString($row_RsProf['ID_prof'], "int"));
	$RsAncienProf = mysqli_query($conn_cahier_de_texte, $query_RsAncienProf) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsAncienProf = mysqli_fetch_assoc($RsAncienProf);
	$totalRows_RsAncienProf = mysqli_num_rows($RsAncienProf);
	?>
	<tr> 
	<td class="tab_detail_gris"><div align="left"><?php
	if ($row_RsProf['identite']=="") {echo "<i><font color="; echo ($totalRows_RsAncienProf==0?"black":"red"); echo ">".$row_RsProf['nom_prof']."</font></i>";} elseif($totalRows_RsAncienProf==0) {echo "<font color=black>".$row_RsProf['identite']."</font>";} else {echo "<b>".$row_RsProf['identite']."</b>";};
        ?></div></td>
        <td class="tab_detail_gris"><div align="left"><a href="mailto:<?php echo $row_RsProf['email']; ?>"><?php echo $row_RsProf['email']; ?></a></div></td>
        </tr>
<?php } while ($row_RsProf = mysqli_fetch_assoc($RsProf)); ?>
</table>
<p>&nbsp;</p>
<div align="center">Autre personnel</div>
<br>
<table border="0" align="center" width="90%" >
<tr> 
<td class="Style6"><div align="center">NOM</div></td>
<td class="Style6">M&eacute;l</td>
</tr>
<?php do { ?>
	<tr> 
	<td class="tab_detail_gris"><div align="left"><?php echo $row_RsProf2['identite']; ?></div></td>
	<td class="tab_detail_gris"><div align="left"><a href="mailto:<?php echo $row_RsProf2['email']; ?>"><?php echo $row_RsProf2['email']; ?></a></div></td>
	</tr>
<?php } while ($row_RsProf2 = mysqli_fetch_assoc($RsProf2)); ?>
</table>

<?php
if ($totalRows_RsOldProf>0){?>
<p>&nbsp;</p>
<div align="center">Ancien personnel</div>
<br>
<table border="0" align="center" width="90%" >
<tr> 
<td class="Style6"><div align="center">NOM</div></td>
<td class="Style6">M&eacute;l</td>
</tr>
<?php do { ?>
	<tr> 
	<td class="tab_detail_gris"><div align="left"><?php echo $row_RsOldProf['identite']; ?></div></td>
	<td class="tab_detail_gris"><div align="left"><a href="mailto:<?php echo $row_RsOldProf['email']; ?>"><?php echo $row_RsOldProf['email']; ?></a></div></td>
	</tr>
<?php } while ($row_RsOldProf = mysqli_fetch_assoc($RsOldProf)); ?>
</table>
<?php
};
?>

<p><a href="
<?php 
if (($_SESSION['droits']==2)||($_SESSION['droits']==8)){echo '../enseignant/enseignant.php';};
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'vie_scolaire.php';};
if ($_SESSION['droits']==4){echo '../direction/direction.php';};
if ($_SESSION['droits']==1){echo '../administration/index.php';};
?>
"><br>

<?php
if (($_SESSION['droits']==2)||($_SESSION['droits']==8)){echo 'Retour au Menu Enseignant';};
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'Retour au Menu Vie scolaire';};
if ($_SESSION['droits']==4){echo 'Retour au Menu Responsable Etablissement';};
if ($_SESSION['droits']==1){echo 'Retour au Menu Administrateur';};
?>
</a> </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsProf);
mysqli_free_result($RsProf2);
mysqli_free_result($RsOldProf);
?>
