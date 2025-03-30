<?php 
include "../authentification/authcheck.php" ;
if ($_SESSION['droits']<>4) { header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');

$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}




// Visa local
// Report de la date du jour dans date_maj du prof
// Report date du jour dans pour la derniere date saisie dans chaque couple matiere/classe
if ((isset($_POST["MM_update4"])) && ($_POST["MM_update4"] == "form4")) {
	$partiel=FALSE;
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsProf = "SELECT * FROM cdt_prof WHERE droits=2";
	$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsProf = mysqli_fetch_assoc($RsProf);
	do { //RAZ des visas partiel d'un prof
		if (isset($_POST['RAZ'.$row_RsProf['ID_prof']]))  { 
			$partiel=TRUE;
			
			$raz='raz2'.$row_RsProf['ID_prof'];
			$updateSQL5 = sprintf("UPDATE cdt_prof SET date_maj='0000-00-00' WHERE ID_prof=%u",$_POST[$raz]);
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result5 = mysqli_query($conn_cahier_de_texte, $updateSQL5) or die(mysqli_error($conn_cahier_de_texte));
			
			$updateSQL6 = sprintf("UPDATE cdt_agenda SET date_visa='0000-00-00' WHERE prof_ID=%u",$_POST[$raz]);
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$Result6 = mysqli_query($conn_cahier_de_texte, $updateSQL6) or die(mysqli_error($conn_cahier_de_texte));
		};
		
	} while (($row_RsProf = mysqli_fetch_assoc($RsProf))&(!($partiel)));
	
	if (!($partiel)) {
		mysqli_data_seek($RsProf,0);
		
		do { //Activation des visas sur tous les CDT d'un prof
			if (isset($_POST['ALL'.$row_RsProf['ID_prof']]))  {
				$partiel=TRUE;
				
				$all='all2'.$row_RsProf['ID_prof'];
				
				$updateSQL6 = sprintf("UPDATE cdt_agenda SET date_visa='0000-00-00' WHERE prof_ID=%u",$_POST[$all]);
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				$Result6 = mysqli_query($conn_cahier_de_texte, $updateSQL6) or die(mysqli_error($conn_cahier_de_texte));
				
				$updateSQL4 = sprintf("UPDATE cdt_prof SET date_maj='%s' WHERE ID_prof = %s",date('Y-m-d'),$_POST[$all]);
				$Result4 = mysqli_query($conn_cahier_de_texte, $updateSQL4) or die(mysqli_error($conn_cahier_de_texte));
				
				// et maintenant prendre chacune de ses matieres/classes
				
				
				$query_Rs = sprintf("SELECT MAX(code_date),ID_agenda, nom_classe, nom_matiere, cdt_classe.ID_classe, cdt_matiere.ID_matiere
					FROM cdt_agenda, cdt_classe, cdt_matiere
					WHERE prof_ID=%u
					AND cdt_classe.ID_classe = cdt_agenda.classe_ID
					AND cdt_matiere.ID_matiere = cdt_agenda.matiere_ID
					GROUP BY ID_classe, ID_matiere", $_POST[$all]);
				
				$Rs = mysqli_query($conn_cahier_de_texte, $query_Rs) or die(mysqli_error($conn_cahier_de_texte));
				$row_Rs= mysqli_fetch_assoc($Rs);
				//Boucle des matieres-classes
				
				do {
					$updateSQLvisa=sprintf("UPDATE cdt_agenda SET date_visa= '%s' WHERE code_date=%s AND prof_ID=%u",date('Y-m-d'),$row_Rs['MAX(code_date)'],$_POST[$all]);
					$Result5 = mysqli_query($conn_cahier_de_texte, $updateSQLvisa) or die(mysqli_error($conn_cahier_de_texte));
				} while ($row_Rs = mysqli_fetch_assoc($Rs));
				mysqli_free_result($Rs);
				
				//fin boucle des matieres-classes
				//fin des matiere classe d'un prof		
				
			};
			
		} while (($row_RsProf = mysqli_fetch_assoc($RsProf))&(!($partiel)));
		
		if (!($partiel)) {
			mysqli_data_seek($RsProf,0);
			
			do { //chaque prof
				$x=$row_RsProf['ID_prof'];
				$case='caseprof'.$row_RsProf['ID_prof'];
				
				if (isset($_POST[$x])&&($_POST[$x]=='on')){
					
					
					$updateSQL4 = sprintf("UPDATE cdt_prof SET date_maj='%s' WHERE ID_prof = %s",date('Y-m-d'),$x);
					$Result4 = mysqli_query($conn_cahier_de_texte, $updateSQL4) or die(mysqli_error($conn_cahier_de_texte));
					
					
					// et maintenant prendre chacune de ses matieres/classes
					
					
					$query_Rs = sprintf("SELECT MAX(code_date),ID_agenda, nom_classe, nom_matiere, cdt_classe.ID_classe, cdt_matiere.ID_matiere
						FROM cdt_agenda, cdt_classe, cdt_matiere
						WHERE prof_ID=%u
						AND cdt_classe.ID_classe = cdt_agenda.classe_ID
						AND cdt_matiere.ID_matiere = cdt_agenda.matiere_ID
						GROUP BY ID_classe, ID_matiere", $x);
					
					$Rs = mysqli_query($conn_cahier_de_texte, $query_Rs) or die(mysqli_error($conn_cahier_de_texte));
					$row_Rs= mysqli_fetch_assoc($Rs);
					//Boucle des matieres-classes
					
					do {
						$updateSQLvisa=sprintf("UPDATE cdt_agenda SET date_visa= '%s' WHERE code_date=%s AND prof_ID=%u",date('Y-m-d'),$row_Rs['MAX(code_date)'],$x);
						$Result5 = mysqli_query($conn_cahier_de_texte, $updateSQLvisa) or die(mysqli_error($conn_cahier_de_texte));
					} while ($row_Rs = mysqli_fetch_assoc($Rs));
					mysqli_free_result($Rs);
					
					//fin boucle des matieres-classes
					//fin des matiere classe d'un prof
					
				}
				else if (isset($_POST[$case])&&($_POST[$case]==$row_RsProf['ID_prof'])) {
					
					$SQL = sprintf("UPDATE cdt_agenda SET `date_visa` = '0000-00-00'  WHERE prof_ID=%u",$x);   
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$Result1 = mysqli_query($conn_cahier_de_texte, $SQL) or die(mysqli_error($conn_cahier_de_texte));
					
					$SQL2 = sprintf("UPDATE cdt_prof SET date_maj='0000-00-00' WHERE ID_prof=%u",$x);
					mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
					$Result2 = mysqli_query($conn_cahier_de_texte, $SQL2) or die(mysqli_error($conn_cahier_de_texte));
				};
				
				
				
				
			} while ($row_RsProf = mysqli_fetch_assoc($RsProf));
		};
	};
	mysqli_free_result($RsProf);
	
} else if ((isset($_POST["MM_update5"])) && ($_POST["MM_update5"] == "form5")) {
	//effacer les visas de controle des enseignants
	
	$updateSQL5 = "UPDATE cdt_prof SET date_maj='0000-00-00' WHERE droits=2";
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result5 = mysqli_query($conn_cahier_de_texte, $updateSQL5) or die(mysqli_error($conn_cahier_de_texte));
	
	$updateSQL6 = "UPDATE cdt_agenda SET date_visa='0000-00-00'";
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result6 = mysqli_query($conn_cahier_de_texte, $updateSQL6) or die(mysqli_error($conn_cahier_de_texte));
	
} 



$datetoday=date('Ymd').'9';
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsPublier = sprintf("SELECT ID_prof,nom_prof,identite, publier_cdt,publier_travail,MAX(code_date), prof_ID,date_maj FROM cdt_prof , cdt_agenda
        WHERE code_date<=%s AND ID_prof=prof_ID GROUP BY prof_ID ORDER BY identite,nom_prof ASC",$datetoday);
$RsPublier = mysqli_query($conn_cahier_de_texte, $query_RsPublier) or die(mysqli_error($conn_cahier_de_texte));
$row_RsPublier = mysqli_fetch_assoc($RsPublier);
$totalRows_RsPublier = mysqli_num_rows($RsPublier);


?>




<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
.Style70 {color: #000066}
-->
</style>
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<link href="../styles/info_bulles.css" rel="stylesheet" type="text/css" />
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
<LINK href="../styles/onglets.css" type=text/css rel=stylesheet>
<link type="text/css" href="../styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />
<script type="text/javascript" src="../jscripts/jquery-1.6.2.js"> </script>
<script type="text/javascript" src="../jscripts/jquery-ui-1.8.13.custom.min.js"> </script>
<script type="text/javascript" src="../jscripts/utils.js"></script>





<style type="text/css">
<!--
.Style71 {
	color: #FF0000;
	font-style: italic;
}
-->
</style>
</HEAD>
<BODY>



<DIV id=page>
<?php 
$header_description="Attribution d'un visa local - Consultation d'un CDT<br />Mise &agrave; disposition de CDT en cas d'inspection";
require_once "../templates/default/header.php";


if ($totalRows_RsPublier>0) {
	?>
	
	


	
	
	<BR />
	<div>
	
	<table border="0" width="95%" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
	
	<tr> 
        <td class="tab_detail">
	<div id="contente">
	<div id="tabs">
        <ul>
            <li><a href="#tabs-1">Attribution d'un visa local</a></li>
            <li><a href="#tabs-2">Mise &agrave; disposition de CDT en cas d'inspection</a></li>
            <li><a href="#tabs-3">Consultation d'un CDT</a></li>

      </ul>

        <div id="tabs-1">
        <p align='center'><span class="Style70"><strong>Nous sommes le <?php echo jour_semaine(date('d/m/Y')).' '.date('d/m/Y')?>.</strong></span></p>
        <p class="Style70">Les visas locaux ne sont pas visibles par les parents et les &eacute;l&egrave;ves
	mais uniquement par les enseignants lors de la saisie de leur cahier de textes.</p>
        <p class="Style70">Deux m&eacute;thodes vous sont propos&eacute;es pour attribuer votre 
        visa local sur le cahier de textes d'un enseignant :</p>
        <p class="Style70">Methode 1 : un visa local pour tous les cahiers de textes d'un enseignant.
	Il suffit de cocher dans le tableau ci-dessous puis d'enregistrer. Un visa portant la date du jour sera alors
        appos&eacute; sur chacune des derni&egrave;res saisies de l'enseignant pour l'ensemble de ses cahiers de textes.</p>
        <p class="Style70">M&eacute;thode 2 : un visa local pour un cahier de textes pr&eacute;cis d'un enseignant. 
	Il suffit de consulter le cahier de textes de l'enseignant choisi en cliquant sur son nom puis sur sa classe. Vous pourrez alors 
        apposer votre visa &agrave; l'endroit de votre choix dans chaque cahier de textes en cliquant sur le &quot;tampon&quot;. </p>
        <p class="Style71">Attention : Si le param&egrave;tre &quot;Blocage apr&egrave;s apposition   d'un visa&quot; a &eacute;t&eacute; choisi dans les param&egrave;tres Administrateur, la modification des cahiers par les enseignants ne sera plus possible pour les contenus ant&eacute;rieurs &agrave; date de votre visa. </p>
        <p  class="Style70">
	<i>Les enseignants dont le nom est en italique sont ceux qui n'ont pas saisi leur identit&eacute; dans leur interface.</i>	</p>
        </div>
        <div id="tabs-2"> 
        <p align='center'><span class="Style70"><strong>Nous sommes le <?php echo jour_semaine(date('d/m/Y')).' '.date('d/m/Y')?>.</strong></span></p>
        <p  class="Style70">Les cahiers de textes des classes dans lesquelles enseigne un professeur doivent &ecirc;tre accessibles en lecture par les inspecteurs dans le cadre de leurs missions. A cet effet, en cliquant sur le nom de l'enseignant, vous disposez d'un  lien crypt&eacute; que vous pouvez leur communiquer. Ils pourront alors acc&eacute;der &agrave; l'ensemble des cahiers de textes de l'enseignant. </p>
        <p  class="Style70">D'autre part, l'administrateur a la possibilit&eacute; de cr&eacute;er un compte de type invit&eacute;. Ainsi, l'inspecteur peut s'identifier sur la page d'accueil de l'application s'il dispose du mot de passe affect&eacute; &agrave; ce compte. Le param&eacute;trage actuel r&eacute;alis&eacute; par l'administrateur lui autorise la consultation <?php if ((isset($_SESSION['acces_inspection_all_cdt']))&&($_SESSION['acces_inspection_all_cdt']=='Non')){echo " <strong><i>uniquement des cahiers de textes mis &agrave; disposition ponctuellement par les enseignants</strong></i> et non ceux de l'ensemble de l'&eacute;tablissement";} else {echo " <strong><i>de l'ensemble des cahiers de textes de l'&eacute;tablissement</strong></i> et non uniquement ceux mis &agrave; disposition par les enseignants";};?>.</p>
        <p  class="Style70">Enfin, chaque enseignant peut lui aussi mettre &agrave; disposition de l'inspecteur les cahiers de textes qu'ils souhaitent diffuser, en lui communiquant un lien crypt&eacute; g&eacute;n&eacute;r&eacute; depuis son menu principal.  </p>
        </div>
	<div id="tabs-3">
        <p align='center'><span class="Style70"><strong>Nous sommes le <?php echo jour_semaine(date('d/m/Y')).' '.date('d/m/Y')?>.</strong></span></p>
        <p  class="Style70">Pour visualiser le cahier de textes d'un enseignant, il suffit de cliquer sur son nom puis ensuite
	de choisir sa classe ad&eacute;quate.</p>
        <p  class="Style70">
	<i>Les enseignants dont le nom est en italique sont ceux qui n'ont pas saisi leur identit&eacute; dans leur interface.</i>	</p>
	</div>
    </div>
	</div>
	<div style="position:absolute;margin-top:7px;margin-left:670px;"><a href="direction.php" ><img src="../images/home-menu.gif" alt="Retour au Menu Responsable Etablissement" width="26" height="20" border="0"></a></div>
	</div>
<br><br><br><br>	</td></tr>
        
        <tr> 
        <td class="tab_detail_gris"> <form action="publication_visa.php" method="POST" name="form4" id="form4">
        <br>
        <table border="0" align="center" cellpadding="0" cellspacing="0" class="bordure">
        <tr> 
        <td class="Style6">Enseignant</td>
        <td colspan="2" class="Style6"><div align="right">Publi&eacute;&nbsp;en&nbsp;ligne&nbsp;</div></td>
        <td class="Style6"><div align="center">Dernier&nbsp;ajout&nbsp;</div></td>
        <td class="Style6"><div align="center">Dernier&nbsp;visa&nbsp;</div></td>
        <td class="Style6"><div align="center">Visa&nbsp;local&nbsp;</div></td>
        </tr>
        <tr class="tab_detail" > 
        <td class="tab_detail">&nbsp;</td>
        <td class="tab_detail"><div align="center">Travail</div></td>
        <td class="tab_detail"><div align="center">CDT</div></td>
        <td class="tab_detail"><div align="right"></div></td>
        <td class="tab_detail"><div align="right"></div></td>
        <td  ><div align="center"> 
        <script>
        
        function selectionnerTout(formulaire){
        	//r&eacute;cup&eacute;ration de toutes elements input
        	lesInputs = document.getElementsByTagName('input');
        	//parcours des inputs
        	for(var i=0; i<lesInputs.length; i++) {
        		//si l'input est une case &agrave; cocher et que ce n'est pas la case "tout cocher/d&eacute;cocher"
        		if(!(lesInputs[i].id=="tousaucun")) {
        			//on met la valeur de la case &eacute;gale &agrave; celle de la case "tout cocher/d&eacute;cocher"
        			lesInputs[i].checked=true;
        		}
        	}
        } 
        
        </script>
        <input name="button" type="button" class="vacances"  id="tousaucun" onClick="selectionnerTout(this.form)" value="Tout cocher">
        </div></td>
        </tr>
        <?php $date_lastajout='';
	do {
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsClassesvisees = sprintf("SELECT distinct `classe_ID`,`date_visa`,`nom_classe` FROM `cdt_agenda`,`cdt_classe` WHERE `prof_ID`=%s AND `date_visa`<>'0000-00-00' AND cdt_agenda.classe_ID=cdt_classe.ID_classe",$row_RsPublier['ID_prof']);
		$RsClassesvisees = mysqli_query($conn_cahier_de_texte, $query_RsClassesvisees) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsClassesvisees = mysqli_fetch_assoc($RsClassesvisees);
		$totalRows_RsClassesvisees = mysqli_num_rows($RsClassesvisees);
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsClasses = sprintf("SELECT distinct `classe_ID` FROM `cdt_agenda` WHERE `prof_ID`=%s",$row_RsPublier['ID_prof']);
		$RsClasses = mysqli_query($conn_cahier_de_texte, $query_RsClasses) or die(mysqli_error($conn_cahier_de_texte));
		$totalRows_RsClasses = mysqli_num_rows($RsClasses);
		
		mysqli_free_result($RsClasses);
		
		$date_lastajout=substr($row_RsPublier['MAX(code_date)'],6,2).'/'.substr($row_RsPublier['MAX(code_date)'],4,2).'/'.substr($row_RsPublier['MAX(code_date)'],0,4);
		?>
		<tr > 
                <td class="tab_detail" ><a href="cdt_enseignant.php?ID_consult=<?php echo $row_RsPublier['ID_prof'] ;?>&ens_consult=<?php echo $row_RsPublier['identite'];?>"><?php echo $row_RsPublier['identite']==''?'&nbsp;<i>'.$row_RsPublier['nom_prof'].'</i>&nbsp;':'&nbsp;<strong>'.$row_RsPublier['identite'].'</ strong>&nbsp;'; ?></a></td>
                <td  align="center" <?php echo $row_RsPublier['publier_travail']=='N'?'bgcolor="#FF9999"':'class="tab_detail"';?>><div align="center"><?php echo $row_RsPublier['publier_travail']; ?></div></td>
                <td  align="center" <?php echo $row_RsPublier['publier_cdt']=='N'?'bgcolor="#FF9999"':'class="tab_detail"';?>><div align="center"><?php echo $row_RsPublier['publier_cdt']; ?></div></td>
                <td align="right" class="tab_detail"><?php echo '&nbsp;'.jour_semaine($date_lastajout).' '.$date_lastajout.'&nbsp;'; ?></td>
                <td align="right" class="tab_detail"> 
                <?php 
		if (substr($row_RsPublier['date_maj'],0,4)<>'0000'){
			$date_maj=substr($row_RsPublier['date_maj'],8,2).'/'.substr($row_RsPublier['date_maj'],5,2).'/'.substr($row_RsPublier['date_maj'],0,4);
		echo '&nbsp;'.jour_semaine($date_maj).' '.$date_maj.'&nbsp;';} else {echo '&nbsp;';}; ?>
		</td>
                <td   align="right" class="tab_detail"><div align="center">
		<?php if (($totalRows_RsClassesvisees==$totalRows_RsClasses)||($totalRows_RsClassesvisees==0)) { ?>
			<input type="hidden" name="caseprof<?php echo $row_RsPublier['prof_ID']; ?>" value="<?php echo $row_RsPublier['prof_ID']; ?>">
			<input type="checkbox" name="<?php echo $row_RsPublier['prof_ID']; ?>"   id="<?php echo $row_RsPublier['prof_ID']; ?>"  <?php if(substr($row_RsPublier['date_maj'],0,4)<>'0000'){echo "checked";}; ?> >
                <?php ;} else {echo "<a href='#' class='tooltip'>Sur certains CDT<em><span></span>";
                	if ($totalRows_RsClassesvisees>1) {echo "Liste des ".$totalRows_RsClassesvisees." Cahiers de Textes sur ".$totalRows_RsClasses." de ";}
                	else {echo "Un Cahier de Textes sur ".$totalRows_RsClasses." de ";}
                	echo  $row_RsPublier['identite']==''?$row_RsPublier['nom_prof']:$row_RsPublier['identite'];
                	echo ($totalRows_RsClassesvisees>1)?" d&eacute;j&agrave; vis&eacute;s :":" d&eacute;j&agrave; vis&eacute; :";
                	echo "<table>";
                	do {
                		echo "<tr><td>&nbsp;&nbsp;&nbsp;</td><td>".$row_RsClassesvisees['nom_classe']."</td><td>";
                		$date_maj=substr($row_RsClassesvisees['date_visa'],8,2).'/'.substr($row_RsClassesvisees['date_visa'],5,2).'/'.substr($row_RsClassesvisees['date_visa'],0,4);
                		echo '&nbsp; vis&eacute; le '.jour_semaine($date_maj).' '.$date_maj.'&nbsp;</td></tr>';
                	} while ($row_RsClassesvisees = mysqli_fetch_assoc($RsClassesvisees));?>
   	  </table></em></a>
                	
                	<div align="center"> 
                	<input type="hidden" name="raz2<?php echo $row_RsPublier['prof_ID']; ?>" value=<?php echo $row_RsPublier['prof_ID']; ?>>
                	<input name="RAZ<?php echo $row_RsPublier['prof_ID']; ?>" type="submit" class="vacances" value="Visa sur aucun de ses CDT">
                	</div>
                	
                	<div align="center"> 
                	<input type="hidden" name="all2<?php echo $row_RsPublier['prof_ID']; ?>" value=<?php echo $row_RsPublier['prof_ID']; ?>>
                	<input name="ALL<?php echo $row_RsPublier['prof_ID']; ?>" type="submit" class="vacances" value="Visa sur tous ses CDT">
                	</div>
                <?php ;}; ?>
		</div></td>
                </tr>
                <?php 
		mysqli_free_result($RsClassesvisees);
	} while ($row_RsPublier = mysqli_fetch_assoc($RsPublier)); ?>
</table>
        <div align="center"> 
        <p> 
        <input type="hidden" name="MM_update4" value="form4">
        <br>
        <input name="submit4" type="submit" class="vacances" id="submit4" value="Enregistrer et mettre &agrave; jour les visas des enseignants avec la date du jour">
        </p>
        </div>
        </form>
        <br> <form action="publication_visa.php" method="POST" name="form5" id="form5">
        <div align="center"> 
        <input type="hidden" name="MM_update5" value="form5">
        <input name="Submit" type="submit" class="vacances" value="Effacer tous les visas de contr&ocirc;le des enseignants">
        </div>
        </form>
        <br></td>
        </tr>
        </table>
        
        
        
        
        <p>&nbsp;</p>
        <p><a href="direction.php">Retour au Menu Responsable Etablissement </a> </p>
</div><?php } else {echo '<br />Aucune saisie encore effectu&eacute;e';};?>
  <DIV id=footer></DIV>
  </DIV>
  
  </body>
  </html>
  <?php
  mysqli_free_result($RsPublier);
  ?>
