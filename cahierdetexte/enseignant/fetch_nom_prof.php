<?php
// include "../authentification/authcheck.php" ;
// ejecter les acces non identifies
// if (($_SESSION['droits']<>1)&& ($_SESSION['droits']<>2)&&($_SESSION['droits']<>3)&&($_SESSION['droits']<>4)&&($_SESSION['droits']<>8)){ header("Location: ../index.php");exit;};
require_once('../Connections/conn_cahier_de_texte.php');
// require_once('../inc/functions_inc.php');


function nom_propre($login) //non utilisee actuellement
{// pour afficher un nom  en capitale depuis un login p-name
if ( (strlen($login)>1 )&& (strpos($login, '. ')== false ))   // c'est sans doute un login, pas une saisie manuelle ni un nom formaté , et donc on decoupe le login
  { $login= substr($login,0,1).'. '.substr( $login,1) ;} ;
$login=  strtoupper ($login) ;

return $login ;
}

mysqli_select_db($conn_cahier_de_texte, $database_conn_cahier_de_texte) ;

// si on reçoit une donnée
if(isset($_GET['q'])) {
    $q = htmlentities($_GET['q']); // protection
     
		 
//	$keyword = $_POST['data'];
	$sql = "select nom_prof from cdt_prof where (droits > 1 ) and (droits < 4) and ( nom_prof  like '".$q."%'  )limit 0,20  " ;
	//$sql = "select name from ".$db_table."";
	$resultat = mysqli_query($conn_cahier_de_texte, $sql) or die(mysqli_error($conn_cahier_de_texte));
		$row = mysqli_fetch_assoc($resultat);
	// affichage des résultats  $row['nom_prof']
	 if( mysqli_num_rows($resultat))
	{	 do { echo strtoupper($row['nom_prof']) ."\n"; }
		while ( $row = mysqli_fetch_assoc($resultat) ) ;
			} ;
	// else	 {echo 'pas de suggestion' ;} ;
 } ; 

?>

	