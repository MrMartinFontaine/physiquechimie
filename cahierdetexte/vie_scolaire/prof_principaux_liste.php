<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>6)&&($_SESSION['droits']<>7)&&($_SESSION['droits']<>1)){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if (isset($_GET['tri'])&&($_GET['tri']=='pp')){
	$query_RsProf = "SELECT * FROM cdt_prof_principal,cdt_prof, cdt_groupe, cdt_classe WHERE cdt_prof_principal.pp_prof_ID=cdt_prof.ID_prof AND cdt_prof_principal.pp_classe_ID=cdt_classe.ID_classe AND cdt_prof_principal.pp_groupe_ID=cdt_groupe.ID_groupe ORDER BY cdt_prof.identite,cdt_prof.nom_prof ASC";
}
else
{
	$query_RsProf = "SELECT * FROM cdt_prof_principal,cdt_prof, cdt_groupe, cdt_classe WHERE cdt_prof_principal.pp_prof_ID=cdt_prof.ID_prof AND cdt_prof_principal.pp_classe_ID=cdt_classe.ID_classe AND cdt_prof_principal.pp_groupe_ID=cdt_groupe.ID_groupe ORDER BY nom_classe ASC";
};
$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);
$totalRows_RsProf = mysqli_num_rows($RsProf);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='pp_groupe' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access3 = $row[0];
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
$header_description="Liste des professeurs principaux d&eacute;clar&eacute;s";
require_once "../templates/default/header.php";
?>

<HR> 

<table width="95%" align="center" border="0">
<tr>
<td><p>Les enseignants ont la possibilit&eacute; via leur Menu Enseignant, de se d&eacute;clarer en tant que professeur principal.<br>
Cela leur conf&egrave;re &eacute;ventuellement des droits pour adresser des messages via le cahier de textes aux classes dont ils ont la charge (param&eacute;trage r&eacute;alis&eacute; par l'administrateur). <br>
<i>Les enseignants dont le nom est rouge sont ceux qui n'ont pas saisi leur identit&eacute; dans leur interface.</i></p></td>
<td valign="top"><a href="
<?php 
if (($_SESSION['droits']==2)||($_SESSION['droits']==8)){echo '../enseignant/enseignant.php';}
else if ($_SESSION['droits']==3){echo 'vie_scolaire.php';}
else if ($_SESSION['droits']==4){echo '../direction/direction.php';}
else if ($_SESSION['droits']==1){echo '../administration/index.php';};
?>
">
<br /><img src="../images/home-menu.gif" border="0" align="top"></a>&nbsp;&nbsp;&nbsp;&nbsp;</td>
</tr>
</table>
<script type="text/JavaScript">

function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}

function confirmation(sup_message,ref) {
	if (confirm(sup_message)) { // Clic sur OK
		MM_goToURL('window','pp_supprime.php?pp_ID='+ref);
	}
}

</script>

<?php if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>6)&&($_SESSION['droits']<>8)) { ?>
	<p align="center"><a href="pp_ajout.php">Ajouter un enseignant comme professeur principal</a></p>
<?php   };?>
<p align="center"><a href="prof_principaux_liste.php?tri=pp">Trier par enseignant</a> - <a href="prof_principaux_liste.php?tri=cl">Trier par nom de classe </a></p>
<table border="0" align="center" width="90%" >
<tr> 
<td class="Style6"><div align="center">NOM</div></td>
<td class="Style6"><div align="center">Classe</div></td>
<?php if ($access3=='Oui') { ?>
	<td class="Style6">Groupe</td>
<?php }; ?>
<td class="Style6">M&eacute;l</td>
<?php if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>6)) {?>
	<td class="Style6">&nbsp;</td>
<?php }; ?>
</tr>
<?php do { ?>
	<tr> 
	<td class="tab_detail_gris"><div align="left"><?php if ($row_RsProf['identite']=="") {echo "<i><font color=red>".$row_RsProf['nom_prof']."</font></i>";} else {echo $row_RsProf['identite'];};?></div></td>
	<td class="tab_detail_gris"><div align="center"><?php echo $row_RsProf['nom_classe']; ?></div></td>
	<?php if ($access3=='Oui') { ?>
		<td class="tab_detail_gris"><div align="center"><?php echo $row_RsProf['groupe']; ?></div></td>
	<?php }; ?>
	<td class="tab_detail_gris"><div align="left"><a href="mailto:<?php echo $row_RsProf['email']; ?>"><?php echo $row_RsProf['email']; ?></a></div></td>
	<?php if (($_SESSION['droits']!=2)&&($_SESSION['droits']<>6)){?>
	<td class="tab_detail_gris">
		<img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick= "return confirmation('Voulez-vous r\351ellement supprimer <?php echo $row_RsProf['identite']==""?$row_RsProf['nom_prof']:$row_RsProf['identite']; ?> comme professeur principal de la classe de <?php echo $row_RsProf['nom_classe']; ?>?',<?php echo $row_RsProf['ID_pp']; ?>)">
	
	</td>
	<?php }; ?>
	</tr>
<?php } while ($row_RsProf = mysqli_fetch_assoc($RsProf));
mysqli_free_result($RsProf);
?>
</table>


<p><a href="
<?php 
if (($_SESSION['droits']==2)||($_SESSION['droits']==8)){echo '../enseignant/enseignant.php';}
else if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'vie_scolaire.php';}
else if ($_SESSION['droits']==4){echo '../direction/direction.php';}
else if ($_SESSION['droits']==6){echo '../assistant_education/assistant_educ.php';}
else if ($_SESSION['droits']==1){echo '../administration/index.php';};
?>
"><br>

<?php
if (($_SESSION['droits']==2)||($_SESSION['droits']==8)){echo 'Retour au Menu Enseignant';}
else if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'Retour au Menu Vie scolaire';}
else if ($_SESSION['droits']==4){echo 'Retour au Menu Responsable Etablissement';}
else if ($_SESSION['droits']==6){echo 'Retour au Menu Assistant &eacute;ducation';}
else if ($_SESSION['droits']==1){echo 'Retour au Menu Administrateur';};
?>
</a> </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
