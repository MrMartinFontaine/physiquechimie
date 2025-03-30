<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 
require_once('../inc/functions_inc.php');

$colname_RsChoixActivite = "0";
if (isset($_POST['ID_activite'])) {
  $colname_RsChoixActivite = (get_magic_quotes_gpc()) ? $_POST['ID_activite'] : addslashes($_POST['ID_activite']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsChoixActivite = sprintf("SELECT * FROM cdt_type_activite WHERE ID_activite=%u", $colname_RsChoixActivite);
$RsChoixActivite = mysqli_query($conn_cahier_de_texte, $query_RsChoixActivite) or die(mysqli_error($conn_cahier_de_texte));
$row_RsChoixActivite = mysqli_fetch_assoc($RsChoixActivite);

$temp=$row_RsChoixActivite['pos_typ'];
  
mysqli_free_result($RsChoixActivite);

$updateSQL = sprintf("UPDATE cdt_type_activite SET pos_typ=%u WHERE ID_activite=%u",
                    GetSQLValueString($_POST['pos_suivant'], "int"),
                    GetSQLValueString($_POST['ID_activite'], "int"));

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
 
$updateSQL2 = sprintf("UPDATE cdt_type_activite SET pos_typ=%u WHERE ID_activite=%u",
                    GetSQLValueString($temp, "int"),
                    GetSQLValueString($_POST['ID_suivant'], "int"));

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Result2 = mysqli_query($conn_cahier_de_texte, $updateSQL2) or die(mysqli_error($conn_cahier_de_texte));

$updateGoTo = 'type_activite_ajout.php?ID_activite='.$_POST['ID_activite'];

header(sprintf("Location: %s", $updateGoTo));
?>
