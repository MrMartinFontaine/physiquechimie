<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

if ((isset($_GET['ID_message'])) && ($_GET['ID_message'] != "")) {
	$deleteSQL = sprintf("DELETE FROM cdt_message_contenu WHERE ID_message=%u",
		GetSQLValueString($_GET['ID_message'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$delete2SQL = sprintf("DELETE FROM cdt_message_destinataire WHERE message_ID=%u",
		GetSQLValueString($_GET['ID_message'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result2 = mysqli_query($conn_cahier_de_texte, $delete2SQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$delete3SQL = sprintf("DELETE FROM cdt_message_modif WHERE ID_message=%u",
		GetSQLValueString($_GET['ID_message'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result3 = mysqli_query($conn_cahier_de_texte, $delete3SQL) or die(mysqli_error($conn_cahier_de_texte));
	
	
	//suppression des fichiers joints
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE message_ID=".$_GET['ID_message'];
	$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
	$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
	
	// suppression des fichiers joints
	if ($totalRows_Rs_fichiers_joints_form <> '0'){ 
		do { 
			// ne pas supprimer le fichier si utilise par ailleurs 
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$ch_select="%_".strchr($row_Rs_fichiers_joints_form['nom_fichier'],'_');
			$query_Recordset3 = sprintf("SELECT * FROM cdt_message_fichiers WHERE nom_fichier like '%s' ",$ch_select );
			$Recordset3 = mysqli_query($conn_cahier_de_texte, $query_Recordset3) or die(mysqli_error($conn_cahier_de_texte));
			$row_Recordset3 = mysqli_fetch_assoc($Recordset3);
			$totalRows_Recordset3 = mysqli_num_rows($Recordset3);
			if ($totalRows_Recordset3==1){
				$fichier = '../fichiers_joints_message/'.$row_Recordset3['nom_fichier'];
				unlink($fichier);		
			}
			mysqli_free_result($Recordset3);
			
		} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
		mysqli_free_result($Rs_fichiers_joints_form);
		
		//on efface de la table fichiers_joints
		$deleteSQL = "DELETE FROM cdt_message_fichiers WHERE message_ID=".$_GET['ID_message'];
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	}
	
	
	if ((isset($_GET['invite']))||(isset($_GET['retour_ecrire']))){$deleteGoTo="ecrire.php?date=".date('Ymd');
		
	} else {  
		$deleteGoTo = "message_ajout.php";
		if(isset($_GET['dest_profs'])) {$deleteGoTo.='?dest_profs=0';};
	};
	header(sprintf("Location: %s", $deleteGoTo));
}
?>
