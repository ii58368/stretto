<?php

include 'framework.php';

$id_project = is_null(request('id_project')) ? "%" : request('id_project');

function select_tsort($selected)
{
   echo "<select name=tsort title=\"Sorteringsrekkefølge dersom dette en av flere aktiviteter på samme dato\">";
   for ($i = 0; $i < 8; $i++)
   {
      echo "<option value=$i";
      if ($i == $selected)
         echo " selected";
      echo ">" . $i;
   }
   echo "</select>";
}

function select_location($selected)
{
   global $db;

   echo "<select name=id_location title=\"Velg prøvested fra listen over registrerte lokaler\">";

   $q = "SELECT id, name FROM location order by name";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         echo " selected";
      echo ">" . $e['name'];
   }
   echo "</select>";
}

function select_project($selected)
{
   global $db;
   global $season;

   echo "<select name=id_project title=\"Velg hvilket prosjekt prøven gjelder for...\">";

   $year = date("Y");
   $q = "SELECT id, name, semester, year, orchestration FROM project " .
           "where year >= " . $season->year() . " " .
           "or id = '$selected' " .
           "order by year, semester DESC";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         echo " selected";
      echo ">" . $e['name'] . " (" . $e['semester'], $e['year'] . ")";
      if ($e['orchestration'] == $db->prj_orch_reduced)
         echo '*';
   }
   echo "</select>";
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

if ($access->auth(AUTH::PLAN_RW))
   echo "<a href=\"$php_self?id_project=$id_project&_action=new&id_location=" . request('id_location') . "\" title=\"Registrer ny prøve...\"><img src=\"images/new_inc.gif\" border=0 hspace=5 vspace=5></a>\n";
echo "<a href=\"plan_pdf.php\" title=\"PDF versjon...\"><img src=images/pdf.jpeg height=22 border=0 hspace=5 vspace=5></a>\n";

echo "
    <form action='$php_self' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::PLAN_RW))
   echo "
      <th>Edit</th>";
echo "
      <th>Dato</th>
      <th>Prøvetid</th>
      <th>Lokale</th>
      <th>Prosjekt</th>
      <th>Merknad</th>
    </tr>";

$hlp_date = "Format: <dato>. <mnd> [<år>] Merk: Måned på engelsk. Eksempel: 12. dec";

if ($action == 'new')
{
   echo "<tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=submit value=ok title=\"Registrer...\"></td>
    <td><input type=date size=10 name=date title=\"$hlp_date\"></td>
    <td nowrap>";
   select_tsort(null);
   echo "<input type=text size=11 name=time value=\"18:30-21:30\" title=\"Prøvetid\"></td>
    <td>";
   select_location(request('id_location'));
   echo "<br><input type=text size=22 name=location title=\"Prøvested som ikke finnes på listen som typisk bare skal benyttes en gang\">";
   echo "</td>
    <td>";
   select_project($id_project);
   echo "
  </td>
  <td><textarea cols=50 rows=6 wrap=virtual name=comment title=\"Fritekst\">Tutti</textarea></td>
  </tr>";
}

if ($action == 'update' && $access->auth(AUTH::PLAN_RW))
{
   if (($ts = strtotime(request('date'))) == false)
      echo "<font color=red>Illegal time format: " . request('date') . "</font>";
   else
   {
      if ($no == NULL)
      {
         $query2 = "select id_person from project where id = $id_project";
         $stmt = $db->query($query2);
         $row = $stmt->fetch(PDO::FETCH_ASSOC);

         $query = "insert into plan (date, tsort, time, id_location, location, id_project, " .
                 "id_responsible, comment, event_type) " .
                 "values ($ts, " . request('tsort') . ", " . $db->qpost('time') . ", " .
                 request('id_location') . ", " . $db->qpost('location') . ", $id_project, " . $row['id_person'] . ", " .
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
                    "id_project = $id_project," .
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
$query .= "or project.status = $db->prj_stat_tentative "
       . "or project.status = $db->prj_stat_internal) "
       . "order by date,tsort,time";

$stmt = $db->query($query);

$last_date = 0;
$last_time = '';

foreach ($stmt as $row)
{
   if ($row['id'] != $no || $action != 'view')
   {
      $gfont = '<font>';
      if ($row['pstatus'] == $db->prj_stat_tentative)
         $gfont = "<font color=lightgrey>";
      if ($row['pstatus'] == $db->prj_stat_draft)
         $gfont = "<font color=lightgrey>";

      if ($access->auth(AUTH::PLAN_RW))
         echo "<tr>
        <td><center>
            <a href=\"$php_self?_action=view&_no=".$row['id']."&id_project=$id_project\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for å editere...\"></a>
             </center></td>";
      echo "<td>$gfont";
      if ($access->auth(AUTH::PLAN_RW) || $row['date'] != $last_date)
        echo strftime('%a %e.%b %y', $row['date']);
      echo "</font></td><td>$gfont";
      if ($access->auth(AUTH::PLAN_RW) || $row['date'] != $last_date || $row['time'] != $last_time)
         echo $row['time'];
      echo "</font></td><td>$gfont";
      if (strlen($row['url']) > 0)
         echo "<a href=\"".$row['url']."\">".$row['lname']."</a>";
      else
         echo $row['lname'];
      echo " ".$row['location'];
      echo "</font></td><td>$gfont<a href=\"prjInfo.php?id=".$row['id_project']."\">".$row['pname']."</a>";
      if ($row['orchestration'] == $db->prj_orch_reduced)
         echo '*';
      echo "</font></td><td>$gfont";
      echo str_replace("\n", "<br>\n", $row['comment']);
      echo "</font></td>" .
      "</tr>";
   }
   else
   {
      echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_no value='$no'>
    <td nowrap><input type=submit value=ok>
    <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette" . strftime('%e.%m.%y', $row['date']) . "?');\"></td>
    <td><input type=date size=10 name=date value=\"" . date('Y-m-d', $row['date']) . "\" title=\"$hlp_date\"></td>
    <td nowrap>";
      select_tsort($row['tsort']);
      echo "<input type=text size=11 name=time value=\"".$row['time']."\" title=\"Prøvetid\"></td>
    <td>";
      select_location($row['id_location']);
      echo "<br><input type=text size=22 name=location value=\"".$row['location']."\" title=\"Prøvested som ikke finnes på listen som typisk bare skal benyttes en gang\">";
      echo "</td>
    <td>";
      select_project($row['id_project']);
      echo "</td>
    <td><textarea cols=50 rows=6 wrap=virtual name=comment title=\"Fritekst\">".$row['comment']."</textarea></td>
    </tr>";
   }
   $last_date = $row['date'];
   $last_time = $row['time'];
}

echo "</table>
</form>";
