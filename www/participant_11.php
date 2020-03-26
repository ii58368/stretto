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
   $ts = 0;
   $stat_self = $db->par_stat_void;
   $comment_self = "''";

   if (is_null(request('del')))
   {
      $stat_self = request('stat_self');
      $ts = strtotime("now");
      $comment_self = $db->qpost('comment_self');
   }

   $query = "update participant set " .
           "stat_self = $stat_self, " .
           "ts_self = $ts, " .
           "comment_self = $comment_self ";
   if ($prj['status'] == $db->prj_stat_internal)
      $query .= ", stat_final = $stat_self, " .
              "ts_final = $ts ";
   $query .= "where id_person = $id_person " .
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
echo "<h2>Spilleplan</h2>\n";

$tb = new TABLE('id=no_border');

$tb->th('Dato');
$tb->th('Prøvetid');
$tb->th('Lokale');
$tb->th('Merknad');
$tb->tr();

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
   $tb->tr();
   $tb->td(strftime('%a %e.%b %y', $row['date']));
   $tb->td($row['time']);
   $lname = (strlen($row['url']) > 0) ? "<a href=\"" . $row['url'] . "\">" . $row['lname'] . "</a>" : $row['lname'];
   $tb->td($lname . ' ' . $row['location']);
   $tb->td(str_replace("\n", "<br>\n", $row['comment']));
}
unset($tb);
echo "<p>\n";

$isTutti = ($prj['orchestration'] == $db->prj_orch_tutti);
$reg_header = ($isTutti) ? "Permisjonssøknad" : "Påmelding";
echo "<h2>$reg_header</h2>";
if ($isTutti)
   echo "Dette er et tuttiprosjekt. Dersom du ikke søker om permisjon vil du automatisk bli påmeldt når permisjonsfristen går ut.<p>\n";
echo "<form action=$php_self method=post>
   <input type=hidden name=_action value=update>
   <input type=hidden name=id_person value=$id_person>
   <input type=hidden name=id_project value=" . request('id_project') . ">\n";

$tb = new TABLE('id=no_border');

$tb->td('Navn:');
$tb->td($pers['firstname'] . " " . $pers['lastname']);
$tb->tr();
$tb->td('Instrument:');
$tb->td(isset($part) ? $part['instrument'] : $pers['instrument']);
$tb->tr();
$tb->td(($isTutti) ? "Permisjonsfrist:" : "Påmeldingsfrist:");
$tb->td($prj['deadline'] < time()) ? "<font color=red>" . strftime('%a %e.%b %Y', $prj['deadline']) . "</font>" :
                strftime('%a %e.%b %Y', $prj['deadline']);
$tb->tr();
$tb->td('Registrert:');
if (isset($part) && $part['ts_self'] != 0)
   $tb->td(is_null(request('stat_self')) ?
                   strftime('%a %e.%b %Y', $part['ts_self']) :
                   "<font color=green>" . strftime('%a %e.%b %Y', $part['ts_self']) .
                   "</font> (Opplysningene kan endres på frem til og med dato for registreringsfrist)");
$tb->tr();
if ($prj['deadline'] >= time() && $pers['id'] == $whoami->id())
{
   $tb->td('Ønsker å være med:');
   $radio = '';
   for ($i = 0; $i < count($db->par_stat); $i++)
   {
      if ($prj['valid_par_stat'] & (1 << $i))
      {
         $radio .= "<input type=radio name=stat_self value=$i";
         if (isset($part) && $part['stat_self'] == $i)
            $radio .= " checked";
         $radio .= ">" . $db->par_stat[$i] . "<br>\n";
      }
   }
   $tb->td($radio);
   $tb->tr();
   $error = ($isTutti && strlen($part['comment_self']) < 4) ? "<br><font color=red>Permisjonsbegrunnelse mangler!</font>" : '';
   $description = $isTutti ? "Begrunnelse:$error" : "Kommentar:";
   $placeholder = $isTutti ? "Angi begrunnelse for å søke permisjon." : "Oppgi eventuell tillegsinformasjon som du ønsker skal bli tatt med i vurderingen";
   $tb->td($description);
   $tb->td("<textarea title=\"$placeholder\" placeholder=\"$placeholder\" cols=30 rows=5 wrap=virtual name=comment_self>" . $part['comment_self'] . "</textarea>");
   $tb->tr();
   $tb->td();
   $tb->td("<input type=submit value=Registrer title=\"Lagre tilbakemelding...\">"
           . "<input type=submit name=del value=Slett title=\"Slett tilbakemelding...\" onClick=\"return confirm('Sikkert at du vil slette?');\">");
}
else
{
   $tb->td("Registrert svar:");
   if (isset($part))
      $tb->td('<b>' . $db->par_stat[$part['stat_self']] . '</b>');
   $tb->tr();
   $tb->td('Kommentar:');
   if (isset($part))
      $tb->td('<b>' . str_replace("\n", "<br>\n", $part['comment_self']) . '</b>');
}
unset($tb);
echo "</form>";

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
         echo "Endelig besetning vil bli bestemt når styret har behandlet saken etter at permisjonsfristen har gått ut.<br>\n";
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
      {
         if ($prj['status'] == $db->prj_stat_internal)
            echo "Vi ser frem til å høre fra deg...";
         else
            echo "Orkesteruttaket er ikke ferdigbehandlet.<br>\n";
      }
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
