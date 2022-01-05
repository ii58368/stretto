<?php

require_once 'conf/opendb.php';

function participant_status($person_id, $project_id)
{
   global $db;

   $status = $db->par_stat_void;
   $blink = null;

   $q = "select stat_inv, stat_self, stat_reg, stat_req, stat_final, "
           . "ts_self, ts_reg, ts_req, ts_final, "
           . "orchestration, deadline "
           . "from participant, project "
           . "where participant.id_project = $project_id "
           . "and participant.id_person = $person_id "
           . "and participant.id_project = project.id";
   $s = $db->query($q);
   $part = $s->fetch(PDO::FETCH_ASSOC);

   // Return void if unknown participant
   if (is_null($part))
      return array($status, $blink);
   
   // Return void if not part of the line-up
   if ($part['stat_inv'] == $db->par_stat_void || $part['stat_inv'] == $db->par_stat_no)
      return array($status, $blink);
   
   // Return void if deadline is passed and no reply from member
   if ($part['stat_final'] == $db->par_stat_void &&
      $part['stat_self'] == $db->par_stat_void &&
      $part['stat_reg'] == $db->par_stat_void &&
      $part['orchestration'] == $db->prj_type_reduced &&
      strtotime('today') > $part['deadline'])
      return array($status, $blink);

   if (isset($part['stat_final']))
      $status = $part['stat_final'];

   if ($part['stat_final'] == $db->par_stat_void)
   {
      $blink = 'b';
      $status = ($part['orchestration'] == $db->prj_type_reduced) ?
              $db->par_stat_no : $db->par_stat_yes;

      if ($part['stat_self'] != $db->par_stat_void)
         $status = $part['stat_self'];

      if ($part['stat_reg'] != $db->par_stat_void)
         $status = $part['stat_reg'];
   }

   // If status has changed after the board has made a decition
   if ($part['stat_final'] != $db->par_stat_void)
      if (($part['stat_self'] != $db->par_stat_void && $part['ts_self'] > $part['ts_final']) ||
          ($part['stat_reg'] != $db->par_stat_void && $part['ts_reg'] > $part['ts_final']) ||
          ($part['stat_req'] != $db->par_stat_void && $part['ts_req'] > $part['ts_final']))
         $blink = 'b';

   return array($status, $blink);
}

function on_leave($id_person, $semester, $year)
{
   global $db; 
   
   $date_min = ($semester == 'V') ? "1. jan" : "1. aug";
   $date_max = ($semester == 'V') ? "1. aug" : "31. dec";
   
   $ts_min = strtotime("$date_min " . $year);
   $ts_max = strtotime("$date_max " . $year);

   $query = "select ts_from, ts_to, status "
           . "from `leave` "
           . "where id_person = $id_person "
           . "and ((ts_from >= $ts_min and ts_to <= $ts_max) "
           . "or (ts_from < $ts_min and ts_to > $ts_min) "
           . "or (ts_from < $ts_max and ts_to > $ts_max) "
           . "or (ts_from < $ts_min and ts_to > $ts_max)) "
           . "order by status";

   $stmt = $db->query($query);
   
   foreach ($stmt as $p)
      return $p['status'];
   
   return $db->lea_stat_unknown;
}

