<?php

include 'framework.php';

$personList = "person.php";

if ($sort == NULL)
   $sort = 'list_order,-def_pos+desc,lastname,firstname';

if (is_null($no))
   $no = $whoami->id();

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

   $str = "<select name=status title=\"Medlemsstatus\">\n";

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

   $str = "<select name=fee title=\"Velg medlemskontingent\">\n";

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

function insert_log($status, $text, $person_id = null)
{
   global $db;
   global $whoami;

   $now = strtotime("now");

   if (is_null($person_id))
      $person_id = $whoami->id();

   $q = "insert into record (ts, status, comment, id_person, id_editor) " .
           "values ($now, $status, " . $db->quote($text) . ", $person_id, " . $whoami->id() . ")";
   $db->query($q);
}

function insert_pers()
{
   global $db;

   $birthday = strtotime($_POST['birthday']);
   $now = strtotime("now");
   $gdpr_ts = is_null(request('gdpr')) ? 0 : $now;

   $query = "insert into person (id_instruments, firstname, middlename, lastname, sex, address, 
              postcode, city, email, uid, password, def_pos, 
              phone1, gdpr_ts, status, fee, birthday, comment)
              values (" . request('id_instruments') . ", " . $db->qpost('firstname') . ", 
                      " . $db->qpost('middlename') . ", " . $db->qpost('lastname') . ", " . request('sex') . ", " . $db->qpost('address') . ",
                      " . request('postcode') . ", " . $db->qpost('city') . ", " . $db->qpost('email') . ",
                      " . $db->qpost('email') . ", MD5('OSO'),
                      " . request('def_pos') . ",
                      " . $db->qpost('phone1') . ", $gdpr_ts,
                      " . $db->qpost('status') . ", " . request('fee') . ", $birthday, " . $db->qpost('comment') . ")";
   echo $query;
   $rc = $db->query($query);
   $no = $db->lastInsertId();
   
   if ($rc == TRUE)
   {
      insert_log($db->rec_stat_info, 'Registrert, ny status: ' . $db->per_stat[$_POST['status']], $no);
      $id_visma = $no + 10000;
      $db->query("update person set id_visma = $id_visma where id = $no");    
   }

   return $no;
}

function log_if_changed($status, $text, $no, $e, $field1, $field2 = null, $field3 = null)
{
   $rfield1 = request($field1);
   if (is_null($rfield1))
      return;
   if (request($field1) == 'null')
      $rfield1 = '';
   if ($rfield1 != $e[$field1] || ($field2 != null && request($field2) != $e[$field2]) || ($field3 != null && request($field3) != $e[$field3]))
      insert_log($status, $text, $no);
}

function log_changes($no)
{
   global $db;
   global $access;
   global $whoami;

   $s = $db->query("select * from person where id = $no");
   $e = $s->fetch(PDO::FETCH_ASSOC);

   log_if_changed($db->rec_stat_board, 'Oppdatert Navn', $no, $e, 'firstname', 'middlename', 'lastname');
   log_if_changed($db->rec_stat_board, 'Oppdatert stemmegruppe', $no, $e, 'id_instruments');
   log_if_changed($db->rec_stat_board, 'Oppdatert standard stemmegruppeplassering', $no, $e, 'def_pos');
   
   if (!is_null(request('status')))
      log_if_changed($db->rec_stat_info, 'Ny status: ' . $db->per_stat[request('status')], $no, $e, 'status');
   if (!is_null(request('fee')))
      log_if_changed($db->rec_stat_board, 'Endret medlemskontingent fra ' . $db->per_fee[$e['fee']] . ' til ' . $db->per_fee[request('fee')], $no, $e, 'fee');
 
   if ($whoami->id() == $no)
   {
      if (is_null(request('gdpr')) && $e['gdpr_ts'] > 0)
         insert_log($db->rec_stat_board, "Aksepterer ikke lenger at OSO kan behandle min kontaktinformasjonen for spesifikke formål.", $no);
      if (!is_null(request('gdpr')) && $e['gdpr_ts'] == 0)
         insert_log($db->rec_stat_board, "Samtykker til at OSO kan behandle min kontaktinformasjonen for spesifikke formål.", $no);
   }
   
   log_if_changed($db->rec_stat_board, 'Oppdatert adresse', $no, $e, 'address', 'postcode', 'city');
   log_if_changed($db->rec_stat_board, 'Oppdatert e-post adresse', $no, $e, 'email');
   log_if_changed($db->rec_stat_board, 'Oppdatert telefonnummer', $no, $e, 'phone1');
   log_if_changed($db->rec_stat_board, 'Oppdatert kommentar', $no, $e, 'comment');
}

function update_pers($no)
{
   global $delete;
   global $access;
   global $db;
   global $action;
   global $whoami;

   if ($delete != NULL)
   {
      $s = $db->query("select id_person from participant where id_person = $no");
      if ($s->rowCount() > 0)
      {
         echo "<font color=red>Kan ikke slettes siden vedkommende allerede har vært med på et prosjekt!</font>";
         $action = 'view';
         $delete = null;
      }
      else
      {
         $db->query("delete from auth_person where id_person = $no");
         $db->query("delete from record where id_person = $no");
         $query = "DELETE FROM person WHERE id = $no";
         $result = $db->query($query);
         $no = NULL;
         update_htpasswd();
      }
   }
   else
   {
      $birthday = strtotime($_POST['birthday']);
      $now = strtotime("now");
      $gdpr_ts = is_null(request('gdpr')) ? 0 : $now;

      log_changes($no);

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
              "birthday = $birthday,";
      if ($whoami->id() == $no)
      {
         $query .= "confirmed_ts = $now," .
                 "gdpr_ts = IF(gdpr_ts > 0 AND $gdpr_ts > 0, gdpr_ts, $gdpr_ts),";
      }
      if ($access->auth(AUTH::MEMB_RW))
         $query .= "status = " . request('status') . ",";
      if (request('fee') != NULL)
         $query .= "fee = " . request('fee') . ",";
      if ($access->auth(AUTH::MEMB_RW))
      {
         $query .= "id_instruments = " . request('id_instruments') . ",";
         $id_visma = request('id_visma');
         if (is_numeric($id_visma))
            $query .= "id_visma = $id_visma,";
      }
      $query .= "comment = " . $db->qpost('comment') . " " .
              "where id = $no";
      $db->query($query);
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

   insert_log($db->rec_stat_board, "Oppdatert passord", $no);

   update_htpasswd();
}

function get_gdpr($gdpr_ts)
{
   if ($gdpr_ts > 0)
      return "Samtykker til at OSO kan behandle informasjonen min for spesifikke formål, og jeg kan trekke tilbake samtykket når som helst.";
   return "Aksepterer <b>ikke</b> at OSO kan behandle informasjonen min for spesifikke formål";
}

if ($action == 'update_pers')
   update_pers($no);
if ($action == 'insert_pers')
   $no = insert_pers();
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
    'sex' => $db->per_sex_unknown,
    'fee' => $db->per_fee_free,
    'def_pos' => 0,
    'address' => '',
    'postcode' => 0,
    'city' => '',
    'email' => '',
    'phone1' => '',
    'status' => $db->per_stat_apply,
    'fee' => $db->per_fee_free,
    'comment' => '',
    'comment_dir' => '',
    'birthday' => 0,
    'gdpr_ts' => 0,
    'confirmed_ts' => 0,
    'id_visma' => 0
);

$do_lookup = !($action == 'new_pers' || ($action == 'update_pers' && !is_null($delete)));

if ($do_lookup)
{
   $query = "SELECT person.id as id, id_instruments, instrument, firstname, middlename, lastname, " .
           "sex, fee, uid, address, postcode, city, def_pos, " .
           "email, phone1, status, person.comment as comment, " .
           "comment_dir, status_dir, birthday, " .
           "gdpr_ts, id_visma, confirmed_ts " .
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

$postcode = sprintf("%04d", $row['postcode']);

if ($action == 'new_pers')
{
   echo "<h1>Ny person</h1>\n";
   echo "Ta gjerne en sjekk på om personen finnes fra før du registrerer en ny.";
}
else
{
   if (is_null($delete))
   {
      echo "<h1>" . $row['firstname'] . " " . $row['middlename'] . " " . $row['lastname'] . "</h1>\n";
      echo "Oversikt over dine personopplysninger. Disse kan du oppdatere forløpende. 
	Her bestemmer du brukernavn og passord for din bruker.";
   }
   else
   {
      echo "<h1>Slettet</h1>";
   }
}
echo "<p>\n";

if ($access->auth(AUTH::MEMB_RW))
{
   echo "<a href=\"person.php?_sort=$sort&f_status[]=$db->per_stat_member&f_status[]=$db->per_stat_eng&f_status[]=$db->per_stat_standin\" title=\"Til adresselisten...\"><img src=\"images/index.gif\" border=0 hspace=5></a>\n";
   echo "<a href=\"$php_self?_sort=$sort&_action=new_pers\" title=\"Registrere ny person...\"><img src=\"images/new_inc.gif\" border=0 hspace=5 vspace=5></a>\n";
   if ($action != 'new_pers' && is_null($delete))
      echo "<a href=\"access.php?f_person=$no\" title=\"Endre tilgang...\"><img src=\"images/stop_red.gif\" border=0 hspace=5 vspace=5></a>\n";
}

$form = new FORM();
$tb = new TABLE('id=no_border');

$tb->th("Personalia", "style=\"text-align:left\"");

if ($action == 'edit_pers' || $action == 'new_pers')
{
   $action_next = ($action == 'edit_pers') ? 'update_pers' : 'insert_pers';
   $cell = "<input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value='$no'>
        <input type=hidden name=_action value='$action_next'>
        <input type=submit value=\"Lagre\">\n";
   if ($action != 'new_pers' && $access->auth(AUTH::MEMB_RW))
      $cell .= "<input type=hidden name=uid value=\"" . $row['uid'] . "\">
        <input type=submit name=_delete value=slett title=\"Kan slettes fra medlemsregisteret dersom vedkommende ikke har vært med på noen prosjekter\" onClick=\"return confirm('Sikkert at du vil slette?');\">\n";
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
   $tb->td("Mobil:");
   $tb->td("<input type=text name=phone1 size=12 value=\"" . $row['phone1'] . "\" title=\"Mobilnummer\">");
   $tb->tr();
   $tb->td("Status:");
   if ($access->auth(AUTH::MEMB_RW))
      $tb->td(select_status($row['status']));
   else
      $tb->td($db->per_stat[$row['status']]);
   $tb->tr();
   $tb->td("Medlemskontingent:");
   $fee_selectable  = ($row['status'] == $db->per_stat_member) || $access->auth(AUTH::MEMB_RW);
   $tb->td($fee_selectable ? select_fee($row['fee']) : $db->per_fee[$row['fee']]);
   $tb->tr();
   $tb->td("Fødselsdag:");
   $tb->td("<input type=date name=birthday size=15 value=\"" . date('Y-m-d', $row['birthday']) . "\" title=\"Nødvendig for å kunne rapportere VO-midler\">");
   $tb->tr();
   $tb->td("Samtykke:");
   if ($action == 'edit_pers' && $whoami->id() == $no)
   {
      $checked = ($row['gdpr_ts'] > 0) ? 'checked' : '';
      $cell = "<input type=checkbox name=gdpr $checked title=\"Kryss av for å godkjenne samtykke.\">"
              . "Samtykker til at OSO kan behandle min informasjonen min for spesifikke formål, og jeg kan trekke tilbake samtykket når som helst. "
              . "<a href=\"Personvernerklaering_Oslo_symfoniorkester_v1.pdf\">Personvern</a><br>"
              . "Det er viktig for OSO at du godkjenner samtykke for at orkesteret skal få økonomisk støtte gjennom VO-midler";
   }
   else
   {
      $cell = get_gdpr($row['gdpr_ts']);
   }
   $tb->td($cell);
   
   if ($action == 'edit_pers' && $access->auth(AUTH::MEMB_RW))
   {
      $tb->tr();
      $tb->td("Visma kundenummer:");
      $tb->td("<input type=number min=0 name=id_visma value=\"" . $row['id_visma'] . "\" title=\"Kundenummer i Visma\">");
   }

   $tb->tr();
   $tb->td("Kommentar:");
   $tb->td("<input type=text name=comment size=50 value=\"" . $row['comment'] . "\" title=\"Legg inn eventuell kommentar\">");
}
else
{
   $cell = ($do_lookup) ? "<input type=hidden name=_sort value='$sort'>
        <input type=hidden name=_no value='$no'>
        <input type=hidden name=_action value=edit_pers>
        <input type=submit value=\"Endre\" title=\"Klikk for for å endre personalia...\">" : "";
   $tb->th($cell, "style=\"text-align:left\"");
   $tb->tr();
   $tb->td("Navn:");
   $tb->td($row['firstname'] . " " . $row['middlename'] . " " . $row['lastname']);
   $tb->tr();
   if ($access->auth(AUTH::MEMB_RW))
   {
      $tb->td("Kjønn:");
      $tb->td($row['sex'] != '' ? $db->per_sex[$row['sex']] : '');
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
   $tb->td("Status:");
   $tb->td($db->per_stat[$row['status']]);
   $tb->tr();
   $tb->td("Medlemskontingent:");
   $tb->td($db->per_fee[$row['fee']]);
   $tb->tr();
   $tb->td("Fødselsdag:");
   $tb->td(strftime('%e. %b %Y', $row['birthday']));
   $tb->tr();
   $tb->td("Samtykke:");
   $tb->td(get_gdpr($row['gdpr_ts']));
   if ($access->auth(AUTH::MEMB_RW))
   {
      $tb->tr();
      $tb->td("Visma kundenummer:");
      $tb->td($row['id_visma']);
      
      $tb->tr();
      $tb->td("Oppdatert:");
      $tb->td(strftime('%e. %b %Y', $row['confirmed_ts']));
   }
   
   $tb->tr();
   $tb->td("Kommentar:");
   $tb->td($row['comment']);
}

unset($tb);
unset($form);

if ($do_lookup)
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
      $warn = ($row['status'] == $db->per_stat_quited or $row['status'] == $db->per_stat_removed) ? "<img src=images/caution.gif height=17 title=\"Vedkommende har tilgang selv om status er sluttet eller slettet\">" : "";
      foreach ($stmt2 as $acc)
         $str .= $acc['name'] . "$warn<br>";
      $tb->td($str);
   }
   unset($tb);
   unset($form);
}

if ($do_lookup)
{
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
         $query = "insert into record (ts, status, comment, id_person, id_editor) " .
                 "values ($ts, " . request('status') . ", " . $db->qpost('comment') . ", $no, " . $whoami->id() . ")";
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
                    "comment = " . $db->qpost('comment') . ", " .
                    "id_editor = " . $whoami->id() . " " .
                    "where id = $rno";
         }
         $rno = null;
      }
      $db->query($query);
   }

   $query = "select record.id as id, "
           . "record.ts as ts, "
           . "record.status as status, "
           . "record.comment as comment, "
           . "person.firstname as firstname, "
           . "person.lastname as lastname "
           . "from record left join person "
           . "on record.id_editor = person.id "
           . "where record.id_person = $no ";
   if (!$access->auth(AUTH::BOARD_RO))
      $query .= "and record.status = $db->rec_stat_info ";
   $query .= "order by ts desc";

   $stmt = $db->query($query);

   foreach ($stmt as $row)
   {
      if ($row['id'] != $rno)
      {
         $tb->tr();
         if ($access->auth(AUTH::MEMB_RW))
         {
            $title = "Click to edit...\nLast changed by " . $row['firstname'] . " " . $row['lastname'];
            $tb->td("<a href=\"$php_self?_sort=$sort&_action=view_log&_rno=" . $row['id'] . "&_no=$no\" title=\"$title\"><img src=\"images/cross_re.gif\" border=0></a>", 'align=left');
         }
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
}
