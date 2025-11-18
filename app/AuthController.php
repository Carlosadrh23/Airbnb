<?php
// CONFIGURACIÓN DE SESIÓN
ini_set('display_errors', 0);
error_reporting(0);

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

session_start();

// Headers PRIMERO
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');

// Obtener datos
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || json_last_error() !== JSON_ERROR_NONE) {
    $data = $_POST;
}

$accion = $data['accion'] ?? '';

// SISTEMA DE ALMACENAMIENTO EN ARCHIVO (simula base de datos)
$archivoDB = 'usuarios_db.json';

function cargarBaseDatos() {
    global $archivoDB;
    if (file_exists($archivoDB)) {
        $contenido = file_get_contents($archivoDB);
        return json_decode($contenido, true) ?: [];
    }
    // Si no existe, crear con algunos usuarios de ejemplo
    $usuariosBase = [
        'mexa27442@gmail.com' => [
            'id' => '1',
            'nombre' => 'Carlos',
            'password' => password_hash('123123', PASSWORD_DEFAULT),
            'fecha_registro' => date('Y-m-d H:i:s')
        ],
        'test@test.com' => [
            'id' => '2', 
            'nombre' => 'Usuario Test',
            'password' => password_hash('123456', PASSWORD_DEFAULT),
            'fecha_registro' => date('Y-m-d H:i:s')
        ]
    ];
    guardarBaseDatos($usuariosBase);
    return $usuariosBase;
}

function guardarBaseDatos($datos) {
    global $archivoDB;
    file_put_contents($archivoDB, json_encode($datos, JSON_PRETTY_PRINT));
}

function buscarUsuarioPorEmail($email) {
    $bd = cargarBaseDatos();
    return $bd[$email] ?? null;
}

function registrarUsuarioBD($nombre, $email, $password) {
    $bd = cargarBaseDatos();
    
    if (isset($bd[$email])) {
        return ['success' => false, 'message' => 'El correo electrónico ya está registrado'];
    }
    
    $nuevoUsuario = [
        'id' => (string)(count($bd) + 1),
        'nombre' => $nombre,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'fecha_registro' => date('Y-m-d H:i:s')
    ];
    
    $bd[$email] = $nuevoUsuario;
    guardarBaseDatos($bd);
    
    return [
        'success' => true, 
        'message' => 'Usuario registrado exitosamente',
        'user_id' => $nuevoUsuario['id']
    ];
}

function verificarLogin($email, $password) {
    $usuario = buscarUsuarioPorEmail($email);
    
    if ($usuario && password_verify($password, $usuario['password'])) {
        return [
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'usuario' => [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'email' => $email
            ]
        ];
    }
    
    return ['success' => false, 'message' => 'Correo o contraseña incorrectos'];
}

// MANEJAR ACCIONES
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

        $resultado = registrarUsuarioBD($nombre, $email, $password);
        
        if ($resultado['success']) {
            // Guardar en sesión
            $_SESSION['usuario_id'] = $resultado['user_id'];
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['usuario_email'] = $email;
            $_SESSION['last_activity'] = time();
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

        $resultado = verificarLogin($email, $password);
        
        if ($resultado['success']) {
            // Guardar en sesión
            $_SESSION['usuario_id'] = $resultado['usuario']['id'];
            $_SESSION['usuario_nombre'] = $resultado['usuario']['nombre'];
            $_SESSION['usuario_email'] = $resultado['usuario']['email'];
            $_SESSION['last_activity'] = time();
        }

        echo json_encode($resultado);
        break;

    case 'logout':
        // Destruir sesión completamente
        $_SESSION = array();
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Sesión cerrada']);
        break;

    case 'verificar_sesion':
        // Verificar si hay sesión activa
        $logueado = isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
        
        if ($logueado) {
            echo json_encode([
                'success' => true,
                'logueado' => true,
                'usuario' => [
                    'id' => $_SESSION['usuario_id'],
                    'nombre' => $_SESSION['usuario_nombre'],
                    'email' => $_SESSION['usuario_email']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true, 
                'logueado' => false
            ]);
        }
        break;

    default:
        echo json_encode([
            'success' => false, 
            'message' => 'Acción no válida'
        ]);
        break;
}
?>