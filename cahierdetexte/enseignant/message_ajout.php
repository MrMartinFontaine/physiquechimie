<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');



$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
$datetoday=date('y-m-d');

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") && (isset($_POST['message']))) {

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
	
	$classe_ID=$_POST['classe_ID'];
	$groupe_ID=$_POST['groupe_ID'];
	//message a tous les profs 
	if (isset($_POST['dest_all']) && $_POST['dest_all'] =='O'){$classe_ID=0;$groupe_ID=0;};
	
        //envole-240812 : utilisation de "NOW()" car "date_envoi" est maintenant un champ "DATETIME" pour une datation plus precise (#2221)
        // remplacement de NOW par datetime pour tenir compte des decalages horaires - ligne 17
		$insertSQL = sprintf("INSERT INTO cdt_message_contenu (message, prof_ID, date_envoi, date_fin_publier,online,dest_ID,pp_classe_ID,pp_groupe_ID)
                VALUES (%s,%u,%s,%s,%s,%u,%u,%u)",
                GetSQLValueString($_POST['message'], "text"),
                GetSQLValueString($_SESSION['ID_prof'], "int"),
                GetSQLValueString($date_envoi, "text"),
		GetSQLValueString($date_fin_publier, "text"),  
		GetSQLValueString($online, "text"),
		GetSQLValueString($_POST['dest_ID'], "int"),
		GetSQLValueString($classe_ID, "int"),
		GetSQLValueString($groupe_ID, "int")
		);
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
        $UID=mysqli_insert_id($conn_cahier_de_texte); 
        
        if($_POST['dest_ID']==0) { //aux eleves
                
                $insertSQL2= sprintf("INSERT INTO cdt_message_destinataire ( message_ID, classe_ID, groupe_ID )  VALUES ('%u', '%u', '%u');",$UID,$_POST['classe_ID'], $_POST['groupe_ID']);
                $Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));
                
        };
		
		if ($_POST['dest_ID']==2) { //aux profs
		
		    //  insertSQL2= sprintf("INSERT INTO cdt_message_destinataire_profs ( message_ID, classe_ID, groupe_ID )  VALUES ('%u', '%u', '%u');",$UID,$_POST['classe_ID'], $_POST['groupe_ID']);
             //   $Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));
		};
		
		
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
                        $insertSQL = sprintf("INSERT INTO cdt_message_fichiers ( message_ID, nom_fichier, prof_ID) VALUES (%u,%s,%u)",
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
	}
	// fin presence fichiers message 2 joint ****************************************************************
	
	
	$insertGoTo = "message_ajout.php";
	if($_POST['dest_ID']==2) {$insertGoTo.='?dest_profs=0';};
	header(sprintf("Location: %s", $insertGoTo));
};


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if(isset($_GET['dest_profs'])){
        $query_Rsmessage =sprintf("SELECT * FROM cdt_message_contenu,cdt_classe,cdt_groupe,cdt_prof WHERE cdt_message_contenu.dest_ID=2 AND cdt_message_contenu.prof_ID = %u AND cdt_message_contenu.prof_ID=cdt_prof.ID_prof AND cdt_message_contenu.pp_classe_ID=cdt_classe.ID_classe AND cdt_message_contenu.pp_groupe_ID=cdt_groupe.ID_groupe ORDER BY date_envoi,ID_message",$_SESSION['ID_prof']);
}
else
{
        $query_Rsmessage =sprintf("SELECT * FROM cdt_message_contenu,cdt_message_destinataire,cdt_classe,cdt_groupe,cdt_prof WHERE cdt_message_contenu.dest_ID<2 AND cdt_message_contenu.prof_ID = cdt_prof.ID_prof AND  cdt_prof.ID_prof= %u AND cdt_message_destinataire.classe_ID=cdt_classe.ID_classe AND cdt_message_destinataire.groupe_ID=cdt_groupe.ID_groupe AND cdt_message_contenu.ID_message=cdt_message_destinataire.message_ID ORDER BY date_envoi DESC,ID_classe ASC",$_SESSION['ID_prof']) ;
;};

$Rsmessage = mysqli_query($conn_cahier_de_texte, $query_Rsmessage) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmessage = mysqli_fetch_assoc($Rsmessage);
$totalRows_Rsmessage = mysqli_num_rows($Rsmessage);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsmessage3 ="
SELECT * FROM cdt_message_contenu,cdt_message_destinataire,cdt_classe,cdt_groupe,cdt_prof 
WHERE cdt_message_contenu.dest_ID <2 
AND cdt_message_contenu.prof_ID = cdt_prof.ID_prof  
AND cdt_message_destinataire.classe_ID=cdt_classe.ID_classe 
AND cdt_message_destinataire.groupe_ID=cdt_groupe.ID_groupe 
AND cdt_message_contenu.ID_message=cdt_message_destinataire.message_ID 
AND cdt_prof.droits>2
ORDER BY date_envoi DESC,ID_classe ASC";

$Rsmessage3 = mysqli_query($conn_cahier_de_texte, $query_Rsmessage3) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmessage3 = mysqli_fetch_assoc($Rsmessage3);
$totalRows_Rsmessage3 = mysqli_num_rows($Rsmessage3);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsmessage2 ="SELECT * FROM cdt_message_contenu,cdt_prof WHERE cdt_message_contenu.dest_ID=1 AND  cdt_message_contenu.online='O' AND cdt_message_contenu.prof_ID= cdt_prof.ID_prof AND cdt_prof.droits>2";

$Rsmessage2 = mysqli_query($conn_cahier_de_texte, $query_Rsmessage2) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmessage2 = mysqli_fetch_assoc($Rsmessage2);
$totalRows_Rsmessage2 = mysqli_num_rows($Rsmessage2);


if ((isset($_SESSION['prof_mess_pp'])&&($_SESSION['prof_mess_pp']=='Oui'))) //extension autorisation aux profs
{
	
	if ($_SESSION['droits']==2){ //enseignant avec un EDT
		$query_Rspp =sprintf("SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe,cdt_emploi_du_temps WHERE cdt_classe.ID_classe=cdt_emploi_du_temps.classe_ID AND prof_ID=%u ORDER BY nom_classe ASC",$_SESSION['ID_prof']);
	};
	if ($_SESSION['droits']==8){$query_Rspp ="SELECT * FROM cdt_classe,cdt_groupe";	};
}

else //prop principal seulement
	{
		$query_Rspp =sprintf("SELECT * FROM cdt_prof_principal,cdt_classe,cdt_groupe WHERE pp_prof_ID=%u AND pp_classe_ID=ID_classe AND pp_groupe_ID=ID_groupe",$_SESSION['ID_prof']);
	};
	

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Rspp = mysqli_query($conn_cahier_de_texte, $query_Rspp) or die(mysqli_error($conn_cahier_de_texte));
$row_Rspp = mysqli_fetch_assoc($Rspp);
$totalRows_Rspp = mysqli_num_rows($Rspp);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);

//parametre autorisant la diffusion d'un message par le professeur principal
$query_read2= "SELECT param_val FROM cdt_params WHERE param_nom='pp_diffusion' LIMIT 1;";
$result_read2 = mysqli_query($conn_cahier_de_texte, $query_read2);
$row2 = mysqli_fetch_row($result_read2);
$pp_diffusion = $row2[0];

if  ((isset($_SESSION['prof_mess_pp']))&&(isset($_GET['dest_profs']))){
	$query_Rsmessage_all =sprintf("SELECT * FROM cdt_message_contenu,cdt_prof WHERE cdt_message_contenu.dest_ID=2 AND cdt_message_contenu.prof_ID = %u AND cdt_message_contenu.prof_ID=cdt_prof.ID_prof AND cdt_message_contenu.pp_classe_ID=0 ORDER BY date_envoi,ID_message",$_SESSION['ID_prof']);
	$Rsmessage_all = mysqli_query($conn_cahier_de_texte, $query_Rsmessage_all) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsmessage_all = mysqli_fetch_assoc($Rsmessage_all);
	$totalRows_Rsmessage_all = mysqli_num_rows($Rsmessage_all);
};
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../styles/style_default.css" rel="stylesheet" type="text/css">
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />
<link href="../styles/info_bulles.css" rel="stylesheet" type="text/css" />
<style>
form{
	margin:0;
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

function confirmation(sup_message,ref)
{
	if (confirm("Voulez-vous supprimer r\351ellement ce message"+" N\260"+ref+sup_message+" ?")) { // Clic sur OK
		MM_goToURL('window','message_supprime.php?ID_message='+ref+'<?php if(isset($_GET['dest_profs'])){echo '&dest_profs=0';}?> ');
	}
}

</script>
</head>
<body >
<?php require_once ("../authentification/sessionVerif.php"); ?>
<table class="lire_bordure" width="95%" border="0" align="center" cellpadding="0" cellspacing="0" >
<tr class="lire_cellule_4">
<td >CAHIER DE TEXTES - <?php echo $_SESSION['identite']. ' - '?> Diffusion d'un message
<?php if  (isset($_GET['dest_profs'])){echo ' aux enseignants de mes &eacute;l&egrave;ves';} else { echo ' &agrave; mes &eacute;l&egrave;ves';}; ?></td>
<td ><div align="right"><a href="enseignant.php"><img src="../images/home-menu.gif" alt="Menu enseignant" title="Menu enseignant" width="26" height="20" border="0" /></a></div></td>
</tr>
<tr>
<td colspan="2" valign="top" align="center" class="lire_cellule_2" ><br />
<br />
<form onLoad= "formfocus()" method="POST"  name="form1" enctype="multipart/form-data" action="message_ajout.php" >
<table width="95%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td valign="top"><fieldset >
<legend align="top"><strong>Nouveau message
<?php if  (isset($_GET['dest_profs'])){echo ' aux enseignants de mes &eacute;l&egrave;ves';} else { echo ' &agrave; mes &eacute;l&egrave;ves';}; ?>
</strong></legend>
<table align="center" cellspacing="5">
<?php 
//afficher si (message d'un prof principal declare ET autorise a publier) OU (message d'un prof principal a destination d'eleves)
if ((($totalRows_Rspp>0)&&($pp_diffusion=="Oui"))||(!isset($_GET['dest_profs']))) {
        ?>
        <tr valign="baseline">
	<td align="center"><?php if  (isset($_GET['dest_profs'])){echo 'Aux enseignants de ';} else { echo 'Aux &eacute;l&egrave;ves de ';};
	
	?>
	<select name='classe_ID' id='classe_ID' >
	<?php	do {
		echo "<option value='".$row_Rspp["ID_classe"]."'>".$row_Rspp["nom_classe"]."</option>";
	}while($row_Rspp = mysqli_fetch_assoc($Rspp));	?>
	</select>
	<select name="groupe_ID" size="1" id="groupe_ID">
	<?php do {  ?>
		<option value="<?php echo $row_Rsgroupe['ID_groupe']?>"><?php echo $row_Rsgroupe['groupe']?></option>
		<?php
	} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
	$rows = mysqli_num_rows($Rsgroupe);
	if($rows > 0) {
		mysqli_data_seek($Rsgroupe, 0);
		$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
	};
	?>
	</select>
	<?php if  ((isset($_SESSION['prof_mess_pp']))&&(isset($_GET['dest_profs']))){
		//message envoye a tous les enseignants
		?>
		ou &agrave; tous les enseignants de mes &eacute;l&egrave;ves
		<input name="dest_all" type="checkbox" id="dest_all" value="O" >
		<?php
	};
	?>
	</td>
	</tr>
	<?php
}; ?>
<tr>
<td valign="top">
<?php 
if ((isset($_SESSION['affiche_xinha']))&&($_SESSION['affiche_xinha']==1)){
	include('../vie_scolaire/area_message.php');}
else { 
	include('../vie_scolaire/area_message_tiny.php');};
;?>


<p>
<textarea name="message" cols="70" rows="7" id="message" width="300" height= "80" >
<?php if ((isset($_SERVER['HTTP_USER_AGENT']))&&(preg_match('/Firefox/i',$_SERVER['HTTP_USER_AGENT']))){echo '<br />';}; 
?></textarea>
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
$date1_form=substr($date_fin_publier,8,2).'/'.substr($date_fin_publier,5,2).'/'.substr($date_fin_publier,0,4);
echo $date1_form;?>" size="10"/>
</em> &nbsp; </div>
<p></p>
<p>
<input name="dest_ID" type="hidden" value="<?php if  (isset($_GET['dest_profs'])){echo '2';}else {echo '0';};?>">
<input name="submit" type="submit" value="Cr&eacute;er ce nouveau message">
</p>
</div></td>
</tr>
</table>
</fieldset >
<input type="hidden" name="MM_insert" value="form1">
</form></td>
</tr>
</table>
<?php if ($totalRows_Rsmessage <>0){?>
	<p>&nbsp; </p>
	<table width="95%" border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
	<tr>
	<td class="Style6">R&eacute;f</td>
	<td class="Style6"><div align="center">
	<?php if  (isset($_GET['dest_profs'])) {echo "Mes messages aux&nbsp;enseignants de mes &eacute;l&egrave;ves ";} else { echo "
	Mes messages &agrave; mes &eacute;l&egrave;ves ";};?>
	</div></td>
	<td class="Style6">Doc.&nbsp;joints </td>
	<td class="Style6">Publi&eacute;</td>
	<td colspan="2" class="Style6">Destinataires</td>
	<td class="Style6">Cr&eacute;&eacute;&nbsp;le </td>
	<td class="Style6">Publi&eacute;&nbsp;jusqu'au&nbsp;</td>
	<td class="Style6">Auteur</td>
	<td class="Style6">&nbsp;</td>
	<td class="Style6">&nbsp;</td>
	<td class="Style6">&nbsp;</td>
	</tr>
	<?php do { 
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rs_fichiers_joints_form=sprintf("SELECT * FROM cdt_message_fichiers WHERE message_ID=%u",$row_Rsmessage['ID_message']);
		$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
		$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rs_modifmessage=sprintf("SELECT * FROM cdt_message_modif,cdt_prof WHERE ID_message=%u AND cdt_message_modif.prof_ID=cdt_prof.ID_prof",$row_Rsmessage['ID_message']);
		$Rs_modifmessage = mysqli_query($conn_cahier_de_texte, $query_Rs_modifmessage) or die(mysqli_error($conn_cahier_de_texte));
		$totalRows_Rs_modifmessage = mysqli_num_rows($Rs_modifmessage);
		?>
		<tr>
		<td class="tab_detail"><div align="center">
		
		<?php 
		if ($totalRows_Rs_modifmessage>0) {
			echo '<a href="#" class="tooltip">'.$row_Rsmessage['ID_message'].'<em>Message modifi&eacute; :<ul>';
			while ($row_Rs_modifmessage = mysqli_fetch_assoc($Rs_modifmessage)) {
				echo '<li> le '.$row_Rs_modifmessage['date_envoi'].' par ';
				echo $row_Rs_modifmessage['identite']==''?$row_Rs_modifmessage['nom_prof']:$row_Rs_modifmessage['identite'];
				echo '</li>'; 
			};
			echo '</ul></em></a>';
		} else {
			echo $row_Rsmessage['ID_message'];
		}
		mysqli_free_result($Rs_modifmessage);
		?>
		</div></td>
		<td class="tab_detail"><?php echo $row_Rsmessage['message']; ?></td>
		<td class="tab_detail"><?php 
		do {
			$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
			echo '<a href="../fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a><br />';
		} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
		?></td>
		<td class="tab_detail"><div align="center"><?php echo $row_Rsmessage['online']; ?></div></td>
		<td class="tab_detail"><?php if  (isset($_GET['dest_profs'])){echo 'Enseignants de ';} else { echo 'El&egrave;ves de ';}; echo $row_Rsmessage['nom_classe']; ?></td>
		<td class="tab_detail"><?php echo $row_Rsmessage['groupe']; ?></td>
		<td class="tab_detail"><?php echo substr($row_Rsmessage['date_envoi'],8,2).'/'.substr($row_Rsmessage['date_envoi'],5,2).'/'.substr($row_Rsmessage['date_envoi'],0,4); ?></td>
		<td class="tab_detail"><?php if($row_Rsmessage['date_fin_publier']<>'0000-00-00'){echo substr($row_Rsmessage['date_fin_publier'],8,2).'/'.substr($row_Rsmessage['date_fin_publier'],5,2).'/'.substr($row_Rsmessage['date_fin_publier'],0,4); }?></td>
		<td class="tab_detail"><?php echo $row_Rsmessage['nom_prof']; ?></td>
		<td class="tab_detail"><?php  if ($row_Rsmessage['email']<>''){ ?>
			<a href="mailto:<?php echo $row_Rsmessage['email'];?>"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant" title="Contacter l'enseignant" /></a>
		<?php ;};?></td>
		<td class="tab_detail"><?php if (($_SESSION['nom_prof']== $row_Rsmessage['nom_prof'])OR ($_SESSION['droits']==4)){?>
			<img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','message_modif.php?ID_message=<?php echo $row_Rsmessage['ID_message']; if(isset($_GET['dest_profs'])){echo '&dest_profs=0';} else { echo '&classe_ID='.$row_Rsmessage['classe_ID'].'&groupe_ID='.$row_Rsmessage['groupe_ID'];};
				?>');return document.MM_returnValue">
      <?php } else {echo '&nbsp;';};?>
      </td>
      <td class="tab_detail"><?php if (($_SESSION['nom_prof']== $row_Rsmessage['nom_prof'])OR ($_SESSION['droits']==4)){?>
      	      <img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer"  onClick= "return confirmation('<?php if ($totalRows_Rs_fichiers_joints_form>0){
      	      		      if ($totalRows_Rs_fichiers_joints_form==1){ echo ' et sa pi&egrave;ce jointe attach&eacute;e';} else {echo ' et ses '.$totalRows_Rs_fichiers_joints_form.' pi&egrave;ces jointes attach&eacute;es';}
      	      };?>','<?php echo $row_Rsmessage['ID_message']?>')" >
      <?php } else {echo '&nbsp;';};?>
      </td>
      </tr>
      	<?php } while ($row_Rsmessage = mysqli_fetch_assoc($Rsmessage)); ?>
      	</table>
<?php };?>
<p>&nbsp; </p>

<?php 

//tableau messages aux enseignants de mes eleves
if  ((isset($_SESSION['prof_mess_pp']))&&(isset($_GET['dest_profs']))&&( $totalRows_Rsmessage_all>0  )){?>
	<table width="95%" border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
	<tr>
	<td class="Style6">R&eacute;f</td>
	<td class="Style6"><div align="center">Mes messages aux&nbsp;enseignants de mes &eacute;l&egrave;ves </div></td>
	<td class="Style6">Doc.&nbsp;joints </td>
	<td class="Style6">Publi&eacute;</td>
	<td class="Style6">Cr&eacute;&eacute;&nbsp;le </td>
	<td class="Style6">Publi&eacute;&nbsp;jusqu'au </td>
	<td class="Style6">Auteur</td>
	<td class="Style6">&nbsp;</td>
	<td class="Style6">&nbsp;</td>
	<td class="Style6">&nbsp;</td>
	</tr>
	<?php do { 
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE message_ID=".$row_Rsmessage_all['ID_message'];
		$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
                $totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
                
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_Rs_modifmessage=sprintf("SELECT * FROM cdt_message_modif,cdt_prof WHERE ID_message=%u AND cdt_message_modif.prof_ID=cdt_prof.ID_prof",$row_Rsmessage_all['ID_message']);
                $Rs_modifmessage = mysqli_query($conn_cahier_de_texte, $query_Rs_modifmessage) or die(mysqli_error($conn_cahier_de_texte));
                $totalRows_Rs_modifmessage = mysqli_num_rows($Rs_modifmessage);
                ?>
		<tr>
		<td class="tab_detail"><div align="center">
                
                <?php 
                if ($totalRows_Rs_modifmessage>0) {
                        echo '<a href="#" class="tooltip">'.$row_Rsmessage_all['ID_message'].'<em>Message modifi&eacute; :<ul>';
                        while ($row_Rs_modifmessage = mysqli_fetch_assoc($Rs_modifmessage)) {
                                echo '<li> le '.$row_Rs_modifmessage['date_envoi'].' par ';
                                echo $row_Rs_modifmessage['identite']==''?$row_Rs_modifmessage['nom_prof']:$row_Rs_modifmessage['identite'];
				echo '</li>'; 
                        };
                        echo '</ul></em></a>';
                } else {
                        echo $row_Rsmessage_all['ID_message'];
                }
                mysqli_free_result($Rs_modifmessage);
                ?>
		</div></td>
		<td class="tab_detail"><?php echo $row_Rsmessage_all['message']; ?></td>
		<td class="tab_detail"><?php 
		do {
			$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
			echo '<a href="../fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a><br />';
		} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
		?></td>
		<td class="tab_detail"><div align="center"><?php echo $row_Rsmessage_all['online']; ?></div></td>
		<td class="tab_detail"><?php echo substr($row_Rsmessage_all['date_envoi'],8,2).'/'.substr($row_Rsmessage_all['date_envoi'],5,2).'/'.substr($row_Rsmessage_all['date_envoi'],0,4); ?></td>
		<td class="tab_detail"><?php if($row_Rsmessage_all['date_fin_publier']<>'0000-00-00'){echo substr($row_Rsmessage_all['date_fin_publier'],8,2).'/'.substr($row_Rsmessage_all['date_fin_publier'],5,2).'/'.substr($row_Rsmessage_all['date_fin_publier'],0,4); }?></td>
		<td class="tab_detail"><?php echo $row_Rsmessage_all['nom_prof']; ?></td>
		<td class="tab_detail"><?php  if ($row_Rsmessage_all['email']<>''){ ?>
			<a href="mailto:<?php echo $row_Rsmessage_all['email'];?>"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant" title="Contacter l'enseignant" /></a>
		<?php ;};?></td>
		<td class="tab_detail"><?php if (($_SESSION['nom_prof']== $row_Rsmessage_all['nom_prof'])OR ($_SESSION['droits']==4)){?>
			<img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','message_modif.php?ID_message=<?php echo $row_Rsmessage_all['ID_message'].'&dest_profs=0';?>');return document.MM_returnValue">
      <?php } else {echo '&nbsp;';};?>
      </td>
      <td class="tab_detail"><?php if (($_SESSION['nom_prof']== $row_Rsmessage_all['nom_prof'])OR ($_SESSION['droits']==4)){?>
      	      <img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer"  onClick= "return confirmation('<?php if ($totalRows_Rs_fichiers_joints_form>0){
      	      		      if ($totalRows_Rs_fichiers_joints_form==1){ echo ' et sa pi&egrave;ce jointe attach&eacute;e';} else {echo ' et ses '.$totalRows_Rs_fichiers_joints_form.' pi&egrave;ces jointes attach&eacute;es';}
      	      };?>','<?php echo $row_Rsmessage_all['ID_message']?>')" >
      <?php } else {echo '&nbsp;';};?>
      </td>
      </tr>
      	<?php } while ($row_Rsmessage_all = mysqli_fetch_assoc($Rsmessage_all)); 
      	mysqli_free_result($Rsmessage_all);
      	?>
      	</table>
      	<?php		
};		

if ($totalRows_Rsmessage3 <>0){?>
	<p>&nbsp; </p>
	
	<table width="95%" border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
	<tr>
	<td class="Style6">R&eacute;f</td>
	<td class="Style6"><div align="center">Messages&nbsp;du&nbsp;Resp.&nbsp;Etablissement&nbsp;ou&nbsp;de&nbsp;la&nbsp;Vie Scolaire</div></td>
	<td class="Style6">Doc.&nbsp;joints </td>
	<td class="Style6">Publi&eacute;</td>
	<td colspan="2" class="Style6">Destinataires</td>
	<td class="Style6">Cr&eacute;&eacute;&nbsp;le</td>
	<td class="Style6">Publi&eacute;&nbsp;jusqu'au</td>
	<td class="Style6">Auteur</td>
	<td class="Style6">&nbsp;</td>
	<td class="Style6">&nbsp;</td>
	<td class="Style6">&nbsp;</td>
	</tr>
	<?php do { 
		
		if ($row_Rspp['ID_classe']==$row_Rsmessage3['classe_ID']){echo $row_Rsmessage3['classe_ID'];};
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE message_ID=".$row_Rsmessage3['ID_message'];
		$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
                $totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
                
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_Rs_modifmessage=sprintf("SELECT * FROM cdt_message_modif,cdt_prof WHERE ID_message=%u AND cdt_message_modif.prof_ID=cdt_prof.ID_prof",$row_Rsmessage3['ID_message']);
                $Rs_modifmessage = mysqli_query($conn_cahier_de_texte, $query_Rs_modifmessage) or die(mysqli_error($conn_cahier_de_texte));
                $totalRows_Rs_modifmessage = mysqli_num_rows($Rs_modifmessage);
                ?>
		<tr>
		<td class="tab_detail"><div align="center">
                
                <?php 
                if ($totalRows_Rs_modifmessage>0) {
                        echo '<a href="#" class="tooltip">'.$row_Rsmessage3['ID_message'].'<em>Message modifi&eacute; :<ul>';
                        while ($row_Rs_modifmessage = mysqli_fetch_assoc($Rs_modifmessage)) {
                                echo '<li> le '.$row_Rs_modifmessage['date_envoi'].' par ';
                                echo $row_Rs_modifmessage['identite']==''?$row_Rs_modifmessage['nom_prof']:$row_Rs_modifmessage['identite'];
				echo '</li>'; 
                        };
                        echo '</ul></em></a>';
                } else {
                        echo $row_Rsmessage3['ID_message'];
                }
                mysqli_free_result($Rs_modifmessage);
                ?>
		</div></td>
		<td class="tab_detail"><?php echo $row_Rsmessage3['message']; ?></td>
		<td class="tab_detail"><?php 
		do {
			$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
			echo '<a href="../fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a><br />';
		} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
		?></td>
		<td class="tab_detail"><div align="center"><?php echo $row_Rsmessage3['online']; ?></div></td>
		<td class="tab_detail"><?php if  (isset($_GET['dest_profs'])){echo 'Enseignants de ';} else { echo 'El&egrave;ves de ';}; echo $row_Rsmessage3['nom_classe']; ?></td>
		<td class="tab_detail"><?php echo $row_Rsmessage3['groupe']; ?></td>
		<td class="tab_detail"><?php echo substr($row_Rsmessage3['date_envoi'],8,2).'/'.substr($row_Rsmessage3['date_envoi'],5,2).'/'.substr($row_Rsmessage3['date_envoi'],0,4); ?></td>
		<td class="tab_detail"><?php if($row_Rsmessage3['date_fin_publier']<>'0000-00-00'){echo substr($row_Rsmessage3['date_fin_publier'],8,2).'/'.substr($row_Rsmessage3['date_fin_publier'],5,2).'/'.substr($row_Rsmessage3['date_fin_publier'],0,4); }?></td>
		<td class="tab_detail"><?php echo $row_Rsmessage3['nom_prof']; ?></td>
		<td class="tab_detail"><?php  if ($row_Rsmessage3['email']<>''){ ?>
			<a href="mailto:<?php echo $row_Rsmessage3['email'];?>"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant" title="Contacter l'enseignant" /></a>
		<?php ;};?></td>
		<td class="tab_detail"><?php if (($_SESSION['nom_prof']== $row_Rsmessage3['nom_prof'])OR ($_SESSION['droits']==4)){?>
			<img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','message_modif.php?ID_message=<?php echo $row_Rsmessage3['ID_message']; if(isset($_GET['dest_profs'])){echo '&dest_profs=0';};?>');return document.MM_returnValue">
      <?php } else {echo '&nbsp;';};?>
      </td>
      <td class="tab_detail"><?php if (($_SESSION['nom_prof']== $row_Rsmessage3['nom_prof'])OR ($_SESSION['droits']==4)){?>
      	      <img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick= "return confirmation('<?php if ($totalRows_Rs_fichiers_joints_form>0){
      	      		      if ($totalRows_Rs_fichiers_joints_form==1){ echo ' et sa pi&egrave;ce jointe attach&eacute;e';} else {echo ' et ses '.$totalRows_Rs_fichiers_joints_form.' pi&egrave;ces jointes attach&eacute;es';}
      	      };?>','<?php echo $row_Rsmessage3['ID_message']?>')" >
      <?php } else {echo '&nbsp;';};?>
      </td>
      </tr>
      <?php 
      mysqli_free_result($Rs_fichiers_joints_form);
        } while ($row_Rsmessage3 = mysqli_fetch_assoc($Rsmessage3)); ?>
        </table>
<?php };


?>
<?php if ($totalRows_Rsmessage2>0) { ?>
	<p>&nbsp;</p>
	
	<table  width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
	<tr>
	<td class="Style6">Messages communiqu&eacute;s &agrave; tous les &eacute;l&egrave;ves de l'&eacute;tablissement par le Responsable Etablissement ou la Vie scolaire</td>
	</tr>
	<?php do { ?>
		<tr>
		<td class="tab_detail"><p>
		<?php 
                $date_envoi_form=substr($row_Rsmessage2['date_envoi'],8,2).'/'.substr($row_Rsmessage2['date_envoi'],5,2).'/'.substr($row_Rsmessage2['date_envoi'],2,2);
                
                echo '<span class="date_message">'.$date_envoi_form.' - '.$row_Rsmessage2['identite'].'</span><br/>'; 
                echo $row_Rsmessage2['message'];
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE message_ID=".$row_Rsmessage2['ID_message'];
                $Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
                $row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
                $totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
                if ($totalRows_Rs_fichiers_joints_form>0){
                	if ($totalRows_Rs_fichiers_joints_form>1){echo '<p>Documents joints : <br /> ';} else {echo '<p>Document joint : ';};
                	do {
                		$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
                		echo '<a href="./fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a><br />';
                	} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
                echo '</p>';};
                
                
                ?>
                </td>
                </tr>
                <?php 
                mysqli_free_result($Rs_fichiers_joints_form);
        } while ($row_Rsmessage2 = mysqli_fetch_assoc($Rsmessage2)); ?>
        </table>
<?php };?>
<p><a href="enseignant.php">Retour au Menu Enseignant &nbsp;&nbsp; <img src="../images/home-menu.gif" alt="Menu enseignant" width="26" height="20" border="0" /></a></p>
</div>
</body>
</html>
<?php
mysqli_free_result($Rsmessage);
mysqli_free_result($Rsmessage2);
mysqli_free_result($Rsmessage3);
mysqli_free_result($Rspp);
mysqli_free_result($Rsgroupe);
?>
