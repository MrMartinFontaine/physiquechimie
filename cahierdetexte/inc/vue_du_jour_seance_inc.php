<?php	
$Rslisteactivite = mysqli_query($conn_cahier_de_texte, $query_Rslisteactivite) or die(mysqli_error($conn_cahier_de_texte));
$row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite);
$totalRows_Rslisteactivite = mysqli_num_rows($Rslisteactivite);
//echo $query_Rslisteactivite;
//echo $totalRows_Rslisteactivite;
if ($totalRows_Rslisteactivite>0){
do {
echo '<strong>'.$row_Rslisteactivite['theme_activ']. '</strong><br />';
echo $row_Rslisteactivite['activite']. '<br />';

// affichage fichiers joints
				
				$refagenda_RsFichiers = "0";
				if (isset($row_Rslisteactivite['ID_agenda'])) {
				$refagenda_RsFichiers = (get_magic_quotes_gpc()) ? 
				$row_Rslisteactivite['ID_agenda'] : addslashes($row_Rslisteactivite['ID_agenda']);
				}
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsFichiers = sprintf("SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=%u AND type ='Cours'", $refagenda_RsFichiers);
				$RsFichiers = mysqli_query($conn_cahier_de_texte, $query_RsFichiers) or die(mysqli_error($conn_cahier_de_texte));
				$row_RsFichiers = mysqli_fetch_assoc($RsFichiers);
				$totalRows_RsFichiers = mysqli_num_rows($RsFichiers);
				
				
				if ($totalRows_RsFichiers<>0){echo'<br/><strong>Document(s)</strong> : ';
				do { 
				?>
       
            <a href="../fichiers_joints/<?php echo $row_RsFichiers['nom_fichier'];  ?>" target="_blank">
            <?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_RsFichiers['nom_fichier']); ?>
            <strong><?php echo $nom_f ;  ?></strong></strong></a> 
        
        <?php
				  } while ($row_RsFichiers = mysqli_fetch_assoc($RsFichiers)); echo '<br/>';
				 mysqli_free_result($RsFichiers);}
				if ($row_Rslisteactivite['rq']<>''){echo '<br /><strong>Annotations personnelles</strong><br />'.$row_Rslisteactivite['rq'].'<br />';}
				
   } while ($row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite)); 

}
	
?>
