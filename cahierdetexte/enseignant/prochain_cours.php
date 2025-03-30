<?php

// Determination du prochain cours dans cette matiere et classe
function NbJourSem($jour_sem) {
	$jour_sem= substr($jour_sem,0,3);
	switch($jour_sem) {
	case "Lun" : 
		$n_sem=1;
		break;
	case "Mar" : 
		$n_sem=2;
		break;
	case "Mer" : 
		$n_sem=3;
		break;
	case "Jeu" : 
		$n_sem=4;
		break;
	case "Ven" : 
		$n_sem=5;
		break;
	case "Sam" : 
		$n_sem=6;
		break;
	case "Dim" : 
		$n_sem=7;
	};
	return $n_sem;
};

function DateProchainCours($total,$k,$a,$d,$x_deb,$dd,$m,$days,$y,$n) {
	/////////////////   DETERMINATION DE LA DATE DU K-EME PROCHAIN COURS A TRAITER DANS LA N-EME SEMAINE A VENIR   ////////////////
	//$total est le nombre de prochains cours
	//$k est le numero du prochain cours
	//$a est le tableau contenant les jours de la semaine des prochains cours
	//$d est le tableau contenant la frequence des prochains cours (semaine alternee ou non)
	//$x_deb est le jour de la semaine de la date de saisie
	//$dd est le mois de la date de saisie
	//$y est l'annee de la date de saisie
	//$m est le mois de la date de saisie
	//$days est le nombre de jours du mois de la date de saisie
	//$y est l'annee de la date de saisie
	//$n est le numero de la semaine a suivre
	
	$x_fin=NbJourSem($a[$k]); //Renvoie le rang du jour de la semaine de la date du prochain cours a traiter
	
	//Determination du jour du mois du prochain cours a traiter ($tot) avec eventuellement determination du nombre de jours entre la date de saisie et le prochain cours a traiter ($ecart)
	if ($x_fin>$x_deb) {$ecart=$x_fin-$x_deb;$tot=$dd+(7*($n-1))+$ecart; } 			//Le prochain cours a traiter est dans la meme semaine que la date de saisie
	else if ($x_fin<$x_deb) {$ecart=7-$x_deb+$x_fin;$tot=$dd+(7*($n-1))+$ecart; }	//Le prochain cours a traiter n'est pas dans la meme semaine que la date de saisie
	else {																			//Le prochain cours a traiter est le meme jour de la semaine que la date de saisie et ...
		if (($k==1)&&($total!=1)) {$tot=$dd+(7*($n-1));}					//... et le prochain cours a traiter est le meme jour de la MEME semaine que la date de saisie
		else if (($k==1)&&($total==1)) {$tot=$dd+(7*$n);}					//... et le prochain cours a traiter est le meme jour d'une AUTRE semaine que la date de saisie car il n'y a qu'un prochain cours (cas non traite : il n'y a qu'un prochain cours, celui de la meme semaine car c'est la fin d'un cycle, fin d'une annee)
		else if ($k!=1) {$tot=$dd+(7*$n);};									//... et le prochain cours a traiter est le meme jour d'une AUTRE semaine que la date de saisie car il y a deja eu un prochain cours et on suppose qu'il n'y a pas 3 seances d'une meme matiere le meme jour
	};
	if (($d[$k]<>'A et B')&&($total==1)){$tot+=7;};	//Si le prochain cours a traiter est sur une semaine en alternance ajouter encore 7 jours
	
	
	//Recherche de la date du prochain cours : $jj est le jour,$ind_mm est le mois, $yyyy est l'annee
	$ind_m=$m*1;	//Initialisation au mois de la date de saisie sous forme de nombre
	$yyyy=$y;		//Initialisation a l'annee de la date de saisie
	
	if ($tot>$days)	//Le prochain cours a traiter a lieu le mois prochain
	{
		$jj=$tot-$days;
		if ($ind_m==12){$yyyy=$yyyy+1;$ind_m=1;} else {$ind_m=$ind_m+1;};
	}
	else {$jj=$tot;}
	
	//Remettre les indices $ind_m et $jj avec deux caracteres
	if ($ind_m<10){$ind_m='0'.$ind_m;};
	if ($jj<10){$jj='0'.$jj;};
	
return array($jj,$ind_m,$yyyy); };

?>
<script type="text/javascript">
function ds_onclick2(date_a_faire) {
	
	//ds_ce.style.display = 'none';
	if (typeof(ds_element.value) != 'undefined') {	ds_element.value = date_a_faire ;
	}
}
</script>
<?php

$edt_madate=substr($madate,0,4).'-'.substr($madate,4,2).'-'.substr($madate,6,2);
$liste_mois=array('Janvier', 'F&eacute;vrier', 'Mars', 'Avril', 'Mai', 'Juin','Juillet', 'Ao&ucirc;t', 'Septembre', 'Octobre', 'Novembre', 'D&eacute;cembre' );

$choixgroupe_RsNextCours = "0";
if (isset($_GET['groupe'])) {
	$choixgroupe_RsNextCours = (get_magic_quotes_gpc()) ? $_GET['groupe'] : addslashes($_GET['groupe']);
}

$choixmatiere_RsNextCours = "0";
if (isset($_GET['matiere_ID'])) {
	$choixmatiere_RsNextCours = (get_magic_quotes_gpc()) ? $_GET['matiere_ID'] : addslashes($_GET['matiere_ID']);
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_partage = sprintf("SELECT * FROM cdt_emploi_du_temps_partage WHERE profpartage_ID=%u", $_SESSION['ID_prof']);
$Rs_partage = mysqli_query($conn_cahier_de_texte, $query_Rs_partage) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_partage = mysqli_fetch_assoc($Rs_partage);


// regroupement
if (($_GET['classe_ID']==0)&&($_GET['gic_ID']>0)) { 
	
	$choixregroupement_RsNextCours = "0";
	if (isset($_GET['gic_ID'])) {
		$choixregroupement_RsNextCours = (get_magic_quotes_gpc()) ? $_GET['gic_ID'] : addslashes($_GET['gic_ID']);
	}
	
	//La requete suivante $query_RsNextCours determine les prochains cours issus de la table emploi_du_temps.
	//Si le cycle (l'annee) n'est pas termine, cette requete n'est pas vide
	
	if ($_GET['groupe']=='Classe entière')
	{
		$query_RsNextCours = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE (
			prof_ID=%u
			AND gic_ID=%u 
			AND matiere_ID=%u  
			AND edt_exist_debut <= '%s'
			AND edt_exist_fin >= '%s'
			)",$_SESSION['ID_prof'], $choixregroupement_RsNextCours,$choixmatiere_RsNextCours,$edt_madate,$edt_madate);
		
		do { //Heure partagee
			$query_RsNextCours .= sprintf(" OR ( 
				ID_emploi=%u
				AND gic_ID=%u 
				AND matiere_ID=%u  
				AND edt_exist_debut <= '%s'
				AND edt_exist_fin >= '%s'
				)",$row_Rs_partage['ID_emploi'], $choixregroupement_RsNextCours,$choixmatiere_RsNextCours,$edt_madate,$edt_madate);
		} while ($row_Rs_partage = mysqli_fetch_assoc($Rs_partage));
		mysqli_free_result($Rs_partage);
	}
	else 
	{
		$query_RsNextCours = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE ( 
			prof_ID=%u 
			AND gic_ID=%u 
			AND matiere_ID=%u 
			AND (groupe ='Classe entière' OR groupe='%s') 
			AND edt_exist_debut <= '%s'
			AND edt_exist_fin >= '%s'
			)",$_SESSION['ID_prof'], $choixregroupement_RsNextCours,$choixmatiere_RsNextCours,$choixgroupe_RsNextCours,$edt_madate,$edt_madate);
		
		do { //Heure partagee
			$query_RsNextCours .= sprintf(" OR ( 
				ID_emploi=%u
				AND gic_ID=%u 
				AND matiere_ID=%u  
				AND (groupe ='Classe entière' OR groupe='%s') 
				AND edt_exist_debut <= '%s'
				AND edt_exist_fin >= '%s'
				)",$row_Rs_partage['ID_emploi'], $choixregroupement_RsNextCours,$choixmatiere_RsNextCours,$choixgroupe_RsNextCours,$edt_madate,$edt_madate);
		} while ($row_Rs_partage = mysqli_fetch_assoc($Rs_partage));
		mysqli_free_result($Rs_partage);
	}
}
else // pas de regroupement - classe normale
{
	
	$choixclasse_RsNextCours = "0";
	if (isset($_GET['classe_ID'])) {
		$choixclasse_RsNextCours = (get_magic_quotes_gpc()) ? $_GET['classe_ID'] : addslashes($_GET['classe_ID']);
	}
	if ($_GET['groupe']=='Classe entière')
	{
		$query_RsNextCours = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE (
			prof_ID=%u 
			AND classe_ID=%u 
			AND matiere_ID=%u  
			AND edt_exist_debut <= '%s'
			AND edt_exist_fin >= '%s'
			)",$_SESSION['ID_prof'], $choixclasse_RsNextCours,$choixmatiere_RsNextCours,$edt_madate,$edt_madate);
		do { //Heure partagee
			$query_RsNextCours .= sprintf(" OR (
				ID_emploi=%u
				AND classe_ID=%u
				AND matiere_ID=%u  
				AND edt_exist_debut <= '%s'
				AND edt_exist_fin >= '%s'
				)",$row_Rs_partage['ID_emploi'], $choixclasse_RsNextCours,$choixmatiere_RsNextCours,$edt_madate,$edt_madate);
		} while ($row_Rs_partage = mysqli_fetch_assoc($Rs_partage));
		mysqli_free_result($Rs_partage);
	}
	else 
	{
		$query_RsNextCours = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE (
			prof_ID=%u
			AND classe_ID=%u 
			AND matiere_ID=%u 
			AND (groupe ='Classe entière' OR groupe='%s') 
			AND edt_exist_debut <= '%s'
			AND edt_exist_fin >= '%s'
			)",$_SESSION['ID_prof'], $choixclasse_RsNextCours,$choixmatiere_RsNextCours,$choixgroupe_RsNextCours,$edt_madate,$edt_madate);
		
		do { //Heure partagee
			$query_RsNextCours .= sprintf(" OR (
				ID_emploi=%u
				AND classe_ID=%u 
				AND matiere_ID=%u  
				AND (groupe ='Classe entière' OR groupe='%s') 
				AND edt_exist_debut <= '%s'
				AND edt_exist_fin >= '%s'
				)",$row_Rs_partage['ID_emploi'], $choixclasse_RsNextCours,$choixmatiere_RsNextCours,$choixgroupe_RsNextCours,$edt_madate,$edt_madate);
		} while ($row_Rs_partage = mysqli_fetch_assoc($Rs_partage));
		mysqli_free_result($Rs_partage);
	};
};

$query_RsNextCours .= " ORDER BY jour_semaine, heure";

//echo $query_RsNextCours;

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$RsNextCours = mysqli_query($conn_cahier_de_texte, $query_RsNextCours) or die(mysqli_error($conn_cahier_de_texte));
$row_RsNextCours = mysqli_fetch_assoc($RsNextCours);
$totalRows_RsNextCours = mysqli_num_rows($RsNextCours);

if ($totalRows_RsNextCours==0){    //Pas de cours prochains trouves (sans doute une fin d'annee ou de cycle)
 	
	for ($i=0;$i<4;$i++) {   //Besoin de trois div differents car trois zones de travail a faire differents
		?>
		<div id="nextcours<?php echo $i; ?>" >
		<table  border="0" cellpadding="0" cellspacing="0" >
		<tr >
		<td class="Style6"colspan="2" ><div align="center">Pas de cours de <?php echo $_GET['nom_matiere'] ;?> dans l'imm&eacute;diat</div></td>
		<td class="Style6"><div align="right"><a href="#peu_importe" onclick="MM_showHideLayers('nextcours<?php echo $i; ?>','','hide');"><img src="../images/close.gif" border="0" /></a></div></td>
		</tr>
		</table>
		</div>
		<?php	
	};
} else {    // Cas normal : un ou plusieurs prochains cours ont ete trouves
	
	//////////////  INITIALISATION /////////////////
	
	// 1 : Recherche pour la date de saisie en cours de plusieurs parametres : son jour, son mois, son annee, son rang dans la semaine, le nb de jours dans le mois,
	// On extrait de la date de la saisie son jour ($dd), son mois ($m) et son annee ($y)
	$dd=substr($_GET['code_date'],6,2);
	$m=substr($_GET['code_date'],4,2);
	$y=substr($_GET['code_date'],0,4);
	
	$x_deb=NbJourSem($_GET['jour_pointe']); //Renvoie le rang du jour de la semaine de la date de saisie
	
	// Determination du nombre de jours du mois en cours ($days)
	if ($m == '01' || $m == '03' || $m == '05' || $m == '07' || $m == '08' || $m == '10' || $m == '12') {
		$days = 31;
	} else if ($m == '04' || $m == '06' || $m == '09' || $m == '11') {
		$days = 30;
	} else if ((is_int($y/4) && !is_int($y/100)) || is_int($y/400)) { //Prise en compte de l'annee bissextile
		$days = 29 ;
	} else {
		$days = 28;
	};
	
	//Fin de la recherche sur la date de saisie
	
	// 2 : La boucle suivante permet de determiner le rang de la requete precedente qui contient le meme jour que la date de saisie ainsi que la meme heure de debut.
	
	mysqli_data_seek($RsNextCours,0);
	$j=1;   // $j est un compteur des prochains cours trouves pour la boucle suivante.
	// En fin de boucle, $j contiendra la numero de la ligne de la requete precedente qui contient le meme jour qu'aujourd'hui
	
	$jourtrouve=false;
	
	while (($row_RsNextCours = mysqli_fetch_assoc($RsNextCours))&&(!($jourtrouve))) {
		
		$x_sem=NbJourSem($row_RsNextCours['jour_semaine']); 
		
		if (!(($x_deb==$x_sem)&&($row_RsNextCours['heure_debut']==$_GET['heure_debut']))) {
			if ($x_deb<$x_sem)  {$j-=1;$jourtrouve=true;}
			else {$j+=1;}
		} else {
			$jourtrouve=true;
		}
	};
	if($j>$totalRows_RsNextCours) {$j=$totalRows_RsNextCours;};
	
	
	// 3 : La boucle suivante permet de changer l'ordre des prochains cours issus de la requete
	// Cette boucle va remettre l'ordre des prochains cours dans l'ordre chronologique en fonction du jour (de la semaine) de jour_pointe
	mysqli_data_seek($RsNextCours,0);
	$w=0;	// $w est un compteur des prochains cours trouves pour la boucle suivante.
	$total=$totalRows_RsNextCours;	// Juste pour faciliter la lecture de la boucle suivante
        
        while ($row_RsNextCours = mysqli_fetch_assoc($RsNextCours)){
                $w=$w+1;
                if ($w<=$j){$pos[$w]= $total-$j+$w;}   // Le tableau pos sert a faire la permutation des indices, l'ordre est toujours le meme mais le cours de debut differe
                else if ($w==$j+1){$pos[$w]=1;}
                else if ($w>$j+1){$pos[$w]= $pos[$w-1]+1;};
                // 5 tableaux contiennent chacun pour chaque cours remis dans l'ordre l'info adequate
                $a[$pos[$w]]=$row_RsNextCours['jour_semaine'];   
                $b[$pos[$w]]=$row_RsNextCours['groupe'];
		$c[$pos[$w]]=$row_RsNextCours['heure_debut'];
		$d[$pos[$w]]=$row_RsNextCours['semaine'];
		$e[$pos[$w]]=$row_RsNextCours['edt_exist_fin'];
	}
	
	//////////////  FIN INITIALISATION /////////////////
	
	
	for ($i=0;$i<4;$i++) {  // Besoin de trois div differents car trois zones de travail a faire differents + une div sous la zone de saisie
		
		?>
		<div id="nextcours<?php echo $i.'"';if ($i==0){echo ' style="display:none;border: thin solid #006600;width:829px;padding:0px;margin:0px;margin-bottom:5px;margin-top:5px;"';};?> >
		<table  border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr >
		<?php if($i==0){?>
			<td class="Style6"colspan="3" ><div align="left"><?php echo 'Prochains cours de '.$_GET['nom_matiere'].' en '.$_GET['nom_classe'] ;?></div></td>
			<?php
		}else {?>
			<td class="Style6"colspan="2" ><div align="center">Prochains cours de <?php echo $_GET['nom_matiere'] ;?></div></td>
			<td class="Style6"><div align="right"><a href="#peu_importe" onclick="MM_showHideLayers('nextcours<?php echo $i; ?>','','hide');"><img src="../images/close.gif" border="0" /></a></div></td>
		<?php };?>
		</tr>
		<?php 
		
		
		//La boucle suivante permet de traiter tous les prochains cours (issus de la requete $RsNextCours) mais dans l'ordre chronologique
		$indice=0;
		$ferie=0;	// $ferie compte le nombre de jours feries parmi les prochains cours
		$jours_trouves=0;
		$FinDePeriode=false;	// $FinDePeriode permet de savoir si on atteint la fin de la periode ou de l'annee
		
		do { $indice+=1;
			$ind=$indice % $totalRows_RsNextCours;	// $ind contient $indice modulo $totalRows_RsNextCours
			$ind=($ind==0?$totalRows_RsNextCours:$ind);	// Si $ind=0, on le remet a $totalRows_RsNextCours
			
			if (($_GET['groupe']=='Classe entière')||($b[$ind]==$_GET['groupe'])||($b[$ind]=='Classe entière')) {
				$ProchainCours = DateProchainCours($totalRows_RsNextCours,$ind,$a,$d,$x_deb,$dd,$m,$days,$y,ceil($indice/$totalRows_RsNextCours));
				$jj=$ProchainCours[0];
				$ind_m=$ProchainCours[1];
				$yyyy=$ProchainCours[2];
				$dtf=$jj.'-'.$ind_m.'-'.$yyyy;
				$lemois=$liste_mois[$ind_m-1];
				$codedate_dtf=$yyyy.$ind_m.$jj;
				
				$DateDeFin=substr($e[$ind],0,4).substr($e[$ind],5,2).substr($e[$ind],8,2);
				$BesoinDeDateFinale=true;	// Cette variable permet de ne rechercher la date de fin de periode qu'une seule fois
				if ($DateDeFin>=$codedate_dtf) {	// Cette conditionnelle exclut les prochains cours dont la date de fin est echue
					
					// La boucle suivante determine si le prochain cours est un jour ferie
					$n=1;$jourferie=false;
					while (($n<=$totalRows_Vacances)&&(!($jourferie))){ 
						if (($codedate_dtf>=$tab_debut[$n]) && ($codedate_dtf<=$tab_fin[$n])) {
							$ferie+=1;
							$ferie_libel[$ferie]=$tab_libel[$n];
							$jourferie=true;
						};
						$n=$n+1;	// Au cas ou un libelle de vacances soit vide, on laisse incrementer cette variable
					}
					
					if (!($jourferie)) { // jour travaille
						
						$jours_trouves+=1;
						$query_RsSemtest =  sprintf("SELECT semaine FROM cdt_semaine_ab WHERE s_code_date<=%s ORDER BY s_code_date DESC LIMIT 1",$codedate_dtf);
						$RsSemtest = mysqli_query($conn_cahier_de_texte, $query_RsSemtest) or die(mysqli_error($conn_cahier_de_texte));
						$row_RsSemtest= mysqli_fetch_assoc($RsSemtest);
						if (($d[$ind]== $row_RsSemtest['semaine']) OR ($d[$ind]=='A et B')){    //Gestion des semaines en alternance
							
							if ($i>0){ 	//Cette partie est destinee aux 3 zones de travail a faire
								?>
								<tr>
								<td class="tab_nextcours" height="20"><p onclick="ds_onclick2('<?php echo $dtf;?>');MM_showHideLayers('nextcours<?php echo $i; ?>','','hide');"><a href="#peu_importe"><img src="../images/puce_bleue.gif" />&nbsp;<?php echo $a[$ind].' '.$jj.' '.$lemois;?> </a></p></td>
								<td class="tab_nextcours"><?php echo $b[$ind]; ?></td>
								<td class="tab_nextcours"><?php echo $c[$ind]; ?></td>
								</tr>
								<?php 
							} else {	//Cette partie est destinee a la zone d'affichage des derniers cours assures.
								?>
								<tr>
								<td class="tab_nextcours" width= "200">&nbsp;&nbsp; <?php echo '&nbsp;'.$a[$ind].' '.$jj.' '.$lemois;?></td>
								<td class="tab_nextcours" width= "200"><?php echo $b[$ind]; ?></td>
								<td class="tab_nextcours" ><?php echo $c[$ind] ?></td>
								</tr>
								<?php
							}
						}
						mysqli_free_result($RsSemtest);
						
					} else if (($ferie==1)||($ferie_libel[$ferie]!=$ferie_libel[$ferie-1])) {	// Premier jour ferie trouve 
						// ou bien autres jours feries trouves et cas ou deux vacances differentes se succedent
						?>
						<tr>
						<td class="tab_nextcours" colspan="3"><em>
						<?php
						if ($tab_debut[$n-1]!=$tab_fin[$n-1]) {	// Recherche pour savoir si c'est un jour ferie seul ou des vacances sur plusieurs jours
							echo '&nbsp;&nbsp;<strong>'.$ferie_libel[$ferie].'</strong> : du '.substr($tab_debut[$n-1],6,2).' '.$liste_mois[substr($tab_debut[$n-1],4,2)-1].' au '.substr($tab_fin[$n-1],6,2).' '.$liste_mois[substr($tab_fin[$n-1],4,2)-1];
						} else {
							echo '&nbsp;&nbsp;<strong>'.$ferie_libel[$ferie].'</strong> : le '.substr($tab_debut[$n-1],6,2).' '.$liste_mois[substr($tab_debut[$n-1],4,2)-1];
						}
						?>
						</em></td>
						</tr>
						<?php 
					} else {
						$ferie-=1;	//Jour ferie identique au precedent donc non affiche (et non pris en compte)
					};	
				} else if ($BesoinDeDateFinale) {	// Cette partie permet de detecter la date de fin de periode et de verifier si la date du prochain cours est avant la date de fin de periode
					$BesoinDeDateFinale=false;
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$query_DateF = "SELECT param_val FROM cdt_params WHERE param_nom='date_fin_annee'";
					$DateF = mysqli_query($conn_cahier_de_texte, $query_DateF);
					$row_DateF = mysqli_fetch_row($DateF);
					$DateFinale=substr($row_DateF[0],6,4).substr($row_DateF[0],3,2).substr($row_DateF[0],0,2);
					mysqli_free_result($DateF);
					if ($codedate_dtf>$DateFinale) {
						$FinDePeriode=true;
					}
				}
			}	
		} while (($jours_trouves < $totalRows_RsNextCours)&&(!($FinDePeriode))); 
		
		echo "</table></div>";
	}
}
mysqli_free_result($RsNextCours);
?>
