<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>4) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
//visa global OUI
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

  $updateSQL1 = sprintf("UPDATE cdt_prof SET date_maj='%s' WHERE droits=4 ",date('Y-m-d'));
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL1) or die(mysqli_error($conn_cahier_de_texte));
}

//visa global NON
if ((isset($_POST["MM_update3"])) && ($_POST["MM_update3"] == "form3")) {
  $updateSQL3 = sprintf("UPDATE cdt_prof SET date_maj='0000-00-00' WHERE droits=4",GetSQLValueString($_SESSION['ID_prof'], "int"));
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Result3 = mysqli_query($conn_cahier_de_texte, $updateSQL3) or die(mysqli_error($conn_cahier_de_texte));
}





mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); // le date_maj du responsable établissement - la date de son dernier visa
$query_RsPublier2 = "SELECT MAX(date_maj) FROM cdt_prof WHERE droits=4 ";
$RsPublier2 = mysqli_query($conn_cahier_de_texte, $query_RsPublier2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsPublier2 = mysqli_fetch_row($RsPublier2);
?>




<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
.Style70 {color: #000066}
-->
</style>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Publication - Visa de contr&ocirc;le";
require_once "../templates/default/header.php";
?>
<BR />

  <div>
    <table border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
      <tr> 
        <td class="tab_detail"> <p align="center"><br>  <span class="Style70">Nous sommes le <?php echo jour_semaine(date('d/m/Y')).' '.date('d/m/Y')?>.</span> 
<BR />  <br />
            <?php 
  $date_actu=substr($row_RsPublier2[0],8,2).'/'.substr($row_RsPublier2[0],5,2).'/'.substr($row_RsPublier2[0],0,4);
  if (substr($row_RsPublier2[0],0,4)=='0000') {
  
  echo 'Actuellement, il n\'y a <strong>pas de visa </strong>affich&eacute; sur les cahiers de textes en mode Consultation El&egrave;ves et Parents.';?>
             <br /> <br />
          

          
          <form action="publication_visa_global.php" method="post" name="form1" id="form1">
            <div align="center"> 
              <input type="hidden" name="MM_update" value="form1">
              <input name="submit1" type="submit" class="vacances" value="Afficher un visa avec la date du jour en mode Consultation El&egrave;ves et Parents">
            </div>
          </form><?php } 
  else 
  {
  echo ' Un visa de contr&ocirc;le <strong>est affich&eacute;</strong> sur tous les cahiers de textes depuis la date du <strong>'.jour_semaine($date_actu).' '.$date_actu.'.</strong>';
  ?><br /> <br />
     <form action="publication_visa_global.php" method="post" name="form3" id="form3">
            <div align="center">  
              <input type="hidden" name="MM_update3" value="form3">
              <input name="submit3" type="submit" class="vacances"  value="Ne pas afficher ce visa sur les cahiers en mode Consultation El&egrave;ves et Parents">
            </div>
          </form> 
          <?php
  };
  ?><br /> <br />
        </td>
      </tr>
    </table> <br />

    <p><a href="direction.php">Retour au Menu Responsable Etablissement</a> </p>
  </div>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsPublier2);

?>
