<?php
require 'framework.php';

function get_participant($id_person)
{
   global $db;

   $q = "select * from participant where id_person=$id_person and id_project=" . request('id');
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
      echo "<select name=id_instruments:$row title=\"Velg instrument som vedkommende skal spille på dette prosjektet\">";

      $q = "SELECT id, instrument FROM instruments order by list_order";
      $s = $db->query($q);
      foreach ($s as $e)
      {
         echo "<option value=\"" . $e['id'] . "\"";
         if ($e['id'] == $selected)
            echo " selected";
         echo ">" . $e['instrument'];
      }

      echo "</select>";
   }
   else
   {
      $q = "SELECT instrument FROM instruments where id = $selected";
      $s = $db->query($q);
      $e = $s->fetch(PDO::FETCH_ASSOC);
      echo $e['instrument'];
   }
   echo "</td>";
}

function stat_select($name, $selected, $valid_par_stat)
{
   global $db;

   echo "<select name=$name title=\"Velg ny ressursstatus\">";

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

function manage_inv($part, $row, $pstat, $edit)
{
   global $db;
   global $access;

   echo "<td align=center>";

   if ($edit && $access->auth(AUTH::RES_INV))
   {
      echo "<input type=checkbox name=stat_inv:$row title=\"Merk av hvis denne ressursen er aktuell for dette prosjektet\"";
      if ((is_null($part) && $pstat == $db->per_stat_member) || (!is_null($part) && $part['stat_inv'] == $db->par_stat_yes))
         echo " checked";
      echo " value=$db->par_stat_yes>";
      echo "<input type=hidden name=comment_inv:$row value=\"\" title=\"Legg inn eventuell tilleggskommentar\">";
      //   echo "<input type=text name=comment_inv:$row size=20 value=\"$part[comment_inv]\">";
   }
   else
   {
      if (!is_null($part) && $part['stat_inv'] != $db->par_stat_void)
      {
         if ($part['stat_inv'] == $db->par_stat_yes)
            echo "<img border=0 src=\"images/tick2.gif\" title=\"" . $db->par_stat[$part['stat_inv']] . "\">\n";
         //      if ($part[stat_inv])
         //         echo "<i>" . strftime('%e.%m', $part[ts_inv]) . "</i>";
         echo "<br>" . str_replace("\n", "<br>\n", $part['comment_inv']);
      }
   }
   echo "</td>";
}

function manage_self($part, $row, $edit)
{
   global $db;
   global $access;

   echo "<td>";
   if (!is_null($part) && $part['stat_self'] != $db->par_stat_void)
   {
      echo "<img border=0 src=\"images/part_stat_" . $part['stat_self'] . ".gif\" title=\"" . $db->par_stat[$part['stat_self']] . "\">\n";
      if ($part['stat_self'])
         echo "<i>" . strftime('%e.%m', $part['ts_self']) . "</i>";
      echo "<br>" . str_replace("\n", "<br>\n", $part['comment_self']);
   }
   echo "</td>";
}

function manage_reg($part, $row, $edit, $valid_par_stat)
{
   global $db;
   global $access;

   echo "<td>";

   if (!is_null($part) && $part['stat_inv'] == $db->par_stat_yes)
   {
      if ($edit && $access->auth(AUTH::RES_REG))
      {
         stat_select("stat_reg:$row", $part['stat_reg'], $valid_par_stat | (1 << $db->par_stat_void));
         echo "<input type=text name=comment_reg:$row size=20 value=\"" . $part['comment_reg'] . "\" title=\"Legg inn eventuell tilleggskommentar\">";
      }
      else
      {
         if ($part['stat_reg'] != $db->par_stat_void)
         {
            echo "<img border=0 src=\"images/part_stat_" . $part['stat_reg'] . ".gif\" title=\"" . $db->par_stat[$part['stat_reg']] . "\">\n";
            if ($part['stat_reg'])
               echo "<i>" . strftime('%e.%m', $part['ts_reg']) . "</i>";
            echo "<br>" . str_replace("\n", "<br>\n", $part['comment_reg']);
         }
      }
   }
   echo "</td>";
}

function manage_req($part, $row, $edit, $orchestration)
{
   global $db;
   global $access;

   echo "<td>";

   if (!is_null($part) && $part['stat_inv'] == $db->par_stat_yes)
   {
      if ($edit && $access->auth(AUTH::RES_REQ))
      {
         if ($orchestration == $db->prj_orch_tutti)
         {
            $htext = "Kryss av for å anbefale permisjon";
            $val = $db->par_stat_no;
         }
         else
         {
            $htext = "Kryss av for å innstille";
            $val = $db->par_stat_yes;
         }
         echo "<input type=checkbox name=stat_req:$row title=\"$htext\"";
         if (($part['stat_req'] == $db->par_stat_no && $orchestration == $db->prj_orch_tutti)
          || ($part['stat_req'] == $db->par_stat_yes && $orchestration == $db->prj_orch_reduced))
            echo " checked";
         echo " value=$val>";
         
         echo "<input type=text name=comment_req:$row size=20 value=\"" . $part['comment_req'] . "\" title=\"Legg inn eventuell tilleggskommentar\">";
      }
      else
      {
         if ($part['stat_req'] != $db->par_stat_void)
         {
            echo "<img border=0 src=\"images/part_stat_" . $part['stat_req'] . ".gif\" title=\"" . $db->par_stat[$part['stat_req']] . "\">\n";
            if ($part['stat_req'])
               echo "<i>" . strftime('%e.%m', $part['ts_req']) . "</i>";
            echo "<br>" . str_replace("\n", "<br>\n", $part['comment_req']);
         }
      }
   }
   echo "</td>";
}

function manage_final($part, $row, $edit, $orchestration)
{
   global $db;
   global $access;

   echo "<td>";

   if (!is_null($part) && ($part['stat_inv'] == $db->par_stat_yes || $part['stat_final'] != $db->par_stat_void))
   {
      if ($edit && $access->auth(AUTH::RES_FIN))
      {
         echo "<input type=checkbox name=stat_final:$row";
         if ($part['stat_final'] == $db->par_stat_yes || 
                 ($part['stat_final'] == $db->par_stat_void && 
                 $part['stat_inv'] == $db->par_stat_yes &&
                 $orchestration == $db->prj_orch_tutti &&
                 $part['stat_req'] != $db->par_stat_no))
            echo " checked";
         echo " value=" . $db->par_stat_yes . " title=\"Merk av dersom vedkommende er tatt ut til å delta på prosjektet\">";
         echo "<input type=text name=comment_final:$row size=20 value=\"" . $part['comment_final'] . "\" title=\"Legg inn eventuell tilleggskommentar\">";
      } else
      {
         if ($part['stat_final'] != $db->par_stat_void)
         {
            echo "<img border=0 src=\"images/part_stat_" . $part['stat_final'] . ".gif\" title=\"" . $db->par_stat[$part['stat_final']] . "\">\n";
            if ($part['stat_final'])
               echo "<i>" . strftime('%e.%m', $part['ts_final']) . "</i>";
            echo "<br>" . str_replace("\n", "<br>\n", $part['comment_final']);
         }
      }
   }
   echo "</td>";
}

function manage_col($col, $htxt)
{
   global $php_self;
   global $sort;

   if (request('col') == $col)
   {
      echo "<input type=submit value=lagre title=\"Lagre\">"
      . "<input type=hidden name=col value=$col>"
      . "<input type=hidden name=_sort value=$sort>";
   }
   else
   {
      echo "<a href=\"$php_self?id=" . request('id') . "&col=$col&_sort=$sort\"><img src=\"images/cross_re.gif\" border=0 title=\"$htxt\"></a>";
   }
}

function reset_col($col)
{
   global $php_self;
   global $sort;
   
   echo "<a href=\"$php_self?id=" . request('id') . "&col=$col&_action=reset&_sort=$sort\" onClick=\"return confirm('Sikkert at du vil nullstille hele kolonnen?');\"><img src=\"images/cross_re.gif\" border=0 title=\"Nullstill vedtaket...\"></a>";   
}

function view_leave($id_person, $year, $semester)
{
   global $db;

   echo "<td>";

   $date_min = ($semester == 'V') ? "1. jan" : "1. jul";
   $date_max = ($semester == 'V') ? "30. jun" : "31. dec";

   $ts_min = strtotime("$date_min $year");
   $ts_max = strtotime("$date_max $year");

   $query = "select ts_from, ts_to, status, text "
           . "from `leave` "
           . "where id_person = $id_person "
           . "and ((ts_from >= $ts_min and ts_to <= $ts_max) "
           . "or (ts_from < $ts_min and ts_to > $ts_min) "
           . "or (ts_from < $ts_max and ts_to > $ts_max) "
           . "or (ts_from < $ts_min and ts_to > $ts_max))";
   $stmt = $db->query($query);

   $first_time = true;

   foreach ($stmt as $e)
   {
      if (!$first_time)
         echo "<hr>\n";
      $first_time = false;

      echo "<i>" . strftime('%e.%m %y', $e['ts_from']) . "-" .
      strftime('%e.%m %y', $e['ts_to']) . "</i><br>\n";
      echo "status: " . $db->lea_stat[$e['status']] . "<br>\n";
      echo str_replace("\n", "<br>\n", $e['text']);
   }

   echo "</td>";
}

function update_cell($id_person, $col, $status, $comment, $id_instruments)
{
   global $db;

   if (is_null($comment))
      return;
   
   $id_project = request('id');
   $ts = strtotime("now");

   if (is_null($status))
      $status = $db->par_stat_void;

   $q = "select * from participant where id_project=$id_project and id_person=$id_person";
   $stmt = $db->query($q);
   if ($stmt->rowCount() == 0)
   {
      if ($status == $db->par_stat_void)
         return;
      $query = "insert into participant (id_person, id_project, stat_$col, ts_$col, comment_$col, id_instruments) " .
              "values ($id_person, $id_project, $status, $ts, " . $db->quote($comment) . ", $id_instruments)";
   } else
   {
      $e = $stmt->fetch(PDO::FETCH_ASSOC);
      $query = "update participant set " .
              "stat_$col = $status, ";
      if ($status != $e["stat_$col"])
         $query .= "ts_$col = $ts, ";
      $query .= "comment_$col = " . $db->quote($comment) . ", " .
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
   if (!is_null($no))
   {
      $id_instruments = request("id_instruments:$no");

      $stat_inv = request("stat_inv:$no");
      update_cell($no, "inv", $stat_inv, "", $id_instruments);

      $stat_req = request("stat_req:$no");
      $comment_req = request("comment_req:$no");
      if ($stat_req == null && strlen($comment_req) > 0)
        $stat_req = $db->par_stat_tentative;
      update_cell($no, "req", $stat_req, $comment_req, $id_instruments);

      $stat_reg = request("stat_reg:$no");
      $comment_reg = request("comment_reg:$no");
      update_cell($no, "reg", $stat_reg, $comment_reg, $id_instruments);

      $stat_final = request("stat_final:$no");
      if (is_null($stat_final))
         $stat_final = $db->par_stat_no;
      if (is_null($stat_inv))
         $stat_final = $db->par_stat_void;
      $comment_final = request("comment_final:$no");
      update_cell($no, "final", $stat_final, $comment_final, $id_instruments);

      $no = null;
   }
   if (($col = request('col')) != null)
   {
      foreach ($_REQUEST as $key => $val)
      {
         if (strstr($key, ':'))
         {
            list($field, $pid) = explode(':', $key);
            if ($field == "comment_$col")
            {
               $stat = request("stat_$col:$pid");
               if ($col == 'final' && is_null($stat))
                  $stat = $db->par_stat_no;
               update_cell($pid, $col, $stat, $val, request("id_instruments:$pid"));
            }
         }
      }
      $_REQUEST['col'] = null;
   }
}

if ($action == 'reset')
{
   $q = "update participant set "
           . "stat_final = $db->par_stat_void "
           . "where  id_project = " . request('id');
   $db->query($q);
   
   $_REQUEST['col'] = null;
}

$query = "select name, semester, year, deadline, orchestration, valid_par_stat"
        . " from project where id=" . request('id');
$stmt = $db->query($query);
$prj = $stmt->fetch(PDO::FETCH_ASSOC);

echo "
    <h1>Deltagelse " . $prj['name'] . " (" . $prj['semester'] . "-" . $prj['year'] . ")</h1>
    <h2>";
if ($prj['orchestration'] == $db->prj_orch_tutti)
   echo "Permisjonsfrist: ";
else
   echo "Påmeldingsfrist: ";
echo strftime('%e.%m.%y', $prj['deadline']) . "</h2>
    <form action='$php_self' method=post>
    <input type=hidden name=_action value=update>
    <input type=hidden name=id value=" . request('id') . ">
    <table border=1>
    <tr>";
if ($access->auth(AUTH::RES_INV, AUTH::RES_REG, AUTH::RES_REQ, AUTH::RES_FIN))
   echo "
      <th>Edit</th>";
echo "
      <th><a href=\"$php_self?id=".request('id')."&_sort=lastname,firstname\">Navn</a></th>
      <th><a href=\"$php_self?id=".request('id')."&_sort=status,list_order,lastname,firstname\">Status</a></th>
      <th><a href=\"$php_self?id=".request('id')."&_sort=list_order,lastname,firstname\">Instrument</a></th>
      <th>";
if ($access->auth(AUTH::RES_INV))
   manage_col("inv", "Besetningsliste. \nMerk av alle som må ta stilling til om de skal være med på prosjektet.");
echo "Bes</th>
      <th>Permisjon</th>
      <th>Egen</th>
      <th>";
if ($access->auth(AUTH::RES_REG))
   manage_col("reg", "Tilbakemelding via styret/sekretær");
echo "Sekretær</th>
      <th>";
if ($access->auth(AUTH::RES_REQ))
   manage_col("req", "Instilling fra MR");
echo "MR</th>
      <th>";
if ($access->auth(AUTH::RES_FIN))
{
   manage_col("final", "Vedtatt av styret");
   reset_col("final");
}
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
      if ($row['id'] == $no)
      {
         echo "<input type=submit value=lagre title=Lagre>"
         . "<input type=hidden name=_no value=$no>"
         . "<input type=hidden name=_sort value=$sort>";
      }
      else
      {
         echo "<a href=\"$php_self?_no=" . $row['id'] . "&id=" . request('id') . "&_sort=$sort\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>";
      }
      echo "</center></td>";
   }
   echo "<td>" . $row['firstname'] . " " . $row['lastname'] . "</td>"
   . "<td>" . $db->per_stat[$row['status']] . "</td>";

   $part = get_participant($row['id']);
   $id_instruments = ($part['id_instruments'] == null) ? $row['id_instruments'] : $part['id_instruments'];
   manage_instrument($id_instruments, $row['id'], $row['id'] == $no || request('col') != null);
   manage_inv($part, $row['id'], $row['status'], $row['id'] == $no || request('col') == "inv");
   view_leave($row['id'], $prj['year'], $prj['semester']);
   manage_self($part, $row['id'], false);
   manage_reg($part, $row['id'], $row['id'] == $no || request('col') == "reg", $prj['valid_par_stat']);
   manage_req($part, $row['id'], $row['id'] == $no || request('col') == "req", $prj['orchestration']);
   manage_final($part, $row['id'], $row['id'] == $no || request('col') == "final", $prj['orchestration']);

   echo "</tr>";
}
?> 

</table>
</form>

