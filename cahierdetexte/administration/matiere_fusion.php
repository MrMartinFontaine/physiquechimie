<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');

if ((isset($_POST['matiere']))&&($_POST['matiere']>0)&&(isset($_POST['matiere2']))&&($_POST['matiere2']>0)) {
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
	$query = sprintf("UPDATE `cdt_agenda` SET `matiere_ID`=%u WHERE `matiere_ID`=%u",$_POST['matiere'],$_POST['matiere2']);
	$Rs = mysqli_query($conn_cahier_de_texte, $query);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
	$query = sprintf("UPDATE `cdt_edt` SET `matiere_ID`=%u WHERE `matiere_ID`=%u",$_POST['matiere'],$_POST['matiere2']);
	$Rs = mysqli_query($conn_cahier_de_texte, $query);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
	$query = sprintf("UPDATE `cdt_emploi_du_temps` SET `matiere_ID`=%u WHERE `matiere_ID`=%u",$_POST['matiere'],$_POST['matiere2']);
	$Rs = mysqli_query($conn_cahier_de_texte, $query);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
	$query = sprintf("UPDATE `cdt_invite` SET `matiere_ID`=%u WHERE `matiere_ID`=%u",$_POST['matiere'],$_POST['matiere2']);
	$Rs = mysqli_query($conn_cahier_de_texte, $query);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
	$query = sprintf("UPDATE `cdt_travail` SET `matiere_ID`=%u WHERE `matiere_ID`=%u",$_POST['matiere'],$_POST['matiere2']);
	$Rs = mysqli_query($conn_cahier_de_texte, $query);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
	$query = sprintf("DELETE FROM `cdt_matiere` WHERE `ID_matiere` = %u LIMIT 1",$_POST['matiere2']);
	$Rs = mysqli_query($conn_cahier_de_texte, $query);
	
	header("Location:matiere_ajout.php");
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"></script>
<script type="text/javascript" src="../jscripts/jquery.jCombo.min.js"></script>
<script type="text/JavaScript">

$(function() {
		$("#matiere").jCombo("matiereafusionner1.php",{
				initial_text: "S&eacute;lectionnez une mati&egrave;re"
		});
		$("#matiere2").jCombo("matiereafusionner2.php?id=", { parent: "#matiere",
				initial_text: "S&eacute;lectionnez une mati&egrave;re"
		});
});

function Activ_Submit()
{	
	sel = document.getElementById('matiere');
	mat = sel.options[sel.selectedIndex].value;  
	sel2 = document.getElementById('matiere2');
	mat2 = sel2.options[sel.selectedIndex].value;  
	if((mat>0)&&(mat2>0)){
		document.formmat.Submit.disabled=false;
	}else{
		document.formmat.Submit.disabled=true;
	};
};
</script>

</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Fusionner deux mati&egrave;res";
require_once "../templates/default/header.php";
?>
<blockquote>
<blockquote>
<blockquote>
<fieldset style="width : 100%">
<p align="left">Il peut &ecirc;tre utile de fusionner deux mati&egrave;res lorsque celles-ci ont d&eacute;j&agrave; &eacute;t&eacute; utilis&eacute;es alors qu'elles d&eacute;signent en fait 
une m&ecirc;me mati&egrave;re. (Exemple : Techno et Technologie).</p>
<p align="left">Il faut choisir une premi&egrave;re mati&egrave;re puis ensuite une seconde mati&egrave;re qui fusionneront sous le nom de la premi&egrave;re choisie.</p>

<p><form name="formmat" method="post">
Mati&egrave;re 1 : <select name="matiere" id="matiere" OnChange="Activ_Submit();"></select>
<br/>
<br/>
Mati&egrave;re 2 : <select name="matiere2" id="matiere2" OnChange="Activ_Submit();"></select>
<br/>
<br/>
<input type="submit" name="Submit"  disabled="disabled" value="Fusionner ces deux mati&egrave;res">
</form></p>
</fieldset>
<p align="left">&nbsp;</p>
</blockquote>
</blockquote>
</blockquote>
<p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
<p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
