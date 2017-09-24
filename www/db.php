<?php

class STMT implements Iterator
{

   private $vec = array();

   public function __construct($res)
   {
      if (is_object($res))
         while ($e = $res->fetch_assoc())
            $this->vec[] = $e;
   }

   public function fetch($opt)
   {
      switch ($opt)
      {
         case PDO::FETCH_ASSOC:
            if (!isset($this->vec[0]))
               return null;
            return $this->vec[0];
      }
      return null;
   }

   public function fetchAll($opt)
   {
      switch ($opt)
      {
         case PDO::FETCH_ASSOC:
            return $this->vec;
      }
      return null;
   }

   public function rowCount()
   {
      return count($this->vec);
   }
   
   public function current()
   {
      return current($this->vec);
   }

   public function key()
   {
      return key($this->vec);
   }

   public function next()
   {
      return next($this->vec);
   }

   public function rewind()
   {
      reset($this->vec);
   }

   public function valid()
   {
      $key = key($this->vec);
      return ($key !== null && $key !== false);
   }

}

class myPDO
{

   const FETCH_ASSOC = 2;

   private $mysqli;

   public function __construct($dsn, $dbuser, $dbpass)
   {
      $dsn = str_replace(";", "&", $dsn);
      parse_str($dsn, $prm);
      $this->mysqli = new mysqli($prm['mysql:host'], $dbuser, $dbpass, $prm['dbname']);
      $this->mysqli->set_charset($prm['charset']);
   }

   public function query($q)
   {
      $res = $this->mysqli->query($q);
      return new STMT($res);
   }

   public function setAttribute($attribute, $value)
   {
      
   }

   public function lastInsertId()
   {
      return $this->mysqli->insert_id;
   }
   
   public function quote($str)
   {
      return "'" . $this->mysqli->real_escape_string($str) . "'";
   }

}

class DB extends myPDO
{

   public function __construct($dbhost, $dbuser, $dbpass, $dbname)
   {
      parent::__construct("mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);

      parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      parent::setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
   }

   public function qpost($key)
   {
     if (!isset($_REQUEST[$key]))
       return "''";
     if (is_string($_REQUEST[$key]))
        return $this->quote($_REQUEST[$key]);
     return $_REQUEST[$key];
   }
   
// table: person
// field: status
   public $per_stat_quited = 0;
   public $per_stat_member = 1;
   public $per_stat_standin = 2;
   public $per_stat_hired = 3;
   public $per_stat_eng = 4;
   public $per_stat_apply = 5;
   public $per_stat = array("Sluttet", "Medlem", "Vikar", "Innleid", "Engasjert", "søkt");
// field: status_dir
   public $per_dir_avail = 0;
   public $per_dir_nocarry = 1;
   public $per_dir_exempt = 2;
   public $per_dir = array("Ledig", "Kan ikke bære bord", "Fritatt");
// table: shift
   public $shi_stat_free = 0;
   public $shi_stat_tentative = 1;
   public $shi_stat_confirmed = 2;
   public $shi_stat_failed = 3;
   public $shi_stat_leave = 4;
   public $shi_stat_responsible = 5;
   public $shi_stat_dropout = 6;
   public $shi_stat = array("Ledig", "Tentativt", "Bekreftet", "Ikke godkjent oppmøte",
       "Permisjon", "Regiansvarlig", "Er ikke med på prosjektet");
// table: project
   public $prj_stat_public = 0;
   public $prj_stat_internal = 1;
   public $prj_stat_tentative = 2;
   public $prj_stat_draft = 3;
   public $prj_stat_canceled = 4;
   public $prj_stat = array("Public", "Internt", "Tentativt", "Draft", "Kanselert");
   public $prj_orch_reduced = 0;
   public $prj_orch_tutti = 1;
   public $prj_docs_avail_rec = 0;
   public $prj_docs_avail_sheet = 1;
   public $prj_docs_avail_doc = 2;
// table: direction
   public $dir_stat_free = 0;
   public $dir_stat_allocated = 1;
// table: plan
   public $plan_evt_rehearsal = 0;
   public $plan_evt_direction = 1;
// table: participant
   public $par_stat_void = 0;
   public $par_stat_no = 1;
   public $par_stat_tentative = 2;
   public $par_stat_can = 3;
   public $par_stat_yes = 4;
   public $par_stat = array("Udefinert", "Nei", "Tentativt", "Kan hvis behov", "Ja");
// table: contigent
   public $con_stat_unknown = 0;
   public $con_stat_unpayed = 1;
   public $con_stat_press = 2;
   public $con_stat_payed = 3;
   public $con_stat_part = 4;
   public $con_stat = array("Undefinert", "Ikke betalt", "Purret", "Betalt", "Delvis betalt");
// table: absence
   public $abs_stat_in = 0;
   public $abs_stat_part = 1;
   public $abs_stat_sick = 2;
   public $abs_stat_busy = 3;
   public $abs_stat_away = 4;
   public $abs_stat_other = 5;
   public $abs_stat = array("Tilstede", "Delvis vekke", "Syk", "Opptatt", "Skulk", "Annet");
// table: event
   public $evt_importance_low = 0;
   public $evt_inportance_norm = 1;
   public $evt_importance_high = 2;
   public $evt_importance = array("Lav", "<b>Normal</b>", "<font color=red>Høy</font>");
   public $evt_status_draft = 0;
   public $evt_status_public = 1;
   public $evt_status = array("Draft", "Public");
// table: music
   public $mus_stat_no = 0;
   public $mus_stat_yes = 1;
// table: leave
   public $lea_stat_unknown = 0;
   public $lea_stat_registered = 1;
   public $lea_stat_rejected = 2;
   public $lea_stat_granted = 3;
   public $lea_stat = array("ukjent", "registrert", "avslått", "innvilget");
// table: record
   public $rec_stat_info = 0;
   public $rec_stat_board = 0;
   public $rec_stat = array("Info", "For styret");
}
