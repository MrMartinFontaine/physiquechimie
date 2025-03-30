<?php
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));

if(isset($_POST['choice']))
	{
	$choice = GetSQLValueString($_POST['choice'], "text");
	$query_write = "UPDATE `cdt_params` SET `param_val`=$choice WHERE `param_nom`='acces_inspection_all_cdt';";
	$result_write = mysqli_query($conn_cahier_de_texte, $query_write);
	}

$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='acces_inspection_all_cdt' LIMIT 1;";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$access = $row[0];
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
.Style70 {color: #000066}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Mise &agrave; disposition des cahiers aux corps d'inspection";
require_once "../templates/default/header.php";
?>

  <blockquote>

        <p style="color:red;">Etat actuel - Acc&egrave;s syst&eacute;matique &agrave; l'ensemble des cahiers de l'&eacute;tablissement :  <?php echo $access; ?></p>
        <fieldset style="width : 100%">
    
        <p align="left"  class="Style70"><img src="../images/lightbulb.png" width="16" height="16">&nbsp;Les cahiers de textes des classes dans lesquelles enseigne un professeur doivent &ecirc;tre accessibles en lecture par les corps d'inspection dans le cadre de leurs missions. </p>
        <p align="left"  class="Style70">Deux possibilit&eacute;s s'offrent &agrave; vous :</p>
        <p align="left"  class="Style13">- Acc&egrave;s ponctuel propos&eacute; par l'enseignant.  </p>
   
        <blockquote>
      
          <div align="left">
            <p>L'administrateur a la possibilit&eacute; de cr&eacute;er un compte de type invit&eacute;. Ainsi, l'inspecteur peut s'identifier sur la page d'accueil de l'application s'il dispose du mot de passe affect&eacute; &agrave; ce compte. Il visualisera alors <strong><em>les seuls cahiers de textes</em></strong> de l'&eacute;tablissement mis &agrave; disposition ponctuellement par les enseignants. </p>
            <p>D'autre part,  chaque enseignant ou le chef d'&eacute;tablissement peuvent aussi mettre &agrave; disposition de l'inspecteur les cahiers de textes qu'ils souhaitent diffuser, en lui communiquant un lien crypt&eacute;. </p>
          </div>
      

        </blockquote>
      
        <p align="left"  class="Style13">&nbsp;</p>
        <p align="left"  class="Style13">- Acc&egrave;s syst&eacute;matique &agrave; l'ensemble des cahiers de l'&eacute;tablissement.</p>
        <blockquote>
         
          <div align="left">En choisissant cette seconde option, l'inspecteur visualisera via un compte invit&eacute; <em><strong>l'ensemble des cahiers de textes</strong></em> de l'&eacute;tablissement. </div>
    
        </blockquote>
    
        <p align="left"><br>
        </p>
        <p align="left">&nbsp;</p>
        <form method="post">
        <?php 
if($access=="Oui") echo "<input type=\"hidden\" name=\"choice\" value=\"Non\"/><input type=\"submit\" value=\"Limiter l'acc&egrave;s aux seuls cahiers d&eacute;finis par les enseignants ou le chef d'&eacute;tablissement\"/>";
else echo "<input type=\"hidden\" name=\"choice\" value=\"Oui\"/><input type=\"submit\" value=\"Permettre l'acc&egrave;s syst&eacute;matique &agrave; l'ensemble des cahiers de l'&eacute;tablissement \"/>";
?>       
        </form>
        </p>
        <p>&nbsp;</p>
    </fieldset>
        <p align="left">&nbsp;</p>
  </blockquote>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>

