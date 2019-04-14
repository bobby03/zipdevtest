<?php
class Contacts {
  // Connection instance
  private $connection;

  // table name
  private $table_name = "contacts";


  public function __construct( $connection ){
    $this->connection = $connection;
  }

  public function create() {

  };

  public function read( $id = null ){

    $query = "SELECT * FROM " . $this->$table_name;

    if( $id != null ) {
      $query .= " WHERE id = '" . $id . "'";
    }


    $stmt = $this->connection->prepare($query);

    $stmt->execute();

    return $stmt;
  }

  public function update() {

  };

  public function delete() {

  };
}
?>
