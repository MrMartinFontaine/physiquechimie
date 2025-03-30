<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

if((isset($_POST['choice']))or(isset($_POST['init'])))
{
	if(isset($_POST['choice']))
	{
		$choice = substr($_POST['choice'],0,1)=='D'?'Non':'Oui';
		$query_write = sprintf("UPDATE `cdt_params` SET `param_val`=%s WHERE `param_nom`='affichage_compteur'",GetSQLValueString($choice, "text"));
		$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	}
	elseif(substr($_POST['init'],0,1)=='R')
	{
		//Remise à zéro du compteur de consultations du cdt
		$query_RsCompt = "UPDATE `cdt_params` SET `param_val` = '0' WHERE `param_nom` ='compteur'";
		$result = mysqli_query($conn_cahier_de_texte, $query_RsCompt) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));
                $query_RsCompt = "UPDATE `cdt_params` SET `param_val` = '".date('Ymd')."' WHERE `param_nom` ='date_raz_compteur'";
                $result = mysqli_query($conn_cahier_de_texte, $query_RsCompt) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));
        }
}

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='affichage_compteur' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access = $row[0];
if (isset($result_read)){mysqli_free_result($result_read);};
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
$header_description="Gestion du compteur de consultations";
require_once "../templates/default/header.php";
?>

<blockquote>
<blockquote>
<blockquote>


<p align="center">&nbsp;</p>
<p align="center">Le compteur est minimaliste et permet de compter uniquement le nombre de fois qu'un cahier de textes ou que le travail &agrave; faire a &eacute;t&eacute; consult&eacute;. </p>
<p style="color:red;">&nbsp;</p>
<p style="color:red;">
<?php if ($access=='Oui'){ echo 'Le compteur est actuellement en place.';
	//Affichage du compteur de consultations du cdt
	
	if ((isset($_SESSION['affichage_compteur']))&&($_SESSION['affichage_compteur']=='Oui'))	{
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsCompt = "SELECT param_val FROM cdt_params WHERE param_nom='compteur'";
		$RsCompt = mysqli_query($conn_cahier_de_texte, $query_RsCompt) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsCompt = mysqli_fetch_assoc($RsCompt);
		$cpt = $row_RsCompt['param_val'];
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsdateRAZ = "SELECT param_val FROM cdt_params WHERE param_nom='date_raz_compteur'";
		$RsdateRAZ = mysqli_query($conn_cahier_de_texte, $query_RsdateRAZ) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsdateRAZ = mysqli_fetch_assoc($RsdateRAZ);
		$date_actu = $row_RsdateRAZ['param_val'];
		
		$date_actu=substr($date_actu,6,2).'/'.substr($date_actu,4,2).'/'.substr($date_actu,0,4);
		
		echo "<br><font size=\'1\'><i>Le cahier de textes a &eacute;t&eacute; consult&eacute; <b>$cpt</b> fois depuis le ".jour_semaine($date_actu)." $date_actu</i></font><br>";
		mysqli_free_result($RsCompt);
		mysqli_free_result($RsdateRAZ);	
	};
	
	
}?>
</p>
<p><form method="post">
<?php 
if($access=="Non") {echo "<input type='hidden' name='choice' value='Oui'/><input type='submit' value='Activer le compteur '/>";}
else { ?>
	<input type='submit' name='choice' value='D&eacute;sactiver le compteur'/>
	<BR/>
	<BR/>
	<input type='submit' name='init' value='R&eacute;initialiser le compteur'/>
<?php }; ?>       
</form></p>
<p align="left">&nbsp;</p>
</blockquote>
</blockquote>
</blockquote>
<p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
<p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>

