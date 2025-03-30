<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Gestion des &eacute;v&egrave;nements, projets et actions p&eacute;dagogiques";
require_once "../templates/default/header.php";
?>

  <blockquote>
    <blockquote>
      <blockquote>
        <table width="100%" border="0">
          <tr>
            <td><img src="../images/even_planning.png" width="25" height="25"></td>
            <td><blockquote>
              <p align="left">Ce module permet aux enseignants de remplir une fiche relative &agrave; chaque &eacute;v&eacute;nement, projet ou action p&eacute;dagogique qu'il m&egrave;nera au cours de l'ann&eacute;e scolaire. </p>
            </blockquote></td>
          </tr>
        </table>
        <p align="left">Le dispositif poursuit 2 principaux objectifs : </p>
   
      
	  
         
		 
          <div align="left"><strong>Etre un outil de communication : </strong>              </div>
       
     
	 
          <ul>
            <li>
              <div align="left">Informer les coll&egrave;gues depuis la page d'accueil de leur cahier de textes.</div>
            </li>
            <li>
              <div align="left">Informer les &eacute;l&egrave;ves via leur cahier de textes.</div>
            </li>
			            <li>
			              <div align="left">Informer les responsables de l'&eacute;tablissement, CPE, ou autres personnes de votre choix par mail.</div>
			            </li>
          </ul>
        <p align="left"><strong>Archiver et disposer en fin d'ann&eacute;e d'un &eacute;tat des actions men&eacute;es </strong></p>
    

          
          
          <p align="left">La fiche peut &ecirc;tre enregistr&eacute;e &agrave; l'&eacute;tat de projet, puis dans un second temps valid&eacute;e par son cr&eacute;ateur ou par un &quot;responsable de validation&quot; d&eacute;clar&eacute;. </p>
          <p align="left">Ce module est op&eacute;rationnel &agrave; la premi&egrave;re installation. <a href="parametrage_even.php">Voir  les param&egrave;tres par d&eacute;faut</a>. </p>
          <p align="left">&nbsp;</p>
          <p align="center"><a href="../enseignant/evenement_liste.php">Consulter le planning des &eacute;v&egrave;nements &amp; actions p&eacute;dagogiques</a></p>
          <p align="center"><a href="../enseignant/evenement_ajout.php">Ajouter une fiche &eacute;v&egrave;nement &amp; action p&eacute;dagogique</a> </p>
          <p align="center"><a href="parametrage_even.php">Param&eacute;trage de la validation des fiches </a></p>
           
	   
          <p align="left">&nbsp;</p>
        </blockquote>
      </blockquote>
    </blockquote>
    <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>

    <p>&nbsp; </p>  <DIV id=footer>
    <p class="auteur"><a href="contribution.php" class="auteur">Pierre Lemaitre 
      - St L&ocirc; (France) -  <br />
      </a></p>
  </DIV>
</DIV>
</body>
</html>
