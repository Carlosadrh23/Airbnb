<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');

// Cargar el modelo
require_once 'UserModel.php';

// Obtener datos
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || json_last_error() !== JSON_ERROR_NONE) {
    $data = $_POST;
}

$accion = $data['accion'] ?? '';
$userModel = new UserModel();

switch ($accion) {
    case 'registro':
        $nombre = trim($data['nombre'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($nombre) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
            exit;
        }

        if (strlen($nombre) < 3) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email inválido']);
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
            exit;
        }

        $resultado = $userModel->registrarUsuario($nombre, $email, $password);
        
        if ($resultado['success']) {
            $_SESSION['user_id'] = $resultado['user_id'];
            $_SESSION['nombre'] = $nombre;
            $_SESSION['email'] = $email;
            $_SESSION['last_activity'] = time();
        }

        echo json_encode($resultado);
        break;

    case 'login':
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email inválido']);
            exit;
        }

        $resultado = $userModel->iniciarSesion($email, $password);
        
        if ($resultado['success']) {
            $_SESSION['user_id'] = $resultado['usuario']['id'];
            $_SESSION['nombre'] = $resultado['usuario']['nombre'];
            $_SESSION['email'] = $resultado['usuario']['email'];
            $_SESSION['last_activity'] = time();
        }

        echo json_encode($resultado);
        break;

    case 'logout':
        $_SESSION = array();
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Sesión cerrada']);
        break;

    case 'verificar_sesion':
        $logueado = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
        
        if ($logueado) {
            echo json_encode([
                'success' => true,
                'logueado' => true,
                'usuario' => [
                    'id' => $_SESSION['user_id'],
                    'nombre' => $_SESSION['nombre'],
                    'email' => $_SESSION['email']
                ]
            ]);
        } else {
            echo json_encode(['success' => true, 'logueado' => false]);
        }
        break;

    case 'test_db':
        // Endpoint para probar conexión
        $conectado = $userModel->verificarConexionBD();
        echo json_encode([
            'success' => true,
            'db_conectada' => $conectado,
            'message' => $conectado ? 'Base de datos conectada' : 'Sin conexión a BD'
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
?>