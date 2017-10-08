<?php

require_once 'request.php';
require_once 'conf/opendb.php';
require_once 'auth.php';

require 'common_pdf.php';
require 'person_query.php';

class PDF extends PDF_util
{

   public function plan()
   {
      global $db;
      global $access;
      global $season;

      $this->SetDrawColor(200, 200, 200);
      $this->SetLineWidth(1);
      //     $this->Line(10, 20, 200, 20);

      $this->SetTextColor(0, 0, 200);
      $this->setFontSize(30);
      $season_text = "Spilleplan " . $season->semester(2) . " " . $season->year();
      $this->Cell(60, 0, $this->sconv($season_text));
      $this->SetFontSize(20);
      $this->SetTextColor(0, 0, 0);

      $this->Line(10, 38, 200, 38);

      $this->setFontSize(12);
      $this->Ln();

      $tab = array(30, 25, 35, 35, 0);
      $col = array("Dato", "Prøvetid", "Lokale", "Prosjekt", "Merknad");

      $this->SetTextColor(0, 0, 200);
      for ($i = 0; $i < count($col); $i++)
         $this->Cell($tab[$i], 22, $this->sconv($col[$i]));
      $this->Ln();

      $query = "select date, time, location.name as lname, " .
              "project.name as pname, " .
              "plan.comment as comment, orchestration, " .
              "project.status as status " .
              "FROM project, plan, location " .
              "where id_location = location.id " .
              "and id_project = project.id " .
              "and plan.event_type = $db->plan_evt_rehearsal " .
              "and project.year = ".$season->year()." " .
              "and project.semester = '".$season->semester()."' " .
              "and (project.status = $db->prj_stat_real ";
      if ($access->auth(AUTH::PRJ_RO))
         $query .= "or project.status = $db->prj_stat_draft ";
      $query .= "or project.status = $db->prj_stat_tentative "
              . "or project.status = $db->prj_stat_internal) "
              . "order by date,tsort,time";

      $stmt = $db->query($query);

      $this->SetTextColor(0, 0, 0);
      $this->setFontSize(12);
      $hight = 3;
      
      $last_date = '';
      $last_time = '';

      foreach ($stmt as $e)
      {
         $tcolor = 0;
         if ($e['status'] == $db->prj_stat_tentative)
            $tcolor = 150;
         if ($e['status'] == $db->prj_stat_draft)
            $tcolor = 200;
         $this->SetTextColor($tcolor, $tcolor, $tcolor);

         $idx = 0;
         
         $date = ($e['date'] != $last_date) ? strftime('%a %e.%b', $e['date']) : '';
         $this->Cell($tab[$idx++], $hight, $this->sconv($date));
         $time = ($e['date'] != $last_date || $e['time'] != $last_time) ? $e['time'] : '';
         $this->Cell($tab[$idx++], $hight, $e['time']);

         $last_date = $e['date'];
         $last_time = $e['time'];

         $this->Cell($tab[$idx++], $hight, $this->sconv($e['lname']));
         $project = $this->sconv($e['pname']);
         if ($e['orchestration'] == $db->prj_orch_reduced)
            $project .= '*';
         $this->Cell($tab[$idx++], $hight, $project);
         $this->MultiCell($tab[$idx++], $hight, $this->sconv($e['comment']));
         $this->Ln(3);
      }

      $this->SetFont('Arial', 'I', 8);
      $this->Cell(0, 30, "* : redusert besetning", 0, 0, 'LB');
   }
   
   public function repertoire()
   {
      global $db;
      global $access;
      global $season;

      $this->SetDrawColor(200, 200, 200);
      $this->SetLineWidth(1);
      //     $this->Line(10, 20, 200, 20);

      $this->SetTextColor(0, 0, 200);
      $this->setFontSize(30);
      $semester_text = "Repertoar " . $season->semester(2) . " " . $season->year();
      $this->Cell(60, 0, $this->sconv($semester_text));

      $this->Line(10, 38, 200, 38);
 
      $query = "select id, name, info, status, orchestration "
              . "from project "
              . "where project.year = ".$season->year()." "
              . "and project.semester = '".$season->semester()."' "
              . "and (project.status = $db->prj_stat_real ";
      if ($access->auth(AUTH::PRJ_RO))
         $query .= "or project.status = $db->prj_stat_draft ";
      $query .= "or project.status = $db->prj_stat_tentative) "
              . "order by project.id";

      $stmt = $db->query($query);
      
      $this->SetY(40);
      $this->SetLineWidth(0.3);
      $this->SetDrawColor(0, 0, 0);
      $this->SetTextColor(0, 0, 0);
      $this->setFontSize(10);

      foreach ($stmt as $prj)
      {
         $tcolor = 0;
         if ($prj['status'] == $db->prj_stat_tentative)
            $tcolor = 150;
         if ($prj['status'] == $db->prj_stat_draft)
            $tcolor = 200;
         $this->SetTextColor($tcolor, $tcolor, $tcolor);
         
         $q = "select "
              . "concert.ts as ts, "
              . "location.name as lname "
              . "from concert, location "
              . "where concert.id_project = ".$prj['id']." "
              . "and concert.id_location = location.id "
              . "order by concert.ts";

         $s = $db->query($q);
         
         $h2 = "";
         if ($prj['orchestration'] == $db->prj_orch_reduced)
            $h2 = "* ";
         $h2 .= $prj['name']." ";
         foreach ($s as $e)
           $h2 .= ', ' . $e['lname'] . ' ' . strftime('%A %e. %B', $e['ts']);
         $this->Cell(100, 5, $this->sconv($h2), "B");
 
         $this->Ln(7);

         $q = "select "
              . "title, firstname, lastname, work, "
              . "repository.comment as r_comment, "
              . "music.comment as m_comment "
              . "from music, repository "
              . "where music.id_project = ".$prj['id']." "
              . "and music.id_repository = repository.id "
              . "and music.status = $db->mus_stat_yes";
         
         $s = $db->query($q);
         
         foreach ($s as $e)
         {
            $this->Cell(50, 5, $this->sconv($e['lastname'].", ".$e['firstname']));
            $this->Cell(50, 5, $this->sconv($e['title'].", ".$e['work']." ".$e['r_comment']));
            $this->Ln();
            if (strlen($e['m_comment']) > 0)
            {
               $this->Cell(50, 5);
               $this->Cell(50, 5, $this->sconv($e['m_comment']));
               $this->Ln();
            }
         }
         
         $this->Ln(4);
         $this->MultiCell(100, 5, $this->sconv($prj['info']));
         $this->Ln(7);
      }

      $this->SetFont('Arial', 'I', 8);
      $this->Cell(0, 30, "* : redusert besetning", 0, 0, 'LB');
   }

}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->SetFont_('Times', '', 10);
setlocale(LC_TIME, "no_NO.UTF-8");
$pdf->AddPage();

$pdf->plan();
$pdf->AddPage();
$pdf->repertoire();

$pdf->Output();
