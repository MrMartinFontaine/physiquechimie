<?php
include "../../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

// Test de l'existence de la table
function check_tables($table, $conn_cdt){
$query_verif_cont = "SELECT * FROM $table LIMIT 1"; 
$verif_cont = mysqli_query($conn_cahier_de_texte, $query_verif_cont, $conn_cdt) or die(mysqli_error($conn_cahier_de_texte));
$row_verif_cont = mysqli_num_rows($verif_cont);
if ($row_verif_cont == 1)
{
echo "La table <b>$table</b> contient des donn&eacute;es.<br>";
}
else
{
echo "Il semble que la table <b>$table</b> est vide.<br>";
}

return true;
}

function infos_tables($table, $conn_cdt){
$query_verif_infos = "SHOW TABLE STATUS LIKE '$table'"; 
$verif_infos = mysqli_query($conn_cahier_de_texte, $query_verif_infos, $conn_cdt) or die(mysqli_error($conn_cahier_de_texte));
$row_verif_infos = mysqli_fetch_array($verif_infos);
echo $row_verif_infos['Create_time'];
return true;
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php
$header_description="D&eacute;claration des absences <br> Diagnostic" ;
require_once "../../templates/default/header2.php";
?>
      <blockquote>

	<p align="center">Diagnostic de la table "ele-liste". </p>
	<p align="center"><?php check_tables("ele_liste");?></p>
	<p align="center"><?php infos_tables("ele_liste");?></p>
	
	<p align="center">Diagnostic de la table "ele-absent". </p>
	<p align="center"><?php check_tables("ele_absent");?></p>
	

	
	<p align="center">&nbsp;</p>
        <p align="left">&nbsp;</p>
      </blockquote>

	<p align="center"><a href="module_absence_install.php">Retour au Menu d'installation du module.</a>
	<p align="center"><a href="../index.php">Retour au Menu Administrateur</a>
	<p>&nbsp; </p>
<DIV id=footer></DIV>
</div>
</body>
</html>
