<?php include "../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

if ((isset($_POST["MM_update2"])) && ($_POST["MM_update2"] == "archives_confirm")) {
	$nom_archive= str_replace(array("/", "&"), "-",$_POST['nom_archive']);
	$nom_archive= str_replace(array("\"", "'","\\"),"",$nom_archive);
	
	$insertSQL = sprintf("INSERT INTO cdt_archive (NomArchive, DateArchive) 
		VALUES ( %s,%s)",
		GetSQLValueString($nom_archive, "text"),
		GetSQLValueString(date('ymd'), "text")
		);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$ResultArch = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_RsArchiv0 = "SELECT NumArchive FROM cdt_archive WHERE NumArchive=LAST_INSERT_ID()";
        $RsArchiv0 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv0) or die(mysqli_error($conn_cahier_de_texte));
        $row_RsArchiv0 = mysqli_fetch_assoc($RsArchiv0);
        
        $num_archive="_save".$row_RsArchiv0['NumArchive'];
        mysqli_free_result($RsArchiv0);
        
        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        $query_RsArchiv2 = "CREATE TABLE cdt_groupe$num_archive AS SELECT * FROM cdt_groupe";
	$ResultArchiv2 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv2) or die(mysqli_error($conn_cahier_de_texte));
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv3 = "CREATE TABLE cdt_agenda$num_archive AS SELECT * FROM cdt_agenda";
	$ResultArchiv3 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv3) or die(mysqli_error($conn_cahier_de_texte));
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv4 = "CREATE TABLE cdt_classe$num_archive AS SELECT * FROM cdt_classe";
	$ResultArchiv4 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv4) or die(mysqli_error($conn_cahier_de_texte));
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv5 = "CREATE TABLE cdt_matiere$num_archive AS SELECT * FROM cdt_matiere";
	$ResultArchiv5 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv5) or die(mysqli_error($conn_cahier_de_texte));
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv6 = "CREATE TABLE cdt_emploi_du_temps_partage$num_archive AS SELECT * FROM cdt_emploi_du_temps_partage";
	$ResultArchiv6 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv6) or die(mysqli_error($conn_cahier_de_texte));
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv6 = "CREATE TABLE cdt_emploi_du_temps$num_archive AS SELECT * FROM cdt_emploi_du_temps";
	$ResultArchiv6 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv6) or die(mysqli_error($conn_cahier_de_texte));
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv7 = "CREATE TABLE cdt_travail$num_archive AS SELECT * FROM cdt_travail";
	$ResultArchiv7 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv7) or die(mysqli_error($conn_cahier_de_texte));
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv8 = "CREATE TABLE cdt_groupe_interclasses$num_archive AS SELECT * FROM cdt_groupe_interclasses";
	$ResultArchiv8 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv8) or die(mysqli_error($conn_cahier_de_texte));
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv9 = "CREATE TABLE cdt_groupe_interclasses_classe$num_archive AS SELECT * FROM cdt_groupe_interclasses_classe";
	$ResultArchiv9 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv9) or die(mysqli_error($conn_cahier_de_texte));
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv10 = "CREATE TABLE cdt_fichiers_joints$num_archive AS SELECT * FROM cdt_fichiers_joints";
	$ResultArchiv10 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv10) or die(mysqli_error($conn_cahier_de_texte));
	
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsArchiv11 = "CREATE TABLE cdt_remplacement$num_archive AS SELECT * FROM cdt_remplacement";
	$ResultArchiv11 = mysqli_query($conn_cahier_de_texte, $query_RsArchiv11) or die(mysqli_error($conn_cahier_de_texte));
	
	if (isset($_POST['vidageCDT_ok'])) {
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		
		if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_fichiers_joints")) { 
			$sql0="TRUNCATE `cdt_fichiers_joints`";
		$result_sql_0=(mysqli_query($conn_cahier_de_texte, $sql0)) or die('Erreur SQL !'.$sql0.mysqli_error($conn_cahier_de_texte));};
		
		if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_emploi_du_temps")) { 
                        $sql1="TRUNCATE `cdt_emploi_du_temps`";
                $result_sql_1=(mysqli_query($conn_cahier_de_texte, $sql1)) or die('Erreur SQL !'.$sql1.mysqli_error($conn_cahier_de_texte));};
                
                if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_emploi_du_temps_partage")) { 
                        $sql1="TRUNCATE `cdt_emploi_du_temps_partage`";
                $result_sql_1=(mysqli_query($conn_cahier_de_texte, $sql1)) or die('Erreur SQL !'.$sql1.mysqli_error($conn_cahier_de_texte));};
                
                if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_agenda")) { 
                        $sql2="TRUNCATE `cdt_agenda`";
                $result_sql_2=mysqli_query($conn_cahier_de_texte, $sql2) or die('Erreur SQL !'.$sql2.mysqli_error($conn_cahier_de_texte)); };
		
		if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_travail")) { 
                        $sql3="TRUNCATE `cdt_travail`";
                $result_sql_3=mysqli_query($conn_cahier_de_texte, $sql3) or die('Erreur SQL !'.$sql3.mysqli_error($conn_cahier_de_texte)); };
                
//if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_groupe")) { 
//$sql4="TRUNCATE `cdt_groupe`";
//$result_sql_4=mysqli_query($conn_cahier_de_texte, $sql4) or die('Erreur SQL !'.$sql4.mysqli_error($conn_cahier_de_texte)); 
//};
                
                if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_prof_principal")) { 
                        $sql5="TRUNCATE `cdt_prof_principal`";
		$result_sql_5=mysqli_query($conn_cahier_de_texte, $sql5) or die('Erreur SQL !'.$sql5.mysqli_error($conn_cahier_de_texte)); };
		
		if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_message_contenu")) { 
			$sql6="TRUNCATE `cdt_message_contenu`";
		$result_sql_6=mysqli_query($conn_cahier_de_texte, $sql6) or die('Erreur SQL !'.$sql6.mysqli_error($conn_cahier_de_texte)); };
		
		if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_message_destinataire")) { 
			$sql7="TRUNCATE `cdt_message_destinataire`";
		$result_sql_7=mysqli_query($conn_cahier_de_texte, $sql7) or die('Erreur SQL !'.$sql7.mysqli_error($conn_cahier_de_texte)); };
		
		if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_message_fichiers")) { 
			$sql8="TRUNCATE `cdt_message_fichiers`";
		$result_sql_8=mysqli_query($conn_cahier_de_texte, $sql8) or die('Erreur SQL !'.$sql8.mysqli_error($conn_cahier_de_texte)); };
		
		if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_evenement_contenu")) { 
			$sql9="TRUNCATE `cdt_evenement_contenu`";
		$result_sql_9=mysqli_query($conn_cahier_de_texte, $sql9) or die('Erreur SQL !'.$sql9.mysqli_error($conn_cahier_de_texte)); };
		
		if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_evenement_destinataire")) { 
			$sql10="TRUNCATE `cdt_evenement_destinataire`";
		$result_sql_10=mysqli_query($conn_cahier_de_texte, $sql10) or die('Erreur SQL !'.$sql10.mysqli_error($conn_cahier_de_texte)); };

		if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_evenement_acteur")) { 
			$sql11="TRUNCATE `cdt_evenement_acteur`";
		$result_sql_11=mysqli_query($conn_cahier_de_texte, $sql11) or die('Erreur SQL !'.$sql11.mysqli_error($conn_cahier_de_texte)); };		

		if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_message_destinataire_profs")) { 
            $sql12="TRUNCATE `cdt_message_destinataire_profs`";
        $result_sql_12=mysqli_query($conn_cahier_de_texte, $sql12) or die('Erreur SQL !'.$sql12.mysqli_error($conn_cahier_de_texte)); };
                

		 
//if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_matiere")) { 
//$sql12="TRUNCATE `cdt_matiere`";
//$result_sql_12=mysqli_query($conn_cahier_de_texte, $sql12) or die('Erreur SQL !'.$sql12.mysqli_error($conn_cahier_de_texte)); 
//};
                
//if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_classe")) { 
//$sql13="TRUNCATE `cdt_classe`";
//$result_sql_13=mysqli_query($conn_cahier_de_texte, $sql13) or die('Erreur SQL !'.$sql13.mysqli_error($conn_cahier_de_texte)); 
//};
                
                if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_groupe_interclasses")) { 
                        $sql14="TRUNCATE `cdt_groupe_interclasses`";
		$result_sql_14=mysqli_query($conn_cahier_de_texte, $sql14) or die('Erreur SQL !'.$sql14.mysqli_error($conn_cahier_de_texte)); };
		
		if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_groupe_interclasses_classe")) { 
			$sql15="TRUNCATE `cdt_groupe_interclasses_classe`";
		$result_sql_15=mysqli_query($conn_cahier_de_texte, $sql15) or die('Erreur SQL !'.$sql15.mysqli_error($conn_cahier_de_texte)); };
		
		if (mysqli_query($conn_cahier_de_texte, "SELECT * from cdt_remplacement")) { 
			$sql16="TRUNCATE `cdt_remplacement`";
		$result_sql_16=mysqli_query($conn_cahier_de_texte, $sql16) or die('Erreur SQL !'.$sql16.mysqli_error($conn_cahier_de_texte)); };
		
		$updateSQL5 = "UPDATE cdt_prof SET id_remplace=0 WHERE id_remplace!=0";
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $Result5 = mysqli_query($conn_cahier_de_texte, $updateSQL5) or die(mysqli_error($conn_cahier_de_texte));
		
		$updateSQL5 = "UPDATE cdt_prof SET date_maj='0000-00-00' WHERE droits=2";
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $Result5 = mysqli_query($conn_cahier_de_texte, $updateSQL5) or die(mysqli_error($conn_cahier_de_texte));
                
                $updateSQL5 = "UPDATE cdt_prof SET stop_cdt = 'N'";
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $Result5 = mysqli_query($conn_cahier_de_texte, $updateSQL5) or die(mysqli_error($conn_cahier_de_texte));
				
	            $updateSQL7 = "UPDATE `cdt_prof` SET `id_etat` = '0', `id_remplace` = '0' ";
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $Result5 = mysqli_query($conn_cahier_de_texte, $updateSQL7) or die(mysqli_error($conn_cahier_de_texte));			
				
				
        };
        
        $updateGoTo = "index.php";
	header(sprintf("Location: %s", $updateGoTo));
	
}


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsArchiv = "SELECT * FROM cdt_archive ORDER BY cdt_archive.NumArchive ASC";
$RsArchiv = mysqli_query($conn_cahier_de_texte, $query_RsArchiv) or die(mysqli_error($conn_cahier_de_texte));
$row_RsArchiv = mysqli_fetch_assoc($RsArchiv);
$totalRows_RsArchiv = mysqli_num_rows($RsArchiv);
?>

<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}

//-->
</script>
<style type="text/css">
<!--
.Style70 {font-size: small;color: #000066;}
.Style71 {
	color: #FF0000;
	font-weight: bold;
}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Archivage du Cahier de Textes";
require_once "../templates/default/header.php";

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "archives")) {
	$nom_archive= str_replace(array("/", "&"), "-",GetSQLValueString($_POST['nom_archive'], "text"));
	$RequeteDoublon = sprintf("SELECT NomArchive FROM cdt_archive WHERE NomArchive=%s LIMIT 1",$nom_archive);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$ResultDoublon = mysqli_query($conn_cahier_de_texte, $RequeteDoublon) or die(mysqli_error($conn_cahier_de_texte));
	$totalRows_ResultDoublon = mysqli_num_rows($ResultDoublon);
        
        if ($totalRows_ResultDoublon==1) {
                ?>
                <BR></BR>
                <p class="erreur">L'archive que vous souhaitez cr&eacute;er <i><b><?php echo str_replace(array("\"", "'","\\"),"",$nom_archive); ?></b></i> porte le m&ecirc;me nom qu'une archive d&eacute;j&agrave; existante.<BR></BR> Veuillez changer son nom.</p>
                <p class="Style70">&nbsp;</p>
                <p class="Style70"><a href="archivage.php">Retour &agrave; l'archivage</a> </p>
                <p>&nbsp;</p>
                <p><a href="index.php">Retour au Menu Administrateur</a> </p>
                
                <?php
        }
        else {
		?>
		<form action="archivage.php" method="post">
		<blockquote>
		<p align="left">&nbsp; </p>
		<p align="left">Vous avez demand&eacute; la cr&eacute;ation d'une nouvelle archive nomm&eacute;e <?php echo $nom_archive;?>.</p>
		</blockquote>
		<p>
		<?php
                if (isset($_POST['vidageCDT'])) {
                        ?>
                        </p>
                        <div align="center"><fieldset style="width : 90%">
                        <legend align="top" class="Style71">Vidage du cahier de textes</legend>
                        <blockquote>
                        <p align="left">Vous avez demand&eacute; &eacute;galement le vidage du cahier de textes de l'ann&eacute;e en cours, en conservant uniquement les profs utilisateurs et les progressions.</p>
                        
                        <?php 
                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $query_Rsstopcdt = sprintf("SELECT identite, nom_prof FROM cdt_prof WHERE stop_cdt='O' AND (droits='2' OR droits='8')");
                        $Rsstopcdt = mysqli_query($conn_cahier_de_texte, $query_Rsstopcdt) or die(mysqli_error($conn_cahier_de_texte));
                        $totalRows_Rsstopcdt = mysqli_num_rows($Rsstopcdt);
                        if ($totalRows_Rsstopcdt>0) { ?>
                                <p align="left" ><span class="Style71">Attention : </span>Les coll&egrave;gues dont les noms suivent ont vu leur cahier de textes interdits de publication par la direction de l'&eacute;tablissement. 
                                Par le biais du vidage du cahier de textes de l'ann&eacute;e en cours, ces coll&egrave;gues verront accessibles de nouveau leur cahier de textes pour la nouvelle ann&eacute;e. Si ce n'est pas 
                                votre souhait, pensez &agrave; annuler l'op&eacute;ration.</p>
                                <p align="left">Liste des coll&egrave;gues dont le cahier de textes &eacute;tait interdit de publication :</p>
                                <ul>
                                <?php while ($row_Rsstopcdt = mysqli_fetch_assoc($Rsstopcdt)) {
                                        echo '<li>';
                                        echo ((($row_Rsstopcdt['identite']=='')||($row_Rsstopcdt['identite']==NULL))?$row_Rsstopcdt['nom_prof']:$row_Rsstopcdt['identite']);
                                        echo 'ii</li>';
                                } ;
                                echo '</ul>';
                        };
                        mysqli_free_result($Rsstopcdt);
                        ?>
                        
                        </fieldset></div>
                        <p>&nbsp;</p>
                        <p>&nbsp; </p>
                        <input type="hidden" name="vidageCDT_ok" value="<?php echo $_POST['vidageCDT'] ?>">
                <?php }; ?>
                <p>
                <input type="hidden" name="MM_update2" value="archives_confirm">
                <input type="hidden" name="nom_archive" value="<?php echo $nom_archive; ?>">
                
                <input type="submit" name="verif" value="Confirmer votre choix" >
                </p>
                <p class="Style70">&nbsp;</p>
                <p class="Style70"><a href="index.php">Annuler l'op&eacute;ration</a> </p>
                </form>
                <?php
	}
}
else
{
        ?>
        
        <HR>
        <p align="center"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Cr&eacute;er une archive</b></font></p>
        <blockquote>
        <p align="left">Cette op&eacute;ration g&eacute;n&eacute;ralement effectu&eacute;e en fin d'ann&eacute;e scolaire, permettra &agrave; chaque enseignant 
        de disposer des cahiers de textes des ann&eacute;es pr&eacute;c&eacute;dentes 
        depuis son menu via le lien Consulter / Imprimer, 
        ou depuis l'ic&ocirc;ne habituelle &quot;Consultation d'archives&quot; en haut de calendrier.</p>
        </blockquote>
        <form name="archives" method="post" action="archivage.php" >
        <br>
        <table width="95%" align="center" class="lire_cellule_22" >
        <tr >
        <td width="50%" class="lire_cellule_22" >
        <p>&nbsp;</p>
        <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b>Nom 
        de l'archive &agrave; cr&eacute;er : </b></font></p>
        <p>&nbsp;</p>
        <td width="45%" class="lire_cellule_22" >
        <p>&nbsp;</p>
        <input name="nom_archive" type="text" id="nom_archive"  size="50" maxlength="80" value="Archives 2012-2013 (par exemple)">
        <p>&nbsp;</p></td>
        </tr>
        <tr>
        <td colspan="2" class="lire_cellule_22" >
        <p align="center">
        <label>
        <div align="left"><font size="2" face="Verdana, Arial, Helvetica, sans-serif"><b><br>
        <br>
        Vidage du cahier de textes de l'ann&eacute;e en cours.</b><br>
        <br>
          En cochant cette case, il y aura &eacute;galement vidage du cahier de textes. Vous conserverez alors uniquement pour d&eacute;buter la nouvelle ann&eacute;e, les tables  mati&egrave;res, classes, profs utilisateurs, progression et groupes et aurez vid&eacute; les autres tables (emploi du temps, professeurs principaux, messages aux &eacute;l&egrave;ves et aux enseignants ).<b><br>
          </b><span class="erreur">ATTENTION - Cocher cette option uniquement en cas de nouvelle ann&eacute;e scolaire. </span></font>
        <input type="checkbox" name="vidageCDT" size="14">
        </label>
        <p>&nbsp;</p>    </tr>
        <tr>
        <td colspan="2" class="lire_cellule_22" >
        <BR></BR>
        <p align="center"><input type="submit" name="verif" value="Valider la cr&eacute;ation de l'archive" >
        </p>    <BR></BR>  </td> 
        </tr>
        </table>
        <input type="hidden" name="MM_update" value="archives">
        </form>
        <p class="erreur Style70"><a href="index.php">Annuler l'op&eacute;ration</a> </p>
        <p>&nbsp; </p>        </td>
        <p align="center"><strong>Etat des archives existantes</strong></p>
        <?php if ($totalRows_RsArchiv==0){echo ' <div class="erreur">Aucune archive actuellement </div>';
        } else {
        ?>
                <table border="1" align="center" cellpadding="0" cellspacing="0" class="bordure">
                <tr>
                <td class="Style6">Nom de l'archive</td>
        	<td class="Style6">Date de son archivage &nbsp;</td>
        	<td class="Style6">Modifier &nbsp;</td>
        	<td class="Style6">Supprimer &nbsp;</td>
        	</tr>
        	<?php $date_arch='';
        	do {
        		$date_arch=substr($row_RsArchiv['DateArchive'],8,2).'/'.substr($row_RsArchiv['DateArchive'],5,2).'/'.substr($row_RsArchiv['DateArchive'],0,4);
        		
        		?>
        		<tr class="tab_detail" >
        		<td class="tab_detail_gris"><?php echo '&nbsp;'.$row_RsArchiv['NomArchive'].'&nbsp;'; ?></td>
        		<td  class="tab_detail_gris"><?php echo '&nbsp;'.jour_semaine($date_arch).' '.$date_arch.'&nbsp;'; ?></td>
        		<td class="tab_detail_gris" align="center"><img src="../images/button_edit.png" style="cursor:pointer" alt="Modifier le nom de l'archive <?php echo $row_RsArchiv['NomArchive']; ?>" title="Modifier le nom de l'archive <?php echo $row_RsArchiv['NomArchive']; ?>" width="12" height="13" onClick="MM_goToURL('window','archivage_modif.php?archID=<?php echo $row_RsArchiv['NumArchive']; ?>');return document.MM_returnValue"></td>
        		<td class="tab_detail_gris" align="center"><img src="../images/ed_delete.gif" style="cursor:pointer" alt="Supprimer l'archive <?php echo $row_RsArchiv['NomArchive']; ?>" title="Supprimer l'archive <?php echo $row_RsArchiv['NomArchive']; ?>" width="11" height="13" onClick="if (confirm('\312tes-vous s\373r de vouloir supprimer l\'archive  <?php echo $row_RsArchiv['NomArchive']; ?> ?')) {MM_goToURL('window','archivage_supp.php?archID=<?php echo $row_RsArchiv['NumArchive']; ?>');return document.MM_returnValue;}"></td>
        		</tr>
        	<?php } while ($row_RsArchiv = mysqli_fetch_assoc($RsArchiv)); ?>
        	</table>
        <?php };?>
        <p>&nbsp;</p>
        <p><a href="index.php">Retour au Menu Administrateur</a> </p>
        <p>&nbsp;</p>
        
        
        
        <?php 
};
mysqli_free_result($RsArchiv);
?>
<DIV id=footer></DIV>
</DIV>
</body>
</html>
