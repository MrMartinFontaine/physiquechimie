<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 

require_once('../inc/functions_inc.php');

if (isset($_POST["maj_ok"])){
  // Mise a jour du fichier par telechargement
  if ($_FILES['fichier1']['name']<>'') {
    $dossier_destination =  getcwd(); 
    $dossier_destination = str_replace('enseignant','',$dossier_destination).'fichiers_joints/';
    $dossier_temporaire = $_FILES['fichier1']['tmp_name'];
	  $type_fichier = $_FILES['fichier1']['type'];
	  $nom_fichier1 = $_POST["nom_f"];
	  $erreur= $_FILES['fichier1']['error'];
	  if ($erreur == 2 ) {
		  exit ("Le fichier 1 d&eacute;passe la taille de 100 Mo.");
	  }
	  if ($erreur == 3 ) {
		  exit ("Le fichier 1 a &eacute;t&eacute; partiellement transf&eacute;r&eacute;. Envoyez-le &agrave; nouveau.");
	  }
	  if( !move_uploaded_file($dossier_temporaire, $dossier_destination . $nom_fichier1) ){
      exit("Impossible de copier le fichier 1 dans $dossier_destination");
    }  
  }
// Fin de mise a jour

  $majGoTo = 'liste_documents.php'; 
  if (isset($_SERVER['QUERY_STRING'])) {
    $majGoTo .= (strpos($majGoTo, '?')) ? "&" : "?";
    $majGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $majGoTo));

}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_callJS(jsStr) { //v2.0
  return eval(jsStr)
}

function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
//-->
</script>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
  <?php 
$header_description="Mise &agrave; jour d'un fichier joint";
require_once "../templates/default/header.php";
?>
  <?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $_GET['nom_fichier']); ?>
  <p align="center">Vous avez demand&eacute; la mise &agrave; jour du fichier <strong>
    <?php  

if (get_magic_quotes_gpc ()){echo stripslashes($nom_f); }else { echo $nom_f ;};

?>
    </strong> </p>
  <p align="center">T&eacute;l&eacute;chargez le fichier de mise &agrave; jour :</p>
  <table width="100%"  border="0" align="center">
    <tr>

      <th width="50%" valign="top" scope="col"> <form name="form1" method="post" enctype="multipart/form-data" action="liste_documents_maj.php">
          <div align="left">
            <p align="center">
              <input type="hidden" name="MAX_FILE_SIZE" value="100000000">
              <input type="file" name="fichier1">
              <br /><br />
              <input name="maj_ok" type="hidden" id="maj_ok">
              <input name="nom_f" type="hidden" id="nom_f" value="<?php echo $_GET['nom_fichier'];?>">
              <input name="Maj" type="submit" id="Supp6" value="Envoyer" >
            </p>
          </div>
        </form></th>
    </tr>
  </table>
  </table>
  <a href="liste_documents.php">Annuler
  </a>
  <p f="MM_callJS('retour')">&nbsp;</p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
