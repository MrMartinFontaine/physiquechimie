<?php
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};

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

$i=1;
do
{
$indcl_id[$i]=$row_RsClasse['ID_classe'];
$i=$i+1;
}while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)) ;

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") && (isset($_POST['nom_gic']))) {

  if ($_SESSION['droits']==1){$num_prof=$_POST['num_prof'];} else {$num_prof=$_SESSION['ID_prof'];};
  
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  		$nom_gic= str_replace(array("/", "&", "\'"), "-",$_POST['nom_gic']);
  		$nom_gic= trim(str_replace('"',' ',$nom_gic));
  		$nom_gic= trim(str_replace("'","-",$nom_gic));
        $insertSQL= sprintf("INSERT INTO cdt_groupe_interclasses (prof_ID , nom_gic , commentaire_gic)  VALUES ('%u', '%s', '%s');",$num_prof,$nom_gic,$_POST['commentaire_gic']);
        $Result = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
        
        
	$UID=mysqli_insert_id($conn_cahier_de_texte); 
	
	$nblign=$_POST['nb_classes'];
        
for ($i=1; $i<=$totalRows_RsClasse; $i++) {  
		$refclasse='classe'.$i;
		$refgroupe='groupe'.$i;

		if (isset($_POST[$refclasse])&&(isset($_POST[$refgroupe])) &&($_POST[$refclasse]=='on')){
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$insertSQL2= sprintf("INSERT INTO cdt_groupe_interclasses_classe (gic_ID, classe_ID, groupe_ID)  VALUES ('%u', '%u', '%u');",$UID,$indcl_id[$i], $_POST[$refgroupe]);

			$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));
		}//du if
	}//du for
	
	if (isset($_POST['gic_eleves'])){
		
		foreach($_POST['gic_eleves'] as $gic_eleve) {
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$insertSQL_ele_gic = sprintf("INSERT INTO ele_gic (ID_ele, ID_gic)  VALUES ('%u', '%u')", GetSQLValueString($gic_eleve, "int") , GetSQLValueString($UID, "int"));
			$Result_ele_gic = mysqli_query($conn_cahier_de_texte, $insertSQL_ele_gic) or die(mysqli_error($conn_cahier_de_texte));
		}
	}     
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if ($_SESSION['droits']==1){
$query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE prof_ID = %u ",$_GET['num_prof']);
} else {
$query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE prof_ID = %u ",$_SESSION['ID_prof']);
}
$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgic = mysqli_fetch_assoc($Rsgic);
$totalRows_Rsgic = mysqli_num_rows($Rsgic);




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
<div id="">
<!--<div id="container3"> en remplacement du div ci-dessus pour un tableau moins large -->
<p>
<table class="lire_bordure" width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
<tr class="lire_cellule_4">
<td ><?php echo $_SESSION['identite']; ?> - Gestion des regroupements d'&eacute;l&egrave;ves issus de plusieurs classes <?php if ($_SESSION['droits']==1){echo ' pour l\'enseignant '.$_GET['nom_prof'];};?></td>
<td ><div align="right"><a href="<?php if (($_SESSION['droits']==2)||($_SESSION['droits']==8))  {echo 'enseignant.php';};
if (($_SESSION['droits']==1)||($_SESSION['droits']==3)){echo '../inc/regroupement_liste.php';};?>"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></div></td>
</tr>
<tr>
<td colspan="2" valign="top" class="lire_cellule_2" ><br />
<br />
<form onLoad= "formfocus()" method="post"  name="form1" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>" onsubmit="return verifier()">
<table width="100%" border="0" cellspacing="5" cellpadding="0">
<tr>
<td valign="top" class="tab_detail_gris">
<table border="0" align="center" cellpadding="0" cellspacing="0" class="bordure">
<tr>
<td class="tab_detail_gris"><div align="center"><strong>El&egrave;ves venant de</strong></div></td>
<td class="tab_detail_gris">
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
<td class="tab_detail_gris"><div align="center"><strong>Groupes</strong></td></div>
</tr>
<?php 
mysqli_data_seek($RsClasse, 0);

$i=1;

while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)){
?>
	<tr>
	<td class="tab_detail"><div align="center"><?php echo $row_RsClasse['nom_classe']; ?></div></td>
	<td class="tab_detail"><div align="center">
	<input type="checkbox" name="<?php echo 'classe'.$i; ?>"   id="<?php echo 'classe'.$row_RsClasse['ID_classe']; ?>"  value="on" <?php
	if ((isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui'))	{
		echo 'onclick="majElevesListe();"';
	} ?> >
	
	</div></td>
	<td class="tab_detail">
	
	<select 
	id="<?php echo 'groupe'.$row_RsClasse['ID_classe']; ?>" name="<?php echo 'groupe'.$i; ?>" size="1" class="menu_deroulant" >
	
	
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
<?php 
$i=$i+1;
} ; ?>
</table></td>
<td valign="top"><?php if($totalRows_Rsgic<>0){ ?>
	<table width="700" border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
	<tr>
	<td class="Style6">R&eacute;f</td>
	<td class="Style6"><div align="center">Nom du regroupement</div></td>
	<td class="Style6">Commentaire</td>
	<td class="Style6">Editer les classes</td>
	<td class="Style6">Supprimer regroupement</td>
	</tr>
	<?php 
	do { ?>
		<tr>
		<td class="tab_detail"><?php echo $row_Rsgic['ID_gic']; ?></td>
		<td class="tab_detail"><?php echo $row_Rsgic['nom_gic']; ?></td>
		<td class="tab_detail"><?php echo $row_Rsgic['commentaire_gic']; ?></td>
		<td class="tab_detail"><div align="center"><img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','groupe_interclasses_modif.php?ID_gic=<?php echo $row_Rsgic['ID_gic']; ?>');return document.MM_returnValue"> </div></td>
		<td class="tab_detail"><div align="center"><img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick= "MM_goToURL('window','groupe_interclasses_supprime.php?ID_gic=<?php echo $row_Rsgic['ID_gic']; ?>');return document.MM_returnValue" > </div></td>
		</tr>
	<?php } while ($row_Rsgic = mysqli_fetch_assoc($Rsgic)); ?>
	</table>
	<br />
	<?php 
}
?>
<blockquote><blockquote>
<div align="left"style="background:#F0EDE5;margin:10px;padding:10px"><img src="../images/lightbulb.png">&nbsp;Il est important de comprendre la diff&eacute;rence entre Regroupement et Groupe :<br>
  <br>
* <strong>Regroupement</strong> (&eacute;l&egrave;ves issus de plusieurs classes, pour une option par exemple). 
Ils sont d&eacute;finis par l&rsquo;enseignant, pr&eacute;alablement &agrave; la saisie de son emploi du temps. <br><br>
* <strong> Groupes</strong> (libell&eacute;s sp&eacute;cifiques &agrave; l&rsquo;&eacute;tablissement permettant d&rsquo;identifier des groupes d&rsquo;&eacute;l&egrave;ves au sein d&rsquo;une m&ecirc;me classe, comme par exemple groupe A et groupe B.) 
	Ils sont d&eacute;finis par l&rsquo;administrateur pour l&rsquo;ensemble de l&rsquo;&eacute;tablissement.</div>
	</blockquote></blockquote><p>&nbsp;</p>
	<div align="center">
	<fieldset style="width : 90%">
	<legend align="top"><strong>Cr&eacute;er un nouveau regroupement d'&eacute;l&egrave;ves issus de diff&eacute;rentes classes</strong></legend>
	<table align="center" cellspacing="5">
	<tr valign="baseline">
	<td valign="top"><p>Libell&eacute; du regroupement <br>
	<input name="nom_gic" type="text" size="50" >
	</p>
	<p>Commentaire (facultatif) <br />
	<textarea name="commentaire_gic" cols="70" rows="2" id="message" width="200" height= "80" ></textarea>
	</p>
	<p>
	<?php
	if ((isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui')) {

		?>
		<p>S&eacute;lection des &eacute;l&egrave;ves <br />
		(mettre en surbrillance en maintenant appuy&eacute; la touche CTRL)<br />
		<select id="gic_eleves" name="gic_eleves[]" multiple size="10">
		<option disabled > <- S&eacute;lectionner des classes</option>
		<option disabled ></option>
		<option disabled ></option>
		<option disabled > <- S&eacute;lectionner des classes</option>
		<option disabled ></option>
		<option disabled ></option>
		<option disabled > <- S&eacute;lectionner des classes</option>
		</select>
		</p>
		<?php 
	}
	
	?>

<br />
	<p>  
	<input name="submit" type="submit" value="Cr&eacute;er ce nouveau regroupement">
	</p></td>
	</tr>
	</table>
	<input type="hidden" name="nb_classes" value="<?php echo $totalRows_RsClasse;?>">
	<?php if ($_SESSION['droits']==1){?>
		<input type="hidden" name="num_prof" value="<?php echo $_GET['num_prof'];?>">
	<?php };?>
	<input type="hidden" name="MM_insert" value="form1">
	<div align="center">
	<a href="<?php 
	if (($_SESSION['droits']==2)||($_SESSION['droits']==8)) {echo 'enseignant.php';};
	if (($_SESSION['droits']==1)||($_SESSION['droits']==3)) {echo '../inc/regroupement_liste.php';};?>
	">Annuler</a></div>
	</fieldset>
	</div>
        </form>

		</td>
        </tr>
        </table>
        </p>
        </div>
        </DIV>
        </body>
        </html>
        <?php
        mysqli_free_result($RsClasse);
        mysqli_free_result($Rsgroupe);
        ?>
