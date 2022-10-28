<?php

include 'framework.php';

$id_project = is_null(request('id_project')) ? "%" : request('id_project');

function select_tsort($selected)
{
   $str = "<select name=tsort title=\"Sorteringsrekkefølge dersom dette en av flere aktiviteter på samme dato\">";
   for ($i = 0; $i < 8; $i++)
   {
      $str .= "<option value=$i";
      if ($i == $selected)
         $str .= " selected";
      $str .= ">" . $i;
   }
   $str .= "</select>";
   
   return $str;
}

function select_location($selected)
{
   global $db;

   $str = "<select name=id_location title=\"Velg prøvested fra listen over registrerte lokaler\">";

   $q = "SELECT id, name FROM location order by name";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      $str .= "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         $str .= " selected";
      $str .= ">" . $e['name'];
   }
   $str .= "</select>";
   
   return $str;
}

function select_project($selected)
{
   global $db;
   global $season;

   $str = "<select name=sid_project title=\"Velg hvilket prosjekt prøven gjelder for...\">";

   $year = date("Y");
   $q = "SELECT id, name, semester, year, orchestration FROM project " .
           "where year >= " . $season->year() . " " .
           "or id = '$selected' " .
           "order by year, semester DESC";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      $str .= "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         $str .= " selected";
      $str .= ">" . $e['name'] . " (" . $e['semester'] . $e['year'] . ")";
      if ($e['orchestration'] == $db->prj_type_reduced)
         $str .= '*';
   }
   $str .= "</select>";
   
   return $str;
}

echo "<h1>Spilleplan</h1>\n";

if ($id_project == '%')
{
   $h2 = $season->semester(1) . " " . $season->year();
}
else
{
   $s = $db->query("select name, semester, year from project where id = $id_project");
   $e = $s->fetch(PDO::FETCH_ASSOC);
   $h2 = $e['name'] . " (" . $e['semester'] . $e['year'] . ")";
}
echo "<h2>$h2</h2>\n";

$ndate = is_null(request('date')) ? 0 : request('date');
if ($access->auth(AUTH::PLAN_RW))
   echo "<a href=\"$php_self?id_project=$id_project&_action=new&date=$ndate&id_location=" . request('id_location') . "\" title=\"Registrer ny prøve...\"><img src=\"images/new_inc.gif\" border=0 hspace=5 vspace=5></a>\n";
echo "<a href=\"plan_pdf.php\" title=\"PDF versjon...\"><img src=images/pdf.jpeg height=22 border=0 hspace=5 vspace=5></a>\n";

$form = new FORM();
$tb = new TABLE('border=1');

if ($access->auth(AUTH::PLAN_RW))
   $tb->th("Edit");

$tb->th("Dato");
$tb->th("Prøvetid");
$tb->th("Lokale");
$tb->th("Prosjekt");
$tb->th("Merknad");
$tb->tr();

$hlp_date = "Format: <dato>. <mnd> [<år>] Merk: Måned på engelsk. Eksempel: 12. dec";

if ($action == 'new')
{
   $tb->td("<input type=hidden name=_action value=update>
    <input type=submit value=ok title=\"Registrer...\">", 'align=left');
   $tb->td("<input type=date size=10 name=date value=$ndate title=\"$hlp_date\">" . select_tsort(null), 'nowrap');
   $tb->td("<input type=text size=11 name=time value=\"18:30-21:30\" title=\"Prøvetid\">");
   $tb->td(select_location(request('id_location')) . "<br><input type=text size=22 name=location title=\"Prøvested som ikke finnes på listen som typisk bare skal benyttes en gang\">");
   $tb->td(select_project($id_project));
   $tb->td("<textarea cols=50 rows=6 wrap=virtual name=comment title=\"Fritekst\">Tutti</textarea>");
   $tb->tr();
}

if ($action == 'update' && $access->auth(AUTH::PLAN_RW))
{
   if (($ts = strtotime(request('date'))) == false)
      echo "<font color=red>Illegal time format: " . request('date') . "</font>";
   else
   {
      if ($no == NULL)
      {
         $query2 = "select id_person from project where id = " . request('sid_project');
         $stmt = $db->query($query2);
         $row = $stmt->fetch(PDO::FETCH_ASSOC);

         $query = "insert into plan (date, tsort, time, id_location, location, id_project, " .
                 "id_responsible, comment, event_type) " .
                 "values ($ts, " . request('tsort') . ", " . $db->qpost('time') . ", " .
                 request('id_location') . ", " . $db->qpost('location') . ", " . request('sid_project') . ", " . $row['id_person'] . ", " .
                 $db->qpost('comment') . ", " . $db->plan_evt_rehearsal . ")";
      }
      else
      {
         if ($delete != NULL)
         {
            $query = "DELETE FROM plan WHERE id = $no";
         }
         else
         {
            $query = "update plan set date = $ts," .
                    "time = ".$db->qpost('time')."," .
                    "tsort = ".request('tsort')."," .
                    "id_location = ".request('id_location')."," .
                    "location = ".$db->qpost('location')."," .
                    "id_project = ".request('sid_project')."," .
                    "comment = ".$db->qpost('comment')."," .
                    "event_type = $db->plan_evt_rehearsal " .
                    "where id = $no";
         }
         $no = NULL;
      }
      $db->query($query);
   }
}


$query = "SELECT plan.id as id, date, time, tsort, id_project, " .
        "id_location, plan.location as location, location.name as lname, " .
        "project.name as pname, location.url as url, " .
        "plan.comment as comment, orchestration, " .
        "project.status as pstatus " .
        "FROM project, plan, location " .
        "where id_location = location.id " .
        "and id_project = project.id " .
        "and plan.id_project like '$id_project' " .
        "and plan.event_type = $db->plan_evt_rehearsal ";
if ($id_project == '%')
   $query .= "and plan.date >= " . $season->ts()[0] . " " .
        "and plan.date < " . $season->ts()[1] . " ";
$query .= "and (project.status = $db->prj_stat_real ";
if ($access->auth(AUTH::PRJ_RO))
    $query .= "or project.status = $db->prj_stat_draft ";
$query .= "or project.status = $db->prj_stat_tentative) "
       . "order by date,tsort,time";

$stmt = $db->query($query);

$last_date = 0;
$last_time = '';

foreach ($stmt as $row)
{
   $tb->tr();
   
   if ($row['id'] != $no || $action != 'view')
   {
      $gfont = '<font>';
      if ($row['pstatus'] == $db->prj_stat_tentative)
         $gfont = "<font color=lightgrey>";
      if ($row['pstatus'] == $db->prj_stat_draft)
         $gfont = "<font color=lightgrey>";

      if ($access->auth(AUTH::PLAN_RW))
         $tb->td ("<a href=\"$php_self?_action=view&_no=".$row['id']."&id_project=$id_project\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for å editere...\"></a>", 'align=center');      
      $date = ($access->auth(AUTH::PLAN_RW) || $row['date'] != $last_date) ? strftime('%a %e.%b %y', $row['date']) : "";
      $tb->td($gfont . $date . "</font>");
      $time = ($access->auth(AUTH::PLAN_RW) || $row['date'] != $last_date || $row['time'] != $last_time) ? $row['time'] : "";
      $tb->td($gfont . $time . "</font>");
      $url = $access->hlink(strlen($row['url']) > 0, $row['url'], $row['lname']);
      $tb->td($gfont . $url . " " . $row['location'] . "</font>");
      $mark = $row['orchestration'] == $db->prj_type_reduced ? '*' : '';
      $tb->td($access->hlink2("prjInfo.php?id=".$row['id_project'], $row['pname'] . $mark));
      $tb->td($gfont . str_replace("\n", "<br>\n", $row['comment']) . "</fonnt>");
   }
   else
   {
      $tb->td("
    <input type=hidden name=_action value=update>
    <input type=hidden name=_no value='$no'>
    <input type=submit value=ok>
    <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette" . strftime('%e.%m.%y', $row['date']) . "?');\">", 'nowrap');
      $tb->td("<input type=date size=10 name=date value=\"" . date('Y-m-d', $row['date']) . "\" title=\"$hlp_date\">" . select_tsort($row['tsort']), 'nowrap');
      $tb->td("<input type=text size=11 name=time value=\"".$row['time']."\" title=\"Prøvetid\">");
      $tb->td(select_location($row['id_location'])
        . "<br><input type=text size=22 name=location value=\"".$row['location']."\" title=\"Prøvested som ikke finnes på listen som typisk bare skal benyttes en gang\">");
      $tb->td(select_project($row['id_project']));
      $tb->td("<textarea cols=50 rows=6 wrap=virtual name=comment title=\"Fritekst\">".$row['comment']."</textarea>");
   }
   $last_date = $row['date'];
   $last_time = $row['time'];
}
