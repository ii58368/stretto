
<?php

require 'framework.php';

function resources_list($id_plan)
{
   global $db;
   
  $q  = "SELECT firstname, lastname, instrument " .
   "FROM person, instruments, direction, participant, project, plan " .
   "where person.id = direction.id_person " .
   "and person.id_instruments = instruments.id " .
   "and participant.id_person = person.id " .
   "and participant.id_project = project.id " .
   "and project.id = plan.id_project " .
   "and plan.id = direction.id_plan " .
   "and (participant.stat_dir = $db->shi_stat_tentative " .
   "or participant.stat_dir = $db->shi_stat_confirmed) " .
   "and direction.id_plan = $id_plan " .
   "and direction.status = $db->dir_stat_allocated " .
   "order by lastname, firstname";

  $s = $db->query($q);

  foreach ($s as $e)
  {
    echo $e[firstname] . " " . $e[lastname] . " (" . $e[instrument] . ")<br>";
  }
}

function format_phone($ph)
{
  $ph = str_replace(' ', '', $ph);
  $ph = substr($ph, 0, -5) . " " . substr($ph, -5, 2) . " " . substr($ph, -3);
  if (strlen($ph) > 9)
    $ph = substr($ph, 0, -10) . " " . substr($ph, -10);
  return $ph;
}

function shift_list()
{
   global $db;

   echo "
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Navn</th>
      <th bgcolor=#A6CAF0>Instrument</th>
      <th bgcolor=#A6CAF0>Mobil</th>
    </tr>";


  $q[0] = "SELECT firstname, lastname, instrument, phone1 " .
   "FROM person, instruments, project " .
   "where person.id_instruments = instruments.id " .
   "and project.id = $_REQUEST[id_project] " .
   "and person.id = project.id_person";

  $q[1] = "SELECT firstname, lastname, instrument, phone1 " .
   "FROM person, instruments, participant " .
   "where person.id = participant.id_person " .
   "and person.id_instruments = instruments.id " .
   "and participant.id_project = $_REQUEST[id_project] " .
   "and (participant.stat_dir = $db->shi_stat_tentative " .
   "or participant.stat_dir = $db->shi_stat_confirmed) " .
   "order by list_order, lastname, firstname";

  $bf = array("<b>", "");
  $bf_ = array("</b>", "");

  for ($i = 0; $i < 2; $i++)
  {
    $s = $db->query($q[$i]);

    foreach ($s as $e)
    {
      echo "<tr><td>" . $bf[$i] . $e[firstname] . " " . $e[lastname] . $bf_[$i] . "</td><td>" . $e[instrument] . "</td><td>" . format_phone($e[phone1]) . "</a></td></tr>";
    }
  }

  echo "</table>";
}

$query  = "SELECT name, semester, year, info_dir from project where id = $_REQUEST[id_project]";
$stmt = $db->query($query);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$project_info = str_replace("\n", "<br>\n", $row[info_dir]);

echo "
    <h1>Regiplan for $row[name], $row[semester]-$row[year]</h1>
Oppgaven til regikomit&eacute;en best&aring;r av &aring; klargj&oslash;re lokalet f&oslash;r pr&oslash;ver og konserter. Til vanlige orkesterpr&oslash;ver gj&oslash;r 5 stykker jobben greit p&aring; 10 minutter. Mengden regioppgaver i selve konsertlokalet vil variere s&aring; her vil behovet for regikomit&eacute;ens innsats blir vurdert fra prosjekt til prosjekt. 
<p>
<b>Regikomit&eacute;en</b> er ansvarlig for &aring;:
<ul>
    <li>Rydde bort bord/stoler til vanlige &oslash;velser med oppm&oslash;te 20 minutter f&oslash;r pr&oslash;ven begynner
    <li>Rigge opp orkesteret til pr&oslash;ver og konserter. Riggen skal st&aring; klar 10 minutter f&oslash;r pr&oslash;ven begynner.
    <li>Slippe folk inn hovedd&oslash;ren fra 15 min f&oslash;r pr&oslash;ven begynner.
    <li>Delta ved transport av utstyr mellom HiOA og konsertlokalet.
</ul>
<b>Alle</b> medlemmer er ansvarlig for &aring;:
<ul>
    <li>V&aelig;re med &aring; rigge ned etter pr&oslash;vene. Dette skal alle som kan bidra med. (Det holder hvis alle tar et bord/en stol)
    <li>Sl&aring; opp notestativer. Dette er den enkeltes ansvar. (Men det er hyggelig at noen gj&oslash;r det likevel...)
    <li>Rigge opp spesialstoler (horn, fagott, cello, kontrabass osv.). Dette m&aring; de som trenger det ta ansvar for selv.
</ul>

Alle medlemmer som ikke har utvidede funksjoner (gruppeledere, styreverv, osv) eller av andre personlige grunner ikke kan bidra med dette, vil m&aring;tte ta del i regioppgaver (Ref. vedtektene &sect;4.2f).

Regiss&oslash;ren vil ikke ha beskjed dersom du ikke har anledning til &aring; komme n&aring;r hele gruppen er satt opp som ansvarlig. Ved for mye skulk kan du risikere &aring; m&aring;tte ta dette igjen p&aring; seinere prosjekter.
<p>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Dato</th>
      <th bgcolor=#A6CAF0>Tid</th>
      <th bgcolor=#A6CAF0>Sted</th>
      <th bgcolor=#A6CAF0>Ansvarlig</th>
      <th bgcolor=#A6CAF0>Hva skjer?</th>
    </tr>";


$query  = "SELECT plan.id as id, date, time, id_location, location.name as lname, project.name as pname, location.url as url, id_responsible, " .
    "plan.responsible as responsible, firstname, lastname, plan.comment as comment, " .
    "event_type " .
    "FROM person, project, plan, location " .
    "where id_location = location.id " .
    "and id_project = project.id " .
    "and id_responsible = person.id " .
    "and plan.id_project like '$_REQUEST[id_project]' " .
    "order by date,tsort,time";
$stmt = $db->query($query);

$last_date = 0;
$last_time = "";

foreach ($stmt as $row)
{
    echo "<tr><td nowrap>";
    if ($row[date] != $last_date)
      echo strftime('%a %e.%b', $row[date]);
    echo "</td><td>";
    if ($row[date] != $last_date || $row[time] != $last_time)
      echo $row[time];

    $last_date = $row[date];
    $last_time = $row[time];

    echo "</td><td>";
    if (strlen($row[url]) > 0)
      echo "<a href=\"{$row[url]}\">{$row[lname]}</a>";
    else 
      echo $row[lname];
    if ($row[event_Type] == $db->plan_evt_direction)
       echo "</td><td nowrap><b>{$row[firstname]} {$row[lastname]}</b><br>";
    resources_list($row[id]);
    echo $row[responsible];
    echo "</td>";
    echo "<td>";
    echo str_replace("\n", "<br>\n", $row[comment]);
    echo "</td>" .
        "</tr>";
} 

echo "</table>
     <h2>Regikomit&eacute;</h2>";
shift_list();
echo "<h2>Generell prosjekt info</h2>
    $project_info";


require 'framework_end.php';