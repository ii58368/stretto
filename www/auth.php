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
   const ABS_RO = 18; // Absence, r/o
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
   const LEAVE_RO = 35; // Leave r/o
   const ABS_ALL = 36; // Absence, for all participants, not only for a group e.g. violin 1
   const MEMB_GREP = 37; // List of member, possible to select filter
   const CONS = 38; // concert schedule, r/w
   const EVENT = 39; // What´s on?
   const RES_INV = 40; // Resources line-up, registered by art director r/w
   const LEAVE_RW = 41; // Leave, r/w
   const FEEDBACK = 42; // Feedback, register own
   const FEEDBACK_R = 43; // Feedback, admin r/o
   const FEEDBACK_W = 44; // Feedback, admin r/w
   const REP_RO = 45; // Music repository, r/o
   const ALL = 0x7fffffffffffffff; // 63 bits enabled
   const NO_VIEWS = 46;

   private $access;
   protected $confirmed_ts;  // retreived for real_uid

   function __construct()
   {
      global $whoami;

      $this->access = $this->auth_access($whoami->real_uid());

      $su_bit = $this->bit(self::SU);

      if ($whoami->uid() != $whoami->real_uid())
      {
         $this->access = ($this->access & $su_bit) | ($this->auth_access($whoami->uid()) & ~$su_bit);
      }

      $this->confirmed_ts = $this->auth_confirm_ts($whoami->real_uid());
   }

   /* PHP 5.6+
     protected function bit(...$auth)
     {
     $acc = 0;
     foreach ($auth as $a)
     $acc |= (1 << $a);

     return $acc;
     }
    */

   protected function bit()
   {
      $acc = 0;
      for ($i = 0; $i < func_num_args(); $i++)
         $acc |= (1 << func_get_arg($i));

      return $acc;
   }

   private function auth_confirm_ts($uid)
   {
      global $db;

      if (is_null($uid))
         return 0;

      $q = "select confirmed_ts "
              . "from person "
              . "where uid = '$uid'";

      $s = $db->query($q);
      $e = $s->fetch(PDO::FETCH_ASSOC);

      return $e['confirmed_ts'];
   }

   private function auth_access($uid)
   {
      global $db;

      if (is_null($uid))
         return 0;

      $query = "select access from person, auth_person, view " .
              "where person.id = auth_person.id_person " .
              "and auth_person.id_view = view.id " .
              "and person.uid = '$uid'";

      $stmt = $db->query($query);

      $access = 0;
      foreach ($stmt as $e)
      {
         $access |= $e['access'];
      }

      return $access;
   }

   public function auth_bit($auth)
   {
      //   printf("%x %x", $auth, $this->access);
      return ($this->access & $auth);
   }

   /* PHP 5.6+
     public function auth(...$auths)
     {
     $auth = 0;

     foreach ($auths as $a)
     $auth |= (1 << $a);

     return $this->auth_bit($auth);
     }
    */

   public function auth()
   {
      $auth = 0;

      for ($i = 0; $i < func_num_args(); $i++)
         $auth |= (1 << func_get_arg($i));

      return $this->auth_bit($auth);
   }

   public function auth_uid()
   {
      $uid = func_get_arg(0);
      $access = $this->auth_access($uid);

      $auth = 0;

      for ($i = 1; $i < func_num_args(); $i++)
         $auth |= (1 << func_get_arg($i));

      return ($access & $auth);
   }

}

class ACCESS extends AUTH
{

   private $list_ro = array();

   public function page_add($filename, $acc)
   {
      $this->list_ro[$filename] = isset($this->list_ro[$filename]) ?
              $this->list_ro[$filename] | $acc : $acc;
   }

   private function page_access($page = NULL)
   {
      global $php_self;

      if (is_null($page))
      {
         $e = explode('/', $php_self);
         $page = array_pop($e); // Pick the last element in the array
      }

      if (is_null($acc = $this->list_ro[$page]))
         return false;

      if (!$this->auth_bit($acc))
         return false;

      return true;
   }

   public function reject_if_unauth($a = 0)
   {
      if (!$this->page_access() || !$this->auth($a))
      {
         echo "<h1>Permission denied</h1>";
         echo "Kontakt <a href=\"mailto:sekretar@oslosymfoniorkester.no\">Sekretæren</a> for å få nødvendig tilgang";
         exit(0);
      }
   }

   public function confirm_pers_info()
   {
      global $whoami;
      global $db;
      global $php_self;
      
      $url_edit = "personEdit.php";
      
      if (request('confirm'))
      {
         $this->confirmed_ts = strtotime("now");
         $q = "update person set confirmed_ts = " . $this->confirmed_ts . " where uid = '" . $whoami->real_uid() . "'";
         $db->query($q);
      }

      if ($this->confirmed_ts < strtotime("-6 months"))
      {
         if (strstr($php_self, $url_edit) == false)
         {
            echo "<h1>Bekreft personalia</h1>\n";
            echo "Bekreft at personalia er oppdatert<p>\n";

            $q = "SELECT instrument, firstname, middlename, lastname, " .
                    "fee, address, postcode, city, def_pos, " .
                    "email, phone1, status, birthday, " .
                    "gdpr_ts, confirmed_ts " .
                    "FROM person, instruments " .
                    "where id_instruments = instruments.id " .
                    "and person.uid = '" . $whoami->real_uid() . "'";

            $s = $db->query($q);
            $e = $s->fetch(PDO::FETCH_ASSOC);

            $tb = new TABLE('id=no_border');

            $tb->td("Navn:");
            $tb->td($e['firstname'] . " " . $e['middlename'] . " " . $e['lastname']);
            $tb->tr();
            $tb->td("Instrument:");
            $tb->td(is_null($e['def_pos'] ? "" : $e['def_pos'] . ". ") . $e['instrument']);
            $tb->tr();
            $tb->td("Adresse:");
            $tb->td($e['address']);
            $tb->tr();
            $tb->td("Post:");
            $tb->td(sprintf("%04d %s", $e['postcode'], $e['city']));
            $tb->tr();
            $tb->td("Mail:");
            $tb->td($e['email']);
            $tb->tr();
            $tb->td("Mobil:");
            $tb->td($e['phone1']);
            $tb->tr();
            $tb->td("Status:");
            $tb->td($db->per_stat[$e['status']]);
            $tb->tr();
            $tb->td("Medlemskontingent:");
            $tb->td($db->per_fee[$e['fee']]);
            $tb->tr();
            $tb->td("Fødselsdag:");
            $tb->td(strftime('%e. %b %Y', $e['birthday']));
            $tb->tr();
            $tb->td("Samtykke:");
            $cell = ($e['gdpr_ts'] > strtotime("-1 year")) 
                    ? "Samtykker til at OSO kan behandle informasjonen min for spesifikke formål, og jeg kan trekke tilbake samtykket når som helst." 
                    : 'Aksepterer ikke at OSO kan behandle informasjonen min for spesifikke formål';
            $tb->td($cell);

            unset($tb);

            echo "<p>";
            echo "<button style=\"background-color:lightgreen;margin:5px\" onClick=\"window.location.href='$php_self?confirm=true'\">Bekreft personalia</button>";
            echo "<button onClick=\"window.location.href='$url_edit?_action=edit_pers'\">Endre</button>";

            exit(0);
         }
      }
   }

}

$access = new ACCESS();
