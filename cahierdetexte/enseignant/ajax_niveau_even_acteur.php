<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>6)&&($_SESSION['droits']<>7)&&($_SESSION['droits']<>8))
{ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsCl = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsCl = mysqli_query($conn_cahier_de_texte, $query_RsCl) or die(mysqli_error($conn_cahier_de_texte));
$row_RsCl = mysqli_fetch_assoc($RsCl);
$totalRows_RsCl = mysqli_num_rows($RsCl);

$n=1;
do
{
$ind_even[$n]=$row_RsCl['ID_classe'];
$n=$n+1;
}while ($row_RsCl = mysqli_fetch_assoc($RsCl)) ;




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
// recherche du rang
$r=1;while ($ind_even[$r]<>$row_Rsniv['classe_ID']){$r=$r+1;};


	if ($val_statut == 1 ) {
		echo 'document.getElementById("classeven'.$r.'").checked=true;';
		echo 'document.getElementById("groupeven'.$r.'").value='.$row_Rsniv['groupe_ID'].';';
	} 
	else {
		echo 'document.getElementById("classeven'.$r.'").checked=false;';
		echo 'document.getElementById("groupeven'.$r.'").value=1;';
	};
}while ($row_Rsniv = mysqli_fetch_assoc($Rsniv));

echo '</SCRIPT>';
}
?>