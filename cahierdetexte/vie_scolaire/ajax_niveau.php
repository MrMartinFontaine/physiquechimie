<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

isset($_POST["ID_niv"]) ? $ID_niv = $_POST["ID_niv"] : $ID_niv = NULL;
isset($_POST["val_statut"]) ? $val_statut = $_POST["val_statut"] : $val_statut = NULL;

if ( !is_null($ID_niv) && !is_null($val_statut)  ) {

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsniv = sprintf("SELECT classe_ID,groupe_ID FROM cdt_niveau_classe WHERE niv_ID=%u ",$ID_niv);
$Rsniv = mysqli_query($conn_cahier_de_texte, $query_Rsniv) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsniv = mysqli_fetch_assoc($Rsniv);
$totalRows_Rsniv = mysqli_num_rows($Rsniv);
echo '<SCRIPT LANGUAGE="JavaScript">';

do {
	if ($val_statut == 1 ) {
		echo 'document.getElementById("classe'.$row_Rsniv['classe_ID'].'").checked=true;';
		echo 'document.getElementById("groupe'.$row_Rsniv['classe_ID'].'").value='.$row_Rsniv['groupe_ID'].';';
	} 
	else {
		echo 'document.getElementById("classe'.$row_Rsniv['classe_ID'].'").checked=false;';
		echo 'document.getElementById("groupe'.$row_Rsniv['classe_ID'].'").value=1;';
	};
}while ($row_Rsniv = mysqli_fetch_assoc($Rsniv));

echo '</SCRIPT>';
}
?>