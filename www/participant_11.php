<?php

require 'framework.php';
include_once 'participant_status.php';

$id_person = (is_null(request('id_person'))) ? $whoami->id() : request('id_person');

if (!$access->auth(AUTH::RES))
{
   if ($id_person != $whoami->id())
   {
      echo "<h1>Permission denied</h1>";
      exit(0);
   }
}

$query = "select person.id as id, "
        . "firstname, lastname, instrument, "
        . "instruments.id as id_instruments"
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
   $stat_self = $db->par_stat_void;
   $comment_self = "''";
   
   if (is_null(request('del')))  
   {
      $stat_self = request('stat_self');
      $comment_self = $db->qpost('comment_self');
   }
   
   $query = "update participant set " .
              "stat_self = $stat_self, " .
              "ts_self = $ts, " .
              "comment_self = $comment_self " .
              "where id_person = $id_person " .
              "and id_project = " . request('id_project');
   $db->query($query);
}

$query = "select *"
        . " from participant, instruments"
        . " where participant.id_instruments = instruments.id "
        . " and id_person=$id_person"
        . " and id_project=" . request('id_project');
$stmt = $db->query($query);
if ($stmt->rowCount() > 0)
   $part = $stmt->fetch(PDO::FETCH_ASSOC);

echo "
    <h1>" . $prj['name'] . " " . $prj['semester'] . "-" . $prj['year'] . "</h1>\n";
echo str_replace("\n", "<br>\n", $prj['info']) . "\n";
echo "<h2>Spilleplan</h2>
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
        "and plan.id_project = " . request('id_project') . " " .
        "and plan.event_type = $db->plan_evt_rehearsal " .
        "order by date,tsort,time";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   echo "<tr>
       <td>" . strftime('%a %e.%b %y', $row['date']) . "</td>" .
   "<td>" . $row['time'] . "</td><td>";
   if (strlen($row['url']) > 0)
      echo "<a href=\"" . $row['url'] . "\">" . $row['lname'] . "</a>";
   else
      echo $row['lname'];
   echo $row['location'];
   echo "</td><td>";
   echo str_replace("\n", "<br>\n", $row['comment']);
   echo "</td>" .
   "</tr>\n";
}
echo "</table><p>\n";

$reg_header = ($prj['orchestration'] == $db->prj_orch_tutti) ? "Permisjonssøknad" : "Påmelding";
echo "<h2>$reg_header</h2>";
if ($prj['orchestration'] == $db->prj_orch_tutti)
   echo "Dette er et tuttiprosjekt. Dersom du ikke søker om permisjon vil du automatisk bli påmeldt når permisjonsfristen går ut.<p>\n";
echo "<form action=$php_self method=post>
   <input type=hidden name=_action value=update>
   <input type=hidden name=id_person value=$id_person>
   <input type=hidden name=id_project value=" . request('id_project') . ">
   <table id=\"no_border\">
  <tr>
  <td>Navn:</td><td>" . $pers['firstname'] . " " . $pers['lastname'] . "</td>
  </tr>
  <tr>
  <td>Instrument:</td><td>";
echo (isset($part)) ? $part['instrument'] : $pers['instrument'];
echo "</td>
  </tr>
  <tr>
  <td>";
echo ($prj['orchestration'] == $db->prj_orch_tutti) ? "Permisjonsfrist:" : "Påmeldingsfrist:";
echo "</td><td>";
echo ($prj['deadline'] < time()) ? "<font color=red>" . strftime('%a %e.%b %y', $prj['deadline']) . "</font>" :
        strftime('%a %e.%b %y', $prj['deadline']);
echo "</td></tr>\n";
echo "<tr><td>Registrert:</td><td>";
if (isset($part) && $part['ts_self'] != 0)
   echo (is_null(request('stat_self'))) ? strftime('%a %e.%b %Y', $part['ts_self']) : "<font color=green>" . strftime('%a %e.%b %Y', $part['ts_self']) . "</font> (Kommentar kan endres på frem til dato for permisjonsfrist)";
echo "</td></tr>\n";
if ($prj['deadline'] > time() && $pers['id'] == $whoami->id())
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
   echo "<tr><td>Kommentar:</td><td><textarea title=\"Registrer her eventuell tilleggsinformasjon...\"cols=30 rows=5 wrap=virtual name=comment_self>" . $part['comment_self'] . "</textarea></td></tr>\n";
   echo "<tr><td></td><td><input type=submit value=Registrer title=\"Lagre tilbakemelding...\">";
   echo "<input type=submit name=del value=Slett title=\"Slett tilbakemelding...\" onClick=\"return confirm('Sikkert at du vil slette?');\"></td></tr>";
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

echo "<h2>Deltakerstatus</h2>";

$lstatus = on_leave($id_person, $prj['semester'], $prj['year']);

if ($lstatus == $db->lea_stat_registered)
{
   echo "<img border=0 src=\"images/yellball.gif\">";
   echo "Du har søkt om langtidspermisjon i samme semester som prosjektet pågår.<br>\n";
}
if ($lstatus == $db->lea_stat_granted)
{
   echo "<img border=0 src=\"images/ball_red.gif\">";
   echo "Du har fått innvilget langtidspermisjon i samme semester som prosjektet pågår.<br>\n";
}
if ($lstatus == $db->lea_stat_rejected)
{
   echo "<img border=0 src=\"images/ball_pin.gif\">";
   echo "Du har fått avslått søknad om langtidspermisjon i samme semester som prosjektet pågår.<br>\n";
}

if ($part['stat_inv'] == $db->par_stat_yes)
{
   if ($prj['orchestration'] == $db->prj_orch_tutti)
   {
      if ($part['stat_reg'] != $db->par_stat_void)
      {
         echo "<img border=0 src=\"images/part_stat_" . $part['stat_reg'] . ".gif\">";
         echo "<b>" . strftime('%e.%m', $part['ts_reg']) . " Sekretær:</b> ";
         if ($part['stat_reg'] == $db->par_stat_no)
            echo ": Permisjonssøknad mottatt: " . $part['comment_reg'];
         if ($part['stat_reg'] == $db->par_stat_tentative)
            echo ": Tentativt: " . $part['comment_reg'];
         if ($part['stat_reg'] == $db->par_stat_can)
            echo ": Kan være med hvis behov: " . $part['comment_reg'];
         if ($part['stat_reg'] == $db->par_stat_yes)
            echo ": Vil gjærne være med på prosjektet: " . $part['comment_reg'];
         echo "<br>\n";
      }

      echo "<img border=0 src=\"images/part_stat_" . $part['stat_final'] . ".gif\">";
      echo "<b>";
      if ($part['stat_final'] != $db->par_stat_void)
         echo strftime('%e.%m', $part['ts_final']);
      echo " Styret:</b> ";
      if ($part['stat_final'] == $db->par_stat_void)
         echo "Endelig besetning vil bli bestemt når permisjonsfristen går ut.<br>\n";
      if ($part['stat_final'] == $db->par_stat_no)
         echo "Du har fått innvilget permisjon til dette prosjektet.<br>\n";
      if ($part['stat_final'] == $db->par_stat_yes)
         echo ": Du er med på dette prosjektet.<br>\n";
   }

   if ($prj['orchestration'] == $db->prj_orch_reduced)
   {
      if ($part['stat_reg'] != $db->par_stat_void)
      {
         echo "<img border=0 src=\"images/part_stat_" . $part['stat_reg'] . ".gif\">";
         echo "<b>" . strftime('%e.%m', $part['ts_reg']) . " Sekretær:</b> ";
         if ($part['stat_reg'] == $db->par_stat_no)
            echo ": Kan ikke være med: " . $part['comment_reg'];
         if ($part['stat_reg'] == $db->par_stat_tentative)
            echo ": Tentativt: " . $part['comment_reg'];
         if ($part['stat_reg'] == $db->par_stat_can)
            echo ": Kan være med hvis behov: " . $part['comment_reg'];
         if ($part['stat_reg'] == $db->par_stat_yes)
            echo ": Vil gjerne være med på prosjektet: " . $part['comment_reg'];
         echo "<br>\n";
      }

      echo "<img border=0 src=\"images/part_stat_" . $part['stat_final'] . ".gif\">";
      echo "<b>";
      if ($part['stat_final'] != $db->par_stat_void)
         echo strftime('%e.%m', $part['ts_final']);
      echo " Styret:</b> ";
      if ($part['stat_final'] == $db->par_stat_void)
         echo "Orkesteruttaket er ikke ferdigbehandlet.<br>\n";
      if ($part['stat_final'] == $db->par_stat_no)
         echo ": Du er ikke tatt ut for å være med på dette prosjektet.<br>\n";
      if ($part['stat_final'] == $db->par_stat_yes)
         echo ": Du er tatt ut til å være med på dette prosjektet.<br>\n";
   }
}
else
{
   echo "<img border=0 src=\"images/part_stat_" . $part['stat_inv'] . ".gif\">";
   echo "<b>Styret:</b> ";
   echo "Du er ikke en del av besetningen på dette prosjektet.<br>\n";
}

if (!is_null($blink))
   echo "Tilbakemeldingen din er under behandling i styret.<br>\n";
