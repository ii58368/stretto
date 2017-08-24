<?php
require 'framework.php';

function get_participant($id_person)
{
   global $db;

   $q = "select * from participant where id_person=$id_person and id_project=$_REQUEST[id]";
   $s = $db->query($q);
   $r = $s->fetch(PDO::FETCH_ASSOC);

   return $r;
}

function manage_instrument($selected, $row, $edit)
{
   global $db;
   global $access;

   echo "<td>";

   if ($edit && $access->auth(AUTH::RES_INV))
   {
      echo "<select name=id_instruments:$row>";

      $q = "SELECT id, instrument FROM instruments order by list_order";
      $s = $db->query($q);
      foreach ($s as $e)
      {
         echo "<option value=\"" . $e[id] . "\"";
         if ($e[id] == $selected)
            echo " selected";
         echo ">" . $e[instrument];
      }

      echo "</select>";
   }
   else
   {
      $q = "SELECT instrument FROM instruments where id = $selected";
      $s = $db->query($q);
      $e = $s->fetch(PDO::FETCH_ASSOC);
      echo "$e[instrument]";
   }
   echo "</td>";
}

function stat_select($name, $selected, $valid_par_stat)
{
   global $db;

   echo "<select name=$name>";

   for ($i = 0; $i < count($db->par_stat); $i++)
   {
      if ($valid_par_stat & (1 << $i))
      {
         echo "<option value=$i";
         if ($selected == $i)
            echo " selected";
         echo ">" . $db->par_stat[$i] . "</option>\n";
      }
   }
   echo "</select>";
}

function manage_inv($part, $row, $edit)
{
   global $db;
   global $access;

   echo "<td align=center>";

   if ($edit && $access->auth(AUTH::RES_INV))
   {
      echo "<input type=checkbox name=stat_inv:$row";
      if ($part[stat_inv] == $db->par_stat_yes)
         echo " checked";
      echo " value=$db->par_stat_yes>";
      echo "<input type=hidden name=comment_inv:$row value=\"\">";
      //   echo "<input type=text name=comment_inv:$row size=20 value=\"$part[comment_inv]\">";
   } else
   {
      if ($part != null || $part[stat_inv] != $db->par_stat_void)
      {
         if ($part[stat_inv] == $db->par_stat_yes)
            echo "<img border=0 src=\"images/tick2.gif\" title=\"" . $db->par_stat[$part[stat_inv]] . "\">\n";
         //      if ($part[stat_inv])
         //         echo "<i>" . date('j.M', $part[ts_inv]) . "</i>";
         echo "<br>" . str_replace("\n", "<br>\n", $part[comment_inv]);
      }
   }
   echo "</td>";
}

function manage_self($part, $row, $edit)
{
   global $db;
   global $access;

   echo "<td>";
   if ($part != null || $part[stat_self] != $db->par_stat_void)
   {
      echo "<img border=0 src=\"images/part_stat_$part[stat_self].gif\" title=\"" . $db->par_stat[$part[stat_self]] . "\">\n";
      if ($part[stat_self])
         echo "<i>" . date('j.M', $part[ts_self]) . "</i>";
      echo "<br>" . str_replace("\n", "<br>\n", $part[comment_self]);
   }
   echo "</td>";
}

function manage_reg($part, $row, $edit, $valid_par_stat)
{
   global $db;
   global $access;

   echo "<td>";

   if ($edit && $access->auth(AUTH::RES_REG))
   {
      stat_select("stat_reg:$row", $part[stat_reg], $valid_par_stat);
      echo "<input type=text name=comment_reg:$row size=20 value=\"$part[comment_reg]\">";
   } else
   {
      if ($part != null || $part[stat_reg] != $db->par_stat_void)
      {
         echo "<img border=0 src=\"images/part_stat_$part[stat_reg].gif\" title=\"" . $db->par_stat[$part[stat_reg]] . "\">\n";
         if ($part[stat_reg])
            echo "<i>" . date('j.M', $part[ts_reg]) . "</i>";
         echo "<br>" . str_replace("\n", "<br>\n", $part[comment_reg]);
      }
   }
   echo "</td>";
}

function manage_req($part, $row, $edit)
{
   global $db;
   global $access;

   echo "<td>";

   if ($edit && $access->auth(AUTH::RES_REQ))
   {
      stat_select("stat_req:$row", $part[stat_req], 0xff);
      echo "<input type=text name=comment_req:$row size=20 value=\"$part[comment_req]\">";
   } else
   {
      if ($part != null || $part[stat_req] != $db->par_stat_void)
      {
         echo "<img border=0 src=\"images/part_stat_$part[stat_req].gif\" title=\"" . $db->par_stat[$part[stat_req]] . "\">\n";
         if ($part[stat_req])
            echo "<i>" . date('j.M', $part[ts_req]) . "</i>";
         echo "<br>" . str_replace("\n", "<br>\n", $part[comment_req]);
      }
   }
   echo "</td>";
}

function manage_final($part, $row, $edit)
{
   global $db;
   global $access;

   echo "<td>";

   if ($edit && $access->auth(AUTH::RES_FIN))
   {
      echo "<input type=checkbox name=stat_final:$row";
      if ($part[stat_final] == $db->par_stat_yes)
         echo " checked";
      echo " value=" . $db->par_stat_yes . ">";
      echo "<input type=text name=comment_final:$row size=20 value=\"$part[comment_final]\">";
   } else
   {
      if ($part != null || $part[stat_final] != $db->par_stat_void)
      {
         echo "<img border=0 src=\"images/part_stat_$part[stat_final].gif\" title=\"" . $db->par_stat[$part[stat_final]] . "\">\n";
         if ($part[stat_final])
            echo "<i>" . date('j.M', $part[ts_final]) . "</i>";
         echo "<br>" . str_replace("\n", "<br>\n", $part[comment_final]);
      }
   }
   echo "</td>";
}

function manage_col($col)
{
   if ($_REQUEST[col] == $col)
   {
      echo "<input type=submit value=lagre>"
      . "<input type=hidden name=col value=$col>";
   } else
   {
      echo "<a href=\"$php_self?id=$_REQUEST[id]&col=$col\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>";
   }
}

function update_cell($id_person, $col, $status, $comment, $id_instruments)
{
   global $db;

   $id_project = $_REQUEST[id];
   $ts = strtotime("now");

   if ($status == null)
      $status = $db->par_stat_no;

   $q = "select * from participant where id_project=$id_project and id_person=$id_person";
   $stmt = $db->query($q);
   if ($stmt->rowCount() == 0)
   {
      if ($status == $db->par_stat_void)
         return;
      $query = "insert into participant (id_person, id_project, stat_$col, ts_$col, comment_$col, id_instruments) " .
              "values ($id_person, $id_project, $status, $ts, '$comment', $id_instruments)";
   } else
   {
      $query = "update participant set " .
              "stat_$col = $status, " .
              "ts_$col = $ts, " .
              "comment_$col = '$comment', " .
              "id_instruments = $id_instruments " .
              "where id_person = $id_person " .
              "and id_project = $id_project";
   }
   $db->query($query);
}

if ($sort == NULL)
   $sort = 'status,list_order,lastname,firstname';

if ($action == 'update')
{
   if ($no != null)
   {
      $id_instruments = $_REQUEST["id_instruments:$no"];

      $stat_inv = $_REQUEST["stat_inv:$no"];
      update_cell($no, "inv", $stat_inv, null, $id_instruments);

      $stat_req = $_REQUEST["stat_req:$no"];
      $comment_req = $_REQUEST["comment_req:$no"];
      update_cell($no, "req", $stat_req, $comment_req, $id_instruments);

      $stat_reg = $_REQUEST["stat_reg:$no"];
      $comment_reg = $_REQUEST["comment_reg:$no"];
      update_cell($no, "reg", $stat_reg, $comment_reg, $id_instruments);

      $stat_final = $_REQUEST["stat_final:$no"];
      $comment_final = $_REQUEST["comment_final:$no"];
      update_cell($no, "final", $stat_final, $comment_final, $id_instruments);

      $no = null;
   }
   if (($col = $_REQUEST[col]) != null)
   {
      foreach ($_REQUEST as $key => $val)
      {
         list($field, $pid) = split(':', $key);
         if ($field == "comment_$col")
            update_cell($pid, $col, $_REQUEST["stat_$col:$pid"], $val, $_REQUEST["id_instruments:$pid"]);
      }
      $_REQUEST[col] = null;
   }
}

$query = "select name, semester, year, deadline, orchestration, valid_par_stat"
        . " from project where id=$_REQUEST[id]";
$stmt = $db->query($query);
$prj = $stmt->fetch(PDO::FETCH_ASSOC);

echo "
    <h1>Deltagelse $prj[name] ($prj[semester]-$prj[year])</h1>
    <h2>";
if ($prj[orchestration] == $prj_orch_tutti)
   echo "Permisjonsfrist: ";
else
   echo "Påmeldingsfrist: ";
echo date('j.M.y', $prj[deadline]) . "</h2>
    <form action='$php_self' method=post>
    <input type=hidden name=_action value=update>
    <input type=hidden name=id value=$_REQUEST[id]>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::RES_INV, AUTH::RES_REG, AUTH::RES_REQ, AUTH::RES_FIN))
   echo "
      <th bgcolor=#A6CAF0>Edit</th>";
echo "
      <th bgcolor=#A6CAF0>Navn</th>
      <th bgcolor=#A6CAF0>Status</th>
      <th bgcolor=#A6CAF0>Instrument</th>
      <th bgcolor=#A6CAF0>";
if ($access->auth(AUTH::RES_INV))
   manage_col("inv");
echo "Bes</th>
      <th bgcolor=#A6CAF0>Egen</th>
      <th bgcolor=#A6CAF0>";
if ($access->auth(AUTH::RES_REG))
   manage_col("reg");
echo "Sekretær</th>
      <th bgcolor=#A6CAF0>";
if ($access->auth(AUTH::RES_REQ))
   manage_col("req");
echo "MR</th>
      <th bgcolor=#A6CAF0>";
if ($access->auth(AUTH::RES_FIN))
   manage_col("final");
echo "Styret</th>
      </tr>";



$query = "SELECT person.id as id, firstname, lastname, status, id_instruments " .
        "FROM person, instruments " .
        "where id_instruments = instruments.id " .
        "and not status = $db->per_stat_quited " .
        "order by $sort";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   echo "<tr>";
   if ($access->auth(AUTH::RES_INV, AUTH::RES_REG, AUTH::RES_REQ, AUTH::RES_FIN))
   {
      echo "
        <td><center>";
      if ($row[id] == $no)
      {
         echo "<input type=submit value = lagre>"
         . "<input type=hidden name=_no value='$no'>";
      } else
      {
         echo "<a href=\"$php_self?_no=$row[id]&id=$_REQUEST[id]\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>";
      }
      echo "</center></td>";
   }
   echo "<td>$row[firstname] $row[lastname]</td>"
   . "<td>" . $db->per_stat[$row[status]] . "</td>";

   $part = get_participant($row[id]);
   $id_instruments = ($part[id_instruments] == null) ? $row[id_instruments] : $part[id_instruments];
   manage_instrument($id_instruments, $row[id], $row[id] == $no || $_REQUEST[col] != null);
   manage_inv($part, $row[id], $row[id] == $no || $_REQUEST[col] == "inv");
   manage_self($part, $row[id], false);
   manage_reg($part, $row[id], $row[id] == $no || $_REQUEST[col] == "reg", $prj[valid_par_stat]);
   manage_req($part, $row[id], $row[id] == $no || $_REQUEST[col] == "req");
   manage_final($part, $row[id], $row[id] == $no || $_REQUEST[col] == "final");

   echo "</tr>";
}
?> 

</table>
</form>

<?php
require 'framework_end.php';
?>

