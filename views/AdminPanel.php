<?php
session_start();

// Verificar que sea el admin
if (!isset($_SESSION["email"]) || strtolower($_SESSION["email"]) !== "adminhomeaway@gmail.com") {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - HomeAway</title>
    <link rel="stylesheet" href="../assets/styles-admin.css">
</head>
<body class="admin-panel-body">
    <!-- Sidebar -->
    <aside class="admin-panel-sidebar">
        <div class="admin-panel-logo">
            <img src="../assets/img/Logo_azul.png" alt="HomeAway">
            <h2>Dashboard</h2>
        </div>

        <nav class="admin-panel-nav">
            <a href="#" class="admin-panel-nav-item active" onclick="mostrarSeccion('dashboard')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="#" class="admin-panel-nav-item" onclick="mostrarSeccion('usuarios')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Usuarios</span>
            </a>

            <a href="#" class="admin-panel-nav-item" onclick="mostrarSeccion('propiedades')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>Propiedades</span>
            </a>

            <a href="#" class="admin-panel-nav-item" onclick="mostrarSeccion('reservaciones')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Reservaciones</span>
            </a>
        </nav>

        <a href="CerrarSesion.php" class="admin-panel-logout-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Cerrar Sesión</span>
        </a>
    </aside>

    <!-- Main Content -->
    <main class="admin-panel-main">
        <!-- Dashboard Section -->
        <div id="seccionDashboard">
            <div class="admin-panel-header">
                <h1>Dashboard</h1>
                <p>Bienvenido al panel de administración de HomeAway</p>
            </div>

            <div class="admin-panel-stats-grid">
                <div class="admin-panel-stat-card">
                    <div class="admin-panel-stat-header">
                        <span class="admin-panel-stat-title">Usuarios</span>
                        <div class="admin-panel-stat-icon blue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#1976d2" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                            </svg>
                        </div>
                    </div>
                    <div class="admin-panel-stat-value" id="totalUsuarios">0</div>
                    <div class="admin-panel-stat-label">Total de usuarios registrados</div>
                </div>

                <div class="admin-panel-stat-card">
                    <div class="admin-panel-stat-header">
                        <span class="admin-panel-stat-title">Propiedades</span>
                        <div class="admin-panel-stat-icon green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="admin-panel-stat-value" id="totalPropiedades">0</div>
                    <div class="admin-panel-stat-label">Propiedades publicadas</div>
                </div>

                <div class="admin-panel-stat-card">
                    <div class="admin-panel-stat-header">
                        <span class="admin-panel-stat-title">Reservaciones</span>
                        <div class="admin-panel-stat-icon orange">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#ef6c00" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                            </svg>
                        </div>
                    </div>
                    <div class="admin-panel-stat-value" id="totalReservaciones">0</div>
                    <div class="admin-panel-stat-label">Reservaciones activas</div>
                </div>
            </div>

            <div class="admin-panel-content-section">
                <div class="admin-panel-section-header">
                    <h2 class="admin-panel-section-title">Últimos Usuarios</h2>
                </div>
                <table class="admin-panel-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tablaUltimosUsuarios">
                        <tr>
                            <td colspan="4" class="admin-panel-no-data">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                </svg>
                                <div>Cargando usuarios...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Usuarios Section -->
        <div id="seccionUsuarios" style="display: none;">
            <div class="admin-panel-header">
                <h1>Gestión de Usuarios</h1>
                <p>Administra todos los usuarios de la plataforma</p>
            </div>

            <div class="admin-panel-content-section">
                <div class="admin-panel-section-header">
                    <h2 class="admin-panel-section-title">Todos los Usuarios</h2>
                </div>
                <table class="admin-panel-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Fecha Registro</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaUsuarios">
                        <tr>
                            <td colspan="6" class="admin-panel-no-data">Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Propiedades Section -->
        <div id="seccionPropiedades" style="display: none;">
            <div class="admin-panel-header">
                <h1>Gestión de Propiedades</h1>
                <p>Administra todas las propiedades publicadas</p>
            </div>

            <div class="admin-panel-content-section">
                <div class="admin-panel-section-header">
                    <h2 class="admin-panel-section-title">Todas las Propiedades</h2>
                </div>
                <table class="admin-panel-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Ubicación</th>
                            <th>Precio/Noche</th>
                            <th>Anfitrión</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPropiedades">
                        <tr>
                            <td colspan="6" class="admin-panel-no-data">Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reservaciones Section -->
        <div id="seccionReservaciones" style="display: none;">
            <div class="admin-panel-header">
                <h1>Gestión de Reservaciones</h1>
                <p>Administra todas las reservaciones realizadas</p>
            </div>

            <div class="admin-panel-content-section">
                <div class="admin-panel-section-header">
                    <h2 class="admin-panel-section-title">Todas las Reservaciones</h2>
                </div>
                <table class="admin-panel-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Propiedad</th>
                            <th>Fechas</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaReservaciones">
                        <tr>
                            <td colspan="7" class="admin-panel-no-data">Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Variables globales
        let datosUsuarios = [];
        let datosPropiedades = [];
        let datosReservaciones = [];

        // Mostrar sección
        function mostrarSeccion(seccion) {
            document.getElementById('seccionDashboard').style.display = 'none';
            document.getElementById('seccionUsuarios').style.display = 'none';
            document.getElementById('seccionPropiedades').style.display = 'none';
            document.getElementById('seccionReservaciones').style.display = 'none';

            document.querySelectorAll('.admin-panel-nav-item').forEach(item => {
                item.classList.remove('active');
            });

            if (seccion === 'dashboard') {
                document.getElementById('seccionDashboard').style.display = 'block';
            } else if (seccion === 'usuarios') {
                document.getElementById('seccionUsuarios').style.display = 'block';
                cargarUsuarios();
            } else if (seccion === 'propiedades') {
                document.getElementById('seccionPropiedades').style.display = 'block';
                cargarPropiedades();
            } else if (seccion === 'reservaciones') {
                document.getElementById('seccionReservaciones').style.display = 'block';
                cargarReservaciones();
            }

            event.currentTarget.classList.add('active');
        }

        // Cargar estadísticas del dashboard
        async function cargarEstadisticas() {
            try {
                // Cargar usuarios
                const resUsuarios = await fetch('../app/AuthController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ accion: 'obtener_todos_usuarios' })
                });
                const usuarios = await resUsuarios.json();
                
                if (usuarios.success) {
                    datosUsuarios = usuarios.usuarios || [];
                    document.getElementById('totalUsuarios').textContent = datosUsuarios.length;
                    mostrarUltimosUsuarios(datosUsuarios.slice(0, 5));
                }

                // Cargar propiedades
                const resPropiedades = await fetch('../app/AnfitrionController.php', {
                    method: 'GET',
                    credentials: 'include'
                });
                const propiedades = await resPropiedades.json();
                
                if (propiedades.success) {
                    datosPropiedades = propiedades.propiedades || [];
                    document.getElementById('totalPropiedades').textContent = datosPropiedades.length;
                }

                // Cargar reservaciones
                const resReservaciones = await fetch('../app/ReservacionController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ accion: 'obtener_todas' })
                });
                const reservaciones = await resReservaciones.json();
                
                if (reservaciones.success) {
                    datosReservaciones = reservaciones.reservaciones || [];
                    const activas = datosReservaciones.filter(r => r.estado === 'confirmada').length;
                    document.getElementById('totalReservaciones').textContent = activas;
                }

            } catch (error) {
                console.error('Error cargando estadísticas:', error);
            }
        }

        // Mostrar últimos usuarios en dashboard
        function mostrarUltimosUsuarios(usuarios) {
            const tbody = document.getElementById('tablaUltimosUsuarios');
            if (usuarios.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="admin-panel-no-data">No hay usuarios registrados</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            usuarios.forEach(usuario => {
                const fecha = new Date(usuario.fecha_registro).toLocaleDateString('es-MX', {
                    day: '2-digit',
                    month: 'short'
                });

                tbody.innerHTML += `
                    <tr>
                        <td><strong>${usuario.nombre}</strong></td>
                        <td>${usuario.email}</td>
                        <td>${fecha}</td>
                        <td><span class="admin-panel-badge activo">Activo</span></td>
                    </tr>
                `;
            });
        }

        // Cargar todos los usuarios
        async function cargarUsuarios() {
            try {
                const response = await fetch('../app/AuthController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ accion: 'obtener_todos_usuarios' })
                });

                const resultado = await response.json();
                const tbody = document.getElementById('tablaUsuarios');

                if (resultado.success && resultado.usuarios.length > 0) {
                    tbody.innerHTML = '';
                    resultado.usuarios.forEach(usuario => {
                        const fecha = new Date(usuario.fecha_registro).toLocaleDateString('es-MX');
                        tbody.innerHTML += `
                            <tr>
                                <td>${usuario.id}</td>
                                <td><strong>${usuario.nombre}</strong></td>
                                <td>${usuario.email}</td>
                                <td>${fecha}</td>
                                <td><span class="admin-panel-badge activo">Activo</span></td>
                                <td>
                                    <button class="admin-panel-btn-action admin-panel-btn-view" onclick="verUsuario(${usuario.id})">Ver</button>
                                    <button class="admin-panel-btn-action admin-panel-btn-delete" onclick="eliminarUsuario(${usuario.id})">Eliminar</button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="admin-panel-no-data">No hay usuarios registrados</td></tr>';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Cargar todas las propiedades
        async function cargarPropiedades() {
            try {
                const response = await fetch('../app/AnfitrionController.php', {
                    method: 'GET',
                    credentials: 'include'
                });

                const resultado = await response.json();
                const tbody = document.getElementById('tablaPropiedades');

                if (resultado.success && resultado.propiedades.length > 0) {
                    tbody.innerHTML = '';
                    resultado.propiedades.forEach(prop => {
                        const precio = parseFloat(prop.precio_noche).toLocaleString('es-MX');
                        tbody.innerHTML += `
                            <tr>
                                <td>${prop.id}</td>
                                <td><strong>${prop.tipo_alojamiento}</strong></td>
                                <td>${prop.ciudad}, ${prop.estado}</td>
                                <td>$${precio} MXN</td>
                                <td>Usuario #${prop.anfitrion_id}</td>
                                <td>
                                    <button class="admin-panel-btn-action admin-panel-btn-view" onclick="verPropiedad(${prop.id})">Ver</button>
                                    <button class="admin-panel-btn-action admin-panel-btn-delete" onclick="eliminarPropiedad(${prop.id})">Eliminar</button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="admin-panel-no-data">No hay propiedades registradas</td></tr>';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Cargar todas las reservaciones
        async function cargarReservaciones() {
            try {
                const response = await fetch('../app/ReservacionController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ accion: 'obtener_todas' })
                });

                const resultado = await response.json();
                const tbody = document.getElementById('tablaReservaciones');

                if (resultado.success && resultado.reservaciones.length > 0) {
                    tbody.innerHTML = '';
                    resultado.reservaciones.forEach(res => {
                        const fechaInicio = new Date(res.fecha_inicio).toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
                        const fechaFin = new Date(res.fecha_fin).toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
                        const precio = parseFloat(res.precio_total).toLocaleString('es-MX');
                        
                        let badgeClass = 'admin-panel-badge ';
                        if (res.estado === 'confirmada') badgeClass += 'activo';
                        else if (res.estado === 'cancelada') badgeClass += 'inactivo';
                        else badgeClass += 'pendiente';

                        tbody.innerHTML += `
                            <tr>
                                <td>${res.id}</td>
                                <td>Usuario #${res.usuario_id}</td>
                                <td>${res.propiedad?.tipo || 'Propiedad'}</td>
                                <td>${fechaInicio} - ${fechaFin}</td>
                                <td>$${precio} MXN</td>
                                <td><span class="${badgeClass}">${res.estado}</span></td>
                                <td>
                                    <button class="admin-panel-btn-action admin-panel-btn-view" onclick="verReservacion(${res.id})">Ver</button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" class="admin-panel-no-data">No hay reservaciones registradas</td></tr>';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Funciones auxiliares
        function verUsuario(id) {
            alert('Ver detalles del usuario #' + id);
        }

        function eliminarUsuario(id) {
            if (confirm('¿Estás seguro de eliminar este usuario?')) {
                alert('Eliminando usuario #' + id);
            }
        }

        function verPropiedad(id) {
            window.location.href = 'DetallePropiedad.php?id=' + id;
        }

        async function eliminarPropiedad(id) {
            if (confirm('¿Estás seguro de eliminar esta propiedad?')) {
                try {
                    const response = await fetch('../app/AnfitrionController.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'include',
                        body: JSON.stringify({ 
                            accion: 'eliminar',
                            id: id 
                        })
                    });

                    const resultado = await response.json();

                    if (resultado.success) {
                        alert('Propiedad eliminada correctamente');
                        cargarPropiedades();
                        cargarEstadisticas();
                    } else {
                        alert('Error al eliminar: ' + resultado.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error al intentar eliminar la propiedad');
                }
            }
        }

        function verReservacion(id) {
            alert('Ver detalles de la reservación #' + id);
        }

        // Cargar estadísticas al iniciar
        document.addEventListener('DOMContentLoaded', cargarEstadisticas);
    </script>
</body>
</html>