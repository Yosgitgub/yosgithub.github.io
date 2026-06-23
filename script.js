function showLogin() {
    document.getElementById('btn-login').classList.add('active');
    document.getElementById('btn-register').classList.remove('active');
    
    document.getElementById('login-form').classList.add('active');
    document.getElementById('register-form').classList.remove('active');
    
    // Ocultar panel lateral al ir a login
    document.getElementById('restaurant-panel').style.display = 'none';
}

function showRegister() {
    document.getElementById('btn-register').classList.add('active');
    document.getElementById('btn-login').classList.remove('active');
    
    document.getElementById('register-form').classList.add('active');
    document.getElementById('login-form').classList.remove('active');
    
    // Restaurar panel lateral si Restaurante estaba seleccionado
    toggleCarnet();
}

function toggleCarnet() {
    const role = document.querySelector('input[name="role"]:checked').value;
    const restaurantPanel = document.getElementById('restaurant-panel');
    const carnetInput = document.getElementById('reg-carnet');
    const cargoInputs = document.querySelectorAll('input[name="cargo"]');
    
    // Elementos del cliente
    const clientDataGroup = document.getElementById('client-data-group');
    const cedulaInput = document.getElementById('reg-cedula');
    const nombreInput = document.getElementById('reg-nombre');
    const apellidoInput = document.getElementById('reg-apellido');
    
    if (role === 'restaurante') {
        // Mostrar panel de restaurante
        restaurantPanel.style.display = 'block';
        carnetInput.required = true;
        cargoInputs.forEach(input => input.required = true);
        
        // Ocultar datos del cliente
        clientDataGroup.style.display = 'none';
        cedulaInput.required = false;
        nombreInput.required = false;
        apellidoInput.required = false;
    } else {
        // Ocultar panel de restaurante
        restaurantPanel.style.display = 'none';
        carnetInput.required = false;
        carnetInput.value = '';
        cargoInputs.forEach(input => {
            input.required = false;
            input.checked = false;
        });
        
        // Mostrar datos del cliente
        clientDataGroup.style.display = 'block';
        cedulaInput.required = true;
        nombreInput.required = true;
        apellidoInput.required = true;
    }
}

// Enviar formulario de inicio de sesión al backend
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('.submit-btn');
    const originalText = btn.textContent;
    btn.textContent = 'Iniciando...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('username', document.getElementById('login-username').value);
    formData.append('password', document.getElementById('login-password').value);

    try {
        const response = await fetch('login.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            alert(data.message + ' Bienvenido ' + data.data.nombre);
            
            // Redirigir dependiendo del cargo o rol
            if (data.data.rol === 'cliente') {
                window.location.href = 'cliente.php';
            } else if (data.data.rol === 'restaurante') {
                const cargo = data.data.cargo.toLowerCase();
                if (cargo === 'chef') {
                    window.location.href = 'chef.php';
                } else if (cargo === 'mesero') {
                    window.location.href = 'mesero.php';
                } else if (cargo === 'administrador') {
                    window.location.href = 'admin.php';
                } else {
                    window.location.href = 'dashboard.php';
                }
            }
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error de conexión con el servidor.');
    } finally {
        btn.textContent = originalText;
        btn.disabled = false;
    }
});

// Enviar formulario de registro al backend
document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('.submit-btn');
    const originalText = btn.textContent;
    btn.textContent = 'Registrando...';
    btn.disabled = true;

    const role = document.querySelector('input[name="role"]:checked').value;
    const formData = new FormData();
    
    // Datos básicos
    formData.append('role', role);
    formData.append('cedula', document.getElementById('reg-cedula').value);
    formData.append('nombre', document.getElementById('reg-nombre').value);
    formData.append('apellido', document.getElementById('reg-apellido').value);
    formData.append('username', document.getElementById('reg-username').value);
    formData.append('password', document.getElementById('reg-password').value);

    // Datos de restaurante
    if (role === 'restaurante') {
        const carnet = document.getElementById('reg-carnet').value.trim();
        const cargoChecked = document.querySelector('input[name="cargo"]:checked');
        
        if (!carnet || !cargoChecked) {
            alert('Por favor, ingresa tu número de carnet y selecciona tu cargo en el panel derecho.');
            btn.textContent = originalText;
            btn.disabled = false;
            return;
        }

        const cargo = cargoChecked.value;
        const primeraLetra = carnet.charAt(0).toLowerCase();

        if (cargo === 'Mesero' && primeraLetra !== 'm') {
            alert('❌ El carnet de un Mesero debe comenzar con la letra "M".\nEjemplo: M001, m123');
            btn.textContent = originalText;
            btn.disabled = false;
            return;
        }

        if (cargo === 'Chef' && primeraLetra !== 'c') {
            alert('❌ El carnet de un Chef debe comenzar con la letra "C".\nEjemplo: C001, c456');
            btn.textContent = originalText;
            btn.disabled = false;
            return;
        }
        
        formData.append('carnet', carnet);
        formData.append('cargo', cargo);
    }

    try {
        const response = await fetch('register.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            // Volver al login tras registro exitoso
            document.getElementById('register-form').reset();
            showLogin();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error de conexión con el servidor.');
    } finally {
        btn.textContent = originalText;
        btn.disabled = false;
    }
});

// ===========================
// CARRUSEL DE IMÁGENES
// ===========================
let currentSlide = 0;
const slides = document.querySelectorAll('.carousel-img');
const dots = document.querySelectorAll('.dot');

function goToSlide(index) {
    // Quitar clase active de la imagen y dot actuales
    slides[currentSlide].classList.remove('active');
    dots[currentSlide].classList.remove('active');
    
    // Actualizar índice
    currentSlide = index;
    
    // Añadir clase active a la nueva imagen y dot
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

function nextSlide() {
    const next = (currentSlide + 1) % slides.length;
    goToSlide(next);
}

// Auto-avance cada 4 segundos
setInterval(nextSlide, 4000);
