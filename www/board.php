<?php

require 'framework.php';

if ($action == 'update' && $access->auth(AUTH::GRP))
{
   $query = "update groups set info = " . $db->qpost('info') . " "
           . "where id = $no";
   $db->query($query);

   $no = NULL;
}

echo "<h1>Styrefunksjoner</h1>\n";
echo "Oversikt over styrefunksjonene i Oslo Symfoniorkester\n";

$query = "SELECT groups.id as id, groups.name as gname, firstname, lastname, person.email as pemail, "
        . "groups.comment as comment, groups.info as info, "
        . "instrument, member.role as role, "
        . "member.email as gemail "
        . "FROM person, instruments, member, groups "
        . "where instruments.id = person.id_instruments "
        . "and groups.id = member.id_groups "
        . "and person.id = member.id_person "
        . "order by groups.name, instruments.list_order, lastname, firstname";

$stmt = $db->query($query);

$gname_old = '';
$form = new FORM();

foreach ($stmt as $row)
{
   if ($row['gname'] != $gname_old)
   {
      unset($tb);
      echo "<h2>" . $row['gname'] . "</h2>\n";
      if ($access->auth(AUTH::GRP))
      {
         if ($row['id'] != $no)
         {
            echo "<a href=\"$php_self?_sort=$sort&_action=view&_no=" . $row['id'] . "\" title=\"Endre tekst...\"><img src=\"images/cross_re.gif\" border=0></a> ";
            $info = str_replace("\n", "<br>\n", $row['info']);
            echo replace_links($info);
         }
         else
         {
            echo "<input type=hidden name=_action value=update>"
            . "<input type=hidden name=_no value='$no'>"
            . "<input type=submit value=ok title=\"Lagre endringer\"><br>\n";
            echo "<textarea cols=60 rows=7 wrap=virtual name=info title=\"Fritekst\">" . $row['info'] . "</textarea>";
         }
      }
      echo "<p>";
      $tb = new TABLE('id=no_border');
      $gname_old = $row['gname'];
   }
   $email = (strlen($row['gemail']) > 0) ? $row['gemail'] : $row['pemail'];
   $tb->td("<a href=\"mailto:$email?subject=OSO: \">" . $row['firstname'] . " " . $row['lastname'] . "</a>");
   $tb->td($row['role']);
   $tb->tr();
}

unset($tb);

