<?php
class conexionController {
    // CONFIGURACIÓN CORREGIDA PARA INFINITY FREE
    private $host = 'sql105.infinityfree.com'; // El hostname de tu panel
    private $dbname = 'if0_40439028_airbnb';
    private $username = 'if0_40439028';
    private $password = 'CjNuGYp519Ho';
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
                    PDO::ATTR_TIMEOUT => 5 // Timeout de 5 segundos
                ]
            );
        } catch (PDOException $e) {
            error_log("Error de conexión BD Infinity Free: " . $e->getMessage());
            $this->conexion = null;
        }
    }

    public function getConexion() {
        return $this->conexion;
    }
}
?>