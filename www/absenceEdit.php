<?php

require 'framework.php';

function get_project()
{
   global $db;

   $query = "select project.id as id, name, semester, year, date "
           . "from project, plan "
           . "where plan.id=" . request('id_plan') . " "
           . "and plan.id_project = project.id";
   $stmt = $db->query($query);
   return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_groups()
{
   global $whoami;
   global $db;

   $query = "select groups.id as id, groups.name as name "
           . "from participant, instruments, groups, person, plan "
           . "where participant.id_person = person.id "
           . "and person.id = " . $whoami->id() . " "
           . "and participant.id_project = plan.id_project "
           . "and plan.id = " . request('id_plan') . " "
           . "and participant.id_instruments = instruments.id "
           . "and instruments.id_groups = groups.id";
   $stmt = $db->query($query);
   return $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($sort == NULL)
   $sort = 'list_order,firstname,lastname';

$prj = get_project();
$grp = get_groups();

$style = '';

if ($action == 'update')
{
   $style = "style=\"background-color:lightgreen\"";

   foreach ($_REQUEST as $key => $val)
   {
      if (strstr($key, ':'))
      {
         list($field, $id_person) = explode(':', $key);
         if ($field == "status")
         {
            $query = "replace into absence "
                    . "(id_person, id_plan, status, comment) "
                    . "values "
                    . "($id_person, " . request('id_plan') . ", $val, " . $db->qpost("comment:$id_person") . ")";
            $db->query($query);
         }
      }
   }
}

echo "
    <h1><a href=\"absence.php?id_project=" . $prj['id'] . "\">Fravær</a></h1>
    <h2>" . $prj['name'] . " " . $prj['semester'] . "-" . $prj['year'] . "</h2>
    <h3>" . strftime('%a %e.%b', $prj['date']) . "</h3>
    <form action='$php_self' method=post>
    <input type=hidden name=id_plan value=" . request('id_plan') . ">
    <input type=hidden name=_sort value=$sort>
    <input type=hidden name=_action value=update>
    <input type=submit value=Lagre $style>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0><a href=\"$php_self?id_plan=" . request('id_plan') . "&_sort=firstname,lastname\">Navn</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?id_plan=" . request('id_plan') . "&_sort=list_order,firstname,lastname\">Instrument</a></th>\n";

for ($i = 0; $i < sizeof($db->abs_stat); $i++)
   echo "<th bgcolor=#A6CAF0>" . $db->abs_stat[$i] . "</th>\n";

echo "<th bgcolor=#A6CAF0>Kommentar</th>\n</tr>\n";

$query = "SELECT participant.id_person as id_person, firstname, lastname, "
        . "person.status as status, instrument, plan.id as id_plan "
        . "FROM person, participant, instruments, groups, plan "
        . "where groups.id = " . $grp['id'] . " "
        . "and instruments.id_groups = groups.id "
        . "and participant.id_instruments = instruments.id "
        . "and participant.id_project = plan.id_project "
        . "and participant.stat_final = $db->par_stat_yes "
        . "and person.id = participant.id_person "
        . "and plan.id = " . request('id_plan') . " "
        . "order by $sort";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   echo "<tr>
      <td>" . $row['firstname'] . " " . $row['lastname'] . "</td>
      <td>" . $row['instrument'] . "</td>\n";

   $q = "select status, comment from absence "
           . "where id_plan=" . request('id_plan') . " "
           . "and id_person=" . $row['id_person'];
   $s = $db->query($q);
   $e = $s->fetch(PDO::FETCH_ASSOC);

   for ($i = 0; $i < sizeof($db->abs_stat); $i++)
   {
      echo "<td align=center><input type=radio name=\"status:" . $row['id_person'] . "\" value=$i";
      if (!is_null($e) && $e['status'] == $i)
         echo " checked";
      echo "></td>\n";
   }

   echo "<td><input type=text name=\"comment:" . $row['id_person'] . "\" value=\"" . $e['comment'] . "\" size=30></td>\n";
   echo "</tr>";
}

echo "</table>\n";
echo "</form>\n";



