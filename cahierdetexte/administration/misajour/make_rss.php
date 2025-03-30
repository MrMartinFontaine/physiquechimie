<?php include "../../authentification/authcheck.php";
if ($_SESSION['droits']<>1) { header("Location: ../index.php");exit;};
require_once('../../Connections/conn_cahier_de_texte.php');
require_once('../../inc/functions_inc.php');

if ((isset($_POST["MM_update2"])) && ($_POST["MM_update2"] == "form2")) {

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
$query_RsClasse = "SELECT * FROM cdt_classe ORDER BY nom_classe ASC";
$RsClasse = mysqli_query($conn_cahier_de_texte, $query_RsClasse) or die(mysqli_error($conn_cahier_de_texte));
$row_RsClasse = mysqli_fetch_assoc($RsClasse);
$totalRows_RsClasse = mysqli_num_rows($RsClasse);

do {
//FLUX Travail à faire
// En-tête du flux RSS version 2.0

$xml = '<?xml version="1.0" encoding="ISO-8859-1"?><rss version="2.0" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">'."\n";
$xml .= '<channel>'."\n";


$xml .= '<title>Cahier de textes - '.$row_RsClasse['nom_classe'].' - Travail</title>'."\n";  // titre du flux
$xml .= '<link>'.$_SESSION['url_etab'].'</link>'."\n";     //lien vers le cahier de textes
$xml .= '<description>'.$_SESSION['nom_etab'].'</description>'."\n";
$xml .= '<language>fr-fr</language>'."\n";

// Ajout de la date actuelle de publication (suivant la DTD RSS)
$xml .= '<lastBuildDate>'.date("D, d M Y H:i:s").' GMT</lastBuildDate>'."\n";

// En-tête suite et fin   à adapter en fonction de votre  établissement
//$xml .= '<webMaster></webMaster>'."\n";   // email du webmestre
$xml .= '<ttl>60</ttl>'."\n";
$xml .= '<image>'."\n";
$xml .= "\t".'<title>Cahier de textes - '.$row_RsClasse['nom_classe'].' - Travail</title>'."\n";  // titre du flux
$xml .= "\t".'<url>'.$_SESSION['url_logo_etab'].'</url>'."\n";  // url du logo
$xml .= "\t".'<link>'.$_SESSION['url_etab'].'</link>'."\n"; // lien vers le cahier de textes
$xml .= '</image>'."\n";

// paramètres de connexion à la base de données

/*********************************************************
//On affiche uniquement les travaux programmés à une lors d'une seance antérieure ou egale à la date du jour
//sauf dans le cas d'un devoir programmé en dehors des heures de cours(dernier chiffre de code_date =0)

*********************************************************/
$cd=date('Ymd').'9'; 
$sql_restriction='AND ((cdt_travail.code_date <= '.$cd.') OR (SUBSTRING(cdt_travail.code_date,9,1)=0))';


$choix_RsAfaire=$row_RsClasse['ID_classe'];
$sql_publier="AND cdt_prof.publier_travail='O'";
$codedatejour=date("Ymd");
$Requete = sprintf("SELECT * FROM cdt_prof, cdt_travail, cdt_matiere WHERE cdt_travail.classe_ID=%u AND cdt_travail.matiere_ID=cdt_matiere.ID_matiere AND cdt_travail.prof_ID=cdt_prof.ID_prof  AND cdt_travail.t_jour_pointe >= %s %s %s ORDER BY cdt_matiere.nom_matiere, cdt_travail.t_jour_pointe",$choix_RsAfaire,  $codedatejour, $sql_publier, $sql_restriction);
	mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
	$Result = mysqli_query($conn_cahier_de_texte, $Requete);

// Creation des items : titre + guid +lien + description + date de publication
$idguid = 1;
while($val=mysqli_fetch_array($Result)) {


   // fichiers joints

   mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte);
   $query_RsFichiers = sprintf("SELECT * FROM cdt_fichiers_joints WHERE cdt_fichiers_joints.agenda_ID=%u ORDER BY type", $val["agenda_ID"]);
   $RsFichiers = mysqli_query($conn_cahier_de_texte, $query_RsFichiers) or die(mysqli_error($conn_cahier_de_texte));
   $row_RsFichiers = mysqli_fetch_assoc($RsFichiers);
   $totalRows_RsFichiers = mysqli_num_rows($RsFichiers);

    //fin fichiers joints
	$xml .= '<item>'."\n";
	$xml .= "\t".'<title>'.$val["nom_matiere"].' - '.$val["t_groupe"].'        ( '.$val["identite"].' )</title>'."\n";
	$xml .= "\t".'<guid isPermaLink="false">cdt_'.$idguid.'</guid>'."\n";
	$idguid = $idguid+1;
	$xml .= "\t".'<description><![CDATA[ Pour le '.$val["t_code_date"].	' : ';
	if ((substr($val['code_date'],8,1)==0)&&(substr($val['t_code_date'],2,1)=='-')){
	$xml .="DEVOIR ";};
	$xml .=$val["travail"];

	
	//affichage des fichiers joints
		if ($totalRows_RsFichiers<>0)
		{  $xml .= '<br /> Fichiers joints :<br />  ';
		   do {
		      $exp = "/^[0-9]+_/"; $nom_f = preg_replace($exp, '', $row_RsFichiers['nom_fichier']);
		      $url_f=$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']);
		      $oter='enseignant';
		      $url_f = preg_replace('`(^|\W)('.$oter.')(\W|$)`si','$1$3', $url_f);
                      $xml .= $row_RsFichiers['type'].' <a href="http://'.$url_f.'fichiers_joints/'.$row_RsFichiers['nom_fichier']. '">'.$nom_f.'</a> <br />';
			} 
                  while ($row_RsFichiers = mysqli_fetch_assoc($RsFichiers));
		  mysqli_free_result($RsFichiers);
		  
      		}

         $xml .="\t".']]></description>'."\n";
	 $xml .= '</item>'."\n";
}

// Déconnexion
//mysqli_close($conn_cahier_de_texte);

// Fin du flux
$xml .="</channel>\n</rss>\n";


// Ecriture du flux dans un fichier xml

$fich="../../rss/classe_".$choix_RsAfaire.".xml";
$fp = fopen($fich, 'w+');
      fputs($fp, $xml);
fclose($fp);
//chmod("../rss/classe_".$choix_RsAfaire.".xml", 0666);

} while ($row_RsClasse = mysqli_fetch_assoc($RsClasse));


 $updateGoTo = "../index.php";
 header(sprintf("Location: %s", $updateGoTo));
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Cahier de textes - <?php echo $_SESSION['identite']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK media=screen href="../../styles/style_default.css" type=text/css rel=stylesheet>
<LINK media=screen href="../../templates/default/header_footer.css" type=text/css rel=stylesheet>
</HEAD>
<BODY>
<DIV id=page>
<?php 
$header_description="Actualisation des flux RSS";
require_once "../../templates/default/header.php";
?>

  <p>&nbsp;</p>
  <p>Suite &agrave; un probl&egrave;me de mise &agrave; jour (incident serveur, suppression du dossier RSS...),</p>
  <p>il peut &ecirc;tre n&eacute;cessaire de r&eacute;actualiser les flux RSS.</p>
  <p>Le script recr&eacute;e ou actualise les fichiers classe_*.xml du dossier RSS.  </p>
  <p> </p>
  <form action="make_rss.php" method="post" name="form2" id="form2">
    <p>
      <input type="hidden" name="MM_update2" value="form2">
    </p>
    <p>&nbsp;</p>
    <p>
      <input name="submit2" type="submit"  value="Actualiser les flux RSS">
    </p>
  </form>
    <p align="center"><br>
    </p>
    <p align="center"><a href="../index.php">Retour au Menu Administrateur</a></p>
  <p>&nbsp; </p>
  <DIV id=footer></DIV>
</DIV>
</body>
</html>
