<?php
//traitement ajax (voir ajax_functions.js) pour affichage dans une fenêtre quand un enseignant rempli son cahier de textes (ecrire.php)
//retrouver les devoirs deja donnes a une classe et a une certaine date
session_start();
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;}
else {header("Content-Type:text/plain; charset=iso-8859-1");};


if(isset($_POST['date'])&&($_POST['date']!=''))
{
	require_once('../inc/functions_inc.php');
	require_once('../Connections/conn_cahier_de_texte.php');
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	
	$date = GetSQLValueString($_POST['date'],"text");
	$t_jour_pointe = substr($date,7,4).substr($date,4,2).substr($date,1,2);
	
	$classe = GetSQLValueString($_POST['classe'],"int");
	$query_devoirs =mysqli_query($conn_cahier_de_texte, "SELECT travail, identite, nom_matiere, groupe, charge ,code_date  FROM cdt_travail, cdt_prof, cdt_matiere WHERE matiere_ID=ID_matiere AND prof_ID=ID_prof  AND classe_ID=$classe AND t_jour_pointe=$t_jour_pointe;" );
	$rep = 0;
	
	if(mysqli_num_rows($query_devoirs)>0)
	{
		while($row_devoirs = mysqli_fetch_assoc($query_devoirs))
		{
			if($rep==0) {
			echo "Cette classe a d\351j\340 du travail pour le ".jour_semaine($_POST['date'])." ".$_POST['date']." :\n";$rep=1;}
			echo "\n* ".$row_devoirs['nom_matiere'];
			if (substr($row_devoirs['code_date'],8,1)==0){echo " - ".$_SESSION['libelle_devoir']. " - ";};
			echo " (".$row_devoirs['groupe'].") - ".$row_devoirs['identite'];
			if ($row_devoirs['charge']!='') {echo " - " .$row_devoirs['charge'];};
			echo "\n ".substr(trim(html_entity_decode(strip_tags($row_devoirs['travail']), ENT_QUOTES)),0,100)." ...";
			echo "\n ";
		}
	}
	else echo "Cette classe n'a encore aucun travail programm\351 pour le ".jour_semaine($_POST['date'])." ".$_POST['date'].".";
}
else echo "Veuillez s\351lectionner une date avant d'effectuer cette v\351rification.";
?>


