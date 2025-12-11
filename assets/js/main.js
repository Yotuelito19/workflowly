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
    
    // Inicializar carrusel del hero
    initHeroCarousel();
});

/**
 * Carrusel del Hero - transición lateral cada 10 segundos
 */
function initHeroCarousel() {
    const slides = document.querySelectorAll('.carousel-slide');
    
    if (slides.length === 0) return;
    
    let currentSlide = 0;
    const AUTOPLAY_DELAY = 10000; // 10 segundos
    
    function nextSlide() {
        const current = slides[currentSlide];
        const nextIndex = (currentSlide + 1) % slides.length;
        const next = slides[nextIndex];
        
        // Slide actual sale hacia la izquierda
        current.classList.remove('active');
        current.classList.add('exit');
        
        // Siguiente slide entra desde la derecha
        next.classList.add('active');
        
        // Después de la transición, resetear el slide anterior sin animación
        setTimeout(() => {
            current.style.transition = 'none';
            current.classList.remove('exit');
            // Forzar reflow para aplicar el cambio inmediatamente
            current.offsetHeight;
            current.style.transition = '';
        }, 1000);
        
        currentSlide = nextIndex;
    }
    
    setInterval(nextSlide, AUTOPLAY_DELAY);
}

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

async function cargarLugares() {
  const res = await fetch('../../api/admin/lugares/listar.php');
  const lugares = await res.json();
  const sel = document.getElementById('idLugar');
  if (!sel) return;
  sel.innerHTML = '<option value="">-- Selecciona lugar --</option>';
  lugares.forEach(l => {
    const opt = document.createElement('option');
    opt.value = l.idLugar;
    opt.textContent = l.ciudad ? `${l.nombre} (${l.ciudad})` : l.nombre;
    if (typeof l.capacidad !== 'undefined' && l.capacidad !== null) {
      opt.dataset.capacidad = l.capacidad;
    }
    sel.appendChild(opt);
  });
}

async function cargarOrganizadores() {
  const res = await fetch('../../api/admin/organizadores/listar.php');
  const orgs = await res.json();
  const sel = document.getElementById('idOrganizador');
  if (!sel) return;
  sel.innerHTML = '<option value="">-- Selecciona organizador --</option>';
  orgs.forEach(o => {
    const opt = document.createElement('option');
    opt.value = o.idOrganizador;
    opt.textContent = o.apellidos ? `${o.nombre} ${o.apellidos}` : o.nombre;
    sel.appendChild(opt);
  });
}

// ============================================
// FUNCIONES PARA SEARCH-EVENTS.PHP
// ============================================

/**
 * Cambiar el orden de los eventos
 * Preserva todos los parámetros de búsqueda actuales
 */
function changeSort(sortValue) {
    const url = new URL(window.location.href);
    url.searchParams.set('orden', sortValue);
    window.location.href = url.toString();
}

/**
 * Resetear el filtro de precio
 * Elimina los parámetros precio_min y precio_max de la URL
 */
function resetPriceFilter() {
    const precioMinInput = document.getElementById('precio_min');
    const precioMaxInput = document.getElementById('precio_max');
    
    if (precioMinInput) precioMinInput.value = '';
    if (precioMaxInput) precioMaxInput.value = '';
    
    const url = new URL(window.location.href);
    url.searchParams.delete('precio_min');
    url.searchParams.delete('precio_max');
    window.location.href = url.toString();
}

(function initSortableTable(){
  const table = document.querySelector('table');
  if (!table) return;

  const thead = table.querySelector('thead');
  const tbody = table.querySelector('#tbody');
  if (!thead || !tbody) return;

  // Mapa de columnas -> clave dentro del JSON de cada fila
  // Índices basados en tu <thead>:
  // 0: ID, 1: Nombre, 2: Inicio, 3: Fin, 4: Estado, 5: Stock, 6: Acciones (no ordena)
  const columnKeyMap = {
    0: 'idEvento',
    1: 'nombre',
    2: 'fechaInicio',
    3: 'fechaFin',
    4: 'estado_nombre',
    5: 'entradasDisponibles'
  };

  // Marca como "sortable" todos los TH salvo el último (Acciones)
  const ths = Array.from(thead.querySelectorAll('th'));
  ths.forEach((th, idx) => {
    if (idx === ths.length - 1) return; // Acciones: no ordenable
    th.classList.add('sortable');
    const indicator = document.createElement('span');
    indicator.className = 'sort-indicator';
    th.appendChild(indicator);

    th.addEventListener('click', () => {
      const key = columnKeyMap[idx];
      if (!key) return;

      // Alternar dirección
      const isAsc = !th.classList.contains('is-asc');
      ths.forEach(h => h.classList.remove('is-asc', 'is-desc'));
      th.classList.add(isAsc ? 'is-asc' : 'is-desc');

      // Tomar filas actuales
      const rows = Array.from(tbody.querySelectorAll('tr'));

      // Función para extraer valor desde data-json (si falla, usa el texto de la celda)
      const getValue = (tr) => {
        try {
          const obj = JSON.parse(tr.getAttribute('data-json') || '{}');
          return obj[key];
        } catch {
          const cell = tr.children[idx];
          return cell ? cell.textContent.trim() : '';
        }
      };

      // Comparadores por tipo de campo
      const parseMaybeDate = (v) => {
        // Soporta 'YYYY-MM-DD HH:MM:SS' y variantes parseables por Date
        const d = new Date(v);
        return isNaN(d.getTime()) ? null : d.getTime();
      };

      const isNumericKey = ['idEvento','entradasDisponibles'].includes(key);
      const isDateKey    = ['fechaInicio','fechaFin'].includes(key);

      rows.sort((a, b) => {
        let va = getValue(a), vb = getValue(b);

        if (isNumericKey) {
          va = Number(va); vb = Number(vb);
          if (isNaN(va)) va = -Infinity;
          if (isNaN(vb)) vb = -Infinity;
        } else if (isDateKey) {
          va = parseMaybeDate(va);
          vb = parseMaybeDate(vb);
          if (va === null) va = -Infinity;
          if (vb === null) vb = -Infinity;
        } else {
          // Texto (Nombre, Estado)
          va = (va ?? '').toString().toLowerCase();
          vb = (vb ?? '').toString().toLowerCase();
        }

        let cmp = 0;
        if (isNumericKey || isDateKey) {
          cmp = va - vb;
        } else {
          cmp = va.localeCompare(vb, 'es', { sensitivity: 'base' });
        }
        return isAsc ? cmp : -cmp;
      });

      // Reinyectar filas en el nuevo orden
      const frag = document.createDocumentFragment();
      rows.forEach(r => frag.appendChild(r));
      tbody.appendChild(frag);
    });
  });
})();
