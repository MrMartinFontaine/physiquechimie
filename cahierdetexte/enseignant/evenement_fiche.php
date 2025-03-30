<?php 
// mmupdate=form1 on enregistre ; sinon on affiche en consultation ou   en edition si proprietaire ou   direction si mod=edit  

include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>6)&&($_SESSION['droits']<>7)&&($_SESSION['droits']<>8))
{ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
include 'evenement_select.php' ; // listes jours, listes classes, routines dates

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
$datetoday=date('y-m-d');
$date_edt= date("d/m/Y");// pour afficher edt

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsniv = "SELECT * FROM cdt_niveau ORDER BY nom_niv ";
$Rsniv = mysqli_query($conn_cahier_de_texte, $query_Rsniv) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsniv = mysqli_fetch_assoc($Rsniv);
$totalRows_Rsniv = mysqli_num_rows($Rsniv);

$i=1;
do
{
$indcl_id[$i]=$row_RsClasse['ID_classe'];
$indcl_nom[$i]=$row_RsClasse['nom_classe'];
$i=$i+1;
}while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)) ;


// y a til un valideur
$valideur=''; //son nom
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='even_nom_valid_mail' LIMIT 1";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$valideur = $row[0];
mysqli_free_result($result_read);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsActeur =sprintf("SELECT * FROM cdt_evenement_acteur WHERE even_ID=%u ",$_GET['ID_even'] );
$RsActeur = mysqli_query($conn_cahier_de_texte, $query_RsActeur) or die(mysqli_error($conn_cahier_de_texte));
$row_RsActeur = mysqli_fetch_assoc($RsActeur);
$totalRows_RsActeur = mysqli_num_rows($RsActeur);



// extraction des classes destinataires de l'even
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsdest =sprintf("SELECT * FROM cdt_evenement_destinataire WHERE even_ID=%u ",$_GET['ID_even'] );
$Rsdest = mysqli_query($conn_cahier_de_texte, $query_Rsdest) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsdest = mysqli_fetch_assoc($Rsdest);
$totalRows_Rsdest = mysqli_num_rows($Rsdest);


// maj  fiche evenement  
if ((isset($_POST["MM_update"])) && ( $_POST["MM_update"] == "form1")  && (isset($_POST['titre_even']))) 
{
/*
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);
$i=1;
do
{
$indcl_id[$i]=$row_RsClasse['ID_classe'];
$indcl_nom[$i]=$row_RsClasse['nom_classe'];

$i=$i+1;
}while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)) ;

*/
$accompagnateurs= 	strtoupper ($_POST['acc1']).';'.strtoupper ( $_POST['acc2'] ).';'.strtoupper ($_POST['acc3']).';'.
					strtoupper ($_POST['acc4']).';'.strtoupper ( $_POST['acc5'] ).';'.strtoupper ($_POST['acc6']).';'.
					strtoupper ($_POST['acc7']).';'.strtoupper ( $_POST['acc8'] ).';'.strtoupper ($_POST['acc9']).';' ;


  if ( ($_POST['mail'] =="oui") && ( $_POST['etat'] =="à valider" ) )   {$etat ="attente" ;} else { $etat = $_POST['etat'] ;} ;


 $updateSQL = sprintf(" UPDATE `cdt_evenement_contenu` SET  detail=%s ,  etat=%s , prof_ID=%u , classes_eff=%u, cout_elv=%s, cout_glob=%s , details_sup=%s , accompagnateurs=%s ,date_modif=%s  WHERE ID_even=%u" ,
  GetSQLValueString( $_POST['detail'], "text"),  // 
  GetSQLValueString($etat, "text"),
  GetSQLValueString($_POST['redacteur'], "int"), // on conserve le prof_ID du redacteur initial
  GetSQLValueString($_POST['classes_eff'], "int"),
  GetSQLValueString($_POST['cout_elv'], "text"),
  GetSQLValueString($_POST['cout_glob'], "text"),
  GetSQLValueString($_POST['details_sup'], "text"),
  GetSQLValueString($accompagnateurs , "text"),
  GetSQLValueString($datetoday, "text"),
  GetSQLValueString($_GET['ID_even'],"int")
 ); //  
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));

if (isset($_POST['modif'])){$pm=$_POST['modif'];}else {$pm='';};
  if ( (isset($_POST['date1'])) && ($pm <>"restreint") ) // crucial pour ne pas effacer avec les pseudo champs input date et classes en affichade du mode restreint....
	{

	$date1=substr($_POST['date1'],6,4).'-'.substr($_POST['date1'],3,2).'-'.substr($_POST['date1'],0,2);
	$date2=substr($_POST['date2'],6,4).'-'.substr($_POST['date2'],3,2).'-'.substr($_POST['date2'],0,2);
	if (isset($_POST['pb_dates']) ) {$pb_dates= $_POST['pb_dates'] ; } else {$pb_dates="0" ;};
	if (isset($_POST['date_modif']) ) { $date_modif = $_POST['date_modif'];} else { $date_modif= $datetoday;} ; // date non modifiée renvoyée par admin.... 
	$updateSQL = sprintf(" UPDATE `cdt_evenement_contenu` SET titre_even =%s, code_mode=%s , code_theme=%s,
					 date_debut=%s, date_fin=%s,pb_dates=%s, date_modif=%s  WHERE ID_even=%u" ,
	GetSQLValueString( str_replace( "\"", "&quot;", $_POST['titre_even']), "text"),  
	GetSQLValueString($_POST['code_mode'], "text"), 
	GetSQLValueString($_POST['code_theme'], "text"), 
	GetSQLValueString($date1, "text"),
	GetSQLValueString($date2, "text"),
	GetSQLValueString($pb_dates , "text"), 
	GetSQLValueString($date_modif, "text"),
	GetSQLValueString($_GET['ID_even'],"int")
	); 

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
	}

  
    if ( (isset($_POST['date1'])  &&(isset($_POST['modif'])) && ($_POST['modif'] <> "restreint")) || (( isset($_POST['modif'])) && ($_POST['modif'] == "restreint") && isset($_POST['modif_horaires']) &&($_POST['modif_horaires']=="oui")) )//modif des heures et mail 
	
 {	$heure1=$_POST['heure_debut_h'].'h'.$_POST['heure_debut_min'];
	$heure2=$_POST['heure_fin_h'].'h'.$_POST['heure_fin_min'];
	$updateSQL = sprintf(" UPDATE `cdt_evenement_contenu` SET  heure_debut=%s , heure_fin=%s WHERE ID_even=%u" ,
	GetSQLValueString($heure1, "text"),
	GetSQLValueString($heure2, "text"),
	GetSQLValueString($_GET['ID_even'],"int")
	);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte)); 
 }
 $change_horaires= ($_POST['modif_horaires']=="oui" );
 
 

  $UID= $_GET['ID_even'];

  $a_valider= (( $_POST['etat']=="Validé") && ( $_POST['etat0']<>"Validé") && ($_SESSION['droits']<>1) ); // pour ne pas diffuser si déja validé ! (ou admin)  

  if ( $a_valider) {  // enregistre la date de validation  appelee date_envoi ( nom d'origine....)
   $updateSQL = sprintf(" UPDATE `cdt_evenement_contenu` SET date_envoi=%s WHERE ID_even=%u" ,
   GetSQLValueString( $datetoday, "text") ,
   	GetSQLValueString($_GET['ID_even'],"int")   
	);
   mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
   $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
  }

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsActeur="SELECT * FROM cdt_evenement_acteur,cdt_classe,cdt_groupe
			WHERE 	cdt_evenement_acteur.even_ID = ".$UID."
					AND cdt_evenement_acteur.classe_ID = cdt_classe.ID_classe 
					AND cdt_evenement_acteur.groupe_ID = cdt_groupe.ID_groupe 	
				ORDER BY nom_classe" ; 				
$RsActeur = mysqli_query($conn_cahier_de_texte, $query_RsActeur) or die(mysqli_error($conn_cahier_de_texte));
$row_RsActeur = mysqli_fetch_assoc($RsActeur);
$totalRows_RsActeur = mysqli_num_rows($RsActeur);
do {
			
			$classes_conc.=$row_RsActeur['nom_classe'].' - '.	$row_RsActeur['groupe']."\n";
	}
while ($row_RsActeur = mysqli_fetch_assoc($RsActeur));






 //envoi d'un mail  soit au valideur ( demande de validation ) //  soit aux -autres- resp (demande validée) 
 if (  ( ($_POST['mail']=="oui") && ( $valideur > "" ) ) ||  $a_valider || $change_horaires ) 
  {
$entete  = 'From: '.noAccents($_SESSION['identite']).'<';
if (isset($_SESSION['email'])){$entete.= $_SESSION['email'];};
$entete.='>'."\r\n"; 
	$subject =  str_replace( '"','-' ,noAccents($titre_even)).'    ('.$_POST['date1'].')' ; // à =E0 é =E8 è =E9
	// $headers .='Content-Type: text/html; charset="UTF-8"'."\n";  $headers .='Content-Transfer-Encoding: 8bit'; 
	// $subject= mb_encode_mimeheader($subject,"UTF-8", "B", "\n"); 
	
		if ($_POST['date1']==$_POST['date2'])  {
				$info_dates='Date : '.jour_dmy($_POST['date1']).' '.$_POST['date1'] ;
				if ( $_POST['code_mode']<= $surplace ) { $info_dates .= '   Debut : ';} else { $info_dates .='Depart : ';} ;
				$info_dates .= $heure1.'    ';
				if ($_POST['code_mode']<= $surplace) { $info_dates .= 'Fin : ';} else { $info_dates .='Retour : ';} ;
				$info_dates .= $heure2."\n"."\n" ;}
			else { $info_dates='Depart : '.jour_dmy($_POST['date1']).' '.$_POST['date1'].'  -> '.$heure1."\n".
							'Retour : '.jour_dmy($_POST['date2']).' '.$_POST['date2'].'  -> '.$heure2."\n"."\n" ; } ;
	


$message =noAccents($_POST['titre_even'])."\n"."\n".' --> '.$_POST['etat'].' <--'."\n"."\n".$info_dates;
$message .=	
	'Description : '.noAccents($_POST['description'])."\n"."\n". 
	'Classes : '."\n"."\n".$classes_conc."\n"."\n".
	'Accompagnateurs : '.str_replace(';','  ',noAccents($accompagnateurs))."\n"."\n".
	'Envoyé le : '.date("d/m/Y").'  à '.date("H").'h '.date("i")."\n"."\n" ;
	
  if ($a_valider)  {$deb_subject='Action validee :  ';} else { $deb_subject=' Changement d horaires pour ';} ;
	
	
			//on recupère l'adresse du site jusqu'a la racide du cdt
			//$http_site = (url_cdt());
			//lien vers la fiche - mais probleme sécutite - a etudier
	        //$message.=$http_site.'enseignant/evenement_fiche.php?ID_even='.$UID.'&mod=edit';
	
  if ($_POST['mail']=="oui" )
		{ 
		

			//on recupere les adresses mel
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='even_dest_mail' ";
			$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
			$row = mysqli_fetch_row($result_read);
			$adr_mail = $row[0];
			mysqli_free_result($result_read);

			$liste_mail =explode(";",$adr_mail);
		
	 $http_site = (url_cdt());
	 mail($valideur_mail,'Action a valider :  '.$subject, $message , $entete);
}

		else { 
		//on recupere les adresses mel
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='even_dest_mail' ";
			$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
			$row = mysqli_fetch_row($result_read);
			$adr_mail = $row[0];
			mysqli_free_result($result_read);

			$liste_mail =explode(";",$adr_mail);
			for ($i=0; $i< count($liste_mail); $i++)
			{
				mail($liste_mail[$i], $deb_subject.$subject, $message, $entete) ;} ;
	    } ;
  }	 // fin mails

  
 // raz destinataires liees a un evenement  avant actualisation 
  if ((isset($_GET['ID_even'])) && ($_GET['ID_even'] != "") ) {
	$deleteSQL = sprintf("DELETE FROM cdt_evenement_destinataire WHERE even_ID=%u",
                       GetSQLValueString($_GET['ID_even'], "int"));

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	}
 
 
 
 // raz acteurs lies a un evenement  avant actualisation 
  if ((isset($_GET['ID_even'])) && ($_GET['ID_even'] != "") ) {
	$deleteSQL = sprintf("DELETE FROM cdt_evenement_acteur WHERE even_ID=%u",
                       GetSQLValueString($_GET['ID_even'], "int"));

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	}
if( $_POST['etat']== "Validé" ) {$visible='O' ;} else{ $visible='N';};








// ceux sont concerne par l'evenement

for ($i=1; $i<=$totalRows_RsClasse; $i++) { 
  $refclasseven='classeven'.$i;
  $refgroupeven='groupeven'.$i;
//echo 'ici'.$_POST[$refclasseven];
	if (isset($_POST[$refclasseven])&&(isset($_POST[$refgroupeven])) &&($_POST[$refclasseven]=='on'))
	{
		$insertSQL2= sprintf("INSERT INTO `cdt_evenement_acteur` ( `even_ID` , `classe_ID` , `groupe_ID`  )  VALUES ('%u', '%u','%u');",$UID,$indcl_id[$i], $_POST[$refgroupeven]);
		$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));
		
		
		}//du if
}//du for









// ceux qui recoivent l'info
if( $_POST['etat']== "Validé" ) {$visible='O' ;} else{ $visible='N';};

for ($i=1; $i<=$totalRows_RsClasse; $i++) { 
  $refclassedest='classedest'.$i;
  $refgroupedest='groupedest'.$i;

if (isset($_POST[$refclassedest])&&(isset($_POST[$refgroupedest])) &&($_POST[$refclassedest]=='on'))
{
$insertSQL2= sprintf("INSERT INTO `cdt_evenement_destinataire` ( `even_ID` , `classe_ID` , `groupe_ID` ,`visible` )  VALUES ('%u', '%u','%u', '%s');",$UID,$indcl_id[$i], $_POST[$refgroupedest] , $visible);
$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));
}//du if
}//du for





$insertGoTo = "evenement_liste.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }

header(sprintf("Location: %s", $insertGoTo));



} // fin isst et sortie  enregistrement


// lecture et affichage de la fiche
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifMessage =sprintf("SELECT * FROM cdt_evenement_contenu,cdt_prof WHERE ID_even=%u AND cdt_evenement_contenu.prof_ID = cdt_prof.ID_prof ",$_GET['ID_even'] );
$RsModifMessage = mysqli_query($conn_cahier_de_texte, $query_RsModifMessage) or die(mysqli_error($conn_cahier_de_texte));
$row_RsModifMessage = mysqli_fetch_assoc($RsModifMessage);
$totalRows_RsModifMessage = mysqli_num_rows($RsModifMessage);

//======GESTION AFFICHAGE ET MODIFS : bascule en  $modif  si (auteur  et pas validé ) ou (direction et mod=edit) ou admin ====================
$autorise_edit=( ( $row_RsModifMessage['ID_prof']== $_SESSION['ID_prof']) || ($_SESSION['droits']==4)  ||($_SESSION['droits']==1) ) ;
if (isset($_GET['mod'])){$modif =(($_GET['mod']=="edit")&&($autorise_edit)); } else {$modif=false;};;


 // bascule en mode  $modif_restreint  pour l'auteur si le  projet est déjà validé 
$modif_restreint=( ( $row_RsModifMessage['ID_prof']== $_SESSION['ID_prof'] )&&  ( $row_RsModifMessage['etat'] =="Validé" ) );

//Dissociation heure et date
$hhd=substr($row_RsModifMessage['heure_debut'],0,2);
$mmd=substr($row_RsModifMessage['heure_debut'],3,2);
$hhf=substr($row_RsModifMessage['heure_fin'],0,2);
$mmf=substr($row_RsModifMessage['heure_fin'],3,2);
$date_debut=substr($row_RsModifMessage['date_debut'],8,2).'/'.substr($row_RsModifMessage['date_debut'],5,2).'/'.substr($row_RsModifMessage['date_debut'],0,4);
$date_fin=substr($row_RsModifMessage['date_fin'],8,2).'/'.substr($row_RsModifMessage['date_fin'],5,2).'/'.substr($row_RsModifMessage['date_fin'],0,4);
$mois_planning=substr($row_RsModifMessage['date_debut'],0,4).substr($row_RsModifMessage['date_debut'],5,2).'01' ; //pour pointage planning

$num_semaine=date ('W',strtotime($row_RsModifMessage['date_debut']));

// Liste des classes et groupes
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
<title>Cahier de textes -<?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../styles/style_default.css" rel="stylesheet" type="text/css">
<?php if ($modif ) 
 {  //seulement en modif 
 ?>
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" >
<link type="text/css" href="../styles/jquery.autocomplete.css" rel="stylesheet"  />
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>
<script type="text/javascript" src="../jscripts/jquery.autocomplete.js"></script>

<script language="JavaScript" type="text/JavaScript">

function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}



function verifier() {
  var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
 
if(document.form1.titre_even.value == "") 
		{ alert ('Donner un titre \340 votre \351v\351nement'); 
         return false; } 
 

 if(document.form1.classe1.value == "") 
 { alert("Il faut indiquer au moins une classe !");
   return false;  } 
 
 
  if(document.form1.classes_eff.value == "") 
  { alert("Il faut preciser l'effectif !");
  return false;  }  
  
  if(document.form1.date1.value == "") 
  { alert("Il faut preciser la date de depart ");
   return false; } 
   
  if(document.form1.heure_debut_min.value == "")
   { alert("Il faut preciser l'heure de depart ");
   return false; }  
   
  if(document.form1.date2.value == "") 
  { alert("Il faut preciser la date de retour ");
  return false;  } 
  
if(document.form1.heure_fin_min.value == "")
   { alert("Il faut preciser l'heure de retour ");
  return false;  } 
  
if(document.form1.acc1.value == "")
   { alert("Il faut au moins un accompagnateur ");
  return false;  } 
    
 if(  document.form1.pb_dates.checked  && (document.form1.etat.value == "à valider") )  
   { alert("Pour une demande de validation, les dates ne peuvent pas être incertaines ! "); // bloque la validation si dates incertaines
   return false;  }   
   
 if( document.form1.mail.checked && (document.form1.etat.value != "à valider") )  
   { alert("Pour envoyer une demande de validation, selectionner l'option - projet à valider -  "); // limite  les enois de mails inutiles
   return false;  }  

   }   
 

 /* function verifier() {
  var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
   for (var i=0; i<cases.length; i++)  {     // on les parcourt
       if (cases[i].type == 'checkbox')    // si on a une checkbox...
	     { //alert(cases[i].checked);
		 if (cases[i].checked==true) {  	//si la case est cochee, envoi du formulaire		
		        if(cases[i].name != 'online') {return true}
				}; 
		 }
		 
   };
   alert("Cocher au moins une classe");
   return false;  
 return true;
 } */
 
 // autocompletion des noms d'accompagnateurs
   $(document).ready(function() {
    $('#acc1').autocomplete('fetch_nom_prof.php');});
   $(document).ready(function() {
    $('#acc2').autocomplete('fetch_nom_prof.php');});
	$(document).ready(function() {
    $('#acc3').autocomplete('fetch_nom_prof.php');});
	$(document).ready(function() {
    $('#acc4').autocomplete('fetch_nom_prof.php');});
    $(document).ready(function() {
    $('#acc5').autocomplete('fetch_nom_prof.php');});
	$(document).ready(function() {
    $('#acc6').autocomplete('fetch_nom_prof.php');});
	$(document).ready(function() {
    $('#acc7').autocomplete('fetch_nom_prof.php');});
	$(document).ready(function() {
    $('#acc8').autocomplete('fetch_nom_prof.php');});
	$(document).ready(function() {
    $('#acc9').autocomplete('fetch_nom_prof.php');});
	
 $(document).ready(function() {
    $('#acc10').autocomplete('fetch_nom_prof.php');});
 
</script>
<?php }; ?>
<script type="text/JavaScript">
 function fermer(){
		var obj_window = window.open('', '_self');
		obj_window.opener = window;
		obj_window.focus();
		opener=self;
		self.close();
	}
function ShowNiveauActeur(ID_niv,val_statut){
var xhr_object = null; 
		var _response = null;
		var _val_statut = null;

		if ( val_statut == true ) {
			_val_statut = 1;
		} else {
			_val_statut = 0;
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
	   	xhr_object.open("POST", "./ajax_niveau_even_acteur.php", true);
	   	xhr_object.onreadystatechange = function() { 
	    	if (xhr_object.readyState == 4) {
	          	_response = xhr_object.responseText; 
			$("#msg1").html(_response );
	        }
	   	}	 
	 	xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
		xhr_object.send("ID_niv=" + ID_niv + "&val_statut=" + _val_statut  ); 
			 
}

function ShowNiveauDest(ID_niv,val_statut){
var xhr_object = null; 
		var _response = null;
		var _val_statut = null;

		if ( val_statut == true ) {
			_val_statut = 1;
		} else {
			_val_statut = 0;
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
	   	xhr_object.open("POST", "./ajax_niveau_even_dest.php", true);
	   	xhr_object.onreadystatechange = function() { 
	    	if (xhr_object.readyState == 4) {
	          	_response = xhr_object.responseText; 
			$("#msg2").html(_response );
	        }
	   	}	 
	 	xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
		xhr_object.send("ID_niv=" + ID_niv + "&val_statut=" + _val_statut  ); 
			 
}
</script>
<style>
form{ margin:5;   padding:0;}
input { margin-top: 3px ; padding: 2px  4px ;}
textarea { margin-top: 3px ; padding: 2px  4px ;}

<?php if ( $row_RsModifMessage['etat'] <>"Validé" )
{ echo '.fige { background-color:#E5E5E5;  padding: 2px  3px ; }' ; }
else { echo '.fige { background-color:#ECD6B8; padding: 2px  4px ; } ' ;} ; ?>

.masque { background-color:#FAF6EF;  padding: 2px  6px ; border-style:none; }

a.info_bulle dfn 
{ position:absolute; top:-2000em;  left:-2000em;  width:1px;  height:1px; 
 overflow:hidden; padding: 4px  6px ;  background:#DDEEFF;   border:1px solid #6699FF; } 
a.info_bulle 
{  color:#2F368A;   text-decoration:none;   padding:2px 16px 2px 2px;  
    background:transparent url('comment.gif') no-repeat right center;   position:relative;} 
a.info_bulle:hover 
{  border:0; /* ligne qui corrige le bug d'IE6 et < */ } 
a.info_bulle:hover dfn, a.info_bulle:focus dfn, a.info_bulle:active dfn 
{ top:auto;  left:auto;  width:220px; height:auto; overflow:visible; }  




<?php if ( !$modif || $modif_restreint){ echo '.lire_cellule_2 {background-color: #FAF6EF; }';} ; ?>

</style>
</head>



<body style="background-color: #FAF6EF;" >



<table class="lire_bordure" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr class="lire_cellule_4">
    <td  align="center">  <a href="evenement_liste.php" title="Retour &agrave; la liste"><img src="../images/even_planning.png" width="16" height="16" border="0" ></a>&nbsp; &nbsp; - &nbsp; &nbsp;  Planification d'un &eacute;v&egrave;nement  &nbsp; &nbsp; - &nbsp; &nbsp; <?php echo $_SESSION['identite'];?></td>
    <td width="58" align="center"><div align="right"><span align="right"> <a href="evenement_liste.php"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /> </a> </span> &nbsp;</div></td>
  </tr>
</table> 
<?php if ( $modif ) { ?>

    <div id="msg1"> </div> <!-- Important - Ne pas supprimer(ajax_niveau_even_acteur)--> 
	<div id="msg2"> </div> <!-- Important - Ne pas supprimer(ajax_niveau_even_dest)  --> 
      <form onLoad= "formfocus()" method="post"  name="form1" action="<?php echo $editFormAction;?>" onsubmit="return verifier()">
        <?php ;} else { ?>
      <form onLoad= "formfocus()" method="post"  name="form1" action="evenement_ajout.php"  onsubmit="return verifier()">
	  <?php };?>	
  <table class="lire_bordure" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
 
    <td valign="top" class="lire_cellule_2" style="font-size:11px;font-style:arial;"><table align="center" border="0" cellspacing="0" cellpadding="0">
             
                <tr>
                  <td valign="top">
                    <strong><br>
                    Classes concern&eacute;es </strong></p>
					
					
					
					<?php if ( $modif ) { ?>
		 <?php if($totalRows_Rsniv<>0){ ?>			
					<br/>
					<table border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
	<tr>
	<td class="Style6"><div align="center">Destinataires par niveaux&nbsp;&nbsp;</div></td>
	<td class="Style6">&nbsp;</td>
	</tr>
	<?php 
	do { ?>
		<tr>
		<td class="tab_detail"><?php echo $row_Rsniv['nom_niv']; ?></td>
		<td class="tab_detail"><div align="center">
          <input type="checkbox" name="<?php echo 'niv'.$row_Rsniv['ID_niv']; ?>"   id="<?php echo 'niv'.$row_Rsniv['ID_niv']; ?>" onclick=ShowNiveauActeur(<?php echo $row_Rsniv['ID_niv']; ?>,this.checked) value="on">

  </div></td>
		</tr>
	<?php } while ($row_Rsniv = mysqli_fetch_assoc($Rsniv)); ?>
	</table>
	
	<br/><?php };?>
                    <table border="0" align="center" class="bordure">
                      <tr>
                        <td class="Style6"><div align="center">Classe&nbsp;</div></td>
                        <td class="Style6"></td>
                        <td class="Style6">
						
						

<SCRIPT>
function cocherToutacteur(etat)
{
   for(var i=1; i<=<?php echo $totalRows_RsClasse?>; i++){
		var c= document.getElementById('classeven'+i);
		c.checked = etat ;  
		} 
}


function decocherToutacteur(n)
{
   var b = document.getElementById('tousacteur');
   var c = document.getElementById('classeven'+n);  
   var d = document.getElementById('classedest'+n); 
     if (c.checked ==true) {d.checked = true;} else {d.checked = false;b.checked = false};
}


</SCRIPT>



   <input type="checkbox" name="classeven" id="tousacteur" onclick="cocherToutacteur(this.checked)" value="ok" ></td>
                        <td class="Style6">Toutes </td>
                      </tr>
                      <?php for ($i=1; $i<=$totalRows_RsClasse; $i++) { ?>
                      <tr>
                        <td class="tab_detail"><div align="left"> <a href="../planning.php?classe_ID=	<?php echo $indcl_id[$i].'&date='.$mois_planning;?>" TARGET="_blank" title="Planning de la <?php echo $indcl_nom[$i];?>"
			> <?php echo $indcl_nom[$i] ; ?> </a> </div></td>
                        <td class="tab_detail"><div align="left"> <a href="../edt_eleve.php?classe_ID=<?php echo $indcl_id[$i].'&date1='.$date_edt.'&submit=Actualiser';?> " TARGET="_blank" title="EDT de la <?php echo $indcl_nom[$i];?>"> <img  src="../images/edt.gif" border="0" alt="Edt" ></a> </div></td>
                        <td class="tab_detail"><div align="center">
                          <input type="checkbox" name="<?php echo 'classeven'.$i; ?>"   id="<?php echo 'classeven'.$i; ?>" value="on" onClick="decocherToutacteur(<?php echo $i; ?>)"
							
							<?php $groupe_sel=0;
							do {
							
			  if ($indcl_id[$i]==$row_RsActeur['classe_ID']){echo  'checked';$groupe_sel=$row_RsActeur['groupe_ID'];};
			     } while ($row_RsActeur = mysqli_fetch_assoc($RsActeur));
			
				 ?>
							
							  >
							  <?php mysqli_data_seek($RsActeur, 0);?>
                        </div></td>
                        <td class="tab_detail"><select name="<?php echo 'groupeven'.$i; ?>" size="1" class="menu_deroulant" id="<?php echo 'groupeven'.$i; ?>">
                            <?php do {  ?>
                            <option value="<?php echo $row_Rsgroupe['ID_groupe']?>"
							<?php if ((isset($groupe_sel))&&($groupe_sel==$row_Rsgroupe['ID_groupe'] )) {echo ' selected';};?>	
							
							><?php echo $row_Rsgroupe['groupe']?></option>
                            <?php
						
                		} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
            $rows = mysqli_num_rows($Rsgroupe);
            if($rows > 0) {
            mysqli_data_seek($Rsgroupe, 0);
	        $row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
            }?>
                          </select>						  </td>
                      </tr>
                      <?php 
				  } ; ?>
                    </table>
 <?php }
 else {
 //read only 
 ?><p align="left">
 <?php
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsActeur="SELECT * FROM cdt_evenement_acteur,cdt_classe,cdt_groupe
			WHERE 	cdt_evenement_acteur.even_ID = ".$_GET['ID_even']."
					AND cdt_evenement_acteur.classe_ID = cdt_classe.ID_classe 
					AND cdt_evenement_acteur.groupe_ID = cdt_groupe.ID_groupe 
						
				ORDER BY nom_classe" ; 		
				
$RsActeur = mysqli_query($conn_cahier_de_texte, $query_RsActeur) or die(mysqli_error($conn_cahier_de_texte));
$row_RsActeur = mysqli_fetch_assoc($RsActeur);
$totalRows_RsActeur = mysqli_num_rows($RsActeur);
do {
			echo $row_RsActeur['nom_classe'].' - '.	$row_RsActeur['groupe'].'<br>';
	}
while ($row_RsActeur = mysqli_fetch_assoc($RsActeur));
 }
 
?>         
                    </p></td>
                </tr>
        </table>		</td>
      <td valign="top" class="lire_cellule_2"><br>
	  
	  <!--Tableau partie centrale **************************************************************************** -->
        <table border="0" align="center" cellpadding="0" cellspacing="0" class="lire_cellule_22">
        <tr class="lire_cellule_4">
          <td align="center" style="padding:0px 0px 0px 0px;"> &nbsp;&nbsp; &nbsp;  &nbsp;&nbsp; &nbsp;  &nbsp; <?php echo $row_RsModifMessage['titre_even']; ?> &nbsp;- &nbsp;&nbsp; &nbsp;&nbsp;(<?php echo $date_debut; ?>)&nbsp;  &nbsp;&nbsp; &nbsp;</td>
          <td ><div align="center"> <a href="evenement_pdf.php?ID_even=<?php echo $_GET['ID_even']; ?>" target="_blank"><img src="../images/pdf2.jpg" width="35" height="16" border="0" title="Obtenir la version pdf de la fiche"></a></div></td>
       </tr>

        <tr>
            <td>
        <tr>
            <!-- tout sauf la barre titre -->
            <td colspan="2" valign="top" class="lire_cellule_2" style="font-size:11px;" ><center>
            </center>
              <?php switch ($row_RsModifMessage['etat']) { // commun consultation / modification
					case "Validé": $color="palegreen";    break;
					case "attente": $color="gold";     break;
					case "à étudier":$color="darkorange" ;     break;
					case "Report":$color= "LightSalmon";     break;
					case "annulé":$color= "coral";     break;
					 default: $color="yellow" ; 
						} ;
					
if ( !$modif ) { // ==============================   ================================================
			
			?>
		<table width="100%" border="0" cellspacing="0" > 
			
			<tr>
			  <td><p align="left" style="margin-top:0px;" ></p>
			    <p align="left" style="margin-top:0px;" ><b>Titre de l'&eacute;v&egrave;nement</b><br>
                      <input name="text2" type="text" class="fige" value="<?php echo $row_RsModifMessage['titre_even']; ?>  "  size="44" readonly="readonly" >
                    </p>
			    <p align="left"><b> Domaine et modalit&eacute;s </b> <br>
                    <?php  
			   echo '<input readonly="readonly"  size="50"  class="fige"  type="text"  value="'.$theme[$row_RsModifMessage['code_theme']]; 
			   if ( $row_RsModifMessage['code_mode'] <> "" ){ echo ' &nbsp; - '.$mode[$row_RsModifMessage['code_mode']].' - ' ; } ; echo '" >' ;
			 ?>
                </p></td>
			  <td rowspan="5" valign="top" >
			  <p align="left"><br><a href="evenement_liste.php" >
			    <b><span align="right" style="background-color:#D8D8D8 ;padding:0.3em  1em;border:1px solid black; ;font-size:11.5px ;" >
				Retour &agrave; la liste</span></b></a></p>
			  <p align="left"><b>Etat du projet </b><br>
			        </p>
			      <p align="left"> <input readonly="readonly" class="fige"  name="etat" type="text" size="7" style="background-color: <?php echo $color;?>; font-size :14px ;" value="  <?php echo $row_RsModifMessage['etat'];?> " >
			    </p>
			  <p align="left">&nbsp;</p>
			  <p align="left"><b>R&eacute;dacteur  : </b> </p>
			  <p align="left"><span align="left" ><?php echo $row_RsModifMessage['identite'];?></span></p>
			<p align="left"><span align="left" >Fiche cr&eacute;&eacute;e</span></p>
			<p align="left"><span align="left" > le <?php echo ymd_dmy($row_RsModifMessage['date_crea']);?></span></p>
			
			  
			    <div align="left">
			      <?php if (substr($row_RsModifMessage['date_envoi'],0,4) <> '0000' ){echo '<p><span align="left"<i>valid&eacute;e  le  '.ymd_dmy($row_RsModifMessage['date_envoi']).'</i> </span></p>'; };?>
			      </div>
			    <p align="left"><span align="left" > Derni&egrave;re modification</span></p>
			<p align="left"><span align="left" > le <?php echo ymd_dmy($row_RsModifMessage['date_modif']) ; ?></span></p>
			<p align="left">&nbsp;</p>
	
	</td>
			</tr>
			<tr> <td><p align="left">
			 <b>Effectif </b> 
             <input readonly="readonly" class="fige"  style="text-align:center;"type="text"  size="2" align="right" 
					value="<?php echo $row_RsModifMessage['classes_eff']; ?>"> 
		     <b>- Participation par &eacute;l&egrave;ve </b>
		     <input name="text"   type="text" class="fige" 
						value="<?php echo $row_RsModifMessage['cout_elv']; ?>" size="3" readonly="readonly">
&euro;
<b> - Co&ucirc;t global</b>
<input name="text" type="text" class="fige"  style="text-align:center;"
						value="<?php echo $row_RsModifMessage['cout_glob']; ?>"  size="4" readonly="readonly" >
&euro;		
		        </td>
		      </tr>
		<tr> <td>
        <p align="left">
		<?php if ($date_fin==$date_debut) //sortie jour distingo debut/depart & fin/retour
	       { echo '<b>Date<span style="margin-left:122px; margin-right:22px">';
		     if ($row_RsModifMessage['code_mode']<= $surplace ) { echo 'D&eacute;but&nbsp;&nbsp;';} else{echo 'D&eacute;part';};
		     echo '</span>';
		     if ($row_RsModifMessage['code_mode']<= $surplace ) { echo 'Fin&nbsp;&nbsp;';} else { echo 'Retour';};
		   echo '</b><br>'; 
		   echo '<input readonly="readonly" class="fige" type="text" size="18" value="'.jour_dmy($date_debut).$date_debut.'"> &nbsp;&nbsp; ' ;
	       echo '<input readonly="readonly" class="fige" type="text" size="3" value="'.$row_RsModifMessage['heure_debut'].'"> &nbsp;&nbsp;';
		   echo '<input readonly="readonly" class="fige" type="text" size="3" value="'.$row_RsModifMessage['heure_fin'].'"> &nbsp;&nbsp;';
		   echo '&nbsp;&nbsp; (semaine '.$num_semaine.')';
		   }
		  else { // sur plusieurs jours ?> 
		   <b>Dates et heures de départ <span style="margin-left:62px;"> et de retour :</b><br>
        
          <input readonly="readonly" class="fige"   type='text' size="18" value="<?php echo jour_dmy($date_debut).' '.$date_debut ;?>" > &nbsp;
		  <input readonly="readonly" class="fige"   type='text' size="3" value="<?php echo ' '.$row_RsModifMessage['heure_debut'] ;?>" >
		  &nbsp;au&nbsp;&nbsp;<?php echo "" ;?>
          <input readonly="readonly" class="fige"   type='text' size="18" value="<?php echo jour_dmy($date_fin). ' '.$date_fin ;?>" > &nbsp;
		  <input readonly="readonly" class="fige"   type='text' size="3" value="<?php echo ' '.$row_RsModifMessage['heure_fin'];?>" > 
		  <?php echo '<br>&nbsp;&nbsp; ( semaine '.$num_semaine.' )';?>
		  
			<?php } ?>
        
            <b><i><?php if ($row_RsModifMessage['pb_dates']=="1"){ echo ' <span style=" padding : 4px ;background : yellow ;" > &nbsp;  ATTENTION : dates incertaines &agrave; confirmer ! </span></b></i> ' ; };  ?> </p>' 
			</td>
		   	</tr> 
            
			<tr><td valign="top"  class="tab_detail2" style="border:0px;">
				<p align="left"><b>Description de l'&eacute;v&egrave;nement </b><br>
				  ( 
				  <?php if($row_RsModifMessage['etat']=="Validé") {echo' Visible';}else { echo 'Pas encore visible';}; ?>
				  sur le cdt des &eacute;l&egrave;ves tant que la fiche n'est pas valid&eacute;e) <br>
				    <textarea rows="4"  cols="71"  readonly="readonly" class="fige"><?php echo $row_RsModifMessage['detail']; ?>
				  </textarea> 
			        </td>
			</tr>	
			
			<tr><td class="tab_detail2"style="border:0px;"> <div align="left"><b>Accompagnateurs - Encadrants </b> (non affich&eacute;  sur le cdt eleves)<br> 
			    <?php $text = explode( ";",trim($row_RsModifMessage['accompagnateurs']," \t\n\r\0\x0B")); ?>
			    <?php for ($i=0; $i<9; $i++) { 
					if (strlen( $text[$i]) >0) { $stylcel="fige";}else {$stylcel="masque";};
				?>
			    	<input readonly="readonly" class="<?php echo $stylcel;?>"  type="text"  size="27" value="<?php echo $text[$i]; ?> ">

				
				<?php if (($i==2)||($i==5)){echo '<br>';}; 
				
				};
				?>
			  </div></td>
			  </tr>

			<tr><td class="tab_detail2"style="border:0px;"> 
				<div align="left"><b>Détails d'organisation </b>(non affich&eacute;  sur le cdt eleves) <br>
				    <textarea  rows="5"  cols="71" height= "50"	readonly="readonly" class="fige" /><?php echo $row_RsModifMessage['details_sup']; ?></textarea> 
				  </div></td> <td valign="bottom">					  
				  <p align="left"><br><a href="evenement_liste.php" >
			    <b><span align="right" style="background-color:#D8D8D8 ;padding:0.3em  1em;border:1px solid black; ;font-size:11.5px ;" >
				Retour &agrave; la liste</span></b></a></p></td>
			  </tr>
			<tr>
			  <td colspan="2" class="tab_detail2"style="border:0px;"> </td>
			  </tr>
			</table>	
			
			
			
			
			
			
			
			
<?php			
			
			 }  
else { //=======  $modif=true, masque de modif saisie renseignements   avec  restriction $modif_restreint si projet déja validé ============== 

			if( $modif_restreint ) {$type_modif="restreint";} else {$type_modif="full";};
		echo '<input  type="hidden" name="modif" id="modif" value="'.$type_modif.'" />'; // pour validation des champs à modifier l:82 et l:104 ?>

?>
              <table align="left" >
                <tr>
                  <td colspan="3" class="saisie" ><i>Fiche cr&eacute;&eacute;e le <?php echo ymd_dmy($row_RsModifMessage['date_crea']) ;
			if (substr($row_RsModifMessage['date_envoi'],0,4) <> '0000' ){echo '&nbsp; et valid&eacute;e  le  '.ymd_dmy($row_RsModifMessage['date_envoi']) ; };
			echo '   -   Derni&egrave;re modification : '.ymd_dmy($row_RsModifMessage['date_modif']) ; 
			if ($_SESSION['droits'] ==1 ) { echo '<input  type="hidden" name="date_modif" value="'.$row_RsModifMessage['date_modif'].'" />' ; };
			?></i><br> </td>
                </tr>
                <tr>
                  <td colspan="3" class="saisie" ><b>Titre de l'&eacute;v&egrave;nement</b>
				   <?php if( $modif_restreint ) { echo '&nbsp;&nbsp; <i><a class="info_bulle" href="#"> <img  src="../images/lightbulb.png" border="0" style="width:18px; height:18px; vertical-align:bottom ;" ><dfn><img  src="../images/lightbulb.png" border="0" style="width:18px; height:18px; "> &nbsp; Si vous désirez modifier les <b>éléments de base </b>du projet <br>(zones fonc&eacute;es), <br>il faut repasser &agrave; l&#145;&eacutetat  "en projet", <br>et apr&egrave;s modifications, redemander une validation...<br><br> (Vous pouvez aussi demander &agrave; '.$valideur.' d\'effectuer directement ces modifications )	</dfn></a></i>' ;}; ?>	<br> <input name="titre_even" type="text"  id="titre_even" size="44"  
			<?php if( $modif_restreint ) { echo 'readonly="readonly" class="fige"' ;}; ?>  value="<?php echo $row_RsModifMessage['titre_even']; ?>" >                  			   </td>
                </tr>
                <tr>
                  <td colspan="2" class="saisie" >				  <strong>Modalit&eacute;s</strong></td>
                  <td class="saisie" ><b>Domaine</b></td>
                </tr>
                <tr>
                  <td class="saisie" >                      </td>
                  <td class="saisie" >              
                      <?php if( $modif_restreint ) 
					{
					?>
				
					<input type="text" size="30"  "readonly" class="fige" value="<?php echo $mode_resume[$row_RsModifMessage['code_mode']];?>">
					<?php ; 
					}
					
			      else {
				  
				  ?>
					   <select name="code_mode" id="code_mode" >
					   <?php 
						for ($c=0; $c<sizeof($mode) ; $c=$c+1) {?>
									<option value="<?php echo $c;?>" ; 
									<?php if ( $c == $row_RsModifMessage['code_mode']) { echo 'selected';} ; // fin si ?>
									><?php echo $mode[$c];?></option><?php } ;  // fin  for $c ?>
					  </select> &nbsp; 

					  
			  <?php }  ?></td>
                  <td class="saisie" >
				    <?php if( $modif_restreint ) 
					{
					?>
				  	<input type="text" size="30"  "readonly" class="fige" value="<?php echo $theme[$row_RsModifMessage['code_theme']];?>">
		<?php ; 
					}
					
			      else {
				  ?>
				  <select name="code_theme" id="code_theme" >
						<?php 
						for ($c=0; $c<sizeof($theme) ; $c=$c+1) { ?>
									<option value="<?php echo $c;?>"<?php  
									if ( $c == $row_RsModifMessage['code_theme']) { echo 'selected';} ; // fin si ?>
									><?php echo $theme[$c];?></option><?php  } ;  // fin  for $c ?>
				    </select> 	  <?php }  ?></td>
                </tr>
                <tr>
                  <td colspan="3" class="saisie" ></td>
                </tr>
                <tr>
                  <td colspan="3">
				
				<?php if( $modif_restreint ) { 
				
				echo '<input  type="hidden" name="date1" value="'.$date_debut.'" />'; // pour mail modif horaire			  
			if ($date_fin==$date_debut)  //sortie jour distingo debut/depart & fin/retour en restreint
				{ if ($row_RsModifMessage['code_mode']< 4 ) 
					{ echo '<b>Date <span style="margin-left:120px; margin-right:38px;"> heure de début </span> heure de fin </b> <br>'; }
				else{ echo'<b>Date <span style="margin-left:120px; margin-right:38px;"> heure de départ </span>  heure de retour </b><br>';  } }
			else { // sur plusieurs jours en restreint
					echo ' <b>Dates et heures de départ <span style="margin-left:120px;"> et de retour :</b><br>' ; } 
					?>
			<input readonly="readonly" class="fige" type="text" size="18" value="<?php echo jour_dmy($date_debut).$date_debut;?>"> &nbsp;&nbsp;		
				<?php }
	else { ?>
                    <b><span style="margin-left:90px; margin-right:150px"> Dates et heures de départ et de retour :</span></b><br>
                    <input name='date1' type='text' id='date1' value="<?php echo $date_debut;?>" size="10"/>
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
                    <?php } ;	?>
                    <select name="heure_debut_h" id="heure_debut_h" >
                      <?php for ($i=0; $i<$nb_heures; $i=$i+1) { 				   
					echo '<option value="'.$choix_heures[$i].'"' ; 
				   if ($hhd==$choix_heures[$i] ) { echo 'selected';} ;
					echo  '>'.$choix_heures[$i].'h </option> ' ; } ;?>
                    </select>
                    <select name="heure_debut_min" size="1" id="heure_debut_min" >
                      <?php for ($i=0; $i<$nb_min; $i=$i+1) { 				   
					echo '<option value="'.$choix_min[$i].'"' ; 
				   if ($mmd==$choix_min[$i] ) { echo 'selected';} ;
					echo  '>'.$choix_min[$i].'min </option> ' ; } ;?>
                    </select>
                    &nbsp; &nbsp; &nbsp;
                    <?php if($modif_restreint ) {
			if($date_fin <> $date_debut) {
			echo '<input readonly="readonly" class="fige"  type="text" size="18" value="'.jour_dmy($date_fin).' '.$date_fin .'" />';
			echo '<input  type="hidden" name="date2" id="date2" value="'.$date_fin.'" />';  } ; }
			
		else { echo '<input name="date2" type="text" id="date2" value="'.$date_fin.'" size="10"/>' ;};   ?>
                    <select name="heure_fin_h" id="heure_fin_h" >
                      <?php for ($i=0; $i<$nb_heures; $i=$i+1) { 				   
					echo '<option value="'.$choix_heures[$i].'"' ; 
				   if ($hhf==$choix_heures[$i] ) { echo 'selected';} ;
					echo  '>'.$choix_heures[$i].'h </option> ' ; } ;?>
                    </select>
                    <select name="heure_fin_min" size="1" id="heure_fin_min" >
                      <?php for ($i=0; $i<$nb_min; $i=$i+1) { 				   
					echo '<option value="'.$choix_min[$i].'"' ; 
				   if ($mmf==$choix_min[$i] ) { echo 'selected';} ;
					echo  '>'.$choix_min[$i].'min </option> ' ; } ;?>
                    </select>
                 
                    <br>
                    <?php echo '&nbsp;&nbsp;&nbsp;&nbsp; ( '.$date_debut.'&nbsp;:&nbsp; Semaine '.$num_semaine.' )';  ?> &nbsp;&nbsp;&nbsp;<span >
                    <?php if( $modif_restreint ) { echo ' <i> Si changements d&#145;horaires,  cochez :</i> <INPUT type="checkbox" style="width:18px; height:18px; style="vertical-align:middle ;"  name="modif_horaires" value="oui"> <a class="info_bulle" href=""> <img  src="../images/lightbulb.png" border="0" width="18" height="18" vertical-align= "top"  ><dfn> <img  src="../images/lightbulb.png" border="0" width="18" height="18" vertical-align= "middle"  > &nbsp; Cochez ici pour que les modifications d&#145;horaires soient prises en compte,  <br> (et qu&#145;en même temps le cpe soit inform&eacute; par mail), <br><br>puis cliquez sur <br> &nbsp;[Enregistrer les modifications].</dfn></a> ' ; } 
										else { echo '<input type="hidden" name="modif_horaires" value="non"> ' ; } ; ?>
                    </span> </td>
                </tr>
                <tr>
                  <td colspan="3" valign="top" class="lire_cellule_2"  style="font-size:11px;padding-left:0px;">
				 
				  <?php if ($row_RsModifMessage['pb_dates']=="1"){ ?>
                      <span style =" padding : 4px ;background : yellow ; ">Signaler que ces dates sont encore incertaines</span>
                    <?php } else{ echo '<span>Dates incertaines </span>' ; } ; ?>    
					<input type="checkbox" style="width:18px; height:18px;vertical-align:middle ;" name="pb_dates"   id="pb_dates" value="1" onclick=decocherTout()   
					<?php if ($row_RsModifMessage['pb_dates']=="1"){echo  'checked';} ;?>>                 				  
					</td>
                </tr>
                <tr>
                  <td colspan="3" valign="top" class="lire_cellule_2"  style="font-size:11px;padding-left:0px;"><p align="center"><b>Description de l'&eacute;v&egrave;nement </b><br>
                      ( visible sur le cdt &eacute;leves si le projet sera validé, <b>et si </b> vous cochez leur classe dans le tableau de droite ) <br>
                      <textarea name="detail" rows="5" id="detail" cols="71" height= "50" 
					 text-align:left; ><?php echo $row_RsModifMessage['detail']; ?></textarea></p>
                    </td>
                </tr>
                <tr>
                  <td colspan="3" valign="top" class="lire_cellule_2"  style="font-size:11px;padding-left:0px;"><div align="center"><span class="lire_cellule_2" style="font-size:11px;padding-left:0px;">&nbsp; &nbsp; &nbsp;&nbsp;<b>Effectif </b>&nbsp; &nbsp; &nbsp;<b>   Participation par &eacute;l&egrave;ve </b>&nbsp; &nbsp; &nbsp;<b>Co&ucirc;t global</b></span></div></td>
                </tr>
                <tr>
                  <td colspan="3" valign="top" class="lire_cellule_2"  style="font-size:11px;padding-left:0px;">

                        <div align="center">
  <input name="classes_eff" type="text" style="text-align:center; " id="classes_eff" size="3" value="<?php echo $row_RsModifMessage['classes_eff']; ?>">
  &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;
                          <input name="cout_elv" style="text-align:center;" type="number" id="cout_elv" size="3" value="<?php echo $row_RsModifMessage['cout_elv']; ?>">
  &euro;   
                          &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp;                    
                          <input name="cout_glob" style="text-align:center;" type="number" id="cout_glob" size="4" value="<?php echo $row_RsModifMessage['cout_glob']; ?>"> 
                      &euro;					  </div></td>
                </tr>
				
                <tr>
                  <td colspan="3" class="lire_cellule_2" style="font-size:11px;padding-left:0px;" ><div align="center"><b>Accompagnateurs </b>(non affich&eacute;  sur le cdt des &eacute;l&egrave;ves)  &nbsp; &nbsp;<br>
                      <?php $text = explode( ";", $row_RsModifMessage['accompagnateurs'] ) ; // trim($row_RsModifMessage['accompagnateurs']," \t\n\r\0\x0B")); 
				?>
                      <?php for ($i=0; $i<9 ; $i++) { ?>
                      <input type="text" name="acc<?php echo $i+1; ?>" id="acc<?php echo $i+1; ?>" size="27" value="<?php echo $text[$i]; ?>">
                      <?php 
					  if (($i==2)||($i==5)){echo '</br>';};
					  } ; ?>
                      <br>                  
                    </div></td>
                </tr>
                <tr>
                  <td colspan="3" class="lire_cellule_2" style="font-size:11px ; padding-left:0px;" ><div align="center"><br>
                      <b>Détails d'organisation </b>(non affich&eacute;  sur le cdt eleves)<br>
                      <textarea name="details_sup" rows="5" id="details_sup" cols="71"
					 height= "50" ><?php echo $row_RsModifMessage['details_sup']; ?></textarea>                  
                    <br>                    
                  </div></td>
                </tr>
                <tr>
                  <td colspan="3" class="lire_cellule_2" style="font-size:11px ; padding-left:0px;" >
				  <p align="center">
                      <select name="etat" style="background-color:<?php echo $color;?>;" >
                        <option  value="<?php echo $row_RsModifMessage['etat'];?>"><?php echo $row_RsModifMessage['etat']; ?>
                        <selected>
                        </option>
                        <option value="projet" style="background-color:white ;" >en projet</option>
                        <option value="&agrave; valider" style="background-color:LightSkyBlue ;" >projet &agrave; valider </option>
                        <?php if ( ($_SESSION['droits']==1) || ($_SESSION['droits']==4 ) || ( $valideur =="" ) ) 
				{ // droits pour admin à enlever ? ?>
                        <option value="Valid&eacute;" style="background-color:aquamarine ;" >Valider le projet</option>
                        <option value="&agrave; &eacute;tudier" style="background-color:yellow;" >projet &agrave; (re)&eacute;tudier</option>
                        <?php } ; ?>
                        <option value="Report" style="background-color:LightSalmon;" >projet report&eacute; </option>
                        <option value="annul&eacute;" style="background-color:Coral;" >projet annul&eacute; </option>
                      </select>
                                   <input name="submit" type="submit" value="Enregistrer les modifications">
              <input type="hidden" name="nb_classes" value="<?php echo $totalRows_RsClasse;?>">
              <input type="hidden" name="MM_update" value="form1">
              <input type="hidden" name="redacteur" value="<?php echo $row_RsModifMessage['prof_ID'] ; ?>">
                      <br>
					  
                      <?php if ( $row_RsModifMessage['etat']=="annulé" )
				{ echo 'Supprimer <br><u>d&eacute;finitivement </u>cette fiche ? &nbsp; &nbsp; <a href="evenement_supprime.php?ID_even='.$row_RsModifMessage['ID_even'].'"> <img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="16" height="16" align="top"> </a>' ;}
			else {  echo '<a class="info_bulle" href=""> <img  src="../images/info2.jpg" border="0" width="18" height="18" vertical-align= "middle"  ><dfn> <img  src="../images/info2.jpg" border="0" width="18" height="18" vertical-align= "middle"  ><i> Si "projet annul&eacute;" est choisi, <br>le rédacteur pourra supprimer la fiche &agrave la prochaine &eacutedition.... </i></dfn></a>' ; } ; ?>
                      <?php if (($valideur <>"" ) && ($row_RsModifMessage['etat']<>"annulé") &&( !$modif_restreint ) && ($_SESSION['droits']<>4 ))
							{ echo '<p><INPUT type="checkbox" style="width:18px; height:18px;vertical-align:bottom ;" name="mail" value="oui"> Envoyer une demande de validation &agrave; '.$valideur.' par mail </p>' ; } else { echo '<input type="hidden" name="mail" value="non"> ' ; } ; ?>
                      <input type="hidden" name="etat0" value="<?php echo $row_RsModifMessage['etat']; ?>">
                      <br>
                      <!-- pour suivre les demandes de validation -->
                    L'&eacute;v&egrave;nement n'apparaitra dans le CDT des &eacute;l&egrave;ves<b> qu'une fois le projet valid&eacute;</b>.</p>				  </td>
                </tr>
                <tr>
                  <td colspan="3" class="lire_cellule_2" style="font-size:11px ; padding-left:0px;" ></td>
                </tr>
              </table>
            <?php } ; // =============================================================================================?>            
			</td>
      </tr>
          <?php if ( $modif ) { ?>

  <?php }; ?>
</table>
<br>
<?php if ($modif){?>
<div align="center"><span style="background-color:#D8D8D8 ;padding:0.3em.9em;border:1px solid black;font-size:13px ;"> <a href="evenement_liste.php">&nbsp; Retourner &agrave; la liste sans enregistrer  &nbsp;</a> </span></div>
<?php }?>
<!-------------------------------------------------------------------------------------------------------------------------------------------------------------------!>					  
			  <!--Fin Tableau partie centrale -->
      <td valign="top" class="lire_cellule_2" style="font-size:11px;font-style:arial;">
			
			
			
			<!--tableau destinataire -->

<table align="center" border="0" cellspacing="0" cellpadding="0">
                <!--521 > 561 -->
                <tr>
                  <td valign="top">
                    <p><strong>Information diffus&eacute;e vers </strong>:</p>
<?php if ( $modif ) { ?>	

				
                   
<?php if($totalRows_Rsniv<>0){ ?>
                      <br/>
                      <table border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
                        <tr>
                          <td class="Style6"><div align="center">Destinataires par niveaux&nbsp;&nbsp;</div></td>
                          <td class="Style6">&nbsp;</td>
                        </tr>
                        <?php 
					     mysqli_data_seek($Rsniv, 0);
					while ($row_Rsniv = mysqli_fetch_assoc($Rsniv)) {?>
                        <tr>
                          <td class="tab_detail"><?php echo $row_Rsniv['nom_niv']; ?></td>
                          <td class="tab_detail">
							  <div align="center">
                              <input type="checkbox" name="<?php echo 'niv'.$row_Rsniv['ID_niv']; ?>"   id="<?php echo 'niv'.$row_Rsniv['ID_niv']; ?>" onclick=ShowNiveauDest(<?php echo $row_Rsniv['ID_niv']; ?>,this.checked) value="on">
                              
                            </div></td>
                        </tr>
                        <?php } ; ?>
                      </table>
                      <br/>
                      <?php };?>		   
				   
			   
				    <table border="0" align="center" class="bordure">
                      <tr>
                        <td class="Style6"><div align="center">Classe&nbsp;</div></td>
                        <td class="Style6">
						
						
<SCRIPT>

function cocherToutdestinataire(etat)
{
   for(var i=1; i<=<?php echo $totalRows_RsClasse?>; i++){
		var d= document.getElementById('classedest'+i);
		d.checked = etat ;  
		} 
}


function decocherToutdestinataire(n)
{
   var b = document.getElementById('tousdest');
   
   var d = document.getElementById('classedest'+n); 
     if (d.checked ==false) {
	 b.checked = false;
	 } 
     
}
</SCRIPT>
                     <input type="checkbox" name="classedest" id="tousdest" onclick= "cocherToutdestinataire(this.checked)" value="ok" ></td>
                        <td class="Sty le6">Toutes </td>
                      </tr>
                      <?php for ($i=1; $i<=$totalRows_RsClasse; $i++) { ?>
                      <tr>
                        <td class="tab_detail"><div align="left"> <a href="../planning.php?classe_ID=	<?php echo $indcl_id[$i].'&date='.$mois_planning;?>" TARGET="_blank" title="Planning de la <?php echo $indcl_nom[$i];?>"
			> <?php echo $indcl_nom[$i] ; ?> </a> </div></td>
                        <td class="tab_detail"><div align="center">
                            <input type="checkbox" name="<?php echo 'classedest'.$i; ?>"   id="<?php echo 'classedest'.$i; ?>" value="on" onclick="decocherToutdestinataire()" 
							
											  <?php 
											  $groupe_sel=0;
											  do { 
			  if ($indcl_id[$i]==$row_Rsdest['classe_ID']){echo  ' checked';$groupe_sel=$row_Rsdest['groupe_ID'];};
			     } while ($row_Rsdest = mysqli_fetch_assoc($Rsdest));
            $rows = mysqli_num_rows($Rsdest);
            if($rows > 0) {				 
			mysqli_data_seek($Rsdest, 0);
			$row_Rsdest = mysqli_fetch_assoc($Rsdest);
			};
				 ?>
				 >
                          </div></td>
                        <td class="tab_detail"><select name="<?php echo 'groupedest'.$i; ?>" size="1" class="menu_deroulant" id="<?php echo 'groupedest'.$i; ?>">
                            <?php do {  ?>
                            <option value="<?php echo $row_Rsgroupe['ID_groupe']?>"
							<?php if ((isset($groupe_sel))&&($groupe_sel==$row_Rsgroupe['ID_groupe'] )) {echo ' selected';};?>	
							
							><?php echo $row_Rsgroupe['groupe']?></option>
							
							

                            
							
							
							<?php
						
                		} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
            $rows = mysqli_num_rows($Rsgroupe);
            if($rows > 0) {
            mysqli_data_seek($Rsgroupe, 0);
	        $row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
            };?>
                          </select>                        </td>
                      </tr>
                      <?php 
				  } ; ?>
                    </table>
 <?php }
 
 else
 {

 //read only
 ?>
 <p align="left">
 <?php 
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsDest="SELECT * FROM cdt_evenement_destinataire,cdt_classe,cdt_groupe
			WHERE 	cdt_evenement_destinataire.even_ID = ".$_GET['ID_even']."
					AND cdt_evenement_destinataire.classe_ID = cdt_classe.ID_classe 
					AND cdt_evenement_destinataire.groupe_ID = cdt_groupe.ID_groupe 
						
				ORDER BY nom_classe" ; 		
				
$RsDest = mysqli_query($conn_cahier_de_texte, $query_RsDest) or die(mysqli_error($conn_cahier_de_texte));
$row_RsDest = mysqli_fetch_assoc($RsDest);
$totalRows_RsDest = mysqli_num_rows($RsDest);
do {
			echo $row_RsDest['nom_classe'].' - '.	$row_RsDest['groupe'].'<br>';
	}
while ($row_RsDest = mysqli_fetch_assoc($RsDest));
 
 
 };?>
                
                     </p></td>
                  <!-- fin zone selection classe -->
                </tr>
      </table>



  <!-- fin tableau destinataire-->			</td>
  </tr>
</table>
  </form>  
</body>
</html>
<?php

if( isset($_POST['date1']) and  isset($_POST['date2']	) ) {mysqli_free_result($Rsmessage) or die(mysqli_error($conn_cahier_de_texte)); } ;
mysqli_free_result($RsClasse);
mysqli_free_result($Rsgroupe);
mysqli_free_result($Rsniv);
?>
