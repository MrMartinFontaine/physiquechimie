<?php 
//modifier pour chaque nouvelle version
$indice_version='5510'; 
$libelle_version='Version 5.5.1.0 Standard';
//---------------------------------

require_once('../Connections/conn_cahier_de_texte.php'); 
include "../authentification/authcheck.php"; 
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../inc/functions_inc.php');


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) {

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsparams3 = "SELECT param_val FROM cdt_params WHERE param_nom='ind_maj_base'";
$Rsparams3 = mysqli_query($conn_cahier_de_texte, $query_Rsparams3) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsparams3 = mysqli_fetch_assoc($Rsparams3);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsparams1 = "SELECT param_val FROM cdt_params WHERE param_nom='version'";
$Rsparams1 = mysqli_query($conn_cahier_de_texte, $query_Rsparams1) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsparams1 = mysqli_fetch_assoc($Rsparams1);

} else{?>
<a href="./misajour/maj306_exe.php">La table cdt_params est absente - Faire cette mise &agrave; jour</a>
<?php 
;};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsPublier2 = "SELECT date_maj FROM cdt_prof WHERE ID_prof=1 ";
$RsPublier2 = mysqli_query($conn_cahier_de_texte, $query_RsPublier2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsPublier2 = mysqli_fetch_assoc($RsPublier2);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_devoir_planif = "SELECT param_val FROM cdt_params WHERE param_nom='devoir_planif'";
$Rs_devoir_planif = mysqli_query($conn_cahier_de_texte, $query_Rs_devoir_planif) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_devoir_planif = mysqli_fetch_assoc($Rs_devoir_planif);
$totalRows_Rs_devoir_planif = mysqli_num_rows($Rs_devoir_planif);
if ($totalRows_Rs_devoir_planif>0) {$_SESSION['devoir_planif']=$row_Rs_devoir_planif['param_val']; } else {$_SESSION['devoir_planif']='Oui';};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='devoir_planif' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$dev_planif = $row[0];
mysqli_free_result($result_read);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='site_ferme' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$site_ferme = $row[0];
mysqli_free_result($result_read);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_debut_annee' LIMIT 1";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$date_debut_annee = $row[0];
mysqli_free_result($result_read);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_fin_annee' LIMIT 1";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$date_fin_annee = $row[0];
mysqli_free_result($result_read);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style70 {
	color: #CC0000;
	font-style: italic;
}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>

<div align="center">
    <?php 
$header_description="ADMINISTRATION";
require_once "../templates/default/header.php";
require_once ("../authentification/sessionVerif.php");
?>
<table width="100%" border="0">
  <tr>
    <td>

      <div align="center">
        <?php
/* Control version en ligne - L'acces a un autre serveur semble poser probleme chez certains.
error_reporting(E_ERROR); 
$f = file('http://www.etab.ac-caen.fr/bsauveur/cahier_de_texte/version.txt');
while ( list( $num_ligne, $ligne ) = each( $f ) ) {
    $versel = htmlspecialchars( $ligne );
 };
error_reporting(E_WARNING); 
*/

// Alerte presence du dossier install

    if (is_dir('../install/')) {echo '<br /> <span class="Style70">Votre dossier <b>install</b> est encore pr&eacute;sent. <br />Vous devez imp&eacute;rativement le supprimer ou le renommer pour s&eacute;curiser l\'application.</span> <br /> ';};

// Alerte si conn_cahier_de_texte.php est ouvert en ecriture
if (is_writable('../Connections/conn_cahier_de_texte.php')) {
   echo '<br /> <span class="Style70">Attention, le fichier <b>Connections/conn_cahier_de_texte.php</b> est accessible en &eacute;criture.<br />Il est imp&eacute;ratif de lui remettre l\'attribut lecture seule pour s&eacute;curiser l\'application <br />(Ne pas tenir compte de cela pour un h&eacute;bergement Free).</span> <br /> ';
} ;


//Affichage du compteur de consultations du cdt

if ((isset($_SESSION['affichage_compteur']))&&($_SESSION['affichage_compteur']=='Oui'))	{

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsCompt = "SELECT param_val FROM cdt_params WHERE param_nom='compteur'";
$RsCompt = mysqli_query($conn_cahier_de_texte, $query_RsCompt) or die(mysqli_error($conn_cahier_de_texte));
$row_RsCompt = mysqli_fetch_assoc($RsCompt);
$cpt = $row_RsCompt['param_val'];

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsdateRAZ = "SELECT param_val FROM cdt_params WHERE param_nom='date_raz_compteur'";
$RsdateRAZ = mysqli_query($conn_cahier_de_texte, $query_RsdateRAZ) or die(mysqli_error($conn_cahier_de_texte));
$row_RsdateRAZ = mysqli_fetch_assoc($RsdateRAZ);
$date_actu = $row_RsdateRAZ['param_val'];

$date_actu=substr($date_actu,6,2).'/'.substr($date_actu,4,2).'/'.substr($date_actu,0,4);

echo "<br><font size=\'1\'><i>Le cahier de textes a &eacute;t&eacute; consult&eacute; <b>$cpt</b> fois depuis le ".jour_semaine($date_actu)." $date_actu</i></font><br>";
        
};

echo '<br>Version PHP : '.phpversion().'    -    Version MYSQL : ', mysqli_get_server_info($conn_cahier_de_texte);
// Alerte si mise a jour de la base necessaire

if ((isset($row_Rsparams3['param_val'])) && ($row_Rsparams3['param_val']<$indice_version) ){ echo ' <br /> <br /><span class="Style70">Une mise &agrave; jour de la base de donn&eacute;es est n&eacute;cessaire <br />Une sauvegarde pr&eacute;alable est conseill&eacute;e (Voir menus ci-dessous)</span> <br /> <br />';};

if ((isset($_SESSION['site_ferme']))&&($_SESSION['site_ferme']=='Oui')){
?>
<br/><img src="../images/interdit.jpg" title="Site bloqu&eacute;" alt="Site bloqu&eacute;"><br/> 
L'acc&egrave;s &agrave; l'application est actuellement bloqu&eacute; pour les utilisateurs.<br/><?php }


?>
      </div></td>
    <td width="8%" valign="top"><div float="right"><a href="../index.php"><img src="../images/home-menu.gif" border="0" align="top"></a>&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
  </tr>
</table>  
  <br/>
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="5">
    <tr>
      <td width="50%"><div align="left" class="Style6">Gestion</div></td>
      <td><div align="left" class="Style6">Autres configurations</div></td>
    </tr>
    <tr>
      <td width="50%" valign="top"><p align="left"><a href="prof_ajout.php">Gestion des utilisateurs et mot de 
          passe Administrateur</a></p>
        <p align="left"><a href="classe_ajout.php">Gestion des classes</a></p>
        <p align="left"><a href="../vie_scolaire/niveau_classe_ajout.php">Gestion des niveaux de classes</a></p>
        <p align="left"><a href="matiere_ajout.php">Gestion des mati&egrave;res</a></p>
        <p align="left"><a href="groupe_ajout.php">Gestion des groupes au sein de la classe</a> </p>
        <p align="left"><a href="../inc/regroupement_liste.php">Gestion des regroupements de classes</a> </p>
        <p align="left"><a href="dates_annee_scol_param.php">Gestion des dates de d&eacute;but et fin de l'ann&eacute;e scolaire
		<?php 
		if ($date_debut_annee!=''){echo '('.substr($date_debut_annee,6,4).'-'.substr($date_fin_annee,6,4).')'; } else {echo '<span style="color: #FF0000">- NON DEFINI</span>';};?> </a></p>
        <p align="left"><a href="even_ajout.php">Gestion des dates de vacances </a></p>
        <p align="left"><a href="plages_horaires.php">Gestion des plages horaires </a></p>
        <p align="left"><a href="semaine_ab_menu.php">Gestion de la programmation des semaines en alternance </a></p>
	<?php	
	$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_debut_annee' LIMIT 1;";
	$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
	$row = mysqli_fetch_row($result_read);
	$date_debut_annee = substr($row[0],6,4).substr($row[0],3,2).substr($row[0],0,2);
	mysqli_free_result($result_read);
	$query_read = "SELECT s_code_date FROM `cdt_semaine_ab` ORDER BY `cdt_semaine_ab`.`s_code_date` ASC LIMIT 1";
	$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
	$row = mysqli_fetch_row($result_read);
	$date_debut_prog = $row[0]; // l'annee peut commencer en milieu de semaine
	mysqli_free_result($result_read);
	if ($date_debut_annee<$date_debut_prog){echo'<p class="Style70">La programmation des semaines en alternance semble incoh&eacute;rente avec les dates actuelles de votre ann&eacute;e scolaire</p>';};
	?>	
	
 		<p align="left"><a href="remplacement/remplacement.php">Gestion des remplacements</a></p>
 		<p align="left"><a href="even_projet_menu.php">Gestion des &eacute;v&egrave;nements, projets et actions p&eacute;dagogiques</a> </p>
 		<p align="left">&nbsp;</p>       
		<p align="left"><a href="./module_absence/module_absence.php">Gestion de la d&eacute;claration des &eacute;l&egrave;ves absents</a></p>      </td>
      <td valign="top"> <p align="left"><a href="parametrage_gen.php">Param&egrave;tres g&eacute;n&eacute;raux (Nom &eacute;tablissement, logo...)</a></p>
                <p class="Style70"><?php if ($_SESSION['nom_etab']=="") {echo "Pensez &agrave; renseigner le nom de votre &eacute;tablissement.";};?></p>
                <p class="Style70"><?php if ($_SESSION['url_etab']=="") {echo "Pensez &agrave; renseigner l'adresse Internet de votre &eacute;tablissement.";};?></p>
                <p align="left"><a href="xinha_conseils.php">Activer ou d&eacute;sactiver certains param&egrave;tres de l'&eacute;diteur Xinha</a></p>
        <p align="left"><a href="parametrage_latex.php">Param&eacute;trage du service Web LaTEX</a></p>
		<p align="left"><a href="archive_param.php">Acc&egrave;s &agrave; un ancien cahier de textes sauvegard&eacute; dans une autre base de donn&eacute;es
          <?php
//parametre autorisant l'acces ou non a un ancien cahier de textes
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='old_cdt_access' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access = $row[0];

if ($access=='Oui') { echo " (Oui)";}
else {echo " (Non)";};
?>
          </a><br>
        </p>
        <p align="left"><a href="pp_param.php">A propos des professeurs principaux</a></p>
		<p align="left"><a href="prof_mess_param.php">Autorisation de diffusion des messages par les enseignants</a></p>
        <p align="left"><a href="changer_theme.php">Changer de th&egrave;me de pr&eacute;sentation des menus</a></p>
                <p align="left"><a href="devoir_planif.php">D&eacute;sactiver la planification de devoirs  en dehors des heures de cours</a></p>
        <?php if ((isset($_SESSION['devoir_planif']))&&($_SESSION['devoir_planif']=='Oui')){ ?>
        <p align="left"><a href="libelle_devoirs.php">Libell&eacute; attribu&eacute; aux devoirs planifi&eacute;s hors heures de cours</a></p>
        <?php }; ?>

        <p align="left"><a href="modif_login.php">Modification par les utilisateurs de leur identifiant </a></p>
        <p align="left"><a href="modif_passe.php">Modification par les utilisateurs de leur mot de passe</a></p>
        <p align="left"><a href="facebook_icon.php">Visibilit&eacute; de l'icone facebook</a> </p>
        <p align="left"><a href="acces_inspection.php">Mise &agrave; disposition des cahiers aux corps d'inspection</a> </p>
        <p align="left"><a href="parametrage_even.php"></a> <a href="menu_deroul.php">Mode d'authentification sur la page d'accueil </a><br></td>
    </tr>
    <tr>
      <td valign="top"><div align="left" class="Style6">Importation</div></td>
      <td valign="top"><div align="left" class="Style6">Consultation - Information </div></td>
    </tr>
    <tr>
      <td valign="top"><p align="left"><a href="import_csv.php">Importation des utilisateurs depuis un fichier CSV ou txt</a></p>
        <p align="left"><a href="import_classe.php">Importation des classes depuis un fichier CSV ou txt</a> </p>
        <p align="left"><a href="import_matiere.php">Importation des mati&egrave;res depuis un fichier CSV ou txt</a> </p>
        <p align="left"><a href="import_sconet.php">Importation de donn&eacute;es depuis SCONET /STSWeb</a> </p>
      <p align="left"><a href="edt.php">Importation des emplois du temps depuis EDT ou UDT</a></p>
	  <p align="left"><a href="importfromedt_plage.php">Importation  de plages emplois du temps pr&eacute;-dat&eacute;es (txt/csv</a>)</p>
      <p align="left">&nbsp;</p></td>
      <td valign="top"><p align="left" ><a href="publication.php">Etat de la publications des enseignants</a> </p>
      <p align="left" ><a href="../enseignant/evenement_liste.php">Planning des &eacute;v&eacute;nements et actions p&eacute;dagogiques</a> </p>
      <p align="left" ><a href="../edt_eleve.php">Emploi du temps d'une classe</a></p>
      <p align="left" ><a href="../vie_scolaire/prof_principaux_liste.php?tri=pp">Liste des professeurs principaux d&eacute;clar&eacute;s</a></p>
      <p align="left" ><a href="../vie_scolaire/prof_liste.php">Liste de l'ensemble du personnel avec leur m&eacute;l</a></p>
      <p align="left" ><a href="http://www.etab.ac-caen.fr/bsauveur/cahier_de_texte/"></a><br>
      </p></td>
    </tr>
    <tr>
      <td valign="top"><div align="left" class="Style6">Outils statistiques </div></td>
      <td valign="top"><div align="left" class="Style6">Communication</div></td>
    </tr>
    <tr>
      <td valign="top"><p align="left"><a href="activation_compteur.php">Compteur de consultation : Activation - R&eacute;initialisation</a></p>
      <p align="left"><a href="statistiques_consultation.php">Statistiques de consultation par classe</a></p></td>
      <td valign="top"><p align="left"><a href="../vie_scolaire/message_ajout.php?tri=auteur">Diffusion d'un message aux &eacute;l&egrave;ves</a></p>
        <p align="left"><a href="../vie_scolaire/message_ajout_profs.php">Diffuser un message aux enseignants</a></p>
        <p align="left" ><a href="../vie_scolaire/message_ajout_profs.php?ppliste=1">Diffuser un message aux professeurs principaux</a><br>
      </p></td>
    </tr>
    <tr>
      <td width="50%" valign="top"><div align="left" class="Style6">Utilitaires &amp; Maintenance </div></td>
      <td valign="top"><div align="left" class="Style6"> Mise &agrave; jour </div></td>
    </tr>
    <tr>
      <td width="50%" valign="top">
        <p align="left"><a href="sauvegarde1.php">Sauvegarde</a>&nbsp;-
          <?php 
  $date_actu=substr($row_RsPublier2['date_maj'],8,2).'/'.substr($row_RsPublier2['date_maj'],5,2).'/'.substr($row_RsPublier2['date_maj'],0,4);
  if (substr($row_RsPublier2['date_maj'],8,2)=='00') {echo '<span class="Style70">Pas de sauvegarde</span>';} else {echo '<span class="Style70">Derni&egrave;re sauvegarde : '.jour_semaine($date_actu).' '.$date_actu.'</span>';};
?>
        </p>
<p align="left"><a href="archivage.php">Archivage ann&eacute;e &eacute;coul&eacute;e </a>
<?php
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$sql="SELECT param_val FROM cdt_params WHERE param_nom='Maj_Archives' LIMIT 1 ";
$MaJArchives=(mysqli_query($conn_cahier_de_texte, $sql)) or die('Erreur SQL !'.$sql.mysqli_error($conn_cahier_de_texte));
$row_MaJArchives = mysqli_fetch_assoc($MaJArchives);
if ($row_MaJArchives['param_val']=='Oui') {
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
     $query_archives = "SELECT NumArchive FROM `cdt_archive`";
	$archives = mysqli_query($conn_cahier_de_texte, $query_archives) or die(mysqli_error($conn_cahier_de_texte));
     $total_archives = mysqli_num_rows($archives);
     mysqli_free_result($archives);
     if ($total_archives==0){
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$insertionSQL = "UPDATE `cdt_params` SET `param_val` = 'Non' WHERE `param_nom` ='Maj_Archives'";
                $Arch = mysqli_query($conn_cahier_de_texte, $insertionSQL) or die(mysqli_error($conn_cahier_de_texte));
     } else {
         ?>
         - <a href='MAJ_Archives.php'><font color=red><blink>CLIQUER ICI POUR APPLIQUER UN CORRECTIF RELATIF A L'ARCHIVAGE</blink></font></a>
         <script language="JavaScript" type="text/JavaScript">
                alert("Suite \340 la derni\350re mise \340 jour, vous avez besoin aussi de mettre \340 jour votre base de donn\351es pour que votre archivage soit parfaitement finalis\351.\n\nCliquez sur le lien appropri\351 dans la partie Utilitaires \46 Maintenance du menu administrateur.");
</script>
<?php
     }
}
mysqli_free_result($MaJArchives);?>
</p>

        <p align="left"><a href="controle_tables_cdt.php">Aper&ccedil;u du contenu des tables</a></p>
        <p align="left"><a href="misajour/maj_forcee.php">R&eacute;parer - actualiser les tables</a> </p>
        <p align="left"><a href="nettoie_agenda_avant_date.php">Suppression d'enregistrements 
        ant&eacute;rieurs &agrave; une date</a></p>
        <p align="left">
          <a href="cryptage.php">Utilitaire cryptage apr&egrave;s import profs dans PhpMyAdmin </a></p>
        <p align="left"><a href="protection.php"> Protection de certains dossiers </a> -
          <a href="http://www.etab.ac-caen.fr/bsauveur/cahier_de_texte/">Consulter la Faq en ligne </a></p>
        <p align="left"><a href="gestion_connect.php"><?php echo ($site_ferme=='Oui'?'Activer':'D&eacute;sactiver'); ?> l'acc&egrave;s &agrave; l'application </a><img src="../images/exclamation.png" width="16" height="16"></p>
<td valign="top"><?php if (isset($row_Rsparams1['param_val']) ) {
echo '<p align="left">Version install&eacute;e : <span class="Style70">'.$row_Rsparams1['param_val'].'</span></p>';
};?>
        <p align="left" > <span class="Style71">
                <a href="http://www.bonsauveur.monarobase.net/wp_cdt/"> Derni&egrave;re version disponible en ligne</a>
                <!-- <a href="http://www.etab.ac-caen.fr/bsauveur/cahier_de_texte/"> Derni&egrave;re version disponible en ligne</a> : <?php //echo $versel;?>-->

                          <a href="http://www.bonsauveur.monarobase.net/wp_cdt/version.xml"><img src="../images/rss.png" title="Flux rss - Nouvelle version Cahier de textes" alt="Flux rss - Nouvelle version Cahier de textes" border="0" /></a>&nbsp;<br />
        </span> </p>
        <p align="left" ><a href="http://www.etab.ac-caen.fr/bsauveur/cahier_de_texte/correctifs.html"><em>Tous les derniers correctifs</em></a> </p>
        <p align="left"><a href="http://www.etab.ac-caen.fr/bsauveur/cahier_de_texte/">Consulter le site web de l'application et la Faq en ligne</a></p>
        <p align="left"><a href="migration.php">Comment faire la mise &agrave; jour?</a></p>
        <p align="left"><a href="misajour/maj.php">Mise &agrave; jour de la base de donn&eacute;es </a>
          <?php 
$afficher='<span class="Style70">OK</span>';
mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
//pour anciennes versions 3.x.x
if (!mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_params")) { echo '<span class="Style70"> A faire - Important</span> ';};




if ((isset($row_Rsparams3['param_val'])) && ($row_Rsparams3['param_val']<$indice_version) ){ echo '<span class="Style70"> A faire - Important</span> ';};

?>
        </p>
        <p align="left"><a href="misajour/make_rss.php">Mise &agrave; jour des Flux RSS</a>
          <?php $dir = "../rss";if (!is_dir($dir)){echo ' -  <span class="Style70">A faire  - Important </span>';};?>
        </p>
        <p align="left"><a href="nouvelle_annee_scolaire.php">Changement d'ann&eacute;e scolaire </a></p>
        <p align="left" ><a href="licence.php">Licence</a></p>      </td>
    </tr>
  </table>
  <p><a href="../index.php">Me d&eacute;connecter</a></p>
  <DIV id=footer>
    <p class="auteur"><a href="../contribution.php" class="auteur">Pierre Lemaitre 
      - St L&ocirc; (France) </a></p>
  </DIV>
</DIV>
</body>
</html>
<?php mysqli_close($conn_cahier_de_texte); ?>
