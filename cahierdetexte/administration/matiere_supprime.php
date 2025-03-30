<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
if ((isset($_GET['ID_matiere'])) && ($_GET['ID_matiere'] != "")&& (isset($_POST['MM_supprime']))) {
	
	$deleteSQL = sprintf("DELETE FROM cdt_matiere WHERE ID_matiere=%u",GetSQLValueString($_GET['ID_matiere'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$deleteSQL = sprintf("DELETE FROM cdt_emploi_du_temps WHERE matiere_ID=%u",GetSQLValueString($_GET['ID_matiere'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$deleteSQL = sprintf("DELETE FROM cdt_agenda WHERE matiere_ID=%u",GetSQLValueString($_GET['ID_matiere'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$deleteSQL = sprintf("DELETE FROM cdt_travail WHERE matiere_ID=%u",GetSQLValueString($_GET['ID_matiere'], "int"));
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$deleteGoTo = "matiere_ajout.php";
	if (isset($_SERVER['QUERY_STRING'])) {
		$deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
		$deleteGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $deleteGoTo));
};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsUtilisateurMatiere = sprintf("SELECT DISTINCT ID_prof,nom_prof,identite FROM cdt_emploi_du_temps, cdt_prof WHERE ID_prof=prof_ID AND matiere_ID= %u ORDER BY nom_prof ASC",$_GET['ID_matiere']);
$RsUtilisateurMatiere = mysqli_query($conn_cahier_de_texte, $query_RsUtilisateurMatiere) or die(mysqli_error($conn_cahier_de_texte));
$row_RsUtilisateurMatiere = mysqli_fetch_assoc($RsUtilisateurMatiere);
$totalRows_RsUtilisateurMatiere = mysqli_num_rows($RsUtilisateurMatiere);
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
$header_description="Suppression d'une mati&egrave;re";
require_once "../templates/default/header.php";


?>
<HR>
<form method="post" name="form1" action="<?php echo $editFormAction; ?>">
<p>Vous avez demand&eacute; la suppression de la mati&egrave;re </p>
<p><strong><?php echo $_GET['nom_matiere']?></strong></p>
<blockquote>
<blockquote>
<?php
if ($totalRows_RsUtilisateurMatiere>0){
	?>
        <p align="left" class="erreur">Attention, cette mati&egrave;re a &eacute;t&eacute; int&eacute;gr&eacute;e dans les emplois du temps<br>
        des professeurs et peut faire l'objet de saisies dans le cahier de textes.<br>
        <br>
        Confirmer entrainera la suppression des diff&eacute;rentes entr&eacute;es li&eacute;es &agrave; cette mati&egrave;re.
        <br>
        Il peut &ecirc;tre plus pertinent de fusionner plut&ocirc;t deux mati&egrave;res avec le lien ad&eacute;quat.
        </p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p align="center"><strong>Etat des saisies li&eacute;es &agrave; cette mati&egrave;re </strong>
        <table border="0" align="center" width ="60 %">
        <tr class="Style6">
        <td>Enseignants</td>
        <td>Nombre de saisies</td>
        </tr>
        <?php
        do {
		
        	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        	$query_RsAgendaMatiere = sprintf("SELECT * FROM cdt_agenda WHERE prof_ID=%u AND matiere_ID= %u",$row_RsUtilisateurMatiere['ID_prof'],$_GET['ID_matiere']);
        	$RsAgendaMatiere = mysqli_query($conn_cahier_de_texte, $query_RsAgendaMatiere) or die(mysqli_error($conn_cahier_de_texte));
        	$row_RsAgendaMatiere = mysqli_fetch_assoc($RsAgendaMatiere);
        	$totalRows_RsAgendaMatiere = mysqli_num_rows($RsAgendaMatiere);
        	
        	?>
        	<tr>
        	<td class="tab_detail_gris">
        	<?php
        	if ($row_RsUtilisateurMatiere['identite']<>''){echo $row_RsUtilisateurMatiere['identite'];
        	} 
        	else {
			echo $row_RsUtilisateurMatiere['nom_prof'];
		};
		?></td>
		<td class="tab_detail_gris"><?php echo $totalRows_RsAgendaMatiere;?></td>
		</tr>
		<?php
		
	} while ($row_RsUtilisateurMatiere = mysqli_fetch_assoc($RsUtilisateurMatiere));
	
	
	
	mysqli_free_result($RsAgendaMatiere);
	mysqli_free_result($RsUtilisateurMatiere);?>
        </table>
        <?php 
}
else {?>
        <p align="center">Aucune entr&eacute;e dans la base ne fait r&eacute;f&eacute;rence &agrave; cette mati&egrave;re.<br>
        Vous pouvez donc la supprimer.</p>
<?php	}		?>
<p>&nbsp;</p>
</blockquote>
</blockquote>
<p>
<input type="submit" name="Submit" value="Confirmer la suppression">
</p>
<p>&nbsp;</p>
<p>
<input type="hidden" name="MM_supprime" value="form1">
<input type="hidden" name="ID_matiere" value="<?php echo $_GET['ID_matiere']?>">
</p>
</form>
<p>&nbsp;</p>
<p align="center"><a href="matiere_ajout.php">Annuler</a></p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
