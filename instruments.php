<?php
  require 'framework.php';

  if ($sort == NULL)
      $sort = 'list_order';
  
echo "
<h1>Instrumentgrupper</h1>
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Ny gruppe\">
    </form>
    <form action='$php_self' method=post>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Edit</th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=instrument,list_order\">Instrument</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=list_order\">Sortering</a></th>
      <th bgcolor=#A6CAF0>Ansvarlig</th>
      <th bgcolor=#A6CAF0>Kommentar</th>
      </tr>";


function select_groups($selected)
{
  echo "<select name=id_groups title=\"Ansvarlig\">";

  $q  = "SELECT id, name FROM groups " .
   "order by name";

  $r = mysql_query($q);

  while($e = mysql_fetch_array($r, MYSQL_ASSOC))
  {
    echo "<option value=\"" . $e[id] . "\"";
    if ($e[id] == $selected)
      echo " selected";
    echo ">$e[name]";
  }
  echo "</select>";
}


if ($action == 'new')
{
  echo "  <tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"{$sort}\">
    <input type=submit value=ok></td>
    <th><input type=text size=10 name=instrument>
    <th><input type=text size=15 name=list_order></th>
    <th>";
    select_groups(0);
  echo "</th>
    <th><input type=text size=10 name=comment></th>
  </tr>";
}

if ($action == 'update')
{
  if ($no == NULL)
  {
    $query = "insert into instruments (instrument, list_order, id_groups, comment)
              values ('$_POST[instrument]', $_POST[list_order], $_POST[id_groups], '$_POST[comment]')";
  }
  else
  {
    if ($delete != NULL)
    {
      $q = "select count(*) as count from person where id_instruments = {$no}";
      $r = mysql_query($q);
      $e = mysql_fetch_array($r, MYSQL_ASSOC);
      if ($e[count] == 0)
        $query = "DELETE FROM instruments WHERE id = {$no}";
      else
        echo "<font color=red>Error: Some persons are already playing this instrument</font>";
    }
    else
    {
      $query = "update instruments set instrument = '$_POST[instrument]'," .
                               "list_order = $_POST[list_order]," .
                               "id_groups = $_POST[id_groups]," .
                               "comment = '$_POST[comment]' " .
             "where id = $no";
    }
    $no = NULL;
  }
  mysql_query($query);
}   

$query  = "SELECT instruments.id as id, instrument, list_order, id_groups, groups.name as name, instruments.comment as comment " .
          "FROM instruments, groups " .
          "where id_groups = groups.id " .
          " order by {$sort}";

$result = mysql_query($query);

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
  if ($row[id] != $no)
  {
    echo "<tr>
         <td><center>
           <a href=\"{$_SERVER[PHP_SELF]}?_sort=$sort&_action=view&_no={$row[id]}\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for å editere...\"></a>
             </center></td>" .
         "<td>{$row[instrument]}</td>" .
         "<td>{$row[list_order]}</td>" .
         "<td>{$row[name]}</td>" .
         "<td>{$row[comment]}</td>" .
         "</tr>";
  }
  else
  {
    echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <th nowrap><input type=submit value=ok>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette {$row[part_name]} {$row[instrument]}?');\"></th>
    <th><input type=text size=10 name=instrument value=\"{$row[instrument]}\">
    <th><input type=text size=15 name=list_order value=\"{$row[list_order]}\"></th>
    <th>";
    select_groups($row[id_groups]);
    echo "</td>
    <th><input type=text size=10 name=comment value=\"{$row[comment]}\"></th>
    </tr>";
  }
} 


?> 

    </table>
    </form>

<?php
  require 'framework_end.php';
?>

