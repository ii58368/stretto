<?php

require_once 'conf/opendb.php';

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$auth_su = 0; // Super-user.
$auth_myprj = 1; // My projects
$auth_prj_ro = 2; // Projects, r/o
$auth_myplan = 3; // My rehearsal plan, r/o
$uath_mydir = 4; // My direction
$auth_pers = 5; // My personal information
$auth_board_ro = 6; // General board information, r/o
$auth_dir_rw = 7; // read/write access for director
$auth_memb_ro = 8; // List of members, r/o
$auth_memb_rw = 9; // List of members, r/w
$auth_plan_ro = 10; // Rehearsal plan for all projects, r/o
$auth_plan_rw = 11; // Rehearsal plan for all projects, r/w
$auth_grp = 12; // List of groups, r/w
$auth_insr = 13; // List of instruments, r/w
$auth_acc = 14; // List of access, r/w
$auth_accgrp = 15; // List of access grpoups, r/w
$auth_rep = 16; // Music repository, r/w
$auth_prj = 17; // Projects, r/w
$auth_abs_ro = 18; // Absence register, r/o
$auth_loc = 19; // Location, r/w
$auth_doc_ro = 20; // Documents in general, r/o
$auth_doc_rw = 21; // Documents in general, r/w
$auth_cont_ro = 22; // Contigent, r/o
$auth_cont_rw = 23; // Contigent, r/w
$auth_prim = 24; // Information about all projects, r/o
$auth_seat = 25; // Seating, r/w
$auth_prog = 26; // Concert program, r/w
$auth_prjdoc = 27; // Download of optional sheet music, recordings and project documents
$auth_dir_ro = 28; // Committee of direction, r/o
$auth_abs_rw = 29; // paticipant, absence, r/w
$auth_res = 30; // Resources available, r/o
$auth_res_self = 31; // Resources available, self register, r/w
$auth_res_reg = 32; // Resources available, registered by secretary, r/w
$auth_res_req = 33; // Resources available, recommended by MR, r/w
$auth_res_fin = 34; // Resources available, decided, r/w
$auth_fback = 35; // Feedback, r/w
$auth_abs_grp = 36; // Absence per group, r/w
$auth_abs_all = 37; // Absence, all members, r/w
$auth_cons = 38; // concert schedule, r/w
$auth_event = 39; // What´s on?
$auth_all = 0x7fffffffffffffff;

$auth_list_ro = array(
    "myProject.php" => auth_bit($auth_myprj),
    "project1x.php" => auth_bit($auth_myprj),
    "myPlan.php" => auth_bit($auth_myplan),
    "myDirection.php" => auth_bit($auth_mydir),
    "personal.php" => auth_bit($auth_pers),
    "dirResources.php" => auth_bit($auth_board_ro),
    "dirShift.php" => auth_bit($auth_board_ro),
    "dirProject.php" => auth_bit($auth_board_ro),
    "dirPlan.php" => auth_bit($auth_board_ro),
    "person.php" => auth_bit($auth_memb_ro),
    "plan.php" => auth_bit($auth_plan_ro),
    "group.php" => auth_bit($auth_board_ro),
    "instruments.php" => auth_bit($auth_board_ro),
    "access.php" => auth_bit($auth_board_ro),
    "view.php" => auth_bit($auth_board_ro),
    "repository.php" => auth_bit($auth_board_ro),
    "projectxx.php" => auth_bit($auth_board_ro),
    "feedback.php" => auth_bit($auth_board_ro),
    "location.php" => auth_bit($auth_board_ro),
    "participant.php" => auth_bit($auth_abs_ro),
    "document.php" => auth_bit($auth_doc_ro, $auth_prjm),
    "contigent.php" => auth_bit($auth_cont_ro),
    "prjInfo.php" => auth_bit($auth_prjm),
    "seating.php" => auth_bit($auth_prjm),
    "plan.php" => auth_bit($auth_prjm),
    "program.php" => auth_bit($auth_prjm),
    "person.php" => auth_bit($auth_memb_ro),
    "direction.php" => auth_bit($auth_dir_ro),
    "register.php" => auth_bit($auth_all),
    "feedback.php" => auth_bit($auth_prjm),
    "absence.php" => auth_bit($auth_abs_grp),
    "resources.php" => auth_bit($auth_all),
    //   "concert.php" => auth_bit($auth_conc),
    "event.php" => auth_bit($auth_prjm)
);


$auth_list_rw = array(
    "myProject.php" => auth_bit($auth_myprj),
    "project.php" => auth_bit($auth_myprj),
    "myPlan.php" => auth_bit($auth_myplan),
    "myDirection.php" => auth_bit($auth_mydir),
    "personal.php" => auth_bit($auth_pers),
    "dirResources.php" => auth_bit($auth_dir_rw),
    "dirShift.php" => auth_bit($auth_dir_rw),
    "dirProject.php" => auth_bit($auth_dir_rw),
    "dirPlan.php" => auth_bit($auth_dir_rw),
    "person.php" => auth_bit($auth_memb_rw),
    "plan.php" => auth_bit($auth_plan_rw),
    "group.php" => auth_bit($auth_grp),
    "instruments.php" => auth_bit($auth_instr),
    "access.php" => auth_bit($auth_acc),
    "view.php" => auth_bit($auth_accgrp),
    "repository.php" => auth_bit($auth_rep),
    "project.php" => auth_bit($auth_prj), // TBD: occurs twice
    "feedback.php" => auth_bit($auth_fback),
    "location.php" => auth_bit($auth_loc),
//    "participant.php" => auth_bit($auth_abs_ro),
    "document.php" => auth_bit($auth_doc_rw, $auth_prjdoc),
    "contigent.php" => auth_bit($auth_cont_rw),
//    "prjInfo.php" => auth_bit($auth_prjm),
    "seating.php" => auth_bit($auth_seat),
    "plan.php" => auth_bit($auth_prjm),
    "program.php" => auth_bit($auth_prj),
    "person.php" => auth_bit($auth_memb_rw),
//    "direction.php" => auth_bit($auth_dir_ro),
    "register.php" => auth_bit($auth_all),
    "feedback.php" => auth_bit($auth_prjm),
    "absence.php" => auth_bit($auth_abs_grp),
    "resources.php" => auth_bit($auth_all), // TBD
    "concert.php" => auth_bit($auth_conc),
    "event.php" => auth_bit($auth_event)
);

function auth_access_uid($uid, $auth)
{
   $query = "select access from person, auth_person, view " .
           "where person.id = auth_person.id_person " .
           "and auth_person.id_view = view.id " .
           "and person.email = '$uid'";

   $result = mysql_query($query);

   while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
   {
      $access |= $row[access];
   }

   if ($auth & $access)
      return true;

   return false;
}

function auth_access($auth)
{
   global $auth_su;
   global $whoami;

   static $access = null;

   if ($access == null)
   {
      $access = auth_access_uid($_SERVER[PHP_AUTH_USER], $auth);

      if ($access & $auth & (1 << $auth_su))
         return true;  // Yes, the actual user has super permision

      if ($whoami != $_SERVER[PHP_AUTH_USER])
      {
         $access = auth_access_uid($whoami, $auth);
      }
   }

   return $access;
}

function auth_bit(...$auth)
{
   $acc = 0;
   foreach ($auth as $a)
   {
      $acc |= (1 << $a);
   }
   return $acc;
}

function auth_select_person($selected)
{
   global $per_stat_quited;

   $q = "SELECT email, firstname, lastname, instrument "
           . "FROM person, instruments "
           . "where not person.status = $per_stat_quited "
           . "and person.id_instruments = instruments.id "
           . "order by list_order, lastname, firstname";
   $r = mysql_query($q);

   while ($e = mysql_fetch_array($r, MYSQL_ASSOC))
   {
      echo "<option value=\"" . $e[email] . "\"";
      if ($e[email] == $selected)
         echo " selected";
      echo ">$e[firstname] $e[lastname] ($e[instrument])\n";
   }
}

function auth_page($auth_list, $page = NULL)
{
   global $php_self;

   if ($page == NULL)
      $page = $php_self;

   if (($acc = $auth_list[$page]) == NULL)
      return false;

   if (!auth_access($acc))
      return false;

   return true;
}

function auth_page_ro($page = NULL)
{
   global $auth_list_ro;

   return auth_access($auth_list_ro, $page);
}

function auth_page_rw($page = NULL)
{
   global $auth_list_rw;

   return auth_page($auth_list_rw, $page);
}

function auth_page_deny()
{
   if (auth_page_ro())
      return;

   echo "<h1>Permission denied</h1>";
   exit(0);
}

function auth_li($li, $page)
{
   //  if (auth_page_ro($page) || auth_page_rw($page))
   echo "<li><a href=\"$page\">$li</a></li>";
}

function auth_whoami()
{
   global $whoami;
   global $php_self;
   global $auth_su;

   if (auth_access(auth_bit($auth_su)))
   {
      echo "<form action=\"$php_self\" method=post>
         <select name=set_eff_uid onChange=\"set_cookie('uid', this.form.set_eff_uid.value); submit();\">\n";
      auth_select_person($whoami);
      echo "</select>
      </form>";
   } else
   {
      $query = "select firstname, lastname, instrument "
              . "from person, instruments "
              . "where person.id_instruments = instruments.id "
              . "and email = '$whoami'";
      $result = mysql_query($query);
      $row = mysql_fetch_array($result, MYSQL_ASSOC);

      echo "$row[firstname] $row[lastname] ($row[instrument])";
   }
}
