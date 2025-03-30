<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 

require_once('../inc/functions_inc.php');

if (isset($_POST["sup_ok"]))
{
if ((isset($_POST['ID_fichiers'])) && ($_POST['ID_fichiers'] != "")) {

// Si ce fichier est utilise par ailleurs, ne pas le supprimer physiquement
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		
			$ch_select="%_".$_POST['nom_f'];
		
		
			$query_Recordset3 = sprintf("SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.nom_fichier like '%s' ",$ch_select );

			$Recordset3 = mysqli_query($conn_cahier_de_texte, $query_Recordset3) or die(mysqli_error($conn_cahier_de_texte));
			$row_Recordset3 = mysqli_fetch_assoc($Recordset3);
			$totalRows_Recordset3 = mysqli_num_rows($Recordset3);

             if ($totalRows_Recordset3==1){
			  $fichier = '../fichiers_joints/'.$row_Recordset3['nom_fichier'];
              unlink($fichier);
 			 }
mysqli_free_result($Recordset3);


//on efface de la table
$deleteSQL = sprintf("DELETE FROM cdt_fichiers_joints WHERE ID_fichiers='%s'",
                       GetSQLValueString($_POST['ID_fichiers'], "int"));

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
  



//mysqli_free_result($Recordset2);
  $deleteGoTo = 'liste_documents.php'; 
  if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
  }
header(sprintf("Location: %s", $deleteGoTo));
}

} //du if

$idfich_Recordset1 = "0";
if (isset($_GET['ID_fichiers'])) {
  $idfich_Recordset1 = (get_magic_quotes_gpc()) ? $_GET['ID_fichiers'] : addslashes($_GET['ID_fichiers']);
}


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Recordset1 = sprintf("SELECT * FROM cdt_fichiers_joints WHERE ID_fichiers='%u'", $idfich_Recordset1);
$Recordset1 = mysqli_query($conn_cahier_de_texte, $query_Recordset1) or die(mysqli_error($conn_cahier_de_texte));
$row_Recordset1 = mysqli_fetch_assoc($Recordset1);
$totalRows_Recordset1 = mysqli_num_rows($Recordset1);


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
$header_description="Suppression d'un fichier joint";
require_once "../templates/default/header.php";
?>
  <?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $_GET['nom_fichier']); ?>
  <p align="center">Vous avez demand&eacute; la suppression du fichier <strong>
    <?php  

if (get_magic_quotes_gpc ()){echo stripslashes($nom_f); }else { echo $nom_f ;};

?>
    </strong> </p>
  <p align="center">Confirmez-vous la suppression ?</p>
  <table width="100%"  border="0" align="center">
    <tr>

      <th width="50%" valign="top" scope="col"> <form name="form1" method="post" action="liste_documents_supprime.php">
          <div align="left">
            <p align="center">
              <input name="Supp" type="submit" id="Supp6" value="Supprimer" >
              <input name="sup_ok" type="hidden" id="sup_ok">
              <input name="ID_fichiers" type="hidden" id="ID_fichiers" value="<?php echo $row_Recordset1['ID_fichiers'];?>">
              <input name="nom_f" type="hidden" id="nom_f" value="<?php echo $nom_f;?>">
            </p>
          </div>
        </form></th>
    </tr>
  </table>
  </table>
  <?php
mysqli_free_result($Recordset1);

?>
  <a href="liste_documents.php">Annuler
  </a>
  <p f="MM_callJS('retour')">&nbsp;</p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
