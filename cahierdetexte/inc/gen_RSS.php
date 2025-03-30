<?php

//FLUX Travail a faire
// En-tete du flux RSS version 2.0

$xml = '<?xml version="1.0" encoding="ISO-8859-1"?><rss version="2.0" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">'."\n";
$xml .= '<channel>'."\n";


$xml .= '<title>Cahier de textes - '.$_GET['nom_classe'].' - Travail</title>'."\n";  // titre du flux
$xml .= '<link>';
        if (isset($_SESSION['url_etab'])){$xml .= $_SESSION['url_etab'];};// lien vers le cahier de textes
$xml .='</link>'."\n";
$xml .= '<description>';
        if (isset($_SESSION['nom_etab'])){$xml .= $_SESSION['nom_etab'];};
$xml .='</description>'."\n";
$xml .= '<language>fr-fr</language>'."\n";

// Ajout de la date actuelle de publication (suivant la DTD RSS)
$xml .= '<lastBuildDate>'.date("D, d M Y H:i:s").' GMT</lastBuildDate>'."\n";

// En-tete suite et fin a adapter en fonction de votre  etablissement
//$xml .= '<webMaster></webMaster>'."\n";   // email du webmestre
$xml .= '<ttl>60</ttl>'."\n";
$xml .= '<image>'."\n";
$xml .= "\t".'<title>Cahier de textes - '.$_GET['nom_classe'].' - Travail</title>'."\n";  // titre du flux
$xml .= "\t".'<url>';
        if (isset($_SESSION['url_logo_etab'])){$xml .= $_SESSION['url_logo_etab'];};// url du logo
$xml .='</url>'."\n";  
$xml .= "\t".'<link>';
        if (isset($_SESSION['url_etab'])){$xml .= $_SESSION['url_etab'];};// lien vers le cahier de textes
$xml .='</link>'."\n"; 
$xml .= '</image>'."\n";

// parametres de connexion a la base de donnees

/*********************************************************
//On affiche uniquement les travaux programmes a une lors d'une seance anterieure ou egale a la date du jour
//sauf dans le cas d'un devoir programme en dehors des heures de cours(dernier chiffre de code_date =0)

*********************************************************/
$cd=date('Ymd').'9'; 
$sql_restriction='AND ((cdt_travail.code_date <= '.$cd.') OR (SUBSTRING(cdt_travail.code_date,9,1)=0))';


$choix_RsAfaire=$_GET['classe_ID'];
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
   $query_RsFichiers = sprintf("SELECT * FROM cdt_fichiers_joints WHERE agenda_ID=%u ORDER BY type", $val["agenda_ID"]);
   $RsFichiers = mysqli_query($conn_cahier_de_texte, $query_RsFichiers) or die(mysqli_error($conn_cahier_de_texte));
   $row_RsFichiers = mysqli_fetch_assoc($RsFichiers);
   $totalRows_RsFichiers = mysqli_num_rows($RsFichiers);

    //fin fichiers joints
	$xml .= '<item>'."\n";
	$xml .= "\t".'<title>'.preg_replace('/&/','_',$val["nom_matiere"]).' - '.$val["t_groupe"].'        ( '.$val["identite"].' )</title>'."\n";
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

// Deconnexion
//mysqli_close($conn_cahier_de_texte);

// Fin du flux
$xml .="</channel>\n</rss>\n";


// Ecriture du flux dans un fichier xml
$fich="../rss/classe_".$choix_RsAfaire.".xml";
$fp = fopen($fich, 'w+');
      fputs($fp, $xml);
fclose($fp);

//chmod("../rss/classe_".$choix_RsAfaire.".xml", 0666);


?>
