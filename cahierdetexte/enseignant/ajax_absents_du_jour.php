<?php
session_start();
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;}
else {header("Content-Type: text/plain; charset=ISO-8859-1");};
require_once('../Connections/conn_cahier_de_texte.php'); 


//Heure normale
if ($_POST['classe_ID']==0){
 // $classe_Rslisteactivite=$row_Rsgic_classe_ID_default['classe_ID'];
  $classe_Rslisteactivite=$_POST['classe_ID'];
  $sql_classe_gic = " AND cdt_agenda.gic_ID = '". $_POST['gic_ID']."' ";
} else {
    $classe_Rslisteactivite=$_POST['classe_ID'];
    $sql_classe_gic = " AND cdt_agenda.classe_ID = '".$_POST['classe_ID']."' ";
}



mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);	


//on prend meme matiere mais pas obligatoirement la meme heure
//en l'etat on ne connait pas le groupe des eleves donc pas de groupes ds la requete

$query_Rs_Absent_du_jour = sprintf("SELECT distinct code_date FROM cdt_agenda  WHERE cdt_agenda.code_date< %s AND cdt_agenda.prof_ID=%u %s AND cdt_agenda.matiere_ID=%u ORDER BY cdt_agenda.code_date DESC LIMIT 4", $_POST['code_date'],$_SESSION['ID_prof'],$sql_classe_gic,$_POST['matiere_ID']);
// print $query_Rs_Absent_du_jour . "<br />";

$Rs_Absent_du_jour = mysqli_query($conn_cahier_de_texte, $query_Rs_Absent_du_jour) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_Absent_du_jour = mysqli_fetch_assoc($Rs_Absent_du_jour);
$totalRows_Rs_Absent_du_jour = mysqli_num_rows($Rs_Absent_du_jour);

if ($totalRows_Rs_Absent_du_jour>0){
  
  
  $code_date_array = array();
   //array_push( $code_date_array , 0 );
  @mysqli_data_seek($Rs_Absent_du_jour,0) ;
  while (($row_rq = mysqli_fetch_array($Rs_Absent_du_jour , MYSQLI_ASSOC) )) {
    //if ($_POST['classe_ID']==0) { // Gestion des GIC et de l'heure de cours fictive 9
    //  $my_code_date =substr($row_rq['code_date'],0,8) . "9";
    //} else {
      $my_code_date = $row_rq['code_date'] ;
    //}
    
    array_push( $code_date_array , $my_code_date  );
  }
  $in_code_date_array = join(" ,", $code_date_array) ;

//on prend meme matiere mais pas obligatoirement la meme heure
  $sub_query_Rs_Absent_du_jour = sprintf("SELECT * FROM ele_absent, ele_liste  WHERE ele_absent.eleve_ID = ele_liste.ID_ele AND prof_ID=%u AND classe_ID=%u  AND code_date IN (%s) ORDER BY code_date,nom_ele,prenom_ele", $_SESSION['ID_prof'],$classe_Rslisteactivite,$in_code_date_array);
   // print $sub_query_Rs_Absent_du_jour . "<br />";

$Rs_sub_Absent_du_jour = mysqli_query($conn_cahier_de_texte, $sub_query_Rs_Absent_du_jour) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_sub_Absent_du_jour = mysqli_fetch_assoc($Rs_sub_Absent_du_jour);
$totalRows_Rs_sub_Absent_du_jour = mysqli_num_rows($Rs_sub_Absent_du_jour);


$nb_absents=0;  


if ($totalRows_Rs_sub_Absent_du_jour>0){
  $previous_code_date = "";
  $ul_open = false;
   do {
      
      if  ($previous_code_date != $row_Rs_sub_Absent_du_jour['code_date'] ) {
		if ($ul_open) { echo "</ul>"; }
		echo '<u>'.htmlentities(urldecode($row_Rs_sub_Absent_du_jour['jour_pointe'])).' &agrave; '.urldecode($row_Rs_sub_Absent_du_jour['heure_debut']).'&nbsp;('.htmlentities($row_Rs_sub_Absent_du_jour['groupe']).')</u><ul>' ;
		$ul_open = true;	  
      } 
      
      $previous_code_date = $row_Rs_sub_Absent_du_jour['code_date'];
      echo '<li><div style="float:left;display:inline;';
	  if (($row_Rs_sub_Absent_du_jour['retard']=='N')&&($row_Rs_sub_Absent_du_jour['perso1']=='N')&&($row_Rs_sub_Absent_du_jour['perso2']=='N')&&($row_Rs_sub_Absent_du_jour['perso3']=='N')){	//absent
	  	echo 'color: #FF1063;font-weight: bold;';
	  };	  
	  echo '">' .$row_Rs_sub_Absent_du_jour['nom_ele']. "&nbsp; ". $row_Rs_sub_Absent_du_jour['prenom_ele'].'&nbsp;&nbsp;</div>';
	  
	  if ($row_Rs_sub_Absent_du_jour['retard']=='O'){
			echo '<div style="color: #339966;font-weight: bold;display:inline;">&nbsp;Retard';
			if ($row_Rs_sub_Absent_du_jour['motif']<>''){echo '&nbsp;&nbsp;'.$row_Rs_sub_Absent_du_jour['motif'];};
			echo '</div>';
			};
      
	  if (($row_Rs_sub_Absent_du_jour['retard']=='N')&&($row_Rs_sub_Absent_du_jour['perso1']=='N')&&($row_Rs_sub_Absent_du_jour['perso2']=='N')&&($row_Rs_sub_Absent_du_jour['perso3']=='N')){	//absent
			echo '<div style="color: #FF1063;font-weight: bold;">&nbsp;Absent</div>';
			}; 
	  if ($row_Rs_sub_Absent_du_jour['perso1']=='O'){
			echo '<div style="color: #339966;font-weight: bold;display:inline;">&nbsp;';
			echo 'Oubli Carnet</div>';
			};
	  if ($row_Rs_sub_Absent_du_jour['perso2']=='O'){
			echo '<div style="color: #339966;font-weight: bold;display:inline;">&nbsp;';
			echo 'Oubli Mat&eacute;riel</div>';
			};

			
			
			
			
	  echo "</li>" ;
     $nb_absents++;    
    
   } while ($row_Rs_sub_Absent_du_jour = mysqli_fetch_assoc($Rs_sub_Absent_du_jour));
};
if($nb_absents==0){echo "Aucune absence sur les cours pr&eacute;c&eacute;dents. ";};
mysqli_free_result($Rs_sub_Absent_du_jour);
}
else {echo "Aucune absence sur les cours pr&eacute;c&eacute;dents. ";};

mysqli_free_result($Rs_Absent_du_jour);

?>
