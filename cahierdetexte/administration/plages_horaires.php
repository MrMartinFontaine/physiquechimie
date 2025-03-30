<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") ) {

$updateSQL = "UPDATE cdt_plages_horaires SET h1 = '".$_POST['1h1'] ."', mn1 ='".$_POST['1mn1'] ."', h2  = '" .$_POST['1h2'] . "', mn2 ='". $_POST['1mn2']."'   WHERE ID_plage = 1";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));

$updateSQL = "UPDATE cdt_plages_horaires SET h1 = '".$_POST['2h1'] ."', mn1 ='".$_POST['2mn1'] ."', h2  = '" .$_POST['2h2'] . "', mn2 ='". $_POST['2mn2']."'   WHERE ID_plage =2";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte)); 

$updateSQL = "UPDATE cdt_plages_horaires SET h1 = '".$_POST['3h1'] ."', mn1 ='".$_POST['3mn1'] ."', h2  = '" .$_POST['3h2'] . "', mn2 ='". $_POST['3mn2']."'   WHERE ID_plage =3";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));  

$updateSQL = "UPDATE cdt_plages_horaires SET h1 = '".$_POST['4h1'] ."', mn1 ='".$_POST['4mn1'] ."', h2  = '" .$_POST['4h2'] . "', mn2 ='". $_POST['4mn2']."'   WHERE ID_plage =4";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));  

$updateSQL = "UPDATE cdt_plages_horaires SET h1 = '".$_POST['5h1'] ."', mn1 ='".$_POST['5mn1'] ."', h2  = '" .$_POST['5h2'] . "', mn2 ='". $_POST['5mn2']."'   WHERE ID_plage =5";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte)); 

$updateSQL = "UPDATE cdt_plages_horaires SET h1 = '".$_POST['6h1'] ."', mn1 ='".$_POST['6mn1'] ."', h2  = '" .$_POST['6h2'] . "', mn2 ='". $_POST['6mn2']."'   WHERE ID_plage =6";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));  

$updateSQL = "UPDATE cdt_plages_horaires SET h1 = '".$_POST['7h1'] ."', mn1 ='".$_POST['7mn1'] ."', h2  = '" .$_POST['7h2'] . "', mn2 ='". $_POST['7mn2']."'   WHERE ID_plage =7";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte)); 

$updateSQL = "UPDATE cdt_plages_horaires SET h1 = '".$_POST['8h1'] ."', mn1 ='".$_POST['8mn1'] ."', h2  = '" .$_POST['8h2'] . "', mn2 ='". $_POST['8mn2']."'   WHERE ID_plage =8";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte)); 

$updateSQL = "UPDATE cdt_plages_horaires SET h1 = '".$_POST['9h1'] ."', mn1 ='".$_POST['9mn1'] ."', h2  = '" .$_POST['9h2'] . "', mn2 ='". $_POST['9mn2']."'   WHERE ID_plage =9";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));  

$updateSQL = "UPDATE cdt_plages_horaires SET h1 = '".$_POST['10h1'] ."', mn1 ='".$_POST['10mn1'] ."', h2  = '" .$_POST['10h2'] . "', mn2 ='". $_POST['10mn2']."'   WHERE ID_plage =10";

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte)); 

$updateSQL = "UPDATE cdt_plages_horaires SET h1 = '".$_POST['11h1'] ."', mn1 ='".$_POST['11mn1'] ."', h2  = '" .$_POST['11h2'] . "', mn2 ='". $_POST['11mn2']."'   WHERE ID_plage =11";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));  

$updateSQL = "UPDATE cdt_plages_horaires SET h1 = '".$_POST['12h1'] ."', mn1 ='".$_POST['12mn1'] ."', h2  = '" .$_POST['12h2'] . "', mn2 ='". $_POST['12mn2']."'   WHERE ID_plage =12";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte)); 


  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsplage = "SELECT * FROM cdt_plages_horaires ORDER BY ID_plage ASC";
$Rsplage = mysqli_query($conn_cahier_de_texte, $query_Rsplage) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsplage = mysqli_fetch_assoc($Rsplage);
$totalRows_Rsplage = mysqli_num_rows($Rsplage);
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
//-->
</script>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
  <?php 
$header_description="Gestion des plages horaires";
require_once "../templates/default/header.php";
?>
  <HR>
  <form  method="post"  name="form1" action="<?php echo $editFormAction; ?>">
    <p>Cette planification permettra de proposer des valeurs par d&eacute;faut aux enseignants lors de la saisie de leur emploi du temps.</p>
    <p>Si vous utilisez l'import des emplois du temps depuis EDT ou UDT,<br>
    vos plages horaires doivent &ecirc;tre coh&eacute;rentes avec celles de ces logiciels.</p>
    <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" class="bordure">
      <tr>
        <td class="Style6">R&eacute;f</td>
        <td colspan="2" class="Style6"><div align="center">Heure de d&eacute;but </div></td>
        <td colspan="3" class="Style6"><div align="center">Heure de fin </div></td>
      </tr>
      <?php do { ?>
        <tr class="menu_detail">
          <td valign="middle" class="menu_detail" ><?php echo 'Plage N&deg; '.$row_Rsplage['ID_plage']; ?></td>
          <td valign="middle" class="menu_detail" > de
            <select name="<?php echo $row_Rsplage['ID_plage'];?>h1">
              <option value="07"<?php if ($row_Rsplage['h1']=='07') {echo "SELECTED";} ?>>07</option>
              <option value="08"<?php if ($row_Rsplage['h1']=='08') {echo "SELECTED";} ?>>08</option>
              <option value="09"<?php if ($row_Rsplage['h1']=='09') {echo "SELECTED";} ?>>09</option>
              <option value="10"<?php if ($row_Rsplage['h1']=='10') {echo "SELECTED";} ?>>10</option>
              <option value="11"<?php if ($row_Rsplage['h1']=='11') {echo "SELECTED";} ?>>11</option>
              <option value="12"<?php if ($row_Rsplage['h1']=='12') {echo "SELECTED";} ?>>12</option>
              <option value="13"<?php if ($row_Rsplage['h1']=='13') {echo "SELECTED";} ?>>13</option>
              <option value="14"<?php if ($row_Rsplage['h1']=='14') {echo "SELECTED";} ?>>14</option>
              <option value="15"<?php if ($row_Rsplage['h1']=='15') {echo "SELECTED";} ?>>15</option>
              <option value="16"<?php if ($row_Rsplage['h1']=='16') {echo "SELECTED";} ?>>16</option>
              <option value="17"<?php if ($row_Rsplage['h1']=='17') {echo "SELECTED";} ?>>17</option>
              <option value="18"<?php if ($row_Rsplage['h1']=='18') {echo "SELECTED";} ?>>18</option>
              <option value="19"<?php if ($row_Rsplage['h1']=='19') {echo "SELECTED";} ?>>19</option>
              <option value="20"<?php if ($row_Rsplage['h1']=='20') {echo "SELECTED";} ?>>20</option>
              <option value="21"<?php if ($row_Rsplage['h1']=='21') {echo "SELECTED";} ?>>21</option>
            </select>
            h </td>
          <td valign="middle" class="menu_detail" ><select name="<?php echo $row_Rsplage['ID_plage'];?>mn1">
              <option value="00"<?php if ($row_Rsplage['mn1']=='00') {echo "SELECTED";} ?>>00</option>
              <option value="05"<?php if ($row_Rsplage['mn1']=='05') {echo "SELECTED";} ?>>05</option>
              <option value="10"<?php if ($row_Rsplage['mn1']=='10') {echo "SELECTED";} ?>>10</option>
              <option value="15"<?php if ($row_Rsplage['mn1']=='15') {echo "SELECTED";} ?>>15</option>
              <option value="20"<?php if ($row_Rsplage['mn1']=='20') {echo "SELECTED";} ?>>20</option>
              <option value="25"<?php if ($row_Rsplage['mn1']=='25') {echo "SELECTED";} ?>>25</option>
              <option value="30"<?php if ($row_Rsplage['mn1']=='30') {echo "SELECTED";} ?>>30</option>
              <option value="35"<?php if ($row_Rsplage['mn1']=='35') {echo "SELECTED";} ?>>35</option>
              <option value="40"<?php if ($row_Rsplage['mn1']=='40') {echo "SELECTED";} ?>>40</option>
              <option value="45"<?php if ($row_Rsplage['mn1']=='45') {echo "SELECTED";} ?>>45</option>
              <option value="50"<?php if ($row_Rsplage['mn1']=='50') {echo "SELECTED";} ?>>50</option>
              <option value="55"<?php if ($row_Rsplage['mn1']=='55') {echo "SELECTED";} ?>>55</option>
            </select>
            min </td>
          <td valign="middle" class="menu_detail" ><p>&agrave;</p></td>
          <td valign="middle" class="menu_detail" ><select name="<?php echo $row_Rsplage['ID_plage'];?>h2">
              <option value="07"<?php if ($row_Rsplage['h2']=='07') {echo "SELECTED";} ?>>07</option>
              <option value="08"<?php if ($row_Rsplage['h2']=='08') {echo "SELECTED";} ?>>08</option>
              <option value="09"<?php if ($row_Rsplage['h2']=='09') {echo "SELECTED";} ?>>09</option>
              <option value="10"<?php if ($row_Rsplage['h2']=='10') {echo "SELECTED";} ?>>10</option>
              <option value="11"<?php if ($row_Rsplage['h2']=='11') {echo "SELECTED";} ?>>11</option>
              <option value="12"<?php if ($row_Rsplage['h2']=='12') {echo "SELECTED";} ?>>12</option>
              <option value="13"<?php if ($row_Rsplage['h2']=='13') {echo "SELECTED";} ?>>13</option>
              <option value="14"<?php if ($row_Rsplage['h2']=='14') {echo "SELECTED";} ?>>14</option>
              <option value="15"<?php if ($row_Rsplage['h2']=='15') {echo "SELECTED";} ?>>15</option>
              <option value="16"<?php if ($row_Rsplage['h2']=='16') {echo "SELECTED";} ?>>16</option>
              <option value="17"<?php if ($row_Rsplage['h2']=='17') {echo "SELECTED";} ?>>17</option>
              <option value="18"<?php if ($row_Rsplage['h2']=='18') {echo "SELECTED";} ?>>18</option>
              <option value="19"<?php if ($row_Rsplage['h2']=='19') {echo "SELECTED";} ?>>19</option>
              <option value="20"<?php if ($row_Rsplage['h2']=='20') {echo "SELECTED";} ?>>20</option>
              <option value="21"<?php if ($row_Rsplage['h2']=='21') {echo "SELECTED";} ?>>21</option>
            </select>
            h</td>
          <td valign="middle" class="menu_detail" ><select name="<?php echo $row_Rsplage['ID_plage'];?>mn2">
              <option value="00"<?php if ($row_Rsplage['mn2']=='00') {echo "SELECTED";} ?>>00</option>
              <option value="05"<?php if ($row_Rsplage['mn2']=='05') {echo "SELECTED";} ?>>05</option>
              <option value="10"<?php if ($row_Rsplage['mn2']=='10') {echo "SELECTED";} ?>>10</option>
              <option value="15"<?php if ($row_Rsplage['mn2']=='15') {echo "SELECTED";} ?>>15</option>
              <option value="20"<?php if ($row_Rsplage['mn2']=='20') {echo "SELECTED";} ?>>20</option>
              <option value="25"<?php if ($row_Rsplage['mn2']=='25') {echo "SELECTED";} ?>>25</option>
              <option value="30"<?php if ($row_Rsplage['mn2']=='30') {echo "SELECTED";} ?>>30</option>
              <option value="35"<?php if ($row_Rsplage['mn2']=='35') {echo "SELECTED";} ?>>35</option>
              <option value="40"<?php if ($row_Rsplage['mn2']=='40') {echo "SELECTED";} ?>>40</option>
              <option value="45"<?php if ($row_Rsplage['mn2']=='45') {echo "SELECTED";} ?>>45</option>
              <option value="50"<?php if ($row_Rsplage['mn2']=='50') {echo "SELECTED";} ?>>50</option>
              <option value="55"<?php if ($row_Rsplage['mn2']=='55') {echo "SELECTED";} ?>>55</option>
            </select>
            min </td>
        </tr>
        <?php } while ($row_Rsplage = mysqli_fetch_assoc($Rsplage)); ?>
    </table>
    <p>&nbsp; </p>
    <p>
      <input type="submit" value="Enregistrer cette programmation">
      <input type="hidden" name="MM_insert" value="form1">
    </p>
  </form>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($Rsplage);
?>
