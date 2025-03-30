<?php include "../../authentification/authcheck.php"; ?>
<?php if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};?>
<?php 

require_once('../../Connections/conn_cahier_de_texte.php'); 

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);


//------------------maj 3.0.4 -------------------------------------

if (mysqli_query($conn_cahier_de_texte, "SELECT publier from cdt_prof")) {  
$sql_0="ALTER TABLE `cdt_prof` DROP `publier`  ";
$result_sql_0=mysqli_query($conn_cahier_de_texte, $sql_0) or die('Erreur SQL !'.$sql_0.mysqli_error($conn_cahier_de_texte)); 
};

if (!mysqli_query($conn_cahier_de_texte, "SELECT publier_travail from cdt_prof")) {  
$sql_00="ALTER TABLE `cdt_prof` ADD `publier_travail` ENUM( 'O', 'N' ) DEFAULT 'O' NOT NULL";
$result_sql_00=mysqli_query($conn_cahier_de_texte, $sql_00) or die('Erreur SQL !'.$sql_00.mysqli_error($conn_cahier_de_texte)); 
};


if (!mysqli_query($conn_cahier_de_texte, "SELECT publier_cdt from cdt_prof")) {  
$sql1="ALTER TABLE `cdt_prof` ADD `publier_cdt` ENUM( 'O', 'N' ) DEFAULT 'O' NOT NULL";
$result_sql_11=mysqli_query($conn_cahier_de_texte, $sql1) or die('Erreur SQL !'.$sql1.mysqli_error($conn_cahier_de_texte)); 
};



if (!mysqli_query($conn_cahier_de_texte, "SELECT date_maj from cdt_prof")) {  
$sql2="ALTER TABLE `cdt_prof` ADD `date_maj` DATE NOT NULL";
$result_sql_2=mysqli_query($conn_cahier_de_texte, $sql2) or die('Erreur SQL !'.$sql2.mysqli_error($conn_cahier_de_texte)); 
};
  
  
if (!mysqli_query($conn_cahier_de_texte, "SELECT droits from cdt_prof")) {  
$sql3="ALTER TABLE `cdt_prof` ADD `droits` tinyint(3) unsigned NOT NULL default '2'";
$result_sql_3=mysqli_query($conn_cahier_de_texte, $sql3) or die('Erreur SQL !'.$sql3.mysqli_error($conn_cahier_de_texte)); 
};

$sql4="UPDATE `cdt_prof` SET `droits` = '1' WHERE `ID_prof` =1 ";
$result_sql_4=mysqli_query($conn_cahier_de_texte, $sql4) or die('Erreur SQL !'.$sql4.mysqli_error($conn_cahier_de_texte)); 


$sql5="UPDATE cdt_prof SET droits =2 WHERE ID_prof >1 AND droits <>3 AND droits <>4";
$result_sql_5=mysqli_query($conn_cahier_de_texte, $sql5) or die('Erreur SQL !'.$sql5.mysqli_error($conn_cahier_de_texte)); 

if (!mysqli_query($conn_cahier_de_texte, "SELECT date_visa from cdt_agenda")) {  
$sql6="ALTER TABLE `cdt_agenda` ADD `date_visa` date NOT NULL default '0000-00-00'";
$result_sql_6=mysqli_query($conn_cahier_de_texte, $sql6) or die('Erreur SQL !'.$sql6.mysqli_error($conn_cahier_de_texte)); 
};

if (!mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_progression")) {  
$sql7 = "
CREATE TABLE `cdt_progression` (
`ID_progression` tinyint(3) unsigned NOT NULL auto_increment,
`prof_ID` tinyint(4) unsigned NOT NULL default '0',
`titre_progression` varchar(255) NOT NULL default '',
`contenu_progression` text NOT NULL,
PRIMARY KEY (`ID_progression`)
)TYPE=MyISAM AUTO_INCREMENT=1
";
$result_sql_7 = mysqli_query($conn_cahier_de_texte, $sql7)or die('Erreur SQL !'.$sql7.mysqli_error($conn_cahier_de_texte));
};

//------------------maj 3.0.6 -------------------------------------



if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_viescol")) {  
$query1 = "DROP TABLE `cdt_viescol` ";
$result1 = mysqli_query($conn_cahier_de_texte, $query1)or die('Erreur SQL !'.$query1.mysqli_error($conn_cahier_de_texte));
};

if (!mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_message_contenu")) {  
$query2 = "
CREATE TABLE `cdt_message_contenu` (
  `ID_message` smallint(5) NOT NULL auto_increment,
  `message` text NOT NULL,
  `prof_ID` tinyint(4) unsigned NOT NULL default '0',
  `date_envoi` date NOT NULL default '0000-00-00',
  `online` enum('O','N') NOT NULL default 'O',
  `dest_ID` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID_message`)
) TYPE=MyISAM AUTO_INCREMENT=1  
";
$result2 = mysqli_query($conn_cahier_de_texte, $query2)or die('Erreur SQL !'.$query2.mysqli_error($conn_cahier_de_texte));
};


if (!mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_message_destinataire")) {  
$query3 = "
CREATE TABLE `cdt_message_destinataire` (
  `ID_dest` smallint(5) unsigned NOT NULL auto_increment,
  `message_ID` tinyint(3) unsigned NOT NULL default '0',
  `classe_ID` tinyint(3) unsigned NOT NULL default '0',
  `groupe_ID` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID_dest`)
) TYPE=MyISAM AUTO_INCREMENT=1  
";
$result3 = mysqli_query($conn_cahier_de_texte, $query3)or die('Erreur SQL !'.$query3.mysqli_error($conn_cahier_de_texte));
};


if (mysqli_query($conn_cahier_de_texte, "SELECT root_fichier_perso from cdt_prof")) { 
$query4 = "
ALTER TABLE `cdt_prof` DROP `root_fichier_perso` ;
";
$result4 = mysqli_query($conn_cahier_de_texte, $query4)or die('Erreur SQL !'.$query4.mysqli_error($conn_cahier_de_texte));
};

if (!mysqli_query($conn_cahier_de_texte, "SELECT path_fichier_perso from cdt_prof")) {  
$query5 = "
ALTER TABLE `cdt_prof` ADD `path_fichier_perso` VARCHAR( 255 ) 
";
$result5 = mysqli_query($conn_cahier_de_texte, $query5)or die('Erreur SQL !'.$query5.mysqli_error($conn_cahier_de_texte));
};

if (!mysqli_query($conn_cahier_de_texte, "SELECT identite from cdt_prof")) {  
$query6 = "
ALTER TABLE `cdt_prof` ADD `identite` VARCHAR(255) AFTER `passe` 
";
$result6 = mysqli_query($conn_cahier_de_texte, $query6)or die('Erreur SQL !'.$query6.mysqli_error($conn_cahier_de_texte));

$query7 = "
UPDATE `cdt_prof` SET cdt_prof.identite = cdt_prof.nom_prof WHERE cdt_prof.identite=''
";
$result7 = mysqli_query($conn_cahier_de_texte, $query7)or die('Erreur SQL !'.$query7.mysqli_error($conn_cahier_de_texte));

};


if (!mysqli_query($conn_cahier_de_texte, "SELECT email from cdt_prof")) {  
$query8 = "
ALTER TABLE `cdt_prof` ADD `email` VARCHAR( 255 ) AFTER `identite` 
";
$result8 = mysqli_query($conn_cahier_de_texte, $query8)or die('Erreur SQL !'.$query8.mysqli_error($conn_cahier_de_texte));
};




if (!mysqli_query($conn_cahier_de_texte, "SELECT xinha_editlatex from cdt_prof")) {  
$query9 = "
ALTER TABLE `cdt_prof` ADD `xinha_editlatex` enum('O','N') NOT NULL default 'N'
";
$result9 = mysqli_query($conn_cahier_de_texte, $query9)or die('Erreur SQL !'.$query9.mysqli_error($conn_cahier_de_texte));
};

if (!mysqli_query($conn_cahier_de_texte, "SELECT xinha_equation from cdt_prof")) {  
$query10 = "
ALTER TABLE `cdt_prof` ADD `xinha_equation` enum('O','N') NOT NULL default 'N'
";
$result10 = mysqli_query($conn_cahier_de_texte, $query10)or die('Erreur SQL !'.$query10.mysqli_error($conn_cahier_de_texte));
};


if (!mysqli_query($conn_cahier_de_texte, "SELECT xinha_stylist from cdt_prof")) {  
$query11 = "
ALTER TABLE `cdt_prof` ADD `xinha_stylist` enum('O','N') NOT NULL default 'N'
";
$result11 = mysqli_query($conn_cahier_de_texte, $query11)or die('Erreur SQL !'.$query11.mysqli_error($conn_cahier_de_texte));
};

if (!mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) {  
$query12 = "
CREATE TABLE `cdt_params` (
  `param_nom` varchar(32) NOT NULL default '',
  `param_val` text,
  `param_desc` varchar(255) default NULL,
  PRIMARY KEY  (`param_nom`)
) TYPE=MyISAM 
";
$result12 = mysqli_query($conn_cahier_de_texte, $query12)or die('Erreur SQL !'.$query12.mysqli_error($conn_cahier_de_texte));


if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) {  
$query13 = "
INSERT INTO `cdt_params` VALUES ('version', 'Version 3.0.6 standard', 'Version du logiciel')";
$result13 = mysqli_query($conn_cahier_de_texte, $query13)or die('Erreur SQL !'.$query13.mysqli_error($conn_cahier_de_texte));
};


if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) {  
$query14 = "
INSERT INTO `cdt_params` VALUES ('ind_maj_base', '3060', 'indice de la version')";
$result14 = mysqli_query($conn_cahier_de_texte, $query14)or die('Erreur SQL !'.$query14.mysqli_error($conn_cahier_de_texte));
};

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) {  
$query15 = "
INSERT INTO `cdt_params` VALUES ('date_maj_base', '".date("d/m/Y, g:i a")."', 'date de la mise &agrave; jour')";
$result15 = mysqli_query($conn_cahier_de_texte, $query15)or die('Erreur SQL !'.$query15.mysqli_error($conn_cahier_de_texte));
};


};
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
$header_description="Mises &agrave; jour de la base de donn&eacute;es - Ver 3.0.6";
require_once "../../templates/default/header.php";
?>

  <p>&nbsp;</p>
  <p>Les modifications propos&eacute;es &eacute;taient : </p>
  <p align="left">&nbsp;</p>
  <blockquote>
    <blockquote>
      <p align="left">* Ajout des  champs <strong>date_maj, publier_cdt, publier_travail, droits</strong> dans la table <strong>cdt_prof</strong><br>
      * Ajout du  champ <strong>date_visa</strong> dans la table <strong>cdt_agenda</strong><br>
      * Mise &agrave; jour de la valeur <strong>droits=1</strong> pour l'<strong>Administrateur</strong> <br>
      * Mise &agrave; jour de la valeur <strong>droits=2</strong> pour les <strong>enseignants</strong> d&eacute;j&agrave; pr&eacute;sents dans la table <br>
      * Cr&eacute;ation de la table <strong>cdt_progression</strong><br>
      * Cr&eacute;ation des tables <strong>cdt_message_contenu</strong>, <strong>cdt_message_destinataire</strong>, <strong>cdt_params</strong><br>
      * Suppression de l'ancienne table <strong>cdt_viescol </strong><br>
      * Le champ <strong>identite</strong> re&ccedil;oit la valeur du champ nom_prof par d&eacute;faut. <br>
      * Ajout des champs suivants dans la table cdt_prof :<br>
      	<strong>root_fichier_perso , path_fichier_perso, identite, <br>
        email,  xinha_editlatex, xinha_equation, xinha_stylist</strong> <br>
        <br>
      </p>
    </blockquote>
  </blockquote>

  Si aucun message d'erreur n'est apparu, les mises &agrave; jour ont &eacute;t&eacute; r&eacute;alis&eacute;es.
  <p><a href="maj.php">Continuer</a></p>
  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
