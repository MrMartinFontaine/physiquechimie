<?php require_once('../Connections/conn_cahier_de_texte.php');?>

<?php

/***************************************************************************
____  _   _ ____  _              _     _  _   _   _
|  _ \| | | |  _ \| |_ ___   ___ | |___| || | | | | |
| |_) | |_| | |_) | __/ _ \ / _ \| / __| || |_| | | |
|  __/|  _  |  __/| || (_) | (_) | \__ \__   _| |_| |
|_|   |_| |_|_|    \__\___/ \___/|_|___/  |_|  \___/

calendrier.php  -  A calendar
-------------------
begin                : June 2002
Version				 : 2.1 (Jan 04)
copyleft             : (C) 2002-2003 PHPtools4U.com - Mathieu LESNIAK
email                : support@phptools4u.com
***************************************************************************/

/***************************************************************************
*
*   This program is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 2 of the License, or
*   (at your option) any later version.
*
***************************************************************************/


### French Version
$calendar_txt['french']['monthes']          = array('', 'Janvier', 'F&eacute;vrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet',
        'Ao&ucirc;t', 'Septembre', 'Octobre', 'Novembre', 'D&eacute;cembre');
$calendar_txt['french']['days']             = array('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche');
$calendar_txt['french']['first_day']    = 0;
$calendar_txt['french']['misc']             = array('Mois pr&eacute;c&eacute;dent', 'Mois suivant','Jour pr&eacute;c&eacute;dent', 'Jour suivant');

function calendar($access = 'Non', $date='') {
        Global $link_on_day, $PHP_SELF, $params;
	Global $_POST, $_GET;
	Global $calendar_txt;
	Global $jour_pointe;
	Global $current_day_name;
	Global $current_day;
	Global $current_month;
	Global $current_year;
        Global $current_month_2;
        Global $tab_debut;
        Global $tab_fin;
        Global $totalRows_Vacances; //nb de periodes de vacances
        Global $totalRows_RsArchiv;
        Global $en_vacance_today;
        
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
	$param_d['font_today_color']	= '#990000';
	$param_d['font_color']			= '#000000';
	$param_d['font_nav_bg_color']	= '#A9B4B3';
	
	$param_d['font_nav_color']		= '#FFFFFF';
	$param_d['font_header_color']	= '#FFFFFF';
        $param_d['use_img']                             = 1; // Use gif for nav bar on the bottom
        
        
    ### Specifique au cahier de textes
        $param_d['border_color']                = '#0F5080';
        $param_d['bg_listejour']                = '#BBCEDE';
        $param_d['bg_top']                      = 'url(../images/bande_bleue.jpg)';
	    
		###cellules vacances
		//$bg_vacances='#ef8c05';
		$bg_vacances='#F8CE70';
		
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
	$param_d['cell_width']          = 20;
	$param_d['cell_height']         = 20;
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
	
	if (isset($_GET['code_date'])){$date=substr($_GET['code_date'],0,8);};
	
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
	
	
	$en_vacance_today = 0;
	
	### CSS
	$output  = '<style type="text/css">'."\n";
	$output .= '<!--'."\n";
	$output .= '	.calendarNav'.$param['calendar_id'].' 	{  font-family: '.$param['font_face'].'; font-size: '.($param['font_size']-1).'px; font-style: normal; background-color: '.$param['border_color'].'}'."\n";
	$output .= '	.calendarTop'.$param['calendar_id'].' 	{  font-family: '.$param['font_face'].'; font-size: '.($param['font_size']+1).'px; font-style: normal; color: '.$param['font_header_color'].'; font-weight: bold;  background-color: '.$param['border_color'].'}'."\n";
	$output .= '	.calendarToday'.$param['calendar_id'].' {  font-family: '.$param['font_face'].'; font-size: '.$param['font_size'].'px; font-weight: bold; color: '.$param['font_today_color'].'; background-color: '.$param['today_bg_color'].';}'."\n";
	$output .= '	.calendarDays'.$param['calendar_id'].' 	{  width:'.$param['cell_width'].'; height:'.$param['cell_height'].'; font-family: '.$param['font_face'].'; font-size: '.$param['font_size'].'px; font-style: normal; color: '.$param['font_color'].'; background-color: '.$param['bg_color'].'; text-align: center}'."\n";
	$output .= '	.calendarHL'.$param['calendar_id'].' 	{  width:'.$param['cell_width'].'; height:'.$param['cell_height'].';font-family: '.$param['font_face'].'; font-size: '.$param['font_size'].'px; font-style: normal; color: '.$param['font_highlight_color'].'; background-color: '.$param['bg_highlight_color'].'; text-align: center}'."\n";
	$output .= '	.calendarHeader'.$param['calendar_id'].'{  font-family: '.$param['font_face'].'; font-size: '.($param['font_size']-1).'px; background-color: '.$param['font_nav_bg_color'].'; color: '.$param['font_nav_color'].';}'."\n";
	$output .= '	.calendarTable'.$param['calendar_id'].' {  background-color: '.$param['border_color'].'; border: 0px '.$param['border_color'].' solid}'."\n";
	$output .= '-->'."\n";
	$output .= '</style>'."\n";
	$output .= '<table border="0" class="calendarTable'.$param['calendar_id'].'" cellpadding="2" cellspacing="1">'."\n";
	
	### Displaying the current month/year
	if ($param['show_month'] == 1) {
		$output .= '<tr>'."\n";
		$output .= '	<td colspan="'.$param['calendar_columns'].'" align="center" class="calendarTop'.$param['calendar_id'].' " style="background:'.$param['bg_top'].'">'."\n";
		### Insert an img at will
		
		if ($param['use_img'] && $access=='Oui') { //ancien cahier dans une autre base
			$output .= '<a href="../archive/archive_menu.php" target="_blank" ><img src="../images/mois.gif" title="Consulter les archives" alt="Consulter les archives" border="0"></a> ';
		}
		else
		{
			//acces archivage dans le cas de duplication de tables
			if ($totalRows_RsArchiv!=0) {
				$output .= '<a href="../archive/archive_menu2.php" target="_blank"><img src="../images/mois.gif" title="Consulter les archives" alt="Consulter les archives" border="0"></a> ';
			}		
		}
		
		if ( $param['day_mode'] == 1 ) {
			$output .= '		'.$current_day_name.' '.$current_day.' '.$current_month_name.' '.$current_year."\n";
		}
		else {
			$output .= '		'.$current_month_name.' '.$current_year."\n";
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
			
			$day_name = ( $param['short_day_name'] == 1 ) ? substr($calendar_txt[$param['lang']]['days'][$index], 0, 1) : $calendar_txt[$param['lang']]['days'][$index];
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
	
	
	if ( $param['day_mode'] == 1 ) {
		list($hour_start, $min_start) 	= explode(':', $param['time_start']);
		list($hour_end, $min_end)		= explode(':', $param['time_stop']);
		$ts_start 	= ( $hour_start * 60 ) + $min_start;
		$ts_end		= ( $hour_end * 60 ) + $min_end;
		$nb_steps	= ceil( ($ts_end - $ts_start) / $param['time_step'] );
		
		for ( $i = 0; $i <= $nb_steps; $i++ ) {
			$current_ts		= ($ts_start) + $i * $param['time_step'];
			$current_hour 	= floor($current_ts / 60);
			$current_min 	= $current_ts % 60;
			$current_hour 	= (strlen($current_hour) < 2) ? '0'.$current_hour : $current_hour;
			$current_min 	= (strlen($current_min) < 2) ? '0'.$current_min : $current_min;
			
   			$highlight_current  = ( isset($param['highlight'][date('Ymd', $timestamp).$current_hour.$current_min]) );
   			$css_2_use          = ( $highlight_current ) ? 'HL' : 'Days';
   			$txt_2_use          = ( $highlight_current && $param['highlight_type'] == 'text') ? $param['highlight'][date('Ymd', $timestamp).$current_hour.$current_min] : '';
   			
			$output .= '<tr>'."\n";
			if ( $param['link_on_hour'] != '') {
				$output .= '	<td class="calendar'.$css_2_use.$param['calendar_id'].'" width="10%"><a href="'.str_replace('%%hh%%', date('Ymd', $timestamp).$current_hour.$current_min, $param['link_on_hour']).'">'.$current_hour.':'.$current_min.'</a></td>'."\n";
			}
			else {
				$output .= '	<td class="calendar'.$css_2_use.$param['calendar_id'].'" width="10%">'.$current_hour.':'.$current_min.'</td>'."\n";
			}
			$output .= '    <td class="calendar'.$css_2_use.$param['calendar_id'].'">'.$txt_2_use.'</td>	'."\n";
			$output .= '</tr>'."\n";	
		}
		
	}
	else {
		# Filling with empty cells at the begining
		for ($i = 1; $i < $first_decalage; $i++) {
			$output .= '<td class="calendarDays'.$param['calendar_id'].'">&nbsp;</td>'."\n";
			$int_counter++;
		}
		### Building the table
		for ($i = 1; $i <= $nb_days_month; $i++) {
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
                        
                        if ($i == $current_day) { 
                                $output .= '<td ';
                                if ($pv==1){ 
                                        $output .=' class="calendarToday1"> '.$i;
                                $en_vacance_today=1;} 
				else {
					$output .= 'class="calendarToday'.$param['calendar_id'].'" align="center"><a href="'.str_replace('%%dd%%', $current_year.$current_month_2.$i_2,$param['link_on_day']).'" ';
if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){$output .= ' onClick="return saisie_abandon();"';};					
					$output .= '>'.$i.$txt_2_use.'</A></td>'."\n";
				}
				$jour_pointe= $current_day_name. '    '.$i. '    '.$current_month_name. '    '.$current_year; 
				$jj=$i;
			}
			elseif ($param['link_on_day'] != '') {
				$loop_timestamp = mktime(0,0,0, $current_month, $i, $current_year);
				
				if (( ($param['link_after_date'] == 0) && ($current_timestamp < $loop_timestamp)) || (($param['link_before_date'] == 0) && ($current_timestamp >= $loop_timestamp)) ){
					$output .= '<td class="calendar'.$css_2_use.$param['calendar_id'].'">'.$i.$txt_2_use.'</td>'."\n";
				}
				else {
					
					$output .= '<td ';
					
					//coloration vacances				
					
					if ($pv==1){ $output .=' bgcolor="'.$bg_vacances.'"> <span style="font-size: 10px;color:#fff"><a href="'.str_replace('%%dd%%', $current_year.$current_month_2.$i_2,$param['link_on_day']).'" ';
					
					if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){$output .= ' onClick="return saisie_abandon();"';};
					
					$output .='>'.$i.'</a></span>';} 
					else { $output .='class="calendarDays1"'.'"><a href="'.str_replace('%%dd%%', $current_year.$current_month_2.$i_2,$param['link_on_day']).'"';
					if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){$output .= ' onClick="return saisie_abandon();"';};
					 $output .='>'.$i.'</a>';}
					
					
					
					
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
	}
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
			$next_day_link = '<a href="'.$PHP_SELF.'?date='.$next_day.'" title="'.$calendar_txt[$param['lang']]['misc'][3].'" ';
			if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){$next_day_link .= ' onClick="return saisie_abandon();"';};
			$next_day_link .='>'.$d.'</a>'."\n";
		}
		
		if ( ($param['link_before_date'] == 0) 
			&& ($current_timestamp > mktime(0,0,0, $current_month, $current_day-1, $current_year))
		){
		$previous_day_link = '&nbsp;';
		}
		else {
			$previous_day_link 		= '<a href="'.$PHP_SELF.'?date='.$previous_day.'" title="'.$calendar_txt[$param['lang']]['misc'][2].'"';
			if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){$previous_day_link.= ' onClick="return saisie_abandon();"';};
			$previous_day_link.= '>'.$g.'</a>'."\n";
		}
		
		if ( ($param['link_after_date'] == 0) 
			&& ($current_timestamp < mktime(0,0,0, $current_month+1, $current_day, $current_year))
		) {
		$next_month_link = '&nbsp;';		
		}
		else {
			$next_month_link 	= '<a href="'.$PHP_SELF.'?date='.$next_month.'" title="'.$calendar_txt[$param['lang']]['misc'][1].'" ';
			if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){$next_month_link .= ' onClick="return saisie_abandon();"';};
			$next_month_link .='>'.$dd.'</a>'."\n";
		}
		
		if  ( ($param['link_before_date'] == 0) 
			&& ($current_timestamp >= mktime(0,0,0, $current_month-1, $current_day, $current_year))
		){
		$previous_month_link = '&nbsp;';		
		}
		else {
			$previous_month_link 	= '<a href="'.$PHP_SELF.'?date='.$previous_month.'" title="'.$calendar_txt[$param['lang']]['misc'][0].'"';
			if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){$previous_month_link .= ' onClick="return saisie_abandon();"';};
			$previous_month_link .='>'.$gg.'</a>'."\n";
		}
		
		$output .= '<tr>'."\n";
		$output .= '	<td colspan="'.$param['calendar_columns'].'" class="calendarDays'.$param['calendar_id'].'">'."\n";
		$output .= '		<table width="100%" border="0" >';
		$output .= '		<tr>'."\n";
		$output .= '			<td width="25%" align="left" class="calendarDays'.$param['calendar_id'].'">'."\n";
		$output .= 					$previous_month_link;
		$output .= '			</td>'."\n";
		$output .= '			<td width="25%" align="center" class="calendarDays'.$param['calendar_id'].'">'."\n";
		$output .= 					$previous_day_link;
		$output .= '			</td>'."\n";
		$output .= '			<td width="25%" align="center" class="calendarDays'.$param['calendar_id'].'">'."\n";
		$output .= 					$next_day_link;
		$output .= '			</td>'."\n";
		$output .= '			<td width="25%" align="right" class="calendarDays'.$param['calendar_id'].'">'."\n";
		$output .= 					$next_month_link;
		$output .= '			</td>'."\n";
		$output .= '		</tr>';
		$output .= '		</table>';
		$output .= '	</td>'."\n";
		$output .= '</tr>'."\n";
		
	}
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
