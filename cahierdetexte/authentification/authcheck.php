<?php
session_start();
if ((isset($_SESSION['mobile_browser']))&&($_SESSION['mobile_browser']==false)){ //ne pas traiter avec les mobiles
if(!isset($_SESSION['last_access']) || !isset($_SESSION['ipaddr']) || !isset($_SESSION['nom_prof']))
{
  header("Location: ../index.php");
  die();
}
// le $session_timeout est desormais fixe dans les parametres generaux
// voir menu administrateur
// mais si pas defini avant mise a jour alors :
if(!isset($_SESSION['session_timeout'])){$_SESSION['session_timeout']=3600;};

if(time()-$_SESSION['last_access']>$_SESSION['session_timeout'])
{
  unset($_SESSION['last_access']);
  unset($_SESSION['user']);
  unset($_SESSION['ipaddr']);
  header("Location: ../index.php");
  die();
}
if($_SERVER['REMOTE_ADDR']!=$_SESSION['ipaddr'])
{
  unset($_SESSION['last_access']);
  unset($_SESSION['user']);
  unset($_SESSION['ipaddr']);
  header("Location: ../index.php");
  die();
}
$_SESSION['last_access']=time();
};
?>
