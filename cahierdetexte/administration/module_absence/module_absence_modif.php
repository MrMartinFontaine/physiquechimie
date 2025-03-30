<?php
include "../../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');

$id_de_classe = $_POST["ID_Classe"];

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
/** Mise a jour du champ code_class */
if (!empty ($_POST["class_code"])) {
			$class_code = $_POST["class_code"] ;
			$sql = "UPDATE `cdt_classe` SET code_classe='".$class_code."' WHERE ID_classe='".$id_de_classe."'";
			$result = mysqli_query($conn_cahier_de_texte, $sql) or die(mysqli_error($conn_cahier_de_texte));
			$msg_maj_ok = "Le code de la classe a &eacute;t&eacute; mise &agrave; jour avec succ&egrave;s";
}

// Lecture des informations de la classe selon l'ID

$query_infoCodeClasse = "SELECT * FROM `cdt_classe` WHERE ID_classe='$id_de_classe'"; 
$infoCodeClasse = mysqli_query($conn_cahier_de_texte, $query_infoCodeClasse) or die(mysqli_error($conn_cahier_de_texte));
$infoCodeClasseRow = mysqli_fetch_array($infoCodeClasse, MYSQLI_ASSOC);
if ($infoCodeClasseRow) {
	$nom_de_classe = $infoCodeClasseRow['nom_classe'];
	$code_classe = $infoCodeClasseRow['code_classe'];
}

/** Selection des codes de classe dans ele_ **/
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_eleCodeClasse = "SELECT DISTINCT classe_ele FROM ele_liste ORDER BY classe_ele DESC"; 
$eleCodeClasse = mysqli_query($conn_cahier_de_texte, $query_eleCodeClasse) or die(mysqli_error($conn_cahier_de_texte));
$array_eleCodeClasse = array();
while ($row = mysqli_fetch_array($eleCodeClasse, MYSQLI_ASSOC)) {

	array_push($array_eleCodeClasse ,$row["classe_ele"]);
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
$header_description="D&eacute;claration des absences <br> Installation du module" ;
require_once "../../templates/default/header2.php";
?>
      <blockquote>

	<p align="center">Modification du code classe pour la classe : </p>
	<p align="center">Attention ces champs doivent correspondre aux noms de classe attribu&eacute;s aux &eacute;l&egrave;ves !</p>
	
	<?php   if ((isset($msg_maj_ok))&&($msg_maj_ok)) { echo  "<br /><b>$msg_maj_ok </b><br /><br />" ; } ?>

	<form method=post action="module_absence_modif.php">
		
		<table border="0" align="center">
		<tr>
			<td class="Style6">Nom de la classe</td>
			<td class="Style6">Code de la classe</td>
		</tr>
		<tr>
			<td class="tab_detail_gris"><div align="center"><?php echo $nom_de_classe ;?></div></td>
			<td class="tab_detail_gris">
				<select name="class_code">
					<?php
						foreach ($array_eleCodeClasse as $Code ) {
							echo "<option value='" .trim($Code)."' "  ;
							if ( $code_classe == trim($Code)) {  echo " selected "; }
							echo  ">". trim($Code)."</option>" ;
						}
					?>
				</select>
			</td>
		</tr>
	  </table>
	<br />
	<div align="center"><input type="submit" value="Modifier le code classe"></div>
	<input type="hidden"  name="ID_Classe"  value="<?php  echo $id_de_classe ;   ?>">
	</form>
	<p align="center">&nbsp;</p>
        <p align="left">&nbsp;</p>
      </blockquote>

	<p align="center"><a href="module_absence_sql1.php">Retour au Menu de remplissage des classes.</a>
	<p align="center"><a href="module_absence_install.php">Retour au Menu d'installation du module.</a>
	<p align="center"><a href="../index.php">Retour au Menu Administrateur</a>
	<p>&nbsp; </p>
<DIV id=footer></DIV>
</div>
</body>
</html>
