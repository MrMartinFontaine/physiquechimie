<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};

require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
if ((isset($_GET['ID_gic'])) && ($_GET['ID_gic'] != "")) {

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE ID_gic=%u", GetSQLValueString($_GET['ID_gic'], "int"));
	$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsgic = mysqli_fetch_assoc($Rsgic);
	$totalRows_Rsgic = mysqli_num_rows($Rsgic);
	
	$query_RsEdt = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE gic_ID=%u",	GetSQLValueString($_GET['ID_gic'], "int"));
	$RsEdt = mysqli_query($conn_cahier_de_texte, $query_RsEdt) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsEdt = mysqli_fetch_assoc($RsEdt);
	$totalRows_RsEdt = mysqli_num_rows($RsEdt);
	
	$query_RsAgenda = sprintf("SELECT * FROM cdt_agenda WHERE gic_ID=%u", GetSQLValueString($_GET['ID_gic'], "int"));
	$RsAgenda = mysqli_query($conn_cahier_de_texte, $query_RsAgenda) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsAgenda = mysqli_fetch_assoc($RsAgenda);
	$totalRows_RsAgenda = mysqli_num_rows($RsAgenda);



}

else {
	$no_deleteGoTo = "groupe_interclasses_ajout.php";
	header(sprintf("Location: %s", $no_deleteGoTo));
}




if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") && (isset($_GET['ID_gic']))) {

	
	$deleteSQL = sprintf("DELETE FROM cdt_groupe_interclasses WHERE ID_gic=%u",	GetSQLValueString($_GET['ID_gic'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	
	$delete2SQL = sprintf("DELETE FROM cdt_groupe_interclasses_classe WHERE gic_ID=%u",
	GetSQLValueString($_GET['ID_gic'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result2 = mysqli_query($conn_cahier_de_texte, $delete2SQL) or die(mysqli_error($conn_cahier_de_texte));
	
if  ($totalRows_RsEdt>0){

	$delete4SQL = sprintf("DELETE FROM cdt_emploi_du_temps WHERE gic_ID=%u",
	GetSQLValueString($_GET['ID_gic'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result4 = mysqli_query($conn_cahier_de_texte, $delete4SQL) or die(mysqli_error($conn_cahier_de_texte));
	
if  ($totalRows_RsAgenda>0){
	$delete3SQL = sprintf("DELETE FROM cdt_agenda WHERE gic_ID=%u",
	GetSQLValueString($_GET['ID_gic'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result3 = mysqli_query($conn_cahier_de_texte, $delete3SQL) or die(mysqli_error($conn_cahier_de_texte));
	

	
	$delete5SQL = sprintf("DELETE FROM cdt_travail WHERE gic_ID=%u",
	GetSQLValueString($_GET['ID_gic'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result5 = mysqli_query($conn_cahier_de_texte, $delete5SQL) or die(mysqli_error($conn_cahier_de_texte));

//suppression fichiers joints	
	do {
			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$sql_f="SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=".$row_RsAgenda['ID_agenda'];
			$query_Rs_fichiers_joints_form = $sql_f;
			$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
			$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
			$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
			
			if ($totalRows_Rs_fichiers_joints_form <> '0'){ 
			
			do { 
                    // ne pas supprimer le fichier si utilise par ailleurs 
                    mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                    $ch_select="%_".strchr($row_Rs_fichiers_joints_form['nom_fichier'],'_');
                    $query_Recordset3 = sprintf("SELECT * FROM cdt_fichiers_joints WHERE nom_fichier like '%s' ",$ch_select );
					$Recordset3 = mysqli_query($conn_cahier_de_texte, $query_Recordset3) or die(mysqli_error($conn_cahier_de_texte));
					$row_Recordset3 = mysqli_fetch_assoc($Recordset3);
					$totalRows_Recordset3 = mysqli_num_rows($Recordset3);
					if ($totalRows_Recordset3==1){
						$fichier = '../fichiers_joints/'.$row_Recordset3['nom_fichier'];
						unlink($fichier);		
					}
					mysqli_free_result($Recordset3);
					
				} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
				mysqli_free_result($Rs_fichiers_joints_form);
				
				//on efface de la table fichiers_joints
				$deletefich = sprintf("DELETE FROM cdt_fichiers_joints WHERE agenda_ID=%u",
				GetSQLValueString($row_RsAgenda['ID_agenda'], "int"));
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$deletefich = mysqli_query($conn_cahier_de_texte, $deletefich) or die(mysqli_error($conn_cahier_de_texte));
				
		    }
		
		
	} while ($row_RsAgenda = mysqli_fetch_assoc($RsAgenda));

	

	
	if ( (isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui'))	{
	$deleteSQL_ele_gic = sprintf("DELETE FROM ele_gic WHERE ID_gic=%u",
	GetSQLValueString($_GET['ID_gic'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1_ele_gic = mysqli_query($conn_cahier_de_texte, $deleteSQL_ele_gic) or die(mysqli_error($conn_cahier_de_texte));
	} 
	} //delete contenus
	} //delete plage edt
	
	$deleteGoTo = "groupe_interclasses_ajout.php";
	
	header(sprintf("Location: %s", $deleteGoTo));
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
  <?php 
$header_description="Confirmation de la suppression d'un regroupement";
require_once "../templates/default/header.php";
?>
  <br />
  <div align="center">
  <fieldset style="width : 90%">
  <form method="post" name="form1" action="groupe_interclasses_supprime.php?ID_gic=<?php echo GetSQLValueString($_GET['ID_gic'], "int");?>">
    <p align="center"><img src="../images/exclamation.png" >&nbsp;
      Vous avez demand&eacute; la suppression du regroupement <strong><?php echo $row_Rsgic['nom_gic'];?></strong></p>
    <p align="left" class="erreur">
      <?php 

if  ($totalRows_RsEdt>0){echo $totalRows_RsEdt." plages de votre emploi du temps y font r&eacute;f&eacute;rence. <br />";
	if  ($totalRows_RsAgenda>0){echo $totalRows_RsAgenda." saisies ont &eacute;t&eacute; effectu&eacute;es en relation avec ces plages.<br /><br />";};	?>
    </p>
    <p align="center">La validation entrainera la suppression de ces plages de votre emploi du temps </p>
    <p align="center">
      <?php if  ($totalRows_RsAgenda>0){echo " ainsi que la suppression de vos saisies d&eacute;j&agrave; r&eacute;alis&eacute;es pour ces plages.";?>
    </p>
    <?php 
};
};
?>
    <p align="left">&nbsp;</p>
    <p>&nbsp;</p>
    <p>
      <input type="submit" value="Confirmer la suppression">
    </p>
    <input type="hidden" name="MM_update" value="form1">
  </form>
  </p>

  <p align="left">&nbsp;</p>
  <p align="center"><a href="groupe_interclasses_ajout.php">Annuler</a></p>
  <p>&nbsp; </p> 
  </fieldset>
  </div>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
