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
}
$datetoday=date('y-m-d');
$date1=date('Ymd');
$date2=date('Ymd');

$date1_form=substr($date1,6,2).'/'.substr($date1,4,2).'/'.substr($date1,0,4);
$date2_form=substr($date2,6,2).'/'.substr($date2,4,2).'/'.substr($date2,0,4);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsniv = "SELECT * FROM cdt_niveau ORDER BY nom_niv";
$Rsniv = mysqli_query($conn_cahier_de_texte, $query_Rsniv) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsniv = mysqli_fetch_assoc($Rsniv);
$totalRows_Rsniv = mysqli_num_rows($Rsniv);



$i=1;
do
{
$indcl_id[$i]=$row_RsClasse['ID_classe'];
$indcl_nom[$i]=$row_RsClasse['nom_classe'];

$indcl_id_even[$i]=$row_RsClasse['ID_classe'];
$indcl_nom_even[$i]=$row_RsClasse['nom_classe'];
$i=$i+1;
}while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)) ;



mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);

$totalRows_Rsmessage=0;


// y a til un valideur
$valideur=''; //son nom
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='even_nom_valid_mail' LIMIT 1";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$valideur = $row[0];
mysqli_free_result($result_read);

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "ok") && (isset($_POST['titre_even'])))
 {


$date1=substr($_POST['date1'],6,4).'-'.substr($_POST['date1'],3,2).'-'.substr($_POST['date1'],0,2);   // yyyy-dd-jj
$date2=substr($_POST['date2'],6,4).'-'.substr($_POST['date2'],3,2).'-'.substr($_POST['date2'],0,2);
$heure1=$_POST['heure_debut_h'].'h'.$_POST['heure_debut_min'];
$heure2=$_POST['heure_fin_h'].'h'.$_POST['heure_fin_min'];

$accompagnateurs= 	strtoupper($_POST['acc1']).';'.strtoupper( $_POST['acc2'] ).';'.strtoupper($_POST['acc3']).';'.
					strtoupper($_POST['acc4']).';'.strtoupper( $_POST['acc5'] ).';'.strtoupper($_POST['acc6']).';'.
					strtoupper($_POST['acc7']).';'.strtoupper( $_POST['acc8'] ).';'.strtoupper($_POST['acc9']).';' ;
				



//on enregistre la fiche evenement

$titre_even= prem_maj(  str_replace( "\"", "&quot;", $_POST['titre_even']) ) ;  // pour  maj en debut et eliminer le pb des " dans charmap

if ( (isset($_POST['mail'])) &&($_POST['mail'] =="oui") && ( $_POST['etat'] =="à valider" ) )   {$etat ="attente" ;} else { $etat = $_POST['etat'] ;} ;
if (isset( $_POST['pb_dates'])) {$pb_dates = $_POST['pb_dates'] ;} else { $pb_dates = "0" ; };  
   
  $insertSQL = sprintf(" INSERT INTO `cdt_evenement_contenu` ( `titre_even`,`code_mode`,`code_theme`,`detail`,`etat`,`prof_ID` ,
					`classes_eff`, `cout_elv`,`cout_glob`,`details_sup`, `accompagnateurs`,
					`date_debut` ,heure_debut,date_fin,heure_fin,pb_dates,date_crea,date_modif )
					VALUES (%s,%u,%u,%s,%s,%u,%u,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
  GetSQLValueString($titre_even, "text"), 
  GetSQLValueString($_POST['code_mode'], "int"),
  GetSQLValueString($_POST['code_theme'], "int"),
  GetSQLValueString( $_POST['description'] , "text"),
  GetSQLValueString($etat, "text"),
  GetSQLValueString($_SESSION['ID_prof'], "int"),
  GetSQLValueString($_POST['classes_eff'], "int"),
  GetSQLValueString($_POST['cout_elv'], "text"),
  GetSQLValueString($_POST['cout_glob'], "text"),
  GetSQLValueString($_POST['details_sup'], "text"),
  GetSQLValueString($accompagnateurs, "text"),
  GetSQLValueString($date1, "text"),
  GetSQLValueString($heure1, "text"),
  GetSQLValueString($date2, "text"),
  GetSQLValueString($heure2, "text"),
  GetSQLValueString($pb_dates, "text"),
  GetSQLValueString($datetoday, "text"),
  GetSQLValueString($datetoday, "text")
 );
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));

$UID=mysqli_insert_id($conn_cahier_de_texte); 
$validation= ( $_POST['etat']=="Validé") ; // etat0 inexistant ici  !  


// ceux sont concerne par l'evenement
$classes_conc='';
$nb_conc=1;
for ($i=1; $i<=$totalRows_RsClasse; $i++) { 
  $refclasseven='classeven'.$i;
  $refgroupeven='groupeven'.$i;

	if (isset($_POST[$refclasseven])&&(isset($_POST[$refgroupeven])) &&($_POST[$refclasseven]=='on'))
	{
		$insertSQL2= sprintf("INSERT INTO `cdt_evenement_acteur` ( `even_ID` , `classe_ID` , `groupe_ID`  )  VALUES ('%u', '%u','%u');",$UID,$indcl_id_even[$i], $_POST[$refgroupeven]);
		$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));
	//creer la chaine conc
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsConc = "SELECT nom_classe,code_classe FROM cdt_classe WHERE ID_classe =".$indcl_id_even[$i];
	$RsConc = mysqli_query($conn_cahier_de_texte, $query_RsConc) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsConc = mysqli_fetch_assoc($RsConc);
	$totalRows_RsConc = mysqli_num_rows($RsConc);
	if ($row_RsConc["code_classe"]==''){$classe_elem=substr($row_RsConc["nom_classe"],0,5);} 
	else {$classe_elem=substr($row_RsConc["code_classe"],0,5);};
	if ($nb_conc<6){$classes_conc.=$classe_elem.' ';};
	if ($nb_conc==6){$classes_conc.='...';};	
	if ($nb_conc==$totalRows_RsClasse){$classes_conc='Tous';};
	$nb_conc=$nb_conc+1;
	//fin chaine conc	
		
		}//du if
}//du for

//inserer la conc
$insertSQL = sprintf(" UPDATE `cdt_evenement_contenu` SET   classes_conc=%s WHERE ID_even=%u ",  GetSQLValueString($classes_conc, "text"),$UID);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));


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


$classes_conc='';
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
			//echo $row_RsActeur['nom_classe'].' - '.	$row_RsActeur['groupe'].'<br>';
			$classes_conc.=$row_RsActeur['nom_classe'].' - '.	$row_RsActeur['groupe']."\n";
	}
while ($row_RsActeur = mysqli_fetch_assoc($RsActeur));


$valideur_mail=''; //son adresse mail
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='even_adr_valid_mail' LIMIT 1";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$valideur_mail = $row[0];
mysqli_free_result($result_read);



//envoi d'un mail  soit au valideur ( demande de validation ) //  soit aux -autres- resp (demande validée) 
if (  ( (isset($_POST['mail'])) &&($_POST['mail'] =="oui") && ( $valideur > "" ) ) ||  $validation ) 
{
$entete  = 'From: '.noAccents($_SESSION['identite']).'<';
if (isset($_SESSION['email'])){$entete.= $_SESSION['email'];};
$entete.='>'."\r\n"; 
// $subject =  str_replace( '"',' ' ,noAccents($titre_even)).'    ('.$_POST['date1'].')' ; // à =E0 é =E8 è =E9
// $headers .='Content-Type: text/html; charset="UTF-8"'."\n";  $headers .='Content-Transfer-Encoding: 8bit'; 
$subject = utf8_decode( $titre_even.'    ('.$_POST['date1'].')');
$subject= mb_encode_mimeheader($subject,"UTF-8", "B", "\n"); 
$message =	
	'Titre : '.noAccents($_POST['titre_even']).'   - '.$_POST['etat'].' -'."\n"."\n".
	'Description : '.noAccents($_POST['description'])."\n"."\n". 
	'Classes : '."\n"."\n".$classes_conc."\n"."\n".
	'Accompagnateurs : '.str_replace(';','  ',noAccents($accompagnateurs))."\n"."\n".
	'Envoyé le : '.date("d/m/Y").'  à '.date("H").'h '.date("i")."\n"."\n" ;

			//on recupère l'adresse du site jusqu'a la racide du cdt
			//$http_site = (url_cdt());
			//lien vers la fiche - mais probleme securite - a etudier
	        //$message.=$http_site.'enseignant/evenement_fiche.php?ID_even='.$UID.'&mod=edit';
			
if ($_POST['mail']=="oui" )
 { 
	 
	 mail($valideur_mail,'Action a valider :  '.$subject, $message , $entete);}
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
			
			mail($liste_mail[$i], 'Action validee :  '.$subject, $message, $entete) ;} ;
	  } ;
}





 // fin d'enregistrement nouvel éven  


// on va ensuite afficher les risques de surbooking


$mois_planning=date('Ym').'01' ;   //  pour le pointage vers les plannings-classes
//affichage des even sur la même plage
$datemini=substr($date1,0,4).substr($date1,5,2).substr($date1,8,2);  // conversion en yyyy-mm-jj en yyyymmjj pour sql
$datemaxi=substr($date2,0,4).substr($date2,5,2).substr($date2,8,2);


$date_edt= date("d/m/Y");// pour afficher edt





if( isset($_POST['date1']) and  isset($_POST['date2']	) )
{
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsmessage="SELECT * FROM cdt_evenement_contenu,cdt_prof 
			WHERE (   (cdt_evenement_contenu.prof_ID = cdt_prof.ID_prof )
					AND  ( ( ( date_debut >= {$datemini} ) AND ( date_debut<={$datemaxi} ) )
								OR ( ( date_fin >= {$datemini} )   AND  (date_fin <= {$datemaxi} ) )
								OR ( ( date_debut <= {$datemini} ) AND  (date_fin >= {$datemaxi} ) ) ) )	
				ORDER BY date_debut , heure_debut " ; 
$Rsmessage = mysqli_query($conn_cahier_de_texte, $query_Rsmessage) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmessage = mysqli_fetch_assoc($Rsmessage);
$totalRows_Rsmessage = mysqli_num_rows($Rsmessage);
};

} // fin enregistrement nouvelle fiche









$mois_planning=date('Ym').'01' ;   //  pour le pointage vers les plannings-classes
//affichage des even sur la même plage
$datemini=substr($date1,0,4).substr($date1,5,2).substr($date1,8,2);  // conversion en yyyy-mm-jj en yyyymmjj pour sql
$datemaxi=substr($date2,0,4).substr($date2,5,2).substr($date2,8,2);

$date_edt= date("d/m/Y");// pour afficher edt
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes -<?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../styles/style_default.css" rel="stylesheet" type="text/css">
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />
<link type="text/css" href="../styles/jquery.autocomplete.css" rel="stylesheet"  />
<style>
form{   margin:5;   padding:0;}
.bordure_grise {	border: 1px solid #CCCCCC;}

.lire_cellule_2 { padding-left: 5px ;}
.lire_cellule_2 input { margin-top: 3px ; padding: 2px  4px ;}
.lire_cellule_2 textarea{ margin-top: 3px ;padding: 4px ;}

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

.grise {background-color:#C4D1D5 ;   //C0D4DA}
a.info_bulle1 {color:#2F368A;   text-decoration:none;   padding:2px 16px 2px 2px;  
    background:transparent url('comment.gif') no-repeat right center;   position:relative;}
</style>
<script type="text/JavaScript">
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}


function verifier() {
  //var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
   $test=false;
   for ($i=1; $i<=<?php echo $totalRows_RsClasse;?>; $i++)
   {
    $choix=document.getElementById('classeven'+$i).checked;
    if ($choix==true)    {      $test=true;  }
   }
   if ($test==true)   {
   document.getElementById('form1').submit();
   }else{  alert("Vous devez sélectionner au moins une classe. ");
   return false;
  } 
  
  
 if(document.form1.titre_even.value == "") 
		{ alert ('Donner un titre \340 votre \351v\351nement'); 
         return false; } 
 
 if(document.form1.code_mode.value < "1" ) 
 { alert("Précisez les modalités (sur place ?  sortie ? ");
   return false;  } 
   
  if(document.form1.code_theme.value < "1" ) 
 { alert("Indiquez un theme  ");
   return false;  } 
 

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
  
 
}

	


function disableInput(idInput, valeur)
{var input = document.getElementById(idInput);
input.disabled = valeur;
if (valeur) {input.style.background = "#CCC";
BSajoute(idInput);}
	else {input.style.background = "#FFF"; BSsuppr(idInput);}}

function BSajoute(idInput)
{for (var i = 0; i < tableauBS.length; i++) {if (tableauBS[i] == idInput) {return;}}
tableauBS.push(idInput);
}

	
	
/**
* A appeler dans le onsubmit du form pour que
* les champs puissent transmettre leurs valeurs
*/
function activeBeforeSubmit()
{while (tableauBS.length > 0) {var idInput = tableauBS.pop();var input = document.getElementById(idInput);
input.disabled = false;}}



function fermer(){
		var obj_window = window.open('', '_self');
		obj_window.opener = window;
		obj_window.focus();
		opener=self;
		self.close();
	}



</script>
</script>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>
<script type="text/javascript" src="../jscripts/jquery.autocomplete.js"></script>
</head>

<body style="background-color: #FAF6EF;" >

<div id="">
  <table class="lire_bordure" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
   
    <tr class="lire_cellule_4">
    
      <td width="1574" align="center"><img src="../images/even_planning.png" width="16" height="16" border="0" >&nbsp; &nbsp; - &nbsp; &nbsp;  Planification d'un &eacute;v&egrave;nement  &nbsp; &nbsp; - &nbsp; &nbsp; <?php echo $_SESSION['identite'];?></td>
      <td width="58" align="center"><div align="right"><span align="right"> <a href="evenement_liste.php"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /> </a> </span> &nbsp;</div></td>
    </tr>
    <tr>
      <td colspan="2" valign="top" class="lire_cellule_2" style="font-size:11px;font-style:arial;"><?php if ( $totalRows_Rsmessage <>0){  //==========partie  retour de saisie, pour  bilan des actions meme periode    ?>
        <br>
        <table width="90%" border="1" align="center" cellpadding="0" cellspacing="0" class="bordure" style="background-color:#FAF6EF; font-size:11px; " >
          <tr>
            <td colspan="9" align="center" style="padding:5px;" ><?php   if ( $totalRows_Rsmessage==1 ) { echo 'Voici le r&eacute;sum&eacute; de l&#145;action programm&eacutee '  ; }
												else { echo ' Voici le bilan des <b>'.$totalRows_Rsmessage.'</b> actions programm&eacutees <br>' ; } ; ?>
              <?php if( $date1==$date2 ) { echo 'le '.$_POST['date1'].'<br>' ;} 
													else { echo ' entre le '.$_POST['date1'] .' et le '.$_POST['date2'].'<br>' ;} ; ?>
              <br>
              <i>
              <?php  if ( $totalRows_Rsmessage==1 ) { echo 'Pas d&#145;autres actions pr&eacute;vues pour l&#145;instant cette p&eacute;riode'; }			else { echo 'A vous de v&eacute;rifier qu&#145;il  n&#145;y a pas d&#145;interf&eacute;rences entre ces actions....' ; } ; ?>
              </i> <br>
            </td>
          </tr>
          <tr>
            <td class="Style6">Date</td>
            <td class="Style6"> D&eacute;but </td>
            <td class="Style6"><div align="center">Classes</div></td>
            <td class="Style6"><div align="center">Ev&eacute;nement</div></td>
            <td class="Style6"> Fin </td>
            <td class="Style6" > Accompagnateurs </td>
            <td class="Style6">R&eacute;dacteur</td>
            <td colspan="2" class="Style6">Etat</td>
          </tr>
          <?php do { 
				if ($row_Rsmessage['etat']=="Valid&eacute;") { echo ' <tr class="tab_detail_gris_clair" ; >' ;} else {echo '<tr>';} ; ?>
          <td class="tab_detail2"><?php echo substr($row_Rsmessage['date_debut'],8,2).'-'.substr($row_Rsmessage['date_debut'],5,2).'-'.substr($row_Rsmessage['date_debut'],0,4); ?></td>
            <td class="tab_detail2"><?php echo $row_Rsmessage['heure_debut']==$row_Rsmessage['heure_fin']?"--":$row_Rsmessage['heure_debut']; ?></td>
            <td class="tab_detail2"><?php 
					
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsActeur="SELECT * FROM cdt_evenement_acteur,cdt_classe,cdt_groupe
			WHERE 	cdt_evenement_acteur.even_ID = ".$row_Rsmessage['ID_even']."
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
				
					?>
            </td>
            <td class="tab_detail2"><?php echo $row_Rsmessage['titre_even']; ?></td>
            <td class="tab_detail2"><?php if ($row_Rsmessage['date_debut'] == $row_Rsmessage['date_fin']) { 
					echo $row_Rsmessage['heure_debut']==$row_Rsmessage['heure_fin']?"--":$row_Rsmessage['heure_fin']; }
					else { echo '&raquo;&nbsp;'.substr($row_Rsmessage['date_fin'],8,2).'-'.substr($row_Rsmessage['date_fin'],5,2).'-'.substr($row_Rsmessage['date_fin'],2,2) ;} ?>
            </td>
            <td class="tab_detail2"><?php $acc = explode( ";",$row_Rsmessage['accompagnateurs']); ?>
              <?php for ($i=0; $i<sizeof($acc)-1; $i++) { echo  $acc[$i].'<br>'; } ;?>
            </td>
            <td class="tab_detail2"><?php echo $row_Rsmessage['nom_prof']; ?></td>
            <td class="tab_detail2"><?php echo $row_Rsmessage['etat']; ?></td>
            <td class="tab_detail2"><?php if ($_SESSION['nom_prof']== $row_Rsmessage['nom_prof']) { ?>
              <img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','evenement_fiche.php?ID_even=<?php echo $row_Rsmessage['ID_even']; ?>');return document.MM_returnValue">
              <?php } ?>
            </td>
            <?php  if ($row_Rsmessage['etat']=="projet") { echo '</i>' ;} ; ?>
          </tr>
          <?php } while ($row_Rsmessage = mysqli_fetch_assoc($Rsmessage)); ?>
        </table>
        <br>
        <p align="center"> <i>N'oubliez pas de vous concerter avec la Vie scolaire pour les classes que vous et vos accompagnateurs ne prendrez pas en cours. </i></p>
        <br>
        <p align="center"><span style="background-color:#D8D8D8 ;padding:5px ;border:1px solid black; font-size:11px; "> <a href="evenement_liste.php" >Retour &agrave; la liste des &eacute;v&egrave;nements</a></span> &nbsp;  &nbsp;
          <?php } 
 
 
 else {




 // ==== fin  bilan  de retour saisie  &  debut masque de saisie nouvel evenement  >>  =========================?>
      
    <div id="msg1"> </div> <!-- Important - Ne pas supprimer(ajax_niveau_even_acteur)--> 
	<div id="msg2"> </div> <!-- Important - Ne pas supprimer(ajax_niveau_even_dest)  -->     
		<form onLoad= "formfocus()" method="post"  name="form1" action="evenement_ajout.php"  onsubmit="return verifier()">
          <table width="100%" border="0">
            <tr valign="top">
              <td><table align="center" border="0" cellspacing="0" cellpadding="0">
           
                  <tr>
                    <td valign="top"><p>&nbsp;</p>
                      <p><strong><br>
                        Classes concern&eacute;es </strong></p>
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
                          <td class="tab_detail">
							  <div align="center">
                              <input type="checkbox" name="<?php echo 'niv'.$row_Rsniv['ID_niv']; ?>"   id="<?php echo 'niv'.$row_Rsniv['ID_niv']; ?>" onclick=ShowNiveauActeur(<?php echo $row_Rsniv['ID_niv']; ?>,this.checked) value="on">
                              
                            </div></td>
                        </tr>
                        <?php } while ($row_Rsniv = mysqli_fetch_assoc($Rsniv)); ?>
                      </table>
                      <br/>
                      <?php };?>
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
                              <input type="checkbox" name="<?php echo 'classeven'.$i; ?>"   id="<?php echo 'classeven'.$i;; ?>" value="on" onclick="decocherToutacteur(<?php echo $i; ?>)">
                            </div></td>
                          <td class="tab_detail"><select name="<?php echo 'groupeven'.$i; ?>" size="1" class="menu_deroulant" id="<?php echo 'groupeven'.$i; ?>">
                              <?php do {  ?>
                              <option value="<?php echo $row_Rsgroupe['ID_groupe']?>"><?php echo $row_Rsgroupe['groupe']?></option>
                              <?php
						
                		} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
            $rows = mysqli_num_rows($Rsgroupe);
            if($rows > 0) {
            mysqli_data_seek($Rsgroupe, 0);
	        $row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
            }?>
                            </select>
                          </td>
                        </tr>
                        <?php 
				  } ; ?>
                      </table>
                      <p> </p></td>
                  </tr>
                </table></td>
              <td><p>&nbsp;</p>
                <table align="center" cellpadding="0" cellspacing="0">
                  <tr valign="baseline">
                    <td valign="top"><div align="center"><b>Titre de l'&eacute;v&egrave;nement</b></div></td>
                    <td valign="top"><div align="center"><b>Modalit&eacute</b></div></td>
                    <td valign="top"><div align="center"><b> Domaine </b></div></td>
                  </tr>
                  <tr valign="baseline">
                    <td valign="top"><div align="center">
                        <input name="titre_even" type="text" id="titre_even" size="44">
                      </div></td>
                    <td valign="top"><div align="center">
                        <select name="code_mode" id="code_mode">
                          <option value="0" ></option>
                          <?php for ($i=0; $i< sizeof($mode); $i=$i+1) {?>
                          <option value="<?php echo $i;?>"><?php echo $mode[$i];?> </option>
                          <?php ;};?>
                        </select>
                      </div></td>
                    <td valign="top"><div align="center">
                        <select name="code_theme" id="code_theme">
                          <option value="0" ></option>
                          <?php for ($i=0; $i< sizeof($theme); $i=$i+1) {?>
                          <option value="<?php echo $i;?>"><?php echo $theme[$i]; ?></option>
                          <?php ;};?>
                        </select>
                      </div></td>
                  </tr>
                  <tr valign="baseline">
                    <td valign="top">&nbsp;</td>
                    <td valign="top">&nbsp;</td>
                    <td valign="top">&nbsp;</td>
                  </tr>
                  <tr valign="baseline">
                    <td valign="top"><b>Date et heure de départ </b></td>
                    <td colspan="2" valign="top"><b>Date et heure de retour </b></td>
                  </tr>
                  <tr valign="baseline">
                    <td valign="top"><input name='date1' type='text' id='date1' value="<?php echo $date1_form;?>" size="10"/>
                      <select name="heure_debut_h">
                        <option value="" selected="selected"> ? h</option>
                        <?php for ($i=0; $i<$nb_heures; $i=$i+1) { ?>
                        <option value="<?php echo $choix_heures[$i];?>"><?php echo $choix_heures[$i] ; ?> h </option>
                        <?php } ?>
                      </select>
                      <select name="heure_debut_min" size="1">
                        <option value="" selected="selected"> ? min</option>
                        <?php for ($i=0; $i<$nb_min; $i=$i+1) { ?>
                        <option value="<?php echo $choix_min[$i];?>"><?php echo $choix_min[$i] ; ?> min</option>
                        <?php } ?>
                      </select>
                      &nbsp;&nbsp;&nbsp;</td>
                    <td colspan="2" valign="top">&nbsp;
                      <input  name='date2' type='text' id='date2' value="<?php echo $date2_form?>" size="10" />
                      <select name="heure_fin_h">
                        <option value="" selected="selected"> ? h</option>
                        <?php for ($i=0; $i<$nb_heures; $i=$i+1) { ?>
                        <option value="<?php echo $choix_heures[$i];?>"><?php echo $choix_heures[$i] ; ?> h</option>
                        <?php } ?>
                      </select>
                      <select name="heure_fin_min" size="1" id="heure_fin_min">
                        <option value="" selected="selected"> ? min</option>
                        <?php for ($i=0; $i<$nb_min; $i=$i+1) { ?>
                        <option value="<?php echo $choix_min[$i];?>"><?php echo $choix_min[$i] ; ?> min</option>
                        <?php } ?>
                      </select>
                      &nbsp; &nbsp; <span style="vertical-align:top;" > </span></td>
                  </tr>
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
</script>
                  <tr valign="baseline">
                    <td colspan="3" valign="top">&nbsp;</td>
                  </tr>
                  <tr valign="baseline">
                    <td colspan="3" valign="top"><span >Signaler que ces dates sont encore incertaines :
                      <input type="checkbox" style="width:18px;
    height:18px;vertical-align:middle ;" name="pb_dates"  id="pb_dates" onClick = decocherTout() value="1">
                      </span></td>
                  </tr>
                  <tr valign="baseline">
                    <td colspan="3" valign="top">&nbsp;</td>
                  </tr>
                  <tr valign="baseline">
                    <td colspan="3" valign="top"><b>Description de l'&eacute;v&egrave;nement </b> <br>
                      ( visible sur le cdt des &eacute;l&egrave;ves quand le projet sera valid&eacute; <b>si</b> vous cochez leur classe dans le tableau de droite ) </td>
                  </tr>
                  <tr valign="baseline">
                    <td colspan="3" valign="top"><div align="center">
                        <textarea name="description" rows="4" id="description" cols="70" height= "50"   class="grise"  ></textarea>
                      </div></td>
                  </tr>
                  <tr valign="baseline">
                    <td valign="top">&nbsp;</td>
                    <td valign="top">&nbsp;</td>
                    <td valign="top">&nbsp;</td>
                  </tr>
                  <tr valign="baseline">
                    <td colspan="3" valign="top"><b> Effectif &eacute;l&egrave;ves &nbsp;</b>
                      <input class="grise" name="classes_eff" style="text-align:center;" type="number" id="classes_eff" size="3">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>Participation par &eacute;l&egrave;ve :</b>
                      <input name="cout_elv" style="text-align:center;" type="number" id="cout_elv" size="3" class="grise">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>Co&ucirc;t global : </b>
                      <input name="cout_glob" style="text-align:center"; type="number" id="cout_glob" size="4" class="grise">
                      &euro; </td>
                  </tr>
                  <tr valign="baseline">
                    <td colspan="3" valign="top">&nbsp;</td>
                  </tr>
                  <tr valign="baseline">
                    <td colspan="3" valign="top"><b>Accompagnateurs - Encadrants</b> &nbsp; (non affich&eacute;  sur le cdt des &eacute;l&egrave;ves) </td>
                  </tr>
                  <tr valign="baseline">
                    <td colspan="3" valign="top"><div align="center"><br>
                        <input type="text" id="acc1"  name="acc1"  cols="12" />
                        &nbsp; &nbsp;   &nbsp;
                        <input type="text" id="acc2"  name="acc2"  cols="12" class="grise"  />
                        &nbsp; &nbsp;   &nbsp;
                        <input type="text" id="acc3"  name="acc3"  cols="12" class="grise"  />
                        &nbsp; &nbsp;   &nbsp; <br>
                        <input type="text" id="acc4"  name="acc4"  cols="11" class="grise"  />
                        &nbsp; &nbsp;   &nbsp;
                        <input type="text" id="acc5"  name="acc5"  cols="11" class="grise"  />
                        &nbsp; &nbsp;   &nbsp;
                        <input type="text" id="acc6"  name="acc6"  cols="11" class="grise"  />
                        &nbsp; &nbsp;   &nbsp; <br>
                        <input type="text" id="acc7"  name="acc7"  cols="10" class="grise"  />
                        &nbsp; &nbsp;   &nbsp;
                        <input type="text" id="acc8"  name="acc8"  cols="10" class="grise"  />
                        &nbsp; &nbsp;   &nbsp;
                        <input type="text" id="acc9"  name="acc9"  cols="10" class="grise"  />
                        &nbsp; &nbsp;   &nbsp; </div></td>
                  </tr>
                  <tr valign="baseline">
                    <td colspan="3" valign="top">&nbsp;</td>
                  </tr>
                  <tr valign="baseline">
                    <td colspan="3" valign="top"><p align="center"><b>Détails d'organisation </b> (non affich&eacute;  sur le cdt des &eacute;l&egrave;ves) </p>
                      <p align="center">
                        <textarea name="details_sup" rows="4" id="details_sup" cols="70"  class="grise"></textarea>
                        <br>
                        <br>
                        <?php if ($valideur <>"")  // test pour  envoi vers resp  par mail
							{ echo '<p><INPUT type="checkbox" name="mail" value="oui" style="width:18px; height:18px;vertical-align:bottom ;"  > Envoyer une demande de validation &agrave; '.$valideur.' par mail </p>' ; } else { echo '<input type="hidden" name="mail" value="non"> </p> ' ; } ; ?>
                        <select name="etat" >
                          <option value="projet" >en projet</option>
                          <option value="&agrave; valider" style="background-color:LightSkyBlue ;" >projet &agrave; valider </option>
                          <?php if ( ($_SESSION['droits']==4 ) || ( $valideur =="" ) ) { ?>
                          <option value="Valid&eacute;" style="background-color:aquamarine ;" >Valider le projet</option>
                          <?php } ; ?>
                          <option value="annul&eacute;" style="background-color:LightSalmon;" >projet annul&eacute; </option>
                        </select>
                        &nbsp; &nbsp; &nbsp;
                        <input name="submit" type="submit" value="Planifier cet &eacute;v&egrave;nement">
                      </p></td>
                  </tr>
                  <tr valign="baseline">
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td></td>
                  </tr>
                  <tr valign="baseline">
                    <td>
					<?php if ($_SESSION['droits']==1) 
					{?>
                      <a href="../administration/index.php">Retour au Menu Administrateur'; </a>
                      <?php };					
					  if ($_SESSION['droits']==4) 
					{?>
                      <a href="../direction/direction.php">Retour au Menu Resp. Etablissement'; </a>
                      <?php };
				if ($_SESSION['droits']==3 || $_SESSION['droits']==7) 
					{?>
                      <a href="../vie_scolaire/vie_scolaire.php">Retour au Menu Vie Scolaire</a>
                      <?php };
				if ($_SESSION['droits']==8) 
					{?>
                      <a href="../enseignant/enseignant.php">Retour au Menu Documentaliste</a>
                      <?php };					  
					  ?>
                      <input type="hidden" name="MM_insert" value="ok">
                    </td>
                    <td><a href="evenement_liste.php">&nbsp; Retourner &agrave; la liste sans enregistrer  &nbsp;</a></td>
                    <td></td>
                  </tr>
                </table></td>
              <td><table align="center" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td valign="top"><p> L'&eacute;v&egrave;nement n'apparaitra dans le CDT &eacute;l&egrave;ves<b><br>
                        qu'une fois le projet valid&eacute;</b>.</p>
                      <p><strong>Information diffus&eacute;e vers </strong>:</p>
					  
					  
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
                          <td class="Style6"><SCRIPT>

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
                          <td class="Style6">Toutes </td>
                        </tr>
                        <?php for ($i=1; $i<=$totalRows_RsClasse; $i++) { ?>
                        <tr>
                          <td class="tab_detail"><div align="left"> <a href="../planning.php?classe_ID=	<?php echo $indcl_id[$i].'&date='.$mois_planning;?>" TARGET="_blank" title="Planning de la <?php echo $indcl_nom[$i];?>"
			> <?php echo $indcl_nom[$i] ; ?> </a> </div></td>
                          <td class="tab_detail"><div align="center">
                              <input type="checkbox" name="<?php echo 'classedest'.$i; ?>"   id="<?php echo 'classedest'.$i; ?>" value="on" onclick="decocherToutdestinataire(<?php echo $i; ?>)"  >
                            </div></td>
                          <td class="tab_detail"><select name="<?php echo 'groupedest'.$i; ?>" size="1" class="menu_deroulant" id="<?php echo 'groupedest'.$i; ?>">
                              <?php do {  ?>
                              <option value="<?php echo $row_Rsgroupe['ID_groupe']?>"><?php echo $row_Rsgroupe['groupe']?></option>
                              <?php
						
                		} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
            $rows = mysqli_num_rows($Rsgroupe);
            if($rows > 0) {
            mysqli_data_seek($Rsgroupe, 0);
	        $row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
            }?>
                            </select>
                          </td>
                        </tr>
                        <?php 
				  } ; ?>
                      </table>
                      <!-- 347 -->
                      <p> </p></td>
                    <!-- fin zone selection classe -->
                  </tr>
                </table></td>
            </tr>
          </table>
        </form>
        <?php } ;?>
    </tr>
    <tr>
      <td colspan="2">
    </tr>
    <tr>
      <td colspan="2">
  </table>
</div>
</body>
</html>
<?php

if( isset($_POST['date1']) and  isset($_POST['date2']	) ) {mysqli_free_result($Rsmessage) or die(mysqli_error($conn_cahier_de_texte)); } ;
mysqli_free_result($RsClasse);
mysqli_free_result($Rsgroupe);
mysqli_free_result($Rsniv);
?>
