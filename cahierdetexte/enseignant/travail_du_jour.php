<?php
//header('Content-Type: text/html; charset=ISO-8859-1');ini_set( 'default_charset', 'ISO-8859-1' ); 
//si probleme accent pour les données extraites de la base decommenter la ligne ci-dessous
mysqli_query($conn_cahier_de_texte, "SET NAMES latin1");
//Heure sup
//if ((!isset($_GET['ds_prog']))&&(substr($_GET['code_date'],8,1)==0)){$sup_ch="AND substring(code_date,9,1)=0 AND substring(t_code_date,3,1)='-'";};
//Heure normale


if ($_GET['classe_ID']==0){
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgic_classe_ID_default =sprintf("SELECT classe_ID FROM cdt_groupe_interclasses_classe WHERE gic_ID=%u LIMIT 1",$_GET['gic_ID']);
$Rsgic_classe_ID_default = mysqli_query($conn_cahier_de_texte, $query_Rsgic_classe_ID_default) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgic_classe_ID_default = mysqli_fetch_assoc($Rsgic_classe_ID_default);
$row_Rsgic_classe_ID_default['classe_ID'];
$classe_Rslisteactivite=$row_Rsgic_classe_ID_default['classe_ID'];} else {$classe_Rslisteactivite=$_GET['classe_ID'];}

if ($_GET['groupe']=='Classe entière'){$sql_groupe='';}
else { $sql_groupe="AND (groupe='Classe entière' OR groupe='".$_GET['groupe']."')";};
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);	
$query_Rs_Travail_du_jour = sprintf("SELECT * FROM cdt_travail WHERE t_jour_pointe=%s AND prof_ID=%u AND classe_ID=%u AND gic_ID=%u AND matiere_ID=%u %s ORDER BY code_date ", substr($_GET['code_date'],0,8),$_SESSION['ID_prof'],$classe_Rslisteactivite,$_GET['gic_ID'],$_GET['matiere_ID'],$sql_groupe);

//echo $query_Rs_Travail_du_jour;

 
$Rs_Travail_du_jour = mysqli_query($conn_cahier_de_texte, $query_Rs_Travail_du_jour) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_Travail_du_jour = mysqli_fetch_assoc($Rs_Travail_du_jour);
$totalRows_Rs_Travail_du_jour = mysqli_num_rows($Rs_Travail_du_jour);


if ($totalRows_Rs_Travail_du_jour>0){ 

echo ' <span class="Style699">';
do { 
?><script>
$("#d2").show();
</script>
<?php


echo '<u>'.$row_Rs_Travail_du_jour['t_groupe'].'</u> :&nbsp; '.$row_Rs_Travail_du_jour['travail'] ;

//affichage fichiers travail joints
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$sql_f="SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=".$row_Rs_Travail_du_jour['agenda_ID']." AND type ='Travail' AND t_code_date ='".$row_Rs_Travail_du_jour['t_code_date']."' AND ind_position = ".$row_Rs_Travail_du_jour['ind_position']." ORDER BY nom_fichier";
$query_Rs_fichiers_joints_form = $sql_f;

$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

 if ($totalRows_Rs_fichiers_joints_form<>0)
 { echo ' - Doc. joint(s) : ';
    do { ?>
<a style="a:font-style: italic; font-weight: normal;" href="../fichiers_joints/<?php echo $row_Rs_fichiers_joints_form['nom_fichier'];  ?> " target="_blank">
          <?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']); ?>
          <strong><?php echo $nom_f ;  ?></strong></a>
          <?php
				  } while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
}
mysqli_free_result($Rs_fichiers_joints_form);
//fin affichage des fichiers travail joints

echo '<br />';

} while ($row_Rs_Travail_du_jour = mysqli_fetch_assoc($Rs_Travail_du_jour));
}
else {echo 'Pas de travail programm&eacute; pour ce jour.';}
echo '</span>';

mysqli_free_result($Rs_Travail_du_jour);
?>
