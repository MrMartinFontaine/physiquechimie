<?php 
include "../../authentification/authcheck.php" ;
if ($_SESSION['droits']<>1 && $_SESSION['droits']<>3) { header("Location: ../../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');
unset($_SESSION['remplace']);
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


// modif remplacant chercher les remplacants

$erreur3='';
$erreur4='';
$erreur5='';


if (
(isset($_POST["MM_insert"])) 
&& ($_POST["MM_insert"] == "form1")
&& (isset($_POST['nom_prof'])) 
&& (isset($_POST['passe']))
 ) {
        
        if ($_POST['titulaire']==-1) {$erreur5="Ajout impossible ! &nbsp;<strong> il faut s&eacute;lectionner</strong> un enseignant";}
elseif ($_POST['passe']!=$_POST['passe2']) {$erreur5="Ajout impossible ! Les mots de passe ne sont pas identiques.";}
        
        else
        
	{
//formatage date debut remplacement
$date_declare_arrive=substr($_POST['date_declare_arrive'],6,4).'-'.substr($_POST['date_declare_arrive'],3,2).'-'.substr($_POST['date_declare_arrive'],0,2);		

		//tester si ce nom_prof existe deja dans les noms
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rsp = sprintf("SELECT * FROM cdt_prof WHERE nom_prof='%s'",$_POST['nom_prof']);
		$Rsp = mysqli_query($conn_cahier_de_texte, $query_Rsp) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rsp = mysqli_fetch_assoc($Rsp);
		$totalRows_Rsp = mysqli_num_rows($Rsp);
                $ide_remplacant=$row_Rsp['ID_prof']; // recuperation de l'ID_prof que l'on met dans remplacant
                mysqli_free_result($Rsp);
                
        $ancien=0; // $ancien : =0 c'est un nouveau compte, =1, c'est un ancien remplacant, = 2 le compte existe et n'est pas un ancien suppleant.
                if ($totalRows_Rsp<>0)
                {       
                        // si id_etat=2 et id_remplace=0 c'est un ancien remplacant
                	if ($row_Rsp['id_etat']==2 && $row_Rsp['id_remplace']==0)
                	{$ancien=1;
                		// ici il faut effacer l'ancien emploi du temps si non il y a accumulation des emploi du temps
                		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                		$query_RspDel = sprintf("DELETE FROM cdt_emploi_du_temps WHERE prof_ID='%u'",$row_Rsp['ID_prof']);
                                $RspDel = mysqli_query($conn_cahier_de_texte, $query_RspDel) or die(mysqli_error($conn_cahier_de_texte));
                                
                        }
                        else
                        {
                                $ancien=2; // c'est pas un remplacant et il est dans la liste
                	$erreur5="Ajout impossible ! &nbsp;<strong> ".$_POST['nom_prof']."</strong>&nbsp; est un nom d'utilisateur d&eacute;j&agrave; pr&eacute;sent dans la liste.";}
                }
                if ($ancien!=2)
                {
                	// recuperer les 3 dates :  aujourd'hui hier et de fin d'annee.
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
                	// passer la date du format jj/mm/yyy au format yyyy-mm-jj
                	$date_fin_annee=str_replace("/","-",$date_fin_annee);
                	$dateexplode=explode("-",$date_fin_annee);
                	$datefin=$dateexplode[2]."-".$dateexplode[1]."-".$dateexplode[0];
                	
                	
                	//Si le nom long (identite) de l'enseignant n'est pas renseigne, il recoit le meme que le login nom_prof
                	if (($_POST['identite']==NULL)||($_POST['identite']=='')) {$ident=$_POST['nom_prof'];} else {$ident=$_POST['identite'];};
                	
                        $ide_remplace=$_POST['titulaire'];
                        if ($ancien==0)
                        {
						$password=$_POST['passe'];
        $insertSQL = sprintf("INSERT INTO cdt_prof (identite,nom_prof,passe,droits,email,id_remplace,id_etat) VALUES ( %s,%s,'%s',%s,%s,%s,%s)",
                       GetSQLValueString($ident, "text"),
                                        GetSQLValueString($_POST['nom_prof'], "text"),
                       password_hash($password, PASSWORD_DEFAULT),
                                        ('2'),  // on force les droits a 2
                       GetSQLValueString($_POST['email'] , "text"),
                                        GetSQLValueString($ide_remplace, "int"), // on met l'id du prof remplace
                                        ('2') // on force l'etat a 2 c'est a dire remplacant
                                        );
                        }
else
                        {
                                // il suffit de reactiver le compte remettant a jour id_remplace on recupere alors le nom et le mdp
                                $insertSQL = sprintf("UPDATE cdt_prof SET id_remplace=".GetSQLValueString($ide_remplace, "int")." WHERE nom_prof=".GetSQLValueString($_POST['nom_prof'], "text"));
                                $erreur3="Le compte de <strong> ".$_POST['nom_prof']."</strong>&nbsp; a &eacute;t&eacute; r&eacute;activ&eacute;.";
                        };
                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $Result1 = mysqli_query($conn_cahier_de_texte, $insertSQL) or die(mysqli_error($conn_cahier_de_texte));
                	
                	
                	// tester si le prof titulaire est a 1 sinon mettre le prof a 1 $ide_remplace et ajuster ses dates il faut prendre la veille
                	$query_testerprof = sprintf(" SELECT * FROM cdt_prof WHERE (ID_prof=%u AND id_etat='0')",$ide_remplace);
                	$testerprof =  mysqli_query($conn_cahier_de_texte, $query_testerprof) or die(mysqli_error($conn_cahier_de_texte));
                	$row_testerprof = mysqli_fetch_assoc($testerprof);
                	$totalRows_testerprof = mysqli_num_rows($testerprof);
                	mysqli_free_result($testerprof);
                	if ($totalRows_testerprof > 0)
                	{
                		$query_RsTitu1 = sprintf("UPDATE cdt_prof SET id_etat='1' WHERE ID_prof=%u",$ide_remplace);
                		$RsTitu1 = mysqli_query($conn_cahier_de_texte, $query_RsTitu1) or die(mysqli_error($conn_cahier_de_texte));
                		
                		$query_RsDepart=sprintf(" UPDATE cdt_emploi_du_temps SET verrou_remplace=1 WHERE prof_ID=%u ",$ide_remplace);
                		$RsDepart = mysqli_query($conn_cahier_de_texte, $query_RsDepart) or die(mysqli_error($conn_cahier_de_texte));
                		
                		//on enregistre dans cdt_prof cette date de declaration d'absence

                		$query_Rschange_date=sprintf(" UPDATE cdt_prof SET date_declare_absent='%s' WHERE ID_prof=%u",$date_declare_arrive,$ide_remplace); 
                		$Rschange_date = mysqli_query($conn_cahier_de_texte, $query_Rschange_date) or die(mysqli_error($conn_cahier_de_texte));
                		// gerer l'emploi du temps 
                		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                		$query_RsDepart=sprintf(" UPDATE cdt_emploi_du_temps SET verrou_remplace=1 WHERE prof_ID=%u ",$ide_remplace);
                		$RsDepart = mysqli_query($conn_cahier_de_texte, $query_RsDepart) or die(mysqli_error($conn_cahier_de_texte));                  
                		$erreur4="Professeur titulaire mis absent";
                	}
                	
                	
                	// on copie l'emploi du temps, puis on le gere ?
                	// recuperation de l'ID si c'est un nouveau si non on a deja recupere l'ID_prof du remplacant plus haut
                	if ($ancien==0)
                	{
                		
                		$query_auto = sprintf("SHOW TABLE STATUS LIKE \"cdt_prof\"");
                		$auto = mysqli_query($conn_cahier_de_texte, $query_auto) or die(mysqli_error($conn_cahier_de_texte));
                		$row_auto = mysqli_fetch_assoc($auto);
                		$ide_remplacant=$row_auto['Auto_increment']-1; // on decremente le dernier increment
                	}
                	
                	// on declare dans la table des remplacements
                	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                	$query_RsRemplacement = sprintf("INSERT INTO cdt_remplacement(titulaire_ID,remplacant_ID,date_debut_remplace,date_creation_remplace) VALUES (%u,%u,'%s','%s')",
				GetSQLValueString($ide_remplace,"int"),
				GetSQLValueString($ide_remplacant,"int"),
				$date_declare_arrive,
				date('Y-m-d'));
			$RsRemplacement = mysqli_query($conn_cahier_de_texte, $query_RsRemplacement) or die(mysqli_error($conn_cahier_de_texte));
			
			
			//ne pas copier les plages cloturees
			mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
			$query_RsEmploi1 = sprintf("SELECT * FROM cdt_emploi_du_temps WHERE prof_ID=%u AND edt_exist_fin>='%s' ",$ide_remplace,$today);
			$RsEmploi1 = mysqli_query($conn_cahier_de_texte, $query_RsEmploi1) or die(mysqli_error($conn_cahier_de_texte));
			$row_RsEmploi1 = mysqli_fetch_assoc($RsEmploi1);
			$totalRows_RsEmploi1 = mysqli_num_rows($RsEmploi1);  
			
			// si > 0 il y a un emploi du temps, on le recopie en mettant comme prof l'id du remplacant
			
			
			if ($totalRows_RsEmploi1>0)
			{
				mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
				do {
					
					$query_RsEmploi2 = sprintf("INSERT INTO cdt_emploi_du_temps (
						prof_ID,
						jour_semaine,
						semaine,
						heure,
						classe_ID,
						gic_ID,
						groupe,
						matiere_ID,
						heure_debut,
						heure_fin,
						duree,
						edt_exist_debut,
						edt_exist_fin,
						couleur_cellule,
						couleur_police,
						ImportEDT,
						ID_Import,
						verrou_remplace)
						VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
						GetSQLValueString($ide_remplacant,"int"),
						GetSQLValueString($row_RsEmploi1['jour_semaine'],"text"),
						GetSQLValueString($row_RsEmploi1['semaine'],"text"),
						GetSQLValueString($row_RsEmploi1['heure'],"int"),
						GetSQLValueString($row_RsEmploi1['classe_ID'],"int"),
						GetSQLValueString($row_RsEmploi1['gic_ID'],"int"),
						GetSQLValueString($row_RsEmploi1['groupe'],"text"),
						GetSQLValueString($row_RsEmploi1['matiere_ID'],"int"),
						GetSQLValueString($row_RsEmploi1['heure_debut'],"text"),
						GetSQLValueString($row_RsEmploi1['heure_fin'],"text"),
						GetSQLValueString($row_RsEmploi1['duree'],"int"),
						GetSQLValueString($date_declare_arrive,"text"),
						GetSQLValueString($datefin,"text"),
						GetSQLValueString($row_RsEmploi1['couleur_cellule'],"text"),
						GetSQLValueString($row_RsEmploi1['couleur_police'],"text"),
						GetSQLValueString($row_RsEmploi1['ImportEDT'],"text"),
						GetSQLValueString($row_RsEmploi1['ID_Import'],"int"),
						0);
					
					$Result = mysqli_query($conn_cahier_de_texte, $query_RsEmploi2) or die(mysqli_error($conn_cahier_de_texte));
					
				} while ($row_RsEmploi1 = mysqli_fetch_assoc($RsEmploi1));
				
				//Faire une copie des regroupements du titulaire
				// Changer le prof_ID ou duplication des enregistrements ?
				//A faire !!!!!!! Il faudra penser a bloquer pour le titulaire
				
				$query_Rschange=sprintf(" UPDATE cdt_groupe_interclasses SET prof_ID=%u WHERE prof_ID=%u",$ide_remplacant,$ide_remplace);
				$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));
				
				//dans la table suivante, il s'agit d'un id_prof et non d'un prof_id !
				$query_Rschange=sprintf(" UPDATE cdt_type_activite SET ID_prof=%u WHERE ID_prof=%u",$ide_remplacant,$ide_remplace);
				$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));
				
				$erreur5="Emploi du temps du rempla&ccedil;ant mis &agrave; jour !";	
				
				//Re-affectation des saisies du titulaire au remplacant.  $ide_remplace > $ide_remplacant 
				//Copie du prof_ID du remplacant dans les tables correspondantes du titulaire
				
				//Recuperer ce que le titulaire a pu preremplir a partir d'aujourd'hui
				//// agenda et fichiers joints
				$c_today=date('Ymd');
				$query_Rsselect=sprintf(" SELECT ID_agenda FROM cdt_agenda, cdt_fichiers_joints WHERE ID_agenda=agenda_ID AND cdt_agenda.prof_ID=%u AND substring(code_date,1,8)>='%s'",$ide_remplace,$c_today);
				$Rsselect = mysqli_query($conn_cahier_de_texte, $query_Rsselect) or die(mysqli_error($conn_cahier_de_texte));
				$row_Rsselect = mysqli_fetch_assoc($Rsselect);
				
				$query_Rschange=sprintf(" UPDATE cdt_agenda SET prof_ID=%u WHERE prof_ID=%u AND substring(code_date,1,8)>='%s'",$ide_remplacant,$ide_remplace,$c_today);
				$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));
				
				do {
					$query_Rschange=sprintf(" UPDATE cdt_fichiers_joints SET prof_ID=%u WHERE agenda_ID=%u",$ide_remplacant,$row_Rsselect['ID_agenda']);
					$Rschange = mysqli_query($conn_cahier_de_texte, $query_Rschange) or die(mysqli_error($conn_cahier_de_texte));
					
				} while ($row_Rsselect = mysqli_fetch_assoc($Rsselect)); 
				
				mysqli_free_result($Rsselect);
				mysqli_free_result($RsEmploi1);
				
			}
			else
			{
				$erreur5 =" Le titulaire n'a pas d'emploi du temps";
			}
			
			
			
			
			
		}
	}
}

// liste des remplacants 
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsRemplace = "SELECT * FROM cdt_prof WHERE droits=2 AND id_etat=2 AND id_remplace!=0 ORDER BY nom_prof ASC";
$RsRemplace = mysqli_query($conn_cahier_de_texte, $query_RsRemplace) or die(mysqli_error($conn_cahier_de_texte));
$row_RsRemplace = mysqli_fetch_assoc($RsRemplace);
$totalRows_RsRemplace = mysqli_num_rows($RsRemplace);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsTitulaire = "SELECT * FROM cdt_prof WHERE droits=2 AND id_etat!=2 AND ancien_prof='N' ORDER BY nom_prof ASC";
$RsTitulaire = mysqli_query($conn_cahier_de_texte, $query_RsTitulaire) or die(mysqli_error($conn_cahier_de_texte));
$row_RsTitulaire = mysqli_fetch_assoc($RsTitulaire);
$totalRows_RsTitulaire = mysqli_num_rows($RsTitulaire);
echo $query_RsTitulaire;
if ($_SESSION['droits']<>1 && $_SESSION['droits']<>3) { header("Location: ../index.php");exit;};?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<script language="JavaScript" type="text/JavaScript">

function MM_goToURL() { //v3.0
	var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
	for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
function confirmation(var_conf,prof)
{
	if (confirm("Voulez-vous r\351ellement mettre fin au remplacement de "+var_conf+" ?")) { // Clic sur OK
		MM_goToURL('window','fin_remplacement.php?prof='+prof);
	}
}

</script>

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
-->
</style>
</HEAD>
<BODY>
<DIV id=page>

  <?php 
$header_description='Gestion des remplacements - Rempla&ccedil;ants';
require_once "../../templates/default/header.php";
?> 
 <div  style="background:#F0EDE5;margin:20px;padding:10px;" >
 <table width="95%" border="0">
    <tr>
      <td><div align="center">
        <p align="left"><img src="../../images/lightbulb.png" width="16" height="16"> <strong>Cr&eacute;ation du compte du suppl&eacute;ant : </strong></p>
      </div></td>
      <td width="25"><div align="right"><a href="remplacement.php"><img src="../../images/home-menu.gif" width="26" height="20" border="0"></a></div></td>
    </tr>
  </table>
 <p align="left"> Remplissez le formulaire ci-dessous et s&eacute;lectionnez l&#39;enseignant remplac&eacute;.</p>
 <p align="left">Le nom est le libell&eacute; affich&eacute; sur la fiche &eacute;l&egrave;ve et parents (Ex : Bruno Carla) <br>
   L'identifiant est un libell&eacute; court utilis&eacute; par l'enseignant pour se connecter (Ex : c_bruno) <br>
   Si le nom n'est pas renseign&eacute;, il se verra attribuer l'identifiant.<br>
   <br>
   Lors de la cr&eacute;ation de ce compte :</p>
 <div align="left">
<blockquote>
  <p>- Le compte du titulaire se trouve bloqu&eacute; en saisie si cela n'a pas &eacute;t&eacute; d&eacute;j&agrave; fait en Gestion des titulaires </p>
</blockquote>
</div>

<div align="left">
<blockquote>
  <p>- Le suppl&eacute;ant re&ccedil;oit une copie de l'emploi du temps du titulaire. Il lui appartient alors de faire les adaptations n&eacute;cessaires dans son menu enseignant &gt; Gestion de l'emploi du temps. <br>
    NB : Vous pouvez acc&eacute;der &agrave; son emploi du temps dans le tableau ci-dessous via l'icone de la colonne EDT. </p>
</blockquote>
</div>

<p></p>
</p>
<p align="left">Plusieurs suppl&eacute;ants peuvent remplacer un titulaire. Mais en  l'&eacute;tat, un rempla&ccedil;ant ne peut remplacer qu'un seul titulaire. Pour g&eacute;rer cette impossibilit&eacute;, il sera n&eacute;cessaire de cr&eacute;er dans l'imm&eacute;diat un compte rempla&ccedil;ant suppl&eacute;mentaire. </p>
<p align="left">Pour mettre fin &agrave; un remplacement, cliquer sur la croix rouge correspondante dans le tableau. Apr&egrave;s confirmation, les saisies du suppl&eacute;ant sont r&eacute;affect&eacute;es au titulaire. Le compte utilisateur du suppl&eacute;ant n'est pas supprim&eacute; mais le suppl&eacute;ant ne peut plus acc&eacute;der &agrave; l'application. En consultation &eacute;l&egrave;ve, des marquages identifieront clairement les p&eacute;riodes de remplacement. </p>
<p align="left">Pour r&eacute;activer le compte d&#39;un ancien suppl&eacute;ant, il suffit de renseigner son ancien identifiant et le nom du professeur &agrave; remplacer. Il faut s'assurer bien &eacute;videmment que l'administrateur n'a pas supprim&eacute; ce compte de la liste des utilisateurs &agrave; l'issue du remplacement.</p>
</div>
<div class="erreur">
<?php 
if($erreur3!=''){echo $erreur3.'<br/>';};
if($erreur4!=''){echo $erreur4.'<br/>';};
if($erreur5!=''){echo $erreur5.'<br/>';};
?>
</div>
<script language="JavaScript" type="text/JavaScript">

function formfocus() {
	document.form1.nom_prof.focus()
	document.form1.nom_prof.select()
}
</script>

<form onLoad= "formfocus()" method="post"  name="form1" action="<?php echo $editFormAction; ?>" onSubmit="return submit_modifpass2();" >

<table width="90%" align="center">
<tr valign="baseline">
<td><table width="100%"  border="0" cellpadding="5" cellspacing="5" class="tab_detail_gris">

<tr >
              <td><div align="right"><strong>NOM Pr&eacute;nom </strong></div>
                </th>
              <td><input name="identite" type="text" id="identite" value="" size="32"></td>
          </tr>
            <tr>
              <td><div align="right"><strong>IDENTIFIANT (login - pas d'accents)</strong></div>
                </th>
              <td><input type="text" name="nom_prof" value="" size="32"></td>
            </tr>
            <tr>
              <td><div align="right"><strong>Mot de passe </strong></div>
                </th>
              <td><input type="password" name="passe" id="passe" value="" size="32"></td>
            </tr>
            <tr>
              <td><div align="right"><strong>Confirmer le mot de passe </strong></div>
                </th>
              <td ><input type="password" name="passe2" id="passe2" value="" size="32"></td>
            </tr>
            <tr >
              <td><div align="right"><strong>Adresse m&eacute;l (facultatif) </strong></div></td>
              <td><input name="email" id="email" size="32" value=""></td>
            </tr>
                          <tr >
<td><div align="right"><strong>Rempla&ccedil;ant de</strong></div></td>
<td><div align="left">
<select name="titulaire" id="titulaire">
<option value="-1" selected>S&eacute;lectionnez l'enseignant</option>
<?php
                                        do {  
        ?>
        <option value="<?php echo $row_RsTitulaire['ID_prof']?>"><?php 
                                                echo $row_RsTitulaire['identite'];
        ?></option>
        <?php
                                                } while ($row_RsTitulaire = mysqli_fetch_assoc($RsTitulaire));
$rows = mysqli_num_rows($RsTitulaire);
if($rows > 0) {
        mysqli_data_seek($RsTitulaire, 0);
        $row_RsTitulaire = mysqli_fetch_assoc($RsTitulaire);
};
mysqli_free_result($RsTitulaire);
?>
</select>
</div></td>
</tr>
                          <tr >
                            <td><div align="right"><strong>A partir de la date du </strong></div></td>
                            <td>
							<script>
         $(function() {
        	$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
			$.datepicker.setDefaults($.datepicker.regional['fr']);
        	$('#date_declare_arrive').datepicker({firstDay:1});
        });
        </script>
		<input name='date_declare_arrive' type='text' id='date_declare_arrive' value="<?php 
echo date('d-m-Y');?>" size="10"/>
		
		
		
		</td>
                          </tr>
<tr>
<td></th>
<td><br>
                <input name="submit" type="submit" value="Ajouter ou r&eacute;activer ce professeur rempla&ccedil;ant"></td>
</tr>
</table></td>
</tr>
</table>
<p>&nbsp; </p>
<p>
<input type="hidden" name="MM_insert" value="form1">
</p>
</form>

<script> formfocus(); </script>
<table border="0" align="center">
<tr>
<td class="Style6"><div align="center">NOM&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center">Identifiant&nbsp;&nbsp;</div></td>
      <td class="Style6"><div align="center">Mot de passe&nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center">Rempla&ccedil;ant de &nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center">EDT &nbsp;&nbsp;</div></td>
<td class="Style6"><div align="center">Mettre fin au remplacement&nbsp;&nbsp;</div></td>
</tr>
<?php do { 
        ?>
        <tr>
        <td class="tab_detail_gris"><div align="left" >
        <?php echo $row_RsRemplace['identite']; ?></div></td>
        <td class="tab_detail_gris"><div align="left"  >
        <?php echo $row_RsRemplace['nom_prof']; ?></div></td>
        <td class="tab_detail_gris Style70"><div align="center" class="tab_detail_gris">
            <?php 
                if ($totalRows_RsRemplace>0){
          
          if($row_RsRemplace['passe']<>'d41d8cd98f00b204e9800998ecf8427e'){echo '********';} 
          };
          ?>
          </div></td>
        <td class="tab_detail_gris"><div align="left"  >
        <?php if($totalRows_RsRemplace>0 AND $row_RsRemplace['id_remplace']!=0)
        {
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_RsTitu2 = sprintf("SELECT * FROM cdt_prof where (ID_prof=%u AND id_etat=1)",$row_RsRemplace['id_remplace']); 
                $RsTitu2 = mysqli_query($conn_cahier_de_texte, $query_RsTitu2) or die(mysqli_error($conn_cahier_de_texte));
                $row_RsTitu2 = mysqli_fetch_assoc($RsTitu2);
                echo $row_RsTitu2['identite']==''?$row_RsTitu2['nom_prof']:$row_RsTitu2['identite'];
                mysqli_free_result($RsTitu2);
        }?></div></td>
        <?php if($totalRows_RsRemplace>0)
        {
		?>  
		<td class="tab_detail_gris"><div align="center"><?php if($row_RsRemplace['ID_prof']<>1 AND $row_RsRemplace['id_remplace']!=0){ ?>
			<img src="../../images/button_edit.png" alt="Modifier l'emploi du temps" title="Modifier l'emploi du temps" width="11" height="13" onClick="MM_goToURL('window','../../enseignant/emploi.php?ID_prof=<?php echo $row_RsRemplace['ID_prof']."&affiche=1&admin_remplacement"; ?>');return document.MM_returnValue">
          <?php } else {echo '&nbsp;';}
          ?></div>                </td>
          <td class="tab_detail_gris"><div align="center" <?php if($row_RsRemplace['ID_prof']<>1 AND $row_RsRemplace['id_remplace']!=0){ ?>
                onClick="return confirmation(' <?php echo $row_RsRemplace['identite']; ?>','<?php echo $row_RsRemplace['ID_prof'];?>');return document.MM_returnValue">
                  <img src="../../images/ed_delete.gif" alt="Fin du remplacement" title="Fin du remplacement" width="11" height="13">
          <?php } else {echo '&nbsp;';}
          ?></div></td>
          <?php 
        }
        else
        {?><td class="tab_detail_gris"><div align="center">&nbsp;</div></td>
        	<td class="tab_detail_gris"><div align="center">&nbsp;</div></td>
        <?php }
        ; ?>
        </tr>
<?php } while ($row_RsRemplace = mysqli_fetch_assoc($RsRemplace));
mysqli_free_result($RsRemplace); ?>
</table>


<br />
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="5">


<p align="left" class="Style74">&nbsp;</p>
<p align="center" class="Style74"><a href="remplacement.php">Retour au module de Gestion des remplacements</a></p></td>
</tr>
</table>
<DIV id=footer>
    <p class="auteur"><a href="../contribution.php" class="auteur">Pierre Lemaitre 
      - St L&ocirc; (France) </a></p>
</DIV>
</body>
</html>
