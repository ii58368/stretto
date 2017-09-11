<?php

require_once 'auth.php';

function has_access()
{
   global $access;
   global $db;
   global $whoami;
   
   if ($access->auth(AUTH::PRJM))
      return true;

   if (!is_numeric($_REQUEST[id_project]))
      return false;

   $q = "select count(*) as count from participant "
           . "where id_person = " . $whoami->id() . " "
           . "and id_project = $_REQUEST[id_project] "
           . "and stat_final = $db->par_stat_yes";
   $s = $db->query($q);
   $e = $s->fetch(PDO::FETCH_ASSOC);
   
   if ($e[count] > 0)
      return true;

   return false;
}

if (!has_access())
   exit(0);

require 'event.php';
