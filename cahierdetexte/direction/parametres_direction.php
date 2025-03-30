<?php

include "../authentification/authcheck.php";
if ($_SESSION['droits']<>4) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 

require_once('../inc/functions_inc.php');

$editFormAction = '#';

//paramètre autorisant la modification du login
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsparams_login = "SELECT param_val FROM cdt_params WHERE param_nom='modif_login'";
$Rsparams_login = mysqli_query($conn_cahier_de_texte, $query_Rsparams_login) or die('Erreur SQL !'.$query_Rsparams_login.'<br>'.mysqli_error($conn_cahier_de_texte));
$row_Rsparams_login = mysqli_fetch_assoc($Rsparams_login);

//paramètre autorisant la modification du mot de passe
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsparams_passe = "SELECT param_val FROM cdt_params WHERE param_nom='modif_passe'";
$Rsparams_passe = mysqli_query($conn_cahier_de_texte, $query_Rsparams_passe) or die('Erreur SQL !'.$query_Rsparams_passe.'<br>'.mysqli_error($conn_cahier_de_texte));
$row_Rsparams_passe = mysqli_fetch_assoc($Rsparams_passe);


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

	//En cas de remise à blanc, on conserve l'existant 
	if ($_POST['identite']==''){$_POST['identite']=$_SESSION['identite'];};
	if ((isset($_POST['nom_prof']))&&($_POST['nom_prof']=='')){$_POST['nom_prof']=$_SESSION['nom_prof'];};
	//Modification du login interdit par l'administrateur
	if (!isset($_POST['nom_prof'])){$_POST['nom_prof']=$_SESSION['nom_prof'];};
	
	
$updateSQL = sprintf("UPDATE cdt_prof SET  identite=%s,nom_prof=%s,email=%s,email_diffus_restreint=%s WHERE ID_prof=%u",
	    GetSQLValueString($_POST['identite'], 'text'),
	    GetSQLValueString($_POST['nom_prof'], 'text'),
        GetSQLValueString($_POST['email'], 'text'),
		GetSQLValueString(isset($_POST['email_diffus_restreint']) ? 'true' : '', 'defined','"O"','"N"'),
        GetSQLValueString($_SESSION['ID_prof'], 'int')
	);

$password=GetSQLValueString($_POST['passe']);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));

if($row_Rsparams_passe['param_val']=='Oui'){ 
if ($password <>''){

  	$updateSQL2 = sprintf("UPDATE cdt_prof SET  passe='%s' WHERE ID_prof=%u",
                       password_hash($password, PASSWORD_DEFAULT),
					   GetSQLValueString($_SESSION['ID_prof'], 'int')
					   );

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result2 = mysqli_query($conn_cahier_de_texte, $updateSQL2) or die(mysqli_error($conn_cahier_de_texte));
  
 };
  
};

$updateGoTo = "../index.php";
header(sprintf("Location: %s", $updateGoTo));
}



mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProf = sprintf("SELECT * FROM cdt_prof WHERE cdt_prof.ID_prof=%u", $_SESSION['ID_prof']);
$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style70 {font-size: small;color: #000066;}
-->
</style>
<SCRIPT type="text/javascript" src="../jscripts/cryptage_passe.js"></script>

</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Param&egrave;tres g&eacute;n&eacute;raux";
require_once "../templates/default/header.php";
require_once ("../authentification/sessionVerif.php");

?>



    <script language="JavaScript" type="text/JavaScript">

function formfocus() {
document.form1.passe.focus()
document.form1.passe.select()
} </script>

<br />
  <form method="post" name="form1" action="<?php echo $editFormAction; ?>" onSubmit="return submit_modifpass2();" >
    
    <table width="95%" align="center" cellspacing="0" class="lire_cellule_22" >
      <tr valign="baseline">
        <td width="50%" ><p align="right" class="Style70">Mon identit&eacute; (Nom long pr&eacute;sent&eacute; aux parents) &nbsp; </p></td>
        <td width="50%" valign="middle" ><div align="left">
          <input name="identite" type="text" id="identite" size="40" value="<?php echo $row_RsProf['identite'] ;?>">
        </div></td>
      </tr>
      <tr valign="baseline">
        <td width="50%" ><p align="right" class="Style70">Mon identifiant de connexion &nbsp; </p></td>
        <td width="50%" valign="middle" ><div align="left">
		<?php if($row_Rsparams_login['param_val']=='Oui'){ ?>
          <input name="nom_prof" type="text" id="nom_prof" size="25" value="<?php echo $row_RsProf['nom_prof'] ;?>">
		<?php ;} else {echo '<p class="Style70"><strong>'.$row_RsProf['nom_prof'].'</strong></p>';};?>
        </div></td>
      </tr>
      <tr valign="baseline">
        <td width="50%" ><p align="right" class="Style70">Adresse m&eacute;l (optionnel) &nbsp; </p></td>
        <td width="50%" align="left" valign="middle"><input name="email"  type="text" id="email" size="40" value="<?php echo $row_RsProf['email'] ;?>">
       </td>
      </tr>
      <tr valign="baseline">
        <td ><p align="right" class="Style70">Restreindre la diffusion de mon  m&eacute;l &agrave; mes coll&egrave;gues &nbsp;<br>
          (adresse non communiqu&eacute;e aux &eacute;l&egrave;ves) &nbsp;</p>
          </td>
        <td align="left" valign="middle" ><input type="checkbox" name="email_diffus_restreint" id="email_diffus_restreint" value=""  <?php if (!(strcmp($row_RsProf['email_diffus_restreint'],'O'))) {echo "checked=checked";} ?>>
        <span class="Style14"><br>
        </span></td>
      </tr>
    </table>
<br />
<?php
if($row_Rsparams_passe['param_val']=='Oui'){ ?>
	<table width="95%" align="center" cellspacing="0" class="lire_cellule_22" >
      <tr valign="baseline">
        <td width="50%"><p align="right" class="Style70">Changer de mot de passe - Nouveau mot de passe &nbsp; </p></td>
        <td width="50%" align="left" valign="middle" ><p>
            <input type="password" name="passe" id="passe" size="15">
        </p></td>
      </tr>
      <tr valign="baseline">
        <td width="50%" ><p align="right" class="Style70">Je confirme mon  nouveau mot de passe  &nbsp; </p></td>
        <td width="50%" align="left" valign="middle" ><p>
            <input name="passe2"  type="password" id="passe2" size="15">
        </p></td>
      </tr>
    </table>
	<br />
	<?php } else { ?>
	<br />Le param&eacute;trage actuel de l'administrateur n'autorise pas la modification de votre mot de passe. <br />
	<?php };?>
	<br />
	<br />
	<p>&nbsp;</p>
    <p>
      <input type="hidden" name="MM_update" value="form1">         
      <input type="submit" value="Enregistrer mes modifications et me reconnecter">
      
    </p>
  </form>
  <p><a href="direction.php">Annuler</a></p>
  <DIV id=footer>
    <p class="auteur"><a href="contribution.php" class="auteur">Pierre Lemaitre 
      - St L&ocirc; (France)  <br />
    </a></p>
  </DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsProf);
?>

