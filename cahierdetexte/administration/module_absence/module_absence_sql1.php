<?php
include "../../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');

if (isset($_POST["code_class"])){$form_array = $_POST["code_class"];};

/** Peuplement des champs code_class */

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	
if (!empty ($_POST["code_class"]))
{
	foreach ($form_array as $class_id => $class_code) 
		if (($class_code) !== "")
		{
			$sql = "UPDATE `cdt_classe` SET code_classe='$class_code' WHERE ID_classe='$class_id'";
			$result = mysqli_query($conn_cahier_de_texte, $sql) or die(mysqli_error($conn_cahier_de_texte));
		}		
} 

/** Selection des noms de classes **/

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe DESC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);

/** Selection des codes de classe dans ele_ **/

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_CodeClasse = "SELECT DISTINCT classe_ele FROM ele_liste ORDER BY classe_ele DESC"; 
$CodeClasse = mysqli_query($conn_cahier_de_texte, $query_CodeClasse) or die(mysqli_error($conn_cahier_de_texte));
$array_CodeClasse = array();
$array_CodeClasse[-1] = "";
while ($row = mysqli_fetch_array($CodeClasse, MYSQLI_ASSOC)) {
	array_push($array_CodeClasse ,$row["classe_ele"]);
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
function editerClasse(id_classe) { 
	var form = document.getElementById('modif_id_classe'); 
	var hidden = document.getElementById('ID_Classe'); 	
	form.action = "module_absence_modif.php";
	hidden.value = id_classe;	
	form.submit();	
}
//-->
</script> 

<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php
$header_description="D&eacute;claration des absences <br> Installation du module" ;
require_once "../../templates/default/header2.php";
?>
      <blockquote>

	<p align="center">Vous g&eacute;rez ici l'association entre le nom de la classe d&eacute;j&agrave; pr&eacute;sent dans votre cahier de textes <br>
	  et le code de la classe import&eacute; avec la liste des &eacute;l&egrave;ves. </p>
	<p align="center">&nbsp;</p>	

	<form method="post"  name="modif_id_classe"  id="modif_id_classe"  action="module_absence_sql1.php">
		
		<table border="0" align="center">
		<tr>
			<td class="Style6">Nom de la classe dans le CDT&nbsp;</td>
			<td class="Style6">Code de la classe import&eacute;&nbsp; </td>
			<td class="Style6">Action&nbsp;</td>
		</tr>
	<?php do { ?>
		<tr>
			<td class="tab_detail_gris"><div align="center"><?php echo $row_RsClasse['nom_classe']; ?></div></td>

						<?php if ($row_RsClasse['code_classe'] == "") 
				{
					foreach ($array_CodeClasse as $Code ) {
						$select .= '<option value="' .$Code. '">'. $Code .' </option>' ;
					}

					$code_classe_vide = '<td class="tab_detail_gris"><select name="code_class['.$row_RsClasse['ID_classe'] .']">' . $select  .'</select></td><td class="tab_detail_gris"><img align="center" src="./img/exclamation.png" alt="Aucune action" title="Aucune action"></td>';
					echo $code_classe_vide;
				} else {
					$code_classe_plein = '<td class="tab_detail_gris"><div align="center">'. $row_RsClasse['code_classe'] .'</div></td>';
					$code_classe_plein .= '<td class="tab_detail_gris">';
					$code_classe_plein .= '<img onclick="editerClasse(\''. $row_RsClasse['ID_classe']  .'\');" src="./img/page_edit.png" alt="Editer la classe" title="Editer la classe"></td>';

					echo $code_classe_plein;
				}
$select = "";
			?>
		</tr>
      	<?php } while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)); ?>
		</table>
	<br />
	<div align="center"><input type="submit" value="Mettre &agrave; jour"></div>
	<input type="hidden" name="ID_Classe" id="ID_Classe" value="">
	</form>
	
	
	<p align="center">&nbsp;</p>
        <p align="left">&nbsp;</p>
      </blockquote>

	<p align="center"><a href="module_absence_install.php">Retour au Menu d'installation du module.</a>
	<p align="center"><a href="module_absence.php">Retour au Menu Module 
  absence</a>
	<p align="center"><a href="../index.php">Retour au Menu Administrateur</a>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</div>
</body>
</html>
