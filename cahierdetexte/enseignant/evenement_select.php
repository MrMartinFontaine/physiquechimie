<?php
// listes manuelles  et  routines pour evenements


//plages de choix des modalites et themes ( attention, en premier : le choix par-defaut), indispensable pour delimiter sur place/hors etab
$mode=array('?','Sans intervenant ','Avec intervenant','Avec invit&eacute;','Rencontre','Examen','Sortie','Stage ','Visite', 'Voyage');
$mode_resume=array('','&nbsp; &nbsp; &nbsp; &nbsp;','Intervenant ','Invit&eacute;','Rencontre','Examen','Sortie','Stage ','Visite', 'Voyage');
$surplace=5; //delimite les n premiers de la liste mode ci-dessus  " evenement au sein de l'ecole  " pour affichage debut/depart  ou  fin/retour
$theme= array('','Accompagnement personnalis&eacute;','Arts & culture','Cin&eacute;ma',' Citoyennet&eacute;','Dev. durable','Multidiscipl.','Musique','Orientation','Sant&eacute;&nbsp; &nbsp; ','Sciences','Sports &nbsp;','Th&eacute;&acirc;tre');



// plage de choix et ordre dans les selections d'heure
$choix_heures =array ('07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','00','01','02','03','04','05','06' ) ; $nb_heures = 24 ; 
$choix_min =array ('00','05','10','15','20','25','30','35','40','45','50','55') ; $nb_min = 12 ;





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


function url_actuelle()
{
     return "http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
}

function url_cdt()
{
$url_cdt = (url_actuelle());
$tab=explode("enseignant",$url_cdt);
return $tab[0];
}
?>