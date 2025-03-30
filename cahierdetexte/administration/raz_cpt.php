<?php
//Remise à zéro du compteur de consultations du cdt
include('../Connections/conn_cahier_de_texte.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsCompt = "UPDATE `cdt_params` SET `param_val` = '0' WHERE `param_nom` ='compteur'";
$result = mysqli_query($conn_cahier_de_texte, $query_RsCompt) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));
$query_RsCompt = "UPDATE `cdt_params` SET `param_val` = '".date('Ymd')."' WHERE `param_nom` ='date_raz_compteur'";
$result = mysqli_query($conn_cahier_de_texte, $query_RsCompt) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));
header('Location: index.php')
?>