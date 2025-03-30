<?php
include "../../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Importation des &eacute;l&egrave;ves depuis un fichier CSV - Module d&eacute;claration des absences</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
//-->
</script>
<SCRIPT type="text/javascript" src="../../jscripts/cryptage_passe.js"></script>

<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Importation des &eacute;l&egrave;ves depuis un fichier CSV - Module d&eacute;claration des absences";
require_once "../../templates/default/header2.php";

/*Importation du fichier csv*/
if(isset($_FILES['elevesdatacsv']))
{ 
     $dossier = '../../fichiers_joints/';
     $fichier = basename($_FILES['elevesdatacsv']['name']);
     /*Test de l'extension*/
     $infosfichier = pathinfo($_FILES['elevesdatacsv']['name']);
     $extension_upload = $infosfichier['extension'];
     $extensions_autorisees = array('csv','txt');
     if (in_array($extension_upload, $extensions_autorisees)) { //L'extension est bonne
       /*Upload du fichier*/               
       if(move_uploaded_file($_FILES['elevesdatacsv']['tmp_name'], $dossier . $fichier)) //Si la fonction renvoie TRUE, c'est que ca a fonctionne...
       {
            echo '<br />Fichier de donn&eacute;es transf&eacute;r&eacute;<br /><br />';
       }
       else //Sinon (la fonction renvoie FALSE).
       {
            echo "<br />Echec de l'upload !<br /><br />";
            ?>
            <div align="center">
              <p><br />
              </p>
              <p>&nbsp;</p>
              <p><a href="../index.php">Retour au Menu Administrateur</a></p>
            </div>
            <DIV id=footer></DIV>
</DIV>
            <p>&nbsp;</p>
</body>
            </html>
            <?php  
            exit();
       }
     }//fin extension autorisee
     else {
        echo '<br /><b><font color="red">Le fichier n\'est pas au bon format</font></b><br />Echec de l\'upload !<br /><br />';
        ?>
        <br />
	  <p align="center"><a href="../index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
        </DIV>
        </body>
        </html>
        <?php  
        exit();
     }
}

/*Variables*/
$fichier='../../fichiers_joints/'.$fichier; //Emplacement du csv dans un dossier lect/ecrit
$nbimport = 0; //initialisation du Nb d'eleves importes
$nbimport_echec = 0; //initialisation du Nb d'eleves non importes
$nomdouble = array(); //initialisation du tableau d'eleves non importes (doublons)
$liste = array(); //initialisation du tableau d'importation

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) OR die("Erreur de connexion &agrave; la base de donn&eacute;es. Recommencez l'installation et v&eacute;rifiez bien vos param&egrave;tres. ");

/*Ouverture du fichier csv a importer en lecture seulement*/  
if (file_exists($fichier)) {  
$fp = fopen("$fichier", "r");  
}  
else {  
/*le fichier n'existe pas*/  
echo "Fichier introuvable !<br />Importation stopp&eacute;e.";
?>
<br />
	  <p align="center"><a href="../index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php  
exit();  
}
/*Gestion de l'affichage du resultat (Debut de la Table)*/
print '<b>Comptes import&eacute;s</b><br /><br />';  
print '<TABLE border="0" align="center">';
print '<TR valign="baseline"><TD class="Style6" align="center">Nom</TD><TD class="Style6" align="center">Pr&eacute;nom</TD><TD class="Style6" align="center">Classe</TD><TD class="Style6" align="center">R&eacute;sultat</TD></TR>'; 
  
while (!feof($fp)) {  
/*Tant qu'on n'atteint pas la fin du fichier on lit une ligne*/  
$ligne = fgets($fp,4096);  
/*Recuperation des champs separes par ; dans liste*/  
$liste = explode( ";",$ligne);  

        if (count($liste ) >= 3 ) 
	{		

	/*Assignation des variables*/  
	$variable1 = trim($liste[0]);   
	$variable2 = trim($liste[1]);
	$variable3 = trim($liste[2]);
		
	$variable1 = trim($variable1, "\"'");   
	$variable2 = trim($variable2, "\"'");   
	$variable3 = trim($variable3, "\"'");   
		
	$variable1 = htmlentities($variable1, ENT_QUOTES, 'ISO-8859-15');   
	$variable2 = htmlentities($variable2, ENT_QUOTES, 'ISO-8859-15');
	$variable3 = htmlentities($variable3, ENT_QUOTES, 'ISO-8859-15');

          
        /*Ajout d'un nouvel enregistrement dans la table*/
                if (($variable1 != "") AND  ($variable2 != "") AND ($variable3 != "") )  { //Controle d'une identite obligatoire

                        /*Insertion dans la table*/
                        $sql = "INSERT IGNORE INTO `ele_liste` (
			`ID_ele` ,
			`nom_ele` ,
			`prenom_ele` ,
			`classe_ele` 
			)
			VALUES (NULL,'$variable1','$variable2','$variable3')";  			 

			  $result= mysqli_query($conn_cahier_de_texte, $sql);
			  if(mysqli_error($conn_cahier_de_texte)) { 
				  $nbimport_echec++;
                                print "<TR><TD>$variable1</TD><TD>$variable2</TD><TD>$variable3</TD><TD bgcolor='#FF0000'>Erreur SQL</TD></TR>";
                          } else {
                                /*Tout va bien*/  
                                $nbimport++; //incrementation du Nb d'eleves importe
                                  print "<TR><TD>$variable1</TD><TD>$variable2</TD><TD>$variable3</TD><TD bgcolor='#0ff825'>Succ&egrave;s</TD></TR>";
                          }
                  
		}
		else {
		  $nbimport_echec++;
		  print "<TR><TD>$variable1</TD><TD>$variable2</TD><TD>$variable3</TD><TD bgcolor='#efa517'>Incomplet</TD></TR>";
		}
	} else {
		$nbimport_echec++;
		  print "<TR><TD>$variable1</TD><TD>$variable2</TD><TD>$variable3</TD><TD bgcolor='#efa517'>Mal format&eacute;</TD></TR>";
        }
}

/*Gestion de l'affichage du resultat (Fin de la Table)*/ 
print "</TABLE>";

        /*Fermeture du fichier*/  
        fclose($fp);  
        /*fin d'import affichage du Nb d'enregistrements importes*/  
        echo '<br /><b>Fin d\'importation r&eacute;alis&eacute; avec succ&egrave;s.</b><br />';  
        if ($nbimport >1) {
                echo $nbimport." &eacute;l&egrave;ves ajout&eacute;s<br />";
	} else {
                echo $nbimport." &eacute;l&egrave;ves ajout&eacute;<br />"; 
        }

/*Affichage de la liste des enseignants non importes */

        if ($nbimport_echec >=2) {
                echo "<b><font color='red'>$nbimport_echec</font></b> &eacute;l&egrave;ves n'ont pas &eacute;t&eacute; import&eacute;s<br />"; 
	} else  if ($nbimport_echec == 1){
		echo "<b><font color='red'>$nbimport_echec</font></b> &eacute;l&egrave;ve n'a pas &eacute;t&eacute; import&eacute;<br />"; 
        }

  
//Preparation de la requete d'optimisation de la table
$sql = 'OPTIMIZE TABLE `ele_liste`';  
//on execute la requete
$result = mysqli_query($conn_cahier_de_texte, $sql);  

if(mysqli_error($conn_cahier_de_texte)) {  
  //Erreur dans la base de donnees
  print "Erreur dans la base de donn&eacute;es : ".mysqli_error($conn_cahier_de_texte);  
  print "<br />Table non optimis&eacute;e.";  
  exit();  
}  
else {  
  echo '<br /><b>Table optimis&eacute;e</b><br />';  
}

//}
/*Suppression du fichier csv importe*/  
unlink($fichier);
?>
        <p align="center"><a href="module_absence_install.php">Retour au menu d'installation du module.</a></p>

	  <p align="center"><a href="../index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>

