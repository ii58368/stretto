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
      $this->Cell(60, 0, "Adresseliste");
      $this->SetFontSize(20);
      $this->SetTextColor(0, 0, 0);
      if (!is_null($_REQUEST[f_project]))
      {
         $query = "select name, semester, year from project where ";
         foreach ($_REQUEST[f_project] as $f_project)
            $query .= "project.id = $f_project or ";
         $query .= "false order by year DESC,semester";
         $stmt = $db->query($query);

         foreach ($stmt as $e)
            $this->Cell(50, 0, "$e[name] ($e[semester]$e[year]) ");
      }


      $this->Line(10, 38, 200, 38);

      $this->setFontSize(10);
      $this->Ln();

      $tab = array(25, 25, 30, 20, 18, 18, 50);
      $col = array("Etternavn", "Fornavn", "Adresse", "Poststed", "Tlf1", "Tlf2", "Epost");

      $this->SetTextColor(0, 0, 200);
      for ($i = 0; $i < count($col); $i++)
         $this->Cell($tab[$i], 22, $col[$i]);

      $this->Cell(0, 15);
      $this->Ln();

      $query = person_query();

      $stmt = $db->query($query);

      $this->SetTextColor(0, 0, 0);
      $this->SetLineWidth(0.3);
      $this->Cell(0, 1);

      foreach ($stmt as $e)
      {
         if ($e[instrument] != $last_instrument)
         {
            $this->SetFont('Arial', 'B', 8);  // Set bold
            $this->Ln();
            $this->Cell(0, 4, $this->sconv($e[instrument]));
            $this->Ln();
            $this->Line(10, $this->GetY(), 200, $this->GetY());
            $this->SetFont('');  // Removed bold
            $last_instrument = $e[instrument];
         }
         $this->setFontSize(8);

         $idx = 0;
         $hight = 4;
         $this->Cell($tab[$idx++], $hight, $this->sconv($e[lastname]));
         $this->Cell($tab[$idx++], $hight, $this->sconv("$e[firstname] $e[middlename]"));
         $this->Cell($tab[$idx++], $hight, $this->sconv($e[address]));
         $this->Cell($tab[$idx++], $hight, $this->sconv(sprintf("%04d", $e[postcode]) . " $e[city]"));
         $this->Cell($tab[$idx++], $hight, $e[phone1]);
         $this->Cell($tab[$idx++], $hight, $e[phone2]);
         $this->Cell($tab[$idx++], $hight, $e[email]);
         $this->Ln();
      }
   }

}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->SetFont_('Times', '', 10);
$pdf->AddPage();

$pdf->heading();

$pdf->Output();
