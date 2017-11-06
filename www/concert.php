<?php
require 'framework.php';

if (is_null($sort))
   $sort = 'ts';

echo "
<h1>Konserter " . $season->semester(1) . " " . $season->year() . "</h1>";
if ($access->auth(AUTH::CONS))
   echo "
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Ny konsert\" title=\"Registrer ny konsert...\">
      <a href=calender.php title=\"Vis kalender slik den ser ut på eksternsiden...\"><img src=images/text2.gif border=0></a>
    </form>";
echo "
    <form action='$php_self' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::CONS))
   echo "
      <th>Edit</th>";
echo "
      <th><a href=\"$php_self?_sort=ts\" title=\"Sorter på konsertdato\">Dato</a></th>
      <th>Tid</th>
      <th><a href=\"$php_self?_sort=project.name\" title=\"Sortet på prosjektnavn\">Prosjekt</a></th>
      <th>Lokale</th>
      <th>Overskrift</th>
      <th>Tekst</th>
      </tr>";

function select_project($selected)
{
   global $db;
   global $season;

   echo "<select name=id_project title=\"Velg aktuelt prosjekt...\">";

   $q = "SELECT id, name, semester, year "
           . "FROM project "
           . "where year >= " . $season->year() . " "
           . "order by year, semester DESC, id ";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         echo " selected";
      echo ">".$e['name']." (".$e['semester'].$e['year'].")</option>";
   }
   echo "</select>";
}

function select_location($selected)
{
   global $db;

   echo "<select name=id_location title=\"Velg aktuelt konsertlokasjon...\">";

   $q = "SELECT id, name "
           . "FROM location "
           . "order by name ";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         echo " selected";
      echo ">".$e['name']."</option>";
   }
   echo "</select>";
}

if ($action == 'new')
{
   echo "  <tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=submit value=ok title=\"Lagre\"></td>
    <td><input type=date size=10 name=ts title=\"Konsertdato, format: eks: 23. dec 2018\">
    <td><input type=text size=5 maxlength=5 name=time title=\"Klokkeslett, fritt format\">
    <td>";
   select_project(null);
   echo "</td>
       <td>";
   select_location(null);
   echo "<td><input type=text size=30 name=heading title=\"Konsertoverskrift\">\n";
   echo "</td>
    <td><textarea name=text wrap=virtual cols=60 rows=10 title=\"Konsertinformasjon, fritekst\"></textarea></td>
  </tr>";
}

if ($action == 'update' && $access->auth(AUTH::CONS))
{
   if (($ts = strtotime($_POST['ts'])) == false)
      echo "<font color=red>Illegal time format: " . $_POST['ts'] . "</font>";
   else
   {
      if (is_null($no))
      {
         $query = "insert into concert (ts, time, id_project, id_location, heading, text)
              values ($ts, '".$_POST['time']."', ".$_POST['id_project'].", ".$_POST['id_location'].", ".$db->qpost('heading').", ".$db->qpost('text').")";
      } else
      {
         if (!is_null($delete))
         {
            $query = "DELETE FROM concert WHERE id = $no";
         } else
         {
            $query = "update concert set ts = $ts," .
                    "time = '".$_POST['time']."'," .
                    "id_project = ".$_POST['id_project']."," .
                    "id_location = ".$_POST['id_location']."," .
                    "heading = ".$db->qpost('heading').", " .
                    "text = ".$db->qpost('text')." " .
                    "where id = $no";
         }
         $no = NULL;
      }
      $db->query($query);
   }
}

$query = "SELECT concert.id as id, "
        . "concert.ts as ts, "
        . "concert.time as time, "
        . "project.name as pname, "
        . "project.year as year, "
        . "project.semester as semester, "
        . "location.name as lname, "
        . "concert.heading as heading, "
        . "concert.text as text, "
        . "concert.id_location as id_location, "
        . "concert.id_project as id_project "
        . "from concert, location, project "
        . "where concert.id_project = project.id "
        . "and concert.id_location = location.id "
        . "and project.year = " . $season->year() . " "
        . "and project.semester = '" . $season->semester() . "' "
        . "order by $sort";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($row['id'] != $no)
   {
      echo "<tr>";
      if ($access->auth(AUTH::CONS))
         echo "
         <td><center>
           <a href=\"$php_self?_sort=$sort&_action=view&_no=".$row['id']."\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for å editere...\"></a>
             </center></td>";
      echo
      "<td>" . strftime('%a %e.%b %y', $row['ts']) . "</td>" .
      "<td>".$row['time']."</td>" .
      "<td>".$row['pname']." (".$row['semester'].$row['year'].")</td>" .
      "<td>".$row['lname']."</td>" .
      "<td>".$row['heading']."</td>" .
      "<td>";
      echo str_replace("\n", "<br>\n", $row['text']);
      echo "</td>" .
      "</tr>";
   } else
   {
      echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <td nowrap><input type=submit value=ok title=\"Lagre\">
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette " . strftime('%a %e.%b %y', $row['ts']) . "?');\" title=\"Slette...\"></td>
    <td><input type=date size=10 name=ts value=\"" . date('Y-m-d', $row['ts']) . "\" title=\"Konsertdato\"></td>
    <td><input type=text size=5 maxlength=5 name=time value=\"".$row['time']."\" title=\"Klokkeslett\">
    <td>";
      select_project($row['id_project']);
      echo "</td>
    <td>";
      select_location($row['id_location']);
      echo "</td>
    <td><input type=text size=30 name=heading value=\"".$row['heading']."\" title=\"Konsertoverskrift\">
    <td><textarea cols=60 rows=10 wrap=virtual name=text title=\"Konsertinformasjon, fritekst\">".$row['text']."</textarea></td>
    </tr>";
   }
}
?> 

</table>
</form>
