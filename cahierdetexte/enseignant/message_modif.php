<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

if (isset($_GET['sup'])&&($_GET['sup']==1)){
	//on efface de la table fichiers_joints
	$deleteSQL = "DELETE FROM cdt_message_fichiers WHERE cdt_message_fichiers.ID_mesfich=".$_GET['ID_mesfich'];
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$fichier = '../fichiers_joints_message/'.$_GET['nom_fichier'];
	unlink($fichier);		
};


$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
$datetoday=date('y-m-d');

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1") && (isset($_GET['ID_message']))) {
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_time_zone_db = "SELECT param_val FROM cdt_params WHERE param_nom='time_zone'";
	$time_zone_db = mysqli_query($conn_cahier_de_texte, $query_time_zone_db) or die(mysqli_error($conn_cahier_de_texte));
	$row_time_zone_db = mysqli_fetch_assoc($time_zone_db);

	date_default_timezone_set($row_time_zone_db['param_val']);
	
	$date_envoi=date("Y-m-d H:i:s");
	
	if (isset($_POST['online']) && $_POST['online'] =='O'){
		$online='O';
		$date_fin_publier=substr($_POST['date_fin_publier'],6,4).substr($_POST['date_fin_publier'],3,2).substr($_POST['date_fin_publier'],0,2);
		
	} else {
		$online='N';
		$date_fin_publier='0000-00-00';
	};
	$updateSQL = sprintf("UPDATE cdt_message_contenu SET message=%s, date_fin_publier=%s, online=%s WHERE ID_message=%u",
		GetSQLValueString($_POST['message'], "text"),
		GetSQLValueString($date_fin_publier, "text"),
		GetSQLValueString($online, "text"),
		GetSQLValueString($_GET['ID_message'],"int")
		);
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));

        //envole-240812 : utilisation de "NOW()" car "date_envoi" est maintenant un champ "DATETIME" pour une datation plus precise (#2221)
        $insertSQL = sprintf(" INSERT INTO cdt_message_modif (ID_message, prof_ID, date_envoi)
                VALUES (%u,%u,%s)",
                GetSQLValueString($_GET['ID_message'],"int"),
		GetSQLValueString($_SESSION['ID_prof'], "int"),
		GetSQLValueString($date_envoi, "text")
		);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	
	
	
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
			$insertSQL = sprintf("INSERT INTO cdt_message_fichiers ( message_ID, nom_fichier, prof_ID) VALUES (%u,%s,%u)",
				GetSQLValueString($_GET['ID_message'], "int"),
				GetSQLValueString($_GET['ID_message'].'_'.$nom_fichier1, "text"),
				GetSQLValueString($_SESSION['ID_prof'], "int")
				);
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
		}
		
                
        }
        
        // fin presence fichiers message 1 joint ****************************************************************
        
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
        // fin presence fichiers message 2 joint ****************************************************************
        
	$insertGoTo = "message_ajout.php";
	if(isset($_GET['dest_profs'])) {$insertGoTo.='?dest_profs=0';};
	header(sprintf("Location: %s", $insertGoTo));
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifMessage =sprintf("SELECT * FROM cdt_message_contenu,cdt_prof WHERE ID_message=%u AND cdt_message_contenu.prof_ID = cdt_prof.ID_prof ",$_GET['ID_message'] );
$RsModifMessage = mysqli_query($conn_cahier_de_texte, $query_RsModifMessage) or die(mysqli_error($conn_cahier_de_texte));
$row_RsModifMessage = mysqli_fetch_assoc($RsModifMessage);
$totalRows_RsModifMessage = mysqli_num_rows($RsModifMessage);

if (isset($_GET['groupe_ID']) && isset($_GET['classe_ID']) ){
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE ID_classe=%u  ",$_GET['classe_ID']);
	$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsClasse = mysqli_fetch_assoc($RsClasse);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsGroupe = sprintf("SELECT * FROM cdt_groupe WHERE ID_groupe=%u  ",$_GET['groupe_ID']);
	$RsGroupe = mysqli_query($conn_cahier_de_texte, $query_RsGroupe) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsGroupe = mysqli_fetch_assoc($RsGroupe);
	
} else if ($row_RsModifMessage['pp_classe_ID']!=0){
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE ID_classe=%u",$row_RsModifMessage['pp_classe_ID']);
        $RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
        $row_RsClasse = mysqli_fetch_assoc($RsClasse);
        
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_RsGroupe = sprintf("SELECT * FROM cdt_groupe WHERE ID_groupe=%u  ",$row_RsModifMessage['pp_groupe_ID']);
        $RsGroupe = mysqli_query($conn_cahier_de_texte, $query_RsGroupe) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsGroupe = mysqli_fetch_assoc($RsGroupe);
        
};

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

function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}

function confirmation(sup_nom_fich)
{
	if (confirm("Voulez-vous r\351ellement supprimer la pi\350ce jointe "+sup_nom_fich+" ?")) { // Clic sur OK
		MM_goToURL('window','message_modif.php?ID_message=<?php echo $_GET['ID_message']; 
			if (!isset($_GET['dest_profs'])){
				if (isset($_GET['classe_ID'])){echo '&classe_ID='. $_GET['classe_ID'];};
				if (isset($_GET['groupe_ID'])){echo '&groupe_ID='.$_GET['groupe_ID'];};
	} else {echo '&dest_profs=0';};
	?>
	&ID_mesfich=<?php echo $row_Rs_fichiers_joints_form['ID_mesfich'];?>&nom_fichier=<?php echo $row_Rs_fichiers_joints_form['nom_fichier'] ;?>&sup=1');
	}
}
</script>
</head>
<body >

<div id="">
<!--<div id="container3"> en remplacement du div ci-dessus pour un tableau moins large -->
<p><table class="lire_bordure" width="90%" border="0" align="center" cellpadding="0" cellspacing="0">

<tr class="lire_cellule_4">
<td >CAHIER DE TEXTES - 
<?php echo $_SESSION['identite']. ' - ';
if ($row_RsModifMessage['pp_classe_ID']==0){   
	echo 'Diffusion d&acute;un message &agrave; tous les enseignants'; 
}
else if (isset($_GET['dest_profs'])){
	echo 'Diffusion d&acute;un message aux enseignants de la classe de '. $row_RsClasse['nom_classe']. ' - '.$row_RsGroupe['groupe'];
}
else {	
echo  'Diffusion d&acute;un message aux &eacute;l&egrave;ves de la classe de '. $row_RsClasse['nom_classe']. ' - '.$row_RsGroupe['groupe'];};?></td>    <td ><div align="right"><a href="enseignant.php"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></div></td>
</tr>  <tr>
<td colspan="2" valign="top" class="lire_cellule_2" ><br /><br />

<form onLoad= "formfocus()" method="POST" enctype="multipart/form-data" name="form1" action="message_modif.php?ID_message=<?php echo $_GET['ID_message'];if(isset($_GET['dest_profs'])){echo '&dest_profs=0';};?>" >



<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td valign="top">&nbsp;</td>
<td valign="top"><br />
<p align="center" class="Style70">Modification d'un message</p>
<table align="center" cellspacing="5">
<tr valign="baseline">
<td valign="top">
<?php 
if ((isset($_SESSION['affiche_xinha']))&&($_SESSION['affiche_xinha']==1)){
	include('../vie_scolaire/area_message.php');}
else { 
	include('../vie_scolaire/area_message_tiny.php');};
;?>

<p>
<textarea name="message" cols="70" rows="7" id="message" width="300" height= "80" ><?php echo $row_RsModifMessage['message']; ?></textarea>
</p>
<?php if ($totalRows_Rs_fichiers_joints_form>0){ ?>
	<p>
	
	Documents d&eacute;j&agrave; joints &agrave; votre message :<br>
	<?php				
	do {
		$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
		echo '<a href="../fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a>';
		?>&nbsp;&nbsp;
		
		
		<img src="../images/ed_delete.gif" ID="ed_delete" alt="Supprimer" title="Supprimer" width="11" height="11" border="0" onClick= "return confirmation('<?php echo $nom_f;?>')">
		<br />
		<?php 
		
	} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
};
?>
<br>
Documents joints &agrave; votre message :
<br /> <br />
<input type="hidden" name="MAX_FILE_SIZE" value="100000000">
<input type="FILE" size="80" name="fichier_1_message" class="Style2">
<br /><br />
<input type="hidden" name="MAX_FILE_SIZE" value="100000000">
<input type="FILE" size="80" name="fichier_2_message" class="Style2">
<br /><br />					
</p></td>
</tr>
<tr valign="baseline">
<td><div align="center">
<div align="center"> Publier en ligne
<script>
$(function() {
        	$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
        	$.datepicker.setDefaults($.datepicker.regional['fr']);
        	$('#date_fin_publier').datepicker({firstDay:1});
});
</script>
<input name="online" type="checkbox" id="online" value="O" <?php if ($row_RsModifMessage['online']=="O"){echo "checked";}; ?>>
jusqu'au 
<input name='date_fin_publier' type='text' id='date_fin_publier' value="<?php 
if ($row_RsModifMessage['date_fin_publier']=='0000-00-00'){	  
	$date_fin_publier=date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+15,  date("Y")));			  
} 
else {
$date_fin_publier=$row_RsModifMessage['date_fin_publier'];	};		  
$date1_form=substr($date_fin_publier,8,2).'/'.substr($date_fin_publier,5,2).'/'.substr($date_fin_publier,0,4);
echo $date1_form;?>" size="10"/>
</em>
&nbsp;
</div>
<p>&nbsp; </p>
<p>
<input name="submit" type="submit" value="Enregistrer les modifications">
</p>
<p><br>
<a href="message_ajout.php<?php if(isset($_GET['dest_profs'])){echo '?dest_profs=0';};?>">Retour au Menu Gestion des messages</a> - <a href="enseignant.php">Retour au Menu Enseignant</a> - <a href="ecrire.php?date=<?php echo date('Ymd');?>">Retour en saisie de s&eacute;ance</a> </p
>
</div></td>
</tr>
</table>
<input name="dest_ID" type="hidden" value="<?php if  (isset($_GET['dest_profs'])){echo '2';}else {echo '0';};?>">

<input type="hidden" name="MM_update" value="form1">
</form></td>
</tr>

</table>
</p><p>
</p>
</div>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsModifMessage);
if (isset($RsClasse)) {
	mysqli_free_result($RsClasse);
	mysqli_free_result($RsGroupe);
}
mysqli_free_result($Rs_fichiers_joints_form);
?>
