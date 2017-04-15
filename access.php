<?php

require 'framework.php';

if ($action == 'insert')
{
   $query = "insert into auth_person (id_view, id_person) " .
            "values ('$_REQUEST[id_view]', '$_REQUEST[id_person]')";
   mysql_query($query);
}   

if ($action == 'delete')
{
   $query = "delete from auth_person where id_view = '$_REQUEST[id_view]' and id_person = '$_REQUEST[id_person]'";
   mysql_query($query);
}   


echo "
    <h1>Tilgang</h1>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0><a href=$php_self?_sort=firstname,lastname title=\"Sorter p&aring; fornavn\">Fornavn</a>/
                          <a href=$php_self?_sort=lastname,firstname title=\"Sorter p&aring; etternavn\">Etternavn</a></th>
      <th bgcolor=#A6CAF0><a href=$php_self?_sort=list_order,lastname,firstname title=\"Sorter p&aring; instrumentgruppe\">Instrument</a></th>
      <th bgcolor=#A6CAF0><a href=$php_self?_sort=status,list_order,lastname,firstname title=\"Sorter p&aring; status\">Status</a></th>";
 
$query  = "SELECT name " .
          "FROM view " .
          "order by name";
$result = mysql_query($query);

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
  echo "<th bgcolor=#A6CAF0>$row[name]</td>";
echo "</tr><tr>";

if ($sort == NULL)
  $sort = "list_order, lastname, firstname";

$query  = "SELECT person.id as person_id, " .
          "view.id as view_id,  " .
          "view.name as view_name, " .
          "firstname, lastname, instrument, status, " .
          "view.comment as view_comment " .
          "FROM person, instruments, view " .
          "where instruments.id = id_instruments " .
          "and not person.status = $per_stat_quited " .
          "order by $sort";
$result = mysql_query($query);

$prev_id = 0;

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
  if ($row[person_id] != $prev_id)
  {
    echo "</tr><tr><td bgcolor=#A6CAF0>$row[firstname] $row[lastname]";   
    echo "</td><td bgcolor=#A6CAF0> $row[instrument] </td>";
    echo "</td><td bgcolor=#A6CAF0>" . $per_stat[$row[status]] . "</td>";
    $prev_id = $row[person_id];
  }
  $query  = "SELECT comment from auth_person " .
          "where id_view = {$row[view_id]} " .
          "and id_person = {$row[person_id]}"; 
  $result2 = mysql_query($query);
  
  $action = "insert";
  $image = "images/stop_red.gif";
  if ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC))
  {
    $action = "delete";
    $image = "images/tick2.gif";
  }
  echo "<td align=center><a href=\"$_SERVER[PHP_SELF]?_action={$action}&id_person={$row[person_id]}&id_view={$row[view_id]}&_sort={$sort}\"><img src=\"$image\" border=0 title=\"{$row2[comment]}\"></td>";
} 


?> 
    </tr>
    </table>
  
<?php
   include 'framework_end.php';
?>

