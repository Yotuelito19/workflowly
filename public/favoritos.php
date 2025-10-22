<?php
/**
 * WorkFlowly - Eventos Favoritos
 */

// Iniciar sesión
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php?redirect=favoritos.php');
    exit;
}

// Incluir configuración
require_once '../config/database.php';
require_once '../app/controllers/EventoController.php';

// Crear instancia del controlador
$eventoController = new EventoController();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['eliminar_favorito'])) {
        $idEvento = intval($_POST['id_evento']);
        $eventoController->eliminarFavorito($_SESSION['usuario']['id'], $idEvento);
        header('Location: favoritos.php');
        exit;
    }
}

// Obtener eventos favoritos
$favoritos = $eventoController->getFavoritosByUsuario($_SESSION['usuario']['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos Favoritos - WorkFlowly</title>
    
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
                <a href="cuenta.php" class="btn-secondary">Mi Cuenta</a>
                <a href="../app/controllers/AuthController.php?action=logout" class="btn-primary">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Inicio</a> / 
            <a href="cuenta.php">Mi Cuenta</a> / 
            <span>Favoritos</span>
        </div>
    </div>

    <!-- Favorites Content -->
    <section class="favorites-section">
        <div class="container">
            <div class="favorites-header">
                <h1>Mis Eventos Favoritos</h1>
                <p class="favorites-count">
                    <?php echo count($favoritos); ?> evento(s) guardado(s)
                </p>
            </div>

            <?php if (!empty($favoritos)): ?>
                <div class="favorites-grid">
                    <?php foreach ($favoritos as $favorito): ?>
                        <div class="favorite-card">
                            <button class="favorite-remove" 
                                    onclick="eliminarFavorito(<?php echo $favorito['idEvento']; ?>)"
                                    title="Eliminar de favoritos">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                            </button>

                            <div class="event-image">
                                <img src="<?php echo htmlspecialchars($favorito['imagenPrincipal']); ?>" 
                                     alt="<?php echo htmlspecialchars($favorito['nombre']); ?>">
                                <span class="event-badge"><?php echo htmlspecialchars($favorito['tipo']); ?></span>
                            </div>

                            <div class="event-info">
                                <h3><?php echo htmlspecialchars($favorito['nombre']); ?></h3>
                                
                                <p class="event-location">
                                    <?php echo htmlspecialchars($favorito['ubicacion']); ?>
                                </p>
                                
                                <p class="event-date">
                                    <?php echo date('d/m/Y H:i', strtotime($favorito['fechaInicio'])); ?>
                                </p>

                                <div class="event-stats">
                                    <span class="stat-item">
                                        <?php echo $favorito['entradasDisponibles']; ?> disponibles
                                    </span>
                                </div>

                                <div class="event-footer">
                                    <span class="event-price">
                                        Desde <strong><?php echo number_format($favorito['precioMinimo'], 2); ?> EUR</strong>
                                    </span>
                                    <a href="detalle-evento.php?id=<?php echo $favorito['idEvento']; ?>" 
                                       class="btn-primary">
                                        Ver detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="100" height="100" viewBox="0 0 100 100">
                            <path d="M50 85 L30 70 C15 60 10 50 10 40 C10 25 20 15 32.5 15 C40 15 47 19 50 25 C53 19 60 15 67.5 15 C80 15 90 25 90 40 C90 50 85 60 70 70 Z" 
                                  fill="#e0e0e0"/>
                        </svg>
                    </div>
                    <h3>No tienes eventos favoritos</h3>
                    <p>Guarda eventos que te interesen para encontrarlos fácilmente después.</p>
                    <a href="eventos.php" class="btn-primary">Explorar Eventos</a>
                </div>
            <?php endif; ?>
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

    <!-- Form oculto para eliminar favoritos -->
    <form id="form-eliminar-favorito" method="POST" style="display: none;">
        <input type="hidden" name="eliminar_favorito" value="1">
        <input type="hidden" name="id_evento" id="id-evento-eliminar">
    </form>

    <script>
        function eliminarFavorito(idEvento) {
            if (confirm('¿Eliminar este evento de favoritos?')) {
                document.getElementById('id-evento-eliminar').value = idEvento;
                document.getElementById('form-eliminar-favorito').submit();
            }
        }
    </script>
</body>
</html>
