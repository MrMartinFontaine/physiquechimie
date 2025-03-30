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
$header_description="Importation des utilisateurs depuis un fichier CSV";
require_once "../templates/default/header.php";
?>
</p>
  <p>&nbsp;  </p>
    <blockquote>
      <p align="left">Vous pouvez importer une liste des enseignants depuis un fichier <strong>csv</strong> ou <strong>txt</strong>. 
        <br>
        La structure du fichier doit être sous la forme :<br />
      </p>
    </blockquote>
    <p align="center" class="Style70">nom;mot de passe;login;email;droit</p>
    <blockquote>
      <p align="left">&nbsp;</p>
      <blockquote>
        <table border="0" align="center">
          <tr>
            <td colspan="2" class="tab_detail_gris"><strong>Valeur du param&egrave;tre droit (profil) </strong></td>
          </tr>
          <tr>
            <td class="tab_detail_gris">Enseignant </td>
            <td class="tab_detail_gris">2</td>
          </tr>
          <tr>
            <td class="tab_detail_gris">Vie scolaire :</td>
            <td class="tab_detail_gris">3</td>
          </tr>
          <tr>
            <td class="tab_detail_gris">Resp. d'&eacute;tablissement</td>
            <td class="tab_detail_gris">4</td>
          </tr>
          <tr>
            <td class="tab_detail_gris">Invit&eacute; </td>
            <td class="tab_detail_gris">5</td>
          </tr>
          <tr>
            <td class="tab_detail_gris">Assistant &eacute;ducation</td>
            <td class="tab_detail_gris">6</td>
          </tr>
          <tr>
            <td class="tab_detail_gris">P&eacute;riscolaire</td>
            <td class="tab_detail_gris">7</td>
          </tr>
          <tr>
            <td class="tab_detail_gris">Documentaliste</td>
            <td class="tab_detail_gris">8</td>
          </tr>
        </table>
        <p align="left">Le param&egrave;tre droit peut &ecirc;tre absent. La valeur 2 (profil enseignant) sera attribu&eacute;e par d&eacute;faut).<br>
          Le champ login ne doit pas contenir d'accents.<br>
          Le champ mot de passe peut &ecirc;tre vide. Chaque enseignant pourra red&eacute;finir son mot de passe.<br>
          Le champ nom 
        peut &ecirc;tre vide. Il recevra alors par d&eacute;faut le login. <br>
        Le champ email 
        peut &ecirc;tre vide. </p>
      </blockquote>
      <p align="left"><br />
      Exemple de structure du fichier texte d&eacute;limit&eacute; point-virgule :</p>
      <blockquote>
        <p align="left"><strong> Lemaitre Pierre;passeword17;p_lemaitre;pierre.lemaitre@ac-caen.fr;2</strong></p>
        <p align="left">ou structure minimale :        </p>
        <blockquote>
          <p align="left"><strong>;;p_lemaitre;;</strong></p>
        </blockquote>
        <table width="100%"  border="0" cellpadding="5" cellspacing="5" class="tab_detail_gris">
          <tr>
            <td>    <p align="center"><form method="POST" action="import_csv2.php" enctype="multipart/form-data">
     <!-- On limite le fichier à 200Ko -->
     <input type="hidden" name="MAX_FILE_SIZE" value="200000">
     Fichier : <input type="file" size="40" name="datacsv">
     <input type="submit" name="envoyer" value="Envoyer le fichier">
</form></p></td>
          </tr>
        </table>
        <p align="left">&nbsp;</p>
      </blockquote>
    </blockquote>

<blockquote>
  <p align="left">&nbsp;</p>
  <p align="left">Rq : Une autre possibilit&eacute; est l'import depuis un fichier extrait de <strong>SCONET/STSWEB </strong>. Voir menu Administrateur.</p>
  <p align="left">&nbsp; </p>
</blockquote>
<p><div align="center"><a href="index.php">Retour au Menu Administrateur 
            </a></div></p>
  <p align="center"><a href="index.php"></a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
