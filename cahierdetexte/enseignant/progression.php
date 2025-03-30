<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsListeProgression = sprintf("SELECT ID_progression,titre_progression FROM cdt_progression WHERE prof_ID=%u ORDER BY titre_progression ASC", $_SESSION['ID_prof']);
$RsListeProgression = mysqli_query($conn_cahier_de_texte, $query_RsListeProgression) or die(mysqli_error($conn_cahier_de_texte));
$row_RsListeProgression = mysqli_fetch_assoc($RsListeProgression);
$totalRows_RsListeProgression = mysqli_num_rows($RsListeProgression);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}

function confirmation(ref,titre_del)
{
  if (confirm("Voulez-vous supprimer r\351ellement la fiche de progression '"+titre_del+"' ?")) { // Clic sur OK
    MM_goToURL('window','progression_supprime.php?ID_progression='+ref);
       }
}
//-->
</script>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="<br />Carnet de bord - Progressions";
require_once "../templates/default/header.php";

?>
<HR>
  <p>&nbsp;</p>
  <p><a href="progression_ajout.php">
    <input name="Submit" type="submit" onClick="MM_goToURL('window','progression_ajout.php');return document.MM_returnValue" value="Cr&eacute;er une nouvelle fiche dans le carnet de bord">
    </a> &nbsp;&nbsp;&nbsp;<a href="enseignant.php"><img src="../images/home-menu.gif" alt="Menu Enseignant" title="Menu Enseignant" border="0"></a><br>
  </p>
  <?php
  if ($totalRows_RsListeProgression >0){?>
  <table  width="90%"  border="0" align="center" cellpadding="0" cellspacing="0" class="bordure">
    <tr class="Style6">
      <td height="20">Titre de mes fiches</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <?php do { ?>
      <tr bgcolor="#FFFFFF">
        <td height="30" class="menu_detail"><div align="left"><img src="../images/puce_jaune.gif">&nbsp;<a href="progression_affiche.php?ID_progression=<?php echo $row_RsListeProgression['ID_progression']; ?>"><?php echo $row_RsListeProgression['titre_progression']; ?></a></div></td>
        <td class="tab_detail"><div align="center"> <img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" border="0" onClick="MM_goToURL('window','progression_modif.php?ID_progression=<?php echo $row_RsListeProgression['ID_progression']; ?>');return document.MM_returnValue"> </div></td>
        <td class="tab_detail"><div align="center"><img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="12" height="13" border="0" onClick= "return confirmation('<?php echo $row_RsListeProgression['ID_progression'];?>','<?php echo $row_RsListeProgression['titre_progression']; ?>');return document.MM_returnValue;">
</div></td>
      </tr>
      <?php } while ($row_RsListeProgression = mysqli_fetch_assoc($RsListeProgression)); ?>
  </table>
  <?php ; };
  mysqli_free_result($RsListeProgression);
  ?>
  <p align="center"><a href="enseignant.php"><br>
    Retour au Menu Enseignant</a>&nbsp;- <a href="ecrire.php?date=<?php echo date('Ymd');?>">Retour en saisie de s&eacute;ance</a> <br>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>

