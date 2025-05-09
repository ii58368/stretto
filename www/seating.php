<?php

require 'framework.php';

function get_project()
{
   global $db;

   $query = "select name, semester, year "
           . " from project"
           . " where id=" . request('id_project');
   $stmt = $db->query($query);
   return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_mygroup()
{
   global $whoami;
   global $db;

   $query = "select groups.id as id "
           . "from participant, instruments, groups, person "
           . "where participant.id_person = person.id "
           . "and person.id = " . $whoami->id() . " "
           . "and participant.id_project = " . request('id_project') . " "
           . "and participant.id_instruments = instruments.id "
           . "and instruments.id_groups = groups.id";

   $stmt = $db->query($query);
   $grp = $stmt->fetch(PDO::FETCH_ASSOC);
   $grp_prj = $grp['id'];

   $query = "select groups.id as id "
           . "from instruments, groups, person "
           . "where person.id = " . $whoami->id() . " "
           . "and person.id_instruments = instruments.id "
           . "and instruments.id_groups = groups.id";

   $stmt = $db->query($query);
   $grp = $stmt->fetch(PDO::FETCH_ASSOC);
   $grp_def = $grp['id'];

   return array($grp_prj, $grp_def);
}

function get_seating($id_groups)
{
   global $db;

   $query = "select template, firstname, lastname, seating.ts as ts "
           . "from seating, person "
           . "where seating.id_person = person.id "
           . "and id_project=" . request('id_project') . " "
           . "and id_groups=$id_groups";

   $stmt = $db->query($query);
   return $stmt->fetch(PDO::FETCH_ASSOC);
}

function select_group($selected)
{
   global $db;
   $form = new FORM();

   echo "<input type=hidden name=id_project value=" . request('id_project') . ">\n";
   echo "<select name=id_group onChange=\"submit();\"  title=\"Velg stemmegruppe\">\n>";

   $q = "select groups.id as id, groups.name as name "
           . "from instruments, groups "
           . "where instruments.id_groups = groups.id "
           . "group by groups.id ";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         echo " selected";
      echo ">" . $e['name'] . "</option>";
   }
   echo "</select>";
   unset($form);
}

function get_group()
{
   $gname = 'id_group';
   $gid = request($gname);

   if (!is_null($gid))
   {
      return $gid;
   }

   return get_mygroup()[0];
}

function select_template($selected)
{
   $opt = array(
       "Blank",
       "Violin 1, 9 pulter",
       "Violin 1, 7,5 pulter",
       "Violin 2, 9 pulter",
       "Viola, 9 pulter",
       "Cello, 8 pulter",
       "Cello, 7,5 pulter",
       "Violin 1, 10 pulter"
   );
   echo "<select name=template onChange=\"submit();\"  title=\"Velg aktuell template for dette prosjektet\">\n";
   for ($i = 0; $i < sizeof($opt); $i++)
   {
      echo "<option value=$i";
      if ($i == $selected)
         echo " selected";
      echo ">$opt[$i]</option>\n";
   }
   echo "</select>\n";
}

function update_seating($id_groups, $template)
{
   global $whoami;
   global $db;

   $ts = strtotime("now");

   if (!($seat = get_seating($id_groups)))
   {
      $query = "insert into seating (id_groups, id_project, template, id_person, ts) "
              . "values ($id_groups, " . request('id_project') . ", $template, "
              . $whoami->id() . ", $ts)";
   }
   else
   {
      if (is_null($template))
         $template = $seat['template'];

      $query = "update seating set template = $template,"
              . "id_person = " . $whoami->id() . ","
              . "ts = '$ts' "
              . "where id_groups = $id_groups "
              . "and id_project = " . request('id_project');
   }
   $db->query($query);
}

$grp_id = get_group();

if ($action == 'template')
{
   update_seating($grp_id, request('template'));
}

function update_cell($id, $prm, $val)
{
   global $db;

   if ($prm == "position")
      if (empty($val))
         $val = 'NULL';
   if ($prm == "comment_pos")
      $val = $db->quote($val);

   $query = "update participant set $prm = $val "
           . "where id_person = $id "
           . "and id_project = " . request('id_project');
   $db->query($query);
}

if (is_null($sort))
   $sort = 'list_order,-position+DESC,-def_pos+DESC,firstname,lastname';

if ($action == 'update')
{
   foreach ($_REQUEST as $key => $val)
   {
      if (strstr($key, ':'))
      {
         list($prm, $id) = explode(':', $key);
         update_cell($id, $prm, $val);
      }
   }

   update_seating($grp_id, null);
}

$prj = get_project();
$seat = get_seating($grp_id);

$url = "seating_pdf.php?id_project=" . request('id_project') . "&id_groups=$grp_id&template=" . $seat['template'];
echo "
    <h1>Gruppeoppsett" . $access->hlink2($url, "<img src=images/pdf.jpeg height=25 border=0 hspace=5 vspace=5>", "title=\"PDF versjon av gruppeoppsett\"", '') . "</h1>
    <h2>" . $prj['name'] . " " . $prj['semester'] . "-" . $prj['year'] . "</h2>";
if ($access->auth(AUTH::SEAT))
{
   select_group($grp_id);
   $mygrp = get_mygroup();
   
   if ($grp_id == $mygrp[0] || $grp_id == $mygrp[1])
   {
      $query = "SELECT participant.id_person as id, firstname, lastname, instrument, position, comment_pos, comment_final "
              . "FROM person, participant, instruments, groups "
              . "where person.id = participant.id_person "
              . "and participant.id_project = " . request('id_project') . " "
              . "and participant.stat_inv = $db->par_stat_yes "
              . "and participant.stat_final = $db->par_stat_yes "
              . "and participant.id_instruments = instruments.id "
              . "and instruments.id_groups = groups.id "
              . "and groups.id = $grp_id "
              . "order by " . str_replace("+", " ", $sort);

      $stmt = $db->query($query);

      $form = new FORM();

      if (is_null($action) || $action == 'update' || $action == 'template')
      {
         echo "<input type=hidden name=_action value=edit>
    <input type=hidden name=id_project value=" . request('id_project') . ">
    <input type=hidden name=id_group value=$grp_id>
    <input type=submit value=Endre title=\"Endre gruppeoppsett...\">";
      }
      else
      {
         echo "<input type=hidden name=_action value=update>
    <input type=hidden name=id_project value=" . request('id_project') . ">
    <input type=hidden name=id_group value=$grp_id>
    <input type=submit value=Lagre title=\"Lagre gruppeoppsett\">
    <input type=reset value=Tilbakestill title=\"Tilbbakestill endringer uten å lagre\">";
      }

      echo "<p>";

      $tb = new TABLE('border=1');

      $options = "id_project=" . request('id_project') . "&id_group=$grp_id";
      $tb->th("<a href=\"$php_self?$options&_sort=firstname,lastname\" title=\"Sorter på fornavn, deretter etternavn\">Navn</a>");
      $tb->th('Instrument');
      $tb->th("<a href=\"$php_self?$options&_sort=list_order,-position+DESC,-def_pos+DESC,firstname,lastname\" title=\"Sorter på plassnummer\">Plass</a>");
      $tb->th('Kommentar');
      $tb->th("Merknad");

      foreach ($stmt as $row)
      {
         $tb->tr();

         if ($action == 'edit')
         {
            $tb->td($row['firstname'] . " " . $row['lastname']);
            $tb->td($row['instrument']);
            $tb->td("<input type=number min=0 max=30 name=position:" . $row['id'] . " value=\"" . $row['position'] . "\" title=\"Plassnummer\">");
            $tb->td("<input type=text size=30 name=comment_pos:" . $row['id'] . " value=\"" . $row['comment_pos'] . "\" title=\"Fritekst, kun synlig for gruppeleder\">");
            $tb->td($row['comment_final']);
         }
         else
         {
            $tb->td($row['firstname'] . " " . $row['lastname']);
            $tb->td($row['instrument']);
            $tb->td($row['position']);
            $tb->td($row['comment_pos']);
            $tb->td($row['comment_final']);
         }
      }

      unset($tb);
      unset($form);

      $form = new FORM();

      echo "
       <input type=hidden name=_action value=template>
       <input type=hidden name=_sort value='$sort'>
       <input type=hidden name=id_group value=$grp_id>
       <input type=hidden name=id_project value=" . request('id_project') . ">\n";
      select_template($seat['template']);

      unset($form);
   }
}

echo "<img src=\"map.php?id_groups=$grp_id&id_project=" . request('id_project') . "&template=" . $seat['template'] . "&uid=" . $whoami->uid() . "\" width=500><br>\n";

if (!is_null($seat))
   echo $seat['firstname'] . "/" . strftime('%e.%b %y', $seat['ts']) . "\n";
