<?php include "../authentification/authcheck.php"; ?>
<?php if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};?>
<?php require_once('../Connections/conn_cahier_de_texte.php'); ?>
<?php

if (isset($_POST['misajour']) && ($_POST['misajour']=='go')){

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) OR die('Erreur de connexion &agrave; la base de donn&eacute;es. Recommencez l\'installation et v&eacute;rifiez bien vos param&egrave;tres. ');

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Recordset1 = "SELECT * FROM cdt_prof";
$Recordset1 = mysqli_query($conn_cahier_de_texte, $query_Recordset1) or die(mysqli_error($conn_cahier_de_texte));
$row_Recordset1 = mysqli_fetch_assoc($Recordset1);
$totalRows_Recordset1 = mysqli_num_rows($Recordset1);
        
		 do { 
		 
		 if ((substr(($row_Recordset1['passe']), 0, 1) <> "$")&&(strlen($row_Recordset1['passe'])<20))
		 {
		 $passe_code=password_hash($row_Recordset1['passe']); 
		 $updateSQL = sprintf("UPDATE cdt_prof SET  passe='%s' WHERE ID_prof=%u",
                       $passe_code, $row_Recordset1['ID_prof']
		 );
       $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
	           };
          } while ($row_Recordset1 = mysqli_fetch_assoc($Recordset1)); 
		  mysqli_free_result($Recordset1);

		

echo '<p align="center" style="color: #AA0000;	font-size: x-large;">Mise &agrave; jour de la table cdt_prof effectu&eacute;e </p><p align="center" style="color: #AA0000;	font-size: x-large;">SUPPRIMEZ maintenant ce fichier cryptage.php de votre application par s&eacute;curit&eacute;</p>';
}
?> 

<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet></head>


<body>
<DIV id=page> 
<?php 
$header_description="Cryptage des mots de passe dans le cas d'un import";
require_once "../templates/default/header.php";
?>

  <div align="center"><blockquote>
      <p align="left">&nbsp;</p>
      <p align="left">La version PHP 5 introduit la possibilit&eacute; d'un nouvel algorithme bcrypt de cryptage plus s&eacute;curis&eacute;. </p>
      <p align="left">Si vous avez import&eacute; vos profs depuis un fichier 
        texte &agrave; l'aide de PhpMyadmin, les mots de passe sont en clair et 
      ne sont pas crypt&eacute;s.</p>
      <p align="left">L'ex&eacute;cution de cryptage2.php va permettre d'encoder 
        ces mots de passe en clair pr&eacute;sent dans la table cdt_prof.</p>
      <p align="left">Il existait cependant d&eacute;j&agrave; des mots de passe 
        comme celui de l'Administrateur qui ne doivent pas &ecirc;tre r&eacute;-encrypt&eacute;s.</p>
      <p align="left">Comment va se faire la discrimination ? Ce programme va 
        consid&eacute;rer lors de l'ex&eacute;cution que les mots de passe dont 
        la chaine est inf&eacute;reure &agrave; 21 caract&egrave;res ne sont pas 
        crypt&eacute;s. </p>
      <p align="left">Si tout cela vous para&icirc;t obscur, <strong>faire un 
        sauvegarde de la table cdt_prof ou mieux de la base de donn&eacute;es 
        et de l'ensemble du dossier cahier de textes. </strong>En cas de probl&egrave;me, 
        il vous sera ainsi possible de r&eacute;initialiser avec la table cdt_prof 
        sauvegard&eacute;e avec un outil tel que PhpMyadmin et de relancer ce 
        fichier cryptage.php. </p>
      <p align="left">Apr&egrave;s cette op&eacute;ration, et le contr&ocirc;le du bon 
        d&eacute;roulement du cryptage, <strong>SUPPRIMER ou renommer ce fichier cryptage.php</strong> par mesure de s&eacute;curit&eacute;</p>
      <form name="form1" method="post" action="cryptage.php">
        <input name="misajour" type="hidden" id="misajour" value="go">
        <input type="submit" name="Submit" value="Lancer le cryptage des mots de passe de la table cdt_prof">
      </form>
      <p><a href="index.php">Annuler</a></p>
  </blockquote>
  </div>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>


