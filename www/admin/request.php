<?php

$sort = $_REQUEST['_sort'];
str_replace("|", ",", $sort);

$action = $_REQUEST['_action'];

$no = $_REQUEST['_no'];

$delete = $_REQUEST['_delete'];

$php_self = $_SERVER[PHP_SELF];

$whoami = $_SERVER[PHP_AUTH_USER];

?>
