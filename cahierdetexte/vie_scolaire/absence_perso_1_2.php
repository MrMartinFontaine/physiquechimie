<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>7)){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
require_once('../inc/module_absence_couleur.php');

if (!isset($_GET['date1'])){$datetoday=date('Ymd');$date1_form=date('d/m/Y');$jourtoday= jour_semaine(date('d/m/Y'));;
} else {
	$datetoday=substr($_GET['date1'],6,4).substr($_GET['date1'],3,2).substr($_GET['date1'],0,2);
$date1_form= $_GET['date1'];$jourtoday= jour_semaine($_GET['date1']);};

//toutes les classes
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<link media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<link media="all" type="text/css" href="../styles/jquery-ui.css" rel="stylesheet">
 
<style type="text/css">
.Style70 {color: #FFFFFF}
.blanc {color:#FFFFFF;font-weight:bold}
.element.style {
    display: block;
    height: auto;
    left: 678px;
    outline: 0 none;
    position: absolute;
    top: 0;
    width: 600px;
    z-index: 1001;
}
</style>
<script type="text/javascript" src="../jscripts/utils.js"></script>
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../jscripts/jquery-ui.datepicker-fr.js"></script>

<script language="JavaScript" type="text/JavaScript">
$(document).ready(function() 
	{
		var refreshId = setInterval(function()
			{
				location.reload();
			}, 60000);		
	}
	);
</script>
</head>
<body onClick="$('#divPourDialog').dialog('close')";>
<div id="acquittement"  style="display: none;"></div>
<table width="100%" border="0" class="tab_detail_gris">
<tr>
<td><form name="frm" method="GET" action="absence_perso_1_2.php?">
<script>
$(function() {
        	$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
        	$.datepicker.setDefaults($.datepicker.regional['fr']);
        	$('#date1').datepicker({firstDay:1});
});
</script>
<p align="center" class="Style13">Pointage des oublis effectu&eacute; par  les enseignants le&nbsp;
  <input name='date1' type='text' id='date1' value="<?php echo $date1_form;?>" size="10"/>
<input name="submit" type="submit" value="S&eacute;lectionner"/>
</p>
</form></td>
<td><a href="absence_perso_1_2_pdf.php?date1=<?php if (isset($_GET['date1'])){echo $_GET['date1'];}else {echo date('d-m-Y');} ;?>" target="_blank"><img src="../images/pdf2.jpg" width="35" height="16" border="0"></a></td>
<td><div align="right"><a href="  <?php 
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'vie_scolaire.php';};
if ($_SESSION['droits']==4){echo '../direction/direction.php';};
?>"><img src="../images/home-menu.gif" border="0"></a></div></td>
</tr>
</table>
<?php //Quelles sont les classes ou l'appel a ete effectue
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

$query_Rsabsent_cl = sprintf("SELECT DISTINCT classe_ID,classe FROM ele_absent WHERE SUBSTRING(ele_absent.code_date,1,8)= '%s'  ORDER BY classe",$datetoday);
$Rsabsent_cl= mysqli_query($conn_cahier_de_texte, $query_Rsabsent_cl) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsabsent_cl = mysqli_fetch_assoc($Rsabsent_cl);
$totalRows_Rsabsent_cl = mysqli_num_rows($Rsabsent_cl);

if ($totalRows_Rsabsent_cl>0){
	
	do { //pour chaque classe 
		
		
		//recherche de devoirs planifies
		
		
		$query_RsDs = sprintf(" 
			SELECT *
			FROM cdt_agenda, cdt_prof
			WHERE substring( code_date, 9, 1 ) =0
			AND substring( code_date, 1, 8 ) =%s
			AND cdt_prof.ID_prof = cdt_agenda.prof_ID
			AND classe_ID=%u
			GROUP BY cdt_agenda.heure
			ORDER BY heure_debut",
			substr($datetoday,0,8),$row_Rsabsent_cl['classe_ID']);
		
		$RsDs = mysqli_query($conn_cahier_de_texte, $query_RsDs) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsDs = mysqli_fetch_assoc($RsDs);
		$totalRows_RsDs = mysqli_num_rows($RsDs);
		
		//On recherche les eleves absents au cours
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsabsent = sprintf("SELECT DISTINCT eleve_ID,nom_ele,prenom_ele,nom_classe FROM ele_absent,cdt_prof,ele_liste,cdt_classe WHERE  (perso1='O' OR perso2='O') AND ele_liste.classe_ele COLLATE latin1_swedish_ci = cdt_classe.code_classe COLLATE latin1_swedish_ci AND cdt_prof.ID_prof=ele_absent.prof_ID AND ele_absent.eleve_ID= ele_liste.ID_ele AND  ele_absent.code_date LIKE '%s%%' AND ele_absent.classe_ID=%u ORDER BY classe,nom_classe,nom_ele", substr($datetoday,0,8),$row_Rsabsent_cl['classe_ID']);
		
		$Rsabsent = mysqli_query($conn_cahier_de_texte, $query_Rsabsent) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rsabsent = mysqli_fetch_assoc($Rsabsent);
		$totalRows_Rsabsent = mysqli_num_rows($Rsabsent);
		

		?>
		<table border="0" align="center" width="100%" >
		<tr>
		<td class="Style6" width="100%"><div align="left">
		<?php if ($row_Rsabsent_cl['classe_ID']==0){echo 'Regroupements';}else {echo $row_Rsabsent_cl['classe'];};?>
		</div></td>
		</tr>
		</table>
		<table>
		<tr>
		<?php
		
		
		// on recherche si une saisie a ete faite
		
		//recup de la semaine et gestion des semaines A et B
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsSemdate =  sprintf("SELECT * FROM cdt_semaine_ab WHERE cdt_semaine_ab.s_code_date<=%s ORDER BY `s_code_date` DESC LIMIT 1 ",$datetoday);
		$RsSemdate = mysqli_query($conn_cahier_de_texte, $query_RsSemdate) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsSemdate= mysqli_fetch_assoc($RsSemdate);
		$semdate = $row_RsSemdate['semaine'] ;
		
		if ( $semdate == "A" ) {
			$semdate_exclusion = "B";	
		} else if ( $semdate == "B" ) {
			$semdate_exclusion = "A";	
		} else {
			$semdate_exclusion = NULL;
		}
		
		if (!is_null($semdate_exclusion) ) {
			$query_Rs_emploi = sprintf("SELECT heure, heure_debut,heure_fin,identite,groupe,gic_ID,prof_ID,edt_exist_fin FROM cdt_emploi_du_temps,cdt_prof WHERE ID_prof=cdt_emploi_du_temps.prof_ID AND cdt_emploi_du_temps.classe_ID=%u AND cdt_emploi_du_temps.jour_semaine='%s' AND '%s'<= edt_exist_fin AND semaine!='%s'  ORDER BY cdt_emploi_du_temps.heure_debut", $row_Rsabsent_cl['classe_ID'],$jourtoday,date('Y-m-d'),$semdate_exclusion ); 
		} else {
			$query_Rs_emploi = sprintf("SELECT heure, heure_debut,heure_fin,identite,groupe,gic_ID,prof_ID,edt_exist_fin FROM cdt_emploi_du_temps,cdt_prof WHERE ID_prof=cdt_emploi_du_temps.prof_ID AND cdt_emploi_du_temps.classe_ID=%u AND cdt_emploi_du_temps.jour_semaine='%s' AND '%s'<= edt_exist_fin ORDER BY cdt_emploi_du_temps.heure_debut", $row_Rsabsent_cl['classe_ID'],$jourtoday,date('Y-m-d')); 
		};
		
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		
		$Rs_emploi = mysqli_query($conn_cahier_de_texte, $query_Rs_emploi) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi);
		$totalRows_Rs_emploi = mysqli_num_rows($Rs_emploi);
		
		if ( $totalRows_Rs_emploi >0 ){
			
			
			echo '<tr><td  width="150" class="tab_detail_gris"></td>';
			$a=1;
			do {
				
				
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_Rsabs = sprintf("SELECT * FROM ele_absent WHERE  
				(perso1='O' OR perso2='O') AND
					classe_ID=%u
					AND 
					SUBSTRING(ele_absent.code_date,1,8)='%s'
					AND heure =%s
					AND ( eleve_ID=0 OR eleve_ID IS NULL )
					AND prof_ID=%u
					", $row_Rsabsent_cl['classe_ID'],$datetoday,$row_Rs_emploi['heure'],$row_Rs_emploi['prof_ID']);
				$Rsabs = mysqli_query($conn_cahier_de_texte, $query_Rsabs) or die(mysqli_error($conn_cahier_de_texte));
				$row_Rsabs = mysqli_fetch_assoc($Rsabs);
				$totalRows_Rsabs = mysqli_num_rows($Rsabs);
				
				//Dans une classe ou l'appel est fait, nombre d'absents par heure de cours
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				
				//************** heure normale emploi du temps **************************
				$query_Rsnbele = sprintf("SELECT * FROM ele_absent WHERE (perso1='O' OR perso2='O') AND SUBSTRING(ele_absent.code_date,1,8)= '%s' AND ele_absent.classe_ID=%u AND heure='%s' AND prof_ID=%u ",$datetoday,$row_Rsabsent_cl['classe_ID'],$row_Rs_emploi['heure'],$row_Rs_emploi['prof_ID']);	
				$Rsnbele = mysqli_query($conn_cahier_de_texte, $query_Rsnbele) or die(mysqli_error($conn_cahier_de_texte));
				$row_Rsnbele= mysqli_fetch_assoc($Rsnbele);
				$totalRows_Rsnbele = mysqli_num_rows($Rsnbele);
				
				
				
				echo '<td width="120"  ';
				
				if ( ($totalRows_Rsnbele>0) && ( $totalRows_Rsabs>0)) { echo ' bgcolor="'. $couleur_pas_absent  .'"'; }
				else if ( ($totalRows_Rsabs == 0) && ($totalRows_Rsnbele == 0 )) { echo ' bgcolor="'. $couleur_pas_appel .'"'; }
				else if ($totalRows_Rsnbele>0) { echo ' bgcolor="'.  $couleur_absent .'"';}
				else { echo 'class="tab_detail_gris"';};
				echo '>';
				echo $row_Rs_emploi['heure_debut']. '-'.$row_Rs_emploi['heure_fin'];
				if ($row_Rsnbele['salle']<>''){echo ' ('.$row_Rsnbele['salle'].')';};
				echo '<br> '.$row_Rs_emploi['identite'];
				echo '<br> '.$row_Rs_emploi['groupe'] .'<br> ';
				
				//on affiche en plus le nom du regroupement s'il existe
				if($row_Rs_emploi['gic_ID']>0){
					$query_Rsnom_regroup = sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE  ID_gic= %s",$row_Rs_emploi['gic_ID']);	
					$Rsnom_regroup = mysqli_query($conn_cahier_de_texte, $query_Rsnom_regroup) or die(mysqli_error($conn_cahier_de_texte));
					$row_Rsnom_regroup= mysqli_fetch_assoc($Rsnom_regroup);
					echo $row_Rsnom_regroup['nom_gic'] .'<br> ';
				};
				
				if ($totalRows_Rsnbele>0  && $totalRows_Rsabs == 0 ) {
					echo $totalRows_Rsnbele. ' &eacute;l&egrave;ve(s)';
				} elseif ($totalRows_Rsabs>0) {
					echo 'Pas d\'&eacute;l&egrave;ve';
				} else {
					echo 'Pas d\'appel';
				}
				
				echo '</td>';
				$hdeb[$a]=$row_Rs_emploi['heure_debut'];
				$idp[$a]=$row_Rs_emploi['prof_ID'];
				$a=$a+1;
			} while ($row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi)); 
			
} else { echo ' Pas de cours. ';};	

//affichage ds dans cellule du bandeau
if ($totalRows_RsDs>0){
	do
	{
		
		
		//******************* heure de ds et heure sup ********************
		
		$query_Rsnbele = sprintf("SELECT * FROM ele_absent WHERE (perso1='O' OR perso2='O') AND SUBSTRING(ele_absent.code_date,1,8)= '%s' AND ele_absent.classe_ID=%u AND heure_debut='%s' AND prof_ID=%u ",$datetoday,$row_Rsabsent_cl['classe_ID'],$row_RsDs['heure_debut'],$row_RsDs['prof_ID']);	
		$Rsnbele = mysqli_query($conn_cahier_de_texte, $query_Rsnbele) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rsnbele= mysqli_fetch_assoc($Rsnbele);
		$totalRows_Rsnbele = mysqli_num_rows($Rsnbele);
		
		echo '<td width="120"  ';
		
		if ( ($totalRows_Rsnbele>0) && ( $totalRows_Rsabs>0)) { echo ' bgcolor="'. $couleur_pas_absent  .'"'; }
		else if ( ($totalRows_Rsabs == 0) && ($totalRows_Rsnbele == 0 )) { echo ' bgcolor="'. $couleur_pas_appel .'"'; }
		else if ($totalRows_Rsnbele>0) { echo ' bgcolor="'.  $couleur_absent .'"';}
		else { echo 'class="tab_detail_gris"';};
		echo '>';
		echo $row_RsDs['heure_debut']. '-'.$row_RsDs['heure_fin'];
		if ($row_Rsnbele['salle']<>''){echo ' ('.$row_Rsnbele['salle'].')';};
		if ($row_RsDs['type_activ']=='ds_prog'){echo '<br /><span class="blanc">'.$_SESSION['libelle_devoir'].'</span>';} else {echo '<br /><span class="blanc">Heure sup.</span>';};
		echo '<br> '.$row_RsDs['identite'];
		echo '<br> '.$row_RsDs['groupe'] .' <br> ';
		if ($totalRows_Rsnbele>0  && $totalRows_Rsabs == 0 ) {
			echo $totalRows_Rsnbele. ' &eacute;l&egrave;ve(s)';
		} elseif ($totalRows_Rsabs>0) {
			echo 'Pas d\'&eacute;l&egrave;ve';
		} else {
			echo 'Pas d\'appel';
		};
		
		
		echo '</td>';
		$hdeb[$a]=$row_RsDs['heure_debut'];
		$gr[$a]=$row_RsDs['groupe'];
		$np[$a]=$row_RsDs['nom_prof'];
		$idp[$a]=$row_RsDs['ID_prof'];
		$a=$a+1;
		
	} while ($row_RsDs = mysqli_fetch_assoc($RsDs));
};


echo '</tr>';	

if ($totalRows_Rsabsent<>0){
	
	$ic=0;
	$previous_classe = "";
	do { 	?>
		<?php if (($row_Rsabsent_cl['classe_ID'] == 0) && ($previous_classe != $row_Rsabsent['nom_classe'] ) ) { ?>
		  <tr >
			<td class="Style666b" colspan="<?php echo $totalRows_Rs_emploi +1+$totalRows_RsDs;    ?>" ><div align="left">
			<?php   echo $row_Rsabsent['nom_classe'];  ?>
			</div></td>
		  </tr>
			<?php
			
			$previous_classe = $row_Rsabsent['nom_classe'];
                        
                } ?>
                <tr>
                <td  class="tab_detail_gris"><?php echo $row_Rsabsent['nom_ele']. ' '.$row_Rsabsent['prenom_ele'];  ?></td>
                <?php
                for($j=1;$j<=($totalRows_Rs_emploi+$totalRows_RsDs);$j++){
                        ?>
			<?php
                        
                        
                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $query_Rsele = sprintf("SELECT * FROM ele_absent  WHERE (perso1='O' OR perso2='O') AND eleve_ID=%u AND SUBSTRING(code_date,1,8)='%s' AND classe_ID=%u AND heure_debut='%s' AND prof_ID=%u",$row_Rsabsent['eleve_ID'],$datetoday,$row_Rsabsent_cl['classe_ID'],$hdeb[$j], $idp[$j]);
                        
                        $Rsele = mysqli_query($conn_cahier_de_texte, $query_Rsele) or die(mysqli_error($conn_cahier_de_texte));
                        $row_Rsele= mysqli_fetch_assoc($Rsele);
			$totalRows_Rsele = mysqli_num_rows($Rsele);	
			
			if ($totalRows_Rsele <= 0) {
                                
                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                
                                $query_Rsele = sprintf("SELECT * FROM ele_absent WHERE (perso1='O' OR perso2='O') AND eleve_ID=%u AND SUBSTRING(code_date,1,8)='%s' AND eleve_ID!=0 AND classe_ID=%u AND heure_debut='%s' AND prof_ID=%u ",$row_Rsabsent['eleve_ID'],$datetoday,$row_Rsabsent_cl['classe_ID'],$hdeb[$j], $idp[$j]);
                                
                                $Rsele = mysqli_query($conn_cahier_de_texte, $query_Rsele) or die(mysqli_error($conn_cahier_de_texte));
                                $row_Rsele= mysqli_fetch_assoc($Rsele);
				$totalRows_Rsele = mysqli_num_rows($Rsele);
			}
			
			
			if ($row_Rsele['heure_debut']==$hdeb[$j]){
				echo '<td  class="tab_detail_gris" width="120" ><div align="left">&nbsp;';
				if ($row_Rsele['perso1']=='O'){
					echo '&nbsp;Carnet';
				};	
				if (($row_Rsele['perso1']=='O')&&($row_Rsele['perso2']=='O')){echo ' et ';};
				if ($row_Rsele['perso2']=='O'){
					echo '&nbsp;Mat&eacute;riel';
				};
				echo '</div></td>';
			}
			else
			{
				echo '<td class="tab_detail_gris" width="120"></td> ';
			}
			;
			?>
			</div>
			</td>
			<?php   	
		} ;  ?>
		</tr>
		<?php 
		
	} while ($row_Rsabsent = mysqli_fetch_assoc($Rsabsent)); 
};
//} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse)); 	
	} while ($row_Rsabsent_cl = mysqli_fetch_assoc($Rsabsent_cl));
	mysqli_free_result($Rsabsent);
} else {echo '<br> Aucun pointage r&eacute;alis&eacute; pour cette journ&eacute;e <br><br>';};
?>
</table>
<p>&nbsp;</p>
<p><a href="
<?php 
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'vie_scolaire.php';};
if ($_SESSION['droits']==4){echo '../direction/direction.php';};

?>
"><br>
Retour au menu
<?php
if ($_SESSION['droits']==3 || $_SESSION['droits']==7){echo 'Vie scolaire';};
if ($_SESSION['droits']==4){echo 'Responsable Etablissement';};
?>
</a> </p>
<?php
if (isset($RsClasse)){mysqli_free_result($RsClasse);};
if (isset($derniers_ele)){mysqli_free_result($derniers_ele);};
if (isset($Rsmessage2)){mysqli_free_result($Rsmessage2);};
if (isset($Rsele)){mysqli_free_result($Rsele);};
if (isset($Rsnbele)){mysqli_free_result($Rsnbele);};

if (isset($Rs_emploi)){mysqli_free_result($Rs_emploi);};
if (isset($RsSemdate)){mysqli_free_result($RsSemdate);};
if (isset($Rsabsent_cl)){mysqli_free_result($Rsabsent_cl);};
if (isset($Rsnom_regroup)){mysqli_free_result($Rsnom_regroup);};
if (isset($Rsabs)){mysqli_free_result($Rsabs);};

?>
</body>
</html>
