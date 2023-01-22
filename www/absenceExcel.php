<?php

require 'request.php';

$id_project = request('id_project');

$filename = "absence_$id_project.csv";
header("Content-Type: text/csv; name=\"$filename\"");
header("Content-disposition: attachment; filename=\"$filename\"");

$query = "SELECT date, time "
        . "FROM plan "
        . "where plan.id_project = $id_project "
        . "and plan.event_type = $db->plan_evt_rehearsal "
        . "order by date";

$stmt = $db->query($query);
$r = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Line 1: rehearsal date
echo ";;;;;;;;Dato";
foreach ($r as $e)
{
   echo ";" . date('d.m.Y', $e['date']);
}
echo "\n";

// Line 2: Start time
echo ";;;;;;;;Starttid";
reset($r);
foreach ($r as $e)
{
   echo ";" . explode('-', $e['time'])[0];
}
echo "\n";

// Line 3: Physical
echo ";;;;;;;;Hvordan er samlingen gjennomført?";
reset($r);
foreach ($r as $e)
{
   echo ";Fysisk";
}
echo "\n";

// Line 4: Without teatcher
echo ";;;;;;;;Timer uten lærer";
reset($r);
foreach ($r as $e)
{
   echo ";0";
}
echo "\n";

// Line 5: With teatcher
echo "Navn;Adresse;Postnummer;Poststed;Epostadresse;Telefon;Kjønn;Fødselsår;Timer med lærer";
reset($r);
foreach ($r as $e)
{
   $t = explode('-', $e['time']);
   echo ";", intval($t[1]) - intval($t[0]);
}
echo "\n";


$query = "SELECT person.id as id, "
        . "person.firstname as firstname, "
        . "person.lastname as lastname, "
        . "person.firstname as firstname, "
        . "person.middlename as middlename, "
        . "person.address as address, "
        . "person.postcode as postcode, "
        . "person.city as city, "
        . "person.email as email, "
        . "person.phone1 as phone1, "
        . "person.sex as sex, "
        . "person.birthday as birthday, "
        . "plan.date as date, "
        . "plan.time as time, "
        . "absence.status as status "
        . "FROM plan, participant, person "
        . "left join absence "
        . "on absence.id_person = person.id "
        . "where participant.id_project = $id_project "
        . "and participant.stat_final = $db->par_stat_yes "
        . "and person.id = participant.id_person "
        . "and plan.id_project = participant.id_project "
        . "and plan.event_type = $db->plan_evt_rehearsal "
        . "and absence.id_plan = plan.id "
        . "order by person.firstname, person.lastname, person.id, plan.date";

$stmt = $db->query($query);

$prev_id = 0;

foreach ($stmt as $e)
{
   if ($prev_id != $e['id'])
   {
      if ($prev_id > 0)
         echo "\n";
      $name = $e['firstname'] . " " . $e['middlename'] . " " . $e['lastname'];
      $address = $e['address'];
      $postcode = sprintf('%04d', $e['postcode']);
      $city = $e['city'];
      $email = $e['email'];
      $phone = str_replace(' ', '', $e['phone1']);
      $sex = $db->per_sex[(is_null($e['sex'])) ? $db->per_sex_unknown : $e['sex']];
      $born = date('Y', $e['birthday']);

      $str = "$name;$address;$postcode;$city;$email;$phone;$sex;$born;";      
      echo mb_convert_encoding($str, 'UTF-8');
   }
   
   echo ($e['status'] == $db->abs_stat_in || $e['status'] == $db->abs_stat_part) ? ";x" : ";";

   $prev_id = $e['id'];
}

