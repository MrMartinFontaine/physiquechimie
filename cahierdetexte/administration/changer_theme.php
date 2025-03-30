<?php include "../authentification/authcheck.php"; ?>
<?php if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};?>
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
$header_description="Changer de th&egrave;me de pr&eacute;sentation";
require_once "../templates/default/header.php";
?>
<HR>
<blockquote>
  <blockquote>
    <p align="left">Les th&egrave;mes sont relatifs &agrave; la pr&eacute;sentation des menus (page d'accueil par exemple)</p>
    <p align="left">Pour installer un th&egrave;me, copier tous ses fichiers dans le dossier <strong>templates/defaut/</strong><br>
      Le theme install&eacute; par d&eacute;faut est <strong>original_bleu_avec_flash</strong><br>
    Ainsi, en copiant les fichiers du dossier templates/simple_GlossyBlue dans le dossier templates/default, vous intallerez le mod&egrave;le simple_GlossyBlue.</p>
    <p align="left">Le th&egrave;me utilise la feuille de style <strong>header_footer.css</strong> dans laquelle vous retrouverez :</p>
    <p align="left">- soit l'appel &agrave; une couleur (exemple background-color: white;)<br>
      - soit l'appel &agrave; une image d'arri&egrave;re-plan pr&eacute;sente dans le dossier du mod&egrave;le(exemple 	background: url(cdt_bg.jpg) <br>
    </p>
    <p align="left">Voici les 4 principaux &eacute;l&eacute;ments relatifs &agrave; la pr&eacute;sentation et pr&eacute;sents dans le fichier header_footer.css :</p>
    <p align="left">1) Couleur ou image autour du menu<br>
      BODY {	background: .......}</p>
    <p align="left">2) Couleur ou image d'arri&egrave;re plan du bandeau de titre Cahier de textes<br>
      #header { background: ...... }</p>
    <p align="left">3) Couleur ou image d'arri&egrave;re plan centre de la page <br>
      #page {	background: ..........) }</p>
    <p align="left">4) Couleur ou image d'arri&egrave;re plan du bandeau de pied de page avec le nom de l'auteur et version<br>
      #footer { background: ........ }<br>
    </p>
  </blockquote>
  <p align="center">&nbsp;</p>
  <p align="center"><em>Exemple de th&egrave;mes presents dans le dossier templates </em></p>
</blockquote>
<p align="center"><img src="../templates/original_bleu_avec_flash/original_avec_flash.jpg" width="700" height="347"></p>
<p align="center"><strong>Original_avec_Flash</strong></p>
<p align="center">&nbsp;</p>
<p align="center"><img src="../templates/original_bleu_sans_flash/original_sans_flash.jpg" width="700" height="345"></p>
<p align="center"><strong>Original_sans_Flash</strong></p>
<p align="center">&nbsp;</p>
<p align="center"><img src="../templates/simple_GlossyBlue/simple_Gglossyblue.jpg" width="700" height="345"></p>
<p align="center"><strong>Simple_Glossyblue</strong></p>
<p></p><br>
<p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>

  <DIV id=footer></DIV>

</body>
</html>

