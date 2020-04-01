<?php

class FORM
{

   public function __construct($action = null, $method = 'post')
   {
      if (is_null($action))
         $action = $_SERVER['PHP_SELF'];
      echo "<form action=\"$action#inProgress\" method=\"$method\">\n";
   }

   public function __destruct()
   {
      echo "</form>\n";
   }

   public function nop()
   {
      // dummy function to delay execution of destructor
   }

}
