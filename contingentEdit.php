<?php

include 'framework.php';

function select_person($selected)
{
   global $db;
   global $per_stat_member;
   global $php_self;
   global $no;

   echo "<form method=post action='$php_self'>
      <input type=hidden name=id_person value=$selected>
      <input type=hidden name=year value=$_REQUEST[year]>
      <select name=id_person onChange=\"submit();\">\n";

   $q = "SELECT person.id as id, firstname, lastname, instrument "
           . "FROM person, instruments "
           . "where status = $per_stat_member "
           . "and person.id_instruments = instruments.id "
           . "order by list_order, lastname, firstname";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e[id] . "\"";
      if ($e[id] == $selected)
         echo " selected";
      echo ">$e[firstname] $e[lastname] ($e[instrument])";
   }

   echo "</select>\n</form>\n";
}

function select_status($selected)
{
   global $con_stat;
   global $con_stat_payed;

   if (is_null($selected))
      $selected = $con_stat_payed;

   echo "<select name=status>";

   for ($i = 0; $i < count($con_stat); $i++)
   {
      echo "<option value=$i";
      if ($selected == $i)
         echo " selected";
      echo ">$con_stat[$i]</option>\n";
   }

   echo "</select>";
}

if ($action == 'update')
{
   if (($ts = strtotime($_POST[date])) == false)
      echo "<font color=red>Illegal time format: " . $_POST[date] . "</font>";
   else
   {
      try
      {
         if (is_null($no))
         {
            $query = "insert into contingent (ts, amount, year, comment, id_person, status, archive)
              values ('$ts', '$_POST[amount]', 
                      '$_POST[year]', '$_POST[comment]', '$_POST[id_person]',
                      '$_POST[status]', '$_POST[archive]')";
            $db->query($query);
            $no = $db->lastInsertId();
         } else
         {
            if (!is_null($delete))
            {
               $query = "DELETE FROM contingent WHERE id = $no";
               $result = $db->query($query);
               $no = NULL;
            } else
            {
               $query = "update contingent set ts = '$ts'," .
                       "amount = '$_POST[amount]'," .
                       "year = '$_POST[year]'," .
                       "comment = '$_POST[comment]'," .
                       "id_person = '$_POST[id_person]'," .
                       "status = '$_POST[status]'," .
                       "archive = '$_POST[archive]' " .
                       "where id = $no";
               $db->query($query);
            }
         }
      } catch (PDOException $ex)
      {
         echo "<font color=red>Failed to update</font>";
      }
   }
}

$query = "select firstname, middlename, lastname, instrument "
        . "FROM person, instruments "
        . "where person.id_instruments = instruments.id "
        . "and person.id = $_REQUEST[id_person]";

$stmt = $db->query($query);
$per = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_null($no))
{
   $stmt = $db->query("select * from contingent where id = $no");
   $con = $stmt->fetch(PDO::FETCH_ASSOC);
}

$ts = is_null($con) ? strtotime("now") : $con[ts];

echo "
    <h1><a href=contingent.php>Medlemskontingent $_REQUEST[year]</a></h1>
    <h2>$per[firstname] $per[middlename] $per[lastname] ($per[instrument])</h2>
    <table border=0>
      <form action='$php_self' method=post>
      <tr>
      <th colspan=2 align=left>
        <input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value=$con[id]>
        <input type=hidden name=id_person value=$_REQUEST[id_person]>
        <input type=hidden name=year value=$_REQUEST[year]>
        <input type=hidden name=_action value=update>\n";
if (!is_null($no))
   echo "<input type=button value=Ny onClick=\"location.href='$php_self?id_person=$_REQUEST[id_person]&year=$_REQUEST[year]';\">";
echo "<input type=submit value=\"Lagre\">\n";
if (!is_null($con))
   echo "<input type=submit name=_delete value=slett>\n";
echo "</th>
    </tr>
    <tr>
      <td>Dato:</td>
      <td><input type=text name=date size=10 value=\"" . date('j.M y', $ts) . "\"></td>
    </tr>
    <tr>
      <td>Beløp:</td>
      <td><input type=text name=amount size=5 value=\"$con[amount]\"></td>
    </tr>
    <tr>
       <td>Status:</td>
       <td>";
select_status($con[status]);
echo "</td>
    </tr>
    <tr>
      <td>Billag:</td>
      <td><input type=text name=archive length=10 value=\"$con[archive]\"></td>
   </tr>
   <tr>
      <td>Kommentar:</td>
      <td><input type=text name=comment size=50 value=\"$con[comment]\"></td>
    </tr>
    </form>
</table>";

include 'framework_end.php';
?>