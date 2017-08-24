<html>
  <head>
    <title>Historikk</title>
    <LINK href="style.css" rel="stylesheet" type="text/css">
  </head>

<?php

include 'config.php';
include 'opendb.php';

include 'request.php';


if ($action == 'update')
{
   $query = "update shift set comment = '$_REQUEST[comment]' " .
             "where id_person = $_REQUEST[id_person] " .
             "and id_project = $no";
   mysql_query($query);
   $no = NULL;
}   

$query = "SELECT firstname, lastname, table_ok, person.comment as comment, instrument " .
         "from person, instruments " .
         "where id_instrument = instruments.id " .
         "and person.id = $_REQUEST[id_person]";

$result = mysql_query($query);

echo "<body BGCOLOR=FFFFF4 TEXT=000000 LINK=00009F VLINK=008B00 ALINK=890000>";

if ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
  echo "<h2><b>$row[firstname] $row[lastname]</b> ($row[instrument]) ";
  if ($row[table_ok] == '')
    echo "<img src=\"/images/chair-minus-icon.png\" border=0 title=\"Kan ikke l&oslash;fte bord\">";
  echo "</h2>$row[comment]<p>";

  echo "<table border=0>";
  echo "<th bgcolor=#A6CAF0>Status</th>";
  echo "<th bgcolor=#A6CAF0>Semester</th>";
  echo "<th bgcolor=#A6CAF0>Prosjekt</th>";
  echo "<th bgcolor=#A6CAF0>Kommentar</th>";

  $query = "select id_person, id_project, shift.status as status, shift.comment as comment, " .
           "semester, year, project.name as name " .
           "from person, shift, project " .
           "where person.id = $_REQUEST[id_person] " .
           "and person.id = shift.id_person " .
           "and shift.id_project = project.id " .
           "and shift.status > 0 " .
           "order by year desc, semester, project.id desc";

  $result = mysql_query($query);

  $status_tab = array(
    0 => "Klikk for &aring; bli valgt inn i regikomiteen",
    1 => "Tentativt",
    2 => "Bekreftet",
    3 => "Ikke godkjent oppm&oslash;te",
    4 => "Permisjon",
    5 => "Regiansvarlig"
  );

  while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
  {
    echo "<tr>\n";
    echo "<td align=center><a href=\"$_SERVER[PHP_SELF]?_action=edit&id_person={$row[id_person]}&_no={$row[id_project]}\"><img src=\"shift_status_{$row[status]}.gif\" border=0 title=\"{$status_tab[$row[status]]}\"></a></td>\n";
  
    echo "<td>$row[semester]-$row[year]</td>\n";
    echo "<td><a href=plan.php?_sort=time,date&id_project={$row[id_project]} target=content>$row[name]</a></td>\n";
    if ($row[id_project] == $no)
    {
      echo "<form action='{$php_self}' method=post><td>\n";
      echo "<textarea cols=30 rows=3 wrap=auto name=comment>$row[comment]</textarea>\n";
      echo "<input type=submit value=apply>\n";
      echo "<input type=hidden name=_action value=update>\n";
      echo "<input type=hidden name=id_person value=$_REQUEST[id_person]>\n";
      echo "<input type=hidden name=_no value=$no>\n";
      echo "</form>";
    }
    else
    {
      echo "<td>";
      echo str_replace("\n", "<br>\n", $row[comment]);
      echo "</td>\n";
    }
    echo "</tr>";
  } 
  echo "</table>";
}

echo "</body>";

include 'closedb.php';

?>
</html>
