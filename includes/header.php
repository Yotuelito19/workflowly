<?php
/**
 * Header compartido de la aplicación
 */
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/header.css">
<header class="header">
    <div class="container">
        <div class="nav-brand">
            <a href="<?php echo BASE_URL; ?>/index.php" class="logo">
                <div class="logo-circle">
                    <span>W</span>
                </div>
                <span class="brand-name">WorkFlowly</span>
            </a>
        </div>
        <nav class="nav-menu">
            <a href="<?php echo BASE_URL; ?>/views/search-events.php">Eventos</a>
            <a href="<?php echo BASE_URL; ?>/index.php#como-funciona">Cómo funciona</a>
            <?php if (is_admin()): ?>
                <a href="<?php echo BASE_URL; ?>/views/admin/events.php">Gestor</a>
            <?php endif; ?>
        </nav>
        <div class="nav-actions">
            <?php if (is_logged_in()): ?>
                <a href="<?php echo BASE_URL; ?>/views/account.php" class="btn-secondary">Mi Cuenta</a>
                <a href="<?php echo BASE_URL; ?>/api/logout.php" class="btn-primary">Cerrar Sesión</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/views/login.php" class="btn-secondary">Iniciar Sesión</a>
                <a href="<?php echo BASE_URL; ?>/views/login.php" class="btn-primary">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
</header>
