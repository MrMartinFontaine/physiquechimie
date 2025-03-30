<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

if(isset($_POST['choice1']))
	{
	$choice1= GetSQLValueString($_POST['choice1'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice1 WHERE `param_nom`='prof_mess_pp';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);

	if ($_POST['choice1']=="Non"){
		//On depublie les messages existants si on annule la possibilite de publication
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$updateSQL = " UPDATE `cdt_message_contenu` SET online='N'  WHERE online='O' AND pp_classe_ID=0; ";
		$Rsmessage = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
		} 
		else {
		//On republie les messages existants si on annule la possibilite de publication
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$updateSQL = " UPDATE `cdt_message_contenu` SET online='O'  WHERE online='N' AND pp_classe_ID=0; ";
		$Rsmessage = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
		}
	}

if(isset($_POST['choice2']))
	{
	
	$choice2 = GetSQLValueString($_POST['choice2'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice2 WHERE `param_nom`='prof_mess_all';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	
		
			$query_write = "UPDATE `cdt_params` SET `param_val`='Oui' WHERE `param_nom`='prof_mess_pp';";
			$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
					//On republie les messages existants si on annule la possibilite de publication
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$updateSQL = " UPDATE `cdt_message_contenu` SET online='O'  WHERE online='N' AND pp_classe_ID=0; ";
			$Rsmessage = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
		
	}
	
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='prof_mess_pp' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access1 = $row[0];
mysqli_free_result($result_read);

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='prof_mess_all' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access2 = $row[0];
mysqli_free_result($result_read);

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
$header_description="Publication de messages entre les enseignants autres que les professeurs principaux";
require_once "../templates/default/header.php";
?>
  <p align="center">
  <br />
  <blockquote>
    <fieldset style="width : 100%">
    <legend style="color:red;">Autorisation actuelle de publier des messages par un enseignant vers ses coll&egrave;gues et ses &eacute;l&egrave;ves : <b><?php echo $access1; ?></b>&nbsp;</legend>
    <p align="left">Seuls les professeurs principaux sont  autoris&eacute;s &agrave; diffuser des messages vers leurs coll&egrave;gues et vers les &eacute;l&egrave;ves dont ils sont professeur principal. </p>
    <p align="left">Cette option &eacute;tend cette autorisation &agrave; tous les enseignants. Un enseignant peut  alors adresser un message &agrave; un coll&egrave;gue enseignant dans les m&ecirc;mes classes que lui, ainsi qu'aux &eacute;l&egrave;ves des classes dans lequel il enseigne. </p>
    <p align="left">L'enseignant se voit ainsi attribuer les m&ecirc;mes droits que le professeur principal.</p>
    <p align="left">Ces informations apparaissent dans la page d'accueil de remplissage du cahier de textes de l'enseignant.</p>
    <p>
    <form method="post">
      <?php 
if($access1=="Oui") echo "<input type=\"hidden\" name=\"choice1\" value=\"Non\"/><input type=\"submit\" value=\"Interdire aux enseignants la publication de messages (sauf professeurs principaux)\"/>";
else echo "<input type=\"hidden\" name=\"choice1\" value=\"Oui\"/><input type=\"submit\" value=\"Autoriser les enseignants &agrave; publier des messages vers leurs &eacute;l&egrave;ves et les coll&egrave;gues de leurs &eacute;l&egrave;ves \"/>";
?>



    </form>
    </p>
    </fieldset>
  </blockquote>
    <br />
  <blockquote>
    <fieldset style="width : 100%">
    <legend style="color:red;">Autorisation actuelle de publier des messages par un enseignant vers l'ensemble de l'&eacute;tablissement : <b><?php echo $access2; ?></b>&nbsp;</legend>
    <p align="left">Si l'autorisation ci-dessus n'est pas activ&eacute;e, les enseignants  sont  autoris&eacute;s &agrave; diffuser des messages uniquement vers les enseignants et les &eacute;l&egrave;ves des classes dans lesquelles ils enseignent.</p>
    <p align="left">Cette option &eacute;tend cette autorisation &agrave; toutes les classes et &agrave; tous les &eacute;l&egrave;ves. Un enseignant peut  alors adresser un message &agrave; toutes les classes,  m&ecirc;mes &agrave; celles dans lesquelles il n'enseigne pas.</p>
    <p align="left">L'enseignant se voit ainsi attribuer les m&ecirc;mes droits que la vie scolaire.</p>
    <p align="left">Ces informations apparaissent dans la page du travail &agrave; faire dans le cahier de textes des &eacute;l&egrave;ves.</p>
    <p>
    <form method="post">
      <?php 
if($access2=="Oui"){
?>
<input type="hidden" name="choice2" value="Non" />
<input type="submit" value="Interdire aux enseignants la publication de messages vers tous"/>
<?php 
}
else {
?>
<input type="hidden" name="choice2" value="Oui" />
<input type="submit" value="Autoriser l'ensemble des enseignants &agrave; publier des messages vers tous"/>
<?php }
?>



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
