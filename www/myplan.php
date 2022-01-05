<?php

include 'framework.php';


echo "<h1>Spilleplan for " . $whoami->name() . "</h1>
     Dette er din individuelle spilleplan basert på de prosjekter 
     du skal være med på. 
     <p>";

$tb = new TABLE();

$tb->th("Dato");
$tb->th("Prøvetid");
$tb->th("Lokale");
$tb->th("Prosjekt");
$tb->th("Merknad");

$query = "SELECT plan.id as id, "
        . "plan.date as date, "
        . "plan.time as time, "
        . "plan.tsort as sort, "
        . "project.id as id_project, "
        . "project.deadline as deadline, "
        . "id_location, "
        . "plan.location as location, "
        . "location.name as lname, "
        . "project.name as pname, "
        . "location.url as url, "
        . "plan.comment as comment, "
        . "orchestration, "
        . "participant.stat_final as stat_final "
        . "FROM project, plan, location, participant "
        . "where plan.id_location = location.id "
        . "and plan.id_project = project.id "
        . "and participant.id_project = project.id "
        . "and participant.id_person = ".$whoami->id()." "
        . "and participant.stat_inv = $db->par_stat_yes "
        . "and not participant.stat_final = $db->par_stat_no "
        . "and plan.event_type = $db->plan_evt_rehearsal "
        . "and plan.date >= " . strtotime('today') . " "
        . "and project.status = $db->prj_stat_real "
        . "order by plan.date,plan.tsort,plan.time";

$stmt = $db->query($query);

$gfont = "<font color=lightgrey>";

foreach ($stmt as $row)
{   
   $date = strftime('%a %e.%b', $row['date']);
   $time = $row['time'];
   $url = $row['url'];
   $lname = $row['lname'];
   $location = $row['location'];
   $pname = $row['pname'];
   $comment = str_replace("\n", "<br>\n", $row['comment']);
   
   $tb->tr();
   
   $tutti = ($row['orchestration'] == $db->prj_type_reduced) ? '*' : '';
   
   if ($row['stat_final'] == $db->par_stat_void)
   {
      if ($row['orchestration'] == $db->prj_type_reduced && time() > $row['deadline'])
         continue;
      $tb->td("$gfont$date</font>", 'align=right nowrap');
      $tb->td("$gfont$time</font>");
      $tb->td("$gfont$lname</font>");
      $tb->td("$gfont$pname$tutti</font>");
      $tb->td("$gfont$comment</font>");
   }
   else
   {
      $tb->td("$date", 'align=right nowrap');
      $tb->td("$time");
      $hlname = (strlen($url) > 0) ? "<a href=\"$url\">$lname</a>" : $lname;
      $tb->td("$hlname $location");
      $tb->td("<a href=\"prjInfo.php?id=".$row['id_project']."\">$pname</a>$tutti");
      $tb->td("$comment");
   }    
}
