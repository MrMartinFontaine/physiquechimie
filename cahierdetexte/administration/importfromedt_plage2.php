<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<title>Importation de plages d'emploi du temps depuis un planning</title>
</head>
<?php
/*Importation du fichier csv*/
if(isset($_FILES['datacsv']))
{ 
     $dossier = '../fichiers_joints/';
     $fichier = basename($_FILES['datacsv']['name']);
     /*Test de l'extension*/
     $infosfichier = pathinfo($_FILES['datacsv']['name']);
     $extension_upload = $infosfichier['extension'];
     $extensions_autorisees = array('csv','txt');
     if (in_array($extension_upload, $extensions_autorisees)) { //L'extension est bonne
       /*Upload du fichier*/               
       if(move_uploaded_file($_FILES['datacsv']['tmp_name'], $dossier . $fichier)) //Si la fonction renvoie TRUE, c'est que ça a fonctionné...
       {
            echo '<br />Fichier de donn&eacute;es transf&eacute;r&eacute;<br /><br />';
       }
       else //Sinon (la fonction renvoie FALSE).
       {
            echo '<br />Echec de l\'upload !<br /><br />';
            ?>
            <div align="center">
              <p><br />
                </p>
              <p><a href="index.php">Retour au Menu Administrateur</a></p>
            </div>
            <DIV id=footer></DIV>
</DIV>
            <p>&nbsp;</p>
</body>
            </html>
            <?php  
            exit();
       }
     }//fin extension autorisée
     else {
        echo "<br /><b><font color=\"red\">Le fichier n'est pas au bon format</font></b><br />Echec de l'upload !<br /><br />";
        ?>
        <div align="center"><br /><a href="index.php">Retour au Menu Administrateur</a></div>
        <DIV id=footer></DIV>
        </DIV>
        </body>
        </html>
        <?php  
        exit();
     }
}?>
<body>

<?php
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

/*Variables*/
$fichier='../fichiers_joints/'.$fichier; //Emplacement du csv dans un dossier lect/ecrit

$liste = array(); //initialisation du tableau d'importation

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) OR die('Erreur de connexion &agrave; la base de donn&eacute;es. Recommencez l\'installation et v&eacute;rifiez bien vos param&egrave;tres. ');


/*Ouverture du fichier csv à importer en lecture seulement*/  
if (file_exists($fichier)) {  
$fp = fopen("$fichier", "r");  
}  
else {  
/*le fichier n'existe pas*/  
echo "Fichier introuvable !<br />Importation stopp&eacute;e.";
?>
<div align="center"><br /><a href="index.php">Retour au Menu Administrateur</a></div>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php  
exit();  
};

$nb_lignes=1;
$nb_plages=0; 
$plages = array();

// importation
echo "<br><b>RAPPORT SUR L'INJECTION</b><br><br><br>";

while (!feof($fp))
{

$ligne = fgets($fp,4096);
// on crée un tableau des élements séparés par des points virgule
$liste = explode(";",$ligne);
// premier élément
$liste[0] = ( isset($liste[0]) ) ? $liste[0] : Null; //date
$liste[1] = ( isset($liste[1]) ) ? $liste[1] : Null; //heuredebut
$liste[2] = ( isset($liste[2]) ) ? $liste[2] : Null; //heurefin
$liste[3] = ( isset($liste[3]) ) ? $liste[3] : Null; //duree
$liste[4] = ( isset($liste[4]) ) ? $liste[4] : Null; //nom matiere
$liste[5] = ( isset($liste[5]) ) ? $liste[5] : Null; //nom prof
$liste[6] = ( isset($liste[6]) ) ? $liste[6] : Null; //classe1
$liste[7] = ( isset($liste[7]) ) ? $liste[7] : Null; //groupe1 - Choix du groupe dans la classe 1 pour la seance
$liste[8] = ( isset($liste[8]) ) ? $liste[8] : Null; //classe2 ...cela necessite un regroupement
$liste[9] = ( isset($liste[9]) ) ? $liste[9] : Null; //groupe2- Choix du groupe dans la classe 2 pour la seance en regroupement
$nom_regroup='';

//traitement ligne vide
if (
($liste[1]==Null)&&
($liste[2]==Null)&&
($liste[3]==Null)&&
($liste[4]==Null)&&
($liste[5]==Null)&&
($liste[6]==Null)&&
($liste[7]==Null)&&
($liste[8]==Null)&&
($liste[9]==Null))
{
echo '<br /><b><font color="red">ligne '.$nb_lignes.' La ligne est vide</font></b><br />';}
else {

//re-affectation
if (($liste[6]=='')&&($liste[7]=='')&&($liste[8]<>'')&&($liste[9]<>'')){
$liste[6]=$liste[8];
$liste[7]=$liste[9];
$liste[8]='';
$liste[9]='';
};


//Recherche de l'ID du prof 
if ($liste[5]<>''){
$liste[5]= str_replace(array("/", "&", "\'","'"), "-",$liste[5]);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProf = sprintf("SELECT * FROM cdt_prof WHERE cdt_prof.identite='%s'", $liste[5]);
$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);
$totalRows_RsProf = mysqli_num_rows($RsProf);

if ($totalRows_RsProf ==0){ // on ajoute le prof
$insertSQL = sprintf("INSERT INTO cdt_prof (identite,nom_prof,passe,droits) VALUES ( %s,%s,%s,%s)",
                       GetSQLValueString($liste[5], "text"),
                       GetSQLValueString(sans_accent($liste[5]), "text"),
                       GetSQLValueString('d41d8cd98f00b204e9800998ecf8427e', "text"),
                       GetSQLValueString(2, "int")
                        );
					
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
  $ID_prof=mysqli_insert_id($conn_cahier_de_texte);
  echo '<br><b><font color="blue">'.$liste[5].' a été ajouté dans la table cdt_prof </font></b><br /><br>';
} ;

if ($totalRows_RsProf ==1){
	do {
	$ID_prof=$row_RsProf['ID_prof'];
	} while ($row_RsProf = mysqli_fetch_assoc($RsProf)); 
};

if ($totalRows_RsProf >1){echo '<br><b><font color="blue">Des noms de profs existent en doublons dans la table cdt_profs pour '.$liste[5].'</font></b><br /><br>';};

mysqli_free_result($RsProf);
};

//Recherche de l'ID matiere
if ($liste[4]<>''){
$liste[4]= str_replace(array("/", "&", "\'","'"), "-",$liste[4]);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMat = sprintf("SELECT * FROM cdt_matiere WHERE nom_matiere='%s'", $liste[4]);
$RsMat = mysqli_query($conn_cahier_de_texte, $query_RsMat) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMat = mysqli_fetch_assoc($RsMat);
$totalRows_RsMat = mysqli_num_rows($RsMat);

if ($totalRows_RsMat ==0){ // on ajoute la matiere
$insertSQL = sprintf("INSERT INTO cdt_matiere (nom_matiere) VALUES ( %s)", GetSQLValueString($liste[4], "text"));
                       
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
  $ID_matiere=mysqli_insert_id($conn_cahier_de_texte);
  echo '<br><b><font color="green">'.$liste[4].' a été ajouté dans la table cdt_matiere </font></b><br /><br>';
} ;

if ($totalRows_RsMat ==1){
	do {
	$ID_matiere=$row_RsMat['ID_matiere'];
	} while ($row_RsMat = mysqli_fetch_assoc($RsMat)); 
};

if ($totalRows_RsMat >1){echo '<br><b><font color="red">Des noms de matières existent en doublons dans la table cdt_matiere pour '.$liste[4].'</font></b><br /><br><br>';};

mysqli_free_result($RsMat);
}

//Recherche de l'ID de la classe 
//******************************
$liste[6]= str_replace(array("/", "&", "\'","'"), "-",$liste[6]);




//S'agit-il d'un regroupement ?


//************** REGROUPEMENT*****************************************************************************************

if ($liste[8]<>''){// c'est un regroupement

		$liste[8]= str_replace(array("/", "&", "\'","'"), "-",$liste[8]);

// nom du regroupement
		$nom_regroup=$liste[6].'_'.$liste[8];
//le regroupement existe-il pour le prof
		$query_RsGic = sprintf("SELECT * FROM cdt_groupe_interclasses WHERE nom_gic='%s' AND prof_ID=%u", $nom_regroup,$ID_prof);
		$RsGic = mysqli_query($conn_cahier_de_texte, $query_RsGic) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsGic = mysqli_fetch_assoc($RsGic);
		$totalRows_RsGic = mysqli_num_rows($RsGic);
// le regroupemembnt n'existe pas - le créer
		if ($totalRows_RsGic ==0){ // on ajoute la regroupement
		
		//recuperer les ID_classe des deux classes / on cree les classes si elles n'existent pas

		  
	if ($liste[6]<>''){
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE nom_classe='%s'", $liste[6]);
		$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsClasse = mysqli_fetch_assoc($RsClasse);
		$totalRows_RsClasse = mysqli_num_rows($RsClasse);
		
		if ($totalRows_RsClasse ==0){ // on ajoute la classe
		$insertSQL = sprintf("INSERT INTO cdt_classe (nom_classe) VALUES ( %s)", GetSQLValueString($liste[6], "text"));
		  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		  $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		  $ID_classe1=mysqli_insert_id($conn_cahier_de_texte);
		  echo '<br><b><font color="magenta">'.$liste[6].' a été ajouté dans la table cdt_classe  </font></b><br /><br />';
		} ;
		
		if ($totalRows_RsClasse ==1){
			do {
			$ID_classe1=$row_RsClasse['ID_classe'];
			} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)); 
		};
		
		if ($totalRows_RsClasse >1){echo '<br><b><font color="red">Des noms de classe existent en doublons dans la table cdt_classe pour '.$liste[6].'</font></b><br /><br><br>';};
		
		mysqli_free_result($RsClasse);
	};

	if ($liste[7]<>''){//traitement groupe 1  / si vide classe entiere par defaut mis ds la table
		//le groupe existe-t-il
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsGroupe = sprintf("SELECT * FROM cdt_groupe WHERE groupe='%s'", $liste[7]);
		$RsGroupe = mysqli_query($conn_cahier_de_texte, $query_RsGroupe) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsGroupe = mysqli_fetch_assoc($RsGroupe);
		$totalRows_RsGroupe = mysqli_num_rows($RsGroupe);
		
		if ($totalRows_RsGroupe ==0){ // on ajoute la classe
			$insertSQL = sprintf("INSERT INTO cdt_groupe (groupe) VALUES ( %s)", GetSQLValueString($liste[7], "text"));
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));

			$ID_groupe1=mysqli_insert_id($conn_cahier_de_texte);
			echo '<br><b><font color="magenta">'.$liste[7].' a été ajouté dans la table cdt_groupe  </font></b><br /><br />';
		} ;
		mysqli_free_result($RsGroupe);
	};//fin traitement du groupe 1
 
	if ($liste[8]<>''){
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE nom_classe='%s'", $liste[8]);
		$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsClasse = mysqli_fetch_assoc($RsClasse);
		$totalRows_RsClasse = mysqli_num_rows($RsClasse);
		
		if ($totalRows_RsClasse ==0){ // on ajoute la classe
		$insertSQL = sprintf("INSERT INTO cdt_classe (nom_classe) VALUES ( %s)", GetSQLValueString($liste[8], "text"));
		  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		  $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		  $ID_classe2=mysqli_insert_id($conn_cahier_de_texte);
		  echo '<br><b><font color="magenta">'.$liste[8].' a été ajouté dans la table cdt_classe  </font></b><br /><br>';
		} ;
		
		if ($totalRows_RsClasse ==1){
			do {
			$ID_classe2=$row_RsClasse['ID_classe'];
			} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)); 
		};
		
		if ($totalRows_RsClasse >1){echo '<br><b><font color="red">Des noms de classe existent en doublons dans la table cdt_classe pour '.$liste[8].'</font></b><br /><br><br>';};
		
		mysqli_free_result($RsClasse);
	};
	

		//fin recup des ID de classe : $ID_classe1 et $ID_classe2
		
		//traitement groupe 2  / si vide classe entiere par defaut mis ds la table
		if ($liste[9]<>''){
		//le groupe existe-t-il
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsGroupe = sprintf("SELECT * FROM cdt_groupe WHERE groupe='%s'", $liste[9]);
		$RsGroupe = mysqli_query($conn_cahier_de_texte, $query_RsGroupe) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsGroupe = mysqli_fetch_assoc($RsGroupe);
		$totalRows_RsGroupe = mysqli_num_rows($RsGroupe);
		
		if ($totalRows_RsGroupe ==0){ // on ajoute la classe
			$insertSQL = sprintf("INSERT INTO cdt_groupe (groupe) VALUES ( %s)", GetSQLValueString($liste[9], "text"));
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
			
			$ID_groupe2=mysqli_insert_id($conn_cahier_de_texte);
			
			echo '<br><b><font color="magenta">'.$liste[9].' a été ajouté dans la table cdt_groupe  </font></b><br /><br>';
		} ;
		mysqli_free_result($RsGroupe);
		} else {//Groupe 2 est vide
		$ID_groupe2=1; // vaut classe entière
		}

	;//fin traitement du groupe 2
	if (!isset($ID_groupe1)){$ID_groupe1=1;};
	if (!isset($ID_groupe2)){$ID_groupe2=1;};	
		//on cree le regroupement
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$insertSQL = sprintf("INSERT INTO cdt_groupe_interclasses (nom_gic,prof_ID) VALUES ( '%s',%u)", $nom_regroup, $ID_prof);
		  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		  $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		  $gic_ID=mysqli_insert_id($conn_cahier_de_texte);

		  echo '<br><b><font color="Chocolate">Le regroupement '.$nom_regroup.' a été ajouté dans la table cdt_groupe_interclasses pour '.$liste[5].' </font></b><br /><br>';

		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		//premiere classe
		$insertSQL = sprintf("INSERT INTO cdt_groupe_interclasses_classe (gic_ID,classe_ID,groupe_ID) VALUES ( %u,%u,%u)",$gic_ID,$ID_classe1,$ID_groupe1);
		  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		  $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		 
		//deuxieme classe
		$insertSQL = sprintf("INSERT INTO cdt_groupe_interclasses_classe (gic_ID,classe_ID,groupe_ID) VALUES ( %u,%u,%u)",$gic_ID,$ID_classe2,$ID_groupe2);
		  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		  $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		 
		// variable a injecter ds la table cdt_emploi du temps 
		$ID_classe=0;
		//$gic_ID est connu

		}//fin de creation du regroupement
	else { //le regroupement existe deja
		
		$ID_classe=0;
		$gic_ID=$row_RsGic['ID_gic'];	
	};
} // fin c'est un regroupement

//************************ ce n'est pas un regroupement*********************************
else {  
	if ($liste[6]<>''){ //traitement classe

		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE nom_classe='%s'", $liste[6]);
		$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsClasse = mysqli_fetch_assoc($RsClasse);
		$totalRows_RsClasse = mysqli_num_rows($RsClasse);
		
		if ($totalRows_RsClasse ==0){ // on ajoute la classe
		$insertSQL = sprintf("INSERT INTO cdt_classe (nom_classe) VALUES ( %s)", GetSQLValueString($liste[6], "text"));
		  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		  $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		  $ID_classe=mysqli_insert_id($conn_cahier_de_texte);
		  echo '<br><b><font color="magenta">'.$liste[6].' a été ajouté dans la table cdt_classe  </font></b><br /><br>';
		} ;
		
		if ($totalRows_RsClasse ==1){
			do {
			$ID_classe=$row_RsClasse['ID_classe'];
			} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)); 
		};
		
		if ($totalRows_RsClasse >1){echo '<br><b><font color="red">Des noms de classe existent en doublons dans la table cdt_classe pour '.$liste[6].'</font></b><br /><br><br>';};
		
		mysqli_free_result($RsClasse);
		
		$gic_ID=0;
	}; //fin traitement de la classe
	
	if ($liste[7]<>''){//traitement groupe 1  / si vide classe entiere par defaut mis ds la table
		//le groupe existe-t-il
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsGroupe = sprintf("SELECT * FROM cdt_groupe WHERE groupe='%s'", $liste[7]);
		$RsGroupe = mysqli_query($conn_cahier_de_texte, $query_RsGroupe) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsGroupe = mysqli_fetch_assoc($RsGroupe);
		$totalRows_RsGroupe = mysqli_num_rows($RsGroupe);
		
		if ($totalRows_RsGroupe ==0){ // on ajoute la classe
			$insertSQL = sprintf("INSERT INTO cdt_groupe (groupe) VALUES ( %s)", GetSQLValueString($liste[7], "text"));
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
			
			$ID_groupe=mysqli_insert_id($conn_cahier_de_texte);
			
			echo '<br><b><font color="magenta">'.$liste[7].' a été ajouté dans la table cdt_groupe  </font></b><br /><br>';
		} ;
		mysqli_free_result($RsGroupe);
	};//fin traitement du groupe 1

	
}; // fin ce n'est pas un regroupement





// ************************  INJECTION DANS LA TABLE CDT_EMPLOI_DU_TEMPS **************************************

//determination datedebut et date fin

$date1 =$liste[0];
$date2 =$liste[0];

$date1=substr($date1,6,4).'-'.substr($liste[0],3,2).'-'.substr($date1,0,2);
$date2=$date1;


//determination heure
$heure =substr($liste[1],0,2)-7;
if ($heure<1){$heure=1;}; //securite - on remet les pendules a l'heure


$jour_sem=jour_semaine($liste[0]);
$dejapresent=0;
$partage=0;
if (($liste[5]<>'')&&($liste[4]<>'')&&($liste[6]<>'')){

//on verifie si la plage n'est pas dejà dans la table

		$query_RsEdt = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE prof_ID=%u AND jour_semaine='%s' AND heure=%u AND classe_ID=%u AND gic_ID=%u AND matiere_ID=%u AND heure_debut ='%s' AND edt_exist_debut='%s' ", $ID_prof,$jour_sem,$heure,$ID_classe,$gic_ID,$ID_matiere,$liste[1],$date1);
		$RsEdt = mysqli_query($conn_cahier_de_texte, $query_RsEdt) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsEdt = mysqli_fetch_assoc($RsEdt);
		$totalRows_RsEdt = mysqli_num_rows($RsEdt);
		if ($totalRows_RsEdt>0)	{ 
				echo '<br><b><font color="red">ligne '.$nb_lignes.' : La plage du '. $liste[0].' pour '.$liste[5].' en '.$liste[4].' avec '.$liste[6].' existe d&eacute;ja</font></b><br /><br>';	
				$dejapresent=1;
				mysqli_free_result($RsEdt);
		// la plage existe-t-elle pour la meme matiere mais un autre prof ? Si oui envisager le partage		
		};
		
		$query_RsEdt = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE prof_ID<>%u AND jour_semaine='%s' AND heure=%u AND classe_ID=%u AND gic_ID=%u AND matiere_ID=%u AND heure_debut ='%s' AND edt_exist_debut='%s' ", $ID_prof,$jour_sem,$heure,$ID_classe,$gic_ID,$ID_matiere,$liste[1],$date1);
		
		$RsEdt = mysqli_query($conn_cahier_de_texte, $query_RsEdt) or die(mysqli_error($conn_cahier_de_texte));
		
		$row_RsEdt = mysqli_fetch_assoc($RsEdt);
		$totalRows_RsEdt = mysqli_num_rows($RsEdt);

				//recuperer le ID_emploi de l'enregistrement présent
		if ($totalRows_RsEdt ==1){
					do {
					$ID_emploi=$row_RsEdt['ID_emploi'];
					$profpartage_ID=$row_RsEdt['prof_ID'];
					} while ($row_RsEdt = mysqli_fetch_assoc($RsEdt)); 
					$partage=1;
					
				// Ajout dans cdt_emploi_du_temps partage du ID_emploi et profpartage_ID
				
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$insertSQL = sprintf("INSERT INTO cdt_emploi_du_temps_partage (ID_emploi,profpartage_ID) VALUES (%u, %u)",
							GetSQLValueString($ID_emploi, "int"),
							GetSQLValueString($profpartage_ID, "int"));			
				$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
				echo '<br><b><font color="red">La plage du '. $liste[0].' pour '.$liste[5].' en '.$liste[4].' avec '.$liste[6].' sera partag&Eacute;e</font></b><br /><br>';
				
				mysqli_free_result($RsEdt);	
		}; //fin du enregistrement trouvé







//si pas trouvé ou trouvé mais avec un autre prof(partagé) alors on injecte
if (($dejapresent==0)||($partage==1)) { 

					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$insertSQL = sprintf("INSERT INTO cdt_emploi_du_temps (prof_ID, jour_semaine,  heure, classe_ID,groupe, gic_ID, matiere_ID, heure_debut, heure_fin,duree, edt_exist_debut,edt_exist_fin) VALUES (%u, %s, %u, %u, %s, %u, %u, %s, %s, %s, %s, %s )",
                    GetSQLValueString($ID_prof, "int"),
                    GetSQLValueString($jour_sem, "text"),
					GetSQLValueString($heure, "int"),
					GetSQLValueString($ID_classe, "int"),
					GetSQLValueString($liste[7], "text"), //nom du groupe dans classe 1
					GetSQLValueString($gic_ID, "int"), //valeur du gic_ID  //  0 si pas de regroupement 
					GetSQLValueString($ID_matiere, "int"),
					GetSQLValueString($liste[1], "text"), //heure debut
					GetSQLValueString($liste[2],"text"), ////heure fin
					GetSQLValueString($liste[3], "text"), //duree
					GetSQLValueString($date1, "text"),
					GetSQLValueString($date2, "text")		

					);
					//echo $insertSQL.'<br><br><br>';
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
			
				$nb_plages=$nb_plages+1;
				$plage0[$nb_plages]=$liste[0];
				$plage1[$nb_plages]=$liste[1];
				$plage2[$nb_plages]=$liste[2];
				$plage3[$nb_plages]=$liste[3];				
				$plage4[$nb_plages]=$liste[4];
				$plage5[$nb_plages]=$liste[5];
				$plage6[$nb_plages]=$liste[6];
				$plage7[$nb_plages]=$liste[7];				
				$plage8[$nb_plages]=$liste[8];
				$plage9[$nb_plages]=$liste[9];
				$plage10[$nb_plages]=$nb_lignes; //$nb_plages;				
				
				//$nb_plages=$nb_plages+1;				
				echo 'ligne '. $nb_plages.' :      '.$liste[0].'     de   '. $liste[1].'   &agrave;   '.$liste[2].'    ('.$liste[3].') >>>    '. $liste[4].'   avec '.$liste[5].'   pour   '.$liste[6].' ( groupe : '.$liste[7].') ';	
				if ($nom_regroup<>''){ echo '   et   '.$liste[8] .' ('.$liste[9].') ';};
				echo '<br>';
			
				
		}; //du if table n'existe pas

}

else
{
	if($liste[0]<>'') {//ce n'est pas la derniere ligne du fichier
		$ligne_erreur=$nb_lignes+1;
		echo '<br><b><font color="red"> ATTENTION >>> En ligne '.$ligne_erreur.' Un champ est vide pour ...'.$liste[5].' en... '.$liste[4].' avec ...'.$liste[6].'</font></b><br />';
	};
};

};//du ligne vide

$nb_lignes=$nb_lignes+1;

}; //du while

//$nb_lignes_total=$nb_lignes-1;
?>
<br /><br />
	<table width="100%" border="0">
  <tr>
    <td class="Style6">&nbsp;Ligne</td>
    <td class="Style6">&nbsp;Date</td>
    <td class="Style6">&nbsp;Heure début</td>
    <td class="Style6">&nbsp;Heure Fin</td>
    <td class="Style6">&nbsp;Dur&eacute;e</td>
    <td class="Style6">&nbsp;Mati&egrave;re</td>
    <td class="Style6">&nbsp;Prof.</td>
    <td class="Style6">&nbsp;Classe</td>
    <td class="Style6">&nbsp;Groupe</td>
    <td class="Style6">&nbsp;Classe(R)</td>
    <td class="Style6">&nbsp;Groupe(R)</td>	
  </tr>
  <?php
  $t=$nb_plages;
for ($p=1; $p<=$t; $p++) {
    
?>
  <tr>
    <td class="tab_detail_gris"><?php echo $plage10[$p];?></td>
    <td class="tab_detail_gris"><?php echo $plage0[$p];?></td>
    <td class="tab_detail_gris"><?php echo $plage1[$p];?></td>
    <td class="tab_detail_gris"><?php echo $plage2[$p];?></td>
    <td class="tab_detail_gris"><?php echo $plage3[$p];?></td>
    <td class="tab_detail_gris"><?php echo $plage4[$p];?></td>
    <td class="tab_detail_gris"><?php echo $plage5[$p];?></td>
    <td class="tab_detail_gris"><?php echo $plage6[$p];?></td>
    <td class="tab_detail_gris"><?php echo $plage7[$p];?></td>
    <td class="tab_detail_gris"><?php echo $plage8[$p];?></td>
    <td class="tab_detail_gris"><?php echo $plage9[$p];?></td>
  </tr>
  

<?php
	
};
?>
</table>
<?php
$nb_lignes_total=$nb_lignes-1;
echo '<br /><b><font color="red">'.$nb_lignes_total.' lignes de votre fichier ont &eacute;t&eacute; trait&eacute;es. </font></b><br /><br />';
echo '<br /><b><font color="red">'.$nb_plages.' plages horaires ont &eacute;t&eacute; inject&eacute;es dans la table des emplois du temps.</font></b><br />';
// fermeture du fichier
fclose($fp);
?>
<br />
<div align="center"><br /><a href="index.php">Retour au Menu Administrateur</a></div>
<br /><br /><br />
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
