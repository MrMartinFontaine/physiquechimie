<?php
//******************************************************
//SECONDE PARTIE - AFFICHAGE DU RESTE DU CAHIER DE TEXTES
//******************************************************


if (isset($_GET['ecart'])){	
	header("Content-Type:text/plain; charset=iso-8859-1");

	$code_date=$_GET['code_date'];
	$ecart=$_GET['ecart'];
	session_start();
	require_once('../inc/functions_inc.php');	
	$_SESSION['ecart_realise']=$_GET['ecart'];//conserver le choix pour d'autres saisies

	echo '<link href="../styles/style_default.css" rel="stylesheet" type="text/css">';
	if ($_GET['ordre_chrono']==1){$ordre=' ASC';}else{	$ordre=' DESC';};
} else {$ecart=30;$ordre= ' DESC';}; //moins 15 jours par defaut

require_once('../Connections/conn_cahier_de_texte.php');
//determination de l'ID de la premiere classe d'un regroupement
if ((isset($_GET['classe_ID']))&&($_GET['classe_ID']==0)&&(isset($_GET['gic_ID']))) {
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rsgic_classe_ID_default =sprintf("SELECT classe_ID FROM cdt_groupe_interclasses_classe WHERE gic_ID = %u LIMIT 1",$_GET['gic_ID']);
	$Rsgic_classe_ID_default = mysqli_query($conn_cahier_de_texte, $query_Rsgic_classe_ID_default) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsgic_classe_ID_default = mysqli_fetch_assoc($Rsgic_classe_ID_default);
}; 

$refprof_Rslisteactivite = "0";
if (isset($_SESSION['ID_prof'])) {
	$refprof_Rslisteactivite = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
}
$classe_Rslisteactivite = "0";
if (isset($_GET['classe_ID'])) {
	$classe_Rslisteactivite = (get_magic_quotes_gpc()) ? $_GET['classe_ID'] : addslashes($_GET['classe_ID']);
	if ($_GET['classe_ID']==0){$classe_Rslisteactivite=$row_Rsgic_classe_ID_default['classe_ID'];};
};
$gic_Rslisteactivite = "0";
if (isset($_GET['gic_ID'])) {
	$gic_Rslisteactivite = (get_magic_quotes_gpc()) ? $_GET['gic_ID'] : addslashes($_GET['gic_ID']);
}; 

$matiere_Rslisteactivite = "0";
if (isset($_GET['matiere_ID'])) {
	$matiere_Rslisteactivite = (get_magic_quotes_gpc()) ? $_GET['matiere_ID'] : addslashes($_GET['matiere_ID']);
}

//est-ce un remplacement ?
if ((isset($_SESSION['id_etat']))&&(isset($_SESSION['id_remplace']))&&($_SESSION['id_etat']==2)){$sql_remplace=' OR prof_ID='.$_SESSION['id_remplace'];} else {$sql_remplace='';}

if ($ecart!=-1) {
	$codedate_Rslisteactivite = "0";
	if (isset($_GET['code_date'])) {
		$codedate_Rslisteactivite = (get_magic_quotes_gpc()) ? $_GET['code_date'] : addslashes($_GET['code_date']);
	}
	
	// Recherche de la date (et code_date a J- ecart)
	$codedate_j_ecart=date("Ymd",mktime(0,0,0,date("m"),date("d")-$ecart,date("Y")))."0";

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rslisteactivite = sprintf("(SELECT * FROM cdt_agenda WHERE classe_ID=%u AND gic_ID=%u AND matiere_ID=%u AND prof_ID=%u %s AND code_date>='%s' OR (classe_ID =0 AND theme_activ<>'Remplacement'))", $classe_Rslisteactivite,$gic_Rslisteactivite,$matiere_Rslisteactivite,$refprof_Rslisteactivite,$sql_remplace, $codedate_j_ecart);
	$query_Rslisteactivite .= sprintf(" UNION (SELECT * FROM cdt_agenda WHERE classe_ID=%u AND gic_ID=%u AND matiere_ID=%u AND partage='O' AND code_date>='%s') 
		UNION 
(SELECT * FROM cdt_agenda WHERE prof_ID=%u AND theme_activ='Remplacement')
	
	ORDER BY SUBSTRING(code_date,1,8) %s ,heure_debut %s", $classe_Rslisteactivite,$gic_Rslisteactivite,$matiere_Rslisteactivite,$codedate_j_ecart,$_SESSION['ID_prof'],$ordre,$ordre);

	$Rslisteactivite = mysqli_query($conn_cahier_de_texte, $query_Rslisteactivite) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite);
	$totalRows_Rslisteactivite = mysqli_num_rows($Rslisteactivite);

}
else
{
	$codedate_j_ecart=0;
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rslisteactivite = sprintf("(SELECT * FROM cdt_agenda WHERE classe_ID=%u AND gic_ID=%u AND matiere_ID=%u AND prof_ID=%u %s OR (classe_ID =0 AND theme_activ<>'Remplacement'))", $classe_Rslisteactivite,$gic_Rslisteactivite,$matiere_Rslisteactivite,$refprof_Rslisteactivite,$sql_remplace);
	$query_Rslisteactivite .= sprintf(" UNION (SELECT * FROM cdt_agenda WHERE classe_ID=%u AND gic_ID=%u AND matiere_ID=%u AND partage='O') 
	UNION 
(SELECT * FROM cdt_agenda WHERE prof_ID=%u AND theme_activ='Remplacement')
	ORDER BY SUBSTRING(code_date,1,8) %s ,heure_debut %s", $classe_Rslisteactivite,$gic_Rslisteactivite,$matiere_Rslisteactivite,$_SESSION['ID_prof'],$ordre,$ordre);

	$Rslisteactivite = mysqli_query($conn_cahier_de_texte, $query_Rslisteactivite) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite);
	$totalRows_Rslisteactivite = mysqli_num_rows($Rslisteactivite);
	
}

if ($totalRows_Rslisteactivite>0){
	
	do { 
		if($row_Rslisteactivite['classe_ID']<>0){  
			
			//***************************************
			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$sup_ch='';
			//Devoir
			if (isset($_GET['ds_prog'])){$sup_ch="AND substring(code_date,9,1)=0 AND substring(t_code_date,3,1)='/'";};
			//Heure sup
			if ((!isset($_GET['ds_prog']))&&(substr($_GET['code_date'],8,1)==0)){$sup_ch="AND substring(code_date,9,1)=0 AND substring(t_code_date,3,1)='-'";};
			//Heure normale
			
			if ($_GET['classe_ID']==0){$classe_Rslisteactivite=$row_Rsgic_classe_ID_default['classe_ID'];} else {$classe_Rslisteactivite=$_GET['classe_ID'];}
			
			if($row_Rslisteactivite['partage']<>'O'){ 
				$query_Rs_Travail2 = sprintf("SELECT * FROM cdt_travail WHERE agenda_ID=%u AND code_date='%s' AND prof_ID=%u AND matiere_ID=%u AND classe_ID=%u %s ORDER BY code_date", $row_Rslisteactivite['ID_agenda'], $row_Rslisteactivite['code_date'],$_SESSION['ID_prof'],$_GET['matiere_ID'],$classe_Rslisteactivite,$sup_ch);
			} else {
				$query_Rs_Travail2 = sprintf("SELECT * FROM cdt_travail WHERE agenda_ID=%u AND code_date='%s' AND matiere_ID=%u AND classe_ID=%u %s ORDER BY code_date", $row_Rslisteactivite['ID_agenda'], $row_Rslisteactivite['code_date'],$_GET['matiere_ID'],$classe_Rslisteactivite,$sup_ch);	
			};
			$Rs_Travail2 = mysqli_query($conn_cahier_de_texte, $query_Rs_Travail2) or die(mysqli_error($conn_cahier_de_texte));
			$row_Rs_Travail2 = mysqli_fetch_assoc($Rs_Travail2);
			
			$date_a_faire[1]='';$date_a_faire[2]='';$date_a_faire[3]='';
			$travail[1]='';$travail[2]='';$travail[3]='';
			$t_groupe[1]='';$t_groupe[2]='';$t_groupe[3]='';
			$eval[1]='';$eval[2]='';$eval[3]='';
			do {  
				$travail[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['travail'];
				$date_a_faire[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['t_code_date'];
				$t_groupe[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['t_groupe'];
				$eval[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['eval'];
			} while ($row_Rs_Travail2 = mysqli_fetch_assoc($Rs_Travail2));
			
			//******************************************
			
			?>
			<br/>
			<table width="831px"  border="0" cellpadding="0" cellspacing="0" class="Style4">
			<tr>
			<?php 
			$lien_modif = 'ecrire.php?saisie=1&';
			if ($row_Rslisteactivite['type_activ']<>'ds_prog') { 
				if ($row_Rslisteactivite['partage']=='O') { //Heure partagee
					$lien_modif.='share=O&';
				};
				if (isset($_GET['edtID'])) { // Heure classique
					$lien_modif.= 'edtID='.$_GET['edtID'].'&';
				};
			} else { // Heure de DS programme hors heure de cours
				$lien_modif.= 'ds_prog&'; 
			};
			
			$lien_modif .= 'nom_classe='.$_GET['nom_classe'].'&classe_ID='.$_GET['classe_ID'].'&gic_ID='.$row_Rslisteactivite['gic_ID'].'&nom_matiere='.$_GET['nom_matiere'].'&groupe='.$row_Rslisteactivite['groupe'].'&matiere_ID='.$row_Rslisteactivite['matiere_ID'].'&semaine='.$row_Rslisteactivite['semaine'].'&jour_pointe='.$row_Rslisteactivite['jour_pointe'].'&heure='.$row_Rslisteactivite['heure'].'&heure_debut='.$row_Rslisteactivite['heure_debut'].'&heure_fin='.$row_Rslisteactivite['heure_fin'].'&current_day_name='.substr($row_Rslisteactivite['jour_pointe'], 0, strpos($row_Rslisteactivite['jour_pointe'], ' ')).'&code_date='.$row_Rslisteactivite['code_date'].'&duree='.$row_Rslisteactivite['duree'];
			$lien_supprime = '?nom_classe='.$_GET['nom_classe'].'&classe_ID='.$_GET['classe_ID'].'&gic_ID='.$row_Rslisteactivite['gic_ID'].'&nom_matiere='.$_GET['nom_matiere'].'&groupe='.$row_Rslisteactivite['groupe'].'&matiere_ID='.$row_Rslisteactivite['matiere_ID'].'&semaine='.$row_Rslisteactivite['semaine'].'&jour_pointe='.$row_Rslisteactivite['jour_pointe'].'&heure='.$row_Rslisteactivite['heure'].'&heure_debut='.$row_Rslisteactivite['heure_debut'].'&heure_fin='.$row_Rslisteactivite['heure_fin'].'&current_day_name='.substr($row_Rslisteactivite['jour_pointe'], 0, strpos($row_Rslisteactivite['jour_pointe'], ' ')).'&code_date='.$row_Rslisteactivite['code_date'].'&duree='.$row_Rslisteactivite['duree'];  
			?>
			<td width="20%" height="20" class="Style666" style="color: #FFFFFF; "><a style="color: #FFFFFF" href="<?php echo $lien_modif?>" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
			<?php 
			
			echo substr($row_Rslisteactivite['jour_pointe'],0,strlen($row_Rslisteactivite['jour_pointe'])-4);
			
			?>
			</a> &nbsp;<?php visa_edition_possible($row_Rslisteactivite['code_date']);
			
			if ($row_Rslisteactivite['semaine']<>'A et B'){ echo '('. $row_Rslisteactivite['semaine'].')';}; ?>
			<td height="20" class="Style6"><table width="100%"  border="0" cellpadding="0" cellspacing="0">
			<tr>
			<th class="Style66" scope="col"><div align="left"><?php echo $row_Rslisteactivite['theme_activ']; ?></div></th>
			
			<th width="50" class="Style66" scope="col">
			
			<?php if ((visa_edition_possible(substr($row_Rslisteactivite['code_date'],0,8)))&&($row_Rslisteactivite['prof_ID']==$_SESSION['ID_prof'])){
		?>
			<a href="<?php echo $lien_modif ?>" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>> <img src="../images/button_edit.png" alt="Modifier la fiche" title="Modifier la fiche" border="0"></a><?php } else { 
			if ($row_Rslisteactivite['prof_ID']==$_SESSION['ID_prof']){
			?><img src="../images/button_no_edit.png" alt="Modification de la fiche impossible" title="Modification de la fiche impossible" border="0" onclick='edition_impossible()'>
			
			<?php 
			}
			};
			
			if ($row_Rslisteactivite['prof_ID']==$_SESSION['ID_prof']){ //ce n'est pas un remplacement
			?>
			&nbsp;<a href="agenda_supprime.php<?php echo $lien_supprime.'&ID_agenda='.$row_Rslisteactivite['ID_agenda'];?>" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>><img src="../images/ed_delete.gif" alt="Supprimer la fiche" title="Supprimer la fiche" width="11" height="13" border="0"></a>
			<?php
			}?>
			</th>
			</tr>
			</table></td>
			</tr>
			<tr>
			
			<td width="20%" valign="top" class="style9">
			
			<p><?php echo $row_Rslisteactivite['groupe']; ?> <br>
			<?php 
			echo $row_Rslisteactivite['heure_debut']; 
			if ($row_Rslisteactivite['heure_fin']<>''){echo '-'.$row_Rslisteactivite['heure_fin'];}
			if ($row_Rslisteactivite['duree']<>''){echo '- ('. $row_Rslisteactivite['duree'].')'; }
			
			?></p>
			<?php
			if ($row_Rslisteactivite['type_activ']<>'ds_prog'){ 
				echo '<p style="color:'.$row_Rslisteactivite['couleur_activ'].'"><strong>';
				// Recherche si l'heure est partagee ou non 
				if ($row_Rslisteactivite['partage']=='O') {
					echo '<img src="../images/partage.gif" title="Cette plage est partag&eacute;e avec au moins un autre enseignant.">&nbsp;';
				};
				echo $row_Rslisteactivite['type_activ'].'</strong></p>';
				if (substr($row_Rslisteactivite['code_date'],8,1)==0) {echo '<p style="color:#FF0000"><b>Heure Sup.</b></p>';};
			}
			else {echo '<p style="color:#FF0000"><b>'.$_SESSION['libelle_devoir'].'</b></p>';}; 
			?>
			
			<p>
			<?php
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
			
			?>
			<table>
			<tr>
			<td class="Style14"><?php if ($totalRows_RsFichiers<>0){echo'Documents';}?>
			</td>
			</tr>
			<?php
			do { 
				?>
				<tr>
				<td class="Style14"><a href="../fichiers_joints/<?php echo $row_RsFichiers['nom_fichier'];  ?>" target="_blank">
				<?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_RsFichiers['nom_fichier']); ?>
				<strong><?php echo $nom_f ;  ?></strong></a> </td>				
				</tr>
				
				<?php
			} while ($row_RsFichiers = mysqli_fetch_assoc($RsFichiers)); 
			mysqli_free_result($RsFichiers);
			?>
			</table>
			</p>
			
			<td class="Style10"><?php echo ($row_Rslisteactivite['activite']); ?><br />
			<span class="Style699">
			<br />
			<?php
			for ($taf=1;$taf<4;$taf++) {
				if ($date_a_faire[$taf]<>''){
					echo '<u>'.$t_groupe[$taf].' pour le <b>'.jour_semaine($date_a_faire[$taf]).' '.$date_a_faire[$taf].'</b> :</u>';
					
					//affichage fichiers travail joints
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$sql_f="SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND type ='Travail' AND t_code_date='".$date_a_faire[$taf]."' AND ind_position=$taf ORDER BY nom_fichier";
					$query_Rs_fichiers_joints_form = $sql_f;
					$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
					$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
					$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
					
					if ($totalRows_Rs_fichiers_joints_form<>0)
					{
						do { ?>
							<a href="fichier_supprime.php<?php echo $lien_supprime.'&ID_fichiers='.$row_Rs_fichiers_joints_form['ID_fichiers'].'&nom_fichier='.$row_Rs_fichiers_joints_form['nom_fichier']?>"> <img src="../images/ed_delete.gif" alt="Supprimer le fichier" title="Supprimer le fichier" border="0">&nbsp;</a> <a href="../fichiers_joints/<?php echo $row_Rs_fichiers_joints_form['nom_fichier'];  ?> " target="_blank">
							<?php $exp = '/^[0-9]+_/'; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']); ?>
							<strong><?php echo $nom_f ;  ?></strong></a>
							<?php
						} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
					}
					mysqli_free_result($Rs_fichiers_joints_form);
					//fin affichage des fichiers travail joints
					echo '<br />';
					if (!(strcmp($eval[$taf],'O'))){echo "<span style='color:red;'><strong>Evaluation : </strong></span>";};
					echo $travail[$taf].'<br />';
				}
			};	
			?>
			</span>
			<?php if ($row_Rslisteactivite['rq']<>''){echo '<br /><strong>Annotations personnelles :</strong><br/>'.$row_Rslisteactivite['rq'];}; ?></td>
			</tr>
			</table>
		<?php  }
		else {
		    //remplacement
		    if ($row_Rslisteactivite['theme_activ']=='Remplacement'){ ?>
				<br/>
				<table width="831px"  border="0" cellspacing="0" class="Style4">
				<tr>
				<td class="vacances" ><?php echo $row_Rslisteactivite['activite']; ?></td>
				</tr>
				</table>
				<?php
			}
			// affichage des vacances
			else
			{
				$xd=date("Ymd").'0';
				if (( ($row_Rslisteactivite['code_date']>$codedate_j_ecart) && ($row_Rslisteactivite['code_date']<=$xd)))
				{
					
					?>
					<br/>
					<table width="831px"  border="0" cellspacing="0" class="Style4">
					<tr>
					<td width="20%" class="vacances" ><?php echo $row_Rslisteactivite['heure_debut'].' - '.$row_Rslisteactivite['heure_fin']; ?> </td>
					<td class="vacances" ><?php echo $row_Rslisteactivite['theme_activ']; ?></td>
					</tr>
					</table>
					<?php
				}
			}
		};
	} while ($row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite)); 
	
	mysqli_free_result($Rslisteactivite); 
	
}
?>
