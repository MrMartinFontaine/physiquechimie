<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2") ) {
	
	for ($s=1;$s<53;$s++)
	{
		if (isset($_POST['Sem'.$s])){
		$insertSQL = sprintf("UPDATE cdt_semaine_ab SET semaine=%s WHERE num_semaine=%s",
			
			GetSQLValueString($_POST['Sem'.$s], "text"),
			GetSQLValueString($_POST['Num'.$s], "text")
			
			);
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
	}
	}
	$insertGoTo = "semaine_ab_menu.php";
	if (isset($_SERVER['QUERY_STRING'])) {
		$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
		$insertGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $insertGoTo));
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<style type="text/css">
<!--
.cell_A {
	border-top-color: #FFFF55;
	border-right-color: #FFFF55;
	border-bottom-color: #FFFF55;
	border-left-color: #FFFF55;
	background-color: #D4BF55;
}
.cell_B {
	border-top-color: #FFFF55;
	border-right-color: #FFFF55;
	border-bottom-color: #FFFF55;
	border-left-color: #FFFF55;
	background-color: #55DF55;
}
#Layer1 {
	position:absolute;
	width:200px;
	height:115px;
	z-index:1;
	left: 619px;
	top: 239px;
}
-->
</style>
<script type="text/javascript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
	if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
	document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
	else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>
<?php
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsSemaine = "SELECT * FROM cdt_semaine_ab ORDER BY cdt_semaine_ab.s_code_date";
$RsSemaine = mysqli_query($conn_cahier_de_texte, $query_RsSemaine) or die(mysqli_error($conn_cahier_de_texte));
$row_RsSemaine = mysqli_fetch_assoc($RsSemaine);
$totalRows_RsSemaine = mysqli_num_rows($RsSemaine);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsEven = "SELECT * FROM cdt_agenda WHERE classe_ID=0 ORDER BY cdt_agenda.code_date ASC";
$RsEven = mysqli_query($conn_cahier_de_texte, $query_RsEven) or die(mysqli_error($conn_cahier_de_texte));
$row_RsEven = mysqli_fetch_assoc($RsEven);
$totalRows_RsEven = mysqli_num_rows($RsEven);

$i=1;
do { 
	$even_theme[$i]=$row_RsEven['theme_activ'];
	$even_debut_f[$i]=$row_RsEven['heure_debut'];
	$even_debut[$i]=substr($row_RsEven['heure_debut'],6,4).substr($row_RsEven['heure_debut'],3,2).substr($row_RsEven['heure_debut'],0,2);
	$even_fin_f[$i]=$row_RsEven['heure_fin'];
	$even_fin[$i] =substr($row_RsEven['heure_fin'],6,4).substr($row_RsEven['heure_fin'],3,2).substr($row_RsEven['heure_fin'],0,2);
	
	$i=$i+1;
	
} while ($row_RsEven = mysqli_fetch_assoc($RsEven)); 
?>
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
if ($row[0]==1){$sema="Sem. Paire";$semb="Sem. Impaire";} else {$sema="Sem. A";$semb="Sem. B";};

?>
<p>&nbsp;</p>
<?php if($totalRows_RsSemaine==0) {?>
<p>Aucune programmation pour cette ann&eacute;e scolaire n'existe actuellement.</p>
<?php } else { ?>
<p><a href="http://www.education.gouv.fr/pid25058/le-calendrier-scolaire.html" target="_blank">Consulter les dates de vacances sur le Web</a></p>
<form name="form2" action="semaine_ab_modif.php" method="POST">
Modifiez puis <strong>Enregistrez</strong> en  bas de page <br />
<br />
<table border="0" cellspacing ="0" cellpadding="0" align="center">
<tr>
<td valign="top" bordercolor="#FFFFFF" class="Style6"><div align="center">N&deg; Semaine</div></td>
<td valign="top" bordercolor="#FFFFFF" class="Style6"><div align="center">Lundi</div></td>
<td valign="top" bordercolor="#FFFFFF" class="Style6"><div align="center"><?php echo $sema;?></div></td>
<td valign="top" bordercolor="#FFFFFF" class="Style6"><div align="center"><?php echo $semb;?></div></td>
<td valign="top" bordercolor="#FFFFFF" class="Style6"><div align="center">Ev&eacute;nements - Vacances </div></td>
</tr>
<?php 
$s=1;do { ?>
        <tr>
        <td valign="top" bordercolor="#FFFFFF" class="tab_detail"><div align="center"><?php echo $row_RsSemaine['num_semaine']; ?></div></td>
        <td valign="top" bordercolor="#FFFFFF" class="tab_detail"><div align="center"><?php echo $row_RsSemaine['date_lundi']; ?></div></td>
        <td valign="top" bordercolor="#FFFFFF" class="tab_detail"><div align="center">
        <input name="<?php echo 'Sem'.$row_RsSemaine['num_semaine'];?>" type="radio" value="A" <?php if ($row_RsSemaine['semaine']=='A') {echo ' checked="checked"';} ?> />
        </div></td>
        <td valign="top" bordercolor="#FFFFFF" class="tab_detail"><div align="center">
        <input name="<?php echo 'Sem'.$row_RsSemaine['num_semaine'];?>" type="radio" value="B" <?php if ($row_RsSemaine['semaine']=='B') {echo ' checked="checked"';} ?> />
        </div></td>
        <td valign="top" bordercolor="#FFFFFF" class="tab_detail" ><div align="center">
        <?php
        //test presence evenements
        $codedatesem=substr($row_RsSemaine['date_lundi'],6,4).substr($row_RsSemaine['date_lundi'],3,2).substr($row_RsSemaine['date_lundi'],0,2);
        $codedatesem2=substr($row_RsSemaine['date_dimanche'],6,4).substr($row_RsSemaine['date_dimanche'],3,2).substr($row_RsSemaine['date_dimanche'],0,2);
        
        for ($z=1;$z<=$totalRows_RsEven;$z++)
        {
        	
		if  (($even_debut[$z]>=$codedatesem)&&($even_debut[$z]<=$codedatesem2)){echo '<br /><br />'.$even_theme[$z].'<br />(&agrave; partir du '.$even_debut_f[$z].')';};
		if  (($even_fin[$z]>=$codedatesem)&&($even_fin[$z]<=$codedatesem2))
			{echo $even_theme[$z].'<br />(jusqu\'au '.$even_fin_f[$z].')';};
		if  (($even_debut[$z]<$codedatesem)&&($even_fin[$z]>$codedatesem2)){echo $even_theme[$z].'<br /><br />';};
	};	
	?>
	</div></td>
        </tr>
        <input name="<?php echo 'Num'.$row_RsSemaine['num_semaine'];?>" type="hidden" value="<?php echo $row_RsSemaine['num_semaine'];?>" />
        <?php 	
        
	$s=$s+1;
} while ($row_RsSemaine = mysqli_fetch_assoc($RsSemaine)); ?>
</table>
<p>
<input type="hidden" name="MM_insert" value="form2">
</p>
<p>
<input name="Enregistrer" type="submit" value="Enregistrer la programmation ci-dessus" />
</p>
<p>&nbsp;</p>
</form>
<?php } ?>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsSemaine);
mysqli_free_result($RsEven);
?>
</body>
</html>
