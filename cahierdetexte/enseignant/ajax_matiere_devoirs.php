<?php
// affichage des matières du prof pour la classe postée et des devoirs dejà donnés par les collègues
header("Content-Type:text/plain; charset=iso-8859-1");
session_start();
require_once('../Connections/conn_cahier_de_texte.php');
if (substr($_POST["Classe"],0,1)==0){ // c'est un regroupement
$gic_ID=substr($_POST["Classe"],1,strlen($_POST["Classe"]));}else{$gic_ID=0;};

if($gic_ID==0) {
	
	require_once('../Connections/conn_cahier_de_texte.php');
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$rq2="SELECT cdt_matiere.nom_matiere,cdt_prof.nom_prof,cdt_agenda.heure_debut,cdt_agenda.heure_fin,cdt_agenda.duree FROM cdt_travail,cdt_matiere,cdt_prof,cdt_agenda WHERE cdt_travail.matiere_ID= cdt_matiere.ID_matiere AND  cdt_travail.prof_ID= cdt_prof.ID_prof AND cdt_travail.classe_ID=".$_POST["Classe"]." AND  cdt_travail.agenda_ID=cdt_agenda.ID_agenda   AND cdt_travail.code_date =".$_GET['code_date']." AND cdt_travail.code_date LIKE '%0' ORDER BY cdt_travail.matiere_ID";	
	
	
	$Rs = mysqli_query($conn_cahier_de_texte, $rq2) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rs = mysqli_fetch_assoc($Rs);
	$totalRows_Rs = mysqli_num_rows($Rs);
	if ($totalRows_Rs>0){
		echo '<br/>Devoir(s) d&eacute;j&agrave; donn&eacute;(s) dans cette classe ce m&ecirc;me jour en ';	
		do {echo '<br/>'.$row_Rs['nom_matiere'].' par '.$row_Rs['nom_prof'].' - '. $row_Rs['heure_debut'].' - '.$row_Rs['heure_fin'].' - ('. $row_Rs['duree'].')';}
		while($row_Rs = mysqli_fetch_assoc($Rs));		
	};	
	echo '<br/><a style="font-size:11px" href="../planning.php?classe_ID='.$_POST["Classe"].'&code_date='.$_GET["code_date"].'&jour_pointe='.$_GET["jour_pointe"].'&current_day_name='.$_GET["current_day_name"].'" target="_blank">Voir le planning mensuel du travail donn&eacute;</a>';
	
};	
echo "<br /><br />";



if(isset($_POST["Classe"])){
	if ( $gic_ID==0){
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$rq=mysqli_query($conn_cahier_de_texte, "SELECT DISTINCT cdt_matiere.nom_matiere,cdt_emploi_du_temps.matiere_ID FROM cdt_matiere LEFT JOIN cdt_emploi_du_temps ON cdt_matiere.ID_matiere=cdt_emploi_du_temps.matiere_ID WHERE cdt_emploi_du_temps.classe_ID=".$_POST["Classe"]." AND prof_ID= ".$_SESSION['ID_prof']. " ORDER BY matiere_ID");
		echo "<select name='matiere_ID'>";
		echo "<option value='value2'>maintenant choisir la mati&egrave;re</option>";
		while($row = mysqli_fetch_row($rq)){
			echo "<option value='".$row[1]."'";							    
			if (mysqli_num_rows($rq)==1) {echo " selected='selected'";}
			echo ">".$row[0]."</option>";
		};
		echo "</select>";
	}
	else //regroupement
	{
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$rq=mysqli_query($conn_cahier_de_texte, "SELECT DISTINCT cdt_matiere.nom_matiere,cdt_emploi_du_temps.matiere_ID FROM cdt_matiere LEFT JOIN cdt_emploi_du_temps ON cdt_matiere.ID_matiere=cdt_emploi_du_temps.matiere_ID WHERE cdt_emploi_du_temps.classe_ID=0 AND  cdt_emploi_du_temps.gic_ID=".$gic_ID." AND prof_ID= ".$_SESSION['ID_prof']. " ORDER BY matiere_ID");
		echo "<select name='matiere_ID'>";
		echo "<option value='value2'>maintenant choisir la mati&egrave;re</option>";
		while($row = mysqli_fetch_row($rq)){
			echo "<option value='".$row[1]."'";							    
			if (mysqli_num_rows($rq)==1) {echo " selected='selected'";}
			echo ">".$row[0]."</option>";
		};
		echo "</select>";
	};
	
	
}

?>

