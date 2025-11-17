<?php
class conexionController {
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private $conexion;

    public function __construct() {
        // Detectar si estamos en local o en producción
        $isLocal = $_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1';

        if ($isLocal) {
            // CONFIGURACIÓN LOCAL XAMPP
            $this->host = 'localhost';
            $this->port = '3307'; // Puerto de mysql en xampp
            $this->dbname = 'homeaway';
            $this->username = 'root';
            $this->password = '';
        } else {
            // CONFIGURACIÓN DE HOSTING (InfinityFree)
            $this->host = 'sql303.infinityfree.com';
            $this->port = '3306';
            $this->dbname = 'if0_40431484_projectoairbnbhomeaway';
            $this->username = 'if0_40431484';
            $this->password = 'dWCM70G7FWwQU8x';
        }

        try {
            $this->conexion = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function getConexion() {
        return $this->conexion;
    }
}
?>