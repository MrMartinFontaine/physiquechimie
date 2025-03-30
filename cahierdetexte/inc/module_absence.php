<?php 
//Lien vers le module de declaration des absences
if ( (isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui'))  {
//Heure normale
?>	

<form method="post" target="_blank" action="absence_ajout.php?nom_classe=<?php echo $row_RsJour['nom_classe']?>&classe_ID=<?php echo $row_RsJour['classe_ID']?>&gic_ID=<?php echo $row_RsJour['gic_ID']?>&nom_matiere=<?php echo $row_RsJour['nom_matiere']?>&groupe=<?php echo $row_RsJour['groupe']?>&matiere_ID=<?php echo $row_RsJour['matiere_ID']?>&semaine=<?php echo $row_RsJour['semaine']?>&jour_pointe=<?php if (isset($_GET['jour_pointe'])) {echo $_GET['jour_pointe'];} else {echo $jour_pointe;}?>&heure=<?php echo $row_RsJour['heure']?>&duree=<?php echo $row_RsJour['duree']?>&heure_debut=<?php echo $row_RsJour['heure_debut']?>&heure_fin=<?php echo $row_RsJour['heure_fin']?>&current_day_name=<?php echo $current_day_name?>&code_date=<?php echo $code_date?>&edt_modif=<?php if (isset($row_RsAgenda2['edt_modif'])){echo $row_RsAgenda2['edt_modif'];}else{echo'N';}?>">


<input border=0 src="../images/user_absent.png" type="image" value="submit" alt="Gestion des absents" title="Gestion des absents">
</form> 
<?php 
};?>

