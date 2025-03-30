<?php include "../authentification/authcheck.php"; ?>
<?php if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - Contributions</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description='Contributions - Licence';
require_once "../templates/default/header.php";
?>
<p>Vous &ecirc;tes satisfaits de cette application et je suis tr&egrave;s heureux de vous  faire profiter de mon travail. <br>
</p>
<p>Mais avez vous respect&eacute; les termes de la licence ? (Comp&eacute;tence du B2I sur la propri&eacute;t&eacute; intellectuelle ;)) </p>
<p><br>
  <strong>La licence exclusive Chocolaware</strong> exige pour <strong>chaque utilisateur</strong> l'envoi de chocolats ou sp&eacute;cialit&eacute; r&eacute;gionale &agrave; l'auteur. </p>
<p>Adresse d'envoi</p>
<p>Pierre Lemaitre<br>
  324 Rue Ambroise Par&eacute;<br>
  50000 Saint-L&ocirc;</p>
<p align="center"><a href="index.php">Retour &agrave; l'accueil </a></p>
  <DIV id=footer>
</DIV></DIV></BODY></HTML>

