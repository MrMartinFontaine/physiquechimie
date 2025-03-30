<?php 
//session_start();
include "../authentification/authcheck.php";
require_once('../Connections/conn_cahier_de_texte.php'); 
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)){ header("Location: ../index.php");exit;};
// construction url

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProf = sprintf("SELECT passe FROM cdt_prof WHERE cdt_prof.ID_prof=%u", $_SESSION['ID_prof']);
$RsProf = mysqli_query($conn_cahier_de_texte,$query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);
if(isset($_SERVER['SCRIPT_URI'])){
$lien=str_replace("enseignant/smart_appel_lien.php","vie_scolaire/smart_appel.php",$_SERVER["SCRIPT_URI"])."?id=".$_SESSION['ID_prof']."&pw=".substr($row_RsProf["passe"],0,20);
} else {
$lien="......../vie_scolaire/smart_appel.php?id=".$_SESSION['ID_prof']."&pw=".substr($row_RsProf["passe"],0,20);
};

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<style type="text/css">
a img {	border: none;}
</style>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<p>
  <?php 
$header_description="Lien pour Appel sur mobile";
require_once "../templates/default/header.php";
?>
  </p>
<p align="center">&nbsp;</p>
<p align="center">&nbsp;</p>
<p align="center">Vous avez la possibilit&eacute; de saisir tr&egrave;s rapidement vos absences en utilisant votre smartphone. <br>
  Il suffit de saisir sur celui-ci l'adresse ci-dessous (copier / coller ce sera plus simple) <br>
  puis par exemple la mettre dans vos favoris<br>
  ou  mieux encore disposer d'une icone sur la page d'accueil de  votre smartphone pointant vers ce lien. </p>
<p align="center">&nbsp;</p>
<p align="center"><strong> <a href="<?php echo $lien;?>"><?php echo $lien;?></a></strong></p>
<p align="center">Ce lien vous est personnel et ne doit pas &ecirc;tre diffus&eacute;.</p>
<p align="center">L'application d&eacute;tecte imm&eacute;diatement les &eacute;l&egrave;ves correspondant &agrave; votre  plage horaire de cours.  <br>
  Elle compare l'heure actuelle avec l'heure de d&eacute;but de cours que vous avez d&eacute;fini pour chaque plage dans votre emploi du temps.<br>
  Il y a peut-&ecirc;tre lieu de retourner voir ces plages et d'ajuster pr&eacute;cis&eacute;ment l'heure de d&eacute;but de cours <br>
  si vous d&eacute;sirez faire l'appel pendant la toute premi&egrave;re minute de cours ! <br>
</p>
<p align="center"> <a href="enseignant.php">Retour au Menu Enseignant</a></p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
