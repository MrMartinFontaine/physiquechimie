<?php
session_start();
if ((isset($_SESSION['consultation']))||(isset($_SESSION['droits']))){ 
	
	if ((isset($_POST['classe_ID']))&&($_POST['classe_ID']!=intval($_POST['classe_ID']))){  header("Location: index.php");exit;};
	if (isset($_SESSION['droits'])&& isset($_POST['classe_ID'])){$_SESSION['consultation']=intval($_POST['classe_ID']);};
	if ((isset($_GET['date']))&&($_GET['date']!=intval($_GET['date']))){  header("Location: index.php");exit;};
	
	require_once('Connections/conn_cahier_de_texte.php'); 
	require_once('inc/functions_inc.php');
	
	
	
	
	
	if (isset($_POST['date1'])){
		if (jour_semaine($_POST['date1'])=='Dimanche'){$w=0;};
		if (jour_semaine($_POST['date1'])=='Lundi'){$w=1;};
		if (jour_semaine($_POST['date1'])=='Mardi'){$w=2;};
		if (jour_semaine($_POST['date1'])=='Mercredi'){$w=3;};
		if (jour_semaine($_POST['date1'])=='Jeudi'){$w=4;};
		if (jour_semaine($_POST['date1'])=='Vendredi'){$w=5;};
		if (jour_semaine($_POST['date1'])=='Dimanche'){$w=6;};
		if (jour_semaine($_POST['date1'])=='Samedi'){$w=7;};
		
		$date1=substr($_POST['date1'],6,4).'-'.substr($_POST['date1'],3,2).'-'.substr($_POST['date1'],0,2);
		$datetemp = explode("-",$date1);
		$date1_form=$datetemp[2].'/'.$datetemp[1].'/'.$datetemp[0];
		$date_lundi=date("Y-m-d", mktime(0, 0, 0, $datetemp[1], $datetemp[2] - $w + 1, $datetemp[0])); 
		$date_dimanche=date("Y-m-d", mktime(0, 0, 0, $datetemp[1], $datetemp[2] - $w + 7, $datetemp[0])); 
		
	}
	else{
		$date1=Date('Y-m-d');
		$datetemp = explode("-", Date('Y-m-d'));
		$date1_form=$datetemp[2].'/'.$datetemp[1].'/'.$datetemp[0]; 
		$date_lundi=date("Y-m-d", mktime(0, 0, 0, $datetemp[1], $datetemp[2] - Date('w') + 1, $datetemp[0])); 
		$date_dimanche=date("Y-m-d", mktime(0, 0, 0, $datetemp[1], $datetemp[2] - Date('w') + 7, $datetemp[0])); 
		
	};
	
	$code_date_lundi=substr($date_lundi,0,4).substr($date_lundi,5,2).substr($date_lundi,8,2);
	$code_date_dimanche=substr($date_dimanche,0,4).substr($date_dimanche,5,2).substr($date_dimanche,8,2);
	
	
	if (isset($_SESSION['consultation'])){ // on connait ID_classe de la classe - on affiche l'emploi du temps
		
		$choix_RsClasse = $_SESSION['consultation'];
		
		//recup de la semaine
                //$date_sem=date('Ymd');
                $date_sem=$code_date_lundi;
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_RsSemdate =  sprintf("SELECT * FROM cdt_semaine_ab WHERE s_code_date<=%s ORDER BY `s_code_date` DESC LIMIT 1 ",$date_sem);
                $RsSemdate = mysqli_query($conn_cahier_de_texte, $query_RsSemdate) or die(mysqli_error($conn_cahier_de_texte));
                $row_RsSemdate= mysqli_fetch_assoc($RsSemdate);
                if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){
			if ($row_RsSemdate['semaine']=='A et B'){$_SESSION['semdate_libelle']='P et I';} else if($row_RsSemdate['semaine']=='A'){$_SESSION['semdate_libelle']='Paire';} else {$_SESSION['semdate_libelle']='Impaire';};
		}
		else {$_SESSION['semdate_libelle']=$row_RsSemdate['semaine'];};
		$_SESSION['semdate']=$row_RsSemdate['semaine'];
		
		
		
		$editFormAction = '#';
		if (isset($_SERVER['QUERY_STRING'])) {
			$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
		}
		$err=0;
		
		
		
		
		if (isset($_POST['groupe'])){
			if ($_POST['groupe']=='Classe entière'){$sql_groupe='';}
			else { $sql_groupe="AND (groupe='Classe entière' OR groupe='".$_POST['groupe']."')";};
		}
		else {$sql_groupe='';};
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		
		// Le premier select liste les cours normaux
		// Le second select liste les regroupements
		// Le troisieme select liste les heure sup + Les devoirs programmes en dehors des cours
		
		$query_Rs_emploi = sprintf("
			SELECT 
			heure_debut,
			heure_fin,
			edt_exist_debut,
			edt_exist_fin,
			cdt_emploi_du_temps.classe_ID,
			cdt_emploi_du_temps.gic_ID,
			cdt_emploi_du_temps.groupe,
			cdt_emploi_du_temps.heure,
			cdt_emploi_du_temps.jour_semaine,
			cdt_emploi_du_temps.semaine,
			matiere_ID,
			nom_matiere,
			prof_ID,
			identite,
			nom_prof
			FROM cdt_emploi_du_temps, cdt_groupe_interclasses_classe,cdt_prof,cdt_matiere
			WHERE 
			cdt_emploi_du_temps.prof_ID = cdt_prof.ID_prof
			AND cdt_emploi_du_temps.classe_ID =0
			AND cdt_groupe_interclasses_classe.classe_ID =%s
			AND cdt_groupe_interclasses_classe.gic_ID = cdt_emploi_du_temps.gic_ID
			AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere
			AND ((cdt_emploi_du_temps.semaine='%s') OR (cdt_emploi_du_temps.semaine='A et B'))
			AND edt_exist_fin>='%s' AND edt_exist_debut<='%s' 
			%s
			
			UNION
			
			SELECT 
			heure_debut,
			heure_fin,
			edt_exist_debut,
			edt_exist_fin,
			cdt_emploi_du_temps.classe_ID,
			cdt_emploi_du_temps.gic_ID,
			cdt_emploi_du_temps.groupe,
			cdt_emploi_du_temps.heure,
			cdt_emploi_du_temps.jour_semaine,
			cdt_emploi_du_temps.semaine,
			matiere_ID,
			nom_matiere,
			prof_ID,
			identite,
			nom_prof
			FROM cdt_emploi_du_temps,cdt_prof,cdt_matiere
			WHERE 
			cdt_emploi_du_temps.prof_ID = cdt_prof.ID_prof
			AND cdt_emploi_du_temps.classe_ID=%u AND cdt_emploi_du_temps.gic_ID=0
			AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere
			AND ((cdt_emploi_du_temps.semaine='%s') OR (cdt_emploi_du_temps.semaine='A et B'))
			AND edt_exist_fin>='%s' AND edt_exist_debut<='%s'  
			%s
			
			UNION
			
			SELECT 
			cdt_agenda.heure_debut,
			cdt_agenda.heure_fin,
			cdt_agenda.type_activ,
			cdt_agenda.code_date,
			cdt_agenda.classe_ID,
			cdt_agenda.gic_ID,
			cdt_agenda.groupe,
			cdt_agenda.heure,
			cdt_agenda.jour_pointe,
			cdt_agenda.semaine,
			cdt_agenda.matiere_ID,
			cdt_matiere.nom_matiere,
			cdt_agenda.prof_ID,
			cdt_prof.identite,
			cdt_prof.nom_prof
			FROM cdt_prof,cdt_matiere,cdt_agenda
			WHERE 
			cdt_agenda.prof_ID = cdt_prof.ID_prof
			AND substring(cdt_agenda.code_date,9,1)=0
			AND substring(cdt_agenda.code_date,1,8)>=%s
			AND substring(cdt_agenda.code_date,1,8)<=%s
			AND cdt_agenda.classe_ID=%u AND cdt_agenda.gic_ID=0
			AND cdt_agenda.matiere_ID=cdt_matiere.ID_matiere
			AND ((cdt_agenda.semaine = '%s') OR (cdt_agenda.semaine='A et B')) 
			%s
			
			
			ORDER BY jour_semaine,heure,semaine
			", 
			$choix_RsClasse,$_SESSION['semdate'],date('Y-m-d'),date('Y-m-d'),$sql_groupe,
			$choix_RsClasse,$_SESSION['semdate'],date('Y-m-d'),date('Y-m-d'),$sql_groupe,
			$code_date_lundi,$code_date_dimanche,$choix_RsClasse,$_SESSION['semdate'],$sql_groupe
			);
		// echo $query_Rs_emploi;
		$Rs_emploi = mysqli_query($conn_cahier_de_texte, $query_Rs_emploi) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi);
		$totalRows_Rs_emploi = mysqli_num_rows($Rs_emploi);
		
		
		// recherche d'evenements de la semaine
 		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rsmessage =sprintf("SELECT date_debut,date_fin,heure_debut,heure_fin,titre_even,detail,groupe,identite,nom_prof
			FROM cdt_evenement_contenu,cdt_evenement_destinataire,cdt_prof,cdt_groupe WHERE cdt_evenement_contenu.dest_ID=0 
			AND cdt_evenement_contenu.prof_ID = cdt_prof.ID_prof 
			AND cdt_evenement_contenu.Id_even=cdt_evenement_destinataire.even_ID
			AND cdt_evenement_destinataire.groupe_ID=cdt_groupe.ID_groupe
			AND cdt_evenement_destinataire.classe_ID=%u
			AND (
			(date_fin>='%s' AND date_debut<'%s') OR
			(date_fin>='%s' AND date_debut<='%s') OR
			(date_fin<'%s' AND date_debut>'%s')
			)
			
			ORDER BY date_envoi,ID_even"
			,$choix_RsClasse,
			$date_dimanche,$date_dimanche,
			$date_lundi,$date_lundi,
			$date_dimanche,$date_lundi
			) ;
		//echo $query_Rsmessage;
		$Rsmessage = mysqli_query($conn_cahier_de_texte, $query_Rsmessage) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rsmessage = mysqli_fetch_assoc($Rsmessage);
		$totalRows_Rsmessage = mysqli_num_rows($Rsmessage);
		
		
		// recherche de vacances chevauchant la semaine
		
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$df='CONCAT(substring(heure_fin,7,4),substring(heure_fin,4,2),substring(heure_fin,1,2))';
		$dd='CONCAT(substring(heure_debut,7,4),substring(heure_debut,4,2),substring(heure_debut,1,2))';
		$date2_dimanche=substr($date_dimanche,0,4).substr($date_dimanche,5,2).substr($date_dimanche,8,2);
		$date2_lundi=substr($date_lundi,0,4).substr($date_lundi,5,2).substr($date_lundi,8,2);
		
		$query_RsVac =sprintf( "
			SELECT * FROM cdt_agenda 
			WHERE 
			classe_ID=0 
			AND matiere_ID=0
			AND prof_ID=0
			AND (
			($df>='%s' AND $dd<'%s') OR
			($df>='%s' AND $dd<='%s') OR
			($df<'%s' AND $dd>'%s')
			)
			
			ORDER BY cdt_agenda.code_date ASC
			",
			$date2_dimanche,$date2_dimanche,
			$date2_lundi,$date2_lundi,
			$date2_dimanche,$date2_lundi
			) ;
		
		//echo $query_RsVac;
		$RsVac = mysqli_query($conn_cahier_de_texte, $query_RsVac) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsVac = mysqli_fetch_assoc($RsVac);
		$totalRows_RsVac = mysqli_num_rows($RsVac);
		
		
	}; // du isset consultation
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
	$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsClasse = mysqli_fetch_assoc($RsClasse);
	$totalRows_RsClasse = mysqli_num_rows($RsClasse);
	
	// pour que le titre de la classe soit correct ou vide .
	if (isset($_SESSION['consultation']))
	{
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsClasse1 = "SELECT * FROM cdt_classe where ID_classe=".$_SESSION['consultation'];
		$RsClasse1 = mysqli_query($conn_cahier_de_texte, $query_RsClasse1) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsClasse1 = mysqli_fetch_assoc($RsClasse1);
		$totalRows_RsClasse1 = mysqli_num_rows($RsClasse1);
	}
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsMatiere = "SELECT * FROM cdt_matiere ORDER BY nom_matiere ASC";
	$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
	$totalRows_RsMatiere = mysqli_num_rows($RsMatiere);
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rsgroupe = "SELECT * FROM cdt_groupe GROUP BY CASE WHEN groupe = 'Classe entière' THEN '_AAA' ELSE groupe END ";
	$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
	$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);
	?>
        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
        <html>
        <head>
        <title>Emploi du temps 
        <?php if (isset($_SESSION['consultation'])) {?>de la classe de <?php echo $row_RsClasse1['nom_classe'];
} else { ?>d'une classe<?php } ?>
</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link media=screen href="./styles/style_default.css" type=text/css rel=stylesheet>
<link type="text/css" href="./styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />     
<link rel="stylesheet" type="text/css" href="./styles/colorpicker.css" />
<link media="screen" rel="stylesheet" href="./styles/colorbox.css" />
<link href="./styles/arrondis.css" rel="stylesheet" type="text/css" />
<link href="./styles/info_bulles.css" rel="stylesheet" type="text/css" />

<style type="text/css">
.detail {
	font-size: 9px;
	background-color: #FFFFFF;
	text-align:left;
	color: #000066;
	border: 1px solid #CCCCCC;
	border-collapse:collapse;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	vertical-align: top;
}
.curseur_aide { cursor: help; }
.curseur_pointe { cursor: pointer; }
.Style70 {font-size: small;color: #000066;}
</style>

<script type="text/javascript" src="./jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="./jscripts/jquery.colorbox.js"></script>
<script type="text/javascript" src="./jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="./jscripts/jquery-ui.datepicker-fr.js"></script>

</head>
<body>
<table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" class="lire_bordure" >
<tr class="lire_cellule_4">
<td colspan="3" class="black_police">EMPLOI DU TEMPS
<?php 
$today=date("d-m-Y");

echo'&nbsp;&nbsp;&nbsp;&nbsp;Semaine  du Lundi ';
echo substr($code_date_lundi,6,2).'/'.substr($code_date_lundi,4,2).'/'.substr($code_date_lundi,0,4);
echo ' au Dimanche '.substr($code_date_dimanche,6,2).'/'.substr($code_date_dimanche,4,2).'/'.substr($code_date_dimanche,0,4); ?>
<?php  if (isset($_SESSION['semdate_libelle'])){echo '&nbsp;&nbsp;&nbsp;&nbsp;Semaine '.$_SESSION['semdate_libelle'];};?>
</td>
<td><div align="right" > <a href="<?php 
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==1)){echo'administration/index.php';}
else 
if (isset($_SESSION['nom_prof'])&&(($_SESSION['droits']==2)||($_SESSION['droits']==8))){echo'enseignant/enseignant.php';}
else 
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==3 || $_SESSION['droits']==7)){echo 'vie_scolaire/vie_scolaire.php';}
else 
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==4)){echo 'direction/direction.php';}
else 
if (isset($_SESSION['nom_prof'])&&($_SESSION['droits']==6)){echo 'assistant_education/assistant_educ.php';}
else
if (isset($_SESSION['consultation'])){echo 'consulter.php?classe_ID='.intval($_SESSION['consultation']).'&tri=date';}
else {echo 'index.php';};
?>"><img src="./images/home-menu.gif" alt="Accueil" width="26" height="20" border="0"></a><br>
</div></td>
</tr>
<?php 

if (isset($_SESSION['consultation'])){ // on connait ID_classe de la classe - on affiche l'emploi du temps
	
	
	if ($totalRows_Rsmessage>0){
		do {?>
			<tr>
			<td class="tab_detail">
			<img src="images/info.jpg"></td>
			<td class="tab_detail" width="25%"><?php 
			echo '<strong>'.jour_semaine(substr($row_Rsmessage['date_debut'],8,2).'-'.substr($row_Rsmessage['date_debut'],5,2).'-'.substr($row_Rsmessage['date_debut'],0,4));
			echo ' '.substr($row_Rsmessage['date_debut'],8,2).'/'.substr($row_Rsmessage['date_debut'],5,2).'/'.substr($row_Rsmessage['date_debut'],0,4);
			echo '</strong> - '.$row_Rsmessage['heure_debut'];
			if ($row_Rsmessage['date_debut']<>$row_Rsmessage['date_fin']){
				echo '<br /> ';
				echo '<strong>'.jour_semaine(substr($row_Rsmessage['date_fin'],8,2).'-'.substr($row_Rsmessage['date_fin'],5,2).'-'.substr($row_Rsmessage['date_fin'],0,4));
				echo '  '.substr($row_Rsmessage['date_fin'],8,2).'/'.substr($row_Rsmessage['date_fin'],5,2).'/'.substr($row_Rsmessage['date_fin'],0,4);
			};
			echo '</strong> - '.$row_Rsmessage['heure_fin'].'<br /> '.$row_Rsmessage['groupe'].'<br /> ';
			echo ($row_Rsmessage['identite']=='' ?$row_Rsmessage['nom_prof']:$row_Rsmessage['identite']);?></td>
			
			<td  class="tab_detail"><?php echo '<strong>'.$row_Rsmessage['titre_even'].'</strong><br />'.$row_Rsmessage['detail'];?></td>
			<td  class="tab_detail"></td>
			</tr>
		<?php } while ($row_Rsmessage = mysqli_fetch_assoc($Rsmessage)); 
	};	
	
	//affichage des vacances
	if ($totalRows_RsVac>0){
		do {
			?>
			<tr>
			<td class="tab_detail" colspan="4" align="center"><strong> <?php echo $row_RsVac['theme_activ'].' du '.$row_RsVac['heure_debut'].' au '.$row_RsVac['heure_fin'];?> </strong></td>
			</tr>
			<?php
		} while ($row_RsVac = mysqli_fetch_assoc($RsVac));	
	};
	
}; // du second isset consultation
?>
</table>
<BR />
<form name="frm" method="POST" action="edt_eleve.php">

<script>
$(function() {
        	$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
        	$.datepicker.setDefaults($.datepicker.regional['fr']);
        	$('#date1').datepicker({firstDay:1});
});
</script>
Semaine&nbsp;du&nbsp;
<input name='date1' type='text' id='date1' value="<?php echo $date1_form;?>" size="10"/>
<strong>&nbsp;&nbsp;&nbsp;Classe&nbsp;de&nbsp;
<?php 

if (isset($_SESSION['droits'])){?>
	<select name='classe_ID' id='classe_ID' >
	<?php	do {?>
		<option value="<?php echo $row_RsClasse["ID_classe"];?>"
		<?php if ((isset($_SESSION['consultation'])) AND ($_SESSION['consultation']==$row_RsClasse['ID_classe'] )) {echo ' selected';} ;?>><?php echo $row_RsClasse["nom_classe"];?> </option>
	<?php }while($row_RsClasse = mysqli_fetch_assoc($RsClasse));	?>
	</select>
<?php }
else {
	do {
		if ((isset($_SESSION['consultation'])) AND ($_SESSION['consultation']==$row_RsClasse['ID_classe'] )) {echo $row_RsClasse["nom_classe"];}
	}while($row_RsClasse = mysqli_fetch_assoc($RsClasse));
};?>
</strong>&nbsp;
<select name="groupe" size="1" id="select">
<?php do {  ?>
	<option value="<?php echo $row_Rsgroupe['groupe'];?>"<?php 
	if ((isset($_POST['groupe'])) AND ($_POST['groupe']==$row_Rsgroupe['groupe'] )) {echo ' selected';} 
	else {
		if ((!isset($_POST['groupe'])) AND ($row_Rsgroupe['groupe']=='Classe entière')) {echo ' selected';};
	};?>> <?php echo $row_Rsgroupe['groupe']?></option>
	<?php
} while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
$rows = mysqli_num_rows($Rsgroupe);
if($rows > 0) {
	mysqli_data_seek($Rsgroupe, 0);
	$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
};
?>
</select>
<input name="submit" type="submit" value="Actualiser"/>
</form>
<BR />
<?php
if (isset($_SESSION['consultation'])){ // on connait ID_classe de la classe - on affiche l'emploi du temps
	
	if ($totalRows_Rs_emploi>0){?>
		Un enseignant n'ayant pas renseign&eacute; son emploi du temps dans le cahier de textes, n'apparaitra pas dans l'emploi du temps de la classe ci-dessous.<BR/><BR/> 
		<table width="100%" align="center" cellpadding="0" cellspacing="0" class="lire_bordure">
		<tr class="lire_cellule_4">
		<td ><div align="center"></div></td>
		<td >Lundi</td>
		<td >Mardi</td>
		<td >Mercredi</td>
		<td >Jeudi</td>
		<td >Vendredi</td>
		<td >Samedi</td>
		</tr>
		<?php $tab[1]='Lundi';$tab[2]='Mardi';$tab[3]='Mercredi';$tab[4]='Jeudi';$tab[5]='Vendredi';$tab[6]='Samedi';
		
		
		for($x=1;$x < 13;$x++) {?>
			<tr>
			<td bgcolor="#FFFFFF" class="detail"><div align="center"><?php echo $x ;?></div></td>
			<?php
			for($i=1;$i < 7;$i++) { 
				?>
				<td bgcolor="#FFFFFF" class="detail"><?php 		
				$nb_cell=0;
				do { 
					//la requete peut renvoyer "Lundi" ou  "Lundi 3 nov"
					$ChaineTab=explode(" ",$row_Rs_emploi['jour_semaine']); 
					
					if (($ChaineTab[0]==$tab[$i])&&($row_Rs_emploi['heure']==$x )){
						$nb_cell+=1;
						
						if (($_SESSION['semdate']==$row_Rs_emploi['semaine']) || ($row_Rs_emploi['semaine']=='A et B')){
						$row_Rs_emploi['couleur_cellule']='#CAFDBD';} 
						//else {$row_Rs_emploi['couleur_cellule']='#eeeeee';};
						
						//traitement devoir ou heure sup - discrimination
						//(jour_semaine est au format 'lundi 12 mai' au lieu de simplement 'lundi'
						if (strlen($row_Rs_emploi['jour_semaine'])>10) {$row_Rs_emploi['couleur_cellule']='#ADD8E6';} 
						
						
						
						if (($_SESSION['semdate']==$row_Rs_emploi['semaine']) || ($row_Rs_emploi['semaine']=='A et B')){
							$row_Rs_emploi['couleur_police']='#000000';} else {$row_Rs_emploi['couleur_police']='#666666';};
							
							
							
							echo '<style>.raised'. $x.$i.$nb_cell.' .top, .raised'. $x.$i.$nb_cell.' .bottom {display:block; background:transparent; font-size:1px;}
							.raised'. $x.$i.$nb_cell.' .b1, .raised'. $x.$i.$nb_cell.' .b2, .raised'. $x.$i.$nb_cell.' .b3, .raised'. $x.$i.$nb_cell.' .b4, .raised'. $x.$i.$nb_cell.' .b1b, .raised'. $x.$i.$nb_cell.' .b2b, .raised'. $x.$i.$nb_cell.' .b3b, .raised'. $x.$i.$nb_cell.' .b4b {display:block; overflow:hidden;}
							.raised'. $x.$i.$nb_cell.' .b1, .raised'. $x.$i.$nb_cell.' .b2, .raised'. $x.$i.$nb_cell.' .b3, .raised'. $x.$i.$nb_cell.' .b1b, .raised'. $x.$i.$nb_cell.' .b2b, .raised'. $x.$i.$nb_cell.' .b3b {height:1px;}
							.raised'. $x.$i.$nb_cell.' .b2 {background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #eee;} 
							.raised'. $x.$i.$nb_cell.' .b3 {background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #ddd;} 
							.raised'. $x.$i.$nb_cell.' .b4 {background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #aaa;} 
							.raised'. $x.$i.$nb_cell.' .b4b {background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #eee; border-right:1px solid #999;} 
							.raised'. $x.$i.$nb_cell.' .b3b {background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #ddd; border-right:1px solid #999;} 
							.raised'. $x.$i.$nb_cell.' .b2b {background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #aaa; border-right:1px solid #999;} 
							.raised'. $x.$i.$nb_cell.' .boxcontent {background:'.$row_Rs_emploi['couleur_cellule'].';}
							.raised'. $x.$i.$nb_cell.' .b1 {margin:0 5px; background:#fff;}
							.raised'. $x.$i.$nb_cell.' .b2, .raised'. $x.$i.$nb_cell.' .b2b {margin:0 3px; border-width:0 2px;}
							.raised'. $x.$i.$nb_cell.' .b3, .raised'. $x.$i.$nb_cell.' .b3b {margin:0 2px;}
							.raised'. $x.$i.$nb_cell.' .b4, .raised'. $x.$i.$nb_cell.' .b4b {height:2px; margin:0 1px;}
							.raised'. $x.$i.$nb_cell.' .b1b {margin:0 5px; background:#999;}
							.raised'. $x.$i.$nb_cell.' .boxcontent {display:block;  background:'.$row_Rs_emploi['couleur_cellule'].'; border-left:1px solid #fff; border-right:1px solid #999;}
							
							</style>';
							echo '<div class="raised'. $x.$i.$nb_cell.'" ><b class="top"><b class="b1"></b><b class="b2"></b><b class="b3"></b><b class="b4"></b></b><div class="boxcontent">';
							echo '<span style="color:'.$row_Rs_emploi['couleur_police'].'">';
							echo '<strong>&nbsp;'.$row_Rs_emploi['heure_debut'] .'</strong> - '.$row_Rs_emploi['heure_fin'].' - ';
							if ($row_Rs_emploi['semaine']!="A et B") {
								echo "<font color='#666666'><b>";
								if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){
									if($row_Rs_emploi['semaine']=='A'){echo 'Sem. Paire';} else {echo 'Sem. Impaire';}; 
								} else {
									echo 'Sem. '.$row_Rs_emploi['semaine'].'&nbsp;';
								};
								echo "</b></font>";
							} 
							
							
							//------------------------------------
							
							if ($row_Rs_emploi['classe_ID']==0){
								if ($row_Rs_emploi['gic_ID']!=='0'){
									//regroupement / retrouver le nom
									$query_Rsgic2 = sprintf("SELECT nom_gic,commentaire_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u",$row_Rs_emploi['gic_ID']);
									$Rsgic2 = mysqli_query($conn_cahier_de_texte, $query_Rsgic2) or die(mysqli_error($conn_cahier_de_texte));
									$row_Rsgic2 = mysqli_fetch_assoc($Rsgic2);
									echo '(R)&nbsp;'.$row_Rsgic2['nom_gic'];
									if (strlen($row_Rsgic2['nom_gic'])>20){echo'<br />&nbsp;';};
									mysqli_free_result($Rsgic2);
									echo '&nbsp;'.$row_Rs_emploi['groupe'].'<br />';
								}
								else {
									echo "<font color=red><b>Classe inconnue - A modifier</b></font>";
									echo "<br />";
								}
							} else { 
								
								$query_RsClasse = sprintf("SELECT nom_classe FROM cdt_classe WHERE ID_classe=%u",$row_Rs_emploi['classe_ID']);
								$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
								$row_RsClasse = mysqli_fetch_assoc($RsClasse);
								//echo 	$row_RsClasse['nom_classe'];
								//if (strlen($row_RsClasse['nom_classe'])>20){echo'<br />';}; */
								echo '&nbsp;'.$row_Rs_emploi['groupe'].'<br />';
								
								
							};
							
							//------------------------------------
							
							
							echo '&nbsp;';
							echo ($row_Rs_emploi['identite']=='' ?$row_Rs_emploi['nom_prof']:$row_Rs_emploi['identite']);
							echo '<br />';
							echo '&nbsp;<strong>'.$row_Rs_emploi['nom_matiere'];
							//echo '<br />';
							//traitement devoir ou heure sup  - discrimination
							//(jour_semaine est au format 'lundi 12 mai' au lieu de simplement 'lundi'
							if (strlen($row_Rs_emploi['jour_semaine'])>10) {
								if ($row_Rs_emploi['edt_exist_debut']=='ds_prog'){
								echo '&nbsp;<span style="color:#FF0000">'.$_SESSION['libelle_devoir'].'<span>'; } else {echo '&nbsp;<span style="color:#FF0000"> Heure suppl&eacute;mentaire<span>';
								};
							}; 				
							echo '</strong><br />';
							echo '</div><b class="bottom"><b class="b4b"></b><b class="b3b"></b><b class="b2b"></b><b class="b1b"></b></b></div>';
							echo '</span>';
							?>
							</div>
					<?php	 } ; ; 
				} while ($row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi));
				mysqli_data_seek($Rs_emploi, 0);
				?>
				</td>
			<?php }; ?>
			</tr>
			<?php  
			
		}; // du for($x=1;$x < 13;$x++)?>
		</table>
		<?php 
		mysqli_free_result($Rs_emploi);
	};   // du if ($totalRows_Rs_emploi>0) 
}; // du troisieme isset consultation
?>
<BR>
</BR>
</BODY>
</HTML>
<?php


mysqli_free_result($RsClasse);

mysqli_free_result($RsMatiere);

mysqli_free_result($Rsgroupe);



} else { //du isset droits et consultation
	header("Location: index.php");exit;};

if (isset($RsClasse1)){ mysqli_free_result($RsClasse1);}; 
?>
