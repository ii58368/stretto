<?php

include 'framework.php';


echo "<h1>Spilleplan for " . $whoami->name() . "</h1>\n";

echo "<table><tr>
      <th bgcolor=#A6CAF0>Dato</th>
      <th bgcolor=#A6CAF0>Prøvetid</th>
      <th bgcolor=#A6CAF0>Lokale</th>
      <th bgcolor=#A6CAF0>Prosjekt</th>
      <th bgcolor=#A6CAF0>Merknad</th>
    </tr>";


$query = "SELECT plan.id as id, "
        . "plan.date as date, "
        . "plan.time as time, "
        . "plan.tsort as sort, "
        . "project.id as id_project, "
        . "id_location, "
        . "plan.location as location, "
        . "location.name as lname, "
        . "project.name as pname, "
        . "location.url as url, "
        . "plan.comment as comment, "
        . "orchestration "
        . "FROM project, plan, location, participant "
        . "where plan.id_location = location.id "
        . "and plan.id_project = project.id "
        . "and participant.id_project = project.id "
        . "and participant.id_person = ".$whoami->id()." "
        . "and participant.stat_final = $db->par_stat_yes "
        . "and plan.event_type = $db->plan_evt_rehearsal "
        . "and plan.date > " . strtotime('now') . " "
        . "order by plan.date,plan.tsort,plan.time";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   echo "<tr>\n";
   echo "<td align=right nowrap>" . strftime('%a %e.%b', $row['date']) . "</td>" .
   "<td>" . $row['time'] . "</td><td>";
   if (strlen($row['url']) > 0)
      echo "<a href=\"" . $row['url'] . "\">" . $row['lname'] . "</a>";
   else
      echo $row['lname'];
   echo $row['location'];
   echo "</td><td><a href=\"prjInfo.php?id=".$row[id_project]."\">" . $row['pname'] . "</a>";
   if ($row['orchestration'] == $db->prj_orch_reduced)
      echo '*';
   echo "</td><td>";
   echo str_replace("\n", "<br>\n", $row['comment']);
   echo "</td>" .
   "</tr>";
}

echo "</table>";
