<?php

require_once 'conf/opendb.php';
require_once 'request.php';
require_once 'auth.php';

class ITEM
{

   public $name;
   public $link; // URL or class MENU
   public $auth;

   function __construct($name, $link, $acc)
   {
      $this->name = $name;
      $this->link = $link;
      $this->acc = $acc;
   }

}

class SUBMENU
{

   private $menu = array();
   private $ul_option = null;

   function __construct($ul_option)
   {
      $this->ul_option = $ul_option;
   }

   /* PHP 5.6+
     public function add($name, $link, ...$auth)
     {
     global $access;

     $acc = 0;
     foreach ($auth as $a)
     $acc |= (1 << $a);

     if ($acc == 0)
     $acc = AUTH::ALL;

     $item = new ITEM($name, $link, $acc);

     array_push($this->menu, $item);

     if (is_string($link))
     {
     $urn = explode('?', $link);
     $path = $urn[0];
     $path_array = explode('/', $path);
     $filename = array_pop($path_array);

     $access->page_add($filename, $acc);
     }
     }
    */

   public function add()
   {
      global $access;

      if (func_num_args() < 2)
         return;

      $name = func_get_arg(0);
      $link = func_get_arg(1);

      $acc = 0;
      for ($i = 2; $i < func_num_args(); $i++)
         $acc |= (1 << func_get_arg($i));

      if ($acc == 0)
         $acc = AUTH::ALL;

      $item = new ITEM($name, $link, $acc);

      array_push($this->menu, $item);

      if (is_string($link))
      {
         $urn = explode('?', $link);
         $path = $urn[0];
         $path_array = explode('/', $path);
         $filename = array_pop($path_array);

         $access->page_add($filename, $acc);
      }
   }

   public function generate()
   {
      global $access;

      echo "<ul " . $this->ul_option . ">\n";

      foreach ($this->menu as $item)
      {
         if (!$access->auth_bit($item->acc))
            continue;

         if (is_object($item->link))
            $url = "#";
         if (is_string($item->link))
            $url = $item->link;
         if (!is_null($item->name))
            echo "<li><a href=\"$url\">$item->name</a>\n";
         if (is_object($item->link))
            $item->link->generate($this->ul_option);

         echo "</li>\n";
      }
      echo "</ul>\n";
   }

}

class MENU
{
   private $top_menu;

   function __construct()
   {
      $menu = new SUBMENU("class=\"dl-menu\"");
      {
         global $db;
         global $access;
         global $whoami;
         global $season;

         $my_pages = new SUBMENU("class=\"dl-submenu\"");
         $menu->add("Mine sider", $my_pages);

         $my_pages->add("Mine prosjekter", "participant_1x.php", AUTH::MYPRJ);
         $my_pages->add("Min spilleplan", "myplan.php", AUTH::MYPLAN);
         $my_pages->add("Min regi", "myDirection.php", AUTH::MYDIR);
//         $my_pages->add("Mine tilbakemeldinger", "feedback.php", AUTH::FEEDBACK);
         $my_pages->add("Mine personopplysninger", "personEdit.php" , AUTH::PERS, AUTH::MEMB_RW);
      }
      {
         $direction = new SUBMENU("class=\"dl-submenu\"");
         $menu->add("Regi", $direction, AUTH::BOARD_RO);
         $direction->add("Ressurser", "dirResources.php", AUTH::BOARD_RO);
         $direction->add("Turnus", "dirShift.php", AUTH::BOARD_RO);
         $direction->add("Prosjekt", "dirProject.php", AUTH::BOARD_RO);
         $direction->add("Regiplan", "dirPlan.php", AUTH::BOARD_RO);
      }
      {
         global $prj_name;

         $admin = new SUBMENU("class=\"dl-submenu\"");
         $menu->add("Admin", $admin);
         $admin->add("Medlemsliste", "person.php?f_status[]=$db->per_stat_member&f_status[]=$db->per_stat_eng", AUTH::MEMB_RO);
         $admin->add("Spilleplan", "plan.php", AUTH::PLAN_RO);
         {
            $groups = new SUBMENU("class=\"dl-submenu\"");
            $admin->add("Tilgang/grupper", $groups, AUTH::BOARD_RO);
            $groups->add("Grupper", "groups.php", AUTH::BOARD_RO);
            $groups->add("Instrumenter", "instruments.php", AUTH::BOARD_RO);
            $groups->add("Tilgang", "access.php", AUTH::BOARD_RO);
            $groups->add("Tilgangsgrupper", "view.php", AUTH::BOARD_RO);
         }
         $admin->add("Repertoar", "repository.php", AUTH::REP_RO, AUTH::REP_RO_LIM);
         {
            $project = new SUBMENU("class=\"dl-submenu\"");
            $admin->add("Prosjekt", $project, AUTH::BOARD_RO);
            $project->add("Prosjekter", "project.php", AUTH::BOARD_RO);
//            $project->add("Tilbakemeldingstekst", "feedbackProj.php", AUTH::FEEDBACK_R);
//            $project->add("Tilbakemeldinger", "feedbackList.php", AUTH::FEEDBACK_R);
            $project->add("Lokaler", "location.php", AUTH::BOARD_RO);
         }
         $admin->add("Ressurser", "participant_xx.php", AUTH::RES);
         $admin->add("Permisjoner", "leave.php", AUTH::LEAVE_RO);
         $admin->add("Dokumenter", "document.php?path=common", AUTH::DOC_RO);
         $admin->add("Kontingent", "contingent.php?f_status[]=$db->per_stat_member", AUTH::CONT_RO);
         $admin->add("Styrefunksjoner", "board.php");
         $admin->add("Konserter", "concert.php", AUTH::BOARD_RO, AUTH::CONS);
      }
      {
         $projects = new SUBMENU("class=\"dl-submenu\"");
         $menu->add("Prosjekter", $projects);

         if ($access->auth(AUTH::PRJM))
         {
            $q = "select id, name, semester, year, orchestration, docs_avail, deadline, "
                    . "project.status as status "
                    . "from project "
                    . "where (status = $db->prj_stat_real ";
            if ($access->auth(AUTH::PRJ_RO))
               $q .= "or status = $db->prj_stat_draft "
                    . "or status = $db->prj_stat_canceled ";
            $q .= "or status = $db->prj_stat_tentative) "
                    . "and year = " . $season->year() . " "
                    . "and semester = '" . $season->semester() . "' "
                    . "order by year,semester DESC";
         }
         else
         {
            $q = "select project.id as id, project.name as name, semester, "
                    . "year, orchestration, docs_avail, deadline, "
                    . "project.status as status "
                    . "from project, participant, person "
                    . "where project.id = participant.id_project "
                    . "and participant.id_person = person.id "
                    . "and person.id = " . $whoami->id() . " "
                    . "and participant.stat_inv = $db->par_stat_yes "
                    . "and year = " . $season->year() . " "
                    . "and semester = '" . $season->semester() . "' "
                    . "order by year,semester DESC";
         }
         $s = $db->query($q);

         foreach ($s as $e)
         {
            $pid = $e['id'];

            $project = new SUBMENU("class=\"dl-submenu\"");
            $item = $e['name'] . " (" . $e['semester'] . " " . $e['year'] . ")";
            if ($e['status'] == $db->prj_stat_canceled)
               $item .= " (Kansellert)";
            $projects->add($item, $project);
            $project->add("Prosjektinfo", "prjInfo.php?id=$pid");
            $project->add("Beskjeder", "pevent.php?id_project=$pid");
            if ($e['orchestration'] != $db->prj_type_social)
            {
               $project->add("Gruppeoppsett", "seating.php?id_project=$pid");
//             $project->add("Tilbakemelding", "feedbackReg.php?id_project=$pid");
               $project->add("Program", "repository.php?id_project=$pid", AUTH::REP);
            }
            $project->add("Musikere", "person.php?f_project[]=$pid");
            $project->add("Regikomité", "direction.php?id_project=$pid", AUTH::DIR_RO);
            $project->add("Fravær", "absence.php?id_project=$pid", AUTH::ABS_RO);
            $project->add("Prosjektressurser", "participant_x1.php?id=$pid", AUTH::RES);
            if (($e['docs_avail'] & (1 << $db->prj_docs_avail_sheet)) || $access->auth(AUTH::PRJDOC))
               $project->add("Noter", "document.php?path=project/$pid/sheet");
            if ($e['docs_avail'] & (1 << $db->prj_docs_avail_rec) || $access->auth(AUTH::PRJDOC))
               $project->add("Innspilling", "document.php?path=project/$pid/rec");
            if ($e['docs_avail'] & (1 << $db->prj_docs_avail_doc) || $access->auth(AUTH::PRJDOC))
               $project->add("Dokumenter", "document.php?path=project/$pid/doc");
            if (time() < $e['deadline'] && $e['status'] == $db->prj_stat_real)
               $project->add(($e['orchestration'] == $db->prj_type_tutti) ? "Permisjonssøknad" : "Registrering", "participant_11.php?id_project=$pid", AUTH::RES_SELF);
            $c = $db->query("select id from concert where id_project=$pid");
            if ($c->rowCount() > 0)
               $project->add("Konsertreklame", "calendar.php?id_project=$pid");
         }
      }

      $menu->add("Hva skjer?", "event.php", AUTH::PRJM);
      $menu->add("Om $prj_name", "about.php");

      // Pages in use, but not linked to the menu system
      $menu->add(null, "participant_xx.php", AUTH::BOARD_RO);
      $menu->add(null, "participant_x1.php", AUTH::RES);
      $menu->add(null, "participant_11.php", AUTH::RES_SELF);
      $menu->add(null, "contingentEdit.php", AUTH::CONT_RW);
      $menu->add(null, "absenceEdit.php", AUTH::ABS_RW);
      $menu->add(null, "index.php");
      $menu->add(null, "prjInfo.php");
      $menu->add(null, "seating_pdf.php", AUTH::SEAT);

      $this->top_menu = $menu;
   }

   public function generate()
   {
      $this->top_menu->generate();
   }

   private function select_person($selected)
   {
      global $db;

      $q = "SELECT uid, firstname, lastname, instrument "
              . "FROM person, instruments "
              . "where (person.status = $db->per_stat_member "
              . "or person.status = $db->per_stat_standin "
              . "or person.status = $db->per_stat_hired "
              . "or person.status = $db->per_stat_eng) "
              . "and not person.uid = '' "
              . "and person.id_instruments = instruments.id "
              . "order by list_order, lastname, firstname";
      $s = $db->query($q);

      foreach ($s as $e)
      {
         echo "<option value=\"" . $e['uid'] . "\"";
         if ($e['uid'] == $selected)
            echo " selected";
         echo ">" . $e['firstname'] . " " . $e['lastname'] . " (" . $e['instrument'] . ")\n";
      }
   }

   private function url($url = '')
   {
      if (strlen($url) > 0)
         $url = "?$url";
      
      foreach ($_GET as $key => $value)
         if (is_string($value))
            $url .= ((strlen($url) > 0) ? '&' : '?') . $key . '=' . $value;

      return $url;
   }

   public function whoami()
   {
      global $whoami;
      global $php_self;
      global $access;

      if ($access->auth(AUTH::SU))
      {
         echo "<form action=\"$php_self" . $this->url() . "\" method=post>
         <select name=set_eff_uid onChange=\"set_cookie('uid', this.form.set_eff_uid.value); submit();\" title=\"Bytt bruker...\">\n";
         $this->select_person($whoami->uid());
         echo "</select>\n</form>\n";
      }
      else
      {
         echo $whoami->name();
      }
   }

   public function season()
   {
      global $season;
      global $php_self;

      $sem = $season->semester();
      $year = $season->year();

      if ($sem == 'V')
      {
         $op_sem = 'H';
         $next_year = $year;
         $last_year = $year - 1;
      }
      else
      {
         $op_sem = 'V';
         $next_year = $year + 1;
         $last_year = $year;
      }

      echo "<table id=no_border><tr>";
      echo "<td><a href=\"$php_self" . $this->url('season=adjust') . "\" title=\"forrige semester...\" onClick=\"set_cookie('_semester', '{$op_sem}.{$last_year}'); return true;\"><img src=\"images/left.gif\" height=20 border=0 ></a></td>\n";
      echo "<td>$sem$year</td>\n";
      echo "<td><a href=\"$php_self" . $this->url('season=adjust') . "\" title=\"neste semester...\" onClick=\"set_cookie('_semester', '{$op_sem}.{$next_year}'); return true;\"><img src=\"images/right.gif\" height=20 border=0></a></td>\n";
      echo "</tr></table>";
   }

}

$menu = new MENU();
