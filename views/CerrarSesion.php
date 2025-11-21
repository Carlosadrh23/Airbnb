<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrar sesión - HomeAway</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body class="pagina-logout">
    <div class="contenedor-logout">
        <div class="modal-logout">
            <div class="icono-logout">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#222222" stroke-width="1.5">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </div>
            <p class="texto-logout">¿Desea cerrar la sesión de su cuenta?</p>
            <div class="botones-logout">
                <button class="boton-confirmar" onclick="cerrarSesion()">Cerrar sesión</button>
                <button class="boton-cancelar" onclick="window.location.href='index.php'">Cancelar</button>
            </div>
        </div>
    </div>
    
    <script>
        async function cerrarSesion() {
            try {
                const response = await fetch('../app/AuthController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ accion: 'logout' })
                });
                const resultado = await response.json();
                
                if (resultado.success) {
                    alert('Sesión cerrada correctamente');
                    window.location.href = 'index.php';
                } else {
                    alert('Error: ' + resultado.message);
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Error al cerrar sesión');
            }
        }
    </script>
</body>
</html>