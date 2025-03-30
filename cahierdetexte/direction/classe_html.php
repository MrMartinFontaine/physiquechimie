<?php
// Adaptation temporaire pour palier aux problèmes de creation de pdf

session_start();
if ($_SESSION['droits']<>4) { header("Location: ../index.php");exit;};
//if (isset($_SESSION['nom_prof'])){$_SESSION['consultation']=$_GET['classe_ID'];};
//if (!isset($_SESSION['consultation'])OR ($_SESSION['consultation']<>$_GET['classe_ID'])){  header("Location: index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

ob_start();
?>
<style type="text/css">
<!--
td
{
padding: 5px;
border: 1px solid #CCCCCC;
font-family: Arial, Helvetica, sans-serif;
font-size: 8px;
vertical-align: top;
}
-->
</style>

<?php
$etablissement = $_SESSION['nom_etab'];
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE cdt_classe.ID_classe=%u ",intval($_GET['classe_ID']));
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);

$classe_Rslisteactivite = "0";
if (isset($_GET['classe_ID'])) {
  $classe_Rslisteactivite = (get_magic_quotes_gpc()) ? intval($_GET['classe_ID']) : addslashes(intval($_GET['classe_ID']));
}

$date1=$_GET['date1'];
$date2=$_GET['date2'];
$ordre=$_GET['ordre'];

$today=date('Ymd').'9'; $today_form=date('j/m/Y');
   
$date1_form=substr($date1,6,2).'/'.substr($date1,4,2).'/'.substr($date1,0,4);
$date2_form=substr($date2,6,2).'/'.substr($date2,4,2).'/'.substr($date2,0,4);

$titre1=$row_RsClasse['nom_classe']. '  -  ';
$titre1.='P&eacute;riode du '.$date1_form.' au '.$date2_form;
$titre1.='<br>'.$etablissement;
?>
<!-- Bandeau de classe  -->
<table  style="width: 100%;padding: 5px" cellspacing="0" cellpadding="0">
  <tr>
    <td style="width: 100%;text-align: center"><?php echo '<H3>Cahier de textes<br>'.$titre1.'</H3>';?></td>
  </tr>
</table>
<!-- Fin bandeau de classe  -->
<?php

// Balayage des matières du cahier de textes pour la classe
$query_ListeMatiere = "SELECT DISTINCT matiere_ID FROM cdt_agenda WHERE classe_ID = $classe_Rslisteactivite ORDER BY matiere_id";
$ListeMatiere = mysqli_query($conn_cahier_de_texte, $query_ListeMatiere) or die(mysqli_error($conn_cahier_de_texte));
$row_ListeMatiere = mysqli_fetch_assoc($ListeMatiere);
$totalRows_ListeMatiere = mysqli_num_rows($ListeMatiere);
// Boucle de cahier de textes par matières
do {
$matiere_Rslisteactivite = $row_ListeMatiere['matiere_ID'];

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

if (isset($_GET['groupe'])){
	if ($_GET['groupe']=='Classe entière'){$sql_groupe='';}
	else { $sql_groupe="AND (cdt_agenda.groupe='Classe entière' OR cdt_agenda.groupe='".$_GET['groupe']."')";};
}
else {$sql_groupe='';};

if ($ordre=='down'){ 
$query_Rslisteactivite = sprintf("
SELECT *
FROM cdt_agenda
LEFT JOIN cdt_prof ON cdt_agenda.prof_ID = cdt_prof.ID_prof
WHERE (
cdt_agenda.classe_ID=%u
AND cdt_agenda.matiere_ID=%u
AND cdt_agenda.code_date >= '%s'
AND cdt_agenda.code_date <= '%s' 
%s %s
)
OR (
cdt_agenda.classe_ID =0
AND cdt_agenda.code_date >= '%s'
AND cdt_agenda.code_date <= '%s'
)
ORDER BY cdt_agenda.code_date DESC", $classe_Rslisteactivite,$matiere_Rslisteactivite,$date1,$date2,$sql_groupe,$date1,$date2);

}
else
{

$query_Rslisteactivite = sprintf("
SELECT *
FROM cdt_agenda
LEFT JOIN cdt_prof ON cdt_agenda.prof_ID = cdt_prof.ID_prof
WHERE (
cdt_agenda.classe_ID=%u
AND cdt_agenda.matiere_ID=%u
AND cdt_agenda.code_date >= '%s'
AND cdt_agenda.code_date <= '%s' 
%s
)
OR (
cdt_agenda.classe_ID =0
AND cdt_agenda.code_date >= '%s'
AND cdt_agenda.code_date <= '%s'
)
ORDER BY cdt_agenda.code_date ASC", $classe_Rslisteactivite,$matiere_Rslisteactivite,$date1,$date2,$sql_groupe,$date1,$date2);
}
$Rslisteactivite = mysqli_query($conn_cahier_de_texte, $query_Rslisteactivite) or die(mysqli_error($conn_cahier_de_texte));
$row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite);
$totalRows_Rslisteactivite = mysqli_num_rows($Rslisteactivite);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMat = sprintf("SELECT * FROM cdt_matiere WHERE cdt_matiere.ID_matiere=%s ",intval($matiere_Rslisteactivite));
$RsMat = mysqli_query($conn_cahier_de_texte, $query_RsMat) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMat = mysqli_fetch_assoc($RsMat);
$totalRows_RsMat = mysqli_num_rows($RsMat);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsNomprof = sprintf("SELECT cdt_emploi_du_temps.prof_ID,cdt_emploi_du_temps.matiere_ID,cdt_emploi_du_temps.classe_ID,cdt_prof.nom_prof,cdt_prof.identite FROM cdt_emploi_du_temps,cdt_prof WHERE cdt_prof.ID_prof=cdt_emploi_du_temps.prof_ID
AND cdt_emploi_du_temps.matiere_ID=%u AND cdt_emploi_du_temps.classe_ID=%u
",intval($matiere_Rslisteactivite),intval($_GET['classe_ID']));
$RsNomprof = mysqli_query($conn_cahier_de_texte, $query_RsNomprof) or die(mysqli_error($conn_cahier_de_texte));
$row_RsNomprof = mysqli_fetch_assoc($RsNomprof);

$titre = "";
// Déclaration sur la matière
echo '<bookmark title="'.$row_RsMat['nom_matiere'].'" level="0">';
if (isset($_GET['groupe'])){$titre.=$_GET['groupe']. '  -  ';} else {$titre.='Classe entière'. '  -  ';};
$titre.=$row_RsMat['nom_matiere'];
$titre.='  -  '.$row_RsNomprof['identite'];
?>
</bookmark>

<table  style="width: 100%;padding: 5px" cellspacing="0" cellpadding="0">

  <tr>
    <td style="width: 100%;text-align: center"><?php echo "<b><H5>".$titre."</H5></b>";?></td>
  </tr>
</table>

<?php
if ($totalRows_Rslisteactivite  >0){
	  
	  do { 
	  
$c1l1='';$c2l1='';$c3l1='';
$c1l2='';$c2l2='';$c3l2='';
	  
if($row_Rslisteactivite['classe_ID']<>0){  	 

$query_Rs_Travail2 = sprintf("SELECT * FROM cdt_travail WHERE cdt_travail.code_date='%s' AND cdt_travail.matiere_ID=%u AND cdt_travail.classe_ID=%u AND cdt_travail.agenda_ID=%u  ORDER BY cdt_travail.code_date", $row_Rslisteactivite['code_date'],$matiere_Rslisteactivite,$_GET['classe_ID'],$row_Rslisteactivite['ID_agenda']);
$Rs_Travail2 = mysqli_query($conn_cahier_de_texte, $query_Rs_Travail2) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_Travail2 = mysqli_fetch_assoc($Rs_Travail2);
$totalRows_Rs_Travail2 = mysqli_num_rows($Rs_Travail2);

$date_a_faire[1]='';$date_a_faire[2]='';$date_a_faire[3]='';$date_a_faire[4]='';
$travail[1]='';$travail[2]='';$travail[3]='';$travail[4]='';
$t_groupe[1]='';$t_groupe[2]='';$t_groupe[3]='';$t_groupe[4]='';
do {  
 
$travail[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['travail'];
$date_a_faire[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['t_code_date'];
$t_groupe[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['t_groupe'];

} while ($row_Rs_Travail2 = mysqli_fetch_assoc($Rs_Travail2));

if ($row_Rslisteactivite['semaine']<>'A et B'){ $c1l2.='('. $row_Rslisteactivite['semaine'].') ';};

$c1l2.= $row_Rslisteactivite['groupe']."<br>";
$c1l2.=$row_Rslisteactivite['heure_debut'];
if ($row_Rslisteactivite['heure_fin']<>''){$c1l2.='-'.$row_Rslisteactivite['heure_fin'];}
$c1l2.="<br>";
if ($row_Rslisteactivite['duree']<>''){$c1l2.= '('.$row_Rslisteactivite['duree'].')';$c1l2.="<br>";};

if ($row_Rslisteactivite['type_activ']<>'ds_prog'){$c1l2.= $row_Rslisteactivite['type_activ'];
  if (substr($row_Rslisteactivite['code_date'],8,1)==0) {$c1l2.='Heure Sup.';};
   }
   else {   $c1l2.=$_SESSION['libelle_devoir'];}; 
$c1l2.="<br>";

$c2l2 =$row_Rslisteactivite['activite'];

if (isset($row_RsNomprof['nom_prof'])){  if (($row_Rslisteactivite['rq']<>'')&&(isset($_GET['annot']))){  $c2l2.="<br> Rq : ".$row_Rslisteactivite['rq']	  ;}};
// affichage fichiers joints seance
				
				$refagenda_RsFichiers = "0";
				if (isset($row_Rslisteactivite['ID_agenda'])) {
				$refagenda_RsFichiers = (get_magic_quotes_gpc()) ? 
				$row_Rslisteactivite['ID_agenda'] : addslashes($row_Rslisteactivite['ID_agenda']);
				}
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsFichiers = sprintf("SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=%u  AND cdt_fichiers_joints.type<>'Travail'", $refagenda_RsFichiers);
				$RsFichiers = mysqli_query($conn_cahier_de_texte, $query_RsFichiers) or die(mysqli_error($conn_cahier_de_texte));
				$row_RsFichiers = mysqli_fetch_assoc($RsFichiers);
				$totalRows_RsFichiers = mysqli_num_rows($RsFichiers);
	 if ($totalRows_RsFichiers<>0){$c1l2.= "<br>Document(s) Cours <br>";};
do { 

$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_RsFichiers['nom_fichier']); 

            $c1l2.="<a href=\"../fichiers_joints/".$row_RsFichiers['nom_fichier']."\">".$nom_f."</a><br>";      
} while ($row_RsFichiers = mysqli_fetch_assoc($RsFichiers)); 
mysqli_free_result($RsFichiers);

//fin affichage fichiers joints seance

if ( $date_a_faire[1]<>''){
$c3l2.= $t_groupe[1].' pour le '.jour_semaine($date_a_faire[1]).' '.$date_a_faire[1]." <br>";

//affichage fichiers travail joints
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$sql_f="SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND cdt_fichiers_joints.type ='Travail' AND cdt_fichiers_joints.t_code_date ='".$date_a_faire[1]."' AND cdt_fichiers_joints.ind_position = 1 ORDER BY cdt_fichiers_joints.nom_fichier";
$query_Rs_fichiers_joints_form = $sql_f;
$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

 if ($totalRows_Rs_fichiers_joints_form<>0)
 {
 	if ($totalRows_Rs_fichiers_joints_form==1)
 		{$c3l2.= 'avec le document ';}
 	else
 		{$c3l2.='avec les documents ';};
    do { 

          $exp = "/^[0-9]+_/";
          $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
$c3l2.= "<a href=\"../fichiers_joints/".$row_Rs_fichiers_joints_form['nom_fichier']."\">".$nom_f."</a><br>";              
				  } while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
}
mysqli_free_result($Rs_fichiers_joints_form);
//fin affichage des fichiers travail joints

$c3l2 .=$travail[1];

}

if ( $date_a_faire[2]<>''){
$c3l2.= "<br> <br>".$t_groupe[2].' pour le '.jour_semaine($date_a_faire[2]).' '.$date_a_faire[2]."<br>";

//affichage fichiers travail joints
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$sql_f="SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND cdt_fichiers_joints.type ='Travail' AND cdt_fichiers_joints.t_code_date ='".$date_a_faire[2]."' AND cdt_fichiers_joints.ind_position = 2 ORDER BY cdt_fichiers_joints.nom_fichier";
$query_Rs_fichiers_joints_form = $sql_f;

$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

 if ($totalRows_Rs_fichiers_joints_form<>0)
 {
 	if ($totalRows_Rs_fichiers_joints_form==1)
 		{$c3l2.='avec le document ';}
 	else
 		{$c3l2.= 'avec les documents ';};
    do {
          $exp = "/^[0-9]+_/";
          $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
$c3l2.= "<a href=\"../fichiers_joints/".$row_Rs_fichiers_joints_form['nom_fichier']."\">".$nom_f."</a><br>";                
				  } while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
}
mysqli_free_result($Rs_fichiers_joints_form);
//fin affichage des fichiers travail joints
$c3l2 .= $travail[2];
}

if ( $date_a_faire[3]<>''){
$c3l2.=  "<br> <br>".$t_groupe[3].' pour le '.jour_semaine($date_a_faire[3]).' '.$date_a_faire[3]."<br>";
//affichage fichiers travail joints
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$sql_f="SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=".$row_Rslisteactivite['ID_agenda']." AND cdt_fichiers_joints.type ='Travail' AND cdt_fichiers_joints.t_code_date ='".$date_a_faire[3]."' AND cdt_fichiers_joints.ind_position = 3  ORDER BY cdt_fichiers_joints.nom_fichier";
$query_Rs_fichiers_joints_form = $sql_f;

$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

 if ($totalRows_Rs_fichiers_joints_form<>0)
 {
 	if ($totalRows_Rs_fichiers_joints_form==1)
 		{$c3l2.= 'avec le document ';}
 	else
 		{$c3l2.='avec les documents ';};
    do {
          
          $exp = "/^[0-9]+_/";
          $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
$c3l2.= "<a href=\"../fichiers_joints/".$row_Rs_fichiers_joints_form['nom_fichier']."\">".$nom_f."</a><br>";          
          
				  } while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
}
mysqli_free_result($Rs_fichiers_joints_form);
//fin affichage des fichiers travail joints
$c3l2 .= $travail[3]."<br>";
}

$c1l1=substr($row_Rslisteactivite['jour_pointe'],0,strlen($row_Rslisteactivite['jour_pointe'])-4);

if (isset($row_Rslisteactivite['theme_activ'])){$c2l1.= $row_Rslisteactivite['theme_activ'] ;}
if ((isset($totalRows_Rs_Travail2))&&($totalRows_Rs_Travail2>0)){$c3l1.='A faire';}

?>

<table  style="width: 100%;border-collapse: collapse;padding: 5px;" cellspacing="0" cellpadding="0">
  <tr>
    <td style="text-align: left;	width: 15%"><?php echo $c1l1;?></td>
    <td style="text-align: left;	width: 55%"><?php echo $c2l1;?></td>
    <td style="text-align: left;	width: 30%"><?php echo $c3l1;?></td>
  </tr>
  <tr>
    <td style="text-align: left;	width: 15%"><?php echo $c1l2;?></td>
    <td style="text-align: left;	width: 55%"><?php echo $c2l2;?></td>
    <td style="text-align: left;	width: 30%"><?php echo $c3l2;?></td>
  </tr>
</table>

<?php	  

}
  else {
  
  ?>
 <table  style="width: 100%;padding: 5px;" cellspacing="0" cellpadding="0">
    <tr>
      <td style="text-align: center; width: 100%;" ><?php echo $row_Rslisteactivite['heure_debut'].' au '.$row_Rslisteactivite['heure_fin'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row_Rslisteactivite['theme_activ']; ?> </td>
    </tr>
</table>


<?php
 };
  } while ($row_Rslisteactivite = mysqli_fetch_assoc($Rslisteactivite)); 
  }

?>




<?php
}while($row_ListeMatiere = mysqli_fetch_assoc($ListeMatiere)); 
// Fin boucle de cahier de textes par matières
?>



<table  style="width: 100%;padding: 5px" cellspacing="0" cellpadding="0">
  <tr>
  <td style="width: 100%;text-align: center"><?php echo '<H3>Cl&ocirc;ture cahier de textes<br>'.$titre1.'</H3>';?></td>
  </tr>
</table>

<?php
$content = ob_get_clean(); 
echo $content; 

mysqli_free_result($RsNomprof);
if (isset($Rs_Travail2)){mysqli_free_result($Rs_Travail2);};
if (isset($Rsgroupe)){mysqli_free_result($Rsgroupe);};
mysqli_free_result($Rslisteactivite);
mysqli_free_result($RsMat);
mysqli_free_result($ListeMatiere);
mysqli_free_result($RsClasse);
?>