<?php
class conexionController {
    private $host = 'sql105.infinityfree.com';
    private $dbname = 'if0_40439028_airbnb';
    private $username = 'if0_40439028';
    private $password = 'CjNuGYpSi9Ho'; 
    private $conexion;

    public function __construct() {
        try {
            $this->conexion = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 10
                ]
            );
        } catch (PDOException $e) {
            error_log("Error de conexión BD: " . $e->getMessage());
            $this->conexion = null;
        }
    }

    public function getConexion() {
        return $this->conexion;
    }
}
?>