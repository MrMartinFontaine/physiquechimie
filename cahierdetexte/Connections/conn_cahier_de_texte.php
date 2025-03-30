<?PHP
 $hostname_conn_cahier_de_texte = 'localhostsql.free.fr';
 $database_conn_cahier_de_texte = 'www.chimiephysique';
 $username_conn_cahier_de_texte = 'www.chimiephysique';
 $password_conn_cahier_de_texte = 'tuppupv';
 $conn_cahier_de_texte = mysqli_connect($hostname_conn_cahier_de_texte, $username_conn_cahier_de_texte, $password_conn_cahier_de_texte) or die(mysqli_error());
//si probleme accent à l'affichage (points d'interrogation)decommenter la ligne ci-dessous
header('Content-Type: text/html; charset=ISO-8859-1');ini_set( 'default_charset', 'ISO-8859-1' ); 
//si probleme accent pour les données extraites de la base decommenter la ligne ci-dessous
mysqli_query($conn_cahier_de_texte, "SET NAMES latin1");
?>