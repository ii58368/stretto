<?php
require 'framework.php';

if ($action == 'insert')
{
   $query = "insert into shift (id_person, id_project, status) " .
           "values ('$_REQUEST[id_person]', '$_REQUEST[id_project]', $shi_stat_tentative)";
   $db->query($query);
}

if ($action == 'update')
{
   $next_status = intval($_REQUEST[status]) + 1;
   if ($next_status > 4)
      $next_status = 0;

   $query = "update shift set status = $next_status " .
           "where id_person = $_REQUEST[id_person] " .
           "and id_project = $_REQUEST[id_project]";
   $db->query($query);
}

$sel_year = ($_REQUEST[from] == NULL) ? date("Y") : intval($_REQUEST[from]);
$prev_year = $sel_year - 1;

echo "
    <h1>Turnus</h1>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0><a href=\"$php_self?from=$prev_year&id_person={$row[person_id]}&id_project={$row[project_id]}&status={$status}&_sort={$sort}\"><img src=images/left.gif border=0 title=\"Forrige &aring;r...\"></a>
         <a href=$php_self?from=$sel_year&_sort=firstname,lastname title=\"Sorter p&aring; fornavn...\">Fornavn</a>/
         <a href=$php_self?from=$sel_year&_sort=lastname,firstname title=\"Sorter p&aring; etternavn...\">Etternavn</a></th>
      <th bgcolor=#A6CAF0><a href=$php_self?from=$sel_year&_sort=list_order,lastname,firstname title=\"Sorter p&aring; instrumentgruppe...\">Instrument</a></th>";

$query = "SELECT id, name, semester, year " .
        "FROM project " .
        "where year >= $sel_year " .
        "order by year, semester DESC, project.id";
$stmt = $db->query($query);

foreach ($stmt as $row)
   echo "<th bgcolor=#A6CAF0><a href=dirPlan.php?_sort=time,date&id_project={$row[id]}>{$row[name]}<br>{$row[semester]}{$row[year]}</a></td>";
echo "</tr><tr>";

if ($sort == NULL)
   $sort = "(select IFNULL(max(id_project), 0) from shift where id_person = person.id and status = $shi_stat_confirmed), list_order, lastname, firstname ";

$query = "SELECT person.id as person_id, " .
        "person.status_dir as status_dir, " .
        "project.id as project_id,  " .
        "project.name as project_name, " .
        "firstname, lastname, instrument, " .
        "person.comment as comment " .
        "FROM person, instruments, project " .
        "where instruments.id = id_instruments " .
        "and person.status = $per_stat_member " .
        "and project.year >= $sel_year " .
        "order by $sort, year, semester DESC, project.id";
$stmt = $db->query($query);

$prev_id = 0;

$status_tab = array(
    $shi_stat_free => "Klikk for &aring; bli valgt inn i regikomiteen",
    $shi_stat_tentative => "Tentativt",
    $shi_stat_confirmed => "Bekreftet",
    $shi_stat_failed => "Ikke godkjent oppm&oslash;te",
    $shi_stat_leave => "Permisjon",
    $shi_stat_responsible => "Regiansvarlig"
);

foreach ($stmt as $row)
{
   if ($row[person_id] != $prev_id)
   {
      echo "</tr><tr><td bgcolor=#A6CAF0><a href=history.php?id_person=$row[person_id] title=\"$row[comment]\">$row[firstname] $row[lastname]</a>";
      if ($row[status_dir] == $per_dir_nocarry)
         echo " <img src=\"images/chair-minus-icon.png\" border=0 title=\"Kan ikke l&oslash;fte bord\"></h2>";
      echo "</td><td bgcolor=#A6CAF0> $row[instrument] </td>";
      $prev_id = $row[person_id];
   }
   $query = "SELECT status, comment from shift " .
           "where id_project = {$row[project_id]} " .
           "and id_person = {$row[person_id]}";
   $stmt2 = $db->query($query);
   $status = $shi_stat_free;
   $comment = "";
   $action = "insert";
   foreach ($stmt2 as $row2)
   {
      $status = $row2[status];
      $comment = $row2[comment];
      $action = "update";
   }
   echo "<td align=center><a href=\"$_SERVER[PHP_SELF]?_action={$action}&id_person={$row[person_id]}&id_project={$row[project_id]}&status={$status}&from=$sel_year&_sort={$sort}\"><img src=\"images/shift_status_{$status}.gif\" border=0 title=\"{$status_tab[$status]} ({$row[project_name]}) {$comment}\"></a></td>";
}
?> 
</tr>
</table>

<?php
include 'framework_end.php';
?>

