<?php
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: POST");
  header("Access-Control-Max-Age: 3600");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

  include_once '../../config/db.php';
  include_once '../../classes/Contacts.php';

  $db = new Db();
  $connection = $db->dbConnect();

  //check if post is not empty and all the fields are set properly
  if( empty( $_POST ) ) {
    echo json_encode( array( "response"=> false, "message"=> "Please fill al the fields" ) );
    return false;
  }
  if( !isset( $_POST['first_name'] ) && !isset( $_POST['last_name'] ) ) {
    echo json_encode( array( "response"=> false, "message"=> "Please fill at least first_name and last_name fields" ) );
    return false;
  }

  $contacts = new Contacts( $connection );

  // set the $_POST to the private variable to insert a new contact
  $contacts->contentArray = $_POST;
   echo $contacts->create();

?>
