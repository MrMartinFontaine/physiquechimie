<?php


				// absence module elabore avec suivi des carnets
				?>
				
				<form method="post" target="_blank" action="../vie_scolaire/appel.php?nom_classe=<?php echo $row_RsJour['nom_classe']?>&classe_ID=<?php echo $row_RsJour['classe_ID']?>&gic_ID=<?php echo $row_RsJour['gic_ID']?>&nom_matiere=<?php echo $row_RsJour['nom_matiere']?>&groupe=<?php if ($row_RsJour['groupe']!= 'Classe entière'){ echo $row_RsJour['groupe'];}?>&matiere_ID=<?php echo $row_RsJour['matiere_ID']?>&semaine=<?php echo $row_RsJour['semaine']?>&Ds=<?php echo substr($code_date,8,1)?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;} ?>&heure=<?php echo $row_RsJour['heure']?>&heure_debut=<?php echo $row_RsJour['heure_debut']?>&heure_fin=<?php echo $row_RsJour['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&date=<?php echo substr($code_date,0,8)?>&code_date=<?php echo $code_date?>&edt_modif=<?php if (isset($row_RsAgenda2['edt_modif'])){echo $row_RsAgenda2['edt_modif'];}else{echo'N';}?>">
				
<input border=0 src="../images/user_absent.png" type="image" value="submit" alt="Gestion des absents" title="Gestion des absents et incidents">
					</form> 
					<?php
				if (substr($row_RsJour['heure_debut'],1,1)=='h'){$ch_heure_debut='0'.$row_RsJour['heure_debut'];} else { $ch_heure_debut=$row_RsJour['heure_debut']  ;}; //9h00 devient 09h00
				

					$debut_plage= mktime( substr($ch_heure_debut,0,2),substr($ch_heure_debut,3,2),0, substr($code_date,4,2) , substr($code_date,6,2) , substr($code_date,0,4) );$appelpossible=(time()- $debut_plage)>0;	
$Nb_RsEventSaisis=0;					

					
				if ($appelpossible) { 	// date et heure de l'appel
				?>
					<?php	
						mysqli_select_db($conn_cahier_de_texte,$database_conn_cahier_de_texte);

						if ( $row_RsJour['gic_ID'] != "0" ) {// On recupere l'heure de saisie prec de cet appel
							/*
							$query_RsEventSaisis  = sprintf("SELECT date,heure_saisie 
							FROM ele_absent 
							WHERE date = '%s' 
							AND groupe= '%s' 
							AND heure_debut='%s' AND prof_ID=%u ",
							substr($code_date,0,8),$row_RsJour['groupe'] ,$row_RsJour['heure_debut'],$ProfID);
							*/
							$query_RsEventSaisis  = sprintf("SELECT date,heure_saisie 
							FROM ele_absent 
							WHERE date = '%s' 
							AND heure_debut='%s' AND prof_ID=%u ",
							substr($code_date,0,8),$row_RsJour['heure_debut'],$ProfID);
							
						} else {
							$query_RsEventSaisis  = sprintf("SELECT date, heure_saisie FROM ele_absent 
							WHERE date = '%s' AND classe_ID=%u AND  heure_debut='%s' AND prof_ID=%u ",
							substr($code_date,0,8),$row_RsJour['classe_ID'],$row_RsJour['heure_debut'],$ProfID); 
						};// fin On recupere l'heure de saisie prec de cet appel
						//echo $query_RsEventSaisis;
						$RsEventSaisis = mysqli_query( $conn_cahier_de_texte, $query_RsEventSaisis) or die(mysqli_error($conn_cahier_de_texte));
						$row_RsEventSaisis= mysqli_fetch_assoc($RsEventSaisis);
						$Nb_RsEventSaisis = mysqli_num_rows($RsEventSaisis);
						
					    if ($Nb_RsEventSaisis>0)  { // fond clair si appel non fait, bleu si fait
							
							if ($nb_Agenda2==0){$color_abs='#FFFFFF';	} else {$color_abs='#BBCEDE';};
	
							
							 ;?>
						<tr height="10" class="Style1" >					
						<td colspan="6" style="padding:0px;font-color:black; background-color:<?php echo $color_abs;?>"><?php
						
						 			// affichage Appel effectué /non effectué 
							echo '<font color="green">Appel &agrave; ';
							echo substr($row_RsEventSaisis['heure_saisie'],0,2).'h '.substr($row_RsEventSaisis['heure_saisie'],2,2);
					
						echo '</font>';
						mysqli_free_result($RsEventSaisis ); //fin affichage Appel effectué /non effectué 
						

					
									
				?> 	</td>	<td  align="center" style="padding:2px 1px 0px 0px ; background-color:<?php echo $color_abs;?>">
				<?php
					// attention code_date  reformaté  en date , on ajoute un champ Ds pour le 9eme car,   heure  devient  heure  , heure_saisie contiendra  l'heure de saisie et le nom abrege du jour  
					//  et "classe entière" est effacé dans groupe  
					// &duree=<?php echo $row_RsJour['duree']
					?>
					</td> 
					</tr>
					<?php 
					}; // du if ($Nb_RsEventSaisis>0)
			};
				
?>
