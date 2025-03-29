<?php 
// pb n'affiche pas le premier even de chq elv !!!!!!!

include "../authentification/authcheck.php" ;
$droits=$_SESSION['droits'];
if (($droits <>2)&&  ($droits<>3)  &&  ($droits<>4)  &&(  $droits<>7)){ header("Location: ../index.php");};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
require_once('./carnets_inc.php');

if (isset($_GET['classe'])){$classe= $_GET['classe'] ;}else {$classe=''; }; // pour faire suivre 'classe'
if (isset($_GET['sansEven'])){$sansEven= $_GET['sansEven'] ;}else { $sansEven='N';}; // Y/N
if (isset($_GET['-abs'])){$cacheAbs= $_GET['-abs'] ;}else { $cacheAbs='N';}; // Y/N
if (isset($_GET['-rval'])){$cacheRval= $_GET['-rval'];} else { $cacheRval='N';} ;
if (isset($_GET['-ok'])){$cacheOk= $_GET['-ok'];} else { $cacheOk='N';};
if (!isset($_GET['classe'])) { $_GET['classe']='';}; // si param non passé
$plages=array("##","M1","M2","M3","M4","S1","S2","S3");

//date rentree
 mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_debut = 	sprintf(" SELECT param_val FROM cdt_params WHERE param_nom ='date_debut_annee' " );
	
	$debut = mysqli_query( $conn_cahier_de_texte,$query_debut) or die(mysqli_error($conn_cahier_de_texte));
	$row_debut = mysqli_fetch_assoc($debut);
	$date_rentree_sql= $row_debut['param_val']; 
	mysqli_free_result($debut); 

if (!isset($_GET['date1']))
     { // bilan par défaut depuis la rentrée
	$date1_sql=$date_rentree_sql;
	//$date1_form=date('d/m/Y',strtotime( $date1_sql)); 
	$date1_form=$date_rentree_sql;
		// pour bilan sur 3 mois 	$date_3m= mktime(0, 0, 0, date("m")-3 , date("d") , date("Y"));	if ( $date_3m < //$date1_sql) {$date_3m=$date1_sql;}; 
		//$date_3m_form=date('d/m/Y',$date_3m);
	}	
 else {
		$date1_sql=substr($_GET['date1'],6,4).substr($_GET['date1'],3,2).substr($_GET['date1'],0,2);
		$date1_form= $_GET['date1'];$jourtoday= jour_semaine($_GET['date1']);};

if (!isset($_GET['date2']))
		{$date2_sql=date('Ymd');$date2_form=date('d/m/Y');}
	else {	$date2_sql=substr($_GET['date2'],6,4).substr($_GET['date2'],3,2).substr($_GET['date2'],0,2);
			$date2_form= $_GET['date2'];$jourtoday= jour_semaine($_GET['date2']);	};
if (!isset($_GET['classe'])) { $cl='- choisir...';} else { $cl=' pour les '.$_GET['classe'];};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);


// liste des classes pour select choix de classes
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if ( $droits < 3 ) { // only les  classes DU prof
$query_RsClasse = sprintf("SELECT DISTINCT nom_classe,ID_classe 
	FROM cdt_classe,cdt_emploi_du_temps 
	WHERE cdt_classe.ID_classe=cdt_emploi_du_temps.classe_ID 
	AND prof_ID=%u 
	ORDER BY nom_classe ASC",
	$_SESSION['ID_prof']);
} else { // toutes les classes
$query_RsClasse = "SELECT  nom_classe FROM cdt_classe  ORDER BY nom_classe ASC";
};
$ClassesConc = mysqli_query( $conn_cahier_de_texte,$query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_Classes= mysqli_fetch_assoc($ClassesConc);
$totalRows_Classes = mysqli_num_rows($ClassesConc);



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Bilan Vie Scolaire - <?php echo $cl ; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<!-- <link media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet> -->
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />

<style type="text/css">

td { padding-left:4px ; }


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


</style>

<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>

</head>

<body style="background-color: #DEDEDE;">
<div id="container" style="padding-top:0px;background-color: #FAF6EF; min-height:700px;border: none;"> 

<form name="frm" method="GET" action="carnets_bilan.php">
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


<table width="100%" align="center" cellpadding="0" cellspacing="0" style="border :1px solid ;margin-top:2px;"	 >
 <tr class="ligne_gris_fonce" style="border-bottom : 0px solid;" >
 <td colspan="6" align="left" style="margin-top:0x; " >  

 
 <br/><b>Bilans pour la p&eacute;riode du &nbsp; 
 <?php if ( $date1_sql != $date_rentree_sql )  
{ ?> 

<a href="carnets_bilan.php?classe=<?php echo $_GET['classe']; ?>&date2=<?php echo $date2_form; ?>&submit=Actualiser" target="_self" title="Depuis la rentr&eacute;e " >
<IMG SRC="../images/debut20.png" align="top" ></a>  <?php }; ?>

<input name='date1' type='text' id='date1' value="<?php echo $date1_form;?>" size="10"/> 
 au &nbsp; <input name='date2' type='text' id='date2' value="<?php echo $date2_form;?>" size="10"/>

<?php if ( $date2_sql!=  date('Ymd') )
{ ?> <a href="carnets_bilan.php?classe=<? echo $_GET['classe']; ?>&date1=<? echo $date1_form; ?>&submit=Actualiser" target="_self" title="Depuis la rentr&eacute;e " >
<IMG SRC="../images/fin20.png" align="top" title="aujourd'hui"></a>  <?php }; ?>

 </b>&nbsp;pour les &nbsp;   <select name="classe" id="classe">
    <option value="-1">classe</option>
    <?php  do { ?>
    <option value="<?php echo $row_Classes['nom_classe']?>"  <?php if ((isset($_GET['classe']))&&($_GET['classe']==$row_Classes['nom_classe'])){echo 'selected=" selected"';};?>><?php echo $row_Classes['nom_classe']?></option>
    <?php     } while ( $row_Classes = mysqli_fetch_assoc($ClassesConc) );
mysqli_free_result($ClassesConc); 






?>
</select></td>
 <td colspan="1" >
 <span style="margin-right:0px;float:right;background-color:#D8D8D8 ;padding:3px 0px ;border:0px solid black ; font-size:11px; ">
 <A HREF="#" onClick="top.close()" title="Fermer cette page"></A></span></td>
 <td rowspan="2" colspan="2">  <span style="margin-right:0px;float:right;background-color:#D8D8D8 ;padding:0px 0px ;border:0px solid black ; font-size:11px; ">
 <A HREF="#" onClick="top.close()" title="Fermer cette page"><img SRC="../images/cancel.png" width="40" height="16"  BORDER="0"></A></span><br><span style="float:left;margin-right:60px;padding:2px 6px ;border:0px solid black ; font-size:11px; ">
<a href="carnets_viesco.php?classe=<?php echo $_GET['classe']; ?>&submit=Actualiser"  target=_blank ><IMG SRC="../images/radar40.png" title="Bilan Vie scolaire" ></a></span></td> </tr>

	<tr class="ligne_gris_fonce" style="border-top:0px solid; border-bottom: 1px solid #9E9E9E;">
	<td colspan="9" style="padding:2px 10px 3px 40px; vertical-align: middle ;font-size:11px;" >
	Sans les absences
	<input  type="checkbox"  style="vertical-align: middle ;" name="-abs"  id="-abs" value="Y"  <?php if ( $cacheAbs=='Y'){echo ' checked';};?>> &nbsp; &nbsp; Sans les retards val.
	<input  type="checkbox"  style="vertical-align: middle ;" name="-rval"  id="-rval" value="Y"  <?php if ( $cacheAbs =='Y'){echo ' checked';};?>>  &nbsp; &nbsp; Sans les sold&eacute;s ou annul&eacute;s
	<input  type="checkbox" style="vertical-align: middle ;" name="-ok"  id="-ok" value="Y"  <?php if ( $cacheOk=='Y'){echo ' checked';};?>>
 &nbsp;&nbsp;Absences uniquement

  <input  type="checkbox"  style="vertical-align: middle ;" name="sansEven"  id="sansEven" value="Y"  <?php if ( $sansEven =='Y'){echo ' checked';};?>> 
  <span style="float:center;font-size:10px;padding:0px 40px;"> 
  &nbsp;
  <input name="submit" type="submit" value="Actualiser"/>&nbsp;  
  </span> </form>
 </td></tr>
<?php 
 

if ((isset($_GET['classe']))&&($_GET['classe']!='-1')){   
	
	// liste des  abs, retarts et incidents à extaire
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_even = 	sprintf("
		SELECT eleve_ID,nom_ele,prenom_ele,classe,date,heure, heure_saisie,absent,retard_V,pbCarnet,retard_Nv ,motif,details,vie_sco_statut,surcarnet,solde,annule,prof_ID ,identite
		FROM ele_absent,ele_liste ,cdt_prof
		WHERE 	ID_ele=eleve_ID 
			and Id_prof= prof_ID
			AND 	classe='%s'
			AND ele_absent.date <='%s'
			AND ele_absent.date >='%s'
		ORDER BY nom_ele,date,heure ",
		$_GET['classe'],$date2_sql,$date1_sql);
	

	$even = mysqli_query( $conn_cahier_de_texte,$query_even) or die(mysqli_error($conn_cahier_de_texte));
	$row_even = mysqli_fetch_assoc($even);
	$totalRows_even = mysqli_num_rows($even);
	
	if ($totalRows_even>0){
		?>
		
		<tr>
		<td class="Style6"><div align="left" style="width:90px;">Nom / Date </div></td>
		<td class="Style6"><div  style="width:10px;" align="left"></div></td>
		<td class="Style6"><div align="center" style="width:20px;" >Abs</div></td>
		<td class="Style6"><div align="center" style="width:20px;" > C</div></td>
		<td class="Style6"><div align="left" style="width:130px;" >Incident</div></td>
		<td class="Style6"><div align="left" style="width:380px;" > D&eacute;tails</div></td>
		<td class="Style6"><div align="center" style="width:80px;"> </div></td>
		<td class="Style6"><div align="center" style="width:40px;"><img src="../images/carnetok18.png" title="Retranscrit par la Vie Scolaire" ></td>
		<td class="Style6"><div align="center" style=" width:40px;"><img src="../images/balance17v.png" title="A donn&eacute; lieu &agrave retenue" ></td>
		</tr>
		<?php
		$trColor= -1 ;  // avec $trColor+1, on va utiliser les indices 0 et 2  !  att : style pas clos !
		$classTr=array(' class="ligne_gris_clair"  ',    '', ' class="ligne_gris_fonce" ');
		$nomprec=""; $jabsprec="";$jpbcarnetprec=""; $jour="";$nbDemiJabs=0; $nbHabs=0; $nbhabsj=0;$jLiprec=""; 
		
		$nom_j=substr($row_even['heure_saisie'],4,3); if( $nom_j==''){$nom_j='   ';};
		$cejour= substr($row_even['date'],6,2).'/'.substr($row_even['date'],4,2);
		$jabs_prec= $nom_j.' '.$cejour;	// pour le premier
		$jLiprec='';
		
		
		do { 
		
		$cejour= substr($row_even['date'],6,2).'/'.substr($row_even['date'],4,2);
		$val_jour=strval(substr($row_even['date'],6,2).substr($row_even['date'],4,2));
		if ($row_even['heure'] <1) {$plageH=substr( $row_even['heure_saisie'],0,2).'h'.substr( $row_even['heure_saisie'],2,2);}
				else { $plageH=$plages[ $row_even['heure']];};
		$nom_j=substr($row_even['heure_saisie'],4,3); if( $nom_j==''){$nom_j='   ';};// pour alignement vert.
		 $creneau=$nom_j.' '.$cejour.'-'.$plageH; 
		$nom_cejour=$nom_j.' '.$cejour;
		 
		 
		 
							
		if( $row_even['nom_ele']!=$nomprec) // on va changer d' eleve 
				{// affichage  absence du tour prec +  bilan des décomptes d'incidents de lélève
			if( $nomprec !="") { //pas de bilan en debut !
				if( ($jabs_prec !="N")&&($nbhabsj>0) ) { // abs précédente à solder	
					if ($cacheAbs!="Y") {
						 $trColor=$trColor*(-1) ;
						echo '<tr '.$classTr[$trColor+1]. '>';
						echo '<td height="18" >'.$creneau.'</td><td>';
						echo '<td '; echo 'bgcolor="gold">&nbsp;Abs&nbsp;'; echo $nbhabsj.'h </td><td colspan="6"> </td></tr>';
						};		 
					$jabs_prec="N";$nbHabs=$nbHabs+$nbhabsj; 
				};// fin edition derniere absence
				
				?><tr class="ligne_bilan">
				<td colspan="3" align="center" style="border :1px solid gray; "><?php if($nbHabs >0) { echo '&nbsp; absences : <a href="#" title="nombre d\'heures calculé sur les appels réalisés&#10 le nombre d\'heures r&eacute;ellement perdu peut &ecirc;tre sup&eacute;rieur..">'.$nbHabs.'h </a>sur '.$nbDemiJabs.' &frac12;j &nbsp;&nbsp;';} else {echo '&nbsp; aucune absence &nbsp;';};
				$bilanRetards='';
				if ($nbRetNv>0) { $bilanRetards.='+&nbsp;'.$nbRetNv.'Rnv ';	}; 
				if ($nbRetV>0) { $bilanRetards='+&nbsp;'.$nbRetV.' (rv)&nbsp;';};
				echo $bilanRetards; 
				//echo str_replace("--","-",$bilanRetards); // pour eviter les --?>
				</td>
				<td colspan="5" align="center"style="border :1px solid gray;border-right:0px solid;" ><?php 
					$bilan_incid='';
					if ($pbcarnet> 0 ) { $bilan_incid.='-&nbsp;'.$pbcarnet.' pb carnet&nbsp;-';};
					if ($nbTra> 0 ) { $bilan_incid.='-&nbsp;'.$nbTra.' pb travail&nbsp;-';};
					if ($nbAtt> 0 ) { $bilan_incid.='-&nbsp;'.$nbAtt.' pb sign. & doc&nbsp;-';};
					if ($nbCon> 0 ) {$bilan_incid.='-&nbsp;'.$nbCon.' pb comportement & doc&nbsp;-';};
					if ($nbSan> 0 ) {$bilan_incid.='-&nbsp;'.$nbSan.' retenues & exclusions &nbsp;-';};
					echo str_replace("--","-",$bilan_incid); // pour eviter les --
					?></td><td align="right" style="border:1px solid gray; border-left:0px solid #ECCEF5;"> <span style="padding:0px; border:0px solid black ; font-size:12px; ">
				<a href="carnet_elv.php?elvid=<?php echo $idprec.'&classe='.$classe.'&date1='.$date1_form.'&date2='.$date2_form; ?>&submit=Actualiser" target="_blank" title="carnet de l'&eacute;l&egrave;ve" ><IMG SRC="../images/carnet18y.png"></a></span>
				</td></tr>
			<?php if ( $sansEven !='Y'){ echo '	<tr><td style="padding-top: 15px;">&nbsp;</td></tr>';}; 
		};// fin décomptes  n-1
				
				
				// nouvel eleve y compris le 1er
				$nomprec= $row_even['nom_ele'];$nbDemiJabs=0; $nbHabs=0;$nbhabsj=0; $nbRetV=0;$nbRetNv=0;$pbcarnet=0; $nbTra=0;$nbAtt=0;$nbCon=0;$nbSan=0;
				$jpbcarnetprec="";
				$jabs_prec= $nom_j.' '.$cejour;	// pour le premier even analysé si c'est une abs ?>
				
				<tr><td>&nbsp;</td> </tr>  
				<tr  style="border:none; font-size:10pt;padding-top: 3px" > 
				<td colspan="3" bgcolor="aquamarine" style="border :1px solid blue ; border-bottom :1px solid #9E9E9E ;"> &nbsp;
				<?php echo $nomprec.'&nbsp; '.$row_even['prenom_ele']?> <span style="margin-right:10px;float:right;"><?php echo $row_even['classe']?></span></td> <td colspan="8" style="border-bottom :1px solid #9E9E9E ;">&nbsp; </td></tr> <?php
		}; // fin traitement fin d'élève, puis nouvel
				
			if (($jabs_prec !="N")&&($row_even['absent'] =="Y")) { //gestion des suite d'even  absences
			//cumul pour n'afficher qu'une seule ligne par jour 
				if  ( $nom_cejour== $jabs_prec ) {//absence  même jour  que  tour prec
										$nbhabsj= $nbhabsj +1 ; 
							if ($nbhabsj==2){$nbDemiJabs=$nbDemiJabs+1;};// 1/2jour d'abs decompté si + d'1h d'abs	
							if ($nbhabsj==5){$nbDemiJabs=$nbDemiJabs+1;};// +1/2jour d'abs decompté si +de 4h d'abs	
										
									//	echo '<tr '.$classTr[$trColor+1]. '>'.$val_jour.' h:'.$nbhabsj.'</td></tr>';
							// on n'affiche pas la ligne ! 
							// on verra au tour suivant sauf pour le der! qu'il faut memoriser
							$jabs_prec=$nom_cejour; // pour le tour suivant et la sortie
							
					} else {  // absence nouveau jour, on solde les abs du  jour prec et on les affiche
						if (($cacheAbs!="Y")&&($nbhabsj>0) &&($sansEven!="Y") ) {
							$trColor=$trColor*(-1) ;
							echo '<tr '.$classTr[$trColor+1]. '>';
							echo '<td height="18" >'.$jabs_prec.'</td><td>';
							echo '<td '; echo 'bgcolor="gold">&nbsp;Abs&nbsp;'; echo $nbhabsj.'h </td><td colspan="6"> </td></tr>';	
							}; 
							$nbHabs=$nbHabs+$nbhabsj;
							$nbDemiJabs=$nbDemiJabs+0; $nbhabsj=1;$jabs_prec=$nom_cejour; // nouveau jour d'abs
					};
					// fin even d'absences qui se suivent
			};//fin gestion des suite d'even  absences
			
			if (($jabs_prec !="N")&&($row_even['absent'] =="N")&&($nbhabsj>0) ) { // pas une absence,mais abs précédente à solder	
					// sauf psi remier ligne  $nbhabsj==0
					if (($cacheAbs!="Y")&&($sansEven!="Y")&&($nbhabsj > 0) ){
						 $trColor=$trColor*(-1) ;
						echo '<tr '.$classTr[$trColor+1]. '>';
						echo '<td height="18" >'.$creneau.'</td>';
						
					if ($row_even['retard_V'] =="Y") 
						{$zone_cont= '<font style="background-color:#CCCCCC;">&nbsp;(rv)&nbsp;';}
					elseif ($row_even['retard_Nv'] =="Y") 
						{$zone_cont= '<font style="background-color:yellow;">&nbsp;Rnv&nbsp;';}
					else {$zone_cont='<font>';};
					echo '<td>'.$zone_cont.'</font></td>';
					echo '<td colspan="6"> </td></tr>';
						};		 
					$jabs_prec="N";$nbHabs=$nbHabs+$nbhabsj; $nbhabsj=0;
				};
			// fin edition abs j-1   
						
		if ($row_even['absent'] !="Y") {
						 // tous les even  sauf absent et absence de carnet sans incident lié	
						
						//decomptages
					if ( $row_even['annule'] != "Y" )
						{$indexmotif=$row_even['motif'];
						if ( ($indexmotif !=0 ) && ( $row_even['motif']!="0") ) 
						// pour assurer la compatibilité   motif code ou chaine
							{if  (($indexmotif > 6 )&&  $indexmotif <12 ) { $nbTra++;};
							if  (($indexmotif > 11 )&&  $indexmotif <16 ) { $nbAtt++;};
							if  (($indexmotif > 15 )&&  $indexmotif <19 ) { $nbCon++;}; 
							if  (($indexmotif > 21 ) &&  $indexmotif <28 ){ $nbSan++;}; };
							// on compte pas  le motif "autres",les convoc, ni  index 19 et 20 ni les motif !
						if ($row_even['retard_V'] =="Y") {$nbRetV++;};
						if ($row_even['retard_Nv'] =="Y")  {$nbRetNv++ ;};
					
						// analyse pbcarnet  : cumul pour n'afficher qu'une seule ligne par jour	
						//si c'est le même jour on affichera le pb carnet  pour validation !,  mais sans le decompter !
						if (($row_even['pbCarnet'] !="N") &&($cejour!= $jpbcarnetprec))
							{$pbcarnet++;;
							 $jpbcarnetprec=$cejour;						
							}; 
						};
						
						// lignes à ne pas afficher 	
						if ((($row_even['retard_V'] =="Y")&& ($cacheRval=="Y"))  || (($row_even['solde'] !="N" ) && ($cacheOk =="Y") ) ||($sansEven=="Y"))
							{ echo ''; } // ligne non affichée !
						else { // lignes des non masqués
							if ($cejour!= $jLiprec) { $trColor=$trColor*(-1);};
							$jLiprec=$cejour;
							$indexmotif =intval($row_even['motif']); 
						$style_ligne= $classTr[$trColor+1];// par defaut
						
							if  ( $indexmotif > $nbmotifs_profs )
									{$style_ligne=$style_ligne.' style="background-color:#A9F5F2;" ';};
							if ( ($row_even['annule'] != 'N')&&(strlen($row_even['annule'])>2 )) 
								{$style_ligne=$style_ligne.' style="text-decoration:line-through;" ';};
							?>
					
					<tr<?php echo $style_ligne?>>
					<td colspan="1" style="height:18px;"><?php echo $creneau?></td><?php
					
					if ($row_even['retard_V'] =="Y") 
						{$zone_cont= '<font style="background-color:#CCCCCC;">&nbsp;(rv)&nbsp;';}
					elseif ($row_even['retard_Nv'] =="Y") 
						{$zone_cont= '<font style="background-color:yellow;">&nbsp;Rnv&nbsp;';}
					else {$zone_cont='<font>';};
					echo '<td>'.$zone_cont.'</font></td>';
					?>	
					
							<td><?php if ($row_even['pbCarnet'] =="Y") {echo '<font style="background-color:#FF6340">&nbsp;C';} else { echo '<font>';}; if ( $row_even['vie_sco_statut'] == "Y"){ echo '+';} else {echo'&nbsp;';};?></font></td> <td></td>
							 <td>&nbsp; <?php if ( $indexmotif !=0 ){  echo $motifs[$indexmotif] ;};?></td>
							<td  style="padding-top:1px;">&nbsp;
							<textarea class="area_in_ligne" readonly style=" width:<?php 
								if ($row_even['details']!="" &&( $row_even['annule'] == 'N')) {$largeur= 350 ;} else { $largeur= 16 ;}; echo $largeur ; ?>px;  max-width:350px; height:12px ; max-height:100px; background-color:white ;" cols="1" rows="1">
								<?php // supprime ancienne mise en forme
								$chain=str_replace("#"," ",$row_even['details']);$chain=str_replace("pour :","/ ",$chain);
								$chain=str_replace("  "," ",$chain); echo $chain;?></textarea><?php if (strlen($row_even['details']) > 42 )
								{ echo '<img src="../images/more.png" style="vertical-align:top;" title="Agrandir la fen&ecirc;tre pour tout lire">';};?></td>
							
							<td width="180">&nbsp; <?php echo $row_even['identite']?></td>
							<td align="center" ><?php
							$pbCarnetaTraiter=($row_even['pbCarnet'] !="N") && ($row_even['annule'] == "N" );
							$ilyaIncident=($row_even['retard_Nv'] =="Y")||(intval($row_even['motif']) > 0 );
							$est_retrancrit=( $row_even['surcarnet'] != 'N');
							$IncidentaDedoubler=(($row_even['pbCarnet']=='Y') && ($row_even['vie_sco_statut']=='N'));
							// s'il y a pbcarnet + (retard ou incident) on va creer une fiche suppl. de 		pbcarnet et vie_sco_statut passe à 1
							
							if ($est_retrancrit) {
									if ($ilyaIncident) { $logo='carnetpb18.png';} else {$logo='carnetok18.png';};
									$infos=explode ('#',$row_even['surcarnet']);
									if (count($infos) <2){$infos[0]='';$infos[1]='';$infos[2]='';};
									echo '<img src="../images/carnetpb18.png" title="retranscrit par '.$infos[1].' le '.$infos[0].' &agrave; '.$infos[2].'">';
							} else {
									if ( $IncidentaDedoubler ) 
										{	
										echo '<img src="../images/carnetalert18.png" title="Incident et Oubli de carnet à retranscrire" >';
									} else	{ if($pbCarnetaTraiter) {
										// c'est dédoublé mais pas transcrit ou sans objet
										echo '<img src="../images/carnetalert18.png" title="Oubli de carnet à retranscrire" >';
										} else { echo  '-';};};
							};	 // fin gestion pb carnets
							 ?>	</td><td align="center" ><?php
							 
							// incidents soldables 
							$est_annulable=( $row_even['prof_ID']==$_SESSION['ID_prof']) ||(( $droits> 3 ) &&  ($row_even['annule'] == "N" ) && ($row_even['solde'] =="N" ));
						
							$est_soldable= ($row_even['retard_Nv'] =="Y") ||( ($row_even['motif']> ($IndexHorsRetards-1) )&&($row_even['motif'] < ($indexMotifsMax+$nbSanctions+1) ))||($row_even['pbCarnet'] =="Y");   										
							$est_annule= ( strlen($row_even['annule'])> 2 ) ;
							$est_solde= (strlen($row_even['solde'] )> 1);
							$infos[0]='';$infos[1]='';$infos[2]='';
							if ($est_solde) {
									$infos=explode ('#',$row_even['solde']);
									if (count($infos) <2){$infos[0]='';$infos[1]='';$infos[2]='';};
									$img='balance17v.png'; $tache='sold&eacute; par';
							} else { 
									if ($est_soldable) {
											$img='balance17.png'; 
											$tache='Cliquer ici pour solder cet incident';
											$caseAcocher=true;
									};
							};
								
							if ($est_annule) {// peut ecraser les precedents !
									$infos=explode ('#',$row_even['annule']);
									if (count($infos) <2){$infos[0]='';$infos[1]='';$infos[2]='';};
									$img="carnetSup18ok.png";$tache='annul&eacute; par';
							} else {
									if ($est_annulable) {
										$img='carnetSup18.png';
										$tache='Cliquer ici pour solder cet incident';
										$caseAcocher=true;
									};
							};	
							if ($est_solde||$est_annule) { 
								?><img src="../images/<?php echo $img?>" align="top" title="<?php echo $tache.$infos[1].' &#10; le '.$infos[0].' &agrave; '.$infos[2];?>"> <?php
							};
								
							if ($est_soldable && !$est_solde && !$est_annule && $row_even['retard_V']!='Y' && $row_even['absent']!='Y'){
								?><img 	src="../images/cocherOr.png" title="non soldé "><?php  
							}; 
								
							?></td></tr><?php
								
						};// fin non masqués 
							
							} ;// fin tous cas sauf abs
					$idprec=$row_even['eleve_ID']; //pour le lien au tour suivant !
		} while ($row_even = mysqli_fetch_assoc($even)); 



		// bilan du dernier 
	if ($jabs_prec !="N") {// la der ligne est une abs, il faut l'afficher !
			if (($cacheAbs!="Y")&&($nbhabsj > 0)) { // si on ne masque pas les abs
						 $trColor=$trColor*(-1) ;$nbHabs=$nbHabs+$nbhabsj;
						 if ($nbhabsj==2){$nbJabs=$nbJabs+1;};// jour d'abs decompté si + d'1h d'abs
						echo '<tr '.$classTr[$trColor+1]. '>';
						echo '<td height="18" >'.$jabs_prec.'</td><td>';
						echo '<td '; echo 'bgcolor="gold">&nbsp;Abs&nbsp;'; 
						echo $nbhabsj.'h </td><td colspan="6"> </td></tr> ';
			};
	}; 
								// fin	affichage derniere ligne si abs	
						// affichage de son bilan
				?><tr class="ligne_bilan">
				<td colspan="3" align="center" style="border :1px solid gray; "><?php if($nbHabs >0) { echo '&nbsp; absences : <a href="#" title="nombre d\'heures calculé sur les appels réalisés&#10 le nombre d\'heures r&eacute;ellement perdu peut &ecirc;tre sup&eacute;rieur..">'.$nbHabs.'h </a>sur '.$nbDemiJabs.'&frac12;j &nbsp;&nbsp;&nbsp;';} else {echo '&nbsp; aucune absence &nbsp;';};
				$bilanRetards='';
				if ($nbRetNv>0) { $bilanRetards.='+&nbsp;'.$nbRetNv.'Rnv ';	}; 
				if ($nbRetV>0) { $bilanRetards='+&nbsp;'.$nbRetV.' (rv)&nbsp;';};
				echo $bilanRetards; 
				//echo str_replace("--","-",$bilanRetards); // pour eviter les --?>
				</td>
				<td colspan="5" align="center"style="border :1px solid gray;border-right:0px solid;" ><?php 
					$bilan_incid='';
					if ($pbcarnet> 0 ) { $bilan_incid.='-&nbsp;'.$pbcarnet.' pb carnet&nbsp;-';};
					if ($nbTra> 0 ) { $bilan_incid.='-&nbsp;'.$nbTra.' pb travail&nbsp;-';};
					if ($nbAtt> 0 ) { $bilan_incid.='-&nbsp;'.$nbAtt.' pb sign. & doc&nbsp;-';};
					if ($nbCon> 0 ) {$bilan_incid.='-&nbsp;'.$nbCon.' pb comportement & doc&nbsp;-';};
					if ($nbSan> 0 ) {$bilan_incid.='-&nbsp;'.$nbSan.' retenues & exclusions &nbsp;-';};
					echo str_replace("--","-",$bilan_incid); // pour eviter les --
					?></td><td align="right" style="border:1px solid gray; border-left:0px solid #ECCEF5;"> <span style="padding:0px; border:0px solid black ; font-size:12px; ">
				<a href="carnet_elv.php?elvid=<?php echo $idprec.'&classe='.$classe.'&date1='.$date1_form.'&date2='.$date2_form; ?>&submit=Actualiser" target="_blank" title="carnet de l'&eacute;l&egrave;ve" ><IMG SRC="../images/carnet18y.png"></a></span>
				</td></tr>
		</table>
		<p>&nbsp;</p><?php 
	} else {
		echo "<p align=\"center\"> Pas d'&eacute;v&egrave;nements sur cette p&eacute;riode<b> ou pas de classe s&eacute;lectionn&eacute;e...</p>";
	};
	mysqli_free_result($even); 
	
} else {
	echo "<p  class='erreur' align=\"center\"> Il vous faut s&eacute;lectionner une classe.</p>";
};
?>
<p><a href="
<?php 
if ($droits==2 ){echo '../enseignant/enseignant.php';};
if ($droits==3 || $droits==7){echo 'vie_scolaire.php';};
if ($droits==4){echo '../direction/direction.php';};

?>
"><br>
<!--Retour au menu
<?php
if ($droits==2 ){echo ' Enseignant';};
if ($droits==3 || $droits==7){echo ' Vie scolaire';};
if ($droits==4){echo ' Responsable Etablissement';};
?>   -->
</a> </p></div>
</body>
</html>
