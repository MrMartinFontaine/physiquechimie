<?php
// mise à jour 5.5.0.2
include "../../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsparams3 = "SELECT param_val FROM cdt_params WHERE param_nom='ind_maj_base'";
$Rsparams3 = mysqli_query($conn_cahier_de_texte, $query_Rsparams3) or die('Erreur SQL !'.$query_Rsparams3.'<br>'.mysqli_error($conn_cahier_de_texte));
$row_Rsparams3 = mysqli_fetch_assoc($Rsparams3);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<5502)) {
	if ($_SESSION['module_absence']=='Oui'){ 
		mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
		$query = " ALTER TABLE `ele_absent` CHANGE `vie_sco_statut` `vie_sco_statut` ENUM('Y','N') NULL DEFAULT 'N' "; 
		$result = mysqli_query($conn_cahier_de_texte, $query);
		
		mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
		$query = " ALTER TABLE `ele_absent` CHANGE `ID` `ID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT "; 
		$result = mysqli_query($conn_cahier_de_texte, $query);

		mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
		$query = " ALTER TABLE `ele_absent` 
					ADD `Ds` varchar(1) DEFAULT NULL,
					ADD `date` varchar(8) DEFAULT NULL,
					ADD `heure_saisie` varchar(10) DEFAULT NULL,
					ADD  `details` varchar(255) DEFAULT NULL,
					ADD  `absent` enum('Y','N') DEFAULT 'N',
					ADD `retard_V` enum('Y','N') DEFAULT 'N',
					ADD `pbCarnet` enum('Y','N') DEFAULT 'N',
					ADD  `retard_Nv` enum('Y','N') DEFAULT 'N',
					ADD  `signature` enum('Y','N') NOT NULL DEFAULT 'N',
					ADD  `solde` varchar(32) DEFAULT 'N',
					ADD  `surcarnet` varchar(32) DEFAULT 'N',
					ADD  `annule` varchar(32) DEFAULT 'N'"; 
		$result = mysqli_query($conn_cahier_de_texte, $query);		
		

	};
};

?>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p align="center">Version sup&eacute;rieure ou &eacute;gale &agrave; 5502 </p>
<p align="center">Mise &agrave; jour de la table ele_absent r&eacute;alis&eacute;e.</p>
<p align="center"><a href="../module_absence/module_absence.php">Retour au menu absence </a></p>
<p>&nbsp; </p>