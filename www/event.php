<?php
require 'framework.php';

$id_project = request('id_project');

function select_project($selected)
{
   global $db;

   echo "<select name=id_project>";

   $year = date("Y");
   $q = "SELECT id, name, semester, year, orchestration FROM project " .
           "where year >= ${year} " .
           "or id = '${selected}' " .
           "order by year, semester DESC";
   $s = $db->query($q);

   echo "<option value=0>Generell</option>\n";

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         echo " selected";
      echo ">" . $e['name'] . " (" . $e['semester'] . ' ' . $e['year'] . ")";
   }
   echo "</select>";
}

function select_importance($selected)
{
   global $db;

   echo "<select name=importance>";

   for ($i = 0; $i < count($db->evt_importance); $i++)
   {
      echo "<option value=$i";
      if ($selected == $i)
         echo " selected";
      echo ">" . $db->evt_importance[$i] . "</option>\n";
   }

   echo "</select>";
}

function select_status($selected)
{
   global $db;

   echo "<select name=status>";

   for ($i = 0; $i < count($db->evt_status); $i++)
   {
      echo "<option value=$i";
      if ($selected == $i)
         echo " selected";
      echo ">" . $db->evt_status[$i] . "</option>\n";
   }

   echo "</select>";
}

if ($sort == NULL)
   $sort = 'ts_change';

echo "<h1>Hva skjer...?</h1>\n";

if ($action != 'new' && $access->auth(AUTH::EVENT))
{
   echo "<form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=hidden name=id_project value=\"$id_project\">
      <input type=submit value=\"Nytt event\">
    </form>\n";
}

echo "<form action='$php_self' method=post>\n";

if ($action == 'new')
{
   echo "<table border=0>
    <tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <td><input type=submit value=Registrer></td>
    </tr><tr>
    <td><i>Subjekt:</i></td><td><input type=text size=60 name=subject></td>
    </tr><tr>
    <td><i>Prosjekt:</i></td><td>";
   select_project($id_project);
   echo "</td>
     </tr><tr>
     <td><i>Viktighetsgrad:</i></td><td>";
   select_importance(null);
   echo "</td>
     </tr><tr>
     <td><i>Status:</i></td><td>";
   select_status(null);
   echo "</td>
     </tr></table>
    <textarea cols=60 rows=15 wrap=virtual name=body></textarea>
    <p>";
}

if ($action == 'update' && $access->auth(AUTH::EVENT))
{
   $ts = strtotime("now");

   if (is_null($no))
   {
      $query = "insert into event (subject, ts_create, ts_update, importance, body, "
              . "id_person, id_project, status) "
              . "values (" . $db->qpost('subject') . ", $ts, $ts, "
              . request('importance') . ", " . $db->qpost('body') . ", " . $whoami->id() . ", "
              . request('id_project') . ", " . request('status') . ")";
   }
   else
   {
      if (!is_null($delete))
      {
         $query = "delete from event where id = $no";
      }
      else
      {
         $query = "update event set subject = " . $db->qpost('subject') . "," .
                 "ts_update = $ts," .
                 "importance = " . request('importance') . "," .
                 "body = " . $db->qpost('body') . "," .
                 "id_project = " . request('id_project') . "," .
                 "id_person = " . $whoami->id() . "," .
                 "id_project = " . request('id_project') . "," .
                 "status = " . request('status') . " " .
                 "where id = $no";
      }
      $no = NULL;
   }
   $db->query($query);
}

$query = "select event.id as id, subject, ts_create, ts_update, importance, body, "
        . "person.uid as uid, event.id_project as id_project, "
        . "event.status as status, firstname, lastname, instrument "
        . "from event, person, instruments "
        . "where person.id = event.id_person "
        . "and person.id_instruments = instruments.id ";
if ($id_project)
   $query .= "and event.id_project = $id_project ";
else
   $query .= "and ts_update > " . $season->ts()[0] . " "
           . "and ts_update < " . $season->ts()[1] . " ";
$query .= "order by ts_update desc";
$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($row['id'] == $no)
   {
      echo "<table border=0><tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=hidden name=_no value=$no>
    <input type=submit value=Lagre>
    <input type=submit name=_delete value=Slett></td>
    </tr><tr>
    <td><i>Subjekt:</i></td><td><input type=text size=60 name=subject value=\"" . $row['subject'] . "\"></td>
    </tr><tr>
    <td><i>Fra:</i></td><td>" . $row['firstname'] . " " . $row['lastname'] . "</td>
    </tr><tr>
    <td><i>Opprettet:</i></td><td>" . strftime('%e.%b %y', $row['ts_create']) . "</td>
    </tr><tr>
    <td><i>Prosjekt:</i></td><td>";
      select_project($row['id_project']);
      echo "</td>
     </tr><tr>
     <td><i>Viktighetsgrad:</i></td><td>";
      select_importance($row['importance']);
      echo "</td>
     </tr><tr>
     <td><i>Status:</i></td><td>";
      select_status($row['status']);
      echo "</td>
     </tr></table>
    <textarea cols=60 rows=15 wrap=virtual name=body>" . $row['body'] . "</textarea>\n";
   }
   else
   {
      if ($row['uid'] == $whoami->uid() && $access->auth(AUTH::EVENT))
      {
         echo "<input type=button value=Endre onClick=\"location.href='$php_self?_sort=$sort&_action=view&_no=" . $row['id'] . "';\">";
      }
      echo "  <tr>
    <font size=+2><b>" . $row['subject'] . "</b></font>
    <table border=0><tr>
    <td><i>Fra:</i></td><td>" . $row['firstname'] . " " . $row['lastname'] . "</td>
    </tr><tr>
    <td><i>Dato:</i></td><td>" . strftime('%e.%b %Y', $row['ts_update']) . "</td>
    </tr><tr>
    <td><i>Prosjekt:</i></td><td>";
      if ($row['id_project'] > 0)
      {
         $s = $db->query("select name, semester, year from project where id=" . $row['id_project']);
         $e = $s->fetch(PDO::FETCH_ASSOC);
         echo $e['name'] . " (" . $e['semester'] . "-" . $e['year'] . ")";
      }

      echo "</td>
     </tr><tr>
     <td><i>Viktighetsgrad:</i></td><td>" .
      $db->evt_importance[$row['importance']] .
      "</td>
     </tr>
     </table>";
      $body = str_replace("\n", "<br>\n", $row['body']);
      echo ($row['status'] == $db->evt_status_draft) ? "<font color=grey>$body</font>" : $body;
   }
   echo "<p>";
}
?> 

</form>
