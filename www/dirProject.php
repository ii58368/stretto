<?php
require 'framework.php';

if (is_null($sort))
   $sort = 'year,semester+DESC';

function list_group($id)
{
   global $db;

   $q = "SELECT firstname, lastname, status_dir, instrument, participant.stat_dir as status, " .
           "participant.comment_dir as shift_comment " .
           "FROM person, instruments, participant " .
           "where person.id = participant.id_person " .
           "and person.id_instruments = instruments.id " .
           "and participant.id_project = $id " .
           "and (participant.stat_dir >= $db->shi_stat_tentative and participant.stat_dir <= $db->shi_stat_failed) " .
           "order by list_order, lastname, firstname";

   $s = $db->query($q);

   $str = '';
   
   foreach ($s as $e)
   {
      if ($e['status'] == $db->shi_stat_tentative)
         $str .= "<font color=grey>";
      if ($e['status'] == $db->shi_stat_failed)
         $str .= "<strike>";
      $str .= $e['firstname'] . " " . $e['lastname'] . " (" . $e['instrument'] . ")";
      if ($e['status'] == $db->shi_stat_failed)
         $str .= "</strike>";
      if ($e['status'] == $db->shi_stat_tentative)
         $str .= "</font>";
      if ($e['status_dir'] == $db->per_dir_nocarry)
         $str .= "<image src=images/chair-minus-icon.png border=0 title=\"Kan ikke løfte bord\">";
      $str .= "<br>";
   }
   
   return $str;
}

function select_person($selected)
{
   global $db;

   $q = "SELECT person.id as id, firstname, lastname, instrument FROM person, instruments " .
           "where status = $db->per_stat_member and id_instruments = instruments.id " .
           "order by list_order, lastname, firstname";

   $s = $db->query($q);

   $str = "<select name=id_person>\n";
   
   foreach ($s as $e)
   {
      $str .= "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         $str .= " selected";
      $str .= ">" . $e['firstname'] . " " . $e['lastname'] . " (" . $e['instrument'] . ")";
   }
   $str .= "</select>\n";
   
   return $str;
}

function mail2dir($id_project)
{
   global $db;

   $q = "select name from project where id = $id_project";
   $s = $db->query($q);
   $e = $s->fetch(PDO::FETCH_ASSOC);
   $project_name = $e['name'];

   $q = "select email, phone1 from person, participant " .
           "where person.id = participant.id_person " .
           "and participant.id_project = $id_project " .
           "and (participant.stat_dir = $db->shi_stat_tentative or participant.stat_dir = $db->shi_stat_confirmed)";
   $s = $db->query($q);
   $r = $s->fetchAll(PDO::FETCH_ASSOC);

   $str = "<a href=\"mailto:";
   foreach ($r as $e)
      if (strlen($e['email']) > 0)
         $str .= $e['email'] . ",";

   $q = "select email, phone1 "
           . "from person, project "
           . "where person.id = project.id_person "
           . "and project.id = $id_project";
   $s2 = $db->query($q);
   $r2 = $s2->fetch(PDO::FETCH_ASSOC);

   if (strlen($r2['email']) > 0)
      $str .= $r2['email'];

   $str .= "?subject=OSO: Regikomit&eacute;, $project_name&body=Se oppdatert regiplan: https://" . $_SERVER['SERVER_NAME'] . "/direction.php?id_project=$id_project\"><image border=0 src=images/image1.gif hspace=20 title=\"Send mail alle i regikomit&eacute;en\"></a>";

   $str .= "<a href=\"sms:";
   reset($r);
   $s = '';
   foreach ($r as $e)
      if (strlen($e['phone1']) > 0)
         $s .= $e['phone1'] . ",";
   if (strlen($r2['phone1']) > 0)
      $s .= $r2['phone1'] . ",";
   $str .= str_replace(' ', '', $s);
   $str = substr($str, 0, -1);
   $str .= "&body=OSO Regikomit&eacute:\"><image border=0 src=images/sms.png hspace=20 title=\"Send SMS til alle i regikomit&eacute;en\"></a>";
   
   return $str;
}

echo "<h1>Regiprosjekt</h1>";

$form = new FORM();
$tb = new TABLE();

if ($access->auth(AUTH::DIR_RW))
   $tb->th("Edit");
$tb->th("<a href=\"$php_self?_sort=name,id\" title=\"Sorter p&aring; prosjektnavn\">Prosjekt</a>");
$tb->th("<a href=\"$php_self?_sort=year,semester+DESC,id\" title=\"Sorter p&aring; semester\">Sem</a>", 'nowrap');
$tb->th("Status");
$tb->th("Type");
$tb->th("Regiansvarlig");
$tb->th("Regikomit&eacute;");
$tb->th("Generell info");

if ($action == 'update' && $access->auth(AUTH::DIR_RW))
{
   $query = "update project set " .
           "id_person = " . request('id_person') . "," .
           "info_dir = " . $db->qpost('info_dir') . " " .
           "where id = $no";
   $query2 = "update participant set stat_dir = $db->shi_stat_free "
           . "where stat_dir = $db->shi_stat_responsible "
           . "and id_project = $no";
   $db->query($query2);
   $query2 = "update participant set stat_dir = $db->shi_stat_responsible " .
           "where id_person = " . request('id_person') . " " .
           "and id_project = $no";
   $db->query($query2);
   $db->query($query);

   $no = NULL;
}

$query = "SELECT project.id as id, name, semester, year, id_person, project.status as status, " .
        "firstname, lastname, instrument, info_dir, orchestration " .
        "FROM person, project, instruments " .
        "where project.id_person = person.id " .
        "and id_instruments = instruments.id " .
        "and project.year = " . $season->year() . " " .
        "and project.semester = '" . $season->semester() . "' " .
        "and not project.orchestration = " . $db->prj_type_social . " " .
        "order by " . str_replace("+", " ", $sort);

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   $tb->tr();

   if ($row['id'] != $no)
   {
      if ($access->auth(AUTH::DIR_RW))
         $tb->td("<a href=\"$php_self?_sort=$sort&_action=view&_no=" . $row['id'] . "\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>", 'align=center');
      $tb->td("<a href=\"dirPlan.php?id_project=" . $row['id'] . "\">" . $row['name'] . "</a>");
      $tb->td($row['semester'] . " " . " " . $row['year']);
      $tb->td($db->prj_stat[$row['status']]);
      $tb->td($db->prj_type[$row['orchestration']]);
      $tb->td($row['firstname'] . " " . $row['lastname'] . " (" . $row['instrument'] . ")", 'nowrap');
      $tb->td(mail2dir($row['id']) . "<br>" . list_group($row['id']));
      $tb->td(str_replace("\n", "<br>\n", $row['info_dir']));      
   } 
   else
   {
      $tb->td("<input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <input type hidden name=id_person value=" . $row['id_person'] . ">
    <input type=submit value=ok title=\"Lagere endring\" >", 'nowrap');
      
    $tb->td($row['name']);
    $tb->td($row['semester'] . " " . $row['year']);
    $tb->td($db->prj_stat[$row['status']]);
    $tb->td($db->prj_type[$row['orchestration']]);
    $tb->td(select_person($row['id_person']));
    $tb->td(list_group($row['id']));
    $tb->td("<textarea cols=44 rows=10 wrap=virtual name=info_dir>" . $row['info_dir'] . "</textarea>");
   }
}
