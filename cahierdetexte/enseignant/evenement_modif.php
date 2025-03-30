<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
$datetoday=date('y-m-d');


  
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") && (isset($_POST['message']))) {
$date1=substr($_POST['date1'],6,4).'-'.substr($_POST['date1'],3,2).'-'.substr($_POST['date1'],0,2);
$date2=substr($_POST['date2'],6,4).'-'.substr($_POST['date2'],3,2).'-'.substr($_POST['date2'],0,2);
$heure1=$_POST['heure_debut_h'].'h'.$_POST['heure_debut_min'];
$heure2=$_POST['heure_fin_h'].'h'.$_POST['heure_fin_min'];
  $updateSQL = sprintf(" UPDATE `cdt_evenement_contenu` SET titre_even =%s, detail=%s , prof_ID=%u , date_debut=%s ,heure_debut=%s, date_fin=%s,heure_fin=%s,date_envoi=%s WHERE ID_even=%u" ,
  GetSQLValueString($_POST['titre_even'], "text"),
  GetSQLValueString($_POST['message'], "text"),
  GetSQLValueString($_SESSION['ID_prof'], "int"),
  GetSQLValueString($date1, "text"),
  GetSQLValueString($heure1, "text"),
  GetSQLValueString($date2, "text"),
  GetSQLValueString($heure2, "text"),
  GetSQLValueString($datetoday, "text"),
GetSQLValueString($_GET['ID_even'],"int")
  
  );
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));

 

$insertGoTo = "evenement_ajout.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
header(sprintf("Location: %s", $insertGoTo));
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifMessage =sprintf("SELECT * FROM cdt_evenement_contenu,cdt_prof WHERE ID_even=%u AND cdt_evenement_contenu.prof_ID = cdt_prof.ID_prof ",$_GET['ID_even'] );
$RsModifMessage = mysqli_query($conn_cahier_de_texte, $query_RsModifMessage) or die(mysqli_error($conn_cahier_de_texte));
$row_RsModifMessage = mysqli_fetch_assoc($RsModifMessage);
$totalRows_RsModifMessage = mysqli_num_rows($RsModifMessage);

//Dissociation heure et date
$hhd=substr($row_RsModifMessage['heure_debut'],0,2);
$mmd=substr($row_RsModifMessage['heure_debut'],3,2);
$hhf=substr($row_RsModifMessage['heure_fin'],0,2);
$mmf=substr($row_RsModifMessage['heure_fin'],3,2);
$date_debut=substr($row_RsModifMessage['date_debut'],8,2).'/'.substr($row_RsModifMessage['date_debut'],5,2).'/'.substr($row_RsModifMessage['date_debut'],0,4);
$date_fin=substr($row_RsModifMessage['date_fin'],8,2).'/'.substr($row_RsModifMessage['date_fin'],5,2).'/'.substr($row_RsModifMessage['date_fin'],0,4);



mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsdest =sprintf("SELECT * FROM cdt_evenement_destinataire WHERE even_ID=%u ",$_GET['ID_even'] );
$Rsdest = mysqli_query($conn_cahier_de_texte, $query_Rsdest) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsdest = mysqli_fetch_assoc($Rsdest);
$totalRows_Rsdest = mysqli_num_rows($Rsdest);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../styles/style_default.css" rel="stylesheet" type="text/css">
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />	
<style>
form{
   margin:5;
   padding:0;
}
.bordure_grise {
	border: 1px solid #CCCCCC;
}
</style>

<script type="text/JavaScript">
function verifier() {
  var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
   for (var i=0; i<cases.length; i++)  {     // on les parcourt
       if (cases[i].type == 'checkbox')    // si on a une checkbox...
	     { //alert(cases[i].checked);
		 if (cases[i].checked==true) {  	//si la case est cochee, envoi du formulaire		
		        if(cases[i].name != 'online') {return true}
				}; 
		 }
		 
   };
   alert("Il faut indiquer un destinataire. Cocher au moins une classe");
   return false;
   }
</script>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>
</head>
<body >

<div id="">
<!--<div id="container3"> en remplacement du div ci-dessus pour un tableau moins large -->
<p><table class="lire_bordure" width="90%" border="0" align="center" cellpadding="0" cellspacing="0">

  <tr class="lire_cellule_4">
    <td >CAHIER DE TEXTES - VIE SCOLAIRE - Modification d'un &eacute;v&eacute;nement</td>
    <td ><div align="right"><a href="evenement_ajout.php"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></div></td>
  </tr>  <tr>
    <td colspan="2" valign="top" class="lire_cellule_2" ><br /><br />
      <form onLoad= "formfocus()" method="post"  name="form1" action="<?php echo $editFormAction; ?>" >



      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td valign="top">&nbsp;</td>
          <td valign="top"><br />
            <p align="center">&nbsp;</p>
			
        <p align="center"><strong>Saisie des dates et heures</strong> : Pour un &eacute;v&eacute;nement ponctuel sur une journ&eacute;e, les deux dates sont &eacute;videmment identiques .<br> 
        Pour une planification sur plusieurs jours (Stage par exemple), les heures ne seront pas prises en compte ni affich&eacute;es. </p>
		
<script>
	$(function() {
	    $.datepicker.setDefaults($.datepicker.regional['fr']);
		var dates = $( "#date1, #date2" ).datepicker({
			defaultDate: "+1w",
			changeMonth: true,
			numberOfMonths: 1,
			firstDay:1,
			onSelect: function( selectedDate ) {
				var option = this.id == "date1" ? "minDate" : "maxDate",
					instance = $( this ).data( "datepicker" ),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
				dates.not( this ).datepicker( "option", option, date );
			}
		});
	});
</script>   
        <p align="center">Du&nbsp;
          <input name='date1' type='text' id='date1' value="<?php echo $date_debut;?>" size="10"/>
          
          <select name="heure_debut_h">
            <option value="07"<?php if ($hhd=='07'){ echo 'selected';};?>>07 h</option>
            <option value="08"<?php if ($hhd=='08'){ echo 'selected';};?>>08 h</option>
            <option value="09"<?php if ($hhd=='09'){ echo 'selected';};?>>09 h</option>
            <option value="10"<?php if ($hhd=='10'){ echo 'selected';};?>>10 h</option>
            <option value="11"<?php if ($hhd=='11'){ echo 'selected';};?>>11 h</option>
            <option value="12"<?php if ($hhd=='12'){ echo 'selected';};?>>12 h</option>
            <option value="13"<?php if ($hhd=='13'){ echo 'selected';};?>>13 h</option>
            <option value="14"<?php if ($hhd=='14'){ echo 'selected';};?>>14 h</option>
            <option value="15"<?php if ($hhd=='15'){ echo 'selected';};?>>15 h</option>
            <option value="16"<?php if ($hhd=='16'){ echo 'selected';};?>>16 h</option>
            <option value="17"<?php if ($hhd=='17'){ echo 'selected';};?>>17 h</option>
            <option value="18"<?php if ($hhd=='18'){ echo 'selected';};?>>18 h</option>
            <option value="19"<?php if ($hhd=='19'){ echo 'selected';};?>>19 h</option>
            <option value="20"<?php if ($hhd=='20'){ echo 'selected';};?>>20 h</option>
            <option value="21"<?php if ($hhd=='21'){ echo 'selected';};?>>21 h</option>
            <option value="22"<?php if ($hhd=='22'){ echo 'selected';};?>>22 h</option>
          </select>
          <select name="heure_debut_min" size="1">
            <option value="00"<?php if ($mmd=='00'){ echo 'selected';};?>>00 min</option>
            <option value="05"<?php if ($mmd=='05'){ echo 'selected';};?>>05 min</option>
            <option value="10"<?php if ($mmd=='10'){ echo 'selected';};?>>10 min</option>
            <option value="15"<?php if ($mmd=='15'){ echo 'selected';};?>>15 min</option>
            <option value="20"<?php if ($mmd=='20'){ echo 'selected';};?>>05 min</option>
            <option value="25"<?php if ($mmd=='25'){ echo 'selected';};?>>25 min</option>
            <option value="30"<?php if ($mmd=='30'){ echo 'selected';};?>>30 min</option>
            <option value="35"<?php if ($mmd=='35'){ echo 'selected';};?>>35 min</option>
            <option value="40"<?php if ($mmd=='40'){ echo 'selected';};?>>40 min</option>
            <option value="45"<?php if ($mmd=='45'){ echo 'selected';};?>>45 min</option>
            <option value="50"<?php if ($mmd=='50'){ echo 'selected';};?>>50 min</option>
            <option value="55"<?php if ($mmd=='55'){ echo 'selected';};?>>55 min</option>
          </select>
&nbsp;au&nbsp;&nbsp;
          <input  name='date2' type='text' id='date2' value="<?php echo $date_fin;?>" size="10" />
         
          <select name="heure_fin_h" id="heure_fin_h">
            <option value="07"<?php if ($hhf=='07'){ echo 'selected';};?>>07 h</option>
            <option value="08"<?php if ($hhf=='08'){ echo 'selected';};?>>08 h</option>
            <option value="09"<?php if ($hhf=='09'){ echo 'selected';};?>>09 h</option>
            <option value="10"<?php if ($hhf=='10'){ echo 'selected';};?>>10 h</option>
            <option value="11"<?php if ($hhf=='11'){ echo 'selected';};?>>11 h</option>
            <option value="12"<?php if ($hhf=='12'){ echo 'selected';};?>>12 h</option>
            <option value="13"<?php if ($hhf=='13'){ echo 'selected';};?>>13 h</option>
            <option value="14"<?php if ($hhf=='14'){ echo 'selected';};?>>14 h</option>
            <option value="15"<?php if ($hhf=='15'){ echo 'selected';};?>>15 h</option>
            <option value="16"<?php if ($hhf=='16'){ echo 'selected';};?>>16 h</option>
            <option value="17"<?php if ($hhf=='17'){ echo 'selected';};?>>17 h</option>
            <option value="18"<?php if ($hhf=='18'){ echo 'selected';};?>>18 h</option>
            <option value="19"<?php if ($hhf=='19'){ echo 'selected';};?>>19 h</option>
            <option value="20"<?php if ($hhf=='20'){ echo 'selected';};?>>20 h</option>
            <option value="21"<?php if ($hhf=='21'){ echo 'selected';};?>>21 h</option>
            <option value="22"<?php if ($hhf=='22'){ echo 'selected';};?>>22 h</option>
          </select>
          <select name="heure_fin_min" size="1" id="heure_fin_min">
            <option value="00"<?php if ($mmf=='00'){ echo 'selected';};?>>00 min</option>
            <option value="05"<?php if ($mmf=='05'){ echo 'selected';};?>>05 min</option>
            <option value="10"<?php if ($mmf=='10'){ echo 'selected';};?>>10 min</option>
            <option value="15"<?php if ($mmf=='15'){ echo 'selected';};?>>15 min</option>
            <option value="20"<?php if ($mmf=='20'){ echo 'selected';};?>>05 min</option>
            <option value="25"<?php if ($mmf=='25'){ echo 'selected';};?>>25 min</option>
            <option value="30"<?php if ($mmf=='30'){ echo 'selected';};?>>30 min</option>
            <option value="35"<?php if ($mmf=='35'){ echo 'selected';};?>>35 min</option>
            <option value="40"<?php if ($mmf=='40'){ echo 'selected';};?>>40 min</option>
            <option value="45"<?php if ($mmf=='45'){ echo 'selected';};?>>45 min</option>
            <option value="50"<?php if ($mmf=='50'){ echo 'selected';};?>>50 min</option>
            <option value="55"<?php if ($mmf=='55'){ echo 'selected';};?>>55 min</option>
          </select>
        </p>
      <p align="center"><b>Titre de l'&eacute;v&eacute;nement</b> <br />
                 <input name="titre_even" type="text" id="titre_even" value="<?php echo $row_RsModifMessage['titre_even']; ?>" size="50">
        </p><br />
              <table align="center" cellspacing="5">
                <tr valign="baseline">
                  <td valign="top">
				  
<?php 
if ((isset($_SESSION['affiche_xinha']))&&($_SESSION['affiche_xinha']==1)){
	include('../vie_scolaire/area_message.php');}
else { 
	include('../vie_scolaire/area_message_tiny.php');};
;?>
                    <p>
                      <textarea name="message" rows="7" id="message" width="200" height= "80" ><?php echo $row_RsModifMessage['detail']; ?></textarea>
                    </p></td>
              </tr>
                <tr valign="baseline">
                  <td><div align="center">

                  <p>&nbsp; </p>
                  <p>
                    <input name="submit" type="submit" value="Enregistrer les modifications">
                    </p>
                  <p><br>
                     <a href="evenement_ajout.php?ID_even=<?php echo $_GET['ID_even'];?>&classe_ID=<?php echo $_GET['classe_ID'];?>&groupe_ID=<?php echo $_GET['groupe_ID'];?>">Retour au menu gestion des &eacute;v&eacute;nements</a></p>
                </div></td>
              </tr>
              </table>
 
            <input type="hidden" name="nb_classes" value="<?php echo $totalRows_RsClasse;?>">
            <input type="hidden" name="MM_update" value="form1">
          </form></td>
    </tr>

</table>
</p><p>
</p>
</div>




</DIV>
</body>
</html>
<?php
mysqli_free_result($RsModifMessage);
mysqli_free_result($RsClasse);
mysqli_free_result($Rsdest);
mysqli_free_result($Rsgroupe);
?>
