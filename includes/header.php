<?php
/**
 * Header compartido de la aplicación
 */
?>
<header class="main-header">
    <nav class="navbar">
        <div class="container nav-container">
            <a href="<?php echo BASE_URL; ?>/index.php" class="logo">
                <h1>WorkFlowly</h1>
            </a>

            <ul class="nav-menu">
                <li><a href="<?php echo BASE_URL; ?>/index.php">Inicio</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/search-events.php">Eventos</a></li>
                <?php if (is_logged_in()): ?>
                    <?php if (is_organizer()): ?>
                        <li><a href="<?php echo BASE_URL; ?>/views/organizer-dashboard.php">Mis Eventos</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo BASE_URL; ?>/views/account.php">Mi Cuenta</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/api/logout.php">Cerrar Sesión</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>/views/login.php">Iniciar Sesión</a></li>
                <?php endif; ?>
            </ul>

            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>
</header>
