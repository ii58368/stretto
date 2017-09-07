<?php

require_once 'whoami.php';

setlocale(LC_TIME, "no_NO");
date_default_timezone_set('Europe/Paris');

$sort = $_REQUEST['_sort'];
str_replace("|", ",", $sort);

$action = $_REQUEST['_action'];

$no = $_REQUEST['_no'];

$delete = $_REQUEST['_delete'];

$php_self = $_SERVER[PHP_SELF];

$whoami = new WHOAMI();

?>