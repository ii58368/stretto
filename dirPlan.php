<?php
include 'framework.php';

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
      echo "<option value=\"" . $e[id] . "\"";
      if ($e[id] == $selected)
         echo " selected";
      echo ">" . $e[name];
   }
   echo "</select>";
}

function select_person($selected)
{
   global $db;
   global $per_stat_member;

   echo "<select name=id_responsible title=\"Hovedansvarlig\">";

   $q = "SELECT person.id as id, firstname, lastname, instrument FROM person, instruments " .
           "where id_instruments = instruments.id " .
           "and person.status = $per_stat_member " .
           "order by list_order, lastname, firstname";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e[id] . "\"";
      if ($e[id] == $selected)
         echo " selected";
      echo ">" . $e[firstname] . " " . $e[lastname] . " (" . $e[instrument] . ")\n";
   }
   echo "</select>";
}

function select_project($selected)
{
   global $db;

   echo "<select name=id_project>";

   $year = date("Y");
   $q = "SELECT id, name, semester, year FROM project " .
           "where year >= ${year} " .
           "or id = '${selected}' " .
           "order by year, semester DESC";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e[id] . "\"";
      if ($e[id] == $selected)
         echo " selected";
      echo ">" . $e[name] . " (" . $e[semester], $e[year] . ")";
   }
   echo "</select>";
}

function direction_update($id_plan)
{
   global $db;
   global $dir_stat_free;
   global $dir_stat_allocated;

   // Resources from shift list
   $query = "update direction set status = $dir_stat_free where id_plan = $id_plan";
   $db->query($query);

   if ($_POST[id_persons] != null)
   {
      foreach ($_POST[id_persons] as $id_person)
      {
         $stmt = $db->query("select * from direction where id_person=$id_person and id_plan=$id_plan");
         if ($stmt->rowCount() == 0)
         {
            $query = "insert into direction (id_person, id_plan, status) " .
                    "values ('$id_person', '$id_plan', '$dir_stat_allocated')";
         } else
         {
            $query = "update direction set status = $dir_stat_allocated " .
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
   global $shi_stat_tentative;
   global $shi_stat_confirmed;
   global $dir_stat_allocated;

   echo "<select name=\"id_persons[]\" multiple title=\"Medlemmer i regikomit&eacute;en\nCtrl-click to select/unselect single\">";

   $q = "SELECT id_person, firstname, lastname, instrument, shift.status as status " .
           "FROM person, instruments, shift, plan " .
           "where instruments.id = person.id_instruments " .
           "and person.id = shift.id_person " .
           "and shift.id_project = plan.id_project " .
           "and plan.id = $id_plan " .
           "and (shift.status = $shi_stat_tentative or shift.status = $shi_stat_confirmed) " .
           "order by instruments.list_order, lastname, firstname";
   $s = $db->query($q);

   $q2 = "SELECT id_person FROM direction where id_plan = $id_plan and status = $dir_stat_allocated";
   $s2 = $db->query($q2);
   $r2 = $s2->fetchAll(PDO::FETCH_ASSOC);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e[id_person] . "\"";
      reset($r2);
      foreach ($r2 as $e2)
         if ($e[id_person] == $e2[id_person])
            echo " selected";
      echo ">$e[firstname] $e[lastname]";
      if ($e[status] == $shi_stat_tentative)
         echo "*";
      echo " ($e[instrument])";
   }
   echo "</select>";
}

function direction_list($id_plan)
{
   global $db;
   global $dir_stat_allocated;
   global $shi_stat_tentative;
   global $shi_stat_failed;
   global $per_dir_nocarry;

   $q = "SELECT firstname, lastname, status_dir, instrument, direction.status as status, participant.stat_dir as shift_status " .
           "FROM person, instruments, direction, participant, project, plan " .
           "where person.id = direction.id_person " .
           "and person.id_instruments = instruments.id " .
           "and participant.id_person = person.id " .
           "and participant.id_project = project.id " .
           "and project.id = plan.id_project " .
           "and plan.id = direction.id_plan " .
           "and direction.id_plan = ${id_plan} " .
           "and direction.status = $dir_stat_allocated " .
           "order by lastname, firstname";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      if ($e[shift_status] == $shi_stat_tentative)
         echo "<font color=grey>";
      if ($e[shift_status] == $shi_stat_failed)
         echo "<strike>";
      echo $e[firstname] . " " . $e[lastname] . " (" . $e[instrument] . ")";
      if ($e[shift_status] == $shi_stat_tentative)
         echo "</font>";
      if ($e[shift_status] == $shi_stat_failed)
         echo "</strike>";
      if ($e[status_dir] == $per_dir_nocarry)
         echo "<image src=images/chair-minus-icon.png border=0 title=\"Kan ikke l&oslash;fte bord\">";
      echo "<br>";
   }
}

echo "
    <h1>Regiplan</h1>";
echo "
    <form action='$php_self' method=post>
      <input type=hidden name=rehearsal value=true>
      <input type=hidden name=_action value=new>
      <input type=hidden name=id_project value='$_REQUEST[id_project]'>
      <input type=submit value=\"Ny aktivitet\">
    </form>
    <form action='$php_self' method=post>
      <input type=hidden name=id_project value='$_REQUEST[id_project]'>
      <input type=checkbox name=rehearsal title=\"Vis også prøveplan\" onChange=\"submit();\"";
if ($_REQUEST[rehearsal])
   echo "checked ";
echo ">
    </form>
    <form action='{$php_self}' method=post>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Edit</th>
      <th bgcolor=#A6CAF0>Dato</th>
      <th bgcolor=#A6CAF0>Tid</th>
      <th bgcolor=#A6CAF0>Sted</th>
      <th bgcolor=#A6CAF0>Prosjekt</th>
      <th bgcolor=#A6CAF0>Ansvarlig</th>
      <th bgcolor=#A6CAF0>Merknad</th>
    </tr>";


if ($action == 'new')
{
   if ($_REQUEST[rehearsal])
      echo "<input type=hidden name=rehearsal value=true>";
   echo "<tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=submit value=ok></td>
    <th><input type=text size=10 name=date title=\"Format: <dato>. <mnd> [<&aring;r>] Merk: M&aring;ned p&aring; engelsk. Eksempel: 12. dec\"></th>
    <th nowrap>";
   select_tsort(null);
   echo "<input type=text size=10 name=time value=\"18:10\"></th>
    <th>";
   select_location(1);
   echo "<br><input type=text size=22 name=location>";
   echo "</th>
    <th>";
   select_project($_REQUEST[id_project]);
   echo "
  </th>
    <th></th>
    <th><textarea cols=50 rows=6 wrap=virtual name=comment>Opprigg til vanlig orkesterprøve</textarea></th>
  </tr>";
}

if ($action == 'update')
{
   if (($ts = strtotime($_POST[date])) == false)
      echo "<font color=red>Illegal time format: " . $_POST[date] . "</font>";
   else
   {
      if ($no == NULL)
      {
         $query2 = "select id_person from project where id = $_POST[id_project]";
         $stmt = $db->query($query2);
         $row = $stmt->fetch(PDO::FETCH_ASSOC);

         $query = "insert into plan (date, tsort, time, id_location, location, id_project, " .
                 "id_responsible, responsible, comment, event_type) " .
                 "values ('$ts', '$_POST[tsort]', '$_POST[time]', " .
                 "'$_POST[id_location]', '$_POST[location]', '$_POST[id_project]', '$row[id_person]', " .
                 "'Regikomité', '$_POST[comment]', $plan_evt_direction)";
      } else
      {
         if ($delete != NULL)
         {
            $query = "DELETE FROM plan WHERE id = $no";
            $query2 = "delete from direction where id_plan = $no";
            $db->query($query2);
         } else
         {
            $query = "update plan set date = '$ts'," .
                    "time = '$_POST[time]'," .
                    "tsort = '$_POST[tsort]'," .
                    "id_location = '$_POST[id_location]'," .
                    "location = '$_POST[location]'," .
                    "id_project = '$_POST[id_project]'," .
                    "id_responsible = '$_POST[id_responsible]'," .
                    "responsible = '$_POST[responsible]'," .
                    "comment = '$_POST[comment]'," .
                    "event_type = $plan_evt_direction " .
                    "where id = $no";
            direction_update($no);
         }
         $no = NULL;
      }
      $db->query($query);
   }
}


if ($action == 'add')
{
   $query = "select participant.id_person as id_person " .
           "from participant, project " .
           "where participant.id_project = project.id " .
           "and participant.stat_dir = $shi_stat_confirmed " .
           "and project.id = $_REQUEST[id_project] ";

   $stmt = $db->query($query);
   foreach ($stmt as $row)
   {
      $s = $db->query("select * from direction where id_person=$row[id_person] and id_plan=$no");
      if ($s->rowCount() == 0)
      {
         $query = "insert into direction (id_person, id_plan, status) " .
                 "values ($row[id_person], $no, $dir_stat_allocated) ";
      } else
      {
         $query = "update direction set status = $dir_stat_allocated " .
                 "where id_plan = $no " .
                 "and id_person = $row[id_person]";
      }
      $db->query($query);
   }
}

$cur_year = ($_REQUEST[id_project] == '%') ? date("Y") : 0;
$event_type = $_REQUEST[rehearsal] ? "" : "and plan.event_type = $plan_evt_direction ";

$query = "SELECT plan.id as id, date, time, tsort, id_project, event_type, " .
        "id_location, plan.location as location, location.name as lname, " .
        "project.name as pname, location.url as url, id_responsible, plan.responsible as responsible, " .
        "firstname, lastname, plan.comment as comment " .
        "FROM person, project, plan, location " .
        "where id_location = location.id " .
        "and id_project = project.id " .
        "and id_responsible = person.id " .
        "and plan.id_project like '$_REQUEST[id_project]' " .
        $event_type .
        "and project.year >= $cur_year " .
        "order by date,tsort,time";

$stmt = $db->query($query);

foreach($stmt as $row)
{
   if ($row[id] != $no || $action != 'view')
   {
      $reh = $_REQUEST[rehearsal] ? "&rehearsal=true" : "";
      echo "<tr>
        <td><center>";
      if ($row[event_type] == $plan_evt_direction)
         echo "
            <a href=\"{$php_self}?_action=view&_no={$row[id]}&id_project=$_REQUEST[id_project]$reh\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>";
      echo "</center></td>" .
      "<td>" . date('D j.M y', $row[date]) . "</td>" .
      "<td>{$row[time]}</td><td>";
      if (strlen($row[url]) > 0)
         echo "<a href=\"{$row[url]}\">{$row[lname]}</a>";
      else
         echo $row[lname];
      echo $row[location];
      echo "</td><td>{$row[pname]}</td><td nowrap>";
      if ($row[event_type] == $plan_evt_direction)
      {
         echo "<b>{$row[firstname]} {$row[lastname]}</b><a href=\"{$php_self}?_action=add&_no={$row[id]}&id_project=$_REQUEST[id_project]\"><img src=\"images/user_male_add2.png\" border=0 title=\"Legg til regigruppen\"></a><br>";
         direction_list($row[id]);
         echo $row[responsible];
      }
      echo "</td><td>";
      echo str_replace("\n", "<br>\n", $row[comment]);
      echo "</td>" .
      "</tr>";
   } else
   {
      if ($_REQUEST[rehearsal])
         echo "<input type=hidden name=rehearsal value=true>";
      echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_no value='$no'>
    <th nowrap><input type=submit value=ok>
    <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette" . date('j.M.y', $row[date]) . "?');\"></th>
    <th><input type=text size=10 name=date value=\"" . date('j.M.y', $row[date]) . "\"></th>
    <th nowrap>";
      select_tsort($row[tsort]);
      echo "<input type=text size=10 name=time value=\"{$row[time]}\"></th>
    <th>";
      select_location($row[id_location]);
      echo "<br><input type=text size=22 name=location value=\"{$row[location]}\">";
      echo "</th>
    <th>";
      select_project($row[id_project]);
      echo "</th>
    <th>";
      select_person($row[id_responsible]);
      echo "<br>";
      direction_select($row[id]);
      echo "<br><input type=text size=22 name=responsible value=\"{$row[responsible]}\">";
      echo "</th>
    <th><textarea cols=50 rows=6 wrap=virtual name=comment>{$row[comment]}</textarea></th>
    </tr>";
   }
}

echo "</table></form>";

include 'framework_end.php';
?> 
