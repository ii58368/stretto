<?php

require 'framework.php';

function insert_log($text)
{
   global $db;
   global $whoami;

   $s = $db->query("select name from view where id = " . request('id_view'));
   $e = $s->fetch(PDO::FETCH_ASSOC);
   $text .= $e['name'];
   
   $now = strtotime("now");

   $q = "insert into record (ts, status, comment, id_person, id_editor) " .
           "values ($now, $db->rec_stat_board, " . $db->quote($text) . ", " . request('id_person') . ", " . $whoami->id() . ")";
   $db->query($q);
}

$f_person = request('f_person');

if ($action == 'insert')
{
   $ts = strtotime("now");
   $query = "insert into auth_person (id_view, id_person, ts, id_auth) " .
            "values (".request('id_view').", ".request('id_person').", $ts, " . $whoami->id() . ")";
   $db->query($query);
   
   insert_log("Lagt til i tilgangsgruppe: ");
}   

if ($action == 'delete')
{
   $query = "delete from auth_person where id_view = ".request('id_view')." and id_person = ".request('id_person');
   $db->query($query);
   
   insert_log("Slettet fra tilgangsgruppe: ");
}   

$sort_view = "view.name,view.id";
if (is_null($sort))
  $sort = "list_order,lastname,firstname,$sort_view";

echo "<h1>Tilgang</h1>";

$tb = new TABLE('border=');

$tb->th("<a href=$php_self?_sort=firstname,lastname,$sort_view title=\"Sorter p&aring; fornavn\">Fornavn</a>/
                          <a href=$php_self?_sort=lastname,firstname,$sort_view title=\"Sorter p&aring; etternavn\">Etternavn</a>");
$tb->th("<a href=$php_self?_sort=list_order,lastname,firstname,$sort_view title=\"Sorter p&aring; instrumentgruppe\">Instrument</a>");
$tb->th("<a href=$php_self?_sort=status,list_order,lastname,firstname,$sort_view title=\"Sorter p&aring; status\">Status</a>");
 
$query  = "SELECT name " .
          "FROM view " .
          "order by name";
$stmt = $db->query($query);

foreach($stmt as $row)
  $tb->th($row['name']);

$query  = "SELECT person.id as person_id, "
          . "view.id as view_id,  "
          . "view.name as view_name, "
          . "firstname, lastname, instrument, status, "
          . "view.comment as view_comment "
          . "FROM person, instruments, view "
          . "where instruments.id = id_instruments ";
if (is_null($f_person))
   $query .= "and not (person.status = $db->per_stat_quited or person.status = $db->per_stat_removed)";
else
   $query .= "and person.id = $f_person ";
$query .= "order by $sort";

$stmt = $db->query($query);

$prev_id = 0;

foreach($stmt as $row)
{
  if ($row['person_id'] != $prev_id)
  {
    $tb->tr();
    $tb->td("<a href=\"personEdit.php?_no=".$row['person_id']."\" title=\"Gå til personopplysninger...\">".$row['firstname']." ".$row['lastname']."</a>", 'nowrap');
    $tb->td($row['instrument']);
    $tb->td($db->per_stat[$row['status']]);
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
  $href = "\"$php_self?_action=$action&id_person=".$row['person_id']."&id_view=".$row['view_id']."&_sort=$sort$url_filter\" onClick=\"return confirm('$warning');\"";
  if ($access->auth(AUTH::ACC))
     $tb->td("<a href=$href><img src=\"$image\" border=0 title=\"Klikk for å endre tilgang. $ts_txt\">", 'align=center');
  else
     $tb->td("<img src=\"$image\" border=0 title=\"$ts_txt\">", 'align=center'); 
} 
