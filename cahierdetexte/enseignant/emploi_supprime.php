<?php 
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>2) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 

require_once('../inc/functions_inc.php');

if ((isset($_GET['ID_emploi'])) && ($_GET['ID_emploi'] != "")) {
	
	$deleteSQL1 = sprintf("DELETE FROM cdt_emploi_du_temps WHERE ID_emploi=%u",
		GetSQLValueString($_GET['ID_emploi'], "int"));
	$deleteSQL2 = sprintf("DELETE FROM cdt_emploi_du_temps_partage WHERE ID_emploi=%u",
		GetSQLValueString($_GET['ID_emploi'], "int"));
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL1) or die(mysqli_error($conn_cahier_de_texte));
	$Result2 = mysqli_query($conn_cahier_de_texte, $deleteSQL2) or die(mysqli_error($conn_cahier_de_texte));
	
	$deleteGoTo = "emploi.php?affiche=".$_GET['affiche'];
	header(sprintf("Location: %s", $deleteGoTo));
}
?>
