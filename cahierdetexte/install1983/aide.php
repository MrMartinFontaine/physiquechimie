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
$header_description="INSTALLATION - Aide";
require_once "../templates/default/header.php";
?>
<HR>  
<ul>
  <li>
    <div align="left">Si vous venez d'installer EasyPhp en local, la base n'existe pas encore. <br>
      Vous devez donc demander sa cr&eacute;ation.</div>
  </li>
</ul>

<blockquote><p align="left">&nbsp;</p>
  </blockquote>
<div align="left">
  <ul>
    <li>Si vous installez chez un h&eacute;bergeur telque Free, votre base existe d&eacute;j&agrave; pour peu que vous l'ayez activ&eacute;e <br>
      (voir site de Free - Espace perso - Activer sa base). <br>
      Elle porte le nom de votre login. Vous demanderez alors uniquement la cr&eacute;ation des tables.</li>
  </ul>
</div>
<p align="left">&nbsp;</p>
<div align="left">
  <ul>
    <li>Attention, dans les cas de r&eacute;installation, pensez &agrave; supprimer les anciennes tables existantes <br>
      (outil PhpMyadmin - tables &quot;cdt_...&quot;) </li>
  </ul>
</div>
<ul>
</ul>
<DIV id=footer></DIV>
</DIV>
</body>
</html>

