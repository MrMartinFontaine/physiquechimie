<?php
include "../../authentification/authcheck.php" ;
if (($_SESSION['droits']<>1)&&($_SESSION['droits']<>3)) { header("Location: ../../index.php");exit;};

require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
        $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$message='';

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") ) {


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$req = "SELECT min(ID_ele) AS min, max(ID_ele) AS max FROM ele_liste;"; 
$res = mysqli_query($conn_cahier_de_texte, $req) or die(mysqli_error($conn_cahier_de_texte)); 
$row = mysqli_fetch_assoc($res); 
$nblign=(int) $row['max'];

if(isset($_POST['ele_ID']) && !empty($_POST['ele_ID'])){
	$Col1_Array = $_POST['ele_ID'];
	
        foreach($Col1_Array as $selectValue){
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  			$updateSQL2 = sprintf("UPDATE ele_liste SET groupe_ID_ele=%u,groupe_ele=%s WHERE ID_ele=%u",
                       GetSQLValueString($_POST['groupe_ID_ele'], 'int'),
					   GetSQLValueString($_POST['groupe_ele'], 'text'),
					   GetSQLValueString($selectValue, 'int')
					   );
			$Result2 = mysqli_query($conn_cahier_de_texte, $updateSQL2) or die(mysqli_error($conn_cahier_de_texte));
                                
                }
}
$message='<p style="color:#FF0000" align="center">Vos modifications relatives aux affectations du groupe <strong>'.$_POST['groupe_ele'].'</strong> ont &eacute;t&eacute; prises en compte.</p>';
}; //fin affectation

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe WHERE ID_groupe >1 ORDER BY ID_groupe ASC"; //1 = classe entiere
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe_defini = "SELECT DISTINCT classe_ele, groupe_ele, groupe_ID_ele FROM ele_liste WHERE groupe_ID_ele <>1 ORDER BY classe_ele,groupe_ele";
$Rsgroupe_defini = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe_defini) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe_defini = mysqli_fetch_assoc($Rsgroupe_defini);
$totalRows_Rsgroupe_defini = mysqli_num_rows($Rsgroupe_defini);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../styles/style_default.css" rel="stylesheet" type="text/css">
<style>
form{
	margin:5;
	padding:0;
}
</style>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}

//-->
</script>
</head>
<body >
<table class="lire_bordure" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr >
    <td class="Style6" >Module Absences - Affectation des &eacute;l&egrave;ves dans les groupes</td>
    <td class="Style6" ><div align="right"><a href="index.php"><img src="../../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></div></td>
  </tr>
</table>
<table width="90%" border="0">
  <tr>
    <td valign="top"><p>&nbsp;</p>
     
      <?php

    if ( $totalRows_Rsgroupe_defini <>0){
	echo 'Affectations d&eacute;ja r&eacute;alis&eacute;es.'?>
	<p>&nbsp;</p>
      <table border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
        <tr>
          <td class="Style6">Classe&nbsp; </td>
          <td class="Style6">Groupe&nbsp;</td>
          <td class="Style6">Editer&nbsp; </td>
        </tr>
        <?php do { ?>
          <tr>
            <td class="tab_detail"><?php echo $row_Rsgroupe_defini['classe_ele']; ?></td>
            <td class="tab_detail"><?php echo $row_Rsgroupe_defini['groupe_ele']; ?></td>
            <td class="tab_detail"><div align="center"><img src="../../images/button_edit.png" alt="Editer" title="Editer" width="12" height="13" onClick="MM_goToURL('window','ele_affectation_groupe.php?classe_ele=<?php echo $row_Rsgroupe_defini['classe_ele']; ?>&amp;groupe_ID_ele=<?php echo $row_Rsgroupe_defini['groupe_ID_ele']; ?>&modif');return document.MM_returnValue"></div></td>
          </tr>
          <?php } while ($row_Rsgroupe_defini = mysqli_fetch_assoc($Rsgroupe_defini)); 
       } else { echo 'Aucune affectation encore r&eacute;alis&eacute;e.';};       
		  ?>
      </table>
    </td>
    <td>
        <p></p>
        <?php 
        if ($message!=''){echo $message.'<br />';};
        
        if (!isset($_GET['modif'])){
        ?>
        <form action="<?php echo $editFormAction; ?>" method="POST">
        <select name="classe_ID" id="classe_ID" >
          <option value="-1" selected>S&eacute;lectionnez la classe</option>
          <?php
$res = mysqli_query($conn_cahier_de_texte, "SELECT DISTINCT nom_classe,ID_classe FROM cdt_classe ORDER BY nom_classe ASC");

while($row = mysqli_fetch_assoc($res)){
	echo "<option value='".$row["ID_classe"]."'";
	if ((isset($_POST['classe_ID']))&&($row["ID_classe"]==$_POST['classe_ID'])){echo 'selected=" selected"';};
	
	echo "> ".$row["nom_classe"];
	echo "</option>";
	
};?>
        </select>
        <select name="groupe_ID" id="groupe_ID">
          <option value="-1" selected>S&eacute;lectionnez le groupe</option>
          <?php

do {  
?>
          <option value="<?php echo $row_Rsgroupe['ID_groupe']?>"<?php 
	if ((isset($_POST['groupe_ID'])) AND ($_POST['groupe_ID']==$row_Rsgroupe['ID_groupe'] )) {echo 'selected';} ;?>><?php echo  $row_Rsgroupe['groupe']?></option>
          <?php

} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
$rows = mysqli_num_rows($Rsgroupe);
if($rows > 0) {
	mysqli_data_seek($Rsgroupe, 0);
	$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
}
?>
        </select>
        <input name="submit" type="submit" value="S&eacute;lectionner">
      </form>
      <p>&nbsp;</p>
      <br/>
      <?php
}


// a optimiser
if (((isset($_POST['classe_ID']))&&(isset($_POST['groupe_ID'])))||((isset($_GET['classe_ele']))&&(isset($_GET['groupe_ID_ele'])))){

if (((isset($_POST['classe_ID']))&&($_POST['classe_ID']==-1))||((isset($_POST['groupe_ID']))&&($_POST['groupe_ID']==-1))){
echo "<p class='erreur'> Vous devez s&eacute;lectionner la classe et un groupe </p>";
}
else
{
if (isset($_POST['classe_ID'])){$cl= $_POST['classe_ID'];};

if (isset($_GET['classe_ele'])){

	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsClasse3 = "SELECT * FROM cdt_classe WHERE code_classe='". $_GET['classe_ele']."'";
	$RsClasse3 = mysqli_query($conn_cahier_de_texte, $query_RsClasse3) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsClasse3 = mysqli_fetch_assoc($RsClasse3);
	$cl= $row_RsClasse3['ID_classe'];
};


if (isset($_POST['groupe_ID'])){$gr_id=$_POST['groupe_ID'];};
if (isset($_GET['groupe_ID_ele'])){$gr_id=$_GET['groupe_ID_ele'];};

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse2 = "SELECT * FROM cdt_classe WHERE ID_classe=". $cl;
$RsClasse2 = mysqli_query($conn_cahier_de_texte, $query_RsClasse2) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse2 = mysqli_fetch_assoc($RsClasse2);
$totalRows_RsClasse2 = mysqli_num_rows($RsClasse2); 

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe WHERE ID_groupe=". $gr_id;
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsEle = "SELECT * FROM `ele_liste` where `classe_ele` = '". $row_RsClasse2['code_classe']."' ORDER BY nom_ele,prenom_ele;";
$RsEle = mysqli_query($conn_cahier_de_texte, $query_RsEle) or die(mysqli_error($conn_cahier_de_texte));
$row_RsEle = mysqli_fetch_assoc($RsEle);
$totalRows_RsEle = mysqli_num_rows($RsEle);



  // On recupere les eleves selectionnes
  $selected_eles = array();
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $query_Rsele_select_classe = "SELECT * FROM `ele_liste` WHERE classe_ele =  '". $row_RsClasse2['code_classe']."'  AND groupe_ID_ele = ".$gr_id;
 
  $Rsele_select_classe = mysqli_query($conn_cahier_de_texte, $query_Rsele_select_classe) or die(mysqli_error($conn_cahier_de_texte));
  //$row_Rsele_select_classe = mysqli_fetch_assoc($Rsele_select_classe);
  //$totalRows_Rsele_select_classe = mysqli_num_rows($Rsele_select_classe);
  while (($row_rq = mysqli_fetch_array($Rsele_select_classe , MYSQLI_ASSOC) )) {    
    array_push(  $selected_eles , $row_rq['ID_ele'] );
  }
?>
      <form action="ele_affectation_groupe.php" method="POST">
        <p>Utiliser la touche CTRL pour r&eacute;aliser une s&eacute;lection multiple des &eacute;l&egrave;ves de <strong><?php echo $row_RsClasse2['code_classe'];?></strong> et l'affecter au groupe <strong><?php echo $row_Rsgroupe['groupe'];?></strong>.</p>
        <p>
          <?php
echo '<select name="ele_ID[]" id="ele_ID" size="'.$totalRows_RsEle .'" multiple>';
	$n=1;
do { 
echo "<option name='num".$row_RsEle['ID_ele']."'  value='".$row_RsEle['ID_ele'];
echo "' ";
                                if (in_array($row_RsEle['ID_ele'], $selected_eles ) ) {
                                  echo " selected ";
                                };
								
echo " >".' ';
if ($n<10){echo '0'.$n;} else {echo $n;};
echo '&nbsp;&nbsp;&nbsp; '.$row_RsEle['nom_ele'] . "&nbsp;" . $row_RsEle['prenom_ele']."&nbsp;&nbsp;&nbsp; </option>";
	$n=$n+1;
} while ($row_RsEle = mysqli_fetch_assoc($RsEle)); 
echo '</select>';

?>
        </p>
        <p>&nbsp;</p>
        <input name="submit" type="submit" value="Cr&eacute;er cette affectation">
        <input type="hidden" name="groupe_ID_ele" value="<?php echo $gr_id; ?>">
        <input type="hidden" name="groupe_ele" value="<?php echo $row_Rsgroupe['groupe'];?>">
        <input type="hidden" name="MM_insert" value="form1">
      </form>
      <p>&nbsp;</p>
      <p>
        <?php	
};
};

?>
      <p>&nbsp;</p></td>
  </tr>
</table>

<p>&nbsp;</p>
<p>&nbsp;</p>
<?php
if ($_SESSION['droits']==1){?>
<p align="center"><a href="index.php">Retour au Menu Administrateur</a></p>
<?php };
  if ($_SESSION['droits']==3){?>
<p align="center"><a href="../../vie_scolaire/vie_scolaire.php">Retour au Menu Vie scolaire</a></p>
<?php };?>
</body>
</html>
<?php
        mysqli_free_result($RsClasse);
        mysqli_free_result($Rsgroupe);
        ?>
