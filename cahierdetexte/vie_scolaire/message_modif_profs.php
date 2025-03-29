<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>7)&& ($_SESSION['droits']<>8)){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

if (isset($_GET['sup'])&&($_GET['sup']==1)){
	//on efface de la table fichiers_joints
	$deleteSQL = "DELETE FROM cdt_message_fichiers WHERE cdt_message_fichiers.ID_mesfich=".$_GET['ID_mesfich'];
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	
	$fichier = '../fichiers_joints_message/'.$_GET['nom_fichier'];
	unlink($fichier);	
	$insertGoTo = "message_modif_profs.php?ID_message=".$_GET['ID_message'];
	header(sprintf("Location: %s", $insertGoTo));
	
};

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
$datetoday=date('y-m-d');

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") && (isset($_GET['ID_message']))) {
	
	if (isset($_POST['online']) && $_POST['online'] =='O'){
		$online='O';
		$date_fin_publier=substr($_POST['date_fin_publier'],6,4).substr($_POST['date_fin_publier'],3,2).substr($_POST['date_fin_publier'],0,2);
		
	} else {
		$online='N';
		$date_fin_publier='0000-00-00';
	};

	//envole-240812 : utilisation de "NOW()" car "date_envoi" est maintenant un champ "DATETIME" pour une datation plus precise (#2221)
	$updateSQL = sprintf(" UPDATE `cdt_message_contenu` SET message =%s , prof_ID=%u , date_envoi=NOW() ,date_fin_publier=%s , online=%s WHERE ID_message=%u ",
		GetSQLValueString($_POST['message'], "text"),
		GetSQLValueString($_SESSION['ID_prof'], "int"),
		//GetSQLValueString($datetoday, "text"),
		GetSQLValueString($date_fin_publier, "text"),
		GetSQLValueString($online, "text"),
		GetSQLValueString($_GET['ID_message'],"int")
		);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	
	//on efface
	if ((isset($_GET['ID_message'])) && ($_GET['ID_message'] != "")) {
		$deleteSQL = sprintf("DELETE FROM cdt_message_destinataire_profs WHERE message_ID=%u",
			GetSQLValueString($_GET['ID_message'], "int"));
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
		
	}
	
	
	$query_RsProf_max_ID = "SELECT MAX(ID_prof) FROM cdt_prof WHERE droits='2' ";
	$RsProf_max_ID = mysqli_query($conn_cahier_de_texte, $query_RsProf_max_ID) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsProf_max_ID = mysqli_fetch_assoc($RsProf_max_ID);
	
	for ($i=0; $i<=$row_RsProf_max_ID['MAX(ID_prof)']; $i++) { 
		$refprof='prof'.$i;
		
		if ((isset($_POST[$refprof])) && ($_POST[$refprof]=='on')){
			$insertSQL2= sprintf("INSERT INTO `cdt_message_destinataire_profs` ( `message_ID` , `prof_ID` )  VALUES ('%s', '%s' );",$_GET['ID_message'],$i);
			$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));
		}//du if
	}//du for
	
	
	// A faire uniquement si fichier joint
	// fichier message joint 1  ***************************************************************************
	
	if (isset($_FILES['fichier_1_message']['name'])&&($_FILES['fichier_1_message']['name']<>'')) {
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
		
		if( !move_uploaded_file($dossier_temporaire, $dossier_destination . $_GET['ID_message'].'_'.$nom_fichier1) )
		{
			exit("Impossible de copier le fichier 1 joint au message dans le dossier_destination");
		}
		
		//--------------ecriture dans la table du nom du fichier
		if (isset($_FILES['fichier_1_message']['name'])&&($_FILES['fichier_1_message']['name']<>'')) {
			$insertSQL = sprintf("INSERT INTO cdt_message_fichiers ( message_ID, nom_fichier, prof_ID) VALUES (%s,%s,%s)",
				GetSQLValueString($_GET['ID_message'], "int"),
				GetSQLValueString($_GET['ID_message'].'_'.$nom_fichier1, "text"),
				GetSQLValueString($_SESSION['ID_prof'], "int")
				);
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		}
		
		
	}
	
	// fin presence fichiers  message 1 joint ****************************************************************
	
	// fichier message joint 2  ***************************************************************************
	if (isset($_FILES['fichier_2_message']['name'])&&($_FILES['fichier_2_message']['name']<>'')) {
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
		
		if( !move_uploaded_file($dossier_temporaire, $dossier_destination . $_GET['ID_message'].'_'.$nom_fichier2) )
		{
			exit("Impossible de copier le fichier 2 joint au message dans le dossier_destination");
		}
                
                //--------------ecriture dans la table du nom du fichier
                if (isset($_FILES['fichier_2_message']['name'])&&($_FILES['fichier_2_message']['name']<>'')) {
                        $insertSQL = sprintf("INSERT INTO cdt_message_fichiers ( message_ID, nom_fichier, prof_ID) VALUES (%u,%s,%u)",
                                GetSQLValueString($_GET['ID_message'], "int"),
                                GetSQLValueString($_GET['ID_message'].'_'.$nom_fichier2, "text"),
                                GetSQLValueString($_SESSION['ID_prof'], "int")
				);
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		}
	}
	// fin presence fichiers  message 2 joint ****************************************************************
		if (isset($_GET['ppliste'])){
		$insertGoTo = 'message_ajout_profs.php?affiche_mes=1&ppliste=1';} 
	else {
		$insertGoTo = "message_ajout_profs.php?affiche_mes=1"; 
	};
	
	header(sprintf("Location: %s", $insertGoTo));
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifMessage =sprintf("SELECT * FROM cdt_message_contenu,cdt_prof WHERE ID_message=%u AND cdt_message_contenu.prof_ID = cdt_prof.ID_prof ",$_GET['ID_message'] );
$RsModifMessage = mysqli_query($conn_cahier_de_texte, $query_RsModifMessage) or die(mysqli_error($conn_cahier_de_texte));
$row_RsModifMessage = mysqli_fetch_assoc($RsModifMessage);
$totalRows_RsModifMessage = mysqli_num_rows($RsModifMessage);



mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsdest =sprintf("SELECT * FROM cdt_message_destinataire_profs WHERE message_ID=%u ",$_GET['ID_message'] );
$Rsdest = mysqli_query($conn_cahier_de_texte, $query_Rsdest) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsdest = mysqli_fetch_assoc($Rsdest);
$totalRows_Rsdest = mysqli_num_rows($Rsdest);

if (isset($_GET['ppliste'])){
if (isset($_GET['tri'])&&($_GET['tri']=='pp')){
	$query_RsProf = "SELECT * FROM cdt_prof_principal,cdt_prof, cdt_groupe, cdt_classe WHERE cdt_prof_principal.pp_prof_ID=cdt_prof.ID_prof AND cdt_prof_principal.pp_classe_ID=cdt_classe.ID_classe AND cdt_prof_principal.pp_groupe_ID=cdt_groupe.ID_groupe ORDER BY cdt_prof.identite,cdt_prof.nom_prof ASC";
}
else
{
	$query_RsProf = "SELECT * FROM cdt_prof_principal,cdt_prof, cdt_groupe, cdt_classe WHERE cdt_prof_principal.pp_prof_ID=cdt_prof.ID_prof AND cdt_prof_principal.pp_classe_ID=cdt_classe.ID_classe AND cdt_prof_principal.pp_groupe_ID=cdt_groupe.ID_groupe ORDER BY nom_classe ASC";
};
}
else {
$query_RsProf = "SELECT * FROM cdt_prof WHERE droits='2' AND ancien_prof='N' ORDER BY nom_prof ASC";
};
$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);
$totalRows_RsProf = mysqli_num_rows($RsProf);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE cdt_message_fichiers.message_ID=".$_GET['ID_message'];
$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);


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
.Style70 {font-size: 16px}
</style>

<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>

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

function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}

function confirmation(sup_nom_fich)
{
	if (confirm("Voulez-vous r\351ellement supprimer la pi\350ce jointe "+sup_nom_fich+" ?")) { // Clic sur OK
		MM_goToURL('window','message_modif_profs.php?ID_message=<?php echo $_GET['ID_message']; ?>&ID_mesfich=<?php echo $row_Rs_fichiers_joints_form['ID_mesfich'];?>&nom_fichier=<?php echo $row_Rs_fichiers_joints_form['nom_fichier'] ;?>&sup=1');
	}
}

</script>
</head>
<body >
<div id="">
<!--<div id="container3"> en remplacement du div ci-dessus pour un tableau moins large -->
<p>
<table class="lire_bordure" width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
<tr class="lire_cellule_4">
<td >CAHIER DE TEXTES -
<?php if(isset($_SESSION['identite'])){echo $_SESSION['identite'];} ?>
- Diffusion d'un message aux enseignants</td>
<td ><div align="right"><a href="message_ajout_profs.php<?php if (isset($_GET['ppliste'])){echo '?ppliste=1';};?>"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></div></td>
</tr>
<tr>
<td colspan="2" valign="top" class="lire_cellule_2" ><br />
<br />
<form onLoad= "formfocus()" method="post" enctype="multipart/form-data" name="form1" action="<?php echo $editFormAction; ?>" onsubmit="return verifier()">
<table width="100%" border="0" cellspacing="5" cellpadding="0">
<tr>
<td valign="top"><table border="0" align="center" class="bordure">
<tr>
<td class="Style6"><div align="center">Destinataires</div></td>
<td class="Style6"><SCRIPT>
function cocherTout(etat)
{
	var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
	for(var i=1; i<cases.length; i++)     // on les parcourt
		if(cases[i].type=='checkbox' && cases[i].name!='online')     // si on a une checkbox....
		cases[i].checked = etat;     // ... on la coche ou non
}



function decocherTout()
{
	var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
	cases[0].checked = false;     // ... on decoche la premiere, le TOUS
}
</SCRIPT>
Tous
<input type="checkbox" name="checkbox" id="tousaucun" onclick=cocherTout(this.checked) value="ok" ></td>
</tr>
<?php
do { ?>
	<tr>
	<td class="tab_detail"><div align="left">
	<?php if ($row_RsProf['identite']<>''){echo $row_RsProf['identite'];} else {echo $row_RsProf['nom_prof'];}?>
	</div></td>
	<td class="tab_detail"><div align="center">
	<input type="checkbox" name="<?php echo 'prof'.$row_RsProf['ID_prof']; ?>"   id="<?php echo 'prof'.$row_RsProf['ID_prof']; ?>" onclick=decocherTout()  
	<?php do { 
		if ($row_RsProf['ID_prof']==$row_Rsdest['prof_ID']){echo 'checked';};
	} while ($row_Rsdest = mysqli_fetch_assoc($Rsdest));
	
	if ($totalRows_Rsdest<>'0') {mysqli_data_seek($Rsdest, 0);};?>
	>
	</div></td>
	</tr>
<?php } while ($row_RsProf = mysqli_fetch_assoc($RsProf)); ?>
</table></td>
<td valign="top"><br />
<p align="center">Modification d'un  message</p>
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
<textarea name="message" cols="70" rows="7" id="message" width="200" height= "80" ><?php echo $row_RsModifMessage['message']; ?></textarea>
</p>
<?php if ($totalRows_Rs_fichiers_joints_form>0){ ?>
	<p> Documents d&eacute;j&agrave; joints &agrave; votre message :<br>
	<?php				
	do {
		$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
		echo '<a href="../fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a>';
		?>
		&nbsp;&nbsp; <img src="../images/ed_delete.gif" ID="ed_delete" alt="Supprimer" title="Supprimer" width="11" height="11" border="0" onClick= "return confirmation('<?php echo $nom_f;?>')"> <br />
		<?php 
		
	} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
};
?>
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
<input name="online" type="checkbox" id="online" value="O" <?php if ($row_RsModifMessage['online']=="O"){echo "checked";}; ?>>
<script>
$(function() {
                $('selector').datepicker($.datepicker.regional['fr']);$( "#date_fin_publier" ).datepicker();
});
</script>
jusqu'au
<input name='date_fin_publier' type='text' id='date_fin_publier' value="<?php 
if ($row_RsModifMessage['date_fin_publier']=='0000-00-00'){     
	$date_fin_publier=date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+15,  date("Y")));			  
} 
else {
$date_fin_publier=$row_RsModifMessage['date_fin_publier'];	};		  
$date1_form=substr($date_fin_publier,8,2).'-'.substr($date_fin_publier,5,2).'-'.substr($date_fin_publier,0,4);
echo $date1_form;?>" size="10"/>
</em>
&nbsp; </div>
<p>&nbsp; </p>
<p>
<input name="submit" type="submit" value="Enregistrer les modifications">
</p>
<p><br>
<a href="message_ajout_profs.php<?php if (isset($_GET['ppliste'])){echo '?ppliste=1';};?>">Menu gestion des messages aux enseignants</a></p>
</div></td>
</tr>
</table>
<input type="hidden" name="MM_update" value="form1">
</form></td>
</tr>
</table>
</p>
<p> </p>
</div>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsModifMessage);
mysqli_free_result($RsProf);
mysqli_free_result($Rsdest);
mysqli_free_result($Rs_fichiers_joints_form);
?>
