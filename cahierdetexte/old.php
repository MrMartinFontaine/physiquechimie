<?php 
//modifier pour chaque nouvelle version
$indice_version='5510'; 
$libelle_version='Version 5.5.1.0 Standard';
//---------------------------------
//    Cahier de textes - Application développée par Pierre Lemaitre - Saint-Lo 
//
//    Cette application est distribuée sous licence GNU.
//
//    Vous appreciez ce logiciel ? N'hesitez pas a remercier son auteur en lui envoyant une specialité regionale
//    Coordonnées dans le fichier licence.txt
//    
//    Copyleft (C) <2008>  <Pierre Lemaitre - Saint-Lô (France)>
//    This program is free software: you can redistribute it and/or modify 
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.

//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.

//    You should have received a copy of the GNU General Public License
//    along with this program (copying.txt).  If not, see <http://www.gnu.org/licenses/>.


require_once('Connections/conn_cahier_de_texte.php');
require_once('inc/functions_inc.php');


session_start();


unset($_SESSION['identite']);
unset($_SESSION['nom_prof']);
unset($_SESSION['ID_prof']);
unset($_SESSION['email']);
unset($_SESSION['droits']);
unset($_SESSION['publier_cdt']);
unset($_SESSION['publier_travail']);
unset($_SESSION['stop_cdt']);
unset($_SESSION['date_visa']);
unset($_SESSION['copie']);
unset($_SESSION['coller']);
unset($_SESSION['semdate']);
unset($_SESSION['consultation']);
unset($_SESSION['last_access']);
unset($_SESSION['ipaddr']);
unset($_SESSION['path_fichier_perso']);
unset($_SESSION['xinha_editlatex']);
unset($_SESSION['xinha_equation']);
unset($_SESSION['xinha_stylist']);

unset($_SESSION['nom_etab']);
unset($_SESSION['url_etab']);
unset($_SESSION['url_logo_etab']);

unset($_SESSION['libelle_devoir']);
unset($_SESSION['visa_stop_edition']);
unset($_SESSION['session_timeout']);

unset($_SESSION['url_deconnecte_eleve']);
unset($_SESSION['url_deconnecte_prof']);
unset($_SESSION['module_absence']);
unset($_SESSION['choix_module_absence']); 

unset($_SESSION['edt_modif_mat']);
unset($_SESSION['affichage_compteur']);

unset($_SESSION['acces_rapide']);
unset($_SESSION['afficher_messages']);
unset($_SESSION['masque_edt_cloture']);
unset($_SESSION['libelle_semaine']);

unset($_SESSION['mobile_browser']);
unset($_SESSION['site_ferme']);
unset($_SESSION['devoir_planif']);

unset($_SESSION['prof_mess_pp']);
unset($_SESSION['prof_mess_all']);
unset($_SESSION['id_etat']);
unset($_SESSION['ipad']);
unset($_SESSION['affiche_xinha']);

unset($_SESSION['type_affich']);
if (isset($_SESSION['ecart_realise'])){unset($_SESSION['ecart_realise']);};
unset($_SESSION['acces_inspection_all_cdt']);

if (isset($_SESSION['archivID'])){ unset($_SESSION['archivID']);};
if (isset($_SESSION['URL_Piwik'])){ unset($_SESSION['URL_Piwik']);};
if (isset($_SESSION['ID_Piwik'])){ unset($_SESSION['ID_Piwik']);};

//lecture de la table cdt_params (nom etablissement, url ..) et declaration de variables de Sessions
require_once('inc/sessions_params.php'); 

//Test utilisation d'un mobile et redirection eventuelle
if (version_compare(PHP_VERSION, '5.0.0') >= 0) {
require_once('inc/detect_mobile.php'); 
} else
{ 
require_once('inc/detect_mobile_old.php');
};

if ($_SESSION['mobile_browser']==true){
	header(sprintf("Location: %s",'mobile/index.php'));
};

//Mise a jour automatique de la base de donnees

if ((isset($row_Rsparams3['param_val'])) && ($row_Rsparams3['param_val']<$indice_version) ){ 
	require_once "administration/misajour/maj_sql_inc.php";
	mysqli_free_result($Rsparams3);
};



if(isset($_POST['classe_ID']))
{
	$choix_RsClasse = intval($_POST['classe_ID']);
	if($choix_RsClasse>0)
	{ 	
		$choix_RsClasse = VerifChamps($choix_RsClasse);
		require_once('Connections/conn_cahier_de_texte.php'); 
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsClasse = sprintf("SELECT nom_classe,passe_classe FROM cdt_classe WHERE ID_classe=%u", GetSQLValueString($choix_RsClasse,"int"));
		$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
		if(mysqli_num_rows($RsClasse)>0)
		{
			$row_RsClasse = mysqli_fetch_assoc($RsClasse);
			if(!empty($row_RsClasse['passe_classe']) && $row_RsClasse['passe_classe']==md5($_POST['passe_c']))
			{
				$_SESSION['consultation']=$_POST['classe_ID'];
				session_write_close();
				$GoTo='consulter.php?classe_ID='.strtr(GetSQLValueString($choix_RsClasse,"int"),$protect).'&tri=date';
				header(sprintf("Location: %s", $GoTo));
				die();
			}
			else $erreur1='Le mot de passe de la classe '.$row_RsClasse['nom_classe'].' est invalide.';
		}
		else
		{
			$erreur1='La classe s&eacute;lectionn&eacute;e n\'est pas valide';
			$choix_RsClasse = 0;
		}
	}
	else $erreur1='Vous devez s&eacute;lectionner la classe et entrer le mot de passe';
}
else $choix_RsClasse = 0;

$page_accueil=1;
?>
<!--
<script language="Javascript">
if (screen.width <= 640) {          
document.location = "./mobile/";
}

</script>

//-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['nom_etab']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="robots" content="noindex">
<meta name="keywords" content="Cahier de textes Pierre Lemaitre">
<meta name="description" content="Cahier de textes - Application d&eacute;velopp&eacute;e par Pierre Lemaitre - Saint-Lô ">

<LINK media=screen href="styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description='';
require_once "templates/default/header.php";

if ($_SESSION['site_ferme']=='Non'){
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
	$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsClasse = mysqli_fetch_assoc($RsClasse);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsListeProf = "SELECT * FROM cdt_prof WHERE ancien_prof='N' ORDER BY identite,nom_prof ASC";
	$RsListeProf = mysqli_query($conn_cahier_de_texte, $query_RsListeProf) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsListeProf = mysqli_fetch_assoc($RsListeProf);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_menu_deroul = "SELECT param_val FROM cdt_params WHERE param_nom='menu_deroul'";
	$menu_deroul = mysqli_query($conn_cahier_de_texte, $query_menu_deroul) or die(mysqli_error($conn_cahier_de_texte));
	$row_menu_deroul = mysqli_fetch_assoc($menu_deroul);
	
	
	if ($row_menu_deroul['param_val']=='Non'){ //si non parametre alors menu deroulant par defaut
		$menu_deroulant=false;
} else { $menu_deroulant=true;};
mysqli_free_result($menu_deroul);


$cum='';

$idem=true;
do { 
	$cum=$cum.$row_RsClasse['passe_classe'];
	
	if($row_RsClasse['passe_classe']<>md5('')){$idem=false;break;};
} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse));
$rows = mysqli_num_rows($RsClasse);
if($rows > 0) { mysqli_data_seek($RsClasse,0);}; 

?>
<p class="erreur">
<?php if(isset($erreur1)){echo $erreur1;} ?>
</p>
<form onLoad= "formfocus()" name="form3" method="post" action="index.php">
<table width="92%" border="0" align="center" cellpadding="0" cellspacing="0" class="espace_enseignant">
<tr>
<td width="156">
<p align="right"><span class="Style44"><strong><br>
Espace El&egrave;ve &amp; Parents <br>
</strong> S&eacute;lectionner la classe 
<?php if (($cum<>'')&&($idem==false)){echo '<br>et entrer le mot de passe';} ;?>
</span></p>
</td>
<td>&nbsp;</td>
<td><p>
<select name="classe_ID" id="classe_ID" <?php if (($cum<>'')&&($idem==true)){echo ' onchange="this.form.submit()"';};?>>
<option value="value">S&eacute;lectionner la classe</option>
<?php while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)){ ?>
	<option value="<?php echo $row_RsClasse['ID_classe']?>" <?php if($row_RsClasse['ID_classe']==$choix_RsClasse) echo 'selected="selected"'; ?>><?php echo $row_RsClasse['nom_classe']?></option>
<?php	} ;
$rows = mysqli_num_rows($RsClasse);
if($rows > 0) { mysqli_data_seek($RsClasse, 0); $row_RsClasse = mysqli_fetch_assoc($RsClasse); };
mysqli_free_result($RsClasse);
?>
</select>
<input name="passe_c" <?php if (($cum<>'')&&($idem==false)){echo 'type="password"';}else {echo 'type="hidden"';};?> id="passe_c" autocomplete="off" />
</p></td>
<?php if (($cum<>'')&&($idem==false)){?>
	<td width="100"><input type="submit" name="Submit3" value="Valider">
	</td>
<?php };?>
</tr>
</table>
<p> </p>
</form>
<p>&nbsp;
</p>
<hr>
<p align="center"></p>
<?php 
} else {
	
	
	echo "<div>\n";
	echo "<p><font color=#FF0000><i>L'application Cahier de textes est en cours de maintenance et temporairement inaccessible.<br />Veuillez nous excuser de ce d&eacute;rangement et r&eacute;essayer de vous connecter ult&eacute;rieurement.</i></font></p>\n";
	echo "</div>\n";
	
};
if ((isset($menu_deroulant))&&($menu_deroulant==true))  { ?>
	<script language="JavaScript" type="text/JavaScript">
	
	function formfocus() {
		document.form2.passe.focus()
		document.form2.passe.select()
	}
	</script>
<?php } else { ?>
	<script language="JavaScript" type="text/JavaScript">
	
	function formfocus() {
		document.form2.nom_prof.focus()
		document.form2.nom_prof.select()
	}
	</script>
<?php } ?>
<form onLoad= "formfocus()" action='./authentification/auth.php'  method="post" name="form2" id="form2">

<table width="92%" border="0" align="center" cellpadding="0" cellspacing="0" class="espace_enseignant">
<tr>
<?php if ((isset($menu_deroulant))&&($menu_deroulant==true)) { ?>
        <td width="156">
        <p align="right"><span class="Style44"><br>
        <strong>Espace Enseignant</strong><br>
        S&eacute;lectionner votre nom<br>
        et entrer 
        votre mot de passe</span></p>        </td>
        <td>&nbsp;</td>
        <td><select name="nom_prof" id="nom_prof">
        <option value="value">S&eacute;lectionner votre nom</option>
        <?php
        do {  
        	?>
        	<option value="<?php echo $row_RsListeProf['nom_prof']?>">
        	<?php if ($row_RsListeProf['identite']<>""){ echo $row_RsListeProf['identite'];} else {echo $row_RsListeProf['nom_prof'];};?>
        	</option>
        	<?php
        } while ($row_RsListeProf = mysqli_fetch_assoc($RsListeProf));
        $rows = mysqli_num_rows($RsListeProf);
        if($rows > 0) {
        	mysqli_data_seek($RsListeProf, 0);
        	$row_RsListeProf = mysqli_fetch_assoc($RsListeProf);
        };
        mysqli_free_result($RsListeProf);
        ?>
        </select>
        <input name="passe" type="password" id="passe" autocomplete="off"/></td>
<?php } else { ?>
	<td></td>
        <td colspan="2" rowspan="2" scope="col"><span class="Style44">Entrer votre identifiant&nbsp;puis votre mot de passe </span><br/>
        <input name="nom_prof" id="nom_prof" />
        <input name="passe" type="password" id="passe" autocomplete="off" /></td>
        <td>&nbsp;</td>
      </tr>
        <tr>
        <td width="156">
        <p align="right"><span class="Style44"> <strong>Espace Enseignant</strong><br>
        </span></p>
        </td>
        <td>&nbsp;</td>
        <?php } ?>
<td width="100">
  <input type="submit" name="Submit2" value="Valider" />
</td>
</tr>
</table>
</form>
<script> formfocus(); </script>
<HR>
<DIV id=footer>
<p class="auteur"><a href="contribution.php" class="auteur">Pierre Lemaitre 
- St L&ocirc; (France) - <?php echo $libelle_version ;?> <br />
</a></p>
</DIV>
</DIV>

<img src="http://perso0.free.fr/cgi-bin/wwwcount.cgi?df=www.chimiephysique.dat&dd=C&ft=0" width="1" height="1">

</td>

</BODY>
</HTML>
