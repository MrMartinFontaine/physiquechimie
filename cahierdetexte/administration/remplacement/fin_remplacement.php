<?php 

include "../../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1 && $_SESSION['droits']<>3) { header("Location: ../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');

$erreur5='';
$today=date("Y-m-j");

		$ide_remplacant=GetSQLValueString($_GET['prof'], "int");
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsRemplacant = sprintf("SELECT * FROM cdt_prof where ID_prof=".$ide_remplacant);
		$RsRemplacant = mysqli_query($conn_cahier_de_texte, $query_RsRemplacant) or die(mysqli_error($conn_cahier_de_texte));
                $row_RsRemplacant = mysqli_fetch_assoc($RsRemplacant);
                $totalRows_RsRemplacant = mysqli_num_rows($RsRemplacant);

//prendre le dernier enregistrement de la table cdt_remplacement pour ce remplacant
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsRemplacement = sprintf("SELECT * FROM cdt_remplacement WHERE remplacant_ID=%u ORDER BY date_creation_remplace DESC LIMIT 1",$ide_remplacant);
$RsRemplacement = mysqli_query($conn_cahier_de_texte, $query_RsRemplacement) or die(mysqli_error($conn_cahier_de_texte));
$row_RsRemplacement = mysqli_fetch_assoc($RsRemplacement);

$ide_remplace=$row_RsRemplacement['titulaire_ID']; 

//fin du remplacement on met les dates a aujourd'hui et on met id_remplace = 0
                //on cloture ses plages
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_RsDepart=sprintf("UPDATE cdt_emploi_du_temps SET edt_exist_fin='%s' WHERE prof_ID=%u",$today,$ide_remplacant);
                $RsDepart = mysqli_query($conn_cahier_de_texte, $query_RsDepart) or die(mysqli_error($conn_cahier_de_texte));  
                //
                $query_RsDepart1=sprintf("UPDATE cdt_prof SET id_remplace=0 WHERE ID_prof=%u",$ide_remplacant);         
                $RsDepart1 = mysqli_query($conn_cahier_de_texte, $query_RsDepart1) or die(mysqli_error($conn_cahier_de_texte));        

//inserer dans cdt_agenda un engistrement informatif de fin de remplacement
$info_fin_remplacement='<p align="center" class="erreur">Fin du remplacement effectu&eacute; par '.$row_RsRemplacant['identite'].'</p>';
$cd=date('Ymd').'0';
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsInfo_agenda_remplace = sprintf("INSERT INTO cdt_agenda (prof_ID, theme_activ,activite,code_date)
                                VALUES (%u,'%s','%s',%s)",
                                $ide_remplacant,'Remplacement', $info_fin_remplacement,$cd);

$RsInfo_agenda_remplace = mysqli_query($conn_cahier_de_texte, $query_RsInfo_agenda_remplace) or die(mysqli_error($conn_cahier_de_texte));
$UID=mysqli_insert_id($conn_cahier_de_texte);

//mettre la date de fin dans cdt_remplacement
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsRemplacement2 = sprintf("UPDATE cdt_remplacement SET date_fin_remplace='%s', ref_fin_agenda_ID=%u WHERE ID_remplace=%u",$today,$UID,$row_RsRemplacement['ID_remplace']);
$RsRemplacement2 = mysqli_query($conn_cahier_de_texte, $query_RsRemplacement2) or die(mysqli_error($conn_cahier_de_texte));


//Re-affectation des saisies au titulaire. $ide_remplacant > $ide_remplace
//Copie du prof_ID du remplacant dans les tables correspondantes du titulaire

$query_Rschange=sprintf(" UPDATE cdt_agenda SET prof_ID=%u WHERE prof_ID=%u",$ide_remplace,$ide_remplacant);
$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));

$query_Rschange=sprintf(" UPDATE cdt_fichiers_joints SET prof_ID=%u WHERE prof_ID=%u",$ide_remplace,$ide_remplacant);
$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));

$query_Rschange=sprintf(" UPDATE cdt_evenement_contenu SET prof_ID=%u WHERE prof_ID=%u",$ide_remplace,$ide_remplacant);
$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));

$query_Rschange=sprintf(" UPDATE cdt_groupe_interclasses SET prof_ID=%u WHERE prof_ID=%u",$ide_remplace,$ide_remplacant);
$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));

$query_Rschange=sprintf(" UPDATE cdt_invite SET prof_ID=%u WHERE prof_ID=%u",$ide_remplace,$ide_remplacant);
$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));

$query_Rschange=sprintf(" UPDATE cdt_message_contenu SET prof_ID=%u WHERE prof_ID=%u",$ide_remplace,$ide_remplacant);
$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));

$query_Rschange=sprintf(" UPDATE cdt_message_destinataire_profs SET prof_ID=%u WHERE prof_ID=%u",$ide_remplace,$ide_remplacant);
$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));

$query_Rschange=sprintf(" UPDATE cdt_message_fichiers SET prof_ID=%u WHERE prof_ID=%u",$ide_remplace,$ide_remplacant);
$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));

$query_Rschange=sprintf(" UPDATE cdt_message_modif SET prof_ID=%u WHERE prof_ID=%u",$ide_remplace,$ide_remplacant);
$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));

$query_Rschange=sprintf(" UPDATE cdt_progression SET prof_ID=%u WHERE prof_ID=%u",$ide_remplace,$ide_remplacant);
$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));

$query_Rschange=sprintf(" UPDATE cdt_travail SET prof_ID=%u WHERE prof_ID=%u",$ide_remplace,$ide_remplacant);
$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));

$query_Rschange=sprintf(" UPDATE cdt_type_activite SET ID_prof=%u WHERE ID_prof=%u",$ide_remplace,$ide_remplacant);
$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));

if ( (isset($_SESSION['module_absence'])) && ($_SESSION['module_absence']=='Oui'))      {
$query_Rschange=sprintf(" UPDATE ele_absent SET prof_ID=%u WHERE prof_ID=%u",$ide_remplace,$ide_remplacant);
$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));
        }
        
mysqli_free_result($RsRemplacant);
mysqli_free_result($RsRemplacement);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsRemplacement = "SELECT nom_prof,identite FROM cdt_prof WHERE ID_prof=".$ide_remplacant." LIMIT 1";
$RsRemplacement = mysqli_query($conn_cahier_de_texte, $query_RsRemplacement) or die(mysqli_error($conn_cahier_de_texte));
$row_RsRemplacement = mysqli_fetch_assoc($RsRemplacement);
$identite=$row_RsRemplacement['identite']==''?$row_RsRemplacement['nom_prof']:$row_RsRemplacement['identite'];
$erreur5 = " Fin du remplacement de ".$identite; 

mysqli_free_result($RsRemplacement);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
<style type="text/css">
<!--
.Style74 {font-size: 8pt;
	text-indent: 10px;
	color:#0000FF;
}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
  <?php 
$header_description='Fin du remplacement de '.$identite;
require_once "../../templates/default/header.php";
?> <br />
<HR>
  <p align="center">La fin du remplacement ne supprime pas le compte du suppl&eacute;ant.<br> Le suppl&eacute;ant peut encore se connecter, modifier le cahier de textes correspondant &agrave; la p&eacute;riode de son remplacement.<br>La fin du remplacement sera effective &agrave; partir de demain.</p><p>Seul l&#39;administrateur peut supprimer le compte. <br /></p>

        
        <tr></br><div class="erreur"><?php if($erreur5!=''){echo $erreur5.'<br/><br/>' ;}?></div>
        <p align="left" class="Style74">&nbsp;</p>
		<p align="center" class="Style74"><a href="remplacement_remplacants.php">Retour Gestion des rempla&ccedil;ants</a></p></td>
	
    </tr>
  </table>
<DIV id=footer>
</DIV>
</body>
</html>
<?php



?>
