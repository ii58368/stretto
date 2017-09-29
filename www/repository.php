<?php
require 'framework.php';

if ($sort == NULL)
   $sort = 'lastname,firstname,title';

$id_project = is_null(request('id_project')) ? 0 : request('id_project');

if ($id_project == 0)
{
   $s = $db->query("select max(id) as id from project");
   $e = $s->fetch(PDO::FETCH_ASSOC);
   if (isset($e['id']))
      $id_project = $e['id'];
}

function select_project()
{
   global $db;
   global $id_project;

   echo "<select name=id_project onChange=\"submit();\">\n";

   $q = "select id, name, semester, year from project\n"
           . " where year >= " . date("Y")
           . " order by year,semester DESC, id";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=" . $e['id'];
      if ($id_project == $e['id'])
         echo " selected";
      echo ">" . $e['name'] . " (" . $e['semester'] . $e['year'] . ")</option>\n";
   }
   echo "</select>\n";
}

echo "
    <h1>Notearkiv</h1>";
if ($access->auth(AUTH::REP))
   echo "
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=id_project value=$id_project>
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Nytt verk\">
    </form>";
echo "
    <form action='$php_self' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::REP))
   echo "
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=id+DESC&id_project=$id_project\">Edit</a></th>";
echo "
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=lastname,firstname,title&id_project=$id_project\">Komponist</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=title,lastname,firstname&id_project=$id_project\">Tittle</a></th>
      <th bgcolor=#A6CAF0>Fra</th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=reference,tag&id_project=$id_project\">Arkivref</a></th>
      <th bgcolor=#A6CAF0>Kommentar</th>
      <th bgcolor=#A6CAF0>\n";
if ($access->auth(AUTH::REP))
   select_project();
else
   echo "Prosjekt";
echo "</th>
      </tr>";

if ($action == 'new')
{
   echo "
  <tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=submit value=ok></td>
    <th><input type=text size=30 name=firstname title=Fornavn>
        <input type=text size=30 name=lastname title=Etternavn></th>
    <th><input type=text size=30 name=title title=\"Navn på verk\"></th>
    <th><input type=text size=30 name=work title=\"Navn på hovedverk hvis dette er et utdrag\"></th>
    <th><input type=text size=8 name=reference value=OSO title=\"Referanse på hvor noter er leid eller lånt\">
        <input type=text size=6 name=tag value=";
   $s = $db->query("select max(tag) as max_tag from repository where reference=\"OSO\"");
   $e = $s->fetch(PDO::FETCH_ASSOC);
   echo isset($e['max_tag']) ? $e['max_tag'] : 0;
   echo " title=\"Eventuelt referansnummer\"></th>
    <th><textarea cols=50 rows=7 wrap=virtual name=comment></textarea></th>
    <th></th>
  </tr>\n";
}

if ($action == 'update' && $access->auth(AUTH::REP))
{
   $tag = is_numeric(request('tag')) ? request('tag') : 0;
   $ts = strtotime("now");

   if ($no == NULL)
      $query = "insert into repository (firstname, lastname, title, work, tag, reference, comment, ts) "
              . "values (" . $db->qpost('firstname') . ", " . $db->qpost('lastname') . ", " . $db->qpost('title') . ", " . $db->qpost('work') . ", "
              . "$tag, " . $db->qpost('reference') . ", " . $db->qpost('comment') . ", $ts)";
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
                 "reference = " . $db->qpost('reference') . "," .
                 "comment = " . $db->qpost('comment') . " ," .
                 "ts = $ts " .
                 "where id = $no";
      $no = NULL;
   }
   $db->query($query);
}

if ($action == 'toggle_update' && $access->auth(AUTH::REP))
{
   $q = "select status, comment from music where id_project=$id_project and id_repository=$no";
   $s = $db->query($q);
   $e = $s->fetch(PDO::FETCH_ASSOC);
   $status = ($e['status'] == $db->mus_stat_yes) ? $db->mus_stat_no : $db->mus_stat_yes;
   $comment = is_null(request('comment')) ? $e['comment'] : request('comment');
   $query = "replace into music (id_repository, id_project, status, comment) values ($no, $id_project, $status, " . $db->quote($comment) . ")";
   $db->query($query);
   $no = null;
}

$query = "select id_repository, comment, status from music where id_project=$id_project";
$stmt = $db->query($query);
$music = $stmt->fetchAll(PDO::FETCH_ASSOC);

function toggle_project($cno)
{
   global $music;
   global $db;
   global $action;
   global $no;
   global $sort;
   global $php_self;
   global $id_project;

   $q = "select name, semester, year, music.status as status, music.comment as comment "
           . "from project, music "
           . "where music.id_project = project.id "
           . "and music.id_repository = $cno";
   $s = $db->query($q);

   $title = '';
   foreach ($s as $e)
      if ($e['status'] == $db->mus_stat_yes)
         $title = $title . $e['name'] . " (" . $e['semester'] . "-" . $e['year'] . "): " . $e['comment'] . "\n";

   echo "<td";
   foreach ($music as $e)
      if ($e['id_repository'] == $cno)
         break;
   $id_repository = isset($e['id_repository']) ? $e['id_repository'] : 0;
   $status = isset($e['status']) ? $e['status'] : 0;

   if ($id_repository == $cno && $status == $db->mus_stat_yes)
      echo " bgcolor=lightgreen";

   echo ">";
   if ($action == 'toggle' && $no == $cno)
   {
      echo "<input type=hidden name=_action value=toggle_update>
            <input type=hidden name=_sort value='$sort'>
            <input type=hidden name=id_project value=$id_project>
            <input type=hidden name=_no value='$no'>
            <input type=submit value=ok>
          <input type=text name=comment value=\"" . $e['comment'] . "\" size=20>\n";
   }
   else
   {
      $act = ($status == $db->mus_stat_yes) ? 'toggle_update' : 'toggle';
      echo "<a href=\"$php_self?_action=$act&_no=$cno&_sort=$sort&id_project=$id_project&id_repository=$cno\"><img src=\"images/cross_re.gif\" border=0 title=\"$title\"></a>";
      if ($id_repository == $cno && $status == $db->mus_stat_yes)
         echo $e['comment'];
   }
   echo "</td>\n";
}

function view_project($cno)
{
   global $db;

   $q = "select name, semester, year, music.status as status, music.comment as comment "
           . "from project, music "
           . "where music.id_project = project.id "
           . "and music.id_repository = $cno";
   $s = $db->query($q);

   echo "<td>";

   foreach ($s as $e)
      if ($e['status'] == $db->mus_stat_yes)
         echo $e['name'] . " (" . $e['semester'] . "-" . $e['year'] . "): " . $e['comment'] . "<br>\n";

   echo "</td>\n";
}

$query = "SELECT id, firstname, lastname, title, work, tag, reference, comment " .
        "FROM repository order by $sort";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($action != 'view' || $row['id'] != $no)
   {
      echo "<tr>";
      if ($access->auth(AUTH::REP))
         echo "
         <td><center>
           <a href=\"$php_self?_sort=$sort&_action=view&_no=" . $row['id'] . "&id_project=$id_project\"><img src=\"images/cross_re.gif\" border=0></a>
             </center></td>";
      echo
      "<td>" . $row['lastname'] . ", " . $row['firstname'] . "</td>" .
      "<td>" . $row['title'] . "</td>" .
      "<td>" . $row['work'] . "</td>" .
      "<td>" . $row['reference'] . ":" . $row['tag'] . "</td>" .
      "<td>";
      echo str_replace("\n", "<br>\n", $row['comment']);
      echo "</td>";
      if ($access->auth(AUTH::REP))
         toggle_project($row['id']);
      else
         view_project($row['id']);
      echo "</tr>";
   } else
   {
      echo "<tr>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=id_project value=$id_project>
    <input type=hidden name=_no value='$no'>
    <input type=hidden name=_action value=update>
    <td nowrap><input type=submit value=ok>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette " . $row['title'] . "?');\"></td>
    <td><input type=text size=30 name=firstname value=\"" . $row['firstname'] . "\" title=\"Fornavn\">
         <input type=text size=30 name=lastname value=\"" . $row['lastname'] . "\" title=\"Etternavn\"></td>
    <td><input type=text size=30 name=title value=\"" . $row['title'] . "\"></td>
    <td><input type=text size=30 name=work value=\"" . $row['work'] . "\"></td>
    <td><input type=text size=8 name=reference value=\"" . $row['reference'] . "\">
         <input type=text size=6 name=tag value=\"" . $row['tag'] . "\"></td>
    <td><textarea cols=50 rows=7 wrap=virtual name=comment>" . $row['comment'] . "</textarea></td>
    </tr>";
   }
}
?> 

</table>
</form>


