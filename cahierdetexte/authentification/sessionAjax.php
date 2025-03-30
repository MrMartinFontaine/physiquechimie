<?php
session_start();
if (isset($_POST['verifSession']) && $_POST['verifSession'] == "active") {
	if(!isset($_SESSION['last_access']) 
	|| !isset($_SESSION['ipaddr']) 
	|| !isset($_SESSION['nom_prof']) 
	|| time()-$_SESSION['last_access'] > $_SESSION['session_timeout'] 
	|| $_SERVER['REMOTE_ADDR']!=$_SESSION['ipaddr'])
		echo "SESSION EXPIREE" ;
	else
	  echo $_SESSION['last_access']+$_SESSION['session_timeout']-time();//Envoi du temps restant calculé côté serveur.
}
?>