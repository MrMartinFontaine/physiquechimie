<?php include "../../authentification/authcheck.php"; 
if ($_SESSION['droits']<>1) { header("Location: ../../index.php");exit;};
?>
<?php require_once('../../Connections/conn_cahier_de_texte.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Importation des &eacute;l&egrave;ves depuis un fichier CSV - Module d&eacute;claration des absences</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
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
$header_description="Importation des &eacute;l&egrave;ves depuis un fichier CSV - Module d&eacute;claration des absences";
require_once "../../templates/default/header2.php";
?>
</p>
  <p>&nbsp;  </p>
    <blockquote>
      <p align="left">Vous pouvez importer une liste des &eacute;l&egrave;ves depuis un fichier <strong>csv</strong> ou <strong>txt</strong>.<br>
  La structure du fichier doit &ecirc;tre sous la forme :<br />
      </p>
  </blockquote>
    <p align="center" class="Style70">nom;pr&eacute;nom;classe</p>
    <blockquote>     
      <p align="left"><br />
      Exemple de structure du fichier texte d&eacute;limit&eacute; point-virgule :</p>
      <blockquote>
        <p align="left"><strong> Lemaitre;Pierre;6A</strong></p>
      </blockquote>
      <p align="left"><img src="../../images/lightbulb.png" width="16" height="16"> Les groupes ne sont pas &agrave; importer. L'affectation des &eacute;l&egrave;ves au sein de ceux-ci se fera facilement et ult&eacute;rieurement &agrave; partir du menu administrateur ou vie scolaire.</p>
      <blockquote>
        <table width="100%"  border="0" cellpadding="5" cellspacing="5" class="tab_detail_gris">
          <tr>
            <td>    <p align="center"><form method="POST" action="import_absence_eleves_csv2.php" enctype="multipart/form-data">
              <!-- On limite le fichier à 200Ko -->
              <input type="hidden" name="MAX_FILE_SIZE" value="200000">
              Fichier : <input type="file" size="40" name="elevesdatacsv">
              <input type="submit" name="envoyer" value="Envoyer le fichier">
  </form></p></td>
          </tr>
        </table>
        <p align="left">&nbsp;</p>
      </blockquote>
    </blockquote>


	<p align="center"><a href="module_absence_install.php">Retour au Menu d'installation du module.</a></p>
  <p align="center"><a href="../index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
