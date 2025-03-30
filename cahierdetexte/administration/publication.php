<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsPublier = "SELECT nom_prof,identite,publier_cdt,publier_travail,MAX(code_date),date_maj  FROM cdt_prof, cdt_agenda
WHERE ID_prof=prof_ID GROUP BY prof_ID ORDER BY nom_prof ASC";
$RsPublier = mysqli_query($conn_cahier_de_texte, $query_RsPublier) or die(mysqli_error($conn_cahier_de_texte));
$row_RsPublier = mysqli_fetch_assoc($RsPublier);
$totalRows_RsPublier = mysqli_num_rows($RsPublier);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsPublier2 = "SELECT date_maj FROM cdt_prof WHERE ID_prof=1 ";
$RsPublier2 = mysqli_query($conn_cahier_de_texte, $query_RsPublier2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsPublier2 = mysqli_fetch_assoc($RsPublier2);
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
$header_description="Etat de la publication des enseignants";
require_once "../templates/default/header.php";
?>

   
<?php if ($totalRows_RsPublier==0){echo ' <div class="erreur">Aucune saisie actuellement </div>';}
else {;?><p></p>
  <table width="100%"border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
    <tr>
      <td class="Style6">Enseignant &nbsp;</td>
      <td class="Style6">CDT en ligne &nbsp;</td>
          <td class="Style6">Travail en ligne &nbsp;</td>
          <td class="Style6">Dernier ajout &nbsp;</td>
          <td class="Style6">Derni&egrave;re sauvegarde &nbsp;</td>
    </tr>
    <?php
        do {
	  $date_lastajout=substr($row_RsPublier['MAX(code_date)'],6,2).'/'.substr($row_RsPublier['MAX(code_date)'],4,2).'/'.substr($row_RsPublier['MAX(code_date)'],0,4);
    $date_lastsvg=substr($row_RsPublier['date_maj'],8,2).'/'.substr($row_RsPublier['date_maj'],5,2).'/'.substr($row_RsPublier['date_maj'],0,4);
 ?>
      <tr class="tab_detail" >
        <td class="tab_detail"><?php echo $row_RsPublier['identite']<>'' ? '&nbsp;'.$row_RsPublier['identite'].'&nbsp;('.$row_RsPublier['nom_prof'].')' : '&nbsp;'.$row_RsPublier['nom_prof']; ?></td>
        <td class="tab_detail<?php echo $row_RsPublier['publier_cdt']=='N'?'_rose':'';?>"><div align="center"><?php echo $row_RsPublier['publier_cdt']; ?></div></td>
        <td class="tab_detail<?php echo $row_RsPublier['publier_travail']=='N'?'_rose':'';?>"><div align="center"><?php echo $row_RsPublier['publier_travail']; ?></div></td>
        <td class="tab_detail" align="right"><?php echo '&nbsp;'.jour_semaine($date_lastajout).' '.$date_lastajout.'&nbsp;'; ?></td>
        <td class="tab_detail" align="right"><?php echo ($date_lastsvg!="00/00/0000"?'&nbsp;'.jour_semaine($date_lastsvg).' '.$date_lastsvg.'&nbsp;':'&nbsp;Aucune sauvegarde'); ?></td>
      </tr>
      <?php } while ($row_RsPublier = mysqli_fetch_assoc($RsPublier)); ?>
  </table>
  <?php };?>
  <p>&nbsp;</p>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsPublier);
mysqli_free_result($RsPublier2);
?>
