<?php
//Statistiques

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='nom_etab' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$nom_etab = $row[0];
mysqli_free_result($result_read);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='url_etab' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$url_etab = $row[0];
mysqli_free_result($result_read);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='ind_maj_base' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$ind_maj_base = $row[0];
mysqli_free_result($result_read);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='cdt_LCS' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$cdt_LCS = $row[0];
mysqli_free_result($result_read);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='module_absence' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$module_absence = $row[0];
mysqli_free_result($result_read);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProf1 = "SELECT * FROM cdt_prof";
$RsProf1 = mysqli_query($conn_cahier_de_texte, $query_RsProf1) or die(mysqli_error($conn_cahier_de_texte));
$totalRows_RsProf= mysqli_num_rows($RsProf1);
mysqli_free_result($RsProf1);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$totalClasse= mysqli_num_rows($RsClasse);
mysqli_free_result($RsClasse);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProf = "SELECT identite,email FROM cdt_prof WHERE droits=1 AND email<>'' LIMIT 1";
$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);
mysqli_free_result($RsProf);


$lien="http://www.bonsauveur.eu/wp_cdt/cdt_stats.php?email=".$row_RsProf['email']."&nom_etab=".urlencode($nom_etab)."&url_etab=".urlencode($url_etab)."&indice_version=".$ind_maj_base."&abs=".$module_absence."&nb_users=".$totalRows_RsProf."&nb_classes=".$totalClasse."&adm=".$row_RsProf['identite']."&php_version=".phpversion()."&server_name=".$_SERVER['SERVER_NAME'];

$file=@fopen($lien,'r');
if (!$file) {
  exit;
}
fclose($file);
?>