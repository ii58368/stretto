<?php

class DB extends PDO
{

   public function __construct()
   {
//$dbhost = 'mysql04.fastname.no';
//$dbuser = 'd301218';
//$dbpass = 'slow9down!';
//$dbname = 'd301218';

      $dbhost = '127.0.0.1';
      $dbuser = 'root';
      $dbpass = 'Knoll.and.Tott';
      $dbname = 'stretto';
      parent::__construct("mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
   }

   // table: person
// field: status
   public $per_stat_quited = 0;
   public $per_stat_member = 1;
   public $per_stat_standin = 2;
   public $per_stat_hired = 3;
   public $per_stat_eng = 4;
   public $per_stat = array("Sluttet", "Medlem", "Vikar", "Innleid", "Engasjert");
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
// table: project
   public $prj_stat_public = 0;
   public $prj_stat_internal = 1;
   public $prj_stat_tentative = 2;
   public $prj_stat_draft = 3;
   public $prj_stat_canceled = 4;
   public $prj_stat = array("Public", "Internt", "Tentativt", "Draft", "Kanselert");
   public $prj_orch_reduced = 0;
   public $prj_orch_tutti = 1;
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
   public $abs_stat_undef = 0;
   public $abs_stat_sick = 1;
   public $abs_stat_busy = 2;
   public $abs_stat_away = 3;
   public $abs_stat_part = 4;
   public $abs_stat_in = 5;
   public $abs_stat = array("Udefinert", "Syk", "Opptatt", "skulk", "Delvis vekke", "Tilstede");
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

}

$db = new DB();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
?>