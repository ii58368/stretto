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

   foreach($s as $e)
   {
      echo "<option value=\"" . $e[id] . "\"";
      if ($e[id] == $selected)
         echo " selected";
      echo ">" . $e[name];
   }
   echo "</select>";
}


function select_project($selected)
{
   global $db;
   global $prj_orch_reduced;
   
   echo "<select name=id_project>";

   $year = date("Y");
   $q = "SELECT id, name, semester, year, orchestration FROM project " .
           "where year >= ${year} " .
           "or id = '${selected}' " .
           "order by year, semester DESC";
   $s = $db->query($q);

   foreach($s as $e)
   {
      echo "<option value=\"" . $e[id] . "\"";
      if ($e[id] == $selected)
         echo " selected";
      echo ">" . $e[name] . " (" . $e[semester], $e[year] . ")";
      if ($e[orchestration] == $prj_orch_reduced)
         echo '*';
   }
   echo "</select>";
}


echo "
    <h1>Prøveplan</h1>";
echo "
    <form action='$php_self' method=post>
      <input type=hidden name=_action value=new>
      <input type=hidden name=id_project value='$_REQUEST[id_project]'>
      <input type=submit value=\"Ny prøve\">
    </form>
    <form action='{$php_self}' method=post>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Edit</th>
      <th bgcolor=#A6CAF0>Dato</th>
      <th bgcolor=#A6CAF0>Prøvetid</th>
      <th bgcolor=#A6CAF0>Lokale</th>
      <th bgcolor=#A6CAF0>Prosjekt</th>
      <th bgcolor=#A6CAF0>Merknad</th>
    </tr>";


if ($action == 'new')
{
   echo "<tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=submit value=ok></td>
    <th><input type=text size=10 name=date title=\"Format: <dato>. <mnd> [<&aring;r>] Merk: M&aring;ned p&aring; engelsk. Eksempel: 12. dec\"></th>
    <th nowrap>";
   select_tsort(null);
   echo "<input type=text size=11 name=time value=\"18:30-21:30\"></th>
    <th>";
   select_location(1);
   echo "<br><input type=text size=22 name=location>";
   echo "</th>
    <th>";
   select_project($_REQUEST[id_project]);
   echo "
  </th>
  <th><textarea cols=50 rows=6 wrap=virtual name=comment>Tutti</textarea></th>
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
                 "id_responsible, comment, event_type) " .
                 "values ('$ts', '$_POST[tsort]', '$_POST[time]', " .
                 "'$_POST[id_location]', '$_POST[location]', '$_POST[id_project]', '$row[id_person]', " .
                 "'$_POST[comment]', $plan_evt_rehearsal)";
      } else
      {
         if ($delete != NULL)
         {
            $query = "DELETE FROM plan WHERE id = $no";
         } else
         {
            $query = "update plan set date = '$ts'," .
                    "time = '$_POST[time]'," .
                    "tsort = '$_POST[tsort]'," .
                    "id_location = '$_POST[id_location]'," .
                    "location = '$_POST[location]'," .
                    "id_project = '$_POST[id_project]'," .
                    "comment = '$_POST[comment]'," .
                    "event_type = $plan_evt_rehearsal " .
                    "where id = $no";
         }
         $no = NULL;
      }
      
      $db->query($query);
   }
}


$cur_year = ($_REQUEST[id_project] == '%') ? date("Y") : 0;

$query = "SELECT plan.id as id, date, time, tsort, id_project, " .
        "id_location, plan.location as location, location.name as lname, " .
        "project.name as pname, location.url as url, " .
        "plan.comment as comment, orchestration " .
        "FROM project, plan, location " .
        "where id_location = location.id " .
        "and id_project = project.id " .
        "and plan.id_project like '$_REQUEST[id_project]' " .
        "and plan.event_type = $plan_evt_rehearsal " .
        "and project.year >= $cur_year " .
        "order by date,tsort,time";

$stmt = $db->query($query);

foreach($stmt as $row)
{
   if ($row[id] != $no || $action != 'view')
   {
      echo "<tr>
        <td><center>
            <a href=\"{$php_self}?_action=view&_no={$row[id]}&id_project=$_REQUEST[id_project]\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>
             </center></td>" .
      "<td>" . date('D j.M y', $row[date]) . "</td>" .
      "<td>{$row[time]}</td><td>";
      if (strlen($row[url]) > 0)
         echo "<a href=\"{$row[url]}\">{$row[lname]}</a>";
      else
         echo $row[lname];
      echo $row[location];
      echo "</td><td>$row[pname]";
      if ($row[orchestration] == $prj_orch_reduced)
         echo '*';
      echo "</td><td>";
      echo str_replace("\n", "<br>\n", $row[comment]);
      echo "</td>" .
      "</tr>";
   }
   else
   {
      echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_no value='$no'>
    <td nowrap><input type=submit value=ok>
    <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette" . date('j.M.y', $row[date]) . "?');\"></td>
    <td><input type=text size=10 name=date value=\"" . date('j.M.y', $row[date]) . "\"></td>
    <td nowrap>";
      select_tsort($row[tsort]);
      echo "<input type=text size=11 name=time value=\"{$row[time]}\"></td>
    <td>";
      select_location($row[id_location]);
      echo "<br><input type=text size=22 name=location value=\"{$row[location]}\">";
      echo "</td>
    <td>";
      select_project($row[id_project]);
      echo "</td>
    <td><textarea cols=50 rows=6 wrap=virtual name=comment>{$row[comment]}</textarea></td>
    </tr>";
   }
}

include 'framework_end.php';
?> 

</table>
</form>
</body>
</html>

