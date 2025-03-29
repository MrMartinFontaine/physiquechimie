<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>7)){ header("Location: ../index.php");};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
require_once('../inc/module_absence_couleur.php');


//Trier la feuille d'appel  selon un critère de tri
//Certains etablissement ont ajoute une colonne nommee tri dans cdt classe (type int) 
//pour trier les classes du maniere specifique telle que classes college puis classes lycee
//dans ce cas $tri_classe = true sinon $tri_classe = false pour faire un tri alpha sur l'ensemble des noms de classes;
$tri_classe = false;   


//en 362 modif du script origine
$plages='';
$indexMotifsMin=0;

if (!isset($_GET['date1'])){$datetoday=date('Ymd');$date1_form=date('d/m/Y');$jourtoday= jour_semaine(date('d/m/Y'));;
} else {
	$datetoday=substr($_GET['date1'],6,4).substr($_GET['date1'],3,2).substr($_GET['date1'],0,2);
$date1_form= $_GET['date1'];$jourtoday= jour_semaine($_GET['date1']);};

//toutes les classes
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query( $conn_cahier_de_texte,$query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$Nb__RsClasse = mysqli_num_rows($RsClasse);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<link media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>

<script type="text/javascript" language="javascript" src="./jquery.js"></script>
<script type="text/javascript" language="javascript" src="./jquery.dataTables.js"></script>
<script type="text/javascript" language="javascript" class="init">

$.extend( $.fn.dataTable.defaults, {
    "searching": false,
    "ordering": false
} );
 
 
$(document).ready(function() {
    $('#appel').dataTable();
} );
</script>


<script type="text/javascript" src="../jscripts/utils.js"></script>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>
	
<link media="all" type="text/css" href="../styles/jquery-ui.css" rel="stylesheet">
	
 
<style type="text/css">
.blanc {color:#FFFFFF;font-weight:bold}
.element.style {
    display: block;
    height:40px;
    left: 0px;
    outline: 0 none;
    position: absolute;
    top: 0;
    width: 700px;
    z-index: 1001;
}

a.no_underline:link 
{ 
 text-decoration:none; 
} 

.cdi {
	font-size: 9px;
	color: #FF0000;
}

table td { width:80px; overflow:hidden; } // sauf col 1 110px

</style>


<?php if (!isset($_GET['no_actu'])){?>
<script language="JavaScript" type="text/JavaScript">
$(document).ready(function() 
	{
		var refreshId = setInterval(function()
			{
				location.reload();
			}, 120000);		
	}
	);
	
function solde(ele_absent_id, ok) {


	   	var xhr_object = null; 
		var _response = null;
		var _vie_sco_statut = null;
		var _ok=null;

		if ( ok == true ) {
			_vie_sco_statut = 'Y';
		} else {
			_vie_sco_statut = 'N';
		}
	     
		if (window.XMLHttpRequest) // Firefox 
	      	xhr_object = new XMLHttpRequest(); 
	   	else if (window.ActiveXObject) // Internet Explorer 
	      	xhr_object = new ActiveXObject("Microsoft.XMLHTTP"); 
	   	else { 
	   		// XMLHttpRequest non supporte par le navigateur 
	      	alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
	      	return; 
	   	} 
	   	xhr_object.open("POST", "./ajax_absence.php", true);
	   	xhr_object.onreadystatechange = function() { 
	    	if (xhr_object.readyState == 4) {
	          	_response = xhr_object.responseText; 
			$("#acquittement").html(_response );
			$("#acquittement").fadeIn("slow").delay(600).fadeOut("slow");
	        }
	   	}	 
	 	xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
	   	//xhr_object.send("ele_absent_id=" + ele_absent_id + "&id_viesco_prof=" + id_viesco_prof + "&solde=" + _solde  ); 	
		xhr_object.send("ele_absent_id=" + ele_absent_id + "&vie_sco_statut=" + _vie_sco_statut  ); 	 
		 	
 
}
	
</script>
<?php 
};?>

</head>
<body  bgcolor="#FAFAEF" onClick="$('#divPourDialog').dialog('close')";>
<div class="container">
<section>

<div id="acquittement"  style="display: none;"></div>

<table width="100%" border="0" class="tab_detail_gris">
<tr>
<td><form name="frm" method="GET" action="absence.php">
<script>
$(function() {
        	$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
        	$.datepicker.setDefaults($.datepicker.regional['fr']);
        	$('#date1').datepicker({firstDay:1});
});
</script>
<p align="center" class="Style13">Absences du &nbsp;
<input name='date1' type='text' id='date1' value="<?php echo $date1_form;?>" size="10"/>
<input name="submit" type="submit" value="S&eacute;lectionner"/>
</p>
</form></td>
<?php if (isset($_GET['no_actu'])){?>
<td>
<form name="frm" method="GET" action="absence.php
<?php
if (isset($_GET['date1'])){echo '?date1='.$_GET['date1'];}; ?>">
<input name="submit" type="submit" value="Actualiser"/>
</p>
</form>
</td>
<?php };?>
<td>
<a href="absence_pdf.php?date1=<?php if (isset($_GET['date1'])){echo $_GET['date1'];}else {echo date('d-m-Y');} ;?>" target="_blank"><img src="../images/pdf2.jpg" width="35" height="16" border="0"></a></td>
<td><div align="right"><?php 
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo '<span><img onClick="window.close()" src="../images/cancel.png" alt="Fermer cette page" title="Fermer  cette page" height="20" border="0"></img></span>';};
if ($_SESSION['droits']==4){echo '<a href="  ../direction/direction.php"><img src="../images/home-menu.gif" border="0"></a>';};
?></div></td>
</tr>
</table>
<?php //Quelles sont les classes ou l'appel a ete effectue
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);



if ($tri_classe==true){ 
$query_Abs_ou_Ret_cl = sprintf("SELECT DISTINCT classe_ID,classe,cdt_classe.tri
FROM ele_absent,cdt_classe
WHERE date= '%s'  AND classe_ID=ID_classe
ORDER BY tri,classe",
$datetoday);} 

else { //ordre alpha

$query_Abs_ou_Ret_cl = sprintf("SELECT DISTINCT classe_ID,classe
FROM ele_absent 
WHERE date= '%s'  
ORDER BY classe",
$datetoday);
};
//echo $query_Abs_ou_Ret_cl ;
$Abs_ou_Ret_cl= mysqli_query( $conn_cahier_de_texte,$query_Abs_ou_Ret_cl); //or die(mysqli_error($conn_cahier_de_texte));

$row_Abs_ou_Ret_cl = mysqli_fetch_assoc($Abs_ou_Ret_cl);
$Nb__Abs_ou_Ret_cl = mysqli_num_rows($Abs_ou_Ret_cl);

if ($Nb__Abs_ou_Ret_cl>0){ ?>


<table  id="appel" width="1200px">


<tbody>		
		
<?php 
	
	do {   //toutes les classes sauf regroupements
		if	($row_Abs_ou_Ret_cl["classe_ID"]<>0){
			include "../inc/module_absence_inc.php" ;
		} 
	
	} while ($row_Abs_ou_Ret_cl = mysqli_fetch_assoc($Abs_ou_Ret_cl));
	

	//puis les regroupements
	$row_Abs_ou_Ret_cl["classe_ID"]=0;
	$row_Abs_ou_Ret_cl["classe"]="Regroupement";
		
	include "../inc/module_absence_inc.php" ;

	mysqli_free_result($Abs_ou_Ret);
	
} else {echo '<br> Aucun appel r&eacute;alis&eacute; pour cette journ&eacute;e <br><br>';};
?>
</tbody>
</table>

<p style="padding: 10px 30px;"><a href="
<?php 
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'vie_scolaire.php';};
if ($_SESSION['droits']==4){echo '../direction/direction.php';};

?>
"><br>
Retour au menu
<?php
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'Vie scolaire';};
if ($_SESSION['droits']==4){echo 'Responsable Etablissement';};
?>
</a> </p>
<?php
if (!isset($_GET['no_actu'])){
if ((isset($_GET['date1']))&&($_GET['date1']==date('d/m/Y'))||(!isset($_GET['date1']))){
//on affiche les derniers eleves declares absents*

$query_derniers_ele = sprintf("SELECT ID, nom_ele,prenom_ele,eleve_ID, classe_ele,heure_debut,retard_V,retard_Nv,motif FROM ele_absent,ele_liste
			WHERE  absent='Y' 
			AND date= '%s' 
			AND ele_absent.eleve_ID=ele_liste.ID_ele 
			ORDER BY heure_debut DESC,classe_ele,nom_ele ",$datetoday);	
		
			$derniers_ele = mysqli_query( $conn_cahier_de_texte,$query_derniers_ele) or die(mysqli_error($conn_cahier_de_texte));
			$row_derniers_ele= mysqli_fetch_assoc($derniers_ele);
			$totalrow_derniers_ele = mysqli_num_rows($derniers_ele);

//on recherche les SMS
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$df=date('Y-m-d');
$query_Rsmessage2 =sprintf("SELECT * FROM cdt_message_contenu,cdt_prof WHERE prof_ID=ID_prof AND dest_ID=3  AND substring(date_envoi,1,10)='%s' ORDER BY date_envoi DESC",$df);

$Rsmessage2 = mysqli_query( $conn_cahier_de_texte,$query_Rsmessage2) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmessage2 = mysqli_fetch_assoc($Rsmessage2);
$Nb__Rsmessage2 = mysqli_num_rows($Rsmessage2);


if (($totalrow_derniers_ele>0)||($Nb__Rsmessage2>0)){

?>

<div align="left" width="1200"  height="25" id="divPourDialog" title="Messages et absents d&eacute;clar&eacute;s aujourd'hui">

  <div align="left"><a href="absence.php?no_actu=1" class="no_underline"><em>Oter temporairement l'actualisation automatique</em></a>
    <?php
if ($Nb__Rsmessage2>0){$i=0;echo '<p class="tab_detail_bleu">';
do { $i=$i+1;
 echo '<strong>&nbsp;'.$row_Rsmessage2['identite'].'   '.substr($row_Rsmessage2['date_envoi'],10,6).'</strong>';
echo '&nbsp;&nbsp;&nbsp;'.$row_Rsmessage2['message'].'<br>';
} while (($row_Rsmessage2 = mysqli_fetch_assoc($Rsmessage2))&& $i <6);
};echo '</p>';
if ($totalrow_derniers_ele>0){$i=0;echo '<p class="tab_detail_gris" >';
do {$i=$i+1;
echo $row_derniers_ele['nom_ele'].' '.$row_derniers_ele['prenom_ele']. ' - ' .$row_derniers_ele['classe_ele']. ' - ' .$row_derniers_ele['heure_debut'];
if ($row_derniers_ele['retard_V']=='Y'){echo ' - retard_Val';};
if ($row_derniers_ele['retard_Nv']=='Y'){echo ' - retard_Non-Val';};
if ($row_derniers_ele['motif'] >0 ){echo ' - '.$motifs[$row_derniers_ele['motif']];};
echo '&nbsp; / &nbsp; ';
} while ( ($row_derniers_ele = mysqli_fetch_assoc($derniers_ele)) && ($i<6));
} else {echo ' Pas d\'absents';};
?>
  </div>
</div><?php };?>

</div>

<script>
  $(function() {
    $( "#divPourDialog" ).dialog({width:'700px',modal: true})
 
  });
</script>
<?php
};
};
if (isset($RsClasse)){mysqli_free_result($RsClasse);};
if (isset($derniers_ele)){mysqli_free_result($derniers_ele);};
if (isset($Rsmessage2)){mysqli_free_result($Rsmessage2);};
if (isset($Rsele)){mysqli_free_result($Rsele);};
if (isset($Rsnbele)){mysqli_free_result($Rsnbele);};

if (isset($Rs_emploi)){mysqli_free_result($Rs_emploi);};
if (isset($RsSemdate)){mysqli_free_result($RsSemdate);};
if (isset($Abs_ou_Ret_cl)){mysqli_free_result($Abs_ou_Ret_cl);};
if (isset($Rsnom_regroup)){mysqli_free_result($Rsnom_regroup);};
if (isset($Rsabs)){mysqli_free_result($Rsabs);};



?></section>

</body>
</html>
