<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

if(isset($_POST['choice']))
	{
	$choice = GetSQLValueString($_POST['choice'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice WHERE `param_nom`='old_cdt_access';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	}

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='old_cdt_access' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access = $row[0];
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
$header_description="Acc&egrave;s &agrave; un ancien cahier de textes";
require_once "../templates/default/header.php";
?>

  <blockquote>
    <blockquote>
      <blockquote>
        <p style="color:red;">Acc&egrave;s autoris&eacute; : <?php echo $access; ?></p>
        <p align="left"><span class="erreur"><strong>ATTENTION : </strong></span>Cette m&eacute;thode est devenue obsol&egrave;te dans le cas ou vous avez utilis&eacute; l'archivage depuis le menu administrateur (duplication automatis&eacute;e des tables de l'ann&eacute;e &eacute;coul&eacute;e ). Dans ce cas, l'acc&egrave;s ci-dessous doit &ecirc;tre d&eacute;sactiv&eacute;. </p>
        <p align="left">La proc&eacute;dure ci-dessous ne sera donc utilis&eacute;e que dans le cas d'une nouvelle installation utilisant une nouvelle base. Il sera donc n&eacute;cessaire de d&eacute;finir les param&egrave;tres de connexions &agrave; l'ancienne base de donn&eacute;es et d'activer ci-dessous l'autorisation en consultation. </p>
        <hr>
        <p>*****</p>
        <p align="left">Les enseignants peuvent avoir acc&egrave;s &agrave; leur 
          ancien cahier de textes soit depuis le menu enseignant, soit en cours 
          de saisie en cliquant sur une icone plac&eacute;e dans le calendrier 
          &agrave; gauche du mois.</p>
        <p align="left">Cela suppose que leurs identifiants soient conserv&eacute;s 
          et que vous ayez param&eacute;tr&eacute; l'acc&egrave;s &agrave; l'ancienne 
          base de cahier de textes. Pour r&eacute;aliser ce param&eacute;trage, 
          &eacute;diter manuellement le fichier <strong>Connections/conn_cahier_de_texte2.php</strong> 
          et renseignez les diff&eacute;rents champs.</p>
        <p align="left">&nbsp;</p>
        <p><form method="post">
<?php 
if($access=="Oui") echo "<input type=\"hidden\" name=\"choice\" value=\"Non\"/><input type=\"submit\" value=\"D&eacute;sactiver l'acc&egrave;s &agrave; un ancien cahier de textes pr&eacute;sent dans une autre base\"/>";
else echo "<input type=\"hidden\" name=\"choice\" value=\"Oui\"/><input type=\"submit\" value=\"Activer l'acc&egrave;s &agrave; un ancien cahier de textes pr&eacute;sent dans une autre base\"/>";
?>       
        </form></p>
        <p align="left">&nbsp;</p>
      </blockquote>
    </blockquote>
  </blockquote>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
