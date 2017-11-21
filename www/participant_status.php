<?php

require_once 'conf/opendb.php';

function participant_status($person_id, $project_id)
{
   global $db;

   $status = $db->par_stat_void;
   $blink = null;

   $q = "select stat_self, stat_reg, stat_req, stat_final, "
           . "ts_self, ts_reg, ts_req, ts_final, "
           . "orchestration "
           . "from participant, project "
           . "where participant.id_project = $project_id "
           . "and participant.id_person = $person_id "
           . "and participant.id_project = project.id";
   $s = $db->query($q);
   $part = $s->fetch(PDO::FETCH_ASSOC);

   if (is_null($part))
      return array($status, $blink);

   if (isset($part['stat_final']))
      $status = $part['stat_final'];

   if ($part['stat_final'] == $db->par_stat_void)
   {
      $blink = 'b';
      $status = ($part['orchestration'] == $db->prj_orch_reduced) ?
              $db->par_stat_no : $db->par_stat_yes;

      if ($part['stat_self'] != $db->par_stat_void)
         $status = $part['stat_self'];

      if ($part['stat_reg'] != $db->par_stat_void)
         $status = $part['stat_reg'];
   }

   if ($part['stat_final'] != $db->par_stat_void)
      if (($part['stat_self'] != $db->par_stat_void && $part['ts_self'] > $part['ts_final']) ||
          ($part['stat_reg'] != $db->par_stat_void && $part['ts_reg'] > $part['ts_final']) ||
          ($part['stat_req'] != $db->par_stat_void && $part['ts_req'] > $part['ts_final']))
         $blink = 'b';

   return array($status, $blink);
}
