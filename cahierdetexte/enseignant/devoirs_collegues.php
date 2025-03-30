<?php
require_once('../Connections/conn_cahier_de_texte.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$rq2=mysqli_query($conn_cahier_de_texte, "SELECT * FROM cdt_travail,cdt_matiere,cdt_prof WHERE cdt_travail.matiere_ID= cdt_matiere.ID_matiere AND  cdt_travail.prof_ID= cdt_prof.ID_prof AND cdt_travail.classe_ID=".$_POST["Classe"]." AND cdt_travail.codedate >".date('Y,md')."  ORDER BY matiere_ID");	
	echo $rq2;
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
</head>

<body>
</body>
</html>
