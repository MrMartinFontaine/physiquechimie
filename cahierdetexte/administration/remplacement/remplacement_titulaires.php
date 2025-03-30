<?php 

include "../../authentification/authcheck.php" ;

if ($_SESSION['droits']<>1 && $_SESSION['droits']<>3) { header("Location: ../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');

$erreur5='';

if ((isset($_POST['MM_insert']))&&($_POST['MM_insert'] == "form1"))
{

// recuperer les 3 dates : aujourd'hui hier et de fin d'annee.
$today=date("Y-m-j");
$hier=date("Y-m-d", time() - 3600 * 24);

//recuperation de la date de fin de l'annee scolaire
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_read = "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='date_fin_annee' LIMIT 1";
$result_read = mysqli_query($conn_cahier_de_texte, $query_read);
$row = mysqli_fetch_row($result_read);
$date_fin_annee = $row[0];
mysqli_free_result($result_read);

//Cas ou la date n'a pas ete saisie via le menu administrateur
if($date_fin_annee ==''){
//initialisation date fin annee au 13/07
	if(date('n') >= 8 && date('n') <=12){$date_fin_annee="13/07/".(date('Y')+1);} else {$date_fin_annee="13/07/".date('Y');};
};
// passer la date du format jj/mm/yyyy au format yyyy-mm-jj
$date_fin_annee=str_replace("/","-",$date_fin_annee);
$dateexplode=explode("-",$date_fin_annee);
$datefin=$dateexplode[2]."-".$dateexplode[1]."-".$dateexplode[0];

$temp=$_POST['prof'];

// il faut selectionner les profs qui ont encore des remplacants et mettre 3 dans id_etat pour ne pas changer le coef
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsTitu = sprintf("SELECT * FROM cdt_prof WHERE (id_etat=2 AND ID_remplace!=0) AND ancien_prof='N'");
				$RsTitu = mysqli_query($conn_cahier_de_texte, $query_RsTitu) or die(mysqli_error($conn_cahier_de_texte)); 
				$row_RsTitu = mysqli_fetch_assoc($RsTitu);
				$totalRows_RsTitu = mysqli_num_rows($RsTitu);
	// si $totalrows > 0 c'est qu'il existe un prof qui a encore un remplacant le code du prof est id_remplace
				if ($totalRows_RsTitu >0)
				{
				$erreur5="<br />Au moins un enseignant poss&egrave;de encore un rempla&ccedil;ant";
				do {
					$sql=sprintf("UPDATE cdt_prof SET id_etat='3' WHERE id_prof=%u",$row_RsTitu['id_remplace']);
					$Rssql = mysqli_query($conn_cahier_de_texte, $sql) or die(mysqli_error($conn_cahier_de_texte)); 
					} while ($row_RsTitu = mysqli_fetch_assoc($RsTitu));

				}
				
	// selection des profs titulaire		
	          mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$query_RsTitu = sprintf("SELECT * FROM cdt_prof WHERE (droits='2' AND (id_etat='0' OR id_etat='1') AND ancien_prof='N')");
				$RsTitu = mysqli_query($conn_cahier_de_texte, $query_RsTitu) or die(mysqli_error($conn_cahier_de_texte)); 
				$row_RsTitu = mysqli_fetch_assoc($RsTitu);
				$totalRows_RsTitu = mysqli_num_rows($RsTitu);   
			do {
				$temp2=$row_RsTitu['ID_prof'];
				$temp3=$temp[$temp2][0];
				if ($temp3=="oui" AND $row_RsTitu['id_etat']=="1")
				{ 
				// le prof est revenu et il n'a pas d'autre remplacant
				$query_Rschange=sprintf(" UPDATE cdt_prof SET id_etat='0' WHERE ID_prof=%u",$row_RsTitu['ID_prof']);
				$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));
				
				$query_Rschange2=sprintf(" UPDATE cdt_prof SET date_declare_absent='0000-00-00' WHERE ID_prof=%u",$row_RsTitu['ID_prof']);
				$Rschange2 = mysqli_query($conn_cahier_de_texte, $query_Rschange2) or die(mysqli_error($conn_cahier_de_texte));		
				
				
				// Il faut supprimer l'edt du remplacant et copier le prof_ID du titulaire a la place de celui du remplacant dans toutes les tables.
				
				//l'idee est de gerer avec ce verrou les copies d'edt... 
				//si remplacant_1 a deja pris des plages, alors remplacant_2 recoit uniquement les plages restantes dans sa copie. 
				//En stand by pour le moment
				$query_RsRetour=sprintf(" UPDATE cdt_emploi_du_temps SET verrou_remplace=0 WHERE prof_ID=%u ",$row_RsTitu['ID_prof']);
				$RsRetour = mysqli_query($conn_cahier_de_texte, $query_RsRetour) or die(mysqli_error($conn_cahier_de_texte));				
				
				}
				
				$ch='date_declare_absent'.$row_RsTitu['ID_prof'];				

				if ($temp3=="non" AND $row_RsTitu['id_etat']=="0")
				{
				// il faut mettre le prof absent
				$query_Rschange=sprintf(" UPDATE cdt_prof SET id_etat='1' WHERE ID_prof=%u",$row_RsTitu['ID_prof']);
				$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));
				$query_RsDepart=sprintf(" UPDATE cdt_emploi_du_temps SET verrou_remplace=1 WHERE prof_ID=%u ",$row_RsTitu['ID_prof']);
				$RsDepart = mysqli_query($conn_cahier_de_texte, $query_RsDepart) or die(mysqli_error($conn_cahier_de_texte));
				
				//on enregistre dans cdt_prof cette date de declaration d'absence

				if ($_POST[$ch]==''){$date_declare_absent=$today;} 
				else {
				$date_declare_absent=substr($_POST[$ch],6,4).'-'.substr($_POST[$ch],3,2).'-'.substr($_POST[$ch],0,2);
				};
				$query_Rschange_date=sprintf(" UPDATE cdt_prof SET date_declare_absent='%s' WHERE ID_prof=%u",$date_declare_absent,$row_RsTitu['ID_prof']);
				$Rschange_date = mysqli_query($conn_cahier_de_texte, $query_Rschange_date) or die(mysqli_error($conn_cahier_de_texte));
					
				};
			    
				
				if ($temp3=="non" AND $row_RsTitu['id_etat']=="1")
				{
							
				//il y a modification eventuelle de date de declaration d'absence

				if ($_POST[$ch]==''){$date_declare_absent=$today;} 
				else {
				$date_declare_absent=substr($_POST[$ch],6,4).'-'.substr($_POST[$ch],3,2).'-'.substr($_POST[$ch],0,2);
				};
				$query_Rschange_date=sprintf(" UPDATE cdt_prof SET date_declare_absent='%s' WHERE ID_prof=%u",$date_declare_absent,$row_RsTitu['ID_prof']);
				$Rschange_date = mysqli_query($conn_cahier_de_texte, $query_Rschange_date) or die(mysqli_error($conn_cahier_de_texte));
					
				}
			
			
			} while ($row_RsTitu = mysqli_fetch_assoc($RsTitu));

	
// penser a remettre a 1 les profs qui ont 3
					$sql=sprintf("UPDATE cdt_prof SET id_etat='1' WHERE id_etat='3'");
					$Rssql = mysqli_query($conn_cahier_de_texte, $sql) or die(mysqli_error($conn_cahier_de_texte)); 
}


mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProf = "SELECT * FROM cdt_prof WHERE droits='2' AND (id_etat='0' OR id_etat='1') AND ancien_prof='N' ORDER BY nom_prof ASC";
$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf = mysqli_fetch_assoc($RsProf);
$totalRows_RsProf = mysqli_num_rows($RsProf);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
<link type="text/css" href="../../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />
<script type="text/javascript" src="../../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../../jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="../../jscripts/jquery-ui.datepicker-fr.js"></script>
<style type="text/css">
<!--
.Style74 {font-size: 8pt;
	text-indent: 10px;
	color:#0000FF;
}
.Style75 {color: #FF0000}
-->
</style>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description='Gestion des remplacements - Titulaires';
require_once "../../templates/default/header.php";
?>
<div class="erreur">
  <?php if($erreur5!=''){echo $erreur5.'<br/>' ;}?>
</div>
<br />
<form action="remplacement_titulaires.php" method="POST">
  <table width="95%" border="0">
    <tr>
      <td><div align="center">
        <input type="submit" name="Submit" value="Enregistrer mes modifications" />
      </div></td>
      <td width="25"><div align="right"><a href="remplacement.php"><img src="../../images/home-menu.gif" width="26" height="20" border="0"></a></div></td>
    </tr>
  </table>
  

  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="5">
    <tr>
      <td class="Style6"><div align="center">Nom du Professeur</div></td>
      <td class="Style6"><div align="center">Pr&eacute;sent</div></td>
      <td class="Style6"><div align="center">Absent</div></td>
      <td class="Style6"><div align="center">Date d&eacute;but d'absence </div></td>
    </tr>
    <?php 



	do { ?>
      <tr>
        <td class="tab_detail"><div align="left"
	<?php
	if ($row_RsProf['id_etat']=='1'){ echo 'class="Style75"';};
	?>
	>
            <?php if ($row_RsProf['identite']<>''){echo $row_RsProf['identite'];} else {echo $row_RsProf['nom_prof'];}?>
          </div></td>
        <td class="tab_detail"><div align="center">
            <input type="radio" name="prof[<?php echo $row_RsProf['ID_prof'];?>][]" value="oui"<?php if ($row_RsProf['id_etat']=='0') {echo " checked ";}?>>
          </div></td>
        <td class="tab_detail"><div align="center">
            <input type="radio" name="prof[<?php echo $row_RsProf['ID_prof'];?>][]" value="non"<?php if ($row_RsProf['id_etat']=='1') {echo " checked ";}?>>
          </div></td>
        <td class="tab_detail"><div align="center">
            <script>
         $(function() {
        	$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
			$.datepicker.setDefaults($.datepicker.regional['fr']);
        	$('#date_declare_absent<?php echo $row_RsProf['ID_prof'];?>').datepicker({firstDay:1});
        });
        </script>
            <?php
		if ($row_RsProf['date_declare_absent']<>'0000-00-00'){
		$date1_form=substr($row_RsProf['date_declare_absent'],8,2).'-'.substr($row_RsProf['date_declare_absent'],5,2).'-'.substr($row_RsProf['date_declare_absent'],0,4);}else {$date1_form='';};?>
            <input name='date_declare_absent<?php echo $row_RsProf['ID_prof'];?>' type='text' id='date_declare_absent<?php echo $row_RsProf['ID_prof'];?>' value="<?php 
echo $date1_form;?>" size="10"/>
          </div></td>
      </tr>
      <?php } while ($row_RsProf = mysqli_fetch_assoc($RsProf)); ?>
  </table>
  <p align="center">&nbsp;</p>
  <input type="submit" name="Submit" value="Enregistrer mes modifications" />
  <input type="hidden" name="MM_insert" value="form1">
  <p align="left" class="Style74">&nbsp;</p>
  <p align="center" class="Style74"><a href="remplacement.php">Retour au menu Gestion des remplacements</a> </p>
  </td>
  </tr>
  </table>
</form>
<DIV id=footer>
  <p class="auteur"><a href="../contribution.php" class="auteur">Pierre Lemaitre 
    - St L&ocirc; (France) </a></p>
</DIV>
</body>
</html>
<?php
mysqli_free_result($RsProf);
?>
