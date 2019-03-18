<?php

require_once 'whoami.php';
require_once 'season.php';

setlocale(LC_TIME, "no_NO.UTF-8");
date_default_timezone_set('Europe/Paris');

function request($key)
{
   return isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
}

$sort = request('_sort');
if (!is_null($sort))
   $sort = str_replace(" ", "+", $sort);

$action = request('_action');
$no = request('_no');
$delete = request('_delete');

$php_self = $_SERVER['PHP_SELF'];

$prj_name = 'Stretto';

$whoami = new WHOAMI();
$season = new SEASON();
