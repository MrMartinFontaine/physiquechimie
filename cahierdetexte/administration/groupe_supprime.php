<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

if ((isset($_GET['ID_groupe'])) && ($_GET['ID_groupe'] != "")) {
  $deleteSQL = sprintf("DELETE FROM cdt_groupe WHERE ID_groupe=%u",
                       GetSQLValueString($_GET['ID_groupe'], "int"));

  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
  header("Location:groupe_ajout.php");    
} else { 
    header("Location:index.php");
}
?>
