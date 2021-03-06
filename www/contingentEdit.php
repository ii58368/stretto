<?php

include 'framework.php';

function select_person($selected)
{
   global $db;
   global $php_self;
   global $no;

   echo "<form method=post action='$php_self'>
      <input type=hidden name=id_person value=$selected>
      <input type=hidden name=year value=" . request('year') . ">
      <select name=id_person onChange=\"submit();\">\n";

   $q = "SELECT person.id as id, firstname, lastname, instrument "
           . "FROM person, instruments "
           . "where status = $db->per_stat_member "
           . "and person.id_instruments = instruments.id "
           . "order by list_order, lastname, firstname";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         echo " selected";
      echo ">" . $e['firstname'] . " " . $e['lastname'] . " (" . $e['instrument'] . ")";
   }

   echo "</select>\n</form>\n";
}

function select_status($selected)
{
   global $db;

   if (is_null($selected))
      $selected = $db->con_stat_payed;

   echo "<select name=status title=\"Angi status for betaling...\">";

   for ($i = 0; $i < count($db->con_stat); $i++)
   {
      echo "<option value=$i";
      if ($selected == $i)
         echo " selected";
      echo ">" . $db->con_stat[$i] . "</option>\n";
   }

   echo "</select>";
}

$style = '';

if ($action == 'update')
{
   if (($dtime = DateTime::createFromFormat("d.m.Y", request('date'))) == false)
      echo "<font color=red>Illegal time format: " . request('date') . "</font>";
   else
   {
      $ts = $dtime->getTimestamp();
      try
      {
         if ($no == null)
         {
            $query = "insert into contingent (ts, amount, year, comment, id_person, status, archive)
              values ('$ts', " . request('amount') . ", "
                    . request('year') . ", " . $db->qpost('comment') . ", " . request('id_person') . ","
                    . request('status') . ", " . $db->qpost('archive').")";
            $db->query($query);
            $no = $db->lastInsertId();

            $style = "style=\"background-color:lightgreen\"";
         } else
         {
            if (!is_null($delete))
            {
               $query = "DELETE FROM contingent WHERE id = $no";
               $result = $db->query($query);
               $no = NULL;
            } else
            {
               $query = "update contingent set ts = $ts," .
                       "amount = " . request('amount') . "," .
                       "year = " . request('year') . "," .
                       "comment = " . $db->qpost('comment') . "," .
                       "id_person = " . request('id_person') . "," .
                       "status = " . request('status') . "," .
                       "archive = " . $db->qpost('archive') . " " .
                       "where id = $no";
               $db->query($query);

               $style = "style=\"background-color:lightgreen\"";
            }
         }
      } catch (PDOException $ex)
      {
         $style = "style=\"background-color:lightred\"";
      }
   }
}

$query = "select firstname, middlename, lastname, instrument "
        . "FROM person, instruments "
        . "where person.id_instruments = instruments.id "
        . "and person.id = " . request('id_person');

$stmt = $db->query($query);
$per = $stmt->fetch(PDO::FETCH_ASSOC);

function con($key = null)
{
   global $no;
   global $db;
   static $c = null;
   
   if (is_null($no))
      return null;

   if (is_null($c))
   {
      $stmt = $db->query("select * from contingent where id = $no");
      $c = $stmt->fetch(PDO::FETCH_ASSOC);
   }
   
   if (is_null($key))
      return '';
   
   if (isset($c[$key]))
      return $c[$key];
   
   return '';
}

$ts = is_null(con('ts')) ? strtotime("now") : intval(con('ts'));

echo "
    <h1>Medlemskontingent - " . request('year') . "</h1>
    <h2>" . $per['firstname'] . " " . $per['middlename'] . " " . $per['lastname'] . " (" . $per['instrument'] . ")</h2>
    <table id=\"no_border\">
      <form action='$php_self' method=post>
      <tr>
      <th colspan=2 align=left>
        <input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value=" . con('id') . ">
        <input type=hidden name=id_person value=" . request('id_person') . ">
        <input type=hidden name=year value=" . request('year') . ">
        <input type=hidden name=_action value=update>\n";
if (!is_null($no) || !is_null($delete))
   echo "<input type=button value=Ny onClick=\"location.href='$php_self?id_person=" . request('id_person') . "&year=" . request('year') . "';\" title=\"Registrer ny betaling...\">\n";
if (is_null($delete))
   echo "<input type=submit value=Lagre title=\"Lagre endring\" $style>\n";
if (!is_null(con()))
   echo "<input type=submit name=_delete value=slett title=\"Slett betailng...\" onClick=\"return confirm('Sikkert at du vil slette denne betalingen?');\">\n";
echo "<input type=button value=Tilbake onClick=\"location.href='contingent.php?year=" . request('year') . "';\" title=\"Tilbake til liste...\">\n";
echo "</th>
    </tr>\n";
if (is_null($delete))
{
   echo "<tr>
      <td>Dato:</td>
      <td><input type=text name=date size=10 value=\"" . date('d.m.Y', $ts) . "\" title=\"Dato for registrering. Format: eks: 23. dec\" autofocus onFocus=\"this.select()\"></td>
    </tr>
    <tr>
      <td>Beløp:</td>
      <td><input type=text name=amount size=5 value=\"" . con('amount') . "\" title=\"Beløp i hele kroner\"></td>
    </tr>
    <tr>
       <td>Status:</td>
       <td>";
   select_status(con('status'));
   echo "</td>
    </tr>
    <tr>
      <td>Billag:</td>
      <td><input type=text name=archive length=10 value=\"" . con('archive') . "\" title=\"Bilagskode\"></td>
   </tr>
   <tr>
      <td>Kommentar:</td>
      <td><input type=text name=comment size=50 value=\"" . con('comment') . "\" title=\"Fritekst\"></td>
    </tr>\n";
}
echo "</form>
</table>";
