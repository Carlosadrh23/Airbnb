const API_URL = '../app/AuthController.php';

// FUNCIÓN PRINCIPAL PARA PETICIONES
async function hacerPeticion(accion, datos = {}) {
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            credentials: 'include',
            body: JSON.stringify({accion, ...datos})
        });
        
        const texto = await response.text();
        
        try {
            return JSON.parse(texto);
        } catch (e) {
            console.error('Respuesta no JSON:', texto);
            return {
                success: false,
                message: 'Error del servidor: respuesta invalida'
            };
        }
    } catch (err) {
        console.error('Error de conexion:', err);
        return {
            success: false,
            message: 'Error de conexion con el servidor'
        };
    }
}

// INICIALIZAR MENÚ HAMBURGUESA (siempre visible)
function inicializarMenuHamburguesa() {
    const botonMenu = document.querySelector('.boton-menu');
    const menuSinSesion = document.getElementById('menuSinSesion');
    const menuConSesion = document.getElementById('menuConSesion');
    
    if (botonMenu) {
        botonMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log(' Clic en menú hamburguesa');
            
            // Determinar qué menú mostrar según la sesión
            const menuActivo = menuConSesion.style.display !== 'none' ? menuConSesion : menuSinSesion;
            
            if (menuActivo.classList.contains('active')) {
                menuActivo.classList.remove('active');
            } else {
                // Cerrar otros menús primero
                menuSinSesion.classList.remove('active');
                menuConSesion.classList.remove('active');
                // Abrir menú activo
                menuActivo.classList.add('active');
            }
        });
        
        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function() {
            menuSinSesion.classList.remove('active');
            menuConSesion.classList.remove('active');
        });
        
        // Prevenir que se cierre al hacer clic dentro del menú
        menuSinSesion.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        menuConSesion.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        console.log('✅ Menú hamburguesa inicializado');
    }
}

// INICIALIZAR ICONO DE USUARIO (solo cuando hay sesión)
function inicializarIconoUsuario() {
    const iconoUsuario = document.getElementById('iconoUsuario');
    
    if (iconoUsuario && iconoUsuario.style.display !== 'none') {
        iconoUsuario.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log(' Clic en icono usuario');
            
            const menuConSesion = document.getElementById('menuConSesion');
            if (menuConSesion.classList.contains('active')) {
                menuConSesion.classList.remove('active');
            } else {
                // Cerrar otros menús primero
                document.getElementById('menuSinSesion').classList.remove('active');
                // Abrir menú de usuario
                menuConSesion.classList.add('active');
            }
        });
        
        console.log(' Icono usuario inicializado');
    }
}

// REGISTRO
function inicializarRegistro() {
    const formRegistro = document.getElementById('formRegistro');
    if (!formRegistro) return;

    formRegistro.addEventListener('submit', async function(e) {
        e.preventDefault();
        const nombre = document.getElementById('nombre').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        if (!nombre || !email || !password) {
            alert('Completa todos los campos');
            return;
        }

        const btnSubmit = formRegistro.querySelector('button[type="submit"]');
        const textoOriginal = btnSubmit.textContent;
        btnSubmit.textContent = 'Registrando...';
        btnSubmit.disabled = true;

        const resultado = await hacerPeticion('registro', {nombre, email, password});

        if (resultado.success) {
            alert('Registro exitoso');
            window.location.href = 'index.php';
        } else {
            alert('Error: ' + resultado.message);
        }

        btnSubmit.textContent = textoOriginal;
        btnSubmit.disabled = false;
    });
}

// LOGIN
function inicializarLogin() {
    const formLogin = document.getElementById('formLogin');
    if (!formLogin) return;

    formLogin.addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        if (!email || !password) {
            alert('Completa todos los campos');
            return;
        }

        const btnSubmit = formLogin.querySelector('button[type="submit"]');
        const textoOriginal = btnSubmit.textContent;
        btnSubmit.textContent = 'Iniciando sesion...';
        btnSubmit.disabled = true;

        const resultado = await hacerPeticion('login', {email, password});

        if (resultado.success) {
            alert('Bienvenido');
            window.location.href = 'index.php';
        } else {
            alert('Error: ' + resultado.message);
        }

        btnSubmit.textContent = textoOriginal;
        btnSubmit.disabled = false;
    });
}

// VERIFICAR SESIÓN - ACTUALIZADA
async function verificarSesion() {
    try {
        const resultado = await hacerPeticion('verificar_sesion');
        
        const iconoUsuario = document.getElementById('iconoUsuario');
        const menuSinSesion = document.getElementById('menuSinSesion');
        const menuConSesion = document.getElementById('menuConSesion');
        const nombreUsuarioMenu = document.getElementById('nombreUsuarioMenu');
        const emailUsuarioMenu = document.getElementById('emailUsuarioMenu');

        console.log(' Estado sesión:', resultado);

        if (resultado.success && resultado.logueado) {
            console.log('Usuario logueado - Mostrando menú usuario');
            
            // Mostrar icono de usuario y menú con sesión
            if (iconoUsuario) iconoUsuario.style.display = 'flex';
            if (menuSinSesion) menuSinSesion.style.display = 'none';
            if (menuConSesion) menuConSesion.style.display = 'block';
            
            // Actualizar información del usuario en el menú
            if (nombreUsuarioMenu && resultado.usuario) {
                nombreUsuarioMenu.textContent = resultado.usuario.nombre || 'Usuario';
            }
            if (emailUsuarioMenu && resultado.usuario) {
                emailUsuarioMenu.textContent = resultado.usuario.email || '';
            }
            
            // INICIALIZAR EL ICONO DE USUARIO DESPUÉS DE MOSTRARLO
            setTimeout(inicializarIconoUsuario, 100);
            
        } else {
            console.log(' Usuario NO logueado - Mostrando menú normal');
            if (iconoUsuario) iconoUsuario.style.display = 'none';
            if (menuSinSesion) menuSinSesion.style.display = 'block';
            if (menuConSesion) menuConSesion.style.display = 'none';
        }
    } catch (err) {
        console.error('Error verificando sesion:', err);
    }
}

// CERRAR SESION - COMPLETO CON BOTÓN CANCELAR
function inicializarCerrarSesion() {
    const btnConfirmar = document.getElementById('btnConfirmar');
    const btnCancelar = document.getElementById('btnCancelar');
    
    // Botón Confirmar (Cerrar Sesión)
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', async function() {
            try {
                console.log(' Confirmando cierre de sesión...');
                const resultado = await hacerPeticion('logout');
                if (resultado.success) {
                    alert('Sesión cerrada');
                    window.location.href = 'index.php';
                }
            } catch (err) {
                console.error('Error al cerrar sesión:', err);
                alert('Error al cerrar sesión');
            }
        });
    }
    
    // Botón Cancelar (Regresar)
    if (btnCancelar) {
        btnCancelar.addEventListener('click', function() {
            console.log(' Cancelar - Regresando...');
            // Regresar a la página anterior o al index
            if (document.referrer && document.referrer.includes(window.location.hostname)) {
                window.history.back(); // Regresa a la página anterior
            } else {
                window.location.href = 'index.php'; // Si no hay página anterior, va al index
            }
        });
    }
    
    console.log('Botones de cerrar sesión inicializados');
}

// INICIALIZAR MENÚ BÁSICO (para menús sin sesión)
function inicializarMenuBasico() {
    console.log('🔧 Inicializando menús básicos...');
    
    // Buscar elementos de menú comunes y hacerlos clickeables
    const elementosMenu = document.querySelectorAll('nav a, .menu a, [class*="menu"] a');
    elementosMenu.forEach(elemento => {
        elemento.addEventListener('click', function(e) {
            console.log(' Clic en menú:', e.target.textContent);
        });
    });
}

// INICIALIZAR TODO
document.addEventListener('DOMContentLoaded', function() {
    console.log(' Inicializando aplicación...');
    
    // Inicializar menús
    inicializarMenuHamburguesa();
    inicializarMenuBasico();
    
    // Inicializar formularios
    inicializarRegistro();
    inicializarLogin();
    inicializarCerrarSesion();
    
    // Verificar sesión (esto inicializará el icono de usuario si hay sesión)
    verificarSesion();
    
    console.log(' Aplicación inicializada');
});

// DEBUG: Verificar clics en toda la página
document.addEventListener('click', function(e) {
    console.log('🖱️ Clic general en:', e.target.tagName, e.target.className);
});