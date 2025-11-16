<?php
// CONFIGURACIÓN DE SESIÓN - IMPORTANTE: Configurar ANTES de session_start()
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_lifetime', 86400); // 24 horas
ini_set('session.gc_maxlifetime', 86400);

// Configurar parámetros de cookie
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Iniciar sesión
session_start();

// Headers JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');

// LOG DE DIAGNÓSTICO
error_log("=== NUEVA REQUEST ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("Session ID: " . session_id());
error_log("Session data: " . json_encode($_SESSION));
error_log("Cookies: " . json_encode($_COOKIE));

require_once 'UserModel.php';

$userModel = new UserModel();

// Obtener datos
$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!$data) {
    $data = $_POST;
}

$accion = $data['accion'] ?? '';

error_log("Acción: $accion");

switch ($accion) {
    case 'registro':
        $nombre = trim($data['nombre'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        // Validaciones
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
            // Guardar en sesión
            $_SESSION['usuario_id'] = $resultado['user_id'];
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_email'] = $email;
            $_SESSION['last_activity'] = time();
            
            // Forzar escritura inmediata
            session_commit();
            
            error_log("Sesión guardada después de registro");
            error_log("Session después: " . json_encode($_SESSION));
            error_log("Session ID: " . session_id());
        }

        echo json_encode($resultado);
        break;

    case 'login':
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        // Validaciones
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
            // Guardar en sesión
            $_SESSION['usuario_id'] = $resultado['usuario']['id'];
            $_SESSION['usuario_nombre'] = $resultado['usuario']['nombre'];
            $_SESSION['usuario_email'] = $resultado['usuario']['email'];
            $_SESSION['last_activity'] = time();
            
            // Forzar escritura inmediata
            session_commit();
            
            error_log("Sesión guardada después de login");
            error_log("Session después: " . json_encode($_SESSION));
            error_log("Session ID: " . session_id());
        }

        echo json_encode($resultado);
        break;

    case 'logout':
        // Destruir sesión completamente
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        error_log("Sesión cerrada");
        echo json_encode(['success' => true, 'message' => 'Sesión cerrada']);
        break;

    case 'verificar_sesion':
        error_log("Verificando sesión");
        error_log("Session ID: " . session_id());
        error_log("Session data: " . json_encode($_SESSION));
        error_log("COOKIE array: " . json_encode($_COOKIE));
        
        // Verificar si hay sesión activa
        $logueado = isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
        
        if ($logueado) {
            error_log("Usuario encontrado en sesión: " . $_SESSION['usuario_id']);
            echo json_encode([
                'success' => true,
                'logueado' => true,
                'usuario' => [
                    'id' => $_SESSION['usuario_id'],
                    'nombre' => $_SESSION['usuario_nombre'] ?? 'Usuario',
                    'email' => $_SESSION['usuario_email'] ?? ''
                ],
                'debug' => [
                    'session_id' => session_id(),
                    'session_name' => session_name(),
                    'has_cookie' => isset($_COOKIE[session_name()]),
                    'session_data' => $_SESSION
                ]
            ]);
        } else {
            error_log("No hay usuario en sesión");
            echo json_encode([
                'success' => true, 
                'logueado' => false,
                'debug' => [
                    'session_id' => session_id(),
                    'session_name' => session_name(),
                    'session_content' => $_SESSION,
                    'has_cookie' => isset($_COOKIE[session_name()]),
                    'cookie_value' => $_COOKIE[session_name()] ?? 'no-cookie'
                ]
            ]);
        }
        break;

    default:
        echo json_encode([
            'success' => false, 
            'message' => 'Acción no válida',
            'accion_recibida' => $accion
        ]);
        break;
}

error_log("=== FIN REQUEST ===");
?>