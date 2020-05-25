<?php

class FORM
{

   public function __construct($action = null, $method = 'post', $enctype='plain/text')
   {
      if (is_null($action))
         $action = $_SERVER['PHP_SELF'];
      echo "<form action=\"$action\" method=\"$method\" enctype=\"$enctype\">\n";
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
