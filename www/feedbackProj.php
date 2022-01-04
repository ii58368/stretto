
<?php
require 'framework.php';

if ($sort == NULL)
   $sort = 'year,semester+DESC';


echo "<h1>Tilbakemelding - tekst</h1>";

$form = new FORM();
$tb = new TABLE();

if ($access->auth(AUTH::FEEDBACK_W))
{
   $tb->th("Edit");
   $tb->th("<a href=\"$php_self?_sort=name,id\" title=\"Sorter p&aring; prosjektnavn\">Prosjekt</a>");
   $tb->th("<a href=\"$php_self?_sort=year,semester+DESC,id\" title=\"Sorter p&aring; semester\">Sem</a>", 'nowrap');
   $tb->th("Status");
   $tb->th("Tutti");
   $tb->th("Tekst for tilbakemelding");
}

if ($action == 'update' && $access->auth(AUTH::FEEDBACK_W))
{
   $query = "update project set "
           . "feedback_text = " . $db->qpost('feedback_text') . " "
           . "where id = $no";
   $db->query($query);
   $no = NULL;
}

$query = "SELECT project.id as id, name, semester, year, status, " .
        "orchestration, feedback_text " .
        "FROM project " .
        "where project.year = " . $season->year() . " " .
        "and project.semester = '" . $season->semester() . "' " .
        "order by " . str_replace("+", " ", $sort);

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   $tb->tr();
   if ($row['id'] != $no)
   {
      if ($access->auth(AUTH::PRJ))
         $tb->td("<a href=\"$php_self?_sort=$sort&_action=view&_no=" . $row['id'] . "\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>", 'align=center');
   }
   else
   {
      $tb->td("
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>     
    <input type=submit value=ok title=\"Lagere\" >");
   }

   $tb->td("<a href=\"prjInfo.php?id=" . $row['id'] . "\">" . $row['name'] . "</a>");
   $tb->td($row['semester'] . "-" . $row['year']);
   $tb->td($db->prj_stat[$row['status']]);
   $tutti = ($row['orchestration'] == $db->prj_type_tutti) ? "<img src=\"images/tick2.gif\" border=0>" : '';
      $tb->td($tutti, 'align=center');

   if ($row['id'] != $no)
   {
      $tb->td(str_replace("\n", "<br>\n", $row['feedback_text']));
   }
   else
   {
      $tb->td("<textarea cols=44 rows=10 wrap=virtual name=feedback_text title=\"Prosjektinfo\">" . $row['feedback_text'] . "</textarea>");
   }
}
