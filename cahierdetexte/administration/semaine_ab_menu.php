<?php include "../authentification/authcheck.php"; ?>
<?php if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
 
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Programmation des semaines en alternance";
require_once "../templates/default/header.php";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='libelle_semaine' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
if ($row[0]==1){$libelle = 'Semaine Paire et Impaire';} else {$libelle = 'Semaine A et B';};
?>

<HR> 
<p align="center" class="Style13">Inititialisation</p>
<p align="center"><a href="libelle_semaine.php">Libell&eacute; des semaines en alternance (Actuellement <?php echo $libelle; ?>)</a></p>
<p align="center"><a href="semaine_ab_creer.php">Cr&eacute;er une nouvelle programmation des semaines A et B<br /> 
  (A utiliser pour toute nouvelle ann&eacute;e scolaire ou r&eacute;initialisation d'une programmation existante)</a></p>
<p align="center">&nbsp;</p>
<p align="center">&nbsp;</p>
<p align="center"><a href="libelle_semaine.php"><span class="Style13">Gestion des semaines A et B (Paire ou Impaire) </span></a></p>
<p align="center"><a href="semaine_ab_modif.php">Modifier la programmation des semaines A et B de l'ann&eacute;e scolaire en cours</a></p>
<p align="center">&nbsp;</p>
<p align="center"><span class="Style13">Gestion des alternances sur 4 semaines (Sem 1, Sem 2, Sem 3,Sem4) </span></p>
<p align="center"><a href="semaine_alter4_modif.php">Modifier la programmation des alternances 4 semaines de l'ann&eacute;e scolaire en cours</a></p>
<p align="center">&nbsp;</p>
<body>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>

</body>
</html>
