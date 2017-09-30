<?php

class SEASON
{

   private $semester;
   private $year;

   function __construct()
   {
      $this->reset();

      if (isset($_COOKIE['semester']))
         list($this->semester, $this->year) = explode('.', $_COOKIE['semester']);
   }

   public function semester($opt = 0)
   {
      if ($opt == 1)
         return ($this->semester == 'V') ? 'Vår' : 'Høst';
      if ($opt == 2)
         return ($this->semester == 'V') ? 'Våren' : 'Høsten';
      
      return $this->semester;
   }

   public function year()
   {
      return $this->year;
   }

   public function reset()
   {
      $this->semester = (date('n') <= 6) ? 'V' : 'H';
      $this->year = date('Y');
   }

}
