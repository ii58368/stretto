<?php
    require 'framework.php';
    
    if ($sort == NULL)
        $sort = 'groups.name';
    
  echo "
    <h1>Grupper</h1>
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Ny gruppe\">
    </form>
    <form action='$php_self' method=post>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Edit</th>
      <th bgcolor=#A6CAF0>Navn</th>
      <th bgcolor=#A6CAF0>Ansvarlig</th>
      <th bgcolor=#A6CAF0>Medlemmer</th>
      <th bgcolor=#A6CAF0>Kommentar</th>
      </tr>";

function member_select($id_groups)
{
  global $per_stat_quited;
   
  $q  = "SELECT person.id as id, firstname, lastname, instrument " .
        "FROM person, instruments " .
        "where instruments.id = person.id_instruments " .
        "and not person.status = $per_stat_quited " .
        "order by instruments.list_order, lastname, firstname";
  $r = mysql_query($q);

  $q2  = "SELECT id_person FROM member where id_groups = $id_groups";
  $r2 = mysql_query($q2);
  
  echo "<select name=\"id_persons[]\" multiple title=\"Velg personer inn i gruppen.\nCtrl-click to select/unselect single\">";

  while($e = mysql_fetch_array($r, MYSQL_ASSOC))
  {
    echo "<option value=\"" . $e[id] . "\"";
    if (mysql_num_rows($r2) > 0)
      mysql_data_seek($r2, 0);
    while($e2 = mysql_fetch_array($r2, MYSQL_ASSOC))
      if ($e[id] == $e2[id_person])
        echo " selected";
    echo ">$e[firstname] $e[lastname] ($e[instrument])";
  }
  echo "</select>";
}


function member_list($id_groups)
{
  global $per_stat_quited;
  
  $q  = "SELECT firstname, lastname, instrument " .
        "FROM person, instruments, member, groups " .
        "where instruments.id = person.id_instruments " .
        "and groups.id = member.id_groups " .
        "and person.id = member.id_person " .
        "and groups.id = $id_groups " .
        "order by instruments.list_order, lastname, firstname";

  $r = mysql_query($q);

  while($e = mysql_fetch_array($r, MYSQL_ASSOC))
  {
    echo "$e[firstname] $e[lastname] ($e[instrument])<br>";
  }
}

function instruments_list($id_groups)
{
  $q = "select instrument from instruments where id_groups = $id_groups";

  $r = mysql_query($q);

  while($e = mysql_fetch_array($r, MYSQL_ASSOC))
  {
    echo "$e[instrument]<br>";
  }
}


function person_select($selected)
{
  echo "<select name=id_person title=\"Angi hvem som er leder for gruppen\">";

  $q  = "SELECT person.id as id, firstname, lastname, instrument FROM person, instruments " .
        "where person.id_instruments = instruments.id " .
        "order by instrument, lastname, firstname";

  $r = mysql_query($q);

  while($e = mysql_fetch_array($r, MYSQL_ASSOC))
  {
    echo "<option value=\"" . $e[id] . "\"";
    if ($e[id] == $selected)
      echo " selected";
    echo ">$e[firstname] $e[lastname] ($e[instrument])";
  }
  echo "</select>";
}


function member_update($id_groups)
{
  $query = "delete from member where id_groups = $id_groups";
  mysql_query($query);
  
  if ($_POST[id_persons] != null)
  {
    foreach($_POST[id_persons] as $id_person)
    {
      $query = "insert into member (id_person, id_groups) " .
               "values ($id_person, $id_groups)";
      mysql_query($query);
    }
  }
}


if ($action == 'new')
{
  global $sort;
  
  echo "  <tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=submit value=ok></td>
    <td><input type=text size=30 name=name></td>
    <td>";
  person_select(0);
  echo "</td><td>";
  member_select(0);
  echo "
    </td>
    <td><textarea cols=60 rows=7 wrap=virtual name=comment></textarea></td>
  </tr>";
}

if ($action == 'update')
{
  $id_person = $_POST[id_person];
  if ($id_person == NULL)
      $id_person = 0;
  
  if ($no == NULL)
  {
    $query = "insert into groups (name, id_person, comment) " .
             "values ('$_POST[name]', $id_person, '$_POST[comment]')";
    $no = mysql_insert_id();
  }
  else
  {
    if ($delete != NULL)
    {
      $q = "select count(*) as count from instruments where id_groups = {$no}";
      $r = mysql_query($q);
      $e = mysql_fetch_array($r, MYSQL_ASSOC);
      if ($e[count] == 0)
        $query = "DELETE FROM groups WHERE id = {$no}";
      else
        echo "<font color=red>Error: Some instruments are already part of this group</font>";
    }
    else
    {
      $query = "update groups set name = '$_POST[name]'," .
                               "id_person = $id_person," .
                               "comment = '$_POST[comment]' " .
             "where id = $no";
    }
  }

  mysql_query($query);
  
  member_update($no);
  $no = NULL;
}  



$query  = "SELECT groups.id as id, groups.name as name, firstname, lastname, instrument, groups.comment as comment " .
          "FROM groups, person, instruments " .
          "where person.id = groups.id_person " .
          "and instruments.id = person.id_instruments " .
          "order by $sort";
                 
$result = mysql_query($query);

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
  if ($row[id] != $no)
  {
    echo "<tr>
         <td><center>
           <a href=\"$php_self?_sort=$sort&_action=view&_no=$row[id]\"><img src=\"images/cross_re.gif\" border=0></a>
             </center></td>" .
         "<td>$row[name]</td>" .
         "<td>$row[firstname] $row[lastname] ($row[instrument])</td><td>";
    instruments_list($row[id]);
    member_list($row[id]);
    echo "</td><td>";
    echo str_replace("\n", "<br>\n", $row[comment]);
    echo "</td>" .
         "</tr>";
  }
  else
  {
    echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <th nowrap><input type=submit value=ok>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette $row[name]?');\"></th>
    <th><input type=text size=30 name=name value=\"$row[name]\"></th>
    <th>";
    person_select($row[id_person]);
    echo "</td><td>";
    instruments_list($row[id]);
    member_select($no);
    echo "</th>
    <th><textarea cols=60 rows=7 wrap=virtual name=comment>$row[comment]</textarea></th>
    </tr>";
  }
} 

echo "
    </table>
  </form>";

  require 'framework_end.php';
?>


