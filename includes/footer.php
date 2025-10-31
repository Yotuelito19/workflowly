<?php
/**
 * Footer compartido de la aplicación
 */
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/footer.css">
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <div class="logo">
                    <div class="logo-circle">
                        <span>W</span>
                    </div>
                    <span class="brand-name">WorkFlowly</span>
                </div>
                <p>Tu plataforma de confianza para comprar entradas sin reventa.</p>
            </div>

            <div class="footer-section">
                <h4>Enlaces</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/index.php">Inicio</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/views/search-events.php">Buscar Eventos</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/index.php#como-funciona">Cómo funciona</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Legal</h4>
                <ul>
                    <li><a href="#">Términos y Condiciones</a></li>
                    <li><a href="#">Política de Privacidad</a></li>
                    <li><a href="#">Política de Cookies</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Síguenos</h4>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> WorkFlowly. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>
