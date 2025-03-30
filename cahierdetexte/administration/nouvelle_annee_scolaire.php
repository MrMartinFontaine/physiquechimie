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
$header_description="Nouvelle ann&eacute;e scolaire";
require_once "../templates/default/header.php";
?>
<HR>  

<div align="left">
  <blockquote>
    <p><strong>Faut-il pr&eacute;alablement &eacute;diter sur papier l'ensemble des cahiers de l'ann&eacute;e termin&eacute;e ?</strong></p>
    <p>Une des sp&eacute;cificit&eacute;s d'un cahier de textes num&eacute;rique est de pr&eacute;senter des liens vers des documents num&eacute;riques. L'&eacute;dition papier s'av&egrave;rerait donc insuffisante. L'archivage doit donc &ecirc;tre num&eacute;rique ! </p>
    <p><strong>Pr&eacute;paration de la nouvelle ann&eacute;e : </strong></p>
  </blockquote>
  <ul>
    <li>Faire  une sauvegarde de la base (Menu administrateur - Sauvegarde). <br>
    Chaque enseignant peut &eacute;ventuellement recup&eacute;rer un fichier zipp&eacute; de ses fichiers envoy&eacute;s <br>
    (Voir Menu enseignant - Exporter/Sauvegarder  mon cahier et mes fichiers)</li>
    <li>Faire une mise &agrave; jour avec la <strong>derni&egrave;re version</strong> du logiciel </li>
  </ul>
  <blockquote>
    <p>&nbsp;</p>
    <p><br>
      Trois solutions sont alors possibles, la premi&egrave;re &eacute;tant la m&eacute;thode la plus simple et fortement conseill&eacute;e :<br>
      <br>
    </p>
    <ul>
      <li><strong>Solution 1 : </strong><span class="erreur"><strong>NOUVEAU !</strong></span> Conservation de votre base. Duplication des tables pour archivage. <br>
        Il vous faut archiver l'ann&eacute;e termin&eacute;e. <br>
        Cette op&eacute;ration est r&eacute;alis&eacute;e dans le menu Administrateur
      &agrave; l'aide de la rubrique &quot;<strong>Acc&egrave;s &agrave; l'archivage</strong> &quot;.<br>
      Techniquement, il y aura duplication des tables de l'ann&eacute;e termin&eacute;e et possibilit&eacute; de vider les tables n&eacute;cessaires pour commencer une nouvelle ann&eacute;e. <br>
L'acc&egrave;s de l'enseignant aux archives des ann&eacute;es pass&eacute;es se fera depuis son menu enseignant via la rubrique &quot;Consulter - Imprimer mon cahier de textes&quot; ou depuis l'icone habituelle <img src="../images/mois.gif" width="11" height="13"> &quot;Consultation d'archives&quot; en haut de calendrier.<br>
<br>
<blockquote>
  <p>Archiver l'ann&eacute;e pr&eacute;c&eacute;dente <a href="archivage.php">(via la page ad&eacute;quate du menu d'administration)</a> en cochant l'option de vidage du Cahier de Textes. Les enseignants depuis leur menu <i>enseignant</i> ou depuis la saisie de leur Cahier de Textes pourront acc&egrave;der directement &agrave; leurs archives.<BR>
      </BR>
        <BR>
        Red&eacute;finir les dates de d&eacute;but et de fin de la nouvelle ann&eacute;e scolaire
        <a href="dates_annee_scol_param.php">(via la page ad&eacute;quate du menu d'administration)</a>.</p>
  <p>Indiquer les semaines A et B <a href="semaine_ab_menu.php">(via la page ad&eacute;quate du menu d'administration)</a> de votre &eacute;tablissement.<br>
    <br>
    Importer les emplois du temps des enseignants <a href="edt.php">(via la page ad&eacute;quate du menu d'administration)</a> qu'ils auront l'occasion d'ins&eacute;rer eux-m&ecirc;mes dans leur menu <i>enseignant</i>.<br>
    <br>
    Si vous avez import&eacute; depuis UDT ou EDT, il vous faudra &eacute;ventuellement g&eacute;rer les groupes apr&egrave;s import <a href="groupe_ajout.php">(via la page ad&eacute;quate du menu d'administration)</a>.</p>
</blockquote>
      </li>
    </ul>
    <blockquote>
<p>&nbsp;</p>
    </blockquote>
    <ul>
      <li><strong>Solution 2 </strong>: Nouvelle ann&eacute;e, nouvelle base. (plus technique ;) <br>
        Vous disposez d'une seconde base de donn&eacute;es disponible. Vous faites un export des donn&eacute;es des tables cdt_profs, cdt_matiere, cdt_classe ... avec un outil tel que PhpMyAdmin. Vous faites une nouvelle installation de la derni&egrave;re version en ligne sur un autre espace disque. Avec Phpmyadmin, vous ins&eacute;rez les tables sauvegard&eacute;es pr&eacute;c&eacute;demment dans votre nouvelle base de donn&eacute;es.<br>
      Dans le menu administrateur et &agrave; l'aide de la rubrique &quot;Acc&egrave;s &agrave; un ancien cahier de textes&quot;, vous param&eacute;trez l'acc&egrave;s &agrave; l'ancienne base de donn&eacute;es.<br>
      L'acc&egrave;s enseignant &agrave; l'ancien cahier de textes se fera depuis une icone situ&eacute;e devant le mois dans le calendrier ou depuis son menu via la rubrique &quot;Consulter un ancien cahier de textes&quot; si cette m&eacute;thode a &eacute;t&eacute; valid&eacute;e par l'administrateur. <br>
      </li>
    </ul>
    <p><br>
    </p>
    <hr>
  </blockquote>
  <ul>
    <ul>
      <li><strong>Solution 3 </strong>: Pas d'acc&egrave;s en consultation de l'ancien cahier de textes, nettoyage de la base actuelle.<br>
        Attention, ceete solution <strong>ne respecte donc pas les textes officiels</strong> dans lesquels il est demand&eacute; de r&eacute;aliser un archivage des cahiers des ann&eacute;es pr&eacute;c&eacute;dentes.
        <br> 
      </li>
      <li>Cette solution est simple <strong></strong> pour ceux qui ne maitrisent pas un outil tel que PhpMyAdmin. <br>
        Vous viderez simplement la base de donn&eacute;es existante. Mais vous souhaitez sans doute conserver la table des professeurs, leur mot de passe, leurs fiches de progression, leurs types d'activit&eacute;s propres, la table des noms de mati&egrave;res, la table des noms de classe et leurs mots de passe &eacute;ventuels, la table des libell&eacute;s de groupe.<br>
        Vous aurez trois actions &agrave; r&eacute;aliser (et n'oubliez pas la seconde !) </li>
    </ul>
    <ul>
      <ul>
        <li>Ex&eacute;cuter le script <a href="vider_cahier_de_texte.php"> Vider le Cahier de Textes </a></li>
        <li>Supprimer manuellement les fichiers contenu dans les dossiers <br>
        <strong>fichiers_joints, fichiers_joints_message, rss, exportation</strong> &agrave; l'exception des fichiers nomm&eacute;s index </li>
        <li>Corriger dans le menu administrateur les dates de vacances, semaines A et B, la listes des  classes, les nouveaux enseignants... </li>
      </ul>
    </ul>
    <p align="center">&nbsp;</p>
    <hr>
    <br>
    <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  </ul>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>

