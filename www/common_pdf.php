<?php

require 'fpdf/fpdf181/fpdf.php';

class PDF_util extends FPDF
{

   private $fontSize_ = 10;
   private $fontFamily_ = 'Times';
   private $fontStyle = '';
   private $col;
   private $line;

   // Page header
   function Header()
   {
      // Logo
      $this->Image('images/osologo.png', 10, 6, 10);
      // Arial bold 15
      $this->SetFont('Arial', '', 10);
      // Move to the right
      $this->Cell(80);
      // Title
      $this->Cell(30, 10, 'Oslo Symfoniorkester ' . strftime('%a %e.%b %Y'), 0, 0, 'C');
      // Line break
      $this->Ln(20);
   }

   // Page footer
   function Footer()
   {
      // Position at 1.5 cm from bottom
      $this->SetY(-15);
      // Arial italic 8
      $this->SetFont('Arial', 'I', 8);
      // Page number
      $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
   }

   function sconv($str)
   {
      return iconv('UTF-8', 'windows-1252', $str);
   }

   function setFont_($family, $style, $size)
   {
      $this->fontSize_ = $size;
      $this->fontStyle_ = $style;
      $this->fontFamily_ = $family;

      $this->setFont($family, $style, $size);
   }

   function setFontSize_($size)
   {
      $this->fontSize_ = $size;
      $this->setFontSize($size);
   }

   function header1($str)
   {
      $this->SetFont($this->fontFamily_, 'B', 20);
      $this->Cell(0, 0, $this->sconv($str));
      $this->SetFont($this->fontFamily_, $this->fontStyle_, $this->fontSize_);
      $this->Ln(5);
   }

   function bold($str)
   {
      $this->SetFont($this->fontFamily_, 'B', $this->fontSize_);
      $this->Cell(0, 0, $this->sconv($str));
      $this->SetFont($this->fontFamily_, $this->fontStyle_, $this->fontSize_);
   }

   function colStart()
   {
      $this->line = $this->GetY();
      $this->col = $this->lMargin;
   }

   function colSet($x)
   {
      $this->col = $x;
   }

   function colNext($x = 25)
   {
      $this->col += $x;
      $this->SetXY($this->col, $this->line);
   }

   function colLn($h = null)
   {
      if ($h == null)
         $h = $this->lasth;
      $this->SetXY($this->col, $this->GetY() + $h);
   }

}
