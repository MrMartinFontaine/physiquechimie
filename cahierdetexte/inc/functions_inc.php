<?php

//protection injection SQL et XSL
function VerifChamps($valeur)
{
$verif = (get_magic_quotes_gpc()) ? htmlentities($valeur, ENT_QUOTES) :
addslashes($valeur);
return $verif;
}

// Pour la protection des pages contre l'injection SQL
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  //$theValue = (!get_magic_quotes_gpc()) ? addslashes($theValue) : $theValue;
  $theValue =  addslashes($theValue) ;

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}

// Pour la protection des pages contre l'injection CRLF
$protect = array(
    "<" => "&lt;",
    ">" => "&gt;",
    "&" => "&amp;",
    "\"" => "&quot;",
    "'" => "",
    "\n" => " ",
    "\t" => " ",
    "\r" => " ",
    "\0" => " ",
    "\x0A" => "",
    "\x0D" => "",
    " " => ""
);



function jour_semaine($dateX)
{
$joursX = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');
$jourX = substr($dateX,0,2);
$moisX = substr($dateX,3,2);
$anneeX = substr($dateX,6,4);
$tempsX = mktime(0, 0, 0, $moisX , $jourX, $anneeX);
$joursemaineX = date ('w',$tempsX);
return $joursX[$joursemaineX];
};

function my_addslashes($var)
{
	$tmp = str_replace("'","\'",$var);
	$final = str_replace('"','\"',$tmp);
	return $final;
}

function my_apostrophes($var)
{
	$final = str_replace("'","&rsquo;",$var);
	return $final;
};

function remplace_slash($var)
{
	$final = str_replace("/","_",$var);
	return $final;
};

function sans_accent($n)
{
   $n = strtolower($n);
   $n = strtr($n, 'ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ',
   'aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn');
   $n = preg_replace('’[^a-z0-9\._\-]’','', $n);
   $replace = array('’\.{2,}’', '’_{2,}’', '’\-{2,}’');
   $with = array('.', '_', '-');
   $n = preg_replace($replace, $with, $n);
   // limitation au  20 premiers caractères + extension
   if (strlen($n) > 25) {      
   //$n = substr($n, -25);   
   $extension=strstr($n,".");
   $n=substr($n,0,20).$extension;
   }
   
   // le nom ne peut pas comporter que des .-_ et commencer par un point
   $false_name = preg_replace('’[\-._]’', '', $n);
   if (empty($false_name) or ($n{0} == '.')) {
    $n = '';
   }
   return $n;
};

// pour mette en forme le login lors de l'importation SCONET des membres d'un établissement avec le fichier XML
// pour envole, fonction correspondant à la création des logins dans l'annuaire, donc à ne pas modifier au risque de ne pas retrouver les bons logins
function premier ($str) {
   // débuggage : exceptions à traiter
   $de=array("/'/","/^DE /","/^DU /","/^DELA /","/^DOS /","/^DI /","/^ES /","/^EL /","/^LE /","/^LA /","/^DA /","/^VAN /","/^BEN /");
   $vers=array(""   ,"DE"    ,"DU"    ,"DELA"    ,"DOS"    ,"DI"    ,"ES"    ,"EL"    ,"LE"    ,"LA"    ,"DA"    ,"VAN",    "BEN");
   $str=preg_replace($de,$vers,$str);
   $tab=preg_split ('/ |-/', $str,-1);
   return($tab[0]);
}

// Traitement des caractères spéciaux pour le login créé lors de l'importation SCONET
// (source : http://www.phpapps.org)
function prepare_login($string){
	// longueur MAX
	$maxlen=19;
	if (strlen($string)>$maxlen) {$string=substr($string,0,$maxlen);}
	$string=strtolower($string);  
	$Caracs = array(
		"¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A",
		"Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A", 
		"Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E", 
		"Ê" => "E", "Ë" => "E", "Ì" => "I", "Í" => "I", 
		"Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N", 
		"Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O", 	
		"Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U", 
		"Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
		"à" => "a", "á" => "a", "â" => "a", "ã" => "a",
		"ä" => "a", "å" => "a", "æ" => "a", "ç" => "c", 
		"è" => "e", "é" => "e", "ê" => "e", "ë" => "e", 
		"ì" => "i", "í" => "i", "î" => "i", "ï" => "i", 
		"ð" => "o", "ñ" => "n", "ò" => "o", "ó" => "o", 
		"ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o", 
		"ù" => "u", "ú" => "u", "û" => "u", "ü" => "u", 
		"ý" => "y", "ÿ" => "y", "~B"=> "e"); 
	return strtr($string, $Caracs);
}


//PROBLEME !!
//la fonction SHOW ne focntionne pas si le nom de la base possede un trait d'union !!!!

function mysqli_table_exists($table , $db) {
   $requete = 'SHOW TABLES FROM '.$db.' LIKE \''.$table.'\'';
    $existe = mysqli_query($conn_cahier_de_texte, $requete) or die(mysqli_error($conn_cahier_de_texte));
	$row_existe = mysqli_fetch_assoc($existe);
	$total_row_existe = mysqli_num_rows($existe);
return mysqli_num_rows($existe);
}


function visa_edition_possible($date_edition) {
  $date_du_visa=substr($_SESSION['date_visa'],0,4).substr($_SESSION['date_visa'],5,2).substr($_SESSION['date_visa'],8,2);
  if (($date_edition<$date_du_visa)&&($_SESSION['visa_stop_edition']=='Oui')){ return false;} else {return true;}
}  

//Insertion d'un espace dans les noms longs 
//Permet le retour à la ligne lors de l'affichage dans une cellule
//Equivalent au style word-wrap: break-word géré différemment selon les navigateurs
function Insert_espace($input,$longueur)
{
$input_length = strlen($input);
if ($input_length>$longueur) {
$output = '';
$letter_counter = 0;

for($i = 0; $i < $input_length; ++$i) 
{
    if($input[$i] == chr(32) )
    {
		$letter_counter = 0;
    }
    else 
    {
            if($letter_counter == $longueur)
            {
                $output .= chr(32);
                $letter_counter = 0;
            }
            ++$letter_counter;
    }
    $output .= $input[$i];
}
 
} 
else {$output=$input; 
};
return $output;
}


//Si le nom du fichier joint est déjà présent, on lui affecte un suffixe _(1)_   
// exemple 450_toto.txt devient 450_(1)_toto.txt
function renommer($nom_fichier)
{
     $filename = '../fichiers_joints/'.$nom_fichier;
     if (file_exists($filename)) {
         $extract =substr($filename,19);
         $pos = strpos($extract, '_');
         if (substr($extract,$pos+1,1)=='('){ 
             $init='/\('.substr($extract,$pos+2,1).'\)_/';
             $n=substr($extract,$pos+2,1)+1;
             $remplace='('.   $n   .')_';
             $nom_fichier_new = preg_replace($init,$remplace, $extract,1);
          } else {
         $nom_fichier_new=preg_replace('/_/','_(1)_', $extract,1);
         };
     
         return (renommer($nom_fichier_new));
     } else {
         return $nom_fichier;
     }
}

//redimensionnement du logo etablissement
function redimage($img_src,$dst_w,$dst_h) {
   // Lit les dimensions de l'image
   $size = GetImageSize($img_src);  
   $src_w = $size[0]; $src_h = $size[1];
   // Teste les dimensions tenant dans la zone
   $test_h = round(($dst_w / $src_w) * $src_h);
   $test_w = round(($dst_h / $src_h) * $src_w);
   // Si Height final non précisé (0)
   if(!$dst_h) $dst_h = $test_h;
   // Sinon si Width final non précisé (0)
   elseif(!$dst_w) $dst_w = $test_w;
   // Sinon teste quel redimensionnement tient dans la zone
   elseif($test_h>$dst_h) $dst_w = $test_w;
   else $dst_h = $test_h;

   // Affiche les dimensions optimales
   echo "WIDTH=".$dst_w." HEIGHT=".$dst_h;
}
?>