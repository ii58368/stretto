<?php
require 'framework.php';

if (is_null($sort))
   $sort = 'name';

$path = request('path');

function update_db($variant_idx, $id_project, $row)
{
   global $db;
   global $path;

   $docs_bit = 0;

   if ($handle = opendir($path))
   {
      while (($file = readdir($handle)) != false)
      {
         $abs_file = $path . "/" . $file;
         if (!is_file($abs_file))
            continue;
         $docs_bit = 1 << $variant_idx;
         break;
      }
      closedir($handle);
   }

   $docs_avail = $row['docs_avail'] & ~(1 << $variant_idx);
   $docs_avail |= $docs_bit;

   $db->query("update project set docs_avail = $docs_avail where id = $id_project");
}

$apath = explode('/', $path);
$category = $apath[0];
if (isset($apath[1]))
   $id_project = $apath[1];
if (isset($apath[2]))
   $variant = $apath[2];

$heading = '';
$heading2 = '';

if ($category == "project")
{
   $query = "select name, semester, year, docs_avail from project where id = $id_project";
   $s = $db->query($query);
   $row = $s->fetch(PDO::FETCH_ASSOC);

   $var_arr = array(
       "rec" => "Innspillinger",
       "sheet" => "Noter",
       "doc" => "Dokumenter");

   $heading = $row['name'] . " (" . $row['semester'] . $row['year'] . ")";
   $heading2 = $var_arr[$variant];

   $variant_keys = array_keys($var_arr);
   for ($variant_idx = 0; $variant_idx < count($variant_keys); $variant_idx++)
      if ($variant_keys[$variant_idx] == $variant)
         break;
}

if ($category == "common")
{
   $heading = "Generelle dokumenter";
}

if (is_null($heading))
{
   echo "<h1>Illegal path</h1>Access denied";
   exit(0);
}

function this_access_rw()
{
   global $access;
   global $category;

   if ($category == 'project')
      return $access->auth(AUTH::PRJDOC);
   if ($category == 'common')
      return $access->auth(AUTH::DOC_RW);

   return false;
}

echo "
    <h1>$heading</h1>
    <h2>$heading2</h2>";
if (this_access_rw())
   echo "
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=hidden name=path value=\"$path\">
      <input type=submit value=\"Nytt dokument\">
    </form>";
echo "
    <table border=1>
    <tr>";
if (this_access_rw())
   echo "
      <th>Edit</th>";
echo "
      <th>File</th>
      <th>Size</th>
      <th>Last modified</th>
      </tr>";

if ($action == 'new')
{
   echo "  <tr>
    <form action=$php_self method=post enctype=multipart/form-data>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=hidden name=path value=\"$path\">
    <input type=submit value=ok></td>
    <th colspan=3><input type=file name=filename id=filename</th>
    </form>
  </tr>";
}

if ($action == 'update' && this_access_rw())
{
   if (is_null($no))
   {
      if (!is_dir($path))
         mkdir($path, 0755, true);

      $dst_file = $path . "/" . $_FILES['filename']['name'];
      if ($_FILES['filename']['size'] > 10 * 1024 * 1024)
      {
         echo "<font color=red>File too large! (>10MB)</font>";
      }
      else
      {
         if (!move_uploaded_file($_FILES['filename']['tmp_name'], $dst_file))
         {
            echo "<font color=red>Failed to upload!</font>";
         }
      }
   }
   else
   {
      $cur_file = $path . "/" . $no;
      if (!is_null($delete))
      {
         unlink($cur_file);
      }
      else
      {
         rename($cur_file, $path . "/" . request('file')); // old, new
      }
      $no = NULL;
   }

   if (isset($variant_idx))
      update_db($variant_idx, $id_project, $row);
}

if (is_dir($path))
{
   if ($handle = opendir($path))
   {
      while (($file = readdir($handle)) != false)
      {
         $abs_file = $path . "/" . $file;
         if (!is_file($abs_file))
            continue;
         $stat = stat($abs_file);
         echo "<tr>";
         if ($file != $no)
         {
            if (this_access_rw())
               echo "<td><center>
           <a href=\"$php_self?_sort=$sort&_action=view&_no=" . urlencode($file) . "&path=$path\"><img src=\"images/cross_re.gif\" border=0></a>
             </center></td>";
            echo "
             <td><a href=\"$abs_file\">$file</a></td>";
         }
         else
         {
            echo "
            <form action=$php_self method=post>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_filename value='$file'>
    <input type=hidden name=path value=\"$path\">
    <input type=hidden name=_no value=\"$no\">
    <td nowrap><input type=submit value=ok>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette $file?');\"></td>
    <td><input type=text size=30 name=file value=\"$file\"></td>
         </form>";
         }
         echo "<td>" . (int) ($stat['size'] / 1024) . "K</td>" .
         "<td>" . strftime('%a %e.%b %y', $stat['mtime']) . "</td>" .
         "</tr>";
      }

      closedir($handle);
   }
}
?> 

</table>
