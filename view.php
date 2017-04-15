<?php
    require 'framework.php';
    
    if ($sort == NULL)
        $sort = 'name';
    
    $no_views = 39;
    
  echo "
    <h1>Tilgangsgrupper</h1>
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Ny tilgangsgruppe\">
    </form>
    <form action='$php_self' method=post>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Edit</th>
      <th bgcolor=#A6CAF0>Navn</th>
      <th bgcolor=#A6CAF0>Kommentar</th>";
  for ($i = 0; $i < $no_views; $i++)
    echo "<th bgcolor=#A6CAF0>" . $i . "</th>";
  echo "</tr>";

if ($action == 'new')
{
  echo "  <tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=submit value=ok></td>
    <th><input type=text size=30 name=name></th>
    <th><input type=text size=30 name=comment></th>";
  for ($i = 0; $i < $no_views; $i++)
    echo "<th><input type=checkbox name=access$i></th>";
  echo "  </tr>";
}

if ($action == 'update')
{
  $access = 0;
  
  for ($i = 0; $i < $no_views; $i++)
  {
    $key = 'access' . $i;
    if ($_POST[$key])
      $access |= 1 << $i;
  }

  if ($no == NULL)
  {
    $query = "insert into view (name, comment, access) " .
             "values ('$_POST[name]', '$_POST[comment]', '$access')";
  }
  else
  {
    if ($delete != NULL)
    {
      $query = "DELETE FROM location WHERE id = {$no}";
    }
    else
    {
      $query = "update view set name = '$_POST[name]'," .
                               "comment = '$_POST[comment]'," .
                               "access = '$access' " .
             "where id = $no";
      $no = NULL;
    }
  }
  mysql_query($query);
}   

$query  = "SELECT id, name, comment, access " .
          "FROM view order by {$sort}";
       
$result = mysql_query($query);

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
  if ($row[id] != $no)
  {
    echo "<tr>
         <td><center>
           <a href=\"{$php_self}?_sort={$sort}&_action=view&_no={$row[id]}\"><img src=\"images/cross_re.gif\" border=0></a>
             </center></td>" .
         "<td>{$row[name]}</td>" .
         "<td>{$row[comment]}</td>";
     for ($i = 0; $i < $no_views; $i++)
     {
       echo "<td>";
       if ($row[access] & (1 << $i))
         echo "<center><img src=\"images/tick2.gif\" border=0></center>";
       echo "</td>";
     }
     echo "</tr>";
  }
  else
  {
    echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <th nowrap><input type=submit value=ok>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette {$row[name]}?');\"></th>
    <th><input type=text size=30 name=name value=\"{$row[name]}\"></th>
    <th><input type=text size=30 name=comment value=\"{$row[comment]}\"></th>";
    
    for ($i = 0; $i < $no_views; $i++)
    {
       echo "<td><input type=checkbox name=access$i value='*' ";
       if ($row[access] & (1 << $i))
         echo "checked";
       echo "></td>";
    }
   
    echo "</tr>";
  }
} 

?> 

    </table>
    </form>

<?php
  require 'framework_end.php';
?>


