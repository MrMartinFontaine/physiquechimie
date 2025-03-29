<?php
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>1)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$classeOK=true;
$profOK=true;
$groupeOK=true;
$integrationOK=true;

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT param_val FROM cdt_params WHERE `param_nom`='pp_groupe'";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access3 = $row[0];
mysqli_free_result($result_read);

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	if (isset($_POST['classe_ID']) && $_POST['classe_ID'] == 0) {
		$classeOK=false;
	}
	else if (isset($_POST['prof_ID']) && $_POST['prof_ID'] == 0) {
		$profOK=false;
	}
	else {
	    $query_RsDejaPresent = sprintf("SELECT ID_pp FROM cdt_prof_principal WHERE pp_prof_ID=%u AND pp_classe_ID=%u",
			GetSQLValueString($_POST['prof_ID'], "int"),
			GetSQLValueString($_POST['classe_ID'], "int"));
	    mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	    $RsDejaPresent = mysqli_query($conn_cahier_de_texte, $query_RsDejaPresent) or die(mysqli_error($conn_cahier_de_texte));
	    $totalRows_RsDejaPresent = mysqli_num_rows($RsDejaPresent);
	     if ($totalRows_RsDejaPresent>0) {
		$integrationOK=false;
	} else {
		if ($access3=='Oui') {$gpe_ID=$_POST['groupe_ID'];} else {$gpe_ID=1;};
		$updateSQL = sprintf("INSERT INTO cdt_prof_principal (pp_prof_ID,pp_classe_ID,pp_groupe_ID) VALUES (%u,%u,%u)",
			GetSQLValueString($_POST['prof_ID'], "int"),
			GetSQLValueString($_POST['classe_ID'], "int"),
			GetSQLValueString($gpe_ID, "int"));
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
		header("Location:prof_principaux_liste.php");
	}
    }
};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if ($access3=='Oui') {
	$query_RsClasse = "SELECT ID_classe,nom_classe FROM cdt_classe ORDER BY nom_classe ASC";
} else {
    $query_RsClasse = "SELECT ID_classe,nom_classe FROM cdt_classe WHERE ID_classe NOT IN (SELECT pp_classe_ID FROM cdt_prof_principal) ORDER BY nom_classe ASC";
};
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT param_val FROM cdt_params WHERE param_nom='pp_multiclass' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access2 = $row[0];
mysqli_free_result($result_read);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if ($access3=='Oui') {$query_RsProf ="SELECT ID_prof,nom_prof,identite FROM cdt_prof WHERE (droits=2)";}
else { $query_RsProf = "SELECT ID_prof,nom_prof,identite FROM cdt_prof WHERE (droits=2) AND (ID_prof NOT IN (SELECT pp_prof_ID FROM cdt_prof_principal)) ORDER BY nom_prof ASC";};
$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsGroupe = "SELECT ID_groupe,groupe FROM cdt_groupe ORDER BY ID_groupe ASC";
$RsGroupe = mysqli_query($conn_cahier_de_texte, $query_RsGroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_RsGroupe = mysqli_fetch_assoc($RsGroupe);
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
$header_description="Ajout d'un professeur principal";
require_once "../templates/default/header.php";
?>

<blockquote>
<blockquote>
<blockquote>
<p align="left">&nbsp;</p>
<fieldset style="width : 100%">
<?php if(!($classeOK)) {?>
	<p style="color:red;">Veillez &agrave; bien s&eacute;lectionner une classe.</p>
	<p align="left">&nbsp;</p>
<?php } else if(!($profOK)) {?>
	<p style="color:red;">Veillez &agrave; bien s&eacute;lectionner un enseignant.</p>
	<p align="left">&nbsp;</p>
<?php } else if(!($integrationOK)) {?>
	<p style="color:red;">Cet enseignant est d&eacute;j&agrave; professeur principal de cette classe.</p>
	<p align="left">&nbsp;</p>
<?php };?>
<p align="left">Pour pouvoir associer &agrave; une classe un professeur principal, il suffit de s&eacute;lectionner la classe ad&eacute;quate ainsi que le nom
de l'enseignant.</p>
<p align="left">&nbsp;</p>
<p><form method="post">
<table width="95%" align="center" cellspacing="0" class="lire_cellule_22" >
<tr valign="baseline">
<td width="30%" ><p align="right" class="Style70">Classe : &nbsp;</p></td>
<td width="70%" valign="middle" ><div align="left">
<select name="classe_ID" id="classe">
<option value="0" selected='selected'>S&eacute;lectionnez une classe</option>

<?php
do {
	?>
	<option value='<?php echo $row_RsClasse['ID_classe'];?>'
	<?php if ((isset($_POST['classe_ID']))&&($row_RsClasse['ID_classe']==$_POST['classe_ID'])){ echo "selected='selected'";}; ?>
	><?php echo $row_RsClasse['nom_classe'];?></option>
	<?php
} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse));
mysqli_free_result($RsClasse);   
?>
</select>
</div></td></tr>
<td width="30%" ><p align="right" class="Style70">Enseignant : &nbsp;</p></td>
<td width="70%" valign="middle" ><div align="left">
<select name="prof_ID" id="prof">
<option value="0" selected='selected'>S&eacute;lectionnez un enseignant</option>
<?php
do {
	?>
	<option value='<?php echo $row_RsProf['ID_prof'];?>'
	
	<?php if ((isset($_POST['prof_ID']))&&($row_RsProf['ID_prof']==$_POST['prof_ID'])){ echo "selected='selected'";}; ?>
	><?php  echo $row_RsProf['identite']==""?$row_RsProf['nom_prof']:$row_RsProf['identite'];?></option>
								
<?php
} while ($row_RsProf = mysqli_fetch_assoc($RsProf));
mysqli_free_result($RsProf);
?>  
</select>
</div></td></tr>
<?php if ($access3=='Oui') { ?>
	<td width="30%" ><p align="right" class="Style70">Groupe : &nbsp;</p></td>
        <td width="70%" valign="middle" ><div align="left">
        <select name="groupe_ID" id="groupe">
        <?php
        do {
        	?>
        	<option value='<?php echo $row_RsGroupe['ID_groupe'];?>'><?php echo $row_RsGroupe['groupe'];?></option>
        	<?php
        } while ($row_RsGroupe = mysqli_fetch_assoc($RsGroupe));
        mysqli_free_result($RsGroupe);
        ?>  
        </select>
        </div></td></tr>
<?php }; ?>
</table>
<br/><br/>
<input type="hidden" name="MM_update" value="form1">
<input type="submit" name="Submit" value="Valider">

</form></p>
</fieldset>
<p align="left">&nbsp;</p>
</blockquote>
</blockquote>
</blockquote>

<p><a href="
<?php 
if ($_SESSION['droits']==3){echo 'vie_scolaire.php';}
else if ($_SESSION['droits']==4){echo '../direction/direction.php';}
else if ($_SESSION['droits']==1){echo '../administration/index.php';};
?>
"><br>

<?php
if ($_SESSION['droits']==3){echo 'Retour au Menu Vie scolaire';}
else if ($_SESSION['droits']==4){echo 'Retour au Menu Responsable Etablissement';}
else if ($_SESSION['droits']==1){echo 'Retour au Menu Administrateur';};
?>
</a> </p>
<p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
