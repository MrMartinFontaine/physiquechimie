<?php
include "../authentification/authcheck.php";
if ($_SESSION['nom_prof']<>"Administrateur") die;

function jour_semaine($dateX, $sep=true) // $sep signale par défaut l'existence d'un séparateur dans le format jjmmaaaa
	{
	$array_jours = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');
	$array_mois = array ('', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
	$jourX = substr($dateX,0,2);
	if ($sep) $moisX = substr($dateX,3,2); else $moisX = substr($dateX,2,2);
	if ($sep) $anneeX = substr($dateX,6,4); else $anneeX = substr($dateX,4,4);
	$tempsX = mktime(0, 0, 0, $moisX , $jourX, $anneeX);
	$joursemaineX = date('w',$tempsX);
	$moisanneeX = date('n',$tempsX);
	return  $array_jours[$joursemaineX]." ".$jourX." ".$array_mois[$moisanneeX]." ".$anneeX;
	}

function taille($t)
	{
	if ($t >= 1073741824) $t = round($t/1073741824, 2)." Go";
	elseif ($t >= 1048576) $t = round($t/1048576, 2)." Mo";
	elseif ($t >= 1024) $t = round($t/1024, 2)." Ko";
	else $t = $t." octets"; 
	return $t;
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<link href="../styles/style_default.css" rel="stylesheet" type="text/css">

<style>
table {width:500px; margin:0px auto 10px; padding:0px; border:none;}
tr {margin:0px; padding:0px; border:none;}
td {margin:0px; padding:5px; border:none;}
</style>

<script language="javascript">
function selectAll(count)
	{
	var ischecked = document.getElementById("checkall").checked;
	for(var i=1; i<=count; i++) { document.getElementById("checkbox"+i).checked = ischecked; }
	}

function selectOne(objet, count)
	{
	var checkall = document.getElementById("checkall");
	if(checkall.checked == true && objet.checked == false) checkall.checked = false;
	else
		{
		if(checkall.checked == false && objet.checked == true)
			{
			var allchecked = true;
			for(var i=1; i<=count; i++) if(document.getElementById("checkbox"+i).checked == false)  { allchecked = false; break; }
			if(allchecked == true) checkall.checked = true;
			}
		}
	}
</script>

</head>
<body>
<div id="page">
<div id="header">
<div style="margin:0px; padding:0px; border:0px">&nbsp;</div>
<h1>Cahier de textes</h1>
<div class="description"><p>ADMINISTRATION<br/>Gestion des fichiers d'exportation</p></div>
</div>

<?php
$rep = "../exportation"; // répertoire où se trouvent les fichiers

// suppression des fichiers sélectionnés
if (isset($_POST["test"]))
	{
	if (isset($_POST["delete"]))
		{
		$cnt = 0;
		foreach($_POST["delete"] as $file) { unlink($rep."/".$file); $cnt++; }
		if ($cnt==1) echo "<p style=\"color:#f00;\">Un fichier a &eacute;t&eacute; supprim&eacute; avec succ&egrave;s.</p>";
		else echo "<p style=\"color:#f00;\">$cnt fichiers ont &eacute;t&eacute; supprim&eacute;s avec succ&egrave;s.</p>";
		}
	else echo "<p style=\"color:#f00;\">Veuillez s&eacute;lectionner au moins un fichier avant de valider la suppression.</p>";
	}
?>

<p style="text-align:left; padding:10px 50px; line-height:2em;">Seule la version la plus r&eacute;cente d'un fichier d'exportation cr&eacute;&eacute; par un enseignant est conserv&eacute;e.<br/>
  Chaque fichier se pr&eacute;sente sous la forme "<i>jjmmaaaa</i>-CDT-<i>nomprof</i>.zip".</p>

<?php
// recherche des fichiers de sauvegarde présents sur le serveur
$find_zip = opendir($rep);
while ($element = readdir($find_zip)) if(is_file($rep."/".$element) && preg_match("/\.zip$/", $element)) $zip_files[filemtime($rep."/".$element)] = $element;
closedir($find_zip);
ksort($zip_files); // on classe les fichiers selon leur date de création
$n = count($zip_files);

if ($n != 0)
	{
	echo "<p style=\"font-weight:bold;\">Voici le d&eacute;tail des fichiers d'exportation d&eacute;tect&eacute;s sur le serveur :</p>";
	echo "<form action=\"exportation_supprime.php\" method=\"post\">";
	echo "<table>
	<tr><td><input type=\"checkbox\" id=\"checkall\" onclick=\"selectAll($n);\"/></td><td style=\"text-align:left;\" colspan=\"3\">Tout s&eacute;lectionner</td></tr>
	<tr><th></th><th>Ra g</th><th>Date de cr&eacute;ation</th><th>Nom de l'enseignant</th><th>Taille du fichier</th></tr>";
	$total = 0;
	$i = 1;
	foreach($zip_files as $file)
		{
		if(preg_match("/^[0-9]{8}-CDT-.+\.zip$/", $file)) // on vérifie que le format du fichier zip est le bon
			{
			// on récupère les éléments provenant du nom du fichier
			$date_code = substr($file, 0, 8);
			$nom_prof = str_replace(".zip", "", substr($file, 13));
			if($i%2==0) echo "<tr style=\"background:#ffc;\">";
			else echo "<tr style=\"background:#cfc;\">";
			echo "<td><input type=\"checkbox\" id=\"checkbox$i\" name=\"delete[$i]\" value=\"$file\" onclick=\"selectOne(this, $n);\"/></td><td>$i</td><td>".jour_semaine($date_code, false)."</td><td>".$nom_prof."</td>";
			}
		else // format du fichier zip incorrect
			{
			if($i%2==0) echo "<tr style=\"background:#ffc;\">";
			else echo "<tr style=\"background:#cfc;\">";
			echo "<td><input type=\"checkbox\" id=\"checkbox$i\" name=\"delete[$i]\" value=\"$file\" onclick=\"selectOne(this, $n);\"/></td><td>$i</td><td colspan=\"2\">Fichier au format non valide ($file)</td>";
			}
		$file_size = filesize($rep."/".$file);
		echo "<td>".taille($file_size)."</td></tr>";
		$total += $file_size;
		$i++;
		}
	echo "<tr><td style=\"text-align:right;\" colspan=\"4\">Taille totale : </td><td style=\"background:#ccf;\">".taille($total)."</td></tr></table>
	<input type=\"submit\" value=\"Supprimer les fichiers s&eacute;lectionn&eacute;s\"/>
	<input type=\"hidden\" name=\"test\" value=\"test\"/>
	</form>";
	}
else echo "<p style=\"font-weight:bold;\">Aucun fichier d'exportation n'est pr&eacute;sent sur le serveur.</p>";
?>
  <p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</div>
</body>
</html>
