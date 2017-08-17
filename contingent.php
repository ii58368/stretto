<?php
require 'framework.php';

$sel_year = is_null($_REQUEST[from]) ? date("Y") - 3 : intval($_REQUEST[from]);
$prev_year = $sel_year - 1;
$end_year = date("Y") + 1;

echo "
    <h1>Medlemskontingent</h1>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0><a href=\"$php_self?from=$prev_year&id_person={$row[person_id]}&id_project={$row[project_id]}&status={$status}&_sort={$sort}\"><img src=images/left.gif border=0 title=\"Forrige &aring;r...\"></a>
         <a href=\"$php_self?from=$sel_year&_sort=firstname,lastname\" title=\"Sorter p&aring; fornavn...\">Fornavn</a>/
         <a href=\"$php_self?from=$sel_year&_sort=lastname,firstname\" title=\"Sorter p&aring; etternavn...\">Etternavn</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?from=$sel_year&_sort=list_order,lastname,firstname\" title=\"Sorter p&aring; instrumentgruppe...\">Instrument</a></th>\n";

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
   echo "<tr><td bgcolor=#A6CAF0>$row[firstname] $row[middelname] $row[lastname]</a>\n";
   echo "</td><td bgcolor=#A6CAF0> $row[instrument]</td>\n";

   for ($i = $sel_year; $i <= $end_year; $i++)
   {
      echo "<td align=center>";

      $q = "select id, status, amount from contingent where id_person = $row[id_person] and year = $i order by ts";
      $s = $db->query($q);

      $icon = "images/ball_red.gif";
      $status = null;
      foreach ($s as $e)
      {
         $status = $e[status];

         if ($status == $con_stat_unknown)
            $icon = "images/ballc_g1.gif";
         if ($status == $con_stat_unpayed)
            $icon = "images/ball_red.gif";
         if ($status == $con_stat_payed)
            $icon = "images/ballc_gr.gif";
         if ($status == $con_stat_press)
            $icon = "images/g_red_anim.gif";

         echo "<a href=\"contingentEdit.php?id_person=$row[id_person]&year=$i&_no=$e[id]\">"
         . "<img src=\"$icon\" "
         . "border=0 title=\"$con_stat[$status]: $e[amount]\"></a>";
      }
      if (is_null($status))
         echo "<a href=\"contingentEdit.php?id_person=$row[id_person]&year=$i\">"
         . "<img src=\"images/cross_re.gif\" "
         . "border=0 title=\"Ny betaling\"></a>";

      echo "</td>";
   }
   echo "</tr>\n";
}
?>  
</tr>
</table>

<?php
include 'framework_end.php';
?>

