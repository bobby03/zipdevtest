<?php
 class Db {
   private $host   = 'localhost';
   private $user   = "root";
   private $pwd    = "";
   private $dbname = "zipdevtest";

   public $connection;

   pubic function dbConnect() {
     $this->connection = null;
     try {
        $this->connection = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname, $this->user, $this->password);
        $this->connection->exec("set names utf8");
     } catch (PDOException $exception) {
        echo "Error: " . $exception->getMessage();
     }
     
     return $this->connection;
   };

 }
?>
