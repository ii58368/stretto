<?php

if ($_SERVER['SERVER_NAME'] == "internal.oslosymfoniorkester.no")
{
   echo "<html><head><meta http-equiv=\"Refresh\" content=\"0; url=https://stretto.oslosymfoniorkester.no\" /></head></html>";
   exit(0);
}

require_once 'auth.php';

if (count($_GET) == 0)
   $season->reset();

include 'welcome.php';


