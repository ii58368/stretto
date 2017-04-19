<?php
require 'framework.php';

if ($sort == NULL)
   $sort = 'name';

list($categoy, $id_project, $variant) = split($_REQUEST[path], '/');

if ($categoy == "project")
{
   $query = "select name, semester, year from project where id = $id_project";
   $result = mysql_query($query);
   $row = mysql_fetch_array($result, MYSQL_ASSOC);

   $var_arr = array(
       "rec" => "Innspillinger",
       "sheet" => "Noter",
       "doc" => "Dokumenter");

   $heading = "$row[name] ($row[semester]$row[year]]), $var_arr[$variant]";
}

if ($category == "common")
{
   $heading = "Generelle dokumenter";
}

echo "
    <h1>$heading</h1>
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=hidden name=path value=\"$_REQUEST[path]\">
      <input type=submit value=\"Nytt dokument\">
    </form>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Edit</th>
      <th bgcolor=#A6CAF0>File</th>
      <th bgcolor=#A6CAF0>Size</th>
      <th bgcolor=#A6CAF0>Last modified</th>
      </tr>";

if ($action == 'new')
{
   echo "  <tr>
    <form action=$php_self method=post enctype=multipart/form-data>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=hidden name=path value=\"$_REQUEST[path]\">
    <input type=submit value=ok></td>
    <th colspan=3><input type=file name=filename id=filename</th>
    </form>
  </tr>";
}

if ($action == 'update')
{
   if ($no == NULL)
   {
      $dst_file = $_REQUEST[path] . "/" . $_FILES[filename][name];
      if ($_FILES[filename][size] > 10 * 1024 * 1024)
      {
         echo "<font color=red>File too large! (>10MB)</font>";
      } else
      {
         if (!move_uploaded_file($_FILES[filename][tmp_name], $dst_file))
         {
            echo "<font color=red>Failed to upload!</font>";
         }
      }
   } else
   {
      $cur_file = $_REQUEST[path] . "/" . $no;
      if ($delete != NULL)
      {
         unlink($cur_file);
      } else
      {
         rename($cur_file, $_REQUEST[path] . "/" . $_REQUEST[file]); // old, new
      }
      $no = NULL;
   }
}

if ($handle = opendir($_REQUEST[path]))
{
   while (($file = readdir($handle)) != false)
   {
      $abs_file = $_REQUEST[path] . "/" . $file;
      if (!is_file($abs_file))
         continue;
      $stat = stat($abs_file);
      echo "<tr>";
      if ($file != $no)
      {
         echo "<td><center>
           <a href=\"{$php_self}?_sort={$sort}&_action=view&_no=" . urlencode($file) . "&path=$_REQUEST[path]\"><img src=\"images/cross_re.gif\" border=0></a>
             </center></td>
             <td>$file</td>";
      } else
      {
         echo "
            <form action=$php_self method=post>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_filename value='$file'>
    <input type=hidden name=path value=\"$_REQUEST[path]\">
    <input type=hidden name=_no value=\"$no\">
    <td nowrap><input type=submit value=ok>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette $file?');\"></td>
    <td><input type=text size=30 name=file value=\"$file\"></td>
         </form>";
      }
      echo "<td>" . (int)($stat[size] / 1024) . "K</td>" .
      "<td>" . date('D j.M y', $stat[mtime]) . "</td>" .
      "</tr>";
   }

   closedir($handle);
}
?> 

</table>

<?php
require 'framework_end.php';
?>


