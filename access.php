<?php

require 'framework.php';

if ($action == 'insert')
{
   $query = "insert into auth_person (id_view, id_person) " .
            "values ('$_REQUEST[id_view]', '$_REQUEST[id_person]')";
   $db->query($query);
}   

if ($action == 'delete')
{
   $query = "delete from auth_person where id_view = '$_REQUEST[id_view]' and id_person = '$_REQUEST[id_person]'";
   $db->query($query);
}   

$sort_view = "view.name,view.id";
if ($sort == NULL)
  $sort = "list_order, lastname, firstname, $sort_view";

echo "
    <h1>Tilgang</h1>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0><a href=$php_self?_sort=firstname,lastname,$sort_view title=\"Sorter p&aring; fornavn\">Fornavn</a>/
                          <a href=$php_self?_sort=lastname,firstname,$sort_view title=\"Sorter p&aring; etternavn\">Etternavn</a></th>
      <th bgcolor=#A6CAF0><a href=$php_self?_sort=list_order,lastname,firstname,$sort_view title=\"Sorter p&aring; instrumentgruppe\">Instrument</a></th>
      <th bgcolor=#A6CAF0><a href=$php_self?_sort=status,list_order,lastname,firstname,$sort_view title=\"Sorter p&aring; status\">Status</a></th>";
 
$query  = "SELECT name " .
          "FROM view " .
          "order by name";
$stmt = $db->query($query);

foreach($stmt as $row)
  echo "<th bgcolor=#A6CAF0>$row[name]</td>";
echo "</tr><tr>";

$query  = "SELECT person.id as person_id, " .
          "view.id as view_id,  " .
          "view.name as view_name, " .
          "firstname, lastname, instrument, status, " .
          "view.comment as view_comment " .
          "FROM person, instruments, view " .
          "where instruments.id = id_instruments " .
          "and not person.status = $per_stat_quited " .
          "order by $sort";
$stmt = $db->query($query);

$prev_id = 0;

foreach($stmt as $row)
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
  $stmt2 = $db->query($query);
  
  $action = "insert";
  $image = "images/stop_red.gif";
  if ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC))
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

