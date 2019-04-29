<?php
require 'framework.php';

if ($action == 'update' && $access->auth(AUTH::DIR_RW))
{
   $next_status = intval(request('stat_dir')) + 1;
   if ($next_status == $db->shi_stat_leave) // deprecated
      $next_status++;
   if ($next_status > 2)
      $next_status = 0;

   $query = "update participant set stat_dir = $next_status " .
           "where id_person = " . request('id_person') . " " .
           "and id_project = " . request('id_project');
   $db->query($query);
}

echo "
    <h1>Turnus</h1>
    <table border=1>
    <tr>
      <th>
         <a href=\"$php_self?_sort=firstname,lastname\" title=\"Sorter p&aring; fornavn...\">Fornavn</a>/
         <a href=\"$php_self?_sort=lastname,firstname\" title=\"Sorter p&aring; etternavn...\">Etternavn</a></th>
      <th><a href=\"$php_self?_sort=list_order,lastname,firstname\" title=\"Sorter p&aring; instrumentgruppe...\">Instrument</a></th>";

$qperiod = "(project.year > " . $season->year() . " " .
        "  or (project.year = " . $season->year() . " ";
if ($season->semester() == 'H')
   $qperiod .= "and project.semester = '" . $season->semester() . "' ";
$qperiod .= "))";

$query = "SELECT id, name, semester, year " .
        "FROM project " .
        "where status = $db->prj_stat_real "  .
        "and $qperiod " .
        "order by year, semester DESC, project.id";
$stmt = $db->query($query);

foreach ($stmt as $row)
   echo "<th><a href=dirPlan.php?_sort=time,date&id_project=".$row['id'].">".$row['name']."<br>".$row['semester'].$row['year']."</a></td>";
echo "</tr><tr>";

if (is_null($sort))
   $sort = "(select IFNULL(max(id_project), 0) from participant where id_person = person.id and (stat_dir = $db->shi_stat_confirmed or stat_dir = $db->shi_stat_responsible)), list_order, lastname, firstname ";

$query = "SELECT person.id as person_id, " .
        "person.status_dir as status_dir, " .
        "project.id as project_id,  " .
        "project.name as project_name, " .
        "firstname, lastname, instrument, " .
        "person.comment_dir as comment " .
        "FROM person, instruments, project " .
        "where instruments.id = id_instruments " .
        "and person.status = $db->per_stat_member " .
        "and person.status_dir != $db->per_dir_exempt " .
        "and project.status = $db->prj_stat_real " .
        "and $qperiod " .
        "order by $sort, year, semester DESC, project.id";
$stmt = $db->query($query);

$prev_id = 0;

foreach ($stmt as $row)
{
   if ($row['person_id'] != $prev_id)
   {
      echo "</tr><tr><th align=left nowrap><a href=myDirection.php?id_person=".$row['person_id']." title=\"".$row['comment']."\">".$row['firstname']." ".$row['lastname']."</a>";
      if ($row['status_dir'] == $db->per_dir_nocarry)
         echo " <img src=\"images/chair-minus-icon.png\" border=0 title=\"Kan ikke l&oslash;fte bord\"></h2>";
      echo "</th><th align=left>".$row['instrument']."</th>";
      $prev_id = $row['person_id'];
   }
   $query = "SELECT stat_dir, stat_final, comment_dir from participant " .
           "where id_project = ".$row['project_id']." " .
           "and id_person = ".$row['person_id']." " .
           "and (stat_final = $db->par_stat_yes or not stat_dir = $db->shi_stat_free)";
   $stmt2 = $db->query($query);
   echo "<td align=center>";
   foreach ($stmt2 as $row2)
   {
      $status = $row2['stat_dir'];
      if ($row2['stat_dir'] != $db->shi_stat_free && $row2['stat_final'] != $db->par_stat_yes)
         $status = $db->shi_stat_dropout;
      $comment = $row2['comment_dir'];
      $img = "<img src=\"images/shift_status_{$status}.gif\" border=0 title=\"{$db->shi_stat[$status]} (".$row['project_name'].") {$comment}\">";
      if ($access->auth(AUTH::DIR_RW))
         echo "<a href=\"$php_self?_action=update&id_person=".$row['person_id']."&id_project=".$row['project_id']."&stat_dir={$status}\">$img</a>\n";
      else
         if ($status != $db->shi_stat_free)
            echo $img;
   }
   echo "</td>";
}
?> 
</tr>
</table>
