<?php include "../authentification/authcheck.php" ;?>
<?php require_once('../Connections/conn_cahier_de_texte2.php'); ?>
<?php
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2) or die ("Vous n'avez pas acc&egrave;s &agrave; la base de donn&eacute;es relative &agrave; l'ancien cahier de textes. Contactez votre Administrateur.");

$profchoix_RsImprime = "0";
if (isset($_SESSION['ID_prof'])) {
  $profchoix_RsImprime = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);

}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);
$query_RsImprime = sprintf("SELECT DISTINCT nom_classe, nom_matiere, cdt_classe.ID_classe,cdt_matiere.ID_matiere FROM cdt_agenda, cdt_classe, cdt_matiere WHERE prof_ID=%u AND cdt_classe.ID_classe = cdt_agenda.classe_ID AND cdt_matiere.ID_matiere = cdt_agenda.matiere_ID ORDER BY cdt_classe.nom_classe, cdt_matiere.nom_matiere", $profchoix_RsImprime);
$RsImprime = mysqli_query($conn_cahier_de_texte, $query_RsImprime, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsImprime = mysqli_fetch_assoc($RsImprime);
$totalRows_RsImprime = mysqli_num_rows($RsImprime);
	?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
a img {
	border: none;
}
-->
</style>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
  <DIV id=header_archive>
    <H1>&nbsp;</H1>
    <H1>Cahier de textes </H1>
    <DIV class="description"><?php echo $_SESSION['nom_etab']; ?>
      </p>
      <p><?php echo 'Archive '.$annee_scolaire; ?>
    </DIV>
    <p>&nbsp;</p>
  </DIV>
  <p>&nbsp;</p>
  <table  width="80%" align="center" cellpadding="5" cellspacing="1" class="Style555">
    <?php do { ?>
      <tr>
        <td valign="bottom" bgcolor="#FFFFFF"><?php echo $row_RsImprime['nom_classe']; ?>&nbsp;</td>
        <td valign="bottom" bgcolor="#FFFFFF"><?php echo $row_RsImprime['nom_matiere']; ?>&nbsp;</td>
        <td valign="bottom" bgcolor="#FFFFFF" ><a href="voir_archive.php?classe_ID=<?php echo $row_RsImprime['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprime['ID_matiere'];?>&ordre=down"><img src="../images/print.gif"  width="18" height="18"> Standard </a></td>
        <td valign="bottom" bgcolor="#FFFFFF" ><a href="voir_archive.php?classe_ID=<?php echo $row_RsImprime['ID_classe'];?>&matiere_ID=<?php echo $row_RsImprime['ID_matiere'];?>&ordre=down&annot"><img src="../images/print.gif"  width="18" height="18"> Avec annotations perso. </a></td>
      </tr>
      <?php } while ($row_RsImprime = mysqli_fetch_assoc($RsImprime)); ?>
  </table>
  <p>&nbsp;</p>
  <p align="center">
  <a href="index.php">Me d&eacute;connecter</a> - 
  <a href="voir_classe.php">Autres classes</a></p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsImprime);
?>
