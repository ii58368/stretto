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
      <a href=calendar.php title=\"Vis kalender slik den ser ut på eksternsiden...\"><img src=images/text2.gif border=0></a>
    </form>";
echo "
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
      echo ">" . $e['name'] . " (" . $e['semester'] . $e['year'] . ")</option>";
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
      echo ">" . $e['name'] . "</option>";
   }
   echo "</select>";
}

function get_img($id_project)
{
   $path = "project/" . $id_project . "/img/";
   $abs_file = "images/image2.gif";

   if (file_exists($path))
   {
      if ($handle = opendir($path))
      {
         while (($file = readdir($handle)))
         {
            if (is_file($path . $file))
               $abs_file = $path . $file;
         }
         closedir($handle);
      }
   }
   return $abs_file;
}

function del_img($id_project)
{
   $path = "project/" . $id_project . "/img/";

   if ($handle = opendir($path))
   {
      while (($file = readdir($handle)))
      {
         if (is_file($path . $file))
            unlink($path . $file);
      }
      closedir($handle);
   }
}

function new_img()
{
   global $sort;
   global $php_self;
   global $no;

   echo "
    <form action=$php_self method=post enctype=multipart/form-data>
    <input type=hidden name=_action value=img_upload>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=hidden name=_no value=\"$no\">
    <input type=file name=filename id=filename title=\"Velg ny figur som skal lastes opp...\"><br>
    <input type=submit value=\"Last opp\" title=\"Last opp figuren som er valgt med knappen over. Den forrige figuren vil bli overskrevet.\">
    </form>";
}

function store_img($id_project)
{
   $path = "project/" . $id_project . "/img";

   if (!is_dir($path))
      mkdir($path, 0755, true);

   $dst_file = $path . "/" . $_FILES['filename']['name'];

   if ($_FILES['filename']['size'] > 20 * 1024 * 1024)
   {
      echo "<font color=red>File too large! (>20MB)</font>";
   }
   else
   {
      if (!move_uploaded_file($_FILES['filename']['tmp_name'], $dst_file))
      {
         echo "<font color=red>Failed to upload!</font>";
      }
   }
}

function manage_img($id_project, $cno)
{
   global $action;
   global $sort;
   global $php_self;
   global $no;

   if ($no == $cno && $action == 'img_new')
   {
      new_img();
   }
   else
   {
      if ($no == $cno && $action == 'img_upload')
      {
         del_img($id_project);
         store_img($id_project);
      }
      $fname = get_img($id_project);
      echo "<a href=\"$php_self?_sort=$sort&_action=img_new&_no=$cno\" title=\"Klikk for å laste opp ny figur...\"><img src=\"$fname\" height=30></a>";
   }
}

if ($action == 'new')
{
   echo "  <tr>
    <form action='$php_self' method=post>
    <td align=left>
    <input type=hidden name=_action value=update>
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
    </form>
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
              values ($ts, '" . $_POST['time'] . "', " . $_POST['id_project'] . ", " . $_POST['id_location'] . ", " . $db->qpost('heading') . ", " . $db->qpost('text') . ")";
      }
      else
      {
         if (!is_null($delete))
         {
            $query = "DELETE FROM concert WHERE id = $no";
         }
         else
         {
            $query = "update concert set ts = $ts," .
                    "time = '" . $_POST['time'] . "'," .
                    "id_project = " . $_POST['id_project'] . "," .
                    "id_location = " . $_POST['id_location'] . "," .
                    "heading = " . $db->qpost('heading') . ", " .
                    "text = " . $db->qpost('text') . " " .
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
   if ($row['id'] == $no && $action == 'view')
   {
      echo "<tr>
    <form action='$php_self' method=post>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <td nowrap><input type=submit value=ok title=\"Lagre\">
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette " . strftime('%a %e.%b %y', $row['ts']) . "?');\" title=\"Slette...\"></td>
    <td><input type=date size=10 name=ts value=\"" . date('Y-m-d', $row['ts']) . "\" title=\"Konsertdato\"></td>
    <td><input type=text size=5 maxlength=5 name=time value=\"" . $row['time'] . "\" title=\"Klokkeslett\">
    <td>";
      select_project($row['id_project']);
      echo "</td>
    <td>";
      select_location($row['id_location']);
      echo "</td>
    <td><input type=text size=30 name=heading value=\"" . $row['heading'] . "\" title=\"Konsertoverskrift\">
    <td><textarea cols=60 rows=10 wrap=virtual name=text title=\"Konsertinformasjon, fritekst\">" . $row['text'] . "</textarea></td>
    </form>
    </tr>";
   }
   else
   {
      echo "<tr>";
      if ($access->auth(AUTH::CONS))
         echo "
         <td><center>
           <a href=\"$php_self?_sort=$sort&_action=view&_no=" . $row['id'] . "\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for å editere...\"></a>
             </center></td>";
      echo
      "<td>" . strftime('%a %e.%b %y', $row['ts']) . "</td>" .
      "<td>" . $row['time'] . "</td>" .
      "<td>" . $row['pname'] . " (" . $row['semester'] . $row['year'] . ")</td>" .
      "<td>" . $row['lname'] . "</td>" .
      "<td>" . $row['heading'] .
      "<br>";
      manage_img($row['id_project'], $row['id']);
      echo "</td><td>";
      $body = str_replace("\n", "<br>\n", $row['text']);
      $body = replace_links($body);
      echo $body;
      echo "</td>" .
      "</tr>";
   }
}
?> 

</table>

