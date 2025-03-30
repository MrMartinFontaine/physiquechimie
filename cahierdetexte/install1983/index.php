<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - Installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style145 {
	color: #FF0000;
	font-weight: bold;
}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="INSTALLATION";
require_once "../templates/default/header.php";
?>

<BR>

<?php 
if (substr(phpversion(),0,1)<5){
?>
<blockquote>  
  <p align="center">Attention : cette version du cahier de texte n&eacute;cessite une version de PHP</p>
  <p align="center"> &eacute;gale ou sup&eacute;rieure &agrave; <strong>5</strong> (et une version MYSQL égale ou supérieureà 4.1) </p>
  <p align="center">La version de votre serveur PHP est actuellement<span class="Style145"> <?php echo phpversion();?></span></p>


  <div align="center"><br>
  </div>
  <p align="center">En conséquence, il vous est donc <span class="Style145">impossible d'installer cette version</span> du cahier de texte sur ce serveur.</p>
  <p align="center">Si vous disposez d'une version de PHP inf&eacute;rieure &agrave; 5, installez la version cdt_4948 <a href="http://www.bonsauveur.monarobase.net/wp_cdt/?page_id=7"><strong>disponible ici</strong>.</a></p>
    <p align="left"><em>Note &agrave; propos des serveurs FREE : les h&eacute;bergements Free offrent de fa&ccedil;on native du PHP 4. Il est cependant possible de disposer du PHP 5.6 et sup&eacute;rieur par ajout d'un fichier .htaccess en racine de votre h&eacute;bergement. </em><a href="https://www.freenews.fr/freenews-edition-nationale-299/services-web-180/pages-perso-free-ajout-support-php-5-6" target="_blank"><em>Voir ici. </em></a></p>
</blockquote>
  <?php } else {?>
  <blockquote>  
  <p align="left">L'installation se d&eacute;roule en trois parties : </p>
  <ul>
  <li>
    <div align="left">      Enregistrement de vos param&egrave;tres de connexion, cr&eacute;ation de la base si elle n'existe pas et des tables</div>
  </li>
  <li>
    <div align="left">Connexion en tant qu'Administrateur puis saisies successives des enseignants, des classes, des mati&egrave;res</div>
  </li>
  <li> <div align="left">Connexion en tant qu'Enseignant puis saisies successives de son emploi du temps, de ses types d'activit&eacute;s</div>
  </li>
</ul>
Vous devez disposer des droits <strong>en &eacute;criture</strong> sur les dossiers suivants : 
<blockquote>
  <p align="left"><strong>- Connections</strong> et son fichier <strong>conn_cahier_de_texte.php</strong><br>
      <strong>- fichiers_joints</strong><br>
    <strong>- rss </strong><br>
    - <strong>exportation</strong><br>
    - <strong>fichiers_joints_message</strong></p>
</blockquote>
  <p align="left">Si vous r&eacute;alisez une installation en local, vous poss&eacute;dez g&eacute;n&eacute;ralement les droits par d&eacute;faut. <br>
    Si vous avez mont&eacute; l'application chez un h&eacute;bergeur, vous pouvez attribuer les droits en &eacute;criture avec votre outil FTP (g&eacute;n&eacute;ralement clic droit sur le nom du fichier ou du dossier). Ceci &eacute;tant fait, vous pouvez d&eacute;buter l'installation. </p>
  <p align="left">&nbsp;</p>
</blockquote>
<p align="center"><a href="install_param.php"><strong>D&eacute;buter l'installation </strong></a></p>
</blockquote>  
<?php };?>
<DIV id=footer></DIV>
</DIV>
</body>
</html>

