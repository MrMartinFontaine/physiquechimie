<?php 
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>'6') { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 
require_once('../inc/functions_inc.php');

if(isset($_POST['classe_ID'])){
				$choix_RsClasse = intval($_POST['classe_ID']);
				$_SESSION['consultation']=$choix_RsClasse;
				$GoTo='../consulter.php?classe_ID='.strtr(GetSQLValueString($choix_RsClasse,"int"),$protect).'&tri=date';
				header(sprintf("Location: %s", $GoTo));
				}
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
$header_description="<br />Assistant &eacute;ducation - ".$_SESSION['identite'];
require_once "../templates/default/header.php";
require_once ("../authentification/sessionVerif.php"); 


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

?>
<HR>
  <p>&nbsp;</p>
       
		  <form  name="form3" method="post" action="assistant_educ.php">
    <table width="92%" border="0" align="center" cellpadding="0" cellspacing="0" class="espace_enseignant">
      <tr>
        <td><div align="center" class="Style13">
          <p>&nbsp;</p>
          <p class="texte">Consultation du travail &agrave; faire, des cahiers de textes et de l'emploi du temps </p>
        </div></td>
      </tr>
      <tr>

        <td><p>
            <select name="classe_ID" id="classe_ID">
              <option value="value">S&eacute;lectionner la classe</option>
              <?php do {
			  ?>
              <option value="<?php echo $row_RsClasse['ID_classe']?>"><?php echo $row_RsClasse['nom_classe']?></option>
              <?php	} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse));
			  mysqli_free_result($RsClasse);
?>
            </select>
			&nbsp;&nbsp;&nbsp;<input type="submit" name="Submit3" value="Valider">        </p>          </td>
      </tr>
    </table>
    <p> </p>
  </form>
	
        <p align="center" class="Style74">&nbsp;</p>
        <p align="center" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9">&nbsp;<a href="../enseignant/evenement_liste.php">Liste des &eacute;v&egrave;nements ou actions p&eacute;dagogiques </a></p>
        <p align="center" class="Style74"><img src="../images/puce_bleue.gif" width="9" height="9"><a href="../vie_scolaire/prof_principaux_liste.php?vie_sco"> Liste des professeurs principaux </a>  </p>
        <p align="center" class="Style74"><a href="../deconnexion.php">Me d&eacute;connecter</a></p>     
  <DIV id=footer>    <p class="auteur"><a href="../contribution.php" class="auteur">Pierre Lemaitre 
      - St L&ocirc; (France) </a></p></DIV>
</DIV>
</body>
</html>
