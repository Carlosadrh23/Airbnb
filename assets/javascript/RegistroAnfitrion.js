
const API_ANFITRION = '../app/AnfitrionController.php';

// =======================================================================
//              ALMACENAMIENTO DE DATOS
// =======================================================================
const DatosAnfitrion = {
    guardar: (campo, valor) => {
        sessionStorage.setItem(campo, valor);
        console.log('Guardado: ' + campo + ' = ' + valor);
    },
    
    obtener: (campo) => {
        return sessionStorage.getItem(campo) || '';
    },
    
    limpiar: () => {
        const keysToRemove = [
            'tipoAlojamiento', 'region', 'direccion', 'departamento',
            'zona', 'codigoPostal', 'ciudad', 'estado', 'precioNoche', 'numeroNoches'
        ];
        keysToRemove.forEach(key => sessionStorage.removeItem(key));
        console.log('Datos limpiados');
    }
};

// =======================================================================
//              PASO 1: TIPO DE ALOJAMIENTO (Anfitrion1.html)
// =======================================================================
function inicializarPaso1() {
    const botones = document.querySelectorAll('.option-button');
    
    if (botones.length === 0) return;
    
    console.log('Inicializando Paso 1 - Tipo de Alojamiento');
    
    botones.forEach(boton => {
        boton.addEventListener('click', function() {
            // Remover selección previa
            botones.forEach(b => b.classList.remove('seleccionado'));
            
            // Marcar como seleccionado
            this.classList.add('seleccionado');
            
            // Obtener el tipo
            const tipo = this.getAttribute('data-tipo') || this.textContent.trim().toLowerCase();
            
            // Guardar
            DatosAnfitrion.guardar('tipoAlojamiento', tipo);
            
            console.log('Tipo seleccionado: ' + tipo);
            
            // Redirigir después de animación
            setTimeout(() => {
                window.location.href = 'Anfitrion2.html';
            }, 300);
        });
    });
}

// =======================================================================
//              PASO 2: DIRECCIÓN (Anfitrion2.html)
// =======================================================================
function inicializarPaso2() {
    const form = document.getElementById('formDireccion');
    
    if (!form) return;
    
    console.log('Inicializando Paso 2 - Dirección');
    
    // Validar solo números en código postal
    const codigoPostalInput = document.getElementById('codigoPostal');
    if (codigoPostalInput) {
        codigoPostalInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Obtener valores
        const region = document.getElementById('region').value.trim();
        const direccion = document.getElementById('direccion').value.trim();
        const departamento = document.getElementById('departamento').value.trim();
        const zona = document.getElementById('zona').value.trim();
        const codigoPostal = document.getElementById('codigoPostal').value.trim();
        const ciudad = document.getElementById('ciudad').value.trim();
        const estado = document.getElementById('estado').value.trim();
        
        // Validaciones
        if (!region) {
            alert('Por favor ingresa la región');
            document.getElementById('region').focus();
            return;
        }
        
        if (!direccion) {
            alert('Por favor ingresa la dirección');
            document.getElementById('direccion').focus();
            return;
        }
        
        if (!codigoPostal) {
            alert('Por favor ingresa el código postal');
            document.getElementById('codigoPostal').focus();
            return;
        }
        
        if (!/^\d{5}$/.test(codigoPostal)) {
            alert('El código postal debe tener 5 dígitos');
            document.getElementById('codigoPostal').focus();
            return;
        }
        
        if (!ciudad) {
            alert('Por favor ingresa la ciudad o municipio');
            document.getElementById('ciudad').focus();
            return;
        }
        
        if (!estado) {
            alert('Por favor ingresa el estado');
            document.getElementById('estado').focus();
            return;
        }
        
        // Guardar todos los datos
        DatosAnfitrion.guardar('region', region);
        DatosAnfitrion.guardar('direccion', direccion);
        DatosAnfitrion.guardar('departamento', departamento);
        DatosAnfitrion.guardar('zona', zona);
        DatosAnfitrion.guardar('codigoPostal', codigoPostal);
        DatosAnfitrion.guardar('ciudad', ciudad);
        DatosAnfitrion.guardar('estado', estado);
        
        console.log('Dirección guardada');
        
        // Redirigir al siguiente paso
        window.location.href = 'PrecioXNoche.html';
    });
}

// =======================================================================
//              PASO 3: PRECIO E IMAGEN (PrecioXNoche.html)
// =======================================================================
function inicializarPaso3() {
    const form = document.getElementById('formPrecio');
    
    if (!form) return;
    
    console.log('Inicializando Paso 3 - Precio e Imagen');
    
    let imagenSeleccionada = false;
    
    // Formatear precio
    const precioInput = document.getElementById('precioNoche');
    if (precioInput) {
        precioInput.addEventListener('input', function() {
            let valor = this.value.replace(/[^0-9]/g, '');
            if (valor) {
                this.value = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
        });
    }
    
    // Validar número de noches
    const nochesInput = document.getElementById('numeroNoches');
    if (nochesInput) {
        nochesInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value && parseInt(this.value) < 1) {
                this.value = '1';
            }
        });
    }
    
    // Manejo de imagen
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('previewContainer');
    const previewImage = document.getElementById('previewImage');
    const removeImage = document.getElementById('removeImage');
    
    function manejarArchivo(file) {
        if (!file.type.match('image/jpeg|image/png|image/webp')) {
            alert('Por favor selecciona una imagen JPG, PNG o WEBP');
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            alert('La imagen no debe superar los 5MB');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.src = e.target.result;
            uploadArea.style.display = 'none';
            previewContainer.style.display = 'block';
            imagenSeleccionada = true;
            console.log('Imagen cargada');
        };
        reader.readAsDataURL(file);
    }
    
    if (uploadArea) {
        uploadArea.addEventListener('click', () => fileInput.click());
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('drag-over');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            if (e.dataTransfer.files.length > 0) {
                manejarArchivo(e.dataTransfer.files[0]);
            }
        });
    }
    
    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                manejarArchivo(e.target.files[0]);
            }
        });
    }
    
    if (removeImage) {
        removeImage.addEventListener('click', () => {
            previewContainer.style.display = 'none';
            uploadArea.style.display = 'block';
            fileInput.value = '';
            imagenSeleccionada = false;
            console.log('Imagen removida');
        });
    }
    
    // Submit del formulario
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const precioStr = precioInput.value.replace(/[^0-9]/g, '');
        const precio = parseFloat(precioStr);
        
        if (!precio || precio <= 0) {
            alert('Por favor ingresa un precio válido por noche');
            precioInput.focus();
            return;
        }
        
        // Validar número de noches
        const numeroNoches = parseInt(nochesInput.value);
        if (!numeroNoches || numeroNoches < 1) {
            alert('Por favor ingresa un número válido de noches (mínimo 1)');
            nochesInput.focus();
            return;
        }
        
        // Validar descripción
        const descripcionInput = document.getElementById('descripcion');
        const descripcion = descripcionInput.value.trim();
        if (!descripcion || descripcion.length < 20) {
            alert('Por favor ingresa una descripción de al menos 20 caracteres');
            descripcionInput.focus();
            return;
        }
        
        console.log('Enviando datos al servidor...');
        
        const btnSubmit = document.getElementById('btnSubmit');
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Guardando...';
        
        // Crear FormData
        const formData = new FormData();
        formData.append('tipoAlojamiento', DatosAnfitrion.obtener('tipoAlojamiento'));
        formData.append('region', DatosAnfitrion.obtener('region'));
        formData.append('direccion', DatosAnfitrion.obtener('direccion'));
        formData.append('departamento', DatosAnfitrion.obtener('departamento'));
        formData.append('zona', DatosAnfitrion.obtener('zona'));
        formData.append('codigoPostal', DatosAnfitrion.obtener('codigoPostal'));
        formData.append('ciudad', DatosAnfitrion.obtener('ciudad'));
        formData.append('estado', DatosAnfitrion.obtener('estado'));
        formData.append('precioNoche', precio);
        formData.append('numeroNoches', numeroNoches);
        formData.append('descripcion', descripcion);
        
        if (fileInput.files && fileInput.files[0]) {
            formData.append('imagen', fileInput.files[0]);
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
                // Guardar datos para mostrar en confirmación
                sessionStorage.setItem('propiedadRegistrada', JSON.stringify(resultado.datos));
                
                console.log('Propiedad registrada exitosamente');
                
                // Redirigir a página de confirmación
                window.location.href = 'Anfitrion3.html';
            } else {
                alert('Error: ' + resultado.message);
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Siguiente';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Hubo un error al procesar tu solicitud. Por favor intenta de nuevo.');
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Siguiente';
        }
    });
}

// =======================================================================
//              PASO 4: CONFIRMACIÓN (Anfitrion3.html)
// =======================================================================
function inicializarPaso4() {
    const resumenTipo = document.getElementById('resumenTipo');
    const resumenDireccion = document.getElementById('resumenDireccion');
    const resumenCiudad = document.getElementById('resumenCiudad');
    const resumenEstado = document.getElementById('resumenEstado');
    
    if (!resumenTipo) return;
    
    console.log('Inicializando Paso 4 - Confirmación');
    
    // Obtener datos guardados
    const datosGuardados = sessionStorage.getItem('propiedadRegistrada');
    
    if (datosGuardados) {
        try {
            const datos = JSON.parse(datosGuardados);
            
            if (resumenTipo) resumenTipo.textContent = datos.tipo || '-';
            if (resumenDireccion) resumenDireccion.textContent = datos.direccion || '-';
            if (resumenCiudad) resumenCiudad.textContent = datos.ciudad || '-';
            if (resumenEstado) resumenEstado.textContent = datos.estado || '-';
            
            console.log('Datos mostrados en resumen');
        } catch (error) {
            console.error('Error al cargar datos:', error);
        }
    } else {
        // Cargar desde sessionStorage individual
        if (resumenTipo) resumenTipo.textContent = DatosAnfitrion.obtener('tipoAlojamiento') || '-';
        if (resumenDireccion) resumenDireccion.textContent = DatosAnfitrion.obtener('direccion') || '-';
        if (resumenCiudad) resumenCiudad.textContent = DatosAnfitrion.obtener('ciudad') || '-';
        if (resumenEstado) resumenEstado.textContent = DatosAnfitrion.obtener('estado') || '-';
    }
}

// Función global para finalizar (llamada desde HTML)
window.finalizarRegistro = function() {
    console.log('Finalizando registro');
    
    // Limpiar todo
    DatosAnfitrion.limpiar();
    sessionStorage.removeItem('propiedadRegistrada');
    
    // Redirigir a index
    window.location.href = 'index.php';
};

// =======================================================================
//              CARGAR PROPIEDADES EN INDEX
// =======================================================================
async function cargarPropiedadesDinamicas() {
    const contenedor = document.querySelector('.propiedades');
    
    if (!contenedor) return;
    
    console.log('Cargando propiedades dinámicas...');
    
    try {
        const response = await fetch(API_ANFITRION, {
            method: 'GET',
            credentials: 'include'
        });
        
        const resultado = await response.json();
        
        if (resultado.success && resultado.propiedades && resultado.propiedades.length > 0) {
            console.log(resultado.propiedades.length + ' propiedades encontradas');
            
            resultado.propiedades.forEach(propiedad => {
                const card = crearTarjetaPropiedad(propiedad);
                contenedor.appendChild(card);
            });
        } else {
            console.log('No hay propiedades nuevas para mostrar');
        }
    } catch (error) {
        console.error('Error al cargar propiedades:', error);
    }
}

function crearTarjetaPropiedad(propiedad) {
    const div = document.createElement('div');
    div.className = 'condominio';
    
    const imagenUrl = propiedad.imagen_url 
        ? `../assets/img/propiedades/${propiedad.imagen_url}` 
        : '../assets/img/placeholder.png';
    
    const precioFormateado = parseFloat(propiedad.precio_noche).toLocaleString('es-MX');
    
    div.innerHTML = `
        <a href="DetallePropiedad.php?id=${propiedad.id}">
            <div class="contenedor-img">
                <img src="${imagenUrl}" alt="${propiedad.tipo_alojamiento} en ${propiedad.ciudad}" class="img-condominio" style="width: 300px;">
            </div>
        </a>
        <div class="info-condominio">
            <h3 class="titulo-condominio">${propiedad.tipo_alojamiento} en ${propiedad.ciudad}</h3>
            <p class="precio">$${precioFormateado} MXN <span class="noches">por noche</span></p>
            <div class="rating">
                <span class="estrella">★</span>
                <span class="valor-rating">5.0</span>
            </div>
        </div>
    `;
    
    return div;
}

// =======================================================================
//              INICIALIZACIÓN AUTOMÁTICA
// =======================================================================
document.addEventListener('DOMContentLoaded', function() {
    const path = window.location.pathname;
    
    console.log('Sistema de Anfitrión Inicializado');
    console.log('Página actual:', path);
    
    // Detectar página actual e inicializar
    if (path.includes('Anfitrion1.html')) {
        inicializarPaso1();
    } 
    else if (path.includes('Anfitrion2.html')) {
        inicializarPaso2();
    } 
    else if (path.includes('PrecioXNoche.html')) {
        inicializarPaso3();
    } 
    else if (path.includes('Anfitrion3.html')) {
        inicializarPaso4();
    }
    else if (path.includes('index.php') || path.endsWith('/views/')) {
        cargarPropiedadesDinamicas();
    }
});