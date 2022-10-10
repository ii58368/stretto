<?php

require 'person_query.php';

$filename = 'person.csv';
header("Content-Type: text/csv; name=\"$filename\"");
header("Content-disposition: attachment; filename=\"$filename\"");

$query = person_query();
$stmt = $db->query($query);

// Required format: fornavn;etternavn;Medlemskontingent;kjønn;fødselsdato;;postnummer;;;;;;;;;

echo "Kundenummer;Navn;Adresse;Postnummer;Poststed;Epostadresse;Telefon;Kjønn;Fødselsår;Godtatt deling av info;Kontingent\n";

foreach ($stmt as $e)
{
   /*
     $query = "select person.id as id, id_visma, firstname, middlename, lastname, "
     . "status, instrument, email, phone1 "
     . "from person, instruments "
     . "where person.id = $id "
     . "and instruments.id = person.id_instruments";
     $stmt = $db->query($query);
     $e = $stmt->fetch(PDO::FETCH_ASSOC);
    * 
    * Example:
    * Navn                    | Adresse | Postnummer | Poststed    | Epostadresse | Telefon | Kjønn  | Fødselsår | Godtatt deling av info | Kontingent
    * Håvard Hungnes Lien     |         | 0464       | Oslo        |              |         | Mann   | 1975      | Ja                     | Full
    * Gislaug Marie Moe Gimse |         | 2214       | Kongsvinger |              |         | Kvinne | 1978      | nei                    | Redusert
    */
   
   $custom_id = $e['id_visma'];
   $name = $e['firstname'] . " " . $e['middlename'] . " " . $e['lastname'];
   $address = $e['address'];
   $postcode = sprintf('%04d', $e['postcode']);
   $city = $e['city'];
   $email = $e['email'];
   $phone = $e['phone1'];
   $sex = $db->per_sex[(is_null($e['sex'])) ? $db->per_sex_unknown : $e['sex']];
   $born = date('Y', $e['birthday']);
   $gdpr = $e['gdpr_ts'] > 0 ? 'ja' : 'nei';
   $fee = $db->per_fee[$e['fee']];
   
   $str = "$custom_id;$name;$address;$postcode;$city;$email;$phone;$sex;$born;$gdpr;$fee";
   echo mb_convert_encoding($str, 'UTF-8');

   echo "\n"; 
}

