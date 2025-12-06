<?php
session_start();

// Verificar sesión
if(!isset($_SESSION['user_id'])) {
    header('Location: Login.html');
    exit;
}

// Obtener datos de la URL
$propiedadId = isset($_GET['propiedad_id']) ? intval($_GET['propiedad_id']) : 0;
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$numHuespedes = isset($_GET['num_huespedes']) ? intval($_GET['num_huespedes']) : 1;

if ($propiedadId <= 0 || empty($fechaInicio) || empty($fechaFin)) {
    header('Location: index.php');
    exit;
}

// Obtener info de la propiedad
$servidor = "sql105.infinityfree.com";
$usuarioDb = "if0_40439028";
$passwordDb = "CjNuGYpSi9Ho";
$baseDatos = "if0_40439028_airbnb";

$conexion = new mysqli($servidor, $usuarioDb, $passwordDb, $baseDatos);
$conexion->set_charset("utf8mb4");

$sql = "SELECT p.*, u.nombre as nombre_anfitrion 
        FROM propiedades p 
        INNER JOIN usuarios u ON p.anfitrion_id = u.id 
        WHERE p.id = $propiedadId";

$resultado = $conexion->query($sql);

if (!$resultado || $resultado->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$propiedad = $resultado->fetch_assoc();

// Calcular noches y precio
$inicio = new DateTime($fechaInicio);
$fin = new DateTime($fechaFin);
$numeroNoches = $inicio->diff($fin)->days;
$precioTotal = $propiedad['precio_noche'] * $numeroNoches;

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar y pagar - HomeAway</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <header class="header">
        <a href="index.php">
            <img src="../assets/img/Logo_azul.png" alt="Logo" class="logo">
        </a>
    </header>

    <div class="contenedor-pago">
        <div class="seccion-izquierda">
            <button class="back-button" onclick="history.back()">←</button>
            
            <h1 class="titulo-seccion">Confirmar y pagar</h1>
            
            <h2 style="font-size: 22px; margin-bottom: 15px;">Tu viaje</h2>
            <div class="card-info">
                <div class="fila-info">
                    <strong>Fechas:</strong>
                    <span><?php echo date('d M', strtotime($fechaInicio)); ?> - <?php echo date('d M Y', strtotime($fechaFin)); ?></span>
                </div>
                <div class="fila-info">
                    <strong>Huéspedes:</strong>
                    <span><?php echo $numHuespedes; ?></span>
                </div>
                <div class="fila-info">
                    <strong>Noches:</strong>
                    <span><?php echo $numeroNoches; ?></span>
                </div>
            </div>
            
            <h2 style="font-size: 22px; margin: 30px 0 15px;">Agrega una forma de pago</h2>
            <div id="metodosPago">
                <label class="metodo-pago-option">
                    <input type="radio" name="metodo_pago" value="tarjeta" checked>
                    <span>💳 Tarjeta de crédito o débito</span>
                </label>
                
                <label class="metodo-pago-option">
                    <input type="radio" name="metodo_pago" value="paypal">
                    <span> PayPal</span>
                </label>
                
                <label class="metodo-pago-option">
                    <input type="radio" name="metodo_pago" value="oxxo">
                    <span> OXXO Pay</span>
                </label>
                
                <label class="metodo-pago-option">
                    <input type="radio" name="metodo_pago" value="transferencia">
                    <span>Transferencia bancaria</span>
                </label>
            </div>
            
            <h2 style="font-size: 22px; margin: 30px 0 15px;">Revisa tu reservación</h2>
            <div class="info-propiedad">
                <?php 
                $imagenUrl = $propiedad['imagen_url'] 
                    ? '../assets/img/propiedades/' . $propiedad['imagen_url'] 
                    : '../assets/img/placeholder.png';
                ?>
                <img src="<?php echo $imagenUrl; ?>" alt="Propiedad" class="imagen-mini">
                <div>
                    <h3 style="font-size: 18px; margin-bottom: 5px;"><?php echo ucfirst($propiedad['tipo_alojamiento']); ?> en <?php echo $propiedad['ciudad']; ?></h3>
                    <p style="color: #717171; font-size: 14px;"><?php echo $propiedad['direccion']; ?></p>
                    <p style="color: #717171; font-size: 14px;">Anfitrión: <?php echo $propiedad['nombre_anfitrion']; ?></p>
                </div>
            </div>
            
            <div class="politica">
                <strong>✓ Cancelación gratuita</strong>
                <p style="margin-top: 8px;">Si cancelas antes del <?php echo date('d M Y', strtotime($fechaInicio . ' -1 day')); ?>, recibirás reembolso completo.</p>
            </div>
        </div>
        
        <div class="seccion-derecha">
            <div class="precio-total">
                Paga $<?php echo number_format($precioTotal, 0); ?> MXN
            </div>
            
            <button class="boton-pagar" id="btnPagar" onclick="procesarPago()">Listo</button>
            
            <div class="desglose">
                <h3 style="font-size: 16px; margin-bottom: 12px;">Desglose de precios</h3>
                <div class="fila-info">
                    <span>$<?php echo number_format($propiedad['precio_noche'], 0); ?> x <?php echo $numeroNoches; ?> noches</span>
                    <span>$<?php echo number_format($precioTotal, 0); ?></span>
                </div>
                <div class="fila-info" style="padding-top: 15px; border-top: 1px solid #EBEBEB; margin-top: 15px;">
                    <strong>Total</strong>
                    <strong>$<?php echo number_format($precioTotal, 0); ?> MXN</strong>
                </div>
            </div>
        </div>
    </div>

    <script>
        const datosReservacion = {
            propiedad_id: <?php echo $propiedadId; ?>,
            fecha_inicio: '<?php echo $fechaInicio; ?>',
            fecha_fin: '<?php echo $fechaFin; ?>',
            num_huespedes: <?php echo $numHuespedes; ?>
        };
        
        // Selección visual de método de pago
        document.querySelectorAll('.metodo-pago-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.metodo-pago-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input').checked = true;
            });
        });
        
        async function procesarPago() {
            const btnPagar = document.getElementById('btnPagar');
            btnPagar.disabled = true;
            btnPagar.textContent = 'Procesando...';
            
            try {
                const response = await fetch('../app/ReservacionController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify(datosReservacion)
                });
                
                const resultado = await response.json();
                
                if (resultado.success) {
                    alert('¡Reservación confirmada exitosamente!\n\nNúmero de reservación: #' + resultado.reservacion.id);
                    window.location.href = 'Perfil.html';
                } else {
                    alert('Error: ' + resultado.message);
                    btnPagar.disabled = false;
                    btnPagar.textContent = 'Listo';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar el pago');
                btnPagar.disabled = false;
                btnPagar.textContent = 'Listo';
            }
        }
    </script>
</body>
</html>