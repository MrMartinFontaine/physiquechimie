<?php include "../authentification/authcheck.php"; 
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
?>
<?php require_once('../Connections/conn_cahier_de_texte.php'); ?>
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
$header_description="Configuration de l'&eacute;diteur XINHA";
require_once "../templates/default/header.php";
?>
  <blockquote>
    <blockquote>
      <blockquote>
        <p align="left">&nbsp;</p>
        <p align="left">Xinha est un &eacute;diteur tr&egrave;s abouti. Il offre de nombreuses fonctionnalit&eacute;s via sa barre d'outils. Cette barre peut appeler des plugins (Equations, Stylist...) Nous avons ainsi rajout&eacute; un plugin Latex pour les scientifiques. </p>
        <p align="left">Cependant, tout cela peut   &ecirc;tre long  &agrave; charger en m&eacute;moire.</p>
        <p align="left"><strong>Chaque enseignant peut activer ou d&eacute;sactiver</strong> via son menu personnel ces plugins. Ainsi le professeur de Fran&ccedil;ais peut d&eacute;sactiver le plugin Latex, lib&eacute;rant ainsi de la m&eacute;moire vive.</p>
        <p align="left">Il en est de m&ecirc;me pour le plugin Stylist. Certains &eacute;tablissements ou enseignants souhaitent personnaliser leurs styles (Stylist utilise la feuille de style templates/default/perso.css). Certains trouveront ce plugin trop rustique pour des d&eacute;butants, r&eacute;duisant la zone de saisie, et pourront donc le d&eacute;sactiver. </p>
        <p align="left">Vous pouvez aller plus loin dans la personnalisation en &eacute;ditant   le fichier <strong>enseignant/area_activite.php</strong>, vous pourrez :</p>
        <div align="left">
        <ul><li>
            Activer  le skin &quot;Silva&quot; mais cela peut ralentir le chargement de la page. A tester
          </li>
          <li>
            D&eacute;sactiver l'&eacute;diteur Xinha pour les zones de saisie du travail &agrave; faire
          </li>
          <li>
            D&eacute;sactiver certaines icones de la barre d'outil
          </li>
        </ul>
        </div>
        <p align="left">. </p>
      </blockquote>
    </blockquote>
  </blockquote>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
