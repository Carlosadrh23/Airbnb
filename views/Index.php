<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeAway - Búsqueda de Alojamientos</title>
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

                <?php if (isset($_SESSION["usuario_id"]) && $_SESSION["usuario_id"] == 14): ?>
                    <!-- ADMIN: Muestra el Panel de Administración -->
                    <a href="AdminPanel.php" class="menu-item" style="color:#ff385c; font-weight:bold;">
                        Panel de Administración
                    </a>
                    <!-- Enlace de perfil con ID para que JavaScript lo modifique si es necesario -->
                    <a href="AdminPanel.php" class="menu-item" id="enlacePerfil">Mi perfil</a>
                <?php else: ?>
                    <!-- Usuario normal: enlace a Perfil.html -->
                    <a href="Perfil.html" class="menu-item" id="enlacePerfil">Mi perfil</a>
                <?php endif; ?>

                <a href="Anfitrion1.html" class="menu-item">Conviértete en anfitrión</a>
                <a href="CerrarSesion.php" class="menu-item">Cerrar sesión</a>
            </div>
        </div>
    </div>
</header>

<!--Botón del admin fuera del menú (visible en la página) -->
<?php if (isset($_SESSION["usuario_id"]) && $_SESSION["usuario_id"] == 14): ?>
    <div style="margin: 20px;">
        <a href="AdminPanel.php"
           style="
               padding:10px 18px;
               background:#333;
               color:#fff;
               border-radius:8px;
               text-decoration:none;
               font-weight:bold;
               box-shadow:0 2px 6px rgba(0,0,0,0.15);
           ">
            Panel de Administración
        </a>
    </div>
<?php endif; ?>

<!-- NUEVA BARRA DE BÚSQUEDA - SOLO DESTINOS -->
<div class="search-bar-simple">
    <button class="boton-destinos" id="btnDestinos">
        <span class="label-destinos">Destinos</span>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>
    
    <!-- Dropdown con lista de destinos -->
    <div class="dropdown-destinos" id="dropdownDestinos">
        <div class="header-dropdown">
            <h3>Explora destinos</h3>
            <button class="btn-cerrar-dropdown" onclick="cerrarDropdownDestinos()">×</button>
        </div>
        
        <div class="lista-destinos" id="listaDestinos">
            <div class="loading-destinos">Cargando destinos...</div>
        </div>
    </div>
</div>

<!-- Overlay para cerrar el dropdown al hacer clic fuera -->
<div class="overlay-destinos" id="overlayDestinos" onclick="cerrarDropdownDestinos()"></div>

<div class="propiedades" id="contenedorPropiedades">
    <div style="text-align: center; padding: 40px; width: 100%;">
        <p style="color: #717171;">Cargando propiedades...</p>
    </div>
</div>

<script src="../assets/javascript/main.js"></script>
<script>
    // Variables globales para destinos
    let propiedadesDestinos = [];

    // Función para abrir/cerrar dropdown
    function toggleDropdownDestinos() {
        const dropdown = document.getElementById('dropdownDestinos');
        const overlay = document.getElementById('overlayDestinos');
        const boton = document.getElementById('btnDestinos');
        
        const isActive = dropdown.classList.contains('active');
        
        if (isActive) {
            cerrarDropdownDestinos();
        } else {
            dropdown.classList.add('active');
            overlay.classList.add('active');
            boton.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Cargar destinos si aún no se han cargado
            if (propiedadesDestinos.length === 0) {
                cargarDestinos();
            }
        }
    }

    function cerrarDropdownDestinos() {
        const dropdown = document.getElementById('dropdownDestinos');
        const overlay = document.getElementById('overlayDestinos');
        const boton = document.getElementById('btnDestinos');
        
        dropdown.classList.remove('active');
        overlay.classList.remove('active');
        boton.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Cargar todos los destinos desde la base de datos
    async function cargarDestinos() {
        const listaDestinos = document.getElementById('listaDestinos');
        
        try {
            const response = await fetch('../app/AnfitrionController.php', {
                method: 'GET',
                credentials: 'include'
            });

            const resultado = await response.json();

            if (resultado.success && resultado.propiedades && resultado.propiedades.length > 0) {
                propiedadesDestinos = resultado.propiedades;
                mostrarListaDestinos(resultado.propiedades);
            } else {
                listaDestinos.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px; color: #717171;">
                        <p>No hay destinos disponibles en este momento.</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error al cargar destinos:', error);
            listaDestinos.innerHTML = `
                <div style="text-align: center; padding: 40px 20px; color: #e74c3c;">
                    <p>Error al cargar los destinos.</p>
                </div>
            `;
        }
    }

    function mostrarListaDestinos(propiedades) {
        const listaDestinos = document.getElementById('listaDestinos');
        listaDestinos.innerHTML = '';

        propiedades.forEach(propiedad => {
            const imagenUrl = propiedad.imagen_url 
                ? '../assets/img/propiedades/' + propiedad.imagen_url 
                : '../assets/img/placeholder.png';

            const precioFormateado = parseFloat(propiedad.precio_noche).toLocaleString('es-MX');
            const numeroNoches = propiedad.numero_noches || 1;
            const textoNoches = numeroNoches === 1 ? 'noche' : 'noches';

            const itemHTML = `
                <div class="item-destino" onclick="irAPropiedad(${propiedad.id})">
                    <img src="${imagenUrl}" 
                         alt="${propiedad.tipo_alojamiento} en ${propiedad.ciudad}" 
                         class="imagen-destino"
                         onerror="this.src='../assets/img/placeholder.png';">
                    <div class="info-destino">
                        <h4 class="titulo-destino">${propiedad.tipo_alojamiento} en ${propiedad.ciudad}</h4>
                        <p class="ubicacion-destino">${propiedad.ciudad}, ${propiedad.estado}</p>
                        <p class="precio-destino">
                            <strong>$${precioFormateado}</strong> 
                            <span class="moneda">MXN / ${numeroNoches} ${textoNoches}</span>
                        </p>
                    </div>
                </div>
            `;

            listaDestinos.innerHTML += itemHTML;
        });
    }

    function irAPropiedad(propiedadId) {
        window.location.href = `DetallePropiedad.php?id=${propiedadId}`;
    }

    // Cargar propiedades desde la base de datos
    async function cargarPropiedades() {
        try {
            const response = await fetch('../app/AnfitrionController.php', {
                method: 'GET',
                credentials: 'include'
            });

            const resultado = await response.json();

            if (resultado.success && resultado.propiedades?.length > 0) {
                mostrarPropiedades(resultado.propiedades);
            } else {
                document.getElementById('contenedorPropiedades').innerHTML = `
                    <div style="text-align: center; padding: 40px; width: 100%;">
                        <p style="color: #717171;">No hay propiedades disponibles en este momento.</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error al cargar propiedades:', error);
            document.getElementById('contenedorPropiedades').innerHTML = `
                <div style="text-align: center; padding: 40px; width: 100%;">
                    <p style="color: #e74c3c;">Error al cargar las propiedades. Intenta de nuevo más tarde.</p>
                </div>
            `;
        }
    }

    function mostrarPropiedades(propiedades) {
        const contenedor = document.getElementById('contenedorPropiedades');
        contenedor.innerHTML = '';

        propiedades.forEach(propiedad => {
            let imagenUrl = propiedad.imagen_url
                ? '../assets/img/propiedades/' + propiedad.imagen_url
                : '../assets/img/placeholder.png';

            const precioFormateado = parseFloat(propiedad.precio_noche).toLocaleString('es-MX');
            const noches = propiedad.numero_noches || 1;

            const propiedadHTML = `
                <div class="condominio">
                    <a href="DetallePropiedad.php?id=${propiedad.id}">
                        <div class="contenedor-img">
                            <img src="${imagenUrl}" 
                                 alt="${propiedad.tipo_alojamiento} en ${propiedad.ciudad}" 
                                 class="img-condominio" 
                                 style="width: 300px;"
                                 onerror="this.src='../assets/img/placeholder.png';">
                        </div>
                    </a>
                    <div class="info-condominio">
                        <h3 class="titulo-condominio">${propiedad.tipo_alojamiento} en ${propiedad.ciudad}</h3>
                        <p class="precio">$${precioFormateado} MXN 
                            <span class="noches">por ${noches} ${noches === 1 ? "noche" : "noches"}</span>
                        </p>
                        <div class="rating">
                            <span class="estrella">★</span>
                            <span class="valor-rating">5.0</span>
                        </div>
                    </div>
                </div>
            `;

            contenedor.innerHTML += propiedadHTML;
        });
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar botón de destinos
        const btnDestinos = document.getElementById('btnDestinos');
        if (btnDestinos) {
            btnDestinos.addEventListener('click', toggleDropdownDestinos);
        }
        
        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarDropdownDestinos();
            }
        });
        
        // Cargar propiedades al iniciar
        cargarPropiedades();
    });
</script>
</body>
</html>