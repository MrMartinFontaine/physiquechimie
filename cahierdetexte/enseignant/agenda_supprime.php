<?php 
include "../authentification/authcheck.php";
if (($_SESSION['droits']<>2)&&($_SESSION['droits']<>8)) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 

require_once('../inc/functions_inc.php');

$lien = '?nom_classe= '.$_GET['nom_classe'].'&classe_ID='.$_GET['classe_ID'].'&gic_ID='.$_GET['gic_ID'].'&nom_matiere='.$_GET['nom_matiere'].'&groupe='.$_GET['groupe'].'&matiere_ID='.$_GET['matiere_ID'].'&semaine='.$_GET['semaine'].'&jour_pointe='.$_GET['jour_pointe'].'&heure='.$_GET['heure'].'&heure_debut='.$_GET['heure_debut'].'&heure_fin='.$_GET['heure_fin'].'&current_day_name='.$_GET['current_day_name'].'&code_date='.$_GET['code_date'];
$lien .= isset($_GET['duree'])?'&duree='.$_GET['duree']:'';





if (isset($_POST["sup_ok"]))
{
	
	
	if ((isset($_POST['ID_agenda'])) && ($_POST['ID_agenda'] != "")) {
		
		
		//retrouver les ID_agenda de meme contenu si regroupements
		if ($_GET['classe_ID']==0) {
			
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_Rsagenda =sprintf("SELECT * FROM cdt_agenda WHERE  ID_agenda=%u ",$_POST['ID_agenda']);
			$Rsagenda = mysqli_query($conn_cahier_de_texte, $query_Rsagenda) or die(mysqli_error($conn_cahier_de_texte));
			$row_Rsagenda = mysqli_fetch_assoc($Rsagenda);
                        
                        
                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        //requete complete / securite versions anterieures
                        $query_Rsagenda_idem =sprintf("SELECT cdt_agenda.ID_agenda FROM cdt_agenda WHERE  code_date=%s AND gic_ID = %s AND groupe ='%s' AND prof_ID=%u AND matiere_ID=%u",$row_Rsagenda['code_date'],$row_Rsagenda['gic_ID'],$row_Rsagenda['groupe'],$_SESSION['ID_prof'],$row_Rsagenda['matiere_ID']);
                        
                        
			
			$Rsagenda_idem = mysqli_query($conn_cahier_de_texte, $query_Rsagenda_idem) or die(mysqli_error($conn_cahier_de_texte));
			$row_Rsagenda_idem = mysqli_fetch_assoc($Rsagenda_idem);
			$totalRows_Rsagenda_idem = mysqli_num_rows($Rsagenda_idem);
			
			$x=0;
			do {
				$tabidagenda[$x]=$row_Rsagenda_idem['ID_agenda'];$x=$x+1;
			} while ($row_Rsagenda_idem = mysqli_fetch_assoc($Rsagenda_idem));
		}
		else { 
			$totalRows_Rsagenda_idem=1;
		};
		
		
		
		$x_id=0; 
		
		do { 
			
			if ($_GET['classe_ID']==0) {$_POST['ID_agenda']=$tabidagenda[$x_id];}; // Pour conserver le code existant
			
			//on supprime la fiche dans agenda
			$deleteSQL = sprintf("DELETE FROM cdt_agenda WHERE ID_agenda=%u",
				GetSQLValueString($_POST['ID_agenda'], "int"));
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
			
			//suppression du travail eventuel
			$deleteSQL = sprintf("DELETE FROM cdt_travail WHERE agenda_ID=%u",
				GetSQLValueString($_POST['ID_agenda'], "int"));
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
			
			
			//suppression des fichiers joints
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$sql_f="SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=".$_POST['ID_agenda'];
			$query_Rs_fichiers_joints_form = $sql_f;
			$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
			$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
			$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
			
			// suppression des fichiers joints
			if ($totalRows_Rs_fichiers_joints_form <> '0'){ 
                                
                                
                                do { 
                                        // ne pas supprimer le fichier si utilise par ailleurs 
                                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                        $ch_select="%_".strchr($row_Rs_fichiers_joints_form['nom_fichier'],'_');
                                        $query_Recordset3 = sprintf("SELECT * FROM cdt_fichiers_joints WHERE nom_fichier like '%s' ",$ch_select );
					$Recordset3 = mysqli_query($conn_cahier_de_texte, $query_Recordset3) or die(mysqli_error($conn_cahier_de_texte));
					$row_Recordset3 = mysqli_fetch_assoc($Recordset3);
					$totalRows_Recordset3 = mysqli_num_rows($Recordset3);
					if ($totalRows_Recordset3==1){
						$fichier = '../fichiers_joints/'.$row_Recordset3['nom_fichier'];
						unlink($fichier);		
					}
					mysqli_free_result($Recordset3);
					
				} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
				mysqli_free_result($Rs_fichiers_joints_form);
				
				//on efface de la table fichiers_joints
				$deleteSQL = "DELETE FROM cdt_fichiers_joints WHERE agenda_ID=".$_POST['ID_agenda'];
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result1 = mysqli_query($conn_cahier_de_texte, $deleteSQL) or die(mysqli_error($conn_cahier_de_texte));
			}
                        
                        $x_id+=1;
                }   while ($x_id < $totalRows_Rsagenda_idem); 
                //fin enregistrement d'une ou plusieurs fiches selon la valeur de $totalRows_Rsgic egal au nombre de classes (une si pas de regroupement)
                
                
                
		$deleteGoTo = 'ecrire.php?date='.substr($_GET['code_date'],0,8); 
		header(sprintf("Location: %s", $deleteGoTo));
		
                
        } //du if existence ID_agenda
        
} //du if Sup_OK - formulaire poste




$Id_Recordset1 = "0";
if (isset($_GET['ID_agenda'])) {
        $Id_Recordset1 = (get_magic_quotes_gpc()) ? $_GET['ID_agenda'] : addslashes($_GET['ID_agenda']);
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Recordset1 = sprintf("SELECT * FROM cdt_agenda, cdt_classe, cdt_matiere WHERE cdt_agenda.ID_agenda=%u AND cdt_agenda.classe_ID=cdt_classe.ID_classe AND cdt_agenda.matiere_ID=cdt_matiere.ID_matiere", $Id_Recordset1);
$Recordset1 = mysqli_query($conn_cahier_de_texte, $query_Recordset1) or die(mysqli_error($conn_cahier_de_texte));
$row_Recordset1 = mysqli_fetch_assoc($Recordset1);
$totalRows_Recordset1 = mysqli_num_rows($Recordset1);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMat = "SELECT * FROM cdt_matiere ";
$RsMat = mysqli_query($conn_cahier_de_texte, $query_RsMat) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMat = mysqli_fetch_assoc($RsMat);
$totalRows_RsMat = mysqli_num_rows($RsMat);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_callJS(jsStr) { //v2.0
	return eval(jsStr)
}

function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
//-->
</script>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Suppression d'une activit&eacute;";
require_once "../templates/default/header.php";
?>
<HR>
<br>
<p align="center"> Vous avez demand&eacute; la suppression de l'enregistrement ci-dessous en </p>
<p align="center"> <strong>


<?php if ($_GET['classe_ID']==0){
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rsgic =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE cdt_groupe_interclasses.ID_gic = %u ",$_GET['gic_ID']);
	$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsgic = mysqli_fetch_assoc($Rsgic);
	echo $row_Rsgic['nom_gic'];
} else {
	echo $row_Recordset1['nom_classe'];
}?><?php echo ' - '.$row_Recordset1['nom_matiere']; ?>
</strong></p>
<table width="95%" border="1" align="center" cellpadding="1O" cellspacing="0" class="Style4" style="border-collapse:collapse" >
<?php do { ?>
	<tr class="Style6">
	<td width="28%" class="Style666"><?php echo $row_Recordset1['jour_pointe']; ?> </td>
	<td width="72%" valign="top"><?php echo $row_Recordset1['theme_activ']; ?></td>
	</tr>
	<tr valign="top">
	<td class="style9" ><p class="Style67"><?php echo $row_Recordset1['groupe']; ?><br>
	<?php echo $row_Recordset1['heure_debut']; ?> - <?php echo $row_Recordset1['heure_fin']; ?><br>
	<?php if ($row_Recordset1['type_activ']<>'ds_prog'){echo $row_Recordset1['type_activ'];}else{echo $_SESSION['libelle_devoir'];};  ?>
        </p>
        <?php
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $sql_f="SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=".$Id_Recordset1." ORDER BY nom_fichier";
        $query_Rs_fichiers_joints_form = $sql_f;
        $Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
        $row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
        $totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
        
        // Affichage des fichiers deja joints
        if ($totalRows_Rs_fichiers_joints_form <> '0'){     
                echo 'Documents<br /><br />';
                do { 
        		?>
        		<p>
        		<?php  $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);?>
        		<a href="../fichiers_joints/<?php echo $row_Rs_fichiers_joints_form['nom_fichier'];  ?>" target="_blank"><strong><?php echo $nom_f ;  ?></strong></a> </p>
        		
        		<?php
        	} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
        	
        	mysqli_free_result($Rs_fichiers_joints_form);
        } // du if ($totalRows_Rs_modif 
        ?>
        </td>
        <td align="left" style=""><blockquote>
        <p>
        <?php 
	
	// travail ****************************************************************************************
	
	//***************************************
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_Rs_Travail2 = sprintf("SELECT * FROM cdt_travail WHERE agenda_ID=%u ORDER BY code_date", $Id_Recordset1);
	$Rs_Travail2 = mysqli_query($conn_cahier_de_texte, $query_Rs_Travail2) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rs_Travail2 = mysqli_fetch_assoc($Rs_Travail2);
	
	
	
	$date_a_faire[1]='';$date_a_faire[2]='';$date_a_faire[3]='';
	$travail[1]='';$travail[2]='';$travail[3]='';
	$t_groupe[1]='';$t_groupe[2]='';$t_groupe[3]='';
	$eval[1]='';$eval[2]='';$eval[3]='';
	do {  
		$travail[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['travail'];
		$date_a_faire[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['t_code_date'];
		$t_groupe[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['t_groupe'];
		$eval[$row_Rs_Travail2['ind_position']]=$row_Rs_Travail2['eval'];
	} while ($row_Rs_Travail2 = mysqli_fetch_assoc($Rs_Travail2));
	
	
	//******************************************
	
	?>
	<span class="Style69">
	<?php 
	if ( $date_a_faire[1]<>''){
		echo 'A faire pour le '.$date_a_faire[1].'   ';
		
		//affichage fichiers travail joints
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$sql_f="SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=".$Id_Recordset1." AND type ='Travail' AND t_code_date ='".$date_a_faire[1]."' AND ind_position = 1 ORDER BY nom_fichier";
		$query_Rs_fichiers_joints_form = $sql_f;
		$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
		$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
		
		if ($totalRows_Rs_fichiers_joints_form<>0)
		{
			do { ?>
				<a href="fichier_supprime.php<?php echo '&ID_fichiers='.$row_Rs_fichiers_joints_form['ID_fichiers'].'&nom_fichier='.$row_Rs_fichiers_joints_form['nom_fichier']?>">&nbsp;</a> <a href="../fichiers_joints/<?php echo $row_Rs_fichiers_joints_form['nom_fichier'];  ?> " target="_blank">
				<?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']); ?>
				<strong><?php echo $nom_f ;  ?></strong></a>
				<?php
			} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
		}
		mysqli_free_result($Rs_fichiers_joints_form);
		//fin affichage des fichiers travail joints
		echo '<br />';
		if (!(strcmp($eval[1],'O'))){echo "<span style='color:red;'><strong>Evaluation : </strong></span>";};
		echo $travail[1].'<br />';
	}
	
	
	if ( $date_a_faire[2]<>''){
		echo 'A faire pour le '.$date_a_faire[2].'   ';
		//affichage fichiers travail joints
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$sql_f="SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=".$Id_Recordset1." AND type ='Travail' AND t_code_date ='".$date_a_faire[2]."' AND ind_position = 2 ORDER BY nom_fichier";
		$query_Rs_fichiers_joints_form = $sql_f;
		
		$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
		$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
		
		if ($totalRows_Rs_fichiers_joints_form<>0)
		{
			do { ?>
				<a href="fichier_supprime.php<?php echo '&ID_fichiers='.$row_Rs_fichiers_joints_form['ID_fichiers'].'&nom_fichier='.$row_Rs_fichiers_joints_form['nom_fichier']?>">&nbsp;</a> <a href="../fichiers_joints/<?php echo $row_Rs_fichiers_joints_form['nom_fichier'];  ?> " target="_blank">
				<?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']); ?>
				<strong><?php echo $nom_f ;  ?></strong></a>
				<?php
			} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
		}
		mysqli_free_result($Rs_fichiers_joints_form);
		//fin affichage des fichiers travail joints
		echo '<br />';
		if (!(strcmp($eval[2],'O'))){echo "<span style='color:red;'><strong>Evaluation : </strong></span>";};
		echo $travail[2].'<br />';
		
	}
	
	if ( $date_a_faire[3]<>''){
		echo 'A faire pour le '.$date_a_faire[3].'   ';
		//affichage fichiers travail joints
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$sql_f="SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=".$Id_Recordset1." AND type ='Travail' AND t_code_date ='".$date_a_faire[3]."' AND ind_position = 3  ORDER BY nom_fichier";
		$query_Rs_fichiers_joints_form = $sql_f;
		
		$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
		$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
		
		if ($totalRows_Rs_fichiers_joints_form<>0)
		{
			do { ?>
				<a href="fichier_supprime.php<?php echo '&ID_fichiers='.$row_Rs_fichiers_joints_form['ID_fichiers'].'&nom_fichier='.$row_Rs_fichiers_joints_form['nom_fichier']?>">&nbsp;</a> <a href="../fichiers_joints/<?php echo $row_Rs_fichiers_joints_form['nom_fichier'];  ?> " target="_blank">
				<?php $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']); ?>
				<strong><?php echo $nom_f ;  ?></strong></a>
				<?php
			} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form)); 
		}
		mysqli_free_result($Rs_fichiers_joints_form);
		//fin affichage des fichiers travail joints
		echo '<br />';
		if (!(strcmp($eval[3],'O'))){echo "<span style='color:red;'><strong>Evaluation : </strong></span>";};
		echo $travail[3].'<br />';
		
	}
	
	?>
	</span>
	<?php
	//fin travail************************************************************************************* 
	?>
	<br>
	<?php echo $row_Recordset1['activite']; ?><br>
	<?php echo $row_Recordset1['rq']; ?> </p>
	</p>
        </blockquote></td>
        </tr>
        <?php 
	$id_classe1=$row_Recordset1['classe_ID']; //
} while ($row_Recordset1 = mysqli_fetch_assoc($Recordset1)); ?>
</table>
<table width="100%"  border="0" align="center">
<tr>
<th width="50%" scope="col"> <form name="form1" method="post" action="agenda_supprime.php<?php echo $lien ?>">
<div align="left">
<p align="center"> <br>
<br>
<input name="Annuler" type="submit" id="Annuler7" onClick="MM_goToURL('window','ecrire.php<?php echo $lien ?>');return document.MM_returnValue" value="Annuler">
&nbsp;&nbsp;&nbsp;
<input name="Supp" type="submit" id="Supp6" value="Supprimer cette activit&eacute; et le travail &agrave; faire associ&eacute;" >
<input name="sup_ok" type="hidden" id="sup_ok">
<input name="gic_ID" type="hidden" id="gic_ID" value="<?php echo $_GET['gic_ID'];?>">
<input name="id_classe1" type="hidden" id="id_classe1" value="<?php echo $id_classe1;?>">
<input name="ID_agenda" type="hidden" id="ID_agenda" value="<?php echo $Id_Recordset1;?>">

</p>
</div>
</form></th>
</tr>
</table> 
<DIV id=footer></DIV>
</DIV>
</body>
</html>
<?php
mysqli_free_result($Recordset1);
mysqli_free_result($RsMat);
mysqli_free_result($RsClasse);
?>
