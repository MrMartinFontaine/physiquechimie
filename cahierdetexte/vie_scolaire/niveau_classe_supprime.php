<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)) { header("Location: ../index.php");exit;};

require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
if ((isset($_GET['ID_niv'])) && ($_GET['ID_niv'] != "")) {

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rsniv =sprintf("SELECT * FROM cdt_niveau WHERE ID_niv=%u", GetSQLValueString($_GET['ID_niv'], "int"));
	$Rsniv = mysqli_query($conn_cahier_de_texte, $query_Rsniv) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsniv = mysqli_fetch_assoc($Rsniv);
	$totalRows_Rsniv = mysqli_num_rows($Rsniv);

}

else {
	$no_deleteGoTo = "niveau_ajout.php";
	header(sprintf("Location: %s", $no_deleteGoTo));
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") && (isset($_GET['ID_niv']))) {

	
	$deleteSQL = sprintf("DELETE FROM cdt_niveau WHERE ID_niv=%u",	GetSQLValueString($_GET['ID_niv'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	
	$delete2SQL = sprintf("DELETE FROM cdt_niveau_classe WHERE niv_ID=%u",
	GetSQLValueString($_GET['ID_niv'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result2 = mysqli_query($conn_cahier_de_texte, $delete2SQL) or die(mysqli_error($conn_cahier_de_texte));
	

	
	$deleteGoTo = "niveau_classe_ajout.php";
	
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
$header_description="Confirmation de la suppression d'un niveau";
require_once "../templates/default/header.php";
?>
  <br />
  <div align="center">
  <fieldset style="width : 90%">
  <form method="post" name="form1" action="niveau_classe_supprime.php?ID_niv=<?php echo GetSQLValueString($_GET['ID_niv'], "int");?>">
    <p align="center"><img src="../images/exclamation.png" >&nbsp;
      Vous avez demand&eacute; la suppression du niveau <strong><?php echo $row_Rsniv['nom_niv'];?></strong></p>
    <p align="left" class="erreur">

    <p align="left">&nbsp;</p>
    <p>&nbsp;</p>
    <p>
      <input type="submit" value="Confirmer la suppression">
    </p>
    <input type="hidden" name="MM_update" value="form1">
  </form>
  </p>

  <p align="left">&nbsp;</p>
  <p align="center"><a href="niveau_classe_ajout.php">Annuler</a></p>
  <p>&nbsp; </p> 
  </fieldset>
  </div>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>

