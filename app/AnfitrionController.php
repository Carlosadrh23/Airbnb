<?php
// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

// IMPORTANTE: Para producción, cambiar a 0
ini_set('display_errors', 0);
error_reporting(0);

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');

$servidor = "sql105.infinityfree.com";
$usuarioDb = "if0_40439028";
$passwordDb = "CjNuGYpSi9Ho";
$baseDatos = "if0_40439028_airbnb";

// Función para responder con JSON limpio
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
    // OBTENER PROPIEDADES (GET)
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            $sql = "SELECT 
                        p.id,
                        p.tipo_alojamiento,
                        p.ciudad,
                        p.estado,
                        p.direccion,
                        p.precio_noche,
                        p.imagen_url,
                        p.fecha_registro,
                        u.nombre as nombre_anfitrion
                    FROM propiedades p
                    INNER JOIN usuarios u ON p.anfitrion_id = u.id
                    WHERE p.estado_publicacion = 'activo'
                    ORDER BY p.fecha_registro DESC";
            
            $resultado = $conexion->query($sql);
            
            if (!$resultado) {
                responderJSON([
                    'success' => false,
                    'message' => 'Error al consultar propiedades',
                    'propiedades' => []
                ]);
            }
            
            $propiedades = [];
            while ($fila = $resultado->fetch_assoc()) {
                $propiedades[] = [
                    'id' => $fila['id'],
                    'tipo_alojamiento' => ucfirst($fila['tipo_alojamiento']),
                    'ciudad' => $fila['ciudad'],
                    'estado' => $fila['estado'],
                    'direccion' => $fila['direccion'],
                    'precio_noche' => $fila['precio_noche'],
                    'imagen_url' => $fila['imagen_url'],
                    'fecha_registro' => $fila['fecha_registro'],
                    'nombre_anfitrion' => $fila['nombre_anfitrion']
                ];
            }
            
            responderJSON([
                'success' => true,
                'propiedades' => $propiedades,
                'total' => count($propiedades)
            ]);
            
        } catch(Exception $e) {
            responderJSON([
                'success' => false,
                'message' => 'Error al obtener propiedades',
                'propiedades' => []
            ]);
        }
    }
    
    // ============================================
    // REGISTRAR PROPIEDAD (POST)
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Verificar sesión
        if(!isset($_SESSION['user_id'])) {
            responderJSON([
                'success' => false,
                'message' => 'Debes iniciar sesión para registrar una propiedad'
            ]);
        }
        
        $usuarioId = $_SESSION['user_id'];
        
        try {
            // Obtener datos
            $tipoAlojamiento = isset($_POST['tipoAlojamiento']) ? trim($_POST['tipoAlojamiento']) : '';
            $region = isset($_POST['region']) ? trim($_POST['region']) : '';
            $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
            $departamento = isset($_POST['departamento']) ? trim($_POST['departamento']) : '';
            $zona = isset($_POST['zona']) ? trim($_POST['zona']) : '';
            $codigoPostal = isset($_POST['codigoPostal']) ? trim($_POST['codigoPostal']) : '';
            $ciudad = isset($_POST['ciudad']) ? trim($_POST['ciudad']) : '';
            $estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';
            $precioNoche = isset($_POST['precioNoche']) ? floatval($_POST['precioNoche']) : 0;
            
            // Validaciones básicas
            if(empty($tipoAlojamiento)) {
                responderJSON(['success' => false, 'message' => 'El tipo de alojamiento es obligatorio']);
            }
            
            if(empty($direccion)) {
                responderJSON(['success' => false, 'message' => 'La dirección es obligatoria']);
            }
            
            if(empty($ciudad)) {
                responderJSON(['success' => false, 'message' => 'La ciudad es obligatoria']);
            }
            
            if($precioNoche <= 0) {
                responderJSON(['success' => false, 'message' => 'El precio debe ser mayor a 0']);
            }
            
            // Validar tipo
            $tiposValidos = ['casa', 'departamento', 'condominio'];
            $tipoAlojamiento = strtolower($tipoAlojamiento);
            if(!in_array($tipoAlojamiento, $tiposValidos)) {
                $tipoAlojamiento = 'casa';
            }
            
            // Procesar imagen
            $nombreImagen = null;
            $rutaCompleta = null;
            
            if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                $directorioImagenes = "../assets/img/propiedades/";
                
                if(!file_exists($directorioImagenes)) {
                    if(!mkdir($directorioImagenes, 0777, true)) {
                        responderJSON(['success' => false, 'message' => 'No se pudo crear el directorio de imágenes']);
                    }
                }
                
                $tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                $tipoArchivo = $_FILES['imagen']['type'];
                
                if(!in_array($tipoArchivo, $tiposPermitidos)) {
                    responderJSON(['success' => false, 'message' => 'Tipo de archivo no permitido. Solo JPG, PNG y WEBP']);
                }
                
                if($_FILES['imagen']['size'] > 5242880) {
                    responderJSON(['success' => false, 'message' => 'La imagen es muy grande. Máximo 5MB']);
                }
                
                $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $nombreImagen = uniqid() . '_' . time() . '.' . $extension;
                $rutaCompleta = $directorioImagenes . $nombreImagen;
                
                if(!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
                    responderJSON(['success' => false, 'message' => 'Error al guardar la imagen']);
                }
            }
            
            // Escapar datos para SQL
            $tipoAlojamiento = $conexion->real_escape_string($tipoAlojamiento);
            $region = $conexion->real_escape_string($region);
            $direccion = $conexion->real_escape_string($direccion);
            $departamento = $conexion->real_escape_string($departamento);
            $zona = $conexion->real_escape_string($zona);
            $codigoPostal = $conexion->real_escape_string($codigoPostal);
            $ciudad = $conexion->real_escape_string($ciudad);
            $estado = $conexion->real_escape_string($estado);
            $nombreImagen = $nombreImagen ? $conexion->real_escape_string($nombreImagen) : null;
            
            // Iniciar transacción
            $conexion->begin_transaction();
            
            // Insertar propiedad
            $sql = "INSERT INTO propiedades (
                anfitrion_id, tipo_alojamiento, region, direccion, 
                departamento_habitacion, zona, codigo_postal, ciudad, estado,
                precio_noche, imagen_url, estado_publicacion
            ) VALUES (
                $usuarioId, 
                '$tipoAlojamiento', 
                '$region', 
                '$direccion', 
                '$departamento', 
                '$zona', 
                '$codigoPostal', 
                '$ciudad', 
                '$estado', 
                $precioNoche, 
                " . ($nombreImagen ? "'$nombreImagen'" : "NULL") . ", 
                'activo'
            )";
            
            if(!$conexion->query($sql)) {
                $conexion->rollback();
                
                // Eliminar imagen si se subió
                if($rutaCompleta && file_exists($rutaCompleta)) {
                    unlink($rutaCompleta);
                }
                
                responderJSON([
                    'success' => false,
                    'message' => 'Error al guardar la propiedad en la base de datos'
                ]);
            }
            
            $propiedadId = $conexion->insert_id;
            
            // Actualizar usuario como anfitrión
            $sqlUpdate = "UPDATE usuarios SET es_anfitrion = 1 WHERE id = $usuarioId";
            $conexion->query($sqlUpdate);
            
            // Confirmar transacción
            $conexion->commit();
            
            // Actualizar sesión
            $_SESSION['es_anfitrion'] = true;
            
            responderJSON([
                'success' => true,
                'message' => '¡Felicidades! Tu propiedad ha sido registrada exitosamente.',
                'propiedadId' => $propiedadId,
                'datos' => [
                    'tipo' => ucfirst($tipoAlojamiento),
                    'direccion' => $direccion,
                    'ciudad' => $ciudad,
                    'estado' => $estado,
                    'precio' => $precioNoche
                ]
            ]);
            
        } catch(Exception $e) {
            if($conexion->ping()) {
                $conexion->rollback();
            }
            
            // Eliminar imagen si se subió
            if(isset($rutaCompleta) && $rutaCompleta && file_exists($rutaCompleta)) {
                unlink($rutaCompleta);
            }
            
            responderJSON([
                'success' => false,
                'message' => 'Error inesperado al procesar la solicitud'
            ]);
        }
    }
    
    // Si no es GET ni POST
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