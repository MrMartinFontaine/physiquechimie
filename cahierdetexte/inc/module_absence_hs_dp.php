<?php 
//Lien vers le module de declaration des absences
if ( (isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui')) { 

//Heure supplementaire ou devoir planifie
?>
			  
<form method="post" target="_blank" action="absence_ajout.php?nom_classe=<?php echo $row_RsHS['nom_classe']?>&classe_ID=<?php echo $row_RsHS['classe_ID']?>&gic_ID=<?php echo $row_RsHS['gic_ID'];?>&nom_matiere=<?php echo $row_RsHS['nom_matiere']?>&groupe=<?php echo $row_RsHS['groupe']?>&matiere_ID=<?php echo $row_RsHS['matiere_ID']?>&semaine=<?php echo $row_RsHS['semaine']?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&heure=<?php echo $row_RsHS['heure']?>&duree=<?php echo $row_RsHS['duree']?>&heure_debut=<?php echo $row_RsHS['heure_debut']?>&heure_fin=<?php echo $row_RsHS['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php echo $date_edition.'0';?> ">
&nbsp;<input border=0 src="../images/user_absent.png" type="image" value="submit" alt="Gestion des absents" title="Gestion des absents">
</form> 

<?php 
};
?>


