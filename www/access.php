<?php

require 'framework.php';

$f_person = request('f_person');

if ($action == 'insert')
{
   $ts = strtotime("now");
   $query = "insert into auth_person (id_view, id_person, ts, id_auth) " .
            "values (".request('id_view').", ".request('id_person').", $ts, " . $whoami->id() . ")";
   $db->query($query);
}   

if ($action == 'delete')
{
   $query = "delete from auth_person where id_view = ".request('id_view')." and id_person = ".request('id_person');
   $db->query($query);
}   

$sort_view = "view.name,view.id";
if (is_null($sort))
  $sort = "list_order, lastname, firstname, $sort_view";

echo "
    <h1>Tilgang</h1>
    <table border=1>
    <tr>
      <th><a href=$php_self?_sort=firstname,lastname,$sort_view title=\"Sorter p&aring; fornavn\">Fornavn</a>/
                          <a href=$php_self?_sort=lastname,firstname,$sort_view title=\"Sorter p&aring; etternavn\">Etternavn</a></th>
      <th><a href=$php_self?_sort=list_order,lastname,firstname,$sort_view title=\"Sorter p&aring; instrumentgruppe\">Instrument</a></th>
      <th><a href=$php_self?_sort=status,list_order,lastname,firstname,$sort_view title=\"Sorter p&aring; status\">Status</a></th>";
 
$query  = "SELECT name " .
          "FROM view " .
          "order by name";
$stmt = $db->query($query);

foreach($stmt as $row)
  echo "<th>".$row['name']."</td>";
echo "</tr><tr>";

$query  = "SELECT person.id as person_id, " .
          "view.id as view_id,  " .
          "view.name as view_name, " .
          "firstname, lastname, instrument, status, " .
          "view.comment as view_comment " .
          "FROM person, instruments, view " .
          "where instruments.id = id_instruments ";
if (is_null($f_person))
   $query .= "and not person.status = $db->per_stat_quited ";
else
   $query .= "and person.id = $f_person ";
$query .= "order by $sort";
$stmt = $db->query($query);

$prev_id = 0;

foreach($stmt as $row)
{
  if ($row['person_id'] != $prev_id)
  {
    echo "</tr><tr><td nowrap><a href=\"personEdit.php?_no=".$row['person_id']."\" title=\"Gå til personopplysninger...\">".$row['firstname']." ".$row['lastname']."</a>";   
    echo "</td><td>".$row['instrument']."</td>";
    echo "</td><td>" . $db->per_stat[$row['status']] . "</td>";
    $prev_id = $row['person_id'];
  }
  $query  = "SELECT auth_person.ts as ts, "
          . "firstname, lastname "
          . "from auth_person, person "
          . "where person.id = auth_person.id_auth "
          . "and id_view = ".$row['view_id']." "
          . "and id_person = ".$row['person_id']; 
  $stmt2 = $db->query($query);
  
  $action = "insert";
  $image = "images/stop_red.gif";
  $ts_txt= '';
  $warning = "Sikkert at du vil endre tilgang for ".$row['firstname']." ".$row['lastname']."?";
  $url_filter = (is_null($f_person)) ? "" : "&f_person=$f_person";
  if ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC))
  {
     $action = "delete";
     $image = "images/tick2.gif";
     $ts_txt = "Tilgang gitt:" . strftime('%a %e.%b %y', $row2['ts']) . " av ".$row2['firstname']." ".$row2['lastname'];
  }
  if ($access->auth(AUTH::ACC))
     echo "<td align=center><a href=\"$php_self?_action=$action&id_person=".$row['person_id']."&id_view=".$row['view_id']."&_sort=$sort$url_filter\" onClick=\"return confirm('$warning');\"><img src=\"$image\" border=0 title=\"Klikk for å endre tilgang. $ts_txt\"></td>";
  else
     echo "<td align=center><img src=\"$image\" border=0 title=\"$ts_txt\"></td>";
} 


?> 
    </tr>
    </table>
  