<html>
  <head>
    <title>Images in cwd</title>
  </head>

  <body>
    <h1>Images in cwd</h1>

<?php

if ($handle = opendir('.')) {

    while (($file = readdir($handle)) != false) 
    {
        if (strstr($file, ".php") == true)
          continue;
        $stat = stat($file);
        echo "<img src=\"$file\" border=0 title=\"$file size=$stat[size]\">\n";
    }

    closedir($handle);
}
?>

  </body>
</html>


