<?php

class TABLE
{

   const UNDEF = 0;
   const COMPLETED = 1;
   const TABLE = 2;
   const HEAD = 3;
   const BODY = 4;

   private $state = self::UNDEF;
   private $in_tr = false;
   private $opt_td = null;
   private $str = '';

   public function __construct($opt = null)
   {
      $this->str .= "<table";
      if (!is_null($opt))
         $this->str .= " $opt";
      $this->str .= ">\n";
      $this->state = self::TABLE;
   }

   public function __destruct()
   {
      $this->terminate();
      if ($this->state == self::COMPLETED)
         echo $this->str;
   }

   private function terminate()
   {
      if ($this->state >= self::TABLE)
      {
         $this->tr_end();
         $this->str .= "</table>\n";
         $this->state = self::COMPLETED;
      }
   }

   public function res()
   {
      $this->terminate();
      $this->state = self::UNDEF;
      return $this->str;
   }

   private function thead()
   {
      $this->str .= "  <thead>\n";
      $this->state = self::HEAD;
   }

   private function tbody()
   {
      $this->str .= "  <tbody>\n";
      $this->state = self::BODY;
   }

   private function tr_end()
   {
      if ($this->in_tr)
      {
         $this->str .= "  </tr>\n";
         $this->in_tr = false;
      }
   }

   public function tr($opt = null, $state = null)
   {
      if (!is_null($state))
         $this->state = $state;

      $this->tr_end();

      if ($this->state == self::HEAD)
         $this->tbody();
      if ($this->state == self::TABLE)
         $this->thead();

      $this->str .= "  <tr";
      if (!is_null($opt))
         $this->str .= " $opt";
      $this->str .= ">\n";

      $this->in_tr = true;
   }

   public function th($cell = '', $opt = null)
   {
      if ($this->state == self::TABLE)
         $this->tr();

      $this->str .= "    <th";
      if (!is_null($opt))
         $this->str .= " $opt";
      $this->str .= ">$cell</th>\n";
   }

   public function td_opt($opt)
   {
      $this->opt_td = $opt;
   }

   public function td($cell = '', $opt = null)
   {
      if ($this->state == self::TABLE)
      {
         $this->state = self::HEAD;
         $this->tr();
      }

      $this->str .= "    <td";
      if (!is_null($this->opt_td))
         $this->str .= " $this->opt_td";
      if (!is_null($opt))
         $this->str .= " $opt";
      $this->str .= ">$cell</td>\n";
   }

}
