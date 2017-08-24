<html>
  <head>
    <title>Edit</title>
  </head>
  <body background=/images/Image3.jpg>

<?php

if ($_REQUEST[_action] == 'save')
{
  $fp = fopen($_REQUEST[file], 'w');
  fwrite($fp, $_REQUEST[text]);
  fclose($fp);
}

readfile($_REQUEST[file]);

echo "
  <form action=\"$php_self\" method=post>
    <input type=hidden name=_action value=save>
    <input type=hidden name=file value=\"$_REQUEST[file]\">
    <textarea name=text cols=60 rows=20 wrap=viritual>";
    readfile($_REQUEST[file]);
echo "</textarea><br>
    <input type=submit value=save>
    </table>
  </form>
";


?>

  </body>
</html>

