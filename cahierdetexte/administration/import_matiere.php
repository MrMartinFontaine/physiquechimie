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
$header_description="Importation des mati&egrave;res depuis un fichier CSV";
require_once "../templates/default/header.php";
?>
</p>
  <p>&nbsp;  </p>
    <blockquote>
      <p align="left">Vous pouvez importer une liste des mati&egrave;res depuis un fichier <strong>csv</strong> ou <strong>txt</strong>. 
        <br>
        La structure du fichier doit &ecirc;tre sous la forme :<br />
      </p>
    </blockquote>
    <p align="center" class="Style70">Mati&egrave;re 1<br />Mati&egrave;re 2<br />Mati&egrave;re 3</p>
    <blockquote>
        <table width="100%"  border="0" cellpadding="5" cellspacing="5" class="tab_detail_gris">
          <tr>
            <td><p align="center"><form method="POST" action="import_matiere2.php" enctype="multipart/form-data">
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
