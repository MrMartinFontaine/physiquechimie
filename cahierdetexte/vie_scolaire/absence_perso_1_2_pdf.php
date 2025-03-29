<?php 
include "../authentification/authcheck.php" ;
if (($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>7)){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
ob_start();
if (!isset($_GET['date1'])){$datetoday=date('Ymd');$date1_form=date('d/m/Y');$jourtoday= jour_semaine(date('d/m/Y'));;
} else {
	$datetoday=substr($_GET['date1'],6,4).substr($_GET['date1'],3,2).substr($_GET['date1'],0,2);
$date1_form= $_GET['date1'];$jourtoday= jour_semaine($_GET['date1']);
};
$heure = date("H:i");
//toutes les classes
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);


		
		//On recherche les eleves pointes au cours
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsabsent = sprintf("SELECT DISTINCT eleve_ID,nom_ele,prenom_ele,nom_classe,classe_ID FROM ele_absent,ele_liste,cdt_classe WHERE  (perso1='O' OR perso2='O') AND  ele_liste.classe_ele COLLATE latin1_swedish_ci = cdt_classe.code_classe COLLATE latin1_swedish_ci  AND ele_absent.eleve_ID= ele_liste.ID_ele AND  ele_absent.code_date LIKE '%s%%'  ORDER BY classe,nom_classe,nom_ele", substr($datetoday,0,8));
		
		$Rsabsent = mysqli_query($conn_cahier_de_texte, $query_Rsabsent) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rsabsent = mysqli_fetch_assoc($Rsabsent);
		$totalRows_Rsabsent = mysqli_num_rows($Rsabsent);
		


if ($totalRows_Rsabsent<>0){
?><table   border="0" cellspacing="0" align="center">
  <tr >
    <td width="700"  colspan="2" align="center" style="border: 1px solid #CCCCCC;">
<?php echo '<p align="center">Etat des pointages des Oublis pour la journ&eacute;e du  '.$jourtoday. '  '.$date1_form;
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span align="center" style="font-size: 10px">(Etat g&eacute;n&eacute;r&eacute; le  '.jour_semaine(date('d/m/Y')).'  '.date('d/m/Y'). ' &agrave; '.$heure.')</span></p>';

?> </td>
  </tr>
  <?php $i='';
	do { 
	
	?>
    <tr  >
      <td  ><?php 
	  if (($row_Rsabsent['nom_classe']<>$i)||($i=='')){echo '<br /><strong>'.$row_Rsabsent['nom_classe'].'</strong><br />' ;};

	  ?></td>
    </tr>
    <tr>
      <td height="5" ><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- &nbsp;'.$row_Rsabsent['nom_ele'].' '.$row_Rsabsent['prenom_ele'];?>
	  </td>
	  <td>
	  <?php
	  /*Afficher heure, prof motif*/
	  
	  mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsabsent2 = sprintf("SELECT perso1,perso2,nom_prof FROM ele_absent,cdt_prof
WHERE  
(perso1='O' OR perso2='O') 
AND ele_absent.eleve_ID= %u 
AND ele_absent.classe_ID= %u 
AND cdt_prof.ID_prof=ele_absent.prof_ID
AND  ele_absent.code_date LIKE '%s%%' ORDER BY heure_debut",$row_Rsabsent['eleve_ID'],$row_Rsabsent['classe_ID'], substr($datetoday,0,8));
	  
 
	  $Rsabsent2 = mysqli_query($conn_cahier_de_texte, $query_Rsabsent2) or die(mysqli_error($conn_cahier_de_texte));
	  $row_Rsabsent2 = mysqli_fetch_assoc($Rsabsent2); 
	  do {
	  echo '<span >&nbsp;&nbsp;&nbsp;-&nbsp;';
	  
	 			if ($row_Rsabsent2['perso1']=='O'){
					echo '&nbsp;Carnet';
				};	
				if (($row_Rsabsent2['perso1']=='O')&&($row_Rsabsent2['perso2']=='O')){echo ' et ';};
				if ($row_Rsabsent2['perso2']=='O'){
					echo '&nbsp;Mat&eacute;riel';
				};
	  echo '</span>';
	  
	  } while ($row_Rsabsent2 = mysqli_fetch_assoc($Rsabsent2));  
	   
	   ?></td>
    </tr>
    <?php 
		$i= $row_Rsabsent['nom_classe'];
	} while ($row_Rsabsent = mysqli_fetch_assoc($Rsabsent)); 
	?>
</table><?php
	
	mysqli_free_result($Rsabsent);
} else {echo '<br> Aucun pointage r&eacute;alis&eacute; pour cette journ&eacute;e <br><br>';};

$content2 = ob_get_clean();
require_once('../html2pdf/html2pdf.class.php');
$html2pdf = new HTML2PDF('P','A4', 'fr');
$html2pdf->setDefaultFont('Helvetica');
$html2pdf->WriteHTML($content2);
$html2pdf->Output('absence_perso_1_2_pdf.pdf');
?>
