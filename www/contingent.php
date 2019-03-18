<?php
require 'framework.php';

if (is_null($sort))
   $sort = 'list_order,-def_pos+desc,lastname,firstname';

function send_mail($r)
{
   reset($r);
   echo "<a href=\"mailto:?bcc=";
   foreach ($r as $e)
      if (strlen($e['email']) > 0)
         echo $e['email'] . ",";
   echo "&subject=OSO: \"><img border=0 src=images/send_mail.gif hspace=5 vspace=5 title=\"Send mail til alle på listen...\"></a>\n";
}

function send_press($result)
{
   global $season;
   global $sort;
   
   $f_filter = get_filter_as_url();
   
   reset($result);
   $f_person = "";
   foreach ($result as $row)
       $f_person .= "&p[]=" . $row['id_person'];
   echo "<a href=\"$php_self?_sort=$sort&_action=push$f_filter$f_person\" onClick=\"return confirm('Sikkert at du vil legge inn purret på alle på listen for ".$season->year()."?');\"><img border=0 src=images/bug01.gif hspace=5 vspace=5 title=\"Legg inn purret til alle på listen...\"></a>\n";
}

function send_excel($result)
{
   global $season;
   
   reset($result);
   $f_person = "year=" . $season->year();
   foreach ($result as $row)
       $f_person .= "&p[]=" . $row['id_person'];
   echo "<a href=\"contingentExcel.php?$f_person\" ><img border=0 src=images/excel.png height=20 hspace=5 vspace=5 title=\"Generer listen på Excel format...\"></a>\n";
}

function select_filter()
{
   global $db;
   global $sort;
   global $season;

   $gen_htxt = "Ctrl-klikk for å velge/velge bort flere valg samtidig.";
   // Select member status
   echo "<select name=\"f_status[]\" multiple size=3 onChange=\"submit();\" title=\"Filter for medlemsstatus...\nMerk: default valg er medlem og engasjert\n$gen_htxt\">\n";

   for ($i = 0; $i < count($db->per_stat); $i++)
   {
      echo "<option value=$i";
      if (!is_null(request('f_status')))
         foreach (request('f_status') as $f_status)
            if ($f_status == $i)
               echo " selected";
      echo ">" . $db->per_stat[$i] . "</option>\n";
   }

   echo "</select>\n";

   // Select contingent status
   echo "<select name=\"f_contingent[]\" multiple size=3 onChange=\"submit();\" title=\"Filter for betalingsstatus...\n$gen_htxt\">\n";

   for ($i = 0; $i < count($db->con_stat); $i++)
   {
      echo "<option value=$i";
      if (!is_null(request('f_contingent')))
         foreach (request('f_contingent') as $f_contingent)
            if ($f_contingent == $i)
               echo " selected";
      echo ">" . $db->con_stat[$i] . "</option>\n";
   }

   echo "</select>\n";
   
   echo "<input type=hidden name=_sort value=\"$sort\">\n";
}

function get_filter_as_url()
{
   $filter = '';
   
   if (!is_null(request('f_status')))
      foreach (request('f_status') as $f_status)
         $filter .= "&f_status[]=$f_status";
   if (!is_null(request('f_contingent')))
      foreach (request('f_contingent') as $f_contingent)
         $filter .= "&f_contingent[]=$f_contingent";
   
   return $filter;
}

function person_query()
{
   global $db;
   global $sort;
   global $season;
   
   $query = "SELECT person.id as id_person, person.status as status, "
           . "firstname, middlename, lastname, instrument, email "
           . "FROM person, instruments ";
   
   $contingent = request('f_contingent');
   $unpayed = in_array($db->con_stat_unpayed, $contingent);
   if ($unpayed)
      unset($contingent[array_search($db->con_stat_unpayed, $contingent)]);
  
   if (!empty($contingent))
      $query .= ", contingent ";
   $query .= "where person.id_instruments = instruments.id ";
   if (!empty($contingent))
   {
      $query .= "and contingent.id_person = person.id "
              . "and contingent.year = " . $season->year() . " "
              . "and (";
      foreach ($contingent as $f_contingent)
         $query .= "contingent.status = $f_contingent or ";
      $query .= "false) ";
   }
   $query .= " ";
   if ($unpayed)
      $query .= "and not exists (select 1 from contingent "
           . "where contingent.id_person = person.id "
           . "and contingent.year = " . $season->year() . " "
           . "and not contingent.status = $db->con_stat_unpayed) ";
   
   if (!is_null(request('f_status')))
   {
      $query .= "and (";
      foreach (request('f_status') as $f_status)
         $query .= "person.status = $f_status or ";
      $query .= "false) ";
   }
   $query .= "group by person.id order by " . str_replace("+", " ", $sort);

   return $query;
}

$query = person_query();
$stmt = $db->query($query);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($action == 'push')
{
   $ts = strtotime("now");

   foreach (request('f_person') as $f_person)
   {
      $query = "insert into contingent (ts, amount, year, comment, id_person, status)
                values ($ts, 0, " . $season->year()
                . ", 'Fellespurring', $f_person, $db->con_stat_press)";
      $db->query($query);
      echo $query . "<br>";
   }
}

$sel_year = $season->year() - 3;
$end_year = $season->year() + 1;


echo "<h1>Medlemskontingent</h1>";

echo "<form action=\"$php_self\" method=post>\n";
select_filter();
echo "<font color=green>" . count($result) . " treff</font>\n";
echo "</form>\n";

$f_filter = get_filter_as_url();

send_mail($result);
send_press($result);
send_excel($result);

echo "
    <table border=1>
    <tr>
      <th>
         <a href=\"$php_self?_sort=firstname,lastname$f_filter\" title=\"Sorter p&aring; fornavn...\">Fornavn</a>/
         <a href=\"$php_self?_sort=lastname,firstname$f_filter\" title=\"Sorter p&aring; etternavn...\">Etternavn</a></th>
      <th><a href=\"$php_self?_sort=list_order,-def_pos+desc,lastname,firstname$f_filter\" title=\"Sorter etter instrumentgruppe...\">Instrument</a></th>\n
      <th><a href=\"$php_self?_sort=person.status,list_order,-def_pos+desc,lastname,firstname$f_filter\" title=\"Sorter etter medlemsstatus...\">Status</a></th>\n";

for ($i = $sel_year; $i <= $end_year; $i++)
   echo "<th>$i</td>\n";
echo "</tr>";

reset($result);

foreach ($result as $row)
{
   echo "<tr><td><a href=\"mailto:".$row['email']."?subject=OSO:\">".$row['firstname']." ".$row['middlename']." ".$row['lastname']."</a>\n";
   echo "</td><td>".$row['instrument']."</td>\n";
   echo "</td><td>".$db->per_stat[$row['status']]."</td>\n";

   for ($i = $sel_year; $i <= $end_year; $i++)
   {
      echo "<td align=center>";

      $q = "select id, status, amount, ts from contingent where id_person = ".$row['id_person']." and year = $i order by ts";
      $s = $db->query($q);

      $icon = "images/ball_red.gif";
      $status = null;
      foreach ($s as $e)
      {
         $status = $e['status'];

         if ($status == $db->con_stat_unknown)
            $icon = "images/ballc_g1.gif";
         if ($status == $db->con_stat_unpayed)
            $icon = "images/ball_red.gif";
         if ($status == $db->con_stat_payed)
            $icon = "images/ballc_gr.gif";
         if ($status == $db->con_stat_press)
            $icon = "images/g_red_anim.gif";

         echo "<a href=\"contingentEdit.php?id_person=".$row['id_person']."&year=$i&_no=".$e['id']."\">"
         . "<img src=\"$icon\" "
         . "border=0 title=\"".date('d.m.Y', $e['ts']).": ".$db->con_stat[$status].": ".$e['amount'].",-\"></a>";
      }
      if (is_null($status))
         echo "<a href=\"contingentEdit.php?id_person=".$row['id_person']."&year=$i\">"
         . "<img src=\"images/cross_re.gif\" "
         . "border=0 title=\"Ny betaling\"></a>";

      echo "</td>";
   }
   echo "</tr>\n";
}
?>  
</tr>
</table>
