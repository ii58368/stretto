<?php

require 'framework.php';

if (is_null($sort))
   $sort = 'groups.name';

echo "<h1>Grupper</h1>";

if ($access->auth(AUTH::GRP))
{
   $form = new FORM();
   echo "<input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Ny gruppe\" title=\"Definer en ny gruppe...\">";
   unset($form);
}

$form = new FORM();
$tb = new TABLE('border=1');

if ($access->auth(AUTH::GRP))
   $tb->th('Edit');

$tb->th('Navn');
$tb->th('Ansvarlig');
$tb->th('Medlemmer/Instrumentgrupper');
$tb->th('Kommentar');
$tb->tr();

function member_select($id_groups)
{
   global $db;

   $q = "SELECT person.id as id, firstname, lastname, instrument " .
           "FROM person, instruments " .
           "where instruments.id = person.id_instruments " .
           "and not (person.status = $db->per_stat_quited or person.status = $db->per_stat_removed) " .
           "order by instruments.list_order, lastname, firstname";
   $s = $db->query($q);

   $q2 = "SELECT id_person, role, email FROM member where id_groups = $id_groups";
   $s2 = $db->query($q2);
   $r2 = $s2->fetchAll(PDO::FETCH_ASSOC);

   $str = "<select name=\"id_persons[]\" multiple title=\"Velg personer inn i gruppen.\nCtrl-click to select/unselect single\">";

   foreach ($s as $e)
   {
      $str .= "<option value=\"" . $e['id'] . "\"";
      reset($r2);
      foreach ($r2 as $e2)
         if ($e['id'] == $e2['id_person'])
            $str .= " selected";
      $str .= ">" . $e['firstname'] . " " . $e['lastname'] . " (" . $e['instrument'] . ")";
   }
   $str .= "</select>";

   reset($r2);
   foreach ($r2 as $e2)
   {
      $str .= "<input type=hidden name=\"role:" . $e2['id_person'] . "\" value=\"" . $e2['role'] . "\">";
      $str .= "<input type=hidden name=\"email:" . $e2['id_person'] . "\" value=\"" . $e2['email'] . "\">";
   }

   return $str;
}

function member_list($id_groups)
{
   global $db;
   global $sort;
   global $access;
   global $php_self;

   $tb = new TABLE('border=0');
   
   $q = "SELECT firstname, lastname, instrument, member.role as role, "
           . "person.id as id_person, groups.id as id_groups, member.email as email "
           . "FROM person, instruments, member, groups "
           . "where instruments.id = person.id_instruments "
           . "and groups.id = member.id_groups "
           . "and person.id = member.id_person "
           . "and groups.id = $id_groups "
           . "order by instruments.list_order, lastname, firstname";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      $tb->tr();
      if (request('id_groups') == $e['id_groups'] && request('id_person') == $e['id_person'])
      {
         $tb->td("<input type=hidden name=_sort value=\"$sort\">\n"
                 . "<input type=submit value=ok title=\"Lagre rolle...\">\n"
                 . "<input type=hidden name=_action value=update_role>\n"
                 . "<input type=hidden name=id_person value=" . request('id_person') . ">\n"
                 . "<input type=hidden name=id_groups value=" . request('id_groups') . ">");
         $tb->td($e['firstname'] . " " . $e['lastname']);
         $tb->td("<input type=text size=15 name=role value=\"" . $e['role'] . "\" title=\"Spesifiser rolle...\">");
         $tb->td("<input type=text size=30 name=email value=\"" . $e['email'] . "\" title=\"Mailadresse for rolle...\">");
      }
      else
      {
         if ($access->auth(AUTH::GRP))
            $tb->td("<a href=\"$php_self?id_groups=" . $e['id_groups'] . "&id_person=" . $e['id_person'] . "\"><img src=\"images/cross_re.gif\" border=0 title=\"Editere rolle...\"></a>");
         $tb->td($e['firstname'] . " " . $e['lastname'] . " (" . $e['instrument'] . ")");
         $tb->td("<i>" . $e['role'] . "</i>");
         $tb->td("<a href=\"mailto:" . $e['email'] . "?subject=OSO: \">" . $e['email'] . "</a>");
      }
   }

   return $tb->res();
}

function instruments_list($id_groups)
{
   global $db;
   $str = '';

   $q = "select instrument from instruments where id_groups = $id_groups";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      $str .= $e['instrument'] . "<br>";
   }

   return $str;
}

function person_select($selected)
{
   global $db;

   $str = "<select name=id_person title=\"Angi hvem som er leder/kontaktperson for gruppen\">";

   $q = "SELECT person.id as id, firstname, lastname, instrument FROM person, instruments "
           . "where person.id_instruments = instruments.id "
           . "and not (person.status = $db->per_stat_quited or person.status = $db->per_stat_removed) "
           . "order by list_order, lastname, firstname";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      $str .= "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         $str .= " selected";
      $str .= ">" . $e['firstname'] . " " . $e['lastname'] . " (" . $e['instrument'] . ")";
   }
   $str .= "</select>";

   return $str;
}

function member_update($id_groups)
{
   global $db;

   $query = "delete from member where id_groups = $id_groups";
   $db->query($query);
   $i = 0;

   if (isset($_POST['id_persons']))
   {
      foreach ($_POST['id_persons'] as $id_person)
      {
         $role = $db->qpost("role:$id_person");
         $email = $db->qpost("email:$id_person");
         $query = "insert into member (id_person, id_groups, role, email) " .
                 "values ($id_person, $id_groups, $role, $email)";
         $db->query($query);
      }
   }
}

if ($action == 'new')
{
   global $sort;

   $tb->td("<input type=hidden name=_action value=update>"
           . "<input type=hidden name=_sort value=\"$sort\">"
           . "<input type=submit value=ok title=\"Lagre ny gruppe\">");
   $tb->td("<input type=text size=30 name=name title=\"Navn på gruppen\">");
   $tb->td(person_select(0));
   $tb->td(member_select(0));
   $tb->td("<textarea cols=60 rows=7 wrap=virtual name=comment title=\"Fritekst\"></textarea>");
   $tb->tr();
}

if ($action == 'update' && $access->auth(AUTH::GRP))
{
   $id_person = request('id_person');
   if (is_null($id_person))
      $id_person = 0;

   if (is_null($no))
   {
      $query = "insert into groups (name, id_person, comment) " .
              "values (" . $db->qpost('name') . ", $id_person, " . $db->qpost('comment') . ")";
      $db->query($query);
      $no = $db->lastInsertId();
      member_update($no);
   }
   else
   {
      if (!is_null($delete))
      {
         $q = "select count(*) as count from instruments where id_groups = $no";
         $s = $db->query($q);
         $e = $s->fetch(PDO::FETCH_ASSOC);
         if ($e['count'] == 0)
         {
            $db->query("delete from member where id_groups = $no");
            $db->query("DELETE FROM groups WHERE id = $no");
         }
         else
            echo "<font color=red>Error: Some instruments are already part of this group</font>";
      }
      else
      {
         $query = "update groups set name = " . $db->qpost('name') . "," .
                 "id_person = $id_person," .
                 "comment = " . $db->qpost('comment') . " " .
                 "where id = $no";
         $db->query($query);
         member_update($no);
      }
   }

   $no = NULL;
}

if ($action == 'update_role' && $access->auth(AUTH::GRP))
{
   $query = "update member set role = " . $db->qpost('role') . ", "
           . "email = " . $db->qpost('email') . " "
           . "where id_person = " . request('id_person') . " "
           . "and id_groups = " . request('id_groups');
   $db->query($query);

   $_REQUEST['id_groups'] = null;
}


$query = "SELECT groups.id as id, groups.name as name, firstname, lastname, instrument, "
        . "groups.comment as comment, "
        . "person.id as id_person "
        . "FROM groups, person, instruments "
        . "where person.id = groups.id_person "
        . "and instruments.id = person.id_instruments "
        . "order by " . str_replace("+", " ", $sort);

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   $tb->tr();
   if ($row['id'] != $no)
   {
      if ($access->auth(AUTH::GRP))
         $tb->td("<a href=\"$php_self?_sort=$sort&_action=view&_no=" . $row['id'] . "\"><img src=\"images/cross_re.gif\" border=0></a>", 'align=center');
      $tb->td($row['name']);
      $tb->td($row['firstname'] . " " . $row['lastname'] . " (" . $row['instrument'] . ")");
      $tb->td(instruments_list($row['id']) . member_list($row['id']));
      $tb->td(str_replace("\n", "<br>\n", $row['comment']));
   }
   else
   {
      $tb->td("<input type=hidden name=_action value=update>"
              . "<input type=hidden name=_sort value='$sort'>"
              . "<input type=hidden name=_no value='$no'>"
              . "<input type=submit value=ok title=\"Lagre endringer\">"
              . "<input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette " . $row['name'] . "?');\" title=\"Slett gruppe\">", 'nowrap');
      $tb->td("<input type=text size=30 name=name value=\"" . $row['name'] . "\" title=\"Navn på gruppen\">");
      $tb->td(person_select($row['id_person']));
      $tb->td(instruments_list($row['id']) . member_select($no));
      $tb->td("<textarea cols=60 rows=7 wrap=virtual name=comment title=\"Fritekst\">" . $row['comment'] . "</textarea>");
   }
}
