<?php
require 'framework.php';

if (is_null($sort))
   $sort = 'year,semester DESC';

function list_group($id)
{
   global $db;
   global $shi_stat_tentative;
   global $shi_stat_confirmed;
   global $shi_stat_failed;

   global $per_dir_nocarry;

   $q = "SELECT firstname, lastname, status_dir, instrument, participant.stat_dir as status, " .
           "participant.comment_dir as shift_comment " .
           "FROM person, instruments, participant " .
           "where person.id = participant.id_person " .
           "and person.id_instruments = instruments.id " .
           "and participant.id_project = ${id} " .
           "and (participant.stat_dir >= $shi_stat_tentative and participant.stat_dir <= $shi_stat_failed) " .
           "order by list_order, lastname, firstname";

   $s = $db->query($q);

   foreach($s as $e)
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
   global $db;

   $q = "SELECT person.id as id, firstname, lastname, instrument FROM person, instruments " .
           "where status = $db->per_stat_member and id_instruments = instruments.id " .
           "order by list_order, lastname, firstname";

   $s = $db->query($q);

   foreach($s as $e)
   {
      echo "<option value=\"" . $e[id] . "\"";
      if ($e[id] == $selected)
         echo " selected";
      echo ">" . $e[firstname] . " " . $e[lastname] . " (" . $e[instrument] . ")";
   }
}

function mail2dir($id_project)
{
   global $db;

   $q = "select name from project where id = ${id_project}";
   $s = $db->query($q);
   $e = $s->fetch(PDO::FETCH_ASSOC);
   $project_name = $e[name];

   $q = "select email, phone1 from person, participant " .
           "where person.id = participant.id_person " .
           "and participant.id_project = ${id_project} " .
           "and (participant.stat_dir = $db->shi_stat_tentative or participant.stat_dir = $db->shi_stat_confirmed)";
   $s = $db->query($q);
   $r = $s->fetchAll(PDO::FETCH_ASSOC);

   echo "<a href=\"mailto:";
   foreach($r as $e)
      echo $e[email] . ",";
   echo "?subject=OSO: Regikomit&eacute;, $project_name&body=Se oppdatert regiplan: http://" . $_SERVER['SERVER_NAME'] . "/oso/regi/plan.php?id_project=$id_project\"><image border=0 src=images/image1.gif hspace=20 title=\"Send mail alle i regikomit&eacute;en\"></a>";

   echo "<a href=\"sms:";
   reset($r);
   foreach($r as $e)
      $str .= $e[phone1] . ",";
   $str = str_replace(' ', '', $str);
   echo substr($str, 0, -1);
   echo "&body=OSO Regikomit&eacute:\"><image border=0 src=images/sms.png hspace=20 title=\"Send SMS til alle i regikomit&eacute;en\"></a>";
}

$sel_year = is_null($_REQUEST[from]) ? date("Y") : intval($_REQUEST[from]);
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
   $query2 = "update participant set stat_dir = $db->shi_stat_free "
           . "where stat_dir = $db->shi_stat_responsible "
           . "and id_project = $no";
   $db->query($query2);
   $query2 = "update participant set stat_dir = $db->shi_stat_responsible " .
           "where id_person = $_POST[id_person] " .
           "and id_project = $no";
   $db->query($query2);
   $db->query($query);
   
   $no = NULL;
}

$query = "SELECT project.id as id, name, semester, year, id_person, project.status as status, " .
        "firstname, lastname, instrument, info_dir " .
        "FROM person, project, instruments " .
        "where project.id_person = person.id " .
        "and id_instruments = instruments.id " .
        "and project.year >= $sel_year " .
        "order by ${sort}";

$stmt = $db->query($query);

foreach($stmt as $row)
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
    <input type hidden name=id_person value=$row[id_person]>
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

