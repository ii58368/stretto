<?php
require 'framework.php';

if (is_null($sort))
   $sort = 'ts_reg';

$sel_year = is_null(request('from')) ? date("Y") : intval(request('from'));
$prev_year = $sel_year - 1;

echo "
<h1>Permisjoner (langtid)</h1>";
if ($access->auth(AUTH::LEAVE_RW))
   echo "
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Ny permisjon\">
    </form>";
echo "
    <form action='$php_self' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::LEAVE_RW))
   echo "
      <th bgcolor=#A6CAF0>Edit</th>";
echo "
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=ts_reg&from=$sel_year\">Registrert</a>
         <a href=\"$php_self?from=$prev_year&_sort={$sort}\"><img src=images/arrow_up.png border=0 title=\"Forrige &aring;r...\"></a></th>
      <th bgcolor=#A6CAF0>Navn</th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=status,ts_reg&from=$sel_year\">Status</a></th>
      <th bgcolor=#A6CAF0>Behandlet</th>
      <th bgcolor=#A6CAF0>Fra</th>
      <th bgcolor=#A6CAF0>Til</th>
      <th bgcolor=#A6CAF0>Tekst</th>
      </tr>";

function select_person($selected)
{
   global $db;

   echo "<select name=id_person title=\"Velg medlem\">";

   $q = "SELECT id, firstname, middlename, lastname "
           . "FROM person "
           . "where status = $db->per_stat_member "
           . "order by lastname, firstname";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         echo " selected";
      echo ">" . $e['firstname'] . " " . $e['middlename'] . " " . $e['lastname'] . "</option>";
   }
   echo "</select>";
}

function select_status($selected)
{
   global $db;

   if (is_null($selected))
      $selected = $db->lea_stat_registered;

   echo "<select name=status>";

   for ($i = 0; $i < count($db->lea_stat); $i++)
   {
      echo "<option value=$i";
      if ($selected == $i)
         echo " selected";
      echo ">" . $db->lea_stat[$i] . "</option>\n";
   }

   echo "</select>";
}

if ($action == 'new')
{
   echo "  <tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=submit value=ok></td>
    <td>" . strftime('%a %e.%b %y') . "</td>\n<td>";
   select_person(null);
   echo "</td>\n<td>";
   select_status(null);
   echo "</td>
    <td>" . strftime('%a %e.%b %y') . "</td>
    <td><input type=date size=10 name=ts_from></td>
    <td><input type=date size=10 name=ts_to></td>
    <td><textarea name=text wrap=virtual cols=60 rows=10></textarea></td>
  </tr>";
}


function update_db()
{
   global $db;
   global $no;
   global $delete;
   
   if (($ts_from = strtotime(request('ts_from'))) == false)
   {
      echo "<font color=red>Illegal time format: " . request('ts_from') . "</font>";
      return;
   }

   if (($ts_to = strtotime(request('ts_to'))) == false)
   {
      echo "<font color=red>Illegal time format: " . request('ts_to') . "</font>";
      return;
   }

   $ts_now = strtotime("now");

   if (is_null($no))
   {
      $query = "insert into `leave` (ts_reg, id_person, status, ts_proc, ts_from, ts_to, text)
              values ($ts_now, " . request('id_person') . ", " . request('status') . ", $ts_now, $ts_from, $ts_to, " . $db->qpost('text') . ")";
   } else
   {
      if (!is_null($delete))
      {
         $query = "DELETE FROM `leave` WHERE id = $no";
      } else
      {
         $query = "update `leave` set " .
                 "id_person = " . request('id_person') . "," .
                 "status = " . request('status') . "," .
                 "ts_proc = $ts_now," .
                 "ts_from = $ts_from," .
                 "ts_to = $ts_to," .
                 "text = " . $db->qpost('text') . " " .
                 "where id = $no";
      }
      $no = NULL;
   }
   $db->query($query);
}

if ($action == 'update' && $access->auth(AUTH::LEAVE_RW))
   update_db();

$query = "select leave.id as id, "
        . "leave.ts_reg as ts_reg, "
        . "person.id as id_person, "
        . "firstname, middlename, lastname, instrument, "
        . "leave.status as status, "
        . "leave.ts_proc as ts_proc, "
        . "leave.ts_from as ts_from, "
        . "leave.ts_to as ts_to, "
        . "leave.text as text "
        . "from `leave`, person, instruments "
        . "where leave.id_person = person.id "
        . "and person.id_instruments = instruments.id "
        . "and leave.ts_reg >= " . strtotime("1. jan $sel_year") . " "
        . "order by $sort";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($row['id'] != $no)
   {
      echo "<tr>";
      if ($access->auth(AUTH::LEAVE_RW))
         echo "
         <td><center>
           <a href=\"$php_self?_sort=$sort&_action=view&_no=".$row['id']."&ts_reg=$sel_year\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for å editere...\"></a>
             </center></td>";
      echo
      "<td>" . strftime('%a %e.%b %y', $row['ts_reg']) . "</td>" .
      "<td>".$row['firstname']." ".$row['middlename']." ".$row['lastname']." (".$row['instrument'].")</td>" .
      "<td>" . $db->lea_stat[$row['status']] . "</td>" .
      "<td>" . strftime('%a %e.%b %y', $row['ts_proc']) . "</td>" .
      "<td>" . strftime('%a %e.%b %y', $row['ts_from']) . "</td>" .
      "<td>" . strftime('%a %e.%b %y', $row['ts_to']) . "</td>" .
      "<td>";
      echo str_replace("\n", "<br>\n", $row['text']);
      echo "</td>" .
      "</tr>";
   } else
   {
      echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <input type=hidden name=from value='$sel_year'>
    <td nowrap><input type=submit value=ok>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette " . date('D j.M y', $row['ts_reg']) . "?');\"></td>
    <td>" . strftime('%e.%m.%y', $row['ts_reg']) . "</td>
    <td>";
      select_person($row['id_person']);
      echo "</td>
    <td>";
      select_status($row['status']);
      echo "</td>
    <td>" . strftime('%e.%m.%y') . "</td>
    <td><input type=date size=10 name=ts_from value=\"" . date('j. M y', $row['ts_from']) . "\"></td>
    <td><input type=date size=10 name=ts_to value=\"" . date('j. M y', $row['ts_to']) . "\"></td>
     <td><textarea cols=60 rows=10 wrap=virtual name=text>".$row['text']."</textarea></td>
    </tr>";
   }
}
?> 

</table>
</form>
