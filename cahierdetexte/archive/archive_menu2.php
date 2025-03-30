<?php include "../authentification/authcheck.php" ;?>
<?php require_once('../Connections/conn_cahier_de_texte.php'); ?>
<?php


$profchoix_RsImprime = "0";
if (isset($_SESSION['ID_prof'])) {
  $profchoix_RsImprime = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);

}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsArchiv = "SELECT * FROM cdt_archive ORDER BY cdt_archive.NumArchive ASC";
$RsArchiv = mysqli_query($conn_cahier_de_texte, $query_RsArchiv) or die(mysqli_error($conn_cahier_de_texte));
$row_RsArchiv = mysqli_fetch_assoc($RsArchiv);
$totalRows_RsArchiv = mysqli_num_rows($RsArchiv);


	?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
a img {
	border: none;
}
-->
</style>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
  <DIV id=header_archive>
    <H1>&nbsp;</H1>
    <H1>Cahier de textes </H1>
    <DIV class="description"><?php echo $_SESSION['nom_etab']; ?>
    </DIV>
    <p>&nbsp;</p>
  </DIV>
  <p>&nbsp;</p>

    <?php
	////// Archives  
  
  if ($totalRows_RsArchiv!=0) {
	do {
		$num_archive="_save".$row_RsArchiv['NumArchive'];		
//***********************************************************************************************************************************		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsImprime = sprintf("SELECT DISTINCT gic_ID, nom_classe, nom_matiere, cdt_classe$num_archive.ID_classe,cdt_matiere$num_archive.ID_matiere FROM cdt_agenda$num_archive, cdt_classe$num_archive, cdt_matiere$num_archive WHERE prof_ID=%u AND cdt_classe$num_archive.ID_classe = cdt_agenda$num_archive.classe_ID AND cdt_matiere$num_archive.ID_matiere = cdt_agenda$num_archive.matiere_ID ORDER BY gic_ID, cdt_classe$num_archive.nom_classe, cdt_matiere$num_archive.nom_matiere ", $profchoix_RsImprime);
		$RsImprime = mysqli_query($conn_cahier_de_texte, $query_RsImprime) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsImprime = mysqli_fetch_assoc($RsImprime);
		$totalRows_RsImprime = mysqli_num_rows($RsImprime);
		
		  if ($totalRows_RsImprime!=0) {?>
		<p>&nbsp;</p>
		<p><strong>Mes archives -  <?php echo $row_RsArchiv['NomArchive']; ?></strong></p>
		<table  width="80%" align="center" cellpadding="5" cellspacing="1" class="Style555">
     <?php 
	 $last_gic_ID=0;
	 do { 
	 ?>
  <tr> 
 <?php 
	  //Regroupements
			if ($row_RsImprime['gic_ID']==0){?>
<td valign="bottom" bgcolor="#FFFFFF"><?php echo $row_RsImprime['nom_classe']; ?>&nbsp;</td>
                  <td valign="bottom" bgcolor="#FFFFFF"><?php echo $row_RsImprime['nom_matiere']; ?></td>
				  
      <td valign="bottom" bgcolor="#FFFFFF" ><a href="../lire.php?classe_ID=<?php echo $row_RsImprime['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprime['ID_matiere'];?>&gic_ID=<?php echo $row_RsImprime['gic_ID'];?>&ordre=down&archivID=<?php echo $row_RsArchiv['NumArchive'];?>" ><img src="../images/print.gif"  width="18" height="18"> Standard </a></td>
      <td valign="bottom" bgcolor="#FFFFFF" ><a href="../lire.php?classe_ID=<?php echo $row_RsImprime['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprime['ID_matiere'];?>&gic_ID=<?php echo $row_RsImprime['gic_ID'];?>&ordre=down&annot&archivID=<?php echo $row_RsArchiv['NumArchive'];?>" ><img src="../images/print.gif"  width="18" height="18"> Avec annotations perso. </a></td>    
    </tr>			  
				  
				  <?php
				
				}
				else
				{
			//presence de regroupement dans la matiere et la classe
			
if ($row_RsImprime['gic_ID']<>$last_gic_ID){ 
			// Rechercher le nom du regroupement
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsG =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses$num_archive WHERE  cdt_groupe_interclasses$num_archive.ID_gic = %u ",$row_RsImprime['gic_ID']);
$RsG = mysqli_query($conn_cahier_de_texte, $query_RsG) or die(mysqli_error($conn_cahier_de_texte));
$row_RsG = mysqli_fetch_assoc($RsG);?>

<td valign="bottom" bgcolor="#FFFFFF"><?php echo '(R) '.$row_RsG['nom_gic'];
$last_gic_ID=$row_RsImprime['gic_ID'];
 ?>&nbsp;</td>
	<td valign="bottom" bgcolor="#FFFFFF"><?php echo $row_RsImprime['nom_matiere']; ?></td>
      <td valign="bottom" bgcolor="#FFFFFF" ><a href="../lire.php?classe_ID=<?php echo $row_RsImprime['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprime['ID_matiere'];?>&gic_ID=<?php echo $row_RsImprime['gic_ID'];?>&regroupement=<?php echo $row_RsG['nom_gic'];?>&ordre=down&archivID=<?php echo $row_RsArchiv['NumArchive'];?>" ><img src="../images/print.gif"  width="18" height="18"> Standard </a></td>
      <td valign="bottom" bgcolor="#FFFFFF" ><a href="../lire.php?classe_ID=<?php echo $row_RsImprime['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprime['ID_matiere'];?>&gic_ID=<?php echo $row_RsImprime['gic_ID'];?>&regroupement=<?php echo $row_RsG['nom_gic'];?>&ordre=down&annot&archivID=<?php echo $row_RsArchiv['NumArchive'];?>" ><img src="../images/print.gif"  width="18" height="18"> Avec annotations perso. </a></td>    
    </tr>
	<?php
		
mysqli_free_result($RsG);
			}
			}
} while ($row_RsImprime = mysqli_fetch_assoc($RsImprime)); ?>
  </table>		
<?php		
//***********************************************************************************************************************************	
};
mysqli_free_result($RsImprime);
	
	}	while ($row_RsArchiv = mysqli_fetch_assoc($RsArchiv)); 
	
};
  
  
  ;?>


  <p>&nbsp;</p>
  <p align="center">
  <a href="index.php">Me d&eacute;connecter</a></p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>


