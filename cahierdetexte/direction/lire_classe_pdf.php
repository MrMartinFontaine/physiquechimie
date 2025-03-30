<?php 
session_start();
if ($_SESSION['droits']<>4) { header("Location: ../index.php");exit;};

require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
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
<!--
.Style70 {
	color: #0000CC;
	font-weight: bold;
	font-size: 12px;
}
-->
</style>

<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>

<script language="JavaScript" type="text/JavaScript">
<!--
function MM_callJS(jsStr) { //v2.0
  return eval(jsStr)
}
//-->
</script>

</HEAD>
<BODY>
<DIV id=page>

<?php 
$header_description="Edition des cahiers de textes au format PDF et HTML";
require_once "../templates/default/header.php";

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMinDate = "SELECT *  FROM cdt_agenda WHERE prof_ID > 0 and code_date > 0 ORDER BY code_date ASC LIMIT  1 ";
$RsMinDate = mysqli_query($conn_cahier_de_texte, $query_RsMinDate) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMinDate = mysqli_fetch_assoc($RsMinDate);
$totalRows_RsMinDate= mysqli_num_rows($RsMinDate);

$today=date('Ymd').'9'; $today_form=date('j/m/Y');
if (isset($_POST['date2'])){$date2=substr($_POST['date2'],6,4).substr($_POST['date2'],3,2).substr($_POST['date2'],0,2).'9';} else {$date2=$today;};

if (isset($_POST['date1'])){$date1=substr($_POST['date1'],6,4).substr($_POST['date1'],3,2).substr($_POST['date1'],0,2).'1';}
else  {
  $mois_tmp=substr($date2,4,2)-1;
  $date_tmp=substr($date2,0,4). $mois_tmp.substr($date2,6,2).'1';
  if ($date_tmp>$row_RsMinDate['code_date']){ $date1=$date_tmp;} else {$date1=$row_RsMinDate['code_date'];};
  };

$date1_form=substr($date1,6,2).'/'.substr($date1,4,2).'/'.substr($date1,0,4);
$date2_form=substr($date2,6,2).'/'.substr($date2,4,2).'/'.substr($date2,0,4);

?>
<p>
<table width="70%"  border="0" align="center" cellpadding="0" cellspacing="0">
  <tr class="lire_cellule_2">
    <td class="no_imprime">
    <form name="frm" method="POST" action="lire_classe_pdf.php">

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
		  Cahier de textes du&nbsp;&nbsp;
          <input name='date1' type='text' id='date1' value="<?php echo $date1_form;?>" size="10"/>
          &nbsp;au&nbsp;&nbsp;
          <input  name='date2' type='text' id='date2' value="<?php echo $date2_form;?>" size="10" />
          &nbsp;&nbsp;&nbsp;
        <input name="submit" type="submit" value="Actualiser"/>
      </form></td>
  </tr>
</table>
</p>
<p>&nbsp;</p>
<table width="70%" border="0" align="center" class="tab_detail">
  <tr>
    <td>&nbsp;&nbsp;</td>
    <td><img src="../images/pdf2.jpg" alt="Cr&eacute;er pdf" title="Cr&eacute;er pdf" border="0"></td>
    <td>G&eacute;n&egrave;re un fichier pdf</td>
    <td>&nbsp;&nbsp;</td>
    <td><span class="Style70">HTML</span></td>
    <td>Affiche une page Web facilement imprimable </td>
  </tr>
</table>
<p>&nbsp; </p>
<table border="0" align="center">
   <tr>
     <td class="Style6"><div align="center">NOM DE LA CLASSE&nbsp;&nbsp;</div></td>
     <td class="Style6"><div align="center">Edition&nbsp;&nbsp;</div></td>
     <td class="Style6"><div align="center">Edition&nbsp;&nbsp;</div></td>
   </tr>
    <?php do { ?>
   <tr>
     <td class="tab_detail_gris"><div align="left"><?php echo $row_RsClasse['nom_classe']; ?></div></td>
     <td class="tab_detail_gris"><div align="center"><a href='classe_pdf.php?classe_ID=<?php echo $row_RsClasse['ID_classe']; ?>&groupe=Classe%20enti%E8re&date1=<?php echo substr($date1_form,6,4).substr($date1_form,3,2).substr($date1_form,0,2).'1';?>&date2=<?php echo substr($date2_form,6,4).substr($date2_form,3,2).substr($date2_form,0,2).'1';?>&ordre=up'><img src="../images/pdf2.jpg" alt="Cr&eacute;er pdf" title="Cr&eacute;er pdf" border="0"></a></div></td>
     <td class="tab_detail_gris"><div align="center"><a href='classe_html.php?classe_ID=<?php echo $row_RsClasse['ID_classe']; ?>&groupe=Classe%20enti%E8re&date1=<?php echo substr($date1_form,6,4).substr($date1_form,3,2).substr($date1_form,0,2).'1';?>&date2=<?php echo substr($date2_form,6,4).substr($date2_form,3,2).substr($date2_form,0,2).'1';?>&ordre=up'><strong>HTML</strong></a></div></td>
   </tr>
     <?php } while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)); ?>
</table>
<p align="center"><a href="direction.php">Retour au Menu Responsable Etablissement</a></p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsMinDate);
?>
