<?php

require 'framework.php';

function get_project()
{
   global $db;

   $query = "select project.id as id, name, semester, year, date "
           . "from project, plan "
           . "where plan.id=" . request('id_plan') . " "
           . "and plan.id_project = project.id";
   $stmt = $db->query($query);
   return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_groups($id_project)
{
   global $whoami;
   global $db;

   $query = "select groups.id as id "
           . "from participant, instruments, groups, person "
           . "where participant.id_person = person.id "
           . "and person.id = " . $whoami->id() . " "
           . "and participant.id_project = $id_project "
           . "and participant.id_instruments = instruments.id "
           . "and instruments.id_groups = groups.id";

   $stmt = $db->query($query);
   $grp = $stmt->fetch(PDO::FETCH_ASSOC);
   $grp_prj = $grp['id'];

   $query = "select groups.id as id "
           . "from instruments, groups, person "
           . "where person.id = " . $whoami->id() . " "
           . "and person.id_instruments = instruments.id "
           . "and instruments.id_groups = groups.id";

   $stmt = $db->query($query);
   $grp = $stmt->fetch(PDO::FETCH_ASSOC);
   $grp_def = $grp['id'];

   return array($grp_prj, $grp_def);
}


if ($sort == NULL)
   $sort = 'list_order,-position+DESC,-def_pos+DESC,firstname,lastname';

$prj = get_project();
$grp = get_groups($prj['id']);

$style = '';

if ($action == 'update')
{
   $style = "style=\"background-color:lightgreen\"";

   foreach ($_REQUEST as $key => $val)
   {
      if (strstr($key, ':'))
      {
         list($field, $id_person) = explode(':', $key);
         if ($field == "status")
         {
            if (is_null(request('clear')))
            {
               $query = "replace into absence "
                    . "(id_person, id_plan, status, comment) "
                    . "values "
                    . "($id_person, " . request('id_plan') . ", $val, " . $db->qpost("comment:$id_person") . ")";
            }
            else
            {
               $query = "delete from absence "
                       . "where id_plan = " . request('id_plan') . " "
                       . "and id_person = $id_person";
            }
            $db->query($query);
         }
      }
   }
}

echo "
    <h1><a href=\"absence.php?id_project=" . $prj['id'] . "\">Fravær</a></h1>
    <h2>" . $prj['name'] . " " . $prj['semester'] . "-" . $prj['year'] . "</h2>
    <h3>" . strftime('%a %e.%b', $prj['date']) . "</h3>";
$form = new FORM();
echo "<input type=hidden name=id_plan value=" . request('id_plan') . ">
    <input type=hidden name=_sort value=$sort>
    <input type=hidden name=_action value=update>
    <input type=submit value=Lagre $style  title=\"Lagre\">
    <input type=submit name= clear value=Slett  title=\"Slett alt som er registrert for denne prøven\" onClick=\"return confirm('Sikkert at du vil slette alt for denne prøven?');\"><p>\n";
$tb = new TABLE('border=1');
$tb->th("<a href=\"$php_self?id_plan=" . request('id_plan') . "&_sort=firstname,lastname\" title=\"Sorter på fornavn, deretter etternavn\">Navn</a>");
$tb->th("<a href=\"$php_self?id_plan=" . request('id_plan') . "&_sort=list_order,-position+DESC,-def_pos+DESC,firstname,lastname\" title=\"Sorter på instrument, fornavn, etternavn\">Instrument</a>");

for ($i = 0; $i < sizeof($db->abs_stat); $i++)
   $tb->th($db->abs_stat[$i]);

$tb->th("Kommentar");

$query = "SELECT participant.id_person as id_person, firstname, lastname, "
        . "person.status as status, instrument, plan.id as id_plan "
        . "FROM person, participant, instruments, groups, plan "
        . "where (groups.id = ".$grp[0]." or groups.id = ".$grp[1] . ") "
        . "and instruments.id_groups = groups.id "
        . "and participant.id_instruments = instruments.id "
        . "and participant.id_project = plan.id_project "
        . "and participant.stat_inv = $db->par_stat_yes "
        . "and participant.stat_final = $db->par_stat_yes "
        . "and person.id = participant.id_person "
        . "and plan.id = " . request('id_plan') . " "
        . "order by " . str_replace("+", " ", $sort);

$stmt = $db->query($query);

foreach ($stmt as $row)
{
   $tb->tr();
   $tb->td($row['firstname'] . " " . $row['lastname']);
   $tb->td($row['instrument']);

   $q = "select status, comment from absence "
           . "where id_plan=" . request('id_plan') . " "
           . "and id_person=" . $row['id_person'];
   $s = $db->query($q);
   $e = $s->fetch(PDO::FETCH_ASSOC);

   for ($i = 0; $i < sizeof($db->abs_stat); $i++)
   {
      $input = "<input type=radio name=\"status:" . $row['id_person'] . "\" value=$i";
      if (!is_null($e) && $e['status'] == $i)
         $input .= " checked";
      $input .= ">";
      $tb->td($input, 'align=center');
   }

   $tb->td("<input type=text name=\"comment:" . $row['id_person'] . "\" value=\"" . $e['comment'] . "\" size=30 title=\"Eventuell tilleggskommentar\">");
}




