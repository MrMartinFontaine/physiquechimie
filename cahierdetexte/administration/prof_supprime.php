<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if ((isset($_GET['ID_prof'])) && ($_GET['ID_prof'] != "") && (isset($_POST['MM_supprime']))) {
	if ($_GET['ID_prof']<> 1) {
		
		$delete1SQL = sprintf("DELETE FROM cdt_prof WHERE ID_prof=%u",
			GetSQLValueString($_GET['ID_prof'], "int"));
		
		$delete2SQL = sprintf("DELETE FROM cdt_agenda WHERE prof_ID=%u",
			GetSQLValueString($_GET['ID_prof'], "int"));
		
		$delete3SQL = sprintf("DELETE FROM cdt_emploi_du_temps WHERE prof_ID=%u",
			GetSQLValueString($_GET['ID_prof'], "int"));	
		
		$delete4SQL = sprintf("DELETE FROM cdt_fichiers_joints WHERE prof_ID=%u",
			GetSQLValueString($_GET['ID_prof'], "int"));
		
		$delete5SQL = sprintf("DELETE FROM cdt_travail WHERE prof_ID=%u",
			GetSQLValueString($_GET['ID_prof'], "int"));	
		
                $delete6SQL = sprintf("DELETE FROM cdt_type_activite WHERE ID_prof=%u",
                        GetSQLValueString($_GET['ID_prof'], "int"));
                
                $delete7SQL = sprintf("DELETE FROM cdt_prof_principal WHERE pp_prof_ID=%u",
                        GetSQLValueString($_GET['ID_prof'], "int"));    

                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $Result1 = mysqli_query($conn_cahier_de_texte, $delete1SQL) or die(mysqli_error($conn_cahier_de_texte));
		$Result2 = mysqli_query($conn_cahier_de_texte, $delete2SQL) or die(mysqli_error($conn_cahier_de_texte));
		$Result3 = mysqli_query($conn_cahier_de_texte, $delete3SQL) or die(mysqli_error($conn_cahier_de_texte));
                $Result4 = mysqli_query($conn_cahier_de_texte, $delete4SQL) or die(mysqli_error($conn_cahier_de_texte));
                $Result5 = mysqli_query($conn_cahier_de_texte, $delete5SQL) or die(mysqli_error($conn_cahier_de_texte));
                $Result6 = mysqli_query($conn_cahier_de_texte, $delete6SQL) or die(mysqli_error($conn_cahier_de_texte));
                $Result7 = mysqli_query($conn_cahier_de_texte, $delete7SQL) or die(mysqli_error($conn_cahier_de_texte));
        
                        
                //suppression des messages de l'utilisateur
                        $query_Rsmessage =sprintf("SELECT ID_message FROM cdt_message_contenu WHERE prof_ID=%u",GetSQLValueString($_GET['ID_prof'], "int"));
                        $Rsmessage = mysqli_query($conn_cahier_de_texte, $query_Rsmessage) or die(mysqli_error($conn_cahier_de_texte));
                        $row_Rsmessage = mysqli_fetch_assoc($Rsmessage);
                        $totalRows_Rsmessage = mysqli_num_rows($Rsmessage);
			if ($totalRows_Rsmessage >0){
			
				do { //pour chaque message poste par l'utilisateur, effacer les entrees ou il apparait comme emetteur
                                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                                        
                                        //vers les eleves
                                        $delete_destinataire = sprintf("DELETE FROM cdt_message_destinataire WHERE message_ID=%u",$row_Rsmessage['ID_message']);
                                        $Result7 = mysqli_query($conn_cahier_de_texte, $delete_destinataire) or die(mysqli_error($conn_cahier_de_texte));
                                
                                        //vers les autres utilisateurs
					$delete_destinataire_profs = sprintf("DELETE FROM cdt_message_destinataire_profs WHERE message_ID=%u",$row_Rsmessage['ID_message']);
					$Result8 = mysqli_query($conn_cahier_de_texte, $delete_destinataire_profs) or die(mysqli_error($conn_cahier_de_texte));
 		
                                } while ($row_Rsmessage = mysqli_fetch_assoc($Rsmessage));
                        
                        // effacer ses messages emis
                        $delete_contenu = sprintf("DELETE FROM cdt_message_contenu WHERE prof_ID=%u",GetSQLValueString($_GET['ID_prof'], "int"));
                        $Result9 = mysqli_query($conn_cahier_de_texte, $delete_contenu) or die(mysqli_error($conn_cahier_de_texte));   
                                                };      
                        
                        // effacer de la table cdt_destinataire_profs les entrees ou il apparait comme recepteur
                        $delete_destinataire_profs = sprintf("DELETE FROM cdt_message_destinataire_profs WHERE prof_ID=%u",GetSQLValueString($_GET['ID_prof'], "int"));
                        $Result10 = mysqli_query($conn_cahier_de_texte, $delete_destinataire_profs) or die(mysqli_error($conn_cahier_de_texte));
                                        

                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_RsArc = "SELECT NumArchive FROM cdt_archive";
                $RsArc = mysqli_query($conn_cahier_de_texte, $query_RsArc) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsArc = mysqli_fetch_assoc($RsArc);
		$totalRows_RsArc = mysqli_num_rows($RsArc);
		if (!($totalRows_RsArc==0)) { 
                do { 
                        $arcID = "_save".$row_RsArc[NumArchive];
                        
                        $delete2SQL = sprintf("DELETE FROM cdt_agenda$arcID WHERE prof_ID=%u",
                                GetSQLValueString($_GET['ID_prof'], "int"));
                        
                        $delete3SQL = sprintf("DELETE FROM cdt_emploi_du_temps$arcID WHERE prof_ID=%u",
                                GetSQLValueString($_GET['ID_prof'], "int"));    
                        
                        $delete4SQL = sprintf("DELETE FROM cdt_fichiers_joints$arcID WHERE prof_ID=%u",
                                GetSQLValueString($_GET['ID_prof'], "int"));
                        
                        $delete5SQL = sprintf("DELETE FROM cdt_travail$arcID WHERE prof_ID=%u",
                                GetSQLValueString($_GET['ID_prof'], "int"));               
                        
                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result2 = mysqli_query($conn_cahier_de_texte, $delete2SQL) or die(mysqli_error($conn_cahier_de_texte));
			$Result3 = mysqli_query($conn_cahier_de_texte, $delete3SQL) or die(mysqli_error($conn_cahier_de_texte));
			$Result4 = mysqli_query($conn_cahier_de_texte, $delete4SQL) or die(mysqli_error($conn_cahier_de_texte));
			$Result5 = mysqli_query($conn_cahier_de_texte, $delete5SQL) or die(mysqli_error($conn_cahier_de_texte));
			

		} while ($row_RsArc = mysqli_fetch_assoc($RsArc));
		}
		mysqli_free_result($RsArc);
	}
	$deleteGoTo = "prof_ajout.php";
	if (isset($_SERVER['QUERY_STRING'])) {
		$deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
		$deleteGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $deleteGoTo));
	
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Suppression d'un utilisateur";
require_once "../templates/default/header.php";
?>

<HR> 
<form method="post" name="form1" action="<?php echo $editFormAction; ?>">
<p>Vous avez demand&eacute; la suppression de     </p>
<p><strong>
<?php
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsNomProf = sprintf("SELECT identite,nom_prof FROM cdt_prof WHERE ID_prof=%u",GetSQLValueString($_GET['ID_prof'], "int"));
$RsNomProf = mysqli_query($conn_cahier_de_texte, $query_RsNomProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsNomProf = mysqli_fetch_assoc($RsNomProf);
echo $row_RsNomProf['identite']=''?$row_RsNomProf['nom_prof']:$row_RsNomProf['identite'];
mysqli_free_result($RsNomProf);
?></strong></p>
<blockquote>
<blockquote>
<p align="left">Cela supprimera cet utilisateur et ses messages post&eacute;s.</p>
<p align="left">Dans le cas d'un enseignant,  son emploi du temps, ses progressions, ainsi que sa programmation des travaux &agrave; faire seront supprim&eacute;s s'ils existent.</p>
<p align="left">Cela supprimera &eacute;galement cet utilisateur dans les cahiers de textes archiv&eacute;s.</p>
<p align="left">Remarque : Ses fichiers joints resteront dans le dossier fichiers joints (en s&eacute;curit&eacute;), la suppression de fichiers via php posant probl&egrave;me sur certains serveurs. </p>
<p align="left">&nbsp;</p>
</blockquote>
</blockquote>
<p>
<input type="submit" name="Submit" value="Confirmer la suppression">
</p>
<p></p>

<p>
<input type="hidden" name="MM_supprime" value="form1">
<input type="hidden" name="ID_prof" value="<?php echo $_GET['ID_prof']; ?>">
</p>
</form>
<p>&nbsp;</p>
<p align="center"><a href="prof_ajout.php">Annuler</a></p>
<DIV id=footer></DIV>
</DIV>
</body>
</html>




















