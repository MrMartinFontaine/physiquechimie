<?php
 
// on va se connecter au serveur et choisir sa base de données
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};

include('../Connections/conn_cahier_de_texte.php'); 
require_once('../inc/functions_inc.php');

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$base = mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<style type="text/css">
                h2
                {
                   text-align: center;  
                   color: #4682b4;
                }
                
                p
                {
                   text-align: center;
                }
                
                .titre_table
                {
                   font-size: 20px;
                }
                
                .Style1 {
	color: #FF0000;
	font-weight: bold;
}
</style>
</head>
<body>
<!-- On commence par mettre un titre à sa page -->
<table width="100%" border="0" align="center" cellspacing="0">
  <tr >
    <td class="Style6"><div align="center">CAHIER DE TEXTES - <?php echo $_SESSION['nom_etab'];?> - APERCU DES TABLES </div></td>
    <td class="Style6"><div align="right"><a href="index.php"><img src="../images/home-menu.gif" alt="Accueil" width="23" height="17" border="0"></a>&nbsp;&nbsp;</div></td>
  </tr>
</table>
<p>&nbsp;</p>

  <table width="90%" border="1" align="center" cellspacing="0">
    <tr>
      <td valign="top" class="tab_detail_gris">
	  <ul><?php
// on va faire une requête pour rechercher toutes les tables de la bdd concernée
$req_table = "SHOW TABLES";
$result_table = mysqli_query($conn_cahier_de_texte, $req_table) or die ("Impossible d'ex&eacute;cuter la requ&ecirc;te concernant la recherche des tables - ".mysqli_error($conn_cahier_de_texte));
 
// et on va les afficher sous forme de lien

while ($donnees_table = mysqli_fetch_array($result_table))
{
    echo ' <div align="left"><a href="controle_tables_cdt.php?table='.$donnees_table[0].'"><li>'.$donnees_table[0].'</a></li><br /></div>';
}



?></ul></td>
      <td valign="top">
	  <?php mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsparams1 = "SELECT param_val FROM cdt_params WHERE param_nom='version'";
$Rsparams1 = mysqli_query($conn_cahier_de_texte, $query_Rsparams1) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsparams1 = mysqli_fetch_assoc($Rsparams1);
if (isset($row_Rsparams1['param_val']) ) {
echo '<p align="left"><span class="Style71">Version install&eacute;e : '.$row_Rsparams1['param_val'].'</span></p>';
};?>
	    <p><span class="Style1">Si des champs sont manquants, il est sans doute n&eacute;cessaire de forcer la mise &agrave; jour depuis une version ant&eacute;rieure.</span> <a href="misajour/maj_forcee.php">Voir ici.</a>	  
          <?php
if (isset($_GET['table'])){
    
	echo '<p>&nbsp;</p><p>&nbsp;</p>';
    $table = $_GET['table'];
                                
    
                
        // on va créer une variable pour y mettre le texte concernant l'en-tête de la structure qui sera écrit dans le fichier .txt
    $titre_structure = "";
    $titre_structure .= "-- <br />";
    $titre_structure .= "-- Structure de la table ` ".$table." ` <br />";
    $titre_structure .= "--  <br /> <br /> ";
    echo $titre_structure;
                
        // on va demander la "création" de la table
    $req_structure = "SHOW CREATE TABLE $table ";
    $result_structure = mysqli_query($conn_cahier_de_texte, $req_structure) or die ("Impossible de trouver la structure de ". $table .mysqli_error($conn_cahier_de_texte));
    $donnee_structure = mysqli_fetch_array($result_structure);
    $structure = "";
    $structure .= $donnee_structure[1] ;
    $structure .= "; <br /> <br />" ;
                                
    // on affiche la structure sur la page PHP
    echo "<pre>". $structure ."</pre>" ;
                
        // on crée une variable pour le titre du contenu de la table
    $titre_contenu = "";
    $titre_contenu .= "-- <br />";
    $titre_contenu .= "-- Contenu de la table ` ".$table."` <br />";
    $titre_contenu .= "--  <br /> <br />";
    echo $titre_contenu;
 
        // on va récupérer le nombre de champs présents dans la table   
    $req_champ = "SHOW COLUMNS FROM $table";
    $result_champ = mysqli_query($conn_cahier_de_texte, $req_champ) or die ("Impossible de trouver les champs de ". $table .mysqli_error($conn_cahier_de_texte));
    $nbre_champ = mysqli_num_rows($result_champ);
 
        // on va rechercher TOUS les enregistrements de la table concernée 
        $req_tout = "SELECT * FROM $table "; 
        $result_tout = mysqli_query($conn_cahier_de_texte, $req_tout) or die ("Impossible de trouver les enregistrements de ". $table .mysqli_error($conn_cahier_de_texte)); 
        $contenu = "";
        $tout_contenu = "";
 
        // on va boucler pour sortir toutes les données 
        while($donnees_tout = mysqli_fetch_array($result_tout)) 
        { 
                $contenu = "INSERT INTO " . $table . " VALUES ("; 
 
                $i = 0; 
                // on va boucler tous les champs 
                while ( $i < $nbre_champ ) 
                { 
                        // Nous allons remplacer les apostrophes du contenu par 2 apostrophes
                        $donnees_tout[$i] = str_replace("'","''",$donnees_tout[$i]);
                                
                        // et on affiche les résultats en fonction des champs et dans l'ordre des champs 
                        $contenu .= "'" . $donnees_tout[$i] . "',"; 
                        $i++; 
                } 
                // on va enlever la dernière virgule
                $contenu = substr($contenu,0,-1);
                $contenu .= ");\n"; 
                echo "<br />"; 
                $tout_contenu .= $contenu; 
 
                // on affiche le contenu sur la page PHP 
                echo $contenu; 
        }
        
   
}?>
	    </p>
</td>
    </tr>
  </table>
  <?php
// on ferme sa connexion au serveur
mysqli_close($conn_cahier_de_texte);
?>

<p><br />
</p>
<p><a href="index.php">Retour au Menu Administrateur </a> </p>
</body>
</html>
