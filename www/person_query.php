<?php

require_once 'conf/opendb.php';

function person_query()
{
   global $db;
   global $sort;
   
   $query = "SELECT person.id as id, instruments.id as id_instruments, instrument, firstname, middlename, lastname, "
           . "address, postcode, city, "
           . "email, phone1, phone2, phone3, birthday, person.status as status, person.comment as comment "
           . "FROM person, instruments ";
   if (!is_null(request('f_project')))
      $query .= ", participant, project ";
   if (!is_null(request('f_group')))
      $query .= ", groups, member ";
   $query .= "where person.id_instruments = instruments.id ";
   if (!is_null(request('f_project')))
   {
      $query .= "and participant.id_person = person.id "
              . "and participant.id_project = project.id "
              . "and participant.stat_final = $db->par_stat_yes "
              . "and (";
      foreach (request('f_project') as $f_project)
         $query .= "project.id = $f_project or ";
      $query .= "false) ";
   }
   if (!is_null(request('f_group')))
   {
      $query .= "and groups.id = member.id_groups "
              . "and member.id_person = person.id "
              . "and (";
      foreach (request('f_group') as $f_group)
         $query .= "groups.id = $f_group or ";
      $query .= "false) ";
   }
   if (!is_null(request('f_status')))
   {
      $query .= "and (";
      foreach (request('f_status') as $f_status)
         $query .= "person.status = $f_status or ";
      $query .= "false) ";
   }
   if (!is_null(request('f_instrument')))
   {
      $query .= "and (";
      foreach (request('f_instrument') as $f_instrument)
         $query .= "instruments.id = $f_instrument or ";
      $query .= "false) ";
   }
   $query .= "group by person.id order by $sort";

   return $query;
}
