<?php
session_start();

// Obtener el ID de la propiedad desde la URL
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
                <a href="Login.html" class="menu-item">Iniciar sesion / Registrarse</a>
                <a href="Anfitrion1.html" class="menu-item">Conviertete en anfitrion</a>
            </div>
            
            <div class="dropdown-menu" id="menuConSesion" style="display: none;">
                <div class="user-info-menu">
                    <strong id="nombreUsuarioMenu">Usuario</strong>
                    <small id="emailUsuarioMenu">email@ejemplo.com</small>
                </div>
                <hr style="margin: 8px 0; border: none; border-top: 1px solid #EBEBEB;">
                <a href="Perfil.html" class="menu-item">Mi perfil</a>
                <a href="Anfitrion1.html" class="menu-item">Conviertete en anfitrion</a>
                <a href="CerrarSesion.php" class="menu-item">Cerrar sesion</a>
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
            <span class="label">Huespedes</span>
            <span class="value">Cuantos?</span>
        </div>
        
        <button class="search-button">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
        </button>
    </div>

    <div class="contenedor-detalle" id="contenedorDetalle">
        <div class="loading">Cargando...</div>
    </div>

    <script src="../assets/javascript/main.js"></script>
    <script>
        // Cargar detalle de la propiedad
        const propiedadId = <?php echo $propiedadId; ?>;
        
        async function cargarDetallePropiedad() {
            try {
                const response = await fetch('../app/AnfitrionController.php?id=' + propiedadId, {
                    method: 'GET',
                    credentials: 'include'
                });
                
                const resultado = await response.json();
                
                if (resultado.success && resultado.propiedad) {
                    mostrarDetalle(resultado.propiedad);
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
                            
                            <div class="descripcion-propiedad" style="margin: 20px 0; padding: 15px; background: #f7f7f7; border-radius: 8px;">
                                <h4 style="margin-bottom: 10px;">Descripcion:</h4>
                                <p style="line-height: 1.6;">${propiedad.descripcion}</p>
                            </div>
                            
                            <p class="precio">${precioFormateado} MXN <span class="noches">por ${numeroNoches} ${textoNoches}</span></p>
                            <div class="rating">
                                <span class="estrella">★</span>
                                <span class="valor-rating">5.0</span>
                            </div>
                            <p class="anfitrion-info">Anfitrion: ${propiedad.nombre_anfitrion}</p>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('contenedorDetalle').innerHTML = html;
        }
        
        document.addEventListener('DOMContentLoaded', cargarDetallePropiedad);
    </script>
</body>
</html>