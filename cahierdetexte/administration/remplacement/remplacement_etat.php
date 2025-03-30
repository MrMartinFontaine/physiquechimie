<?php 
include "../../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1 && $_SESSION['droits']<>3) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');



	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsRemplacement = "SELECT * FROM cdt_remplacement,cdt_prof WHERE ID_prof=titulaire_ID ORDER BY date_creation_remplace ";
	$RsRemplacement = mysqli_query($conn_cahier_de_texte, $query_RsRemplacement) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsRemplacement = mysqli_fetch_assoc($RsRemplacement);
    $total_row_RsRemplacement = mysqli_num_rows($RsRemplacement);
	
//	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
//	$query_RsRemplacement2 = "SELECT * FROM cdt_remplacement,cdt_prof WHERE ID_prof=remplacant_ID ORDER BY date_creation_remplace ";
//	$RsRemplacement2 = mysqli_query($conn_cahier_de_texte, $query_RsRemplacement2) or die(mysqli_error($conn_cahier_de_texte));
//	$row_RsRemplacement2 = mysqli_fetch_assoc($RsRemplacement2);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
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
$header_description='Gestion des remplacements - Etat';
require_once "../../templates/default/header.php";

if ($total_row_RsRemplacement==0){echo '<br /><br />Aucun remplacement actuellement.<br /><br />';}
else {
?>
  <br />

<table  width="90%"  border="0" align="center" cellpadding="0" cellspacing="0" class="bordure">
    <tr class="Style6">
      <td>Titulaire</td>
      <td>Rempla&ccedil;ant</td>
      <td>D&eacute;but du remplacement  </td>
      <td height="20">Fin du remplacement </td>
      <td>Etat</td>
      </tr>
    <?php do { 
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsRemplacement2 = sprintf("SELECT * FROM cdt_prof WHERE ID_prof=".$row_RsRemplacement['remplacant_ID']);
	$RsRemplacement2 = mysqli_query($conn_cahier_de_texte, $query_RsRemplacement2) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsRemplacement2 = mysqli_fetch_assoc($RsRemplacement2);
	mysqli_free_result($RsRemplacement2);
	?>
      <tr bgcolor="#FFFFFF">
        <td height="30" class="menu_detail"><div align="left"><?php 
		if ($row_RsRemplacement['date_fin_remplace']=='0000-00-00'){ 
		echo '<div align="left" style="color: #FF0000">';}
		else {
		echo '<div align="left">';
		};
		echo $row_RsRemplacement['identite'].'</div>';
		?></td>
        <td height="30" class="menu_detail"><div align="left"><?php echo $row_RsRemplacement2['identite']; ?></div></td>
        <td height="30" class="menu_detail"><div align="center"><?php echo substr($row_RsRemplacement['date_debut_remplace'],8,2).'-'.substr($row_RsRemplacement['date_debut_remplace'],5,2).'-'.substr($row_RsRemplacement['date_debut_remplace'],0,4); ?></div></td>
        <td height="30" class="menu_detail"><div align="left">
          <div align="center">
            <?php 
		if ($row_RsRemplacement['date_fin_remplace']<>'0000-00-00'){echo substr($row_RsRemplacement['date_fin_remplace'],8,2).'-'.substr($row_RsRemplacement['date_fin_remplace'],5,2).'-'.substr($row_RsRemplacement['date_fin_remplace'],0,4);} ?>
          </div></td>
		<td height="30" class="menu_detail"><?php 
		if ($row_RsRemplacement['date_fin_remplace']<>'0000-00-00'){ echo '<div align="left" style="color: #009933">Termin&eacute;</div>';} else {echo '<div align="left" style="color: #FF0000">En cours </div>';}?>
		
		</td>
</tr>
      <?php } while ($row_RsRemplacement = mysqli_fetch_assoc($RsRemplacement)); ?>
  </table>
  <?php
  }
  ?>
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="5">
<p align="left" class="Style74">&nbsp;</p>
        <p align="center" class="Style74"><a href="remplacement.php">Retour au module de Gestion des remplacements</a></p></td>
    </tr>
  </table>

  <DIV id=footer>
    <p class="auteur"><a href="../contribution.php" class="auteur">Pierre Lemaitre 
      - St L&ocirc; (France) </a></p>
  </DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsRemplacement);

?>