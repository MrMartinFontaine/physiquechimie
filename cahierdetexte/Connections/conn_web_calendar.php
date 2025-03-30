<?php
$db_host= 'localhost';
$db_database= 'webcalendar';
$db_login='webcalendar';
$db_password= 'PASS_WEBCALENDAR';
$conn_webcalendar = mysql_pconnect($db_host, $db_login, $db_password) or die(mysql_error());
?>