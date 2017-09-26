<?php
require 'framework.php';

if ($sort == NULL)
   $sort = 'name';

echo "
    <h1>Lokale</h1>";
if ($access->auth(AUTH::LOC))
   echo "
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Nytt lokale\">
    </form>";
echo "
    <form action='$php_self' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::LOC))
   echo "
      <th bgcolor=#A6CAF0>Edit</th>";
echo "
      <th bgcolor=#A6CAF0>Lokale</th>
      <th bgcolor=#A6CAF0>Adresse</th>
      <th bgcolor=#A6CAF0>URL</th>
      <th bgcolor=#A6CAF0>Kontaktperson</th>
      <th bgcolor=#A6CAF0>Kommentar</th>
      </tr>";

if ($action == 'new')
{
   echo "  <tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=submit value=ok></td>
    <th><input type=text size=30 name=name></th>
    <th><input type=text size=30 name=address></th>
    <th><input type=text size=50 name=url></th>
    <th><textarea cols=20 rows=7 wrap=virtual name=contact></textarea></th>
    <th><textarea cols=60 rows=7 wrap=virtual name=comment></textarea></th>
  </tr>";
}

if ($action == 'update' && $access->auth(AUTH::LOC))
{
   if (is_null($no))
      $query = "insert into location (name, address, url, contact, comment) "
              . "values (" . $db->qpost('name') . ","
              . $db->qpost('address') . ","
              . $db->qpost('url') . ","
              . $db->qpost('contact') . ","
              . $db->qpost('comment') . ")";
   else
   {
      if (!is_null($delete))
      {
         $q = "select count(*) as count from plan where id_location = $no";
         $s = $db->query($q);
         $e = $s->fetch(PDO::FETCH_ASSOC);
         if ($e['count'] == 0)
            $query = "DELETE FROM location WHERE id = $no";
         else
            echo "<font color=red>Location in use</font>";
      }
      else
         $query = "update location set "
              . "name = " . $db->qpost('name') . ","
              . "address = " . $db->qpost('address') . ","
              . "url = " . $db->qpost('url') . ","
              . "contact = " . $db->qpost('contact') . ","
              . "comment = " . $db->qpost('comment') . " "
              . "where id = $no";
      $no = null;
   }
   $db->query($query);
}

$query = "SELECT id, name, address, url, contact, comment " .
        "FROM location order by $sort";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($row['id'] != $no)
   {
      echo "<tr>";
      if ($access->auth(AUTH::LOC))
         echo "
         <td><center>
           <a href=\"$php_self?_sort=$sort&_action=view&_no=".$row['id']."\"><img src=\"images/cross_re.gif\" border=0></a>
             </center></td>";
      echo
      "<td>".$row['name']."</td>" .
      "<td>".$row['address']."</td>" .
      "<td>";
      if (strlen($row['url']) > 0)
         echo "<a href = \"".$row['url']."\" title=\"".$row['url']."\">&lt;link&gt;</a>";
      echo "</td><td>";
      echo str_replace("\n", "<br>\n", $row['contact']);
      echo "</td><td>";
      echo str_replace("\n", "<br>\n", $row['comment']);
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
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette ".$row['name']."?');\"></th>
    <th><input type=text size=30 name=name value=\"".$row['name']."\"></th>
    <th><input type=text size=30 name=address value=\"".$row['address']."\"></th>
    <th><input type=text size=50 name=url value=\"".$row['url']."\"></th>
    <th><textarea cols=20 rows=7 wrap=virtual name=contact>".$row['contact']."</textarea></th>
    <th><textarea cols=60 rows=7 wrap=virtual name=comment>".$row['comment']."</textarea></th>
    </tr>";
   }
}
?> 

</table>
</form>
