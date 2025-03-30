<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}



if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] && (isset($_POST['libelle']))  == "form1")) {

	$code_date=substr($_POST['date_debut'],6,4).substr($_POST['date_debut'],3,2).substr($_POST['date_debut'],0,2).'0';
	$date_debut=$_POST['date_debut'];
	$date_fin=$_POST['date_fin'];

	$insertSQL = sprintf("INSERT INTO cdt_agenda (classe_ID,theme_activ,heure_debut,heure_fin,code_date) 
				VALUES ( %s,%s,%s,%s,%s)",
				GetSQLValueString('0', "int"),
				GetSQLValueString($_POST['libelle'], "text"),
				GetSQLValueString($date_debut, "text"),
				GetSQLValueString($date_fin, "text"),
				GetSQLValueString($code_date, "text")
				);
				
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));

	$insertGoTo = "even_ajout.php";
	if (isset($_SERVER['QUERY_STRING'])) {
		$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
		$insertGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $insertGoTo));
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsEven = "SELECT * FROM cdt_agenda WHERE classe_ID=0 AND theme_activ<>'Remplacement' ORDER BY cdt_agenda.code_date ASC";
$RsEven = mysqli_query($conn_cahier_de_texte, $query_RsEven) or die(mysqli_error($conn_cahier_de_texte));
$row_RsEven = mysqli_fetch_assoc($RsEven);
$totalRows_RsEven = mysqli_num_rows($RsEven);
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
<link media="screen" href="../styles/style_default.css" type=text/css rel="stylesheet">
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
  <p align="center">Entrez vos  dates de vacances et autres p&eacute;riodes non travaill&eacute;es - Exemple de libell&eacute; : Vacances de No&euml;l </p>
  <blockquote>
    <p align="left"><br />
      <strong>Attention,</strong> <strong>la date de d&eacute;but</strong> correspond au premier jour de vacances... 
      Si les vacances d&eacute;butent un Mercredi midi, la date de d&eacute;but sera celle du Jeudi 
      (de fa&ccedil;on &agrave; pouvoir saisir les cours du Mercredi matin). De la m&ecirc;me fa&ccedil;on, <strong>la date de fin</strong> correspond au dernier jour des vacances et non &agrave; la date de reprise. 
      Ainsi, si on reprend le lundi, la date de fin sera celle du dimanche.</p>
    <p align="left">Vos entr&eacute;es appara&icirc;tront sur tous les cahiers de textes de l'&eacute;tablissement. La saisie de s&eacute;ance  sera bien &eacute;videmment impossible sur ces p&eacute;riodes pour les enseignants. </p>
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
                  <input type="text" name="libelle" id="libelle" value="" size="32">
                </div></th>
            </tr>
            <tr>
              <th class="tab_detail_gris" scope="row"><div align="left">Date de d&eacute;but </div></th>
              <td width="13%" class="tab_detail_gris"><div align="left">
                  <input name='date_debut' type='text' id='date_debut' value="" size="10"/>
                </div></td>
            </tr>
            <tr>
              <th class="tab_detail_gris" scope="row"><div align="left">Date de fin </div></th>
              <td class="tab_detail_gris"><div align="left">
                  <input name='date_fin' type='text' id='date_fin' value="" size="10"/>
                </div></td>
            </tr>
            <tr>
              <th class="tab_detail_gris" scope="row">&nbsp;</th>
              <td colspan="3" class="tab_detail_gris"><div align="left">
                  <input name="submit" type="submit" value="Ajouter cette p&eacute;riode" onclick="return verif_dates();">
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
      <input type="hidden" name="MM_insert" value="form1">
    </div>
  </form>
  <script type="text/javascript"> formfocus(); </script>
  <?php 
if ($totalRows_RsEven > 0) {
?>
  <table border="0" align="center">
    <tr>
      <td class="Style6"><div align="center">Libell&eacute;&nbsp;&nbsp;</div></td>
      <td class="Style6"><div align="center">Date de d&eacute;but&nbsp;&nbsp;</div></td>
      <td class="Style6"><div align="center">Date de fin&nbsp;&nbsp;</div></td>
      <td class="Style6"><div align="center">Editer&nbsp;&nbsp;</div></td>
      <td class="Style6"><div align="center">Supprimer&nbsp;&nbsp;</div></td>
      <td>&nbsp;</td>
    </tr>
    <?php do { ?>
      <tr>
        <td class="tab_detail_gris"><?php echo $row_RsEven['theme_activ']; ?>&nbsp;</td>
        <td class="tab_detail_gris"><?php echo $row_RsEven['heure_debut']; ?></td>
        <td class="tab_detail_gris"><?php echo $row_RsEven['heure_fin']; ?></td>
        <td class="tab_detail_gris"><div align="center"><img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','even_modif.php?ID_agenda=<?php echo $row_RsEven['ID_agenda']; ?>');return document.MM_returnValue"></div></td>
        <td class="tab_detail_gris"><div align="center"><img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick="MM_goToURL('window','even_supprime.php?ID_agenda=<?php echo $row_RsEven['ID_agenda']; ?>');return document.MM_returnValue"></div></td>
      </tr>
      <?php } while ($row_RsEven = mysqli_fetch_assoc($RsEven)); ?>
  </table>
  <?php 
} ?>
  <p>&nbsp;</p>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</div>
</body>
</html>
<?php
mysqli_free_result($RsEven);
?>
