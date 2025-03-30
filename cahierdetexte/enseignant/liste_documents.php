<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 

$refprof_RsListe = "0";
if (isset($_SESSION['ID_prof'])) {
  $refprof_RsListe = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsListe = sprintf("SELECT * FROM cdt_fichiers_joints,cdt_agenda,cdt_classe,cdt_matiere WHERE cdt_fichiers_joints.prof_ID= %s AND cdt_agenda.ID_agenda=cdt_fichiers_joints.agenda_ID AND cdt_agenda.matiere_ID=cdt_matiere.ID_matiere AND cdt_agenda.classe_ID=cdt_classe.ID_classe ORDER BY cdt_agenda.matiere_ID,cdt_agenda.classe_ID", $refprof_RsListe);
$RsListe = mysqli_query($conn_cahier_de_texte, $query_RsListe) or die(mysqli_error($conn_cahier_de_texte));
$row_RsListe = mysqli_fetch_assoc($RsListe);
$totalRows_RsListe = mysqli_num_rows($RsListe);
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

<?php 
$header_description="Liste des documents joints";
//require_once "../templates/default/header.php";
?>
<p>&nbsp;</p>
<table width="90%"  border="0" align="center" cellpadding="0" cellspacing="0" class="lire_bordure" >
  <tr class="lire_cellule_4">
    <td width="29%" class="black_police"><div align="left">
        <?php if (isset($_SESSION['identite'])){echo '<img src="../images/identite.gif" >&nbsp;'.$_SESSION['identite'];}?>
      </div></td>
    <td width="29%" class="black_police">Liste des documents joints</td>
    <td width="9%" ><div align="right" > <a href="enseignant.php"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0"></a><br>
      </div></td>
  </tr>
</table>

  <p>&nbsp;</p>

  <table  width="90%"  border="0" align="center" cellpadding="0" cellspacing="0" class="bordure">
    <tr class="Style6">
      <td>Mati&egrave;re</td>
      <td>Classe</td>
      <td>Titre de l'activit&eacute; </td>
      <td height="20">Nom du fichier</td>
      <td>Actu</td>
      <td>Sup</td>
      <td>Date</td>
    </tr>
    <?php do { ?>
      <tr bgcolor="#FFFFFF">
        <td height="30" class="menu_detail"><div align="left"><?php echo $row_RsListe['nom_matiere']; ?></div></td>
        <td height="30" class="menu_detail"><div align="left"><?php echo $row_RsListe['nom_classe']; ?></div></td>
        <td height="30" class="menu_detail"><div align="left"><?php echo $row_RsListe['theme_activ']; ?></div></td>
        <td height="30" class="menu_detail"><div align="left"><a href="../fichiers_joints/<?php echo $row_RsListe['nom_fichier']; ?>" target="_blank">
            <?php 
                $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_RsListe['nom_fichier']); 
                ?>
            <?php echo $nom_f; ?></a>
			
			</div></td>
			  <td class="menu_detail"><a href="liste_documents_maj.php?<?php echo 'ID_fichiers='.$row_RsListe['ID_fichiers'].'&nom_fichier='.$row_RsListe['nom_fichier'];?>"><img src="../images/edt_maj.jpeg" alt="Supprimer le fichier" width="19" height="23" border="0" title="Mettre &agrave; jour le fichier"></a>&nbsp;</td> 
              <td class="menu_detail"><a href="liste_documents_supprime.php?<?php echo 'ID_fichiers='.$row_RsListe['ID_fichiers'].'&nom_fichier='.$row_RsListe['nom_fichier'];?>"> <img src="../images/ed_delete.gif" alt="Supprimer le fichier" title="Supprimer le fichier" border="0"></a></td>
        <td height="30" class="menu_detail"><div align="left"><?php echo $row_RsListe['jour_pointe']; ?></div></td>
      </tr>
      <?php } while ($row_RsListe = mysqli_fetch_assoc($RsListe)); ?>
  </table>
<p align="center">&nbsp;</p>
<p align="center">
  <a href="../index.php">Me d&eacute;connecter</a> - <a href="enseignant.php">Retour au Menu Enseignant</a>
</p>
</br></br>
</body>
</html>
<?php
mysqli_free_result($RsListe);

?>
