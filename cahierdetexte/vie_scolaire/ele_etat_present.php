<?php 
include "../authentification/authcheck.php" ;

if (($_SESSION['droits']<>3)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');


if (!isset($_GET['date1'])){
	$datetoday=date('Ymd');$date1_form=date('d/m/Y');$jourtoday= jour_semaine(date('d/m/Y'));$date_present=date('Y-m-d');
	} 
else {
	$datetoday=substr($_GET['date1'],6,4).substr($_GET['date1'],3,2).substr($_GET['date1'],0,2);
	$date1_form= $_GET['date1'];$jourtoday= jour_semaine($_GET['date1']);
	$date_present=substr($_GET['date1'],6,4).'-'.substr($_GET['date1'],3,2).'-'.substr($_GET['date1'],0,2);
};


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<link media="all" type="text/css" href="../styles/jquery-ui.css" rel="stylesheet">
<script type="text/javascript" src="../jscripts/utils.js"></script>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>

</HEAD>
<BODY>
<DIV id=page>
  <p>
    <?php 
$header_description="Etat de la liste des &eacute;l&egrave;ves pr&eacute;sents au CDI";
require_once "../templates/default/header.php";
?>
  </p>
  <table width="95%" border="0" align="center" class="tab_detail_gris">
<tr>
<td>
<form name="frm" method="GET" action="ele_etat_present.php">
<script>
$(function() {
        	$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
        	$.datepicker.setDefaults($.datepicker.regional['fr']);
        	$('#date1').datepicker({firstDay:1});
});
</script>
<p align="center" class="Style13">Etat des pr&eacute;sents au CDI le &nbsp;
  <input name='date1' type='text' id='date1' value="<?php echo $date1_form;?>" size="10"/>
<input name="submit" type="submit" value="S&eacute;lectionner"/>
</p>
</form></td>

<td><div align="right"><a href="  <?php 
if ($_SESSION['droits']==3){echo 'vie_scolaire.php';};
if ($_SESSION['droits']==8){echo '../enseignant/enseignant.php';};
?>"><img src="../images/home-menu.gif" border="0"></a></div></td>
</tr>
</table>
  <?php 
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsele_liste = "SELECT * FROM ele_present,ele_liste WHERE  ID_ele= eleve_ID AND substring(date_heure,1,10)='".$date_present."' ORDER BY classe_ele,nom_ele,prenom_ele,date_heure ASC";
$Rsele_liste = mysqli_query($conn_cahier_de_texte, $query_Rsele_liste) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsele_liste = mysqli_fetch_assoc($Rsele_liste);
$totalRows_Rsele_liste = mysqli_num_rows($Rsele_liste);

if($totalRows_Rsele_liste>0){
?>
 <br/><br/>  
 <table border="0" align="center">
    <tr>
      <td class="Style6">Classe&nbsp;</td>
      <td class="Style6"><div align="center">Nom </div></td>
      <td class="Style6"><div align="center" >Pr&eacute;nom&nbsp;</div></td>
      <td class="Style6">Pr&eacute;sence&nbsp; </td>
      <td class="Style6">Travail&nbsp;</td>
	 
    </tr>
	

<?php


do{
?>




      <tr>
        <td class="tab_detail_gris"><div align="left"><?php echo $row_Rsele_liste['classe_ele'];?></div></td>
        <td class="tab_detail_gris"><div align="left"><?php echo $row_Rsele_liste['nom_ele']; ?></div></td>
        <td class="tab_detail_gris"><div align="left"><?php echo $row_Rsele_liste['prenom_ele']; ?></div></td>
        <td class="tab_detail_gris"><div align="left"><?php echo $row_Rsele_liste['heure_debut'];
		if ($row_Rsele_liste['heure_fin']<>'00:00'){ echo ' - '.$row_Rsele_liste['heure_fin']; };?></div></td>
        <td class="tab_detail_gris"><div align="left"><?php echo $row_Rsele_liste['travail']; ?></div></td>	

      </tr>



  
  <?php 
} while ($row_Rsele_liste = mysqli_fetch_assoc($Rsele_liste)); 
  ?>
  </table>
  <?php 
}
else {
echo "<p><br /><br />Aucun &eacute;l&egrave;ve n'a &eacute;t&eacute; point&eacute; pr&eacute;sent ce jour au CDI.<br /><br /></p>";


}; 
	  ?>

	   <?php 
  if ($_SESSION['droits']==8){?><p align="center"><a href="../enseignant/enseignant.php">Retour au Menu Enseignant</a></p><?php };
  if ($_SESSION['droits']==3){?><p align="center"><a href="vie_scolaire.php">Retour au Menu Vie scolaire</a></p>  
  <?php };
  ?>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsClasse);
?>
