<?php 
//modifier pour chaque nouvelle version
$indice_version='5510'; 
$libelle_version='Version 5.5.1.0 Standard';

include "../authentification/authcheck.php" ;
//probleme init variable
$date1_form='';
$date2_form='';

if ($_SESSION['droits']<>3 && $_SESSION['droits']<>7){ header("Location: ../index.php");exit;};?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style74 {font-size: 8pt;
	text-indent: 10px;
	color:#0000FF;
}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
<?php 
if ($_SESSION['droits']==7){$header_description=$_SESSION['identite'];} else {$header_description='Vie scolaire';};
require_once "../templates/default/header.php";
?>
<br />
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="5">
  <tr>
    <td><div align="left" class="Style6">Configurer </div></td>
    <td><div align="left" class="Style6">Communiquer</div></td>
  </tr>
  <tr>
    <td valign="top" class="Style74"><br />
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="parametres_vie_scolaire.php">Mes param&egrave;tres g&eacute;n&eacute;raux (identifiant, m&eacute;l ...)</a></p>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="chemin_fichier_perso.php">Chemin d'acc&egrave;s &agrave; mes fichiers personnels sur le serveur</a></p>
      <?php if ($_SESSION['droits']==3 ){?>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="niveau_classe_ajout.php">Cr&eacute;er et g&eacute;rer des niveaux de classes </a></p>
	  <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../inc/regroupement_liste.php">Gestion des regroupements de classes</a> </p>
      <?php };
?>    </td>
    <td valign="top" class="Style74"><br />
      <?php if ($_SESSION['droits']==3 ){?>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../administration/remplacement/remplacement.php">Gestion des remplacements</a></p>
      <?php };?>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/message_ajout_profs.php">Diffusion d'un message aux enseignants </a></p>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/message_ajout_profs.php?ppliste=1">Diffusion d'un message aux professeurs principaux </a></p>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/message_ajout.php?tri=auteur">Diffusion d'un message aux &eacute;l&egrave;ves</a></p>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../enseignant/evenement_liste.php">Liste des &eacute;v&egrave;nements ou actions p&eacute;dagogiques</a><a href="../vie_scolaire/evenement_ajout.php"></a></p></td>
  </tr>
  <tr>
  <td><div align="left" class="Style6">S'informer</div></td>
  <?php if ((isset($_SESSION['module_absence']))&&($_SESSION['module_absence']=='Oui'))	{	?>
    <td><div align="left" class="Style6">Absences, carnets et autres consignes </div></td> <?php } else { ?>
	<td></td>
	<?php };?>
  </tr>
  <tr>
    <td width="50%" valign="top" class="Style74"><br />
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="planning_select.php">Planning des devoirs et du travail donn&eacute; dans une classe</a> </p>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../edt_eleve.php">Emploi du temps d'une classe</a></p>
	  <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../enseignant/semaine_ab_voir.php">Planning des semaines en alternance</a></p>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/prof_principaux_liste.php?tri=pp">Liste des professeurs principaux d&eacute;clar&eacute;s</a></p>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../vie_scolaire/prof_liste.php">Liste de l'ensemble du personnel avec leur m&eacute;l</a></p></td>
    <td width="50%" rowspan="3" valign="top" class="Style74"><?php
if ((isset($_SESSION['module_absence']))&&($_SESSION['module_absence']=='Oui'))	{	



if ((isset($_SESSION['choix_module_absence']))&&($_SESSION['choix_module_absence']==2))	{	?>

 <br /> <br />
	  <p align="left" style="margin-top:2px "><img src="../images/puce_jaune.gif" width="9" height="9">&nbsp;<a href="absence.php" target="_blank" >Absences du jour ou d'un autre jour</a></p>

	    <p align="left" ><img src="../images/puce_jaune.gif" width="9" height="9">&nbsp;<a href="carnets_bilan.php" target="_blank" >Consulter les carnets</a></p>	

	  <p align="left" ><img src="../images/puce_jaune.gif" width="9" height="9">&nbsp;<a href="carnets_bilan.php?sansEven=Y&submit=Actualiser" target="_blank" >Bilan des absences sur une p&eacute;riode</a></p> 
   	 <p align="left" ><img src="../images/puce_jaune.gif" width="9" height="9">&nbsp;<a href="ele_etat_present.php" target="_blank" >Etat des &eacute;l&egrave;ves pr&eacute;sents au CDI </a></p>
	<?php } 
	else
	{ ?>
       <br />
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="absence_simple.php">Absences du jour</a></p>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="absence2.php">Etat des absences par classe sur une p&eacute;riode</a></p>
	  
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="absence_perso_1_2.php">Pointage Oublis (carnet de correspondance, mat&eacute;riel...)</a></p>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="absence_perso_1_2_periode.php">Etat des oublis par classe sur une p&eacute;riode</a></p>	  
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="absence_perso_3.php">Pointage relatif au champ "Divers"</a></p>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="ele_liste_present.php">Pointage des &eacute;l&egrave;ves pr&eacute;sents au CDI </a></p>
	        <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="ele_etat_present.php">Etat des &eacute;l&egrave;ves pr&eacute;sents au CDI </a></p>
 
 <?php     
};
?>


      </td>
  </tr>
  <tr>
    <td valign="top" class="Style74"><div align="left" class="Style6">Gestion de la liste des &eacute;l&egrave;ves </div></td>
  </tr>
  <tr>
    <td valign="top" class="Style74"><?php if ($_SESSION['droits']==3 ){?>
	       <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../administration/module_absence/ele_liste_affiche.php">Gestion de la liste des &eacute;l&egrave;ves de chaque classe</a> </p>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../administration/module_absence/ele_affectation_groupe.php">Affectation des &eacute;l&egrave;ves dans les groupes</a> </p>
	  
	        <?php 
	};
};



?></td>
  </tr>
   <tr>
    <td valign="top" class="Style74"></td>
    <td valign="top" class="Style74">
	<p align="left" class="Style74">&nbsp;</p>
      <p align="left" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../deconnexion.php">Me d&eacute;connecter </a></p>
	</td>
	</tr>
</table>
<DIV id=footer>
  <p class="auteur"><a href="contribution.php" class="auteur">Pierre Lemaitre 
    - St L&ocirc; (France) - <?php echo $libelle_version ;?> <br />
    </a></p>
</DIV>
</body>
</html>
