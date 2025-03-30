<?php
include "../../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');
require_once('module_absence_status.php');

if(isset($_GET['val']))
	{
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$choice = GetSQLValueString($_GET['val'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice WHERE `param_nom`='module_absence';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	}


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='module_absence' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access = $row[0];

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
$header_description="D&eacute;claration des absences - Installation du module" ;
require_once "../../templates/default/header.php";
?>

  <blockquote>
    <blockquote>
       
	
	<p align="center">&nbsp;</p>
	
	<p align="center">Bienvenue dans l'assistant d'installation et de gestion de votre module absence.<br>
	L'installation se d&eacute;roule en 3 &eacute;tapes. Une fois ces 3 &eacute;tapes complet&eacute;es,<br>
	vous pourrez activer le module et/ou apporter des modifications &agrave; l'installation. </p>
	
	<p align="center">&nbsp;</p>
	
	<table border="0" align="center">
		<tr>
			<td class="Style6">Etape</td>
			<td class="Style6">Action</td>
			<td class="Style6">Statut</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="tab_detail_gris"><div align="center">1/3</div></td>
			<?php 
				$nom_gic ="ele_gic";
				$nom_li = "ele_liste";
				$nom_ab = "ele_absent";
				$nom_pr = "ele_absent";
				
				$state_gic = exist_table($nom_gic,$conn_cahier_de_texte,$database_conn_cahier_de_texte);
				$state_li = exist_table($nom_li,$conn_cahier_de_texte,$database_conn_cahier_de_texte);
				$state_ab = exist_table($nom_ab,$conn_cahier_de_texte,$database_conn_cahier_de_texte);
				$state_pr = exist_table($nom_pr,$conn_cahier_de_texte,$database_conn_cahier_de_texte);
				   
									if (($state_li == 1)&&($state_ab == 1)&&($state_gic == 1)&&($state_pr == 1)){
										$sub_state = 1;
										echo '<td class="tab_detail_gris">Pr&eacute;paration de la base de donn&eacute;es</td>';
										echo '<td class="tab_detail_gris"><img align="center" alt="Etape termin&eacute;e" title="Etape termin&eacute;e" src="./img/accept.png"></td>';										
									} elseif  (($state_li == 0)||($state_ab == 0)||($state_gic == 0) ||($state_pr == 0)){
										echo '<td class="tab_detail_gris">Le module nécessite dans la base des tables spécifiques :<br> ele_liste, ele_absent, ele_present, ele_gic.<br>Certaines tables sont manquantes.<a href="module_absence_sql.php"> <br><br>Cliquer pour corriger et créer ces tables.</a></td>';
										echo '<td class="tab_detail_gris"><img align="center" alt="Cette action requiert votre attention" title="Cette action requiert votre attention" src="./img/exclamation.png">';
										$sub_state = 0;
									}else {
										echo '<td class="tab_detail_gris"><a href="module_absence_sql.php">Pr&eacute;paration de la base de donn&eacute;es</a></td>';
										echo '<td class="tab_detail_gris"><img align="center" alt="Cette action requiert votre attention" title="Cette action requiert votre attention" src="./img/exclamation.png">';
										$sub_state = 0;
									}
									$state_0 = $sub_state;
			?>
		</tr>
		<?php 
		if ($state_0==1) {?>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="tab_detail_gris"><div align="center">2/3</div></td>
			<?php $state = content_table("ele_liste",$conn_cahier_de_texte,$database_conn_cahier_de_texte);
									if ($state == 1){
										echo '<td class="tab_detail_gris"><a href="import_absence_eleves_csv.php">Utiliser un autre fichier pour compl&eacute;ter la liste.</a></td>';
										echo '<td class="tab_detail_gris"><img align="center" alt="Etape termin&eacute;e" title="Etape termin&eacute;e" src="./img/accept.png"></td>';										
									} else { 
										echo '<td class="tab_detail_gris"><a href="import_absence_eleves_csv.php">Importation des &eacute;l&egrave;ves depuis un fichier CSV.</a></td>';
										echo '<td class="tab_detail_gris"><img align="center" alt="Cette action requiert votre attention" title="Cette action requiert votre attention" src="./img/exclamation.png">';
									}
									$state_1 = $state;
			?>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>	
		<tr>
		<td class="tab_detail_gris"><div align="center">3/3</div></td>
			<?php $state = content_column("cdt_classe", "code_classe",$conn_cahier_de_texte,$database_conn_cahier_de_texte);
									if ($state == 1){
										echo '<td class="tab_detail_gris"><a href="module_absence_sql1.php">Vous pouvez modifier les associations  Nom de la classe /Code classe import&eacute;.</a></td>';
										echo '<td class="tab_detail_gris"><img align="center" alt="Etape termin&eacute;e" title="Etape termin&eacute;e" src="./img/accept.png"></td>';										
									} else { 
										echo '<td class="tab_detail_gris"><a href="module_absence_sql1.php">Remplissage des champs de code des classes.</a></td>';
										echo '<td class="tab_detail_gris"><img align="center" alt="Cette action requiert votre attention" title="Cette action requiert votre attention" src="./img/exclamation.png">';
									}
									$state_2 = $state;
									
			?>
		</tr>
				<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	
<?php 	


};


	if(($state_0 == 1)&&($state_1 == 1)&&($state_2 == 1)){
		$message = '<p align="center"> L\'installation est maintenant termin&eacute;e.<br>
		Vous pouvez activer ou d&eacute;sactiver le module</p>';
		if ($access =='Oui'){
			$mess = 'Le module d&eacute;claration des absences est activ&eacute;';
			echo '<tr><td class="tab_detail_gris"><div align="center">4/4</div></td>';
			echo '<td class="tab_detail_gris"><div><a href="./module_absence_install.php?val=Non">Cliquez ici pour d&eacute;sactiver le module.</a></div></td>';
			echo '<td class="tab_detail_gris"><img align="center" alt="Desactivation possible" title="D&eacute;sactivation possible" src="./img/lightbulb.png"></td></tr>';
			} else { 
			$mess = 'Le module est desactiv&eacute;';
			echo '<tr><td class="tab_detail_gris"><div align="center">4/4</div></td>';
			echo '<td class="tab_detail_gris"><div><a href="./module_absence_install.php?val=Oui">Cliquez ici pour activer le module.</a></div></td>';
			echo '<td class="tab_detail_gris"><img align="center" alt="Activation possible" title="Activation possible" src="./img/lightbulb_off.png"></td></tr>';
			}  
	}	
?>       
</table> 
	<p align="center">&nbsp;</p>
	<p align="center">&nbsp;</p>     
<?php if ($state_0==1) {?>	
	<p align="center">--------------</p>
	  
<?php if(isset($mess)){echo $mess;};
if(isset($message)){echo $message;};?>

	<p align="center">--------------</p>
	
	<p align="center">&nbsp;</p>
	<p align="center">&nbsp;</p>

		<p align="center"><a href="module_absence_suppr.php">Diagnostic et suppression totale ou partielle des donn&eacute;es du module (Utilisateur avanc&eacute;)</a></p> 
<?php };?>
	<p align="center">&nbsp;</p>
    <p align="left">&nbsp;</p>
    </blockquote>
  </blockquote>
  </blockquote>
  <p align="center"><a href="module_absence.php">Retour au Menu Module 
  absence</a></p>
  <p align="center"><a href="../index.php">Retour &agrave; l'espace Administration  (Saisie des Enseignants, mati&egrave;res, classes... )</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>

