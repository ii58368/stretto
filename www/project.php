
<?php

require 'framework.php';

if ($sort == NULL)
   $sort = 'year,semester+DESC';

function select_semester($selected)
{
   $s = "<select name=semester title=\"Velg semester\">";
   $s .= "<option value=V";
   if ($selected == 'V')
      $s .= " selected";
   $s .= ">Vår</option>\n";

   $s .= "<option value=H";
   if ($selected == 'H')
      $s .= " selected";
   $s .= ">Høst</option>\n";
   $s .= "</select>";

   return $s;
}

function select_type($selected)
{
   global $db;

   $str = "<select name=type title=\"Velg prosjekttype\">\n";

   for ($i = 0; $i < count($db->prj_type); $i++)
   {
      $str .= "<option value=$i";
      if ($selected == $i)
         $str .= " selected";
      $str .= ">" . $db->prj_type[$i] . "</option>\n";
   }

   $str .= "</select>";

   return $str;
}

function select_status($selected)
{
   global $db;

   if (is_null($selected))
      $selected = $db->prj_stat_draft;

   $htext = "Velg gjeldende status for prosjektet:\n"
           . "* Draft: Kun synlig for styremedlemmer og MR. Lysegrå tekst\n"
           . "* Tentativt: Synlig også for medlemmer, men med grå tekst\n"
           . "* Internt: Kun synlig internt for de som er med på prosjektet\n"
           . "* Reelt: Vedtatt i styret. Mulig for medlemmer å melde seg/søke permisjon. Svart tekst.\n"
           . "* Kansellert: Ikke lenger med på spilleplanen. Kun synlig for styret og MR.";
   $s = "<select name=status title=\"$htext\">";

   for ($i = 0; $i < count($db->prj_stat); $i++)
   {
      $s .= "<option value=$i";
      if ($selected == $i)
         $s .= " selected";
      $s .= ">" . $db->prj_stat[$i] . "</option>\n";
   }

   $s .= "</select>";

   return $s;
}

function select_valid_par_stat($valid_par_stat)
{
   global $db;

   $s = "<select size=" . sizeof($db->par_stat) . " name=\"valid_par_stat[]\" multiple title=\"Merk av svaralternativer ved av-/påmelding.\nCtrl-click to select/unselect single\">";

   for ($i = 0; $i < sizeof($db->par_stat); $i++)
   {
      $s .= "<option value=\"" . $i . "\"";
      if ($valid_par_stat & (1 << $i))
         $s .= " selected";
      $s .= ">" . $db->par_stat[$i];
   }
   $s .= "</select>";

   return $s;
}

echo "<h1>Prosjekt</h1>";

if ($access->auth(AUTH::PRJ))
{
   $form = new FORM();
   echo "<input type=hidden name=_sort value='$sort'>
      <input type=hidden name=_action value=new>
      <input type=submit value=\"Nytt prosjekt\" title=\"Definer nytt prosjekt\" >";
   unset($form);
}

$form = new FORM();
$tb = new TABLE();


if ($access->auth(AUTH::PRJ))
   $tb->th('Edit');
$tb->th("<a href=\"$php_self?_sort=name,id\" title=\"Sorter p&aring; prosjektnavn\">Prosjekt</a>");
$tb->th("<a href=\"$php_self?_sort=year,semester+DESC,id\" title=\"Sorter p&aring; semester\">Sem</a>", 'nowrap');
$tb->th('Status');
$tb->th('På-/avm.frist');
$tb->th('Type');
$tb->th('På-/avmeld.');
$tb->th('Generell info');

if ($action == 'new')
{
   $tb->tr();
   $tb->td("<input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=submit value=ok title=\"Lagre\">");
   $tb->td("<input type=text size=20 name=name title=\"Navn på prosjekt\">");
   $tb->td(select_semester($season->semester()) . "<input type=text size=4 maxlength=4 name=year value=" . $season->year() . " title=\"Velg årstall\">", 'nowrap');
   $tb->td(select_status(null));
   $tb->td("<input type=date size=10 name=deadline value=\"" .
           date('j. M y', time() + 60 * 60 * 24 * 7 * 12) . // Default dealine: 12 weeks from now
           "\" title=\"Frist for permisjon/påmelding.\nFormat: <dato>. <mnd> [<år>]\nMerk: Måned på engelsk. Eksempel: 12. dec\">");
   $tb->td(select_type($selected));
   $tb->td(select_valid_par_stat(1 << $db->par_stat_no));
   $hinfo = "Informasjon om prosjektet. Blir synlig på planen for prosjektinfo. "
           . "Nyttig  info:\n"
           . "* Dirigent\n"
           . "* Konsertmester\n"
           . "* Arrangør\n"
           . "* Solister";
   $tb->td("<textarea cols=44 rows=10 wrap=virtual name=info title=\"$hinfo\"></textarea>");
}

if ($action == 'update' && $access->auth(AUTH::PRJ))
{
   $valid_par_stat = 0;
   if (request('valid_par_stat') != null)
      foreach (request('valid_par_stat') as $idx)
         $valid_par_stat |= (1 << $idx);

   if (($ts = strtotime(request('deadline') . " + 1 day - 1 second")) == false)
   {
      echo "<font color=red>Illegal time format: " . request('deadline') . "</font>";
   }
   else
   {
      if ($no == NULL)
      {
         $query = "insert into project (name, semester, year, status, deadline, orchestration, info, id_person, valid_par_stat) " .
                 "values (" . $db->qpost('name') . ", '" . request('semester') . "', " .
                 request('year') . ", " . request('status') . ", $ts, " . request('type') . ", " . $db->qpost('info') . ", 1, $valid_par_stat)";
         $db->query($query);
      }
      else
      {
         if ($delete != NULL)
         {
            $query = "DELETE from project WHERE project.id = $no";
         }
         else
         {
            $query = "update project set name = " . $db->qpost('name') . "," .
                    "semester = '" . request('semester') . "'," .
                    "year = " . request('year') . "," .
                    "status = " . request('status') . "," .
                    "deadline = $ts, " .
                    "orchestration = " . request('type') . "," .
                    "valid_par_stat = $valid_par_stat, " .
                    "info = " . $db->qpost('info') . " " .
                    "where id = $no";
         }
         $db->query($query);
         $no = NULL;
      }
   }
}

$query = "SELECT project.id as id, name, semester, year, status, " .
        "deadline, orchestration, valid_par_stat, info " .
        "FROM project " .
        "where project.year = " . $season->year() . " " .
        "and project.semester = '" . $season->semester() . "' " .
        "order by " . str_replace("+", " ", $sort);

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   $tb->tr();

   if ($row['id'] != $no)
   {
      if ($access->auth(AUTH::PRJ))
         $tb->td("<a href=\"$php_self?_sort=$sort&_action=view&_no=" . $row['id'] . "\"><img src=\"images/cross_re.gif\" border=0 title=\"Klikk for &aring; editere...\"></a>", 'align=center');
      $tb->td("<a href=\"prjInfo.php?id=" . $row['id'] . "\">" . $row['name'] . "</a>");
      $tb->td($row['semester'] . "-" . $row['year']);
      $tb->td($db->prj_stat[$row['status']]);
      $tb->td(strftime('%a %e.%b %y', $row['deadline']));
      $tb->td($db->prj_type[$row['orchestration']]);
      $list = '';
      for ($i = 0; $i < sizeof($db->par_stat); $i++)
         if ($row['valid_par_stat'] & (1 << $i))
            $list .= "<img src=\"images/ballc_g1.gif\" border=0>" . $db->par_stat[$i] . "<br>\n";
      $tb->td($list);
      $tb->td(str_replace("\n", "<br>\n", $row['info']));
   }
   else
   {
      $tb->td("<input type=hidden name=_action value=update>
    <input type=hidden name=_sort value='$sort'>
    <input type=hidden name=_no value='$no'>
    <input type=submit value=ok title=\"Lagere\" >
    <input type=submit value=del name=_delete title=\"Slett\" onClick=\"return confirm('Sikkert at du vil slette " . $row['name'] . "?');\">", 'nowrap');
      $tb->td("<input type=text size=20 name=name value=\"" . $row['name'] . "\" title=\"Navn på prosjekt\">");
      $tb->td(select_semester($row['semester']) . "<input type=text size=4 maxlength=4 name=year value=\"" . $row['year'] . "\" title=\"Årstall (4 siffer)\">", 'nowrap');
      $tb->td(select_status($row['status']));
      $tb->td("<input type=date size=10 name=deadline value=\"" . date('Y-m-d', $row['deadline']) . "\" title=\"Permisjons-/påmeldingsfrist\">");
      $tb->td(select_type($row['orchestration']));
      $tb->td(select_valid_par_stat($row['valid_par_stat']));
      $tb->td("<textarea cols=44 rows=10 wrap=virtual name=info title=\"Prosjektinfo\">" . $row['info'] . "</textarea>");
   }
}
