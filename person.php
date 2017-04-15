<?php

include 'framework.php';

$pedit = "personEdit.php";

if ($sort == NULL)
    $sort = 'list_order,lastname,firstname';

function mail2all()
{
  $q = "select email from person " .
       "where (status = '$per_stat_member' or status = '$per_stat_eng')";
  $r = mysql_query($q);

  echo "<a href=\"mailto:?bcc=";
  while ($e = mysql_fetch_array($r, MYSQL_ASSOC))
    echo $e[email] . ",";
  echo "&subject=OSO: \"><image border=0 src=images/image1.gif hspace=20 title=\"Send mail alle i OSO med status medlem eller engasjert\"></a>";
}


function format_phone($ph)
{
  $ph = str_replace(' ', '', $ph);
  $ph = substr($ph, 0, -5) . " " . substr($ph, -5, 2) . " " . substr($ph, -3);
  if (strlen($ph) > 9)
    $ph = substr($ph, 0, -10) . " " . substr($ph, -10);
  return $ph;
}

echo "
    <h1>Medlemsliste</h1>";
echo "
    <form action='$pedit' method=post>
      <input type=hidden name=_sort value='$sort'>
      <input type=hidden name=_action value=edit_pers>
      <input type=submit value=\"Ny person\">";
    mail2all();
echo "
    </form>
    <form action='$pedit' method=post>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Edit</th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=list_order,lastname,firstname\">Instrument</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=firstname,lastname\">For</a>/
                          <a href=\"$php_self?_sort=lastname,firstname\">Etternavn</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=address,lastname,firstname\">Adresse</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=postcode,lastname,firstname\">Postnr</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=city,lastname,firstname\">Sted</a></th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=email\">Mail</a></th>
      <th bgcolor=#A6CAF0>Mobil</th>
      <th bgcolor=#A6CAF0>Priv</th>
      <th bgcolor=#A6CAF0>Arbeid</th>
      <th bgcolor=#A6CAF0><a href=\"$php_self?_sort=status,list_order,lastname,firstname\">Status</a></th>
      <th bgcolor=#A6CAF0>Kommentar</th>
      </tr>";




$query  = "SELECT person.id as id, id_instruments, instrument, firstname, lastname, " .
    "address, postcode, city, " .
    "email, phone1, phone2, phone3, status, person.comment as comment " .
    "FROM person, instruments " .
    "where id_instruments = instruments.id order by $sort";

$result = mysql_query($query);

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
  echo "<tr>
         <td><center>
           <a href=\"{$pedit}?_sort={$sort}&_action=view&_no={$row[id]}\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>
          </center></td>" .
         "<td>{$row[instrument]}</td>" .
         "<td>{$row[firstname]} " .
         "{$row[lastname]}</td>" . 
         "<td>{$row[address]}</td>" . 
         "<td>" .
         sprintf("%04d", $row[postcode]) .
         "</td>" . 
         "<td>{$row[city]}</td>" . 
         "<td><a href=\"mailto:{$row[email]}?subject=OSO:\">{$row[email]}</a></td>" .
         "<td nowrap>" . format_phone($row[phone1]) . "</td>" .
         "<td>{$row[phone2]}</td>" . 
         "<td>{$row[phone3]}</td>" . 
         "<td>{$per_stat[$row[status]]}</td>" .
         "<td>{$row[comment]}</td>" .
         "</tr>";
} 

?>

</table>
    </form>

<?php
  include 'framework_end.php';
?>