<?php
    class database{
    private $servername = "localhost";
    private $username ="root";
    private $password ="";
    private $databasename = "production";
    protected $conn = null;

        function connection_database(){
            try{
                $this->conn = new PDO("mysql:host=" . $this->servername . ";port=3306;dbname=" . $this->databasename, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
            catch(PDOException $e){

                die("Lỗi kết nối CSDL: " . $e->getMessage());
            }
            return $this->conn;
        }
    }
?>