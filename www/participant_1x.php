<?php
require 'framework.php';
include_once 'participant_status.php';

if (is_null($sort))
   $sort = 'year,semester DESC,id';

function on_leave($id_person, $semester, $year)
{
   global $db; 
   
   $date_min = ($semester == 'V') ? "1. jan" : "1. jul";
   $date_max = ($semester == 'V') ? "30. jun" : "31. dec";
   
   $ts_min = strtotime("$date_min " . $year);
   $ts_max = strtotime("$date_max " . $year);

   $query = "select ts_from, ts_to, status "
           . "from `leave` "
           . "where id_person = $id_person "
           . "and ((ts_from >= $ts_min and ts_to <= $ts_max) "
           . "or (ts_from < $ts_min and ts_to > $ts_min) "
           . "or (ts_from < $ts_max and ts_to > $ts_max) "
           . "or (ts_from < $ts_min and ts_to > $ts_max)) "
           . "order by status";

   $stmt = $db->query($query);
   
   foreach ($stmt as $p)
      return $p['status'];
   
   return $db->lea_stat_unknown;
}

$query = "select person.id as id, firstname, lastname, instrument"
        . " from person, instruments"
        . " where person.id = " . request('id') . " "
        . " and person.id_instruments = instruments.id";
$stmt = $db->query($query);
$pers = $stmt->fetch(PDO::FETCH_ASSOC);

echo "
    <h1>Prosjekter for " . $pers['firstname'] . " " . $pers['lastname'] . " (" . $pers['instrument'] . ")</h1>
    <form action='$php_self' method=post>
    <table border=1>
    <tr>
      <th>Prosjekt</th>
      <th>Sem</th>
      <th>Status</th>
      <th>Påmelding-/persmisjonsfrist</th>
      <th>Tutti</th>
    </tr>";



$query = "SELECT project.id as id, name, semester, year, status, " .
        "deadline, orchestration " .
        "FROM project " .
        "where project.year >= " . $season->year() . " " .
        "and status = $db->prj_stat_real " .
        "or status = $db->prj_stat_tentative " .
        "order by $sort";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   list($status, $blink) = participant_status(request('id'), $row['id']);
   
   $lstatus = on_leave($pers['id'], $row['semester'], $row['year']);
   $bgcolor = '';
   if ($lstatus == $db->lea_stat_registered)
      $bgcolor = 'bgcolor=yellow';
   if ($lstatus == $db->lea_stat_granted)
      $bgcolor = 'bgcolor=red';
   if ($lstatus == $db->lea_stat_rejected)
      $bgcolor = 'bgcolor=pink';

   echo "<tr>
      <td>";
   if ($row['status'] == $db->prj_stat_real)
      echo "<a href=\"participant_11.php?id_project=".$row['id']."&id_person=".$pers['id']."\" title=\"Påmelding eller søk om permisjon\">";
   echo $row['name'];
   if ($row['status'] == $db->prj_stat_real)
      echo "</a>";
   echo "</td>\n" .
      "<td>".$row['semester']." ".$row['year']."</td>" .
   "<td align=center $bgcolor>";
   $tstat = $db->par_stat[$status];
   if (!is_null($blink))
      $tstat .= " (under behandling i styret...)";
   if ($row['status'] == $db->prj_stat_real)
      echo "<img src=\"images/part_stat_$status$blink.gif\" border=0 title=\"$tstat\">";
   echo "</td>\n";
   echo "<td>" . strftime('%a %e.%b %y', $row['deadline']) . "</td>" .
   "<td>";
   if ($row['orchestration'] == $db->prj_orch_tutti)
      echo "<center><img src=\"images/tick2.gif\" border=0></center>";
   echo "</td>
         </tr>";
}
?> 

</table>
</form>

