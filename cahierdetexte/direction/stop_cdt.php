<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>4) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}





if ((isset($_POST["MM_update4"])) && ($_POST["MM_update4"] == "form4")) {
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsProf = "SELECT * FROM cdt_prof WHERE droits=2";
	$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsProf = mysqli_fetch_assoc($RsProf);
	do { //chaque prof
		$x=$row_RsProf['ID_prof'];
		
		if (isset($_POST[$x])&&($_POST[$x]=='on')){
			$updateSQL4 = sprintf("UPDATE cdt_prof  SET stop_cdt='%s' WHERE ID_prof = %s",'O',$x);
		}else{
			$updateSQL4 = sprintf("UPDATE cdt_prof  SET stop_cdt='%s' WHERE ID_prof = %s",'N',$x);
		};
		$Result4 = mysqli_query($conn_cahier_de_texte, $updateSQL4) or die(mysqli_error($conn_cahier_de_texte));
	} while ($row_RsProf = mysqli_fetch_assoc($RsProf));
	mysqli_free_result($RsProf);
	
	
	$updateGoTo = "direction.php";
	if (isset($_SERVER['QUERY_STRING'])) {
		$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
		$updateGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $updateGoTo));
}




mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsPublier = "SELECT ID_prof,nom_prof,identite, publier_cdt,publier_travail,MAX(code_date), prof_ID,date_maj,stop_cdt FROM cdt_prof , cdt_agenda
WHERE ID_prof=prof_ID GROUP BY prof_ID ORDER BY nom_prof ASC";
$RsPublier = mysqli_query($conn_cahier_de_texte, $query_RsPublier) or die(mysqli_error($conn_cahier_de_texte));
$row_RsPublier = mysqli_fetch_assoc($RsPublier);
$totalRows_RsPublier = mysqli_num_rows($RsPublier);


?>




<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">

<!--
.Style70 {color: #000066}
-->
</style>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Bloquer l'utilisation de l'application";
require_once "../templates/default/header.php";
?>
<BR />
<div>
<table border="0" width="95%" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
<tr> 
<td class="tab_detail"><p>Vous avez la possibilit&eacute; de bloquer l'utilisation de l'application et sa publication en ligne pour un ou plusieurs enseignants. </p>
<ul><li>Cochez les enseignants pour lesquels vous d&eacute;sirez bloquer l'application. </li>
<li>Pour visualiser le cahier de textes d'un enseignant, cliquez sur son 
nom.</li>

<?php if ($totalRows_RsPublier==0) { ?>
	
	</ul><p align=center style="color:#FF0000">N'ayant aucun enseignant dans le cahier de textes actuellement, le blocage de l'application est inutile.</p>
	</td></tr>
<?php } else {?>
	<li>N'oubliez pas d'enregistrer vos modifications en bas de page. </li></ul>
    <p>
	<i>Les enseignants dont le nom est en italique sont ceux qui n'ont pas saisi leur identit&eacute; dans leur interface.</i>
    </p>
	<p align="center"><a href="direction.php"><br>
	Annuler et retourner au menu Responsable Etablissement </a><br />
	</p>   </td>
	</tr>
	<tr> 
        <td class="tab_detail_gris"> <form action="stop_cdt.php" method="POST" name="form4" id="form4">
        <br>
        <table border="0" align="center" cellpadding="0" cellspacing="0" class="bordure">
        <tr> 
        <td class="Style6">Enseignant</td>
        <td colspan="2" class="Style6"><div align="center">Publi&eacute; en ligne 
        &nbsp;</div></td>
        <td class="Style6"><div align="right">Dernier ajout &nbsp;</div></td>
        <td class="Style6"><div align="right">Dernier visa &nbsp; </div></td>
        <td class="Style6">Bloquer &nbsp;</td>
        </tr>
        <tr class="tab_detail" > 
        <td class="tab_detail">&nbsp;</td>
        <td class="tab_detail"><div align="center">Travail</div></td>
        <td class="tab_detail"><div align="center">CDT</div></td>
        <td class="tab_detail"><div align="right"></div></td>
        <td class="tab_detail"><div align="right"></div></td>
        <td  > 
        
        <div align="center">
        <script>
        function cocherTout(etat)
        {
        	var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
        	for(var i=0; i<cases.length; i++)     // on les parcourt
        		if(cases[i].type == 'checkbox')     // si on a une checkbox...
        		cases[i].checked = etat;     // ... on la coche ou non
        }
        
        function decocherTout()
        {
        	var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
        	cases[0].checked = false;     // ... on decoche la premiere, le TOUS
        }
        </script>		
        Tout
        <input type="checkbox" name="checkbox" id="tousaucun" onclick= cocherTout(this.checked) value="1" >
        </div></td></tr>
        <?php $date_lastajout='';
	do {
		$date_lastajout=substr($row_RsPublier['MAX(code_date)'],6,2).'/'.substr($row_RsPublier['MAX(code_date)'],4,2).'/'.substr($row_RsPublier['MAX(code_date)'],0,4);
		?>
		<tr> <?php if ($row_RsPublier['stop_cdt']=='O'){echo '<bgcolor="#FE9001">&nbsp;';} else {echo ('<class="tab_detail">');};?>
                <td class="tab_detail<?php echo $row_RsPublier['stop_cdt']=='O'?'_orange':'';?>"><?php echo ($row_RsPublier['identite']=='' ? '&nbsp;<i>'.$row_RsPublier['nom_prof'].'</i>&nbsp;': '&nbsp;<strong>'.$row_RsPublier['identite'].'</strong>&nbsp;'); ?></td>
                <td class="tab_detail<?php if ($row_RsPublier['stop_cdt']=='O'){echo '_orange';} else {echo $row_RsPublier['publier_travail']=='N'?'_rose':'';};?>"><div align="center"><?php echo $row_RsPublier['publier_travail']; ?></div></td>
                <td class="tab_detail<?php if ($row_RsPublier['stop_cdt']=='O'){echo '_orange';} else {echo $row_RsPublier['publier_cdt']=='N'?'_rose':'';};?>"><div align="center"><?php echo $row_RsPublier['publier_cdt']; ?></div></td>
                <td class="tab_detail<?php echo $row_RsPublier['stop_cdt']=='O'?'_orange':'';?>"><div align="right"><?php echo '&nbsp;'.jour_semaine($date_lastajout).' '.$date_lastajout.'&nbsp;'; ?></div></td>
                <td class="tab_detail<?php echo $row_RsPublier['stop_cdt']=='O'?'_orange':'';?>"><div align="right"> 
                <?php 
		if (substr($row_RsPublier['date_maj'],0,4)<>'0000'){
			$date_maj=substr($row_RsPublier['date_maj'],8,2).'/'.substr($row_RsPublier['date_maj'],5,2).'/'.substr($row_RsPublier['date_maj'],0,4);
		echo '&nbsp;'.jour_semaine($date_maj).' '.$date_maj.'&nbsp;';} else {echo '&nbsp;';}; ?>
		</div></td>
                <td class="tab_detail<?php echo $row_RsPublier['stop_cdt']=='O'?'_orange':'';?>"><div align="center" > 
                <input type="checkbox" name="<?php echo $row_RsPublier['prof_ID']; ?>"   id="<?php echo $row_RsPublier['prof_ID']; ?>" onclick=decocherTout() <?php if($row_RsPublier['stop_cdt']=='O'){echo "checked";}; ?> >
                </div></td>
                </tr>
        <?php } while ($row_RsPublier = mysqli_fetch_assoc($RsPublier)); ?>
        </table>
        <div align="center"> 
        <p> 
        <input type="hidden" name="MM_update4" value="form4">
        <br>
        <input name="submit4" type="submit" class="vacances" id="submit4" value="Enregistrer les modifications">
        </p>
        </div>
        </form>          </td>
        </tr>
<?php } ?>
</table>




<p>&nbsp;</p>
<p><a href="direction.php">Annuler et retourner au menu Responsable Etablissement</a></p>
</div>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsPublier);
?>
