<?php

include 'framework.php';

$personList = "person.php";

if ($sort == NULL)
   $sort = 'list_order,lastname,firstname';

function select_instrument($selected)
{
   echo "<select name=id_instruments>";

   $q = "SELECT id, instrument FROM instruments order by list_order";
   $r = mysql_query($q);

   while ($e = mysql_fetch_array($r, MYSQL_ASSOC))
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
   global $per_stat;
   global $per_stat_standin;

   if ($selected == NULL)
      $selected = $per_stat_standin;

   echo "<select name=status>";

   for ($i = 0; $i < count($per_stat); $i++)
   {
      echo "<option value=$i";
      if ($selected == $i)
         echo " selected";
      echo ">$per_stat[$i]</option>\n";
   }

   echo "</select>";
}

if ($action == 'update_pers')
{
   if ($no == NULL)
   {
      $query = "insert into person (id_instruments, firstname, lastname, address, 
              postcode, city, email,
              phone1, phone2, phone3, status, comment)
              values ('$_POST[id_instruments]', '$_POST[firstname]', 
                      '$_POST[lastname]', '$_POST[address]',
                      '$_POST[postcode]', '$_POST[city]', '$_POST[email]',
                      '$_POST[phone1]', '$_POST[phone2]', '$_POST[phone3]', 
                      '$_POST[status]', '$_POST[comment]')";
      $result = mysql_query($query);
      $no = mysql_insert_id();
   } else
   {
      if ($delete != NULL)
      {
         $query = "DELETE FROM person WHERE id = $no";
         $result = mysql_query($query);
         $no = NULL;
      } else
      {
         $query = "update person set id_instruments = '$_POST[id_instruments]'," .
                 "firstname = '$_POST[firstname]'," .
                 "lastname = '$_POST[lastname]'," .
                 "address = '$_POST[address]'," .
                 "postcode = '$_POST[postcode]'," .
                 "city = '$_POST[city]'," .
                 "email = '$_POST[email]'," .
                 "phone1 = '$_POST[phone1]'," .
                 "phone2 = '$_POST[phone2]'," .
                 "phone3 = '$_POST[phone3]'," .
                 "status = '$_POST[status]'," .
                 "comment = '$_POST[comment]' " .
                 "where id = $no";
         $result = mysql_query($query);
      }
   }

   if (!$result)
      echo "<font color=red>Failed to update</font>";
}


if ($action == 'update_pwd')
{
   if ($_POST[pwd1] == $_POST[pwd2])
   {
      $query = "update person set password = MD5('$_POST[pwd1]') " .
              "where id = $no";
      $result = mysql_query($query);
      if (!$result)
         echo "<font color=red>Failed to update</font>";

      $pwd = $_POST[pwd1];
      $query = "select email from person where id = $no";
      $result = mysql_query($query);
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      
      $ht_cmd = "/usr/sbin/htpasswd -bd /etc/apache2/{$dbname}_user $row[email] $pwd";
      system($ht_cmd);
   }
   else
   {
      echo "<font color=red>Ikke oppdatert, passordene må være like!</font>";
   }
}


if ($no != NULL)
{
   $query = "SELECT person.id as id, id_instruments, instrument, firstname, lastname, " .
           "address, postcode, city, " .
           "email, phone1, phone2, phone3, status, person.comment as comment, " .
           "comment_dir, direction " .
           "FROM person, instruments " .
           "where id_instruments = instruments.id " .
           "and person.id = $no";

   $result = mysql_query($query);
   $row = mysql_fetch_array($result, MYSQL_ASSOC);
}

$person = ($no == NULL) ? "Ny person" : "$row[firstname] $row[lastname] ($row[instrument])";
$postcode = sprintf("%04d", $row[postcode]);

echo "
    <h1>$person</h1>
    <table border=0>
    <tr>
      <th>Personalia</th>
      <form action='$php_self' method=post>";

if ($action == 'edit_pers')
{
   echo "
      <th>
        <input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value='$no'>
        <input type=hidden name=_action value=update_pers>
        <input type=submit value=\"Lagre\">
        <input type=submit name=_delete value=slett title=\"Kan slettes fra medlemsregisteret dersom vedkommende ikke har vært med på noen prosjekter\"> 
      </th>
    </tr>
    <tr>
      <td>Navn:</td>
      <td><input type=text name=firstname size=30 value=\"$row[firstname]\">
          <input type=text name=lastname size=30 value=\"$row[lastname]\"></td>
    </tr>
    <tr>
      <td>Instrument:</td>
      <td>";
   select_instrument($row[id_instruments]);
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
   select_status($row[status]);
   echo
   "     </td>
      </tr>
    <tr>
      <td>Kommentar:</td>
      <td><input type=text name=comment size=50 value=\"$row[comment]\"></td>
    </tr>
  ";
} else
{
   echo "
      <th>
        <input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value='$no'>
        <input type=hidden name=_action value=edit_pers>
        <input type=submit value=\"Endre\">
      </th>
    </tr>
    <tr><td>Navn:</td><td>$row[firstname] $row[lastname]</td></tr>
    <tr><td>Instrument:</td><td>$row[instrument]</td></tr>
    <tr><td>Adresse:</td><td>$row[address]</td></tr>
    <tr><td>Post:</td><td>$postcode $row[city]</td></tr>
    <tr><td>Mail:</td><td>$row[email]</td></tr>
    <tr><td>Mobil:</td><td>$row[phone1]</td></tr>
    <tr><td>Privat:</td><td>$row[phone2]</td></tr>
    <tr><td>Jobb:</td><td>$row[phone3]</td></tr>
    <tr><td>Status:</td><td>{$per_stat[$row[status]]}</td></tr>
    <tr><td>Kommentar:</td><td>$row[comment]</td></tr>";
}
echo "</form>
        </table>";


echo "
    <p>
    <table border=0>
    <tr>
      <th>Passord</th>
      <form action='$php_self' method=post>";

if ($action == 'edit_pwd')
{
   echo "
      <th>
        <input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value='$no'>
        <input type=hidden name=_action value=update_pwd>
        <input type=submit value=\"Lagre\">
      </th>
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
        <input type=submit value=\"Nytt passord\">
      </th>
    </tr>
    <tr><td>Passord:</td><td>************</td></tr>";
}





include 'framework_end.php';
?>