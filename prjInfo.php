
<?php
require 'framework.php';

$query = "select name, orchestration, semester, year, "
        . "status, info "
        . " from project"
        . " where id=$_REQUEST[id]";
$stmt = $db->query($query);
$prj = $stmt->fetch(PDO::FETCH_ASSOC);

echo "
    <h1>$prj[name] $prj[semester]-$prj[year]</h1>\n";
echo str_replace("\n", "<br>\n", $prj[info]) . "\n";

echo "<h2>Verk</h2>
    <table border=0>
    <tr>
      <th bgcolor=#A6CAF0>Komponist</th>
      <th bgcolor=#A6CAF0>Verk</th>
      <th bgcolor=#A6CAF0>Fra</th>
    </tr>";

$query = "SELECT title, work, firstname, lastname"
        . " from repository, music"
        . " where repository.id = music.id_repository"
        . " and music.id_project = $_REQUEST[id] "
        . " order by lastname, firstname, work";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   echo "<tr><td>$row[firstname] $row[lastname]</td><td>$row[title]</td><td>$row[work]</td></tr>\n";
}
echo "</table><p>\n";

echo "<h2>Prøveplan</h2>
    <table border=0>
    <tr>
      <th bgcolor=#A6CAF0>Dato</th>
      <th bgcolor=#A6CAF0>Prøvetid</th>
      <th bgcolor=#A6CAF0>Lokale</th>
      <th bgcolor=#A6CAF0>Merknad</th>
    </tr>";

$query = "SELECT date, time, " .
        "plan.location as location, location.name as lname, " .
        "location.url as url, " .
        "plan.comment as comment " .
        "FROM project, plan, location " .
        "where id_location = location.id " .
        "and id_project = project.id " .
        "and plan.id_project = $_REQUEST[id] " .
        "and plan.event_type = $plan_evt_rehearsal " .
        "order by date,tsort,time";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   echo "<tr>
       <td>" . date('D j.M y', $row[date]) . "</td>" .
   "<td>{$row[time]}</td><td>";
   if (strlen($row[url]) > 0)
      echo "<a href=\"{$row[url]}\">{$row[lname]}</a>";
   else
      echo $row[lname];
   echo $row[location];
   echo "</td><td>";
   echo str_replace("\n", "<br>\n", $row[comment]);
   echo "</td>" .
   "</tr>\n";
}
echo "</table><p>\n";

echo "<h2>Musikere</h2>\n";

$query = "select firstname, lastname, instrument"
        . " from person, instruments, participant"
        . " where participant.id_project=$_REQUEST[id]"
        . " and person.id_instruments = instruments.id"
        . " and participant.id_person = person.id"
        . " order by instruments.list_order, participant.position";
$stmt = $db->query($query);

echo "<ul>";
foreach ($stmt as $e)
{
   if ($last_instrument != $e[instrument])
      echo "</ul><p><b>$e[instrument]</b><ul>\n";
   echo "<li>$e[firstname] $e[lastname]</li>\n";
}
echo "</ul>";

require 'framework_end.php';
?>

