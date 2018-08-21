<?php

include 'framework.php';

$id_project = request('id_project');

function select_tsort($selected)
{
   echo "<select name=tsort title=\"Sorteringsrekkef&oslash;lge dersom dette en av flere aktiviteter p&aring; samme dato\">";
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

   echo "<select name=id_location>";

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

function select_person($selected)
{
   global $db;

   echo "<select name=id_responsible title=\"Hovedansvarlig\">";

   $q = "SELECT person.id as id, firstname, lastname, instrument FROM person, instruments " .
           "where id_instruments = instruments.id " .
           "and person.status = $db->per_stat_member " .
           "order by list_order, lastname, firstname";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         echo " selected";
      echo ">" . $e['firstname'] . " " . $e['lastname'] . " (" . $e['instrument'] . ")\n";
   }
   echo "</select>";
}

function select_project($selected)
{
   global $db;

   if (is_null($selected))
      $selected = -1;
   echo "<select name=id_project>";

   $year = date("Y");
   $q = "SELECT id, name, semester, year FROM project " .
           "where year >= $year " .
           "or id = $selected " .
           "order by year, semester DESC";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         echo " selected";
      echo ">" . $e['name'] . " (" . $e['semester'], $e['year'] . ")";
   }
   echo "</select>";
}

function direction_update($id_plan)
{
   global $db;

   // Resources from shift list
   $query = "update direction set status = $db->dir_stat_free where id_plan = $id_plan";
   $db->query($query);

   if (!is_null(request('id_persons')))
   {
      foreach ($_POST['id_persons'] as $id_person)
      {
         $stmt = $db->query("select * from direction where id_person=$id_person and id_plan=$id_plan");
         if ($stmt->rowCount() == 0)
         {
            $query = "insert into direction (id_person, id_plan, status) " .
                    "values ($id_person, $id_plan, $db->dir_stat_allocated)";
         }
         else
         {
            $query = "update direction set status = $db->dir_stat_allocated " .
                    "where id_plan = $id_plan " .
                    "and id_person = $id_person";
         }
         $db->query($query);
      }
   }
}

function direction_select($id_plan)
{
   // Resources from the shift list

   global $db;

   $q = "SELECT id_person, firstname, lastname, instrument, stat_dir as status " .
           "FROM person, instruments, participant, plan " .
           "where instruments.id = person.id_instruments " .
           "and person.id = participant.id_person " .
           "and participant.id_project = plan.id_project " .
           "and plan.id = $id_plan " .
           "and (stat_dir = $db->shi_stat_tentative or stat_dir = $db->shi_stat_confirmed) " .
           "order by instruments.list_order, lastname, firstname";
   $s = $db->query($q);

   if (($size = $s->rowCount()) == 0)
      return;

   if ($size > 5)
      $size = 5;

   echo "<select name=\"id_persons[]\" multiple size=$size title=\"Medlemmer i regikomit&eacute;en\nCtrl-click to select/unselect single\">";

   $q2 = "SELECT id_person FROM direction where id_plan = $id_plan and status = $db->dir_stat_allocated";
   $s2 = $db->query($q2);
   $r2 = $s2->fetchAll(PDO::FETCH_ASSOC);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e['id_person'] . "\"";
      reset($r2);
      foreach ($r2 as $e2)
         if ($e['id_person'] == $e2['id_person'])
            echo " selected";
      echo ">" . $e['firstname'] . " " . $e['lastname'];
      if ($e['status'] == $db->shi_stat_tentative)
         echo "*";
      echo " (" . $e['instrument'] . ")";
   }
   echo "</select>";
}

function direction_list($id_plan)
{
   global $db;

   $q = "SELECT firstname, lastname, status_dir, instrument, direction.status as status, participant.stat_dir as shift_status " .
           "FROM person, instruments, direction, participant, project, plan " .
           "where person.id = direction.id_person " .
           "and person.id_instruments = instruments.id " .
           "and participant.id_person = person.id " .
           "and participant.id_project = project.id " .
           "and project.id = plan.id_project " .
           "and plan.id = direction.id_plan " .
           "and direction.id_plan = $id_plan " .
           "and direction.status = $db->dir_stat_allocated " .
           "order by lastname, firstname";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      if ($e['shift_status'] == $db->shi_stat_tentative)
         echo "<font color=grey>";
      if ($e['shift_status'] == $db->shi_stat_failed)
         echo "<strike>";
      echo $e['firstname'] . " " . $e['lastname'] . " (" . $e['instrument'] . ")";
      if ($e['shift_status'] == $db->shi_stat_tentative)
         echo "</font>";
      if ($e['shift_status'] == $db->shi_stat_failed)
         echo "</strike>";
      if ($e['status_dir'] == $db->per_dir_nocarry)
         echo "<image src=images/chair-minus-icon.png border=0 title=\"Kan ikke l&oslash;fte bord\">";
      echo "<br>";
   }
}

echo "
    <h1>Regiplan</h1>";
if ($access->auth(AUTH::DIR_RW))
{
   echo "
    <form action='$php_self' method=post>
    <input type=hidden name=_action value=new>";
   if (!is_null(request('rehearsal')))
      echo "<input type=hidden name=rehearsal value=true>";
   if (!is_null($id_project))
      echo "<input type=hidden name=id_project value=" . request('id_project') . ">\n";
   echo "
      <input type=hidden name=id_location value=" . request('id_location') . ">
      <input type=submit value=\"Ny aktivitet\">
    </form>
    <form action='$php_self' method=post>\n";
   if (!is_null($id_project))
      echo "<input type=hidden name=id_project value=" . request('id_project') . ">\n";
   echo "<input type=checkbox name=rehearsal title=\"Vis også prøveplan\" onChange=\"submit();\"";
   if (!is_null(request('rehearsal')))
      echo " checked";
   echo ">
    </form>";
}
echo "
    <form action='{$php_self}' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::DIR_RW))
   echo "
      <th>Edit</th>";
echo "
      <th>Dato</th>
      <th>Tid</th>
      <th>Sted</th>
      <th>Prosjekt</th>
      <th>Ansvarlig</th>
      <th>Merknad</th>
    </tr>";


if ($action == 'new')
{
   if (!is_null(request('rehearsal')))
      echo "<input type=hidden name=rehearsal value=true>";
   echo "<tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=submit value=ok></td>
    <td><input type=date size=10 name=date title=\"Format: <dato>. <mnd> [<&aring;r>] Merk: M&aring;ned p&aring; engelsk. Eksempel: 12. dec\"></td>
    <td nowrap>";
   select_tsort(null);
   echo "<input type=text size=10 name=time value=\"18:10\"></td>
    <td>";
   select_location(request('id_location'));
   echo "<br><input type=text size=22 name=location>";
   echo "</td>
    <td>";
   select_project($id_project);
   echo "
  </td>
    <td></td>
    <td><textarea cols=50 rows=6 wrap=virtual name=comment>Opprigg til vanlig orkesterprøve</textarea></td>
  </tr>";
}

if ($action == 'update' && $access->auth(AUTH::DIR_RW))
{
   if (($ts = strtotime(request('date'))) == false)
      echo "<font color=red>Illegal time format: " . request('date') . "</font>";
   else
   {
      if (is_null($no))
      {
         $query2 = "select id_person from project where id = $id_project";
         $stmt = $db->query($query2);
         $row = $stmt->fetch(PDO::FETCH_ASSOC);

         $query = "insert into plan (date, tsort, time, id_location, location, id_project, " .
                 "id_responsible, responsible, comment, event_type) " .
                 "values ($ts, " . request('tsort') . ", '" . request('time') . "', " .
                 request('id_location') . ", " . $db->qpost('location') . ", $id_project, " . $row['id_person'] . ", " .
                 "'Regikomité', " . $db->qpost('comment') . ", $db->plan_evt_direction)";
      }
      else
      {
         if (!is_null($delete))
         {
            $query = "DELETE FROM plan WHERE id = $no";
            $query2 = "delete from direction where id_plan = $no";
            $db->query($query2);
         }
         else
         {
            $query = "update plan set date = $ts," .
                    "time = '" . request('time') . "'," .
                    "tsort = " . request('tsort') . "," .
                    "id_location = " . request('id_location') . "," .
                    "location = " . $db->qpost('location') . "," .
                    "id_project = $id_project," .
                    "id_responsible = " . request('id_responsible') . "," .
                    "responsible = " . $db->qpost('responsible') . "," .
                    "comment = " . $db->qpost('comment') . "," .
                    "event_type = $db->plan_evt_direction " .
                    "where id = $no";
            direction_update($no);
         }
         $no = NULL;
      }
      $db->query($query);
   }
}


if ($action == 'add' && $access->auth(AUTH::DIR_RW))
{
   $query = "select participant.id_person as id_person " .
           "from participant, project " .
           "where participant.id_project = project.id " .
           "and participant.stat_dir = $db->shi_stat_confirmed " .
           "and project.id = $id_project";

   $stmt = $db->query($query);
   foreach ($stmt as $row)
   {
      $s = $db->query("select * from direction where id_person=" . $row['id_person'] . " and id_plan=$no");
      if ($s->rowCount() == 0)
      {
         $query = "insert into direction (id_person, id_plan, status) " .
                 "values (" . $row['id_person'] . ", $no, $db->dir_stat_allocated) ";
      }
      else
      {
         $query = "update direction set status = $db->dir_stat_allocated " .
                 "where id_plan = $no " .
                 "and id_person = " . $row['id_person'];
      }
      $db->query($query);
   }
}


$query = "SELECT plan.id as id, date, time, tsort, id_project, event_type, " .
        "id_location, plan.location as location, location.name as lname, " .
        "project.name as pname, location.url as url, id_responsible, plan.responsible as responsible, " .
        "firstname, lastname, plan.comment as comment " .
        "FROM person, project, plan, location " .
        "where id_location = location.id " .
        "and id_project = project.id " .
        "and id_responsible = person.id ";
if (!is_null($id_project))
   $query .= "and plan.id_project = $id_project ";
else
   $query .= "and plan.date >= " . $season->ts()[0] . " " .
        "and plan.date < " . $season->ts()[1] . " ";
if (is_null(request('rehearsal')))
   $query .= "and plan.event_type = $db->plan_evt_direction ";
$query .= "order by date,tsort,time";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($row['id'] != $no || $action != 'view')
   {
      $style = ($row['event_type'] != $db->plan_evt_direction) ? "style=\"background-color: #EEEEEE;\"" : "";
      echo "<tr $style>";
      if ($access->auth(AUTH::DIR_RW))
      {
         $reh = request('rehearsal') ? "&rehearsal=true" : "";
         echo "<td><center>";
         if ($row['event_type'] == $db->plan_evt_direction)
            echo "
               <a href=\"$php_self?_action=view&_no=" . $row['id'] . "&id_project=".$row['id_project']."$reh\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>";
         echo "</center></td>";
      }
      echo
      "<td>" . strftime('%a %e.%b %y', $row['date']) . "</td>" .
      "<td>" . $row['time'] . "</td><td>";
      if (strlen($row['url']) > 0)
         echo "<a href=\"" . $row['url'] . "\">" . $row['lname'] . "</a>";
      else
         echo $row['lname'];
      echo $row['location'];
      echo "</td><td>" . $row['pname'] . "</td><td nowrap>";
      if ($row['event_type'] == $db->plan_evt_direction)
      {
         echo "<b>" . $row['firstname'] . " " . $row['lastname'] . "</b><br>\n";
         direction_list($row['id']);
         echo $row['responsible'];
      }
      echo "</td><td>";
      echo str_replace("\n", "<br>\n", $row['comment']);
      echo "</td>" .
      "</tr>\n";
   }
   else
   {
      if (request('rehearsal'))
         echo "<input type=hidden name=rehearsal value=true>";
      echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_no value='$no'>
    <td nowrap><input type=submit value=ok>
    <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette" . strftime('%e.%b %y', $row['date']) . "?');\"></td>
    <td><input type=date size=10 name=date value=\"" . date('Y-m-d', $row['date']) . "\"></td>
    <td nowrap>";
      select_tsort($row['tsort']);
      echo "<input type=text size=10 name=time value=\"" . $row['time'] . "\"></td>
    <td>";
      select_location($row['id_location']);
      echo "<br><input type=text size=22 name=location value=\"" . $row['location'] . "\">";
      echo "</td>
    <td>";
      select_project($row['id_project']);
      echo "</td>
    <td>";
      select_person($row['id_responsible']);
      echo "<br>";
      direction_select($row['id']);
      echo "<br><input type=text size=22 name=responsible value=\"" . $row['responsible'] . "\">";
      echo "</td>
    <td><textarea cols=50 rows=6 wrap=virtual name=comment>" . $row['comment'] . "</textarea></td>
    </tr>";
   }
}

echo "</table></form>";
