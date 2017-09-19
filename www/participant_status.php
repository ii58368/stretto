<?php

require_once 'conf/opendb.php';

function participant_status($person_id, $project_id)
{
   global $db;

   $q1 = "select deadline, orchestration from project where id=$project_id";
   $s1 = $db->query($q1);
   $prj = $s1->fetch(PDO::FETCH_ASSOC);

   $q2 = "select stat_self, stat_reg, stat_final from participant " .
           "where id_project = $project_id " .
           "and id_person = $person_id";
   $s2 = $db->query($q2);
   $part = $s2->fetch(PDO::FETCH_ASSOC);

   $past_dl = ($prj['deadline'] < date("now"));
   $orch = $prj['orchestration'];
   $stat_self = ($part['stat_reg'] != $db->par_stat_void) ? $part['stat_reg'] : $part['stat_self'];
   $stat_final = $part['stat_final'];

   $status = isset($part['stat_final']) ? $part['stat_final'] : $db->par_stat_void;
   $blink = null;
   
   if ($orch == $db->prj_orch_reduced)
   {
      if ($status == $db->par_stat_void)
      {
         if ($part['stat_self'] != $db->par_stat_void)
         {
            $blink = 'b';
            $status = $part['stat_self'];
         }
         if ($part['stat_reg'] != $db->par_stat_void)
         {
            $blink = 'b';
            $status = $part['stat_reg'];
         }
      }
   }

   if ($orch == $db->prj_orch_tutti)
   {
      if ($part['stat_final'] == $db->par_stat_void)
      {
         if ($part['stat_self'] != $db->par_stat_void)
         {
            $blink = 'b';
            $status = $part['stat_self'];
         }
         if ($part['stat_reg'] != $db->par_stat_void)
         {
            $blink = 'b';
            $status = $part['stat_reg'];
         }
      }
   }

   return array($status, $blink);
}
