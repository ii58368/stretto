<?php

require_once 'auth.php';

if (count($_GET) == 0)
   $season->reset();


if (empty($_SERVER['HTTP_REFERER']))
{
   include 'welcome.php';
}
else
{
   include 'myplan.php';
}


