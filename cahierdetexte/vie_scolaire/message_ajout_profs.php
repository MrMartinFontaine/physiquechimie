<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>7)&&($_SESSION['droits']<>8)){ header("Location: ../index.php");exit;};

require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
$datetoday=date('y-m-d');
$envoi_ok=0;
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") && (isset($_POST['message']))) {
	
	if (isset($_POST['online']) && $_POST['online'] =='O'){
		$online='O';
		$date_fin_publier=substr($_POST['date_fin_publier'],6,4).substr($_POST['date_fin_publier'],3,2).substr($_POST['date_fin_publier'],0,2);
	} else {
		$online='N';
		$date_fin_publier='0000-00-00';
	};
	
	//envole-240812 : utilisation de "NOW()" car "date_envoi" est maintenant un champ "DATETIME" pour une datation plus precise (#2221)
	$insertSQL = sprintf(" INSERT INTO `cdt_message_contenu` ( `message` , `prof_ID` , `date_envoi` , `date_fin_publier` , `online`,`dest_ID`  )
		VALUES (%s, %s,NOW(),%s,%s,2)",
		GetSQLValueString($_POST['message'], "text"),
		GetSQLValueString($_SESSION['ID_prof'], "int"),
		//GetSQLValueString($datetoday, "text"),
		GetSQLValueString($date_fin_publier, "text"),
		GetSQLValueString($online, "text")
		);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	$UID=mysqli_insert_id($conn_cahier_de_texte); 
	
	$nblign=$_POST['nb_profs'];
	

	for ($i=0; $i<=255; $i++) { 
		$refprof='prof'.$i;
		
		if ((isset($_POST[$refprof])) && ($_POST[$refprof]=='on')){
			$insertSQL2= sprintf("INSERT INTO `cdt_message_destinataire_profs` ( `message_ID` , `prof_ID` )  VALUES ('%s', '%s');",$UID,$i);
			
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
	
	// fin presence fichiers  message 1 joint ****************************************************************
	
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
		$envoi_ok=1;
	};
	// fin presence fichiers message 2 joint ****************************************************************
	

	
	if (isset($_GET['ppliste'])){
		$insertGoTo = 'message_ajout_profs.php?affiche_mes=1&ppliste=1';} 
	else {
		$insertGoTo = "message_ajout_profs.php?affiche_mes=1"; 
	};
	header(sprintf("Location: %s", $insertGoTo));
};

if  (($envoi_ok==1)){
?>
<script type="text/JavaScript">
alert(' Le message a \351t\351 envoy\351');
</script>
<?php
};
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsmessage ="SELECT * FROM cdt_message_contenu,cdt_prof WHERE cdt_message_contenu.dest_ID=2 AND cdt_message_contenu.prof_ID = cdt_prof.ID_prof ORDER BY date_envoi,ID_message" ;
$Rsmessage = mysqli_query($conn_cahier_de_texte, $query_Rsmessage) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmessage = mysqli_fetch_assoc($Rsmessage);
$totalRows_Rsmessage = mysqli_num_rows($Rsmessage);

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
	alert("Il faut indiquer un destinataire. Cochez au moins une classe.");
	return false;
}

function confirmation(sup_message,ref)
{
	if (confirm("Voulez-vous r\351ellement supprimer ce message"+" N\260"+ref+sup_message+" ?")) { // Clic sur OK
		MM_goToURL('window','message_supprime.php?ID_message='+ref+'&dest_profs=0&affiche_mes=1<?php
		if (isset($_GET['ppliste'])){echo '&ppliste=1';} 
	;?>');
	}
}

</script>

</head>
<body>
<div id="">
<!--<div id="container3"> en remplacement du div ci-dessus pour un tableau moins large -->

<table class="lire_bordure" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
<tr class="lire_cellule_4">
<td >CAHIER DE TEXTES -
<?php if(isset($_SESSION['identite'])){echo $_SESSION['identite'];} ?>
 - Diffusion d'un message aux <?php if (isset($_GET['ppliste'])){echo 'professeurs principaux';} else {echo 'enseignants';};?></td>
<td ><div align="right"><a href="<?php 
if ($_SESSION['droits']==1) {echo '../administration/index.php';}; 
if ($_SESSION['droits']==2) {echo '../enseignant/enseignant.php';}; 
if ($_SESSION['droits']==4) {echo '../direction/direction.php';};
if ($_SESSION['droits']==3 || $_SESSION['droits']==7) {echo 'vie_scolaire.php';};
if ($_SESSION['droits']==8) {echo '../enseignant/enseignant.php';};
?>"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></div></td>
</tr>
<tr>
<td colspan="2" valign="top" class="lire_cellule_2" >
<br />

<a href="<?php if (isset($_GET['affiche_mes'])) {
	echo 'message_ajout_profs.php';	
	if (isset($_GET['ppliste'])){echo '?ppliste=1';}
	;}
	else {
	echo 'message_ajout_profs.php?affiche_mes=1';if (isset($_GET['ppliste'])){echo '&ppliste=1';};
	};
	if (isset($_GET['tri'])){echo '&tri='.$_GET['tri'];};
?>"><input name="af" id="af"  type="submit" value="<?php if (isset($_GET['affiche_mes'])) {echo 'Masquer'; } else {echo 'Afficher';};?> les messages d&eacute;j&agrave; publi&eacute;s"></a>
<br />
<br />

<?php if ($totalRows_Rsmessage <>0){
if (isset($_GET['affiche_mes'])){
?>
	<table border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
	<tr>
	<td class="Style6"><div align="center">R&eacute;f&nbsp;</div></td>
	<td class="Style6"><div align="center">Messages d&eacute;j&agrave; diffus&eacute;s</div></td>
	<td class="Style6"><div align="center">Publi&eacute;&nbsp;</div></td>
	<td class="Style6"><div align="center">Cr&eacute;&eacute;&nbsp;le&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
	<td class="Style6"><div align="center">Publi&eacute;&nbsp;jusqu'au&nbsp; </div></td>
	<td class="Style6"><div align="center">Auteur&nbsp;</div></td>
	<td class="Style6">&nbsp;</td>
	<td class="Style6">&nbsp;</td>
	<td class="Style6">&nbsp;</td>
	</tr>
	<?php do { ?>
		<tr>
		<td class="tab_detail"><div align="right"><?php echo $row_Rsmessage['ID_message']; ?></div></td>
		<td class="tab_detail"><?php echo $row_Rsmessage['message']; ?>
		<?php
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE cdt_message_fichiers.message_ID=".$row_Rsmessage['ID_message'];
                $Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
                $row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
                $totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
                if ($totalRows_Rs_fichiers_joints_form>0){
                	if ($totalRows_Rs_fichiers_joints_form>1){echo '<p>Documents joints : <br /> ';} else {echo '<p>Document joint : <br /> ';};
                	do {
                		$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
                		echo '<a href="../fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a><br />';
                	} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
                echo '</p>';};?>                    </td>
                <td class="tab_detail"><div align="center"><?php echo $row_Rsmessage['online']; ?></div></td>
                <td class="tab_detail"><div align="center"><?php echo substr($row_Rsmessage['date_envoi'],8,2).'-'.substr($row_Rsmessage['date_envoi'],5,2).'-'.substr($row_Rsmessage['date_envoi'],0,4); ?></div></td>
                <td class="tab_detail"><div align="center"><?php if($row_Rsmessage['date_fin_publier']<>'0000-00-00'){echo substr($row_Rsmessage['date_fin_publier'],8,2).'-'.substr($row_Rsmessage['date_fin_publier'],5,2).'-'.substr($row_Rsmessage['date_fin_publier'],0,4); }?></div></td>
                <td class="tab_detail"><div align="center"><?php echo $row_Rsmessage['nom_prof']; ?></div></td>
                <td class="tab_detail"><?php  if ($row_Rsmessage['email']<>''){ ?>
                	<a href="mailto:<?php echo $row_Rsmessage['email'];?>"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant" title="Contacter l'enseignant" /></a>
                <?php ;};?></td>
                <td class="tab_detail"><?php if (($_SESSION['nom_prof']== $row_Rsmessage['nom_prof'])OR ($_SESSION['droits']==4)){?>
                	<img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','message_modif_profs.php?ID_message=<?php echo $row_Rsmessage['ID_message'];if (isset($_GET['ppliste'])){echo '&ppliste=1';};?>');return document.MM_returnValue">
                      <?php } else {echo '&nbsp;';};?>                    </td>
                      <td class="tab_detail"><?php if (($_SESSION['nom_prof']== $row_Rsmessage['nom_prof'])OR ($_SESSION['droits']==4)){?>
                      	      <img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick= "return confirmation('<?php if ($totalRows_Rs_fichiers_joints_form>0){
                      	      	      if ($totalRows_Rs_fichiers_joints_form==1){ echo ' et sa pi&egrave;ce jointe attach&eacute;e';} else {echo ' et ses '.$totalRows_Rs_fichiers_joints_form.' pi&egrave;ces jointes attach&eacute;es';}
                      };?>','<?php echo $row_Rsmessage['ID_message']?>')" >
                      <?php } else {echo '&nbsp;';};?>                    </td>
                  </tr>
<?php } while ($row_Rsmessage = mysqli_fetch_assoc($Rsmessage)); ?>
</table>

<?php 
};
};?>





<form onLoad= "formfocus()" method="post"  name="form1" enctype="multipart/form-data" action="message_ajout_profs.php?affiche_mes=1<?php if (isset($_GET['ppliste'])){echo '&ppliste=1';};?>" onsubmit="return verifier()">
<fieldset>
<legend align="top"><strong><img src="../images/email.gif" width="16" height="14"> Nouveau message aux <?php if (isset($_GET['ppliste'])){echo 'professeurs principaux';} else {echo 'enseignants';};?> </strong></legend>


<table><tr valign="top"><td>
<?php
if (isset($_GET['ppliste'])){?>
<p align="center"><a href="message_ajout_profs.php?ppliste=1&tri=pp
<?php if (isset($_GET['affiche_mes'])){echo '&affiche_mes='.$_GET['affiche_mes'];};?>">Trier par enseignant</a> - <a href="message_ajout_profs.php?ppliste=1&tri=cl<?php if (isset($_GET['affiche_mes'])){echo '&affiche_mes='.$_GET['affiche_mes'];};?>
">Trier par nom de classe </a></p>

<?php 
} else {echo '<br />';};
?>
<table border="0" align="center" class="bordure">
<tr>
<td class="Style6"><div align="left"><img src="../images/user_absent.png">&nbsp;&nbsp;&nbsp;Destinataires</div></td>
<?php
if (isset($_GET['ppliste'])){?>
<td class="Style6">Classe</td><td class="Style6">Groupe</td>
<?php 
};
?>
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
<?php do { ?>
	<tr>
	<td class="tab_detail"><div align="left"> <?php echo $row_RsProf['identite']<>'' ? '&nbsp;'.$row_RsProf['identite'].'&nbsp;('.$row_RsProf['nom_prof'].')' : '&nbsp;'.$row_RsProf['nom_prof']; ?> </div></td>
	<?php
if (isset($_GET['ppliste'])){?>
<td class="tab_detail">
<div align="center"><?php echo $row_RsProf['nom_classe']; ?></div>
		
</td>
<td class="tab_detail"><div align="center"><?php echo $row_RsProf['groupe']; ?></div></td>
<?php 
};
?>
	
	<td class="tab_detail"><div align="center">
	<input type="checkbox" name="<?php echo 'prof'.$row_RsProf['ID_prof']; ?>"   id="<?php echo 'prof'.$row_RsProf['ID_prof']; ?>" onclick=decocherTout() value="on">
	</div></td>
	</tr>
<?php } while ($row_RsProf = mysqli_fetch_assoc($RsProf)); ?>
</table>
</td>

<td>



<table align="center" cellspacing="5">
<tr valign="top">
<td>
<?php 
if ((isset($_SESSION['affiche_xinha']))&&($_SESSION['affiche_xinha']==1)){
	include('area_message.php');}
else { 
	include('area_message_tiny.php');};

if (isset($_GET['ppliste'])){echo '<p>&nbsp;</p>';}
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
        <script>
         $(function() {
        	$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
			$.datepicker.setDefaults($.datepicker.regional['fr']);
        	$('#date_fin_publier').datepicker({firstDay:1});
        });
        </script>
<input name="online" type="checkbox" id="online" value="O" checked>
jusqu'au
<input name='date_fin_publier' type='text' id='date_fin_publier' value="<?php 
$date_fin_publier=date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+15,  date("Y")));			  
$date1_form=substr($date_fin_publier,8,2).'-'.substr($date_fin_publier,5,2).'-'.substr($date_fin_publier,0,4);
echo $date1_form;?>" size="10"/>
</em>
&nbsp; </div>
<p>
<input name="submit" type="submit" value="Cr&eacute;er ce nouveau message">
</p>
<p><br>
<br>
</a></p>
 <p align="center"> 

<a href="<?php 
if ($_SESSION['droits']==1) {echo '../administration/index.php';};
if ($_SESSION['droits']==2) {echo '../enseignant/enseignant.php';};
if ($_SESSION['droits']==4) {echo '../direction/direction.php';};
if ($_SESSION['droits']==3 || $_SESSION['droits']==7) {echo 'vie_scolaire.php';};
if ($_SESSION['droits']==8) {echo '../enseignant/enseignant.php';};

?>">Retour au Menu &nbsp;<img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></p>
</div></td>
</tr>
</table>

</td>
</tr>
</table>
<input type="hidden" name="nb_profs" value="<?php echo $totalRows_RsProf;?>">
<input type="hidden" name="MM_insert" value="form1">

</form>
<p>&nbsp;</p>
</tr>
</table>
</td>
</tr>
</table>

<p>&nbsp;</p>

</div>
</DIV>
</body>
</html>
<?php
mysqli_free_result($Rsmessage);
mysqli_free_result($RsProf);

?>
