<?php 
//modifier pour chaque nouvelle version
$indice_version='5510'; 
$libelle_version='Version 5.5.1.0 Standard';
//---------------------------------


require_once('../Connections/conn_cahier_de_texte.php'); 
session_start();
if (isset($_GET['creer_base'])){
$query = "CREATE DATABASE ". $database_conn_cahier_de_texte; 
$result = mysqli_query($conn_cahier_de_texte, $query);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) OR die('Erreur de connexion &agrave; la base de donn&eacute;es. Recommencez l\'installation et v&eacute;rifiez bien vos param&egrave;tres. ');

$query = "SET @@global.sql_mode= '' ";
$result = mysqli_query($conn_cahier_de_texte, $query);



$query = "
CREATE TABLE IF NOT EXISTS `cdt_agenda` (
  `ID_agenda` mediumint(8) unsigned NOT NULL auto_increment,
  `prof_ID` smallint(5) unsigned NOT NULL default '0',
  `classe_ID` smallint(5) unsigned NOT NULL default '0',
  `gic_ID` smallint(5) unsigned NOT NULL default '0',
  `matiere_ID` smallint(5) unsigned NOT NULL default '0',
  `groupe` varchar(255) NOT NULL default '',
  `semaine` varchar(255) NOT NULL default '',
  `jour_pointe` varchar(255) NOT NULL default '',
  `heure` tinyint(4) NOT NULL default '0',
  `duree` varchar(255) default NULL,
  `heure_debut` varchar(255) default NULL,
  `heure_fin` varchar(255) default NULL,
  `theme_activ` varchar(255) default NULL,
  `type_activ` varchar(255) default NULL,
  `couleur_activ` varchar(7) NOT NULL DEFAULT '#000066',
  `a_faire` text,
  `activite` text,
  `rq` text default NULL,
  `code_date` varchar(255) NOT NULL default '',
  `date_visa` date NOT NULL default '0001-01-01',
  `edt_modif` enum('O','N') NOT NULL DEFAULT 'N',
  `partage` enum('O','N') NOT NULL DEFAULT 'N',
  `emploi_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY `ID_agenda` (`ID_agenda`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `cdt_classe` (
  `ID_classe` smallint(5) unsigned NOT NULL auto_increment,
  `nom_classe` varchar(255) NOT NULL default '',
  `passe_classe` varchar(32) default NULL,
  `code_classe` varchar(20) default NULL,
  PRIMARY KEY  (`ID_classe`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1
";
$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_query($conn_cahier_de_texte, "SET NAMES latin1");
$query = "
CREATE TABLE IF NOT EXISTS `cdt_emploi_du_temps` (
  `ID_emploi` smallint(5) unsigned NOT NULL auto_increment,
  `prof_ID` smallint(5) unsigned NOT NULL default '0',
  `jour_semaine` enum('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') NOT NULL default 'Lundi',
  `semaine` varchar(255) NOT NULL default 'A et B',
  `heure` tinyint(2) unsigned NOT NULL default '0',
  `classe_ID` smallint(5) unsigned default '0',
  `gic_ID` smallint(5) unsigned NOT NULL default '0',
  `groupe` varchar(255) default 'Classe entière',
  `matiere_ID` smallint(5) unsigned default NULL,
  `heure_debut` varchar(255) default NULL,
  `heure_fin` varchar(255) default NULL,
  `duree` varchar(255) default NULL,
  `edt_exist_debut` date NOT NULL default '0001-01-01',
  `edt_exist_fin` date NOT NULL default '0001-01-01',
  `couleur_cellule` varchar(7) DEFAULT '#CAFDBD',
  `couleur_police` varchar(7) DEFAULT '#000000',
  `ImportEDT` varchar(255) NOT NULL DEFAULT 'NON',
  `ID_Import` smallint(5) unsigned NOT NULL DEFAULT '0',
  `fusion_gic` enum('O','N') NOT NULL DEFAULT 'N',
  `verrou_remplace` smallint(5) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`ID_emploi`) 
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1
";
$result = mysqli_query($conn_cahier_de_texte, $query);


$query = "
CREATE TABLE IF NOT EXISTS `cdt_fichiers_joints` (
  `ID_fichiers` smallint(5) unsigned NOT NULL auto_increment,
  `agenda_ID` mediumint(8) unsigned NOT NULL default '0',
  `ind_position` tinyint(3) NOT NULL default '1',
  `nom_fichier` varchar(255) NOT NULL default '',
  `prof_ID` smallint(5) unsigned NOT NULL default '0',
  `type` enum('Cours','Travail') default 'Cours',
  `t_code_date` varchar(255) default NULL,
  PRIMARY KEY  (`ID_fichiers`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);


$query = "
CREATE TABLE IF NOT EXISTS `cdt_matiere` (
  `ID_matiere` smallint(5) unsigned NOT NULL auto_increment,
  `nom_matiere` varchar(255) NOT NULL default '',
  `code_matiere` varchar(20) default NULL,
  PRIMARY KEY  (`ID_matiere`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `cdt_prof` (
  `ID_prof` smallint(5) unsigned NOT NULL auto_increment,
  `nom_prof` varchar(255) NOT NULL default '',
  `passe` varchar(255) default NULL,
  `identite` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `email_diffus_restreint` enum('O','N') NOT NULL DEFAULT 'N',
  `gestion_sem_ab` enum('O','N') NOT NULL default 'O',
  `publier_cdt` enum('O','N') NOT NULL default 'O',
  `publier_travail` enum('O','N') NOT NULL default 'O',
  `stop_cdt` enum('O','N') NOT NULL DEFAULT 'N',
  `date_maj` date NOT NULL default '0001-01-01',
  `droits` tinyint(3) unsigned NOT NULL default '0',
  `path_fichier_perso` varchar(255) default NULL,
  `xinha_editlatex` enum('O','N') NOT NULL default 'N',
  `xinha_equation` enum('O','N') NOT NULL default 'N',
  `xinha_stylist` enum('O','N') NOT NULL default 'N',
  `acces_rapide` enum('O','N') NOT NULL DEFAULT 'O',
  `afficher_messages` enum('O','N') NOT NULL DEFAULT 'O',  
  `masque_edt_cloture` enum('O','N') NOT NULL DEFAULT 'N', 
  `message_invite` enum('O','N') NOT NULL DEFAULT 'N',
  `Num_Import` smallint(5) unsigned NOT NULL DEFAULT '0',
  `lien_invite_dir` varchar(255) NULL,
  `datefin_invite_dir` date NULL,
  `lien_invite_prof` varchar(255) NULL,
  `datefin_invite_prof` date NULL,
  `id_etat` smallint(5) unsigned NOT NULL DEFAULT '0',
  `id_remplace` smallint(5) unsigned NOT NULL DEFAULT '0',
  `date_declare_absent` date NOT NULL DEFAULT '0001-01-01',
  `type_affich` tinyint(4) NOT NULL DEFAULT '1',
  `PrimoConn` enum('O','N') NOT NULL DEFAULT 'N',
  `ancien_prof` enum('O','N') DEFAULT 'N',
  PRIMARY KEY  (`ID_prof`),
  UNIQUE KEY `nom_prof` (`nom_prof`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);

$pw='$2y$10$J9yakmjaV/EFLzIjpLddU.XGKPHeFuUnx0XqQhmBc8zELhMLkcvFm';

$query = "INSERT INTO `cdt_prof` ( `ID_prof` , `nom_prof` , `passe` , `identite` ,`droits` ) VALUES (1, 'Administrateur','".$pw."' , 'Administrateur', 1)";




$result = mysqli_query($conn_cahier_de_texte, $query);


$query = "
CREATE TABLE IF NOT EXISTS `cdt_semaine_ab` (
  `ID_sem` tinyint(3) NOT NULL auto_increment,
  `semaine` enum('A','B') NOT NULL default 'A',
  `semaine_alter` enum('Sem 1','Sem 2','Sem 3','Sem 4') NOT NULL DEFAULT 'Sem 1',
  `num_semaine` tinyint(3) NOT NULL default '0',
  `s_code_date` varchar(255) NOT NULL default '',
  `date_lundi` varchar(255) NOT NULL default '',
  `date_dimanche` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID_sem`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);


$query = "
CREATE TABLE IF NOT EXISTS `cdt_travail` (
  `ID_travail` int(10) unsigned NOT NULL auto_increment,
  `agenda_ID` mediumint(8) unsigned NOT NULL default '0',
  `ind_position` tinyint(3) NOT NULL default '0',
  `prof_ID` smallint(5) unsigned NOT NULL default '0',
  `classe_ID` smallint(5) unsigned NOT NULL default '0',
  `gic_ID` smallint(5) unsigned NOT NULL default '0',
  `matiere_ID` smallint(5) unsigned NOT NULL default '0',
  `groupe` varchar(255) NOT NULL default '',
  `semaine` varchar(255) NOT NULL default '',
  `jour_pointe` varchar(255) NOT NULL default '',
  `heure` varchar(255) NOT NULL default '',
  `code_date` varchar(255) NOT NULL default '',
  `t_groupe` varchar(255) NOT NULL default '',
  `t_semaine` varchar(255) default NULL,
  `t_jour_pointe` varchar(255) default NULL,
  `t_code_date` varchar(255) NOT NULL default '',

  `travail` text,
  `charge` varchar(10) DEFAULT NULL,
  `eval` enum('O','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY  (`ID_travail`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1 
";

$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `cdt_type_activite` (
  `ID_activite` smallint(5) unsigned NOT NULL auto_increment,
  `ID_prof` smallint(5) unsigned NOT NULL default '0',
  `activite` varchar(255) default NULL,
  `pos_typ` smallint(5) unsigned NOT NULL default '1',
  `couleur_activite` varchar(7) NOT NULL DEFAULT '#000066',
  PRIMARY KEY  (`ID_activite`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1 
";

$result = mysqli_query($conn_cahier_de_texte, $query);



$query = "
CREATE TABLE IF NOT EXISTS `cdt_groupe` ( 
  `ID_groupe` smallint(5) unsigned NOT NULL auto_increment, 
  `groupe` varchar(255) NOT NULL default '', 
  `code_groupe` varchar(20) default NULL, 
  PRIMARY KEY  (`ID_groupe`), 
  UNIQUE KEY `groupe` (`groupe`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1
";

$result = mysqli_query($conn_cahier_de_texte, $query);

mysqli_query($conn_cahier_de_texte, "SET NAMES latin1");
$query = "INSERT INTO `cdt_groupe` VALUES (1, 'Classe entière','classe_entiere')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_groupe` VALUES (2, 'Groupe A','groupe_a')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_groupe` VALUES (3, 'Groupe B','groupe_b')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_groupe` VALUES (4, 'Groupe Réduit','groupe_reduit')";
$result = mysqli_query($conn_cahier_de_texte, $query);


$query = "
CREATE TABLE IF NOT EXISTS `cdt_progression` (
  `ID_progression` tinyint(3) unsigned NOT NULL auto_increment,
  `prof_ID` smallint(5) unsigned NOT NULL default '0',
  `titre_progression` varchar(255) NOT NULL default '',
  `contenu_progression` LONGTEXT NULL,
  PRIMARY KEY  (`ID_progression`)
)ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1
";
$result = mysqli_query($conn_cahier_de_texte, $query);



$query = "
CREATE TABLE IF NOT EXISTS `cdt_message_contenu` (
  `ID_message` smallint(5) NOT NULL auto_increment,
  `message` text NOT NULL,
  `prof_ID` smallint(5) unsigned NOT NULL default '0',
  `date_envoi` DATETIME NOT NULL DEFAULT '0001-01-01 00:00:00',
  `date_fin_publier` date NOT NULL default '0001-01-01',
  `online` enum('O','N') NOT NULL default 'O',
  `dest_ID` tinyint(4) NOT NULL default '0',
  `pp_classe_ID` smallint(5) NOT NULL default '0',
  `pp_groupe_ID` smallint(5) NOT NULL default '0',
  PRIMARY KEY  (`ID_message`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1  
";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `cdt_message_destinataire` (
  `ID_dest` smallint(5) unsigned NOT NULL auto_increment,
  `message_ID` smallint(5) unsigned NOT NULL default '0',
  `classe_ID` smallint(5) unsigned NOT NULL default '0',
  `groupe_ID` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID_dest`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1  
";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `cdt_message_destinataire_profs` (
  `ID_dest` smallint(5) unsigned NOT NULL auto_increment,
  `message_ID` smallint(5) unsigned NOT NULL default '0',
  `prof_ID` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID_dest`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1  
";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `cdt_params` (
  `param_nom` varchar(32) NOT NULL default '',
  `param_val` text,
  `param_desc` varchar(255) default NULL,
  PRIMARY KEY  (`param_nom`)
) ENGINE=MyISAM CHARACTER SET latin1
";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `cdt_evenement_contenu` (
  `ID_even` smallint(5) NOT NULL AUTO_INCREMENT,
  `titre_even` varchar(80) NOT NULL DEFAULT '',
  `code_mode` tinyint(2) NOT NULL DEFAULT '0',
  `code_theme` tinyint(2) NOT NULL DEFAULT '0',
  `detail` text,
  `prof_ID` smallint(4) unsigned NOT NULL DEFAULT '0',
  `date_debut` date NOT NULL DEFAULT '0001-01-01',
  `heure_debut` varchar(6) NOT NULL DEFAULT '00h00',
  `date_fin` date NOT NULL DEFAULT '0001-01-01',
  `heure_fin` varchar(6) NOT NULL DEFAULT '00h00',
  `pb_dates` tinyint(1) NOT NULL DEFAULT '0',
  `dest_ID` tinyint(4) NOT NULL DEFAULT '0',
  `date_modif` date NOT NULL DEFAULT '0001-01-01',
  `date_envoi` date DEFAULT '0001-01-01',
  `date_crea` date NOT NULL DEFAULT '0001-01-01',
  `etat` varchar(9) DEFAULT NULL,
  `classes_conc` varchar(60) DEFAULT NULL,
  `classes_eff` smallint(4) NULL DEFAULT '0',
  `details_sup` text,
  `accompagnateurs` text,
  `cout_elv` float DEFAULT '0',
  `cout_glob` float DEFAULT '0',
  PRIMARY KEY  (`ID_even`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1
";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE  IF NOT EXISTS `cdt_evenement_destinataire` (
  `ID_dest` smallint(5) unsigned NOT NULL auto_increment,
  `even_ID` smallint(5) unsigned NOT NULL default '0',
  `classe_ID` smallint(5) unsigned NOT NULL default '0',
  `groupe_ID` smallint(5) unsigned NOT NULL default '0',
  `visible` enum('O','N') NOT NULL default 'N',
  PRIMARY KEY  (`ID_dest`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `cdt_evenement_acteur` (
  `ID_act` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `even_ID` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `classe_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `groupe_ID` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID_act`)
) ENGINE=MyISAM CHARACTER SET latin1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `cdt_prof_principal` (
  `ID_pp` smallint(5) unsigned NOT NULL auto_increment,
  `pp_prof_ID` smallint(5) NOT NULL default '0',
  `pp_classe_ID` smallint(5) NOT NULL default '0',
  `pp_groupe_ID` smallint(5) NOT NULL default '0',
  PRIMARY KEY  (`ID_pp`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);


$query = "
CREATE TABLE IF NOT EXISTS `cdt_message_fichiers` (
  `ID_mesfich` smallint(5) unsigned NOT NULL auto_increment,
  `message_ID` smallint(5) NOT NULL default '0',
  `nom_fichier` varchar(255) NOT NULL default '',
  `prof_ID` smallint(5) NOT NULL default '0',
  PRIMARY KEY  (`ID_mesfich`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `cdt_groupe_interclasses` (
  `ID_gic` smallint(5) unsigned NOT NULL auto_increment,
  `prof_ID` smallint(5) unsigned NOT NULL default '0',
  `nom_gic` varchar(255) NOT NULL default '',
  `commentaire_gic` text,
  `code_gic` varchar(20) default NULL,
  PRIMARY KEY  (`ID_gic`)
) ENGINE=MyISAM  AUTO_INCREMENT=1  CHARACTER SET latin1 
";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `cdt_groupe_interclasses_classe` (
  `ID_gic_classe` smallint(5) unsigned NOT NULL auto_increment,
  `gic_ID` smallint(5) unsigned NOT NULL default '0',
  `classe_ID` smallint(5) unsigned NOT NULL default '0',
  `groupe_ID` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID_gic_classe`)
) ENGINE=MyISAM  AUTO_INCREMENT=1  CHARACTER SET latin1
";
$result = mysqli_query($conn_cahier_de_texte, $query);


$query = "INSERT INTO `cdt_params` VALUES ('version', '".$libelle_version."', 'Version du logiciel')";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('ind_maj_base', '".$indice_version."', 'indice de la version')";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('date_maj_base', '".date("d/m/Y, g:i a")."', 'date de la mise &agrave; jour')";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('old_cdt_access', 'Non', 'Acces ancien cahier de textes')";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('pp_diffusion', 'Oui', 'Autoriser les profs principaux &agrave; diffuser un message aux coll&egrave;gues')";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('url_logo_etab', '".$_SESSION['url_logo_etab']."', 'Adresse Web du logo etablissement')";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('session_timeout', '3600', 'Temps de cloture de session si inactivite')";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('url_etab', '".$_SESSION['url_etab']."', 'Adresse Web etablissement')";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('nom_etab', '".$_SESSION['nom_etab']."', 'Nom etablissement')";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('cdt_LDAP', 'Non', 'Version LDAP du CDT ?')";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('cdt_LCS', 'Non', 'Plugin LCS du CDT ?')";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('libelle_devoir', 'DEVOIR', 'Libelle attribue par defaut aux (devoirs,controles,ds...)')";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('visa_stop_edition', 'Oui', 'Interdit la modification des fiches saisies si leur date est anterieure &agrave; la date du visa')";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ( 'url_deconnecte_eleve', 'index.php', 'Url de sortie apres deconnexion du cdt' )";
$result = mysqli_query($conn_cahier_de_texte, $query); 

$query = "INSERT INTO `cdt_params` VALUES ( 'url_deconnecte_prof', 'index.php', 'Url de sortie apres deconnexion du cdt' )";
$result = mysqli_query($conn_cahier_de_texte, $query); 

$query = "INSERT INTO `cdt_params` VALUES ('module_absence', 'Non', 'Activation du module absence')";
$result = mysqli_query($conn_cahier_de_texte, $query); 

$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ('choix_module_absence', '2', 'Choix du module de gestion des absences et suivi carnets - Version simple = 1 / Version elaboree = 2')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('Maj_Archives', 'Oui', 'MAJ des Archives a faire apres version 4.72')";
$result = mysqli_query($conn_cahier_de_texte, $query); 

$query = "INSERT INTO `cdt_params` VALUES ('Publication_Import', 'Oui', 'Autorisation de publier l\'import des edt')";
$result = mysqli_query($conn_cahier_de_texte, $query); 

$query = "INSERT INTO `cdt_params` VALUES ('Import', '', 'Emploi du temps importe')";
$result = mysqli_query($conn_cahier_de_texte, $query); 

$query = "INSERT INTO `cdt_params` VALUES ('modif_passe', 'Oui', 'Enseignant peut changer son mot de passe')";
$result = mysqli_query($conn_cahier_de_texte, $query); 

$query = "INSERT INTO `cdt_params` VALUES ('modif_login', 'Non', 'Enseignant peut changer son login')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('libelle_semaine', '0', 'Libelle de la semaine 0 = A et B  & 1= Paire et impaire ')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('facebook_icon', 'Oui', 'Affichage icone facebook')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('site_ferme', 'Non', 'Etat du site Non=ouvert Oui=ferme')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('devoir_planif', 'Oui', 'Autoriser la planification de devoirs en dehors des heures de cours')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('pp_multiclass', 'Oui', 'Autoriser un prof a etre PP de plusieurs classes')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('pp_groupe', 'Oui', 'Autoriser un prof a etre PP d un groupe')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('prof_mess_pp', 'Non', 'Autoriser la publication de messages par tous les enseignants')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('prof_mess_all', 'Non', 'Autoriser la publication de messages par les enseignants vers toutes les classes')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('date_debut_annee', NULL, 'Date de debut annee scolaire')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('date_fin_annee', NULL, 'Date de fin annee scolaire')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ( 'affichage_compteur', 'Non', 'Affiche ou non un compteur ultra simpliste')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ( 'compteur', '0', 'Le compteur de consulter')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` ( `param_nom` , `param_val` , `param_desc` ) VALUES ( 'date_raz_compteur', '20100311', 'Date de remise a zero du compteur')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` (`param_nom` ,`param_val` ,`param_desc`) VALUES ('time_zone', 'Europe/Paris', 'Zone de temps de votre localisation geographique')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('MAJ_Partage_4911', 'Non', 'MAJ effectuee pour Partage dans Archives ?')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('acces_inspection_all_cdt', 'Non', 'Acces systematique aux cahiers par les inspecteurs')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('MAJ_Latex_4909', 'Oui', 'MAJ effectuee  pour Latex ?')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('URL_Piwik', '', 'Adresse de Piwik')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('ID_Piwik', '1', 'ID de site de Piwik')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ( 'menu_deroul', 'Oui', 'Oui:Menu deroulant pour les profs dans la page index - Non:Zone de saisie pour les profs')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('even_nom_valid_mail', NULL , 'Personne ayant valide le projet et declenchant envoi de mels')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('even_adr_valid_mail', NULL , 'Adresse mail de la personne validant')";
$result = mysqli_query($conn_cahier_de_texte, $query);

$query = "INSERT INTO `cdt_params` VALUES ('even_dest_mail', NULL , 'Adresse mel des personnes souhaitant etre informe de la validation')";
$result = mysqli_query($conn_cahier_de_texte, $query);


$query = "CREATE TABLE IF NOT EXISTS `cdt_plages_horaires` (
  `ID_plage` smallint(5) unsigned NOT NULL,
  `h1` char(2) default NULL,
  `mn1` char(2) default NULL,
  `h2` char(2) default NULL,
  `mn2` char(2) default NULL,
  PRIMARY KEY  (`ID_plage`)
) ENGINE=MyISAM CHARACTER SET latin1";
$result= mysqli_query($conn_cahier_de_texte, $query);



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

$query = "
CREATE TABLE IF NOT EXISTS `cdt_archive` (
	`NumArchive` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
	`NomArchive` VARCHAR( 255 ) NOT NULL ,
	`DateArchive` DATE NOT NULL ,
	PRIMARY KEY ( `NumArchive` )
) ENGINE=MyISAM CHARACTER SET latin1";
$result= mysqli_query($conn_cahier_de_texte, $query);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query = "
CREATE TABLE IF NOT EXISTS `cdt_edt` (
  `ID_emploi` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `prof_ref` varchar(255) default NULL,
  `jour_semaine` enum('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') default NULL,
  `semaine` varchar(255) default NULL,
  `heure` tinyint(2) unsigned default NULL,
  `classe_ID` smallint(5) unsigned default NULL,
  `matiere_ID` smallint(5) unsigned default NULL,
  `heure_debut` varchar(255) default NULL,
  `heure_fin` varchar(255) default NULL,
  `couleur_cellule` varchar(255) default NULL,
  `IdentiteProf` varchar(255) default NULL,
  `groupe` varchar(255) NOT NULL default 'Classe entière',
  PRIMARY KEY  (`ID_emploi`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 CHARACTER SET latin1";
$result= mysqli_query($conn_cahier_de_texte, $query);

mysqli_query($conn_cahier_de_texte, "SET NAMES latin1");
$query = "
CREATE TABLE IF NOT EXISTS `cdt_invite` (
  `ID_invite` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `prof_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `classe_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `gic_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `matiere_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `NumArchive` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID_invite`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `cdt_emploi_du_temps_partage` (
  `Partage_ID` smallint(5) NOT NULL auto_increment,
  `ID_emploi` smallint(5) NOT NULL,
  `profpartage_ID` smallint(5) NOT NULL,
  PRIMARY KEY  (`Partage_ID`)
) ENGINE=MyISAM  CHARACTER SET latin1";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "
CREATE TABLE IF NOT EXISTS `cdt_message_modif` (
   `ID_modif` smallint(5) NOT NULL auto_increment,
   `ID_message` smallint(5) NOT NULL,
   `prof_ID` smallint(5) unsigned NOT NULL default '0',
   `date_envoi` DATETIME NOT NULL DEFAULT '0001-01-01 00:00:00',
   PRIMARY KEY  (`ID_modif`)
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1 ";
$result= mysqli_query($conn_cahier_de_texte, $query);

$query = "CREATE TABLE IF NOT EXISTS `cdt_remplacement` (
  `ID_remplace` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `titulaire_ID` smallint(5) NOT NULL,
  `remplacant_ID` smallint(5) NOT NULL,
  `date_debut_remplace` date NOT NULL DEFAULT '0001-01-01',
  `date_fin_remplace` date NOT NULL DEFAULT '0001-01-01',
  `date_creation_remplace` date NOT NULL DEFAULT '0001-01-01',
  `ref_debut_agenda_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `ref_fin_agenda_ID` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID_remplace`)
) ENGINE=MyISAM  AUTO_INCREMENT=1  CHARACTER SET latin1";
$result = mysqli_query($conn_cahier_de_texte, $query);


$query = "CREATE TABLE IF NOT EXISTS `cdt_niveau` (
  `ID_niv` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nom_niv` varchar(255) NOT NULL DEFAULT '',
  `commentaire_niv` text,
  PRIMARY KEY (`ID_niv`)
) ENGINE=MyISAM  AUTO_INCREMENT=1  CHARACTER SET latin1";
$result = mysqli_query($conn_cahier_de_texte, $query);


$query = "CREATE TABLE IF NOT EXISTS `cdt_niveau_classe` (
  `ID_niv_classe` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `niv_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `classe_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `groupe_ID` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID_niv_classe`)
) ENGINE=MyISAM  AUTO_INCREMENT=1  CHARACTER SET latin1";
$result = mysqli_query($conn_cahier_de_texte, $query);



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
) ENGINE=MyISAM AUTO_INCREMENT=1  CHARACTER SET latin1 ";
$result = mysqli_query($conn_cahier_de_texte, $query);


 
?> 
<html>
<head>
<title>Cahier de textes - Installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style1 {
	color: #FF0000;
	font-weight: bold;
}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="INSTALLATION - Cr&eacute;ation des tables";
require_once "../templates/default/header.php";




$tab_cdt =array(
'cdt_agenda',
'cdt_archive',
'cdt_archive_association','cdt_classe','cdt_edt','cdt_emploi_du_temps','cdt_emploi_du_temps_partage',
'cdt_evenement_acteur','cdt_evenement_contenu','cdt_evenement_destinataire','cdt_fichiers_joints',
'cdt_groupe','cdt_groupe_interclasses','cdt_groupe_interclasses_classe','cdt_invite','cdt_matiere',
'cdt_message_contenu','cdt_message_destinataire','cdt_message_destinataire_profs','cdt_message_fichiers',
'cdt_message_modif','cdt_niveau','cdt_niveau_classe','cdt_params','cdt_plages_horaires','cdt_prof',
'cdt_prof_principal','cdt_progression','cdt_remplacement','cdt_semaine_ab','cdt_travail','cdt_type_activite');
$m=0;
$n=0;
$i=1;
?>
<br>
<table width="42%" border="1" align="center" cellspacing="0">

<?php

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
 
	
while ($n<32) {
 ?>
  <tr>
    <td class="tab_detail_gris" width="6%"><div align="right"><?php echo '  '.$i;?></div></td>
    <td class="tab_detail_gris" width="40%"><div align="left"><?php echo '  '.$tab_cdt[$n]; ?></div></td>
    <td class="tab_detail_gris" width="54%"><div align="center">
      <?php 
	$x=0;
	    $result = mysqli_query($conn_cahier_de_texte, "SHOW TABLES FROM ".$database_conn_cahier_de_texte." "); 
	while($row = mysqli_fetch_array($result)){
		if ($tab_cdt[$n]==$row[0]){$x=1;};
	
	};
	
	if ($x==0){echo '<span class="Style1">    manquante</span>';$m=$m+1;} 
	else { ?> 
	
	     <img src="../images/accept.png" width="16" height="16">
	     <?php      
		 };
	?>
    </div></td>
  </tr>


 
    <?php
		$n=$n+1;$i=$i+1;
    }  ;
?>
</table>
<?php	
if ($m>0 ){
 ?>

  <p align="center"><span class="Style1">Des tables n'ont pas pu &ecirc;tre cr&eacute;&eacute;es</span>.</p>
  <p align="center"> <a href="install_table.php">Rafraichir cette page pour tenter de recr&eacute;er les tables manquantes.</a></p>
  <p align="center">En cas de probl&egrave;me, il  sera n&eacute;cessaire   de recr&eacute;er les tables manquantes </p>
  <p align="center">avec Phpmyadmin en utilisant ce fichier d'installation manuellle pr&eacute;sent dans le dossier Install</p>
  <p align="center"><a href="install_tables_manuel.txt" target="_blank">Installation manuelle des tables </a></p>
<?php
}
else

{
?>

    <div align="center">
      <blockquote>
	 
        <p align="left">La cr&eacute;ation des tables vient d'&ecirc;tre r&eacute;alis&eacute;e. La premi&egrave;re partie de l'installation &eacute;tant termin&eacute;e, le dossier install n'est plus n&eacute;cessaire. Par souci de s&eacute;curit&eacute;, renommez le ou  mieux encore, supprimez le. </p>
        <p align="left">L'op&eacute;ration &eacute;tant faite, vous allez passer maintenant en mode Administrateur pour cr&eacute;er la liste  des Enseignants, mati&egrave;res, classes, groupes, la programmation des semaines A et B...</p>
        <p align="left">Vous pourrez faire chacune de ces op&eacute;rations s&eacute;par&eacute;ment, manuellement ou &agrave; partir de fichiers type CSV. <br>
        Une autre solution consistera &agrave; faire une importation depuis SCONET. Voir tout cela dans le menu Admistrateur. </p>
        <p align="left">Sur la page d'accueil, vous s&eacute;lectionnerez  Administrateur sans mot de passe pour acc&eacute;der &agrave; l'interface Administration </p>
        <p align="left">&nbsp;</p>
        <p align="left"><font color="#CC0000" size="2" face="Verdana, Arial, Helvetica, sans-serif"><em>Les 
          utilisateurs Free doivent imp&eacute;rativement avoir cr&eacute;&eacute; 
          un dossier nomm&eacute; <strong>sessions</strong> <br>
        (en minuscules - tr&egrave;s important) &agrave; la <strong>racine</strong> de leur site</em></font>.</p>
      </blockquote>
      <p align="center"></p>
      <p align="center"><a href="../index.php">Continuer et s'identifier en tant qu'Administrateur sans mot de passe sur la page d'accueil </a></p>
    </div>
<?php
};
?>
<DIV id=footer></DIV>
</DIV>
</body>
</html>

