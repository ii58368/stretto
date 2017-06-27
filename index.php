<?php

require 'conf/auth.php';

if ($auth->access(AUTH::PRJM))
{
    include 'event.php';
}
else
{
    include 'about.php';
}

