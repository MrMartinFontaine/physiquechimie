<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>4)){ header("Location: ../../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_archives = "SELECT NumArchive,NomArchive FROM `cdt_archive`";
        $archives = mysqli_query($conn_cahier_de_texte, $query_archives) or die(mysqli_error($conn_cahier_de_texte));
        $total_archives = mysqli_num_rows($archives);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style70 {font-size: 12}
-->
</style>
</HEAD>
<BODY>
<p>&nbsp;</p>
<DIV id=page>
  <p>
    <?php 
$header_description="Sauvegarde des tables cahier de textes de la base de donn&eacute;es";
require_once "../templates/default/header.php";
?>
  </p>
  </p>
  <p>&nbsp;</p>
  <blockquote>
    <p align="left">L'ex&eacute;cution de la sauvegarde g&eacute;n&eacute;rera un enregistrement de vos tables du cahier de textes pr&eacute;fix&eacute;es cdt_. Le fichier texte obtenu d'extension sql, contient un ensemble de  commandes SQL. </p>
    <p align="left">Si le fichier de sauvegarde est volumineux et votre connexion au serveur lente, il se peut que le script de sauvegarde soit interrompu et la sauvegarde partielle. En cons&eacute;quence, vous trouverez ci-dessous plusieurs modes de sauvegarde.</p>
    <?php if ($_SESSION['droits']==1) {  //Que pour l'admin ?>
    <p align="left">Si des probl&egrave;mes persistent, il vous est toujours possible d'effectuer votre sauvegarde avec un utilitaire comme PhpMyadmin en utilisant sa fonction exporter. </p>
    <?php } ?>
    <p align="left">En fin de sauvegarde, il vous est propos&eacute; d'enregistrer le fichier de sauvegarde g&eacute;n&eacute;r&eacute;. Si cela ne vous est pas propos&eacute;, il est possible que certaines librairies php soient manquantes sur votre serveur. Voir votre h&eacute;bergeur.</p>
    <?php if ($_SESSION['droits']==1) { //Que pour l'admin ?>
    <p align="left">En cas de force majeure, vous pourrez restaurer vos tables cdt_xxx avec un utilitaire comme PhpMyadmin &agrave; l'aide de votre fichier de sauvegarde en utilisant sa fonction importer.</p>
    <?php } else { // Que pour le chef d'etab ?>
    <p align="left">En cas de force majeure, vous pourrez contacter l'administrateur du cahier de textes pour qu'il restaure vos tables.</p>
    <?php } ?>
   
  </blockquote>
  <table width="90%" border="0" align="center" class="tab_detail_gris">
    <tr>
      <td valign="middle" class="tab_detail"><p>Sauvegarde de toutes les tables, archives comprises.
        Attention, le fichier obtenu peut &ecirc;tre volumineux. </p>      </td>
      <td class="tab_detail"><form action="sauvegarde2.php" method="post" name="form1" id="form1">
        <p>
          <input type="hidden" name="type_sauvegarde" value="1">
          <input name="submit1" type="submit"  value="Sauvegarder toutes les tables ">
        </p>
      </form></td>
    </tr>

    <tr>
      <td valign="middle" class="tab_detail">Sauvegarde de l'ann&eacute;e en cours uniquement sans les archives. </td>
      <td class="tab_detail"><form action="sauvegarde2.php" method="post" name="form2" id="form2">
        <p>
          <input type="hidden" name="type_sauvegarde" value="2">
          <input name="submit2" type="submit"  value="Sauvegarder uniquement les tables de l'ann&eacute;e en cours ">
        </p>
      </form></td>
    </tr>

    <tr>
      <td valign="middle" class="tab_detail">Sauvegarde uniquement de la table cdt_agenda de l'ann&eacute;e en cours (cette table est  la plus volumineuse).</td>
      <td class="tab_detail"><form action="sauvegarde2.php" method="post" name="form3" id="form3">
        <p>
          <input type="hidden" name="type_sauvegarde" value="3">
          <input name="submit3" type="submit"  value="Sauvegarder uniquement  la table cdt_agenda">
        </p>
      </form></td>
    </tr>
    <tr>
      <td valign="middle" class="tab_detail">Sauvegarde de toutes les tables de l'ann&eacute;e en cours sauf la table cdt_agenda. </td>
      <td class="tab_detail"><form action="sauvegarde2.php" method="post" name="form4" id="form4">
        <p>
          <input type="hidden" name="type_sauvegarde" value="4">
          <input name="submit4" type="submit"  value="Sauvegarder les tables - ann&eacute;e en cours sauf cdt_agenda">
        </p>
      </form></td>
    </tr>
	
	<tr>
	
      <td valign="middle" class="tab_detail">Sauvegarde uniquement de la table cdt_emploi_du_temps de l'ann&eacute;e en cours </td>
      <td class="tab_detail"><form action="sauvegarde2.php" method="post" name="form3" id="form3">
        <p>
          <input type="hidden" name="type_sauvegarde" value="6">
          <input name="submit3" type="submit"  value="Sauvegarder uniquement  la table cdt_emploi_du_temps">
        </p>
      </form></td>
    </tr>
	
    <?php if ($total_archives!=0){ ?>
                
         <tr>
     <td colspan="2" valign="middle" align="middle" ><br/>
      La partie suivante traite uniquement la sauvegarde des archives.<br/>&nbsp;
    </td>
    </tr>
     <tr>
      <td valign="middle" class="tab_detail"><br>
      Sauvegarde de toutes les archives. </td>
      <td class="tab_detail"><form action="sauvegarde2.php" method="post" name="form5" id="form5">
          <p>
            <input type="hidden" name="type_sauvegarde" value="5">
            <input name="submit5" type="submit"  value="Sauvegarder toutes les tables d'archives">
          </p>
      </form></td>
    </tr>
	
    <?php 
    if ($total_archives>1){
        while ($row_archives = mysqli_fetch_array($archives)) { 
            $number_archives=$row_archives['NumArchive']+5;
        ?>
    <tr>
      <td valign="middle" class="tab_detail"><br>
      Sauvegarde des archives <?php echo $row_archives['NomArchive']; ?> uniquement. </td>
      <td class="tab_detail"><form action="sauvegarde2.php" method="post" name="form<?php echo $number_archives; ?>" id="form<?php echo $number_archives; ?>">
          <p>
            <input name="submit5" type="submit"  value="Sauvegarder les tables d'archives <?php echo $row_archives['NomArchive']; ?> uniquement">
            <input type="hidden" name="type_sauvegarde" value="<?php echo $number_archives; ?>">
          </p>
      </form></td>
    </tr>

    <?php
    }
    }
}
mysqli_free_result($archives);	
?><tr>
     <td colspan="2" valign="middle" align="middle" ><br/>
      La partie suivante traite uniquement la sauvegarde des emplois du temps. <br/>
      &nbsp;
    </td>
    </tr>
   <tr>
	
      <td valign="middle" class="tab_detail">Sauvegarde uniquement de la table cdt_emploi_du_temps de l'ann&eacute;e en cours </td>
      <td class="tab_detail"><form action="sauvegarde2.php" method="post" name="form3" id="form3">
        <p>
          <input type="hidden" name="type_sauvegarde" value="7">
          <input name="submit3" type="submit"  value="Sauvegarder uniquement  la table cdt_emploi_du_temps">
        </p>
      </form></td>
    </tr>
  </table>
  <p> </p>
  <p>&nbsp;</p>
  <?php 
  if ($_SESSION['droits']==1){ echo '<p><a href="index.php">Retour au Menu Administrateur</a></p>';};
  if ($_SESSION['droits']==4){ echo '<p><a href="../direction/direction.php">Retour au Menu Resp. Etablissement</a></p>';};
  ?>

  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
