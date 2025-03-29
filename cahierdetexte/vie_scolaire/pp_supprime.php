<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

if ((isset($_GET['pp_ID'])) && ($_GET['pp_ID'] != "")) {
	$deleteSQL = sprintf("DELETE FROM cdt_prof_principal WHERE ID_pp=%s",
		GetSQLValueString($_GET['pp_ID'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	header("Location: prof_principaux_liste.php");
}
?>
