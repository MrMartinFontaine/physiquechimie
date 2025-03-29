<?php 
// de absence2.php

include "../authentification/authcheck.php" ;
$droits=$_SESSION['droits'];
include "./carnets_inc.php";
//on recupere la date de rentree
 mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_param = 	sprintf(" SELECT param_val FROM cdt_params 
							WHERE param_nom ='date_raz_compteur' " );
	
	$params = mysqli_query( $conn_cahier_de_texte,$query_param) or die(mysqli_error($conn_cahier_de_texte));
	$row_params = mysqli_fetch_assoc($params);
	$date_rentree_sql= $row_params['param_val']; 
	mysqli_free_result($params); 

if (isset($_GET['classe'])){$classe= $_GET['classe'] ;}else {$classe=''; }; // pour faire suivre 'classe'
if (isset($_GET['seuil'])){$quota= $_GET['seuil'] ;}else {$quota=3; }; // le quota est la lim à ne pas depasser....

if (!isset($_GET['date1']))
     { // bilan par défaut depuis la rentrée
	$date1_sql=$date_rentree_sql;
	$date1_form=date('d/m/Y',strtotime( $date1_sql));
	//pour bilan sur 3 mois	  $decal=90;		$date_debut_bilan = mktime(0, 0, 0, date("m") , date("d") - $decal, date("Y"));

		}
	else {
		$date1_sql=substr($_GET['date1'],6,4).substr($_GET['date1'],3,2).substr($_GET['date1'],0,2);
		$date1_form= $_GET['date1'];};

if (!isset($_GET['date2']))
		// bilan  à j-1 pour ne pas lister les pb carnet du jour j 
       { $date_hier = mktime(0, 0, 0, date("m") , date("d") - 1, date("Y"));
	   $date2_sql=date('Ymd', $date_hier);
		$date2_form=date('d/m/Y', $date_hier);}
	else {	$date2_sql=substr($_GET['date2'],6,4).substr($_GET['date2'],3,2).substr($_GET['date2'],0,2);
			$date2_form= $_GET['date2']; //sauf pour punitions à retranscrire
		};
		
if ((isset($_GET['classe']))&&($_GET['classe']!=-1) && ($_GET['classe']!=''))
		{ $sql_classe = 'AND classe="'.$_GET['classe']. '"' ; 
		$classe_lien = 'classe='.$_GET['classe'].'&' ;}
	else { $sql_classe ='' ; $classe_lien=''; };

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
$RsClasse = mysqli_query( $conn_cahier_de_texte,$query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse= mysqli_fetch_assoc($RsClasse);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>A&Iuml;DA - Alertes vie scolaire </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<!-- <link media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet> -->
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />

<style type="text/css">

tr { background-color:#FAFAEF; border:1px solid #FAFAEF ; border-collapse: collapse; } 
td { text-align:left ; padding:2px ; padding-left:4px ;vertical-align:middle ; border: 0px solid #FAFAEF }
.fond { background-color:#FAFAEF ; border:1px solid #FAFAEF ; }

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
					border : 0px solid #FAFAEF ;	border-collapse:collapse; background-color: #ECCEF5;
					text-align: left;	vertical-align: middle;}	
					
.area_in_ligne {	border:1px solid #CCCCCC;	padding :1px ;
				 background-color:white;font-size:10px	;}

</style>

<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>

</head>


<body style="background-color: #DEDEDE;">
<div id="container" style=" width:900px; background-color: #FAF6EF; min-height:700px; border:none ;padding-top:8px;"> 

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

<table border="0" width="840" align="center" style="margin : 10px auto auto auto " border="0">
<tr> 
<td style="padding:10px;padding-top:0px;border:1px solid black"><span style="float:right;margin-right:0px;  background-color:#D8D8D8 ; padding:0px ; border:0px solid black ; font-size:11px; vertical-align: middle;"><A HREF="#" onClick="top.close()" title="Fermer cette page"><img SRC="../images/cancel.png"  BORDER="0"></A></span> <?php //conteneur ! ?>

 <form name="frm" method="GET" action="carnets_viesco.php">
 <table  align="left" border="0" style="margin:20px 12px;">
 <tr class="ligne_gris_fonce" style="border:1px solid gray ;"> 
 <td align="center" colspan="2" style="padding: 6px; border:1px solid gray; border-right:0px solid;"><b>  Bilans pour la p&eacute;riode du &nbsp;</b>
<?php if ( $date1_sql!=$date_rentree_sql)  
{ ?> <a href="carnets_viesco.php?<?php echo $classe_lien.$date2_form; ?>&submit=Actualiser" target="_self" title="Depuis la rentr&eacute;e " >
<IMG SRC="../images/debut20.png" align="top" ></a>  <?php }; ?>

 <input name='date1' type='text' style="text-align:center;" id='date1' value="<?php echo $date1_form;?>" size="9"/> 
&nbsp;  au &nbsp; <input name='date2' type='text' style="text-align:center;" id='date2' value="<?php echo $date2_form;?>" size="9"/> 

<?php if ( $date2_sql!=  date('Ymd') )
{ ?> <a href="carnets_viesco.php?<?php echo $classe_lien.'date1='.$date1_form.'&date2='.date('d/m/Y') ; ?>&submit=Actualiser" target="_self" title="Depuis la rentr&eacute;e " >
<IMG SRC="../images/fin20.png" align="top" title="j-1"></a>  <?php }; ?></td>
<td style="padding: 6px;  border:1px solid gray; border-left:0px solid;">
 &nbsp;pour <select name="classe" id="classe">
    <option value="-1">tous</option>
    <?php while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)) { ?>
    <option value="<?php echo $row_RsClasse['nom_classe']?>"  <?php if ((isset($_GET['classe']))&&($_GET['classe']==$row_RsClasse['nom_classe'])){echo 'selected=" selected"';};?>><?php echo $row_RsClasse['nom_classe']?></option>
    <?php     } ;
mysqli_free_result($RsClasse); ?>
</select></td>
 <td class="fond" style="padding: 6px; border:0px solid ;" width="100"> &nbsp; <input name="submit" type="submit" value="Actualiser"/> </td>
 </tr>
 </table> 
 
 <table  border="1" align="left" cellpadding="0" cellspacing="0" 	style="border :1px ;margin:5px 20px;" >

<tr>
<td class="fond" width="30" > </td>	
<td class="fond" width="150"></td>
<td class="fond" width="150"></td>
<td class="fond" width="140"></td>
<td class="fond"  ></td>		
</tr>
 <tr><td class="Style6" colspan="3" style="padding:2px 0px 4px 4px ;text-align:left;" > Incident ET oubli de carnet,  &agrave; retranscrire</td>
 <td colspan="2" class="fond">&nbsp;  </td> </tr>
<?php 
 // avec $trColor+1, on va utiliser les indices 0 et 2  !  ATTENTION : style pas clos  >> ajouter "!
  $classTr=array(' class="ligne_gris_clair"  ',                  '',
						' class="ligne_gris_fonce"  ');
$trColor= -1 ; 


//=====================================  gestion des messages et pb carnet à transcrire  =========================================
	// liste des evenEtCar à extaire 
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_evenEtCar = 	sprintf("
		SELECT eleve_ID,nom_ele,prenom_ele,classe_ele,motif,details,identite 
		FROM ele_absent,ele_liste,cdt_prof
		WHERE 	ID_ele=eleve_ID  %s
			AND Id_prof= prof_ID
		    AND pbCarnet='Y'
			AND vie_sco_statut !='Y'
			AND  surcarnet = 'N'
			AND solde = 'N'
			AND annule = 'N'
			AND date <='%s'
			AND date >='%s'
		ORDER BY classe_ele,nom_ele,date,substring(heure_saisie,0,4)",
		 $sql_classe,$date2_sql, $date1_sql);
	
	$evenEtCar = mysqli_query( $conn_cahier_de_texte,$query_evenEtCar) or die(mysqli_error($conn_cahier_de_texte));
	$row_evenEtCar = mysqli_fetch_assoc($evenEtCar);
	$totalRows_evenEtCar = mysqli_num_rows($evenEtCar);
	
	
		?>


<?php 
	if ( $totalRows_evenEtCar > 0)
		{	$trColor=-1;
			do {$indexmotif= intval($row_evenEtCar['motif']); 
				if (( $indexmotif > 0)|| (strlen($row_evenEtCar['details']>0) ))
				// il faut retranscrire !
					{ $trColor=$trColor*(-1);
					 ?>
						<tr <?php echo $classTr[$trColor+1]?> >
						<td class="left" ><?php echo $row_evenEtCar['classe_ele']?></td>
						<td><?php echo $row_evenEtCar['nom_ele'].'&nbsp; '.$row_evenEtCar['prenom_ele']?></td>
						<td > <span style=" width:150px;background-color:<?php echo $color[$indexmotif]?>; padding:2px;">
						<?php if ($indexmotif >0){echo $motifs[$indexmotif];}
							else {?> 
			<textarea class="area_in_ligne" readonly style=" width:75px;  max-width:240px; height:12px; max-height:64px;font-size:10px;" cols="1"; rows="1"   >	<?php 
							// elimine balisage # initial
							echo  str_replace("#","",$row_evenEtCar['details']). '</textarea>' ;}; ?>
						</span>
						</td>						
						<td class="right">&nbsp; pour <?php echo  $row_evenEtCar['identite']; ?></td> 
						<td class="fond"><span style="border:0px solid black ; font-size:11px; ">
						<a href="carnet_elv.php?elvid=<?php echo $row_evenEtCar['eleve_ID'].'&classe='.$classe.'&date1='.$date1_form.'&date2='.$date2_form; ?>&submit=Actualiser" target="_blank" title="carnet de l'&eacute;l&egrave;ve" ><IMG SRC="../images/carnet18y.png"></a></span></td><td class="fond"></td></tr>
						<?php  };
				} while ($row_evenEtCar = mysqli_fetch_assoc($evenEtCar)); 
				
		}  // pas de pb a retrancrire
	else { ?><tr> <td colspan="3" class="encadre"  >Pas d incidents &agrave; retranscrire </td></tr>
<?php 	};?><tr><td class="fond" height=40>&nbsp;</td></tr>;
	
<?php 	mysqli_free_result($evenEtCar); 

	
//=============================gestion des oublis de carnet seul  à transcrire  avec motif=0 si prof, motif=null si retranscrit  ========

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_evenCar = 	sprintf("
		SELECT eleve_ID,nom_ele,prenom_ele,classe_ele,identite,motif
		FROM ele_absent,ele_liste ,cdt_prof
		WHERE 	ID_ele=eleve_ID %s
			AND Id_prof= prof_ID
		    AND pbCarnet='Y'
			 AND  ( motif =0 or isnull(motif))
			AND  surcarnet = 'N'
			AND solde = 'N'
			AND annule = 'N'
			AND date <='%s'
			AND date >='%s'
			GROUP BY ID_ele
		ORDER BY classe_ele,nom_ele,date,substring(heure_saisie,0,4)",
		$sql_classe,$date2_sql, $date1_sql);
	
	$evenCar = mysqli_query( $conn_cahier_de_texte,$query_evenCar) or die(mysqli_error($conn_cahier_de_texte));
	$row_evenCar = mysqli_fetch_assoc($evenCar);
	$totalRows_evenCar = mysqli_num_rows($evenCar);
// ==================oublis carnets à retranscrire  =========================================================================	
 ?>

<tr><td class="Style6" colspan="3" > Oublis de carnet à retranscrire</td>
<td colspan="2" class="fond"> &nbsp; </td> </tr>

 <?php  if ($totalRows_evenCar>0)
	{ $trColor=-1;
	do {	$indexmotif= intval($row_evenCar['motif']); 
		// il faut retranscrire !
			{ $trColor=$trColor*(-1); ?>
			<tr <?php echo $classTr[$trColor+1] ?> >
			<td class="left" ><?php echo $row_evenCar['classe_ele']?></td>
			
			<td><?php echo $row_evenCar['nom_ele'].'&nbsp; '.$row_evenCar['prenom_ele']?></td>
			
			<td><?php echo $row_evenCar['identite']?></td>
			
			<td class="fond" ><span style="border:0px solid black ; font-size:11px; ">
			<a href="carnet_elv.php?elvid=<?php echo $row_evenCar['eleve_ID'].'&classe='.$classe.'&date1='.$date1_form.'&date2='.$date2_form; ?>&submit=Actualiser" target="_blank"  title="carnet de l'&eacute;l&egrave;ve"  ><IMG SRC="../images/carnet18y.png"></a></span></td>
			<td class="fond"> &nbsp; </td><td class="fond"> &nbsp; </td> </tr>
						<?php  };
		} while ($row_evenCar = mysqli_fetch_assoc($evenCar)); 
	 
	}  // pas de défaut carnet a retrancrire
	else {
		echo '<tr><td colspan="3" class="encadre"  >Pas d\'incidents à retranscrire </td><tdcolspan="2" class="fond"></tr>';
	};
	echo '<tr class="fond"><td class="fond" height=40>&nbsp;</td></tr>';
	
	mysqli_free_result($evenCar); 
	
	
	
	//========================== Alertes  de quotas incidents atteints  mais pas dépassés !=================
	// rappel quota est la lim à ne pas depasser....
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_even = 	sprintf("
		SELECT eleve_ID,nom_ele,prenom_ele,classe_ele,date, heure_saisie,pbCarnet,retard_Nv ,motif,details 
		FROM ele_absent,ele_liste 
		WHERE 	ID_ele=eleve_ID %s
			AND absent = 'N'	AND solde = 'N'	AND annule = 'N'
			AND( retard_Nv='Y' OR  pbCarnet='Y' OR  (motif > 6 AND motif < 19))
			AND date <='%s'
			AND date >='%s'
		ORDER BY classe_ele,nom_ele,date,substring(heure_saisie,0,4)",
		  $sql_classe,$date2_sql, $date1_sql);
	
	$even = mysqli_query( $conn_cahier_de_texte,$query_even) or die(mysqli_error($conn_cahier_de_texte));
	$row_even = mysqli_fetch_assoc($even);
	$totalRows_even = mysqli_num_rows($even);
	
	// <form name="frm" method="GET" action="carnets_viesco.php">
		?>

<tr>
<td class="Style6" colspan="2"> Alerte quotas </td>
<td colspan="3" class="fond" style=" padding:0px;"> <span style= "padding:0px 6px ; border: 1px #CCCCCC ;">Seuil quotas : &nbsp;<form>
 <?php 	for ($i=1; $i<=5; $i++){ echo '<input type="radio" name="seuil" id="seuil" value="'.$i.'" ';
								if ($i==$quota  ) { echo '&nbsp; checked ';};
								echo '>'.$i.' ';}; ?>
		&nbsp; &nbsp; <input align="right" name="submit" type="submit" value="Actualiser"/> </span></form> </td> 
		<td class="fond">&nbsp; </td></tr>


<?php if ( $totalRows_even> 0 )
	{	$trColor=1;
		$nomprec="";$nbRetV=0;$nbRetNv=0;$pbcarnet=0; $nbTra=0;$nbSign_et_Carn=0;$nbCon=0;$nbSan=0;
		// initialisation pour premiere boucle !
			$nomprec= $row_even['nom_ele'];$prenomprec=$row_even['prenom_ele'];$idprec=$row_even['eleve_ID'];$classeprec=$row_even['classe_ele'];
			$jpbcarnetprec=0;
		do { 	
			if ( $row_even['nom_ele']!=$nomprec) { // bilan  eleve  et chgt
				if (($nbRetNv == $quota)|| ($pbcarnet == $quota)||($nbTra ==$quota)||($nbSign_et_Carn== $quota) ||($nbCon==$quota)) {		// affichage des décomptes d'incidents seulement si depassasement de seuil
						 $trColor=$trColor*(-1);?>
						<tr <?php echo $classTr[$trColor+1]?> >
						<td class="left" > <?php echo $classeprec.'</td><td>'.$nomprec.'&nbsp; '.$prenomprec;?>
						</td><td class="right" style="padding:0px;border:0px;">
						<?php if ($nbRetNv == $quota) {?>
						<span style=" width:150px;display:inline-block;padding:2px; background-color:#9FE855">&nbsp;<?php echo $nbRetNv?> retards  </span>
						<?php };if ($pbcarnet == $quota) {
						?><span style=" width:150px;display:inline-block;padding:2px; background-color:LightSalmon">&nbsp;<?php echo $pbcarnet?> defauts de carnet </span>
						<?php };if ($nbTra == $quota) {
							?><span style=" width:150px;display:inline-block;padding:2px; background-color:Khaki">&nbsp;<?php echo $nbTra?> pb travail </span>
						<?php };
						if ($nbSign_et_Carn == $quota) {
							?><span style=" width:150px;display:inline-block;padding:2px; background-color:yellow">&nbsp;<?php echo $nbSign_et_Carn?>  pb sign.& gest.carnet  </span><?php 
						};
						if ($nbCon == $quota) {
							?><span style=" width:150px;display:inline-block;padding:2px; background-color:red">&nbsp;<?php echo $nbCon?> pb comportement</span><?php 
						};
						?></td><td class="fond"><span style="border:0px solid black ; font-size:11px; ">
						<a href="carnet_elv.php?elvid=<?php echo $idprec.'&date1='.$date1_form.'&classe='.$classe.'&date2='.$date2_form; ?>&submit=Actualiser" target="_blank"  title="carnet de l'&eacute;l&egrave;ve"  ><IMG SRC="../images/carnet18y.png"></a></span></td>
						<td class="fond">&nbsp;</td><td class="fond">&nbsp;</td></tr>
		<?php };		// fin du bilan du precedant
						
					// nouvel eleve 
				$nomprec= $row_even['nom_ele'];$prenomprec=$row_even['prenom_ele'];$idprec=$row_even['eleve_ID'];$classeprec=$row_even['classe_ele'];
				$nbJabs=0; $nbHabs=0;$nbabsj=0; $nbRetV=0;$nbRetNv=0;$pbcarnet=0; $nbTra=0;$nbSign_et_Carn=0;$nbCon=0;$nbSan=0;$jpbcarnetprec=0;
				} ;// fin cas chgt elev
				
							
				
					//  debut d'un nouveau ou suite d'un même élève
						$indexmotif= $row_even['motif']; 
					//  criteres sur $row_even['annule']=='N');	($row_even['solde']=='N'); inutiles car déjà filtrés!
						
							if ($indexmotif > 0) {
								if  (($indexmotif > 6 ) &&  $indexmotif <12 ) { $nbTra++;};
								if  (($indexmotif > 11 )&&  $indexmotif <16 ) { $nbSign_et_Carn++;};
								if  (($indexmotif > 15 )&&  $indexmotif <19 ) { $nbCon++;}; 
							};
							
							if ($row_even['retard_Nv'] =="Y") {$nbRetNv++ ;};
							//si c'est le même jour on ne decompte pas le pb carnet  pour validation !,  mais sans le decompter !
							$lejour=substr($row_even['date'],6,2).'/'.substr($row_even['date'],4,2);
							if (($row_even['pbCarnet'] =="Y") &&($lejour!= $jpbcarnetprec))
								{$pbcarnet++;;
									$jpbcarnetprec=$lejour;						
								}; 
					} while ($row_even = mysqli_fetch_assoc($even)); 
		
	} else {	echo '<tr><<td colspan="3" class="encadre"  >Actualiser ! ou Pas de d&eacutepassements de quotas  </td></tr>';}
	;
	echo '<tr class="fond"><td height=40>&nbsp;</td></tr>';
	
	
	
//==========================  Depassements de quotas incidents  =======================================
	// rappel quota est la lim à ne pas depasser....; on reprend la même extraction
	if ($totalRows_even>0){mysqli_data_seek($even, 0);};
	?>

<tr>
<td class="Style6" colspan="2" > D&eacute;passement de quotas</td>
</tr>
 


<?php if ( $totalRows_even> 0 )
	{	$trColor=1;
		$nomprec="";$nbRetV=0;$nbRetNv=0;$pbcarnet=0; $nbTra=0;$nbSign_et_Carn=0;$nbCon=0;$nbSan=0;
		// initialisation pour premiere boucle !
			$nomprec= $row_even['nom_ele'];$prenomprec=$row_even['prenom_ele'];$idprec=$row_even['eleve_ID'];$classeprec=$row_even['classe_ele'];
			$jpbcarnetprec=0;
		do { 	
			if ( $row_even['nom_ele']!=$nomprec ) { // bilan  eleve  et chgt
				if (($nbRetNv > $quota) || ($pbcarnet > $quota)||($nbTra > $quota)||($nbSign_et_Carn > $quota) ||($nbCon > $quota))
					{// affichage des décomptes d'incidents si depassasement de seuil
						 $trColor=$trColor*(-1);
						 ?><tr <?php echo $classTr[$trColor+1]?> >
						<td class="left" > <?php echo $classeprec.'</td><td>'.$nomprec.'&nbsp; '.$prenomprec;?>
						</td><td class="right" style="padding:0px;border:0px;"><?php 
						if ($nbRetNv > $quota) {
						?><span style=" width:150px;display:inline-block;padding:2px; background-color:#9FE855">&nbsp;<?php echo $nbRetNv?> retards  </span><?php 
						};
						if ($pbcarnet > $quota) {
						?><span style=" width:150px;display:inline-block;padding:2px; background-color:LightSalmon">&nbsp;<?php echo $pbcarnet?> defaut de carnet </span>	<?php 
						};
						if ($nbTra > $quota) {
						?><span style=" width:150px;display:inline-block;padding:2px; background-color:Khaki">&nbsp;<?php echo $nbTra?> pb travail </span><?php 
						};
						if ($nbSign_et_Carn > $quota) {
							?><span style=" width:150px;display:inline-block;padding:2px; background-color:yellow">&nbsp;<?php echo $nbSign_et_Carn?>  pb sign.& gest.carnet  </span><?php
						};
						if ($nbCon > $quota) {
							?><span style=" width:150px;display:inline-block;padding:2px; background-color:red">&nbsp;<?php echo $nbCon?> pb comportement</span><?php
						};
						?></td><td class="fond"><span style="border:0px solid black ; font-size:11px; ">
						<a href="carnet_elv.php?elvid=<?php echo $idprec.'&classe='.$classe.'&date1='.$date1_form.'&date2='.$date2_form; ?>&submit=Actualiser" target="_blank"  title="carnet de l'&eacute;l&egrave;ve"  ><IMG SRC="../images/carnet18y.png"></a></span></td>
						<td class="fond">&nbsp;</td><td class="fond">&nbsp;</td></tr><?php 
					};
					// fin affichage des elv  si depassasement de seuil
					
			
				// nouvel eleve 
				$nomprec= $row_even['nom_ele'];$prenomprec=$row_even['prenom_ele'];$idprec=$row_even['eleve_ID'];$classeprec=$row_even['classe_ele'];
				$nbJabs=0; $nbHabs=0;$nbabsj=0; $nbRetV=0;$nbRetNv=0;$pbcarnet=0; $nbTra=0;$nbSign_et_Carn=0;$nbCon=0;$nbSan=0;$jpbcarnetprec=0;
			} ;	// fin cas chgt elev
				
			  // suite d'un même élève ou debut nouvel
				$indexmotif= $row_even['motif']; 
				if ($indexmotif > 0) {
						if  (($indexmotif > 6 )&&  $indexmotif <12 ) { $nbTra++;};
						if  (($indexmotif > 11 )&&  $indexmotif <16 ) { $nbSign_et_Carn++;};
						if  (($indexmotif > 15 )&&  $indexmotif <19 ) { $nbCon++;}; 
				};
						
				if ($row_even['retard_Nv'] =="Y") {$nbRetNv++ ;};
				//si c'est le même jour on ne decompte pas le pb carnet  pour validation !,  mais sans le decompter !
				$lejour=substr($row_even['date'],6,2).'/'.substr($row_even['date'],4,2);
				if (($row_even['pbCarnet'] =="Y") &&($lejour!= $jpbcarnetprec) ){
					$pbcarnet++;;
					$jpbcarnetprec=$lejour;						
				}; 
										
		} while ($row_even = mysqli_fetch_assoc($even)); 
		
	} else {	echo '<tr><<td colspan="3" class="encadre"  >Actualiser ! ou Pas de d&eacutepassements de quotas  </td></tr>';}
	;
	echo '<tr class="fond"><td height=40>&nbsp;</td></tr>';
	
	mysqli_free_result($even); 

	
	//=============================== Rdv et retenues à notifier ===========================================================

	// liste des evenRetenue à extaire AND solde=''  			AND    motif <> '' AND motif <>'0') and annule<> 1		
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_evenRetenue = 	sprintf("
		SELECT eleve_ID,nom_ele,prenom_ele,classe_ele,date, heure_saisie,absent,retard_V,pbCarnet,retard_Nv ,motif,details,solde,identite
		FROM ele_absent,ele_liste ,cdt_prof
		WHERE 	ID_ele=eleve_ID %s
			AND Id_prof= prof_ID
		    AND motif > %u
			AND  surcarnet = 'N'
			AND solde = 'N'
			AND annule = 'N'
			AND date <='%s'
			AND date >='%s'
		ORDER BY classe_ele,nom_ele,date,substring(heure_saisie,0,4)",
		$sql_classe, $nbmotifs_profs, date('Ymd'), $date1_sql);//ici date2 est remplace par date du jour 
	
	$evenRetenue = mysqli_query( $conn_cahier_de_texte,$query_evenRetenue) or die(mysqli_error($conn_cahier_de_texte));
	$row_evenRetenue = mysqli_fetch_assoc($evenRetenue);
	$totalRows_evenRetenue = mysqli_num_rows($evenRetenue);
	?>

<tr><td class="Style6" colspan="3" > Rendez-vous &agrave; venir  et sanctions  &agrave; retranscrire</td>
<td colspan="2" class="fond">&nbsp;  </td> </tr>


<?php 
	if ( $totalRows_evenRetenue > 0)
		{	$trColor=-1;
			do {$indexmotif= intval($row_evenRetenue['motif']); 
				if (( $indexmotif > 0)|| (strlen($row_evenRetenue['details']>0) ))
				// il faut retranscrire ! 
					{ $trColor=$trColor*(-1); ?>
						<tr <?php echo $classTr[$trColor+1]?> >
						<td class="left" > <?php echo $row_evenRetenue['classe_ele']?></td>
						<td> <?php echo $row_evenRetenue['nom_ele'].'&nbsp; '.$row_evenRetenue['prenom_ele']?></td>
						<td style="background-color:<?php echo $color[$indexmotif].'; border-bottom:1px solid #CCCCCC;"> '.$motifs[$indexmotif]?></td>
						<td> par <?php echo $row_evenRetenue['identite']?></td>
						<td class="right" style="padding :1px;" > <?php
						// $infos[0]='';$infos[1]='';$infos[2]='';
						if (strlen($row_evenRetenue['details'])> 1) {
								// $infos=$row_evenRetenue['details']; // explode ('#',$row_evenRetenue['details']);
								// if (count($infos) <2){$infos[0]='';$infos[1]='';$infos[2]='';};
								$largeur= 210 ;
						} else {
						 $largeur= 10 ;
						 };  
						 $hauteur=13;?><textarea  class="area_in_ligne"  readonly style=" width:<?php echo $largeur ; ?>px;  max-width:210px; height:<?php echo $hauteur;?>px; max-height:90px; background-color:white ;" cols="1" rows="1" ><?php 	
								// echo $infos[0].$infos[1].$infos[2].'
								// eliminer les balises # version initiale balisée
						$chain= str_replace("#","", $row_evenRetenue['details']);
						$chain= str_replace("  "," ",$chain);$chain=str_replace("pour :","/",$chain);
						echo $chain;	?></textarea><img src="../images/more45.png" title="Agrandir la fen&ecirc;tre pour tout lire"></td>
						<td class="fond" style="padding : 0px 5px;"><span style="border:0px solid black ; font-size:11px; ">
				<a href="carnet_elv.php?elvid=<?php echo $row_evenRetenue['eleve_ID'].'&classe='.$classe.'&date1='.$date1_form.'&date2='.$date2_form; ?>&submit=Actualiser" target="_blank"  title="carnet de l'&eacute;l&egrave;ve"  ><IMG SRC="../images/carnet18y.png"></a></span></td></tr>
						<?php  };
				} while ($row_evenRetenue = mysqli_fetch_assoc($evenRetenue)); 
				
		}  // pas de pb a retrancrire
	else { 
	echo '<tr> <td colspan="3" class="encadre"  >Pas de retenues ou sanctions &agrave; retranscrire </td></tr>';
		};
echo '<tr class="fond"><td class="fond" height=40>&nbsp;</td></tr>';
	
	mysqli_free_result($evenRetenue); 

	
	//======================== retenues et sanctions  transcrites à venir =======================================================

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_evenRetenue = 	sprintf("
		SELECT eleve_ID,nom_ele,prenom_ele,classe_ele,date, heure_saisie,absent,retard_V,pbCarnet,retard_Nv ,motif,details,solde,identite
		FROM ele_absent,ele_liste ,cdt_prof
		WHERE 	ID_ele=eleve_ID %s 
			AND Id_prof= prof_ID
		    AND motif > %u
			AND  surcarnet != 'N'
			AND solde = 'N'
			AND annule = 'N'
			AND date <='%s'
			AND date >='%s'
		ORDER BY classe_ele,nom_ele,date,substr(heure_saisie,0,4)",
		$sql_classe,$nbmotifs_profs, $date2_sql, $date1_sql);
	
	$evenRetenue = mysqli_query( $conn_cahier_de_texte,$query_evenRetenue) or die(mysqli_error($conn_cahier_de_texte));
	$row_evenRetenue = mysqli_fetch_assoc($evenRetenue);
	$totalRows_evenRetenue = mysqli_num_rows($evenRetenue);
	
	
		?>
<tr><td class="Style6" colspan="3" > Retenues retranscrites &agrave; venir ou &agrave; solder </td>
<td colspan="2" class="fond">&nbsp;  </td> </tr>


<?php 	
	if ( $totalRows_evenRetenue > 0)
		{	$trColor=-1;
			do {$indexmotif= intval($row_evenRetenue['motif']); 
				if (( $indexmotif > 0)|| (strlen($row_evenRetenue['details']>0) ))
				// il faut retranscrire !
					{ $trColor=$trColor*(-1);?>
						<tr <?php echo $classTr[$trColor+1]?> >
						<td class="left" ><?php echo $row_evenRetenue['classe_ele']?></td>
						<td><?php echo $row_evenRetenue['nom_ele'].'&nbsp; '.$row_evenRetenue['prenom_ele']?></td>
						<td style="background-color:<?php echo $color[$indexmotif].'; border-bottom:1px solid #CCCCCC;"> '.$motifs[$indexmotif] ?></td>								
						<td> par <?php echo $row_evenRetenue['identite']?></td>
						<td class="right" style="padding :1px;" ><?php 
						// $infos[0]='';$infos[1]='';$infos[2]='';
						if (strlen($row_evenRetenue['details'])> 1) {
								// $infos= explode ('#',$infos=$row_evenRetenue['details']; // explode ('#',$row_evenRetenue['details']););
								/// if (count($infos) <2){$infos[0]='';$infos[1]='';$infos[2]='';};
								$largeur= 210 ;
						} else {
						 $largeur= 10 ;
						 };  
						 $hauteur=13;?><textarea  class="area_in_ligne"  readonly style=" width:<?php echo $largeur ; ?>px;  max-width:210px; height:<?php echo $hauteur;?>px; max-height:90px; background-color:white ;" cols="1" rows="1" ><?php 
								//echo $infos[0].'à'.$infos[1].$infos[2].'&#10;pour '.$infos[3].'&#10'.$infos[4].'
								//echo  explode ('#',$row_evenRetenue['details']);
									// eliminer les balises # version initiale balisée
						$chain= str_replace("#","", $row_evenRetenue['details']);
						$chain= str_replace("  "," ",$chain);$chain=str_replace("pour :","/",$chain);
						echo $chain;?></textarea><img src="../images/more45.png" title="Agrandir la fen&ecirc;tre pour tout lire"></td>

						<td class="fond"><span style="border:0px solid black ; font-size:11px; ">
				<a href="carnet_elv.php?elvid=<?php echo $row_evenRetenue['eleve_ID'].'&classe='.$classe.'&date1='.$date1_form.'&date2='.$date2_form; ?>&submit=Actualiser" target="_blank"  title="carnet de l'&eacute;l&egrave;ve"  ><IMG SRC="../images/carnet18y.png"></a></span></td></tr>
						<?php  };
				} while ($row_evenRetenue = mysqli_fetch_assoc($evenRetenue)); 
				
		}  // pas de pb a retrancrire
	else { 
	echo '<tr> <td colspan="3" class="encadre"  >Pas de retenues programm&eacute;es sur cette p&eacute;riode </td></tr>';
		};
	echo '</table>';
	
	mysqli_free_result($evenRetenue); 

	
		

	
	
	
?></tr></table>	
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
</a><div>
</body>
</html>
