<?php

require_once 'request.php';
require_once 'conf/opendb.php';

require 'common_pdf.php';

class PDF extends PDF_util
{

   function info($id_project)
   {
      global $db;

      $query = "select name, orchestration, semester, year, "
              . "status, info "
              . "from project "
              . "where id = $id_project";
      $stmt = $db->query($query);
      $prj = $stmt->fetch(PDO::FETCH_ASSOC);

      $this->header1($prj['name']." ".$prj['semester']."-".$prj['year']);
      $this->MultiCell(0, 5, $this->sconv($prj['info']));
      $this->Ln();
   }

   function program($id_project)
   {
      global $db;

      $query = "SELECT title, work, firstname, lastname, music.comment as comment,"
              . " repository.comment as r_comment"
              . " from repository, music"
              . " where repository.id = music.id_repository"
              . " and music.status = $db->mus_stat_yes"
              . " and music.id_project = ".request('id')." "
              . " order by lastname, firstname, work";

      $stmt = $db->query($query);

      $this->header1("Repertoar");

      foreach ($stmt as $row)
      {
         $this->Cell(50, 5, $this->sconv($row['firstname']." ".$row['lastname'].":"));
         $this->Cell(50, 5, $this->sconv($row['title']));
         if (strlen($row['work']) > 0)
            $this->Cell(60, 5, $this->sconv("fra ".$row['work']));
         $this->Cell(50, 5, $this->sconv($row['r_comment']));
         $this->Cell(50, 5, $this->sconv($row['comment']));
         $this->Ln();
      }
      $this->Ln();
   }

   function plan($id_project)
   {
      global $db;
   
      $this->header1("Prøveplan");

      $w = array(30, 30, 30, 50);
      $h = array("Dato", "Prøvetid", "Lokale", "Merknad");

      $this->setFillColor(0xA6, 0xCA, 0xF0);
      for ($i = 0; $i < count($w); $i++)
         $this->Cell($w[$i], 5, $this->sconv($h[$i]), 0, 0, "L", true);
      $this->Ln();

      $query = "SELECT date, time, " .
              "plan.location as location, location.name as lname, " .
              "location.url as url, " .
              "plan.comment as comment " .
              "FROM project, plan, location " .
              "where id_location = location.id " .
              "and id_project = project.id " .
              "and plan.id_project = $id_project " .
              "and plan.event_type = $db->plan_evt_rehearsal " .
              "order by date,tsort,time";

      $stmt = $db->query($query);

      foreach ($stmt as $row)
      {
         $this->Cell($w[0], 5, $this->sconv(strftime('%a %e.%b', $row['date'])));
         $this->Cell($w[1], 5, $row['time']);
         $this->Cell($w[2], 5, $this->sconv($row['lname']));
         $this->Cell($w[3], 5, $this->sconv($row['comment']));
         $this->Ln();
      }
      $this->Ln();
   }

   function participants($id_project)
   {
      global $db;
      global $par_stat_yes;

      $query = "select firstname, lastname, instrument, stat_final"
              . " from person, instruments, participant"
              . " where participant.id_project=$id_project"
              . " and participant.id_instruments = instruments.id"
              . " and participant.id_person = person.id"
              . " and participant.stat_final = $db->par_stat_yes"
              . " order by instruments.list_order, participant.position";

      $stmt = $db->query($query);

      $this->header1("Musikere");

      $this->colStart();
      $last_instrument = '';
      
      foreach ($stmt as $e)
      {
         if ($last_instrument != $e['instrument'])
         {
            $this->colLn();            
            if ($this->GetY() > $this->GetPageHeight() - 30)
               $this->colNext(35);
            $this->bold($e['instrument'].":");
            $this->colLn(2);
         }
         $name = $e['firstname']." ".$e['lastname'];
         $this->Cell(0, 4, $this->sconv($name));
         $this->colLn();
         if ($this->GetY() > $this->GetPageHeight())
            $this->colNext(35);
         $last_instrument = $e['instrument'];
      }
   }

}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->SetFont_('Times', '', 10);
$pdf->AddPage();
$pdf->info(request('id'));
$pdf->program(request('id'));
$pdf->plan(request('id'));
$pdf->participants(request('id'));

$pdf->Output();
