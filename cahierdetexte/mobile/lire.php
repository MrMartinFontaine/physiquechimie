<?php 
session_start();
if (isset($_SESSION['nom_prof'])){$_SESSION['consultation']=$_GET['classe_ID'];};
//on filtre
if ((isset($_GET['classe_ID']))&&($_GET['classe_ID']!=intval($_GET['classe_ID']))){  header("Location: index.php");exit;};
if ((isset($_GET['matiere_ID']))&&($_GET['matiere_ID']!=intval($_GET['matiere_ID']))){  header("Location: index.php");exit;};
if ((isset($_GET['prof_ID']))&&($_GET['prof_ID']!=intval($_GET['prof_ID']))){  header("Location: index.php");exit;};
if ((!isset($_SESSION['consultation'])||($_SESSION['consultation']!=$_GET['classe_ID']))){  header("Location: index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php'); 

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsPublier2 = "SELECT date_maj FROM cdt_prof WHERE droits=4 ";
$RsPublier2 = mysqli_query($conn_cahier_de_texte, $query_RsPublier2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsPublier2 = mysqli_fetch_assoc($RsPublier2);
$totalRows_RsPublier2= mysqli_num_rows($RsPublier2);
//restriction d'affichage
if (!isset($_SESSION['ID_prof'])) {$sql_publier="AND cdt_prof.publier_cdt='O'";}else {$sql_publier='';};
$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
$arcID = "";
if (isset($_GET['archivID'])) {
	$arcID = (get_magic_quotes_gpc()) ? intval($_GET['archivID']) : addslashes(intval($_GET['archivID']));
	$arcID = "_save".$arcID;
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe$arcID ORDER BY groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMinDate = "SELECT *  FROM cdt_agenda$arcID WHERE code_date > 0 ORDER BY code_date ASC LIMIT  1 ";
$RsMinDate = mysqli_query($conn_cahier_de_texte, $query_RsMinDate) or die(mysqli_error($conn_cahier_de_texte));
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
$gic_ID_Rslisteactivite = "0";
if (isset($_GET['gic_ID'])) {
	$gic_ID_Rslisteactivite = (get_magic_quotes_gpc()) ? intval($_GET['gic_ID']) : addslashes(intval($_GET['gic_ID']));
}
$today=date('Ymd').'9'; $today_form=date('j/m/Y');
if (isset($_POST['date_jour2'])){
	$date2 = $_POST['date_annee2'].$_POST['date_mois2'].$_POST['date_jour2'].'9';      
} else {$date2=$today;
}; 
if (isset($_POST['date_jour1'])){// IF 12
        $date1 = $_POST['date_annee1'].$_POST['date_mois1'].$_POST['date_jour1'].'1';
        
} 
else  {   
	$mois_tmp=substr($date2,4,2)-1;
	if($mois_tmp<10) $mois_tmp = "0".$mois_tmp;
	if($mois_tmp=='00') { 
		$annee_tmp=substr($date2,0,4)-1;
		$date_tmp=$annee_tmp.'12'.substr($date2,6,2).'1';
	} else {
		$date_tmp=substr($date2,0,4). $mois_tmp.substr($date2,6,2).'1';
	};
	if ($date_tmp>$row_RsMinDate['code_date']){ $date1=$date_tmp;} else {$date1=$row_RsMinDate['code_date'];};
}//END IF 12
;
//en mode eleve, interdire l'affichage du cahier postérieur à la date du jour
if (!isset($_SESSION['nom_prof'])){
	if ( $date2>$today){$date2=$today;};
};
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if (isset($_POST['chrono']))
	{$ordre='up'; } else {$ordre='down';};
if (isset($_POST['groupe'])){
	if ($_POST['groupe']=='Classe entière'){$sql_groupe='';}
	else { $sql_groupe="AND (cdt_agenda$arcID.groupe='Classe entière' OR cdt_agenda$arcID.groupe='".$_POST['groupe']."')";};
}
else {$sql_groupe='';};
//eleve
if ((!isset($_SESSION['ID_prof']))&&(isset($_GET['prof_ID']))){$sql_prof_ID=intval($_GET['prof_ID']);} else     {$sql_prof_ID=$_SESSION['ID_prof'];};
//profs
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==2)){$sql_prof='AND cdt_agenda'.$arcID.'.prof_ID = '.$_SESSION['ID_prof'];} else                         {$sql_prof='';};
//chef etablissement
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==4)){
	$sql_prof='AND cdt_agenda'.$arcID.'.prof_ID = '.intval($_GET['ID_consult']);
	$sql_prof_ID=intval($_GET['ID_consult']);
} 
else {$sql_prof='';};
//invite
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==5)&&(isset($_GET['prof_ID']))){
	$sql_prof_ID=intval($_GET['prof_ID']);
} ;
if ($ordre=='down'){ 
	$query_Rslisteactivite = sprintf("
		SELECT *
		FROM cdt_agenda$arcID
		LEFT JOIN cdt_prof ON cdt_agenda$arcID.prof_ID = cdt_prof.ID_prof
		WHERE (
		prof_ID=%u
		AND cdt_agenda$arcID.classe_ID=%u
		AND cdt_agenda$arcID.matiere_ID=%u
		AND cdt_agenda$arcID.gic_ID=%u
		AND cdt_agenda$arcID.code_date >= '%s'
		AND cdt_agenda$arcID.code_date <= '%s' 
		%s %s %s
		)
		OR (
		cdt_agenda$arcID.classe_ID =0
		AND cdt_agenda$arcID.code_date >= '%s'
		AND cdt_agenda$arcID.code_date <= '%s'
		)
		ORDER BY cdt_agenda$arcID.code_date DESC",$sql_prof_ID, $classe_Rslisteactivite,$matiere_Rslisteactivite,$gic_ID_Rslisteactivite,$date1,$date2,$sql_prof,$sql_groupe,$sql_publier,$date1,$date2);
}
else
{
	$query_Rslisteactivite = sprintf("
		SELECT *
		FROM cdt_agenda$arcID
		LEFT JOIN cdt_prof ON cdt_agenda$arcID.prof_ID = cdt_prof.ID_prof
		WHERE (
		prof_ID=%u
		AND cdt_agenda$arcID.classe_ID=%u
		AND cdt_agenda$arcID.matiere_ID=%u
		AND cdt_agenda$arcID.gic_ID=%u
		AND cdt_agenda$arcID.code_date >= '%s'
		AND cdt_agenda$arcID.code_date <= '%s' 
		%s %s %s
		)
		OR (
		cdt_agenda$arcID.classe_ID =0
		AND cdt_agenda$arcID.code_date >= '%s'
		AND cdt_agenda$arcID.code_date <= '%s'
		)
		ORDER BY cdt_agenda$arcID.code_date ASC",$sql_prof_ID, $classe_Rslisteactivite,$matiere_Rslisteactivite,$gic_ID_Rslisteactivite,$date1,$date2,$sql_prof,$sql_groupe,$sql_publier,$date1,$date2);
}
$Rslisteactivite = mysqli_query($conn_cahier_de_texte, $query_Rslisteactivite) or die(mysqli_error($conn_cahier_de_texte));
$row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite);
$totalRows_Rslisteactivite = mysqli_num_rows($Rslisteactivite);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMat = sprintf("SELECT * FROM cdt_matiere$arcID WHERE cdt_matiere$arcID.ID_matiere=%s ",intval($_GET['matiere_ID']));
$RsMat = mysqli_query($conn_cahier_de_texte, $query_RsMat) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMat = mysqli_fetch_assoc($RsMat);
$totalRows_RsMat = mysqli_num_rows($RsMat);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = sprintf("SELECT * FROM cdt_classe$arcID WHERE cdt_classe$arcID.ID_classe=%u ",intval($_GET['classe_ID']));
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsNomprof = sprintf("SELECT cdt_emploi_du_temps$arcID.prof_ID,cdt_emploi_du_temps$arcID.matiere_ID,cdt_emploi_du_temps$arcID.classe_ID,cdt_prof.identite,cdt_prof.email,cdt_prof.email_diffus_restreint FROM cdt_emploi_du_temps$arcID,cdt_prof WHERE cdt_prof.ID_prof=cdt_emploi_du_temps$arcID.prof_ID
	AND cdt_emploi_du_temps$arcID.matiere_ID=%u AND cdt_emploi_du_temps$arcID.classe_ID=%u LIMIT 1",intval($_GET['matiere_ID']),intval($_GET['classe_ID']));
$RsNomprof = mysqli_query($conn_cahier_de_texte, $query_RsNomprof) or die(mysqli_error($conn_cahier_de_texte));
$row_RsNomprof = mysqli_fetch_assoc($RsNomprof);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>
<?php 
if(isset($_GET['regroupement'])){echo '(R) '.$_GET['regroupement'];}
else {	echo $row_RsClasse['nom_classe'];	};	?>
&nbsp;
<?php echo $row_RsMat['nom_matiere'].' &nbsp;';
if( isset($_GET['archivID'])){ 
	// Nom archive
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv = "SELECT * FROM cdt_archive WHERE NumArchive=".$_GET['archivID'];
	$RsArchiv = mysqli_query($conn_cahier_de_texte, $query_RsArchiv) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsArchiv = mysqli_fetch_assoc($RsArchiv);
	echo '[ '.$row_RsArchiv['NomArchive'].' ]&nbsp;'; 
};
echo '&nbsp;';

//eleve
if (!isset($_SESSION['identite'])){echo $row_RsNomprof['identite'];};
if ((isset($_GET['ens_consult']))&&($_SESSION['droits']==4)){echo '&nbsp;('.$_GET['ens_consult'].')&nbsp;-&nbsp; ';} ;
//invite
if ((isset($_GET['ens_consult']))&&($_SESSION['droits']==5)){echo '&nbsp;('.$_GET['ens_consult'].')&nbsp;-&nbsp; ';} ;
if (isset($_SESSION['identite'])){echo $_SESSION['identite'];};
?>
</title>
<meta content="yes" name="apple-mobile-web-app-capable" />
<meta content="index,follow" name="robots" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="pics/homescreen.png" rel="apple-touch-icon" />
<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />
<link href="css/style.css" rel="stylesheet" media="screen" type="text/css" />
<script src="javascript/functions.js" type="text/javascript"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_callJS(jsStr) { //v2.0
	return eval(jsStr)
}
//-->
</script>
<?php
if((isset($_SESSION['droits']))&&($_SESSION['droits']==4)){ 
	echo '<script language="javascript" type="text/javascript" src="../jscripts/ajax_functions.js"></script>';
};
?>
</head>
<body >
<div id="topbar">
<div id="leftnav">
<a href="<?php 
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==2)){echo'enseignant/enseignant.php';}
else 
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==4))
{
	echo "direction/cdt_enseignant.php?ID_consult=";
	if (isset($_GET['ID_consult'])){ echo $_GET['ID_consult'];};
	echo "&ens_consult=";
	if (isset($_GET['ens_consult'])){echo $_GET['ens_consult'];};
}
else
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==5))
	{	echo "invite/invite.php";}
else
{echo 'consulter.php?classe_ID='.intval($_GET['classe_ID']).'&tri=date';};?>"><img src="images/navleft.png" alt="Accueil" width="26" height="20" border="0"></a>
</div><div id="title"><?php 
if(isset($_GET['regroupement'])){echo '(R) '.$_GET['regroupement'];}
else
{
	echo $row_RsClasse['nom_classe'];
};
echo "&nbsp;&ndash;&nbsp;".$row_RsMat['nom_matiere'];

if( isset($_GET['archivID'])){ 
	
	// Nom archive
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv = "SELECT * FROM cdt_archive WHERE NumArchive=".$_GET['archivID'];
	$RsArchiv = mysqli_query($conn_cahier_de_texte, $query_RsArchiv) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsArchiv = mysqli_fetch_assoc($RsArchiv);
	
	echo '[ '.$row_RsArchiv['NomArchive'].' ]&nbsp;'; 
}; ?></div> 
</div>
<div id="content">
<span class="graytitle"><?php 
//eleve
if (!isset($_SESSION['identite'])){?>
<img src="../images/identite.gif" ><?php echo $row_RsNomprof['identite'];};
if ((isset($_GET['ens_consult']))&&($_SESSION['droits']==4)){echo '&nbsp;('.$_GET['ens_consult'].')&nbsp;-&nbsp; ';} ;
//invite
if ((isset($_GET['ens_consult']))&&($_SESSION['droits']==5)){echo '&nbsp;('.$_GET['ens_consult'].')&nbsp;-&nbsp; ';} ;


if (isset($_SESSION['identite'])){?><img src="../images/identite.gif" >&nbsp;<?php echo $_SESSION['identite'];};

if (($row_RsNomprof['email']<>'')&&($row_RsNomprof['email_diffus_restreint']=='N')){ ?>
	&nbsp;<a href="mailto:<?php echo $row_RsNomprof['email'];?>"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant" title="Contacter l'enseignant"/></a>
<?php };?>
</span>
<ul class="pageitem">
<?php 
$var_consult='';
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==2)&&isset($_GET['annot'])){$annot_affich='&annot';} else {$annot_affich='';};
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==4)&&isset($_GET['ID_consult'])){$var_consult='&ID_consult='.$_GET['ID_consult'].'&ens_consult='.$_GET['ens_consult'];};
if (isset($_GET['gic_ID'])&&isset($_GET['regroupement'])){$var_regroupement='&gic_ID='.$_GET['gic_ID'].'&regroupement='.$_GET['regroupement'];} else {$var_regroupement='';};

?>
<form name="frm" method="POST" action="lire.php?classe_ID=<?php echo $_GET['classe_ID'];?>&matiere_ID=<?php echo $_GET['matiere_ID'].$annot_affich.$var_consult.$var_regroupement;if (isset($_GET['archivID'])) {?>&archivID=<?php echo $_GET['archivID'];};if (isset($_GET['prof_ID'])){echo '&prof_ID='.$_GET['prof_ID'];};?>">
<li class="textbox">
<table width="100%" border="0">
<tr>
<td width="90%"><select name="groupe" size="1" id="select"  class="monselect">
<?php do {  ?>
	<option value="<?php echo $row_Rsgroupe['groupe']?>"<?php if ((isset($_POST['groupe'])) AND ($_POST['groupe']==$row_Rsgroupe['groupe'] )) {echo 'selected';} else {if (!(isset($_POST['groupe'])) AND ($row_Rsgroupe['groupe']=='Classe entière')) {echo 'selected';};};?>><?php echo $row_Rsgroupe['groupe']?></option>
	<?php
} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
$rows = mysqli_num_rows($Rsgroupe);
if($rows > 0) {
	mysqli_data_seek($Rsgroupe, 0);
	$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
}
?>
</select>
</td>
<td rowspan="3" width="10%"><input border=0 name="submit" class="Fsubmit" value="Actualiser" type="image" src="images/boutonEnvoi.png" align="middle" /></td>
</tr>
<tr>
<td><table width="100%" border="0" align="center">
<tr> <td width="5%">Du </td>
<td align="center" width="22%">
<select name="date_jour1" class="monselect">
<?php   
$date1_form=substr($date1,6,2).'/'.substr($date1,4,2).'/'.substr($date1,0,4);
$date2_form=substr($date2,6,2).'/'.substr($date2,4,2).'/'.substr($date2,0,4);
for($i=1;$i < 32;$i++) {   
	echo '<option value="'.($i<10? '0':'').$i.'"'.($i==substr($date1,6,2)? ' selected':'').'> '.($i<10? '0':'').$i.'</option>';
}
?>
</select></td> <td width="3%">/</td>
<td align="center" width="22%"><select name="date_mois1" class="monselect">
<?php
for($i=1;$i < 13;$i++) {   
	echo '<option value="'.($i<10? '0':'').$i.'"'.($i==substr($date1,4,2)? ' selected':'').'> '.($i<10? '0':'').$i.'</option>';
}
?>
</select> </td> 
<td width="3%">/</td>
<td align="center"width="45%"><select name="date_annee1" class="monselect"> 
<?php 
IF(substr($date1,0,4)==substr($date2,0,4))
{
	?>
	<option value="<?php echo (substr($date1,0,4)-1);?>"> <?php echo (substr($date1,0,4)-1);?></option>
	<option value="<?php echo substr($date1,0,4);?>" selected> <?php echo substr($date1,0,4);?></option>
	<?php  
}
else {                       
	?> 
	<option value="<?php echo substr($date1,0,4);?>" selected> <?php echo substr($date1,0,4);?></option>
	<option value="<?php echo (substr($date1,0,4)+1);?>"> <?php echo (substr($date1,0,4)+1);?></option> 
	<?php 
}
?>  

</select>
</td>
</tr>
</table>
</td>

</tr>
<tr><td><table width="100%" border="0" align="center">
<tr> <td width="5%">au </td>
<td align="center" width="22%">
<select name="date_jour2">
<?php   
for($i=1;$i < 32;$i++) {   
	echo '<option value="'.($i<10? '0':'').$i.'"'.($i==substr($date2,6,2)? ' selected':'').'> '.($i<10? '0':'').$i.'</option>';
}
?>
</select></td> <td width="3%">/</td>
<td align="center" width="22%"><select name="date_mois2">
<?php
for($i=1;$i < 13;$i++) {   
	echo '<option value="'.($i<10? '0':'').$i.'"'.($i==substr($date2,4,2)? ' selected':'').'> '.($i<10? '0':'').$i.'</option>';
}
?>
</select> </td> <td width="3%">/</td>
<td align="center"width="45%"><select name="date_annee2"> 
<?php IF (substr($date2,4,2)<9)  {  ?>          	
	<option value="<?php echo (substr($date2,0,4)-1);?>"> <?php echo (substr($date2,0,4)-1);?></option>
<?php }  ?>
<option value="<?php echo substr($date2,0,4);?>" selected> <?php echo substr($date2,0,4);?></option>
<?php IF (substr($date2,4,2)>8)  {  ?>
	<option value="<?php echo (substr($date2,0,4)+1);?>"> <?php echo (substr($date2,0,4)+1);?></option>
<?php }  ?>   
</select>
</td>
</tr>
</table>
</tr>
</table>
<table width="100%" border="0">
<tr>
<td width=65% valign="middle"><p>Ordre chronologique :</p></td>
<td><input name="chrono" type="checkbox" id="chrono" value="checkbox" <?php if ($ordre=='up'){echo 'checked';}?>></td>
</tr>
</table> 
</li>
</form>
</ul>
<?php 
if ($totalRows_Rslisteactivite  >0){
	do { 
		if ($row_Rslisteactivite['classe_ID']<>0){  	
			$query_Rs_Travail2 = sprintf("SELECT * FROM cdt_travail$arcID WHERE code_date='%s' AND matiere_ID=%u AND classe_ID=%u AND agenda_ID=%u ORDER BY code_date", $row_Rslisteactivite['code_date'],$_GET['matiere_ID'],$_GET['classe_ID'],$row_Rslisteactivite['ID_agenda']);
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
			?>
			<span class="graytitle"><?php echo $row_Rslisteactivite['jour_pointe'];?>
			&nbsp;
			<?php if ($row_Rslisteactivite['semaine']<>'A et B'){ 
				echo '(';
				if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){
					if ($row_Rslisteactivite['semaine']=='A'){echo 'P';} else {echo 'I';};
				} 
				else {
					echo $row_Rslisteactivite['semaine'];
				};
			echo')';}; 
			if ($row_Rslisteactivite['gic_ID']>0){	  
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_Rsgic =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses$arcID WHERE  cdt_groupe_interclasses$arcID.ID_gic=%u ",$row_Rslisteactivite['gic_ID']);
				$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
				$row_Rsgic = mysqli_fetch_assoc($Rsgic);
				echo '<br />(R) '.$row_Rsgic['nom_gic'].'';
			};    
			?>
			</span>
			<ul class="pageitem">
			<li class="textbox">
			<p>
			<?php 
			if ($row_Rslisteactivite['type_activ']<>'ds_prog')
				{echo'<u><font color="'.$row_Rslisteactivite['couleur_activ'].'"><strong>'.$row_Rslisteactivite['type_activ'].'</strong></font></u>'.'&nbsp;'.$row_Rslisteactivite['theme_activ'].(substr($row_Rslisteactivite['code_date'],8,1)==0?'<br /><font color="#FF0000"><b>Heure Sup.</b></font>':''); }    
			else {echo '<font color="#FF0000"><b>'.$_SESSION['libelle_devoir'].'</b></font>'; }
			?>
			<br />
			<?php 
			############################################ contenu séance ###############################################"" -->
			$date_jour='20'.date("ymd").'1'; 
			//nettoyage des balises <p> et des <br /> supplémentaires :      
			$contenu = str_replace("<p>", "<br />", $row_Rslisteactivite['activite']);
			$contenu = str_replace("</p>", "", $contenu);
			$contenu = trim($contenu); $contenu = trim($contenu); 
			IF (substr($contenu, 0, 4) == "<br />") $contenu = substr($contenu, 4);
			IF (substr($contenu, -4, 4) == "<br />") $contenu = substr($contenu, 0, strlen($contenu)-4); 
			IF (substr($contenu, 0, 6) == "<br />") $contenu = substr($contenu, 6);
			IF (substr($contenu, -6, 6) == "<br />") $contenu = substr($contenu, 0, strlen($contenu)-6); 
			//On supprime les lignes inutiles dans le texte, Smartphone oblige et on y met la dose...
			FOR($ip=0; $ip<11; $ip++){
				$contenu = str_replace("<br /><br />", "<br />", $contenu);
				$contenu = str_replace("<br /><br />", "<br />", $contenu);  
			}  
			//on vire les imagettes contenues dans les balises div car ces balises reformatent le texte 
			$pattern = "(<div style=\"text-align: left; padding: 5px; float: left;\">(.|\n)*</div>)"; 
			$contenu = ereg_replace($pattern,"",$contenu);
			echo $contenu; 
			if (isset($_SESSION['nom_prof'])){
				if (($row_Rslisteactivite['rq']<>'')&&(isset($_GET['annot']))){echo '<br />Rq : '.$row_Rslisteactivite['rq'];}
			};
			############################################## traitement du visa ############################################################
			if((isset($_SESSION['droits']))&&($_SESSION['droits']==4)){ 
				if (substr($row_Rslisteactivite['date_visa'],0,4)=='0000'){
				echo '<div align="right" id="image'.$row_Rslisteactivite['ID_agenda'].'" ><img src="../images/tampon3.gif" onclick="go_visa('.$row_Rslisteactivite['ID_agenda'].','.$row_Rslisteactivite['prof_ID'].')"></div>';}
				else 
				{
					echo '<div align="right"id="image'.$row_Rslisteactivite['ID_agenda'].'" ><img src="../images/visa.gif" onclick="go_visa_supprime('.$row_Rslisteactivite['ID_agenda'].','.$row_Rslisteactivite['prof_ID'].')" ><br ><strong>Le '.substr($row_Rslisteactivite['date_visa'],8,2).'/'.substr($row_Rslisteactivite['date_visa'],5,2).'/'.substr($row_Rslisteactivite['date_visa'],0,4).'</strong></div>';
				};
			};         
			######################################### affichage fichiers joints seance #########################################				
			$refagenda_RsFichiers = "0";
			if (isset($row_Rslisteactivite['ID_agenda'])) {
				$refagenda_RsFichiers = (get_magic_quotes_gpc()) ? 
				$row_Rslisteactivite['ID_agenda'] : addslashes($row_Rslisteactivite['ID_agenda']);
			}
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_RsFichiers = sprintf("SELECT * FROM cdt_fichiers_joints$arcID WHERE cdt_fichiers_joints$arcID.agenda_ID=%u  AND cdt_fichiers_joints$arcID.type<>'Travail'", $refagenda_RsFichiers);
			$RsFichiers = mysqli_query($conn_cahier_de_texte, $query_RsFichiers) or die(mysqli_error($conn_cahier_de_texte));
			//$row_RsFichiers = mysqli_fetch_assoc($RsFichiers);
			$totalRows_RsFichiers = mysqli_num_rows($RsFichiers);
			if ($totalRows_RsFichiers<>0){echo'<br />Document'.($totalRows_RsFichiers>1?'s':'').' de cours : ';
				while ($row_RsFichiers = mysqli_fetch_assoc($RsFichiers)) { 
					$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_RsFichiers['nom_fichier']);   
					
					echo '<a href="../fichiers_joints/'.$row_RsFichiers['nom_fichier'].'" target="_blank"><strong>'.$nom_f.'</a>';
					
				} ; 
			};
			mysqli_free_result($RsFichiers);
			######################################### Fin affichage fichiers joints seance #########################################
			################################# AFFICHAGE DU TRAVAIL EN PREPARATION ########################################################
			for ($il=1; $il<4; $il++){
				if ( $date_a_faire[$il]<>''){//IF 1
					echo '<br /><u>'.$t_groupe[$il].' pour le <b>'.jour_semaine($date_a_faire[$il]).' '.$date_a_faire[$il].'</b></u> :';
					//affichage fichiers travail joints
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$sql_f="SELECT * FROM cdt_fichiers_joints$arcID WHERE cdt_fichiers_joints$arcID.agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND cdt_fichiers_joints$arcID.type ='Travail' AND cdt_fichiers_joints$arcID.t_code_date ='".$date_a_faire[$il]."' AND cdt_fichiers_joints$arcID.ind_position = 1 ORDER BY cdt_fichiers_joints$arcID.nom_fichier";
					$query_Rs_fichiers_joints_form = $sql_f;
					$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
					$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
					$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);  
					//nettoyage des balises <p> et des <br /> supplémentaires pour les addicts de la touche enter :      
					$contenu = str_replace("<p>", "<br />", $travail[$il]);
					if (!(strcmp($eval[$il],'O'))){$contenu = "<span style='color:red;'><strong>Evaluation : </strong></span>"+$contenu;};
					$contenu = str_replace("</p>", "", $contenu);
					$contenu = trim($contenu); $contenu = trim($contenu); 
					IF (substr($contenu, 0, 4) == "<br />") $contenu = substr($contenu, 4);
					IF (substr($contenu, -4, 4) == "<br />") $contenu = substr($contenu, 0, strlen($contenu)-4); 
					IF (substr($contenu, 0, 6) == "<br />") $contenu = substr($contenu, 6);
					IF (substr($contenu, -6, 6) == "<br />") $contenu = substr($contenu, 0, strlen($contenu)-6); 
					//On supprime les lignes inutiles dans le texte, Smartphone oblige et on y met la dose...
					FOR($ip=0; $ip<11; $ip++){
						$contenu = str_replace("<br /><br />", "<br />", $contenu);
						$contenu = str_replace("<br /><br />", "<br />", $contenu);  
					}
					echo ' <br />'.$contenu.' ';
					if ($totalRows_Rs_fichiers_joints_form<>0)
					{ //IF 2
						if ($totalRows_Rs_fichiers_joints_form==1) {echo 'avec le document ';} else  {echo 'avec les documents ';};
						do { 
							
							echo "<a href=\"../fichiers_joints/".$row_Rs_fichiers_joints_form['nom_fichier']."\" target=\"_blank\">";          
							
							$exp = "/^[0-9]+_/";
							$nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
							echo $nom_f."</a>&nbsp;";           
						} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
					}//IF 2
					mysqli_free_result($Rs_fichiers_joints_form);
					//fin affichage des fichiers travail joints
				} //IF 1
			}
			################################# FIN AFFICHAGE DU TRAVAIL EN PREPARATION ########################################################
			//fin affichage fichiers joints travail
			
			
			?>
			</li></ul>
		<?php  }
		else {
			
			?>
			<ul class="pageitem"><li class="textbox"><p><?php echo $row_Rslisteactivite['heure_debut'].' - '.$row_Rslisteactivite['heure_fin']; ?>&nbsp;<?php echo $row_Rslisteactivite['theme_activ']; ?></p>
			</li></ul>
			<?php
			//}
		};
		
		
		
	} while ($row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite)); 
}

// affichage du visa
if ($totalRows_RsPublier2>0) {
	$date_actu=substr($row_RsPublier2['date_maj'],8,2).'/'.substr($row_RsPublier2['date_maj'],5,2).'/'.substr($row_RsPublier2['date_maj'],0,4);
	if (substr($row_RsPublier2['date_maj'],8,2)<>'00')  {echo "<div id=\"visa\" class=\"no_imprime\" ><p><img src=\"images/visa.gif\" ></p>
	<p>".jour_semaine($date_actu).' '.$date_actu." </p></div> ";};
};?>
<div id="topbar">
<div id="leftnav">
<a href="<?php 
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==2)){echo'enseignant/enseignant.php';}
else 
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==4))
{
	echo "direction/cdt_enseignant.php?ID_consult=";
	if (isset($_GET['ID_consult'])){ echo $_GET['ID_consult'];};
	echo "&ens_consult=";
	if (isset($_GET['ens_consult'])){echo $_GET['ens_consult'];};
}
else
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==5))
	{	echo "invite/invite.php";}
else
{echo 'consulter.php?classe_ID='.intval($_GET['classe_ID']).'&tri=date';};?>"><img src="images/navleft.png" alt="Accueil" width="26" height="20" border="0"></a>
</div><div id="title"><?php 
if(isset($_GET['regroupement'])){echo '(R) '.$_GET['regroupement'];}
else
{
	echo $row_RsClasse['nom_classe'];
};
echo "&nbsp;&ndash;&nbsp;".$row_RsMat['nom_matiere'];

if( isset($_GET['archivID'])){ 
	
	// Nom archive
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv = "SELECT * FROM cdt_archive WHERE NumArchive=".$_GET['archivID'];
	$RsArchiv = mysqli_query($conn_cahier_de_texte, $query_RsArchiv) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsArchiv = mysqli_fetch_assoc($RsArchiv);
	
	echo '[ '.$row_RsArchiv['NomArchive'].' ]&nbsp;'; 
}; ?></div> 
</div>

</div> 
<?php include("footer.php"); ?>
</body>
</html>
<?php
mysqli_free_result($RsNomprof);
if (isset($Rs_Travail2)){mysqli_free_result($Rs_Travail2);};
if (isset($RsPublier2)){mysqli_free_result($RsPublier2);};
if (isset($Rsgroupe)){mysqli_free_result($Rsgroupe);};
mysqli_free_result($Rslisteactivite);
mysqli_free_result($RsMat);
mysqli_free_result($RsMinDate);
mysqli_free_result($RsClasse);
?>
