<?php

require_once 'auth.php';

$season->reset();

if ($access->auth(AUTH::PRJM))
{
    include 'event.php';
}
else
{
    include 'about.php';
}

