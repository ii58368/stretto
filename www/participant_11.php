<?php
require 'framework.php';

$id_person = (is_null(request('id_person'))) ? $whoami->id() : request('id_person');

$query = "select firstname, lastname, instrument, instruments.id as id_instruments"
        . " from person, instruments"
        . " where person.id=$id_person"
        . " and id_instruments = instruments.id";
$stmt = $db->query($query);
$pers = $stmt->fetch(PDO::FETCH_ASSOC);

$query = "select name, deadline, orchestration, semester, year, "
        . "status, info, valid_par_stat"
        . " from project"
        . " where id=" . request('id_project');
$stmt = $db->query($query);
$prj = $stmt->fetch(PDO::FETCH_ASSOC);

if (request('stat_self'))
{
   $ts = strtotime("now");

   $q = "select * from participant where id_project=".request('id_project')." and id_person=$id_person";
   $stmt = $db->query($q);
   if ($stmt->rowCount() == 0)
   {
      $query = "insert into participant (id_person, id_project, stat_self, ts_self, comment_self, id_instruments) " .
              "values ($id_person, ".request('id_project').", ".request('stat_self').", $ts, "
              . $db->qpost('comment_self').", " . $pers['id_instruments'] . ")";
   } else
   {
      $query = "update participant set " .
              "stat_self = ".request('stat_self').", " .
              "ts_self = $ts, " .
              "comment_self = " . $db->qpost('comment_self') . " " .
              "where id_person = $id_person " .
              "and id_project = ".request('id_project');
   }
   $db->query($query);
}

$query = "select *"
        . " from participant, instruments"
        . " where participant.id_instruments = instruments.id "
        . " and id_person=$id_person"
        . " and id_project=".request('id_project');
$stmt = $db->query($query);
if ($stmt->rowCount() > 0)
   $part = $stmt->fetch(PDO::FETCH_ASSOC);

echo "
    <h1>".$prj['name']." ".$prj['semester']."-".$prj['year']."</h1>\n";
echo str_replace("\n", "<br>\n", $prj['info']) . "\n";
echo "<h2>Spilleplan</h2>
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
        "and plan.id_project = ".request('id_project')." " .
        "and plan.event_type = $db->plan_evt_rehearsal " .
        "order by date,tsort,time";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   echo "<tr>
       <td>" . strftime('%a %e.%b %y', $row['date']) . "</td>" .
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

echo "<form action=$php_self method=post>
   <input type=hidden name=_action value=update>
   <input type=hidden name=id_person value=$id_person>
   <input type=hidden name=id_project value=".request('id_project').">
   <table border=0>
  <tr>
  <td>Navn:</td><td>".$pers['firstname']." ".$pers['lastname']."</td>
  </tr>
  <tr>
  <td>Instrument:</td><td>";
echo (isset($part)) ? $part['instrument'] : $pers['instrument'];
echo "</td>
  </tr>
  <tr>
  <td>";
echo ($prj['orchestration'] == $db->prj_orch_tutti) ? "Permisjonsfrist:" : "Påmeldingsprist:";
echo "</td><td>";
echo ($prj['deadline'] < time()) ? "<font color=red>" . strftime('%a %e.%b %y', $prj['deadline']) . "</font>" :
        strftime('%a %e.%b %y', $prj['deadline']);
echo "</td></tr>\n";
echo "<tr><td>Registrert:</td><td>";
if (isset($part))
   echo (is_null(request('stat_self'))) ? strftime('%a %e.%b %y', $part['ts_self']) : "<font color=green>" . strftime('%a %e.%b %y', $part['ts_self']) . "</font> (Kommentar kan endres på frem til dato for permisjonsfrist)";
echo "</td></tr>\n";
if ($prj['deadline'] > time())
{
   echo "<tr><td>Ønsker å være med:</td><td>";
   for ($i = 0; $i < count($db->par_stat); $i++)
   {
      if ($prj['valid_par_stat'] & (1 << $i))
      {
         echo "<input type=radio name=stat_self value=$i";
         if (isset($part) && $part['stat_self'] == $i)
            echo " checked";
         echo ">" . $db->par_stat[$i] . "<br>\n";
      }
   }
   echo "</td></tr>\n";
   echo "<tr><td>Kommentar:</td><td><textarea cols=30 rows=5 wrap=virtual name=comment_self>".$part['comment_self']."</textarea></td></tr>\n";
   echo "<tr><td></td><td><input type=submit value=Registrer></td></tr>";
}
else
{
   echo "<tr><td>Registrert svar:</td><td><b>";
   if (isset($part))
      echo $db->par_stat[$part['stat_self']];
   echo "</b></td></tr>\n";
   echo "<tr><td>Kommentar:</td><td><b>";
   if (isset($part))
      echo str_replace("\n", "<br>\n", $part['comment_self']);
   echo "</b></td></tr>\n";
}
echo "</table>\n</form>";
