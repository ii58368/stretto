<?php

require_once 'const.php';
require_once 'request.php';
require_once 'conf/opendb.php';

require 'common_pdf.php';
require 'person_query.php';

class PDF extends PDF_util
{

   function heading()
   {
      global $db;

      $this->SetDrawColor(200, 200, 200);
      $this->SetLineWidth(1);
      //     $this->Line(10, 20, 200, 20);

      $this->SetTextColor(0, 0, 200);
      $this->setFontSize(30);
      $this->Cell(60, 0, $this->sconv("Prøveplan $_REQUEST[semester]-$_REQUEST[year]"));
      $this->SetFontSize(20);
      $this->SetTextColor(0, 0, 0);

      $this->Line(10, 38, 200, 38);

      $this->setFontSize(12);
      $this->Ln();

      $tab = array(30, 25, 35, 35, 50);
      $col = array("Dato", "Prøvetid", "Lokale", "Prosjekt", "Merknad");

      $this->SetTextColor(0, 0, 200);
      for ($i = 0; $i < count($col); $i++)
         $this->Cell($tab[$i], 22, $this->sconv($col[$i]));
      $this->Ln();

      $query = "select date, time, location.name as lname, " .
        "project.name as pname, " .
        "plan.comment as comment, orchestration " .
        "FROM project, plan, location " .
        "where id_location = location.id " .
        "and id_project = project.id " .
        "and plan.event_type = $db->plan_evt_rehearsal " .
        "and project.year = $_REQUEST[year] " .
        "and project.semester = '$_REQUEST[semester]' " .
        "order by date,tsort,time";

      $stmt = $db->query($query);

      $this->SetTextColor(0, 0, 0);
      $this->setFontSize(12);
      $hight = 5;

      foreach ($stmt as $e)
      {
         $idx = 0;
         $this->Cell($tab[$idx++], $hight, date('D j.M', $e[date]));
         $this->Cell($tab[$idx++], $hight, $e[time]);
         $this->Cell($tab[$idx++], $hight, $this->sconv($e[lname]));
         $project = $this->sconv($e[pname]);
         if ($e[orchestration] == $db->prj_orch_reduced)
            $project .= '*';
         $this->Cell($tab[$idx++], $hight, $project);
         $this->Cell($tab[$idx++], $hight, $this->sconv($e[comment]));
         $this->Ln();
      }
   }

}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->SetFont_('Times', '', 10);
setlocale(LC_TIME, "no_NO.UTF-8");
$pdf->AddPage();

$pdf->heading();

$pdf->Output();
