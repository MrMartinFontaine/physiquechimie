<?php
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
if (!((isset($_FILES['fichier_udt']['name'])&&($_FILES['fichier_udt']['error'] == 0)))) { header("Location:edt.php");};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

 //special php4
 if(!function_exists('stripos')) {
 function stripos($haystack, $needle, $offset = 0) {
 return strpos(strtolower($haystack), strtolower($needle), $offset);
 }
 };

?>
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

$header_description="Import d'emplois du temps depuis UDT";
require_once "../templates/default/header.php";

$chemin_destination = '../fichiers_joints/edt/';   
move_uploaded_file($_FILES['fichier_udt']['tmp_name'], $chemin_destination.$_FILES['fichier_udt']['name']);
$infocsv=pathinfo($chemin_destination.$_FILES['fichier_udt']['name']);
if (isset($_FILES['fichier_sconet']['name'])&&($_FILES['fichier_sconet']['error'] == 0)) {
	move_uploaded_file($_FILES['fichier_sconet']['tmp_name'], $chemin_destination.$_FILES['fichier_sconet']['name']);
	$infoxml=pathinfo($chemin_destination.$_FILES['fichier_sconet']['name']);
	$csvsconet=true;
} else {
	$csvsconet=false;
}

$mime_types = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'text/csv', 'text/plain', 'application/csv', 'application/x-csv', 'application/plain', 'application/force-download', 'application/excel', 'application/vnd.msexcel'); 
if (!in_array($_FILES['fichier_udt']['type'],$mime_types)) {  
	echo "<p class='erreur'>Le fichier <b>".$_FILES['fichier_udt']['name']."</b> n'est pas un fichier de type CSV ou TXT, l'import ne peut pas se faire.</p>";
	unlink($chemin_destination.$_FILES['fichier_udt']['name']);
	if (isset($_FILES['fichier_sconet']['name'])&&($_FILES['fichier_sconet']['error'] == 0)) {
		unlink($chemin_destination.$_FILES['fichier_sconet']['name']);
	}
	echo '<p align="center"><a href="edt.php">Retour &agrave; l\'import des emplois du temps</a></p>';
	
}
else if ((isset($_FILES['fichier_sconet']['name'])&&($_FILES['fichier_sconet']['error'] == 0))&&(stripos($_FILES['fichier_sconet']['type'],"xml")==false))
{ 
	echo "<p class='erreur'>Le fichier <b>".$_FILES['fichier_sconet']['name']."</b> n'est pas un fichier XML, l'import ne peut pas se faire.</p>";
	unlink($chemin_destination.$_FILES['fichier_udt']['name']);
	unlink($chemin_destination.$_FILES['fichier_sconet']['name']);
} else {
	$fichier_udt=$chemin_destination.$_FILES['fichier_udt']['name'];
	$fichier_sconet=$chemin_destination.$_FILES['fichier_sconet']['name'];
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsHoraire = "SELECT * FROM cdt_plages_horaires ORDER BY h1,mn1 ASC";
	$RsHoraire = mysqli_query($conn_cahier_de_texte, $query_RsHoraire) or die(mysqli_error($conn_cahier_de_texte));
	$totalrows_RsHoraire=mysqli_num_rows($RsHoraire);
	$tab_horaire=array();
	$i=0;
	while ($row_RsHoraire = mysqli_fetch_assoc($RsHoraire)) {
		$tab_horaire[$i][0]=intval($row_RsHoraire['h1'])*60+intval($row_RsHoraire['mn1']);
		$tab_horaire[$i][1]=$row_RsHoraire['ID_plage'];
		$tab_horaire[$i][2]=strval($row_RsHoraire['h1'])."h".strval($row_RsHoraire['mn1']);
		$tab_horaire[$i][3]=strval($row_RsHoraire['h2'])."h".strval($row_RsHoraire['mn2']);
		$i++;
	};
	mysqli_free_result($RsHoraire);
	
	if ($totalrows_RsHoraire!=='0') {// Import possible
		
		$insertSQL = sprintf("INSERT IGNORE INTO cdt_groupe (groupe, code_groupe) VALUES (%s,%s),(%s,%s),(%s,%s),(%s,%s),(%s,%s),(%s,%s),(%s,%s),(%s,%s)",
			GetSQLValueString("Classe entière","text"),
			GetSQLValueString("classe_entiere","text"),
			GetSQLValueString("Groupe A","text"),
			GetSQLValueString("groupe_a","text"),
			GetSQLValueString("Groupe B","text"),
			GetSQLValueString("groupe_b","text"),
			GetSQLValueString("Groupe C","text"),
			GetSQLValueString("groupe_c","text"),
			GetSQLValueString("Groupe 1","text"),
			GetSQLValueString("groupe_1","text"),
			GetSQLValueString("Groupe 2","text"),
			GetSQLValueString("groupe_2","text"),
			GetSQLValueString("Groupe 3","text"),
			GetSQLValueString("groupe_3","text"),
			GetSQLValueString("Groupe Réduit","text"),
			GetSQLValueString("groupe_reduit","text")
			);
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		
		
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsClasse = "SELECT nom_classe,ID_classe,code_classe FROM cdt_classe ORDER BY ID_classe ASC";
		$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
		$tab_class=array();
		$i=0;
		while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)) {
			$tab_class[$i][0]=trim($row_RsClasse['nom_classe']);
			$tab_class[$i][1]=trim($row_RsClasse['ID_classe']);
			$tab_class[$i][2]=trim($row_RsClasse['code_classe']);
			$i++;
		} ;
		mysqli_free_result($RsClasse);
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsMatiere = "SELECT nom_matiere,ID_matiere FROM cdt_matiere ORDER BY ID_matiere ASC";
		$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
		$tab_matieres=array();
		$i=0;
		while ($row_RsMatiere = mysqli_fetch_assoc($RsMatiere)) {
			$tab_matieres[$i][0]=$row_RsMatiere['nom_matiere'];
			$tab_matieres[$i][1]=$row_RsMatiere['ID_matiere'];
			$i++;	
		} ;
		mysqli_free_result($RsMatiere);
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsGroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
		$RsGroupe = mysqli_query($conn_cahier_de_texte, $query_RsGroupe) or die(mysqli_error($conn_cahier_de_texte));
		$tab_Groupes=array();
		$tab_Groupes2=array();
		$igpe=0;
		while ($row_RsGroupe = mysqli_fetch_assoc($RsGroupe)) {
			$tab_Groupes[$igpe]=$row_RsGroupe['groupe'];
			$tab_Groupes2[$igpe]=$row_RsGroupe['code_groupe'];
			$igpe++;	
		} ;
		mysqli_free_result($RsGroupe);
		
		if ($csvsconet) {
			if (file_exists($fichier_sconet)) {
				$xml = simplexml_load_file($fichier_sconet);
				$mats = $xml->xpath('//MATIERE');
				$tab_mat=array();
				$i=0;
				foreach ($mats as $mat) {
					$tab_mat[$i][0]=$mat->CODE_GESTION;
					$tab_mat[$i][1]=$mat->LIBELLE_LONG;
					$tab_mat[$i][2]=$mat->LIBELLE_COURT;
					$i++;
				}
			} else {
				echo('<p class="erreur"> Echec lors de l\'ouverture du fichier '.$fichier_sconet.'.</p>');
			}
		}
		if (is_file($fichier_udt)) {
			if ($TabFich = file($fichier_udt)) {
				if (substr($fichier_udt,-3)=="csv") { $separateur=";";}
				else if (substr($fichier_udt,-3)=="txt") { $separateur="\t";}
				$Event = array();
				//Remplissage du tableau Event contenant le numero de la ligne d'un nouvel evenement 
				$j=0;
				$nblignes=count($TabFich);
				for($i = 1; $i < $nblignes; $i++) {
					$Event[$j]=explode($separateur,trim(str_replace(array('"',"'"),'',$TabFich[$i])));
					if (trim($Event[$j][4])!=="") {
						$j++;
					}
				}
				
				for($i = 0; $i < count($Event); $i++) {
					
					
					//Determination de l'ID de la plage horaire
					$caractere_trouve=false;
					for($j=0;$j<strlen($Event[$i][1]);$j++) {
						if (!(is_numeric(substr($Event[$i][1],$j,1)))) {
							$caractere=substr($Event[$i][1],$j,1);
							$caractere_trouve=true;
							break;
						}
					}
					
					
					if ($caractere_trouve) {
						$position=stripos(strtolower($Event[$i][1]),$caractere);
						$hdebut[0]=substr($Event[$i][1],0,$position);
						if ($position==strlen($Event[$i][1])-1) {
							$hdebut[1]="00";
						} else {
							$hdebut[1]=substr($Event[$i][1],$position+1,2);
						}
					} else {
						$hdebut[0]=$Event[$i][1];
						$hdebut[1]="00";
					}
					$conversionhdebut=intval($hdebut[0])*60+intval($hdebut[1]);
					$Event[$i][17]=$tab_horaire[count($tab_horaire)-1][1];
					$Event[$i][14]=$tab_horaire[count($tab_horaire)-1][2];
					$Event[$i][15]=$tab_horaire[count($tab_horaire)-1][3];
					$nbhoraire=count($tab_horaire);
					for ($j=0;$j<$nbhoraire;$j++) {
						if ($j==0) {
							$diffhoraire=abs($conversionhdebut-$tab_horaire[$j][0]);
						} else {
							if ($diffhoraire>=abs($conversionhdebut-$tab_horaire[$j][0])) {
								$diffhoraire=abs($conversionhdebut-$tab_horaire[$j][0]);
							} else {
								$Event[$i][17]=$tab_horaire[$j-1][1];
								$Event[$i][14]=$tab_horaire[$j-1][2];
								$Event[$i][15]=$tab_horaire[$j-1][3];
								break;
							}
						}
					}
					
					
					
					//Determination de la classe et de la couleur cellule
					$classe=strtolower(trim($Event[$i][2]));
					$classe_sansespace=strtolower(str_replace(' ','',$classe));
					$Event[$i][8]="0";
					$Event[$i][7]="#FFE4B5";
					for ($jtab = 0 ; $jtab < count($tab_class) ; $jtab++) {
						if (($classe == strtolower($tab_class[$jtab][0]))||($classe_sansespace == strtolower($tab_class[$jtab][0]))) {
							$Event[$i][8] = $tab_class[$jtab][1];
							$Event[$i][7]="#ADD8E6";
							break;
						} elseif ((isset($tab_class[$jtab][2]))&&($tab_class[$jtab][2]!=='')&&(($classe == strtolower($tab_class[$jtab][2]))||($classe_sansespace == strtolower($tab_class[$jtab][2])))) {
							$Event[$i][8] = $tab_class[$jtab][1];
							$Event[$i][7]="#ADD8E6";
							break;
						}
					}
					
					//Determination de la matiere
					
					
					$matiere=trim($Event[$i][3]);
					$mat=str_replace(' ','',$matiere);
					$Event[$i][9]=$matiere;
					$matNomOK = false;
					if ($csvsconet) {
						$nb_mat=count($tab_mat);
						for ($jtab = 0 ; $jtab < $nb_mat ; $jtab++) {
							if ($mat == str_replace(" ","",trim($tab_mat[$jtab][0]))) {
								$Event[$i][9]=trim($tab_mat[$jtab][1]);
								$matiere=$Event[$i][9];
								$mat_long=str_replace(" ","",$tab_mat[$jtab][1]);
								$mat_court=str_replace(" ","",$tab_mat[$jtab][2]);
								$matNomOK = true ;
								break; //le code matiere a ete transforme en le libelle adequat
							}
						}
					}
					
					$matiereOK = false ;
					// Comparons ces libelles matiere ou ce code matiere avec les matieres deja existantes dans la table edt_matieres
					$nb_matieres=count($tab_matieres);
					for ($jtab = 0 ; $jtab < $nb_matieres ; $jtab++) {
						if ($matNomOK) { 
							$matiere_trouve=($mat_long == trim(str_replace(" ","",$tab_matieres[$jtab][0])));
						} else {
							$matiere_trouve=(trim(str_replace(" ","",$tab_matieres[$jtab][0])) == str_replace("'","",trim($mat)));
						}
						if ($matiere_trouve) {
							$Event[$i][11] = $tab_matieres[$jtab][1];
							if (!($matNomOK)) { $Event[$i][9]=$tab_matieres[$jtab][0]; }
							$matiereOK = true ;
							break;
						} else if (($matNomOK)&&(stripos($mat_court,str_replace(" ","",$tab_matieres[$jtab][0])) !== false)) {
							$Event[$i][11] = $tab_matieres[$jtab][1];
							$matiereOK = true ;
							break;
						}
					}
					
					
					
					if ($matiereOK == false) {
						// Ajout de cette matiere dans la base de donnees
						$matiere=str_replace("'","",trim($matiere));
						$matiere = str_replace(array("/", "&", "\'"),"-",$matiere);
						$iprof=count($tab_matieres);
						$insertSQL = sprintf("INSERT INTO cdt_matiere (nom_matiere) VALUES (%s)",
							GetSQLValueString($matiere,"text")
							);
						mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
						$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
						
						$insertSQL = sprintf("SELECT ID_matiere FROM cdt_matiere WHERE nom_matiere=%s LIMIT 1",
							GetSQLValueString($matiere,"text")
							);
						mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
						$DernMatiere = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
						$row_DernMatiere = mysqli_fetch_assoc($DernMatiere);
						
						$tab_matieres[$iprof][1]=$row_DernMatiere['ID_matiere'];
						$tab_matieres[$iprof][0]=$matiere;
						$Event[$i][11]=$tab_matieres[$iprof][1];
						
					}
					
					//Determination de l'ID prof
					
					$nomfichierprof=str_replace("-","_",$Event[$i][4]);
					$ID_prof = ucwords(strtolower($nomfichierprof));
					$Event[$i][16]=$ID_prof;
					
					/*
					//Propre a LCS
					//Recherche de l'ID du prof associee au fichier ics adequat sinon on laisse l'identite du prof
					
					include ("/var/www/lcs/includes/headerauth.inc.php");
					@mysqli_select_db($conn_cahier_de_texte, $DBAUTH) or die ("impossible");
					$result=mysqli_query($conn_cahier_de_texte, "SELECT * FROM params WHERE name='uidPolicy'");
					$rparams=mysqli_fetch_array($result);
					$uidpolicy=$rparams["value"];
					mysqli_free_result($result);
					
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$query_RsProf = "SELECT * FROM cdt_prof ORDER BY ID_prof ASC";
					$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
					$row_RsProf = mysqli_fetch_assoc($RsProf);
					
					
					$pastrouve = true;
					
					// Les tests ne se feront que sur les noms de famille car
					// les utilisateurs d'EDT ne fournissent pas forcement les prenoms
					// des enseignants quand ils etablissent leurs emplois du temps
					$uidpolicy=4;
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
									break;
								}
							}
						} while ($row_RsProf = mysqli_fetch_assoc($RsProf));
						mysqli_free_result($RsProf);
						break;
					}
					*/
					
					$Event[$i][12]=$ID_prof;
					
					//Determination de la semaine A et/ou B
					//En colonne 11 libellee Freq
					//prend la valeur sp /si  ou 1234
					//attention indice 0 pour premiere colonne 1
		
					if ((isset($Event[$i][10]))&&($Event[$i][10]!=="")) {
						if (stripos($Event[$i][10],"A")!==false) {
							$Event[$i][13]="A";
						} else if (stripos($Event[$i][10],"sp")!==false) {
							$Event[$i][13]="A";						
						} else if (stripos($Event[$i][10],"B")!==false) {
							$Event[$i][13]="B";
						} else if (stripos($Event[$i][10],"si")!==false) {
							$Event[$i][13]="B";	
						} else {
							$Event[$i][13]=$Event[$i][10];
						}
					} else {
						$Event[$i][13]="A et B";
					}
					
					
					
					//Determination du groupe : Classe Entiere, Gpe A, Gpe B, Gpe reduit
					$Event[$i][18]="Classe entière";
					if ((isset($Event[$i][6]))&&($Event[$i][6]!=="")) {
						$Groupe=trim($Event[$i][6]);
						
						$Event[$i][18]=$Groupe;
						if ((!(in_array($Groupe,$tab_Groupes)))&&(!(in_array($Groupe,$tab_Groupes2)))) {
							$tab_Groupes[$igpe]=$Groupe;
							$tab_Groupes2[$igpe]=$Groupe;
							$igpe++;
							$insertSQL = sprintf("INSERT INTO cdt_groupe (groupe,code_groupe) 
								VALUES (%s,%s)",
								GetSQLValueString($Groupe,"text"),
								GetSQLValueString($Groupe,"text")
								);
							mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
							$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
						}
					}
				}
			}
			/////////// Les 5 premiers, le 7 et le 11 sont issus directement du CSV
			// 0 : Jour semaine
			// 1 : Horaire de debut
			// 2 : Classe
			// 3 : Matiere
			// 4 : Identite Prof
			// 5 : ID jour semaine  ??? Salle ?
			// 6 : Codegroupe
			// 7 : Couleur_cellule
			// 8 : ID classe
			// 9 : Intitule Matiere
			//10 : semaine A ou B 
			//11 : ID Matiere
			//12 : ID prof
			//13 : Semaine A et/ou B
			//14 : Heure de debut
			//15 : Heure de fin
			//16 : Identite du Prof
			//17 : ID placement cours dans journee
			//18 : Intitule Groupe
			
		//dans le csv >	Jour	Heure	Classe	Matière	Professeur	Salle	Groupe	Regroup	Eff	Mo	Freq	Aire

			// Il reste a remplir la base de donnees.

//dans des versions anterieures, certains ont eu des problemes pour creer cette table
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query = "
CREATE TABLE IF NOT EXISTS `cdt_edt` (
  `ID_emploi` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `prof_ref` varchar(255) default NULL,
  `jour_semaine` enum('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') default NULL,
  `semaine` varchar(255) default NULL,
  `heure` tinyint(2) unsigned default NULL,
  `classe_ID` smallint(5) unsigned default NULL,
  `matiere_ID` smallint(5) unsigned default NULL,
  `heure_debut` varchar(255) default NULL,
  `heure_fin` varchar(255) default NULL,
  `couleur_cellule` varchar(255) default NULL,
  `IdentiteProf` varchar(255) default NULL,
  `groupe` varchar(255) NOT NULL default 'Classe entière',
  PRIMARY KEY  (`ID_emploi`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 CHARACTER SET latin1";
$result= mysqli_query($conn_cahier_de_texte, $query);

			$deleteSQL = "TRUNCATE TABLE cdt_edt;";
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
			
			foreach($Event as $elem) {
				$insertSQL = sprintf("INSERT INTO cdt_edt (prof_ref,jour_semaine,semaine,heure,classe_ID,matiere_ID,heure_debut,heure_fin,couleur_cellule,IdentiteProf,groupe) 
					VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
					GetSQLValueString($elem[12],"text"),
					GetSQLValueString($elem[0],"text"),
					GetSQLValueString($elem[13],"text"),
					GetSQLValueString($elem[17],"text"),
					GetSQLValueString($elem[8],"text"),
					GetSQLValueString($elem[11],"text"),
					GetSQLValueString($elem[14],"text"),
					GetSQLValueString($elem[15],"text"),
					GetSQLValueString($elem[7],"text"),
					GetSQLValueString($elem[16],"text"),
					GetSQLValueString($elem[18],"text")
					);
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
			}
			$updateSQL = sprintf("UPDATE cdt_params SET param_val='UDT' WHERE param_nom='Import'");
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
			?>		
			<blockquote>
			<blockquote>
			<?php		
			$RequeteSQL = sprintf("SELECT DISTINCT IdentiteProf,prof_ref FROM cdt_edt ORDER BY IdentiteProf");
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$NomdesProfs = mysqli_query($conn_cahier_de_texte, $RequeteSQL) or die(mysqli_error($conn_cahier_de_texte));
			$CompteProfs = mysqli_num_rows($NomdesProfs);
			?>
			
			<p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
			<p><b>IMPORTATION AVEC SUCC&Egrave;S DE <?php echo $CompteProfs; ?> EMPLOIS DU TEMPS</b></p>
			<p>Les emplois du temps de chacun des professeurs dont les noms figurent ci-dessous ont &eacute;t&eacute; ins&eacute;r&eacute;s dans la base de donn&eacute;es. Si un enseignant n'acc&egrave;de pas &agrave; son emploi du temps, vous pourrez v&eacute;rifier que son fichier est bien list&eacute; ci-dessus. Dans le cas contraire, il n'&eacute;tait pas dans votre fichier compress&eacute;. V&eacute;rifiez sa pr&eacute;sence dans UDT et recommencez l'op&eacute;ration d'import des emplois du temps.</p>
			<blockquote>
			
			<?php
			echo "<p>";
			while ($row_NomdesProfs = mysqli_fetch_assoc($NomdesProfs)) {
				if ($row_NomdesProfs['IdentiteProf']==$row_NomdesProfs['prof_ref']) {
					echo "<font color=red>".$row_NomdesProfs['IdentiteProf']."</font><BR></BR>";
				} else 
				echo $row_NomdesProfs['IdentiteProf']."<BR></BR>";
			}
			echo "</p>";		
			?>
			</blockquote>
			<p>Les enseignants dont <b>le nom est bleu </b>trouveront la possibilit&eacute; d'ins&eacute;rer directement leur emploi du temps dans le cahier de textes depuis leur menu enseignant.</p>
			<p>Les enseignants dont <font color=red>le nom est rouge </font>ne trouveront pas ce choix directement mais devront choisir parmi plusieurs emplois du temps. La raison est parmi les suivantes :
			<ul><li>Le nom du fichier ne co&iuml;ncide pas assez avec son login.</li>
			<li>L'enseignant en question n'apparait pas dans la liste des professeurs du Cahier de Textes.</li>
			<li>L'enseignant en question ne s'est encore jamais connect&eacute; au Cahier de Textes.</li>
			<li>Il y a un souci d'homonymie sur les noms de famille.</li></ul>
			<BR></BR>
			<p>En tout &eacute;tat de cause, vous pouvez refaire, sans risque, cette &eacute;tape autant de fois que vous le souhaitez, que les emplois du temps aient &eacute;t&eacute; d&eacute;j&agrave; choisis ou non par certains enseignants... &agrave; tout moment de l'ann&eacute;e.</p>
			</blockquote>
			</blockquote>
			
			<?php	
			mysqli_free_result($NomdesProfs);	
		}  else { //Import impossible sans remplissage des plages horaires
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
		unlink($fichier_udt);
		if ($csvsconet) {
			unlink($fichier_sconet);	
		}
	}
}
?>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>

