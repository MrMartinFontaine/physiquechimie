<?php
ini_set('memory_limit', '128M');
include "../authentification/authcheck.php";

// en cas de version anterieure a PHP5
if (!function_exists('fputcsv'))
{
	function fputcsv($fh, $arr, $sep)
	{
		$csv = "";
		foreach($arr as $key=>$val)
		{
			$val = str_replace('"', '""', $val);
			$csv .= '"'.$val.'"'.$sep;
		}
		$csv = substr($csv, 0, -1);
		$csv .= "\n";
		if (!@fwrite($fh, $csv)) return FALSE;
	}
}

if (!function_exists('scandir'))
{
	function scandir($rep)
	{
		$dir = opendir($rep);
		$result = array();
		while ($element = readdir($dir)) $result[] = $element;
		closedir($dir);
		return $result;
	}
}

//mise en forme dans le nom des fichiers et repertoires
function mef($chaineNonValide)
{
	$chaineNonValide = preg_replace('`\s+`', '_', trim($chaineNonValide));
	$chaineNonValide = str_replace("'", "_", $chaineNonValide);
	$chaineNonValide = preg_replace('`_+`', '_', trim($chaineNonValide));
	$chaineValide=strtr($chaineNonValide,"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüýÿÑñ","AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyyNn");
	return $chaineValide;
}

//fonction recursive pour supprimer un repertoire existant
function supprimer_repertoire($path)
{
	$contenu = scandir($path);
	foreach($contenu as $element)
	{
		if($element=="." || $element =="..") continue;
		if(is_dir($path."/".$element)) supprimer_repertoire($path."/".$element);	
		else unlink($path."/".$element);
	}
	rmdir($path);	
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</head>
<body>
<div id="page">
<?php 
$header_description="Exporter son cahier de textes";
require_once "../templates/default/header.php";
?>

<?php
$prof_name = mef(str_replace(".", "",$_SESSION['nom_prof']));
$rep = "../exportation"; // repertoire ou placer les fichiers

function jour_semaine($dateX, $sep=true) // $sep signale par defaut l'existence d'un separateur dans le format jjmmaaaa
{
	$array_jours = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');
	$array_mois = array ('', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
	//$jourX = substr($dateX,0,2);
	$jourX = intval(substr($dateX,0,2));
	
	if ($sep) $moisX = substr($dateX,3,2); else $moisX = substr($dateX,2,2);
	if ($sep) $anneeX = substr($dateX,6,4); else $anneeX = substr($dateX,4,4);
	$tempsX = mktime(0, 0, 0, $moisX , $jourX, $anneeX);
	$joursemaineX = date('w',$tempsX);
	$moisanneeX = date('n',$tempsX);
	return  $array_jours[$joursemaineX]." ".$jourX." ".$array_mois[$moisanneeX]." ".$anneeX;
}
$totalRows_classes=1;
if(isset($_POST["choix"]) && $_POST["choix"]=="exportation")
{
	require_once('../Connections/conn_cahier_de_texte.php');
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	// on liste ici AUSSI les classes contenues dans les regroupements
	$query_classes =  "SELECT DISTINCT gic_ID, nom_classe, nom_matiere, cdt_classe.ID_classe,cdt_matiere.ID_matiere FROM cdt_agenda, cdt_classe, cdt_matiere WHERE `prof_ID` =".$_SESSION['ID_prof']." AND cdt_classe.ID_classe = cdt_agenda.classe_ID AND cdt_matiere.ID_matiere = cdt_agenda.matiere_ID ORDER BY gic_ID, cdt_classe.nom_classe, cdt_matiere.nom_matiere";
	$classes = mysqli_query($conn_cahier_de_texte, $query_classes);
	$row_classes = mysqli_fetch_assoc($classes);
	$totalRows_classes = mysqli_num_rows($classes);
}

// message pour confirmer a l'utilisateur ce qu'il a demande
if (isset($_POST["choix"]))
{
	if($_POST["choix"]=="exportation") {
		if($totalRows_classes==0) {
			echo "<p style=\"text-align:center; color:#f00;\">EXPORT IMPOSSIBLE : Le Cahier de Textes est vide.</p>";
		} else {
			echo "<p style=\"text-align:center; color:#f00;\">Vous avez demand&eacute; &agrave; exporter votre cahier de textes.</p>";
		}
	}
	else
	{
		//verifions que le fichier a effacer se trouve bien dans le repertoire et appartient bien a l'utilisateur
		if(preg_match("/\.\./",$_POST["choix"])) echo "<p style=\"text-align:center; color:#f00;\">Impossible de supprimer le fichier ".$_POST["choix"]." (emplacement interdit).</p>";
		elseif(!preg_match("/".$prof_name."/",$_POST["choix"])) echo "<p style=\"text-align:center; color:#f00;\">Impossible de supprimer le fichier ".$_POST["choix"]." (propri&eacute;taire incorrect).</p>";
		elseif (@unlink($rep."/".$_POST["choix"])) echo "<p style=\"text-align:center; color:#f00;\">Le fichier ".$_POST["choix"]." a &eacute;t&eacute; effac&eacute; avec succ&egrave;s.</p>";
		else echo "<p style=\"text-align:center; color:#f00;\">Probl&egrave;me lors de l'effacement du fichier ".$_POST["choix"].". Contacter l'administrateur.</p>";
	}
}

// recherche d'un fichier de sauvegarde deja present sur le serveur
$find_zip = opendir($rep);
while ($element = readdir($find_zip))
{
	if(is_file($rep."/".$element) && preg_match("/".$prof_name."\.zip$/", $element))
	{
		$old_zip = $element;
		break; // il ne peut exister qu'un seul fichier ZIP au nom d'un meme enseignant donc inutile de poursuivre la boucle
	}
}
closedir($find_zip);
?>

<p style="text-align:left; padding:10px 50px;">Le contenu de vos cahiers de textes est export&eacute; sous la forme de fichiers <acronym title="Comma-Separated Values">CSV</acronym> lisibles par un tableur.<br/>L'ensemble des documents utilis&eacute;s est rappel&eacute; dans ces fichiers mais n'est pas fourni dans l'archive.<br/><br/>L'exportation se fait sous la forme d'un fichier compress&eacute; (format ZIP) dont l'exemplaire le plus r&eacute;cent est conserv&eacute; par d&eacute;faut sur le serveur.</p>
<form action="exportationCSV.php" method="post">
<fieldset style="margin:10px 150px; border:1px solid #006; padding:0px;">
<legend style="margin:0px 5px;">Que souhaitez-vous faire ?</legend><table style="margin:0px; padding:0px; border:none; width:100%;">
<tr><td style="text-align:left; padding:5px 5px 0px; font-size:0.8em;"><input type="radio" name="choix" value="exportation"/>Cr&eacute;er un fichier &agrave; exporter</td><td style="text-align:right; padding:0px 5px 5px;"><input type="submit" value="Valider"/></td></tr>
<?php



// bouton de suppression uniquement si un fichier existe deja ou existera a la fin du script...
if (isset($old_zip))
	echo " <tr><td style=\"text-align:left; padding:0px 5px 5px; font-size:0.8em;\"><input type=\"radio\" name=\"choix\" value=\"$old_zip\"/>Supprimer la sauvegarde pr&eacute;sente sur le serveur</td></tr>";
elseif (isset($_POST["choix"]) && $_POST["choix"]=="exportation" && ($totalRows_classes!=0))
	echo "<tr><td style=\"text-align:left; padding:0px 5px 5px; font-size:0.8em;\"><input type=\"radio\" name=\"choix\" value=\"\"/>Supprimer la sauvegarde pr&eacute;sente sur le serveur</td></tr>";
?>
</table>
</fieldset></form>
<p align="center"><a href="enseignant.php">Retour au Menu Enseignant</a></p>
<p>&nbsp;</p>

<?php

if(isset($old_zip))
{
	preg_match("/^[0-9]{8}/", $old_zip, $tab_date);
	$form_date = jour_semaine($tab_date[0], false);
	echo "<div style=\"text-align:center; border:1px solid #006; width:350px; margin:0px auto 20px; padding:10px;\"><div id=\"zipDIV\" style=\"font-weight:bold; padding:0px 0px 10px; margin:0px;\">Fichier d&eacute;tect&eacute; (cr&eacute;&eacute; le $form_date) :</div><a id=\"zipA\" href=\"".$rep."/".$old_zip."\">$old_zip</a></div>";
}
else
echo "<div style=\"text-align:center; border:1px solid #006; width:350px; margin:0px auto 20px; padding:10px;\"><div id=\"zipDIV\" style=\"font-weight:bold; padding:0px 0px 10px; margin:0px;\">Aucune sauvegarde n'est pr&eacute;sente sur le serveur.</div><a id=\"zipA\" href=\"#\"></a></div>";

if(isset($_POST["choix"]) && $_POST["choix"]=="exportation")
{
	// creation du repertoire ou placer les fichiers a exporter et choix du nom du fichier compresse
	$path = sprintf("%s/%s-CDT-%s", $rep, date('dmY'), $prof_name);
	$path_zip = sprintf("%s-CDT-%s", date('dmY'), $prof_name);
	if(is_dir($path)) supprimer_repertoire($path); //si une premiere tentative d'exportation la meme journee aurait echoue avant la fin
	mkdir($path);
	$name = $path.".zip";
	
	if ($totalRows_classes!=0) {
		
		// ***************************** creation d'un fichier CSV pour chaque classe
		$last_gic_ID=0;
		do
		{
			//si regroupement, on traite uniquement la premiere classe
			if (($row_classes['gic_ID']==0)||(($row_classes['gic_ID']<>0)&&($row_classes['gic_ID']<>$last_gic_ID)))
			{
				if 	($row_classes['gic_ID']<>0	){			
					// Rechercher le nom du regroupement
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$query_RsG =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u ",$row_classes['gic_ID']);
					$RsG = mysqli_query($conn_cahier_de_texte, $query_RsG) or die(mysqli_error($conn_cahier_de_texte));
					$row_RsG = mysqli_fetch_assoc($RsG);
					$name_file = str_replace("/","_",mef(sprintf("%s-%s.csv",  $row_RsG['nom_gic'], $row_classes['nom_matiere'])));
				}	
				else
				{	
					$name_file = str_replace("/","_",mef(sprintf("%s-%s.csv",$row_classes['nom_classe'], $row_classes['nom_matiere'])));
				};
				
				$fd = fopen($path."/".$name_file,"wt");
				fputcsv($fd,array("Date","Heure","Groupe","Type de s&eacute;ance","Contenu de la s&eacute;ance","Travail n&deg;1","Travail n&deg;2","Travail n&deg;3","Travail n&deg;4"),";");
				fputcsv($fd,array("","","","","","","","",""),";");
				
				// recherche des informations pour la classe concernee
				$query_agenda = sprintf("SELECT * FROM cdt_agenda WHERE classe_ID=%u AND prof_ID=%u AND matiere_ID=%u ORDER BY code_date", $row_classes['ID_classe'], $_SESSION['ID_prof'], $row_classes['ID_matiere']);
				$agenda = mysqli_query($conn_cahier_de_texte, $query_agenda);
				$ligne = array(); // construction du tableau dont les elements representent une ligne du fichier CSV a fournir
				
				// chaque entree dans l'agenda est a placer dans le fichier csv avec les informations correspondantes
				while($row_agenda = mysqli_fetch_assoc($agenda))
				{
					$ligne[] = $row_agenda['jour_pointe'];
					$ligne[] = $row_agenda['heure_debut'];
					$ligne[] = $row_agenda['groupe'];
					$ligne[] = $row_agenda['type_activ'];
					$chaine = "";
					
					// verification de presence d'un titre pour la seance
					if (isset($row_agenda['theme_activ'])) {$chaine .= " * ".strtoupper(trim($row_agenda['theme_activ']))." * ";}
					
					// un enregistrement peut avoir ete effectue sans presence d'une description de la seance qui apparait alors vide dans la table et non a NULL
					// verification de la presence d'au moins une lettre sans accent ou un chiffre dans cette description
					$activite = trim(html_entity_decode(strip_tags($row_agenda['activite']),ENT_QUOTES));
					if (preg_match("/[[:alnum:]]+/",$activite)) {$chaine .= $activite;} else {$chaine.= "Description de la s&eacute;ance non fournie.";}
					
					// documents joints a la seance
					$query_documents = "SELECT * FROM cdt_fichiers_joints WHERE type='Cours' AND agenda_ID=".$row_agenda['ID_agenda'];
					$documents = mysqli_query($conn_cahier_de_texte, $query_documents);
					$totalRows_documents = mysqli_num_rows($documents);
					if ($totalRows_documents<>0)
					{
						$chaine .= " (DOCUMENTS : ";
						$tiret=false;
						while ($row_documents = mysqli_fetch_assoc($documents))
						{
							$exp = "/^[0-9]+_/";
							$nom_f = mef(preg_replace($exp, '', $row_documents['nom_fichier']));
							if ($tiret) {$chaine .= " - ".$nom_f;} else {$chaine .= $nom_f; $tiret = true;}
						}
						$chaine .= ")";
					}
					mysqli_free_result($documents);
					
					// annotations eventuelles
					if (isset($row_agenda['rq'])) {$chaine .=" ~ REMARQUES : ".trim($row_agenda['rq']);}	
					$ligne[] = $chaine;
					
					// travail a faire
					$query_devoirs = "SELECT * FROM cdt_travail WHERE agenda_ID=".$row_agenda['ID_agenda'];
					$devoirs = mysqli_query($conn_cahier_de_texte, $query_devoirs);
					$totalRows_devoirs = mysqli_num_rows($devoirs);
					
					if ($totalRows_devoirs<>0) // un travail n'est present dans la base de donnees que si il a ete date et avec un contenu (cf ecrire.php)
					{
						while ($row_devoirs = mysqli_fetch_assoc($devoirs))
						{
							$chaine = sprintf("Pour le %s (%s) ~ %s", jour_semaine($row_devoirs['t_code_date']), trim($row_devoirs['t_groupe']), trim(html_entity_decode(strip_tags($row_devoirs['travail']),ENT_QUOTES)));
							
							// documents joints au travail a faire
							$query_fichiers = sprintf("SELECT nom_fichier FROM cdt_fichiers_joints WHERE type='Travail' AND agenda_ID=%u AND ind_position=%u", $row_agenda['ID_agenda'], $row_devoirs['ind_position']);
							$fichiers = mysqli_query($conn_cahier_de_texte, $query_fichiers);
							$totalRows_fichiers = mysqli_num_rows($fichiers);
							if ($totalRows_fichiers<>0)
							{
								$chaine .= " (DOCUMENTS : ";
								$tiret = false;
								while ($row_fichiers = mysqli_fetch_assoc($fichiers))
								{
									$exp = "/^[0-9]+_/";
									$nom_f = mef(preg_replace($exp, '', $row_fichiers['nom_fichier']));
									if ($tiret) {$chaine .= " - ".$nom_f;} else {$chaine .= $nom_f; $tiret = true;}
								}
								$chaine .= ")";
							}	
							mysqli_free_result($fichiers);
							$ligne[] = $chaine;
						}
					}
					else {$ligne[] = "Travail non sp&eacute;cifi&eacute;.";}
					mysqli_free_result($devoirs);
					//var_dump($ligne); echo "<br/>";
					fputcsv($fd,$ligne,";");
					unset($ligne);
				}
				mysqli_free_result($agenda);
				fclose($fd);
			}
			$last_gic_ID=$row_classes['gic_ID'];
		}
		while($row_classes = mysqli_fetch_assoc($classes));
		mysqli_free_result($classes);
		
		//**************************************** compression du dossier a exporter
		
		require_once("../inc/zip.lib.php");
		$zip= new zipfile();
		
		$contenu = scandir($path);
		sort($contenu);
		echo "
		<p style=\"text-align:center; margin-bottom:0px;\">-- R&eacute;capitulatif des fichiers g&eacute;n&eacute;r&eacute;s pour l'exportation --</p>
		<ul style=\"text-align:left; line-height:2em; padding-left:100px;\">\n";
		foreach($contenu as $element)
		{
			if($element == "." || $element == "..") continue;
			if(is_file($path."/".$element))
			{
				$zip->addFile(file_get_contents($path."/".$element), $path_zip."/".$element);
				echo "<li>".$element."</li>\n";
				unlink($path."/".$element);
			}
		}	
		rmdir($path);
		echo "</ul>";
		
		// creation du fichier compresse
		$filezipped = $zip->file();
		$fp = fopen($name, "w");
		fwrite($fp, $filezipped); 
		fclose($fp);
		
		// complete la page HTML une fois le fichier ZIP cree
		$form_date = jour_semaine(date('dmY'), false);
		$file_name = array_pop(explode("/",$name));
		echo "
		<script language=\"javascript\">
		document.getElementById(\"zipDIV\").innerHTML=\"Nouveau fichier g&eacute;n&eacute;r&eacute; le $form_date :\";
		document.getElementById(\"zipA\").href=\"$name\";
		document.getElementById(\"zipA\").innerHTML=\"$file_name\";
		document.getElementById(\"zipA\").style.color=\"#f00\";
		document.forms[0].elements[3].value=\"$file_name\";
		</script>";
		
		// suppression s'il existe d'un fichier compresse precedemment cree pour le professeur concerne
		$verif = opendir($rep);
		while ($recherche = readdir($verif)) if(is_file($rep."/".$recherche) && preg_match("/".$prof_name."\.zip$/", $recherche) && $rep."/".$recherche!=$name) unlink($rep."/".$recherche);
		closedir($verif);
	}
	echo "<div id=\"footer\"><p align=\"center\"><a href=\"enseignant.php\">Retour au Menu Enseignant</a></p></div>";
	
}
else { echo"<div id=\"footer\"></div>"; }
?>

</div>
</body>
</html>
