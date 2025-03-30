<?php

// Get parameters from Array
$matid = !empty($_GET['id'])?intval($_GET['id']):0; 
require_once('../Connections/conn_cahier_de_texte.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
if ($matid==0) {
	
	$items = array();
	$items[] = array( -1, 'S&eacute;lectionnez d\'abord une premi&egrave;re mati&egrave;re');
	echo json_encode($items);
}
else {
	$query_mat2 = sprintf("SELECT ID_matiere,nom_matiere FROM cdt_matiere WHERE ID_matiere!=%u ORDER BY nom_matiere",$matid);
	$Rsmat2 = mysqli_query($conn_cahier_de_texte, $query_mat2);
	
	$items = array();
	if($Rsmat2 && mysqli_num_rows($Rsmat2)>0) {
		while($row = mysqli_fetch_array($Rsmat2)) {
			$items[] = array($row[0], utf8_encode($row[1]));
		}        
	}
	mysqli_close($conn_cahier_de_texte);
	echo json_encode($items);
}
?>

