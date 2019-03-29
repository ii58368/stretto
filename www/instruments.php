<?php
require 'framework.php';

if (is_null($sort))
   $sort = 'list_order';

echo "
<h1>Instrumenter</h1>";
if ($access->auth(AUTH::INSTR))
  echo "
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Ny gruppe\" title=\"Definer ny instrumentgruppe...\">
    </form>";
echo "
    <form action='$php_self' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::INSTR))
   echo "
      <th>Edit</th>";
echo "
      <th><a href=\"$php_self?_sort=instrument,list_order\">Instrument</a></th>
      <th><a href=\"$php_self?_sort=list_order\">Sortering</a></th>
      <th>Ansvarlig</th>
      <th>Instrumentgruppe</th>
      <th>Kommentar</th>
      </tr>";

function select_groups($selected)
{
   global $db;

   echo "<select name=id_groups title=\"Ansvarlig ledergruppe\">";

   $q = "SELECT id, name FROM groups " .
           "order by name";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         echo " selected";
      echo ">".$e['name'];
   }
   echo "</select>";
}

function select_instr_grp($selected)
{
   global $db;

   echo "<select name=id_instr_grp title=\"Gruppering i forbindelse med regi\">";

   $q = "SELECT id, name FROM instr_grp " .
           "order by name";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         echo " selected";
      echo ">".$e['name'];
   }
   echo "</select>";
}

if ($action == 'new')
{
   echo "  <tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=submit value=ok title=\"Lagre ny instrumentgruppe\"></td>
    <td><input type=text size=10 name=instrument title=\"Navn på instrumentgruppe\"></td>
    <td><input type=text size=15 name=list_order title=\"Sortering\"></td>
    <td>";
   select_groups(0);
   echo "</td>
    <td>";
   select_instr_grp(0);
   echo "</td>
    <td><input type=text size=10 name=comment title=\"Fritekst\"></td>
  </tr>";
}

if ($action == 'update' && $access->auth(AUTH::INSTR))
{
   if (is_null($no))
   {
      $query = "insert into instruments (instrument, list_order, id_groups, id_instr_grp, comment)
              values (" . $db->qpost('instrument') . ", " . request('list_order') . ", "
              . "" . request('id_groups') . ", " . request('id_instr_grp') . ", " . $db->qpost('comment') . ")";
   } else
   {
      if (!is_null($delete))
      {
         $q = "select count(*) as count from person where id_instruments = $no";
         $s = $db->query($q);
         $e = $s->fetch(PDO::FETCH_ASSOC);
         if ($e['count'] == 0)
            $query = "DELETE FROM instruments WHERE id = $no";
         else
            echo "<font color=red>Error: Some persons are already playing this instrument</font>";
      }
      else
      {
         $query = "update instruments set instrument = " . $db->qpost('instrument') . "," .
                 "list_order = " . request('list_order') . "," .
                 "id_groups = " . request('id_groups') . "," .
                 "id_instr_grp = " . request('id_instr_grp') . "," .
                 "comment = " . $db->qpost('comment') . " " .
                 "where id = $no";
      }
      $no = NULL;
   }
   $db->query($query);
}

$query = "SELECT instruments.id as id, instrument, list_order, "
        . "id_groups, groups.name as name, instr_grp.name as igname, "
        . "instruments.id_instr_grp as id_instr_grp, "
        . "instruments.comment as comment "
        . "FROM instruments, groups, instr_grp "
        . "where instruments.id_instr_grp = instr_grp.id "
        . "and instruments.id_groups = groups.id "
        . " order by {$sort}";

        
$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($row['id'] != $no)
   {
      echo "<tr>";
      if ($access->auth(AUTH::INSTR))
         echo "
         <td><center>
           <a href=\"$php_self?_sort=$sort&_action=view&_no=".$row['id']."\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for å editere...\"></a>
             </center></td>";
      echo 
      "<td>".$row['instrument']."</td>" .
      "<td>".$row['list_order']."</td>" .
      "<td>".$row['name']."</td>" .
      "<td>".$row['igname']."</td>" .
      "<td>".$row['comment']."</td>" .
      "</tr>";
   } else
   {
      echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <td nowrap><input type=submit value=ok title=\"Lagre\">
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette ".$row['instrument']."?');\" title=\"Slette\"></td>
    <td><input type=text size=10 name=instrument value=\"".$row['instrument']."\" title=\"Navn på instrumentgruppe\"></td>
    <td><input type=text size=15 name=list_order value=\"".$row['list_order']."\" title=\"Sortering\"></td>
    <td>";
      select_groups($row['id_groups']);
      echo "</td>
    <td>";
      select_instr_grp($row['id_instr_grp']);
      echo "</td>
    <td><input type=text size=10 name=comment value=\"".$row['comment']."\" title=\"Fritekst\"></td>
    </tr>";
   }
}
?> 

</table>
</form>
