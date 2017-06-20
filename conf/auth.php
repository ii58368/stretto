<?php

require_once 'conf/opendb.php';

class AUTH
{

   const su = 0; // Super-user.
   const myprj = 1; // My projects
   const prj_ro = 2; // Projects, r/o
   const myplan = 3; // My rehearsal plan, r/o
   const mydir = 4; // My direction
   const pers = 5; // My personal information
   const board_ro = 6; // General board information, r/o
   const dir_rw = 7; // read/write access for director
   const memb_ro = 8; // List of members, r/o
   const memb_rw = 9; // List of members, r/w
   const plan_ro = 10; // Rehearsal plan for all projects, r/o
   const plan_rw = 11; // Rehearsal plan for all projects, r/w
   const grp = 12; // List of groups, r/w
   const instr = 13; // List of instruments, r/w
   const acc = 14; // List of access, r/w
   const accgrp = 15; // List of access grpoups, r/w
   const rep = 16; // Music repository, r/w
   const prj = 17; // Projects, r/w
   const abs_ro = 18; // Absence register, r/o
   const loc = 19; // Location, r/w
   const doc_ro = 20; // Documents in general, r/o
   const doc_rw = 21; // Documents in general, r/w
   const cont_ro = 22; // Contigent, r/o
   const cont_rw = 23; // Contigent, r/w
   const prjm = 24; // Information about all projects, r/o
   const seat = 25; // Seating, r/w
   const prog = 26; // Concert program, r/w
   const prjdoc = 27; // Download of optional sheet music, recordings and project documents
   const dir_ro = 28; // Committee of direction, r/o
   const abs_rw = 29; // paticipant, absence, r/w
   const res = 30; // Resources available, r/o
   const res_self = 31; // Resources available, self register, r/w
   const res_reg = 32; // Resources available, registered by secretary, r/w
   const res_req = 33; // Resources available, recommended by MR, r/w
   const res_fin = 34; // Resources available, decided, r/w
   const fback = 35; // Feedback, r/w
   const abs_grp = 36; // Absence per group, r/w
   const abs_all = 37; // Absence, all members, r/w
   const cons = 38; // concert schedule, r/w
   const event = 39; // What´s on?
   const all = 0x7fffffffffffffff;

   private $list_ro = array();
   private $list_rw = array();

   function __construct()
   {
      $list_ro["myProject.php"] = $this->bit(self::myprj);
      $list_ro["project1x.php"] = $this->bit(self::myprj);
      $list_ro["myPlan.php"] = $this->bit(self::myplan);
      $list_ro["myDirection.php"] = $this->bit(self::mydir);
      $list_ro["personal.php"] = $this->bit(self::pers);
      $list_ro["dirResources.php"] = $this->bit(self::board_ro);
      $list_ro["dirShift.php"] = $this->bit(self::board_ro);
      $list_ro["dirProject.php"] = $this->bit(self::board_ro);
      $list_ro["dirPlan.php"] = $this->bit(self::board_ro);
      $list_ro["person.php"] = $this->bit(self::memb_ro);
      $list_ro["plan.php"] = $this->bit(self::plan_ro);
      $list_ro["group.php"] = $this->bit(self::board_ro);
      $list_ro["instruments.php"] = $this->bit(self::board_ro);
      $list_ro["access.php"] = $this->bit(self::board_ro);
      $list_ro["view.php"] = $this->bit(self::board_ro);
      $list_ro["repository.php"] = $this->bit(self::board_ro);
      $list_ro["projectxx.php"] = $this->bit(self::board_ro);
      $list_ro["feedback.php"] = $this->bit(self::board_ro);
      $list_ro["location.php"] = $this->bit(self::board_ro);
      $list_ro["participant.php"] = $this->bit(self::abs_ro);
      $list_ro["document.php"] = $this->bit(self::doc_ro, self::prjm);
      $list_ro["contigent.php"] = $this->bit(self::cont_ro);
      $list_ro["prjInfo.php"] = $this->bit(self::prjm);
      $list_ro["seating.php"] = $this->bit(self::prjm);
      $list_ro["plan.php"] = $this->bit(self::prjm);
      $list_ro["program.php"] = $this->bit(self::prjm);
      $list_ro["person.php"] = $this->bit(self::memb_ro);
      $list_ro["direction.php"] = $this->bit(self::dir_ro);
      $list_ro["register.php"] = $this->bit(self::all);
      $list_ro["feedback.php"] = $this->bit(self::prjm);
      $list_ro["absence.php"] = $this->bit(self::abs_grp);
      $list_ro["resources.php"] = $this->bit(self::all);
      //   $list_ro["concert.php"] = $this->bit(self::conc);
      $list_ro["event.php"] = $this->bit(self::prjm);

      $list_rw["myProject.php"] = $this->bit(self::myprj);
      $list_rw["project.php"] = $this->bit(self::myprj);
      $list_rw["myPlan.php"] = $this->bit(self::myplan);
      $list_rw["myDirection.php"] = $this->bit(self::mydir);
      $list_rw["personal.php"] = $this->bit(self::pers);
      $list_rw["dirResources.php"] = $this->bit(self::dir_rw);
      $list_rw["dirShift.php"] = $this->bit(self::dir_rw);
      $list_rw["dirProject.php"] = $this->bit(self::dir_rw);
      $list_rw["dirPlan.php"] = $this->bit(self::dir_rw);
      $list_rw["person.php"] = $this->bit(self::memb_rw);
      $list_rw["plan.php"] = $this->bit(self::plan_rw);
      $list_rw["group.php"] = $this->bit(self::grp);
      $list_rw["instruments.php"] = $this->bit(self::instr);
      $list_rw["access.php"] = $this->bit(self::acc);
      $list_rw["view.php"] = $this->bit(self::accgrp);
      $list_rw["repository.php"] = $this->bit(self::rep);
      $list_rw["project.php"] = $this->bit(self::prj); // TBD: occurs twice
      $list_rw["feedback.php"] = $this->bit(self::fback);
      $list_rw["location.php"] = $this->bit(self::loc);
//    $list_rw["participant.php"] = $this->bit(self::abs_ro);
      $list_rw["document.php"] = $this->bit(self::doc_rw, self::prjdoc);
      $list_rw["contigent.php"] = $this->bit(self::cont_rw);
//    $list_rw["prjInfo.php"] = $this->bit(self::prjm);
      $list_rw["seating.php"] = $this->bit(self::seat);
      $list_rw["plan.php"] = $this->bit(self::prjm);
      $list_rw["program.php"] = $this->bit(self::prj);
      $list_rw["person.php"] = $this->bit(self::memb_rw);
//    $list_rw["direction.php"] = $this->bit(self::dir_ro);
      $list_rw["register.php"] = $this->bit(self::all);
      $list_rw["feedback.php"] = $this->bit(self::prjm);
      $list_rw["absence.php"] = $this->bit(self::abs_grp);
      $list_rw["resources.php"] = $this->bit(self::all); // TBD
      $list_rw["concert.php"] = $this->bit(self::cons);
      $list_rw["event.php"] = $this->bit(self::event);
   }

   private function access_uid($uid, $auth)
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

      if ($auth & $access)
         return true;

      return false;
   }

   public function access($auth)
   {
      global $whoami;

      static $access = null;

      if (is_null($access))
      {
         $access = $this->access_uid($_SERVER[PHP_AUTH_USER], $auth);

         if ($access & $auth & (1 << self::su))
            return true;  // Yes, the actual user has super permisions

         if ($whoami != $_SERVER[PHP_AUTH_USER])
         {
            $access = $this->access_uid($whoami, $auth);
         }
      }

      return $access;
   }

   private function bit(...$auth)
   {
      $acc = 0;
      foreach ($auth as $a)
      {
         $acc |= (1 << $a);
      }
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

      if (!$this->access($acc))
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

      if ($this->access($this->bit(self::su)))
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