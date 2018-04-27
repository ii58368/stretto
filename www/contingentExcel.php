<?php
require_once 'request.php';
require_once 'conf/opendb.php';

header( "Content-Type: application/vnd.ms-excel" );
header( "Content-disposition: attachment; filename=contingent.xls" );
 
echo "Fornavn\tMellomnavn\tEtternavn\tInstrument\tStatus\tEmail\tMobil\tBetalinger\n";
foreach (request('p') as $id)
{
   $query = "select person.id as id, firstname, middlename, lastname, "
           . "status, instrument, email, phone1 "
            . "from person, instruments "
            . "where person.id = $id "
            . "and instruments.id = person.id_instruments";
   $stmt = $db->query($query);
   $e = $stmt->fetch(PDO::FETCH_ASSOC);

   echo $e['firstname'] . "\t" . $e['middlename'] . "\t" . $e['lastname'] . "\t" .
        $e['instrument'] . "\t" . $db->per_stat[$e['status']] . "\t" .
        $e['email'] . "\t" . $e['phone1'] . "\t";
   
   $q2 = "select * from contingent where id_person = " . $e['id'];
   $s2 = $db->query($q2);
   foreach ($s2 as $e2)
   {
      echo date('d.m.Y', $e2['ts']) . "\t" . $db->con_stat[$e2['status']] .
              "\t" . $e2['amount'] . "\t";
   }
   echo "\n";
}

?>
