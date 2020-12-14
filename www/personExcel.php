<?php

require 'person_query.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-disposition: attachment; filename=participants.xls");

$query = person_query();
$stmt = $db->query($query);

// Required format: fornavn;etternavn;Medlemskontingent;kjønn;fødselsdato;;postnummer;;;;;;;;;

foreach ($stmt as $e)
{
   /*
     $query = "select person.id as id, firstname, middlename, lastname, "
     . "status, instrument, email, phone1 "
     . "from person, instruments "
     . "where person.id = $id "
     . "and instruments.id = person.id_instruments";
     $stmt = $db->query($query);
     $e = $stmt->fetch(PDO::FETCH_ASSOC);
    */
   $sex = (is_null($e['sex'])) ? $db->per_sex_unknown : $e['sex'];
   $str = $e['firstname'] . " " . $e['middlename'] . ";" . $e['lastname'] . ";"
           . $db->per_fee[$e['fee']] . ";"
           . $db->per_sex2[$sex] . ";" . date('d.m.Y', $e['birthday']) . ";;"
           . sprintf('%04d', $e['postcode']) . ";;;;;;;;;";
   echo mb_convert_encoding($str, 'UTF-8');

   echo "\n";
}

