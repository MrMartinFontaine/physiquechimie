<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

if ((isset($_GET['ID_agenda'])) && ($_GET['ID_agenda'] != "")) {

  $deleteSQL = sprintf("DELETE FROM cdt_agenda WHERE ID_agenda=%u",
                       GetSQLValueString($_GET['ID_agenda'], "int"));

  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));

  $deleteGoTo = "even_ajout.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $deleteGoTo));
}
?>
