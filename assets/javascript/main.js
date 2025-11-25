// CONFIGURACIÓN GENERAL
const API_URL = '../app/AuthController.php';

// =======================================================================
//                           REGISTRO
// =======================================================================
function inicializarRegistro() {
    const formRegistro = document.getElementById('formRegistro');
    if (!formRegistro) return;

    formRegistro.addEventListener('submit', async function(e) {
        e.preventDefault();

        const nombre = document.getElementById('nombre').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        if (!nombre || !email || !password) return alert('Completa todos los campos');
        if (nombre.length < 3) return alert('El nombre debe tener al menos 3 caracteres');
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return alert('Correo inválido');
        if (password.length < 6) return alert('La contraseña debe tener mínimo 6 caracteres');

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ accion: 'registro', nombre, email, password })
            });

            const resultado = await response.json();
            console.log('Respuesta registro:', resultado);

            if (resultado.success) {
                alert('¡Registro exitoso! Bienvenido ' + nombre);
                
                const redirectUrl = sessionStorage.getItem('redirect_after_login');
                if (redirectUrl) {
                    sessionStorage.removeItem('redirect_after_login');
                    window.location.href = redirectUrl;
                } else {
                    window.location.href = window.location.origin + '/views/index.php';
                }
            } else {
                alert(resultado.message);
            }
        } catch (err) {
            console.error('Error:', err);
            alert('Error de conexión.');
        }
    });
}

// =======================================================================
//                              LOGIN
// =======================================================================
function inicializarLogin() {
    const formLogin = document.getElementById('formLogin');
    if (!formLogin) return;

    formLogin.addEventListener('submit', async function(e) {
        e.preventDefault();

        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        if (!email || !password) return alert('Completa todos los campos');
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return alert('Correo inválido');

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ accion: 'login', email, password })
            });

            const resultado = await response.json();
            console.log('Respuesta login:', resultado);

            if (resultado.success) {
                alert('¡Bienvenido ' + resultado.usuario.nombre + '!');
                
                const redirectUrl = sessionStorage.getItem('redirect_after_login');
                if (redirectUrl) {
                    sessionStorage.removeItem('redirect_after_login');
                    window.location.href = redirectUrl;
                } else {
                    window.location.href = window.location.origin + '/views/index.php';
                }
            } else {
                alert(resultado.message);
            }
        } catch (err) {
            console.error('Error:', err);
            alert('Error al iniciar sesión');
        }
    });
}

// =======================================================================
//                         CERRAR SESIÓN
// =======================================================================
async function cerrarSesion() {
    if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        return;
    }
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ accion: 'logout' })
        });

        const resultado = await response.json();
        
        if (resultado.success) {
            sessionStorage.removeItem('redirect_after_login');
            alert('Sesión cerrada correctamente');
            window.location.href = window.location.origin + '/views/index.php';
        } else {
            alert('Error al cerrar sesión: ' + resultado.message);
        }
    } catch (err) {
        console.error('Error:', err);
        alert('Error al cerrar sesión');
    }
}

window.cerrarSesion = cerrarSesion;

// =======================================================================
//                         MENÚ HAMBURGUESA
// =======================================================================
function inicializarMenu() {
    const botonMenu = document.querySelector('.boton-menu');
    const menuSinSesion = document.getElementById('menuSinSesion');
    const menuConSesion = document.getElementById('menuConSesion');

    if (botonMenu) {
        botonMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            
            if (menuSinSesion && menuSinSesion.style.display !== 'none') {
                menuSinSesion.classList.toggle('active');
            }
            if (menuConSesion && menuConSesion.style.display !== 'none') {
                menuConSesion.classList.toggle('active');
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.menu-hamburguesa')) {
                if (menuSinSesion) menuSinSesion.classList.remove('active');
                if (menuConSesion) menuConSesion.classList.remove('active');
            }
        });
    }
}

// =======================================================================
//                       VERIFICAR SESIÓN
// =======================================================================
async function verificarSesion() {
    console.log('Verificando sesión...');
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ accion: 'verificar_sesion' })
        });

        const resultado = await response.json();
        console.log('Resultado de sesión:', resultado);

        const iconoUsuario = document.querySelector('.icono-usuario');
        const menuSinSesion = document.getElementById('menuSinSesion');
        const menuConSesion = document.getElementById('menuConSesion');
        const nombreUsuarioMenu = document.getElementById('nombreUsuarioMenu');
        const emailUsuarioMenu = document.getElementById('emailUsuarioMenu');

        if (resultado.logueado && resultado.usuario) {
            console.log('Usuario logueado:', resultado.usuario.nombre);
            
            if (iconoUsuario) iconoUsuario.style.display = 'flex';
            if (menuSinSesion) menuSinSesion.style.display = 'none';
            if (menuConSesion) menuConSesion.style.display = 'block';
            
            if (nombreUsuarioMenu) nombreUsuarioMenu.textContent = resultado.usuario.nombre;
            if (emailUsuarioMenu) emailUsuarioMenu.textContent = resultado.usuario.email;
            
            return true;
            
        } else {
            console.log('Sin sesión activa');
            
            if (iconoUsuario) iconoUsuario.style.display = 'none';
            if (menuSinSesion) menuSinSesion.style.display = 'block';
            if (menuConSesion) menuConSesion.style.display = 'none';
            
            return false;
        }
    } catch (err) {
        console.error('Error verificando sesión:', err);
        
        const iconoUsuario = document.querySelector('.icono-usuario');
        const menuSinSesion = document.getElementById('menuSinSesion');
        const menuConSesion = document.getElementById('menuConSesion');
        
        if (iconoUsuario) iconoUsuario.style.display = 'none';
        if (menuSinSesion) menuSinSesion.style.display = 'block';
        if (menuConSesion) menuConSesion.style.display = 'none';
        
        return false;
    }
}

// =======================================================================
//              PROTEGER ENLACES DE ANFITRIÓN (CORREGIDO)
// =======================================================================
async function protegerEnlacesAnfitrion() {
    const enlacesAnfitrion = document.querySelectorAll('a[href*="Anfitrion"], .menu-item[href*="Anfitrion"]');
    
    if (enlacesAnfitrion.length === 0) {
        console.log('No hay enlaces de anfitrión en esta página');
        return;
    }
    
    console.log('Configurando protección para', enlacesAnfitrion.length, 'enlaces');
    
    enlacesAnfitrion.forEach(enlace => {
        // Remover listeners previos clonando el elemento
        const nuevoEnlace = enlace.cloneNode(true);
        enlace.parentNode.replaceChild(nuevoEnlace, enlace);
        
        nuevoEnlace.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const urlDestino = this.getAttribute('href');
            console.log('Click en:', urlDestino);
            
            try {
                // Verificar sesión EN TIEMPO REAL
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ accion: 'verificar_sesion' })
                });

                const resultado = await response.json();
                
                if (resultado.logueado && resultado.usuario) {
                    console.log(' Acceso permitido:', resultado.usuario.nombre);
                    sessionStorage.removeItem('redirect_after_login');
                    window.location.href = urlDestino;
                } else {
                    console.log(' Sin sesión - redirigiendo a login');
                    sessionStorage.setItem('redirect_after_login', urlDestino);
                    alert('Debes iniciar sesión para convertirte en anfitrión');
                    window.location.href = 'Login.html';
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Error de conexión');
            }
        });
    });
}

// =======================================================================
//                        INICIALIZAR TODO
// =======================================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Cargado - Sistema inicializado');
    
    inicializarRegistro();
    inicializarLogin();
    inicializarMenu();
    verificarSesion();
    protegerEnlacesAnfitrion();
});