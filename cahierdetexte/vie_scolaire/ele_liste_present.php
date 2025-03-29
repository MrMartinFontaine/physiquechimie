<?php 
include "../authentification/authcheck.php" ;

if (($_SESSION['droits']<>3)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") && (isset($_GET['code_classe']))) {
//Envoi en vie scolaire	

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsClasse_ID = "SELECT ID_classe FROM cdt_classe WHERE code_classe='".$_GET['code_classe']."'";
	$RsClasse_ID = mysqli_query($conn_cahier_de_texte, $query_RsClasse_ID) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsClasse_ID = mysqli_fetch_assoc($RsClasse_ID);

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$req = "SELECT min(ID_ele) AS min, max(ID_ele) AS max FROM ele_liste;"; 
	$res = mysqli_query($conn_cahier_de_texte, $req) or die(mysqli_error($conn_cahier_de_texte)); 
	$row = mysqli_fetch_assoc($res); 
	$nblign=(int) $row['max'];
	$nb_insert=0;
	$salle='CDI';
	$taf='';
	for ($i=(int) $row['min']; $i<=$nblign; $i++) { 
			$refele='ele'.$i;
			$reftaf='taf'.$i;
			if ((isset($_POST[$reftaf])) && ($_POST[$reftaf]<>'')){$taf=$_POST[$reftaf];};
			
			
			if ((isset($_POST[$refele])) && ($_POST[$refele]=='on')){ //eleve coche
			// eleve existe -t-il
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_RsExiste = "SELECT * FROM ele_present WHERE eleve_ID=".$i." AND heure_fin='00:00' ORDER BY date_heure DESC LIMIT 1";
			$RsExiste = mysqli_query($conn_cahier_de_texte, $query_RsExiste) or die(mysqli_error($conn_cahier_de_texte));
			$totalRows_RsExiste = mysqli_num_rows($RsExiste);
			
			if ($totalRows_RsExiste==0){
			
					$insertSQL2= sprintf("INSERT INTO `ele_present` ( `eleve_ID`,`prof_ID`,`classe_ID`,`heure_debut`,`heure_fin`,`travail`,`salle`)  VALUES ('%u','%u','%u','%s','%s','%s','%s');",$i,$_SESSION['ID_prof'],$row_RsClasse_ID['ID_classe'],date("H:i"),'00:00',$taf,$salle);
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));
					
					};
			}
			else // eleve pas coche
			{
			//si l'enregistrement existe - mettre une heure de fin
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_RsExiste = "SELECT * FROM ele_present WHERE eleve_ID=".$i." AND heure_fin='00:00' AND substring(date_heure,1,10)='".date('Y-m-d')."'  ORDER BY date_heure DESC LIMIT 1";

			$RsExiste = mysqli_query($conn_cahier_de_texte, $query_RsExiste) or die(mysqli_error($conn_cahier_de_texte));
			$row_RsExiste = mysqli_fetch_assoc($RsExiste);
			$totalRows_RsExiste = mysqli_num_rows($RsExiste);
				if ($totalRows_RsExiste==1){
				$update="UPDATE `ele_present` SET heure_fin='".date("H:i")."' WHERE ID=".$row_RsExiste['ID'];
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result3 = mysqli_query($conn_cahier_de_texte, $update) or die(mysqli_error($conn_cahier_de_texte));
				};
			
			};
			mysqli_free_result($RsExiste);
	}//du for
	
	
	mysqli_free_result($RsClasse_ID);
?>
	<SCRIPT language=javascript>
	alert('La liste des pr\351sents  a \351t\351 envoy\351ee en vie scolaire');
	</SCRIPT>
<?php

}


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>



<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
</HEAD>
<BODY>
<DIV id=page>
  <p>
    <?php 
$header_description="Gestion de la liste des &eacute;l&egrave;ves pr&eacute;sents au CDI";
require_once "../templates/default/header.php";
?>
  </p>
  


<form method="GET"  name="form" action="ele_liste_present.php">
    <table width="95%" align="center">
      <tr valign="baseline">
        <td class="tab_detail_gris">
		<div style="float:left;display:inline;width:95%">

            <div align="center">
              <select name="code_classe" id="code_classe">
                <option value="value">S&eacute;lectionner la classe</option>
                <?php  do { ?>
                <option value="<?php echo $row_RsClasse['code_classe']?>"
			  <?php 
			  if ((isset($_GET['code_classe']))&&($row_RsClasse["code_classe"]==$_GET['code_classe'])){echo 'selected=" selected"';};
			  ?>><?php echo $row_RsClasse['nom_classe']?></option>
                <?php	} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)); ?>
              </select>
              <input name="submit" type="submit" value="S&eacute;lectionner">
            </div>
		</div>
<div style="width:5%;float:right;">
<?php 
if ($_SESSION['droits']==8){?>
<a href="../enseignant/enseignant.php"><img src="../images/home-menu.gif">&nbsp;&nbsp;</a>
<?php };

if ($_SESSION['droits']==3){?>
<a href="vie_scolaire.php"><img src="../images/home-menu.gif">&nbsp;&nbsp;</a>
<?php };?>
</div>
		  
		  
		  
	    </td>
      </tr>
    </table>
  </form>
 <br/><br/>
  <?php 
if (isset($_GET['code_classe'])) {
	
$choix_classe=$_GET['code_classe'];


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsele_liste = sprintf("SELECT * FROM ele_liste WHERE classe_ele= '%s' ORDER BY nom_ele,prenom_ele ASC",$choix_classe);
$Rsele_liste = mysqli_query($conn_cahier_de_texte, $query_Rsele_liste) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsele_liste = mysqli_fetch_assoc($Rsele_liste);
$totalRows_Rsele_liste = mysqli_num_rows($Rsele_liste);


if($totalRows_Rsele_liste>0){?>



<form method="POST" name="form1" id="form1" action="ele_liste_present.php?code_classe=<?php echo $_GET['code_classe'];?>" >
  <input name="submit" type="submit" value="Envoyer en vie scolaire"><br/><br/>
  <table border="0" align="center">
    <tr>
      <td class="Style6">&nbsp;</td>
      <td class="Style6"><div align="center">Nom </div></td>
      <td class="Style6"><div align="center" >Pr&eacute;nom</div></td>
      <td class="Style6">Pr&eacute;sence&nbsp; </td>
      <td width="200" class="Style6">Travail</td>
    </tr>
	<tr>
      <td class="tab_detail_gris">&nbsp;</td>
      <td class="tab_detail_gris"><div align="center"><?php echo $totalRows_Rsele_liste. '&nbsp;&eacute;l&egrave;ves ';?></div></td>
      <td class="tab_detail_gris"><div align="center" ></div></td>
      <td class="tab_detail_gris"><SCRIPT>
function cocherTout(etat)
{
var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
for(var i=1; i<cases.length; i++)     // on les parcourt
if(cases[i].type=='checkbox' && cases[i].name!='online')     // si on a une checkbox....
cases[i].checked = etat;     // ... on la coche ou non
}


function decocherTout()
{
var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
cases[0].checked = false;     // ... on decoche la premiere, le TOUS
}
</SCRIPT>
Tous
<input type="checkbox" name="checkbox" id="tousaucun" onclick=cocherTout(this.checked) value="ok" >
</td>
      <td width="200" class="tab_detail_gris"><div align="center"><?php echo 'Le '.date('d/m/Y  -  H:i');?></div></td>
	</tr>
	
    <?php 
	$n=1;
	do { ?>
      <tr>
        <td class="tab_detail_gris">
		<?php 
		echo $n;
		// echo '  '.$row_Rsele_liste['ID_ele']; 
		?></td>
        <td class="tab_detail_gris"><div align="left"><?php echo $row_Rsele_liste['nom_ele']; ?></div></td>
        <td class="tab_detail_gris"><div align="left"><?php echo $row_Rsele_liste['prenom_ele']; ?></div></td>
        <td class="tab_detail_gris">
		


		

		<input  type="checkbox" name="<?php echo 'ele'.$row_Rsele_liste['ID_ele'];?>"  id="<?php echo 'ele'.$row_Rsele_liste['ID_ele'];?>" onclick='decocherTout()' value="on" 
		
		<?php 
		//eleve deja pointe 
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rsele_pointe = sprintf("SELECT * FROM ele_present WHERE eleve_ID= '%u' AND substring(date_heure,1,10)= '%s' ORDER BY date_heure DESC LIMIT 1",$row_Rsele_liste['ID_ele'],date('Y-m-d'));
		$Rsele_pointe = mysqli_query($conn_cahier_de_texte, $query_Rsele_pointe) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rsele_pointe = mysqli_fetch_assoc($Rsele_pointe);
		$totalRows_Rsele_pointe = mysqli_num_rows($Rsele_pointe);

		//encore au cdi
		if (($totalRows_Rsele_pointe==1)&&($row_Rsele_pointe['heure_fin']=='00:00' )){echo ' checked';};
			
		?>
		>
		<?php //echo $totalRows_Rsele_pointe.'  ';
		if (($totalRows_Rsele_pointe==1)&&($row_Rsele_pointe['heure_fin']=='00:00' )){
		echo	'&nbsp;'.$row_Rsele_pointe['heure_debut'];
		};?>	</td>
        <td width="200" class="tab_detail_gris">


<script>
  $(function(){
    var mySpan = $("#<?php echo 'taf'.$row_Rsele_liste['ID_ele'];?>").<?php 
	if (($totalRows_Rsele_pointe==1)&&($row_Rsele_pointe['heure_fin']=='00:00' )){echo 'show';} else {echo'hide';};
	?>();
    $("#<?php echo 'ele'.$row_Rsele_liste['ID_ele'];?>").click(function(){
      if($(this).is(":checked"))
        mySpan.show();
      else
        mySpan.hide();
    });
  });
</script>
		<input   name="<?php echo 'taf'.$row_Rsele_liste['ID_ele'];?>" type="text" id="<?php echo 'taf'.$row_Rsele_liste['ID_ele'];?>"  onclick="document.getElementById('<?php echo 'ele'.$row_Rsele_liste['ID_ele'];?>').checked=true;" value="<?php 
		
		if (($totalRows_Rsele_pointe==1)&&($row_Rsele_pointe['heure_fin']=='00:00' )){
		echo $row_Rsele_pointe['travail'];
		};
		 ?>" size="40" >


		</td>
      </tr>
      <?php 
	  $n=$n+1;
	  } while ($row_Rsele_liste = mysqli_fetch_assoc($Rsele_liste)); 
	  
	  ?>
  </table>
 <!--Salle (facultatif)  <input name="salle" type="text" size="5"> -->
  <input type="hidden" name="nb_ele" value="<?php echo $totalRows_Rsele_liste;?>">
  <input type="hidden" name="MM_insert" value="form1">
  <br>
  <input name="submit" type="submit" value="Envoyer en vie scolaire">
  </form>
  <?php 
  }
  } 
  
  if ($_SESSION['droits']==8){?><p align="center"><a href="../enseignant/enseignant.php">Retour au Menu Enseignant</a></p><?php };
  if ($_SESSION['droits']==3){?><p align="center"><a href="vie_scolaire.php">Retour au Menu Vie scolaire</a></p>  
  <?php };
  ?>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsClasse);
?>
