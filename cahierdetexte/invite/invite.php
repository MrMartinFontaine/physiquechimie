<?php 
//include "../authentification/authcheck.php";
//if ($_SESSION['droits']<>5) { header("Location: ../index.php");exit;};

//modifier pour chaque nouvelle version
$indice_version='5510'; 
$libelle_version='Version 5.5.1.0 Standard';

require_once('../inc/functions_inc.php');
require_once('../Connections/conn_cahier_de_texte.php');
$editFormAction = '#';
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

//demande d'acces par cle sans compte utilisateur
if ((isset($_GET['ID_prof']))&&(isset($_GET['ident']))){
	session_start();
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsProf = sprintf("SELECT * FROM cdt_prof WHERE ID_prof=%u ",intval(strtr($_GET['ID_prof'],$protect)));;
	$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsProf = mysqli_fetch_assoc($RsProf);
	
	if(isset($_GET['all'])) //mise a disposition par la direction
        {
        	$date_validite = $row_RsProf["datefin_invite_dir"];
        	$identifiant = $row_RsProf["lien_invite_dir"];
        }
        else //mise a disposition par l'enseignant
        {
        	$date_validite = $row_RsProf["datefin_invite_prof"];
        	$identifiant = $row_RsProf["lien_invite_prof"];
        }
        if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/",$date_validite)) $date_validite = date('Y-m-d',time()-3600*24); //precaution
        $date_acces = date('Y-m-d');
        if($_GET['ident']=="" || $identifiant=="" || $_GET['ident']!=$identifiant || $date_acces>$date_validite) //acces invalide
        {
        	header("Location: ../index.php");
        	die();
	}
	else
	{
		$_SESSION['droits'] = 5;
		$_SESSION['ID_prof'] = 0;
		$_SESSION['nom_prof'] = 'Invit&eacute;';
	}
	
} else {
	include "../authentification/authcheck.php" ;
};
if ($_SESSION['droits']<>5) { header("Location: ../index.php");exit;};

if ((isset($_GET['ID_prof']))&&(isset($row_RsProf['ID_prof']))&&($_GET['ID_prof']==$row_RsProf['ID_prof'])&&(isset($_GET['ident']))){$sql="WHERE cdt_invite.prof_ID=".intval(strtr($row_RsProf['ID_prof'],$protect));} else {$sql='';};
//else {header("Location: ../index.php");exit;};
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../templates/default/header_footer.css" type=text/css rel=stylesheet>
</head>
<BODY>
<DIV id=page>
<p>
<?php 


if ((isset($_SESSION['acces_inspection_all_cdt']))&&($_SESSION['acces_inspection_all_cdt']=='Oui')){
	//**************************************************************************
	// Acces a tous les cdt de l'etablissement
	//**************************************************************************
	
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsListeProf = "SELECT * FROM cdt_prof WHERE droits='2' ORDER BY cdt_prof.identite,cdt_prof.nom_prof ASC";
	$RsListeProf = mysqli_query($conn_cahier_de_texte, $query_RsListeProf) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsListeProf = mysqli_fetch_assoc($RsListeProf);
	$totalRows_RsListeProf = mysqli_num_rows($RsListeProf);
	
	$profchoix_RsInvite = "0";
	if (isset($_GET['ID_prof'])) {
		$profchoix_RsInvite = (get_magic_quotes_gpc()) ? $_GET['ID_prof'] : addslashes($_GET['ID_prof']);
	}
	$query_RsInvite =sprintf("SELECT DISTINCT gic_ID, nom_classe, nom_matiere, cdt_classe.ID_classe,cdt_matiere.ID_matiere FROM cdt_agenda, cdt_classe, cdt_matiere WHERE `prof_ID` =%u  AND cdt_classe.ID_classe = cdt_agenda.classe_ID AND cdt_matiere.ID_matiere = cdt_agenda.matiere_ID ORDER BY gic_ID, cdt_classe.nom_classe, cdt_matiere.nom_matiere ", intval(strtr($profchoix_RsInvite,$protect)));
	
	$RsInvite = mysqli_query($conn_cahier_de_texte, $query_RsInvite) or die(mysqli_error($conn_cahier_de_texte));
	$row_RsInvite = mysqli_fetch_assoc($RsInvite);
	$totalRows_RsInvite = mysqli_num_rows($RsInvite);  
	$header_description="Mise &agrave; disposition de cahiers de textes de l'ensemble de l'&eacute;tablissement";
	require_once "../templates/default/header.php";
	
	?>
	<form  method="GET" action="invite.php" name="form2" id="form2">
	<p>&nbsp;</p>
	<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" class="espace_enseignant">
	<tr>
        <td><select name="ID_prof"   id="ID_prof">
        <option value="value">S&eacute;lectionner l'enseignant</option>
        <?php
        do {  
        	?>
        	<option value="<?php echo $row_RsListeProf['ID_prof']?>" <?php if ($profchoix_RsInvite==$row_RsListeProf['ID_prof']){echo ' selected';};?>>
        	<?php if ($row_RsListeProf['identite']<>""){ echo $row_RsListeProf['identite'];} else {echo $row_RsListeProf['nom_prof'];};?>
        	</option>
        	<?php
        } while ($row_RsListeProf = mysqli_fetch_assoc($RsListeProf));
        $rows = mysqli_num_rows($RsListeProf);
        if($rows > 0) {
        	mysqli_data_seek($RsListeProf, 0);
        	$row_RsListeProf = mysqli_fetch_assoc($RsListeProf);
        }
        ?>
        </select>
        <td><input type="submit" name="Submit2" value="Valider" /></td>
        </tr>
        </table>
        
        </form>
        <?php
        if (isset($_GET['ID_prof'])){
        	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
        	$query_RsProf2 = sprintf("SELECT * FROM cdt_prof WHERE ID_prof=%u ORDER BY identite,nom_prof ASC",intval(strtr($profchoix_RsInvite,$protect)));
        	$RsProf2 = mysqli_query($conn_cahier_de_texte, $query_RsProf2) or die(mysqli_error($conn_cahier_de_texte));
        	$row_RsProf2 = mysqli_fetch_assoc($RsProf2);
        	$totalRows_RsProf2 = mysqli_num_rows($RsProf2);?>
        	<br />
        	<table width="90%" align="center" cellpadding="0" cellspacing="0" class="tab_detail">
        	<tr>
        	<td  colspan="3" valign="top" class="tab_detail_gris"><strong><img src="../images/identite.gif" width="16" height="18">&nbsp;&nbsp;<?php echo $row_RsProf2['identite'];?> </strong> &nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;
        	<?php if ($row_RsProf2['message_invite']=='O'){ ?>
        		<a href="message_ajout_inv.php?ID_prof=<?php 
        		if ((isset($_GET['ID_prof']))&&(isset($_GET['ident']))){echo strtr(GetSQLValueString($_GET['ID_prof'],"int"),$protect).'&ident='.strtr(GetSQLValueString($_GET['ident'],"text"),$protect);} 
        		else {
        			echo strtr(GetSQLValueString($profchoix_RsInvite,"int"),$protect);
        		};
        		?>"><img src="../images/cahier2.png" alt="Contacter l'enseignant via son cahier de textes" width="25" height="25" border="0" align="absbottom" title="Contacter l'enseignant via son cahier de textes"></a>
        		<?php
        		
        	};
        	
        	if ($row_RsProf2['email']<>''){ ?>
        		&nbsp;&nbsp;&nbsp;&nbsp;<a href="mailto:<?php echo $row_RsProf2['email'];?>"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant &nbsp; <?php echo $row_RsProf2['email'];?>" title="Contacter l'enseignant &nbsp; <?php echo $row_RsProf2['email'];?>"/></a>
        	<?php };?>
        	</td>
        	</tr>
        	</table>
        	<p>&nbsp;</p>
        	<?php if ($totalRows_RsInvite>0){?>
        		<table  width="80%" align="center" cellpadding="5" cellspacing="1" class="Style555">
        		<?php 
        		$last_gic_ID=0;
        		do { 
        			?>
        			<tr>
        			<?php 
        			//Regroupements
        			if ($row_RsInvite['gic_ID']==0){?>
        				<td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo strtr(GetSQLValueString($row_RsInvite['ID_classe'],"int"),$protect);?>&matiere_ID=<?php echo strtr(GetSQLValueString($row_RsInvite['ID_matiere'],"int"),$protect);?>&gic_ID=<?php echo strtr(GetSQLValueString($row_RsInvite['gic_ID'],"int"),$protect);?>&ens_consult=<?php echo strtr(GetSQLValueString($row_RsProf2['identite'],"text"),$protect);?>&prof_ID=<?php echo strtr(GetSQLValueString($row_RsProf2['ID_prof'],"int"),$protect);?>&ordre=down" ><?php echo strtr(GetSQLValueString($row_RsInvite['nom_matiere'],"text"),$protect); ?></a></td>
        				<td valign="bottom" bgcolor="#FFFFFF"><?php echo $row_RsInvite['nom_classe']; ?>&nbsp;</td>
        				</tr>
        				<?php
        				
                                }
                                else
                                {
                                	//presence de regroupement dans la matiere et la classe
                                	
                                	if ($row_RsInvite['gic_ID']<>$last_gic_ID){ 
                                		// Rechercher le nom du regroupement
                                		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                		$query_RsG =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE  cdt_groupe_interclasses.ID_gic = %u ",intval(strtr($row_RsInvite['gic_ID'],$protect)));
                                		$RsG = mysqli_query($conn_cahier_de_texte, $query_RsG) or die(mysqli_error($conn_cahier_de_texte));
                                		$row_RsG = mysqli_fetch_assoc($RsG);?> 
                                		<td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo strtr(GetSQLValueString($row_RsInvite['ID_classe'],"int"),$protect);?>&matiere_ID=<?php echo strtr(GetSQLValueString($row_RsInvite['ID_matiere'],"int"),$protect);?>&gic_ID=<?php echo strtr(GetSQLValueString($row_RsInvite['gic_ID'],"int"),$protect);?>&regroupement=<?php echo strtr(GetSQLValueString($row_RsG['nom_gic'],"text"),$protect);?>&ens_consult=<?php echo strtr(GetSQLValueString($row_RsProf2['identite'],"text"),$protect);?>&prof_ID=<?php echo strtr(GetSQLValueString($row_RsProf2['ID_prof'],"int"),$protect);?>&ordre=down" ><?php echo strtr(GetSQLValueString($row_RsInvite['nom_matiere'],"text"),$protect); ?></a></td>
                                		<td valign="bottom" bgcolor="#FFFFFF"><?php echo '(R) '.$row_RsG['nom_gic'];
                                		$last_gic_ID=$row_RsInvite['gic_ID'];
                                		?>&nbsp;</td>
                                		
                                		</tr>
                                		<?php
                                		
                                		mysqli_free_result($RsG);
                                	}
                                }
                        } while ($row_RsInvite = mysqli_fetch_assoc($RsInvite)); ?>
                        </table>
                        <?php
                } else
                { echo "<br /><br />Il n'y a pas de cahiers de textes disponibles actuellement pour cet enseignant. <br />";
                };
        };
}


else

{       
	//**************************************************************************
	// Acces aux cdt mis a disposition par le chef etablissement ou l'enseignant
	//**************************************************************************
	
	if (!isset($_GET['all'])){ // mise a disposition de l'ensemble des cahiers de l'enseignant par l'enseignant
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsInvite = sprintf("SELECT * FROM ((cdt_invite INNER JOIN cdt_classe ON cdt_invite.classe_ID=cdt_classe.ID_classe INNER JOIN cdt_prof ON  cdt_invite.prof_ID =cdt_prof.ID_prof INNER JOIN cdt_matiere ON  cdt_invite.matiere_ID =cdt_matiere.ID_matiere ) LEFT JOIN cdt_groupe_interclasses ON cdt_invite.gic_ID=cdt_groupe_interclasses.ID_gic) %s ORDER BY nom_prof, nom_matiere, NumArchive,  nom_classe ",$sql);
		
		
		$RsInvite = mysqli_query($conn_cahier_de_texte, $query_RsInvite) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsInvite = mysqli_fetch_assoc($RsInvite);
		$totalRows_RsInvite = mysqli_num_rows($RsInvite);  
		$header_description='Mise &agrave; disposition de cahiers de textes par les enseignants';
		require_once "../templates/default/header.php";
		
		
		?>
		<p>&nbsp; </p>
		<?php
		if ($totalRows_RsInvite==0){echo "<br /><br />Il n'y a pas de cahiers de textes disponibles actuellement via ce compte &quot;<em>invit&eacute;</em>&quot;. <br />";}
		else {
			
			
			$nom='';
			
			do { 	 
				
				if($nom<>$row_RsInvite['identite']){
					if ($nom<>''){echo '</table>';};?>
					<br />
					<table width="90%" align="center" cellpadding="0" cellspacing="0" class="tab_detail">
					<tr>
					<td  colspan="3" valign="top" class="tab_detail_gris"><strong><img src="../images/identite.gif" width="16" height="18">&nbsp;&nbsp;<?php echo $row_RsInvite['identite'];?> </strong> &nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;
					<?php if ($row_RsInvite['message_invite']=='O'){ ?>
						<a href="message_ajout_inv.php?ID_prof=<?php 
						if ((isset($_GET['ID_prof']))&&(isset($_GET['ident']))){echo intval(strtr($_GET['ID_prof'],$protect)).'&ident='.$_GET['ident'];} 
						else {
							echo intval(strtr($row_RsInvite['ID_prof'],$protect));
						};
						?>"><img src="../images/cahier2.png" alt="Contacter l'enseignant via son cahier de textes" width="25" height="25" border="0" align="absbottom" title="Contacter l'enseignant via son cahier de textes"></a>
						<?php
						
					};
					
					if ($row_RsInvite['email']<>''){ ?>
						&nbsp;&nbsp;&nbsp;&nbsp;<a href="mailto:<?php echo $row_RsInvite['email'];?>"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant &nbsp; <?php echo $row_RsInvite['email'];?>" title="Contacter l'enseignant &nbsp; <?php echo $row_RsInvite['email'];?>"/></a>
					<?php };?>
					</td>
					</tr>
					<?php
				}
				
				?>
				<tr>
				<td width="40%" class="tab_detail">
				
				
				
				<a href="../lire.php?prof_ID=<?php echo strtr(GetSQLValueString($row_RsInvite['ID_prof'],"int"),$protect);?>&ens_consult=<?php echo strtr(GetSQLValueString($row_RsInvite['identite'],"text"),$protect);?>&classe_ID=<?php echo strtr(GetSQLValueString($row_RsInvite['ID_classe'],"int"),$protect);?>&matiere_ID=<?php echo strtr(GetSQLValueString($row_RsInvite['ID_matiere'],"int"),$protect);?>&gic_ID=<?php echo strtr(GetSQLValueString($row_RsInvite['gic_ID'],"int"),$protect);
				if ($row_RsInvite['gic_ID']<>0){ echo '&regroupement='.$row_RsInvite['nom_gic'];};?>&ordre=down
				<?php if ($row_RsInvite['NumArchive']<>0){echo '&archivID='.strtr(GetSQLValueString($row_RsInvite['NumArchive'],"int"),$protect);};?>" ><?php echo $row_RsInvite['nom_matiere']; 
				
				if ($row_RsInvite['NumArchive']<>0){
					
					// Nom archive
                                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                        $query_RsArchiv = "SELECT * FROM cdt_archive WHERE NumArchive=".intval(strtr($row_RsInvite['NumArchive'],$protect));
                                        $RsArchiv = mysqli_query($conn_cahier_de_texte, $query_RsArchiv) or die(mysqli_error($conn_cahier_de_texte));
                                        $row_RsArchiv = mysqli_fetch_assoc($RsArchiv);
                                        echo '('.$row_RsArchiv['NomArchive'].')</strong>';
                                        mysqli_free_result($RsArchiv);
                                };?>
                                </a>
                                
				</td>
				<td width="40%" class="tab_detail"><?php if($row_RsInvite['gic_ID']==0){ echo $row_RsInvite['nom_classe'];} else {echo '(R) '.$row_RsInvite['nom_gic'];}; ?>
				</td>
				</tr>
				<?php $nom=$row_RsInvite['identite'];$message_invite=$row_RsInvite['message_invite'];
			} while ($row_RsInvite = mysqli_fetch_assoc($RsInvite)); ?>
			</table>
			<?php  
		};
		
	}
	
	
	
	
	
	else 
	// mise a disposition de l'ensemble des cahiers de l'enseignant par la direction
	
	
	{  
		
		$profchoix_RsInvite_direction=$_GET['ID_prof'];
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsProf = "SELECT identite,email FROM cdt_prof WHERE ID_prof =".intval(strtr($_GET['ID_prof'],$protect));
		$RsProf = mysqli_query($conn_cahier_de_texte, $query_RsProf) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsProf = mysqli_fetch_assoc($RsProf);
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsInvite_direction = sprintf("SELECT DISTINCT gic_ID, nom_classe, nom_matiere, cdt_classe.ID_classe,cdt_matiere.ID_matiere FROM cdt_agenda, cdt_classe, cdt_matiere WHERE `prof_ID` =%u AND cdt_classe.ID_classe = cdt_agenda.classe_ID AND cdt_matiere.ID_matiere = cdt_agenda.matiere_ID ORDER BY gic_ID, cdt_classe.nom_classe, cdt_matiere.nom_matiere ", intval(strtr($profchoix_RsInvite_direction,$protect)));
		$RsInvite_direction = mysqli_query($conn_cahier_de_texte, $query_RsInvite_direction) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsInvite_direction = mysqli_fetch_assoc($RsInvite_direction);
		$totalRows_RsInvite_direction = mysqli_num_rows($RsInvite_direction);
		
		
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rsgic =sprintf("SELECT * FROM cdt_groupe_interclasses WHERE  cdt_groupe_interclasses.prof_ID = %u ",$_SESSION['ID_prof']);
		$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rsgic = mysqli_fetch_assoc($Rsgic);
		$totalRows_Rsgic = mysqli_num_rows($Rsgic);
		
		
		
		$header_description="Mise &agrave; disposition par le Responsable Etablissement des cahiers de textes";
		require_once "../templates/default/header.php";
		?>
		<HR>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<table  width="80%" align="center" cellpadding="5" cellspacing="1" class="Style555">
		<tr>
		<td  colspan="3" valign="top" class="tab_detail_gris"><strong><img src="../images/identite.gif" width="16" height="18">&nbsp;&nbsp;<?php echo $row_RsProf['identite'];?> </strong> &nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp; <a href="message_ajout_inv.php?ID_prof=<?php 
		if ((isset($_GET['ID_prof']))&&(isset($_GET['ident']))){echo intval(strtr($_GET['ID_prof'],$protect)).'&ident='.$_GET['ident'];} 
		else {
			echo intval(strtr($row_RsInvite['ID_prof'],$protect));
		};
		?>"><img src="../images/cahier2.png" alt="Contacter l'enseignant via son cahier de textes" width="25" height="25" border="0" align="absbottom" title="Contacter l'enseignant via son cahier de textes"></a>
		<?php 
		
		if ($row_RsProf['email']<>''){ ?>
			&nbsp;&nbsp;&nbsp;&nbsp;<a href="mailto:<?php echo $row_RsProf['email'];?>"><img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant &nbsp; <?php echo $row_RsProf['email'];?>" title="Contacter l'enseignant &nbsp; <?php echo $row_RsProf['email'];?>"/></a>
		<?php };?>
		</td>
		</tr>
		<?php  
		$last_gic_ID=0;
		do { 
			?>
			<tr>
			<?php 
			//Regroupements
                        if ($row_RsInvite_direction['gic_ID']==0){?>
                        	<td valign="bottom" bgcolor="#FFFFFF"><?php echo $row_RsInvite_direction['nom_classe']; ?>&nbsp;</td>
                        	<td valign="bottom" bgcolor="#FFFFFF"><a href="../lire.php?classe_ID=<?php echo strtr(GetSQLValueString($row_RsInvite_direction['ID_classe'],"int"),$protect);?>&matiere_ID=<?php echo strtr(GetSQLValueString($row_RsInvite_direction['ID_matiere'],"int"),$protect);?>&gic_ID=<?php echo strtr(GetSQLValueString($row_RsInvite_direction['gic_ID'],"int"),$protect);?>&regroupement=<?php echo strtr(GetSQLValueString($row_RsInvite_direction['nom_gic'],"text"),$protect);?>&prof_ID=<?php echo strtr(GetSQLValueString($_GET['ID_prof'],"int"),$protect);?>&ordre=down"> <?php echo $row_RsInvite_direction['nom_matiere']; ?></a></td>
                        	</tr>
                        	<?php
                                
                        }
                        else
                        {
                        	//presence de regroupement dans la matiere et la classe
                        	
                        	if ($row_RsInvite_direction['gic_ID']<>$last_gic_ID){ 
                        		// Rechercher le nom du regroupement
                        		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        		$query_RsG =sprintf("SELECT nom_gic FROM cdt_groupe_interclasses WHERE  cdt_groupe_interclasses.ID_gic = %u ",intval(strtr($row_RsInvite_direction['gic_ID'],$protect)));
                        		$RsG = mysqli_query($conn_cahier_de_texte, $query_RsG) or die(mysqli_error($conn_cahier_de_texte));
                        		$row_RsG = mysqli_fetch_assoc($RsG);?>
                        		<td valign="bottom" bgcolor="#FFFFFF"><?php echo '(R) '.$row_RsG['nom_gic'];
                        		$last_gic_ID=$row_RsInvite_direction['gic_ID'];
                        		?>&nbsp;</td>
                        		<td valign="bottom" bgcolor="#FFFFFF"><?php echo $row_RsInvite_direction['nom_matiere']; ?></td>
                        		</tr>
                        		<?php
                        		
                        		mysqli_free_result($RsG);
                        	}
			}
		} while ($row_RsInvite_direction = mysqli_fetch_assoc($RsInvite_direction)); ?>
		</table>
		<?php
		mysqli_free_result($RsInvite_direction); 
	};
};
?>
<p>&nbsp;</p>
<p>&nbsp;</p>
  <p><a href="../index.php">Me d&eacute;connecter </a></p>
<DIV id=footer>
    <p class="auteur"><a href="contribution.php" class="auteur">Pierre Lemaitre 
      - St L&ocirc; (France) - <?php echo $libelle_version ;?> <br />
      </a></p>
</DIV>
</DIV>
<?php if (isset($_GET['envoi_ok'])){?>
	<script type="text/JavaScript"> alert('Votre message a \351t\351 envoy\351'); </script>
<?php };?>
</body>
</html>
