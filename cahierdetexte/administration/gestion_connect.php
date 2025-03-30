<?php
//---------------------------------

//    Cahier de textes - Application développée par Pierre Lemaitre - Saint-Lô 
//
//    Cette application est distribuée sous licence GNU.
//
//    Vous appréciez ce logiciel ? N'hésitez pas à remercier son auteur en lui envoyant une spécialité régionale
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

// protection de la page

include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

// fin ajout
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
.Style74 {
	font-size: 8pt;
	text-indent: 10px;
	color:#0000FF;
}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
<p>

<?php
$header_description="Acc&egrave;s &agrave; l'application Cahier de textes";
require_once "../templates/default/header.php";
 

if (isset($_POST['disable_login']))
{
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$sql="UPDATE cdt_params SET param_val = '$_POST[disable_login]' where param_nom='site_ferme' ";
$RsdateRAZ = mysqli_query($conn_cahier_de_texte, $sql) or die(mysqli_error($conn_cahier_de_texte));
if ($_POST['disable_login']=='Oui') $_SESSION['site_ferme']="Oui";
else $_SESSION['site_ferme']="Non";
echo "<p><font color=#FF0000><strong>Votre modification a bien &eacute;t&eacute; prise en compte.</strong></font></p>\n";
}


//
// Activation/désactivation des connexions
//



$disable_login=$_SESSION['site_ferme'];

if($disable_login=="Oui"){
	echo "<p>L'acc&egrave;s &agrave; l'application Cahier de textes est actuellement <font color=#FF0000><U><b>d&eacute;sactiv&eacute;</b></U></font>.</p>\n";
	echo "Aucune nouvelle connexion n'est accept&eacute;e.</p>\n";
}
elseif($disable_login=="Non"){
	echo "<p>L'acc&egrave;s &agrave; l'application Cahier de textes est actuellement <font color=#FF0000><U><b>activ&eacute;</b></U></font>.</p>\n";
}

echo "<p>En d&eacute;sactivant l'acc&egrave;s, vous rendez impossible la connexion &agrave; l'application pour les utilisateurs, hormis les administrateurs.</p>\n";

echo "<form action=\"gestion_connect.php\" name=\"form_acti_connect\" method=\"post\">\n";

echo "<table border='0' summary='Activation/d&eacute;sactivation des connexions'>\n";
echo "<tr>\n";
echo "<td valign='top'>\n";
echo "&nbsp;"; echo "&nbsp;"; echo "&nbsp;"; echo "&nbsp;";
echo "<input type='radio' name='disable_login' value='Oui' id='label_1a'";
if ($disable_login=='Oui'){ echo " checked ";}
echo " />\n";
echo "</td>\n";
echo "<td>\n";
echo "<label for='label_1a' style='cursor: pointer;'>D&eacute;sactiver l'acc&egrave;s</label>\n";
echo "(<i><span style='color:red;'>Attention, la d&eacute;connection se fera &agrave; la prochaine ouverture de session.</span></i>)\n";
echo "</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td valign='top'>\n";
echo "&nbsp;"; echo "&nbsp;"; echo "&nbsp;"; echo "&nbsp;";
echo "<input type='radio' name='disable_login' value='Non' id='label_2a'";
if ($disable_login=='Non'){ echo " checked ";}
echo " />\n";
echo "</td>\n";
echo "<td>\n";
echo "<label for='label_2a' style='cursor: pointer;'>&nbsp;&nbsp;&nbsp;Activer l'acc&egrave;s</label>\n";
echo "(<i><span style='color:green;'>Tous les utilisateurs sont d&eacute;sormais autoris&eacute;s &agrave; se connecter &agrave; l'application.</span></i>)\n";
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";

echo "<br /><center><input type=\"submit\" name=\"valid_acti_mdp\" value=\"Valider\" /></center>\n";
echo "</form>\n";

echo "<hr class=\"header\" style=\"margin-top: 32px; margin-bottom: 24px;\" />\n";


?>

<p>&nbsp;</p>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</div>
</DIV>
</body>
</html>
