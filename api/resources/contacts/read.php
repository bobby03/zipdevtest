<?php
  header("Content-Type: application/json; charset=UTF-8");
  include_once '../../config/db.php';
  include_once '../../classes/Contacts.php';

  $db = new Db();
  $connection = $db->dbConnect();

  $contacts = new Contacts( $connection );
  $data  = $contacts->read();
   echo json_encode($data);
?>
