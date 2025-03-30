<?php include "../authentification/authcheck.php"; ?>
<?php if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};?>
<?php require_once('../Connections/conn_cahier_de_texte.php'); 
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_emploi_du_temps")) { 
$sql1="TRUNCATE `cdt_emploi_du_temps`";
$result_sql_1=(mysqli_query($conn_cahier_de_texte, $sql1)) or die('Erreur SQL !'.$sql1.mysqli_error($conn_cahier_de_texte));};

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_emploi_du_temps_partage")) { 
$sql1="TRUNCATE `cdt_emploi_du_temps_partage`";
$result_sql_1=(mysqli_query($conn_cahier_de_texte, $sql1)) or die('Erreur SQL !'.$sql1.mysqli_error($conn_cahier_de_texte));};

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_agenda")) { 
$sql2="TRUNCATE `cdt_agenda`";
$result_sql_2=mysqli_query($conn_cahier_de_texte, $sql2) or die('Erreur SQL !'.$sql2.mysqli_error($conn_cahier_de_texte)); };

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_travail")) { 
$sql3="TRUNCATE `cdt_travail`";
$result_sql_3=mysqli_query($conn_cahier_de_texte, $sql3) or die('Erreur SQL !'.$sql3.mysqli_error($conn_cahier_de_texte)); };

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_fichiers_joints")) { 
$sql4="TRUNCATE `cdt_fichiers_joints`";
$result_sql_4=mysqli_query($conn_cahier_de_texte, $sql4) or die('Erreur SQL !'.$sql4.mysqli_error($conn_cahier_de_texte)); };

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_prof_principal")) { 
$sql5="TRUNCATE `cdt_prof_principal`";
$result_sql_5=mysqli_query($conn_cahier_de_texte, $sql5) or die('Erreur SQL !'.$sql5.mysqli_error($conn_cahier_de_texte)); };

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_message_contenu")) { 
$sql6="TRUNCATE `cdt_message_contenu`";
$result_sql_6=mysqli_query($conn_cahier_de_texte, $sql6) or die('Erreur SQL !'.$sql6.mysqli_error($conn_cahier_de_texte)); };

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_message_destinataire")) { 
$sql7="TRUNCATE `cdt_message_destinataire`";
$result_sql_7=mysqli_query($conn_cahier_de_texte, $sql7) or die('Erreur SQL !'.$sql7.mysqli_error($conn_cahier_de_texte)); };

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_message_fichiers")) { 
$sql8="TRUNCATE `cdt_message_fichiers`";
$result_sql_8=mysqli_query($conn_cahier_de_texte, $sql8) or die('Erreur SQL !'.$sql8.mysqli_error($conn_cahier_de_texte)); };

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_evenement_contenu")) { 
$sql9="TRUNCATE `cdt_evenement_contenu`";
$result_sql_9=mysqli_query($conn_cahier_de_texte, $sql9) or die('Erreur SQL !'.$sql9.mysqli_error($conn_cahier_de_texte)); };

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_evenement_destinataire")) { 
$sql10="TRUNCATE `cdt_evenement_destinataire`";
$result_sql_10=mysqli_query($conn_cahier_de_texte, $sql10) or die('Erreur SQL !'.$sql10.mysqli_error($conn_cahier_de_texte)); };

if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_message_destinataire_profs")) { 
$sql11="TRUNCATE `cdt_message_destinataire_profs`";
$result_sql_11=mysqli_query($conn_cahier_de_texte, $sql11) or die('Erreur SQL !'.$sql11.mysqli_error($conn_cahier_de_texte)); };

                $updateSQL5 = "UPDATE cdt_prof SET id_remplace=0 WHERE id_remplace!=0";
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $Result5 = mysqli_query($conn_cahier_de_texte, $updateSQL5) or die(mysqli_error($conn_cahier_de_texte));
                
                $updateSQL5 = "UPDATE cdt_prof SET date_maj='0000-00-00' WHERE droits=2";
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $Result5 = mysqli_query($conn_cahier_de_texte, $updateSQL5) or die(mysqli_error($conn_cahier_de_texte));
                
                $updateSQL5 = "UPDATE cdt_prof SET stop_cdt = 'N'";
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $Result5 = mysqli_query($conn_cahier_de_texte, $updateSQL5) or die(mysqli_error($conn_cahier_de_texte));
 
$updateSQL6 = "UPDATE cdt_agenda SET date_visa='0000-00-00'";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Result6 = mysqli_query($conn_cahier_de_texte, $updateSQL6) or die(mysqli_error($conn_cahier_de_texte));


$sql12="TRUNCATE `cdt_archive_association`";
$result_sql_12=mysqli_query($conn_cahier_de_texte, $sql12) or die('Erreur SQL !'.$sql12.mysqli_error($conn_cahier_de_texte)); 

$insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }

header(sprintf("Location: %s", $insertGoTo));
};
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
$header_description="Vider le cahier de textes";
require_once "../templates/default/header.php";
?>
<HR>
<form name="form1" method="post" action="vider_cahier_de_texte.php">
  <p>&nbsp;  </p>
  <blockquote>
    <blockquote>
      <p align="left">Vous avez choisi de vider les donn&eacute;es de l'ann&eacute;e pr&eacute;c&eacute;dente. Vous conserverez : </p>
    </blockquote>
  </blockquote>
  <div align="left">
    <ul>
      <ul>
        <ul>
          <li>la table des enseignants et leur mot de passe(cdt_prof)</li>
          <li>la table de leurs progressions (cdt_progression)</li>
          <li>la table de leurs types d'activit&eacute;s (cdt_activite) </li>
          <li>la table des libell&eacute;s de classes et mots de passe (cdt_classe)</li>
          <li>la table des libell&eacute;s de mati&egrave;res  (cdt_matiere)</li>
          <li>la table des libell&eacute;s de groupes  (cdt_groupe)</li>
          <li>la table des semaines A et B (cdt_semaine_ab)</li>
          <li>la table des identifiants de version logiciel(cdt_params)  <br>
          </li>
        </ul>
        <blockquote>
          <p>&nbsp;</p>
          <p>Seules les tables ci-dessous seront vid&eacute;es de leur contenu.</p>
          <p>&nbsp;</p>
        </blockquote>
      </ul>
    </ul>
  </div>
  <ul>
  </ul>
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td class="Style6">Tables</td>
      <td class="Style6">Contenus</td>
    </tr>
    <tr>
      <td class="tab_detail_gris">cdt_emploi_du_temps</td>
      <td class="tab_detail_gris">L'emploi du temps de chaque enseignant </td>
    </tr>
    <tr>
      <td class="tab_detail_gris">cdt_agenda</td>
      <td class="tab_detail_gris">Le contenu de chaque s&eacute;quence de cours </td>
    </tr>
    <tr>
      <td class="tab_detail_gris">cdt_travail</td>
      <td class="tab_detail_gris">Le travail donn&eacute; &agrave; faire tout au long de l'ann&eacute;e </td>
    </tr>
    <tr>
      <td class="tab_detail_gris">cdt_fichiers_joints</td>
      <td class="tab_detail_gris">Les libell&eacute;s des fichiers joints envoy&eacute;s </td>
    </tr>
    <tr>
      <td class="tab_detail_gris">cdt_prof_principal</td>
      <td class="tab_detail_gris">La liste des professeurs principaux d&eacute;clar&eacute;s </td>
    </tr>
    <tr>
      <td class="tab_detail_gris">cdt_message_contenu</td>
      <td class="tab_detail_gris">Les messages envoy&eacute;s aux &eacute;l&egrave;ves par la direction, vie scolaire ou professeurs principaux </td>
    </tr>
    <tr>
      <td class="tab_detail_gris">cdt_message_destinataire</td>
      <td class="tab_detail_gris">Les  destinataires de ces messages </td>
    </tr>
    <tr>
      <td class="tab_detail_gris">cdt_message_fichiers</td>
      <td class="tab_detail_gris">Les fichiers joints &agrave; ces messages </td>
    </tr>
    <tr>
      <td class="tab_detail_gris">cdt_evenement</td>
      <td class="tab_detail_gris">La liste des &eacute;v&egrave;nements (examen blanc...) </td>
    </tr>
    <tr>
      <td class="tab_detail_gris">cdt_evenement_destinataire</td>
      <td class="tab_detail_gris">Les destinataires de ces &eacute;v&egrave;nements </td>
    </tr>
    <tr>
      <td class="tab_detail_gris">cdt_evenement_destinataire_profs</td>
      <td class="tab_detail_gris">Les messages aux enseignants</td>
    </tr>
  </table>
  <p class="erreur">&nbsp;</p>
  <p class="erreur">Apr&egrave;s cette op&eacute;ration, n'oubliez pas de supprimer manuellement les fichiers contenu dans les dossiers<br> 
    <strong>fichiers_joints, fichiers_joints_message, rss, exportation</strong> &agrave; l'exception des fichiers nomm&eacute;s index </p>
  <p class="erreur">&nbsp;</p>
  <p>
    <input type="submit" name="Submit" value="Vider les tables ci-dessus de la base de donn&eacute;es du  cahier de textes">
  </p>
  <p>
    <input type="hidden" name="MM_update" value="form1">
</p>
</form>
<br>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>

