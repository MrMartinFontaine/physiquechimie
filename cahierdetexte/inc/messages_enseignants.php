<?php
//messages entre professeurs si option activee (parametre administrateur)

// Ne pas afficher ici les messages de la Vie scolaire - Webmestre ou Responsable Etablissement
$query_Rsprof_all_diffusion ="SELECT * FROM cdt_message_contenu,cdt_prof WHERE cdt_message_contenu.dest_ID=2 AND cdt_message_contenu.prof_ID=cdt_prof.ID_prof AND cdt_prof.droits=2 AND cdt_message_contenu.pp_classe_ID=0 AND cdt_message_contenu.online='O' ORDER BY date_envoi,ID_message";
$Rsprof_all_diffusion = mysqli_query($conn_cahier_de_texte, $query_Rsprof_all_diffusion) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsprof_all_diffusion = mysqli_fetch_assoc($Rsprof_all_diffusion);
$totalRows_Rsprof_all_diffusion = mysqli_num_rows($Rsprof_all_diffusion);

if ($totalRows_Rsprof_all_diffusion>0) {
	?><br />
	<table width="600" height="172" border="0" cellpadding="0" cellspacing="0" class="bordure">
	<tr>
	<td height="20"  class="Style6">Messages des <?php if($totalRows_Rsprof_all_diffusion>0){echo 'autres ';};?>enseignants</td>
	<td class="Style6"><div align="right">
	<?php if ((isset($_SESSION['afficher_messages']))&&($_SESSION['afficher_messages']=='O')){?> 
		<a href="ecrire.php?date=<?php echo date('Ymd');?>&masquer_messages"><font color="#FFFFFF">X</font>&nbsp;</a> 
	<?php };?>
	</div>	</td>
	</tr>
	<tr>
	<td valign="top" class="Style15">
	<br />
	<?php 
	
	$nb=0;
	do { 
		
		//fichiers joints au message des professeurs 
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE message_ID=".$row_Rsprof_all_diffusion['ID_message'];
			$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
			$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
			$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
		
		echo '<p><b>';
		if($row_Rsprof_all_diffusion['identite']==''){echo $row_Rsprof_all_diffusion['nom_prof'];}else {echo $row_Rsprof_all_diffusion['identite'];};
		echo '</b>';
		echo ' - '.substr($row_Rsprof_all_diffusion['date_envoi'],8,2).'/'.substr($row_Rsprof_all_diffusion['date_envoi'],5,2).'/'.substr($row_Rsprof_all_diffusion['date_envoi'],0,4);
		
		if (($row_Rsprof_all_diffusion['prof_ID']<>$_SESSION['ID_prof'])&&($row_Rsprof_all_diffusion['email']<>'')){ ?> <a href="mailto:<?php echo $row_Rsprof_all_diffusion['email'];?>"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant" title="Contacter l'enseignant" /></a><?php ;};
		if ($row_Rsprof_all_diffusion['prof_ID']==$_SESSION['ID_prof']) { ?>
		<img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','message_modif.php?ID_message=<?php echo $row_Rsprof_all_diffusion['ID_message'];?>&dest_profs=0');return document.MM_returnValue">&nbsp;
                                
		<img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick= "return confirmation('<?php if ($totalRows_Rs_fichiers_joints_form>0){
                                        if ($totalRows_Rs_fichiers_joints_form==1){ echo ' et sa pi&egrave;ce jointe attach&eacute;e';} else {echo ' et ses '.$totalRows_Rs_fichiers_joints_form.' pi&egrave;ces jointes attach&eacute;es';}
			};?>','<?php echo $row_Rsprof_all_diffusion['ID_message']?>')" >
		<?php
		};
		echo '</p>';
		if ((isset($_SESSION['afficher_messages']))&&($_SESSION['afficher_messages']=='O')){
			echo $row_Rsprof_all_diffusion['message'];
			
			if ($totalRows_Rs_fichiers_joints_form>0){
				if ($totalRows_Rs_fichiers_joints_form>1){echo '<blockquote><p>Documents joints : <br /> ';} else {echo '<blockquote><p>Document joint : ';};
				do {
					$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
					echo '<img src="../images/attach.png" alt="fichier_joint"><a href="../fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a><br />';
                                } while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
                        echo '</p></blockquote>';};
                        $nb=$nb+1;if($nb < $totalRows_Rsprof_all_diffusion){ echo '<p class="bas_ligne"></p>';};          
                        mysqli_free_result($Rs_fichiers_joints_form);
                };
        } while ($row_Rsprof_all_diffusion = mysqli_fetch_assoc($Rsprof_all_diffusion)); 
        mysqli_free_result($Rsprof_all_diffusion);
        ?><br />
        </td>
        <?php if ((isset($_SESSION['afficher_messages']))&&($_SESSION['afficher_messages']=='N')){?> 
		<td width="100">
		<div align="right">
		<a href="ecrire.php?date=<?php echo date('Ymd');?>&afficher_messages"><img src="../images/post-it2.jpg" alt="post-it" border="0" ></a>&nbsp;<br />
		<div align="center" class="Style2">
		<a href="ecrire.php?date=<?php echo date('Ymd');?>&afficher_messages">Afficher  </a>
		</div>
	</td><?php };?>
        </tr>
        </table>
<?php };
?>
