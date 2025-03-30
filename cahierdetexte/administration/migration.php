<?php include "../authentification/authcheck.php"; ?>
<?php if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style70 {
	color: #FF0000;
	font-weight: bold;
}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Changement de version - Aide";
require_once "../templates/default/header.php";
?>
<HR>  

<div align="left">
  <ul>
    <li><p align="left">Dans le cadre d'un changement de version, vous gardez absolument toute votre base.<br> 
    Le but n'est que de modifier les fichiers qui permettent &agrave; votre cahier de textes de passer &agrave; la version suivante.</li>
    <li>
      <p align="left">Deux solutions s'offrent &agrave; vous, la seconde &eacute;tant la plus rapide. <br>
    </li>
    <li><span class="Style70">Solution 1  :</span> On sauvegarde en local les fichiers n&eacute;cessaires. On vide le site distant. On monte les fichiers de la nouvelle version puis les fichiers sauvegard&eacute;s en local <br>
      <br>
    </li>
      <ul>
        <li>1. Sauvegarder en local le dossier : <b>Connections</b> contenant vos param&egrave;tres de connexion &agrave; la base, nom de votre &eacute;tablissement.... <br>
          <br>
        </li>
		<li>2. Sauvegarder en local tout les dossiers <strong>fichiers_joints, fichiers_joints_message, rss, exportation</strong>.<br>
          <br>
        </li>
        <li>3. Sauvegarder en local tout le dossier <b>templates/default </b>dans le cas ou vous avez d&eacute;fini des  styles personnels et/ou modifi&eacute; le fichier header.php.<br>
          <br>
        </li>
        <li>4. Supprimer tous les fichiers de votre cahier de textes, ancienne version.<br>
          <br>
        </li>
        <li>5. <a href="http://www.etab.ac-caen.fr/bsauveur/cahier_de_texte/?page_id=7">T&eacute;l&eacute;charger</a> l'archive et la d&eacute;compresser. Transf&eacute;rer les fichiers de la nouvelle version sur votre serveur.<br>
          <br>
        </li>
        <li>6. Remettre vos  dossiers   sauvegard&eacute;s Connections, fichiers_joints, fichiers_joints_message, rss, exportation<br>
 et templates/default sur le serveur.<br>
          <br>
        </li>
        <li>7. Se connecter sur l'interface du cahier de textes en administrateur avec le m&ecirc;me mot de passe qu'auparavant.<br>
          <br>
        <li>8. <strong>V&eacute;rifiez si une mise &agrave; jour de votre base de donn&eacute;es est n&eacute;cessaire dans la partie "Mise &agrave; jour" <br>
        du menu Administrateur. </strong></li>
      </ul>
      <p>&nbsp;</p>
      <p><span class="Style70">Solution 2  :</span> On sauvegarde en local les fichiers n&eacute;cessaires notamment le dossier Connections. On monte en &eacute;crasant les fichiers distants. </p>
      <p>1) <a href="http://www.etab.ac-caen.fr/bsauveur/cahier_de_texte/?page_id=7">T&eacute;l&eacute;charger</a> l'archive et la d&eacute;compresser</p>
      <p>2) Dans ce dossier d&eacute;compress&eacute;, supprimez le   dossier Connections (et &eacute;ventuellement les dossiers template et styles   pour ceux qui auraient personnalis&eacute; le cdt). Si vous utilisez une   version LDAP, supprimer le dossier authentification de fa&ccedil;on &agrave; conserver   en ligne votre fichier authentification/config_ldap.php. </p>
      <p>3) Avec votre outil FTP, vous montez tout le reste   et  ECRASEZ les anciens fichiers d&eacute;j&agrave; pr&eacute;sents... </p>
      <p>8. <strong>V&eacute;rifiez si une mise &agrave; jour de votre base de donn&eacute;es est n&eacute;cessaire dans la partie "Mise &agrave; jour" <br>
du menu Administrateur. </strong></p>
      <p align="center"><a href="index.php"><br>
      Retour au Menu Administrateur</a></p>
  </ul>

  <DIV id=footer></DIV>
</DIV>
</body>
</html>

