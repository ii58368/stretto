<?php

require_once 'request.php';
require_once 'conf/opendb.php';

require 'common_pdf.php';

class PDF extends PDF_util
{

   public function content()
   {
      global $db;

      $id_groups = request('id_groups');
      $id_project = request('id_project');
      $template = request('template');
      
      $this->SetDrawColor(200, 200, 200);
      $this->SetLineWidth(1);
      //     $this->Line(10, 20, 200, 20);

      $query = "select name from groups where id = $id_groups";
      $stmt = $db->query($query);
      $e = $stmt->fetch(PDO::FETCH_ASSOC);

      $this->SetTextColor(0, 0, 200);
      $this->setFontSize(30);
      $text = "Gruppeoppsett ".$e['name'];
      $this->Cell(60, 20, $this->sconv($text));
      $this->SetFontSize(20);
      $this->SetTextColor(0, 0, 0);
      $this->Ln();

      $query = "select name, year, semester from project where id = $id_project";
      $stmt = $db->query($query);
      $e = $stmt->fetch(PDO::FETCH_ASSOC);

      $text = $e['name']." (".$e['semester']."-".$e['year'].")";
      $this->Cell(60, 0, $this->sconv($text));

      $this->Ln();
     
      $referer = $_SERVER['HTTP_REFERER'];
      $path = substr($referer, 0, strrpos($referer, '/'));
      $this->Image("$path/map.php?id_groups=$id_groups&id_project=$id_project&template=$template",10,60,0,0,'JPEG');
   }

}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->SetFont_('Times', '', 10);
$pdf->AddPage();

$pdf->content();

$pdf->Output();
