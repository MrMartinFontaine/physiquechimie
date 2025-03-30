<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>3)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
$editFormAction = '#';
if ((isset($_SERVER['QUERY_STRING']))and($_SERVER['QUERY_STRING']!='')) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsProf = "SELECT * FROM cdt_prof WHERE droits=2 AND ancien_prof='N' ORDER BY identite ";
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
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Gestion des regroupements";
require_once "../templates/default/header.php";
?>
<HR>
<blockquote>
  <p align="left"><em> Dans cette page, sont list&eacute;s uniquement  les Regroupements et non les Groupes.</em></p>
  <p align="left"><em><strong>Groupe</strong> : Ensemble d'&eacute;l&egrave;ves d'une m&ecirc;me classe (Ex : la classe est divis&eacute;e en 2 groupes).<br>
    Les groupes sont cr&eacute;&eacute;s et g&eacute;r&eacute;s par l'administrateur.<br>
    <br>
    <strong>Regroupement</strong> :  Ensemble d'&eacute;l&egrave;ves provenant de plusieurs classes (Ex : Option Chinois).<br>
    Les regroupements sont cr&eacute;&eacute;s et g&eacute;r&eacute;s par chaque enseignant. <br>
  </em><em><br>
  </em><em>Les liens du tableau ci-dessous, vous permettent cependant d'acc&eacute;der en tant qu'administrateur  &agrave; la page de gestion des regroupements en &eacute;mulant le mode enseignant. <br>
  </em></p>
  </blockquote>
  <br />
  <p align="center">
  <?php if ($_SESSION['droits']==1){ echo '  <a href="../administration/index.php">Retour au Menu Administrateur</a>';};?>
  <?php if ($_SESSION['droits']==3){ echo '  <a href="../vie_scolaire/vie_scolaire.php">Retour au Menu Vie scolaire</a>';};?>
  </p>


<table border="0" align="center">
<tr> 
<td class="Style6">Enseignants</td>
<td class="Style6">Regroupements</td>
</tr>
<?php 
do { 
?>

	<tr> 
	  <td class="tab_detail_gris_clair"><a href="../enseignant/groupe_interclasses_ajout.php?num_prof=<?php echo $row_RsProf['ID_prof']?>&nom_prof=<?php echo $row_RsProf['identite']?>">
<?php echo $row_RsProf['identite']?>
	    </a></td>
		<td class="tab_detail_gris_clair">
		
		<?php
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsregroupement = "SELECT * FROM cdt_groupe_interclasses WHERE prof_ID=".$row_RsProf['ID_prof']." ORDER BY nom_gic ASC";
$Rsregroupement = mysqli_query($conn_cahier_de_texte, $query_Rsregroupement) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsregroupement = mysqli_fetch_assoc($Rsregroupement);
$totalRows_Rsregroupement = mysqli_num_rows($Rsregroupement);
if ($totalRows_Rsregroupement>0) {
do { 
?>
		<table>
		<tr class="tab_detail_gris_clair">
	  <td ><a href="../enseignant/groupe_interclasses_modif.php?ID_gic=<?php echo $row_Rsregroupement['ID_gic']; ?>"><?php echo $row_Rsregroupement['nom_gic']; ?></a></td>
	</tr>
		</table>
<?php
} while ($row_Rsregroupement = mysqli_fetch_assoc($Rsregroupement)); 
 }?>	
		</td>
	</tr>
<?php
} while ($row_RsProf = mysqli_fetch_assoc($RsProf)); ?>
</table>


  <p align="center">&nbsp;</p>
  <p align="center">
  <?php if ($_SESSION['droits']==1){ echo '  <a href="../administration/index.php">Retour au Menu Administrateur</a>';};?>
  <?php if ($_SESSION['droits']==3){ echo '  <a href="../vie_scolaire/vie_scolaire.php">Retour au Menu Vie scolaire</a>';};?>

  
  </p>
  
  <p>&nbsp; </p>

<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($Rsregroupement);
mysqli_free_result($RsProf);
?>
