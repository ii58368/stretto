<?php
require 'framework.php';

if ($sort == NULL)
   $sort = 'lastname,firstname,title';

$id_project = is_null(request('id_project')) ? 0 : request('id_project');
$search = request('search');


function get_projectname($id_project)
{
   global $db;

   $s = $db->query("select name, semester, year from project where id = $id_project");
   $e = $s->fetch(PDO::FETCH_ASSOC);
   
   return $e['name'] . " (" . $e['semester'] . $e['year'] . ")";
}

if ($action == 'rep_update' && $access->auth(AUTH::REP))
{
   $status = is_null($delete) ? $db->mus_stat_yes : $db->mus_stat_no;
   $query = "replace into music (id_repository, id_project, status, comment) values ($no, $id_project, $status, " . $db->qpost('comment') . ")";
   $db->query($query);
   $no = null;
}

if ($id_project > 0)
{
   echo "
    <h1>Repertoar: ".get_projectname($id_project)."</h1>
    <form action='$php_self' method=post>
    <table>
    <tr>
    <th>Edit</th>
    <th>Komponist</th>
    <th>Tittel</th>
    <th>Info som gjelder dette prosjektet</th>
    </tr>
    <tr>";

   $q = "select repository.id as id_repository,"
           . "firstname, lastname, title,"
           . "music.comment as comment "
           . "from repository, music "
           . "where repository.id = music.id_repository "
           . "and music.status = $db->mus_stat_yes "
           . "and music.id_project = $id_project ";
   $s = $db->query($q);
   foreach ($s as $e)
   {
      echo "<tr>";
      if ($action != 'rep_view' || $e['id_repository'] != $no)
      {
         echo "<td><center>
           <a href=\"$php_self?_sort=$sort&_action=rep_view&_no=" . $e['id_repository'] . "&id_project=$id_project&search=$search\"><img src=\"images/cross_re.gif\" border=0></a>
             </center></td>"
          . "<td>".$e['firstname']." ".$e['lastname']."</td>"
          . "<td>".$e['title']."</td>"
          . "<td>".$e['comment']."</td>"
          . "</tr>";
      }
      else
      {
         echo "<input type=hidden name=_action value=rep_update>
            <input type=hidden name=_sort value='$sort'>
            <input type=hidden name=search value=\"$search\">
            <input type=hidden name=id_project value=$id_project>
            <input type=hidden name=_no value='$no'>
            <td><input type=submit value=ok title=\"Lagre endring\">
                <input type=submit value=del name=_delete title=\"Slette fra repertoarlisten\"></td>"
          . "<td>".$e['firstname']." ".$e['lastname']."</td>"
          . "<td>".$e['title']."</td>"
          . "<td><input type=text name=comment value=\"" . $e['comment'] . "\" size=30 title=\"Tilleggsinformasjon som kun for dette prosjektet, f.eks: Kantate 1,2 og 3\"></td>\n";      
      }
      echo "</tr>";
   }
   echo "</table></form>";
}

echo "
    <h1>Notearkiv</h1>";
echo "
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=id_project value=$id_project>
      <img src=\"images/search.png\" height=20>
      <input type=text name=search value=\"$search\" title=\"Søk på hele eller deler av komponistnavn eller tittel og trykk enter\">
    </form>";

if ($access->auth(AUTH::REP))
   echo "<a href=\"$php_self?_sort=$sort&_action=new&id_project=$id_project&search=$search\" title=\"Registrer nytt verk...\"><img src=\"images/new_inc.gif\" border=0 hspace=5 vspace=5></a>\n";

echo "<a href=\"repository_pdf.php\" title=\"PDF versjon av alle verk som har arkivref OSO\"><img src=images/pdf.jpeg height=22 border=0 hspace=5 vspace=5></a>\n";

echo "
    <form action='$php_self' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::REP))
   echo "
      <th><a href=\"$php_self?_sort=id+DESC&id_project=$id_project&search=$search\" title=\"Sorter i omvendt registreringsrekkefølge\">Edit</a></th>";
echo "
      <th><a href=\"$php_self?_sort=lastname,firstname,title&id_project=$id_project&search=$search\" title=\"Sorter på komponistnavn\">Komponist</a></th>
      <th><a href=\"$php_self?_sort=title,lastname,firstname&id_project=$id_project&search=$search\" title=\"Sorter på tittel\">Tittle</a></th>
      <th>Fra</th>
      <th><a href=\"$php_self?_sort=archive,tag&id_project=$id_project&search=$search\" title=\"Sorter på arkivreferanse\">Arkivref</a></th>
      <th>Kommentar</th>
      <th>\n";
echo "Prosjekt";
echo "</th>
      </tr>";

if ($action == 'new')
{
   $s = $db->query("select max(tag) as max_tag from repository where archive = 'OSO'");
   $e = $s->fetch(PDO::FETCH_ASSOC);
   $arch_no =  isset($e['max_tag']) ? $e['max_tag'] + 1 : 0;

   echo "
  <tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=hidden name=search value=\"$search\">
    <input type=hidden name=id_project value=\"$id_project\">
    <input type=submit value=ok></td>
    <td><input type=text size=30 name=firstname title=Fornavn>
        <input type=text size=30 name=lastname title=Etternavn></td>
    <td><input type=text size=30 name=title title=\"Navn på verk\"></td>
    <td><input type=text size=30 name=work title=\"Navn på hovedverk hvis dette er et utdrag\"></td>
    <td><input type=text size=8 name=archive value=OSO title=\"Referanse på hvor noter er leid eller lånt\">
        <input type=text size=6 name=tag value=$arch_no title=\"Eventuelt referansnummer. ($arch_no er første ledige nummer i OSO arkivet)\"></td>
    <td><textarea cols=50 rows=7 wrap=virtual name=comment title=\"Fritekst\"></textarea></td>
    <td></td>
  </tr>\n";
}

if ($action == 'update' && $access->auth(AUTH::REP))
{
   $tag = is_numeric(request('tag')) ? request('tag') : 0;
   $ts = strtotime("now");

   if ($no == NULL)
      $query = "insert into repository (firstname, lastname, title, work, tag, archive, comment, ts) "
              . "values (" . $db->qpost('firstname') . ", " . $db->qpost('lastname') . ", " . $db->qpost('title') . ", " . $db->qpost('work') . ", "
              . "$tag, " . $db->qpost('archive') . ", " . $db->qpost('comment') . ", $ts)";
   else
   {
      if ($delete != NULL)
      {
         $q = "select count(*) as count from music where id_repository = $no";
         $s = $db->query($q);
         $e = $s->fetch(PDO::FETCH_ASSOC);
         if ($e['count'] == 0)
            $query = "DELETE FROM repository WHERE id = $no";
         else
            echo "<font color=red>Used in project</font>";
      }
      else
         $query = "update repository set firstname = " . $db->qpost('firstname') . "," .
                 "lastname = " . $db->qpost('lastname') . "," .
                 "title = " . $db->qpost('title') . "," .
                 "work = " . $db->qpost('work') . "," .
                 "tag = $tag," .
                 "archive = " . $db->qpost('archive') . "," .
                 "comment = " . $db->qpost('comment') . " ," .
                 "ts = $ts " .
                 "where id = $no";
      $no = NULL;
   }
   $db->query($query);
}


$query = "select id_repository, comment, status from music where id_project=$id_project";
$stmt = $db->query($query);
$music = $stmt->fetchAll(PDO::FETCH_ASSOC);


function view_project($cno)
{
   global $db;
   global $id_project;

   $q = "select name, semester, year, music.status as status, music.comment as comment, "
           . "project.id as id_project "
           . "from project, music "
           . "where music.id_project = project.id "
           . "and music.id_repository = $cno";
   $s = $db->query($q);

   $is_included = false;
   
   foreach ($s as $e)
   {
      if ($e['status'] == $db->mus_stat_yes)
      {
         if ($e['id_project'] == $id_project)
            $is_included = true;
         echo "<a href=\"prjInfo.php?id=".$e['id_project']."\" title=\"Se prosjektinfo...\">" . $e['name'] . " (" . $e['semester'] . "-" . $e['year'] . ")</a>";
         if (strlen($e['comment']) > 0)
            echo ": " . $e['comment'];
         echo "<br>\n";
      }
   }
   return $is_included;
}

$query = "SELECT id, firstname, lastname, title, work, tag, archive, comment "
        . "FROM repository ";
$db_search = $db->quote("%$search%");
if (strlen($search) > 0)
   $query .= "where firstname like $db_search "
           . "or lastname like $db_search "
           . "or title like $db_search ";
$query .= "order by $sort "
        . "limit 50";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($action != 'view' || $row['id'] != $no)
   {
      echo "<tr>";
      if ($access->auth(AUTH::REP))
         echo "
         <td><center>
           <a href=\"$php_self?_sort=$sort&_action=view&_no=" . $row['id'] . "&id_project=$id_project&search=$search\"><img src=\"images/cross_re.gif\" border=0></a>
             </center></td>";
      echo
      "<td>" . $row['lastname'] . ", " . $row['firstname'] . "</td>" .
      "<td>" . $row['title'] . "</td>" .
      "<td>" . $row['work'] . "</td>" .
      "<td>" . $row['archive'] . ":" . $row['tag'] . "</td>" .
      "<td>";
      echo str_replace("\n", "<br>\n", $row['comment']);
      echo "</td><td>";
      $is_included = view_project($row['id']);
      if ($access->auth(AUTH::REP) && !$is_included && $id_project > 0)
         echo "<a href=\"$php_self?_action=rep_update&_no=" . $row['id'] . "&_sort=$sort&id_project=$id_project&search=$search\"><img src=\"images/folder.open.gif\" border=0 title=\"Legg til dette verket i repertoarlisten\"></a>";
      echo "</td></tr>";
   } else
   {
      echo "<tr>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=search value=\"$search\">
    <input type=hidden name=id_project value=$id_project>
    <input type=hidden name=_no value='$no'>
    <input type=hidden name=_action value=update>
    <td nowrap><input type=submit value=ok title=\"Lagre\">
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette " . $row['title'] . "?');\" title=\"Slette\"></td>
    <td><input type=text size=30 name=firstname value=\"" . $row['firstname'] . "\" title=\"Fornavn\">
         <input type=text size=30 name=lastname value=\"" . $row['lastname'] . "\" title=\"Etternavn\"></td>
    <td><input type=text size=30 name=title value=\"" . $row['title'] . "\" title=\"Verk\"></td>
    <td><input type=text size=30 name=work value=\"" . $row['work'] . "\" title=\"Navn på hovedverk hvis dette er et utdrag\"></td>
    <td><input type=text size=8 name=archive value=\"" . $row['archive'] . "\" title=\"Referanse på hvor noter er leid eller lånt\">
         <input type=text size=6 name=tag value=\"" . $row['tag'] . "\" title=\"Eventuelt referansenummer\"></td>
    <td><textarea cols=50 rows=7 wrap=virtual name=comment title=\"Fritekst\">" . $row['comment'] . "</textarea></td>
    <td>";
    view_project($row['id']);
    echo "</td></tr>";
   }
}
?> 

</table>
</form>


