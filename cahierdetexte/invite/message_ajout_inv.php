<?php 
if ((isset($_GET['ID_prof']))&&(isset($_GET['ident']))){session_start();} else {include "../authentification/authcheck.php" ;};
if ($_SESSION['droits']<>5) { header("Location: ../index.php");exit;};
$_SESSION['ID_prof']=0;$_SESSION['nom_prof']='Invit&eacute;';
require_once('../inc/functions_inc.php');
require_once('../Connections/conn_cahier_de_texte.php');
 
$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") && (isset($_POST['message']))) {

$datetoday=date('y-m-d');

$date_fin_publier=date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+15,  date("Y")));		

$chaine="<strong>".$_POST['nom_invite']."</strong> - ".date('d/m/Y')."&nbsp;";
if($_POST['mail_invite']<>''){
	$chaine.= "<a href=\"mailto:".$_POST['mail_invite']."\"><img src=\"../images/email.gif\" border=\"0\"/></a>";
};
$chaine.="<br />".$_POST['message'];
//envole-240812 : utilisation de "NOW()" car "date_envoi" est maintenant un champ "DATETIME" pour une datation plus precise (#2221)
$insertSQL = sprintf(" INSERT INTO `cdt_message_contenu` ( `message` , `prof_ID` , `date_envoi`, `date_fin_publier`, `dest_ID`, `online`  )
VALUES (%s,0,NOW(),'%s',2,'O')",  GetSQLValueString($chaine,"text"),$date_fin_publier );



  
  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));

$UID=mysqli_insert_id($conn_cahier_de_texte); 

if (isset($_GET['ID_prof'])){
$insertSQL2= sprintf("INSERT INTO `cdt_message_destinataire_profs` ( `message_ID` , `prof_ID` )  VALUES ('%s', '%s');",$UID,GetSQLValueString($_GET['ID_prof'], "int"));} else {$insertSQL2= sprintf("INSERT INTO `cdt_message_destinataire_profs` ( `message_ID` , `prof_ID` )  VALUES ('%s', '%s');",$UID,GetSQLValueString($_POST['ID_prof'], "int"));};

$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL2) or die(mysqli_error($conn_cahier_de_texte));


  // A faire uniquement si fichier joint
  // fichier message joint 1  ***************************************************************************

  if ($_FILES['fichier_1_message']['name']<>'') {
    $dossier_destination = getcwd().'/../fichiers_joints_message/'; 

    $dossier_temporaire = $_FILES['fichier_1_message']['tmp_name'];
	$type_fichier = $_FILES['fichier_1_message']['type'];
	$nom_fichier1 = sans_accent($_FILES['fichier_1_message']['name']);

	if (preg_match('/.php/i',$nom_fichier1)) {$nom_fichier1 .= ".txt"; };
	$erreur= $_FILES['fichier_1_message']['error'];
	if ($erreur == 2 ) {
		exit ("Le fichier 1 joint au message d&eacute;passe la taille de 100 Mo.");
	}
	if ($erreur == 3 ) {
		exit ("Le fichier 1 joint au message a &eacute;t&eacute; partiellement transf&eacute;r&eacute;. Envoyez-le &agrave; nouveau.");
	}

		if( !move_uploaded_file($dossier_temporaire, $dossier_destination . $UID.'_'.$nom_fichier1) )
    {
        exit("Impossible de copier le fichier 1 joint au message dans le dossier_destination");
    }
  
    //--------------ecriture dans la table du nom du fichier
    if ($_FILES['fichier_1_message']['name']<>'') {
		$insertSQL = sprintf("INSERT INTO cdt_message_fichiers ( message_ID, nom_fichier, prof_ID) VALUES (%s,%s,%s)",
                       GetSQLValueString($UID, "int"),
                       GetSQLValueString($UID.'_'.$nom_fichier1, "text"),
                        GetSQLValueString($_POST['ID_prof'], "int")
					   );
					   
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  	$Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
	}

  
  }

// fin presence fichiers  message 1 joint ****************************************************************

// fichier message joint 2  ***************************************************************************
if ($_FILES['fichier_2_message']['name']<>'') {
    $dossier_destination = getcwd().'/../fichiers_joints_message/'; 

    $dossier_temporaire = $_FILES['fichier_2_message']['tmp_name'];
	$type_fichier = $_FILES['fichier_2_message']['type'];
	$nom_fichier2 = sans_accent($_FILES['fichier_2_message']['name']);

	if (preg_match('/.php/i',$nom_fichier2)) {$nom_fichier2 .= ".txt"; };
	$erreur= $_FILES['fichier_2_message']['error'];
	if ($erreur == 2 ) {
		exit ("Le fichier 2 joint au message d&eacute;passe la taille de 100 Mo.");
	}
	if ($erreur == 3 ) {
		exit ("Le fichier 2 joint au message a &eacute;t&eacute; partiellement transf&eacute;r&eacute;. Envoyez-le &agrave; nouveau.");
	}

		if( !move_uploaded_file($dossier_temporaire, $dossier_destination . $UID.'_'.$nom_fichier2) )
    {
        exit("Impossible de copier le fichier 2 joint au message dans le dossier_destination");
    }
  
    //--------------ecriture dans la table du nom du fichier
    if ($_FILES['fichier_2_message']['name']<>'') {
		$insertSQL = sprintf("INSERT INTO cdt_message_fichiers ( message_ID, nom_fichier, prof_ID) VALUES (%s,%s,%s)",
                       GetSQLValueString($UID, "int"),
                       GetSQLValueString($UID.'_'.$nom_fichier2, "text"),
                         GetSQLValueString($_POST['ID_prof'], "int")
					   );
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
  	$Result2 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
	}
	}
// fin presence fichiers  message 2 joint ****************************************************************


if ((isset($_GET['ID_prof']))&&(isset($_GET['ident']))){
$insertGoTo = "invite.php?ID_prof=".$_GET['ID_prof']."&ident=".$_GET['ident']."&envoi_ok";
} else {$insertGoTo = "invite.php?envoi_ok";};
header(sprintf("Location: %s", $insertGoTo));


};






?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<?php 
if ((isset($_GET['ID_prof']))&&(isset($_GET['ident']))){$sql="WHERE cdt_invite.prof_ID=".$_GET['ID_prof'];} else {$sql='';};
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsInvite = sprintf("SELECT * FROM ((cdt_invite INNER JOIN cdt_classe ON cdt_invite.classe_ID=cdt_classe.ID_classe INNER JOIN cdt_prof ON  cdt_invite.prof_ID =cdt_prof.ID_prof INNER JOIN cdt_matiere ON  cdt_invite.matiere_ID =cdt_matiere.ID_matiere
 ) LEFT JOIN cdt_groupe_interclasses ON cdt_invite.gic_ID=cdt_groupe_interclasses.ID_gic) %s ORDER BY nom_prof, nom_matiere, NumArchive,  nom_classe ",$sql);

$RsInvite = mysqli_query($conn_cahier_de_texte, $query_RsInvite) or die(mysqli_error($conn_cahier_de_texte));
$row_RsInvite = mysqli_fetch_assoc($RsInvite);
$totalRows_RsInvite = mysqli_num_rows($RsInvite);


?>
</HEAD>
<BODY>
<DIV id=page>
  <?php 
$header_description="Envoyer un message &agrave; l'enseignant via son cahier de textes";
require_once "../templates/default/header.php";
 
?>
  <script> formfocus(); </script>
  <div align="center">
    <p>&nbsp;</p>
    <fieldset style="width : 90%">
    <legend align="top">Contacter l'enseignant via son cahier de textes</legend>
    <br />
    <br />
    <form onLoad= "formfocus()" method="post"  name="form1" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>">
      <div align="center">
        <table width="75%" border="0" class="tab_detail_gris">
          <tr>
            <td><br>
              <div align="right">Votre nom : </div></td>
            <td><br>
              <input name="nom_invite" type="text" id="nom_invite" size="50"></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td><div align="right">Votre adresse m&eacute;l : </div>
              <br></td>
            <td><input name="mail_invite" type="text" id="mail_invite" size="50">
              <br>
              <br></td>
          </tr>
        </table>
        <label><br>
        </label>
        <label></label>
        <?php
	if ((isset($_SESSION['affiche_xinha']))&&($_SESSION['affiche_xinha']==1)){
		include('area_message.php');}
	else { 
		include('../enseignant/area_message_tiny.php');};
	?>
        <textarea name="message" cols="80" rows="15" id="message" width="200" height= "80" ></textarea>
        <br>
        Documents joints &agrave; votre message : <br />
        <br />
        <input type="hidden" name="MAX_FILE_SIZE" value="100000000">
        <input type="FILE" size="80" name="fichier_1_message" class="Style2">
        <br />
        <br />
        <input type="hidden" name="MAX_FILE_SIZE" value="100000000">
        <input type="FILE" size="80" name="fichier_2_message" class="Style2">
        <br />
        <br />
        <input name="submit" type="submit" value="Envoyer ce message">
        <input type="hidden" name="MM_insert" value="form1">
        <input type="hidden" name="ID_prof" value="<?php echo $_GET['ID_prof'];?>">
      </div>
    </form>
    </fieldset>
  </div>
  <?php
	if ((isset($_GET['ID_prof']))&&(isset($_GET['ident']))){?>
  <p><a href="invite.php?ID_prof=<?php echo $_GET['ID_prof'];?>&ident=<?php echo $_GET['ident'];?>">Annuler </a></p>
  <?php } else {?>
  <p><a href="invite.php">Annuler </a></p>
  <?php };?>
  <DIV id=footer>
    <p class="auteur"><a href="../contribution.php" class="auteur">Pierre Lemaitre 
      - St L&ocirc; (France) </a></p>
  </DIV>
</DIV>
</body>
</html>
