/* ========================================
   JAVASCRIPT PARA SECCIÓN DE PAGOS
   ======================================== */

let metodoPagoActual = null;

// Inicializar cuando el DOM este listo
document.addEventListener('DOMContentLoaded', function() {
    inicializarSeccionPagos();
});

// ============================================
// INICIALIZACIÓN
// ============================================
function inicializarSeccionPagos() {
    cargarMetodosPago();
    configurarFiltros();
    configurarFormularioTarjeta();
}

// ============================================
// MÉTODOS DE PAGO
// ============================================
function cargarMetodosPago() {
    const container = document.getElementById('paymentCardsContainer');
    
    // Simular carga de metodos de pago
    setTimeout(() => {
        const metodosPago = [
            {
                id: 1,
                tipo: 'Visa',
                numero: '**** **** **** 4532',
                titular: 'Juan Pérez',
                expiracion: '12/25',
                predeterminado: true
            },
            {
                id: 2,
                tipo: 'Mastercard',
                numero: '**** **** **** 8765',
                titular: 'Juan Pérez',
                expiracion: '08/24',
                predeterminado: false
            },
             {
                id: 3,
                tipo: 'bbva',
                numero: '**** **** **** 0654',
                titular: 'pedro jimenez',
                expiracion: '06/27',
                predeterminado: false
            }
        ];
        
        mostrarMetodosPago(metodosPago);
    }, 500);
}

function mostrarMetodosPago(metodos) {
    const container = document.getElementById('paymentCardsContainer');
    
    if (!metodos || metodos.length === 0) {
        container.innerHTML = `
            <div class="empty-state-cards">
                <i class="fas fa-credit-card"></i>
                <p>No tienes métodos de pago guardados</p>
                <button class="btn-primary" onclick="abrirModalAgregarTarjeta()">
                    <i class="fas fa-plus"></i> Añadir método de pago
                </button>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    metodos.forEach(metodo => {
        const iconClass = metodo.tipo === 'Visa' ? 'cc-visa' : 
                         metodo.tipo === 'Mastercard' ? 'cc-mastercard' : 
                         metodo.tipo === 'American Express' ? 'cc-amex' : 'credit-card';
        
        html += `
            <div class="payment-card-item" data-card-id="${metodo.id}">
                <div class="card-brand-icon">
                    <i class="fab fa-${iconClass}"></i>
                </div>
                <div class="card-details">
                    <div class="card-type">
                        ${metodo.tipo}
                        ${metodo.predeterminado ? '<span class="badge-default">Predeterminado</span>' : ''}
                    </div>
                    <div class="card-number">${metodo.numero}</div>
                    <div class="card-expiry">
                        <i class="fas fa-calendar-alt"></i> 
                        Expira ${metodo.expiracion}
                    </div>
                </div>
                <div class="card-actions-item">
                    ${!metodo.predeterminado ? `
                        <button class="btn-card-action" 
                                onclick="establecerPredeterminado(${metodo.id})"
                                title="Establecer como predeterminado">
                            <i class="fas fa-star"></i>
                        </button>
                    ` : ''}
                    <button class="btn-card-action" 
                            onclick="editarMetodoPago(${metodo.id})"
                            title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-card-action danger" 
                            onclick="eliminarMetodoPago(${metodo.id})"
                            title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function establecerPredeterminado(idMetodo) {
    if (!confirm('¿Establecer este método como predeterminado?')) {
        return;
    }
    
    // Simular actualización (en producción sería una llamada AJAX)
    mostrarNotificacion('Método de pago establecido como predeterminado', 'success');
    
    // Recargar metodos
    setTimeout(() => {
        cargarMetodosPago();
    }, 500);
}

function editarMetodoPago(idMetodo) {
    mostrarNotificacion('Funcionalidad en desarrollo', 'info');
}

function eliminarMetodoPago(idMetodo) {
    if (!confirm('¿Estás seguro de eliminar este método de pago?')) {
        return;
    }
    
    const card = document.querySelector(`[data-card-id="${idMetodo}"]`);
    if (card) {
        card.style.opacity = '0';
        card.style.transform = 'scale(0.8)';
        
        setTimeout(() => {
            card.remove();
            mostrarNotificacion('Método de pago eliminado', 'success');
            
            // Si no quedan mas tarjetas, mostrar estado vacio
            const container = document.getElementById('paymentCardsContainer');
            if (!container.querySelector('.payment-card-item')) {
                mostrarMetodosPago([]);
            }
        }, 300);
    }
}

// ============================================
// MODAL AGREGAR TARJETA
// ============================================
function abrirModalAgregarTarjeta() {
    const modal = document.getElementById('modalAgregarTarjeta');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Limpiar formulario
    document.getElementById('formAgregarTarjeta').reset();
}

function cerrarModalAgregarTarjeta() {
    const modal = document.getElementById('modalAgregarTarjeta');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function configurarFormularioTarjeta() {
    const form = document.getElementById('formAgregarTarjeta');
    if (!form) return;
    
    // Formateo automatico del número de tarjeta
    const cardNumber = document.getElementById('cardNumber');
    if (cardNumber) {
        cardNumber.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
    }
    
    // Formateo de fecha de expiracion
    const cardExpiry = document.getElementById('cardExpiry');
    if (cardExpiry) {
        cardExpiry.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });
    }
    
    // Solo números en CVV
    const cardCVV = document.getElementById('cardCVV');
    if (cardCVV) {
        cardCVV.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }
    
    // Submit del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        guardarMetodoPago();
    });
}

function guardarMetodoPago() {
    const cardNumber = document.getElementById('cardNumber').value;
    const cardName = document.getElementById('cardName').value;
    const cardExpiry = document.getElementById('cardExpiry').value;
    const cardCVV = document.getElementById('cardCVV').value;
    const setAsDefault = document.getElementById('setAsDefault').checked;
    
    // Validaciones basicas
    if (!validarNumeroTarjeta(cardNumber.replace(/\s/g, ''))) {
        mostrarNotificacion('Número de tarjeta inválido', 'error');
        return;
    }
    
    if (!validarFechaExpiracion(cardExpiry)) {
        mostrarNotificacion('Fecha de expiración inválida', 'error');
        return;
    }
    
    if (cardCVV.length !== 3) {
        mostrarNotificacion('CVV inválido', 'error');
        return;
    }
    
    // Simular guardado
    mostrarNotificacion('Procesando...', 'info');
    
    setTimeout(() => {
        cerrarModalAgregarTarjeta();
        mostrarNotificacion('Método de pago añadido correctamente', 'success');
        cargarMetodosPago();
    }, 1500);
}

function validarNumeroTarjeta(numero) {
    // Algoritmo de Luhn para validación bsica
    if (!/^\d{13,19}$/.test(numero)) return false;
    
    let sum = 0;
    let isEven = false;
    
    for (let i = numero.length - 1; i >= 0; i--) {
        let digit = parseInt(numero.charAt(i), 10);
        
        if (isEven) {
            digit *= 2;
            if (digit > 9) digit -= 9;
        }
        
        sum += digit;
        isEven = !isEven;
    }
    
    return (sum % 10) === 0;
}

function validarFechaExpiracion(fecha) {
    if (!/^\d{2}\/\d{2}$/.test(fecha)) return false;
    
    const [mes, ano] = fecha.split('/').map(n => parseInt(n, 10));
    const ahora = new Date();
    const anoActual = ahora.getFullYear() % 100;
    const mesActual = ahora.getMonth() + 1;
    
    if (mes < 1 || mes > 12) return false;
    if (ano < anoActual) return false;
    if (ano === anoActual && mes < mesActual) return false;
    
    return true;
}

// ============================================
// FILTROS DE HISTORIAL
// ============================================
function configurarFiltros() {
    const filterYear = document.getElementById('filterYear');
    const filterStatus = document.getElementById('filterStatus');
    
    if (filterYear) {
        filterYear.addEventListener('change', aplicarFiltros);
    }
    
    if (filterStatus) {
        filterStatus.addEventListener('change', aplicarFiltros);
    }
}

function aplicarFiltros() {
    const yearFilter = document.getElementById('filterYear')?.value || 'all';
    const statusFilter = document.getElementById('filterStatus')?.value || 'all';
    const rows = document.querySelectorAll('#purchasesTableBody tr');
    
    rows.forEach(row => {
        const year = row.getAttribute('data-year');
        const status = row.getAttribute('data-status');
        
        const showYear = yearFilter === 'all' || year === yearFilter;
        const showStatus = statusFilter === 'all' || status === statusFilter;
        
        row.style.display = (showYear && showStatus) ? '' : 'none';
    });
}

// ============================================
// DETALLES DE COMPRA
// ============================================
function verDetallesCompra(idCompra) {
    const modal = document.getElementById('modalDetallesCompra');
    const content = document.getElementById('detallesCompraContent');
    
    content.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Cargando detalles...</p>
        </div>
    `;
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Simular carga de detalles (en producción sería AJAX)
    setTimeout(() => {
        content.innerHTML = `
            <div class="purchase-detail-header">
                <i class="fas fa-receipt"></i>
                <h2>Detalles de la compra #${idCompra}</h2>
            </div>
            
            <div class="purchase-detail-body">
                <div class="detail-section">
                    <h3>Información de la compra</h3>
                    <div class="detail-grid">
                        <div class="detail-item-modal">
                            <label>Fecha</label>
                            <span>15/11/2024 - 14:30</span>
                        </div>
                        <div class="detail-item-modal">
                            <label>Estado</label>
                            <span class="status-badge status-completada">
                                <i class="fas fa-check-circle"></i> Completada
                            </span>
                        </div>
                        <div class="detail-item-modal">
                            <label>Método de pago</label>
                            <span>Visa **** 4532</span>
                        </div>
                        <div class="detail-item-modal">
                            <label>Total</label>
                            <span class="amount">45,00 €</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Entradas compradas</h3>
                    <div class="tickets-list-modal">
                        <div class="ticket-item-modal">
                            <i class="fas fa-ticket-alt"></i>
                            <div>
                                <strong>Concierto Rock Festival 2024</strong>
                                <span>Zona VIP - Entrada General</span>
                            </div>
                            <span class="ticket-price">45,00 €</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-actions">
                <button class="btn-secondary" onclick="cerrarModalDetalles()">
                    Cerrar
                </button>
                <button class="btn-primary" onclick="descargarFactura(${idCompra})">
                    <i class="fas fa-download"></i> Descargar factura
                </button>
            </div>
        `;
    }, 500);
}

function cerrarModalDetalles() {
    const modal = document.getElementById('modalDetallesCompra');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ============================================
// DESCARGAR FACTURA
// ============================================
function descargarFactura(idCompra) {
    mostrarNotificacion('Descargando factura...', 'info');
    
    // Simular descarga (en produccion seria una llamada al backend)
    setTimeout(() => {
        mostrarNotificacion('Factura descargada correctamente', 'success');
    }, 1000);
}

// ============================================
// CERRAR MODALES CON ESC O CLICK FUERA
// ============================================
document.addEventListener('click', function(e) {
    const modalTarjeta = document.getElementById('modalAgregarTarjeta');
    const modalDetalles = document.getElementById('modalDetallesCompra');
    
    if (e.target === modalTarjeta) {
        cerrarModalAgregarTarjeta();
    }
    
    if (e.target === modalDetalles) {
        cerrarModalDetalles();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModalAgregarTarjeta();
        cerrarModalDetalles();
    }
});

// ============================================
// FUNCIÓN DE NOTIFICACIÓN
// ============================================
if (typeof mostrarNotificacion !== 'function') {
    function mostrarNotificacion(mensaje, tipo) {
        const notif = document.createElement('div');
        notif.className = `notification notification-${tipo} show`;
        notif.innerHTML = `
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : 
                              tipo === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${mensaje}</span>
        `;
        
        document.body.appendChild(notif);
        
        setTimeout(() => {
            notif.classList.remove('show');
            setTimeout(() => notif.remove(), 300);
        }, 3000);
    }
}