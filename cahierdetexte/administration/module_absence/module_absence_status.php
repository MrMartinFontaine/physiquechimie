<?php 


// Fonction de verification d'existence d'une table ou d'une colonne
// A appeler comme suit : exist_table("nom_de_la_table", "nom_de_la_colonne" ou "");

function exist_table($table,$conn_cahier_de_texte,$database_conn_cahier_de_texte){

	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	
	$query = "SHOW COLUMNS FROM $table";
	
	$result = mysqli_query($conn_cahier_de_texte, $query);
	if(mysqli_error($conn_cahier_de_texte)) { 
		$state = "0";
		} else {
		$state ="1";
		}
	return $state;
}



// Fonction de verification du contenu d'une table
// A appeler comme suit : content_table("nom_de_la_table");
// On ne selectionne que la premiere entree. Si elle existe on considere
// que la table est remplie, sinon on considere qu'elle est vide.

function content_table($table, $conn_cahier_de_texte,$database_conn_cahier_de_texte){
	$query = "SELECT * FROM $table LIMIT 1";
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$result = mysqli_query($conn_cahier_de_texte, $query);
	$count_result = mysqli_num_rows($result);
	if ($count_result == 1){
		$state = "1";
		} else {
		$state ="0";
		}
	return $state;
}

function content_column($table, $column,$conn_cahier_de_texte,$database_conn_cahier_de_texte){
	$query = "SELECT $column FROM $table LIMIT 1";
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$result = mysqli_query($conn_cahier_de_texte, $query) or die (mysqli_error($conn_cahier_de_texte));
	$array_result = mysqli_fetch_array($result);
	if ($array_result[0] == ""){
		$state = "0";
		} else {
		$state = "1";
		}
	return $state;
}
?>
