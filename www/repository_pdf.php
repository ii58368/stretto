<?php

require_once 'request.php';
require_once 'conf/opendb.php';

require 'common_pdf.php';

class PDF extends PDF_util
{

   public function composer_list()
   {
      global $db;

      $this->SetDrawColor(200, 200, 200);
      $this->SetLineWidth(1);
      //     $this->Line(10, 20, 200, 20);

      $this->SetTextColor(0, 0, 200);
      $this->setFontSize(30);
      $this->Cell(60, 0, "Notearkiv for Oslo Symfoniorkester");
      $this->SetFontSize(20);
      $this->SetTextColor(0, 0, 0);

      $query = "select * from repository "
              . "where sheet_type = " . $db->rep_stype_local . " "
              . "order by lastname, firstname, work";
      $stmt = $db->query($query);

      $this->Line(10, 38, 200, 38);

      $this->setFontSize(10);
      $this->Ln();

      $tab = array(50, 60, 60, 7);
      $col = array("Tittel", "verk", "Kommentar", "Arkivref");

      $this->SetTextColor(0, 0, 200);
      for ($i = 0; $i < count($col); $i++)
         $this->Cell($tab[$i], 22, $col[$i]);

      $this->Cell(0, 15);
      $this->Ln();

      $this->SetTextColor(0, 0, 0);
      $this->SetLineWidth(0.3);
      $this->Cell(0, 1);

      $last_composer = ''; 
      
      foreach ($stmt as $e)
      {
         $composer = $e['lastname'].", ".$e['firstname'];
         
         if ($composer != $last_composer)
         {
            $this->SetFont('Arial', 'B', 8);  // Set bold
            $this->Ln();
            $this->Cell(0, 4, $this->sconv($composer));
            $this->Ln();
            $this->Line(10, $this->GetY(), 200, $this->GetY());
            $this->SetFont('');  // Removed bold
            $last_composer = $composer;
         }
         $this->setFontSize(8);

         $idx = 0;
         $hight = 4;
         $this->Cell($tab[$idx++], $hight, $this->sconv($e['title']));
         $this->Cell($tab[$idx++], $hight, $this->sconv($e['work']));
         $this->Cell($tab[$idx++], $hight, $this->sconv($e['comment']));
         $this->Cell($tab[$idx++], $hight, $e['tag']);
         $this->Ln();
      }
   }

   public function tag_list()
   {
      global $db;

      $this->SetDrawColor(200, 200, 200);
      $this->SetLineWidth(1);
      //     $this->Line(10, 20, 200, 20);

      $this->SetTextColor(0, 0, 200);
      $this->setFontSize(30);
      $this->Cell(60, 0, "Notearkiv for Oslo Symfoniorkester");
      $this->SetFontSize(20);
      $this->SetTextColor(0, 0, 0);

      $query = "select * from repository "
              . "where sheet_type = " . $db->rep_stype_local . " "
              . "order by tag";
      $stmt = $db->query($query);

      $this->Line(10, 38, 200, 38);

      $this->setFontSize(10);
      $this->Ln();

      $tab = array(7, 40, 50, 50, 40);
      $col = array("Nr", "Komponist", "Tittel", "verk", "Kommentar");

      $this->SetTextColor(0, 0, 200);
      for ($i = 0; $i < count($col); $i++)
         $this->Cell($tab[$i], 22, $col[$i]);

      $this->Cell(0, 15);
      $this->Ln();

      $this->SetTextColor(0, 0, 0);
      $this->SetLineWidth(0.3);
      $this->Cell(0, 1);
      $this->setFontSize(8);
      $this->Ln();

      foreach ($stmt as $e)
      {
         $composer = $e['lastname'].", ".$e['firstname'];
         
         $idx = 0;
         $hight = 4;
         $this->Cell($tab[$idx++], $hight, $e['tag']);
         $this->Cell($tab[$idx++], $hight, $this->sconv($composer));
         $this->Cell($tab[$idx++], $hight, $this->sconv($e['title']));
         $this->Cell($tab[$idx++], $hight, $this->sconv($e['work']));
         $this->Cell($tab[$idx++], $hight, $this->sconv($e['comment']));
         $this->Ln();
      }
   }

}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->SetFont_('Times', '', 10);
$pdf->AddPage();

$pdf->composer_list();
$pdf->AddPage();
$pdf->tag_list();

$pdf->Output();
