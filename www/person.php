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

   return $filter;
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


$query = person_query();

$stmt = $db->query($query);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$f_filter = get_filter_as_url();

if ($access->auth(AUTH::MEMB_RW))
   echo "<a href=\"$pedit?_sort=$sort&_action=edit_pers$f_filter\" title=\"Registrer ny person...\"><img src=\"images/new_inc.gif\" border=0 hspace=5 vspace=5></a>\n";

echo "<a href=\"person_pdf.php?_sort=list_order,-def_pos+desc,lastname,firstname$f_filter\" title=\"PDF versjon...\"><img src=images/pdf.jpeg height=22 border=0 hspace=5 vspace=5></a>\n";

if ($access->auth(AUTH::MEMB_RW, AUTH::MEMB_GREP))
{
   send_mail($result);
   echo "<a href=\"personExcel.php?_sort=$sort&$f_filter\" ><img border=0 src=images/excel.png height=20 hspace=5 vspace=5 title=\"Excel fil for innrapportering til VO...\"></a>\n";
   echo "<form action=\"$php_self\" method=post>\n";
   select_filter();
   echo "<font color=green>" . count($result) . " treff</font>\n";
   echo "</form>\n";
}

$tb = new TABLE('border=1');

if ($access->auth(AUTH::MEMB_RW))
   $tb->th('Edit');

$tb->th("<a href=\"$php_self?_sort=list_order,-def_pos+desc,lastname,firstname$f_filter\" title=\"Sorter på instrumentgruppe...\">Instrument</a>");
$tb->th("<a href=\"$php_self?_sort=firstname,lastname$f_filter\" title=\"Sorter på fornavn...\">For</a>/
                          <a href=\"$php_self?_sort=lastname,firstname$f_filter\" title=\"Sorter på etternavn...\">Etternavn</a>");
$tb->th("<a href=\"$php_self?_sort=address,lastname,firstname$f_filter\" title=\"Sorter på addresse...\">Adresse</a>");
$tb->th("<a href=\"$php_self?_sort=postcode,lastname,firstname$f_filter\" title=\"Sorter på postnummer...\">Postnr</a>");
$tb->th("<a href=\"$php_self?_sort=city,lastname,firstname$f_filter\" title=\"Sorter på sted...\">Sted</a>");
$tb->th("Email");
$tb->th("Mobil");
$tb->th("Priv");
$tb->th("Arbeid");
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
   $tb->td($row['phone2']);
   $tb->td($row['phone3']);
   $tb->td($db->per_stat[$row['status']]);
}
