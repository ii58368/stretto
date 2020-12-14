<?php

include 'framework.php';

$personList = "person.php";

if ($sort == NULL)
   $sort = 'list_order,-def_pos+desc,lastname,firstname';

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
   $str = "<select name=id_instruments title=\"Hovedinstrument (Dersom vedkommende spiller et annet instrument på et prosjekt, registreres dette i ressursplanen for det aktuelle prosjektet.\">";

   $q = "SELECT id, instrument FROM instruments order by list_order";
   $s = $db->query($q);

   foreach ($s as $e)
   {
      $str .= "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         $str .= " selected";
      $str .= ">" . $e['instrument'];
   }

   $str .= "</select>";

   return $str;
}

function select_status($selected)
{
   global $db;

   if (is_null($selected))
      $selected = $db->per_stat_standin;

   $str = "<input type=hidden name=status_old value=$selected>\n";
   $str .= "<select name=status title=\"Medlemsstatus\">\n";

   for ($i = 0; $i < count($db->per_stat); $i++)
   {
      $str .= "<option value=$i";
      if ($selected == $i)
         $str .= " selected";
      $str .= ">" . $db->per_stat[$i] . "</option>\n";
   }

   $str .= "</select>";

   return $str;
}

function select_sex($selected)
{
   global $db;

   $str = "<select name=sex title=\"Velg kjønn\">\n";

   for ($i = 0; $i < count($db->per_sex); $i++)
   {
      $str .= "<option value=$i";
      if ($selected == $i)
         $str .= " selected";
      $str .= ">" . $db->per_sex[$i] . "</option>\n";
   }

   $str .= "</select>";

   return $str;
}

function select_fee($selected)
{
   global $db;

   $str = "<input type=hidden name=fee_old value=$selected>\n";
   $str .= "<select name=fee title=\"Velg medlemskontingent\">\n";

   for ($i = 0; $i < count($db->per_fee); $i++)
   {
      $str .= "<option value=$i";
      if ($selected == $i)
         $str .= " selected";
      $str .= ">" . $db->per_fee[$i] . "</option>\n";
   }

   $str .= "</select>";

   return $str;
}

function select_def_pos($selected)
{
   global $db;

   $str = "<select name=def_pos title=\"Standard stemme/plassering, gjelder typisk blåsere\">\n";

   $str .= "<option value=null>N/A</option>";

   for ($i = 1; $i < 5; $i++)
   {
      $str .= "<option value=$i";
      if ($selected == $i)
         $str .= " selected";
      $str .= ">$i</option>\n";
   }

   $str .= "</select>";

   return $str;
}

function select_status_log($selected)
{
   global $db;

   $str = "<select name=status title=\"Registreringsstatus.\nInfo: Synlig også vedkommende selv\">";

   for ($i = 0; $i < count($db->rec_stat); $i++)
   {
      $str .= "<option value=$i";
      if ($selected == $i)
         $str .= " selected";
      $str .= ">" . $db->rec_stat[$i] . "</option>\n";
   }

   $str .= "</select>";

   return $str;
}

if ($action == 'update_pers')
{
   $birthday = strtotime($_POST['birthday']);
   $now = strtotime("now");

   try
   {
      if ($no == NULL)
      {
         $query = "insert into person (id_instruments, firstname, middlename, lastname, sex, address, 
              postcode, city, email, uid, password, def_pos, 
              phone1, phone2, phone3, status, fee, birthday, comment)
              values (" . request('id_instruments') . ", " . $db->qpost('firstname') . ", 
                      " . $db->qpost('middlename') . ", " . $db->qpost('lastname') . ", " . request('sex') . ", " . $db->qpost('address') . ",
                      " . request('postcode') . ", " . $db->qpost('city') . ", " . $db->qpost('email') . ",
                      " . $db->qpost('email') . ", MD5('OSO'),
                      " . request('def_pos') . ",
                      " . $db->qpost('phone1') . ", " . $db->qpost('phone2') . ", " . $db->qpost('phone3') . ", 
                      " . $db->qpost('status') . ", " .$db->qpost('fee') . ", $birthday, " . $db->qpost('comment') . ")";
         $db->query($query);
         $no = $db->lastInsertId();
         $db->query("insert into record (ts, status, comment, id_person) " .
                 "values ($now, $db->rec_stat_info, 'Ny status: " . $db->per_stat[$_POST['status']] . "', $no)");
      }
      else
      {
         if ($delete != NULL)
         {
            $s = $db->query("select id from participant where id_person = $no");
            if ($s->rowCount() > 0)
            {
               echo "<font color=red>Kan ikke slettes siden vedkommende allerede har vært med på et prosjekt!</font>";
            }
            else
            {
               $db->query("delete from record where id_person = $no");
               $query = "DELETE FROM person WHERE id = $no";
               $result = $db->query($query);
               $no = NULL;
               update_htpasswd();
            }
         }
         else
         {
            $query = "update person set ";
            if ($access->auth(AUTH::MEMB_RW))
               $query .= "firstname = " . $db->qpost('firstname') . "," .
                       "middlename = " . $db->qpost('middlename') . "," .
                       "lastname = " . $db->qpost('lastname') . "," .
                       "sex = " . request('sex') . "," .
                       "def_pos = " . request('def_pos') . ",";
            $query .= "address = " . $db->qpost('address') . "," .
                    "postcode = " . request('postcode') . "," .
                    "city = " . $db->qpost('city') . "," .
                    "email = " . $db->qpost('email') . "," .
                    "phone1 = " . $db->qpost('phone1') . "," .
                    "phone2 = " . $db->qpost('phone2') . "," .
                    "phone3 = " . $db->qpost('phone3') . "," .
                    "birthday = $birthday,";
            if ($access->auth(AUTH::MEMB_RW))
               $query .= "status = " . request('status') . "," .
                       "fee = " . request('fee') . "," .
                       "id_instruments = " . request('id_instruments') . ",";
            $query .= "comment = " . $db->qpost('comment') . " " .
                    "where id = $no";
            $db->query($query);
            if (request('status') != request('status_old'))
               $db->query("insert into record (ts, status, comment, id_person) " .
                       "values ($now, $db->rec_stat_info, 'Ny status: " . $db->per_stat[request('status')] . "', $no)");
            if (request('fee') != request('fee_old'))
               $db->query("insert into record (ts, status, comment, id_person) " .
                       "values ($now, $db->rec_stat_info, 'Endret medlemskontingent: " . $db->per_fee[request('fee')] . "', $no)");
         }
      }
   } catch (PDOException $ex)
   {
      echo "<font color=red>Failed to update</font>";
   }
}

function update_htpasswd()
{
   global $db;

   $stmt = $db->query("select uid, password from person");
   $fw = fopen("conf/.htpasswd", "w");

   foreach ($stmt as $e)
      if (strlen($e['uid']) > 0)
         fwrite($fw, $e['uid'] . ":" . $e['password'] . "\n");

   fclose($fw);
}

function update_pwd($no)
{
   global $db;

   $s = $db->query("select id from person where not id = $no and uid = '" . request('uid2') . "'");
   if ($s->rowCount() > 0)
   {
      echo "<font color=red>Ikke oppdatert, brukeren finnes fra før!</font>";
      return;
   }

   if (strlen(request('uid2')) < 2)
   {
      echo "<font color=red>Ikke oppdatert, brukeren må bestå av minst 2 bokstaver!</font>";
      return;
   }

   if (request('pwd1') != request('pwd2'))
   {
      echo "<font color=red>Ikke oppdatert, passordene må være like!</font>";
      return;
   }

   if (strlen(request('pwd1')) == 0)
   {
      echo "<font color=red>Ikke oppdatert, passord må ha minst 1 bokstav</font>";
      return;
   }

   if (preg_match("/[^A-Za-z0-9]/", request('uid2')))
   {
      echo "<font color=red>Ugyldig brukernavn. Gyldige tegn: A-Z, a-z, 0-9</font>";
      return;
   }

   $pwd = request('pwd1');
   $hash_pwd = crypt($pwd, base64_encode($pwd));
   $query = "update person set uid = " . $db->qpost('uid2') . ", password = " . $db->quote($hash_pwd) .
           " where id = $no";
   try
   {
      $db->query($query);
   } catch (PDOExeption $ex)
   {
      echo "<font color=red>Failed to update</font>";
   }
   $stmt = $db->query("select uid from person where id = $no");
   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   update_htpasswd();
}

if ($action == 'update_pwd')
   update_pwd($no);

$row = array(
    'id' => 0,
    'id_instruments' => 0,
    'instrument' => '',
    'firstname' => '',
    'middlename' => '',
    'lastname' => '',
    'uid' => '',
    'sex' => ' ',
    'fee' => $db->per_fee_free,
    'def_pos' => 0,
    'address' => '',
    'postcode' => 0,
    'city' => '',
    'email' => '',
    'phone1' => '',
    'phone2' => '',
    'phone3' => '',
    'status' => $db->per_stat_apply,
    'fee' => $db->per_fee_free,
    'comment' => '',
    'comment_dir' => '',
    'birthday' => 0,
);

if (!is_null($no))
{
   $query = "SELECT person.id as id, id_instruments, instrument, firstname, middlename, lastname, " .
           "sex, fee, uid, address, postcode, city, def_pos, " .
           "email, phone1, phone2, phone3, status, person.comment as comment, " .
           "comment_dir, status_dir, birthday " .
           "FROM person, instruments " .
           "where id_instruments = instruments.id " .
           "and person.id = $no";

   $stmt = $db->query($query);
   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   $query2 = "select view.name as name "
           . "from view, auth_person, person "
           . "where view.id = auth_person.id_view "
           . "and auth_person.id_person = person.id "
           . "and person.id = $no";
   $stmt2 = $db->query($query2);
}

$person = is_null($no) ? "Ny person" : $row['firstname'] . " " . $row['middlename'] . " " . $row['lastname'];
$postcode = sprintf("%04d", $row['postcode']);

echo "<h1>$person</h1>\n";

echo "Oversikt over dine personopplysninger. Disse kan du oppdatere forløpende. 
	Her bestemmer du brukernavn og passord for din bruker. <p>";

if ($access->auth(AUTH::MEMB_RW))
{
   echo "<a href=\"person.php?_sort=$sort&f_status[]=$db->per_stat_member&f_status[]=$db->per_stat_eng&f_status[]=$db->per_stat_standin\" title=\"Til adresselisten...\"><img src=\"images/index.gif\" border=0 hspace=5></a>\n";
   echo "<a href=\"$php_self?_sort=$sort&_action=edit_pers\" title=\"Registrere ny person...\"><img src=\"images/new_inc.gif\" border=0 hspace=5 vspace=5></a>\n";
   echo "<a href=\"access.php?f_person=$no\" title=\"Endre tilgang...\"><img src=\"images/stop_red.gif\" border=0 hspace=5 vspace=5></a>\n";
}

$form = new FORM();
$tb = new TABLE('id=no_border');

$tb->th("Personalia", "style=\"text-align:left\"");

if ($action == 'edit_pers')
{
   $cell = "<input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value='$no'>
        <input type=hidden name=_action value=update_pers>
        <input type=submit value=\"Lagre\">\n";
   if ($no != null && $access->auth(AUTH::MEMB_RW))
      $cell .= "<input type=hidden name=uid value=\"" . $row['uid'] . "\">
        <input type=submit name=_delete value=slett title=\"Kan slettes fra medlemsregisteret dersom vedkommende ikke har vært med på noen prosjekter\">\n";
   $tb->th($cell, "style=\"text-align:left\"");
   $tb->tr();
   $tb->td("Navn:");
   if ($access->auth(AUTH::MEMB_RW))
   {
      $tb->td("<input type=text name=firstname size=30 value=\"" . $row['firstname'] . "\" title=\"Fornavn\">
          <input type=text name=middlename size=30 value=\"" . $row['middlename'] . "\" title=\"Mellomnavn\">
          <input type=text name=lastname size=30 value=\"" . $row['lastname'] . "\" title=\"Etternavn\">");
      $tb->tr();
      $tb->td("Kjønn:");
      $tb->td(select_sex($row['sex']));
   }
   else
   {
      $tb->td($row['firstname'] . " <i>" . $row['middlename'] . "</i> " . $row['lastname']);
   }

   $tb->tr();
   $tb->td("Instrument:");
   if ($access->auth(AUTH::MEMB_RW))
   {
      $tb->td(select_def_pos($row['def_pos']) . select_instrument($row['id_instruments']));
   }
   else
      $tb->td(is_null($row['def_pos'] ? "" : $row['def_pos'] . ". ") . $row['instrument']);
   $tb->tr();
   $tb->td("Adresse:");
   $tb->td("<input type=text name=address size=30 value=\"" . $row['address'] . "\" title=\"Adresse\">");
   $tb->tr();
   $tb->td("Post:");
   $tb->td("<input type=text name=postcode size=4 maxlength=4 value=\"$postcode\" title=\"Postnummer\">
          <input type=text name=city size=30 value=\"" . $row['city'] . "\" title=\"Poststed\">");
   $tb->tr();
   $tb->td("Mail:");
   $tb->td("<input type=text name=email size=40 value=\"" . $row['email'] . "\" title=\"Mailadresse\">");
   $tb->tr();
   $tb->td("Telefon:");
   $tb->td("mob:<input type=text name=phone1 size=12 value=\"" . $row['phone1'] . "\" title=\"Mobilnummer\">
          priv:<input type=text name=phone2 size=12 value=\"" . $row['phone2'] . "\" title=\"Privat (fasttelefon)\">
          jobb:<input type=text name=phone3 size=12 value=\"" . $row['phone3'] . "\" title=\"Evt. telefonnummer arbeidssted\">");
   $tb->tr();
   $tb->td("Status:");
   if ($access->auth(AUTH::MEMB_RW))
      $tb->td(select_status($row['status']));
   else
      $tb->td($db->per_stat[$row['status']]);
   $tb->tr();
   $tb->td("Medlemskontingent:");
   if ($access->auth(AUTH::MEMB_RW))
      $tb->td(select_fee($row['fee']));
   else
      $tb->td($db->per_fee[$row['fee']]);
   $tb->tr();
   $tb->td("Fødselsdag:");
   $tb->td("<input type=date name=birthday size=15 value=\"" . date('Y-m-d', $row['birthday']) . "\" title=\"(frivillig) Eks: 10 jan 2017\">");
   $tb->tr();
   $tb->td("Kommentar:");
   $tb->td("<input type=text name=comment size=50 value=\"" . $row['comment'] . "\" title=\"Legg inn eventuell kommentar\">");
}
else
{
   $tb->th("<input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value='$no'>
        <input type=hidden name=_action value=edit_pers>
        <input type=submit value=\"Endre\" title=\"Klikk for for å endre personalia...\">", "style=\"text-align:left\"");
   $tb->tr();
   $tb->td("Navn:");
   $tb->td($row['firstname'] . " " . $row['middlename'] . " " . $row['lastname']);
   $tb->tr();
   if ($access->auth(AUTH::MEMB_RW))
   {
      $tb->td("Kjønn:");
      $sex = (is_null($row['sex'])) ? $db->per_sex_unknown : $row['sex'];
      $tb->td($db->per_sex[$sex]);
      $tb->tr();
   }
   $tb->td("Instrument:");
   $tb->td(is_null($row['def_pos'] ? "" : $row['def_pos'] . ". ") . $row['instrument']);
   $tb->tr();
   $tb->td("Adresse:");
   $tb->td($row['address']);
   $tb->tr();
   $tb->td("Post:");
   $tb->td($postcode . " " . $row['city']);
   $tb->tr();
   $tb->td("Mail:");
   $tb->td($row['email']);
   $tb->tr();
   $tb->td("Mobil:");
   $tb->td($row['phone1']);
   $tb->tr();
   $tb->td("Privat:");
   $tb->td($row['phone2']);
   $tb->tr();
   $tb->td("Jobb:");
   $tb->td($row['phone3']);
   $tb->tr();
   $tb->td("Status:");
   $tb->td($db->per_stat[$row['status']]);
   $tb->tr();
   $tb->td("Medlemskontingent:");
   $tb->td($db->per_fee[$row['fee']]);
   $tb->tr();
   $tb->td("Fødselsdag:");
   $tb->td(strftime('%e. %b %Y', $row['birthday']));
   $tb->tr();
   $tb->td("Kommentar:");
   $tb->td($row['comment']);
}

unset($tb);
unset($form);

if (!is_null($no))
{
   echo "<p>\n";
   $form = new FORM();
   $tb = new TABLE('id=no_border');
   $tb->th("Innlogging", "style=\"text-align:left\"");
   if ($action == 'edit_pwd')
   {
      $tb->th("<input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value='$no'>
        <input type=hidden name=_action value=update_pwd>
        <input type=submit value=\"Lagre\">", "style=\"text-align:left\"");
      $tb->tr();
      $tb->td("Bruker-id:");
      $tb->td("<input type=text name=uid2 size=30 value=\"" . $row['uid'] . "\">");
      $tb->tr();
      $tb->td("Nytt passord:");
      $tb->td("<input type=password name=pwd1 size=20>");
      $tb->tr();
      $tb->td("Gjenta passord:");
      $tb->td("<input type=password name=pwd2 size=20>");
   }
   else
   {
      $tb->th("<input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value='$no'>
        <input type=hidden name=_action value=edit_pwd>
        <input type=submit value=\"Endre\" title=\"Klikk for å endre brukernavn og/eller passord...\">", "style=\"text-align:left\"");
      $tb->tr();
      $tb->td("Bruker-id:");
      $tb->td($row['uid']);
      $tb->tr();
      $tb->td("Passord:");
      $tb->td("************");
      $tb->tr();
      $tb->td("Tilgangsgruppe(r):");
      $str = '';
      $warn = ($row['status'] == $db->per_stat_quited) ? "<img src=images/caution.gif height=17>" : "";
      foreach ($stmt2 as $acc)
         $str .= $acc['name'] . "$warn<br>";
      $tb->td($str);
   }
   unset($tb);
   unset($form);
}


echo "<h3>Logg</h3>";
if ($access->auth(AUTH::MEMB_RW))
{
   $form = new FORM();
   echo "<input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_no value='$no'>
      <input type=hidden name=_action value=new_log>
      <input type=submit value=\"Legg til\" title=\"Loggføring av medlemsinformasjon...\">";
   unset($form);
}

$form = new FORM();
$tb = new TABLE('id=no_border');

if ($access->auth(AUTH::MEMB_RW))
   $tb->th("Edit");
$tb->th("Dato");
$tb->th("Type");
$tb->th("Tekst");

$rno = request('_rno');

if ($action == 'new_log')
{
   $tb->tr();
   $tb->td("<input type=hidden name=_action value=update_log>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=hidden name=_no value=$no>
    <input type=submit value=ok title=\"Klikk for registrering...\">", 'align=left');
   $tb->td("<input type=date size=15 value=\"" . date('Y-m-d') . "\" name=ts title=\"Dato for registrering (Eks.: 17. oct 17)\">");
   $tb->td(select_status_log(null));
   $tb->td("<textarea cols=60 rows=3 wrap=virtual name=comment title=\"Logginfo\"></textarea>");
}

if ($action == 'update_log' && $access->auth(AUTH::MEMB_RW))
{
   $ts = strtotime(request('ts'));

   if (is_null($rno))
      $query = "insert into record (ts, status, comment, id_person) " .
              "values ($ts, " . request('status') . ", " . $db->qpost('comment') . ", $no)";
   else
   {
      if (!is_null($delete))
      {
         $query = "delete from record where id = " . request('_rno');
      }
      else
      {
         $query = "update record set ts = $ts," .
                 "status = " . request('status') . "," .
                 "comment = " . $db->qpost('comment') . " " .
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
   if ($row['id'] != $rno)
   {
      $tb->tr();
      if ($access->auth(AUTH::MEMB_RW))
         $tb->td("<a href=\"$php_self?_sort=$sort&_action=view_log&_rno=" . $row['id'] . "&_no=$no\"><img src=\"images/cross_re.gif\" border=0></a>", 'align=left');
      $tb->td(strftime('%e. %b %Y', $row['ts']));
      $tb->td($db->rec_stat[$row['status']]);
      $tb->td(str_replace("\n", "<br>\n", $row['comment']));
   }
   else
   {
      $tb->tr();
      $tb->td("<input type=hidden name=_action value=update_log>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_rno value=$rno>
    <input type=hidden name=_no value=$no>
    <input type=submit value=ok>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette?');\">", 'no_wrap');
      $tb->td("<input type=date size=15 name=ts value=\"" . date('Y-m-d', $row['ts']) . "\" title=\"Eks: 10 dec 201\">");
      $tb->td(select_status_log($row['status']));
      $tb->td("<textarea cols=60 rows=3 wrap=virtual name=comment>" . $row['comment'] . "</textarea>");
   }
}

