<?php 
session_start();
//if (!isset($_SESSION['consultation'])){  header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte2.php'); 
require_once('../inc/functions_inc.php');



//Consulter le cahier de texte





mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);
if (isset($_POST['classe_ID'])) 
{
$query_RsMatiere = sprintf("SELECT DISTINCT cdt_matiere.nom_matiere,cdt_agenda.matiere_ID,cdt_agenda.classe_ID FROM cdt_matiere LEFT JOIN cdt_agenda ON cdt_matiere.ID_matiere=cdt_agenda.matiere_ID WHERE cdt_agenda.classe_ID=%u",intval(strtr($_POST['classe_ID'],$protect)));
$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);
if (!isset($_POST['classe_ID'])) 
{
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
}
else
{
$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE cdt_classe.ID_classe=%u ",intval($_POST['classe_ID']));
	
}
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte2, $conn_cahier_de_texte2);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY ID_groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe, $conn_cahier_de_texte2) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="keywords" content="Cahier de textes Pierre Lemaitre">
<meta name="description" content="Cahier de textes - Aplication d&eacute;velopp&eacute;e par Pierre Lemaitre - Saint-Lô ">

<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<DIV id=header_archive>

<H1>&nbsp;</H1>
<H1>Cahier de textes </H1>
<DIV class="description"><?php echo $_SESSION['nom_etab']; ?></p><p><?php echo 'Archive '.$annee_scolaire; ?></DIV>
<p>&nbsp;</p>
</DIV>
<?
//Consulter le cahier de textes
if (!isset($_POST['classe_ID'])) 
{

?>		<p class="erreur"><?php if(isset($erreur1)){echo $erreur1;} ?></p>

    <form onLoad= "formfocus()" name="form3" method="post" action="voir_classe.php"> 
     <table width="92%" border="0" align="center" cellpadding="0" cellspacing="0" class="espace_enseignant">
<tr>
        <td width="36%"><blockquote>
          <p align="right"><span class="Style44"><br>
           
              S&eacute;lectionner la classe
           </span></p>
        </blockquote></td>
        <td width="49%"><p>
                <select name="classe_ID" id="classe_ID2">
                  <option value="value">S&eacute;lectionner la classe</option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_RsClasse['ID_classe']?>"><?php echo $row_RsClasse['nom_classe']?></option>
                  <?php
} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse));
  $rows = mysqli_num_rows($RsClasse);
  if($rows > 0) {
      mysqli_data_seek($RsClasse, 0);
	  $row_RsClasse = mysqli_fetch_assoc($RsClasse);
  }
?>
                </select>
      </td>
				<td width="15%"><br>
				<input type="submit" name="Submit3" value="Valider">
              </p>
				
</td></tr>
    </table>
    <p>      

    </p>
    </form><?
		
}
else

{
	?>


    
  <h3><?php echo $row_RsClasse['nom_classe'];?></h3>



<p>&nbsp;</p>

    <p>
      <?php if(isset($erreur2)){echo $erreur2;} ?>
    </p>
   <form name="form1" method="GET" action="voir_archive.php?classe_ID=<?php echo $_POST['classe_ID']?>">
      <table width="92%"  border="0" align="center" class="espace_enseignant" >
        <tr>
          <td width="36%"><blockquote>
              <p align="left"><span class="Style44"><br>
                Consulter le cahier de textes <br>
              </span></p>
          </blockquote></td>
          <td width="27%"> 
			<div id='matiere' style='display:inline'>

		      <div align="left">
			        <select name="matiere_ID" id="matiere_ID">
			          <option value="value2">S&eacute;lectionner la mati&egrave;re</option>
			          <?php
do {  
?>
			          <option value="<?php echo $row_RsMatiere['matiere_ID']?>"><?php echo $row_RsMatiere['nom_matiere']?></option>
			          <?php
} while ($row_RsMatiere = mysqli_fetch_assoc($RsMatiere));
  $rows = mysqli_num_rows($RsMatiere);
  if($rows > 0) {
      mysqli_data_seek($RsMatiere, 0);
	  $row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
  }
?>
	            </select>
			        
	          </div>
		    </div></td>
          <td width="36%"><input name="Submit" type="submit" value="Valider"></td>
       </tr>
    </table>
    <input name="ordre" type="hidden" value="up">
	<input name="classe_ID" type="hidden" value="<?php echo $_POST['classe_ID'];?>">
 

  </form>
<? }

?><hr>
  
  <DIV id=footer>
  <p align="right" class="auteur">&nbsp;</p>
</DIV>
</DIV></BODY></HTML>
<?php

if (isset($RsMatiere)){mysqli_free_result($RsMatiere);};
if (isset($Rsgroupe)){mysqli_free_result($Rsgroupe);};

?>
