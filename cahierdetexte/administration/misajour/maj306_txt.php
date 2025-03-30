<?php include "../../authentification/authcheck.php" ;?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/JavaScript">
<!--
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
//-->
</script>
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Mises &agrave; jour de la base de donn&eacute;es - Ver 3.0.6";
require_once "../../templates/default/header.php";
?>

  
  <p>&nbsp;</p>
<script language="JavaScript" type="text/JavaScript">
function formfocus() {
document.form1.date_form.focus()
document.form1.date_form.select()
}
</script>

    <blockquote> 
      
    <p align="left"><strong>Mise &agrave; jour 3.0.6 / 15 F&eacute;vrier 2008 </strong></p>
      
    <p align="left">A faire dans le cas d'une mise &agrave; jour d'une version 
      ant&eacute;rieure &agrave; la version 3.0.6.</p>
    <p align="left">Cela n'affectera pas vos donn&eacute;es. Quelques tables ou champs seront rajout&eacute;s ou modifi&eacute;s.</p>
    <p align="left" class="erreur">Attention, les messages actuellement publi&eacute;s par la vie scolaire aux &eacute;l&egrave;ves  seront supprim&eacute;s. </p>
      <p align="left">Le lien au-bas de cette page effectuera automatiquement cette mise &agrave; jour. </p>
      <p align="left">Cependant, certains n'ont pas toujours les droits pour manipuler leur base de fa&ccedil;on automatis&eacute;e et devront alors effectuer ces requ&ecirc;tes manuellement avec un outil tel que phpmyadmin. </p>
      <p align="left">La syntaxe est :</p>
      <p align="left">ALTER TABLE `cdt_prof` ADD `publier_cdt` ENUM( 'O', 'N' ) DEFAULT 'O' NOT NULL;<br>
      ALTER TABLE `cdt_prof` ADD `publier_travail` ENUM( 'O', 'N' ) DEFAULT 'O' NOT NULL;<br>
      ALTER TABLE `cdt_prof` ADD `date_maj` DATE NOT NULL;<br>
      ALTER TABLE `cdt_prof` ADD `droits` tinyint(3) unsigned NOT NULL default '2';<br>
      ALTER TABLE `cdt_agenda` ADD `date_visa` date NOT NULL default '0000-00-00';<br>
      <br>
      UPDATE `cdt_prof` SET `droits` = '1' WHERE `ID_prof` =1;<br>
      UPDATE `cdt_prof` SET `droits` = '2' WHERE `ID_prof` &gt;1;</p>
      <p align="left">CREATE TABLE `cdt_progression` (<br>
        `ID_progression` tinyint(3) unsigned NOT NULL auto_increment,<br>
        `prof_ID` tinyint(4) unsigned NOT NULL default '0',<br>
        `titre_progression` varchar(255) NOT NULL default '',<br>
        `contenu_progression` text NOT NULL,<br>
        PRIMARY KEY  (`ID_progression`)<br>
        )TYPE=MyISAM AUTO_INCREMENT=1;</p>
      <p align="left">CREATE TABLE `cdt_message_contenu` (<br>
`ID_message` smallint(5) NOT NULL auto_increment,<br>
`message` text NOT NULL,<br>
`prof_ID` tinyint(4) unsigned NOT NULL default '0',<br>
`date_envoi` date NOT NULL default '0000-00-00',<br>
`online` enum('O','N') NOT NULL default 'O',<br>
`dest_ID` tinyint(4) NOT NULL default '0',<br>
        PRIMARY KEY  (`ID_message`)<br>
        ) TYPE=MyISAM AUTO_INCREMENT=1;</p>
      <p align="left">CREATE TABLE `cdt_message_destinataire` (<br>
`ID_dest` smallint(5) unsigned NOT NULL auto_increment,<br>
`message_ID` tinyint(3) unsigned NOT NULL default '0',<br>
`classe_ID` tinyint(3) unsigned NOT NULL default '0',<br>
`groupe_ID` tinyint(3) unsigned NOT NULL default '0',<br>
PRIMARY KEY  (`ID_dest`)<br>
) TYPE=MyISAM AUTO_INCREMENT=1; </p>
      <p align="left">DROP  TABLE  `cdt_viescol` ; <br>
        <br>
        ALTER  TABLE  `cdt_prof`  ADD  `path_fichier_perso` VARCHAR( 255  ) ; <br>
      ALTER TABLE `cdt_prof` ADD `identite` VARCHAR( 255 ) AFTER `passe`;<br>
      ALTER TABLE `cdt_prof` ADD `email` VARCHAR( 255 ) AFTER `identite`;<br>
      <br>
      UPDATE `cdt_prof` SET cdt_prof.identite = cdt_prof.nom_prof WHERE cdt_prof.identite=''</p>
      <p align="left">ALTER TABLE `cdt_prof` ADD `xinha_editlatex` enum('O','N') NOT NULL default 'N';<br>
      ALTER TABLE `cdt_prof` ADD `xinha_equation` enum('O','N') NOT NULL default 'N';<br>
      ALTER TABLE `cdt_prof` ADD `xinha_stylist` enum('O','N') NOT NULL default 'N';</p>
      <p align="left"><a href="maj304_exe.php"></a>CREATE TABLE `cdt_params` (<br>
`param_nom` varchar(32) NOT NULL default '',<br>
`param_val` text,<br>
`param_desc` varchar(255) default NULL,<br>
PRIMARY KEY  (`param_nom`)<br>
) TYPE=MyISAM ;</p>
      <p align="left">INSERT INTO `cdt_params` VALUES ('version', 'Version 3.0.6 standard', 'Version du logiciel');<br>
        INSERT INTO `cdt_params` VALUES ('ind_maj_base', '3060', 'indice de la version');<br>
      INSERT INTO `cdt_params` VALUES ('date_maj_base', NULL, 'date de la mise &agrave; jour');</p>
      <p align="left">&nbsp;</p>
      <p align="center">
        <input name="Submit" type="submit" onClick="MM_goToURL('window','maj306_exe.php');return document.MM_returnValue" value="Faire automatiquement la mise &agrave; jour de la base de donn&eacute;es">
</p>
    </blockquote>
    <p align="center"><br>
    </p>
    <p align="center"><a href="../index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
