<html>
  <head>
    <title>Regiplan</title>
    <LINK href="style.css" rel="stylesheet" type="text/css">
  </head>

<?php

include 'config.php';
include 'opendb.php';

include 'request.php';



function mk_resources_str($id_plan)
{
  if ($_POST[id_reponsible] != NULL)
  {
    $query = "select forname, lastname from person where id = $_POST[id_responsible]";
    $req = mysql_query($query);
    if ($row = mysql_fetch_array($req, MYSQL_ASSOC))
      $title = "Ansvarlig: $row[forname], $row[lastname], ";
  }

  if ($_POST[id_persons] != null)
  {
    foreach($_POST[id_persons] as $id_person)
    {
      $query = "select forname, lastname from person where id = $id_person";
      $req = mysql_query($query);
      if ($row = mysql_fetch_array($req, MYSQL_ASSOC))
        $title += "Regi: $row[forname] $row[lastname], ";
    }
    $req += "id_persons=$id_person&";
  }

  if ($_POST[id_instruments] != null)
  {
    foreach($_POST[id_instruments] as $id_instrument)
    {
      $query = "select instrument from instruments where id = $id_instrument";
      $req = mysql_query($query);
      if ($row = mysql_fetch_array($req, MYSQL_ASSOC))
        $title += "$row[instrument], ";
    }
    $req += "id_instruments=$id_instrument&";
  }
  return "";
}



function resources_list($id_plan)
{
  $q  = "SELECT firstname, lastname, table_ok, instrument, resource.status as status, shift.status as shift_status " .
   "FROM person, instruments, resource, shift, project, plan " .
   "where person.id = resource.id_person " .
   "and id_instrument = instruments.id " .
   "and shift.id_person = person.id " .
   "and shift.id_project = project.id " .
   "and project.id = plan.id_project " .
   "and plan.id = resource.id_plan " .
   "and resource.id_plan = ${id_plan} " .
   "and resource.status = '1' " .
   "order by lastname, firstname";

  $r = mysql_query($q);

  while($e = mysql_fetch_array($r, MYSQL_ASSOC))
  {
    if ($e[shift_status] == 1)
      echo "<font color=grey>";
    if ($e[shift_status] == 3)
      echo "<strike>";
    echo $e[firstname] . " " . $e[lastname] . " (" . $e[instrument] . ")";
    if ($e[shift_status] == 1)
      echo "</font>";    
    if ($e[shift_status] == 3)
      echo "</strike>";
    if ($e[table_ok] == NULL)
      echo "<image src=/images/chair-minus-icon.png border=0 title=\"Kan ikke l&oslash;fte bord\">";
    echo "<br>";
  }

  $q = "select instrument from instruments, gresource " .
       "where instruments.id = gresource.id_instrument " .
       "and gresource.id_plan = ${id_plan} " .
       "and gresource.status = 1 " .
       "order by instruments.list_order";

  $r = mysql_query($q);

  while($e = mysql_fetch_array($r, MYSQL_ASSOC))
    echo "$e[instrument]<br>";
}

echo "
  <body BGCOLOR=FFFFF4 TEXT=000000 LINK=00009F VLINK=008B00 ALINK=890000>
    <h1>Regiplan</h1>";
echo "
    <form action='$php_self' method=post>
      <input type=hidden name=_action value=new>
      <input type=hidden name=id_project value='$_REQUEST[id_project]'>
      <input type=submit value=\"Ny aktivitet\">
    </form>
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
  echo "<tr>
  <form action='$php_self' method=post>
    <td align=left><input type=hidden name=_action value=update>
    <input type=submit value=ok></td>
    <th><input type=text size=10 name=date title=\"Format: <dato>. <mnd> [<&aring;r>] Merk: M&aring;ned p&aring; engelsk. Eksempel: 12. dec\"></th>
    <th nowrap>";
  select_tsort(null);
  echo "<input type=text size=10 name=time value=\"18:10\"></th>
    <th>";
  select_location(1);
  echo "</th>
    <th>";
  select_project($_REQUEST[id_project]);
  echo "
  </th>
    <th></th>
    <th><textarea cols=50 rows=6 wrap=virtual name=comment>Opprigg til vanlig orkesterpr&oslash;ve</textarea></th>
  </form>
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
      $query2 = "select id_def_responsible from project where id = $_POST[id_project]";
      $result = mysql_query($query2);
      $row = mysql_fetch_array($result, MYSQL_ASSOC);

      $query = "insert into plan (date, tsort, time, id_location, id_project, " .
    "id_responsible, comment) " .
    "values ('$ts', '$_POST[tsort]', '$_POST[time]', " .
    "'$_POST[id_location]', '$_POST[id_project]', '$row[id_def_responsible]', " .
    "'$_POST[comment]')";
    }
    else
    {
      if ($delete != NULL)
      { 
        $query = "DELETE FROM plan WHERE id = $no";
        $query2 = "delete from resource where id_plan = $no";
        mysql_query($query2); 
        $query2 = "delete from gresource where id_plan = $no";
        mysql_query($query2); 
      } 
      else
      {
        $query = "update plan set date = '$ts'," .
          "time = '$_POST[time]'," .
          "tsort = '$_POST[tsort]'," .
          "id_location = '$_POST[id_location]'," .
          "id_project = '$_POST[id_project]'," .
          "id_responsible = '$_POST[id_responsible]'," .
          "comment = '$_POST[comment]'" .
          "where id = $no"; 
        resources_update($no);
      }
      $no = NULL; 
    } 
    mysql_query($query);
  }
}   


if ($action == 'add')
{
/*
  $query = "select shift.id_person as id_person " .
           "from shift, project " .
           "where shift.id_project = project.id " .
           "and shift.status = 2 " .
           "and project.id = $_REQUEST[id_project] " .
           "and shift.id_person not in (select resource.id_person from resource " .
           "where resource.id_plan = $no)";
*/
  $query = "select id from instruments " .
           "where instruments.direction = '*'";
  $result = mysql_query($query);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC))
  {
    $q = "insert into gresource (id_instrument, id_plan, status) " .
         "values ('$row[id]', '$no', 1) ";
    mysql_query($q);
  }
}

$cur_year = ($_REQUEST[id_project] == '%') ? date("Y") : 0;

$query  = "SELECT plan.id as id, date, time, tsort, id_project, id_location, location.name as lname, project.name as pname, location.url as url, id_responsible, " .
    "firstname, lastname, plan.comment as comment " .
    "FROM person, project, plan, location " .
    "where id_location = location.id " .
    "and id_project = project.id " .
    "and id_responsible = person.id " .
    "and plan.id_project like '$_REQUEST[id_project]' " .
    "and project.year >= $cur_year " .
    "order by date,tsort,time";
$result = mysql_query($query);

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
  if ($row[id] != $no || $action != 'view')
  {
    echo "<tr>
        <td><center>
            <a href=\"{$php_self}?_action=view&_no={$row[id]}&id_project=$_REQUEST[id_project]\"><img src=\"/images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>
             </center></td>" .
        "<td>" . date('D j.M y', $row[date]) . "</td>" .
        "<td>{$row[time]}</td><td>";
   if (strlen($row[url]) > 0)
     echo "<a href=\"{$row[url]}\">{$row[lname]}</a>";
   else 
     echo $row[lname];
   echo "</td><td>{$row[pname]}</td>" .
        "<td nowrap><b>{$row[firstname]} {$row[lastname]}</b><a href=\"{$php_self}?_action=add&_no={$row[id]}&id_project=$_REQUEST[id_project]\"><img src=\"/images/user_male_add2.png\" border=0 title=\"Legg til regigruppen\"></a><br>";
    resources_list($row[id]);
    echo "</td><td>";
    echo str_replace("\n", "<br>\n", $row[comment]);
    echo "</td>" .
        "</tr>";
  }
  else
  {
    echo "<tr>
    <form action='{$php_self}' method=post>
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
  echo "</th>
    <th>";
  select_project($row[id_project]);
  echo "</th>
    <th>";
  select_person($row[id_responsible]);
  echo "<br>";
  resources_select($row[id]);
  echo "</th>
    <th><textarea cols=50 rows=6 wrap=virtual name=comment>{$row[comment]}</textarea></th>
    </form>
    </tr>";
  }
} 

include 'closedb.php';

?> 

    </table>
  </body>
</html>

