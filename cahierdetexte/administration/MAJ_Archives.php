<?php include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
?>
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>

<script language="JavaScript" type="text/JavaScript">

function vidage(){
	if (confirm("Confirmez-vous d\'avoir proc\351d\351 au vidage du CDT pendant l'archivage pr\351c\351dent ?")) {
		
		window.location.href='MAJ_Archives.php?vidage=1';
		
	};
	return;       		
}

</script>
</head>
<BODY>
<DIV id=page>
<?php 
$header_description="Correction de la mise en place de l'archivage dans le Cahier de Textes";
require_once "../templates/default/header.php";
?>

<?php
if ((isset($_GET['vidage']))&&($_GET['vidage']==1)) {
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);		 
	$sql0="TRUNCATE cdt_fichiers_joints";
	$result_sql_0=(mysqli_query($conn_cahier_de_texte, $sql0)) or die('Erreur SQL !'.$sql0.mysqli_error($conn_cahier_de_texte));
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_archives = "SELECT MAX(NumArchive) FROM `cdt_archive`";
	$archives = mysqli_query($conn_cahier_de_texte, $query_archives) or die(mysqli_error($conn_cahier_de_texte));
	$total_archives = mysqli_data_seek($archives,0,'MAX(NumArchive)');
	mysqli_free_result($archives);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);		 
	$sql1="SELECT ID_fichiers, agenda_ID FROM cdt_fichiers_joints_save$total_archives ORDER BY agenda_ID DESC LIMIT 1 ";
	$MaxAgenda=(mysqli_query($conn_cahier_de_texte, $sql1)) or die('Erreur SQL !'.$sql1.mysqli_error($conn_cahier_de_texte));
	$row_MaxAgenda = mysqli_fetch_assoc($MaxAgenda);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Insertion = sprintf("INSERT INTO cdt_fichiers_joints SELECT * FROM cdt_fichiers_joints_save$total_archives WHERE ID_fichiers>=%s", GetSQLValueString($row_MaxAgenda['ID_fichiers']+1,"int"));
	$Rs_Insertion = mysqli_query($conn_cahier_de_texte, $Insertion) or die(mysqli_error($conn_cahier_de_texte));
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Suppression = sprintf("DELETE FROM cdt_fichiers_joints_save$total_archives WHERE ID_fichiers>=%s", GetSQLValueString($row_MaxAgenda['ID_fichiers']+1,"int"));
	$Rs_Suppression = mysqli_query($conn_cahier_de_texte, $Suppression) or die(mysqli_error($conn_cahier_de_texte));
	
	mysqli_free_result($MaxAgenda);
	
	echo '<BR></BR>Mise &agrave; jour bien effectu&eacute;e<BR></BR>';
	
} else {
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_archives = "SELECT NumArchive FROM `cdt_archive`";
	$archives = mysqli_query($conn_cahier_de_texte, $query_archives) or die(mysqli_error($conn_cahier_de_texte));
	$total_archives = mysqli_num_rows($archives);
	if ($total_archives!=0){ 
		
		while ($row_archives = mysqli_fetch_array($archives)) {
			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_joints = "show tables";
			$joints = mysqli_query($conn_cahier_de_texte, $query_joints) or die(mysqli_error($conn_cahier_de_texte));
			
			
			while ($row_joints = mysqli_fetch_array($joints)) {
				if ($row_joints[0]=='cdt_agenda_save'.$row_archives['NumArchive']) {
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$query_RsArchiv10 = "CREATE TABLE IF NOT EXISTS cdt_fichiers_joints_save".$row_archives['NumArchive']." AS SELECT * FROM cdt_fichiers_joints";
					$ResultArchiv10 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv10);
					echo "<BR></BR>Cr&eacute;ation de la table manquante cdt_fichiers_joints_save".$row_archives['NumArchive'].".<BR></BR>"; 
					break;
				}
			}
			
		}
	}
	
	mysqli_free_result($archives);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$insertionSQL = "UPDATE `cdt_params` SET `param_val` = 'Non' WHERE `param_nom` ='Maj_Archives'";
	$Arch = mysqli_query($conn_cahier_de_texte, $insertionSQL) or die(mysqli_error($conn_cahier_de_texte));
	?>
	<table width="90%" align=center>
	    <td width="80%">
	Si lors de l'archivage, vous avez d&eacute;cid&eacute; de vider votre cahier de textes (comme il se doit en d&eacute;but d'ann&eacute;e), alors une derni&egrave;re
	modification est n&eacute;cessaire et il faut cliquer sur le bouton ci-dessous.
	<p align=center>
	<input type="button" name="lien" value="Appliquer une ultime modification" onClick="vidage();">
	</p>
	</td>
	</table>
<?php } ?>
<p>&nbsp;</p>
<p><a href="index.php">Retour au Menu Administrateur </a></p>
<DIV id=footer></DIV>
</div>
</body>
</html>


