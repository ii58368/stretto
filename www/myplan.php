<?php

include 'framework.php';


echo "<h1>Spilleplan for " . $whoami->name() . "</h1>
     Dette er din individuelle spilleplan basert på de prosjekter 
     du skal være med på. 
     <p>";

echo "<table><tr>
      <th>Dato</th>
      <th>Prøvetid</th>
      <th>Lokale</th>
      <th>Prosjekt</th>
      <th>Merknad</th>
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
        . "orchestration, "
        . "participant.stat_final as stat_final "
        . "FROM project, plan, location, participant "
        . "where plan.id_location = location.id "
        . "and plan.id_project = project.id "
        . "and participant.id_project = project.id "
        . "and participant.id_person = ".$whoami->id()." "
        . "and participant.stat_inv = $db->par_stat_yes "
        . "and plan.event_type = $db->plan_evt_rehearsal "
        . "and plan.date >= " . strtotime('today') . " "
        . "and project.status = $db->prj_stat_real "
        . "order by plan.date,plan.tsort,plan.time";

$stmt = $db->query($query);

$gfont = "<font color=lightgrey>";

foreach ($stmt as $row)
{
   if ($row['stat_final'] == $db->par_stat_no)
      continue;
   
   $date = strftime('%a %e.%b', $row['date']);
   $time = $row['time'];
   $url = $row['url'];
   $lname = $row['lname'];
   $location = $row['location'];
   $pname = $row['pname'];
   $comment = str_replace("\n", "<br>\n", $row['comment']);
   
   echo "<tr>";
   
   if ($row['stat_final'] == $db->par_stat_void)
   {
      echo "<td align=right nowrap>$gfont$date</font></td>"
              . "<td>$gfont$time</font></td>"
              . "<td>$gfont$lname$location</font></td>"
              . "<td>$gfont$pname";
      if ($row['orchestration'] == $db->prj_orch_reduced)
         echo '*';
      echo "</font></td>"
      . "<td>$gfont$comment</font></td>";
   }
   else
   {
      echo "<td align=right nowrap>$date</td>"
              . "<td>$time</td>";
      if (strlen($url) > 0)
         echo "<td><a href=\"$url\">$lname</a>$location</td>";
      else
         echo "<td>$lname$location</td>";
      echo "<td><a href=\"prjInfo.php?id=".$row['id_project']."\">$pname</a>";
      if ($row['orchestration'] == $db->prj_orch_reduced)
         echo '*';
      echo "</td>"
      . "<td>$comment</td>";     
   }
     
   echo "</tr>";
}

echo "</table>";
