<?php
// routines et listes manuelles  
// pour evenements

//$datetoday=date('y-m-d');
//$mois_liste='00';

/*
function tronque($texte,$max) {  // troncation avec nettoyage  <p> </p> <br> <br/> <br />et car /n pour mise en 1ligne
 global $end ;
 if (strlen($texte) >= $max)	
		{ $chaine = substr($texte, 0 , $max) ;
		$nb_car_sup = 1*substr_count($texte, '<br>')+2*substr_count($texte, '<br/>')+3*substr_count($texte, '<br />')+0*substr_count($texte, '<p>')+1*substr_count($texte, '</p>')+0*substr_count($texte, '<b>')+4*substr_count($texte, '<strong>') ; // nb de caracteres qui vont être elimines par sup de ces balises
		$chaine = substr($texte, 0 , $max + $nb_car_sup);
		$end=strrpos($chaine, " ");
		$texte = substr($chaine,0,$end).' ';
		}
		else {$end=$max;};	// sinon repete le message....
// nettoyage balises		
 $texte=str_replace(array('<br>','<br/>','<br />','<p>','</p>','<i>','<b>','<strong>',),array(' - ',' - ',' - ',' - ',' - ',' ',' ',' ',),rtrim($texte) ) 	;
 // $end renvoie le rep de la suite
return $texte;
			}
*/  

function noAccents($texte) {  // nettoyage pour envoi par mail- crucial pour destinataire et subject ! -
 $texte=str_replace(chr(92),'',$texte);
 $texte = str_replace(
 array('à','â','ä','á','ã','å','î','ï','ì','í','ô','ö','ò','ó','õ','ø','ù','û','ü','ú','é','è','ê','ë','ç','ÿ','ñ',),
 array('a','a','a','a','a','a','i','i','i','i','o','o','o','o','o','o','u','u','u','u','e','e','e','e','c','y','n',),
 $texte  );
 return $texte;        
}


function nom_depuis_login($login) 
{// pour afficher un nom  en capitale depuis un login p-name
if ( (strlen($login)>1 )&& (strpos($login, '. ')== false ))   // on decoupe le login du type  [initiale prenom + nom]
  { $login= substr($login,0,1).'. '.substr( $login,1) ;} ;
$login=  strtoupper ($login) ;

return $login ;
}

function prem_maj($titre) // titre commence par capitale 
{
$titre=strtoupper(substr($titre,0,1)).substr($titre,1);
return $titre ;
}

function ymd_dmy ($date) // convertit yyyy-mm-dd ou yyyy/mm/dd en dd-mm-yyyy
{
$date= substr($date,8,2).'-'.substr($date,5,2).'-'.substr($date,0,4);
return $date ;
}


function jour3_ymd($date) // retourne le N° du jour  en 3 car d'une date en yyyy-mm-dd dans la base
{
$nom_jour = array(0 => "Dim ", "Lun ", "Mar ","Mer ", "Jeu ","Ven ","Sam ");
$date = explode ("-",$date);
$lejour = $nom_jour[date('w',mktime(0,0,0,$date[1],$date[2],$date[0])) ];
return $lejour ;
}
function jour_dmy($date) // retourne le N° du jour d'une date en dd/mm/yyyy dans le formulaire
{
$nom_jour = array(0 => "Dimanche ", "Lundi ", "Mardi ","Mercredi ", "Jeudi ","Vendredi ","Samedi ");
$date = explode ("/",$date);
$lejour = $nom_jour[date('w',mktime(0,0,0,$date[1],$date[0],$date[2]))];
return $lejour ;
}

function nom_mois($texte)
{
  $texte = str_replace(
 array('00','01','02','03','04','05','06','07','08','09','10','11','12',),
 array('?','Janvier','F&eacute;vrier','Mars','Avril','Mai','Juin','Juille','Aout','Septembre','Octobre','Novembre','D&eacute;cembre',),
 $texte  );
 return $texte;        
}




$http_site='' ; //url du site pour envoi de mel


$valideur= "";  // responsable validation ; si ce champ est vide,  la validation est faite directement par le rédacteur
$valideur_mail="" ;   // sinon envoie de validation à l'adresse spécifiee ici 
$liste_mail[1]= "pierre.lemaitre@laposte.net" ;//adresse mel
$liste_mail[2]= "" ;// liste non limitée des autres personnes informees
$liste_mail[3]= "";


// plage de choix et ordre dans les selections d'heure
$choix_heures =array ('07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','00','01','02','03','04','05','06' ) ; $nb_heures = 24 ; 
$choix_min =array ('00','05','10','15','20','25','30','35','40','45','50','55') ; $nb_min = 12 ;

//plages de choix des modalites et themes ( attention, en premier : le choix par-defaut), indispensable pour delimiter sur place/hors etab
$mode=array('?','Sans intervenant ','Avec intervenant','Avec invit&eacute;','Rencontre','Sortie','Stage ','Visite', 'Voyage','Concours');
$mode_resume=array('','&nbsp; &nbsp; &nbsp; &nbsp;','Intervenant ','Invit&eacute;','Rencontre','Sortie ','Stage ','Visite ', 'Voyage ','Concours');
$surplace=4; //delimite les modes "sur place " pour debut/depart  & fin/retour
$theme= array('','Arts & culture','Cin&eacute;ma',' Citoyennet&eacute;','Dev. durable','Multidiscipl.','Musique','Orientation','Sant&eacute;&nbsp; &nbsp; ','Sciences','Sports &nbsp;','Th&eacute;&acirc;tre','Unss &nbsp; &nbsp;');

// variables a adapter  a l'annee , en choissisant l'ordre d'affichage (pas forcement alphabetique)dans les boites d'affichage 
//$fin_annee='07/07/2014';
//$choix_classe=array('LYCEE ET COLLEGE',' COLLEGE','SIXIEMES','6 Colette','6 La Fontaine ','6 Molière ','6 Pagnol ','CINQUIEMES ','5 Cousteau','5 Galilée ','5 Lavoisier','5 Pascal ','QUATRIEMES','4 Benoit Costil ','4 Hugues Duboscq','4 Jules Marie','4 Danny Rodrigues ','TROISIEMES','3 Thomas Howie','3 John Langan ','3 Walter Muller','3 Leonard Moone','LYCEE Techno','Seconde','PREMIERES','Première ST2S A','Première ST2S B','TERMINALES','Terminale ST2S A','Terminale ST2S B','LYCEE Pro ','CAP Petite enfance','SECONDES Pro','2 Pro D','2 Pro S ','PREMIERES Pro','1 Pro D ','1 Pro S','TERMINALES Pro','Ter Pro D','Ter Pro S ','POST BAC ','EDIAS ','TOURISME','1 BTS Tourisme ','2 BTS Tourisme ','SP3S ','BTS SP3S1 ','BTS SP3S2 ','Péri-éducatif 1 ','Péri-éducatif 2','Groupe défini');
//$nb_classes=sizeof($choix_classe) ;

?>
