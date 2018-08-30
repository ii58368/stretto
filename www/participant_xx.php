<?php
require 'framework.php';
require 'participant_status.php';

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

$qstat = "(project.status = $db->prj_stat_real " .
        "or project.status = $db->prj_stat_tentative " .
        "or project.status = $db->prj_stat_internal)";

$query = "SELECT id, name, semester, year, status " .
        "FROM project " .
        "where $qperiod " .
        "and $qstat " .
        "order by year, semester DESC, project.id";
$stmt = $db->query($query);

foreach ($stmt as $row)
{
   $text = $row['name']."<br>".$row['semester'].$row['year'];
   if ($row['status'] == $db->prj_stat_real || $row['status'] == $db->prj_stat_internal)
      echo "<th><a href=\"participant_x1.php?id=".$row['id']."\" title=\"Administrasjon av ressurser for ".$row['name']." prosjektet...\">$text</a></td>\n";
   else
      echo "<th>$text</th>\n";
}
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
        "and not (person.status = $db->per_stat_quited " .
        "or person.status = $db->per_stat_apply) " .
        "and $qstat " .
        "and $qperiod " .
        "order by $sort, year, semester DESC, project.id";
$stmt = $db->query($query);

$prev_id = 0;

foreach ($stmt as $row)
{
   if ($row['person_id'] != $prev_id)
   {
      echo "</tr><tr><td nowrap><a href=\"participant_1x.php?id=".$row['person_id']."\" title=\"Ressursstatus for ".$row['firstname']." ".$row['lastname']."...\">".$row['firstname']." ".$row['lastname']."</a>\n";
      echo "</td><td> ".$row['instrument']." </td>\n";
      echo "</td><td> ".$db->per_stat[$row['status']]." </td>\n";
      $prev_id = $row['person_id'];
   }

   list($status, $blink) = participant_status($row['person_id'], $row['project_id']);
   
   $lstatus = on_leave($row['person_id'], $row['semester'], $row['year']);
   $bgcolor = '';
   if ($lstatus == $db->lea_stat_registered)
      $bgcolor = 'bgcolor=yellow';
   if ($lstatus == $db->lea_stat_granted)
      $bgcolor = 'bgcolor=red';
   if ($lstatus == $db->lea_stat_rejected)
      $bgcolor = 'bgcolor=pink';

   $htext = is_null($blink) ? '' : 'Forløpig ';
   
   echo "<td align=center $bgcolor><a href=\"participant_11.php?id_person=".$row['person_id']."&id_project=".$row['project_id']."\">"
      . "<img src=\"images/part_stat_$status$blink.gif\" "
      . "border=0 title=\"$htext{$db->par_stat[$status]}\"></a></td>\n";
}
?>  
</tr>
</table>
