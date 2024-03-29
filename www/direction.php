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
    echo $e['firstname'] . " " . $e['lastname'] . " (" . $e['instrument'] . ")<br>";
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
      <th>Navn</th>
      <th>Instrument</th>
      <th>Mobil</th>
    </tr>";


  $q[0] = "SELECT firstname, lastname, instrument, phone1 " .
   "FROM person, instruments, project " .
   "where person.id_instruments = instruments.id " .
   "and project.id = " . request('id_project') . " " .
   "and person.id = project.id_person";

  $q[1] = "SELECT firstname, lastname, instrument, phone1 " .
   "FROM person, instruments, participant " .
   "where person.id = participant.id_person " .
   "and person.id_instruments = instruments.id " .
   "and participant.id_project = " . request('id_project') . " " .
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
      echo "<tr><td>" . $bf[$i] . $e['firstname'] . " " . $e['lastname'] . $bf_[$i] . "</td><td>" . $e['instrument'] . "</td><td>" . format_phone($e['phone1']) . "</a></td></tr>";
    }
  }

  echo "</table>";
}

function lineup()
{
   global $db;

   echo "
    <table border=1>
    <tr>
      <th>Gruppe</th>
      <th>Musikere</th>
      <th>Stativer</th>
    </tr>";

   $qc ="(select count(*) "
           . "from instr_grp as igrp, instruments, participant "
           . "where participant.stat_final = " . $db->par_stat_yes . " "
           . "and participant.stat_inv = " . $db->par_stat_yes . " "
           . "and participant.id_project = " . request('id_project') . " "
           . "and participant.id_instruments = instruments.id "
           . "and instruments.id_instr_grp = igrp.id "
           . "and igrp.id = instr_grp.id) as gpart "; 
          
   $q = "select name, stand, $qc from instr_grp order by id";
   
   $s = $db->query($q);

   $sstands = 0;
   $sgpart = 0;
   
   foreach ($s as $e)
   {
      $stand = (int)$e['stand'];
      $gpart = (int)$e['gpart'];
      $stands = round($gpart / $stand);
      $sstands += $stands;
      $sgpart += $gpart;
      echo "<tr><td>" . $e['name'] . "</td><td>" . $e['gpart'] . "</td><td>" . $stands . "</td></tr>";
   }
   
   echo "<tr><td>Sum</td><td>$sgpart</td><td>$sstands</td></tr>";

   echo "</table>";
}

$query  = "SELECT name, semester, year, info_dir from project where id = " . request('id_project');
$stmt = $db->query($query);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$project_info = str_replace("\n", "<br>\n", $row['info_dir']);

echo "
    <h1>Regiplan for " . $access->hlink(true, "prjInfo.php?id=" . request('id_project'), $row['name'] . ", " . $row['semester'] . "-" . $row['year']) . "</h1>
Oppgaven til regikomitéen består av å klargjøre lokalet før prøver og konserter. 
Til vanlige orkesterprøver gjør 5 stykker jobben greit på 10 minutter. 
Mengden regioppgaver i selve konsertlokalet vil variere så her vil behovet for 
regikomitéens innsats blir vurdert fra prosjekt til prosjekt. 
<p>
<b>Regikomitéen</b> er ansvarlig for å:
<ul>
    <li>Rydde bort bord/stoler til vanlige øvelser med oppmøte 20 minutter før prøven begynner
    <li>Rigge opp orkesteret til prøver og konserter. Riggen skal stå klar 10 minutter før prøven begynner.
    <li>Slippe folk inn hoveddøren fra 15 min før prøven begynner.
    <li>Delta ved transport av utstyr mellom OsloMet og konsertlokalet.
</ul>
<b>Alle</b> medlemmer er ansvarlig for å:
<ul>
    <li>Være med å rigge ned etter prøvene. Dette skal alle som kan bidra med. (Det holder hvis alle tar et bord/en stol)
    <li>Slå opp notestativer. Dette er den enkeltes ansvar. (Men det er hyggelig at noen gjør det likevel...)
    <li>Rigge opp spesialstoler (horn, fagott, cello, kontrabass osv.). Dette må de som trenger det ta ansvar for selv.
</ul>

Alle medlemmer som ikke har utvidede funksjoner (gruppeledere, styreverv, osv) eller av andre personlige grunner ikke kan bidra med dette, vil måtte ta del i regioppgaver (Ref. vedtektene &sect;4.2f).

Regissøren vil ikke ha beskjed dersom du ikke har anledning til å komme når hele gruppen er satt opp som ansvarlig. Ved for mye skulk kan du risikere å måtte ta dette igjen på seinere prosjekter.
<p>
    <table border=1>
    <tr>
      <th>Dato</th>
      <th>Tid</th>
      <th>Sted</th>
      <th>Ansvarlig</th>
      <th>Hva skjer?</th>
    </tr>";


$query  = "SELECT plan.id as id, date, time, id_location, location.name as lname, project.name as pname, location.url as url, id_responsible, " .
    "plan.responsible as responsible, firstname, lastname, plan.comment as comment, " .
    "event_type " .
    "FROM person, project, plan, location " .
    "where id_location = location.id " .
    "and id_project = project.id " .
    "and id_responsible = person.id " .
    "and plan.event_type = $db->plan_evt_direction " .
    "and plan.id_project like '" . request('id_project') . "' " .
    "order by date,tsort,time";
$stmt = $db->query($query);

$last_date = 0;
$last_time = '';

foreach ($stmt as $row)
{
    echo "<tr><td nowrap>";
    if ($row['date'] != $last_date)
      echo strftime('%a %e.%b', $row['date']);
    echo "</td><td>";
    if ($row['date'] != $last_date || $row['time'] != $last_time)
      echo $row['time'];

    $last_date = $row['date'];
    $last_time = $row['time'];

    echo "</td><td>";
    if (strlen($row['url']) > 0)
      echo "<a href=\"" . $row['url'] . "\">" . $row['lname'] . "</a>";
    else 
      echo $row['lname'];
    if ($row['event_type'] == $db->plan_evt_direction)
       echo "</td><td nowrap><b>" . $row['firstname'] . " " . $row['lastname'] . "</b><br>";
    resources_list($row['id']);
    echo $row['responsible'];
    echo "</td>";
    echo "<td>";
    echo str_replace("\n", "<br>\n", $row['comment']);
    echo "</td>" .
        "</tr>";
} 

echo "</table>
     <h2>Regikomité</h2>";
shift_list();
echo "<h2>Rigg</h2>";
lineup();
echo "<h2>Generell prosjekt info</h2>";
echo $project_info;
