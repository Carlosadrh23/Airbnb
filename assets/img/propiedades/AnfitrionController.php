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
        
        // Si hay un ID en la URL, obtener solo esa propiedad
        if (isset($_GET['id'])) {
            $propiedadId = intval($_GET['id']);
            
            try {
                $sql = "SELECT 
                            p.id,
                            p.anfitrion_id,
                            p.tipo_alojamiento,
                            p.ciudad,
                            p.estado,
                            p.direccion,
                            p.region,
                            p.departamento_habitacion,
                            p.zona,
                            p.codigo_postal,
                            p.precio_noche,
                            p.numero_noches,
                            p.descripcion,
                            p.imagen_url,
                            p.fecha_registro,
                            u.nombre as nombre_anfitrion
                        FROM propiedades p
                        INNER JOIN usuarios u ON p.anfitrion_id = u.id
                        WHERE p.id = $propiedadId AND p.estado_publicacion = 'activo'";
                
                $resultado = $conexion->query($sql);
                
                if (!$resultado || $resultado->num_rows === 0) {
                    responderJSON([
                        'success' => false,
                        'message' => 'Propiedad no encontrada'
                    ]);
                }
                
                $propiedad = $resultado->fetch_assoc();
                
                responderJSON([
                    'success' => true,
                    'propiedad' => [
                        'id' => $propiedad['id'],
                        'anfitrion_id' => $propiedad['anfitrion_id'],
                        'tipo_alojamiento' => ucfirst($propiedad['tipo_alojamiento']),
                        'ciudad' => $propiedad['ciudad'],
                        'estado' => $propiedad['estado'],
                        'direccion' => $propiedad['direccion'],
                        'region' => $propiedad['region'],
                        'departamento_habitacion' => $propiedad['departamento_habitacion'],
                        'zona' => $propiedad['zona'],
                        'codigo_postal' => $propiedad['codigo_postal'],
                        'precio_noche' => $propiedad['precio_noche'],
                        'numero_noches' => $propiedad['numero_noches'],
                        'descripcion' => $propiedad['descripcion'],
                        'imagen_url' => $propiedad['imagen_url'],
                        'fecha_registro' => $propiedad['fecha_registro'],
                        'nombre_anfitrion' => $propiedad['nombre_anfitrion']
                    ]
                ]);
                
            } catch(Exception $e) {
                responderJSON([
                    'success' => false,
                    'message' => 'Error al obtener la propiedad'
                ]);
            }
        }
        
        // Si no hay ID, obtener todas las propiedades
        try {
            $sql = "SELECT 
                        p.id,
                        p.anfitrion_id,
                        p.tipo_alojamiento,
                        p.ciudad,
                        p.estado,
                        p.direccion,
                        p.precio_noche,
                        p.numero_noches,
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
                    'anfitrion_id' => $fila['anfitrion_id'],
                    'tipo_alojamiento' => ucfirst($fila['tipo_alojamiento']),
                    'ciudad' => $fila['ciudad'],
                    'estado' => $fila['estado'],
                    'direccion' => $fila['direccion'],
                    'precio_noche' => $fila['precio_noche'],
                    'numero_noches' => $fila['numero_noches'],
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
            $numeroNoches = isset($_POST['numeroNoches']) ? intval($_POST['numeroNoches']) : 1;
            $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
            
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
            
            if($numeroNoches < 1) {
                responderJSON(['success' => false, 'message' => 'El número de noches debe ser al menos 1']);
            }
            
            if(empty($descripcion) || strlen($descripcion) < 20) {
                responderJSON(['success' => false, 'message' => 'La descripción debe tener al menos 20 caracteres']);
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
                
                // ⭐ NUEVO: Dar permisos de lectura a la imagen para que todos puedan verla
                chmod($rutaCompleta, 0644);
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
            $descripcion = $conexion->real_escape_string($descripcion);
            $nombreImagen = $nombreImagen ? $conexion->real_escape_string($nombreImagen) : null;
            
            // Iniciar transacción
            $conexion->begin_transaction();
            
            // Insertar propiedad
            $sql = "INSERT INTO propiedades (
                anfitrion_id, tipo_alojamiento, region, direccion, 
                departamento_habitacion, zona, codigo_postal, ciudad, estado,
                precio_noche, numero_noches, descripcion, imagen_url, estado_publicacion
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
                $numeroNoches,
                '$descripcion',
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
                'message' => 'Felicidades! Tu propiedad ha sido registrada exitosamente.',
                'propiedadId' => $propiedadId,
                'datos' => [
                    'tipo' => ucfirst($tipoAlojamiento),
                    'direccion' => $direccion,
                    'ciudad' => $ciudad,
                    'estado' => $estado,
                    'precio' => $precioNoche,
                    'noches' => $numeroNoches
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