<?php  

require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

session_start();
unset($_SESSION['identite']);
unset($_SESSION['nom_prof']);
unset($_SESSION['ID_prof']);
unset($_SESSION['email']);
unset($_SESSION['droits']);
unset($_SESSION['publier_cdt']);
unset($_SESSION['publier_travail']);
unset($_SESSION['date_visa']);
unset($_SESSION['copie']);
unset($_SESSION['coller']);
unset($_SESSION['stop_cdt']);
unset($_SESSION['semdate']);
unset($_SESSION['consultation']);
unset($_SESSION['last_access']);
unset($_SESSION['ipaddr']);
unset($_SESSION['path_fichier_perso']);
unset($_SESSION['xinha_editlatex']);
unset($_SESSION['xinha_equation']);
unset($_SESSION['xinha_stylist']);

unset($_SESSION['nom_etab']);
unset($_SESSION['url_etab']);
unset($_SESSION['url_logo_etab']);

unset($_SESSION['libelle_devoir']);
unset($_SESSION['visa_stop_edition']);
unset($_SESSION['session_timeout']);

unset($_SESSION['url_deconnecte_eleve']);
unset($_SESSION['url_deconnecte_prof']);
unset($_SESSION['module_absence']);
unset($_SESSION['edt_modif_mat']);
unset($_SESSION['affichage_compteur']);

unset($_SESSION['acces_rapide']);
unset($_SESSION['afficher_messages']);
unset($_SESSION['masque_edt_cloture']);
unset($_SESSION['libelle_semaine']);

unset($_SESSION['mobile_browser']);
unset($_SESSION['site_ferme']);

unset($_SESSION['devoir_planif']);

unset($_SESSION['prof_mess_pp']);
unset($_SESSION['prof_mess_all']);
unset($_SESSION['id_etat']);

unset($_SESSION['ipad']);
unset($_SESSION['affiche_xinha']);
unset($_SESSION['type_affich']);

if (isset($_SESSION['ecart_realise'])){unset($_SESSION['ecart_realise']);};


//lecture de la table cdt_params (nom etablissement, url ..) et declaration de variables de Sessions
require_once('../inc/sessions_params.php'); 

if ($_SESSION['site_ferme']!='Non')
{
echo "<div>\n";
echo "<p><font color=#FF0000><strong>L'application Cahier de textes est en cours de maintenance et temporairement inaccessible.<br />Veuillez nous excuser de ce d&eacute;rangement et r&eacute;essayer de vous connecter ult&eacute;rieurement.</strong></font></p>\n";
echo "</div>\n";
}

else if (isset($_POST['classe_ID']))

{
if ($_POST['classe_ID']<>'value')
{ 	
$choix_RsClasse = "0";
if (isset($_POST['classe_ID'])) {
  $choix_RsClasse = (get_magic_quotes_gpc()) ? $_POST['classe_ID'] : addslashes($_POST['classe_ID']);
}
require_once('../Connections/conn_cahier_de_texte.php'); 
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE cdt_classe.ID_classe=%u", GetSQLValueString($choix_RsClasse,"int"));

$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
if(!empty($row_RsClasse['passe_classe']) && $row_RsClasse['passe_classe']==md5($_POST['passe_c']))
		{
		
		$_SESSION['consultation']=$_POST['classe_ID'];
		$GoTo='log2_android.php?classe_ID='.strtr(GetSQLValueString($_POST['classe_ID'],"int"),$protect).'&mdp='.$_POST['passe_c'];	
		header(sprintf("Location: %s", $GoTo));} 
		else 
		{ $erreur1='Vous devez s&eacute;lectionner la classe et entrer le mot de passe';};
		};
		
}




mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);
                 



$page_accueil=1;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta content="yes" name="apple-mobile-web-app-capable" />
<meta content="index,follow" name="robots" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="pics/homescreen.png" rel="apple-touch-icon" />
<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />  

<link href="css/style.css" rel="stylesheet" media="screen" type="text/css" />
<script src="javascript/functions.js" type="text/javascript"></script>
<title>CDT &nbsp;<?php echo $_SESSION['nom_etab']?></title>
<meta name="keywords" content="Cahier de textes Pierre Lemaitre">
<meta name="description" content="Cahier de textes - Application d&eacute;velopp&eacute;e par Pierre Lemaitre - Saint-L&ocirc; ">
<link href="pics/startup.png" rel="apple-touch-startup-image" />

</head>

<body>
<div id="topbar">
	<div id="title"><?php echo $_SESSION['nom_etab']?></div>
</div>

  <?php
$cum='';
do { 
$cum=$cum.$row_RsClasse['passe_classe'];
} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse));
  $rows = mysqli_num_rows($RsClasse);
if($rows > 0) { mysqli_data_seek($RsClasse,0);}; 
?>
<div id="content">
		<form method="post" onLoad= "formfocus()" name="form3" action="log_android.php">
		<fieldset>
        <span class="graytitle">Espace parents et &eacute;l&egrave;ves</span>
		<ul class="pageitem"> 
        <li class="select">
			<select placeholder="classe" name="classe_ID" id="classe_ID"> 
			<option value="value">S&eacute;lectionner votre classe</option>
            <?php while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)){ ?>
            <option value="<?php echo $row_RsClasse['ID_classe']?>"><?php echo $row_RsClasse['nom_classe']?></option>
              <?php	} ;
  $rows = mysqli_num_rows($RsClasse);
  if($rows > 0) { mysqli_data_seek($RsClasse, 0); $row_RsClasse = mysqli_fetch_assoc($RsClasse); };
?>
			</select> 
          </li>
            <li class="bigfield">
			<input placeholder="<?php if ($cum<>''){echo 'Mot de passe';} ;?>" name="passe_c" <?php if ($cum<>''){echo 'type="password"';}else {echo 'type="hidden"';};?> /></li>

			 <li class="button">
			<input type="submit" value="Envoi" />
            </li>
		</ul>
		</fieldset></form>       
            
</div>


</body>

</html>
<?php
mysqli_free_result($RsClasse);


?>
