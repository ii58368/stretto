<?php

require_once 'conf/opendb.php';

class WHOAMI
{

   private $id = 0;
   private $firstname;
   private $lastname;
   private $uid = null;
   private $status;
   private $instrument;
   private $real_uid = null;

   function __construct()
   {
      global $db;

      $real_uid = null;
      
      if (isset($_SERVER['REDIRECT_REMOTE_USER']))
         $real_uid = $_SERVER['REDIRECT_REMOTE_USER'];
      if (isset($_SERVER['PHP_AUTH_USER']))
         $real_uid = $_SERVER['PHP_AUTH_USER'];

      if (is_null($real_uid))
         return;
      
      $uid = $real_uid;
      if (isset($_COOKIE['uid']))
         $uid = $_COOKIE['uid'];

      $q = "select firstname, lastname, person.id as id, instrument, "
              . "person.status as status "
              . "from person, instruments "
              . " where uid = '$uid' "
              . "and person.id_instruments = instruments.id";

      $s = $db->query($q);
      $e = $s->fetch(PDO::FETCH_ASSOC);

      $this->real_uid = $real_uid;
      $this->id = $e['id'];
      $this->uid = $uid;
      $this->firstname = $e['firstname'];
      $this->lastname = $e['lastname'];
      $this->instrument = $e['instrument'];
      $this->status = $e['status'];
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

   public function status()
   {
      return $this->status;
   }

   public function real_uid()
   {
      return $this->real_uid;
   }

}
