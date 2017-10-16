<?php
require 'framework.php';
require 'participant_status.php';

function on_leave($e)
{
   global $db; 
   
   $date_min = ($e['semester'] == 'V') ? "1. jan" : "1. jul";
   $date_max = ($e['semester'] == 'V') ? "30. jun" : "31. dec";
   
   $ts_min = strtotime("$date_min " . $e['year']);
   $ts_max = strtotime("$date_max " . $e['year']);

   $query = "select ts_from, ts_to, status "
           . "from `leave` "
           . "where id_person = ".$e['person_id']." "
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

echo "
    <h1>Prosjektressurser</h1>
    <table border=1>
    <tr>
      <th>
         <a href=\"$php_self?_sort=firstname,lastname\" title=\"Sorter på fornavn...\">Fornavn</a>/
         <a href=\"$php_self?_sort=lastname,firstname\" title=\"Sorter på etternavn...\">Etternavn</a></th>
      <th><a href=\"$php_self?_sort=list_order,lastname,firstname\" title=\"Sorter på instrumentgruppe...\">Instrument</a></th>
      <th><a href=\"$php_self?_sort=status,list_order,lastname,firstname\" title=\"Sorter på medlemsstatus...\">Status</a></th>\n";

$qperiod = "(project.year > " . $season->year() . " " .
        "  or (project.year = " . $season->year() . " ";
if ($season->semester() == 'H')
   $qperiod .= "and project.semester = '" . $season->semester() . "' ";
$qperiod .= "))";


$query = "SELECT id, name, semester, year " .
        "FROM project " .
        "where $qperiod " .
        "order by year, semester DESC, project.id";
$stmt = $db->query($query);

foreach ($stmt as $row)
   echo "<th><a href=\"participant_x1.php?id=".$row['id']."\">".$row['name']."<br>".$row['semester'],$row['year']."</a></td>\n";
echo "</tr><tr>";

if (is_null($sort))
   $sort = "list_order,lastname,firstname";

$query = "SELECT person.id as person_id, " .
        "project.id as project_id,  " .
        "project.name as project_name, " .
        "firstname, lastname, instrument, " .
        "person.status as status, " .
        "year, semester " .
        "FROM person, instruments, project " .
        "where instruments.id = id_instruments " .
        "and not person.status = $db->per_stat_quited " .
        "and $qperiod " .
        "order by $sort, year, semester DESC, project.id";
$stmt = $db->query($query);

$prev_id = 0;

foreach ($stmt as $row)
{
   if ($row['person_id'] != $prev_id)
   {
      echo "</tr><tr><td nowrap><a href=\"participant_1x.php?id=".$row['person_id']."\">".$row['firstname']." ".$row['lastname']."</a>\n";
      echo "</td><td> ".$row['instrument']." </td>\n";
      echo "</td><td> ".$db->per_stat[$row['status']]." </td>\n";
      $prev_id = $row['person_id'];
   }

   list($status, $blink) = participant_status($row['person_id'], $row['project_id']);
   
   $lstatus = on_leave($row);
   $bgcolor = '';
   if ($lstatus == $db->lea_stat_registered)
      $bgcolor = 'bgcolor=yellow';
   if ($lstatus == $db->lea_stat_granted)
      $bgcolor = 'bgcolor=red';
   if ($lstatus == $db->lea_stat_rejected)
      $bgcolor = 'bgcolor=pink';

   echo "<td align=center $bgcolor><a href=\"participant_11.php?id_person=".$row['person_id']."&id_project=".$row['project_id']."\">"
      . "<img src=\"images/part_stat_$status$blink.gif\" "
      . "border=0 title=\"{$db->par_stat[$status]}\"></a></td>\n";
}
?>  
</tr>
</table>
