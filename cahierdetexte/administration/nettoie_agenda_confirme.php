<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;}; 
require_once('../Connections/conn_cahier_de_texte.php'); 

if ( (isset($_POST['Submit2']))&& (isset($_POST['date_form2'])) && ($_POST['date_form2'] != "")) {

$code_date_supprime=substr($_POST['date_form2'],6,4).substr($_POST['date_form2'],3,2).substr($_POST['date_form2'],0,2).'0';

//select des anciens enregistrements
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsSup = sprintf("SELECT * FROM `cdt_agenda` WHERE cdt_agenda.code_date<%s ",intval($code_date_supprime));
$RsSup = mysqli_query($conn_cahier_de_texte, $query_RsSup) or die(mysqli_error($conn_cahier_de_texte));
$row_RsSup = mysqli_fetch_assoc($RsSup);
$totalRows_RsSup = mysqli_num_rows($RsSup);

if ($totalRows_RsSup>0) {
// boucle suppression dans cdt_agenda et cdt_fichiers_joints et cdt_travail
   do {
$deleteSQL = sprintf("DELETE FROM `cdt_agenda` WHERE `code_date` ='%s'",$row_RsSup['code_date']);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte)); 
 
$deleteSQL2 = sprintf("DELETE FROM `cdt_fichiers_joints` WHERE `agenda_ID` =%s",$row_RsSup['ID_agenda']);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);  
  $Result2 = mysqli_query($conn_cahier_de_texte, $deleteSQL2) or die(mysqli_error($conn_cahier_de_texte));
  
$deleteSQL3 = sprintf("DELETE FROM `cdt_travail` WHERE `agenda_ID` =%s",$row_RsSup['ID_agenda']);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);  
  $Result3 = mysqli_query($conn_cahier_de_texte, $deleteSQL3) or die(mysqli_error($conn_cahier_de_texte));
 
  } while ($row_RsSup = mysqli_fetch_assoc($RsSup)); 
//fin boucle
};
mysqli_free_result($RsSup);

  $deleteGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
  }

header(sprintf("Location: %s", $deleteGoTo));
}
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
$header_description="Suppression des activit&eacute;s";
require_once "../templates/default/header.php";
?>
  <blockquote>
    <blockquote>
      <blockquote>&nbsp;
	  <?php
	  if ((isset($_POST['date_form'])) && ($_POST['date_form'] != "")) {
echo 'Vous avez demand&eacute; la suppression des s&eacute;ances ant&eacute;rieures au '.$_POST['date_form']; ?>
	    <form name="form1" method="post" action="nettoie_agenda_confirme.php">
          Confimez la suppression de ces s&eacute;ances 
		  <input name="date_form2" type="hidden" value="<? echo $_POST['date_form']; ?>">
          <input type="submit" name="Submit2" value="Valider">
        </form><?php } else {echo 'Date de suppression non renseign&eacute;e' ;};?>
      </blockquote>
    </blockquote>
  </blockquote>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
