<?php
    require 'framework.php';
    
    if ($sort == NULL)
        $sort = 'name';
    
    $no_views = 39;
    
  echo "
    <h1>Tilgangsgrupper</h1>
    <form action=\"$php_self\" method=post>
      <input type=hidden name=_sort value=\"$sort\">
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Ny tilgangsgruppe\">
    </form>
    <form action='$php_self' method=post>
    <table border=1>
    <tr>
      <th bgcolor=#A6CAF0>Edit</th>
      <th bgcolor=#A6CAF0>Navn</th>
      <th bgcolor=#A6CAF0>Kommentar</th>";
  for ($i = 0; $i < $no_views; $i++)
    echo "<th bgcolor=#A6CAF0>" . $i . "</th>";
  echo "</tr>";

if ($action == 'new')
{
  echo "  <tr>
    <td align=left><input type=hidden name=_action value=update>
    <input type=hidden name=_sort value=\"$sort\">
    <input type=submit value=ok></td>
    <th><input type=text size=30 name=name></th>
    <th><input type=text size=30 name=comment></th>";
  for ($i = 0; $i < $no_views; $i++)
    echo "<th><input type=checkbox name=access$i></th>";
  echo "  </tr>";
}

if ($action == 'update')
{
  $access = 0;
  
  for ($i = 0; $i < $no_views; $i++)
  {
    $key = 'access' . $i;
    if ($_POST[$key])
      $access |= 1 << $i;
  }

  if ($no == NULL)
  {
    $query = "insert into view (name, comment, access) " .
             "values ('$_POST[name]', '$_POST[comment]', '$access')";
  }
  else
  {
    if ($delete != NULL)
    {
      $query = "DELETE FROM location WHERE id = {$no}";
    }
    else
    {
      $query = "update view set name = '$_POST[name]'," .
                               "comment = '$_POST[comment]'," .
                               "access = '$access' " .
             "where id = $no";
      $no = NULL;
    }
  }
  $db->query($query);
}   

$query  = "SELECT id, name, comment, access " .
          "FROM view order by {$sort}";
       
$stmt = $db->query($query);

foreach($stmt as $row)
{
  if ($row[id] != $no)
  {
    echo "<tr>
         <td><center>
           <a href=\"{$php_self}?_sort={$sort}&_action=view&_no={$row[id]}\"><img src=\"images/cross_re.gif\" border=0></a>
             </center></td>" .
         "<td>{$row[name]}</td>" .
         "<td>{$row[comment]}</td>";
     for ($i = 0; $i < $no_views; $i++)
     {
       echo "<td>";
       if ($row[access] & (1 << $i))
         echo "<center><img src=\"images/tick2.gif\" border=0></center>";
       echo "</td>";
     }
     echo "</tr>";
  }
  else
  {
    echo "<tr>
    <input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <th nowrap><input type=submit value=ok>
      <input type=submit value=del name=_delete onClick=\"return confirm('Sikkert at du vil slette {$row[name]}?');\"></th>
    <th><input type=text size=30 name=name value=\"{$row[name]}\"></th>
    <th><input type=text size=30 name=comment value=\"{$row[comment]}\"></th>";
    
    for ($i = 0; $i < $no_views; $i++)
    {
       echo "<td><input type=checkbox name=access$i value='*' ";
       if ($row[access] & (1 << $i))
         echo "checked";
       echo "></td>";
    }
   
    echo "</tr>";
  }
} 

?> 

    </table>
    </form>

 
<?php
  function tline($c2, $c3)
  {
     static $i = 0;
     
     echo "<tr>";
     echo "<td>$i</td>";
     echo "<td>$c2</td>";
     echo "<td>$c3</td>";
     echo  "</tr>\n";  
     $i++;
  }
  
  echo "<table border=0>\n";
  
  tline("", "Super-user. Mulighet for å utgi seg for å være en  annen bruker");
  tline("r", "Mine prosjekter (permisjon/deltagelse, alle prosjekter)");
  tline("r", "Prosjekter (alle medlemmer, alle prosjekter)");
  tline("r", "Min prøveplan");
  tline("r", "Min regi");
  tline("w", "Mine personopplysninger, mulighet for editering");
  tline("r", "Regi, all medlemsinfo, grupper, tilgang, noter, lokale, prosjekter. For alle i styret");
  tline("w", "Regi");
  tline("r", "Medlemsliste");
 tline("r", "Prøveplan, alle prosjekter");
 tline("w", "Prøveplan, alle prosjekter");
 tline("w", "Redigering av administrasjonsgrupper. (Styret, gruppeledere, o.l.");
 tline("w", "Editering av instrumentgrupper");
 tline("w", "Editere autorisasjonsgrupper");
 tline("w", "Editere autorisasjon pr. bruker");
 tline("w", "Ajourhold av notearkiv.");
 tline("w", "Endre/definere nye prosjekter");
 
 13  
auth_instr

x
Instrumenter
 14  
auth_acc

x
Tilgang
 15  
auth_accgrp

x
Tilgangsgrupper
 16  
auth_rep

x
Notearkiv
 17  
auth_prj

x
Prosjekter
 18  
auth_abs_ro
x

Permisjon/Deltagelse (pr. Medlem, alle prosjekter)
 19  
auth_loc

x
Lokale
 20  
auth_doc_ro
x

Lesetilgang for generelle dokumenter
 21  
auth_doc_rw

x
Tilgang for opplasting av generelle dokumenter
 22  
auth_cont_ro
x

Kontigent
 23  
auth_cont_rw

x
Kontigent
 24  
auth_prjm
x

Info alle prosjekter, prosjektinfo, gruppeoppsett, prøveplan
 25  
auth_seat

x
Gruppeoppsett, plassering av musikere
 26  
auth_prog

x
Program
 27  
auth_prjdoc 

x
Opplasting av noter, opptak, prosjektdokumenter
 28  
auth_dir_ro
x

Regikomité
 29  
auth_abs_rw

x
Påmelding/permisjonssøknad
 30  
auth_res
x

Ressurser
 31  
auth_res_self

x
Ressurser – self. Medlem 
 32  
auth_res_reg

x
Sekretær. registrering
 33  
auth_res_req

x
Ressurser, recommended. MR
 34  
auth_res_fin

x
Ressurser, final. Styret
 35  
auth_fback

x
Tilbakemelding. Ris og ros
 36  
auth_abs_grp

x
Fravær, pr gruppe
 37  
auth_abs_all

x
Fravær alle medlemmer
 38  
auth_cons

x
Konsertkalender
 39  
auth_event

x
Hva skjer

  
  echo "</table>";
?>


