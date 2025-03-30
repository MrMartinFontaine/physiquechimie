<?php
session_start();

require_once('../inc/functions_inc.php');
require_once('../Connections/conn_cahier_de_texte.php');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

function CheckUser($login,$password,$base_user,$base_passe,$base_id,$conn_cahier_de_texte, $database_conn_cahier_de_texte)
{

if (substr($base_passe, 0, 1) == "$")
{

    // Password already converted, verify using password_verify
	if (password_verify($password, $base_passe)) {return true;};
	return false;
}
else
{
    // User still using the old MD5, update it!

    if (md5($password) == $base_passe)
    {
		$updateSQL = sprintf("UPDATE cdt_prof SET passe='%s'  WHERE ID_prof=%u", password_hash($password, PASSWORD_DEFAULT),GetSQLValueString($base_id, "int") );
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$Result = mysqli_query($conn_cahier_de_texte, $updateSQL) or die(mysqli_error($conn_cahier_de_texte));	

		return true;
		
    };
	return false;
};

} 
//fin fonction




if(!isset($_POST['passe']))
{
  header("Location: ../index.php");
  die();
}
if(!isset($_POST['nom_prof']))
{
  header("Location: ../index.php");
  die();
}
if($_POST['nom_prof']==NULL)
{
  header("Location: ../index.php");
  die();
}


$password = $_POST["passe"];

$login=VerifChamps($_POST['nom_prof']);

$query_RsProf = sprintf("SELECT * FROM cdt_prof WHERE nom_prof='%s'", $login);

$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);


if ((isset($_SESSION['site_ferme']))&&($_SESSION['site_ferme']!='Non')&&($row_RsProf['droits']<>1))
{
  header("Location: ../index.php");
  die();
};

$base_user=$row_RsProf['nom_prof'];
$base_passe=$row_RsProf['passe'];
$base_id=$row_RsProf['ID_prof'];



	if(!CheckUser($login,$password,$base_user,$base_passe,$base_id,$conn_cahier_de_texte, $database_conn_cahier_de_texte))
	{
	  header("Location: ../index.php");
	  die();
	};

mysqli_free_result($RsProf);
//mot de passe ok


$_SESSION['last_access']=time();
$_SESSION['ipaddr']=$_SERVER['REMOTE_ADDR'];
$_SESSION['ID_prof'] = $row_RsProf['ID_prof'];
$_SESSION['nom_prof'] = $row_RsProf['nom_prof'];
	
// Les if permettent de rentrer pour les mises a jour	

	if (isset($row_RsProf['identite'])){	
	if ($row_RsProf['identite']==NULL){$_SESSION['identite'] = $row_RsProf['nom_prof'];} else {$_SESSION['identite']=$row_RsProf['identite'];};
	}	else {$_SESSION['identite']=$row_RsProf['nom_prof'];};

	if (isset($row_RsProf['email'])){$_SESSION['email'] = $row_RsProf['email'];};
	if (isset($row_RsProf['droits'])){$_SESSION['droits'] = $row_RsProf['droits'];};
	if (isset($row_RsProf['publier_cdt'])){$_SESSION['publier_cdt'] = $row_RsProf['publier_cdt'];};
	if (isset($row_RsProf['publier_travail'])){$_SESSION['publier_travail'] = $row_RsProf['publier_travail'];};
	if (isset($row_RsProf['stop_cdt'])){$_SESSION['stop_cdt'] = $row_RsProf['stop_cdt'];};	
	if (isset($row_RsProf['date_maj'])){$_SESSION['date_visa'] = $row_RsProf['date_maj'];};
	if (isset($row_RsProf['path_fichier_perso'])){$_SESSION['path_fichier_perso'] = $row_RsProf['path_fichier_perso'];};
	if (isset($row_RsProf['xinha_editlatex'])){$_SESSION['xinha_editlatex'] = $row_RsProf['xinha_editlatex'];};
	if (isset($row_RsProf['xinha_equation'])){$_SESSION['xinha_equation'] = $row_RsProf['xinha_equation'];};
	if (isset($row_RsProf['xinha_stylist'])){$_SESSION['xinha_stylist'] = $row_RsProf['xinha_stylist'];};
	if (isset($row_RsProf['acces_rapide'])){$_SESSION['acces_rapide'] = $row_RsProf['acces_rapide'];};
	if (isset($row_RsProf['afficher_messages'])){$_SESSION['afficher_messages'] = $row_RsProf['afficher_messages'];};
	if (isset($row_RsProf['masque_edt_cloture'])){$_SESSION['masque_edt_cloture'] = $row_RsProf['masque_edt_cloture'];};
	if (isset($row_RsProf['id_etat'])){$_SESSION['id_etat'] = $row_RsProf['id_etat'];};	
	if (isset($row_RsProf['id_remplace'])){$_SESSION['id_remplace'] = $row_RsProf['id_remplace'];};
	if (isset($row_RsProf['date_declare_absent'])){$_SESSION['date_declare_absent'] = $row_RsProf['date_declare_absent'];};
	if (isset($row_RsProf['type_affich'])){$_SESSION['type_affich'] = $row_RsProf['type_affich'];};
	

//depublier les messages dont la date de fin de publication est echue
require_once('../inc/depublier_messages.php');

if (isset($row_RsProf['droits'])){
switch ($row_RsProf['droits']) {
case 0:
    header("Location: ../index.php");exit;
    break;
case 1:
    header("Location: ../administration/index.php");exit;
    break;
case 2:// enseignant -  si id_etat = 2 et id_remplace=0 alors c'est un suppleant qui a fini son remplacement
case 8:// documentaliste
    if ($row_RsProf['stop_cdt']=='O'){
	header("Location: stop_enseignant.php");exit;}
	elseif ($row_RsProf['id_etat']=='2' && $row_RsProf['id_remplace']=='0')
	{header("Location: ../index.php");exit;}
	else {

	
	if ($_SESSION['acces_rapide']=='O'){header("Location: ../enseignant/ecrire.php?date=".date('Ymd'));exit;} else {header("Location: ../enseignant/enseignant.php");exit;};
	}
    break;
case 3:
    header("Location: ../vie_scolaire/vie_scolaire.php");exit;
    break;
case 4:
    header("Location: ../direction/direction.php");exit;
    break;
case 5:
    header("Location: ../invite/invite.php");exit;
    break;
case 6:
    header("Location: ../assistant_education/assistant_educ.php");exit;
    break;
case 7: //periscolaire
    header("Location: ../vie_scolaire/vie_scolaire.php");exit;
    break;

default:
    header("Location: ../index.php");exit;}
} else // cas avant mise a jour 304
{
if ($_SESSION['nom_prof']=='Administrateur') { 
$_SESSION['droits']=1; //pour la mise  jour
header("Location: ../administration/index.php");exit;} else {header("Location: ../enseignant/enseignant.php");exit;};
}

?>