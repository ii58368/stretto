<?php
require 'framework.php';
include_once 'participant_status.php';

if (is_null($sort))
   $sort = 'year,semester DESC,id';

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
      <th bgcolor=#A6CAF0>Prosjekt</th>
      <th bgcolor=#A6CAF0>Sem</th>
      <th bgcolor=#A6CAF0>Status</th>
      <th bgcolor=#A6CAF0>Påmelding-/persmisjonsfrist</th>
      <th bgcolor=#A6CAF0>Tutti</th>
    </tr>";



$query = "SELECT project.id as id, name, semester, year, status, " .
        "deadline, orchestration " .
        "FROM project " .
        "where project.year >= " . $season->year() . " " .
        "and status = $db->prj_stat_public " .
        "or status = $db->prj_stat_tentative " .
        "order by $sort";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   list($status, $blink) = participant_status(request('id'), $row['id']);
   echo "<tr>
      <td>";
   if ($row['status'] == $db->prj_stat_public)
      echo "<a href=\"participant_11.php?id_project=".$row['id']."&id_person=".$pers['id']."\" title=\"Påmelding eller søk om permisjon\">";
   echo $row['name'];
   if ($row['status'] == $db->prj_stat_public)
      echo "</a>";
   echo "</td>\n" .
      "<td>".$row['semester']." ".$row['year']."</td>" .
   "<td align=center>";
   if ($row['status'] == $db->prj_stat_public)
      echo "<img src=\"images/part_stat_$status$blink.gif\" border=0 title=\"".$db->prj_stat[$row['status']]."\">";
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

