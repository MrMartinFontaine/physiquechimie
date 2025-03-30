<?php
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>4) { header("Location: ../index.php");exit;};
header("Content-Type:text/plain; charset=iso-8859-1");
session_start();
require_once('../Connections/conn_cahier_de_texte.php');
$updateSQL="UPDATE cdt_agenda SET date_visa = ".date('Ymd')." WHERE ID_agenda =".$_POST['ID_agenda'];
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $updateSQL2="UPDATE cdt_prof SET date_maj = ".date('Ymd')." WHERE ID_prof =".$_POST['numprof'];
  $Result2 = mysqli_query($conn_cahier_de_texte, $updateSQL2) or die(mysqli_error($conn_cahier_de_texte));
?>