
<?php
require 'framework.php';

if ($sort == NULL)
   $sort = 'year,semester DESC';

function select_semester($selected)
{
   echo "<select name=semester>";
   echo "<option value=V";
   if ($selected == 'V')
      echo " selected";
   echo ">V&aring;r</option>\n";

   echo "<option value=H";
   if ($selected == 'H')
      echo " selected";
   echo ">H&oslash;st</option>\n";
   echo "</select>";
}

function select_status($selected)
{
   global $prj_stat;
   global $prj_stat_draft;

   if ($selected == NULL)
      $selected = $prj_stat_draft;

   echo "<select name=status>";

   for ($i = 0; $i < count($prj_stat); $i++)
   {
      echo "<option value=$i";
      if ($selected == $i)
         echo " selected";
      echo ">$prj_stat[$i]</option>\n";
   }

   echo "</select>";
}

$sel_year = ($_REQUEST[from] == NULL) ? date("Y") : intval($_REQUEST[from]);
$prev_year = $sel_year - 1;

echo "
    <h1>Prosjekt</h1>
    <form action='$php_self' method=post>
      <input type=hidden name=_sort value='$sort'>
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Nytt prosjekt\" title=\"Definer nytt prosjekt\" >
    </form>
    <form action='$php_self' method=post>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Edit</th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=name,id&from=$sel_year\" title=\"Sorter p&aring; prosjektnavn\">Prosjekt</a></th>
      <th bgcolor=#A6CAF0 nowrap><a href=\"$php_self?_sort=year,semester+DESC,id&from=$sel_year\" title=\"Sorter p&aring; semester\">Sem</a>
           <a href=\"$php_self?from=$prev_year&_sort={$sort}\"><img src=images/arrow_up.png border=0 title=\"Forrige &aring;r...\"></a></th>
      <th bgcolor=#A6CAF0>Status</th>
      <th bgcolor=#A6CAF0>Deadline</th>
      <th bgcolor=#A6CAF0>Tutti</th>
      <th bgcolor=#A6CAF0>Generell info</th>
    </tr>";


if ($action == 'new')
{
   echo "  <tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=from value='$sel_year'>
    <input type=submit value=ok title=\"Registrer prosjekt\" ></td>
    <th><input type=text size=20 name=name></th>
    <th nowrap>";
   select_semester(null);
   echo "
    <input type=text size=4 maxlength=4 name=year value=" . date("Y") . "></th>
    <th>";
   select_status(null);
   echo "</th>
    <th><input type=text size=10 name=deadline title=\"Format: <dato>. <mnd> [<&aring;r>] Merk: M&aring;ned p&aring; engelsk. Eksempel: 12. dec\"></th>
    <th><input type=checkbox name=orchestration></th>
    <th><textarea cols=44 rows=10 wrap=virtual name=info></textarea></th>
  </tr>";
}

if ($action == 'update')
{
   $orchestration = ($_POST[orchestration] == null) ? $prj_orch_reduced : $prj_orch_tutti;

   if (($ts = strtotime($_POST[deadline])) == false)
   {
      echo "<font color=red>Illegal time format: " . $_POST[deadline] . "</font>";
   } else
   {
      if ($no == NULL)
      {
         $query = "insert into project (name, semester, year, status, deadline, orchestration, info, id_person) " .
                 "values ('$_POST[name]', '$_POST[semester]', " .
                 "'$_POST[year]', '$_POST[status]', '$ts', '$orchestration', '$_POST[info]', 1)";
      } else
      {
         if ($delete != NULL)
         {
            $query = "DELETE from project WHERE project.id = $no";
         } else
         {
            $query = "update project set name = '$_POST[name]'," .
                    "semester = '$_POST[semester]'," .
                    "year = '$_POST[year]'," .
                    "status = '$_POST[status]'," .
                    "deadline = '$ts', " .
                    "orchestration = '$orchestration', " .
                    "info = '$_POST[info]' " .
                    "where id = $no";
         }
         $no = NULL;
      }
      mysql_query($query);
   }
}

$query = "SELECT project.id as id, name, semester, year, status, " .
        "deadline, orchestration, info " .
        "FROM project " .
        "where project.year >= $sel_year " .
        "order by ${sort}";

$result = mysql_query($query);

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
   if ($row[id] != $no)
   {
      echo "<tr>
        <td><center>
            <a href=\"{$php_self}?_sort={$sort}&from=$sel_year&_action=view&_no={$row[id]}\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>
             </center></td>" .
      "<td><a href=\"plan.php?id_project={$row[id]}\" target=content>{$row[name]}</a></td>" .
      "<td>{$row[semester]} " .
      "    {$row[year]}</td>" .
      "<td>" . $prj_stat[$row[status]] . "</td>" .
      "<td>" . date('D j.M y', $row[deadline]) . "</td>" .
      "<td>";
      if ($row[orchestration] == $prj_orch_tutti)
         echo "<center><img src=\"images/tick2.gif\" border=0></center>";
      echo "</td><td>";

      echo str_replace("\n", "<br>\n", $row[info]);
      echo "</td>" .
      "</tr>";
   }
   else
   {
      echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <input type=hidden name=from value='$sel_year'>
    <th nowrap><input type=submit value=ok title=\"Lagere endring\" >
    <input type=submit value=del name=_delete title=\"Slett prosjekt\" onClick=\"return confirm('Sikkert at du vil slette {$row[name]}?');\"></th>
    <th><input type=text size=20 name=name value=\"{$row[name]}\"></th>
    <th nowrap>";
      select_semester($row[semester]);
      echo "<input type=text size=4 maxlength=4 name=year value=\"{$row[year]}\"></th>
    <th>";
      select_status($row[status]);
      echo "</th>";
      echo "<td><input type=text size=10 name=deadline value=\"" . date('j.M.y', $row[deadline]) . "\"></td>";
      echo "<th><input type=checkbox name=orchestration";
      if ($row[orchestration] == $prj_orch_tutti)
         echo " checked";
      echo "></th>";
      echo " </td>
    <th><textarea cols=44 rows=10 wrap=virtual name=info>{$row[info]}</textarea></th>
    </tr>";
   }
}
?> 

</table>
</form>

<?php
require 'framework_end.php';
?>

