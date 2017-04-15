<?php

include 'conf/auth.php';

if (auth_access(auth_event))
{
    include 'event.php';
}
else
{
    include 'about.php';
}

?>
