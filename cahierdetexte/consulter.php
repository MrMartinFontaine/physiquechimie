<?php 
session_start();
if (!isset($_SESSION['consultation'])OR ($_SESSION['consultation']<>$_GET['classe_ID'])){  header("Location: index.php");exit;};
if ((!isset($_SESSION['url_deconnecte_eleve'])) OR (!isset($_SESSION['libelle_devoir']))){  header("Location: index.php");exit;};
require_once('Connections/conn_cahier_de_texte.php');
require_once('inc/functions_inc.php');

if (isset($_GET['tri'])){$tri=htmlspecialchars(strip_tags( $_GET['tri']));};

// couleur des lignes de taf  jaune pale : #efe    bleu pale : #E6F0FA ou #DBEAF9
$coul_taf_fait='#ffff99';
$coul_taf_nonfait='#E6F0FA';


if(function_exists("date_default_timezone_set")){ //fonction PHP 5 
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_time_zone_db = "SELECT param_val FROM cdt_params WHERE param_nom='time_zone'";
$time_zone_db = mysqli_query($conn_cahier_de_texte, $query_time_zone_db) or die(mysqli_error($conn_cahier_de_texte));
$row_time_zone_db = mysqli_fetch_assoc($time_zone_db);
date_default_timezone_set($row_time_zone_db['param_val']);
};


//compteur
if ((isset($_SESSION['affichage_compteur']))&&($_SESSION['affichage_compteur']=='Oui')) {
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) or die(mysqli_error($conn_cahier_de_texte)); 		
	$query = "UPDATE cdt_params SET param_val = param_val + 1 WHERE param_nom='compteur'; ";
	$update_compteur = mysqli_query($conn_cahier_de_texte, $query);
};


$sql_publier="AND cdt_prof.publier_travail='O'";

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);

$query_RsMatiere = sprintf("SELECT DISTINCT cdt_matiere.nom_matiere,cdt_agenda.matiere_ID,cdt_agenda.classe_ID,cdt_agenda.gic_ID, cdt_agenda.prof_ID,cdt_agenda.partage FROM cdt_matiere LEFT JOIN cdt_agenda ON cdt_matiere.ID_matiere=cdt_agenda.matiere_ID WHERE cdt_agenda.classe_ID=%u ORDER BY nom_matiere",intval(strtr($_GET['classe_ID'],$protect)));
$RsMatiere = mysqli_query($conn_cahier_de_texte, $query_RsMatiere) or die(mysqli_error($conn_cahier_de_texte));
$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsProf_princ = sprintf("SELECT DISTINCT cdt_prof.identite,cdt_prof.email,cdt_prof.email_diffus_restreint FROM cdt_prof_principal,cdt_prof,cdt_groupe, cdt_classe WHERE cdt_prof_principal.pp_prof_ID=cdt_prof.ID_prof AND cdt_prof_principal.pp_classe_ID=%u AND cdt_prof_principal.pp_groupe_ID=cdt_groupe.ID_groupe ORDER BY cdt_prof.identite ASC",intval(strtr($_GET['classe_ID'],$protect)));
$RsProf_princ = mysqli_query($conn_cahier_de_texte, $query_RsProf_princ) or die(mysqli_error($conn_cahier_de_texte));
$row_RsProf_princ = mysqli_fetch_assoc($RsProf_princ);
$totalRows_RsProf_princ = mysqli_num_rows($RsProf_princ);

//Consulter le cahier de textes
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
                
                //On recupere en fait ds matiere_ID une chaine du type 4-7  avec matiere = 5 et gic_ID = 7
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
if (isset($_POST['date_anciens_travaux'])){ $codedatejour=substr($_POST['date_anciens_travaux'],6,4).substr($_POST['date_anciens_travaux'],3,2).substr($_POST['date_anciens_travaux'],0,2);}
else {$codedatejour=date("Ymd");};


$cd=date('Ymd').'9'; 
$sql_restriction=' AND ((SUBSTRING(cdt_travail.code_date,1,9) <= '.$cd.') OR (
(SUBSTRING(cdt_travail.code_date,9,1)=0)  AND  (SUBSTRING(cdt_travail.t_code_date,3,1)="/")
))';


if ((isset($tri))&&($tri=='date') OR (!isset($tri))){
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
$query_Rsmessage =sprintf("SELECT cdt_message_contenu.ID_message,cdt_message_contenu.message, cdt_message_contenu.date_envoi,cdt_message_contenu.date_fin_publier, cdt_prof.identite,cdt_message_destinataire.groupe_ID,cdt_groupe.groupe FROM cdt_message_contenu,cdt_message_destinataire,cdt_groupe,cdt_prof WHERE cdt_message_contenu.dest_ID<2 AND  cdt_message_contenu.ID_message=cdt_message_destinataire.message_ID AND cdt_groupe.ID_groupe=cdt_message_destinataire.groupe_ID AND cdt_message_contenu.online='O' AND cdt_message_contenu.prof_ID= cdt_prof.ID_prof AND cdt_message_destinataire.classe_ID = %s AND cdt_message_contenu.date_fin_publier>= '%s' %s
	
	ORDER BY date_envoi DESC",$choix_RsClasse,date('Y-m-d'),$choix_groupe_sql2) ;



$Rsmessage = mysqli_query($conn_cahier_de_texte, $query_Rsmessage) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmessage = mysqli_fetch_assoc($Rsmessage);
$totalRows_Rsmessage = mysqli_num_rows($Rsmessage);

//message a tous les eleves de la part de la vie scolaire ou resp Etab

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rsmessage_a_tous ="SELECT * FROM cdt_message_contenu,cdt_prof WHERE cdt_message_contenu.dest_ID=1 AND cdt_message_contenu.online='O' AND cdt_message_contenu.prof_ID= cdt_prof.ID_prof AND cdt_prof.droits>2 ORDER BY date_envoi DESC";
$Rsmessage_a_tous = mysqli_query($conn_cahier_de_texte, $query_Rsmessage_a_tous) or die(mysqli_error($conn_cahier_de_texte));
$row_Rsmessage_a_tous = mysqli_fetch_assoc($Rsmessage_a_tous);
$totalRows_Rsmessage_a_tous = mysqli_num_rows($Rsmessage_a_tous);

//evenement

	    require_once('inc/even_a_venir_inc.php');	
/*
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rseven =sprintf("SELECT titre_even,detail,date_envoi,date_debut,date_fin,heure_debut,heure_fin,identite FROM cdt_evenement_contenu,cdt_evenement_destinataire, cdt_prof WHERE 
	cdt_evenement_contenu.ID_even=cdt_evenement_destinataire.even_ID 
	AND cdt_evenement_destinataire.classe_ID = %s 
	AND cdt_evenement_contenu.date_envoi <='%s'  
	AND cdt_evenement_contenu.date_fin >='%s'  
	AND cdt_evenement_contenu.prof_ID = cdt_prof.ID_prof
	ORDER BY date_debut", $choix_RsClasse, date('Y-m-d'), date('Y-m-d')) ;
*/
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_Rseven = sprintf("SELECT titre_even,detail,date_envoi,date_debut,date_fin,heure_debut,heure_fin,identite FROM cdt_evenement_contenu,cdt_evenement_destinataire, cdt_prof
			WHERE  ((  date_debut >= {$datemini}  	AND  date_debut <= {$datemaxi} ) 
				OR (  date_debut < {$datemini}  	AND  date_fin >= {$datemini} ))
					AND cdt_evenement_contenu.prof_ID = cdt_prof.ID_prof
					AND cdt_evenement_contenu.ID_even=cdt_evenement_destinataire.even_ID 
	                AND cdt_evenement_destinataire.classe_ID = %s 
				ORDER BY date_debut , heure_debut  ",$choix_RsClasse); 
$Rseven = mysqli_query($conn_cahier_de_texte, $query_Rseven) or die(mysqli_error($conn_cahier_de_texte));
$row_Rseven = mysqli_fetch_assoc($Rseven);
$totalRows_Rseven = mysqli_num_rows($Rseven);

//parametre autorisant ou interdisant affichage icone facebook
$query_read2= "SELECT `param_val` FROM `cdt_params` WHERE `param_nom`='facebook_icon' LIMIT 1;";
$result_read2 = mysqli_query($conn_cahier_de_texte, $query_read2);
$row2 = mysqli_fetch_row($result_read2);
$facebook_icon = $row2[0];

//recup de la semaine

$date_sem=date('Ymd');
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsSemdate =  sprintf("SELECT * FROM cdt_semaine_ab WHERE s_code_date<=%s ORDER BY `s_code_date` DESC LIMIT 1 ",$date_sem);
$RsSemdate = mysqli_query($conn_cahier_de_texte, $query_RsSemdate) or die(mysqli_error($conn_cahier_de_texte));
$row_RsSemdate= mysqli_fetch_assoc($RsSemdate);
if ((isset($_SESSION['libelle_semaine']))&&($_SESSION['libelle_semaine']==1)){
	if ($row_RsSemdate['semaine']=='A et B'){$_SESSION['semdate_libelle']='P et I';} else if($row_RsSemdate['semaine']=='A'){$_SESSION['semdate_libelle']='Paire';} else {$_SESSION['semdate_libelle']='Impaire';};
}
else {$_SESSION['semdate_libelle']=$row_RsSemdate['semaine'];};

$_SESSION['semdate']=$row_RsSemdate['semaine'];


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="robots" content="noindex">
<meta name="Description" content="<?php echo $row_RsClasse['nom_classe'];?>" />
<title><?php echo $row_RsClasse['nom_classe'];?> - Travail &agrave; faire</title>
<link href="./styles/style_default.css" rel="stylesheet" type="text/css" />
<link href="./styles/arrondis.css" rel="stylesheet" type="text/css">
<link href="./templates/default/perso.css" rel="stylesheet" type="text/css" />
<link type="text/css" href="./styles/jquery-ui-1.8.13.custom_perso.css" rel="stylesheet" />     

<style>
form{ margin:5px; padding:0;}
.bordure_grise {border: 1px solid #CCCCCC;}
.no_travail {font-size: 16px}
</style>
<script type="text/javascript" src="enseignant/xinha/plugins/Equation/ASCIIMathML.js"></script>
<script type="text/javascript" src="./jscripts/jquery-1.6.2.js"></script>
<script type="text/javascript" src="./jscripts/jquery-ui.datepicker-fr.js"></script>
<script type="text/javascript" src="./jscripts/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript">
function setCookie(c_name,value,expiredays) {
        var exdate=new Date()
        exdate.setDate(exdate.getDate()+expiredays)
        document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate)
    }

function getCookie(c_name) {
        if (document.cookie.length>0) {
            c_start=document.cookie.indexOf(c_name + "=")
            if (c_start!=-1) { 
                c_start=c_start + c_name.length+1 
                c_end=document.cookie.indexOf(";",c_start)
                if (c_end==-1) c_end=document.cookie.length
                    return unescape(document.cookie.substring(c_start,c_end))
            } 
        }
        return null
    }


function set_check(taf_id){
        setCookie(taf_id, document.getElementById(taf_id).checked? 1 : 0, 3600);
        this.id='ligne'+taf_id; 
        if (document.getElementById(taf_id).checked)
        {
        document.getElementById(this.id).style.background = '<?php echo $coul_taf_fait;?>'; 
        }
        else {  document.getElementById(this.id).style.background = '<?php echo $coul_taf_nonfait;?>'; }
}

</script>
</head>
<body >

<br />
<table class="lire_bordure" width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
<tr class="lire_cellule_4">
<td width="30%" >CAHIER DE TEXTES </td>
<td width="56%">
<?php 
echo '&nbsp;&nbsp;'.$row_RsClasse['nom_classe']. '  -  '.$groupe_select;
if (  $totalRows_Rsmessage+$totalRows_Rsmessage_a_tous+$totalRows_Rseven  >0) {echo '&nbsp;&nbsp;&nbsp; - Informations';};
?>
</td>
<td width="14%"><div align="right">

<a href="<?php  
if ((isset($_SESSION['droits']))&&($_SESSION['droits']==2)){echo 'enseignant/enseignant.php';} 
else 
if ((isset($_SESSION['droits']))&&($_SESSION['droits']==3)){echo 'vie_scolaire/vie_scolaire.php';}
else 
if ((isset($_SESSION['droits']))&&($_SESSION['droits']==6)){echo 'assistant_education/assistant_educ.php';}
else 
if (isset($_SESSION['url_deconnecte_eleve'])){echo $_SESSION['url_deconnecte_eleve']; }
else {echo 'index.php';};?>">
<img src="images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a>
</div></td>
</tr>
<tr class="lire_cellule_2">
<?php 
// afficher si au moins un prof publie en ligne
?>
<td  >
<?php 
if ( $totalRows_Rsmessage+$totalRows_Rsmessage_a_tous +$totalRows_Rseven ==0) {?>
<div style="float:left; display:inline;"><br />
<img src="images/cahier.png" width="135" height="128"  /></div>
<?php };?>

<?php 
if ( $totalRows_Rsmessage+$totalRows_Rsmessage_a_tous +$totalRows_Rseven ==0) {?>
        </td>
        <td colspan="2" valign="middle" ><table class="lire_bordure" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
<?php };?><br />
<span style="font-size: 12px;"><strong>Consulter le cahier de textes par mati&egrave;res</strong></span> <br /><br />
<form action="consulter.php?classe_ID=<?php echo $_GET['classe_ID'];?>"  method="POST">
<select  name="matiere_ID" id="matiere_ID" style="font-size:12px" onChange='this.form.submit()'>
<option value="value2">S&eacute;lectionner la mati&egrave;re</option>
<?php
do {    
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$query_RsP =sprintf("SELECT identite,publier_cdt,stop_cdt,nom_prof,id_remplace,id_etat FROM cdt_prof WHERE ID_prof= %u ",$row_RsMatiere['prof_ID']);
        $RsP = mysqli_query($conn_cahier_de_texte, $query_RsP) or die(mysqli_error($conn_cahier_de_texte));
        $row_RsP = mysqli_fetch_assoc($RsP);
                
        if ($row_RsMatiere['gic_ID']==0){
                //recherche du nom du prof et si ce prof publie en ligne
                                // modif remplacant recherche si id_etat = 0 ou 2 et dans le cas de id_etat=2 il faut aussi que id_remplace!=0
 $visu_liste="no";
             // selectionner les remplacants de l'enseignant s'il y en a
                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_RsListe =sprintf("SELECT * FROM cdt_prof WHERE id_etat=2 AND ID_remplace=%u ",$row_RsMatiere['prof_ID']);
                $RsListe = mysqli_query($conn_cahier_de_texte, $query_RsListe) or die(mysqli_error($conn_cahier_de_texte));
                $row_RsListe = mysqli_fetch_assoc($RsListe);
                           // si le titulaire est absent mais pas de suppleant on affiche le titulaire
                                if ($row_RsP['id_etat']==1)
								{
									if($row_RsListe<=0)
										{$visu_liste="ok";}
								else
						// Il  y a un remplacant mais il n'a pas encore rempli son cdt, il faut garder le titulaire
									{
							mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
							$query_RsListe2 =sprintf("SELECT * FROM cdt_agenda WHERE (prof_ID=%u AND matiere_ID=%u AND classe_ID=%u)",$row_RsListe['ID_prof'],$row_RsMatiere['matiere_ID'],$row_RsMatiere['classe_ID']);
							$RsListe2 = mysqli_query($conn_cahier_de_texte, $query_RsListe2) or die(mysqli_error($conn_cahier_de_texte));
							$row_RsListe2 = mysqli_fetch_assoc($RsListe2);
									if($row_RsListe2<=0)
										{$visu_liste="ok";}
									}
								}
                                // on affiche le titulaire present ou si le suppleant n'a pas fini son remplacement on affiche
                                if (($row_RsP['id_etat']==0) ||(($row_RsP['id_etat']==2)&&($row_RsP['id_remplace']!=0)))
                                {$visu_liste="ok";}
                if (($row_RsP['publier_cdt']=='O')&&($row_RsP['stop_cdt']=='N')&& ($visu_liste=="ok")){
                        echo '<option value="'.$row_RsMatiere['matiere_ID'].'-'.$row_RsMatiere['gic_ID'].'-'.$row_RsMatiere['prof_ID'];?>">
                        <?php echo $row_RsMatiere['nom_matiere'].'  -  (';
                        if ($row_RsMatiere['partage']=='O') {
                                echo "Plusieurs enseignants : ";
                                echo $row_RsP['identite']==''?$row_RsP['nom_prof']:$row_RsP['identite'];
                                echo "...";
                        } else {
                                echo $row_RsP['identite']==''?$row_RsP['nom_prof']:$row_RsP['identite'];
                        }
                        echo ')</option>';
                }

        }
        else
        {
                //presence de regroupement dans la matiere et la classe
                // Rechercher le nom du regroupement
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_RsG =sprintf("SELECT nom_gic,identite,publier_cdt,prof_ID FROM cdt_groupe_interclasses,cdt_groupe_interclasses_classe,cdt_prof WHERE 
cdt_prof.ID_prof=cdt_groupe_interclasses.prof_ID AND 
cdt_groupe_interclasses.ID_gic = %u AND 
cdt_groupe_interclasses_classe.gic_ID=cdt_groupe_interclasses.ID_gic AND 
cdt_groupe_interclasses_classe.classe_ID = %u"  
,$row_RsMatiere['gic_ID'],$_GET['classe_ID']);
$RsG = mysqli_query($conn_cahier_de_texte, $query_RsG) or die(mysqli_error($conn_cahier_de_texte));
$row_RsG = mysqli_fetch_assoc($RsG);

// Rechercher s'il faut fusionner
$fusion=0;
mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsEdt =sprintf("SELECT fusion_gic FROM cdt_emploi_du_temps WHERE 

cdt_emploi_du_temps.gic_ID=%u AND
cdt_emploi_du_temps.matiere_ID= %u AND
cdt_emploi_du_temps.prof_ID=%u
"
,$row_RsMatiere['gic_ID'],$row_RsMatiere['matiere_ID'],$row_RsG['prof_ID']);
$RsEdt = mysqli_query($conn_cahier_de_texte, $query_RsEdt) or die(mysqli_error($conn_cahier_de_texte));
$row_RsEdt = mysqli_fetch_assoc($RsEdt);
if ((isset($row_RsEdt['fusion_gic']))&&($row_RsEdt['fusion_gic']=='O')){$fusion=1;};
mysqli_free_result($RsEdt);


                if (($row_RsG['publier_cdt']=='O')&&($fusion==0))
                {                      
                        echo '<option value="'.$row_RsMatiere['matiere_ID'].'-'.$row_RsMatiere['gic_ID'].'-'.$row_RsMatiere['prof_ID'];?>">
                        <?php echo $row_RsMatiere['nom_matiere'].'  -  (';
                        if ($row_RsMatiere['partage']=='O') {
                                echo "Plusieurs enseignants : ";
                                echo $row_RsP['identite']==''?$row_RsP['nom_prof']:$row_RsP['identite'];
                                echo "...";
                        } else {
                                echo $row_RsP['identite']==''?$row_RsP['nom_prof']:$row_RsP['identite'];
                        }
                        echo ') - (R) -> '.$row_RsG['nom_gic'].'</option>';
                }
        };
	mysqli_free_result($RsP);
        
} while ($row_RsMatiere = mysqli_fetch_assoc($RsMatiere));
$rows = mysqli_num_rows($RsMatiere);
if($rows > 0) {
	mysqli_data_seek($RsMatiere, 0);
	$row_RsMatiere = mysqli_fetch_assoc($RsMatiere);
}
?>
</select>
<input name="groupe" type="hidden" value="Classe entière" />
<input name="Submit2" type="hidden" id="Submit2" value="OK" />
<input name="ordre" type="hidden" value="up" />
</form>
<p>
<form action="cours_du_jour.php?classe_ID=<?php echo strtr(GetSQLValueString($_GET['classe_ID'],"int"),$protect); ?>" method="post">
<input name="Submit3" type="submit" id="Submit3" value="Afficher les derniers cours en date" />
</form>
</p>
<br />
<?php 
if ( $totalRows_Rsmessage+$totalRows_Rsmessage_a_tous + $totalRows_Rseven>0) {?>



        <td colspan="2" valign="middle" >

        <table class="lire_bordure" width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
        <?php 
};

if ( $totalRows_Rsmessage  >0) {        
        do { ?>
                <tr>
                <td width="10%" class="tab_detail">
                                <?php 
                                
                   
                $date_envoi_form=substr($row_Rsmessage['date_envoi'],8,2).'/'.substr($row_Rsmessage['date_envoi'],5,2).'/'.substr($row_Rsmessage['date_envoi'],2,2);
                
                echo '<span class="date_message">'.$date_envoi_form.'<br />'.$row_Rsmessage['identite'].'</span>'; 
                ?>
                .
                                <br /></td>
                <td class="tab_detail"><?php  
                
                
                if ($row_Rsmessage['groupe_ID']>1){echo '<span style=" text-decoration:underline; ">A l\'attention du groupe <b>'.$row_Rsmessage['groupe']. '</b> </span><br /> ';};
                echo $row_Rsmessage['message'];?>
                <?php
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE message_ID=".$row_Rsmessage['ID_message'];
                $Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
                $row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
                $totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
                if ($totalRows_Rs_fichiers_joints_form>0){
                	if ($totalRows_Rs_fichiers_joints_form>1){echo '<p>Documents joints : <br /> ';} else {echo '<p>Document joint : ';};
                	do {
                		$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
                		echo '<a href="./fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'" target=\"_blank\" />'.$nom_f.'</a><br />';
                	} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
                echo '</p>';};?></td>
                </tr>
        <?php } while ($row_Rsmessage = mysqli_fetch_assoc($Rsmessage)); ?>
<?php };?>
<?php 
if ( $totalRows_Rsmessage_a_tous  >0) {	?>
	<?php do { ?>
                <tr>
                <td width="10%" class="tab_detail"><?php 
                $date_envoi_form=substr($row_Rsmessage_a_tous['date_envoi'],8,2).'/'.substr($row_Rsmessage_a_tous['date_envoi'],5,2).'/'.substr($row_Rsmessage_a_tous['date_envoi'],2,2);
                
                echo '<span class="date_message">'.$date_envoi_form.'<br />'.$row_Rsmessage_a_tous['identite'].'</span>'; 
                ?>
                <br /></td>
                <td class="tab_detail"><?php  
                
                echo $row_Rsmessage_a_tous['message'];?>
                <?php
                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                $query_Rs_fichiers_joints_form="SELECT * FROM cdt_message_fichiers WHERE message_ID=".$row_Rsmessage_a_tous['ID_message'];
                $Rs_fichiers_joints_form = mysqli_query($conn_cahier_de_texte, $query_Rs_fichiers_joints_form) or die(mysqli_error($conn_cahier_de_texte));
                $row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form);
                $totalRows_Rs_fichiers_joints_form = mysqli_num_rows($Rs_fichiers_joints_form);
                if ($totalRows_Rs_fichiers_joints_form>0){
                	if ($totalRows_Rs_fichiers_joints_form>1){echo '<p>Documents joints : <br /> ';} else {echo '<p>Document joint : ';};
                	do {
                		$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_Rs_fichiers_joints_form['nom_fichier']);
                		echo '<a href="./fichiers_joints_message/'.$row_Rs_fichiers_joints_form['nom_fichier'].'" target=\"_blank\"/>'.$nom_f.'</a><br />';
                	} while ($row_Rs_fichiers_joints_form = mysqli_fetch_assoc($Rs_fichiers_joints_form));
                echo '</p>';};?></td>
                </tr>
        <?php } while ($row_Rsmessage_a_tous = mysqli_fetch_assoc($Rsmessage_a_tous)); 
};			
//affichage des evenements
if ($totalRows_Rseven>0) {	
	do { ?>
                <tr>
                <td width="10%" class="tab_detail"><?php $row_Rseven['date_envoi'];

                echo '<span class="date_message">'; 
					if ($row_Rseven['date_envoi']<>'0000-00-00'){
					$date_envoi_form=substr($row_Rseven['date_envoi'],8,2).'/'.substr($row_Rseven['date_envoi'],5,2).'/'.substr($row_Rseven['date_envoi'],2,2);
					echo $date_envoi_form.'<br />';
					} ;				
				echo $row_Rseven['identite'].'</span>'; ?>
                <br />
                </td>
                <td class="tab_detail"><?php  
                if ($row_Rseven['date_debut']==$row_Rseven['date_fin']){
                	echo 'Le '.jour_semaine(substr($row_Rseven['date_debut'],8,2).'/'.substr($row_Rseven['date_debut'],5,2).'/'.substr($row_Rseven['date_debut'],0,4)).' '.substr($row_Rseven['date_debut'],8,2).'/'.substr($row_Rseven['date_debut'],5,2).'/'.substr($row_Rseven['date_debut'],0,4).' -  De '.$row_Rseven['heure_debut'].' &agrave; '.$row_Rseven['heure_fin'];
                }
                else {
                	echo 'Du '.jour_semaine(substr($row_Rseven['date_debut'],8,2).'/'.substr($row_Rseven['date_debut'],5,2).'/'.substr($row_Rseven['date_debut'],0,4)).' '.substr($row_Rseven['date_debut'],8,2).'/'.substr($row_Rseven['date_debut'],5,2).'/'.substr($row_Rseven['date_debut'],0,4);
                	echo ' au '.jour_semaine(substr($row_Rseven['date_fin'],8,2).'/'.substr($row_Rseven['date_fin'],5,2).'/'.substr($row_Rseven['date_fin'],0,4)).' '.substr($row_Rseven['date_fin'],8,2).'/'.substr($row_Rseven['date_fin'],5,2).'/'.substr($row_Rseven['date_fin'],0,4);
                };
                echo '<br /><strong>'.$row_Rseven['titre_even'].'</strong><br />'.$row_Rseven['detail'];?>
                
                </td>
                </tr>
        <?php } while ($row_Rseven = mysqli_fetch_assoc($Rseven));			
        
};


?>
</table>
</td>
</tr>
</table>
<br />
<?php 
//affichage du professeur principal
if ($totalRows_RsProf_princ>0){ 
	if ($totalRows_RsProf_princ==1){echo '<strong>Professeur principal&nbsp;:&nbsp;&nbsp;</strong><img src="images/identite.gif" width="16" height="18"> '.$row_RsProf_princ['identite'].'&nbsp;';
		if (($row_RsProf_princ['email']<>'')&&($row_RsProf_princ['email_diffus_restreint']=='N')){ ?>
			&nbsp;&nbsp;<a href="mailto:<?php echo $row_RsProf_princ['email'];?>"><img alt="Contacter l'enseignant &nbsp; <?php echo $row_RsProf_princ['email'];?>" title="Contacter l'enseignant &nbsp; <?php echo $row_RsProf_princ['email'];?>" src="images/email.gif" width="16" height="14" border="0" /></a>
		<?php };
	} else {
		echo '<strong>Professeurs principaux&nbsp;:&nbsp;&nbsp;</strong>';
		do {
			echo '<img src="images/identite.gif" width="16" height="18"> '.$row_RsProf_princ['identite'].'&nbsp;';
			if (($row_RsProf_princ['email']<>'')&&($row_RsProf_princ['email_diffus_restreint']=='N')){ ?>
				&nbsp;&nbsp;<a href="mailto:<?php echo $row_RsProf_princ['email'];?>"><img alt="Contacter l'enseignant &nbsp; <?php echo $row_RsProf_princ['email'];?>" title="Contacter l'enseignant &nbsp; <?php echo $row_RsProf_princ['email'];?>" src="images/email.gif" width="16" height="14" border="0" /></a>&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php };
                } while ($row_RsProf_princ = mysqli_fetch_assoc($RsProf_princ));
        };
        echo '<br /><br />';
};

echo('<a name="travauxafaire"></a>');
if ($totalRows_RsAfaire==0){ 
        ?>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <table  width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr valign="middle" class="lire_cellule_4">
        <td colspan="2" > TRAVAIL A FAIRE en <?php echo $row_RsClasse['nom_classe']. '  -  '.$groupe_select;?> </td>
        <td colspan="2"  align="left" valign="middle" ><?php 
        $today=date("d-m-Y");echo 'Nous sommes le '.jour_semaine($today).' '.$today;
        echo ' -  Semaine '.$_SESSION['semdate_libelle']; 
        ?></td>
        <td  align="left" valign="bottom" ><div align="right"><a href="<?php  
                if ((isset($_SESSION['droits']))&&($_SESSION['droits']==2)){echo 'enseignant/enseignant.php';} 
                else if((isset($_SESSION['droits']))&&($_SESSION['droits']==3)){echo 'vie_scolaire/vie_scolaire.php';}
                else if ((isset($_SESSION['droits']))&&($_SESSION['droits']==6)){echo 'assistant_education/assistant_educ.php';}                
                else if (isset($_SESSION['url_deconnecte_eleve'])){echo $_SESSION['url_deconnecte_eleve'];}
        else {echo 'index.php';};?>">
                
                
                <img src="images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></div></td>
        </tr>
        <tr valign="middle" >
        <td width="2%" class="lire_cellule_2" ></td>
        <td class="lire_cellule_2" ><p>&nbsp; </p>
        <p align="center"> 
        <strong><span>
        <h4 align="center" class="no_travail" >Aucun travail n'est programm&eacute; pour les 
        prochains jours.<br />
        <br />
        Mais il y a toujours quelque chose &agrave; faire...</h4>
        <span></strong>
        </p></td>
        <td  align="left" class="lire_cellule_2" >
                
                <div style="float:left; display:inline;">
                <form action="planning.php?classe_ID=<?php echo intval($_GET['classe_ID']);?>" method="post">
        <input type="submit" name="Submit_p2" value="Planning travail &agrave; faire" />
        </form>
        </div>
                
                <div style="float:right; display:inline;">
        <form action="edt_eleve.php" method="post">
        <input type="submit" name="Submit_e" value="Emploi du temps de la semaine" />
        </form>
        </div>

<br />
<div style="float:right; display:inline;">
          <form name="frm" action="consulter.php?classe_ID=<?php echo intval($_GET['classe_ID']);?>&amp;tri=date#travauxafaire" method="post">
        <script>
        $(function() {
        		$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
			$.datepicker.setDefaults($.datepicker.regional['fr']);
        		$('#date_anciens_travaux').datepicker({firstDay:1});
        });
        </script>
        <?php $date_debut_historique=date("d/m/Y", mktime(0, 0, 0, date("m"), date("d")-15,  date("Y")));?>
Historique du travail &agrave; faire depuis le &nbsp;
<input name='date_anciens_travaux' type='text' id='date_anciens_travaux' value="<?php if (!isset($_POST['date_anciens_travaux'])){echo $date_debut_historique;}else{echo $_POST['date_anciens_travaux'];};?>" size="10" />
        <input type="submit" name="Submit_6" value="Afficher" />
        </form>
</div>        </td>
        <td class="lire_cellule_2"></td>
        <td  align="left" class="lire_cellule_2" >
                
                <p align="right">
        <?php if (dirname($_SERVER['PHP_SELF'])=='/'){
                $link_xml= 'http://'.$_SERVER['SERVER_NAME'].'/rss/classe_';
        } 
        else {
                $link_xml= 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/rss/classe_';
        };?>
        <a href="<?php echo $link_xml.$_GET['classe_ID'].'.xml' ;?>"><img src="images/rss.png" title="S'abonner &agrave; ce flux rss" alt="S'abonner &agrave; ce flux rss" border="0" /></a>&nbsp;&nbsp;&nbsp;          </p>
                <br />
                <p align="right">
        <!--Icone facebook  -->
        <?php
        if ($facebook_icon=='Oui'){?>
                <a onclick="return fbs_click()" href="http://www.facebook.com/share.php?u=<?php echo $link_xml.$_GET['classe_ID'].'.xml' ;?>&amp;tri=date" target="_blank"> <img src="./images/facebook.png" alt="Facebook" border="0" /></a>&nbsp;&nbsp;&nbsp;
        <?php }; ?>
        <!--Fin facebook -->
        </p></td>
        </tr>
        </table>
      <?php
} else {?>
        <table width="98%" border="0" align="center" cellpadding="0" cellspacing="0" class="lire_bordure">
        <tr valign="middle" class="lire_cellule_4">
        <td colspan="3" > TRAVAIL A FAIRE en <?php echo $row_RsClasse['nom_classe']. '  -  '.$groupe_select;?> </td>
        <td colspan="2"  align="left" valign="middle" ><?php 
        $today=date("d-m-Y");echo 'Nous sommes le '.jour_semaine($today).' '.$today;
        echo ' -  Semaine '.$_SESSION['semdate_libelle']; 
        ?></td>
        <td  align="left" valign="bottom" ><div align="right"><a href="<?php  
                if ((isset($_SESSION['droits']))&&($_SESSION['droits']==2)){echo 'enseignant/enseignant.php';} 
                else if((isset($_SESSION['droits']))&&($_SESSION['droits']==3)){echo 'vie_scolaire/vie_scolaire.php';}
                else if ((isset($_SESSION['droits']))&&($_SESSION['droits']==6)){echo 'assistant_education/assistant_educ.php';}                
                else if (isset($_SESSION['url_deconnecte_eleve'])){echo $_SESSION['url_deconnecte_eleve'];}
        else {echo 'index.php';};?>">
                
                
                <img src="images/home-menu.gif" alt="Accueil" width="26" height="20" border="0" /></a></div></td>
        </tr>
        <tr valign="middle" >
        <td width="2%" class="lire_cellule_2" ></td>
        <td width="31%" class="lire_cellule_2" ><form action="consulter.php?classe_ID=<?php echo intval($_GET['classe_ID']);?>&amp;tri=<?php 
        if (isset($tri)) {echo $tri; } else {echo 'date';};	?>" method="post">
        <div align="center">
        <select name="groupe" size="1" id="groupe">
        <?php do {?>
                <option value="<?php echo $row_Rsgroupe['groupe']?>"<?php if ((isset($_POST['groupe'])) AND ($_POST['groupe']==$row_Rsgroupe['groupe'] )) {echo 'selected';} else {if (!(isset($_POST['groupe'])) AND ($row_Rsgroupe['groupe']=='Classe entière')) {echo 'selected';};};?>><?php echo $row_Rsgroupe['groupe']?></option>
                <?php
        } while ($row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe));
        $rows = mysqli_num_rows($Rsgroupe);
        if($rows > 0) {
        	mysqli_data_seek($Rsgroupe, 0);
        	$row_Rsgroupe = mysqli_fetch_assoc($Rsgroupe);
        }
        ?>
        </select>
        <input type="submit" name="Submit3" value="S&eacute;lectionner" />
        </div>
        </form></td>
        <td  align="left" valign="middle" class="lire_cellule_2" >
                <form action="consulter.php?classe_ID=<?php echo intval($_GET['classe_ID']);?>&amp;tri=matiere#travauxafaire" method="post">
        <input type="submit" name="Submit_1" value="Classer par mati&egrave;res" />
        <input type="hidden" name="groupe2" value="<?php echo $groupe_select;?>" />
        </form>
        
        <form action="consulter.php?classe_ID=<?php echo intval($_GET['classe_ID']);?>&amp;tri=date#travauxafaire" method="post">
        <input type="submit" name="Submit_2" value="Classer par date" />
        <input type="hidden" name="groupe2" value="<?php  echo $groupe_select;?>" />
        </form></td>
        <td  align="left" class="lire_cellule_2" >
                
                <div style="float:left; display:inline;">
                <form action="planning.php?classe_ID=<?php echo intval($_GET['classe_ID']);?>" method="post">
        <input type="submit" name="Submit_p2" value="Planning travail &agrave; faire" />
        </form>
        </div>
                
                <div style="float:right; display:inline;">
        <form action="edt_eleve.php" method="post">
        <input type="submit" name="Submit_e" value="Emploi du temps de la semaine" />
        </form>
        </div>

<br />
<div style="float:right; display:inline;">
          <form name="frm" action="consulter.php?classe_ID=<?php echo intval($_GET['classe_ID']);?>&amp;tri=date#travauxafaire" method="post">
        <script>
        $(function() {
        		$.datepicker.regional['fr'] = { dateFormat: 'dd-mm-yy'};
			$.datepicker.setDefaults($.datepicker.regional['fr']);
        		$('#date_anciens_travaux').datepicker({firstDay:1});
        });
        </script>
        <?php $date_debut_historique=date("d/m/Y", mktime(0, 0, 0, date("m"), date("d")-15,  date("Y")));?>
Historique du travail &agrave; faire depuis le &nbsp;
<input name='date_anciens_travaux' type='text' id='date_anciens_travaux' value="<?php if (!isset($_POST['date_anciens_travaux'])){echo $date_debut_historique;}else{echo $_POST['date_anciens_travaux'];};?>" size="10" />
        <input type="submit" name="Submit_6" value="Afficher" />
        </form>
</div>
        </td>
        <td class="lire_cellule_2"></td>
        <td  align="left" class="lire_cellule_2" >
                
                <p align="right">
        <?php if (dirname($_SERVER['PHP_SELF'])=='/'){
                $link_xml= 'http://'.$_SERVER['SERVER_NAME'].'/rss/classe_';
        } 
        else {
                $link_xml= 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/rss/classe_';
        };?>
        <a href="<?php echo $link_xml.$_GET['classe_ID'].'.xml' ;?>"><img src="images/rss.png" title="S'abonner &agrave; ce flux rss" alt="S'abonner &agrave; ce flux rss" border="0" /></a>&nbsp;&nbsp;&nbsp;
                </p>
                <br />
                <p align="right">
        <!--Icone facebook  -->
        <?php
        if ($facebook_icon=='Oui'){?>
                <a onclick="return fbs_click()" href="http://www.facebook.com/share.php?u=<?php echo $link_xml.$_GET['classe_ID'].'.xml' ;?>&amp;tri=date" target="_blank"> <img src="http://b.static.ak.fbcdn.net/images/share/facebook_share_icon.gif?8:26981" alt="facebook" border="0" /></a>&nbsp;&nbsp;&nbsp;
        <?php }; ?>
        <!--Fin facebook -->
        </p></td>
        </tr>
        </table>

        <?php if ((isset($tri) AND ($tri<>'matiere')) OR (!isset($tri))  ){ ?>
                <table  width="98%" border="0"  align="center" cellpadding="0" cellspacing="0" class="lire_bordure"  >
                <tr class="lire_cellule_4">
                <td  width="15%"><div align="center">Mati&egrave;re</div></td>
                <td>&nbsp;</td>
        	<td width="11%" ><div align="left">Groupe</div></td>
        	<td width="13%" ><div align="left">Pour le </div></td>
        	<td width="31%" ><div align="center">Faire / Revoir </div></td>
        	<td width="14%" ><div align="center">Documents</div></td>
        	<td >Temps</td>
        	<td width="16%" ><div align="center">Professeur</div></td>
        	<td width="16%" ><div align="center">@</div></td>
        	</tr>
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
			$row_RsFichiers = mysqli_fetch_assoc($RsFichiers);
			$totalRows_RsFichiers = mysqli_num_rows($RsFichiers);
			
                        $codedatevar=substr($row_RsAfaire['t_code_date'],6,4).substr($row_RsAfaire['t_code_date'],3,2).substr($row_RsAfaire['t_code_date'],0,2);
                        
                        $visu='Oui';
                        //Si le cours a lieu a la date du jour, on affiche uniquement si l'heure horloge est superieure ou egale a heure de debut de cours
                        if (($row_RsAfaire['t_code_date']!='') &&(substr($row_RsAfaire['code_date'],0,8) == $codedatejour)){
                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                $query_heure_debut =sprintf("SELECT heure_debut FROM cdt_agenda WHERE ID_agenda=%u",$row_RsAfaire['agenda_ID']);
                                $Rs_h= mysqli_query($conn_cahier_de_texte, $query_heure_debut) or die(mysqli_error($conn_cahier_de_texte));
                                $row_Rs_h = mysqli_fetch_assoc($Rs_h);
                                $heure_actuelle=date('Hi',time());
				$heure_seance=substr($row_Rs_h['heure_debut'],0,2).substr($row_Rs_h['heure_debut'],3,2) ;
                                if ($heure_seance>$heure_actuelle){$visu='Non';};
                        };
                        
                        if (($row_RsAfaire['t_code_date']!='') && ( $codedatevar >= $codedatejour)&&($visu=='Oui')){
                                ?>

                                <tr  id="lignetaf_<?php echo $row_RsAfaire['ID_travail'];?>" bgcolor="<?php if (isset($_COOKIE['taf_'.$row_RsAfaire['ID_travail']])&&($_COOKIE['taf_'.$row_RsAfaire['ID_travail']]==1)) { echo $coul_taf_fait;} else {echo $coul_taf_nonfait;};?>">
                                
                                <td width="15%" align="left"  class="lire_cellule_1">


                                <input name="taf_<?php echo $row_RsAfaire['ID_travail'];?>" type="checkbox" id="taf_<?php echo $row_RsAfaire['ID_travail'];?>" onchange="set_check('taf_<?php echo $row_RsAfaire['ID_travail'];?>');"; <?php
                                if (isset($_COOKIE['taf_'.$row_RsAfaire['ID_travail']])&&($_COOKIE['taf_'.$row_RsAfaire['ID_travail']]==1)) { echo 'checked';};?>
                                >

                                
                                  <?php echo '<b>'.$row_RsAfaire['nom_matiere'].'</b>'; $nom=$row_RsAfaire['nom_matiere'];
                                if ($row_RsAfaire['gic_ID']>0){
                                        
                                        //regroupement / retrouver le nom
                                        $query_Rsgic = sprintf("SELECT nom_gic,commentaire_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u",$row_RsAfaire['gic_ID']);
                                        $Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
                                        $row_Rsgic = mysqli_fetch_assoc($Rsgic);echo '<br />(R) '.$row_Rsgic['nom_gic'];
                                };
                                                                echo '<br /><span class="date_message"">  -  '.$row_RsAfaire['jour_pointe'].'</span>';
                                ?></td>
                                <td width="5%" align="left"  class="lire_cellule_1"><?php if ((((substr($row_RsAfaire['code_date'],8,1)==0)&&(substr($row_RsAfaire['t_code_date'],2,1)=='/'))||($row_RsAfaire['eval']=='O'))&&(isset($_SESSION['libelle_devoir']))){echo '<span style="color:#FF0000"><b>'.$_SESSION['libelle_devoir'].'</b></span>';};?></td>
                                <td width="11%" align="left"  class="lire_cellule_1"><?php echo $row_RsAfaire['t_groupe']; ?></td>
                                <td width="13%" align="left" class="lire_cellule_1"><?php 
                                
                                echo '<b>'.jour_semaine($row_RsAfaire['t_code_date']).' '.substr($row_RsAfaire['t_code_date'],0,2).'-'.substr($row_RsAfaire['t_code_date'],3,2).'-'.substr($row_RsAfaire['t_code_date'],6,4).'</b>';


                                ?>                              </td>
                                <td align="left" class="lire_cellule_1"><?php echo $row_RsAfaire['travail']; ?></td>
                                <td width="14%" align="left"  class="lire_cellule_1"><?php 
                                //affichage des fichiers joints
                                if ($totalRows_RsFichiers<>0)
                                {
					do {
					//ne pas afficher les fichiers de cours si cdt desactive (non publie)
						if ((($row_RsFichiers['type']=='Cours')&&($row_RsAfaire['publier_cdt']=='O'))||($row_RsFichiers['type']<>'Cours')){
						$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_RsFichiers['nom_fichier']); ?>
						<?php 

						echo $row_RsFichiers['type'].' ';?><a href="fichiers_joints/<?php echo $row_RsFichiers['nom_fichier'];  ?>" target="_blank"> <?php echo $nom_f;  ?></a><br />
						<br />
						<?php
						};
                     } while ($row_RsFichiers = mysqli_fetch_assoc($RsFichiers)); 
                     mysqli_free_result($RsFichiers);
                                        
                                }?>                                </td>
                                <td align="left" class="lire_cellule_1" style="white-space:nowrap" ><?php echo $row_RsAfaire['charge']; ?></td>
                                <td width="16%" align="left" class="lire_cellule_1"><?php 
                                
                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                $query_RsPartageOuNon=sprintf("SELECT partage,emploi_ID FROM cdt_agenda WHERE ID_agenda=%u",$row_RsAfaire['agenda_ID']);
                                $RsPartageOuNon = mysqli_query($conn_cahier_de_texte, $query_RsPartageOuNon) or die(mysqli_error($conn_cahier_de_texte));
                                $row_RsPartageOuNon = mysqli_fetch_assoc($RsPartageOuNon);
                                if ($row_RsPartageOuNon['partage']=='O') {
                                        echo "<i>(Heure partag&eacute;e)</i><ul><li>";
                                        echo $row_RsAfaire['identite']==''?$row_RsAfaire['nom_prof']:$row_RsAfaire['identite'];
                                        echo "</li>";
                                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                        $query_RsProfPartage=sprintf("SELECT nom_prof,identite FROM cdt_emploi_du_temps_partage,cdt_prof WHERE cdt_emploi_du_temps_partage.ID_emploi=%u AND cdt_emploi_du_temps_partage.profpartage_ID=cdt_prof.ID_prof",$row_RsPartageOuNon['emploi_ID']);
                                        $RsProfPartage = mysqli_query($conn_cahier_de_texte, $query_RsProfPartage) or die(mysqli_error($conn_cahier_de_texte));    
                                        while ($row_RsProfPartage = mysqli_fetch_assoc($RsProfPartage)) {
                                                echo "<li>";
                                                echo $row_RsProfPartage['identite']==''?$row_RsProfPartage['nom_prof']:$row_RsProfPartage['identite'];
                                                echo "</li>";
                                        };
                                        echo "</ul>";
                                        mysqli_free_result($RsProfPartage);
                                        
                                } else {
                                        echo $row_RsAfaire['identite']==''?$row_RsAfaire['nom_prof']:$row_RsAfaire['identite']; 
                                }
                                mysqli_free_result($RsPartageOuNon);     
                                
                                ?></td>
                                <td width="16%" align="left" class="lire_cellule_1"><?php 
                                if (($row_RsAfaire['email']<>'')&&($row_RsAfaire['email_diffus_restreint']=='N')){ ?>
                                        <a href="mailto:<?php echo $row_RsAfaire['email'];?>"><img src="images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant &nbsp; <?php echo $row_RsAfaire['email'];?>" title="Contacter l'enseignant &nbsp; <?php echo $row_RsAfaire['email'];?>"/></a>
                                <?php };?></td>
				</tr>
				<?php
			}
			
		} while ($row_RsAfaire = mysqli_fetch_assoc($RsAfaire)); ?>
	  </table>
		<?php
        }
        else
        {?>
                <table  width="98%" border="0" align="center" cellpadding="0" cellspacing="0" class ="lire_bordure">
                <?php 
                $nom='';
                $cd=date('Ymd').'9';    
                ?>
                                                <tr class="lire_cellule_4" >
                                <td width="15%" class="lire_cellule_4" ><div align="center">Mati&egrave;re</div></td>
                                <td class="lire_cellule_4" >&nbsp;</td>
				<td width="11%" class="lire_cellule_4" ><div align="left">Groupe</div></td>
				<td width="13%" class="lire_cellule_4" ><div align="left">Pour le </div></td>
				<td width="31%" class="lire_cellule_4" ><div align="center">Faire / Revoir </div></td>
				<td width="14%" class="lire_cellule_4"><div align="center">Documents</div></td>
				<td class="lire_cellule_4">Temps</td>
                                <td width="16%" class="lire_cellule_4">Professeur</td>
                                <td width="16%" class="lire_cellule_4"><div align="center">@</div></td>
        </tr>
<?php           
                do { 
                        $codedatevar=substr($row_RsAfaire['t_code_date'],6,4).substr($row_RsAfaire['t_code_date'],3,2).substr($row_RsAfaire['t_code_date'],0,2);
                        ?>
                        <?php if (($nom<>$row_RsAfaire['nom_matiere']) && ( $codedatevar >= $codedatejour)){?>


                        <?php   ;};
                        // recherche fichiers joints eventuels
                        
                        if (isset($row_RsAfaire['agenda_ID'])) {
                                $refagenda_RsFichiers = (get_magic_quotes_gpc()) ? $row_RsAfaire['agenda_ID'] : addslashes($row_RsAfaire['agenda_ID']);
                        }
                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                        $query_RsFichiers = sprintf("SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=%u AND ind_position=%u ORDER BY type", $refagenda_RsFichiers,$row_RsAfaire['ind_position']);
                        $RsFichiers = mysqli_query($conn_cahier_de_texte, $query_RsFichiers) or die(mysqli_error($conn_cahier_de_texte));
                        $row_RsFichiers = mysqli_fetch_assoc($RsFichiers);
                        $totalRows_RsFichiers = mysqli_num_rows($RsFichiers);
                        
                        $visu='Oui';
                        //Si le cours a lieu a la date du jour, on affiche uniquement si l'heure horloge est superieure ou egale a heure de debut de cours
                        if (($row_RsAfaire['t_code_date']!='') &&(substr($row_RsAfaire['code_date'],0,8) == $codedatejour)){
                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                $query_heure_debut =sprintf("SELECT heure_debut FROM cdt_agenda where ID_agenda=%u",$row_RsAfaire['agenda_ID']);
                                $Rs_h= mysqli_query($conn_cahier_de_texte, $query_heure_debut) or die(mysqli_error($conn_cahier_de_texte));
                                $row_Rs_h = mysqli_fetch_assoc($Rs_h);
                                $heure_actuelle=date('Hi',time());
				$heure_seance=substr($row_Rs_h['heure_debut'],0,2).substr($row_Rs_h['heure_debut'],3,2) ;
				if ($heure_seance>$heure_actuelle){$visu='Non';};
			};
			
                        
                        if (($row_RsAfaire['t_code_date']!='') && ( $codedatevar >= $codedatejour)&&($visu=='Oui')){
                                ?>
                                <tr  id="lignetaf_<?php echo $row_RsAfaire['ID_travail'];?>" bgcolor="<?php if (isset($_COOKIE['taf_'.$row_RsAfaire['ID_travail']])&&($_COOKIE['taf_'.$row_RsAfaire['ID_travail']]==1)) { echo $coul_taf_fait;} else {echo $coul_taf_nonfait;};?>">
                                
                                <td width="15%" align="left"  class="lire_cellule_1">
                                
                                
                                <input name="taf_<?php echo $row_RsAfaire['ID_travail'];?>" type="checkbox" id="taf_<?php echo $row_RsAfaire['ID_travail'];?>" onchange="set_check('taf_<?php echo $row_RsAfaire['ID_travail'];?>');"; <?php
                                if (isset($_COOKIE['taf_'.$row_RsAfaire['ID_travail']])&&($_COOKIE['taf_'.$row_RsAfaire['ID_travail']]==1)) { echo 'checked';};?>
                                >
                                
                                <?php echo '<b>'.$row_RsAfaire['nom_matiere'].'</b>'; $nom=$row_RsAfaire['nom_matiere']; 
                                if ($row_RsAfaire['gic_ID']>0){
                                        
                                        //regroupement / retrouver le nom
                                        $query_Rsgic = sprintf("SELECT nom_gic,commentaire_gic FROM cdt_groupe_interclasses WHERE ID_gic=%u",$row_RsAfaire['gic_ID']);
                                        $Rsgic = mysqli_query($conn_cahier_de_texte, $query_Rsgic) or die(mysqli_error($conn_cahier_de_texte));
                                        $row_Rsgic = mysqli_fetch_assoc($Rsgic);echo '<br />(R) '.$row_Rsgic['nom_gic'];
                                };
                                                                echo '<br /><span class="date_message"">  -  '.$row_RsAfaire['jour_pointe'].'</span>';
                                ?>
                                  </td>
                                <td width="5%" align="left"  class="lire_cellule_1"><?php if (((substr($row_RsAfaire['code_date'],8,1)==0)&&(substr($row_RsAfaire['t_code_date'],2,1)=='/'))||($row_RsAfaire['eval']=='O')){
                                                                echo '<span style="color:#FF0000"><b>'.'- '.$row_RsAfaire['eval'];
                                                                if(isset($_SESSION['libelle_devoir'])){echo ' - '.$_SESSION['libelle_devoir'];} else {echo 'DEVOIR';};
                                                                echo '</b></span>';
                                                                };?>
                                  </td>
                                <td width="11%" align="left"  class="lire_cellule_1"><?php echo $row_RsAfaire['t_groupe']; ?> </td>
                                <td width="13%" align="left" class="lire_cellule_1"><?php 
                                echo '<b>'.jour_semaine($row_RsAfaire['t_code_date']).' '.substr($row_RsAfaire['t_code_date'],0,2).'-'.substr($row_RsAfaire['t_code_date'],3,2).'-'.substr($row_RsAfaire['t_code_date'],6,4).'</b>';
                                ?>
                                </td>
                                <td align="left"  class="lire_cellule_1"><?php echo $row_RsAfaire['travail']; ?></td>
                                <td width="14%" align="left" class="lire_cellule_1"><?php 
                                //affichage des fichiers joints
                                if ($totalRows_RsFichiers<>0)
                                {
					do { 
					//ne pas afficher les fichiers de cours si cdt desactive (non publie)
						if ((($row_RsFichiers['type']=='Cours')&&($row_RsAfaire['publier_cdt']=='O'))||($row_RsFichiers['type']<>'Cours')){
						$exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_RsFichiers['nom_fichier']); ?>
						<?php echo $row_RsFichiers['type'].' ';?><a href="fichiers_joints/<?php echo $row_RsFichiers['nom_fichier'];  ?>" target="_blank"> <?php echo $nom_f;  ?></a><br />
						<br />
						<?php
						};
					} while ($row_RsFichiers = mysqli_fetch_assoc($RsFichiers)); 
                                        mysqli_free_result($RsFichiers);
                                        
                                }?></td>
                                <td align="left" class="lire_cellule_1" style="white-space:nowrap"><?php echo $row_RsAfaire['charge']; ?></td>
                                <td width="16%" align="left" class="lire_cellule_1"><?php 
                                mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                $query_RsPartageOuNon=sprintf("SELECT partage,emploi_ID FROM cdt_agenda WHERE ID_agenda=%u",$row_RsAfaire['agenda_ID']);
                                $RsPartageOuNon = mysqli_query($conn_cahier_de_texte, $query_RsPartageOuNon) or die(mysqli_error($conn_cahier_de_texte));
                                $row_RsPartageOuNon = mysqli_fetch_assoc($RsPartageOuNon);
                                if ($row_RsPartageOuNon['partage']=='O') {
                                        echo "<i>(Heure partag&eacute;e)</i><ul><li>";
                                        //echo $row_RsAfaire['identite'];
                                        echo $row_RsAfaire['identite']==''?$row_RsAfaire['nom_prof']:$row_RsAfaire['identite'];
                                        echo "</li>";
                                        mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
                                        $query_RsProfPartage=sprintf("SELECT nom_prof,identite FROM cdt_emploi_du_temps_partage,cdt_prof WHERE cdt_emploi_du_temps_partage.ID_emploi=%u AND cdt_emploi_du_temps_partage.profpartage_ID=cdt_prof.ID_prof",$row_RsPartageOuNon['emploi_ID']);
                                        $RsProfPartage = mysqli_query($conn_cahier_de_texte, $query_RsProfPartage) or die(mysqli_error($conn_cahier_de_texte));    
                                        while ($row_RsProfPartage = mysqli_fetch_assoc($RsProfPartage)) {
                                                echo "<li>";
                                                echo $row_RsProfPartage['identite']==''?$row_RsProfPartage['nom_prof']:$row_RsProfPartage['identite'];
                                                echo "</li>";
                                        };
                                        echo "</ul>";
                                        mysqli_free_result($RsProfPartage);
                                        
                                } else {
                                        echo $row_RsAfaire['identite']==''?$row_RsAfaire['nom_prof']:$row_RsAfaire['identite']; 
                                }
                                mysqli_free_result($RsPartageOuNon);
                                ?></td>
                                <td width="16%" align="left" class="lire_cellule_1"><?php 
                                if (($row_RsAfaire['email']<>'')&&($row_RsAfaire['email_diffus_restreint']=='N')){ ?>
                                        <a href="mailto:<?php echo $row_RsAfaire['email'];?>"><img src="images/email.gif" width="16" height="14" border="0" alt="Contacter l'enseignant &nbsp; <?php echo $row_RsAfaire['email'];?>" title="Contacter l'enseignant &nbsp; <?php echo $row_RsAfaire['email'];?>"/></a>
                                <?php };?></td>
        </tr>
                                <?php
                        }
                } while ($row_RsAfaire = mysqli_fetch_assoc($RsAfaire)); ?>
      </table>
                <?php
        }
}  


?>
<p>&nbsp;</p>
<table width="98%" border="0" align="center" cellpadding="0" cellspacing="0" style="border-collapse:collapse">
  <tr>
    <td width="15%" class="lire_cellule_1"><a href="https://play.google.com/store/apps/details?id=com.lemaitre.cdtchocolat2&amp;feature=search_result#?t=W251bGwsMSwxLDEsImNvbS5sZW1haXRyZS5jZHRjaG9jb2xhdDIiXQ.."><img src="images/google-play.png" width="300" height="150" border="0" /></a></td>
    <td class="lire_cellule_1"><blockquote>
      <p class="Style13"><br />
        Votre application de cahier de textes sur votre smartphone</p>
      <p>T&eacute;l&eacute;chargez l'application Cahier de textes - Pierre Lemaitre sur <a href="https://play.google.com/store/apps/details?id=com.lemaitre.cdtchocolat2&feature=search_result#?t=W251bGwsMSwxLDEsImNvbS5sZW1haXRyZS5jZHRjaG9jb2xhdDIiXQ..">Google Play Store</a>  </p>
      <p>Enregistrez sur votre smartphone l'adresse de votre cahier de textes, la classe et le mot de passe. </p>
      <p>Vous acc&eacute;derez alors directement au Travail &agrave; faire par un simple clic sur le logo de l'application. </p>
    </blockquote></td>
  </tr>
</table> </tr>
</table>
<p>&nbsp;</p>

<p class="date_message">&nbsp;</p>
<p class="date_message"><strong>Note aux enseignants</strong> : Les travaux programm&eacute;s lors d'une s&eacute;ance seront affich&eacute;s ci-dessus d&egrave;s que la date de cette s&eacute;ance sera &eacute;chue. </p>
 <p>&nbsp; </p>
    <p class="date_message"><strong><a href="contribution.php" class="auteur">&copy; Application d&eacute;velopp&eacute;e par Pierre Lemaitre - Saint-L&ocirc; (France)</a></strong></p>
<?php
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
