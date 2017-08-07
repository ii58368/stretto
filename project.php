
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
   global $db;
   
   if (is_null($selected))
      $selected = $db->prj_stat_draft;

   echo "<select name=status>";

   for ($i = 0; $i < count($db->prj_stat); $i++)
   {
      echo "<option value=$i";
      if ($selected == $i)
         echo " selected";
      echo ">" . $db->prj_stat[$i] . "</option>\n";
   }

   echo "</select>";
}

function select_valid_par_stat($valid_par_stat)
{
   global $db;
   
   echo "<select size=" . sizeof($db->par_stat) . " name=\"valid_par_stat[]\" multiple title=\"Ctrl-click to select/unselect single\">";

   for ($i = 0; $i < sizeof($db->par_stat); $i++)
   {
      echo "<option value=\"" . $i . "\"";
      if ($valid_par_stat & (1 << $i))
         echo " selected";
      echo ">" . $db->par_stat[$i];
   }
   echo "</select>";
}

$sel_year = ($_REQUEST[from] == NULL) ? date("Y") : intval($_REQUEST[from]);
$prev_year = $sel_year - 1;

echo "
    <h1>Prosjekt</h1>";
if ($access->auth(AUTH::PRJ))
   echo "
    <form action='$php_self' method=post>
      <input type=hidden name=_sort value='$sort'>
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Nytt prosjekt\" title=\"Definer nytt prosjekt\" >
    </form>";
echo "
    <form action='$php_self' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::PRJ))
   echo "
      <th bgcolor=#A6CAF0>Edit</th>";
echo "
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=name,id&from=$sel_year\" title=\"Sorter p&aring; prosjektnavn\">Prosjekt</a></th>
      <th bgcolor=#A6CAF0 nowrap><a href=\"$php_self?_sort=year,semester+DESC,id&from=$sel_year\" title=\"Sorter p&aring; semester\">Sem</a>
           <a href=\"$php_self?from=$prev_year&_sort={$sort}\"><img src=images/arrow_up.png border=0 title=\"Forrige &aring;r...\"></a></th>
      <th bgcolor=#A6CAF0>Status</th>
      <th bgcolor=#A6CAF0>På-/avm.frist</th>
      <th bgcolor=#A6CAF0>Tutti</th>
      <th bgcolor=#A6CAF0>På-/avmeld.</th>
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
    <th><input type=text size=10 name=deadline value=\"" . 
    date('j.M y', time() + 60*60*24*7*12) .    // Default dealine: 12 weeks from now
    "\" title=\"Format: <dato>. <mnd> [<&aring;r>] Merk: M&aring;ned p&aring; engelsk. Eksempel: 12. dec\"></th>
    <th><input type=checkbox name=orchestration></th>
    <th>";
   select_valid_par_stat((1 << $db->par_stat_no) | (1 << $db->par_stat_yes));
   echo "</td>
     <th><textarea cols=44 rows=10 wrap=virtual name=info></textarea></th>
  </tr>";
}

if ($action == 'update' && $access->auth(AUTH::PRJ))
{
   $orchestration = ($_POST[orchestration] == null) ? $db->prj_orch_reduced : $db->prj_orch_tutti;

   $valid_par_stat = 0;
   if ($_POST[valid_par_stat] != null)
      foreach ($_POST[valid_par_stat] as $idx)
         $valid_par_stat |= (1 << $idx);

   if (($ts = strtotime($_POST[deadline])) == false)
   {
      echo "<font color=red>Illegal time format: " . $_POST[deadline] . "</font>";
   } else
   {
      if ($no == NULL)
      {
         $query = "insert into project (name, semester, year, status, deadline, orchestration, info, id_person, valid_par_stat) " .
                 "values ('$_POST[name]', '$_POST[semester]', " .
                 "'$_POST[year]', '$_POST[status]', '$ts', '$orchestration', '$_POST[info]', 1, $valid_par_stat)";
         $db->query($query);
         mkdir("project/" . $db->lastInsertId() . "/rec", 0755, true);
         mkdir("project/" . $db->lastInsertId() . "/doc", 0755, true);
         mkdir("project/" . $db->lastInsertId() . "/sheet", 0755, true);
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
                    "valid_par_stat = $valid_par_stat, " .
                    "info = '$_POST[info]' " .
                    "where id = $no";
         }
         $db->query($query);
         $no = NULL;
      }
   }
}

$query = "SELECT project.id as id, name, semester, year, status, " .
        "deadline, orchestration, valid_par_stat, info " .
        "FROM project " .
        "where project.year >= $sel_year " .
        "order by ${sort}";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($row[id] != $no)
   {
      echo "<tr>";
      if ($access->auth(AUTH::PRJ))
         echo "
        <td><center>
            <a href=\"{$php_self}?_sort={$sort}&from=$sel_year&_action=view&_no={$row[id]}\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>
             </center></td>";
      echo
      "<td><a href=\"plan.php?id_project={$row[id]}\">{$row[name]}</a></td>" .
      "<td>{$row[semester]} " .
      "    {$row[year]}</td>" .
      "<td>" . $db->prj_stat[$row[status]] . "</td>" .
      "<td>" . date('D j.M y', $row[deadline]) . "</td>" .
      "<td>";
      if ($row[orchestration] == $db->prj_orch_tutti)
         echo "<center><img src=\"images/tick2.gif\" border=0></center>";
      echo "</td><td>";
      for ($i = 0; $i < sizeof($db->par_stat); $i++)
         if ($row[valid_par_stat] & (1 << $i))
            echo $db->par_stat[$i] . "<br>\n";
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
      if ($row[orchestration] == $db->prj_orch_tutti)
         echo " checked";
      echo "></th>\<td>";
      select_valid_par_stat($row[valid_par_stat]);
      echo " </td>
    <td><textarea cols=44 rows=10 wrap=virtual name=info>{$row[info]}</textarea></td>
    </tr>";
   }
}
?> 

</table>
</form>

<?php
require 'framework_end.php';
?>

