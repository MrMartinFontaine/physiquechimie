<?php 
session_start();
if (isset($_SESSION['nom_prof'])){$_SESSION['consultation']=$_GET['classe_ID'];};
if (!isset($_SESSION['consultation'])OR ($_SESSION['consultation']<>$_GET['classe_ID'])){  header("Location: index.php");exit;};
//on filtre
if ((isset($_GET['classe_ID']))&&($_GET['classe_ID']!=intval($_GET['classe_ID']))){  header("Location: index.php");exit;};
if ((isset($_GET['date']))&&($_GET['date']!=intval($_GET['date']))){  header("Location: index.php");exit;};
require_once('Connections/conn_cahier_de_texte.php'); 

require_once('inc/functions_inc.php');

if(function_exists("date_default_timezone_set")){ //fonction PHP 5 
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_time_zone_db = "SELECT param_val FROM cdt_params WHERE param_nom='time_zone'";
$time_zone_db = mysqli_query($conn_cahier_de_texte, $query_time_zone_db) or die(mysqli_error($conn_cahier_de_texte));
$row_time_zone_db = mysqli_fetch_assoc($time_zone_db);
date_default_timezone_set($row_time_zone_db['param_val']);
};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if (isset($_GET['date'])){
	$codedateanneemois=substr($_GET['date'],0,6);
} else {
$codedateanneemois=date('Ym');};


//periodes de vacances
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Vacances = "SELECT * FROM cdt_agenda WHERE cdt_agenda.classe_ID=0 ORDER BY heure_debut ASC";
$Vacances = mysqli_query($conn_cahier_de_texte, $query_Vacances) or die(mysqli_error($conn_cahier_de_texte));
$row_Vacances = mysqli_fetch_assoc($Vacances);
$totalRows_Vacances = mysqli_num_rows($Vacances);
$n=0;

do {  
	$j_debut=substr($row_Vacances['heure_debut'],0,2);
	$m_debut=substr($row_Vacances['heure_debut'],3,2);
	$a_debut=substr($row_Vacances['heure_debut'],6,4);
	$j_fin=substr($row_Vacances['heure_debut'],0,2);
	$m_fin=substr($row_Vacances['heure_debut'],3,2);
	$a_fin=substr($row_Vacances['heure_debut'],6,4);
	$code_debut=$a_debut.$m_debut.$j_debut;
	$j_fin=substr($row_Vacances['heure_fin'],0,2);
	$m_fin=substr($row_Vacances['heure_fin'],3,2);
	$a_fin=substr($row_Vacances['heure_fin'],6,4);
	$j_fin=substr($row_Vacances['heure_fin'],0,2);
	$m_fin=substr($row_Vacances['heure_fin'],3,2);
	$a_fin=substr($row_Vacances['heure_fin'],6,4);
	$code_fin=$a_fin.$m_fin.$j_fin;
	
	$n=$n+1;
	
	$tab_debut[$n]=$code_debut; $tab_fin[$n]=$code_fin; $tab_libel[$n]=$row_Vacances['theme_activ'];
	
} while ($row_Vacances = mysqli_fetch_assoc($Vacances)); 






?>
<?php

/***************************************************************************
Adaptation du script calendar pour Cahier de textes
-------------------
begin                : June 2002
Version				 : 2.1 (Jan 04)
copyleft             : (C) 2002-2003 PHPtools4U.com - Mathieu LESNIAK
email                : support@phptools4u.com
***************************************************************************/

$calendar_txt['french']['monthes'] 	    = array('', 'Janvier', 'F&eacute;vrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet',
	'Ao&ucirc;t', 'Septembre', 'Octobre', 'Novembre', 'D&eacute;cembre');
$calendar_txt['french']['days']		    = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
$calendar_txt['french']['first_day']    = 0;
$calendar_txt['french']['misc'] 	    = array('Mois pr&eacute;c&eacute;dent', 'Mois suivant','Jour pr&eacute;c&eacute;dent', 'Jour suivant');

function calendar($date = '') {
	
	
	Global $link_on_day, $PHP_SELF, $params;
	Global $_POST, $_GET;
	Global $calendar_txt;
	Global $jour_pointe;
	Global $current_day_name;
	Global $day_name;
	Global $current_day;
	Global $current_month;
	Global $current_year;
	Global $current_month_2;
	Global $tab_debut;
	Global $tab_fin;
	Global $totalRows_Vacances; //nb de periodes de vacances
	Global $database_conn_cahier_de_texte;
	Global $conn_cahier_de_texte;
	Global $codedateanneemois;
	Global $travail;
	Global $choix_RsClasse;
	
	$choix_RsClasse = "0";
	if (isset($_GET['classe_ID'])) {
		$choix_RsClasse = (get_magic_quotes_gpc()) ? intval($_GET['classe_ID']) : addslashes(intval($_GET['classe_ID']));
	}
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	
	$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE cdt_classe.ID_classe=%u", $choix_RsClasse);
	$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsClasse = mysqli_fetch_assoc($RsClasse);
	$totalRows_RsClasse = mysqli_num_rows($RsClasse);
	
	### Default Params
	
	$param_d['calendar_id']			= 1; // Calendar ID
	$param_d['calendar_columns'] 	= 5; // Nb of columns
	$param_d['show_day'] 			= 1; // Show the day bar
	$param_d['show_month']			= 1; // Show the month bar
	$param_d['nav_link']			= 1; // Add a nav bar below
	$param_d['link_after_date']		= 1; // Enable link on days after the current day
	$param_d['link_before_date']	= 1; // Enable link on days before the current day
	
	$param_d['link_on_day']			= $PHP_SELF.'?date=%%dd%%'; // Link to put on each day
	$param_d['font_face']			= 'Verdana, Arial, Helvetica'; // Default font to use
	$param_d['font_size']			= 10; // Font size in px
	
	$param_d['bg_color']			= '#FFFFFF'; 
	$param_d['today_bg_color']		= '#BBCEDE';
	$param_d['font_today_color']	= '#000000';
	$param_d['font_color']			= '#000000';
	$param_d['font_nav_bg_color']	= '#A9B4B3';
	
	$param_d['font_nav_color']		= '#FFFFFF';
	$param_d['font_header_color']	= '#000000';
	$param_d['use_img']				= 1; // Use gif for nav bar on the bottom
	
	
	### Specifique au cahier de textes
	$param_d['border_color']		= '#0F5080';
	//$param_d['bg_listejour']		= '#CCCCFF';
	$param_d['bg_listejour']		= 'url(images/bande_bleue.jpg)';
	$param_d['bg_top']		        = '#BBCEDE';
	
	
	### New params V2
	$param_d['lang']				= 'french';
	$param_d['font_highlight_color']= '#FF0000';
	$param_d['bg_highlight_color']  = '#00FF00';
	$param_d['day_mode']			= 0;
	$param_d['time_step']			= 60;
	$param_d['time_start']			= '8:00';
	$param_d['time_stop']			= '18:00';
	$param_d['highlight']			= array();
	// Can be 'hightlight' or 'text'
	$param_d['highlight_type']      = 'highlight';
	$param_d['cell_width']          = 100;
	$param_d['cell_height']         = 50;
	$param_d['short_day_name']      = 1;
	$param_d['link_on_hour']        = $PHP_SELF.'?hour=%%hh%%';
	
	### /Params
	
	
	### Getting all params
	while (list($key, $val) = each($param_d)) {
		if (isset($params[$key])) {
			$param[$key] = $params[$key];
		}
		else {
			$param[$key] = $param_d[$key];
		}
	}
	
	$monthes_name = $calendar_txt[$param['lang']]['monthes'];
	$param['calendar_columns'] = ($param['show_day']) ? 7 : $param['calendar_columns'];
	
	$date = priv_reg_glob_calendar('date');
	if ($date == '') {
		$timestamp = time();
	}
	else {
		$month 		= substr($date, 4 ,2);
		$day 		= substr($date, 6, 2);
		$year		= substr($date, 0 ,4);
		$timestamp 	= mktime(0, 0, 0, $month, $day, $year);
	}
	
	
	$current_day 		= date("d", $timestamp);
	$current_month 		= date('n', $timestamp);
	$current_month_2	= date('m', $timestamp);
	$current_year 		= date('Y', $timestamp);
	$first_decalage 	= date("w", mktime(0, 0, 0, $current_month, 1, $current_year));
	### Sunday is the _LAST_ day
	$first_decalage		= ( $first_decalage == 0 ) ? 7 : $first_decalage;
	
	
	$current_day_index	= date('w', $timestamp) + $calendar_txt[$param['lang']]['first_day'] - 1;
	$current_day_index	= ($current_day_index == -1) ? 7 : $current_day_index;
	$current_day_index	= ($current_day_index == 7) ? 6 : $current_day_index;
	$current_day_name	= $calendar_txt[$param['lang']]['days'][$current_day_index];
	$current_month_name = $monthes_name[$current_month];
	$nb_days_month 		= date("t", $timestamp);
	
	$current_timestamp 	= mktime(23,59,59,date("m"), date("d"), date("Y"));
	
	
	
	
	### CSS
	$output  = '<style type="text/css">'."\n";
	$output .= '<!--'."\n";
	$output .= '	.calendarNav'.$param['calendar_id'].' 	{  font-family: '.$param['font_face'].'; font-size: '.($param['font_size']-1).'px; font-style: normal; background-color: '.$param['border_color'].'}'."\n";
	$output .= '	.calendarTop'.$param['calendar_id'].' 	{  font-family: '.$param['font_face'].'; font-size: '.($param['font_size']+3).'px; font-style: normal; color: '.$param['font_header_color'].'; font-weight: bold;  background-color: '.$param['border_color'].'}'."\n";
	$output .= '	.calendarToday'.$param['calendar_id'].' {  font-family: '.$param['font_face'].'; font-size: '.$param['font_size'].'px;  color: '.$param['font_today_color'].'; background-color: '.$param['today_bg_color'].';}'."\n";
	$output .= '	.calendarDays'.$param['calendar_id'].' 	{  width:'.$param['cell_width'].'; height:'.$param['cell_height'].'; font-family: '.$param['font_face'].'; font-size: '.$param['font_size'].'px; font-style: normal; color: '.$param['font_color'].'; background-color: '.$param['bg_color'].'; text-align: center}'."\n";
	$output .= '	.calendarHL'.$param['calendar_id'].' 	{  width:'.$param['cell_width'].'; height:'.$param['cell_height'].';font-family: '.$param['font_face'].'; font-size: '.$param['font_size'].'px; font-style: normal; color: '.$param['font_highlight_color'].'; background-color: '.$param['bg_highlight_color'].'; text-align: center}'."\n";
	$output .= '	.calendarHeader'.$param['calendar_id'].'{  font-family: '.$param['font_face'].'; font-size: '.($param['font_size']+3).'px; background-color: '.$param['font_nav_bg_color'].'; color: '.$param['font_nav_color'].';}'."\n";
	$output .= '	.calendarTable'.$param['calendar_id'].' {  background-color: '.$param['border_color'].'; border: 0px '.$param['border_color'].' solid}'."\n";
	$output .= '-->'."\n";
	$output .= '</style>'."\n";
	$output .= '<table width="100%" border="0" class="calendarTable'.$param['calendar_id'].'" cellpadding="2" cellspacing="1">'."\n";
	
	### Displaying the current month/year
	if ($param['show_month'] == 1) {
		$output .= '<tr>'."\n";
		$output .= '<td colspan="'.$param['calendar_columns'].'" align="center" class="calendarTop'.$param['calendar_id'].' " style="background:'.$param['bg_top'].'">'."\n";
		### Insert an img at will
		//if ($param['use_img'] ) {
		//$output .=;
		//}
		if ( $param['day_mode'] == 1 ) {
			$output .= '		'.$current_day_name.' '.$current_day.' '.$current_month_name.' '.$current_year."\n";
		}
		else {
			
			$output .= '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
			### Display the nav links on the bottom of the table
			if ($param['nav_link'] == 1) {
				$previous_month = date("Ymd", 	
					mktime( 12, 
						0, 
						0, 
						($current_month - 1),
						$current_day,
						$current_year
						)
					);
				
				$previous_day 	= date("Ymd", 	
					mktime( 12, 
						0, 
						0, 
						$current_month,
						$current_day - 1,
						$current_year
						)
					);
				$next_day 		= date("Ymd", 	
					mktime( 1, 
						12, 
						0, 
						$current_month,
						$current_day + 1,
						$current_year
						)
					);
				$next_month		= date("Ymd", 	
					mktime( 1, 
						12, 
						0, 
						$current_month + 1,
						$current_day,
						$current_year
						)
					);
				
				
				if ($param['use_img']) {
					$g 	= '<img src="images/g.gif" border="0">';
					$gg = '<img src="images/gg.gif" border="0">';
					$d 	= '<img src="images/d.gif" border="0">';
					$dd = '<img src="images/dd.gif" border="0">';
				}
				else {
					$g 	= '&lt;';
					$gg = '&lt;&lt;';
					$d = '&gt;';
					$dd = '&gt;&gt;';
				}
				
				if ( ($param['link_after_date'] == 0) 
					&& ($current_timestamp < mktime(0,0,0, $current_month, $current_day+1, $current_year))
				) {
				$next_day_link = '&nbsp;';
				}
				else {
					$next_day_link 		= '<a href="'.$PHP_SELF.'?date='.$next_day.'" title="'.$calendar_txt[$param['lang']]['misc'][3].'">'.$d.'</a>'."\n";
				}
				
				if ( ($param['link_before_date'] == 0) 
					&& ($current_timestamp > mktime(0,0,0, $current_month, $current_day-1, $current_year))
				){
				$previous_day_link = '&nbsp;';
				}
				else {
					$previous_day_link 		= '<a href="'.$PHP_SELF.'?date='.$previous_day.'" title="'.$calendar_txt[$param['lang']]['misc'][2].'">'.$g.'</a>'."\n";
				}
				
				if ( ($param['link_after_date'] == 0) 
					&& ($current_timestamp < mktime(0,0,0, $current_month+1, $current_day, $current_year))
				) {
				$next_month_link = '&nbsp;';		
				}
				else {
					$next_month_link 	= '<a href="'.$PHP_SELF.'?classe_ID='.$_GET['classe_ID'].'&date='.$next_month.'" title="'.$calendar_txt[$param['lang']]['misc'][1].'">'.$dd.'</a>'."\n";
				}
				
				if  ( ($param['link_before_date'] == 0) 
					&& ($current_timestamp >= mktime(0,0,0, $current_month-1, $current_day, $current_year))
				){
				$previous_month_link = '&nbsp;';		
				}
				else {
					$previous_month_link 	= '<a href="'.$PHP_SELF.'?classe_ID='.$_GET['classe_ID'].'&date='.$previous_month.'" title="'.$calendar_txt[$param['lang']]['misc'][0].'">'.$gg.'</a>'."\n";
				};
				
			};	
			$output .= '<tr>
			<td>&nbsp;&nbsp;'.$row_RsClasse['nom_classe'].'</td><td align="center">'.$previous_month_link.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$current_month_name.' '.$current_year.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$next_month_link.'</td> <td align="right"><a href="';
			if (isset($_SESSION['nom_prof'])){
				
				if($_SESSION['droits']==2){
					if ((isset($_GET['code_date']))&&(isset($_SESSION['devoir_planif']))&&($_SESSION['devoir_planif']=='Oui')) {
					$output .=	'enseignant/devoirs_planifies.php?code_date='.$_GET['code_date'].'&jour_pointe='.$_GET['jour_pointe'].'&current_day_name='.$_GET['current_day_name'];} 
					else 
					{$output .=	'enseignant/enseignant.php';};
				};
				if($_SESSION['droits']==3){$output .=	'vie_scolaire/vie_scolaire.php';  };
				if($_SESSION['droits']==4){$output .=	'direction/direction.php';  };
				if($_SESSION['droits']==6){$output .=	'assistant_education/assistant_educ.php';  };
				
			}
			else { $output .='consulter.php?classe_ID='.$_GET['classe_ID'].'&tri=date'; };
			$output .=	'"><img src="images/home-menu.gif" alt="Accueil" width="22" height="19" border="0" /></a></td>
			</tr>
			</table>';
			$output .="\n";
		}
		$output .= '	</td>'."\n";
		$output .= '</tr>'."\n";
	}
	
	### Building the table row with the days
	if ($param['show_day'] == 1 && $param['day_mode'] == 0) {
		$output .= '<tr align="center">'."\n";
		$first_day = $calendar_txt[$param['lang']]['first_day'];
		for ($i = $first_day; $i < 7 + $first_day; $i++) {
			
			$index = ( $i >= 7) ? (7 + $i): $i;
			$index = ($i < 0) ? (7 + $i) : $i;
			
			$day_name = ( $param['short_day_name'] == 1 ) ? substr($calendar_txt[$param['lang']]['days'][$index], 0, 9) : $calendar_txt[$param['lang']]['days'][$index];
			$output .= '	<td class="calendarHeader'.$param['calendar_id'].'" style="background:'.$param['bg_listejour'].'"><b>'.$day_name.'</b></td>'."\n";
		}
		
		$output .= '</tr>'."\n";	
		$first_decalage = $first_decalage - $calendar_txt[$param['lang']]['first_day'];
		$first_decalage = ( $first_decalage > 7 ) ? $first_decalage - 7 : $first_decalage;
	}
	else {
		$first_decalage = 0;	
	}
	
	$output .= '<tr align="center">';
	$int_counter = 0;
	
	
	
	# Filling with empty cells at the begining
	for ($i = 1; $i < $first_decalage; $i++) {
		
		//cellule vide
		$output .= '<td class="calendarDays'.$param['calendar_id'].'">&nbsp;</td>'."\n";
		$int_counter++;
	}
	### Building the table
	for ($i = 1; $i <= $nb_days_month; $i++) {
		
		//--------------------------------------------remplissage----------------------------------------------------------------
		$remplissage='';
		$current_day_index=$i%7-2+$first_decalage; 
		if ($current_day_index>=7) {$current_day_index=$current_day_index-7;};
		if ($current_day_index==-1) {$current_day_index=6;};
		$current_day_name=$calendar_txt[$param['lang']]['days'][$current_day_index];
		if ($i<10){$jour_p='0'.$i;}else{$jour_p=$i;};
		$codedateanneemoisjour=$codedateanneemois.$jour_p; //format aaaammjj
		
		//mise au format mysql aaaa-mm-jj
		$dch=substr($codedateanneemoisjour,0,4).'-'.substr($codedateanneemoisjour,4,2).'-'.substr($codedateanneemoisjour,6,2);
		//evenement
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rseven =sprintf("SELECT * FROM cdt_evenement_contenu,cdt_evenement_destinataire WHERE cdt_evenement_contenu.ID_even=cdt_evenement_destinataire.even_ID AND cdt_evenement_destinataire.classe_ID = %u AND '%s'>= cdt_evenement_contenu.date_debut   AND '%s'<= cdt_evenement_contenu.date_fin  ORDER BY date_debut", $choix_RsClasse, $dch,$dch) ;
		$Rseven = mysqli_query($conn_cahier_de_texte, $query_Rseven) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rseven = mysqli_fetch_assoc($Rseven);
		$totalRows_Rseven = mysqli_num_rows($Rseven);
		
		
		//travail a faire
		//On affiche uniquement les travaux programmes a une date seance anterieure ou egale a la date du jour
		
		$query_RsAfaire = sprintf("SELECT * FROM cdt_prof, cdt_travail, cdt_matiere, cdt_agenda WHERE cdt_travail.classe_ID=%u AND cdt_travail.matiere_ID=cdt_matiere.ID_matiere AND cdt_travail.prof_ID=cdt_prof.ID_prof  AND cdt_travail.t_jour_pointe = %s AND cdt_prof.publier_travail='O' AND cdt_agenda.ID_agenda=cdt_travail.agenda_ID ORDER BY cdt_matiere.nom_matiere,cdt_agenda.heure_debut  ",$choix_RsClasse, $codedateanneemoisjour);
		$RsAfaire = mysqli_query($conn_cahier_de_texte, $query_RsAfaire) or die(mysqli_error($conn_cahier_de_texte));
                $row_RsAfaire = mysqli_fetch_assoc($RsAfaire);
                $totalRows_RsAfaire = mysqli_num_rows($RsAfaire);
                
                //remplissage uniquement pour les jours a venir....
                if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==2)&&(isset($_SESSION['devoir_planif']))&&($_SESSION['devoir_planif']=='Oui')){
                        $remplissage .=' <a href="./enseignant/devoirs_planifies2.php?jour_pointe='.$current_day_name.' '.$jour_p.' '.$current_month_name.' '.$current_year.'&current_day_name='.$current_day_name.'&classe_ID='.$_GET['classe_ID'].'&code_date='.$codedateanneemoisjour.'0 " class="tooltip">ds+<em>Planifier un nouveau devoir</em></a><br/><br/>';
                };
		
		//affichage des evenements
		//echo $totalRows_Rseven.'  '.$row_Rseven['date_debut'].'  '.$codedateanneemoisjour.'  '.date('Ymd').'<br />';
		if (($totalRows_Rseven>0)AND($codedateanneemoisjour>=date('Ymd'))){
			
			do { 
				$remplissage .='<div class="raised">
				<b class="top"><b class="eb1"></b><b class="eb2"></b><b class="eb3"></b><b class="eb4"></b></b>
				<div class="boxcontent3">';
				$remplissage .='    <div align="center"><a href="#" class="tooltip">'.$row_Rseven['titre_even'].'<em>'.$row_Rseven['detail'].'</em></a>...</div>';
				if ($row_Rseven['date_debut']==$row_Rseven['date_fin']){
					$remplissage .=    '<div align="center">'.$row_Rseven['heure_debut'].' - '.$row_Rseven['heure_fin'].'</div>';
				};
				$remplissage .='</div>
				<b class="bottom"><b class="eb4b"></b><b class="eb3b"></b><b class="eb2b"></b><b class="eb1b"></b></b>
				</div>'; 
				
				
			} while ($row_Rseven = mysqli_fetch_assoc($Rseven)); 
		};//fin des evenements
		
		
		if (($totalRows_RsAfaire>0)AND($codedateanneemoisjour>=date('Ymd'))){
			
			do { 
				
				//On affiche uniquement les travaux programmes a une date seance anterieure ou egale a la date du jour
				//sauf si devoir programme (dernier chiffre de code_date =0) 
				$cd=date('Ymd'); 
				if ((substr($row_RsAfaire['code_date'],0,8)<=$cd)||(substr($row_RsAfaire['code_date'],8,1)==0)){
					
					$visu='Oui';
					//Si le cours a lieu a la date du jour, on affiche uniquement si l'heure horloge est superieure ou egale a l'heure de debut de cours
					if (($row_RsAfaire['t_code_date']!='') &&(substr($row_RsAfaire['code_date'],0,8) == date('Ymd'))){
						mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
						$query_heure_debut =sprintf("SELECT heure_debut FROM cdt_agenda where ID_agenda=%u",$row_RsAfaire['agenda_ID']);
						$Rs_h= mysqli_query($conn_cahier_de_texte, $query_heure_debut) or die(mysqli_error($conn_cahier_de_texte));
						$row_Rs_h = mysqli_fetch_assoc($Rs_h);
						$heure_actuelle=date('Hi',time());
						$heure_seance=substr($row_Rs_h['heure_debut'],0,2).substr($row_Rs_h['heure_debut'],3,2) ;
						if ($heure_seance>$heure_actuelle){$visu='Non';};
					};
					
					
					if ($visu=='Oui'){
						
						$remplissage .='<div class="raised">
						<b class="top"><b class="b1"></b><b class="b2"></b><b class="b3"></b><b class="b4"></b></b>
						<div class="boxcontent">';
						
						$remplissage .=    '<div align="center"><b>'.$row_RsAfaire['nom_matiere'].'</b></div>';
						
						
						
						
						//si devoir, lien pour modification
						if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==2)&&($_SESSION['ID_prof']==$row_RsAfaire['prof_ID'])&&(substr($row_RsAfaire['code_date'],8,1)==0)&&(substr($row_RsAfaire['t_code_date'],2,1)=='/')){
							$remplissage .='<div style="float:left">&nbsp;&nbsp;&nbsp;&nbsp;'.$row_RsAfaire['identite'].'&nbsp;   </div>';
							echo $row_RsAfaire['code_date'];
							$remplissage .='<div style="float:left">
							
							<form name="form_edition" enctype="multipart/form-data" action="enseignant/agenda_supprime.php?ds_prog&nom_classe=';
							$remplissage .=$row_RsClasse['nom_classe'];
							$remplissage .='&classe_ID='.$row_RsAfaire['classe_ID'];
							
							$remplissage .='&gic_ID='.$row_RsAfaire['gic_ID'];
							
							
							$remplissage .='&nom_matiere='.$row_RsAfaire['nom_matiere'].'&groupe='.$row_RsAfaire['groupe'].'&matiere_ID='.$row_RsAfaire['matiere_ID'].'&semaine='.$row_RsAfaire['semaine'].'&jour_pointe='.$row_RsAfaire['jour_pointe'].'&heure='.$row_RsAfaire['heure'].'&duree='.$row_RsAfaire['duree'].'&heure_debut='.$row_RsAfaire['heure_debut'].'&heure_fin='.$row_RsAfaire['heure_fin'].'&current_day_name='.$current_day_name.'&code_date='.$row_RsAfaire['code_date'].'&ID_agenda='.$row_RsAfaire['agenda_ID'].' " method="post">
							<input name="img_edit" type="image" src="./images/ed_delete.gif" alt="Supprimer ce devoir" title="Supprimer ce devoir">
							&nbsp;</form></div>';
							
							$remplissage .='<div style="float:left"><form name="form_edition" enctype="multipart/form-data" action="enseignant/ecrire.php?ds_prog&nom_classe=';
							$remplissage .=$row_RsClasse['nom_classe'];
							
							
							
							$remplissage .='&classe_ID='.$row_RsAfaire['classe_ID'];
							$remplissage .='&gic_ID='.$row_RsAfaire['gic_ID'];
							$remplissage .='&nom_matiere='.$row_RsAfaire['nom_matiere'].'&groupe='.$row_RsAfaire['groupe'].'&matiere_ID='.$row_RsAfaire['matiere_ID'].'&semaine='.$row_RsAfaire['semaine'].'&jour_pointe='.$row_RsAfaire['jour_pointe'].'&heure='.$row_RsAfaire['heure'].'&duree='.$row_RsAfaire['duree'].'&heure_debut='.$row_RsAfaire['heure_debut'].'&heure_fin='.$row_RsAfaire['heure_fin'].'&current_day_name='.$current_day_name.'&code_date='.$row_RsAfaire['code_date'].' " method="post">
							<input name="img_edit" type="image" src="./images/button_edit.png" alt="Modifier ce devoir" title="Modifier ce devoir">
							</form></div> ';
							
							//fin si devoir, lien pour modification
						} 
						else {$remplissage .=''.$row_RsAfaire['identite'];};
						
						
						$remplissage .=    '<div align="center">';
						
						
						//si c'est  un devoir, il faut mettre l'heure du devoir
						if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==2)&&($_SESSION['ID_prof']==$row_RsAfaire['prof_ID'])&&(substr($row_RsAfaire['code_date'],8,1)==0)&&(substr($row_RsAfaire['t_code_date'],2,1)=='/')){
							if (substr($row_RsAfaire['heure_debut'],0,1)==0){$hd=substr($row_RsAfaire['heure_debut'],1,strlen($row_RsAfaire['heure_debut']));}else{$hd=$row_RsAfaire['heure_debut'];};
							$remplissage .= $hd;
						}
						else
						//si c'est un travail a faire et non un devoir, il faut mettre l'heure du prochain cours...mais cette heure n'a pas ete enregistree !!
						//recherche donc dans l'emploi du temps de la premiere heure de cours dans cette matiere
						{
							
							if ( $row_RsAfaire['gic_ID']>0) {//regroupement 
								$query_Rs_heure_deb = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE  cdt_emploi_du_temps.gic_ID=%u  AND cdt_emploi_du_temps.matiere_ID=%u AND cdt_emploi_du_temps.jour_semaine='%s' AND cdt_emploi_du_temps.groupe='%s'",$row_RsAfaire['gic_ID'],$row_RsAfaire['matiere_ID'],$current_day_name,$row_RsAfaire['t_groupe']);  
							}
							else  //classe normale
							{
								$query_Rs_heure_deb = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE  cdt_emploi_du_temps.classe_ID=%u  AND cdt_emploi_du_temps.matiere_ID=%u AND cdt_emploi_du_temps.jour_semaine='%s' AND cdt_emploi_du_temps.groupe='%s'",$choix_RsClasse,$row_RsAfaire['matiere_ID'],$current_day_name,$row_RsAfaire['t_groupe']);  
							};
							
							$Rs_heure_deb = mysqli_query($conn_cahier_de_texte, $query_Rs_heure_deb) or die(mysqli_error($conn_cahier_de_texte));
							$row_Rs_heure_deb = mysqli_fetch_assoc($Rs_heure_deb);
							$remplissage .= $row_Rs_heure_deb['heure_debut'];
							
						};
						
						if ($row_RsAfaire['duree']<>''){$remplissage .='('.$row_RsAfaire['duree'].')'.' ';};
						if ($row_RsAfaire['gic_ID']<>0){
							//regroupement / retrouver le nom
							$query_Rsgic = sprintf("SELECT nom_gic,commentaire_gic FROM cdt_groupe_interclasses  WHERE ID_gic=%u",$row_RsAfaire['gic_ID']);
							$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
							$row_Rsgic = mysqli_fetch_assoc($Rsgic);
							$remplissage .= '<br />(R) '.$row_Rsgic['nom_gic'];
						}
						$remplissage .='<br />'.$row_RsAfaire['groupe'].'</div>';
						
						if ((substr($row_RsAfaire['code_date'],8,1)==0)&&(substr($row_RsAfaire['t_code_date'],2,1)=='/')){
							
							$remplissage .=    '<div align="center" style="color:#FF0000" ><b>'.$_SESSION['libelle_devoir'].'</b></div>';
							
							
							$remplissage .=    '<div align="center" style="color:#0000FF">'.$row_RsAfaire['theme_activ'].'</div>';
							
							
							$remplissage .='    <div align="center"><a href="#" class="tooltip">A revoir<em>'.$row_RsAfaire['travail'].'</em></a>...</div>';
							
						}else{
							
							$remplissage .='    <div align="center"><a href="#" class="tooltip">A faire<em>'.strip_tags($row_RsAfaire['travail']).'<br /><i>Pour plus de d&eacute;tails, consulter la page travail &agrave; faire.</i></em></a></div>';
							
						};
						$remplissage .='</div>
						<b class="bottom"><b class="b4b"></b><b class="b3b"></b><b class="b2b"></b><b class="b1b"></b></b>
						</div>'; 
						$remplissage .='<br />';
					}
				};
				
			} while ($row_RsAfaire = mysqli_fetch_assoc($RsAfaire)); 
		};
		
		mysqli_free_result($RsAfaire);
		
		
		//-------------------------------------------fin -remplissage----------------------------------------------------------------	
		
		### Do we highlight the current day ?
		$i_2 = ($i < 10) ? '0'.$i : $i;		
		$highlight_current = ( isset($param['highlight'][date('Ym', $timestamp).$i_2]) );	
		### Row start
		if ( ($i + $first_decalage) % $param['calendar_columns'] == 2 && $i != 1) {
			$output .= '<tr align="center">'."\n";
			$int_counter = 0;
		}
		
		$css_2_use = ( $highlight_current ) ? 'HL' : 'Days';
		$txt_2_use = ( $highlight_current && $param['highlight_type'] == 'text') ? '<br>'.$param['highlight'][date('Ym', $timestamp).$i_2] : '';
		
		//################# VACANCES #################		
		
		
		$nv=0;$pv=0;
		$code_d=$current_year.$current_month_2.$i_2;
		do { 
			$nv=$nv+1;	
			
			if (($code_d>=$tab_debut[$nv])&&($code_d<=$tab_fin[$nv])) { $pv=1;} 
			
		}	  
		while ($nv<$totalRows_Vacances); 
		
		//##############################################
		//formatage du mois actuel
		if (date('m')<10){$m_actu=substr(date('m'),1,1);} else {$m_actu=date('m');};
	        
		if (($i == $current_day)&&($m_actu==$current_month)) { 
			$output .= '<td ';
			//vacances
			if ($pv==1){ $output .=' class="calendarToday1"> '.$i;} 
			else { 
				//jour courant
				
				$output .= 'class="calendarToday'.$param['calendar_id'].'" width="10%" ';
				if ($totalRows_RsAfaire>0){$output .=' valign="top" ';};
				$output .=	'><b>'.$i.' </b>'.$remplissage;
				
				$output .='</td>'."\n";
			}
			$jour_pointe= $current_day_name. '   '.$i. '    '.$current_month_name. '    '.$current_year; 
			$jj=$i;
		}
		elseif ($param['link_on_day'] != '') {
			$loop_timestamp = mktime(0,0,0, $current_month, $i, $current_year);
			
			if (( ($param['link_after_date'] == 0) && ($current_timestamp < $loop_timestamp)) || (($param['link_before_date'] == 0) && ($current_timestamp >= $loop_timestamp)) ){
				$output .= '<td class="calendar'.$css_2_use.$param['calendar_id'].'">'.$i.$txt_2_use.'</td>'."\n";
			}
			else {
				
				$output .= '<td width="10%" height="50" valign="top"';
				
				//coloration vacances	ou remplissage		
				
				if ($pv==1){ $output .=' bgcolor="#EBF0F4"> <span style="font-size: 10px">'.$i.'</span>';} 
				else { 
					
					
					$output .=' class="calendarDays1"';
					if ($totalRows_RsAfaire>0){$output .=' valign="top" ';};
					$output .=	'><b>'.$i.'</b>'.$remplissage;
					
					
					//fin remplissage
				}
				
				
				
				
				$output .=$txt_2_use;
				$output .='</td>'."\n";
				
				
				
				
			}
		}
		else {
			$output .= '<td class="calendar'.$css_2_use.$param['calendar_id'].'">'.$i.'</td>'."\n";
		}	
		$int_counter++;
		
		### Row end
		if (  ($i + $first_decalage) % ($param['calendar_columns'] ) == 1 ) {
			$output .= '</tr>'."\n";	
		}
	}
	$cell_missing = $param['calendar_columns'] - $int_counter;
	
	for ($i = 0; $i < $cell_missing; $i++) {
		$output .= '<td class="calendarDays'.$param['calendar_id'].'">&nbsp;</td>'."\n";
	}
	$output .= '</tr>'."\n";
	
	### Building the table row with the days
	if ($param['show_day'] == 1 && $param['day_mode'] == 0) {
		$output .= '<tr align="center">'."\n";
		$first_day = $calendar_txt[$param['lang']]['first_day'];
		for ($i = $first_day; $i < 7 + $first_day; $i++) {
			
			$index = ( $i >= 7) ? (7 + $i): $i;
			$index = ($i < 0) ? (7 + $i) : $i;
			
			$day_name = ( $param['short_day_name'] == 1 ) ? substr($calendar_txt[$param['lang']]['days'][$index], 0, 9) : $calendar_txt[$param['lang']]['days'][$index];
			$output .= '	<td class="calendarHeader'.$param['calendar_id'].'" style="background:'.$param['bg_listejour'].'"><b>'.$day_name.'</b></td>'."\n";
		}
		
		$output .= '</tr>'."\n";	
		$first_decalage = $first_decalage - $calendar_txt[$param['lang']]['first_day'];
		$first_decalage = ( $first_decalage > 7 ) ? $first_decalage - 7 : $first_decalage;
	}
	else {
		$first_decalage = 0;	
	};
	
	
	$output .= '</table>'."\n";
	return $output;
}




function priv_reg_glob_calendar($var) {
	Global $_GET, $_POST;
	
	if (isset($_GET[$var])) {
		return $_GET[$var];
	}
	elseif (isset($_POST[$var])) {
		return $_POST[$var];
	}
	else {
		return '';
	}	
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Planning du travail &agrave; faire</title>
<link href="styles/info_bulles.css" rel="stylesheet" type="text/css" />
<link href="styles/arrondis.css" rel="stylesheet" type="text/css" />
<link href="styles/style_default.css" rel="stylesheet" type="text/css">
<link href="templates/default/perso.css" rel="stylesheet" type="text/css">
</head>
<body>
<p>
<?php
echo calendar();?>
</p>
<p>
<?php if ((isset($_SESSION['droits']))&&(($_SESSION['droits']==3) OR ($_SESSION['droits']==4))){  ?>
	<a href="vie_scolaire/evenement_ajout.php">Ajouter un &eacute;v&eacute;nement</a>
<?php };?>
<?php if (isset($_SESSION['droits'])&&($_SESSION['droits']==2)	) { 
	//prof principal ? 
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rspp =sprintf("SELECT * FROM cdt_prof_principal,cdt_groupe WHERE pp_prof_ID=%u",$_SESSION['ID_prof']);
	$Rspp = mysqli_query($conn_cahier_de_texte, $query_Rspp) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rspp = mysqli_fetch_assoc($Rspp);
	$totalRows_Rspp = mysqli_num_rows($Rspp);
	if ($totalRows_Rspp>0){ ?>
		<a href="enseignant/evenement_select.php">Ajouter un &eacute;v&eacute;nement</a>
	<?php };?>
	<?php
;};


//affichage des messages vie scolaire et prof principal adresses a l'ensemble des classes
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsmessage_tous ="SELECT *FROM cdt_message_contenu,cdt_prof WHERE cdt_message_contenu.dest_ID =1
AND cdt_message_contenu.online = 'O' AND cdt_message_contenu.prof_ID = cdt_prof.ID_prof" ;
$Rsmessage_tous = mysqli_query($conn_cahier_de_texte, $query_Rsmessage_tous) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmessage_tous = mysqli_fetch_assoc($Rsmessage_tous);
$totalRows_Rsmessage_tous = mysqli_num_rows($Rsmessage_tous);

//affichage des messages vie scolaire et prof principal adresses a une ou plusieurs classes
$choix_groupe_sql2='';
if (isset($_POST['groupe'])){ 
        
        if ($_POST['groupe']<>"Classe entière"){$choix_groupe_sql2="AND (cdt_message_destinataire.groupe_ID = ". GetSQLValueString(strip_tags($_POST['groupe']),"text")." OR cdt_message_destinataire.groupe_ID = 'Classe entière' )";};
};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsmessage =sprintf("SELECT * FROM cdt_message_contenu,cdt_message_destinataire,cdt_groupe,cdt_prof WHERE cdt_message_contenu.dest_ID=0 AND cdt_message_contenu.ID_message=cdt_message_destinataire.message_ID AND cdt_groupe.ID_groupe=cdt_message_destinataire.groupe_ID AND cdt_message_contenu.prof_ID= cdt_prof.ID_prof AND cdt_message_contenu.online='O' AND cdt_message_destinataire.classe_ID = %s %s",$choix_RsClasse,$choix_groupe_sql2) ;
$Rsmessage = mysqli_query($conn_cahier_de_texte, $query_Rsmessage) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmessage = mysqli_fetch_assoc($Rsmessage);
$totalRows_Rsmessage = mysqli_num_rows($Rsmessage);


if ( $totalRows_Rsmessage_tous + $totalRows_Rsmessage >0) {	?>
	<p>&nbsp;</p>
	<table class="lire_bordure" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
	<tr class="lire_cellule_4">
	<td >Informations </td>
	<td><div align="right">&nbsp;</div></td>
	</tr>
	<tr class="lire_cellule_2">
	<td width="15%"  valign="middle" >&nbsp;</td>
	<td width="85%"  valign="middle" >
	<table class="lire_bordure" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
	<?php 
	if ($totalRows_Rsmessage_tous>0){  
		do { ?>
			<tr>
			<td width="10%" class="tab_detail"><?php 
			$date_envoi_form=substr($row_Rsmessage_tous['date_envoi'],8,2).'/'.substr($row_Rsmessage_tous['date_envoi'],5,2).'/'.substr($row_Rsmessage_tous['date_envoi'],2,2);
			
			echo '<span class="date_message">'.$date_envoi_form.'<br />'.$row_Rsmessage_tous['identite'].'</span>';
			?>
			<br /></td>
			<td class="tab_detail"><p>
			<?php  
			echo $row_Rsmessage_tous['message'];?>
			</p>
			<?php
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE cdt_message_fichiers.message_ID=".$row_Rsmessage_tous['ID_message'];
			$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
			$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
			$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
			if ($totalRows_Rs_fichiers_joints_form>0){
				if ($totalRows_Rs_fichiers_joints_form>1){echo '<p>Documents joints : <br /> ';} else {echo '<p>Document joint : ';};
				do {
					$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
					echo '<a href="./fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a><br />';
				} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
			echo '</p>';};?>
			</td>
			</tr>
		<?php } while ($row_Rsmessage_tous = mysqli_fetch_assoc($Rsmessage_tous));	
	};
	
	if ($totalRows_Rsmessage>0){ 
		do { ?>
			<tr>
			<td width="10%" class="tab_detail"><?php 
			$date_envoi_form=substr($row_Rsmessage['date_envoi'],8,2).'/'.substr($row_Rsmessage['date_envoi'],5,2).'/'.substr($row_Rsmessage['date_envoi'],2,2);
			
			echo '<span class="date_message">'.$date_envoi_form.'<br />'.$row_Rsmessage['identite'].'</span>';
			?>
			<br /></td>
			<td class="tab_detail"><p>
			<?php  
			
			if ($row_Rsmessage['groupe_ID']>1){echo 'A l\'attention du groupe <b>'.$row_Rsmessage['groupe']. '</b> <br /> ';};
			echo $row_Rsmessage['message'];?>
			</p>
			<?php
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE cdt_message_fichiers.message_ID=".$row_Rsmessage['ID_message'];
			$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
			$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
			$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
			if ($totalRows_Rs_fichiers_joints_form>0){
				if ($totalRows_Rs_fichiers_joints_form>1){echo '<p>Documents joints : <br /> ';} else {echo '<p>Document joint : ';};
				do {
					$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
					echo '<a href="./fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a><br />';
				} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
			echo '</p>';};?>
			</td>
			</tr>
		<?php } while ($row_Rsmessage = mysqli_fetch_assoc($Rsmessage));		
	}; ?>
	</table>
	
	</td>
	</tr>
</table> <?php };?>
</p>
</body>
</html>

<?php
if (isset($Vacances)){mysqli_free_result($Vacances);};
if (isset($RsClasse)){mysqli_free_result($RsClasse);};
if (isset($Rseven)){mysqli_free_result($Rseven);};
if (isset($RsAfaire)){mysqli_free_result($RsAfaire);};

if (isset($Rs_h)){mysqli_free_result($Rs_h);};
if (isset($Rs_heure_deb)){mysqli_free_result($Rs_heure_deb);};
if (isset($Rsgic)){mysqli_free_result($Rsgic);};
if (isset($Rspp)){mysqli_free_result($Rspp);};

if (isset($Rsmessage_tous)){mysqli_free_result($Rsmessage_tous);};
if (isset($Rsmessage)){mysqli_free_result($Rsmessage);};
if (isset($Rs_fichiers_joints_form)){mysqli_free_result($Rs_fichiers_joints_form);};
?>
