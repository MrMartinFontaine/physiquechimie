<?php 
session_start();

//on filtre
if ((isset($_GET['classe_ID']))&&($_GET['classe_ID']!=intval($_GET['classe_ID']))){  header("Location: index.php");exit;};
if ((!isset($_SESSION['consultation'])||($_SESSION['consultation']!=$_GET['classe_ID']))){  header("Location: index.php");exit;};

require_once('Connections/conn_cahier_de_texte.php');
require_once('inc/functions_inc.php');

if(function_exists("date_default_timezone_set")){ //fonction PHP 5 
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_time_zone_db = "SELECT param_val FROM cdt_params WHERE param_nom='time_zone'";
$time_zone_db = mysqli_query($conn_cahier_de_texte, $query_time_zone_db) or die(mysqli_error($conn_cahier_de_texte));
$row_time_zone_db = mysqli_fetch_assoc($time_zone_db);
date_default_timezone_set($row_time_zone_db['param_val']);
mysqli_free_result($time_zone_db);
};

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);

$classe_Rslisteactivite = "0";
if (isset($_GET['classe_ID'])) {
	$classe_Rslisteactivite = (get_magic_quotes_gpc()) ? intval($_GET['classe_ID']) : addslashes(intval($_GET['classe_ID']));
}

$gic_ID_Rslisteactivite = "0";
if (isset($_GET['gic_ID'])) {
	$gic_ID_Rslisteactivite = (get_magic_quotes_gpc()) ? intval($_GET['gic_ID']) : addslashes(intval($_GET['gic_ID']));
}

if (isset($_POST['date1'])){
	$date_j=substr($_POST['date1'],6,4).substr($_POST['date1'],3,2).substr($_POST['date1'],0,2);
	$date_j_post=$date_j;
	if ($date_j>date('Ymd')){ $date_j=date('Ymd'); };
} else  {$date_j=date('Ymd'); }; 

$date1_form=substr($date_j,6,2).'/'.substr($date_j,4,2).'/'.substr($date_j,0,4);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);


if (isset($_POST['groupe'])){
	if ($_POST['groupe']=='Classe entière'){$sql_groupe='';}
	else { $sql_groupe="AND (groupe='Classe entière' OR groupe='".$_POST['groupe']."')";};
}
else {$sql_groupe='';};

$query_Rslisteactivite = sprintf("
	SELECT *
	FROM cdt_agenda
	LEFT JOIN cdt_prof ON cdt_agenda.prof_ID = cdt_prof.ID_prof
	WHERE publier_cdt='O' AND publier_travail='O' 
	AND
	(
	classe_ID=%u
	AND SUBSTRING(code_date,1,8) = '%s'
	%s
	)
	OR (
	classe_ID=0
	AND SUBSTRING(code_date,1,8) = '%s'
	)
	ORDER BY code_date ASC",$classe_Rslisteactivite,$date_j,$sql_groupe,$date_j);

$Rslisteactivite = mysqli_query($conn_cahier_de_texte, $query_Rslisteactivite) or die(mysqli_error($conn_cahier_de_texte));
$row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite);
$totalRows_Rslisteactivite = mysqli_num_rows($Rslisteactivite);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE ID_classe=%u ",intval($_GET['classe_ID']));
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - S&eacute;ances de la journ&eacute;e</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="./styles/style_default.css" rel="stylesheet" type="text/css">
<link href="./templates/default/perso.css" rel="stylesheet" type="text/css">
<link type="text/css" href="./styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />

<style type='text/css'>
.Style72 {font-size: 11px}
form{
	margin:0;
	padding:4px;
}
</style>

<style type="text/css" media="print">
thead {display:table-header-group ;}
.no_imprime {display:none;}
.bas_ligne_2{   
border-bottom:0px;  }
.black_police{ /* pour IE !!*/
color:#000000;  }
</style>
<style type="text/css" media="screen">
.bas_ligne_2 {
	border-bottom-style: solid;
	border-bottom-color: #A9B4B3;
	border-width: 1px;
}
</style>
<script type="text/javascript" src="enseignant/xinha/plugins/Equation/ASCIIMathML.js"></script>
<script type="text/javascript" src="jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="jscripts/jquery-ui.datepicker-fr.js"></script>
</head>


<body >
<p>
<table width="90%"  border="0" align="center" cellpadding="0" cellspacing="0"> <tr class="lire_cellule_4">
<td>
<?php 
if(isset($_GET['regroupement'])){echo '(R) '.$_GET['regroupement'];}
else
{
	echo $row_RsClasse['nom_classe'];
};
mysqli_free_result($RsClasse);
?>
&nbsp;&nbsp;
<?php if (isset($_POST['groupe'])){echo $_POST['groupe'];} else {echo 'Classe entière';};?>
<?php echo '&nbsp;&nbsp;-&nbsp;&nbsp;S&eacute;ances du ';
if ($totalRows_Rslisteactivite==0){echo ' '.jour_semaine($date1_form).'  '.$date1_form;} 
else {
	echo $row_Rslisteactivite['jour_pointe'].'&nbsp;';
	if ($row_Rslisteactivite['semaine']<>'A et B'){ 
		echo '(';
		if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){
			if ($row_Rslisteactivite['semaine']=='A'){echo 'P';} else {echo 'I';};
		} 
		else {
			echo $row_Rslisteactivite['semaine'];
		};
	echo')';}; 
};?>
</td>  
<td ><div align="right" class="no_imprime"> 
<a href="<?php echo 'consulter.php?classe_ID='.intval($_GET['classe_ID']).'&tri=date';?>"><img src="images/home-menu.gif" alt="Accueil" width="26" height="20" border="0"></a><br>
</div></td>
</tr>
<tr valign="baseline" class="lire_cellule_2">
<td class="no_imprime"><?php 
$var_consult='';

if (isset($_GET['gic_ID'])&&isset($_GET['regroupement'])){$var_regroupement='&gic_ID='.$_GET['gic_ID'].'&regroupement='.$_GET['regroupement'];} else {$var_regroupement='';};

?>
<form name="frm" method="POST" action="cours_du_jour.php?classe_ID=<?php echo strtr(GetSQLValueString($_GET['classe_ID'],"int"),$protect); ?>">
<div align="right" class="Style72">

<script>
$(function() {
		$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
		$.datepicker.setDefaults($.datepicker.regional['fr']);
		$('#date1').datepicker({firstDay:1});
});
</script>
Afficher les cours du&nbsp;&nbsp;
<input name='date1' type='text' id='date1' value="<?php echo $date1_form;?>" size="10"/>
&nbsp;en &nbsp;


<select name="groupe" size="1" id="select">
<?php do {  ?>
	<option value="<?php echo $row_Rsgroupe['groupe']?>"<?php if ((isset($_POST['groupe'])) AND ($_POST['groupe']==$row_Rsgroupe['groupe'] )) {echo 'selected';} else {if (!(isset($_POST['groupe'])) AND ($row_Rsgroupe['groupe']=='Classe entière')) {echo 'selected';};};?>><?php echo $row_Rsgroupe['groupe']?></option>
	<?php
} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
mysqli_free_result($Rsgroupe);
?>
</select>
&nbsp;&nbsp;Choix pr&eacute;sentation
<input type="radio" name="Genre" value="1" <?php if ((isset($_POST['Genre'])) AND ($_POST['Genre']=='1')){echo 'checked';};?>>
1
<input type="radio" name="Genre" value="2" <?php if (isset($_POST['Genre'])) {
if  (($_POST['Genre']<>'1')AND($_POST['Genre']<>'3')){echo 'checked';};}
else {echo 'checked';};  ?>>
2
<input type="radio" name="Genre" value="3" <?php if ((isset($_POST['Genre'])) AND ($_POST['Genre']=='3')){echo 'checked';}?>>
3 &nbsp;&nbsp;

<input name="submit" type="submit" value="Actualiser"/>
</div>
</form></td>
<td valign="bottom" class="no_imprime"><form name="form_reset" method="post" action="cours_du_jour.php?classe_ID=<?php echo strtr(GetSQLValueString($_GET['classe_ID'],"int"),$protect); ?>">
<div align="right" class="Style72">
<input name="reset" type="Submit" value="Annuler">
</div>
</form></td>
</tr>
</table>
<p>
<?php 



if ($totalRows_Rslisteactivite>0){
	
	do { 
		
		if ($row_Rslisteactivite['classe_ID']<>0){  
			//A la date du jour, on teste aussi si l'heure de debut de cours est echue;
			$visu='Oui';
			if ((substr($row_Rslisteactivite['code_date'],0,8)==date('Ymd'))){
				$heure_actuelle=date('Hi',time());
				$heure_seance=substr($row_Rslisteactivite['heure_debut'],0,2).substr($row_Rslisteactivite['heure_debut'],3,2) ;
				if($heure_seance>$heure_actuelle){$visu='Non';}; 
			};  
			if ($visu=='Oui'){
				
				$query_Rs_Travail2 = sprintf("SELECT * FROM cdt_travail WHERE code_date='%s' AND matiere_ID=%u AND classe_ID=%u AND agenda_ID=%u ORDER BY code_date", $row_Rslisteactivite['code_date'],$row_Rslisteactivite['matiere_ID'],$_GET['classe_ID'],$row_Rslisteactivite['ID_agenda']);
				$Rs_Travail2 = mysqli_query($conn_cahier_de_texte, $query_Rs_Travail2) or die(mysqli_error($conn_cahier_de_texte));
				$row_Rs_Travail2 = mysqli_fetch_assoc($Rs_Travail2);
				for ($i=1;$i<4;$i++) {
					$date_a_faire[$i]='';
					$travail[$i]='';
					$t_groupe[$i]='';
					$eval[$i]='';
				};
				do {     
					$travail[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['travail'];
					$date_a_faire[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['t_code_date'];
					$t_groupe[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['t_groupe'];
					$eval[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['eval'];
					
				} while ($row_Rs_Travail2 = mysqli_fetch_assoc($Rs_Travail2));
				mysqli_free_result($Rs_Travail2);
				
				
				//****************************************** 
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsNomprof = sprintf("SELECT cdt_prof.identite,cdt_prof.email,cdt_prof.email_diffus_restreint FROM cdt_prof WHERE ID_prof=%u LIMIT 1",$row_Rslisteactivite['prof_ID']);
				$RsNomprof = mysqli_query($conn_cahier_de_texte, $query_RsNomprof) or die(mysqli_error($conn_cahier_de_texte));
				$row_RsNomprof = mysqli_fetch_assoc($RsNomprof);
				
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsMat = sprintf("SELECT * FROM cdt_matiere WHERE ID_matiere=%u ",$row_Rslisteactivite['matiere_ID']);
				$RsMat = mysqli_query($conn_cahier_de_texte, $query_RsMat) or die(mysqli_error($conn_cahier_de_texte));
				$row_RsMat = mysqli_fetch_assoc($RsMat);        
				?>
				
				<table  width="90%" align="center" cellspacing="0"  <?php if (isset($_SESSION['nom_prof'])){echo 'class="bas_ligne_2"';} else {echo 'class="lire_bordure"';};?>>
				<thead>
				<!-- Pour eviter les coupures a l'impression - les deux lignes forment un bloc -->
				<tr>
				<td 
				<?php 
				if ((isset($_POST['Genre'])) AND($_POST['Genre'] =="3")){               // Choix d'impression 3
				echo "width=\"30%\"";}
				else {
				echo "width=\"25%\"";};
				?> 
				class="lire_cellule_3"><div align="left" class="black_police">
				<?php 
				echo $row_RsMat['nom_matiere'];
				mysqli_free_result($RsMat);
				?>
				&nbsp;
				
				</div>
				<!-- Le border_bottom ci-dessous est utilise pour l'impression -->
				<td colspan="2" class="lire_cellule_4" style="border-bottom: 1px #666666 solid" ><div align="left" class="black_police"><?php echo $row_Rslisteactivite['theme_activ']; ?></div>
				</td>
				<?php 
				if (((isset($_POST['Genre'])) AND($_POST['Genre'] =="2")) || (!isset($_POST['Genre'])))  // Choix d'impression 2
					{echo "<td class=\"lire_cellule_4\"><div align=\"left\" >&nbsp;</div>";};
				?>
				</tr>
				<tr >
				<td valign="top" class="lire_cellule_2" ><?php
				if ($row_Rslisteactivite['gic_ID']>0){    
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$query_Rsgic =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u",$row_Rslisteactivite['gic_ID']);
					$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
					$row_Rsgic = mysqli_fetch_assoc($Rsgic);
					echo '<div align="left"><strong>(R) '.$row_Rsgic['nom_gic'].'</strong></div><br />';
					mysqli_free_result($Rsgic);
				};  ?>
				<div align="left"> 
				<?php 
				echo '<br /><strong>'.$row_Rslisteactivite['heure_debut'].' - '.$row_Rslisteactivite['heure_fin'].'</strong>'; 
				if ($row_Rslisteactivite['duree']<>''){echo ' - ('.$row_Rslisteactivite['duree'].')';};
				echo '<br />'.$row_RsNomprof['identite']; 
				if (($row_RsNomprof['email']<>'')&&($row_RsNomprof['email_diffus_restreint']=='N')){ ?>
					<a href="mailto:<?php echo $row_RsNomprof['email'];?>">&nbsp;<img src="images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant" title="Contacter l'enseignant"/></a>
				<?php };?>
				<br />
				<?php
				mysqli_free_result($RsNomprof);
				echo $row_Rslisteactivite['groupe'];
				?>
				<br />
				
				
				
				<?php 
				
				
				if ($row_Rslisteactivite['type_activ']<>'ds_prog'){echo ' <p style="color:'.$row_Rslisteactivite['couleur_activ'].'"><strong>'.$row_Rslisteactivite['type_activ'].'</strong></p>';
					if (substr($row_Rslisteactivite['code_date'],8,1)==0) {echo '<p style="color:#FF0000"><b>Heure Sup.</b></p>';};
				}
				else {echo '<p style="color:#FF0000"><b>'.$_SESSION['libelle_devoir'].'</b></p>';}; 
				?>
				<br />
				<?php 
				
				// affichage fichiers joints seance
				
				$refagenda_RsFichiers = "0";
				if (isset($row_Rslisteactivite['ID_agenda'])) {
					$refagenda_RsFichiers = (get_magic_quotes_gpc()) ? 
					$row_Rslisteactivite['ID_agenda'] : addslashes($row_Rslisteactivite['ID_agenda']);
				}
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsFichiers = sprintf("SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=%u AND type<>'Travail'", $refagenda_RsFichiers);
				$RsFichiers = mysqli_query($conn_cahier_de_texte, $query_RsFichiers) or die(mysqli_error($conn_cahier_de_texte));
				$row_RsFichiers = mysqli_fetch_assoc($RsFichiers);
				$totalRows_RsFichiers = mysqli_num_rows($RsFichiers);
				if ($totalRows_RsFichiers<>0){echo'<br />Document(s) Cours<br />';};
				do { 
					
					$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_RsFichiers['nom_fichier']); 
					?>
					<a href="fichiers_joints/<?php echo $row_RsFichiers['nom_fichier'];  ?>" target="_blank"><strong><?php echo $nom_f;  ?></strong></a><br />
					<?php
				} while ($row_RsFichiers = mysqli_fetch_assoc($RsFichiers)); 
				mysqli_free_result($RsFichiers);
				
				//fin affichage fichiers joints seance
				
				?>
				<span class="Style666a">
				<?php 
				if ((isset($_POST['Genre'])) && ($_POST['Genre']=="3")){              // Choix d'impression 3
					for ($i=1;$i<4;$i++) {
						if ( $date_a_faire[$i]<>''){
							echo '<u>'.$t_groupe[$i].' pour le <b>'.jour_semaine($date_a_faire[$i]).' '.$date_a_faire[$i].'</b></u> :';
							
							//affichage fichiers travail joints
							mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
							$sql_f="SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND type ='Travail' AND t_code_date ='".$date_a_faire[$i]."' AND ind_position = ".$i." ORDER BY nom_fichier";
							$query_Rs_fichiers_joints_form = $sql_f;
							$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
							$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
							$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
							
							if ($totalRows_Rs_fichiers_joints_form<>0)
							{
								if ($totalRows_Rs_fichiers_joints_form==1){
									echo ' avec le document ';
								} else {
									echo ' avec les documents ';
								};
								do { 
									echo "<a
									href=\"fichiers_joints/".$row_Rs_fichiers_joints_form['nom_fichier']."\"
									target=\"_blank\">";
									$exp = "/^[0-9]+_/";
									$nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
									echo "<strong>".$nom_f." &nbsp; &nbsp;</strong></a>";           
								} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
							}
							mysqli_free_result($Rs_fichiers_joints_form);
							//fin affichage des fichiers travail joints
							echo '<br />';
							if (!(strcmp($eval[$i],'O'))){echo "<span style='color:red;'><strong>Evaluation : </strong></span>";};
							echo $travail[$i].'<br />';
						}
					};
				}
				?>
				</span> </div>
				</td>
				<td <?php 
				if (((isset($_POST['Genre'])) AND ($_POST['Genre']=="2"))OR (!isset($_POST['Genre'])))                     // Choix d'impression 2
					{echo "width=\"45%\"";};
				?> class="Style10"><div align="left">
				<?php $date_jour='20'.date("ymd").'1';?>
				<span>
				<?php  
				echo $row_Rslisteactivite['activite'].'<div class="no_imprime" ><br /></div>'; 
				if (isset($_SESSION['nom_prof'])){
					if (($row_Rslisteactivite['rq']<>'')&&(isset($_GET['annot']))){echo 'Rq : '.$row_Rslisteactivite['rq'].'<div class="no_imprime" ><br /></div>';}
				};
				?>
				<div class="no_imprime" ><br />
				</div>
				<?php 
				//traitement du visa
				if((isset($_SESSION['droits']))&&($_SESSION['droits']==4)){ 
					if (substr($row_Rslisteactivite['date_visa'],0,4)=='0000'){
					echo '<div align="right" id="image'.$row_Rslisteactivite['ID_agenda'].'" ><img src="images/tampon3.gif" onclick="go_visa('.$row_Rslisteactivite['ID_agenda'].','.$row_Rslisteactivite['prof_ID'].')"></div>';}
					else 
					{
						echo '<div align="right"id="image'.$row_Rslisteactivite['ID_agenda'].'" ><img src="images/visa.gif" onclick="go_visa_supprime('.$row_Rslisteactivite['ID_agenda'].','.$row_Rslisteactivite['prof_ID'].')" ><br ><strong>Le '.substr($row_Rslisteactivite['date_visa'],8,2).'/'.substr($row_Rslisteactivite['date_visa'],5,2).'/'.substr($row_Rslisteactivite['date_visa'],0,4).'</strong></div>';
					};
				};
				
				
				if (((isset($_POST['Genre'])) AND ($_POST['Genre']=="2")) OR (!isset($_POST['Genre']))) {                 // Choix d'impression 2
					echo "</td><td width=\"35%\" class=\"Style10\"><div align=\"left\">";
				};
				?>
				<span class="Style699">
				<?php 
				if ((isset($_POST['Genre']) AND !($_POST['Genre']=="3")) || (!isset($_POST['Genre']))) {                    
					
					for ($i=1;$i<4;$i++) {
						if ( $date_a_faire[$i]<>''){
							echo '<u>'.$t_groupe[$i].' pour le <b>'.jour_semaine($date_a_faire[$i]).' '.$date_a_faire[$i].'</b></u> :';
							
							//affichage fichiers travail joints
							mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
							$sql_f="SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND type ='Travail' AND t_code_date ='".$date_a_faire[$i]."' AND ind_position = ".$i." ORDER BY nom_fichier";
							$query_Rs_fichiers_joints_form = $sql_f;
							$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
							$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
							$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
							
							if ($totalRows_Rs_fichiers_joints_form<>0)
							{
								if ($totalRows_Rs_fichiers_joints_form==1){
									echo ' avec le document ';
								} else {
									echo ' avec les documents ';
								};
								do { 
									echo "<a
									href=\"fichiers_joints/".$row_Rs_fichiers_joints_form['nom_fichier']."\"
									target=\"_blank\">";
									$exp = "/^[0-9]+_/";
									$nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
									echo "<strong>".$nom_f." &nbsp; &nbsp;</strong></a>";           
								} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
							}
							mysqli_free_result($Rs_fichiers_joints_form);
							//fin affichage des fichiers travail joints
							echo '<br />';
							if (!(strcmp($eval[$i],'O'))){echo "<span style='color:red;'><strong>Evaluation : </strong></span>";};
							echo $travail[$i].'<br />';
						}
					};
				}
				?>
				</span>
				</div></td>
				</tr>
				</thead>
				</table>
				<br />
				
				<?php 
			} 
                } else {
                	?>
                	<table width="90%"  border="0" align="center" cellspacing="0" class="lire_bordure">
                	<thead>
                	<tr>
                	<td width="30%" class="vacances" ><?php echo $row_Rslisteactivite['heure_debut'].' - '.$row_Rslisteactivite['heure_fin']; ?> </td>
                	<td class="vacances"><?php echo $row_Rslisteactivite['theme_activ']; ?></td>
                	</tr>
                	</thead>
                	</table>
                	<br />
                	<?php
                };
        } while ($row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite)); 
        
} else {
	if ((isset($_POST['date1']))&&($date_j_post>date('Ymd'))){ 
		echo "<br /><br /><p><strong>La date que vous avez saisie, est post&eacute;rieure &agrave; la date d'aujourd'hui.</strong></p>";
	};
	echo "<br /><br /><p><strong>Aucune activit&eacute; n'a &eacute;t&eacute; enregistr&eacute;e pour cette date dans le cahier de textes.</strong></p><br />";
};
mysqli_free_result($Rslisteactivite);
?>
<div align="right" class="no_imprime">
<div align="center"><a href="<?php echo 'consulter.php?classe_ID='.intval($_GET['classe_ID']).'&tri=date';?>"><br />Retour au travail &agrave; faire &nbsp;&nbsp;<img src="images/home-menu.gif" width="26" height="20" border="0"></a><br>
</div>
</div>
</body>
</html>
