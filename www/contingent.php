<?php
require 'framework.php';

$sel_year = $season->year() - 3;
$end_year = $season->year() + 1;

echo "
    <h1>Medlemskontingent</h1>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>
         <a href=\"$php_self?_sort=firstname,lastname\" title=\"Sorter p&aring; fornavn...\">Fornavn</a>/
         <a href=\"$php_self?_sort=lastname,firstname\" title=\"Sorter p&aring; etternavn...\">Etternavn</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=list_order,lastname,firstname\" title=\"Sorter p&aring; instrumentgruppe...\">Instrument</a></th>\n";

for ($i = $sel_year; $i <= $end_year; $i++)
   echo "<th bgcolor=#A6CAF0>$i</td>\n";
echo "</tr>";

if (is_null($sort))
   $sort = "list_order,lastname,firstname";

$query = "SELECT person.id as id_person, " .
        "firstname, middlename, lastname, instrument " .
        "FROM person, instruments " .
        "where instruments.id = person.id_instruments " .
        "and person.status = $db->per_stat_member " .
        "order by $sort";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   echo "<tr><td bgcolor=#A6CAF0>".$row['firstname']." ".$row['middlename']." ".$row['lastname']."</a>\n";
   echo "</td><td bgcolor=#A6CAF0>".$row['instrument']."</td>\n";

   for ($i = $sel_year; $i <= $end_year; $i++)
   {
      echo "<td align=center>";

      $q = "select id, status, amount from contingent where id_person = ".$row['id_person']." and year = $i order by ts";
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
         . "border=0 title=\"".$db->con_stat[$status].": ".$e['amount']."\"></a>";
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
