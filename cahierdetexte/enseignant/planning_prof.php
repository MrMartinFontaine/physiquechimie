<?php 

include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};

require_once('../Connections/conn_cahier_de_texte.php'); 

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

mysqli_free_result($Vacances);

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
	Global $r; //pour la creation des styles raised
	
	$choix_RsClasse = "0";
	if (isset($_GET['classe_ID'])) {
		$choix_RsClasse = (get_magic_quotes_gpc()) ? intval($_GET['classe_ID']) : addslashes(intval($_GET['classe_ID']));
	}
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	
	$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE ID_classe=%u", $choix_RsClasse);
	$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsClasse = mysqli_fetch_assoc($RsClasse);
	
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
	$param_d['bg_listejour']		= 'url(../images/bande_bleue.jpg)';
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
	$r=0;
	
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
					$g 	= '<img src="../images/g.gif" border="0">';
					$gg = '<img src="../images/gg.gif" border="0">';
					$d 	= '<img src="../images/d.gif" border="0">';
					$dd = '<img src="../images/dd.gif" border="0">';
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
					$next_month_link 	= '<a href="'.$PHP_SELF.'?date='.$next_month.'" title="'.$calendar_txt[$param['lang']]['misc'][1].'">'.$dd.'</a>'."\n";
				}
				
				if  ( ($param['link_before_date'] == 0) 
					&& ($current_timestamp >= mktime(0,0,0, $current_month-1, $current_day, $current_year))
				){
				$previous_month_link = '&nbsp;';		
				}
				else {
					$previous_month_link 	= '<a href="'.$PHP_SELF.'?date='.$previous_month.'" title="'.$calendar_txt[$param['lang']]['misc'][0].'">'.$gg.'</a>'."\n";
				};
				
			};	
			$output .= '<tr>
			<td>&nbsp;&nbsp;'.$row_RsClasse['nom_classe'].'</td><td align="center">'.$previous_month_link.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$current_month_name.' '.$current_year.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$next_month_link.'</td> <td align="right"><a href="';
			if (isset($_SESSION['nom_prof'])){
				
				if (($_SESSION['droits']==2)||($_SESSION['droits']==8)){
					if (isset($_GET['code_date'])){
					$output .=	'devoirs_planifies.php?code_date='.$_GET['code_date'].'&jour_pointe='.$_GET['jour_pointe'];} 
					else 
					{$output .=	'ecrire.php?date='.date('Ymd');};
				};
				if($_SESSION['droits']==3){$output .=	'../vie_scolaire/vie_scolaire.php';  };
				if($_SESSION['droits']==4){$output .=	'../direction/direction.php';  };
				
			}
			else { $output .='consulter.php?classe_ID='.$_GET['classe_ID'].'&tri=date'; };
			$output .=	'"><img src="../images/home-menu.gif" alt="Accueil" width="22" height="19" border="0" /></a></td>
			</tr>
			</table>';
			$output .="\n";
		}
		$output .= '	</td>'."\n";
		$output .= '</tr>'."\n";
	}
	mysqli_free_result($RsClasse);
	
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
		include('planning_inc.php');       
		//formatage du mois actuel
		if (date('m')<10){$m_actu=substr(date('m'),1,1);} else {$m_actu=date('m');};
	        
		if (($i == $current_day)&&($m_actu==$current_month)) { 
			$output .= '<td ';
			//vacances
			if ($pv==1){ $output .=' class="calendarToday1"> '.$i;} 
			else { 
				//jour courant
				
				//affichage du jour $i date du jour
				$output .=' class="calendarDays1_planning" valign="top"';
				$output .=	'><div align="center"  style="color:#FF0000";cursor:pointer"><b>'.$i.'</b></div>'.$remplissage;
				
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
				
				$output .= '<td width="10%"  valign="top" ';
				
				//coloration vacances ou remplissage		
				
				if ($pv==1){ $output .=' bgcolor="#EBF0F4"> <div align="center">'.$i.'</div>';} 
				else { 
					
					
					$output .=' class="calendarDays1_planning" style="cursor:pointer"';
					//affichage du jour $i autres jours
					$output .=	'><div align="center" >'.$i.'</div>'.$remplissage;
					
					
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
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>

<link href="../styles/info_bulles.css" rel="stylesheet" type="text/css" />
<link href="../styles/arrondis.css" rel="stylesheet" type="text/css" />
<link href="../styles/style_default.css" rel="stylesheet" type="text/css">
<link href="../templates/default/perso.css" rel="stylesheet" type="text/css">
<style>
.calendarDays1_planning 	{  width:100; height:50; font-family: Verdana, Arial, Helvetica; font-size: 9px; font-style: normal; color: #000066; background-color: #FFFFFF; text-align: left ;valign: top;}
</style>
<script type="text/JavaScript">
<!--
function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
//-->
</script>
</head>

<body>
<p>
<?php
echo calendar();?>

</p>
<p><?php if ((isset($_SESSION['droits']))&&(($_SESSION['droits']==3) OR ($_SESSION['droits']==4))){  ?> 
<a href="../vie_scolaire/evenement_ajout.php">Ajouter un &eacute;v&eacute;nement</a><?php };?> 
<?php 
//affichage des messages vie scolaire et prof principal
$choix_groupe_sql2='';
if (isset($_POST['groupe'])){ 
        
        if ($_POST['groupe']<>"Classe entière"){$choix_groupe_sql2="AND (cdt_message_destinataire.groupe_ID = ". GetSQLValueString(strip_tags($_POST['groupe']),"text")." OR cdt_message_destinataire.groupe_ID = 'Classe entière' )";};
};

?>



</p>
</body>
</html>
