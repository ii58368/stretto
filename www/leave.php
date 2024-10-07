<?php

require 'framework.php';

if (is_null($sort))
   $sort = 'ts_reg';

echo "
<h1>Permisjoner (langtid)</h1>
Her registreres permisjoner som strekker seg over en lenger tidsperiode, typisk et helt semester eller mer.<br>
Permisjoner for enkeltprosjekter registrers under Admin->Ressurser-><i>prosjektnavn</i><p>";
if ($access->auth(AUTH::LEAVE_RW))
{
   $form = new FORM();
   echo "
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Ny permisjon\" title=\"Registrer ny permisjonssøknad...\">";
   unset($form);
}

$form = new FORM();
$tb = new TABLE('border=1');

if ($access->auth(AUTH::LEAVE_RW))
   $tb->th("Edit");
$tb->th("<a href=\"$php_self?_sort=ts_reg\"title=\"Sorter på registreringsdato...\">Registrert</a>");
$tb->th("Endret");
$tb->th("Navn");
$tb->th("<a href=\"$php_self?_sort=status,ts_reg\"title=\"Sorter på søknadsstatus, deretter registreringsdato...\">Status</a>");
$tb->th("Fra");
$tb->th("Til");
$tb->th("Tekst");

function select_person($selected)
{
   global $db;

   $str = "<select name=id_person title=\"Velg medlem permisjonssøknaden skal gjelde for\">";

   $q = "SELECT person.id as id, firstname, middlename, lastname, instrument "
           . "FROM person, instruments "
           . "where (person.status = $db->per_stat_member "
           . "or person.id = $selected) "
           . "and person.id_instruments = instruments.id "
           . "order by list_order, lastname, firstname";

   $s = $db->query($q);

   foreach ($s as $e)
   {
      $str .= "<option value=\"" . $e['id'] . "\"";
      if ($e['id'] == $selected)
         $str .= " selected";
      $str .= ">" . $e['firstname'] . " " . $e['lastname'] . " (" . $e['instrument'] . ")</option>";
   }
   $str .= "</select>";

   return $str;
}

function select_status($selected)
{
   global $db;

   if (is_null($selected))
      $selected = $db->lea_stat_registered;

   $str = "<select name=status title=\"Status på permisjonssøknad\">";

   for ($i = 0; $i < count($db->lea_stat); $i++)
   {
      $str .= "<option value=$i";
      if ($selected == $i)
         $str .= " selected";
      $str .= ">" . $db->lea_stat[$i] . "</option>\n";
   }

   $str .= "</select>";

   return $str;
}

if ($action == 'new')
{
   $tb->tr();
   $tb->td("<input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=submit value=ok title=\"Lagre\">", 'align=left');
   $tb->td(strftime('%e.%b %y'));
   $tb->td(strftime('%e.%b %y'));
   $tb->td(select_person(0));
   $tb->td(select_status(null));
   $tb->td("<input type=date size=10 name=ts_from title=\"Dato permisjonssøknaden gjelder fra. Format: eks: 3. dec 2017\">");
   $tb->td("<input type=date size=10 name=ts_to title=\"Dato permisjonssøknaden gjelder til. Format: eks: 3. dec 2017\">");
   $tb->td("<textarea name=text wrap=virtual cols=60 rows=10 title=\"Fritekst\"></textarea>");
}

function update_db()
{
   global $db;
   global $no;
   global $delete;

   if (($ts_from = strtotime(request('ts_from'))) == false)
   {
      echo "<font color=red>Illegal time format: " . request('ts_from') . "</font>";
      return;
   }

   if (($ts_to = strtotime(request('ts_to'))) == false)
   {
      echo "<font color=red>Illegal time format: " . request('ts_to') . "</font>";
      return;
   }

   $ts_now = strtotime("now");

   if (is_null($no))
   {
      $query = "insert into `leave` (ts_reg, id_person, status, ts_proc, ts_from, ts_to, text)
              values ($ts_now, " . request('id_person') . ", " . request('status') . ", $ts_now, $ts_from, $ts_to, " . $db->qpost('text') . ")";
   }
   else
   {
      if (!is_null($delete))
      {
         $query = "DELETE FROM `leave` WHERE id = $no";
      }
      else
      {
         $query = "update `leave` set " .
                 "id_person = " . request('id_person') . "," .
                 "status = " . request('status') . "," .
                 "ts_proc = $ts_now," .
                 "ts_from = $ts_from," .
                 "ts_to = $ts_to," .
                 "text = " . $db->qpost('text') . " " .
                 "where id = $no";
      }
      $no = NULL;
   }
   $db->query($query);
}

if ($action == 'update' && $access->auth(AUTH::LEAVE_RW))
   update_db();

$ts_min = $season->ts()[0];
$ts_max = $season->ts()[1];

$query = "select leave.id as id, "
        . "leave.ts_reg as ts_reg, "
        . "person.id as id_person, "
        . "firstname, middlename, lastname, instrument, "
        . "leave.status as status, "
        . "leave.ts_proc as ts_proc, "
        . "leave.ts_from as ts_from, "
        . "leave.ts_to as ts_to, "
        . "leave.text as text "
        . "from `leave`, person, instruments "
        . "where leave.id_person = person.id "
        . "and person.id_instruments = instruments.id "
        . "and ((leave.ts_proc >= $ts_min and leave.ts_proc <= $ts_max) "
        . "or (leave.ts_from >= $ts_min and leave.ts_to <= $ts_max) "
        . "or (leave.ts_from < $ts_min and leave.ts_to > $ts_min) "
        . "or (leave.ts_from < $ts_max and leave.ts_to > $ts_max) "
        . "or (leave.ts_from < $ts_min and leave.ts_to > $ts_max)) "
        . "order by $sort";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   $tb->tr();
   if ($row['id'] != $no)
   {
      if ($access->auth(AUTH::LEAVE_RW))
         $tb->td("<a href=\"$php_self?_sort=$sort&_action=view&_no=" . $row['id'] . "&ts_reg=" . $season->year() . "\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for å editere...\"></a>", 'align=center');
      $tb->td(strftime('%e.%b %y', $row['ts_reg']));
      $tb->td(strftime('%e.%b %y', $row['ts_proc']));
      $tb->td($row['firstname'] . " " . $row['middlename'] . " " . $row['lastname'] . " (" . $row['instrument'] . ")");
      $tb->td($db->lea_stat[$row['status']]);
      $tb->td(strftime('%e.%b %y', $row['ts_from']));
      $tb->td(strftime('%e.%b %y', $row['ts_to']));
      $tb->td(str_replace("\n", "<br>\n", $row['text']));
   }
   else
   {
      $tb->td("<input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <input type=submit value=ok title=Lagre>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette " . date('D j.M y', $row['ts_reg']) . "?');\" title=\"Slett...\">", 'nowrap');
      $tb->td(strftime('%e.%m.%y', $row['ts_reg']));
      $tb->td(strftime('%e.%m.%y'));
      $tb->td(select_person($row['id_person']));
      $tb->td(select_status($row['status']));
      $tb->td("<input type=date size=10 name=ts_from value=\"" . date('Y-m-d', $row['ts_from']) . "\" title=\"Dato permisjonssøknaden gjelder fra. Format: eks: 3. dec 2017\">");
      $tb->td("<input type=date size=10 name=ts_to value=\"" . date('Y-m-d', $row['ts_to']) . "\" title=\"Dato permisjonssøknaden gjelder til. Format: eks: 3. dec 2017\">");
      $tb->td("<textarea cols=60 rows=10 wrap=virtual name=text title=Fritekst>" . $row['text'] . "</textarea>");
   }
}
