<?php 
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 
require_once('../inc/functions_inc.php');
$remplissage='';

$jour_RsJour = "0";

$jour_RsJour = (get_magic_quotes_gpc()) ? $current_day_name : addslashes($current_day_name);

$jour_RsJour='"'.$jour_RsJour.'"';
$nom_RsSem = GetSQLValueString($_SESSION['ID_prof'],"int");

if ($i<10){$jj='0'.$i;} else {$jj=$i;};
if (isset($_GET['date'])){$date_sem=$current_year.substr($_GET['date'],4,2).$jj; } else { $date_sem=date('Ymd');};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_partage = sprintf("SELECT * FROM cdt_emploi_du_temps_partage WHERE profpartage_ID=%u",GetSQLValueString($_SESSION['ID_prof'],"int"));
$Rs_partage = mysqli_query($conn_cahier_de_texte, $query_Rs_partage) or die(mysqli_error($conn_cahier_de_texte));

//La gestion semaine ab definie par l'administrateur est-elle prise en compte ?
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsSem = sprintf("SELECT * FROM cdt_prof WHERE ID_prof=%u", $nom_RsSem);
$RsSem = mysqli_query($conn_cahier_de_texte, $query_RsSem) or die(mysqli_error($conn_cahier_de_texte));
$row_RsSem = mysqli_fetch_assoc($RsSem);

if ($row_RsSem['gestion_sem_ab']=='O'){
	
	//recup de la semaine
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsSemdate =  sprintf("SELECT * FROM cdt_semaine_ab WHERE s_code_date<=%s ORDER BY s_code_date DESC LIMIT 1 ",$date_sem);
	$RsSemdate = mysqli_query($conn_cahier_de_texte, $query_RsSemdate) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsSemdate= mysqli_fetch_assoc($RsSemdate);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsJour =  sprintf("(SELECT * FROM cdt_emploi_du_temps,cdt_matiere 
									WHERE cdt_emploi_du_temps.prof_ID=%u 
									AND cdt_emploi_du_temps.jour_semaine=%s 
									AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
									AND (cdt_emploi_du_temps.semaine='%s' OR cdt_emploi_du_temps.semaine='A et B'))",
									GetSQLValueString($_SESSION['ID_prof'],"int"),$jour_RsJour,$row_RsSemdate['semaine']);
		while ($row_Rs_partage = mysqli_fetch_assoc($Rs_partage)) {
		$query_RsJour .= sprintf(" UNION (SELECT * FROM cdt_emploi_du_temps,cdt_matiere 
			WHERE cdt_emploi_du_temps.ID_emploi=%u 
			AND cdt_emploi_du_temps.jour_semaine=%s 
			AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
			AND (cdt_emploi_du_temps.semaine='%s' OR cdt_emploi_du_temps.semaine='A et B'))",
			$row_Rs_partage['ID_emploi'],$jour_RsJour,$row_RsSemdate['semaine']);		
	} ;
	mysqli_free_result($Rs_partage);
	$query_RsJour .=" ORDER BY heure,semaine";
}

else { //gestion des semaines pas prises en compte
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsJour =  sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_matiere 
									WHERE cdt_emploi_du_temps.prof_ID=%u 
									AND cdt_emploi_du_temps.jour_semaine=%s 
									AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere",
									GetSQLValueString($_SESSION['ID_prof'],"int"),$jour_RsJour);
									while ($row_Rs_partage = mysqli_fetch_assoc($Rs_partage)) {
                $query_RsJour .= sprintf(" UNION (SELECT * FROM cdt_emploi_du_temps,cdt_matiere 
                        WHERE cdt_emploi_du_temps.ID_emploi=%u 
                        AND cdt_emploi_du_temps.jour_semaine=%s 
                        AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere)",
                        $row_Rs_partage['ID_emploi'],$jour_RsJour);             
        };
        mysqli_free_result($Rs_partage);
	$query_RsJour .=" ORDER BY heure,semaine";
	
}; // du $row_RsSem['gestion_sem_ab']=='O'

$RsJour = mysqli_query($conn_cahier_de_texte, $query_RsJour) or die(mysqli_error($conn_cahier_de_texte));
$row_RsJour = mysqli_fetch_assoc($RsJour);
$totalRows_RsJour = mysqli_num_rows($RsJour);

//$totalRows_RsJour est le nombre d'heures de cours dans la journee

//on definit le remplissage 

do {
	
	//******** test de la presence d'une activite et coloration de la cellule *******
	if ($totalRows_RsJour<>0){
		
		//On verifie l'existence de la plage (cloturee ?)
		$code_date_f=substr($date_sem,0,4).'-'.substr($date_sem,4,2).'-'.substr($date_sem,6,2);
		if (($code_date_f>=$row_RsJour['edt_exist_debut'])&&($code_date_f<=$row_RsJour['edt_exist_fin'])){
			
			$code_date=$date_sem.$row_RsJour['heure'];
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			
			$query_RsAgenda2 = sprintf("SELECT ID_agenda,theme_activ,activite FROM cdt_agenda WHERE code_date=%s AND prof_ID=%u AND groupe='%s'", $code_date,$row_RsJour['prof_ID'],$row_RsJour['groupe']);
			
			$RsAgenda2 = mysqli_query($conn_cahier_de_texte, $query_RsAgenda2) or die(mysqli_error($conn_cahier_de_texte));
			$row_RsAgenda2 = mysqli_fetch_assoc($RsAgenda2);
			$totalRows_RsAgenda2 = mysqli_num_rows($RsAgenda2);
			if ($totalRows_RsAgenda2 != 0) {mysqli_data_seek($RsAgenda2,0);}
			
			if ($totalRows_RsAgenda2>0){
				
				$remplissage .='<div class="raised" onclick="MM_goToURL(\'window\',\'ecrire.php?date='.$date_sem.'\');return document.MM_returnValue">';
				$remplissage .='<b class="top"><b class="bb1"></b><b class="bb2"></b><b class="bb3"></b><b class="bb4"></b></b><div class="boxcontent2">';
			} 
			
			else {
				
				$remplissage .='<div class="raised" onclick="MM_goToURL(\'window\',\'ecrire.php?date='.$date_sem.'\');return document.MM_returnValue">';
				
				if ($row_RsJour['couleur_cellule']==''){   
					$remplissage .='<b class="top"><b class="b1"></b><b class="b2"></b><b class="b3"></b><b class="b4"></b></b><div class="boxcontent">';
				}  else 
				{//background personnel
					$remplissage .='
					<b style="display:block; background:transparent; font-size:1px;"><b style="height:1px;margin:0 5px; background:#fff;"></b><b style="background:'.$row_RsJour['couleur_cellule'].';display:block; overflow:hidden; border-left:1px solid #fff; border-right:1px solid #eee;height:1px;margin:0 3px; border-width:0 2px;"></b><b style="background:'.$row_RsJour['couleur_cellule'].';display:block; overflow:hidden; border-left:1px solid #fff; border-right:1px solid #ddd;height:1px;margin:0 2px;"></b> <b style="background:'.$row_RsJour['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #aaa; display:block;overflow:hidden;height:1px;height:2px; margin:0 1px;"></b></b><div style="display:block;  background:'.$row_RsJour['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #999;">';				
				};
			};
			
			$remplissage .='&nbsp;'.'<b>'.$row_RsJour['heure_debut'].'</b>'.' <span style="color:'.$row_RsJour['couleur_police'].'">';
			
			//------------------------------------------------------------------
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			
			if ($row_RsJour['classe_ID']==0){
				//regroupement / retrouver le nom
				$query_Rsgic = sprintf("SELECT nom_gic,commentaire_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u",$row_RsJour['gic_ID']);
				$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
				$row_Rsgic = mysqli_fetch_assoc($Rsgic);
				$remplissage .= '(R)&nbsp;'.$row_Rsgic['nom_gic'];
			} else { 
				
				$query_RsCl = sprintf("SELECT nom_classe FROM cdt_classe WHERE ID_classe=%u",$row_RsJour['classe_ID']);
				$RsCl = mysqli_query($conn_cahier_de_texte, $query_RsCl) or die(mysqli_error($conn_cahier_de_texte));
				$row_RsCl = mysqli_fetch_assoc($RsCl);
				$remplissage .=$row_RsCl['nom_classe'];
			};
			
			
			//------------------------------------------------------------------
			
			$remplissage .='</span> '.$row_RsJour['groupe'].' <br /> &nbsp;'.$row_RsJour['nom_matiere'].'<br />';
			if ($totalRows_RsAgenda2>0){
				$remplissage .='<div align="left">';
				$remplissage .='<img src="../images/accept.png" width="16" height="16" />';
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsEDTPartage = sprintf("SELECT profpartage_ID FROM cdt_emploi_du_temps_partage WHERE ID_emploi=%u",$row_RsJour['ID_emploi']);
				$RsEDTPartage = mysqli_query($conn_cahier_de_texte, $query_RsEDTPartage) or die(mysqli_error($conn_cahier_de_texte));
				$totalRows_RsEDTPartage = mysqli_num_rows($RsEDTPartage);
				mysqli_free_result($RsEDTPartage);
				$remplissage .=$totalRows_RsEDTPartage>0?'<img src="../images/partage.gif" width="16" height="16" />':'';
				$remplissage .='<a href="#" class="tooltip">&nbsp;';
				$remplissage .=$row_RsAgenda2['theme_activ'].'<em><span></span>'.strip_tags($row_RsAgenda2['activite'],'<p>').'</em></a></div>';
			} else {
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsEDTPartage = sprintf("SELECT profpartage_ID FROM cdt_emploi_du_temps_partage WHERE ID_emploi=%u",$row_RsJour['ID_emploi']);
				$RsEDTPartage = mysqli_query($conn_cahier_de_texte, $query_RsEDTPartage) or die(mysqli_error($conn_cahier_de_texte));
				$totalRows_RsEDTPartage = mysqli_num_rows($RsEDTPartage);
				mysqli_free_result($RsEDTPartage);
				$remplissage .=$totalRows_RsEDTPartage>0?'<img src="../images/partage.gif" width="16" height="16" />':'';
			};
			if ($totalRows_RsAgenda2>0){
				$remplissage .='</div><b class="bottom"><b class="bb4b"></b><b class="bb3b"></b><b class="bb2b"></b><b class="bb1b"></b></b></div>';
			} else {
				if ($row_RsJour['couleur_cellule']==''){ 
					$remplissage .='</div><b class="bottom"><b class="b4b"></b><b class="b3b"></b><b class="b2b"></b><b class="b1b"></b></b></div>';
				} else
				{ //background personnel
					$remplissage .='</div><b style="display:block; background:transparent; font-size:1px;"><b style="background:'.$row_RsJour['couleur_cellule'].'; border-left:1px solid #eee; border-right:1px solid #999;margin:0 5px;height:2px; margin:0 1px;display:block; overflow:hidden;"></b><b style="background:'.$row_RsJour['couleur_cellule'].'; border-left:1px solid #aaa; border-right:1px solid #999;height:1px;margin:0 2px;display:block; overflow:hidden;"></b><b style="background:'.$row_RsJour['couleur_cellule'].'; border-left:1px solid #ddd; border-right:1px solid #999;height:1px;margin:0 3px; border-width:0 2px;display:block; overflow:hidden;"></b><b 
					style="background:'.$row_RsJour['couleur_cellule'].'; border-left:1px solid #eee; border-right:1px solid #999;height:1px;margin:0 5px; background:#999;display:block; overflow:hidden;"></b></b></div>';
					
				}
			};
			mysqli_free_result($RsAgenda2);
		}; 
	}; // du $totalRows_RsJour<>0
	
	
} while ($row_RsJour = mysqli_fetch_assoc($RsJour)); 


//affichage heures supplementaires
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsHS = sprintf("SELECT * FROM cdt_agenda,cdt_classe,cdt_matiere WHERE substring(code_date,9,1)=0 AND substring(code_date,1,8)=%s AND prof_ID=%u AND cdt_classe.ID_classe=cdt_agenda.classe_ID AND cdt_matiere.ID_matiere=cdt_agenda.matiere_ID AND cdt_agenda.type_activ
	<>'ds_prog' ORDER BY cdt_agenda.heure",$date_sem,GetSQLValueString($_SESSION['ID_prof'],"int"));
$RsHS = mysqli_query($conn_cahier_de_texte, $query_RsHS) or die(mysqli_error($conn_cahier_de_texte));
$row_RsHS = mysqli_fetch_assoc($RsHS);
$totalRows_RsHS = mysqli_num_rows($RsHS);
if ($totalRows_RsHS>0){
	
	do { 
		
		$remplissage .='<div class="raised" onclick="MM_goToURL(\'window\',\'ecrire.php?date='.$date_sem.'\');return document.MM_returnValue">
		<b class="top"><b class="bb1"></b><b class="bb2"></b><b class="bb3"></b><b class="bb4"></b></b>';
		$remplissage .='<div class="boxcontent2"';
		$remplissage .='<b>';
		if ($row_RsHS['heure_debut'] <>''){ $remplissage .= '&nbsp;'.$row_RsHS['heure_debut'];} else {$remplissage .= '&nbsp;'.$row_RsHS['heure'];};
		$remplissage .='</b>'.' '.$row_RsHS['nom_classe'].' '.$row_RsHS['groupe'].' <br /> &nbsp;'.$row_RsHS['nom_matiere'].'<br />'.'&nbsp;';
		if ($_SESSION['droits']<>8){
		$remplissage .='<span style="color: #FF0000;font-weight: bold;" >Heure suppl&eacute;mentaire</span><br />';
		};
		$remplissage .='<img src="../images/accept.png" width="16" height="16" />';
		$remplissage .='&nbsp;'.$row_RsHS['theme_activ'].'<br />';
		$remplissage .='</div><b class="bottom"><b class="bb4b"></b><b class="bb3b"></b><b class="bb2b"></b><b class="bb1b"></b></b></div>';
		
		
	} while ($row_RsHS = mysqli_fetch_assoc($RsHS));
	mysqli_free_result($RsHS);
};

//affichage devoirs planifies sur classe normale
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsHS = sprintf("SELECT * FROM cdt_agenda,cdt_classe,cdt_matiere WHERE substring(code_date,9,1)=0 AND substring(code_date,1,8)=%s AND prof_ID=%u AND cdt_classe.ID_classe=cdt_agenda.classe_ID AND cdt_agenda.gic_ID =0 AND cdt_matiere.ID_matiere=cdt_agenda.matiere_ID AND cdt_agenda.type_activ='ds_prog' ORDER BY cdt_agenda.heure",$date_sem,GetSQLValueString($_SESSION['ID_prof'],"int"));

$RsHS = mysqli_query($conn_cahier_de_texte, $query_RsHS) or die(mysqli_error($conn_cahier_de_texte));
$row_RsHS = mysqli_fetch_assoc($RsHS);
$totalRows_RsHS = mysqli_num_rows($RsHS);
if ($totalRows_RsHS>0){
	
	do { 
		
		$remplissage .='<div class="raised" onclick="MM_goToURL(\'window\',\'ecrire.php?date='.$date_sem.'\');return document.MM_returnValue">
		<b class="top"><b class="bb1"></b><b class="bb2"></b><b class="bb3"></b><b class="bb4"></b></b>';
		$remplissage .='<div class="boxcontent2"';
		$remplissage .='<b>';
		if ($row_RsHS['heure_debut'] <>''){ $remplissage .= '&nbsp;'.$row_RsHS['heure_debut'];} else {$remplissage .= '&nbsp;'.$row_RsHS['heure'];};
		$remplissage .='</b>'.' '.$row_RsHS['nom_classe'].' '.$row_RsHS['groupe'].' <br /> '.'&nbsp;'.$row_RsHS['nom_matiere'].'<br />'.'&nbsp;'.'<span style="color: #FF0000;
		font-weight: bold;" >'.$_SESSION['libelle_devoir'].'</span><br />';
		$remplissage .='<img src="../images/accept.png" width="16" height="16" />';
		$remplissage .='&nbsp;'.$row_RsHS['theme_activ'].'<br />';
		$remplissage .='</div><b class="bottom"><b class="bb4b"></b><b class="bb3b"></b><b class="bb2b"></b><b class="bb1b"></b></b></div>';
		
		
	} while ($row_RsHS = mysqli_fetch_assoc($RsHS));
};
mysqli_free_result($RsHS);



//affichage devoirs planifies sur regroupements
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsHS = sprintf("SELECT  * FROM cdt_agenda,cdt_classe,cdt_matiere WHERE substring(code_date,9,1)=0 AND substring(code_date,1,8)=%s AND prof_ID=%u AND cdt_agenda.gic_ID<>0 AND cdt_matiere.ID_matiere=cdt_agenda.matiere_ID AND cdt_agenda.type_activ='ds_prog' GROUP BY heure",$date_sem,GetSQLValueString($_SESSION['ID_prof'],"int"));

$RsHS = mysqli_query($conn_cahier_de_texte, $query_RsHS) or die(mysqli_error($conn_cahier_de_texte));
$row_RsHS = mysqli_fetch_assoc($RsHS);
$totalRows_RsHS = mysqli_num_rows($RsHS);
if ($totalRows_RsHS>0){
	
	do { 
		
		$remplissage .='<div class="raised" onclick="MM_goToURL(\'window\',\'ecrire.php?date='.$date_sem.'\');return document.MM_returnValue">
		<b class="top"><b class="bb1"></b><b class="bb2"></b><b class="bb3"></b><b class="bb4"></b></b>';
		$remplissage .='<div class="boxcontent2"';
		$remplissage .='<b>';
		if ($row_RsHS['heure_debut'] <>''){ $remplissage .= '&nbsp;'.$row_RsHS['heure_debut'];} else {
			$remplissage .='<img src="../images/accept.png" width="16" height="16" />';
		$remplissage .= '&nbsp;'.$row_RsHS['heure'];};
		$remplissage .='</b>';
		
		//regroupement / retrouver le nom/
		//------------------------------------------------------------------
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		
		$query_Rsgic = sprintf("SELECT nom_gic,commentaire_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u",$row_RsHS['gic_ID']);
		$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rsgic = mysqli_fetch_assoc($Rsgic);
		$remplissage .= '(R)&nbsp;'.$row_Rsgic['nom_gic'];
		
		
		
		$remplissage .=' '.$row_RsHS['groupe'].' <br /> '.'&nbsp;(R)&nbsp;'.$row_RsHS['nom_matiere'].'<br />'.'&nbsp;'.'<span style="color: #FF0000;
		font-weight: bold;" >'.$_SESSION['libelle_devoir'].'</span><br />';
		$remplissage .='<img src="../images/accept.png" width="16" height="16" />';
		$remplissage .='&nbsp;'.$row_RsHS['theme_activ'].'<br />';
		$remplissage .='</div><b class="bottom"><b class="bb4b"></b><b class="bb3b"></b><b class="bb2b"></b><b class="bb1b"></b></b></div>';
		
		
	} while ($row_RsHS = mysqli_fetch_assoc($RsHS));
}
mysqli_free_result($RsHS);

//affichage des evenements
$dch=substr($codedateanneemoisjour,0,4).'-'.substr($codedateanneemoisjour,4,2).'-'.substr($codedateanneemoisjour,6,2);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_res = sprintf("SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe,cdt_emploi_du_temps WHERE cdt_classe.ID_classe=cdt_emploi_du_temps.classe_ID AND prof_ID=%u  ORDER BY nom_classe ASC",GetSQLValueString($_SESSION['ID_prof'],"int"));
$res = mysqli_query($conn_cahier_de_texte, $query_res) or die(mysqli_error($conn_cahier_de_texte));

while($row = mysqli_fetch_assoc($res)){
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rseven =sprintf("SELECT * FROM cdt_evenement_contenu,cdt_evenement_destinataire,cdt_classe,cdt_groupe WHERE cdt_evenement_contenu.ID_even=cdt_evenement_destinataire.even_ID AND cdt_evenement_destinataire.classe_ID = %s AND '%s'>= cdt_evenement_contenu.date_debut   AND '%s'<= cdt_evenement_contenu.date_fin  AND cdt_evenement_destinataire.classe_ID = cdt_classe.ID_classe AND cdt_evenement_destinataire.groupe_ID = cdt_groupe.ID_groupe ORDER BY date_debut", $row["ID_classe"], $dch,$dch) ;
	$Rseven = mysqli_query($conn_cahier_de_texte, $query_Rseven) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rseven = mysqli_fetch_assoc($Rseven);
	$totalRows_Rseven = mysqli_num_rows($Rseven);
	
	if (($totalRows_Rseven>0)AND($codedateanneemoisjour>=date('Ymd'))){
		do { 
			$remplissage .='<div class="raised">
			<b class="top"><b class="eb1"></b><b class="eb2"></b><b class="eb3"></b><b class="eb4"></b></b>
			<div class="boxcontent3">';
			if ($row_Rseven['date_debut']==$row_Rseven['date_fin']){
				$remplissage .=    '<div align="left"><b>&nbsp;'.$row_Rseven['heure_debut'].'</b> - '.$row_Rseven['nom_classe'].' '.$row_Rseven['groupe'].'</div>';
			};
			$remplissage .='<div align="left"><a href="#" class="tooltip" >&nbsp;'.$row_Rseven['titre_even'].'<em><span></span>'.$row_Rseven['detail'].'</em></a>...</div>';
			$remplissage .='</div><b class="bottom"><b class="eb4b"></b><b class="eb3b"></b><b class="eb2b"></b><b class="eb1b"></b></b>
			</div>'; 
					
		} while ($row_Rseven = mysqli_fetch_assoc($Rseven)); 
	};
};//fin des evenements
?>
