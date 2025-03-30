<?php 
include "../authentification/authcheck.php"; 
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rspp =sprintf("SELECT pp_prof_ID FROM cdt_prof_principal,cdt_groupe WHERE pp_prof_ID=%u ",$_SESSION['ID_prof']);
$Rspp = mysqli_query($conn_cahier_de_texte, $query_Rspp) or die(mysqli_error($conn_cahier_de_texte));
$totalRows_Rspp = mysqli_num_rows($Rspp);
mysqli_free_result($Rspp);

//parametre autorisant l'acces ou non a un ancien cahier de textes
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='old_cdt_access' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access = $row[0];
mysqli_free_result($result_read);

//parametre autorisant la diffusion d'un message par le professeur principal
$query_read2= "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='pp_diffusion' LIMIT 1;";
$result_read2 = mysqli_query($conn_cahier_de_texte, $query_read2);
$row2 = mysqli_fetch_row($result_read2);
$pp_diffusion = $row2[0];
mysqli_free_result($result_read2);

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
.Style74 {
	font-size: 8pt;
	text-indent: 10px;
	color:#0000FF;
}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
<?php 
if ($_SESSION['droits']==8){
$header_description="Menu documentaliste";} 
else {
$header_description="Menu enseignant";};
require_once "../templates/default/header.php";
require_once ("../authentification/sessionVerif.php"); 

if ((isset($_SESSION['id_etat'])) AND ($_SESSION['id_etat']==1)){       // titulaire absent
	$date_declare_abs=str_replace("-","",$_SESSION['date_declare_absent']);
	$date_declare_abs_f=substr($date_declare_abs,6,2).'/'.substr($date_declare_abs,4,2).'/'.substr($date_declare_abs,0,4);
	?>
	<br />
	<p style="color: #FF0000"><strong>Vous &ecirc;tes actuellement d&eacute;clar&eacute; absent par l'administration &agrave; dater du <?php echo $date_declare_abs_f;?>. </strong></p>
<?php };
?>
<p>&nbsp;</p>
<p align="center">
<?php if($_SESSION['droits']<>8){?>
	<a href="planning_select.php">Planning &eacute;l&egrave;ve</a>&nbsp;&nbsp;-&nbsp;&nbsp;
<?php };?>
<a href="planning_prof.php?date=<?php echo date('Ymd');?>">Planning enseignant</a>&nbsp;&nbsp;-&nbsp;&nbsp;<a href="ecrire.php?date=<?php echo date('Ymd');?>">Remplir le cahier de textes</a></p>
<br />





<table width="95%" border="0" align="center" cellpadding="0" cellspacing="5">
<tr>
<?php if ($_SESSION['droits']==2){ ?>
<td width="50%"><div align="left" class="Style6">Installer - Configurer </div></td>


<td><div align="left" class="Style6">Mes cahiers </div></td>
<?php } ;?>

</tr>
<tr>
<td width="50%" rowspan="8" valign="top"><br>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="parametres.php">Mes param&egrave;tres (identit&eacute;, publication...) </a></p>
<?php
//      si $_SESSION['id_etat'] =0 le titulaire peut modifier l'emploi du temps en totalite 
//      si $_SESSION['id_etat'] =1 le titulaire ne peut pas modifier l'emploi du temps pendant son absence
//      si $_SESSION['id_etat'] =2 le suppleant peut modifer l'emploi du temps en partie 
//      il faut egalement tester si le remplacement est termine, dans ce cas ne pas autoriser a modifier son emploi du temps
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsP =sprintf("SELECT id_remplace,id_etat FROM cdt_prof WHERE ID_prof= %u ",$_SESSION['ID_prof']);
$RsP = mysqli_query($conn_cahier_de_texte, $query_RsP) or die(mysqli_error($conn_cahier_de_texte));
$row_RsP = mysqli_fetch_assoc($RsP);
$visu_emploi="ok";
if ($row_RsP['id_etat']==2 AND $row_RsP['id_remplace']==0)
	{$visu_emploi="no";}
mysqli_free_result($RsP);


if ((isset($_SESSION['id_etat'])) AND( $_SESSION['id_etat']<>1) AND ($visu_emploi=="ok")){	
       if ($_SESSION['droits']==2){?>
        <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="groupe_interclasses_ajout.php">Gestion des groupes issus de plusieurs classes/Regroup.</a></p>
       
        	<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="emploi.php?affiche=1<?php if ((isset($_SESSION['masque_edt_cloture']))&&($_SESSION['masque_edt_cloture']=='O')){echo '&masque_cloture=1';} else {echo '&masque_cloture=0';};?>">Saisir - G&eacute;rer mon emploi du temps</a></p>
			
<?php };
};?>

<?php if ($_SESSION['droits']==2){?>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="type_activite_ajout.php">G&eacute;rer mes types d'activit&eacute;s</a></p>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="professeur_principal.php">Je suis professeur principal</a></p>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="aide/aide.htm">Aide pour les d&eacute;butants</a></p>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="exportationCSV.php">Exporter/Sauvegarder mon cahier et mes fichiers</a> </p>
<?php };?>
<?php
if (($_SESSION['module_absence']=='Oui')&&($_SESSION['droits']==2)){ ?>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="smart_appel_lien.php">R&eacute;aliser l'appel des absences sur mon mobile</a></p>
<?php };?>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="contact_admin.php">Contacter l'administrateur</a> </p></td>

<?php if ($_SESSION['droits']==2){ ?>

<td valign="top"><br />
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="imprimer_menu.php">Consulter mes cahiers de textes</a></p>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="cahiers_archives_liste.php">Consulter mes cahiers en archives</a></p>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="liste_documents.php">Lister mes documents envoy&eacute;s en pi&egrave;ces jointes</a></p>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="progression.php">Carnet de bord - Fiches de progression</a></p>
<?php if ($access=="Oui") {?>
        <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../archive/archive_menu.php" target="_blank" >Consulter un ancien cahier de textes</a></p>
<?php };?>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="publier_invite.php">Cahiers accessibles par le compte invit&eacute;</a></p>
</td>

<?php };?>

</tr>

    
	<tr>
	<td valign="top"><div align="left" class="Style6">Diffuser l'information </div></td>
	</tr>
	

	<tr>
	<td valign="top">
<?php 
//prof principal autorise a diffuser ou prof disposant droits pp  
// diffusion aux eleves des classes dans lequel on enseigne
// diffusion aux enseignants des classes dans lequel on enseigne
if ((($pp_diffusion=="Oui")&&($totalRows_Rspp>0))||((isset($_SESSION['prof_mess_pp']))&&($_SESSION['prof_mess_pp']=='Oui'))) 
{?>
		<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="message_ajout.php">Diffuser un message &agrave; mes &eacute;l&egrave;ves</a> </p>
        
        <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="message_ajout.php?dest_profs=0">Diffuser un message aux enseignants de mes &eacute;l&egrave;ves</a> </p>     
<?php };?>


<?php 
//prof principal autorise a diffuser ou prof disposant droits pp ou documentaliste
// diffusion aux eleves de toutes les classes
// diffusion a tous les enseignantsde l'etablissement
//on retrouve les droits de diffusion de la vie scolaire
if ((isset($_SESSION['prof_mess_all']))&&($_SESSION['prof_mess_all']=='Oui')||($_SESSION['droits']==8)) 
{?>
		<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/message_ajout.php">Diffuser un message aux &eacute;l&egrave;ves de l'&eacute;tablissement </a> </p>
        <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/message_ajout_profs.php?dest_profs=0">Diffuser un message aux enseignants de l'&eacute;tablissement</a> </p>     
<?php };?>


<?php 
//prof principal autorise a diffuser ou prof disposant droits pp ou documentaliste ou prof ayant des droits de diffusion etendus
//diffusion aux PP
if ((($pp_diffusion=="Oui")&&($totalRows_Rspp>0))||((isset($_SESSION['prof_mess_pp']))&&($_SESSION['prof_mess_pp']=='Oui'))||($_SESSION['droits']==8)||($_SESSION['prof_mess_all']=='Oui')) 
{?>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/message_ajout_profs.php?dest_profs=0&ppliste=1">Diffuser un message aux professeurs principaux</a> </p>
<?php };?>




<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="evenement_liste.php">Planifier un &eacute;v&eacute;nement ou une action p&eacute;dagogique </a></p>

</td>
</tr>
<?php 


if ((isset($_SESSION['module_absence']))&& ($_SESSION['module_absence']=='Oui')) {
	?>
	
	
	
	<tr>
	<td valign="top"><div align="left" class="Style6">Absences &amp; Oublis </div></td>
	</tr>
	<tr>
	<td valign="top">
	<?php //documentaliste - pointage present
	if ($_SESSION['droits']==8){?>
		<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/ele_liste_present.php">Pointage des &eacute;l&egrave;ves pr&eacute;sents au CDI </a></p>
		<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/ele_etat_present.php">Etat des &eacute;l&egrave;ves pr&eacute;sents au CDI </a></p>
		<?php };?>
	<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/absence2.php">Etat des absences par classe sur une p&eacute;riode</a></p>
	<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/absence_perso_1_2_periode.php">Etat des oublis (carnet, mat.) par classe sur une p&eacute;riode</a></p>       
	
	</td>
	</tr>
<?php } ?>
<tr>
<td valign="top"><div align="left" class="Style6">Mon environnement de travail </div></td>
</tr>
<tr>
<td valign="top"><p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="semaine_ab_voir.php">Planning semaines en alternance et dates de vacances</a></p>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/prof_principaux_liste.php?vie_sco">Liste des professeurs principaux</a></p>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/prof_liste.php?vie_sco">Liste de l'ensemble du personnel</a> </p>
<p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../edt_eleve.php">Afficher l'emploi du temps d'une classe</a><br>
</p></td>
</tr>
<tr>
<td valign="top"><p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../deconnexion.php">Me d&eacute;connecter</a></p></td>
</tr>
</table>
<DIV id=footer>
<p class="auteur"><a href="../contribution.php" class="auteur">Pierre Lemaitre 
- St L&ocirc; (France) </a></p>
</DIV>
</DIV>
</body>
</html>
