<?php
//error_reporting(E_ALL);
//ini_set("display_errors",1); 
include("../authentification/authcheck.php");
if($_SESSION['droits']!=1) header("Location: ../index.php");
require_once('../Connections/conn_cahier_de_texte.php');

$conn_cahier_de_texte = mysqli_connect($hostname_conn_cahier_de_texte, $username_conn_cahier_de_texte, $password_conn_cahier_de_texte) or die(mysqli_connect_errno());
// ajout pierre lemaitre - Conversion des slash dans les noms de groupes
function remplace_slash($var)
{
	$final = str_replace("/","_",$var);
	return $final;
};

//vérification de la version de PHP par existence de la fonction utilisée pour parser les XML
//hors de l'objet "ImportSconet" car lui-même non instanciable avant PHP5 du fait de l'utilisation de la méthode magique "__construct()"
if(!function_exists("simplexml_load_file"))
	{
	echo "
	<html>
	<head>
	<title>Cahier de textes</title>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />
	<link media=\"screen\" href=\"../styles/style_default.css\" type=\"text/css\" rel=\"stylesheet\" />
	<link media=\"screen\" href=\"../templates/default/header_footer.css\" type=\"text/css\" rel=\"stylesheet\" />
	</head>
	<body>
	<div id=\"page\">";
	$header_description = "Importation des enseignants, classes, matières et emploi du temps";
	include("../templates/default/header.php");
	echo "
	<div class=\"erreur\" style=\"margin-top:10px;\">
	La fonction utilis&eacute;e \"simplexml_load_file()\" n&rsquo;est pas prise en charge par votre version de PHP.<br/>
	Cette proc&eacute;dure d&rsquo;importation n&eacute;cessite au moins PHP5 pour fonctionner.
	</div>
	<div id=\"footer\">
	<p><a href=\"index.php\">Retour au menu administrateur</a></p>
	</div>
	</div>
	</body>
	</html>
	";
	die();
	}

/**
 * Plugin pour le Cahier de Textes Chocolat de Pierre Lemaitre <pierre.lemaitre@ac-caen.fr>
 * (http://www.etab.ac-caen.fr/bsauveur/cahier_de_texte/)
 * 
 * Importation de données à partir des fichiers XML fournis par STSWeb et un logiciel d'emploi du temps
 *
 * sts_emp_rne_annee.xml :
 *     - C'est le fichier fourni par STSweb.
 *     - Il contient les individus, les matières et les classes d'un établissement.
 *     - Les données sont indépendantes les unes des autres.
 *     - Il est obligatoire.
 * emp_sts_rne_annee.xml :
 *     - C'est le fichier fourni par le logiciel d'emplois du temps.
 *     - Il contient les groupes et les cours.
 *     - Ces données supplémentaires dépendent de celles du premier fichier (utilisation de la session pour sauvegarder les associations XML/CDT)
 *     - Il est optionnel.
 * Remarque : le premier fichier contient des groupes mais ils ne sont pas pris en compte car ne correspondent souvent pas aux groupes réellement utilisés dans les emplois du temps
 *
 * Principe général pour chaque étape "step" d'importation de données :
 *     1. phase de lecture des données du fichier XML concerné et des données présentes dans le cahier de textes => méthodes "loadStep" et "getCdtStep"
 *     2. phase de détermination d'éventuelles données déjà présentes dans le cahier de textes (en fonction du code ou du nom) => réalisé dans "loadStep"
 *     3. phase de traitement des données si envoi de formulaire => "step_process"
 *     4. phase d'affichage => "step_display"
 *
 * Différentes supports de données (utilisateurs, classes...) :
 *     - tableau $step qui reçoit les informations du fichier XML
 *     - tableau $cdt_step qui reçoit les informations du cahier de textes
 *     - tableau en session $_SESSION["importsconet"]["step"] qui reçoit les correspondances entre identifiants XML et identifiants CDT uniquement si import des emplois du temps
 * 
 * Certains traitements s'inspirent de ce que fait la procédure d'importation de l'EAD d'un serveur Scribe (équipe Eole) pour faciliter l'adaptation pour le projet EnvOLE
 * (http://eole.orion.education.fr/depot/filedetails.php?repname=Eole&path=%2FEole2.2%2Fscribe%2Fscribe-backend%2Ftrunk%2Fscribe%2Feoletools.py)
 *
 * @author Christophe Deseure <christophe.deseure@ac-creteil.fr>
 * @version 1 (beta) du 25/11/09
 */
class ImportSconet
	{
	/**
	 * Fonctionnement ent EnvOLE
	 * Connexion LDAP pour proposer logins et noms de classe
	 *
	 * @var boolean
	 * @access private
	 */
	private $useEnvole = false;

	/**
	 * Pour visualiser les objets issus des fichiers XML une fois chargés
	 *
	 * @var boolean
	 * @access private
	 */
	private $seeXML = false;

	/**
	 * Pour sauvegarder sous format html le résultat de l'import des emplois du temps
	 *
	 * @var boolean
	 * @access private
	 */
	private $saveImport = true;

	/**
	 * Nom à donner au tableau mis en session
	 * Utiliser uniquement si emp_sts
	 *
	 * @var string
	 * @access private
	 */
	private $sessname = "importsconet";

	/**
	 * Chemin du fichier de connexion à la base de données MySQL
	 *
	 * @var string
	 * @access private
	 */
	private $file_connexion = '../Connections/conn_cahier_de_texte.php';

	/**
	 * Identifiant de connexion MySQL
	 *
	 * @var resource
	 * @access private
	 */
	private $db_conn = false;

	/**
	 * Résultat d'une requête MySQL
	 *
	 * @var resource
	 * @access private
	 */
	private $requete = false;

	/**
	 * Ordre des étapes communes
	 * Ne pas changer
	 * "gic" , "groupes" et "edt" seront ajoutés éventuellement à l'instanciation si emp_sts
	 *
	 * @var array
	 * @access private
	 */	
	private $step_list = array("start","upload","profs","matieres","classes");

	/**
	 * Texte des boutons pour chaque étape
	 * index 0 : afficher par l'étape précédente pour passer à la suivante
	 * index 1 : afficher pour valider les choix dans une étape donnée
	 *
	 * @var array
	 * @access private
	 */
	private $step_display = array
		(
		"start" => array("Lancement de la proc&eacute;dure d&rsquo;importation &raquo;","D&eacute;marrer l&rsquo;importation"),
		"upload" => array("Charger le fichier &raquo;","Charger le fichier"),
		"uploads" => array("Charger les 2 fichiers &raquo;","Charger les 2 fichiers"),
		"profs" => array("Importation des utilisateurs &raquo;","Importer les utilisateurs"),
		"matieres" => array("Importation des mati&egrave;res &raquo;","Importer les mati&egrave;res"),
		"classes" => array("Importation des classes &raquo;","Importer les classes"),
		"pregroupes" => array("V&eacute;rification des groupes natifs &raquo;","Valider les groupes natifs"),
		"groupes" => array("Importation des groupes &raquo;","Importer les groupes"),
		"gic" => array("Importation des regroupements &raquo;","Importer les regroupements"),
		"edt_param" => array("Param&eacute;trage des emplois du temps &raquo;","Param&eacute;trer les emplois du temps"),
		"edt_import" => array("Importation des emplois du temps &raquo;","Importer les emplois du temps"),
		"end" => array("Importation termin&eacute;e &raquo;","Importation termin&eacute;e")
		);

	/**
	 * Nom de l'étape en cours incluse dans chaque formulaire envoyé
	 *
	 * @var string
	 * @access private
	 */
	private $step = "";

	/**
	 * Nom de l'étape suivante déduite de l'étape en cours
	 *
	 * @var string
	 * @access private
	 */
	private $step_next = "";

	/**
	 * Utiliser si interruption du script
	 *
	 * @var string
	 * @access private
	 */
	private $abandon = "<br/>Proc&eacute;dure d&rsquo;importation abandonn&eacute;e.<br/>-- <a href=\"import_sconet.php\">Retour</a> --";

	/**
	 * Noms à donner aux 2 fichiers uploadés
	 *
	 * @var array
	 * @access private
	 */
	private $filenames = array
		(
		"sts_emp"=>"sts_emp_file.xml",
		"emp_sts"=>"emp_sts_file.xml",
		"log"=>"import_sconet_log.html"
		);

	/**
	 * Chemin d'enregistrement des fichiers XML
	 *
	 * @var string
	 * @access private
	 */
	private $to_dir = "../fichiers_joints/";

	/**
	 * Affichage à donner aux codes de civilité des individus
	 *
	 * @var array
	 * @access private
	 */
	private $civilites = array(0=>"M./Mme",1=>"M.",2=>"Mme",3=>"Mme");

	/**
	 * Différents statuts possibles des individus avec leur nom et les droits à donner dans le cahier de textes
	 * Tout utilisateur avec un statut inconnu sera vu comme un enseignant
	 *
	 * @var array
	 * @access private
	 */
	private $statuts = array
		(
		"id" => array("ens","edu","dir"),
		"droits" => array(2,3,4),
		"nom" => array("enseignant","cpe","direction")
		);

	/**
	 * Objet correspondant aux données du fichier sts_emp_rne_annee.xml
	 *
	 * @var object
	 * @access private
	 */
	private $sts_emp = false;

	/**
	 * Objet correspondant aux données du fichier emp_sts_rne_annee.xml
	 *
	 * @var object
	 * @access private
	 */
	private $emp_sts = false;

	/**
	 * RNE de l'établissement
	 *
	 * @var string
	 * @access private
	 */
	private $rne = "";

	/**
	 * Nom de l'établissement
	 *
	 * @var string
	 * @access private
	 */
	private $etab = "";

	/**
	 * Année scolaire
	 *
	 * @var string
	 * @access private
	 */
	private $year = 0;

	/**
	 * Informations sur les individus présents dans le fichier XML
	 *
	 * @var array
	 * @access private
	 */
	private $profs = array();

	/**
	 * Informations sur les individus présents dans le CDT
	 *
	 * @var array
	 * @access private
	 */
	private $cdt_profs = array();

	/**
	 * Nombre d'individus non lus dans le fichier XML
	 *
	 * @var int
	 * @access private
	 */
	private $profs_errors = 0;

	/**
	 * Informations sur les matières présentes dans le fichier XML
	 *
	 * @var array
	 * @access private
	 */
	private $matieres = array();

	/**
	 * Informations sur les matières présentes dans le CDT
	 *
	 * @var array
	 * @access private
	 */
	private $cdt_matieres = array();

	/**
	 * Nombre de matières non lues dans le fichier XML
	 *
	 * @var int
	 * @access private
	 */
	private $matieres_errors = 0;

	/**
	 * Informations sur les classes présentes dans le fichier XML
	 *
	 * @var array
	 * @access private
	 */
	private $classes = array();

	/**
	 * Informations sur les classes présentes dans le CDT
	 *
	 * @var array
	 * @access private
	 */
	private $cdt_classes = array();

	/**
	 * Nombre de classes non lues dans le fichier XML
	 *
	 * @var int
	 * @access private
	 */
	private $classes_errors = 0;

	/**
	 * Informations sur les groupes présents dans le fichier XML
	 *
	 * @var array
	 * @access private
	 */
	private $groupes = array();

	/**
	 * Informations sur les groupes présents dans le CDT
	 *
	 * @var array
	 * @access private
	 */
	private $cdt_groupes = array();

	/**
	 * Nombre de groupes non lus dans le fichier XML
	 *
	 * @var int
	 * @access private
	 */
	private $groupes_errors = 0;

	/**
	 * Informations sur les regroupements  (ou groupements inter-classes) présents dans le fichier XML
	 *
	 * @var array
	 * @access private
	 */
	private $gic = array();

	/**
	 * Informations sur les regroupements  (ou groupements inter-classes) présents dans le CDT
	 *
	 * @var array
	 * @access private
	 */
	private $cdt_gic = array();

	/**
	 * Nombre de regroupements  (ou groupements inter-classes) non lus dans le fichier XML
	 *
	 * @var int
	 * @access private
	 */
	private $gic_errors = 0;

	/**
	 * Informations sur les plages horaires spécifiées dans le CDT
	 *
	 * @var array
	 * @access private
	 */
	private $cdt_horaires = array();

	/**
	 * Informations sur les emplois du temps
	 *
	 * @var array
	 * @access private
	 */
	private $edt = array();

	/**
	 * Nombre de problèmes de lectures rencontrées pour atteindre les cours
	 *
	 * @var int
	 * @access private
	 */
	private $edt_errors = 0;
	
	/**
	 * Suffixe numérique à mettre éventuellement dans un login (pour éviter les doublons)
	 * login => quantité utilisée
	 *
	 * @var array
	 * @access private
	 */
	private $login_list = array();

	/**
	 * Base des motifs utilisés dans les modifications groupées de mots de passe ou de logins
	 * Attention, les bases "N" et "P" sont réservés
	 *
	 * @var array
	 * @access private
	 */
	private $text_motifs = array
		(
		"code" => "CODE",
		"naissance" => "NAISSANCE",
		"nom" => "NOM",
		"prenom" => "PRENOM"
		);

	/**
	 * Le délimiteur des motifs utilisés
	 *
	 * @var string
	 * @access private
	 */
	private $delimiteur = "%";

	/**
	 * Motif pour le code d'une donnée
	 * Utilisé pour générer des mots de passe pour les classes
	 *
	 * @var string
	 * @access private
	 */
	private $motif_code = "";
	
	/**
	 * Motif pour le nom d'un individu
	 *
	 * @var string
	 * @access private
	 */
	private $motif_nom = "";

	/**
	 * Motif pour le prénom d'un individu
	 *
	 * @var string
	 * @access private
	 */
	private	$motif_prenom = "";

	/**
	 * Motif pour la date de naissance (jjmmaaaa)
	 *
	 * @var string
	 * @access private
	 */
	private	$motif_naissance = "";

	/**
	 * Motif pour une lettre du nom d'un individu
	 *
	 * @var string
	 * @access private
	 */
	private	$motif_n = "";

	/**
	 * Motif pour une lettre du prénom d'un individu
	 *
	 * @var string
	 * @access private
	 */
	private	$motif_p = "";

	/**
	 * Motif demandé par l'utilisateur pour un mot de passe
	 *
	 * @var string
	 * @access private
	 */
	private $pwd_motif = "";

	/**
	 * Motif demandé par l'utilisateur pour un login
	 *
	 * @var string
	 * @access private
	 */
	private $login_motif = "";

	/**
	 * Correspondances entre alternances prises en comptes et semaines à afficher dans le cdt
	 *
	 * @var array
	 * @access private
	 */
	private $semaines = array
		(
		"H" => "A et B", //cours hebdomadaires sur l'année
		"A" => "A", //cours une semaine sur deux en semaine A sur l'année
		"B" => "B",  //cours une semaine sur deux en semaine B sur l'année
		"S1" => "A et B",  //cours au premier semestre, hebdomadaires mais période limitée
		"S2" => "A et B",  //cours au deuxième semestre, hebdomadaires mais période limitée
		);

	/**
	 * Définition des jours
	 *
	 * @var array
	 * @access private
	 */
	private $jours = array
		(
		1 => "Lundi",
		2 => "Mardi",
		3 => "Mercredi",
		4 => "Jeudi",
		5 => "Vendredi",
		6 => "Samedi",
		7 => "Dimanche"
		);

	/**
	 * Code des groupes de base à vérifier
	 * Ne pas changer l'ordre !
	 *
	 * @var array
	 * @access private
	 */
	private $gr_codes = array
		(
		"code" => array("classe_entiere","groupe_a","groupe_b","groupe_reduit"),
		"id" => array(0,0,0,0),
		"regexp" => array("classeentiere","groupe.*(a|1)","groupe.*(b|2)","groupereduit"),
		"form" => array(false,false,false,false) //pour vérification du formulaire
		);

	/**
	 * Nombre de cours lus dans emps_sts
	 *
	 * @var int
	 * @access private
	 */
	private $edt_compteur = 0;

	/**
	 * Nombre de cours désactivés dans la dernière étape
	 *
	 * @var int
	 * @access private
	 */
	private $edt_desactivated = 0;

	/**
	 * Nom du serveur LDAP
	 *
	 * @var string
	 * @access private
	 */
	private $ldap_server = "localhost";

	/**
	 * Port du serveur LDAP
	 *
	 * @var string
	 * @access private
	 */
	private $ldap_port = "389";

	/**
	 * Version du protocole LDAP
	 *
	 * @var string
	 * @access private
	 */
	private $ldap_version = "3";

	/**
	 * Gérer la version du protocole LDAP
	 *
	 * @var string
	 * @access private
	 */
	private $set_ldap_version = false;

	/**
	 * Base du chemin à utiliser dans les recherches LDAP
	 *
	 * @var string
	 * @access private
	 */
	private $ldap_base_dn = "o=gouv,c=fr";

	/**
	 * Filtre individu à utiliser dans les recherches LDAP
	 *
	 * @var string
	 * @access private
	 */
	private $ldap_user_filter = "(objectclass=PosixAccount)";
	//private $ldap_user_filter = '(objectclass=sambaSamAccount)(objectclass=inetOrgPerson)(!(description=Computer))';

	/**
	 * Filtre classe à utiliser dans les recherches LDAP
	 *
	 * @var string
	 * @access private
	 */
	private $ldap_classe_filter = "(objectclass=classe)";
	
	/**
	 * Attributs à récupérer dans les recherches LDAP
	 *
	 * @var string
	 * @access private
	 */
	private $ldap_user_attr = array("uid");

	/**
	 * Constructeur de la classe
	 * Initialise certaines variables et lance le traitement
	 */
	public function __construct()
		{
		$this->displayHeader(); //tout de suite pour avoir les css si interruption du script	
		
		//variables
		$this->cleanArray("all");
		if(isset($_SESSION["sess_rne"]) && preg_match("/^[0-9]{7}[a-z]{1}$/i",trim($_SESSION["sess_rne"]))) $this->rne = $_SESSION["sess_rne"]; //dans ent envole 1.5 (ajax-portail)
		else $this->rne = "RNE";
		$this->year = intval(date("Y"));
		if(time()<mktime(0,0,0,6,30,$this->year)) $this->year--;
		if(is_file($this->to_dir.$this->filenames["emp_sts"])) //étapes supplémentaires si fichier d'emplois du temps
			{
			$this->step_list[] = "pregroupes";
			$this->step_list[] = "groupes";
			$this->step_list[] = "gic";
			$this->step_list[] = "edt_param"; //choix de paramètres (alternances...)
			$this->step_list[] = "edt_import"; //importation effective
			}
		$this->step_list[] = "end"; //dernière étape : supprime les fichiers XML et renvoit à la première page de la procédure
		$this->motif_code = $this->delimiteur.$this->text_motifs["code"].$this->delimiteur;
		$this->motif_nom = $this->delimiteur.$this->text_motifs["nom"].$this->delimiteur;
		$this->motif_prenom = $this->delimiteur.$this->text_motifs["prenom"].$this->delimiteur;
		$this->motif_naissance = $this->delimiteur.$this->text_motifs["naissance"].$this->delimiteur;
		$this->motif_n = $this->delimiteur."[N]+".$this->delimiteur;
		$this->motif_p = $this->delimiteur."[P]+".$this->delimiteur;	

		//seule variable possible en GET pour supprimer le fichier de log
		if(isset($_GET["suplog"]) && is_file($this->to_dir.$this->filenames["log"])) unlink($this->to_dir.$this->filenames["log"]);
		
		//étape actuelle
		if(isset($_POST["step"]) && !empty($_POST["step"]))
			{
			if(in_array($_POST["step"],$this->step_list)) $this->step = $_POST["step"];
			else $this->death("L&rsquo;&eacute;tape <i>".$_POST["step"]."</i> n&rsquo;est pas pr&eacute;vue par le programme.");
			}
		else $this->step = "start";
		
		//parser les fichiers dès qu'ils sont disponibles, on récupère les 2 variables $this->sts_emp et $this->emp_sts
		//si étape d'upload, ils sont loadés après avoir été récupérés (car problème si les fichiers existent déjà en arrivant à cette étape)
		if($this->step!="upload") $this->loadFiles();
		
		//connexion à la base de données
		$this->connexion();
		
		//demande de réinitialisation des associations
		if($this->emp_sts && isset($_POST["reinit"]) && $_POST["reinit"]=="yes") 
			{
			$this->startSession();
			$this->step = $this->step_list[2]; //juste après l'upload
			}

		//format spécifique demandé pour les mots de passe
		if(isset($_POST["my_pwd_motif"]) && !empty($_POST["my_pwd_motif"])) $this->pwd_motif = $this->getPost("my_pwd_motif");
		elseif(isset($_POST["pwd_motif"]) && !empty($_POST["pwd_motif"])) $this->pwd_motif = $this->getPost("pwd_motif");
		elseif($this->step=="profs") $this->pwd_motif = $this->motif_naissance;
		else $this->pwd_motif = ""; //cas des classes
		
		//format spécifique demandé pour les logins utilisateurs
		if(isset($_POST["my_login_motif"]) && !empty($_POST["my_login_motif"]))
			{
			$this->login_motif = $this->getPost("my_login_motif");
			if($this->emp_sts) $_SESSION[$this->sessname]["profs"] = array(); //recherche sous un autre format de login
			}
		elseif(isset($_POST["login_motif"]) && !empty($_POST["login_motif"])) $this->login_motif = $this->getPost("login_motif");
		else $this->login_motif = $this->motif_prenom.".".$this->motif_nom;

		//appel des fonctions de traitement et d'affichage
		$this->launchStep();
		}

	/**
	 * Traitements à faire en fin d'exécution ou arrêt du script
	 */
	public function __destruct()
		{
		$this->displayFooter();
		//$this->displaySession();
		}

	/**
	 * Lancement d'une étape du traitement
	 * Recherche de l'étape suivante
	 * Les étapes sont spécifiées dans $this->step_list
	 * Chaque étape {step} possède sa méthode {step}_process pour les traitements et éventuellement {step}_display pour l'affichage
	 * 
	 * @param string $step si une étape est spécifiée, celle-ci est chargée en priorité 
	 */
	public function launchStep($step="")
		{
		if(!empty($step)) $this->step = $step;
		
		//étape suivante
		if(!$this->emp_sts && $this->step=="classes") $this->step_next = "end";
		else
			{
			$key = array_search($this->step,$this->step_list);
			if($key!==false && ++$key<count($this->step_list)) $this->step_next = $this->step_list[$key];
			else $this->step_next = $this->step_list[0];
			}
		
		//appel des méthodes
		$callback = array($this,$this->step."_process");
		if(is_callable($callback))
			{
			call_user_func($callback);
			$echo_callback = array($this,$this->step."_display");
			if(is_callable($echo_callback)) call_user_func($echo_callback);
			}
		else $this->death("Tentative d&rsquo;utiliser une proc&eacute;dure non pr&eacute;vue. Impossible de poursuivre.");
		}

	/**
	 * Test pour vérifier si le cdt a bien une base à jour avec les emplacements pour les codes
	 * On se contente de vérifier pour la table cdt_classe
	 */
	public function testCdt()
		{
		$isOk = false;
		$query_test = "SHOW COLUMNS FROM `cdt_classe`";
		$this->query($query_test);
		while($row=mysqli_fetch_assoc($this->requete))
			{
			if($row["Field"]=="code_classe")
				{
				$isOk = true;
				break;
				}
			}
		return $isOk;
		}

	/**
	 * Test pour vérifier les codes des groupes de base (pas le cas des primo_installation antérieures à la version 4.5.0.1)
	 * Le tableau $gr_codes est mis à jour
	 */
	public function testCdt2()
		{
		$is_ok = true;
		foreach($this->gr_codes["code"] as $n=>$code)
			{
			/*
			$query_test = "SELECT count(*) FROM `cdt_groupe` WHERE `code_groupe`='".$groupe[0]."'";
			$this->query($query_test);
			$row = mysqli_fetch_row($this->requete);
			$total = intval($row[0]);
			if($total>0) $this->gr_codes[$n][1] = true;
			else $is_ok = false;
			*/
			
			if(in_array($code,$this->cdt_groupes["code"]))
				{
				$key = array_search($code,$this->cdt_groupes["code"]);
				$this->gr_codes["id"][$n] = $this->cdt_groupes["id"][$key];
				}
			else $is_ok = false;
			}
		return $is_ok;
		}

	/**
	 * Initialise la session chargée de mémoriser les correspondances XML/CDT
	 */
	public function startSession()
		{
		unset($_SESSION[$this->sessname]);
		$_SESSION[$this->sessname] = array
			(
			"profs" => array(),
			"matieres" => array(),
			"classes" => array(),
			"groupes" => array(),
			"gic" => array()
			);			
		}

	/**
	 * Initialise les tableaux dans lesquels sont placées les différentes données
	 * 
	 * @param string $tab le tableau particulier à (ré)initialiser
	 */
	public function cleanArray($tab="all")
		{
		if($tab="all" || $tab="profs")
			{
			$this->profs = array
				(
				"id" => array(),
				"code" => array(), //correspond à l'id du XML mais traiter contre les espaces, on ne sait jamais...
				"login" => array(),
				"nom" => array(),
				"prenom" => array(),
				"identite" => array(),
				"naissance" => array(),
				"statut" => array(),
				"etat" => array(),
				"pwd" => array()
				);
			$this->profs_errors = 0;
			$this->login_list = array();
			}

		if($tab="all" || $tab="matieres")
			{
			$this->matieres = array
					(
					"id" => array(),
					"code" => array(), //basé sur un libellé
					"nom" => array(),
					"etat" => array()
					);
			$this->matieres_errors = 0;
			}
		
		if($tab="all" || $tab="classes")
			{
			$this->classes = array
				(
				"id" => array(),
				"code" => array(), //basé sur l'id (mais sans espaces...)
				"nom" => array(),
				"etat" => array(),
				"pwd" => array()
				);
			$this->classes_errors = 0;
			}

		if($tab="all" || $tab="groupes")
			{
			$this->groupes = array
				(
				"id" => array(),
				"code" => array(), //basé sur l'id (mais sans espaces...)
				"nom" => array(),
				"indication" => array(),
				"etat" => array(),
				"classe" => array(), //classe concernée
				"matieres" => array() //matières concernées
				);
			$this->gic_errors = 0;
			}

		if($tab="all" || $tab="gic")
			{
			$this->gic = array
				(
				"id" => array(),
				"code" => array(), //basé sur l'id + id_xml matiere + id_cdt prof
				"nom" => array(),
				"etat" => array(),
				"classes" => array(), //classes concernées
				"matiere" => array(), //matière concernée
				"prof" => array() //enseignant concerné
				);
			$this->gic_errors = 0;
			}

		if($tab="all" || $tab="alternances")
			{
			$this->alternances = array
				(
				"id" => array(),
				"code" => array(),
				"nom" => array(),
				"type" => array(), // H, A, B, S1 ou S2
				"first" => array(), //timestamp
				"second" => array(), //timestamp (pour repérer les alternances par quinzaine)
				"last" => array(), //timestamp
				"semaines" => array()
				);
			}
	
		if($tab="all" || $tab="edt")
			{
			$this->edt = array();
			$this->edt_errors = 0;
			$this->edt_compteur = 0;
			}	

		if($tab="all" || $tab="cdt_profs")
		$this->cdt_profs = array
			(
			"id" => array(),
			"login" => array(),
			"nom" => array(),
			"statut" => array()
			);

		if($tab="all" || $tab="cdt_matieres")
		$this->cdt_matieres = array
			(
			"id" => array(),
			"code" => array(),
			"nom" => array()
			);
	
		if($tab="all" || $tab="cdt_classes")
		$this->cdt_classes = array
			(
			"id" => array(),
			"code" => array(),
			"nom" => array(),
			"pwd" => array()
			);
		
		if($tab="all" || $tab="cdt_groupes")
		$this->cdt_groupes = array
			(
			"id" => array(),
			"code" => array(),
			"nom" => array()
			);

		if($tab="all" || $tab="cdt_gic")
		$this->cdt_gic = array
			(
			"id" => array(),
			"code" => array(),
			"nom" => array(),
			"prof" => array(),
			"classes" => array()
			);

		if($tab="all" || $tab="cdt_horaires")
		$this->cdt_horaires = array
			(
			"id" => array(),
			"start" => array(),
			"end" => array()
			);
		}

	/**
	 * Connexion MySQL
	 */
	public function connexion()
		{
		if(!is_file($this->file_connexion)) $this->death("Le fichier de connexion &agrave; la base de donn&eacute;es est introuvable. Impossible de poursuivre.");
		@include($this->file_connexion);
		$this->db_conn = $conn_cahier_de_texte;	
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		
		if(!mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte))
		$this->death("La s&eacute;lection de la base de donn&eacute;es <i>".$database_conn_cahier_de_texte."</i> a &eacute;chou&eacute;.");
		}

	/**
	 * Requête MySQL
	 * Une seule ressource est prévue pour être connue à la fois
	 * 
	 * @param string  $sql  la requête à effectuer
	 * @param boolean $save précise s'il faut mémoriser la ressource résultante
	 */
	public function query($sql,$save=true)
		{
	
		global $database_conn_cahier_de_texte;
		global $conn_cahier_de_texte;
		if(!$this->db_conn) $this->death("Impossible d'effectuer une requ&ecirc;te car la connexion MySQL est inexistante.");

		mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
		$result = mysqli_query($conn_cahier_de_texte, $sql);
		if(!$result) $this->death("La requête suivante a échoué :<br/>".$sql."</br>");
		if($save) $this->requete = $result;
		}	

	/**
	 * Traitement de l'étape initiale "start"
	 */
	public function start_process()
		{
		if(!$this->testCdt()) $this->death("La base utilis&eacute;e par le cahier de textes n&rsquo;est pas &agrave; jour, l&rsquo;utilisation des codes n&rsquo;est donc pas possible.");
		$this->startSession();
		$this->unlinkXML(); //par précaution mais fait aussi à la fin de l'importation
		}

	/**
	 * Affichage de l'étape initiale "start"
	 */
	public function start_display()
		{
		if($this->saveImport && is_file($this->to_dir.$this->filenames["log"])) $log_link = "<p>Il existe un fichier de log d&rsquo;une pr&eacute;c&eacute;dente importation des emplois du temps : <a href=\"".$this->to_dir.$this->filenames["log"]."\" target=\"_blank\">voir</a> - <a href=\"import_sconet.php?suplog\">supprimer</a>.</p>";
		else  $log_link = "";

		echo "
		<div id=\"div_import\">
		<div class=\"commentaire\">
		Il est vivement conseill&eacute; d'effectuer une <a href=\"sauvegarde1.php\">sauvegarde</a> des donn&eacute;es avant toute nouvelle importation.<br/>
		</div>
		".$log_link."
		<p>Pour r&eacute;aliser l&rsquo;importation dans les tables du cahier de textes, vous avez besoin de deux fichiers XML :</p>
		<ul>
		<li><b>sts_emp_".$this->rne."_".$this->year.".xml</b> :
			<ul style=\"padding:2px 0px 10px 30px;\">
			<li>il est obligatoire</li>
			<li>il est obtenu par une exportation des donn&eacute;es depuis STSweb (sur SCONET)</li>
			<li>il contient les agents, les mati&egrave;res et les classes</li>
			</ul>
		</li>
		<li><b>emp_sts_".$this->rne."_".$this->year.".xml</b> :
			<ul style=\"padding:2px 0px 10px 30px;\">
			<li>il est facultatif</li>
			<li>il est obtenu par une exportation des donn&eacute;es depuis votre logiciel d&rsquo;emploi du temps.</li>
			<li>il contient les regroupements et les emplois du temps (sauf avec EDT2009)</li>
			</ul>
		</li>
		</ul>
		Chargement des fichiers :
		<form enctype=\"multipart/form-data\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" onsubmit=\"return confirmInput(this);\">
		<p style=\"margin-bottom:20px;\">
		<label>
		<input type=\"radio\" name=\"type_importation\" value=\"1\" onclick=\"activeInput(this.form);\" checked=\"checked\"/>Je ne dispose que du premier fichier pour importer uniquement les donn&eacute;es de base.
		</label>
		<br/>
		<label>
		<input type=\"radio\" name=\"type_importation\" value=\"2\" onclick=\"activeInput(this.form);\"/>J&rsquo;utilise les deux fichiers pour importer en plus les emplois du temps.
		</label>
		</p>
		<fieldset>
		<legend style=\"font-family:verdana; font-size:11; font-weight:700;\">sts_emp_".$this->rne."_".$this->year.".xml :</legend>
		<input type=\"file\" size=\"60\" name=\"sts_emp\"/>
		</fieldset>
		<fieldset style=\"display:none;\" id=\"file_emp_sts\">
		<legend style=\"font-family:verdana; font-size:11; font-weight:700;\">emp_sts_".$this->rne."_".$this->year.".xml :</legend>
		<input type=\"file\" size=\"60\" name=\"emp_sts\"/>
		</fieldset>
		<input type=\"hidden\" name=\"step\" value=\"".$this->step_next."\" />
		<p style=\"text-align:center;\"><input type=\"submit\" name=\"soumettre\" value=\"".$this->step_display[$this->step_next][1]."\"/></p>
		</form>
		</div>";
		}

	/**
	 * Traitement de l'étape "upload"
	 * Récupération et chargement du (des) fichier(s) XML
	 */
	public function upload_process()
		{
		$upload_first_file = isset($_POST["type_importation"]) && isset($_FILES["sts_emp"]);
		$upload_second_file = $upload_first_file && $_POST["type_importation"]=="2" && isset($_FILES["emp_sts"]);
		
		if($upload_first_file)
			{
			$first_file_name = $_FILES['sts_emp']['name'];
			if(preg_match("/sts_emp_[0-9a-z]{8}_[0-9]{4}\.xml/i",$first_file_name)) $this->file_upload("sts_emp");
			else $this->death("Le premier fichier <i>".$first_file_name."</i> doit &ecirc;tre de la forme <b>sts_emp_".$this->rne."_".$this->year.".xml</b>. Envoyez-le &agrave; nouveau.");
			}
		else $this->death("Le premier fichier est obligatoire et n&rsquo;a pas &eacute;t&eacute; transmis.");
		
		if($upload_second_file)
			{
			$second_file_name = $_FILES['emp_sts']['name'];
			if(preg_match("/emp_sts_[0-9a-z]{8}_[0-9]{4}\.xml/i",$second_file_name)) $this->file_upload("emp_sts");
			else $this->death("Le second fichier <i>".$second_file_name."</i> doit &ecirc;tre de la forme <b>emp_sts_".$this->rne."_".$this->year.".xml</b>. Envoyez-le &agrave; nouveau.");
			}

		$this->loadFiles();
		$this->loadXml("all"); //à cette étape, on charge tout le XML pour donner quelques informations et vérifier une première fois les données (comme les alternances si emp_sts)
		if($this->emp_sts) $this->loadEdt(); //pour en afficher le nombre
		}

	/**
	 * Affichage de l'étape "upload"
	 * Présentation de quelques informations sommaires
	 */	
	public function upload_display()
		{
		if($this->emp_sts)
		$type_importation = "
		Vous allez pouvoir importer en plus des donn&eacute;es classiques l&rsquo;ensemble des emplois du temps des enseignants.<br/>
		Pour ce faire, chaque donn&eacute;e du XML &agrave; prendre en compte devra &ecirc;tre associ&eacute;e &agrave; une donn&eacute;e du cdt.";
		elseif($this->sts_emp)
		$type_importation = "Vous allez pouvoir importer uniquement les donn&eacute;es permettant de d&eacute;marrer l'&rsquo;utilisation du cahier de textes.";
		else
		$this->death("Une erreur est survenue pendant l&rsquo;&eacute;tape d&rsquo;upload des fichiers. Essayez &agrave; nouveau.");

		echo "
		<div id=\"div_import\">
		<p><b>".$_FILES["sts_emp"]["name"]." :</b> <span class=\"succes\">Upload effectu&eacute; avec succ&egrave;s.</span></p>";
		if($this->emp_sts)
			{
			echo "<p><b>".$_FILES["emp_sts"]["name"]." :</b> <span class=\"succes\">Upload effectu&eacute; avec succ&egrave;s.</span></p>";
			$info_cours = "<li>";
			if($this->edt_compteur===0) $info_cours .= "<span class=\"echec\">Le fichier ".$_FILES["emp_sts"]["name"]." ne contient aucune heure d&rsquo;emploi du temps.</span>";
			else $info_cours .= "Nombre de cours lus : <span class=\"styleP\">".$this->edt_compteur."</span> (".($this->edt_errors>0 ? "<span class=\"echec\">".$this->edt_errors." erreur".($this->edt_errors>1 ? "s" : "")."</span>" : "<span class=\"succes\">Aucune erreur</span>").").";
			$info_cours .= "</li>";
			}
		else $info_cours = "";
		echo "
		<p style=\"margin:20px 0px;\">".$type_importation."</p>
		<p><u>Selon les donn&eacute;es fournies</u> :</p>
		<ul>
		<li>Le RNE de votre &eacute;tablissement est <span class=\"styleP\">".$this->rne."</span>.</li>
		<li>Votre &eacute;tablissement se nomme <span class=\"styleP\">".$this->etab."</span>.</li>
		<li>Importation des donn&eacute;es pour l'ann&eacute;e scolaire <span class=\"styleP\">".$this->year."/".($this->year+1)."</span>.</li>
		<li>Nombre d&rsquo;utilisateurs lus : <span class=\"styleP\">".count($this->profs["code"])."</span> (".($this->profs_errors>0 ? "<span class=\"echec\">".$this->profs_errors." erreur".($this->profs_errors>1 ? "s" : "")."</span>" : "<span class=\"succes\">Aucune erreur</span>").").</li>
		<li>Nombre de mati&egrave;res lues : <span class=\"styleP\">".count($this->matieres["code"])."</span> (".($this->matieres_errors>0 ? "<span class=\"echec\">".$this->matieres_errors." erreur".($this->matieres_errors>1 ? "s" : "")."</span>" : "<span class=\"succes\">Aucune erreur</span>").").</li>
		<li>Nombre de classes lues : <span class=\"styleP\">".count($this->classes["code"])."</span> (".($this->classes_errors>0 ? "<span class=\"echec\">".$this->classes_errors." erreur".($this->classes_errors>1 ? "s" : "")."</span>" : "<span class=\"succes\">Aucune erreur</span>").").</li>
		".$info_cours."
		</ul>
		<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
		<input type=\"hidden\" name=\"step\" value=\"".$this->step_next."\" />
		<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][0]."\"/></p>
		</form>
		</div>";
		}

	/**
	 * Vérification des données d'un formulaire
	 * 
	 * @param  string  $step étape de la procédure
	 * @return boolean true si formulaire reçu et données valides
	 */
	public function checkForm($step)
		{
		$liste = array("profs","matieres","classes","groupes","gic");
		$isOk = true;
		$others = 0;
		$datas = 0;
		$total = 0;
		if(in_array($step,$liste))
			{
			foreach($this->{$step}["code"] as $i=>$code)
				{
				if($this->{$step}["etat"][$i]!="I") $others++; //donc donnée déjà présente dans le cdt et identifiée à la lecture du XML (possible seulement si pas emp_sts)
				elseif($step=="profs" && isset($_POST["ident_".$code]) && isset($_POST["nom_".$code]) && preg_match("/[0-9a-z]+/i",$_POST["ident_".$code]) && preg_match("/[0-9a-z]+/i",$_POST["nom_".$code])) $datas++;
				elseif($step!="profs" && isset($_POST["nom_".$code]) && preg_match("/[0-9a-z]+/i",$_POST["nom_".$code])) $datas++; //même test pour les autres types de données
				$total++;
				}
			
			if($datas==0) $isOk = false; //aucune donnée envoyée, aucun traitement à réaliser
			elseif(($datas+$others)!==$total) //données reçues mais toutes ne sont pas valides et/ou présentes
				{
				$this->sendMessage("-- FORMULAIRE INCOMPLET --<br/>Veuillez v&eacute;rifier que toutes les données sont valides (pas de champ laiss&eacute; vide par exemple).<br/>Relancez ensuite la proc&eacute;dure.");
				$isOk = false; //pas de traitement des données à faire
				}
			}
		else $isOk = false;
		return $isOk;
		}

	/**
	 * Traitement de l'étape "profs"
	 * Possible si un individu est dans l'état importable "I"
	 * A lieu lors de l'envoi du formulaire
	 */
	public function profs_process()
		{
		$this->getCdt("profs");
		$this->loadXml("profs");
		global $database_conn_cahier_de_texte;
		global $conn_cahier_de_texte;
		//Vérification de l'intégrité de l'ensemble des données avant de les traiter
		if(!$this->checkForm("profs")) return true;
		
		//Traitement des données envoyées
		foreach($this->profs["code"] as $i=>$code)
			{
			$id_xml = $this->profs["id"][$i]; //utile uniquement pour les correspondances lors de l'importation des emplois du temps
			if($this->profs["etat"][$i]!="I") continue; //donc utilisateur déjà présent identifié à la lecture du XML
			elseif(isset($_POST["cb_".$code])) //importation demandée
				{
				$nom = $this->getPost("ident_".$code);
				$login = $this->getPost("nom_".$code);
				$login = preg_replace('/[^a-z0-9.]/i','',strtolower($login));
				
				if($this->emp_sts && array_key_exists($id_xml,$_SESSION[$this->sessname]["profs"])) //correspondance déjà identifiée à la lecture du xml
					{
					$id_cdt = $_SESSION[$this->sessname]["profs"][$id_xml];
					if(in_array($id_cdt,$this->cdt_profs["id"]))
						{
						$etat = "P"; //déjà présent donc plus importable
						$key = array_search($id_cdt,$this->cdt_profs["id"]);
						$nom = empty($this->cdt_profs["nom"][$key]) ? $this->cdt_profs["login"][$key] : $this->cdt_profs["nom"][$key];
						$login = $this->cdt_profs["login"][$key]; //login à jour
						}
					else $this->death("L&rsquo;utilisateur dont l&rsquo;identifiant est <i>".$id_cdt."</i> dans le cdt n&rsquo;a pas &eacute;t&eacute; retrouv&eacute;."); 
					}
				elseif(in_array($login,$this->cdt_profs["login"])) //éviter de créer un deuxième utilisateur identique
					{
					$etat = "P"; //déjà présent donc plus importable
					$key = array_search($login,$this->cdt_profs["login"]);
					$nom = empty($this->cdt_profs["nom"][$key]) ? $login : $this->cdt_profs["nom"][$key];
					if($this->emp_sts) $_SESSION[$this->sessname]["profs"][$id_xml] = $this->cdt_profs["id"][$key]; //correspondances pour les emplois du temps
					}
				else
					{
					$pwd = (isset($_POST["pwd_".$code]) && !empty($_POST["pwd_".$code])) ? $this->getPost("pwd_".$code) : $this->profs["naissance"][$i];
					$this->profs["pwd"][$i] = $pwd;
						//$pwd = md5($pwd);
						$pwd = password_hash($pwd, PASSWORD_DEFAULT);
					$droits = $this->getXmlData("statuts",$this->profs["statut"][$i],"droits");
					if(empty($droits)) $droits = 2; //par précaution
					$query = "
					INSERT INTO `cdt_prof` (`nom_prof`,`identite`,`passe`,`droits`) 
					VALUES ('".mysqli_real_escape_string($conn_cahier_de_texte, $login)."','".mysqli_real_escape_string($conn_cahier_de_texte, $nom)."','".mysqli_real_escape_string($conn_cahier_de_texte, $pwd)."','".$droits."');";
					$this->query($query);
					$new_id = mysqli_insert_id($conn_cahier_de_texte);
					if($droits==2) //si prof, un type "cours" par défaut pour l'enregistrement de séances
						{
						$query_activite = "INSERT INTO `cdt_type_activite` (`ID_prof`,`activite`,`pos_typ`) VALUES (".$new_id.",'Cours',1)";
						$this->query($query_activite);
						}
					$etat = "R"; //importation réussie
					if($this->emp_sts) $_SESSION[$this->sessname]["profs"][$id_xml] = $new_id; //correspondances pour les emplois du temps

					//mise à jour
					$a = count($this->cdt_profs["id"]);
					while(isset($this->cdt_profs["id"][$a])) $a++; //on ne sait jamais...
					$this->cdt_profs["id"][$a] = $new_id;
					$this->cdt_profs["login"][$a] = $login;
					$this->cdt_profs["nom"][$a] = $nom;
					}

				$this->profs["identite"][$i] = $nom; //nom à jour
				$this->profs["login"][$i] = $login; //login à jour
				}
			elseif($this->emp_sts && isset($_POST["ref_".$code])) //si pas d'importation, correspondance obligatoire avec l'existant destinée à l'import des emplois du temps
				{
				$id_cdt = intval($_POST["ref_".$code]);
				$_SESSION[$this->sessname]["profs"][$id_xml] = $id_cdt;
				if($id_cdt==0) $etat = "N"; //demande de non importation
				elseif(in_array($id_cdt,$this->cdt_profs["id"]))
					{
					$key = array_search($id_cdt,$this->cdt_profs["id"]);
					$nom = empty($this->cdt_profs["nom"][$key]) ? $this->cdt_profs["login"][$key] : $this->cdt_profs["nom"][$key];
					$this->profs["login"][$i] = $this->cdt_profs["login"][$key]; //login à jour (on laisse par contre le nom indiqué dans le fichier xml)
					$etat = "P"; //référencé donc plus importable
					}
				else $this->death("L&rsquo;utilisateur dont l&rsquo;identifiant est <i>".$id_cdt."</i> dans le cdt n&rsquo;a pas &eacute;t&eacute; retrouv&eacute;e.");
				}
			else $etat = "N"; //on n'arrive en principe jamais là si emp_sts
			$this->profs["etat"][$i] = $etat;
			}
		}

	/**
	 * Affichage de l'étape "profs"
	 * Etape suivante possible uniquement si plus aucun individu n'est dans l'état importable "I"
	 * Si emp_sts : liste proposée pour correspondance avec l'existant
	 */
	public function profs_display()
		{
		if(empty($this->profs)) $this->death("Aucun utilisateur n&rsquo;a &eacute;t&eacute; r&eacute;cup&eacute;r&eacute;e."); 
		else
			{
			$output = "";
			$c = 0; //couleur
			$select = "";
			$changeInput = "";
			
			if(in_array("I",$this->profs["etat"])) //il y a des données à valider
				{
				$this->step_next = "profs"; //on reste dans l'étape d'importation des profs
				$onclick = "onclick=\"return checkSaisie(this.form);\"";
				if($this->emp_sts) $changeInput = "changeInput(this);";
				$isReady = false;
				$index = 1;
				$etape = "(s&eacute;lection)";
				}
			else
				{
				$onclick = "";
				$isReady = true;
				$index = 0;
				$etape = "(r&eacute;sultat)";
				}
			
			if($this->useEnvole)
				{
				$envole_message = "
				<div class=\"commentaire\">
				<p style=\"text-align:center; font-weight:bold;\">Commentaire EnVOLE</p>
				Tout <u>login soulign&eacute;</u> correspond &agrave; un identifiant retrouv&eacute; dans l&rsquo;annuaire des utilisateurs du Scribe.<br/>
				Si vous renseignez un login qui n&rsquo;existe pas dans l&rsquo;annuaire, il sera possible ensuite d&rsquo;aller modifier le compte dans le cdt.<br/>
				Un compte est cr&eacute;&eacute; dans le cahier de textes avec le mot de passe sp&eacute;cifi&eacute; mais avec le SSO, celui-ci ne sera pas utilis&eacute;.
				</div>";
				}
			else $envole_message = "";

			$form_pwd_login = "
			<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
			<div id=\"div_import\">
			<table>
			<tr>
			<td rowspan=\"2\"><input type=\"submit\" value=\"Actualiser\"/><input type=\"hidden\" name=\"step\" value=\"".$this->step."\"/></td>
			<td><input type=\"text\" name=\"my_pwd_motif\" value=\"".$this->pwd_motif."\"/></td>
			<td>
			>>> Cr&eacute;ation group&eacute;e de mots de passe
			&nbsp;(<a href=\"#\" onclick=\"alert('Les mots cl&eacute;s suivants seront remplac&eacute;s par leur valeur :\\n\\n".$this->motif_naissance."\\n".$this->motif_nom."\\n".$this->motif_prenom."\\n".$this->delimiteur."NN".$this->delimiteur." par 2 lettres du nom...\\n".$this->delimiteur."PPPPP".$this->delimiteur." par 5 lettres du prénom...');\">aide</a>)
			</td>
			</tr>
			<tr>
			<td><input type=\"text\" name=\"my_login_motif\" value=\"".$this->login_motif."\"/></td>
			<td>
			>>> Format du login
			&nbsp;(<a href=\"#\" onclick=\"alert('Par d&eacute;faut au format prenom.nom sinon doit &ecirc;tre de la forme debut.fin\\nIl sera tronqué à 20 caractères.\\nLe point est optionnel.\\ndebut et fin doivent être un des mots clés suivants qui sera remplac&eacute; par sa valeur :\\n\\n".$this->motif_nom."\\n".$this->motif_prenom."\\n".$this->delimiteur."NN".$this->delimiteur." par 2 lettres du nom...\\n".$this->delimiteur."PPPPP".$this->delimiteur." par 5 lettres du prénom...');\">aide</a>)
			</td>
			</tr>
			</table>
			</div>
			</form>";

			//affichage spécifique si import des emplois du temps à faire
			if($this->emp_sts)
				{
				$this->checkSession(); //provoque un affichage bilan des données enregistrées
				
				$precision = "<li>Pour les autres, les associer &agrave; des logins existants ou laisser \"Ne rien faire\" pour ne pas en tenir compte</li>";
				
				//liste triée des logins existants
				$select = "<select name=\"ref_::CODE::\" ::CLASS:: onchange=\"this.style.border='none';\">";
				$option_x = "<option selected=\"selected\" value=\"0\">Ne rien faire</option>";
				$options = "";
				$tmp_profs = array(); //pour classer par ordre alphabétique couples id/login selon les logins
				foreach($this->cdt_profs["login"] as $n=>$login)
					{
					$id = $this->cdt_profs["id"][$n];
					if(!empty($id)) $tmp_profs[$id] = $login;
					}
				asort($tmp_profs);
				foreach($tmp_profs as $id=>$login) $options .= "<option value=\"".$id."\">".$login."</option>";
				unset($tmp_profs);
				$options = empty($options) ? "<optgroup label=\"Aucun login disponible\"></optgroup>" : "<optgroup label=\"Liste des logins existants\">".$options."</optgroup>";
				}
			else $precision = "";

			foreach($this->profs["code"] as $i=>$code)
				{
				$etat = $this->profs["etat"][$i];
				switch($etat)
					{
					case "I" : //possibilité d'importer l'utilisateur
					$text_pwd = $this->getPwd($i); //date de naissance comme mot de passe par défaut
					$style = "style".$etat;
					$checkbox = "<input type=\"checkbox\" name=\"cb_".$code."\" onclick=\"selectOne(this); ".$changeInput."\" checked=\"checked\"/>";
					$text_pwd = "<input type=\"text\" class=\"text color".($c%2)." ".$style."\" name=\"pwd_".$code."\" value=\"".$text_pwd."\"/>";
					$statut = $this->profs["statut"][$i];
					$text_statut = $this->getXmlData("statuts",$statut,"nom","inconnu");
					$disabled = "";
					$myselect = "";
					if($this->useEnvole && $this->ldapCheckLogin($this->profs["login"][$i])) {$underline = "underline";}
					else $underline = "";
					if($this->emp_sts)
						{
						$id_xml = $this->profs["id"][$i];
						$myoptions = $option_x;
						
						//pré-association : login, nom et mot de passe non modifiables + liste vide, juste choix de non prise en compte
						if(array_key_exists($id_xml,$_SESSION[$this->sessname]["profs"]) && $_SESSION[$this->sessname]["profs"][$id_xml]>0)
							{
							$style = "styleP";
							$text_pwd = "d&eacute;j&agrave; renseign&eacute;";
							$disabled = "readonly=\"readonly\"";
							}
						else $myoptions .= $options;
						
						$myselect = str_replace("::CODE::",$code,$select);
						$myselect = str_replace("::CLASS::","class=\"color".($c%2)." ".$style."\"",$myselect);
						$myselect .= $myoptions."</select>";
						}
					$text_nom = "<input type=\"text\" class=\"text color".($c%2)." ".$style."\" name=\"ident_".$code."\" value=\"".$this->profs["identite"][$i]."\" ".$disabled."/>";
					$text_code = "<input type=\"text\" class=\"text color".($c%2)." ".$style." ".$underline."\" name=\"nom_".$code."\" value=\"".$this->profs["login"][$i]."\" ".$disabled."/>";
					$text_code .= $myselect;
					break;
					
					case "P" : //déjà existante, rien à faire
					case "R" : //venant d'être importée avec succès
					case "N" : //aucune importation demandée
					$style = "style".$etat;
					$checkbox = $etat;
					$text_code = $this->profs["login"][$i];
					if($this->useEnvole && $this->ldapCheckLogin($this->profs["login"][$i])) $text_code = "<u>".$text_code."</u>";
					$text_nom = $this->profs["identite"][$i];
					if($etat=="R") $text_pwd = $this->profs["pwd"][$i];
					elseif($etat=="P") $text_pwd = "d&eacute;j&agrave; renseign&eacute;";
					else $text_pwd = "--------";
					$statut = $this->profs["statut"][$i];
					$text_statut = $this->getXmlData("statuts",$statut,"nom","inconnu");
					break;
				
					default :
					continue;
					}
				$output .= "
				<tr class=\"color".($c%2)." ".$style."\">
				<td>".$checkbox."</td>
				<td>".$text_code."</td>
				<td>".$text_nom."</td>
				<td>".$text_pwd."</td>
				<td>".$text_statut."</td>
				</tr>";
				$c++;
				}
			echo "
			<div>
			<h2>&Eacute;tape d&rsquo;importation des utilisateurs ".$etape."</h2>
			".$envole_message."
			<div class=\"commentaire\">
			Les utilisateurs qui auraient &eacute;t&eacute; pr&eacute;c&eacute;demment import&eacute;s ne seront pas modifi&eacute;s. Si besoin, c&rsquo;est &agrave; faire <a href=\"prof_ajout.php\" target=\"_blank\">ici</a>.<br/>
			D&egrave;s qu&rsquo;un login test&eacute; est retrouv&eacute; dans le cdt, l&rsquo;utilisateur concern&eacute; ne peut &ecirc;tre associ&eacute; qu&rsquo;&agrave; celui-ci.
			<ol>
			<li>Choisir les utilisateurs que vous souhaitez importer</li>
			<li>Editer si besoin les logins/identit&eacute;s propos&eacute;s (modifiable ult&eacute;rieurement)</li>
			<li>Modifier &eacute;ventuellement le mot de passe, par d&eacute;faut &agrave; la date de naissance (modifiable ult&eacute;rieurement)</li>
			".$precision."
			<li>Lancer la proc&eacute;dure</li>
			</ol>
			</div>
			<ul style=\"text-align:left;\">
			<li class=\"styleP\">P : utilisateur pr&eacute;sent dans le cahier de textes</li>
			<li class=\"styleR\">R : cr&eacute;ation r&eacute;ussie de l&rsquo;utilisateur</li>
			<li class=\"styleN\">N : non prise en compte de l&rsquo;utilisateur</li>
			</ul>
			".($isReady ? "" : $form_pwd_login)."
			<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
			<div style=\"text-align:center;\">
			<p>
			".($isReady ? "<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][$index]."\" ".$onclick."/></p>" : "<input type=\"checkbox\" name=\"cb_all\" id=\"cb_all\" onclick=\"selectAll();\" checked=\"checked\"/>Tout s&eacute;lectionner")."
			</p>
			<table id=\"tableau\">
			<colgroup><col width=\"5%\"><col width=\"25%\"><col width=\"30%\"><col width=\"20%\"><col width=\"15%\"></colgroup>
			<tr><td></td><td>Login</td><td>Identit&eacute;</td><td>Mot de passe</td><td>Fonction</td></tr>
			".$output."
			</table>
			</div>
			<input type=\"hidden\" name=\"step\" value=\"".$this->step_next."\" />
			<input type=\"hidden\" name=\"pwd_motif\" value=\"".$this->pwd_motif."\"/>
			<input type=\"hidden\" name=\"login_motif\" value=\"".$this->login_motif."\" />
			<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][$index]."\" ".$onclick."/></p>
			</form>
			</div>";
			}
		}

	/**
	 * Traitement de l'étape "matieres"
	 * Possible si une matière est dans l'état importable "I"
	 * A lieu lors de l'envoi du formulaire
	 */
	public function matieres_process()
		{
		$this->getCdt("matieres");
		$this->loadXml("matieres");
		global $database_conn_cahier_de_texte;
		global $conn_cahier_de_texte;
		//Vérification de l'intégrité de l'ensemble des données avant de les traiter
		if(!$this->checkForm("matieres")) return true;
		
		//Traitement des données envoyées
		foreach($this->matieres["code"] as $i=>$code)
			{
			$id_xml = $this->matieres["id"][$i]; //utile uniquement pour les correspondances lors de l'importation des emplois du temps
			if($this->matieres["etat"][$i]!="I") continue; //donc matière déjà présente identifiée à la lecture du XML
			elseif(isset($_POST["cb_".$code])) //importation demandée
				{
				$nom = $this->getPost("nom_".$code);
				$nom_lower = strtolower($nom);
				
				if($this->emp_sts && array_key_exists($id_xml,$_SESSION[$this->sessname]["matieres"])) //correspondance déjà identifiée à la lecture du xml
					{
					$id_cdt = $_SESSION[$this->sessname]["matieres"][$id_xml];
					if(in_array($id_cdt,$this->cdt_matieres["id"]))
						{
						$etat = "P"; //déjà présent donc plus importable
						$key = array_search($id_cdt,$this->cdt_matieres["id"]);
						$nom = $this->cdt_matieres["nom"][$key];
						}
					else $this->death("La mati&egrave;re dont l&rsquo;identifiant est <i>".$id_cdt."</i> dans le cdt n&rsquo;a pas &eacute;t&eacute; retrouv&eacute;e."); 
					}
				elseif(in_array($nom_lower,$this->cdt_matieres["nom"])) //éviter de créer une deuxième matière identique
					{
					$etat = "P"; //déjà présente donc plus importable
					$key = array_search($nom_lower,$this->cdt_matieres["nom"]);
					$ref_id = $this->cdt_matieres["id"][$key];
					
					//on insère le code disponible s'il n'est pas présent
					$code_cdt = $this->cdt_matieres["code"][$key];
					if(empty($code_cdt)) $this->setCode("matieres",$key,$code);
					
					//correspondances pour les emplois du temps
					if($this->emp_sts) $_SESSION[$this->sessname]["matieres"][$id_xml] = $ref_id;
					}
				else
					{
					$query = "
					INSERT INTO `cdt_matiere` (`code_matiere`,`nom_matiere`) 
					VALUES ('".mysqli_real_escape_string($conn_cahier_de_texte, $code)."','".mysqli_real_escape_string($conn_cahier_de_texte, remplace_slash($nom))."');";
					$this->query($query);
					$etat = "R"; //importation réussie
					$new_id = mysqli_insert_id($conn_cahier_de_texte);
					if($this->emp_sts) $_SESSION[$this->sessname]["matieres"][$id_xml] = $new_id; //correspondances pour les emplois du temps
					
					//mise à jour
					$a = count($this->cdt_matieres["nom"]);
					while(isset($this->cdt_matieres["nom"][$a])) $a++; //on ne sait jamais...
					$this->cdt_matieres["id"][$a] = $new_id;
					$this->cdt_matieres["code"][$a] = $code;
					$this->cdt_matieres["nom"][$a] = $nom_lower;
					}
				
				$this->matieres["nom"][$i] = $nom; //nom à jour
				}
			elseif($this->emp_sts && isset($_POST["ref_".$code])) //si pas d'importation, correspondance obligatoire avec l'existant destinée à l'import des emplois du temps
				{
				$id_cdt = intval($_POST["ref_".$code]);
				$_SESSION[$this->sessname]["matieres"][$id_xml] = $id_cdt;
				if($id_cdt==0) $etat = "N"; //demande de non importation
				elseif(in_array($id_cdt,$this->cdt_matieres["id"]))
					{
					$key = array_search($id_cdt,$this->cdt_matieres["id"]);
					$this->matieres["nom"][$i] = $this->cdt_matieres["nom"][$key]; //nom à jour
					$etat = "P"; //référencé donc plus importable
					}
				else $this->death("La Mati&egrave;re dont l&rsquo;identifiant est <i>".$id_cdt."</i> dans le cdt n&rsquo;a pas &eacute;t&eacute; retrouv&eacute;e."); 
				}
			else $etat = "N"; //on n'arrive en principe jamais là si emp_sts
				
			$this->matieres["etat"][$i] = $etat;
			}
		}

	/**
	 * Affichage de l'étape "matieres"
	 * Etape suivante possible uniquement si plus aucune matière n'est dans l'état importable "I"
	 * Si emp_sts : liste proposée pour correspondance avec l'existant
	 */
	public function matieres_display()
		{
		if(empty($this->matieres)) $this->death("Aucune mati&egrave;re n&rsquo;a &eacute;t&eacute; r&eacute;cup&eacute;r&eacute;e."); 
		else
			{
			$output = "";
			$c = 0; //couleur
			$select = "";
			$changeInput = "";

			if(in_array("I",$this->matieres["etat"])) //il y a des données à valider
				{
				$this->step_next = "matieres"; //on reste dans l'étape d'importation des matières
				$onclick = "onclick=\"return checkSaisie(this.form);\"";
				if($this->emp_sts) $changeInput = "changeInput(this);";
				$isReady = false;
				$index = 1;
				$etape = "(s&eacute;lection)";
				}
			else
				{
				$onclick = "";
				$isReady = true;
				$index = 0;
				$etape = "(r&eacute;sultat)";
				}
			
			if($this->emp_sts)
				{
				$this->checkSession(); //provoque un affichage bilan des données enregistrées
				
				$precision = "<li>Pour les autres, les associer &agrave; des mati&egrave;res existantes ou laisser \"Ne rien faire\" pour ne pas en tenir compte</li>";
				
				//liste des matières existantes
				$select = "<select name=\"ref_::CODE::\" ::CLASS:: onchange=\"this.style.border='none';\">";
				$option_x = "<option selected=\"selected\" value=\"0\">Ne rien faire</option>";
				$options = "";
				foreach($this->cdt_matieres["nom"] as $n=>$nom)
					{
					$id = $this->cdt_matieres["id"][$n];
					if(!empty($id)) $options .= "<option value=\"".$id."\">".$nom."</option>";
					}
				$options = empty($options) ? "<optgroup label=\"Aucune mati&egrave;re disponible\"></optgroup>" : "<optgroup label=\"Liste des mati&egrave;res existantes\">".$options."</optgroup>";
				}
			else $precision = "";
			
			foreach($this->matieres["code"] as $i=>$code)
				{
				$etat = $this->matieres["etat"][$i];
				switch($etat)
					{
					case "I" : //possibilité d'importer la matiere
					$style = "style".$etat;
					$checkbox = "<input type=\"checkbox\" name=\"cb_".$code."\" onclick=\"selectOne(this); ".$changeInput."\" checked=\"checked\"/>";
					$text_code = $code;
					$disabled = "";
					$myselect = "";
					if($this->emp_sts)
						{
						$id_xml = $this->matieres["id"][$i];
						$myoptions = $option_x;
						if(array_key_exists($id_xml,$_SESSION[$this->sessname]["matieres"])) //pré-association : nom non modifiable + liste vide, juste choix de non prise en compte
							{
							$style = "styleP";
							$disabled = "readonly=\"readonly\"";
							}
						else $myoptions .= $options;
						$myselect = str_replace("::CODE::",$code,$select);
						$myselect = str_replace("::CLASS::","class=\"color".($c%2)." ".$style."\"",$myselect);
						$myselect .= $myoptions."</select>";
						}
					$text_nom = "<input type=\"text\" style=\"text-align:center; border:none; width:95%; cursor:pointer;\" class=\"color".($c%2)." ".$style."\" name=\"nom_".$code."\" value=\"".$this->matieres["nom"][$i]."\" ".$disabled."/>";
					$text_nom .= $myselect;
					break;
					
					case "P" : //déjà existante, rien à faire
					case "R" : //venant d'être importée avec succès
					case "N" : //aucune importation demandée
					$style = "style".$etat;
					$checkbox = $etat;
					$text_code = $code;
					$text_nom = $this->matieres["nom"][$i];
					break;
				
					default :
					continue;
					}
				$output .= "
				<tr class=\"color".($c%2)." ".$style."\">
				<td>".$checkbox."</td>
				<td>".$text_code."</td>
				<td>".$text_nom."</td>
				</tr>";
				$c++;
				}
			echo "
			<div>
			<h2>&Eacute;tape d&rsquo;importation des mati&egrave;res ".$etape."</h2>
			<div class=\"commentaire\">
			Les mati&egrave;res qui auraient &eacute;t&eacute; pr&eacute;c&eacute;demment import&eacute;es ne seront pas modifi&eacute;es. Si besoin, c&rsquo;est &agrave; faire <a href=\"matiere_ajout.php\" target=\"_blank\">ici</a>.<br/>
			D&egrave;s qu&rsquo;un code test&eacute; (ou un nom) est retrouv&eacute; dans le cdt, la mati&egrave;re concern&eacute;e ne peut &ecirc;tre associ&eacute;e qu&rsquo;&agrave; celui-ci.
			<ol>
			<li>Choisir les mati&egrave;res que vous souhaitez importer</li>
			<li>Editer si besoin les noms propos&eacute;s (modifiable ult&eacute;rieurement)</li>
			".$precision."
			<li>Lancer la proc&eacute;dure</li>
			</ol>
			</div>
			<ul style=\"text-align:left;\">
			<li class=\"styleP\">P : mati&egrave;re pr&eacute;sente dans le cahier de textes</li>
			<li class=\"styleR\">R : cr&eacute;ation r&eacute;ussie de la mati&egrave;re</li>
			<li class=\"styleN\">N : non prise en compte de la mati&egrave;re</li>
			</ul>
			<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
			<div style=\"text-align:center;\">
			<p>
			".($isReady ? "<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][$index]."\" ".$onclick."/></p>" : "<input type=\"checkbox\" name=\"cb_all\"  id=\"cb_all\" onclick=\"selectAll();\" checked=\"checked\"/>Tout s&eacute;lectionner")."
			</p>
			<table id=\"tableau\">
			<colgroup><col width=\"5%\"><col width=\"20%\"><col width=\"75%\"></colgroup>
			<tr><td></td><td>Code</td><td>Appellation</td></tr>
			".$output."
			</table>
			</div>
			<input type=\"hidden\" name=\"step\" value=\"".$this->step_next."\" />
			<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][$index]."\" ".$onclick."/></p>
			</form>
			</div>";
			}
		}

	/**
	 * Traitement de l'étape "classes"
	 * Possible si une classe est dans l'état importable "I"
	 * A lieu lors de l'envoi du formulaire
	 */
	public function classes_process()
		{
		$this->getCdt("classes");
		$this->loadXml("classes");
		global $database_conn_cahier_de_texte;
		global $conn_cahier_de_texte;
		//Vérification de l'intégrité de l'ensemble des données avant de les traiter
		if(!$this->checkForm("classes")) return true;
		
		//Traitement des données envoyées
		foreach($this->classes["code"] as $i=>$code)
			{
			$id_xml = $this->classes["id"][$i]; //utile uniquement pour les correspondances lors de l'importation des emplois du temps
			if($this->classes["etat"][$i]!="I") continue; //donc classe déjà présente identifiée à la lecture du XML
			elseif(isset($_POST["cb_".$code])) //importation demandée
				{
				$nom = $this->getPost("nom_".$code);
				$nom_lower = strtolower($nom);
				
				if($this->emp_sts && array_key_exists($id_xml,$_SESSION[$this->sessname]["classes"])) //correspondance déjà identifiée à la lecture du xml
					{
					$id_cdt = $_SESSION[$this->sessname]["classes"][$id_xml];
					if(in_array($id_cdt,$this->cdt_classes["id"]))
						{
						$etat = "P"; //déjà présent donc plus importable
						$key = array_search($id_cdt,$this->cdt_classes["id"]);
						$nom = $this->cdt_classes["nom"][$key];
						$this->classes["pwd"][$i] = $this->checkPwd("classes",$key);
						}
					else $this->death("La classe dont l&rsquo;identifiant est <i>".$id_cdt."</i> dans le cdt n&rsquo;a pas &eacute;t&eacute; retrouv&eacute;e."); 
					}
				elseif(in_array($nom_lower,$this->cdt_classes["nom"])) //éviter de créer une deuxième classe identique
					{
					$etat = "P"; //déjà présente donc plus importable
					$key = array_search($nom_lower,$this->cdt_classes["nom"]);
					$ref_id = $this->cdt_classes["id"][$key];
					$this->classes["pwd"][$i] = $this->checkPwd("classes",$key);

					//on insère le code disponible s'il n'est pas présent
					$code_cdt = $this->cdt_classes["code"][$key];
					if(empty($code_cdt)) $this->setCode("classes",$key,$code);
					
					//correspondances pour les emplois du temps
					if($this->emp_sts) $_SESSION[$this->sessname]["classes"][$id_xml] = $ref_id;
					}
				else
					{
					if(isset($_POST["pwd_".$code]) && preg_match("/[0-9a-z]+/i",$_POST["pwd_".$code])) //demande de protection par mot de passe
						{
						$pwd = $this->getPost("pwd_".$code);
						$this->classes["pwd"][$i] = $pwd;
						//$pwd = md5($pwd);
						$pwd = password_hash($pwd, PASSWORD_DEFAULT);
						$query = "
						INSERT INTO `cdt_classe` (`code_classe`,`nom_classe`,`passe_classe`) 
						VALUES ('".$code."','".mysqli_real_escape_string($conn_cahier_de_texte, remplace_slash($nom))."','".mysqli_real_escape_string($conn_cahier_de_texte, $pwd)."');";
						}
					else
						{
						$pwd = "";
						$this->classes["pwd"][$i] = "non prot&eacute;g&eacute;";
						$query = "
						INSERT INTO `cdt_classe` (`code_classe`,`nom_classe`) 
						VALUES ('".$code."','".mysqli_real_escape_string($conn_cahier_de_texte, remplace_slash($nom))."');";
						}
					$this->query($query);
					$etat = "R"; //importation réussie
					$new_id = mysqli_insert_id($conn_cahier_de_texte);
					if($this->emp_sts) $_SESSION[$this->sessname]["classes"][$id_xml] = $new_id; //correspondances pour les emplois du temps

					//mise à jour
					$a = count($this->cdt_classes["id"]);
					while(isset($this->cdt_classes["id"][$a])) $a++; //on ne sait jamais...
					$this->cdt_classes["id"][$a] = $new_id;
					$this->cdt_classes["code"][$a] = $code;
					$this->cdt_classes["nom"][$a] = $nom_lower;
					$this->cdt_classes["pwd"][$a] = $pwd;
					}
				
				$this->classes["nom"][$i] = $nom; //nom à jour
				}
			elseif($this->emp_sts && isset($_POST["ref_".$code])) //si pas d'importation, correspondance obligatoire avec l'existant destinée à l'import des emplois du temps
				{
				$id_cdt = intval($_POST["ref_".$code]);
				$_SESSION[$this->sessname]["classes"][$id_xml] = $id_cdt;
				if($id_cdt==0) $etat = "N"; //demande de non importation
				elseif(in_array($id_cdt,$this->cdt_classes["id"]))
					{
					$key = array_search($id_cdt,$this->cdt_classes["id"]);
					$this->classes["nom"][$i] = $this->cdt_classes["nom"][$key]; //nom à jour
					$this->classes["pwd"][$i] = $this->checkPwd("classes",$key);
					$etat = "P"; //référencé donc plus importable
					}
				else $this->death("La classe dont l&rsquo;identifiant est <i>".$id_cdt."</i> dans le cdt n&rsquo;a pas &eacute;t&eacute; retrouv&eacute;e.");
				}
			else $etat = "N"; //on n'arrive en principe jamais là si emp_sts
			$this->classes["etat"][$i] = $etat;
			}
		}

	/**
	 * Affichage de l'étape "classes"
	 * Etape suivante possible uniquement si plus aucune classe n'est dans l'état importable "I"
	 * Si emp_sts : liste proposée pour correspondance avec l'existant
	 */
	public function classes_display()
		{
		if(empty($this->classes)) $this->death("Aucune classe n&rsquo;a &eacute;t&eacute; r&eacute;cup&eacute;r&eacute;e."); 
		else
			{
			$output = "";
			$c = 0; //couleur
			$select = "";
			$changeInput = "";

			if(in_array("I",$this->classes["etat"])) //il y a des données à valider
				{
				$this->step_next = "classes"; //on reste dans l'étape d'importation des classes
				$onclick = "onclick=\"return checkSaisie(this.form);\"";
				if($this->emp_sts) $changeInput = "changeInput(this);";
				$isReady = false;
				$index = 1;
				$etape = "(s&eacute;lection)";
				}
			else
				{
				$onclick = "";
				$isReady = true;
				$index = 0;
				$etape = "(r&eacute;sultat)";
				}

			if($this->useEnvole)
				{
				$envole_message = "
				<div class=\"commentaire\">
				<p style=\"text-align:center; font-weight:bold;\">Commentaire EnVOLE</p>
				Tout <u>code classe soulign&eacute;</u> correspond &agrave; une classe retrouv&eacute;e dans l&rsquo;annuaire du Scribe.<br/>
				Si jamais une correspondance ne se fait pas, il faudra alors mettre comme nom de classe celui existant dans l&rsquo;annuaire.
				</div>";
				}
			else $envole_message = "";

			$pwd_format = "
			<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
			<div id=\"div_import\">
			Protéger l'accès par mot de passe ?
			&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"my_pwd_motif\" value=\"".(empty($this->pwd_motif) ? $this->motif_code : $this->pwd_motif)."\"/>
			&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Actualiser\"/>
			&nbsp;&nbsp;&nbsp;(<a href=\"#\" onclick=\"alert('Cr&eacute;ation group&eacute;e de mots de passe.\\nLe mot clé ".$this->motif_code." sera remplac&eacute; par le code de la classe.');\">aide</a>)
			<input type=\"hidden\" name=\"step\" value=\"".$this->step."\"/>
			</div>
			</form>";

			if($this->emp_sts)
				{
				$this->checkSession(); //provoque un affichage bilan des données enregistrées
				
				$precision = "<li>Pour les autres, les associer &agrave; des classes existantes ou laisser \"Ne rien faire\" pour ne pas en tenir compte</li>";
				
				//liste des classes existantes
				$select = "<select name=\"ref_::CODE::\" ::CLASS:: onchange=\"this.style.border='none';\">";
				$option_x = "<option selected=\"selected\" value=\"0\">Ne rien faire</option>";
				$options = "";
				foreach($this->cdt_classes["nom"] as $n=>$nom)
					{
					$id_cdt = $this->cdt_classes["id"][$n];
					$code_cdt = $this->cdt_classes["code"][$n];
					if(!empty($id_cdt)) $options .= "<option value=\"".$id_cdt."\">".$nom." ".(empty($code_cdt) ? "" : "(".$code_cdt.")")."</option>";
					}
				$options = empty($options) ? "<optgroup label=\"Aucune classe disponible\"></optgroup>" : "<optgroup label=\"Liste des classes existantes\">".$options."</optgroup>";
				}
			else $precision = "";

			foreach($this->classes["code"] as $i=>$code)
				{
				$etat = $this->classes["etat"][$i];
				switch($etat)
					{
					case "I" : //possibilité d'importer la classe
					$style = "style".$etat;
					$checkbox = "<input type=\"checkbox\" name=\"cb_".$code."\" onclick=\"selectOne(this); ".$changeInput."\" checked=\"checked\"/>";
					$text_pwd = $this->getPwd($i);
					$text_pwd = "<input type=\"text\" style=\"text-align:center; border:none; width:95%; cursor:pointer;\" class=\"color".($c%2)." ".$style."\" name=\"pwd_".$code."\" value=\"".$text_pwd."\"/>";
					$text_code = $code;
					$disabled = "";
					$myselect = "";
					
					if($this->useEnvole && $this->ldapCheckClasse($code)) $text_code = "<u>".$text_code."</u>";
					
					if($this->emp_sts)
						{
						$id_xml = $this->classes["id"][$i];
						$myoptions = $option_x;
						if(array_key_exists($id_xml,$_SESSION[$this->sessname]["classes"])) //pré-association : nom non modifiable + liste vide, juste choix de non prise en compte
							{
							$style = "styleP";
							$disabled = "readonly=\"readonly\"";
							$text_pwd = $this->classes["pwd"][$i];
							}
						else $myoptions .= $options;
						$myselect = str_replace("::CODE::",$code,$select);
						$myselect = str_replace("::CLASS::","class=\"color".($c%2)." ".$style."\"",$myselect);
						$myselect .= $myoptions."</select>";							
						}
					$text_nom = "<input type=\"text\" style=\"text-align:center; border:none; width:95%; cursor:pointer;\" class=\"color".($c%2)." ".$style."\" name=\"nom_".$code."\" value=\"".$this->classes["nom"][$i]."\" ".$disabled."/>";
					$text_nom .= $myselect;
					break;
					
					case "P" : //déjà existante, rien à faire
					case "R" : //venant d'être importée avec succès
					case "N" : //aucune importation demandée
					$style = "style".$etat;
					$checkbox = $etat;
					$text_code = $code;
					if($this->useEnvole && $this->ldapCheckClasse($code)) $text_code = "<u>".$text_code."</u>";
					$text_nom = $this->classes["nom"][$i];
					if(isset($this->classes["pwd"][$i]) && !empty($this->classes["pwd"][$i])) $text_pwd = $this->classes["pwd"][$i];
					else $text_pwd = "--------";
					break;
				
					default :
					continue;
					}
				$output .= "
				<tr class=\"color".($c%2)." ".$style."\">
				<td>".$checkbox."</td>
				<td>".$text_code."</td>
				<td>".$text_nom."</td>
				<td>".$text_pwd."</td>
				</tr>";
				$c++;
				}
			echo "
			<div>
			<h2>&Eacute;tape d&rsquo;importation des classes ".$etape."</h2>
			".$envole_message."
			<div class=\"commentaire\">
			Les classes qui auraient &eacute;t&eacute; pr&eacute;c&eacute;demment import&eacute;es ne seront pas modifi&eacute;es. Si besoin, c&rsquo;est &agrave; faire <a href=\"classe_ajout.php\" target=\"_blank\">ici</a>.<br/>
			D&egrave;s qu&rsquo;un code test&eacute; (ou un nom) est retrouv&eacute; dans le cdt, la classe concern&eacute;e ne peut &ecirc;tre associ&eacute;e qu&rsquo;&agrave; celui-ci.
			<ol>
			<li>Choisir les classes que vous souhaitez importer</li>
			<li>Editer si besoin les noms propos&eacute;s (modifiable ult&eacute;rieurement)</li>
			<li>Ajouter un mot de passe par classe si vous souhaitez que l'acc&egrave;s soit prot&eacute;g&eacute; (modifiable ult&eacute;rieurement)</li>
			".$precision."
			<li>Lancer la proc&eacute;dure</li>
			</ol>
			</div>
			<ul style=\"text-align:left;\">
			<li class=\"styleP\">P : classe pr&eacute;sente dans le cahier de textes</li>
			<li class=\"styleR\">R : cr&eacute;ation r&eacute;ussie de la classe</li>
			<li class=\"styleN\">N : non prise en compte de la classe</li>
			</ul>
			".($isReady ? "" : $pwd_format)."
			<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
			<div style=\"text-align:center;\">
			<p>
			".($isReady ? "<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][$index]."\" ".$onclick."/></p>" : "<input type=\"checkbox\" name=\"cb_all\"  id=\"cb_all\" onclick=\"selectAll();\" checked=\"checked\"/>Tout s&eacute;lectionner")."
			</p>
			<table id=\"tableau\">
			<colgroup><col width=\"5%\"><col width=\"15%\"><col width=\"55%\"><col width=\"25%\"></colgroup>
			<tr><td></td><td>Code</td><td>Appellation</td><td>Mot de passe</td></tr>
			".$output."
			</table>
			</div>
			<input type=\"hidden\" name=\"step\" value=\"".$this->step_next."\"/>
			<input type=\"hidden\" name=\"pwd_motif\" value=\"".$this->pwd_motif."\"/>
			<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][$index]."\" ".$onclick."/></p>
			</form>
			</div>";
			}
		}

	/**
	 * Etape préliminaire à la création des groupes, on s'assure d'avoir bien identifié les groupes natifs (groupe_a, groupe_b, groupe_reduit, classe_entiere)
	 * Possibilité de réassocier ces groupes à d'autres noms de groupes
	 */
	public function pregroupes_process()
		{
		$this->getCdt("groupes");
		
		$this->testCdt2(); //vérifie la présence dans le cdt des codes pour les groupes de base et alimente $this->gr_codes
		$this->step_next = "pregroupes"; //on reste dans la même étape
		
		//vérifions s'il faut faire le traitement
		foreach($this->gr_codes["code"] as $i=>$code)
			{
			$ref = isset($_POST["ref_".$code]) ? intval($_POST["ref_".$code]) : 0;
			if($ref>0) //association demandée
				{
				$id_cdt = $ref;
				if(!in_array($id_cdt,$this->cdt_groupes["id"])) continue; //ne devrait jamais arriver...
				if(in_array($id_cdt,$this->gr_codes["form"])) //on ne peut utiliser un même groupe pour deux codes...
					{
					$this->sendMessage("-- FORMULAIRE INCORRECTEMENT RENSEIGN&Eacute; --<br/>Un m&ecirc;me nom de groupe ne peut &ecirc;tre utilis&eacute; que pour un seul code.<br/>Recommencez l&rsquo;association.");
					return true;
					}
				$this->gr_codes["form"][$i] = $id_cdt;
				}
			}
		foreach($this->gr_codes["code"] as $i=>$code) if($this->gr_codes["form"][$i]===false) return true; //des demandes manquantes donc formulaire non encore envoyé

		//Traitement des données envoyées
		foreach($this->gr_codes["code"] as $i=>$code)
			{
			$id_cdt = $this->gr_codes["form"][$i];
			$key = array_search($id_cdt,$this->cdt_groupes["id"]);
			
			//le code demandé est celui déjà associé, pas de mise à jour nécessaire
			if($code==$this->cdt_groupes["code"][$key]) continue;

			//on procède à l'insertion
			$query = "UPDATE `cdt_groupe` SET `code_groupe`='".$code."' WHERE `ID_groupe`='".$id_cdt."' LIMIT 1;";
			$this->query($query);
			$this->gr_codes["id"][$i] = $id_cdt; //mise à jour
			$this->cdt_groupes["code"][$key] = $code; //mise à jour
			}
		
		//on refait le test pour voir si on peut passer à l'étape suivante
		if($this->testCdt2())
			{
			$this->sendMessage("Les associations sont correctement effectu&eacute;es.");
			$this->step_next = "groupes";
			}
		}
	
	/**
	 * Affichage de l'étape préliminaire "pregroupes"
	 */
	public function pregroupes_display()
		{
		if(empty($this->cdt_groupes)) $this->death("Aucun groupe du cdt n&rsquo;a &eacute;t&eacute; r&eacute;cup&eacute;r&eacute;. <a> href=\"groupe_ajout.php\">En existe-t-il ?</a>"); 
		else
			{
			$output = "";
			$c = 0; //couleur
			$index = ($this->step_next=="groupes") ? 0 : 1;
			
			foreach($this->gr_codes["code"] as $i=>$code)
				{
				$id = $this->gr_codes["id"][$i];
				$regexp = $this->gr_codes["regexp"][$i];
				if(in_array($id,$this->cdt_groupes["id"])) $etat = "P"; //association déjà présente
				else $etat = "I"; //association à faire

				//liste des groupes existants
				$select = "<select name=\"ref_".$code."\" style=\"display:block;\" class=\"color".($c%2)." style".$etat."\">";
				foreach($this->cdt_groupes["id"] as $n=>$id_cdt)
					{
					$nom = $this->cdt_groupes["nom"][$n];
					$search = $this->codeForm($nom);
					if($etat=="P") $test = ($id_cdt==$id); //recherche de l'association connue
					elseif($etat=="I") $test = preg_match("/^".$regexp."$/i",$search); //recherche d'un nom de groupe potentiel
					else $test = false;
					$select .= "<option value=\"".$id_cdt."\" ".($test ? "selected=\"selected\"" : "").">".$nom."</option>";
					}
				$select .= "</select>";
					
				$output .= "
				<tr class=\"color".($c%2)." style".$etat."\">
				<td>".$code."</td>
				<td>".$select."</td>
				</tr>";
				$c++;
				}
				
			echo "
			<div>
			<h2>V&eacute;rification des groupes natifs du Cahier de textes (".($this->step_next=="groupes" ? "r&eacute;sultat" : "association").")</h2>
			<div class=\"commentaire\">
			Le cahier de textes utilise 4 groupes principaux, souvent suffisants, identifi&eacute;s par des codes pr&eacute;cis.<br/>
			Avant de traiter les groupes du fichier, v&eacute;rifiez que chaque code ci-dessous est bien associ&eacute; &agrave; un groupe connu.<br/>
			Il n&rsquo;est pas possible d&rsquo;utiliser le m&ecirc;me nom de groupe pour deux codes diff&eacute;rents.<br/>
			</div>
			<ul style=\"text-align:left;\">
			<li class=\"styleP\">association pr&eacute;sente dans le cahier de textes</li>
			<li class=\"styleI\">association inexistante</li>
			</ul>
			<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
			<div style=\"text-align:center;\">
			<table id=\"tableau\">
			<colgroup><col width=\"40%\"><col width=\"60%\"></colgroup>
			<tr><td>Codes &agrave; configurer</td><td>Noms connus dans le cahier de textes</td></tr>
			".$output."
			</table>
			</div>
			<input type=\"hidden\" name=\"step\" value=\"".$this->step_next."\" />
			<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][$index]."\"/></p>
			</form>
			</div>";
			}	
		}

	/**
	 * Traitement de l'étape "groupes" uniquement si emp_sts
	 * Possible si un groupe est dans l'état importable "I"
	 * A lieu lors de l'envoi du formulaire
	 */
	public function groupes_process()
		{
		$this->getCdt("groupes");
		$this->loadXml("groupes");

		if(!$this->emp_sts) $this->death("Traitement des groupes impossible sans le fichier \"emp_sts_".$this->rne."_".$this->year.".xml\"."); 

		//Vérification de l'intégrité de l'ensemble des données avant de les traiter
		if(!$this->checkForm("groupes")) return true;
		
		//Traitement des données envoyées
		foreach($this->groupes["code"] as $i=>$code)
			{
			$id_xml = $this->groupes["id"][$i]; //utile uniquement pour les correspondances lors de l'importation des emplois du temps
			if($this->groupes["etat"][$i]!="I") continue; //donc groupe déjà présent (ou en erreur) identifié à la lecture du XML
			elseif(isset($_POST["cb_".$code])) //importation demandée
				{
				$nom = $this->getPost("nom_".$code);
				$nom_lower = strtolower($nom);

				if(array_key_exists($id_xml,$_SESSION[$this->sessname]["groupes"])) //correspondance déjà identifiée à la lecture du xml
					{
					$id_cdt = $_SESSION[$this->sessname]["groupes"][$id_xml];
					if(in_array($id_cdt,$this->cdt_groupes["id"]))
						{
						$etat = "P"; //déjà présent donc plus importable
						$key = array_search($id_cdt,$this->cdt_groupes["id"]);
						$nom = $this->cdt_groupes["nom"][$key];
						}
					else $this->death("Le groupe dont l&rsquo;identifiant est <i>".$id_cdt."</i> dans le cdt n&rsquo;a pas &eacute;t&eacute; retrouv&eacute;."); 
					}
				elseif(in_array($nom_lower,$this->cdt_groupes["nom"])) //éviter de créer un deuxième groupe identique
					{
					$etat = "P"; //déjà présente donc plus importable
					$key = array_search($nom_lower,$this->cdt_groupes["nom"]);
					$ref_id = $this->cdt_groupes["id"][$key];

					//on insère le code disponible s'il n'est pas présent
					$code_cdt = $this->cdt_groupes["code"][$key];
					if(empty($code_cdt)) $this->setCode("groupes",$key,$code);
					
					//correspondances pour les emplois du temps
					$_SESSION[$this->sessname]["groupes"][$id_xml] = $ref_id;
					}
				else
					{
					$query = "
					INSERT INTO `cdt_groupe` (`code_groupe`,`groupe`) 
					VALUES ('".mysqli_real_escape_string($conn_cahier_de_texte, $code)."','".mysqli_real_escape_string($conn_cahier_de_texte, remplace_slash($nom))."');";
					$this->query($query);
					$etat = "R"; //importation réussie
					$new_id = mysqli_insert_id($conn_cahier_de_texte);
					$_SESSION[$this->sessname]["groupes"][$id_xml] = $new_id; //correspondances pour les emplois du temps
					
					//mise à jour
					$a = count($this->cdt_groupes["nom"]);
					while(isset($this->cdt_groupes["nom"][$a])) $a++; //on ne sait jamais...
					$this->cdt_groupes["id"][$a] = $new_id;
					$this->cdt_groupes["code"][$a] = $code;
					$this->cdt_groupes["nom"][$a] = $nom_lower;
					}
				
				$this->groupes["nom"][$i] = $nom; //nom à jour
				}
			elseif(isset($_POST["ref_".$code])) //si pas d'importation, correspondance obligatoire avec l'existant destinée à l'import des emplois du temps
				{
				$id_cdt = intval($_POST["ref_".$code]);
				$_SESSION[$this->sessname]["groupes"][$id_xml] = $id_cdt;
				if($id_cdt==0) $etat = "N"; //demande de non importation
				elseif(in_array($id_cdt,$this->cdt_groupes["id"]))
					{
					$key = array_search($id_cdt,$this->cdt_groupes["id"]);
					$this->groupes["nom"][$i] = $this->cdt_groupes["nom"][$key]; //nom à jour
					$etat = "P"; //référencé donc plus importable
					}
				else $this->death("Le groupe dont l&rsquo;identifiant est <i>".$id_cdt."</i> dans le cdt n&rsquo;a pas &eacute;t&eacute; retrouv&eacute;e."); 
				}
			else $etat = "N"; //on n'arrive en principe jamais là
			$this->groupes["etat"][$i] = $etat;
			}
		}

	/**
	 * Affichage de l'étape "groupes" uniquement si emp_sts
	 * Etape suivante possible uniquement si plus aucun groupe n'est dans l'état importable "I"
	 */
	public function groupes_display()
		{
		if(empty($this->groupes)) $this->death("Aucun groupe n&rsquo;a &eacute;t&eacute; r&eacute;cup&eacute;r&eacute;e."); 
		elseif(!$this->emp_sts) $this->death("Traitement des groupes impossible sans le fichier \"emp_sts_".$this->rne."_".$this->year.".xml\"."); 
		else
			{
			$output = "";
			$c = 0; //couleur
			$select = "";
			$precision = "<li>Pour les autres, les associer &agrave; des groupes existants ou laisser \"Ne rien faire\" pour ne pas en tenir compte</li>";
			$javascript = "";

			if(in_array("I",$this->groupes["etat"])) //il y a des données à valider
				{
				$this->step_next = "groupes"; //on reste dans l'étape d'importation des groupes
				$onclick = "onclick=\"return checkSaisie(this.form);\"";
				$changeInput = "changeInput(this);";
				$isReady = false;
				$index = 1;
				$etape = "(s&eacute;lection)";
				}
			else
				{
				$onclick = "";
				$changeInput = "";
				$isReady = true;
				$index = 0;
				$etape = "(r&eacute;sultat)";
				}

			$this->checkSession(); //provoque un affichage bilan des données enregistrées

			//liste des groupes existants
			$select = "<select name=\"ref_::CODE::\" ::CLASS:: onchange=\"this.style.border='none';\">";
			$option_x = "<option selected=\"selected\" value=\"0\">Ne rien faire</option>";
			$options = "";
			$option_r = "";
			$option_a = "";
			$option_b = "";
			foreach($this->cdt_groupes["id"] as $n=>$id)
				{
				$nom = $this->cdt_groupes["nom"][$n];
				$code = $this->cdt_groupes["code"][$n];
				
				switch($code)
					{
					case $this->gr_codes["code"][3] : //groupe_reduit
					$option_r = "<option ::GR_R:: value=\"".$id."\">".$nom."</option>";
					break;
					
					case $this->gr_codes["code"][1] : //groupe_a
					$option_a = "<option ::GR_A:: value=\"".$id."\">".$nom."</option>";
					break;

					case $this->gr_codes["code"][2] : //groupe_b
					$option_b = "<option ::GR_B:: value=\"".$id."\">".$nom."</option>";
					break;

					default :
					$options .= "<option value=\"".$id."\">".$nom."</option>";
					break;
					}
				}
			
			foreach($this->groupes["code"] as $i=>$code) //code non affiché pour les groupes car non parlant
				{
				$etat = $this->groupes["etat"][$i];
				switch($etat)
					{
					case "I" : //possibilité d'importer le groupe
					$style = "style".$etat;
					$checkbox = "<input type=\"checkbox\" name=\"cb_".$code."\" id=\"cb_".$code."\" onclick=\"selectOne(this); ".$changeInput."\" checked=\"checked\"/>";
					$selected = "selected = \"selected\"";
					$selected_r = "";
					$selected_a = "";
					$selected_b = "";
					$disabled = "";
					$gr_type = $this->getTypeGroupe($code);
					${"selected_".$gr_type} = $selected;
					
					//individualisation de la liste
					$id_xml = $this->groupes["id"][$i];
					if(array_key_exists($id_xml,$_SESSION[$this->sessname]["groupes"])) //pré-association : nom non modifiable + liste vide, juste choix de non prise en compte
						{
						$style = "styleP";
						$disabled = "readonly=\"readonly\"";
						$javascript .= "document.getElementById('cb_".$code."').click();";
						$myoptions = "";
						}
					else
						{
						$myoptions = str_replace("::GR_R::",$selected_r,$option_r).str_replace("::GR_A::",$selected_a,$option_a).str_replace("::GR_B::",$selected_b,$option_b);
						$myoptions .= $options;
						}
					$myoptions = empty($myoptions) ? "" : "<optgroup label=\"Liste des groupes existants\">".$myoptions."</optgroup>";
					$myoptions = $option_x.$myoptions;
					$myselect = str_replace("::CODE::",$code,$select);
					$myselect = str_replace("::CLASS::","class=\"color".($c%2)." ".$style."\"",$myselect);
					$myselect .= $myoptions."</select>";
						
					$text_nom = "<input type=\"text\" style=\"text-align:center; border:none; width:95%; cursor:pointer;\" class=\"color".($c%2)." ".$style."\" name=\"nom_".$code."\" value=\"".$this->groupes["nom"][$i]."\" ".$disabled."/>";
					$text_nom .= $myselect;
					break;
					
					case "P" : //déjà existant, rien à faire
					case "R" : //venant d'être importé avec succès
					case "N" : //aucune importation demandée
					case "E" : //erreur pour identifier la classe concernée ou au moins une matière
					$style = "style".$etat;
					$checkbox = $etat;
					$text_nom = $this->groupes["nom"][$i];
					break;
				
					default :
					continue;
					}
				$output .= "
				<tr class=\"color".($c%2)." ".$style."\">
				<td>".$checkbox."</td>
				<td>".$text_nom."</td>
				<td>".$this->groupes["indication"][$i]."</td>
				</tr>";
				$c++;
				}
			echo "
			<div>
			<h2>&Eacute;tape d&rsquo;importation des groupes ".$etape."</h2>
			<div class=\"commentaire\">
			Les groupes qui auraient &eacute;t&eacute; pr&eacute;c&eacute;demment import&eacute;s ne seront pas modifi&eacute;s. Si besoin, c&rsquo;est &agrave; faire <a href=\"groupe_ajout.php\" target=\"_blank\">ici</a>.<br/>
			D&egrave;s qu&rsquo;un code test&eacute; (ou un nom) est retrouv&eacute; dans le cdt, le groupe concern&eacute; ne peut &ecirc;tre associ&eacute; qu&rsquo;&agrave; celui-ci.<br/>
			Un groupe ne peut &ecirc;tre pris en compte que si la classe et les mati&egrave;res concern&eacute;es le sont d&eacute;j&agrave;.<br/>
			Il vaut mieux se contenter des groupes par d&eacute;faut, souvent suffisants pour identifier les &eacute;l&egrave;ves concern&eacute;s.
			<ol>
			<li>Choisir les groupes que vous souhaitez tout de m&ecirc;me importer</li>
			<li>Editer si besoin les noms propos&eacute;s (modifiable ult&eacute;rieurement)</li>
			".$precision."
			<li>Lancer la proc&eacute;dure</li>
			</ol>
			</div>
			<ul style=\"text-align:left;\">
			<li class=\"styleP\">P : groupe pr&eacute;sent dans le cahier de textes</li>
			<li class=\"styleR\">R : cr&eacute;ation r&eacute;ussie du groupe</li>
			<li class=\"styleN\">N : non prise en compte du groupe<br/><span style=\"font-weight:normal;\">(automatique si une classe ou mati&egrave;re concern&eacute;e n&rsquo;est pas &agrave; traiter)</span></li>
			<li class=\"styleE\">E : erreur dans le traitement du groupe<br/><span style=\"font-weight:normal;\">(classe ou mati&egrave;re concern&eacute;e non retrouv&eacute;e)</span></li>
			</ul>
			<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
			<div style=\"text-align:center;\">
			<p>
			".($isReady ? "<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][$index]."\" ".$onclick."/></p>" : "<input type=\"checkbox\" name=\"cb_all\" id=\"cb_all\" onclick=\"selectAll();\"/>Tout s&eacute;lectionner")."
			</p>
			<table id=\"tableau\">
			<colgroup><col width=\"5%\"><col width=\"55%\"><col width=\"40%\"></colgroup>
			<tr><td></td><td>Appellation</td><td>Indication (<i>mati&egrave;res_classe</i>)</td></tr>
			".$output."
			</table>
			</div>
			<input type=\"hidden\" name=\"step\" value=\"".$this->step_next."\" />
			<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][$index]."\" ".$onclick."/></p>
			</form>
			</div>
			<script type=\"text/javascript\">selectAll();".$javascript."</script>";
			}
		}

	/**
	 * Traitement de l'étape "gic" (regroupements) uniquement si emp_sts
	 * Possible si un regroupement est dans l'état importable "I"
	 * A lieu lors de l'envoi du formulaire
	 */
	public function gic_process()
		{
		$this->getCdt("gic");
		$this->loadXml("gic");

		if(!$this->emp_sts) $this->death("Traitement des regroupements impossible sans le fichier \"emp_sts_".$this->rne."_".$this->year.".xml\"."); 
		
		//Vérification de l'intégrité de l'ensemble des données avant de les traiter
		if(!$this->checkForm("gic")) return true;
		
		//Traitement des données envoyées
		foreach($this->gic["code"] as $i=>$code)
			{
			$id_xml = $this->gic["id"][$i]; //utile uniquement pour les correspondances lors de l'importation 
			if($this->gic["etat"][$i]!="I") continue; //donc regroupement déjà présent (ou en erreur) identifié à la lecture du XML
			elseif(isset($_POST["cb_".$code])) //importation demandée
				{
				$nom = $this->getPost("nom_".$code);
				$id_xml_prof = $this->gic["prof"][$i];
				$nom_lower = strtolower($nom);
				
				if(array_key_exists($id_xml_prof,$_SESSION[$this->sessname]["profs"])) //toujours le cas à priori (id déjà présent dans la construction du code utilisé)
					{
					$insert_gic = true; //insertion par défaut, changement d'état si un regroupement est considéré comme déjà présent dans le cdt
					$id_cdt_prof = $_SESSION[$this->sessname]["profs"][$id_xml_prof];
					
					//éviter de créer un deuxième regroupement identique
					$ref_key = $this->checkGic($i);

					if(array_key_exists($id_xml,$_SESSION[$this->sessname]["gic"])) //correspondance déjà identifiée à la lecture du xml
						{
						$id_cdt = $_SESSION[$this->sessname]["gic"][$id_xml];
						if(in_array($id_cdt,$this->cdt_gic["id"]))
							{
							$etat = "P"; //déjà présent donc plus importable
							$key = array_search($id_cdt,$this->cdt_gic["id"]);
							$nom = $this->cdt_gic["nom"][$key];
							}
						else $this->death("Le regroupement dont l&rsquo;identifiant est <i>".$id_cdt."</i> dans le cdt n&rsquo;a pas &eacute;t&eacute; retrouv&eacute;."); 
						}
					elseif($ref_key) //même nom, même utilisateur et constitué des mêmes classes
						{
						$etat = "P"; //déjà présente donc plus importable
						$ref_id = $this->cdt_gic["id"][$ref_key];

						//on insère le code disponible s'il n'est pas présent
						$code_cdt = $this->cdt_gic["code"][$ref_key];
						if(empty($code_cdt)) $this->setCode("gic",$ref_key,$code);
						
						//correspondances pour les emplois du temps
						$_SESSION[$this->sessname]["gic"][$id_xml] = $ref_id;
						}
					else
						{
						$query = "
						INSERT INTO `cdt_groupe_interclasses` (`prof_ID`,`nom_gic`,`code_gic`) 
						VALUES ('".$id_cdt_prof."','".mysqli_real_escape_string($conn_cahier_de_texte, remplace_slash($nom))."','".$code."');";
						$this->query($query);
						$etat = "R"; //importation réussie
						$new_id = mysqli_insert_id($conn_cahier_de_texte);
						$_SESSION[$this->sessname]["gic"][$id_xml] = $new_id; //correspondances pour les emplois du temps

						//mise à jour
						$a = count($this->cdt_gic["id"]);
						while(isset($this->cdt_gic["id"][$a])) $a++; //on ne sait jamais...
						$this->cdt_gic["id"][$a] = $new_id;
						$this->cdt_gic["code"][$a] = $code;
						$this->cdt_gic["nom"][$a] = $nom_lower;
						$this->cdt_gic["prof"][$a] = $id_cdt_prof;
					
						//on insère les classes concernées
						//si pas de classe trouvée, ce n'est pas bloquant car l'emploi du temps sera inséré pour le gic donc à l'enseignant de remettre les bonnes classes dedans par la suite
						$this->checkGicClasses($i,$a,$new_id);
						}
					
					$this->gic["nom"][$i] = $nom; //nom à jour
					}
				else $etat = "E"; //regroupement ne pouvant être pris en compte si l'enseignant associé n'est pas retrouvé
				}
			elseif(isset($_POST["ref_".$code])) //si pas d'importation, correspondance obligatoire avec l'existant destinée à l'import des emplois du temps
				{
				$id_cdt = intval($_POST["ref_".$code]);
				$_SESSION[$this->sessname]["gic"][$id_xml] = $id_cdt;
				if($id_cdt==0) $etat = "N"; //demande de non importation
				elseif(in_array($id_cdt,$this->cdt_gic["id"]))
					{
					$key = array_search($id_cdt,$this->cdt_gic["id"]);
					$this->gic["nom"][$i] = $this->cdt_gic["nom"][$key]; //nom à jour
					$etat = "P"; //référencé donc plus importable
					
					//on insère les classes concernées si elles ne sont pas inscrites dans ce regroupement
					$this->checkGicClasses($i,$key,$id_cdt);				
					}
				else $this->death("Le regroupement dont l&rsquo;identifiant est <i>".$id_cdt."</i> dans le cdt n&rsquo;a pas &eacute;t&eacute; retrouv&eacute;e."); 
				}
			else $etat = "N"; //on n'arrive en principe jamais là
			$this->gic["etat"][$i] = $etat;
			}
		}

	/**
	 * Affichage de l'étape "gic" (regroupements) uniquement si emp_sts
	 * Etape suivante possible uniquement si plus aucun regroupement n'est dans l'état importable "I"
	 */
	public function gic_display()
		{
		if(empty($this->gic)) $this->death("Aucun regroupement n&rsquo;a &eacute;t&eacute; r&eacute;cup&eacute;r&eacute;."); 
		elseif(!$this->emp_sts) $this->death("Traitement des regroupements impossible sans le fichier \"emp_sts_".$this->rne."_".$this->year.".xml\"."); 
		else
			{
			$output = "";
			$c = 0; //couleur
			$select = "";
			$precision = "<li>Pour les autres, les associer &agrave; des regroupements existants ou laisser \"Ne rien faire\" pour ne pas en tenir compte</li>";
			
			if(in_array("I",$this->gic["etat"])) //il y a des données à valider
				{
				$this->step_next = "gic"; //on reste dans l'étape d'importation des regroupements
				$onclick = "onclick=\"return checkSaisie(this.form);\"";
				$changeInput = "changeInput(this);";
				$isReady = false;
				$index = 1;
				$etape = "(s&eacute;lection)";
				}
			else
				{
				$onclick = "";
				$changeInput = "";
				$isReady = true;
				$index = 0;
				$etape = "(r&eacute;sultat)";
				}

			$this->checkSession(); //provoque un affichage bilan des données enregistrées
			
			$select = "<select name=\"ref_::CODE::\" ::CLASS:: onchange=\"this.style.border='none';\">";
			$option_x = "<option selected=\"selected\" value=\"0\">Ne rien faire</option>";
			
			foreach($this->gic["code"] as $i=>$code)
				{
				$etat = $this->gic["etat"][$i];
				$id_xml_prof = $this->gic["prof"][$i];
				$key_prof = array_search($id_xml_prof,$this->profs["id"]);
				if($key_prof!==false) $text_prof = $this->profs["login"][$key_prof];
				else $text_prof = "??????";
				
				switch($etat)
					{
					case "I" : //possibilité d'importer le regroupement
					$style = "style".$etat;
					$checkbox = "<input type=\"checkbox\" name=\"cb_".$code."\" onclick=\"selectOne(this); ".$changeInput."\" checked=\"checked\"/>";

					//liste des regroupements existants à construire en fonction de l'enseignant concerné, donc à refaire à chaque itération
					$myoptions = "";
					$id_xml = $this->gic["id"][$i];
					if(array_key_exists($id_xml,$_SESSION[$this->sessname]["gic"])) //pré-association : nom non modifiable + liste vide, juste choix de non prise en compte
						{
						$style = "styleP";
						$disabled = "readonly=\"readonly\"";
						}
					else
						{
						if(array_key_exists($id_xml_prof,$_SESSION[$this->sessname]["profs"]))
							{
							$id_cdt_prof = $_SESSION[$this->sessname]["profs"][$id_xml_prof];
							foreach($this->cdt_gic["prof"] as $n=>$prof)
								{
								if($prof!=$id_cdt_prof) continue;
								$id = $this->cdt_gic["id"][$n];
								$nom = $this->cdt_gic["nom"][$n];
								if(!empty($id)) $myoptions .= "<option value=\"".$id."\">".$nom."</option>";
								}
							}
						}
					$myoptions = empty($myoptions) ? "<optgroup label=\"Aucun regroupement disponible\"></optgroup>" : "<optgroup label=\"Liste des regroupements existants\">".$myoptions."</optgroup>";
					$myoptions = $option_x.$myoptions;
					$myselect = str_replace("::CODE::",$code,$select);
					$myselect = str_replace("::CLASS::","class=\"color".($c%2)." ".$style."\"",$myselect);
					$myselect .= $myoptions."</select>";
					
					$text_nom = "<input type=\"text\" style=\"text-align:center; border:none; width:95%; cursor:pointer;\" class=\"color".($c%2)." ".$style."\" name=\"nom_".$code."\" value=\"".$this->gic["nom"][$i]."\"/>";
					$text_nom .= $myselect;
					break;
					
					case "P" : //déjà existante, rien à faire
					case "R" : //venant d'être importée avec succès
					case "N" : //aucune importation demandée
					case "E" : //erreur pour identifier la matière ou le prof concerné
					$style = "style".$etat;
					$checkbox = $etat;
					$text_nom = $this->gic["nom"][$i];
					break;
				
					default :
					continue;
					}
				$output .= "
				<tr class=\"color".($c%2)." ".$style."\">
				<td>".$checkbox."</td>
				<td>".$text_nom."</td>
				<td>".$text_prof."</td>
				</tr>";
				$c++;
				}
			echo "
			<div>
			<h2>&Eacute;tape d&rsquo;importation des regroupements ".$etape."</h2>
			<div class=\"commentaire\">
			Les regroupements qui auraient &eacute;t&eacute; pr&eacute;c&eacute;demment import&eacute;es ne seront pas modifi&eacute;s.<br/>
			Si besoin, c&rsquo;est &agrave; faire par le propri&eacute;taire du regroupement.<br/>
			Un regroupement ne peut &ecirc;tre pris en compte que si l&rsquo;enseignant et la mati&egrave;re concern&eacute;e le sont d&eacute;j&agrave;.
			<ol>
			<li>Choisir les regroupements que vous souhaitez importer</li>
			<li>Editer si besoin les noms propos&eacute;s par d&eacute;faut sous la forme \"mati&egrave;re_classes\" (modifiable ult&eacute;rieurement)</li>
			".$precision."
			<li>Lancer la proc&eacute;dure</li>
			</ol>
			</div>
			<ul style=\"text-align:left;\">
			<li class=\"styleP\">P : regroupement pr&eacute;sent dans le cahier de textes</li>
			<li class=\"styleR\">R : cr&eacute;ation r&eacute;ussie du regroupement</li>
			<li class=\"styleN\">N : non prise en compte du regroupement<br/><span style=\"font-weight:normal;\">(automatique si une mati&egrave;re ou un enseignant concern&eacute; n&rsquo;est pas &agrave; traiter)</span></li>
			<li class=\"styleE\">E : erreur dans le traitement du regroupement<br/><span style=\"font-weight:normal;\">(mati&egrave;re ou enseignant concern&eacute; non retrouv&eacute;)</span></li>
			</ul>
			<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
			<div style=\"text-align:center;\">
			<p>
			".($isReady ? "<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][$index]."\" ".$onclick."/></p>" : "<input type=\"checkbox\" name=\"cb_all\"  id=\"cb_all\" onclick=\"selectAll();\" checked=\"checked\"/>Tout s&eacute;lectionner")."
			</p>
			<table id=\"tableau\">
			<colgroup><col width=\"5%\"><col width=\"60%\"><col width=\"35%\"></colgroup>
			<tr><td></td><td>Appellation</td><td>Propri&eacute;taire</td></tr>
			".$output."
			</table>
			</div>
			<input type=\"hidden\" name=\"step\" value=\"".$this->step_next."\" />
			<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][$index]."\" ".$onclick."/></p>
			</form>
			</div>";
			}
		}

	/**
	 * Traitement de l'étape "edt_param" uniquement si emp_sts
	 * Simple chargement de données, aucun traitement particulier
	 */
	public function edt_param_process()
		{
		if(!$this->emp_sts) $this->death("Traitement des emplois du temps impossible sans le fichier \"emp_sts_".$this->rne."_".$this->year.".xml\".");

		$this->getCdt("edt");
		$this->loadXml("edt");

		
		//si aucun enseignant n'est à prendre en compte, inutile d'aller plus loin
		$notSave = 0;
		foreach($_SESSION[$this->sessname]["profs"] as $value) if($value==0) $notSave++;
		if($notSave==count($this->profs["id"])) $this->death("Aucun enseignant du fichier n&rsquo;a &eacute;t&eacute; demand&eacute; &agrave; &ecirc;tre pris en compte.<br/>Il est donc inutile de proc&eacute;der &agrave; l&rsquo;importation des emplois du temps.");
		}

	/**
	 * Affichage de l'étape "edt_param" uniquement si emp_sts
	 * Formulaire de choix des paramètres pour l'importation des emplois du temps (étape "edt_import")
	 */
	public function edt_param_display()
		{
		if(empty($this->alternances)) $this->death("Aucune alternance n&rsquo;a &eacute;t&eacute; r&eacute;cup&eacute;r&eacute;e."); 
		elseif(!$this->emp_sts) $this->death("Traitement des emplois du temps impossible sans le fichier \"emp_sts_".$this->rne."_".$this->year.".xml\"."); 
		else
			{
			$output = "";
			$c = 0; //couleur
			$isReady = false;
			$select = "";
			$onclick = "onclick=\"return checkEdt(this.form);\"";
			$precision = "";
			$etape = "(param&eacute;trage)";
			
			$this->checkSession(); //provoque un affichage bilan des données enregistrées

			//liste des alternances possibles
			$select_alternance = "
			<select name=\"periode_::CODE::\" ::CLASS:: onchange=\"this.style.border='none';\" style=\"display:block;\">
			<option ::X:: value=\"X\">Ne pas utiliser</option>
			<option ::H:: value=\"H\">Ann&eacute;e compl&egrave;te</option>
			<option ::A:: value=\"A\">Semaines A</option>
			<option ::B:: value=\"B\">Semaines B</option>
			<option ::1:: value=\"S1\">Semestre 1</option>
			<option ::2:: value=\"S2\">Semestre 2</option>
			</select>
			";
			
			foreach($this->alternances["code"] as $i=>$code)
				{
				$style = "styleP";
				$text_nom = $this->alternances["nom"][$i];
				$liste_semaines = "<select style=\"display:block;\" class=\"color".($c%2)." ".$style."\">";
				foreach($this->alternances["semaines"][$i]["debut"] as $lundi) $liste_semaines .= "<option>".$lundi."</option>";
				$liste_semaines .= "</select>";
				
				$selected = "selected = \"selected\"";
				$selected_X = "";
				$selected_H = "";
				$selected_A = "";
				$selected_B = "";
				$selected_S1 = "";
				$selected_S2 = "";
				${"selected_".$this->alternances["type"][$i]} = $selected;

				$myselect = str_replace("::CODE::",$code,$select_alternance);
				$myselect = str_replace("::CLASS::","class=\"color".($c%2)." ".$style."\"",$myselect);	
				$myselect = str_replace("::X::",$selected_X,$myselect);
				$myselect = str_replace("::H::",$selected_H,$myselect);
				$myselect = str_replace("::A::",$selected_A,$myselect);
				$myselect = str_replace("::B::",$selected_B,$myselect);
				$myselect = str_replace("::1::",$selected_S1,$myselect);
				$myselect = str_replace("::2::",$selected_S2,$myselect);
				
				$output .= "
				<tr class=\"color".($c%2)." ".$style."\">
				<td>".$text_nom."</td>
				<td>".$liste_semaines."</td>
				<td>".$myselect."</td>
				</tr>";
				$c++;		
				}
			echo "
			<div>
			<h2>&Eacute;tape d&rsquo;importation des emplois du temps ".$etape."</h2>
			<div class=\"commentaire\">
			Aucune heure d&rsquo;emplois du temps d&eacute;j&agrave; pr&eacute;sente ne sera supprim&eacute;e. Si besoin, c&rsquo;est &agrave; faire par l&rsquo;enseignant lui-m&ecirc;me.<br/>
			<ol>
			<li>V&eacute;rifier les alternances propos&eacute;es pour l'ann&eacute;e totale, les semaines A et B ainsi que les semestres</li>
			<li>Choisir de d&eacute;sactiver ou non les heures existantes dans le cdt</li>
			".$precision."
			<li>Lancer la proc&eacute;dure</li>
			</ol>
			</div>
			<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
			<div style=\"text-align:left;\">
			<ul>
			
			<li>
			<b>Voici la liste des alternances trouv&eacute;es dans le fichier, corriger si besoin les p&eacute;riodes propos&eacute;es :</b>
			<div style=\"margin:10px 0px;\">
			Chaque type de p&eacute;riode ne peut &ecirc;tre s&eacute;lectionn&eacute; qu&rsquo;une seule fois.<br/>
			Si une alternance ne doit pas &ecirc;tre prise en compte, donner lui le type \"Ne pas utiliser\".<br/>
			Il y a 3 p&eacute;riodes &agrave; renseigner obligatoirement : Ann&eacute;e compl&egrave;te, Semaines A et Semaines B.
			</div>
			<div style=\"text-align:center; margin:10px 0px 20px;\">
			<table id=\"tableau\">
			<colgroup><col width=\"40%\"><col width=\"30%\"><col width=\"30%\"></colgroup>
			<tr><th>Libell&eacute; XML</th><th>Semaines concern&eacute;es<br/>(pour information)</th><th>Type<br/>(&agrave; choisir)</th></tr>
			".$output."
			</table>
			</div>
			</li>
			
			<li>
			<input type=\"checkbox\" name=\"disable\" id=\"disable\" checked=\"checked\"/>&nbsp;<label for=\"disable\" style=\"cursor:pointer;\"><b>Je souhaite d&eacute;sactiver les heures de cours d&eacute;j&agrave; pr&eacute;sentes dans le cdt mais non retrouv&eacute;es</b></label>
			<div style=\"margin:10px 0px 20px;\">
			Dans ce cas, une p&eacute;riode de validit&eacute; avec comme date de fin la date d&rsquo;aujourd'hui sera utilis&eacute;e.<br/>
			Les enseignants concern&eacute;s disposeront toujours de ces heures et pourront donc les r&eacute;activer si besoin.
			</div>		
			</li>

			<li>
			<input type=\"text\" name=\"start_date\" id=\"start_date\" size=\"8\" value=\"".date("d/m/Y")."\"/>&nbsp;<label for=\"start_date\" style=\"cursor:pointer;\"><b>sera la date de d&eacute;but de validit&eacute; des heures ins&eacute;r&eacute;es ou mises &agrave; jour.</b></label>
			<div style=\"margin:10px 0px 20px;\">
			Si une p&eacute;riode (comme le semestre 2) d&eacute;bute &agrave; une date ult&eacute;rieure, la date indiqu&eacute;e ici ne sera pas prise en compte.<br/>
			La date de fin de p&eacute;riode sera toujours la derni&egrave;re semaine de cours indiqu&eacute;e dans la p&eacute;riode concern&eacute;e.
			</div>		
			</li>
							
			</ul>
			</div>
			<input type=\"hidden\" name=\"step\" value=\"".$this->step_next."\" />
			<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][0]."\" ".$onclick."/></p>
			</form>
			</div>";
			}
		}

	/**
	 * Traitement de l'étape "edt_import" uniquement si emp_sts
	 * Réception du formulaire de l'étape "edt_param" et importation des emplois du temps
	 */
	public function edt_import_process()
		{
		if(!$this->emp_sts) $this->death("Traitement des emplois du temps impossible sans le fichier \"emp_sts_".$this->rne."_".$this->year.".xml\".");

		$this->sendMessage("Lecture des donn&eacute;es...");
		$this->getCdt("edt");
		$this->loadXml("edt");
				
		//variables
		$alternances = array(); //seront prises en compte les alternances enregistrées dans ce tableau
		$start_date = $this->getPost("start_date");
	
		//récupération des périodes
		$this->sendMessage("Traitement des alternances...");
		foreach($this->alternances["code"] as $i=>$code) $this->alternances["type"][$i] = "X"; //annulation de la prédétermination
		foreach($this->alternances["code"] as $i=>$code)
			{
			$periode = $this->getPost("periode_".$code);
			if(empty($periode) || !array_key_exists($periode,$this->semaines) || in_array($periode,$this->alternances["type"])) continue; //seule une alternance de chaque type est prise encompte
			$this->alternances["type"][$i] = $periode; 
			}
			
		//obligatoire pour poursuivre
		if(!in_array("H",$this->alternances["type"]) || !in_array("A",$this->alternances["type"]) || !in_array("B",$this->alternances["type"]))
			{
			echo "<h3 class=\"echec\">Probl&egrave;me de lecture du choix des alternances obligatoires<br/>(ann&eacute;e compl&egrave;te, semaines A et semaines B)<br/>-- Veuillez recommencer --</h3>";
			$this->launchStep("edt_param"); //sinon, on redirige vers l'étape de choix des paramètres
			exit();
			}
		
		//classement des semaines A et B
		//besoin de regrouper les données dans un même tableau temporaire afin de numéroter les semaines (champ `num_semaine` de la table `cdt_semaine_ab`)
		$tmp_semaines = array();
		$n = 0;
		$key_A = array_search("A",$this->alternances["type"]);
		$key_B = array_search("B",$this->alternances["type"]);
		
		foreach($this->alternances["semaines"][$key_A]["tri"] as $i=>$code_date)
			{
			$tmp_semaines["semaine"][$n] = "A";
			$tmp_semaines["code"][$n] = $code_date;
			$tmp_semaines["lundi"][$n] = $this->alternances["semaines"][$key_A]["debut"][$i];
			$tmp_semaines["dimanche"][$n] = $this->alternances["semaines"][$key_A]["fin"][$i];
			$n++;
			}
			
		foreach($this->alternances["semaines"][$key_B]["tri"] as $i=>$code_date)
			{
			$tmp_semaines["semaine"][$n] = "B";
			$tmp_semaines["code"][$n] = $code_date;
			$tmp_semaines["lundi"][$n] = $this->alternances["semaines"][$key_B]["debut"][$i];
			$tmp_semaines["dimanche"][$n] = $this->alternances["semaines"][$key_B]["fin"][$i];
			$n++;
			}
			
		array_multisort($tmp_semaines["code"],$tmp_semaines["semaine"],$tmp_semaines["lundi"],$tmp_semaines["dimanche"]);
		//$this->displayArray($tmp_semaines,"semaines");
		
		//enregistrement des semaines A et B
		$this->query("TRUNCATE `cdt_semaine_ab`");
		foreach($tmp_semaines["code"] as $i=>$code_date)
			{
			$query = "
			INSERT INTO `cdt_semaine_ab` (`semaine`,`num_semaine`,`s_code_date`,`date_lundi`,`date_dimanche`) 
			VALUES ('".$tmp_semaines["semaine"][$i]."','".($i+1)."','".$code_date."','".$tmp_semaines["lundi"][$i]."','".$tmp_semaines["dimanche"][$i]."');";
			$this->query($query);
			}
		unset($tmp_semaines);
		
		//les périodes doivent avoir été récupérées avant de charger les cours
		$this->sendMessage("Lecture des emplois du temps...");
		$this->loadEdt();
			
		//désactivation des anciens emplois du temps
		$tmp_edt = array(); //pour retrouver tous les edt qui n'ont ni été mis à jour, ni inséré
		if(isset($_POST["disable"])) $desactivate = true;
		else $desactivate = false;

		//date de début de validité des nouveaux cours à insérer
		if(preg_match("/^([0-9]{2}).{1}([0-9]{2}).{1}([0-9]{4})$/",$start_date,$matches)) $start_timestamp = mktime(0,0,0,$matches[2],$matches[1],$matches[3]);
		else $start_timestamp = time();

		//insertion des cours
		$nbre = 0;
		$total = count($this->edt);
		foreach($this->edt as $id_cdt_prof=>$jours) //classement par prof
			{
			$this->sendMessage("Traitement edt n°".++$nbre." sur ".$total."...");
			ksort($jours);
			$identite = $this->getCdtData("profs",$id_cdt_prof,"nom");

			foreach($jours as $jour=>$positions) //classement par jour
				{
				$nom_jour = $this->jours[$jour];
				ksort($positions);
					
				foreach($positions as $position=>$cours) //classement par position
					{
					foreach($cours as $i=>$infos) //lecture des cours
						{
						$id_cdt_matiere = $infos[0];
						$id_cdt_classe = $infos[1];
						$id_cdt_gic = $infos[2];
						$nom_groupe = $infos[3];
						$periode = $infos[4];
						$cdt_debut = $infos[5];
						$cdt_fin = $infos[6];
						$cdt_duree = $infos[7];
						$first = $infos[8];
						$last = $infos[9];
						if($first<$start_timestamp)
							{
							$cdt_start = date("Y-m-d",$start_timestamp);
							$this->edt[$id_cdt_prof][$jour][$position][$i][8] = $start_timestamp;
							}
						else $cdt_start = date("Y-m-d",$first);
						$cdt_finish = date("Y-m-d",$last);
						$existence = "du ".date("d/m/Y",$first)." au ".date("d/m/Y",$last);
						
						if( ! (($id_cdt_classe>0 || $id_cdt_gic>0) && $id_cdt_prof>0 && $id_cdt_matiere>0) )
							{
							$this->edt[$id_cdt_prof][$jour][$position][$i][10] = 0; //signaler comme cours non traité car utilisant une donnée demandée à ne pas être prise en compte
							continue;
							}

						$verification = "
						SELECT `ID_emploi` FROM `cdt_emploi_du_temps` 
						WHERE `prof_ID`= '".$id_cdt_prof."' 
						AND `jour_semaine`='".$nom_jour."' 
						AND `semaine`='".$periode."' 
						AND `heure`='".$position."' 
						AND `classe_ID`='".$id_cdt_classe."' 
						AND `gic_ID`='".$id_cdt_gic."' 
						AND `groupe`='".$nom_groupe."' 
						AND `matiere_ID`='".$id_cdt_matiere."' 
						AND `heure_debut`='".$cdt_debut."' 
						AND `heure_fin`='".$cdt_fin."' 
						AND `duree`='".$cdt_duree."' LIMIT 1;";
						$this->query($verification);
	
						if(mysqli_num_rows($this->requete)>0)
							{
							$row = mysqli_fetch_row($this->requete);
							$id_cdt_edt = $row[0];
							$this->edt[$id_cdt_prof][$jour][$position][$i][10] = 1; //signaler présence (donc juste une mise à jour)
							$update = "
							UPDATE `cdt_emploi_du_temps` 
							SET `edt_exist_debut`='".$cdt_start."',`edt_exist_fin`='".$cdt_finish."' 
							WHERE `ID_emploi`='".$id_cdt_edt."' LIMIT 1";
							$this->query($update,false);						
							if($desactivate) $tmp_edt[] = $id_cdt_edt;
							}
						else
							{	
							$nom_groupe=remplace_slash($nom_groupe);				
							$insertion = "
							INSERT INTO `cdt_emploi_du_temps` (`prof_ID`,`jour_semaine`,`semaine`,`heure`,`classe_ID`,`gic_ID`,`groupe`,`matiere_ID`,`heure_debut`,`heure_fin`,`duree`,`edt_exist_debut`,`edt_exist_fin`) 
							VALUES ('".$id_cdt_prof."','".$nom_jour."','".$periode."','".$position."','".$id_cdt_classe."','".$id_cdt_gic."','".$nom_groupe."','".$id_cdt_matiere."','".$cdt_debut."','".$cdt_fin."','".$cdt_duree."','".$cdt_start."','".$cdt_finish."');";
							$this->query($insertion,false);
							$this->edt[$id_cdt_prof][$jour][$position][$i][10] = 2; //signaler insertion
							if($desactivate) $tmp_edt[] = mysqli_insert_id($conn_cahier_de_texte);
							}
						}
					}
				}
			}
		
		//désactivons le reste des emplois du temps
		if($desactivate)
			{
			$this->edt_desactivated = 0;
			$now = date("Y-m-d");
			$select = "SELECT `ID_emploi` FROM `cdt_emploi_du_temps`;";
			$this->query($select);
			while($row=mysqli_fetch_row($this->requete))
				{
				$id_cdt_edt = $row[0];
				if(in_array($id_cdt_edt,$tmp_edt)) continue;
				$update = "
				UPDATE `cdt_emploi_du_temps` 
				SET `edt_exist_fin`='".$now."' 
				WHERE `ID_emploi`='".$id_cdt_edt."' LIMIT 1";
				$this->query($update,false);
				$this->edt_desactivated++;		
				}
			}
		else $this->edt_desactivated = false;

		$this->sendMessage(""); //cache le bloc affichant les messages
		}

	/**
	 * Affichage de l'étape "edt_import" uniquement si emp_sts
	 * Bilan visuel du résultat de l'importation des emplois du temps*
	 * Difficulté : n'afficher ce qui est dans l'état "0" que dans le fichier de log
	 */
	public function edt_import_display()
		{
		if(empty($this->edt)) $this->death("Aucun emploi du temps n&rsquo;a &eacute;t&eacute; r&eacute;cup&eacute;r&eacute;e."); 
		elseif(!$this->emp_sts) $this->death("Traitement des emplois du temps impossible sans le fichier \"emp_sts_".$this->rne."_".$this->year.".xml\"."); 
		else
			{
			$c = 0; //couleur
			$ignorer = "<span class=\"echec\">null</span>";
			if($this->saveImport)
				{
				$this->flux = fopen($this->to_dir.$this->filenames["log"],"w");
				if($this->flux)
				$entete = "
				<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
				<html>
				<head>
				<title>Log de l&rsquo;important des emplois du temps</title>
				<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />
				<style>
				body {background:#CBFECB; font-size:16px;}
				#edt_synthese {background:transparent; margin:0px auto; padding:10px 0px;}
				table.recapitulatif {font-size:0.8em; margin:0px auto 10px; width:90%;}
				h3 {text-align:center; color:#3054BF;}
				h2 {font-weight:bold; color:#990099; text-align:center; margin:10px auto 0px; padding:5px; width:90%;}
				p {text-align:left; margin-left:100px; }
				span.styleP {font-weight:bold; color:#990099;}
				span.echec {color:red; font-style:italic;}
				tr.color0 {background:#FFDDAA;}
				tr.color1 {background:#99DDFF;}
				</style>
				</head>
				<body>
				<h3>R&eacute;sultat de l&rsquo;importation des emplois du temps dans le cahier de textes<br/>Fichier g&eacute;n&eacute;r&eacute; le ".date("d/m/Y à H:i:s")."</h3>";
				fwrite($this->flux,$entete);
				}
			
			echo "</div>";
			$this->setLog
				("
				<div id=\"edt_synthese\">
				");
			echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";

			$profs_null = 0;
			$total = count($this->edt);
			foreach($_SESSION[$this->sessname]["profs"] as $id) if($id==0) $profs_null++;
			if($profs_null==1) $this->setLog("<p>Rappel : <span class=\"styleP\">".$profs_null."</span> utilisateur n&rsquo;a pas &eacute;t&eacute; pris en compte comme demand&eacute; &agrave; l&rsquo;&eacute;tape concern&eacute;e.</p>");
			elseif($profs_null>0) $this->setLog("<p><span class=\"styleP\">".$profs_null."</span> utilisateurs n&rsquo;ont pas &eacute;t&eacute; pris en compte comme demand&eacute; &agrave; l&rsquo;&eacute;tape concern&eacute;e.</p>");
			$this->setLog("<p><span class=\"styleP\">".$total."</span> enseignants ont vu leur emploi du temps g&eacute;n&eacute;r&eacute;.</p>");
			
			$this->setLog("<p><span class=\"styleP\">".$this->edt_compteur."</span> heures de cours lues dans le fichier ont &eacute;t&eacute; trait&eacute;es.</p>");
			
			if($this->edt_desactivated===false) $this->setLog("<p><span class=\"styleP\">Aucune</span> heure du cdt non pr&eacute;sente dans le fichier n&rsquo;a &eacute;t&eacute; d&eacute;sactiv&eacute;e.</p>");
			elseif($this->edt_desactivated===0) $this->setLog("<p><span class=\"styleP\">Aucune</span> heure du cdt n&rsquo;a &eacute;t&eacute; d&eacute;sactiv&eacute;e car elles sont toutes pr&eacute;sentes dans le fichier.</p>");
			else $this->setLog("<p><span class=\"styleP\">".$this->edt_desactivated."</span> heures du cdt ne correspondaient pas &agrave; des heures du fichier et ont donc &eacute;t&eacute; d&eacute;sactiv&eacute;es.</p>");
			
			$this->setLog
				("
				<div style=\"text-align:center; margin:10px 0px 20px;\">
				<table style=\"margin:0px auto 10px; width:615px; text-align:left;\">
				<colgroup><col width=\"15px\"><col width=\"600px\"></colgroup>
				<tr><td style=\"background:#0066ff\">&nbsp;</td><td>D&eacute;j&agrave; pr&eacute;sent : la plage de validit&eacute; a &eacute;t&eacute; mise &agrave; jour</td></tr>
				<tr><td style=\"background:#00cc66\">&nbsp;</td><td>Insertion : nouvelle entr&eacute;e dans le cahier de textes</td></tr>
				");
			$this->setLog("<tr><td style=\"background:#ff0033\">&nbsp;</td><td>Non insertion : cours utilisant une donn&eacute;e dont il ne fallait pas tenir compte (valeur ".$ignorer.")</td></tr>",true);
			$this->setLog("</table>");
			
			ksort($this->edt);
			$nbre = 0;
			$total = count($this->edt);
			foreach($this->edt as $id_cdt_prof=>$jours)
				{
				ksort($jours);
				$identite = $this->getCdtData("profs",$id_cdt_prof,"nom");
				$login = $this->getCdtData("profs",$id_cdt_prof,"login",$id_cdt_prof);
				
				if($this->useEnvole && !$this->ldapCheckLogin($login)) $login = "<span style=\"color:red; cursor:help;\" title=\"Absent de l'annuaire du Scribe !!!\">!!!".$login."!!!</span>";

				$this->setLog
					("
					<h2 class=\"intitule\">Enseignant n°".++$nbre." sur ".$total." : ".$login." ".(empty($identite) ? "" : "(".$identite.")")."</h2>
					<table class=\"recapitulatif\">
					<tr><th>Jour</th><th></th><th>Pos</th><th>D&eacute;but</th><th>Fin</th><th>Mati&egrave;re</th><th>Classe / Regroupement</th><th>Groupe</th><th>Semaine</th><th>Dur&eacute;e</th><th>Plage de validit&eacute;</th></tr>
					");
				
				foreach($jours as $jour=>$positions)
					{
					ksort($positions);
					$jour = $this->jours[$jour];
					//chaque jour ne sera écrit qu'une fois dans le récapitulatif d'un enseignant
					//tout ce qui a été demandé à ne pas être pris en compte n'est affiché que dans le log
					$count_cours = 0;
					$count_cours_log = 0;
					foreach($positions as $cours)
						{
						$count_cours_log += count($cours);
						foreach($cours as $infos) if($infos[10]!=0) $count_cours++;
						}
					
					//echo "<pre>";
					//print_r($positions);
					//echo"</pre>";
					
					foreach($positions as $position=>$cours)
						{
						foreach($cours as $infos)
							{
							$id_cdt_matiere = $infos[0];
							$id_cdt_classe = $infos[1];
							$id_cdt_gic = $infos[2];
							$nom_groupe = $infos[3];
							$periode = $infos[4];
							$cdt_debut = $infos[5];
							$cdt_fin = $infos[6];
							$cdt_duree = $infos[7];
							$first = $infos[8];
							$last = $infos[9];
							$etat = $infos[10];
							switch($etat)
								{
								case 0 : //non insertion
								$style = "#ff0033";
								break;

								case 1 : //déjà présent
								$style = "#0066ff";
								break;

								case 2 : //insertion
								$style = "#00cc66";
								break;

								default :
								$style = "none";
								break;
								}
							$existence = "du ".date("d/m/Y",$first)." au ".date("d/m/Y",$last);
							if($id_cdt_classe>0 || $id_cdt_gic>0)
								{
								$nom_classe = $id_cdt_classe>0 ? $this->getCdtData("classes",$id_cdt_classe,"nom",$id_cdt_classe) : $this->getCdtData("gic",$id_cdt_gic,"nom",$id_cdt_gic);
								if($this->useEnvole && $id_cdt_classe>0) //vérification à ne faire que si classe
									{
									$code_classe = $this->getCdtData("classes",$id_cdt_classe,"code",$id_cdt_classe);
									if(!$this->ldapCheckClasse($code_classe) && !$this->ldapCheckClasse($nom_classe)) $nom_classe = "<span style=\"color:red; cursor:help;\" title=\"Absent de l'annuaire du Scribe !!!\">!!!".$nom_classe."!!!</span>";
									}
								}
							else $nom_classe = $ignorer;
							if($id_cdt_matiere>0) $nom_matiere = $this->getCdtData("matieres",$id_cdt_matiere,"nom",$id_cdt_matiere);
							else $nom_matiere = $ignorer;
							
							$ligne = "
							<td style=\"background:".$style."\">&nbsp;&nbsp;</td>
							<td>".$position."</td>
							<td>".$cdt_debut."</td>
							<td>".$cdt_fin."</td>
							<td>".$nom_matiere."</td>
							<td>".$nom_classe."</td>
							<td>".$nom_groupe."</td>
							<td>".$periode."</td>
							<td>".$cdt_duree."</td>
							<td>".$existence."</td>";
							
							//affichage dans le log
							$this->setLog
								("
								<tr class=\"color".($c%2)."\">
								".($count_cours_log>0 ? "<td rowspan=\"".$count_cours_log."\">".$jour."</td>" : "")."
								".$ligne."
								</tr>
								",true);
							$count_cours_log = 0;
							
							//affichage sur la page (que ce qui a été demandé à être traité)
							if($etat!=0)
								{
								echo "
								<tr class=\"color".($c%2)."\">
								".($count_cours>0 ? "<td rowspan=\"".$count_cours."\">".$jour."</td>" : "")."
								".$ligne."
								</tr>
								";
								$count_cours = 0;
								}
							}
						}
					$c++;
					}
				
				$this->setLog("</table>");		
				}
			
			$this->setLog("</div>");
			echo "
			<input type=\"hidden\" name=\"step\" value=\"".$this->step_next."\" />
			<p style=\"text-align:center;\"><input type=\"submit\" value=\"".$this->step_display[$this->step_next][0]."\"/></p>
			</form>";
			$this->setLog("</div>");
			echo "<div>";
			
			if($this->saveImport && $this->flux)
				{
				fwrite($this->flux,"</body></html>");
				fclose($this->flux);
				}
			}	
		}

	/**
	 * Traitement de la dernière étape "end"
	 */
	public function end_process()
		{
		$this->unlinkXML(); //effacement des fichiers XML
		$this->launchStep("start"); //redirection
		exit();
		}

	/**
	 * Texte à afficher dans le log en plus de l'écran
	 * 
	 * @param string  $text      message à afficher
	 * @param boolean $onlyInLog si pas d'affichage écran souhaité
	 */
	public function setLog($text,$onlyInLog=false)
		{
		if(!$onlyInLog) echo $text;
		if($this->saveImport && $this->flux) fwrite($this->flux,$text);
		}

	/**
	 * Affichage de la mémoire utilisée par le script à un instant t
	 */
	public function echo_memory_usage()
		{
		$mem_usage = memory_get_usage(true);
		if($mem_usage<1024) $mem_usage .= " o";
        elseif($mem_usage<1048576) $mem_usage = round($mem_usage/1024,2)." Ko";
        else $mem_usage = round($mem_usage/1048576,2)." Mo";
		echo "<p>".$mem_usage."</p>";
		}

	/**
	 * Déterminer la position d'un cours dans la journée selon les plages horaires du cahier de textes
	 * 
	 * En fonction de l'heure du début du cours, on cherche la première plage dont l'heure est à plus ou moins une demi-heure du début de celle-ci
	 * Donc 7h50 aura la plage 8h00/9h00 et 11h20 aura la plage 11h00/12h00
	 * 
	 * @param int $debut en minutes
	 * 
	 * @return int position trouvée (1 par défaut)
	 */
	public function getPosition($debut)
		{
		$position = 1;
		if(preg_match("/^[0-9]+$/",$debut))
			{
			foreach($this->cdt_horaires["id"] as $n=>$id)
				{
				$start = intval($this->cdt_horaires["start"][$n]);
				if($debut>=$start-30 && $debut<=$start+30)
					{
					$position = $id;
					break;
					}
				}
			}
		return $position;
		}

	/**
	 * Déterminer l'identifiant cdt d'une donnée à partir de son identifiant dans le fichier XML
	 * La recherche se fait dans la session en fonction des correspondances demandées
	 * 
	 * @param string $type   profs/classes/matieres/groupes/gic
	 * @param string $id_xml identifiant dans le fichier XML
	 * 
	 * @return int identifiant dans la base du cahier de textes
	 */
	public function getCdtId($type,$id_xml)
		{
		if(in_array($type,$this->step_list) && in_array($id_xml,$this->{$type}["id"]) && isset($_SESSION[$this->sessname][$type][$id_xml]) && intval($_SESSION[$this->sessname][$type][$id_xml])>0)
		return intval($_SESSION[$this->sessname][$type][$id_xml]);
		else
		return 0;
		}

	/**
	 * Récupérer une information particulière sur une donnée du cahier de textes d'après son identifiant en base
	 * 
	 * @param string $type    profs/classes/matieres/groupes/gic...
	 * @param string $id_cdt  identifiant dans le cahier de textes
	 * @param string $data    nom du libellé dans le tableau cdt_{type}
	 * @param string $default valeur renvoyée si rien n'est trouvé
	 * 
	 * @return string information demandée
	 */
	public function getCdtData($type,$id_cdt,$data,$default="")
		{
		if(isset($this->{"cdt_".$type}[$data]) && isset($this->{"cdt_".$type}["id"]) && in_array($id_cdt,$this->{"cdt_".$type}["id"])) $key = array_search($id_cdt,$this->{"cdt_".$type}["id"]);
		else $key = false;
		
		return ($key!==false && !empty($this->{"cdt_".$type}[$data][$key])) ? $this->{"cdt_".$type}[$data][$key] : $default;
		}

	/**
	 * Récupérer une information particulière sur une donnée du fichier d'après son identifiant dans celui-ci
	 * 
	 * @param string $type    profs/classes/matieres/groupes/gic...
	 * @param string $id_xml  identifiant dans le cahier de textes
	 * @param string $data    nom du libellé dans le tableau {type}
	 * @param string $default valeur renvoyée si rien n'est trouvé
	 * 
	 * @return string information demandée
	 */
	public function getXmlData($type,$id_xml,$data="nom",$default="")
		{
		if(isset($this->{$type}[$data]) && isset($this->{$type}["id"]) && in_array($id_xml,$this->{$type}["id"])) $key = array_search($id_xml,$this->{$type}["id"]);
		else $key = false;
		
		return ($key!==false && !empty($this->{$type}[$data][$key])) ? $this->{$type}[$data][$key] : $default;
		}

	/**
	 * Reconnaître groupe A ou B
	 * Pour tout groupe susceptible d'être de ce type, on vérifie l'existence du "complémentaire" pour validation
	 * 
	 * @param string $code code du groupe dans le fichier
	 * 
	 * @return string a/b/r
	 */
	public function getTypeGroupe($code)
		{
		$c = array("1"=>"2","2"=>"1","a"=>"b","b"=>"a"); //complémentaires
		$r = array("1"=>"a","2"=>"b","a"=>"a","b"=>"b"); //résultats
		if(preg_match("/(1|2|a|b)$/i",$code,$matches))
			{
			$end = $matches[1];
			$c_end = $c[$end];
			$c_code = preg_replace("/(1|2|a|b)$/i",$c_end,$code);
			if(in_array($c_code,$this->groupes["code"])) return $r[$end];
			}
		return "r"; //groupe réduit
		}

	/**
	 * Ajouter au cahier de textes si besoin les classes présentes dans un regroupement
	 * 
	 * @param int $i   indice des informations du groupe concerné dans $this->gic (pour lecture des classes)
	 * @param int $key indice des informations du groupe connu dans $this->cdt_gic (pour maj uniquement)
	 * @param $id_gic  identifiant du regroupement dans le cahier de textes
	 */
	public function checkGicClasses($i,$key,$id_gic)
		{
		foreach($this->gic["classes"][$i] as $id_xml_classe)
			{
			if(!array_key_exists($id_xml_classe,$_SESSION[$this->sessname]["classes"])) continue;
			$id_cdt_classe = $_SESSION[$this->sessname]["classes"][$id_xml_classe];
			
			//vérification
			$query = "SELECT * FROM `cdt_groupe_interclasses_classe` WHERE `gic_ID`='".$id_gic."' AND `classe_ID`='".$id_cdt_classe."' LIMIT 1;";
			$this->query($query);
			if(mysqli_num_rows($this->requete)>0) continue;
			
			//il nous faut l'identifiant du groupe réduit
			if(in_array("groupe_reduit",$this->cdt_groupes["code"]))
				{
				$n = array_search("groupe_reduit",$this->cdt_groupes["code"]);
				$id_cdt_groupe = $this->cdt_groupes["id"][$n];
				}
			else $id_cdt_groupe = 0; //à l'utilisateur de reprendre son regroupement par la suite...
			
			//insertion						
			$query = "
			INSERT INTO `cdt_groupe_interclasses_classe` (`gic_ID`,`classe_ID`,`groupe_ID`) 
			VALUES ('".$id_gic."','".$id_cdt_classe."','".$id_cdt_groupe."');";
			$this->query($query);
			$this->cdt_gic["classes"][$key][] = mysqli_insert_id($conn_cahier_de_texte);
			}	
		}

	/**
	 * Calcul d'un format spécifique de mot de passe
	 * 
	 * @param int $key indice de la donnée concernée dans le tableau
	 * 
	 * @return string mot de passe généré
	 */
	public function getPwd($key)
		{
		$password = "";
		switch($this->step)
			{
			case "classes" :
			$password = str_replace($this->motif_code,$this->classes["code"][$key],$this->pwd_motif);
			break;
			
			case "profs" :
			$nom = preg_replace('/[^a-z0-9]/','',strtolower($this->profs["nom"][$key]));
			$prenom = preg_replace('/[^a-z0-9]/','',strtolower($this->profs["prenom"][$key]));
			$password = str_replace($this->motif_naissance,$this->profs["naissance"][$key],$this->pwd_motif);
			$password = str_replace($this->motif_nom,$nom,$password);
			$password = str_replace($this->motif_prenom,$prenom,$password);
			if(preg_match_all("/".$this->motif_n."/",$password,$matches))
				{
				foreach($matches[0] as $motif)
					{
					$extrait = str_replace($this->delimiteur,"",$motif);
					$taille = strlen($extrait);
					$cut = substr($nom,0,$taille);
					$password = str_replace($motif,$cut,$password);
					}
				}
			if(preg_match_all("/".$this->motif_p."/",$password,$matches))
				{
				foreach($matches[0] as $motif)
					{
					$extrait = str_replace($this->delimiteur,"",$motif);
					$taille = strlen($extrait);
					$cut = substr($prenom,0,$taille);
					$password = str_replace($motif,$cut,$password);
					}
				}
			break;
			}
		return $password;
		}

	/**
	 * Message de remplacement si présence ou non d'un mot de passe pour une donnée dans le cahier de textes
	 * 
	 * @param string $type toujours "classes" (seule donnée protégeable)
	 * @param int    $key  indice de la donnée concernée dans le tableau
	 * 
	 * @return string le message
	 */
	public function checkPwd($type,$key)
		{
		if(!in_array($type,$this->step_list)) $text = "";
		elseif(isset($this->{"cdt_".$type}["pwd"][$key]) && !empty($this->{"cdt_".$type}["pwd"][$key])) $text = "prot&eacute;g&eacute;";
		else $text = "non prot&eacute;g&eacute;";
		return $text;
		}

	/**
	 * Analyse de ce qui a été mis en session (donc seulement si emp_sts)
	 * Objectif : afficher à chaque étape, l'état d'avancement de la préparation d'insertion des emplois du temps conditionné par les correspondances enregistrées en session
	 */
	public function checkSession()
		{
		if(!$this->emp_sts) return true;
		
		$output = "
		<div class=\"commentaire\">
		<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" onsubmit=\"return setReinit();\">
		<p>Etat des correspondances dans la pr&eacute;paration d&rsquo;importation des emplois du temps : <input type=\"submit\" name=\"reinitialisation\" value=\"R&eacute;initialiser\"/></p>
		<input type=\"hidden\" name=\"reinit\" value=\"yes\"/><input type=\"hidden\" name=\"step\" value=\"".$this->step."\"/>
		</form>
		<ul>";
		$parameters = array
			(
			"profs" => array("nom"=>"utilisateur","genre"=>""),
			"matieres" => array("nom"=>"mati&egrave;re","genre"=>"e"),
			"classes" => array("nom"=>"classe","genre"=>"e"),
			"groupes" => array("nom"=>"groupe","genre"=>""),
			"gic" => array("nom"=>"regroupement","genre"=>"")
			);

		foreach($_SESSION[$this->sessname] as $step=>$associations)
			{
			if(!array_key_exists($step,$parameters)) continue;
			$name = $parameters[$step]["nom"];
			$title = strtoupper($name[0]).substr($name,1)."s";
			$e = $parameters[$step]["genre"];
			$count_xml = count($this->{$step}["id"]);
			$count_errors = 0;
			$count_success = 0;
			$count_abandon = 0;
			$display_list = "";
			$warning = "";
			foreach($this->{$step}["id"] as $n=>$id_xml) //listing de l'état du référencement
				{
				if(array_key_exists($id_xml,$associations))
					{
					$count_success++;
					if($associations[$id_xml]=="0")
						{
						$type = "N"; //une demande explicite de l'utilisateur de non prise en compte
						$count_abandon++;
						}
					elseif($this->{$step}["etat"][$n]=="R") $type = "R"; //vient juste d'être importé
					else $type = "P"; //association ok
					}
				else
					{
					$count_errors++;
					$type = "E";
					}
				$display_list .= "<li class=\"style".$type."\">";
				switch($step)
					{
					case "profs" : //affichage du login associée si pas de création de compte
					$login = $this->{$step}["login"][$n];
					if($this->useEnvole && $this->ldapCheckLogin($login)) $login = "<u>".$login."</u>";
					$display_list .= $login." / ".$this->{$step}["identite"][$n];
					break;
					
					case "matieres" :
					case "classes" :
					case "groupes" :
					$code = $this->{$step}["code"][$n];
					if($step=="classes" && $this->useEnvole && $this->ldapCheckClasse($code)) $code = "<u>".$code."</u>";
					$display_list .= $code." / ".$this->{$step}["nom"][$n];
					break;

					case "gic" :
					$display_list .= $this->{$step}["nom"][$n];
					break;				
					}
				$display_list .= " (".$type.")</li>";
				}

			//spécial profs : vérifions d'éventuels doublons (plusieurs emplois du temps mis sur un même compte cdt)
			if($step=="profs" && $count_errors==0)
				{
				$verif = array();
				foreach($_SESSION[$this->sessname][$step] as $id_xml=>$id_cdt) $verif[$id_cdt][] = $id_xml;
				foreach($verif as $id_cdt=>$users)
					{
					$count_edt = count($users);
					if($id_cdt!="0" && $count_edt>1)
						{
						$key_cdt = array_search($id_cdt,$this->{"cdt_".$step}["id"]);
						$login = $this->{"cdt_".$step}["login"][$key_cdt];
						$list_users = "";
						foreach($users as $id_xml)
							{
							$key_xml = array_search($id_xml,$this->{$step}["id"]);
							$list_users .= "<li>".$this->{$step}["prenom"][$key_xml]." ".$this->{$step}["nom"][$key_xml]."</li>";
							}
						$warning .= "
						<li>
						le compte <b>".$login."</b> du cdt est associ&eacute; &agrave; <b>".$count_edt."</b> individus du fichier XML :
						<ul>".$list_users."</ul>
						</li>
						";
						}
					}
				if(!empty($warning))
				$warning = "
				<div>
				<div style=\"margin:10px;text-align:center;color:red\">!!! <u>AVERTISSEMENT</u> !!!</div>
				&Agrave; v&eacute;rifier avant de poursuivre car vous avez associ&eacute; plusieurs personnes sur un même compte.<br/>
				Le compte concern&eacute; va donc recevoir l&rsquo;emploi du temps de chacune de ces personnes.
				<ul>".$warning."</ul>
				</div>";
				}

			//affichage
			$message = "";
			$cliquable = "style=\"text-decoration:underline;\"";
			if(array_search($this->step_next,$this->step_list)<=array_search($step,$this->step_list)) $message .= "aucun".$e." ".$name." du cdt n&rsquo;est encore associ&eacute;".$e." &agrave; ".($e ? "celles" : "ceux")." pr&eacute;sent".$e."s dans les fichiers.";
			elseif(!empty($this->{$step}["id"])) //ici, on a passé l'étape concernée, toute erreur est donc à signaler
				{
				if($count_errors>0) $message .= "<span style=\"color:red;\">ATTENTION</span> - Certain".$e."s ".$name."s n&rsquo;ont pas &eacute;t&eacute; enregistr&eacute;".$e."s (".$count_errors." sur ".$count_xml.")";
				else $message .= "<span class=\"succes\">pr&ecirc;t pour l&rsquo;importation</span> (".$count_xml." lu".$e."s , ".$count_success." enregistr&eacute;".$e."s".(empty($count_abandon) ? "" : " dont ".$count_abandon." non pris".(empty($e) ? "" : "es")." en compte").")";
				$message .= "<ul style=\"display:none;\">".$display_list."</ul>";
				if(!empty($warning)) $message .= $warning;
				$cliquable = "style=\"text-decoration:underline;cursor:pointer;\" onclick=\"seeList(this.parentNode);\"";
				}
			else $message .= "v&eacute;rification impossible (liste des ".$name."s pr&eacute;sent".$e."s dans le fichier non g&eacute;n&eacute;r&eacute;e)";
			$output .= "<li><span ".$cliquable.">".$title."</span> : ".$message."</li>";
			}
			
		$output .= "</ul></div>";
		echo $output;
		}

	/**
	 * Récupération d'un fichier
	 * 
	 * @param string $file nom du fichier
	 * 
	 * @return boolean true si action réussie et affichage d'un message d'erreur sinon
	 */
	public function file_upload($file)
		{
		if(!array_key_exists($file,$this->filenames)) $this->death("Le variable <i>".$file."</i> n&rsquo;est attach&eacute;e &agrave; aucun nom de fichier.");
		
		$upload_max_filesize = ini_get('upload_max_filesize');
		$post_max_size = ini_get('post_max_size');
		
		$to_file = $this->to_dir.$this->filenames[$file]; 
		$from_file = $_FILES[$file]['tmp_name'];
		$filename = $_FILES[$file]['name'];
		$erreur = intval($_FILES[$file]['error']);
		if($_FILES[$file]['size']==0) $erreur = 4;
		$is_upload = false;
		
		switch($erreur)
			{
			case 0 :
			$is_upload = @move_uploaded_file($from_file,$to_file);
			$message = "Impossible de copier le fichier <i>".$filename."</i> dans le dossier \"fichiers_joints\".<br/>V&eacute;rifiez les droits en &eacute;criture sur ce r&eacute;pertoire.";
			break;
			
			case 1 :
			$message = "Le fichier <i>".$filename."</i> d&eacute;passe la taille de <b>".$upload_max_filesize."</b>. Importation abandonn&eacute;e.";
			break;
			
			case 2 :
			$message = "Le fichier <i>".$filename."</i> d&eacute;passe la taille de <b>".$post_max_size."</b>. Importation abandonn&eacute;e.";
			break;
			
			case 3 :
			$message = "Le fichier <i>".$filename."</i> a &eacute;t&eacute; partiellement transf&eacute;r&eacute;. Envoyez-le &agrave; nouveau.";
			break;
			
			case 4 :
			$message = "Le fichier <i>".$filename."</i> est de taille nulle. Envoyez-le &agrave; nouveau.";
			break;
			
			default :
			$message = "Une erreur est survenue pendant l&rsquo;upload du fichier <i>".$filename."</i>. Envoyez-le &agrave; nouveau.";
			break; 
			}
			
		if($is_upload) return true;
		else $this->death($message);
		}

	/**
	 * Arrêt du script et affichage d'un message
	 * 
	 * @param string $text message à afficher
	 */
	public function death($text)
		{
		echo "<div class=\"commentaire\">".$text.$this->abandon."</div>";
		die();
		}

	/**
	 * Chargement des fichiers sts_emp et emp_sts
	 */
	public function loadFiles()
		{
		if(is_file($this->to_dir.$this->filenames["sts_emp"]) && !$this->sts_emp)
			{
			$this->sts_emp = simplexml_load_file($this->to_dir.$this->filenames["sts_emp"]);
			if(!$this->sts_emp) $this->death("Impossible de charger les donn&eacute;es du fichier <i>".$this->filenames["sts_emp"]."</i>.");
			}
		
		if(is_file($this->to_dir.$this->filenames["emp_sts"]) && !$this->emp_sts)
			{
			$this->emp_sts = simplexml_load_file($this->to_dir.$this->filenames["emp_sts"]);
			if(!$this->emp_sts) $this->death("Impossible de charger les donn&eacute;es du fichier <i>".$this->filenames["emp_sts"]."</i>.");
			}
		
		if($this->seeXML)
			{
			echo "<pre>";
			print_r($this->sts_emp);
			print_r($this->emp_sts);
			echo "</pre>";
			}
		}

	/**
	 * Lecture des objets XML représentants les fichiers
	 * Si emp_sts : on récupère toutes les données pour vérifier au fur et à mesure la préparation de l'importation des emplois du temps
	 * 
	 * @param string $type ce qui doit être lu
	 */
	public function loadXml($type="all")
		{
		if($this->emp_sts) $type="all"; //on force
		$this->loadInfos();
		if($type=="all" || $type=="profs") $this->loadProfs();
		if($type=="all" || $type=="matieres") $this->loadMatieres();
		if($type=="all" || $type=="classes") $this->loadClasses();
		if($type=="all" || $type=="groupes") $this->loadGroupes();
		if($type=="all" || $type=="gic") $this->loadGic();
		if($type=="all" || $type=="edt") $this->loadAlternances();
		}
	
	/**
	 * XML : Informations Générales - indépendant
	 */
	public function loadInfos()
		{	
		if(!$this->sts_emp) return false;
		
		foreach($this->sts_emp->xpath('//UAJ') as $infos_uaj)
			{
			$this->rne = (string)$infos_uaj["CODE"];
			$this->etab = trim(utf8_decode((string)$infos_uaj->DENOM_PRINC." ".(string)$infos_uaj->DENOM_COMPL));
			}
		foreach($this->sts_emp->xpath('//ANNEE_SCOLAIRE') as $infos_annee) $this->year = (int)$infos_annee["ANNEE"];
		}

	/**
	 * XML : Individus - indépendant
	 */
	public function loadProfs()
		{
		if(!$this->sts_emp) return false;

		//tableau réinitialisé si besoin
		if(!empty($this->profs["id"])) $this->cleanArray("profs");
		
		//expression régulière appliquée sur le motif du login
		$expression =  $this->delimiteur."(".$this->text_motifs["prenom"]."|".$this->text_motifs["nom"]."|N+|P+)".$this->delimiteur;
		
		//parcours du XML
		$n = 0;
		foreach($this->sts_emp->xpath('//INDIVIDU') as $infos_user)
			{
			//booléens
			$test_id = isset($infos_user["ID"]) && !empty($infos_user["ID"]);
			$test_prenom = isset($infos_user->PRENOM) && !empty($infos_user->PRENOM);
			$test_nom = isset($infos_user->NOM_USAGE) && !empty($infos_user->NOM_USAGE);

			//vérification des données obligatoires
			if( !($test_id && $test_prenom && $test_nom) )
				{
				$this->individus_errors++;
				continue;
				}

			//récupération des données
			//id et code sont à priori identique mais le code est traité surtout pour être assuré de ne pas avoir d'espaces
			$id_xml = (string)$infos_user["ID"];
			$code = $this->codeForm($id_xml);
			$prenom = $this->stringForm((string)$infos_user->PRENOM);
			$nom = $this->stringForm((string)$infos_user->NOM_USAGE);
			$login_prenom = $this->getCleanFirstname($prenom);
			$login_nom = $this->getCleanName($nom);
			
			if(isset($infos_user->DATE_NAISSANCE) && !empty($infos_user->DATE_NAISSANCE) && preg_match("/^([0-9]{4}).{1}([0-9]{2}).{1}([0-9]{2})$/",(string)$infos_user->DATE_NAISSANCE,$matches))
				{
				$naissance = $matches[3].$matches[2].$matches[1]; //format "jjmmaaaa" utilisé comme mot de passe par défaut des comptes créés
				}
			else $naissance = "00000000";
			
			if(isset($infos_user->CIVILITE) && !empty($infos_user->CIVILITE))
				{
				$user_civilite = intval((string)$infos_user->CIVILITE);
				if($user_civilite>3) $user_civilite = 0;
				$civilite = $this->civilites[$user_civilite];
				}
			else $civilite = $this->civilites[0];

			if(isset($infos_user->FONCTION) && !empty($infos_user->FONCTION))
				{
				$statut = strtolower((string)$infos_user->FONCTION);
				if(!in_array($statut,$this->statuts["id"])) $statut = "ens";
				}
			else $statut = "ens"; //enseignant par défaut
			
			if($this->useEnvole) $login = $this->ldapSearchLogin((string)$infos_user->PRENOM." ".(string)$infos_user->NOM_USAGE,$naissance);
			else $login = false;
			
			if(!$login)
				{
				if(preg_match("/".$expression."(\.?)".$expression."/",$this->login_motif,$matches)) //format spécifique de login demandé
					{
					$debut = $matches[1];
					$point = $matches[2];
					$fin = $matches[3];
					
					if($debut==$this->text_motifs["prenom"]) {$d = 0; $debut = $login_prenom;}
					elseif($debut==$this->text_motifs["nom"]) {$d = 0; $debut = $login_nom;}
					elseif(preg_match("/N+/",$debut)) {$d = strlen($debut); $debut = $login_nom;}
					elseif(preg_match("/P+/",$debut)) {$d = strlen($debut); $debut = $login_prenom;}
					else {$d = 0; $debut = $login_prenom;}

					if($fin==$this->text_motifs["prenom"]) {$f = 0; $fin = $login_prenom;}
					elseif($fin==$this->text_motifs["nom"]) {$f = 0; $fin = $login_nom;}
					elseif(preg_match("/N+/",$fin)) {$f = strlen($fin); $fin = $login_nom;}
					elseif(preg_match("/P+/",$fin)) {$f = strlen($fin); $fin = $login_prenom;}
					else {$f = 0; $fin = $login_prenom;}
					
					if($point==".") $point = true;
					else $point = false;
			
					$login = $this->getLogin($debut,$fin,$d,$f,$point);
					}
				else
					{
					$this->login_motif = $this->motif_prenom.".".$this->motif_nom;
					$login = $this->getLogin($login_prenom,$login_nom); //format par défaut
					}
				}

			//affectation
			$this->profs["id"][$n] = $id_xml;
			$this->profs["code"][$n] = $code;
			$this->profs["prenom"][$n] = $prenom;
			$this->profs["nom"][$n] = $nom;
			$this->profs["identite"][$n] = $civilite." ".$nom;
			$this->profs["login"][$n] = $login;
			$this->profs["naissance"][$n] = $naissance;
			$this->profs["statut"][$n] = $statut;
			$this->profs["etat"][$n] = "I";
			$n++;
			}			

		//utilisateur déjà présent dans le cdt ? on considère que oui si on retrouve le login mais sans homonymes possibles...
		//la vérification des homonymes oblige à faire ce traitement une fois tous les utilisateurs lus, donc dans une seconde boucle
		foreach($this->profs["id"] as $n=>$id_xml)
			{
			$login = $this->profs["login"][$n];
			$key = false;
			$doublon = false;
			
			//vérification des homonymes
			foreach($this->login_list as $original=>$quantite)
				{
				if($quantite<2) continue;
				elseif(preg_match("/^".$original."[0-9]*$/",$login))
					{
					$doublon = true;
					break;
					}
				}
			
			//login existant déjà ?
			if(!$doublon && isset($this->cdt_profs["login"]) && !empty($this->cdt_profs["login"]) && in_array($login,$this->cdt_profs["login"]))
				{			
				$key = array_search($login,$this->cdt_profs["login"]);
				
				//satut à jour
				$droits = $this->cdt_profs["statut"][$key];
				if(in_array($droits,$this->statuts["droits"]))
					{
					$key2 = array_search($droits,$this->statuts["droits"]);
					$this->profs["statut"][$n] = $this->statuts["id"][$key2];
					}
				
				//correspondance pour les emplois du temps avec possibilité laisser de ne pas prendre en compte la donnée
				if($this->emp_sts)
					{
					$id_cdt = $this->cdt_profs["id"][$key];
					if(!isset($_SESSION[$this->sessname]["profs"][$id_xml])) $_SESSION[$this->sessname]["profs"][$id_xml] = $id_cdt;
					}
				else $this->profs["etat"][$n] = "P"; //déjà présent donc plus importable si pas d'importation prévue des emplois du temps
				}
			elseif($this->emp_sts && isset($_SESSION[$this->sessname]["profs"][$id_xml]) && $_SESSION[$this->sessname]["profs"][$id_xml]>0) //pas de création, juste une référence
				{
				$id_cdt = $_SESSION[$this->sessname]["profs"][$id_xml];
				$key = array_search($id_cdt,$this->cdt_profs["id"]);
				if($key!==false) $this->profs["login"][$n] = $this->cdt_profs["login"][$key];
				}
			
			}
		
		//tri selon les noms
		array_multisort($this->profs["nom"],$this->profs["id"],$this->profs["code"],$this->profs["prenom"],$this->profs["naissance"],$this->profs["login"],$this->profs["identite"],$this->profs["statut"],$this->profs["etat"]);
		}

	/**
	 * XML : Matières - indépendant
	 */
	public function loadMatieres()
		{
		if(!$this->sts_emp) return false;
		
		//tableau réinitialisé si besoin
		if(!empty($this->matieres["id"])) $this->cleanArray("matieres");
			
		//parcours du XML
		$n = 0;
		foreach($this->sts_emp->xpath('//MATIERE') as $infos_matiere)
			{
			//booléens
			$test_id = isset($infos_matiere["CODE"]) && !empty($infos_matiere["CODE"]);
			$test_code = isset($infos_matiere->CODE_GESTION) && !empty($infos_matiere->CODE_GESTION);
			$test_libelle_1 = isset($infos_matiere->LIBELLE_EDITION) && !empty($infos_matiere->LIBELLE_EDITION);
			$test_libelle_2 = isset($infos_matiere->LIBELLE_LONG) && !empty($infos_matiere->LIBELLE_LONG);
			$test_libelle_3 = isset($infos_matiere->LIBELLE_COURT) && !empty($infos_matiere->LIBELLE_COURT);
			
			//vérification des données obligatoires
			if( ! ($test_id && ($test_code || $test_libelle_1 || $test_libelle_2 || $test_libelle_3)) )
				{
				$this->matieres_errors++;
				continue;
				}
			
			//id utilisé pour correspondance lors de l'importation des emplois du temps
			$id_xml = (string)$infos_matiere["CODE"];
			
			//code de la matière pour affichage/insertion en base (plus parlant pour l'utilisateur que l'id)
			if($test_code) $code = $this->codeForm((string)$infos_matiere->CODE_GESTION);
			else $code = $this->codeForm($id_xml);
			//assurer l'unicité
			$suffix = 0;
			$tmp_code = $code;
			while(in_array($tmp_code,$this->matieres["code"]))
				{
				$suffix++;
				$tmp_code = $code.$suffix;
				}
			$code = $tmp_code;
			
			//libelle de la matière mis en minuscule pour les comparaisons
			if($test_libelle_1) $nom = $this->stringForm((string)$infos_matiere->LIBELLE_EDITION);
			elseif($test_libelle_2) $nom = $this->stringForm((string)$infos_matiere->LIBELLE_LONG);
			elseif($test_libelle_3) $nom = $this->stringForm((string)$infos_matiere->LIBELLE_COURT);
			else $nom = $this->stringForm((string)$infos_matiere->CODE_GESTION);
			
			//affectation
			$this->matieres["id"][$n] = $id_xml;
			$this->matieres["code"][$n] = $code;
			$this->matieres["nom"][$n] = $nom;
			$this->matieres["etat"][$n] = "I";
			$n++;
			}

		//matière déjà présente dans le cdt ?
		//pour le nom, on valide si il est unique dans la liste totalement constituée, la recherche se fait donc dans une seconde boucle
		foreach($this->matieres["id"] as $n=>$id_xml)
			{
			$code = $this->matieres["code"][$n];
			$nom = $this->matieres["nom"][$n];
			$key = false;
			if(isset($this->cdt_matieres["code"]) && !empty($this->cdt_matieres["code"]) && in_array($code,$this->cdt_matieres["code"])) $key = array_search($code,$this->cdt_matieres["code"]); //même code
			elseif(isset($this->cdt_matieres["nom"]) && !empty($this->cdt_matieres["nom"]) && count(array_keys($this->matieres["nom"],$nom))==1) $key = array_search($nom,$this->cdt_matieres["nom"]); //même nom

			//correspondance trouvée
			if($key!==false)
				{
				$id_cdt = $this->cdt_matieres["id"][$key];
				$code_cdt = $this->cdt_matieres["code"][$key];
				
				//on assume d'insérer le code disponible
				if(empty($code_cdt)) $this->setCode("matieres",$key,$code);
				
				if($this->emp_sts) //correspondance pour les emplois du temps avec possibilité laisser de ne pas prendre en compte la donnée
					{	
					if(!isset($_SESSION[$this->sessname]["matieres"][$id_xml])) $_SESSION[$this->sessname]["matieres"][$id_xml] = $id_cdt;
					}
				else $this->matieres["etat"][$n] = "P"; //déjà présente donc plus importable si pas d'importation prévue des emplois du temps
				
				$this->matieres["nom"][$n] = $this->cdt_matieres["nom"][$key]; //on affiche le nom connu dans l'application
				}
			elseif($this->emp_sts && isset($_SESSION[$this->sessname]["matieres"][$id_xml]) && $_SESSION[$this->sessname]["matieres"][$id_xml]>0) //pas de création, juste une référence
				{
				$id_cdt = $_SESSION[$this->sessname]["matieres"][$id_xml];
				$key = array_search($id_cdt,$this->cdt_matieres["id"]);
				if($key!==false) $this->matieres["nom"][$n] = $this->cdt_matieres["nom"][$key];
				}
			}

		//tri selon les appellations
		array_multisort($this->matieres["nom"],$this->matieres["id"],$this->matieres["code"],$this->matieres["etat"]);
		}

	/**
	 * Insertion d'un code pour une donnée existant déjà dans le cahier de textes
	 * 
	 * @param string $step donnée concernée
	 * @param int    $key  index dans le tableau cdt_{step}
	 * @param string $code valeur à insérer
	 */
	public function setCode($step,$key,$code)
		{
		$liste = array("matieres","classes","groupes","gic");
		if(in_array($step,$liste))
			{
			$id_cdt = $this->{"cdt_".$step}["id"][$key];
			$this->{"cdt_".$step}["code"][$key] = $code;
			switch($step)
				{
				case "matieres" :
				$query = "UPDATE `cdt_matiere` SET `code_matiere`='".$code."' WHERE `ID_matiere`='".$id_cdt."' LIMIT 1;";
				break;
				
				case "classes" :
				$query = "UPDATE `cdt_classe` SET `code_classe`='".$code."' WHERE `ID_classe`='".$id_cdt."' LIMIT 1;";
				break;
				
				case "groupes" :
				$query = "UPDATE `cdt_groupe` SET `code_groupe`='".$code."' WHERE `ID_groupe`='".$id_cdt."' LIMIT 1;";
				break;

				case "gic" :
				$query = "UPDATE `cdt_groupe_interclasses` SET `code_gic`='".$code."' WHERE `ID_gic`='".$id_cdt."' LIMIT 1;";
				break;
				}
			$this->query($query);
			}
		}

	/**
	 * XML : Classes - indépendant
	 */
	public function loadClasses()
		{
		if(!$this->sts_emp) return false;
		
		//tableau réinitialisé si besoin
		if(!empty($this->classes["id"])) $this->cleanArray("classes");

		//parcours du XML
		$n = 0;	
		foreach($this->sts_emp->xpath('//DIVISION') as $infos_classe)
			{
			//booléens
			$test_code = isset($infos_classe["CODE"]) && !empty($infos_classe["CODE"]);
			$test_libelle_1 = isset($infos_classe->LIBELLE_EDITION) && !empty($infos_classe->LIBELLE_EDITION);
			$test_libelle_2 = isset($infos_classe->LIBELLE_LONG) && !empty($infos_classe->LIBELLE_LONG);
			$test_libelle_3 = isset($infos_classe->LIBELLE_COURT) && !empty($infos_classe->LIBELLE_COURT);
			
			//vérification des données obligatoires
			if(!$test_code)
				{
				$this->classes_errors++;
				continue;
				}

			//id utilisé pour correspondance lors de l'importation des emplois du temps
			$id_xml = (string)$infos_classe["CODE"];
						
			//code de la classe
			$code = $this->classeForm($id_xml);
			
			//assurer l'unicité
			$suffix = 0;
			$tmp_code = $code;
			while(in_array($tmp_code,$this->classes["code"]))
				{
				$suffix++;
				$tmp_code = $code.$suffix;
				}
			$code = $tmp_code;
			
			
			//libelle de la classe
			if($test_libelle_1) $nom = $this->stringForm((string)$infos_classe->LIBELLE_EDITION);
			elseif($test_libelle_2) $nom = $this->stringForm((string)$infos_classe->LIBELLE_LONG);
			elseif($test_libelle_3) $nom = $this->stringForm((string)$infos_classe->LIBELLE_COURT);
			else $nom = $this->stringForm((string)$infos_classe["CODE"]);
			
			//affectation
			$this->classes["id"][$n] = $id_xml;
			$this->classes["code"][$n] = $code;
			$this->classes["nom"][$n] = $nom;
			$this->classes["etat"][$n] = "I";
			$this->classes["pwd"][$n] = "";
			$n++;
			}

		//classes déjà présentes dans le cdt ?
		//pour le nom, on valide si il est unique dans la liste totalement constituée, la recherche se fait donc dans une seconde boucle
		foreach($this->classes["id"] as $n=>$id_xml)
			{
			$code = $this->classes["code"][$n];
			$nom = $this->classes["nom"][$n];
			$key = false;
			if(isset($this->cdt_classes["code"]) && !empty($this->cdt_classes["code"]) && in_array($code,$this->cdt_classes["code"])) $key = array_search($code,$this->cdt_classes["code"]); //même code
			elseif(isset($this->cdt_classes["nom"]) && !empty($this->cdt_classes["nom"]) && count(array_keys($this->classes["nom"],$nom))==1) $key = array_search($nom,$this->cdt_classes["nom"]); //même nom

			//correspondance trouvée
			if($key!==false)
				{
				$id_cdt = $this->cdt_classes["id"][$key];
				$code_cdt = $this->cdt_classes["code"][$key];
				
				//on assume d'insérer le code disponible
				if(empty($code_cdt)) $this->setCode("classes",$key,$code);
				
				if($this->emp_sts) //correspondance pour les emplois du temps avec possibilité laisser de ne pas prendre en compte la donnée
					{	
					if(!isset($_SESSION[$this->sessname]["classes"][$id_xml])) $_SESSION[$this->sessname]["classes"][$id_xml] = $id_cdt;
					}
				else $this->classes["etat"][$n] = "P"; //déjà présente donc plus importable si pas d'importation prévue des emplois du temps
				
				$this->classes["nom"][$n] = $this->cdt_classes["nom"][$key]; //on affiche le nom connu dans l'application
				$this->classes["pwd"][$n] = $this->checkPwd("classes",$key);	
				}
			elseif($this->emp_sts && isset($_SESSION[$this->sessname]["classes"][$id_xml]) && $_SESSION[$this->sessname]["classes"][$id_xml]>0) //pas de création, juste une référence
				{
				$id_cdt = $_SESSION[$this->sessname]["classes"][$id_xml];
				$key = array_search($id_cdt,$this->cdt_classes["id"]);
				if($key!==false)
					{
					$this->classes["nom"][$n] = $this->cdt_classes["nom"][$key];
					$this->classes["pwd"][$n] = $this->checkPwd("classes",$key);
					}
				}
			}
				
		//tri selon les codes (souvent lisibles de la forme "31","32",...)
		array_multisort($this->classes["code"],$this->classes["id"],$this->classes["nom"],$this->classes["etat"],$this->classes["pwd"]);
		}

	/**
	 * XML : groupes - matieres/classes/profs doivent avoir été chargées
	 * Ici, la variable de session contient toutes les correspondances nécessaires des classes/profs/matières insérées au préalable
	 * On utilise un état supplémentaire "E" pour erreur si une correspondance est manquante
	 * Un groupe = une classe et peut-être plusieurs matières concernées (enseignants non pris en compte)
	 * On prévoit la possibilité de créer des noms de groupes même si il vaut mieux à l'utilisation éviter et se contenter de groupeA, groupeB ou groupeRéduit
	 */
	public function loadGroupes()
		{
		if(!$this->emp_sts || empty($this->matieres["id"]) || empty($this->classes["id"])) return false;

		//tableau réinitialisé si besoin
		if(!empty($this->groupes["id"])) $this->cleanArray("groupes");
		
		//parcours du XML
		$n = 0;		
		foreach($this->emp_sts->xpath('//GROUPE') as $infos_groupes)
			{
			$groupes_classes = $infos_groupes->DIVISIONS_APPARTENANCE->DIVISION_APPARTENANCE;
			$groupes_services = $infos_groupes->SERVICES->SERVICE;
			$etat = "I";
			
			//booléens
			$test_id = isset($infos_groupes["CODE"]) && !empty($infos_groupes["CODE"]);
			$test_classe = isset($groupes_classes) && count($groupes_classes)==1 && isset($groupes_classes[0]["CODE"]) && !empty($groupes_classes[0]["CODE"]); //un groupe = portion d'une classe
			$test_services = isset($groupes_services) && !empty($groupes_services); //détails des heures de cours avec matière et enseigant concerné
					
			//vérification des données obligatoires
			if(!$test_classe) continue;
			elseif(!$test_id || !$test_services)
				{
				$this->groupes_errors++;
				continue;
				}

			//id utilisé pour correspondance lors de l'importation des emplois du temps
			$id_groupe = (string)$infos_groupes["CODE"];

			//code du groupe
			$code = $this->codeForm($id_groupe);
			//assurer l'unicité
			$suffix = 0;
			$tmp_code = $code;
			while(in_array($tmp_code,$this->groupes["code"]))
				{
				$suffix++;
				$tmp_code = $code.$suffix;
				}
			$code = $tmp_code;

			//lecture de la classe rattachée
			$code_classe = (string)$groupes_classes[0]["CODE"];
			if(in_array($code_classe,$this->classes["id"]) && array_key_exists($code_classe,$_SESSION[$this->sessname]["classes"]))
				{
				$id_classe = $code_classe;
				if($_SESSION[$this->sessname]["classes"][$code_classe]=="0") $etat = "N";
				}
			else
				{
				$id_classe = "-1"; //sans association de classe, on ne pourra pas insérer l'emploi du temps
				$etat = "E";
				}

			//lecture des matières rattachées
			$matieres = array();
			$matieres_ok = false;
			$matieres_ko = false;
			foreach($groupes_services as $infos_service)
				{
				if(!isset($infos_service["CODE_MATIERE"]) || empty($infos_service["CODE_MATIERE"])) continue;
				$code_matiere = (string)$infos_service["CODE_MATIERE"];
				if(in_array($code_matiere,$this->matieres["id"]) && array_key_exists($code_matiere,$_SESSION[$this->sessname]["matieres"]))
					{
					$matieres[] = $code_matiere;
					//il faut au moins une matière valide pour rendre possible l'importation des emplois du temps
					if($_SESSION[$this->sessname]["matieres"][$code_matiere]!="0") $matieres_ok = true;
					}
				else
					{
					$matieres_ko = true;
					$matieres[] = -1;
					}
				}
			//on met à jour l'état du groupe
			if($etat=="I")
				{
				if($matieres_ok) $etat = "I"; //une matière valide
				elseif($matieres_ko) $etat = "E"; //aucune valide et une erreur, donc au moins une matière à prendre en compte pose problème
				else $etat = "N"; //aucune matière de ce groupe n'a été demandé à être prise en compte
				}
			
			//pour le nom du groupe : formé des matières concernées + la classe
			$nom_groupe = "";
			foreach($matieres as $id_matiere)
				{
				$key_matiere = array_search($id_matiere,$this->matieres["id"]);
				if(!empty($nom_groupe)) $nom_groupe .= "_";
				$nom_groupe .= ($key_matiere===false) ? "?????" : $this->matieres["code"][$key_matiere];
				}
			$key_classe = array_search($id_classe,$this->classes["id"]);
			$nom_groupe .= ($key_classe===false) ? "_??" : "_".$this->classes["code"][$key_classe];
				
			$this->groupes["id"][$n] = $id_groupe;
			$this->groupes["code"][$n] = $code;
			$this->groupes["nom"][$n] = $nom_groupe;
			$this->groupes["indication"][$n] = $nom_groupe; //lui ne change pas et sert à l'affichage pour mieux voir à quoi correspond le groupe (matières et classe)
			$this->groupes["classe"][$n] = $id_classe;
			$this->groupes["matieres"][$n] = $matieres;
			$this->groupes["etat"][$n] = $etat;
			if($etat=="N") $_SESSION[$this->sessname]["groupes"][$id_groupe] = "0";
			$n++;
			}

		//mettre à jour les noms des groupes identifiés comme groupe A ou B
		foreach($this->groupes["code"] as $n=>$code)
			{
			$type = $this->getTypeGroupe($code);
			$nom = $this->groupes["nom"][$n];
			switch($type)
				{
				case "a" :
				$this->groupes["nom"][$n] .= "_A";
				$this->groupes["indication"][$n] .= "_A";
				break;
				
				case "b" :
				$this->groupes["nom"][$n] .= "_B";
				$this->groupes["indication"][$n] .= "_B";
				break;
				}
			}

		//groupe déjà présent dans le cdt ?
		foreach($this->groupes["id"] as $n=>$id_xml)
			{
			if($this->groupes["etat"][$n]!="I") continue;
			$code = $this->groupes["code"][$n];
			$nom = $this->groupes["nom"][$n];
			$key = false;
			if(isset($this->cdt_groupes["code"]) && !empty($this->cdt_groupes["code"]) && in_array($code,$this->cdt_groupes["code"])) $key = array_search($code,$this->cdt_groupes["code"]); //même code
			elseif(isset($this->cdt_groupes["nom"]) && !empty($this->cdt_groupes["nom"]) && count(array_keys($this->groupes["nom"],$nom))==1) $key = array_search($nom,$this->cdt_groupes["nom"]); //même nom

			//correspondance trouvée
			if($key!==false)
				{
				$id_cdt = $this->cdt_groupes["id"][$key];
				$code_cdt = $this->cdt_groupes["code"][$key];
				
				//on assume d'insérer le code disponible
				if(empty($code_cdt)) $this->setCode("groupes",$key,$code);
				
				//correspondance pour les emplois du temps avec possibilité laisser de ne pas prendre en compte la donnée
				if(!isset($_SESSION[$this->sessname]["groupes"][$id_xml])) $_SESSION[$this->sessname]["groupes"][$id_xml] = $id_cdt;
				
				$this->groupes["nom"][$n] = $this->cdt_groupes["nom"][$key]; //on affiche le nom connu dans l'application
				}
			elseif(isset($_SESSION[$this->sessname]["groupes"][$id_xml]) && $_SESSION[$this->sessname]["groupes"][$id_xml]>0) //pas de création, juste une référence
				{
				$id_cdt = $_SESSION[$this->sessname]["groupes"][$id_xml];
				$key = array_search($id_cdt,$this->cdt_groupes["id"]);
				if($key!==false) $this->groupes["nom"][$n] = $this->cdt_groupes["nom"][$key];
				}
			}

		//tri selon les codes
		array_multisort($this->groupes["nom"],$this->groupes["indication"],$this->groupes["code"],$this->groupes["id"],$this->groupes["classe"],$this->groupes["matieres"],$this->groupes["etat"]);
		}

	/**
	 * XML : Regroupements (gic pour Groupements Inter Classes) - matieres/classes/profs doivent avoir été chargées
	 * Ici, la variable de session contient toutes les correspondances nécessaires des classes/profs/matières insérées au préalable
	 * On utilise un état supplémentaire "E" pour erreur si une correspondance est manquante
	 * Un regroupement = groupe + matière donc son code sera la concaténation des 2 auquel on ajoute en début de chaîne pour l'unicité l'id_cdt du prof concerné car un regroupement appartient à un prof donné
	 */
	public function loadGic()
		{
		if(!$this->emp_sts || empty($this->matieres["id"]) || empty($this->classes["id"]) || empty($this->profs["id"])) return false;

		//tableau réinitialisé si besoin
		if(!empty($this->gic["id"])) $this->cleanArray("gic");
		
		//parcours du XML
		$n = 0;		
		foreach($this->emp_sts->xpath('//GROUPE') as $infos_gic)
			{
			$gic_classes = $infos_gic->DIVISIONS_APPARTENANCE->DIVISION_APPARTENANCE;
			$gic_services = $infos_gic->SERVICES->SERVICE;
			
			//booléens
			$test_id = isset($infos_gic["CODE"]) && !empty($infos_gic["CODE"]);
			$test_classes = isset($gic_classes) && count($gic_classes)>1; //Il faut au moins 2 classes rattachées pour être un regroupement
			$test_services = isset($gic_services) && !empty($gic_services); //détails des heures de cours avec matière et enseignant concerné
					
			//vérification des données obligatoires
			if(!$test_classes) continue;
			elseif(!$test_id || !$test_services)
				{
				$this->gic_errors++;
				continue;
				}

			//id du groupe (pas encore celui du regroupement...)
			$id_groupe = (string)$infos_gic["CODE"];
			
			//lecture des classes rattachées
			$classes = array();
			foreach($gic_classes as $infos_classe)
				{
				if(!isset($infos_classe["CODE"]) || empty($infos_classe["CODE"])) continue;
				$code_classe = (string)$infos_classe["CODE"];
				if(in_array($code_classe,$this->classes["id"]) && array_key_exists($code_classe,$_SESSION[$this->sessname]["classes"])) $classes[] = $code_classe;
				else $classes[] = -1;
				}
			if(count($classes)<2) continue; //Il faut au moins 2 classes rattachées pour être un regroupement
			
			//lecture des matières rattachées
			foreach($gic_services as $infos_service)
				{
				if(!isset($infos_service["CODE_MATIERE"]) || empty($infos_service["CODE_MATIERE"])) continue;
				$etat = "I"; //l'état va évoluer en fonction de celui de la matière et du prof concerné (pas les classes car non bloquant, rattachables plus tard à la main par l'enseignant)
				
				$code_matiere = (string)$infos_service["CODE_MATIERE"];
				if(in_array($code_matiere,$this->matieres["id"]) && array_key_exists($code_matiere,$_SESSION[$this->sessname]["matieres"]))
					{
					$id_matiere = $code_matiere;
					if($_SESSION[$this->sessname]["matieres"][$code_matiere]=="0") $etat = "N";
					}
				else 
					{
					$id_matiere = -1;
					$etat = "E";
					}

				foreach($infos_service->ENSEIGNANTS->ENSEIGNANT as $infos_prof)
					{
					if(!isset($infos_prof["ID"]) || empty($infos_prof["ID"])) continue;
					
					$code_prof = (string)$infos_prof["ID"];
					if(in_array($code_prof,$this->profs["id"]) && array_key_exists($code_prof,$_SESSION[$this->sessname]["profs"])) 
						{
						$id_prof = $code_prof;
						if($_SESSION[$this->sessname]["profs"][$code_prof]=="0" && $etat=="I") $etat = "N";
						}
					else 
						{
						$id_prof = -1;
						$etat = "E";
						}
				
					//nom du regroupement
					$nom_gic = "";
					$key_matiere = array_search($id_matiere,$this->matieres["id"]);
					$nom_gic .= ($key_matiere===false) ? "??????" : $this->matieres["code"][$key_matiere];
					foreach($classes as $id_classe)
						{
						$key_classe = array_search($id_classe,$this->classes["id"]);
						$nom_gic .= ($key_classe===false) ? "_??" : "_".$this->classes["code"][$key_classe];
						}
					
					//code du regroupement
					$code_gic = $this->codeForm($id_groupe);
					if($etat!="E")
						{
						$code_gic .= $code_matiere;
						$code_gic = $this->codeForm($_SESSION[$this->sessname]["profs"][$id_prof].$code_gic);
						}
					else //regroupement non valide, on assure tout de même l'unicité du code
						{
						$suffix = 0;
						$tmp_code = $code_gic;
						while(in_array($tmp_code,$this->gic["code"]))
							{
							$suffix++;
							$tmp_code = $code_gic.$suffix;
							}
						$code_gic = $tmp_code;		
						}
					
					//affectation
					$id_gic = $id_groupe.$id_matiere.$id_prof;
					$this->gic["id"][$n] = $id_gic;
					$this->gic["nom"][$n] = $nom_gic;
					$this->gic["classes"][$n] = $classes;
					$this->gic["matiere"][$n] = $id_matiere;
					$this->gic["prof"][$n] = $id_prof;
					$this->gic["code"][$n] = $code_gic;
					$this->gic["etat"][$n] = $etat;
					if($etat=="N") $_SESSION[$this->sessname]["gic"][$id_gic] = "0";
					
					//regroupement déjà présent dans le cdt ?
					//le code suffit mais pour le nom, il faut aussi que ce soit pour le même prof et avec les mêmes classes déclarées
					if($etat=="I") //éviter de chercher une correspondance pour une regroupement en erreur ou à ne pas prendre en compte
						{
						$key = false;
						if(!empty($code_gic) && in_array($code_gic,$this->cdt_gic["code"])) $key = array_search($code_gic,$this->cdt_gic["code"]); //même code
						else $key = $this->checkGic($n); //test plus détaillé

						//correspondance trouvée
						if($key!==false)
							{
							$id_cdt = $this->cdt_gic["id"][$key];
							$code_cdt = $this->cdt_gic["code"][$key];
							
							//on assume d'insérer le code disponible
							if(empty($code_cdt)) $this->setCode("gic",$key,$code_gic);
							
							//correspondance pour les emplois du temps avec possibilité laisser de ne pas prendre en compte la donnée
							if(!isset($_SESSION[$this->sessname]["gic"][$id_gic])) $_SESSION[$this->sessname]["gic"][$id_gic] = $id_cdt;
							
							$this->gic["nom"][$n] = $this->cdt_gic["nom"][$key]; //on affiche le nom connu dans l'application
							}
						elseif(isset($_SESSION[$this->sessname]["gic"][$id_gic]) && $_SESSION[$this->sessname]["gic"][$id_gic]>0) //pas de création, juste une référence
							{
							$id_cdt = $_SESSION[$this->sessname]["gic"][$id_gic];
							$key = array_search($id_cdt,$this->cdt_gic["id"]);
							if($key!==false) $this->gic["nom"][$n] = $this->cdt_gic["nom"][$key];
							}
						} // fin $check

					$n++;
					} //prof
				} //matière
			} //groupe
			
		//tri selon les codes
		array_multisort($this->gic["nom"],$this->gic["code"],$this->gic["id"],$this->gic["classes"],$this->gic["matiere"],$this->gic["prof"],$this->gic["etat"]);;
		}

	/**
	 * Recherche dans le cdt d'un regroupement identique : même nom, même propriétaire, mêmes classes déclarées
	 * 
	 * @param int $n index du regroupement dans le tableau $this->gic
	 * 
	 * @return boolean si retrouvé ou non
	 */
	public function checkGic($n)
		{
		$nom = $this->gic["nom"][$n];
		$id_xml_prof = $this->gic["prof"][$n];
		if(!isset($_SESSION[$this->sessname]["profs"][$id_xml_prof]) || empty($_SESSION[$this->sessname]["profs"][$id_xml_prof]) || !in_array($nom,$this->cdt_gic["nom"])) return false;
		$id_cdt_prof = $_SESSION[$this->sessname]["profs"][$id_xml_prof];
		$keys = array_keys($this->cdt_gic["nom"],$nom); //possibilités d'avoir plusieurs regroupements sous un même nom...
		
		foreach($keys as $key)
			{	
			$id_cdt_user = $this->cdt_gic["prof"][$key];
			if($id_cdt_prof==$id_cdt_user)
				{
				if(!isset($this->cdt_gic["classes"][$key]) || empty($this->cdt_gic["classes"][$key])) continue; //un regroupement sans classes ? sûrement inutilisée mais on n'en tient pas compte							
				$all_found = true;
				
				//vérifions les classes
				foreach($this->gic["classes"][$n] as $id_xml_classe)
					{
					if(!array_key_exists($id_xml_classe,$_SESSION[$this->sessname]["classes"])) continue; //pas de référence : pas normal mais on ignore car elle ne sera pas prise en compte lors de l'importation des edt
					$id_cdt_classe = $_SESSION[$this->sessname]["classes"][$id_xml_classe];
					if(!in_array($id_cdt_classe,$this->cdt_gic["classes"][$key])) //on trouve une classe non présente dans le regroupement testé donc il est considéré comme différent
						{
						$all_found = false;
						break; //inutile de vérifier les éventuelles autres classes de ce regroupement
						}
					}
				
				//correspondance trouvée
				if($all_found) return $key;
				}
			}

		return false;
		}

	/**
	 * XML : Alternances - indépendant
	 */
	public function loadAlternances()
		{	
		if(!$this->emp_sts) return false;
		
		//tableau réinitialisé si besoin
		if(!empty($this->alternances["id"])) $this->cleanArray("alternances");
		
		//tableau temporaire pour identifier les périodes
		$tmp_alternances = array();
		
		//parcours du XML
		$n = 0;
		foreach($this->emp_sts->xpath('//ALTERNANCE') as $infos_alternance)
			{
			$semaines = $infos_alternance->SEMAINES->DATE_DEBUT_SEMAINE;
			
			//booléens
			$test_id = isset($infos_alternance["CODE"]) && !empty($infos_alternance["CODE"]);
			$test_libelle_1 = isset($infos_alternance->LIBELLE_EDITION) && !empty($infos_alternance->LIBELLE_EDITION);
			$test_libelle_2 = isset($infos_alternance->LIBELLE_LONG) && !empty($infos_alternance->LIBELLE_LONG);
			$test_libelle_3 = isset($infos_alternance->LIBELLE_COURT) && !empty($infos_alternance->LIBELLE_COURT);
			$test_semaines =  isset($semaines) && !empty($semaines); 
			
			//vérification des données obligatoires
			if(!$test_id && $test_semaines) continue;
			
			//identifiant et code
			$id_xml = (string)$infos_alternance["CODE"];
			$code = $this->codeForm($id_xml);

			//libelle
			if($test_libelle_1) $nom = $this->stringForm((string)$infos_alternance->LIBELLE_EDITION);
			elseif($test_libelle_2) $nom = $this->stringForm((string)$infos_alternance->LIBELLE_LONG);
			elseif($test_libelle_3) $nom = $this->stringForm((string)$infos_alternance->LIBELLE_COURT);
			else $nom = $this->stringForm($id);
			
			//affectation
			$this->alternances["id"][$n] = $id_xml;
			$this->alternances["code"][$n] = $id_xml;
			$this->alternances["nom"][$n] = $nom;
			$this->alternances["type"][$n] = "X"; // il faudra identifier les semaines A, B et les cours hebdomadaires (A et B)
			$this->alternances["first"][$n] = 0;
			$this->alternances["second"][$n] = 0; //pour identifier A et B
			$this->alternances["last"][$n] = 0; //pour les semestres
			
			//semaines concernées
			$i = 0;
			foreach ($semaines as $semaine)
				{
				$date = trim((string)$semaine);
				
				//vérification du format de date (le message d'échec apparaissant dès la fin de l'upload où tout est chargé une première fois)
				if(!preg_match("/^([0-9]{4}).{1}([0-9]{2}).{1}([0-9]{2})$/",$date,$matches))
				$this->death("La date \"".$date."\" est dans un format non valide.");
				
				//extraction des détails de la date
				$annee = intval($matches[1]);
				$mois = intval($matches[2]);
				$jour = intval($matches[3]);
				
				//correctif de certains fichiers dont les dates ne sont pas des lundis (seuls des dimanches ont été constatés)
				$jour_zero = intval(date('w',mktime(0,0,0,$mois,$jour,$annee))); //entre 0 et 6
				if($jour_zero==0) $jour++; //le dimanche avance
				elseif($jour_zero>1) $jour = $jour - ($jour_zero-1); //les autres jours reculent
				
				$debut = mktime(0,0,0,$mois,$jour,$annee); //un lundi
				$fin = mktime(0,0,0,$mois,$jour+6,$annee); //un dimanche
				
				//first/second/last
				if($i==0)
					{
					$this->alternances["first"][$n] = $debut;
					$this->alternances["second"][$n] = $debut;
					$this->alternances["last"][$n] = $fin;				
					}
				else
					{
					if($debut<=$this->alternances["first"][$n]) $this->alternances["first"][$n] = $debut;
					elseif($this->alternances["first"][$n]==$this->alternances["second"][$n] || $debut<$this->alternances["second"][$n]) $this->alternances["second"][$n] = $debut;
					if($fin>$this->alternances["last"][$n]) $this->alternances["last"][$n] = $fin;
					}
				
				//affectation
				$this->alternances["semaines"][$n]["tri"][$i] = date('Ymd',$debut); //pour trier mais aussi valeur à placer dans le table des alternances du cdt
				$this->alternances["semaines"][$n]["debut"][$i] = date('d-m-Y',$debut);
				$this->alternances["semaines"][$n]["fin"][$i] = date('d-m-Y',$fin);

				$i++;
				}
			
			array_multisort($this->alternances["semaines"][$n]["tri"],$this->alternances["semaines"][$n]["debut"],$this->alternances["semaines"][$n]["fin"]);
			$n++;
			}
		
		//pour automatisation mais la décision finale est humaine...
		//tentative pour identifier les différentes alternances
		//hypothèses :
		//A et B sont les seules périodes par quinzaine (les 2 premières trouvées seront repérées comme A et B)
		//H est la période avec le maximum de semaines
		//S2 est la période qui commence le plus tard
		foreach($this->alternances["nom"] as $key=>$nom)
			{
			$ecart = ($this->alternances["second"][$key]-$this->alternances["first"][$key])/(3600*24);
			
			//on identifie comme A et B les 2 premières périodes dans les 2 premières semaines ont 15 jours d'écart
			if($ecart==14 && !in_array("B",$this->alternances["type"])) //c'est A ou B et on traite tant que B n'est pas identifié
				{
				if(!in_array("A",$this->alternances["type"])) $this->alternances["type"][$key] = "A"; //premier passage, on suppose A
				else //passages suivants, A existe, on déduit B en rectifiant si besoin
					{
					$key_A = array_search("A",$this->alternances["type"]);
					if($this->alternances["first"][$key]>=$this->alternances["first"][$key_A]) $this->alternances["type"][$key] = "B"; //on trouve B
					else //on a mal supposé A, on intervertit
						{
						$this->alternances["type"][$key] = "A";
						$this->alternances["type"][$key_A] = "B";
						}
					}
				}
			elseif($ecart==7) //c'est H, S1 ou S2
				{
				if(!in_array("H",$this->alternances["type"])) $this->alternances["type"][$key] = "H"; //premier passage, on suppose H
				else //passages suivants, H existe, on cherche à identifier S1 et S2
					{
					$key_H = array_search("H",$this->alternances["type"]);
					if($this->alternances["first"][$key]>$this->alternances["first"][$key_H]) $this->alternances["type"][$key] = "S2"; //on a trouvé S2 mais H n'est pas validé
					else
						{
						if(count($this->alternances["semaines"][$key]["debut"])>count($this->alternances["semaines"][$key_H]["debut"])) //plus de semaines que H => H est S1 ou S2
							{
							$this->alternances["type"][$key] = "H"; //devient H
							if($this->alternances["first"][$key_H]>$this->alternances["first"][$key]) $this->alternances["type"][$key_H] = "S2"; //H commence strictement après, H devient S2
							else $this->alternances["type"][$key_H] = "S1"; //H devient S1
							}
						elseif(!in_array("S1",$this->alternances["type"])) $this->alternances["type"][$key] = "S1"; //moins ou autant de semaines que H, on suppose S1
						else //ici, H et S1 existent et la période analysée possède moins ou autant de semaines que H => est S1 ou S2
							{
							$key_S1 = array_search("S1",$this->alternances["type"]);
							if($this->alternances["first"][$key_H]>$this->alternances["first"][$key]) $this->alternances["type"][$key] = "S2"; //H commence strictement avant => c'est S2
							else //c'est S1, on intervertit avec le S1 existant
								{
								$this->alternances["type"][$key] = "S1";
								$this->alternances["type"][$key_S1] = "S2";
								}					
							}
						}
					}
				}
			}
		/*
		foreach($this->alternances["nom"] as $key=>$nom)
			{
			echo "<h1>".$nom."</h1>";
			echo "<p>type : ".$this->alternances["type"][$key]."</p>";
			echo "<p>first : ".date('d-m-Y',$this->alternances["first"][$key])."</p>";
			echo "<p>second : ".date('d-m-Y',$this->alternances["second"][$key])."</p>";
			echo "<p>last : ".date('d-m-Y',$this->alternances["last"][$key])."</p>";
			}
		*/
		}

	/**
	 * XML : Cours
	 * Compte le nombre de cours présent et alimente $this->edt si étape "edt_import"
	 */
	public function loadEdt()
		{
		if(!$this->emp_sts) return false;
		
		if(!empty($this->edt)) $this->cleanArray("edt");
		
		foreach($this->emp_sts->xpath('//DIVISION') as $infos)
			{
			$services = $infos->SERVICES->SERVICE; //détails des heures de cours avec matière et enseignant concerné
			
			//booléens
			$test_id = isset($infos["CODE"]) && !empty($infos["CODE"]);
			$test_services = isset($services) && !empty($services);
					
			//vérification
			if(!($test_id && $test_services))
				{
				$this->edt_errors++;
				continue;
				}

			//id de la classe
			$id_xml_classe = (string)$infos["CODE"];
			$id_xml_groupe = 0;

			foreach($services as $service)
				{
				if(!isset($service["CODE_MATIERE"]) || empty($service["CODE_MATIERE"]))
					{
					$this->edt_errors++;
					continue;
					}
					
				$id_xml_matiere = (string)$service["CODE_MATIERE"];

				foreach($service->ENSEIGNANTS->ENSEIGNANT as $prof)
					{
					$cours = $prof->COURS_RATTACHES->COURS; //cours de l'enseignant
					
					$test_prof = isset($prof["ID"]) && !empty($prof["ID"]);
					$test_cours = isset($cours) && !empty($cours);
					
					if(!($test_prof && $test_cours))
						{
						$this->edt_errors++;
						continue;
						}
						
					$id_xml_prof = (string)$prof["ID"];
					
					//lecture et enregistrement des cours
					$this->setCours($cours,$id_xml_prof,$id_xml_matiere,$id_xml_classe,$id_xml_groupe);
					}
				}
			}

		foreach($this->emp_sts->xpath('//GROUPE') as $infos)
			{
			$classes = $infos->DIVISIONS_APPARTENANCE->DIVISION_APPARTENANCE; //classes concernés
			$services = $infos->SERVICES->SERVICE; //détails des heures de cours avec matière et enseignant concerné
			
			//booléens
			$test_id = isset($infos["CODE"]) && !empty($infos["CODE"]);
			$test_services = isset($services) && !empty($services);
			$test_classes = isset($classes) && count($classes)>0;
					
			//vérification
			if(!($test_id && $test_services && $test_classes))
				{
				$this->edt_errors++;
				continue;
				}

			//id du groupe
			$id_xml_groupe = (string)$infos["CODE"];
			
			//groupe (= 1 classe) ou gic (> 1 classe)
			$i = 0;
			$id_xml_classe = 0;
			foreach($classes as $classe) //recherche du nom de la première classe uniquement
				{
				if(!isset($classe["CODE"]) || empty($classe["CODE"])) continue;
				if($id_xml_classe==0) $id_xml_classe = (string)$classe["CODE"];
				$i++;
				}
			if($i==0) continue; //pas de classe associée avec un code lisible
			elseif($i>1) $id_xml_classe = 0; //regroupement, on annule la classe
			
			foreach($services as $service)
				{
				if(!isset($service["CODE_MATIERE"]) || empty($service["CODE_MATIERE"]))
					{
					$this->edt_errors++;
					continue;
					}
				
				$id_xml_matiere = (string)$service["CODE_MATIERE"];

				foreach($service->ENSEIGNANTS->ENSEIGNANT as $prof)
					{
					$cours = $prof->COURS_RATTACHES->COURS; //cours de l'enseignant
					
					$test_prof = isset($prof["ID"]) && !empty($prof["ID"]);
					$test_cours = isset($cours) && !empty($cours);
					
					if(!($test_prof && $test_cours))
						{
						$this->edt_errors++;
						continue;
						}
					
					$id_xml_prof = (string)$prof["ID"];
					
					//lecture et enregistrement des cours
					$this->setCours($cours,$id_xml_prof,$id_xml_matiere,$id_xml_classe,$id_xml_groupe);
					}
				}
			}
		}

	/**
	 * Enregistrer un cours dans $this->edt avec des données du cahier de textes
	 * Des données XML sont fournies et il faut retrouver les correspondances enregistrées
	 * Ne fait que comptabiliser les cours tant qu'on ne se trouve pas à l'étape de l'importation proprement dite "edt_import"
	 * 
	 * @param object  $cours          pour un prof dans une classe et pouvant contenir plusieurs séances
	 * @param string  $id_xml_prof    identifiant XML de l'enseignant
	 * @param string  $id_xml_matiere identifiant XML de la matière
	 * @param string  $id_xml_classe  identifiant XML de la classe
	 * @param string  $id_xml_groupe  identifiant XML du groupe
	 */
	public function setCours($cours,$id_xml_prof,$id_xml_matiere,$id_xml_classe,$id_xml_groupe)
		{
		if($this->step!="edt_import" || empty($this->alternances["id"])) //on compte, c'est tout
			{
			foreach($cours as $seance) $this->edt_compteur++;
			return true;
			}
		else
			{
			$id_cdt_prof = $this->getCdtId("profs",$id_xml_prof);
			if($id_cdt_prof==0) return false;
			
			$id_cdt_matiere = $this->getCdtId("matieres",$id_xml_matiere);
			
			if($id_xml_classe!==0) //classe ou groupe
				{
				$id_cdt_classe = $this->getCdtId("classes",$id_xml_classe);
				$id_cdt_gic = 0;
				}
			else //regroupement
				{
				$id_cdt_classe = 0;
				$id_xml_gic = $id_xml_groupe.$id_xml_matiere.$id_xml_prof;
				$id_cdt_gic = $this->getCdtId("gic",$id_xml_gic);
				}
				
			if($id_cdt_gic===0 && $id_xml_groupe>0) //uniquement si groupe
				{
				$id_cdt_groupe = $this->getCdtId("groupes",$id_xml_groupe);
				$nom_groupe = $this->getCdtData("groupes",$id_cdt_groupe,"nom","Groupe Réduit");
				}
			else $nom_groupe = "Classe entière";
			
			
			foreach($cours as $seance)
				{
				$alternance = (string)$seance->CODE_ALTERNANCE;
				$jour = (int)$seance->JOUR;
				$heure_debut = (string)$seance->HEURE_DEBUT;
				$duree = (string)$seance->DUREE;
				$test_alternance = isset($alternance) && !empty($alternance);
				$test_jour = isset($jour) && !empty($jour);
				$test_heure = isset($heure_debut) && !empty($heure_debut);
				$test_duree = isset($duree) && !empty($duree);
				if( !($test_alternance && $test_jour && $test_heure && $test_duree && in_array($alternance,$this->alternances["id"]) && array_key_exists($jour,$this->jours)) ) continue;
				$periode = $this->getXmlData("alternances",$alternance,"type");
				if(!array_key_exists($periode,$this->semaines)) continue; //période "X" ne devant pas être pris en compte
				$periode = $this->semaines[$periode];
				$cdt_debut = substr($heure_debut,0,2)."h".substr($heure_debut,2,2);
				$cdt_duree = substr($duree,0,2)."h".substr($duree,2,2);
				$heure_debut = intval(substr($heure_debut,0,2))*60+intval(substr($heure_debut,2,2)); //en minutes
				$duree = intval(substr($duree,0,2))*60+intval(substr($duree,2,2)); //en minutes
				$heure_fin = $heure_debut+$duree;
				$heure_fin_h = floor($heure_fin/60);
				$heure_fin_m = $heure_fin%60;
				$cdt_fin = ($heure_fin_h<10 ? "0" : "").$heure_fin_h."h".($heure_fin_m<10 ? "0" : "").$heure_fin_m;
				
				$first = $this->getXmlData("alternances",$alternance,"first");
				$last = $this->getXmlData("alternances",$alternance,"last");
				
				$cdt_position = $this->getPosition($heure_debut);
				
				$this->edt[$id_cdt_prof][$jour][$cdt_position][] = array($id_cdt_matiere,$id_cdt_classe,$id_cdt_gic,$nom_groupe,$periode,$cdt_debut,$cdt_fin,$cdt_duree,$first,$last);
				$this->edt_compteur++;
				}
			}
		}

	/**
	 * Suppression de tout ce qui est à zéro en session, donc à ne pas prendre en compte
	 * Méthode finalement non utilisée...
	 */
	public function cleanSession()
		{
		foreach($_SESSION[$this->sessname] as $step)
			{
			foreach($step as $id_xml=>$id_cdt)
				{
				if($id_cdt=="0") unset($_SESSION[$this->sessname][$step][$id_xml]);
				}
			}
		}

	/**
	 * Lecture des données présentes dans le cahier de textes
	 * Si emp_sts : on récupère systématiquement toutes les données
	 * 
	 * @param string $type ce qui doit être lu
	 */
	public function getCdt($type="all")
		{
		if($this->emp_sts) $type="all"; //on force
		if($type=="all" || $type=="profs") $this->getProfs();
		if($type=="all" || $type=="matieres") $this->getMatieres();
		if($type=="all" || $type=="classes") $this->getClasses();
		if($type=="all" || $type=="gic") $this->getGic();
		if($type=="all" || $type=="groupes") $this->getGroupes();
		if($type=="all" || $type=="horaires") $this->getHoraires();
		}

	/**
	 * Récupération des utilisateurs du cahier de textes dans $this->cdt_profs
	 */
	public function getProfs()
		{
		//tableau réinitialisé si besoin
		if(!empty($this->cdt_profs["id"])) $this->cleanArray("cdt_profs");
		
		$n = 0;
		$query = "SELECT `ID_prof`,`nom_prof`,`identite`,`droits` FROM `cdt_prof`";
		$this->query($query);
		while($row = @mysqli_fetch_row($this->requete))
			{
			$id = intval($row[0]);
			$login = $row[1];
			$nom = $row[2];
			$droits = $row[3];
			$this->cdt_profs["id"][$n] = $id;
			$this->cdt_profs["login"][$n] = $login;
			$this->cdt_profs["nom"][$n] = $nom;
			$this->cdt_profs["statut"][$n] = $droits;
			$n++;
			}
		}

	/**
	 * Récupération des matières du cahier de textes dans $this->cdt_matieres
	 */
	public function getMatieres()
		{
		//tableau réinitialisé si besoin
		if(!empty($this->cdt_matieres["id"])) $this->cleanArray("cdt_matieres");
		
		$n = 0;
		$query = "SELECT `ID_matiere`,`code_matiere`,`nom_matiere` FROM `cdt_matiere`";
		$this->query($query);
		while($row = @mysqli_fetch_row($this->requete))
			{
			$id = intval($row[0]);
			$code = $row[1];
			$nom = $row[2];
			$this->cdt_matieres["id"][$n] = $id;
			$this->cdt_matieres["code"][$n] = $code;
			$this->cdt_matieres["nom"][$n] = strtolower($nom); //nécessaire pour les comparaisons
			$n++;
			}
		}

	/**
	 * Récupération des classes du cahier de textes dans $this->cdt_classes
	 */
	public function getClasses()
		{
		//tableau réinitialisé si besoin
		if(!empty($this->cdt_classes["id"])) $this->cleanArray("cdt_classes");
	
		$n = 0;
		$query = "SELECT `ID_classe`,`code_classe`,`nom_classe`,`passe_classe` FROM `cdt_classe`";
		$this->query($query);
		while($row = @mysqli_fetch_row($this->requete))
			{
			$id = intval($row[0]);
			$code = $row[1];
			$nom = $row[2];
			$pwd = $row[3];
			$this->cdt_classes["id"][$n] = $id;
			$this->cdt_classes["code"][$n] = $code;
			$this->cdt_classes["nom"][$n] = strtolower($nom); //nécessaire pour les comparaisons
			if(isset($pwd) && !empty($pwd)) $this->cdt_classes["pwd"][$n] = $pwd;
			$n++;
			}
		}

	/**
	 * Récupération des regroupements du cahier de textes dans $this->cdt_gic
	 */
	public function getGic()
		{
		//tableau réinitialisé si besoin
		if(!empty($this->cdt_gic["id"])) $this->cleanArray("cdt_gic");

		$n = 0;
		//$query = "SELECT `ID_gic`,`code_gic`,`nom_gic`,`prof_ID`,`classe_ID` FROM `cdt_groupe_interclasses`";
		$query = "
		SELECT `ID_gic`,`code_gic`,`nom_gic`,`prof_ID`,`classe_ID` FROM `cdt_groupe_interclasses` AS `gi`
		LEFT JOIN `cdt_groupe_interclasses_classe` AS `gic` ON `gic`.`gic_ID` = `gi`.`ID_gic`
		ORDER BY `gi`.`ID_gic`;";
		$this->query($query);
		while($row = @mysqli_fetch_row($this->requete))
			{
			$id = intval($row[0]);
			$code = $row[1];
			$nom = $row[2];
			$userid = $row[3];
			$classeid = intval($row[4]);
			if(isset($this->cdt_gic["id"]) && in_array($id,$this->cdt_gic["id"]) && !empty($classeid))
				{
				$key = array_search($id,$this->cdt_gic["id"]);
				$this->cdt_gic["classes"][$key][] = $classeid;
				}
			else
				{
				$this->cdt_gic["id"][$n] = $id;
				$this->cdt_gic["code"][$n] = $code;
				$this->cdt_gic["nom"][$n] = strtolower($nom); //nécessaire pour les comparaisons
				$this->cdt_gic["prof"][$n] = $userid;
				if(!empty($classeid)) $this->cdt_gic["classes"][$n][] = $classeid;
				$n++;
				}			
			}
		}

	/**
	 * Récupération des groupes du cahier de textes dans $this->cdt_groupes
	 */
	public function getGroupes()
		{
		//tableau réinitialisé si besoin
		if(!empty($this->cdt_groupes["id"])) $this->cleanArray("cdt_groupes");
	
		$n = 0;
		$query = "SELECT `ID_groupe`,`groupe`,`code_groupe` FROM `cdt_groupe`";
		$this->query($query);
		while($row = @mysqli_fetch_row($this->requete))
			{
			$id = intval($row[0]);
			$nom = $row[1];
			$this->cdt_groupes["code"][$n] = $row[2];
			$this->cdt_groupes["id"][$n] = $id;
			$this->cdt_groupes["nom"][$n] = strtolower($nom); //nécessaire pour les comparaisons
			$test_nom = $this->codeForm($nom);
			$n++;
			}
		}
	
	/**
	 * Récupération des plages horaires du cahier de textes dans $this->cdt_horaires
	 * On utilise les minutes pour comparaison
	 */
	public function getHoraires()
		{
		//tableau réinitialisé si besoin
		if(!empty($this->cdt_horaires["id"])) $this->cleanArray("cdt_horaires");
	
		$n = 0;
		$query = "SELECT `ID_plage`,`h1`,`mn1`,`h2`,`mn2` FROM `cdt_plages_horaires`";
		$this->query($query);
		while($row = @mysqli_fetch_row($this->requete))
			{
			$this->cdt_horaires["id"][$n] = intval($row[0]);
			$this->cdt_horaires["start"][$n] = intval($row[1])*60+intval($row[2]);
			$this->cdt_horaires["end"][$n] = intval($row[3])*60+intval($row[4]);
			$n++;
			}
		}

	/**
	 * Affichage des tableaux de données provenant des fichiers XML
	 */	
	public function displayXML()
		{
		$elements = array("matieres"=>"mati&egrave;res","classes"=>"classes","gic"=>"regroupements","users"=>"individus");
		
		foreach($elements as $type=>$value)
			{
			if(isset($this->$type) && !empty($this->$type))
				{
				$nb_erreurs = $type."_errors";
				echo "<h2>".count($this->{$type}["id"])." ".$value." (".($this->$nb_erreurs>0 ? $this->$nb_erreurs." erreurs" : "aucune erreur").")</h2>";
				echo "<pre>";
				print_r($this->$type);
				echo "</pre>";
				}
			else echo "<h2>La liste des ".$value." est vide ou inexistante.</h2>";	
			}	
		}

	/**
	 * Affichage des données de correspondances mises en session
	 */	
	public function displaySession()
		{
		if($this->emp_sts)
			{
			echo "<h2>Session \"".$this->sessname."\"</h2>";
			echo "<pre>";
			print_r($_SESSION[$this->sessname]);
			echo "</pre>";
			}		
		}

	/**
	 * Affichage d'un tableau quelconque
	 * 
	 * @param array  $tab   tableau à afficher
	 * @param string $title titre à afficher
	 */	
	public function displayArray($tab,$title="tableau")
		{
		echo "<h2>".$title."</h2>";
		echo "<pre>";
		print_r($tab);
		echo "</pre>";			
		}

	/**
	 * Suppression des fichiers XML
	 */	
	public function unlinkXML()
		{
		if(is_file($this->to_dir.$this->filenames["sts_emp"])) unlink($this->to_dir.$this->filenames["sts_emp"]);
		if(is_file($this->to_dir.$this->filenames["emp_sts"])) unlink($this->to_dir.$this->filenames["emp_sts"]);
		}

	/**
	 * Mise en forme pour la codification
	 * Ils sont utilisés comme identifiant pour les formulaires et sont ceux mis en base dans le cahier de textes
	 * 
	 * @param string $code valeur à mettre en forme
	 */		
	public function codeForm($code)
		{
		$code = strtolower(trim($code)); //en minuscules
		$code = strtr($code,'ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ','aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn');
		$code = preg_replace('/[^a-z0-9]/','',$code); //ne contenant que des caractères alphanumériques
		$taille = strlen($code);
		if($taille>19) $code = substr($code,0,19); //limiter à 20 caractères en base et un caractère libre pour les doublons éventuels
		return $code;
		}

	/**
	 * Mise en forme pour la codification d'une classe
	 *
	 * nécessite une codage particulier sous EnvOLE relatif à la façon de faire du scribe
	 * voir paquet scribe-backend : scribe/eoletools.py (replace_more_cars puis ok_groupe)
	 * 
	 * @param string $code valeur à mettre en forme
	 */		
	public function classeForm($code)
		{
		$code = strtolower(utf8_decode(trim($code)));
		$code = strtr($code,'ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ','aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn');
		list($code) = explode("(",$code);
		list($code) = explode(",",$code);
		$code = str_replace(' ','',$code); //espaces
		$code = str_replace('*','e',$code); //étoile (ex: mp*)
		$code = str_replace('.','',$code); //point
		$code = str_replace(':','',$code); //2points
		$code = str_replace(';','',$code); //point-virgule
		$code = str_replace('=','',$code); //égal
		$code = str_replace('"','',$code); //double-quotes
		$code = str_replace("'",'',$code); //apostrophes
		$code = str_replace('$','',$code); //dollar
		$code = str_replace('+','',$code); //plus
		$code = str_replace(')','',$code); //parenthèse fermante
		if($this->useEnvole) if(preg_match("/^[0-9]+$/",$code)) $code = "c".$code;
		if(strlen($code)>20) $code = substr($code,0,20);
		return $code;
		}

	/**
	 * Mise en forme des chaînes de caractères
	 * Surtout pour le décodage car les XML sont parsés automatiquement en utf-8
	 * 
	 * @param string  $string chaîne à décoder
	 * @param boolean $lower  si mise en minuscules désirée
	 */	
	public function stringForm($string,$lower=true)
		{
		$new_string = utf8_decode(trim($string));
		//bricolage car si fichier XML déclaré ISO et pourtant codé en UTF-8, la procédure simplexml_load_file() code une deuxième en UTF-8 ce qu'elle renvoit...
		if(preg_match("/Ã/",$new_string)) $new_string = utf8_decode($new_string);
		return $lower ? strtolower($new_string) : $new_string;
		}

	/**
	 * Obtention d'un login
	 * La mise en forme des parties du login est faite en amont (minuscules...) car on ne sait pas d'avance si $debut/$fin sont des noms/prénoms
	 * En cas de login déjà donné, le suivant reçoit le suffixe 1, etc...
	 * 
	 * @param  string  $debut première partie du login
	 * @param  string  $fin   seconde partie du login
	 * @param  int     $d     longueur à utiliser dans $debut
	 * @param  int     $f     longueur à utiliser dans $fin
	 * @param  boolean $point si les 2 parties sont à séparer par un point
	 * @return string  le login obtenu
	 */		
	public function getLogin($debut,$fin,$d=0,$f=0,$point=true)
		{
		$maxlength = 19;
		$d = intval($d);
		$f = intval($f);
		if($d>0) $debut = substr($debut,0,$d);
		if($f>0) $fin = substr($fin,0,$f);
		$login = $debut.($point ? "." : "").$fin;
		if(strlen($login)>$maxlength) $login = substr($login,0,$maxlength);
		if(array_key_exists($login,$this->login_list)) $login .= ++$this->login_list[$login];
		else $this->login_list[$login] = 0;
		return $login;
		}

	/**
	 * Mise en forme d'un nom de famille
	 * 
	 * @param  string $string un nom
	 * @return string le nom modifié
	 */	
	public function getCleanName($string)
		{
		$string = strtolower($string);
		$string = str_replace("'","",$string);
		$particules = array("de","du","dela","dos","di","es","el","le","la","da","van","ben","saint","ez","ait");
		foreach($particules as $p) $string = preg_replace("/^".$p."( |-)/",$p,$string); //coller la particule pour ne pas récupérer qu'elle
		$tab = preg_split ('/ |-/',$string); //on ne conserve que la première partie
		return $this->getCleanElement($tab[0]);
		}

	/**
	 * Mise en forme d'un prénom
	 * 
	 * @param  string $string un prénom
	 * @return string le prénom modifié
	 */	
	public function getCleanFirstname($string)
		{
		$string = strtolower($string);
		$tab = preg_split ('/ /',$string); //on ne conserve que la première partie
		return $this->getCleanElement($tab[0]);
		}
		
	/**
	 * Remplacement des caractères problématiques
	 * 
	 * @param  string $string la chaîne de caractères à traiter
	 * @return string le chaîne modifiée
	 */	
	public function getCleanElement($string)
		{
		$Caracs = array(
			"¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A",
			"Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A", 
			"Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E", 
			"Ê" => "E", "Ë" => "E", "Ì" => "I", "Í" => "I", 
			"Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N", 
			"Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O", 	
			"Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U", 
			"Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
			"à" => "a", "á" => "a", "â" => "a", "ã" => "a",
			"ä" => "a", "å" => "a", "æ" => "a", "ç" => "c", 
			"è" => "e", "é" => "e", "ê" => "e", "ë" => "e", 
			"ì" => "i", "í" => "i", "î" => "i", "ï" => "i", 
			"ð" => "o", "ñ" => "n", "ò" => "o", "ó" => "o", 
			"ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o", 
			"ù" => "u", "ú" => "u", "û" => "u", "ü" => "u", 
			"ý" => "y", "ÿ" => "y", "~B"=> "e");
		$string = strtr($string,$Caracs); //remplacements des caractères accentués
		$string =  preg_replace("/[^a-z]/i","",$string); //on supprime tout caractère gênant qui resterait
		return $string;
		}

	/**
	 * Récupérer une variable provenant d'un formulaire
	 * 
	 * @param  string $data nom de la variable
	 * @return string la valeur de la variable
	 */	
	public function getPost($data)
		{
		if(isset($_POST[$data])) return get_magic_quotes_gpc() ? stripslashes(trim($_POST[$data])) : trim($_POST[$data]);
		else return "";
		}

	/**
	 * Connexion au LDAP - prévu pour EnvOLE
	 * 
	 * @return resource la connexion
	 */	
	public function ldapConnexion()
		{
		if(!function_exists("ldap_connect")) $this->death("La version de PHP utilis&eacute;e ne supporte pas les connexions &agrave; un annuaire LDAP.");
		$ds = @ldap_connect ($this->ldap_server,$this->ldap_port);
		if($ds)
			{
			if($this->set_ldap_version) ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,$this->ldap_version);
			$r = @ldap_bind($ds);
			if(!$r) $this->death("Impossible de s&rsquo;authentifier sur l&rsquo;annuaire LDAP.");
			}
		else $this->death("Erreur de connexion sur l&rsquo;annuaire LDAP.");
		return $ds;
		}

	/**
	 * Recherche d'un login dans le LDAP - prévu pour EnvOLE
	 * Elle se fait à partir de l'identité et si besoin de la date de naissance de l'individu
	 * 
	 * @param  string $identite forme "prenom nom"
	 * @param  string $naissance 
	 * @return string le login trouvé (boolean false sinon)
	 */	
	public function ldapSearchLogin($identite,$naissance)
		{
		$ds = $this->ldapConnexion();

		//méthode utilisée au départ mais peu fiable car nom et prénom ont subi un traitement lors de l'importation des comptes dans l'EAD du scribe
		//$sr=ldap_search($ds,"o=gouv,c=fr","(&(sn=$nom)(givenname=$prenom))");
		//on utilise donc l'identité complète qui semble être présente dans l'annuaire de l'EAD dans le format d'origine
		$sr = ldap_search($ds,$this->ldap_base_dn,"(&(cn=".$identite.")".$this->ldap_user_filter.")",$this->ldap_user_attr);
		
		$info = ldap_get_entries($ds,$sr);
		
		if($info['count']<=0) $result = false; //Individu inconnu
		elseif($info['count']>1) //Doublons, essayons d'identifier l'utilisateur en ajoutant la date de naissance fournie
			{
			$sr2 = ldap_search($ds,$this->ldap_base_dn,"(&(cn=".$identite.")(dateNaissance=".$naissance.")".$this->ldap_user_filter.")",$this->ldap_user_attr);
			$info2 = ldap_get_entries($ds,$sr2);
			if($info2['count']<=0 || $info2['count']>1 )$result = false;
			else $result = $info2[0]["uid"][0];
			}
		else $result = $info[0]["uid"][0];
		@ldap_free_result($sr);
		@ldap_close($ds);
		return $result;
		}

	/**
	 * Vérification de l'existence d'un login dans le LDAP - prévu pour EnvOLE
	 * 
	 * @param  string  $login le login à vérifier
	 * @return boolean existence
	 */	
	public function ldapCheckLogin($login)
		{
		$ds = $this->ldapConnexion();
		$sr = ldap_search($ds,"o=gouv,c=fr","(&(uid=".$login.")".$this->ldap_user_filter.")");
		$info = ldap_get_entries($ds,$sr);
		@ldap_free_result($sr);
		@ldap_close($ds);
		if($info['count']<=0) return false;
		else return true;
		}

	/**
	 * Vérification de l'existence d'une classe dans le LDAP - prévu pour EnvOLE
	 * 
	 * @param  string  $login la classe à vérifier
	 * @return boolean existence
	 */
	public function ldapCheckClasse($classe)
		{
		$ds = $this->ldapConnexion();
		$sr = ldap_search($ds,"o=gouv,c=fr","(&(cn=".$classe.")".$this->ldap_classe_filter.")");
		$info = ldap_get_entries($ds,$sr);
		@ldap_free_result($sr);
		@ldap_close($ds);
		if($info['count']<=0) return false;
		else return true;
		}

	/**
	 * Moyen utiliser pour avertir l'utilisateur en cours d'exécution du script
	 * Utiliser lors de l'insertion des emplois du temps (edt_import_process)
	 * S'emploie parallèlement à la fonction javascript du même nom
	 * 
	 * @param string $message texte à afficher
	 */	
	public function sendMessage($message)
		{
		echo "<script type=\"text/javascript\">sendMessage(\"".$message."\")</script>";
		flush();
		}

	/**
	 * Affichage de l'en-tête
	 * Contient tout le javascript utilisé pour le fonctionnement de la procédure
	 */	
	public function displayHeader()
		{
		$upload_text = $this->step_display["upload"][1];
		$uploads_text = $this->step_display["uploads"][1];
		echo
<<<HEADER
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
		<head>
<title>Cahier de textes</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<link media="screen" href="../styles/style_default.css" type="text/css" rel="stylesheet" />
		<link media="screen" href="../templates/default/header_footer.css" type="text/css" rel="stylesheet" />
		<style>
		#div_import {text-align:left; padding:10px 40px;}
		#div_import table td {padding:5px;}
		#tableau {margin:0px auto; width:600px;}
		#tableau select {display:none; text-align:left; border:none; width:100%;}
		#tableau input.text {text-align:center; border:none; width:95%; cursor:pointer;}
		#edt_synthese {background:#ffffff; margin:-20px auto 0px; padding:10px 0px;}
		#message {font-weight:bold; color:#009933; padding:20px 0px; display:none;}
		table.recapitulatif {font-size:0.8em; margin:0px auto 10px; width:90%;}
		h2.intitule {font-weight:bold; color:#990099; text-align:center; margin:10px auto 0px; padding:5px; width:90%; background:#ffccff;}
		ul, ol {margin:5px; padding-left:30px;}
		li {margin:2px;}
		fieldset {border:none;}
		.commentaire
			{
			font-size:1.1em;
			text-align:left;
			margin:20px;
			padding:5px 10px;
			border:1px dashed #3054BF;
			color:#3054BF;
			background:#BFE5FF;
			font-family:Georgia,'Times New Roman',serif;
			}
		.commentaire a { color: #FF3300; }
		.commentaire a:hover { text-decoration: underline; }
		.succes {color:green; font-style:italic;}
		.echec {color:red; font-style:italic;}
		.color0 {background:#FFDDAA;}
		.color1 {background:#99DDFF;}
		.styleI {font-weight:bold; color:#3054BF;}
		.styleP {font-weight:bold; color:#990099;}
		.styleR {font-weight:bold; color:#009933;}
		.styleN {font-weight:bold; color:#000000;}
		.styleE {font-weight:bold; color:#EE0000;}
		.underline {text-decoration:underline;}
		</style>
		<script type="text/javascript" language="javascript">
		function activeInput(form)
			{
			if(form.type_importation[1].checked)
				{
				document.getElementById("file_emp_sts").style.display = "block";
				form.soumettre.value = "{$uploads_text}";
				}
			else
				{
				document.getElementById("file_emp_sts").style.display = "none";
				form.soumettre.value = "{$upload_text}";
				}
			}

		function confirmInput(form)
			{
			var sts_emp_regexp = /sts_emp_[0-9a-z]{8}_[0-9]{4}\.xml/i;
			var emp_sts_regexp = /emp_sts_[0-9a-z]{8}_[0-9]{4}\.xml/i;
			var sts_emp_file = form.sts_emp.value;
			var emp_sts_file = form.emp_sts.value;
			
			if(!sts_emp_regexp.test(sts_emp_file))
				{
				alert('Le premier fichier doit être de la forme "sts_emp_RNE_annee.xml".');
				return false;
				}

			if(form.type_importation[1].checked)
				{
				if(!emp_sts_regexp.test(emp_sts_file))
					{
					alert('Le second fichier doit être de la forme "emp_sts_RNE_annee.xml".');
					return false;
					}
				}
			return true;
			}

		function selectAll()
			{
			var obj = document.getElementById('cb_all');
			var cb_regexp = /^cb_/i;
			var f = obj.form;
			var ischecked = obj.checked;
			var input_liste = f.getElementsByTagName('input');
			var the_input = null;
			for(var i=1; i<input_liste.length; i++)
				{
				the_input = input_liste[i];
				if(the_input.type=="checkbox" && the_input.name!="cb_all" && cb_regexp.test(the_input.name)) the_input.checked = ischecked;
				}
			
			//changement d'état si liste de type "ref_" existantes
			var select_regexp = /^ref_/i;
			var select_liste = f.getElementsByTagName('select');
			var the_select = null;
			var the_input = null;
			var the_pwd = null;
			var nom = "";
			var code = "";
			for(var i=0; i<select_liste.length; i++)
				{
				the_select = select_liste[i];
				nom = the_select.name;
				if(select_regexp.test(nom))
					{
					code = nom.replace(select_regexp,"");
					eval("the_input = f.nom_"+code+";");
					eval("the_pwd = f.pwd_"+code+";");
					if(the_input && the_select)
						{
						if(ischecked)
							{
							the_select.style.display = "none";
							the_select.style.border = "none";
							the_input.style.display = "block";
							if(the_pwd) the_pwd.style.display = "block";
							}
						else
							{
							the_select.style.display = "block";
							the_input.style.display = "none";
							if(the_pwd) the_pwd.style.display = "none";
							}
						}
					}	
				}		
			}

		function selectOne(obj)
			{
			var cb_regexp = /^cb_/i;
			var f = obj.form;
			var input_liste = f.getElementsByTagName('input');
			if(f.cb_all.checked==true && obj.checked==false) f.cb_all.checked = false;
			else if(f.cb_all.checked==false && obj.checked==true)
				{
				var allchecked = true;
				var the_input = null;
				for(var i=1; i<input_liste.length; i++)
					{
					the_input = input_liste[i];
					if(the_input.type=="checkbox" && the_input.name!="cb_all" && cb_regexp.test(the_input.name) && the_input.checked==false)
						{
						allchecked = false;
						break;
						}
					}
				if(allchecked==true) f.cb_all.checked = true;
				}
			}

		function changeInput(obj)
			{
			var cb_regexp = /^cb_/i;
			var pwd_regexp = /^pwd_/i;
			var nom = obj.name;
			if(cb_regexp.test(nom))
				{
				var code = nom.replace(cb_regexp,"");
				eval("var the_input = obj.form.nom_"+code+"; var the_select = obj.form.ref_"+code+"; var the_pwd = obj.form.pwd_"+code+";");
				if(the_input && the_select)
					{
					if(obj.checked==true)
						{
						the_select.style.display = "none";
						the_input.style.display = "block";
						if(the_pwd) the_pwd.style.display = "block";
						}
					else
						{
						the_select.style.display = "block";
						the_input.style.display = "none";
						if(the_pwd) the_pwd.style.display = "none";
						}
					}
				else
					{
					if(obj.checked==true) obj.checked = false;
					else obj.checked = true;
					alert("Problème javascript : impossible de modifier ce paramètre.\\nRechargez la page et signalez ce problème s'il persiste.");
					}
				}
			else alert(search[0]);
			}

		function checkSaisie(form)
			{
			return true; //la valeur 0 correspond désormais à une demande de non prise en compte et non un choix non fait
			/*
			var select_regexp = /^ref_/i;
			var select_liste = form.getElementsByTagName('select');
			var the_select = null;
			var is_ok = true;
			for(var i=0; i<select_liste.length; i++)
				{
				the_select = select_liste[i];
				if(select_regexp.test(the_select.name) && the_select.style.display=="block" && the_select.value=="0")
					{
					the_select.style.border = "1px solid #ff0000";
					is_ok = false;
					}	
				}
			if(is_ok) return true;
			else
				{
				alert("Certaines valeurs ne sont pas renseignées.\\nVeuillez compléter le formulaire avant de l'envoyer.");
				return false;
				}
			*/
			}

		function checkEdt(form)
			{
			var select_regexp = /^periode_/i;
			var date_regexp = /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/i;
			var select_liste = form.getElementsByTagName('select');
			var the_select = null;
			var is_H = 0;
			var is_A = 0;
			var is_B = 0;
			var is_S1 = 0;
			var is_S2 = 0;
			var is_ok = false;
			
			for(var i=0; i<select_liste.length; i++)
				{
				the_select = select_liste[i];
				if(select_regexp.test(the_select.name) && the_select.value!="X")
					{
					switch(the_select.value)
						{
						case "H":
						is_H++;
						break;
						case "A":
						is_A++;
						break;
						case "B":
						is_B++;
						break;
						case "S1":
						is_S1++;
						break;
						case "S2":
						is_S2++;
						break;
						}
					}
				}
			if(is_H==0) alert("Veuillez sélectionner une période \"Année complète\"");
			else if(is_A==0) alert("Veuillez sélectionner une période \"Semaines A\"");
			else if(is_B==0) alert("Veuillez sélectionner une période \"Semaines B\"");
			else if(is_H>1 || is_A>1 || is_B>1 || is_S1>1 || is_S2>1) alert("Veuillez sélectionner une seule fois maximum chaque type de période.");
			else if(!date_regexp.test(form.start_date.value)) { alert("La date doit être sous la forme \"jj/mm/aaaa\"."); form.start_date.focus();}
			else is_ok = true;

			return is_ok;
			}

		function seeList(the_li)
			{
			the_ul = the_li.getElementsByTagName('ul')[0];
			if(the_ul) the_ul.style.display = (the_ul.style.display=="block") ? "none" : "block";
			}

		function sendMessage(text)
			{
			var content = document.getElementById("message");
			if(text=="") content.style.display = "none";
			else
				{
				content.style.display = "block";
				content.innerHTML = text;
				}
			}

		function setReinit()
			{
			return confirm("Êtes-vous sûr de vouloir reprendre l'importation à la sélection des individus ?");
			}
		</script>
		</head>
		<body>
		<div id="page">
HEADER;
		$header_description = "Importation des enseignants, mati&egrave;res, classes et emplois du temps";
		require_once "../templates/default/header.php";
		echo "<p id=\"message\"></p>"; //pour sendmessage()
		}

	/**
	 * Affichage du pied de page
	 */	
	public function displayFooter()
		{
		//pour forcer le clique sur le bouton à la fin de l'importation (plus propre car suppression des fichiers utilisés)
		if($this->step_next=="end") $retour = "";
		else $retour = "<p><a href=\"index.php\">Retour au menu administrateur</a></p>";
		echo
<<<FOOTER
		<div id="footer">
		{$retour}
		<p><a href="mailto:christophe.deseure@ac-creteil.fr">rectorat de Cr&eacute;teil</a> - version 1 - décembre 2009</p>
		</div>
		</div>
		</body>
		</html>
FOOTER;
		}
	}

//lancement de la procédure
$import = new ImportSconet();
?>
