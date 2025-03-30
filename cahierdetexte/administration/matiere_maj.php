<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');


if ((isset($_POST["MM_sup"])) && ($_POST["MM_sup"] == "form1")) {	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMat = "
DELETE FROM cdt_matiere
WHERE ID_matiere NOT IN (SELECT matiere_ID FROM  cdt_agenda)";

$RsMat = mysqli_query($conn_cahier_de_texte, $query_RsMat) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMat = mysqli_fetch_assoc($RsMat);

header("Location:matiere_ajout.php");
};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMatiere = "
SELECT ID_matiere,nom_matiere
FROM cdt_matiere
WHERE ID_matiere NOT IN (SELECT matiere_ID FROM  cdt_agenda) ORDER BY nom_matiere";
$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
$totalRows_RsMatiere = mysqli_num_rows($RsMatiere);

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"></script>
<script type="text/javascript" src="../jscripts/jquery.jCombo.min.js"></script>
</HEAD>
<BODY>
<DIV id=page>
  <p>
    <?php 
$header_description="Visualisation - Suppression de mati&egrave;res inutilis&eacute;es";
require_once "../templates/default/header.php";

if ($totalRows_RsMatiere==0){?>
  </p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>Les mati&egrave;res pr&eacute;sentes sont toutes rattach&eacute;es &agrave; des contenus d&eacute;j&agrave; saisis. </p>
    <?php } else {;?>
      
  <blockquote>


<fieldset style="width : 100%">
<p align="left">Les mati&egrave;res affich&eacute;es ci-dessous ne sont pas encore rattach&eacute;es actuellement &agrave; des contenus. <br>
  <strong>Il y a lieu probablement de les conserver afin que les enseignants b&acirc;tissent leur emploi du temps. </strong></p>
<p align="left">Cependant, suite &agrave; un import d&eacute;fectueux de noms de mati&egrave;res, il peut &ecirc;tre n&eacute;cessaire de r&eacute;aliser leur suppression. </p>
<p align="left">Il se peut par ailleurs, que vous ayez saisi des doublons. (Math&eacute;matiques et Maths par exemple)... Mais lesquels sont utilis&eacute;s ? (Seulement Math&eacute;matiques, ou les deux ?) </p>
<p align="left">Ce script vous permet de visualiser ci-dessous puis d'&eacute;ventuellement supprimer en bloc l'ensemble de ces mati&egrave;res dont les r&eacute;f&eacute;rences ne sont pas utilis&eacute;es dans les contenus de l'ann&eacute;e en cours.</p>
<p align="left">&nbsp;</p>


  <table border="0" align="center" width ="70 %">
    <tr class="Style6">
      <td>Ref &nbsp;</td>
      <td><div align="center"><?php echo $totalRows_RsMatiere;?> mati&egrave;re(s)</div></td>
      </tr>
    <?php do { ?>
      <tr>
        <td class="tab_detail_gris"><div align="center"><?php echo $row_RsMatiere['ID_matiere']; ?></div></td>
        <td class="tab_detail_gris"><?php echo $row_RsMatiere['nom_matiere']; ?></td>
        </tr>
      <?php } while ($row_RsMatiere = mysqli_fetch_assoc($RsMatiere)); ?>
  </table>

<form action="matiere_maj.php" method="post" name="form1" onsubmit="return confirm('Voulez vous supprimer l\'ensemble de ces matieres ?');">
<br/>
<br/>
<input type="submit"   name="Submit"  value="Supprimer l'ensemble de ces mati&egrave;res non rattach&eacute;es &agrave; des contenus">
<input type="hidden" name="MM_sup" value="form1">
</form>
</p>
</fieldset>
<p align="left">&nbsp;</p>
</blockquote>
<?php }; ?>
<p align="center"><a href="matiere_ajout.php">Annuler</a></p>
<p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
<p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
