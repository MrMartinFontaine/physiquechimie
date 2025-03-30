<?php 
include "../authentification/authcheck.php";
if ($_SESSION['droits']<>2) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 

require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


$mes_erreur=0;
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

if ((!(isset($_POST['matiere_ID'])))||(isset($_POST['matiere_ID'])&&($_POST['matiere_ID']=='value2'))){ $mes_erreur=1;}
else 
{
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE ID_classe=%u",GetSQLValueString($_POST['classe_ID'], "int"));
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);


$query_RsMatiere = sprintf("SELECT * FROM cdt_matiere WHERE ID_matiere=%u",GetSQLValueString($_POST['matiere_ID'], "int"));
$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);

if (substr($_POST["classe_ID"],0,1)==0){ // c'est un regroupement
$gic_ID=substr($_POST["classe_ID"],1,strlen($_POST["classe_ID"]));$classe_ID=0; }
else
{$gic_ID=0;$classe_ID=$_POST["classe_ID"];};


$GoTo='ecrire.php?edt_modif="O"&nom_classe='.$row_RsClasse['nom_classe']
.'&classe_ID='.$classe_ID
.'&gic_ID='.$gic_ID
.'&nom_matiere='.$row_RsMatiere['nom_matiere']
.'&groupe='.$_POST['groupe']
.'&matiere_ID='.$_POST['matiere_ID']
.'&semaine='.$_GET['semaine']
.'&jour_pointe='.$_GET['jour_pointe']
.'&heure='.$_GET['heure']
.'&duree='.$_GET['duree']
.'&heure_debut='.$_GET['heure_debut']
.'&heure_fin='.$_GET['heure_fin']
.'&current_day_name='. $_POST['current_day_name']
.'&code_date='.$_POST['code_date'];


  mysqli_free_result($RsClasse);
  mysqli_free_result($RsMatiere);
 
 header(sprintf("Location: %s", $GoTo));

};
};


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = sprintf("SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe,cdt_emploi_du_temps WHERE cdt_classe.ID_classe=cdt_emploi_du_temps.classe_ID AND prof_ID=%u  ORDER BY nom_classe ASC",$_SESSION['ID_prof']);
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMatiere = sprintf("SELECT DISTINCT cdt_matiere.ID_matiere,cdt_matiere.nom_matiere,cdt_emploi_du_temps.matiere_ID FROM cdt_matiere LEFT JOIN cdt_emploi_du_temps ON cdt_matiere.ID_matiere=cdt_emploi_du_temps.matiere_ID WHERE prof_ID=%u ORDER BY matiere_ID",$_SESSION['ID_prof']);
$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
$totalRows_RsMatiere = mysqli_num_rows($RsMatiere);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE prof_ID=%u ",$_SESSION['ID_prof']);
$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgic = mysqli_fetch_assoc($Rsgic);
$totalrows_Rsgic=mysqli_num_rows($Rsgic);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Modification ponctuelle d'une plage horaire</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
var xhr = null; 
function getXhr()
{
     if(window.XMLHttpRequest)xhr = new XMLHttpRequest(); 
else if(window.ActiveXObject)
  { 
  try{
     xhr = new ActiveXObject("Msxml2.XMLHTTP");
     } catch (e) 
     {
     xhr = new ActiveXObject("Microsoft.XMLHTTP");
     }
  }
else 
  {
  alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
  xhr = false; 
  } 
}
 
function ShowRegroupement()
{
getXhr();
xhr.onreadystatechange = function()
    {
     if(xhr.readyState == 4 && xhr.status == 200)
     {
     document.getElementById('gic_ID').innerHTML=xhr.responseText;
     }
    }
//xhr.open("GET","ajax_regroupement.php",true);
//xhr.send(null);
				xhr.open("POST","ajax_regroupement.php",true);
				// ne pas oublier ça pour le post
 				xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
 				sel = document.getElementById('classe');
				classe = sel.options[sel.selectedIndex].value;
				xhr.send("Classe="+classe);
}
 


			function go(){
				getXhr();
				// On définit ce qu'on va faire quand on aura la réponse
				xhr.onreadystatechange = function(){
					// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
					if(xhr.readyState == 4 && xhr.status == 200){
						leselect = xhr.responseText;
						// On se sert de innerHTML pour rajouter les options a la liste
						document.getElementById('matiere').innerHTML = leselect;
					}
				}

				// Ici on va voir comment faire du post
				xhr.open("POST","ajax_matiere.php?matiere_ID=<?php echo $_GET['matiere_ID'];?>",true);
				// ne pas oublier ça pour le post
				xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				// ne pas oublier de poster les arguments
				// ici, l'id de l'auteur
				sel = document.getElementById('classe');
				classe = sel.options[sel.selectedIndex].value;
				xhr.send("Classe="+classe);
			}
</script>

<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<link href="../styles/info_bulles.css" rel="stylesheet" type="text/css" />
</HEAD>
<BODY onload='go()'>
<p>&nbsp;</p>
<table width="90%"  border="0" align="center" cellpadding="0" cellspacing="0" class="lire_bordure" >
  <tr class="lire_cellule_4">
    <td width="26%" class="black_police"><div align="left">
        <?php if (isset($_SESSION['identite'])){echo '<img src="../images/identite.gif" >&nbsp;'.$_SESSION['identite'];}?>
      </div></td>
    <td width="70%" class="black_police">Modification ponctuelle de l'emploi du temps</td>
    <td width="4%" ><div align="right" > <a href="enseignant.php"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0"></a><br>
      </div></td>
  </tr>
  <tr  valign="middle" class="lire_cellule_2">
    <td colspan="3" >
	
	<p>&nbsp;</p>
<p><strong>Vous souhaitez ponctuellement modifier votre emploi du temps pour cette heure de cours. <br>
  D&eacute;finissez alors ci-dessous  les nouveaux param&egrave;tres de cette s&eacute;ance.</strong><BR />
</p>
<p>&nbsp;</p>
<form method="post" name="form1" action="<?php echo $editFormAction; ?>">
  <div align="center">
    <table align="center" class="espace_enseignant">
      <tr>
        <td nowrap align="right"> <div align="left" class="Style13">
          <div align="center"><?php echo $_GET['jour_pointe'] ;?></div>
        </div></td>
      </tr>
	<tr>
        <td nowrap align="right"> <div align="left" class="Style13">
          <div align="center"><?php echo 'de '.$_GET['heure_debut'].' à '.$_GET['heure_fin'];?></div>
        </div></td>
      </tr>
      <tr>
        <td nowrap align="right">&nbsp;</td>
      </tr>
      <tr>
        <td nowrap align="right"><div align="left">
          <select name='classe_ID' id='classe' onchange='go()'>
            <option value='value'>S&eacute;lectionner une classe</option>
            <?php
			
		
						$res = mysqli_query($conn_cahier_de_texte, sprintf("SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe,cdt_emploi_du_temps WHERE cdt_classe.ID_classe=cdt_emploi_du_temps.classe_ID AND prof_ID=%u  ORDER BY nom_classe ASC",$_SESSION['ID_prof']));
						while($row = mysqli_fetch_assoc($res)){
							echo "<option value='".$row["ID_classe"]."'"; 
							    if (!(strcmp($row["nom_classe"], $_GET['nom_classe']))) {echo " SELECTED";}
							echo ">".$row["nom_classe"];
			           
							echo "</option>";
						}
						
						
						//regroupements						
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE  cdt_groupe_interclasses.prof_ID = %u",$_SESSION['ID_prof']);
$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgic = mysqli_fetch_assoc($Rsgic);

		do { 
			echo "<option value='0".$row_Rsgic['ID_gic']."'";
			if (!(strcmp($row_Rsgic['ID_gic'], $_GET['gic_ID']))) {echo " SELECTED";}
			echo "'>".$row_Rsgic['nom_gic'];
			echo "</option>";
		} while ($row_Rsgic = mysqli_fetch_assoc($Rsgic));
		
		
					?>
          </select>
        </div></td>
      </tr>


      <tr valign="baseline">
        <td align="left">&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td align="left"><div  id='matiere' style='display:inline' align="left"></div>
            <?php if ($mes_erreur ==1){echo '<br /><span style="color: #FF0000">Vous devez s&eacute;lectionner la classe puis la mati&egrave;re</span>';};?>        </td>
      </tr>
      <tr valign="baseline">
        <td>&nbsp;</td>
      </tr>
      <tr valign="baseline">
        <td><div align="left"><strong>
            <select name="groupe" id="groupe">
              <?php
do {  
?>
              <option value="<?php echo $row_Rsgroupe['groupe']?>"<?php if (!(strcmp($row_Rsgroupe['groupe'], $_GET['groupe']))) {echo "SELECTED";} ?>><?php echo $row_Rsgroupe['groupe']?></option>
              <?php
} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
  $rows = mysqli_num_rows($Rsgroupe);
  if($rows > 0) {
      mysqli_data_seek($Rsgroupe, 0);
	  $row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
  }
?>
            </select>
            </strong></div></td>
      </tr>
      <tr valign="baseline">
        <td><p align="left">&nbsp; </p>
          <p align="center"><strong>
            <input type="submit" value="Valider et saisir la s&eacute;ance">
          </strong></p>
          <p align="left">&nbsp;</p>
      </tr>
    </table>
    </fieldset>
  </div>
  <input type="hidden" name="MM_update" value="form1">
  <input type="hidden" name="current_day_name" value="<?php echo $_GET['current_day_name'];?>">
  <input type="hidden" name="code_date" value="<?php echo $_GET['code_date'];?>">
</form>
<p>&nbsp;</p>
<p><a href="ecrire.php?date=<?php echo substr($_GET['code_date'],0,8)?>">Annuler
  </a>
</p>
	</td>
  </tr>
</table>

</BODY>
</HTML>
<?php
mysqli_free_result($RsClasse);
mysqli_free_result($RsMatiere);
mysqli_free_result($Rsgroupe);
mysqli_free_result($Rsgic);
?>
