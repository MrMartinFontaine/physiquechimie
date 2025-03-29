<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>7)){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

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

if (isset($_GET['sup'])&&($_GET['sup']==1)){
	//on efface de la table fichiers_joints
	$deleteSQL = "DELETE FROM cdt_message_fichiers WHERE cdt_message_fichiers.ID_mesfich=".$_GET['ID_mesfich'];
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	
	$fichier = '../fichiers_joints_message/'.$_GET['nom_fichier'];
	unlink($fichier);	
	$insertGoTo = "message_modif.php?ID_message=".$_GET['ID_message'];
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
	
	if ((isset($_POST['checkbox']))&&($_POST['checkbox']==1)){$dest_ID=1;}else{$dest_ID=0;};

	//envole-240812 : utilisation de "NOW()" car "date_envoi" est maintenant un champ "DATETIME" pour une datation plus precise (#2221)
	$updateSQL = sprintf(" UPDATE `cdt_message_contenu` SET message =%s , prof_ID=%u , date_envoi=NOW() , date_fin_publier=%s , online=%s , dest_ID=%u WHERE ID_message=%u ",
		GetSQLValueString($_POST['message'], "text"),
		GetSQLValueString($_SESSION['ID_prof'], "int"),
		//GetSQLValueString($datetoday, "text"),
		GetSQLValueString($date_fin_publier, "text"),
		GetSQLValueString($online, "text"),
		$dest_ID,
		GetSQLValueString($_GET['ID_message'],"int")  
		);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
	

  $UID= $_GET['ID_message'];	
	//on efface
	if ((isset($_GET['ID_message'])) && ($_GET['ID_message'] != "")) {
		$deleteSQL = sprintf("DELETE FROM cdt_message_destinataire WHERE message_ID=%u",
			GetSQLValueString($_GET['ID_message'], "int"));
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
		
	}

for ($i=1; $i<=$totalRows_RsClasse; $i++) { 
  $refclassedest='classedest'.$i;
  $refgroupedest='groupedest'.$i;

if (isset($_POST[$refclassedest])&&(isset($_POST[$refgroupedest])) &&($_POST[$refclassedest]=='on'))
{
$insertSQL2= sprintf("INSERT INTO `cdt_message_destinataire` ( `message_ID` , `classe_ID` , `groupe_ID` )  VALUES ('%u', '%u', '%s');",$UID,$indcl_id[$i], $_POST[$refgroupedest]);

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
        
        $insertGoTo = "message_ajout.php?tri=auteur";
        header(sprintf("Location: %s", $insertGoTo));
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifMessage =sprintf("SELECT * FROM cdt_message_contenu,cdt_prof WHERE ID_message=%u AND cdt_message_contenu.prof_ID = cdt_prof.ID_prof ",$_GET['ID_message'] );
$RsModifMessage = mysqli_query($conn_cahier_de_texte, $query_RsModifMessage) or die(mysqli_error($conn_cahier_de_texte));
$row_RsModifMessage = mysqli_fetch_assoc($RsModifMessage);
$totalRows_RsModifMessage = mysqli_num_rows($RsModifMessage);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsdest =sprintf("SELECT * FROM cdt_message_destinataire WHERE message_ID=%u ",$_GET['ID_message'] );
$Rsdest = mysqli_query($conn_cahier_de_texte, $query_Rsdest) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsdest = mysqli_fetch_assoc($Rsdest);
$totalRows_Rsdest = mysqli_num_rows($Rsdest);

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
		MM_goToURL('window','message_modif.php?ID_message=<?php echo $_GET['ID_message']; ?>&ID_mesfich=<?php echo $row_Rs_fichiers_joints_form['ID_mesfich'];?>&nom_fichier=<?php echo $row_Rs_fichiers_joints_form['nom_fichier'] ;?>&sup=1');
	}
}

</script>
</head>


<body>
<div id="msg2"> </div> <!-- Important - Ne pas supprimer(ajax_niveau_even_dest)  -->     
<div id="">

<p>
<table class="lire_bordure" width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
<tr class="lire_cellule_4">
<td >CAHIER DE TEXTES - VIE SCOLAIRE - Diffusion d'un message au travers des cahiers de textes</td>
<td ><div align="right"><a href="<?php if ($_SESSION['droits']==1) {echo '../administration/index.php';}; if ($_SESSION['droits']==4) {echo '../direction/direction.php';};if ($_SESSION['droits']==3 || $_SESSION['droits']==7) {echo 'vie_scolaire.php';};?>"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></div></td>
</tr>
<tr>
<td colspan="2" valign="top" class="lire_cellule_2" ><br />
<br />
<form onLoad= "formfocus()" method="post" enctype="multipart/form-data" name="form1" action="<?php echo $editFormAction; ?>" onsubmit="return verifier()">
<table width="100%" border="0" cellspacing="5" cellpadding="0">
<tr>
<td valign="top">

<table align="center" border="0" cellspacing="0" cellpadding="0">
                <!--521 > 561 -->
                <tr>
                  <td valign="top">
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
                        <td class="Sty le6">Toutes </td>
                      </tr>
                      <?php for ($i=1; $i<=$totalRows_RsClasse; $i++) { ?>
                      <tr>
                        <td class="tab_detail"><div align="left">  <?php echo $indcl_nom[$i] ; ?></div></td>
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

 
 </td>
                  <!-- fin zone selection classe -->
                </tr>
      </table>


</td>
<td valign="top"><br />
<p align="center" class="Style70">Modification d'un  message</p>
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
<a href="message_ajout.php?tri=auteur">Menu gestion des messages</a></p>
</div></td>
</tr>
</table>
<input type="hidden" name="nb_classes" value="<?php echo $totalRows_RsClasse;?>">
<input type="hidden" name="MM_update" value="form1">
</form></td>
</tr>
</table>
</p>
<p> </p>

</DIV>
</body>
</html>
<?php
mysqli_free_result($RsModifMessage);
mysqli_free_result($RsClasse);
mysqli_free_result($Rsdest);
mysqli_free_result($Rsgroupe);
mysqli_free_result($Rsniv);
?>
