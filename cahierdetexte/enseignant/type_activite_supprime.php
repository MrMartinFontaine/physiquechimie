<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 

require_once('../inc/functions_inc.php');

if ((isset($_GET['ID_activite'])) && ($_GET['ID_activite'] != "")) {

$choix_prof_RsActivite = "0";
if (isset($_SESSION['ID_prof'])) {
  $choix_prof_RsActivite = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsActivite = sprintf("SELECT * FROM cdt_type_activite WHERE ID_prof=%u ", $choix_prof_RsActivite);
$RsActivite = mysqli_query($conn_cahier_de_texte, $query_RsActivite) or die(mysqli_error($conn_cahier_de_texte));
$row_RsActivite = mysqli_fetch_assoc($RsActivite);
$totalRows_RsActivite = mysqli_num_rows($RsActivite);

if ($totalRows_RsActivite>1){

  $deleteSQL = sprintf("DELETE FROM cdt_type_activite WHERE ID_activite=%u",
                       GetSQLValueString($_GET['ID_activite'], "int"));

  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));

  $deleteGoTo = "type_activite_ajout.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $deleteGoTo));
  
  } else { echo '<br /><br /><p align="center" >Vous devez conserver au moins un type d\'activit&eacute;</p><br /><br />';?>
  <p align="center" ><a href="type_activite_ajout.php">Retour au menu G&eacute;rer mes types d'activit&eacute;s</a></p>
  <?php
  };
}
?>
