<?php
include "../../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

if(isset($_POST['choice']))
	{
	$choice = GetSQLValueString($_POST['choice'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice WHERE `param_nom`='module_absence';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	}

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='module_absence' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access = $row[0];
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
$header_description="D&eacute;claration des absences";
require_once "../../templates/default/header2.php";
?>

  <blockquote>
    <blockquote>
      <blockquote>


        <p align="left"><img src="../../images/lightbulb.png" width="16" height="16"> Ce module vous permet de d&eacute;clarer les absents lors de la saisie de s&eacute;ance du cahier de textes. La liste est transmise et consultable par la Vie Scolaire. 
          
          La premi&egrave;re partie de ce <a href="http://www.etab.ac-caen.fr/bsauveur/cahier_de_texte/Installation du module absences.pdf" target="_blank">document pdf</a> vous pr&eacute;sente ce module.
        Cela implique l'int&eacute;gration dans la base de donn&eacute;es de la liste des &eacute;l&egrave;ves. Une installation automatique du module est d&eacute;sormais possible par importation d'un fichier csv ou txt contenant la liste de vos &eacute;l&egrave;ves.</p>
        <p align="left"><strong>Installation</strong></p>
        <p align="center"><a href="module_absence_install.php">Premi&egrave;re installation  du module de d&eacute;claration d'absences</a></p>
        <p align="center">-------------------</p>
        <p align="left"><strong>Gestion du module de d&eacute;claration d'absences </strong></p>
      </blockquote>
      <ul>
        <li>
          <div align="left"><a href="import_absence_eleves_csv.php" align="center">Importer la liste des &eacute;l&egrave;ves depuis un fichier csv ou txt </a></div>
        </li>
        <li>
          <div align="left"><a href="ele_liste_affiche.php" align="center">Gestion de la liste des &eacute;l&egrave;ves - Modification - Suppression</a></div>
        </li>
        <li>
          <div align="left"><a href="ele_affectation_groupe.php" align="center">Affectation des &eacute;l&egrave;ves au sein d'un groupe </a></div>
        </li>
        <li>
          <div align="left"><a href="module_absence_sql1.php" align="center">Gestion des associations  : Nom de la classe / Code classe import&eacute; de la liste des &eacute;l&egrave;ves</a></div>
        </li>
      </ul>
      <blockquote>
        <p align="center">------------------------</p>
        <p align="left"><strong>Choix du module absence </strong><?php if (isset($_SESSION['choix_module_absence'])){
		
		if ($_SESSION['choix_module_absence']==1){echo "(actuellement module simple )";} else {echo "(actuellement module &eacute;labor&eacute;)";};};?></p>
        <p align="center"><a href="module_absence_choix.php">	Simple ou &eacute;labor&eacute; avec suivi  des incidents de cours </a></p>
        <p align="center">------------------------</p>
        <p align="left"><strong>Mise &agrave; jour </strong></p>
        <p align="left">Mise &agrave; jour de la table ele_absent pour les versions du CDT sup&eacute;rieures &agrave; 5501 <br>
          (Le module absence doit pr&eacute;alablement avoir &eacute;t&eacute; install&eacute; et activ&eacute;). </p>
        <p align="center"><a href="../misajour/maj_ele_absent.php">Mettre &agrave; jour la table ele_absent</a> </p>
        <p align="center">------------------------</p>
        <p align="left"><strong>Nouvelle ann&eacute;e scolaire </strong></p>
        <p align="center"><a href="vider_eleve_absence.php">Nouvelle ann&eacute;e scolaire - Vidage des tables absences et de la liste des &eacute;l&egrave;ves</a></p>
        <p>&nbsp;</p>
        <p align="center" style="color: #FF0000">
          <?php if ($access=='Oui'){ echo 'Le module d&eacute;claration des absences est activ&eacute;'; } else {echo 'Le module d&eacute;claration des absences est d&eacute;sactiv&eacute;';};?>
        </p>
		<p align="center"><a href="module_absence_choix.php"></a> </p>
		
        <p>        
        <form method="post">
          <?php 
if($access=="Non") echo "<input type=\"hidden\" name=\"choice\" value=\"Oui\"/><input type=\"submit\" value=\"Activer le module de d&eacute;claration des absences \"/>";
else echo "<input type=\"hidden\" name=\"choice\" value=\"Non\"/><input type=\"submit\" value=\"D&eacute;sactiver le module de d&eacute;claration des absences \"/>";
?>       
        </form>
        </p>
        <p align="left">&nbsp;</p>
      </blockquote>
    </blockquote>
  </blockquote>
  <p align="center"><a href="../index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>

