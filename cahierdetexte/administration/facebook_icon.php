<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

if(isset($_POST['choice']))
	{
	$choice = GetSQLValueString($_POST['choice'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice WHERE `param_nom`='facebook_icon';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	}

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='facebook_icon' LIMIT 1;";
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
$header_description="Visibilit&eacute; de l'ic&ocirc;ne Facebook";
require_once "../templates/default/header.php";
?>

  <blockquote>
    <blockquote>
      <blockquote>
        <p style="color:red;">Etat actuel - Afficher cette ic&ocirc;ne Facebook   : <?php echo $access; ?></p>
        <fieldset style="width : 100%">
        <p align="left"><img src="../images/lightbulb.png" width="16" height="16">&nbsp;L'ic&ocirc;ne Facebook pr&eacute;sente dans la page Travail &agrave; faire n'est pas un simple lien vers ce r&eacute;seau social. C'est un fil RSS permettant &agrave; l'&eacute;l&egrave;ve d'int&eacute;grer son travail &agrave; faire dans sa page personnelle Facebook. La  synchronisation se fera alors de fa&ccedil;on automatique avec l'application Cahier de textes.<br>
        </p>
        <p align="left">&nbsp;</p>
        <form method="post"><?php 
if($access=="Oui") echo "<input type=\"hidden\" name=\"choice\" value=\"Non\"/><input type=\"submit\" value=\"Ne pas afficher l'ic&ocirc;ne Facebook \"/>";
else echo "<input type=\"hidden\" name=\"choice\" value=\"Oui\"/><input type=\"submit\" value=\"Afficher l'ic&ocirc;ne Facebook \"/>";
?>       
        </form>
        </p>
	</fieldset>
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

