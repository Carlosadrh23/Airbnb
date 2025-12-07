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
                <a href="Perfil.html" class="menu-item">Mi perfil</a>
                <a href="Anfitrion1.html" class="menu-item">Conviértete en anfitrión</a>
                <a href="CerrarSesion.php" class="menu-item">Cerrar sesión</a>
            </div>
        </div>
    </div>
</header>

 <div class="search-bar">
        <div class="search-option">
            <span class="label">Destino</span>
            <span class="value">Buscar destinos</span>
        </div>
        
        <div class="search-option">
            <span class="label">Fechas</span>
            <span class="value">Agregar fechas</span>
        </div>
        
        <div class="search-option">
            <span class="label">Huéspedes</span>
            <span class="value">¿Cuántos?</span>
        </div>
        
        <button class="search-button">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
        </button>
    </div>

   <div class="propiedades" id="contenedorPropiedades">
       <div style="text-align: center; padding: 40px; width: 100%;">
           <p style="color: #717171;">Cargando propiedades...</p>
       </div>
   </div>

    <script src="../assets/javascript/main.js"></script>
    <script>
        // Cargar propiedades desde la base de datos
        async function cargarPropiedades() {
            try {
                const response = await fetch('../app/AnfitrionController.php', {
                    method: 'GET',
                    credentials: 'include'
                });

                const resultado = await response.json();
                console.log('Propiedades cargadas:', resultado);

                if (resultado.success && resultado.propiedades && resultado.propiedades.length > 0) {
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
                // Determinar la ruta de la imagen
                let imagenUrl;
                if (propiedad.imagen_url) {
                    imagenUrl = '../assets/img/propiedades/' + propiedad.imagen_url;
                } else {
                    imagenUrl = '../assets/img/placeholder.png';
                }

                // Calcular precio y noches
                const precioFormateado = parseFloat(propiedad.precio_noche).toLocaleString('es-MX');
                const numeroNoches = propiedad.numero_noches || 1;
                const textoNoches = numeroNoches === 1 ? 'noche' : 'noches';

                // Crear card de propiedad
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
                            <p class="precio">$${precioFormateado} MXN <span class="noches">por ${numeroNoches} ${textoNoches}</span></p>
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

        // Cargar propiedades al iniciar
        document.addEventListener('DOMContentLoaded', cargarPropiedades);
    </script>
</body>
</html>