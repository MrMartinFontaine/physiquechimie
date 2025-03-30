<?php
// affichage des regroupements du prof 
header("Content-Type:text/plain; charset=iso-8859-1");
session_start();

if ($_POST['Classe']==0){
	require_once('../Connections/conn_cahier_de_texte.php');
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE cdt_groupe_interclasses.prof_ID = %u ",$_SESSION['ID_prof']);
	$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsgic = mysqli_fetch_assoc($Rsgic);
	$totalrows_Rsgic=mysqli_num_rows($Rsgic);
	
	if ($totalrows_Rsgic==0){echo '<span class=\'erreur\'>(Pas de regroupement d&eacute;fini)<span>';} else
	{ 
		echo "<select name='gic_ID'>";
			do { 
				echo "<option value='".$row_Rsgic['ID_gic']."'>".$row_Rsgic['nom_gic']."</option>";
			} while ($row_Rsgic = mysqli_fetch_assoc($Rsgic));
	
		echo "</select>";
		// il faudrait verifier qu'une matiere principale puisse accueillir ces contenus de regroupements
		// si la matiere n'existe pas, en affichage eleve, si case cochee, le contenu du regroupement n'apparait pas !
		echo '<br /><br />Fusionner &agrave; l\'affichage les contenus de ce regroupement avec les autres contenus de la mati&egrave;re&nbsp;<input type="checkbox" name="fusion_gic" id="fusion_gic" value="" ><br />';
		}
} else {
	echo '<input name="cl" type="hidden" value="'.$_POST['Classe'].'" />';
};
	
?>
