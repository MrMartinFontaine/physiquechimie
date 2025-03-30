<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$erreur5='';



if (
(isset($_POST["MM_insert"])) 
&& ($_POST["MM_insert"] == "form1")
&& (isset($_POST['nom_prof'])) 
&& (isset($_POST['passe']))
 ) {

//tester si ce nom_prof existe deja
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsp = sprintf("SELECT nom_prof FROM cdt_prof WHERE nom_prof='%s'",$_POST['nom_prof']);
$Rsp = mysqli_query($conn_cahier_de_texte, $query_Rsp) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsp = mysqli_fetch_assoc($Rsp);
$totalRows_Rsp = mysqli_num_rows($Rsp);
mysqli_free_result($Rsp);
if ($totalRows_Rsp<>0){$erreur5="Ajout impossible ! &nbsp;<strong> ".$_POST['nom_prof']."</strong>&nbsp; est un nom d'utilisateur d&eacute;j&agrave; pr&eacute;sent dans la liste.";}

else
{

//Si le nom long (identite) de l'enseignant n'est pas renseigne, il recoit le meme que le login nom_prof
if ($_POST['identite']==NULL) {$ident=$_POST['nom_prof'];} else {$ident=$_POST['identite'];};
$password=$_POST['passe'];
$insertSQL = sprintf("INSERT INTO cdt_prof (identite,nom_prof,passe,droits,email) VALUES ( %s,%s,'%s',%s,%s)",
                       GetSQLValueString($ident, "text"),
                                           GetSQLValueString(sans_accent($_POST['nom_prof']), "text"),
                       					   password_hash($password, PASSWORD_DEFAULT),
                                           GetSQLValueString($_POST['droits'], "int"),
                                           GetSQLValueString($_POST['email'] , "text")
                        );
                
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));

        
  
  //MAX de  ID prof 
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $query_RsMax_prof = "SELECT MAX( ID_prof ) FROM cdt_prof";
  $RsMax_prof = mysqli_query($conn_cahier_de_texte, $query_RsMax_prof) or die(mysqli_error($conn_cahier_de_texte));
  $row_RsMax_prof = mysqli_fetch_assoc($RsMax_prof);
  $ind_max_prof=$row_RsMax_prof['MAX( ID_prof )'];
   mysqli_free_result($RsMax_prof);
  
  
  
  $insertSQL = sprintf("INSERT INTO cdt_type_activite (ID_prof,activite,pos_typ) VALUES ( %s,%s,%s)",
                       GetSQLValueString($ind_max_prof, "int"),
                                           GetSQLValueString('Cours', "text"),
                                           GetSQLValueString($ind_max_prof, "int")
                                           );

  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));

  
}

}

if ((isset($_POST["MM_insert2"])) && ($_POST["MM_insert2"] == "form2")||( isset($_POST["MM_insert4"])) && ($_POST["MM_insert4"] == "form4")) {
        
        if (isset($_POST["AncienProf"])) {$ancienprof = $_POST["AncienProf"];} else {$ancienprof=array();};
        for ($i=0; $i<count($ancienprof); $i++) {
        	$updateSQL = sprintf("UPDATE cdt_prof SET ancien_prof='O' WHERE ID_prof=%u",
        		GetSQLValueString($ancienprof[$i], 'int')
        		);
        	
        	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
        }
};


if ((isset($_POST["MM_insert3"])) && ($_POST["MM_insert3"] == "form3") ) {

        //if (isset($_POST["AncienProf"])) {$ancienprof = $_POST["AncienProf"];} else {$ancienprof=array();};
        if (isset($_POST["AncienOldProf"])) {$ancienprof = $_POST["AncienOldProf"];} else {$ancienprof=array();};
        $updateSQL = "UPDATE cdt_prof SET ancien_prof='N'";
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
		//$ancienprof = $_POST["AncienProf"];
		if(isset($_POST["AncienOldProf"])){
        $ancienprof = $_POST["AncienOldProf"];
        for ($i=0; $i<count($ancienprof); $i++) {
	
        	$updateSQL = sprintf("UPDATE cdt_prof SET ancien_prof='O' WHERE ID_prof=%u",
        		GetSQLValueString($ancienprof[$i], 'int')
        		);
        	//echo $updateSQL.'<br>';
        	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        	$Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));
			}
        }
};

$erreur3='';$erreur4='';

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);


if (isset($_GET['tri'])){
	if ($_GET['tri']=='nom_prof'){$query_Rs_pas_prof = "SELECT * FROM cdt_prof WHERE ancien_prof='N' AND droits<>2 ORDER BY nom_prof ASC";};
	if ($_GET['tri']=='identite'){$query_Rs_pas_prof = "SELECT * FROM cdt_prof WHERE ancien_prof='N' AND droits<>2 ORDER BY identite ASC";};
	if ($_GET['tri']=='droits'){$query_Rs_pas_prof = "SELECT * FROM cdt_prof WHERE ancien_prof='N' AND droits<>2  ORDER BY droits ASC";};
}
else { 
	$query_Rs_pas_prof = "SELECT * FROM cdt_prof WHERE ancien_prof='N' AND droits<>2 ORDER BY nom_prof ASC";
};


$Rs_pas_prof = mysqli_query($conn_cahier_de_texte, $query_Rs_pas_prof) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_pas_prof = mysqli_fetch_assoc($Rs_pas_prof);
$totalRows_Rs_pas_prof=mysqli_num_rows($Rs_pas_prof);


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if (isset($_GET['tri'])){
	if ($_GET['tri']=='nom_prof'){$query_RsProf = "SELECT * FROM cdt_prof WHERE ancien_prof='N' AND droits=2 ORDER BY nom_prof ASC";};
	if ($_GET['tri']=='identite'){$query_RsProf = "SELECT * FROM cdt_prof WHERE ancien_prof='N' AND droits=2 ORDER BY identite ASC";};
	if ($_GET['tri']=='droits'){$query_RsProf = "SELECT * FROM cdt_prof WHERE ancien_prof='N' AND droits=2  ORDER BY droits ASC";};
}
else { 
	$query_RsProf = "SELECT * FROM cdt_prof WHERE ancien_prof='N' AND droits=2 ORDER BY nom_prof ASC";
};
$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);
$totalRows_RsProf = mysqli_num_rows($RsProf);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
if (isset($_GET['tri'])){
	if ($_GET['tri']=='nom_prof'){$query_RsOldProf = "SELECT * FROM cdt_prof WHERE ancien_prof='O' ORDER BY nom_prof ASC";};
	if ($_GET['tri']=='identite'){$query_RsOldProf = "SELECT * FROM cdt_prof WHERE ancien_prof='O' ORDER BY identite ASC";};
	if ($_GET['tri']=='droits'){$query_RsOldProf = "SELECT * FROM cdt_prof WHERE ancien_prof='O' ORDER BY droits ASC";};
}
else { 
	$query_RsOldProf = "SELECT * FROM cdt_prof WHERE ancien_prof='O' ORDER BY nom_prof ASC";
};
$RsOldProf = mysqli_query($conn_cahier_de_texte, $query_RsOldProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsOldProf = mysqli_fetch_assoc($RsOldProf);
$totalRows_RsOldProf = mysqli_num_rows($RsOldProf);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProfil3 = "SELECT * FROM cdt_prof WHERE droits=3";
$RsProfil3 = mysqli_query($conn_cahier_de_texte, $query_RsProfil3) or die(mysqli_error($conn_cahier_de_texte));
$totalRows_RsProfil3= mysqli_num_rows($RsProfil3);
if ($totalRows_RsProfil3==0){$erreur3="Vous n'avez pas encore d'utilisateur avec le profil Vie Scolaire";};
mysqli_free_result($RsProfil3);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProfil4 = "SELECT * FROM cdt_prof WHERE droits=4";
$RsProfil4 = mysqli_query($conn_cahier_de_texte, $query_RsProfil4) or die(mysqli_error($conn_cahier_de_texte));
$totalRows_RsProfil4= mysqli_num_rows($RsProfil4);
if ($totalRows_RsProfil4==0){$erreur4="Vous n'avez pas encore d'utilisateur avec le profil Responsable &eacute;tablissement";};
mysqli_free_result($RsProfil4);




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
<style type="text/css">
<!--
body {
	background-color: #FFFFFF;
}
.Style145 {font-size: medium}
-->
</style>
</HEAD>
<BODY>

<?php 
$header_description="Gestion des utilisateurs";
//require_once "../templates/default/header.php";


?>
<table width="100%" border="0" align="center" cellspacing="0">
  <tr >
    <td class="Style6">
       <div align="center">CAHIER DE TEXTES - <?php echo $_SESSION['nom_etab'];?> - GESTION DES UTILISATEURS
    </div></td>
            <td class="Style6"><div align="right"><a href="index.php"><img src="../images/home-menu.gif" width="23" height="17" border="0" ></a>&nbsp;&nbsp;</div></td>
  </tr>
</table>

  <div class="erreur">
    <?php if (isset($_POST['nom_prof'])){
 echo '<p style="color:red">'.$_POST['nom_prof']." a &eacute;t&eacute; ajout&eacute; &agrave; la liste des utilisateurs.</p>";}
 echo $erreur3.'<br/>'.$erreur4.'<br/>'; if($erreur5!=''){echo $erreur5.'<br/><br/>' ;}?>
</div>
  <script language="JavaScript" type="text/JavaScript">

function formfocus() {
document.form1.nom_prof.focus()
document.form1.nom_prof.select()
}
</script>
<div align="center">
<fieldset style="width : 90%">
<legend align="top" class="Style13 Style145">Ajout d'un utilisateur</legend>
 <p align="center">Le champ NOM Pr&eacute;nom ou identit&eacute; est le libell&eacute; affich&eacute; sur la fiche &eacute;l&egrave;ve et parents (Ex : Bruno Carla). <br>
   Eviter de mattre Mr ou Mme devant le nom, le tri se faisant sur la premi&egrave;re lettre dans les menus d&eacute;roulants. <br>
   L'identifiant est un libell&eacute; court et sans accents ni espaces utilis&eacute; par l'enseignant pour se connecter (Ex : c_bruno) <br>
   Si le nom n'est pas renseign&eacute;, il se verra attribuer l'identifiant. </p>
 <form onLoad= "formfocus()" method="post"  name="form1" action="<?php echo $editFormAction; ?>" onSubmit="return submit_modifpass2();" >
  
    <table width="60%" align="center">
      <tr valign="baseline">
        <td><table width="100%"  border="0" align="center" cellpadding="5" cellspacing="5" class="tab_detail_gris">
            <tr>
              <td><div align="right"><strong>NOM Pr&eacute;nom </strong></div>
                </th>
              <td><input name="identite" type="text" id="identite" value="" size="32"></td>
            </tr>
            <tr>
              <td><div align="right"><strong>IDENTIFIANT (login - pas d'accents)</strong></div>
                </th>
              <td><input type="text" name="nom_prof" value="" size="32"></td>
            </tr>
            <tr>
              <td><div align="right"><strong>Mot de passe </strong></div>
                </th>
              <td><input type="password" name="passe" id="passe" value="" size="32"></td>
            </tr>
            <tr>
              <td><div align="right"><strong>Confirmer le mot de passe </strong></div>
                </th>
              <td ><input type="password" name="passe2" id="passe2" value="" size="32"></td>
            </tr>
            <tr >
              <td><div align="right"><strong>Adresse m&eacute;l (facultatif) </strong></div></td>
              <td><input name="email" id="email" size="32" value=""></td>
            </tr>
            <tr>
              <th scope="row"><div align="right"><strong>PROFIL - DROITS </strong></div></th>
              <td><div align="left">
                  <select name="droits" id="droits">
                    <option value="0">Interdire temporairement l'acc&egrave;s </option>
                    <option value="1">Administrateur</option>
                    <option value="2" selected>Enseignant</option>
                    <option value="3">Vie Scolaire</option>
                    <option value="4">Resp. Etablissement</option>
                    <option value="5">Invit&eacute;</option>
                    <option value="6">Assistant d'&eacute;ducation</option>
                    <option value="7">P&eacute;riscolaire (Infirmi&egrave;re...)</option>
                    <option value="8">Documentaliste</option>                                               
                  </select>
                </div></td>
            </tr>
            <tr>
              <td></th>
              <td><br>
                <input name="submit" type="submit" value="Ajouter cet utilisateur"></td>
            </tr>
          </table></td>
      </tr>
    </table>
    <p>&nbsp; </p>
    <p>
      <input type="hidden" name="MM_insert" value="form1">
    </p>
  </form>
  </fieldset>
</div>
  <p><a href="import_csv.php">Importation des utilisateurs depuis un fichier CSV ou txt</a> </p>
  <p> <a href="import_sconet.php">Importation des utilisateurs depuis SCONET /STSWeb</a> </p>
  <p><a href="index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp;</p>

  <script> formfocus(); </script>
<div align="center">

<!--    Affichage du personnel qui n'est pas prof -->


<fieldset style="width : 90%">
<legend align="top" class="Style13 Style145">Membres du personnel de gestion de l'ann&eacute;e en cours</legend>
    <p align="left">&nbsp;</p>
<form method="POST"  name="form2" enctype="multipart/form-data" action="prof_ajout.php">
<?php if ($totalRows_Rs_pas_prof>0) { ?>
<table border="0" align="center">
<tr>
<td class="Style6"><div align="center">R&eacute;f&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center" onClick="MM_goToURL('window','prof_ajout.php?tri=identite');return document.MM_returnValue">NOM&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center" onClick="MM_goToURL('window','prof_ajout.php?tri=nom_prof');return document.MM_returnValue">Identifiant&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center" onClick="MM_goToURL('window','prof_ajout.php?tri=droits');return document.MM_returnValue">Profil&nbsp;&nbsp;</div></td>
      <td class="Style6">M&egrave;l</td>
      <td class="Style6"><div align="center">Editer&nbsp;</div></td>
<td class="Style6"><div align="center">Ancien prof.&nbsp;&nbsp;</div></td>
      <td class="Style6"><div align="center">Suppr.</div></td>
      <!-- Activer pour autoriser une connexion directe
          <td class="Style6">Conn.</td>
          -->
</tr>
<?php do { 
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsAncienProf = sprintf("SELECT prof_ID FROM cdt_agenda WHERE prof_ID=%u",
		GetSQLValueString($row_Rs_pas_prof['ID_prof'], "int"));
	$RsAncienProf = mysqli_query($conn_cahier_de_texte, $query_RsAncienProf) or die(mysqli_error($conn_cahier_de_texte));
	$totalRows_RsAncienProf = mysqli_num_rows($RsAncienProf);
	mysqli_free_result($RsAncienProf);
	?>
	<tr>
        <td class="tab_detail_gris"><div align="right"><?php echo $row_Rs_pas_prof['ID_prof']; ?></div></td>
        <td class="tab_detail_gris"><div align="left" 
        <?php 
        if($row_Rs_pas_prof['droits']==1){echo ' style="color:#FF0000; font-size:12px;"';}
        else if($row_Rs_pas_prof['droits']==0){echo ' style="color:#FE9001; font-size:12px; font-weight: bold;"';}
        else if($row_Rs_pas_prof['droits']==3){echo ' style="color:#339933; font-size:12px; font-weight: bold;"';}
        else if($row_Rs_pas_prof['droits']==4){echo ' style="color:#FF0000; font-size:12px;"';}
        else if($row_Rs_pas_prof['droits']==5){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_Rs_pas_prof['droits']==6){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_Rs_pas_prof['droits']==7){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_Rs_pas_prof['droits']==8){echo ' style="color:#CC00CC; font-size:12px;"';}                      
        else if ($totalRows_RsAncienProf==0){echo ' style="color:#000000; font-size:12px;"';};    
        ?>><?php echo $row_Rs_pas_prof['identite']; ?></div></td>
        <td class="tab_detail_gris"><div align="left"  
        <?php 
        if($row_Rs_pas_prof['droits']==1){echo ' style="color:#FF0000; font-size:12px;"';}
        else if($row_Rs_pas_prof['droits']==0){echo ' style="color:#FE9001; font-size:12px; font-weight: bold;"';}
        else if($row_Rs_pas_prof['droits']==3){echo ' style="color:#339933; font-size:12px; font-weight: bold;"';}
        else if($row_Rs_pas_prof['droits']==4){echo ' style="color:#FF0000; font-size:12px;"';}
        else if($row_Rs_pas_prof['droits']==5){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_Rs_pas_prof['droits']==6){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_Rs_pas_prof['droits']==7){echo ' style="color:#CC00CC; font-size:12px;"';}      
        else if($row_Rs_pas_prof['droits']==8){echo ' style="color:#CC00CC; font-size:12px;"';}              
        else if ($totalRows_RsAncienProf==0){echo ' style="color:#000000; font-size:12px;"';};    
        ?>><?php  echo $row_Rs_pas_prof['nom_prof']; ?></div></td>
        <td class="tab_detail_gris"><div align="left"  
        <?php 
        if($row_Rs_pas_prof['droits']==1){echo ' style="color:#FF0000; font-size:12px;"';}
        else if($row_Rs_pas_prof['droits']==0){echo ' style="color:#FE9001; font-size:12px; font-weight: bold;"';}
        else if($row_Rs_pas_prof['droits']==3){echo ' style="color:#339933; font-size:12px; font-weight: bold;"';}
        else if($row_Rs_pas_prof['droits']==4){echo ' style="color:#FF0000; font-size:12px;"';}
        else if($row_Rs_pas_prof['droits']==5){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_Rs_pas_prof['droits']==6){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_Rs_pas_prof['droits']==7){echo ' style="color:#CC00CC; font-size:12px;"';}      
        else if($row_Rs_pas_prof['droits']==8){echo ' style="color:#CC00CC; font-size:12px;"';}                              
        else if ($totalRows_RsAncienProf==0){echo ' style="color:#000000; font-size:12px;"';};    
        ?>><?php  
        if ($row_Rs_pas_prof['droits']==0){$profil='Acc&egrave;s interdit';}
        else if ($row_Rs_pas_prof['droits']==1){$profil='Administrateur';}
        else if ($row_Rs_pas_prof['droits']==2)
        {$profil='Enseignant';
                if ($row_Rs_pas_prof['id_etat']==2)
        $profil='Rempla&ccedil;ant';}
else if ($row_Rs_pas_prof['droits']==3){$profil='Vie scolaire';}
else if ($row_Rs_pas_prof['droits']==4){$profil='Resp. Etablissement';}
else if ($row_Rs_pas_prof['droits']==5){$profil='Invit&eacute;';}    
else if ($row_Rs_pas_prof['droits']==6){$profil='Assistant Education';}
else if ($row_Rs_pas_prof['droits']==7){$profil='P&eacute;riscolaire (Infirmi&egrave;re...)';}
else if ($row_Rs_pas_prof['droits']==8){$profil='Documentaliste';}           
;  
echo $profil.'&nbsp;' ?>
</div></td>
        <td class="tab_detail_gris"><?php  echo $row_Rs_pas_prof['email']; ?></td>
        <td class="tab_detail_gris"><div align="center"><img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','prof_modif.php?ID_prof=<?php echo $row_Rs_pas_prof['ID_prof']; ?>');return document.MM_returnValue"></div></td>
<td class="tab_detail_gris"><div align="center">&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="AncienProf[]" title="Devenir un ancien membre du personnel" value="<?php echo $row_Rs_pas_prof['ID_prof']; ?>" <?php echo $row_Rs_pas_prof['ancien_prof']=='O'?'checked':''; ?> />&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
<td class="tab_detail_gris"><div align="center">
          <?php if($row_Rs_pas_prof['ID_prof']<>1){ ?>
          <img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick="MM_goToURL('window','prof_supprime.php?ID_prof=<?php echo $row_Rs_pas_prof['ID_prof']; ?>');return document.MM_returnValue">
          <?php } else {echo '&nbsp;';}?>
        </div></td>
                <!-- Activer les lignes ci-dessous pour une connexion directe
        <td class="tab_detail_gris">
                <?php 
                // if($row_Rs_pas_prof['droits']>1){?>
                  <div align="center">
          <form name="form2" method="post" action="../authentification/auth.php" target="_blank">
                  <input name="nom_prof" type="hidden" value="<?php //echo $row_Rs_pas_prof['nom_prof'];?>">
                  <input name="md5" type="hidden" value="<?php //echo $row_Rs_pas_prof['passe'];?>">
                  <input type="image" src="../images/cle.gif" width="18" height="18" alt="Se connecter &agrave; son interface" title="Se connecter &agrave; son interface" name="Se connecter &agrave; son interface" />
          </form>
          </div>
                  <?php 
                  //};
                  ?>
                 </td>
                 -->
</tr>
<?php } while ($row_Rs_pas_prof = mysqli_fetch_assoc($Rs_pas_prof));?>
</table>
<input type="hidden" name="MM_insert2" value="form2">
<p>
<input name="submit" type="submit" value="Enregistrer vos modifications">
</p>
</form>

<?php } else { ?>
<blockquote>
<p>
<div align="center">Aucun membre de la liste des utilisateurs n'est encore un ancien membre du personnel.</div>
</blockquote>
<?php }; ?>
</fieldset>
<br><br>


<!--    Affichage du personnel qui est prof -->

<fieldset style="width : 90%">
<legend align="top" class="Style13 Style145">Enseignants de l'ann&eacute;e en cours</legend>
    <div align="center">En noir, apparaissent les enseignants qui n'ont encore rien saisi dans le cahier de textes actuel.</div>
    <p align="left">&nbsp;</p>
<form method="POST"  name="form4" enctype="multipart/form-data" action="prof_ajout.php">
<?php if ($totalRows_RsProf>0) { ?>
<table border="0" align="center">
<tr>
<td class="Style6"><div align="center">R&eacute;f&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center" onClick="MM_goToURL('window','prof_ajout.php?tri=identite');return document.MM_returnValue">NOM&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center" onClick="MM_goToURL('window','prof_ajout.php?tri=nom_prof');return document.MM_returnValue">Identifiant&nbsp;&nbsp;</div></td>
<td class="Style6">M&egrave;l </td>
<td class="Style6"><div align="center">Editer&nbsp;</div></td>
<td class="Style6"><div align="center">Ancien prof.&nbsp;&nbsp;</div></td>
      <td class="Style6"><div align="center">EDT &nbsp;&nbsp;</div></td>
      <td class="Style6"><div align="center">Suppr.</div></td>
      <!-- Activer pour autoriser une connexion directe
          <td class="Style6">Conn.</td>
          -->
</tr>
<?php do { 
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsAncienProf = sprintf("SELECT prof_ID FROM cdt_agenda WHERE prof_ID=%u",
		GetSQLValueString($row_RsProf['ID_prof'], "int"));
	$RsAncienProf = mysqli_query($conn_cahier_de_texte, $query_RsAncienProf) or die(mysqli_error($conn_cahier_de_texte));
	$totalRows_RsAncienProf = mysqli_num_rows($RsAncienProf);
	mysqli_free_result($RsAncienProf);
	?>
	<tr>
        <td class="tab_detail_gris"><div align="right"<?php
	       if ($totalRows_RsAncienProf==0){echo ' style="color:#000000; font-size:12px;"';} else {echo 'style="color:#0000CC; font-weight: bold;"';}  ;
	?>><?php 
		 echo $row_RsProf['ID_prof']; ?></div></td>
        <td class="tab_detail_gris"><div align="left" 
        <?php 
       if ($totalRows_RsAncienProf==0){echo ' style="color:#000000; font-size:12px;"';} else {echo 'style="color:#0000CC; font-weight: bold;"';}  ;
        ?>><?php echo $row_RsProf['identite']; ?></div></td>
        <td class="tab_detail_gris"><div align="left"  
        <?php 
       if ($totalRows_RsAncienProf==0){echo ' style="color:#000000; font-size:12px;"';} else {echo 'style="color:#0000CC; font-weight: bold;"';};
        ?>><?php  echo $row_RsProf['nom_prof']; ?></div></td>
        <td class="tab_detail_gris"><?php  echo $row_RsProf['email']; ?></td>
        <td class="tab_detail_gris"><div align="center"><img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','prof_modif.php?ID_prof=<?php echo $row_RsProf['ID_prof']; ?>');return document.MM_returnValue"></div></td>
<td class="tab_detail_gris"><div align="center">&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="AncienProf[]" title="Devenir un ancien membre du personnel" value="<?php echo $row_RsProf['ID_prof']; ?>" <?php echo $row_RsProf['ancien_prof']=='O'?'checked':''; ?> />&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
<td class="tab_detail_gris"><div align="center">
<?php if ($row_RsProf['droits']==2){?>  
          <img src="../images/planning_prof.gif" alt="Modifier l'emploi du temps" title="Modifier l'emploi du temps" width="20" height="18" onClick="MM_goToURL('window','../enseignant/emploi.php?ID_prof=<?php echo $row_RsProf['ID_prof']."&affiche=1"; ?>');return document.MM_returnValue">
<?php };?>
</div></td>
<td class="tab_detail_gris"><div align="center">
          <?php if($row_RsProf['ID_prof']<>1){ ?>
          <img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick="MM_goToURL('window','prof_supprime.php?ID_prof=<?php echo $row_RsProf['ID_prof']; ?>');return document.MM_returnValue">
          <?php } else {echo '&nbsp;';}?>
        </div></td>
                <!-- Activer les lignes ci-dessous pour une connexion directe
        <td class="tab_detail_gris">
                <?php 
                // if($row_RsProf['droits']>1){?>
                  <div align="center">
          <form name="form2" method="post" action="../authentification/auth.php" target="_blank">
                  <input name="nom_prof" type="hidden" value="<?php //echo $row_RsProf['nom_prof'];?>">
                  <input name="md5" type="hidden" value="<?php //echo $row_RsProf['passe'];?>">
                  <input type="image" src="../images/cle.gif" width="18" height="18" alt="Se connecter &agrave; son interface" title="Se connecter &agrave; son interface" name="Se connecter &agrave; son interface" />
          </form>
          </div>
                  <?php 
                  //};
                  ?>
                 </td>
                 -->
</tr>
<?php } while ($row_RsProf = mysqli_fetch_assoc($RsProf));?>
</table>
<input type="hidden" name="MM_insert4" value="form4">
<p>
<input name="submit" type="submit" value="Enregistrer vos modifications">
</p>
</form>
<blockquote>
<p>
</blockquote>
<?php } else { ?>
<blockquote>
<p>
<div align="center">Aucun membre de la liste des utilisateurs n'est encore un ancien membre du personnel.</div>
</blockquote>
<?php }; ?>
</fieldset>





<br><br>
<fieldset style="width : 90%">
<legend align="top" class="Style13 Style145">Anciens membres du personnel</legend>
<br><br>
<?php if ($totalRows_RsOldProf>0) { ?>
<form method="POST"  name="form3" enctype="multipart/form-data" action="prof_ajout.php">

<table border="0" align="center">
<tr>
<td class="Style6"><div align="center">R&eacute;f&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center" onClick="MM_goToURL('window','prof_ajout.php?tri=identite');return document.MM_returnValue">NOM&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center" onClick="MM_goToURL('window','prof_ajout.php?tri=nom_prof');return document.MM_returnValue">Identifiant&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center" onClick="MM_goToURL('window','prof_ajout.php?tri=droits');return document.MM_returnValue">Profil&nbsp;&nbsp;</div></td>
<td class="Style6">M&egrave;l</td>
<td class="Style6"><div align="center">Editer&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center">Ancien prof&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center">Supprimer&nbsp;&nbsp;</div></td>
</tr>
<?php 
    do { 
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsAncienProf = sprintf("SELECT prof_ID FROM cdt_agenda WHERE prof_ID=%u",
		GetSQLValueString($row_RsOldProf['ID_prof'], "int"));
	$RsAncienProf = mysqli_query($conn_cahier_de_texte, $query_RsAncienProf) or die(mysqli_error($conn_cahier_de_texte));
	$totalRows_RsAncienProf = mysqli_num_rows($RsAncienProf);
	mysqli_free_result($RsAncienProf);
	?>
	<tr>
        <td class="tab_detail_gris"><div align="right"><?php echo $row_RsOldProf['ID_prof']; ?></div></td>
        <td class="tab_detail_gris"><div align="left" 
        <?php 
        if($row_RsOldProf['droits']==1){echo ' style="color:#FF0000; font-size:12px;"';}
        else if($row_RsOldProf['droits']==0){echo ' style="color:#FE9001; font-size:12px; font-weight: bold;"';}
        else if($row_RsOldProf['droits']==3){echo ' style="color:#339933; font-size:12px; font-weight: bold;"';}
        else if($row_RsOldProf['droits']==4){echo ' style="color:#FF0000; font-size:12px;"';}
        else if($row_RsOldProf['droits']==5){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_RsOldProf['droits']==6){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_RsOldProf['droits']==7){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_RsOldProf['droits']==8){echo ' style="color:#CC00CC; font-size:12px;"';}                      
        else if ($totalRows_RsAncienProf==0){echo ' style="color:#000000; font-size:12px;"';};    
        ?>><?php echo $row_RsOldProf['identite']; ?></div></td>
        <td class="tab_detail_gris"><div align="left"  
        <?php 
        if($row_RsOldProf['droits']==1){echo ' style="color:#FF0000; font-size:12px;"';}
        else if($row_RsOldProf['droits']==0){echo ' style="color:#FE9001; font-size:12px; font-weight: bold;"';}
        else if($row_RsOldProf['droits']==3){echo ' style="color:#339933; font-size:12px; font-weight: bold;"';}
        else if($row_RsOldProf['droits']==4){echo ' style="color:#FF0000; font-size:12px;"';}
        else if($row_RsOldProf['droits']==5){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_RsOldProf['droits']==6){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_RsOldProf['droits']==7){echo ' style="color:#CC00CC; font-size:12px;"';}      
        else if($row_RsOldProf['droits']==8){echo ' style="color:#CC00CC; font-size:12px;"';}              
        else if ($totalRows_RsAncienProf==0){echo ' style="color:#000000; font-size:12px;"';};    
        ?>><?php  echo $row_RsOldProf['nom_prof']; ?></div></td>
        <td class="tab_detail_gris"><div align="left"  
        <?php 
        if($row_RsOldProf['droits']==1){echo ' style="color:#FF0000; font-size:12px;"';}
        else if($row_RsOldProf['droits']==0){echo ' style="color:#FE9001; font-size:12px; font-weight: bold;"';}
        else if($row_RsOldProf['droits']==3){echo ' style="color:#339933; font-size:12px; font-weight: bold;"';}
        else if($row_RsOldProf['droits']==4){echo ' style="color:#FF0000; font-size:12px;"';}
        else if($row_RsOldProf['droits']==5){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_RsOldProf['droits']==6){echo ' style="color:#CC00CC; font-size:12px;"';}
        else if($row_RsOldProf['droits']==7){echo ' style="color:#CC00CC; font-size:12px;"';}      
        else if($row_RsOldProf['droits']==8){echo ' style="color:#CC00CC; font-size:12px;"';}                              
        else if ($totalRows_RsAncienProf==0){echo ' style="color:#000000; font-size:12px;"';};    
        ?>><?php  
        if ($row_RsOldProf['droits']==0){$profil='Acc&egrave;s interdit';}
        else if ($row_RsOldProf['droits']==1){$profil='Administrateur';}
        else if ($row_RsOldProf['droits']==2)
        {$profil='Enseignant';
                if ($row_RsOldProf['id_etat']==2)
        $profil='Rempla&ccedil;ant';}
else if ($row_RsOldProf['droits']==3){$profil='Vie scolaire';}
else if ($row_RsOldProf['droits']==4){$profil='Resp. Etablissement';}
else if ($row_RsOldProf['droits']==5){$profil='Invit&eacute;';}    
else if ($row_RsOldProf['droits']==6){$profil='Assistant Education';}
else if ($row_RsOldProf['droits']==7){$profil='P&eacute;riscolaire (Infirmi&egrave;re...)';}
else if ($row_RsOldProf['droits']==8){$profil='Documentaliste';}           
;  
echo $profil.'&nbsp;' ?>
</div></td>
        <td class="tab_detail_gris"><?php  echo $row_RsOldProf['email']; ?></td>
        <td class="tab_detail_gris"><div align="center">&nbsp;&nbsp;&nbsp;&nbsp;<img src="../images/button_edit.png" alt="Modifier" title="Modifier" width="12" height="13" onClick="MM_goToURL('window','prof_modif.php?ID_prof=<?php echo $row_RsOldProf['ID_prof']; ?>');return document.MM_returnValue">&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
<td class="tab_detail_gris"><div align="center">&nbsp;&nbsp;&nbsp;&nbsp;
<?php if ($row_RsOldProf['droits']!=1){?>
	<input type="checkbox" name="AncienOldProf[]" title="Devenir un membre du personnel de l'ann&eacute;e en cours" value="<?php echo $row_RsOldProf['ID_prof']; ?>" <?php echo $row_RsOldProf['ancien_prof']=='O'?'checked':''; ?> />
<?php };?>
&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
<td class="tab_detail_gris"><div align="center">
<?php if ($row_RsOldProf['droits']==2){?>  
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php if($row_RsOldProf['nom_prof']<>"admin"){ ?><img src="../images/ed_delete.gif" alt="Supprimer" title="Supprimer" width="11" height="13" onClick="MM_goToURL('window','prof_supprime.php?ID_prof=<?php echo $row_RsOldProf['ID_prof']; ?>');return document.MM_returnValue"> <?php } else {echo '&nbsp;';};?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
<?php };?>
</tr>
<?php } while ($row_RsOldProf = mysqli_fetch_assoc($RsOldProf)); ?>
</table>

<input type="hidden" name="MM_insert3" value="form3">
<p>
<input name="submit" type="submit" value="Enregistrer vos modifications">
</p>
</form>
<?php } else { ?>
<blockquote>
<p>
<div align="center">Aucun membre de la liste des utilisateurs n'est encore un ancien membre du personnel.</div>
</blockquote>
<?php }; ?>
</fieldset>
<br><br>
</div>
<p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
<p>&nbsp; </p>

</body>
</html>
<?php
mysqli_free_result($RsProf);
mysqli_free_result($RsOldProf);
mysqli_free_result($Rs_pas_prof);
?>
