<?php
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)) { header("Location: ../index.php");exit;};

require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
$datetoday=date('y-m-d');
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") && (isset($_POST['nom_niv']))) {
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$insertSQL= sprintf("INSERT INTO cdt_niveau ( nom_niv , commentaire_niv)  VALUES ('%s', '%s');",$_POST['nom_niv'],$_POST['commentaire_niv']);
	$Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	
	$UID=mysqli_insert_id($conn_cahier_de_texte); 
	
	$nblign=$_POST['nb_classes'];
        
	for ($i=0; $i<=255; $i++) { 
		$refclasse='classe'.$i;
		$refgroupe='groupe'.$i;
		if (isset($_POST[$refclasse])&&(isset($_POST[$refgroupe])) &&($_POST[$refclasse]=='on')){
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$insertSQL2= sprintf("INSERT INTO cdt_niveau_classe (niv_ID, classe_ID, groupe_ID)  VALUES ('%u', '%u', '%u');",$UID,$i, $_POST[$refgroupe]);
			
			$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));
		}//du if
	}//du for
	
	if (isset($_POST['niv_eleves'])){
		
		foreach($_POST['niv_eleves'] as $niv_eleve) {
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$insertSQL_ele_niv = sprintf("INSERT INTO ele_niv (ID_ele, ID_niv)  VALUES ('%u', '%u')", GetSQLValueString($niv_eleve, "int") , GetSQLValueString($UID, "int"));
			$Result_ele_niv = mysqli_query($conn_cahier_de_texte, $insertSQL_ele_niv) or die(mysqli_error($conn_cahier_de_texte));
		}
	}     
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsniv = "SELECT * FROM cdt_niveau ";
$Rsniv = mysqli_query($conn_cahier_de_texte, $query_Rsniv) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsniv = mysqli_fetch_assoc($Rsniv);
$totalRows_Rsniv = mysqli_num_rows($Rsniv); 

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
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/utils.js"></script>
<style>
form{
	margin:5;
	padding:0;
}

.bordure_grise {
	border: 1px solid #CCCCCC;
}
</style>
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



</script>
</head>
<body >
<?php 

if ($totalRows_RsClasse>0){ ?>
<div id="">
<!--<div id="container3"> en remplacement du div ci-dessus pour un tableau moins large -->
<p>
<table class="lire_bordure" width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
<tr class="lire_cellule_4">
<td >Gestion des niveaux de classes </td>
<td ><div align="right">
<a href="
<?php if ($_SESSION['droits']==3) {echo 'vie_scolaire.php';}else if ($_SESSION['droits']==4){echo '../direction/direction.php';}?>">
<a href="<?php switch ( $_SESSION['droits'] )   {
				case 1 : echo '../administration/index.php'; break ;
				case 3 : echo 'vie_scolaire.php'; break ;
				case 4 : echo '../direction/direction.php'; break ;
				} ?>"> <img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></div>
				</td>
</tr>
<tr>
<td colspan="2" valign="top" class="lire_cellule_2" ><br />
  <div align="left"style="background:#F0EDE5;margin:10px;padding:10px"><img src="../images/lightbulb.png">&nbsp;Vous pouvez d&eacute;finir ici des niveaux de classes (par exemple, &quot;Ensemble des classes de seconde&quot;). Cela facilitera ult&eacute;rieurement la diffusion de messages vers cet ensemble de classes. </div>
  <br />
<form onLoad= "formfocus()" method="post"  name="form1" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>" onsubmit="return verifier()">
<table width="100%" border="0" cellspacing="5" cellpadding="0">
<tr>
<td valign="top" >

<table border="0" align="center" cellpadding="0" cellspacing="0" class="bordure">
<tr>
<td class="Style6"><div align="center"><strong>Classes</strong></div></td>
<td class="Style6">
<SCRIPT>
function cocherTout(etat)
{
	var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
	for(var i=0; i<cases.length; i++)     // on les parcourt
		if(cases[i].type == 'checkbox')     // si on a une checkbox...
		cases[i].checked = etat;     // ... on la coche ou non
}

function decocherTout()
{
	var cases = document.getElementsByTagName('input');   // on recupere tous les INPUT
	cases[0].checked = false;     // ... on decoche la premiere, le TOUS
}
</SCRIPT>     
<div align="center"><strong>Tout</strong>
<input type="checkbox" name="checkbox" id="tousaucun" onclick=cocherTout(this.checked) value="ok" ></div></td>
<td class="Style6"><div align="center"><strong>Groupes</strong></td></div>
</tr>
<?php 
do { ?>
	<tr>
	<td class="tab_detail"><div align="center"><?php echo $row_RsClasse['nom_classe']; ?></div></td>
	<td class="tab_detail"><div align="center">
	<input type="checkbox" name="<?php echo 'classe'.$row_RsClasse['ID_classe']; ?>"   id="<?php echo 'classe'.$row_RsClasse['ID_classe']; ?>"  value="on" <?php
	 ?> >
	</div></td>
	<td class="tab_detail"><select id="<?php echo 'groupe'.$row_RsClasse['ID_classe']; ?>" name="<?php echo 'groupe'.$row_RsClasse['ID_classe']; ?>" size="1" class="menu_deroulant" id=" <?php echo 'groupe'.$row_RsClasse['ID_classe']; ?>">
	<?php do {  ?>
		<option value="<?php echo $row_Rsgroupe['ID_groupe']?>"><?php echo $row_Rsgroupe['groupe']?></option>
		<?php
	} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
	$rows = mysqli_num_rows($Rsgroupe);
	if($rows > 0) {
		mysqli_data_seek($Rsgroupe, 0);
	        $row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
	};?>
	</select>
	</td>
	</tr>
<?php } while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)); ?>
</table></td>
<td valign="top"><?php if($totalRows_Rsniv<>0){ ?>
	<table width="700" border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
	<tr>
	<td class="Style6">R&eacute;f</td>
	<td class="Style6"><div align="center">Nom du niveau</div></td>
	<td class="Style6">Commentaire</td>
	<td class="Style6">Editer les classes</td>
	<td class="Style6">Supprimer niveau</td>
	</tr>
	<?php 
	do { ?>
		<tr>
		<td class="tab_detail"><?php echo $row_Rsniv['ID_niv']; ?></td>
		<td class="tab_detail"><?php echo $row_Rsniv['nom_niv']; ?></td>
		<td class="tab_detail"><?php echo $row_Rsniv['commentaire_niv']; ?></td>
		<td class="tab_detail"><div align="center"><img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','niveau_classe_modif.php?ID_niv=<?php echo $row_Rsniv['ID_niv']; ?>');return document.MM_returnValue"> </div></td>
		<td class="tab_detail"><div align="center"><img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick= "MM_goToURL('window','niveau_classe_supprime.php?ID_niv=<?php echo $row_Rsniv['ID_niv']; ?>');return document.MM_returnValue" > </div></td>
		</tr>
	<?php } while ($row_Rsniv = mysqli_fetch_assoc($Rsniv)); ?>
	</table>
	<br />
	<?php 
}
?>
<blockquote><blockquote>&nbsp;</blockquote>
</blockquote><p>&nbsp;</p>
	<div align="center">
	<fieldset style="width : 90%">
	<legend align="top"><strong>Cr&eacute;er un nouveau niveau</strong></legend>
	<table align="center" cellspacing="5">
	<tr valign="baseline">
	<td valign="top"><p>Libell&eacute; du niveau <br>
	<input name="nom_niv" type="text" size="50" >
	</p>
	<p>Commentaire (facultatif) <br />
	<textarea name="commentaire_niv" cols="70" rows="2" id="message" width="200" height= "80" ></textarea>
	</p>
	<p>


<br />
	<p>  
	<input name="submit" type="submit" value="Cr&eacute;er ce nouveau niveau">
	</p></td>
	</tr>
	</table>
	<input type="hidden" name="nb_classes" value="<?php echo $totalRows_RsClasse;?>">
	<input type="hidden" name="MM_insert" value="form1">
	</fieldset>
	</div>
      </form></td>
        </tr>
        </table>
        </p>
        </div>
        </DIV>
		<?php } else { echo "<br><br><p align =\"center\"> Aucune classe n'est actuellement d&eacute;finie.</p>";};?>
        </body>
        </html>
        <?php
        mysqli_free_result($RsClasse);
        mysqli_free_result($Rsgroupe);
        ?>
