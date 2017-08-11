<?php
include 'framework.php';

$pedit = "personEdit.php";

if (is_null($sort))
   $sort = 'list_order,lastname,firstname';

function send_mail($r)
{
   reset($r);
   echo "<a href=\"mailto:?bcc=";
   foreach ($r as $e)
      if (strlen($e[email]) > 0)
         echo $e[email] . ",";
   echo "&subject=OSO: \"><image border=0 src=images/image1.gif hspace=20 title=\"Send mail til alle på listen...\"></a>";
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

   // Select status
   echo "<select name=\"f_status[]\" multiple size=3 onChange=\"submit();\">\n";

   for ($i = 0; $i < count($db->per_stat); $i++)
   {
      echo "<option value=$i";
      if (!is_null($_REQUEST[f_status]))
         foreach ($_REQUEST[f_status] as $f_status)
            if ($f_status == $i)
               echo " selected";
      echo ">" . $db->per_stat[$i] . "</option>\n";
   }

   echo "</select>\n";

   // Instrument
   echo "<select name=\"f_instrument[]\" multiple size=3 onChange=\"submit();\">\n";

   $s = $db->query("select id, instrument from instruments");
   foreach ($s as $e)
   {
      echo "<option value=$e[id]";
      if (!is_null($_REQUEST[f_instrument]))
         foreach ($_REQUEST[f_instrument] as $f_instrument)
            if ($f_instrument == $e[id])
               echo " selected";
      echo ">" . $e[instrument] . "</option>\n";
   }

   echo "</select>\n";

// Groups
   echo "<select name=\"f_group[]\" multiple size=3 onChange=\"submit();\">\n";

   $s = $db->query("select id, name from groups, member "
           . "where groups.id = member.id_groups group by id order by name");
   foreach ($s as $e)
   {
      echo "<option value=$e[id]";
      if (!is_null($_REQUEST[f_group]))
         foreach ($_REQUEST[f_group] as $f_group)
            if ($f_group == $e[id])
               echo " selected";
      echo ">" . $e[name] . "</option>\n";
   }

   echo "</select>\n";

   // Project
   echo "<select name=\"f_project[]\" multiple size=3 onChange=\"submit();\">\n";

   $s = $db->query("select id, name, year, semester from project order by year DESC,semester");
   foreach ($s as $e)
   {
      echo "<option value=$e[id]";
      if (!is_null($_REQUEST[f_project]))
         foreach ($_REQUEST[f_project] as $f_project)
            if ($f_project == $e[id])
               echo " selected";
      echo ">" . "$e[name] ($e[semester]$e[year])" . "</option>\n";
   }

   echo "</select>\n";

   echo "<input type=hidden name=_sort value=\"$sort\">\n";
}

function get_filter_as_url()
{
   if (!is_null($_REQUEST[f_status]))
      foreach ($_REQUEST[f_status] as $f_status)
         $filter .= "&f_status[]=$f_status";
   if (!is_null($_REQUEST[f_instrument]))
      foreach ($_REQUEST[f_instrument] as $f_instrument)
         $filter .= "&f_instrument[]=$f_instrument";
   if (!is_null($_REQUEST[f_group]))
      foreach ($_REQUEST[f_group] as $f_group)
         $filter .= "&f_group[]=$f_group";
   if (!is_null($_REQUEST[f_project]))
      foreach ($_REQUEST[f_project] as $f_project)
         $filter .= "&f_project[]=$f_project";
   
   return $filter;
}

echo "
    <h1>Adresseliste</h1>";
if (!is_null($_REQUEST[f_project]))
{
   $query = "select name, semester, year from project where ";
   foreach ($_REQUEST[f_project] as $f_project)
      $query .= "project.id = $f_project or ";
   $query .= "false order by year DESC,semester";
   $stmt = $db->query($query);
   echo "<h2>";
   foreach ($stmt as $e)
      echo "$e[name] ($e[semester]$e[year]) ";
   echo "</h2>\n";
}


$query = "SELECT person.id as id, instruments.id as id_instruments, instrument, firstname, middlename, lastname, "
        . "address, postcode, city, "
        . "email, phone1, phone2, phone3, person.status as status, person.comment as comment "
        . "FROM person, instruments ";
if (!is_null($_REQUEST[f_project]))
   $query .= ", participant, project ";
if (!is_null($_REQUEST[f_group]))
   $query .= ", groups, member ";
$query .= "where person.id_instruments = instruments.id ";
if (!is_null($_REQUEST[f_project]))
{
   $query .= "and participant.id_person = person.id "
           . "and participant.id_project = project.id "
           . "and participant.stat_final = $db->par_stat_yes "
           . "and (";
   foreach ($_REQUEST[f_project] as $f_project)
      $query .= "project.id = $f_project or ";
   $query .= "false) ";
}
if (!is_null($_REQUEST[f_group]))
{
   $query .= "and groups.id = member.id_groups "
           . "and member.id_person = person.id "
           . "and (";
   foreach ($_REQUEST[f_group] as $f_group)
      $query .= "groups.id = $f_group or ";
   $query .= "false) ";
}
if (!is_null($_REQUEST[f_status]))
{
   $query .= "and (";
   foreach ($_REQUEST[f_status] as $f_status)
      $query .= "person.status = $f_status or ";
   $query .= "false) ";
}
if (!is_null($_REQUEST[f_instrument]))
{
   $query .= "and (";
   foreach ($_REQUEST[f_instrument] as $f_instrument)
      $query .= "instruments.id = $f_instrument or ";
   $query .= "false) ";
}
$query .= "group by person.id order by $sort";


$stmt = $db->query($query);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$f_filter = get_filter_as_url();

if ($access->auth(AUTH::MEMB_RW))
{
   echo "
    <form action='$pedit' method=post>
      <input type=hidden name=_sort value='$sort'>
      <input type=hidden name=_action value=edit_pers>
      <input type=submit value=\"Ny person\">
      </form>\n";
}

echo "<a href=person_pdf.php?_sort=$sort$f_filter title=\"PDF versjon\"><img src=images/pdf.jpeg height=22></a>";

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
      <th bgcolor=#A6CAF0>Edit</th>";
echo "
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=list_order,lastname,firstname$f_filter\">Instrument</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=firstname,lastname$f_filter\">For</a>/
                          <a href=\"$php_self?_sort=lastname,firstname$f_filter\">Etternavn</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=address,lastname,firstname$f_filter\">Adresse</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=postcode,lastname,firstname$f_filter\">Postnr</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=city,lastname,firstname$f_filter\">Sted</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=email$f_filter\">Mail</a></th>
      <th bgcolor=#A6CAF0>Mobil</th>
      <th bgcolor=#A6CAF0>Priv</th>
      <th bgcolor=#A6CAF0>Arbeid</th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=status,list_order,lastname,firstname$f_filter\">Status</a></th>
      <th bgcolor=#A6CAF0>Kommentar</th>
      </tr>";

reset($result);

foreach ($result as $row)
{
   echo "<tr>";
   if ($access->auth(AUTH::MEMB_RW))
      echo "
         <td><center>
           <a href=\"{$pedit}?_sort={$sort}&_action=view&_no={$row[id]}$f_filter\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>
          </center></td>";
   echo "<td>{$row[instrument]}</td>" .
   "<td>{$row[firstname]} {$row[middlename]} {$row[lastname]}</td>" .
   "<td>{$row[address]}</td>" .
   "<td>" .
   sprintf("%04d", $row[postcode]) .
   "</td>" .
   "<td>{$row[city]}</td>" .
   "<td><a href=\"mailto:{$row[email]}?subject=OSO:\">{$row[email]}</a></td>" .
   "<td nowrap>" . format_phone($row[phone1]) . "</td>" .
   "<td>{$row[phone2]}</td>" .
   "<td>{$row[phone3]}</td>" .
   "<td>{$db->per_stat[$row[status]]}</td>" .
   "<td>{$row[comment]}</td>" .
   "</tr>";
}
?>

</table>
</form>
