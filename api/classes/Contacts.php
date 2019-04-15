<?php
class Contacts {
  // Connection instance
  private $connection;

  // table name
  private $table_name = "`contacts`";

  public $contentArray;

  public function __construct( $connection ){
    $this->connection = $connection;
  }

  public function create() {
    try {
      $return = array();
      // adjust an optional extra content
      if( !isset( $this->contentArray['extra'] ) ) {
        $this->contentArray['extra'] = NULL;
      } else {
        $this->contentArray['extra'] = json_encode($this->contentArray['extra']);
      }

      // preparing data for insertion
      $prepare_data = [
        "first_name" => $this->contentArray['first_name'],
        "last_name"  => $this->contentArray['last_name'],
        "extra"      => $this->contentArray['extra']
      ];

      //insert query
      $insert = "INSERT INTO " . $this->table_name . "(first_name, last_name, extra) " .
                "VALUES (:first_name, :last_name, :extra)";
      $stmt = $this->connection->prepare($insert);

      $stmt->execute($prepare_data);

      //this is the id from last query insertion
      $id = (int)$this->connection->lastInsertId();

      //checking if the user send the emails array
      if( isset( $this->contentArray['emails'] ) ) {
        $this->insertContactEmails( $id, $this->contentArray['emails'] );
      }
      //checking if the user send the emails array
      if( isset( $this->contentArray['phones'] ) ) {
        $this->insertContactPhones( $id, $this->contentArray['phones'] );
      }
      return json_encode( array(
        "response" => true,
        "message"  => "The contact was added succesfully",
        "contact_id" => $id
      ));
    } catch (PDOException $e) {
       echo "Error: " . $e->getMessage();
    }

  }

  //  method to add all emails for a contact id
  public function insertContactEmails( $contact_id, $emails ) {

    // check if the contact id is an integer
    if( $contact_id > 0 ) {
      // check if the $emails is an array
      if ( is_array( $emails ) ) {
        foreach( $emails as $mail ) {
          // if name and email are not set in this row, will not be inserted
          if( isset( $mail['name'] ) && isset( $mail['email'] ) ) {
            $insertEmailQuery = 'INSERT INTO `contact_emails` (contact_id, name, email)' .
                                "VALUES (:contact_id, :name, :email)";
            $stmt = $this->connection->prepare( $insertEmailQuery );
            $stmt->bindValue(':contact_id', $contact_id);
            $stmt->bindValue(':name', $mail['name']);
            $stmt->bindValue(':email', $mail['email']);
            $stmt->execute();
          }
        }
      }
    }
  }

  //method to insert all the phones listed by the user just created
  public function insertContactPhones( $contact_id, $phones ) {

    // check if the contact id got the proper GUID format
    if( $contact_id > 0 ) {

      // check if the $phones is an array
      if ( is_array( $phones ) ) {
        foreach( $phones as $phone ) {

          // if name and phone are not set in this row, will not be inserted
          if( isset( $phone['name'] ) && isset( $phone['phone'] ) ) {
            $insertPhoneQuery = "INSERT INTO `contact_phones` (contact_id, name, phone) " .
                                "VALUES (:contact_id, :name, :phone)";
            $stmt = $this->connection->prepare( $insertPhoneQuery );
            $stmt->bindValue(':contact_id', $contact_id);
            $stmt->bindValue(':name', $phone['name']);
            $stmt->bindValue(':phone', $phone['phone']);
            $stmt->execute();
          }
        }
      }
    }
  }

  public function read( $id = null ){

    $query = "SELECT *  FROM " . $this->table_name ;

    if( $id != null ) {
      $query .= " WHERE id = '" . $id . "'";
    }


    $stmt = $this->connection->prepare($query);

    $stmt->execute();

    if( $stmt->rowCount() > 0 ) {

      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $contacts = array();
      foreach( $data as $row ) {
        $contact = $row;
        $contact['phones'] = $this->getPhones( $row['id'] );
        $contact['emails'] = $this->getEmails(  $row['id'] );
        $contacts[] = $contact;
      }
      return $contacts;

    } else {
      return false;
    }
  }

  public function getPhones( $id ) {

    $query = "SELECT id, name, phone FROM `contact_phones` WHERE `contact_id` = :contact_id";
    $stmt = $this->connection->prepare( $query );
    $stmt->execute( array( "contact_id" => $id ) );
    if( $stmt->rowCount() > 0 ) {
      $phones = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $phones;
    }
    else {
      return false;
    }
  }

  public function getEmails( $id ) {

    $query = "SELECT id, name, email FROM `contact_emails` WHERE `contact_id` = :contact_id";

    $stmt = $this->connection->prepare( $query );
    $stmt->execute( array( "contact_id" => $id ) );

    if( $stmt->rowCount() > 0 ) {

      $mails = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $mails;
    }
    else {
      return false;
    }
  }

  public function update() {
    $id  = (int) $this->contentArray['id'];
    if( $id <= 0 ) {
      echo json_encode( array( "response"=> false, "message"=> "This is not an id, please use a numeric id" ) );
      return false;
    }
    $phones = array();
    $mails = array();
    unset( $this->contentArray['id'] );

    if(isset( $this->contentArray['phones'] ) ) {
      $phones = $this->contentArray['phones'];
      $this->updateUserPhones( $id, $phones );
      unset($this->contentArray['phones']);
    }


    if(isset( $this->contentArray['emails'] ) ) {
      $mails = $this->contentArray['emails'];
      $this->updateUserEmails( $id, $mails );
      unset($this->contentArray['emails']);
    }
    if(count( $this->contentArray ) > 0 ) {
      $update_prepare = array();
      $update = "UPDATE " . $this->table_name . " SET";
      foreach($this->contentArray as $key => $value) {
        $update .= " ". $key ." = :" .$key . ",";
        if($key == 'extra') {
          $value = json_encode($value);
        }
        $update_prepare[$key] = $value;
      }
      $update = rtrim($update, ",");
      $update .= " WHERE id = :id";
      $update_prepare['id'] = $id;

      $stmt = $this->connection->prepare( $update );
      $stmt->execute( $update_prepare );

      return json_encode( array(
        "response" => true,
        "message"  => "The user has been updated succesfully",
        "id"  => $id
      ));
    }

    return json_encode( array(
      "response" => true,
      "message"  => "The user has been updated succesfully",
      "id"  => $id
    ));

  }

  public function updateUserEmails( $contact_id, $mails ) {
    if( is_array($mails) ) {
      foreach( $mails as $mail ) {
        $option = $mail['action'];
        unset($mail['action']);
        switch ( $option ) {
          case 'create':
            $this->insertContactEmails( $contact_id, array( $mail ) );
            break;
          case 'update':
            $id = (int)$mail['id'];
            unset( $mail['id'] );
            if( count($mail) > 0 ) {
              $update = "UPDATE `contact_emails` SET ";
              $update_mail = array();
              foreach($mail as $key => $value ) {
                $update .= " `" . $key . "` = :" . $key . ",";
                $update_mail[$key] = $value;
              }
              $update = rtrim($update, ",");
              $update .= " WHERE id = :id AND contact_id = :contact_id";
              $update_mail['id'] = $id;
              $update_mail['contact_id'] = $contact_id;
              $stmt = $this->connection->prepare( $update );
              $stmt->execute( $update_mail );
            }

            break;

          case 'delete':

            $delete = "DELETE FROM `contact_emails` WHERE `id` = :id";
            $delete_mail['id'] = (int)$mail['id'];
            $stmt = $this->connection->prepare( $delete );
            $stmt->execute( $delete_mail );
            break;

          default:
            // code...
            break;
        }
      }
    }
  }
  public function updateUserPhones( $contact_id, $phones ) {
    if( is_array($phones) ) {
      foreach( $phones as $phone ) {
        $option = $phone['action'];
        unset( $phone['action'] );
        switch ($option) {
          case 'create':
            $this->insertContactPhones( $contact_id, array( $phone ) );
            break;
          case 'update':
            $id = (int)$phone['id'];
            unset( $phone['id'] );
            if( count($phone) > 0 ) {
              $update = "UPDATE `contact_phones` SET ";
              $update_phone = array();
              foreach($phone as $key => $value ) {
                $update .= " `" . $key . "` = :" . $key . ",";
                $update_phone[$key] = $value;
              }
              $update = rtrim($update, ",");
              $update .= " WHERE id = :id AND contact_id = :contact_id";
              $update_phone['id'] = $id;
              $update_phone['contact_id'] = $contact_id;

              $stmt = $this->connection->prepare( $update );
              $stmt->execute( $update_phone );
            }

            break;

          case 'delete':

            $delete = "DELETE FROM `contact_phones` WHERE `id` = :id";
            $delete_phone['id'] = (int)$phone['id'];
            $stmt = $this->connection->prepare( $delete );
            $stmt->execute( $delete_phone );
            break;

          default:
            // code...
            break;
        }
      }
    }
  }
  public function delete() {

    $id  = (int) $this->contentArray['id'];

    echo "delete: ";
    var_dump( $id );
    if( $id <= 0 ) {
      echo json_encode( array( "response"=> false, "message"=> "This is not an id, please use a numeric id" ) );
      return false;
    }
    $hasEmails = $this->getEmails( $id );
    if( $hasEmails != false ) {
      $this->removeContactEmails( $id );
    }
    $hasPhones = $this->getPhones( $id );
    if( $hasPhones != false ) {
      $this->removeContactPhones( $id );
    }
    $query = 'DELETE FROM `contacts` WHERE id = :id';
     $delete_data['id'] = (int)$id;
    $stmt = $this->connection->prepare( $query );
    $stmt->execute( $delete_data );

    return json_encode( array( "response" => true, "message"=> "the user with id " . $id . " has been removed" ) );

  }
  public function removeContactEmails( $contact_id ) {
    $query = "DELETE FROM `contact_emails` WHERE `contact_id` = :contact_id";
    $prepare_data['contact_id'] = $contact_id;
    $stmt = $this->connection->prepare( $query );
    $stmt->execute( $prepare_data );

  }
  public function removeContactPhones( $contact_id ) {
    $query = "DELETE FROM `contact_phones` WHERE `contact_id` = :contact_id";
    $prepare_data['contact_id'] = $contact_id;
    $stmt = $this->connection->prepare( $query );
    $stmt->execute( $prepare_data );

  }

}
?>
