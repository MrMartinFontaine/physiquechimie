<?php 
include "../authentification/authcheck.php"; 
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); ?>
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
$header_description="SECURITE";
require_once "../templates/default/header.php";
?>

  <blockquote>
    <blockquote>
      <blockquote>
        <p align="left">Les tables et les param&egrave;tres de connexion &eacute;tant maintenant install&eacute;s, il est conseill&eacute; pour des raisons de s&eacute;curit&eacute;, d'interdire l'acc&egrave;s au dossier &quot;<strong>install</strong>&quot;. Renommez-le ou supprimez-le.</p>
        <p align="left"> Les param&egrave;tres de connexion et le nom de l'&eacute;tablissement sont enregistr&eacute;s dans le fichier <strong>Connections/conn_cahier_de_texte.php</strong>. </p>
        <p align="left">Seul les dossiers <strong>fichiers_joints,fichiers_joints_message, exportation </strong> et <strong>RSS</strong> doivent poss&eacute;der des droits en &eacute;criture pour les utilisateurs. </p>
        <p align="left">Les autres  dossiers ne doivent pas poss&eacute;der des droits en &eacute;criture pour les utilisateurs (contr&ocirc;ler les droits avec votre outil FTP). </p>
        <p align="left">La connexion est perdue par d&eacute;faut au bout d'un certain temps d'inactivit&eacute;. Vous pouvez param&eacute;trer ce temps dans les <a href="parametrage_gen.php">param&egrave;tres g&eacute;n&eacute;raux de configuration</a>. </p>
        <p align="left">Les mots de passe profs et Administrateur sont crypt&eacute; . En cas de probl&egrave;me d'acc&egrave;s ou perte de mot de passe Administrateur, PhpMyAdmin est votre ami ;) </p>
        <p align="left">Si vous d&eacute;celez un probl&egrave;me de s&eacute;curit&eacute;, merci de m'en informer par mail. </p>
        <p align="left">Consultez r&eacute;guli&egrave;rement en ligne la page <a href="http://www.etab.ac-caen.fr/bsauveur/cahier_de_texte/">Mise &agrave; jour et correctifs.  </a></p>
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
