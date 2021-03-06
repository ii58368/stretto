<?php

require_once 'conf/opendb.php';
require_once 'request.php';

function log_query($full)
{
   global $sort;
   
   $select = "select person.id as id, "
           . "instrument, firstname, middlename, lastname, "
           . "person.uid as uid, "
           . "person.status as status, "
           . "person.fee as fee, "
           . "person.gdpr_ts as gdpr_ts, "
           . "person.confirmed_ts as confirmed_ts, "
           . "person.birthday as birthday, "
           . "person.comment as comment, "
           . "record.status as rstatus, "
           . "record.ts as rts, "
           . "record.comment as rcomment "
           . "from instruments, person left join record on person.id = record.id_person ";
   
   $where = is_null($full) ? "and record.ts > " . strtotime("-1 year") . " " : " ";
   
   $qsort = str_replace("+", " ", $sort);
   return $select . $where . from_filter() . where_filter() . " order by $qsort,record.ts desc";
}

function person_query()
{
   $query = "SELECT person.id as id, instruments.id as id_instruments, instrument, firstname, middlename, lastname, "
           . "sex, fee, address, postcode, city, "
           . "person.email as email, phone1, phone2, phone3, birthday, person.status as status, person.comment as comment "
           . "FROM person, instruments ";
   
   return $query . from_filter() . where_filter() . sort_filter();
}

function from_filter()
{
   $query = "";
   
   if (!is_null(request('f_project')))
      $query .= ", participant, project ";
   if (!is_null(request('f_group')))
      $query .= ", groups, member ";

   return $query;
}

function where_filter()
{
   global $db;
   
   $query = "where person.id_instruments = instruments.id ";
   if (!is_null(request('f_project')))
   {
      $query .= "and participant.id_person = person.id "
              . "and participant.id_project = project.id "
              . "and participant.stat_inv = $db->par_stat_yes "
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
   
   return $query;
}

function sort_filter()
{
   global $sort;

   $qsort = str_replace("+", " ", $sort);
   $query = "group by person.id order by $qsort";

   return $query;
}
