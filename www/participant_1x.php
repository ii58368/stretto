<?php

require 'framework.php';
include_once 'participant_status.php';

$person_id = $whoami->id();

if ($access->auth(AUTH::RES) && request('id') != null)
   $person_id = request('id');

if (is_null($sort))
   $sort = 'year,semester DESC,id';

$query = "select person.id as id, firstname, lastname, instrument"
        . " from person, instruments"
        . " where person.id = $person_id "
        . " and person.id_instruments = instruments.id";
$stmt = $db->query($query);
$pers = $stmt->fetch(PDO::FETCH_ASSOC);

echo "
    <h1>Prosjekter for " . $pers['firstname'] . " " . $pers['lastname'] . " (" . $pers['instrument'] . ")</h1>
    Dette er oversikten over alle orkesterets prosjekter og hvilke av disse du skal være med på. 
    Merk at prosjekter med redusert besetning må du aktivt melde deg på for å bli med.
    Kolonnen <i>Frist</i> viser fristen for å melde seg på eller søke permisjon. 
    Klikk på datoen for å melde deg på eller søke permisjon.
    Blinkende status-lamper viser hva deltagelsen din blir hvis du ikke foretar deg noe innen fristen.
    Prosjekter uten frist.-dato krever ingen av- eller påmelding.
<p>";

$tb = new TABLE('border=1');

$tb->th("Prosjekt");
$tb->th("Sem");
$tb->th("Status");
$tb->th("Frist");
$tb->th("Type");

$qperiod = "(project.year > " . $season->year() . " " .
        "  or (project.year = " . $season->year() . " ";
if ($season->semester() == 'H')
   $qperiod .= "and project.semester = '" . $season->semester() . "' ";
$qperiod .= "))";

$query = "SELECT project.id as id, name, semester, year, status, " .
        "deadline, orchestration " .
        "FROM project " .
        "where $qperiod " .
        "and not status = $db->prj_stat_draft " .
        "order by $sort";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   list($status, $blink) = participant_status($person_id, $row['id']);

   $lstatus = on_leave($pers['id'], $row['semester'], $row['year']);
   $bgcolor = '';
   if ($lstatus == $db->lea_stat_registered)
      $bgcolor = ";background-color:yellow";
   if ($lstatus == $db->lea_stat_granted)
      $bgcolor = ";background-color:red";
   if ($lstatus == $db->lea_stat_rejected)
      $bgcolor = ";background-color:pink";

   $tb->tr();

   $request = $row['status'] == $db->prj_stat_real &&
           $status != $db->par_stat_void &&
           $lstatus != $db->lea_stat_granted;

   $tb->td($access->hlink2("prjInfo.php?id=" . $row['id'], $row['name'], "title=\"Klikk for å se prosjektinfo...\""));
   $tb->td($row['semester'] . " " . $row['year']);

   $tstat = $db->par_stat[$status];
   if (!is_null($blink) && strtotime('today') > $row['deadline'])
      $tstat .= "\n(tilbakemeldingen er under behandling i styret...)";
   if (!is_null($blink) && strtotime('today') <= $row['deadline'])
   {
      if ($row['orchestration'] == $db->prj_type_tutti)
         $tstat .= "\nTutti. (Du må søke permisjon hvis du ikke kan være med på dette prosjektet...)";
      else
         $tstat .= "\n(Påmeldingsprosjekt, du må melde deg på for å bli med...)";
   }
   $cell = '';
   if ($row['status'] == $db->prj_stat_real)
      $cell = "<img src=\"images/part_stat_$status$blink.gif\" border=0 title=\"$tstat\">";
   if ($row['status'] == $db->prj_stat_postponed || $row['status'] == $db->prj_stat_canceled)
      $cell = $db->prj_stat[$row['status']];
   $tb->td($cell, "style=\"vertical-align:middle; text-align:center $bgcolor\"");
   $deadline = $request ? strftime('%e.%b %Y', $row['deadline']) : '';
   
   $cell = '';
   $pid = ($pers['id'] == $whoami->id()) ? '' : "&id_person=" . $pers['id'];
   
   if ($row['orchestration'] == $db->prj_type_tutti)
   {
      $cell = $access->hlink($request, "participant_11.php?id_project=" . $row['id'] . $pid, $deadline, "title=\"Klikk for å søke permisjon...\"");
   }
   if ($row['orchestration'] == $db->prj_type_reduced || $row['orchestration'] == $db->prj_type_social)
   {
      $cell = $access->hlink($request, "participant_11.php?id_project=" . $row['id'] . $pid, $deadline, "title=\"Klikk for påmelding...\"");
   }
   $tb->td($cell);
   
   $tb->td($db->prj_type[$row['orchestration']]);
}
