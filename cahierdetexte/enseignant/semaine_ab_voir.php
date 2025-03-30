<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 

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

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<style type="text/css">
<!--
.cell_A {
}


.cell_B {
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
?>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Programmation des semaines en alternance";
require_once "../templates/default/header.php";
?>

  
 <p align="center"><a href="enseignant.php">Retour au Menu Enseignant</a></p>

  <table  width="80%" align="center" cellpadding="5" cellspacing="1" class="Style555">
  <tr>
    <td width="21%">N&deg; Semaine</td>
    <td width="26%"><div align="center">Lundi</div></td>
    
    <td width="17%"><div align="center">Semaine A/B</div></td>
	<td width="17%"><div align="center">Semaine /4</div></td>
	<td width="36%"><div align="center">Ev&eacute;nements - Vacances </div></td>  
  </tr>
  <?php 
  do { ?>
    <tr>
      <td bgcolor="#FFFFFF"><?php echo $row_RsSemaine['num_semaine']; ?></td>
      <td bgcolor="#FFFFFF"><div align="center"><?php echo $row_RsSemaine['date_lundi']; 
$codedatesem=substr($row_RsSemaine['date_lundi'],6,4).substr($row_RsSemaine['date_lundi'],3,2).substr($row_RsSemaine['date_lundi'],0,2);
$codedatesem2=substr($row_RsSemaine['date_dimanche'],6,4).substr($row_RsSemaine['date_dimanche'],3,2).substr($row_RsSemaine['date_dimanche'],0,2);
	  ?></div></td>
	
	  <td bgcolor="#FFFFFF" <?php if ($row_RsSemaine['semaine']=='A') {echo 'class="cell_A" ';} else {echo 'class="cell_B" ';}; ?>><div align="center">
	    <?php
		if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){
		if ($row_RsSemaine['semaine']=='A et B'){echo 'P et I';} else if($row_RsSemaine['semaine']=='A'){echo 'Paire';} else {echo 'Impaire';};
		}
		else {
		echo $row_RsSemaine['semaine']; 
		};
	  
	 ?>
	    </div></td>  
	  <td bgcolor="#FFFFFF"><?php echo $row_RsSemaine['semaine_alter'];?> </td>
	  <td bgcolor="#FFFFFF">
		<?php
		//test presence evenements
		
		for ($s=1;$s<=$totalRows_RsEven;$s++)
		{

		if  (($even_debut[$s]>=$codedatesem)&&($even_debut[$s]<=$codedatesem2)){echo $even_theme[$s].'<br />( &agrave; partir du '.$even_debut_f[$s].')';};
		if  (($even_fin[$s]>=$codedatesem)&&($even_fin[$s]<=$codedatesem2)){echo $even_theme[$s].'<br />( jusqu\'au '.$even_fin_f[$s].')';};
		if  (($even_debut[$s]<$codedatesem)&&($even_fin[$s]>$codedatesem2)){echo $even_theme[$s];};
		};	
		?>		</td><?php
	} while ($row_RsSemaine = mysqli_fetch_assoc($RsSemaine)); ?>
</table>
 <p align="center">
 <?php
 if ($_SESSION['droits']==2){echo '<a href="enseignant.php">Retour au Menu Enseignant </a></p>';};
 if ($_SESSION['droits']==3){echo '<a href="..\vie_scolaire\vie_scolaire.php">Retour au Menu Vie Scolaire </a></p>';};
 ?>
  <DIV id=footer></DIV>
</DIV>


<?php
mysqli_free_result($RsSemaine);
?>
</body>
</html>
