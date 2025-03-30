<?php
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
//-->
</script>
<SCRIPT type="text/javascript" src="../jscripts/cryptage_passe.js"></script>

<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Importation des mati&egrave;res depuis un fichier CSV";
require_once "../templates/default/header.php";

/*Importation du fichier csv*/
if(isset($_FILES['datacsv']))
{ 
	$dossier = '../fichiers_joints/';
	$fichier = basename($_FILES['datacsv']['name']);
	/*Test de l'extension*/
	$infosfichier = pathinfo($_FILES['datacsv']['name']);
	$extension_upload = $infosfichier['extension'];
	$extensions_autorisees = array('csv','txt');
	if (in_array($extension_upload, $extensions_autorisees)) { //L'extension est bonne
		/*Upload du fichier*/               
		if(move_uploaded_file($_FILES['datacsv']['tmp_name'], $dossier . $fichier)) //Si la fonction renvoie TRUE, c'est que ça a fonctionné...
		{
			echo '<br />Fichier de donn&eacute;es transf&eacute;r&eacute;<br /><br />';
		}
		else //Sinon (la fonction renvoie FALSE).
		{
			echo '<br />Echec de l\'upload !<br /><br />';
			?>
			<div align="center">
			<p><br />
			</p>
			<p><a href="index.php">Retour au Menu Administrateur</a></p>
			</div>
			<DIV id=footer></DIV>
			</DIV>
			<p>&nbsp;</p>
			</body>
			</html>
			<?php  
			exit();
		}
	}//fin extension autorisée
	else {
		echo "<br /><b><font color=\"red\">Le fichier n'est pas au bon format</font></b><br />Echec de l'upload !<br /><br />";
		?>
		<div align="center"><br /><a href="index.php">Retour au Menu Administrateur</a></div>
		<DIV id=footer></DIV>
		</DIV>
		</body>
		</html>
		<?php  
		exit();
	}
}

/*Variables*/
$fichier='../fichiers_joints/'.$fichier; //Emplacement du csv dans un dossier lect/écrit
$nbimport = 0; //initialisation du Nb de matières importése
$nomdouble = array(); //initialisation du tableau des matières non importés (doublons)

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) OR die('Erreur de connexion &agrave; la base de donn&eacute;es. Recommencez l\'installation et v&eacute;rifiez bien vos param&egrave;tres. ');

/*Ouverture du fichier csv à importer en lecture seulement*/  
if (file_exists($fichier)) {  
	$fp = fopen("$fichier", "r");  
}  
else {  
	/*le fichier n'existe pas*/  
	echo "Fichier introuvable !<br />Importation stopp&eacute;e.";
	?>
	<div align="center"><br /><a href="index.php">Retour au Menu Administrateur</a></div>
	<DIV id=footer></DIV>
	</DIV>
	</body>
	</html>
	<?php  
	exit();  
}
/*Gestion de l'affichage du résultat (Début de la Table)*/
print '<b>Mati&egrave;res import&eacute;es</b><br /><br />';  
print '<TABLE border="0" align="center">';
print '<TR valign="baseline"><TD class="Style6" align="center">Mati&egrave;res</TD></TR>'; 

while (!feof($fp)) {  
	/*Tant qu'on n'atteint pas la fin du fichier on lit une ligne*/  
	$ligne = fgets($fp,4096);
	$variable1 = str_replace(array("/", "&", "\'"), "-",trim($ligne));  
	
	/*Ajout d'un nouvel enregistrement dans la table*/
	//tester si cette matière existe déjà rajout addslashes en cas d'apostrophe dans la matière (ex : Economie d'entreprise)
	$resultquery = mysqli_query($conn_cahier_de_texte, "SELECT * FROM cdt_matiere WHERE nom_matiere = '".addslashes($variable1)."' ORDER BY nom_matiere ASC") or die(mysqli_error($conn_cahier_de_texte));
	$nombreResultat = mysqli_num_rows($resultquery);
	
	if (($nombreResultat==0) and (strlen($variable1) != 0)) { //la matière n'existe pas, elle est importée et Contrôle d'une matière obligatoire
		
		/*Insertion dans la table*/
		$sql = "INSERT IGNORE INTO `cdt_matiere` (
		`ID_matiere` ,
		`nom_matiere`
		)
		VALUES (NULL,\"$variable1\")";  
		/*Tout va bien*/  
		$nbimport++; //incrémentation du Nb de matières importées
		/*Affichage du nom de la matiere importee selon le profil 2, 3 ou 4*/
		print "<TR><TD class=\"tab_detail_gris\">$variable1</TD></TR>";
		$result= mysqli_query($conn_cahier_de_texte, $sql);
	}
	else {
		if ((strlen($variable1) != 0)) {
			array_push($nomdouble,$variable1); //Ajout dans le tableau des matières non importés (matière existe déjà)
		}
	}
	mysqli_free_result($resultquery);
}

if(mysqli_error($conn_cahier_de_texte)) {  
	/*Erreur dans la base de donnees*/  
	print "Erreur dans la base de donn&eacute;es : ".mysqli_error($conn_cahier_de_texte);
	print "<br /><br /><font color=\"red\"><b>Des lignes vides doivent se trouver en fin du fichier d'import<br />Il faut supprimer ces lignes vides et relancer l'import</b></font>";  
	print "<br /><br />Importation stopp&eacute;e.";
	?>
	<div align="center"><br /><a href="index.php">Retour au Menu Administrateur</a></div>
	<DIV id=footer></DIV>
	</DIV>
	</body>
	</html>
	<?php    
	exit();  
}  
else {  
	/*Gestion de l'affichage du résultat (Fin de la Table)*/ 
	print "</TABLE>";
	
	/*Fermeture du fichier*/  
	fclose($fp);  
	/*fin d'import affichage du Nb de matières importées*/  
	echo '<br /><b>Fin d\'importation r&eacute;alis&eacute;e avec succ&egrave;s./b><br />';  
	if ($nbimport >1) {
		echo $nbimport." mati&egrave;res ajout&eacute;es<br />";
	}
	else {
		echo $nbimport." mati&egrave;re ajout&eacute;e<br />"; 
	}
	
	/*Affichage de la liste des matières non importées (matière en double)*/
	if (sizeof($nomdouble)>0) {
		echo '<br/><b><font color="red">Les mati&egrave;res suivantes existent d&eacute;j&agrave;<br/>et n\'ont pas &eacute;t&eacute; import&eacute;es :</font></b><br />';
		for($i=0;$i<sizeof($nomdouble);$i++) // tant que $i est inferieur au nombre d'éléments du tableau...
		{
			echo $nomdouble[$i].'<br>'; // on affiche l'élément du tableau d'indice $i
		} 
	}
	
	/*Préparation de la requête d'optimisation de la table*/  
	$sql = 'OPTIMIZE TABLE cdt_matiere';  
	/*on exécute la requête*/  
	$result = mysqli_query($conn_cahier_de_texte, $sql);  
	
	if(mysqli_error($conn_cahier_de_texte)) {  
		/*Erreur dans la base de donnees*/  
		print "Erreur dans la base de donn&eacute;es : ".mysqli_error($conn_cahier_de_texte);  
		print "<br />Table non optimis&eacute;e.";  
		exit();  
	}  
	else {  
		echo '<br /><b>Table optimis&eacute;e</b><br />';  
	}
}
/*Suppression du fichier csv importé*/  
unlink($fichier);
?>
<div align="center"><br /><a href="index.php">Retour au Menu Administrateur</a></div>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($result);
?>
