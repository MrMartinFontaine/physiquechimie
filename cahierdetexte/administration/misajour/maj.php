<?php 
include "../../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');
$page_accueil=3;


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
.Style70 {font-size: 12}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
  <p>
    <?php 
$header_description="Mises &agrave; jour de la base de donn&eacute;es";
require_once "../../templates/default/header.php";
?>
  </p>
  <p>&nbsp; </p>
  <p>
  <?php
require_once "maj_sql_inc.php";
?>
  </p>
  <p>&nbsp;</p>
  <p><a href="../index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
if (isset($Rsparams)){mysqli_free_result($Rsparams);};
if (isset($Rsparams2)){mysqli_free_result($Rsparams2);};
if (isset($Rsparams3)){mysqli_free_result($Rsparams3);};
?>

