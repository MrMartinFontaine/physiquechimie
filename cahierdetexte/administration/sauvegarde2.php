<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>4)){ header("Location: ../../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 
require_once('../inc/functions_inc.php');


function dumpMySQL($serveur, $login, $password, $base, $type_sauvegarde,$nom_fich) 
{ 
	$conn_cahier_de_texte = mysqli_connect($serveur, $login, $password); 
	mysqli_select_db($conn_cahier_de_texte, $base); 
	
	$entete = "-- ----------------------\n"; 
	$entete .= "-- dump de la base ".$base." au ".date("d-M-Y")."\n"; 
	$entete .= "-- ----------------------\n\n\n"; 
	$creations = ""; 
	$insertions = "\n\n"; 
	$listeTables = mysqli_query($conn_cahier_de_texte, "SHOW TABLES");
	
	$number_archives=$type_sauvegarde-5;
	$conn_cahier_de_texte = mysqli_connect($serveur, $login, $password); 
	mysqli_select_db($conn_cahier_de_texte, $base); 
	mysqli_query($conn_cahier_de_texte, "SET NAMES latin1");
	if (($number_archives>0)&&(mysqli_query($conn_cahier_de_texte, "SELECT ID_agenda FROM cdt_agenda_save".$number_archives))){
		$type_sauvegarde=6;
	}
	
	
	while($table = mysqli_fetch_array($listeTables)) 
	{ 
		$tabHS=true;
		switch ($type_sauvegarde) {
		case 1:
			if ((substr($table[0],0,4)<>'cdt_')&&(substr($table[0],0,4)<>'ele_')) {$tabHS=false;}
			break;
		case 2:
			if (((substr($table[0],0,4)<>'cdt_')||(strpos($table[0],'save')<>false))&&(substr($table[0],0,4)<>'ele_')) {$tabHS=false;}
			break;
		case 3:
			if ((!(strpos($table[0],'agenda'))) || ((strpos($table[0],'save')))) {$tabHS=false;}
			break;
		case 4:
			if ((((substr($table[0],0,4)<>'cdt_')||(strpos($table[0],'save')<>false))&&(substr($table[0],0,4)<>'ele_'))||(strpos($table[0],'agenda')<>false)) {$tabHS=false;}
			break;
		case 5:
			if (!(strpos($table[0],'save'))) {$tabHS=false;}
			break;
		case 6 :
			if (!(strpos($table[0],'save'.$number_archives))) {$tabHS=false;}
		case 7:
			if ((!(strpos($table[0],'emploi_du_temps'))) || ((strpos($table[0],'save')))) {$tabHS=false;}
			break;
		};
		
		
		if ($tabHS) {
			
			$creations .= "-- -----------------------------\n"; 
			$creations .= "-- creation de la table ".$table[0]."\n"; 
			$creations .= "-- -----------------------------\n"; 
			$listeCreationsTables = mysqli_query($conn_cahier_de_texte, "show create table ".$table[0]); 
			while($creationTable = mysqli_fetch_array($listeCreationsTables)) 
			{ 
				$creations .= $creationTable[1].";\n\n"; 
			} 
			
			$donnees = mysqli_query($conn_cahier_de_texte, "SELECT * FROM ".$table[0]); 
			$insertions .= "-- -----------------------------\n"; 
			$insertions .= "-- insertions dans la table ".$table[0]."\n"; 
			$insertions .= "-- -----------------------------\n"; 
			while($nuplet = mysqli_fetch_array($donnees)) 
			{ 
			
			
				$insertions .= "INSERT INTO `".$table[0]."`" ;
				
				$insertions .= " ( ";
				
				for($i=0; $i < mysqli_field_count($conn_cahier_de_texte); $i++) 
				{ 
					if($i != 0) 
						$insertions .=  ", "; 
					$finfo = mysqli_fetch_field_direct($donnees, $i);

					$insertions .=  "`" .  $finfo->name .  "`" ; //name
				}
				
				$insertions .= " ) ";
				$insertions .=   "VALUES(";
				
				for($i=0; $i < mysqli_field_count($conn_cahier_de_texte); $i++) 
				{ 
				    $finfo = mysqli_fetch_field_direct($donnees, $i);
					//echo $finfo->type. '  '.$finfo->name.'                      ';
					if($i != 0) 
						$insertions .=  ", "; 
					if($finfo->type == 253 || $finfo->type == 252 || $finfo->type == 10 || $finfo->type == 11 || $finfo->type == 12 || $finfo->type == 254) //type
						$insertions .=  "'"; 
					$insertions .= addslashes($nuplet[$i]); 
					if($finfo->type == 253 || $finfo->type == 252 || $finfo->type == 10 || $finfo->type == 11 || $finfo->type == 12 || $finfo->type == 254) //type
						$insertions .=  "'"; 
				} 
				$insertions .=  ");\n"; 
			} 
			$insertions .= "\n"; 
		}
	} 
	
	mysqli_close($conn_cahier_de_texte); 
	$chemin_nom_fich='../fichiers_joints/'.$nom_fich;
	$fichierDump = fopen($chemin_nom_fich, "wb"); 
	
	fwrite($fichierDump, $entete); 
	fwrite($fichierDump, $creations); 
	fwrite($fichierDump, $insertions); 
	fclose($fichierDump);  
	
} 


//*********************************************

if (isset($_POST['type_sauvegarde'])){
	if ($_POST['type_sauvegarde']>5) {
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_archives = sprintf("SELECT NomArchive FROM `cdt_archive` WHERE NumArchive=%u",$_POST['type_sauvegarde']-5);
		$archives = mysqli_query($conn_cahier_de_texte, $query_archives) or die(mysqli_error($conn_cahier_de_texte));
		$row_archives = mysqli_fetch_array($archives);
		$Nom_archive = str_replace(' ','',$row_archives['NomArchive']);
		mysqli_free_result($archives);
	}
	
	switch ($_POST['type_sauvegarde']) {
	case 1:
		$nom_fich='cdt_complet_'.date('d-m-Y').'.sql';
		break;
	case 2:
		$nom_fich='cdt_en_cours_'.date('d-m-Y').'.sql';
		break;
	case 3:
		$nom_fich='cdt_agenda_'.date('d-m-Y').'.sql';
		break;
	case 4:
		$nom_fich='cdt_en_cours_sauf_agenda_'.date('d-m-Y').'.sql';
		break;
	case 5:
		$nom_fich='cdt_archives_'.date('d-m-Y').'.sql';
		break;
	case 7:
		$nom_fich='cdt_emploi_du_temps_'.date('d-m-Y').'.sql';
		break;		
	default:
		$nom_fich='cdt_archives_'.$Nom_archive.'_'.date('d-m-Y').'.sql';
	}
	
	$updateSQL2 = sprintf("UPDATE cdt_prof SET date_maj='%s' WHERE droits=1",date('Y-m-d'));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result2 = mysqli_query($conn_cahier_de_texte, $updateSQL2) or die(mysqli_error($conn_cahier_de_texte));
	
	dumpMySQL($hostname_conn_cahier_de_texte, $username_conn_cahier_de_texte,  $password_conn_cahier_de_texte, $database_conn_cahier_de_texte,$_POST['type_sauvegarde'],$nom_fich);
	
	$Fichier_a_telecharger= '../fichiers_joints/'.$nom_fich;
	$type = "Document texte";
	
	$ch_header="Content-disposition: attachment; filename=".$nom_fich;
header($ch_header); 
header("Content-Type: application/force-download"); 
header("Content-Type: text/plain; charset=ISO-8859-1");
header("Content-Transfer-Encoding: $type\n"); // Surtout ne pas enlever le \n
//header("Content-Transfer-Encoding: 8bit\n"); // Surtout ne pas enlever le \n
header("Content-Length: ".filesize($Fichier_a_telecharger)); 
header("Pragma: no-cache"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public"); 
header("Expires: 0"); 
	readfile($Fichier_a_telecharger); 
	unlink($Fichier_a_telecharger);
	
}
if ($_SESSION['droits']==1){ $updateGoTo = "index.php";}
else if ($_SESSION['droits']==4){ $updateGoTo = "../direction/direction.php";};
header(sprintf("Location: %s", $updateGoTo));
?>
