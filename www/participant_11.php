<?php

require 'framework.php';
include_once 'participant_status.php';

$id_person = (is_null(request('id_person'))) ? $whoami->id() : request('id_person');
$id_project = request('id_project');

function select_reason($selected)
{
   global $db;

   $str = "<select name=status title=\"Type fravær...\">";
   $str .= "<option value=null>Uavklart</option>\n";

   for ($i = 0; $i < count($db->abs_stat); $i++)
   {
      $str .= "<option value=$i";
      if (!is_null($selected) && $selected == $i)
         $str .= " selected";
      $str .= ">" . $db->abs_stat[$i] . "</option>\n";
   }

   $str .= "</select>";

   return $str;
}

function get_lnk($e)
{
   global $db;
   global $action;
   global $php_self;
   global $id_person;
   global $id_project;

   $status = $db->abs_stat_other;
   $reason = 'Uregistrert';
   if (!is_null($e['status']))
   {
      $status = $e['status'];
      $reason = $e['reason'];
   }
   $img = "<img src=\"images/abs_stat_$status.gif\" title=\"" . $db->abs_stat[$status] . ": $reason\">";

   $one_day = 60*60*24;
   if ($e['date'] + $one_day > time())
      $lnk = "<a href=\"$php_self?_action=edit&id_person=$id_person&id_project=$id_project&id_plan=" . $e['id_plan'] . "\">$img</a>";
   else
      $lnk = $img;
   
   if ($action == 'edit' && $e['id_plan'] == request('id_plan'))
   {
      $lnk = "<form method=post action=$php_self>\n";
      $lnk .= "<input type=hidden name=id_person value=$id_person>\n";
      $lnk .= "<input type=hidden name=id_project value=$id_project>\n";
      $lnk .= "<input type=hidden name=id_plan value=" . $e['id_plan'] . ">\n";
      $lnk .= "<input type=hidden name=_action value=abs_update>\n";
      $lnk .= "<input type=submit value=Lagre title=Lagre><br>\n";
      $lnk .= select_reason($e['status']) . "<br>";
      $lnk .= "<input type=text name=reason value=\"" . $e['reason'] . "\" size=13 title=\"Eventuell kommentar...\">\n";
      $lnk .= "</form>\n";
   }
   return $lnk;
}

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
        . " where id=$id_project";
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
           "and id_project = $id_project";
   $db->query($query);
}

if ($action == "abs_update")
{
   $query = "replace into absence "
           . "(id_person, id_plan, status, comment) "
           . "values "
           . "($id_person, " . request('id_plan') . ", " . request('status') . ", " . $db->qpost('reason') . ")";
   $db->query($query);
}

$query = "select *"
        . " from participant, instruments"
        . " where participant.id_instruments = instruments.id "
        . " and id_person=$id_person"
        . " and id_project=$id_project";
$stmt = $db->query($query);
if ($stmt->rowCount() > 0)
   $part = $stmt->fetch(PDO::FETCH_ASSOC);

$project_name = $prj['name'] . " " . $prj['semester'] . "-" . $prj['year'];
echo "<h1>$project_name</h1>\n";
echo str_replace("\n", "<br>\n", $prj['info']) . "\n";
echo "<h2>Spilleplan</h2>\n";

$tb = new TABLE('id=no_border');

$tb->th('S');
$tb->th('Dato');
$tb->th('Prøvetid');
$tb->th('Lokale');
$tb->th('Merknad');
$tb->tr();

$query = "SELECT plan.date as date, plan.time as time, "
        . "plan.location as location, location.name as lname, "
        . "plan.id as id_plan, "
        . "location.url as url, "
        . "plan.comment as comment, "
        . "absence.status as status, "
        . "absence.comment as reason "
        . "FROM project, location, plan "
        . "left join absence "
        . "on absence.id_person = $id_person "
        . "and absence.id_plan = plan.id "
        . "where id_location = location.id "
        . "and id_project = project.id "
        . "and plan.id_project = $id_project "
        . "and plan.event_type = $db->plan_evt_rehearsal "
        . "order by date,tsort,time";


$stmt = $db->query($query);

foreach ($stmt as $row)
{
   $tb->tr();
   $tb->td(($part['stat_final'] == $db->par_stat_yes) ? get_lnk($row) : '');
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
$form = new FORM();
echo "<input type=hidden name=_action value=update>
   <input type=hidden name=id_person value=$id_person>
   <input type=hidden name=id_project value=$id_project>\n";

$tb = new TABLE('id=no_border');

$tb->td('Navn:');
$tb->td($pers['firstname'] . " " . $pers['lastname']);
$tb->tr();
$tb->td('Instrument:');
$tb->td(isset($part) ? $part['instrument'] : $pers['instrument']);
$tb->tr();
$tb->td(($isTutti) ? "Permisjonsfrist:" : "Påmeldingsfrist:");
$tss = strftime('%a %e.%b %Y', $prj['deadline']);
$tb->td($prj['deadline'] < time() ? "<font color=red>$tss</font>" : $tss);
$tb->tr();
$tb->td('Registrert:');
$tss = (isset($part) && $part['ts_self'] != 0) ? strftime('%a %e.%b %Y', $part['ts_self']) : '';
$tb->td(is_null(request('stat_self')) ? $tss : "<font color=green>$tss</font> (Opplysningene kan endres til fristen går ut)");
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
   $error = (isset($part) && $part['stat_self'] == $db->par_stat_no && $isTutti && strlen($part['comment_self']) < 4) ? "<br><font color=red>Permisjonsbegrunnelse mangler!</font>" : '';
   $description = $isTutti ? "Begrunnelse:$error" : "Kommentar:";
   $placeholder = $isTutti ? "Angi begrunnelse for å søke permisjon. Sensitive opplysninger? Send mail direkte til gruppeleder." : "Oppgi eventuell tilleggsinformasjon som du ønsker skal bli tatt med i vurderingen";
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
unset($form);

$query = "select firstname, lastname, email "
        . "from person "
        . "where id = "
        . "(select id_person from person, instruments, groups "
        . "where person.id_instruments = instruments.id "
        . "and instruments.id_groups = groups.id "
        . "and person.id = " . $whoami->id() . ")";
$stmt = $db->query($query);
$glead = $stmt->fetch(PDO::FETCH_ASSOC);

If ($prj['deadline'] < time())
{
   echo "Har det skjedd endringer som påvirker deltagelsen din på dette prosjektet, send mail til gruppelederen din ";
   echo "<a href=\"mailto:?to=" . $glead['email'] . "&subject=OSO: $project_name\">" . $glead['firstname'] . " " . $glead['lastname'] . "</a> ";
   echo "som vil videreformidle en innstilling om dette til styret.<br>\n";
}
echo "Dersom du må søke permisjon for ett helt semester eller mer, send mail til ";
echo "<a href=\"mailto:?to=" . $glead['email'] . "&subject=OSO permisjonssøknad\">gruppeleder</a>. ";

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

$help = "Klikk for å registrere en ny tilbakemelding.\nTilbakemeldingen kan endres frem til det blir markert som lest av styret";
$button = "<img src=images/cross_re.gif title=\"$help\">";
$link = $access->auth(AUTH::FEEDBACK) ? "<a href=\"feedbackReg.php?_action=new&id_project=$id_project\">$button</a> " : "";

echo "<h2>Tilbakemelding $link</h2>";

$query = "select id, ts, status, comment "
        . "from feedback "
        . "where id_person = $id_person "
        . "and id_project = $id_project "
        . "order by status, ts desc";
$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($row['status'] == $db->fbk_stat_new)
   {
      $help = "Klikk for å editere.\nTilbakemeldingen kan endres frem til det blir markert som lest av styret";
      $button = "<img src=images/cross_re.gif title=\"$help\">";
      echo "<a href=\"feedbackReg.php?_action=view&_no=" . $row['id'] . "&id_project=$id_project\">$button</a> ";
   }
   echo "<b>" . strftime('%e.%b %Y', $row['ts']) . "</b>\n";

   echo "<br>";
   $comment = str_replace("\n", "<br>\n", $row['comment']);
   $comment = replace_links($comment);
   echo $comment;

   echo "<p>";
}

