<?php
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
// création ou modification du fichier serveur_latex.js
// pour tenir compte du choix effectué par l'admin.
// contribution de philippe SIMIER
// ce fichier définit une variable globale serveur_cgi

$url_serveur = $_POST['serveur_latex'];
$contenu ='serveur_cgi ="'.$url_serveur.'";'. "\n";

 // écriture du fichier "serveur_latex.js"
$fp = fopen("../enseignant/xinha/plugins/EditLatex/popups/serveur_latex.js", 'w+');
fputs($fp, $contenu);
fclose($fp);

//echo 'Mise à jour du fichier serveur_latex.js est effectuée</br>';
//echo 'avec l\'url serveur latex :'.$url_serveur.'</br>';

$updateGoTo = "index.php";
header(sprintf("Location: %s", $updateGoTo));
?>
