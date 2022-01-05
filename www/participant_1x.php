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
    Kolonnen Tutti viser om prosjektet er et tuttiprosjekt 
    for alle eller om det er et prosjekt med redusert besetning. 

    Under status ser du status for uttaket, 
    om styret har vedtatt hvem som skal være med etc. 
<p>";

$tb = new TABLE('border=1');

$tb->th("Prosjekt");
$tb->th("Sem");
$tb->th("Delt");
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
        "and (status = $db->prj_stat_real " .
        "or status = $db->prj_stat_tentative " .
        "or status = $db->prj_stat_postponed) " .
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
   $tb->td($cell, "style=\"vertical-align:middle; text-align:center $bgcolor\"");
   $tb->td($db->prj_stat[$row['status']]);
   $deadline = $request ? strftime('%a %e.%b %y', $row['deadline']) : '';
   if ($row['orchestration'] == $db->prj_type_tutti)
   {
      $tb->td($access->hlink($request, "participant_11.php?id_project=" . $row['id'] . "&id_person=" . $pers['id'], $deadline, "title=\"Klikk for å søke permisjon...\""));
   }
   if ($row['orchestration'] == $db->prj_type_reduced || $row['orchestration'] == $db->prj_type_social)
   {
      $tb->td($access->hlink($request, "participant_11.php?id_project=" . $row['id'] . "&id_person=" . $pers['id'], $deadline, "title=\"Klikk for påmelding...\""));
   }
   if ($row['orchestration'] == $db->prj_type_primavista)
   {
      $tb->td();
   }
   $tb->td($db->prj_type[$row['orchestration']]);
}
