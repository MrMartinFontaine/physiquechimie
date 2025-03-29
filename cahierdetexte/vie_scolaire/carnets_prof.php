<?php 


include "../authentification/authcheck.php" ;
include "./carnets_inc.php";

$modif=false;	
// controle de post avant enregistrement des modifs du champ details	
if ((isset($_POST['ID_even'])) && ($_POST['ID_even']!='') && ($_POST['ID_even'] > 0)) { $modif=true ;};

if ( isset($_POST['modifDetails'])  && $modif==true) { //modif  restreinte à details 
	$updateSQL = sprintf("UPDATE ele_absent SET details = %s  
		WHERE ele_absent.ID = %u ", 
		GetSQLValueString($_POST['modifDetails'], "text"), 
		GetSQLValueString($_POST['ID_even'], "int")  );

	  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	  $Result = mysqli_query( $conn_cahier_de_texte,$updateSQL) or die(mysqli_error($conn_cahier_de_texte));
	 // $_POST['solde']='';	$_POST['details']=''; //pour pas faire des doublons en relance de page !	
	};

// validation du controle de signature des mots !
if ( isset($_POST['Signature'])  && ($_POST['Signature']=='ok') && $modif=true) { //modif  restreinte à details 
	 $signe= 'Y';
	$updateSQL = sprintf("UPDATE ele_absent SET signature = %s 
		WHERE ID = %u    ",
		GetSQLValueString($signe, "text") ,
		GetSQLValueString($_POST['ID_even'], "int")  );
		
	  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	  $Result = mysqli_query( $conn_cahier_de_texte,$updateSQL) or die(mysqli_error($conn_cahier_de_texte));;  
};


	

// gestion de la plage de dates à afficher
//on recupere d'abord la date de rentree
 mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_param = 	sprintf(" SELECT param_val FROM cdt_params 
							WHERE param_nom ='date_raz_compteur' " );
	
	$params = mysqli_query( $conn_cahier_de_texte,$query_param) or die(mysqli_error($conn_cahier_de_texte));
	$row_params = mysqli_fetch_assoc($params);
	$date_rentree_sql= $row_params['param_val']; 
	mysqli_free_result($params); 


if (!isset($_GET['date1']))
     { // pour bilan par défaut depuis la rentrée  $date1_sql=$date_rentree_sql;	$date1_form=date('d/m/Y',strtotime( $date1_sql));
	// pour bilan sur $decal jours	  
	$decal=30;		
	$time_debut_bilan = mktime(0, 0, 0, date("m") , date("d") - $decal, date("Y"));
	$date1_sql=date('Ymd',$time_debut_bilan);
	if ($date1_sql < $date_rentree_sql) {$date1_sql = $date_rentree_sql;};
		
	$date1_form=date('d/m/Y',$time_debut_bilan);

		}
	else {
		$date1_sql=substr($_GET['date1'],6,4).substr($_GET['date1'],3,2).substr($_GET['date1'],0,2);
		$date1_form= $_GET['date1'];};

if (!isset($_GET['date2']))
		{ $date_cejour = mktime(0, 0, 0, date("m") , date("d") , date("Y"));
	   $date2_sql=date('Ymd', $date_cejour);
		$date2_form=date('d/m/Y', $date_cejour);}
	else {	$date2_sql=substr($_GET['date2'],6,4).substr($_GET['date2'],3,2).substr($_GET['date2'],0,2);
			$date2_form= $_GET['date2']; //sauf pour punitions à retranscrire
		};

$msgTitre="";$sql_classeOuElv='';
// options d'affichage restreint à une classe ou un elv (suivi de signature)		
if (isset($_GET['classe'])&&($_GET['classe']!=-1) && ($_GET['classe']!='')) {
	$sql_classeOuElv = " AND classe='".$_GET['classe']. "'" ; 
	$classe= $_GET['classe'] ;
	$classe_lien = 'classe='.$_GET['classe'].'&' ;$msgTitre='pour les '.$classe;
} else { 
$sql_classeOuElv ='' ; $classe_lien=''; 
};

if (isset($_GET['elvid']) && ($_GET['elvid']!='')) {$elvid= $_GET['elvid'] ; $_GET['elvid']='';// pour elargir au tour suivant 
 // on ecrase le filtre sql sur classe
 $sql_classeOuElv = 'AND eleve_ID="'.$elvid. '"' ; $msgTitre='pour l\'&eacute;l&egrave;ve list&eacute;';
 
 };

if (isset($_GET['classe'])){$classe= $_GET['classe'] ;}else {$classe=''; }; 
if (isset($_GET['idprof'])){$idprof= $_GET['idprof'] ;}else {$idprof=$_SESSION['ID_prof']; }; 

if (isset($_GET['nonSignes']) && ($_GET['nonSignes']=='Y')) {
	$nonSignes ='Y' ; $nonSignes_relance="&nonSignes=Y";
	$sql_classeOuElv .= " AND signature !='Y' ";
} else { $nonSignes ='N' ; $nonSignes_relance="";};


// liste des classes pour select choix de classe
//pour select  classes DU prof
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = sprintf("SELECT DISTINCT nom_classe,ID_classe 
	FROM cdt_classe,cdt_emploi_du_temps 
	WHERE cdt_classe.ID_classe=cdt_emploi_du_temps.classe_ID 
	AND prof_ID=%u 
	ORDER BY nom_classe ASC",
	$_SESSION['ID_prof']);
$RsClasse = mysqli_query( $conn_cahier_de_texte,$query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
// select only classes avec incident
//$ query_RsClasse = "SELECT distinct classe FROM ele_absent WHERE length( classe ) = 2  ORDER BY classe ASC";



// liens de relance de carnets_profs
if (isset($_GET['classe'])&&($_GET['classe']!='')){ $lien_classe='classe='.$_GET['classe'].'&';} else {$lien_classe='';};
//pas de & en tete!
 if (isset($_GET['elvid'])&&($_GET['elvid']!='')){$lien_elvid='elvid='.$_GET['elvid'].'&';} else {$lien_elvid='';};

$debut_lien_relance='carnets_prof.php?'.$lien_elvid.$lien_classe.$nonSignes_relance;
$dates_relance='date1='.$date1_form.'&date2='.$date2_form.'&submit=actualiser' ;
$lien_relance=$debut_lien_relance.$dates_relance ;// sans les " " pour utiliser dans les POST ?????
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Bilan des incidents d&eacutesclar&eacute;s </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<!-- <link media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet> -->
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />

<style type="text/css">

tr { background-color:#FAF6EF; border:1px solid #FAF6EF ; border-collapse: collapse; } 
td { text-align:left ; padding:1px ; padding-left:4px ;vertical-align:middle ; border: 0px solid #FAF6EF }
.fond { background-color:#FAF6EF ; border:1px solid #FAF6EF ; }

.ligne_gris_fonce {	font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt;color: #000066;
					border-bottom : 1px solid #CCCCCC; border-left:1px solid #9E9E9E ; border-right:1px solid #9E9E9E ;	border-collapse:collapse; background-color:#DFDFDF ;           // #9E9E9E gris souris
					text-align: left;	vertical-align: top;}	
.ligne_gris_clair {	font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;color: #000066;// 12px=9pt
					border-bottom : 1px solid #D8D8D8; border-left:1px solid #9E9E9E ; border-right:1px solid#9E9E9E; border-collapse:collapse; background-color: #F2F2F2; //#F2F2F2
					text-align: left;	vertical-align: top;}	

.left { border-left:0px solid black ;} 
.right { border-right:0px solid black ;}// #CCCCCC solid;} 
.encadre { border:4px solid black;  padding : 8px;  > 

		
.ligne_bilan {	font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt;color: black ;
					border : 0px solid #FAF6EF ;	border-collapse:collapse; background-color: #ECCEF5;
					text-align: left;	vertical-align: middle;}	
					
.area_in_ligne {	border:1px solid #CCCCCC;	padding :1px ;
				 background-color:white;font-size:10px	;}

</style>

<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>

</head>
<body style="background-color: #DEDEDE;">

<div id="container" align="center" style=" min-width:600px;background-color: #FAF6EF; min-height:700px;border: none;"> 



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
<table border="0"  align="center" style="padding-top:8px;" >
<tr> <td style="padding:10px;padding-top:0px;border:0px solid black;"> <?php //conteneur ! ?>

 <form name="frm" method="GET" action="carnets_prof.php">
 
 
 <table  align="left" border="0" style="margin:5px 20px 0px 20px;">
 <tr class="ligne_gris_fonce" style="border:1px solid gray ;"> 
 <td align="center" colspan="2" style="padding: 6px; border:1px solid gray; border-right:0px solid;">
 <b>  Bilans pour la p&eacute;riode du &nbsp;<?php 
 if ( $date1_sql!=$date_rentree_sql)  { 	?> 
	<a href="carnets_prof.php?<?php echo $classe_lien.$date2_form; ?>&submit=Actualiser" target="_self" title="Depuis la rentr&eacute;e " ><IMG SRC="../images/debut20.png" align="top" ></a><?php 
}; 
?> <input name='date1' type='text' style="text-align:center;" id='date1' value="<?php echo $date1_form;?>" size="9" > 
&nbsp;  au &nbsp; <input name='date2' type='text' style="text-align:center;" id='date2' value="<?php echo $date2_form;?>" size="9" > <?php 
if ( $date2_sql!=  date('Ymd') ){ 
	?> <a href="carnets_prof.php?<?php echo $classe_lien.'date1='.$date1_form.'&date2='.date('d/m/Y') ; ?>&submit=Actualiser" target="_self" title="Depuis la rentr&eacute;e " ><IMG SRC="../images/fin20.png" align="top" title="j-1"></a><?php 
};
 ?></td><td style="padding: 6px;  border:1px solid gray; border-left:0px solid;">
 &nbsp;pour <select name="classe" id="classe">
    <option value="">tous</option><?php 
do {  ?>
    <option value="<?php echo $row_RsClasse['nom_classe']?>"  <?php if ((isset($_GET['classe']))&&($_GET['classe']==$row_RsClasse['nom_classe'])){echo 'selected=" selected"';};?>><?php echo $row_RsClasse['nom_classe']?></option><?php 
} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)) ;
mysqli_free_result($RsClasse); 
?></select></td> <td class="fond" style="padding: 6px; border:0px solid ;" width="100">
<span style="float:right;font-size:10px;padding:0px 40px;"> </span> &nbsp; <input name="submit" type="submit" value="Actualiser"/>
 </td> <td class="fond"></td>
 <td class="fond" style="padding: 6px; border:0px solid ;"> 
 <span style="float:right;margin-right:0px;  background-color:#D8D8D8 ; padding:0px ; border:0px solid black ; font-size:11px; vertical-align: middle;"><A HREF="#" onClick="top.close()" title="Fermer cette page"><img SRC="../images/out25x50.png"  BORDER="0"></A></span></td>
 </tr>
 <tr> <td colspan="2"> </td><td colspan="2" ><span style="font-size:10px;padding:0px 0px Opx 0px ;"> <input  type="checkbox"  style="vertical-align: middle ;" name="nonSignes"  id="nonSignes" value="Y"  <?php if ( $nonSignes  =='Y'){echo ' checked';};?>> Masquer les &eacute;v&egrave;nements v&eacute;rifi&eacute;s </span></td></tr>
 </table> 
 </form>
 <table  border="1" align="left" cellpadding="0" cellspacing="0" 	style="border :1px ;margin:0px 20px 5px 20px ;" >

<tr>
<td class="fond" width="120" > </td>	
<td class="fond" width="14"></td>
<td class="fond" width="140"></td>
<td class="fond" width="120"></td>
<td class="fond" width="340"></td>
<td class="fond" width="20" ></td>		
</tr>
 <tr><td class="Style6" colspan="5" style="padding:2px 0px 4px 4px ;text-align:left;" > Retards Nv, oublis de carnet, incidents  notifi&eacute;s & remarques <?php echo $msgTitre?></td>
 <td colspan="2" class="fond"> &nbsp; </td> </tr>
<?php 
 // avec $trColor+1, on va utiliser les indices 0 et 2  !  ATTENTION : style pas clos  >> ajouter "!
  $classTr=array(' class="ligne_gris_clair"  ',                  '',
						' class="ligne_gris_fonce"  ');
$trColor= -1 ; 


//============ bilan incidents déclarés pour un prof=========================================
	// liste des evenCar à extaire 
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_evenCar = 	sprintf("
		SELECT eleve_ID,nom_ele,prenom_ele,classe_ele,ID,retard_Nv,pbCarnet,surcarnet, motif, details, date, heure, heure_saisie, annule, solde, signature
		FROM ele_absent,ele_liste
		WHERE prof_ID=%s   %s
		AND ID_ele=eleve_ID
		AND absent='N'
		AND ( retard_Nv='Y' or pbCarnet='Y' or  motif >0 or details<>'') 
		AND date <='%s'
		AND date >='%s'
		ORDER BY date,substring(heure_saisie,0,4)",
		$idprof, $sql_classeOuElv,$date2_sql, $date1_sql);
	
	$evenCar = mysqli_query( $conn_cahier_de_texte,$query_evenCar) or die(mysqli_error($conn_cahier_de_texte));
	$row_evenCar = mysqli_fetch_assoc($evenCar);
	$totalRows_evenCar = mysqli_num_rows($evenCar);
	$nbmodifiables=0;
		?>


<?php $est_modifiable="false";
	if ( $totalRows_evenCar > 0){	
		echo '<tr><td colspan="3" align="center">&nbsp;</td></tr>';
		$trColor=-1;
		do {    $indexmotif= intval($row_evenCar['motif']); // motif=0 par defaut
				// on liste les retard Nv et motifs y compris = 0 ; maais pas les absences et retard V
				$trColor=$trColor*(-1);
				$jourEven= substr($row_evenCar['date'],6,2).'/'.substr($row_evenCar['date'],4,2);
				if ($row_evenCar['heure'] < 1) {
					$plageH=substr( $row_evenCar['heure_saisie'],0,2).'h'.substr( $row_evenCar['heure_saisie'],2,2);
					} else {
					$plageH=$plages[ $row_evenCar['heure']];
				};
				$nom_j=substr($row_evenCar['heure_saisie'],4,3); if( $nom_j==''){$nom_j='   ';};// pour alignement vert.
				$creneau=$nom_j.' '.$jourEven.'-'.$plageH; 
					
				$est_modifiable=( ($row_evenCar['annule'] == "N" ) && ($row_evenCar['solde'] =="N" ));
					// borné ensuite par date à 7j
				$time_max_modif = mktime(0, 0, 0, substr($row_evenCar['date'],4,2) , substr($row_evenCar['date'],6,2) +7, substr($row_evenCar['date'],0,4) );
				if (date('Ymd') > date('Ymd',$time_max_modif)){ $est_modifiable=false;}; // pour modif et supprime
				if ($est_modifiable) {$nbmodifiables++ ;};
				?> <tr <?php echo $classTr[$trColor+1]?> >
				<td class="left" ><?php echo $creneau?></td>
				<td class="left" ><?php echo $row_evenCar['classe_ele']?></td>
				<td><?php echo $row_evenCar['nom_ele'].'&nbsp; '.$row_evenCar['prenom_ele'];?></td>
				
				<td style="background-color:<?php 
					if  (( $row_evenCar['retard_Nv']=='Y')&&($indexmotif==0)) { echo $clr[1] ; } // color des retards 
					elseif ( $row_evenCar['pbCarnet']=='Y') {  echo $clr[6] ; } // skyblue pour pas de carnet
					elseif ( $indexmotif==0 ) {  echo $clr[10] ; } //gris clair
					else   { echo $color[$indexmotif];}; ?>; border-bottom:1px solid #CCCCCC;">
				<?php 
				if ($indexmotif ==0){ if ( $row_evenCar['retard_Nv']=='Y') { echo 'Retard non valable';};
										if ( $row_evenCar['pbCarnet']=='Y'){ echo 'Pas de carnet';};
										if (strlen($row_evenCar['details'])>1){ echo 'N.B.';};
				} else {
					if ( $row_evenCar['pbCarnet']=='Y'){ echo 'Pb carnet et <br>';};
					if	( $indexmotif <=$IndexRetards ) { echo 'Rnv ';};
				};
				echo $motifs[$indexmotif];
				?></td><?php
				?><td class="right"> <?php 
				if ( $est_modifiable ) { 
					echo '<form method="POST" name="ModifD" id="ModifD" action="'.$debut_lien_relance.'date1='.$date1_form.'&date2='.$date2_form.'&submit=Actualiser" >'; 
				};
				?>&nbsp;<textarea class="area_in_ligne" name="modifDetails" id="modifDetails" <?php if (!$est_modifiable) { echo 'readonly';};?> style=" width:<?php if ((strlen($row_evenCar['details'])>1) || $est_modifiable) {$largeur= 294 ;} else { $largeur= 16 ;}; echo $largeur ; ?>px;  max-width:300px; height:12px ; max-height:100px; background-color:white ;font-size:11px;" cols="1" rows="1"><?php echo str_replace("#"," &#10;",$row_evenCar['details']);?></textarea><?php
				if (strlen($row_evenCar['details']) > 25 )	{ 
					echo '<img src="../images/more.png" style="vertical-align:top;" title="Agrandir la fen&ecirc;tre pour tout lire">';
				};
				if ($est_modifiable) { 
						echo '<input type="hidden" name="ID_even" value="'.$row_evenCar['ID'].'">';
						echo '<input  style="float:right" name="submit" type="image" src="../images/save16.png" title="Enregistrer les modifications">'; 
						echo ' </form>';	
				}; 
				
			?></td>
			<td class="fond"><span style="border:0px solid black ; font-size:11px; ">
			<a href="carnet_elv.php?elvid=<?php echo $row_evenCar['eleve_ID'].'&classe='.$classe.'&date1='.$date1_form.'&date2='.$date2_form; ?>&submit=Actualiser" target="_blank" title="carnet de l'&eacute;l&egrave;ve" ><IMG SRC="../images/carnet18y.png"></a></span></td><td class="fond"><?php 
			
			if  ( $row_evenCar['signature'] =="Y") {echo '<img src="../images/ok.jpg" title="V&eacute;rifi&eacute;" >';	}
			elseif (  $row_evenCar['pbCarnet']=='Y' &&  $row_evenCar['surcarnet']=='N') { // att sinon surcarnet ne contient pas Y mais la date...
						?><a href="carnet_elv.php?elvid=<?php echo $row_evenCar['eleve_ID'].'&classe='.$classe.'&date1='.$date1_form.'&date2='.$date2_form; ?>&submit=Actualiser" target="_blank" title="Incident &agrave; retranscrire" ><IMG SRC="../images/carnetedit_doigt.png"></a></span></td><td class="fond"> <?php }
			else { // checking signatures
						$Msg="V&eacute;rifier signature dans le carnet";
						$oeilcolor="bleu"; // par defaut
						if ( $row_evenCar['pbCarnet']=='Y')  { //avec surcarnet ="Y"
									$oeilcolor="orange"; }
						elseif ( $row_evenCar['retard_Nv']=='Y') { $oeilcolor="jaunegris";}
						elseif($row_evenCar['motif']> 1) {  $oeilcolor="orange";}
						else {$Msg="Information &agrave; suivre ";};
							
			?><form method="POST" name="signature" id="signature" action="<?php echo  $debut_lien_relance.'&date1='.$date1_form.'&date2='.$date2_form;?>&submit=Actualiser" ><input type="hidden" name="ID_even" value="<?php echo $row_evenCar['ID']?>"><input type="hidden" name="Signature" value="ok">	<input name="submit" type="image" src="../images/cocher_oeil<?php
			echo $oeilcolor.'40.png" height="20" title="'.$Msg.'">';?> </form> <?php  
			} ;
			?>	</td></tr><?php
			
		} while ($row_evenCar = mysqli_fetch_assoc($evenCar)); 
			
	} else {	// pas de pb a retrancrire
	?><tr> <td colspan="3" class="encadre"  >Pas d incidents &agrave; d&eacute:clar&eacute;s </td></tr><?php 
	};
	?><tr><td class="fond" height=40>&nbsp;</td></tr>;
	
<?php 	mysqli_free_result($evenCar); 


	
?>

<?php if ( $nbmodifiables > 0 ) { 
?><tr> <td  colspan="5" style=" background-color:yellow; color:black ; padding :3px 10px ;border:1px solid #000 ;font-weight: bold;" >Ajout ou modification des d&eacute;tails</td></tr>
<tr style="border:1px solid #9E9E9E;"><td colspan="5" style="padding :5px 10px;"><img src="../images/save16.png" align="top" > Cet ic&ocirc;ne rep&egrave;re les &eacute;v&egrave;nements  dont vous &ecirc;tes l'auteur, et datant de moins de 8 jours. <br>&nbsp; &nbsp; &nbsp; La rubrique "D&eacute;tails" de ces messages est modifiable ; pour <b>valider les modifications</b>, cliquez sur l'ic&ocirc;ne correspondant au message.</td></tr><?php }; ?><tr><td> &nbsp;</td></tr>
<tr style="margin-top:10px"> <td  colspan="5" style=" background-color:yellow; color:black ; padding :3px 10px ;border:1px solid #000 ;font-weight: bold;" >V&eacute;rification des carnets</td></tr>
<tr style="border:1px solid #9E9E9E;"><td colspan="5" style="padding :5px 10px;"><IMG SRC="../images/carnetedit_doigt.png" align="bottom"> Indique un incident li&eacute; &agrave un d&eacute;faut de carnet qui n'est pas encore report&eacute; dans le carnet. <br>&nbsp; &nbsp; &nbsp; &nbsp; Si vous effectuez cette retranscription, cliquez sur cet ic&ocirc;ne.</td></tr>

<tr><td colspan="5" ><img src="../images/oeil2020.png" height="20"> Ce logo pointe les &eacute;v&egrave;nements r&eacute;cents non v&eacute;rifi&eacute;s.(orange-> incident ou retard, bleu-> remarque).<br>&nbsp; &nbsp; &nbsp;  En cliquant sur la case accol&eacute;e &agrave cet ic&ocirc;ne,vous pouvez pointer les signatures v&eacute;rifi&eacute;es ; dans ce cas,<br>&nbsp; &nbsp; &nbsp;   le signalement disparait dans la feuille d'appel.(apr&egrave;s  actualisation)</td></tr>
</table>
<a href="
<?php 
if ($_SESSION['droits']==2 ){echo '../enseignant/enseignant.php';};
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'vie_scolaire.php';};
if ($_SESSION['droits']==4){echo '../direction/direction.php';};

?>
"><br>
<!--Retour au menu
<?php
if ($_SESSION['droits']==2 ){echo ' Enseignant';};
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo ' Vie scolaire';};
if ($_SESSION['droits']==4){echo ' Responsable Etablissement';};
?>   -->
</a> 
</div>
</body>
</html>
