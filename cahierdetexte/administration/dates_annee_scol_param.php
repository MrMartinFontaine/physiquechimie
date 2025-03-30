<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
	$time_date1=$_POST['date1'];
	$time_date2=$_POST['date2'];
	$time_date11=mktime(0,0,0,substr($time_date1,3,2),substr($time_date1,0,2),substr($time_date1,6,4));//H,Mn,Sec,mois,jour,annee
	$time_date22=mktime(0,0,0,substr($time_date2,3,2),substr($time_date2,0,2),substr($time_date2,6,4));//H,Mn,Sec,mois,jour,annee
	
	if (abs($time_date22-$time_date11)/(24*3600)<=357) {
		
		$date_debut_annee = GetSQLValueString($_POST['date1'], "text");
		$query_write = "UPDATE `cdt_params` SET `param_val`=$date_debut_annee WHERE `param_nom`='date_debut_annee';";
		$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
		
		$date_fin_annee = GetSQLValueString($_POST['date2'], "text");
		$query_write = "UPDATE `cdt_params` SET `param_val`=$date_fin_annee WHERE `param_nom`='date_fin_annee';";
		$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
		
		$insertGoTo = "index.php";
		header(sprintf("Location: %s", $insertGoTo));
		
	} else {
		?>
		<script language="JavaScript" type="text/JavaScript">
		
                alert("Vos choix de dates ne conviennent pas car leur \351cart d\351passe les 357 jours autoris\351s.");
                
                
                </script>
                <?php
        }
};
if (isset($_POST['date1'])) {$date_debut_annee=$_POST['date1'];} else {
	$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_debut_annee' LIMIT 1;";
	$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
	$row = mysqli_fetch_row($result_read);
	$date_debut_annee = $row[0];
	mysqli_free_result($result_read);
};

if (isset($_POST['date2'])) {$date_fin_annee=$_POST['date2'];} else {
	$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_fin_annee' LIMIT 1;";
	$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
	$row = mysqli_fetch_row($result_read);
	$date_fin_annee = $row[0];
	mysqli_free_result($result_read);
};

?>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Dates de l'ann&eacute;e scolaire en cours";
require_once "../templates/default/header.php";
?>
<p align="center"> <br />
<blockquote>
<fieldset style="width : 100%">
<form method="post" name="form1" action="dates_annee_scol_param.php">
<p>
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
</p>
<p>&nbsp;</p>
<p>L'ann&eacute;e scolaire en cours est d&eacute;finie 
du&nbsp;&nbsp;
<input name='date1' type='text' id='date1' value="<?php echo $date_debut_annee; ?>" size="10"/>
&nbsp;au&nbsp;&nbsp;
<input  name='date2' type='text' id='date2' value="<?php echo $date_fin_annee; ?>" size="10" />
<input type="hidden" name="MM_insert" value="form1">
</p>
<p>&nbsp;</p>
<p>
<input type="submit" name="Submit" value="Enregistrer">
</p>
<p>&nbsp;             </p>
</form>
</p>
</fieldset>
</blockquote>
</p>
<p align="left">&nbsp;</p>
<p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
<p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
