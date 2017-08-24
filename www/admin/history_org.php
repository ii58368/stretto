<html>
  <head>
    <title>Historikk</title>
    <LINK href="style.css" rel="stylesheet" type="text/css">
  </head>

<?php

include 'config.php';
include 'opendb.php';

include 'request.php';

/*
if ($action == 'update')
{
   $query = "update shift set comment = $_REQUEST[comment] " .
             "where id_person = $_REQUEST[id_person] " .
             "and id_project = $_REQUEST[id_project]";
   mysql_query($query);
}   

$query = "SELECT firstname, lastname, table_ok, person.comment as comment, instrument " .
         "from person, instruments " .
         "where id_instrument = instruments.id " .
         "and person.id = $_REQUEST[id_person]";

$result = mysql_query($query);
*/
echo "<body BGCOLOR=FFFFF4 TEXT=000000 LINK=00009F VLINK=008B00 ALINK=890000>";

if ($row = mysql_fetch_arry($result, MYSQL_ASSOC)
{
  echo "$row[firstname] $row[lastname]<br>";
  echo $row[instrument];
  if ($row[table_ok] == '')
    echo "<img src=\"/images/chair-minus-icon.png\" border=0 title=\"Kan ikke l&oslash;fte bord\"><br>";
  echo "$row[comment]<br>";

  echo "<table border=0>";
/*
  $query = "select id_person, id_project, shift.status as status, shift.comment as comment, " .
           "semester, year, project.name as name " .
           "from person, shift, project " .
           "where person.id = $_REQUEST[id_person] " .
           "and person.id = shift.id_person " .
           "and shift.id_project = project.id " .
           "order by year, semester, project.id";

  $result = mysql_query($query);

  $status_tab = array(
    0 => "Klikk for &aring; bli valgt inn i regikomiteen",
    1 => "Forespurt",
    2 => "Bekreftet",
    3 => "Ikke godkjent oppm&oslash;te",
    4 => "Permisjon"
  );

  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)
  {
    echo "<tr>";
    echo "<td align=center><a href=\"$_SERVER[PHP_SELF]?_action={$action}&id_person={$row[person_id]}&id_project={$row[project_id]}\"><img src=\"shift_status_{$status}.gif\" border=0 title=\"{$status_tab[$status]}\"></a></td>";
  
    echo "<td>$row[semester]-$row[year]</td>";
    echo "<td>$row[name]</td>";
    echo "<td>$row[comment]</td>";
    echo "</tr>";
  } */
  echo "</table>";
}

echo "</body>";

include 'closedb.php';

?>
</html>
