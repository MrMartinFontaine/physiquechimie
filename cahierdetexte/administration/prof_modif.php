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
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {


//tester si ce nom_prof existe déjà en cas de changement de nom_prof
if ($_POST['nom_prof_initial']<>$_POST['nom_prof']){
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsp = sprintf("SELECT nom_prof FROM cdt_prof WHERE nom_prof='%s'",$_POST['nom_prof']);
$Rsp = mysqli_query($conn_cahier_de_texte, $query_Rsp) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsp = mysqli_fetch_assoc($Rsp);
$totalRows_Rsp = mysqli_num_rows($Rsp);

if ($totalRows_Rsp<>0){$erreur5="Modification impossible ! &nbsp;<strong> ".$_POST['nom_prof']."</strong>&nbsp; est un nom d'utilisateur d&eacute;j&agrave; pr&eacute;sent dans la liste.";}
mysqli_free_result($Rsp);
};

if ($erreur5==''){


  $ident=$_POST['identite'];

    	if ((isset($_POST['ID_prof'])) && ($_POST['ID_prof'] == "1"))
        	{$droits_prof = '1';
        	}
        else 	{$droits_prof = $_POST['droits'];
        	} ;
  	$updateSQL = sprintf("UPDATE cdt_prof SET identite=%s , nom_prof=%s ,  droits=%s , email=%s WHERE ID_prof=%u",
                       GetSQLValueString($ident, "text"),
                       GetSQLValueString($_POST['nom_prof'], "text"),
                       GetSQLValueString($droits_prof , "int"),
                       GetSQLValueString($_POST['email'] , "text"),
			GetSQLValueString($_POST['ID_prof'], "int")
					   );
				
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));


if ($_POST['passe']<>''){
 

 
  $password=$_POST['passe'];

  
  $updateSQL2 = sprintf("UPDATE cdt_prof SET  passe='%s' WHERE ID_prof=%u",
                       password_hash($password, PASSWORD_DEFAULT),
					   GetSQLValueString($_POST['ID_prof'], 'int')
					   );

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$Result2 = mysqli_query($conn_cahier_de_texte, $updateSQL2) or die(mysqli_error($conn_cahier_de_texte));
  
  
  
  };

  $updateGoTo = "prof_ajout.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
 header(sprintf("Location: %s", $updateGoTo));
}
}
$IDmat_RsModifProf = "0";
if (isset($_GET['ID_prof'])) {
  $IDmat_RsModifProf = (get_magic_quotes_gpc()) ? $_GET['ID_prof'] : addslashes($_GET['ID_prof']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsModifProf = sprintf("SELECT * FROM cdt_prof WHERE cdt_prof.ID_prof=%u", $IDmat_RsModifProf);
$RsModifProf = mysqli_query($conn_cahier_de_texte, $query_RsModifProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsModifProf = mysqli_fetch_assoc($RsModifProf);
$totalRows_RsModifProf = mysqli_num_rows($RsModifProf);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<SCRIPT type="text/javascript" src="../jscripts/cryptage_passe.js"></script>

</HEAD>
<BODY>
<DIV id=page>
<?php 
if ($row_RsModifProf['ID_prof']=='1'){$header_description='Modification du mot de passe de l\'Administrateur';} else {$header_description='Modification du nom et mot de passe d\'un utilisateur';   };
require_once "../templates/default/header.php";
?>
<HR>
 <?php  
if ($row_RsModifProf['ID_prof']!='1'){?>
  <p>Le champ NOM Pr&eacute;nom ou identit&eacute; est le libell&eacute; affich&eacute; sur la fiche &eacute;l&egrave;ve et parents (Ex : Bruno Carla). <br>
Eviter de Mr ou Mme devant le nom, le tri se faisant sur la premi&egrave;re lettre dans les menus d&eacute;roulants. <br>
L'identifiant est un libell&eacute; court et sans accents ni espaces utilis&eacute; par l'enseignant pour se connecter (Ex : c_bruno) <br>
Si le nom n'est pas renseign&eacute;, il se verra attribuer l'identifiant. </p>
  <?php };?>


<?php if($erreur5!=''){echo '<div class="erreur"><br/>'.$erreur5.'<br/><br/></div>' ;}?>
 
  <form method="post" name="form1" action="<?php echo $editFormAction; ?>" onSubmit="return submit_modifpass2();" >
    

  <table align="center" cellpadding="5" cellspacing="5" class="tab_detail_gris" >

	 
	  <tr valign="baseline">
      <td><div align="right"><strong>NOM Pr&eacute;nom </strong></div></td>
      <td><div align="left">
        <input type="text" name="identite" value="<?php echo $row_RsModifProf['identite']; ?>" size="32">
      </div></td>
    </tr>
	<?php if ($row_RsModifProf['ID_prof']=='1'){?>
	  <input name="nom_prof" id="nom_prof" type="hidden" value="Administrateur">
	<?php ;} else { ?>
	  <tr valign="baseline">
      <td><div align="right"><strong>IDENTIFIANT (login - pas d'accents)</strong></div></td>
      <td><div align="left">
        <input type="text" name="nom_prof"  id="nom_prof" value="<?php echo $row_RsModifProf['nom_prof']; ?>" size="32">
      </div></td></tr>
	<?php ;};?>
	  <tr valign="baseline">
        <td><div align="right"><strong>Nouveau<br>
        MOT DE PASSE </strong></div></td>
	    <td><div align="left">
            <input type="password" name="passe" id="passe" size="32">
        </div></td>
      </tr>
	  <tr valign="baseline">
	  <td><div align="right"><strong>Confirmer le <br>
	    MOT DE PASSE </strong></div></td>
      <td>
        <div align="left">
          <input type="password" name="passe2"  id="passe2" size="32">
        </div></td>
    </tr>
	<tr valign="baseline">
	  <td><div align="right"><strong>Adresse m&eacute;l (facultatif) </strong></div></td>
      <td>
        <div align="left">
          <input name="email" type="text" id="email" size="32" value="<?php echo $row_RsModifProf['email']; ?>">
        </div></td>
    </tr>
	
    <tr>
	      <td><div align="right"><strong>PROFILS ET DROITS</strong></div></td>
      <td> <div align="left"><select name="droits" id="droits" 
      <?php 
	  if ($row_RsModifProf['ID_prof']=='1'){echo "disabled";} ;?> >
          <option value="0" <?php if( $row_RsModifProf['droits']==0){echo 'selected';} ; ?>>Interdire temporairement l'acc&egrave;s</option>
          <option value="1" <?php if( $row_RsModifProf['droits']==1){echo 'selected';} ; ?>>Administrateur</option>
          <option value="2" <?php if( $row_RsModifProf['droits']==2){echo 'selected';} ; ?>>Enseignant</option>
          <option value="3" <?php if( $row_RsModifProf['droits']==3){echo 'selected';} ; ?>>Vie Scolaire</option>
          <option value="4" <?php if( $row_RsModifProf['droits']==4){echo 'selected';} ; ?>>Resp. Etablissement</option>
          <option value="5" <?php if( $row_RsModifProf['droits']==5){echo 'selected';} ; ?>>Invit&eacute;</option>
		  <option value="6" <?php if( $row_RsModifProf['droits']==6){echo 'selected';} ; ?>>Assistant Education</option>
		  <option value="7" <?php if( $row_RsModifProf['droits']==7){echo 'selected';} ; ?>>P&eacute;riscolaire (Infirmi&egrave;re...)</option>	
		  <option value="8" <?php if( $row_RsModifProf['droits']==8){echo 'selected';} ; ?>>Documentaliste</option>
        </select> </div>      </td>
    </tr>
    <tr valign="baseline">
    
      <td colspan="2">
          <p align="center"><br>
            <input type="submit" value="Mettre &agrave; jour">
            <br>
        </p>          <p align="right">        </p></td>
    </tr>
  </table>
  <p></p>

  <p>
    <input type="hidden" name="MM_update" value="form1">
    <input type="hidden" name="ID_prof" value="<?php echo $row_RsModifProf['ID_prof']; ?>">
	<input type="hidden" name="nom_prof_initial" value="<?php echo $row_RsModifProf['nom_prof']; ?>">

    </p>
</form>
<p>&nbsp;</p>
<p align="center"><a href="prof_ajout.php">Annuler</a></p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>

<?php
mysqli_free_result($RsModifProf);
?>
