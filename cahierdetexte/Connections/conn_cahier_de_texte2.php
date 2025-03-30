<?PHP
//paramétrage de l'accès à la base de données d'un ANCIEN Cahier de textes (Archive)
 $hostname_conn_cahier_de_texte2 = 'localhost';
 $database_conn_cahier_de_texte2 = 'base_archive';
 $username_conn_cahier_de_texte2 = 'root';
 $password_conn_cahier_de_texte2 = '';

$annee_scolaire='2007-2008';
$conn_cahier_de_texte2 = mysql_pconnect($hostname_conn_cahier_de_texte2, $username_conn_cahier_de_texte2, $password_conn_cahier_de_texte2) or die(mysql_error());

?>
