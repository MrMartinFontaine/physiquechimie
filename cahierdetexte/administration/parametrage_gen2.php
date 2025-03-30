<?php

include "../authentification/authcheck.php";

if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};

$serveur                = $_POST['serveur'] ;
$login                  = $_POST['login'] ;
$password               = $_POST['password'] ;
$base                   = $_POST['base'] ;
$nom_etab_new           = $_POST['nom_etab'] ;
$url_etab_new           = $_POST['url_etab'] ;
$url_logo_etab_new      = $_POST['url_logo_etab'] ;

if ($_POST['url_deconnecte_eleve']<>''){ $url_deconnecte_eleve_new = $_POST['url_deconnecte_eleve'] ;} else {$url_deconnecte_eleve_new='index.php';};

if ($_POST['url_deconnecte_prof']<>''){ $url_deconnecte_prof_new = $_POST['url_deconnecte_prof'] ;} else {$url_deconnecte_prof_new='index.php';};


$session_timeout_new = $_POST['session_timeout'];
$time_zone_new  = $_POST['time_zone'];

$URL_Piwik              = $_POST['URL_Piwik'];
$ID_Piwik               = $_POST['ID_Piwik'];

$url_deconnecte_eleve=$_POST['url_deconnecte_eleve'];
$url_deconnecte_prof=$_POST['url_deconnecte_prof'];



if ($session_timeout_new==''){$session_timeout_new=3600;};

 

$erreurs['erreur1'] = "<b>Le fichier &quot;Connections/conn_cahier_de_texte.php&quot; n'a pas pu &ecirc;tre ouvert. V&eacute;rifiez que vous poss&eacute;dez les droits en &eacute;criture sur ce fichier. </b>" ;

$erreurs['erreur2'] = "<b>Erreur &gt; Ecriture du fichier de configuration ! </b>" ;

$erreurs['erreur2'] = "<b>Erreur &gt; Fermeture du fichier !</b>" ;

 

function EcrireFichier($serveur,$base,$login,$password,$nom_etab,$url_etab,$url_logo_etab,$session_timeout,$time_zone) {

                $fp = @fopen("../Connections/conn_cahier_de_texte.php", "w") ;
                if (!$fp)
                        return 'erreur1' ;      
                $data = "<?PHP\n";

                $data.= " \$hostname_conn_cahier_de_texte = '".$serveur."';\n";

        $data.= " \$database_conn_cahier_de_texte = '".$base."';\n";

                $data.= " \$username_conn_cahier_de_texte = '".$login."';\n";

                $data.= " \$password_conn_cahier_de_texte = '".$password."';\n";

                $data.= " \$conn_cahier_de_texte = mysqli_connect(\$hostname_conn_cahier_de_texte, \$username_conn_cahier_de_texte, \$password_conn_cahier_de_texte) or die(mysqli_connect_errno());\n";

		$data.="//si probleme accent a l'affichage (points d'interrogation)decommenter la ligne ci-dessous; \n";
		$data.="header('Content-Type: text/html; charset=ISO-8859-1');ini_set( 'default_charset', 'ISO-8859-1' );\n" ;
		$data.="//si probleme accent pour les données extraites de la base decommenter la ligne ci-dessous;\n" ;
		$data.="mysqli_query(\$conn_cahier_de_texte, \"SET NAMES latin1\");\n";

                $data.= "\n";

                $data.= "?>";

                $desc = @fwrite($fp, $data);

                if (!$desc)

                        return 'erreur2';

                $fc = @fclose($fp);

                if (!$fc)

                        return 'erreur3';

 

                return true ;

}

$ecrireFichier = EcrireFichier($serveur, $base, $login, $password, $nom_etab_new,$url_etab_new,$url_logo_etab_new,$session_timeout_new,$time_zone_new)  ;

 

     require_once('../Connections/conn_cahier_de_texte.php');
 
    $_SESSION['nom_etab']=stripslashes($nom_etab_new);
    
	$_SESSION['URL_Piwik']=$URL_Piwik;
	$_SESSION['ID_Piwik']=$ID_Piwik;

 

        mysqli_select_db($conn_cahier_de_texte,  $database_conn_cahier_de_texte);
        $query = "UPDATE `cdt_params` SET `param_val` = '".mysqli_real_escape_string($conn_cahier_de_texte, $nom_etab_new)."' WHERE `param_nom` ='nom_etab'";
        $result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));
        $query = "UPDATE `cdt_params` SET `param_val` = '".mysqli_real_escape_string($conn_cahier_de_texte, $url_etab_new)."' WHERE `param_nom` ='url_etab'";
        $result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));
        $query = "UPDATE `cdt_params` SET `param_val` = '".mysqli_real_escape_string($conn_cahier_de_texte, $url_logo_etab_new)."' WHERE `param_nom` ='url_logo_etab'";
        $result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));
        $query = "UPDATE `cdt_params` SET `param_val` = '".mysqli_real_escape_string($conn_cahier_de_texte, $time_zone_new)."' WHERE `param_nom` ='time_zone'";
        $result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));
        $query = "UPDATE `cdt_params` SET `param_val` = '".mysqli_real_escape_string($conn_cahier_de_texte, $URL_Piwik)."' WHERE `param_nom` ='URL_Piwik'";
        $result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));
        $query = "UPDATE `cdt_params` SET `param_val` = '".mysqli_real_escape_string($conn_cahier_de_texte, $ID_Piwik)."' WHERE `param_nom` ='ID_Piwik'";
        $result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));
		$query = "UPDATE `cdt_params` SET `param_val` = '".mysqli_real_escape_string($conn_cahier_de_texte, $url_deconnecte_eleve)."' WHERE `param_nom` ='url_deconnecte_eleve'";
        $result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));
		$query = "UPDATE `cdt_params` SET `param_val` = '".mysqli_real_escape_string($conn_cahier_de_texte, $url_deconnecte_prof)."' WHERE `param_nom` ='url_deconnecte_prof'";
        $result = mysqli_query($conn_cahier_de_texte, $query) or die('Erreur SQL !'.$query.mysqli_error($conn_cahier_de_texte));


?>
<html>
<head>
<title>Param&egrave;tres g&eacute;n&eacute;raux</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style70 {color: #000066}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
  <?php 
$header_description="Param&egrave;tres g&eacute;n&eacute;raux";
require_once "../templates/default/header.php";
?>
  <HR>
  <p>&nbsp;</p>
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr valign="top">
      <td>&nbsp;</td>

    <td> 

      <blockquote>

        <?php

        if ($ecrireFichier !== true)

                echo '

                <p style="color:red;font-size: 13px">'.$erreurs[$ecrireFichier].'</p>';

        else {

        ?>

          <p align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Les 

            param&egrave;tres g&eacute;n&eacute;raux de connexion &agrave; la base de donn&eacute;es ont &eacute;t&eacute; enregistr&eacute;s 

          dans le fichier <strong>Connections/cahier_de_texte.php</strong></font></p>

          <p align="left"><font size="2" color="red">Par mesure de s&eacute;curit&eacute;, il 

            sera n&eacute;cessaire de remettre des droits en lecture seule sur ce fichier. </font></p>

        <?php

        }

        ?>

          <p align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Les 

            autres param&egrave;tres ont bien &eacute;t&eacute; enregistr&eacute;s dans la base de donn&eacute;es.</font></p>

          <p align="left">&nbsp;</p>
          <p align="center" class="Style70"><font size="2"><a href="index.php">Retour au Menu Administrateur</a></font></p>
        </blockquote>
      </td>
    </tr>
  </table>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
