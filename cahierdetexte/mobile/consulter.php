<?php
session_start();
if (!isset($_SESSION['consultation'])OR ($_SESSION['consultation']<>$_GET['classe_ID'])){ header("Location: index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
require_once('../inc/functions_inc.php');
//compteur
if ((isset($_SESSION['affichage_compteur']))&&($_SESSION['affichage_compteur']=='Oui'))	{
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte));
	$query = "UPDATE cdt_params SET param_val = param_val + 1 WHERE param_nom='compteur'; ";
	$update_compteur = mysqli_query($conn_cahier_de_texte, $query);
};
$sql_publier="AND cdt_prof.publier_travail='O'";
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsMatiere = sprintf("SELECT DISTINCT cdt_matiere.nom_matiere,cdt_agenda.matiere_ID,cdt_agenda.classe_ID,cdt_agenda.gic_ID, cdt_agenda.prof_ID FROM cdt_matiere LEFT JOIN cdt_agenda ON cdt_matiere.ID_matiere=cdt_agenda.matiere_ID WHERE cdt_agenda.classe_ID=%u ORDER BY nom_matiere",intval(strtr($_GET['classe_ID'],$protect)));
$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProf_princ = sprintf("SELECT DISTINCT cdt_prof.identite,cdt_prof.email,cdt_prof.email_diffus_restreint FROM cdt_prof_principal,cdt_prof,cdt_groupe, cdt_classe WHERE cdt_prof_principal.pp_prof_ID=cdt_prof.ID_prof AND cdt_prof_principal.pp_classe_ID=%u AND cdt_prof_principal.pp_groupe_ID=cdt_groupe.ID_groupe ORDER BY cdt_prof.identite ASC",intval(strtr($_GET['classe_ID'],$protect)));
$RsProf_princ = mysqli_query($conn_cahier_de_texte, $query_RsProf_princ) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf_princ = mysqli_fetch_assoc($RsProf_princ);
$totalRows_RsProf_princ = mysqli_num_rows($RsProf_princ);
//Consulter le cahier de texte
if (isset($_POST['Submit2'])&& isset($_GET['classe_ID']))
{
	if ($_POST['matiere_ID']<>'value2')
	{
		function ExtractChamp($chaine,$entier,$sep)
                {
                        if($entier > 0)
                        {
                                $res=strtok($chaine,$sep); //decoupe la chaine en segment avec le separateur $sep.
                                if($res!="") //si la chaine comporte au moins une fois le separateur alors
                                {
                                        for($i=1;$i<$entier;$i++)
                                        {
						$res=strtok($sep);//passe au segment suivant
					}
				}
                        }else{
                                $res=false;
                        }
                        return($res);//retourne le resultat
                } ;
                //On recupere en fait ds matiere_ID une chaine du type 4-7 avec matiere = 5 et gic_ID = 7
                // On isole matiere_ID et gic_ID
		$matiere_ID=	ExtractChamp($_POST['matiere_ID'],1,'-');
		$gic_ID=	ExtractChamp($_POST['matiere_ID'],2,'-');
		$prof_ID=	ExtractChamp($_POST['matiere_ID'],3,'-');
		if ($gic_ID==''){$gic_ID=0;};
		if ($gic_ID<>0)	{$reg='&regroupement';} else {$reg='';};
		$GoTo2='lire.php?classe_ID='.strtr(GetSQLValueString($_GET['classe_ID'],"int"),$protect).'&matiere_ID='.GetSQLValueString(strtr($matiere_ID,$protect),"int").'&gic_ID='.GetSQLValueString(strtr($gic_ID,$protect),"int").'&prof_ID='.GetSQLValueString(strtr($prof_ID,$protect),"int").'&ordre='.strtr(GetSQLValueString($_POST['ordre'],"text"),$protect).$reg;
		header(sprintf("Location: %s", $GoTo2));
	}
	else
	{ $erreur2='Vous devez s&eacute;lectionner la mati&egrave;re'; };
}
//fin redirection consultation cahier de texte
if (isset($_POST['groupe'])){$groupe_select=htmlspecialchars(strip_tags($_POST['groupe']));}
else {$groupe_select='Classe entière';};
$choix_groupe_sql='';
if (isset($_POST['groupe'])){
	if ($_POST['groupe']<>"Classe entière"){$choix_groupe_sql="AND (cdt_travail.t_groupe = ". GetSQLValueString(strip_tags($_POST['groupe']),"text")." OR cdt_travail.t_groupe = 'Classe entière' )";};
};
$choix_RsAfaire = "0";
if (isset($_GET['classe_ID'])) {
	$choix_RsAfaire = (get_magic_quotes_gpc()) ? intval($_GET['classe_ID']) : addslashes(intval($_GET['classe_ID']));
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
/*********************************************************
//On affiche uniquement les travaux programmes lors d'une seance anterieure ou egale a la date du jour
//sauf dans le cas d'un devoir programme en dehors des heures de cours(dernier chiffre de code_date =0)
*********************************************************/
if (isset($_POST['date_anciens_travaux_jour'])){ //$codedatejour=substr($_POST['date_anciens_travaux'],6,4).substr($_POST['date_anciens_travaux'],3,2).substr($_POST['date_anciens_travaux'],0,2);
        $codedatejour = $_POST['date_anciens_travaux_annee'].$_POST['date_anciens_travaux_mois'].$_POST['date_anciens_travaux_jour'];  
}
else {$codedatejour=date("Ymd");};
$cd=date('Ymd').'9';
$sql_restriction=' AND ((SUBSTRING(cdt_travail.code_date,1,9) <= '.$cd.') OR (SUBSTRING(cdt_travail.code_date,9,1)=0))';
if ((isset($_GET['tri']))&&($_GET['tri']=='date') OR (!isset($_GET['tri']))){
	$query_RsAfaire = sprintf("SELECT * FROM cdt_prof, cdt_travail, cdt_matiere WHERE cdt_travail.classe_ID=%u AND cdt_travail.matiere_ID=cdt_matiere.ID_matiere AND cdt_travail.prof_ID=cdt_prof.ID_prof %s AND cdt_travail.t_jour_pointe >= %s %s %s ORDER BY cdt_travail.t_jour_pointe",$choix_RsAfaire, $choix_groupe_sql, $codedatejour, $sql_publier, $sql_restriction);
}
else
{
	$query_RsAfaire = sprintf("SELECT * FROM cdt_prof, cdt_travail, cdt_matiere WHERE cdt_travail.classe_ID=%u AND cdt_travail.matiere_ID=cdt_matiere.ID_matiere AND cdt_travail.prof_ID=cdt_prof.ID_prof %s AND cdt_travail.t_jour_pointe >= %s %s %s ORDER BY cdt_matiere.nom_matiere, cdt_travail.t_jour_pointe",$choix_RsAfaire, $choix_groupe_sql, $codedatejour, $sql_publier, $sql_restriction);
}
$RsAfaire = mysqli_query($conn_cahier_de_texte, $query_RsAfaire) or die(mysqli_error($conn_cahier_de_texte));
$row_RsAfaire = mysqli_fetch_assoc($RsAfaire);
$totalRows_RsAfaire = mysqli_num_rows($RsAfaire);
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsgroupe = "SELECT * FROM cdt_groupe ORDER BY groupe ASC";
$Rsgroupe = mysqli_query($conn_cahier_de_texte, $query_Rsgroupe) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
$totalRows_Rsgroupe = mysqli_num_rows($Rsgroupe);
$choix_RsClasse = "0";
if (isset($_GET['classe_ID'])) {
        $choix_RsClasse = (get_magic_quotes_gpc()) ? intval($_GET['classe_ID']) : addslashes(intval($_GET['classe_ID']));
}
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = sprintf("SELECT * FROM cdt_classe WHERE ID_classe=%u", $choix_RsClasse);
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);
$choix_groupe_sql2='';
if (isset($_POST['groupe'])){
	if ($_POST['groupe']<>"Classe entière"){$choix_groupe_sql2="AND (cdt_message_destinataire.groupe_ID = ". GetSQLValueString(strip_tags($_POST['groupe']),"text")." OR cdt_message_destinataire.groupe_ID = 'Classe entière' )";};
};
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsmessage =sprintf("SELECT cdt_message_contenu.ID_message,cdt_message_contenu.message, cdt_message_contenu.date_envoi, cdt_prof.identite,cdt_message_destinataire.groupe_ID,cdt_groupe.groupe FROM cdt_message_contenu,cdt_message_destinataire,cdt_groupe,cdt_prof WHERE cdt_message_contenu.dest_ID<2 AND cdt_message_contenu.ID_message=cdt_message_destinataire.message_ID AND cdt_groupe.ID_groupe=cdt_message_destinataire.groupe_ID AND cdt_message_contenu.online='O' AND cdt_message_contenu.prof_ID= cdt_prof.ID_prof AND cdt_message_destinataire.classe_ID = %s %s
	ORDER BY date_envoi DESC",$choix_RsClasse,$choix_groupe_sql2) ;
$Rsmessage = mysqli_query($conn_cahier_de_texte, $query_Rsmessage) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmessage = mysqli_fetch_assoc($Rsmessage);
$totalRows_Rsmessage = mysqli_num_rows($Rsmessage);
//message a tous les eleves de la part de la vie scolaire ou resp Etab
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsmessage_a_tous ="SELECT * FROM cdt_message_contenu,cdt_prof WHERE cdt_message_contenu.dest_ID=1 AND cdt_message_contenu.online='O' AND cdt_message_contenu.prof_ID= cdt_prof.ID_prof AND cdt_prof.droits>2 ORDER BY date_envoi DESC";
$Rsmessage_a_tous = mysqli_query($conn_cahier_de_texte, $query_Rsmessage_a_tous) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmessage_a_tous = mysqli_fetch_assoc($Rsmessage_a_tous);
$totalRows_Rsmessage_a_tous = mysqli_num_rows($Rsmessage_a_tous);
//test de presence d'au moins un prof publiant le CDT dans la classe selectionnee
//mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
//$query_RsProfpublie = sprintf("SELECT prof_ID,cdt_prof.publier_cdt FROM cdt_emploi_du_temps,cdt_prof WHERE (classe_ID =%s OR classe_ID=0) AND cdt_prof.ID_prof=cdt_emploi_du_temps.prof_ID AND publier_cdt = 'O'", $_GET['classe_ID']);
//$RsProfpublie = mysqli_query($conn_cahier_de_texte, $query_RsProfpublie) or die(mysqli_error($conn_cahier_de_texte));
//$row_RsProfpublie = mysqli_fetch_assoc($RsProfpublie);
//$totalRows_RsProfpublie = mysqli_num_rows($RsProfpublie);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="yes" name="apple-mobile-web-app-capable" />
<meta content="index,follow" name="robots" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="pics/homescreen.png" rel="apple-touch-icon" />
<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />

<link href="css/style.css" rel="stylesheet" media="screen" type="text/css" />
<script src="javascript/functions.js" type="text/javascript"></script>
<title>Smartphone/<?php echo $row_RsClasse['nom_classe'];?> - Travail &agrave; faire </title>
<meta name="description" content="<?php echo $row_RsClasse['nom_classe'];?>">
<?php $classe_nom = $row_RsClasse['nom_classe'];?>
<title><?php echo $row_RsClasse['nom_classe'];?> - Travail &agrave; faire </title>
</head>  
<body > 
<div id="topbar">
<div id="leftnav">
<a href="index.php"><img alt="home" src="images/home.png" /></a></div>&nbsp;<div id="title">CDT &nbsp;<?php echo isset($_SESSION['nom_etab'])?$_SESSION['nom_etab']:''; ?></div>
</div>

<div id="content">


<?php
//affichage du professeur principal
if ($totalRows_RsProf_princ>0){  
        echo '<span class="graytitle">';
        if ($totalRows_RsProf_princ==1){echo $row_RsClasse['nom_classe'].' &ndash; Professeur principal</span><ul class="pageitem">
        	<li class="textbox">
        	<span class="header">
        	<img src="../images/identite.gif" width="16" height="18"> '.$row_RsProf_princ['identite'].'&nbsp;';
        	if (($row_RsProf_princ['email']<>'')&&($row_RsProf_princ['email_diffus_restreint']=='N')){ ?> &nbsp;&nbsp;<a href="mailto:<?php echo $row_RsProf_princ['email'];?>?Subject=Cahier de texte &mdash; Contact avec le professeur principal de la <?php echo $classe_nom." &ndash; ".$row_RsProf_princ['identite']; ?>&body=Bonjour, "><img src="../images/email.gif" width="16" height="14" border="0" /></a>
        	<?php };
        } else 
        {
        	echo 'Professeurs principaux&nbsp</span><ul class="pageitem">
        	<li class="textbox">
        	<span class="header">
        	<img src="../images/identite.gif" width="16" height="18"> ';
        	do {
                        echo $row_RsProf_princ['identite'].'&nbsp;';
                        if (($row_RsProf_princ['email']<>'')&&($row_RsProf_princ['email_diffus_restreint']=='N')){ ?> &nbsp;&nbsp;<a href="mailto:<?php echo $row_RsProf_princ['email'];?>?Subject=Cahier de texte &mdash; Contact avec le professeur principal de la <?php echo $classe_nom." &ndash; ".$row_RsProf_princ['identite']; ?>&body=Bonjour, "><img src="../images/email.gif" width="16" height="14" border="0" /></a>&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php };
                } while ($row_RsProf_princ = mysqli_fetch_assoc($RsProf_princ));
        }; 
        echo '</span></li></ul>';
};?>

<form action="consulter.php?classe_ID=<?php echo $_GET['classe_ID'];?>" method="POST">
<fieldset>
<span class="graytitle">Cahier de textes par mati&egrave;res </span>
<ul class="pageitem"> 
<li class="select"> 

<select name="matiere_ID" id="matiere_ID" >
<option value="value2">S&eacute;lectionner la mati&egrave;re</option>
<?php
do {
	if ($row_RsMatiere['gic_ID']==0){
		//recherche du nom du prof et si ce prof publie en ligne
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_RsP =sprintf("SELECT identite,publier_cdt,stop_cdt FROM cdt_prof WHERE cdt_prof.ID_prof= %s ",$row_RsMatiere['prof_ID']);
		$RsP = mysqli_query($conn_cahier_de_texte, $query_RsP) or die(mysqli_error($conn_cahier_de_texte));
		$row_RsP = mysqli_fetch_assoc($RsP);
		if (($row_RsP['publier_cdt']=='O')&&($row_RsP['stop_cdt']=='N')){
			echo '<option value="'.$row_RsMatiere['matiere_ID'].'-'.$row_RsMatiere['gic_ID'].'-'.$row_RsMatiere['prof_ID'];?>"><?php echo $row_RsMatiere['nom_matiere'].'</option>';
			$nom_matiere_affichee=1;
		}
        }
        else
        {
                //presence de regroupement dans la matiere et la classe
                // Rechercher le nom du regroupement
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_RsG =sprintf("SELECT nom_gic, identite,publier_cdt FROM cdt_groupe_interclasses,cdt_prof WHERE cdt_prof.ID_prof=cdt_groupe_interclasses.prof_ID AND cdt_groupe_interclasses.ID_gic=%u ",$row_RsMatiere['gic_ID']);
                $RsG = mysqli_query($conn_cahier_de_texte, $query_RsG) or die(mysqli_error($conn_cahier_de_texte));
                $row_RsG = mysqli_fetch_assoc($RsG);
                if ($row_RsG['publier_cdt']=='O'){
			echo '<option value="'.$row_RsMatiere['matiere_ID'].'-'.$row_RsMatiere['gic_ID'].'-'.$row_RsMatiere['prof_ID'];?>"><?php echo $row_RsMatiere['nom_matiere'].'</option>';
		}
	};
} while ($row_RsMatiere = mysqli_fetch_assoc($RsMatiere));
$rows = mysqli_num_rows($RsMatiere);
if($rows > 0) {
	mysqli_data_seek($RsMatiere, 0);
	$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
}
?>
</select>
<span class="arrow"></span> </li>
<li class="button">
<input name="groupe" type="hidden" value="Classe entière" />
<input name="Submit2" type="submit" id="Submit2" value="OK" />
<input name="ordre" type="hidden" value="up" /> 
</li>
</ul>
</fieldset>
</form> 

<?php
if ( $totalRows_Rsmessage+$totalRows_Rsmessage_a_tous >0) {?>
	
	<?php
};
if ( $totalRows_Rsmessage >0) { 
	echo'<span class="graytitle">Information pour la '.$row_RsClasse['nom_classe'].'</span>';
	do { ?>
		<?php
		$date_envoi_form=substr($row_Rsmessage['date_envoi'],8,2).'/'.substr($row_Rsmessage['date_envoi'],5,2).'/'.substr($row_Rsmessage['date_envoi'],2,2); 
		echo '<ul class="pageitem">
		<li class="textbox">';
		echo '<span class="header">'.$date_envoi_form.' &mdash; '.$row_Rsmessage['identite'].'</span>';
		?>
		<p>
		<?php
		if ($row_Rsmessage['groupe_ID']>1){echo '<span style=" text-decoration:underline; ">A l\'attention du groupe <b>'.$row_Rsmessage['groupe']. '</b> </span><br /> ';};
		echo $row_Rsmessage['message'];?>
		<?php
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE cdt_message_fichiers.message_ID=".$row_Rsmessage['ID_message'];
		$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
		$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
                if ($totalRows_Rs_fichiers_joints_form>0){
                	if ($totalRows_Rs_fichiers_joints_form>1){echo 'Documents joints : <br /> ';} else {echo 'Document joint : ';};
                	do {
                                $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
                                echo '<a href="../fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'" target=\"_blank\" />'.$nom_f.'</a><br />';
                        } while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
                };?>
                </p></li></ul>
        <?php } while ($row_Rsmessage = mysqli_fetch_assoc($Rsmessage)); ?>  
        
<?php };?>
<?php
if ( $totalRows_Rsmessage_a_tous >0) {	?>
	<?php do { ?>
		<?php
		$date_envoi_form=substr($row_Rsmessage_a_tous['date_envoi'],8,2).'/'.substr($row_Rsmessage_a_tous['date_envoi'],5,2).'/'.substr($row_Rsmessage_a_tous['date_envoi'],2,2);   
		echo '<ul class="pageitem">
		<li class="textbox">';
		echo '<span class="header">'.$date_envoi_form.' &mdash; '.$row_Rsmessage_a_tous['identite'].'</span>';
		?>
		<p>
		<?php
		echo $row_Rsmessage_a_tous['message'];?>
		<?php
		mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
		$query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE cdt_message_fichiers.message_ID=".$row_Rsmessage_a_tous['ID_message'];
		$Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
		$row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
		$totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
		if ($totalRows_Rs_fichiers_joints_form>0){
			if ($totalRows_Rs_fichiers_joints_form>1){echo '<p>Documents joints : <br /> ';} else {echo '<p>Document joint : ';};
			do {
				$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
				echo '<a href="../fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'" target=\"_blank\"/>'.$nom_f.'</a><br />';
			} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
		echo '</p></li></ul>';};?>
	<?php } while ($row_Rsmessage_a_tous = mysqli_fetch_assoc($Rsmessage_a_tous));
};?>

<?php if ($totalRows_RsAfaire==0){
	?>
	<span class="graytitle">Travail &agrave; faire</span>
	<ul class="pageitem">
	<li class="textbox">Aucun travail n'est programm&eacute; pour les
	prochains jours.<br />
	Mais il y a toujours quelque chose &agrave; faire...
	</ul>
	</li>
	<?php
}
##########################################################################################################
else {?>
        <span class="graytitle">Travail &agrave; faire</span>
        <?php
        do {
                // recherche fichiers joints eventuels
                if (isset($row_RsAfaire['agenda_ID'])) {
                        $refagenda_RsFichiers = (get_magic_quotes_gpc()) ?
                        $row_RsAfaire['agenda_ID'] : addslashes($row_RsAfaire['agenda_ID']);
                }
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_RsFichiers = sprintf("SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=%u AND ind_position=%u ORDER BY type", $refagenda_RsFichiers,$row_RsAfaire['ind_position']);
                $RsFichiers = mysqli_query($conn_cahier_de_texte, $query_RsFichiers) or die(mysqli_error($conn_cahier_de_texte));
                $totalRows_RsFichiers = mysqli_num_rows($RsFichiers);
                $codedatevar=substr($row_RsAfaire['t_code_date'],6,4).substr($row_RsAfaire['t_code_date'],3,2).substr($row_RsAfaire['t_code_date'],0,2);
                if (($row_RsAfaire['t_code_date']!='') && ( $codedatevar >= $codedatejour)){?>
                        <ul class="pageitem">
                        <li class="textbox">
                        <span class="header"><?php echo $row_RsAfaire['nom_matiere']. ' - '.$row_RsAfaire['groupe']; $nom=$row_RsAfaire['nom_matiere'];
                        if ($row_RsAfaire['gic_ID']>0){
                                //regroupement / retrouver le nom
                                $query_Rsgic = sprintf("SELECT nom_gic,commentaire_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u",$row_RsAfaire['gic_ID']);
				$Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
				$row_Rsgic = mysqli_fetch_assoc($Rsgic);
                        };
                        ?><br><?php echo $row_RsAfaire['identite']; ?>
                        <?php
                        if (($row_RsAfaire['email']<>'')&&($row_RsAfaire['email_diffus_restreint']=='N')){ ?> <a href="mailto:<?php echo $row_RsAfaire['email'];?>?Subject=Cahier de textes <?php echo $row_RsAfaire['nom_matiere']; ?> &mdash; travail &agrave; faire du <?php echo jour_semaine($row_RsAfaire['t_code_date']).' '.substr($row_RsAfaire['t_code_date'],0,2).'-'.substr($row_RsAfaire['t_code_date'],3,2).'-'.substr($row_RsAfaire['t_code_date'],6,4).' &mdash; '.$row_RsClasse['nom_classe']; ?>&body=Bonjour M. (ou Mme) <?php echo $row_RsAfaire['identite']; ?>, ">
                                <img src="../images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant" title="Contacter l'enseignant"/></a>
                        <?php };?>
                        </span>
			<p>
			<?php
                        echo'Pour le <b>'.strtolower(jour_semaine($row_RsAfaire['t_code_date'])).' '.substr($row_RsAfaire['t_code_date'],0,2).'-'.substr($row_RsAfaire['t_code_date'],3,2).'-'.substr($row_RsAfaire['t_code_date'],6,4).' </b><br>';
                        
                        if ((((substr($row_RsAfaire['code_date'],8,1)==0)&&(substr($row_RsAfaire['t_code_date'],2,1)=='/'))||($row_RsAfaire['eval']=='O'))&&(isset($_SESSION['libelle_devoir']))){echo ' <span style="color:#FF0000">'.$_SESSION['libelle_devoir'].'&nbsp; : </span>';};
                        //nettoyage des balises <p> et des <br> supplementaires :      
                        $contenu = str_replace("<p>", "<br>", $row_RsAfaire['travail']);
                        $contenu = str_replace("</p>", "", $contenu);
                        $contenu = trim($contenu); $contenu = trim($contenu); 
			IF (substr($contenu, 0, 4) == "<br>") $contenu = substr($contenu, 4);
			IF (substr($contenu, -4, 4) == "<br>") $contenu = substr($contenu, 0, strlen($contenu)-4); 
			IF (substr($contenu, 0, 5) == "<br />") $contenu = substr($contenu, 5);
			IF (substr($contenu, -6, 6) == "<br />") $contenu = substr($contenu, 0, strlen($contenu)-6); 
			$contenu = str_replace("<br><br><br>", "<br />", $contenu);
			$contenu = str_replace("<br><br>", "<br />", $contenu);
			$contenu = str_replace("<br /><br />", "<br />", $contenu);
			$contenu = str_replace("<br /><br /><br />", "<br />", $contenu);
			echo $contenu;
			//echo $row_RsAfaire['travail']; 
			//echo strip_tags($row_RsAfaire['travail'], '<a><img>'); 
			//affichage des fichiers joints              
			if ($totalRows_RsFichiers<>0)
			{
				while ($row_RsFichiers = mysqli_fetch_assoc($RsFichiers)) {
					//ne pas afficher les fichiers de cours si cdt desactive (non publie)
						if ((($row_RsFichiers['type']=='Cours')&&($row_RsAfaire['publier_cdt']=='O'))||($row_RsFichiers['type']<>'Cours')){
				
					$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_RsFichiers['nom_fichier']); ?>
					<?php //echo $row_RsFichiers['type'].' ';?><br />
					<a href="../fichiers_joints/<?php echo $row_RsFichiers['nom_fichier'];  ?>" target="_blank"><strong><?php echo $nom_f;  ?></strong></a> 			
					
					<?php
				}
				} 
				mysqli_free_result($RsFichiers);
			}?> 
			</p>
			</li>
			</ul>
			<?php
		}
	} while ($row_RsAfaire = mysqli_fetch_assoc($RsAfaire)); ?>
	<?php
}
?> 
<ul class="pageitem">
<li class="textbox"> 
<fieldset>
<form name="frm" action="consulter.php?classe_ID=<?php echo intval($_GET['classe_ID']);?>&amp;tri=date&amp;" method="post">
<p>
Afficher tous les anciens travaux donn&eacute;s depuis le
</p>
</li>   
<li class="select">     
<table width="90%" border="0" align="center">
<tr>
<td align="center" width="20%">
<select name="date_anciens_travaux_jour"> 
<?php
for($i=1;$i < 32;$i++) {   
	echo '<option value="'.($i<10? '0':'').$i.'"'.($i==date("d")? ' selected':'').'> '.($i<10? '0':'').$i.'</option>';
}
?>
</select></td> <td width="5%">/</td>
<td align="center" width="42%"><select name="date_anciens_travaux_mois">  
<?php
$month= array(
        1=>'janvier',
        2=>'f&eacute;vrier',
        3=>'mars',
        4=>'avril',
        5=>'mai',
        6=>'juin',
        7=>'juillet',
        8=>'ao&ucirc;t',
        9=>'septembre',
        10=>'octobre',
        11=>'novembre',
        12=>'d&eacute;cembre',
        );  
for($i=1;$i < 13;$i++) {   
        echo '<option value="'.($i<10? '0':'').$i.'"'.($i==date("m")? ' selected':'').'> '.$month[$i].'</option>';
}
?>
</select> </td>   <td width="5%">/</td>
<td align="center"width="27%"><select name="date_anciens_travaux_annee">
<option value="2010"> <?php echo (date("Y")-1);?></option>
<option value="2011"  selected> <?php echo date("Y");?></option>
</select>
</td>
</tr>
</table>
</li>
<li class="button">
<input type="submit" name="Submit_6" value="Afficher" />
</form>  
</fieldset>
</li>
</ul>
</div>    

<?php 
include("footer.php");

if(!empty($_SESSION['URL_Piwik']) && !empty($_SESSION['ID_Piwik'])) //sonde active des que les 2 parametres sont valides
    {
    echo '
    <!-- Piwik -->
    <script type="text/javascript">
    var pkBaseURL = ("https:" == document.location.protocol) ? "https://" : "http://";
    pkBaseURL += "'.$_SESSION['URL_Piwik'].'";
    document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E"));
    </script><script type="text/javascript">
    try {
      var piwikTracker = Piwik.getTracker(pkBaseURL+"piwik.php",'.$_SESSION['ID_Piwik'].');
      piwikTracker.trackPageView();
          piwikTracker.enableLinkTracking();
    } catch( err ) {}
    </script>
    <!-- End Piwik Code -->';
    }
?>
</body>
</html>
<?php
if (isset($Rsgroupe)){mysqli_free_result($Rsgroupe);};
if (isset($RsMatiere)){mysqli_free_result($RsMatiere);};
mysqli_free_result($RsClasse);
mysqli_free_result($RsProf_princ);
mysqli_free_result($RsAfaire);
?>
