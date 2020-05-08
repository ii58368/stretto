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
           . "from instruments, groups, person "
           . "where person.id = " . $whoami->id() . " "
           . "and person.id_instruments = instruments.id "
           . "and instruments.id_groups = groups.id";
   $stmt = $db->query($query);
   return $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($sort == NULL)
   $sort = 'list_order,-position+DESC,-def_pos+DESC,firstname,lastname';

$grp = get_groups();
$prj = get_project();

echo "<h1>Fravær</h1>
    <h2>".$prj['name']." ".$prj['semester']."-".$prj['year']."</h2>\n";

$tb = new TABLE('border=1');
$tb->th("<a href=\"$php_self?id_project=".request('id_project')."&_sort=firstname,lastname\" title=\"Sorter på fornavn, deretter etternavn\">Navn</a>");
$tb->th("<a href=\"$php_self?id_project=".request('id_project')."&_sort=list_order,-position+DESC,-def_pos+DESC,firstname,lastname\" title=\"Sorter i partiturrekkefølge, deretter fornavn og deretter etternavn\">Instrument</a>");
$tb->th("Status");

$query = "select id, date from plan "
        . "where id_project = ".request('id_project')." "
        . "and event_type = $db->plan_evt_rehearsal "
        . "order by date";
$stmt = $db->query($query);

foreach ($stmt as $e)
{
   $rehersal = strftime('%a %e.%b', $e['date']);
   if ($access->auth(AUTH::ABS_RW))
      $tb->th("<a href=\"absenceEdit.php?id_plan=".$e['id']."\" title=\"Registrere oppmøte...\">$rehersal</a>");
   else
      $tb->th($rehersal);
}

if ($access->auth(AUTH::ABS_ALL))
{
   $query = "SELECT participant.id_person as id_person, firstname, lastname, "
           . "person.status as status, instrument, plan.id as id_plan "
           . "FROM person, participant, instruments, plan "
           . "where participant.id_project = ".request('id_project')." "
           . "and participant.id_instruments = instruments.id "
           . "and participant.stat_inv = $db->par_stat_yes "
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
           . "and participant.stat_inv = $db->par_stat_yes "
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
      $tb->tr();
      $tb->td($row['firstname']." ".$row['lastname']);
      $tb->td($row['instrument']);
      $tb->td($db->per_stat[$row['status']]);
      $prev_id = $row['id_person'];
   }

   $query = "select status, comment from absence "
           . "where id_person = ".$row['id_person']." "
           . "and id_plan = ".$row['id_plan'];

   $s = $db->query($query);
   $e = $s->fetch(PDO::FETCH_ASSOC);
   $img = $e ? "<img src=\"images/abs_stat_".$e['status'].".gif\" title=\"" . $db->abs_stat[$e['status']] . ": ".$e['comment']."\">" : '';
   $tb->td($img, 'align=center');
}
