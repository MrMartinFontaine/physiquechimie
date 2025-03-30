<?php 
//modifier pour chaque nouvelle version
$indice_version='5510'; 
$libelle_version='Version 5.5.1.0 Standard';
//---------------------------------


$message_erreur_sql_1= '
<br /><br /><div style="border: 1px solid #000000;"><p >&nbsp;</p>
<p style="text-align:center;color: #0000FF;	font-size: large;"><strong>L\'application Cahier de textes est momentan&eacute;ment indisponible.</strong></p><br /><p style="text-align:center;" >Une erreur SQL a &eacute;t&eacute; rencontr&eacute;e &agrave; la mise &agrave; jour de la base de donn&eacute;es lors de l\'installation de la derni&egrave;re version.</p><p style="text-align:center;" >';
$message_erreur_sql_2='<br /></p><p style="text-align:center;">Contacter l\'administrateur pour corriger ce probl&egrave;me (Voir Faq). </p><p >&nbsp;</p></div>';



mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")){

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsparams = "SELECT param_val FROM cdt_params WHERE param_nom='version'";
$Rsparams = mysqli_query($conn_cahier_de_texte, $query_Rsparams) or die('Erreur SQL !'.$query_Rsparams.'<br>'.mysqli_error($conn_cahier_de_texte));
$row_Rsparams = mysqli_fetch_assoc($Rsparams);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsparams2 = "SELECT param_val FROM cdt_params WHERE param_nom='date_maj_base'";
$Rsparams2 = mysqli_query($conn_cahier_de_texte, $query_Rsparams2) or die('Erreur SQL !'.$query_Rsparams2.'<br>'.mysqli_error($conn_cahier_de_texte));
$row_Rsparams2 = mysqli_fetch_assoc($Rsparams2);
$totalRows_Rsparams2 = mysqli_num_rows($Rsparams2);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsparams3 = "SELECT param_val FROM cdt_params WHERE param_nom='ind_maj_base'";
$Rsparams3 = mysqli_query($conn_cahier_de_texte, $query_Rsparams3) or die('Erreur SQL !'.$query_Rsparams3.'<br>'.mysqli_error($conn_cahier_de_texte));
$row_Rsparams3 = mysqli_fetch_assoc($Rsparams3);

};

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")){
 //echo $row_Rsparams['param_val'] . ' de la base de donn&eacute;es install&eacute;e';
 if ($totalRows_Rsparams2==1) {
 //echo '  mise &agrave; jour effectu&eacute;e le ' .$row_Rsparams2['param_val'] ;
 }
 else {
 	$query = "INSERT INTO `cdt_params` VALUES ('date_maj_base', NULL, 'date de la mise &agrave; jour')";
 	$result = mysqli_query($conn_cahier_de_texte, $query);
 	$query = "UPDATE `cdt_params` SET `param_val` = '".date("d/m/Y, g:i a")."' WHERE `param_nom` ='date_maj_base'";
	$result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.'<br>'.mysqli_error($conn_cahier_de_texte));
	echo '. Date de mise &agrave; jour initialis&eacute;e &agrave; aujourd\'hui.' ;
 };
 };



//mise à jour 3.09

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<3090)) {echo 'Vous devez effectuer la mise &agrave; jour 3.0.9  <br /><br />
Par prudence, faites pr&eacute;alablement une sauvegarde de votre base de donn&eacute;es (voir menu Administrateur)<br /><br /><br /><strong><a href="maj309_txt.php">Faire cette mise &agrave; jour 3.0.9</a></strong> <br /><br />'; }

//mise à jour 4.00

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4000)) {
$query ="SELECT * from cdt_params WHERE `param_nom` ='old_cdt_access'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$row_result = mysqli_fetch_assoc($result);
$totalRows_result = mysqli_num_rows($result);

if($totalRows_result==0){
$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ( 'old_cdt_access', 'Non', 'Acces ancien cahier de textes' )";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_type_activite` CHANGE `ID_activite` `ID_activite` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT ";
$result = mysqli_query($conn_cahier_de_texte, $query);
}

//mise à jour 4.0.0.d

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4004)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_agenda` CHANGE `matiere_ID` `matiere_ID` SMALLINT( 5 ) UNSIGNED DEFAULT '0' NOT NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_emploi_du_temps` CHANGE `matiere_ID` `matiere_ID` SMALLINT( 5 ) UNSIGNED DEFAULT NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_matiere` CHANGE `ID_matiere` `ID_matiere` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_travail` CHANGE `matiere_ID` `matiere_ID` SMALLINT( 5 ) UNSIGNED DEFAULT '0' NOT NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_travail` CHANGE `classe_ID` `classe_ID` TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_prof` ADD UNIQUE (`nom_prof`) ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.0.0.4 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4004' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
}


//mise à jour 4.0.0.f

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4006)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_progression` CHANGE `contenu_progression` `contenu_progression` TEXT DEFAULT NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.0.0.6 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4006' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
}


//mise à jour 4.1.0

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4100)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query ="SELECT * from cdt_params WHERE `param_nom` ='pp_diffusion'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$row_result = mysqli_fetch_assoc($result);
$totalRows_result = mysqli_num_rows($result);
if($totalRows_result==0){
$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ('pp_diffusion', 'Oui', 'Autoriser les profs principaux &agrave; diffuser un message aux coll&egrave;gues')";
$result = mysqli_query($conn_cahier_de_texte, $query);
};


mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
 if (!mysqli_query($conn_cahier_de_texte, "SELECT pp_classe_ID from cdt_message_contenu ")){
$query = "ALTER  TABLE`cdt_message_contenu` ADD `pp_classe_ID` SMALLINT( 5 ) NOT NULL ;";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
if (!mysqli_query($conn_cahier_de_texte, "SELECT pp_groupe_ID from cdt_message_contenu ")){
$query = "ALTER  TABLE`cdt_message_contenu` ADD `pp_groupe_ID` SMALLINT( 5 ) NOT NULL ;";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
 if (!mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_message_destinataire_profs;")){
$query = "
CREATE TABLE IF NOT EXISTS `cdt_message_destinataire_profs` (
  `ID_dest` smallint(5) unsigned NOT NULL auto_increment,
  `message_ID` tinyint(3) unsigned NOT NULL default '0',
  `prof_ID` tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID_dest`)
) ENGINE=MyISAM AUTO_INCREMENT=1
";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.1.0.0 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4100' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
}


//mise à jour 4.2.0

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4201)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
 if (!mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_groupe_interclasses;")){
$query = "
CREATE TABLE IF NOT EXISTS `cdt_groupe_interclasses` (
  `ID_gic` smallint(5) unsigned NOT NULL auto_increment,
  `prof_ID` tinyint(4) unsigned NOT NULL default '0',
  `nom_gic` varchar(255) NOT NULL default '',
  `commentaire_gic` text,
  PRIMARY KEY  (`ID_gic`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
 if (!mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_groupe_interclasses_classe;")){
$query = "
CREATE TABLE IF NOT EXISTS `cdt_groupe_interclasses_classe` (
  `ID_gic_classe` smallint(5) unsigned NOT NULL auto_increment,
  `gic_ID` smallint(5) unsigned NOT NULL default '0',
  `classe_ID` tinyint(3) unsigned NOT NULL default '0',
  `groupe_ID` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID_gic_classe`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;
";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_emploi_du_temps` ADD `gic_ID` smallint(5) unsigned NOT NULL default '0' AFTER `classe_ID`";
$result = mysqli_query($conn_cahier_de_texte, $query);


mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_emploi_du_temps` ADD `edt_exist_debut` date NOT NULL default '0000-00-00'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_emploi_du_temps` ADD `edt_exist_fin` date NOT NULL default '2100-00-00'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_emploi_du_temps` ADD `couleur_cellule` varchar(7) default NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_emploi_du_temps` ADD `couleur_police` varchar(7) default NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_agenda` ADD `gic_ID` smallint(5) unsigned NOT NULL default '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_travail` ADD `gic_ID` smallint(5) unsigned NOT NULL default '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` VALUES ('libelle_devoir', 'DEVOIR', 'Libelle attribue par defaut aux (devoirs,controles,ds...)')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query ="SELECT * from cdt_params WHERE `param_nom` ='nom_etab'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$row_result = mysqli_fetch_assoc($result);
$totalRows_result = mysqli_num_rows($result);

if($totalRows_result==0){

//On protege - présence éventuelle d'apostrophes 

$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ('nom_etab', '".mysqli_real_escape_string($conn_cahier_de_texte, $nom_etab)."', 'Nom de l\'&eacute;tablissement')";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query ="SELECT * from cdt_params WHERE `param_nom` ='url_etab'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$row_result = mysqli_fetch_assoc($result);
$totalRows_result = mysqli_num_rows($result);
if($totalRows_result==0){
$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ('url_etab', '$url_etab', 'Adresse Web de l\'etablissement')";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query ="SELECT * from cdt_params WHERE `param_nom` ='url_logo_etab'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$row_result = mysqli_fetch_assoc($result);
$totalRows_result = mysqli_num_rows($result);
if($totalRows_result==0){
$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ('url_logo_etab', '$url_logo_etab', 'Adresse Web du logo de l\'etablissement')";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query ="SELECT * from cdt_params WHERE `param_nom` ='session_timeout'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$row_result = mysqli_fetch_assoc($result);
$totalRows_result = mysqli_num_rows($result);
if($totalRows_result==0){
$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ('session_timeout', '3600', 'Temps de cloture de session en cas d\'inactvite;')";
$result = mysqli_query($conn_cahier_de_texte, $query);
};


mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query ="SELECT * from cdt_params WHERE `param_nom` ='cdt_LDAP'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$row_result = mysqli_fetch_assoc($result);
$totalRows_result = mysqli_num_rows($result);
if($totalRows_result==0){
$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ('cdt_LDAP', 'Non', 'Version LDAP du CDT ?')";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query ="SELECT * from cdt_params WHERE `param_nom` ='cdt_LCS'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$row_result = mysqli_fetch_assoc($result);
$totalRows_result = mysqli_num_rows($result);
if($totalRows_result==0){
$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ('cdt_LCS', 'Non', 'Plugin LCS du CDT ?')";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "
CREATE TABLE IF NOT EXISTS `cdt_plages_horaires` (
  `ID_plage` tinyint(3) unsigned NOT NULL,
  `h1` char(2) default NULL,
  `mn1` char(2) default NULL,
  `h2` char(2) default NULL,
  `mn2` char(2) default NULL,
  PRIMARY KEY  (`ID_plage`)
) ENGINE=MyISAM";
$result = mysqli_query($conn_cahier_de_texte, $query);



$query = "INSERT INTO `cdt_plages_horaires` VALUES (1, '08', '00', '09', '00')";$result= mysqli_query($conn_cahier_de_texte, $query);
$query = "INSERT INTO `cdt_plages_horaires` VALUES (2, '09', '00', '10', '00')";$result= mysqli_query($conn_cahier_de_texte, $query);
$query = "INSERT INTO `cdt_plages_horaires` VALUES (3, '10', '00', '11', '00')";$result= mysqli_query($conn_cahier_de_texte, $query);
$query = "INSERT INTO `cdt_plages_horaires` VALUES (4, '11', '00', '12', '00')";$result= mysqli_query($conn_cahier_de_texte, $query);
$query = "INSERT INTO `cdt_plages_horaires` VALUES (5, '12', '00', '13', '00')";$result= mysqli_query($conn_cahier_de_texte, $query);
$query = "INSERT INTO `cdt_plages_horaires` VALUES (6, '13', '00', '14', '00')";$result= mysqli_query($conn_cahier_de_texte, $query);
$query = "INSERT INTO `cdt_plages_horaires` VALUES (7, '14', '00', '15', '00')";$result= mysqli_query($conn_cahier_de_texte, $query);
$query = "INSERT INTO `cdt_plages_horaires` VALUES (8, '15', '00', '16', '00')";$result= mysqli_query($conn_cahier_de_texte, $query);
$query = "INSERT INTO `cdt_plages_horaires` VALUES (9, '16', '00', '17', '00')";$result= mysqli_query($conn_cahier_de_texte, $query);
$query = "INSERT INTO `cdt_plages_horaires` VALUES (10, '17', '00', '18', '00')";$result= mysqli_query($conn_cahier_de_texte, $query);
$query = "INSERT INTO `cdt_plages_horaires` VALUES (11, '18', '00', '19', '00')";$result= mysqli_query($conn_cahier_de_texte, $query);
$query = "INSERT INTO `cdt_plages_horaires` VALUES (12, '19', '00', '20', '00')";$result= mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.2.0.1 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4201' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
}

//mise à jour 4.2.0.4

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4204)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` )VALUES ('visa_stop_edition', 'Non', 'Interdit la modification des fiches saisies si leur date est anterieure &agrave; la date du visa')";$result= mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.2.0.4 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4204' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
}

//mise à jour 4.4.2.0
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4420)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_prof` CHANGE `ID_prof` `ID_prof` SMALLINT( 4 ) UNSIGNED NOT NULL AUTO_INCREMENT";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_agenda` CHANGE `prof_ID` `prof_ID` SMALLINT( 4 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_emploi_du_temps` CHANGE `prof_ID` `prof_ID` SMALLINT( 4 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_evenement_contenu` CHANGE `prof_ID` `prof_ID` SMALLINT( 4 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_fichiers_joints` CHANGE `prof_ID` `prof_ID` SMALLINT( 4 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_groupe_interclasses` CHANGE `prof_ID` `prof_ID` SMALLINT( 4 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_message_contenu` CHANGE `prof_ID` `prof_ID` SMALLINT( 4 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_message_destinataire_profs` CHANGE `prof_ID` `prof_ID` SMALLINT( 4 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_progression` CHANGE `prof_ID` `prof_ID` SMALLINT( 4 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_travail` CHANGE `prof_ID` `prof_ID` SMALLINT( 4 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_type_activite` CHANGE `ID_prof` `ID_prof` SMALLINT( 4 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_prof` ADD `stop_cdt` ENUM( 'O', 'N' ) NOT NULL DEFAULT 'N' AFTER `publier_cdt`";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.4.2.0 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4420' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
} 

// //mise à jour 4.5.0.1
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4501)) {


mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_classe` ADD `code_classe` VARCHAR( 20 ) default NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_matiere` ADD `code_matiere` VARCHAR( 20 ) default NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_groupe_interclasses` ADD `code_gic` VARCHAR( 20 ) default NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_groupe` ADD `code_groupe` VARCHAR( 20 ) default NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE `cdt_prof` ADD `acces_rapide` ENUM( 'O', 'N' ) NOT NULL DEFAULT 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.5.0.1 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4501' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
}


// //mise à jour 4.6.0.0
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4600)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE `cdt_agenda` ADD `edt_modif` ENUM( 'O', 'N' ) NOT NULL DEFAULT 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ( 'url_deconnecte_eleve', 'index.php', 'Url de sortie apres deconnexion eleve du cdt')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ( 'url_deconnecte_prof', 'index.php', 'Url de sortie apres deconnexion prof du cdt')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` (`param_nom` ,`param_val` ,`param_desc`) VALUES ('module_absence', 'Non', 'Activation du module absence')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.6.0.0 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4600' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

// //mise à jour 4.6.1.0

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4610)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE `cdt_travail` ADD `charge` varchar(10) DEFAULT NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.6.1.0 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4610' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
};



// //mise à jour 4.7.0.0

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4700)) {
	
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "
CREATE TABLE IF NOT EXISTS `cdt_archive` (
	`NumArchive` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
	`NomArchive` VARCHAR( 255 ) NOT NULL ,
	`DateArchive` DATE NOT NULL ,
	PRIMARY KEY ( `NumArchive` )
) ENGINE=MyISAM" ;
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` (`param_nom` ,`param_val` ,`param_desc`) VALUES ('affichage_compteur', 'Non', 'Affiche ou non un compteur ultra simpliste')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` (`param_nom` ,`param_val` ,`param_desc`) VALUES ('compteur', '0', '	Le compteur de consulter')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` (`param_nom` ,`param_val` ,`param_desc`) VALUES ('date_raz_compteur', '00000000', '	Date de remise a zero du compteur')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.7.0.0 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4700' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

// //mise à jour 4.7.3.0
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4730)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` (`param_nom`,`param_val`,`param_desc`) VALUES ('Maj_Archives', 'Oui', 'MAJ des Archives a faire apres version 4.72')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.7.3.0 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4730' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);




};





// //mise à jour 4.7.4.0
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4740)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE `cdt_groupe` ADD UNIQUE (`groupe`)";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE `cdt_groupe` ADD UNIQUE (`code_groupe`)";
$result = mysqli_query($conn_cahier_de_texte, $query);


mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "
CREATE TABLE IF NOT EXISTS `cdt_edt` (
  `ID_emploi` smallint(5) unsigned NOT NULL auto_increment,
  `prof_ref` varchar(255) default NULL,
  `jour_semaine` enum('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi') default NULL,
  `semaine` enum('A','B','A et B') default NULL,
  `heure` tinyint(2) unsigned default NULL,
  `classe_ID` tinyint(3) unsigned default NULL,
  `matiere_ID` smallint(5) unsigned default NULL,
  `heure_debut` varchar(255) default NULL,
  `heure_fin` varchar(255) default NULL,
  `couleur_cellule` varchar(255) default NULL,
  `IdentiteProf` varchar(255) default NULL,
  `groupe` varchar(255) NOT NULL default 'Classe entière',
  PRIMARY KEY  (`ID_emploi`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.7.4.0 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4740' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

// //mise à jour 4.7.5.0
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4750)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` (`param_nom` ,`param_val` ,`param_desc`) VALUES ('modif_passe', 'Oui', 'Enseignant peut changer son mot de passe')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` VALUES ('Publication_Import', 'Oui', 'Autorisation de publier l\'import des edt')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` VALUES ('Import', '', 'Emploi du temps importe')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.7.5.0 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4750' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
};


// //mise à jour 4.7.6.0  (oubli dans la version precedente)
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4760)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_prof` ADD `Num_Import` SMALLINT UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query) ;

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE `cdt_emploi_du_temps` ADD `ImportEDT` VARCHAR( 255 ) NOT NULL DEFAULT 'NON'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE `cdt_emploi_du_temps` ADD `ID_Import` SMALLINT UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE `cdt_emploi_du_temps` CHANGE `couleur_cellule` `couleur_cellule` VARCHAR( 7 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '#CAFDBD',CHANGE `couleur_police` `couleur_police` VARCHAR( 7 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '#000000'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.7.6.0 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4760' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

// //mise à jour 4.7.7.0 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4770)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_groupe_interclasses` SET nom_gic=replace(nom_gic, '/', '_')";
$result = mysqli_query($conn_cahier_de_texte, $query) ;

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_classe` SET nom_classe=replace(nom_classe, \"'\", \"-\")";
$result = mysqli_query($conn_cahier_de_texte, $query) ;

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_groupe` SET groupe=replace(groupe, \"'\", \"-\")";
$result = mysqli_query($conn_cahier_de_texte, $query) ;

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_agenda` SET groupe=replace(groupe, \"'\", \"-\")";
$result = mysqli_query($conn_cahier_de_texte, $query) ;

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_travail` SET groupe=replace(groupe, \"'\", \"-\")";
$result = mysqli_query($conn_cahier_de_texte, $query) ;

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_travail` SET t_groupe=replace(t_groupe, \"'\", \"-\")";
$result = mysqli_query($conn_cahier_de_texte, $query) ;

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_matiere` SET nom_matiere=replace(nom_matiere, \"'\", \"-\")";
$result = mysqli_query($conn_cahier_de_texte, $query) ;

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.7.7.0 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4770' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);

};


//mise à jour 4.8.0.0 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4800)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_prof` ADD `afficher_messages` ENUM( 'O', 'N' ) NOT NULL DEFAULT 'O' AFTER `acces_rapide`";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER  TABLE`cdt_prof` ADD `message_invite` ENUM( 'O', 'N' ) NOT NULL DEFAULT 'N' AFTER `acces_rapide`";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE  `cdt_invite` (
  `ID_invite` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `prof_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `classe_ID` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `gic_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `matiere_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `NumArchive` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID_invite`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
$result= mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.8.0.0 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4800' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

// //mise à jour 4.8.3.0 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4830)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` (`param_nom` ,`param_val` ,`param_desc`) VALUES ('libelle_semaine', '0', 'Libelle de la semaine 0 = A et B  & 1= Paire et impaire ')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_agenda` CHANGE `rq` `rq` TEXT"; 
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Version 4.8.3.0 standard ' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '4830' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

// mise à jour 4.8.4.0 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4840)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` (`param_nom` ,`param_val` ,`param_desc`) VALUES ('modif_login', 'Non', 'Enseignant peut changer son login')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_prof` ADD `email_diffus_restreint` ENUM( 'O', 'N' ) NOT NULL DEFAULT 'N' AFTER `email` ";
$result = mysqli_query($conn_cahier_de_texte, $query);

};


// mise à jour 4.8.5.0 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4850)) {

if ((isset($_SESSION['module_absence']))&& ($_SESSION['module_absence']=='Oui')) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "
CREATE TABLE IF NOT EXISTS `ele_liste` (
`ID_ele` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
`nom_ele` varchar(50) NOT NULL,
`prenom_ele` varchar(50) NOT NULL,
`classe_ele` varchar(20) NOT NULL,
PRIMARY KEY (`ID_ele`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `ele_absent` (
`ID` smallint(10) unsigned NOT NULL auto_increment,
`classe_ID` tinyint(4) unsigned default NULL,
`classe` varchar(50) default NULL,
`groupe` varchar(50) default NULL,
`heure` tinyint(4) default NULL,
`heure_debut` varchar(50) default NULL,
`heure_fin` varchar(50) default NULL,
`code_date` varchar(255) default NULL,
`salle` varchar(50) default NULL,
`jour_pointe` varchar(50) default NULL,
`eleve_ID` smallint(4) default NULL,
`prof_ID` smallint(4) default NULL,
`motif` varchar(20) default NULL,
PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `ele_absent` ADD vie_sco_statut tinyint(4) default '0' ";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query ="
CREATE TABLE IF NOT EXISTS `ele_gic` (
  `ID_ele_gic` smallint(10) unsigned NOT NULL auto_increment,
  `ID_ele` smallint(5) unsigned NOT NULL default '0',
  `ID_gic` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID_ele_gic`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
$result = mysqli_query($conn_cahier_de_texte, $query);
};
};



// mise à jour 4.8.9.0 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4890)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_emploi_du_temps` CHANGE `jour_semaine` `jour_semaine` ENUM( 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche' )";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_edt` CHANGE `jour_semaine` `jour_semaine` ENUM( 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche' )";
$result = mysqli_query($conn_cahier_de_texte, $query);

};

// mise à jour 4.8.9.1 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4891)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` (`param_nom`, `param_val`, `param_desc`) VALUES ('facebook_icon', 'Oui', 'Affichage icone facebook');";
$result = mysqli_query($conn_cahier_de_texte, $query);



};


// mise à jour 4.8.9.3 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4893)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` VALUES ('site_ferme', 'Non', 'Etat du CDT Non=ouvert Oui=ferme')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` VALUES ('devoir_planif', 'Oui', 'Autoriser la planification de devoirs en dehors des heures de cours')";
$result = mysqli_query($conn_cahier_de_texte, $query);

};

// mise à jour 4.8.9.5 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4895)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_message_destinataire_profs` CHANGE `message_ID` `message_ID` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0' ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_message_destinataire` CHANGE `message_ID` `message_ID` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0' ";
$result = mysqli_query($conn_cahier_de_texte, $query);

};


// mise à jour 4.9.0.0 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4900)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_prof` ADD `lien_invite_dir` VARCHAR( 255 ) NOT NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_prof` ADD `datefin_invite_dir` DATE NOT NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_prof` ADD `lien_invite_prof` VARCHAR( 255 ) NOT NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_prof` ADD `datefin_invite_prof` DATE NOT NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_message_contenu` ADD `date_fin_publier` DATE NOT NULL AFTER `date_envoi` ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` VALUES ('pp_multiclass', 'Oui', 'Autoriser un prof a etre PP de plusieurs classes')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` VALUES ('pp_groupe', 'Oui', 'Autoriser un prof a etre PP d un groupe')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_travail` ADD `eval` ENUM( 'O', 'N' ) NOT NULL DEFAULT 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_type_activite` ADD `couleur_activite` VARCHAR( 7 ) NOT NULL DEFAULT '#000066'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_agenda` ADD `couleur_activ` VARCHAR( 7 ) NOT NULL DEFAULT '#000066' AFTER `type_activ`";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` VALUES ('prof_mess', 'Non', 'Autoriser la publication de messages par tous les enseignants')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_agenda` ADD `partage` ENUM( 'O', 'N' ) NOT NULL DEFAULT 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` VALUES ('date_debut_annee', NULL, 'Date de debut annee scolaire')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` VALUES ('date_fin_annee', NULL, 'Date de fin annee scolaire')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "CREATE TABLE IF NOT EXISTS `cdt_emploi_du_temps_partage` (
  `Partage_ID` smallint(5) NOT NULL auto_increment,
  `ID_emploi` smallint(5) NOT NULL,
  `profpartage_ID` smallint(5) NOT NULL,
  PRIMARY KEY  (`Partage_ID`)
) ENGINE=MyISAM  ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_agenda` ADD `emploi_ID` smallint(5) unsigned NOT NULL default '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);


mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "CREATE TABLE IF NOT EXISTS `cdt_message_modif` (
   `ID_modif` smallint(5) NOT NULL auto_increment,
   `ID_message` smallint(5) NOT NULL,
   `prof_ID` smallint(5) unsigned NOT NULL default '0',
   `date_envoi` date NOT NULL default '0000-00-00',
   PRIMARY KEY  (`ID_modif`)
) ENGINE=MyISAM AUTO_INCREMENT=1";
$result = mysqli_query($conn_cahier_de_texte, $query);


mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_groupe` CHANGE `ID_groupe` `ID_groupe` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_message_destinataire` CHANGE `groupe_ID` `groupe_ID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_destinataire` CHANGE `groupe_ID` `groupe_ID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_groupe_interclasses_classe` CHANGE `groupe_ID` `groupe_ID` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT";
$result = mysqli_query($conn_cahier_de_texte, $query);

};

// mise à jour 4.9.0.1 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4901)) {

$query = "INSERT IGNORE INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ( 'affichage_compteur', 'Non', 'Affiche ou non un compteur ultra simpliste')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT IGNORE INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ( 'compteur', '0', 'Le compteur de consulter')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT IGNORE INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ( 'date_raz_compteur', '20100311', 'Date de remise a zero du compteur')";
$result = mysqli_query($conn_cahier_de_texte, $query);

};

// mise à jour 4.9.0.2 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4902)) {
$query = "ALTER TABLE `cdt_progression` CHANGE `contenu_progression` `contenu_progression` LONGTEXT NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

// mise à jour 4.9.0.3 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4903)) {
$query = "INSERT INTO `cdt_params` (`param_nom` ,`param_val` ,`param_desc`) VALUES ('time_zone', 'Europe/Paris', 'Zone de temps de votre localisation géographique')";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

// mise à jour 4.9.0.4 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4904)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);

$query = "ALTER TABLE `cdt_prof` ADD `id_remplace` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "ALTER TABLE `cdt_prof` ADD `id_etat` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "ALTER TABLE `cdt_classe` CHANGE `passe_classe` `passe_classe` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);

//Crypter le mot de passe de la classe tout en evitant le recryptage

$query = "UPDATE `cdt_classe` SET `passe_classe` = md5(`passe_classe`) WHERE length(passe_classe)<32 AND `passe_classe`!='' AND `passe_classe` IS NOT NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "UPDATE `cdt_classe` SET `passe_classe` = MD5( '' ) WHERE `passe_classe` = '' OR `passe_classe` IS NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "ALTER TABLE `cdt_classe` CHANGE `ID_classe` `ID_classe` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT ";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "ALTER TABLE `cdt_plages_horaires` CHANGE `ID_plage` `ID_plage` SMALLINT( 5 ) UNSIGNED NOT NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);


};

// mise à jour 4.9.0.5 
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4905)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);


$query = "CREATE TABLE IF NOT EXISTS `cdt_remplacement` (
  `ID_remplace` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `titulaire_ID` smallint(5) NOT NULL,
  `remplacant_ID` smallint(5) NOT NULL,
  `date_debut_remplace` date NOT NULL DEFAULT '0000-00-00',
  `date_fin_remplace` date NOT NULL DEFAULT '0000-00-00',
  `date_creation_remplace` date NOT NULL DEFAULT '0000-00-00',
  `ref_debut_agenda_ID` smallint(5) unsigned DEFAULT NULL,
  `ref_fin_agenda_ID` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID_remplace`)
) ENGINE=MyISAM  AUTO_INCREMENT=1";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "ALTER TABLE `cdt_prof` ADD `date_declare_absent` date NOT NULL DEFAULT '0000-00-00'";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "ALTER TABLE `cdt_emploi_du_temps` ADD `fusion_gic` enum('O','N') NOT NULL DEFAULT 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "ALTER TABLE `cdt_emploi_du_temps` ADD `verrou_remplace` smallint(5) NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

}


// mise à jour 4.9.0.6
//suite a un oubli d'effectuer un mysqli_query dans la version 4904, on le refait
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4906)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);

$query = "UPDATE `cdt_classe` SET `passe_classe` = md5(`passe_classe`) WHERE length(passe_classe)<32 AND `passe_classe`!='' AND `passe_classe` IS NOT NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);

}



// mise à jour 4.9.0.7
//remettre certains code_date relatifs aux vacances au format standard des autres code_date yyyymmdd0
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4907)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE cdt_agenda SET code_date= CONCAT(SUBSTRING( code_date, 7, 4 ),SUBSTRING( code_date, 4, 2 ),SUBSTRING( code_date, 1, 2 ),0) WHERE SUBSTRING( code_date, 3, 1 ) = '/'";
$result = mysqli_query($conn_cahier_de_texte, $query);
}

// mise à jour 4.9.0.8
//Changement d'adresse du serveur Latex - suppression de "dreamhost" dans l'url
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4908)) {
	// Modifier cdt_agenda 
	mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
    $requete="UPDATE cdt_agenda SET activite=REPLACE(activite,'%dreamhost%','') WHERE activite LIKE '%dreamhost%'"; 
    $Resultat = mysqli_query($conn_cahier_de_texte, $requete) or die(mysqli_error($conn_cahier_de_texte)); 

	// Modifier archives de cdt_agenda
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); 
    $query_RsArchiv0 = "SELECT NumArchive FROM cdt_archive"; 
    $RsArchiv0 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv0) or die(mysqli_error($conn_cahier_de_texte)); 

	
	while ($row_RsArchiv0 = mysqli_fetch_assoc($RsArchiv0)) { 
	mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
    $table_archive="cdt_agenda_save".$row_RsArchiv0['NumArchive'];

	//if (mysqli_table_exists($table_archive, $database_conn_cahier_de_texte)==1){
	

        $requete="UPDATE ".$table_archive." SET activite=REPLACE(activite,'%dreamhost%','') WHERE activite LIKE '%dreamhost%'"; 
		
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); 
        $Resultat = mysqli_query($conn_cahier_de_texte, $requete) or die(mysqli_error($conn_cahier_de_texte)); 
	//};	

	
	
	}; //du while archive agenda
	
	// Modifier cdt_travail 
	mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
    $requete="UPDATE cdt_travail SET travail=REPLACE(travail,'%dreamhost%','') WHERE travail LIKE '%dreamhost%'"; 
	
    $Resultat = mysqli_query($conn_cahier_de_texte, $requete) or die(mysqli_error($conn_cahier_de_texte)); 	
	
	// Modifier archives de cdt_travail
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); 
    $query_RsArchiv1 = "SELECT NumArchive FROM cdt_archive"; 
    $RsArchiv1 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv1) or die(mysqli_error($conn_cahier_de_texte));
 
	while ($row_RsArchiv1 = mysqli_fetch_assoc($RsArchiv1)) { 
	mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
	$table_archive="cdt_travail_save".$row_RsArchiv1['NumArchive'];
	
    //if(mysqli_table_exists($table_archive, $database_conn_cahier_de_texte)==1){

        $requete="UPDATE ".$table_archive." SET travail=REPLACE(travail,'%dreamhost%','') WHERE travail LIKE '%dreamhost%'";
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); 
        $Resultat = mysqli_query($conn_cahier_de_texte, $requete) or die(mysqli_error($conn_cahier_de_texte)); 
	//};	
	}; //du while archive travail
	
	mysqli_free_result($RsArchiv0);mysqli_free_result($RsArchiv1);
	
	//choix d'affichage travail a faire en page de saisie
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); 
	$query = "ALTER TABLE `cdt_prof` ADD `type_affich` TINYINT NOT NULL DEFAULT '1'";
	$result = mysqli_query($conn_cahier_de_texte, $query);
	
}


// mise à jour 4.9.1.0

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4910)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
// Modifier archives de cdt_agenda
    mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); 
    $query_RsArchiv0 = "SELECT NumArchive FROM cdt_archive"; 
    $RsArchiv0 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv0) or die(mysqli_error($conn_cahier_de_texte));
    
    while ($row_RsArchiv0 = mysqli_fetch_assoc($RsArchiv0)) { 
        $table_archive="cdt_agenda_save".$row_RsArchiv0['NumArchive'];
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); 
		$query_p = "SHOW COLUMNS FROM $table_archive LIKE 'partage'";
		$result = mysqli_query($conn_cahier_de_texte, $query_p) or die(mysqli_error($conn_cahier_de_texte));
		
		if(mysqli_num_rows($result) == 0) {
		$requete="ALTER TABLE  ".$table_archive." ADD `partage` ENUM( 'O', 'N' ) NOT NULL DEFAULT 'N'"; 
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); 
        $Resultat = mysqli_query($conn_cahier_de_texte, $requete); 
		}
    };    
    mysqli_free_result($RsArchiv0);
	
//pour synchro avec version LCS	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); 
	$query = "ALTER TABLE `cdt_prof` ADD `PrimoConn` ENUM( 'O', 'N' ) NOT NULL DEFAULT 'N'";
	$result = mysqli_query($conn_cahier_de_texte, $query);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); 
	$query = "INSERT INTO `cdt_params` VALUES ('MAJ_Latex_4909', 'Non', 'MAJ effectuee pour Latex ?')";
	$result = mysqli_query($conn_cahier_de_texte, $query);	

}

// mise à jour 4.9.1.1
//pour synchro avec version LCS	
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4911)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` VALUES ('MAJ_Partage_4911', 'Non', 'MAJ effectuee pour Partage dans Archives ?')";
$result = mysqli_query($conn_cahier_de_texte, $query);
}


// mise à jour 4.9.1.2
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4912)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "CREATE TABLE IF NOT EXISTS `cdt_niveau` (
  `ID_niv` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nom_niv` varchar(255) NOT NULL DEFAULT '',
  `commentaire_niv` text,
  PRIMARY KEY (`ID_niv`)
) ENGINE=MyISAM  AUTO_INCREMENT=1";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "CREATE TABLE IF NOT EXISTS `cdt_niveau_classe` (
  `ID_niv_classe` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `niv_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `classe_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `groupe_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID_niv_classe`)
) ENGINE=MyISAM  AUTO_INCREMENT=1";
$result = mysqli_query($conn_cahier_de_texte, $query);


mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_prof` SET `acces_rapide` = 'O'";
$result = mysqli_query($conn_cahier_de_texte, $query);
}



// mise à jour 4.9.1.3
//pour synchro avec version LCS	
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4913)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Oui' WHERE `param_nom`  = 'MAJ_Partage_4911'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = 'Oui' WHERE `param_nom`  = 'MAJ_Latex_4909'";
$result = mysqli_query($conn_cahier_de_texte, $query);
}


// mise à jour 4.9.1.4
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4914)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` VALUES ('acces_inspection_all_cdt', 'Non', 'Acces systematique aux cahiers par les inspecteurs')";
$result = mysqli_query($conn_cahier_de_texte, $query);
}

// mise à jour 4.9.1.5
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4915)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_travail` CHANGE `agenda_ID` `agenda_ID` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);
}

// mise à jour 4.9.1.6
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4916)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_agenda` CHANGE `ID_agenda` `ID_agenda` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_travail` CHANGE `agenda_ID` `agenda_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_fichiers_joints` CHANGE `agenda_ID` `agenda_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_remplacement` CHANGE `ref_debut_agenda_ID` `ref_debut_agenda_ID` mediumint(8) UNSIGNED DEFAULT NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_remplacement` CHANGE `ref_fin_agenda_ID` `ref_debut_agenda_ID` mediumint(8) UNSIGNED DEFAULT NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);

}

// mise à jour 4.9.1.8
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4918)) {
if ((isset($_SESSION['module_absence']))&& ($_SESSION['module_absence']=='Oui')) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `ele_absent` ADD `retard` enum('O','N') DEFAULT 'N' ";
$result = mysqli_query($conn_cahier_de_texte, $query);
};
}

// mise à jour 4.9.1.9
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4919)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_message_contenu` CHANGE `date_envoi` `date_envoi` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_message_modif` CHANGE `date_envoi` `date_envoi` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'";
$result = mysqli_query($conn_cahier_de_texte, $query);

}

// mise à jour 4.9.2.2
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4922)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_remplacement` SET `ref_fin_agenda_ID`=0 WHERE isnull( `ref_fin_agenda_ID` )";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_remplacement` SET `ref_debut_agenda_ID`=0 WHERE isnull( `ref_debut_agenda_ID` )";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_remplacement` CHANGE `ref_fin_agenda_ID` `ref_fin_agenda_ID` MEDIUMINT( 8 ) UNSIGNED NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_remplacement` CHANGE `ref_debut_agenda_ID` `ref_debut_agenda_ID` MEDIUMINT( 8 ) UNSIGNED NULL DEFAULT '0' ";
$result = mysqli_query($conn_cahier_de_texte, $query);

}
// mise à jour 4.9.2.0
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4920)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_prof` ADD `ancien_prof` enum('O','N') DEFAULT 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);

}

// mise à jour 4.9.2.3
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4923)) {

if ((isset($_SESSION['module_absence']))&& ($_SESSION['module_absence']=='Oui')) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `ele_liste` ADD `groupe_ID_ele` smallint(4) NOT NULL DEFAULT '1' ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `ele_liste` ADD `groupe_ele` varchar(50) NOT NULL DEFAULT 'Classe entière' ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `ele_absent` CHANGE `classe_ID` `classe_ID` SMALLINT( 5 ) UNSIGNED NULL DEFAULT NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_agenda` CHANGE `classe_ID` `classe_ID` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_edt` CHANGE `classe_ID` `classe_ID` SMALLINT( 5 ) UNSIGNED NULL DEFAULT NULL ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_emploi_du_temps` CHANGE `classe_ID` `classe_ID` SMALLINT( 5 ) UNSIGNED NULL DEFAULT '0' ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_destinataire` CHANGE `classe_ID` `classe_ID` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_groupe_interclasses_classe` CHANGE `classe_ID` `classe_ID` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_invite` CHANGE `classe_ID` `classe_ID` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0' ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_message_destinataire` CHANGE `classe_ID` `classe_ID` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0' ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_travail` CHANGE `classe_ID` `classe_ID` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);


};


// mise à jour 4.9.2.4
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4924)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "CREATE TABLE IF NOT EXISTS `cdt_archive_association` (
  `ID_assoc` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `prof_ID` smallint(5) NOT NULL,
  `classe_ID` smallint(5) NOT NULL,
  `gic_ID` smallint(5) NOT NULL,
  `matiere_ID` smallint(5) NOT NULL,
  `NumArchive` smallint(5) NOT NULL,
  `classe_ID_archive` smallint(5) NOT NULL,
  `gic_ID_archive` smallint(5) NOT NULL,
  `matiere_ID_archive` smallint(5) NOT NULL,
  PRIMARY KEY (`ID_assoc`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ";
$result = mysqli_query($conn_cahier_de_texte, $query);
}

// mise à jour 4.9.2.5
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4925)) {
if ((isset($_SESSION['module_absence']))&& ($_SESSION['module_absence']=='Oui')) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `ele_absent` ADD `perso1` enum('O','N') DEFAULT 'N' ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `ele_absent` ADD `perso2` enum('O','N') DEFAULT 'N' ";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `ele_absent` ADD `perso3` enum('O','N') DEFAULT 'N' ";
$result = mysqli_query($conn_cahier_de_texte, $query);
};
}

// mise à jour 4.9.3.4
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4934)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` (`param_nom`,`param_val`,`param_desc`) VALUES ('URL_Piwik', '', 'Adresse de Piwik')";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` (`param_nom`,`param_val`,`param_desc`) VALUES ('ID_Piwik', '', 'ID de site de Piwik')";
$result = mysqli_query($conn_cahier_de_texte, $query);
}

// mise à jour 4.9.3.5
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4935)) {

$query = "INSERT INTO `cdt_params` VALUES ( 'menu_deroul', 'Oui', 'Oui:Menu deroulant pour les profs dans la page index - Non:Zone de saisie pour les profs')";
$result = mysqli_query($conn_cahier_de_texte, $query);

}


// mise à jour 4.9.3.8
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4938)) {
if ((isset($_SESSION['module_absence']))&& ($_SESSION['module_absence']=='Oui')) {
$query = "CREATE TABLE IF NOT EXISTS `ele_present` (
  `ID` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
  `classe_ID` smallint(5) unsigned DEFAULT NULL,
  `heure_debut` varchar(20) DEFAULT NULL,
  `heure_fin` varchar(20) DEFAULT NULL,
  `salle` varchar(20) DEFAULT NULL,
  `eleve_ID` smallint(4) DEFAULT NULL,
  `prof_ID` smallint(4) DEFAULT NULL,
  `travail` varchar(100) DEFAULT NULL,
  `date_heure` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

}

// mise à jour 4.9.3.9
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4939)) {
$query = "UPDATE cdt_groupe_interclasses SET nom_gic = REPLACE(nom_gic,'/','-') WHERE nom_gic LIKE '%/%'";
$result = mysqli_query($conn_cahier_de_texte, $query);
}

// mise à jour 4.9.4.1

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4941)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_prof` ADD `masque_edt_cloture` enum('O','N') NOT NULL DEFAULT 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_progression` CHANGE `ID_progression` `ID_progression` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT";
$result = mysqli_query($conn_cahier_de_texte, $query); 
};



// mise à jour 4.9.4.2
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4942)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_destinataire` ADD `visible` enum('O','N') NOT NULL DEFAULT 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` CHANGE `titre_even` `titre_even` varchar(80) NOT NULL DEFAULT ''";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` ADD `code_mode` tinyint(2) NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query); 

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` ADD `code_theme` tinyint(2) NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query); 

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` ADD `pb_dates` tinyint(1) NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query); 

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` ADD `pb_dates` tinyint(1) NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query); 

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` ADD `date_modif` date NOT NULL DEFAULT '0000-00-00'";
$result = mysqli_query($conn_cahier_de_texte, $query); 

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` ADD `date_crea` date NOT NULL DEFAULT '0000-00-00'";
$result = mysqli_query($conn_cahier_de_texte, $query); 

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` ADD `etat` varchar(9) DEFAULT NULL";
$result = mysqli_query($conn_cahier_de_texte, $query); 

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` ADD `classes_conc` varchar(60) DEFAULT NULL";
$result = mysqli_query($conn_cahier_de_texte, $query); 

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` ADD `classes_eff` smallint(4) NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` ADD `details_sup` text";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` ADD `accompagnateurs` text";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` ADD `cout_elv` float DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` ADD `cout_glob` float DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_evenement_contenu` SET `etat` = 'Validé'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "
CREATE TABLE IF NOT EXISTS `cdt_evenement_acteur` (
  `ID_act` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `even_ID` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `classe_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `groupe_ID` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID_act`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
$result = mysqli_query($conn_cahier_de_texte, $query);



mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "
INSERT INTO cdt_params (
`param_nom` ,
`param_val` ,
`param_desc`
)
VALUES 
('even_nom_valid_mail', NULL , 'Personne ayant valide le projet et declenchant envoi de mels'), 
('even_adr_valid_mail', NULL , 'Adresse mail de la personne validant'), 
('even_dest_mail', NULL , 'Adresse mel des personnes souhaitant etre informe de la validation')
";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_nom` = 'prof_mess_pp', `param_desc` = 'Autoriser la publication de messages par les enseignants vers tous ses eleves et les collegues de ses eleves' WHERE `cdt_params`.`param_nom` = 'prof_mess';";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` VALUES ('prof_mess_all', 'Non', 'Autoriser la publication de messages par les enseignants vers tous les enseignants et toutes les classes')";
$result = mysqli_query($conn_cahier_de_texte, $query);



}

// mise à jour 4.9.4.6

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4946)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_message_contenu` ADD `titre` varchar(40) NOT NULL DEFAULT 'Message' AFTER `ID_message`";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_groupe_interclasses_classe` CHANGE `groupe_ID` `groupe_ID` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_groupe_interclasses_classe` CHANGE `classe_ID` `groupe_ID` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE cdt_groupe SET code_groupe=groupe WHERE code_groupe IS NULL";
$result = mysqli_query($conn_cahier_de_texte, $query); 
};

// mise à jour 4.9.4.7

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4947)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE cdt_groupe SET groupe = 'Classe entière', code_groupe = 'classe_entiere' WHERE ID_groupe = 1 AND groupe=''";
$result = mysqli_query($conn_cahier_de_texte, $query);
 
};


// mise à jour 4.9.5.1

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<4951)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "SET @@global.sql_mode= '' "; //desactivation mode strict sur version recente mysql
$result = mysqli_query($conn_cahier_de_texte, $query);
};

// mise à jour 5502

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<5502)) {
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ('choix_module_absence', '2', 'Choix du module de gestion des absences et suivi carnets - Version simple = 1 / Version elaboree = 2')"; 
$result = mysqli_query($conn_cahier_de_texte, $query);
};


// mise à jour 5509

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<5509)) {

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_semaine_ab` ADD `semaine_alter` VARCHAR(255) NOT NULL DEFAULT 'Sem 1' AFTER `semaine`";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_emploi_du_temps` CHANGE `semaine` `semaine` varchar(255) CHARACTER SET latin1  NOT NULL DEFAULT 'A et B'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_edt` CHANGE `semaine` `semaine` VARCHAR(255) CHARACTER SET latin1 NULL DEFAULT NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);
};

// mise à jour 5510

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']<5510)) {

//modif des format de dates


mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_agenda` CHANGE `date_visa` `date_visa` DATE NOT NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_emploi_du_temps` CHANGE `edt_exist_debut` `edt_exist_debut` DATE NOT NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_emploi_du_temps` CHANGE `edt_exist_fin` `edt_exist_fin` DATE NOT NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_prof` CHANGE `date_maj` `date_maj` DATE NOT NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_prof` CHANGE `date_declare_absent` `date_declare_absent` DATE NOT NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_message_contenu` CHANGE `date_envoi` `date_envoi` DATETIME NOT NULL DEFAULT '0001-01-01 00:00:00'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_message_contenu` CHANGE `date_fin_publier` `date_fin_publier` DATE NOT NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` CHANGE `date_debut` `date_debut` DATE NOT NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` CHANGE `date_fin` `date_fin` DATE NOT NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` CHANGE `date_modif` `date_modif` DATE NOT NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` CHANGE `date_envoi` `date_envoi` DATE NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_evenement_contenu` CHANGE `date_crea` `date_crea` DATE NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_message_modif` CHANGE `date_envoi` `date_envoi` DATETIME NOT NULL DEFAULT '0001-01-01 00:00:00'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_remplacement` CHANGE `date_debut_remplace` `date_debut_remplace` DATE NOT NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_remplacement` CHANGE `date_fin_remplace` `date_fin_remplace` DATE NOT NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "ALTER TABLE `cdt_remplacement` CHANGE `date_creation_remplace` `date_fin_creation` DATE NOT NULL DEFAULT '0001-01-01'";
$result = mysqli_query($conn_cahier_de_texte, $query);


//Fin modif des format de dates

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_table= "SHOW TABLES LIKE 'ele_absent'";
$result = mysqli_query($conn_cahier_de_texte,$query_table) or die(mysqli_error($conn_cahier_de_texte));
if (!$result) {
   echo "Erreur DB, impossible de lister les tables\n";
   echo 'Erreur MySQL : ' . mysql_error();
   exit;
} else {
$query ="ALTER TABLE `ele_absent` ADD  `date` varchar(8) default NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);
$query ="ALTER TABLE `ele_absent` ADD  `Ds` varchar(1) default NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);
$query ="ALTER TABLE `ele_absent` ADD  `heure_saisie` varchar(10) default NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);
$query ="ALTER TABLE `ele_absent` ADD  `details` varchar(255) default NULL";
$result = mysqli_query($conn_cahier_de_texte, $query);
$query ="ALTER TABLE `ele_absent` ADD  `absent` enum('Y';'N') default 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$query ="ALTER TABLE `ele_absent` ADD  `retard_V` enum('Y';'N') default 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$query ="ALTER TABLE `ele_absent` ADD  `pbCarnet` enum('Y';'N') default 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$query ="ALTER TABLE `ele_absent` ADD  `retard_Nv` enum('Y';'N') default 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$query ="ALTER TABLE `ele_absent` ADD  `signature` enum('Y';'N') NOT NULL default 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$query ="ALTER TABLE `ele_absent` ADD  `solde` varchar(32) default 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$query ="ALTER TABLE `ele_absent` ADD  `surcarnet` varchar(32) default 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);
$query ="ALTER TABLE `ele_absent` ADD  `annule` varchar(32) default 'N'";
$result = mysqli_query($conn_cahier_de_texte, $query);

};


}
//mise a jour finale effectuee si tout s'est bien passe
 
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '".$libelle_version."' WHERE `param_nom` ='version'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '".$indice_version."' WHERE `param_nom` ='ind_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
$query = "UPDATE `cdt_params` SET `param_val` = '".date("d/m/Y, g:i a")."' WHERE `param_nom` ='date_maj_base'";
$result = mysqli_query($conn_cahier_de_texte, $query);

if ($totalRows_Rsparams2==1) {
?>
<div class="erreur">
  <p align="center"><br />
    Les mises &agrave; jour ont &eacute;t&eacute; effectu&eacute;es.<br />
    <br />
    Votre base de donn&eacute;es est maintenant actualis&eacute;e pour la version <strong> <?php echo $indice_version;?>. <br /> 
    <br />
    Avez vous bien pens&eacute; &agrave; vous acquitter de votre licence Chocolaware envers l'auteur de cette application ?</strong></p>
  <p align="center"><img src="./images/chocolat.png" width="220" height="171" /></p>
  <p align="center">Vous &ecirc;tes satisfaits de cette application.<br />
    Elle a n&eacute;cessit&eacute; plus de 400 heures de travail.<br />
    Afin de soutenir ce projet, d&rsquo;encourager sa maintenance et le d&eacute;veloppement des versions futures, je serai ravi de recevoir</p>
  <p align="center"><strong>de la part de chaque&nbsp;</strong><strong>utilisateur</strong></p>
  <p align="center"><strong>des chocolats ou autres sp&eacute;cialit&eacute;s r&eacute;gionales de votre choix</strong>.</p>
  <p align="center">A exp&eacute;dier &agrave;<br />
    Pierre Lemaitre<br />
    324 Rue Ambroise Par&eacute; &ndash; 50000 Saint-L&ocirc;</p>
  <p align="center"><a href="mailto:pierre.lemaitre@laposte.net">pierre.lemaitre@laposte.net</a></p>
  <p align="center">Vous retrouverez ces informations en cliquant sur &quot;Pierre Lemaitre&quot; en bas de la page d'accueil du cahier de textes </p>
  <p align="center"><a href="./index.php">Page d'accueil du cahier de texte</a> </p>
  <br/>
</div>
<?php require_once('stats.php');
};

if ((mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) &&($row_Rsparams3['param_val']==$indice_version))
{echo '<br /><br />Aucune mise &agrave; jour de votre base de donn&eacute;es n\'est n&eacute;cessaire. <br /><br />'; };

?>

