<?php

include 'framework.php';

$personList = "person.php";

if ($sort == NULL)
   $sort = 'list_order,lastname,firstname';

if (!$access->auth(AUTH::MEMB_RW))
{
   if ($no != $whoami->id())
   {
      echo "<h1>Permission denied</h1>";
      exit(0);
   }
}

function select_instrument($selected)
{
   global $db;
   echo "<select name=id_instruments>";

   $q = "SELECT id, instrument FROM instruments order by list_order";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      echo "<option value=\"" . $e[id] . "\"";
      if ($e[id] == $selected)
         echo " selected";
      echo ">" . $e[instrument];
   }

   echo "</select>";
}

function select_status($selected)
{
   global $db;

   if (is_null($selected))
      $selected = $db->per_stat_standin;

   echo "<input type=hidden name=status_old value=$selected>\n";
   echo "<select name=status>\n";

   for ($i = 0; $i < count($db->per_stat); $i++)
   {
      echo "<option value=$i";
      if ($selected == $i)
         echo " selected";
      echo ">" . $db->per_stat[$i] . "</option>\n";
   }

   echo "</select>";
}

function select_status_log($selected)
{
   global $db;

   echo "<select name=status>";

   for ($i = 0; $i < count($db->rec_stat); $i++)
   {
      echo "<option value=$i";
      if ($selected == $i)
         echo " selected";
      echo ">" . $db->rec_stat[$i] . "</option>\n";
   }

   echo "</select>";
}

if ($action == 'update_pers')
{
   $birthday = strtotime($_POST[birthday]);
   $now = strtotime("now");
   
   try
   {
      if ($no == NULL)
      {
         $query = "insert into person (id_instruments, firstname, middlename, lastname, address, 
              postcode, city, email, uid, password,
              phone1, phone2, phone3, status, birthday, comment)
              values ('$_POST[id_instruments]', '$_POST[firstname]', 
                      '$_POST[middlename]', '$_POST[lastname]', '$_POST[address]',
                      '$_POST[postcode]', '$_POST[city]', '$_POST[email]',
                      '$_POST[email]', MD5('OSO'),
                      '$_POST[phone1]', '$_POST[phone2]', '$_POST[phone3]', 
                      '$_POST[status]', $birthday, '$_POST[comment]')";
         $db->query($query);
         $no = $db->lastInsertId();
         $db->query("insert into record (ts, status, comment, id_person) " .
              "values ($now, $db->rec_stat_info, '" .$db->per_stat[$_POST[status]] . "', $no)");
      } 
      else
      {
         if ($delete != NULL)
         {
            $query = "DELETE FROM person WHERE id = $no";
            $result = $db->query($query);
            $db->query("delete from record where id_person = $no");
            $no = NULL;
            update_htpasswd($_POST[uid], null);
         } 
         else
         {
            $query = "update person set " .
                    "firstname = '$_POST[firstname]'," .
                    "middlename = '$_POST[middlename]'," .
                    "lastname = '$_POST[lastname]'," .
                    "address = '$_POST[address]'," .
                    "postcode = '$_POST[postcode]'," .
                    "city = '$_POST[city]'," .
                    "email = '$_POST[email]'," .
                    "phone1 = '$_POST[phone1]'," .
                    "phone2 = '$_POST[phone2]'," .
                    "phone3 = '$_POST[phone3]'," .
                    "birthday = $birthday,";
            if ($access->auth(AUTH::MEMB_RW))
               $query .= "status = '$_POST[status]'," .
                         "id_instruments = '$_POST[id_instruments]',";
            $query .= "comment = '$_POST[comment]' " .
                    "where id = $no";
            $db->query($query);
            if ($_POST[status] != $_POST[status_old])
               $db->query("insert into record (ts, status, comment, id_person) " .
                 "values ($now, $db->rec_stat_info, 'Ny status: " . $db->per_stat[$_POST[status]] . "', $no)");
         }
      }
   } catch (PDOException $ex)
   {
      echo "<font color=red>Failed to update</font>";
   }
}

function update_htpasswd($usr, $pwd)
{
   $fname = "conf/.htpasswd";
   $fr = fopen($fname, "r");
   $fw = fopen("{$fname}~", "w");
   
   while (($ln = fgets($fr, 1024)) != null)
   {
      $e = explode(':', $ln);
      if ($usr != $e[0])
         fwrite($fw, "$ln");
   }
   if ($pwd != null)
      fwrite($fw, "$usr:" . md5($pwd) . "\n");
   
   fclose($fr);
   fclose($fw);
   
   rename("$fname~", $fname);
}

function update_pwd($no)
{
   global $db;

   $s = $db->query("select id from person where not id = $no and uid = '$_POST[uid]'");
   if ($s->rowCount() > 0)
   {
      echo "<font color=red>Ikke oppdatert, brukeren finnes fra før!</font>";
      return;
   }

   if (strlen($_POST[uid]) < 2)
   {
      echo "<font color=red>Ikke oppdatert, brukeren må bestå av minst 2 bokstaver!</font>";
      return;
   }

   if ($_POST[pwd1] != $_POST[pwd2])
   {
      echo "<font color=red>Ikke oppdatert, passordene må være like!</font>";
      return;
   }

   if (strlen($_POST[pwd1]) == 0)
   {
      echo "<font color=red>Ikke oppdatert, passord må ha minst 1 bokstav</font>";
      return;
   }

   if (preg_match("/[^A-Za-z0-9]/", $_POST[uid]))
   {
      echo "<font color=red>Ugyldig brukernavn. Gyldige tegn: A-Z, a-z, 0-9</font>";
      return;
   }

   $query = "update person set uid = '$_POST[uid]' , password = MD5('$_POST[pwd1]')" .
           "where id = $no";
   try
   {
      $db->query($query);
   } catch (PDOExeption $ex)
   {
      echo "<font color=red>Failed to update</font>";
   }
   $pwd = $_POST[pwd1];
   $stmt = $db->query("select uid from person where id = $no");
   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   update_htpasswd($row[uid], $pwd1);
}

if ($action == 'update_pwd')
   update_pwd($no);

if ($no != NULL)
{
   $query = "SELECT person.id as id, id_instruments, instrument, firstname, middlename, lastname, " .
           "uid, address, postcode, city, " .
           "email, phone1, phone2, phone3, status, person.comment as comment, " .
           "comment_dir, status_dir, birthday " .
           "FROM person, instruments " .
           "where id_instruments = instruments.id " .
           "and person.id = $no";

   $stmt = $db->query($query);
   $row = $stmt->fetch(PDO::FETCH_ASSOC);
}

$person = ($no == NULL) ? "Ny person" : "$row[firstname] $row[middlename] $row[lastname]";
$postcode = sprintf("%04d", $row[postcode]);

echo "<h1>$person</h1>\n";
echo "<a href=\"person.php\" title=\"Til adresselisten...\"><img src=\"images/index.gif\" border=0 hspace=5></a>\n";
if ($access->auth(AUTH::MEMB_RW))
   echo "<a href=\"$php_self?_sort=$sort&_action=edit_pers\" title=\"Registrere ny person...\"><img src=\"images/new_inc.gif\" border=0 hspace=5 vspace=5></a>\n";
echo "<table border=0>
    <tr bgcolor=#A6CAF0>
      <th>Personalia</th>
      <form action='$php_self' method=post>";

if ($action == 'edit_pers')
{
   echo "
      <th align=left>
        <input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value='$no'>
        <input type=hidden name=_action value=update_pers>
        <input type=submit value=\"Lagre\">\n";
   if ($no != null  && $access->auth(AUTH::MEMB_RW))
      echo "<input type=hidden name=uid value=\"$row[uid]\">
        <input type=submit name=_delete value=slett title=\"Kan slettes fra medlemsregisteret dersom vedkommende ikke har vært med på noen prosjekter\">\n";
   echo "</th>
    </tr>
    <tr>
      <td>Navn:</td>
      <td><input type=text name=firstname size=30 value=\"$row[firstname]\">
          <input type=text name=middlename size=30 value=\"$row[middlename]\">
          <input type=text name=lastname size=30 value=\"$row[lastname]\"></td>
    </tr>
    <tr>
      <td>Instrument:</td>
      <td>";
   if ($access->auth(AUTH::MEMB_RW))
      select_instrument($row[id_instruments]);
   else
      echo $row[instrument];
   echo
   "     </td>
      </tr>
   <tr>
      <td>Adresse:</td>
      <td><input type=text name=address size=30 value=\"$row[address]\"></td>
    </tr>
    <tr>
      <td>Post:</td>
      <td><input type=text name=postcode size=4 maxlength=4 value=\"$postcode\">
          <input type=text name=city size=30 value=\"$row[city]\"></td>
    </tr>
    <tr>
     <td>Mail:</td>
      <td><input type=text name=email size=40 value=\"$row[email]\"></td>
    </tr>
    <tr>
      <td>Telefon:</td>
      <td>mob:<input type=text name=phone1 size=12 value=\"$row[phone1]\">
          priv:<input type=text name=phone2 size=12 value=\"$row[phone2]\">
          jobb:<input type=text name=phone3 size=12 value=\"$row[phone3]\"></td>
    </tr>
    <tr>
      <td>Status:</td>
      <td>";
   if ($access->auth(AUTH::MEMB_RW))
      select_status($row[status]);
   else
      echo $db->per_stat[$row[status]];
   echo
   "     </td>
      </tr>
    <tr>
      <td>Fødselsdag:</td>
      <td><input type=date name=birthday size=15 value=\"" . date('j. M Y', $row[birthday]) . "\" title=\"(frivillig) Eks: 10 jan 2017\"></td>
    </tr>
    <tr>
      <td>Kommentar:</td>
      <td><input type=text name=comment size=50 value=\"$row[comment]\"></td>
    </tr>
  ";
} else
{
   echo "
      <th align=left>
        <input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value='$no'>
        <input type=hidden name=_action value=edit_pers>
        <input type=submit value=\"Endre\">
      </th>
    </tr>
    <tr><td>Navn:</td><td>$row[firstname] $row[middlename] $row[lastname]</td></tr>
    <tr><td>Instrument:</td><td>$row[instrument]</td></tr>
    <tr><td>Adresse:</td><td>$row[address]</td></tr>
    <tr><td>Post:</td><td>$postcode $row[city]</td></tr>
    <tr><td>Mail:</td><td>$row[email]</td></tr>
    <tr><td>Mobil:</td><td>$row[phone1]</td></tr>
    <tr><td>Privat:</td><td>$row[phone2]</td></tr>
    <tr><td>Jobb:</td><td>$row[phone3]</td></tr>
    <tr><td>Status:</td><td>{$db->per_stat[$row[status]]}</td></tr>
    <tr><td>Fødselsdag:</td><td>" . strftime('%e. %b %Y', $row[birthday]) . "</td></tr>
    <tr><td>Kommentar:</td><td>$row[comment]</td></tr>";
}
echo "</form>
        </table>";


if ($no != null)
{
   echo "
    <p>
    <table border=0>
    <tr bgcolor=#A6CAF0>
      <th>Innlogging</th>
      <form action='$php_self' method=post>";

   if ($action == 'edit_pwd')
   {
      echo "
      <th align=left>
        <input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value='$no'>
        <input type=hidden name=_action value=update_pwd>
        <input type=submit value=\"Lagre\">
      </th>
    </tr>
    <tr>
      <td>Bruker-id:</td>
      <td><input type=text name=uid size=30 value=\"$row[uid]\"></td>
    </tr>
    <tr>
      <td>Nytt passord:</td>
      <td><input type=password name=pwd1 size=20></td>
    </tr>
    <tr>
      <td>Gjenta passord:</td>
      <td><input type=password name=pwd2 size=20></td>
    </tr>";
   } else
   {
      echo "
      <th>
        <input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value='$no'>
        <input type=hidden name=_action value=edit_pwd>
        <input type=submit value=\"Endre\">
      </th>
    </tr>
    <tr><td>Bruker-id:</td><td>$row[uid]</td></tr>
    <tr><td>Passord:</td><td>************</td></tr>";
   }
   echo "</form>
        </table>";

}


echo "
    <h3>Logg</h3>";
if ($access->auth(AUTH::MEMB_RW))
   echo "
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_no value='$no'>
      <input type=hidden name=_action value=new_log>
      <input type=submit value=\"Legg til\">
    </form>";
echo "
    <form action='$php_self' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::MEMB_RW))
   echo "
      <th bgcolor=#A6CAF0>Edit</th>";
echo "
      <th bgcolor=#A6CAF0>Dato</th>
      <th bgcolor=#A6CAF0>Status</th>
      <th bgcolor=#A6CAF0>Tekst</th>
      </tr>";

$rno = $_REQUEST[_rno];

if ($action == 'new_log')
{
   echo "  <tr>
    <td align=left><input type=hidden name=_action value=update_log>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=hidden name=_no value=$no>
    <input type=submit value=ok></td>
    <th><input type=date size=15 value=\"" . date('j. M y') . "\" name=ts></th>
    <th>\n";
   select_status_log(null);
   echo "
   </th>
    <th><textarea cols=60 rows=3 wrap=virtual name=comment></textarea></th>
  </tr>";
}

if ($action == 'update_log' && $access->auth(AUTH::MEMB_RW))
{
   $ts = strtotime($_POST[ts]);

   if (is_null($rno))
      $query = "insert into record (ts, status, comment, id_person) " .
              "values ($ts, $_POST[status], '$_POST[comment]', $no)";
   else
   {
      if (!is_null($delete))
      {
         $query = "delete from record where id = $_POST[_rno]";
      } 
      else
      {
         $query = "update record set ts = $ts," .
                 "status = $_POST[status]," .
                 "comment = '$_POST[comment]' " .
                 "where id = $rno";
      }
      $rno = null;
   }
   $db->query($query);
}

$query = "select id, ts, status, comment "
        . "from record "
        . "where id_person = $no ";
if (!$access->auth(AUTH::BOARD_RO))
   $query .= "and status = $db->rec_stat_info ";
$query .= "order by ts";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($row[id] != $rno)
   {
      echo "<tr>";
      if ($access->auth(AUTH::MEMB_RW))
         echo "
         <td><center>
           <a href=\"$php_self?_sort=$sort&_action=view_log&_rno=$row[id]&_no=$no\"><img src=\"images/cross_re.gif\" border=0></a>
             </center></td>";
      echo "<td>" . strftime('%e. %b %Y', $row[ts]) . "</td>" .
      "<td>" . $db->rec_stat[$row[status]] . "</td>\n";
      echo "<td>";
      echo str_replace("\n", "<br>\n", $row[comment]);
      echo "</td>" .
      "</tr>";
   }
   else
   {
      echo "<tr>
    <input type=hidden name=_action value=update_log>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_rno value=$rno>
    <input type=hidden name=_no value=$no>
    <th nowrap><input type=submit value=ok>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette?');\"></th>
    <th><input type=date size=15 name=ts value=\"" . date('j. M y', $row[ts]) . "\" title=\"Eks: 10 dec 201\"></th>\n";
    echo "<td>";
      select_status_log($row[status]);
    echo "</td>
    <th><textarea cols=60 rows=3 wrap=virtual name=comment>$row[comment]</textarea></th>
    </tr>";
   }
}
?> 

</table>
</form>
