<?php
include "../authentification/authcheck.php";
if (isset($_SESSION['droits'])){
switch ($_SESSION['droits']) {
case 0:
    header("Location: ../index.php");
    break;
case 1:
    header("Location: ../administration/index.php");
    break;
case 2:
$ch="Location: ecrire.php?date=".date('Ymd');
  header($ch);
 break;
case 3:
	
   header("Location: ../vie_scolaire/vie_scolaire.php");
    break;
case 4:

  header("Location: ../direction/direction.php");
    break;
default:
    header("Location: ../index.php");
}
}?>
