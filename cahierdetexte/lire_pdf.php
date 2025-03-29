<?php 
ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_NOTICE);
session_start();
if (isset($_SESSION['nom_prof'])){$_SESSION['consultation']=$_GET['classe_ID'];};
//on filtre
if ((isset($_GET['classe_ID']))&&($_GET['classe_ID']!=intval($_GET['classe_ID']))){  header("Location: index.php");exit;};
if ((isset($_GET['matiere_ID']))&&($_GET['matiere_ID']!=intval($_GET['matiere_ID']))){  header("Location: index.php");exit;};
if ((isset($_GET['prof_ID']))&&($_GET['prof_ID']!=intval($_GET['prof_ID']))){  header("Location: index.php");exit;};
if ((!isset($_SESSION['consultation'])||($_SESSION['consultation']!=$_GET['classe_ID']))){  header("Location: index.php");exit;};

require_once('./Connections/conn_cahier_de_texte.php');
require_once('./inc/functions_inc.php');
ob_start();

if(function_exists("date_default_timezone_set")){ //fonction PHP 5 

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_time_zone_db = "SELECT param_val FROM cdt_params WHERE param_nom='time_zone'";
$time_zone_db = mysqli_query($conn_cahier_de_texte,$query_time_zone_db ) or die(mysqli_error($conn_cahier_de_texte));
$row_time_zone_db = mysqli_fetch_assoc($time_zone_db);
date_default_timezone_set($row_time_zone_db['param_val']);
};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsPublier2 = "SELECT date_maj FROM cdt_prof WHERE droits=4 ";
$RsPublier2 = mysqli_query($conn_cahier_de_texte,$query_RsPublier2) or die(mysqli_error($conn_cahier_de_texte));
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
$Rsgroupe = mysqli_query( $conn_cahier_de_texte,$query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMinDate = "SELECT * FROM cdt_agenda$arcID WHERE code_date > 0 ORDER BY code_date ASC LIMIT 1";
$RsMinDate = mysqli_query( $conn_cahier_de_texte,$query_RsMinDate) or die(mysqli_error($conn_cahier_de_texte));
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
$RsProf_remplace = mysqli_query($conn_cahier_de_texte,$query_RsProf_remplace) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf_remplace = mysqli_fetch_assoc($RsProf_remplace);
$totalRows_RsProf_remplace = mysqli_num_rows($RsProf_remplace);

if($totalRows_RsProf_remplace==0){
$sql_prof_ID=intval($_GET['prof_ID']);
} else {
$sql_prof_ID=intval($_GET['prof_ID']). ' OR prof_ID ='.$row_RsProf_remplace['titulaire_ID']. ' ';
};
} 
else {$sql_prof_ID=$_SESSION['ID_prof'];};


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

$date1=$_GET['date1'];
$date2=$_GET['date2'];

//en mode eleve, interdire l'affichage du cahier posterieur a la date du jour
if (!isset($_SESSION['nom_prof'])&&($date2>$today)){$date2=$today;};


$date1_form=substr($date1,6,2).'/'.substr($date1,4,2).'/'.substr($date1,0,4);
$date2_form=substr($date2,6,2).'/'.substr($date2,4,2).'/'.substr($date2,0,4);

if (isset($_GET['chrono']))
	{$ordre='ASC'; } else {$ordre='DESC';};
if (isset($_GET['ordre'])){ 
	$ordre=strtr(GetSQLValueString($_GET['ordre'],"text"),$protect);
	} ;

if (isset($_GET['groupe'])){
	if ($_GET['groupe']=='Classe entière'){$sql_groupe='';}
	else { $sql_groupe="AND (cdt_agenda$arcID.groupe='Classe entière' OR cdt_agenda$arcID.groupe=".GetSQLValueString($_GET['groupe'], 'text').")";};
}
else {$sql_groupe='';};

$sql_partage='';
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$requete = "SHOW TABLES LIKE 'cdt_emploi_du_temps_partage$arcID'";
$exec = mysqli_query($conn_cahier_de_texte,$requete);
$compteur_table = mysqli_num_rows($exec);

if ($compteur_table>0) {   // Test d'existence de la table cdt_emploi_du_temps_partage$arcID (posterieure a la creation des archives)
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rs_partage = sprintf("SELECT ID_emploi FROM cdt_emploi_du_temps_partage$arcID WHERE profpartage_ID=%u",GetSQLValueString($sql_prof_ID,"int"));
	$Rs_partage = mysqli_query($conn_cahier_de_texte,$query_Rs_partage) or die(mysqli_error($conn_cahier_de_texte));

	
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
$regroupement='';}else{$regroupement= " AND cdt_agenda.gic_ID ='".$gic_ID_Rslisteactivite."' ";};


$query_Rslisteactivite = sprintf("
	SELECT *
	FROM cdt_agenda$arcID
	LEFT JOIN cdt_prof ON cdt_agenda$arcID.prof_ID = cdt_prof.ID_prof
	WHERE (
	cdt_agenda$arcID.classe_ID = %s
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
	ORDER BY cdt_agenda$arcID.code_date %s",$classe_Rslisteactivite,$matiere_Rslisteactivite,$regroupement,$date1,$date2,$sql_prof,$sql_groupe,$sql_publier,$date1,$date2,$sql_partage,$sql_prof_ID,$ordre);	
//echo $query_Rslisteactivite;
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Rslisteactivite = mysqli_query($conn_cahier_de_texte,$query_Rslisteactivite) or die(mysqli_error($conn_cahier_de_texte));

// Recherche des dates extremes d'une archive
if ((isset($_GET['archivID']))&&(!isset($_GET['date2']))) {
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
$RsMat = mysqli_query($conn_cahier_de_texte,$query_RsMat) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMat = mysqli_fetch_assoc($RsMat);
$totalRows_RsMat = mysqli_num_rows($RsMat);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = sprintf("SELECT * FROM cdt_classe$arcID WHERE ID_classe=%u ",intval($_GET['classe_ID']));
$RsClasse = mysqli_query($conn_cahier_de_texte,$query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
//eleve
if ((!isset($_SESSION['ID_prof']))&&(isset($_GET['prof_ID']))){
	$query_RsNomprof = sprintf("SELECT cdt_prof.identite,cdt_prof.email,cdt_prof.email_diffus_restreint FROM cdt_prof WHERE ID_prof=%u",intval($_GET['prof_ID']));
}
else {
	//autre
	$query_RsNomprof = sprintf("SELECT cdt_emploi_du_temps$arcID.prof_ID,cdt_emploi_du_temps$arcID.matiere_ID,cdt_emploi_du_temps$arcID.classe_ID,cdt_prof.identite,cdt_prof.email,cdt_prof.email_diffus_restreint FROM cdt_emploi_du_temps$arcID,cdt_prof WHERE cdt_prof.ID_prof=cdt_emploi_du_temps$arcID.prof_ID
		AND cdt_emploi_du_temps$arcID.matiere_ID=%u AND cdt_emploi_du_temps$arcID.classe_ID=%u LIMIT 1",intval($_GET['matiere_ID']),intval($_GET['classe_ID']));
};
$RsNomprof = mysqli_query($conn_cahier_de_texte,$query_RsNomprof) or die(mysqli_error($conn_cahier_de_texte));
$row_RsNomprof = mysqli_fetch_assoc($RsNomprof);

/*
if(isset($_GET['regroupement'])){echo '(R) '.$_GET['regroupement'];}
else {	echo $row_RsClasse['nom_classe'];	};	?>
&nbsp;&nbsp;
<?php if (isset($_POST['groupe'])){echo $_POST['groupe'];} else {echo 'Classe entière';};?>
&nbsp;&nbsp;<?php echo $row_RsMat['nom_matiere'].' &nbsp;';
if( isset($_GET['archivID'])){ 
	// Nom archive
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv = sprintf("SELECT * FROM cdt_archive WHERE NumArchive=%u",$_GET['archivID']);
	$RsArchiv = mysqli_query($conn_cahier_de_texte,$query_RsArchiv) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsArchiv = mysqli_fetch_assoc($RsArchiv);
        echo '['.$row_RsArchiv['NomArchive'].']&nbsp;'; 
};
*/
?>

<link href="templates/default/perso.css" rel="stylesheet" type="text/css"> 
<style type="text/css">
<!--
td
{
	padding: 5px;
	border: 1px solid #CCCCCC;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 8px;
	vertical-align: top;
}
-->
</style>

<?php
$titre=$row_RsClasse['nom_classe']. '  -  ';
if (isset($_GET['groupe'])){$titre.=$_GET['groupe']. '  -  ';} else {$titre.='Classe entière'. '  -  ';};
$titre.=$row_RsMat['nom_matiere'];

if ((isset($_SESSION['droits']))&&($_SESSION['droits']==4)){$titre.='  -  '.$_GET['ens_consult'];}
else if ((isset($_SESSION['droits']))&&($_SESSION['droits']==2)) {$titre.='  -  '.$_SESSION['identite'];}
//eleve
else if ((!isset($_SESSION['identite']))&&(isset($_GET['ens_consult']))) {$titre.='  -  '.$_GET['ens_consult'];};

$titre.='  -  P&eacute;riode du '.$date1_form.' au '.$date2_form;
?>
<table  style="width: 100%;padding: 5px" cellspacing="0" cellpadding="0">

<tr>
<td style="width: 100%;text-align: center"><?php echo str_replace("\'","",$titre);?></td>
</tr>
</table>
<?php
if ($totalRows_Rslisteactivite  >0){
	
	do { 
		
		$c1l1='';$c2l1='';$c3l1='';
		$c1l2='';$c2l2='';$c3l2='';
		
		if($row_Rslisteactivite['classe_ID']<>0){ 
		
		 		if   ($row_Rslisteactivite['gic_ID']>0){
					// rechercher si on doit fusionner
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$query_RsEdt =sprintf("SELECT fusion_gic FROM cdt_emploi_du_temps WHERE 
					cdt_emploi_du_temps.gic_ID=%u AND
					cdt_emploi_du_temps.matiere_ID= %u AND
					cdt_emploi_du_temps.prof_ID=%u
					"
					,$row_Rslisteactivite['gic_ID'],$row_Rslisteactivite['matiere_ID'],$row_Rslisteactivite['prof_ID']);
					$RsEdt = mysqli_query($conn_cahier_de_texte,$query_RsEdt) or die(mysqli_error($conn_cahier_de_texte));
					$row_RsEdt = mysqli_fetch_assoc($RsEdt);

				}
				
				if  ((isset($_GET['regroupement'])) ||
				((!isset($_GET['regroupement'])) &&(isset($row_RsEdt['fusion_gic']))&&($row_RsEdt['fusion_gic']=='O')&&($row_Rslisteactivite['gic_ID']>0))||
				($row_Rslisteactivite['gic_ID']==0)){
				
				
                        //A la date du jour, on teste aussi si l'heure de debut de cours est echue;
                        $visu='Oui';
                        if ((substr($row_Rslisteactivite['code_date'],0,8)==date('Ymd'))){
                                $heure_actuelle=date('Hi',time());
                                $heure_seance=substr($row_Rslisteactivite['heure_debut'],0,2).substr($row_Rslisteactivite['heure_debut'],3,2) ;
                                if($heure_seance>$heure_actuelle){$visu='Non';}; 
                        };
			
			if ($visu=='Oui'){	 
				
				
				$query_Rs_Travail2 = sprintf("SELECT * FROM cdt_travail$arcID WHERE cdt_travail$arcID.code_date='%s' AND cdt_travail$arcID.matiere_ID=%u AND cdt_travail$arcID.classe_ID=%u AND cdt_travail$arcID.agenda_ID=%u  ORDER BY cdt_travail$arcID.code_date", $row_Rslisteactivite['code_date'],$_GET['matiere_ID'],$_GET['classe_ID'],$row_Rslisteactivite['ID_agenda']);
				
				$Rs_Travail2 = mysqli_query( $conn_cahier_de_texte,$query_Rs_Travail2) or die(mysqli_error($conn_cahier_de_texte));
				$row_Rs_Travail2 = mysqli_fetch_assoc($Rs_Travail2);
				$totalRows_Rs_Travail2 = mysqli_num_rows($Rs_Travail2);
				
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
				
				
				if ($row_Rslisteactivite['semaine']<>'A et B'){ 
					$c1l2.='(';
					if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){
						if ($row_Rslisteactivite['semaine']=='A'){$c1l2.='P';} else {$c1l2.= 'I';};
					} 
					else {
						$c1l2.= $row_Rslisteactivite['semaine'];
					};
				$c1l2.=') ';};
				
				
				$c1l2.= $row_Rslisteactivite['groupe']."<br>";
				$c1l2.=$row_Rslisteactivite['heure_debut'];
				if ($row_Rslisteactivite['heure_fin']<>''){$c1l2.='-'.$row_Rslisteactivite['heure_fin'];}
				$c1l2.="<br>";
				if ($row_Rslisteactivite['duree']<>''){$c1l2.= '('.$row_Rslisteactivite['duree'].')';$c1l2.="<br>";};
				
				
				if ($row_Rslisteactivite['type_activ']<>'ds_prog'){$c1l2.= $row_Rslisteactivite['type_activ'];
					if (substr($row_Rslisteactivite['code_date'],8,1)==0) {$c1l2.='Heure Sup.';};
				}
				else {   $c1l2.=$_SESSION['libelle_devoir'];}; 
				$c1l2.="<br>";
				
				
				
				$c2l2 =$row_Rslisteactivite['activite'];
				
				
				if (isset($_SESSION['nom_prof'])){  if (($row_Rslisteactivite['rq']<>'')&&(isset($_GET['annot']))){  $c2l2.="<br> Rq : ".$row_Rslisteactivite['rq']	  ;}};
				// affichage fichiers joints seance
				
				$refagenda_RsFichiers = "0";
				if (isset($row_Rslisteactivite['ID_agenda'])) {
					$refagenda_RsFichiers = (get_magic_quotes_gpc()) ? 
					$row_Rslisteactivite['ID_agenda'] : addslashes($row_Rslisteactivite['ID_agenda']);
				}
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsFichiers = sprintf("SELECT * FROM cdt_fichiers_joints$arcID WHERE cdt_fichiers_joints$arcID.agenda_ID=%u  AND cdt_fichiers_joints$arcID.type<>'Travail'", $refagenda_RsFichiers);
				$RsFichiers = mysqli_query($conn_cahier_de_texte,$query_RsFichiers) or die(mysqli_error($conn_cahier_de_texte));
				$row_RsFichiers = mysqli_fetch_assoc($RsFichiers);
				$totalRows_RsFichiers = mysqli_num_rows($RsFichiers);
				if ($totalRows_RsFichiers<>0){$c1l2.= "<br>Document(s) Cours <br>";};
				do { 
					
					$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_RsFichiers['nom_fichier']); 
					
					$c1l2.="<a href=\"fichiers_joints/".$row_RsFichiers['nom_fichier']."\">".$nom_f."</a><br>";      
				} while ($row_RsFichiers = mysqli_fetch_assoc($RsFichiers)); 
				mysqli_free_result($RsFichiers);
				
				//fin affichage fichiers joints seance
				
				
				
				if ( $date_a_faire[1]<>''){
					$c3l2.= $t_groupe[1].' pour le '.jour_semaine($date_a_faire[1]).' '.$date_a_faire[1]." <br>";
					
					
					
					//affichage fichiers travail joints
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$sql_f="SELECT * FROM cdt_fichiers_joints$arcID WHERE cdt_fichiers_joints$arcID.agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND cdt_fichiers_joints$arcID.type ='Travail' AND cdt_fichiers_joints$arcID.t_code_date ='".$date_a_faire[1]."' AND cdt_fichiers_joints$arcID.ind_position = 1 ORDER BY cdt_fichiers_joints$arcID.nom_fichier";
					$query_Rs_fichiers_joints_form = $sql_f;
					$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte,$query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
					$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
					$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
					
					if ($totalRows_Rs_fichiers_joints_form<>0)
					{
						if ($totalRows_Rs_fichiers_joints_form==1)
							{$c3l2.= ' avec le document ';}
						else
						{$c3l2.=' avec les documents ';};
						do { 
							
							$exp = "/^[0-9]+_/";
							$nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
							$c3l2.= "<a href=\"fichiers_joints/".$row_Rs_fichiers_joints_form['nom_fichier']."\">".$nom_f."</a><br>";              
						} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
					}
					mysqli_free_result($Rs_fichiers_joints_form);
					//fin affichage des fichiers travail joints
					if (!(strcmp($eval[1],'O'))){$c3l2 .="<span style='color:red;'><strong>Evaluation : </strong></span>";};
					$c3l2 .=$travail[1];
					
				}
				
				
				if ( $date_a_faire[2]<>''){
					$c3l2.= "<br> <br>".$t_groupe[2].' pour le '.jour_semaine($date_a_faire[2]).' '.$date_a_faire[2]."<br>";
					
					//affichage fichiers travail joints
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$sql_f="SELECT * FROM cdt_fichiers_joints$arcID WHERE cdt_fichiers_joints$arcID.agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND cdt_fichiers_joints$arcID.type ='Travail' AND cdt_fichiers_joints$arcID.t_code_date ='".$date_a_faire[2]."' AND cdt_fichiers_joints$arcID.ind_position = 2 ORDER BY cdt_fichiers_joints$arcID.nom_fichier";
					$query_Rs_fichiers_joints_form = $sql_f;
					
					$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte,$query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));

					$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
					$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
					
					if ($totalRows_Rs_fichiers_joints_form<>0)
					{
						if ($totalRows_Rs_fichiers_joints_form==1)
							{$c3l2.=' avec le document ';}
						else
						{$c3l2.= ' avec les documents ';};
						do {
							$exp = "/^[0-9]+_/";
							$nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
							$c3l2.= "<a href=\"fichiers_joints/".$row_Rs_fichiers_joints_form['nom_fichier']."\">".$nom_f."</a><br>";                
						} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
					}
					mysqli_free_result($Rs_fichiers_joints_form);
					//fin affichage des fichiers travail joints
					if (!(strcmp($eval[2],'O'))){$c3l2 .="<span style='color:red;'><strong>Evaluation : </strong></span>";};
					$c3l2 .=$travail[2];
				}
				
				if ( $date_a_faire[3]<>''){
					$c3l2.=  "<br> <br>".$t_groupe[3].' pour le '.jour_semaine($date_a_faire[3]).' '.$date_a_faire[3]."<br>";
					//affichage fichiers travail joints
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$sql_f="SELECT * FROM cdt_fichiers_joints$arcID WHERE cdt_fichiers_joints$arcID.agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND cdt_fichiers_joints$arcID.type ='Travail' AND cdt_fichiers_joints$arcID.t_code_date ='".$date_a_faire[3]."' AND cdt_fichiers_joints$arcID.ind_position = 3  ORDER BY cdt_fichiers_joints$arcID.nom_fichier";
					$query_Rs_fichiers_joints_form = $sql_f;
					
					$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte,$query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
					$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
					$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
					
					if ($totalRows_Rs_fichiers_joints_form<>0)
					{
						if ($totalRows_Rs_fichiers_joints_form==1)
							{$c3l2.= ' avec le document ';}
						else
						{$c3l2.=' avec les documents ';};
						do {
							
							$exp = "/^[0-9]+_/";
							$nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
							$c3l2.= "<a href=\"fichiers_joints/".$row_Rs_fichiers_joints_form['nom_fichier']."\">".$nom_f."</a><br>";          
							
						} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
					}
					mysqli_free_result($Rs_fichiers_joints_form);
					//fin affichage des fichiers travail joints
					if (!(strcmp($eval[3],'O'))){$c3l2 .="<span style='color:red;'><strong>Evaluation : </strong></span>";};
					$c3l2 .=$travail[3];
				}
				
				$c1l1=substr($row_Rslisteactivite['jour_pointe'],0,strlen($row_Rslisteactivite['jour_pointe'])-4);
				
				if (isset($row_Rslisteactivite['theme_activ'])){$c2l1.= $row_Rslisteactivite['theme_activ'] ;}
				if ((isset($totalRows_Rs_Travail2))&&($totalRows_Rs_Travail2>0)){$c3l1.='A faire';}
				
				
				?>
				
				<!--<nobreak> -->
				<table  style="width: 100%;border-collapse: collapse;padding: 5px;" >
				<tr>
				<td style="text-align: left;	width: 15%"><?php echo $c1l1;?></td>
				<td style="text-align: left;	width: 55%"><?php echo $c2l1;?></td>
				<td style="text-align: left;	width: 30%"><?php echo $c3l1;?></td>
				</tr>
				<tr>
				<td style="text-align: left;	width: 15%"><?php echo $c1l2;?></td>
				<td style="text-align: left;	width: 55%"><?php echo $c2l2;?></td>
				<td style="text-align: left;	width: 30%"><?php echo $c3l2;?></td>
				</tr>
				</table>
				<!--</nobreak> -->
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
}

//$content = utf8_encode(ob_get_clean()); 
$content = ob_get_clean();
require_once('html2pdf/html2pdf.class.php');

$html2pdf = new HTML2PDF('P','A4', 'fr');
$html2pdf->setDefaultFont('Helvetica');
$html2pdf->WriteHTML($content);

if (preg_match("/MSIE/i", $_SERVER["HTTP_USER_AGENT"])){ 
header("Content-type: application/PDF"); 
} else { 
header("Content-type: application/PDF"); 
header("Content-Type: application/pdf"); 
} 
header('Cache-Control: private, max-age=0, must-revalidate');

$html2pdf->Output('cdt.pdf');




mysqli_free_result($RsNomprof);

if (isset($Rs_Travail2)){mysqli_free_result($Rs_Travail2);};
if (isset($RsPublier)){mysqli_free_result($RsPublier);};
if (isset($RsPublier2)){mysqli_free_result($RsPublier2);};
if (isset($Rsgroupe)){mysqli_free_result($Rsgroupe);};
mysqli_free_result($Rslisteactivite);
mysqli_free_result($RsMat);
mysqli_free_result($RsClasse);
?>
