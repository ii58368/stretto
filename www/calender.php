<?php
require 'framework.php';

if ($access->auth(AUTH::CONS))
   echo "<a href=concert.php>[Rediger]</a>\n";
   
$id_project = request('id_project');

if (!$id_project)
   echo "<h1>Konsertkalender</h1><hr>";

$query = "SELECT concert.id as id, "
        . "concert.ts as ts, "
        . "concert.time as time, "
        . "location.name as lname, "
        . "concert.heading as heading, "
        . "concert.text as text "
        . "from concert, location, project "
        . "where concert.id_project = project.id "
        . "and concert.id_location = location.id ";
if ($id_project)
   $query .= "and project.id = $id_project ";
else
   $query .= "and project.year >= " . $season->year() . " ";
if (!$access->auth(AUTH::CONS, AUTH::PRJ_RO))
   $query .= "and project.status = $db->prj_stat_public ";
$query .=  "order by concert.ts";

$stmt = $db->query($query);

$first_time = true;

foreach ($stmt as $row)
{
   if (!$first_time)
      echo "<hr>\n";
   $first_time = false;
   
   echo "<h1>" . $row['heading'] . "</h1>\n";
   
   echo "<h3> " . strftime('%A %e. %b %Y', $row['ts']) . " kl. " . $row['time'] . 
           " - " . $row['lname'] . "</h3>\n";
   
   echo str_replace("\n", "<br>\n", $row['text']);
}

