<?php

require 'framework.php';

if (is_null($sort))
   $sort = 'groups.name';

echo "
    <h1>Grupper</h1>";
if ($access->auth(AUTH::GRP))
   echo "
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Ny gruppe\">
    </form>";
echo "
    <form action='$php_self' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::GRP))
   echo "
      <th bgcolor=#A6CAF0>Edit</th>";
echo "
      <th bgcolor=#A6CAF0>Navn</th>
      <th bgcolor=#A6CAF0>Ansvarlig</th>
      <th bgcolor=#A6CAF0>Medlemmer/Instrumentgrupper</th>
      <th bgcolor=#A6CAF0>Kommentar</th>
      </tr>";

function member_select($id_groups)
{
   global $db;

   $q = "SELECT person.id as id, firstname, lastname, instrument " .
           "FROM person, instruments " .
           "where instruments.id = person.id_instruments " .
           "and not person.status = $db->per_stat_quited " .
           "order by instruments.list_order, lastname, firstname";
   $s = $db->query($q);

   $q2 = "SELECT id_person, role FROM member where id_groups = $id_groups";
   $s2 = $db->query($q2);
   $r2 = $s2->fetchAll(PDO::FETCH_ASSOC);

   echo "<select name=\"id_persons[]\" multiple title=\"Velg personer inn i gruppen.\nCtrl-click to select/unselect single\">";

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e[id] . "\"";
      reset($r2);
      foreach ($r2 as $e2)
         if ($e[id] == $e2[id_person])
            echo " selected";
      echo ">$e[firstname] $e[lastname] ($e[instrument])";
   }
   echo "</select>";
   
   reset($r2);
   foreach ($r2 as $e2)
      echo "<input type=hidden name=\"role:$e2[id_person]\" value=\"$e2[role]\">";
}

function member_list($id_groups)
{
   global $db;
   global $sort;
   global $access;

   $q = "SELECT firstname, lastname, instrument, member.role as role, "
           . "person.id as id_person, groups.id as id_groups "
           . "FROM person, instruments, member, groups "
           . "where instruments.id = person.id_instruments "
           . "and groups.id = member.id_groups "
           . "and person.id = member.id_person "
           . "and groups.id = $id_groups "
           . "order by instruments.list_order, lastname, firstname";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      if ($_REQUEST[id_groups] == $e[id_groups] && $_REQUEST[id_person] == $e[id_person])
      {
         echo "<form action=\"$php_self\" method=post>\n"
         . "<input type=hidden name=_sort value=\"$sort\">\n"
         . "<input type=submit value=ok title=\"Lagre rolle...\">\n"
         . "<input type=hidden name=_action value=update_role>\n"
         . "<input type=hidden name=id_person value=$_REQUEST[id_person]>\n"
         . "<input type=hidden name=id_groups value=$_REQUEST[id_groups]>\n"
         . "$e[firstname] $e[lastname] "
         . "<input type=text size=15 name=role value=\"$e[role]\" title=\"Spesifiser rolle...\">\n"
         . "</form><br>\n";
      } 
      else
      {
         if ($access->auth(AUTH::GRP))
            echo "<a href=\"$php_self?id_groups=$e[id_groups]&id_person=$e[id_person]\"><img src=\"images/cross_re.gif\" border=0 title=\"Editere rolle...\"></a> ";
         echo "$e[firstname] $e[lastname] ($e[instrument]) <i>$e[role]</i><br>";
      }
   }
}

function instruments_list($id_groups)
{
   global $db;

   $q = "select instrument from instruments where id_groups = $id_groups";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "$e[instrument]<br>";
   }
}

function person_select($selected)
{
   global $db;

   echo "<select name=id_person title=\"Angi hvem som er leder for gruppen\">";

   $q = "SELECT person.id as id, firstname, lastname, instrument FROM person, instruments " .
           "where person.id_instruments = instruments.id " .
           "order by instrument, lastname, firstname";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e[id] . "\"";
      if ($e[id] == $selected)
         echo " selected";
      echo ">$e[firstname] $e[lastname] ($e[instrument])";
   }
   echo "</select>";
}

function member_update($id_groups)
{
   global $db;

   $query = "delete from member where id_groups = $id_groups";
   $db->query($query);
   $i = 0;
   
   if (!is_null($_POST[id_persons]))
   {
      foreach ($_POST[id_persons] as $id_person)
      {
         $role = $_POST["role:$id_person"];
         $query = "insert into member (id_person, id_groups, role) " .
                 "values ($id_person, $id_groups, '$role')";
         $db->query($query);
      }
   }
}

if ($action == 'new')
{
   global $sort;

   echo "  <tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=submit value=ok></td>
    <td><input type=text size=30 name=name></td>
    <td>";
   person_select(0);
   echo "</td><td>";
   member_select(0);
   echo "
    </td>
    <td><textarea cols=60 rows=7 wrap=virtual name=comment></textarea></td>
  </tr>";
}

if ($action == 'update' && $access->auth(AUTH::GRP))
{
   $id_person = $_POST[id_person];
   if (is_null($id_person))
      $id_person = 0;

   if (is_null($no))
   {
      $query = "insert into groups (name, id_person, comment) " .
              "values ('$_POST[name]', $id_person, '$_POST[comment]')";
      $db->query($query);
      $no = $db->lastInsertId();
      member_update($no);
   } else
   {
      if (!is_null($delete))
      {
         $q = "select count(*) as count from instruments where id_groups = {$no}";
         $s = $db->query($q);
         $e = $s->fetch(PDO::FETCH_ASSOC);
         if ($e[count] == 0)
         {
            $db->query("delete from member where id_groups = $no");
            $db->query("DELETE FROM groups WHERE id = $no");
         } else
            echo "<font color=red>Error: Some instruments are already part of this group</font>";
      }
      else
      {
         $query = "update groups set name = '$_POST[name]'," .
                 "id_person = $id_person," .
                 "comment = '$_POST[comment]' " .
                 "where id = $no";
         $db->query($query);
         member_update($no);
      }
   }

   $no = NULL;
}

if ($action == 'update_role' && $access->auth(AUTH::GRP))
{
   $query = "update member set role = '$_POST[role]' "
           . "where id_person = $_POST[id_person] "
           . "and id_groups = $_POST[id_groups]";
   $db->query($query);
   
   $_REQUEST[id_groups] = null;
}


$query = "SELECT groups.id as id, groups.name as name, firstname, lastname, instrument, groups.comment as comment " .
        "FROM groups, person, instruments " .
        "where person.id = groups.id_person " .
        "and instruments.id = person.id_instruments " .
        "order by $sort";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($row[id] != $no)
   {
      echo "<tr>";
      if ($access->auth(AUTH::GRP))
         echo "
         <td><center>
           <a href=\"$php_self?_sort=$sort&_action=view&_no=$row[id]\"><img src=\"images/cross_re.gif\" border=0></a>
             </center></td>";
      echo
      "<td>$row[name]</td>" .
      "<td>$row[firstname] $row[lastname] ($row[instrument])</td><td>";
      instruments_list($row[id]);
      member_list($row[id]);
      echo "</td><td>";
      echo str_replace("\n", "<br>\n", $row[comment]);
      echo "</td>" .
      "</tr>";
   } else
   {
      echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <th nowrap><input type=submit value=ok>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette $row[name]?');\"></th>
    <th><input type=text size=30 name=name value=\"$row[name]\"></th>
    <th>";
      person_select($row[id_person]);
      echo "</td><td>";
      instruments_list($row[id]);
      member_select($no);
      echo "</th>
    <th><textarea cols=60 rows=7 wrap=virtual name=comment>$row[comment]</textarea></th>
    </tr>";
   }
}

echo "
    </table>
  </form>";

