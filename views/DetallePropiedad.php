<<?php
session_start();

$propiedadId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($propiedadId <= 0) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeAway - Detalle de Propiedad</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body class="pagina-principal">
  
   <header class="header">
    <a href="index.php">
        <img src="../assets/img/Logo_azul.png" alt="Logo" class="logo">
    </a>
    
    <div class="menu-container">
        <div class="icono-usuario" id="iconoUsuario" style="display: none;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        
        <div class="menu-hamburguesa">
            <button class="boton-menu">
                <span class="linea"></span>
                <span class="linea"></span>
                <span class="linea"></span>
            </button>
            
            <div class="dropdown-menu" id="menuSinSesion">
                <a href="Login.html" class="menu-item">Iniciar sesión / Registrarse</a>
                <a href="Anfitrion1.html" class="menu-item">Conviértete en anfitrión</a>
            </div>
            
            <div class="dropdown-menu" id="menuConSesion" style="display: none;">
                <div class="user-info-menu">
                    <strong id="nombreUsuarioMenu">Usuario</strong>
                    <small id="emailUsuarioMenu">email@ejemplo.com</small>
                </div>
                <hr style="margin: 8px 0; border: none; border-top: 1px solid #EBEBEB;">
                <a href="Perfil.html" class="menu-item">Mi perfil</a>
                <a href="Anfitrion1.html" class="menu-item">Conviértete en anfitrión</a>
                <a href="CerrarSesion.php" class="menu-item">Cerrar sesión</a>
            </div>
        </div>
    </div>
</header>

    <div class="contenedor-detalle" id="contenedorDetalle">
        <div class="loading">Cargando...</div>
    </div>

    <!-- Botón de Reservar -->
    <button class="boton-reservar-fijo" id="btnReservar" onclick="abrirModalReserva()" style="display: none;">
        Reservar
    </button>

    <!-- Modal de Reservación -->
    <div class="modal-reserva" id="modalReserva" onclick="cerrarModalSiFueraClick(event)">
        <div class="modal-contenido">
            <button class="boton-cerrar" onclick="cerrarModalReserva()">×</button>
            
            <div class="modal-header">
                <div class="precio-modal" id="precioModal">$0 MXN</div>
                <div class="noches-modal" id="nochesModal">por noche</div>
            </div>

            <div class="fila-fechas">
                <div class="campo-fecha">
                    <label class="label-fecha">Llegada</label>
                    <input type="date" class="input-fecha" id="fechaLlegada" required>
                </div>
                <div class="campo-fecha">
                    <label class="label-fecha">Salida</label>
                    <input type="date" class="input-fecha" id="fechaSalida" required>
                </div>
            </div>

            <div class="campo-huespedes">
                <label class="label-huespedes">Huéspedes</label>
                <div class="contador-huespedes">
                    <button class="boton-contador" onclick="cambiarHuespedes(-1)" id="btnMenos" disabled>-</button>
                    <span class="numero-huespedes" id="numHuespedes">0</span>
                    <button class="boton-contador" onclick="cambiarHuespedes(1)">+</button>
                </div>
            </div>

            <button class="boton-verificar" onclick="confirmarReservacion()" id="btnConfirmar">
                Continuar
            </button>
        </div>
    </div>

    <script src="../assets/javascript/main.js"></script>
    <script>
        const propiedadId = <?php echo $propiedadId; ?>;
        let propiedadActual = null;
        let numHuespedes = 0;
        let usuarioLogueado = false;
        
        // Verificar si hay sesión activa
        async function verificarSesion() {
            try {
                const response = await fetch('../app/AuthController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ accion: 'verificar_sesion' })
                });
                
                const resultado = await response.json();
                usuarioLogueado = resultado.logueado || false;
                return usuarioLogueado;
            } catch (error) {
                console.error('Error verificando sesión:', error);
                return false;
            }
        }
        
        // Cargar detalle de la propiedad
        async function cargarDetallePropiedad() {
            try {
                const response = await fetch('../app/AnfitrionController.php?id=' + propiedadId, {
                    method: 'GET',
                    credentials: 'include'
                });
                
                const resultado = await response.json();
                
                if (resultado.success && resultado.propiedad) {
                    propiedadActual = resultado.propiedad;
                    mostrarDetalle(resultado.propiedad);
                    document.getElementById('btnReservar').style.display = 'block';
                } else {
                    document.getElementById('contenedorDetalle').innerHTML = '<div class="error">Propiedad no encontrada</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('contenedorDetalle').innerHTML = '<div class="error">Error al cargar la propiedad</div>';
            }
        }
        
        function mostrarDetalle(propiedad) {
            const imagenUrl = propiedad.imagen_url 
                 '../assets/img/propiedades/' + propiedad.imagen_url 
                 '../assets/img/placeholder.png';
            
            const precioFormateado = parseFloat(propiedad.precio_noche).toLocaleString('es-MX');
            const numeroNoches = propiedad.numero_noches || 1;
            const textoNoches = numeroNoches === 1 ? 'noche' : 'noches';
            
            // Actualizar modal
            document.getElementById('precioModal').textContent = '$' + precioFormateado + ' MXN';
            document.getElementById('nochesModal').textContent = 'por ' + numeroNoches + ' ' + textoNoches + ' mínimo';
            
            const html = `
                <h1>${propiedad.tipo_alojamiento} en ${propiedad.ciudad}</h1>
                
                <div class="propiedades">
                    <div class="condominio">
                        <div class="contenedor-img">
                            <img src="${imagenUrl}" alt="${propiedad.tipo_alojamiento} en ${propiedad.ciudad}" class="img-condominio">
                        </div>
                        <div class="info-condominio">
                            <h3 class="titulo-condominio">${propiedad.tipo_alojamiento} en ${propiedad.ciudad}</h3>
                            <p class="descripcion-detalle">${propiedad.direccion}</p>
                            <p class="descripcion-detalle">${propiedad.ciudad}, ${propiedad.estado}</p>
                            
                            <div class="descripcion-propiedad">
                                <h4>Descripción:</h4>
                                <p>${propiedad.descripcion}</p>
                            </div>
                            
                            <p class="precio">$${precioFormateado} MXN <span class="noches">por ${numeroNoches} ${textoNoches}</span></p>
                            <div class="rating">
                                <span class="estrella">★</span>
                                <span class="valor-rating">5.0</span>
                            </div>
                            <p class="anfitrion-info">Anfitrión: ${propiedad.nombre_anfitrion}</p>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('contenedorDetalle').innerHTML = html;
            
            // Configurar fecha mínima (hoy)
            const hoy = new Date().toISOString().split('T')[0];
            document.getElementById('fechaLlegada').min = hoy;
            document.getElementById('fechaSalida').min = hoy;
        }
        
        function abrirModalReserva() {
            document.getElementById('modalReserva').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalReserva() {
            document.getElementById('modalReserva').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function cerrarModalSiFueraClick(event) {
            if (event.target.id === 'modalReserva') {
                cerrarModalReserva();
            }
        }

        function cambiarHuespedes(cambio) {
            numHuespedes += cambio;
            if (numHuespedes < 0) numHuespedes = 0;
            
            document.getElementById('numHuespedes').textContent = numHuespedes;
            document.getElementById('btnMenos').disabled = numHuespedes === 0;
        }

        //  Redirige a página de pago en lugar de reservar directamente
        async function confirmarReservacion() {
            const fechaLlegada = document.getElementById('fechaLlegada').value;
            const fechaSalida = document.getElementById('fechaSalida').value;
            
            if (!fechaLlegada || !fechaSalida) {
                alert('Por favor selecciona las fechas de llegada y salida');
                return;
            }

            if (numHuespedes === 0) {
                alert('Por favor indica el número de huéspedes');
                return;
            }
            
            // Verificar si el usuario está logueado
            const logueado = await verificarSesion();
            
            if (!logueado) {
                if (confirm('Debes iniciar sesión para hacer una reservación.\n\n¿Deseas iniciar sesión ahora?')) {
                    // Guardar datos para después del login
                    sessionStorage.setItem('reserva_pendiente', JSON.stringify({
                        propiedad_id: propiedadId,
                        fecha_inicio: fechaLlegada,
                        fecha_fin: fechaSalida,
                        num_huespedes: numHuespedes
                    }));
                    window.location.href = 'Login.html';
                }
                return;
            }
            
            // C Redirige a la página de confirmación y pago
            window.location.href = `ConfirmarYPagar.php?propiedad_id=${propiedadId}&fecha_inicio=${fechaLlegada}&fecha_fin=${fechaSalida}&num_huespedes=${numHuespedes}`;
        }

        // Actualizar fecha mínima de salida cuando cambia la llegada
        document.addEventListener('DOMContentLoaded', async function() {
            await verificarSesion();
            await cargarDetallePropiedad();
            
            document.getElementById('fechaLlegada').addEventListener('change', function() {
                const fechaLlegada = this.value;
                if (fechaLlegada) {
                    const fecha = new Date(fechaLlegada);
                    fecha.setDate(fecha.getDate() + 1);
                    document.getElementById('fechaSalida').min = fecha.toISOString().split('T')[0];
                }
            });

            // Cerrar modal con ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && document.getElementById('modalReserva').classList.contains('active')) {
                    cerrarModalReserva();
                }
            });
            
            //  Verificar si hay reserva pendiente (después de login)
            const reservaPendiente = sessionStorage.getItem('reserva_pendiente');
            if (reservaPendiente) {
                const datos = JSON.parse(reservaPendiente);
                sessionStorage.removeItem('reserva_pendiente');
                
                // Redirigir a la página de pago
                window.location.href = `ConfirmarYPagar.php?propiedad_id=${datos.propiedad_id}&fecha_inicio=${datos.fecha_inicio}&fecha_fin=${datos.fecha_fin}&num_huespedes=${datos.num_huespedes}`;
            }
        });
    </script>
</body>
</html>