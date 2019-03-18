<?php
require 'framework.php';

$query = "select name, orchestration, semester, year, "
        . "status, info "
        . " from project"
        . " where id=".request('id');
$stmt = $db->query($query);
$prj = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h1>Prosjektinfo</h1>
    <h2>".$prj['name']." ".$prj['semester']."-".$prj['year']."<a href=prjinfo_pdf.php?id=".request('id')." title=\"PDF versjon\"><img src=images/pdf.jpeg height=30></a></h2>\n";
echo str_replace("\n", "<br>\n", $prj['info']) . "\n";

echo "<h3>Repertoar</h3>
    <table id=\"no_border\">\n";

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
   echo "<tr><td valign=top>".$row['firstname']." ".$row['lastname'].":</td><td>".$row['title']."<br>".$row['r_comment']."</td>";
   if (strlen($row['work']) > 0)
      echo "<td valign=top>fra ".$row['work']."</td>";
   echo "<td valign=top>".$row['comment']."</td>\n";
   echo "</tr>\n";
}
echo "</table><p>\n";

echo "<h3>Prøveplan</h3>
    <table id=\"no_border\">
    <tr>
      <th>Dato</th>
      <th>Prøvetid</th>
      <th>Lokale</th>
      <th>Merknad</th>
    </tr>";

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
   echo "<tr>
       <td>" . strftime('%a %e.%b', $row['date']) . "</td>" .
   "<td>".$row['time']."</td><td>";
   if (strlen($row['url']) > 0)
      echo "<a href=\"".$row['url']."\">".$row['lname']."</a>";
   else
      echo $row['lname'];
   echo $row['location'];
   echo "</td><td>";
   echo str_replace("\n", "<br>\n", $row['comment']);
   echo "</td>" .
   "</tr>\n";
}
echo "</table><p>\n";

echo "<h3>Musikere</h3>\n";

$query = "select firstname, lastname, instrument, stat_inv, stat_final"
        . " from person, instruments, participant"
        . " where participant.id_project=".request('id')
        . " and participant.id_instruments = instruments.id"
        . " and participant.id_person = person.id"
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
