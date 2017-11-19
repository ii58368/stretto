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
    Dette er oversikten over alle orkesterets prosjekter og hvilke av disse du skal være med på. 
    Kolonnen Tutti viser om prosjektet er et tuttiprosjekt 
    for alle eller om det er et prosjekt med redusert besetning. 

    Under status ser du status for uttaket, 
    om styret har vedtatt hvem som skal være med etc. 
<p>
    <form action='$php_self' method=post>
    <table border=1>
    <tr>
      <th>Prosjekt</th>
      <th>Sem</th>
      <th>Status</th>
      <th>Påmelding-/persmisjonsfrist</th>
      <th>Tutti</th>
    </tr>";

$qperiod = "(project.year > " . $season->year() . " " .
        "  or (project.year = " . $season->year() . " ";
if ($season->semester() == 'H')
   $qperiod .= "and project.semester = '" . $season->semester() . "' ";
$qperiod .= "))";

$query = "SELECT project.id as id, name, semester, year, status, " .
        "deadline, orchestration " .
        "FROM project " .
        "where $qperiod " .
        "and (status = $db->prj_stat_real " .
        "or status = $db->prj_stat_tentative) " .
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
   
   $request = ($row['status'] == $db->prj_stat_real && 
           $status != $db->par_stat_void && 
           $lstatus != $db->lea_stat_granted);
      
   if ($request)
      echo "<a href=\"participant_11.php?id_project=".$row['id']."&id_person=".$pers['id']."\" title=\"Klikk for påmelding eller søk om permisjon...\">";
   echo $row['name'];
   if ($request)
      echo "</a>";
   echo "</td>\n" .
      "<td>".$row['semester']." ".$row['year']."</td>" .
   "<td align=center $bgcolor>";
   $tstat = $db->par_stat[$status];
   if (!is_null($blink))
      $tstat .= "\n(tilbakemeldingen er under behandling i styret...)";
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

