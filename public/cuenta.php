<?php
/**
 * WorkFlowly - Cuenta de Usuario
 * Conversión de account.html a PHP
 */

// Iniciar sesión
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php?redirect=cuenta.php');
    exit;
}

// Incluir configuración
require_once '../config/database.php';
require_once '../app/controllers/UsuarioController.php';
require_once '../app/controllers/CompraController.php';

// Crear instancias
$usuarioController = new UsuarioController();
$compraController = new CompraController();

// Obtener datos actualizados del usuario
$usuario = $usuarioController->getUsuarioById($_SESSION['usuario']['id']);

// Actualizar sesión con datos frescos
$_SESSION['usuario'] = $usuario;

// Obtener compras recientes
$comprasRecientes = $compraController->getComprasByUsuario($usuario['idUsuario'], 5);

// Obtener estadísticas
$totalCompras = $compraController->getTotalComprasUsuario($usuario['idUsuario']);
$totalGastado = $compraController->getTotalGastadoUsuario($usuario['idUsuario']);

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $resultado = $usuarioController->actualizarPerfil(
        $usuario['idUsuario'],
        $_POST
    );
    
    if ($resultado['success']) {
        $successMessage = 'Perfil actualizado correctamente';
        // Recargar datos
        $usuario = $usuarioController->getUsuarioById($usuario['idUsuario']);
        $_SESSION['usuario'] = $usuario;
    } else {
        $errorMessage = $resultado['mensaje'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - WorkFlowly</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/account.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav-brand">
                <a href="index.php" class="logo">
                    <div class="logo-circle">W</div>
                    <span class="brand-name">WorkFlowly</span>
                </a>
            </nav>
            
            <div class="nav-menu">
                <a href="index.php">Inicio</a>
                <a href="eventos.php">Busca Eventos</a>
                <a href="#organizadores">Organizadores</a>
            </div>
            
            <div class="nav-actions">
                <a href="cuenta.php" class="btn-secondary active">Mi Cuenta</a>
                <a href="../app/controllers/AuthController.php?action=logout" class="btn-primary">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <!-- Account Content -->
    <section class="account-section">
        <div class="container">
            <div class="account-layout">
                <!-- Sidebar -->
                <aside class="account-sidebar">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h3>
                        <p class="user-email"><?php echo htmlspecialchars($usuario['email']); ?></p>
                        <span class="user-badge">
                            <?php echo htmlspecialchars($usuario['tipoUsuario']); ?>
                        </span>
                    </div>

                    <nav class="account-nav">
                        <a href="#perfil" class="nav-item active" onclick="showSection('perfil')">
                            👤 Mi Perfil
                        </a>
                        <a href="#entradas" class="nav-item" onclick="showSection('entradas')">
                            🎫 Mis Entradas
                        </a>
                        <a href="#compras" class="nav-item" onclick="showSection('compras')">
                            📦 Historial de Compras
                        </a>
                        <a href="#favoritos" class="nav-item" onclick="showSection('favoritos')">
                            ❤️ Eventos Favoritos
                        </a>
                        <?php if ($usuario['tipoUsuario'] === 'Organizador'): ?>
                            <a href="admin/index.php" class="nav-item nav-admin">
                                ⚙️ Panel Organizador
                            </a>
                        <?php endif; ?>
                        <a href="#seguridad" class="nav-item" onclick="showSection('seguridad')">
                            🔒 Seguridad
                        </a>
                    </nav>

                    <div class="account-stats">
                        <div class="stat-item">
                            <strong><?php echo $totalCompras; ?></strong>
                            <span>Compras realizadas</span>
                        </div>
                        <div class="stat-item">
                            <strong>€<?php echo number_format($totalGastado, 2); ?></strong>
                            <span>Total gastado</span>
                        </div>
                    </div>
                </aside>

                <!-- Main Content -->
                <main class="account-main">
                    <?php if (isset($successMessage)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($successMessage); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($errorMessage)): ?>
                        <div class="alert alert-error">
                            <?php echo htmlspecialchars($errorMessage); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Sección: Mi Perfil -->
                    <div id="section-perfil" class="account-section-content active">
                        <h2>Mi Perfil</h2>
                        
                        <form action="" method="POST" class="profile-form">
                            <input type="hidden" name="actualizar_perfil" value="1">
                            
                            <div class="form-section">
                                <h3>Información Personal</h3>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="nombre">Nombre</label>
                                        <input type="text" 
                                               id="nombre" 
                                               name="nombre" 
                                               value="<?php echo htmlspecialchars($usuario['nombre']); ?>"
                                               required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="apellidos">Apellidos</label>
                                        <input type="text" 
                                               id="apellidos" 
                                               name="apellidos" 
                                               value="<?php echo htmlspecialchars($usuario['apellidos']); ?>"
                                               required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           value="<?php echo htmlspecialchars($usuario['email']); ?>"
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="tel" 
                                           id="telefono" 
                                           name="telefono" 
                                           value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-primary">
                                    Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Sección: Mis Entradas -->
                    <div id="section-entradas" class="account-section-content">
                        <h2>Mis Entradas</h2>
                        
                        <div class="quick-filters">
                            <button class="filter-btn active">Próximas</button>
                            <button class="filter-btn">Pasadas</button>
                            <button class="filter-btn">Todas</button>
                        </div>

                        <div class="tickets-grid">
                            <p class="empty-state">
                                No tienes entradas próximas. 
                                <a href="eventos.php">Buscar eventos</a>
                            </p>
                        </div>
                    </div>

                    <!-- Sección: Historial de Compras -->
                    <div id="section-compras" class="account-section-content">
                        <h2>Historial de Compras</h2>
                        
                        <?php if (!empty($comprasRecientes)): ?>
                            <div class="purchases-list">
                                <?php foreach ($comprasRecientes as $compra): ?>
                                    <div class="purchase-card">
                                        <div class="purchase-header">
                                            <div class="purchase-info">
                                                <h4>Pedido #<?php echo str_pad($compra['idCompra'], 6, '0', STR_PAD_LEFT); ?></h4>
                                                <p class="purchase-date">
                                                    <?php echo date('d/m/Y H:i', strtotime($compra['fechaCompra'])); ?>
                                                </p>
                                            </div>
                                            <div class="purchase-status">
                                                <span class="status-badge status-<?php echo strtolower($compra['estado']); ?>">
                                                    <?php echo htmlspecialchars($compra['estado']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="purchase-details">
                                            <p class="event-name">
                                                <?php echo htmlspecialchars($compra['evento']['nombre']); ?>
                                            </p>
                                            <p class="purchase-items">
                                                <?php echo $compra['cantidadEntradas']; ?> entrada(s)
                                            </p>
                                        </div>
                                        
                                        <div class="purchase-footer">
                                            <span class="purchase-total">
                                                €<?php echo number_format($compra['total'], 2); ?>
                                            </span>
                                            <a href="confirmacion.php?compra=<?php echo $compra['idCompra']; ?>" 
                                               class="btn-secondary btn-small">
                                                Ver Detalles
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="empty-state">
                                No tienes compras aún. 
                                <a href="eventos.php">Explorar eventos</a>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Sección: Eventos Favoritos -->
                    <div id="section-favoritos" class="account-section-content">
                        <h2>Eventos Favoritos</h2>
                        
                        <div class="favorites-grid">
                            <p class="empty-state">
                                No tienes eventos favoritos aún. 
                                <a href="eventos.php">Descubrir eventos</a>
                            </p>
                        </div>
                    </div>

                    <!-- Sección: Seguridad -->
                    <div id="section-seguridad" class="account-section-content">
                        <h2>Seguridad</h2>
                        
                        <div class="security-section">
                            <h3>Cambiar Contraseña</h3>
                            
                            <form action="" method="POST" class="security-form">
                                <input type="hidden" name="cambiar_password" value="1">
                                
                                <div class="form-group">
                                    <label for="password_actual">Contraseña Actual</label>
                                    <input type="password" 
                                           id="password_actual" 
                                           name="password_actual"
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password_nueva">Nueva Contraseña</label>
                                    <input type="password" 
                                           id="password_nueva" 
                                           name="password_nueva"
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password_confirmar">Confirmar Nueva Contraseña</label>
                                    <input type="password" 
                                           id="password_confirmar" 
                                           name="password_confirmar"
                                           required>
                                </div>
                                
                                <button type="submit" class="btn-primary">
                                    Actualizar Contraseña
                                </button>
                            </form>
                        </div>

                        <div class="danger-zone">
                            <h3>Zona Peligrosa</h3>
                            <p>Estas acciones son permanentes y no se pueden deshacer.</p>
                            <button class="btn-danger">Eliminar Cuenta</button>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 WorkFlowly. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        function showSection(sectionName) {
            // Ocultar todas las secciones
            const sections = document.querySelectorAll('.account-section-content');
            sections.forEach(section => section.classList.remove('active'));
            
            // Desactivar todos los nav items
            const navItems = document.querySelectorAll('.account-nav .nav-item');
            navItems.forEach(item => item.classList.remove('active'));
            
            // Mostrar sección seleccionada
            document.getElementById('section-' + sectionName).classList.add('active');
            
            // Activar nav item
            event.target.classList.add('active');
            
            // Actualizar URL
            window.history.replaceState({}, '', '#' + sectionName);
        }

        // Cargar sección desde hash al iniciar
        window.addEventListener('load', function() {
            const hash = window.location.hash.substring(1);
            if (hash) {
                const navItem = document.querySelector(`[onclick="showSection('${hash}')"]`);
                if (navItem) {
                    navItem.click();
                }
            }
        });
    </script>
</body>
</html>
