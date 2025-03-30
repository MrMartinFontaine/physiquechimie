<?php
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
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
$header_description="Importation des utilisateurs depuis un fichier CSV";
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
$nbimport = 0; //initialisation du Nb de profs importés
$nomdouble = array(); //initialisation du tableau des profs non importés (doublons)
$liste = array(); //initialisation du tableau d'importation

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) OR die('Erreur de connexion &agrave; la base de donn&eacute;es. Recommencez l\'installation et v&eacute;rifiez bien vos param&egrave;tres. ');

//$query_Recordset1 = "SELECT * FROM cdt_prof";
//$Recordset1 = mysqli_query($conn_cahier_de_texte, $query_Recordset1) or die(mysqli_error($conn_cahier_de_texte));

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
print '<b>Comptes import&eacute;s</b><br /><br />';  
print '<TABLE border="0" align="center">';
print '<TR valign="baseline"><TD class="Style6" align="center">Nom</TD><TD class="Style6" align="center">Mot de passe</TD><TD class="Style6" align="center">Identit&eacute;</TD><TD class="Style6" align="center">Email</TD><TD class="Style6" align="center">Compte</TD></TR>'; 
  
while (!feof($fp)) {  
/*Tant qu'on n'atteint pas la fin du fichier on lit une ligne*/  
$ligne = fgets($fp,4096);  
/*Récupération des champs séparés par ; dans liste*/  
$liste = explode( ";",$ligne);  
/*Assignation des variables*/  
$variable1 = $liste[0];  
$variable2 = $liste[1];  
$variable3 = $liste[2];  
$variable4 = $liste[3];  
$variable5 = $liste[4];

/*Gestion des données vides*/
if ($variable1 == "") {  //Contrôle du nom long du prof
  $variable1 = $variable3; //affectation de l'identité (login) si le nom est vide
  
}

//suppressions d'eventuels accents sur le login
$variable3=sans_accent($variable3);

if ($variable2 == "") {  //Contrôle du mot de passe /*Cryptage du mot de passe */
  // $variable2 = $variable3; //affectation de l'identité si le password est vide
  $variable2="$2y$10$J9yakmjaV/EFLzIjpLddU.XGKPHeFuUnx0XqQhmBc8zELhMLkcvFm"; //mot de passe vide crypté 
}
else {
$variable2 = password_hash($variable2, PASSWORD_DEFAULT);
};

error_reporting(E_ERROR); //Désactivation du warning pour div/0 de $variable5
if (($variable5/$variable5 != 1) or ($variable5 > 4)) { //Contrôle du profil
  $variable5=2;
}
error_reporting(E_WARNING); //Réactivation du warning



  
/*Ajout d'un nouvel enregistrement dans la table*/
//tester si ce nom_prof existe déjà
$resultquery = mysqli_query($conn_cahier_de_texte, "SELECT * FROM cdt_prof WHERE nom_prof = '".$variable3."'") or die(mysqli_error($conn_cahier_de_texte));
$nombreResultat = mysqli_num_rows($resultquery);

if ($nombreResultat==0) { //l'enseignant n'existe pas, il est importé

if ($variable3 != "") { //Contrôle d'une identité obligatoire

/*Insertion dans la table*/
$sql = "INSERT IGNORE INTO `cdt_prof` (
`ID_prof` ,
`nom_prof` ,
`passe` ,
`identite` ,
`email` ,
`gestion_sem_ab` ,
`publier_cdt` ,
`publier_travail` ,
`date_maj` ,
`droits` ,
`path_fichier_perso` ,
`xinha_editlatex` ,
`xinha_equation` ,
`xinha_stylist`
)
VALUES (NULL,'$variable3','$variable2',\"$variable1\",'$variable4','O','O','O','0000-00-00','$variable5',NULL,'N','N','N')";  
/*Tout va bien*/  
$nbimport++; //incrémentation du Nb d'enseignant importé
/*Affichage du nom d'enseignant importé selon le profil 2, 3 ou 4*/
if ($variable2=="d41d8cd98f00b204e9800998ecf8427e"){$ch_mdp='';}else{$ch_mdp='********';};
  switch ($variable5) {
  case 2:
      print "<TR><TD class=\"tab_detail_gris\">$variable1</TD><TD class=\"tab_detail_gris\">$ch_mdp</TD><TD class=\"tab_detail_gris\">$variable3</TD><TD class=\"tab_detail_gris\">$variable4</TD><TD class=\"tab_detail_gris\">Enseignant</TD></TR>";
      break;
  case 3:
      print "<TR><TD class=\"tab_detail_gris\">$variable1</TD><TD class=\"tab_detail_gris\">$ch_mdp</TD><TD class=\"tab_detail_gris\">$variable3</TD><TD class=\"tab_detail_gris\">$variable4</TD><TD bgcolor=\"#FFFF80\">Vie Scolaire</TD></TR>";
      break;
  case 4:
      print "<TR><TD class=\"tab_detail_gris\">$variable1</TD><TD class=\"tab_detail_gris\">$ch_mdp</TD><TD class=\"tab_detail_gris\">$variable3</TD><TD class=\"tab_detail_gris\">$variable4</TD><TD bgcolor=\"#80FF80\">Resp. Etablissement</TD></TR>";
      break;
  default:
      print "<TR><TD>$variable1</TD><TD>$variable2</TD><TD>$variable3</TD><TD>$ch_mdp</TD><TD bgcolor='#FF0000'>Inconnu !!</TD></TR>";
  }
}
  $result= mysqli_query($conn_cahier_de_texte, $sql);
}
else {
array_push($nomdouble,$variable1); //Ajout dans le tableau des enseignants non importés (nom existe déjà)
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
/*fin d'import affichage du Nb d'enregistrements importés*/  
echo '<br /><b>Fin d\'importation r&eacute;alis&eacute;e avec succ&egrave;s.</b><br />';  
if ($nbimport >1) {
echo $nbimport." Enregistrements ajout&eacute;s<br />";
}
else {
echo $nbimport." Enregistrement ajout&eacute;<br />"; 
}

/*Affichage de la liste des enseignants non importés (nom en double)*/
if (sizeof($nomdouble)>0) {
echo '<br/><b><font color="red">Les personnes suivantes existent d&eacute;j&agrave;<br />et n\'ont pas &eacute;t&eacute; import&eacute;es :</font></b><br />';
for($i=0;$i<sizeof($nomdouble);$i++) // tant que $i est inferieur au nombre d'éléments du tableau...
    {
    echo $nomdouble[$i].'<br>'; // on affiche l'élément du tableau d'indice $i
    } 
}
  
/*Préparation de la requête d'optimisation de la table*/  
$sql = 'OPTIMIZE TABLE cdt_prof';  
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
