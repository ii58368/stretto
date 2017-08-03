<?php

require 'conf/auth.php';

if ($access->auth(AUTH::PRJM))
{
    include 'event.php';
}
else
{
    include 'about.php';
}

