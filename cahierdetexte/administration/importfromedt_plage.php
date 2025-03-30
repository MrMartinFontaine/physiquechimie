<?php include "../authentification/authcheck.php"; 
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
?>
<?php require_once('../Connections/conn_cahier_de_texte.php'); ?>
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
	font-size: 10pt;
	font-weight: bold;
}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
  <p>
    <?php 
$header_description="Importation de plages horaires depuis un fichier txt extrait d'un planning";
require_once "../templates/default/header.php";
?>
</p>
  <p>&nbsp;  </p>
    <blockquote>
      <p align="left">La proc&eacute;dure ci-dessus est adapt&eacute;e aux &eacute;tablissements dont l'emploi du temps est modifi&eacute; toutes les semaines. C'est le cas par exemple des GRETA, CFA, MFR...</p>
      <p align="left">Vous disposez alors le plus souvent d'un planning pour une longue p&eacute;riode (une semaine, un mois, un trimestre...)</p>
      <p align="left">Ce planning est existant sur un tableur ou via un logiciel tel que YPAREO pour les CFA. Il est donc possible de r&eacute;aliser un export au format texte d&eacute;limit&eacute; point-virgule (txt ou csv d&eacute;limit&eacute; point-virgule) depuis votre application.</p>
      <p align="left">Il vous faudra probablement  remettre en forme ce fichier sur un tableur avant la proc&eacute;dure d'import dans l'application cahier de textes. Il devra en effet poss&eacute;der une structure bien pr&eacute;cise  d&eacute;finie ci-dessous :<br />
  </p>
      <p align="left">Chaque ligne de votre fichier texte comprend la d&eacute;finition d'une plage horaire et comprends 10 champs : </p>
    </blockquote>
    <p align="center" class="Style70">date s&eacute;ance ; heure  d&eacute;but ; heure fin; dur&eacute;e; mati&egrave;re ;nom  prof;classe 1;groupe1;classe2;groupe2;</p>
    <blockquote>
      <p align="center" class="Style70">27/10/2014;15h00;17h00;02h00;Technologie;BELLET Benedicte;Seconde_A;;;;</p>
      <p align="center">.</p>
      <ul><li>
        <div align="left">Les libell&eacute;s de mati&egrave;re, de classe, et d'enseignants doivent correspondre &agrave; ceux d&eacute;j&agrave; pr&eacute;sents dans votre cahier de textes. Si la correspondance n'est pas trouv&eacute;e, la valeur de ces nouveaux profs, mati&egrave;res ou classes seront ajout&eacute;es automatiquement &agrave; l'existant. <br>
        Avant le premier import de l'ann&eacute;e, les tables mati&egrave;res, classes et profs peuvent donc &ecirc;tre vides. On realise avec cette seule proc&eacute;dure, le remplissage des tables utilisateurs, classes, mati&egrave;res, groupes, et empoi du temps.</div>
      </li>






        <li>
          <div align="left"> Si groupe1 et groupe2 sont vides, ils prendront par défaut la valeur "Classe entière" 
            
            
            
            Si classe2 n'est pas vide, classe 1 et classe 2 vont constituer un regroupement pour la séance (les deux noms sont concaténés).          </div>
        </li>
        <li>
          <div align="left">Il y a donc <strong>10 champs</strong> par ligne (certains peuvent rester vides - mais il faut respecter le nombre de points-virgules.<br>
            <div align="left">Respectez bien le nombre de point-virgules. (10 points-virgules par ligne). </div>
            <br>
            Chaque ligne se termine par un point-virgule.</div>
        </li>
        <li>
          <div align="left">
            <div align="left">Le format de heure d&eacute;but, heure de fin et dur&eacute;e n'a pas d'importance.<br>
        </div>
          </div>
        </li>
        <li>
          <div align="left">La dur&eacute;e est facultative.<br>
            <br>
        </div>
        </li>
        <li>
          <div align="left">V&eacute;rifiez bien  dans votre fichier que tous les champs soient bien renseign&eacute;s pour chacune de vos lignes. Ainsi par exemple, si une mati&egrave;re est manquante, la ligne ne sera pas trait&eacute;e. <br>
          </div>
        </li>
        <li>
          <div align="left">Il ne doit pas y avoir de premi&egrave;re ligne d&eacute;crivant les champs. La premi&egrave;re ligne du fichier constitue la description de la premi&egrave;re plage horaire. </div>
        </li>
      </ul>
      <blockquote>
        <p>.</p>
        <blockquote>
          <p>R&eacute;aliser pr&eacute;alablement une sauvegarde de la table actuelle des emplois du temps : &nbsp;
          <form action="sauvegarde2.php" method="post" name="form3" id="form3">
       
          <input type="hidden" name="type_sauvegarde" value="7">
          <input name="submit3" type="submit"  value="Sauvegarder">
        </p>
      </form>
		  <p>.</p>
          <p>Votre fichier est pr&ecirc;t et <strong>bien v&eacute;rifi&eacute;</strong>, alors poursuivons en s&eacute;lectionnant votre fichier. <br>
          Soyez patient, le temps d'ex&eacute;cution de la proc&eacute;dure d'importation peut &ecirc;tre long. </p>
        </blockquote>
        <table width="100%"  border="0" cellpadding="5" cellspacing="5" class="tab_detail_gris">
          <tr>
            <td>    <p align="center"><form method="POST" action="importfromedt_plage2.php" enctype="multipart/form-data">
     <!-- On limite le fichier à 900Ko -->
     <input type="hidden" name="MAX_FILE_SIZE" value="900000">
     Fichier : <input type="file" size="40" name="datacsv">
     <input type="submit" name="envoyer" value="Envoyer le fichier">
</form></p></td>
          </tr>
        </table>
        <p align="left">.</p>
      </blockquote>
  </blockquote>

<blockquote><a href="index.php">Retour au Menu Administrateur 
  </a></blockquote>
</p>
  <p align="center"><a href="index.php"></a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
