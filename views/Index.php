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
    <img src="../assets/img/Logo_azul.png" alt="Logo" class="logo">
    
    <div class="menu-container">
        <!-- Ícono de usuario (solo visible si hay sesión) -->
        <div class="icono-usuario" id="iconoUsuario" style="display: none;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        
        <!-- Menú hamburguesa (SIEMPRE visible) -->
        <div class="menu-hamburguesa">
            <button class="boton-menu">
                <span class="linea"></span>
                <span class="linea"></span>
                <span class="linea"></span>
            </button>
            
            <!-- Menú sin sesión -->
            <div class="dropdown-menu" id="menuSinSesion">
                <a href="Login.html" class="menu-item">Iniciar sesión / Registrarse</a>
                <a href="#" class="menu-item">Conviertete en anfitrión</a>
            </div>
            
            <!-- Menú con sesión -->
            <div class="dropdown-menu" id="menuConSesion" style="display: none;">
                <div class="user-info-menu">
                    <strong id="nombreUsuarioMenu">Usuario</strong>
                    <small id="emailUsuarioMenu">email@ejemplo.com</small>
                </div>
                <hr style="margin: 8px 0; border: none; border-top: 1px solid #EBEBEB;">
                <a href="#" class="menu-item">Mi perfil</a>
                <a href="#" class="menu-item">Conviertete en anfitrión</a>
               <a href="CerrarSession.html" class="menu-item">Cerrar sesión</a>
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

   <div class="propiedades">
    
    <!-- Condominio CDMX -->
<a href="CondominioCDMX.html">
    <div class="condominio">
        <div class="contenedor-img">
            <img src="../assets/img/condominio.png" alt="Condominio en Ciudad de México" class="img-condominio" style="width: 300px;">
        </div>
        <div class="info-condominio">
            <h3 class="titulo-condominio">Condominio en Ciudad de México</h3>
            <p class="precio">$2500 MXN <span class="noches">por 2 noches</span></p>
            <div class="rating">
                <span class="estrella">★</span>
                <span class="valor-rating">4.9</span>
            </div>
        </div>
    </div>
</a>

    <!-- Departamento Guadalajara -->
<a href="CondominioGDL.html">
    <div class="depa-gdl">
        <div class="contenedor-img">
            <img src="../assets/img/depa_gdl.png" alt="Departamento en Guadalajara" class="img-depa-gdl" style="width: 300px;">
        </div>
        <div class="info-depa-gdl">
            <h3 class="titulo-depa-gdl">Departamento en Guadalajara</h3>
            <p class="precio">$2900 MXN <span class="noches">por 2 noches</span></p>
            <div class="rating">
                <span class="estrella">★</span>
                <span class="valor-rating">4.89</span>
            </div>
        </div>
    </div>
</a>

    <!-- Departamento La Paz -->
<a href="CondominioLPZ.html">
    <div class="depa-lapaz">
        <div class="contenedor-img">
            <img src="../assets/img/depa_lapaz.png" alt="Departamento en La Paz BCS" class="img-depa-lapaz" style="width: 300px;">
        </div>
        <div class="info-depa-lapaz">
            <h3 class="titulo-depa-lapaz">Departamento en La Paz BCS</h3>
            <p class="precio">$3400 MXN <span class="noches">por 1 noches</span></p>
            <div class="rating">
                <span class="estrella">★</span>
                <span class="valor-rating">3.65</span>
            </div>
        </div>
    </div>
</a>

    <!-- Departamento Cabos -->
<a href="CondominioLosCabos.html">
    <div class="depa-cabos">
        <div class="contenedor-img">
            <img src="../assets/img/H-cabos.jpg" alt="Departamento en Cabos San Lucas" class="img-depa-cabos" style="width: 300px;">
        </div>
        <div class="info-depa-cabos">
            <h3 class="titulo-depa-cabos">Departamento en cabos san lucas</h3>
            <p class="precio">$2500 MXN <span class="noches">por 2 noches</span></p>
            <div class="rating">
                <span class="estrella">★</span>
                <span class="valor-rating">4.89</span>
            </div>
        </div>
    </div>
</a>

</div>
       <script src="../assets/javascript/main.js"></script>
</body>
</html>