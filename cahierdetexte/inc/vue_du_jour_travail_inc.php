<?php

$Rs_Travail_du_jour = mysqli_query($conn_cahier_de_texte, $query_Rs_Travail_du_jour) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_Travail_du_jour = mysqli_fetch_assoc($Rs_Travail_du_jour);
$totalRows_Rs_Travail_du_jour = mysqli_num_rows($Rs_Travail_du_jour);

if ($totalRows_Rs_Travail_du_jour>0){ 
do { 
$date_a_faire_jour[1]='';$date_a_faire_jour[2]='';$date_a_faire_jour[3]='';
$travail_du_jour[1]='';$travail_du_jour[2]='';$travail_du_jour[3]='';
$t_groupe_jour[1]='';$t_groupe_jour[2]='';$t_groupe_jour[3]='';

 
$travail_du_jour[$row_Rs_Travail_du_jour['ind_position']]=$row_Rs_Travail_du_jour['travail'];
$date_a_faire_jour[$row_Rs_Travail_du_jour['ind_position']]=$row_Rs_Travail_du_jour['t_code_date'];
$t_groupe_jour[$row_Rs_Travail_du_jour['ind_position']]=$row_Rs_Travail_du_jour['t_groupe'];
$id_agenda_jour[$row_Rs_Travail_du_jour['ind_position']]=$row_Rs_Travail_du_jour['agenda_ID'];




$date_f=substr($_GET['madate'],0,4).substr($_GET['madate'],5,2).substr($_GET['madate'],8,2);
echo ' <span class="Style699">';

if ( $date_a_faire_jour[1]<>''){

if (substr($date_a_faire_jour[1],6,4).substr($date_a_faire_jour[1],3,2).substr($date_a_faire_jour[1],0,2)<>$date_f){
echo '<u>Pour le <b>'.jour_semaine($date_a_faire_jour[1]).' '.$date_a_faire_jour[1].'</b>&nbsp;-&nbsp;</u>';}
echo '<u><i>'. $t_groupe_jour[1].'</i></u> :<br />'.$travail_du_jour[1] ;

//affichage fichiers travail joints
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$sql_f="SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=".$id_agenda_jour[1]." AND cdt_fichiers_joints.type ='Travail' AND cdt_fichiers_joints.t_code_date ='".$date_a_faire_jour[1]."' AND cdt_fichiers_joints.ind_position = 1 ORDER BY cdt_fichiers_joints.nom_fichier";
$query_Rs_fichiers_joints_form = $sql_f;

$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

 if ($totalRows_Rs_fichiers_joints_form<>0)
 { echo ' - Doc. joint(s) : ';
    do { ?>
        <a style="a:font-style: italic;	font-weight: normal;" href="../fichiers_joints/<?php echo $row_Rs_fichiers_joints_form['nom_fichier'];  ?> " target="_blank">
        <?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']); ?>
        <strong><?php echo $nom_f ;  ?></strong></a>
        <?php
				  } while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
}
mysqli_free_result($Rs_fichiers_joints_form);
//fin affichage des fichiers travail joints


}


if ( $date_a_faire_jour[2]<>''){echo '<br />';
if (substr($date_a_faire_jour[1],6,4).substr($date_a_faire_jour[2],3,2).substr($date_a_faire_jour[2],0,2)<>$date_f){
echo '<u>Pour le <b>'.jour_semaine($date_a_faire_jour[2]).' '.$date_a_faire_jour[2].'</b>&nbsp;-&nbsp;</u>';}
echo '<u><i>'. $t_groupe_jour[2].'</i></u> :<br />'.$travail_du_jour[2] ;
//affichage fichiers travail joints
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$sql_f="SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=".$id_agenda_jour[2]." AND cdt_fichiers_joints.type ='Travail' AND cdt_fichiers_joints.t_code_date ='".$date_a_faire_jour[2]."' AND cdt_fichiers_joints.ind_position = 2 ORDER BY cdt_fichiers_joints.nom_fichier";
$query_Rs_fichiers_joints_form = $sql_f;

$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

 if ($totalRows_Rs_fichiers_joints_form<>0)
  { echo ' - Doc. joint(s) : ';
    do { ?>
      <a href="../fichiers_joints/<?php echo $row_Rs_fichiers_joints_form['nom_fichier'];  ?> " target="_blank">
      <?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']); ?>
      <strong><?php echo $nom_f ;  ?></strong></a>
      <?php
				  } while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
}
mysqli_free_result($Rs_fichiers_joints_form);
//fin affichage des fichiers travail joints


}

if ( $date_a_faire_jour[3]<>''){echo '<br />';
if (substr($date_a_faire_jour[3],6,4).substr($date_a_faire_jour[1],3,2).substr($date_a_faire_jour[3],0,2)<>$date_f){
echo '<u>Pour le <b>'.jour_semaine($date_a_faire_jour[3]).' '.$date_a_faire_jour[3].'</b>&nbsp;-&nbsp;</u>';}
echo '<u><i>'. $t_groupe_jour[3].'</i></u> :<br />'.$travail_du_jour[3] ;

//affichage fichiers travail joints
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$sql_f="SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=".$id_agenda_jour[3]." AND cdt_fichiers_joints.type ='Travail' AND cdt_fichiers_joints.t_code_date ='".$date_a_faire_jour[3]."' AND cdt_fichiers_joints.ind_position = 3  ORDER BY cdt_fichiers_joints.nom_fichier";
$query_Rs_fichiers_joints_form = $sql_f;

$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);

 if ($totalRows_Rs_fichiers_joints_form<>0)
 { echo ' - Doc. joint(s) : ';
    do { ?>
      <a href="../fichiers_joints/<?php echo $row_Rs_fichiers_joints_form['nom_fichier'];  ?> " target="_blank">
      <?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']); ?>
      <strong><?php echo $nom_f ;  ?></strong></a>
      <?php
				  } while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
}
mysqli_free_result($Rs_fichiers_joints_form);
//fin affichage des fichiers travail joints


}
echo '</span><br /><br />';
} while ($row_Rs_Travail_du_jour = mysqli_fetch_assoc($Rs_Travail_du_jour));


}
//else {echo 'Pas de travail programmé pour ce jour.';}




?>