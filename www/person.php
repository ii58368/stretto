<?php
require 'framework.php';
require 'person_query.php';

$pedit = "personEdit.php";

if (is_null($sort))
   $sort = 'list_order,lastname,firstname';

function send_mail($r)
{
   reset($r);
   echo "<a href=\"mailto:?bcc=";
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

   // Select status
   echo "<select name=\"f_status[]\" multiple size=3 onChange=\"submit();\" title=\"Filter for medlemsstatus...\">\n";

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
   echo "<select name=\"f_instrument[]\" multiple size=3 onChange=\"submit();\" title=\"Filter for instrumentgruppe...\">\n";

   $s = $db->query("select id, instrument from instruments");
   foreach ($s as $e)
   {
      echo "<option value=".$e['id'];
      if (!is_null(request('f_instrument')))
         foreach (request('f_instrument') as $f_instrument)
            if ($f_instrument == $e['id'])
               echo " selected";
      echo ">" . $e['instrument'] . "</option>\n";
   }

   echo "</select>\n";

// Groups
   echo "<select name=\"f_group[]\" multiple size=3 onChange=\"submit();\" title=\"Filter for gruppering registrert under grupper...\">\n";

   $s = $db->query("select id, name from groups, member "
           . "where groups.id = member.id_groups group by id order by name");
   foreach ($s as $e)
   {
      echo "<option value=".$e['id'];
      if (!is_null(request('f_group')))
         foreach (request('f_group') as $f_group)
            if ($f_group == $e['id'])
               echo " selected";
      echo ">" . $e['name'] . "</option>\n";
   }

   echo "</select>\n";

   // Project
   echo "<select name=\"f_project[]\" multiple size=3 onChange=\"submit();\" title=\"Filter for prosjekt...\">\n";

   $s = $db->query("select id, name, year, semester from project "
           . "where year = ".$season->year()." "
           . "and semester = '".$season->semester()."' "
           . "and status = $db->prj_stat_real "
           . "order by id");
   foreach ($s as $e)
   {
      echo "<option value=".$e['id'];
      if (!is_null(request('f_project')))
         foreach (request('f_project') as $f_project)
            if ($f_project == $e['id'])
               echo " selected";
      echo ">" . $e['name']." (".$e['semester'].$e['year'].")</option>\n";
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
      echo $e['name']." (".$e['semester'].$e['year'].") ";
   echo "</h2>\n";
}


$query = person_query();

$stmt = $db->query($query);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$f_filter = get_filter_as_url();

if ($access->auth(AUTH::MEMB_RW))
   echo "<a href=\"$pedit?_sort=$sort&_action=edit_pers$f_filter\" title=\"Registrer ny person...\"><img src=\"images/new_inc.gif\" border=0 hspace=5 vspace=5></a>\n";

echo "<a href=\"person_pdf.php?_sort=list_order,lastname,firstname$f_filter\" title=\"PDF versjon...\"><img src=images/pdf.jpeg height=22 border=0 hspace=5 vspace=5></a>\n";

if ($access->auth(AUTH::MEMB_RW, AUTH::MEMB_GREP))
{
   send_mail($result);
   echo "<form action=\"$php_self\" method=post>\n";
      select_filter();
   echo "</form>\n";
}

echo "
    <form action='$pedit' method=post>
    <table border=1>
    <tr>";
if ($access->auth(AUTH::MEMB_RW))
   echo "
      <th>Edit</th>";
echo "
      <th><a href=\"$php_self?_sort=list_order,lastname,firstname$f_filter\" title=\"Sorter på instrumentgruppe...\">Instrument</a></th>
      <th><a href=\"$php_self?_sort=firstname,lastname$f_filter\" title=\"Sorter på fornavn...\">For</a>/
                          <a href=\"$php_self?_sort=lastname,firstname$f_filter\" title=\"Sorter på etternavn...\">Etternavn</a></th>
      <th><a href=\"$php_self?_sort=address,lastname,firstname$f_filter\" title=\"Sorter på addresse...\">Adresse</a></th>
      <th><a href=\"$php_self?_sort=postcode,lastname,firstname$f_filter\" title=\"Sorter på postnummer...\">Postnr</a></th>
      <th><a href=\"$php_self?_sort=city,lastname,firstname$f_filter\" title=\"Sorter på sted...\">Sted</a></th>
      <th>Email</th>
      <th>Mobil</th>
      <th>Priv</th>
      <th>Arbeid</th>
      <th><a href=\"$php_self?_sort=status,list_order,lastname,firstname$f_filter\" title=\"Sorter på status...\">Status</a></th>
      <th>Kommentar</th>
      </tr>";

reset($result);

foreach ($result as $row)
{
   echo "<tr>";
   if ($access->auth(AUTH::MEMB_RW))
      echo "
         <td><center>
           <a href=\"$pedit?_sort=$sort&_action=view&_no=".$row['id'].$f_filter."\"><img src=\"images/cross_re.gif\" border=0 title=\"Editere person...\"></a>
          </center></td>";
   echo "<td>".$row['instrument']."</td>" .
   "<td>".$row['firstname']." ".$row['middlename']." ".$row['lastname']."</td>" .
   "<td>".$row['address']."</td>" .
   "<td>" .
   sprintf("%04d", $row['postcode']) .
   "</td>" .
   "<td>".$row['city']."</td>" .
   "<td><a href=\"mailto:".$row['email']."?subject=OSO:\">".$row['email']."</a></td>" .
   "<td nowrap>" . format_phone($row['phone1']) . "</td>" .
   "<td>".$row['phone2']."</td>" .
   "<td>".$row['phone3']."</td>" .
   "<td>".$db->per_stat[$row['status']]."</td>" .
   "<td>".$row['comment']."</td>" .
   "</tr>";
}
?>

</table>
</form>
