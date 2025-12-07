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

// Formatear fecha en español
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish');
$fechaFormateada = strftime('%A, %d de %B de %Y', strtotime($fechaInicio));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar y pagar - HomeAway</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/styles2.css">
</head>
<body>
    <header class="header">
        <a href="index.php">
            <img src="../assets/img/Logo_azul.png" alt="Logo" class="logo">
        </a>
    </header>

    <div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        <button class="back-button" onclick="history.back()">←</button>
        <h1 class="titulo-pagina">Confirmar y pagar</h1>
    </div>

    <div class="contenedor-pago">
        <!-- COLUMNA IZQUIERDA -->
        <div class="columna-izquierda">
            <!-- PASO 1: Precio inicial -->
            <div class="seccion-pago" id="paso1">
                <div class="precio-grande">Paga $<?php echo number_format($precioTotal, 0); ?> MXN</div>
                <button class="boton-listo" onclick="mostrarMetodoPago()">Listo</button>
            </div>
            
            <!-- PASO 2: Método de pago -->
            <div class="seccion-pago" id="paso2" style="display: none;">
                <h2 style="font-size: 20px; margin-bottom: 16px;">Paga</h2>
                <h3 style="font-size: 18px; margin-bottom: 16px;">Agrega una forma de pago</h3>
                
                <div class="metodo-pago-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" fill="none" stroke-width="2"/>
                        <line x1="2" y1="10" x2="22" y2="10" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Tarjeta de crédito o débito</span>
                </div>
                
                <div class="form-pago">
                    <input type="text" class="input-pago" placeholder="Número de tarjeta" id="numTarjeta" maxlength="16">
                    <div class="fila-input">
                        <input type="text" class="input-pago" placeholder="Caducidad (MM/YY)" id="caducidad" maxlength="5">
                        <input type="text" class="input-pago" placeholder="CVV" id="cvv" maxlength="3">
                    </div>
                    <input type="text" class="input-pago" placeholder="Código postal" id="codigoPostal" maxlength="5">
                </div>
                
                <button class="boton-listo" onclick="procesarPago()" id="btnPagar">Listo</button>
            </div>
            
            <!-- PASO 3: Confirmación -->
            <div class="seccion-pago" id="paso3" style="display: none;">
                <h2 style="font-size: 20px; margin-bottom: 0;">Tu reservación se ha completado</h2>
                
                <div class="seccion-confirmacion">
                    <div class="icono-check">✓</div>
                    <div class="titulo-confirmacion">Gracias por reservar</div>
                    <div class="detalle-confirmacion" id="detallesReserva">
                        <div style="margin-bottom: 8px;">Número de la reserva <strong id="numReserva"></strong></div>
                        <div style="margin-bottom: 8px;">Reserva confirmada el <strong id="fechaConfirmacion"></strong></div>
                        <div style="margin-bottom: 8px;">Nombre <strong><?php echo isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Usuario'; ?></strong></div>
                        <div>Correo <strong><?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?></strong></div>
                    </div>
                    <button class="boton-volver" onclick="window.location.href='index.php'">Volver al inicio</button>
                </div>
            </div>
        </div>
        
        <!-- COLUMNA DERECHA -->
        <div class="columna-derecha">
            <div class="card-resumen">
                <?php 
                $imagenUrl = $propiedad['imagen_url'] 
                    ? '../assets/img/propiedades/' . $propiedad['imagen_url'] 
                    : '../assets/img/placeholder.png';
                ?>
                <img src="<?php echo $imagenUrl; ?>" alt="Propiedad" class="imagen-propiedad-mini">
                <h3 style="margin-bottom: 8px;"><?php echo ucfirst($propiedad['tipo_alojamiento']); ?> en <?php echo $propiedad['ciudad']; ?></h3>
                
                <div style="margin-top: 16px;">
                    <p style="font-size: 14px; color: #222; margin-bottom: 8px;"><strong>Cancelación gratuita</strong></p>
                    <p style="font-size: 13px; color: #717171;">Si cancelas antes del <?php echo date('d M Y', strtotime($fechaInicio . ' -1 day')); ?>, recibirás reembolso completo.</p>
                </div>
                
                <div style="margin-top: 16px;">
                    <div class="info-item">
                        <span>Fechas</span>
                        <span><?php echo date('d-m', strtotime($fechaInicio)); ?> - <?php echo date('d-m', strtotime($fechaFin)); ?> de 2025</span>
                    </div>
                    <div class="info-item">
                        <span>Huéspedes</span>
                        <span><?php echo $numHuespedes; ?></span>
                    </div>
                    <div class="info-item">
                        <span>Total</span>
                        <span>$<?php echo number_format($precioTotal, 0); ?> MXN</span>
                    </div>
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
        
        function mostrarMetodoPago() {
            document.getElementById('paso1').style.display = 'none';
            document.getElementById('paso2').style.display = 'block';
        }
        
        async function procesarPago() {
            // Validar campos
            const numTarjeta = document.getElementById('numTarjeta').value;
            const caducidad = document.getElementById('caducidad').value;
            const cvv = document.getElementById('cvv').value;
            const codigoPostal = document.getElementById('codigoPostal').value;
            
            if (!numTarjeta || !caducidad || !cvv || !codigoPostal) {
                alert('Por favor completa todos los campos de pago');
                return;
            }
            
            // Validar número de tarjeta (16 dígitos)
            if (numTarjeta.length < 13) {
                alert('El número de tarjeta debe tener al menos 13 dígitos');
                return;
            }
            
            // Validar CVV (3 dígitos)
            if (cvv.length < 3) {
                alert('El CVV debe tener 3 dígitos');
                return;
            }
            
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
                    // Ocultar paso 2
                    document.getElementById('paso2').style.display = 'none';
                    
                    // Mostrar confirmación con animación
                    document.getElementById('paso3').style.display = 'block';
                    document.getElementById('numReserva').textContent = '#' + resultado.reservacion.id;
                    
                    
                    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    const fechaHoy = new Date().toLocaleDateString('es-ES', opciones);
                    document.getElementById('fechaConfirmacion').textContent = fechaHoy;
                    
                    // Scroll  para la confirmación
                    document.getElementById('paso3').scrollIntoView({ behavior: 'smooth', block: 'start' });
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