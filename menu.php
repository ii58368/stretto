<?php

require_once 'conf/opendb.php';
require_once 'request.php';
require_once 'conf/auth.php';

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
            $item->link->generate($ul_option);

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

         $my_pages = new SUBMENU("class=\"dl-submenu\"");
         $menu->add("Mine sider", $my_pages);

         $s = $db->query("select id from person where uid='$whoami'");
         $pers = $s->fetch(PDO::FETCH_ASSOC);

         $my_pages->add("Mine prosjekter", "participant_1x.php?id=$pers[id]", AUTH::MYPRJ);
         $my_pages->add("Min prøveplan", "plan.php?id_person=$pers[id]", AUTH::MYPLAN);
         $my_pages->add("Min regi", "myDirection.php?id_person=$pers[id]", AUTH::MYDIR);
         $my_pages->add("Mine personopplysninger", "personEdit.php?_no=$pers[id]", AUTH::PERS, AUTH::MEMB_RW);
      }
      {
         $direction = new SUBMENU("class=\"dl-submenu\"");
         $menu->add("Regi", $direction, AUTH::BOARD_RO);
         $direction->add("Ressurser", "dirResources.php", AUTH::BOARD_RO);
         $direction->add("Turnus", "dirShift.php", AUTH::BOARD_RO);
         $direction->add("Prosjekt", "dirProject.php", AUTH::BOARD_RO);
         $direction->add("Regiplan", "dirPlan.php?id_project=%", AUTH::BOARD_RO);
      }
      {
         $admin = new SUBMENU("class=\"dl-submenu\"");
         $menu->add("Admin", $admin);
         $admin->add("Medlemsliste", "person.php", AUTH::MEMB_RO);
         $admin->add("Prøveplan", "plan.php?id_project=%", AUTH::PLAN_RO);
         $admin->add("Grupper", "groups.php", AUTH::BOARD_RO);
         $admin->add("Instrumenter", "instruments.php", AUTH::BOARD_RO);
         $admin->add("Tilgang", "access.php", AUTH::BOARD_RO);
         $admin->add("Tilgangsgrupper", "view.php", AUTH::BOARD_RO);
         $admin->add("Notearkiv", "repository.php", AUTH::BOARD_RO);
         $admin->add("Prosjekter", "project.php", AUTH::BOARD_RO);
         $admin->add("Tilbakemeldinger", "feedback.php", AUTH::BOARD_RO);
         $admin->add("Lokale", "location.php", AUTH::BOARD_RO);
         $admin->add("Ressurser", "participant_xx.php", AUTH::RES);
         $admin->add("Dokumenter", "document.php?path=common", AUTH::DOC_RO);
         $admin->add("Kontingent", "contingent.php", AUTH::CONT_RO);
         $admin->add("Om $prj_name", "about.php");
      }
      {
         $projects = new SUBMENU("class=\"dl-submenu\"");
         $menu->add("Prosjekter", $projects);

         if ($access->auth(AUTH::PRJM))
         {
            $q = "select id, name, semester, year, orchestration "
                    . "from project "
                    . "where (status = $db->prj_stat_public "
                    . "or status = $db->prj_stat_tentative) "
                    . "and year >= " . date("Y") . " "
                    . "order by year,semester DESC";
         } else
         {
            $q = "select project.id as id, project.name as name, semester, year, orchestration "
                    . "from project, participant, person "
                    . "where project.id = participant.id_project "
                    . "and participant.id_person = person.id "
                    . "and person.uid = '$whoami' "
                    . "and participant.stat_final = $db->par_stat_yes "
                    . "and year >= " . date("Y") . " "
                    . "order by year,semester DESC";
         }
         $s = $db->query($q);

         foreach ($s as $e)
         {
            $pid = $e[id];

            $project = new SUBMENU("class=\"dl-submenu\"");
            $projects->add("$e[name] ($e[semester]$e[year])", $project);
            $project->add("Prosjektinfo", "prjInfo.php?id=$pid");
            $project->add("Gruppeoppsett", "seating.php?id_project=$pid");
            $project->add("Program", "program.php?id=$pid");
            $project->add("Musikere", "person.php?id=$pid");
            $project->add("Noter", "document.php?path=project/$pid/sheet");
            $project->add("Innspilling", "document.php?path=project/$pid/rec");
            $project->add("Dokumenter", "document.php?path=project/$pid/doc");
            $project->add("Regikomité", "direction.php?id_project=$pid", AUTH::DIR_RO);
            $project->add(($e[orchestration] == $db->prj_orch_tutti) ? "Permisjonssøknad" : "Påmelding", "participant_11.php?id_project=$pid&id_person=$pers[id]", AUTH::RES_SELF);
            $project->add("Tilbakemelding", "feedback.php?id=$pid");
            $project->add("Fravær", "absence.php?id_project=$pid", AUTH::ABS_RO);
            $project->add("Prosjektressurser", "participant_x1.php?id=$pid", AUTH::RES);
            $project->add("Konsertkalender", "consert.php?id=$pid", AUTH::BOARD_RO);
         }
      }

      $menu->add("Hva skjer?", "event.php", AUTH::PRJM);

      // Pages in use, but not linked to the meu system
      $menu->add(null, "participant_xx.php", AUTH::BOARD_RO);
      $menu->add(null, "participant_x1.php", AUTH::RES);
      $menu->add(null, "participant_11.php", AUTH::RES_SELF);
      $menu->add(null, "contingentEdit.php", AUTH::CONT_RW);
      $menu->add(null, "absenceEdit.php", AUTH::ABS_RW);
      $menu->add(null, "index.php");

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

   public function whoami()
   {
      global $db;
      global $whoami;
      global $php_self;
      global $access;

      if ($access->auth(AUTH::SU))
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

$menu = new MENU();
