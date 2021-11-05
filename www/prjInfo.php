<?php
require 'framework.php';

$query = "select name, orchestration, semester, year, "
        . "status, info "
        . " from project"
        . " where id=".request('id');
$stmt = $db->query($query);
$prj = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h1>Prosjektinfo</h1>\n";
if ($prj['status'] == $db->prj_stat_canceled)
   echo "<h2><font color=red>Prosjektet er avlyst!</font></h2>\n";
echo "<h2>".$prj['name']." ".$prj['semester']."-".$prj['year']."<a href=prjinfo_pdf.php?id=".request('id')." title=\"PDF versjon\"><img src=images/pdf.jpeg height=30></a></h2>\n";
echo str_replace("\n", "<br>\n", $prj['info']) . "\n";

echo "<h3>Repertoar</h3>";
   
$tb = new TABLE('id=no_border');

$query = "SELECT title, work, firstname, lastname, "
        . " music.comment as comment, "
        . " repository.comment as r_comment"
        . " from repository, music"
        . " where repository.id = music.id_repository"
        . " and music.status = $db->mus_stat_yes"
        . " and music.id_project = ".request('id')." "
        . " order by lastname, firstname, work";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   $tb->td($row['firstname'] . ' ' . $access->hlink2("repository.php?search=" . urlencode($row['lastname']), $row['lastname']), 'valign=top');
   $tb->td($row['title']."<br>".$row['r_comment']);
   if (strlen($row['work']) > 0)
      $tb->td("fra ".$row['work'], 'valign=top');
   $tb->td($row['comment'], 'valign=top');
   $tb->tr();
}

unset($tb);

echo "<h3>Prøveplan</h3>";

$tb = new TABLE('no_border');

$tb->th('Dato');
$tb->th('Prøvetid');
$tb->th('Lokale');
$tb->th('Merknad');

$query = "SELECT date, time, " .
        "plan.location as location, location.name as lname, " .
        "location.url as url, " .
        "plan.comment as comment " .
        "FROM project, plan, location " .
        "where id_location = location.id " .
        "and id_project = project.id " .
        "and plan.id_project = ".request('id')." " .
        "and plan.event_type = $db->plan_evt_rehearsal " .
        "order by date,tsort,time";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   $tb->tr();
   $tb->td(strftime('%a %e.%b', $row['date']));
   $tb->td($row['time']);
   $tb->td($access->hlink(strlen($row['url']) > 0, $row['url'], $row['lname']) . ' ' . $row['location']);
   $tb->td(str_replace("\n", "<br>\n", $row['comment']));
}

unset($tb);

echo "<p>\n";

echo "<h3>Musikere</h3>\n";

$query = "select firstname, lastname, instrument, stat_final"
        . " from person, instruments, participant"
        . " where participant.id_project=".request('id')
        . " and participant.id_instruments = instruments.id"
        . " and participant.id_person = person.id"
        . " and participant.stat_inv = $db->par_stat_yes"
        . " and participant.stat_final = $db->par_stat_yes"
        . " order by instruments.list_order, -participant.position DESC, -person.def_pos DESC";
$stmt = $db->query($query);

$last_instrument = '';

echo "<ul>";
foreach ($stmt as $e)
{
   if ($last_instrument != $e['instrument'])
      echo "</ul><p><b>".$e['instrument']."</b><ul>\n";
   $name = $e['firstname']." ".$e['lastname'];
   echo "<li>$name</li>\n";
   $last_instrument = $e['instrument'];
}
echo "</ul>";
