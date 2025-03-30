 <?php
 
 //pour chaque classe 
		
		
		//recherche de devoirs planifies
		 // att  code_date et heure : on est pas dans ele_absent !
				// 	GROUP BY cdt_agenda.heure
		$query_RsDs = sprintf(" 
		SELECT * FROM cdt_agenda, cdt_prof
			WHERE substring( code_date, 9, 1 ) =0
			AND substring( code_date, 1, 8 ) =%s
			AND cdt_prof.ID_prof = cdt_agenda.prof_ID
			AND classe_ID=%u

			ORDER BY heure, heure_debut",
			substr($datetoday,0,8),$row_Abs_ou_Ret_cl['classe_ID']);
	
		$RsDs = mysqli_query( $conn_cahier_de_texte,$query_RsDs) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsDs = mysqli_fetch_assoc($RsDs);
		$Nb__RsDs = mysqli_num_rows($RsDs);
		
		//On recherche les eleves absents au cours
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Abs_ou_Ret = sprintf("SELECT DISTINCT eleve_ID,nom_ele,prenom_ele,nom_classe 
FROM ele_absent,cdt_prof,ele_liste,cdt_classe 
			WHERE ( (absent='Y') OR ( retard_V='Y') OR ( retard_Nv='Y' ))
			AND  ele_liste.classe_ele COLLATE latin1_swedish_ci = cdt_classe.code_classe COLLATE latin1_swedish_ci 
			AND cdt_prof.ID_prof=ele_absent.prof_ID 
			AND ele_absent.eleve_ID= ele_liste.ID_ele 
			AND  ele_absent.date LIKE '%s%%' 
			AND ele_absent.classe_ID=%u 
			AND ele_absent.eleve_ID > 0
			ORDER BY classe,nom_classe,nom_ele", 
			$datetoday,$row_Abs_ou_Ret_cl['classe_ID']);
		
		$Abs_ou_Ret = mysqli_query( $conn_cahier_de_texte,$query_Abs_ou_Ret) or die(mysqli_error($conn_cahier_de_texte));
		$row_Abs_ou_Ret = mysqli_fetch_assoc($Abs_ou_Ret);
		$Nb__Abs_ou_Ret = mysqli_num_rows($Abs_ou_Ret);
		

		// entete bleue d'une  classe===========================================================================  
		
		?>
		<table border="0" align="center" width="100%" >
		<tr>
		<td class="Style6" width="100%"><div align="left"> 
		<?php  echo '&nbsp;';
		//if ($row_Abs_ou_Ret_cl['classe_ID']==0){echo 'Regroupements';}else {echo $row_Abs_ou_Ret_cl['classe'];};?>
		</div></td> 		</tr>
		</table>
		
		<table> 
		
		<?php
		
		
		// on recherche si une saisie a ete faite
		
		//recup de la semaine et gestion des semaines A et B
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsSemdate =  sprintf("SELECT * FROM cdt_semaine_ab WHERE cdt_semaine_ab.s_code_date<=%s ORDER BY `s_code_date` DESC LIMIT 1 ",$datetoday);
		$RsSemdate = mysqli_query( $conn_cahier_de_texte,$query_RsSemdate) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsSemdate= mysqli_fetch_assoc($RsSemdate);
		$semdate = $row_RsSemdate['semaine'] ;
		
		if ( $semdate == "A" ) {
			$semdate_exclusion = "B";	
		} else if ( $semdate == "B" ) {
			$semdate_exclusion = "A";	
		} else {
			$semdate_exclusion = NULL;
		}
		
		if (!is_null($semdate_exclusion) ) {
			$query_Rs_emploi = sprintf("SELECT heure, heure_debut, heure_fin, identite, groupe, gic_ID, prof_ID, edt_exist_fin 
			FROM cdt_emploi_du_temps,cdt_prof 
			WHERE ID_prof=cdt_emploi_du_temps.prof_ID 
			AND cdt_emploi_du_temps.classe_ID=%u 
			AND cdt_emploi_du_temps.jour_semaine='%s' AND '%s'<= edt_exist_fin 
			AND semaine!='%s'  
			ORDER BY cdt_emploi_du_temps.heure_debut", $row_Abs_ou_Ret_cl['classe_ID'],$jourtoday,date('Y-m-d'),$semdate_exclusion ); 
		} else {
			$query_Rs_emploi = sprintf("SELECT heure, heure_debut, heure_fin, identite, groupe, gic_ID, prof_ID, edt_exist_fin 
			FROM cdt_emploi_du_temps,cdt_prof 
			WHERE ID_prof=cdt_emploi_du_temps.prof_ID 
			AND cdt_emploi_du_temps.classe_ID=%u 
			AND cdt_emploi_du_temps.jour_semaine='%s' 
			AND '%s'<= edt_exist_fin 
			ORDER BY cdt_emploi_du_temps.heure_debut", $row_Abs_ou_Ret_cl['classe_ID'],$jourtoday,date('Y-m-d')); 
		};
		
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		
		$Rs_emploi = mysqli_query( $conn_cahier_de_texte,$query_Rs_emploi) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi);
		$Nb__Rs_emploi = mysqli_num_rows($Rs_emploi);
		$a=1;
		if ( $Nb__Rs_emploi >0 ){
			
			// debut de ligne classe ===========================================================================
			echo '<tr><td  class="tab_detail_gris" style=" width:150px; font-size:20px; padding:8px; padding-left: 15px;  font-weight:bold;">';
			if ($row_Abs_ou_Ret_cl['classe_ID']==0){echo 'Regroup.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';}else {echo $row_Abs_ou_Ret_cl['classe'];};
			echo '</td>';
			;
			do {
	//===================================================DECOMPTES +++++++++++++++++++++++++++++++++++++++++++++++++			
				// decompte  cas appel fait avec "aucun absent"
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_Rsabs = sprintf("SELECT * FROM ele_absent 
					WHERE 	classe_ID=%u
					AND date='%s'
					AND heure =%s
					AND  eleve_ID=0 
					AND prof_ID=%u	", 
					$row_Abs_ou_Ret_cl['classe_ID'],$datetoday,$row_Rs_emploi['heure'],$row_Rs_emploi['prof_ID']);
				$AucunAbs = mysqli_query( $conn_cahier_de_texte,$query_Rsabs) or die(mysqli_error($conn_cahier_de_texte));
				$row_AucunAbs = mysqli_fetch_assoc($AucunAbs);
				$nb_AucunAbs = mysqli_num_rows($AucunAbs); // si =1  case aucun absent cochée
				
				//Dans une classe ou l'appel est fait, nombre d'absents par heure de cours
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				
				//************** Absents sur une heure normale emploi du temps **************************
				$query_Rsnbele = sprintf("SELECT * FROM ele_absent WHERE  absent='Y'
				AND		date= '%s' 
				AND ele_absent.classe_ID=%u 
				AND ele_absent.eleve_ID > 0
				AND heure='%s' 
				AND prof_ID=%u ",
				$datetoday,$row_Abs_ou_Ret_cl['classe_ID'],$row_Rs_emploi['heure'],$row_Rs_emploi['prof_ID']);	
				$LesAbs = mysqli_query( $conn_cahier_de_texte,$query_Rsnbele) or die(mysqli_error($conn_cahier_de_texte));
				$row_LesAbs= mysqli_fetch_assoc($LesAbs);
				$Nb_LesAbs = mysqli_num_rows($LesAbs); // nb absents  declares
				
				//$couleur_pas_absent='#7FFFD4';$couleur_pas_appel='#CCCCCC';$couleur_absent='red';
				//============================= entete bilan d'une classe : decompte nb absents =====================================
				echo '<td  valign="top" style="width:100px"';
				
				if ( ($nb_AucunAbs > 0) && ( $Nb_LesAbs == 0 )) { echo ' bgcolor="'. $couleur_pas_absent  .'"'; }
				else if ( ($nb_AucunAbs == 0) && ( $Nb_LesAbs == 0 )) { echo ' bgcolor="'. $couleur_pas_appel .'"'; }
				else if ( $Nb_LesAbs >0) { echo ' bgcolor="'.  $couleur_absent .'"';}
				else { echo 'class="tab_detail_gris" bgcolor="#DFDFDF" style="marging-left:4px;" ';};
				echo '>';
				if ($row_Rs_emploi['heure'] < 1 ) {$plageH= $row_Rs_emploi['heure_debut'];}
						else { 
						//modif pierre
						//$plageH=$plages[ $row_Rs_emploi['heure']];
						echo $row_Rs_emploi['heure_debut']."-".$row_Rs_emploi['heure_fin'];
						$plageH='';
						}; 
						
						// rappel :dans empl du tmps, heure est le code plage h!
				echo '<br>';
				//echo '<font style="font-size:12px;border:1px solid;padding-left:2px;padding-right:2px;">'.$plageH.'</font>&nbsp;';
				//if($row_Rs_emploi['groupe'] !='Classe entière'){ $row_Rs_emploi['groupe'] ;};
				echo $row_Rs_emploi['groupe'].'<br>';
				//$nom_prenom=explode(" ", $row_Rs_emploi['identite']);
				echo '<br><b>'.$row_Rs_emploi['identite'].'</b><br>';
				if(strlen($row_Rs_emploi['identite'])<14){echo'<br>';};
				//if ($Nb_LesAbs['salle']<>''){echo '&nbsp;-S'.$Nb_LesAbs['salle'].'-';};
				
				
				
				//on affiche en plus le nom du regroupement s'il existe
				if($row_Rs_emploi['gic_ID']>0){
					$query_Rsnom_regroup = sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE  ID_gic= %s",$row_Rs_emploi['gic_ID']);	
					$Rsnom_regroup = mysqli_query( $conn_cahier_de_texte,$query_Rsnom_regroup) or die(mysqli_error($conn_cahier_de_texte));
					$row_Rsnom_regroup= mysqli_fetch_assoc($Rsnom_regroup);
					echo '<br> '.$row_Rsnom_regroup['nom_gic'] .'<br> ';
				};
				echo '<br>';
				if ($Nb_LesAbs>0  && $nb_AucunAbs == 0 ) {
					echo $Nb_LesAbs. ' absent';	if ($Nb_LesAbs>1){echo 's';};
				} elseif ($nb_AucunAbs>0) {
					//echo 'Pas d\'absents';
				} else {
					//echo 'Pas d\'appel';
				}
				
				echo '</td>';
				$hdeb[$a]=$row_Rs_emploi['heure_debut'];
				$idp[$a]=$row_Rs_emploi['prof_ID'];
				$a=$a+1;
			} while ($row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi)); 
			
} else { 
	if ($Nb__RsDs>0){echo '<td  class="tab_detail_gris"></td>';} else {echo ' Pas de cours. ';};
};	

//affichage ds dans cellule du bandeau
if ($Nb__RsDs>0){;
	do
	{
		
		
		//*******************Decompte absents sur  heure de ds et heure sup  decompte  nb absents********************
		
		$query_Rsnbele = sprintf("SELECT * FROM ele_absent WHERE absent='Y'
		AND	date= '%s' 
		AND ele_absent.classe_ID=%u 
		AND heure_debut='%s' 
		AND prof_ID=%u ",
		$datetoday,$row_Abs_ou_Ret_cl['classe_ID'],$row_RsDs['heure_debut'],$row_RsDs['prof_ID']);
		
		$Rsnbele = mysqli_query( $conn_cahier_de_texte,$query_Rsnbele) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rsnbele= mysqli_fetch_assoc($Rsnbele);
		$Nb__Rsnbele = mysqli_num_rows($Rsnbele);
		
		echo '<td style=" width:100px;" ';
		
		if (($Nb__Rsnbele==1) && ( $row_Rsnbele['elev_ID']==0)) { echo ' bgcolor="'. $couleur_pas_absent  .'"'; }
		else if ( $Nb__Rsnbele == 0 ) { echo ' bgcolor="'. $couleur_pas_appel .'"'; }
		else if ($Nb__Rsnbele>1) { echo ' bgcolor="'.  $couleur_absent .'"';}
		else { echo 'class="tab_detail_gris"';};
		echo '>';
		echo $row_RsDs['heure_debut']. '-'.$row_RsDs['heure_fin'];
		if ($row_Rsnbele['salle']<>''){echo ' ('.$row_Rsnbele['salle'].')';};
		if ($row_RsDs['type_activ']=='ds_prog'){echo '<br /><span class="blanc">'.$_SESSION['libelle_devoir'].'</span>';} else {echo '<br /><span class="blanc">Heure sup.</span>';};
		echo '<br> '.$row_RsDs['identite'];
		echo '<br> '.$row_RsDs['groupe'] .' <br> ';
		if (($Nb__Rsnbele==1) && ( $row_Rsnbele['elev_ID']==0)) {
			echo 'Pas d\'absents';
		} elseif ($Nb__Rsnbele>1) {
			echo $Nb__Rsnbele. ' absent(s)';
		} elseif ($Nb__Rsnbele == 0) {
			echo 'Pas d\'appel';
		};
	
		
		echo '</td>';
		$hdeb[$a]=$row_RsDs['heure_debut'];
		$gr[$a]=$row_RsDs['groupe'];
		$np[$a]=$row_RsDs['nom_prof'];
		$idp[$a]=$row_RsDs['ID_prof'];
		$a=$a+1;
		
	} while ($row_RsDs = mysqli_fetch_assoc($RsDs));
};


echo '</tr>';	

if ($Nb__Abs_ou_Ret>0){
	//mysqli_data_seek($Rsnbele, 0);
	$ic=0;
	$previous_classe = "";
	do { 	?>
		<?php if (($row_Abs_ou_Ret_cl['classe_ID'] == 0) && ($previous_classe != $row_Abs_ou_Ret['nom_classe'] ) ) { ?>
		  <tr>
			<td class="Style666b" colspan="<?php echo $Nb__Rs_emploi +1+$Nb__RsDs;    ?>" ><div align="left">
			<?php   echo $row_Abs_ou_Ret['nom_classe'];  ?>
			</div></td>
		  </tr>
			<?php
			
			$previous_classe = $row_Abs_ou_Ret['nom_classe'];
			//================remplissage cases eleves ================================================================ 
		} ?>
		<tr>
		<td  class="tab_detail_gris" style="padding:6px 12px 4px 4px;text-align:left;width:150px;background-color:#CCC;">
		<a href="carnet_elv.php?elvid=<?php echo $row_Abs_ou_Ret['eleve_ID'].'&classe='.$row_Abs_ou_Ret['nom_classe'].'&date1=01/09/2014'.'&date2='.date('d/m/Y'); ?>&submit=Actualiser" target="_blank" title="carnet de l'&eacute;l&egrave;ve" >
		&nbsp;<!--<IMG SRC="../images/carnet18y.png"> -->
		<?php
					echo '<b>'.$row_Abs_ou_Ret['nom_ele'].'&nbsp;'.$row_Abs_ou_Ret['prenom_ele'].'</b>';	
?>  
		</a></td>
		<?php

		for($j=1;$j<=($Nb__Rs_emploi+$Nb__RsDs);$j++){
			?>
			<?php
			
			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $query_Rsele = sprintf("SELECT * FROM ele_absent  WHERE 
                       ( (absent='Y') OR ( retard_V='Y') OR ( retard_Nv='Y' ))
AND
                        eleve_ID=%u AND date = '%s' AND classe_ID=%u AND heure_debut='%s' AND prof_ID=%u",$row_Abs_ou_Ret['eleve_ID'],$datetoday,$row_Abs_ou_Ret_cl['classe_ID'],$hdeb[$j], $idp[$j]);
                        
                        $Rsele = mysqli_query( $conn_cahier_de_texte,$query_Rsele) or die(mysqli_error($conn_cahier_de_texte));
                        $row_Rsele= mysqli_fetch_assoc($Rsele);
			$Nb__Rsele = mysqli_num_rows($Rsele);	
			//echo '$Nb__Rsele = '.$Nb__Rsele.'<br>'.$query_Rsele;
			if ($Nb__Rsele <= 0) {
				
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				
                                $query_Rsele = sprintf("SELECT * FROM ele_absent WHERE  
                               ( (absent='Y') OR ( retard_V='Y') OR ( retard_Nv='Y' ))
AND
                                eleve_ID=%u AND date = '%s' AND eleve_ID!=0 AND classe_ID=%u AND heure_debut='%s' AND prof_ID=%u ",$row_Abs_ou_Ret['eleve_ID'],$datetoday,$row_Abs_ou_Ret_cl['classe_ID'],$hdeb[$j], $idp[$j]);
                                
                                $Rsele = mysqli_query( $conn_cahier_de_texte,$query_Rsele) or die(mysqli_error($conn_cahier_de_texte));
                                $row_Rsele= mysqli_fetch_assoc($Rsele);
				$Nb__Rsele = mysqli_num_rows($Rsele);
			}
			
			//echo '* '.$row_Rsele['heure_debut'].'  et '.$hdeb[$j].'<br>';
			if ($row_Rsele['heure_debut']==$hdeb[$j])
			{
				echo '<td  class="tab_detail_gris" style="background-color:#CCC" ><div align="left">&nbsp;';
				
				isset($row_Rsele['vie_sco_statut']) ? $visa = $row_Rsele['vie_sco_statut'] : $visa ='N';
				if (isset($row_Rsele['absent'])&&($row_Rsele['absent']=='Y'))
					{ echo '<b>Absent</b>';
					echo '<input type="checkbox" onclick="solde(\''. $row_Rsele['ID']  .'\' ,this.checked);" ' ;
					if ( $visa =='Y' ) { echo " checked "   ;};
					echo '>';
					}
				else { if (isset($row_Rsele['retard_V'])&&($row_Rsele['retard_V']=='Y')){echo '(r)&nbsp;';}
						else { if (($row_Rsele['motif']> 0 )&& ( $row_Rsele['motif']< $indexMotifsMin)) {
									$msg = $motifs[$row_Rsele['motif']];} else {$msg='Retard ';} ;     
							if ( isset($row_Rsele['retard_Nv']) && ($row_Rsele['retard_Nv']=='Y')) { 
							echo '<span style="color:#FF0000";><b>'.$msg.' N.V.!</b></span>';
							};
							};
					 };



//Presence au CDI ?

		$hfdeb=substr($row_Rsele['heure_debut'],0,2).':'.substr($row_Rsele['heure_debut'],3,2);
		$hffin=substr($row_Rsele['heure_fin'],0,2).':'.substr($row_Rsele['heure_fin'],3,2);
		$query_Rsele_pointe = sprintf("
		SELECT * FROM ele_present WHERE 
		eleve_ID= '%u' 
		AND classe_ID='%u' 
		AND  SUBSTRING(date_heure,1,10)= '%s' 
		AND heure_debut > '%s' 
		AND heure_fin <= '%s' 
		ORDER BY date_heure DESC LIMIT 1",$row_Rsele['eleve_ID'],$row_Rsele['classe_ID'],date('Y-m-d'),$hfdeb,$hffin);
		$Rsele_pointe = mysqli_query( $conn_cahier_de_texte,$query_Rsele_pointe) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rsele_pointe = mysqli_fetch_assoc($Rsele_pointe);
		$Nb__Rsele_pointe = mysqli_num_rows($Rsele_pointe);
		
//fin presence CDI



				if ($Nb__Rsele_pointe==1){echo '<div class="cdi" style="display:inline" >CDI ';
				echo $row_Rsele_pointe['heure_debut']; if ($row_Rsele_pointe['heure_fin']<>'00:00'){echo ' '.$row_Rsele_pointe['heure_fin'];} else {echo '   -- > ?';};
				echo'</div>';};
				echo '</div>';echo '</td>';
			}
			else
			{
				echo '<td class="tab_detail_gris" ></td> ';
			}
			;
			?>
			
			
			<?php   	
		} ;  ?>
		</tr>
		<?php 

	} while ($row_Abs_ou_Ret = mysqli_fetch_assoc($Abs_ou_Ret)); 		if ($row_Abs_ou_Ret_cl['classe_ID']==0){echo '<br><br>';};
};
	
?>