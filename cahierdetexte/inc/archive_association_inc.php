<?php
if ($row_RsImprime['gic_ID']>0){$ID_classe=0;} else {$ID_classe=$row_RsImprime['ID_classe'];};
$query_RsAssoc =sprintf("SELECT * FROM cdt_archive_association WHERE prof_ID=%u AND classe_ID=%u AND gic_ID=%u AND matiere_ID=%u",$_SESSION['ID_prof'],$ID_classe,$row_RsImprime['gic_ID'],$row_RsImprime['ID_matiere']);             
$RsAssoc = mysqli_query($conn_cahier_de_texte, $query_RsAssoc) or die(mysqli_error($conn_cahier_de_texte));
$row_RsAssoc = mysqli_fetch_assoc($RsAssoc);
$totalRows_RsAssoc = mysqli_num_rows($RsAssoc);  

if ($totalRows_RsAssoc>0){
	$IDArchive="_save".$row_RsAssoc['NumArchive'];
	$query_Rs1 =sprintf("SELECT NomArchive FROM cdt_archive WHERE NumArchive=%u",$row_RsAssoc['NumArchive']);              
	$Rs1 = mysqli_query($conn_cahier_de_texte, $query_Rs1) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rs1 = mysqli_fetch_assoc($Rs1);
	
	$query_Rs2 =sprintf("SELECT nom_classe FROM cdt_classe$IDArchive WHERE ID_classe=%u",$row_RsAssoc['classe_ID_archive']);         
	$Rs2 = mysqli_query($conn_cahier_de_texte, $query_Rs2) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rs2 = mysqli_fetch_assoc($Rs2); 
	
	$query_Rs3 =sprintf("SELECT nom_matiere FROM cdt_matiere$IDArchive WHERE ID_matiere=%u",$row_RsAssoc['matiere_ID_archive']);             
	$Rs3 = mysqli_query($conn_cahier_de_texte, $query_Rs3) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rs3 = mysqli_fetch_assoc($Rs3); 
	
	$query_Rs4 =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses$IDArchive WHERE ID_gic=%u",$row_RsAssoc['gic_ID_archive']);             
	$Rs4 = mysqli_query($conn_cahier_de_texte, $query_Rs4) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rs4 = mysqli_fetch_assoc($Rs4);
	$totalRows_Rs4 = mysqli_num_rows($Rs4);
	
	echo '<span style="color:#6A2300; font-family:verdana; font-size:10px;"> <img src="../images/link.png" width="16" height="16">&nbsp;&nbsp;'.$row_Rs1['NomArchive'] .' - '.$row_Rs2['nom_classe'] .' - '.$row_Rs3['nom_matiere'];
	if ($totalRows_Rs4>0) { echo ' - '.$row_Rs4['nom_gic']; };
	echo '</span>';
	?>
	<img src="../images/ed_delete.gif" alt="Supprimer ce raccourci" title="Supprimer ce raccourci" width="10" height="10" onClick="MM_goToURL('window','imprimer_menu.php?ID_supprime=<?php echo $row_RsAssoc['ID_assoc']; ?>');return document.MM_returnValue">
	
	<?php
	mysqli_free_result($Rs1);
	mysqli_free_result($Rs2);
	mysqli_free_result($Rs3);
	mysqli_free_result($Rs4);
}
mysqli_free_result($RsAssoc);    
?>
