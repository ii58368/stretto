<?php

require 'framework.php';

if ($action == 'update' && $access->auth(AUTH::DIR_RW))
{
   $query = "update participant set comment_dir = '$_REQUEST[comment]' " .
             "where id_person = $_REQUEST[id_person] " .
             "and id_project = $no";
   $db->query($query);
   $no = NULL;
}   

$query = "SELECT firstname, middlename, lastname, status_dir, person.comment_dir as comment, instrument " .
         "from person, instruments " .
         "where person.id_instruments = instruments.id " .
         "and person.id = $_REQUEST[id_person]";

$stmt = $db->query($query);

echo "<body BGCOLOR=FFFFF4 TEXT=000000 LINK=00009F VLINK=008B00 ALINK=890000>";

$row = $stmt->fetch(PDO::FETCH_ASSOC);

  echo "<h2><b>$row[firstname] $row[middlename] $row[lastname]</b> ($row[instrument]) ";
  if ($row[status_dir] == $db->per_dir_nocarry)
    echo "<img src=\"images/chair-minus-icon.png\" border=0 title=\"Kan ikke l&oslash;fte bord\">";
  echo "</h2>$row[comment]<p>";

  echo "<table border=0>";
  echo "<th bgcolor=#A6CAF0>Status</th>";
  echo "<th bgcolor=#A6CAF0>Semester</th>";
  echo "<th bgcolor=#A6CAF0>Prosjekt</th>";
  echo "<th bgcolor=#A6CAF0>Kommentar</th>";

  $query = "select person.id as id_person, id_project, participant.stat_dir as status, participant.comment_dir as comment, " .
           "semester, year, project.name as name " .
           "from person, participant, project " .
           "where person.id = $_REQUEST[id_person] " .
           "and person.id = participant.id_person " .
           "and participant.id_project = project.id " .
           "and participant.stat_dir != $db->shi_stat_free " .
           "order by year desc, semester, project.id desc";

  $stmt = $db->query($query);

  foreach ($stmt as $row)
  {
    echo "<tr>\n";
    if ($access->auth(AUTH::DIR_RW))
       $help_txt = ". Klikk for å legge inn kommentar..."; 
    echo "<td align=center><a href=\"$_SERVER[PHP_SELF]?_action=edit&id_person={$row[id_person]}&_no={$row[id_project]}\"><img src=\"images/shift_status_{$row[status]}.gif\" border=0 title=\"{$db->shi_stat[$row[status]]}$help_txt\"></a></td>\n";
  
    echo "<td>$row[semester]-$row[year]</td>\n";
    echo "<td><a href=direction.php?id_project=$row[id_project]>$row[name]</a></td>\n";
    if ($row[id_project] == $no && $access->auth(AUTH::DIR_RW))
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

echo "</body></html>";


