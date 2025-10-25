<?php
/**
 * Footer compartido de la aplicación
 */
?>
<footer class="main-footer">
    <div class="container footer-content">
        <div class="footer-section">
            <h3>WorkFlowly</h3>
            <p>Tu plataforma de confianza para comprar entradas a los mejores eventos.</p>
        </div>

        <div class="footer-section">
            <h4>Enlaces rápidos</h4>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/index.php">Inicio</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/search-events.php">Buscar Eventos</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/about.php">Sobre Nosotros</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/contact.php">Contacto</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h4>Legal</h4>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/views/terms.php">Términos y Condiciones</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/privacy.php">Política de Privacidad</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/cookies.php">Política de Cookies</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h4>Síguenos</h4>
            <div class="social-links">
                <a href="#" aria-label="Facebook">📘</a>
                <a href="#" aria-label="Twitter">🐦</a>
                <a href="#" aria-label="Instagram">📷</a>
                <a href="#" aria-label="LinkedIn">💼</a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> WorkFlowly. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>
