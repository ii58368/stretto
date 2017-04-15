<?php
//$dbhost = 'mysql04.fastname.no';
//$dbuser = 'd301218';
//$dbpass = 'slow9down!';
//$dbname = 'd301218';

$dbhost = '127.0.0.1';
$dbuser = 'root';
$dbpass = 'Knoll.and.Tott';
$dbname = 'stretto';

$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
mysql_select_db($dbname);
mysql_set_charset('utf8', $conn);
?> 


