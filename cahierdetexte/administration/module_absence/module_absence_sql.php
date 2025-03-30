<?php 
include "../../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../../index.php");exit;};

require_once('../../inc/functions_inc.php');
require_once('../../Connections/conn_cahier_de_texte.php'); 
// Connection a la base de donnees
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) OR die('Erreur de connexion &agrave; la base de donn&eacute;es. V&eacute;rifiez bien vos param&egrave;tres de connexion et recommencez. ');
// init error
$CodeClasse ="1";
$error_list = "";
// Definition des instructions SQL a effectuer
$query = "
CREATE TABLE IF NOT EXISTS `ele_liste` (
`ID_ele` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
`nom_ele` varchar(50) NOT NULL,
`prenom_ele` varchar(50) NOT NULL,
`classe_ele` varchar(20) NOT NULL,
`groupe_ID_ele` smallint(4) NOT NULL DEFAULT '1',
`groupe_ele` varchar(50) NOT NULL DEFAULT 'Classe entière',
PRIMARY KEY (`ID_ele`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1
";
$result = mysqli_query($conn_cahier_de_texte, $query);
if(mysqli_error($conn_cahier_de_texte)) { $CodeClasse ="0"; $error_list = " - ele_liste <br />";};

$query = "
CREATE TABLE IF NOT EXISTS `ele_absent` (
`ID` int(10) unsigned NOT NULL auto_increment,
`classe_ID` smallint(5) unsigned default NULL,
`classe` varchar(50) default NULL,
`groupe` varchar(50) default NULL,
`heure` tinyint(4) default NULL,
`heure_debut` varchar(50) default NULL,
`heure_fin` varchar(50) default NULL,
`code_date` varchar(255) default NULL,
`salle` varchar(50) default NULL,
`jour_pointe` varchar(50) default NULL,
`eleve_ID` smallint(4) default NULL,
`prof_ID` smallint(4) default NULL,
`motif` varchar(20) default NULL,
`vie_sco_statut` enum('Y','N') DEFAULT 'N',
`retard` enum('O','N') DEFAULT 'N',
`perso1` enum('O','N') DEFAULT 'N',
`perso2` enum('O','N') DEFAULT 'N',
`perso3` enum('O','N') DEFAULT 'N',
  `Ds` varchar(1) DEFAULT NULL,
  `date` varchar(8) DEFAULT NULL,
  `heure_saisie` varchar(10) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `absent` enum('Y','N') DEFAULT 'N',
  `retard_V` enum('Y','N') DEFAULT 'N',
  `pbCarnet` enum('Y','N') DEFAULT 'N',
  `retard_Nv` enum('Y','N') DEFAULT 'N',
  `signature` enum('Y','N') NOT NULL DEFAULT 'N',
  `solde` varchar(32) DEFAULT 'N',
  `surcarnet` varchar(32) DEFAULT 'N',
  `annule` varchar(32) DEFAULT 'N',
PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET latin1
";
// Execution des requetes 
$result = mysqli_query($conn_cahier_de_texte, $query);

if(mysqli_error($conn_cahier_de_texte)) { $CodeClasse ="0"; $error_list = " - ele_absent <br />";};

$query = "
CREATE TABLE IF NOT EXISTS `ele_present` (
  `ID` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `classe_ID` smallint(5) unsigned DEFAULT NULL,
  `heure_debut` varchar(20) DEFAULT NULL,
  `heure_fin` varchar(20) DEFAULT NULL,
  `salle` varchar(20) DEFAULT NULL,
  `eleve_ID` smallint(4) DEFAULT NULL,
  `prof_ID` smallint(4) DEFAULT NULL,
  `travail` varchar(100) DEFAULT NULL,
  `date_heure` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  AUTO_INCREMENT=1  CHARACTER SET latin1
";
// Execution des requetes 
$result = mysqli_query($conn_cahier_de_texte, $query);
if(mysqli_error($conn_cahier_de_texte)) { $CodeClasse ="0"; $error_list .= "- ele_present <br />";};

$query ="
CREATE TABLE IF NOT EXISTS `ele_gic` (
  `ID_ele_gic` smallint(10) unsigned NOT NULL auto_increment,
  `ID_ele` smallint(5) unsigned NOT NULL default '0',
  `ID_gic` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID_ele_gic`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1 
";
// Execution des requetes 
$result = mysqli_query($conn_cahier_de_texte, $query);
if(mysqli_error($conn_cahier_de_texte)) { $CodeClasse ="0"; $error_list .= "- ele_gic <br />";};

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Mises &agrave; jour de la base de donn&eacute;es ";
require_once "../../templates/default/header2.php";
?>

  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <div align="left">
    <blockquote>
    
    	<?php // On affiche le resultat de la commande
	if ($CodeClasse == "0") {
			echo "<p align='center'><strong>Echec de la cr&eacute;ation:</strong><br/>les tables suivantes n'ont pas  pu &ecirc;tre cr&eacute;&eacute;es<br/>".$error_list."<strong><br />Veuillez recommencer l'op&eacute;ration.</strong><br/>Si l'erreur persiste, n'h&eacute;sitez pas &agrave; contacter le d&eacute;veloppeur</p>";
		} 
		elseif ($CodeClasse == "1")
		{
			echo '<p align="center"><strong>Cr&eacute;ation des tables relatives aux absences (ele_liste, ele_gic, ele_absent, ele_present) r&eacute;ussie.</strong></p>';
		}
		
	?>
    </blockquote>
  </div>
  <p>&nbsp; </p>
  	<p align="center"><a href="module_absence_install.php">Retour au menu d'installation du module.</a></p>
	<p align="center"><a href="../index.php">Retour &agrave; l'espace Administration  (Saisie des Enseignants, mati&egrave;res, classes...)</a></p>
  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
