<html>
  <head>
    <LINK href="style.css" rel="stylesheet" type="text/css">
    <link href="http://www.oslo-symfoniorkester.org/templates/flexitemplate/favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <title>Turnus</title>
  </head>

<?php

include 'config.php';
include 'opendb.php';

include 'request.php';


echo "
  <body BGCOLOR=FFFFF4 TEXT=000000 LINK=00009F VLINK=008B00 ALINK=890000>
    <h1>Oslo Symfoniorkester - turnusplan for regikomit&eacute</h1>

Oslo Symfoniorkester vil for hvert prosjekt oppnevne en regikomit&eacute; som skal v&aelig;re ansvarlig for &aring; rigge opp til pr&oslash;ver og konserter. F&oslash;lgende turnusliste gir oversikt over n&aring;r det er din tur. Du vil ogs&aring; bli kontaktet p&aring; mail n&aring;r det n&aelig;rmer seg. Dersom det passer d&aring;rlig for deg &aring; v&aelig;re med i regikomit&eacute;en for det aktuelle prosjeketet, m&aring; du selv bytte med noen du ser skal v&aelig;re med i regikomit&eacute;en p&aring; et annet prosjekt. Gi i tilfelle beskjed til <a href=\"mailto:regi@oslo-symfoniorkester.org?subject=OSO regi\">regiss&oslash;ren</a> om hvem du bytter med.
<p>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Musiker</th>
      <th bgcolor=#A6CAF0>Instrument</th>";

$cur_year = date("Y");

$query  = "SELECT id, name, semester, year " .
          "FROM project " .
          "where year >= $cur_year " .
          "order by year, semester DESC, project.id";
$result = mysql_query($query);

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
  echo "<th bgcolor=#A6CAF0><a href=plan.php?id_project={$row[id]} title=\"Regiplan for $row[name]...\">{$row[name]}<br>{$row[semester]}{$row[year]}</a></td>";
echo "</tr><tr>";


$query  = "SELECT person.id as person_id, project.id as project_id,  " .
          "project.name as project_name, " .
          "firstname, lastname, instrument " .
          "FROM person, instruments, project " .
          "where instruments.id = id_instrument " .
          "and person.status = 'aktiv' " .
          "and project.year >= $cur_year " .
          "order by list_order, lastname, firstname, year, semester DESC, project.id";
$result = mysql_query($query);

$prev_id = 0;

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
  if ($row[person_id] != $prev_id)
    echo "</tr><tr><td bgcolor=#A6CAF0>" . $row[firstname] . " " . $row[lastname] . "</td><td bgcolor=#A6CAF0>" . 
        $row[instrument] . "</td>";
  $prev_id = $row[person_id];
  $query  = "SELECT status, comment from shift " .
          "where id_project = {$row[project_id]} " .
          "and id_person = {$row[person_id]}"; 
  $result2 = mysql_query($query);
  $status = 0;
  $comment = "";
  $action = "insert";
  if ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC))
  {
    $status = $row2[status];
    $comment = $row2[comment];
  }
  echo "<td align=center>";
  if ($status == 1 || $status == 2)
   echo "<img src=\"shift_status_{$status}.gif\" border=0 title=\"{$row[project_name]} {$comment}\">";
  echo "</td>";
} 

include 'closedb.php';

?> 
    </tr>
    </table>
  </body>
</html>

