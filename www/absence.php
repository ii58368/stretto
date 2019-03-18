<?php

require 'framework.php';

function get_project()
{
   global $db;

   $query = "select name, semester, year "
           . " from project"
           . " where id=" . request('id_project');
   $stmt = $db->query($query);
   return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_groups()
{
   global $whoami;
   global $db;

   $query = "select groups.id as id, groups.name as name "
           . "from participant, instruments, groups, person "
           . "where participant.id_person = person.id "
           . "and person.id = " . $whoami->id() . " "
           . "and participant.id_project = ".request('id_project')." "
           . "and participant.id_instruments = instruments.id "
           . "and instruments.id_groups = groups.id";
   $stmt = $db->query($query);
   return $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($sort == NULL)
   $sort = 'list_order,-position+DESC,-def_pos+DESC,firstname,lastname';

$grp = get_groups();
$prj = get_project();

echo "
    <h1>Fravær</h1>
    <h2>".$prj['name']." ".$prj['semester']."-".$prj['year']."</h2>
    <table border=1>
    <tr>
      <th><a href=\"$php_self?id_project=".request('id_project')."&_sort=firstname,lastname\" title=\"Sorter på fornavn, deretter etternavn\">Navn</a></th>
      <th><a href=\"$php_self?id_project=".request('id_project')."&_sort=list_order,-position+DESC,-def_pos+DESC,firstname,lastname\" title=\"Sorter i partiturrekkefølge, deretter fornavn og deretter etternavn\">Instrument</a></th>
      <th>Status</th>\n";

$query = "select id, date from plan "
        . "where id_project = ".request('id_project')." "
        . "and event_type = $db->plan_evt_rehearsal "
        . "order by date";
$stmt = $db->query($query);

foreach ($stmt as $e)
{
   $rehersal = strftime('%a %e.%b', $e['date']);
   if ($access->auth(AUTH::ABS_RW))
      echo "<th><a href=\"absenceEdit.php?id_plan=".$e['id']."\" title=\"Registrere oppmøte...\">$rehersal</a></th>";
   else
      echo "<th>$rehersal</th>";
}

if ($access->auth(AUTH::ABS_ALL))
{
   $query = "SELECT participant.id_person as id_person, firstname, lastname, "
           . "person.status as status, instrument, plan.id as id_plan "
           . "FROM person, participant, instruments, plan "
           . "where participant.id_project = ".request('id_project')." "
           . "and participant.id_instruments = instruments.id "
           . "and participant.stat_final = $db->par_stat_yes "
           . "and person.id = participant.id_person "
           . "and plan.id_project = ".request('id_project')." "
           . "and plan.event_type = $db->plan_evt_rehearsal "
           . "order by " . str_replace("+", " ", $sort) . ",plan.date";
}
else
{
   $query = "SELECT participant.id_person as id_person, firstname, lastname, "
           . "person.status as status, instrument, plan.id as id_plan "
           . "FROM person, participant, instruments, groups, plan "
           . "where groups.id = ".$grp['id']." "
           . "and instruments.id_groups = groups.id "
           . "and participant.id_instruments = instruments.id "
           . "and participant.id_project = ".request('id_project')." "
           . "and participant.stat_final = $db->par_stat_yes "
           . "and person.id = participant.id_person "
           . "and plan.id_project = ".request('id_project')." "
           . "and plan.event_type = $db->plan_evt_rehearsal "
           . "order by " . str_replace("+", " ", $sort) . ",plan.date";

}

$stmt = $db->query($query);

$prev_id = 0;

foreach ($stmt as $row)
{
   if ($row['id_person'] != $prev_id)
   {
      echo "</tr>
      <tr>
      <td>".$row['firstname']." ".$row['lastname']."</td>
      <td>".$row['instrument']."</td>
      <td>" . $db->per_stat[$row['status']] . "</td>";
      $prev_id = $row['id_person'];
   }

   $query = "select status, comment from absence "
           . "where id_person = ".$row['id_person']." "
           . "and id_plan = ".$row['id_plan'];

   $s = $db->query($query);
   $e = $s->fetch(PDO::FETCH_ASSOC);
   echo "<td align=center>";
   if ($e)
      echo "<img src=\"images/abs_stat_".$e['status'].".gif\" title=\"" . $db->abs_stat[$e['status']] . ": ".$e['comment']."\">";
   echo "</td>";
}

echo "</tr></table>\n";
