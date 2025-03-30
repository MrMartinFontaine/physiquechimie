<?php

//Heure normale
if ($_GET['classe_ID']==0){$classe_Rslisteactivite=$row_Rsgic_classe_ID_default['classe_ID'];} else {$classe_Rslisteactivite=$_GET['classe_ID'];}

        if ($_GET['groupe']=='Classe entière'){$sql_groupe='';}
        else { $sql_groupe="AND (groupe='Classe entière' OR groupe='".$_GET['groupe']."')";};
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte); 
$query_Rs_Annot_du_jour = sprintf("SELECT rq,jour_pointe,groupe FROM cdt_agenda WHERE code_date< %s AND prof_ID=%u AND classe_ID=%u AND matiere_ID=%u %s ORDER BY code_date DESC LIMIT 3", $_GET['code_date'],$_SESSION['ID_prof'],$classe_Rslisteactivite,$_GET['matiere_ID'],$sql_groupe);

$Rs_Annot_du_jour = mysqli_query($conn_cahier_de_texte, $query_Rs_Annot_du_jour) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_Annot_du_jour = mysqli_fetch_assoc($Rs_Annot_du_jour);
$totalRows_Rs_Annot_du_jour = mysqli_num_rows($Rs_Annot_du_jour);
$nb_annot=0;
if ($totalRows_Rs_Annot_du_jour>0){ 

do {  
if ($row_Rs_Annot_du_jour['rq']<>''){echo '<u>'.$row_Rs_Annot_du_jour['jour_pointe'].'</u> -  '.$row_Rs_Annot_du_jour['groupe'].'<br />'.$row_Rs_Annot_du_jour['rq'].'<br /><br />';$nb_annot=$nb_annot+1;}

} while ($row_Rs_Annot_du_jour = mysqli_fetch_assoc($Rs_Annot_du_jour));

};
if( $nb_annot==0){echo "Aucune annotation sur les cours pr&eacute;c&eacute;dents. ";}
mysqli_free_result($Rs_Annot_du_jour);
?>
