<?php
// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('display_errors', 0);
error_reporting(0);

session_start();

// Headers CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: https://homeawayairbnb.infinityfreeapp.com');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar peticiones OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$servidor = "sql105.infinityfree.com";
$usuarioDb = "if0_40439028";
$passwordDb = "CjNuGYpSi9Ho";
$baseDatos = "if0_40439028_airbnb";

function responderJSON($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $conexion = new mysqli($servidor, $usuarioDb, $passwordDb, $baseDatos);
    
    if ($conexion->connect_error) {
        responderJSON([
            'success' => false,
            'message' => 'Error de conexión a la base de datos'
        ]);
    }
    
    $conexion->set_charset("utf8mb4");
    
    // ============================================
    // MÉTODOS POST
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Verificar sesión
        if(!isset($_SESSION['user_id'])) {
            responderJSON([
                'success' => false,
                'message' => 'Debes iniciar sesión',
                'requiere_login' => true
            ]);
        }
        
        $usuarioId = $_SESSION['user_id'];
        
        // Obtener datos del POST
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        $accion = isset($data['accion']) ? $data['accion'] : 'crear';
        
        // ============================================
        // CANCELAR RESERVACIÓN 
        // ============================================
        if($accion === 'cancelar') {
            $reservacionId = isset($data['id']) ? intval($data['id']) : 0;
            
            if($reservacionId <= 0) {
                responderJSON(['success' => false, 'message' => 'ID de reservación inválido']);
            }
            
            try {
                // Verificar que la reservación pertenezca al usuario
                $sqlVerificar = "SELECT id FROM reservaciones 
                                WHERE id = $reservacionId 
                                AND usuario_id = $usuarioId";
                
                $resultado = $conexion->query($sqlVerificar);
                
                if(!$resultado || $resultado->num_rows === 0) {
                    responderJSON([
                        'success' => false, 
                        'message' => 'Reservación no encontrada o no tienes permiso para cancelarla'
                    ]);
                }
                
                // Eliminar la reservación
                $sqlEliminar = "DELETE FROM reservaciones 
                               WHERE id = $reservacionId 
                               AND usuario_id = $usuarioId";
                
                if($conexion->query($sqlEliminar)) {
                    responderJSON([
                        'success' => true,
                        'message' => 'Reservación cancelada exitosamente'
                    ]);
                } else {
                    responderJSON([
                        'success' => false,
                        'message' => 'Error al cancelar la reservación'
                    ]);
                }
                
            } catch(Exception $e) {
                responderJSON([
                    'success' => false,
                    'message' => 'Error al procesar la cancelación'
                ]);
            }
        }
        
        // ============================================
        // CREAR RESERVACIÓN
        // ============================================
        $propiedadId = isset($data['propiedad_id']) ? intval($data['propiedad_id']) : 0;
        $fechaInicio = isset($data['fecha_inicio']) ? trim($data['fecha_inicio']) : '';
        $fechaFin = isset($data['fecha_fin']) ? trim($data['fecha_fin']) : '';
        $numHuespedes = isset($data['num_huespedes']) ? intval($data['num_huespedes']) : 0;
        
        // Validaciones
        if($propiedadId <= 0) {
            responderJSON(['success' => false, 'message' => 'Propiedad inválida']);
        }
        
        if(empty($fechaInicio) || empty($fechaFin)) {
            responderJSON(['success' => false, 'message' => 'Las fechas son obligatorias']);
        }
        
        if($numHuespedes <= 0) {
            responderJSON(['success' => false, 'message' => 'Debe haber al menos 1 huésped']);
        }
        
        // Validar que la fecha de inicio sea menor que la de fin
        if(strtotime($fechaInicio) >= strtotime($fechaFin)) {
            responderJSON(['success' => false, 'message' => 'La fecha de salida debe ser posterior a la de llegada']);
        }
        
        // Validar que las fechas no sean en el pasado
        if(strtotime($fechaInicio) < strtotime(date('Y-m-d'))) {
            responderJSON(['success' => false, 'message' => 'No puedes reservar fechas pasadas']);
        }
        
        try {
            // Obtener información de la propiedad
            $sqlPropiedad = "SELECT precio_noche, numero_noches, anfitrion_id 
                            FROM propiedades 
                            WHERE id = $propiedadId AND estado_publicacion = 'activo'";
            
            $resultado = $conexion->query($sqlPropiedad);
            
            if(!$resultado || $resultado->num_rows === 0) {
                responderJSON(['success' => false, 'message' => 'Propiedad no encontrada']);
            }
            
            $propiedad = $resultado->fetch_assoc();
            $precioNoche = $propiedad['precio_noche'];
            $nochesMinimas = $propiedad['numero_noches'];
            $anfitrionId = $propiedad['anfitrion_id'];
            
            // No permitir que el anfitrión reserve su propia propiedad
            if($usuarioId == $anfitrionId) {
                responderJSON(['success' => false, 'message' => 'No puedes reservar tu propia propiedad']);
            }
            
            // Calcular número de noches
            $fecha1 = new DateTime($fechaInicio);
            $fecha2 = new DateTime($fechaFin);
            $numeroNoches = $fecha1->diff($fecha2)->days;
            
            // Validar mínimo de noches
            if($numeroNoches < $nochesMinimas) {
                responderJSON([
                    'success' => false, 
                    'message' => "Esta propiedad requiere un mínimo de $nochesMinimas noches"
                ]);
            }
            
            // Calcular precio total
            $precioTotal = $precioNoche * $numeroNoches;
            
            // Verificar disponibilidad (que no haya otras reservas confirmadas en esas fechas)
            $sqlDisponibilidad = "SELECT id FROM reservaciones 
                                 WHERE propiedad_id = $propiedadId 
                                 AND estado_reservacion IN ('confirmada', 'pendiente')
                                 AND (
                                     (fecha_inicio <= '$fechaInicio' AND fecha_fin > '$fechaInicio')
                                     OR (fecha_inicio < '$fechaFin' AND fecha_fin >= '$fechaFin')
                                     OR (fecha_inicio >= '$fechaInicio' AND fecha_fin <= '$fechaFin')
                                 )";
            
            $resultadoDisponibilidad = $conexion->query($sqlDisponibilidad);
            
            if($resultadoDisponibilidad && $resultadoDisponibilidad->num_rows > 0) {
                responderJSON([
                    'success' => false, 
                    'message' => 'Lo sentimos, esta propiedad no está disponible en las fechas seleccionadas'
                ]);
            }
            
            // Iniciar transacción
            $conexion->begin_transaction();
            
            // Insertar reservación
            $sqlReserva = "INSERT INTO reservaciones (
                usuario_id, propiedad_id, fecha_inicio, fecha_fin, 
                numero_huespedes, precio_total, estado_reservacion
            ) VALUES (
                $usuarioId, 
                $propiedadId, 
                '$fechaInicio', 
                '$fechaFin', 
                $numHuespedes, 
                $precioTotal, 
                'confirmada'
            )";
            
            if(!$conexion->query($sqlReserva)) {
                $conexion->rollback();
                responderJSON([
                    'success' => false, 
                    'message' => 'Error al crear la reservación'
                ]);
            }
            
            $reservacionId = $conexion->insert_id;
            
            // Confirmar transacción
            $conexion->commit();
            
            responderJSON([
                'success' => true,
                'message' => '¡Reservación confirmada exitosamente!',
                'reservacion' => [
                    'id' => $reservacionId,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'numero_noches' => $numeroNoches,
                    'numero_huespedes' => $numHuespedes,
                    'precio_total' => $precioTotal
                ]
            ]);
            
        } catch(Exception $e) {
            if($conexion->ping()) {
                $conexion->rollback();
            }
            
            responderJSON([
                'success' => false,
                'message' => 'Error al procesar la reservación'
            ]);
        }
    }
    
    // ============================================
    // OBTENER MIS RESERVACIONES
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        if(!isset($_SESSION['user_id'])) {
            responderJSON([
                'success' => false,
                'message' => 'Debes iniciar sesión'
            ]);
        }
        
        $usuarioId = $_SESSION['user_id'];
        
        try {
            $sql = "SELECT 
                        r.id,
                        r.fecha_inicio,
                        r.fecha_fin,
                        r.numero_huespedes,
                        r.precio_total,
                        r.estado_reservacion,
                        r.fecha_reservacion,
                        p.tipo_alojamiento,
                        p.ciudad,
                        p.estado,
                        p.imagen_url,
                        u.nombre as nombre_anfitrion
                    FROM reservaciones r
                    INNER JOIN propiedades p ON r.propiedad_id = p.id
                    INNER JOIN usuarios u ON p.anfitrion_id = u.id
                    WHERE r.usuario_id = $usuarioId
                    ORDER BY r.fecha_reservacion DESC";
            
            $resultado = $conexion->query($sql);
            
            $reservaciones = [];
            while ($fila = $resultado->fetch_assoc()) {
                $reservaciones[] = [
                    'id' => $fila['id'],
                    'fecha_inicio' => $fila['fecha_inicio'],
                    'fecha_fin' => $fila['fecha_fin'],
                    'numero_huespedes' => $fila['numero_huespedes'],
                    'precio_total' => $fila['precio_total'],
                    'estado' => $fila['estado_reservacion'],
                    'fecha_reservacion' => $fila['fecha_reservacion'],
                    'propiedad' => [
                        'tipo' => ucfirst($fila['tipo_alojamiento']),
                        'ciudad' => $fila['ciudad'],
                        'estado' => $fila['estado'],
                        'imagen' => $fila['imagen_url']
                    ],
                    'anfitrion' => $fila['nombre_anfitrion']
                ];
            }
            
            responderJSON([
                'success' => true,
                'reservaciones' => $reservaciones,
                'total' => count($reservaciones)
            ]);
            
        } catch(Exception $e) {
            responderJSON([
                'success' => false,
                'message' => 'Error al obtener reservaciones'
            ]);
        }
    }
    
    // ============================================
    // ELIMINAR RESERVACIÓN 
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        
        if(!isset($_SESSION['user_id'])) {
            responderJSON([
                'success' => false,
                'message' => 'Debes iniciar sesión'
            ]);
        }
        
        $usuarioId = $_SESSION['user_id'];
        
        // Obtener datos del DELETE
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        $reservacionId = isset($data['id']) ? intval($data['id']) : 0;
        
        if($reservacionId <= 0) {
            responderJSON(['success' => false, 'message' => 'ID de reservación inválido']);
        }
        
        try {
            // Verificar que la reservación pertenezca al usuario
            $sqlVerificar = "SELECT id FROM reservaciones 
                            WHERE id = $reservacionId 
                            AND usuario_id = $usuarioId";
            
            $resultado = $conexion->query($sqlVerificar);
            
            if(!$resultado || $resultado->num_rows === 0) {
                responderJSON([
                    'success' => false, 
                    'message' => 'Reservación no encontrada o no tienes permiso para cancelarla'
                ]);
            }
            
            // Eliminar la reservación
            $sqlEliminar = "DELETE FROM reservaciones 
                           WHERE id = $reservacionId 
                           AND usuario_id = $usuarioId";
            
            if($conexion->query($sqlEliminar)) {
                responderJSON([
                    'success' => true,
                    'message' => 'Reservación cancelada exitosamente'
                ]);
            } else {
                responderJSON([
                    'success' => false,
                    'message' => 'Error al cancelar la reservación'
                ]);
            }
            
        } catch(Exception $e) {
            responderJSON([
                'success' => false,
                'message' => 'Error al procesar la cancelación'
            ]);
        }
    }
    
    responderJSON([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    
} catch(Exception $e) {
    responderJSON([
        'success' => false,
        'message' => 'Error general del servidor'
    ]);
}
?>