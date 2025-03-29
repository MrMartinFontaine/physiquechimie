<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>7)){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');



if (!isset($_GET['date1'])){$datetoday=date('Ymd');$date1_form=date('d/m/Y');$jourtoday= jour_semaine(date('d/m/Y'));;
} else {
$datemini=substr($_GET['date1'],6,4).substr($_GET['date1'],3,2).substr($_GET['date1'],0,2);
$date1_form= $_GET['date1'];$jourtoday= jour_semaine($_GET['date1']);};

if (!isset($_GET['date2'])){$datetoday=date('Ymd');$date2_form=date('d/m/Y');$jourtoday= jour_semaine(date('d/m/Y'));;
} else {
$datemaxi=substr($_GET['date2'],6,4).substr($_GET['date2'],3,2).substr($_GET['date2'],0,2);
$date2_form= $_GET['date2'];$jourtoday= jour_semaine($_GET['date2']);};


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT distinct classe FROM ele_absent ORDER BY classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<link media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />

<style type="text/css">
.Style70 {color: #FFFFFF}

</style>

<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>

</head>

<body>
<table width="100%" border="0" class="tab_detail_gris" > <tr>
      <td> 
<form name="frm" method="GET" action="absence_perso_1_2_periode.php?">
<script>
	$(function() {
	    $.datepicker.setDefaults($.datepicker.regional['fr']);
		var dates = $( "#date1, #date2" ).datepicker({
			defaultDate: "+1w",
			changeMonth: true,
			numberOfMonths: 1,
			firstDay:1,
			onSelect: function( selectedDate ) {
				var option = this.id == "date1" ? "minDate" : "maxDate",
					instance = $( this ).data( "datepicker" ),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
				dates.not( this ).datepicker( "option", option, date );
			}
		});
	});
</script> 


  <p align="center" class="Style13">Etat des oublis du  &nbsp;
    <?php // echo $jourtoday. ' '.date('d-m-Y');?>
    <input name='date1' type='text' id='date1' value="<?php echo $date1_form;?>" size="10"/>
	&nbsp;au&nbsp;
    <input name='date2' type='text' id='date2' value="<?php echo $date2_form;?>" size="10"/>
    &nbsp;&nbsp;
    <select name="classe" id="classe">
      <option value="-1">S&eacute;lectionner la classe</option>
      <?php do { ?>
      <option value="<?php echo $row_RsClasse['classe']?>"  <?php if ((isset($_GET['classe']))&&($_GET['classe']==$row_RsClasse['classe'])){echo 'selected=" selected"';};?>><?php echo $row_RsClasse['classe']?></option>
      <?php	} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)) ;
  $rows = mysqli_num_rows($RsClasse);
  if($rows > 0) { mysqli_data_seek($RsClasse, 0); $row_RsClasse = mysqli_fetch_assoc($RsClasse); };
?>
    </select>
    <input name="submit" type="submit" value="Valider"/>
  </p>
</form>
</td>
      <td><div align="right"><a href="  <?php 
  if ($_SESSION['droits']==2 ){echo '../enseignant/enseignant.php';};
  if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'vie_scolaire.php';};
  if ($_SESSION['droits']==4){echo '../direction/direction.php';};
  ?>"><img src="../images/home-menu.gif" border="0"></a></div></td>
    </tr>
	
  </table>
<?php 
   
   
if ((isset($_GET['classe']))&&($_GET['classe']!='-1')){ 


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsabsent = sprintf("
SELECT nom_ele,prenom_ele,classe,count(eleve_ID),retard,eleve_ID,perso1,perso2 FROM ele_absent,ele_liste  
WHERE 
(perso1='O' OR perso2='O' )
AND
ID_ele=eleve_ID 
AND
classe='%s'
AND SUBSTRING(ele_absent.code_date,1,8)<='%s'
AND SUBSTRING(ele_absent.code_date,1,8)>='%s'
GROUP BY eleve_ID
ORDER BY classe,nom_ele,prenom_ele
",$_GET['classe'],$datemaxi,$datemini);

$Rsabsent = mysqli_query($conn_cahier_de_texte, $query_Rsabsent) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsabsent = mysqli_fetch_assoc($Rsabsent);
$totalRows_Rsabsent = mysqli_num_rows($Rsabsent);

if ($totalRows_Rsabsent>0){
?>
<table border="0" align="center"  >
  <tr>
    <td class="Style6" ><div align="left">Nom</div></td>
    <td class="Style6" ><div align="left">Pr&eacute;nom &nbsp;</div></td>
    <td class="Style6" ><div align="left">Nb d'oublis de Carnet &nbsp;</div></td>
	<td class="Style6" ><div align="left">Nb d'oublis de mat&eacute;riel &nbsp;</div></td>

  </tr>
  <?php
  $alterne=1;
do {
echo '<tr>';
if($alterne>0){echo '<td  class="tab_detail_gris">';}else {echo '<td  class="tab_detail_rose">';};
echo $row_Rsabsent['nom_ele']. '</td>';
if($alterne>0){echo '<td  class="tab_detail_gris">';}else {echo '<td  class="tab_detail_rose">';};echo $row_Rsabsent['prenom_ele']. '</td>';


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsperso1 = sprintf("
SELECT count(eleve_ID) FROM ele_absent 
WHERE 
 perso1='O' 
AND
eleve_ID =%u
AND
classe='%s'
AND SUBSTRING(ele_absent.code_date,1,8)<='%s'
AND SUBSTRING(ele_absent.code_date,1,8)>='%s'
",$row_Rsabsent['eleve_ID'],$_GET['classe'],$datemaxi,$datemini);

$Rsperso1 = mysqli_query($conn_cahier_de_texte, $query_Rsperso1) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsperso1 = mysqli_fetch_assoc($Rsperso1);
$totalRows_Rsperso1 = mysqli_num_rows($Rsperso1);

if($alterne>0){echo '<td  class="tab_detail_gris">';}else {echo '<td  class="tab_detail_rose">';};echo  '&nbsp;&nbsp;&nbsp;'.$row_Rsperso1['count(eleve_ID)']. '</td>';

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsperso2 = sprintf("
SELECT count(eleve_ID) FROM ele_absent 
WHERE 
 perso2='O' 
AND
eleve_ID =%u
AND
classe='%s'
AND SUBSTRING(ele_absent.code_date,1,8)<='%s'
AND SUBSTRING(ele_absent.code_date,1,8)>='%s'
",$row_Rsabsent['eleve_ID'],$_GET['classe'],$datemaxi,$datemini);

$Rsperso2 = mysqli_query($conn_cahier_de_texte, $query_Rsperso2) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsperso2 = mysqli_fetch_assoc($Rsperso2);
$totalRows_Rsperso2 = mysqli_num_rows($Rsperso2);


if($alterne>0){echo '<td  class="tab_detail_gris">';}else {echo '<td  class="tab_detail_rose">';};
echo '&nbsp;&nbsp;&nbsp;'.$row_Rsperso2['count(eleve_ID)']. '</td>';
$alterne=$alterne*(-1);
	} while ($row_Rsabsent = mysqli_fetch_assoc($Rsabsent)); 
echo '</tr>';	

	?>
</table>
<p>&nbsp;</p>
<?php 
}
else {
echo "<p>&nbsp;</p><p align=\"center\"> Pas d'oublis point&eacute;s sur cette p&eacute;riode.</p>";
};

mysqli_free_result($Rsabsent); 
}
else {
 echo "<p>&nbsp;</p><p  class='erreur' align=\"center\"> Il vous faut s&eacute;lectionner une classe.</p>";
}
?>

<p><a href="
<?php 
if ($_SESSION['droits']==2 ){echo '../enseignant/enseignant.php';};
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'vie_scolaire.php';};
if ($_SESSION['droits']==4){echo '../direction/direction.php';};

?>
"><br>
Retour au menu
<?php
if ($_SESSION['droits']==2 ){echo ' Enseignant';};
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'Vie scolaire';};
if ($_SESSION['droits']==4){echo 'Responsable Etablissement';};
?>
</a> </p>
</body>
</html>
