<?php 
session_start();

if (isset($_SESSION['nom_prof'])){$_SESSION['consultation']=$_GET['classe_ID'];};
//on filtre
if ((isset($_GET['classe_ID']))&&($_GET['classe_ID']!=intval($_GET['classe_ID']))){  header("Location: index.php");exit;};
if ((isset($_GET['matiere_ID']))&&($_GET['matiere_ID']!=intval($_GET['matiere_ID']))){  header("Location: index.php");exit;};
if ((isset($_GET['prof_ID']))&&($_GET['prof_ID']!=intval($_GET['prof_ID']))){  header("Location: index.php");exit;};
if ((!isset($_SESSION['consultation'])||($_SESSION['consultation']!=$_GET['classe_ID']))){  header("Location: index.php");exit;};

if(isset($_POST['MM_copie_archive'])){
//Copie d'une seance dans une archive
if ((isset($_POST['ID_agenda']))&&($_POST['ID_agenda']<>'')){$_SESSION['copie']=$_POST['ID_agenda'];};
if ((isset($_GET['archivID']))&&($_GET['archivID']!=intval($_GET['archivID']))){ header("Location: index.php");exit;};
if ((isset($_GET['archivID']))&&($_GET['archivID']<>'')){$_SESSION['archivID']=$_GET['archivID'];};
};

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
$query_RsPublier2 = "SELECT date_maj FROM cdt_prof WHERE droits=4 ";
$RsPublier2 = mysqli_query($conn_cahier_de_texte, $query_RsPublier2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsPublier2 = mysqli_fetch_assoc($RsPublier2);
$totalRows_RsPublier2= mysqli_num_rows($RsPublier2);

//restriction d'affichage

if (!isset($_SESSION['ID_prof'])) {$sql_publier="AND cdt_prof.publier_cdt='O'";} else {$sql_publier='';};

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
$query_RsMinDate = "SELECT * FROM cdt_agenda$arcID WHERE code_date > 0 ORDER BY code_date ASC LIMIT 1";
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

//eleve
if ((!isset($_SESSION['ID_prof']))&&(isset($_GET['prof_ID']))){
		//rechercher si le prof est remplacant
$query_RsProf_remplace = sprintf("SELECT * FROM cdt_remplacement WHERE remplacant_ID = %u LIMIT 1",$_GET['prof_ID']);
$RsProf_remplace = mysqli_query($conn_cahier_de_texte, $query_RsProf_remplace) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf_remplace = mysqli_fetch_assoc($RsProf_remplace);
$totalRows_RsProf_remplace = mysqli_num_rows($RsProf_remplace);

if($totalRows_RsProf_remplace==0){
$sql_prof_ID=intval($_GET['prof_ID']);
} else {
$sql_prof_ID=intval($_GET['prof_ID']). ' OR prof_ID ='.$row_RsProf_remplace['titulaire_ID']. ' ';
};
} 
else {
if (isset($_SESSION['ID_prof'])){$sql_prof_ID=$_SESSION['ID_prof'];} else {$sql_prof_ID=0;};
};


//profs
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==2)){
	if (isset($_GET['afficher_titulaire'])){ //prof remplacant - afficher ici le titulaire
		$sql_prof='AND cdt_agenda'.$arcID.'.prof_ID = '.$_SESSION['id_remplace'];
		$sql_prof_ID=intval($_SESSION['id_remplace']);
	} 
	else
	{
		$sql_prof='AND cdt_agenda'.$arcID.'.prof_ID = '.$_SESSION['ID_prof'];
	};
} else {$sql_prof='';};


//chef etablissement
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==4)){
	$sql_prof='AND cdt_agenda'.$arcID.'.prof_ID = '.intval($_GET['ID_consult']);
	$sql_prof_ID=intval($_GET['ID_consult']);
} else {$sql_prof='';};

//invite
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==5)&&(isset($_GET['prof_ID']))){
	$sql_prof_ID=intval($_GET['prof_ID']);
} ;


$today=date('Ymd'); $today_form=date('j/m/Y');

if (isset($_POST['date2'])){$date2=substr($_POST['date2'],6,4).substr($_POST['date2'],3,2).substr($_POST['date2'],0,2);} else {$date2=$today;};

if (isset($_POST['anneeentiere'])) {
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsPublier = sprintf("SELECT MIN(code_date) FROM cdt_agenda$arcID WHERE prof_ID=%u",$sql_prof_ID);
        $RsPublier = mysqli_query($conn_cahier_de_texte, $query_RsPublier) or die(mysqli_error($conn_cahier_de_texte));
        $row_RsPublier = mysqli_fetch_assoc($RsPublier);
        $date1 = $row_RsPublier['MIN(code_date)'];
        $date1=substr($date1,0,8);$date2=$today;
} else if (isset($_POST['date1'])){$date1=substr($_POST['date1'],6,4).substr($_POST['date1'],3,2).substr($_POST['date1'],0,2);} 
else if (isset($_GET['archivID'])) {$date1='00000000';} //Permet l'affichage initial de toute l'archive
else {
        
        $mois_tmp=substr($date2,4,2)-1;
	
        if($mois_tmp<10) $mois_tmp = "0".$mois_tmp;
        if($mois_tmp=='00') { 
                $annee_tmp=substr($date2,0,4)-1;
                $date_tmp=$annee_tmp.'12'.substr($date2,6,2);
        } else {
                $date_tmp=substr($date2,0,4). $mois_tmp.substr($date2,6,2);
        };
        if ($date_tmp>substr($row_RsMinDate['code_date'],0,8)){$date1=$date_tmp;} else {$date1=substr($row_RsMinDate['code_date'],0,8);};
};
mysqli_free_result($RsMinDate);

//en mode eleve, interdire l'affichage du cahier posterieur a la date du jour
if (!isset($_SESSION['nom_prof'])){
        if ( $date2>$today){$date2=$today;};
};

$date1_form=substr($date1,6,2).'/'.substr($date1,4,2).'/'.substr($date1,0,4);
$date2_form=substr($date2,6,2).'/'.substr($date2,4,2).'/'.substr($date2,0,4);

if (isset($_POST['chrono'])) {$ordre='ASC'; } else {$ordre='DESC';};


if (isset($_POST['groupe'])){
        if ($_POST['groupe']=='Classe entière'){$sql_groupe='';}
        else { $sql_groupe="AND (cdt_agenda$arcID.groupe='Classe entière' OR cdt_agenda$arcID.groupe=".GetSQLValueString($_POST['groupe'], 'text').")";};
}
else {$sql_groupe='';};

$sql_partage='';
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$requete = "SHOW TABLES LIKE 'cdt_emploi_du_temps_partage$arcID'";
$exec = mysqli_query($conn_cahier_de_texte, $requete);
$compteur_table = mysqli_num_rows($exec);

if ($compteur_table>0) {   // Test d'existence de la table cdt_emploi_du_temps_partage$arcID (posterieure a la creation des archives)
	
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_Rs_partage = sprintf("SELECT ID_emploi FROM cdt_emploi_du_temps_partage$arcID WHERE profpartage_ID=%u",GetSQLValueString($sql_prof_ID,"int"));
        $Rs_partage = mysqli_query($conn_cahier_de_texte, $query_Rs_partage) or die(mysqli_error($conn_cahier_de_texte));

        
        while ($row_Rs_partage = mysqli_fetch_assoc($Rs_partage)) {
                $sql_partage .= sprintf("OR (cdt_agenda$arcID.partage='O' AND cdt_agenda$arcID.emploi_ID=%u AND cdt_agenda$arcID.classe_ID = '%u'
                        AND cdt_agenda$arcID.matiere_ID = '%u'
                        AND cdt_agenda$arcID.gic_ID = '%u'
                        AND SUBSTRING(cdt_agenda$arcID.code_date,1,8) >= '%u'
                        AND SUBSTRING(cdt_agenda$arcID.code_date,1,8) <= '%u' 
                        %s %s %s
                        )",$row_Rs_partage['ID_emploi'],$classe_Rslisteactivite,$matiere_Rslisteactivite,$gic_ID_Rslisteactivite,$date1,$date2,$sql_prof,$sql_groupe,$sql_publier);
        };
	mysqli_free_result($Rs_partage);
};


if (!isset($_GET['regroupement'])) {
$regroupement='';}else{$regroupement= " AND cdt_agenda$arcID.gic_ID ='".$gic_ID_Rslisteactivite."' ";};

$query_Rslisteactivite = sprintf("
	SELECT DISTINCT *
	FROM cdt_agenda$arcID
	LEFT JOIN cdt_prof ON cdt_agenda$arcID.prof_ID = cdt_prof.ID_prof
	WHERE (
        ((cdt_agenda$arcID.prof_ID = %s
        AND cdt_agenda$arcID.partage = 'N')
	OR cdt_agenda$arcID.partage = 'O')
        AND cdt_agenda$arcID.classe_ID = '%u'
        AND cdt_agenda$arcID.matiere_ID = '%u'
        %s
        AND SUBSTRING(cdt_agenda$arcID.code_date,1,8) >= '%u'
        AND SUBSTRING(cdt_agenda$arcID.code_date,1,8) <= '%u' 
        %s %s %s
        )
        OR (
        cdt_agenda$arcID.classe_ID = 0
        AND cdt_agenda$arcID.theme_activ<>'Remplacement'
        AND SUBSTRING(cdt_agenda$arcID.code_date,1,8) >= '%u'
        AND SUBSTRING(cdt_agenda$arcID.code_date,1,8) <= '%u'
        ) %s 
        OR (prof_ID=%u AND theme_activ='Remplacement')
        ORDER BY cdt_agenda$arcID.code_date %s",$sql_prof_ID,$classe_Rslisteactivite,$matiere_Rslisteactivite,$regroupement,$date1,$date2,$sql_prof,$sql_groupe,$sql_publier,$date1,$date2,$sql_partage,$sql_prof_ID,$ordre);             
//echo $query_Rslisteactivite;

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Rslisteactivite = mysqli_query($conn_cahier_de_texte, $query_Rslisteactivite) or die(mysqli_error($conn_cahier_de_texte));

// Recherche des dates extremes d'une archive
if ((isset($_GET['archivID']))&(!isset($_POST['date2']))) {
	mysqli_data_seek($Rslisteactivite, 0);
	$row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite);
	$d1=$row_Rslisteactivite['code_date'];
	$totalRows_Rslisteactivite = mysqli_num_rows($Rslisteactivite);
	mysqli_data_seek($Rslisteactivite, $totalRows_Rslisteactivite-1);
	$row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite);
	$d2=$row_Rslisteactivite['code_date'];
	mysqli_data_seek($Rslisteactivite, 0);
	$row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite);
	if ($d1<$d2) {
		$date1_form=substr($d1,6,2).'/'.substr($d1,4,2).'/'.substr($d1,0,4);
		$date2_form=substr($d2,6,2).'/'.substr($d2,4,2).'/'.substr($d2,0,4);
	} else {
		$date2_form=substr($d1,6,2).'/'.substr($d1,4,2).'/'.substr($d1,0,4);
		$date1_form=substr($d2,6,2).'/'.substr($d2,4,2).'/'.substr($d2,0,4);
	};
} else {
	$row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite);
	$totalRows_Rslisteactivite = mysqli_num_rows($Rslisteactivite);    
};


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMat = sprintf("SELECT * FROM cdt_matiere$arcID WHERE ID_matiere=%u ",intval($_GET['matiere_ID']));
$RsMat = mysqli_query($conn_cahier_de_texte, $query_RsMat) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMat = mysqli_fetch_assoc($RsMat);
$totalRows_RsMat = mysqli_num_rows($RsMat);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = sprintf("SELECT * FROM cdt_classe$arcID WHERE ID_classe=%u ",intval($_GET['classe_ID']));
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
//eleve
if ((!isset($_SESSION['ID_prof']))&&(isset($_GET['prof_ID']))){
	$query_RsNomprof = sprintf("SELECT cdt_prof.identite,cdt_prof.email,cdt_prof.email_diffus_restreint FROM cdt_prof WHERE ID_prof=%u",intval($_GET['prof_ID']));
}
else {
        //autre
        $query_RsNomprof = sprintf("SELECT cdt_emploi_du_temps$arcID.prof_ID,cdt_emploi_du_temps$arcID.matiere_ID,cdt_emploi_du_temps$arcID.classe_ID,cdt_prof.identite,cdt_prof.nom_prof,cdt_prof.email,cdt_prof.email_diffus_restreint FROM cdt_emploi_du_temps$arcID,cdt_prof WHERE cdt_prof.ID_prof=cdt_emploi_du_temps$arcID.prof_ID
                AND cdt_emploi_du_temps$arcID.matiere_ID=%u AND cdt_emploi_du_temps$arcID.classe_ID=%u LIMIT 1",intval($_GET['matiere_ID']),intval($_GET['classe_ID']));
};
$RsNomprof = mysqli_query($conn_cahier_de_texte, $query_RsNomprof) or die(mysqli_error($conn_cahier_de_texte));
$row_RsNomprof = mysqli_fetch_assoc($RsNomprof);
$identite_prof=$row_RsNomprof['identite']==''?$row_RsNomprof['nom_prof']:$row_RsNomprof['identite'];


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier De Textes - <?php 
if(isset($_GET['regroupement'])){echo '(R) '.$_GET['regroupement'];}
else {  echo $row_RsClasse['nom_classe'];       };      ?>
&nbsp;&nbsp;
<?php if (isset($_POST['groupe'])){echo $_POST['groupe'];} else {echo 'Classe entière';};?>
&nbsp;&nbsp;<?php echo $row_RsMat['nom_matiere'].' &nbsp;';
if( isset($_GET['archivID'])){ 
        // Nom archive
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv = sprintf("SELECT * FROM cdt_archive WHERE NumArchive=%u",$_GET['archivID']);
	$RsArchiv = mysqli_query($conn_cahier_de_texte, $query_RsArchiv) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsArchiv = mysqli_fetch_assoc($RsArchiv);
        echo '['.$row_RsArchiv['NomArchive'].']&nbsp;'; 
};
echo '&nbsp;&nbsp;';

//eleve
if (!isset($_SESSION['identite'])){echo $identite_prof;};
if ((isset($_GET['ens_consult']))&&($_SESSION['droits']==4)){echo '&nbsp;('.$_GET['ens_consult'].')&nbsp;-&nbsp; ';} ;
//invite
if ((isset($_GET['ens_consult']))&&($_SESSION['droits']==5)){echo '&nbsp;('.$_GET['ens_consult'].')&nbsp;-&nbsp; ';} ;
if (isset($_SESSION['identite'])){echo $_SESSION['identite'];};
?>

</title>
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

#visa {
	position:absolute;
	width:12px;
	height:115px;
	z-index:999;
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

<script language="JavaScript" type="text/JavaScript">

function MM_callJS(jsStr) { //v2.0
        return eval(jsStr);
};
  function Message() {
    alert('Votre copie a bien \351t\351 effectu\351e. Pour b\351n\351ficier du collage dans votre cahier de textes, pensez \340 rafraichir la page de saisie de votre cahier de textes.');
}


</script>
<?php
if((isset($_SESSION['droits']))&&($_SESSION['droits']==4)){ 
	echo '<script language="javascript" type="text/javascript" src="jscripts/ajax_functions.js"></script>';
};

?>
<script type="text/javascript" src="enseignant/xinha/plugins/Equation/ASCIIMathML.js"></script>
<script type="text/javascript" src="./jscripts/jquery-1.6.2.js"></script>
<script type="text/javascript" src="./jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="./jscripts/jquery-ui.datepicker-fr.js"></script>

</head>
<body >
<p>
<table width="90%"  border="0" align="center" cellpadding="0" cellspacing="0" <?php if (isset($_SESSION['nom_prof'])){;} else {echo 'class="lire_bordure"';};?> >
<tr class="lire_cellule_4">
<td class="black_police"><?php 
if(isset($_GET['regroupement'])){echo '(R) '.$_GET['regroupement'];}
else
{
	echo $row_RsClasse['nom_classe'];
};
mysqli_free_result($RsClasse);
?>
&nbsp;&nbsp;
<?php if (isset($_POST['groupe'])){echo $_POST['groupe'];} else {echo 'Classe entière';};?>
&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $row_RsMat['nom_matiere'].' &nbsp;';
mysqli_free_result($RsMat);

if( isset($_GET['archivID'])){ 
	echo '['.$row_RsArchiv['NomArchive'].']&nbsp;';
	mysqli_free_result($RsArchiv);
};
echo '&nbsp;&nbsp;&nbsp;';

//eleve
if (!isset($_SESSION['identite'])){?>
<img src="images/identite.gif" >&nbsp;<?php echo $identite_prof;};
if ((isset($_GET['ens_consult']))&&($_SESSION['droits']==4)){echo '&nbsp;('.$_GET['ens_consult'].')&nbsp;-&nbsp; ';} ;
//invite
if ((isset($_GET['ens_consult']))&&($_SESSION['droits']==5)){echo '&nbsp;('.$_GET['ens_consult'].')&nbsp;-&nbsp; ';} ;

if (isset($_SESSION['identite'])){?><img src="images/identite.gif" >&nbsp;<?php echo $_SESSION['identite'];};

//remplacement
if ((isset($_GET['afficher_titulaire']))&&($_SESSION['droits']==2)){echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cahier de textes du professeur titulaire&nbsp; ';} ;

if (($row_RsNomprof['email']<>'')&&($row_RsNomprof['email_diffus_restreint']=='N')){ ?>
	&nbsp;<a href="mailto:<?php echo $row_RsNomprof['email'];?>"><img src="images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant" title="Contacter l'enseignant"/></a>
<?php };?>
</td>
<td ><div align="right" class="no_imprime"> <a href="<?php 
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==2)){echo'enseignant/enseignant.php';}
else if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==4))
{
	echo "direction/cdt_enseignant.php?ID_consult=";
	if (isset($_GET['ID_consult'])){ echo GetSQLValueString($_GET['ID_consult'], 'int');};
	echo "&ens_consult=";
	if (isset($_GET['ens_consult'])){echo GetSQLValueString($_GET['ens_consult'], 'int');};
}
else if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==5))
	        {       
			echo "invite/invite.php";
			if (isset($_GET['ID_prof'])){echo '?ID_prof='.$_GET['ID_prof'];};

		}
else {
echo 'consulter.php?classe_ID='.intval($_GET['classe_ID']).'&tri=date';};?>">
<img src="images/home-menu.gif" alt="Accueil" width="26" height="20" border="0"></a><br>
</div></td>
</tr>
<tr valign="baseline" class="lire_cellule_2">
<td class="no_imprime"><?php 
$var_consult='';

if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==4)&&isset($_GET['ID_consult'])){$var_consult='&ID_consult='.$_GET['ID_consult'].'&ens_consult='.$_GET['ens_consult'];};
if (isset($_GET['gic_ID'])&&isset($_GET['regroupement'])){$var_regroupement='&gic_ID='.$_GET['gic_ID'].'&regroupement='.$_GET['regroupement'];} else {$var_regroupement='';};

?>
<form name="frm" method="POST" action="lire.php?classe_ID=<?php echo $_GET['classe_ID'];?>&matiere_ID=<?php echo $_GET['matiere_ID'].$var_consult.$var_regroupement;if (isset($_GET['archivID'])) {?>&archivID=<?php echo $_GET['archivID'];};if (isset($_GET['prof_ID'])){echo '&prof_ID='.$sql_prof_ID;};if (isset($_GET['afficher_titulaire'])){echo '&afficher_titulaire';};?>">
<div align="right" class="Style72">
<input name="anneeentiere" type="submit" value="Ann&eacute;e enti&egrave;re"/>
<select name="groupe" size="1" id="select">
<?php do {  ?>
        <option value="<?php echo $row_Rsgroupe['groupe']?>"<?php if ((isset($_POST['groupe'])) AND ($_POST['groupe']==$row_Rsgroupe['groupe'] )) {echo 'selected';} else {if (!(isset($_POST['groupe'])) AND ($row_Rsgroupe['groupe']=='Classe entière')) {echo 'selected';};};?>><?php echo $row_Rsgroupe['groupe']?></option>
        <?php
} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
$rows = mysqli_num_rows($Rsgroupe);
if($rows > 0) {
	mysqli_data_seek($Rsgroupe, 0);
	$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
}

if (isset($Rsgroupe)){mysqli_free_result($Rsgroupe);};

?>
</select>
<script>
$(function() {
		$.datepicker.setDefaults($.datepicker.regional['fr']);
		var dates = $( "#date1, #date2" ).datepicker({
				defaultDate: "+1w",
				changeMonth: true,
				numberOfMonths: 1,
				firstDay:1,
				onSelect: function( selectedDate ) {
					var option = this.id == "date1" ? "minDate" : "maxDate",
					instance = $( this ).data( "datepicker" ),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
					dates.not( this ).datepicker( "option", option, date );
				}
		});
});
</script> 


du&nbsp;&nbsp;
<input name='date1' type='text' id='date1' value="<?php echo $date1_form;?>" size="10"/>
&nbsp;au&nbsp;&nbsp;
<input  name='date2' type='text' id='date2' value="<?php echo $date2_form;?>" size="10" />
&nbsp;&nbsp;Chronologie<input name="chrono" type="checkbox" id="chrono" value="checkbox" <?php if ($ordre=='ASC'){echo 'checked';}?>> 
&nbsp;&nbsp;Annotations<input name="annot" type="checkbox"  id="annot" value="annot"<?php if (isset($_POST['annot'])){echo ' checked';}?>> 
&nbsp;&nbsp;Choix pr&eacute;sentation
<input type="radio" name="Genre" value="1" <?php if ((isset($_POST['Genre'])) AND ($_POST['Genre']=='1')){echo 'checked';};?>>
1
<input type="radio" name="Genre" value="2" <?php if (isset($_POST['Genre'])) {
if  (($_POST['Genre']<>'1')AND($_POST['Genre']<>'3')){echo 'checked';};}
else {echo 'checked';};  ?>>
2
<input type="radio" name="Genre" value="3" <?php if ((isset($_POST['Genre'])) AND ($_POST['Genre']=='3')){echo 'checked';}?>>
3 &nbsp;&nbsp;<a href="lire_pdf.php?classe_ID=<?php echo $_GET['classe_ID'];
echo isset($_GET['gic_ID'])?'&gic_ID='.$_GET['gic_ID']:'';

//consultation par le Resp. Etablissement
if(isset($_GET['ID_consult'])){ echo '&ID_consult='.GetSQLValueString($_GET['ID_consult'], 'int');};
if(isset($_GET['ens_consult'])){ echo '&ens_consult='.GetSQLValueString($_GET['ens_consult'], 'text');} 
else { echo '&ens_consult='.$identite_prof;};

//invite
if(isset($_GET['prof_ID'])){ echo '&prof_ID='.GetSQLValueString($_GET['prof_ID'], 'int');};
?>
&groupe=
<?php
if(isset($_POST['groupe'])) {echo $_POST['groupe'];}
else {echo 'Classe enti&egrave;re';};
?>
&matiere_ID=<?php echo GetSQLValueString($_GET['matiere_ID'], 'int');?>
&date1=<?php echo $date1;?>
&date2=<?php echo $date2;?>&ordre=<?php echo $ordre;if (isset($_GET['archivID'])) {?>
&archivID=<?php echo GetSQLValueString($_GET['archivID'], 'int');

};
if(isset($_GET['regroupement'])) {echo '&regroupement';};

?>
" target="_blank"> <img src="images/pdf2.jpg" alt="pdf" border="0"></a>&nbsp;
<input name="submit" type="submit" value="Actualiser"/>
</div>
</form></td>
<td valign="bottom" class="no_imprime"><form name="form_reset" method="post" action="lire.php?classe_ID=<?php echo intval($_GET['classe_ID']);?>&matiere_ID=<?php echo intval($_GET['matiere_ID']).$var_consult.$var_regroupement;if (isset($_GET['prof_ID'])){echo '&prof_ID='.$sql_prof_ID;};if (isset($_GET['afficher_titulaire'])){echo '&afficher_titulaire';};?>">
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
				
				$fusion=0;
				if   ($row_Rslisteactivite['gic_ID']>0){
					// rechercher si on doit fusionner
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$query_RsEdt =sprintf("SELECT fusion_gic FROM cdt_emploi_du_temps WHERE 
					cdt_emploi_du_temps.gic_ID=%u AND
					cdt_emploi_du_temps.matiere_ID= %u AND
					cdt_emploi_du_temps.prof_ID=%u
					"
					,$row_Rslisteactivite['gic_ID'],$row_Rslisteactivite['matiere_ID'],$row_Rslisteactivite['prof_ID']);
					$RsEdt = mysqli_query($conn_cahier_de_texte, $query_RsEdt) or die(mysqli_error($conn_cahier_de_texte));
					$row_RsEdt = mysqli_fetch_assoc($RsEdt);
					if ((isset($row_RsEdt['fusion_gic']))&&($row_RsEdt['fusion_gic']=='O')){$fusion=1;};
					mysqli_free_result($RsEdt);
				} 
				
				if   (
				(isset($_GET['regroupement'])) ||
				((!isset($_GET['regroupement'])) &&($fusion==1)&&($row_Rslisteactivite['gic_ID']>0))||
				($row_Rslisteactivite['gic_ID']==0)
				
				){
				
                        //A la date du jour, on teste aussi si l'heure de debut de cours est echue;
                        $visu='Oui';
                        if ((substr($row_Rslisteactivite['code_date'],0,8)==date('Ymd'))){
                                $heure_actuelle=date('Hi',time());
                                $heure_seance=substr($row_Rslisteactivite['heure_debut'],0,2).substr($row_Rslisteactivite['heure_debut'],3,2) ;
                                if($heure_seance>$heure_actuelle){$visu='Non';}; 
                        };
			
			if ($visu=='Oui'){
				$query_Rs_Travail2 = sprintf("SELECT * FROM cdt_travail$arcID WHERE cdt_travail$arcID.code_date='%u' AND cdt_travail$arcID.matiere_ID='%u' AND cdt_travail$arcID.classe_ID='%u' AND cdt_travail$arcID.agenda_ID='%u'  ORDER BY cdt_travail$arcID.code_date", $row_Rslisteactivite['code_date'],$_GET['matiere_ID'],$_GET['classe_ID'],$row_Rslisteactivite['ID_agenda']);
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
					if (isset($row_Rs_Travail2['eval'])){$eval[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['eval'];};
				} while ($row_Rs_Travail2 = mysqli_fetch_assoc($Rs_Travail2));
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
				<?php echo $row_Rslisteactivite['jour_pointe'];?>
				<?php
				if ($row_Rslisteactivite['semaine']<>'A et B'){ 
					echo '(';
					if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){
						if ($row_Rslisteactivite['semaine']=='A'){echo 'P';} else {echo 'I';};
					} 
					else {
						echo $row_Rslisteactivite['semaine'];
                                        };
                                echo')';}; ?>
                                </div>
                                <!-- Le border_bottom ci-dessous est utilise pour l'impression -->
                                <td colspan="2" class="lire_cellule_4" style="border-bottom: 1px #666666 solid" ><div align="left" class="black_police"><?php echo $row_Rslisteactivite['theme_activ']; ?></div>
                                </td>
                                <?php 
                                if (((isset($_POST['Genre'])) AND($_POST['Genre'] =="2"))      OR (!isset($_POST['Genre'])))  // Choix d'impression 2
					{echo "<td class=\"lire_cellule_4\"><div align=\"left\" >&nbsp;</div>";};
				?>
				</tr>
				<tr >
				<td valign="top" class="lire_cellule_2" ><?php
				if ($row_Rslisteactivite['gic_ID']>0){	  
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$query_Rsgic =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses$arcID WHERE ID_gic = %u",$row_Rslisteactivite['gic_ID']);
					$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
					$row_Rsgic = mysqli_fetch_assoc($Rsgic);
					echo '<div align="left"><strong>(R) '.$row_Rsgic['nom_gic'].'</strong></div><br />';
				};	  ?>
				<div align="left"> <?php echo $row_Rslisteactivite['groupe']; ?> <br />
				<?php echo $row_Rslisteactivite['heure_debut']; ?>- <?php echo $row_Rslisteactivite['heure_fin']; ?>
				<?php if ($row_Rslisteactivite['duree']<>''){echo '- ('.$row_Rslisteactivite['duree'].')';
				}?>
				<br />
                                <?php 
                                if ($row_Rslisteactivite['type_activ']<>'ds_prog'){
								  if (isset($row_Rslisteactivite['couleur_activ'])){
                                        echo '<p style="color:'.$row_Rslisteactivite['couleur_activ'].'"><strong>';} else {
										echo '<p><strong>';};
                                        // Recherche si l'heure est partagee ou non 
                                        if ((isset($row_Rslisteactivite['partage']))&&($row_Rslisteactivite['partage']=='O')) {
                                                echo '<img src="images/partage.gif" title="Cette plage est partag&eacute;e avec au moins un autre enseignant.">&nbsp;';
                                        };
					echo $row_Rslisteactivite['type_activ'].'</strong></p>';
                                if (substr($row_Rslisteactivite['code_date'],8,1)==0) {echo '<p style="color:#FF0000"><b>Heure Sup.</b></p>';};}
                                else {   
								    echo '<p style="color:#FF0000"><b>';
									if(isset($_SESSION['libelle_devoir'])){echo $_SESSION['libelle_devoir'];} else {echo 'DEVOIR';};
									echo '</b></p>';}; 
                                ?>
                                <?php
if ((isset($_SESSION['droits']))&&($_SESSION['droits']==2)){?>
<form name="form_archive_copie" method="post" action="<?php echo $editFormAction;?>">
        <input name="copie_archive" type="image" src="images/ed_copy.gif"   border="0" title = "Copier la fiche" alt="Copier la fiche" onClick="Message()">
        <input type="hidden" name="ID_agenda" value="<?php echo $row_Rslisteactivite['ID_agenda']  ;?>">
        <input type="hidden" name="MM_copie_archive" value="form__copie_archive">
</form>
<?php };?>
                        
                                <?php 
                                
                                // affichage fichiers joints seance
				
				$refagenda_RsFichiers = "0";
				if (isset($row_Rslisteactivite['ID_agenda'])) {
					$refagenda_RsFichiers = (get_magic_quotes_gpc()) ? 
					$row_Rslisteactivite['ID_agenda'] : addslashes($row_Rslisteactivite['ID_agenda']);
				}
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsFichiers = sprintf("SELECT * FROM cdt_fichiers_joints$arcID WHERE agenda_ID=%u AND type<>'Travail'", $refagenda_RsFichiers);
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
				if ((isset($_POST['Genre'])) AND ($_POST['Genre']=="3")){              // Choix d'impression 3
					
					for ($taf=1;$taf<4;$taf++) {
						if ($date_a_faire[$taf]<>''){
							echo '<u>'.$t_groupe[$taf].' pour le <b>'.jour_semaine($date_a_faire[$taf]).' '.$date_a_faire[$taf].'</b> :</u>';
							
							//affichage fichiers travail joints
							mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
							$sql_f="SELECT * FROM cdt_fichiers_joints$arcID WHERE agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND type ='Travail' AND t_code_date='".$date_a_faire[$taf]."' AND ind_position=$taf ORDER BY nom_fichier";
							$query_Rs_fichiers_joints_form = $sql_f;
							$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
							$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
							$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
							
							if ($totalRows_Rs_fichiers_joints_form<>0)
							{
								if ($totalRows_Rs_fichiers_joints_form==1)
									{echo ' avec le document ';}
								else
								{echo ' avec les documents ';};
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
							if (!(strcmp($eval[$taf],'O'))){echo "<span style='color:red;'><strong>Evaluation : </strong></span>";};
							echo $travail[$taf].'<br />';
						};
					};		
				};
				?>
				</span> </div>

				</td>
				<td <?php 
				if (((isset($_POST['Genre'])) AND ($_POST['Genre']=="2")) OR (!isset($_POST['Genre'])))  // Choix d'impression 2
					{echo "width=\"45%\"";};
				?> class="Style10"><div align="left">
				<?php $date_jour='20'.date("ymd").'1';?>
				<span>
                                <?php  
                                echo $row_Rslisteactivite['activite'].'<div class="no_imprime" ><br /></div>'; 
                                if (isset($_SESSION['nom_prof'])){
                                        if (($row_Rslisteactivite['rq']<>'')&&(isset($_POST['annot']))&&($_SESSION['droits']==2)){echo 'Rq : '.$row_Rslisteactivite['rq'].'<div class="no_imprime" ><br /></div>';}
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
				
				
				if (((isset($_POST['Genre'])) AND ($_POST['Genre']=="2")) OR (!isset($_POST['Genre'])))                 // Choix d'impression 2
				{echo "</td>
				<td width=\"35%\" class=\"Style10\"><div align=\"left\">";};
				?>
				<span class="Style699">
				<?php 
				if ((isset($_POST['Genre']) AND !($_POST['Genre']=="3"))OR (!isset($_POST['Genre'])))
				{                    
					// Choix d'impression 1 ou 2
					
					
					for ($taf=1;$taf<4;$taf++) {
						if ($date_a_faire[$taf]<>''){
							echo '<u>'.$t_groupe[$taf].' pour le <b>'.jour_semaine($date_a_faire[$taf]).' '.$date_a_faire[$taf].'</b> :</u>';
							
							//affichage fichiers travail joints
							mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
							$sql_f="SELECT * FROM cdt_fichiers_joints$arcID WHERE agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND type ='Travail' AND t_code_date='".$date_a_faire[$taf]."' AND ind_position=$taf ORDER BY nom_fichier";
							$query_Rs_fichiers_joints_form = $sql_f;
							$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
							$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
							$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
							
							if ($totalRows_Rs_fichiers_joints_form<>0)
							{
								if ($totalRows_Rs_fichiers_joints_form==1)
									{echo ' avec le document ';}
								else
								{echo ' avec les documents ';};
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
							if (!(strcmp($eval[$taf],'O'))){echo "<span style='color:red;'><strong>Evaluation : </strong></span>";};
							echo $travail[$taf].'<br />';
						};
					};		
				};
				?>
				</span>
				<?php				//fin affichage fichiers joints travail             ?>
				</div></td>
				</tr>
				</thead>
				</table>
				<br/>
				<?php  
			}
                }
				}
                else {
                        //remplacement
		    if ($row_Rslisteactivite['theme_activ']=='Remplacement'){ ?>
				
				<table width="90%"  border="0" cellspacing="0" class="Style4" align="center">
				<tr>
				<td class="vacances" ><?php echo $row_Rslisteactivite['activite']; ?></td>
				</tr>
				</table>
				<br />
				<?php
			}
			else
			{
                        // les vacances s'affichent seulement a partir de J-7
                        // {  
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
			}
		};
	} while ($row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite)); 
	mysqli_free_result($Rslisteactivite);
}

// affichage du visa
if ($totalRows_RsPublier2>0) {
	$date_actu=substr($row_RsPublier2['date_maj'],8,2).'/'.substr($row_RsPublier2['date_maj'],5,2).'/'.substr($row_RsPublier2['date_maj'],0,4);
	if (substr($row_RsPublier2['date_maj'],8,2)<>'00')  {echo "<div id=\"visa\" class=\"no_imprime\" ><p><img src=\"images/visa.gif\" ></p>
	<p>".jour_semaine($date_actu).' '.$date_actu." </p></div> ";};
};?>
<div align="right" class="no_imprime">
<div align="center"><a href="<?php 
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==2)){echo'enseignant/enseignant.php';}
else 
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==4))
        {echo "direction/cdt_enseignant.php?ID_consult=".$row_RsNomprof['prof_ID']."&ens_consult=".$identite_prof;}
else
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==5))
	        {       
			echo "invite/invite.php";
			if (isset($_GET['ID_prof'])){echo '?ID_prof='.$_GET['ID_prof'];};

		}
else
{echo 'consulter.php?classe_ID='.intval($_GET['classe_ID']).'&tri=date';};?>"><br />
<?php if (!isset($_SESSION['nom_prof'])){echo 'Retour au travail &agrave; faire';}; ?>
&nbsp;&nbsp;<img src="images/home-menu.gif" width="26" height="20" border="0"></a><br>
</div>
</div>
</body>
</html>
<?php
mysqli_free_result($RsNomprof);
if (isset($Rs_Travail2)){mysqli_free_result($Rs_Travail2);};
if (isset($RsPublier2)){mysqli_free_result($RsPublier2);};

?>
