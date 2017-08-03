
<?php
require 'framework.php';
include_once 'participant_status.php';

if ($sort == NULL)
   $sort = 'year,semester DESC,id';


$sel_year = ($_REQUEST[from] == NULL) ? date("Y") : intval($_REQUEST[from]);
$prev_year = $sel_year - 1;

$query = "select person.id as id, firstname, lastname, instrument"
        . " from person, instruments"
        . " where person.id=$_REQUEST[id]"
        . " and person.id_instruments = instruments.id";
$stmt = $db->query($query);
$pers = $stmt->fetch(PDO::FETCH_ASSOC);

echo "
    <h1>Prosjekter for $pers[firstname] $pers[lastname] ($pers[instrument])</h1>
    <form action='$php_self' method=post>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Prosjekt</th>
      <th bgcolor=#A6CAF0 nowrap>Sem
           <a href=\"$php_self?from=$prev_year&id=$_REQUEST[id]\"><img src=images/arrow_up.png border=0 title=\"Forrige &aring;r...\"></a></th>
      <th bgcolor=#A6CAF0>Status</th>
      <th bgcolor=#A6CAF0>Deadline</th>
      <th bgcolor=#A6CAF0>Tutti</th>
    </tr>";



$query = "SELECT project.id as id, name, semester, year, status, " .
        "deadline, orchestration " .
        "FROM project " .
        "where project.year >= $sel_year " .
        "and status = $db->prj_stat_public " .
        "or status = $db->prj_stat_tentative " .
        "order by $sort";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   list($status, $blink) = participant_status($_REQUEST[id], $row[id]);
   echo "<tr>
      <td>";
   if ($row[status] == $prj_stat_public)
      echo "<a href=\"participant_11.php?id_project=$row[id]&id_person=$pers[id]\" title=\"Påmelding eller søk om permisjon\">";
   echo $row[name];
   if ($row[status] == $prj_stat_public)
      echo "</a>";
   echo "</td>\n" .
   "<td>{$row[semester]} " .
   "    {$row[year]}</td>" .
   "<td align=center>";
   if ($row[status] == $db->prj_stat_public)
      echo "<img src=\"images/part_stat_$status$blink.gif\" border=0>";
   echo "</td>\n";
   echo "<td>" . date('D j.M y', $row[deadline]) . "</td>" .
   "<td>";
   if ($row[orchestration] == $prj_orch_tutti)
      echo "<center><img src=\"images/tick2.gif\" border=0></center>";
   echo "</td>
         </tr>";
}
?> 

</table>
</form>

<?php
require 'framework_end.php';
?>

