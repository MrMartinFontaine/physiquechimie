<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

	$code_date=substr($_POST['date_debut'],6,4).substr($_POST['date_debut'],3,2).substr($_POST['date_debut'],0,2).'0';
	$date_debut=$_POST['date_debut'];
	$date_fin=$_POST['date_fin'];

	$updateSQL = sprintf("UPDATE cdt_agenda 
				SET theme_activ=%s , heure_debut=%s , heure_fin=%s, code_date=%s 
				WHERE ID_agenda=%u",
				GetSQLValueString($_POST['libelle'], "text"),
				GetSQLValueString($date_debut, "text"),
				GetSQLValueString($date_fin, "text"),
				GetSQLValueString($code_date, "text"),
				GetSQLValueString($_GET['ID_agenda'], "int")
				);
				
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));

	$updateGoTo = "even_ajout.php";
	if (isset($_SERVER['QUERY_STRING'])) {
	$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
	$updateGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $updateGoTo));
}

$ID_RsModifEven = "0";
if (isset($_GET['ID_agenda'])) {
	$ID_RsModifEven = (get_magic_quotes_gpc()) ? $_GET['ID_agenda'] : addslashes($_GET['ID_agenda']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifEven = sprintf("SELECT * FROM cdt_agenda WHERE cdt_agenda.ID_agenda=%u", $ID_RsModifEven);
$RsModifEven = mysqli_query($conn_cahier_de_texte, $query_RsModifEven) or die(mysqli_error($conn_cahier_de_texte));
$row_RsModifEven = mysqli_fetch_assoc($RsModifEven);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><link media="screen" href="../styles/style_default.css" type=text/css rel="stylesheet">
<link media="screen" href="../templates/default/header_footer.css" type=text/css rel="stylesheet">
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>
</head>
<body>
<div id="page">
  <?php 
$header_description="Gestion des p&eacute;riodes de vacances et autres p&eacute;riodes non travaill&eacute;es";
require_once "../templates/default/header.php";
?>
  <hr />
  <p align="center">&nbsp;</p>
  <blockquote>
    <p align="left"><br />
      <strong>Attention,</strong> <strong>la date de d&eacute;but</strong> correspond au premier jour de vacances... 
      Si les vacances d&eacute;butent un Mercredi midi, la date de d&eacute;but sera celle du Jeudi 
      (de fa&ccedil;on &agrave; pouvoir saisir les cours du Mercredi matin). De la m&ecirc;me fa&ccedil;on, <strong>la date de fin</strong> correspond au dernier jour des vacances et non &agrave; la date de reprise. 
      Ainsi, si on reprend le lundi, la date de fin sera celle du dimanche.</p>
    <p align="left">&nbsp;</p>
    <p align="left">
      <script>
	$(function() {
	    $.datepicker.setDefaults($.datepicker.regional['fr']);
                var dates = $( "#date_debut, #date_fin" ).datepicker({
                        defaultDate: "+1w",
                        changeMonth: true,
                        numberOfMonths: 1,
						firstDay:1,
                        onSelect: function( selectedDate ) {
                                var option = this.id == "date_debut" ? "minDate" : "maxDate",
                                        instance = $( this ).data( "datepicker" ),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
				dates.not( this ).datepicker( "option", option, date );
			}
		});
	});
	
function formfocus() {
	document.form1.libelle.focus()
	document.form1.libelle.select()
}

function verif_saisie()
			{
			if(document.form1.libelle.value == "")  {
				alert("Vous n'avez pas saisi le libell\351 de la p\351riode.");
				document.form1.libelle.focus();
				return false;
				}
			if(document.form1.date_debut.value == "")  {
				alert("Vous n'avez pas saisi la date du d\351but de la p\351riode. ");
				document.form1.date_debut.focus();
				return false;
				}
			if(document.form1.date_fin.value == "")  {
				alert("Vous n'avez pas saisi la date de la fin de la p\351riode. ");
				document.form1.date_fin.focus();
				return false;
				}
			}
</script>
    </p>
  </blockquote>
  <form method="post"  onLoad= "formfocus()" name="form1" action="<?php echo $editFormAction; ?>" onSubmit="return verif_saisie()">
    <table align="center">
      <tr valign="baseline">
        <td width="424"><table width="100%"  border="0">
            <tr>
              <th width="24%" class="tab_detail_gris" scope="col"><div align="left">Libell&eacute;</div></th>
              <th colspan="3" class="tab_detail_gris" scope="col"> <div align="left">
                  <input type="text" name="libelle" id="libelle" value="<?php echo $row_RsModifEven['theme_activ'];?>" size="32">
                </div></th>
            </tr>
            <tr>
              <th class="tab_detail_gris" scope="row"><div align="left">Date de d&eacute;but </div></th>
              <td width="13%" class="tab_detail_gris"><div align="left">
                  <input name='date_debut' type='text' id='date_debut' value="<?php echo $row_RsModifEven['heure_debut'];?>" size="10"/>
                </div></td>
            </tr>
            <tr>
              <th class="tab_detail_gris" scope="row"><div align="left">Date de fin </div></th>
              <td class="tab_detail_gris"><div align="left">
                  <input name='date_fin' type='text' id='date_fin' value="<?php echo $row_RsModifEven['heure_fin'];?>" size="10"/>
                </div></td>
            </tr>
            <tr>
              <th class="tab_detail_gris" scope="row">&nbsp;</th>
              <td colspan="3" class="tab_detail_gris"><div align="left">
                  <input name="submit" type="submit" value="Enregistrer les modifications" onclick="return verif_dates();">
                </div></td>
            </tr>
          </table></td>
      </tr>
      <tr valign="baseline">
        <td><div align="center">
            <p><a href="http://www.education.gouv.fr/pid25058/le-calendrier-scolaire.html" target="_blank">Consulter  les dates de vacances sur le Web</a></p>
            <br />
          </div></td>
      </tr>
    </table>
    <div align="center">
      <input type="hidden" name="MM_update" value="form1">
    </div>
  </form>
  <script type="text/javascript"> formfocus(); </script>
  <p>&nbsp;</p>
  <a href="even_ajout.php">Annuler</a>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</div>
</body>
</html>
<?php
mysqli_free_result($RsModifEven);
?>
