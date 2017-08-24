<?php
require 'framework.php';
require 'participant_status.php';

$sel_year = ($_REQUEST[from] == NULL) ? date("Y") : intval($_REQUEST[from]);
$prev_year = $sel_year - 1;

echo "
    <h1>Prosjektressurser</h1>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0><a href=\"$php_self?from=$prev_year&id_person={$row[person_id]}&id_project={$row[project_id]}&status={$status}&_sort={$sort}\"><img src=images/left.gif border=0 title=\"Forrige &aring;r...\"></a>
         <a href=\"$php_self?from=$sel_year&_sort=firstname,lastname\" title=\"Sorter p&aring; fornavn...\">Fornavn</a>/
         <a href=\"$php_self?from=$sel_year&_sort=lastname,firstname\" title=\"Sorter p&aring; etternavn...\">Etternavn</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?from=$sel_year&_sort=list_order,lastname,firstname\" title=\"Sorter p&aring; instrumentgruppe...\">Instrument</a></th>\n";

$query = "SELECT id, name, semester, year " .
        "FROM project " .
        "where year >= $sel_year " .
        "order by year, semester DESC, project.id";
$stmt = $db->query($query);

foreach ($stmt as $row)
   echo "<th bgcolor=#A6CAF0><a href=\"participant_x1.php?id={$row[id]}\">{$row[name]}<br>{$row[semester]}{$row[year]}</a></td>\n";
echo "</tr><tr>";

if ($sort == NULL)
   $sort = "list_order,lastname,firstname";

$query = "SELECT person.id as person_id, " .
        "project.id as project_id,  " .
        "project.name as project_name, " .
        "firstname, lastname, instrument " .
        "FROM person, instruments, project " .
        "where instruments.id = id_instruments " .
        "and not person.status = $db->per_stat_quited " .
        "and project.year >= $sel_year " .
        "order by $sort, year, semester DESC, project.id";
$stmt = $db->query($query);

$prev_id = 0;

foreach ($stmt as $row)
{
   if ($row[person_id] != $prev_id)
   {
      echo "</tr><tr><td bgcolor=#A6CAF0 nowrap><a href=\"participant_1x.php?id=$row[person_id]\">$row[firstname] $row[lastname]</a>\n";
      echo "</td><td bgcolor=#A6CAF0> $row[instrument] </td>\n";
      $prev_id = $row[person_id];
   }

   list($status, $blink) = participant_status($row[person_id], $row[project_id]);
   echo "<td align=center><a href=\"participant_11.php?id_person=$row[person_id]&id_project=$row[project_id]\">"
      . "<img src=\"images/part_stat_$status$blink.gif\" "
      . "border=0 title=\"{$db->par_stat[$status]}\"></a></td>\n";
}
?>  
</tr>
</table>

<?php
include 'framework_end.php';
?>

