<?php 
include "../authentification/authcheck.php"; 
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProfAdmin = "SELECT identite,email FROM cdt_prof WHERE cdt_prof.ID_prof=1";
$RsProfAdmin = mysqli_query($conn_cahier_de_texte, $query_RsProfAdmin) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProfAdmin = mysqli_fetch_assoc($RsProfAdmin);
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
$header_description="";
require_once "../templates/default/header.php";
?>  <p>&nbsp;</p>
  
  <table width="70%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td width="50%"><div align="left" class="Style6">Administrateur(s) de l'application Cahier de textes </div></td>
    </tr>

    <tr>
      <td width="50%"><p>&nbsp;</p></td>
    </tr>
    <?php do{ ?>
    <tr >
        <td class="tab_detail"><?php echo $row_RsProfAdmin['identite']. '&nbsp;&nbsp;&nbsp;<a href="mailto:'.$row_RsProfAdmin['email'].'">'.$row_RsProfAdmin['email'].'</a>'; ?></td>
    </tr>
      <?php  }  while ($row_RsProfAdmin = mysqli_fetch_assoc($RsProfAdmin));  ?>

  </table>       
  <p align="center">&nbsp;</p> 
  <p><a href="enseignant.php">Retour au Menu Enseignant </a></p>

  <DIV id=footer></DIV>
</DIV>
</body>
</html>
