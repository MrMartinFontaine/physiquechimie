<?php 
// A verifier : ligne 825
//pas de verification de conservation de session pour un mobile
include "../authentification/authcheck.php";


if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { exit();header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 
require_once('../inc/functions_inc.php');
//rappel : xinha ne fonctionne pas sous iphone et ipad
//Pour iphone et ipad temporairement tinyMCE va se substituer a xinha

$listage_simple=0;

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
        $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$arcID = "";
if (isset($_SESSION['archivID'])) {
        $arcID = (get_magic_quotes_gpc()) ? intval($_SESSION['archivID']) : addslashes(intval($_SESSION['archivID']));
        $arcID = "_save".$arcID;
}

// Infos sur les regroupements de classes
// le libelle gic present dans le nom de plusieurs variables signifie "Groupement Inter Classes"
// si pas de regroupements : classe ID = celui de la classe et gic_ID= 0
// si regroupements     : classe_ID=0 et gic_ID = celui du regroupement
// La premiere classe du regroupement sert de modele pour le remplissage de la fiche des autres classes du regroupement
// Apres enregistrement de la fiche de la premiere classe et upload de ses fichiers joints,
// il y  a copie de la fiche pour les autres classes du regroupement
// et redirection du lien associe a ses fichiers joints vers ceux de la premiere classe (voir indice et indice_base lors du remplissage de cdt_travail)
// donc pas de multiples upload de fichiers sur les regroupements.

//les devoirs sont libelles ds_prog - heure est variable - le 9eme chiffre de code_date vaut 0
//les heures sup - heure s'incremente - 

 //special php4
 if(!function_exists('stripos')) {
 function stripos($haystack, $needle, $offset = 0) {
 return strpos(strtolower($haystack), strtolower($needle), $offset);
 }
 };

//determination de l'ID de la premiere classe d'un regroupement
if ((isset($_GET['classe_ID']))&&($_GET['classe_ID']==0)&&(isset($_GET['gic_ID']))) {
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_gic_classe_ID_default =sprintf("SELECT classe_ID FROM cdt_groupe_interclasses_classe WHERE gic_ID = %u LIMIT 1",$_GET['gic_ID']);
	$sel_gic_classe_ID_default = mysqli_query($conn_cahier_de_texte, $query_gic_classe_ID_default) or die(mysqli_error($conn_cahier_de_texte));
        $un_gic_classe_ID_default = mysqli_fetch_assoc($sel_gic_classe_ID_default);
};

$ProfID=$_SESSION['ID_prof'];
$heurepartagee=false;
$AgendaPartage='N';
$EmploiID=0; 

if ((isset($_GET['share']) && $_GET['share']=='O'  &&  !(isset($_POST["MM_coller"]) && $_POST["MM_coller"]=="form_coller")) || (isset($_POST['share_hour']) && $_POST['share_hour']<>'0')){ // Cas d'heure partagee
        if (isset($_POST['share_hour']) && $_POST['share_hour']<>'0') {$IDedt=GetSQLValueString($_POST['share_hour'],"int");} else {$IDedt=GetSQLValueString($_GET['edtID'],"int");};
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_partage = sprintf("SELECT cdt_emploi_du_temps.prof_ID,cdt_emploi_du_temps.ID_emploi FROM cdt_emploi_du_temps_partage,cdt_emploi_du_temps WHERE cdt_emploi_du_temps_partage.profpartage_ID=%u AND cdt_emploi_du_temps_partage.ID_emploi=%u AND cdt_emploi_du_temps_partage.ID_emploi=cdt_emploi_du_temps.ID_emploi",GetSQLValueString($_SESSION['ID_prof'],"int"),$IDedt);
	$sel_partage = mysqli_query($conn_cahier_de_texte, $query_partage) or die(mysqli_error($conn_cahier_de_texte));
	$un_partage = mysqli_fetch_assoc($sel_partage);
	$totalun_partage = mysqli_num_rows($sel_partage);
        if ($totalun_partage==1) { // L'heure est partagee mais le prof actuel n'en est pas le createur
                $ProfID=$un_partage['prof_ID'];
                $EmploiID=$un_partage['ID_emploi'];
                $heurepartagee=true;
                $AgendaPartage='O';
        };
	
} else {
	if (isset($_GET['edtID'])){
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_partage = sprintf("SELECT Partage_ID,ID_emploi FROM cdt_emploi_du_temps_partage WHERE ID_emploi=%u",GetSQLValueString($_GET['edtID'],"int"));
		$sel_partage = mysqli_query($conn_cahier_de_texte, $query_partage) or die(mysqli_error($conn_cahier_de_texte));
		$un_partage = mysqli_fetch_assoc($sel_partage);
		$totalun_partage = mysqli_num_rows($sel_partage);
		mysqli_free_result($sel_partage);
		if ($totalun_partage>0) { // L'heure est partagee mais le prof actuel en est le createur
			$AgendaPartage='O';
			$EmploiID=$un_partage['ID_emploi'];
		};
	};
};


// Enregistrement de la seance en cours
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
	
	//enlever les <br />
	if($_POST['activite']=='<br />'){$activite=NULL;} else {$activite=$_POST['activite'];};
	$activite=GetSQLValueString($activite,"text");
	
	
	//s'il s'agit d'un remplacement et de la premiere seance pour la plage
	//mettre a jour la date de debut de remplacement dans cdt_remplacement
	//creer un enregistrement purement informatif " Debut de remplacement de Mr X par Mr Y"

$_GET['saisie']=0; //la saisie est terminee
       
	if ($_SESSION['id_etat']==2) { //remplacement
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Remplacement = "SELECT * FROM cdt_remplacement WHERE remplacant_ID=".$_SESSION['ID_prof']." ORDER BY date_creation_remplace DESC LIMIT 1";
		$sel_Remplacement = mysqli_query($conn_cahier_de_texte, $query_Remplacement) or die(mysqli_error($conn_cahier_de_texte));
		$un_Remplacement = mysqli_fetch_assoc($sel_Remplacement);
		$nb_Remplacement= mysqli_num_rows($sel_Remplacement);
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_nomprof = "SELECT nom_prof,identite FROM cdt_prof WHERE ID_prof=".$un_Remplacement['titulaire_ID'];
		$sel_nomprof = mysqli_query($conn_cahier_de_texte, $query_nomprof) or die(mysqli_error($conn_cahier_de_texte));
		$un_nomprof = mysqli_fetch_assoc($sel_nomprof);
		
		$dateremp=substr($_POST['code_date'],0,4).'-'.substr($_POST['code_date'],4,2).'-'.substr($_POST['code_date'],6,2);
		
		$cd=substr($_POST['code_date'],0,8).'0';	
		if (($nb_Remplacement>0)&&(($un_Remplacement['date_debut_remplace']=='0000-00-00')||($dateremp<$un_Remplacement['date_debut_remplace']))){
			
			if ($un_Remplacement['date_debut_remplace']=='0000-00-00'){ //premiere saisie de cours
			
				// Si identite n'est pas remplie
				$NOM_PROF=$un_nomprof['identite']==''?$un_nomprof['nom_prof']:$un_nomprof['identite'];
				
				$info_debut_remplacement='<p align="center" class="erreur">D&eacute;but du remplacement de '.$NOM_PROF.' par '.$_SESSION['identite'].'</p>';

				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_Info_agenda_remplace = sprintf("INSERT INTO cdt_agenda (prof_ID, theme_activ,activite,code_date)
					VALUES (%u,  '%s', '%s',%s)",
					GetSQLValueString($_SESSION['ID_prof'], "int"),'Remplacement', $info_debut_remplacement,$cd);
				
				
				$sel_Info_agenda_remplace = mysqli_query($conn_cahier_de_texte, $query_Info_agenda_remplace) or die(mysqli_error($conn_cahier_de_texte));
				
				$UID=mysqli_insert_id($conn_cahier_de_texte);
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_Remplacement2 = sprintf(" UPDATE cdt_remplacement SET date_debut_remplace='%s', ref_debut_agenda_ID=%u WHERE ID_remplace=%s",$dateremp,$UID,$un_Remplacement['ID_remplace']);
				$sel_Remplacement2 = mysqli_query($conn_cahier_de_texte, $query_Remplacement2) or die(mysqli_error($conn_cahier_de_texte));
			}
			else
			{ //cette saisie devient chronologiquement la premiere saisie du remplacement
                                
				$updateSQL = sprintf("UPDATE cdt_agenda SET code_date=%s WHERE ID_agenda=%u",
					$cd,GetSQLValueString($un_Remplacement['agenda_ID'], "int")
					);
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_Remplacement2 = sprintf(" UPDATE cdt_remplacement SET date_debut_remplace='%s' WHERE ID_remplace=%s",$dateremp,$un_Remplacement['ID_remplace']);
				$sel_Remplacement2 = mysqli_query($conn_cahier_de_texte, $query_Remplacement2) or die(mysqli_error($conn_cahier_de_texte));
			}
		}
		mysqli_free_result($sel_nomprof);
		mysqli_free_result($sel_Remplacement);
	}	
	
	
	
	
	//recherche de la couleur de l'activite    
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_CouleurActivite = sprintf("SELECT couleur_activite FROM cdt_type_activite WHERE ID_prof=%u AND activite=%s",$ProfID,GetSQLValueString($_POST['type_activ'], "text"));
        $sel_CouleurActivite = mysqli_query($conn_cahier_de_texte, $query_CouleurActivite) or die(mysqli_error($conn_cahier_de_texte));
        $un_CouleurActivite = mysqli_fetch_assoc($sel_CouleurActivite);
        $totalun_CouleurActivite = mysqli_num_rows($sel_CouleurActivite);
        if ($totalun_CouleurActivite>0) {$CouleurActivite=$un_CouleurActivite['couleur_activite'];} else {$CouleurActivite='#000066';};
        mysqli_free_result($sel_CouleurActivite);
        
        //recherche des ID des classes du regroupement
        if ($_GET['classe_ID']==0) {
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_gic =sprintf("SELECT cdt_groupe_interclasses_classe.classe_ID FROM cdt_groupe_interclasses_classe WHERE cdt_groupe_interclasses_classe.gic_ID = %u",$_POST['gic_ID']);
		$sel_gic = mysqli_query($conn_cahier_de_texte, $query_gic) or die(mysqli_error($conn_cahier_de_texte));
		$un_gic = mysqli_fetch_assoc($sel_gic);
		$nb_gic = mysqli_num_rows($sel_gic);
		$x=0;
		do {
			$tabc1[$x]=$un_gic['classe_ID'];$x=$x+1;
		} while ($un_gic = mysqli_fetch_assoc($sel_gic));
	}
	else { 
		$nb_gic=1;
	};
	
	//BOUCLE sur les classes de regroupement (un seul passage si pas de regroupement)
        $x_gic=0; 
    	if (isset($_GET['edt_modif'])){$edt_modif='O';} else {$edt_modif='N';};
        do {            
                if ($_GET['classe_ID']==0) { $classe_ID_traitee = $tabc1[$x_gic]; } else {$classe_ID_traitee= $_POST['classe_ID'];};
                if ($_POST['date_a_faire_1']!=''){$datafaire[1]=$_POST['date_a_faire_1'];} else {$datafaire[1]='';}
                if ($_POST['date_a_faire_2']!=''){$datafaire[2]=$_POST['date_a_faire_2'];} else {$datafaire[2]='';}
                if ($_POST['date_a_faire_3']!=''){$datafaire[3]=$_POST['date_a_faire_3'];} else {$datafaire[3]='';}
                
		if ($_POST['misajour']<>'0') {
		
			if ($_GET['classe_ID']==0) {	
				
				// on boucle sur chaque classe du regroupement
				
				// rechercher l'ID_agenda a modifier
				
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_IDagenda_modif =sprintf(" 
					SELECT ID_agenda FROM cdt_agenda
					WHERE prof_ID=%u AND classe_ID=%u AND gic_ID=%u AND groupe=%s AND matiere_ID=%u AND heure=%u AND code_date=%s",
					GetSQLValueString($ProfID, "int"),
					GetSQLValueString($classe_ID_traitee, "int"),
					GetSQLValueString($_GET['gic_ID'], "int"),
					GetSQLValueString($_GET['groupe'], "text"),
					GetSQLValueString($_GET['matiere_ID'], "int"),
					GetSQLValueString($_GET['heure'], "int"),
					GetSQLValueString($_GET['code_date'], "text")
					);
				
                                $sel_IDagenda_modif = mysqli_query($conn_cahier_de_texte, $query_IDagenda_modif) or die(mysqli_error($conn_cahier_de_texte));
                                $un_IDagenda_modif = mysqli_fetch_assoc($sel_IDagenda_modif);
                                $updateSQL = sprintf("UPDATE cdt_agenda SET theme_activ=%s, type_activ=%s, couleur_activ=%s, a_faire='Oui', activite=%s, rq=%s
                                        WHERE ID_agenda=%u",
                                        GetSQLValueString($_POST['theme_activ'], "text"),
                                        GetSQLValueString($_POST['type_activ'], "text"),
                                        GetSQLValueString($CouleurActivite, "text"),
                                        $activite,
                                        GetSQLValueString($_POST['rq'], "text"),
                                        GetSQLValueString($un_IDagenda_modif['ID_agenda'], "int")
					);
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));	
				
				//on efface dans cdt_travail pour cet ID_agenda
				$deleteSQL = sprintf("DELETE FROM cdt_travail WHERE agenda_ID=%u AND prof_ID=%u",
					GetSQLValueString($un_IDagenda_modif['ID_agenda'], "int"),
					GetSQLValueString($ProfID, "int"));
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
				
				$indice=$un_IDagenda_modif['ID_agenda'];
                                
					} else {
					
                                // Normal sans regroupements
                                $updateSQL = sprintf("UPDATE cdt_agenda SET prof_ID=%u, classe_ID=%u ,gic_ID=%u, matiere_ID=%u, groupe=%s, semaine=%s ,jour_pointe=%s ,heure=%u ,duree=%s ,heure_debut=%s ,heure_fin=%s ,theme_activ=%s, type_activ=%s, couleur_activ=%s, a_faire=%s, activite=%s, rq=%s, code_date=%s, partage=%s, emploi_ID=%u
                                        WHERE ID_agenda='%u'",
                                        GetSQLValueString($ProfID, "int"),
                                        GetSQLValueString($_POST['classe_ID'], "int"),
					GetSQLValueString($_POST['gic_ID'], "int"),
					GetSQLValueString($_POST['matiere_ID'], "int"),
					GetSQLValueString($_POST['groupe'], "text"),
					GetSQLValueString($_POST['semaine'], "text"),   
					GetSQLValueString($_POST['jour_pointe'], "text"),
					GetSQLValueString($_POST['heure'], "int"),
					GetSQLValueString($_POST['duree'], "text"),
					GetSQLValueString($_POST['heure_debut'], "text"),
                                        GetSQLValueString($_POST['heure_fin'], "text"),
                                        GetSQLValueString($_POST['theme_activ'], "text"),
                                        GetSQLValueString($_POST['type_activ'], "text"),
                                        GetSQLValueString($CouleurActivite, "text"),
                                        GetSQLValueString('Oui', "text"),
                                        $activite,
                                        GetSQLValueString($_POST['rq'], "text"),
					GetSQLValueString($_POST['code_date'], "text"),
					GetSQLValueString($AgendaPartage, "text"),
					GetSQLValueString($EmploiID, "int"),
					GetSQLValueString($_POST['ID_agenda'], "int")
					);
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));	
				
				$indice=$_POST['ID_agenda'];	
				
				$deleteSQL = sprintf("DELETE FROM cdt_travail WHERE agenda_ID=%u AND prof_ID=%u",
					GetSQLValueString($indice, "int"),
					GetSQLValueString($ProfID, "int"));
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
			};	 	
                } else {
                	if (isset($_GET['edt_modif'])){$edt_modif='O';} else {$edt_modif='N';};
            
			
			$insertSQL = sprintf("INSERT INTO cdt_agenda (prof_ID, classe_ID, gic_ID, matiere_ID,groupe,semaine,jour_pointe,heure,duree,heure_debut,
                                heure_fin,theme_activ,type_activ,couleur_activ,a_faire,activite,rq,code_date,edt_modif,partage,emploi_ID)
                                VALUES (%u, %u, %u, %u, %s, %s, %s, %u, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %u)",
                                GetSQLValueString($ProfID, "int"),
                                GetSQLValueString($classe_ID_traitee, "int"),
                                GetSQLValueString($_POST['gic_ID'], "int"),
				GetSQLValueString($_POST['matiere_ID'], "int"),
				GetSQLValueString($_POST['groupe'], "text"),
				GetSQLValueString($_POST['semaine'], "text"),   
				GetSQLValueString($_POST['jour_pointe'], "text"),
				GetSQLValueString($_POST['heure'], "int"),
				GetSQLValueString($_POST['duree'], "text"),
				GetSQLValueString($_POST['heure_debut'], "text"),
                                GetSQLValueString($_POST['heure_fin'], "text"),
                                GetSQLValueString($_POST['theme_activ'], "text"),
                                GetSQLValueString($_POST['type_activ'], "text"),
                                GetSQLValueString($CouleurActivite, "text"),
                                GetSQLValueString('Oui', "text"),
                                $activite,
                                GetSQLValueString($_POST['rq'], "text"),
				GetSQLValueString($_POST['code_date'], "text"),
				GetSQLValueString($edt_modif, "text"),
				GetSQLValueString($AgendaPartage, "text"),
				GetSQLValueString($EmploiID, "int")
				);			
				
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_Max_agenda = "SELECT MAX(ID_agenda) FROM cdt_agenda";
			$sel_Max_agenda = mysqli_query($conn_cahier_de_texte, $query_Max_agenda) or die(mysqli_error($conn_cahier_de_texte));
			$un_Max_agenda = mysqli_fetch_assoc($sel_Max_agenda);
			$nb_Max_agenda = mysqli_num_rows($sel_Max_agenda);
			$indice=$un_Max_agenda['MAX(ID_agenda)'];
			mysqli_free_result($sel_Max_agenda);
		}
		
		if ($x_gic==0) {$indice_base=$indice; }; //indice base = conservation reference premiere classe pour eviter par la suite l'upload de fichiers identiques
		
		//Enregistrer le travail 1 a faire dans la table cdt_travail - 
		//pour formater sous forme d'une chaine de caracteres classique et eviter des champs pourtant vides comme "<p>&nbsp;</p>" ou "<br/>"
		
		for ($taf=1;$taf<4;$taf++)
		{
			
			// travail joint   ***************************************************************************
			
			if (isset($_POST['a_faire_'.$taf])){
				// cas de la programmation d'un devoir ou $_POST['a_faire_1'] est vide (pas de revision programmee)
				// On cree qd meme un enregistrement avec "Preparer le devoir".  
				if ($taf==1 && isset($_GET['ds_prog']) && $_POST['date_a_faire_1']!='' && $_POST['a_faire_1']=='') {$_POST['a_faire_1']='Pr&eacute;parer le '.$_SESSION['libelle_devoir'];} ;
				
				if (isset($_GET['ds_prog'])){$jour_prog=jour_semaine(date('d/m/Y  ')).' '.date('d/m/Y');} else {$jour_prog=$_POST['jour_pointe'];};
				$afaire=$_POST['a_faire_'.$taf];
                                $afaire=trim(html_entity_decode(strip_tags($afaire), ENT_QUOTES));
                        };
                        if ($_POST['date_a_faire_'.$taf]!='' && preg_match("/[[:alnum:]]+/",$afaire)){
                                $boody = array("<body>", "</body>");
                                $afaire = str_replace($boody, "", $_POST['a_faire_'.$taf]);
                                $t_jour_pointe=substr($datafaire[$taf],6,4).substr($datafaire[$taf],3,2).substr($datafaire[$taf],0,2);
                                $evalounon=(isset($_POST['eval'.$taf])) ?'O':'N';
                                $insertSQL = sprintf("INSERT INTO cdt_travail (agenda_ID, ind_position, prof_ID, classe_ID, gic_ID, matiere_ID,groupe,semaine,jour_pointe,heure,code_date,t_groupe,t_jour_pointe,t_code_date,travail,charge,eval)
					VALUES (%u, %u, %u, %u, %u, %u, %s, %s, %s, %u, %s, %s, %u, %s, %s, %s, %s)",
					GetSQLValueString($indice, "int"),
					GetSQLValueString($taf, "int"),
					GetSQLValueString($ProfID, "int"),
					GetSQLValueString($classe_ID_traitee, "int"),
					GetSQLValueString($_POST['gic_ID'], "int"),
					GetSQLValueString($_POST['matiere_ID'], "int"),
					GetSQLValueString($_POST['groupe'], "text"),
					GetSQLValueString($_POST['semaine'], "text"),   
					//GetSQLValueString($_POST['jour_pointe'], "text"),
					GetSQLValueString($jour_prog, "text"),
					GetSQLValueString($_POST['heure'], "int"),
					GetSQLValueString($_POST['code_date'], "text"),
                                        GetSQLValueString($_POST['t_groupe_'.$taf], "text"),
                                        GetSQLValueString($t_jour_pointe, "int"),
                                        GetSQLValueString($datafaire[$taf], "text"),
                                        GetSQLValueString($afaire, "text"),
                                        GetSQLValueString($_POST['charge_'.$taf], "text"),
                                        GetSQLValueString($evalounon, "text")
                                        );
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
				
				//suite a un changement de date, il faut remettre la nouvelle date pour les fichiers deja joints
				
				$updateSQL = sprintf("UPDATE cdt_fichiers_joints SET t_code_date=%s WHERE agenda_ID=%u AND ind_position=%u",
				GetSQLValueString($datafaire[$taf], "text"),
				GetSQLValueString($indice, "int"),
				GetSQLValueString($taf, "int"));
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
			
			};
			
			// fin du travail joint   ***************************************************************************
			
			// fichier cours joint   ***************************************************************************
			
			if ($_FILES['fichier'.$taf]['name']<>'') {
				
				$dossier_destination =  getcwd(); 
				$dossier_destination = str_replace('enseignant','',$dossier_destination).'fichiers_joints/'; 
				
				if ($x_gic==0) {
					$dossier_temporaire = $_FILES['fichier'.$taf]['tmp_name'];
					$type_fichier = $_FILES['fichier'.$taf]['type'];
					$nom_fichier1 = sans_accent($_FILES['fichier'.$taf]['name']);
					$nom_f1 = renommer($indice.'_'.$nom_fichier1);
					if (preg_match('/.php/i',$nom_f1)) {$nom_f1 .= ".txt"; };
					$erreur= $_FILES['fichier'.$taf]['error'];
					if ($erreur == 2 ) {
						exit ("Le fichier $taf d&eacute;passe la taille de 100 Mo.");
					}
					if ($erreur == 3 ) {
						exit ("Le fichier $taf a &eacute;t&eacute; partiellement transf&eacute;r&eacute;. Envoyez-le &agrave; nouveau.");
					}
					if( !move_uploaded_file($dossier_temporaire, $dossier_destination.$nom_f1) )
                                        {
                                                exit("Impossible de copier le fichier $taf dans $dossier_destination");
                                        }
                  //enregistrer le nom des 3 fichiers pour replication dans les regroupements
                      //$f_save[$taf]=$_FILES['fichier'.$taf]['name'];
					  $f_save[$taf]=$nom_fichier1;
                     }
                    else   {
							 $nom_f1 = $indice_base.'_'.$f_save[$taf];
                    };
                                                       

// Modif droits CHMOD 

if (!is_readable($dossier_destination.$nom_f1)){chmod($dossier_destination.$nom_f1, 0644);};



                    //--------------ecriture dans la table du nom du fichier
                     if ($_FILES['fichier'.$taf]['name']<>'') {
                     $insertSQL = sprintf("INSERT INTO cdt_fichiers_joints (agenda_ID, nom_fichier, prof_ID, type) VALUES (%u,%s,%u,%s)",
						GetSQLValueString($indice, "int"),
						GetSQLValueString($nom_f1, "text"),
						GetSQLValueString($ProfID, "int"),
						GetSQLValueString('Cours', "text")		
						);

					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
				}	
			}
			
			// fin fichier cours joint ****************************************************************
			
			// fichier travail joint  ***************************************************************************
			
			if ((isset($_FILES['t_fichier_'.$taf.'1']['name']))&&($_FILES['t_fichier_'.$taf.'1']['name']<>'')&&($_POST['date_a_faire_'.$taf]!='')&&(strlen($_POST['a_faire_'.$taf])>2)) {
				
				$dossier_destination =  getcwd(); 
				$dossier_destination = str_replace('enseignant','',$dossier_destination).'fichiers_joints/'; 
				
				if ($x_gic==0) {
					
					$dossier_temporaire = $_FILES['t_fichier_'.$taf.'1']['tmp_name'];
					$type_fichier = $_FILES['t_fichier_'.$taf.'1']['type'];
					$nom_fichier11 = sans_accent($_FILES['t_fichier_'.$taf.'1']['name']);
					$nom_f11 = renommer($indice.'_'.$nom_fichier11);

					if (preg_match('/.php/i',$nom_f11)) {$nom_f11 .= ".txt"; };
					$erreur= $_FILES['t_fichier_'.$taf.'1']['error'];
					if ($erreur == 2 ) {
						exit ("Le fichier du travail $taf d&eacute;passe la taille de 100 Mo.");
					}
					if ($erreur == 3 ) {
						exit ("Le fichier du travail $taf a &eacute;t&eacute; partiellement transf&eacute;r&eacute;. Envoyez-le &agrave; nouveau.");
					}
					
					if( !move_uploaded_file($dossier_temporaire, $dossier_destination . $nom_f11) ){
                        exit("Impossible de copier le fichier du travail $taf dans $dossier_destination");
                    }
                }

                            
// Modif droits CHMOD 
if (!is_readable($dossier_destination.$nom_f11)){chmod($dossier_destination.$nom_f11, 0644);};


                                //--------------ecriture dans la table du nom du fichier
                                if ($_FILES['t_fichier_'.$taf.'1']['name']<>'') {
                                        $insertSQL = sprintf("INSERT INTO cdt_fichiers_joints (agenda_ID, ind_position, nom_fichier, prof_ID, type, t_code_date) VALUES (%u,%u,%s,%u,%s,%s)",
						GetSQLValueString($indice, "int"),
						GetSQLValueString($taf, "int"),
                                                GetSQLValueString($nom_f11, "text"),
                                                GetSQLValueString($ProfID, "int"),
                                                GetSQLValueString('Travail', "text"),
                                                GetSQLValueString($datafaire[$taf], "text")
                                                );
                                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                        $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
				}	
			}
			
			// fin presence fichiers  travail 1_1 joint   ***************************************************************************
			
		};
		
                $indice_precedent=$indice;
		$x_gic+=1;
	}   while ($x_gic < $nb_gic); 
        
        // fin de la boucle sur les classes en cas de regroupements / passage unique si pas de regroupements 
        
        $listage_simple=1;
        
        //FIL RSS - Reecriture du fichier XML de cette classe si travail a faire
        if ((($_POST['date_a_faire_1']!='')&&($_POST['a_faire_1']!='')) || (($_POST['date_a_faire_2']!='')&&($_POST['a_faire_2']!=''))||(($_POST['date_a_faire_3']!='')&&($_POST['a_faire_3']!='')) || (isset($_POST['date_a_faire_4'])&&($_POST['date_a_faire_4']!='')&&($_POST['a_faire_4']!=''))) {
		require_once('../inc/gen_RSS.php');
	};
	
};
// Fin de l'enregistrement de la seance en cours   ***************************************************************************


//copie de la fiche   ***************************************************************************
if ((isset($_POST["MM_copie"])) && ($_POST["MM_copie"] == "form_copie")) {
	$_SESSION['copie']=$_POST['ID_agenda'];
	$listage_simple=1;
	$_GET['saisie']=0; //la saisie est terminee
}


// coller la fiche   ***************************************************************************
if (isset($_POST["MM_coller"]) && $_POST["MM_coller"]=="form_coller") {
        
        $listage_simple=1;
		$_GET['saisie']=0; //la saisie est terminee
		
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_Copie = sprintf("SELECT * FROM cdt_agenda$arcID WHERE ID_agenda=%u",$_SESSION['copie']);
        $sel_Copie = mysqli_query($conn_cahier_de_texte, $query_Copie) or die(mysqli_error($conn_cahier_de_texte));
        $un_Copie = mysqli_fetch_assoc($sel_Copie);
        
	//recherche des ID des classes du regroupement
	if ($_POST['gic_ID']<>0) {
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_gic =sprintf("SELECT classe_ID FROM cdt_groupe_interclasses_classe WHERE gic_ID=%u",$_POST['gic_ID']);
		$sel_gic = mysqli_query($conn_cahier_de_texte, $query_gic) or die(mysqli_error($conn_cahier_de_texte));
		$un_gic = mysqli_fetch_assoc($sel_gic);
		$nb_gic = mysqli_num_rows($sel_gic);
		$x=0;
		do {
			$tabc1[$x]=$un_gic['classe_ID'];
			$x=$x+1;
		} while ($un_gic = mysqli_fetch_assoc($sel_gic));
	}
	else { 
		$nb_gic=1;
	};
	
	if (isset($_POST['share_hour']) && $_POST['share_hour']<>'0') {$AgendaPartage='O';} else {$AgendaPartage='N';};
	
	//coller insertion (sur heure vierge)   ***************************************************************************
	
	if ((isset($_POST["coller_insertion"])) && ($_POST["coller_insertion"] == "coller_insertion")) {
		
		//BOUCLE sur les classes de regroupement (un seul passage si pas de regroupement)
		$x_gic=0; 
		
		//probleme a voir / si magi_quotes est desactive
		$un_Copie['activite'] = (!get_magic_quotes_gpc()) ? $un_Copie['activite']:my_addslashes($un_Copie['activite']) ;
		
		do { 
			
			if ($_POST['gic_ID']<>0)  {$classe_ID_traitee = $tabc1[$x_gic];} else {$classe_ID_traitee= $_POST['classe_ID'];};
			
			if (isset($_GET['edt_modif'])){$edt_modif='O';} else {$edt_modif='N';};
                        if (isset($_POST['edt_modif'])){$edt_modif=$_POST['edt_modif'];} //cas d'un copier de cellule


//*********************************************
                        $insertSQL = sprintf("INSERT INTO cdt_agenda (prof_ID, classe_ID, gic_ID, matiere_ID,groupe,semaine,jour_pointe,heure,duree,heure_debut,
                                heure_fin,theme_activ,type_activ,couleur_activ,a_faire,activite,code_date,partage,emploi_ID)
                                VALUES (%u, %u, %u, %u, %s, %s, %s, %u, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %u)",
                                GetSQLValueString($ProfID, "int"),
                                GetSQLValueString($classe_ID_traitee, "int"),
                                GetSQLValueString($_POST['gic_ID'], "int"),
				GetSQLValueString($_POST['matiere_ID'], "int"),
				GetSQLValueString($_POST['groupe'], "text"),
				GetSQLValueString($_POST['semaine'], "text"),   
				GetSQLValueString($_POST['jour_pointe'], "text"),
				GetSQLValueString($_POST['heure'], "int"),
				GetSQLValueString($_POST['duree'], "text"),
				GetSQLValueString($_POST['heure_debut'], "text"),
                                GetSQLValueString($_POST['heure_fin'], "text"),
                                GetSQLValueString(my_addslashes($un_Copie['theme_activ']), "text"),
                                GetSQLValueString(my_addslashes($un_Copie['type_activ']), "text"),
                                GetSQLValueString(my_addslashes($un_Copie['couleur_activ']), "text"),
                                GetSQLValueString(my_addslashes($un_Copie['a_faire']), "text"),
                                GetSQLValueString($un_Copie['activite'], "text"),
                                GetSQLValueString($_POST['code_date'], "text"),
				GetSQLValueString($AgendaPartage, "text"),
                                GetSQLValueString($EmploiID, "int")
                                );
                        
                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
                        
                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $query_Max_agenda = "SELECT MAX(ID_agenda) FROM cdt_agenda";
                        $sel_Max_agenda = mysqli_query($conn_cahier_de_texte, $query_Max_agenda) or die(mysqli_error($conn_cahier_de_texte));
                        $un_Max_agenda = mysqli_fetch_assoc($sel_Max_agenda);
                        $_SESSION['coller']=$un_Max_agenda['MAX(ID_agenda)'];
                        mysqli_free_result($sel_Max_agenda);
                        
                        //***********copie du travail a faire *******************
                                                        // mais pas de copie du travail a faire dans le cas ou la provenance est une archive
                                                if (!isset($_SESSION['archivID'])) {                      
                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $query_Travail2 = sprintf("SELECT * FROM cdt_travail WHERE agenda_ID=%u ORDER BY ind_position", GetSQLValueString($_SESSION['copie'],"int"));
                        $sel_Travail2 = mysqli_query($conn_cahier_de_texte, $query_Travail2) or die(mysqli_error($conn_cahier_de_texte));
			$un_Travail2 = mysqli_fetch_assoc($sel_Travail2);
			$nb_Travail2 = mysqli_num_rows($sel_Travail2);
			if ($nb_Travail2>0){ 
				
				do {
					
					$insertSQL = sprintf("INSERT INTO cdt_travail (agenda_ID, ind_position, prof_ID, classe_ID, gic_ID, matiere_ID,groupe,semaine,jour_pointe,heure,code_date,t_groupe,t_jour_pointe,t_code_date,travail,charge,eval)
						VALUES (%u, %u, %u, %u, %u, %s, %s, %s, %s, %u, %s, %s, %s, %s, %s, %s, %s)",
						GetSQLValueString($_SESSION['coller'], "int"),
						GetSQLValueString($un_Travail2['ind_position'], "int"),
						GetSQLValueString($ProfID, "int"),
						GetSQLValueString($classe_ID_traitee, "int"),
						GetSQLValueString($_POST['gic_ID'], "int"),
						GetSQLValueString($_POST['matiere_ID'], "text"),
						GetSQLValueString($_POST['groupe'], "text"),
						GetSQLValueString($_POST['semaine'], "text"),   
						GetSQLValueString($_POST['jour_pointe'], "text"),
						GetSQLValueString($_POST['heure'], "int"),
						GetSQLValueString($_POST['code_date'], "text"),
						GetSQLValueString($_POST['groupe'], "text"),
						GetSQLValueString($un_Travail2['t_jour_pointe'], "text"),
						GetSQLValueString($un_Travail2['t_code_date'], "text"),
						GetSQLValueString(my_apostrophes($un_Travail2['travail']), "text"),
						GetSQLValueString(my_apostrophes($un_Travail2['charge']), "text"),
						GetSQLValueString(($un_Travail2['eval']), "text")
						);
					
					
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
					
					
                                } while ($un_Travail2 = mysqli_fetch_assoc($sel_Travail2));
                                
                        }          //***********fin copie du travail a faire ***************
                        }
                        $x_gic+=1;
                }   while ($x_gic < $nb_gic); // fin de la boucle sur les classes en cas de regroupements / passage unique si pas de regroupements 
                
	}   //fin coller_insertion    ***************************************************************************
	
	
	
	//coller update (sur heure deja remplie)   ***************************************************************************
	
	if ((isset($_POST["coller_update"])) && ($_POST["coller_update"] == "coller_update")) {
		
		//BOUCLE sur les classes de regroupement (un seul passage si pas de regroupement)
		$x_gic=0; 
		
		do { 
			
			if ($_POST['gic_ID']<>0)  {$classe_ID_traitee = $tabc1[$x_gic];} else {$classe_ID_traitee= $_POST['classe_ID'];};
			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_IDagenda_modif = sprintf("(SELECT ID_agenda FROM cdt_agenda WHERE prof_ID=%u AND classe_ID=%u AND gic_ID=%u AND matiere_ID=%u AND code_date=%s AND heure=%u)",
				GetSQLValueString($ProfID, "int"),
				GetSQLValueString($classe_ID_traitee, "int"),
				GetSQLValueString($_POST['gic_ID'], "int"),
				GetSQLValueString($_POST['matiere_ID'], "int"),
				GetSQLValueString($_POST['code_date'], "text"),
				GetSQLValueString($_POST['heure'], "int")
				);
			
			$query_IDagenda_modif .= sprintf("UNION (SELECT ID_agenda FROM cdt_agenda WHERE prof_ID=%u AND classe_ID=%u AND gic_ID=%u AND matiere_ID=%u AND code_date=%s AND heure=%u)",
				GetSQLValueString($_SESSION['ID_prof'], "int"),    // Dans le cas d'une heure partagee qui a subi une modif ponctuelle d'EDT
				GetSQLValueString($classe_ID_traitee, "int"),
				GetSQLValueString($_POST['gic_ID'], "int"),
				GetSQLValueString($_POST['matiere_ID'], "int"),
				GetSQLValueString($_POST['code_date'], "text"),
				GetSQLValueString($_POST['heure'], "int")
				);
			
                        $sel_IDagenda_modif = mysqli_query($conn_cahier_de_texte, $query_IDagenda_modif) or die(mysqli_error($conn_cahier_de_texte));
                        $un_IDagenda_modif = mysqli_fetch_assoc($sel_IDagenda_modif);
                        
                        $updateSQL = sprintf("UPDATE cdt_agenda SET theme_activ=%s, type_activ=%s, couleur_activ=%s, a_faire=%s, activite=%s WHERE classe_ID=%u AND matiere_ID=%u AND code_date=%s AND heure=%u AND prof_ID=%u",
                                GetSQLValueString(my_addslashes($un_Copie['theme_activ']), "text"),
                                GetSQLValueString(my_addslashes($un_Copie['type_activ']), "text"),
                                GetSQLValueString(my_addslashes($un_Copie['couleur_activ']), "text"),
                                GetSQLValueString(my_addslashes($un_Copie['a_faire']), "text"),
                                GetSQLValueString(my_apostrophes($un_Copie['activite']), "text"),
                                GetSQLValueString($classe_ID_traitee, "int"),
                                GetSQLValueString($_POST['matiere_ID'], "int"),                                           
                                GetSQLValueString($_POST['code_date'], "text"),
				GetSQLValueString($_POST['heure'], "int"),
				GetSQLValueString($ProfID, "int")
				);
			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));        
                        
                        //coller update du travail a faire
                        // mais pas de copie du travail a faire dans le cas ou la provenance est une archive
                                                if (!isset($_SESSION['archivID'])) {  
                        //on efface dans cdt_travail pour cet ID_agnda
                        $deleteSQL = sprintf("DELETE FROM cdt_travail WHERE agenda_ID=%u AND prof_ID=%u AND classe_ID=%u",
                                $un_IDagenda_modif['ID_agenda'],
                        	GetSQLValueString($ProfID, "int"),
                        	$classe_ID_traitee);
                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
                        
                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $query_Travail2 = sprintf("SELECT * FROM cdt_travail WHERE agenda_ID=%u ORDER BY ind_position",GetSQLValueString($_SESSION['copie'],"int"));
                        $sel_Travail2 = mysqli_query($conn_cahier_de_texte, $query_Travail2) or die(mysqli_error($conn_cahier_de_texte));
			$un_Travail2 = mysqli_fetch_assoc($sel_Travail2);
			$nb_Travail2 = mysqli_num_rows($sel_Travail2);
			
                        if ($nb_Travail2>0) { 
                                $_SESSION['coller']=$_POST['ID_agenda'];
                                
                                do {  
                                        $insertSQL = sprintf("INSERT INTO cdt_travail (agenda_ID, ind_position, prof_ID, classe_ID, gic_ID, matiere_ID,groupe,semaine,jour_pointe,heure,code_date,t_groupe,t_jour_pointe,t_code_date,travail,charge,eval)
                                                VALUES (%u, %u, %u, %u, %u, %u, %s, %s, %s, %u, %s, %s, %s, %s, %s, %s, %s)",
                                                $un_IDagenda_modif['ID_agenda'],
						GetSQLValueString($un_Travail2['ind_position'], "int"),
						GetSQLValueString($ProfID, "int"),
						GetSQLValueString($classe_ID_traitee, "int"),


						GetSQLValueString($_POST['gic_ID'], "int"),
						GetSQLValueString($_POST['matiere_ID'], "int"),
						GetSQLValueString($_POST['groupe'], "text"),
						GetSQLValueString($_POST['semaine'], "text"),   
						GetSQLValueString($_POST['jour_pointe'], "text"),
						GetSQLValueString($_POST['heure'], "int"),
						GetSQLValueString($_POST['code_date'], "text"),
						GetSQLValueString($_POST['groupe'], "text"),
						GetSQLValueString($un_Travail2['t_jour_pointe'], "text"),
						GetSQLValueString($un_Travail2['t_code_date'], "text"),
						GetSQLValueString(my_apostrophes($un_Travail2['travail']), "text"),
						GetSQLValueString(my_apostrophes($un_Travail2['charge']), "text"),
						GetSQLValueString(($un_Travail2['eval']), "text")
						);
					
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
                                        
                                } while ($un_Travail2 = mysqli_fetch_assoc($sel_Travail2)); 
                        }
                        }
                        $x_gic+=1;
                } while ($x_gic < $nb_gic); 
        } //fin coller_update
        
        // copie des noms de fichiers joints / insertion ou update
        // mais pas de copie de fichiers joints dans le cas ou la provenance est une archive
       if (!isset($_SESSION['archivID'])) {     
        // suppression physique des anciens fichiers joints (sauf si utilises par ailleurs)
        
        // ***************************************************************
        //  Les fichiers ne seront pas supprimes 
        //  Cela permettra a l'administrateur de faire une recuperation 
        //  dans le dossier fichier_joints
        //  suite a un copier coller malencontreux.
        //  **************************************************************
        
        
        //Boucle sur chaque ID_agenda du regroupement
        
        // rechercher l'ID_agenda a dupliquer  
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_IDagenda_modif = sprintf(" 
                SELECT ID_agenda FROM cdt_agenda
                WHERE prof_ID=%u AND gic_ID=%u AND groupe=%s AND matiere_ID=%u AND code_date=%s",
                GetSQLValueString($ProfID, "int"),
                GetSQLValueString($_POST['gic_ID'], "int"),
                GetSQLValueString($_POST['groupe'], "text"),            
                GetSQLValueString($_POST['matiere_ID'], "int"),
                GetSQLValueString($_POST['code_date'], "text")
		);
	
	$sel_IDagenda_modif = mysqli_query($conn_cahier_de_texte, $query_IDagenda_modif) or die(mysqli_error($conn_cahier_de_texte));
	$un_IDagenda_modif = mysqli_fetch_assoc($sel_IDagenda_modif);
	
	
	//insertion de la copie des noms de fichiers joints
	//correction ci-dessous pour la copie des noms de fichiers avec apostrophe
	// suppression du GetValueString bien interprete en local - mal interprete sur serveur distant -remplace par my_addslashes
	
	do {
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);                  
		$query_Copie_fichiersjoints = sprintf("SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=%u",GetSQLValueString($_SESSION['copie'], "int"));
		$sel_Copie_fichiersjoints = mysqli_query($conn_cahier_de_texte, $query_Copie_fichiersjoints) or die(mysqli_error($conn_cahier_de_texte));
		$un_Copie_fichiersjoints = mysqli_fetch_assoc($sel_Copie_fichiersjoints);
		$nb_Copie_fichiersjoints = mysqli_num_rows($sel_Copie_fichiersjoints); 
		
		if ($nb_Copie_fichiersjoints>0) {
			
			//suppression des noms des anciens fichiers joints dans la table
			$deleteSQL = sprintf("DELETE FROM cdt_fichiers_joints WHERE agenda_ID=%u AND prof_ID=%u",
				GetSQLValueString($un_IDagenda_modif['ID_agenda'], "int"),
				GetSQLValueString($ProfID, "int")); 
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
			
			do {
				$insertSQL = sprintf("INSERT INTO cdt_fichiers_joints (agenda_ID, nom_fichier,ind_position,type,t_code_date, prof_ID) VALUES (%u,'%s', %u, %s, %s, %u)",
					GetSQLValueString($un_IDagenda_modif['ID_agenda'], "int"),
					my_addslashes($un_Copie_fichiersjoints['nom_fichier']),
					GetSQLValueString($un_Copie_fichiersjoints['ind_position'], "int"),                       		     
					GetSQLValueString($un_Copie_fichiersjoints['type'], "text"),
                                        GetSQLValueString($un_Copie_fichiersjoints['t_code_date'], "text"),
                                        GetSQLValueString($ProfID, "int"));
                                
                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
                        } while ($un_Copie_fichiersjoints = mysqli_fetch_assoc($sel_Copie_fichiersjoints)); 
		};
		mysqli_free_result($sel_Copie_fichiersjoints);
        } while ($un_IDagenda_modif = mysqli_fetch_assoc($sel_IDagenda_modif));
        
        mysqli_free_result($sel_IDagenda_modif);
        }
        mysqli_free_result($sel_Copie);
        
        if (isset($_SESSION['archivID'])){ unset($_SESSION['archivID']);unset($_SESSION['copie']);};
        
}; //fin coller


// La connexion $conn_cahier_de_text est perdue dans le cas de remplissage de seance AVEC travail à faire !!Si ligne ci-dessous manquante, la connexion ne se fait pas
//$conn_cahier_de_texte = mysqli_connect($hostname_conn_cahier_de_texte, $username_conn_cahier_de_texte, $password_conn_cahier_de_texte,$database_conn_cahier_de_texte) or die(mysqli_connect_errno());

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Vacances = "SELECT * FROM cdt_agenda WHERE cdt_agenda.classe_ID=0 ORDER BY heure_debut ASC";
$Vacances = mysqli_query($conn_cahier_de_texte, $query_Vacances) or die(mysqli_error($conn_cahier_de_texte));
$un_Vacances = mysqli_fetch_assoc($Vacances);
$totalRows_Vacances = mysqli_num_rows($Vacances);
//echo '$totalRows_Vacances='.$totalRows_Vacances.'   ';

$n=0;

do {  
	$j_debut=substr($un_Vacances['heure_debut'],0,2);
	$m_debut=substr($un_Vacances['heure_debut'],3,2);
	$a_debut=substr($un_Vacances['heure_debut'],6,4);
	$j_fin=substr($un_Vacances['heure_debut'],0,2);
	$m_fin=substr($un_Vacances['heure_debut'],3,2);
	$a_fin=substr($un_Vacances['heure_debut'],6,4);
	$code_debut=$a_debut.$m_debut.$j_debut;
	$j_fin=substr($un_Vacances['heure_fin'],0,2);
	$m_fin=substr($un_Vacances['heure_fin'],3,2);
	$a_fin=substr($un_Vacances['heure_fin'],6,4);
	$j_fin=substr($un_Vacances['heure_fin'],0,2);
	$m_fin=substr($un_Vacances['heure_fin'],3,2);
	$a_fin=substr($un_Vacances['heure_fin'],6,4);
	$code_fin=$a_fin.$m_fin.$j_fin;
	
	$n=$n+1;
	
	$tab_debut[$n]=$code_debut; $tab_fin[$n]=$code_fin; $tab_libel[$n]=$un_Vacances['theme_activ'];
	
} while ($un_Vacances = mysqli_fetch_assoc($Vacances)); 

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_groupe = "SELECT * FROM cdt_groupe ORDER BY groupe ASC";
$sel_groupe = mysqli_query($conn_cahier_de_texte, $query_groupe) or die(mysqli_error($conn_cahier_de_texte));
$un_groupe = mysqli_fetch_assoc($sel_groupe);
$nb_groupe = mysqli_num_rows($sel_groupe); 

?>
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../styles/style_default.css" rel="stylesheet" type="text/css">
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />


<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>
<script type="text/javascript" src="../jscripts/utils.js"></script>
<script type="text/javascript" src="../jscripts/ajax_functions.js"></script>

<!--  
Appel de la feuille de style perso pour creation de ses styles personnels dans l'editeur XINHA 
-->
<?php 
if($_SESSION['xinha_stylist']=="O"){ ?>
        <link href="../templates/default/perso.css" rel="stylesheet" type="text/css">
<?php ;};
if($_SESSION['xinha_equation']=="O"){ ?>
        <script type="text/javascript" src="xinha/plugins/Equation/ASCIIMathML.js"></script>
<?php ;};?>
<!-- Feuille de style specifique pour resolution d'ecran 800x600 -->

<script language="javascript">
if (window.screen){
        if (screen.width <= 800) {
		css="../styles/800_600.css" ; 
	document.writeln('<link rel="stylesheet" href="'+css+' "type ="text/css">');}
}
</script>
<script language="javascript">
function test_progression(obj) {
	var i = obj.firstChild.nodeName;
	if (i=="FORM") return false;
	else go_progression();
}


function Lien() {
	i = document.Choix.Liste.selectedIndex;
	document.Choix.Liste.options[0].selected = true;
	if (i == 0) return;
	url = document.Choix.Liste.options[i].value;
	window.open(url,'_blank');
}


function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}



function confirmation(sup_message,ref)
{
        if (confirm("Voulez-vous supprimer r\351ellement ce message"+" N\260"+ref+sup_message+" ?")) { // Clic sur OK
                MM_goToURL('window','message_supprime.php?ID_message='+ref+'&retour_ecrire');
        }
}


function MM_reloadPage(init) {  //reloads the window if Nav4 resized
	if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
	document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
	else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

function formtest() {
	
	var theme_activ;
	theme_activ=document.form1.theme_activ.value;
	if (theme_activ==""){
		alert("Il vous faut donner un titre \340 la s\351ance")
		return false;
	}
}

function formfocus() {
	document.form1.theme_activ.focus()
	document.form1.theme_activ.select()
}

function MM_findObj(n, d) { //v4.01
	var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
	d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
	if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
	for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
	if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_showHideLayers() { //v6.0
	var i,p,v,obj,args=MM_showHideLayers.arguments;
	for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
		if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
	obj.visibility=v; }
}

function edition_impossible () {alert ("Vous ne pouvez plus \351diter cette fiche. Sa date est ant\351rieure \340 la date du visa (<?php echo substr($_SESSION['date_visa'],8,2).'/'.substr($_SESSION['date_visa'],5,2).'/'.substr($_SESSION['date_visa'],0,4);?>) pos\351e par votre Responsable Etablissement. Si cette modification est imp\351rative, demandez lui de supprimer temporairement votre visa.");}

function suppression_impossible () {alert ("Vous ne pouvez plus supprimer cette fiche. Sa date est ant\351rieure \340 la date du visa (<?php echo substr($_SESSION['date_visa'],8,2).'/'.substr($_SESSION['date_visa'],5,2).'/'.substr($_SESSION['date_visa'],0,4);?>) pos\351e par votre Responsable Etablissement. Si cette suppression est imp\351rative, demandez lui de supprimer temporairement votre visa.");}

function coller_impossible () {alert ("Vous ne pouvez modifier cette fiche. Sa date est ant\351rieure \340 la date du visa (<?php echo substr($_SESSION['date_visa'],8,2).'/'.substr($_SESSION['date_visa'],5,2).'/'.substr($_SESSION['date_visa'],0,4);?>) pos\351e par votre Responsable Etablissement. Si cette modification est imp\351rative, demandez lui de supprimer temporairement votre visa.");}

function saisie_abandon() {
if (!confirm('Vous avez actuellement une saisie de s\351ance en cours. Si vous poursuivez sans enregistrer, vous allez perdre les informations saisies. Voulez-vous poursuivre ?')) return false;
}

//-->
</script>


<style type="text/css">
<!--
#Layer1 {
	position:absolute;
	width:200px;
	height:115px;
	z-index:1;
}

a.discret:link { color :#000000 ;}
a.discret:visited {text-decoration:none; color :darkgreen ;}
a.discret:active { text-decoration:none; color  :darkgreen ;font-style:italic;}
.Style70 {color: #BBCEDE}
-->
</style>
</head>
<body style="background-color: #FAF6EF;">

<div id="container" >
<div id="colGauche" >
<div id="haut">
<a style="text-decoration:none" href="../deconnexion.php" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>><img src="../images/deconnecte.gif" alt="Se d&eacute;connecter" width="18" height="20" border="0" title="Se d&eacute;connecter"></a>&nbsp;&nbsp;
<a href="ecrire.php?saisie=0&date=<?php echo date('Ymd');?>" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>><img src="../images/home-menu.gif" alt="Accueil - Messages"  border="0" title="Accueil - Messages"></a><a href="enseignant.php" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>Menu Enseignant</a></div>
<?php

if ((isset($_SESSION['mobile_browser']))&&($_SESSION['mobile_browser']==false)){require_once ("../authentification/sessionVerif.php");};

// recherche d'une variable utilisee dans calendrier.php pour autoriser ou non l'acces a un ancien cahier de textes
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='old_cdt_access' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access = $row[0];
mysqli_free_result($result_read);

// recherche d'archivage par duplication de tables : Requete necessaire pour l'appel de calendrier.php
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Archiv = "SELECT * FROM cdt_archive ORDER BY cdt_archive.NumArchive ASC";
$sel_Archiv = mysqli_query($conn_cahier_de_texte, $query_Archiv) or die(mysqli_error($conn_cahier_de_texte));
$totalRows_RsArchiv = mysqli_num_rows($sel_Archiv);
mysqli_free_result($sel_Archiv);

require_once('calendrier.php');
echo calendar($access);

$jour_Jour = "0";
if (isset($_GET['current_day_name']))
{
	$current_day_name=$_GET['current_day_name'];
	$jour_Jour = (get_magic_quotes_gpc()) ? $current_day_name : addslashes($current_day_name);
	
}
else
{
	$jour_Jour = (get_magic_quotes_gpc()) ? $current_day_name : addslashes($current_day_name);
}
$jour_Jour='"'.$jour_Jour.'"';

//La gestion semaine ab definie par l'administrateur est-elle prise en compte ?
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Sem = sprintf("SELECT gestion_sem_ab FROM cdt_prof WHERE ID_prof=%u", GetSQLValueString($_SESSION['ID_prof'],"int"));
$sel_Sem = mysqli_query($conn_cahier_de_texte, $query_Sem) or die(mysqli_error($conn_cahier_de_texte));
$un_Sem = mysqli_fetch_assoc($sel_Sem);

if (isset($_GET['date'])){$madate=substr($_GET['date'],0,4).'-'.substr($_GET['date'],4,2).'-'.substr($_GET['date'],6,2);} else {
if (isset($_GET['code_date'])){$madate=substr($_GET['code_date'],0,4).'-'.substr($_GET['code_date'],4,2).'-'.substr($_GET['code_date'],6,2);}else{$madate=date('Y-m-d');}};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_partage = sprintf("SELECT * FROM cdt_emploi_du_temps_partage WHERE profpartage_ID=%u",GetSQLValueString($_SESSION['ID_prof'],"int"));
$sel_partage = mysqli_query($conn_cahier_de_texte, $query_partage) or die(mysqli_error($conn_cahier_de_texte));
$un_partage = mysqli_fetch_assoc($sel_partage);

if ($un_Sem['gestion_sem_ab']=='O'){
	
	//recup de la semaine
        if (!isset($_GET['code_date'])){ //chercher la semaine
                if (!isset($_GET['date'])){$date_sem=date('Ymd');} else {$date_sem=$_GET['date'];};
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_Semdate =  sprintf("SELECT * FROM cdt_semaine_ab WHERE s_code_date<=%s ORDER BY `s_code_date` DESC LIMIT 1",$date_sem);
                $sel_Semdate = mysqli_query($conn_cahier_de_texte, $query_Semdate) or die(mysqli_error($conn_cahier_de_texte));
                $un_Semdate= mysqli_fetch_assoc($sel_Semdate);
          if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){
					if ($un_Semdate['semaine']=='A et B'){$_SESSION['semdate_libelle']='P et I';} else if($un_Semdate['semaine']=='A'){$_SESSION['semdate_libelle']='Paire';} else {$_SESSION['semdate_libelle']='Impaire';};
			        $_SESSION['semdate_alter_libelle']=substr($un_Semdate['semaine_alter'],4);
			
			
		}
		else {$_SESSION['semdate_libelle']=$un_Semdate['semaine'];};
		$_SESSION['semdate']=$un_Semdate['semaine'];$_SESSION['semdate_alter_libelle']=substr($un_Semdate['semaine_alter'],4);
	};
	
	$exp="'%".$_SESSION['semdate_alter_libelle']."%'";


	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	
	$query_Jour =  sprintf("(SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
		LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
		WHERE cdt_emploi_du_temps.prof_ID=%u 
		AND cdt_emploi_du_temps.jour_semaine=%s 
                AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
                AND (cdt_emploi_du_temps.semaine='%s' OR cdt_emploi_du_temps.semaine='A et B' OR cdt_emploi_du_temps.semaine LIKE %s) 
                AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
                AND cdt_emploi_du_temps.edt_exist_fin >= '%s')",GetSQLValueString($_SESSION['ID_prof'],"int"),$jour_Jour,$_SESSION['semdate'],$exp,$madate,$madate);
 
	           $query_classeID0 =  sprintf("(SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
                LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
		WHERE cdt_emploi_du_temps.prof_ID=%u 
		AND cdt_emploi_du_temps.jour_semaine=%s 
		AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
		AND (cdt_emploi_du_temps.semaine='%s' OR cdt_emploi_du_temps.semaine='A et B' OR cdt_emploi_du_temps.semaine LIKE %s) 
                AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
                AND cdt_emploi_du_temps.edt_exist_fin >= '%s'
                AND cdt_emploi_du_temps.classe_ID ='0'
                AND cdt_emploi_du_temps.gic_ID ='0')",GetSQLValueString($_SESSION['ID_prof'],"int"),$jour_Jour,$_SESSION['semdate'],$exp,$madate,$madate);

                
        do {
                
		$query_Jour .= sprintf(" UNION (SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
			LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
			WHERE cdt_emploi_du_temps.ID_emploi=%u 
			AND cdt_emploi_du_temps.jour_semaine=%s 
			AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
			AND (cdt_emploi_du_temps.semaine='%s' OR cdt_emploi_du_temps.semaine='A et B' OR cdt_emploi_du_temps.semaine LIKE %s) 
			AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
			AND cdt_emploi_du_temps.edt_exist_fin >= '%s')",$un_partage['ID_emploi'],$jour_Jour,$_SESSION['semdate'],$exp,$madate,$madate);
		
		$query_classeID0 .=  sprintf(" UNION (SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
			LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
			WHERE cdt_emploi_du_temps.ID_emploi=%u  
			AND cdt_emploi_du_temps.jour_semaine=%s 
			AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
			AND (cdt_emploi_du_temps.semaine='%s' OR cdt_emploi_du_temps.semaine='A et B' OR cdt_emploi_du_temps.semaine LIKE %s) 
			AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
			AND cdt_emploi_du_temps.edt_exist_fin >= '%s'
			AND cdt_emploi_du_temps.classe_ID ='0'
			AND cdt_emploi_du_temps.gic_ID ='0')",$un_partage['ID_emploi'],$jour_Jour,$_SESSION['semdate'],$exp,$madate,$madate);
		
	} while ($un_partage = mysqli_fetch_assoc($sel_partage));
	mysqli_free_result($sel_partage);
	$query_Jour .="ORDER BY heure,semaine";
	$query_classeID0 .="ORDER BY heure,semaine";
}

else { //gestion des semaines pas prises en compte
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Jour =  sprintf("(SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
		LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
		WHERE cdt_emploi_du_temps.prof_ID=%u 
                AND cdt_emploi_du_temps.jour_semaine=%s 
                AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
                AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
                AND cdt_emploi_du_temps.edt_exist_fin >= '%s')",GetSQLValueString($_SESSION['ID_prof'],"int"),$jour_Jour,$madate,$madate);
        
        $query_classeID0 =  sprintf("(SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
                LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
		WHERE cdt_emploi_du_temps.prof_ID=%u 
		AND cdt_emploi_du_temps.jour_semaine=%s 
		AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
                AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
                AND cdt_emploi_du_temps.edt_exist_fin >= '%s'
                AND cdt_emploi_du_temps.classe_ID ='0'
                AND cdt_emploi_du_temps.gic_ID ='0')",GetSQLValueString($_SESSION['ID_prof'],"int"),$jour_Jour,$madate,$madate);
        
        do {
                
		$query_Jour .= sprintf(" UNION (SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
			LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
			WHERE cdt_emploi_du_temps.ID_emploi=%u 
			AND cdt_emploi_du_temps.jour_semaine=%s 
			AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
			AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
			AND cdt_emploi_du_temps.edt_exist_fin >= '%s')",$un_partage['ID_emploi'],$jour_Jour,$madate,$madate);
		
		$query_classeID0 .=  sprintf(" UNION (SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
			LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
			WHERE cdt_emploi_du_temps.ID_emploi=%u  
			AND cdt_emploi_du_temps.jour_semaine=%s 
			AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
			AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
			AND cdt_emploi_du_temps.edt_exist_fin >= '%s'
			AND cdt_emploi_du_temps.classe_ID ='0'
			AND cdt_emploi_du_temps.gic_ID ='0')",$un_partage['ID_emploi'],$jour_Jour,$madate,$madate);
		
	} while ($un_partage = mysqli_fetch_assoc($sel_partage));
	mysqli_free_result($sel_partage);
        $query_Jour .="ORDER BY heure,semaine";
        $query_classeID0 .="ORDER BY heure,semaine";
}; // du $un_Sem['gestion_sem_ab']=='O'

$sel_Jour = mysqli_query($conn_cahier_de_texte, $query_Jour) or die(mysqli_error($conn_cahier_de_texte));
$row_RsJour = mysqli_fetch_assoc($sel_Jour);
$nb_Jour = mysqli_num_rows($sel_Jour);
//$nb_Jour est le nombre d'heures de cours dans la journee

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$sel_classeID0 = mysqli_query($conn_cahier_de_texte, $query_classeID0) or die(mysqli_error($conn_cahier_de_texte));
$un_classeID0 = mysqli_fetch_assoc($sel_classeID0);
$nb_classeID0 = mysqli_num_rows($sel_classeID0);
//$nb_classeID0 est le nombre d'heures de cours dans la journee ou classeId=0 et gicID=O (Situation possible avec l'import)

//____________________________

if (isset($_GET['jour_pointe'])) {
        $prof_ID_modif = $_SESSION['ID_prof'];
        //Verification que c'est une heure partagee ou non
        $heurepartagee=false;
        $ProfID=$_SESSION['ID_prof'];
        if (isset($_GET['share']) && $_GET['share']=='O' ){
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_partage = sprintf("SELECT cdt_emploi_du_temps.prof_ID FROM cdt_emploi_du_temps_partage,cdt_emploi_du_temps WHERE cdt_emploi_du_temps_partage.profpartage_ID=%u AND cdt_emploi_du_temps_partage.ID_emploi=%u AND cdt_emploi_du_temps_partage.ID_emploi=cdt_emploi_du_temps.ID_emploi",GetSQLValueString($_SESSION['ID_prof'],"int"),GetSQLValueString($_GET['edtID'],"int"));
		$sel_partage = mysqli_query($conn_cahier_de_texte, $query_partage) or die(mysqli_error($conn_cahier_de_texte));
		$un_partage = mysqli_fetch_assoc($sel_partage);
		$totalun_partage = mysqli_num_rows($sel_partage);
		if ($totalun_partage==1) {
			$ProfID=$un_partage['prof_ID'];
			$prof_ID_modif = $un_partage['prof_ID'];
			$heurepartagee=true;
		};
		mysqli_free_result($sel_partage);
	};
	$classe_ID_modif = "0";
	if (isset($_GET['classe_ID'])) {
		if ($_GET['classe_ID']==0) {
			$classe_ID_modif=$un_gic_classe_ID_default['classe_ID'];
		} else {
		$classe_ID_modif = (get_magic_quotes_gpc()) ? $_GET['classe_ID'] : addslashes($_GET['classe_ID']); };
	}
	
	$matiere_ID_modif = "0";
	if (isset($_GET['matiere_ID'])) {
		$matiere_ID_modif = (get_magic_quotes_gpc()) ? $_GET['matiere_ID'] : addslashes($_GET['matiere_ID']);
	}
	
	$heure_modif = "0";
	if (isset($_GET['heure'])) {
		$heure_modif = (get_magic_quotes_gpc()) ? $_GET['heure'] : addslashes($_GET['heure']);
	}
	
	$semaine_modif = "0";
	if (isset($_GET['semaine'])) {
		$semaine_modif = (get_magic_quotes_gpc()) ? $_GET['semaine'] : addslashes($_GET['semaine']);
	}
	
	$groupe_modif = "0";
	if (isset($_GET['groupe'])) {
		$groupe_modif = (get_magic_quotes_gpc()) ? $_GET['groupe'] : addslashes($_GET['groupe']);
	}
	
	$jour_modif = "0";
	if (isset($_GET['jour_pointe'])) {
		$jour_modif = (get_magic_quotes_gpc()) ? $_GET['jour_pointe'] : addslashes($_GET['jour_pointe']);
	}
	
	if (isset($_GET['ds_prog'])){$modif_req=" AND type_activ='ds_prog'";} else {$modif_req=" AND type_activ<>'ds_prog' ";};
	if (isset($_GET['hs'])){$modif_req2="AND substring(code_date,9,1)=0";} else {$modif_req2="";};
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_modif = sprintf("SELECT * FROM cdt_agenda WHERE prof_ID=%u AND classe_ID=%u AND matiere_ID=%u AND jour_pointe='%s' AND semaine='%s' AND groupe ='%s' AND heure=%s  %s %s",$prof_ID_modif,$classe_ID_modif,$matiere_ID_modif, $jour_modif,$semaine_modif,$groupe_modif,$heure_modif,$modif_req,$modif_req2);
	$sel_modif = mysqli_query($conn_cahier_de_texte, $query_modif) or die(mysqli_error($conn_cahier_de_texte));
	$un_modif = mysqli_fetch_assoc($sel_modif);
	$nb_modif = mysqli_num_rows($sel_modif);
	if ($nb_modif <> '1'){ $misajour= '0';} else {$misajour=$un_modif['ID_agenda'];};
};

//____________________________
// Pas d'affichage en colonne de gauche si nous sommes en vacances aujourd'hui
// $en_vacance_today est une variable globale definie dans calendrier.php
$date_declare_abs=str_replace("-","",$_SESSION['date_declare_absent']);
$date_declare_abs_f=substr($date_declare_abs,6,2).'/'.substr($date_declare_abs,4,2).'/'.substr($date_declare_abs,0,4);

if ($en_vacance_today==1){ //on recherche le libelle des vacances
	if (!isset($_GET['date'])){$dt=substr($_GET['code_date'],0,8);} else {$dt=$_GET['date'];};
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Eventoday =sprintf("SELECT theme_activ,code_date FROM cdt_agenda WHERE classe_ID=0 AND %s>=CONCAT(substring(heure_debut,7,4),substring(heure_debut,4,2),substring(heure_debut,1,2)) ORDER BY code_date DESC LIMIT 1",$dt);
	
	$sel_Eventoday = mysqli_query($conn_cahier_de_texte, $query_Eventoday) or die(mysqli_error($conn_cahier_de_texte));
	$un_Eventoday = mysqli_fetch_assoc($sel_Eventoday);
	$nb_Eventoday = mysqli_num_rows($sel_Eventoday);
	echo "<p class='cell_vacances'><br/>".$un_Eventoday['theme_activ']."<br/><br/></p>Pas de cours aujourd'hui";
	?>
	
	
	<?php
} elseif ((isset($_GET['date']))&&($_GET['date']>=$date_declare_abs)&&($_SESSION['id_etat']==1)){ //le prof est declare absent
	echo '<br/><br/><p  style="color: #FF0000">Vous ne pouvez modifier <br/>que les s&eacute;ances ant&eacute;rieures au '.$date_declare_abs_f.'</p>';
};


///////////////////////////////////////////
//on affiche toutes les cellules de gauche
///////////////////////////////////////////
	
	if 	(($nb_Jour<>0)&&($nb_Jour!==$nb_classeID0)) {
		
		?>
		<p>
		<?php if ((isset($_GET['date'])) or (isset($_GET['jour_pointe'])) ) {  ?>
	</p>
		<?php   if (($un_Sem['gestion_sem_ab']=='O')&&($en_vacance_today<>1)){ echo 'Semaine '.$_SESSION['semdate_libelle'].' ('.$_SESSION['semdate_alter_libelle'].')<br />';  } ;?>
			
			
			<p><a href="planning_prof.php?date=<?php echo date('Ymd');?>" target="_blank"><img src="../images/planning_prof.gif" alt="Planning enseignant" width="24" height="24"  border="0" title="Planning enseignant"></a>&nbsp;
			<a href="planning_select.php" target="_blank"><img src="../images/planning_eleve.gif" title="Planning des devoirs et du travail donn&eacute; dans une classe" alt="Planning des devoirs et du travail donn&eacute; dans une classe" width="24" height="24" border="0"></a>
			&nbsp;<a href="evenement_liste.php?classe=toutes&submit=valider"  target="_blank"><img src="../images/even_planning.png" alt="Planning &eacute;v&eacute;nements" width="24" height="24"  border="0" title="Planning &eacute;v&eacute;nements"></a>
			
			<a href="heure_sup.php?hs&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php 
			if(isset($_GET['date'])){ echo substr($_GET['date'],0,8).'0';}	 
			else
			{
			if (isset($_GET['code_date'])){ echo  substr($_GET['code_date'],0,8).'0';} else {echo date('Ymd').'0';}; }; ?> " title="Ajouter une heure suppl&eacute;mentaire" alt="Ajouter une heure suppl&eacute;mentaire" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>><img src="../images/heure_sup.gif" style="border:none" width="24" height="24"></a>
			
			<?php if ((isset($_SESSION['devoir_planif']))&&($_SESSION['devoir_planif']=='Oui')) { ?>
				<a href="devoirs_planifies.php?jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php 
				if(isset($_GET['date'])){ echo substr($_GET['date'],0,8).'0';}	 else
				{if (isset($_GET['code_date'])){ echo  substr($_GET['code_date'],0,8).'0';} else {echo date('Ymd').'0';}; }; ?> " title="Planifier un devoir en dehors des heures de cours" alt="Planifier un devoir en dehors des heures de cours"  <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>><img src="../images/devoirs.gif" style="border:none" width="24" height="24" > </a>		

			</p>
			<?php }; ?>

			<p>
			<a href="vue_du_jour.php?jour_RsJour=<?php echo (get_magic_quotes_gpc()) ? $current_day_name : addslashes($current_day_name);?>&madate=<?php echo $madate;?>&gestion_sem_ab=<?php echo $un_Sem['gestion_sem_ab'];?>">Synth&egrave;se de la journ&eacute;e</a>			</p>
			<form name="form_search1" method="post" action="chercher.php" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
<input type="text" onFocus="if(this.value=='recherche...') this.value='';" onBlur="if(this.value=='') this.value='recherche...';" id="search" name="search"  value="recherche..."  alt="Recherche" style="width:100%">
            </form>	
					<!-- cellules classes de la colonne de gauche 1268 -->
			
<?php
if ($en_vacance_today<>1){
	if (!isset($_GET['date'])){echo '<br />';};
?>		
			<table width="100%" border="0" cellpadding="0"  cellspacing="0"  class="bordure" >
			<tr>
			<?php 
			
			if (isset($_GET['jour_pointe'])) {$texte_date= substr($_GET['jour_pointe'],0,strlen($_GET['jour_pointe'])-4);} else {$texte_date=  substr($jour_pointe,0,strlen($jour_pointe)-4);};?>
			
			<td colspan="2" height="20" class="Style666">
			
			<?php echo $texte_date;?> <br/>
			
			</td>
			</tr>
			<?php 
			if ($current_month<10){$current_month='0'.$current_month ;}
			
			//on trace les cellules de la colonne de gauche 1289
                        
                        do { 
                                $heurepartagee=false;
                                $ProfID=$_SESSION['ID_prof'];
                                if ($row_RsJour['prof_ID']<>$_SESSION['ID_prof']) {  //Le prof n'est pas le createur de la plage mais partage celle-ci
                                        $heurepartagee=true;
                                        $ProfID=$row_RsJour['prof_ID'];
                                };
				if (($row_RsJour['classe_ID']!=="0")||($row_RsJour['gic_ID']!=="0")) {
                                        if (isset($_GET['code_date'])) {$code_date =substr($_GET['code_date'],0,8).$row_RsJour['heure'];} else {$code_date=$current_year.$current_month.$current_day.$row_RsJour['heure'];}
                                        
                                        if ($row_RsJour['gic_ID']>0){ //regroupement
                                        	
                                                $query_Agenda2 = sprintf("SELECT cdt_agenda.ID_agenda,cdt_agenda.classe_ID,cdt_agenda.groupe,cdt_agenda.matiere_ID,cdt_matiere.nom_matiere,cdt_agenda.edt_modif,cdt_classe.nom_classe,cdt_agenda.theme_activ,cdt_agenda.partage FROM cdt_agenda,cdt_classe,cdt_matiere WHERE cdt_agenda.code_date =%s AND cdt_agenda.prof_ID=%u AND cdt_agenda.heure=%u AND cdt_agenda.heure_debut='%s' AND cdt_classe.ID_classe=cdt_agenda.classe_ID AND cdt_matiere.ID_matiere=cdt_agenda.matiere_ID", $code_date, $ProfID, $row_RsJour['heure'], $row_RsJour['heure_debut']);
                                                
                                        }
                                        else
                                        {   //normal
                                                $query_Agenda2 = sprintf("SELECT cdt_agenda.ID_agenda,cdt_agenda.classe_ID,cdt_agenda.groupe,cdt_agenda.matiere_ID,cdt_matiere.nom_matiere,cdt_agenda.edt_modif,cdt_classe.nom_classe,cdt_agenda.theme_activ,cdt_agenda.partage FROM cdt_agenda,cdt_classe,cdt_matiere WHERE cdt_agenda.code_date =%s  AND cdt_agenda.prof_ID=%u AND cdt_agenda.heure=%u AND cdt_classe.ID_classe=cdt_agenda.classe_ID AND cdt_matiere.ID_matiere=cdt_agenda.matiere_ID",$code_date, $ProfID, $row_RsJour['heure']);
                                        };
                                        
                                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                        $sel_Agenda2 = mysqli_query($conn_cahier_de_texte, $query_Agenda2) or die(mysqli_error($conn_cahier_de_texte));
                                        $row_RsAgenda2 = mysqli_fetch_assoc($sel_Agenda2);
                                        $nb_Agenda2 = mysqli_num_rows($sel_Agenda2);
                                        
                                        if ($nb_Agenda2==0 && $heurepartagee) { //Si heure partagee, recherche d'une modif d'EDT
                                                
                                                if ($row_RsJour['gic_ID']>0){ //regroupement
                                                        $query_Agenda2 = sprintf("SELECT cdt_agenda.ID_agenda,cdt_agenda.classe_ID,cdt_agenda.groupe,cdt_agenda.matiere_ID,cdt_matiere.nom_matiere,cdt_agenda.edt_modif,cdt_classe.nom_classe,cdt_agenda.theme_activ,cdt_agenda.partage FROM cdt_agenda,cdt_classe,cdt_matiere WHERE cdt_agenda.code_date =%s AND cdt_agenda.prof_ID=%u AND cdt_agenda.heure=%u AND cdt_agenda.heure_debut='%s' AND cdt_classe.ID_classe=cdt_agenda.classe_ID AND cdt_matiere.ID_matiere=cdt_agenda.matiere_ID", $code_date, GetSQLValueString($_SESSION['ID_prof'],"int"), $row_RsJour['heure'], $row_RsJour['heure_debut']);
                                                }
                                                else
                                                {   //normal
                                                        $query_Agenda2 = sprintf("SELECT cdt_agenda.ID_agenda,cdt_agenda.classe_ID,cdt_agenda.groupe,cdt_agenda.matiere_ID,cdt_matiere.nom_matiere,cdt_agenda.edt_modif,cdt_classe.nom_classe,cdt_agenda.theme_activ,cdt_agenda.partage FROM cdt_agenda,cdt_classe,cdt_matiere WHERE cdt_agenda.code_date =%s  AND cdt_agenda.prof_ID=%u AND cdt_agenda.heure=%u AND cdt_classe.ID_classe=cdt_agenda.classe_ID AND cdt_matiere.ID_matiere=cdt_agenda.matiere_ID",$code_date, GetSQLValueString($_SESSION['ID_prof'],"int"), $row_RsJour['heure']);
                                                };
                                                
                                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                        	$sel_Agenda2 = mysqli_query($conn_cahier_de_texte, $query_Agenda2) or die(mysqli_error($conn_cahier_de_texte));
                                        	$row_RsAgenda2 = mysqli_fetch_assoc($sel_Agenda2);
                                        	$nb_Agenda2 = mysqli_num_rows($sel_Agenda2);
                                        };
                                        $AgendaPartage='N';
                                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                        $query_partage = sprintf("SELECT Partage_ID FROM cdt_emploi_du_temps_partage WHERE ID_emploi=%u",GetSQLValueString($row_RsJour['ID_emploi'],"int"));
                                        $sel_partage = mysqli_query($conn_cahier_de_texte, $query_partage) or die(mysqli_error($conn_cahier_de_texte));
                                        $totalun_partage = mysqli_num_rows($sel_partage);
                                        mysqli_free_result($sel_partage);
                                        if ($totalun_partage>0) { // L'heure est partagee mais le prof actuel en est le createur
                                        	$AgendaPartage='O';
                                        };
					
                                        ?>
					<tr class="Style1" >
					<td class="Style33"><?php 
					if ($row_RsAgenda2['partage']=='O' ||  $AgendaPartage=='O'){echo '&nbsp;<img src="../images/partage.gif" title="Cette plage est partag&eacute;e avec au moins un autre enseignant.">';};
					echo '<br/>';	
					if ($row_RsJour['heure_debut'] <>''){ echo $row_RsJour['heure_debut'];} else {echo $row_RsJour['heure'];};?>
					<br>
					<?php 
					if  (($row_RsJour['semaine']=="A" )||($row_RsJour['semaine']=="B" ))  {
					
						
                                                if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){
                                                        if($row_RsJour['semaine']=='A'){echo 'Paire';} else {echo 'Impaire';};
                                                }
                                                else { 
                                                        echo 'Sem.&nbsp;'.$row_RsJour['semaine'];
                                                };
                                                
						
					};
					
					if (($row_RsJour['semaine']!="A et B") &&($row_RsJour['semaine']!="A" )&& ($row_RsJour['semaine']!="B" )){echo 'S.'.$row_RsJour['semaine'];
					
					} ;?>
					</td>
                                        <?php
                                        
                                        //******** test de la presence d'une activite et coloration de la cellule *******
                                        
                                        if ($nb_Agenda2 != 0) {        mysqli_data_seek($sel_Agenda2,0);          }
                                        
                                        if ($nb_Agenda2>0){echo ' <td style="  border-width: 1px;border-left-style: solid;border-left-color: #CBDCEB;" class="Style33" >';} else {echo ' <td class="bas_ligne">' ;  };
                                        
                                        //Si l'edt a subi une modification ponctuelle du nom de la classe, groupe ou matiere
                                        $cl_modif='';$gr_modif='';$mat_modif='';
                                        if (($nb_Agenda2>0)&&($row_RsAgenda2['edt_modif']=='O')){
						echo '<p class="texte_rouge">EDT MODIFIE</p>';
						if ($row_RsJour['classe_ID']<>$row_RsAgenda2['classe_ID']){
							$row_RsJour['nom_classe']=$row_RsAgenda2['nom_classe'];
							$row_RsJour['classe_ID']=$row_RsAgenda2['classe_ID'];
							$cl_modif='<span class="texte_rouge">*</span>';
						};
						if ($row_RsJour['groupe']<>$row_RsAgenda2['groupe']){
							$row_RsJour['groupe']=$row_RsAgenda2['groupe'];
						$gr_modif='<span class="texte_rouge">*</span>';}
						if ($row_RsJour['matiere_ID']<>$row_RsAgenda2['matiere_ID']){
							$row_RsJour['nom_matiere']=$row_RsAgenda2['nom_matiere'];
                                                        $row_RsJour['matiere_ID']=$row_RsAgenda2['matiere_ID'];
                                                        $mat_modif='<span class="texte_rouge">*</span>';
                                                };
                                        };
                                        //*********************************************************************************
                                        mysqli_free_result($sel_Agenda2);
                                        
                                        //lien sur le nom de la classe dans la cellule pour modification de fiche 
                                        if ($row_RsJour['classe_ID']==0){
                                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                                $query_gic =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE cdt_groupe_interclasses.ID_gic = %u ",$row_RsJour['gic_ID']);
                                                $sel_gic = mysqli_query($conn_cahier_de_texte, $query_gic) or die(mysqli_error($conn_cahier_de_texte));
                                                $un_gic = mysqli_fetch_assoc($sel_gic);
                                                $classe_affiche= '(R) '.$un_gic['nom_gic'];
												
                                        } else {
                                        	
											$classe_affiche=$row_RsJour['nom_classe'];
                                        };
                                        echo $cl_modif;
                                        if (isset($_GET['date'])){$date_edition=$_GET['date'];} else {$date_edition=substr($_GET['code_date'],0,8);};
                                        if (visa_edition_possible($date_edition)) {    
                                                if ($heurepartagee) { //Avec prise en compte des heures partagees ?>
                                                	<a href="ecrire.php?saisie=1&share=O&edtID=<?php echo $row_RsJour['ID_emploi'];?>&nom_classe=<?php echo $classe_affiche;?>&classe_ID=<?php echo $row_RsJour['classe_ID']?>&gic_ID=<?php echo $row_RsJour['gic_ID']?>&nom_matiere=<?php echo $row_RsJour['nom_matiere']?>&groupe=<?php echo $row_RsJour['groupe']?>&matiere_ID=<?php echo $row_RsJour['matiere_ID']?>&semaine=<?php echo $row_RsJour['semaine']?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&heure=<?php echo $row_RsJour['heure']?>&duree=<?php echo $row_RsJour['duree']?>&heure_debut=<?php echo $row_RsJour['heure_debut']?>&heure_fin=<?php echo $row_RsJour['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php echo $code_date ?>" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
                                                <?php 
												} else 
												{
												?>
                                                	<a href="ecrire.php?saisie=1&edtID=<?php echo $row_RsJour['ID_emploi'];?>&nom_classe=<?php echo $classe_affiche;?>&classe_ID=<?php echo $row_RsJour['classe_ID']?>&gic_ID=<?php echo $row_RsJour['gic_ID']?>&nom_matiere=<?php echo $row_RsJour['nom_matiere']?>&groupe=<?php echo $row_RsJour['groupe']?>&matiere_ID=<?php echo $row_RsJour['matiere_ID']?>&semaine=<?php echo $row_RsJour['semaine']?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&heure=<?php echo $row_RsJour['heure']?>&duree=<?php echo $row_RsJour['duree']?>&heure_debut=<?php echo $row_RsJour['heure_debut']?>&heure_fin=<?php echo $row_RsJour['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php echo $code_date ?>" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>
													>
                                                <?php }; ?>
                                                <?php echo Insert_espace($classe_affiche,13); ?> </a>
                                                <?php } else {echo Insert_espace($classe_affiche,13);};?>
                                                
                                                <br>
                                                <?php
                                                
                                                echo Insert_espace($row_RsJour['groupe'].$gr_modif,13).' <br> '.Insert_espace($row_RsJour['nom_matiere'].$mat_modif,13);?>
                                                <br />
                                                <?php if ($nb_Agenda2>0){
        echo '<div style="width: 80px;  word-wrap: break-word; overflow:hidden;">';
        if (strlen($row_RsAgenda2['theme_activ'])<28){
              echo '<strong>'.$row_RsAgenda2['theme_activ'].'</strong>';
              }
        else {
             echo '<strong>'.substr($row_RsAgenda2['theme_activ'],0,28).'...'.'</strong>';
        };
        echo '</div>';												
												
												} 
                                                
                                                ?>
                                                <br />
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                <td><?php if ($nb_Agenda2 != 0) {?>
                                                	
                                                	<form method="post" name="form_copie" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>">
                                                	<input type="hidden" name="ID_agenda" value="<?php echo $row_RsAgenda2['ID_agenda']?>">
                                                	<input type="hidden" name="MM_copie" value="form_copie">
                                                	<input name="img_copie" type="image" src="../images/ed_copy.gif" alt="Copier la fiche" title="Copier la fiche" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
                                                	<?php if ($heurepartagee) {
                                                		echo '<input type="hidden" name="share_hour" value="'.$row_RsJour["ID_emploi"].'">';
                                                	} else {
                                                		echo '<input type="hidden" name="share_hour" value="0">';
                                                	};?>
                                                	<!--Traiter les copies de cellules EDT MODIFIE -->
                                                	<?php echo' <input type="hidden" name="edt_modif_mat" value="'.$row_RsAgenda2['matiere_ID'].'">';?>
                                                	<?php echo' <input type="hidden" name="edt_modif" value="'.$row_RsAgenda2['edt_modif'].'">';?>                            
                                                	</form>
                                                	<?php 
                                                }?>
                                                </td>
                                                <td>
                                                <?php if (visa_edition_possible($date_edition)){ ?>			  
                                                	<form method="post" name="form_coller" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>">
                                                	<input type="hidden" name="MM_coller" value="form_coller">
                                                	<input type="hidden" name="ID_agenda" value="<?php echo $row_RsAgenda2['ID_agenda']?>">
                                                	<input type="hidden" name="classe_ID" value="<?php echo $row_RsJour['classe_ID'];?>">
                                                	<input type="hidden" name="gic_ID" value="<?php echo $row_RsJour['gic_ID'];?>">
                                                	<input type="hidden" name="matiere_ID" value="<?php 
                                                	if ($row_RsAgenda2['edt_modif']=='O'){echo $row_RsAgenda2['matiere_ID'];}else {echo $row_RsJour['matiere_ID'];};?>">
                                                	<input type="hidden" name="code_date" value="<?php echo $code_date;?>">
                                                	<input type="hidden" name="groupe" value="<?php echo $row_RsJour['groupe'];?>">
                                                	<input type="hidden" name="semaine" value="<?php echo $row_RsJour['semaine'];?>">
                                                	<input type="hidden" name="jour_pointe" value="<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>">
                                                	<input type="hidden" name="heure" value="<?php echo $row_RsJour['heure'];?>">
                                                	<input type="hidden" name="duree" value="<?php echo $row_RsJour['duree'];?>">
                                                	<input type="hidden" name="heure_debut" value="<?php echo $row_RsJour['heure_debut'];?>">
                                                	<input type="hidden" name="heure_fin" value="<?php echo $row_RsJour['heure_fin'];?>">
                                                	<input type="hidden" name="edt_modif" value="<?php if ($row_RsAgenda2['edt_modif']=='O'){echo 'O';} else {echo 'N';} ;?>">
                                                	<?php if ($nb_Agenda2 == 0) { ?>
                                                		<input type="hidden" name="coller_insertion" value="coller_insertion">
                                                		<?php
                                                	} 
                                                	else 
                                                	{
                                                                ?>
                                                                <input type="hidden" name="coller_update" value="coller_update" >
                                                        <?php };
                                                        //Modification : L'icone coller est toujours presente - probleme coller archive pas regle
              if (isset($_SESSION['copie'])) {
                                                                echo '<input name="img_copie" type="image" src="../images/ed_paste.gif" title="Coller la fiche"';
if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};
																 echo'>';
                                                                if ($heurepartagee) {
                                                                        echo '<input type="hidden" name="share_hour" value="'.$row_RsJour["ID_emploi"].'">';
                                                                } else {
                                                                        echo '<input type="hidden" name="share_hour" value="0">';
                                                                };
                                                        };
                                                        ?>
                                                  </form>
                                <?php }else { ?><img src="../images/ed_no_paste.gif" alt="Copie de la fiche impossible" title="Copie de la fiche impossible" border="0" onClick='copie_impossible()'><?php };?>
				</td>
				<td><?php
				if ($nb_Agenda2 != 0){
					if (visa_edition_possible($date_edition)){
						$lien_a_supprimer="agenda_supprime.php?nom_classe=".$row_RsJour['nom_classe']."&classe_ID=".$row_RsJour['classe_ID']."&gic_ID=".$row_RsJour['gic_ID']."&nom_matiere=".$row_RsJour['nom_matiere']."&groupe=".$row_RsJour['groupe']."&matiere_ID=".$row_RsJour['matiere_ID']."&semaine=".$row_RsJour['semaine']."&heure=".$row_RsJour['heure']."&heure_debut=".$row_RsJour['heure_debut']."&heure_fin=".$row_RsJour['heure_fin']."&current_day_name=".$current_day_name."&code_date=".$code_date."&ID_agenda=".$row_RsAgenda2['ID_agenda'];
						$lien_a_supprimer.="&jour_pointe=";
						$lien_a_supprimer.=isset($_GET['jour_pointe'])?$_GET['jour_pointe']:$jour_pointe; 
						$lien_a_supprimer.=$row_RsJour['duree']!=''?"&duree=".$row_RsJour['duree']:'';
						?>
						<form name="form_supprime" enctype="multipart/form-data" action="<?php echo $lien_a_supprimer;?>" method="post">
						<input name="img_suppr" type="image" src="../images/ed_delete.gif" alt="Supprimer la fiche" title="Supprimer la fiche" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
						</form>
				<?php }else { ?><img src="../images/ed_no_delete.gif" alt="Suppression de la fiche impossible" title="Suppression de la fiche impossible" border="0" onClick='suppression_impossible()'><?php };}?></td>
				<td>
				<?php	 
				if (visa_edition_possible($date_edition)){ //Edition de la seance
					if ($heurepartagee) { //Avec prise en compte des heures partagees ?>
						<form name="form_edition" enctype="multipart/form-data" action="ecrire.php?saisie=0&share=O&edtID=<?php echo $row_RsJour['ID_emploi'];?>&nom_classe=<?php echo $row_RsJour['nom_classe']?>&classe_ID=<?php echo $row_RsJour['classe_ID']?>&gic_ID=<?php echo $row_RsJour['gic_ID']?>&nom_matiere=<?php echo $row_RsJour['nom_matiere']?>&groupe=<?php echo $row_RsJour['groupe']?>&matiere_ID=<?php echo $row_RsJour['matiere_ID']?>&semaine=<?php echo $row_RsJour['semaine']?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&heure=<?php echo $row_RsJour['heure']?>&duree=<?php echo $row_RsJour['duree']?>&heure_debut=<?php echo $row_RsJour['heure_debut']?>&heure_fin=<?php echo $row_RsJour['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php echo $code_date?>&pas_de_saisie " method="post"><input name="eye" type="image" src="../images/deja_realise.png" alt="D&eacute;j&agrave; r&eacute;alis&eacute; dans cette classe" title="D&eacute;j&agrave; r&eacute;alis&eacute; dans cette classe" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
					<?php } else {?>
						<form name="form_edition" enctype="multipart/form-data" action="ecrire.php?saisie=0&edtID=<?php echo $row_RsJour['ID_emploi'];?>&nom_classe=<?php echo $row_RsJour['nom_classe']?>&classe_ID=<?php echo $row_RsJour['classe_ID']?>&gic_ID=<?php echo $row_RsJour['gic_ID']?>&nom_matiere=<?php echo $row_RsJour['nom_matiere']?>&groupe=<?php echo $row_RsJour['groupe']?>&matiere_ID=<?php echo $row_RsJour['matiere_ID']?>&semaine=<?php echo $row_RsJour['semaine']?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&heure=<?php echo $row_RsJour['heure']?>&duree=<?php echo $row_RsJour['duree']?>&heure_debut=<?php echo $row_RsJour['heure_debut']?>&heure_fin=<?php echo $row_RsJour['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php echo $code_date?>&pas_de_saisie " method="post"><input name="eye" type="image" src="../images/deja_realise.png" alt="D&eacute;j&agrave; r&eacute;alis&eacute; dans cette classe" title="D&eacute;j&agrave; r&eacute;alis&eacute; dans cette classe" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
					<?php }; ?>
					</form>
				<?php } else { ?><img src="../images/button_no_edit.png" alt="Modification de la fiche impossible" title="Modification de la fiche impossible" border="0" onClick='edition_impossible()'>
				<?php };?>
				</td>
				<td><!--Modification ponctuelle d'une plage -->	
				<?php	 
				if ((visa_edition_possible($date_edition))&&($nb_Agenda2==0)){?>	
					<form name="form_edition" enctype="multipart/form-data" action="emploi_modif_ponctuel.php?nom_classe=<?php echo $row_RsJour['nom_classe']?>&classe_ID=<?php echo $row_RsJour['classe_ID']?>&gic_ID=<?php echo $row_RsJour['gic_ID']?>&nom_matiere=<?php echo $row_RsJour['nom_matiere']?>&groupe=<?php echo $row_RsJour['groupe']?>&matiere_ID=<?php echo $row_RsJour['matiere_ID']?>&semaine=<?php echo $row_RsJour['semaine']?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&heure=<?php echo $row_RsJour['heure']?>&duree=<?php echo $row_RsJour['duree']?>&heure_debut=<?php echo $row_RsJour['heure_debut']?>&heure_fin=<?php echo $row_RsJour['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php echo $code_date?> " method="post"><input name="img_edit" type="image" src="../images/outils.png" alt="Modification ponctuelle de l'emploi du temps" title="Modification ponctuelle de l'emploi du temps" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
					</form>
				<?php } ;?>
				</td>
				
				<td><!--Gestion des absences -->	
				<?php	 
			
			if ( (isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui'))	{
				if ((isset($_SESSION['choix_module_absence']))&&($_SESSION['choix_module_absence']==1))	{	 
			
				
				//Lien vers le module de declaration des absences simple
						include('../inc/module_absence_simple.php');
				
				// fin absence module lycee
				}
				else
				{
				// absence module elabore avec suivi des carnets
				
					include('../inc/module_absence_elabore.php');
				};
			};
				?>
				</td>
				</tr>
				</table>
				</td>
				</tr>
				<?php
				
				}	
                        } while ($row_RsJour = mysqli_fetch_assoc($sel_Jour)); 
                        
                          ?>                   
    </table> 
	<br />
	<?php
	} //fin du trace des cellules de cours en colonne gauche

	?>
    
         <table width="100%"  cellspacing="0" cellpadding="0"  class="bordure" >
            <tr class="Style1">
			<td class="bas_ligne"><a href="heure_sup.php?hs&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php 
			if(isset($_GET['date'])){ echo substr($_GET['date'],0,8).'0';}	 else
			{if (isset($_GET['code_date'])){ echo  substr($_GET['code_date'],0,8).'0';} else {echo date('Ymd').'0';}; }; ?> " <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>Ajout heure suppl&eacute;mentaire</a> </td>
			</tr>
			</table>
			<br />
			<table width="100%"  cellspacing="0" cellpadding="0"  class="bordure" >
			<tr class="Style1">
			<td class="bas_ligne"><a href="planning_select.php" target="_blank">Planning des devoirs et travail donn&eacute; dans une classe</a> </td>
			</tr>
			</table>
			<br />
			<?php if ((isset($_SESSION['devoir_planif']))&&($_SESSION['devoir_planif']=='Oui')){ ?>
				<table width="100%"  cellspacing="0" cellpadding="0"  class="bordure" >
				<tr class="Style1">
				
				<td class="bas_ligne"><a href="devoirs_planifies.php?jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php 
				if(isset($_GET['date'])){ echo substr($_GET['date'],0,8).'0';
				}	 
				else{
				if (isset($_GET['code_date'])){ echo  substr($_GET['code_date'],0,8).'0';} else {echo date('Ymd').'0';}; }; ?> " <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>Planifier un devoir en dehors des heures de cours pour le<br /><?php 
				if (isset($_GET['date'])){$texte_date= jour_semaine(substr($_GET['date'],6,2).'/'.substr($_GET['date'],4,2).'/'.substr($_GET['date'],0,4)).'&nbsp;'.substr($_GET['date'],6,2).'/'.substr($_GET['date'],4,2).'/'.substr($_GET['date'],0,4);};
				if(isset($texte_date)){echo $texte_date;}else{echo jour_semaine(date('d/m/Y')).'&nbsp;'.date('d/m/Y');};?></a></td>
				</tr>
				</table>
                                <?php
                        }
                }
        } 
		
		else { //du if	(($nb_Jour<>0)&&($nb_Jour!==$nb_classeID0))
		
                if ($un_Sem['gestion_sem_ab']=='O'){ echo '<br /><br />Semaine '.$_SESSION['semdate_libelle'];  };
if ($_SESSION['droits']<>8){echo '<br /><br />Pas de cours le '.$current_day_name.'<br />';};?><br />
<a href="evenement_liste.php?classe=toutes&submit=valider"  target="_blank"><img src="../images/even_planning.png" alt="Planning evenements" width="24" height="24"  border="0" title="Planning evenements"></a><br /><br />
<form name="form_search2" method="post" action="chercher.php" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
<input type="text" onFocus="if(this.value=='recherche...') this.value='';" onBlur="if(this.value=='') this.value='recherche...';" name="search" id="search" value="recherche..."  alt="Recherche" style="width:100%">
</form>
<?php	if (!isset($_GET['date'])){echo '<br />';};?>	
		<table width="100%"  cellspacing="0" cellpadding="0"  class="bordure" >
		<tr class="Style1">
		<td class="bas_ligne"><a href="heure_sup.php?hs&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php 
		if(isset($_GET['date'])){ echo substr($_GET['date'],0,8).'0';}	 else
		{if (isset($_GET['code_date'])){ echo  substr($_GET['code_date'],0,8).'0';} else {echo date('Ymd').'0';}; }; ?> " <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>Ajout heure suppl&eacute;mentaire </a> </td>
		</tr>
		</table>
		<br />
		<?php if ((isset($_SESSION['devoir_planif']))&&($_SESSION['devoir_planif']=='Oui')){ ?>
			<table width="100%"  cellspacing="0" cellpadding="0"  class="bordure" >
			<tr class="Style1">
			<td class="bas_ligne"><a href="devoirs_planifies.php?jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php 
			if(isset($_GET['date'])){ echo substr($_GET['date'],0,8).'0';}	 else
			{if (isset($_GET['code_date'])){ echo  substr($_GET['code_date'],0,8).'0';} else {echo date('Ymd').'0';}; }; ?> ">Planifier un devoir en dehors des heures de cours pour le<br /><?php 
			if (isset($_GET['date'])){$texte_date= jour_semaine(substr($_GET['date'],6,2).'/'.substr($_GET['date'],4,2).'/'.substr($_GET['date'],0,4)).'&nbsp;'.substr($_GET['date'],6,2).'/'.substr($_GET['date'],4,2).'/'.substr($_GET['date'],0,4);};
			if(isset($texte_date)){echo $texte_date;}else{echo jour_semaine(date('d/m/Y')).'&nbsp;'.date('d/m/Y');};?></a></td>
			</tr>
			</table>
			<?php
		}
	};
	
	
	//debut affichage cellules heures supplementaires
	if (isset($_GET['date'])){$madate=substr($_GET['date'],0,8);};
        if (isset($_GET['code_date'])){$madate=substr($_GET['code_date'],0,8);};
        if (isset($madate)){
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_HS = sprintf("SELECT * FROM cdt_agenda,cdt_classe,cdt_matiere WHERE substring(code_date,9,1)=0 AND substring(code_date,1,8)=%s AND cdt_agenda.prof_ID=%u AND cdt_classe.ID_classe=cdt_agenda.classe_ID AND cdt_matiere.ID_matiere=cdt_agenda.matiere_ID AND cdt_agenda.type_activ<>'ds_prog'  GROUP BY cdt_agenda.heure ORDER BY cdt_agenda.heure",$madate,GetSQLValueString($_SESSION['ID_prof'],"int"));
                $sel_HS = mysqli_query($conn_cahier_de_texte, $query_HS) or die(mysqli_error($conn_cahier_de_texte));
                $row_RsHS = mysqli_fetch_assoc($sel_HS);
                $nb_HS = mysqli_num_rows($sel_HS);
                
                if ($nb_HS>0){
                	if (isset($_GET['date'])){$date_edition=$_GET['date'];}else{$date_edition=substr($_GET['code_date'],0,8);};
                        ?>
                        
                        <br />
			<table width="100%" border="0" cellpadding="0"  cellspacing="0"  class="bordure" >
			<tr>
			<td colspan="4" height="20" class="Style666"><?php if ($nb_HS==1){echo 'Heure suppl&eacute;mentaire';} else {echo 'Heures suppl&eacute;mentaires';};?>
			</td>
			</tr>
			<?php
			do { 
				?>
                                <tr class="Style1" >
                                <td class="Style33"><?php 
                                
                                if ($row_RsHS['heure_debut'] <>''){ echo $row_RsHS['heure_debut'];} else {echo $row_RsHS['heure'];};
                                ?></td>
                                <?php  
                                echo ' <td style="border-width: 1px;border-left-style: solid;border-left-color: #CBDCEB;" class="Style33" >';
                                
                                if ($row_RsHS['gic_ID']==0){
                                        ?>
					
					<a href="ecrire.php?saisie=1&nom_classe=<?php echo $row_RsHS['nom_classe']?>&classe_ID=<?php echo $row_RsHS['classe_ID']?>&gic_ID=<?php echo $row_RsHS['gic_ID'];?>&nom_matiere=<?php echo $row_RsHS['nom_matiere']?>&groupe=<?php echo $row_RsHS['groupe']?>&matiere_ID=<?php echo $row_RsHS['matiere_ID']?>&semaine=<?php echo $row_RsHS['semaine']?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&heure=<?php echo $row_RsHS['heure']?>&duree=<?php echo $row_RsHS['duree']?>&heure_debut=<?php echo $row_RsHS['heure_debut']?>&heure_fin=<?php echo $row_RsHS['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php echo $date_edition.'0';?>  "><?php echo Insert_espace($row_RsHS['nom_classe'],13);?></a>		 
				<?php }else{
					
					$query_gic_classe_ID_default =sprintf("SELECT cdt_groupe_interclasses.nom_gic FROM cdt_groupe_interclasses WHERE  cdt_groupe_interclasses.ID_gic = %u LIMIT 1",$row_RsHS['gic_ID']);
					$sel_gic_classe_ID_default = mysqli_query($conn_cahier_de_texte, $query_gic_classe_ID_default) or die(mysqli_error($conn_cahier_de_texte));
					$un_gic_classe_ID_default = mysqli_fetch_assoc($sel_gic_classe_ID_default);	 
					?>
					
					<a href="ecrire.php?saisie=1&nom_classe=<?php echo $row_RsHS['nom_classe']?>&classe_ID=<?php echo $row_RsHS['classe_ID']?>&gic_ID=<?php echo $row_RsHS['gic_ID'];?>&nom_matiere=<?php echo $row_RsHS['nom_matiere']?>&groupe=<?php echo $row_RsHS['groupe']?>&matiere_ID=<?php echo $row_RsHS['matiere_ID']?>&semaine=<?php echo $row_RsHS['semaine']?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&heure=<?php echo $row_RsHS['heure']?>&duree=<?php echo $row_RsHS['duree']?>&heure_debut=<?php echo $row_RsHS['heure_debut']?>&heure_fin=<?php echo $row_RsHS['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php echo $date_edition.'0';?>"><?php echo Insert_espace($un_gic_classe_ID_default['nom_gic'],13);?></a>
					<?php	 
					
				};
                                
echo '<br/>'.Insert_espace($row_RsHS['groupe'],13).'<br/>'.Insert_espace($row_RsHS['nom_matiere'],13).'<br/>';

      

        echo '<div style="width: 80px;  word-wrap: break-word; overflow:hidden;">';
        if (strlen($row_RsHS['theme_activ'])<28){
              echo '<strong>'.$row_RsHS['theme_activ'].'</strong>';
        }
        else {
             echo '<strong>'.substr($row_RsHS['theme_activ'],0,28).'...'.'</strong>';
        };
        echo '</div>';												
						      
?>
        <br />
   <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
      <td>
      <form method="post" name="form_copie" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>">
      <input type="hidden" name="ID_agenda" value="<?php echo $row_RsHS['ID_agenda']?>">
      <input type="hidden" name="MM_copie" value="form_copie">
      <input name="img_copie" type="image" src="../images/ed_copy.gif" alt="Copier la fiche" title="Copier la fiche"  <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
      <!--Traiter les copies de cellules EDT MODIFIE -->
      <?php echo' <input type="hidden" name="edt_modif_mat" value="'.$row_RsHS['matiere_ID'].'">';?>
      <?php echo' <input type="hidden" name="edt_modif" value="'.$row_RsHS['edt_modif'].'">';?>                                 
      </form>
      </td><td>
      <?php
      
      if (visa_edition_possible($date_edition)){
      	if (isset($_GET['code_date'])) {$code_date =substr($_GET['code_date'],0,8).'0';} else {$code_date=$current_year.$current_month.$current_day.'0';}
      	
      	?>			  
      	<form method="post" name="form_coller" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>">
      	<input type="hidden" name="MM_coller" value="form_coller">
      	<input type="hidden" name="ID_agenda" value="<?php echo $row_RsHS['ID_agenda']?>">
      	<input type="hidden" name="classe_ID" value="<?php echo $row_RsHS['classe_ID'];?>">
      	<input type="hidden" name="gic_ID" value="<?php echo $row_RsHS['gic_ID'];?>">
      	<input type="hidden" name="matiere_ID" value="<?php 
      	if ($row_RsHS['edt_modif']=='O'){echo  $_SESSION['edt_modif_mat'];}else {echo $row_RsHS['matiere_ID'];};?>">
      	<input type="hidden" name="code_date" value="<?php echo $code_date;?>">
      	<input type="hidden" name="groupe" value="<?php echo $row_RsHS['groupe'];?>">
      	<input type="hidden" name="semaine" value="<?php echo $row_RsHS['semaine'];?>">
      	<input type="hidden" name="jour_pointe" value="<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>">
      	<input type="hidden" name="heure" value="<?php echo $row_RsHS['heure'];?>">
      	<input type="hidden" name="duree" value="<?php echo $row_RsHS['duree'];?>">
      	<input type="hidden" name="heure_debut" value="<?php echo $row_RsHS['heure_debut'];?>">
      	<input type="hidden" name="heure_fin" value="<?php echo $row_RsHS['heure_fin'];?>">
      	<input type="hidden" name="edt_modif" value="<?php if ($row_RsHS['edt_modif']=='O'){echo 'O';} else {echo 'N';} ;?>">
      	<?php if ($nb_HS == 0) { ?>
      		<input type="hidden" name="coller_insertion" value="coller_insertion">
      		<?php
      	} 
      	else 
      	{
      		?>
                      <input type="hidden" name="coller_update" value="coller_update" >
              <?php }?>
              <?php
              ////Modification : L'icone coller est toujours presente - probleme coller archive pas regle
                    //if (isset($_SESSION['copie'])) {
                                echo '<input name="img_copie" type="image" src="../images/ed_paste.gif" title="Coller la fiche"';
if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};																													
echo'>';
              //}?>
        </form>
        <?php } else { ?><img src="../images/ed_no_paste.gif" alt="Copie de la fiche impossible" title="Copie de la fiche impossible" border="0" onClick='copie_impossible()'><?php };?>
        </td><td>               
	<?php		
	if (isset($_GET['date'])){$date_edition=$_GET['date'];}else{$date_edition=substr($_GET['code_date'],0,8);};
	if (visa_edition_possible($date_edition)){ 
		if ($row_RsHS['gic_ID']<>0){$row_RsHS['classe_ID']=0;};
		$lien_a_supprimer="agenda_supprime.php?nom_classe=".$row_RsHS['nom_classe']."&classe_ID=".$row_RsHS['classe_ID']."&gic_ID=".$row_RsHS['gic_ID']."&nom_matiere=".$row_RsHS['nom_matiere']."&groupe=".$row_RsHS['groupe']."&matiere_ID=".$row_RsHS['matiere_ID']."&semaine=".$row_RsHS['semaine']."&heure=".$row_RsHS['heure']."&heure_debut=".$row_RsHS['heure_debut']."&heure_fin=".$row_RsHS['heure_fin']."&current_day_name=".$current_day_name."&code_date=".$madate."0&ID_agenda=".$row_RsHS['ID_agenda'];
		$lien_a_supprimer.="&jour_pointe=";
		$lien_a_supprimer.=isset($_GET['jour_pointe'])?$_GET['jour_pointe']:$jour_pointe; 
		$lien_a_supprimer.=$row_RsHS['duree']!=''?"&duree=".$row_RsHS['duree']:'';
		?>
		<form name="form_supprime" enctype="multipart/form-data" action="<?php echo $lien_a_supprimer?>" method="post">
		<input name="img_suppr" type="image" src="../images/ed_delete.gif" alt="Supprimer la fiche" title="Supprimer la fiche" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
		</form>
		
		
                <?php }else { ?><img src="../images/ed_no_delete.gif" alt="Suppression de la fiche impossible" title="Suppression de la fiche impossible" border="0" onClick='suppression_impossible()'><?php };?>			  
                
                </td>
                <td>&nbsp;</td>
                <td>	
                <?php if (visa_edition_possible($madate)){
                	
                	if ($row_RsHS['gic_ID']<>0){$row_RsHS['classe_ID']=0;};;
			
			?>
			<form name="form_edition" enctype="multipart/form-data" action="ecrire.php?saisie=0&nom_classe=<?php echo $row_RsHS['nom_classe']?>&classe_ID=<?php echo $row_RsHS['classe_ID']?>&gic_ID=<?php echo $row_RsHS['gic_ID'];?>&nom_matiere=<?php echo $row_RsHS['nom_matiere']?>&groupe=<?php echo $row_RsHS['groupe']?>&matiere_ID=<?php echo $row_RsHS['matiere_ID']?>&semaine=<?php echo $row_RsHS['semaine']?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&heure=<?php echo $row_RsHS['heure']?>&duree=<?php echo $row_RsHS['duree']?>&heure_debut=<?php echo $row_RsHS['heure_debut']?>&heure_fin=<?php echo $row_RsHS['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php echo $date_edition.'0';?>&pas_de_saisie " method="post">
			<input name="eye" type="image" src="../images/deja_realise.png" alt="D&eacute;j&agrave; r&eacute;alis&eacute; dans cette classe" title="D&eacute;j&agrave; r&eacute;alis&eacute; dans cette classe" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
		</form><?php } else { ?><img src="../images/button_no_edit.png" alt="Modification de la fiche impossible" title="Modification de la fiche impossible" border="0" onClick='edition_impossible()'>
		<?php };?>
		</td>
		<td>
		<!--Gestion des absences -->	
		<?php	 
		//Lien vers le module de declaration des absences
		if ( (isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui'))	{
			include('../inc/module_absence_hs_dp.php');
		};
		?>
		</td>
		</tr>
		</table>
		<?php echo '</td>';?> </tr>
		<?php
              		} while ($row_RsHS = mysqli_fetch_assoc($sel_HS));
              		mysqli_free_result($sel_HS);
              		?>
	</table>
              		<?php  
              	}; // du $nb_HS
        };
        
        //fin affichage cellules heures sup
        
        
        
        //debut affichage cellules devoirs planifies
        if (isset($_GET['date'])){$madate=substr($_GET['date'],0,8);};
        if (isset($_GET['code_date'])){$madate=substr($_GET['code_date'],0,8);};
        if (isset($madate)){
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_HS = sprintf("SELECT * FROM cdt_agenda,cdt_classe,cdt_matiere WHERE substring(code_date,9,1)=0 AND substring(code_date,1,8)=%s AND cdt_agenda.prof_ID=%u AND cdt_classe.ID_classe=cdt_agenda.classe_ID AND cdt_matiere.ID_matiere=cdt_agenda.matiere_ID AND cdt_agenda.type_activ='ds_prog' GROUP BY cdt_agenda.heure ORDER BY heure_debut",$madate,GetSQLValueString($_SESSION['ID_prof'],"int"));
                $sel_HS = mysqli_query($conn_cahier_de_texte, $query_HS) or die(mysqli_error($conn_cahier_de_texte));
                $row_RsHS = mysqli_fetch_assoc($sel_HS);
                $nb_HS = mysqli_num_rows($sel_HS);
                if ($nb_HS>0){
                        if (isset($_GET['date'])){$date_edition=$_GET['date'];}else{$date_edition=substr($_GET['code_date'],0,8);};
                        ?>
                        
                        <br />
        		<table width="100%" border="0" cellpadding="0"  cellspacing="0"  class="bordure" >
        		<tr>
        		<td colspan="4" height="20" class="Style666"><?php if ($nb_HS==1){echo 'Devoir planifi&eacute;';} else {echo 'Devoirs planifi&eacute;s';};?>
        		</td>
        		</tr>
        		<?php
        		do { 
        			?>
        			<tr class="Style1" >
        			<td class="Style33"><?php 			


        			if ($row_RsHS['heure_debut'] <>''){ echo $row_RsHS['heure_debut'];} else {echo $row_RsHS['heure'];};
                                ?></td>
                                <?php  
                                if ($nb_HS>0){echo ' <td style="       border-width: 1px;border-left-style: solid;border-left-color: #CBDCEB;" class="Style33" >';} else {echo ' <td class="bas_ligne"> ' ;  };
                                
                                
                                if ($row_RsHS['gic_ID']==0){
                                        //lien sur nom de la classe pour edition
                                        ?>
        				<a href="ecrire.php?saisie=1&ds_prog&nom_classe=<?php echo $row_RsHS['nom_classe']?>&classe_ID=<?php echo $row_RsHS['classe_ID']?>
        				&gic_ID=<?php echo $row_RsHS['gic_ID'];?>&nom_matiere=<?php echo $row_RsHS['nom_matiere']?>&groupe=<?php echo $row_RsHS['groupe']?>&matiere_ID=<?php echo $row_RsHS['matiere_ID']?>&semaine=<?php echo $row_RsHS['semaine']?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&heure=<?php echo $row_RsHS['heure']?>&duree=<?php echo $row_RsHS['duree']?>&heure_debut=<?php echo $row_RsHS['heure_debut']?>&heure_fin=<?php echo $row_RsHS['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php echo $date_edition.'0';?> "><?php echo Insert_espace($row_RsHS['nom_classe'],13);?></a>
        				
        				<?php
        			}
        			else{
        				$query_gic_classe_ID_default =sprintf("SELECT cdt_groupe_interclasses.nom_gic FROM cdt_groupe_interclasses WHERE  cdt_groupe_interclasses.ID_gic = %u LIMIT 1",$row_RsHS['gic_ID']);
        				$sel_gic_classe_ID_default = mysqli_query($conn_cahier_de_texte, $query_gic_classe_ID_default) or die(mysqli_error($conn_cahier_de_texte));
        				$un_gic_classe_ID_default = mysqli_fetch_assoc($sel_gic_classe_ID_default);	 
        				?>
        				<a href="ecrire.php?saisie=1&ds_prog&nom_classe=<?php echo $row_RsHS['nom_classe']?>&classe_ID=<?php echo $row_RsHS['classe_ID']?>&gic_ID=<?php echo $row_RsHS['gic_ID'];?>&nom_matiere=<?php echo $row_RsHS['nom_matiere']?>&groupe=<?php echo $row_RsHS['groupe']?>&matiere_ID=<?php echo $row_RsHS['matiere_ID']?>&semaine=<?php echo $row_RsHS['semaine']?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&heure=<?php echo $row_RsHS['heure']?>&duree=<?php echo $row_RsHS['duree']?>&heure_debut=<?php echo $row_RsHS['heure_debut']?>&heure_fin=<?php echo $row_RsHS['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php echo $date_edition.'0';?> "><?php echo Insert_espace($un_gic_classe_ID_default['nom_gic'],13);?></a>
        				<?php
                                };

                                
                                echo '<br/>'.Insert_espace($row_RsHS['groupe'],13).'<br/>'.Insert_espace($row_RsHS['nom_matiere'],13).'<br/>';
								
        echo '<div style="width: 80px;  word-wrap: break-word; overflow:hidden;">';
        if (strlen($row_RsHS['theme_activ'])<28){
              echo '<strong>'.$row_RsHS['theme_activ'].'</strong>';
        }
        else {
             echo '<strong>'.substr($row_RsHS['theme_activ'],0,28).'...'.'</strong>';
        };
        echo '</div>';	                                     
                                if ($row_RsHS['gic_ID']<>0){$row_RsHS['classe_ID']=0;};
                                $lien_a_supprimer="agenda_supprime.php?nom_classe=".$row_RsHS['nom_classe']."&classe_ID=".$row_RsHS['classe_ID']."&gic_ID=".$row_RsHS['gic_ID']."&nom_matiere=".$row_RsHS['nom_matiere']."&groupe=".$row_RsHS['groupe']."&matiere_ID=".$row_RsHS['matiere_ID']."&semaine=".$row_RsHS['semaine']."&heure=".$row_RsHS['heure']."&heure_debut=".$row_RsHS['heure_debut']."&heure_fin=".$row_RsHS['heure_fin']."&current_day_name=".$current_day_name."&code_date=".$madate."0&ID_agenda=".$row_RsHS['ID_agenda'];
                                $lien_a_supprimer.="&jour_pointe=";
                                $lien_a_supprimer.=isset($_GET['jour_pointe'])?$_GET['jour_pointe']:$jour_pointe; 
                                $lien_a_supprimer.=$row_RsHS['duree']!=''?"&duree=".$row_RsHS['duree']:'';
                                
                                ?>
                                <br />
                                <table border="0" cellpadding="0" cellspacing="0">
        			<tr>
        			<td><form name="form_supprime" enctype="multipart/form-data" action="<?php echo $lien_a_supprimer?>" method="post">
        			<input name="img_suppr" type="image" src="../images/ed_delete.gif" alt="Supprimer la fiche" title="Supprimer la fiche" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
        			</form></td>
        			<td>&nbsp;</td>
        			<td>	<?php if (visa_edition_possible($madate)){
        				if ($row_RsHS['gic_ID']<>0){$row_RsHS['classe_ID']=0;};
        				?>
        				<form name="form_edition" enctype="multipart/form-data" action="ecrire.php?saisie=0&ds_prog&nom_classe=<?php echo $row_RsHS['nom_classe']?>&classe_ID=<?php echo $row_RsHS['classe_ID']?>&gic_ID=<?php echo $row_RsHS['gic_ID'];?>&nom_matiere=<?php echo $row_RsHS['nom_matiere']?>&groupe=<?php echo $row_RsHS['groupe']?>&matiere_ID=<?php echo $row_RsHS['matiere_ID']?>&semaine=<?php echo $row_RsHS['semaine']?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&heure=<?php echo $row_RsHS['heure']?>&duree=<?php echo $row_RsHS['duree']?>&heure_debut=<?php echo $row_RsHS['heure_debut']?>&heure_fin=<?php echo $row_RsHS['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php echo $date_edition.'0';?>&pas_de_saisie " method="post">
        				<input name="eye" type="image" src="../images/deja_realise.png" alt="D&eacute;j&agrave; r&eacute;alis&eacute; dans cette classe" title="D&eacute;j&agrave; r&eacute;alis&eacute; dans cette classe" <?php if ((isset($_GET['saisie']))&&($_GET['saisie']==1)){echo ' onClick="return saisie_abandon();"';};?>>
              </form><?php } else { ?><img src="../images/button_no_edit.png" alt="Modification de la fiche impossible" title="Modification de la fiche impossible" border="0" onClick='edition_impossible()'><?php };?>
              </td>
              <td>
              <!--Gestion des absences -->	
              <?php	 
              //Lien vers le module de declaration des absences
			  if ( (isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui'))	{
              	include('../inc/module_absence_hs_dp.php');
			  };
              ?>
              </td>
              </tr>
              </table>
              </td>
              </tr>
              <?php
              		} while ($row_RsHS = mysqli_fetch_assoc($sel_HS));
              		mysqli_free_result($sel_HS);
              		?>
	</table>
              		<?php
              	};
        };
//du sinon "on n'est pas en vacances aujourd'hui"
?>


</div>

<?php
//fin affichage cellules devoirs planifies
//fin du bloc colonne de gauche



//affichage des infos utilisateurs - bloc etat
if (!isset($_GET['code_date'])){
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_Publier = sprintf("SELECT MAX(code_date) FROM cdt_agenda
                WHERE prof_ID=%u",GetSQLValueString($_SESSION['ID_prof'],"int"));
        $sel_Publier = mysqli_query($conn_cahier_de_texte, $query_Publier) or die(mysqli_error($conn_cahier_de_texte));
        $un_Publier = mysqli_fetch_assoc($sel_Publier);
        $date_lastajout=substr($un_Publier['MAX(code_date)'],6,2).'/'.substr($un_Publier['MAX(code_date)'],4,2).'/'.substr($un_Publier['MAX(code_date)'],0,4);
	
	?>
<div id="etat">
  <table width="<?php if((isset($_SESSION['ipad'])) && ($_SESSION['ipad']==1)){echo '760';} else{ echo '830';};?>" height="102" border="0" cellpadding="0" cellspacing="0" class="bordure">
    <tr>
      <td height="20"  class="Style6"><img src="../images/identite.gif" width="16" height="16">&nbsp;&nbsp;<?php echo $_SESSION['identite'];?></td>
      <td class="Style6" ><div align="right"><span onClick="window.open('planning_prof.php?date=<?php echo date('Ymd');?>','_blank'); " id='lien_planning'>Mon planning&nbsp;&nbsp;&nbsp;&nbsp;</span> </div></td>
      <td class="Style6"><span onClick="window.open('imprimer_menu.php','_blank'); " id='lien_cahiers'  align="right" style='display:inline'>Mes cahiers&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span onClick="window.open('cahiers_archives_liste.php','_blank'); " id='lien_cahiers'  align="right" style='display:inline'>Mes archives&nbsp;&nbsp</span></td>     
      <td class="Style6"><span onClick="window.open('progression.php','_blank'); " id='lien_progression'  align="right" style='display:inline'>Carnet de bord - Progressions </span> </td>
    </tr>
    <tr>
      <td colspan="2" valign="top" class="Style15"><p><br><img src="../images/puce_jaune.gif">
              <?php if ($_SESSION['publier_travail']=='O'){
		echo 'Mon travail &agrave; faire est <strong>publi&eacute; en ligne </strong>actuellement. ';} else { echo ' Mon travail &agrave; faire <strong>n\'est pas publi&eacute; en ligne </strong>actuellement.  ';}; ?>
              </p>
			  <p><img src="../images/puce_jaune.gif">
              <?php if ($_SESSION['publier_cdt']=='O'){
                echo 'Mon cahier de textes est <strong>publi&eacute; en ligne </strong>actuellement. ';} else { echo ' Mon cahier de textes <strong>n\'est pas publi&eacute; en ligne </strong>actuellement.  ';}; ?></p>
        
          <?php if ($date_lastajout!='//'){?>
          <p><img src="../images/puce_jaune.gif">&nbsp;Ma derni&egrave;re s&eacute;ance saisie est dat&eacute;e du <a href="ecrire.php?saisie=0&date=<?php echo substr($un_Publier['MAX(code_date)'],0,8);?>"> <strong>
            <?php  echo jour_semaine($date_lastajout).' '.$date_lastajout;?>
          </strong></a>
        <p>
            <?php };
                
                
                if ((isset($_SESSION['id_etat'])) && ($_SESSION['id_etat']==1)){ // titulaire absent ?>
        <p style="color: #FF0000"><strong>Vous &ecirc;tes actuellement d&eacute;clar&eacute;
            absent par<br/>
            l'administration &agrave; dater du <?php echo $date_declare_abs_f;?>.</strong></p>
        <?php 
                	//si titulaire, recherche des noms des remplacants
                	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					// Attention il ne faut donner que les remplacants du titulaires
                	$query_Remplace = sprintf("SELECT identite FROM cdt_prof where (droits=2 AND id_etat=2 AND id_remplace='%u') ORDER BY nom_prof ASC",GetSQLValueString($_SESSION['ID_prof'],"int"));
                	$sel_Remplace = mysqli_query($conn_cahier_de_texte, $query_Remplace) or die(mysqli_error($conn_cahier_de_texte));
                	$un_Remplace = mysqli_fetch_assoc($sel_Remplace);
                	$nb_Remplace = mysqli_num_rows($sel_Remplace);
                	if ($nb_Remplace>0){
                	echo '<p style="color: #FF0000">Rempla&ccedil;ant(s) :&nbsp;';
                	do {
                	echo $un_Remplace['identite'].'&nbsp;&nbsp;';
                	}
                	while ($un_Remplace = mysqli_fetch_assoc($sel_Remplace));
                	echo '</strong></p>';
                	}
                	mysqli_free_result($sel_Remplace);				
                	};
                	
                	//si remplacant, recherche du nom du titulaire
                	if ((isset($_SESSION['id_etat'])) && ($_SESSION['id_etat']==2)){
                	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                	$query_titulaire = sprintf("SELECT identite,date_declare_absent FROM cdt_prof where (droits=2 AND ID_prof= %u)",$_SESSION['id_remplace']);
                	$sel_titulaire = mysqli_query($conn_cahier_de_texte, $query_titulaire) or die(mysqli_error($conn_cahier_de_texte));
                	$un_titulaire = mysqli_fetch_assoc($sel_titulaire);
                	$nb_titulaire = mysqli_num_rows($sel_titulaire);
                	if ($nb_titulaire>0){
					$date_declare_absent_form=substr($un_titulaire['date_declare_absent'],8,2).'-'.substr($un_titulaire['date_declare_absent'],5,2).'-'.substr($un_titulaire['date_declare_absent'],0,4);
                	echo '<p style="color: #FF0000">Vous remplacez  '.$un_titulaire['identite'].' &agrave; dater du '.$date_declare_absent_form.'</strong></p>';
                	}
                	mysqli_free_result($sel_titulaire);	
                	}
                	?>
         <?php // <p> <em><br>  <em><br> Cliquez sur la date de votre choix.... </em><br> </em></p> ?>
		 
		 </td>
      <td align="center" ><?php if (substr($_SESSION['date_visa'],0,4)<>'0000')  {
			$date_form_visa=substr($_SESSION['date_visa'],8,2).'/'.substr($_SESSION['date_visa'],5,2).'/'.substr($_SESSION['date_visa'],0,4);
			
			echo "  <div align=\"center\" class=\"Style2\" ><br /><br /><img src=\"../images/visa.gif\" width=\"60\" height=\"60\"  ><br /><br /><em>Le ".jour_semaine($date_form_visa).' '.$date_form_visa."</em> </div>";}; ?>
      </td>
	  <td>
<?php // ====================================================affichage des messages===================================================messa_debut
include 'evenement_select.php' ; // listes jours, listes classes, routines dates 
$duree_max=7 ; //duree d'affichage des messages pour  $_SESSION['afficher_messages']=='N' si date_fin_publier est non renseigne soit valeur 0000-00-00 
?>

<script type="text/javascript">
			$(document).ready(function(){
			$("#voir_msg").click(function(){
			$(".msg_ppe").slideToggle("fast");
			$(".msg_pp").slideToggle("fast");
			$(".msg_adm").slideToggle("fast");
			$(".msg_adme").slideToggle("fast");
			$(".msg_inv").slideToggle("fast");
			});	
			});
			
function afficheMasque(){
$(document).ready(function(){
			(".msg_adme").slideToggle("fast");
			});}
  </script>
 

 <div  align="center"  class="Style2" id="voir_msg"  > 
              <br><em>
              <img src="../images/post-it2.jpg" alt="post-it" border="0" height="40" width="40" >&nbsp;<br />
              D&eacute;ployer / R&eacute;sumer<br> les messages</em> </div>
  </td>  </tr>
  </table>  <br>
<?php //===================================on separe la zone message en deux colonnes  ============================================= ?>
	<table width="830" cellspacing="0" cellpadding="0" border="0">
	<tr>  <td width="410"  valign="top" style="margin-top:0px ;padding-top:0px;"  > 

<?php //=============================================evenements à venir==============================================================messa_evene

	    require_once('../inc/even_a_venir_inc.php');	
		
			$query_evenements="SELECT * FROM cdt_evenement_contenu 
			WHERE  (  date_debut >= {$datemini}  	AND  date_debut <= {$datemaxi} ) 
				OR (  date_debut < {$datemini}  	AND  date_fin >= {$datemini} )
				ORDER BY date_debut , heure_debut  "  ;
			$sel_evenements = mysqli_query($conn_cahier_de_texte, $query_evenements) or die(mysqli_error($conn_cahier_de_texte));
			$un_evene = mysqli_fetch_assoc($sel_evenements);
			$nb_evenements = mysqli_num_rows($sel_evenements);	
					
	if ($nb_evenements>0) 
		{ ?>
			<table width="410"  border="0" cellpadding="0" cellspacing="0" class="bordure" >
			<tr><td height="20"  class="Style6"><span onClick="window.open('evenement_liste.php','_blank'); " id='lien_even'>Ev&egrave;nements en cours et &agrave; venir<span style="margin-left:160px;"><img src="../images/even_planning.png" width="14" height="14"></span></span></td>	
			</tr>
			<tr><td valign="top" class="Style15" style="padding-top:10px;" >
			
			
			<table> <tr>
			<?php $nb=0; $jm_traite="";$jm_planning=substr($_GET['date'],6,2).'-'.substr($_GET['date'],4,2);
			do { $fluo='';
				$jm_fiche=substr($un_evene['date_debut'],8,2).'-'.substr($un_evene['date_debut'],5,2) ;
				if ( $jm_planning ==$jm_fiche ) {$fluo='style="background:yellow; " ';}	else { $fluo='';};
				if ($jm_fiche <> $jm_traite) // on saute une li et on affiche la date du jour
					{ $jm_traite=$jm_fiche;	
					if (( $un_evene['etat']=='Report')||( $un_evene['etat']=='annulé' ) ) {$fluo=' style=" text-decoration: line-through; " '.$fluo ;};
					echo '<tr></tr> <tr></tr> <tr> <td class="Style15" width="60" '.$fluo.' >';

					if  ( ($un_evene['date_debut'] <  date('Y-m-d') ) && ( $un_evene['date_fin'] > date('Y-m-d') ) )
						{ $fin_even = '->> '.substr($un_evene['date_fin'],8,2).'-'.substr($un_evene['date_fin'],5,2);	echo $fin_even.'</td> ';}
						else {$fin_even =''; echo jour3_ymd($un_evene['date_debut']).$jm_fiche.'</td> ';};}
					//if  ( ($un_evene['date_debut'] <  date('Y-m-d') ) && ( $un_evene['date_fin'] > date('Y-m-d') ) ){ echo $fin_even;};						
				else { echo '<tr> <td></td> ';}; // on ne repete pas la date du jour
				//echo '<td class="Style15"'.$fluo.'>'.$fin_even.$un_evene['classes_conc'].'</td> <td class="Style15" '.$fluo.'>'.$un_evene['titre_even'].'</td></tr>' ; }
				echo '<td class="Style15"'.$fluo.'>'.$un_evene['classes_conc'].'</td> <td class="Style15" '.$fluo.'><a class="discret"  href="evenement_fiche.php?ID_even='.$un_evene['ID_even'].' " target="_blank" >'.$un_evene['titre_even'].'</a></td></tr>' ; }
				
			while ( $un_evene = mysqli_fetch_assoc($sel_evenements) );
			 ?>
			</table>
			
		<br></td></tr>
			</table>
			<br>
		
		<?php } ;
		mysqli_free_result($sel_evenements);	

 // ==================================routines pour afficher les messages de tous type =========================================messa_functions
   
function cherche_pj($query)  //lite les fichiers joints associes a un message 
{	
	$query_pj="SELECT * FROM cdt_message_fichiers WHERE message_ID=".$query; // .'"'  ?
		global $database_conn_cahier_de_texte, $conn_cahier_de_texte ,$sel_pj, $une_pj,$nb_pj;
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$sel_pj = mysqli_query($conn_cahier_de_texte, $query_pj) or die(mysqli_error($conn_cahier_de_texte));
		$une_pj = mysqli_fetch_assoc($sel_pj);
		$nb_pj = mysqli_num_rows($sel_pj);
						
};				
 
 
function liste_pj()  //edition des fichiers joints associes a un message( $query_.....)  
{ global $sel_pj, $une_pj, $nb_pj ;
  if ($nb_pj >0) 
				{
					if ($nb_pj>1){echo '<p>Documents joints : ';} else {echo '<p>Document joint : ';};
					do {$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $une_pj['nom_fichier']);
						echo '<img src="../images/attach.png" alt="fichier_joint"><a href="../fichiers_joints_message/'.$une_pj['nom_fichier'].'"  target="_blank"/>'.$nom_f.'</a> &nbsp; &nbsp; ';
						
					} while ($une_pj = mysqli_fetch_assoc($sel_pj));
					echo '</p>';
				};
			
} ;

function entete_message( $nom_classe,$date_envoi,$identite,$nom_prof,$prof_ID,$email,$ID_message)
{ global $nb_pj  ;
	echo '<p';
	if (substr($date_envoi,0,10)==date('Y-m-d')){echo ' style = "background: #F8CE70"';};
	echo '><img src="../images/puce_jaune.gif">&nbsp;<b>'.$nom_classe.' &nbsp; ';
	echo substr($date_envoi,8,2).'/'.substr($date_envoi,5,2).'/'.substr($date_envoi,0,4).'</b> - ';
	if($identite==''){echo $nom_prof;} else {echo $identite;};
	echo ' - '.substr($date_envoi,11,5) ;
					
	if (($prof_ID<>$_SESSION['ID_prof'] )&&($email<>''))
	{ echo ' <a href="mailto:'.$email. '"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l\'enseignant" title="Contacter l\' enseignant" /></a>';
	};

	if ($prof_ID==$_SESSION['ID_prof']) 
	{ echo '&nbsp;&nbsp;<img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL(\'window\',\'message_modif.php?ID_message='.$ID_message.'&dest_profs=0\');return document.MM_returnValue">&nbsp'; 
	
	echo '&nbsp; <img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick= "return confirmation(\'';
				if ($nb_pj==1){ echo ' et sa pi&egrave;ce jointe attach&eacute;e';} ;
				if ($nb_pj >1 ){ echo ' et ses '.$nb_pj.' pi&egrave;ces jointes attach&eacute;es';};
				echo '\' ,\''.$ID_message.'\')" >'; 
	};
	
	echo '<br>';

} ;

//=============================================messages envoyes par les invites==================================================messa_invites

       		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_msg_Invite =sprintf("SELECT * FROM cdt_message_contenu,cdt_message_destinataire_profs WHERE cdt_message_contenu.dest_ID=2 
AND SUBSTR(date_envoi,1,10)<=date_fin_publier
AND cdt_message_contenu.date_fin_publier>= '%s'
AND  cdt_message_contenu.ID_message=cdt_message_destinataire_profs.message_ID  AND cdt_message_contenu.online='O' AND cdt_message_destinataire_profs.prof_ID = %u AND cdt_message_contenu.prof_ID=0 ORDER BY date_envoi DESC ",date('Y-m-d'),GetSQLValueString($_SESSION['ID_prof'],"int")) ;
			
			$sel_msg_Invite = mysqli_query($conn_cahier_de_texte, $query_msg_Invite) or die(mysqli_error($conn_cahier_de_texte));
			$un_msg_Invite = mysqli_fetch_assoc($sel_msg_Invite);
			$nb_msg_Invite = mysqli_num_rows($sel_msg_Invite);
			$nb_total_msg=$nb_msg_Invite;
	
	if ($nb_msg_Invite >0)
			{?>
				<br />
				<table width="410"  border="0" cellpadding="0" cellspacing="0" class="bordure">
				<tr>	<td height="20"  class="Style6">Messages re&ccedil;us de vos invit&eacute;s</td></tr>
				<tr><td valign="top" class="Style15" style="padding-top:10px;">
				<br />
			<?php 
				$li=0;
		do { 	$li=$li+1;
				cherche_pj($un_msg_Invite['ID_message']);
				entete_message( $un_msg_Invite['nom_classe'],$un_msg_Invite['date_envoi'],$un_msg_Invite['identite'],$un_msg_Invite['nom_prof'],$un_msg_Invite['prof_ID'],$un_msg_Invite['email'],$un_msg_Invite['ID_message'] );
												
				
				echo '<div  class="msg_ppe" style="margin-left:12;';
				
				if ((isset($_SESSION['afficher_messages']))&&($_SESSION['afficher_messages']=='N'))
				
				{ echo 'display:none">';}	  else 		{	echo 'display:block">';	};	
					echo $un_msg_Invite['message']; 
					if ($nb_pj>0) { liste_pj();} ;            // affiche les pj 
					echo '</div>';
					if($li < $nb_msgPP_to_profs){ echo '<p class="bas_ligne" style="margin-top:0px;" ></p>';};	  
		} while ($un_msg_Invite = mysqli_fetch_assoc($sel_msg_Invite)); 
			
			?>
			<div style="margin-left:12" > &nbsp; </div>
			</td></tr>
			</table> <?php echo '<br>'; ?>	
		<?php };
 mysqli_free_result($sel_msg_Invite);

//==========================messages des professeurs principaux aux enseignants ================================================messa_pp-profs
			//messages des professeurs principaux 
			
			// la table cdt_groupe_interclasses_classe est-elle vide ?
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_vide_gic = "SELECT * FROM cdt_groupe_interclasses_classe";
			$sel_vide_gic = mysqli_query($conn_cahier_de_texte, $query_vide_gic) or die(mysqli_error($conn_cahier_de_texte));
			$un_vide_gic = mysqli_fetch_assoc($sel_vide_gic);
			$nb_vide_gic = mysqli_num_rows($sel_vide_gic);
			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			
			if ($nb_vide_gic ==0){
				$query_msgPP_to_profs =sprintf("
					SELECT DISTINCT ID_message,nom_classe,message,date_envoi,identite,nom_prof,email,cdt_message_contenu.prof_ID
					FROM cdt_classe, cdt_emploi_du_temps, cdt_message_contenu, cdt_prof_principal, cdt_prof,cdt_groupe
					WHERE `dest_ID`=2
					AND SUBSTR(date_envoi,1,10)<=date_fin_publier
					AND cdt_message_contenu.date_fin_publier>= '%s' 
					AND cdt_classe.ID_classe = cdt_emploi_du_temps.classe_ID AND cdt_groupe.groupe = cdt_emploi_du_temps.groupe
					AND cdt_emploi_du_temps.prof_ID = %u
					AND cdt_prof_principal.pp_classe_ID = cdt_classe.ID_classe
					AND cdt_message_contenu.prof_ID = cdt_prof_principal.pp_prof_ID
					AND cdt_message_contenu.prof_ID = cdt_prof.ID_prof
					AND cdt_message_contenu.pp_classe_ID =cdt_classe.ID_classe
					AND (cdt_message_contenu.pp_groupe_ID =cdt_groupe.ID_groupe OR cdt_groupe.ID_groupe=1 OR cdt_message_contenu.pp_groupe_ID=1)
					AND cdt_message_contenu.online='O'
					ORDER BY date_envoi DESC,nom_classe ASC",date('Y-m-d'),GetSQLValueString($_SESSION['ID_prof'],"int"));
			} else {
				$query_msgPP_to_profs =sprintf("
					SELECT DISTINCT ID_message,nom_classe,message,date_envoi,identite,nom_prof,email,cdt_message_contenu.prof_ID
					FROM cdt_classe, cdt_emploi_du_temps, cdt_message_contenu, cdt_prof_principal, cdt_prof,cdt_groupe, cdt_groupe_interclasses_classe
					WHERE `dest_ID`=2
					AND SUBSTR(date_envoi,1,10)<=date_fin_publier
					AND cdt_message_contenu.date_fin_publier>= '%s' 
					AND 
					((cdt_classe.ID_classe = cdt_emploi_du_temps.classe_ID AND cdt_groupe.groupe = cdt_emploi_du_temps.groupe )
					OR
					(cdt_emploi_du_temps.classe_ID=0 AND cdt_emploi_du_temps.gic_ID = cdt_groupe_interclasses_classe.gic_ID AND cdt_classe.ID_classe=cdt_groupe_interclasses_classe.classe_ID))
					AND cdt_emploi_du_temps.prof_ID = %u
					AND cdt_prof_principal.pp_classe_ID = cdt_classe.ID_classe
					AND cdt_message_contenu.prof_ID = cdt_prof_principal.pp_prof_ID
					AND cdt_message_contenu.prof_ID = cdt_prof.ID_prof
					AND cdt_message_contenu.pp_classe_ID =cdt_classe.ID_classe
					AND (cdt_message_contenu.pp_groupe_ID =cdt_groupe.ID_groupe OR cdt_groupe.ID_groupe=1 OR cdt_message_contenu.pp_groupe_ID=1)
					AND cdt_message_contenu.online='O'
					ORDER BY date_envoi DESC,nom_classe ASC",date('Y-m-d'),GetSQLValueString($_SESSION['ID_prof'],"int"));
				
			;}
			
			$sel_msgPP_to_profs = mysqli_query($conn_cahier_de_texte, $query_msgPP_to_profs) or die(mysqli_error($conn_cahier_de_texte));
			$un_msgPP_to_profs = mysqli_fetch_assoc($sel_msgPP_to_profs);
			$nb_msgPP_to_profs = mysqli_num_rows($sel_msgPP_to_profs);
			$nb_total_msg+=$nb_msgPP_to_profs;
			
			
		
			
	if ($nb_msgPP_to_profs>0) 
		{ ?>
				<table width="410"  border="0" cellpadding="0" cellspacing="0" class="bordure">
				<tr><td height="20"  class="Style6">Messages de professeurs principaux
				</td></tr>
				
				<tr> <td valign="top" class="Style15" style="padding-top:10px;">
		<?php 


		$li=0;
		do { 	$li=$li+1;
		
				cherche_pj($un_msgPP_to_profs['ID_message']);
				entete_message( $un_msgPP_to_profs['nom_classe'],$un_msgPP_to_profs['date_envoi'],$un_msgPP_to_profs['identite'],$un_msgPP_to_profs['nom_prof'],$un_msgPP_to_profs['prof_ID'],$un_msgPP_to_profs['email'],$un_msgPP_to_profs['ID_message'] );
												
				echo '<div  class="msg_ppe" style="margin-left:12;';
				
				if ((isset($_SESSION['afficher_messages']))&&($_SESSION['afficher_messages']=='N'))
				
				{ echo 'display:none">';}	  else 		{	echo 'display:block">';	};	
				echo $un_msgPP_to_profs['message']; 
					if ($nb_pj>0) { liste_pj();} ;            // affiche les pj 
					echo '</div>';
					if($li < $nb_msgPP_to_profs){ echo '<p class="bas_ligne" style="margin-top:0px;"></p>';};
				//on met en tableau le ref de ces messages. Il ne devront par reapparaitre ds les message aux autres enseignants
				if ($_SESSION['prof_mess_pp']=='Oui'){
				
				$ref_mess_pp[$li]=$un_msgPP_to_profs['ID_message'];
				
				};
				
				
		} while ($un_msgPP_to_profs = mysqli_fetch_assoc($sel_msgPP_to_profs)); 
			
			?>
			<div style="margin-left:12"> &nbsp; </div>
			</td></tr>
			</table> <?php echo '<br>'; ?>
		<?php };
	 mysqli_free_result($sel_msgPP_to_profs);

//==============================messages entre professeurs si option activee (parametre administrateur)========================messa_prof_profs

// Ne pas afficher ici les messages de la Vie scolaire - Webmestre ou Responsable Etablissement
		$query_prof_to_profs =sprintf("SELECT * FROM cdt_message_contenu,cdt_prof WHERE cdt_message_contenu.dest_ID=2 
AND SUBSTR(date_envoi,1,10)<=date_fin_publier 
AND cdt_message_contenu.date_fin_publier>= '%s' 
AND cdt_message_contenu.prof_ID=cdt_prof.ID_prof AND cdt_prof.droits=2  AND cdt_message_contenu.online='O' ORDER BY date_envoi,ID_message",date('Y-m-d'));
		$sel_prof_to_profs = mysqli_query($conn_cahier_de_texte, $query_prof_to_profs) or die(mysqli_error($conn_cahier_de_texte));
		$un_prof_to_profs = mysqli_fetch_assoc($sel_prof_to_profs);
		$nb_prof_to_profs = mysqli_num_rows($sel_prof_to_profs);
	
		if ($nb_prof_to_profs>0) 
		{ 
		if ($_SESSION['prof_mess_all']=='Oui'){
		?>
				<table width="410"  border="0" cellpadding="0" cellspacing="0" class="bordure">
				<tr><td height="20"  class="Style6">Messages des autres enseignants		</td></tr>
				
				<tr> <td valign="top" class="Style15" style="padding-top:10px;">
				<?php
		$li=0;
		mysqli_data_seek($sel_prof_to_profs, 0);
		do { 	
		
			
			$li=$li+1;
				cherche_pj($un_prof_to_profs['ID_message']);
				entete_message( /*$un_prof_to_profs['nom_classe'],*/ '',$un_prof_to_profs['date_envoi'],$un_prof_to_profs['identite'],$un_prof_to_profs['nom_prof'],$un_prof_to_profs['prof_ID'],$un_prof_to_profs['email'],$un_prof_to_profs['ID_message'] );
												
				echo '<div  class="msg_ppe" style="margin-left:12;';
				if ((isset($_SESSION['afficher_messages']))&&($_SESSION['afficher_messages']=='N'))
				{ echo 'display:none">';}	  else 		{	echo 'display:block">';	};	
					echo $un_prof_to_profs['message']; 
					if ($nb_pj>0) { liste_pj();} ;            // affiche les pj 
					echo '</div>';
					echo $li.'<br>';
					if($li < $nb_prof_to_profs){ echo '<p class="bas_ligne" style="margin-top:0px;" ></p>';};	
			
		} while ($un_prof_to_profs = mysqli_fetch_assoc($sel_prof_to_profs)); 
?>
			<div style="margin-left:12"> &nbsp; </div>
			</td></tr>
			</table> 
			<?php 
			echo '<br>'; 
		}
		else
		{
		//A faire si message diffuse par autres enseignants que le pp est autorise 
		//je recherche mes classes

		if ($_SESSION['prof_mess_pp']=='Oui'){
				$query_Rsmc =sprintf("SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe,cdt_emploi_du_temps WHERE cdt_classe.ID_classe=cdt_emploi_du_temps.classe_ID AND prof_ID=%u ORDER BY nom_classe ASC",$_SESSION['ID_prof']);
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Rsmc = mysqli_query($conn_cahier_de_texte, $query_Rsmc) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmc = mysqli_fetch_assoc($Rsmc);
$totalRows_Rsmc = mysqli_num_rows($Rsmc);

	
// je limite l affichage aux messages a destination de mes classes
do {
$message_affiche=0;
		$li=0;
		mysqli_data_seek($sel_prof_to_profs, 0);
		do { 	
		
			if ($un_prof_to_profs['pp_classe_ID']==$row_Rsmc['ID_classe']){
			// on recherche si ce message n a pas ete diffuse deja par un pp
			
			$mess_present=false;
			for ($r=1; $r<=$nb_total_msg; $r++) { 
			if ($un_prof_to_profs['ID_message']==$ref_mess_pp[$r]){$mess_present=true;};
			};
			//- fin de recherche de presence dans les mess de pp
			

			//si le precedent message est le meme ne pas afficher - car double affichage pour l'auteur du message
			if(	$un_prof_to_profs['ID_message']==$message_affiche){$mess_present=true;};
				
			if ($mess_present==false){
			if ($li==0){
				?>
				<table width="410"  border="0" cellpadding="0" cellspacing="0" class="bordure">
				<tr><td height="20"  class="Style6">Messages des autres enseignants		</td></tr>
				
				<tr> <td valign="top" class="Style15" style="padding-top:10px;">
		<?php 
			};
			$li=$li+1;
			   //echo $un_prof_to_profs['prof_ID'].' et '.$_SESSION['ID_prof'];
				cherche_pj($un_prof_to_profs['ID_message']);
				entete_message( /*$un_prof_to_profs['nom_classe'],*/ '',$un_prof_to_profs['date_envoi'],$un_prof_to_profs['identite'],$un_prof_to_profs['nom_prof'],$un_prof_to_profs['prof_ID'],$un_prof_to_profs['email'],$un_prof_to_profs['ID_message'] );
				echo '<div  class="msg_ppe" style="margin-left:12;';
				if ((isset($_SESSION['afficher_messages']))&&($_SESSION['afficher_messages']=='N'))
				{ echo 'display:none">';}	  else 		{	echo 'display:block">';	};	
					echo $un_prof_to_profs['message']; 
					if ($nb_pj>0) { liste_pj();} ;            // affiche les pj 
					echo '</div>';
echo '<br>';
			$message_affiche=$un_prof_to_profs['ID_message'];
			};
			}; 
			
		} while ($un_prof_to_profs = mysqli_fetch_assoc($sel_prof_to_profs)); 

} while ($row_Rsmc = mysqli_fetch_assoc($Rsmc)); 	
		?>
			<div style="margin-left:12"> &nbsp; </div>
			</td></tr>
			</table> <?php echo '<br>'; ?>
		<?php 
		mysqli_free_result($Rsmc);
		};
		};
		};
mysqli_free_result($sel_prof_to_profs);

//=========================================messages vie scolaire ou direction aux seuls enseignants ===========================messa_dir_profs
			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_dir_to_profs =sprintf("SELECT * FROM cdt_message_contenu,cdt_message_destinataire_profs,cdt_prof WHERE cdt_message_contenu.dest_ID=2 AND SUBSTR(date_envoi,1,10)<=date_fin_publier AND cdt_message_contenu.date_fin_publier>= '%s' AND  cdt_message_contenu.ID_message=cdt_message_destinataire_profs.message_ID  AND cdt_message_contenu.online='O' AND cdt_message_contenu.prof_ID= cdt_prof.ID_prof AND cdt_message_destinataire_profs.prof_ID = %u
				ORDER BY date_envoi DESC,nom_prof ASC ",date('Y-m-d'),GetSQLValueString($_SESSION['ID_prof'],"int")) ;
			
			$sel_dir_to_profs = mysqli_query($conn_cahier_de_texte, $query_dir_to_profs) or die(mysqli_error($conn_cahier_de_texte));
			$un_dir_to_profs = mysqli_fetch_assoc($sel_dir_to_profs);
			$nb_dir_to_profs = mysqli_num_rows($sel_dir_to_profs);
			$nb_total_msg+=$nb_dir_to_profs;
		
	if ($nb_dir_to_profs>0) 
		{ ?>
				<table width="410"  border="0" cellpadding="0" cellspacing="0" class="bordure">
				<tr><td height="20"  class="Style6">Messages aux enseignants	</td></tr>
				
				<tr> <td valign="top" class="Style15" style="padding-top:10px;">
		<?php 


		$li=0;
		do { 	$li=$li+1;
		$un_dir_to_profs['nom_classe']='';
				cherche_pj($un_dir_to_profs['ID_message']); //pas injecetr l'id sinon tout le monde peut modifier ou supprimer !
				entete_message( $un_dir_to_profs['nom_classe'],$un_dir_to_profs['date_envoi'],$un_dir_to_profs['identite'],$un_dir_to_profs['nom_prof'],"pas injecter ID!",$un_dir_to_profs['email'],$un_dir_to_profs['ID_message'] );
												
				echo '<div  class="msg_ppe" style="margin-left:12;';
				
				if ((isset($_SESSION['afficher_messages']))&&($_SESSION['afficher_messages']=='N'))
				
				{ echo 'display:none">';}	  else 		{	echo 'display:block">';	};	
					echo $un_dir_to_profs['message']; 
					if ($nb_pj>0) { liste_pj();} ;            // affiche les pj 
					echo '</div>';
					if($li < $nb_dir_to_profs){ echo '<p class="bas_ligne" style="margin-top:0px;" ></p>';};	  
		} while ($un_dir_to_profs = mysqli_fetch_assoc($sel_dir_to_profs)); 
			
			?>
			<div style="margin-left:12" > &nbsp; </div>
			</td></tr>
			</table> 	<?php echo '<br>'; ?>
		<?php };

	mysqli_free_result($sel_dir_to_profs);


//================================================== 2eme colonne  messages aux élèves  ============================================messa_pp-elv
		echo '</td> <td width="10"> </td> <td  valign="top"> ';
//=================================messages du prof principal et actuellement publies ====================================================

			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_message =sprintf("SELECT * FROM cdt_message_contenu,cdt_classe,cdt_groupe,cdt_prof WHERE cdt_message_contenu.dest_ID<2
AND SUBSTR(date_envoi,1,10)<=date_fin_publier
AND cdt_message_contenu.date_fin_publier>= '%s' 
AND cdt_message_contenu.prof_ID = %u AND cdt_message_contenu.prof_ID=cdt_prof.ID_prof AND cdt_message_contenu.pp_classe_ID=cdt_classe.ID_classe AND cdt_message_contenu.pp_groupe_ID=cdt_groupe.ID_groupe AND cdt_message_contenu.online='O' ORDER BY date_envoi DESC,ID_message" ,date('Y-m-d'),GetSQLValueString($_SESSION['ID_prof'],"int"));
			
			$sel_msg_pp_elv = mysqli_query($conn_cahier_de_texte, $query_message) or die(mysqli_error($conn_cahier_de_texte));
			$un_msg_pp_elv = mysqli_fetch_assoc($sel_msg_pp_elv);
			$nb_msg_pp_elv = mysqli_num_rows($sel_msg_pp_elv);
			//$nb_total_msg+=$nb_message;
			$nb_total_msg+=$nb_msg_pp_elv;
		
		if ($nb_msg_pp_elv>0) 
		{ ?>
				<table width="410"  border="0" cellpadding="0" cellspacing="0" class="bordure">
				<tr>
				  <td height="20"  class="Style6">Messages diffus&eacute;s aux &eacute;l&egrave;ves en tant que prof. principal
				</td>
				</tr>
				
				<tr> <td valign="top" class="Style15" style="padding-top:10px;">
		<?php 


		$li=0;
		do { 	$li=$li+1;
				cherche_pj($un_msg_pp_elv['ID_message']);//$nom_classe,$date_envoi,$identite,$nom_prof,$prof_ID,$email,$ID_message
				entete_message( $un_msg_pp_elv['nom_classe'],$un_msg_pp_elv['date_envoi'],$un_msg_pp_elv['identite'],$un_msg_pp_elv['nom_prof'],$un_msg_pp_elv['prof_ID'],$un_msg_pp_elv['email'],$un_msg_pp_elv['ID_message'] );
												

				echo '<div  class="msg_ppe" style="margin-left:12;';

				 	
				if ((isset($_SESSION['afficher_messages']))&&($_SESSION['afficher_messages']=='N'))
				
				{ echo 'display:none">';}	  else 		{	echo 'display:block">';	};	
					echo $un_msg_pp_elv['message']; 
					if ($nb_pj>0) { liste_pj();} ;            // affiche les pj 
					echo '</div>';
					if($li < $nb_msg_pp_elv){ echo '<p class="bas_ligne" style="margin-top:0px;" ></p>';};	  
		} while ($un_msg_pp_elv = mysqli_fetch_assoc($sel_msg_pp_elv)); 
			
			?>
			<div style="margin-left:12" > &nbsp; </div>
			</td></tr>
			</table> 	<?php echo '<br>'; ?>
		<?php };
				
	mysqli_free_result($sel_msg_pp_elv);		

//====================================message vie scolaire et resp.etab. a tous eleves  =======================================messa_dir_tous
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_dir_to_tous =sprintf("SELECT * FROM cdt_message_contenu, cdt_prof WHERE cdt_message_contenu.dest_ID<2 AND SUBSTR(date_envoi,1,10)<=date_fin_publier AND  cdt_message_contenu.date_fin_publier>= '%s' AND cdt_message_contenu.online='O' AND cdt_message_contenu.prof_ID= cdt_prof.ID_prof AND cdt_prof.droits>2 ORDER BY date_envoi DESC,nom_prof ASC ",date('Y-m-d')) ;
			
			$sel_dir_to_tous = mysqli_query($conn_cahier_de_texte, $query_dir_to_tous) or die(mysqli_error($conn_cahier_de_texte));
			$un_dir_to_tous = mysqli_fetch_assoc($sel_dir_to_tous);
			$nb_dir_to_tous = mysqli_num_rows($sel_dir_to_tous);
			$nb_total_msg+=$nb_dir_to_tous;
	
if ($nb_dir_to_tous>0) 
		{ ?>
				<table width="410"  border="0" cellpadding="0" cellspacing="0" class="bordure">
				<tr><td height="20"  class="Style6">Messages &agrave tous les &egrave;l&egrave;ves et parents <i>(consultant le cdt !)</i> </td></tr>
				
				<tr> <td valign="top" class="Style15" style="padding-top:10px;">
		<?php 


		$li=0;
		do { 	$li=$li+1;
		$un_dir_to_tous['nom_classe']='';
				cherche_pj($un_dir_to_tous['ID_message']);
				entete_message( $un_dir_to_tous['nom_classe'],$un_dir_to_tous['date_envoi'],$un_dir_to_tous['identite'],$un_dir_to_tous['nom_prof'],$un_dir_to_tous['prof_ID'],$un_dir_to_tous['email'],$un_dir_to_tous['ID_message'] );
												
				echo '<div  class="msg_ppe" style="margin-left:12;';
				
				if ((isset($_SESSION['afficher_messages']))&&($_SESSION['afficher_messages']=='N'))
				
				{ echo 'display:none">';}	  else 		{	echo 'display:block">';	};	
				
					echo $un_dir_to_tous['message']; 
					if ($nb_pj>0) { liste_pj();} ;            // affiche les pj 
					echo '</div>';
					if($li < $nb_dir_to_tous){ echo '<p class="bas_ligne" style="margin-top:0px;" ></p>';};
		} while ($un_dir_to_tous = mysqli_fetch_assoc($sel_dir_to_tous)); 
			
			?>
			<div style="margin-left:12"> &nbsp; </div>
			</td></tr>
			</table> 
		<?php };
	 mysqli_free_result($sel_dir_to_tous);
	
		?>
		</td></tr></table>	
		<?php echo '<br>'; 
};
		
//========================================= fin du bloc etat depuis 1899=====================================================messa_end

if (isset($_GET['jour_pointe'])) { 
	$lien_supprime = '?nom_classe='.$_GET['nom_classe'].'&classe_ID='.$_GET['classe_ID'].'&gic_ID='.$_GET['gic_ID'].'&nom_matiere='.$_GET['nom_matiere'].'&groupe='.$_GET['groupe'].'&matiere_ID='.$_GET['matiere_ID'].'&semaine='.$_GET['semaine'].'&jour_pointe='.$_GET['jour_pointe'].'&heure='.$_GET['heure'].'&heure_debut='.$_GET['heure_debut'].'&heure_fin='.$_GET['heure_fin'].'&current_day_name='.$_GET['current_day_name'].'&code_date='.$_GET['code_date'];
	if(isset($_GET['duree'])){$lien_supprime.='&duree='.$_GET['duree'];};  
	
	
	
	//******************************************************
	//      PREMIERE PARTIE - AFFICHAGE DU FORMULAIRE
	//******************************************************	
?>	
<div class="block_centre_accueil" >	
<?php	
if (!isset($_GET['pas_de_saisie'])) {
	
	
	// S'il y a saisie d'un devoir, listage_simple=1
	if (isset($_GET['listage_simple'])){$listage_simple=1;};
	
	//Verification que c'est une heure partagee ou non
	$heurepartagee=false;
	$ProfID=$_SESSION['ID_prof'];
	if (isset($_GET['share']) && $_GET['share']=='O' ){
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_partage = sprintf("SELECT cdt_emploi_du_temps.prof_ID FROM cdt_emploi_du_temps_partage,cdt_emploi_du_temps WHERE cdt_emploi_du_temps_partage.profpartage_ID=%u AND cdt_emploi_du_temps_partage.ID_emploi=%u AND cdt_emploi_du_temps_partage.ID_emploi=cdt_emploi_du_temps.ID_emploi",GetSQLValueString($_SESSION['ID_prof'],"int"),GetSQLValueString($_GET['edtID'],"int"));
		$sel_partage = mysqli_query($conn_cahier_de_texte, $query_partage) or die(mysqli_error($conn_cahier_de_texte));
		$un_partage = mysqli_fetch_assoc($sel_partage);
		$totalun_partage = mysqli_num_rows($sel_partage);
		if ($totalun_partage==1) {
			$ProfID=$un_partage['prof_ID'];
			$heurepartagee=true;
		};
		mysqli_free_result($sel_partage);
	};
	?>
	
	

	
	<script type="text/javascript">
	
	
	
	$(document).ready(function(){
	$(".voir_nextcours0").click(function(){	
	$("#nextcours0").slideToggle("slow"); 
	});	
	$(".voir_d1").click(function(){
	$("#d1").slideToggle("fast"); 
	$("#d1").style("overflow: auto;");
	});
	$(".voir_d2").click(function(){
	$("#d2").slideToggle("slow"); 
	});
	$("#voir_d3").click(function(){
	$("#d3").slideToggle("slow"); 
	});
	$("#voir_d4").click(function(){
	$.post('ajax_absents_du_jour.php', {
	classe_ID:<?php echo $_GET['classe_ID']; ?>,
	gic_ID:<?php echo $_GET['gic_ID']; ?>,
	matiere_ID:<?php echo $_GET['matiere_ID']; ?>,
	heure:<?php echo $_GET['heure']; ?>,
	code_date:<?php echo $_GET['code_date']; ?>
	} , function(data){
	$("#d4").html(data).slideToggle("slow");
	}, "text" );
	
	});
	});
	</script>
	<div>
	<?php
	
	//determination de l'ID de la premiere classe d'un regroupement
	if ((isset($_GET['classe_ID']))&&($_GET['classe_ID']==0)&&(isset($_GET['gic_ID']))) {
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_gic_classe_ID_default =sprintf("SELECT cdt_groupe_interclasses_classe.classe_ID FROM cdt_groupe_interclasses_classe WHERE cdt_groupe_interclasses_classe.gic_ID = %u LIMIT 1",$_GET['gic_ID']);
		$sel_gic_classe_ID_default = mysqli_query($conn_cahier_de_texte, $query_gic_classe_ID_default) or die(mysqli_error($conn_cahier_de_texte));
		$un_gic_classe_ID_default = mysqli_fetch_assoc($sel_gic_classe_ID_default);
	};

	
	
	$codedate_Travail = "0";
	if (isset($_GET['code_date'])) {
		$codedate_Travail = (get_magic_quotes_gpc()) ? $_GET['code_date'] : addslashes($_GET['code_date']);
	}
	
	
	$sup_ch='';
	//Devoir
	if (isset($_GET['ds_prog'])){$sup_ch="AND substring(code_date,9,1)=0 AND substring(t_code_date,3,1)='/'";};
	//Heure sup
	if ((!isset($_GET['ds_prog']))&&(substr($_GET['code_date'],8,1)==0)){$sup_ch="AND substring(code_date,9,1)=0 AND substring(t_code_date,3,1)='-'";};
	//Heure normale
	
	if ($_GET['classe_ID']==0){$classe_listeactivite=$un_gic_classe_ID_default['classe_ID'];} else {$classe_listeactivite=$_GET['classe_ID'];}
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Travail = sprintf("SELECT * FROM cdt_travail WHERE code_date='%s' AND prof_ID=%u AND classe_ID=%u AND matiere_ID=%u AND groupe=%s %s ORDER BY cdt_travail.code_date", $codedate_Travail,$ProfID,$classe_listeactivite, GetSQLValueString($_GET['matiere_ID'], "int"), GetSQLValueString($_GET['groupe'], "text"),$sup_ch);
	
	$sel_Travail = mysqli_query($conn_cahier_de_texte, $query_Travail) or die(mysqli_error($conn_cahier_de_texte));
	$un_Travail = mysqli_fetch_assoc($sel_Travail);
	$nb_Travail = mysqli_num_rows($sel_Travail);
	
	$date_a_faire[1]='';$date_a_faire[2]='';$date_a_faire[3]='';
	$travail[1]='';$travail[2]='';$travail[3]='';
	$charge[1]='';$charge[2]='';$charge[3]='';
	$eval[1]='';$eval[2]='';$eval[3]='';
	$t_groupe[1]=$_GET['groupe'];$t_groupe[2]=$_GET['groupe'];$t_groupe[3]=$_GET['groupe'];
	do {  
		$indic=$un_Travail['ind_position'];
		$travail[$indic]=$un_Travail['travail'];
		$date_a_faire[$indic]=$un_Travail['t_code_date'];
		$t_groupe[$indic]=$un_Travail['t_groupe'];
		$charge[$indic]=$un_Travail['charge'];
		$eval[$indic]=$un_Travail['eval'];
	} while ($un_Travail = mysqli_fetch_assoc($sel_Travail));
	
	mysqli_free_result($sel_Travail);
	
	
	//******************************************
	
	
	?>
	</div>
	<table width="830" border="0" cellpadding="0" cellspacing="0"  >
	<tr >
	<td height="20"  class="Style6"><?php 
	
	if ($_GET['classe_ID']==0){
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_gic =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE cdt_groupe_interclasses.ID_gic=%u ",$_GET['gic_ID']);
		$sel_gic = mysqli_query($conn_cahier_de_texte, $query_gic) or die(mysqli_error($conn_cahier_de_texte));
		$un_gic = mysqli_fetch_assoc($sel_gic);
		echo '(R) '.$un_gic['nom_gic'];
		} else {echo $_GET['nom_classe'];};?></td>
                <td class="Style6">&nbsp;<?php echo $_GET['nom_matiere'] ;?></td>
                <td class="Style6" align="right">
<!--    <div onClick='test_progression(this)' id='liste_progression'  align="right" style='display:inline'>Afficher mes fiches du carnet de bord &nbsp;&nbsp;</div>-->
                <span onClick="window.open('imprimer_menu.php','_blank'); " id='lien_cahiers'  align="center" style='display:inline'>Mes cahiers&nbsp; &nbsp;&nbsp;</span>
                <span onClick="window.open('<?php 
                //le prof a t il des raccourcis vers archive
                
$query_Assoc =sprintf("SELECT * FROM cdt_archive_association WHERE prof_ID=%u AND classe_ID=%u AND gic_ID=%u AND matiere_ID= %u ",$_SESSION['ID_prof'],$_GET['classe_ID'],$_GET['gic_ID'],$_GET['matiere_ID']);               
$sel_Assoc = mysqli_query($conn_cahier_de_texte, $query_Assoc) or die(mysqli_error($conn_cahier_de_texte));
$un_Assoc = mysqli_fetch_assoc($sel_Assoc);
$nb_Assoc = mysqli_num_rows($sel_Assoc);  

        if      ($nb_Assoc>0) {// un raccourci
        echo "../lire.php?classe_ID=".$un_Assoc['classe_ID_archive']."&matiere_ID=".$un_Assoc['matiere_ID_archive']."&gic_ID=".$un_Assoc['gic_ID_archive']."&ordre=down&archivID=".$un_Assoc['NumArchive'];
        } else {
        echo "cahiers_archives_liste.php";
        };
?>','_blank'); " id='lien_cahiers'  align="center" style='display:inline'>Mes archives&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;</span>            

        <span onClick="window.open('progression.php','_blank'); " id='lien_progression'  align="right" style='display:inline'>Carnet de bord - Progressions&nbsp;&nbsp;</span>
                </td>
      </tr>
  </table>
		
		
		
		<?php
		if (!isset ($_GET['ds_prog'])){?>	
			
			<div style="display:none;border: thin solid #006600;width:829px;padding:0px;margin:0px;margin-bottom:5px; " id="d2">
			
			<div style="padding:5px;"><?php include "travail_du_jour.php";?></div>
			</div>
			<?php 
			
		;}
		
		if ($listage_simple==0) { 
			?><script type="text/javascript">
			<!--
			function verif_saisie_travail_a_faire()
			{	
			intitule="Vous n'avez pas saisi de date pour ";	
			if((document.form1.date_a_faire_1.value == "")&&(document.form1.a_faire_1.value!="<?php if ((isset($_SERVER['HTTP_USER_AGENT']))&&(preg_match('/Firefox/i',$_SERVER['HTTP_USER_AGENT']))&&(isset($_SESSION['affiche_xinha']))&&($_SESSION['affiche_xinha']==1))
			{echo '<br />';};?>"))  {
			if (document.getElementById("eval1").checked) {intitule+="l'\351valuation 1.";} else {intitule+="le travail \340 faire 1.";};
			alert(intitule);
			document.form1.date_a_faire_1.focus();
			return false;
			}
			else if(
			(document.form1.date_a_faire_2.value == "")
			&&(document.form1.a_faire_2.value!="<?php if ((isset($_SERVER['HTTP_USER_AGENT']))&&(preg_match('/Firefox/i',$_SERVER['HTTP_USER_AGENT']))&&(isset($_SESSION['affiche_xinha']))&&($_SESSION['affiche_xinha']==1))
			{echo '<br />';};?>"))  {
			if (document.getElementById("eval2").checked) {intitule+="l'\351valuation 2.";} else {intitule+="le travail \340 faire 2.";};
			alert(intitule);
			document.form1.date_a_faire_2.focus();
			return false;
			}

			else if((document.form1.date_a_faire_3.value == "")&&(document.form1.a_faire_3.value!="<?php if ((isset($_SERVER['HTTP_USER_AGENT']))&&(preg_match('/Firefox/i',$_SERVER['HTTP_USER_AGENT']))&&(isset($_SESSION['affiche_xinha']))&&($_SESSION['affiche_xinha']==1))
			{echo '<br />';};?>"))  {
			if (document.getElementById("eval3").checked) {intitule+="l'\351valuation 3.";} else {intitule+="le travail \340 faire 3.";};
			alert(intitule);
			document.form1.date_a_faire_3.focus();
			return false;
			};
			intitule1="Saisir un commentaire pour ";	
			intitule3=" en accompagnement de votre fichier joint";	
			if((document.form1.date_a_faire_1.value != "")&&(document.form1.a_faire_1.value=="")&&(document.form1.t_fichier_11.value!=""))  {
			if (document.getElementById("eval1").checked) {intitule2="l'\351valuation 1";} else {intitule2="le travail \340 faire 1"};
			alert(intitule1+intitule2+intitule3);
			document.form1.date_a_faire_1.focus();
			return false;
			}
			if((document.form1.date_a_faire_2.value != "")&&(document.form1.a_faire_2.value=="")&&(document.form1.t_fichier_21.value!=""))  {
			alert("Saisir un commentaire pour le travail \340 faire 2 en accompagnement de votre fichier joint");
			document.form1.date_a_faire_2.focus();
			return false;
			}
			if((document.form1.date_a_faire_3.value != "")&&(document.form1.a_faire_3.value=="")&&(document.form1.t_fichier_31.value!=""))  {
			alert("Saisir un commentaire pour le travail \340 faire 3 en accompagnement de votre fichier joint");
			document.form1.date_a_faire_3.focus();
			return false;
			}
			}
			//-->
			</script>
		
			<div class="Style555">
			<form class="bordure" onLoad= "formfocus()" method="post" enctype="multipart/form-data" id="form1" name="form1" action="" onSubmit="return verif_saisie_travail_a_faire()">
			<input name="misajour" type="hidden" value="<?php echo $misajour; ?>">
			<input name="classe_ID" type="hidden" value="<?php echo $_GET['classe_ID']; ?>">
			<input name="matiere_ID" type="hidden" value="<?php echo $_GET['matiere_ID']; ?>">
			<input name="groupe" type="hidden" value="<?php echo $_GET['groupe']; ?>">
			<input name="semaine" type="hidden" value="<?php echo $_GET['semaine']; ?>">
			<input name="jour_pointe" type="hidden" value="<?php echo $_GET['jour_pointe']; ?>">
			<input name="heure" type="hidden" value="<?php echo $_GET['heure']; ?>">
			<input name="duree" type="hidden" value="<?php echo (isset($_GET['duree']))?$_GET['duree']:''; ?>">
			
			<input name="heure_debut" type="hidden" value="<?php echo $_GET['heure_debut']; ?>">
			<input name="heure_fin" type="hidden" value="<?php echo $_GET['heure_fin']; ?>">
			<input name="code_date" type="hidden" value="<?php echo $_GET['code_date']; ?>">
			<input name="ID_agenda" type="hidden" value="<?php echo $un_modif['ID_agenda']; ?>">
			<div id="d0" >
			<table border="0" cellspacing="5" cellpadding="0">
			<tr >
			<td width="140" class="Style666" ><?php echo substr($_GET['jour_pointe'],0,strlen($_GET['jour_pointe'])-4);?> </td>
			<td style="padding-left:5px;" >
			<input name="theme_activ" type="text" class="Style7" style="width:680px;" value="<?php 
			if ($misajour==0){
                                //nouvelle seance - on propose de remettre le titre de la seance precedente 
                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                if      (($_GET['classe_ID']==0)&&($_GET['gic_ID']<>0)){                
                                        $query_derniertitre = sprintf("SELECT theme_activ FROM cdt_agenda WHERE gic_ID=%u AND matiere_ID=%u AND prof_ID=%u AND code_date < %s AND theme_activ<>'' ORDER BY code_date DESC LIMIT 0,1", $_GET['gic_ID'],$_GET['matiere_ID'],$ProfID,$_GET['code_date']);
                                } else {
                                        $query_derniertitre = sprintf("SELECT theme_activ FROM cdt_agenda WHERE classe_ID=%u AND matiere_ID=%u AND prof_ID=%u  AND code_date < %s AND theme_activ<>'' ORDER BY code_date DESC LIMIT 0,1", $_GET['classe_ID'],$_GET['matiere_ID'],$ProfID,$_GET['code_date']);
                                };
                                $sel_derniertitre = mysqli_query($conn_cahier_de_texte, $query_derniertitre) or die(mysqli_error($conn_cahier_de_texte));
                                $un_derniertitre = mysqli_fetch_assoc($sel_derniertitre);
				$nb_derniertitre = mysqli_num_rows($sel_derniertitre);
				// si pas d'enregistrement precedent, ne rien ecrire
				if ($nb_derniertitre==1){echo $un_derniertitre['theme_activ'];  };
			}
			else {
                                echo $un_modif['theme_activ']; 
                        }
                        
                        
                        ?>">              </td>
              </tr>
                        <tr valign="top">
                        
                        <td width="140" class="Style144">
                        <br />
                 <div style="font-size:9px;padding-bottom:10px;"><strong>CONTENU SEANCE</strong></div> 
                 <br />
                        <?php if (!isset ($_GET['ds_prog'])){?> 
                                <div >
                                <a href="#" class="voir_d2">
                                <strong>A faire pour ce jour</strong></a><br />
				<br />
				</div>
				
				<?php 
			}
			if (isset($_SESSION['semdate'])){$sem='Semaine '.$_SESSION['semdate_libelle'];} else {$sem='';};
			if (isset ($_GET['code_date'])&&(substr($_GET['code_date'],8,1)==0)){
				
				if (isset ($_GET['ds_prog'])){	echo '<p style="color:#FF0000"><b>'.$_SESSION['libelle_devoir'].'</b></p>';} else {echo '<p style="color:#FF0000"><b>HEURE SUP.</b></p>';};
				echo $sem;
				
			}?>
			<p> <?php echo $_GET['groupe']; ?><br />
			<?php $hd='';$hf='';
			if ($_GET['heure_debut']<>''){ 
				if (substr($_GET['heure_debut'],0,1)==0){$hd=substr($_GET['heure_debut'],1,strlen($_GET['heure_debut']));}else{$hd=$_GET['heure_debut'];};
			} ;
			if ($_GET['heure_fin']<>''){ 
				if (substr($_GET['heure_fin'],0,1)==0){$hf=substr($_GET['heure_fin'],1,strlen($_GET['heure_fin']));}else{$hf=$_GET['heure_fin'];};
			} ;
			echo $hd.' - '.$hf.'&nbsp;'; 
			
			
			if ((isset($_GET['duree']))&&($_GET['duree']<>'')){echo '('.$_GET['duree'].')'; };
			if(!isset($_GET['ds_prog'])) {	
				?>
				<br />
				<br />
				Type d'activit&eacute; <br />
			  </p>
				<?php	
				
				
				//***************************************************
				// menu deroulant liste des activites de l'enseignant	  
				$profactivite_TypeActivite = "0";
				if (isset($ProfID)) {
					$profactivite_TypeActivite = (get_magic_quotes_gpc()) ? $ProfID : addslashes($ProfID);
				}
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_TypeActivite = sprintf("SELECT * FROM cdt_type_activite WHERE cdt_type_activite.ID_prof=%u ORDER BY cdt_type_activite.pos_typ", $profactivite_TypeActivite);
				$sel_TypeActivite = mysqli_query($conn_cahier_de_texte, $query_TypeActivite) or die(mysqli_error($conn_cahier_de_texte));
				$un_TypeActivite = mysqli_fetch_assoc($sel_TypeActivite);
				$nb_TypeActivite = mysqli_num_rows($sel_TypeActivite);
				
				// Si aucun type d'activite n'a ete cree par l'enseignant, forcer la creation du type activite "Cours"
				if ($nb_TypeActivite==0){
					
					mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
                                        $insertSQL = sprintf("INSERT INTO cdt_type_activite (`ID_prof` , `activite` , `pos_typ` ) VALUES (%s, 'Cours', '1') ", $ProfID);
                                        $result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die('Erreur SQL !'.$insertSQL.mysqli_error($conn_cahier_de_texte));
                                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                        $query_TypeActivite = sprintf("SELECT * FROM cdt_type_activite WHERE ID_prof=%u ORDER BY pos_typ", $profactivite_TypeActivite);
                                        $sel_TypeActivite = mysqli_query($conn_cahier_de_texte, $query_TypeActivite) or die(mysqli_error($conn_cahier_de_texte));
                                        $un_TypeActivite = mysqli_fetch_assoc($sel_TypeActivite);
                                };
				
				?>
				<select name="type_activ" class="menu_deroulant" >
				<?php
				do {  
					?>
					<option value="<?php echo $un_TypeActivite['activite']?>"<?php if (!(strcmp($un_TypeActivite['activite'], $un_modif['type_activ']))) {echo "selected=\"selected\"";} ?>><?php if (strlen($un_TypeActivite['activite'])>16){echo substr($un_TypeActivite['activite'],0,16).'.';}else {echo $un_TypeActivite['activite'];};?></option>
					
					<?php
				} while ($un_TypeActivite = mysqli_fetch_assoc($sel_TypeActivite));
				$rows = mysqli_num_rows($sel_TypeActivite);
				if($rows > 0) {
					mysqli_data_seek($sel_TypeActivite, 0);
					$un_TypeActivite = mysqli_fetch_assoc($sel_TypeActivite);
				}
				?>
				</select>
				
<?php ;} else { ?><input type="hidden" name="type_activ" value="ds_prog"><?php ;};?>
<br /><br />



<p><input name="submit" type="submit"  value="> Enregistrer" ></p>
<p>&nbsp;</p>
<p>&nbsp;<a href="ecrire.php?date=<?php echo date('Ymd');?>">Annuler</a></p></td>

<td class="Style14"><?php if (isset ($_GET['ds_prog'])){echo "Ci-dessus, la r&eacute;f&eacute;rence ou le titre du devoir - Ci dessous le texte du devoir lorsqu'il aura &eacute;t&eacute; effectu&eacute;.";};?><br />
<?php 
//dans le cas d'un mobile ou d'un Ipad, on desactive xinha et on utilise tinymce

if ((isset($_SESSION['affiche_xinha']))&&($_SESSION['affiche_xinha']==1)){
	include('area_activite.php');}
else {
	include('area_activite_tiny.php');
	};

?><textarea id="activite" name="activite"  class="Style11"  cols="80" rows="20" style="width:680px;height:250px" ><?php 
if ((isset($_SERVER['HTTP_USER_AGENT']))&&(preg_match('/Firefox/i',$_SERVER['HTTP_USER_AGENT']))&&($un_modif['activite']==NULL)&&(isset($_SESSION['affiche_xinha']))&&($_SESSION['affiche_xinha']==1)){echo '<br />';}; 
echo $un_modif['activite']; ?></textarea></td>
</tr>
<tr class="Style14">
<td width="140" valign="top"></td>
<td valign="top"><?php if (!isset($_GET['ds_prog'])){ echo '&nbsp;Documents joints &agrave; la s&eacute;ance';} else { echo '&nbsp;Documents joints au devoir';};?>
<?php
// Affichage des fichiers seance de cours deja joints
if ($nb_modif <> '0'){
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	
	$sql_f="SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=".$un_modif['ID_agenda']." AND cdt_fichiers_joints.type ='Cours' ORDER BY cdt_fichiers_joints.nom_fichier";
	$query_pj = $sql_f;
	$sel_pj = mysqli_query($conn_cahier_de_texte, $query_pj) or die(mysqli_error($conn_cahier_de_texte));
	$un_pj = mysqli_fetch_assoc($sel_pj);
	$nb_pj = mysqli_num_rows($sel_pj);
	
	?>
	<?php
	if ($nb_pj<>0)
	{
		do { 
			?>
			
			<a href="fichier_supprime.php<?php echo $lien_supprime.'&ID_fichiers='.$un_pj['ID_fichiers'].'&nom_fichier='.$un_pj['nom_fichier']?>"> <img src="../images/ed_delete.gif" alt="Supprimer le fichier" title="Supprimer le fichier" border="0">&nbsp;</a> <a href="../fichiers_joints/<?php echo $un_pj['nom_fichier'];  ?> " target="_blank">
			<?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $un_pj['nom_fichier']); ?>
			<strong><?php echo $nom_f ;  ?></strong></a>&nbsp;
			
			<?php
		} while ($un_pj = mysqli_fetch_assoc($sel_pj)); 
	}
	mysqli_free_result($sel_pj);
} // du if ($nb_modif 
?>

<br />
<input type="hidden" name="MAX_FILE_SIZE" value="100000000">&nbsp;<input type="file" size="21" name="fichier1" class="Style2">
<input type="hidden" name="MAX_FILE_SIZE" value="100000000">&nbsp;<input type="file" size="21" name="fichier2" class="Style2">
<input type="hidden" name="MAX_FILE_SIZE" value="100000000">&nbsp;<input type="file" size="21" name="fichier3" class="Style2"></td>
</tr>
<tr >
  <td width="140" class="Style144"><?php if (!isset ($_GET['ds_prog'])){?>

	  <div align="left" style="font-size:9px"><a href="#" class="voir_d1"><br>
	    Afficher - Masquer<br/>zone de Travail &agrave; faire</a>      </div>
	  <?php };?></td><td valign="bottom" class="Style14"><img src="../images/user_edit.png" width="16" height="16" style="border:none">
Annotations personnelles 
<?php if (!isset ($_GET['ds_prog'])){?>&nbsp;&nbsp;
	<a href="#" class="voir_d3" id="voir_d3" ><img src="../images/user_comment.png" width="16" height="16" style="border:none"><strong>Annotations de mes cours pr&eacute;c&eacute;dents</strong></a>&nbsp;&nbsp;
	<?php
	if ( (isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui'))	{
		?>
		<a href="#" class="voir_d4" id="voir_d4"><img src="../images/user_absent2.png" width="16" height="16" style="border:none"><strong>&nbsp;&nbsp;Absents et oublis &agrave; mes cours pr&eacute;c&eacute;dents</strong></a>	
	<?php }; ?>
	
	<div id="d3" style="display:none;border: thin solid #006600;a:font-style: italic;font-weight: normal;padding:5px;margin-bottom:5px; ">
	<?php include ('annotation_du_jour.php');?>
	</div>
	
	<div id="d4" style="display:none;border: thin solid #006600;a:font-style: italic;font-weight: normal;padding:5px;margin-bottom:5px; " >	</div>
<?php }?><br />
<textarea class="Style11" style="width:680px;height:30px" cols="77"   name="rq"  ><?php echo $un_modif['rq']; ?></textarea>

<br />




</td>
</tr>
</table></div>

<!--Choix de presentation-->
<?php
if ((isset($_SESSION['type_affich']))&&($_SESSION['type_affich']==1)){ 
	echo '<div id="d1" style="overflow: auto;';
	if (isset($_GET['ds_prog'])){echo ' height: 300px;">'; } else { echo ' height: 250px;">';}
}
else {
	echo '<div id="d1">';
}
?>

<!-- BOUCLE SUR L'AFFICHAGE DES TRAVAUX A FAIRE    ******************************************************************* -->

<table border="0" cellspacing="5" >


<?php
for ($taf=1;$taf<4;$taf++)
{
	echo "
	<script language='javascript'>
	
	function showhide_nextcours$taf()
	{
	if (clic == 0)
	{
	clic = 1;
	exit;
	}
	if (clic == 1)
	{
	pos_nextcours$taf();
	MM_showHideLayers('nextcours$taf','','hide');
	$('#date_a_faire_$taf').datepicker('hide');
	clic = 0;
	exit;
	}
	}
	
	function Affich_taf$taf()
	{
	var eval$taf = document.getElementById('eval$taf');
	
	if(eval$taf.checked==true) {
	$('#travailafaire$taf').hide();
	$('#reviseval$taf').show();
	} else {
	$('#travailafaire$taf').show();
	$('#reviseval$taf').hide();
	}
	}
	
	</script>
	";
	
	?>
	<!-- SAISIE DES TRAVAUX A FAIRE -->

	<tr class="Style14">

	<td width="140" valign="top" class="Style144" >
	<?php if (!isset($_GET['ds_prog'])){ ?>
	<?php if ($taf==1) { echo '<script language="JavaScript" type="text/JavaScript" src="../jscripts/next_cours.js"></script>'; }; ?>
		
		<div style="font-size:9px;padding-bottom:10px;" id="travailafaire<?php echo $taf; ?>"<?php if (!(strcmp($eval[$taf],'O'))) {echo 'style="display:none"';} ?>
		><strong>TRAVAIL A FAIRE N&deg;<?php echo $taf; ?></strong></div> 
		
		<div id="reviseval<?php echo $taf; ?>"
		<?php if (strcmp($eval[$taf],'O')) {echo 'style="display:none"';} ?>
		><strong> A REVISER POUR CETTE EVALUATION 
		</strong></div> 
<div  >
		
		Evaluation
		<input type="checkbox" name="eval<?php echo $taf; ?>" id="eval<?php echo $taf; ?>" onClick="Affich_taf<?php echo $taf; ?>();" value=""  <?php if ($eval[$taf]=='O') {echo " checked=checked";} ?>>
		<br />
	
		<br />Pour le<br />
		<?php 
		$dadate="";
                if (isset ($_GET['code_date'])) {
                        $dadate=substr($_GET['code_date'],6,2).'-'.substr($_GET['code_date'],4,2).'-'.substr($_GET['code_date'],2,2);
                };
                echo "<script language='javascript'>
                $(function() {
                $.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
                $.datepicker.setDefaults($.datepicker.regional['fr']);
		$('#date_a_faire_$taf').datepicker({ 
		defaultDate: '$dadate',
		firstDay:1,
		onSelect: function() {
		showhide_nextcours$taf();
                }        
                });
                });
                </script>";
                ?>
                <input name="date_a_faire_<?php echo $taf; ?>" class="Style44" id="date_a_faire_<?php echo $taf; ?>" style="cursor: text; padding-left: 20px;" onClick="pos_nextcours<?php echo $taf; ?>();MM_showHideLayers('nextcours<?php echo $taf; ?>','','show');ds_sh(date_a_faire_<?php echo $taf; ?>)" value="<?php echo $date_a_faire[$taf]; ?>" size="13" readonly="readonly" />
                <br>
                <br>
		<input name="affiche_prochain_cours_<?php echo $taf; ?>" type="button" class="Style44" id="affiche_prochain_cours_<?php echo $taf; ?>" onClick="$('#date_a_faire_<?php echo $taf; ?>').datepicker('show');pos_nextcours<?php echo $taf; ?>();MM_showHideLayers('nextcours<?php echo $taf; ?>','','show');ds_sh(date_a_faire_<?php echo $taf; ?>)" value="Prochains Cours">
		<br>
		<br/>
		
		<a href="#" onClick="go_devoirs(document.getElementsByName('date_a_faire_<?php echo $taf; ?>')[0].value,<?php echo $_GET['classe_ID']; ?>);"> Travail d&eacute;j&agrave; donn&eacute; </a>
		<br />
		<br />
		et en <br />
		<select name="t_groupe_<?php echo $taf; ?>" size="1" class="Style44" id="select">
		<?php
		do {  
                        ?>
                        <option value="<?php echo $un_groupe['groupe']?>"<?php if (!(strcmp($un_groupe['groupe'], $t_groupe[$taf]))) {echo " SELECTED";} ?>>
                        <?php 
                        if (strlen($un_groupe['groupe'])>16){echo substr($un_groupe['groupe'],0,16).'.';} 
                        else{echo $un_groupe['groupe'];};?>
                        </option>
                        <?php
		} while ($un_groupe = mysqli_fetch_assoc($sel_groupe));
		$rows = mysqli_num_rows($sel_groupe);
		if($rows > 0) {
			mysqli_data_seek($sel_groupe, 0);
			$un_groupe = mysqli_fetch_assoc($sel_groupe);
                }
                ?>
                </select>
                </div> 
        <?php 
        
        } else if ((isset($_GET['ds_prog']))&&($taf==1)){ //devoir
        $var_d = substr($_GET['code_date'],6,2).'/'.substr($_GET['code_date'],4,2).'/'.substr($_GET['code_date'],0,4);
         ?>
       <strong>A REVISER<br />POUR CE DEVOIR</strong>
                     <div>
                <input type="hidden" name="date_a_faire_1" value="<?php echo $var_d;?>">
                <input name="date_a_faire_2" type="hidden" value="">
                <input name="date_a_faire_3" type="hidden" value="">
                <input type="hidden" name="t_groupe_1" value="<?php echo $_GET['groupe'];?>">
                </div>
         <?php 
        };
        
        if (($taf==1)||(($taf>1)&& (!isset($_GET['ds_prog'])))){  // c'est un devoir hors heures de cours
                
                ?>
                          
        
        <br />
       
        Temps estim&eacute;<br />
        <select name="charge_<?php echo $taf; ?>" class="Style44">
        <option value=""<?php if (!(strcmp('', $charge[$taf]))) {echo " SELECTED";} ?>></option>
        <option value="&nbsp;5 min"<?php if (!(strcmp('&nbsp;5 min', $charge[$taf]))) {echo " SELECTED";} ?>>&nbsp;5 min</option>
        <option value="10 min"<?php if (!(strcmp('10 min', $charge[$taf]))) {echo " SELECTED";} ?>>10 min</option>
        <option value="15 min"<?php if (!(strcmp('15 min', $charge[$taf]))) {echo " SELECTED";} ?>>15 min</option>
        <option value="20 min"<?php if (!(strcmp('20 min', $charge[$taf]))) {echo " SELECTED";} ?>>20 min</option>
        <option value="25 min"<?php if (!(strcmp('25 min', $charge[$taf]))) {echo " SELECTED";} ?>>25 min</option>
        <option value="30 min"<?php if (!(strcmp('30 min', $charge[$taf]))) {echo " SELECTED";} ?>>30 min</option>
        <option value="45 min"<?php if (!(strcmp('45 min', $charge[$taf]))) {echo " SELECTED";} ?>>45 min</option>
        <option value="1 h"<?php if (!(strcmp('1 h', $charge[$taf]))) {echo " SELECTED";} ?>>1 h</option>
        <option value="1 h 15 min"<?php if (!(strcmp('1 h 15 min', $charge[$taf]))) {echo " SELECTED";} ?>>1 h 15 min</option>
        <option value="1 h 30 min"<?php if (!(strcmp('1 h 30 min', $charge[$taf]))) {echo " SELECTED";} ?>>1 h 30 min</option>
        <option value="1 h 45 min"<?php if (!(strcmp('1 h 45 min', $charge[$taf]))) {echo " SELECTED";} ?>>1 h 45 min</option>
        <option value="2 h"<?php if (!(strcmp('2 h', $charge[$taf]))) {echo " SELECTED";} ?>>2 h 00 min</option>
        <option value="2 h 15 min"<?php if (!(strcmp('2 h 15 min', $charge[$taf]))) {echo " SELECTED";} ?>>2 h 15 min</option>
        <option value="2 h 30 min"<?php if (!(strcmp('2 h 30 min', $charge[$taf]))) {echo " SELECTED";} ?>>2 h 30 min</option>
        <option value="3 h"<?php if (!(strcmp('3 h', $charge[$taf]))) {echo " SELECTED";} ?>>3 h 00 min</option>
        </select>
        
                
                </td>
        <td align="left"  valign="top" class="style14" ><p><textarea id="a_faire_<?php echo $taf; ?>" name="a_faire_<?php echo $taf; ?>" class="Style11" cols="77" rows="15"  style="width:665px;height:200px"><?php if ((isset($_SERVER['HTTP_USER_AGENT']))&&(preg_match('/Firefox/i',$_SERVER['HTTP_USER_AGENT']))&&($travail[$taf]==NULL)&&(isset($_SESSION['affiche_xinha']))&&($_SESSION['affiche_xinha']==1)){echo '<br />';}; 
echo $travail[$taf];?></textarea>
        <br />
        Document(s) de travail&nbsp;
        <input type="hidden" name="MAX_FILE_SIZE2" value="100000000">
        <input name="t_fichier_<?php echo $taf; ?>1" type="file" class="Style44" id="t_fichier_<?php echo $taf; ?>1" size="22">
        <?php
	// Affichage des fichiers travail deja joints
	if ($nb_modif <> '0'){
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		
		$sql_f="SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=".$un_modif['ID_agenda']." AND type ='Travail' AND t_code_date='".$date_a_faire[$taf]."' AND ind_position=$taf ORDER BY nom_fichier";
		$query_pj = $sql_f;
		$sel_pj = mysqli_query($conn_cahier_de_texte, $query_pj) or die(mysqli_error($conn_cahier_de_texte));
		$un_pj = mysqli_fetch_assoc($sel_pj);
		$nb_pj = mysqli_num_rows($sel_pj);
		
		if ($nb_pj<>0)
		{
			do { ?>
				<a href="fichier_supprime.php<?php echo $lien_supprime.'&ID_fichiers='.$un_pj['ID_fichiers'].'&nom_fichier='.$un_pj['nom_fichier']?>"> <img src="../images/ed_delete.gif" alt="Supprimer le fichier" title="Supprimer le fichier" border="0">&nbsp;</a> <a href="../fichiers_joints/<?php echo $un_pj['nom_fichier'];  ?> " target="_blank">
				<?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $un_pj['nom_fichier']); ?>
				<strong><?php echo $nom_f ;  ?></strong></a>&nbsp;
				<?php
			} while ($un_pj = mysqli_fetch_assoc($sel_pj)); 
		}
		mysqli_free_result($sel_pj);
	} // du if ($nb_modif 
        
        //fin affichage des fichiers travail joints
        
        
         };
        ?>
        </p>
       
        </td>
        </tr>
        <?php 
};
?>
<!-- FIN DE BOUCLE DE L'AFFICHAGE DU TRAVAIL A FAIRE **************************************************** -->
</table>

<?php echo'</div>'; //de d1 ?>

<input type="hidden" name="MM_insert" value="form1">
<input type="hidden" name="gic_ID" value="<?php echo $_GET['gic_ID'];?>">
</form>
</div>
<script> formfocus(); </script>
 		<?php 	}; 
 }; //du pas_de_saisie
 		
 		//******************************************************
 		//SECONDE PARTIE - AFFICHAGE DU RESTE DU CAHIER DE TEXTES
 		//******************************************************
 		?>
 		
 		<script language="JavaScript" type="text/JavaScript">
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
 		
 		
		var ordre_chrono=0;
		function Ecrire_realise(){
 			getXhr();
 			xhr.onreadystatechange = function(){
 				if(xhr.readyState == 4 && xhr.status == 200){
 					document.getElementById('affichage_realise').innerHTML = xhr.responseText;
 				}
 			}
			if ( ordre_chrono == 0 ) {ordre_chrono = 1} else {ordre_chrono=0};	     
 			sel = document.getElementById('ecart');

 			selval= sel.options[sel.selectedIndex].value;
 			xhr.open("GET","ajax_ecrire_realise.php?<?php echo $_SERVER['QUERY_STRING'];?>&ecart="+selval+"&ordre_chrono="+ordre_chrono,true);
 			xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
 			xhr.send(null);
		}
		</script>
		<br />
		<select style="float:left;display:inline;"  name="ecart" id="ecart" onChange="Ecrire_realise()" >
		<option value="15" <?php 
		if ((isset($_SESSION['ecart_realise']))&&($_SESSION['ecart_realise']==15)){echo ' selected';};?>
		>Afficher les 15 derniers jours</option>
		<option value="30"<?php if (((isset($_SESSION['ecart_realise']))&&($_SESSION['ecart_realise']==30))||(!isset($_SESSION['ecart_realise']))){echo ' selected';};?>
		>Afficher les 30 derniers jours</option>
		<option value="90"<?php if ((isset($_SESSION['ecart_realise']))&&($_SESSION['ecart_realise']==90)){echo ' selected';};?>>Afficher les 3 derniers mois</option>
		<option value="-1" <?php if ((isset($_SESSION['ecart_realise']))&&($_SESSION['ecart_realise']==-1)){echo ' selected';};?>>Afficher la totalit&eacute;</option>
		</select>
		<input  style="float:left;display:inline;" name="ordre_chrono" id="ordre_chrono" type="button" value="Inverser la chronologie"  onClick="Ecrire_realise()">
		<input style="float:left;display:inline;" name="voir_nextcours0" id="voir_nextcours0" class="voir_nextcours0" type="button" value="<?php echo 'Prochains cours de '.$_GET['nom_matiere']. ' en '.$_GET['nom_classe'];?>">

		
		<div>
		<?php	 // Determination du prochain cours dans cette matiere et classe
	    require_once('prochain_cours.php');	?>
		</div>
		<br /><br />
		<div name="affichage_realise" id="affichage_realise">
		<?php    // Affichage du travail deja realise
		require_once('ajax_ecrire_realise.php');?>
		</div>
		
<?php
}
?>
</div>
</body>
</html>
<?php
mysqli_free_result($Vacances);
if (isset($sel_Semdate)){ mysqli_free_result($sel_Semdate);};
if (isset($_GET['jour_pointe'])) { mysqli_free_result($sel_modif);};
		
if (isset($sel_Travail2)) { mysqli_free_result($sel_Travail2);}
mysqli_free_result($sel_Jour);
mysqli_free_result($sel_Sem);
mysqli_free_result($sel_groupe);

?>

