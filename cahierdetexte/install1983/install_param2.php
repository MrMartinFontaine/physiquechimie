<?php

$serveur		= $_POST['serveur'] ;
$login			= $_POST['login'] ;
$password		= $_POST['password'] ;
$base			= $_POST['base'] ;
session_start();
$_SESSION['nom_etab']= $_POST['nom_etab'] ;
$_SESSION['url_etab']       = $_POST['url_etab'] ;
$_SESSION['url_logo_etab']  = $_POST['url_logo_etab'] ;

function EcrireFichier($serveur,$base,$login,$password) {

		$fp = @fopen("../Connections/conn_cahier_de_texte.php", "w")
			or die ("<b>Le fichier Connections/conn_cahier_de_texte.php n'a pas pu être ouvert. V&eacute;rifiez que vous poss&eacute;dez les droits en &eacute;criture sur ce fichier. </b>");
	
		
		$data = "<?PHP\n";
		$data.= " \$hostname_conn_cahier_de_texte = \"".$serveur."\";\n";
        $data.= " \$database_conn_cahier_de_texte = \"".$base."\";\n";
		$data.= " \$username_conn_cahier_de_texte = \"".$login."\";\n";
		$data.= " \$password_conn_cahier_de_texte = \"".$password."\";\n";
		
		//$data.= " \$nom_etab = \"".$nom_etab."\";\n";
		//$data.= " \$url_etab = \"".$url_etab."\";\n";
		//$data.= " \$url_logo_etab = \"".$url_logo_etab."\";\n";
		

				
		$data.= " \$conn_cahier_de_texte = mysqli_connect(\$hostname_conn_cahier_de_texte, \$username_conn_cahier_de_texte, \$password_conn_cahier_de_texte) or die(mysqli_connect_errno());\n";
		
		$data.="//si probleme accent a l'affichage (points d'interrogation)decommenter la ligne ci-dessous; \n";
		$data.="header('Content-Type: text/html; charset=ISO-8859-1');ini_set( 'default_charset', 'ISO-8859-1' );\n" ;
		$data.="//si probleme accent pour les données extraites de la base decommenter la ligne ci-dessous;\n" ;
		$data.="mysqli_query(\$conn_cahier_de_texte, \"SET NAMES latin1\");\n";
		
		$data.= "\n";
		$data.= "?>";
		$desc = @fwrite($fp, $data) or die ("<b>Erreur > Ecriture du fichier de configuration ! </b>");
		@fclose($fp) or die ("<b>Erreur > Fermeture du fichier </b>");


}


EcrireFichier($serveur, $base, $login, $password)  ;




?>



<html>
<head>
<title>Cahier de textes - Installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
 

<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style145 {color: #FF0000}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="INSTALLATION - Premi&egrave;re partie - Etape 2";
require_once "../templates/default/header.php";
?>

<HR>  <p>&nbsp;</p>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr valign="top"> 
    <td>&nbsp;</td>
    <td> 
      <blockquote>
          <p align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Les 
            param&egrave;tres de connexions ont &eacute;t&eacute; enregistr&eacute;s 
            dans le fichier Connections/cahier_de_texte.php</font></p>
          <p align="left"><font size="2" color="red">Par mesure de s&eacute;curit&eacute;, il 
            sera n&eacute;cessaire d'enlever les droits en &eacute;criture sur ce fichier 
            mis le temps de l'installation.</font></p>
          <p align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">L'op&eacute;ration suivante consiste &agrave; cr&eacute;er les tables de l'application Cahier de textes au sein d'une base de donn&eacute;es. Vous pouvez utiliser une base de donn&eacute;es d&eacute;j&agrave; existante, les nouvelles tables cr&eacute;&eacute;es seront pr&eacute;fix&eacute;es cdt_</font></p>
          <p align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">Certains h&eacute;bergeurs ne permettent pas la cr&eacute;ation d'une base de donn&eacute;e en ligne via ce script php. Il est donc pr&eacute;f&eacute;rable de cr&eacute;er la base pr&eacute;alablement via par exemple l'espace client ou phpmyadmin_</font></p>
          <p align="center"><br>
            <font size="2" face="Verdana, Arial, Helvetica, sans-serif">Deux possibilit&eacute;s au choix (<a href="aide.php" target="_blank">Aide sur le choix</a>)</font></p>
        </blockquote>
      <p align="center"><a href="install_table.php?creer_base">Cr&eacute;er ma base  MySql et les tables </a>(<span class="Style145">Attention, voir ci-dessus</span>) </p>
      <p align="center">ou</p>
      <p align="center"><a href="install_table.php">Ma base MySql existe d&eacute;j&agrave; - Cr&eacute;er uniquement les tables </a>(<span class="Style145">Pr&eacute;f&eacute;rable</span>)</p>      </td>
  </tr>
</table>

<DIV id=footer></DIV>
</DIV>
</body>
</html>
