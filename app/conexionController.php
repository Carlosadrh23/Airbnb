<?php
class conexionController {
    private $host = 'sql303.infinityfree.com';
    private $port = '3306';
    private $dbname = 'if0_40431484_homeaway'; // ← Nombre completo
    private $username = 'if0_40431484'; // ← Sin el nombre de la BD
    private $password = 'dWCM70G7FWwQU8x';
    private $conexion;

    public function __construct() {
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