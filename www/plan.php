<?php

include 'framework.php';

$id_project = is_null($_REQUEST[id_project]) ? "%" : $_REQUEST[id_project];
$cur_semester = (date("n") > 6) ? 'H' : 'V';
$cur_year = date("Y");
$semester = is_null($_REQUEST[semester]) ? $cur_semester : $_REQUEST[semester];
$year = is_null($_REQUEST[year]) ? $cur_year : $_REQUEST[year];

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

function select_project($selected)
{
   global $db;

   echo "<select name=id_project>";

   $year = date("Y");
   $q = "SELECT id, name, semester, year, orchestration FROM project " .
           "where year >= $year " .
           "or id = '$selected' " .
           "order by year, semester DESC";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e[id] . "\"";
      if ($e[id] == $selected)
         echo " selected";
      echo ">" . $e[name] . " (" . $e[semester], $e[year] . ")";
      if ($e[orchestration] == $db->prj_orch_reduced)
         echo '*';
   }
   echo "</select>";
}

echo "<h1>Prøveplan</h1>\n";

if ($id_project == '%')
{
   $h2 = ($semester == 'V') ? "Vår $year" : "Høst $year";
}
else
{
   $s = $db->query("select name, semester, year from project where id = $id_project");
   $e = $s->fetch(PDO::FETCH_ASSOC);
   $h2 = "$e[name] ($e[semester]$e[year])";
}
echo "<h2>$h2</h2>\n";

if ($access->auth(AUTH::PLAN_RW))
   echo "<a href=\"$php_self?id_project=$id_project&semester=$semester&year=$year&_action=new&id_location=$_REQUEST[id_location]\" title=\"Registrer ny prøve...\"><img src=\"images/new_inc.gif\" border=0 hspace=5 vspace=5></a>\n";
echo "<a href=\"plan_pdf.php?semester=$semester&year=$year\" title=\"PDF versjon...\"><img src=images/pdf.jpeg height=22 border=0 hspace=5 vspace=5></a>\n";

if ($semester == 'V')
{
   $op_semester = 'H';
   $next_year = $year;
   $last_year = $year - 1;
} 
else
{
   $op_semester = 'V';
   $next_year = $year + 1;
   $last_year = $year;
}

echo "<a href=\"$php_self?semester=$op_semester&year=$last_year\" title=\"Plan for forrige semester...\"><img src=\"images/left.gif\" height=22 border=0 hspace=5 vspace=5></a>\n";
echo "<a href=\"$php_self?semester=$cur_semester&year=$cur_year\" title=\"Plan for dette semesteret...\"><img src=\"images/die1.gif\" height=22 border=0 hspace=5 vspace=5></a>\n";
echo "<a href=\"$php_self?semester=$op_semester&year=$next_year\" title=\"Plan for neste semester...\"><img src=\"images/right.gif\" height=22 border=0 hspace=5 vspace=5></a>\n";

echo "
    <form action='{$php_self}' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::PLAN_RW))
   echo "
      <th bgcolor=#A6CAF0>Edit</th>";
echo "
      <th bgcolor=#A6CAF0>Dato</th>
      <th bgcolor=#A6CAF0>Prøvetid</th>
      <th bgcolor=#A6CAF0>Lokale</th>
      <th bgcolor=#A6CAF0>Prosjekt</th>
      <th bgcolor=#A6CAF0>Merknad</th>
    </tr>";

$hlp_date = "Format: <dato>. <mnd> [<år>] Merk: Måned på engelsk. Eksempel: 12. dec";

if ($action == 'new')
{
   echo "<tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=submit value=ok></td>
    <th><input type=date size=10 name=date title=\"$hlp_date\"></th>
    <th nowrap>";
   select_tsort(null);
   echo "<input type=text size=11 name=time value=\"18:30-21:30\"></th>
    <th>";
   select_location($_REQUEST[id_location]);
   echo "<br><input type=text size=22 name=location>";
   echo "</th>
    <th>";
   select_project($id_project);
   echo "
  </th>
  <th><textarea cols=50 rows=6 wrap=virtual name=comment>Tutti</textarea></th>
  </tr>";
}

if ($action == 'update' && $access->auth(AUTH::PLAN_RW))
{
   if (($ts = strtotime($_POST[date])) == false)
      echo "<font color=red>Illegal time format: " . $_POST[date] . "</font>";
   else
   {
      if ($no == NULL)
      {
         $query2 = "select id_person from project where id = $id_project";
         $stmt = $db->query($query2);
         $row = $stmt->fetch(PDO::FETCH_ASSOC);

         $query = "insert into plan (date, tsort, time, id_location, location, id_project, " .
                 "id_responsible, comment, event_type) " .
                 "values ($ts, $_POST[tsort], '$_POST[time]', " .
                 "$_POST[id_location], '$_POST[location]', $id_project, $row[id_person], " .
                 "'$_POST[comment]', $db->plan_evt_rehearsal)";
      } else
      {
         if ($delete != NULL)
         {
            $query = "DELETE FROM plan WHERE id = $no";
         } else
         {
            $query = "update plan set date = $ts," .
                    "time = '$_POST[time]'," .
                    "tsort = $_POST[tsort]," .
                    "id_location = $_POST[id_location]," .
                    "location = '$_POST[location]'," .
                    "id_project = $id_project," .
                    "comment = '$_POST[comment]'," .
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
        "plan.comment as comment, orchestration " .
        "FROM project, plan, location " .
        "where id_location = location.id " .
        "and id_project = project.id " .
        "and plan.id_project like '$id_project' " .
        "and plan.event_type = $db->plan_evt_rehearsal ";
if ($id_project == '%')
   $query .= 
        "and project.year = $year " .
        "and project.semester = '$semester' ";
$query .= 
        "order by date,tsort,time";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($row[id] != $no || $action != 'view')
   {
      if ($access->auth(AUTH::PLAN_RW))
         echo "<tr>
        <td><center>
            <a href=\"{$php_self}?_action=view&_no={$row[id]}&id_project=$id_project&semester=$semester&year=$year\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for å editere...\"></a>
             </center></td>";
      echo "<td>" . strftime('%a %e.%b %y', $row[date]) . "</td>" .
      "<td>{$row[time]}</td><td>";
      if (strlen($row[url]) > 0)
         echo "<a href=\"{$row[url]}\">{$row[lname]}</a>";
      else
         echo $row[lname];
      echo $row[location];
      echo "</td><td>$row[pname]";
      if ($row[orchestration] == $db->prj_orch_reduced)
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
    <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette" . strftime('%e.%m.%y', $row[date]) . "?');\"></td>
    <td><input type=date size=10 name=date value=\"" . date('j. M y', $row[date]) . "\" title=\"$hlp_date\"></td>
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

echo "</table>
</form>";
