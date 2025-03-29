<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>7)&& ($_SESSION['droits']<>8)){ header("Location: ../index.php");exit;};

require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
$datetoday=date('y-m-d');

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsniv = "SELECT * FROM cdt_niveau ";
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") && (isset($_POST['message']))) {
	if ((isset($_POST['checkbox']))&&($_POST['checkbox']==1)){$dest_ID=1;}else{$dest_ID=0;};
	
	if (isset($_POST['online']) && $_POST['online'] =='O'){
		$online='O';
		$date_fin_publier=substr($_POST['date_fin_publier'],6,4).substr($_POST['date_fin_publier'],3,2).substr($_POST['date_fin_publier'],0,2);
	} else {
		$online='N';
		$date_fin_publier='0000-00-00';
	};
	//envole-240812 : utilisation de "NOW()" car "date_envoi" est maintenant un champ "DATETIME" pour une datation plus precise (#2221)
	$insertSQL = sprintf(" INSERT INTO `cdt_message_contenu` ( `message` , `prof_ID` , `date_envoi` , `date_fin_publier` , `online`,`dest_ID`)
		VALUES (%s,%u,NOW(),%s,%s,%u)",
		GetSQLValueString($_POST['message'], "text"),
		GetSQLValueString($_SESSION['ID_prof'], "int"),
		//GetSQLValueString($datetoday, "text"),
		GetSQLValueString($date_fin_publier, "text"),
		GetSQLValueString($online, "text"),
		$dest_ID
		);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$UID=mysqli_insert_id($conn_cahier_de_texte); 
for ($i=1; $i<=$totalRows_RsClasse; $i++) { 
  $refclassedest='classedest'.$i;
  $refgroupedest='groupedest'.$i;

if (isset($_POST[$refclassedest])&&(isset($_POST[$refgroupedest])) &&($_POST[$refclassedest]=='on'))
{
$insertSQL2= sprintf("INSERT INTO `cdt_message_destinataire` ( `message_ID` , `classe_ID` , `groupe_ID`  )  VALUES ('%u', '%u','%u');",$UID,$indcl_id[$i], $_POST[$refgroupedest] );
$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));
}//du if
}//du for
	// A faire uniquement si fichier joint
	// fichier message joint 1  ***************************************************************************
	
	if ($_FILES['fichier_1_message']['name']<>'') {
		$dossier_destination = getcwd().'/../fichiers_joints_message/'; 
		
		$dossier_temporaire = $_FILES['fichier_1_message']['tmp_name'];
		$type_fichier = $_FILES['fichier_1_message']['type'];
		$nom_fichier1 = sans_accent($_FILES['fichier_1_message']['name']);

		if (preg_match('/.php/i',$nom_fichier1)) {$nom_fichier1 .= ".txt"; };
		$erreur= $_FILES['fichier_1_message']['error'];
		if ($erreur == 2 ) {
			exit ("Le fichier 1 joint au message d&eacute;passe la taille de 100 Mo.");
		}
		if ($erreur == 3 ) {
			exit ("Le fichier 1 joint au message a &eacute;t&eacute; partiellement transf&eacute;r&eacute;. Envoyez-le &agrave; nouveau.");
		}
		
		if( !move_uploaded_file($dossier_temporaire, $dossier_destination . $UID.'_'.$nom_fichier1) )
		{
			exit("Impossible de copier le fichier 1 joint au message dans le dossier_destination");
		}
		
		//--------------ecriture dans la table du nom du fichier
		if ($_FILES['fichier_1_message']['name']<>'') {
			$insertSQL = sprintf("INSERT INTO cdt_message_fichiers ( message_ID, nom_fichier, prof_ID) VALUES (%s,%s,%s)",
				GetSQLValueString($UID, "int"),
				GetSQLValueString($UID.'_'.$nom_fichier1, "text"),
				GetSQLValueString($_SESSION['ID_prof'], "int")
				);
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		}
		
		
	}
	
	// fin presence fichiers message 1 joint ****************************************************************
	
	// fichier message joint 2  ***************************************************************************
	if ($_FILES['fichier_2_message']['name']<>'') {
		$dossier_destination = getcwd().'/../fichiers_joints_message/'; 
		
		$dossier_temporaire = $_FILES['fichier_2_message']['tmp_name'];
		$type_fichier = $_FILES['fichier_2_message']['type'];
		$nom_fichier2 = sans_accent($_FILES['fichier_2_message']['name']);

		if (preg_match('/.php/i',$nom_fichier2)) {$nom_fichier2 .= ".txt"; };
		$erreur= $_FILES['fichier_2_message']['error'];
		if ($erreur == 2 ) {
			exit ("Le fichier 2 joint au message d&eacute;passe la taille de 100 Mo.");
		}
		if ($erreur == 3 ) {
			exit ("Le fichier 2 joint au message a &eacute;t&eacute; partiellement transf&eacute;r&eacute;. Envoyez-le &agrave; nouveau.");
		}
		
		if( !move_uploaded_file($dossier_temporaire, $dossier_destination . $UID.'_'.$nom_fichier2) )
		{
			exit("Impossible de copier le fichier 2 joint au message dans le dossier_destination");
		}
		
		//--------------ecriture dans la table du nom du fichier
		if ($_FILES['fichier_2_message']['name']<>'') {
			$insertSQL = sprintf("INSERT INTO cdt_message_fichiers ( message_ID, nom_fichier, prof_ID) VALUES (%u,%s,%u)",
				GetSQLValueString($UID, "int"),
				GetSQLValueString($UID.'_'.$nom_fichier2, "text"),
				GetSQLValueString($_SESSION['ID_prof'], "int")
				);
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		}
	};
	// fin presence fichiers message 2 joint ****************************************************************
	?>
	<script type="text/JavaScript">
	alert(' Le message a \351t\351 envoy\351');
	</script>
	<?php
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if (isset($_GET['tri'])&&($_GET['tri']=='auteur')){
	$query_Rsmessage ="SELECT *
	FROM cdt_message_contenu, cdt_prof
	WHERE cdt_message_contenu.dest_ID <2
	AND cdt_message_contenu.prof_ID = cdt_prof.ID_prof
	ORDER BY droits DESC, nom_prof, date_envoi DESC, ID_message DESC" ;
}
else 
{
	$query_Rsmessage ="SELECT * FROM cdt_message_contenu,cdt_prof WHERE cdt_message_contenu.dest_ID<2 AND cdt_message_contenu.prof_ID = cdt_prof.ID_prof ORDER BY date_envoi DESC,droits DESC,nom_prof";
};

$Rsmessage = mysqli_query($conn_cahier_de_texte, $query_Rsmessage) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmessage = mysqli_fetch_assoc($Rsmessage);
$totalRows_Rsmessage = mysqli_num_rows($Rsmessage);

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

<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>
<script type="text/JavaScript">

function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}

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

function confirmation(sup_message,ref,reftri)
{
	if (confirm("Voulez-vous supprimer r\351ellement ce message"+" N\260"+ref+sup_message+" ?")) { // Clic sur OK
		MM_goToURL('window','message_supprime.php?ID_message='+ref+'&tri='+reftri);
	}
}


function ShowNiveau(ID_niv,val_statut){
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
	   	xhr_object.open("POST", "./ajax_niveau.php", true);
	   	xhr_object.onreadystatechange = function() { 
	    	if (xhr_object.readyState == 4) {
	          	_response = xhr_object.responseText; 
			$("#msg").html(_response );
	        }
	   	}	 
	 	xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
		xhr_object.send("ID_niv=" + ID_niv + "&val_statut=" + _val_statut  ); 	 
}
</script>
</head>
<body >
<div id="msg2"> </div> <!-- Important - Ne pas supprimer(ajax_niveau_even_dest)  -->     
<div id="">


<table class="lire_bordure" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
<tr class="lire_cellule_4">
<td >CAHIER DE TEXTES - Diffusion d'un message aux &eacute;l&egrave;ves via leurs cahiers de textes</td>
<td ><div align="right"><a href="<?php 
if ($_SESSION['droits']==1) {echo '../administration/index.php';};
if ($_SESSION['droits']==2) {echo '../enseignant/enseignant.php';};
if ($_SESSION['droits']==4) {echo '../direction/direction.php';};
if ($_SESSION['droits']==3 || $_SESSION['droits']==7) {echo 'vie_scolaire.php';};
if ($_SESSION['droits']==8) {echo '../enseignant/enseignant.php';};

?>">


<img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></div></td>
</tr>
<tr>
<td colspan="2" valign="top" class="lire_cellule_22" ><br />
<?php if ($totalRows_Rsmessage <>0){?>


<a href="<?php if (isset($_GET['affiche_mes'])) {
	echo 'message_ajout.php';	if (isset($_GET['tri'])){echo '?tri='.$_GET['tri'];};
	;}
	else {
	echo 'message_ajout.php?affiche_mes=1';if (isset($_GET['tri'])){echo '&tri='.$_GET['tri'];};
	};
	if (isset($_GET['tri'])){echo '&tri='.$_GET['tri'];};
?>"><input name="af" id="af"  type="submit" value="<?php if (isset($_GET['affiche_mes'])) {echo 'Masquer'; } else {echo 'Afficher';};?> les messages d&eacute;j&agrave; publi&eacute;s"> </a>
        

        
<?php if (isset($_GET['affiche_mes'])) {?>
<input name="tri_auteur" id="tri_auteur"  type="submit" value="Trier par auteur" onClick="MM_goToURL('window','message_ajout.php?affiche_mes=1&tri=auteur');return document.MM_returnValue"/>
		<input name="tri_date" id="tri_date"  type="submit" value="Trier par date" onClick="MM_goToURL('window','message_ajout.php?affiche_mes=1&tri=date');return document.MM_returnValue"/>
<br /><br />
        <table width="100%"  border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
        <tr>
        <td class="Style6">N&deg;</td>
        <td class="Style6"><?php if ($row_Rsmessage['dest_ID']<>2){ echo 'Pour ';};?></td>
        <td class="Style6"><div align="center">Messages&nbsp;</div></td>
        <td class="Style6">Publi&eacute;&nbsp;</td>
        <td class="Style6">Cr&eacute;&eacute;&nbsp;le&nbsp;</td>
        <td class="Style6">Publi&eacute;&nbsp;jusqu'au&nbsp;</td>
        <td class="Style6"><div align="left">
        <?php 
        if($row_Rsmessage['droits']==2){echo 'Enseignant';};
        if($row_Rsmessage['droits']==3){echo 'Vie&nbsp;Scolaire';} ;
        if($row_Rsmessage['droits']==4){echo 'Direction';};
        ?>&nbsp;
        </div></td>
        <td class="Style6">@</td>
        <td class="Style6">&nbsp;</td>
        <td class="Style6">&nbsp;</td>
        </tr>
        <?php $prec_ID_auteur='';
        do { 
        	if(($row_Rsmessage['prof_ID']<>$prec_ID_auteur)&&($prec_ID_auteur<>'')&&(isset($_GET['tri']))&&($_GET['tri']=='auteur')&&($prec_ID_auteur<>2)){
        		?>
        		<tr>
        		<td class="Style6">N&deg;</td>
        		<td class="Style6"><?php if ($row_Rsmessage['dest_ID']<>2){ echo 'Pour ';};?></td>
        		<td class="Style6"><div align="center">Messages&nbsp;</div></td>
        		<td class="Style6">Publi&eacute;&nbsp;</td>
        		<td class="Style6">Cr&eacute;&eacute;&nbsp;le&nbsp;</td>
        		<td class="Style6">Publi&eacute;&nbsp;jusqu'au&nbsp;</td>
        		<td class="Style6"><div align="left">
        		<?php 
        		if($row_Rsmessage['droits']==2){echo 'Enseignant';};
        		if($row_Rsmessage['droits']==3){echo 'Vie Scolaire';} ;
        		if($row_Rsmessage['droits']==4){echo 'Direction';};
        		?>&nbsp;
        		</div></td>
        		<td class="Style6">@</td>
        		<td class="Style6">&nbsp;</td>
        		<td class="Style6">&nbsp;</td>
        		</tr>
        	<?php }?>
        	<tr>
        	<td class="tab_detail"><?php echo $row_Rsmessage['ID_message']; ?></td>
        	<td class="tab_detail">
        	<?php if ($row_Rsmessage['dest_ID']==1){ echo 'Tous';}
        	else if ($row_Rsmessage['dest_ID']==0) {
        		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        		$query_RsClasses = sprintf("SELECT `nom_classe` FROM `cdt_classe`,`cdt_message_destinataire` WHERE `message_ID`=%u AND `classe_ID`=`ID_classe` ORDER BY nom_classe ",GetSQLValueString($row_Rsmessage['ID_message'], "int"));
        		$RsClasses = mysqli_query($conn_cahier_de_texte, $query_RsClasses) or die(mysqli_error($conn_cahier_de_texte));
        		$row_RsClasses = mysqli_fetch_assoc($RsClasses);
        		$totalRows_RsClasses = mysqli_num_rows($RsClasses);
        		if ($totalRows_RsClasses<11){
				do {
        			echo '-&nbsp;'.$row_RsClasses['nom_classe'].'<br>';
        		} while ($row_RsClasses = mysqli_fetch_assoc($RsClasses));
				} else {
				$x=1;
				echo '<table><tr>';
				do {
        			echo '<td>'.$row_RsClasses['nom_classe'].'<td>';
					if ($x<0){echo '</tr><tr>';};
					$x=$x*(-1);
        		} while ($row_RsClasses = mysqli_fetch_assoc($RsClasses));	
				echo '</tr></table>';			
				}
        		mysqli_free_result($RsClasses);
        	};?></td>
        	<td class="tab_detail"><?php echo $row_Rsmessage['message']; ?>
        	<?php
        	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        	$query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE message_ID=".$row_Rsmessage['ID_message'];
        	$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
        	$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
        	$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
        	if ($totalRows_Rs_fichiers_joints_form>0){
        		if ($totalRows_Rs_fichiers_joints_form>1){echo '<p>Documents joints : <br /> ';} else {echo '<p>Document joint : <br /> ';};
        		do {
        			$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
        			echo '<a href="../fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a><br />';
        		} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
        	echo '</p>';};?>            </td>
        	<td class="tab_detail"><div align="center"><?php echo $row_Rsmessage['online']; ?></div></td>
        	<td class="tab_detail"><?php echo substr($row_Rsmessage['date_envoi'],8,2).'-'.substr($row_Rsmessage['date_envoi'],5,2).'-'.substr($row_Rsmessage['date_envoi'],0,4); ?></td>
        	<td class="tab_detail"><?php if($row_Rsmessage['date_fin_publier']<>'0000-00-00'){echo substr($row_Rsmessage['date_fin_publier'],8,2).'-'.substr($row_Rsmessage['date_fin_publier'],5,2).'-'.substr($row_Rsmessage['date_fin_publier'],0,4); }?></td>
        	<td class="tab_detail"><?php echo $row_Rsmessage['nom_prof'];?></td>
        	<td class="tab_detail"><?php  if ($row_Rsmessage['email']<>''){ ?>
        		<a href="mailto:<?php echo $row_Rsmessage['email'];?>"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant" title="Contacter l'enseignant" /></a>
        	<?php ;};?></td>
        	<td class="tab_detail"><?php if (($_SESSION['nom_prof']== $row_Rsmessage['nom_prof'])OR ($_SESSION['droits']==4)){?>
        		<img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','message_modif.php?ID_message=<?php echo $row_Rsmessage['ID_message']; ?>');return document.MM_returnValue">
              <?php } else {echo '&nbsp;';};?>            </td>
              <td class="tab_detail"><?php if (($_SESSION['nom_prof']== $row_Rsmessage['nom_prof'])OR ($_SESSION['droits']==4)){?>
              	      <img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick= "return confirmation('<?php if ($totalRows_Rs_fichiers_joints_form>0){
              	      	      if ($totalRows_Rs_fichiers_joints_form==1){ echo ' et sa pi&egrave;ce jointe attach&eacute;e';} else {echo ' et ses '.$totalRows_Rs_fichiers_joints_form.' pi&egrave;ces jointes attach&eacute;es';}
              };?>','<?php echo $row_Rsmessage['ID_message'];?>','<?php if(isset($_GET['tri'])){echo $_GET['tri'];}?>')" >
              <?php } else {echo '&nbsp;';};
              ?>            </td>
          </tr>
              <?php
              
              $prec_ID_auteur=$row_Rsmessage['prof_ID'];
              
} while ($row_Rsmessage = mysqli_fetch_assoc($Rsmessage)); ?>
</table>
      
  <?php
  };
};?>
<br />
  <fieldset >
<legend align="top"><strong>&nbsp;<img src="../images/email.gif" width="16" height="14">&nbsp;Nouveau message&nbsp;&nbsp;</strong></legend>


        <form onLoad= "formfocus()" method="post"  name="form1" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>" onsubmit="return verifier()">
<table width="100%" border="0" cellspacing="5" cellpadding="0">
<tr>
<td valign="top">
 <?php if($totalRows_Rsniv<>0){ ?>
 <br />
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
          <input type="checkbox" name="<?php echo 'niv'.$row_Rsniv['ID_niv']; ?>"   id="<?php echo 'niv'.$row_Rsniv['ID_niv']; ?>" onclick=ShowNiveauDest(<?php echo $row_Rsniv['ID_niv']; ?>,this.checked) value="on">

  </div></td>
		</tr>
	<?php } while ($row_Rsniv = mysqli_fetch_assoc($Rsniv)); ?>
	</table>
	<br />
	<?php 
}
?>

<table border="0" align="center" class="bordure">
                        <tr>
                          <td class="Style6"><div align="center">Classe&nbsp;</div></td>
                          <td class="Style6">
<SCRIPT>


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
	   	xhr_object.open("POST", "../enseignant/ajax_niveau_even_dest.php", true);
	   	xhr_object.onreadystatechange = function() { 
	    	if (xhr_object.readyState == 4) {
	          	_response = xhr_object.responseText; 
			$("#msg2").html(_response );
	        }
	   	}	 
	 	xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
		xhr_object.send("ID_niv=" + ID_niv + "&val_statut=" + _val_statut  ); 
			 
}

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
                          <td class="tab_detail"><div align="left">  <?php echo $indcl_nom[$i] ; ?></div></td>
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
</td>
<td valign="top">

<table align="center" cellspacing="5">
<tr valign="baseline">
<td valign="top">
<?php 
if ((isset($_SESSION['affiche_xinha']))&&($_SESSION['affiche_xinha']==1)){
	include('area_message.php');}
else { 
	include('area_message_tiny.php');};
;?>
<p>
<textarea name="message" cols="70" rows="7" id="message" width="200" height= "80" ><?php if ((isset($_SERVER['HTTP_USER_AGENT']))&&(preg_match('/Firefox/i',$_SERVER['HTTP_USER_AGENT']))){echo '<br />';}; ?></textarea>
<br>
Documents joints &agrave; votre message : <br />
<br />
<input type="hidden" name="MAX_FILE_SIZE" value="100000000">
<input type="FILE" size="80" name="fichier_1_message" class="Style2">
<br />
<br />
<input type="hidden" name="MAX_FILE_SIZE" value="100000000">
<input type="FILE" size="80" name="fichier_2_message" class="Style2">
<br />
<br />
</p></td>
</tr>
<tr valign="baseline">
<td><div align="center">
<div align="center"> Publier en ligne
<input name="online" type="checkbox" id="online" value="O" checked>
        <script>
         $(function() {
        	$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
			$.datepicker.setDefaults($.datepicker.regional['fr']);
        	$('#date_fin_publier').datepicker({firstDay:1});
        });
        </script>
jusqu'au 
<input name='date_fin_publier' type='text' id='date_fin_publier' value="<?php 
$date_fin_publier=date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+15,  date("Y")));                          
$date1_form=substr($date_fin_publier,8,2).'-'.substr($date_fin_publier,5,2).'-'.substr($date_fin_publier,0,4);
echo $date1_form;?>" size="10"/>
</em>
&nbsp;

</div>
<p>
<input name="submit" type="submit" value="Cr&eacute;er ce nouveau message">
</p>
<p><br>
</div></td>
</tr>
</table>
</fieldset>
<input type="hidden" name="nb_classes" value="<?php echo $totalRows_RsClasse;?>">
<input type="hidden" name="MM_insert" value="form1">
</form>

        <div align="center"><br />
          <br />
        </div>
        <p align="center"> 

<a href="<?php 
if ($_SESSION['droits']==1) {echo '../administration/index.php';};
if ($_SESSION['droits']==2) {echo '../enseignant/enseignant.php';};
if ($_SESSION['droits']==4) {echo '../direction/direction.php';};
if ($_SESSION['droits']==3 || $_SESSION['droits']==7) {echo 'vie_scolaire.php';};
if ($_SESSION['droits']==8) {echo '../enseignant/enseignant.php';};

?>">Retour au Menu &nbsp;<img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></p>
</td>
</tr>
</table>

</DIV>



</body>
</html>
<?php
mysqli_free_result($Rsmessage);
mysqli_free_result($RsClasse);
mysqli_free_result($Rsgroupe);
mysqli_free_result($Rsniv);

?>
