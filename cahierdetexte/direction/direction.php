<?php 
//modifier pour chaque nouvelle version
$indice_version='5510'; 
$libelle_version='Version 5.5.1.0 Standard';

include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>4) { header("Location: ../index.php");exit;};?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style74 {	font-size: 8pt;
	text-indent: 10px;
	color:#0000FF;
}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
  <p>
    <?php 
$header_description="Responsable Etablissement";
require_once "../templates/default/header.php";
?>
  </p>
  <br />
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="5">
    <tr>
      <td width="50%"><div align="left" class="Style6"> Configurer </div></td>
      <td><div align="left" class="Style6">G&eacute;rer</div></td>
    </tr>
    <tr>
      <td width="50%" valign="top" class="Style74"><br /><p align="left"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="parametres_direction.php">Mes param&egrave;tres g&eacute;n&eacute;raux (identifiant, m&eacute;l...)</a></p>
        <p align="left"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="chemin_fichier_perso.php">Chemin d'acc&egrave;s &agrave; mes fichiers personnels sur le serveur</a></p>
        <p align="left"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="visa_stop_edition.php">Modification des saisies ant&eacute;rieures au visa</a></p>
        <p align="left">&nbsp; </p>
        <p align="left">&nbsp;&nbsp;<img src="../images/exclamation.png" width="16" height="16">&nbsp;<a href="stop_cdt.php">BLOQUER L'EDITION ET LA PUBLICATION</a></p>
       <br />
        
         </p>
        <p align="left" class="tab_detail_bleu"><img src="../images/lightbulb.png" width="16" height="16"><strong>&nbsp;Attribution d'un visa par le responsable &eacute;tablissement</strong><br>
          <br>
          <br>
          Il existe deux types de visas : visa global et visa local. Il appartient au chef d'&eacute;tablissement de choisir l'un OU l'autre.<br>
          <br>
          <strong>Visa global : </strong><br>
          Ce visa est appos&eacute; sur tous les cahiers de textes et VISIBLE en mode &eacute;l&egrave;ve et parents dans chacun des cahiers de textes s&eacute;lectionn&eacute; depuis la page travail &agrave; faire. Ce visa est &eacute;galement visible cot&eacute; enseignant &agrave; l'int&eacute;rieur de leur cahier de textes accessible depuis leur menu enseignant. <br>
          <br>
          <br>
          <strong>Visa local</strong> : <br>
          Un tel visa peut &ecirc;tre appos&eacute; de deux fa&ccedil;ons : <br>
          &nbsp;- par validation de l'ensemble des cahiers d'un enseignant,<br>
          &nbsp;- par validation d'une partie d'un cahier de l'enseignant.<br>
          Ce visa n'est PAS VISIBLE COTE ELEVE - mais sera visible par l'enseignant en page de saisie au-dessus de ses messages. <br>
          <br>
          Le param&egrave;tre <i>"Modification des saisies ant&eacute;rieures au visa"</i> peut permettre d'interdire ou d'autoriser toute modification des saisies de l'enseignant ant&eacute;rieures &agrave; la date du visa.<br>
          <br />
          <br>
          <br />
          <br />
          <br />
        </p></td>
      <td valign="top" class="Style74"><br /><p align="left"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="publication_visa_global.php">Attribution d'un visa global </a></p>
        <p align="left"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="publication_visa.php?option=1">Attribution de visas locaux </a></p>
        <p align="left"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="publication_visa.php?option=3">Consultation des cahiers de textes d'un enseignant</a></p>
        <p align="left"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="publication_visa.php?option=2">Mise &agrave; disposition des cahiers en cas d'inspection</a></p>
        <p align="left"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../administration/sauvegarde1.php">Sauvegarde de la base de donn&eacute;es </a></p>
        <p align="left"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="lire_classe_pdf.php">Impression des cahiers de textes</a>        </p>
        <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/niveau_classe_ajout.php">Cr&eacute;er et g&eacute;rer des niveaux de classes </a></p>
        <br>
        <div align="left" class="Style6">Communiquer</div><br />
        <p align="left"class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/message_ajout_profs.php">Diffusion d'un message aux enseignants </a></p>
		<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/message_ajout_profs.php?ppliste=1">Diffusion d'un message aux professeurs principaux </a></p>
        <p align="left"class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/message_ajout.php?tri=auteur">Diffusion d'un message aux &eacute;l&egrave;ves</a></p>
        <p align="left"class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../enseignant/evenement_liste.php">Liste des &eacute;v&egrave;nements ou actions p&eacute;dagogiques </a></p>
        <br>
        <div align="left" class="Style6">S'informer</div><br />
        <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="planning_select.php">Planning des devoirs et du travail donn&eacute; dans une classe</a> </p>
        <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../edt_eleve.php">Emploi du temps d'une classe</a></p>
        <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/prof_principaux_liste.php?tri=pp">Liste des professeurs principaux d&eacute;clar&eacute;s</a></p>
        <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/prof_liste.php">Liste de l'ensemble du personnel avec leur m&eacute;l</a></p>

        <br>
        <?php
if ((isset($_SESSION['module_absence']))&&($_SESSION['module_absence']=='Oui')) {       ?>
        <div align="left" class="Style6">Absences</div><br />
        <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/absence.php">Absences du jour</a></p>
        <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/absence2.php">Etat des absences par classe sur une p&eacute;riode</a></p>
        <?php ;};?>
        <p align="left" class="Style74">&nbsp;</p>
        <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../deconnexion.php">Me d&eacute;connecter </a></p></td>
    </tr>
  </table>
  <DIV id=footer>
    <p class="auteur"><a href="contribution.php" class="auteur">Pierre Lemaitre 
      - St L&ocirc; (France) - <?php echo $libelle_version ;?> <br />
      </a></p>
  </DIV>
</DIV>
</body>
</html>
