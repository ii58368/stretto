<?php

require_once 'conf/opendb.php';

class WHOAMI
{

   private $id = 0;
   private $firstname;
   private $lastname;
   private $uid;
   private $instrument;

   function __construct()
   {
      global $db;

      $uid = $_SERVER[PHP_AUTH_USER];
      if ($_COOKIE['uid'] != null)
         $uid = $_COOKIE['uid'];

      $q = "select firstname, lastname, person.id as id, instrument "
              . "from person, instruments "
              . " where uid = '$uid' "
              . "and person.id_instruments = instruments.id";
      
      $s = $db->query($q);
      $e = $s->fetch(PDO::FETCH_ASSOC);

      $this->id = $e[id];
      $this->uid = $uid;
      $this->firstname = $e[firstname];
      $this->lastname = $e[lastname];
      $this->instrument = $e[instrument];
   }

   public function name()
   {
      return "$this->firstname $this->lastname";
   }

   public function uid()
   {
      return $this->uid;
   }

   public function id()
   {
      return $this->id;
   }

   public function instrument()
   {
      return $this->instrument;
   }

}
