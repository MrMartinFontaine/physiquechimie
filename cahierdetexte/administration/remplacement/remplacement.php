<?php 

include "../../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1 && $_SESSION['droits']<>3) { header("Location: ../index.php");exit;};?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style74 {font-size: 8pt;
	text-indent: 10px;
	color:#0000FF;
}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
  <?php 
$header_description='Gestion des remplacements';
require_once "../../templates/default/header.php";
?>
  <br />
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="5">
    <tr ><td>
      <div  style="background:#F0EDE5;margin:10px;padding:5px;" >
<div align="left">
  <p><img src="../../images/lightbulb.png">
      <strong>Gestion des titulaires :</strong></p>
  <blockquote>
    <p> Permet de mettre un professeur absent ou pr&eacute;sent.<br>
      Lorsque un professeur est absent, il ne peut plus remplir le cahier de textes concernant la p&eacute;riode durant laquelle il est absent.
      Il peut toutefois modifier son cahier de textes correspondant &agrave; sa p&eacute;riode de pr&eacute;sence. </p>
  </blockquote>
</div>
<p align="left"><strong><img src="../../images/lightbulb.png"> Gestion des rempla&ccedil;ants : </strong></p>
<blockquote>
  <p align="left">    Permet de cr&eacute;er un rempla&ccedil;ant, de g&eacute;rer son emploi du temps et de mettre fin &agrave; son remplacement.
    Suite &agrave; la mise en place d'un rempla&ccedil;ant, le titulaire sera mis en absence, si cela n&#39;a pas &eacute;t&eacute; d&eacute;j&agrave; fait. </p>
</blockquote>
      </div></td>
    </tr>
    <tr>
      <td valign="top" class="Style74"><p align="center"><a href="remplacement_titulaires.php">Gestion des titulaires</a></p>
        <p align="center"><a href="remplacement_remplacants.php">Gestion des rempla&ccedil;ants</a></p>
        <p align="center"><a href="remplacement_etat.php">Etat des remplacements </a></p></td>
    </tr>
  </table>
  <table>
    <tr>
      <p align="left" class="Style74">&nbsp;</p>
      <?php
	if ($_SESSION['droits']==1) { ?>
      <p align="center" class="Style74"><a href="../index.php">Retour au Menu Administrateur</a></p>
      </td>
      <?php }
	else { ?>
      <p align="center" class="Style74"><a href="../../vie_scolaire/vie_scolaire.php">Retour au Menu Vie scolaire</a></p>
      </td>
      <?php } ?>
    </tr>
  </table>
  <DIV id=footer>
    <p class="auteur"><a href="../contribution.php" class="auteur">Pierre Lemaitre 
      - St L&ocirc; (France) </a></p>
  </DIV>
</DIV>
</body>
</html>
