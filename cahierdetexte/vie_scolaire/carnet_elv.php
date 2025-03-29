<?php 
// form1 saisie incidents profs &   form2 saisie sanction vie sco 
// date1 et date2 sont en "d/m/Y,,au format pret pour le cal datex_form    cf l150
//version sans separateur dans details

include "../authentification/authcheck.php" ;
include "./carnets_inc.php";
$droits = $_SESSION['droits']; 
// $droits = 3;//  pour  neutraliser   2> ens , 3>viesco, 4> chef
// acces 0 : pas de mire de saisie even car pas d'even saisi sur cette plage,  acces1 la mire de saisie sans les retard   


// recherche prof princ .pour info et elevation de droits
$pp=false ;
if (isset($_GET['classe']) && $_GET['classe']<>'') { 
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_pp = sprintf("SELECT DISTINCT cdt_prof.identite, cdt_prof.ID_prof
		FROM cdt_prof_principal,cdt_prof,cdt_groupe, cdt_classe 
		WHERE cdt_classe.nom_classe = '%s'
		AND cdt_prof_principal.pp_classe_ID=cdt_classe.ID_classe 
		AND cdt_prof_principal.pp_prof_ID=cdt_prof.ID_prof 
		ORDER BY cdt_prof.identite ASC",
		$_GET['classe']  )  ;
	$cherche_pp = mysqli_query($conn_cahier_de_texte,$query_pp) or die(mysqli_error($conn_cahier_de_texte));
	$un_pp = mysqli_fetch_assoc($cherche_pp);
	$nb_pp = mysqli_num_rows($cherche_pp);
if ($nb_pp > 0 && ($_SESSION['ID_prof']==$un_pp['ID_prof'])) { $pp=true ;} ; 
mysqli_free_result($cherche_pp); 
};
$saisir_evenProf=true;$saisir_evenAutre=true;
// messages si pas d'even à afficher 669
$msg0=' Pour consulter le carnet ou saisir un incident, &eacute;teignez le vid&eacute;oprojecteur, <br>
 puis cliquez sur l\'ic&ocirc;ne <img SRC="../images/carnetloupe.png" height="24" width="24"> ci-dessus &agrave droite ';
$msg_retour1= ' Pour saisir un incident, utilisez la feuille d\'appel : <A HREF="#" onClick="top.close()"  title="Fermer cette page"><img SRC="../images/out25x50.png"  BORDER="0" style="vertical-align:middle;"></A>';

if (isset($_GET['classe'])){$classe= $_GET['classe'] ;}else {$classe='';$_GET['classe']=""; }; // pour faire suivre 'classe'

//limitations d'affichage en venant de appel.php
if ((isset($_GET['mode']))&& $_GET['mode']=='0'){
		$saisir_evenProf=false;$saisir_evenAutre= false ;$msg_suite=$msg0;};
if ((isset($_GET['mode']))&& $_GET['mode']=='1'){
		$saisir_evenProf=false;$saisir_evenAutre= false ;$msg_suite=$msg_retour1;};
if ((isset($_GET['mode']))&& $_GET['mode']=='2'){$saisir_evenProf=true;$saisir_evenAutre= false ;};

// filtrage des details à afficher
if (isset($_GET['-abs'])&& ( $_GET['-abs']=="Y" )) {$cacheAbs='Y';} else { $cacheAbs='N';}; // Y/N
if (isset($_GET['-rval'])&& ( $_GET['-rval']=="Y" )) {$cacheRval='Y';} else { $cacheRval='N';} ;
if (isset($_GET['-ok'])&& ( $_GET['-ok']=="Y" )) {$cacheOk ='Y';} else { $cacheOk='N';};
if (isset($_GET['bilan'])&& ( $_GET['bilan']=="Y" )) {$bilan_seul=true;} else { $bilan_seul=false;};

// contrôle des différentes entrees formulaires pour les nouveaux even
$nb_evenDejaSaisi=0;$insertNew=false; // sera levé par les controles suivants :
$reporter='N'; // pour suivre les reports carnets par vie sco 
if ((isset($_POST['idxMotif'])) && ($_POST['idxMotif']) >0  )  { $idxMotif=$_POST['idxMotif'];$insertNew=true; } else {$idxMotif=0;};
if ((isset($_POST['pbcarnet'])) && ($_POST['pbcarnet'] =='Y'  ) ) { $pbcarnet="Y";$insertNew=true; } else {$pbcarnet="N";};
if ((isset($_POST['details'])) && (strlen($_POST['details'])>0 )){ $details=$_POST['details'];$insertNew=true; } else {$details='';};
// pour les rdv et sanctions details sert à stocker  une suite d'elts NON separés par #
if(( isset($_POST['idxSanc']))&& ($_POST['idxSanc'] > 0 ))	{ 
	$idxMotif = $_POST['idxSanc']; $insertNew=true; $reporter='Y';
	if(( isset($_POST['idxRdv_Ret']))&& ($_POST['idxRdv_Ret'] > 0 )){
				$pour='/'.$motifs[$_POST['idxRdv_Ret']];}else {$pour="";};
				$details='le '.$_POST['date_retenue'].' à '.$_POST['heure_retenue'].'h '.$_POST['min_retenue'].' '.$pour.$details ; 
};
// pour sanction et rdv date et heure dans  plages heure-debut et heure fin en 5car !	
if (!isset($_POST['elvid']) || strval($_POST['elvid']) <1 ) {$insertNew=false;};



if( $insertNew && ($droits==2)) {  // avant insertion nouvel even,on verifie d'abord qu'il n'y a pas doublon meme motif pour le même jour !!!
					// sauf vie sco pour permettre reinjection incidents, ou  rdv ou punitions
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_evenCar = 	sprintf("
		SELECT ID	FROM ele_absent
		WHERE prof_ID=%s  
		AND eleve_ID=%s
		AND motif=%s
		AND date =%s",
		$_SESSION['ID_prof'], $_POST['elvid'],$idxMotif,date('Ymd'));
	
	$evenDejaSaisi = mysqli_query($conn_cahier_de_texte,$query_evenCar) or die(mysqli_error($conn_cahier_de_texte));
	$deja = mysqli_fetch_assoc($evenDejaSaisi);
	$nb_evenDejaSaisi = mysqli_num_rows($evenDejaSaisi);


if ($nb_evenDejaSaisi >0)  { $insertNew=false; $msgDoublon='&Eacute;v&egrave;nement non enregistr&eacute; : il y a d&eacute;j&agrave un &eacute;v&egrave;nement identique le même jour !';} else {$msgDoublon='';};
mysqli_free_result($evenDejaSaisi); };

if( $insertNew) {  // insertion nouvel even 
$_GET['date2']=date('d/m/Y'); // on modifie date2 pour le voir !!!!!!!
// $surcarnet="N"; $retard_V="N"; $pbCarnet="N"; $retard_Nv="N"; $absent="N"; $statutVs='N'; $annule='N';
	$table_jours = array('Dim','Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam');
	$index_jour= date('w', mktime(0, 0, 0,date('m'), date('d'), date('Y')) )  ;
	$nom_jour=$table_jours[$index_jour];	
	// $localtime=localtime();$heure=$localtime[tm_hour].date('i');
	
 // ajouter possibilité modif ! en testant id even 
 $insertNewSQL = sprintf("INSERT INTO `ele_absent` (classe,heure_saisie,date,eleve_ID,prof_ID,    absent,retard_V,retard_Nv,    motif,pbCarnet, details,vie_sco_statut,  annule) VALUES (%s,%s,%s,%u,%u,      'N','N','N',  %u ,%s,%s,%s,    'N')", 
	GetSQLValueString($_GET['classe'], "text"),
	GetSQLValueString(date('Hi').$nom_jour, "text"),
	GetSQLValueString(date('Ymd'), "text"),
	GetSQLValueString($_POST['elvid'], "int"),
	GetSQLValueString($_SESSION['ID_prof'], "int"),
					
	GetSQLValueString($idxMotif, "int"),
	GetSQLValueString($pbcarnet,"text"),
	GetSQLValueString($details,"text"),
	GetSQLValueString($reporter,"text")
					);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result = mysqli_query($conn_cahier_de_texte,$insertNewSQL) or die(mysqli_error($conn_cahier_de_texte));
				
	$_POST['motif']=0;	$_POST['details']='';$_POST['idxSanc']=0; //pour pas faire des doublons en relance de page !	
		}
else 
{
	if ( isset($_POST['modifDetails']) &&  isset($_POST['ID_even']) && ($_POST['ID_even']> 0  ))
	{//modif  restreinte à details 
	$updateSQL = sprintf("UPDATE ele_absent SET details = %s  
		WHERE ele_absent.ID = %u ", 
		GetSQLValueString($_POST['modifDetails'], "text"), 
		GetSQLValueString($_POST['ID_even'], "int")  );

	  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	  $Result = mysqli_query($conn_cahier_de_texte,$updateSQL) or die(mysqli_error($conn_cahier_de_texte));
	 // $_POST['solde']='';	$_POST['details']=''; //pour pas faire des doublons en relance de page !	
	};
$_POST['modifDetails']='';
};// fin insertion even




//dedouble fiche   si incident+pbcarnet
if ((isset($_POST['IDdedouble'])) && ($_POST['IDdedouble'] > 0) )
{  $insertNewSQL = sprintf("INSERT INTO `ele_absent`  (classe,classe_ID, date,heure, eleve_ID, prof_ID, pbCarnet )
	SELECT classe,classe_ID, date,heure, eleve_ID, prof_ID, pbCarnet  FROM `ele_absent` 
	WHERE  ele_absent.ID = %u   ",
	GetSQLValueString($_POST['IDdedouble'], "int") );
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	  $Result = mysqli_query($conn_cahier_de_texte,$insertNewSQL, $conn_cahier_de_texte) ;

	// cet even n' pas une abs, on va ici utiliser le champ vie_sco pour reperer les incidents avec pb carnet qui sont dedoubles l:485 !
	  $statut= 'Y';
	$updateSQL = sprintf("UPDATE ele_absent SET vie_sco_statut = %s 
		WHERE ele_absent.ID = %u    ",
		GetSQLValueString($statut, "text") ,
		GetSQLValueString($_POST['IDdedouble'], "int") );
		
	  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	  $Result = mysqli_query($conn_cahier_de_texte,$updateSQL) or die(mysqli_error($conn_cahier_de_texte));;  
	  
	$_POST['IDdedouble']= 0 ; //pour pas faire des doublons en relance de page !
};

// report si pbcarnet 
if ((isset($_POST['IDreport'])) && ($_POST['IDreport'] > 0) )
{  	$updateSQL = sprintf("UPDATE ele_absent SET surcarnet = %s 
		WHERE ele_absent.ID = %u    ",
	   
	   GetSQLValueString( date('d/m').' # '.$_SESSION['identite'].' # '.date("H").'h'.date("i") , "text"),
	   	GetSQLValueString($_POST['IDreport'], "int") );	
		
	  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	  $Result = mysqli_query($conn_cahier_de_texte,$updateSQL) or die(mysqli_error($conn_cahier_de_texte));;  
	  
	$_POST['IDreport']= 0 ; //pour pas faire de recriture  en relance de page !
	  
	  
};

// even soldé
if ((isset($_POST['IDsolde'])) && ($_POST['IDsolde'] > 0) )
{  	$updateSQL = sprintf("UPDATE ele_absent SET solde = %s 
		WHERE ele_absent.ID = %u    ",
	   GetSQLValueString( date('d/m').' # '.$_SESSION['identite'].' # '.date("H").'h'.date("i") , "text"),
		GetSQLValueString($_POST['IDsolde'], "int") );	
		
	  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	  $Result = mysqli_query($conn_cahier_de_texte,$updateSQL) or die(mysqli_error($conn_cahier_de_texte));;  
	  
	$_POST['IDsolde']= 0 ; //pour pas faire de recriture  en relance de page !
	  
	  
};


//classe un even
if ((isset($_POST['IDannule'])) && ($_POST['IDannule'] > 0) )
{ 	$annule= 'Y';
	$updateSQL2 = sprintf("UPDATE ele_absent SET annule = %s 
		WHERE ele_absent.ID = %u    ",
	 GetSQLValueString( date('d/m').' # '.$_SESSION['identite'].'# '.date("H").'h'.date("i") , "text"),
		GetSQLValueString($_POST['IDannule'], "int") );
		
	  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	  $Result = mysqli_query($conn_cahier_de_texte,$updateSQL2) or die(mysqli_error($conn_cahier_de_texte));;  
	  
	$_POST['IDannule']=''; //pour pas faire des doublons en relance de page !
	  
	  
};


//date rentree
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_debut = 	sprintf(" SELECT param_val FROM cdt_params WHERE param_nom ='date_debut_annee' " );
	
	$debut = mysqli_query($conn_cahier_de_texte,$query_debut) or die(mysqli_error($conn_cahier_de_texte));
	$row_debut = mysqli_fetch_assoc($debut);
	$rentree_sql= $row_debut['param_val']; 
	mysqli_free_result($debut); 
	$rentree_form=substr($rentree_sql,6,2).'/'.substr($rentree_sql,4,2).'/'.substr($rentree_sql,0,4);


if (isset($_GET['-abs'])){$cacheAbs= $_GET['-abs'] ;}; // Y/N
if (isset($_GET['-rval'])){$cacheRval= $_GET['-rval'] ;}; 
if (isset($_GET['-ok'])){$cacheOk= $_GET['-ok'] ;};


// date1 et 2 type 12/09/2014   date_pour sql 20140912  date pour calen comme date1 et date2

if (!isset($_GET['date1']) )//  || strlen($_GET['date1'])<8 )
	{ $decal=3;// bilan par défaut sur 3 mois  avec mini =rentree !
		$date_prec = mktime(0, 0, 0, date("m") - $decal , date("d"), date("Y"));
		$date1_sql= date("Ymd",$date_prec);
		if ($date1_sql < $rentree_sql) {$date1_sql = $rentree_sql;$date1_form=$rentree_form;
		} else {
		$date1_form=date("d/m/Y",$date_prec); };
		$_GET['date1']=$date1_form;
		}  
	else {	$date1_form= $_GET['date1'];
			$date1_sql=substr($_GET['date1'],6,4).substr($_GET['date1'],3,2).substr($_GET['date1'],0,2);
		};

if (!isset($_GET['date2']))// || strlen($_GET['date2'])<8)
		{$date2_sql=date('Ymd');
		$date2_form=date('d/m/Y');
		$_GET['date2']=$date2_form;}
	else {	$date2_sql=substr($_GET['date2'],6,4).substr($_GET['date2'],3,2).substr($_GET['date2'],0,2);
			$date2_form= $_GET['date2'];
			};
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT distinct classe FROM ele_absent ORDER BY classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte,$query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
 
 // gestion des modes non renseignés
 if (isset($_GET['mode'])&& ($_GET['mode']!="")) { $mode='&mode='.$_GET['mode'];} else {$mode="";};
if ($saisir_evenAutre){$mode="";};// pour passer outre le mode 0 en multisaisies
if (isset($_GET['classe'])&&($_GET['classe']!='')){ $lien_classe='&classe='.$_GET['classe'];} else {$lien_classe='';};
$debut_lien_relance='carnet_elv.php?elvid='.$_GET['elvid'].$lien_classe;
$dates_relance='&date1='.$_GET['date1'].'&date2='.$_GET['date2'].'&submit=actualiser' ;
$lien_relance=$debut_lien_relance.$dates_relance ;// sans les " " pour utiliser dans les POST!







?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<!-- <link media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet> -->
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />

<style type="text/css">
td { padding-left:4px ; border:0px solid ;text-indent: 0px;}	// ne marche pas avec padding:2px  !!!
tr { border :0px solid;}

.ligne_gris_fonce {	font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt;color: #000066;
					border-bottom : 1px solid #CCCCCC; border-left:1px solid #9E9E9E ; border-right:1px solid #9E9E9E ;	border-collapse:collapse; background-color: #DFDFDF;                              // #9E9E9E gris souris	// ne marche pas avec padding:2px  !!!
					padding:1px; text-align: left;	vertical-align: middle;}	

.ligne_gris_clair {	font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;color: #000066;// 12px=9pt
					border-bottom : 1px solid #D8D8D8;	 border-left:1px solid #9E9E9E ; border-right:1px solid#9E9E9E; border-collapse:collapse; background-color: #F2F2F2; //#F2F2F2
					padding: 1px; text-align: left;	vertical-align: middle;}			
.ligne_bilan {	font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9pt;color: black ;
					border : 1px solid #9E9E9E ;	border-collapse:collapse; background-color: #ECCEF5;
					padding:1px; text-align: left;	vertical-align: middle;}	
					
.area_in_ligne {	border: 1px solid #CCCCCC;	padding :2px ;// style="border:none; background-color:yellow;font-size:10px "
				 background-color:yellow;font-size:11px	;} 
a img {border: none;}
.Style6 {text-indent: 0px;padding-left:6px;}


</style>

<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>

<script>
function verif_form3() {
  var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
  
   if(document.form3.date.value == "") 
  { alert("Il faut preciser la date ");
   return false; } 
   
  if(document.form3.heure.value == "") 
  { alert("Il faut preciser l'heure ");
   return false; } 
  
  if(document.form3.min.value == "") 
  { alert("Il faut preciser l'heure ");
   return false; } 
   
   if(document.form3.idxSanc.valuevalue < "1")
   { alert("Il faut preciser le motif ");
   return false; }  
   
   if(document.form3.idxRdv_Ret.valuevalue < "1" && document.form3.Detaols== "")
   { alert("Il faut donner la raison ");
   return false; }  
}
 </script>
</head>


<body style="background-color: #DEDEDE;">
<div id="container" align="center" style=" min-width:600px;background-color: #FAF6EF; min-height:700px;border: none;padding-top:0px;"> 

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

$(function() {
		$.datepicker.setDefaults($.datepicker.regional['fr']);
		var dates = $( "#date3" ).datepicker({
				defaultDate: "+1w",
				changeMonth: true,
				numberOfMonths: 1,
				firstDay:1,
				onSelect: function( selectedDate ) {
					var option = this.id == "date3" ? "minDate" : "maxDate",
					instance = $( this ).data( "datepicker" ),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
					dates.not( this ).datepicker( "option", option, date );
				}
                });
});

$(function() {
        	$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
        	$.datepicker.setDefaults($.datepicker.regional['fr']);
        	$('#dateretenue').datepicker({firstDay:1});
});
$( function() {
  $('select').click( function() {
    $(this).css('background', '#FAF79B')
	 $(this).css('color', '#000')
  } );
} );
$( function() {
  $('textarea').click( function() {
    $(this).css('background', '#FAF79B')
	 $(this).css('color', '#000')
  } );
} );

</script> 
 <?php // table 9col 706px def en 374?>
 <table class="lire_bordure" border='0' align="center" cellpadding="0" cellspacing="0" style="border : none;margin-top:0px;"	 >

 <form name="frm" method="GET" action="<?php echo $lien_relance;?>">
 <tr class="ligne_gris_fonce" style="border-top : 1px solid #9E9E9E ;border-bottom: 0px solid;">
	
	
<?php if ( $saisir_evenAutre)  {//  bilan evens sur la période  ?> 
   <td colspan="6"   align="left" style="padding:8px; padding-left:30px;" > 
   <b>Bilans pour la p&eacute;riode du &nbsp; <?php  // bouton raz date1
   if ( $date1_sql != $rentree_sql ){  
			?> <a href="<?php echo $debut_lien_relance.'&date1='.$rentree_form.'&date2='.$date2_form;?>&submit=Actualiser" target="_self" title="Depuis la rentr&eacute;e " ><IMG SRC="../images/debut20.png" align="top" ></a>  <?php }; ?>
			<input name='date1' type='text' id='date1' value="<?php echo $date1_form;?>" size="10"/> &nbsp;  au &nbsp; <input name='date2' type='text' id='date2' value="<?php echo $date2_form;?>" size="10"/>

	<?php if ( $date2_sql!=  date('Ymd') )	{
		// boutionn raz date2?>
		<a href="<?php echo $debut_lien_relance.'&date1='.$date1_form?>&submit=Actualiser" target="_self" title="Jusqu'&agrave; ce jour" > 	<IMG SRC="../images/fin20.png" align="top" title="aujourd'hui"></a>  <?php }; ?></td>
	
	<td></td><td rowspan="2"> <span style="float:right;padding:2px ;padding-right:20px ;border:0px solid black ; font-size:11px; ">
	<a href="carnets_bilan.php?<?php echo $lien_classe.'&submit=actualiser'?>"  target="_self" title="Bilan pour la classe"  >Bilan Classe<IMG SRC="../images/carnets40f.png" width="59" height="59"></a></span><br> </td>
	
	<td rowspan="2" colspan="1" valign="top">
	
	  <div align="right"><A HREF="#" onClick="top.close()" title="Fermer cette page"><img SRC="../images/cancel.png" width="40" height="18"  BORDER="0" align="top" ></A></div>
	</td> </tr>

 
	<tr class="ligne_gris_fonce" style="border-top:0px solid; border-bottom: 1px solid #9E9E9E;"><td colspan="7" style="padding:2px 10px 3px 40px; vertical-align: middle ;font-size:11px;" >Sans les absences<input  type="checkbox"  style="vertical-align: middle ;" name="-abs"  id="-abs" value="Y"  <?php if ( $cacheAbs=='Y'){echo ' checked';};?>> &nbsp; &nbsp; Sans les retards val.<input  type="checkbox"  style="vertical-align: middle ;" name="-rval"  id="-rval" value="Y"  <?php if ( $cacheAbs =='Y'){echo ' checked';};?>>  &nbsp; &nbsp; Sans les sold&eacute;s ou annul&eacute;s<input  type="checkbox" style="vertical-align: middle ;" name="-ok"  id="-ok" value="Y"  <?php if ( $cacheOk=='Y'){echo ' checked';};?>>
   <input type="hidden" name="elvid" value="<?php echo $_GET['elvid']; ?>">  
   <input type="hidden" name="mode" value="<?php echo $mode; ?>">

  <span style="float:center;font-size:10px;padding:0px 0px;">&nbsp; &nbsp;&nbsp;&nbsp;<input name="submit" type="submit" value="Actualiser"/></span> </td> 
	</tr> 


 
 <?php } else {
		?> 
	<td colspan="9"  align="left" style="padding:6px 20px;" > <b> Incidents d&eacute;j&agrave; signal&eacute;s le &nbsp; </b> <input type="text" readonly  value=" &nbsp; <?php echo $_GET['jour'].'&nbsp; '.$date1_form ;?> &nbsp; " style="align:center;width:140px;"> <span align="right" style="padding-left:50px;">
	
	<span style="float:right;background-color:#D8D8D8 ;padding:0px  ;border:0px solid black ; font-size:11px;vertical-align: middle ; "><A HREF="#" onClick="top.close()" title="Fermer cette page"><img SRC="../images/cancel.png" width="40" height="18"  BORDER="0"></A></span>
	
	<span style="float:right; font-size:12px;margin-right:80px;"><a href="<?php echo $debut_lien_relance.'&date2='.$_GET['date2'].'&submit=Actualiser"';?> target="_self" title="Bilan pour l'&eacute;l&egrave;ve " ><IMG SRC="../images/loupe.jpg" width="50" height="50"></a> </span>


	<span style="float:right;font-size:11px;padding:0px;margin-right:40px;"> Pour consulter tout le carnet ,<br>
	<b>&eacute;teignez le vid&eacute;projecteur</b>... <br>avant de cliquer ci-joint -&raquo; </span>
	
	</span> 
	
	</td></tr>
<?php } ; ?>

</form> 
<?php 									// fin  bilan evens sur la période 
 ?> 
<?php
if ((!isset($_GET['elvid'])) || ($_GET['elvid']=='')) { // msg erreur pas d'id el
echo '<p>&nbsp;</p><p  class="erreur" align="center"> Erreur sur le nom d\'&eacute;l&egrave;ve......</p>';
};
 
if ((isset($_GET['elvid'])) && ($_GET['elvid']!='') ) { // extraction elv

 mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Elv = 	sprintf("
		SELECT nom_ele, prenom_ele, classe_ele 	FROM ele_liste
		WHERE 	ID_ele ='%u'",$_GET['elvid'] );
	
	$Elv = mysqli_query($conn_cahier_de_texte,$query_Elv) or die(mysqli_error($conn_cahier_de_texte));
	$row_Elv = mysqli_fetch_assoc($Elv);
	$totalRows_Elv = mysqli_num_rows($Elv);
	};?>
<?php // table 9col 706px ?>

	<tr>
		<td width=120px >&nbsp;</td>
		<td width=36px >&nbsp;</td>
		<td width=24px >&nbsp;</td>
		<td width=16px >&nbsp;</td>
		<td width=130px >&nbsp;</td>
		<td width=366px>&nbsp;</td>
		<td width=120px>&nbsp;</td>
		<td width=22px >&nbsp;</td>
		<td width=22px >&nbsp;</td>
	</tr>
		
		
		<tr> <td class="Style6"  colspan="4"  > <div align="left" style=" margin:2px;margin-left:0px;background-color:aquamarine; color:black ;padding :3px;" > <?php echo $row_Elv['nom_ele'].' &nbsp;'.$row_Elv['prenom_ele'].'&nbsp;'.$row_Elv['classe_ele'].'&nbsp; '; ?> </div></td> 
		
		<td class="Style6"> &Eacute;v&egrave;nement</td>
		<td class="Style6" > D&eacute;tails</td>
		<td class="Style6" > signal&eacute; par</td>
		<td class="Style6"  >Pb</td>
		<td class="Style6" align="center" ><img src="../images/balance17v.png" title='Incidents sold&eacute;s &#10;suite &agrave retenue'></td>
		
		</tr> 
		<tr><td colspan="9" style="border-bottom : 1px solid #CCCCCC;">&nbsp;</td></tr>


<?php 	mysqli_free_result($Elv); // liste des  abs, retarts et incidents à extaire=============pb si + from ele_liste
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_even = 	sprintf("
		SELECT eleve_ID,nom_ele,prenom_ele,classe,ID, date,heure, heure_saisie,absent,retard_V,pbCarnet,retard_Nv ,motif,details,vie_sco_statut,surcarnet,solde,annule,prof_ID ,identite
		FROM ele_absent,ele_liste ,cdt_prof
		WHERE 	ele_absent.eleve_ID ='%u' 
		AND   ID_ele=eleve_ID 
		AND cdt_prof.ID_prof = ele_absent.prof_ID
		AND ele_absent.date <='%s'
		AND ele_absent.date >='%s'	
		ORDER BY date,heure_saisie",
		$_GET['elvid'],$date2_sql,$date1_sql);
		$even = mysqli_query($conn_cahier_de_texte,$query_even) or die(mysqli_error($conn_cahier_de_texte));
	$row_even = mysqli_fetch_assoc($even);
	$totalRows_even = mysqli_num_rows($even);
	$nbmodifiables=0;		
	


 if ( $totalRows_even > 0 )	{ //  il y a des evens, analyse des even extraits : comptage, affichage
				
		$trColor= -1 ;  // avec $trColor+1, on va utiliser les indices 0 et 2  !  att : style pas clos !
		$classTr=array(' class="ligne_gris_clair"  ',    '', ' class="ligne_gris_fonce" ');
		$val_jour=0;$nbJabs=0; $nbHabs=0; $nbhabsj=0;$val_jLiprec=0;	$jpbcarnetprec="";
		$nbRetV=0;
		$nbRetNv=0;$pbcarnet=0; $nbTra=0;$nbAtt=0;$nbCon=0;
		$nbRetNvNS=0;$pbcarnetNS=0; $nbTraNS=0;$nbAttNS=0;$nbConNS=0; // les non soldés
		$nbSan=0;$nbDemiJabs=0;
		$nom_j=substr($row_even['heure_saisie'],4,3); if( $nom_j==''){$nom_j='   ';};
			$jourEven= substr($row_even['date'],6,2).'/'.substr($row_even['date'],4,2);
		$jabs_prec= $nom_j.' '.$jourEven;	// pour le premier
		$jLiprec='';
		do { // analyse de chaque even : comptage, affichage  date ex 20140903
				$NS= ($row_even['solde']=='N'); // pour compter les non soldés
				$est_modifiable=(( $row_even['prof_ID']==$_SESSION['ID_prof']) ||( $droits > 3 )  )&&  ($row_even['annule'] == "N" ) && ($row_even['solde'] =="N" );// borné ensuite par date à 7j
				$time_max_modif = mktime(0, 0, 0, substr($row_even['date'],4,2) , substr($row_even['date'],6,2) +7, substr($row_even['date'],0,4) );
				if (date('Ymd') > date('Ymd',$time_max_modif)){ $est_modifiable=false;}; // pour modif et supprime
		if ($est_modifiable) {$nbmodifiables++ ;};
				
			$jourEven= substr($row_even['date'],6,2).'/'.substr($row_even['date'],4,2);
			$val_jour=strval(substr($row_even['date'],6,2).substr($row_even['date'],4,2));
			
			if ($row_even['heure'] <1) {
			$plageH=substr( $row_even['heure_saisie'],0,2).'h'.substr( $row_even['heure_saisie'],2,2);
			}	else {
			$plageH=$plages[ $row_even['heure']];};
			$nom_j=substr($row_even['heure_saisie'],4,3); if( $nom_j==''){$nom_j='   ';};// pour alignement vert.
			$creneau=$nom_j.' '.$jourEven.'-'.$plageH; 
			$nom_jourEven=$nom_j.' '.$jourEven;
			
			if (($jabs_prec !="N")&&($row_even['absent'] =="Y")) { //gestion suite d'even  absences
			//cumul pour n'afficher qu'une seule ligne par jour 
				if  ( $nom_jourEven== $jabs_prec ) {//absence  même jour  que  tour prec
							$nbhabsj= $nbhabsj +1 ; 
							if ($nbhabsj==2){$nbDemiJabs=$nbDemiJabs+1;};// 1/2jour d'abs decompté si + d'1h d'abs	
							if ($nbhabsj==5){$nbDemiJabs=$nbDemiJabs+1;};// +1/2jour d'abs decompté si +de 4h d'abs	
							// on n'affiche pas la ligne ! 
							// on verra au tour suivant sauf pour le der! qu'il faut memoriser
							$jabs_prec=$nom_jourEven; // pour le tour suivant et la sortie
							
					} else {  // absence nouveau jour, on solde les abs du  jour prec et on les affiche
						if ($cacheAbs!="Y") {
							$trColor=$trColor*(-1) ;
							echo '<tr '.$classTr[$trColor+1]. '>';
							echo '<td height="18" >'.$jabs_prec.'</td>';
							echo '<td '; echo 'bgcolor="gold">&nbsp;Abs&nbsp;'; echo $nbhabsj.'h </td><td colspan="8"> </td></tr>';	
							}; 
							$nbHabs=$nbHabs+$nbhabsj;
							$nbJabs=$nbJabs+0;// jour ajouté si + d'1h d'abs dans la journée
							$nbhabsj=1;$jabs_prec=$nom_jourEven; // nouveau jour d'abs
					};
					// fin even d'absences qui se suivent
			};
			if (($jabs_prec !="N")&&($row_even['absent'] =="N")) { // pas une absence,mais abs précédente à solder	
					if (($cacheAbs!="Y") &&($nbhabsj > 0)){
						 $trColor=$trColor*(-1) ;
						echo '<tr '.$classTr[$trColor+1]. '>';
						echo '<td height="18" >'.$creneau.'</td><td>';
						echo '<td '; echo 'bgcolor="gold">&nbsp;Abs&nbsp;'; echo $nbhabsj.'h </td><td colspan="8"> </td></tr>';
						};		 
					$jabs_prec="N";$nbHabs=$nbHabs+$nbhabsj; $nbhabsj=0;
				};
			// fin edition abs j-1   $jabs_prec=$val_jour; // pour le tour suivant et la sortie
			
						
if ($row_even['absent'] !="Y")	{ // listage de tous les even sauf absences
								// listage absences géré au tour suivant pour gestion cumul en 436)	
				
				if ( $row_even['annule'] != "Y" ) 	{ // //decomptes comptabilisés seulement si pas annulé 466
						$indexmotif=$row_even['motif'];
					if ( ($indexmotif !=0 ) && ( $row_even['motif']!="0") ) 
					{// pour assurer la compatibilité   motif code ou chaine
						if  (($indexmotif > 6 )&&  $indexmotif <12 ) { $nbTra++; if ($NS) {$nbTraNS++; }; };
						if  (($indexmotif > 11 )&&  $indexmotif <16 ) { $nbAtt++; if ($NS) {$nbAttNS++; }; };
						if  (($indexmotif > 15 )&&  $indexmotif <19 ) { $nbCon++; if ($NS) {$nbConNS++; }; }; 
						if  (($indexmotif > 21 ) &&  $indexmotif <28 ) { $nbSan++;}; 
					};
					if ($row_even['retard_V'] =="Y") {$nbRetV++;};
					if ($row_even['retard_Nv'] =="Y")  {$nbRetNv++ ;if ($NS) {$nbRetNvNS++; };};
					
					// analyse pbcarnet  : cumul pour n'afficher qu'une seule ligne par jour	
					//si c'est le même jour on affichera le pb carnet  pour validation !,  mais sans le decompter !
					if (($row_even['pbCarnet'] !="N") &&($jourEven!= $jpbcarnetprec)) {
						$pbcarnet++;if ($NS) {$pbcarnetNS++; } ;
						$jpbcarnetprec=$jourEven;		//	$lejour			
						}; 
					};
				// fin// comptabilisé seulement si pas annulé	
				?>
				
	<?php 	if ((($row_even['retard_V'] =="Y")&& ($cacheRval=="Y"))  || (($row_even['solde'] !="N" ) && ($cacheOk =="Y") )|| $bilan_seul ) {// ligne non affichée 
					echo '';
		 } else { // edition ligne affichée >>620
				 
					if ($jourEven!= $jLiprec) { $trColor=$trColor*(-1);};
					$jLiprec=$jourEven;
					$indexmotif =intval($row_even['motif']); 
					$style_ligne= $classTr[$trColor+1];// par defaut
						
					if  ( $indexmotif > $nbmotifs_profs ) {
					$style_ligne=$style_ligne.' style="background-color:#A9F5F2;" ';
					} ;
					
					if ( ($row_even['annule'] != 'N')&&(strlen($row_even['annule'])>2 )) {
					$style_ligne=$style_ligne.' style="text-decoration:line-through;" ';};
					?>
				<?php	//fin // ligne non affichée ## ligne affichée 
				?>
					
					<tr<?php echo $style_ligne?>>
					<td colspan="2" style="height:18px;"><?php echo $creneau?></td><?php
					$cell_Abs_Ret='<td ';
					if ($row_even['retard_V'] =="Y") {$cell_Abs_Ret.= 'bgcolor=#40FF00;">&nbsp;(rv)&nbsp;';}
					elseif ($row_even['retard_Nv'] =="Y") {$cell_Abs_Ret.='bgcolor="yellow">&nbsp;Rnv&nbsp;';}
					else {$cell_Abs_Ret.='>';};
					echo $cell_Abs_Ret.'</td>';
					?>	
					<?php	if ($row_even['pbCarnet'] =="Y") {// affichage pbcarnet simple ou double
							echo '<td bgcolor="#FF6340">&nbsp;C';
							if ( $row_even['vie_sco_statut'] == "Y"){ echo '+';} else {echo'&nbsp;';};
							echo '</td>';
							}  else { 
							echo '<td> </td>'; 
							};
							?> 
					<?php // affichage details  
?>					<td width="60" >&nbsp; <?php if ( $indexmotif !=0 ){ echo $motifs[$indexmotif] ;};?></td>
					<td style="padding-top:1px;">&nbsp;<?php
					
					if ( $est_modifiable ) { echo '<form method="POST" name="ModifD" id="ModifD" action="'.$debut_lien_relance.'&date1='.$date1_form.'&date2='.$date2_form.'&submit=Actualiser" >'; };
					?><textarea class="area_in_ligne" name="modifDetails" id="modifDetails" <?php 
					
					if (!$est_modifiable) { echo 'readonly';};?> style=" width:<?php if (strlen($row_even['details'])>1 &&($row_even['annule']=='N')) {$largeur= 338 ;} else { $largeur= 16 ;}; echo $largeur ; ?>px;  max-width:338px; height:12px ; max-height:100px; background-color:white ;" cols="1" rows="1"><?php // eliminer les balises # version initiale balisée
					$chain= str_replace("#","",$row_even['details']);$chain= str_replace("  "," ",$chain);$chain=str_replace("pour :","/",$chain);
					echo $chain;	?></textarea><?php
					
					if (strlen($row_even['details']) > 64 )	{ 
							echo '<img src="../images/more.png" style="vertical-align:top;" title="Agrandir la fen&ecirc;tre pour tout lire">';
							};
					if ($est_modifiable) { ?><input type="hidden" name="ID_even" value="<?php echo $row_even['ID'];?>"> <input name="submit" type="image" src="../images/save16.png" title="Enregistrer les modifications"> </form>
							<?php 
					}; 
				?> </td>	<td width="180">&nbsp; <?php echo $row_even['identite']?></td>
					<td align="center" ><?php 
				
					// pbcarnet (seul ou avec incident OU convoc ou retenue ) 
					$pbCarnet=($row_even['pbCarnet'] !="N" ||$row_even['motif']> $nbmotifs_profs ) && ($row_even['annule'] == "N" );
					$ilyaIncident=($row_even['retard_Nv'] =="Y") || (( $row_even['motif'] > $IndexHorsRetards) && ($row_even['motif'] < $indexMotifsMax) );
					
					$IncidentAvecPbCarnetaReporter=($pbCarnet && $ilyaIncident && ($row_even['vie_sco_statut']=='N'));
					// s'il y a pbcarnet + (retard ou incident) on va creer dabord une fiche suppl. de 		pbcarnet et vie_sco_statut de l'even carnet passe à 1 quand le dedoublement est réalisé
							
					if (($pbCarnet )&& ($row_even['surcarnet'] != 'N')) {// on signale la transcription
							if ($ilyaIncident) { $logo='carnetpb18.png';} else {$logo='carnetok18.png';};
							$infos=explode  ('#',$row_even['surcarnet']);
							if (count($infos) < 2){$infos[0]='';$infos[1]='';$infos[2]='';};
							echo '<img src="../images/carnetpb18.png" title="retranscrit par '.$infos[1].'&#10; le '.$infos[0].' &agrave; '.$infos[2].'">';
					};
					if ( $IncidentAvecPbCarnetaReporter ) 	{	//on cree la fiche incident
							echo '<img src="../images/carnetedit_doigt.png" title="Incident et Oubli de carnet à retranscrire" >';
							if  (( $droits > 2  || $pp ) || ($row_even['prof_ID']==$_SESSION['ID_prof']))  {
							?>	<form method="POST" name="formD" id="formD" action="<?php echo $debut_lien_relance.'&date1='.$date1_form.'&date2='.$date2_form;?>&submit=Actualiser" > <input type="hidden" name="IDdedouble" value="<?php echo $row_even['ID']?>"> <input name="submit" type="image" src="../images/cocher.png" title="Dédoubler les 2 incidents "> </form> <?php 
									};
					}; 
					if ($pbCarnet&&($row_even['surcarnet'] == 'N')&& !$IncidentAvecPbCarnetaReporter)	{	
							// pb carnet à reporter (seul  ou avec incident deja reporté)
							
							if  (( $droits > 2  || $pp ) || ($row_even['prof_ID']==$_SESSION['ID_prof'])) 	{
								?><form method="POST" name="formC" id="formC" action="<?php echo  $debut_lien_relance.'&date1='.$date1_form.'&date2='.$date2_form;?>&submit=Actualiser" >
								<input type="hidden" name="IDreport" value="<?php echo $row_even['ID']?>">	<input name="submit" type="image" src="../images/carnetedit_cocher.png" title="Incident NON not&eacute dans le carnet &#10; cocher une fois l'incident retrancrit "> </form> <?php 
							} else { 
								echo '<img src="../images/carnetalert18.png" title="&#192 retranscrire" >';
							};
					} ;
							
				
						// fin outils de gestion pb carnets	
						?>	</td><td align="center" ><?php
						
						// incidents annulables ,soldables, ou non     // &&($row_even['motif'] < ($indexMotifsMax+$nbSanctions+1) )
						
						$est_annule= ( strlen($row_even['annule']) > 2 ) ;
						$est_soldable=( ( ($row_even['retard_Nv'] =="Y") ||( $row_even['motif'] >= $IndexHorsRetards)  || ($row_even['pbCarnet'] =="Y")) && ($est_annule==false) &&( $droits > 2 || $pp )&& ($row_even['solde']=="N") && !$IncidentAvecPbCarnetaReporter && (!$pbCarnet || ( $pbcarnet && $row_even['surcarnet'] != 'N' ))) ;   										
						$est_solde= (strlen($row_even['solde'] ) > 1);
						$infos[0]='';$infos[1]='';$infos[2]='' ;
						
						if ($est_annule) { 
							echo ' <img src="../images/carnetSup18ok.png" title=';
							$infos=explode ('#',$row_even['annule']);
							if (count($infos) < 2){$infos[0]='';$infos[1]='';$infos[2]='';};
							echo '"annul&eacute; par'.$infos[1].'&#10; le '.$infos[0].' &agrave; '.$infos[2].'">';
							     } ;
						if ($est_modifiable) { // et donc ici supprimable
								 ?><form method="POST" name="formA" id="formA" action="<?php echo $lien_relance?>">
								 <input type="hidden" name="IDannule" value="<?php echo $row_even['ID']?>" >
								 <input name="submit" type="image" src="../images/CarnetSup18.png" title="Cet évènement peut être annulé &#10 Ne pas oublier de le biffer aussi &#10 dans le carnet de liaison !"> </form> <?php
								};
						if ($est_solde ) { // 
									echo' <img src="../images/balance17v.png" title=';
									$infos=explode ('#',$row_even['solde']);
									if (count($infos) <2){$infos[0]='';$infos[1]='';$infos[2]='';};
									echo '"sold&eacute; par'.$infos[1].'&#10; le '.$infos[0].' &agrave; '.$infos[2].'">';
									};
						if ( $est_soldable)	{ // 
									?><form method="POST" name="formS" id="formS" action="<?php echo $lien_relance?>">
									<input type="hidden" name="IDsolde" id="IDsolde" value="<?php echo $row_even['ID']?>" >
									<input name="submit" type="image" src="../images/cocher.png" title="Cet évènement peut être soldé &#10 suite à retenue.&#10&#10&Agrave; solder dès que la retenue est posée !&#10 La retenue, elle, ne sera soldée &#10 qu'une fois effectuée. "> </form> <?php
									};
						if (!$est_soldable && !$est_modifiable && !$est_solde && !$est_annule && $row_even['retard_V']!='Y' && $row_even['absent']!='Y'){
									 // echo '-';
								?><img 	src="../images/cocher.png" title="non soldé "><?php  
								}; 
								// fin soldé # soldable
							
						echo '</td></tr>';
						};			
					// fin lignes affichées 472
							
				} ; // 446 ?>
			<?php	// fin listage evens  sauf absent ->> géré au tour suivant 
			
			$idprec=$row_even['eleve_ID']; //pour le lien au tour suivant !
		} while ($row_even = mysqli_fetch_assoc($even)); 	?>
	<?php 	 // fin analyse des even extraits : comptage, affichage 413
		 

	if ($nbhabsj > 0) {// la der ligne est une abs, il faut l'afficher !
			
			//if ($cacheAbs!="Y") {
				$trColor=$trColor*(-1) ;
				echo '<tr '.$classTr[$trColor+1]. '>';
				echo '<td height="18" >'.$jabs_prec.'</td>';
				echo '<td '; echo 'bgcolor="gold">&nbsp;Abs&nbsp;'; echo $nbhabsj.'h </td><td colspan="8"> </td></tr>';	
			//};
		}; 	
		
								// fin	affichage derniere ligne si abs



if($saisir_evenAutre) {  // bilan incidents  (sauf depuis appel, si pas de 1er incident saisi)
?>
				<tr  style="background-color:#ECCEF5;font-size:10pt; border:1px;" >
				<td colspan="4" align="center" style="border :1px solid gray; "><?php if($nbHabs >0) { echo '&nbsp; absences : <a href="#" title="nombre d\'heures calculé sur les appels réalisés&#10 le nombre d\'heures r&eacute;ellement perdu peut &ecirc;tre sup&eacute;rieur..">'.$nbHabs.'h </a>sur '.$nbDemiJabs.' &frac12;j &nbsp;';} else {echo '&nbsp;Aucune absence&nbsp;';};
				$bilanRetards='';
				if ($nbRetV>0) { $bilanRetards='+&nbsp;'.$nbRetV.' (rv)&nbsp;';};
				if ($nbRetNv>0) { $bilanRetards.='+&nbsp;'.$nbRetNv.' Rnv -';}; 
					echo $bilanRetards; 
				//echo str_replace("--","-",$bilanRetards); // pour eviter les --?>
				</td>
				<td colspan="4" align="center" style="border :1px solid gray;border-right:0px solid;" ><?php 
					$bilan_incid='';
					if ($pbcarnet> 0 ) { $bilan_incid.='-&nbsp;'.$pbcarnet.' pb carnet&nbsp;-';};
					if ($nbTra> 0 ) { $bilan_incid.='-&nbsp;'.$nbTra.' pb travail&nbsp;-';};
					if ($nbAtt> 0 ) { $bilan_incid.='-&nbsp;'.$nbAtt.' pb sign. & doc&nbsp;-';};
					if ($nbCon> 0 ) {$bilan_incid.='-&nbsp;'.$nbCon.' pb comportement & doc&nbsp;-';};
					if ($nbSan> 0 ) {$bilan_incid.='-&nbsp;'.$nbSan.' retenues & exclusions &nbsp;-';};
					echo str_replace("--","-",$bilan_incid); // pour eviter les --
					
					$nbNS=0;
					$bilan_incidNS='<br><b> Non sold&eacute;s : ';
					if ($pbcarnetNS> 0 ) { $bilan_incidNS.='-&nbsp;'.$pbcarnetNS.' pb carnet&nbsp;-'; $nbNS=$nbNS+1;};
					if ($nbTraNS> 0 ) { $bilan_incidNS.='-&nbsp;'.$nbTraNS.' pb travail&nbsp;-';$nbNS=$nbNS+1 ;};
					if ($nbAttNS> 0 ) { $bilan_incidNS.='-&nbsp;'.$nbAttNS.' pb sign. & doc&nbsp;-';$nbNS=$nbNS+1; };
					if ($nbConNS> 0 ) {$bilan_incidNS.='-&nbsp;'.$nbConNS.' pb comportement & doc&nbsp;-';$nbNS=$nbNS+1; };
					if ( $nbNS>0) { echo str_replace("--","-",$bilan_incidNS.'</b>'); };// pour eviter les --
					
					
					?></td><td align="right" style="border:1px solid gray; border-left:0px solid #ECCEF5;"> <span style="padding:0px; border:0px solid black ; font-size:12px; ">
			<!--	<a href="carnet_elv.php?elvid=<?php echo $idprec.'&date1='.$date1_form.'&date2='.$date2_form; ?>&submit=Actualiser" target="_blank" title="carnet de l'&eacute;l&egrave;ve" ><IMG SRC="../images/carnet18y.png"></a></span> -->
				</td></tr>
<?php 	} ;	 // fin affichage bilan 
 if ($nb_evenDejaSaisi> 0 ) { echo '<tr><td colspan="6" style="padding-bottom:8px; font-size:12px; font-weight:bold;border:2px solid red"><br>&nbsp;'.$msgDoublon.'</td></tr>';};
 
  // fin // il y a des evens, analyse des even extraits : comptage, affichage if l:413
	mysqli_free_result($even); 

	
} else { // pas d'éven à lister


if ($saisir_evenProf ==false){// messages si rien à afficher
		echo '<tr><td colspan="8" ><br>'.$msg_suite.'</td></tr>';
		} else { // aucun even ce jour
		echo '<tr><td colspan="8" ><i> Pas d\'&eacute;v&egrave;nements ';
		if ($saisir_evenAutre) 	{ 
			echo 'sur cette p&eacute;riode ou pas de classe s&eacute;lectionn&eacute;e...';
			} else {
			 echo 'notifiés ce jour  <br>'. $msg_suite;
			 };
		echo '</td></tr>';
	 }; 
	 };?>
<?php // fin des messages si rien à afficher 

if ( $nbmodifiables > 0 ) { 
?><tr><td style="height:30px;">&nbsp;  </td></tr> <tr> <td  colspan="5" style=" background-color:yellow; color:black ; padding :3px 10px ;border:1px solid #000 ;font-weight: bold;" >Ajouter ou modifier des d&eacute;tails</td></tr>
<tr style="border:1px solid #9E9E9E;"><td colspan="9" style="padding :5px 10px;">Les &eacute;v&egrave;nements  dont vous &ecirc;tes l'auteur, et datant de moins de 8 jours, sont rep&eacute;r&eacute;s par &nbsp;<img src="../images/save16.png"><br> La rubrique "D&eacute;tails" de ces messages est modifiable; pour <b>valider les modifications</b>, cliquez sur la &nbsp;<img src="../images/save16.png"> correspondant au message.<?php };

// DEBUT  Masques de saisies profs, vie sco, rdv et retenue en fonction des drots ET des contextes [mode]
?>
<tr  style="border: 0px solid;height:50px;"> <td>&nbsp; </td></tr><?php
 
if ( (($droits == 2 ) && ($saisir_evenProf)) || $droits==4 ) {//  saisie  d'un nouvel évènement  PROF  
			// ou [ modif détails ou  annulation ]========( mais pas de retards ! )
			// $createur_heure_part_ID=$idpc[$j];
			$datetoday=date('d/m'); 
		?><form method="POST" name="formNew" id="formNew" action="<?php echo $lien_relance;
								if ($mode=='mini') { echo '&mode=mini';};?>">
			
			<tr> <td  colspan="5" style=" background-color:yellow; color:black ; padding :3px 8px ;border:1px solid #000 ;font-weight: bold;" >
			Ajouter un &eacute;v&egrave;nement (<a href="#" title="Absences et retards sont impérativement saisis dans la feuille d'appel !">sauf absences et retards</a> ) </td></tr>
			
			<tr style="border:1px solid #9E9E9E;">
			<td colspan="4" style="padding :5px 10px;"> <?php echo $datetoday; ?> &nbsp;
			<select  name="idxMotif" id="idxMotif" >
			<option value="0" style="background-color:white;"> motifs </option>	
			<?php	//  l:820 : liste des motifs definis en debut sufs retards 
			for ($i=$IndexHorsRetards; $i<=$nbmotifs_profs; $i++) 
					{ echo '<option value="'.$i.'" style="background-color:'.$color[$i].' ;">'.$motifs[$i].' </option> ' ;
					 }; ?>
			</select><span style="float:right;"><input  type="checkbox" name="pbcarnet"  id="pbcarnet" value="Y" > Carnet non pr&eacute;sent&eacute;</span></td>
			<td colspan="2" style="padding : 5px 10px;" >
			<textarea style=" width:250px;  height:14px; max-height:28px;  background-color::white; " cols="1" rows="1"  class="area_in_ligne"  id="details"   name="details" ></textarea>
			</td >
			<td colspan="4" align="right" style="padding : 3px 10px;" >
				<input type="hidden" name="elvid"  id="elvid" value="<?php echo $_GET['elvid'];?>" >

			<span style="float:right; text-align:right;"><input name="submit" style="background-color: #FAF79B" type="submit"  value="Enregistrer"></span></td></tr>	</form> 
<tr  style="border: 0px solid;height:50px;"> <td>&nbsp; </td></tr>
<?php }; // fin saisie nouvel even   PROF 
?>		


	
<?php	if ( $saisir_evenAutre && $droits>2) { // saisie  even VIE SCO
		?><form  method="POST" name="form2" id="form2"  action="<?php echo $lien_relance?>" >
			<tr> <td  colspan="5" style=" background-color:yellow; color:black ; padding :3px 10px ;border:1px solid #000;font-weight: bold;" >	Déclarer un incident de vie sco.</td></tr>
			<tr style="border:1px solid #9E9E9E;">
			<td colspan="4" style="padding : 5px 10px;"><?php echo date('m/d') ?> &nbsp;	
			 <select  name="motif" id="motif" >
			 <option value="0" style="background-color:white;"> motifs  </option>
			<?php	//  l:820 : liste des motifs_ret definis en debut / attention value 0 pour pas d'enregistrement !
				for ($i=0; $i<=$nbmotifs_profs - $indexMotifsViesco  ; $i++) 
					{ $j= $i + $indexMotifsViesco ; // index à patir de 31 et plus
					echo '<option value="'.$j.'" style="background-color:'.$color[$j].' ">'.$motifs[$j].' </option> ' ;
					 }; ?>
			</select></td>
			<td colspan="4" style="padding : 5px 10px;" >
			<textarea style=" width:250px;  height:14px; max-height:42px; max-width:250px; background-color::white;" cols="1" rows="1"  class="area_in_ligne"   id="details"   name="details" ></textarea>
			&nbsp; <input  type="checkbox" name="pbcarnet"  id="pbcarnet" value="Y" > Carnet non pr&eacute;sent&eacute;  	
			
				<input type="hidden" name="elvid"  id="elvid" value="<?php echo $_GET['elvid'];?>" >
	
				<?php // pour conserver 'classe' dans les liens vers les autres pages ?>
				<span style="float:right; text-align:right;"><input name="submit" style="background-color: #FAF79B" type="submit"  value="Enregistrer"></span> </td></tr></form>
<tr> <td style="height:50px">&nbsp; </td></tr><?php }; ?>
<?php  // fin saisie Even vie sco 
?> 



<?php if ( (($droits == 2)&& ($saisir_evenAutre)) || ($droits > 2  || $pp)) {	// saisie RDV et retenues 
	 ?>	<form method="POST" name="form3" id="form3"  onsubmit="return verif_form3()"  action="<?php echo $lien_relance?>">
			<tr> <td>&nbsp; </td></tr>
			<tr> <td  colspan="5" style=" background-color:yellow; color:black ; padding :3px 10px ;border:1px solid #000;font-weight: bold;" >
			Planifier un Rdv ou une sanction</td></tr>
			<tr style="border:1px solid  #9E9E9E; ;border-bottom:0px solid ;">
			<td colspan="9" style="border:0px solid #000;padding :5px 10px"> &nbsp;  <?php echo date('m/d'); ?> &nbsp;	
			<select  name="idxSanc" id="idxSanc" >
			<option value="0" style="background-color:white;"> rdv ou sanction  </option>
			<?php	//  l:820 : liste des motifs_ret definis en debut / attention value 0 pour pas d'enregistrement !
				if ( $droits <4){$nbItems=$nbRetenues;} else{$nbItems=$nbRetenues+$nbSanctions;};
				for ($i=0; $i<=$nbItems; $i++) 
					{ $j= $i + $nbmotifs_profs + 1 ; // index à patir de 31 et plus
					echo '<option value="'.$j.'" style="background-color:'.$color[$j].' ;" >'.$motifs[$j].' </option> ' ;
					 }; ?>
			</select> &nbsp; 
			<select  name="idxRdv_Ret" id="idxRdv_Ret" >
			<option value="" style="background-color:white;"> concernant </option>
			<?php	//  l:820 : liste des motifs_ret definis en debut / attention value 0 pour pas d'enregistrement !
				
				for ( $i=0; $i<$nbMotifs; $i++) 
					{ $j= $i + $nbmotifs_profs + $nbRetenues+ $nbSanctions ; // index à patir de $j ! 
					  			echo '<option value="'.$j.'" style="background-color:'.$color[$j].' ;">'.$motifs[$j].' </option> ' ;
					 };  ?>
			</select> 	 le : &nbsp; 
			<input name='date_retenue' type='text' id='date3' value="<?php echo $date2_form;?>" style="width:72px; text-align:center;"/> &nbsp; &agrave; &nbsp; 
			
			<select name="heure_retenue" id="heure"  style="width:50px;" >
				<option value="" selected="selected" > ? h</option>
                <?php for ($i=0; $i<$nb_heures; $i=$i+1) { ?>
			  <option value="<?php echo $choix_heures[$i].'">'.$choix_heures[$i] ; ?> h </option> 
				<?php } ?>
				</select>

               <select name="min_retenue" id="min" style="width:66px;">
                  <option value="" selected="selected"> ? min</option>
                  <?php for ($i=0; $i<$nb_min; $i=$i+1) { ?>
			  <option value="<?php echo $choix_min[$i].'">'.$choix_min[$i] ; ?> min</option> 
				<?php } ?>
                </select> &nbsp; <input  type="checkbox" name="pbcarnet"  id="pbcarnet" value="Y" style="position:relative; top: 1px;" > Noté dans le carnet 	
					
			</td></tr>
			
			<tr style="border:1px solid #9E9E9E ; border-top:0px solid ; "> <td colspan="2"></td>
			<td colspan="7"><div style="float:left;padding:5px;padding-left:15px;"><textarea style=" width:250px;  height:14px; max-height:42px; max-width:250px;  background-color:white;"  cols="1" rows="1"  class="area_in_ligne"   id="details"   name="details" ></textarea></div>
			&nbsp;	<div style="float:right; padding-right:20px; ">
			<?php echo $_SESSION['identite']; ?>		
				<input type="hidden" name="elvid"  id="elvid" value="<?php echo $_GET['elvid'];?>" >
				<span style="float:right; text-align:right;padding-right:6px;padding-left:6px; "><input name="submit" style="background-color: #FAF79B" type="submit"  value="Enregistrer"></span> </div></td></tr>
				<tr><td colspan="2">&nbsp;</td></tr>
				 </form>
<?php }; ?>
<?php 					 // fin // saisie RDV et retenues 
?> 
</table><div align="right" style ="margin:4px 25px ;"><i>
<?php if ($nb_pp >0) { echo  'prof. principal : '.$un_pp['identite'];}; ?> </i></div><?php
/*
 !--Retour au menu
<p><a href="
<?php 
if ($_SESSION['droits']==2 ){echo '../enseignant/enseignant.php';};
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'vie_scolaire.php';};
if ($_SESSION['droits']==4){echo '../direction/direction.php';};
echo '<br>'
if ($_SESSION['droits']==2 ){echo ' Enseignant';};
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo ' Vie scolaire';};
if ($_SESSION['droits']==4){echo ' Responsable Etablissement';};
*/ ?>
</div>
</body>
</html>