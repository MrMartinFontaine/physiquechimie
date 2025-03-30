<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='pp_groupe' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access3 = $row[0];
mysqli_free_result($result_read);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='pp_multiclass' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access2 = $row[0];
mysqli_free_result($result_read);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT MAX(ID_classe) FROM `cdt_classe`;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$imax = $row[0];
mysqli_free_result($result_read);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT MIN(ID_classe) FROM `cdt_classe`;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$imin = $row[0];
mysqli_free_result($result_read);

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") ) {
	
	//on efface
	$deleteSQL = sprintf("DELETE FROM cdt_prof_principal WHERE pp_prof_ID=%u", $_SESSION['ID_prof']);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	for ($i=$imin; $i<=$imax; $i++) { 
		$refclasse='pp_classe'.$i;
		if ($access3=='Oui') {
			$refgroupe='pp_groupe'.$i;
		};
		
		if ((($access3=='Oui')&&(isset($_POST[$refclasse])&&(isset($_POST[$refgroupe])) &&($_POST[$refclasse]=='on')))||(($access3=='Non')&&(isset($_POST[$refclasse])&&($_POST[$refclasse]=='on')))){ 
			if ($access3=='Oui') {$gpe_ID=$_POST[$refgroupe];} else {$gpe_ID=1;};
			$insertSQL2= sprintf("INSERT INTO `cdt_prof_principal` ( `pp_prof_ID` ,`pp_classe_ID` , `pp_groupe_ID` )  VALUES ('%u', '%u', '%u');",$_SESSION['ID_prof'],$i, $gpe_ID);
			$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));
		}//du if
	}//du for
	$insertGoTo = "enseignant.php";
	
	header(sprintf("Location: %s", $insertGoTo));
	
};
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<?php if ($access2=='Non') {?>
	<script language="JavaScript" type="text/javascript"> 
	
	function verif_multiPP(mini, maxi)
	{	  
		var i,j;
		j=0;
		for (i=mini; i<maxi; i++) {
			if(document.getElementById("pp_classe"+i)!=null){
				if(document.getElementById("pp_classe"+i).checked==true){j++;} ;
			};
		}; 
		if(j>1){
			document.form1.Submit.disabled=true;
			window.alert('Vous ne pouvez \351tre professeur principal que d\'une seule classe. Vous ne pourrez pas valider votre choix tant que vous ne s\351lectionnerez pas qu\'une seule classe.');
		} else {
			document.form1.Submit.disabled=false;
		};
	};
	</script>
<?php }; ?>
<BODY <?php echo $access2=='Non'?'onLoad="verif_multiPP('.$imin.', '.$imax.')"':'';?>>
<DIV id=page>
<p>
<?php 
$header_description="D&eacute;finition des classes pour lesquelles je suis professeur principal";
require_once "../templates/default/header.php";

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
//Requete 1 : Selection de toutes les classes "classiques" ou le prof enseigne
//Union Requete 2 : Selection de toutes les classes ou le prof enseigne en heures partagees dont il n'est pas le createur
//Union Requete 3 : Selection de toutes les classes ou le prof enseigne en regroupement.
$query_RsClasse = sprintf("(SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe,cdt_emploi_du_temps WHERE cdt_classe.ID_classe=cdt_emploi_du_temps.classe_ID AND cdt_emploi_du_temps.prof_ID=%u)
								UNION DISTINCT (SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe,cdt_emploi_du_temps,cdt_emploi_du_temps_partage WHERE profpartage_ID=%u AND cdt_emploi_du_temps_partage.ID_emploi=cdt_emploi_du_temps.ID_emploi AND cdt_classe.ID_classe=cdt_emploi_du_temps.Classe_ID)
								UNION DISTINCT (SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe,cdt_groupe_interclasses,cdt_groupe_interclasses_classe WHERE cdt_groupe_interclasses.prof_ID=%u AND cdt_groupe_interclasses.ID_GIC=cdt_groupe_interclasses_classe.gic_ID AND cdt_groupe_interclasses_classe.classe_ID=ID_classe) ORDER BY nom_classe ASC",
			GetSQLValueString($_SESSION['ID_prof'],"int"),
			GetSQLValueString($_SESSION['ID_prof'],"int"),
			GetSQLValueString($_SESSION['ID_prof'],"int"));
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);
 
if ($access3=='Oui') {	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
	$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
	$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);
};

if ($totalRows_RsClasse<>0) {
	?>
  </p>
	<blockquote>
	    <blockquote>
	      <p align="left">&nbsp;</p>
	      <p align="left"><img src="../images/lightbulb.png">&nbsp;En tant que professeur principal, vous disposez de droits 
	        pour planifier un &eacute;v&eacute;nement ou diffuser un message &agrave; vos classes. Cochez la ou les classes pour lesquelles vous assurez la fonction de professeur principal.</p>
	    </blockquote>
      <p align="left">&nbsp;</p>
	</blockquote>
	<form name="form1" method="post" action="professeur_principal.php">
	<table border="0"  align="center" cellpadding="0" cellspacing="0" class="lire_bordure"  >
	<tr>
        <td class="Style6">Classes &nbsp;</td>
        <?php if ($access3=='Oui') { ?>
        	<td class="Style6">Groupes</td>
	<?php }; ?>
	<td class="Style6">Je suis professeur principal &nbsp;</td>
	<td class="Style6">Professeur principal d&eacute;j&agrave; d&eacute;clar&eacute;&nbsp; </td>
	</tr>
	
	<?php do { 
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rspp =sprintf("SELECT * FROM cdt_prof_principal,cdt_groupe WHERE pp_prof_ID=%u AND pp_classe_ID=%u AND pp_groupe_ID=ID_groupe",GetSQLValueString($_SESSION['ID_prof'],"int"), $row_RsClasse['ID_classe']);
		$Rspp = mysqli_query($conn_cahier_de_texte, $query_Rspp) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rspp = mysqli_fetch_assoc($Rspp);
		$totalRows_Rspp = mysqli_num_rows($Rspp);
		
		?>
		<tr>
		<td class="tab_detail_gris"><div align="center" class="Style7"><?php echo $row_RsClasse["nom_classe"]; ?>&nbsp;</div></td>
		<?php if ($access3=='Oui') { ?>
			<td class="tab_detail_gris"><div align="center">
			<select name="<?php echo 'pp_groupe'.$row_RsClasse['ID_classe']; ?>" size="1" class="menu_deroulant" id="<?php echo 'pp_groupe'.$row_RsClasse['ID_classe']; ?>">
			<?php do {  
				
				?>
				<option value="<?php  echo $row_Rsgroupe['ID_groupe'];?>"
				<?php if (($totalRows_Rspp>0)&&($row_Rsgroupe['ID_groupe']==$row_Rspp['pp_groupe_ID'])){ echo ' selected';};?>
				
				><?php echo $row_Rsgroupe['groupe'];
				
				?> </option>
				<?php
			} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
			$rows = mysqli_num_rows($Rsgroupe);
			if($rows > 0) {
				mysqli_data_seek($Rsgroupe, 0);
				$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
				
			}?>
			</select>
		  </div></td>
		<?php }; ?>
		
		<td class="tab_detail_gris"><div align="center" class="Style7">
		<?php
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsPPClasse = sprintf("SELECT nom_prof,identite,ID_prof FROM cdt_prof,cdt_prof_principal WHERE pp_prof_ID=ID_prof AND pp_classe_ID=%u",$row_RsClasse['ID_classe']);
		$RsPPClasse = mysqli_query($conn_cahier_de_texte, $query_RsPPClasse) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsPPClasse = mysqli_fetch_assoc($RsPPClasse);
		$totalRows_RsPPClasse = mysqli_num_rows($RsPPClasse);
		
		if (($totalRows_RsPPClasse==0)||(($totalRows_RsPPClasse==1)&&($row_RsClasse['ID_classe']==$row_Rspp['pp_classe_ID']))) {
			?>
			
			<input type="checkbox" name="<?php echo 'pp_classe'.$row_RsClasse['ID_classe']; ?>"   id="<?php echo 'pp_classe'.$row_RsClasse['ID_classe']; ?>"  
			<?php  if ($row_RsClasse['ID_classe']==$row_Rspp['pp_classe_ID']){echo 'checked';}; echo $access2=='Non'?' onclick="verif_multiPP('.$imin.', '.$imax.')"':'';?>>
          <?php }
		else { 
			//plusieurs pp
			if ($access3=='Non') { echo $row_RsPPClasse['identite']==""?$row_RsPPClasse['nom_prof']:$row_RsPPClasse['identite']; }
		
			else { 
						
		?>
			<input type="checkbox" name="<?php echo 'pp_classe'.$row_RsClasse['ID_classe']; ?>"   id="<?php echo 'pp_classe'.$row_RsClasse['ID_classe']; ?>"  
			<?php  if ($row_RsClasse['ID_classe']==$row_Rspp['pp_classe_ID']){echo 'checked';}; echo $access2=='Non'?' onclick="verif_multiPP('.$imin.', '.$imax.')"':'';?>>
			
								
				<?php		}
           };?>
	  </div></td>
	    <td class="tab_detail_gris"><div align="center" class="Style7">
		<?php
		do {
									if ($row_RsPPClasse['ID_prof']!=$_SESSION['ID_prof']) { // Cas ou le PP est le prof en question
										echo $row_RsPPClasse['identite']==""?$row_RsPPClasse['nom_prof'].'<br/>':$row_RsPPClasse['identite'].'<br/>';
									};
								} while ($row_RsPPClasse = mysqli_fetch_assoc($RsPPClasse))?>
		
		
		
		</div>
		</td>
	  </tr>
	  <?php mysqli_free_result($Rspp);mysqli_free_result($RsPPClasse);
	} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse));  ?>
	</table>
	<p>&nbsp;</p>
	<p>
	  <input type="hidden" name="MM_insert" value="form1">
	  <input type="submit" name="Submit" value="Enregistrer">
	  </p>
	</form>
  <?php ;} else {echo '<br />Vous devez pr&eacute;alablement saisir votre emploi du temps';};?>
  <p align="center">&nbsp;</p>
  <p align="center">
  <a href="enseignant.php">Retour au Menu Enseignant</a>
  </p>
  <DIV id=footer></DIV>
</DIV>
  </body>
  </html>
