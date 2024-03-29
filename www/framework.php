<?php
require_once 'request.php';
require_once 'conf/opendb.php';
require_once 'auth.php';
require_once 'common.php';
require_once 'table.php';
require_once 'form.php';
?>

<html>

    <script>
       function set_cookie(cookieName, cookieValue)
       {
           var today = new Date();
           var expire = new Date();
           var nDays = 1;
           expire.setTime(today.getTime() + 3600000 * 24 * nDays);
           document.cookie = cookieName + '=' + escape(cookieValue) + ';expires=' + expire.toGMTString();
       }

    </script>
    <meta charset="UTF-8">
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title><?php echo $prj_name ?></title>
    <meta name="author" content="Jan Olav Rolfsnes" />
    <link rel="stylesheet" type="text/css" href="css/default.css" />
    <link rel="stylesheet" type="text/css" href="css/component.css" />
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link href="images/favicon.ico" rel="shortcut icon">
    <script src="js/modernizr.custom.js"></script>

    <title><?php echo $prj_name ?></title>

    <body>
        <div class="container demo-6">	

            <?php
            require 'menu.php';
            require 'page.php';

            $access->reject_if_unauth();
            $access->confirm_pers_info();
            ?>


