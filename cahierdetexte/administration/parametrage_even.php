<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

if ((isset($_POST['MM_UPDATE']))&&($_POST['MM_UPDATE']=='on'))
	{


$even_nom_valid_mail	= $_POST['even_nom_valid_mail'];
$even_adr_valid_mail		= $_POST['even_adr_valid_mail'];
$even_dest_mail		= $_POST['even_dest_mail'];



mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);	
$query = "UPDATE `cdt_params` SET `param_val` = '".mysqli_real_escape_string($conn_cahier_de_texte, $even_nom_valid_mail)."' WHERE `param_nom` ='even_nom_valid_mail'";
$result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));

$query = "UPDATE `cdt_params` SET `param_val` = '".mysqli_real_escape_string($conn_cahier_de_texte, $even_adr_valid_mail)."' WHERE `param_nom` ='even_adr_valid_mail'";
$result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));
	
$query = "UPDATE `cdt_params` SET `param_val` = '".mysqli_real_escape_string($conn_cahier_de_texte, $even_dest_mail)."' WHERE `param_nom` ='even_dest_mail'";
$result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));

$insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }

header(sprintf("Location: %s", $insertGoTo));
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_even_nom_valid_mail = "SELECT param_val FROM cdt_params WHERE param_nom='even_nom_valid_mail'";
$even_nom_valid_mail = mysqli_query($conn_cahier_de_texte, $query_even_nom_valid_mail) or die(mysqli_error($conn_cahier_de_texte));
$row_even_nom_valid_mail = mysqli_fetch_assoc($even_nom_valid_mail);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_even_adr_valid_mail = "SELECT param_val FROM cdt_params WHERE param_nom='even_adr_valid_mail'";
$even_adr_valid_mail = mysqli_query($conn_cahier_de_texte, $query_even_adr_valid_mail) or die(mysqli_error($conn_cahier_de_texte));
$row_even_adr_valid_mail = mysqli_fetch_assoc($even_adr_valid_mail);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_even_dest_mail = "SELECT param_val FROM cdt_params WHERE param_nom='even_dest_mail'";
$even_dest_mail = mysqli_query($conn_cahier_de_texte, $query_even_dest_mail) or die(mysqli_error($conn_cahier_de_texte));
$row_even_dest_mail = mysqli_fetch_assoc($even_dest_mail);
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
.Style145 {font-size: 2px}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Validation des &eacute;v&eacute;nements, projets et actions p&eacute;dagogiques";
require_once "../templates/default/header.php";
?>
<br/>
      
        <form method="post" action="parametrage_even.php">
          <table width="95%" align="center" cellspacing="0">
		<tr class="lire_cellule_2">
		  <td colspan="2" ><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><p>&nbsp;</p>
		    </font>
		    <blockquote>
		      <p align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Par d&eacute;faut, les utilisateurs sont habilit&eacute;s &agrave; proposer <strong>et valider eux-m&ecirc;mes</strong> leur fiche &eacute;v&egrave;nement,  projet ou action p&eacute;dagogique. </font></p>
		      <p align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Vous pouvez restreindre cette possibilit&eacute; en d&eacute;signant un valideur de projet. D&egrave;s qu'une fiche est propos&eacute;e vour validation, cette personne recevra un m&eacute;l lui demandant d'aller sur le site r&eacute;aliser cette validation.</font></p>
		      <p align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Les personnes ayant le profil <em>Responsable &eacute;tablissement</em> dispose des droits pour valider la fiche. </font></p>
		      <p align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Enfin, il est possible d'informer par m&eacute;ls certaines personnes lorqu'une action est valid&eacute;e</font></p>
		      <p align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Nb : Pour les pros, la d&eacute;finition des items des menus d&eacute;roulants &quot;<em>Modalit&eacute;s</em>&quot; et &quot;<em>Domaine</em>&quot; est r&eacute;alis&eacute;e dans le fichier <em>enseignant\evenement_select.php</em>. Editer ce fichier pour toutes modifications.</font>
		      <p align="left" class="Style145">&nbsp;</p>
		    </blockquote>		    
		    <font size="2" face="Verdana, Arial, Helvetica, sans-serif">
		    <p align="left">
	        <p>&nbsp; </p>
		    </font></td>
		  </tr>
		<tr>
		<td class="lire_cellule_22"><p align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Nom de la personne habilit&eacute;e &agrave; valider les fiches : </font></p>
		  <p align="left">&nbsp;</p>
		  <p align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Adresse m&egrave;l de cette personne :<br>
            <br>
	          </font></p></td>
        <td class="lire_cellule_22">
          <p align="left"><br>
            <input name="even_nom_valid_mail" type="text" id="even_nom_valid_mail" value="<?php echo $row_even_nom_valid_mail['param_val']?>"  size="50" maxlength="50">
			<?php mysqli_free_result($even_nom_valid_mail);?>
            <br>
            <span class="Style44 Style44"><em><font face="Verdana, Arial, Helvetica, sans-serif">Laisser vide, si vous d&eacute;sirez que les utilisateurs valident<br> 
          eux-m&ecirc;me leur propre fiche. </font></em></span></p>
			            <p align="left"><br>
                          <input name="even_adr_valid_mail" type="text" id="even_adr_valid_mail" value="<?php echo $row_even_adr_valid_mail['param_val']?>"  size="50" maxlength="50">
                          <?php mysqli_free_result($even_adr_valid_mail);?>
          </p>
        <p>&nbsp;</p></td>
		</tr>
		<tr>
		  <td class="lire_cellule_22"><div align="left">
		    <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Adresses m&eacute;ls des personnes d&eacute;sireuses d'&ecirc;tre inform&eacute;es par courrier de la validation d'une fiche.</font></p>
		    <p><a href="../vie_scolaire/prof_liste.php">Liste de l'ensemble du personnel avec leur m&eacute;l</a></p>
		    <p>&nbsp;</p>
		  </div></td>
		  <td class="lire_cellule_22"><div align="left">
		    <p>
		      <textarea name="even_dest_mail" cols="38" rows="5" id="even_dest_mail"><?php echo $row_even_dest_mail['param_val']?></textarea>
		      <br>
		      <em>Entrer les adresses m&eacute;ls s&eacute;par&eacute;es par un <strong>point-virgule</strong> </em>	        </p>
		    <p>&nbsp;</p>
		  </div>
	      <?php mysqli_free_result($even_dest_mail);?></td>
		  </tr>
		<tr>
		  <td colspan="2" class="lire_cellule_22"><div align="center">
		    <p>&nbsp;	        </p>
		    <p>
		      <input type="submit" name="verif" value="Enregistrer ces nouveaux param&egrave;tres " >
            </p>
		    <p>&nbsp;</p>
		  </div></td>
		  </tr>
		</table>
		
		<input name="MM_UPDATE" id="MM_UPDATE" type="hidden" value="on">
		</form></p>


  <p align="center">&nbsp;</p>
  <p align="center"><a href="even_projet_menu.php">Retour au Menu Gestion des &eacute;v&eacute;nements &amp; actions p&eacute;dagogiques </a></p>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a> </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>

