<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

if ($_GET['gestion_sem_ab']=='O'){


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsJour =  sprintf("SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
WHERE cdt_emploi_du_temps.prof_ID=%u 
AND cdt_emploi_du_temps.jour_semaine='%s' 
AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
AND (cdt_emploi_du_temps.semaine='%s' OR cdt_emploi_du_temps.semaine='A et B' ) 
AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
AND cdt_emploi_du_temps.edt_exist_fin >= '%s'
ORDER BY cdt_emploi_du_temps.heure,cdt_emploi_du_temps.semaine",$_SESSION['ID_prof'],$_GET['jour_RsJour'],$_SESSION['semdate'],$_GET['madate'],$_GET['madate']);

}

else { //gestion des semaines pas prises en compte
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsJour =  sprintf("SELECT * FROM cdt_matiere, cdt_emploi_du_temps 
LEFT JOIN cdt_classe ON cdt_emploi_du_temps.classe_ID=cdt_classe.ID_classe 
WHERE cdt_emploi_du_temps.prof_ID=%u 
AND cdt_emploi_du_temps.jour_semaine='%s' 
AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere 
AND cdt_emploi_du_temps.edt_exist_debut <= '%s'
AND cdt_emploi_du_temps.edt_exist_fin >= '%s'
ORDER BY cdt_emploi_du_temps.heure,cdt_emploi_du_temps.semaine",$_SESSION['ID_prof'],$_GET['jour_RsJour'],$_GET['madate'],$_GET['madate']);

}; // du $row_RsSem['gestion_sem_ab']=='O'


$RsJour = mysqli_query($conn_cahier_de_texte, $query_RsJour) or die(mysqli_error($conn_cahier_de_texte));
$row_RsJour = mysqli_fetch_assoc($RsJour);
$totalRows_RsJour = mysqli_num_rows($RsJour);?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Cahier de textes - Synth&egrave;se du <?php echo $_GET['jour_RsJour'].'  '.substr($_GET['madate'],8,2).'-'.substr($_GET['madate'],5,2).'-'.substr($_GET['madate'],0,4);?></title>
<link href="../styles/style_default.css" rel="stylesheet" type="text/css" />
<!--  
Appel de la feuille de style perso pour creation de ses styles personnels dans l'editeur XINHA 
-->
<?php 
if($_SESSION['xinha_stylist']=="O"){ ?><link href="../templates/default/perso.css" rel="stylesheet" type="text/css"><?php ;};
if($_SESSION['xinha_equation']=="O"){ ?><script type="text/javascript" src="xinha/plugins/Equation/ASCIIMathML.js"></script>
<?php ;};?>
<style type="text/css">
<!--
.Style71 {
	font-size: 12;
	font-weight: bold;
}
-->
</style>
</head>
<?php
if 	($totalRows_RsJour<>0) {?>
<body>
<div align="center">
  <div class="lire_cellule_4" style="width:95%;" >
    <?php if (isset($_SESSION['identite'])){echo $_SESSION['identite'];}?>
    <a href="<?php 
	if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==2)){echo'ecrire.php?date='.substr($_GET['madate'],0,4).substr($_GET['madate'],5,2).substr($_GET['madate'],8,2);}?>
">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="../images/home-menu.gif" alt="Accueil" width="26" height="18" border="0" /></a></div>
</div>
<p class="Style71"><br />
  Synth&egrave;se du <?php echo $_GET['jour_RsJour'].'  '.substr($_GET['madate'],8,2).'-'.substr($_GET['madate'],5,2).'-'.substr($_GET['madate'],0,4);?>  - Semaine&nbsp;<?php   
if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){
			if ($_SESSION['semdate']=='A et B'){echo 'P et I';} 
			else if($_SESSION['semdate']=='A'){echo 'Paire';} 
			else {echo 'Impaire';};
		}
else {echo isset($_SESSION['semdate'])?$_SESSION['semdate']:'';};?><br />
</p><br />
<table  width="95%" align="center" cellspacing="0" >
  <tr>
    <td width="10%"  class="lire_cellule_4">&nbsp;</td>
    <td width="25%" class="lire_cellule_4">A faire pour ce jour</td>
    <td width="35%" class="lire_cellule_4">Contenu de la s&eacute;ance</td>
    <td width="25%" class="lire_cellule_4">A faire prochainement</td>
  </tr>


</table>
<br />

<table  width="95%" align="center" cellspacing="0"  class="lire_bordure">
  <?php 
do {   ?>
  <tr>
    <td width="10%" class="tab_detail"><?php 

if ($row_RsJour['heure_debut'] <>''){ echo $row_RsJour['heure_debut'].'<br />';} else {echo $row_RsJour['heure'].'<br />';}
echo '<br />';
if ($row_RsJour['classe_ID']==0){

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgic_classe_ID_default =sprintf("SELECT classe_ID FROM cdt_groupe_interclasses_classe WHERE gic_ID=%u LIMIT 1",$row_RsJour['gic_ID']);
$Rsgic_classe_ID_default = mysqli_query($conn_cahier_de_texte, $query_Rsgic_classe_ID_default) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgic_classe_ID_default = mysqli_fetch_assoc($Rsgic_classe_ID_default);
$classe_Rslisteactivite=$row_Rsgic_classe_ID_default['classe_ID'];} else {
$classe_Rslisteactivite=$row_RsJour['classe_ID'];}

if ($row_RsJour['classe_ID']==0){
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgic =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u ",$row_RsJour['gic_ID']);
$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgic = mysqli_fetch_assoc($Rsgic);
$classe_affiche= '<strong>'.$row_Rsgic['nom_gic']. ' (R)</strong>';
;}
else
{$classe_affiche=$row_RsJour['nom_classe'];};

echo '<strong>'.$classe_affiche.'</strong><br>';
echo $row_RsJour['groupe'].'<br>';
echo $row_RsJour['nom_matiere'].'<br>';?>
    </td>
    <td width="25%" class="tab_detail"><?php 
        //cellule 1 Travail a faire pour ce jour
        if ($row_RsJour['groupe']=='Classe entière'){$sql_groupe='';}
        else { $sql_groupe="AND (groupe='Classe entière' OR groupe='".$row_RsJour['groupe']."')";};


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);	
$query_Rs_Travail_du_jour = sprintf("SELECT * FROM cdt_travail WHERE t_jour_pointe=%s AND prof_ID=%u AND classe_ID=%u AND gic_ID=%u AND matiere_ID=%u %s ORDER BY code_date ", substr($_GET['madate'],0,4).substr($_GET['madate'],5,2).substr($_GET['madate'],8,2),$_SESSION['ID_prof'],$classe_Rslisteactivite,$row_RsJour['gic_ID'],$row_RsJour['matiere_ID'],$sql_groupe);
	
	include "../inc/vue_du_jour_travail_inc.php" ;?>
    </td>
    <td width="35%" class="tab_detail"><?php 
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			
$query_Rslisteactivite = sprintf("SELECT * FROM cdt_agenda WHERE prof_ID=%u AND classe_ID=%u AND matiere_ID=%u AND substring(code_date,1,8)='%s' AND semaine='%s' AND groupe ='%s' AND heure=%s  ",$_SESSION['ID_prof'], $classe_Rslisteactivite,$row_RsJour['matiere_ID'],substr($_GET['madate'],0,4).substr($_GET['madate'],5,2).substr($_GET['madate'],8,2),$row_RsJour['semaine'],$row_RsJour['groupe'],$row_RsJour['heure']);
		
include "../inc/vue_du_jour_seance_inc.php" ;?>
    </td>
    <td width="25%" class="tab_detail"><?php mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);	
$query_Rs_Travail_du_jour = sprintf("SELECT * FROM cdt_travail WHERE substring(code_date,1,8)='%s' AND substring(code_date,9,1)<>0 AND prof_ID=%u AND matiere_ID=%u AND classe_ID=%u AND gic_ID=%u %s ORDER BY code_date ", substr($_GET['madate'],0,4).substr($_GET['madate'],5,2).substr($_GET['madate'],8,2),$_SESSION['ID_prof'],$row_RsJour['matiere_ID'],$classe_Rslisteactivite,$row_RsJour['gic_ID'],$sql_groupe);


include "../inc/vue_du_jour_travail_inc.php" ;?>
    </td>
  </tr>
  <?php

  } while ($row_RsJour = mysqli_fetch_assoc($RsJour)); 
mysqli_free_result($RsJour);
?>
</table>
<?php
}

//********************************************************************
//debut affichage cellules heures supplementaires

if (isset($_GET['madate'])){
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsJour = sprintf("SELECT * FROM cdt_agenda,cdt_classe,cdt_matiere WHERE substring(code_date,9,1)=0 AND substring(code_date,1,8)=%s AND cdt_agenda.prof_ID=%u AND cdt_classe.ID_classe=cdt_agenda.classe_ID AND cdt_matiere.ID_matiere=cdt_agenda.matiere_ID AND cdt_agenda.type_activ<>'ds_prog'  GROUP BY cdt_agenda.heure ORDER BY cdt_agenda.heure",substr($_GET['madate'],0,4).substr($_GET['madate'],5,2).substr($_GET['madate'],8,2),$_SESSION['ID_prof']);
$RsJour = mysqli_query($conn_cahier_de_texte, $query_RsJour) or die(mysqli_error($conn_cahier_de_texte));
$row_RsJour = mysqli_fetch_assoc($RsJour);
$totalRows_RsJour = mysqli_num_rows($RsJour);

if ($totalRows_RsJour>0){
?>
<br />
<table  width="95%" align="center" cellspacing="0"  class="lire_bordure">
  <?php
do { 
      ?>
  <tr class="Style1" >
    <td width="10%" class="tab_detail"><div  style="color: #FF0000">Heure sup.</div>
      <?php
 		   if ($row_RsJour['heure_debut'] <>''){ echo $row_RsJour['heure_debut'];} else {echo $row_RsJour['heure'];};
echo '<br />';?>
      </p>
      <?php
		 
		 if ($row_RsJour['gic_ID']==0){echo '<strong><br/>'.$row_RsJour['nom_classe'].'</strong>';}else{
		 
$query_Rsgic_classe_ID_default =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u LIMIT 1",$row_RsJour['gic_ID']);
$Rsgic_classe_ID_default = mysqli_query($conn_cahier_de_texte, $query_Rsgic_classe_ID_default) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgic_classe_ID_default = mysqli_fetch_assoc($Rsgic_classe_ID_default);	 
echo '<strong><br/>'.$row_Rsgic_classe_ID_default['nom_gic'].' (R)</strong>';
		 
		 
		 };
		 //if ($row_RsJour['gic_ID']==0){echo $row_RsJour['gic_ID'];

		 echo '<br/>'.$row_RsJour['groupe'].'<br/>'.$row_RsJour['nom_matiere'].'<br/>';
?>
      <br />
    </td>
    <td width="25%" class="tab_detail"><?php 
  //Pas de travail programme sur une heure sup
 //include "../inc/vue_du_jour_travail_inc.php" ;?>    </td>
    <td width="35%" class="tab_detail"><?php 
	
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rslisteactivite = sprintf("SELECT * FROM cdt_agenda,cdt_matiere WHERE substring(code_date,9,1)=0 AND substring(code_date,1,8)=%s AND cdt_agenda.prof_ID=%u AND cdt_agenda.classe_ID = %u AND cdt_agenda.gic_ID = %u AND cdt_matiere.ID_matiere=cdt_agenda.matiere_ID AND cdt_agenda.type_activ<>'ds_prog' GROUP BY cdt_agenda.heure ORDER BY cdt_agenda.heure",substr($_GET['madate'],0,4).substr($_GET['madate'],5,2).substr($_GET['madate'],8,2),$_SESSION['ID_prof'],$row_RsJour['classe_ID'],$row_RsJour['gic_ID']);

include "../inc/vue_du_jour_seance_inc.php" ;?></td>
    <td width="25%" class="tab_detail"><?php 	
	
$query_Rs_Travail_du_jour = sprintf("SELECT * FROM cdt_travail WHERE substring(cdt_travail.code_date,1,8)='%s' AND prof_ID=%u AND matiere_ID=%u AND classe_ID=%u AND gic_ID=%u %s ORDER BY code_date", substr($_GET['madate'],0,4).substr($_GET['madate'],5,2).substr($_GET['madate'],8,2),$_SESSION['ID_prof'],$row_RsJour['matiere_ID'],$row_RsJour['classe_ID'],$row_RsJour['gic_ID'],$sql_groupe);
	
include "../inc/vue_du_jour_travail_inc.php" ;?>    </td>
  </tr>
  <?php
} while ($row_RsJour = mysqli_fetch_assoc($RsJour));
mysqli_free_result($RsJour);
};
};


?>
</table>
<?php
//fin affichage cellules heures sup




//debut affichage cellules devoirs planifies

if (isset($_GET['madate'])){
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsJour = sprintf("SELECT * FROM cdt_agenda,cdt_classe,cdt_matiere WHERE substring(code_date,9,1)=0 AND substring(code_date,1,8)=%s AND cdt_agenda.prof_ID=%u AND cdt_classe.ID_classe=cdt_agenda.classe_ID AND cdt_matiere.ID_matiere=cdt_agenda.matiere_ID AND cdt_agenda.type_activ='ds_prog' GROUP BY cdt_agenda.heure ORDER BY heure_debut",substr($_GET['madate'],0,4).substr($_GET['madate'],5,2).substr($_GET['madate'],8,2),$_SESSION['ID_prof']);

//echo $query_RsJour;
$RsJour = mysqli_query($conn_cahier_de_texte, $query_RsJour) or die(mysqli_error($conn_cahier_de_texte));
$row_RsJour = mysqli_fetch_assoc($RsJour);
$totalRows_RsJour = mysqli_num_rows($RsJour);
if ($totalRows_RsJour>0){
?>
<br />
<?php
do { 
      ?>
<table  width="95%" align="center" cellspacing="0"  class="lire_bordure">
  <tr class="Style1" >
    <td width="10%" class="tab_detail"><div  style="color: #FF0000"><?php echo $_SESSION['libelle_devoir'];?></div>
      <?php
 		   if ($row_RsJour['heure_debut'] <>''){ echo $row_RsJour['heure_debut'];} else {echo $row_RsJour['heure'];};
echo '<br />';?>
      </p>
      <?php
        
		 if ($row_RsJour['gic_ID']==0){echo '<br /><strong>'.$row_RsJour['nom_classe'].'</strong>';}
		 else{
$query_Rsgic_classe_ID_default =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u LIMIT 1",$row_RsJour['gic_ID']);
$Rsgic_classe_ID_default = mysqli_query($conn_cahier_de_texte, $query_Rsgic_classe_ID_default) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgic_classe_ID_default = mysqli_fetch_assoc($Rsgic_classe_ID_default);	 
echo '<br/><strong>'.$row_Rsgic_classe_ID_default['nom_gic'].' (R)</strong>';
		 };
		 
		 echo '<br/>'.$row_RsJour['groupe'].'<br/>'.$row_RsJour['nom_matiere'].'<br/>';
		  if ($row_RsJour['gic_ID']<>0){$row_RsJour['classe_ID']=0;};
?>
      <br />
    </td>
    <td width="25%" class="tab_detail"><?php
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_Travail_du_jour = sprintf("SELECT * FROM cdt_travail WHERE substring(t_jour_pointe,1,8)='%s' AND prof_ID=%u AND matiere_ID=%u AND classe_ID=%u %s ORDER BY code_date ", substr($_GET['madate'],0,4).substr($_GET['madate'],5,2).substr($_GET['madate'],8,2),$_SESSION['ID_prof'],$row_RsJour['matiere_ID'],$row_RsJour['classe_ID'],$sql_groupe);

include "../inc/vue_du_jour_travail_inc.php" ;?>    </td>
    <td width="35%" class="tab_detail"><?php 

  
  include "../inc/vue_du_jour_seance_inc.php" ;?>    </td>
    <td width="25%" class="tab_detail">&nbsp;</td>
  </tr>
  <?php
} while ($row_RsJour = mysqli_fetch_assoc($RsJour));
mysqli_free_result($RsJour);
};
};
?>
</table>
</div>
<?php
//fin affichage cellules devoirs planifies
?>
</body>
</html>
