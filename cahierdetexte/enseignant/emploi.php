<?php 
include "../authentification/authcheck.php";

// autoriser Administrateur, Vie scolaire et Suppleant 
if ($_SESSION['droits']<>1 && $_SESSION['droits']<>2 && $_SESSION['droits']<>3) { header("Location: ../index.php");exit;};

require_once('../Connections/conn_cahier_de_texte.php'); 
//gestion de l'aller retour remplace_remplacant - EDT emploi du remplacant - Index de administrateur
if ($_SESSION['droits']==1 || $_SESSION['droits']==3 )
{
if (isset($_GET['ID_prof']))
{$_SESSION['ID_prof']=$_GET['ID_prof'];}
$refprof_RsListe = "0";
if (isset($_SESSION['ID_prof'])) {
  $refprof_RsListe = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
 $query_RsProf = sprintf("SELECT * FROM cdt_prof WHERE cdt_prof.ID_prof=%u", $refprof_RsListe);
$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);
$_SESSION['identite']=$row_RsProf['identite'];

};

require_once('../inc/functions_inc.php');

$editFormAction = '';
$err=0;

if (isset($_POST['import'])) {
	$numimport=true;
	if ((isset($_POST['edt_exist_debut2']))&&(isset($_POST['edt_exist_fin2']))) {
		$timestamp_debut=mktime(0,0,0,substr($_POST['edt_exist_debut2'],3,2),substr($_POST['edt_exist_debut2'],0,2),substr($_POST['edt_exist_debut2'],6,4));//H,Mn,Sec,mois,jour,annee
		$timestamp_fin=mktime(0,0,0,substr($_POST['edt_exist_fin2'],3,2),substr($_POST['edt_exist_fin2'],0,2),substr($_POST['edt_exist_fin2'],6,4));//H,Mn,Sec,mois,jour,annee
		if ($timestamp_debut>$timestamp_fin) {
			$message_erreur_date="La date de d&eacute;but doit &ecirc;tre avant la date de fin.<br />";
			$err=10;
		}
	}
	if ($err==0){
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rs_empedt = sprintf("SELECT * FROM cdt_edt WHERE prof_ref=%s ORDER BY jour_semaine, heure, semaine", GetSQLValueString($_POST['import'],"text"));
		$Rs_empedt = mysqli_query($conn_cahier_de_texte, $query_Rs_empedt) or die(mysqli_error($conn_cahier_de_texte));
		while ($row_Rs_empedt = mysqli_fetch_assoc($Rs_empedt)) {
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_Rs_existedeja = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE prof_ID=%u 
				AND jour_semaine=%s
				AND semaine=%s
				AND heure=%s
				AND classe_ID=%u
				AND matiere_ID=%u",
				GetSQLValueString($_SESSION['ID_prof'], "int"),
				GetSQLValueString($row_Rs_empedt['jour_semaine'], "text"),
				GetSQLValueString($row_Rs_empedt['semaine'], "text"),
				GetSQLValueString($row_Rs_empedt['heure'], "int"),
				GetSQLValueString($row_Rs_empedt['classe_ID'], "int"),
				GetSQLValueString($row_Rs_empedt['matiere_ID'], "int"));
			$Rs_existedeja = mysqli_query($conn_cahier_de_texte, $query_Rs_existedeja) or die(mysqli_error($conn_cahier_de_texte));
			//Cette requete recherche s'il existe deja une plage horaire dans l'edt en cours identique a l'edt a importer
			$totalRows_Rs_existedeja = mysqli_num_rows($Rs_existedeja);
			
			if ($totalRows_Rs_existedeja>0) {
				$inserer=true;
				while ($row_Rs_existedeja = mysqli_fetch_assoc($Rs_existedeja)) {
					$timestamp_deja=mktime(0,0,0,substr($row_Rs_existedeja['edt_exist_fin'],5,2),substr($row_Rs_existedeja['edt_exist_fin'],8,2),substr($row_Rs_existedeja['edt_exist_fin'],0,4));//H,Mn,Sec,mois,jour,annee
					$timestamp_debut=mktime(0,0,0,substr($_POST['edt_exist_debut2'],3,2),substr($_POST['edt_exist_debut2'],0,2),substr($_POST['edt_exist_debut2'],6,4));//H,Mn,Sec,mois,jour,annee
					$timestamp_fin=mktime(0,0,0,substr($_POST['edt_exist_fin2'],3,2),substr($_POST['edt_exist_fin2'],0,2),substr($_POST['edt_exist_fin2'],6,4));//H,Mn,Sec,mois,jour,annee
					if ($timestamp_deja+2*3600*24>=$timestamp_debut) {
						//si date de fin de l'ancien edt est inferieure a la date de debut du nouveau a deux jours pres, on importe telle quelle cette plage sinon on regarde les dates de fin pour faire une eventuelle modif					
						if ($numimport) {
							$updateSQL = sprintf("UPDATE cdt_prof SET Num_Import=Num_Import+1 WHERE ID_prof=%u",GetSQLValueString($_SESSION['ID_prof'],"int"));
							mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
							$Result = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
							$numimport=false;
							mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
							$query_Rs_Num_Import = sprintf("SELECT Num_Import FROM cdt_prof WHERE ID_prof=%u",GetSQLValueString($_SESSION['ID_prof'], "int"));
							$Rs_Num_Import = mysqli_query($conn_cahier_de_texte, $query_Rs_Num_Import) or die(mysqli_error($conn_cahier_de_texte));
							$row_Rs_Num_Import = mysqli_fetch_assoc($Rs_Num_Import);
							$Num_Import = $row_Rs_Num_Import['Num_Import'];
							mysqli_free_result($Rs_Num_Import);
						}
						if ($timestamp_fin>$timestamp_deja) {
							//si date de fin de l'ancien edt est inferieure a la date fin du nouveau, mettre a jour l'heure de fin de cette plage horaire et son num d'import
							
							
							$date2=substr($_POST['edt_exist_fin2'],6,4).'-'.substr($_POST['edt_exist_fin2'],3,2).'-'.substr($_POST['edt_exist_fin2'],0,2);
							$updateSQL = sprintf("UPDATE cdt_emploi_du_temps SET edt_exist_fin=%s,ID_Import=%s WHERE ID_emploi=%s",
								GetSQLValueString($date2, "text"),
								$Num_Import,
								GetSQLValueString($row_Rs_existedeja['ID_emploi'], "int"));
							mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
							$Result = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
						} else { //Ne mettre a jour que son num d'import
							$updateSQL = sprintf("UPDATE cdt_emploi_du_temps SET ID_Import=%s WHERE ID_emploi=%s",
								$Num_Import,
								GetSQLValueString($row_Rs_existedeja['ID_emploi'], "int"));
							mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
							$Result = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
						}
						$inserer=false;
						//pas de break parce qu'il faut traiter tous les cas et changer le num d'import le cas echeant
					}
				}
			}
			mysqli_free_result($Rs_existedeja);
			
			if (($totalRows_Rs_existedeja==0)||($inserer)) {
				
				if ($numimport) {
					$updateSQL = sprintf("UPDATE cdt_prof SET Num_Import=Num_Import+1 WHERE ID_prof=%u",GetSQLValueString($_POST['import'],"int"));
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$Result = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
					$numimport=false;
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$query_Rs_Num_Import = sprintf("SELECT Num_Import,nom_prof FROM cdt_prof WHERE ID_prof=%u LIMIT 1",GetSQLValueString($_SESSION['ID_prof'], "int"));
					$Rs_Num_Import = mysqli_query($conn_cahier_de_texte, $query_Rs_Num_Import) or die(mysqli_error($conn_cahier_de_texte));
					$row_Rs_Num_Import = mysqli_fetch_assoc($Rs_Num_Import);
					$Num_Import = $row_Rs_Num_Import['Num_Import'];
					mysqli_free_result($Rs_Num_Import);
				}
                                $date1=substr($_POST['edt_exist_debut2'],6,4).'-'.substr($_POST['edt_exist_debut2'],3,2).'-'.substr($_POST['edt_exist_debut2'],0,2); 
                                $date2=substr($_POST['edt_exist_fin2'],6,4).'-'.substr($_POST['edt_exist_fin2'],3,2).'-'.substr($_POST['edt_exist_fin2'],0,2);
                                
                                $insertSQL = sprintf("INSERT INTO cdt_emploi_du_temps (prof_ID, jour_semaine, semaine, heure, classe_ID, matiere_ID, heure_debut, heure_fin, couleur_cellule, ImportEDT,edt_exist_debut,edt_exist_fin,ID_Import,groupe) VALUES (%u, %s, %s, %u, %u, %u, %s, %s, %s, %s, %s, %s, %u, %s)",
                                        GetSQLValueString($_SESSION['ID_prof'], "int"),
                                        GetSQLValueString($row_Rs_empedt['jour_semaine'], "text"),
                                        GetSQLValueString($row_Rs_empedt['semaine'], "text"),
					GetSQLValueString($row_Rs_empedt['heure'], "int"),
					GetSQLValueString($row_Rs_empedt['classe_ID'], "int"),
					GetSQLValueString($row_Rs_empedt['matiere_ID'], "int"),
					GetSQLValueString($row_Rs_empedt['heure_debut'], "text"),	
					GetSQLValueString($row_Rs_empedt['heure_fin'], "text"),
					GetSQLValueString($row_Rs_empedt['couleur_cellule'], "text"),
					GetSQLValueString("OUI", "text"),
					GetSQLValueString($date1, "text"),
					GetSQLValueString($date2, "text"),		
					$Num_Import,
					GetSQLValueString($row_Rs_empedt['groupe'], "text")
					);
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
			}
		}
		mysqli_free_result($Rs_empedt);
		if (isset($_POST['stopedt'])) { //Stopper l'EDT precedent
			//Inserer Num Import et changer la date de fin (y mettre la veille de la date de debut saisie)  si celle-ci est superieure a sa date de debut
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); 
			$query_Rs_Num_Import = sprintf("SELECT Num_Import,nom_prof FROM cdt_prof WHERE ID_prof=%u LIMIT 1",GetSQLValueString($_SESSION['ID_prof'], "int"));
			$Rs_Num_Import = mysqli_query($conn_cahier_de_texte, $query_Rs_Num_Import) or die(mysqli_error($conn_cahier_de_texte));
			$row_Rs_Num_Import = mysqli_fetch_assoc($Rs_Num_Import);
			$Num_Import = $row_Rs_Num_Import['Num_Import'];
			mysqli_free_result($Rs_Num_Import);
			
			$timestamp_debut=mktime(0,0,0,substr($_POST['edt_exist_debut2'],3,2),substr($_POST['edt_exist_debut2'],0,2),substr($_POST['edt_exist_debut2'],6,4));//H,Mn,Sec,mois,jour,annee
			$veille=$timestamp_debut-24*3600;
			$dateveille=strftime("%F",$veille);
			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_Rs_astopper = sprintf("UPDATE cdt_emploi_du_temps SET edt_exist_fin=%s,ID_Import=%s WHERE prof_ID=%u AND ID_Import<%s AND edt_exist_fin>%s AND edt_exist_debut<%s",
				GetSQLValueString($dateveille, "text"),
				GetSQLValueString($Num_Import, "int"),
				GetSQLValueString($_SESSION['ID_prof'], "int"),
				GetSQLValueString($Num_Import, "int"),
				GetSQLValueString($dateveille, "text"),
				GetSQLValueString($dateveille, "text"));
			$Rs_astopper = mysqli_query($conn_cahier_de_texte, $query_Rs_astopper) or die(mysqli_error($conn_cahier_de_texte));
			//Cette requete recherche s'il existe deja une plage horaire dans l'edt en cours identique a l'edt a importer	
		}
	}
}

//Changement de couleur d'une classe ou d'un regroupement
if ((isset($_POST['couleur_cellule2']))&&(isset($_POST['couleur_police2']))) {
	if ((isset($_POST['choixclasse']))&&($_POST['choixclasse']!=='0')&&($_POST['choixclasse']!=='-1')) {
		if (($_POST['couleur_cellule2']!=='')&&($_POST['couleur_police2']!=='')) {
			$updateSQL = sprintf("UPDATE cdt_emploi_du_temps SET couleur_cellule=%s, couleur_police=%s WHERE prof_ID=%u AND classe_ID=%u",
				GetSQLValueString($_POST['couleur_cellule2'], "text"),
				GetSQLValueString($_POST['couleur_police2'], "text"),
				GetSQLValueString($_SESSION['ID_prof'], "int"),
				GetSQLValueString($_POST['choixclasse'], "int"));
		} else if ($_POST['couleur_cellule2']!=='') {
			$updateSQL = sprintf("UPDATE cdt_emploi_du_temps SET couleur_cellule=%s WHERE prof_ID=%u AND classe_ID=%u",
				GetSQLValueString($_POST['couleur_cellule2'], "text"),
				GetSQLValueString($_SESSION['ID_prof'], "int"),
				GetSQLValueString($_POST['choixclasse'], "int"));
		} else {
			$updateSQL = sprintf("UPDATE cdt_emploi_du_temps SET couleur_police=%s WHERE prof_ID=%u AND classe_ID=%u",
				GetSQLValueString($_POST['couleur_police2'], "text"),
				GetSQLValueString($_SESSION['ID_prof'], "int"),
				GetSQLValueString($_POST['choixclasse'], "int"));
		}
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
	} else if ((isset($_POST['choixgic']))&&($_POST['choixgic']!=='0')&&($_POST['choixgic']!=='-1')) {
		if (($_POST['couleur_cellule2']!=='')&&($_POST['couleur_police2']!=='')) {
			$updateSQL = sprintf("UPDATE cdt_emploi_du_temps SET couleur_cellule=%s, couleur_police=%s WHERE prof_ID=%u AND gic_ID=%u",
				GetSQLValueString($_POST['couleur_cellule2'], "text"),
				GetSQLValueString($_POST['couleur_police2'], "text"),
				GetSQLValueString($_SESSION['ID_prof'], "int"),
				GetSQLValueString($_POST['choixgic'], "int"));
		} else if ($_POST['couleur_cellule2']!=='') {
			$updateSQL = sprintf("UPDATE cdt_emploi_du_temps SET couleur_cellule=%s WHERE prof_ID=%u AND gic_ID=%u",
				GetSQLValueString($_POST['couleur_cellule2'], "text"),
                                GetSQLValueString($_SESSION['ID_prof'], "int"),
                                GetSQLValueString($_POST['choixgic'], "int"));
                } else {
                        $updateSQL = sprintf("UPDATE cdt_emploi_du_temps SET couleur_police=%s WHERE prof_ID=%u AND gic_ID=%u",
                                GetSQLValueString($_POST['couleur_police2'], "text"),
                                GetSQLValueString($_SESSION['ID_prof'], "int"),
                                GetSQLValueString($_POST['choixgic'], "int"));
		}
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));	
	}
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$sem_alter='';
	if (isset($_POST['s1'])) {$s1=GetSQLValueString($_POST['s1'], "int");}else{$s1='';};
	if (isset($_POST['s2'])) {$s2=GetSQLValueString($_POST['s2'], "int");}else{$s2='';};
	if (isset($_POST['s3'])) {$s3=GetSQLValueString($_POST['s3'], "int");}else{$s3='';};
	if (isset($_POST['s4'])) {$s4=GetSQLValueString($_POST['s4'], "int");}else{$s4='';};

  

	$sem_alter=$s1.$s2.$s3.$s4;

	if($sem_alter<>''){$choix_semaine=$sem_alter;} else {$choix_semaine=GetSQLValueString($_POST['semaine'], "text");};
        
        if ((isset($_POST['edt_exist_debut']))&&(isset($_POST['edt_exist_fin']))) {
                $timestamp_debut=mktime(0,0,0,substr($_POST['edt_exist_debut'],3,2),substr($_POST['edt_exist_debut'],0,2),substr($_POST['edt_exist_debut'],6,4));//H,Mn,Sec,mois,jour,annee
                $timestamp_fin=mktime(0,0,0,substr($_POST['edt_exist_fin'],3,2),substr($_POST['edt_exist_fin'],0,2),substr($_POST['edt_exist_fin'],6,4));//H,Mn,Sec,mois,jour,annee
		if ($timestamp_debut>$timestamp_fin) {
			$message_erreur_date="La date de d&eacute;but doit &ecirc;tre avant la date de fin.<br />";
			$err=1;
		}
	}
	if ($_POST['heure']=='-1'){$message_erreur_plage='Indice de plage de cours &agrave; s&eacute;lectionner.<br />';$err=1;};
	if ($_POST['matiere_ID']=='-1'){$message_erreur_matiere='Mati&egrave;re &agrave; s&eacute;lectionner.<br />';$err=1;};
	if ($_POST['classe_ID']==-1){$message_erreur_classe='Classe ou regroupement &agrave; s&eacute;lectionner.<br />';$err=1;};
	if ($err==0){
		$gic_val=0;
		if (isset($_POST['gic_ID'])&&($_POST['gic_ID']<>'')){$gic_val=GetSQLValueString($_POST['gic_ID'], "int");}		
		$duree=isset($_POST['duree'])?$_POST['duree']:'';
		if (isset($_POST['edt_exist_debut'])){$date1=substr($_POST['edt_exist_debut'],6,4).'-'.substr($_POST['edt_exist_debut'],3,2).'-'.substr($_POST['edt_exist_debut'],0,2);} 
		if (isset($_POST['edt_exist_fin'])){$date2=substr($_POST['edt_exist_fin'],6,4).'-'.substr($_POST['edt_exist_fin'],3,2).'-'.substr($_POST['edt_exist_fin'],0,2);} 
		
		$heure_debut=$_POST['h1'].'h'.$_POST['mn1'];
		$heure_fin=$_POST['h2'].'h'.$_POST['mn2'];
		
		$insertSQL = sprintf("INSERT INTO cdt_emploi_du_temps (prof_ID, jour_semaine, semaine, heure, classe_ID, gic_ID, groupe, matiere_ID, heure_debut, heure_fin, duree, fusion_gic, couleur_cellule, couleur_police, edt_exist_debut, edt_exist_fin) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,%s)",
			GetSQLValueString($_SESSION['ID_prof'], "int"),
			GetSQLValueString($_POST['jour_semaine'], "text"),
			$choix_semaine,
			GetSQLValueString($_POST['heure'], "int"),
			GetSQLValueString($_POST['classe_ID'], "int"),
			$gic_val,
			GetSQLValueString($_POST['groupe'], "text"),
			GetSQLValueString($_POST['matiere_ID'], "int"),
			GetSQLValueString($heure_debut, "text"),
			GetSQLValueString($heure_fin, "text"),
			GetSQLValueString($duree, "text"),
			GetSQLValueString(isset($_POST['fusion_gic']) ? 'true' : '', 'defined','"O"','"N"'),
			GetSQLValueString($_POST['couleur_cellule'], "text"),
			GetSQLValueString($_POST['couleur_police'], "text"),
			GetSQLValueString($date1, "text"),
			GetSQLValueString($date2, "text")
			);
            //echo $insertSQL;  
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
                $UID=mysqli_insert_id($conn_cahier_de_texte);
                
                if(isset($_POST['partage']) && isset($_POST['partage_ID']) && !empty($_POST['partage_ID'])){
                        $Col1_Array = $_POST['partage_ID'];
                        foreach($Col1_Array as $selectValue){
                                $insertSQL = sprintf("INSERT INTO cdt_emploi_du_temps_partage (ID_emploi, profpartage_ID) VALUES (%u, %u)",$UID,$selectValue);
                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
                        }
                }               
                $err=100;
                $toto=isset($_GET['affiche'])?$_GET['affiche']:'2';
                unset($_POST);
                unset($_GET);
                $_GET['affiche']=$toto;
        };
};

$refprof_Rs_emploi = "0";
if (isset($_SESSION['ID_prof'])) {
        $refprof_Rs_emploi = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
}


//y a t il des plages cloturees ?
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_cloture = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE edt_exist_fin<='%s' AND prof_ID=%u", date('Y-m-d'),$refprof_Rs_emploi);
$Rs_cloture = mysqli_query($conn_cahier_de_texte, $query_Rs_cloture) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_cloture = mysqli_fetch_assoc($Rs_cloture);
$totalRows_Rs_cloture = mysqli_num_rows($Rs_cloture);

$req_cloture='';
if (($totalRows_Rs_cloture>0)&&(isset($_GET['masque_cloture']))&&($_GET['masque_cloture']==1)){ 
$req_cloture=" AND edt_exist_fin >='".date('Y-m-d')."'";
} ;

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_partage = sprintf("SELECT * FROM cdt_emploi_du_temps_partage WHERE profpartage_ID=%u", $refprof_Rs_emploi);
$Rs_partage = mysqli_query($conn_cahier_de_texte, $query_Rs_partage) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_partage = mysqli_fetch_assoc($Rs_partage);

$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_matiere WHERE (cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere AND cdt_emploi_du_temps.prof_ID=%u %s)", $refprof_Rs_emploi,$req_cloture);

do {
    $query_Rs_emploi .= sprintf(" OR ((cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere AND cdt_emploi_du_temps.ID_emploi=%u) %s )", $row_Rs_partage['ID_emploi'],$req_cloture);
} while ($row_Rs_partage = mysqli_fetch_assoc($Rs_partage));

mysqli_free_result($Rs_partage);

$query_Rs_emploi .=" ORDER BY cdt_emploi_du_temps.jour_semaine, cdt_emploi_du_temps.heure, cdt_emploi_du_temps.semaine";
$Rs_emploi = mysqli_query($conn_cahier_de_texte, $query_Rs_emploi) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi);
$totalRows_Rs_emploi = mysqli_num_rows($Rs_emploi);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMatiere = "SELECT * FROM cdt_matiere ORDER BY nom_matiere ASC";
$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
$totalRows_RsMatiere = mysqli_num_rows($RsMatiere);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsplage_all ="SELECT * FROM cdt_plages_horaires ORDER BY ID_plage";
$Rsplage_all = mysqli_query($conn_cahier_de_texte, $query_Rsplage_all) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsplage_all= mysqli_fetch_assoc($Rsplage_all);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE prof_ID = %u",$refprof_Rs_emploi);
$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgic = mysqli_fetch_assoc($Rsgic);
$totalrows_Rsgic=mysqli_num_rows($Rsgic);




?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type="text/css" rel=stylesheet>
<link href="../styles/arrondis.css" rel="stylesheet" type="text/css" />
<link href="../styles/info_bulles.css" rel="stylesheet" type="text/css" />
<link media="screen" rel="stylesheet" href="../styles/colorbox.css" />
<link rel="stylesheet" type="text/css" href="../styles/colorpicker.css" />
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />
<style type="text/css">
.detail {
	font-size: 9px;
	background-color: #FFFFFF;
	text-align:left;
	color: #000066;
	border: 1px solid #CCCCCC;
	border-collapse:collapse;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	vertical-align: top;
}
.curseur_aide { cursor: help; }
.curseur_pointe { cursor: pointer; }
.Style70 {font-size: small;color: #000066;}
</style>
<script type="text/javascript" src="../jscripts/CP_Class.js"></script>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery.colorbox.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>
<script language="javascript" type="text/javascript">
$(document).ready(function() 
	{
	$("#showr").click(function () {
		if ($("#cache").is(":hidden")) {
				  $("#cache").fadeIn();
			  }else {
				  $("#cache").fadeOut();
			  };
		if (!($("#saisirplage").is(":hidden"))) {
				  $("#saisirplage").fadeOut();
			  };
		if (!($("#colorierplage").is(":hidden"))) {
				  $("#colorierplage").fadeOut();
			  };
		});	
	$("#saisie").click(function () {
		if ($("#saisirplage").is(":hidden")) {
				  $("#saisirplage").fadeIn();
			  }else {
				  $("#saisirplage").fadeOut();
			  };
		if (!($("#cache").is(":hidden"))) {
				  $("#cache").fadeOut();
			  };
		if (!($("#colorierplage").is(":hidden"))) {
				  $("#colorierplage").fadeOut();
			  };
		});	
	$("#coloriage").click(function () {
		if ($("#colorierplage").is(":hidden")) {
				  $("#colorierplage").fadeIn();
			  }else {
				  $("#colorierplage").fadeOut();
			  };
		if (!($("#saisirplage").is(":hidden"))) {
				  $("#saisirplage").fadeOut();
			  };
		if (!($("#cache").is(":hidden"))) {
				  $("#cache").fadeOut();
			  };
		});	
	}
	);

function voirsaisie()
{
	$("#saisirplage").show();
}

function voirgic()
{
	if($("#choixgic option:selected").val()=="0") {
		$("#gic_ID2").show();
		$("#gic_ID3").show();
	} else {
		$("#gic_ID2").hide();
		$("#gic_ID3").hide();
	}
}

var xhr = null; 
function getXhr()
{
	if(window.XMLHttpRequest)xhr = new XMLHttpRequest(); 
	else if(window.ActiveXObject)
	{ 
		try{
			xhr = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) 
		{
			xhr = new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	else 
	{
		alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
		xhr = false; 
	} 
}


function ShowRegroupement(){
	getXhr();
	// On definit ce qu'on va faire quand on aura la reponse
	xhr.onreadystatechange = function(){
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		if(xhr.readyState == 4 && xhr.status == 200){
			leselect = xhr.responseText;
			// On se sert de innerHTML pour rajouter les options a la liste
			document.getElementById('gic_ID').innerHTML = leselect;
		}
	}
	
	// Ici on va voir comment faire du post
	xhr.open("POST","ajax_regroupement.php",true);
	// ne pas oublier ca pour le post
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	// ne pas oublier de poster les arguments
	// ici, la classe
	sel = document.getElementById('classe');
	classe = sel.options[sel.selectedIndex].value;
	xhr.send("Classe="+classe);
}

function ShowPlages(){
	getXhr();
	// On definit ce qu'on va faire quand on aura la reponse
	xhr.onreadystatechange = function(){
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		if(xhr.readyState == 4 && xhr.status == 200){
			leselect = xhr.responseText;
			// On se sert de innerHTML pour rajouter les options a la liste
			document.getElementById('plages').innerHTML = leselect;
		}
	}
	
	// Ici on va voir comment faire du post
	xhr.open("POST","ajax_plages.php",true);
	// ne pas oublier ca pour le post
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	// ne pas oublier de poster les arguments
	// ici, la plage horaire
	sel = document.getElementById('heure');
	ID_plage = sel.options[sel.selectedIndex].value;
	xhr.send("ID_plage="+ID_plage);
}



function MM_reloadPage(init) {  //reloads the window if Nav4 resized
	
	if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
		
	document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
	
	else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
	
}

MM_reloadPage(true);


function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}


function confirmation(ref,jour_del,heure_del)
{
	if (confirm("Voulez-vous supprimer r\351ellement cette plage horaire du "+jour_del+" "+heure_del+ " ?")) { // Clic sur OK
		MM_goToURL('window','emploi_supprime_verif.php?ID_emploi='+ref+'&affiche=2');
	}
}


function MM_popupMsg(msg) { //v1.0
	alert(msg);
}

window.onload = function()
{
	fctLoad();
}
window.onscroll = function()
{
	fctShow();
}
window.onresize = function()
{
        fctShow();
}

function PartageShow()
{
        var Partage = document.getElementById("partage");
        
        if(Partage.checked==true) {
                $("#cachepartage").show("slow");
        } else {
                $("#cachepartage").hide("slow");
        }
}
</script>
</HEAD>
<BODY>
<BR />
<table width="95%"  border="0" align="center" cellpadding="0" cellspacing="0" class="lire_bordure" >
  <tr class="lire_cellule_4">
    <td  class="black_police"><div align="left">
        <?php if (isset($_SESSION['identite'])){echo '<img src="../images/identite.gif" >&nbsp;'.$_SESSION['identite'];
		if ($_SESSION['id_etat']==2){echo '&nbsp (Professeur suppl&eacute;ant)';};
		}?>
      </div></td>
    <td width="58%" class="black_police"><div align="center">Gestion de mon emploi du temps -
        <?php if((isset($_GET['affiche']))&&($_GET['affiche']==1)){ echo 'Mode planning';}else{echo 'Mode tableau';};?>
      </div></td>
    <td  ><div align="right" ><a href="<?php if ((isset($_SESSION['droits']))&&($_SESSION['droits']==1)){echo '../administration/index.php';} elseif ((isset($_SESSION['droits']))&&($_SESSION['droits']==3)){echo '../vie_scolaire/vie_scolaire.php';} else {echo 'enseignant.php';};?>"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0"></a><br>
      </div></td>
  </tr>
</table>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr style="background:#F0EDE5;">
    <td>
        <p align="left"><img src="../images/lightbulb.png"> <strong>Changement d'emploi du temps en cours d'ann&eacute;e scolaire</strong></p>
        <p align="left" style="margin-left:10px"> Afin que les anciennes saisies restent accessibles, la plage ne sera pas supprim&eacute;e mais cl&ocirc;tur&eacute;e.<br>
          Pour r&eacute;aliser cette op&eacute;ration, il suffit de modifier la date de fin d'existence de la plage.<br>
          Un cadenas appara&icirc;tra. Il vous suffira alors de cr&eacute;er la nouvelle plage de votre emploi du temps.</p>
     
	  </td>
    <td>
	<p align="center"><br />
<?php if ($totalRows_Rs_emploi>0){ ?>
        <?php if((isset($_GET['affiche']))&&($_GET['affiche']==1)){ ?>
        <form action="emploi.php?affiche=2" method="post"><input name="Affich_tableau" type="submit" id="Affich_tableau" value="Afficher en mode tableau">
	</form>
        <?php
	}else{?>
        <form action="emploi.php?affiche=1" method="post"><input name="Affich_planning" type="submit" id="Affich_planning" value="Afficher en mode planning">
	</form>        <?php 
	};
};?>     
         </p>
      <p align="center">
	  <?php
if ($totalRows_Rs_cloture>0){

if ((isset($_GET['masque_cloture']))&&($_GET['masque_cloture']==1)){
?>
    <form action="emploi.php?affiche=<?php if(isset($_GET['affiche'])){echo $_GET['affiche'];};?>&masque_cloture=0" method="post"><input name="affiche_cloture" type="submit" id="affiche_cloture" value="Afficher les plages clotur&eacute;es">
	</form>
        <?php
	}else{?>
    <form action="emploi.php?affiche=<?php if(isset($_GET['affiche'])){echo $_GET['affiche'];};?>&masque_cloture=1" method="post"><input name="masque_cloture" type="submit" id="masque_cloture" value="Masquer les plages clotur&eacute;es">
	</form>        
	<?php
};
};	  ?>
	  </p>
	  
	  
	  
	  
	  
	  </td>
  </tr>
</table>
<br/>

<?php
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_NomImport ="SELECT param_val FROM cdt_params WHERE param_val='EDT' OR param_val='UDT' LIMIT 1";
$NomImport = mysqli_query($conn_cahier_de_texte, $query_NomImport) or die(mysqli_error($conn_cahier_de_texte));
$totalrows_NomImport=mysqli_num_rows($NomImport);
mysqli_free_result($NomImport);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsparamImport2 = "SELECT param_val FROM cdt_params WHERE param_nom='Publication_Import' LIMIT 1";
$RsparamImport2 = mysqli_query($conn_cahier_de_texte, $query_RsparamImport2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsparamImport2 = mysqli_fetch_assoc($RsparamImport2);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsmesclasses = sprintf("SELECT DISTINCT cdt_classe.nom_classe,cdt_emploi_du_temps.classe_ID FROM cdt_classe,cdt_emploi_du_temps WHERE cdt_emploi_du_temps.prof_ID=%u AND cdt_emploi_du_temps.classe_ID != '' AND cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe ORDER BY cdt_classe.nom_classe ASC",$_SESSION['ID_prof']);
$Rsmesclasses = mysqli_query($conn_cahier_de_texte, $query_Rsmesclasses) or die(mysqli_error($conn_cahier_de_texte));
$totalRows_Rsmesclasses = mysqli_num_rows($Rsmesclasses);


//Boutons
if (($totalrows_NomImport==1)&&($row_RsparamImport2['param_val']=="Oui")) { //L'import EDT a ete effectue par admin
?>
<button id="showr">Importer un emploi du temps</button>
<?php 
}?>
<button id="saisie">Saisir une nouvelle plage horaire</button>
<?php
if (($totalRows_Rs_emploi>0)&&(($totalRows_Rsmesclasses!==0)||($totalrows_Rsgic>0))) {?>
<button id="coloriage">Colorier mes plages</button>
<?php
}



if (($totalrows_NomImport==1)&&($row_RsparamImport2['param_val']=="Oui")) { //L'import EDT a ete effectue par admin
	?>
	
<div id="cache" align="center"	<?php 	if ((!(isset($_POST['edtprof'])))&&($err!==10)) { echo "style=\"display:none\"";}?>>
<fieldset style="width : 90%" >
<legend align="top">Import d'un emploi du temps</legend>
<p class="Style70" style="width : 90%"> Vous avez l'opportunit&eacute; d'importer directement votre emploi du temps dans le cahier de textes. Vous pouvez visualiser l'emploi du temps, choisir les dates de validit&eacute; de cet emploi du temps (par d&eacute;faut, c'est toute l'ann&eacute;e scolaire) et importer ensuite celui-ci. <BR/>
  <BR/>
  Si votre emploi du temps n'est pas vide, cet import<b> ne l'effacera pas</b>.
  <?php if ($totalRows_Rs_emploi>0){ ?>
  En tout &eacute;tat de cause, vous aurez la possibilit&eacute; de modifier cet emploi du temps pr&eacute;c&eacute;demment saisi ou import&eacute; si vous le souhaitez.<BR>
  </BR>
</p>
<p class="Style70" style="width : 90%" align=left> Par rapport &agrave; votre emploi du temps actuel, cet import :<BR>
  </BR>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- ne changera pas les plages qui sont identiques (&eacute;ventuellement leurs dates de fin seront chang&eacute;es si celles-ci sont
  ant&eacute;rieures &agrave; la date de fin choisie pour l'import),<BR>
  </BR>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- ne rajoutera des nouvelles plages que si elles sont diff&eacute;rentes des actuelles.<BR>
  </BR>
</p>
<p class="Style70" style="width : 90%" align=center> Un choix vous est propos&eacute; aussi de stopper l'ancien emploi du temps. Cela signifie que si vous cochez la case,
  alors toutes les plages horaires actuelles (et encore d'actualit&eacute;) qui sont diff&eacute;rentes des plages &agrave; importer verront
  leur date de fin mise &agrave; jour &agrave; la veille de la date de d&eacute;but choisie pour l'import. Ce choix n'est utile qu'en cas de changement
  d'emploi du temps dans l'ann&eacute;e en cl&ocirc;turant ainsi l'emploi du temps en cours.
  <?php } ?>
</p>
<BR>
</BR>
<?php
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rsedt = sprintf("SELECT DISTINCT prof_ref FROM cdt_edt WHERE prof_ref=%u",GetSQLValueString($_SESSION['ID_prof'], "int"));
	$Rsedt = mysqli_query($conn_cahier_de_texte, $query_Rsedt) or die(mysqli_error($conn_cahier_de_texte));
	$totalRows_Rsedt = mysqli_num_rows($Rsedt);
	?>
<table width="90%" align="center" cellpadding="0" cellspacing="0">
  <tr valign=baseline><td width="35%" align="left">
    <?php
	
	if ($totalRows_Rsedt == 0) { //Pas d'edt trouve pour ce prof, il doit choisir son edt dans une liste
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rsedt2 ="SELECT DISTINCT prof_ref FROM cdt_edt WHERE prof_ref>='a' ORDER BY prof_ref asc";
		$Rsedt2 = mysqli_query($conn_cahier_de_texte, $query_Rsedt2) or die(mysqli_error($conn_cahier_de_texte));
		$totalRows_Rsedt2 = mysqli_num_rows($Rsedt2);
		if ($totalRows_Rsedt2 != 0) {
			
			?>
    <form method="post" name="form_edt" action="emploi.php<?php if(isset($_GET['affiche'])) {echo "?affiche=".$_GET['affiche'];}; ?>">
    <?php
			if (isset($_POST['edtprof'])) { $select="'".$_POST['edtprof']."'"; } else { $select=0; };
			echo "<select name='edtprof' onchange=\"if(this.value!=".$select.") form.submit();\">";
			
			?>
    <option value="0" 
			<?php if (!(isset($_POST['edtprof']))) { echo "selected"; }	?>
			>S&eacute;lectionnez le bon emploi du temps</option>
    <?php
			while ($row_Rsedt2 = mysqli_fetch_assoc($Rsedt2)) {
				if((isset($_POST['edtprof']))&&($row_Rsedt2['prof_ref']==$_POST['edtprof'])) {
					$selected="selected";
				} else {
					$selected="";
				}	
				echo "<option value='".$row_Rsedt2['prof_ref']."' ".$selected." >".$row_Rsedt2['prof_ref']."</option>";	
			} ;
			echo "</select></form>";
			echo '<p><a href="emploi.php?affiche=1">Annuler</a></p>';
		}
		

		mysqli_free_result($Rsedt2);	
	} else {
		echo "&nbsp;";
	}
	echo "<td width='65%' align='left'>";
	if (($totalRows_Rsedt !== 0)||((isset($_POST['edtprof']))&&(($_POST['edtprof'])!=='0'))) { //si le prof a son edt qui a deja ete authentifie ou bien s'il a choisi son nom dans la liste
		if ($totalRows_Rsedt !== 0) {
			if ($_SESSION['identite']=='') {
				$nomduprof=$_SESSION['nom_prof'];
			} else {
				$nomduprof=$_SESSION['identite'];
			}	
			$nprof=$_SESSION['ID_prof'];
		} else {
			$nomduprof=ucwords(str_replace("_"," ",$_POST['edtprof']));
                        $nprof=$_POST['edtprof'];
                }
                ?>
    <input type="button" name="lien" value="Visualiser l'emploi du temps de <?php echo $nomduprof; ?> &agrave; importer"        onclick="JavaScript: $.colorbox({width:'800px', height:'600px',href:'edt.php?idp=<?php echo $nprof; ?>', iframe:true});">
    <BR>
    </BR>
    <BR>
    </BR>
    </td>
    <form method="post" name="formimport" action="<?php echo $editFormAction; ?>">
    <tr>
        <td width="35%" align="right"> Date de validit&eacute; de cet emploi du temps import&eacute; <a href="#" class="tooltip">(Aide) <em>Si laiss&eacute; par d&eacute;faut,<br/>
          l'emploi du temps import&eacute; existera pendant toute l'ann&eacute;e scolaire.<br/>
          Vous pouvez utiliser ces param&egrave;tres suite &agrave; <br/>
          une modification d'emploi du temps en cours d'ann&eacute;e.</em></a>: </td>
        <td width="65%"><div STYLE="float: left" align="left">
            <?php 
		//initialisation date de debut
		
			$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_debut_annee' LIMIT 1;";
	$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
	$row = mysqli_fetch_row($result_read);
	$today_form = $row[0];
	mysqli_free_result($result_read);
	
                //initialisation date fin annee (avant modif, au 13/07)
			$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_fin_annee' LIMIT 1;";
	$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
	$row = mysqli_fetch_row($result_read);
	$date_fin = $row[0];
	mysqli_free_result($result_read);
	
	//Cas ou les dates n'ont pas ete saisies via le menu administrateur
if($today_form ==''){
// Modifie depuis la version 4.4.0 - On prend la date de debut d'annee plutot que la date du jour
	if(date('n') >= 8 && date('n') <=12){$today_form="01/09/".date('Y');} else {$today_form="01/09/".(date('Y')-1);};
};
if($date_fin ==''){
//initialisation date fin annee au 13/07
	if(date('n') >= 8 && date('n') <=12){$date_fin="13/07/".(date('Y')+1);} else {$date_fin="13/07/".date('Y');};
};
                ?>
            <script language="javascript" type="text/javascript">
                $(function() {
				$('selector').datepicker($.datepicker.regional['fr']);
				var dates = $( "#edt_exist_debut2, #edt_exist_fin2" ).datepicker({
						defaultDate: "+1w",
						changeMonth: true,
						numberOfMonths: 1,
						firstDay:1,
						onSelect: function( selectedDate ) {
							var option = this.id == "edt_exist_debut2" ? "minDate" : "maxDate",
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
            &nbsp;du&nbsp;
<input name='edt_exist_debut2' id='edt_exist_debut2' type='text' class="curseur_pointe"  id='edt_exist_debut2' value="
<?php if(isset($_POST['edt_exist_debut2'])){echo $_POST['edt_exist_debut2'];} else { echo $today_form;}; ?>" size="10"/>
            
			&nbsp;&nbsp;&nbsp;au&nbsp;
<input  name='edt_exist_fin2' id='edt_exist_fin2' type='text' class="curseur_pointe"  id='edt_exist_fin2' value="
<?php if(isset($_POST['edt_exist_fin2'])){echo $_POST['edt_exist_fin2'];} else { echo $date_fin;}; ?>"  size="10" />
          </div>
          <div id="plages2"  align="left"> <span class='erreur'>
            <?php 
		if (isset($message_erreur_date)){echo $message_erreur_date;};
		?>
            </span></div></td>
      </tr>
    <tr>
        <td width="35%" valign="middle" align="right"> Stopper l'emploi du temps pr&eacute;c&eacute;dent <a href="#" class="tooltip">(Aide) <em>Si cette case coch&eacute;e,<br/>
          l'emploi du temps pr&eacute;c&eacute;demment saisi sera stopp&eacute; &agrave; la veille de la date choisie pour le d&eacute;but de l'emploi du temps import&eacute;.</em></a>:
        <td width="65%" valign="middle" align="left"><input type="checkbox" name="stopedt" id="stopedt" value="" <?php if (isset($_POST['stopedt'])) {echo "checked=checked";}?>>
          <input type="hidden" name="import" value="<?php echo $nprof ;?>">
        </td>
      </tr>
    <BR>
    </BR>
    <BR>
    </BR>
    <tr>
        <td width="35%" valign="middle" align="right">&nbsp;</td>
        <td><p>
          <input type="submit" value="Importer cet emploi du temps dans mon Cahier de Textes" name="validedt">
        </p>
        <p><a href="emploi.php?affiche=1">Annuler</a></p></td>
      </tr>
</table>
</form>
<?php
	} else { // le prof doit choisir parmi plusieurs edt
		echo "</td></tr></table>";
	}
	?>
<BR>
</BR>
<BR>
</BR>
</div>
</fieldset>
<BR>
</BR>
<?php
}
mysqli_free_result($RsparamImport2);







if (($totalRows_Rs_emploi>0)&&(($totalRows_Rsmesclasses!==0)||($totalrows_Rsgic>0))) {
?>



<form  method="post" name="couleur" action="<?php echo $editFormAction; ?>">
  <div align="center">
  <div id='colorierplage' align="center" style="display:none">
    <fieldset style="width : 90%">
    <legend align="top">Choisir une m&ecirc;me couleur pour une classe ou pour un regroupement</legend>
    <BR>
    </BR>
    <table width="90%" align="center">
      <tr valign="baseline">
        <td width="10%" align="right"><div id="choixdelaclasse">Classe :</div></td>
        <td width="30%"><div align="left">
          <?php
	if ($totalRows_Rsmesclasses!==0) {
		echo "<select name='choixclasse' id='choixgic' onChange=\"voirgic();\">";
		echo "<option value=\"-1\" selected=\"selected\">S&eacute;lectionnez la classe</option>";
		while($row_Rsmesclasses = mysqli_fetch_assoc($Rsmesclasses)){
			echo "<option value=".$row_Rsmesclasses['classe_ID'].">".$row_Rsmesclasses['nom_classe']."</option>";
		}
		
		if ($totalrows_Rsgic>0) {
			?>
          <option value="0"></option>
          <option value="0"  style="font-weight: bold;">Regroupement de classes</option>
          <option value="0"></option>
          <?php	
		}
		echo "</select>";
	} 
	?>
        </td>
        <td width="40%" align="right" nowrap>Couleur de fond de la plage horaire <a href="#" class="tooltip">Aide <em>Si laiss&eacute; vide, la couleur des cellules sera inchang&eacute;e.</em></a> : </td>
        <td><div align="left">
            <input type="text" size="10" name="couleur_cellule2" value="" maxlength="7" style="font-family:Tahoma;font-size:x-small" 
	onChange="this.style.backgroundColor='red'">
            <img src="../images/color.gif" width="21" height="20" border="0" align="absmiddle" onClick="fctShow(document.couleur.couleur_cellule2);" style="cursor:pointer;"> </div></td>
      </tr>
      <tr>
        <td width="10%"  align="right"><div id='gic_ID2' style='display:none'>
            <?php 
	if ($totalrows_Rsgic==0){echo '</td><td width="30%"><span id="gic_ID3" class=\'erreur\'>(Pas de regroupement d&eacute;fini)<span>';}
	else { 
		$premier="OK";
		echo "Regroupement :</td><td width='30%'><div id='gic_ID3' style='display:none' align='left'><select name='choixgic'>";
		echo "<option value=\"-1\" selected=\"selected\">S&eacute;lectionnez le regroupement</option>";
		do { 
			echo "<option value='".$row_Rsgic['ID_gic']."'>".$row_Rsgic['nom_gic']."</option>";
		} while ($row_Rsgic = mysqli_fetch_assoc($Rsgic));
		echo "</select>";
	}
	if ($totalRows_Rsmesclasses==0) {
		echo "<script language='JavaScript' type='text/JavaScript'>$('#gic_ID2').show();$('#gic_ID3').show();$('#choixdelaclasse').hide();</script>";
	}
	?>
          </div></td>
        <td width="40%" align="right" nowrap>Couleur de la police du nom de la classe ou du regroupement <a href="#" class="tooltip">Aide <em>Si laiss&eacute; vide, la couleur de la police du nom de la classe sera inchang&eacute;e.</em></a> : </td>
        <td><div align="left">
            <input type="text" size="10" name="couleur_police2" value="" maxlength="7" style="font-family:Tahoma;font-size:x-small">
            <img src="../images/color.gif" width="21" height="20" border="0" align="absmiddle" onClick="fctShow(document.couleur.couleur_police2);" style="cursor:pointer"> </div></td>
      </tr>
    </table>
    <BR>
    </BR>
    <input type="hidden" name="couleur" value="couleur" />
    <input type="hidden" name="id_emploi" value="couleur" />
    <input type="submit" value="Valider la couleur pour cette classe">
    <BR>
    </BR>
	<p><a href="emploi.php?affiche=1">Annuler</a></p>
    <BR>
    </BR>
    </fieldset>
        
  </div>
</form>
<?php
};
?>

<form  method="post" name="form1" action="<?php echo $editFormAction; ?>#saisirplage" >
  <div id='saisirplage' align="center" style="display:

<?php
if ((((isset($_GET['saisie']))&&($_GET['saisie'])=='OK')||($err==1))&&($err!==100)) {echo "block";} else {echo "none";}
?>
">
    <fieldset style="width : 90%">
    <legend align="top">Saisir une nouvelle plage horaire</legend>
    <p><span class="erreur">NB : Pour saisir une plage de 2 heures par exemple, il suffit simplement de modifier l'heure de fin de la plage propos&eacute;e par d&eacute;faut. <br>
      La hauteur des cellules n'est pas proportionnelle &agrave; la dur&eacute;e.<br />
      </span></p>
    <table width="90%" align="center">
      <tr valign="baseline">
        <td nowrap align="right" >Jour de la semaine :</td>
        <td width="85%"><div align="left">
            <select name="jour_semaine" size="1" id="jour_semaine">
              <option value="Lundi" <?php if ((isset($_GET['ind_jour_sem'])&&($_GET['ind_jour_sem']==1))||((isset($_POST['jour_semaine']))&&(!(strcmp("Lundi", $_POST['jour_semaine']))))){echo ' selected="selected"';}?>>Lundi</option>
              <option value="Mardi" <?php if ((isset($_GET['ind_jour_sem'])&&($_GET['ind_jour_sem']==2))||((isset($_POST['jour_semaine']))&&(!(strcmp("Mardi", $_POST['jour_semaine']))))){echo ' selected="selected"';}?>>Mardi</option>
              <option value="Mercredi" <?php if ((isset($_GET['ind_jour_sem'])&&($_GET['ind_jour_sem']==3))||((isset($_POST['jour_semaine']))&&(!(strcmp("Mercredi", $_POST['jour_semaine']))))){echo ' selected="selected"';}?>>Mercredi</option>
              <option value="Jeudi" <?php if ((isset($_GET['ind_jour_sem'])&&($_GET['ind_jour_sem']==4))||((isset($_POST['jour_semaine']))&&(!(strcmp("Jeudi", $_POST['jour_semaine']))))){echo ' selected="selected"';}?>>Jeudi</option>
              <option value="Vendredi" <?php if ((isset($_GET['ind_jour_sem'])&&($_GET['ind_jour_sem']==5))||((isset($_POST['jour_semaine']))&&(!(strcmp("Vendredi", $_POST['jour_semaine']))))){echo ' selected="selected"';}?>>Vendredi</option>
              <option value="Samedi" <?php if ((isset($_GET['ind_jour_sem'])&&($_GET['ind_jour_sem']==6))||((isset($_POST['jour_semaine']))&&(!(strcmp("Samedi", $_POST['jour_semaine']))))){echo ' selected="selected"';}?>>Samedi</option>
              <option value="Dimanche" <?php if ((isset($_GET['ind_jour_sem'])&&($_GET['ind_jour_sem']==7))||((isset($_POST['jour_semaine']))&&(!(strcmp("Dimanche", $_POST['jour_semaine']))))){echo ' selected="selected"';}?>>Dimanche</option>
            </select>
          </div></td>
      </tr>
      <tr>
        <td align="right" nowrap >Indice de plage 
          de cours (1 &agrave; 12) <a href="#" class="tooltip">Aide <em>Ce nombre permettra d'ordonner les cours de la journ&eacute;e<br/>
          Vous pouvez d&eacute;finir jusqu'&agrave; 12 plages horaires sur une journ&eacute;e.<br/>
          Vous pouvez modifier les horaires propos&eacute;s par d&eacute;faut<br>
          par votre administrateur.</em></a>:</td>
        <td><div STYLE="float: left;" >
            <select name="heure" id="heure"  onChange='if($("#heure option:selected").val()!=="-1") {
$("#plages").show();
} else {
$("#plages").hide();
};ShowPlages();'>
              <option value="-1" selected>S&eacute;lectionnez la plage</option>
              <?php
$j=1;
do {
	if (isset($_GET['ind_plage'])) {
		echo '<option value="'.$j.'" ';
		if ($_GET['ind_plage']==$j){
			echo ' selected="selected"' ;
		}
		echo '>';
		if ($j<10){echo '0';};
		echo $j.'&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;'.$row_Rsplage_all['h1'].'h'.$row_Rsplage_all['mn1'].' - '.$row_Rsplage_all['h2'].'h'.$row_Rsplage_all['mn2'].'&nbsp;)</option>';
		$j=$j+1;
	} else { //sinon c'est une saisie faite par remplissage de saisie directement et en cas d'erreur de saisie, il faut garder en 
		echo '<option value="'.$j.'" ';
		if(isset($_POST['heure'])&&($_POST['heure']==$j)){
			echo ' selected="selected"' ;
		}
		echo '>';
		if ($j<10){echo '0';};
		echo $j.'&nbsp;&nbsp;&nbsp;&nbsp;(&nbsp;';
		//en cas d'erreur (absence matiere, classe..), on reprend les valeurs precedemment postees sans executer ShowPlages
		if(isset($_POST['h1'])){echo $_POST['h1'];} else {echo $row_Rsplage_all['h1'];};
		echo'h';
		if(isset($_POST['mn1'])){echo $_POST['mn1'];} else {echo $row_Rsplage_all['mn1'];};
		echo ' - ';
		if(isset($_POST['h2'])){echo $_POST['h2'];} else {echo $row_Rsplage_all['h2'];};
		echo'h';
		if(isset($_POST['mn2'])){echo $_POST['mn2'];} else {echo $row_Rsplage_all['mn2'];};
		
		echo '&nbsp;)</option>';
		$j=$j+1;
		
	}
} while ($row_Rsplage_all = mysqli_fetch_assoc($Rsplage_all)); ?>
            </select>
          </div>
          <?php if(isset($_GET['ind_plage'])){echo '<div style="float: left" id="plages"  align="left">';
	include 'ajax_plages.php' ;
echo '</div>';}
else { ?>
          <div STYLE="float: left" id="plages"  align="left"> <span class='erreur'>
            <?php 
	if (isset($message_erreur_plage)){echo $message_erreur_plage;};
	?>
            </span></div>
          <?php }?>        </td>
      </tr>
      <tr valign="baseline">
        <td nowrap align="right"><?php if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){echo 'Semaine Paire/Impaire :';} else {echo 'Semaine A et/ou B :';};?>        </td>
        <td width="85%"><div align="left">
            <select name="semaine" id="select14">
              <option value="A et B" <?php if ((!(strcmp("A et B", $row_Rs_emploi['jour_semaine'])))||((isset($_POST['jour_semaine']))&&(!(strcmp("A et B", $_POST['semaine']))))) {echo ' selected="selected"';} ?>>
              <?php if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){echo 'Paire et Impaire';} 
else {echo 'A et B';};?>
              </option>
              <option value="A" <?php if ((!(strcmp("A", $row_Rs_emploi['jour_semaine'])))||((isset($_POST['jour_semaine']))&&(!(strcmp("A", $_POST['semaine']))))) {echo ' selected="selected"';} ?>>
              <?php if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){echo 'Semaine Paire';} 
else {echo 'A';};?>
              </option>
              <option value="B" <?php if ((!(strcmp("B", $row_Rs_emploi['jour_semaine'])))||((isset($_POST['jour_semaine']))&&(!(strcmp("B", $_POST['semaine']))))) {echo ' selected="selected"';} ?>>
              <?php if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){echo 'Semaine Impaire';} 
else {echo 'B';};?>

		  
			  
            </select>
			
	OU 	Autre(s) alternance(s)	: Sem 1<input name="s1" type="checkbox" id="s1" value="1" style="vertical-align:middle;" >  
			Sem 2<input name="s2" type="checkbox" id="s2" value="2" style="vertical-align:middle;" > 
			Sem 3<input name="s3" type="checkbox" id="s3" value="3" style="vertical-align:middle;" > 
			Sem 4<input name="s4" type="checkbox" id="s4" value="4" style="vertical-align:middle;" >
			
          </div></td>
      </tr>

      <tr valign="baseline">
        <td nowrap align="right">Classe :</td>
        <td width="85%"><div align="left">
            <?php 

if ($totalrows_Rsgic>0) {?>
            <select name="classe_ID" id="classe"  onChange="ShowRegroupement()" >
              <?php } else { ?>
              <select name="classe_ID" id="classe" >
              <?php
};?>
              <option value="-1" selected>S&eacute;lectionnez la classe</option>
              <?php
$res = mysqli_query($conn_cahier_de_texte, "SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe ORDER BY nom_classe ASC");
while($row = mysqli_fetch_assoc($res)){
	echo "<option value='".$row["ID_classe"]."'";
	if ((isset($_POST['classe_ID']))&&($row["ID_classe"]==$_POST['classe_ID'])){echo 'selected=" selected"';};
	
	echo ">".$row["nom_classe"];
	echo "</option>";
};?>
              <?php if ($totalrows_Rsgic>0) {?>
              <option value="0"></option>
              <option value="0" <?php if ((isset($_POST['classe_ID']))&&($_POST['classe_ID']==0)){echo 'selected=" selected"';};?> style="font-weight: bold;" >Regroupement de classes</option>
              <option value="0"></option>
              <?php } ?>
            </select>
            <div  id='gic_ID' style='display:inline' align="left">
              <?php if ((isset($_POST['classe_ID']))&&($_POST['classe_ID']==0)){
	
	if ($totalrows_Rsgic==0){echo '<span class=\'erreur\'>(Pas de regroupement d&eacute;fini)<span>';} else
	{ 
		echo "<select name='gic_ID'>";
		do { 
			echo "<option value='".$row_Rsgic['ID_gic']."'>".$row_Rsgic['nom_gic']."</option>";
		} while ($row_Rsgic = mysqli_fetch_assoc($Rsgic));
		echo "</select>";
		
	}
	
	
}; ?>
            </div>
            <span class='erreur'>
            <?php if (isset($message_erreur_classe)){echo $message_erreur_classe;};?>
            </span> <br/>
          </div></td>
      </tr>

      <tr valign="baseline">
        <td nowrap align="right">Groupe :</td>
        <td width="85%"><div align="left">
            <select name="groupe" id="groupe">
              <?php
do {  
	?>
              <option value="<?php echo $row_Rsgroupe['groupe']?>"<?php 
	if ((isset($_POST['groupe'])) AND ($_POST['groupe']==$row_Rsgroupe['groupe'] )) {echo 'selected';} else {if (!(isset($_POST['groupe'])) AND ($row_Rsgroupe['groupe']=='Classe entière')) {echo 'selected';};};?>><?php echo $row_Rsgroupe['groupe']?></option>
              <?php
} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
$rows = mysqli_num_rows($Rsgroupe);
if($rows > 0) {
	mysqli_data_seek($Rsgroupe, 0);
	$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
}
?>
            </select>
          </div></td>
      </tr>
      <tr valign="baseline">
        <td nowrap align="right">Mati&egrave;re :</td>
        <td width="85%"><div align="left">
            <select name="matiere_ID" id="select17">
              <option value="-1" selected>S&eacute;lectionnez la mati&egrave;re</option>
              <?php
do {  
	?>
              <option value="<?php echo $row_RsMatiere['ID_matiere']?>"<?php if ((!(strcmp($row_RsMatiere['ID_matiere'], $row_Rs_emploi['matiere_ID'])))||((isset($_POST['matiere_ID']))&&(!(strcmp($row_RsMatiere['ID_matiere'], $_POST['matiere_ID'])))))  {echo ' selected="selected"';} ?>><?php echo $row_RsMatiere['nom_matiere']?></option>
              <?php
} while ($row_RsMatiere = mysqli_fetch_assoc($RsMatiere));
$rows = mysqli_num_rows($RsMatiere);
if($rows > 0) {
	mysqli_data_seek($RsMatiere, 0);
	$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
}
?>
            </select>
            <span class='erreur'>
            <?php 
if (isset($message_erreur_matiere)){echo $message_erreur_matiere;};
?>
            </span> </div></td>
      </tr>
      <tr>
        <td valign='middle' align="right">Heure partag&eacute;e <a href="#" class="tooltip">Aide <em>Cochez si cette plage horaire est partag&eacute;e avec d'autres enseignants. </em></a>:</td>
        <td><input name="partage" type="checkbox" id="partage" value="partage" style="vertical-align:middle;" onClick="PartageShow();">        </td>
        <td valign='middle'><div id="cachepartage"  style="display:none" align="center"> Choisissez au moins un enseignant avec qui vous partagez cette heure&nbsp;:<br/>
            <a href="#" class="tooltip">Aide <em>Pour s&eacute;lectionner plusieurs enseignants, maintenez enfonc&eacute;e la touche CTRL de votre clavier en cliquant sur un nom.</em></a>
            <select multiple="multiple" name="partage_ID[]" id="partage_ID" size=5>
              <?php
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

$query_RsProf = sprintf("SELECT * FROM cdt_prof WHERE droits='2' AND ID_prof<>%u ORDER BY nom_prof ASC",$_SESSION['ID_prof']);
$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);

do {  
        ?>
              <option value="<?php echo $row_RsProf['ID_prof']?>">
              <?php if ($row_RsProf['identite']=="")  {echo $row_RsProf['nom_prof'];} else {echo $row_RsProf['identite'];}?>
              </option>
              <?php
} while ($row_RsProf = mysqli_fetch_assoc($RsProf));
?>
            </select>
          </div></td>
      </tr>
      <tr valign="baseline">
        <td align="right" nowrap>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td align="right" nowrap>Couleur de fond de la plage horaire (facultatif) <a href="#" class="tooltip">Aide <em>Si laiss&eacute; vide, les cellules sont vertes par d&eacute;faut. <br/>
          Il peut &ecirc;tre n&eacute;cessaire cependant de mat&eacute;rialiser <br/>
          une cellule pour la mettre en &eacute;vidence <br/>
          dans son emploi du temps (heure de vie de classe par exemple). <br/>
          Attention &agrave; ne pas utiliser la couleur bleu/gris&eacute; utilis&eacute;e <br>
          dans l'application pour mettre en &eacute;vidence les cellules <br>
          relatives aux s&eacute;ances d&eacute;j&agrave; remplies.</em></a>: </td>
        <td><div align="left">
            <input type="text" size="10" name="couleur_cellule" value="<?php if(isset($_POST['couleur_cellule'])){echo $_POST['couleur_cellule'];};?>" maxlength="7" style="font-family:Tahoma;font-size:x-small;background-color:#CAFDBD">
            <img src="../images/color.gif" width="21" height="20" border="0" align="absmiddle" onClick="fctShow(document.form1.couleur_cellule);" style="cursor:pointer;"> </div></td>
      </tr>
      <tr valign="baseline">
        <td align="right" nowrap>Couleur de la police du nom de la classe (facultatif) <a href="#" class="tooltip">Aide <em>Il peut &ecirc;tre n&eacute;cessaire de mettre en &eacute;vidence <br/>
          le nom d'une classe dans son emploi du temps <br/>
          (heure de vie de classe par exemple).</em></a>: </td>
        <td><div align="left">
            <input type="text" size="10" name="couleur_police" value="<?php if(isset($_POST['couleur_police'])){echo $_POST['couleur_police'];};?>" maxlength="7" style="font-family:Tahoma;font-size:x-small;background-color:#000000">
            <img src="../images/color.gif" width="21" height="20" border="0" align="absmiddle" onClick="fctShow(document.form1.couleur_police);" style="cursor:pointer;"> </div></td>
      </tr>
      <tr>
        <td nowrap align="right">Existence de la plage horaire (facultatif) <a href="#" class="tooltip">Aide <em>Si laiss&eacute; par d&eacute;faut,<br/>
          la plage horaire existera pendant toute l'ann&eacute;e scolaire.<br/>
          Vous pouvez utiliser ces param&egrave;tres suite &agrave; <br/>
          une modification d'emploi du temps en cours d'ann&eacute;e.</em></a>: </td>
        <td><div STYLE="float: left" align="left">
            <?php 
//recuperation des dates de debut et de fin de l'annee scolaire

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_debut_annee' LIMIT 1";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$date_debut_annee = $row[0];
mysqli_free_result($result_read);


$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_fin_annee' LIMIT 1";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$date_fin_annee = $row[0];
mysqli_free_result($result_read);

//Cas ou les dates n'ont pas ete saisies via le menu administrateur
if($date_debut_annee ==''){
// Modifie depuis la version 4.4.0 - On prend la date de debut d'annee plutot que la date du jour
	if(date('n') >= 8 && date('n') <=12){$date_debut_annee="01/09/".date('Y');} else {$date_debut_annee="01/09/".(date('Y')-1);};
};
if($date_fin_annee ==''){
//initialisation date fin annee au 13/07
	if(date('n') >= 8 && date('n') <=12){$date_fin_annee="13/07/".(date('Y')+1);} else {$date_fin_annee="13/07/".date('Y');};
};
?>
            <script language="javascript" type="text/javascript">
$(function() {
$('selector').datepicker($.datepicker.regional['fr']);
var dates = $( "#edt_exist_debut, #edt_exist_fin" ).datepicker({
defaultDate: "+1w",
changeMonth: true,
numberOfMonths: 1,
onSelect: function( selectedDate ) {
var option = this.id == "edt_exist_debut" ? "minDate" : "maxDate",
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
<?php


if ($_SESSION['id_etat']==2){ //enseignant remplacant

// ici mettre la date de creation de l'edt du suppleant

// balayer la table edt et trouver la 1ere date de debut 
$datedebut='';
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsEmploiSupl = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE  prof_ID=%u",$_SESSION['ID_prof']);
$RsEmploiSupl = mysqli_query($conn_cahier_de_texte, $query_RsEmploiSupl) or die(mysqli_error($conn_cahier_de_texte));
$row_RsEmploiSupl = mysqli_fetch_assoc($RsEmploiSupl);
$totalRows_RsEmploiSupl = mysqli_num_rows($RsEmploiSupl);
if ($totalRows_RsEmploiSupl>0){

	$datedebut=substr($row_RsEmploiSupl['edt_exist_debut'],8,2)."/".substr($row_RsEmploiSupl['edt_exist_debut'],5,2)."/".substr($row_RsEmploiSupl['edt_exist_debut'],0,4);
	echo $datedebut;
	}
	else { 
        //si pas de date mettre celle d'aujourd'hui;
        echo date('d/m/Y');
        };
        // mettre un input a hidden pour passer la variable
        ?>
        
<input type='hidden' name='edt_exist_debut' type='text' class="curseur_pointe"  id='edt_exist_debut' value="
<?php echo $datedebut;?>" size="10"/>
<?php


}
else{
// C'est un enseignant titulaire

?>
<input name='edt_exist_debut' type='text' class="curseur_pointe"  id='edt_exist_debut' value="<?php if(isset($_POST['edt_exist_debut'])){echo $_POST['edt_exist_debut'];} else { 
//echo $date_debut_annee;
echo date('d/m/Y');
};?>" size="10"/>
<?php
}
?>
&nbsp;au&nbsp;&nbsp;
            <input  name='edt_exist_fin' type='text' class="curseur_pointe"  id='edt_exist_fin' value="<?php if(isset($_POST['edt_exist_fin'])){echo $_POST['edt_exist_fin'];}else{ echo $date_fin_annee;};?>"  size="10" />
          </div>
          <div id="plages"  align="left"> <span class='erreur'>
            <?php 
if (isset($message_erreur_date)){echo $message_erreur_date;};
?>
            </span></div></td>
      </tr>
      <tr valign="baseline">
        <td nowrap align="right">&nbsp;</td>
        <td width="85%"><div align="left"> 
          <p><br>
            <?php
	    //en cas d'erreur (absence matiere, classe..), on reprend les valeurs precedemment postees 
		if(isset($_POST['h1'])){echo '<input type="hidden" name="h1" value="'.$_POST['h1'].'" />';}; 
		if(isset($_POST['mn1'])){echo '<input type="hidden" name="mn1" value="'.$_POST['mn1'].'" />';}; 
		if(isset($_POST['h2'])){echo '<input type="hidden" name="h2" value="'.$_POST['h2'].'" />';}; 
		if(isset($_POST['mn2'])){echo '<input type="hidden" name="mn2" value="'.$_POST['mn2'].'" />';}; 
		if(isset($_POST['duree'])){echo '<input type="hidden" name="duree" value="'.$_POST['duree'].'" />';};
	?>
            <input type="submit" value="Ins&eacute;rer cette nouvelle plage horaire">
          </p>
          <p><a href="emploi.php?affiche=1">Annuler</a></p>
        </div>
          <br/></td>
      </tr>
    </table>
    <input type="hidden" name="MM_insert" value="form1">
    </fieldset>
  </div>
</form>

<?php





	
	if ((isset($_GET['affiche']))&&($_GET['affiche']==1)){?>
<table width="95%" align="center" cellpadding="0" cellspacing="0" class="lire_bordure">
  <tr class="lire_cellule_4">
    <td ><div align="center"></div></td>
    <td >Lundi</td>
    <td >Mardi</td>
    <td >Mercredi</td>
    <td >Jeudi</td>
    <td >Vendredi</td>
    <td >Samedi</td>
    <td >Dimanche</td>
  </tr>
  <?php $tab[1]='Lundi';$tab[2]='Mardi';$tab[3]='Mercredi';$tab[4]='Jeudi';$tab[5]='Vendredi';$tab[6]='Samedi';$tab[7]='Dimanche';
		
		
		for($x=1;$x < 13;$x++) {?>
  <tr>
    <td bgcolor="#FFFFFF" class="detail"><div align="center"><?php echo $x ;?></div></td>
    <?php
			for($i=1;$i < 8;$i++) { ?>
    <td bgcolor="#FFFFFF" class="detail"><?php 		
				$nb_cell=0;
				do { 
					if (($row_Rs_emploi['jour_semaine']==$tab[$i])&&($row_Rs_emploi['heure']==$x )){
						$nb_cell+=1;
						if ($row_Rs_emploi['couleur_cellule']==''){$row_Rs_emploi['couleur_cellule']='#CAFDBD';}
						if ($row_Rs_emploi['couleur_police']==''){$row_Rs_emploi['couleur_police']='#000000';}
						?>
      <div onClick="MM_goToURL('window','emploi_modif.php?ID_emploi=<?php echo $row_Rs_emploi['ID_emploi'];?>&affiche=1&regroupement=<?php if ($row_Rs_emploi['gic_ID']==0){echo '0';} else {echo '1';}?>');return document.MM_returnValue" style="cursor:pointer">
        <?php
						echo '<style>.raised'. $x.$i.$nb_cell.' .top, .raised'. $x.$i.$nb_cell.' .bottom {display:block; background:transparent; font-size:1px;}
						.raised'. $x.$i.$nb_cell.' .b1, .raised'. $x.$i.$nb_cell.' .b2, .raised'. $x.$i.$nb_cell.' .b3, .raised'. $x.$i.$nb_cell.' .b4, .raised'. $x.$i.$nb_cell.' .b1b, .raised'. $x.$i.$nb_cell.' .b2b, .raised'. $x.$i.$nb_cell.' .b3b, .raised'. $x.$i.$nb_cell.' .b4b {display:block; overflow:hidden;}
						.raised'. $x.$i.$nb_cell.' .b1, .raised'. $x.$i.$nb_cell.' .b2, .raised'. $x.$i.$nb_cell.' .b3, .raised'. $x.$i.$nb_cell.' .b1b, .raised'. $x.$i.$nb_cell.' .b2b, .raised'. $x.$i.$nb_cell.' .b3b {height:1px;}
						.raised'. $x.$i.$nb_cell.' .b2 {background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #eee;} 
						.raised'. $x.$i.$nb_cell.' .b3 {background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #ddd;} 
						.raised'. $x.$i.$nb_cell.' .b4 {background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #aaa;} 
						.raised'. $x.$i.$nb_cell.' .b4b {background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #eee; border-right:1px solid #999;} 
						.raised'. $x.$i.$nb_cell.' .b3b {background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #ddd; border-right:1px solid #999;} 
						.raised'. $x.$i.$nb_cell.' .b2b {background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #aaa; border-right:1px solid #999;} 
						.raised'. $x.$i.$nb_cell.' .boxcontent {background:'.$row_Rs_emploi['couleur_cellule'].';}
						.raised'. $x.$i.$nb_cell.' .b1 {margin:0 5px; background:#fff;}
						.raised'. $x.$i.$nb_cell.' .b2, .raised'. $x.$i.$nb_cell.' .b2b {margin:0 3px; border-width:0 2px;}
						.raised'. $x.$i.$nb_cell.' .b3, .raised'. $x.$i.$nb_cell.' .b3b {margin:0 2px;}
						.raised'. $x.$i.$nb_cell.' .b4, .raised'. $x.$i.$nb_cell.' .b4b {height:2px; margin:0 1px;}
						.raised'. $x.$i.$nb_cell.' .b1b {margin:0 5px; background:#999;}
						.raised'. $x.$i.$nb_cell.' .boxcontent {display:block;  background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #999;}
						
						</style>';
						echo '<div class="raised'. $x.$i.$nb_cell.'" ><b class="top"><b class="b1"></b><b class="b2"></b><b class="b3"></b><b class="b4"></b></b><div class="boxcontent">';
						if (date('Y-m-d')>$row_Rs_emploi['edt_exist_fin']){echo '&nbsp;<img src="../images/cadenas.gif" width="15" height="18" alt="Plage cl&ocirc;tur&eacute;e depuis le '.substr($row_Rs_emploi['edt_exist_fin'],8,2).'/'.substr($row_Rs_emploi['edt_exist_fin'],5,2).'/'.substr($row_Rs_emploi['edt_exist_fin'],0,4).'" title="Plage cl&ocirc;tur&eacute;e depuis le '.substr($row_Rs_emploi['edt_exist_fin'],8,2).'/'.substr($row_Rs_emploi['edt_exist_fin'],5,2).'/'.substr($row_Rs_emploi['edt_exist_fin'],0,4).'">';};
						
						mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
						$query_RsPartage = sprintf("SELECT profpartage_ID FROM cdt_emploi_du_temps_partage WHERE ID_emploi=%u",$row_Rs_emploi['ID_emploi']);
						$RsPartage = mysqli_query($conn_cahier_de_texte, $query_RsPartage) or die(mysqli_error($conn_cahier_de_texte));
						$totalRows_RsPartage = mysqli_num_rows($RsPartage);
						mysqli_free_result($RsPartage);
						
						if ($totalRows_RsPartage>0){echo '&nbsp;<img src="../images/partage.gif" title="Cette plage est partag&eacute;e avec au moins un autre enseignant.">';};
						
						echo '<strong>&nbsp;'.$row_Rs_emploi['heure_debut'] .'</strong> - '.$row_Rs_emploi['heure_fin'].' -  ';
						
											
						if (($row_Rs_emploi['semaine']!="A et B") && (($row_Rs_emploi['semaine']=="A" )||($row_Rs_emploi['semaine']=="B" ))   ) {
							echo "<font color=red><b>";
							if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){
								if($row_Rs_emploi['semaine']=='A'){echo 'Sem. Paire';} else {echo 'Sem. Impaire';}; 
							} else {
								echo 'Sem... '.$row_Rs_emploi['semaine']; //on
							};
							echo "</b></font>";
						};
						
					
						if($row_Rs_emploi['semaine']=="A et B"){
							if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){echo 'Sem. P et I';}else{echo 'Sem. '.$row_Rs_emploi['semaine'];};
							} ;
							
						if (($row_Rs_emploi['semaine']!="A et B") && (($row_Rs_emploi['semaine']!="A" )&&($row_Rs_emploi['semaine']!="B" ))   ) 
							{
							echo "<font color=red><b>Sem ".$row_Rs_emploi['semaine']."</b></font>";
							};
						
						
						echo '<br />&nbsp;<span style="color:'.$row_Rs_emploi['couleur_police'].'">';
						
						
						//------------------------------------
						
                                                if ($row_Rs_emploi['classe_ID']==0){
                                                        if ($row_Rs_emploi['gic_ID']!=='0'){
                                                                //regroupement / retrouver le nom
                                                                $query_Rsgic2 = sprintf("SELECT nom_gic,commentaire_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u",$row_Rs_emploi['gic_ID']);
                                                                $Rsgic2 = mysqli_query($conn_cahier_de_texte, $query_Rsgic2) or die(mysqli_error($conn_cahier_de_texte));
                                                                $row_Rsgic2 = mysqli_fetch_assoc($Rsgic2);
                                                                echo '(R)&nbsp;'.$row_Rsgic2['nom_gic'];
								if (strlen($row_Rsgic2['nom_gic'])>20){echo'<br />';};
								mysqli_free_result($Rsgic2);
								echo '</span>&nbsp;-&nbsp;' .$row_Rs_emploi['groupe'] .'<br />';
							}
							else {
								echo "<font color=red><b>Classe inconnue - A modifier</b></font>";
								echo "</span><br />";
							}
						} else { 
							$query_RsClasse = sprintf("SELECT nom_classe FROM cdt_classe WHERE ID_classe=%u",$row_Rs_emploi['classe_ID']);
							$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
							$row_RsClasse = mysqli_fetch_assoc($RsClasse);
							echo 	$row_RsClasse['nom_classe'];
							if (strlen($row_RsClasse['nom_classe'])>20){echo'<br />';};
							echo '</span>&nbsp;-&nbsp;' .$row_Rs_emploi['groupe'] .'<br />';
						};
						
						//------------------------------------
						
						
						
						echo '&nbsp;'.$row_Rs_emploi['nom_matiere'].'<br /></div><b class="bottom"><b class="b4b"></b><b class="b3b"></b><b class="b2b"></b><b class="b1b"></b></b></div>';
					?>
      </div>
      <?php	 } ; ; 
				} while ($row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi));
	 
	 if ($totalRows_Rs_emploi>0){mysqli_data_seek($Rs_emploi, 0);};
				
                                if ($nb_cell==0) {
                                        
                                        //cellule vide ?>
      <div onClick="MM_goToURL('window','emploi.php?ind_jour_sem=<?php echo $i;?>&ID_prof=<?php echo $_SESSION['ID_prof'];?>&ind_plage=<?php echo $x;?>&affiche=1&saisie=OK#saisirplage');return document.MM_returnValue" style="cursor:pointer"><br/>
        <br/>
        <br/>
        <br/>
      </div>
      <?php   
                                } ?>
    </td>
    <?php
			}?>
  </tr>
  <?php } ?>
</table>
<BR>
</BR>
<?php } else { ?>
<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" class="bordure">
  <tr class="Style6" height="20" >
    <td >&nbsp;</td>
    <td >Jour </td>
    <td>Pos.</td>
    <td>Sem.</td>
    <td>Classe</td>
    <td>Groupe</td>
    <td>Mati&egrave;re</td>
    <td>Heure d&eacute;b.</td>
    <td>Heure fin</td>
    <td>Dur&eacute;e</td>
    <td><div align="left">Existe du </div></td>
    <td><div align="left">jusqu'au</div></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <?php 
                
                mysqli_data_seek($Rs_emploi, 0);
                
                while ($row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi)) {
                        
                        ?>
  <tr height="20">
    <td bgcolor="#FFFFFF" class="menu_detail"><?php if (date('Y-m-d')>$row_Rs_emploi['edt_exist_fin']){echo '&nbsp;<img src="../images/cadenas.gif" width="15" height="18" alt="Plage cl&ocirc;tur&eacute;e depuis le '.substr($row_Rs_emploi['edt_exist_fin'],8,2).'/'.substr($row_Rs_emploi['edt_exist_fin'],5,2).'/'.substr($row_Rs_emploi['edt_exist_fin'],0,4).'" title="Plage cl&ocirc;tur&eacute;e depuis le '.substr($row_Rs_emploi['edt_exist_fin'],8,2).'/'.substr($row_Rs_emploi['edt_exist_fin'],5,2).'/'.substr($row_Rs_emploi['edt_exist_fin'],0,4).'">';};
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_RsPartage = sprintf("SELECT profpartage_ID FROM cdt_emploi_du_temps_partage WHERE ID_emploi=%u",$row_Rs_emploi['ID_emploi']);
			$RsPartage = mysqli_query($conn_cahier_de_texte, $query_RsPartage) or die(mysqli_error($conn_cahier_de_texte));
			$totalRows_RsPartage = mysqli_num_rows($RsPartage);
			mysqli_free_result($RsPartage);
			
			if ($totalRows_RsPartage>0){echo '&nbsp;<img src="../images/partage.gif" width="15" height="18" title="Cette plage est partag&eacute;e avec au moins un autre enseignant.">';};?></td>
    <td bgcolor="#FFFFFF" class="menu_detail"><div align="left"><?php echo $row_Rs_emploi['jour_semaine']; ?></div></td>
    <td bgcolor="#FFFFFF" class="menu_detail"><div align="left"><?php echo $row_Rs_emploi['heure']; ?></div></td>
    <td bgcolor="#FFFFFF" class="menu_detail"><div align="left"><?php echo $row_Rs_emploi['semaine']; ?></div></td>
    <td bgcolor="#FFFFFF" class="menu_detail"><div align="left">
        <?php 
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			if ($row_Rs_emploi['classe_ID']==0){
				if ($row_Rs_emploi['gic_ID']!=='0'){
					//regroupement / retrouver le nom
					$query_Rsgic = sprintf("SELECT nom_gic,commentaire_gic FROM cdt_groupe_interclasses  WHERE ID_gic=%u",$row_Rs_emploi['gic_ID']);
					$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
					$row_Rsgic = mysqli_fetch_assoc($Rsgic);
					?>
        <SCRIPT LANGUAGE="JavaScript"> function commentaire_alert () {alert ("<?php echo $row_Rsgic['nom_gic'].' - '.$row_Rsgic['commentaire_gic'] ?>");}</SCRIPT>
        <?php
					echo '<div class="curseur_aide" onClick="commentaire_alert ()" >'.$row_Rsgic['nom_gic'].'</div>';
				}
				else {
					echo "<font color=red><b>Classe inconnue</b></font>";
				}
			} else { 
				$query_RsClasse = sprintf("SELECT nom_classe FROM cdt_classe WHERE ID_classe=%u",$row_Rs_emploi['classe_ID']);
				$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
				$row_RsClasse = mysqli_fetch_assoc($RsClasse);
				echo 	$row_RsClasse['nom_classe'];
			}; ?>
      </div></td>
    <td bgcolor="#FFFFFF" class="menu_detail"><div align="left"><?php echo $row_Rs_emploi['groupe']; ?></div></td>
    <td bgcolor="#FFFFFF" class="menu_detail"><div align="left"><?php echo $row_Rs_emploi['nom_matiere']; ?></div></td>
    <td bgcolor="#FFFFFF" class="menu_detail"><div align="left"><?php echo $row_Rs_emploi['heure_debut']; ?></div></td>
    <td bgcolor="#FFFFFF" class="menu_detail"><div align="left"><?php echo $row_Rs_emploi['heure_fin']; ?></div></td>
    <td bgcolor="#FFFFFF" class="menu_detail"><div align="left"><?php echo $row_Rs_emploi['duree']; ?></div></td>
    <td bgcolor="#FFFFFF" class="menu_detail"><div align="left">
        <?php if ($row_Rs_emploi['edt_exist_debut']<>'0000-00-00'){ echo substr($row_Rs_emploi['edt_exist_debut'],8,2).'/'.substr($row_Rs_emploi['edt_exist_debut'],5,2).'/'.substr($row_Rs_emploi['edt_exist_debut'],0,4);}?>
      </div></td>
    <td bgcolor="#FFFFFF" class="menu_detail"><div align="left">
        <?php if ($row_Rs_emploi['edt_exist_fin']<>'2100-00-00'){ echo substr($row_Rs_emploi['edt_exist_fin'],8,2).'/'.substr($row_Rs_emploi['edt_exist_fin'],5,2).'/'.substr($row_Rs_emploi['edt_exist_fin'],0,4);};
			?>
      </div></td>
    <td bgcolor="#FFFFFF" class="menu_detail"><div align="left"><img class="curseur_pointe" src="../images/button_edit.png" width="12" height="13" border="0" onClick="MM_goToURL('window','emploi_modif.php?ID_emploi= <?php echo $row_Rs_emploi['ID_emploi'];?>&regroupement=<?php if ($row_Rs_emploi['gic_ID']==0){echo '0';} 
				else {echo '1';}?>');return document.MM_returnValue"></div></td>
    <td bgcolor="#FFFFFF" class="menu_detail"><div align="left"><img class="curseur_pointe" src="../images/ed_delete.gif" width="11" height="13" 
				onClick= "return confirmation('<?php echo $row_Rs_emploi['ID_emploi'];?>','<?php echo $row_Rs_emploi['jour_semaine'];?>','<?php echo $row_Rs_emploi['heure_debut'];?>');return document.MM_returnValue;
				"></div></td>
  </tr>
  <?php } ?>
</table>
<BR>
</BR>
<?php 
	}
?>




<BR>
</BR>

<p>&nbsp; </p>
<p align="center"><a href="<?php if ((isset($_SESSION['droits']))&&($_SESSION['droits']==1)){echo '../administration/index.php';} elseif ((isset($_SESSION['droits']))&&($_SESSION['droits']==3)){echo '../vie_scolaire/vie_scolaire.php';} else {echo 'enseignant.php';};?>"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0"></a> </p>
</BODY>
</HTML>
<?php
mysqli_free_result($Rs_emploi);
mysqli_free_result($RsClasse);
mysqli_free_result($RsMatiere);
mysqli_free_result($Rsgroupe);			
mysqli_free_result($Rsgic);
mysqli_free_result($Rs_cloture);

if (isset($Rsprof)){mysqli_free_result($Rsprof);};
?>
