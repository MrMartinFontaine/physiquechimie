<?php 
// On teste s'il y a eu des saisies realisees pour cette plage horaire
// Dans l'affirmative on bloque la suppression.


include "../authentification/authcheck.php";
if ($_SESSION['droits']<>2) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php'); 



//Test de validite d'un regroupement - Permet de corriger un bug d'une ancienne version
//Suite a une modification d'une plage avec regroupement, un enregistrement de la table cdt_emploi_du_temps
//a pu se retrouver avec un gic_ID=0 et un classe_ID=0 donc sans affectation de classe.
$bug=0;

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rs_bug = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE ID_emploi= %u", $_GET['ID_emploi']);
$Rs_bug = mysqli_query($conn_cahier_de_texte, $query_Rs_bug) or die(mysqli_error($conn_cahier_de_texte));
$row_Rs_bug = mysqli_fetch_assoc($Rs_bug);
if (($row_Rs_bug['classe_ID']==0)&&($row_Rs_bug['gic_ID']==0)&&($row_Rs_bug['ImportEDT']=='NON')){$bug=1;
	echo '<br /><br /><p>Suite &agrave; une modification de cette plage avec regroupement dans une version ant&eacute;rieure, cette plage n\'est affect&eacute;e actuellement &agrave; aucune classe.<br /> Il est fortement conseill&eacute; de la supprimer.</p>';?>
	<p><a href="emploi_supprime.php?ID_emploi=<?php echo $_GET['ID_emploi'].'&affiche=2';?>">Supprimer cette plage</a> </p>
	<p><a href="emploi.php?affiche=2">Ne rien faire et retourner &agrave; la gestion de mon emploi du temps</a> </p>
	<?php
};
mysqli_free_result($Rs_bug);
if ($bug==0){
	$refprof_Rs_emploi = "0";
	if (isset($_SESSION['ID_prof'])) {
		$refprof_Rs_emploi = (get_magic_quotes_gpc()) ? $_SESSION['ID_prof'] : addslashes($_SESSION['ID_prof']);
	}
	$indice_Rs_emploi = "0";
	if (isset($_GET['ID_emploi'])) {
		$indice_Rs_emploi = (get_magic_quotes_gpc()) ? $_GET['ID_emploi'] : addslashes($_GET['ID_emploi']);
	}
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	if ((isset($_GET['regroupement']))&& ($_GET['regroupement']==1)){
	$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_classe,cdt_matiere WHERE cdt_emploi_du_temps.ID_emploi=%u AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere AND cdt_emploi_du_temps.prof_ID=%u ORDER BY cdt_emploi_du_temps.jour_semaine, cdt_emploi_du_temps.heure, cdt_emploi_du_temps.semaine", $indice_Rs_emploi,$refprof_Rs_emploi);}
	else {
		$query_Rs_emploi = sprintf("SELECT * FROM cdt_emploi_du_temps,cdt_matiere WHERE cdt_emploi_du_temps.ID_emploi= %u 
			AND cdt_emploi_du_temps.matiere_ID=cdt_matiere.ID_matiere ORDER BY cdt_emploi_du_temps.jour_semaine, cdt_emploi_du_temps.heure, cdt_emploi_du_temps.semaine", $indice_Rs_emploi);
	};
	$Rs_emploi = mysqli_query($conn_cahier_de_texte, $query_Rs_emploi) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rs_emploi = mysqli_fetch_assoc($Rs_emploi);
	$totalRows_Rs_emploi = mysqli_num_rows($Rs_emploi);
	
	$exp="'%".$row_Rs_emploi['jour_semaine']."%'";
	$query_Rsagenda =sprintf("
		SELECT * FROM cdt_agenda WHERE ((prof_ID=%u) OR ((prof_ID=%u) AND (partage='O'))) AND heure=%s AND jour_pointe LIKE %s AND semaine = '%s' AND classe_ID=%u AND groupe ='%s' ",
		$_SESSION['ID_prof'],$row_Rs_emploi['prof_ID'],$row_Rs_emploi['heure'],$exp,$row_Rs_emploi['semaine'],$row_Rs_emploi['classe_ID'],$row_Rs_emploi['groupe']);
	$Rsagenda = mysqli_query($conn_cahier_de_texte, $query_Rsagenda) or die(mysqli_error($conn_cahier_de_texte));
	$row_Rsagenda = mysqli_fetch_assoc($Rsagenda);
	$totalRows_Rsagenda = mysqli_num_rows($Rsagenda);	
	
	if ($totalRows_Rsagenda==0){
		$GoTo = "../enseignant/emploi_supprime.php?ID_emploi=".$indice_Rs_emploi;
		if (isset($_GET['affiche'])){$GoTo .= "&affiche=".$_GET['affiche'];}
		header(sprintf("Location: %s", $GoTo));
	}
	else
	{
		?>
                <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
                <html>
                <head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                <LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
                </HEAD>
		<BODY>
		<p>&nbsp;</p>
		<table width="90%"  border="0" align="center" cellpadding="0" cellspacing="0" class="lire_bordure" >
		<tr class="lire_cellule_4">
		<td width="29%" class="black_police"><div align="left">
		<?php if (isset($_SESSION['identite'])){echo '<img src="../images/identite.gif" >&nbsp;'.$_SESSION['identite'];}?>
		</div></td>
		<td width="29%" class="black_police"> Suppression d'une plage de mon emploi du temps</td>
		<td width="9%" ><div align="right" > <a href="enseignant.php"><img src="../images/home-menu.gif" alt="Accueil" width="26" height="20" border="0"></a><br>
		</div></td>
		</tr>
		</table>
		<BR />
		<p>
		<?php
		echo '<p class="erreur"><br /><br /> Des fiches de cours ont d&eacute;j&agrave; &eacute;t&eacute; remplies pour cette plage horaire.<br /> <br />
		Pour supprimer cette plage, vous devez pr&eacute;alablement supprimer les saisies de ces fiches de cours.
		<br /> <br /></p>';
		
		if ($totalRows_Rsagenda==1){ echo '<p>Ces cours sont dat&eacute;s  du  :<br /></p>';} else { echo '<p>Ce cours est dat&eacute;  du  :<br /></p>';}
		do {
			
			echo '<p>'.$row_Rsagenda['jour_pointe'].'</p>';
		} while ($row_Rsagenda = mysqli_fetch_assoc($Rsagenda));
		mysqli_free_result($Rsagenda);
		?>
		<p>
		<br />
		</p>
		<p>
		<p><a href="emploi.php<?php if (isset($_GET['affiche'])){echo '?affiche='.$_GET['affiche'];}?>">Retourner &agrave; la gestion d'emploi du temps</a>
		
		</BODY>
		</HTML>
		<?php
	};
	
	mysqli_free_result($Rs_emploi);
}
?>
