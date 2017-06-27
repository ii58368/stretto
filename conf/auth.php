<?php

require_once 'conf/opendb.php';
require_once 'request.php';

class AUTH
{
   const SU = 0; // Super-user.
   const MYPRJ = 1; // My projects
   const PRJ_RO = 2; // Projects, r/o
   const MYPLAN = 3; // My rehearsal plan, r/o
   const MYDIR = 4; // My direction
   const PERS = 5; // My personal information
   const BOARD_RO = 6; // General board information, r/o
   const DIR_RW = 7; // read/write access for director
   const MEMB_RO = 8; // List of members, r/o
   const MEMB_RW = 9; // List of members, r/w
   const PLAN_RO = 10; // Rehearsal plan for all projects, r/o
   const PLAN_RW = 11; // Rehearsal plan for all projects, r/w
   const GRP = 12; // List of groups, r/w
   const INSTR = 13; // List of instruments, r/w
   const ACC = 14; // List of access, r/w
   const ACCGRP = 15; // List of access groups, r/w
   const REP = 16; // Music repository, r/w
   const PRJ = 17; // Projects, r/w
   const ABS_RO = 18; // Absence register, r/o
   const LOC = 19; // Location, r/w
   const DOC_RO = 20; // Documents in general, r/o
   const DOC_RW = 21; // Documents in general, r/w
   const CONT_RO = 22; // Contigent, r/o
   const CONT_RW = 23; // Contigent, r/w
   const PRJM = 24; // Information about all projects, r/o
   const SEAT = 25; // Seating, r/w
   const PROG = 26; // Concert program, r/w
   const PRJDOC = 27; // Download of optional sheet music, recordings and project documents
   const DIR_RO = 28; // Committee of direction, r/o
   const ABS_RW = 29; // paticipant, absence, r/w
   const RES = 30; // Resources available, r/o
   const RES_SELF = 31; // Resources available, self register, r/w
   const RES_REG = 32; // Resources available, registered by secretary, r/w
   const RES_REQ = 33; // Resources available, recommended by MR, r/w
   const RES_FIN = 34; // Resources available, decided, r/w
   const FBACK = 35; // Feedback, r/w
   const ABS_GRP = 36; // Absence per group, r/w
   const ABS_ALL = 37; // Absence, all members, r/w
   const CONS = 38; // concert schedule, r/w
   const EVENT = 39; // What´s on?
   const ALL = 0x7fffffffffffffff;
   
   const NO_VIEWS = 39;

   private $list_ro = array();
   private $list_rw = array();

   function __construct()
   {
      $list_ro["myProject.php"] = $this->bit(self::MYPRJ);
      $list_ro["project1x.php"] = $this->bit(self::MYPRJ);
      $list_ro["myPlan.php"] = $this->bit(self::MYPLAN);
      $list_ro["myDirection.php"] = $this->bit(self::MYDIR);
      $list_ro["personal.php"] = $this->bit(self::PERS);
      $list_ro["dirResources.php"] = $this->bit(self::BOARD_RO);
      $list_ro["dirShift.php"] = $this->bit(self::BOARD_RO);
      $list_ro["dirProject.php"] = $this->bit(self::BOARD_RO);
      $list_ro["dirPlan.php"] = $this->bit(self::BOARD_RO);
      $list_ro["person.php"] = $this->bit(self::MEMB_RO);
      $list_ro["plan.php"] = $this->bit(self::PLAN_RO);
      $list_ro["group.php"] = $this->bit(self::BOARD_RO);
      $list_ro["instruments.php"] = $this->bit(self::BOARD_RO);
      $list_ro["access.php"] = $this->bit(self::BOARD_RO);
      $list_ro["view.php"] = $this->bit(self::BOARD_RO);
      $list_ro["repository.php"] = $this->bit(self::BOARD_RO);
      $list_ro["projectxx.php"] = $this->bit(self::BOARD_RO);
      $list_ro["feedback.php"] = $this->bit(self::BOARD_RO);
      $list_ro["location.php"] = $this->bit(self::BOARD_RO);
      $list_ro["participant.php"] = $this->bit(self::ABS_RO);
      $list_ro["document.php"] = $this->bit(self::DOC_RO, self::PRJM);
      $list_ro["contigent.php"] = $this->bit(self::CONT_RO);
      $list_ro["prjInfo.php"] = $this->bit(self::PRJM);
      $list_ro["seating.php"] = $this->bit(self::PRJM);
      $list_ro["plan.php"] = $this->bit(self::PRJM);
      $list_ro["program.php"] = $this->bit(self::PRJM);
      $list_ro["person.php"] = $this->bit(self::MEMB_RO);
      $list_ro["direction.php"] = $this->bit(self::DIR_RO);
      $list_ro["register.php"] = $this->bit(self::ALL);
      $list_ro["feedback.php"] = $this->bit(self::PRJM);
      $list_ro["absence.php"] = $this->bit(self::ABS_GRP);
      $list_ro["resources.php"] = $this->bit(self::ALL);
      //   $list_ro["concert.php"] = $this->bit(self::CONC);
      $list_ro["event.php"] = $this->bit(self::PRJM);

      $list_rw["myProject.php"] = $this->bit(self::MYPRJ);
      $list_rw["project.php"] = $this->bit(self::MYPRJ);
      $list_rw["myPlan.php"] = $this->bit(self::MYPLAN);
      $list_rw["myDirection.php"] = $this->bit(self::MYDIR);
      $list_rw["personal.php"] = $this->bit(self::PERS);
      $list_rw["dirResources.php"] = $this->bit(self::DIR_RW);
      $list_rw["dirShift.php"] = $this->bit(self::DIR_RW);
      $list_rw["dirProject.php"] = $this->bit(self::DIR_RW);
      $list_rw["dirPlan.php"] = $this->bit(self::DIR_RW);
      $list_rw["person.php"] = $this->bit(self::MEMB_RW);
      $list_rw["plan.php"] = $this->bit(self::PLAN_RW);
      $list_rw["group.php"] = $this->bit(self::GRP);
      $list_rw["instruments.php"] = $this->bit(self::INSTR);
      $list_rw["access.php"] = $this->bit(self::ACC);
      $list_rw["view.php"] = $this->bit(self::ACCGRP);
      $list_rw["repository.php"] = $this->bit(self::REP);
      $list_rw["project.php"] = $this->bit(self::PRJ); // TBD: occurs twice
      $list_rw["feedback.php"] = $this->bit(self::FBACK);
      $list_rw["location.php"] = $this->bit(self::LOC);
//    $list_rw["participant.php"] = $this->bit(self::ABS_RO);
      $list_rw["document.php"] = $this->bit(self::DOC_RW, self::PRJDOC);
      $list_rw["contigent.php"] = $this->bit(self::CONT_RW);
//    $list_rw["prjInfo.php"] = $this->bit(self::PRJM);
      $list_rw["seating.php"] = $this->bit(self::SEAT);
      $list_rw["plan.php"] = $this->bit(self::PRJM);
      $list_rw["program.php"] = $this->bit(self::PRJ);
      $list_rw["person.php"] = $this->bit(self::MEMB_RW);
//    $list_rw["direction.php"] = $this->bit(self::DIR_RO);
      $list_rw["register.php"] = $this->bit(self::ALL);
      $list_rw["feedback.php"] = $this->bit(self::PRJM);
      $list_rw["absence.php"] = $this->bit(self::ABS_GRP);
      $list_rw["resources.php"] = $this->bit(self::ALL); // TBD
      $list_rw["concert.php"] = $this->bit(self::CONS);
      $list_rw["event.php"] = $this->bit(self::EVENT);
   }

   private function access_uid($uid)
   {
      global $db;

      $query = "select access from person, auth_person, view " .
              "where person.id = auth_person.id_person " .
              "and auth_person.id_view = view.id " .
              "and person.uid = '$uid'";

      $stmt = $db->query($query);

      foreach ($stmt as $row)
      {
         $access |= $row[access];
      }

      return $access;
   }

   /*
   public function access(...$auths)
   {
      global $whoami;

      $auth = 0;
      foreach ($auths as $a)
      {
         $auth |= (1 << $a);
      }
   
      static $access = null;

      if (is_null($access))
      {
         $access = $this->access_uid($_SERVER[PHP_AUTH_USER]);

         if ($access & $auth & (1 << self::SU))
            return true;  // Yes, the real user has super permisions

         if ($whoami != $_SERVER[PHP_AUTH_USER])
         {
            $access = $this->access_uid($whoami);
         }
      }

      return ($access & auth);
   }
   */
   private function access_bit($auth)
   {
      global $whoami;

      static $access = null;

      if (is_null($access))
      {
         $access = $this->access_uid($_SERVER[PHP_AUTH_USER]);
         
         $su_bit = $this->bit(self::SU);
         
         if ($whoami != $_SERVER[PHP_AUTH_USER])
         {
            $access = ($access & $su_bit) | ($this->access_uid($whoami) & ~$su_bit);
         }
      }

      return ($access & $auth);
   }

   public function access(...$auths)
   {
      $auth = 0;
      
      foreach ($auths as $a)
         $auth |= (1 << $a);
      
      return $this->access_bit($auth);
   }
    
   private function bit(...$auth)
   {
      $acc = 0;
      foreach ($auth as $a)
         $acc |= (1 << $a);

      return $acc;
   }

   public function select_person($selected)
   {
      global $db;

      $q = "SELECT uid, firstname, lastname, instrument "
              . "FROM person, instruments "
              . "where not person.status = $db->per_stat_quited "
              . "and person.id_instruments = instruments.id "
              . "order by list_order, lastname, firstname";
      $s = $db->query($q);

      foreach ($s as $e)
      {
         echo "<option value=\"" . $e[uid] . "\"";
         if ($e[uid] == $selected)
            echo " selected";
         echo ">$e[firstname] $e[lastname] ($e[instrument])\n";
      }
   }

   private function page($auth_list, $page = NULL)
   {
      global $php_self;

      if (is_null($page))
         $page = $php_self;

      if (is_null($acc = $auth_list[$page]))
         return false;

      if (!$this->access_bit($acc))
         return false;

      return true;
   }

   public function page_ro($page = NULL)
   {
      return $this->page($this->list_ro, $page);
   }

   public function page_rw($page = NULL)
   {
      return $this->page($this->list_rw, $page);
   }

   function page_deny()
   {
      if ($this->page_ro())
         return;

      echo "<h1>Permission denied</h1>";
      exit(0);
   }

   public function li($li, $page)
   {
//  if ($this->page_ro($page) || $this->page_rw($page))
      echo "<li><a href=\"$page\">$li</a></li>";
   }

   public function whoami()
   {
      global $db;
      global $whoami;
      global $php_self;

      if ($this->access(self::SU))
      {
         echo "<form action=\"$php_self\" method=post>
         <select name=set_eff_uid onChange=\"set_cookie('uid', this.form.set_eff_uid.value); submit();\">\n";
         $this->select_person($whoami);
         echo "</select>
      </form>";
      } else
      {
         $query = "select firstname, lastname, instrument "
                 . "from person, instruments "
                 . "where person.id_instruments = instruments.id "
                 . "and uid = '$whoami'";
         $stmt = $db->query($query);
         $row = $stmt->fetch(PDO::FETCH_ASSOC);

         echo "$row[firstname] $row[lastname] ($row[instrument])";
      }
   }

}

$auth = new AUTH();