<?php
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: PUT");
  header("Access-Control-Max-Age: 3600");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

  include_once '../../config/db.php';
  include_once '../../classes/Contacts.php';

  if($_SERVER['REQUEST_METHOD'] != "PUT") {
     echo json_encode( array( "response"=> false, "message"=> "Please use a proper http method (PUT)" ) );
     return false;
  }

  parse_str(file_get_contents("php://input"), $_PUT);
  foreach ($_PUT as $key => $value) {
		unset($_PUT[$key]);
		$_PUT[str_replace('amp;', '', $key)] = $value;
	}

	$PUT = array_merge($_REQUEST, $_PUT);
  if( !isset( $PUT['id'] ) ) {
    echo json_encode( array( "response"=> false, "message"=> "Please add the contact id to update" ) );
    return false;
  }

  $db = new Db();
  $connection = $db->dbConnect();
  $contacts = new Contacts( $connection );

  $contact = $contacts->read((int)$PUT['id']);
  if( $contact == false ) {
    echo json_encode( array( "response"=> false, "message"=> "This user doesn't exist" ) );
    return false;
  }
  $contacts->contentArray = $PUT;

  echo $contacts->update();

?>
