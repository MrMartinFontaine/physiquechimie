<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>6)&&($_SESSION['droits']<>7)&&($_SESSION['droits']<>8))
{ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
include 'evenement_select.php' ; // listes jours, listes classes, routines dates

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
};

//recup date debut d'annee scolaire
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_debut_annee' LIMIT 1";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$date_debut_annee = $row[0];
mysqli_free_result($result_read);

//recup date fin d'annee scolaire
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_fin_annee' LIMIT 1";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$date_fin_annee = $row[0];
mysqli_free_result($result_read);


if (!isset($_GET['date1'])) { $date1=date('d/m/Y');}  else { $date1=$_GET['date1'] ; } ;  // att date en yyyy-mm-jj dans la base !
 	$datemini=substr($date1,6,4).substr($date1,3,2).substr($date1,0,2);   // conversion en yyyymmjj
	$date1_form=substr($date1,0,2).'/'.substr($date1,3,2).'/'.substr($date1,6,4); // conversion  jj/mm/yyyy
	
if (isset($_GET['even_annee_entiere']) ) { 
	$date1=$date_debut_annee; 	
	$datemini=substr($date1,6,4).substr($date1,3,2).substr($date1,0,2);   // conversion en yyyymmjj
	$date1_form=substr($date1,0,2).'/'.substr($date1,3,2).'/'.substr($date1,6,4); // conversion  jj/mm/yyyy};	
};  
	
if (!isset($_GET['date2'])){ $date2=$date_fin_annee ; } else {$date2=$_GET['date2'] ;} ;
 $datemaxi=substr($date2,6,4).substr($date2,3,2).substr($date2,0,2);
 $date2_form=substr($date2,0,2).'/'.substr($date2,3,2).'/'.substr($date2,6,4);




if (!isset($_GET['classe']) ) { $selection="toutes" ;} else {$selection=$_GET['classe'] ;} ; 



// Préparation des liens pour les requetes de tri      ORDER BY {$order_sens} 
if (!isset($_GET['order_champs']) ) { $order_champs = 'date_debut , heure_debut ';} else { $order_champs=$_GET['order_champs'] ;} ; 
if (!isset($_GET['sens']) ) { $sens ='ASC';}   else { $sens = $_GET['sens'] ;}; 
if (!isset($_GET['chrono']) ) { $chrono ='';}   else { $chrono = $_GET['chrono'] ;}; 

//  elaboration des liens filtre evenement_liste.php? en fonction du contexte
function lien_tri($chrono_val,$nom_champ,$prem_sens,$textasc, $textdesc)
{  global $order_champs, $sens, $date1,$date2,$selection ;
     $lien = '<a href="evenement_liste.php?date1='.$date1.'&amp;date2='.$date2.'&amp;classe='.$selection.'&amp;order_champs='.$nom_champ ;
     if (( $order_champs==$nom_champ )&& ( $sens=='DESC') )
		{ $lien .= '&amp;sens=ASC&amp;chrono='.$chrono_val.'"  class="order_desc" >' . $textdesc .'</a>'; }
	else {if( isset($_GET['sens']) && ($order_champs==$nom_champ )) {$lien .= '&amp;sens=DESC&amp;chrono='.$chrono_val.' " class="order_asc" >' .$textasc .'</a>';} 
					else {$lien .= '&amp;sens='.$prem_sens.'&amp;chrono='.$chrono_val.' " class="blanco" title="trier selon '.$textdesc.'" >' .$textasc .'</a>';} ;};

	return $lien; 
}


if ($chrono <>'') {$order_sql=$order_champs.' '.$sens.' , '.$chrono ;}
				else {$order_sql=$order_champs.' '.$sens ;};
if ($order_champs=='nom_prof') {$order_sql='substr(nom_prof,2) '.$sens.' , '.$chrono ;};    // pour  tri sur le nom et non l'initiale du prenom !		
		$df='CONCAT(substring(date_fin,1,4),substring(date_fin,6,2),substring(date_fin,9,2))';
		$dd='CONCAT(substring(date_debut,1,4),substring(date_debut,6,2),substring(date_debut,9,2))';
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if (isset($_GET['classe_ID'])&&($_GET['classe_ID']>0)){

$query_Rsmessage="SELECT * FROM cdt_evenement_contenu ,cdt_prof,cdt_evenement_acteur 
WHERE  cdt_evenement_contenu.prof_ID = cdt_prof.ID_prof 
AND ( ".$dd." >= {$datemini} ) AND ( ".$df." <= {$datemaxi})
AND cdt_evenement_acteur.classe_ID=".$_GET['classe_ID']." AND cdt_evenement_acteur.even_ID=ID_even
ORDER BY ".$order_sql ;
} 
else
{
$query_Rsmessage="SELECT * FROM cdt_evenement_contenu ,cdt_prof 
WHERE  cdt_evenement_contenu.prof_ID = cdt_prof.ID_prof 
AND ( ".$dd." >= {$datemini} ) AND ( ".$df." <= {$datemaxi})
ORDER BY ".$order_sql ;
};



	$Rsmessage = mysqli_query($conn_cahier_de_texte, $query_Rsmessage) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsmessage = mysqli_fetch_assoc($Rsmessage);
	$totalRows_Rsmessage = mysqli_num_rows($Rsmessage);
	


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe" ;
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../styles/style_default.css" rel="stylesheet" type="text/css">
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" >

<style type="text/css">

a.discret:link { color :darkgreen ; font-style:italic;}
a.discret:active { text-decoration:none; color  :darkgreen ;font-style:italic;}
a.discret:visited {text-decoration:none; color :darkgreen ;}
a.discret:selected {text-decoration:none; color :darkgreen ;}
a.discret:hover {text-decoration:none; color :darkgreen ;}

a.blanco:link { color :#FFFFFF ;}
a.blanco:active { text-decoration:none; color  :#FFFFFF ;}
a.blanco:visited {text-decoration:none; color :#FFFFFF ;}
a.blanco:selected {text-decoration:none; color :#FFFFFF ;}
a.blanco:hover {text-decoration:none; color :#FFFFFF ;}


a.order_asc, a.order_desc:link { color :#FFFFFF ;}
a.order_asc, a.order_desc:visited  { color :#FFFFFF ;}
a.order_asc, a.order_desc: selected { color :#FFFFFF ;}
a.order_asc, a.order_desc:hover {   padding-right:14px;    background:transparent url(../images/tri_asc.png) right no-repeat;}
a.order_desc, a.order_asc:hover {    padding-right:14px;    background:transparent url(../images/tri_desc.png) right no-repeat;}

a.sem:hover {text-decoration:none; }

form{  margin:5;  padding:0;}
.bordure_grise { border: 1px solid #CCCCCC;}
body { width:100%; min-height:600px; background-color: #FAF6EF;  }
input { padding: 2px 4px ;}

.jour_en_cours { background-color: Khaki ; color: rgb(0, 0, 102); padding-left: 4px ;border: 1px solid #FAF6EF ;}
.valide_pair { background-color: #ECD6B8; ; }
.valide_impair {background-color: #E9DCCA;} 
.valide_projet {background-color: #E9DCCA} /*   #A8FFA8 vert  #E6F0FA  bleu pale     */
.projet {background-color: #EEEEEE;  }
.fond_titre { background-color: #ECD6B8; ; }
.neutre {background-color: #FFFFFF; }
.annule {background-color: #EEEEEE;  text-decoration: line-through; }
</style>

<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>
<script type="text/JavaScript">
	function MM_goToURL() 
		{ //v3.0
		var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
		for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
	}
</script>

	
</head>

<body>

  <!--<div id="container3"> en remplacement du div ci-dessus pour un tableau moins large -->

  <table class="" width="100%" border="0px" align="center" cellpadding="0" cellspacing="0">
    <!-- page 2l 2col -->
    <tr class="lire_cellule_4">
      <!-- page 1l titre -->
      <td align="center" valign="top" style="padding:0px 10px 4px 50px;"><img src="../images/identite.gif" width="16" height="16">&nbsp;<?php echo $_SESSION['identite'];?> &nbsp;&nbsp;-&nbsp;&nbsp;<img src="../images/even_planning.png" width="14" height="14" border="0" align="bottom" >&nbsp;Liste des &eacute;v&egrave;nements et actions p&eacute;dagogiques &nbsp;&agrave; <?php echo date("H").' h '.date("i") ?> &nbsp;&nbsp; &nbsp;</td>
      <td ><div align="left"> <a href="<?php switch ( $_SESSION['droits'] )   {
				case 1 : echo '../administration/index.php'; break ;
				case 2 : echo './enseignant.php'; break ;
				case 3 : echo '../vie_scolaire/vie_scolaire.php'; break ;
				case 4 : echo '../direction/direction.php'; break ;
				case 6 : echo '../assistant_education/assistant_educ.php'; break ;
				case 7 : echo '../vie_scolaire/vie_scolaire.php'; break ;
				
				//default: echo 'ecrire.php?date='.date('Ymd') ; break ;  
				} ?>"> 
				<img <?php 
				//if($_SESSION['droits']==2){echo ' onClick="window.close()" ';}?> src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" ></a></div></td>
    </tr>
    <tr class="lire_cellule_2">
      <td colspan="2">

<script type="text/javascript">
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
          <form name="frm" method="GET" action="evenement_liste.php">
            <p align="center" class="Style13"> 

			  
			  <input name="even_annee_entiere" id="even_annee_entiere"  type="submit" value="Tous les &eacute;v&egrave;nements"/>
                &nbsp; &nbsp;ou pour la p&eacute;riode du &nbsp;
                <input name='date1' type='text' id='date1' value="<?php echo $date1_form;?>" size="10">
              &nbsp;au&nbsp;
              <input name='date2' type='text' id='date2' value="<?php echo $date2_form;?>" size="10">
              &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; classes :
              <select name="classe_ID" id="classe_ID">
                <option value="0">Toutes les classes</option>
                <?php do {
			  ?>
                <option value="<?php echo $row_RsClasse['ID_classe']?>" 
			  
			   <?php 
			  if ((isset($_GET['classe_ID']))&&($row_RsClasse["ID_classe"]==$_GET['classe_ID'])){echo 'selected=" selected"';}; ?>><?php echo $row_RsClasse['nom_classe']?></option>
                <?php	} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse));
			  mysqli_free_result($RsClasse);
?>
              </select>
              &nbsp; &nbsp;
              <input name="submit" type="submit" value="Actualiser" >
              &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
			  <input type="button" value="Ajouter un &eacute;v&egrave;nement" onclick="MM_goToURL('parent','evenement_ajout.php');return document.MM_returnValue"> 
			  </p>
          </form>	    </td>
    </tr>
    <tr>
      <!-- 2em ligne page : tout sauf titre -->
      <td  valign="top" class="" colspan="2"><?php if ( $totalRows_Rsmessage <>0){?>
          <!-- zone liste sous le if -->
         
          <table width="100%" border="1"  align="center" cellpadding="0" cellspacing="0" class="bordure">
            <tr style="height: 24px;">
              <td class="Style6" style="padding:4px;" width="106px"><?php echo lien_tri(' heure_debut','date_debut','ASC','Date','date'); ?></td>
              <td class="Style6"> D&eacute;but </td>
              <td class="Style6" >Classes</td>
              <td class="Style6"><?php echo lien_tri('date_debut, heure_debut' ,'titre_even','ASC','Ev&egrave;nement','&eacute;v&egrave;nement'); ?></td>
              <td class="Style6"> Fin </td>
              <td class="Style6"><?php echo lien_tri('date_debut, heure_debut, code_mode ','code_theme','ASC','Domaine & modalit&eacute;&nbsp;','domaine & modalit&eacute;$nbsp;'); ?> </td>
              <td class="Style6">Eff&nbsp;</td>
              <td class="Style6" width="90px" ><?php echo lien_tri('date_debut, heure_debut ','nom_prof','ASC','R&eacute;dacteur','r&eacute;dacteur'); ?></td>
              <td class="Style6"><?php echo lien_tri( '','date_modif','DESC','Modifi&eacute;','modifi&eacute;'); ?></td>
              <?php if ($_SESSION['droits']==1){?><td class="Style6" ></td><?php ;};?>
			 
              <td class="Style6" ><?php echo lien_tri('date_debut, heure_debut','etat','ASC','Etat','etat'); ?></td>
            </tr>
            <!-- formatage  -->
            <?php  $val_tri_prec=''; $mois_traite=''; $num_semaine_prec='';$cmd_class='';
		 $suite_jour_en_cours=false;$val_tri_prec='';
		 if (isset($_GET['order_champs']) ) {  $champ_trie=$_GET['order_champs']; } else {$champ_trie="";}; 
			
		do { 
		 $nb_lignes=0;$titre='';$titre2='';$h_ligne2=0;$cmd_class2="";$cmd_class3='';$onglet="";$encore_en_cours=false;
			$saut_tri= (( $champ_trie <>'') && ( $val_tri_prec != substr( $row_Rsmessage[$champ_trie],0,8) ) && ( $champ_trie <>'date_debut') );
			if ($saut_tri )	{ $val_tri_prec = substr($row_Rsmessage[$champ_trie],0,8) ; // pour separer les lots tries, limite le nb de sauts 
					;$nb_lignes =1;$h_ligne='12';$titre=''; $align='middle';
					};				
			$num_semaine=date ('W',strtotime($row_Rsmessage['date_debut']));
			
			$saut_semaine=(( $num_semaine != $num_semaine_prec ) && (( $champ_trie=="" ) || ( $champ_trie=="date_debut" )));
			/*
			if ($saut_semaine )	{ $num_semaine_prec=$num_semaine;  //une ligne vide ou 2 si titre
								$nb_lignes=1;
								$h_ligne=3; $cmd_class=""; $align="middle";
								if ($num_semaine== date ('W')) 
										{ $nb_lignes=2;$h_ligne2="12"; $align2="bottom";
										$titre2=' cette semaine&nbsp;[S.'.$num_semaine.']&nbsp;';};
								if ($num_semaine== date ('W')+1) 
										{ $nb_lignes= 2; $h_ligne2="16"; $align="bottom"; 
										$titre2='la semaine prochaine&nbsp;[S.'.$num_semaine.']&nbsp;';};
										
								if ($num_semaine== date ('W')+2) 
										{ $nb_lignes= 2; $h_ligne2="16"; $align="bottom";
										$titre2='semaines suivantes :';};		
										};
				*/						
			$saut_mois = ( (substr($row_Rsmessage['date_debut'],5,2) != $mois_traite ) && (($champ_trie=="") || ($champ_trie=="date_debut")));
			if($saut_mois )		{$mois_traite=substr($row_Rsmessage['date_debut'],5,2) ;
								$nb_lignes=2;$align="middle";$cmd_class="";
								$h_ligne=24; $align="bottom";//  on va event ecraser titre et ou titre2 !
								$titre='<b>'.nom_mois($mois_traite).'</b>';$titre2='';
								if ($num_semaine== date ('W')) 
										{ $nb_lignes=2;$h_ligne2="12"; $align2="bottom";
										$titre2='cette semaine&nbsp;[S.'.$num_semaine.']&nbsp;:';};
								if ($num_semaine== date ('W')+1) 
									{  $nb_lignes=2;$h_ligne2="12"; $align2="bottom";
									$titre2='&nbsp;la semaine prochaine&nbsp;:'.$num_semaine+1 .'&nbsp;:';};
								if ($num_semaine== date ('W')+2) 
									{  $nb_lignes=2;$h_ligne2="12"; $align2="bottom";
									$titre2='&nbsp;semaines suivantes : ';}; // pas de br si cht mois +chgt sem en même temps .... 
			};
			
			
			$encore_en_cours=(($row_Rsmessage['date_debut'] < date('Y-m-d')) && ($row_Rsmessage['date_fin']>= date('Y-m-d')) );
			if ($encore_en_cours){$nb_lignes=1; $cmd_class="jour_en_cours";$titre='';$encore_en_cours=false;};
			
			$jour_en_cours = ( $row_Rsmessage['date_debut'] == date('Y-m-d') )   ;
			if (($jour_en_cours) && ($suite_jour_en_cours==false))
							{$nb_lignes=2; $cmd_class2=''; $cmd_class3='class="jour_en_cours"';
								$h_ligne2=24; $align2="middle";// une li  pour onglet avecsc text proc ecrasé!
								$titre2='cette semaine&nbsp;[S.'.$num_semaine.']';
								$onglet='Aujourd\'hui ';
								$suite_jour_en_cours=true ; 
							};
						
			// edition ligne(s) de separation	
//echo '<tr><td>'.$nb_lignes.'</td></tr>';
             if ( $nb_lignes>0) {
								echo '<tr style="border:1px solid #FAF6EF ;  " >
								<td colspan="3" '.$cmd_class.'  valign="'.$align .'" style=" border-right:1px solid #FAF6EF ; height:'.$h_ligne .'px;" > '.$titre.'</td></tr>' ;
								
								} ;
								
			if (($nb_lignes > 1)){
								echo '<tr style="border:1px solid #FAF6EF ; " >'; 
								{ echo'<td colspan="2" '.$cmd_class2.'   valign="bottom" style=" border-right:1px solid #FAF6EF ; height:'.$h_ligne2 .'px;" > '.$titre2.'</td>' ;}
								if ($onglet<>""){ echo '<td '.$cmd_class3.' align="center" valign="'.$align2 .'" style=" border-right:1px solid #FAF6EF ; height:'.$h_ligne2 .'px;" >'.$onglet.'</td>'; };
								echo '</tr>'; 
								};
								
			
	
			// debut ligne fiche	
			if  (($row_Rsmessage['etat']=="Validé") || ($row_Rsmessage['etat']=="annulé") || ($row_Rsmessage['etat']=="Report") )
						{ 
						//	if ( $num_semaine%2 == 1 ) { $class_fiche='valide_pair' ; } else { $class_fiche='valide_impair'  ; } ;
						$class_fiche='valide_projet';
						}
						   else {  $class_fiche="projet" ;}  ;
			if (($jour_en_cours) || ($encore_en_cours)){ $class_fiche="jour_en_cours" ;}; //ecrase la coloration prec
			if (($row_Rsmessage['etat']=="Report") || ($row_Rsmessage['etat']=="annulé")) 
					{ $style_barre=' style="text-decoration: line-through; "';} else {$style_barre='';};
			if (($champ_trie<>"") && ($row_Rsmessage['etat']=="Validé")) {$class_fiche="projet";}; // pour les tri gris&blancs
			if (($champ_trie<>"") && ($row_Rsmessage['etat']<>"Validé")) {$class_fiche='neutre' ; };
			
			echo '<tr class="'.$class_fiche.'" '.' >' ; // fin de balise <tr> avant edition fiche 
				

			 ?>
            <td class="tab_detail2" <?php echo $style_barre ;?> ><?php echo '<a  class="sem" title="semaine '.$num_semaine.'"> ';
					if ($row_Rsmessage['pb_dates']<>"1") 
								{ echo  jour3_ymd($row_Rsmessage['date_debut']);} 
								else { echo '&nbsp;~~&nbsp;' ;} ;
					echo ymd_dmy($row_Rsmessage['date_debut']).'</a>' ; 
					if ( $row_Rsmessage['date_fin']!= $row_Rsmessage['date_debut'] ) 
					{ echo '<br> &nbsp; &nbsp; au <br>'.jour3_ymd($row_Rsmessage['date_fin']).ymd_dmy($row_Rsmessage['date_fin']) ; } ;
					?> </td>
            <td class="tab_detail2" ><?php if ($row_Rsmessage['heure_debut']<>'h'){echo $row_Rsmessage['heure_debut'];}; ?></td>
              <td class="tab_detail2"><div align="left">
                <?php
					
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasseSel = "SELECT nom_classe FROM cdt_classe,cdt_evenement_acteur WHERE ".$row_Rsmessage['ID_even']." = even_ID AND classe_ID=ID_classe  ORDER BY nom_classe" ;
$RsClasseSel = mysqli_query($conn_cahier_de_texte, $query_RsClasseSel) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasseSel = mysqli_fetch_assoc($RsClasseSel);
$totalRows_RsClasseSel = mysqli_num_rows($RsClasseSel);
if 	($totalRows_RsClasseSel>5){echo '&nbsp;'.$totalRows_RsClasseSel.' classes';} else {
do {
echo '&nbsp;'.$row_RsClasseSel['nom_classe'].'<br>';
} while ($row_RsClasseSel = mysqli_fetch_assoc($RsClasseSel));		
}
						?>            
              </div></td>
              <td class="tab_detail2"><a href="evenement_fiche.php?ID_even=<?php echo $row_Rsmessage['ID_even'] ?>"  ><?php echo $row_Rsmessage['titre_even']; ?> </a></td>
              <td class="tab_detail2"><?php //if ($row_Rsmessage['date_debut'] != $row_Rsmessage['date_fin']) { echo '<br><br>'; } ; 
							if ($row_Rsmessage['heure_fin']<>'h'){echo $row_Rsmessage['heure_fin'];};
							if (($row_Rsmessage['etat']=="Report") || ($row_Rsmessage['etat']=="annul&eacute;")) { echo '</s>'; }; ?>            </td>
              <td class="tab_detail2" ><?php if ($row_Rsmessage['pb_dates']=="1") 
						{ echo ' <i> [ Dates &agrave; confirmer ] </i> ' ; } 
						else {echo $theme[$row_Rsmessage['code_theme']]; 
							if ($row_Rsmessage['code_mode'] <>"1"){ echo '  - '.$mode_resume[$row_Rsmessage['code_mode']].' -';};
							}; ?></td>
              <td class="tab_detail2" align="right"><?php echo $row_Rsmessage['classes_eff']; ?>&nbsp;</td>
              <td class="tab_detail2"><?php if(($_SESSION['nom_prof']== $row_Rsmessage['nom_prof']) ||($_SESSION['droits']==4) ||($_SESSION['droits']==1))
					{echo '<a class="discret" href="evenement_fiche.php?ID_even='.$row_Rsmessage['ID_even'].'&amp;mod=edit" title=" Editer la fiche ">  <img src="../images/button_edit2.png" alt="Modifier" title="Modifier" align="top" width="13" height="13"> '.$row_Rsmessage['identite'] ;}	
					else 
					{echo $row_Rsmessage['identite'];}; ?></td>
              <td class="tab_detail2"><?php echo substr($row_Rsmessage['date_modif'],8,2).'-'.substr($row_Rsmessage['date_modif'],5,2).'-'.substr($row_Rsmessage['date_modif'],0,4); ?></td>
              
                
					               <?php if ($_SESSION['droits']==1){?>
								   <td style: background="gold" >
								   <img src="../images/ed_delete.gif" onClick="if (confirm('\312tes-vous s\373r de vouloir supprimer cette fiche  <?php echo $row_Rsmessage['titre_even']; ?> ?')) {MM_goToURL('window','evenement_supprime.php?ID_even=<?php echo $row_Rsmessage['ID_even']; ?>');return document.MM_returnValue;}">
								   </td>
								   
								   <?php };?>
                     <?php  if ( (($_SESSION['droits']==4) ||($_SESSION['droits']==1)) &&( ($row_Rsmessage['etat']=="attente") ||($row_Rsmessage['etat']=="à valider") ) ) 
					{
					?>
					<td class="tab_detail2" style: background="gold">
					 <a class="valider" href="evenement_fiche.php?ID_even='<?php echo $row_Rsmessage['ID_even'];?>.'&amp;mod=edit" title=" &agrave; valider " ><?php echo $row_Rsmessage['etat'];?>
					<br> &nbsp; <img src="../images/button_edit2.png" > &nbsp;&nbsp;</a>				</td>
					<?php 
					}
					else{ 
					?>
					<td class="tab_detail2">
					<?php echo $row_Rsmessage['etat'] ;};?>					</td>
              <?php  if ($row_Rsmessage['etat']=="projet") { echo '</i>' ;} ; ?>
            </tr>
            <?php } while ($row_Rsmessage = mysqli_fetch_assoc($Rsmessage)); ?>
        </table></td>
  </table>
  <?php };?>
  <!-- fin while  tableau listes evenements 252-->

</body>
</html>
<?php
mysqli_free_result($Rsmessage); 

?>
