<?php

class SEASON
{

   private $semester;
   private $year;

   function __construct()
   {
      $this->reset();

      if (isset($_COOKIE['_semester']))
         list($this->semester, $this->year) = explode('.', $_COOKIE['_semester']);
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
   
   public function set_year($year)
   {
      $this->year = $year;
   }

   public function set_semester($semester)
   {
      $this->semester = $semester;
   }

   public function ts()
   {
      $f_date = ($this->semester == 'V') ? "1. jan" : "1. jul";
      $t_date = ($this->semester == 'V') ? "30. jun" : "31. dec";

      $f_ts = strtotime("$f_date " . $this->year);
      $t_ts = strtotime("$t_date " . $this->year) + 60*60*24;

      return array($f_ts, $t_ts);
   }
   
   public function isWithin($ts)
   {
      list($f_ts, $t_ts) = $this->ts();
      
      if ($ts < $f_ts)
         return false;
      if ($ts > $t_ts)
         return false;

      return true;
   }

}
