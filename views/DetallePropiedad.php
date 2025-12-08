<?php
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
    <link rel="stylesheet" href="../assets/styles2.css">
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

            <div class="info-reserva" id="infoReserva" style="display: none; padding: 12px; background: #f7f7f7; border-radius: 8px; margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Noches seleccionadas:</span>
                    <strong id="nochesSeleccionadas">0</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Precio por noche:</span>
                    <strong id="precioPorNoche">$0</strong>
                </div>
                <hr style="margin: 8px 0; border: none; border-top: 1px solid #ddd;">
                <div style="display: flex; justify-content: space-between;">
                    <strong>Total:</strong>
                    <strong id="precioTotal" style="color: #FF385C;">$0</strong>
                </div>
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

    <!-- Modal de Amenidades -->
    <div class="modal-overlay" id="modalAmenidades" onclick="cerrarModalSiClickFuera(event)">
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title">Lo que ofrece este lugar</h2>
                <button class="btn-cerrar" onclick="cerrarModalAmenidades()">×</button>
            </div>
            
            <div class="modal-body">
                <div class="amenidades-grid">
                    <!-- Baño -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 6a3 3 0 1 0 6 0"></path>
                            <path d="M12 3v3"></path>
                            <path d="M3 13h18"></path>
                            <path d="M5 13v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6"></path>
                        </svg>
                        <span class="amenidad-texto">Baño</span>
                    </div>

                    <!-- Ventiladores -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M12 2v3"></path>
                            <path d="M12 19v3"></path>
                            <path d="M4.22 4.22l2.12 2.12"></path>
                            <path d="M17.66 17.66l2.12 2.12"></path>
                            <path d="M2 12h3"></path>
                            <path d="M19 12h3"></path>
                            <path d="M4.22 19.78l2.12-2.12"></path>
                            <path d="M17.66 6.34l2.12-2.12"></path>
                        </svg>
                        <span class="amenidad-texto">Ventiladores</span>
                    </div>

                    <!-- Productos de limpieza -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6L6 18"></path>
                            <path d="M6 6l12 12"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span class="amenidad-texto">Productos de limpieza</span>
                    </div>

                    <!-- Cerradura en la puerta -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <span class="amenidad-texto">Cerradura en la puerta</span>
                    </div>

                    <!-- Agua caliente -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                        </svg>
                        <span class="amenidad-texto">Agua caliente</span>
                    </div>

                    <!-- Wifi -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12.55a11 11 0 0 1 14.08 0"></path>
                            <path d="M1.42 9a16 16 0 0 1 21.16 0"></path>
                            <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
                            <line x1="12" y1="20" x2="12.01" y2="20"></line>
                        </svg>
                        <span class="amenidad-texto">Wifi</span>
                    </div>

                    <!-- Lavadora -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                            <circle cx="12" cy="13" r="5"></circle>
                            <circle cx="12" cy="13" r="2"></circle>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                        <span class="amenidad-texto">Lavadora</span>
                    </div>

                    <!-- Cocina -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3h18v4H3z"></path>
                            <path d="M3 7v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7"></path>
                            <circle cx="9" cy="13" r="2"></circle>
                        </svg>
                        <span class="amenidad-texto">Cocina</span>
                    </div>

                    <!-- Plancha -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 3h6l3 8H6l3-8z"></path>
                            <path d="M6 11v7a3 3 0 0 0 3 3h6a3 3 0 0 0 3-3v-7"></path>
                        </svg>
                        <span class="amenidad-texto">Plancha</span>
                    </div>

                    <!-- Comedor -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="8" width="18" height="4" rx="1"></rect>
                            <path d="M4 12v7a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-7"></path>
                            <path d="M16 12v7a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-7"></path>
                        </svg>
                        <span class="amenidad-texto">Comedor</span>
                    </div>

                    <!-- Closet -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                            <line x1="12" y1="3" x2="12" y2="21"></line>
                            <line x1="9" y1="12" x2="9.01" y2="12"></line>
                            <line x1="15" y1="12" x2="15.01" y2="12"></line>
                        </svg>
                        <span class="amenidad-texto">Closet</span>
                    </div>

                    <!-- Refrigerador -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                            <line x1="4" y1="10" x2="20" y2="10"></line>
                            <line x1="10" y1="5" x2="10" y2="7"></line>
                            <line x1="10" y1="13" x2="10" y2="16"></line>
                        </svg>
                        <span class="amenidad-texto">Refrigerador</span>
                    </div>

                    <!-- TV -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect>
                            <polyline points="17 2 12 7 7 2"></polyline>
                        </svg>
                        <span class="amenidad-texto">TV</span>
                    </div>

                    <!-- Muebles exteriores -->
                    <div class="amenidad-item">
                        <svg class="amenidad-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 9v10"></path>
                            <path d="M20 9v10"></path>
                            <path d="M4 9h16"></path>
                            <path d="M6 9V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v3"></path>
                        </svg>
                        <span class="amenidad-texto">Muebles exteriores</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/javascript/main.js"></script>
     <script>
    const propiedadId = <?php echo $propiedadId; ?>;
let propiedadActual = null;
let numHuespedes = 0;
let usuarioLogueado = false;

// ÚNICA FUNCIÓN para verificar sesión Y actualizar interfaz
async function verificarSesionYActualizarMenu() {
    try {
        const response = await fetch('../app/AuthController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ accion: 'verificar_sesion' })
        });
        
        const resultado = await response.json();
        usuarioLogueado = resultado.logueado || false;
        
        // Actualizar interfaz del menú
        const iconoUsuario = document.getElementById('iconoUsuario');
        const menuSinSesion = document.getElementById('menuSinSesion');
        const menuConSesion = document.getElementById('menuConSesion');
        const nombreUsuarioMenu = document.getElementById('nombreUsuarioMenu');
        const emailUsuarioMenu = document.getElementById('emailUsuarioMenu');

        if (resultado.logueado && resultado.usuario) {
            console.log(' Usuario logueado:', resultado.usuario.nombre);
            
            // Mostrar icono de usuario y menú con sesión
            if (iconoUsuario) iconoUsuario.style.display = 'flex';
            if (menuSinSesion) menuSinSesion.style.display = 'none';
            if (menuConSesion) menuConSesion.style.display = 'block';
            
            // Actualizar nombre y email
            if (nombreUsuarioMenu) nombreUsuarioMenu.textContent = resultado.usuario.nombre;
            if (emailUsuarioMenu) emailUsuarioMenu.textContent = resultado.usuario.email;
        } else {
            console.log(' Sin sesión activa');
            
            // Mostrar menú sin sesión
            if (iconoUsuario) iconoUsuario.style.display = 'none';
            if (menuSinSesion) menuSinSesion.style.display = 'block';
            if (menuConSesion) menuConSesion.style.display = 'none';
        }
        
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
        ? '../assets/img/propiedades/' + propiedad.imagen_url 
        : '../assets/img/placeholder.png';
    
    const precioFormateado = parseFloat(propiedad.precio_noche).toLocaleString('es-MX');
    const numeroNoches = propiedad.numero_noches || 1;
    const textoNoches = numeroNoches === 1 ? 'noche' : 'noches';
    
    // Actualizar modal
    document.getElementById('precioModal').textContent = '$' + precioFormateado + ' MXN';
    document.getElementById('nochesModal').textContent = 'por noche (mínimo ' + numeroNoches + ' ' + textoNoches + ')';
    
    const html = `
        <h1>${propiedad.tipo_alojamiento} en ${propiedad.ciudad}</h1>
        
        <div class="propiedades">
            <div class="condominio">
                <div class="contenedor-img">
                    <img src="${imagenUrl}" 
                         alt="${propiedad.tipo_alojamiento} en ${propiedad.ciudad}" 
                         class="img-condominio"
                         onerror="this.src='../assets/img/placeholder.png';">
                </div>
                <div class="info-condominio">
                    <h3 class="titulo-condominio">${propiedad.tipo_alojamiento} en ${propiedad.ciudad}</h3>
                    <p class="descripcion-detalle">${propiedad.direccion}</p>
                    <p class="descripcion-detalle">${propiedad.ciudad}, ${propiedad.estado}</p>
                    
                    <div class="descripcion-propiedad">
                        <h4>Descripción:</h4>
                        <p>${propiedad.descripcion}</p>
                    </div>
                    
                    <button class="btn-ver-detalles" onclick="abrirModalAmenidades()" style="margin: 20px 0; padding: 12px 24px; background: #5B8A8F; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600;">
                        Incluye
                    </button>
                    
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

async function abrirModalReserva() {
    // Verificar si el usuario está logueado
    const logueado = await verificarSesionYActualizarMenu();
    
    if (!logueado) {
        if (confirm('Debes iniciar sesión para hacer una reservación.\n\n¿Deseas iniciar sesión ahora?')) {
            sessionStorage.setItem('regresar_propiedad', propiedadId);
            window.location.href = 'Login.html';
        }
        return;
    }
    
    // Si está logueado, abrir el modal normalmente
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

function calcularTotal() {
    const fechaLlegada = document.getElementById('fechaLlegada').value;
    const fechaSalida = document.getElementById('fechaSalida').value;
    
    if (!fechaLlegada || !fechaSalida || !propiedadActual) {
        document.getElementById('infoReserva').style.display = 'none';
        return;
    }

    const fecha1 = new Date(fechaLlegada);
    const fecha2 = new Date(fechaSalida);
    const diffTime = Math.abs(fecha2 - fecha1);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays > 0) {
        const precioNoche = parseFloat(propiedadActual.precio_noche);
        const total = precioNoche * diffDays;
        const nochesMinimas = propiedadActual.numero_noches || 1;

        document.getElementById('nochesSeleccionadas').textContent = diffDays + (diffDays === 1 ? ' noche' : ' noches');
        document.getElementById('precioPorNoche').textContent = '$' + precioNoche.toLocaleString('es-MX') + ' MXN';
        document.getElementById('precioTotal').textContent = '$' + total.toLocaleString('es-MX') + ' MXN';
        document.getElementById('infoReserva').style.display = 'block';

        const btnConfirmar = document.getElementById('btnConfirmar');
        if (diffDays < nochesMinimas) {
            btnConfirmar.style.background = '#ccc';
            btnConfirmar.style.cursor = 'not-allowed';
            btnConfirmar.disabled = true;
            btnConfirmar.textContent = `Mínimo ${nochesMinimas} ${nochesMinimas === 1 ? 'noche' : 'noches'}`;
        } else {
            btnConfirmar.style.background = '';
            btnConfirmar.style.cursor = 'pointer';
            btnConfirmar.disabled = false;
            btnConfirmar.textContent = 'Continuar';
        }
    } else {
        document.getElementById('infoReserva').style.display = 'none';
    }
}

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

    // Validar mínimo de noches
    const fecha1 = new Date(fechaLlegada);
    const fecha2 = new Date(fechaSalida);
    const diffTime = Math.abs(fecha2 - fecha1);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    const nochesMinimas = propiedadActual.numero_noches || 1;

    if (diffDays < nochesMinimas) {
        alert(`Esta propiedad requiere un mínimo de ${nochesMinimas} ${nochesMinimas === 1 ? 'noche' : 'noches'}`);
        return;
    }
    
    // Verificar sesión antes de continuar
    const logueado = await verificarSesionYActualizarMenu();
    
    if (!logueado) {
        if (confirm('Debes iniciar sesión para hacer una reservación.\n\n¿Deseas iniciar sesión ahora?')) {
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
    
    window.location.href = `ConfirmarYPagar.php?propiedad_id=${propiedadId}&fecha_inicio=${fechaLlegada}&fecha_fin=${fechaSalida}&num_huespedes=${numHuespedes}`;
}

// Funciones para modal de amenidades
function abrirModalAmenidades() {
    document.getElementById('modalAmenidades').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModalAmenidades() {
    document.getElementById('modalAmenidades').classList.remove('active');
    document.body.style.overflow = 'auto';
}

function cerrarModalSiClickFuera(event) {
    if (event.target.id === 'modalAmenidades') {
        cerrarModalAmenidades();
    }
}

// INICIALIZACIÓN
document.addEventListener('DOMContentLoaded', async function() {
    console.log(' Iniciando DetallePropiedad...');
    
    // 1. Verificar sesión y actualizar menú PRIMERO
    await verificarSesionYActualizarMenu();
    
    // 2. Cargar datos de la propiedad
    await cargarDetallePropiedad();
    
    // 3. Event listeners de fechas
    document.getElementById('fechaLlegada').addEventListener('change', function() {
        const fechaLlegada = this.value;
        if (fechaLlegada) {
            const fecha = new Date(fechaLlegada);
            fecha.setDate(fecha.getDate() + 1);
            document.getElementById('fechaSalida').min = fecha.toISOString().split('T')[0];
            calcularTotal();
        }
    });

    document.getElementById('fechaSalida').addEventListener('change', calcularTotal);

    // 4. Cerrar modales con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (document.getElementById('modalReserva').classList.contains('active')) {
                cerrarModalReserva();
            }
            if (document.getElementById('modalAmenidades').classList.contains('active')) {
                cerrarModalAmenidades();
            }
        }
    });
    
    // 5. Manejar reserva pendiente
    const reservaPendiente = sessionStorage.getItem('reserva_pendiente');
    if (reservaPendiente) {
        const datos = JSON.parse(reservaPendiente);
        sessionStorage.removeItem('reserva_pendiente');
        window.location.href = `ConfirmarYPagar.php?propiedad_id=${datos.propiedad_id}&fecha_inicio=${datos.fecha_inicio}&fecha_fin=${datos.fecha_fin}&num_huespedes=${datos.num_huespedes}`;
    }
    
    // 6. Abrir modal si regresó después de login
    const regresarPropiedad = sessionStorage.getItem('regresar_propiedad');
    if (regresarPropiedad && usuarioLogueado) {
        sessionStorage.removeItem('regresar_propiedad');
        setTimeout(() => {
            document.getElementById('modalReserva').classList.add('active');
            document.body.style.overflow = 'hidden';
        }, 500);
    }
    
    console.log(' DetallePropiedad inicializado');
});
         </script>
</body>
</html>