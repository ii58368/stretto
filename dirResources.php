<?php
include 'framework.php';

if (is_null($sort))
   $sort = 'list_order,lastname,firstname';

function mail2all()
{
   global $db;
   
   $q = "select email from person " .
           "where (status = $db->per_stat_member or status = $db->per_stat_eng)";
   $s = $db->query($q);

   echo "<a href=\"mailto:?bcc=";
   foreach ($s as $e)
      echo $e[email] . ",";
   echo "&subject=OSO: \"><image border=0 src=images/image1.gif hspace=20 title=\"Send mail alle i OSO med status medlem eller engasjert\"></a>";
}

function select_instrument($selected)
{
   global $db;
   
   $q = "SELECT id, instrument FROM instruments order by list_order";
   $s = $db->query($q);

   echo "<select name=id_instruments>";
   
   foreach ($s as $e)
   {
      echo "<option value=\"" . $e[id] . "\"";
      if ($e[id] == $selected)
         echo " selected";
      echo ">" . $e[instrument];
   }
   
   echo "</select>";
}

function select_status($selected)
{
   global $db;

   echo "<select name=status_dir>";
   
   for ($i = 0; $i < count($db->per_dir); $i++)
   {
      echo "<option value=$i";
      if ($selected == $i)
         echo " selected";
      echo ">" . $db->per_dir[$i] . "</option>\n";
   }
   
   echo "</select>";
}

function format_phone($ph)
{
   $ph = str_replace(' ', '', $ph);
   $ph = substr($ph, 0, -5) . " " . substr($ph, -5, 2) . " " . substr($ph, -3);
   if (strlen($ph) > 9)
      $ph = substr($ph, 0, -10) . " " . substr($ph, -10);
   return $ph;
}

echo "
    <h1>Regiressurser</h1>";
mail2all();
echo "
    </form>
    <form action='$php_self' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::DIR_RW))
   echo "
      <th bgcolor=#A6CAF0>Edit</th>";
echo "
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=list_order,lastname,firstname\">Instrument</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=firstname,lastname\">For</a>/
                          <a href=\"$php_self?_sort=lastname,firstname\">Etternavn</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=email\">Mail</a>";
echo "</th>
      <th bgcolor=#A6CAF0>Mobil</th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=status,list_order,lastname,firstname\">Medlemsstatus</a></th>
      <th bgcolor=#A6CAF0>Registatus</th>
      <th bgcolor=#A6CAF0>Merknad</th>
      </tr>";



if ($action == 'update' && $access->auth(AUTH::DIR_RW))
{
   $query = "update person set status_dir = '$_POST[status_dir]'," .
           "comment_dir = '$_POST[comment_dir]' " .
           "where id = $no";
   $no = NULL;

   $db->query($query);
}


$query = "SELECT person.id as id, id_instruments, instrument, firstname, lastname, " .
        "email, phone1, status, status_dir, comment_dir " .
        "FROM person, instruments where id_instruments = instruments.id order by ${sort}";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($row[id] != $no)
   {
      if ($access->auth(AUTH::DIR_RW))
         echo "<tr>
         <td><center>
           <a href=\"{$php_self}?_sort={$sort}&_action=view&_no={$row[id]}\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>
             </center></td>";
      echo "<td>{$row[instrument]}</td>" .
      "<td><a href=history.php?id_person=$row[id]>{$row[firstname]} " .
      "{$row[lastname]}</a></td>" .
      "<td><a href=\"mailto:{$row[email]}?subject=$concept - $project\">{$row[email]}</a></td>" .
      "<td nowrap>" . format_phone($row[phone1]) . "</a></td>" .
      "<td>" . $db->per_stat[$row[status]] . "</td>" .
      "<td>";
      if ($row[status_dir] == $db->per_dir_avail)
         echo "<center><img src=\"images/happy.gif\" border=0 title=\"" . $db->per_dir[$per_dir_avail] . "\"></center>";
      if ($row[status_dir] == $db->per_dir_nocarry)
         echo "<center><img src=\"images/chair-minus-icon.png\" border=0 title=\"" . $db->per_dir[$per_dir_nocarry] . "\"></center>";
      if ($row[status_dir] == $db->per_dir_exempt)
         echo "<center><img src=\"images/answer_empty.gif\" border=0 title=\"" . $db->per_dir[$per_dir_exempt] . "\"></center>";
      echo "</td>" .
      "<td>{$row[comment_dir]}</td>" .
      "</tr>";
   }
   else
   {
      echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <th nowrap><input type=submit value=ok></th>
    <td>$row[instrument]</td>
    <th nowrap>$row[firstname] $row[lastname]</th>
    <th align=left>$row[email]</th>
    <th>$row[phone1]</th>
    <td>" . $db->per_stat[$row[status]] . "</td>
    <th>";
      select_status($row[status_dir]);
      echo "</th>
    <th><input type=text size=40 name=comment_dir value=\"$row[comment_dir]\"></th>
    </tr>";
   }
}
?>

</table>
</form>
