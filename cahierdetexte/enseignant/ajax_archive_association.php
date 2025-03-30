<?php   

header("Content-Type:text/plain; charset=iso-8859-1");
session_start();
require_once('../Connections/conn_cahier_de_texte.php');        
if((isset($_POST["NumArch"]))&&($_POST["NumArch"]<>-1)){
	$profchoix = "0";
	if (isset($_SESSION['ID_prof'])) {
		$profchoix = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
		
	}
	$num_archive="_save".$_POST["NumArch"]; 
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArch = sprintf("SELECT DISTINCT gic_ID, nom_classe, nom_matiere, cdt_classe$num_archive.ID_classe,cdt_matiere$num_archive.ID_matiere FROM cdt_agenda$num_archive, cdt_classe$num_archive, cdt_matiere$num_archive WHERE prof_ID=%u AND cdt_classe$num_archive.ID_classe = cdt_agenda$num_archive.classe_ID AND cdt_matiere$num_archive.ID_matiere = cdt_agenda$num_archive.matiere_ID ORDER BY gic_ID, cdt_classe$num_archive.nom_classe, cdt_matiere$num_archive.nom_matiere ", $profchoix);  
	$RsArch = mysqli_query($conn_cahier_de_texte, $query_RsArch) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsArch = mysqli_fetch_assoc($RsArch);
	$totalRows_RsArch = mysqli_num_rows($RsArch);
	?>
	<div style="float:left">
	<select name="archive_detail">
	<option value='-1'>S&eacute;lectionner la classe et la mati&egrave;re</option>
	<?php 
	
	//***********************************************************************************************************************************           

	if ($totalRows_RsArch!=0) {
		
		$last_gic_ID=0;
		do { 
			?>
			
			<?php 
			//Regroupements
                        if ($row_RsArch['gic_ID']==0){?>
                        	<option value="<?php echo $row_RsArch['ID_classe'].'-'.$row_RsArch['gic_ID'].'-'.$row_RsArch['ID_matiere'];?>"><?php echo  $row_RsArch['nom_classe'].' - '.$row_RsArch['nom_matiere']; ?></option>
                        	<?php
                                
                        }
                        else
                        {
                        	//presence de regroupement dans la matiere et la classe
                        	
                        	if ($row_RsArch['gic_ID']<>$last_gic_ID){ 
                        		// Rechercher le nom du regroupement
                        		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        		$query_RsG =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses$num_archive WHERE ID_gic=%u",$row_RsArch['gic_ID']);
                        		$RsG = mysqli_query($conn_cahier_de_texte, $query_RsG) or die(mysqli_error($conn_cahier_de_texte));
                        		$row_RsG = mysqli_fetch_assoc($RsG);?>
                        		
                        		<option value="<?php echo $row_RsArch['ID_classe'].'-'.$row_RsArch['gic_ID'].'-'.$row_RsArch['ID_matiere'] ;?>"><?php echo '(R) '.$row_RsG['nom_gic']. '  '.$row_RsArch['nom_matiere'];
                        		$last_gic_ID=$row_RsArch['gic_ID'];
                        		?></option>
                        		<?php
                        		
                        		mysqli_free_result($RsG);
                        	}
                        }
                        
                } while ($row_RsArch = mysqli_fetch_assoc($RsArch));
                mysqli_free_result($RsArch);
        }?>
        
        </select></div>
        <?php
}
?>
<div style="float:right">
<input type="submit" name="Submit" value="Cr&eacute;er"></div>
