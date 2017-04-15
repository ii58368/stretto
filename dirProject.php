<?php
require 'framework.php';

if ($sort == NULL)
   $sort = 'year,semester DESC';

function list_group($id)
{
   global $shi_stat_tentative;
   global $shi_stat_confirmed;
   global $shi_stat_failed;

   global $per_dir_nocarry;

   $q = "SELECT firstname, lastname, status_dir, instrument, shift.status as status, " .
           "shift.comment as shift_comment " .
           "FROM person, instruments, shift " .
           "where person.id = shift.id_person " .
           "and id_instruments = instruments.id " .
           "and shift.id_project = ${id} " .
           "and (shift.status >= $shi_stat_tentative and shift.status <= $shi_stat_failed) " .
           "order by list_order, lastname, firstname";

   $r = mysql_query($q);

   while ($e = mysql_fetch_array($r, MYSQL_ASSOC))
   {
      if ($e[status] == $shi_stat_tentative)
         echo "<font color=grey>";
      if ($e[status] == $shi_stat_failed)
         echo "<strike>";
      echo $e[firstname] . " " . $e[lastname] . " (" . $e[instrument] . ")";
      if ($e[status] == $shi_stat_failed)
         echo "</font>";
      if ($e[status] == $shi_stat_tentative)
         echo "</strike>";
      if ($e[status_dir] == $per_dir_nocarry)
         echo "<image src=images/chair-minus-icon.png border=0 title=\"Kan ikke l&oslash;fte bord\">";
      echo "<br>";
   }
}

function select_person($selected)
{
   global $per_stat_member;

   $q = "SELECT person.id as id, firstname, lastname, instrument FROM person, instruments " .
           "where status = ${per_stat_member} and id_instruments = instruments.id " .
           "order by list_order, lastname, firstname";

   $r = mysql_query($q);

   while ($e = mysql_fetch_array($r, MYSQL_ASSOC))
   {
      echo "<option value=\"" . $e[id] . "\"";
      if ($e[id] == $selected)
         echo " selected";
      echo ">" . $e[firstname] . " " . $e[lastname] . " (" . $e[instrument] . ")";
   }
}

function mail2dir($id_project)
{
   global $shi_stat_tentative;
   global $shi_stat_confirmed;

   $q = "select name from project where id = ${id_project}";
   $r = mysql_query($q);
   $e = mysql_fetch_array($r, MYSQL_ASSOC);
   $project_name = $e[name];

   $q = "select email, phone1 from person, shift " .
           "where person.id = shift.id_person " .
           "and shift.id_project = ${id_project} " .
           "and (shift.status = ${shi_stat_tentative} or shift.status = ${shi_stat_confirmed})";
   $r = mysql_query($q);

   echo "<a href=\"mailto:";
   while ($e = mysql_fetch_array($r, MYSQL_ASSOC))
      echo $e[email] . ",";
   echo "?subject=OSO: Regikomit&eacute;, $project_name&body=Se oppdatert regiplan: http://" . $_SERVER['SERVER_NAME'] . "/oso/regi/plan.php?id_project=$id_project\"><image border=0 src=images/image1.gif hspace=20 title=\"Send mail alle i regikomit&eacute;en\"></a>";

   echo "<a href=\"sms:";
   mysql_data_seek($r, 0);
   while ($e = mysql_fetch_array($r, MYSQL_ASSOC))
      $str .= $e[phone1] . ",";
   $str = str_replace(' ', '', $str);
   echo substr($str, 0, -1);
   echo "&body=OSO Regikomit&eacute:\"><image border=0 src=images/sms.png hspace=20 title=\"Send SMS til alle i regikomit&eacute;en\"></a>";
}

$sel_year = ($_REQUEST[from] == NULL) ? date("Y") : intval($_REQUEST[from]);
$prev_year = $sel_year - 1;

echo "
    <h1>Regiprosjekt</h1>
    <form action='$php_self' method=post>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Edit</th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=name,id&from=$sel_year\" title=\"Sorter p&aring; prosjektnavn\">Prosjekt</a></th>
      <th bgcolor=#A6CAF0 nowrap><a href=\"$php_self?_sort=year,semester+DESC,id&from=$sel_year\" title=\"Sorter p&aring; semester\">Sem</a>
           <a href=\"$php_self?from=$prev_year&_sort={$sort}\"><img src=images/arrow_up.png border=0 title=\"Forrige &aring;r...\"></a></th>
      <th bgcolor=#A6CAF0>Status</th>
      <th bgcolor=#A6CAF0>Regiansvarlig</th>
      <th bgcolor=#A6CAF0>Regikomit&eacute;</th>
      <th bgcolor=#A6CAF0>Generell info</th>
    </tr>";


if ($action == 'update')
{
   $query = "update project set " .
           "id_person = '$_POST[id_person]'," .
           "info_dir = '$_POST[info_dir]' " .
           "where id = $no";
   $query2 = "delete from shift " .
           "where id_person = $_POST[current_id_person] " .
           "and id_project = $no";
   mysql_query($query2);
   $query2 = "insert into shift (id_person, id_project, status) " .
           "values ($_POST[id_person], $no, ${shi_stat_responsible})";
   mysql_query($query2);
   $query2 = "update shift set status = ${shi_stat_responsible} " .
           "where id_person = $_POST[id_person] " .
           "and id_project = $no";
   $no = NULL;
   mysql_query($query);
}

$query = "SELECT project.id as id, name, semester, year, id_person, project.status as status, " .
        "firstname, lastname, instrument, info_dir " .
        "FROM person, project, instruments " .
        "where project.id_person = person.id " .
        "and id_instruments = instruments.id " .
        "and project.year >= $sel_year " .
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
      "<td><a href=\"dirPlan.php?id_project={$row[id]}\">{$row[name]}</a></td>" .
      "<td>{$row[semester]} " .
      "    {$row[year]}</td>" .
      "<td>" . $prj_stat[$row[status]] . "</td>" .
      "<td>{$row[firstname]} {$row[lastname]} ({$row[instrument]})</td>" .
      "<td nowrap>";
      mail2dir($row[id]);
      echo "<br>";
      list_group($row[id]);
      echo "</td><td>";
      echo str_replace("\n", "<br>\n", $row[info_dir]);
      echo "</td>" .
      "</tr>";
   } else
   {
      echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <input type=hidden name=from value='$sel_year'>
    <input type hidden name=current_id_def_responsible value=$row[id_def_responsible]>
    <th nowrap><input type=submit value=ok title=\"Lagere endring\" >
    <th>$row[name]</th>
    <th>$row[semester] $row[year]</th>
    <th>" . $prj_stat[$row[status]] . "</th>
    <th><select name=id_person>";
      select_person($row[id_person]);
      echo "</select></th>";
      echo "<td>";
      list_group($row[id]);
      echo " </td>
    <th><textarea cols=44 rows=10 wrap=virtual name=info_dir>{$row[info_dir]}</textarea></th>
    </tr>";
   }
}
?> 

</table>
</form>

<?php
require 'framework_end.php';
?>

