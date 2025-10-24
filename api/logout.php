<?php
/**
 * API - Logout de usuario
 */

require_once '../config/config.php';

session_unset();
session_destroy();

redirect('/views/login.php?logout=1');
?>
