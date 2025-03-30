<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$IDclasse_RsModifClasse = isset($_POST['ID_classe']) ? intval($_POST['ID_classe']) : (isset($_GET['ID_classe']) ? intval($_GET['ID_classe']) : 0);
$redirection = false;
if($IDclasse_RsModifClasse>0) //parametre valide
	{
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsModifClasse = sprintf("SELECT * FROM cdt_classe WHERE cdt_classe.ID_classe=%u", $IDclasse_RsModifClasse);
	$RsModifClasse = mysqli_query($conn_cahier_de_texte, $query_RsModifClasse) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsModifClasse = mysqli_fetch_assoc($RsModifClasse);
	if(mysqli_num_rows($RsModifClasse)>0) //classe trouvee
		{
		$choix_passe_classe = empty($row_RsModifClasse['passe_classe']) ? 0 : ($row_RsModifClasse['passe_classe']==md5("") ? 1 : 2);
		$etat_passe = ($choix_passe_classe==2 && !empty($row_RsModifClasse['passe_classe'])) ? "modifiable ici" : "&agrave; sp&eacute;cifier ici";

		if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1"))
			{
			$nom_classe= str_replace(array("/", "&", "\'"), "-",GetSQLValueString($_POST['nom_classe'], "text") );
			$new_choix_passe_classe = empty($_POST["choix_passe_classe"]) ? 0 : intval($_POST["choix_passe_classe"]);
			if($new_choix_passe_classe==0) $passe_classe = ""; //acces interdit
			elseif($new_choix_passe_classe==1) $passe_classe = md5(""); //acces sans mot de passe
			elseif($new_choix_passe_classe==2) //acces avec mot de passe
				{
				if(!empty($_POST["passe_classe"])) $passe_classe = md5($_POST["passe_classe"]);
				elseif($choix_passe_classe!=2) $passe_classe = md5("");
				else $passe_classe= false; //seul cas ou l'on ne met pas a jour le mot de passe car il en existe deja un
				}
			if($passe_classe!==false)
				{
				if($new_choix_passe_classe==0){$pc='';} else {$pc=md5($_POST['passe_classe']);};
				$updateSQL = sprintf("UPDATE cdt_classe SET nom_classe=%s,passe_classe  =%s WHERE ID_classe=%u",
					$nom_classe,
					GetSQLValueString($pc, "text"),
					GetSQLValueString($_POST['ID_classe'], "int"));
				}
			else 
				{
				$updateSQL = sprintf("UPDATE cdt_classe SET nom_classe=%s WHERE ID_classe=%u",
					$nom_classe,
					GetSQLValueString($_POST['ID_classe'], "int"));
				}	
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
			$redirection = true;
			}
		}
	else $redirection = true;
	}
	elseif ($IDclasse_RsModifClasse==0) //modification en bloc de l'acces pour l'ensemble des classes

	{
		if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1"))
			{
			$new_choix_passe_classe = empty($_POST["choix_passe_classe"]) ? 0 : intval($_POST["choix_passe_classe"]);
			if($new_choix_passe_classe==0) $passe_classe = ""; //acces interdit
			elseif($new_choix_passe_classe==1) $passe_classe = md5(""); //acces sans mot de passe
			elseif($new_choix_passe_classe==2) $passe_classe = md5($_POST["passe_classe"]);//acces avec mot de passe

			$updateSQL = sprintf("UPDATE cdt_classe SET passe_classe  =%s ",GetSQLValueString($passe_classe, "text"));
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
			$redirection = true;
			}
	}

else $redirection = true;

if($redirection)
	{
	$updateGoTo = "classe_ajout.php";
	if(isset($_SERVER['QUERY_STRING']))
		{
		$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
		$updateGoTo .= $_SERVER['QUERY_STRING'];
		}
	header(sprintf("Location: %s", $updateGoTo));
	die();
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Modification des param&egrave;tres d'une classe";
require_once "../templates/default/header.php";
?>


<HR> 
<p align="center">&nbsp;</p>
<form method="post" name="form1" action="<?php echo $editFormAction; ?>">
<p align="center">&nbsp;</p>
<?php 
if ($_GET['ID_classe']>0){ ?>
<p align="center">Modifier le nom et le mot de passe de la classe <strong><?php echo $row_RsModifClasse['nom_classe']; ?></strong></p><?php }
else {
?>
<p align="center"><strong>Modification de l'acc&egrave;s en consultation pour l'ensemble des classes</strong></p>
<?php };?>
<table align="center" class="Style5">
<tr valign="baseline">
<td class="Style55">
<div align="left">


<?php
if ($_GET['ID_classe']>0){ ?>
<p>Nom de la classe <input type="text" name="nom_classe" value="<?php echo $row_RsModifClasse['nom_classe']; ?>" size="32"></p>
<p>Acc&egrave;s en consultation  :</p>
<p><br/>
    <input type="radio" name="choix_passe_classe" value="0" <?php if($choix_passe_classe==0) echo 'checked="checked"'; ?>/>
  acc&egrave;s interdit<br/>
    <input type="radio" name="choix_passe_classe" value="1" <?php if($choix_passe_classe==1) echo 'checked="checked"'; ?>/>
  acc&egrave;s libre sans mot de passe<br/>
    <input type="radio" name="choix_passe_classe" value="2" <?php if($choix_passe_classe==2) echo 'checked="checked"'; ?>/>
  acc&egrave;s prot&eacute;g&eacute; par mot de passe,  <?php echo $etat_passe; ?> :
  <input type="text" name="passe_classe" value="" size="15"/>
</p>
<?php }
else { ?>
<p>Acc&egrave;s en consultation  :</p>
<p><br/>
    <input type="radio" name="choix_passe_classe" value="0" />
  acc&egrave;s interdit<br/>
    <input type="radio" name="choix_passe_classe" value="1" />
  acc&egrave;s libre sans mot de passe<br/>
    <input type="radio" name="choix_passe_classe" value="2" checked="checked"/>
  acc&egrave;s prot&eacute;g&eacute; par mot de passe &agrave; sp&eacute;cifier ici : 
  <input type="text" name="passe_classe" value="" size="15"/>
</p>
<?php
}
; ?>
</div></td>
</tr>
<tr valign="baseline">
<td class="Style55"><div align="center">
<p>
<input type="submit" value="Enregistrer les modifications">
</p>
</div></td>
</tr>
</table>
<input type="hidden" name="MM_update" value="form1">
<input type="hidden" name="ID_classe" value="<?php echo $row_RsModifClasse['ID_classe']; ?>">

</form>  <p align="center">&nbsp;</p>
<p align="center"><a href="classe_ajout.php">Annuler</a></p>  
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
if(isset($RsModifClasse)){mysqli_free_result($RsModifClasse);};
?>
