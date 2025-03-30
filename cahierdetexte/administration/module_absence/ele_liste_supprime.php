<?php 
include "../../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>3)) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');

if ((isset($_GET['ID_ele'])) && ($_GET['ID_ele'] != "")) {
  $deleteSQL = sprintf("DELETE FROM ele_liste WHERE ID_ele=%s",
                       GetSQLValueString($_GET['ID_ele'], "int"));

  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));

  $deleteGoTo = "ele_liste_affiche.php?classe_ele=".$_GET['classe_ele'];
  if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $deleteGoTo));
}
?>
