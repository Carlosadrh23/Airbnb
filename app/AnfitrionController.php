<?php
// Misma configuración de sesión que AuthController
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');

$servidor = "sql105.infinityfree.com";
$usuarioDb = "if0_40439028";
$passwordDb = "CjNuGYpSi9Ho";
$baseDatos = "if0_40439028_airbnb";

// Verificar que el usuario esté logueado
if(!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para registrar una propiedad'
    ]);
    exit;
}

$usuarioId = $_SESSION['user_id'];

try {
    // Conexión a la base de datos
    $conexion = new mysqli($servidor, $usuarioDb, $passwordDb, $baseDatos);
    
    if ($conexion->connect_error) {
        throw new Exception("Error de conexión: " . $conexion->connect_error);
    }
    
    $conexion->set_charset("utf8mb4");
    
    // Obtener y limpiar datos del formulario
    $tipoAlojamiento = isset($_POST['tipoAlojamiento']) ? $conexion->real_escape_string($_POST['tipoAlojamiento']) : '';
    $region = isset($_POST['region']) ? $conexion->real_escape_string($_POST['region']) : '';
    $direccion = isset($_POST['direccion']) ? $conexion->real_escape_string($_POST['direccion']) : '';
    $departamento = isset($_POST['departamento']) ? $conexion->real_escape_string($_POST['departamento']) : '';
    $zona = isset($_POST['zona']) ? $conexion->real_escape_string($_POST['zona']) : '';
    $codigoPostal = isset($_POST['codigoPostal']) ? $conexion->real_escape_string($_POST['codigoPostal']) : '';
    $ciudad = isset($_POST['ciudad']) ? $conexion->real_escape_string($_POST['ciudad']) : '';
    $estado = isset($_POST['estado']) ? $conexion->real_escape_string($_POST['estado']) : '';
    $precioNoche = isset($_POST['precioNoche']) ? floatval($_POST['precioNoche']) : 0;
    
    // Validar datos obligatorios
    if(empty($tipoAlojamiento)) {
        throw new Exception("El tipo de alojamiento es obligatorio");
    }
    
    if(empty($direccion)) {
        throw new Exception("La dirección es obligatoria");
    }
    
    if(empty($codigoPostal)) {
        throw new Exception("El código postal es obligatorio");
    }
    
    if(empty($ciudad)) {
        throw new Exception("La ciudad es obligatoria");
    }
    
    if($precioNoche <= 0) {
        throw new Exception("El precio por noche debe ser mayor a 0");
    }
    
    // Validar tipo de alojamiento
    $tiposValidos = ['casa', 'departamento', 'condominio'];
    if(!in_array(strtolower($tipoAlojamiento), $tiposValidos)) {
        $tipoAlojamiento = 'casa';
    }
    
    // Procesar imagen si existe
    $nombreImagen = null;
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $directorioImagenes = "../assets/img/propiedades/";
        
        if(!file_exists($directorioImagenes)) {
            mkdir($directorioImagenes, 0777, true);
        }
        
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $tipoArchivo = $_FILES['imagen']['type'];
        
        if(!in_array($tipoArchivo, $tiposPermitidos)) {
            throw new Exception("Tipo de archivo no permitido. Solo JPG, PNG y WEBP");
        }
        
        if($_FILES['imagen']['size'] > 5242880) {
            throw new Exception("La imagen es muy grande. Máximo 5MB");
        }
        
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombreImagen = uniqid() . '_' . time() . '.' . $extension;
        $rutaCompleta = $directorioImagenes . $nombreImagen;
        
        if(!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
            throw new Exception("Error al subir la imagen");
        }
    }
    
    // Iniciar transacción
    $conexion->begin_transaction();
    
    // Insertar la propiedad
    $sql = "INSERT INTO propiedades (
        anfitrion_id, tipo_alojamiento, region, direccion, 
        departamento_habitacion, zona, codigo_postal, ciudad, estado,
        precio_noche, imagen_url, estado_publicacion
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')";
    
    $stmt = $conexion->prepare($sql);
    
    if(!$stmt) {
        throw new Exception("Error al preparar consulta: " . $conexion->error);
    }
    
    $stmt->bind_param(
        "issssssssds",
        $usuarioId,
        $tipoAlojamiento,
        $region,
        $direccion,
        $departamento,
        $zona,
        $codigoPostal,
        $ciudad,
        $estado,
        $precioNoche,
        $nombreImagen
    );
    
    if(!$stmt->execute()) {
        throw new Exception("Error al insertar propiedad: " . $stmt->error);
    }
    
    $propiedadId = $conexion->insert_id;
    
    // Actualizar usuario como anfitrión
    $sqlUpdate = "UPDATE usuarios SET es_anfitrion = 1 WHERE id = ?";
    $stmtUpdate = $conexion->prepare($sqlUpdate);
    $stmtUpdate->bind_param("i", $usuarioId);
    
    if(!$stmtUpdate->execute()) {
        throw new Exception("Error al actualizar usuario: " . $stmtUpdate->error);
    }
    
    // Confirmar transacción
    $conexion->commit();
    
    // Actualizar la sesión
    $_SESSION['es_anfitrion'] = true;
    
    echo json_encode([
        'success' => true,
        'message' => '¡Felicidades! Tu propiedad ha sido registrada exitosamente.',
        'propiedadId' => $propiedadId,
        'datos' => [
            'tipo' => $tipoAlojamiento,
            'direccion' => $direccion,
            'ciudad' => $ciudad,
            'estado' => $estado,
            'precio' => $precioNoche
        ]
    ]);
    
    $stmt->close();
    if(isset($stmtUpdate)) $stmtUpdate->close();
    $conexion->close();
    
} catch(Exception $e) {
    if(isset($conexion) && $conexion->ping()) {
        $conexion->rollback();
    }
    
    if(isset($nombreImagen) && isset($rutaCompleta) && file_exists($rutaCompleta)) {
        unlink($rutaCompleta);
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>