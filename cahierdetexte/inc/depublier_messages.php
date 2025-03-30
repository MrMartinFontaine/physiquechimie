<?php
//dépublier les messages dont la date de fin de publication est échue
require_once('functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$updateSQL = sprintf(" UPDATE `cdt_message_contenu` SET date_fin_publier=%s , online=%s  WHERE online='O' AND date_fin_publier < '%s' ",
        GetSQLValueString('0000-00-00', "text"),
		GetSQLValueString('N', "text"),
		date('Y-m-d')
		);
$Rsmessage = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
?>
