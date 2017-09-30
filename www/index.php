<?php

require_once 'auth.php';
require_once 'request.php';

$season->reset();

if ($access->auth(AUTH::PRJM))
{
    include 'event.php';
}
else
{
    include 'about.php';
}

