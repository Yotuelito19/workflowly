/**
 * JavaScript principal de WorkFlowly
 */

// Toggle del menú móvil
function toggleMobileMenu() {
    const navMenu = document.querySelector('.nav-menu');
    const toggle = document.querySelector('.mobile-menu-toggle');
    navMenu.classList.toggle('active');
    toggle.classList.toggle('active');
}

// Validación de formularios
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('error');
        } else {
            field.classList.remove('error');
        }
    });

    return isValid;
}

// Validar email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Mostrar/ocultar contraseña
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}

// Mostrar notificaciones
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Confirmar acción
function confirmAction(message) {
    return confirm(message);
}

// Formatear precio
function formatPrice(price) {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
}

// Formatear fecha
function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('es-ES', options);
}

// Carrito de compras (localStorage)
const Cart = {
    get: function() {
        return JSON.parse(localStorage.getItem('cart')) || [];
    },
    
    set: function(cart) {
        localStorage.setItem('cart', JSON.stringify(cart));
    },
    
    add: function(item) {
        let cart = this.get();
        const existingItem = cart.find(i => i.id === item.id);
        
        if (existingItem) {
            existingItem.quantity += item.quantity;
        } else {
            cart.push(item);
        }
        
        this.set(cart);
        this.updateCartCount();
        showNotification('Entrada añadida al carrito', 'success');
    },
    
    remove: function(itemId) {
        let cart = this.get();
        cart = cart.filter(item => item.id !== itemId);
        this.set(cart);
        this.updateCartCount();
    },
    
    clear: function() {
        localStorage.removeItem('cart');
        this.updateCartCount();
    },
    
    getTotal: function() {
        const cart = this.get();
        return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    },
    
    getCount: function() {
        const cart = this.get();
        return cart.reduce((count, item) => count + item.quantity, 0);
    },
    
    updateCartCount: function() {
        const cartCountElements = document.querySelectorAll('.cart-count');
        const count = this.getCount();
        
        cartCountElements.forEach(element => {
            element.textContent = count;
            element.style.display = count > 0 ? 'inline' : 'none';
        });
    }
};

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar contador del carrito
    Cart.updateCartCount();
    
    // Cerrar notificaciones al hacer clic
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('notification')) {
            e.target.remove();
        }
    });
    
    // Validación de formularios en tiempo real
    const inputs = document.querySelectorAll('input[required], textarea[required], select[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
    });
});

// Prevenir envío múltiple de formularios
function preventMultipleSubmit(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Procesando...';
    }
}

// Permitir deseleccionar radio buttons al hacer clic nuevamente (para filtros)
function initDeselectableRadioButtons() {
    const radioButtons = document.querySelectorAll('input[type="radio"][name="tipo"]');
    let lastChecked = null;

    radioButtons.forEach(radio => {
        radio.addEventListener('click', function(e) {
            if (this === lastChecked) {
                // Si se hace clic en el mismo radio que ya estaba seleccionado, lo deseleccionamos
                this.checked = false;
                lastChecked = null;
                // Enviar el formulario para actualizar los resultados
                this.form.submit();
            } else {
                lastChecked = this;
            }
        });

        // Guardar el radio que está inicialmente seleccionado
        if (radio.checked) {
            lastChecked = radio;
        }
    });
}

// Resetear filtro de precio
function resetPriceFilter() {
    const precioMinInput = document.getElementById('precio_min');
    const precioMaxInput = document.getElementById('precio_max');
    
    if (precioMinInput) precioMinInput.value = '';
    if (precioMaxInput) precioMaxInput.value = '';
    
    // Enviar el formulario para actualizar los resultados
    const form = precioMinInput ? precioMinInput.form : precioMaxInput.form;
    if (form) {
        form.submit();
    }
}

// Inicializar radio buttons deseleccionables cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initDeselectableRadioButtons();
});