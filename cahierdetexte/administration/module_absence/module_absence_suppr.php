<?php
include "../../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

if (!empty ($_POST["watodo"])) {
		
	$watodo = $_POST["watodo"];

	if ($watodo == 'suppr'){
		t_drop($_POST["wertodo"],$conn_cahier_de_texte);
	} 
	elseif ($watodo == 'empty'){
		t_truncate($_POST["wertodo"],$conn_cahier_de_texte);
	}
	else {
		echo "Une erreur est survenue. Merci de contacter l'administrateur du site";
	}
}
		
function t_truncate($t_table, $conn_cahier_de_texte){
	$query = "TRUNCATE TABLE `$t_table`";
	$CodeClasse = mysqli_query($conn_cahier_de_texte, $query) or die(mysqli_error($conn_cahier_de_texte)); 
	if(mysqli_error($conn_cahier_de_texte)) { 
			$CodeClasse = "0";
			} else {
			$CodeClasse = "1";
			}
}

function t_drop($d_table, $conn_cahier_de_texte){
	$query = "DROP TABLE `$d_table`";
	$CodeClasse = mysqli_query($conn_cahier_de_texte, $query) or die(mysqli_error($conn_cahier_de_texte));
	if(mysqli_error($conn_cahier_de_texte)) { 
			$CodeClasse = "0";
			} else {
			$CodeClasse = "1";
			}
}

function t_update($u_table, $conn_cahier_de_texte){
	$query = "UPDATE `cdt_classe` SET `code_classe`=\"\"";
		$CodeClasse = mysqli_query($conn_cahier_de_texte, $query) or die(mysqli_error($conn_cahier_de_texte));
if(mysqli_error($conn_cahier_de_texte)) { 
			$CodeClasse = "0";
			} else {
			$CodeClasse = "1";
			}
}

function t_check($table, $conn_cahier_de_texte){
$query = "SELECT * FROM $table LIMIT 1"; 
$verif_cont = mysqli_query($conn_cahier_de_texte, $query);
$row_verif_cont = mysqli_num_rows($verif_cont);
	if ($row_verif_cont == 1) {
		echo "La table <b>$table</b> contient des donn&eacute;es.<br>";
	} else {
		echo "Il semble que la table <b>$table</b> est vide.<br>";
	}
return true;
}

function t_infos($table, $conn_cahier_de_texte){
global $tab_exist;
$tab_exist=1;
$query = "SHOW TABLE STATUS LIKE '$table'"; 
$verif_infos = mysqli_query($conn_cahier_de_texte, $query);
$row_verif_infos = mysqli_fetch_array($verif_infos);
if ($row_verif_infos['Create_time'] == "") {
	echo "La table \" " .$table. " \" n'existe pas.";$tab_exist=0;
} else {
	echo "Table cr&eacute;e le :" .$row_verif_infos['Create_time'];
}
return true;
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php
$header_description="D&eacute;claration des absences <br> Remise &agrave; z&eacute;ro  des tables" ;
require_once "../../templates/default/header2.php";
?>

  <blockquote>
    <blockquote>
      <blockquote>
<!-- On affiche le resultat de la commande-->

	<?php 	
	if (isset($CodeClasse)){
	if ($CodeClasse == "0") {
			echo 'Echec de la suppression, veuillez recommencer l\'op&eacute;ration.';
		} 
		elseif ($CodeClasse == "1")
		{
			echo 'Suppression r&eacute;ussie.';
		}
	}	
	?>
	<!--ELE LISTE -->	<p align="center">&nbsp;</p>
	
	<fieldset style="background: #EEEEEE ;">
	<legend align="center" STYLE="color: #3333DD ;"><b>TABLE "ele_liste"</b></legend>
	
	<p align="center"><?php t_infos("ele_liste",$conn_cahier_de_texte);?></p>
	<?php if ($tab_exist<>0){ ?>
		<form method="post" name="form_drop_table" action="module_absence_suppr.php">
		<input type=hidden name="watodo" value="suppr">
		<input type=hidden name="wertodo" value="ele_liste">
		<div valign="center" align="center">Suppression de la table.&nbsp;<input type="image" alt="Supprimer" title="Supprimer" src="./img/table_delete.png"/></div>
		</form>	
		
		<p align="center">&nbsp;</p>	
		
		<p align="center"><?php t_check("ele_liste",$conn_cahier_de_texte);?></p>
		<form method="post" name="form_table" action="module_absence_suppr.php">
		<input type=hidden name="watodo" value="empty">
		<input type=hidden name="wertodo" value="ele_liste">
		<div valign="center" align="center">Suppression des donn&eacute;es &agrave; l'interieur de la table.&nbsp;<input type="image" alt="Supprimer" title="Supprimer" src="./img/table_delete.png"/></div>
		</form>
	<?php };?>	

	</fieldset>
	
	
	

	
	
	<!--ELE ABSENT -->		<p align="center">--------</p>
	
	<fieldset style="background: #EEEEEE ;">
	<legend align="center" STYLE="color: #3333DD ;"><b>TABLE "ele_absent"</b></legend>	
	<p align="center"><?php t_infos("ele_absent",$conn_cahier_de_texte);?></p>
	
	<?php if ($tab_exist<>0){ ?>
		<form method="post" name="form_drop_table_0" action="module_absence_suppr.php">
		<input type=hidden name="watodo" value="suppr">
		<input type=hidden name="wertodo" value="ele_absent">
		<div valign="center" align="center">Suppression de la table.&nbsp;<input type="image" alt="Supprimer" title="Supprimer" src="./img/table_delete.png"/></div>
		</form>
	
		<p align="center">&nbsp;</p>
		<p align="center"><?php t_check("ele_absent",$conn_cahier_de_texte);?></p>
		<form method="post" name="form_table_0" action="module_absence_suppr.php">
		<input type=hidden name="watodo" value="empty">
		<input type=hidden name="wertodo" value="ele_absent">
		<div valign="center" align="center">Suppression des donn&eacute;es &agrave; l'interieur de la table.&nbsp;<input type="image" alt="Supprimer" title="Supprimer" src="./img/table_delete.png"/></div>
	</form>
	<?php };?>

	</fieldset>
	
	
	<!--ELE GIC -->	<p align="center">--------</p>
	
	<fieldset style="background: #EEEEEE ;">
	<legend align="center" STYLE="color: #3333DD ;"><b>TABLE "ele_gic"</b></legend>
	
	<p align="center"><?php t_infos("ele_gic",$conn_cahier_de_texte);?></p>
	<?php if ($tab_exist<>0){ ?>
		<form method="post" name="form_drop_table_0" action="module_absence_suppr.php">
		<input type=hidden name="watodo" value="suppr">
		<input type=hidden name="wertodo" value="ele_gic">
		<div valign="center" align="center">Suppression de la table.&nbsp;<input type="image" alt="Supprimer" title="Supprimer" src="./img/table_delete.png"/></div>
		</form>
		<p align="center">&nbsp;</p>
		<p align="center"><?php t_check("ele_gic",$conn_cahier_de_texte);?></p>
		<form method="post" name="form_table_0" action="module_absence_suppr.php">
		<input type=hidden name="watodo" value="empty">
		<input type=hidden name="wertodo" value="ele_gic">
		<div valign="center" align="center">Suppression des donn&eacute;es &agrave; l'interieur de la table.&nbsp;<input type="image" alt="Supprimer" title="Supprimer" src="./img/table_delete.png"/></div>
		</form>
	
	<?php };?>
	
	</fieldset>
	
	<!--ELE PRESENT -->	<p align="center">--------</p>
	
	<fieldset style="background: #EEEEEE ;">
	<legend align="center" STYLE="color: #3333DD ;"><b>TABLE "ele_present"</b></legend>
	
	<p align="center"><?php t_infos("ele_present",$conn_cahier_de_texte);?></p>
	<?php if ($tab_exist<>0){ ?>
		<form method="post" name="form_drop_table_0" action="module_absence_suppr.php">
		<input type=hidden name="watodo" value="suppr">
		<input type=hidden name="wertodo" value="ele_present">
		<div valign="center" align="center">Suppression de la table.&nbsp;<input type="image" alt="Supprimer" title="Supprimer" src="./img/table_delete.png"/></div>
		</form>	
		
		<p align="center">&nbsp;</p>
		
		<p align="center"><?php t_check("ele_present",$conn_cahier_de_texte);?></p>
		<form method="post" name="form_table_0" action="module_absence_suppr.php">
		<input type=hidden name="watodo" value="empty">
		<input type=hidden name="wertodo" value="ele_present">
		<div valign="center" align="center">Suppression des donn&eacute;es &agrave; l'interieur de la table.&nbsp;<input type="image" alt="Supprimer" title="Supprimer" src="./img/table_delete.png"/></div>
		</form>
	<?php };?>
	</fieldset>
	
	
	<!--COLONNE CODE_CLASSE --><p align="center">--------</p>
	<fieldset style="background: #EEEEEE ;">
	<legend align="center" STYLE="color: #3333DD ;"><b>TABLE "cdt_classe" COLONNE "code_class"</b></legend>
	<form method="post" name="form_table_1" action="module_absence_suppr.php">
	<input type=hidden name="table_1" value="cdt_classe">
	<div valign="center" align="center">Suppression des donn&eacute;es &agrave; l'interieur de la colonne.&nbsp;<input type="image" alt="Supprimer" title="Supprimer" src="./img/tab_delete.png"/></div>
	
	</form>
	</fieldset>
	
	
	<p align="center">&nbsp;</p>
	<p align="left">&nbsp;</p>
      </blockquote>
    </blockquote>
  </blockquote>
  <p align="center"><a href="module_absence_install.php">Retour au menu d'installation du module.</a>
	<p align="center"><a href="../index.php">Retour &agrave; l'espace Administration  (Saisie des Enseignants, mati&egrave;res, classes... )</a>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
