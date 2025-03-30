<?php echo $_SERVER['SERVER_NAME'].' est server_name <br />';?><?php echo $_SERVER['REQUEST_URI'].' est request url<br />';?>
<?php echo $_SERVER['PHP_SELF'].' est php self <br />';?><?php echo $_SERVER['DOCUMENT_ROOT'].' est document root<br />';?>
<?php echo 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']);


$x=dirname($_SERVER['PHP_SELF']); echo $x;
$url_abs = 'http://'.$_SERVER['HTTP_HOST'].$_SESSION['root_fichier_perso'];
$dir     = $_SERVER['DOCUMENT_ROOT'].$_SESSION['path_fichier_perso'];
echo $url_abs;
echo $dir;
?>