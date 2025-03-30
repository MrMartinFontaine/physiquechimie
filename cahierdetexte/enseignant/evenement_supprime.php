<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

if ((isset($_GET['ID_even'])) && ($_GET['ID_even'] != "")) {
  $deleteSQL = sprintf("DELETE FROM cdt_evenement_contenu WHERE ID_even=%u",
                       GetSQLValueString($_GET['ID_even'], "int"));
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
  
  $deleteSQL = sprintf("DELETE FROM cdt_evenement_acteur WHERE even_ID=%u",
                       GetSQLValueString($_GET['ID_even'], "int"));
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
  
  $delete2SQL = sprintf("DELETE FROM cdt_evenement_destinataire WHERE even_ID=%u",
                       GetSQLValueString($_GET['ID_even'], "int"));
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result2 = mysqli_query($conn_cahier_de_texte, $delete2SQL) or die(mysqli_error($conn_cahier_de_texte));


  $deleteGoTo = "evenement_liste.php";


  header(sprintf("Location: %s", $deleteGoTo));
}
?>
