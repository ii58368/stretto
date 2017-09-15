<?php

require 'framework.php';

function get_project()
{
   global $db;

   $query = "select name, semester, year "
           . " from project"
           . " where id=$_REQUEST[id_project]";
   $stmt = $db->query($query);
   return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_groups()
{
   global $whoami;
   global $db;

   $query = "select groups.id as id "
           . "from participant, instruments, groups, person "
           . "where participant.id_person = person.id "
           . "and person.id = " . $whoami->id() . " "
           . "and participant.id_project = $_REQUEST[id_project] "
           . "and participant.id_instruments = instruments.id "
           . "and instruments.id_groups = groups.id";

   $stmt = $db->query($query);
   $grp = $stmt->fetch(PDO::FETCH_ASSOC);

   if ($grp == null)
   {
      $query = "select groups.id as id "
              . "from instruments, groups, person "
              . "where person.id = " . $whoami->id() . " "
              . "and person.id_instruments = instruments.id "
              . "and instruments.id_groups = groups.id";

      $stmt = $db->query($query);
      $grp = $stmt->fetch(PDO::FETCH_ASSOC);
   }
   return $grp[id];
}

function get_seating($id_groups)
{
   global $db;

   $query = "select template, firstname, lastname, seating.ts as ts "
           . "from seating, person "
           . "where seating.id_person = person.id "
           . "and id_project=$_REQUEST[id_project] "
           . "and id_groups=$id_groups";

   $stmt = $db->query($query);
   return $stmt->fetch(PDO::FETCH_ASSOC);
}

function select_template($selected)
{
   $opt = array(
       "Blank",
       "Violin 1, 8 pulter",
       "Violin 1, 7,5 pulter",
       "Violin 2, 9 pulter",
       "Viola, 9 pulter",
       "Cello, 8 pulter",
       "Cello, 7,5 pulter"
   );
   echo "<select name=template onChange=\"submit();\">\n";
   for ($i = 0; $i < sizeof($opt); $i++)
   {
      echo "<option value=$i";
      if ($i == $selected)
         echo " selected";
      echo ">$opt[$i]</option>\n";
   }
   echo "</select>\n";
}

function update_seating($id_groups, $template)
{
   global $whoami;
   global $db;

   $ts = strtotime("now");

   if (!($seat = get_seating($id_groups)))
   {
      $query = "insert into seating (id_groups, id_project, template, id_person, ts) "
              . "values ($id_groups, $_POST[id_project], $template, "
              . $whoami->id() . ", $ts)";
   } else
   {
      if (is_null($template))
         $template = $seat[template];

      $query = "update seating set template = $template,"
              . "id_person = " . $whoami->id() . ","
              . "ts = '$ts' "
              . "where id_groups = $id_groups "
              . "and id_project = $_POST[id_project]";
   }
   $db->query($query);
}

$grp_id = get_groups();

if ($action == 'template')
{
   update_seating($grp_id, $_POST[template]);
}


if (is_null($sort))
   $sort = 'position,list_order,firstname,lastname';

if ($action == 'update')
{
   $position = is_null($_POST[position]) ? 'NULL' : $_POST[position];
   $query = "update participant set position = $position," .
           "comment_pos = '$_POST[comment_pos]' " .
           "where id_person = $no " .
           "and id_project = $_REQUEST[id_project]";
   $db->query($query);
   $no = NULL;

   update_seating($grp_id, null);
}

$prj = get_project();
$seat = get_seating($grp_id);

echo "
    <h1>Gruppeoppsett</h1>
    <h2>$prj[name] $prj[semester]-$prj[year]</h2>";
if ($access->auth(AUTH::SEAT))
{
   echo "
    <form action='$php_self' method=post>
       <input type=hidden name=_action value=template>
       <input type=hidden name=_sort value='$sort'>
       <input type=hidden name=id_project value=$_REQUEST[id_project]>\n";
   select_template($seat[template]);
   echo "
    </form>
    <form action='$php_self' method=post>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Edit</th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?id_project=$_REQUEST[id_project]&_sort=firstname,lastname\">Navn</a></th>
      <th bgcolor=#A6CAF0>Instrument</th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?id_project=$_REQUEST[id_project]&_sort=position,list_order,firstname,lastname\">Plass</a></th>
      <th bgcolor=#A6CAF0>Kommentar</th>
      </tr>";


   $query = "SELECT participant.id_person as id, firstname, lastname, instrument, position, comment_pos "
           . "FROM person, participant, instruments, groups "
           . "where person.id = participant.id_person "
           . "and participant.id_project = $_REQUEST[id_project] "
           . "and participant.id_instruments = instruments.id "
           . "and instruments.id_groups = groups.id "
           . "and groups.id = $grp_id "
           . "order by $sort";

   $stmt = $db->query($query);

   foreach ($stmt as $row)
   {
      if ($row[id] != $no)
      {
         echo "<tr>
         <td><center>
           <a href=\"$php_self?_sort=$sort&_action=view&_no=$row[id]&id_project=$_REQUEST[id_project]\"><img src=\"images/cross_re.gif\" border=0></a>
             </center></td>" .
         "<td>$row[firstname] $row[lastname]</td>" .
         "<td>$row[instrument]</td>" .
         "<td>$row[position]</td>" .
         "<td>$row[comment_pos]</td>" .
         "</tr>";
      } else
      {
         echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <input type=hidden name=id_project value=$_REQUEST[id_project]>
    <th nowrap><input type=submit value=ok>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette {$row[name]}?');\"></th>
    <td>$row[firstname] $row[lastname]</td>
    <td>$row[instrument]</td>
    <th><input type=text size=2 name=position value=\"$row[position]\"></th>
    <th><input type=text size=30 name=comment_pos value=\"$row[comment_pos]\"></th>
    </tr>";
      }
   }


   echo "
</table>
</form>";
}

echo "<img src=\"map.php?id_groups=$grp_id&id_project=$_REQUEST[id_project]&template=$seat[template]\"><br>\n";

if (!is_null($seat))
   echo "$seat[firstname]/" . strftime('%e.%b %y', $seat[ts]) . "\n";
