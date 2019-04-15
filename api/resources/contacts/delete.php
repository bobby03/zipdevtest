<?php

  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: DELETE");
  header("Access-Control-Max-Age: 3600");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

  include_once '../../config/db.php';
  include_once '../../classes/Contacts.php';

  if($_SERVER['REQUEST_METHOD'] != "DELETE") {
     echo json_encode( array( "response"=> false, "message"=> "Please use a proper http method (DELETE)" ) );
     return false;
  }

    parse_str(file_get_contents("php://input"), $_DELETE);
    foreach ($_DELETE as $key => $value) {
  		unset($_DELETE[$key]);
  		$_DELETE[str_replace('amp;', '', $key)] = $value;
  	}

  	$DELETE = array_merge($_REQUEST, $_DELETE);
    if( !isset( $DELETE['id'] ) ) {
       echo json_encode( array( "response"=> false, "message"=> "Please select the contact id to delete" ) );
       return false;
    }

    $db = new Db();
    $connection = $db->dbConnect();
    $contacts = new Contacts( $connection );
    $contact = $contacts->read((int)$DELETE['id']);
    if( $contact == false ) {
      echo json_encode( array( "response"=> false, "message"=> "This user doesn't exist" ) );
      return false;
    }
    $contacts->contentArray = $DELETE;
    echo $contacts->delete();

?>
