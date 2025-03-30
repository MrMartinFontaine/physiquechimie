<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes -<?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
  <?php 
$header_description="Statistiques de consultation par classe";
require_once "../templates/default/header.php";
?>
  <p align="left">&nbsp;</p>  
  <blockquote>
    <blockquote>
      <p align="left"><img src="../images/lightbulb.png" width="16" height="16">&nbsp;Des outils statistiques de consultation du cahier de textes pour chacune de vos classes peuvent &ecirc;tre mis en oeuvre.</p>
      <p align="left">Si vous ne disposez pas d&eacute;j&agrave; d'un tel outil, nous vous proposons d'utiliser <a href="http://fr.piwik.org/">PIWIK</a>.</p>
      <p align="left"><a href="http://fr.piwik.org/">PIWIK</a><em> </em>est un logiciel libre et open source de mesure de statistiques web. </p>
      <p align="left"> Votre application de cahier de textes a &eacute;t&eacute; d&eacute;velopp&eacute;e de mani&egrave;re &agrave; int&eacute;grer tr&egrave;s facilement cet outil.</p>
      <p align="left">Une fois <a href="http://fr.piwik.org/">PIWIK</a> install&eacute; sur un serveur, vous disposerez de deux param&egrave;tres :</p>
      <blockquote>
        <p align="left">- Une Url Piwik (<em>exemple : www.ac-XXXX.fr/piwik/</em>) </p>
        <p align="left">- Un Id de site Piwik (<em>un simple num&eacute;ro</em>) </p>
      </blockquote>
      <p align="left">Il vous suffira alors  de rentrer les deux param&egrave;tres ci-dessus dans <a href="parametrage_gen.php">les param&egrave;tres g&eacute;n&eacute;raux</a> de votre cahier de textes. </p>
      <p align="left"><em>Note aux utilisateurs avanc&eacute;s : <br>
      Plut&ocirc;t qu'un copier-coller d'un morceau de script au bas de votre page consulter.php, vous &eacute;vitez ainsi les oublis en cas de mise &agrave; jour du fichier consulter.php. </em></p>
    </blockquote>
  </blockquote>

  <p align="left">&nbsp;</p>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
