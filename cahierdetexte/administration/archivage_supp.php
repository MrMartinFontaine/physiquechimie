<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

if ((isset($_GET['archID'])) && ($_GET['archID'] != "")) {
	$num_archive="_save".$_GET['archID'];
	
	$deleteSQL1 = "DROP TABLE cdt_groupe$num_archive;";
	$deleteSQL2 = "DROP TABLE cdt_agenda$num_archive;";
	$deleteSQL3 = "DROP TABLE cdt_classe$num_archive;";
	$deleteSQL4 = "DROP TABLE cdt_matiere$num_archive;";
	$deleteSQL5 = "DROP TABLE cdt_emploi_du_temps$num_archive;";
	$deleteSQL6 = "DROP TABLE cdt_travail$num_archive;";
	$deleteSQL7 = "DROP TABLE cdt_groupe_interclasses$num_archive;";
	$deleteSQL8 = "DROP TABLE cdt_groupe_interclasses_classe$num_archive;";
	$deleteSQL9 = "DROP TABLE cdt_fichiers_joints$num_archive;";
	$deleteSQL10 = "DROP TABLE cdt_remplacement$num_archive;";
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL1);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result2 = mysqli_query($conn_cahier_de_texte, $deleteSQL2);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result3 = mysqli_query($conn_cahier_de_texte, $deleteSQL3);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result4 = mysqli_query($conn_cahier_de_texte, $deleteSQL4);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result5 = mysqli_query($conn_cahier_de_texte, $deleteSQL5);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result6 = mysqli_query($conn_cahier_de_texte, $deleteSQL6);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result7 = mysqli_query($conn_cahier_de_texte, $deleteSQL7);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result8 = mysqli_query($conn_cahier_de_texte, $deleteSQL8);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result9 = mysqli_query($conn_cahier_de_texte, $deleteSQL9);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result10 = mysqli_query($conn_cahier_de_texte, $deleteSQL10);
	
	$deleteSQL = sprintf("DELETE FROM cdt_archive WHERE NumArchive=%s",
		GetSQLValueString($_GET['archID'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result = mysqli_query($conn_cahier_de_texte, $deleteSQL);
	
	
	
	$deleteGoTo = "archivage.php";
	if (isset($_SERVER['QUERY_STRING'])) {
		$deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
		$deleteGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $deleteGoTo));
}
?>
