<?php

require 'framework.php';
require 'person_query.php';

$pedit = "personEdit.php";

if (is_null($sort))
   $sort = 'list_order,-def_pos+desc,lastname,firstname';

function send_mail($r)
{
   $recip = is_null(request('f_group')) ? 'bcc' : 'to';

   reset($r);
   echo "<a href=\"mailto:?$recip=";
   foreach ($r as $e)
      if (strlen($e['email']) > 0)
         echo $e['email'] . ",";
   echo "&subject=OSO: \"><img border=0 src=images/send_mail.gif hspace=5 vspace=5 title=\"Send mail til alle på listen...\"></a>\n";
}

function format_phone($ph)
{
   $ph = str_replace(' ', '', $ph);
   $ph = substr($ph, 0, -5) . " " . substr($ph, -5, 2) . " " . substr($ph, -3);
   if (strlen($ph) > 9)
      $ph = substr($ph, 0, -10) . " " . substr($ph, -10);
   return $ph;
}

function select_filter()
{
   global $db;
   global $sort;
   global $season;

   $gen_htxt = "Ctrl-klikk for å velge/velge bort flere valg samtidig.";
   // Select status
   echo "<select name=\"f_status[]\" multiple size=3 onChange=\"submit();\" title=\"Filter for medlemsstatus...\nMerk: default valg er medlem og engasjert\n$gen_htxt\">\n";

   for ($i = 0; $i < count($db->per_stat); $i++)
   {
      echo "<option value=$i";
      if (!is_null(request('f_status')))
         foreach (request('f_status') as $f_status)
            if ($f_status == $i)
               echo " selected";
      echo ">" . $db->per_stat[$i] . "</option>\n";
   }

   echo "</select>\n";

   // Instrument
   echo "<select name=\"f_instrument[]\" multiple size=3 onChange=\"submit();\" title=\"Filter for instrumentgruppe...\n$gen_htxt\">\n";

   $s = $db->query("select id, instrument from instruments order by list_order");
   foreach ($s as $e)
   {
      echo "<option value=" . $e['id'];
      if (!is_null(request('f_instrument')))
         foreach (request('f_instrument') as $f_instrument)
            if ($f_instrument == $e['id'])
               echo " selected";
      echo ">" . $e['instrument'] . "</option>\n";
   }

   echo "</select>\n";

// Groups
   echo "<select name=\"f_group[]\" multiple size=3 onChange=\"submit();\" title=\"Filter for gruppering registrert under grupper...\n$gen_htxt\">\n";

   $s = $db->query("select id, name from groups, member "
           . "where groups.id = member.id_groups group by id order by name");
   foreach ($s as $e)
   {
      echo "<option value=" . $e['id'];
      if (!is_null(request('f_group')))
         foreach (request('f_group') as $f_group)
            if ($f_group == $e['id'])
               echo " selected";
      echo ">" . $e['name'] . "</option>\n";
   }

   echo "</select>\n";

   // Project
   echo "<select name=\"f_project[]\" multiple size=3 onChange=\"submit();\" title=\"Filter for prosjekt...\n$gen_htxt\">\n";

   $q = "select id, name, year, semester from project "
           . "where (year = " . $season->year() . " "
           . "and semester = '" . $season->semester() . "' "
           . "and (status = $db->prj_stat_real "
           . "or status = $db->prj_stat_internal "
           . "or status = $db->prj_stat_canceled)) ";
   if (!is_null(request('f_project')))
      foreach (request('f_project') as $f_project)
         $q .= "or id = $f_project ";
   $q .= "order by id";

   $s = $db->query($q);
   foreach ($s as $e)
   {
      echo "<option value=" . $e['id'];
      if (!is_null(request('f_project')))
         foreach (request('f_project') as $f_project)
            if ($f_project == $e['id'])
               echo " selected";
      echo ">" . $e['name'] . " (" . $e['semester'] . $e['year'] . ")</option>\n";
   }

   echo "</select>\n";

   echo "<input type=hidden name=_sort value=\"$sort\">\n";
}

function get_filter_as_url()
{
   $filter = '';

   if (!is_null(request('f_status')))
      foreach (request('f_status') as $f_status)
         $filter .= "&f_status[]=$f_status";
   if (!is_null(request('f_instrument')))
      foreach (request('f_instrument') as $f_instrument)
         $filter .= "&f_instrument[]=$f_instrument";
   if (!is_null(request('f_group')))
      foreach (request('f_group') as $f_group)
         $filter .= "&f_group[]=$f_group";
   if (!is_null(request('f_project')))
      foreach (request('f_project') as $f_project)
         $filter .= "&f_project[]=$f_project";

   if (!is_null(request('showlog')))
      $filter .= "&showlog=true";

   return $filter;
}

function date2str($ts, $limit)
{
   if ($ts == 0 || $ts == -3600)
      return ($limit < 0) ? '' : '<font color=red>Ubekreftet</font>';

   $sdate = strftime('%e. %b %Y', $ts);

   if ($limit < 0)
      return $sdate;
   if ($ts < $limit)
      return "<font color=red>$sdate</font>";

   return $sdate;
}

function count_person($a)
{
   $old_id = 0;
   $count = 0;
   
   foreach ($a as $e)
   {
      if ($e['id'] != $old_id)
      {
         $count++;
         $old_id = $e['id'];
      }
   }
   
   return $count;
}

echo "
    <h1>Adresseliste</h1>
    Oversikt og kontaktinformasjon til orkesterets medlemmer. 
    Ønsker du å skrive ut listen trykker du på pdf-ikonet 
    til venstre over listen. <p>";
if (!is_null(request('f_project')))
{
   $query = "select name, semester, year from project where ";
   foreach (request('f_project') as $f_project)
      $query .= "project.id = $f_project or ";
   $query .= "false order by year DESC,semester";
   $stmt = $db->query($query);
   echo "<h2>";
   foreach ($stmt as $e)
      echo $e['name'] . " (" . $e['semester'] . $e['year'] . ") ";
   echo "</h2>\n";
}

$query = (request('showlog') && $access->auth(AUTH::MEMB_GREP)) ? log_query(request('logg')) : person_query();

$stmt = $db->query($query);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$f_filter = get_filter_as_url();

if ($access->auth(AUTH::MEMB_RW))
   echo "<a href=\"$pedit?_sort=$sort&_action=new_pers$f_filter\" title=\"Registrer ny person...\"><img src=\"images/new_inc.gif\" border=0 hspace=5 vspace=5></a>\n";

echo "<a href=\"person_pdf.php?_sort=list_order,-def_pos+desc,lastname,firstname$f_filter\" title=\"PDF versjon...\"><img src=images/pdf.jpeg height=22 border=0 hspace=5 vspace=5></a>\n";

if ($access->auth(AUTH::MEMB_RW))
{
   send_mail($result);
}
if ($access->auth(AUTH::CONT_RO))
{
   echo "<a href=\"personExcel.php?_sort=$sort&$f_filter\" ><img border=0 src=images/excel.png height=20 hspace=5 vspace=5 title=\"Excel fil for innrapportering til VO...\"></a>\n";
}

$form = new FORM();
if ($access->auth(AUTH::MEMB_GREP))
{
   select_filter();  
   echo "<font color=green>" . count_person($result) . " treff</font>\n";
}
if ($access->auth(AUTH::MEMB_RW))
{
   echo "<input type=checkbox name=showlog title=\"Vis logg\" onChange=\"submit();\"";
   if (!is_null(request('showlog')))
      echo " checked";
   echo ">\n";
}
unset($form);

$tb = new TABLE('border=1');

if ($access->auth(AUTH::MEMB_RW))
   $tb->th('Edit');

if (request('showlog') && $access->auth(AUTH::MEMB_GREP))
{
   $tb->th("<a href=\"$php_self?_sort=list_order,-def_pos+desc,lastname,firstname$f_filter\" title=\"Sorter på instrumentgruppe...\">Instrument</a>");
   $tb->th("<a href=\"$php_self?_sort=firstname,lastname$f_filter\" title=\"Sorter på fornavn...\">For</a>/
                          <a href=\"$php_self?_sort=lastname,firstname$f_filter\" title=\"Sorter på etternavn...\">Etternavn</a>");
   $tb->th("<a href=\"$php_self?_sort=uid$f_filter\" title=\"Sorter på Bruker-id...\">UID</a>");
   $tb->th("<a href=\"$php_self?_sort=status,list_order,-def_pos+desc,lastname,firstname$f_filter\" title=\"Sorter på status...\">Status</a>");
   $tb->th("<a href=\"$php_self?_sort=birthday$f_filter\" title=\"Sorter på Fødselsdag...\">Fødtselsdag</a>");
   $tb->th("<a href=\"$php_self?_sort=fee,list_order,-def_pos+desc,lastname,firstname$f_filter\" title=\"Sorter på type kontingent...\">Kontingent</a>");
   $tb->th("<a href=\"$php_self?_sort=gdpr_ts,list_order,-def_pos+desc,lastname,firstname$f_filter\" title=\"Sorter på dato for samtykke...\">Samtykke</a>");
   $tb->th("<a href=\"$php_self?_sort=confirmed_ts,list_order,-def_pos+desc,lastname,firstname$f_filter\" title=\"Sorter på dato for bekreftelse av personopplysninger...\">Oppdatert</a>");
   $tb->th("Kommentar");
   $tb->th("<a href=\"$php_self?_sort=$sort$f_filter&logg=full\" title=\"Vis full logg\">Logg</a>");

   $old_id = 0;
   $log = '';

   foreach ($stmt as $row)
   {
      if ($old_id != $row['id'])
      {
         if ($old_id != 0)
            $tb->td($log);

         $tb->tr();
         if ($access->auth(AUTH::MEMB_RW))
            $tb->td("<a href=\"$pedit?_sort=$sort&_action=view&_no=" . $row['id'] . $f_filter . "\"><img src=\"images/cross_re.gif\" border=0 title=\"Editere person...\"></a>", 'align=center');
         $tb->td($row['instrument']);
         $tb->td($row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname']);
         $tb->td($row['uid']);
         $tb->td($db->per_stat[$row['status']]);
         $tb->td(date2str($row['birthday'], -1), 'align=right');
         $tb->td($db->per_fee[$row['fee']]);
         $tb->td(date2str($row['gdpr_ts'], strtotime("-1 year")), 'align=right');
         $tb->td(date2str($row['confirmed_ts'], strtotime("-6 months")), 'align=right');
         $tb->td($row['comment']);
         $old_id = $row['id'];
         $log = '';
      }
      if ($row['rts'] > 0)
         $log .= strftime('%e. %b %Y', $row['rts']) . ' ' . $row['rcomment'] . "<br>\n";
   }
   if ($old_id != 0)
      $tb->td($log);
}
else
{
   $tb->th("<a href=\"$php_self?_sort=list_order,-def_pos+desc,lastname,firstname$f_filter\" title=\"Sorter på instrumentgruppe...\">Instrument</a>");
   $tb->th("<a href=\"$php_self?_sort=firstname,lastname$f_filter\" title=\"Sorter på fornavn...\">For</a>/
                          <a href=\"$php_self?_sort=lastname,firstname$f_filter\" title=\"Sorter på etternavn...\">Etternavn</a>");
   $tb->th("<a href=\"$php_self?_sort=address,lastname,firstname$f_filter\" title=\"Sorter på addresse...\">Adresse</a>");
   $tb->th("<a href=\"$php_self?_sort=postcode,lastname,firstname$f_filter\" title=\"Sorter på postnummer...\">Postnr</a>");
   $tb->th("<a href=\"$php_self?_sort=city,lastname,firstname$f_filter\" title=\"Sorter på sted...\">Sted</a>");
   $tb->th("Email");
   $tb->th("Mobil");
   $tb->th("<a href=\"$php_self?_sort=status,list_order,-def_pos+desc,lastname,firstname$f_filter\" title=\"Sorter på status...\">Status</a>");

   reset($result);

   foreach ($result as $row)
   {
      $tb->tr();
      if ($access->auth(AUTH::MEMB_RW))
         $tb->td("<a href=\"$pedit?_sort=$sort&_action=view&_no=" . $row['id'] . $f_filter . "\"><img src=\"images/cross_re.gif\" border=0 title=\"Editere person...\"></a>", 'align=center');
      $tb->td($row['instrument']);
      $tb->td($row['firstname'] . " " . $row['middlename'] . " " . $row['lastname']);
      $tb->td($row['address']);
      $tb->td(sprintf("%04d", $row['postcode']));
      $tb->td($row['city']);
      $tb->td("<a href=\"mailto:" . $row['email'] . "?subject=OSO:\">" . $row['email'] . "</a>");
      $tb->td(format_phone($row['phone1']), 'nowrap');
      $tb->td($db->per_stat[$row['status']]);
   }
}
