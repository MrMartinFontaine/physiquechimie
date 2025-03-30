<?php

include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
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
	
	
$updateSQL = sprintf("UPDATE cdt_prof SET  identite=%s,nom_prof=%s,email=%s,email_diffus_restreint=%s,publier_cdt=%s,publier_travail=%s,gestion_sem_ab=%s, path_fichier_perso=%s,xinha_editlatex=%s,xinha_equation=%s,xinha_stylist=%s,acces_rapide=%s, afficher_messages=%s, masque_edt_cloture=%s, type_affich=%u WHERE ID_prof=%u",
	    GetSQLValueString($_POST['identite'], 'text'),
	    GetSQLValueString($_POST['nom_prof'], 'text'),
        GetSQLValueString($_POST['email'], 'text'),
		GetSQLValueString(isset($_POST['email_diffus_restreint']) ? 'true' : '', 'defined','"O"','"N"'),
        GetSQLValueString(isset($_POST['publier_cdt']) ? 'true' : '', 'defined','"O"','"N"'),
        GetSQLValueString(isset($_POST['publier_travail']) ? 'true' : '', 'defined','"O"','"N"'),
        GetSQLValueString(isset($_POST['gestion_sem_ab']) ? 'true' : '', 'defined','"O"','"N"'),
	    GetSQLValueString($_POST['path_fichier_perso'], 'text'),
	    GetSQLValueString(isset($_POST['xinha_editlatex']) ? 'true' : '', 'defined','"O"','"N"'),
		GetSQLValueString(isset($_POST['xinha_equation']) ? 'true' : '', 'defined','"O"','"N"'),
	    GetSQLValueString(isset($_POST['xinha_stylist']) ? 'true' : '', 'defined','"O"','"N"'),
	    GetSQLValueString(isset($_POST['acces_rapide']) ? 'true' : '', 'defined','"O"','"N"'),
	    GetSQLValueString(isset($_POST['afficher_messages']) ? 'true' : '', 'defined','"O"','"N"'),	
	    GetSQLValueString(isset($_POST['masque_edt_cloture']) ? 'true' : '', 'defined','"O"','"N"'),	
		GetSQLValueString($_POST['type_affich'], 'int'),
	
        GetSQLValueString($_SESSION['ID_prof'], 'int')
	);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));

if($row_Rsparams_passe['param_val']=='Oui'){ 


$password=$_POST['passe'];
	if ($password <>''){
	
  	$updateSQL2 = sprintf("UPDATE cdt_prof SET  passe='%s' WHERE ID_prof=%u",
	
                       password_hash($password, PASSWORD_DEFAULT),
					   GetSQLValueString($_SESSION['ID_prof'], 'int')
					   );
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result2 = mysqli_query($conn_cahier_de_texte, $updateSQL2) or die(mysqli_error($conn_cahier_de_texte));
  
	};  
  
};

$updateGoTo = "../deconnexion.php";
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
        <span class="Style14"><br>
        <br>
        <img src="../images/lightbulb.png" width="16" height="16"> Cette adresse m&eacute;l sera accessible &agrave; la suite de votre nom lorsqu'il sera communiqu&eacute; aux parents et &eacute;l&egrave;ves sur les fiches &quot;travail &agrave; faire&quot;. Cette option peut &ecirc;tre utilis&eacute;e par exemple pour la communication lors d'un soutien aux devoirs.</span></td>
      </tr>
      <tr valign="baseline">
        <td ><p align="right" class="Style70">Restreindre la diffusion de mon  m&eacute;l &agrave; mes coll&egrave;gues &nbsp;<br>
          (adresse non communiqu&eacute;e aux &eacute;l&egrave;ves) &nbsp;</p>
          <p><br>
            <br>
        </p></td>
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
	<?php };?>
	<table width="95%" align="center" cellspacing="0" class="lire_cellule_22" >
      <tr valign="baseline">
        <td colspan="2" ><div align="left">
          <blockquote>
            <p><img src="../images/lightbulb.png" width="16" height="16"> La visibilit&eacute; du travail &agrave; faire et de l'ensemble de vos cahiers de textes peut-&ecirc;tre d&eacute;sactiv&eacute;e. Vos publications  ne seront donc plus accessibles, y compris pour un compte invit&eacute; (corps d'inspection par exemple). Cette d&eacute;sactivation ne doit &ecirc;tre que temporaire afin de respecter les directives minist&eacute;rielles stipulant que les cahiers et le travail &agrave; faire doivent &ecirc;tre accessibles en ligne. </p>
          </blockquote>
        </div></td>
      </tr>
      <tr valign="baseline">
        <td width="50%" ><p align="right" class="Style70">Visibilit&eacute; <strong>en ligne</strong> de mon cahier de textes &nbsp;</p></td>
        <td width="50%" valign="middle" ><div align="left">
          <input type="checkbox" name="publier_cdt" id="publier_cdt" value=""  <?php if (!(strcmp($row_RsProf['publier_cdt'],'O'))) {echo "checked=checked";} ?>>
        </div></td>
      </tr>
      <tr valign="baseline">
        <td width="50%" ><p align="right" class="Style70">Visibilit&eacute; <strong>en ligne</strong> du travail &agrave; faire&nbsp;</p></td>
        <td width="50%" valign="middle" ><div align="left">
          <input type="checkbox" name="publier_travail" id="publier_travail" value=""  <?php if (!(strcmp($row_RsProf['publier_travail'],'O'))) {echo "checked=checked";} ?>>
        </div></td>
      </tr>
  	</table>	
		<br />  
	<table width="95%" align="center" cellspacing="0" class="lire_cellule_22" >	
	  <tr valign="baseline">
        <td width="50%" ><p align="right" class="Style70">Acc&egrave;s rapide au remplissage <br>
          sans passer par le menu enseignant </p></td>
        <td width="50%" valign="middle" ><div align="left">
          <input type="checkbox" name="acces_rapide" id="acces_rapide" value=""  <?php if (!(strcmp($row_RsProf['acces_rapide'],'O'))) {echo "checked=checked";} ?>>
        </div></td>
      </tr>
	</table>	
		<br />  
	<table width="95%" align="center" cellspacing="0" class="lire_cellule_22" >
	  <tr valign="baseline">
	    <td colspan="2" >
	      <blockquote>
	        <p align="left" ><img src="../images/lightbulb.png" alt=" Attention" width="16" height="16">&nbsp;Il est parfois souhaitable lors de la vid&eacute;oprojection de masquer aux &eacute;l&egrave;ves les diff&eacute;rents messages. A cette fin, <strong>un bouton &quot;d&eacute;ployer/r&eacute;sumer</strong> les messages&quot; existe dans la page de saisie. Si la case ci-dessous est coch&eacute;e, les messages sont d&eacute;ploy&eacute;s par d&eacute;faut &agrave; chaque entr&eacute;e en page de saisie. </p>
        </blockquote></td>
      </tr>
	  <tr >
        <td width="50%" valign="middle"><p align="right" class="Style70">D&eacute;ployer le contenu des messages 
          (profs principaux, vie scolaire...) en page de saisie de l'enseignant.</p>
          </td>
        <td width="50%" valign="middle"><div align="left">
          <input type="checkbox" name="afficher_messages" id="afficher_messages" value=""  <?php if (!(strcmp($row_RsProf['afficher_messages'],'O'))) {echo "checked=checked";} ?>>
        </div></td>
      </tr>
    </table>
	<br />  
	<table width="95%" align="center" cellspacing="0" class="lire_cellule_22" >	  
	  <tr valign="baseline">
        <td width="50%" ><p align="right" class="Style70">Dans l'affichage de mon emploi du temps, <br>
          masquer par d&eacute;faut  les plages horaires d&eacute;j&agrave; clotur&eacute;es </p></td>
        <td width="50%" valign="middle" ><div align="left">
          <input type="checkbox" name="masque_edt_cloture" id="masque_edt_cloture" value=""  <?php if (!(strcmp($row_RsProf['masque_edt_cloture'],'O'))) {echo "checked=checked";} ?>>
        </div></td>
      </tr>
    </table>
	<br />
	<table width="95%" align="center" cellspacing="0" class="lire_cellule_22" >
      <tr valign="baseline">
        <td width="50%" valign="middle" ><div align="right"><span class="Style70">Type de pr&eacute;sentation de la zone de travail &agrave; faire </span></div></td>
        <td width="50%" valign="middle" ><p align="left">
            <label>

              <input type="radio" name="type_affich" value="1" <?php if ($row_RsProf['type_affich']==1) {echo 'checked="checked"';} ?>checked="checked">
              Chaque zone de travail &agrave; faire est accessible via un ascenseur 

            </label>
			<br />
            <label> 
            <input type="radio" name="type_affich" value="2" <?php if ($row_RsProf['type_affich']==2) {echo 'checked="checked"';}?>>
              La zone de saisie des travaux est enti&egrave;rement developp&eacute;e 
            </label>

        </p></td>
      </tr>
    </table>
	<br />
	<table width="95%" align="center" cellspacing="0" class="lire_cellule_22" >
      <tr valign="baseline">
        <td width="50%" ><p align="right" class="Style70">Afficher le bouton Latex   &nbsp;</p></td>
        <td width="50%" valign="middle" ><div align="left">
          <input type="checkbox" name="xinha_editlatex" id="xinha_editlatex" value=""  <?php if (!(strcmp($row_RsProf['xinha_editlatex'],'O'))) {echo "checked=checked";} ?>>
          Acc&egrave;s &agrave; l'&eacute;criture scientifique et math&eacute;matique.</div></td>
      </tr>
      <tr valign="baseline">
        <td width="50%" ><p align="right" class="Style70">Afficher le bouton Equations &nbsp;</p></td>
        <td width="50%" valign="middle" ><div align="left">
          <input type="checkbox" name="xinha_equation" id="xinha_equation" value=""  <?php if (!(strcmp($row_RsProf['xinha_equation'],'O'))) {echo "checked=checked";} ?>>
        Module rudimentaire permettant d'&eacute;crire fractions, racines... </div></td>
      </tr>
	        <tr valign="baseline">
        <td width="50%" ><p align="right" class="Style70">Afficher les styles propres &agrave; l'&eacute;tablissement &nbsp;</p></td>
        <td width="50%" valign="middle" ><div align="left">
          <input type="checkbox" name="xinha_stylist" id="xinha_stylist" value=""  <?php if (!(strcmp($row_RsProf['xinha_stylist'],'O'))) {echo "checked=checked";} ?>>
        Styles d&eacute;finis par votre Administrateur. 
        Affich&eacute;s dans la marge droite de l'&eacute;diteur. </div></td>
      </tr>
	        <tr valign="baseline">
        <td colspan="2" > <div align="left">
          <blockquote>
            <p><img src="../images/lightbulb.png" width="16" height="16"> Pour lib&eacute;rer de la m&eacute;moire, vous pouvez d&eacute;sactiver les outils ci-dessus pr&eacute;sents lors de la saisie si vous ne les utilisez pas.<br>
                <br>
              </p>
          </blockquote>
        </div></td>
      </tr>
    </table>
	<br />
	    <table width="95%" align="center" cellspacing="0" class="lire_cellule_22" >
      <tr valign="baseline">
        <td colspan="2" ><blockquote>
          <p align="left"><img src="../images/lightbulb.png" width="16" height="16"> Vous pouvez activer ou d&eacute;sactiver la prise en compte automatis&eacute;e des semaines A et B programm&eacute;es par l'Administrateur.</p>
          <p align="left">	<strong>Activ&eacute; :</strong> Votre emploi du temps n'affichera que les cours en rapport avec la semaine (A ou B) d&eacute;finie par l'administrateur.</p>
          <p align="left"><strong>D&eacute;sactiv&eacute; : </strong>Votre emploi du temps affichera quelle que soit la semaine tous les cours des semaines A et B. Il vous appartient alors de savoir dans quelle semaine vous vous situez. D&eacute;sactiver temporairement permet de continuer &agrave; saisir son emploi du temps suite &agrave; une erreur de programmation des semaines A et B de l'administrateur... </p>
        </blockquote></td>
        </tr>
      <tr valign="baseline">
        <td width="50%" ><p align="right" class="Style70">Activer&nbsp; </p></td>
        <td width="50%" valign="middle" ><div align="left">
          <input type="checkbox" name="gestion_sem_ab" id="gestion_sem_ab" value=""  <?php if (!(strcmp($row_RsProf['gestion_sem_ab'],'O'))) {echo "checked=checked";} ?>>
        </div></td>
      </tr>
    </table>
	<br />
	    <table width="95%" align="center" cellspacing="0" class="lire_cellule_22" >
      <tr valign="baseline">
        <td colspan="2" ><p align="center" class="Style70"><strong>Optionnel</strong> - Param&eacute;trage de mes liens hypertextes pour un acc&egrave;s &eacute;ventuel <br>
          &agrave; mes fichiers 
        qui seraient d&eacute;j&agrave; pr&eacute;sents sur ce serveur.</p></td>
        </tr>
      <tr valign="baseline">
        <td width="68%" >&nbsp;</td>
        <td width="32%" valign="middle" >&nbsp;</td>
      </tr>

      <tr valign="baseline">
        <td colspan="2" >&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td colspan="2" >Chemin d'acc&egrave;s &agrave; mes dossiers depuis la racine  &gt; exemple<em> : /mon_nom/mes_cours</em></td>
      </tr>
      <tr valign="baseline">
        <td colspan="2" ><div align="center">
          <input name="path_fichier_perso" type="text" id="path_fichier_perso" size="100" value="<?php echo $row_RsProf['path_fichier_perso'] ;?>">
        </div></td>
      </tr>
      <tr valign="baseline">
        <td colspan="2" ><p align="right" class="Style70">&nbsp;</p></td>
        </tr>
    </table>

    <p>&nbsp;</p>
    <p>
      <input type="hidden" name="MM_update" value="form1">         
      <input type="submit" value="Enregistrer mes modifications et me reconnecter">
      
    </p>
  </form>
  <p><a href="enseignant.php">Annuler</a></p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsProf);
?>
