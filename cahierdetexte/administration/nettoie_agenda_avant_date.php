<?php include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<link media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>
</head>
<body>
<DIV id=page>
  <?php 
$header_description="Suppression des activit&eacute;s ant&eacute;rieures &agrave; une date";
require_once "../templates/default/header.php";
?>
  <p>&nbsp;</p>
  <script language="JavaScript" type="text/JavaScript">
function formfocus() {
document.form1.date_form.focus()
document.form1.date_form.select()
}
</script>
  <form name="form1" onLoad= "formfocus()" method="post" action="nettoie_agenda_confirme.php">
    <blockquote>
      <p align="left">Il peut &ecirc;tre int&eacute;ressant de supprimer les saisies 
        ant&eacute;rieures &agrave; une date. Une sauvegarde pr&eacute;alable 
        est &eacute;videmment conseill&eacute;e. Cette op&eacute;ration affecte 
        les tables cdt_agenda et cdt_fichiers_joints. Les pi&egrave;ces jointes 
        ne sont pas &eacute;limin&eacute;es du dossiers Fichiers_joints. Seules 
        leurs r&eacute;f&eacute;rences seront supprim&eacute;es dans la table.<br>
        <br>
        <em>Cette action peut &ecirc;tre r&eacute;alis&eacute;e par exemple en d&eacute;but d'ann&eacute;e 
        scolaire pour supprimer les saisies de d&eacute;monstration enregistr&eacute;es lors 
        d'une formation</em>. </p>
      <p align="center">&nbsp;</p>
      <p align="center"><strong>Entrer la date avant laquelle les s&eacute;ances 
        seront supprim&eacute;es... </strong></p>
    </blockquote>

        <script>
         $(function() {
        	$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
			$.datepicker.setDefaults($.datepicker.regional['fr']);
        	$('#date_form').datepicker({firstDay:1});
        });
        </script>
    <p align="center">
      <input name='date_form' type='text' id='date_form'  size="10"/>
      &nbsp;
      <input type="submit" name="Submit" value="Envoyer">
    </p>
    <p align="center"> </p>
  </form>
  <p align="center">&nbsp;</p>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
