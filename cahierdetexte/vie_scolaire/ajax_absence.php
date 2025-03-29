<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');


$msg = "<img src='../images/error.png' >&nbsp;Aucune donn&eacute;es envoy&eacute;es";


isset($_POST["ele_absent_id"]) ? $ele_absent_id = $_POST["ele_absent_id"] : $ele_absent_id = NULL;
isset($_POST["id_viesco_prof"]) ? $id_viesco_prof = $_POST["id_viesco_prof"] : $id_viesco_prof = NULL;
isset($_POST["ele_absent_statut"]) ? $ele_absent_statut = $_POST["ele_absent_statut"] : $ele_absent_statut = NULL;

if ( !is_null($ele_absent_id) && !is_null($ele_absent_statut)  ) {
	
	if ($ele_absent_statut == 1 ) {
		$msg = "<img src='../images/accept.png' >&nbsp;Absence valid&#233;e";
	} else {
		$msg = "<img src='../images/accept.png' >&nbsp;Absence d&#233;-valid&#233;e";
	}

	
	// On crée/met à jour l'acquittement
	$updateSQL = sprintf("UPDATE ele_absent SET vie_sco_statut = %s WHERE ele_absent.ID = %s ", 
	  GetSQLValueString($ele_absent_statut, "int"),
	  GetSQLValueString($ele_absent_id, "int")
	  
	  );

	  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	  $Result = mysqli_query($conn_cahier_de_texte, $updateSQL) ;
	  if (!$Result) { $msg = "<img src='../images/exclamation.png' >&nbsp;Erreur lors de l'insertion : "  . mysqli_error($conn_cahier_de_texte)  ; }

}

echo $msg;


?>
