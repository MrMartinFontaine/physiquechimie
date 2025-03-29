<?php


$tab_jours = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');
$current_day_name = $tab_jours[date('w', mktime(0,0,0,date('m'),date('d'),date('Y')))];
//echo $current_day_name; jour de la semaine



$heure_courante = date("H").'h'.date("i");
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
$sel_Sem = mysqli_query($conn_cahier_de_texte,$query_Sem) or die(mysqli_error($conn_cahier_de_texte));
$un_Sem = mysqli_fetch_assoc($sel_Sem);

if (isset($_GET['date'])){$madate=substr($_GET['date'],0,4).'-'.substr($_GET['date'],4,2).'-'.substr($_GET['date'],6,2);} else {
if (isset($_GET['code_date'])){$madate=substr($_GET['code_date'],0,4).'-'.substr($_GET['code_date'],4,2).'-'.substr($_GET['code_date'],6,2);}else{$madate=date('Y-m-d');}};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_partage = sprintf("SELECT * FROM cdt_emploi_du_temps_partage WHERE profpartage_ID=%u",GetSQLValueString($_SESSION['ID_prof'],"int"));
$sel_partage = mysqli_query($conn_cahier_de_texte,$query_partage) or die(mysqli_error($conn_cahier_de_texte));
$un_partage = mysqli_fetch_assoc($sel_partage);

$un_Sem['gestion_sem_ab']='O';

if ($un_Sem['gestion_sem_ab']=='O'){
	
	//recup de la semaine
        if (!isset($_GET['code_date'])){ //chercher la semaine
                if (!isset($_GET['date'])){$date_sem=date('Ymd');} else {$date_sem=$_GET['date'];};
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_Semdate =  sprintf("SELECT * FROM cdt_semaine_ab WHERE s_code_date<=%s ORDER BY `s_code_date` DESC LIMIT 1",$date_sem);
                $sel_Semdate = mysqli_query($conn_cahier_de_texte,$query_Semdate) or die(mysqli_error($conn_cahier_de_texte));
                $un_Semdate= mysqli_fetch_assoc($sel_Semdate);
                if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){
			if ($un_Semdate['semaine']=='A et B'){$_SESSION['semdate_libelle']='P et I';} else if($un_Semdate['semaine']=='A'){$_SESSION['semdate_libelle']='Paire';} else {$_SESSION['semdate_libelle']='Impaire';};
		}
		else {$_SESSION['semdate_libelle']=$un_Semdate['semaine'];};
		$_SESSION['semdate']=$un_Semdate['semaine'];
	};
	
	
	//echo 'la semaine est '.$un_Semdate['semaine'];
	
	
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	
	$query_Jour =  sprintf("(SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
		LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
		WHERE 
		cdt_emploi_du_temps.heure_debut<='%s'
		AND cdt_emploi_du_temps.heure_fin>'%s'
		AND cdt_emploi_du_temps.prof_ID=%u 
		AND cdt_emploi_du_temps.jour_semaine=%s 
                AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
                AND (cdt_emploi_du_temps.semaine='%s' OR cdt_emploi_du_temps.semaine='A et B')
                AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
                AND cdt_emploi_du_temps.edt_exist_fin >= '%s')",$heure_courante,$heure_courante,GetSQLValueString($_SESSION['ID_prof'],"int"),$jour_Jour,$_SESSION['semdate'],$madate,$madate);
        
        $query_classeID0 =  sprintf("(SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
                LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
		WHERE 		cdt_emploi_du_temps.heure_debut<='%s'
		AND cdt_emploi_du_temps.heure_fin>'%s'
		AND cdt_emploi_du_temps.prof_ID=%u  
		AND cdt_emploi_du_temps.jour_semaine=%s 
		AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
		AND (cdt_emploi_du_temps.semaine='%s' OR cdt_emploi_du_temps.semaine='A et B')
                AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
                AND cdt_emploi_du_temps.edt_exist_fin >= '%s'
                AND cdt_emploi_du_temps.classe_ID ='0'
                AND cdt_emploi_du_temps.gic_ID ='0')",$heure_courante,$heure_courante,GetSQLValueString($_SESSION['ID_prof'],"int"),$jour_Jour,$_SESSION['semdate'],$madate,$madate);
        
        do {
                
		$query_Jour .= sprintf(" UNION (SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
			LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
			WHERE 		cdt_emploi_du_temps.heure_debut<='%s'
		AND cdt_emploi_du_temps.heure_fin>'%s'
		AND cdt_emploi_du_temps.prof_ID=%u  
			AND cdt_emploi_du_temps.jour_semaine=%s 
			AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
			AND (cdt_emploi_du_temps.semaine='%s' OR cdt_emploi_du_temps.semaine='A et B')
			AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
			AND cdt_emploi_du_temps.edt_exist_fin >= '%s')",$heure_courante,$heure_courante,$un_partage['ID_emploi'],$jour_Jour,$_SESSION['semdate'],$madate,$madate);
		
		$query_classeID0 .=  sprintf(" UNION (SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
			LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
			WHERE 		cdt_emploi_du_temps.heure_debut<='%s'
		AND cdt_emploi_du_temps.heure_fin>'%s'
		AND cdt_emploi_du_temps.prof_ID=%u   
			AND cdt_emploi_du_temps.jour_semaine=%s 
			AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
			AND (cdt_emploi_du_temps.semaine='%s' OR cdt_emploi_du_temps.semaine='A et B')
			AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
			AND cdt_emploi_du_temps.edt_exist_fin >= '%s'
			AND cdt_emploi_du_temps.classe_ID ='0'
			AND cdt_emploi_du_temps.gic_ID ='0')",$heure_courante,$heure_courante,$un_partage['ID_emploi'],$jour_Jour,$_SESSION['semdate'],$madate,$madate);
		
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

//echo '******************************'. $query_Jour;

$sel_Jour = mysqli_query($conn_cahier_de_texte,$query_Jour) or die(mysqli_error($conn_cahier_de_texte));
$row_RsJour = mysqli_fetch_assoc($sel_Jour);
$nb_Jour = mysqli_num_rows($sel_Jour);
//$nb_Jour est le nombre d'heures de cours dans la journee
?>

