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
      
    <p align="left"><strong>Mise &agrave; jour 3.0.7 / 7 Mai 2008 </strong></p>
      
    <p align="left">A faire dans le cas d'une mise &agrave; jour d'une version 
      ant&eacute;rieure &agrave; la version 3.0.7</p>
    <p align="left">Cela n'affectera pas vos donn&eacute;es. Trois tables seront ajout&eacute;es.</p>
    <p align="left" class="erreur">Le lien au-bas de cette page effectuera automatiquement cette mise &agrave; jour. </p>
      <p align="left">Cependant, certains n'ont pas toujours les droits pour manipuler leur base de fa&ccedil;on automatis&eacute;e et devront alors effectuer ces requ&ecirc;tes manuellement avec un outil tel que phpmyadmin. </p>
      <p align="left">La syntaxe est :</p>
      <p align="left">CREATE TABLE `cdt_evenement_contenu` (<br>
        `ID_even` smallint(5) NOT NULL auto_increment,<br>
        `titre_even` varchar(50) NOT NULL default '',<br>
        `detail` text,<br>
        `prof_ID` tinyint(4) unsigned NOT NULL default '0',<br>
        `date_debut` date NOT NULL default '0000-00-00',<br>
        `heure_debut` varchar(6) NOT NULL default '00h00',<br>
        `date_fin` date NOT NULL default '0000-00-00',<br>
        `heure_fin` varchar(6) NOT NULL default '00h00',<br>
        `date_envoi` date NOT NULL default '0000-00-00',<br>
        `dest_ID` tinyint(4) NOT NULL default '0',<br>
        PRIMARY KEY  (`ID_even`)<br>
        ) TYPE=MyISAM AUTO_INCREMENT=1 ;</p>
      <p align="left">CREATE TABLE `cdt_evenement_destinataire` (<br>
        `ID_dest` smallint(5) unsigned NOT NULL auto_increment,<br>
        `even_ID` tinyint(3) unsigned NOT NULL default '0',<br>
        `classe_ID` tinyint(3) unsigned NOT NULL default '0',<br>
        `groupe_ID` tinyint(3) unsigned NOT NULL default '0',<br>
        PRIMARY KEY  (`ID_dest`)<br>
        ) TYPE=MyISAM AUTO_INCREMENT=1 ;</p>
      <p align="left">CREATE TABLE `cdt_prof_principal` (<br>
`ID_pp` smallint(5) unsigned NOT NULL auto_increment,<br>
`pp_prof_ID` smallint(5) NOT NULL default '0',<br>
`pp_classe_ID` smallint(5) NOT NULL default '0',<br>
`pp_groupe_ID` smallint(5) NOT NULL default '0',<br>
PRIMARY KEY  (`ID_pp`)<br>
) TYPE=MyISAM AUTO_INCREMENT=1 ;</p>
      <p align="left">UPDATE `cdt_params` SET `param_val` = 'Version 3.0.7 standard' WHERE `param_nom` ='version' ;</p>
      <p align="left">UPDATE `cdt_params` SET `param_val` = '3072' WHERE `param_nom` ='ind_maj_base' ;</p>
      <p align="left">UPDATE `cdt_params` SET `param_val` = <em>'date du jour</em>' WHERE `param_nom` ='date_maj_base'  </p>
      <p align="left">&nbsp;</p>
      <p align="center">
        <input name="Submit" type="submit" onClick="MM_goToURL('window','maj307_exe.php');return document.MM_returnValue" value="Faire automatiquement la mise &agrave; jour de la base de donn&eacute;es">
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
