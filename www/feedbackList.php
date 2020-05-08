<?php

require 'framework.php';

echo "<h1>Tilbakemeldinger</h1>\n";

if ($action == 'update' && $access->auth(AUTH::FEEDBACK_W))
{
   $query = "update feedback set "
           . "status = " . request('status') . " "
           . "where id = $no";
   $db->query($query);
   $no = NULL;
}

if ($action == 'delete' && $access->auth(AUTH::FEEDBACK_W))
{
   $query = "delete from feedback where status = $db->fbk_stat_discarded";
   $db->query($query);
}

$query = "select feedback.id as id, feedback.ts as ts, "
        . "feedback.status as status, feedback.comment as comment, "
        . "project.name as pname, year, semester, "
        . "project.id as id_project, "
        . "firstname, lastname, instrument, email "
        . "from feedback left join project "
        . "on feedback.id_project = project.id "
        . "left join person "
        . "on feedback.id_person = person.id "
        . "left join instruments "
        . "on person.id_instruments = instruments.id "
        . "where ts > " . $season->ts()[0] . " "
        . "and ts < " . $season->ts()[1] . " "
        . "order by status, ts desc";
$stmt = $db->query($query);

$first_time_discarded = TRUE;

foreach ($stmt as $row)
{
   if ($row['status'] == $db->fbk_stat_discarded && $first_time_discarded)
   {
      echo "<input type=button value=\"Slett listen under\" onClick=\"location.href='$php_self?_action=delete';\"><br>\n";
      $first_time_discarded = FALSE;
   }
   
   //$heading = ($row['pname'] == null) ? "Generell" : $row['pname'] . " (" . $row['semester'] . "-" . $row['year'] . ")";
   //echo "<font size=+2><b>$heading</b></font>\n";

   $href = '';
   $a_end = '';
   $help = $db->fbk_stat[$row['status']];
   
   if ($access->auth(AUTH::FEEDBACK_W))
   {
      $a_end = "</a>";
      if ($row['status'] == $db->fbk_stat_new)
         $next_status = $db->fbk_stat_read;
      if ($row['status'] == $db->fbk_stat_read)
         $next_status = $db->fbk_stat_discarded;
      if ($row['status'] == $db->fbk_stat_discarded)
         $next_status = $db->fbk_stat_new;
      $href = "<a href=\"$php_self?_action=update&status=$next_status&_no=" . $row['id'] . "\">";
      $help .= ". Klick for å endre status til " . $db->fbk_stat[$next_status];
   }
   $bullet = "$href<img src=images/feedback_stat_" . $row['status'] . ".gif title=\"$help\">$a_end";
   echo "$bullet<font size=+2><b>" . strftime('%e.%b %Y', $row['ts']) . "</b></font>\n";

   $tb = new TABLE('id=no_border');

   if ($row['pname'] != null)
   {
      $tb->tr();
      $tb->td("<i>Prosjekt:</i>", 'align=right');
      $project = $row['pname'] . " (" . $row['semester'] . "-" . $row['year'] . ")";
      $hproject = "<a href=\"prjInfo.php?id=" . $row['id_project'] . "\">$project</a>";
      $tb->td($hproject);
      $tb->tr();
   }
   
   if ($row['lastname'] != null)
   {
      $tb->tr();
      $tb->td("<i>Fra:</i>", 'align=right');
      $name = $row['firstname'] . " " . $row['lastname'] . " (" . $row['instrument'] . ")";
      $hname = (strlen($row['email']) > 0) ? "<a href=\"mailto:?to=" . $row['email'] . "&subject=OSO: \">$name</a>" : $name;
      $tb->td($hname);
      $tb->tr();
   }
   
   unset($tb);

   echo "<br>";
   $comment = str_replace("\n", "<br>\n", $row['comment']);
   $comment = replace_links($comment);
   echo $comment;

   echo "<p>";
}

