<?php
require_once 'conexionController.php';

class UserModel {
    private $conexion;

    public function __construct() {
        $db = new conexionController();
        $this->conexion = $db->getConexion();
    }

    public function registrarUsuario($nombre, $email, $password) {
        if (!$this->conexion) {
            return ['success' => false, 'message' => 'Error de conexión con la base de datos'];
        }

        try {
            // Verificar si el email ya existe
            $stmt = $this->conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'El correo electrónico ya está registrado'];
            }

            // Encriptar contraseña
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar usuario
            $stmt = $this->conexion->prepare(
                "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)"
            );
            
            if ($stmt->execute([$nombre, $email, $passwordHash])) {
                return [
                    'success' => true, 
                    'message' => 'Usuario registrado exitosamente',
                    'user_id' => $this->conexion->lastInsertId()
                ];
            } else {
                return ['success' => false, 'message' => 'Error al registrar usuario'];
            }

        } catch (PDOException $e) {
            error_log("Error en registro: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en el servidor de base de datos'];
        }
    }

    public function iniciarSesion($email, $password) {
        if (!$this->conexion) {
            return ['success' => false, 'message' => 'Error de conexión con la base de datos'];
        }

        try {
            $stmt = $this->conexion->prepare(
                "SELECT id, nombre, email, password FROM usuarios WHERE email = ?"
            );
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario) {
                if (password_verify($password, $usuario['password'])) {
                    return [
                        'success' => true,
                        'message' => 'Inicio de sesión exitoso',
                        'usuario' => [
                            'id' => $usuario['id'],
                            'nombre' => $usuario['nombre'],
                            'email' => $usuario['email']
                        ]
                    ];
                } else {
                    return ['success' => false, 'message' => 'Contraseña incorrecta'];
                }
            } else {
                return ['success' => false, 'message' => 'Usuario no encontrado'];
            }

        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en el servidor de base de datos'];
        }
    }

    public function verificarConexionBD() {
        if (!$this->conexion) {
            return false;
        }
        
        try {
            $this->conexion->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>