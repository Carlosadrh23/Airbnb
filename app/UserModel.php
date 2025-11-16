<?php
require_once 'conexionController.php';

class UserModel {
    private $conexion;

    public function __construct() {
        $db = new conexionController();
        $this->conexion = $db->getConexion();
    }

    // Registrar nuevo usuario
    public function registrarUsuario($nombre, $email, $password) {
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
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Iniciar sesión
    public function iniciarSesion($email, $password) {
        try {
            $stmt = $this->conexion->prepare(
                "SELECT id, nombre, email, password FROM usuarios WHERE email = ?"
            );
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password'])) {
                // Contraseña correcta
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
                return ['success' => false, 'message' => 'Correo o contraseña incorrectos'];
            }

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Obtener usuario por email
    public function obtenerUsuarioPorEmail($email) {
        try {
            $stmt = $this->conexion->prepare(
                "SELECT id, nombre, email FROM usuarios WHERE email = ?"
            );
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>