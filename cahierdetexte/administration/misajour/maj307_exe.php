<?php include "../../authentification/authcheck.php"; ?>
<?php if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};?>
<?php 

require_once('../../Connections/conn_cahier_de_texte.php'); 

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);


//------------------maj 3.0.7 -------------------------------------
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "
CREATE TABLE `cdt_evenement_contenu` (
  `ID_even` smallint(5) NOT NULL auto_increment,
  `titre_even` varchar(50) NOT NULL default '',
  `detail` text,
  `prof_ID` tinyint(4) unsigned NOT NULL default '0',
  `date_debut` date NOT NULL default '0000-00-00',
  `heure_debut` varchar(6) NOT NULL default '00h00',
  `date_fin` date NOT NULL default '0000-00-00',
  `heure_fin` varchar(6) NOT NULL default '00h00',
  `date_envoi` date NOT NULL default '0000-00-00',
  `dest_ID` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID_even`)
) TYPE=MyISAM AUTO_INCREMENT=1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "
CREATE TABLE `cdt_evenement_destinataire` (
  `ID_dest` smallint(5) unsigned NOT NULL auto_increment,
  `even_ID` tinyint(3) unsigned NOT NULL default '0',
  `classe_ID` tinyint(3) unsigned NOT NULL default '0',
  `groupe_ID` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID_dest`)
) TYPE=MyISAM AUTO_INCREMENT=1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "
CREATE TABLE `cdt_prof_principal` (
  `ID_pp` smallint(5) unsigned NOT NULL auto_increment,
  `pp_prof_ID` smallint(5) NOT NULL default '0',
  `pp_classe_ID` smallint(5) NOT NULL default '0',
  `pp_groupe_ID` smallint(5) NOT NULL default '0',
  PRIMARY KEY  (`ID_pp`)
) TYPE=MyISAM AUTO_INCREMENT=1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 3.0.7 standard' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '3080' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '".date("d/m/Y, g:i a")."' WHERE `param_nom` ='date_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));

mysqli_close($conn_cahier_de_texte);  
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Mises &agrave; jour de la base de donn&eacute;es ";
require_once "../../templates/default/header.php";
?>

  <p>&nbsp;</p>
  <p>Les modifications propos&eacute;es &eacute;taient l'ajout des trois tables <br>
    <strong>cdt_evenement_contenu</strong>, <strong>cdt_evenement_destinataire</strong> et  <strong>cdt_prof_principal</strong>. <br>
  </p>
  <blockquote><blockquote>&nbsp;</blockquote>
  </blockquote>

  <div align="left">
    <blockquote>
      <p align="center">Si aucun message d'erreur n'est apparu, les mises &agrave; jour ont &eacute;t&eacute; r&eacute;alis&eacute;es. </p>
      <p align="center">&nbsp;</p>
    </blockquote>
  </div>
  <p><a href="maj.php">Continuer</a></p>
  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
