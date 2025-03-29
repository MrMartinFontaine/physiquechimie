<?php 
//if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { exit();header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 
// revoir  groupe="" & groupe='Classe entière'
// ######### REVOIR in_classe_gic vers 287    pour 929  (HSUP DE GIC)
// avec plage  horaire de saisie capée,  neutralisable ici:
$full=true; 


// if ((isset($_GET['full']))&&( $_GET['full']=="full")) { $_SESSION['full']=true;} ;
// if (isset($_SESSION['full']) && $_SESSION['full']==true) {$full=true ;} else {$full=false;};
//on faisait substr(code_date,0,8) dans 4 rquetes et surtout  4*8*30 requetes  substr(code_date,1,8 ) par ecran 
//  dans les fiches ele_absent,  code_date devient date +Ds  dans  inc.module_abscence.php
// heure passe en heure pour plage+ l'heure de saisie et le jour au dela du 9eme car 
 // debug 594

//include "../authentification/authcheck.php" ;
include "../authentification/authcheck.php" ;
include "./carnets_inc.php";
require_once('../inc/module_absence_couleur.php');
// require_once('../inc/functions_inc.php');



$table_jours = array('Dim','Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam');
//nb de colonne maxi en affichage standard 
$maxcol=6;
$sql_affiche='';
$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);}

//verrouille la saisie si autre jour et autre plage horaire !
if (substr($_GET['heure_debut'],1,1)=='h'){$ch_heure_debut='0'.$_GET['heure_debut'];} else {$ch_heure_debut= $_GET['heure_debut']  ;}; //9h00 devient 09h00
$debut_plage= mktime( substr($ch_heure_debut,0,2),substr($ch_heure_debut,3,2),0, substr($_GET['date'],4,2) , substr($_GET['date'],6,2) , substr($_GET['date'],0,4) );

if (substr($_GET['heure_fin'],1,1)=='h'){$ch_heure_fin='0'.$_GET['heure_fin'];} else {$ch_heure_fin= $_GET['heure_fin']  ;}; //9h00 devient 09h00
$fin_plage= mktime(substr($ch_heure_fin,0,2),substr($ch_heure_fin,3,2),0, substr($_GET['date'],4,2) , substr($_GET['date'],6,2) , substr($_GET['date'],0,4));

// $encours=time(); tolerance 1/4d'heure av et 1/2h apres
$saisie_start= (time()- $debut_plage) > -900;
$saisie_stop= (time()- $fin_plage) > 1800 ;
$saisie_oui= ($saisie_start && !$saisie_stop);

if ($full== true) { $saisie_start=true;$saisie_stop=false;$saisie_oui=true;}; // neutralisation 
$jourtoday= jour_semaine(date('d/m/Y'));


$index_jour= date('w', mktime(0, 0, 0, substr($_GET['date'],4,2) , substr($_GET['date'],6,2) , substr($_GET['date'],0,4)) ) ;
$nom_jour=$table_jours[$index_jour];
$maxcol=20;	 //llimite de securite ? 
if ( isset($_GET['type_affiche'])) { $type_affiche=$_GET['type_affiche'];} else { $type_affiche=2;};
if ($type_affiche==1){//affiche uniquement la colonne de saisie
	
	$sql_affiche = 'AND heure_debut="'.$_GET['heure_debut']. '"' ;
};
if ($type_affiche==2){ //affiche le début de journée
	$sql_affiche = 'AND heure_debut<="'.$_GET['heure_debut']. '"' ;
};
if ($type_affiche==3){	//afficher toute la journée
	$maxcol=30;
}


$nbElevesCoches=-1; // pour controle saisie valide
//enregistrement pour  Envoi en vie scolaire
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") && ($saisie_oui )) { // enregistrements
 //apres vslidation ,  on basculera en affichage plaage 
	$sql_affiche = 'AND heure_debut="'.$_GET['heure_debut']. '"' ; $_GET['type_affiche']=1;
	

	//A revoir pour les cours partages
	if (isset($_POST['createur_heure_part_ID'])){$prof_declarant=$_POST['createur_heure_part_ID'];} else {$prof_declarant=$_SESSION['ID_prof'];};
	

	
// on vide les absents et autres incidents !
	$deleteSQL = sprintf("DELETE FROM ele_absent WHERE classe_ID=%u  AND prof_ID=%u AND heure=%s AND heure_debut=%s AND date=%s ",
		GetSQLValueString($_GET['classe_ID'], "int"),
		//   GetSQLValueString($_GET['groupe'], "text"), // AND groupe=%s
		GetSQLValueString($prof_declarant, "int"),
		GetSQLValueString($_GET['heure'], "text"),
		GetSQLValueString($_GET['heure_debut'], "text"),
		GetSQLValueString($_GET['date'], "text")
		);mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL ) or die(mysqli_error($conn_cahier_de_texte));
	
	$req = "SELECT min(ID_ele) AS min, max(ID_ele) AS max FROM ele_liste;"; 
	$res = mysqli_query($conn_cahier_de_texte, $req ) or die(mysqli_error($conn_cahier_de_texte)); 
	$row = mysqli_fetch_assoc($res); 
	$nblign=(int) $row['max'];
	$nb_insert=0;

$nbElevesCoches=0;


for ($i=(int) $row['min']; $i<=$nblign; $i++){//fin boucle enregistrements pour eleves faisant objet de signalement
		$absent='N';$retard_V ='N';$retard_Nv = 'N';$motif = 0 ;$pbCarnet='N';$details="";$statutVs ='N';
		$unElvAb='Ab_'.$i; 		if ( isset($_POST[$unElvAb]) && $_POST[$unElvAb] =='Y') {$absent = 'Y';} ;
		$unElvRv='Rv_'.$i ; 	if (isset($_POST[$unElvRv]) && $_POST[$unElvRv] =='Y')	{ $retard_V = 'Y';}
		$unElvRnv='Nv_'.$i; 	if (isset($_POST[$unElvRnv])&& $_POST[$unElvRnv] =='Y') { $retard_Nv = 'Y';} ; 
		$unElvMotif='Mo_'.$i;	if ( isset($_POST[$unElvMotif])&& $_POST[$unElvMotif] >0){ $motif = $_POST[$unElvMotif];} ; 	
		$unElvCarnet='Ca_'.$i; 	if ( isset($_POST[$unElvCarnet]) && $_POST[$unElvCarnet] =='Y')	{ $pbCarnet= 'Y';} ; 
		$unElvDetail='De_'.$i; 	if ( isset($_POST[$unElvDetail]) && strlen($_POST[$unElvDetail])>0 )	{ $details = $_POST[$unElvDetail];}; 
		$unElvStatut='St_'.$i ; if (isset($_POST[$unElvStatut])&& $_POST[$unElvStatut] =='Y')	{$statutVs = 'Y';};
		$unElvAnnule='An_'.$i ; if (isset($_POST[$unElvAnnule])&& strlen($_POST[$unElvAnnule])>1 ) 
																	{ $annule = $_POST[$unElvannule];}else {$annule='N';};
		
		if ( $absent =='Y'   || $retard_V =='Y'  || $retard_Nv=='Y'|| $pbCarnet =='Y' ||  $motif > 0 || strlen($details) >2 || $statutVs== 'Y'||$annule == 'Y')// il y a un even !  
			{	$nbElevesCoches++;
				// mise en forme des donnees à enregistrer _val
				if ( (intval($motif)>0) && (intval($motif)<6 ) && $retard_V=="N") {$retard_Nv ='Y';}; // val par defaut si item retard
				if (($retard_V == 'Y') && ($retard_Nv == 'Y')) { $retard_V = 'N';}; //priorité au Nv !
				if ( strlen($details) <3) {$details='';}; // elimine les frappes parasites de 1 ou 2 car
				if ($absent == 'Y') { $retard_V = 'N'; $retard_Nv = 'N';$motif=0; $details='';$pbCarnet ='N';};
				
				$nom_classe = "";
				if ( $_GET['gic_ID'] != "0" ) 
				{	
					$nameClasseSQL = sprintf("SELECT ID_classe, nom_classe FROM ele_liste,cdt_classe WHERE ele_liste.classe_ele COLLATE latin1_swedish_ci=cdt_classe.code_classe COLLATE latin1_swedish_ci AND ele_liste.ID_ele=%u LIMIT 1;", 
						GetSQLValueString($i, "int") 
						);mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

					$nameClasseResult = mysqli_query($conn_cahier_de_texte, $nameClasseSQL ) or die(mysqli_error($conn_cahier_de_texte));
					$row_nameClasseResult = mysqli_fetch_assoc($nameClasseResult);
					$nom_classe = "Regroupement";
				} 
				else {	$nom_classe = $_GET['nom_classe'] ;}
			
				$code_date=$_GET['date'].$_GET['heure']; //champ code_date pour avoir les absents avec le module simplifie
				
				$insertSQL = sprintf("INSERT INTO `ele_absent` (classe_ID,classe,groupe,heure,     heure_debut,heure_fin,date,Ds,    eleve_ID,prof_ID,       heure_saisie,absent,retard_V,retard_Nv,motif,   pbCarnet, details,vie_sco_statut,annule,code_date) 
				VALUES (%u,%s,%s,%s,  %s,%s,%s,%u,  %u,%u,   %s,%s,%s,%s,%u,  %s,%s,%s,%s,%s)", 
					
					GetSQLValueString($_GET['classe_ID'], "int"),
					GetSQLValueString($nom_classe, "text"),
					GetSQLValueString($_GET['groupe'], "text"),
					GetSQLValueString( $_GET['heure'], "text"),
					
					GetSQLValueString($_GET['heure_debut'], "text"),
					GetSQLValueString($_GET['heure_fin'], "text"),
					GetSQLValueString($_GET['date'], "text"),
					GetSQLValueString($_GET['Ds'], "text"),
					
					GetSQLValueString($i, "int"),
					GetSQLValueString($prof_declarant, "int"),
					
					
					GetSQLValueString(date("Hi").$nom_jour, "text"),
					GetSQLValueString($absent, "text"),
					GetSQLValueString($retard_V, "text"),
					GetSQLValueString($retard_Nv, "text"),
					GetSQLValueString($motif, "int"),
					
					GetSQLValueString($pbCarnet, "text"),
					GetSQLValueString($details,"text"),			
					// $absent.'/'.$retard_V.'/'.$retard_Nv.'/'.$motif.'/'.$pbCarnert, "text") 

					GetSQLValueString($statutVs, "text"),
					GetSQLValueString($annule, "text"),
					//on insere le champ code_date de maniere a avoir les absents avec le module simplifie
					//code_date=date+heure
					GetSQLValueString($code_date, "text")
					
					);mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

				$Result = mysqli_query($conn_cahier_de_texte, $insertSQL ) or die(mysqli_error($conn_cahier_de_texte));
			//if (($unPbCarnet_val!='N')&&($unRetard_Nv_val=='N')&&($p<1) ) {$nb_insert=$nb_insert+1;};
				
			
		}
	} 
	// fin boucle enregistrements pour eleves faisant objet de signalement repérés par les post[id] reçus
	mysqli_free_result($res);
	
	if ((isset($_POST['pasdabsent']))&&($_POST['pasdabsent']=='on')){ //fiche [pas d'absent]
		// on cree un enregistrement pour un eleve fictif avec elev_id=0 SEULEMNT SI PAS  D'INCIDENTS ???? 
		$nbElevesCoches++;
		if ($_GET['classe_ID']==0){$nom_classe = "Regroupement"; } else { $nom_classe = $_GET['nom_classe'] ; }
		$insertSQL = sprintf("INSERT INTO `ele_absent` (classe_ID,classe,groupe,heure,    heure_debut,heure_fin,date,Ds, eleve_ID,prof_ID, heure_saisie) VALUES (%u,%s,%s,%s,  %s,%s,%s,%s  ,%u,%u,%s)",
			
			GetSQLValueString($_GET['classe_ID'], "int"),
			GetSQLValueString($nom_classe, "text"),
			GetSQLValueString($_GET['groupe'], "text"),
			GetSQLValueString( $_GET['heure'], "text"),
			
			GetSQLValueString($_GET['heure_debut'], "text"),
			GetSQLValueString($_GET['heure_fin'], "text"),
			GetSQLValueString($_GET['date'].date("Hi"), "text"),
			GetSQLValueString($_GET['Ds'], "text"),
			
			0,
			GetSQLValueString($prof_declarant, "int"),
			GetSQLValueString(date("Hi").$nom_jour, "text") 
			);mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

		$Result = mysqli_query($conn_cahier_de_texte, $insertSQL ) or die(mysqli_error($conn_cahier_de_texte));
	};
																	// fin fiche [ pas d'absent]
	
	
	if ((isset($_POST['sms_vie_sco']))&&($_POST['sms_vie_sco']<>'')&&($_POST['sms_vie_sco']<>'Rédiger un message pour la vie scolaire...')){//enregistrement sms vie scolaire
		$classe_ID=$_GET['classe_ID'];
		$groupe_ID=$_POST['gic_ID'];
		$insertSQL = sprintf("INSERT INTO cdt_message_contenu (message, prof_ID, date_envoi, date_fin_publier,online,dest_ID,pp_classe_ID,pp_groupe_ID)
			VALUES (%s,%u,NOW(),'0000-00-00','O',3,%u,%u)",
			GetSQLValueString($_POST['sms_vie_sco'], "text"),
			GetSQLValueString($_SESSION['ID_prof'], "int"),
			GetSQLValueString($classe_ID, "int"),
			GetSQLValueString($groupe_ID, "int")
			);mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

		$Result = mysqli_query($conn_cahier_de_texte, $insertSQL ) or die(mysqli_error($conn_cahier_de_texte));
	}
							//fin enregistrement sms vie scolaire
} 
																						// fin enregistrements


if ( $_SESSION['semdate'] == "A" ) { //Gestion des semaines A et B
	$semdate_exclusion = "B";
} else if ( $_SESSION['semdate'] == "B" ) {
	$semdate_exclusion = "A";
} else {
	$semdate_exclusion = NULL;
}
									// fin Gestion des semaines A et B

if ($_GET['classe_ID'] != "0" ) {  // extractions plages horaires "Classes" à afficher
require_once('../Connections/conn_cahier_de_texte.php'); 

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

	$query_Rslibelle = sprintf("SELECT code_classe 
	FROM cdt_classe 
	WHERE ID_Classe = %u", 
	GetSQLValueString($_GET['classe_ID'], "int") );
	$Rslibelle = mysqli_query($conn_cahier_de_texte, $query_Rslibelle ) or die(mysqli_error($conn_cahier_de_texte));
	$row_libelle = mysqli_fetch_assoc($Rslibelle);
	
if ($_GET['groupe']=='Classe entière' || $_GET['groupe']==''){ // selection tous les eleves
		$query_liste_d_appel = sprintf("SELECT ID_ele ,nom_ele,prenom_ele FROM ele_liste 
		WHERE classe_ele COLLATE latin1_swedish_ci='%s' COLLATE latin1_swedish_ci 
		ORDER BY nom_ele",$row_libelle['code_classe']);
	} else {
		//avec la gestion de groupes
		// selection des eleves du groupe dans ele liste
		$query_liste_d_appel = sprintf("SELECT ID_ele ,nom_ele,prenom_ele	FROM ele_liste 
		WHERE groupe_ele COLLATE latin1_swedish_ci='%s' COLLATE latin1_swedish_ci 
		AND classe_ele COLLATE latin1_swedish_ci='%s' COLLATE latin1_swedish_ci 
		ORDER BY nom_ele",$_GET['groupe'],$row_libelle['code_classe']);mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

		$liste_d_appel = mysqli_query($conn_cahier_de_texte, $query_liste_d_appel ) or die(mysqli_error($conn_cahier_de_texte));
		$fiche_liste_d_appel = mysqli_fetch_assoc($liste_d_appel);
		$nb_fiche_liste_d_appel = mysqli_num_rows($liste_d_appel);
		
		if ((isset($nb_fiche_liste_d_appel))&&($nb_fiche_liste_d_appel==0)){//extraction classe ou groupe- 
			$query_liste_d_appel = sprintf("SELECT ID_ele ,nom_ele,prenom_ele	FROM ele_liste 
			WHERE classe_ele COLLATE latin1_swedish_ci='%s' COLLATE latin1_swedish_ci 
			ORDER BY nom_ele",$row_libelle['code_classe']);
		} else {
			$query_liste_d_appel = sprintf("SELECT ID_ele ,nom_ele,prenom_ele	FROM ele_liste 
			WHERE groupe_ele COLLATE latin1_swedish_ci='%s' COLLATE latin1_swedish_ci 
			AND classe_ele COLLATE latin1_swedish_ci='%s' COLLATE latin1_swedish_ci 
			ORDER BY nom_ele",$_GET['groupe'],$row_libelle['code_classe']);
		};
																	//fin//extraction classe ou groupe-
	};									
	// fin selection eleves
	
	mysqli_free_result($Rslibelle);mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

	$liste_d_appel = mysqli_query($conn_cahier_de_texte, $query_liste_d_appel ) or die(mysqli_error($conn_cahier_de_texte));
	$fiche_liste_d_appel = mysqli_fetch_assoc($liste_d_appel);
	$nb_fiche_liste_d_appel = mysqli_num_rows($liste_d_appel);
	
if (!is_null($semdate_exclusion) ) { // gestion semaines exclues
		$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_prof 
		WHERE ID_prof=prof_ID 
		AND cdt_emploi_du_temps.classe_ID=%u 
		AND cdt_emploi_du_temps.jour_semaine='%s' 
		AND '%s'<= edt_exist_fin 
		AND semaine!='%s' %s 
		ORDER BY cdt_emploi_du_temps.heure_debut", 
		$_GET['classe_ID'],$_GET['current_day_name'],date('Y-m-d') ,$semdate_exclusion,$sql_affiche );
	} else {
		$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_prof 
		WHERE ID_prof=prof_ID 
		AND cdt_emploi_du_temps.classe_ID=%u 
		AND cdt_emploi_du_temps.jour_semaine='%s' 
		AND '%s'<= edt_exist_fin %s 
		ORDER BY cdt_emploi_du_temps.heure_debut",
		$_GET['classe_ID'],$_GET['current_day_name'],date('Y-m-d'),$sql_affiche);
	}
										// fin gestion semaines exclues	
	
} elseif ( $_GET['gic_ID'] != "0" ) {	
							// fin //extractions plages horaires "Classes" à afficher

		// Extractions Regroupement inter classe (gic) à affichermysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);	
	$query_Rsclasses_gic = sprintf("SELECT * FROM cdt_groupe_interclasses_classe 
	WHERE gic_ID='%u'", 
	GetSQLValueString($_GET['gic_ID'], "int"));
	$Rsclasses_gic = mysqli_query($conn_cahier_de_texte, $query_Rsclasses_gic ) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsclasses_gic = mysqli_fetch_assoc($Rsclasses_gic);
	
	$classe_gic = array();
	array_push( $classe_gic , 0 );
	@mysqli_data_seek($Rsclasses_gic,0) ;
	while (($row_rq = mysqli_fetch_array($Rsclasses_gic , MYSQLI_ASSOC) )) {  
		array_push( $classe_gic , $row_rq['classe_ID'] );
	}
	$in_classe_gic = join(" ,", $classe_gic) ;
	mysqli_free_result($Rsclasses_gic);
	
	//supprimer le "0,"
	$in_classe_gic=substr($in_classe_gic,3,strlen($in_classe_gic));
	
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);	
	$query_liste_d_appel_gic = sprintf("SELECT * FROM ele_gic WHERE ele_gic.ID_gic='%u'", GetSQLValueString($_GET['gic_ID'], "int"));
	$liste_d_appel_gic = mysqli_query($conn_cahier_de_texte, $query_liste_d_appel_gic ) or die(mysqli_error($conn_cahier_de_texte));
	$fiche_liste_d_appel_gic = mysqli_fetch_assoc($liste_d_appel_gic);
	

	$ele_gic = array();
	if ( @mysqli_data_seek($liste_d_appel_gic,0) ) { 
		while (($row_rq = mysqli_fetch_array($liste_d_appel_gic , MYSQLI_ASSOC) )) {  
			array_push( $ele_gic , $row_rq['ID_ele'] );
		}
		$in_ele_gic = join(" ,", $ele_gic) ;
	} else {
		die('D&eacute;finissez les &eacute;l&egrave;ves du regroupement. ');
	};
	mysqli_free_result($liste_d_appel_gic);
	
	// listes des eleves
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_liste_d_appel = sprintf("SELECT * FROM ele_liste WHERE ele_liste.ID_ele IN ( %s ) ORDER BY nom_ele",$in_ele_gic);
	$liste_d_appel = mysqli_query($conn_cahier_de_texte, $query_liste_d_appel ) or die(mysqli_error($conn_cahier_de_texte));
	$fiche_liste_d_appel = mysqli_fetch_assoc($liste_d_appel);
	$nb_fiche_liste_d_appel = mysqli_num_rows($liste_d_appel);
	
	if (!is_null($semdate_exclusion) ) {// recherche des plages [heures de cours 'normales'] dans les edt profs 
		
		$query_Rs_emploi = sprintf("SELECT prof_ID,heure,heure_debut,heure_fin,groupe,identite ,ID_emploi,ID_prof,classe_ID
		FROM cdt_emploi_du_temps,cdt_prof 
		WHERE ID_prof=prof_ID 
		AND (cdt_emploi_du_temps.classe_ID IN (%s) OR (cdt_emploi_du_temps.classe_ID =0 
		AND cdt_emploi_du_temps.gic_ID=%u)) 
		AND cdt_emploi_du_temps.jour_semaine='%s' 
		AND '%s'<= edt_exist_fin AND semaine!='%s' %s 
		ORDER BY cdt_emploi_du_temps.heure_debut", 
		$in_classe_gic, GetSQLValueString($_GET['gic_ID'], "int") ,
		$_GET['current_day_name'] ,date('Y-m-d'),$semdate_exclusion,$sql_affiche );
	} else {
		
		$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_prof WHERE ID_prof=prof_ID AND (cdt_emploi_du_temps.classe_ID IN (%s) OR (cdt_emploi_du_temps.classe_ID =0 AND cdt_emploi_du_temps.gic_ID=%u)) AND 
			cdt_emploi_du_temps.jour_semaine='%s' AND '%s'<= edt_exist_fin %s ORDER BY cdt_emploi_du_temps.heure_debut", $in_classe_gic, GetSQLValueString($_GET['gic_ID'], "int"),$_GET['current_day_name'],date('Y-m-d'),$sql_affiche);
	}
}; ?>
<?php
//echo $query_Rs_emploi;mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Rs_emploi = mysqli_query($conn_cahier_de_texte, $query_Rs_emploi ) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi);
$totalRows_Rs_emploi = mysqli_num_rows($Rs_emploi);
$a=1;$pos=0;
										// fin  // Extractions cours  calles ou Regroupement [gic] dans les edt profs 
?>

<?php if ($totalRows_Rs_emploi>0){ // il y a des plages [heures de cours 'normales'] à afficher
do { // extraction de toutes les plages horaires à afficher depuis Rs_emploi
    // stockage dans une collection de tables $numcol, $hdeb $hfin $gr $ipc
		if (($_GET['heure'] ==$row_Rs_emploi['heure'])&&($_GET['heure_debut']==$row_Rs_emploi['heure_debut'])&&($_SESSION['ID_prof']==$row_Rs_emploi['ID_prof'])){$pos=$a;};
		//on rajoute $plageH 
		$plageH[$a]=$row_Rs_emploi['heure'];  // dans cdt_emploi_du_temps, 'heure' est l'index de plage hor !
		if (strval($plageH[$a])==0) {$plageH[$a]=1;}; // heure inconnue,  on force l'affichage en debut de journée
		$hdeb[$a]=$row_Rs_emploi['heure_debut'];
		$hfin[$a]=$row_Rs_emploi['heure_fin'];
		$gr[$a]=$row_Rs_emploi['groupe']; // attention  "groupe" contient "classe entiere" ou...; pour nous n°salle !!!	
		
		$clid[$a]=$row_Rs_emploi['classe_ID']; //  Rajouté pour query decompte absents en 824 /848
		
				// cette heure est-elle partagee ?
				$colpart[$a]=0;mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsPart=sprintf("SELECT cdt_emploi_du_temps_partage.ID_emploi,
					cdt_emploi_du_temps_partage.profpartage_ID,
					cdt_emploi_du_temps.prof_ID 
				FROM cdt_emploi_du_temps_partage,cdt_emploi_du_temps 
				WHERE cdt_emploi_du_temps.ID_emploi=%u 
				AND cdt_emploi_du_temps_partage.ID_emploi=%u",
				$row_Rs_emploi['ID_emploi'],
				$row_Rs_emploi['ID_emploi']);
				
				$RsPart = mysqli_query($conn_cahier_de_texte, $query_RsPart ) or die(mysqli_error($conn_cahier_de_texte));
				$row_RsPart = mysqli_fetch_assoc($RsPart);
				$totalRows_RsPart = mysqli_num_rows($RsPart);

				
if ($totalRows_RsPart==0){	//id  du  prof concerné  idpc
				//heure non partagee
					$idpc[$a]=$row_Rs_emploi['ID_prof']; 
				}	else {
				//heure partagee
				$idpc[$a]=$row_RsPart['prof_ID'];
				$colpart[$a]=1;
				};
											// fin id  du  prof concerné  idpc
		
		//on determine pos : le numero de la colonne de pointage active,>>corrigée en 449 si ds ou hs
		
		if (($_GET['heure']==$row_Rs_emploi['heure'])&&($_GET['heure_debut']==$row_Rs_emploi['heure_debut'])&&($_GET['groupe']==$row_Rs_emploi['groupe'] ||$_GET['groupe']=='')&&($idpc[$a]==$row_Rs_emploi['ID_prof'])){
			$pos=$a;$ident[$a]=$_SESSION['identite'];
		} else { 
			$ident[$a]=$row_Rs_emploi['identite'];
		};
		$numcol[$a]=$a;
		$a=$a+1;
	} 	while ($row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi)) ;
	} 	// fin extraction de toutes les plages horaires à afficher 

	?>
<?php 						// fin // il y a des plages [heures de cours 'normales'] à afficher ?>

<?php if((isset($_GET['edt_modif']))&&($_GET['edt_modif']=='O')){ // heure modifiee ponctuellement
	$sql_edt_modif=sprintf(" UNION 
		SELECT *
		FROM cdt_agenda, cdt_prof
		WHERE substring( code_date, 1, 9 )=%s
		AND cdt_agenda.classe_ID=%u
		AND cdt_agenda.edt_modif='O'
		AND cdt_prof.ID_prof = cdt_agenda.prof_ID"
		,$_GET['date'].$_GET['Ds'] ,$_GET['classe_ID']);
	} else {
		$sql_edt_modif='';
		};
?>	
<?php														// fin  // heure modifiee ponctuellement ?>

<?php 							//recherche de Devoirs planifies ou Heures supplementaires  
	$query_RsDs = sprintf(" 
		SELECT *
		FROM cdt_agenda, cdt_prof
		WHERE substring( code_date, 9, 1 )=0
		AND substring( code_date, 1, 8 )=%s
	
		AND cdt_agenda.classe_ID =%u
		AND cdt_prof.ID_prof = cdt_agenda.prof_ID
		%s
		GROUP BY cdt_agenda.heure
		ORDER BY heure_debut"
		,$_GET['date'],$_GET['classe_ID'],$sql_edt_modif);

	$RsDs = mysqli_query($conn_cahier_de_texte, $query_RsDs ) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsDs = mysqli_fetch_assoc($RsDs);
	$totalRows_RsDs = mysqli_num_rows($RsDs);?>
<?php						// fin  //recherche de Devoirs planifies ou Heures supplementaires ?>
						
<?php if ($totalRows_RsDs>0){ 			// on complete la table avec les HS et DS
	do { // pour chaque devoir ou hs
		$hdeb[$a]=$row_RsDs['heure_debut'];
		$hfin[$a]=$row_RsDs['heure_fin'];
		$gr[$a]=$row_RsDs['groupe']; // "classe entiere" remplacé par num salle !!!
		$ident[$a]=$row_RsDs['identite'];
		$idpc[$a]=$row_RsDs['ID_prof'];
		$gicid[$a]=$row_RsDs['gic_ID'];
		$typac[$a]=$row_RsDs['type_activ'];
		$numcol[$a]=$a;
		
		$clid[$a]=$row_RsDs['classe_ID']; // Rajouté pour query decompte absents en 824 /848
	
//if (($_GET['Ds']==$row_RsDs['heure'])&&($_GET['heure_debut']==$row_RsDs['heure_debut'])&&($_GET['groupe']==$row_RsDs['groupe'] ||$_GET['groupe']=='')&&($idpc[$a]==$row_RsDs['ID_prof'])){	$pos=$a; };	

if (($_GET['heure_debut']==$row_RsDs['heure_debut'])&&($_GET['groupe']==$row_RsDs['groupe'] ||$_GET['groupe']=='')&&($idpc[$a]==$row_RsDs['ID_prof'])){	$pos=$a; };	
		$a=$a+1;
	}	while ($row_RsDs = mysqli_fetch_assoc($RsDs));	
	// fin pour chaque devoir ou hs
};

mysqli_free_result($RsDs);?>
<?php 							//fin // on complete la table avec les HS et DS  ?>

<?php 							//fin extractions de toutes les plages horaires à afficher 
//calcul de date_jprec: si lundi, on pointe sur le vendredi precedent et non la veille
		$mktime_date=mktime(0, 0, 0, substr($_GET['date'],4,2) , substr($_GET['date'],6,2) , substr($_GET['date'],0,4));
		
		$date_form=date('d/m/Y',$mktime_date ); // pour envoi vers attituds_elv !
			$table_jours = array('Dim','Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam');
			$index_jour= date('w',$mktime_date  )  ;
		$jour=$table_jours[$index_jour];	
		
		// calcul du jour prec : -1 sauf si on est le lundi
		if (date('w',$mktime_date)==1){$decal=3;}else{$decal=1;};
		$time_jprec = mktime(0, 0, 0, substr($_GET['date'],4,2) , substr($_GET['date'],6,2) - $decal, substr($_GET['date'],0,4));
		$date_jprec=date('Ymd',$time_jprec);
// calcul de date_jMoinsX pour suivi signatures
		$jMoinsX=14; // pour ceux qui ne voient les elv que 1fois/sem ou quinzaine....
		$time_jMoinsX = mktime(0, 0, 0, substr($_GET['date'],4,2) , substr($_GET['date'],6,2)- $jMoinsX, substr($_GET['date'],0,4));
		$date_jMoinsX=date('Ymd',$time_jMoinsX);
		
		//  si on sort de vac  [dans agenda classe_id=0, heure_deb est date de deb en jj/mm/aaaa !mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

		$query_Vacances = sprintf("SELECT heure_debut, heure_fin FROM cdt_agenda 
				WHERE cdt_agenda.classe_ID=0  
					and concat(substr(heure_debut,0,2),substr(heure_debut,3,2),substr(heure_debut,6,4))<%s 
					and concat(substr(heure_fin,0,2),substr(heure_fin,3,2),substr(heure_fin,6,4))<%s ",
				$date_jMoinsX,$date_jMoinsX );
		$Vacances = mysqli_query($conn_cahier_de_texte, $query_Vacances ) or die(mysqli_error($conn_cahier_de_texte));
		$un_Vacances = mysqli_fetch_assoc($Vacances);
		$totalRows_Vacances = mysqli_num_rows($Vacances);
		if ($totalRows_Vacances > 0) {
					$jMoinsX=28;
					$time_jMoinsX = mktime(0, 0, 0, substr($_GET['date'],4,2) , substr($_GET['date'],6,2) - $jMoinsX, substr($_GET['date'],0,4));
					$date_jMoinsX=date('Ymd',$time_jMoinsX);
		};
		mysqli_free_result($Vacances);
		
		
		

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title style="background-color:green;"> &nbsp;  &nbsp; Appel en <?php echo $_GET['nom_classe'];?> &nbsp;  &nbsp; (<?php echo $_GET['heure_debut'].' -'.$_GET['heure_fin'] ;?>) </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../styles/style_default.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../jscripts/utils.js"></script>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<style>
form{
	margin:5;
	padding:0;
}
.rouge {color:#FF0000}
.selected td {
	background-color: #48507B;
	color: #FFFFFF;
}
.tab_detail_gris_fonce {	border: 0px solid #CCCCCC;padding :4px ;	}
.tab_detail_gris_clair {	border: 0px solid #CCCCCC;padding :4px ;}

td {  padding-top:2px;
}	
	
input [ disabled] {
   color: red;
}

	
</style>

<script type="text/JavaScript">
function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
};

function absent(IDabsent) {
	document.getElementById('Rv_'+IDabsent).checked=false; // retard val
	// document.getElementById('A_'+IDabsent).checked=false; // pas de carnet= ^pbCarnet
	document.getElementById('Nv_'+IDabsent).checked=false; // retard non val =retard_Nv
	document.getElementById('pasdabsent').checked=false;
};


$(document).ready(function() {
		$('#rowclick5 tr ')			// Selection des lignes des absents...
		.filter(':has(:checkbox:checked)')	// .. contenant des checkbox cochees...
		.addClass('selected')			// .. et coloration de ces lignes...
		.end()					//  ----- break -----
		
		.click(function(event) {
				var Ligne=$(this);
				if ((event.target.type !== 'checkbox')&&(event.target.type !== 'text')){
					var BoxAbsent=$(':checkbox:first', this);
					BoxAbsent.attr('', function() {
							BoxAbsent.attr('',!(BoxAbsent.attr('checked')));
							absent(BoxAbsent.attr('id'));
							if (BoxAbsent.attr('checked')) {Ligne.addClass("selected")} else {Ligne.removeClass("selected")};
					}
					);
				} else if ((event.target.type == 'checkbox')) {
					var cases =$(':checkbox:checked', this);
					if(cases.attr('id')==undefined) {Ligne.removeClass("selected")} else {Ligne.addClass("selected")};
				};
		});
		
		$('#pasdabsent').click(function() { 
				var cases = $("#rowclick5").find(':checkbox');
				var ligne = $("#rowclick5");
				
				if(this.checked){
					for(var i=0;i<cases.length/5;i++) {
						if (!(isNaN(cases[5*i].id))) {
							cases[5*i].checked=false;
							var CasesChecked=false;
							for (var j=1;j<5;j++){
								CasesChecked = CasesChecked || cases[5*i+j].checked;
							};
							if (!CasesChecked) {$('#ligne'+i).removeClass('selected');};
						}
					}
				}
		});
		
});
$( function() {
  $('select').click( function() {
    $(this).css('background', '#FAF79B')
	 $(this).css('color', '#000')
  } );
} );

$( function() {
  $('textarea').click( function() {
    $(this).css('background', '#FAF79B')
	 $(this).css('color', '#000')
  } );
} );


var ima_bt= new Image();
function flip(n)
{ for (var i=0 ;i<=2;i++)
    if (i==n) document.images[i].src=ima_bt.src; //image préchargée..
        else document.images[i].src="valider_appel_off.png";
 }
function pre_ch(n)
{ ima_bt.src="../images/valider_appel_on.png"; } // préchargement de l'image..

</script>
</head>
<body style="background-color: #DEDEDE;">

<div id="container" style=" min-width:600px;background-color: #FAF6EF; min-height:700px;border: none;"> 

<table class="lire_bordure" border="0" align="center" cellpadding="0" cellspacing="0" width="100%">
<tr class="lire_cellule_4">
<td align="center" valign="middle"> D&eacute;claration des absences et incidents en classe de
<?php 
if (isset($_GET['gic_ID'])&&(isset($_GET['classe_ID']))&&($_GET['classe_ID']==0)) {// affichage nom classe ou gicmysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

	$query_Rsnom_gic = sprintf("SELECT * 
	FROM cdt_groupe_interclasses 
	WHERE ID_gic='%u' LIMIT 1", 
	GetSQLValueString($_GET['gic_ID'], "int"));
	
	$Rsnom_gic = mysqli_query($conn_cahier_de_texte, $query_Rsnom_gic ) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsnom_gic = mysqli_fetch_assoc($Rsnom_gic);
	echo $row_Rsnom_gic['nom_gic'];
	mysqli_free_result($Rsnom_gic);
} else {
echo '<font style="background-color:PowderBlue;color:black;"> &nbsp; '.$_GET['nom_classe'].'&nbsp; </font>';
};
																				// fin affichage nom classe ou gic

echo '&nbsp;'.$_GET['groupe'];
if ( isset($_GET['jour_pointe'] ) ){ echo ' '.$_GET['jour_pointe'];};?>
</td>
<td style="width:220px;padding-top:0px;"><?php
 if ((isset($_SESSION['mobile_browser']))&&($_SESSION['mobile_browser']==false)){
 
 //require_once ("../authentification/sessionVerif4.php");
 require_once "../authentification/authcheck.php" ;
 
 };
 ?><div align="right"><img src="../images/cancel.png" alt="Fermer la page" width="40" height="16" border="0" title="Fermer la page" onClick="window.close()" /></div></td>
</tr>
</table>
<?php if ($nb_fiche_liste_d_appel==0){ // message si pas de gestion des absences?>
	<p><strong><br /><br />
	La gestion des absences n'a pas &eacute;t&eacute; encore mise en place pour cette classe.<br />
	<br /></strong></p>
<?php } ;?>
<?php							// fin message si pas de gestion des absences

if ($nb_fiche_liste_d_appel > 0){ //  Il y a des eleves : display  >> fin l1076
	
	if(isset($_GET['duree'])){$duree=$_GET['duree'];} else{$duree='';};
	$lien_post='appel.php?nom_classe='.$_GET['nom_classe'].'&classe_ID='.$_GET['classe_ID'].'&gic_ID='.$_GET['gic_ID'].'&nom_matiere='.$_GET['nom_matiere'].'&groupe='.$_GET['groupe'].'&matiere_ID='.$_GET['matiere_ID'].'&semaine='.$_GET['semaine'].'&Ds='.$_GET['Ds'] ;
	$lien_post.='&heure='.$_GET['heure'].'&duree='.$duree.'&heure_debut='.$_GET['heure_debut'].'&heure_fin='.$_GET['heure_fin'].'&date='.$_GET['date'].'&current_day_name='.$_GET['current_day_name'];
	if (isset($_GET['edt_modif'])){$lien_post.='&edt_modif='.$_GET['edt_modif'];}else{$lien_post.='&edt_modif=N';}
	?><form method="POST" name="form1" id="form1" action="<?php echo $lien_post;?>" >
	<table width="700" border="0" align="center">
	<tr>
	<td width="350" >
	<div align="left">
	<?php $styleInput='';$stylePushed=' style="border:2px solid aquamarine; border-radius: 6px; box-shadow: 2px 2px 0px gray;" ';
	if ($type_affiche==2) {$styleInput=$stylePushed;} else {$styleInput='';}; ?>
	<input type="button" <?php echo $styleInput?> value="D&eacute;but  de journ&eacute;e" onclick="MM_goToURL('parent','<?php echo $lien_post.'&type_affiche=2&pos='.$pos;?>');return document.MM_returnValue"> &nbsp; <?php ; 
	if ($type_affiche==1) {$styleInput=$stylePushed;} else {$styleInput='';}; ?>
	<input type="button" <?php echo $styleInput?> value=" S&eacute;ance " onclick="MM_goToURL('parent','<?php echo $lien_post.'&type_affiche=1';?>');return document.MM_returnValue"> &nbsp;<?php ; 
	if ($type_affiche==3) {$styleInput=$stylePushed;} else {$styleInput='';}; ?>
	<input type="button" <?php echo $styleInput?> value="Toute la journ&eacute;e" onclick="MM_goToURL('parent','<?php echo $lien_post.'&type_affiche=3';?>');return -document.MM_returnValue">
	</td><td width="120" ></td><td align="center" style="padding-top:4px;font-size:12px;">
	<?php mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

			if ( $_GET['gic_ID'] != "0" ) {// On recupere l'heure de saisie prec de cet appel
				
				/*
				$query_RsEventSaisis  = sprintf("SELECT date,heure_saisie 
				FROM ele_absent 
				WHERE date = '%s' 
				AND classe_ID IN (%s) 
				AND ( (eleve_ID IN (%s) ) OR (eleve_ID=0)) 
				AND heure_debut='%s' AND prof_ID=%u ",
				$_GET['date'],$in_classe_gic,$in_ele_gic ,$hdeb[$pos],$idpc[$pos]);
				*/
				
				$query_RsEventSaisis  = sprintf("SELECT date,heure_saisie 
				FROM ele_absent 
				WHERE date = '%s' 
				AND classe_ID =0 
				AND heure_debut='%s' AND prof_ID=%u ",
				$_GET['date'],$hdeb[$pos],$idpc[$pos]);
				
				
			} else {
				$query_RsEventSaisis  = sprintf("SELECT date, heure_saisie FROM ele_absent 
				WHERE date = '%s' AND classe_ID=%u AND  heure_debut='%s' AND prof_ID=%u ",
				
				$_GET['date'],
				$_GET['classe_ID'],
				$hdeb[$pos],
				$idpc[$pos]); 
			}
									// fin On recupere l'heure de saisie prec de cet appel
			$RsEventSaisis = mysqli_query($conn_cahier_de_texte,  $query_RsEventSaisis ) or die(mysqli_error($conn_cahier_de_texte));
			$row_RsEventSaisis= mysqli_fetch_assoc($RsEventSaisis);
			$Nb_RsEventSaisis = mysqli_num_rows($RsEventSaisis);
			//echo $query_RsEventSaisis;
?>			

<?php 
 if ($Nb_RsEventSaisis > 0)	{ 			// affichage Appel effectué /non effectué 
	 echo '<span style="border:2px solid #4b9b3e;padding:4px 10px;font-size:11px;"> Appel envoy&eacute;';
	 if ($saisie_oui==false){ echo  ' le '.substr($row_RsEventSaisis['date'],6,2).'/'.substr($row_RsEventSaisis['date'],4,2).'&nbsp;';};
	 echo ' &agrave;  '.substr($row_RsEventSaisis['heure_saisie'],0,2).'h '.substr($row_RsEventSaisis['heure_saisie'],2,2).' </span>';
	 }	else { 
			// if ($saisie_stop) { 
			if ( $nbElevesCoches < 0){ echo '<span style="border:2px solid red;padding: 2px 5px;"> Pas d\'appel effectu&eacute; </span>';}; 
			if (!$saisie_stop) {  if ( $nbElevesCoches == 0) { echo '<span style="background-color: yellow;font-size: 10pt;border:1px;"> Appel NON VALIDE ! <br>cochez &agrave; minima<br>[Aucun absent] !</span>';};
							  };
		};	
	mysqli_free_result($RsEventSaisis ); //fin affichage Appel effectué /non effectué 
	
	?> </td> </tr><tr>
	<td style="padding-top:6px;"><textarea name="sms_vie_sco" cols="1" rows="1" class="tab_detail_gris_clair"  style=" width: 330px;  max-width:330px; height:24px; border:1px solid gray; border-radius: 6px; background-color:#FFFFFF;" id="sms_vie_sco" type="text" onFocus="if(this.value='Rédiger un message pour la vie scolaire...') this.value='';" onBlur="if(this.value=='') this.value='Rédiger un message pour la vie scolaire...';" >Rédiger un message pour la vie scolaire...</textarea>
	</td> <td style="text-align:center;vertical-align:middle;padding-top:6px;"> 	<span style=" margin-left:10px; background-color:#D8D8D8 ;padding-top:0px 0px;border:0px solid black ; font-size:11px; ">
    <!--<IMG src="../images/cancel.png" alt="Fermer la page" width="40" height="16"  border="0" title="Fermer la page" onClick="window.close()"></span> -->	</td>
     <td style="padding-top:6px;text-align:center;vertical-align:bottom;font-size:11px;">
 	<?php 	if ($saisie_oui) {// message conseil
	?>
	<!-- 
	<input name="submit" type="image" style="padding:4px;" onClick="flip(0)" src="../images/valider_appel_off.png" alt="Valider et envoyer en vie scolaire" title="ne pas hésiter à transmettre plusieurs fois"> 
	-->
	<input name="submit" type="submit" style="padding:4px;"  value="Valider et envoyer en vie scolaire" alt="Valider et envoyer en vie scolaire" title="ne pas hésiter à transmettre plusieurs fois"> 
	
	<?php 
	} else {
			if( $saisie_stop){ echo ' - Appel cl&ocirc;tur&eacute; - <br><i>(l\'acc&egrave;s aux carnets reste possible) </i>';
			} else { 
			echo ' - P&eacute;riode &agrave; venir ... - ';
			};
	};?>
	<?php 					//fin  message conseil?>
	</td></tr>	</table> 
	 
	<table class="lire_bordure"  bordercolor="Gray" align="center" style="border:1px solid gray;" cellpadding="0" cellspacing="0"  >
	<tbody style="border:1px solid black; " >
	<tr> 
	<td class="tab_detail_bleu" style="border-left:1px solid black ;border-top:1px solid black;" > <div style="margin-top:20px; background-color: #C2F8DA; text-align:center; width:120px; font-size:11px;" ><?php
	echo $nb_fiche_liste_d_appel?> &eacute;l&egrave;ves </div></td>
	<td class="tab_detail_bleu" style="padding:0px; border-top:1px solid black;" ><div style="width:30px;float:left;margin:0px;margin-top:4px;">Jour<img src="../images/user_absent1.png" width="16" height="16" style="margin-top:8px;" title="Absent le  jour précédent"> </div> </td>
	<td class="tab_detail_bleu" style="padding:0px 3px;border-top:1px solid black;" ><div style="width:30px;float:left;margin:0px;margin-top:4px;">pr&eacute;c.<img src="../images/carnet2.png" width="16" height="16" style="margin-top:8px;" title="Pas de carnet le jour précédent"></div></td>
	<?php // une col pour detacher la plage de saisie des col prec )?>
	<td class="tab_detail_bleu" width="5px" style="border-top:1px solid black;" ></td><?php
	
	$a=1;
if ($totalRows_Rs_emploi>0)	{	

//########################## affichage plages 


 // Entetes des plages [cours normaux] à afficher
		for($a=1;$a<=$totalRows_Rs_emploi;$a++)	{ //  suite des col d'entete: plages  du tableau d'appel
			if (($pos<=$maxcol)||($numcol[$a]>$pos-$maxcol)){
				echo '<td class="tab_detail_bleu" style="border-top:1px solid black;border-right:1px solid black; ';
				if ($a!=$pos) {echo 'text-align:center;">';} 
				else {echo 'border-collapse:separate; border-left:1px solid black;">';} ;
	
		
				
			if ($a==$pos) { //  recherche des absents déclarés  pour checkbox [ Aucun absent] de la col de saisiemysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

					if ( $_GET['gic_ID'] != "0" ) { // filtre  extraction des even [Aucun absent ]
						$query_Aucun_Abs = sprintf("SELECT ID FROM ele_absent 
						WHERE ele_absent.eleve_ID=0 
						AND date ='%s' AND classe_ID IN (%s) 
						AND heure_debut='%s' 
						AND prof_ID=%u ",
						$_GET['date'],$in_classe_gic,$hdeb[$a],$idpc[$a]);
					} else {
						$query_Aucun_Abs = sprintf("SELECT ID
						FROM ele_absent 
						WHERE ele_absent.eleve_ID=0 
						AND date ='%s' AND classe_ID=%u AND heure_debut='%s' 
						AND prof_ID=%u ",
						$_GET['date'],$_GET['classe_ID'],$hdeb[$a],$idpc[$a]);
							};	
					// fin filtre extraction des even [Aucun absent ]		
					$Aucun_Abs = mysqli_query($conn_cahier_de_texte,  $query_Aucun_Abs ) or die(mysqli_error($conn_cahier_de_texte));
					$Nb_Aucun_Abs = mysqli_num_rows($Aucun_Abs);
					mysqli_free_result($Aucun_Abs);	?>
	<?php 		 //  fin recherche des absents déclarés  pour checkbox [ Aucun absent]de la col de saisie
							
					//zone [pas d'absent]?> &nbsp; <fieldset style="float:left; display: inline-block; width:380px; height:36px;  margin:0px;  padding:0px; border:0px; ">
					
					<div style="display:inline-block;vertical-align:top; width:20px;height:19px; margin:0px; margin-top:0px; padding:8px 4px 7px 2px; background-color: #C2F8DA; font: Helvetica 20pt;border: 1px solid black; border-right:0px solid;"><input type="checkbox" name="pasdabsent" id="pasdabsent" <?php 
					if ( $saisie_oui==false  ) {echo ' onclick="return false" ';};
					if ( $Nb_Aucun_Abs > 0 ) {echo ' checked';};?>></div><div style="display: inline-block; vertical-align:top; height:26px; width:90px;  margin:0px; padding:4px; background-color: #C2F8DA; font: Helvetica 20pt;border: 1px solid black;border-left:0px solid">
					Aucun absent &nbsp; <br> &nbsp;  &agrave; signaler </div>
					
					<div style="display: inline-block;width:240px;padding-left:6px;"> 
					
					<?php 
					
					echo '<font style="font-size:10px;border:1px solid;padding-left:2px;padding-right:1px;"><b>'.$plages[$plageH[$a]].'</font></b>&nbsp; &nbsp;'.$ident[$a].'<br>';?> <?php
						echo $hdeb[$a]. '-'.$hfin[$a].'<br>';
						echo $gr[$a];
						//if ($gr[$a]!="Classe entière"){echo $gr[$a].' ';}; 
						if ($colpart[$a]==1){echo '(classe partagée)'.' ';};?>
					<br><?php 
					//echo 'Salle';
					//echo substr($gr[$a],0,4); // remplacer par salle !?></font>
					</div></fieldset>
					<?php
				
				

	?>
					
	<?php
		$b=$a;
	$tot=$totalRows_RsDs+$totalRows_Rs_emploi;
	
	//affichage Devoir et heure sup dans cellule du bout du bandeau
	if ($totalRows_RsDs>0){
		for($a=$b;$a<=$tot;$a++){ //echo 'ICI';
			
								?>
					<div style="display: inline-block;width:240px;padding-left:6px;"> <?php
			
			
			echo $hdeb[$a]. '-'.$hfin[$a].' <br> '.$ident[$a].'<br>';
			if ($gicid[$a]>0) {echo "Regroupement";}else {echo $gr[$a];};
			if ($typac[$a]=='ds_prog'){echo '<br /><span class="rouge">Devoir</span>';} else if 
			(
				(isset($_GET['edt_modif']))&&($_GET['edt_modif']=='O')
				){echo '<br /><span class="rouge">Edt modif.</span>';} else {
			
				echo '<br /><span class="rouge">Heure sup.</span>';};
				echo '</td>';
				
		} ;
	} //du if $totalRows_RsDs
	// fin du bandeau
//fin old
				
				
				
				} else {
				// les autres colonnes : plage, prof, groupe, salle	
				//echo '<font style="font-size:12px;border:1px solid;padding-left:2px;padding-right:2px;"><b>'.$plages[$plageH[$a]].'</b></font><br><br>S'.substr($gr[$a],0,4); // remplacer par salle !
				echo '<font style="font-size:10px;border:1px solid;padding-left:2px;padding-right:2px;"><b>'.$plages[$plageH[$a]].'</b></font><br><br>';
				if ($gr[$a]=='Classe entière'){ echo 'Cl. entière';} else {echo$gr[$a];};
				
				};
				
				
				
				
				
				
				
				
				// fin  checkbox [ Aucun absent] pour  plage en saisie :
			
			};
			};
			// fin  suite des col d'entete: des plages cours du tableau d'appel
			
			
//########################## Fin affichage
		
	//debut insertion bandeau ancienne version
	
	
	
	
	
	}
	
	

	
	//fin insertion bandeau ancienne version
									// fin entetes des plages [cours normaux]						
	$b=$a;
	$tot=$totalRows_RsDs+$totalRows_Rs_emploi;
	?>
<?php if ($totalRows_RsDs>0)  {  //  dernieres  col d entete: plages  Devoir et heure sup
			for($a=$b;$a<=$tot;$a++){
			
			echo '<td class="tab_detail_bleu" style="border-top:1px ;">'.$hdeb[$a]. '-'.$hfin[$a].' <br> '.$ident[$a].'<br>';
			if ($gicid[$a]>0) {echo "Regroupement";}else {echo $gr[$a];};
			if ($typac[$a]=='ds_prog'){echo '<br /><span class="rouge">Devoir</span>';} else if 
			(
				(isset($_GET['edt_modif']))&&($_GET['edt_modif']=='O')
				){echo '<br /><span class="rouge">Edt modif.</span>';} else {
			
				echo '<br /><span class="rouge">Heure sup.</span>';};
				
				
				//zone [pas d'absent]?> &nbsp; <fieldset style="float:left; display: inline-block; width:380px; height:36px;  margin:0px;  padding:0px; border:0px; margin-left:100px";margin-top:0px;>
					
					<div style="display:inline-block;vertical-align:top; width:20px;height:19px; margin:0px;margin-top:0px;  padding:8px 4px 7px 2px; background-color: #C2F8DA; font: Helvetica 20pt;border: 1px solid black; border-right:0px solid;"><input type="checkbox" name="pasdabsent" id="pasdabsent" <?php 
					if ( $saisie_oui==false  ) {echo ' onclick="return false" ';};
					if ( $Nb_Aucun_Abs > 0 ) {echo ' checked';};?>></div><div style="display: inline-block; vertical-align:top; height:26px; width:90px;  margin:0px; padding:4px; background-color: #C2F8DA; font: Helvetica 20pt;border: 1px solid black;border-left:0px solid">
					Aucun absent &nbsp; <br> &nbsp;  &agrave; signaler </div>
					
				</fieldset>
			
			
			
		<?php 	
				echo '</td>';
				
			} ;
			
			
			
								
			
	} 									//fin dernieres  col d'entete: plages  Devoir et heure sup
	?>
	
	<?php						//  fin dernieres  col d'entete: plages  Devoir et heure sup
	?></tr>
	
	
<tr><?php 	// ligne de sous-entete : nb absents 
	?><td class="tab_detail_gris_clair" style="text-align:right;vertical-align:center;font-size:11px;">
	<?php  echo 'Nb. absents signal&eacute;s : ';
	?></td><td class="tab_detail_gris_clair" style="text-align:center;border-left:1px solid #CCCCCC; border-right:0px solid ;" ><?php  //absents  le j-1
	/* $query_AbsJ_1 = sprintf("SELECT ID FROM ele_absent 
							WHERE date = '%s' 
							AND classe_ID=%u 
							AND eleve_ID!=0 
							AND absent='Y'
							GROUP BY eleve_ID",
				$date_jprec,$_GET['classe_ID']);mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

			// $AbsJ_1 = mysqli_query($conn_cahier_de_texte,  $query_AbsJ_1 ) or die(mysqli_error($conn_cahier_de_texte));
			$Nb_AbsJ_1 = mysqli_num_rows($AbsJ_1);
			mysqli_free_result($AbsJ_1 );// fin absents  le j-1
			echo $Nb_AbsJ_1; */
 ?>	</td>
	<td class="tab_detail_gris_clair" align="center" style="padding:2px 5px;border-left:0px solid; border-right:1px solid #CCCCCC;"><?php  //pbcarnet  le j-1
	/* $query_Carnet_J_1 = sprintf("SELECT ID FROM ele_absent 
							WHERE date = '%s' 
							AND classe_ID=%u 
							AND eleve_ID!=0 
							AND pbCarnet='Y'
							GROUP BY eleve_ID",
				$date_jprec,$_GET['classe_ID']);mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

			// $Carnet_J_1 = mysqli_query($conn_cahier_de_texte,  $query_Carnet_J_1 ) or die(mysqli_error($conn_cahier_de_texte));
			$Nb_Carnet_J_1 = mysqli_num_rows($Carnet_J_1);
			mysqli_free_result($Carnet_J_1 );// fin pb carnets   le j-1
			if ( $Nb_Carnet_J_1 > 0 ){echo '<br>'.$Nb_Carnet_J_1.'car'; }; */
?>	</td>
	<td class="tab_detail_gris_clair"> </td><?php //(une colonne pour detacher) ?>
	
	<?php for($j=1;$j<=$tot;$j++){ // Affichage [nb abs ]   +  Entete pour la plage en saisie
	if (($pos<=$maxcol)||($numcol[$j]>$pos-$maxcol)) { // bornage du balayage colonnes de la ligne NbAbs
			
			if ( $_GET['gic_ID'] != "0" ) {// recherche si [Aucun absent] validé
			$query_Aucun_Abs = sprintf("SELECT * FROM ele_absent 
					WHERE ele_absent.eleve_ID=0 
					AND date ='%s' 
					AND classe_ID IN (%s) 
					AND heure_debut='%s' 
					AND prof_ID=%u ",
					$_GET['date'],$in_classe_gic,$hdeb[$j],$idpc[$j]);
			} else {
				$query_Aucun_Abs = sprintf("SELECT * FROM ele_absent 
					WHERE ele_absent.eleve_ID=0 
					AND date ='%s' 
					AND classe_ID=%u 
					AND heure_debut='%s' 
					AND prof_ID=%u ",
					$_GET['date'],$clid[$j],$hdeb[$j],$idpc[$j]);
			};mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

			$Aucun_Abs = mysqli_query($conn_cahier_de_texte,  $query_Aucun_Abs ) or die(mysqli_error($conn_cahier_de_texte));
			$Nb_Aucun_Abs = mysqli_num_rows($Aucun_Abs);
											// fin recherche si [Aucun absent] validé
											
			if ( $_GET['gic_ID'] != "0" ) {// On recupere le nombre d'eleves absents  
				$query_RsEventSaisis = sprintf("SELECT ID FROM ele_absent 
					WHERE date = %s 
					AND classe_ID IN (%s) 
					AND eleve_ID IN (%s) 
					AND heure_debut='%s' 
					AND prof_ID=%u  
					AND absent='Y' ",
					$_GET['date'],$in_classe_gic,$in_ele_gic ,$hdeb[$j],$idpc[$j]);
			} else {
				$query_RsEventSaisis = sprintf("SELECT ID FROM ele_absent 
					WHERE date = '%s' 
					AND classe_ID=%u 
					AND eleve_ID!=0 
					AND heure_debut='%s' 
					AND prof_ID=%u 
					AND absent='Y' ",
					$_GET['date'],$clid[$j],$hdeb[$j],$idpc[$j]);  // $gr defini en  353
			};mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

			$Abs_Saisis = mysqli_query($conn_cahier_de_texte,  $query_RsEventSaisis ) or die(mysqli_error($conn_cahier_de_texte));
			$Nb_Abs_Saisis = mysqli_num_rows($Abs_Saisis);
			mysqli_free_result($Aucun_Abs);
			mysqli_free_result($Abs_Saisis );// fin On recupere le nombre d'eleves absents 
			
			if ($pos==$j) { // colonne  en saisie : pas d'appel /nb d'absents / coche pas d'absents  Car Mat Autre	
			?> <br /><td class="tab_detail_gris_clair" valign="middle" style="padding-left:0px; border-collapse:separate; border:1px solid black;" >
				<span style="width:16px;float:left;margin-left:10px;margin-top:0px;"><img src="../images/user_absent1.png" width="16" height="16" title="Signaler un absent"> </span>
			<span style="width:16px;float:left;margin-left:10px;margin-top:0px;"><img src="../images/retard_ok.png" width="16" title="Signaler un retard valable"> </span>
				<span style="width:16px;float:left;margin-left:12px;margin-top:0px;"><img src="../images/retard_no_ok.png" width="16" height="16" title="Signaler un retard NON valable"> </span><?php
				//<input type="hidden" name="nombre_absent_select" id="nombre_absent_select" value="'.$Nb_RsEventSaisis.'"> 
				?>
				<span style="width:132px;float:left;margin-left:6px;margin-top:0px;padding:3px;text-align:center; font-size:11px; border:1px solid white;"><a href="#" title="Sélectionner le motif du retard ou de l'incident&#10; dans la boite déroulante.&#10; Par défaut, un retard est qualifié [non valable].&#10;&#10; S'il y a retard ET un autre incident,&#10;- saisir le retard ici, valider cette saisie,&#10;- puis cliquer sur l'icône carnet en bout de ligne &#10;  pour saisir l'incident&#10;&#10;Pour annuler une déclaration de retard ou d'incident,&#10; sélectionner l'item [- Annulation -] en bas dans la liste des motifs "> Motif </a></span>
				
				<span style="width:16px;float:left;margin-left:6px;margin-top:0px;"><img src="../images/carnet2.png" width="16" height="16" title="Signaler l 'absence de carnet"></span><span style="width:120px;float:left;margin-left:8px; margin-top:0px;padding:3px;text-align:center; font-size:11px; border:1px solid white;"> <a href="#" title="Pour effectuer la saisie,&#10; vous pouvez agrandir la fen&ecirc;tre&#10; en s&eacute;lectionnant avec la souris le coin inférieur droit">Détails</a></span> &nbsp;<a href="#" title="Pour consulter le carnet d'un élève&#10; et/ou saisir d'autres évènements le concernant"></a></td>
	<?php 
				} else { // sous entete des autes colonnes de  la ligne nb abs
				?><td class="tab_detail_gris_clair" style="text-align:center; vertical-align:center;font-size:12px;"><?php
				 $mg='';
				 if ( $Nb_Abs_Saisis == 0 ) { // display l'état de l'appel 
						if ($Nb_Aucun_Abs ==0)	{ $visuel='?'; $msg='Appel non effectué';}// pas d'appel
						else { $visuel='0';  $msg='Aucun absent';} ;
					} else { $visuel=$Nb_Abs_Saisis;$msg=$Nb_Abs_Saisis. ' absent' ; 
								if ($Nb_Abs_Saisis>1){$msg.='s';};
					};
											// fin display état de l'appel
								
				echo '<b><a href="#" title="&nbsp; &nbsp;'.$msg.'&#10; entre '.$hdeb[$j].' et '.$hfin[$j].'&#10;';
				echo '&nbsp; '.$ident[$j].'&#10;'; // $ident[$j] : nom du prof
				if ($gr[$j]!="Classe entière"){
						echo '&nbsp; en '.$gr[$j].'&#10;';};  // rappel $gr renvoit pour nous la salle au lieu de "classe entiere" cd 345
				if ($colpart[$j]==1){echo '(classe partagée)'.'&#10;';};
				echo '">'.$visuel.'</a></b>';
							
				?></td>	<?php
				}; // fin ligne abs
		}; 											// fin  bornage du balayage colonnes de la ligne NbAbs
		
	; } 							// fin Affichage [nb abs ]   +  Entete pour la plage en saisie	?>	
 <?php 		// fin ligne de sous-entete : nb absents  ?>
	
	</tr></tbody ><tbody id="rowclick5"  style="border:1px;" > 

<?php //=====================  fin de l'entete ,  Debut listing eleves ====================	?>
	
	<?php $alterne=1; $ind_ligne=0; $quadriLign=0;
	do {					 // edition des lignes eleve par eleve    > 1062 								 
		$alterne=$alterne*(-1);$quadriLign=$quadriLign+1;
		if ($alterne==1){ // gestion alternance de grisé de ligne
		$style_tab= 'class="tab_detail_gris_clair" ';	} else {
			$style_tab='class="tab_detail_gris_fonce" ';};
		if ($quadriLign==4) {
		$quadriLign=0; $TdSouligne ='border-bottom:1px solid #999999;';}else {$TdSouligne='';};
		
		?><tr <?php echo 'id="ligne'.$ind_ligne.'" '.$style_tab ; $ind_ligne+=1; ?> >
		
		<td style="padding:4px;font-size:11px;<?php echo $TdSouligne?> " nowrap="nowrap" id="<?php echo 'div'.$fiche_liste_d_appel['ID_ele'];?>"><div align="left" onmouseover="this.style.cursor='pointer';" >
		<?php echo $fiche_liste_d_appel['nom_ele'].' '.$fiche_liste_d_appel['prenom_ele'];?>
		</div></td>
		
		<td style="border-left:1px solid #CCCCCC; border-right:0px solid;<?php echo $TdSouligne?>text-align:center;"><?php 
		//Recherche  absents & carnets J-1 et  mot du prof jusqu'a j_7mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

		$query_RsEvent_hier = sprintf("SELECT eleve_ID,absent FROM ele_absent WHERE eleve_ID = %u AND date ='%s' AND absent ='Y' ",$fiche_liste_d_appel['ID_ele'],$date_jprec);
		$RsEvent_hier = mysqli_query($conn_cahier_de_texte, $query_RsEvent_hier ) or die(mysqli_error($conn_cahier_de_texte));
		// $row_RsEvent_hier = mysqli_fetch_assoc($RsEvent_hier);
		$totalRows_RsEvent_hier = mysqli_num_rows($RsEvent_hier); 
		//  echo $date_jprec.' '.$totalRows_RsEvent_hier;
		if($totalRows_RsEvent_hier > 0 ){ // au moins 1h d'absence
		echo '<img src="../images/absentGris20.png">';};
		echo '</td><td style="padding:2px 5px;border-left:0px solid; border-right:1px solid #CCCCCC;'.$TdSouligne.'">';
		mysqli_free_result($RsEvent_hier);mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

		$query_RsEvent_hier = sprintf("SELECT eleve_ID,absent FROM ele_absent WHERE eleve_ID = %u AND date ='%s' AND pbCarnet ='Y' ",$fiche_liste_d_appel['ID_ele'],$date_jprec);
		$RsEvent_hier = mysqli_query($conn_cahier_de_texte, $query_RsEvent_hier ) or die(mysqli_error($conn_cahier_de_texte));
		// $row_RsEvent_hier = mysqli_fetch_assoc($RsEvent_hier);
		$totalRows_RsEvent_hier = mysqli_num_rows($RsEvent_hier);
		// echo $date_jprec.' '.$totalRows_RsEvent_hier;
		if($totalRows_RsEvent_hier>0){ echo '<img src="../images/carnetalert18.png">';};
		mysqli_free_result($RsEvent_hier);
		?></td><td style="<?php echo $TdSouligne?>width:12px;"><?php 
		
		/// echo $nb_MotCarRecent;
		
		?></td>
<?php  															// fin Recherche absents J-1 & Pb carnets J-1
		// liste des mots  du pro à j-7 non verifies (sinon visa='Y'
	
	
	for($j=1;$j<=$tot;$j++){ 								// edition  de la ligne de cet elv col par col  /text-align:center;
				?><td nowrap="nowrap" style="<?php echo $TdSouligne?>padding-top:2px;" >
				<div>
		<?php if (($pos<=$maxcol)||($numcol[$j]>$pos-$maxcol)){ 	//bornage de colonnes des lignes listing
			
				// On recupere les fiches d'appel enregistrées pour cours réguliersmysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

				if ( $_GET['gic_ID'] != "0" ) { $sqlclasse= 'N';} else { $sqlclasse=$_GET['classe_ID'];};
				
				$query_appel_elv = sprintf("SELECT * FROM ele_absent 
				WHERE eleve_ID=%u 
				AND date ='%s' AND classe_ID=%u AND heure_debut='%s' 
				AND prof_ID=%u",
				$fiche_liste_d_appel['ID_ele'],	$_GET['date'],$sqlclasse,$hdeb[$j],$idpc[$j]); 
				$appel_elv = mysqli_query($conn_cahier_de_texte, $query_appel_elv ) or die(mysqli_error($conn_cahier_de_texte));
				$fiche_appel_elv= mysqli_fetch_assoc($appel_elv);
				$nb_fiche_appel_elv = mysqli_num_rows($appel_elv);

				if ($nb_fiche_appel_elv <= 0 ) { // pb ici      
				// On recupere les fiche d'appel enregistrées pour cours supplémantairesmysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

				  if( $_GET['gic_ID'] != "0" ) { $sqlclasse= "AND classe_ID='N'";}
													//	pb avec   IN '.$in_classe_gic ;} ####################
										  else { $sqlclasse='AND classe_ID='.$_GET['classe_ID'] ;};
										  
				  $query_appel_elv = sprintf("SELECT * FROM ele_absent 
				  WHERE eleve_ID=%u 
				  AND ele_absent.date = '%s' %s AND heure_debut='%s' 
				  AND prof_ID=%u",    
				  $fiche_liste_d_appel['ID_ele'],$_GET['date'],$sqlclasse,$hdeb[$j],$idpc[$j]); 
				  $appel_elv = mysqli_query($conn_cahier_de_texte, $query_appel_elv ) or die(mysqli_error($conn_cahier_de_texte));
				  $fiche_appel_elv= mysqli_fetch_assoc($appel_elv);
				  $nb_fiche_appel_elv = mysqli_num_rows($appel_elv);
				} ;
				
				if (($nb_fiche_appel_elv>0) && ($pos!=$j) ){ // affichage des  absences, retards et incidents des autres plages 
				echo ' <div style="width:20px;margin-left:auto;margin-right:auto;">';
						if ( $fiche_appel_elv['absent']=='Y')	{ echo '<img src="../images/user_absent1.png">';  };			
						if ($fiche_appel_elv['retard_V']=='Y') { echo '<img src="../images/retard_ok.png">';	};
						if ($fiche_appel_elv['retard_Nv']=='Y') { echo '<img src="../images/retard_no_ok.png">';};
						if ($fiche_appel_elv['motif'] >=$IndexHorsRetards) {echo '<img src="../images/carnetedit18.png" title="'.$motifs[$fiche_appel_elv['motif']].'">';};
						if ($fiche_appel_elv['pbCarnet']=='Y') {echo '<img src="../images/carnetalert18.png">';};		  
				echo '</div>';};
				//  fin affichage des  absences, retards et incidents des autres plages 
				 // $gr_url=$_GET['groupe'];
		};														//fin bornage colonnes (sauf colonne en saisie)
	

	if ( ($j==$pos )&& ( $saisie_oui))	{ // affichage de la colonne des saisies si NON verrouillée
						$createur_heure_part_ID=$idpc[$j];
						?><?php
				// recherche des mots récents non vérifiés, depuis  j-X défini vers 485mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

					$query_evenCar = 	sprintf("
					SELECT eleve_ID, classe, retard_Nv, motif, pbCarnet,surcarnet, details	FROM ele_absent
					WHERE prof_ID=%s 
					AND eleve_ID=%s
					AND ((retard_Nv='Y')  OR ( motif > %s ) OR (motif=0 and details!='') or ( pbCarnet = 'Y' ))
					AND signature='N'
					AND  date <'%s'
					AND date >='%s'
					ORDER BY date,substring(heure_saisie,0,4)",
					$_SESSION['ID_prof'],$fiche_liste_d_appel['ID_ele'],$IndexHorsRetards, $_GET['date'], $date_jMoinsX); // $prof_declarant ????
	
					$evenMotCarRecent = mysqli_query($conn_cahier_de_texte, $query_evenCar ) or die(mysqli_error($conn_cahier_de_texte));
					$row_MotCarRecent = mysqli_fetch_assoc($evenMotCarRecent);
					$nb_MotCarRecent = mysqli_num_rows($evenMotCarRecent);
			
				   	if ($nb_fiche_appel_elv>0) { 
					$absent= $fiche_appel_elv['absent']; $retard_V=$fiche_appel_elv['retard_V'];$retard_Nv=$fiche_appel_elv['retard_Nv'];
					$motif=$fiche_appel_elv['motif']; $pbCarnet=$fiche_appel_elv['pbCarnet']; 
					$statutVs= $fiche_appel_elv['vie_sco_statut'] ;
					} else	{
					$absent='N';$retard_V ='N';$retard_Nv = 'N';$motif = 'N';$pbCarnet='N';$details="";$statutVs ='N';
					};
						
					if ($statutVs == 'Y') {	echo 'Absence validée';};
							
					// pas d'incidents à visualiser ou saisir !
					// saisie abs ouverte  0-->  pas validé par vie sco !	1> validé
					if ($statutVs == 'N')	{
						$idelv=$fiche_liste_d_appel['ID_ele']; // pour alleger la suite 
						?>&nbsp; <input onclick="absent('<?php echo $idelv;?>')" type="checkbox" name="Ab_<?php echo $idelv;?>"  id="Ab_<?php echo $idelv;?>" value="Y" <?php if ($absent=='Y') { echo '  checked ';};?>> &nbsp; 
							
						<input onclick="document.getElementById('<?php echo $idelv;?>')" type="checkbox" name="Rv_<?php echo $idelv;?>"  id="Rv_<?php echo $idelv;?>" value="Y"  <?php if ($retard_V =='Y'){echo '  checked';};?>>
				
						<input onclick="document.getElementById('<?php echo $fiche_liste_d_appel['ID_ele'];?>')" type="checkbox" name="Nv_<?php echo $fiche_liste_d_appel['ID_ele'];?>"  id="Nv_<?php echo $fiche_liste_d_appel['ID_ele'];?>" value="Y"  <?php if ($retard_Nv =='Y'){echo '  checked';};?>>&nbsp;<?php 
						// si retard nv et item retard choisi, on n'affiche plus les autres pour eviter ecrasement par 2eme incident 
							if ($retard_Nv =='Y'){$limite_motifs=5 ;} else {$limite_motifs=$nbmotifs_profs;};
							
						// si retard nv et item retard choisi, on n'affiche plus les autres pour eviter ecrasement par 2eme incident 
						?><select  name="Mo_<?php echo $fiche_liste_d_appel['ID_ele'];?>" id="Mo_<?php echo $fiche_liste_d_appel['ID_ele'];?>" <?php  if ($motif < 1) { echo ' style ="background-color:#E6E6E6 ; color:gray ; height:18px" ';}; ?>>
							
						<option value="0"> &nbsp; motif ...&nbsp; &nbsp; &nbsp;  </value>
						<?php	for ($i=1; $i<=$limite_motifs; $i++) {//  items motifs
						echo '<option value="'.$i.'" style="background-color:'.$color[$i].' ;"' ;
						if ( ($i>0 )&&($fiche_appel_elv['motif']== $i)){echo ' selected ';};
						echo '>'.$motifs[$i].' </option> ' ;
									} ;
						if ($fiche_appel_elv['motif']>0){	// on ajoute l'item "annulation" avec index 0
							echo '<option value="0" style="background-color:white;"]>- Annulation -</value>';
						};	
						?></select>
						<input  type="checkbox" name="Ca_<?php echo $fiche_liste_d_appel['ID_ele'];?>"  id="Ca_<?php echo $fiche_liste_d_appel['ID_ele'];?>" value="Y"  <?php if ($pbCarnet=='Y'){echo ' checked';};?>><?php 
						if ($fiche_appel_elv['details']!=""){
							$largeur= 12+ 6*strlen( $fiche_appel_elv['details']	);
							if($largeur >200){$largeur=200;};
						} else {
							$largeur= 120;	
						};
						?>&nbsp;
						
<textarea 
style="width:<?php echo $largeur ; ?>px;  max-width:300px; height:10px; max-height:124px;  background-color:white ;border:none; background-color:#FFFFCC" 
cols="1" 
rows="1"  
class="tab_detail_gris_clair" 
id="De_<?php echo $fiche_liste_d_appel['ID_ele'];?>"   

name="De_<?php echo $fiche_liste_d_appel['ID_ele'];?>" 
>
<?php echo $fiche_appel_elv['details']; ?>
</textarea>
												
						<span style="width:50px;padding:0px;border:0px solid black ; font-size:11px; "><?php
						if( $fiche_appel_elv['motif']> 0) {
							$mode=2;$title='Ajouter un autre incident';	$img='carnetajout.png';
						} else  {
						$mode=1;$title=' Carnet de '.$fiche_liste_d_appel['prenom_ele'].' ';$img='carnet18y.png';
						};
						?><a href="carnet_elv.php?elvid=<?php echo $fiche_liste_d_appel['ID_ele'].'&date1='.$date_form.'&date2='.date('d/m/Y').'&classe='.$_GET['nom_classe'].'&jour='.$jour.'&mode='.$mode.'&submit=Actualiser" target="_blank" title="'.$title.'"><IMG SRC="../images/'.$img;?>"></a><?php  //attention date2 à date du jour de consult pour rendre la fiche actualisée !!!!!!
								
						if ( $nb_MotCarRecent > 0)	{ 
							$Msg="V&eacute;rifier signature dans le carnet";
							$Img='oeilbleu20'; // par defaut
							if ( $row_MotCarRecent['pbCarnet']=='Y')  { //avec ou non retard ou incident 
									if ( $row_MotCarRecent['surcarnet']=='Y') { 
										$Img="oeilorange20"; 
										} else { $Img="oeilrouge20";$Msg="Incident &agrave; retranscrire";
									};}
							elseif ($row_MotCarRecent['retard_Nv']=='Y') { $Img="oeiljaune20";}
							elseif ( $row_MotCarRecent['motif'] > 1)  {  $Img="oeilorange20";}
							else {$Msg="Information &agrave; suivre ";};
									 
									echo '<a href="carnets_prof.php?classe='.$_GET['nom_classe'].'&nonSignes=Y&submit=Actualiser" target="_blank" title="'.$Msg.'"><IMG SRC="../images/'.$Img.'.png"></a>';
						
												
						}; // pb avec $row_MotCarRecent['classe'] ???
						?> </span><?php
				};
					// attention on va renvoyer le statut et l'etat d'anul pour ne pas les perdre  ! 
					 ?> <input  name="St_<?php echo $fiche_liste_d_appel['ID_ele'];?>" id="St_<?php echo $fiche_liste_d_appel['ID_ele'];?>" type="hidden" value="<?php echo $statutVs ; ?>">
					    <input  name="An_<?php echo $fiche_liste_d_appel['ID_ele'];?>" id="St_<?php echo $fiche_liste_d_appel['ID_ele'];?>" type="hidden" value="<?php echo $fiche_appel_elv['annule'] ; ?>"><?php 
			mysqli_free_result($evenMotCarRecent);
	}; 					// fin affichage de la colonne de saisie si NON verouillee depuis 928
				
	
		if ( ($j==$pos )&& ( !$saisie_oui))	{//  affichage de la colonne plage horaire en cours SI VERROUILLEE
			$createur_heure_part_ID=$idpc[$j];
				// recherche des mots récents non vérifiés depuis  j-X défini vers 485
				$query_evenCar = 	sprintf("
				SELECT eleve_ID, classe, retard_Nv, motif, pbCarnet,surcarnet, details		FROM ele_absent
				WHERE prof_ID=%s 
				AND eleve_ID=%s
				AND ((retard_Nv='Y')  OR ( motif > %s ) OR (motif=0 and details!='') or ( pbCarnet = 'Y' ))
				AND signature='N'
				AND  date <'%s'
				AND date >='%s'
				ORDER BY date,substring(heure_saisie,0,4)",
				$_SESSION['ID_prof'],$fiche_liste_d_appel['ID_ele'],$IndexHorsRetards, $_GET['date'], $date_jMoinsX); // $prof_declarant ????
	
				$evenMotCarRecent = mysqli_query($conn_cahier_de_texte, $query_evenCar ) or die(mysqli_error($conn_cahier_de_texte));
				$row_MotCarRecent = mysqli_fetch_assoc($evenMotCarRecent);
				$nb_MotCarRecent = mysqli_num_rows($evenMotCarRecent);
				// mysqli_free_result($evenMotCarRecent);
				   
				   
				if ($nb_fiche_appel_elv>0) { //initialisation les variables y compris si pas de fiche  ou pas complete !
				$absent= $fiche_appel_elv['absent']; $retard_V=$fiche_appel_elv['retard_V'];$retard_Nv=$fiche_appel_elv['retard_Nv'];
					 $motif=$fiche_appel_elv['motif']; $pbCarnet=$fiche_appel_elv['pbCarnet']; $statutVs= $fiche_appel_elv['vie_sco_statut'] ;
					} else	{ 
					$absent='N';$retard_V ='N';$retard_Nv = 'N';$motif = 'N';$pbCarnet='N';$details="";$statutVs ='N';
					};
										//fin initialisation les variables 	SI VERROUILLEE
				 				
					if ($statutVs == 'Y') {//  absent :saisie fermée et pas d'incidents à afficher SI VERROUILLEE
												echo 'Absence validée';};
					if ($statutVs != 'Y') 	{  // present : on affiche les champs incidents SI VERROUILLEE
								$idelv=$fiche_liste_d_appel['ID_ele']; // pour alleger la suite
								?> &nbsp; <input onclick="absent('<?php echo $idelv ?>')" type="checkbox" name="Ab_'<?php echo $idelv?>'"  id="Ab_'<?php echo $idelv ?>" value="Y" readOnly="true" <?php if ($absent=='Y') {echo ' checked ';};?>> &nbsp; 
								<input onclick="document.getElementById('<?php echo $idelv?>')" type="checkbox" name="Rv_<?php echo $idelv;?>"  id="Rv_<?php echo $idelv;?>" value="Y" readonly="readonly"<?php if ( $retard_V =='Y') { echo ' checked';};?>><input onclick="document.getElementById('<?php echo $idelv;?>')" type="checkbox" name="Nv_<?php echo $idelv;?>"  id="Nv_<?php echo $idelv;?>" value="Y"  readonly  <?php if ($retard_Nv =='Y'){echo ' checked';};?>><?php
								if ($retard_Nv =='Y') { // ajustage items incidents
									//  si retard nv et item retard choisi, on n'affiche plus les autres pour eviter ecrasement par 2eme incident
									$limite_motifs=5 ;
									} else {
									$limite_motifs=$nbmotifs_profs;	
								};
																// fin  ajustage items incidents SI VERROUILLEE
															
								?> <input readonly="readonly" style ="background-color:<?php  
								if ($fiche_appel_elv['motif'] > 0 ){ // colorisation de la fenetre motif SI VERROUILLEE
								echo $color[$fiche_appel_elv['motif']];
								} else {
								echo '#EDEDED ';}; 							
								?>; width:132px;  height:14px" value=" <?php 
										if ($fiche_appel_elv['motif'] > 0 ) { echo $motifs[$fiche_appel_elv['motif']];};?>">
								<input  readonly="true"  type="checkbox" name="Ca_<?php echo $fiche_liste_d_appel['ID_ele'];?>"  id="Ca_<?php echo $fiche_liste_d_appel['ID_ele'];?>"  <?php if ($pbCarnet=='Y'){echo ' checked';};?>><?php            
								if ($fiche_appel_elv['details']!=""){	$largeur= 12+ 6*strlen( $fiche_appel_elv['details']);
								if($largeur >200){$largeur=200;}; } else {$largeur= 120;	};
									
								if ($fiche_appel_elv['details']!=""){ ?>
									<textarea readOnly style=" width:<?php echo $largeur ; ?>px;  max-width:500px; height:11px; max-height:84px;  background-color:#EFEFEF ;border:none" cols="1" rows="1"  class="tab_detail_gris_clair" id="De_<?php echo $fiche_liste_d_appel['ID_ele'];?>"   name="De_<?php echo $fiche_liste_d_appel['ID_ele'];?>" ><?php echo $fiche_appel_elv['details'];?></textarea> <?php 
								} ;
								?>
								<span style="width:50px;padding-top:3px;margin-left:0px;"> 								
								<a href="carnet_elv.php?elvid=<?php echo $fiche_liste_d_appel['ID_ele'].'&date1='.$date_form.'&date2='.date('d/m/Y').'&classe='.$_GET['nom_classe'].'&jour='.$jour.'&mode=0&submit=Actualiser" target="_blank"'; 
								if( $fiche_appel_elv['motif']> 0) { 
										echo 'title=" Ajouter un autre incident"><IMG SRC="../images/carnetajout.png">';
								 } else  {
										echo 'title=" Carnet et bilan de '.$fiche_liste_d_appel['prenom_ele'].'" ><IMG SRC="../images/carnet18y.png">';
								 };
							 ?> </a><?php
								if ( $nb_MotCarRecent > 0)	{ 
											$Msg="V&eacute;rifier signature dans le carnet";
											$Img='oeilbleu20'; // par defaut
											if ( $row_MotCarRecent['pbCarnet']=='Y')  { //avec ou non incident 
													if ( $row_MotCarRecent['surcarnet']=='Y') { 
														$Img="oeilorange20"; 
														} else { $Img="oeilrouge20";$Msg="Incident &agrave; retranscrire";
														};}
											elseif ($row_MotCarRecent['retard_Nv']=='Y') { $Img="oeiljaune20";}
											elseif ( $row_MotCarRecent['motif'] > 1)  {  $Img="oeilorange20";}
											else {$Msg="Information &agrave; suivre ";};
								
																												 
										echo '<a href="carnets_prof.php?classe='.$_GET['nom_classe'].'&nonSignes=Y&submit=Actualiser" target="_blank" title="'.$Msg.'"><IMG SRC="../images/'.$Img.'.png"></a>';
									};
									?></span>  <?php
								 }; // fin si pas valide vie sco'  pb:&classe='.$fiche_liste_d_appel['classe_ele'].'	
					?> <input  name="St_<?php echo $fiche_liste_d_appel['ID_ele'];?>" id="St_<?php echo $fiche_liste_d_appel['ID_ele'];?>" type="hidden" value="<?php 
					     echo $statutVs ; ?>">
					     <input  name="An_<?php echo $fiche_liste_d_appel['ID_ele'];?>" id="St_<?php echo $fiche_liste_d_appel['ID_ele'];?>" type="hidden" value="<?php echo $fiche_appel_elv['annule'] ; ?>"> <?php 			
				 } ;
				 ?> </div>	</td>
				 <?php	// fin  affichage de la colonne plage horaire en cours SI VERROUILLée  			
								/*?>	<SCRIPT langage="Javascript">  function ouvrir(fichier,fenetre) {
  ff=window.open(fichier,fenetre, "width=250,height=150,left=30,top=20")</script> }	<?php	 */	 ?>		
				
<?php	}; ?>  </tr> <?php
} while ( $fiche_liste_d_appel = mysqli_fetch_assoc($liste_d_appel));  	
//   pb mysqli_free_result($evenMotCarRecent); ?>	
<?php //fin balayage de colonnes des lignes eleves ?>
	
</tbody ></table>
	<?php 													// fin edition  1 ligne par eleve  ?>
												
	 <?php if ($saisie_oui) {// Validation de bas de page ?>
	 <p> <span style="float:left;padding-left :320px; padding-right:80px;">
	<i> N'h&eacute;sitez pas &agrave; renvoyer l'appel plusieurs fois dans l'heure <br> en cas de changements ou d'erreurs.</i> &nbsp; &nbsp;  &nbsp;
		</span>
		<input name="submit" type="submit" style="padding:4px;"  value="Valider et envoyer en vie scolaire" alt="Valider et envoyer en vie scolaire" title="ne pas hésiter à transmettre plusieurs fois">
	</p>
	<?php ;}; ?>
	<?php 					// fin  Validation de bas de page ?>

	<?php if (isset($createur_heure_part_ID))	{ // si heure partagee -  $row_RsPart['profpartage_ID'] est createur du partage // les absents seront enregistres dans la table sous son ID
	?><input type="hidden" name="createur_heure_part_ID" value="<?php echo $createur_heure_part_ID;?>">	<?php };?>
	  <input type="hidden" name="MM_insert" value="form1">
	  <input type="hidden" name="nb_eleves" value="<?php echo $nb_fiche_liste_d_appel;?>">
	  <input type="hidden" name="gic_ID" value="<?php echo $_GET['gic_ID'];?>">
	  </form><?php
};?>	
<?php	//  FIN édition  1 ligne par eleve    > de 813



mysqli_free_result($Rs_emploi);
mysqli_free_result($liste_d_appel);
?></div></body  onLoad="pre_ch()">
</html>