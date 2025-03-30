<?php 
//session_start();
include "../authentification/authcheck.php";
require_once('../Connections/conn_cahier_de_texte.php'); 
require_once('../inc/functions_inc.php');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
</head>

<body>
	  <div align="center">
	    <?php //messages des professeurs principaux 
	  
	  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rspp_diffusion =sprintf("
SELECT DISTINCT ID_message,nom_classe,message,date_envoi,identite,nom_prof,email
FROM cdt_classe, cdt_emploi_du_temps, cdt_message_contenu, cdt_prof_principal, cdt_prof, cdt_groupe
WHERE `dest_ID` =2
AND cdt_classe.ID_classe = cdt_emploi_du_temps.classe_ID 
AND cdt_groupe.groupe = cdt_emploi_du_temps.groupe
AND cdt_emploi_du_temps.prof_ID=%u
AND cdt_prof_principal.pp_classe_ID = cdt_classe.ID_classe
AND cdt_message_contenu.prof_ID = cdt_prof_principal.pp_prof_ID
AND cdt_message_contenu.prof_ID = cdt_prof.ID_prof
AND cdt_message_contenu.pp_classe_ID =cdt_classe.ID_classe
AND (cdt_message_contenu.pp_groupe_ID =cdt_groupe.ID_groupe OR cdt_groupe.ID_groupe=1 OR cdt_message_contenu.pp_groupe_ID=1)
AND cdt_message_contenu.online='O'
ORDER BY date_envoi DESC,nom_classe ASC",$_SESSION['ID_prof']);


$Rspp_diffusion = mysqli_query($conn_cahier_de_texte, $query_Rspp_diffusion) or die(mysqli_error($conn_cahier_de_texte));
$row_Rspp_diffusion = mysqli_fetch_assoc($Rspp_diffusion);
$totalRows_Rspp_diffusion = mysqli_num_rows($Rspp_diffusion);


//messages vie scolaire ou resp. etablissement

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsadm_diffusion =sprintf("SELECT * FROM cdt_message_contenu,cdt_message_destinataire_profs,cdt_prof WHERE cdt_message_contenu.dest_ID=2 AND cdt_message_contenu.ID_message=cdt_message_destinataire_profs.message_ID AND cdt_message_contenu.online='O' AND cdt_message_contenu.prof_ID=cdt_prof.ID_prof AND cdt_message_destinataire_profs.prof_ID=%u
ORDER BY date_envoi DESC,nom_prof ASC ",$_SESSION['ID_prof']) ;

$Rsadm_diffusion = mysqli_query($conn_cahier_de_texte, $query_Rsadm_diffusion) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsadm_diffusion = mysqli_fetch_assoc($Rsadm_diffusion);
$totalRows_Rsadm_diffusion = mysqli_num_rows($Rsadm_diffusion);


if ($totalRows_Rspp_diffusion>0) {
	  ?>
	    <br />
	      <table width="600" height="172" border="0" cellpadding="0" cellspacing="0" class="bordure">
	        <tr>
	          <td height="20"  class="Style6">Messages des professeurs principaux</td>
          </tr>
	        <tr>
	          <td valign="top" class="Style15">
                <div align="left"><br />
                  <?php $nb=0;do { 
			echo '<p><b>'.$row_Rspp_diffusion['nom_classe'].'</b> (';
			if($row_Rspp_diffusion['identite']==''){echo $row_Rspp_diffusion['nom_prof'];}else {echo $row_Rspp_diffusion['identite'];};
			echo ' - '.substr($row_Rspp_diffusion['date_envoi'],8,2).'/'.substr($row_Rspp_diffusion['date_envoi'],5,2).'/'.substr($row_Rspp_diffusion['date_envoi'],0,4).')';
			
	  if ($row_Rspp_diffusion['email']<>''){ ?> 
                  <a href="mailto:<?php echo $row_Rspp_diffusion['email'];?>"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant" title="Contacter l'enseignant" /></a>
                  <?php ;};
	  echo'</p><blockquote><p>'.$row_Rspp_diffusion['message'].'</p></blockquote>';
	  
	  //fichiers joints au message des professeurs principaux
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE message_ID=".$row_Rspp_diffusion['ID_message'];
$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

if ($totalRows_Rs_fichiers_joints_form>0){
if ($totalRows_Rs_fichiers_joints_form>1){echo '<blockquote><p>Documents joints : <br /> ';} else {echo '<blockquote><p>Document joint : ';};
do {
$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
echo '<a href="../fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a><br />';
} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
echo '</p></blockquote>';}
$nb=$nb+1;if($nb < $totalRows_Rspp_diffusion){ echo '<p class="bas_ligne"></p>';};	  
	  
	 
		      } while ($row_Rspp_diffusion = mysqli_fetch_assoc($Rspp_diffusion)); ?>
                 </div></td>
          </tr>
	          </table>
	    <?php  };// fin message prof. principaux 
	  
	  //message vie scolaire et resp.etab. aux enseignants
	  if ($totalRows_Rsadm_diffusion>0) {
	  ?>
	    <br />
	      <table width="600" height="172" border="0" cellpadding="0" cellspacing="0" class="bordure">
	        <tr>
	          <td height="20"  class="Style6">Messages de la vie scolaire et resp. &eacute;tablissement aux enseignants</td>
          </tr>
	        <tr>
	          <td valign="top" class="Style15">
                <div align="left"><br />
                  <?php $nb=0;do { 
			if($row_Rsadm_diffusion['identite']==''){echo '<strong>'.$row_Rsadm_diffusion['nom_prof'].'</strong>';}else {echo '<strong>'.$row_Rsadm_diffusion['identite'].'</strong>';};
			echo ' - '.substr($row_Rsadm_diffusion['date_envoi'],8,2).'/'.substr($row_Rsadm_diffusion['date_envoi'],5,2).'/'.substr($row_Rsadm_diffusion['date_envoi'],0,4);
			
	  if ($row_Rsadm_diffusion['email']<>''){ ?> 
                  <a href="mailto:<?php echo $row_Rsadm_diffusion['email'];?>"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant" title="Contacter l'enseignant" /></a>
                  <?php ;};
	  echo'</p><blockquote><p>'.$row_Rsadm_diffusion['message'].'</p></blockquote>';
	  //fichiers joints au message
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE message_ID=".$row_Rsadm_diffusion['ID_message'];
$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

if ($totalRows_Rs_fichiers_joints_form>0){
if ($totalRows_Rs_fichiers_joints_form>1){echo '<blockquote><p>Documents joints : <br /> ';} else {echo '<blockquote><p>Document joint : ';};
do {
$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
echo '<a href="../fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a><br />';
} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
echo '</p></blockquote>';};
$nb=$nb+1;if($nb < $totalRows_Rsadm_diffusion){ echo '<p class="bas_ligne"></p>';};
	  

	  
		      } while ($row_Rsadm_diffusion = mysqli_fetch_assoc($Rsadm_diffusion)); ?>
                 </div></td>
          </tr>
	          </table>
	    <?php  };// fin message vie scolaire et resp. etab. aux enseignants
          
          
          
          //message vie scolaire et resp. etab. a tous les eleves
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsadm_diffusion_elev ="SELECT * FROM cdt_message_contenu, cdt_prof WHERE cdt_message_contenu.dest_ID=1 AND cdt_message_contenu.online='O' AND cdt_message_contenu.prof_ID=cdt_prof.ID_prof ORDER BY date_envoi DESC,nom_prof ASC" ;

$Rsadm_diffusion_elev = mysqli_query($conn_cahier_de_texte, $query_Rsadm_diffusion_elev) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsadm_diffusion_elev = mysqli_fetch_assoc($Rsadm_diffusion_elev);
$totalRows_Rsadm_diffusion_elev = mysqli_num_rows($Rsadm_diffusion_elev);
	  if ($totalRows_Rsadm_diffusion_elev>0) {
	  ?>
	    <br />
	      <table width="600" height="172" border="0" cellpadding="0" cellspacing="0" class="bordure">
	        <tr>
	          <td height="20"  class="Style6">Messages de la vie scolaire et resp. &eacute;tablissement &agrave; tous les &eacute;l&egrave;ves</td>
          </tr>
	        <tr>
	          <td valign="top" class="Style15">
                <div align="left"><br />
                  <?php $nb=0;do { 
			if($row_Rsadm_diffusion_elev['identite']==''){echo '<strong>'.$row_Rsadm_diffusion_elev['nom_prof'].'</strong>';}else {echo '<strong>'.$row_Rsadm_diffusion_elev['identite'].'</strong>';};
			echo ' - '.substr($row_Rsadm_diffusion_elev['date_envoi'],8,2).'/'.substr($row_Rsadm_diffusion_elev['date_envoi'],5,2).'/'.substr($row_Rsadm_diffusion_elev['date_envoi'],0,4);
			
	  if ($row_Rsadm_diffusion_elev['email']<>''){ ?> 
                  <a href="mailto:<?php echo $row_Rsadm_diffusion_elev['email'];?>"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant" title="Contacter l'enseignant" /></a>
                  <?php ;};
	  echo'</p><blockquote><p>'.$row_Rsadm_diffusion_elev['message'].'</p></blockquote>';
	  //fichiers joints au message
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE message_ID=".$row_Rsadm_diffusion_elev['ID_message'];
$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

if ($totalRows_Rs_fichiers_joints_form>0){
if ($totalRows_Rs_fichiers_joints_form>1){echo '<blockquote><p>Documents joints : <br /> ';} else {echo '<blockquote><p>Document joint : ';};
do {
$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
echo '<a href="../fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'"/>'.$nom_f.'</a><br />';
} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
echo '</p></blockquote>';};
$nb=$nb+1;if($nb < $totalRows_Rsadm_diffusion_elev){ echo '<p class="bas_ligne"></p>';};
	  

	  
		      } while ($row_Rsadm_diffusion_elev = mysqli_fetch_assoc($Rsadm_diffusion_elev)); ?>
                 </div></td>
          </tr>
	          </table>
	    <?php  };// fin message vie scolaire et resp. etab. a tous les eleves
	  
	  ?>
      </div>
</body>
</html>
