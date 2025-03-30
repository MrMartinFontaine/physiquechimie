<?php 

session_start();
require_once('../Connections/conn_cahier_de_texte2.php'); 
require_once('../inc/functions_inc.php');

//if (!isset($_SESSION['consultation'])){  header("Location: index.php");exit;};





$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);
$query_RsMinDate = "SELECT * FROM cdt_agenda ORDER BY code_date ASC LIMIT  1 ";
$RsMinDate = mysqli_query($conn_cahier_de_texte, $query_RsMinDate, $conn_cahier_de_texte2) or die($query_RsMinDate.mysqli_error($conn_cahier_de_texte));
$row_RsMinDate = mysqli_fetch_assoc($RsMinDate);
$totalRows_RsMinDate= mysqli_num_rows($RsMinDate);

$classe_Rslisteactivite = "0";
if (isset($_GET['classe_ID'])) {
  $classe_Rslisteactivite = (get_magic_quotes_gpc()) ? intval($_GET['classe_ID']) : addslashes(intval($_GET['classe_ID']));
}
$matiere_Rslisteactivite = "0";
if (isset($_GET['matiere_ID'])) {
  $matiere_Rslisteactivite = (get_magic_quotes_gpc()) ? intval($_GET['matiere_ID']) : addslashes(intval($_GET['matiere_ID']));
}
if (isset($_GET['date1'])){$date1=substr($_GET['date1'],6,4).substr($_GET['date1'],3,2).substr($_GET['date1'],0,2).'1';} else {$date1=$row_RsMinDate['code_date'];};


$today=date('Ymd').'9'; $today_form=date('j/m/Y');
if (isset($_GET['date2'])){$date2=substr($_GET['date2'],6,4).substr($_GET['date2'],3,2).substr($_GET['date2'],0,2).'9';} else {$date2=$today;};

$date1_form=substr($date1,6,2).'/'.substr($date1,4,2).'/'.substr($date1,0,4);
$date2_form=substr($date2,6,2).'/'.substr($date2,4,2).'/'.substr($date2,0,4);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);

if (isset($_GET['chrono']))
{$ordre='down'; } else {$ordre='up';};
if ($ordre=='down'){ 
$query_Rslisteactivite = sprintf("SELECT * FROM cdt_agenda WHERE (cdt_agenda.classe_ID=%u AND cdt_agenda.matiere_ID=%u AND cdt_agenda.code_date>='%s' AND cdt_agenda.code_date<='%s' ) OR ( cdt_agenda.classe_ID=0 ) ORDER BY cdt_agenda.code_date DESC", $classe_Rslisteactivite,$matiere_Rslisteactivite,$date1,$date2);

}
else
{
$query_Rslisteactivite = sprintf("SELECT * FROM cdt_agenda WHERE (cdt_agenda.classe_ID=%u AND cdt_agenda.matiere_ID=%u AND cdt_agenda.code_date>='%s' AND cdt_agenda.code_date<='%s' ) OR ( cdt_agenda.classe_ID=0 ) ORDER BY cdt_agenda.code_date ASC", $classe_Rslisteactivite,$matiere_Rslisteactivite,$date1,$date2);

}

$Rslisteactivite = mysqli_query($conn_cahier_de_texte, $query_Rslisteactivite, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
$row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite);
$totalRows_Rslisteactivite = mysqli_num_rows($Rslisteactivite);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);
$query_RsMat = sprintf("SELECT * FROM cdt_matiere WHERE cdt_matiere.ID_matiere=%s ",intval($_GET['matiere_ID']));
$RsMat = mysqli_query($conn_cahier_de_texte, $query_RsMat, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMat = mysqli_fetch_assoc($RsMat);
$totalRows_RsMat = mysqli_num_rows($RsMat);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);
$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE cdt_classe.ID_classe=%u ",intval($_GET['classe_ID']));
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);



mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);
$query_RsNomprof = sprintf("SELECT cdt_emploi_du_temps.prof_ID,cdt_emploi_du_temps.matiere_ID,cdt_emploi_du_temps.classe_ID,cdt_prof.nom_prof FROM cdt_emploi_du_temps,cdt_prof WHERE cdt_prof.ID_prof=cdt_emploi_du_temps.prof_ID
AND cdt_emploi_du_temps.matiere_ID=%u AND cdt_emploi_du_temps.classe_ID=%u
",intval($_GET['matiere_ID']),intval($_GET['classe_ID']));
$RsNomprof = mysqli_query($conn_cahier_de_texte, $query_RsNomprof, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsNomprof = mysqli_fetch_assoc($RsNomprof);

?>

<script  type="text/javascript">
	var allMonth=[31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	var allNameOfWeekDays=["Lu","Ma", "Me", "Je", "Ve", "Sa", "Di"];
	var allNameOfMonths=["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
	var newDate=new Date();
	var yearZero=newDate.getFullYear();
	var monthZero=newDate.getMonth();
	var day=newDate.getDate();
	var currentDay=0, currentDayZero=0;
	var month=monthZero, year=yearZero;
	var yearMin=2006, yearMax=2014;
	var target='';
	var hoverEle=false;
	function setTarget(e){
		if(e) return e.target;
		if(event) return event.srcElement;
	}
	function newElement(type, attrs, content, toNode) {
		var ele=document.createElement(type);
		if(attrs) {
			for(var i=0; i<attrs.length; i++) {
				eval('ele.'+attrs[i][0]+(attrs[i][2] ? '=\u0027' :'=')+attrs[i][1]+(attrs[i][2] ? '\u0027' :''));
			}
		}
		if(content) ele.appendChild(document.createTextNode(content));
		if(toNode) toNode.appendChild(ele);
		return ele;
	}
	function setMonth(ele){month=parseInt(ele.value);calender()}
	function setYear(ele){year=parseInt(ele.value);calender()}
	function setValue(ele) {
		if(ele.parentNode.className=='week' && ele.firstChild){
			var dayOut=ele.firstChild.nodeValue;
			if(dayOut < 10) dayOut='0'+dayOut;
			var monthOut=month+1;
			if(monthOut < 10) monthOut='0'+monthOut;
			target.value=dayOut+'/'+monthOut+'/'+year;
			removeCalender();
		}
	}
	function removeCalender() {
		var parentEle=document.getElementById("calender");
		while(parentEle.firstChild) parentEle.removeChild(parentEle.firstChild);
		document.getElementById('basis').parentNode.removeChild(document.getElementById('basis'));
	}		
	function calender() {
		var parentEle=document.getElementById("calender");
		parentEle.onmouseover=function(e) {
			var ele=setTarget(e);
			if(ele.parentNode.className=='week' && ele.firstChild && ele!=hoverEle) {
				if(hoverEle) hoverEle.className=hoverEle.className.replace(/hoverEle ?/,'');
				hoverEle=ele;
				ele.className='hoverEle '+ele.className;
			} else {
				if(hoverEle) {
					hoverEle.className=hoverEle.className.replace(/hoverEle ?/,'');
					hoverEle=false;
				}
			}
		}
		while(parentEle.firstChild) parentEle.removeChild(parentEle.firstChild);
		function check(){
			if(year%4==0&&(year%100!=0||year%400==0))allMonth[1]=29;
			else allMonth[1]=28;
		}
		function addClass (name) { if(!currentClass){currentClass=name} else {currentClass+=' '+name} };
		if(month < 0){month+=12; year-=1}
		if(month > 11){month-=12; year+=1}
		if(year==yearMax-1) yearMax+=1;
		if(year==yearMin) yearMin-=1;
		check();
		var control=newElement('p',[['id','control',1]],false,parentEle);
		var controlPlus=newElement('a', [['href','javascript:month--;calender()',1],['className','controlPlus',1]], '<', control);
		var select=newElement('select', [['onchange',function(){setMonth(this)}]], false, control);
		for(var i=0; i<allNameOfMonths.length; i++) newElement('option', [['value',i,1]], allNameOfMonths[i], select);
		select.selectedIndex=month;
		select=newElement('select', [['onchange',function(){setYear(this)}]], false, control);
		for(var i=yearMin; i<yearMax; i++) newElement('option', [['value',i,1]], i, select);
		select.selectedIndex=year-yearMin;
		controlPlus=newElement('a', [['href','javascript:month++;calender()',1],['className','controlPlus',1]], '>', control);
		check();
		currentDay=1-new Date(year,month,1).getDay();
		if(currentDay > 0) currentDay-=7;
		currentDayZero=currentDay;
		var newMonth=newElement('table',[['cellSpacing',0,1],['onclick',function(e){setValue(setTarget(e))}]], false, parentEle);
		var newMonthBody=newElement('tbody', false, false, newMonth);
		var tr=newElement('tr', [['className','head',1]], false, newMonthBody);
		tr=newElement('tr', [['className','weekdays',1]], false, newMonthBody);
		for(i=0;i<7;i++) td=newElement('td', false, allNameOfWeekDays[i], tr);	
		tr=newElement('tr', [['className','week',1]], false, newMonthBody);
		for(i=0; i<allMonth[month]-currentDayZero; i++){
			var currentClass=false;			
			currentDay++;
			if(currentDay==day && month==monthZero && year==yearZero) addClass ('today');
			if(currentDay <= 0 ) {
				if(currentDayZero!=-7) td=newElement('td', false, false, tr);
			}
			else {
				if((currentDay-currentDayZero)%7==0) addClass ('holiday');
				td=newElement('td', (!currentClass ? false : [['className',currentClass,1]] ), currentDay, tr);
				if((currentDay-currentDayZero)%7==0) tr=newElement('tr', [['className','week',1]], false, newMonthBody);
			}
			if(i==allMonth[month]-currentDayZero-1){
				i++;
				while(i%7!=0){i++;td=newElement('td', false, false, tr)};
			}
		}
	}
	function showCalender(ele) {
		if(document.getElementById('basis')) { removeCalender() }
		else {
			target=document.getElementById(ele.id.replace(/for_/,'')); 
			var basis=ele.parentNode.insertBefore(document.createElement('div'),ele);
			basis.id='basis';
			newElement('div', [['id','calender',1]], false, basis);
			calender();
		}
	}
</script>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<link href="../styles/style_default.css" rel="stylesheet" type="text/css">
<style type='text/css'>

#basis {
	display:inline;
	position:relative;
	}
	
 .pos_img_calend{
    position:relative;
	height:22px;width:20px;
    top:6px;
    left:-6px;}

#calender {
	position:absolute;
	top:30px;
	left:0;
	width:220px;
	background-color:#fff;
	border:3px solid #ccc;
	padding:10px;
	z-index:10;
	}
#control {
	text-align:center;
	margin:0 0 5px 0;
	}
#control select {
	font-family:"Lucida sans unicode", sans-serif;
	font-size:11px;
	margin:0 5px;
	vertical-align:middle;
	}
#calender .controlPlus {
	padding:0 5px;
	text-decoration:none;
	color:#333;
	}
#calender table {
	empty-cells: show;
	width:100%;
	font-size:11px;
	table-layout:fixed;
	}
#calender .weekdays td{
	text-align:right;
	padding:1px 5px 1px 1px;
	color:#333;
	}
#calender .week td {
	text-align:right;
	cursor:pointer;
	border:1px solid #fff;
	padding:1px 4px 1px 0;
	}
#calender .week .today { 
	background-color:#ccf;
	border-color:#ccf;
	}
#calender .week .holiday {
	font-weight: bold;
	}
#calender .week .hoverEle {
	border-color:#666;
	background-color:#99f;
	color:#000;
	}
.Style72 {font-size: 11px}
.Style55 {	background-color: #D1A96E;}
.Style6 {	background: url(../images/bande_marron.jpg);}
.Style666 {	background: url(../images/bande_marron.jpg);}
     

</style>


</head>
<body style="background-color: #FFFFFF;">
<p>
<table width="90%"  border="0" align="center" cellpadding="0" cellspacing="0" <?php if (isset($_SESSION['nom_prof'])){;} else {echo 'class="Style4"';};?> >
  <tr>
    <td class="Style6"><?php echo $row_RsClasse['nom_classe'];?> - <?php echo $row_RsMat['nom_matiere'] ;?> - <?php echo $row_RsNomprof['nom_prof'];?></td>
    <td width="9%" class="Style6"><div align="right"><a href="<?php if (isset($_SESSION['nom_prof']))
{echo'archive_menu.php';}
else 
{
//echo 'consulter.php?classe_ID='.intval($_GET['classe_ID']);
};
?>"><img src="../images/up.gif" alt="Accueil" width="26" height="20" border="0"></a>
 <a href="<?php if (isset($_SESSION['nom_prof']))
{echo'../enseignant/enseignant.php';}
else 
{
//echo 'consulter.php?classe_ID='.intval($_GET['classe_ID']);
};
?>"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0"></a><br>
    </div></td>
  </tr>
</table>
<table border="0" align="center">
  <tr>
    <td valign="baseline"><form name="frm" method="GET" action="voir_archive.php?annot">
      <span class="Style72">Consultation/impression pour la p&eacute;riode du </span>
      <input name='date1' type='text' id='date1' value="<?php echo $date1_form;?>" size="10"/>
    <a href="#" onclick='showCalender(date1)'><img class="pos_img_calend" alt="Calendrier" src="../images/petit_calendrier.gif" border="0"/></a>
    <span class="Style72">au </span>
    <input  name='date2' type='text' id='date2' value="<?php echo $date2_form?>" size="10" />
<a href="#" onclick='showCalender(date2)'><img class="pos_img_calend" alt="Calendrier" src="../images/petit_calendrier.gif" border="0"/></a>
<label>
<input name="chrono" type="checkbox" id="chrono" value="checkbox" <?php if ($ordre=='down'){echo 'checked';}?>>
<span class="Style72">Chronologie inverse</span>
</label>
 <input name='classe_ID' type='hidden'  value="<?php echo $_GET['classe_ID'];?>"/>
 <input name='matiere_ID' type='hidden'  value="<?php echo $_GET['matiere_ID'];?>"/>
 
<input name="submit" type="submit" value="Actualiser"/></form></td>
    <td valign="baseline">
	<form name="form_reset" method="GET" action="voir_archive.php?classe_ID=<?php echo intval($_GET['classe_ID']);?>&matiere_ID=<?php echo intval($_GET['matiere_ID']);?>">
<input name="reset" type="Submit" value="Annuler">
</form></td>
  </tr>
</table>
<p><?php 
	if ($totalRows_Rslisteactivite  >0){
	  
	  do { 
	  
	 if($row_Rslisteactivite['classe_ID']<>0){  	 
  
	 //***************************************



$query_Rs_Travail2 = sprintf("SELECT * FROM cdt_travail WHERE cdt_travail.code_date='%s' AND cdt_travail.matiere_ID=%u AND cdt_travail.classe_ID=%u AND cdt_travail.agenda_ID=%u  ORDER BY cdt_travail.code_date", $row_Rslisteactivite['code_date'],$_GET['matiere_ID'],$_GET['classe_ID'],$row_Rslisteactivite['ID_agenda']);

$Rs_Travail2 = mysqli_query($conn_cahier_de_texte, $query_Rs_Travail2, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_Travail2 = mysqli_fetch_assoc($Rs_Travail2);




$date_a_faire[1]='';$date_a_faire[2]='';$date_a_faire[3]='';$date_a_faire[4]='';
$travail[1]='';$travail[2]='';$travail[3]='';$travail[4]='';
$t_groupe[1]='';$t_groupe[2]='';$t_groupe[3]='';$t_groupe[4]='';
do {  
 
$travail[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['travail'];
$date_a_faire[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['t_code_date'];
$t_groupe[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['t_groupe'];



} while ($row_Rs_Travail2 = mysqli_fetch_assoc($Rs_Travail2));


//******************************************


//******************************************  
	  ?>
</p>
<table width="90%" align="center" cellspacing="0"  <?php if (isset($_SESSION['nom_prof'])){echo 'class="bas_ligne_2"';} else {echo 'class="Style4"';};?>>
  <tr>
    <td width="25%" class="Style666"><div align="left">
        <?php 
	  echo substr($row_Rslisteactivite['jour_pointe'],0,strlen($row_Rslisteactivite['jour_pointe'])-4); ?>
        &nbsp;
        <?php if ($row_Rslisteactivite['semaine']<>'A et B'){ echo '('. $row_Rslisteactivite['semaine'].')';}; ?>
      </div>
    <td class="Style6"><div align="left"><?php echo $row_Rslisteactivite['theme_activ']; ?></div></td>
  </tr>
  <tr>
    <td width="25%" valign="top" class="Style55" ><div align="left"> <?php echo $row_Rslisteactivite['groupe']; ?> <br />
        <?php echo $row_Rslisteactivite['heure_debut']; ?>- <?php echo $row_Rslisteactivite['heure_fin']; ?>
        <?php if ($row_Rslisteactivite['duree']<>''){echo '- ('.$row_Rslisteactivite['duree'].')';
		   }?>
        <br />
        <?php echo $row_Rslisteactivite['type_activ']; ?> <br />
        <?php 
		
// affichage fichiers joints seance
				
				$refagenda_RsFichiers = "0";
				if (isset($row_Rslisteactivite['ID_agenda'])) {
				$refagenda_RsFichiers = (get_magic_quotes_gpc()) ? 
				$row_Rslisteactivite['ID_agenda'] : addslashes($row_Rslisteactivite['ID_agenda']);
				}
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);
				$query_RsFichiers = sprintf("SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=%u  AND cdt_fichiers_joints.type<>'Travail'", $refagenda_RsFichiers);
				$RsFichiers = mysqli_query($conn_cahier_de_texte, $query_RsFichiers, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
				$row_RsFichiers = mysqli_fetch_assoc($RsFichiers);
				$totalRows_RsFichiers = mysqli_num_rows($RsFichiers);
	 if ($totalRows_RsFichiers<>0){echo'</br>Documents s&eacute;ance</br>';};
do { 

$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_RsFichiers['nom_fichier']); 
?>
          <a href="../fichiers_joints/<?php echo $row_RsFichiers['nom_fichier'];  ?>"><?php echo $nom_f;  ?></a></br>
          <?php
} while ($row_RsFichiers = mysqli_fetch_assoc($RsFichiers)); 
mysqli_free_result($RsFichiers);


//fin affichage fichiers joints seance
?>
      </div></td>
    </td>
    <td class="Style10"><div align="left">
        <?php 
	  $date_jour='20'.date("ymd").'1';
	if ( substr($row_Rslisteactivite['a_faire'],0,1)<>'?'){
	  
	  ?>
        <span class=<?php if ($row_Rslisteactivite['code_date']< $date_jour) 
{echo '"Style699"';} else { //echo'"Style13"';
};?>>
        <?php  
if ($row_Rslisteactivite['a_faire']=='Oui'	){	 //compatibilite passage ver 2.0 à supérieure





?>
		<?php
	  } 
	  echo (nl2br($row_Rslisteactivite['activite'])); 
	  if (isset($_SESSION['nom_prof'])){
	  if (($row_Rslisteactivite['rq']<>'')&&(isset($_GET['annot']))){echo '</br>Rq : '.$row_Rslisteactivite['rq'];}
	  };?>
	  <span class="Style699">
        <?php 
if ( $date_a_faire[1]<>''){
echo '<u>'.$t_groupe[1].' pour le <b>'.jour_semaine($date_a_faire[1]).' '.$date_a_faire[1].'</b></u> :';

//affichage fichiers travail joints
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);
$sql_f="SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND cdt_fichiers_joints.type ='Travail' AND cdt_fichiers_joints.t_code_date ='".$date_a_faire[1]."' AND cdt_fichiers_joints.ind_position = 1 ORDER BY cdt_fichiers_joints.nom_fichier";
$query_Rs_fichiers_joints_form = $sql_f;
$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

 if ($totalRows_Rs_fichiers_joints_form<>0)
 {
 	if ($totalRows_Rs_fichiers_joints_form=1)
 		{echo 'avec le document ';}
 	else
 		{echo 'avec les documents ';};
    do { ?>
          <a href="../fichiers_joints/<?php echo $row_Rs_fichiers_joints_form['nom_fichier'];  ?> " target="_blank">
          <?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']); ?>
          <?php echo $nom_f ;  ?></a>
          <?php
				  } while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
}
mysqli_free_result($Rs_fichiers_joints_form);
//fin affichage des fichiers travail joints
echo '<br />'.$travail[1].'<br />';
}


if ( $date_a_faire[2]<>''){
echo '<u>'.$t_groupe[2].' pour le <b>'.jour_semaine($date_a_faire[2]).' '.$date_a_faire[2].'</b></u> :';
//affichage fichiers travail joints
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);
$sql_f="SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND cdt_fichiers_joints.type ='Travail' AND cdt_fichiers_joints.t_code_date ='".$date_a_faire[2]."' AND cdt_fichiers_joints.ind_position = 2 ORDER BY cdt_fichiers_joints.nom_fichier";
$query_Rs_fichiers_joints_form = $sql_f;

$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

 if ($totalRows_Rs_fichiers_joints_form<>0)
 {
 	if ($totalRows_Rs_fichiers_joints_form=1)
 		{echo 'avec le document ';}
 	else
 		{echo 'avec les documents ';};
    do { ?>
        <a href="../fichiers_joints/<?php echo $row_Rs_fichiers_joints_form['nom_fichier'];  ?> " target="_blank">
        <?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']); ?>
        <?php echo $nom_f ;  ?></a>
        <?php
				  } while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
}
mysqli_free_result($Rs_fichiers_joints_form);
//fin affichage des fichiers travail joints
echo '<br />'.$travail[2].'<br />';

}

if ( $date_a_faire[3]<>''){
echo '<u>'.$t_groupe[3].' pour le <b>'.jour_semaine($date_a_faire[3]).' '.$date_a_faire[3].'</b></u> :';
//affichage fichiers travail joints
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);
$sql_f="SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND cdt_fichiers_joints.type ='Travail' AND cdt_fichiers_joints.t_code_date ='".$date_a_faire[3]."' AND cdt_fichiers_joints.ind_position = 3  ORDER BY cdt_fichiers_joints.nom_fichier";
$query_Rs_fichiers_joints_form = $sql_f;

$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

 if ($totalRows_Rs_fichiers_joints_form<>0)
 {
 	if ($totalRows_Rs_fichiers_joints_form=1)
 		{echo 'avec le document ';}
 	else
 		{echo 'avec les documents ';};
    do { ?>
        <a href="../fichiers_joints/<?php echo $row_Rs_fichiers_joints_form['nom_fichier'];  ?> " target="_blank">
        <?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']); ?>
        <?php echo $nom_f ;  ?></a>
        <?php
				  } while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
}
mysqli_free_result($Rs_fichiers_joints_form);
//fin affichage des fichiers travail joints
echo '<br />'.$travail[3].'<br />';

}

if ( $date_a_faire[4]<>''){
echo '<u>'.$t_groupe[4].' pour le <b>'.jour_semaine($date_a_faire[4]).' '.$date_a_faire[4].'</b></u> :';
//affichage fichiers travail joints
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);
$sql_f="SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND cdt_fichiers_joints.type ='Travail' AND cdt_fichiers_joints.t_code_date ='".$date_a_faire[4]."' AND cdt_fichiers_joints.ind_position = 4 ORDER BY cdt_fichiers_joints.nom_fichier";
$query_Rs_fichiers_joints_form = $sql_f;

$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

 if ($totalRows_Rs_fichiers_joints_form<>0)
 {
 	if ($totalRows_Rs_fichiers_joints_form=1)
 		{echo 'avec le document ';}
 	else
 		{echo 'avec les documents ';};
    do { ?>
        <a href="../fichiers_joints/<?php echo $row_Rs_fichiers_joints_form['nom_fichier'];  ?> " target="_blank">
        <?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']); ?>
        <?php echo $nom_f ;  ?></a>
        <?php
				  } while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
}
mysqli_free_result($Rs_fichiers_joints_form);
//fin affichage des fichiers travail joints
echo '<br />'.$travail[4].'<br />';



}	  


		  
		  ?>
        </span>
        <?php	 
	 
	 
	 
	 } else {
		   echo '<u>A faire pour le <b>'.substr($row_Rslisteactivite['a_faire'],0,10).'</b> : </u>'.substr($row_Rslisteactivite['a_faire'],11,strlen($row_Rslisteactivite['a_faire'])); 
		   }
		   



//fin affichage fichiers joints travail
		   
		   
		   ?>
        </span>
        
      </div></td>
  </tr>
</table>
<?php  }
  else {
  
  // affichage des vacances 
  $datejour=date("Ymj").'0';
  $d=$row_Rslisteactivite['code_date']-$datejour;
    if ( $d<70)
  // les vacances s'affichent seulement à partir de J-7
  {  
   ?>
<br/>
<table width="90%"  border="0" align="center" cellspacing="0" class="Style4">
  <tr>
    <td width="30%" class="vacances" ><?php echo $row_Rslisteactivite['heure_debut'].' - '.$row_Rslisteactivite['heure_fin']; ?> </td>
    <td class="vacances"><?php echo $row_Rslisteactivite['theme_activ']; ?></td>
  </tr>
</table>
<?php
  }
  };
  
  
  
  } while ($row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite)); 
	  }
	  if (isset($_SESSION['nom_prof'])){;}else{?>
<p>&nbsp;</p>
<p align="center">&nbsp;</p>
<p align="center">&nbsp;</p>
<p align="center"><a href="../enseignant/enseignant.php">Retour au menu enseignant</a></p>
<?php }
  
  
  if (isset($_SESSION['nom_prof'])){;} else {echo '</div>';};
  ?>
</body>
</html>
<?php
mysqli_free_result($RsNomprof);
if (isset($Rs_Travail2)){mysqli_free_result($Rs_Travail2);};
mysqli_free_result($Rslisteactivite);

mysqli_free_result($RsMat);
mysqli_free_result($RsMinDate);
mysqli_free_result($RsClasse);
?>
