<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
if (!((isset($_FILES['fichiers_edt']['name'])&&($_FILES['fichiers_edt']['error'] == 0)))) { header("Location:edt.php");};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
require('../inc/zip.lib.php');

//special php4
 if(!function_exists('stripos')) {
 function stripos($haystack, $needle, $offset = 0) {
 return strpos(strtolower($haystack), strtolower($needle), $offset);
 }
 };
 
 
/*
//*********** Propre a LCS
include ("/var/www/lcs/includes/headerauth.inc.php");
@mysqli_select_db($conn_cahier_de_texte, $DBAUTH) or die ("impossible");
$result=mysqli_query($conn_cahier_de_texte, "SELECT * FROM params WHERE name='uidPolicy'");
$rparams=mysqli_fetch_array($result);
$uidpolicy=$rparams["value"];
mysqli_free_result($result);
//***********
*/

function cmp1($a,$b) {
	return strcmp($a[1], $b[1]);
}

function cmp0($a,$b) {
	if ($a[0] == $b[0])
		return cmp1($a,$b);
	return ($a[0] < $b[0]) ? -1 : 1;
}

function supprime_rep($dossier){
	if(($dir=opendir($dossier))===false)
		return;
	
	while($name=readdir($dir)){
		if($name==='.' or $name==='..')
			continue;
		$full_name=$dossier.'/'.$name;
		
		if(is_dir($full_name))
			supprime_rep($full_name);
		else unlink($full_name);
	}
	
	closedir($dir);
	if ($dossier!="../fichiers_joints/edt") {
		@rmdir($dossier);
	}
} 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<HEAD>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 

$header_description="Import d'emplois du temps depuis EDT";
require_once "../templates/default/header.php";



$chemin_destination = '../fichiers_joints/edt/';   
move_uploaded_file($_FILES['fichiers_edt']['tmp_name'], $chemin_destination.$_FILES['fichiers_edt']['name']);
$infozip=pathinfo($chemin_destination.$_FILES['fichiers_edt']['name']);
if (($infozip['extension']!=="zip")&&(stripos($_FILES['fichiers_edt']['type'],"zip")!==false)) { 
	echo "<p class='erreur'>Le fichier fourni n'est pas un fichier compress&eacute; ZIP, l'import ne peut pas se faire.</p>";
	unlink($chemin_destination.$_FILES['fichiers_edt']['name']);
}
else {
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsHoraire = "SELECT * FROM cdt_plages_horaires ORDER BY h1,mn1 ASC";
	$RsHoraire = mysqli_query($conn_cahier_de_texte, $query_RsHoraire) or die(mysqli_error($conn_cahier_de_texte));
	$totalrows_RsHoraire=mysqli_num_rows($RsHoraire);
	$tab_horaire=array();
	$i=0;
	while ($row_RsHoraire = mysqli_fetch_assoc($RsHoraire)) {
		$tab_horaire[$i][0]=intval($row_RsHoraire['h1'])*60+intval($row_RsHoraire['mn1']);
		$tab_horaire[$i][1]=$row_RsHoraire['ID_plage'];
		$i++;
	};
	mysqli_free_result($RsHoraire);
	
	if ($totalrows_RsHoraire!=='0') {// Import possible
		echo "<p align=\"center\"><a href=\"index.php\">Retour au Menu Administrateur </a></p>";
		
		$deleteSQL = "TRUNCATE TABLE cdt_edt;";
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
		
		$insertSQL = sprintf("INSERT IGNORE INTO cdt_groupe (groupe, code_groupe) VALUES (%s,%s),(%s,%s)",
			GetSQLValueString("Classe entière","text"),
			GetSQLValueString("classe_entiere","text"),
			GetSQLValueString("Groupe réduit","text"),
			GetSQLValueString("groupe_reduit","text")
			);
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsMatiere = "SELECT * FROM cdt_matiere ORDER BY ID_matiere ASC";
		$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
		$tab_mat=array();
		$i=0;
		while ($row_RsMatiere = mysqli_fetch_assoc($RsMatiere)) {
			$tab_mat[$i][0]=$row_RsMatiere['nom_matiere'];
			$tab_mat[$i][1]=$row_RsMatiere['ID_matiere'];
			$i++;	
		} ;
		mysqli_free_result($RsMatiere);
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsClasse = "SELECT nom_classe,ID_classe,code_classe FROM cdt_classe ORDER BY ID_classe ASC";
		$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
		$tab_class=array();
		$i=0;
		while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)) {
			$tab_class[$i][0]=trim(str_replace(array("-","_"," "),"",$row_RsClasse['nom_classe']));
			$tab_class[$i][1]=$row_RsClasse['ID_classe'];
			$tab_class[$i][2]=trim(str_replace(array("-","_"," "),"",$row_RsClasse['code_classe']));
			$i++;	
		} ;
		mysqli_free_result($RsClasse);
		
		$TABLO_DES_EDT=array();
		$i_edt=0;
		
		$liste_edt = array();
		$liste_edt = unzip($chemin_destination.$_FILES['fichiers_edt']['name'],$chemin_destination,true);
		echo '<BR></BR>Le fichier compress&eacute; <i>'.$_FILES['fichiers_edt']['name'].'</i> contenait '.count($liste_edt).' fichier(s) : <BR></BR>  <blockquote><blockquote><blockquote><ul>';
		foreach ($liste_edt as $nom_fichier) {
			$affichagenomprof=true;
			$Fichier = $chemin_destination.$nom_fichier;
			$infofichier=pathinfo($Fichier);
			$chemin_destination = '../fichiers_joints/edt/';
			if ((is_file($Fichier))&&($infofichier['extension']=="ics")) {
				if ($TabFich = file($Fichier)) {
					
					$Event = array();
					//Remplissage du tableau Event contenant le numero de la ligne d'un nouvel evenement 
					$j=0;
					$nblignes=count($TabFich);
					for($i = 0; $i < $nblignes; $i++) {
						if (strpos($TabFich[$i],"BEGIN:VEVENT") !== false) {
							$Event[$j]=$i;
							$j++;
						};
					}
					
					//Traitement du tableau Event pour remplir la table EDT
					
					//On cherche le premier jour de la semaine travaille... eh oui, tous les profs ne commencent pas un lundi !!!
					
					
					$itab=0;
					
					$tab_edt = array();
					$nbevent=count($Event);
					$initialisation=true;
					for($i = 0; $i < $nbevent; $i++) {
						if ($i !== $nbevent-1) {
							$fin=$Event[$i+1]-1;
						} else {
							$fin=$nblignes;
						}
						$eventOK=false;
						for($j = $Event[$i]+1; $j < $fin; $j++) {
							
							if (strpos($TabFich[$j],"DTSTART") !== false) {
								// Recherche du jour de la semaine	
								if ($initialisation) { 
									//Initialisation du premier evenement du fichier ical. On ne cherchera ensuite que les evenements
									//dont les dates sont egales ou posterieures a cet evenement.
									
									$itab_init=$itab;
									$date_extraite_init=substr($TabFich[$j],8,8);
									if (is_numeric(substr($date_extraite_init,0,4))) {
										$initialisation=false;
										$eventOK=true;
										$date_jour_init=date('N',mktime(0,0,0,substr($date_extraite_init,4,2),substr($date_extraite_init,6,2),substr($date_extraite_init,0,4)));
										$timestamp_init=mktime(0,0,0,substr($date_extraite_init,4,2),substr($date_extraite_init,6,2),substr($date_extraite_init,0,4));//H,Mn,Sec,mois,jour,annee
										
										//Recherche de la semaine A ou B de ce premier evenement
										
										mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
										$query_RsAB = "SELECT * FROM cdt_semaine_ab ORDER BY s_code_date ASC";
										$RsAB = mysqli_query($conn_cahier_de_texte, $query_RsAB) or die(mysqli_error($conn_cahier_de_texte));
										$row_RsAB = mysqli_fetch_assoc($RsAB);
										$totalRows_RsAB = mysqli_num_rows($RsAB);
										
										$semAB_init="A et B";
										do {
											if ($row_RsAB['s_code_date'] <= $date_extraite_init) {
												$semAB_init = $row_RsAB['semaine'];
											} else {
												break;	
											}
										} while ($row_RsAB = mysqli_fetch_assoc($RsAB));
										
										mysqli_free_result($RsAB);
										
										
										$place_=stripos($nom_fichier,"_");
										if ($place_==false) {
											$place_=strripos($nom_fichier,".");
										}
										$nomfichierprof2=substr($nom_fichier,0,$place_);
										$nomfichierprof=str_replace("-","_",$nomfichierprof2);
										$ID_prof = strtoupper($nomfichierprof);
										$ID_prof = ucwords(str_replace(array("_","-")," ",$nomfichierprof));
										$pastrouve = true;
										/*
										//Propre a LCS
										//Recherche de l'ID du prof associee au fichier ics adequat sinon on laisse l'identite du prof
										
										mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
										$query_RsProf = "SELECT * FROM cdt_prof ORDER BY ID_prof ASC";
										$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
										$row_RsProf = mysqli_fetch_assoc($RsProf);
										
										// Les tests ne se feront que sur les noms de famille car
										// les utilisateurs d'EDT ne fournissent pas forcement les prenoms
										// des enseignants quand ils etablissent leurs emplois du temps
										
										switch($uidpolicy) {
										case 0 : //prenom suivi du nom separes par un point. 
											do {
												$place_=stripos($row_RsProf['nom_prof'],".");
												$nom_prof=substr($row_RsProf['nom_prof'],$place_+1);
												$prof_trouve=stripos($nomfichierprof,$nom_prof);
												if ($prof_trouve !== false) { //Au cas ou un prof est trouve
													if ($pastrouve == true) { //Recherche de l'unicite sur le nom
														$ID_prof=$row_RsProf['ID_prof'];
														$pastrouve = false ;
													} else {
														$ID_prof = $nomfichierprof;
														$pastrouve = true ;
														break;
													}
												}
											} while ($row_RsProf = mysqli_fetch_assoc($RsProf));
											mysqli_free_result($RsProf);
											break;
											
										case 1 : //prenom suivi du nom separe par un point le tout tronque a 19 caracteres 
											do {
												$place_=stripos($row_RsProf['nom_prof'],".");
												$nom_prof=substr($row_RsProf['nom_prof'],$place_+1);
												$prof_trouve=stripos($nomfichierprof,$nom_prof);
												if ($prof_trouve !== false) {
													if ($pastrouve == true) { //Recherche de l'unicite sur le nom
														$ID_prof=$row_RsProf['ID_prof'];
														$pastrouve = false ;
													} else {
														$ID_prof = $nomfichierprof;
														$pastrouve = true ;
														break;
													}
												}
											} while ($row_RsProf = mysqli_fetch_assoc($RsProf));
											mysqli_free_result($RsProf);
											break;
											
										case 2 : //premiere lettre du prenom suivie du nom le tout tronque a 19 caracteres. 
											do {
												$nom_prof=substr($row_RsProf['nom_prof'],1);
												$prof_trouve=stripos($nomfichierprof,$nom_prof);
												if ($prof_trouve !== false) {
													if ($pastrouve == true) { //Recherche de l'unicite sur le nom
														$ID_prof=$row_RsProf['ID_prof'];
														$pastrouve = false ;
													} else {
														$ID_prof = $nomfichierprof;
														$pastrouve = true ;
														break;
													}
												}
											} while ($row_RsProf = mysqli_fetch_assoc($RsProf));
											mysqli_free_result($RsProf);
											break;
											
										case 3 : //premiere lettre du prenom suivie du nom le tout tronque a 8 caracteres. 
											do {
												$nom_prof=substr($row_RsProf['nom_prof'],1);
												$prof_trouve=stripos($nomfichierprof,$nom_prof);
												if ($prof_trouve !== false) {
													if ($pastrouve == true) { //Recherche de l'unicite sur le nom
														$ID_prof=$row_RsProf['ID_prof'];
														$pastrouve = false ;
													} else {
														$ID_prof = $nomfichierprof;
														$pastrouve = true ;
														break;
													}
												}
											} while ($row_RsProf = mysqli_fetch_assoc($RsProf));
											mysqli_free_result($RsProf);
											break;
											
										case 4 : //nom suivi de la premiere lettre du prenom le tout tronque a 8 caracteres. 
											do {										
												$longueur=strlen($row_RsProf['nom_prof']);
												$nom_prof=substr($row_RsProf['nom_prof'],0,$longueur-1);
												$prof_trouve=stripos($nomfichierprof,$nom_prof);
												if ($prof_trouve !== false) {
													if ($pastrouve == true) { //Recherche de l'unicite sur le nom
														$ID_prof=$row_RsProf['ID_prof'];
														$pastrouve = false ;
													} else {
														$ID_prof = $nomfichierprof;
														$pastrouve = true ;
														break;
													}
												}
											} while ($row_RsProf = mysqli_fetch_assoc($RsProf));
											mysqli_free_result($RsProf);
											break;
										}
										*/
									}
								} else {
									$eventOK=true;
								}
								
								//Traitement des cours dans les 14 jours suivants : jour de la semaine et semaine A ou B.
								if ($eventOK) {
									$eventOK=false;
									$date_extraite_test=substr($TabFich[$j],8,8);
									if (is_numeric(substr($date_extraite_test,0,4))) {
										$timestamp_test=mktime(0,0,0,substr($date_extraite_test,4,2),substr($date_extraite_test,6,2),substr($date_extraite_test,0,4));//H,Mn,Sec,mois,jour,annee
										$nbJours=(($timestamp_test-$timestamp_init)/(3600*24));
										if (($date_extraite_init < $date_extraite_test) & ($nbJours < 15)) {
											$date_formatee=substr($date_extraite_test,6,2).'/'.substr($date_extraite_test,4,2).'/'.substr($date_extraite_test,0,4);
											$date_jour=jour_semaine($date_formatee);
											$date_jour1=date('N',mktime(0,0,0,substr($date_extraite_test,4,2),substr($date_extraite_test,6,2),substr($date_extraite_test,0,4)));
											$tab_edt[$itab][0]=$date_jour1; //permet le tri du tableau $tab_edt
											$tab_edt[$itab][7]=$date_jour; //permet de ranger le jour de la semaine (et non son numero associee) dans la BDD
											$eventOK=true;
											
											//Traitement des semaines A ou B pour les jours suivants
											$semAB=$semAB_init;
											if (($nbJours >= (8-$date_jour_init)) & ($nbJours <= (15-$date_jour_init))){
												if ($semAB_init == "A") { $semAB="B";} elseif ($semAB_init == "B") { $semAB="A";};
											}
											
											$tab_edt[$itab][5]=$semAB;
											
											
											//Recherche de l'heure de debut	
											mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
											$query_time_zone_db = "SELECT param_val FROM cdt_params WHERE param_nom='time_zone'";
											$time_zone_db = mysqli_query($conn_cahier_de_texte, $query_time_zone_db) or die(mysqli_error($conn_cahier_de_texte));
											$row_time_zone_db = mysqli_fetch_assoc($time_zone_db);
											$seconds = date_offset_get(new DateTime("now", new DateTimeZone($row_time_zone_db['param_val'])));
											mysqli_free_result($time_zone_db);
											$decalagehoraire = $seconds / 3600;
											$HDEB=intval(substr($TabFich[$j],17,2))+$decalagehoraire;
											$heure_debut=$HDEB."h".substr($TabFich[$j],19,2);
											$tab_edt[$itab][1]=$heure_debut;
											//ID du prof
											$tab_edt[$itab][8]=$ID_prof;
											if ($affichagenomprof) {
												$affichagenomprof=false;
												if ($pastrouve) {
													echo "<li><p align=\"left\"><font color=red>".$nom_fichier."</font></p></li>";
												} else {
													echo "<li><p align=\"left\">".$nom_fichier."</p></li>";
												}
											}					
										}
									}
								}
							}
							
							if (($eventOK) & ((strpos($TabFich[$j],"DTEND") !== false))) {
								// Recherche de l'heure de fin
								mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
								$query_time_zone_db = "SELECT param_val FROM cdt_params WHERE param_nom='time_zone'";
								$time_zone_db = mysqli_query($conn_cahier_de_texte, $query_time_zone_db) or die(mysqli_error($conn_cahier_de_texte));
								$row_time_zone_db = mysqli_fetch_assoc($time_zone_db);
								$seconds = date_offset_get(new DateTime("now", new DateTimeZone($row_time_zone_db['param_val'])));
								mysqli_free_result($time_zone_db);$decalagehoraire = $seconds / 3600;
								$HFIN=intval(substr($TabFich[$j],15,2))+$decalagehoraire;			
								$heure_fin=$HFIN."h".substr($TabFich[$j],17,2);
								$tab_edt[$itab][4]=$heure_fin;
								
							}
							
							if (($eventOK) & ((strpos($TabFich[$j],"DESCRIPTION") !== false))) {
								// Recherche de la classe
								
								//        Pour Pronote d'avant 2012
								
							/* if (($eventOK) & ((strpos($TabFich[$j],"SUMMARY") !== false))) {
								
								$indice=strpos($TabFich[$j],":");
								$classe=substr($TabFich[$j],$indice+1,strlen($TabFich[$j]));
								$classe_sansespace=strtolower(trim(str_replace(array("-","_"," "),"",$classe)));*/
								
								$indice=strripos($TabFich[$j],"Classe : ");
								$classe=substr($TabFich[$j],$indice+9,strlen($TabFich[$j]));
								if (stripos($classe,"\\n")==false) {
										$longmat=strlen($TabFich[$j]);
									} else {
										$longmat=stripos($classe,"\\n")-1;
									}
									
								$classe=substr($classe,0,$longmat);
								$classe_sansespace=strtolower(trim(str_replace(array("<",">","-","_"," "),"",$classe)));
								
								$tab_edt[$itab][2]="0";
								$tab_edt[$itab][10]="#FFE4B5";
								for ($jtab = 0 ; $jtab < count($tab_class) ; $jtab++) {
									$classe_trouve1=($classe_sansespace==strtolower($tab_class[$jtab][0]));
									$classe_trouve2=($classe_sansespace==strtolower($tab_class[$jtab][2]));
									if ($classe_trouve1) {
										$tab_edt[$itab][2] = $tab_class[$jtab][1];
										$tab_edt[$itab][10]="#ADD8E6";
										break;
									} elseif ($classe_trouve2) {
										$tab_edt[$itab][2] = $tab_class[$jtab][1];
										$tab_edt[$itab][10]="#ADD8E6";
										break;
									}
								}							
							}
							
							
							if (($eventOK) & ((strpos($TabFich[$j],"DESCRIPTION") !== false))) {
								// Recherche de la matiere		
								
								$indice=stripos($TabFich[$j],":");
								$matiere1=substr($TabFich[$j],$indice+1,strlen($TabFich[$j]));
								$indice=stripos($matiere1,":");
								if ($indice) {
									if (stripos($matiere1,"\\n")==false) {
										$longmat=strlen($TabFich[$j]);
									} else {
										$longmat=stripos($matiere1,"\\n")-$indice-1;
									}
									$matiere=substr($matiere1,$indice+1,$longmat);
									$tab_edt[$itab][9]=$matiere;
									$matiereOK = false;
									//Recherche de la matiere dans les matieres du prof deja trouvees
									for ($jtab = $itab_init ; $jtab < $itab ; $jtab++) {
										$matiere_trouve=stripos($matiere,$tab_edt[$jtab][9]);
										if ($matiere_trouve !== false) {
											$tab_edt[$itab][3] = $tab_edt[$jtab][3];
											$matiereOK = true ;
											break;
										}
									}
									
									if ($matiereOK == false) {
										$nb_mat=count($tab_mat);
										for ($jtab = 0 ; $jtab < $nb_mat ; $jtab++) {
											$matiere_trouve=(stripos($matiere,$tab_mat[$jtab][0])||stripos(str_replace(array("/", "&", "\'"),"-",$matiere),$tab_mat[$jtab][0]));
											if ($matiere_trouve !== false) {
												$tab_edt[$itab][3] = $tab_mat[$jtab][1];
												$matiereOK = true ;
												break;
											}
										}	
									}
									
									if ($matiereOK == false) {
										// Ajout de cette matiere dans la base de donnees
										$matiere=str_replace("'","",trim($matiere));
										$matiere = str_replace(array("/", "&", "\'"),"-",$matiere);
										$insertSQL = sprintf("INSERT IGNORE INTO cdt_matiere (nom_matiere) VALUES (%s)",
											GetSQLValueString($matiere,"text")
											);
										mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
										$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
										
										$selecymatiere = sprintf("SELECT ID_matiere FROM cdt_matiere WHERE nom_matiere=%s LIMIT 1",
											GetSQLValueString($matiere,"text")
											);
										mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
										$Nmatiere = mysqli_query($conn_cahier_de_texte, $selecymatiere) or die(mysqli_error($conn_cahier_de_texte));
										$row_Nmatiere = mysqli_fetch_assoc($Nmatiere);
										
										$iprof=count($tab_mat);
										$tab_mat[$iprof][0]=$matiere;
										$tab_mat[$iprof][1]=$row_Nmatiere['ID_matiere'];
										$tab_edt[$itab][3]=$tab_mat[$iprof][1];
										mysqli_free_result($Nmatiere);
									}
								} else {
									$eventOK=false;
								}
							}
							
						}
						if ($eventOK) {$itab++;}
					}
					
					$nb_tab=count($tab_edt);
					
					//Rangement du tableau de l'EDT dans l'ordre des dates puis des horaires
					usort($tab_edt, "cmp0");
					
					//Suppression des cours en double (semaine A et semaine B) pour n'en garder qu'un en semaine A et B.
					$nb_tab=count($tab_edt);
					for ($i=0;$i<$nb_tab-1;$i++) {
						$test0 = strcmp($tab_edt[$i][0], $tab_edt[$i+1][0]);
						$test1 = strcmp($tab_edt[$i][1], $tab_edt[$i+1][1]);
						$test2 = strcmp($tab_edt[$i][2], $tab_edt[$i+1][2]);
						$test3 = strcmp($tab_edt[$i][3], $tab_edt[$i+1][3]);
						$test4 = strcmp($tab_edt[$i][4], $tab_edt[$i+1][4]);
						if (($test0 == 0)&($test1 == 0)&($test2 == 0)&($test3 == 0)&($test4 == 0)) {
							$tab_edt[$i][5]="A et B";
							unset($tab_edt[$i+1]);
							$i++;
						}
					}
					$tab_edt = array_values($tab_edt);
					
					//Affectation de l'ordre des cours dans la septieme colonne du tableau
					$nb_tab=count($tab_edt);
					$j=1;
					for ($i=0;$i<$nb_tab;$i++) {
						$hdebut=explode("h",$tab_edt[$i][1]);
						$conversionhdebut=intval($hdebut[0])*60+intval($hdebut[1]);
						$tab_edt[$i][6]=$tab_horaire[count($tab_horaire)-1][1];
						$nbhoraire=count($tab_horaire);
						for ($j=0;$j<$nbhoraire;$j++) {
							if ($j==0) {
								$diffhoraire=abs($conversionhdebut-$tab_horaire[$j][0]);
							} else {
								if ($diffhoraire>=abs($conversionhdebut-$tab_horaire[$j][0])) {
									$diffhoraire=abs($conversionhdebut-$tab_horaire[$j][0]);
								} else {
									$tab_edt[$i][6]=$tab_horaire[$j-1][1];
									break;
								}
							}
						}	
					}
					
					
					foreach($tab_edt as $elem) {
						//0 : ID jour semaine
						//1 : Horaire de debut
						//2 : ID classe
						//3 : ID matiere
						//4 : Horaire de fin
						//5 : Semaine A et/ou B
						//6 : ID placement cours dans journee
						//7 : Jour semaine
						//8 : ID prof
						//9 : Intitule Matiere
						//10 : Couleur_cellule
						
						for ($j_edt=0;$j_edt<11;$j_edt++) {
							$TABLO_DES_EDT[$i_edt][$j_edt]=$elem[$j_edt];
						}
						$i_edt++;
					}
				}
				
			}
		}
		
		
		foreach($TABLO_DES_EDT as $elem) {
			$insertSQL = sprintf("INSERT INTO cdt_edt (prof_ref,jour_semaine,semaine,heure,classe_ID,matiere_ID,heure_debut,heure_fin,couleur_cellule) 
				VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
				GetSQLValueString($elem[8],"text"),
				GetSQLValueString($elem[7],"text"),
				GetSQLValueString($elem[5],"text"),
				GetSQLValueString($elem[6],"text"),
				GetSQLValueString($elem[2],"text"),
				GetSQLValueString($elem[3],"text"),
				GetSQLValueString($elem[1],"text"),
				GetSQLValueString($elem[4],"text"),
				GetSQLValueString($elem[10],"text")
				);
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		}
		
		$updateSQL = sprintf("UPDATE cdt_params SET param_val='EDT' WHERE param_nom='Import'");
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
		
		echo '</ul></blockquote></blockquote></blockquote>';
		?>
		<blockquote>
		<blockquote>
		<BR></BR>
		<p><b>IMPORTATION AVEC SUCC&Egrave;S</b></p>
		<p>Les emplois du temps de chacun des professeurs dont les noms figurent ci-dessus dans l'intitul&eacute; du fichier ont &eacute;t&eacute; ins&eacute;r&eacute;s dans la base de donn&eacute;es. Si un enseignant n'acc&egrave;de pas &agrave; son emploi du temps, vous pourrez v&eacute;rifier que son fichier est bien list&eacute; ci-dessus. Dans le cas contraire, il n'&eacute;tait pas dans votre fichier compress&eacute;. Mettez-le et recommencer l'op&eacute;ration d'import des emplois du temps.</p>
		<!-- Propre a LCS
		<BR></BR>
		<p>Les enseignants dont <b>le nom du fichier est bleu </b>trouveront la possibilit&eacute; d'ins&eacute;rer directement leur emploi du temps dans le cahier de textes depuis leur menu enseignant.</p>
		<p>Les enseignants dont <font color=red>le nom du fichier est rouge </font>ne trouveront pas ce choix directement mais devront choisir parmi plusieurs emplois du temps. La raison est parmi les suivantes :
		<ul><li>Le nom du fichier ne co&iuml;ncide pas assez avec son login.</li>
		<li>L'enseignant en question n'apparait pas dans la liste des professeurs du Cahier de Textes.</li>
		<li>L'enseignant en question ne s'est encore jamais connect&eacute; au Cahier de Textes.</li>
		<li>Il y a un souci d'homonymie sur les noms de famille.</li></ul>
		-->
		Pour r&eacute;soudre ces probl&egrave;mes, il suffit de modifier le nom des fichiers en question et de recommencer cette &eacute;tape.</p>
		<BR></BR><BR></BR>
		<p>En tout &eacute;tat de cause, vous pouvez refaire, sans risque, cette &eacute;tape autant de fois que vous le souhaitez, que les emplois du temps aient &eacute;t&eacute; d&eacute;j&agrave; choisis ou non par certains enseignants... &agrave; tout moment de l'ann&eacute;e.</p>
		</blockquote>
		</blockquote>
		<?php
		$repertoire="../fichiers_joints/edt";
		supprime_rep($repertoire);
		if(sizeof(scandir($repertoire))>2)
		{
			?>
			<p class="erreur"> Le dossier <strong>fichiers_joints/edt</strong> contenant les fichiers d'extension &quot;<strong>ics</strong>&quot;n'a pas &eacute;t&eacute; vid&eacute; enti&egrave;rement de ses fichiers. </p>
			<?php 
		}
		else
		{
			?>
			<p>Leur traitement &eacute;tant effectu&eacute;, les fichiers d'extension &quot;<strong>ics</strong>&quot; du dossier <strong>fichiers_joints/edt</strong> </p>
			<p>issus de cet import ont &eacute;t&eacute; supprim&eacute;s du serveur.</p>
			<?php } 
	} else { //Import impossible sans remplissage des plages horaires
		?>
		<blockquote>
		<blockquote>
		<BR></BR>
		<p><b><font color=red>IMPORTATION IMPOSSIBLE</font></b></p>
		<p>Les plages horaires de votre cahier de textes ne sont pas d&eacute;finies. Veuillez le faire avec minutie avant de retenter une nouvelle fois l'import.</p>
		<BR></BR>
		<p align="center"><a href="plages_horaires.php">Gestion des plages horaires</a></p>
		<BR></BR><BR></BR>
		</blockquote>
		</blockquote>
		<?php 
	}
}
?>
<p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</body>
</html>
