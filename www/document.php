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
   $info_text = "";

   $variant_keys = array_keys($var_arr);
   for ($variant_idx = 0; $variant_idx < count($variant_keys); $variant_idx++)
      if ($variant_keys[$variant_idx] == $variant)
         break;
}

if ($category == "common")
{
   $heading = "Generelle dokumenter";
   $info_text = "Her finner du dokumenter som omhandler orkesteret. 
           Eksempelvis vedtekter, sakspapirer generalforsamling, 
           informasjon til nye medlemmer, instruks for musikalsk råd etc.";
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
    <h2>$heading2</h2>
    $info_text<p>";

if (this_access_rw())
{
   echo "For å legge til nye dokumenter:"
   . "<ul>"
   . "<li>Velg \"Nytt dokument\"</li>"
   . "<li>Velg \"Browse\" og velg deretter lokal fil som skal lastes opp</li>"
   . "<li>Velg \"ok\" for å laste opp</li>"
   . "</ul>"
   . "For å endre navn på eksisterende dokumenter:"
   . "<ul>"
   . "<li>Velg \"Edit-knapp\" for aktuelt dokument</li>"
   . "<li>Endre navn i navnetekst</li>"
   . "<li>Velg \"ok\""
   . "</ul>"
   . "For å slette eksisterende dokument:"
   . "<ul>"
   . "<li>Velg \"Edit-knapp\" for aktuelt dokument</li>"
   . "<li>Velg \"Del\"</li>"
   . "</ul>";
   $form = new FORM();
   echo "
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=hidden name=path value=\"$path\">
      <input type=submit value=\"Nytt dokument\" title=\"Last opp nytt dokument...\">";
   unset($form);
}

$form = new FORM(null, 'post', 'multipart/form-data');
$tb = new TABLE('border=1');

if (this_access_rw())
   $tb->th('Edit');
$tb->th('File');
$tb->th('Size');
$tb->th('Last modified');

if ($action == 'new')
{
   $tb->tr();
   $tb->td("<input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=hidden name=path value=\"$path\">
    <input type=submit value=ok title=Lagre>", 'align=left');
   $tb->td("<input type=file name=filename id=filename title=\"Velg lokal fil som skal lastes opp...\">", 'colspan=3');
}

if ($action == 'update' && this_access_rw())
{
   if (is_null($no))
   {
      if (!is_dir($path))
         mkdir($path, 0755, true);

      $dst_file = $path . "/" . $_FILES['filename']['name'];
      if ($_FILES['filename']['size'] > 100 * 1024 * 1024)
      {
         echo "<font color=red>File too large! (>100MB)</font>";
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
         $flist[$file] = $file;
      }
      closedir($handle);
   }

   asort($flist);

   foreach ($flist as $file)
   {
      $abs_file = $path . "/" . $file;
      $stat = stat($abs_file);
      $tb->tr();
      if ($file != $no)
      {
         if (this_access_rw())
            $tb->td("<a href=\"$php_self?_sort=$sort&_action=view&_no=" . urlencode($file) . "&path=$path\" title=\"Endre navn eller slette...\" ><img src=\"images/cross_re.gif\" border=0></a>", 'align=center');
         $tb->td("<a href=\"$abs_file\" target=\"_blank\" title=\"Klikk for å laste ned dokument\">$file</a>");
      }
      else
      {
         $tb->td("
               <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_filename value='$file'>
    <input type=hidden name=path value=\"$path\">
    <input type=hidden name=_no value=\"$no\">
    <input type=submit value=ok title=Lagre>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette $file?');\" title=\"Slett...\">", 'nowrap');
         $tb->td("<input type=text size=30 name=file value=\"$file\" title=\"Angi nytt filnavn...\">");
      }
      $tsize = ($stat['size'] / 1024 > 1024) ? (int) ($stat['size'] / (1024 * 1024)) . "MB" : (int) ($stat['size'] / 1024) . "KB";
      $tb->td($tsize);
      $tb->td(strftime('%e.%b %Y', $stat['mtime']));
   }
}
