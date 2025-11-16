// CONFIGURACIÓN GENERAL PARA HOSTING
const API_URL = '../app/AuthController.php'; // Ruta relativa desde views/

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
                window.location.href = 'Principal.html';
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
                window.location.href = 'Principal.html';
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
function inicializarCerrarSesion() {
    const btnConfirmar = document.getElementById('btnConfirmar');
    const btnCancelar = document.getElementById('btnCancelar');

    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', async function() {
            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ accion: 'logout' })
                });

                const resultado = await response.json();

                if (resultado.success) {
                    alert('Sesión cerrada correctamente');
                    window.location.href = 'Principal.html';
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Error al cerrar sesión');
            }
        });
    }

    if (btnCancelar) {
        btnCancelar.addEventListener('click', function() {
            window.location.href = 'Principal.html';
        });
    }
}

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
        const menuHamburguesa = document.querySelector('.menu-hamburguesa');
        const menuSinSesion = document.getElementById('menuSinSesion');
        const menuConSesion = document.getElementById('menuConSesion');
        const nombreUsuarioMenu = document.getElementById('nombreUsuarioMenu');
        const emailUsuarioMenu = document.getElementById('emailUsuarioMenu');

        if (resultado.logueado && resultado.usuario) {
            console.log('Usuario logueado:', resultado.usuario.nombre);
            
            if (iconoUsuario) iconoUsuario.style.display = 'flex';
            if (menuHamburguesa) menuHamburguesa.style.display = 'block';
            if (menuSinSesion) menuSinSesion.style.display = 'none';
            if (menuConSesion) menuConSesion.style.display = 'block';
            
            if (nombreUsuarioMenu) nombreUsuarioMenu.textContent = resultado.usuario.nombre;
            if (emailUsuarioMenu) emailUsuarioMenu.textContent = resultado.usuario.email;
            
        } else {
            console.log('Sin sesión activa');
            
            if (iconoUsuario) iconoUsuario.style.display = 'none';
            if (menuHamburguesa) menuHamburguesa.style.display = 'block';
            if (menuSinSesion) menuSinSesion.style.display = 'block';
            if (menuConSesion) menuConSesion.style.display = 'none';
        }
    } catch (err) {
        console.error('Error verificando sesión:', err);
        
        const iconoUsuario = document.querySelector('.icono-usuario');
        const menuHamburguesa = document.querySelector('.menu-hamburguesa');
        const menuSinSesion = document.getElementById('menuSinSesion');
        const menuConSesion = document.getElementById('menuConSesion');
        
        if (iconoUsuario) iconoUsuario.style.display = 'none';
        if (menuHamburguesa) menuHamburguesa.style.display = 'block';
        if (menuSinSesion) menuSinSesion.style.display = 'block';
        if (menuConSesion) menuConSesion.style.display = 'none';
    }
}

// =======================================================================
//                        INICIALIZAR TODO
// =======================================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Cargado - Sistema inicializado');
    
    inicializarRegistro();
    inicializarLogin();
    inicializarCerrarSesion();
    inicializarMenu();

    if (window.location.pathname.includes('Principal.html')) {
        console.log('Verificando sesión en Principal');
        verificarSesion();
    }
});