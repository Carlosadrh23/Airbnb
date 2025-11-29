const API_ANFITRION = '../app/AnfitrionController.php';

//Variables globales para almacenar los datos del formulario
let datosAnfitrion = {
    tipoAlojamiento: '',
    region: '',
    direccion: '',
    departamento: '',
    zona: '',
    codigoPostal: '',
    ciudad: '',
    estado: '',
    precioNoche: 0,
    imagen: null
};

let pasoActual = 1;
const totalPasos = 4;

//NAVEGACIÓN ENTRE PASOS

function siguientePaso() {
    if (validarPasoActual()) {
        guardarDatosPaso();
        
        if (pasoActual < totalPasos) {
            pasoActual++;
            mostrarPaso(pasoActual);
        } else {
            // Último paso - enviar todos los datos
            enviarDatosAnfitrion();
        }
    }
}

function pasoAnterior() {
    if (pasoActual > 1) {
        pasoActual--;
        mostrarPaso(pasoActual);
    }
}

function mostrarPaso(paso) {
    console.log('Mostrando paso:', paso);
    
    // Ocultar todos los pasos
    const pasos = document.querySelectorAll('[class*="paso-"]');
    pasos.forEach(p => {
        if (p.classList.contains(`paso-${paso}`)) {
            p.style.display = 'block';
        } else {
            p.style.display = 'none';
        }
    });
}

//GUARDAR DATOS POR PASO

function guardarDatosPaso() {
    console.log('Guardando datos del paso:', pasoActual);
    
    switch(pasoActual) {
        case 1: // Tipo de alojamiento
            guardarTipoAlojamiento();
            break;
            
        case 2: // Dirección
            guardarDireccion();
            break;
            
        case 3: // Precio y foto
            guardarPrecioYFoto();
            break;
    }
    
    console.log('Datos actuales:', datosAnfitrion);
}

function guardarTipoAlojamiento() {
    // Buscar la opción seleccionada
    const opciones = document.querySelectorAll('.opcion-alojamiento, [data-tipo], button[onclick*="seleccionar"]');
    
    opciones.forEach(opcion => {
        if (opcion.classList.contains('seleccionado') || 
            opcion.classList.contains('activo') ||
            opcion.classList.contains('active')) {
            
            // Intentar obtener el tipo de diferentes formas
            datosAnfitrion.tipoAlojamiento = 
                opcion.dataset.tipo || 
                opcion.getAttribute('data-tipo') ||
                opcion.textContent.trim().toLowerCase();
        }
    });
    
    // Buscar texto visible en los botones
    if (!datosAnfitrion.tipoAlojamiento) {
        const textoBotones = ['casa', 'departamento', 'condominio'];
        opciones.forEach(opcion => {
            const texto = opcion.textContent.toLowerCase();
            textoBotones.forEach(tipo => {
                if (texto.includes(tipo)) {
                    datosAnfitrion.tipoAlojamiento = tipo;
                }
            });
        });
    }
}

function guardarDireccion() {
    // Buscar todos los inputs del paso 2
    const inputs = document.querySelectorAll('input[type="text"]');
    
    inputs.forEach(input => {
        const placeholder = input.placeholder.toLowerCase();
        const value = input.value.trim();
        
        // Identificar cada campo por su placeholder o name
        if (placeholder.includes('la paz') || placeholder.includes('región') || input.name === 'region') {
            datosAnfitrion.region = value;
        }
        else if (placeholder.includes('sonora') || placeholder.includes('dirección') || input.name === 'direccion') {
            datosAnfitrion.direccion = value;
        }
        else if (placeholder.includes('departamento') || placeholder.includes('habitación') || input.name === 'departamento') {
            datosAnfitrion.departamento = value;
        }
        else if (placeholder.includes('zona') || input.name === 'zona') {
            datosAnfitrion.zona = value;
        }
        else if (placeholder.includes('código') || placeholder.includes('postal') || input.name === 'codigoPostal') {
            datosAnfitrion.codigoPostal = value;
        }
        else if (placeholder.includes('ciudad') || placeholder.includes('municipio') || input.name === 'ciudad') {
            datosAnfitrion.ciudad = value;
        }
        else if (placeholder.includes('baja california') || placeholder.includes('estado') || input.name === 'estado') {
            datosAnfitrion.estado = value;
        }
    });
}

function guardarPrecioYFoto() {
    // Buscar el input del precio
    const precioInput = document.querySelector('input[type="number"]') || 
                       document.querySelector('input[placeholder*="precio"]') ||
                       document.querySelector('input[name="precio"]');
    
    if (precioInput) {
        datosAnfitrion.precioNoche = parseFloat(precioInput.value) || 0;
    }
    
    // Buscar el input de la imagen
    const imagenInput = document.querySelector('input[type="file"]');
    
    if (imagenInput && imagenInput.files && imagenInput.files[0]) {
        datosAnfitrion.imagen = imagenInput.files[0];
    }
}

//VALIDACIÓN DE PASOS

function validarPasoActual() {
    switch(pasoActual) {
        case 1:
            return validarTipoAlojamiento();
        case 2:
            return validarDireccion();
        case 3:
            return validarPrecioYFoto();
        case 4:
            return true; // Paso de confirmación, siempre válido
        default:
            return true;
    }
}

function validarTipoAlojamiento() {
    const tieneSeleccion = document.querySelector('.seleccionado, .activo, .active');
    
    if (!tieneSeleccion) {
        alert('Por favor selecciona un tipo de alojamiento');
        return false;
    }
    
    return true;
}

function validarDireccion() {
    const inputs = document.querySelectorAll('input[type="text"]');
    let direccionValida = false;
    let codigoPostalValido = false;
    let ciudadValida = false;
    
    inputs.forEach(input => {
        const placeholder = input.placeholder.toLowerCase();
        const value = input.value.trim();
        
        if ((placeholder.includes('sonora') || placeholder.includes('dirección')) && value) {
            direccionValida = true;
        }
        if ((placeholder.includes('código') || placeholder.includes('postal')) && value) {
            codigoPostalValido = true;
        }
        if ((placeholder.includes('ciudad') || placeholder.includes('municipio') || placeholder.includes('la paz')) && value) {
            ciudadValida = true;
        }
    });
    
    if (!direccionValida || !codigoPostalValido || !ciudadValida) {
        alert('Por favor completa los campos obligatorios: Dirección, Código Postal y Ciudad');
        return false;
    }
    
    return true;
}

function validarPrecioYFoto() {
    const precioInput = document.querySelector('input[type="number"]');
    
    if (!precioInput || !precioInput.value || parseFloat(precioInput.value) <= 0) {
        alert('Por favor ingresa un precio válido por noche');
        return false;
    }
    
    return true;
}

//ENVIAR DATOS AL SERVIDOR

async function enviarDatosAnfitrion() {
    console.log('Enviando datos del anfitrión:', datosAnfitrion);
    
    const formData = new FormData();
    
    // Agregar todos los datos
    formData.append('tipoAlojamiento', datosAnfitrion.tipoAlojamiento);
    formData.append('region', datosAnfitrion.region);
    formData.append('direccion', datosAnfitrion.direccion);
    formData.append('departamento', datosAnfitrion.departamento);
    formData.append('zona', datosAnfitrion.zona);
    formData.append('codigoPostal', datosAnfitrion.codigoPostal);
    formData.append('ciudad', datosAnfitrion.ciudad);
    formData.append('estado', datosAnfitrion.estado);
    formData.append('precioNoche', datosAnfitrion.precioNoche);
    
    if (datosAnfitrion.imagen) {
        formData.append('imagen', datosAnfitrion.imagen);
    }
    
    try {
        const response = await fetch(API_ANFITRION, {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        
        const resultado = await response.json();
        console.log('Respuesta del servidor:', resultado);
        
        if (resultado.success) {
            alert('¡Felicidades! Tu propiedad ha sido registrada exitosamente.');
            // Mostrar el paso de confirmación final o redirigir
            mostrarConfirmacionFinal(resultado);
        } else {
            alert('Error: ' + resultado.message);
        }
    } catch (error) {
        console.error('Error al enviar datos:', error);
        alert('Hubo un error al procesar tu solicitud. Por favor intenta de nuevo.');
    }
}

function mostrarConfirmacionFinal(resultado) {
    // Mostrar el último paso con los datos confirmados
    if (pasoActual < totalPasos) {
        pasoActual = totalPasos;
        mostrarPaso(pasoActual);
    }
    
    // Llenar el resumen si existe
    const resumenTipo = document.querySelector('[data-resumen="tipo"]');
    const resumenDireccion = document.querySelector('[data-resumen="direccion"]');
    const resumenCiudad = document.querySelector('[data-resumen="ciudad"]');
    const resumenEstado = document.querySelector('[data-resumen="estado"]');
    
    if (resumenTipo) resumenTipo.textContent = datosAnfitrion.tipoAlojamiento;
    if (resumenDireccion) resumenDireccion.textContent = datosAnfitrion.direccion;
    if (resumenCiudad) resumenCiudad.textContent = datosAnfitrion.ciudad;
    if (resumenEstado) resumenEstado.textContent = datosAnfitrion.estado;
    
    // Buscar los elementos por texto visible
    setTimeout(() => {
        const elementos = document.querySelectorAll('p, span, div');
        elementos.forEach(el => {
            const texto = el.textContent;
            if (texto.includes('Tipo:')) {
                el.innerHTML = `Tipo: <strong>${datosAnfitrion.tipoAlojamiento}</strong>`;
            }
            if (texto.includes('Dirección:')) {
                el.innerHTML = `Dirección: <strong>${datosAnfitrion.direccion}</strong>`;
            }
            if (texto.includes('Ciudad:')) {
                el.innerHTML = `Ciudad: <strong>${datosAnfitrion.ciudad}</strong>`;
            }
            if (texto.includes('Estado:')) {
                el.innerHTML = `Estado: <strong>${datosAnfitrion.estado}</strong>`;
            }
        });
    }, 100);
}

//MANEJO DE CLICKS EN OPCIONES

function configurarSeleccionOpciones() {
    // Configurar las opciones de tipo de alojamiento
    const opciones = document.querySelectorAll('.opcion-alojamiento, [data-tipo], button[onclick*="tipo"]');
    
    opciones.forEach(opcion => {
        opcion.addEventListener('click', function() {
            // Remover selección de todas las opciones
            opciones.forEach(o => {
                o.classList.remove('seleccionado', 'activo', 'active');
            });
            
            // Marcar esta opción como seleccionada
            this.classList.add('seleccionado');
            
            console.log('Tipo seleccionado:', this.textContent.trim());
        });
    });
}

//INICIALIZACIÓN

document.addEventListener('DOMContentLoaded', function() {
    console.log('Sistema de registro de anfitrión inicializado');
    
    // Configurar selección de opciones
    configurarSeleccionOpciones();
    
    // Configurar botones de navegación
    const botonesSiguiente = document.querySelectorAll('button[onclick*="siguiente"], .btn-siguiente, button:contains("Siguiente")');
    botonesSiguiente.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            siguientePaso();
        });
    });
    
    const botonesAtras = document.querySelectorAll('button[onclick*="atras"], .btn-atras, .btn-anterior');
    botonesAtras.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            pasoAnterior();
        });
    });
    
    const botonFinalizar = document.querySelector('button[onclick*="finalizar"], .btn-finalizar');
    if (botonFinalizar) {
        botonFinalizar.addEventListener('click', function(e) {
            e.preventDefault();
            // Redirigir al dashboard o página principal
            window.location.href = 'index.php';
        });
    }
    
    // Mostrar el primer paso
    mostrarPaso(1);
});