<?php

require 'framework.php';

if ($sort == NULL)
   $sort = 'name';

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
for ($i = 0; $i < AUTH::NO_VIEWS; $i++)
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
   for ($i = 0; $i < AUTH::NO_VIEWS; $i++)
      echo "<th><input type=checkbox name=access$i></th>";
   echo "  </tr>";
}

if ($action == 'update')
{
   $access = 0;

   for ($i = 0; $i < AUTH::NO_VIEWS; $i++)
   {
      $key = 'access' . $i;
      if ($_POST[$key])
         $access |= 1 << $i;
   }

   if ($no == NULL)
   {
      $query = "insert into view (name, comment, access) " .
              "values ('$_POST[name]', '$_POST[comment]', '$access')";
   } else
   {
      if ($delete != NULL)
      {
         $query = "DELETE FROM location WHERE id = {$no}";
      } else
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

$query = "SELECT id, name, comment, access " .
        "FROM view order by {$sort}";

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   if ($row[id] != $no)
   {
      echo "<tr>
         <td><center>
           <a href=\"{$php_self}?_sort={$sort}&_action=view&_no={$row[id]}\"><img src=\"images/cross_re.gif\" border=0></a>
             </center></td>" .
      "<td>{$row[name]}</td>" .
      "<td>{$row[comment]}</td>";
      for ($i = 0; $i < AUTH::NO_VIEWS; $i++)
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

      for ($i = 0; $i < AUTH::NO_VIEWS; $i++)
      {
         echo "<td><input type=checkbox name=access$i value='*' ";
         if ($row[access] & (1 << $i))
            echo "checked";
         echo "></td>";
      }

      echo "</tr>";
   }
}

echo "</table></form>\n";

function add_li($li, $sub = 0, $r = false, $w = false, $comment = null)
{
   echo "<li>$li ";
   if ($r)
      echo "<font color=green>$r</font> ";
   if ($w)
      echo "<font color=red>$w</font> ";
   echo "<i>$comment</i>";
   if ($sub > 0)
      echo "\n<ul type=circle>\n";
   if ($sub == 0)
      echo "</li>\n";
   if ($sub < 0)
      for ($i = 0; $i > $sub; $i--)
         echo "</li>\n</ul>\n</li>\n";
}

echo "<ul type=circle>\n";

add_li("Meny", 1);
{
   add_li("Mine sider", 1);
   {
      add_li("Mine prosjekter", 0, AUTH::MYPRJ, AUTH::MYPRJ, "Oversikt og status over kommende prosjeker. Mulighet for å melde på/søke permisjon");
      add_li("Min prøveplan", 0, AUTH::MYPLAN, false, "Oversikt over min egen prøveplan");
      add_li("Min regi", 0, AUTH::MYDIR, false, "Oversikt over egne regioppgaver");
      add_li("Mine personopplysninger", -1, AUTH::BOARD_RO, AUTH::PERS);
   }
   add_li("Regi", 1, AUTH::BOARD_RO, AUTH::DIR_RW);
   {
      add_li("Ressurser", 0, AUTH::BOARD_RO, AUTH::DIR_RW, "Oversikt over- og tilleggsopplysninger om regiressurser");
      add_li("Turnus", 0, AUTH::BOARD_RO, AUTH::DIR_RW, "Turnusliste for regi");
      add_li("Prosjekt", 0, AUTH::BOARD_RO, AUTH::DIR_RW, "Detaljer om regiprosjekter");
      add_li("Regiplan", -1, AUTH::BOARD_RO, AUTH::DIR_RW, "Detaljerte regiplaner for hvert prosjekt");
   }
   add_li("Admin", 1);
   {
      add_li("Medlemsliste", 0, AUTH::MEMB_RO, AUTH::MEMB_RW);
      add_li("Prøveplan", 0, AUTH::PLAN_RO, AUTH::PLAN_RW, "Prøveplan, alle prosjekter");
      add_li("Grupper", 0, AUTH::BOARD_RO, AUTH::GRP, "Faste medlemsgrupper som styret og instrumentgrupper");
      add_li("Instrumenter", 0, AUTH::BOARD_RO, AUTH::INSTR, "Liste over instrumentgrupper. Alle musikerne må tilhøre en instrumentgruppe");
      add_li("Tilgang", 0, AUTH::BOARD_RO, AUTH::ACC, "Liste over tilganger for hver enkelt musiker");
      add_li("Tilgangsgrupper", 0, AUTH::BOARD_RO, AUTH::ACCGRP, "Definering av tilgangsgrupper");
      add_li("Notearkiv", 0, AUTH::BOARD_RO, AUTH::REP, "Oversikt over notearkiv");
      add_li("Prosjekter", 0, AUTH::BOARD_RO, AUTH::PRJ, "Overordnet administrasjon av prosjekter");
      add_li("Tilbakemeldinger", 0, AUTH::BOARD_RO, false, "Oversikt over tilbakemeldiner fra medlemmer");
      add_li("Lokale", 0, AUTH::BOARD_RO, AUTH::LOC, "Informasjon om lokaliteter for øvelser og konsert");
      add_li("Ressurser", 0, AUTH::RES, false, "Oversikt over status på ressurser pr. prosjekt");
      add_li("Dokumenter", 0, AUTH::DOC_RO, AUTH::DOC_RW, "Tilgang til generelle dokumenter som vedtekter, generalforsamlingspapirer, ol.");
      add_li("Kontigent", 0, AUTH::CONT_RO, AUTH::CONT_RW, "Oversikt over kontigentinnbetalinger");
      add_li("Om $prj_name", -1, false, false, "Generell informasjon om $prj_name");
   }
   add_li("Prosjekter", 1);
   {
      add_li("Operaball", 0, AUTH::PRJM, false, "(Prosjekt som jeg ikke er med på selv)");
      add_li("Symfonikonsert", 1, false, false, "(Prosjekt jeg er med på selv)");
      {
         add_li("Prosjektinfo");
         add_li("Gruppeoppsett", 0, false, AUTH::SEAT, "Kart over plassering av musikere pr. instrumentgruppe");
         add_li("Program");
         add_li("Musikere");
         add_li("Noter", 0, false, AUTH::PRJDOC, "Oversikt/administrajon av øvingsnoter");
         add_li("Innspilling", 0, false, AUTH::PRJDOC, "Oversikt/administrajon av egne opptak og andre innspillinger");
         add_li("Dokumenter", 0, false, AUTH::PRJDOC, "Oversikt/administrajon av prosjektdokumenter");
         add_li("Regikomité", 0, AUTH::DIR_RO, false, "Full oversikt over gjeldende regiprosjekt");
         add_li("Permisjonssøknad", 0, false, AUTH::RES_SELF, "Registrere påmelding eller søke om persmisjon for gjeldende prosjekt");
         add_li("Tilbakemelding", 0, false, AUTH::FBACK, "Mulighet for å tilbakemelding på et prosjekt");
         add_li("Fravær", 0, AUTH::ABS_ALL, AUTH::ABS_GRP, "Oversikt over fravær for alle ressurser for et prosjekt og registrering av fravær pr. gruppe ");
         add_li("Prosjektressurser", 0, AUTH::RES, "Sekretær: " . AUTH::RES_REG . ", MR: " . AUTH::RES_REQ . ", Styret: " . AUTH::RES_FIN, "Registrering av prosjektressurser");
         add_li("Konsertkalender", -2, AUTH::BOARD_RO, AUTH::CONS, "Redigering av konsertkalender");
      }
   }
   add_li("Hva skjer?", -1, AUTH::PRJM, AUTH::EVENT);
}

echo "</ul>\n";
