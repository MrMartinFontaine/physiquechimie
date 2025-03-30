<?php 
require_once('../Connections/conn_cahier_de_texte.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
$query_mat = "SELECT ID_matiere,nom_matiere FROM cdt_matiere ORDER BY nom_matiere";
$Rsmat = mysqli_query($conn_cahier_de_texte, $query_mat);

$items = array();
if($Rsmat && mysqli_num_rows($Rsmat)>0) {
        while($row = mysqli_fetch_array($Rsmat)) {
        	$items[] = array($row[0], utf8_encode($row[1]));
        }        
} 
mysqli_close($conn_cahier_de_texte);
// convert into JSON format and print
echo json_encode($items); 

?>  
