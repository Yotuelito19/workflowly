<?php
/**
 * Panel de usuario - WorkFlowly
 */

require_once '../config/config.php';
require_once '../config/database.php';

// Verificar que esté logueado
if (!is_logged_in()) {
    redirect('/views/login.php');
}

// Conectar BD
$database = new Database();
$db = $database->getConnection();

// Obtener datos del usuario
$usuarioModel = new Usuario($db);
$usuario = $usuarioModel->obtenerPorId($_SESSION['user_id']);

// Obtener entradas del usuario
$compraModel = new Compra($db);
$entradas = $compraModel->obtenerEntradasUsuario($_SESSION['user_id']);
$compras = $compraModel->obtenerComprasUsuario($_SESSION['user_id']);

// Procesar actualización de perfil
$mensaje_perfil = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $usuarioModel->idUsuario = $_SESSION['user_id'];
    $usuarioModel->nombre = sanitize_input($_POST['nombre']);
    $usuarioModel->apellidos = sanitize_input($_POST['apellidos']);
    $usuarioModel->telefono = sanitize_input($_POST['telefono']);
    
    if ($usuarioModel->actualizar()) {
        $mensaje_perfil = 'Perfil actualizado correctamente';
        $_SESSION['user_name'] = $usuarioModel->nombre;
        $usuario = $usuarioModel->obtenerPorId($_SESSION['user_id']);
    } else {
        $mensaje_perfil = 'Error al actualizar el perfil';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - WorkFlowly</title>
    <link rel="stylesheet" href="../assets/css/account.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <a href="../index.php" class="logo">
                    <div class="logo-circle"><span>W</span></div>
                    <span class="brand-name">WorkFlowly</span>
                </a>
            </div>
            <nav class="nav-menu">
                <a href="search-events.php">Eventos</a>
                <a href="../index.php">Inicio</a>
                <a href="/ayuda">Ayuda</a>
            </nav>
            <div class="nav-actions">
                
                <a href="../api/logout.php" class="btn-link">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <main class="account-main">
        <div class="container">
            <div class="account-layout">
                <!-- Sidebar -->
                 <!-- cambio y ponerlo bonmito-->
                <aside class="account-sidebar">
    <div class="user-profile">
        <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <h3><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h3>
        <p><?php echo htmlspecialchars($usuario['email']); ?></p>
        <span class="user-badge"><?php echo htmlspecialchars($usuario['tipoUsuario']); ?></span>
    </div>

    <nav class="account-nav">
        <a href="#perfil" class="active" onclick="showTab(event, 'perfil')">
            <i class="fas fa-user"></i> Perfil
        </a>
        <a href="#mis-entradas" onclick="showTab(event, 'mis-entradas')">
            <i class="fas fa-ticket-alt"></i> Mis Entradas
        </a>
        <a href="#historial" onclick="showTab(event, 'historial')">
            <i class="fas fa-credit-card"></i> Pagos
        </a>
        <a href="#seguridad" onclick="showTab(event, 'seguridad')">
            <i class="fas fa-shield-alt"></i> Seguridad
        </a>
        <a href="#preferencias" onclick="showTab(event, 'preferencias')">
            <i class="fas fa-sliders-h"></i> Preferencias
        </a>
    </nav>
</aside>
<!-- cambio y ponerlo bonmito-->

                <!-- Main Content -->
                <div class="account-content">
                    <!-- Mis Entradas -->
                    <div class="tab-content active" id="mis-entradas">
                        <h1>Mis Entradas</h1>
                        
                        <?php if (!empty($entradas)): ?>
                            <div class="tickets-grid">
                                <?php foreach ($entradas as $entrada): ?>
                                    <div class="ticket-card">
                                        <div class="ticket-header">
                                            <h3><?php echo htmlspecialchars($entrada['evento_nombre']); ?></h3>
                                            <span class="ticket-status <?php echo strtolower($entrada['estado_entrada']); ?>">
                                                <?php echo htmlspecialchars($entrada['estado_entrada']); ?>
                                            </span>
                                        </div>
                                        <div class="ticket-details">
                                            <p><i class="fas fa-calendar"></i> <?php echo format_date($entrada['fechaInicio']); ?></p>
                                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($entrada['ubicacion']); ?></p>
                                            <p><i class="fas fa-ticket-alt"></i> <?php echo htmlspecialchars($entrada['tipo_entrada_nombre']); ?></p>
                                            <?php if (!empty($entrada['zona_nombre'])): ?>
                                                <p><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($entrada['zona_nombre']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ticket-qr">
                                            <div class="qr-code">
                                                <i class="fas fa-qrcode" style="font-size: 60px;"></i>
                                            </div>
                                            <p class="qr-code-text"><?php echo htmlspecialchars($entrada['codigoQR']); ?></p>
                                        </div>
                                        <button class="btn-secondary">
                                            <i class="fas fa-download"></i> Descargar entrada
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-ticket-alt"></i>
                                <h3>No tienes entradas aún</h3>
                                <p>Explora eventos y compra tus primeras entradas</p>
                                <a href="search-events.php" class="btn-primary">Buscar eventos</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Historial de Compras -->
                    <div class="tab-content" id="historial">
                        <h1>Historial de Compras</h1>
                        
                        <?php if (!empty($compras)): ?>
                            <div class="purchases-list">
                                <?php foreach ($compras as $compra): ?>
                                    <div class="purchase-card">
                                        <div class="purchase-header">
                                            <div>
                                                <h3>Compra #<?php echo $compra['idCompra']; ?></h3>
                                                <p><?php echo format_date($compra['fechaCompra']); ?></p>
                                            </div>
                                            <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $compra['estado_compra'])); ?>">
                                                <?php echo htmlspecialchars($compra['estado_compra']); ?>
                                            </span>
                                        </div>
                                        <div class="purchase-details">
                                            <p><strong>Total:</strong> <?php echo format_price($compra['total']); ?></p>
                                            <p><strong>Método de pago:</strong> <?php echo htmlspecialchars($compra['metodo_pago']); ?></p>
                                            <p><strong>Entradas:</strong> <?php echo $compra['num_entradas']; ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-bag"></i>
                                <h3>No has realizado compras</h3>
                                <p>Tu historial de compras aparecerá aquí</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Mi Perfil nuevo -->
                    <div class="tab-content active" id="perfil">
    <h1>Mi Perfil</h1>
    
    <?php if (!empty($mensaje_perfil)): ?>
        <div class="alert alert-success"><?php echo $mensaje_perfil; ?></div>
    <?php endif; ?>

    <div class="content-grid">
        <!-- Tarjeta de perfil -->
        <div class="profile-card">
            <div class="card-header">
                <h2>Información Personal</h2>
                <button class="btn-edit" onclick="toggleEditMode()">
                    <i class="fas fa-edit"></i>
                    Editar
                </button>
            </div>
            <div class="card-body">
                <div class="profile-header">
                    <div class="avatar-large">
                        <i class="fas fa-user"></i>
                        <button class="avatar-upload">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <div class="profile-main">
                        <h3><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h3>
                        <p class="user-since">Miembro desde <?php echo date('F Y', strtotime($usuario['fechaRegistro'])); ?></p>
                        <div class="verification-badges">
                            <span class="badge verified">
                                <i class="fas fa-check-circle"></i>
                                Email verificado
                            </span>
                            <?php if (!empty($usuario['telefono'])): ?>
                            <span class="badge verified">
                                <i class="fas fa-mobile-alt"></i>
                                Teléfono verificado
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="" class="profile-form" id="profileForm">
                    <input type="hidden" name="actualizar_perfil" value="1">
                    
                    <div class="profile-details">
                        <div class="detail-group">
                            <label>Nombre completo</label>
                            <div class="detail-display">
                                <p><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></p>
                            </div>
                            <div class="detail-edit" style="display:none;">
                                <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                <input type="text" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="detail-group">
                            <label>Email</label>
                            <p><?php echo htmlspecialchars($usuario['email']); ?></p>
                            <small>El email no se puede cambiar</small>
                        </div>
                        
                        <div class="detail-group">
                            <label>Teléfono</label>
                            <div class="detail-display">
                                <p><?php echo htmlspecialchars($usuario['telefono'] ?? 'No especificado'); ?></p>
                            </div>
                            <div class="detail-edit" style="display:none;">
                                <input type="tel" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="edit-actions" style="display:none;">
                        <button type="submit" class="btn-primary">Guardar cambios</button>
                        <button type="button" class="btn-secondary" onclick="toggleEditMode()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-card">
            <h3>Tu actividad</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value"><?php echo count($entradas); ?></span>
                        <span class="stat-label">Entradas compradas</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value"><?php echo count($compras); ?></span>
                        <span class="stat-label">Compras realizadas</span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value">
                            <?php 
                            $total = array_sum(array_column($compras, 'total'));
                            echo format_price($total);
                            ?>
                        </span>
                        <span class="stat-label">Total gastado</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actividad reciente -->
        <div class="activity-card">
            <div class="card-header">
                <h3>Actividad Reciente</h3>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    <?php foreach (array_slice($compras, 0, 3) as $compra): ?>
                    <div class="activity-item">
                        <div class="activity-icon purchase">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="activity-content">
                            <p><strong>Compra realizada</strong></p>
                            <p class="activity-desc"><?php echo $compra['num_entradas']; ?> entradas</p>
                            <span class="activity-time"><?php echo time_ago($compra['fechaCompra']); ?></span>
                        </div>
                        <div class="activity-amount"><?php echo format_price($compra['total']); ?></div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($compras)): ?>
                    <p class="text-muted">No hay actividad reciente</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Tab Seguridad nuevo -->


                     <!-- Tab Seguridad
                      nuevo -->
<div class="tab-content" id="seguridad">
    <h1>Seguridad</h1>
    
    <div class="security-section">
        <!-- Contraseña -->
        <div class="security-card">
            <div class="card-header">
                <div>
                    <h3>Contraseña</h3>
                    <p>Última actualización hace 3 meses</p>
                </div>
                <button class="btn-primary">Cambiar contraseña</button>
            </div>
            <div class="card-body">
                <div class="password-strength">
                    <span>Fortaleza de la contraseña:</span>
                    <div class="strength-indicator strong">
                        <div class="strength-bar"></div>
                    </div>
                    <span class="strength-text">Fuerte</span>
                </div>
            </div>
        </div>

        <!-- Autenticación de dos factores -->
        <div class="security-card">
            <div class="card-header">
                <div>
                    <h3>Autenticación de dos factores</h3>
                    <p>Añade una capa extra de seguridad a tu cuenta</p>
                </div>
                <button class="btn-secondary">Configurar</button>
            </div>
            <div class="card-body">
                <div class="tfa-options">
                    <div class="tfa-option">
                        <i class="fas fa-mobile-alt"></i>
                        <div>
                            <strong>SMS</strong>
                            <p>Recibe un código en tu teléfono móvil</p>
                        </div>
                        <span class="status-badge">Inactivo</span>
                    </div>
                    <div class="tfa-option">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <strong>App de autenticación</strong>
                            <p>Usa Google Authenticator o similar</p>
                        </div>
                        <span class="status-badge">Inactivo</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sesiones activas -->
        <div class="security-card">
            <div class="card-header">
                <div>
                    <h3>Sesiones activas</h3>
                    <p>Dispositivos donde has iniciado sesión</p>
                </div>
                <button class="btn-danger">Cerrar todas</button>
            </div>
            <div class="card-body">
                <div class="sessions-list">
                    <div class="session-item current">
                        <div class="session-icon">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <div class="session-info">
                            <strong>Chrome en Windows</strong>
                            <p>Madrid, España • <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
                            <span class="session-time">Sesión actual</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seguridad de la cuenta -->
        <div class="security-card">
            <div class="card-header">
                <div>
                    <h3>Seguridad de la cuenta</h3>
                    <p>Registro de actividad sospechosa</p>
                </div>
            </div>
            <div class="card-body">
                <div class="security-log">
                    <div class="log-empty">
                        <i class="fas fa-check-circle"></i>
                        <p>No se ha detectado actividad sospechosa</p>
                        <span>Tu cuenta está segura</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Preferencias -->
<div class="tab-content" id="preferencias">
    <h1>Preferencias</h1>
    
    <div class="preferences-section">
        <!-- Notificaciones -->
        <div class="pref-card">
            <h3>Notificaciones por email</h3>
            <div class="pref-list">
                <div class="pref-item">
                    <div class="pref-info">
                        <strong>Confirmaciones de compra</strong>
                        <p>Recibe confirmación cuando compres entradas</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="pref-item">
                    <div class="pref-info">
                        <strong>Recordatorios de eventos</strong>
                        <p>Te avisamos 24h antes del evento</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="pref-item">
                    <div class="pref-info">
                        <strong>Ofertas y promociones</strong>
                        <p>Entérate de descuentos y eventos especiales</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="pref-item">
                    <div class="pref-info">
                        <strong>Newsletter mensual</strong>
                        <p>Los mejores eventos del mes en tu ciudad</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Preferencias de eventos -->
        <div class="pref-card">
            <h3>Preferencias de eventos</h3>
            <div class="categories-grid">
                <label class="category-checkbox">
                    <input type="checkbox" checked>
                    <span class="category-box">
                        <i class="fas fa-music"></i>
                        Música
                    </span>
                </label>
                <label class="category-checkbox">
                    <input type="checkbox" checked>
                    <span class="category-box">
                        <i class="fas fa-theater-masks"></i>
                        Teatro
                    </span>
                </label>
                <label class="category-checkbox">
                    <input type="checkbox">
                    <span class="category-box">
                        <i class="fas fa-futbol"></i>
                        Deportes
                    </span>
                </label>
                <label class="category-checkbox">
                    <input type="checkbox" checked>
                    <span class="category-box">
                        <i class="fas fa-palette"></i>
                        Arte
                    </span>
                </label>
                <label class="category-checkbox">
                    <input type="checkbox">
                    <span class="category-box">
                        <i class="fas fa-graduation-cap"></i>
                        Formación
                    </span>
                </label>
                <label class="category-checkbox">
                    <input type="checkbox" checked>
                    <span class="category-box">
                        <i class="fas fa-glass-cheers"></i>
                        Festivales
                    </span>
                </label>
            </div>
        </div>

        <!-- Privacidad -->
        <div class="pref-card">
            <h3>Privacidad</h3>
            <div class="pref-list">
                <div class="pref-item">
                    <div class="pref-info">
                        <strong>Perfil público</strong>
                        <p>Permite que otros usuarios vean tu perfil</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="pref-item">
                    <div class="pref-info">
                        <strong>Mostrar eventos asistidos</strong>
                        <p>Otros pueden ver a qué eventos has ido</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Zona de peligro -->
        <div class="pref-card danger-zone">
            <h3>Zona de peligro</h3>
            <div class="danger-actions">
                <div class="danger-item">
                    <div>
                        <strong>Desactivar cuenta</strong>
                        <p>Tu cuenta se desactivará temporalmente</p>
                    </div>
                    <button class="btn-outline-danger">Desactivar</button>
                </div>
                <div class="danger-item">
                    <div>
                        <strong>Eliminar cuenta</strong>
                        <p>Esta acción no se puede deshacer</p>
                    </div>
                    <button class="btn-danger">Eliminar cuenta</button>
                </div>
            </div>
        </div>
    </div>
</div>   
<!-- nuevo -->

                    <!-- Mis Eventos (solo organizadores) -->
                    <div class="tab-content" id="mis-eventos">
                        <h1>Mis Eventos</h1>
                        <p>Próximamente: Panel de gestión de eventos</p>
                        <a href="#" class="btn-primary">Crear nuevo evento</a>
                    </div>
                    
                </div>
            </div>
        </div>
    </main>

    <script>
function showTab(event, tabId) {
    event.preventDefault();
    
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Desactivar todos los links
    document.querySelectorAll('.account-nav a').forEach(link => {
        link.classList.remove('active');
    });
    
    // Mostrar tab seleccionado
    document.getElementById(tabId).classList.add('active');
    
    // Activar link correspondiente
    event.target.closest('a').classList.add('active');
}

function toggleEditMode() {
    const displays = document.querySelectorAll('.detail-display');
    const edits = document.querySelectorAll('.detail-edit');
    const actions = document.querySelector('.edit-actions');
    const btn = document.querySelector('.btn-edit');
    
    displays.forEach(d => d.style.display = d.style.display === 'none' ? 'block' : 'none');
    edits.forEach(e => e.style.display = e.style.display === 'none' ? 'block' : 'none');
    actions.style.display = actions.style.display === 'none' ? 'flex' : 'none';
}
</script>
</body>
</html>
