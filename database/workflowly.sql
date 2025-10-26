-- ============================================================
-- Script para crear la base de datos WorkFlowly
-- VERSIÓN PORTABLE - Funciona en cualquier fecha
-- Sistema de venta y gestión de entradas para eventos
-- ============================================================

-- Creación de la base de datos
DROP DATABASE IF EXISTS workflowly;
CREATE DATABASE workflowly DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE workflowly;

-- ============================================================
-- TABLAS
-- ============================================================

-- Tabla Estado (debe crearse primero por ser referenciada por muchas tablas)
CREATE TABLE Estado (
    idEstado INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255),
    tipoEntidad VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- Insertar estados básicos del sistema
INSERT INTO Estado (nombre, descripcion, tipoEntidad) VALUES
('Activo', 'Elemento activo y disponible', 'General'),
('Inactivo', 'Elemento inactivo o deshabilitado', 'General'),
('Pendiente', 'Elemento en espera de procesamiento', 'General'),
('Completado', 'Elemento procesado completamente', 'General'),
('Cancelado', 'Elemento cancelado', 'General'),
('Disponible', 'Elemento disponible para su uso', 'Asiento'),
('Reservado', 'Elemento reservado temporalmente', 'Asiento'),
('Vendido', 'Elemento vendido', 'Asiento'),
('Pagado', 'Pago completado', 'Compra'),
('Pendiente de pago', 'Pago en proceso', 'Compra'),
('Verificado', 'Elemento verificado', 'Entrada'),
('No verificado', 'Elemento sin verificar', 'Entrada');

-- Tabla Usuario
CREATE TABLE Usuario (
    idUsuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    fechaRegistro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipoUsuario ENUM('NoRegistrado', 'Comprador', 'Organizador') NOT NULL DEFAULT 'NoRegistrado',
    idEstadoUsuario INT NOT NULL,
    FOREIGN KEY (idEstadoUsuario) REFERENCES Estado(idEstado)
) ENGINE=InnoDB;

-- Tabla Evento
CREATE TABLE Evento (
    idEvento INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    tipo VARCHAR(50) NOT NULL,
    fechaInicio DATETIME NOT NULL,
    fechaFin DATETIME NOT NULL,
    ubicacion VARCHAR(255) NOT NULL,
    aforoTotal INT NOT NULL,
    entradasDisponibles INT NOT NULL,
    imagenPrincipal VARCHAR(255) NOT NULL DEFAULT 'imagen/default.jpg',
    idEstadoEvento INT,
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario) ON DELETE CASCADE,
    FOREIGN KEY (idEstadoEvento) REFERENCES Estado(idEstado) ON DELETE SET NULL,
    CHECK (fechaFin > fechaInicio),
    CHECK (entradasDisponibles <= aforoTotal)
) ENGINE=InnoDB;

-- Tabla TipoEntrada
CREATE TABLE TipoEntrada (
    idTipoEntrada INT AUTO_INCREMENT PRIMARY KEY,
    idEvento INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    cantidadDisponible INT NOT NULL,
    fechaInicioVenta DATETIME NOT NULL,
    fechaFinVenta DATETIME NOT NULL,
    FOREIGN KEY (idEvento) REFERENCES Evento(idEvento),
    CHECK (fechaFinVenta > fechaInicioVenta),
    CHECK (cantidadDisponible >= 0),
    CHECK (precio >= 0)
) ENGINE=InnoDB;

-- Tabla Zona
CREATE TABLE Zona (
    idZona INT AUTO_INCREMENT PRIMARY KEY,
    idEvento INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    capacidad INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    FOREIGN KEY (idEvento) REFERENCES Evento(idEvento),
    CHECK (capacidad > 0)
) ENGINE=InnoDB;

-- Tabla de relación entre TipoEntrada y Zona
CREATE TABLE TipoEntradaZona (
    idTipoEntradaZona INT AUTO_INCREMENT PRIMARY KEY,
    idTipoEntrada INT NOT NULL,
    idZona INT NOT NULL,
    FOREIGN KEY (idTipoEntrada) REFERENCES TipoEntrada(idTipoEntrada),
    FOREIGN KEY (idZona) REFERENCES Zona(idZona),
    UNIQUE KEY unique_tipo_zona (idTipoEntrada, idZona)
) ENGINE=InnoDB;

-- Tabla Asiento
CREATE TABLE Asiento (
    idAsiento INT AUTO_INCREMENT PRIMARY KEY,
    idZona INT NOT NULL,
    fila VARCHAR(10) NOT NULL,
    numero INT NOT NULL,
    idEstadoAsiento INT NOT NULL,
    FOREIGN KEY (idZona) REFERENCES Zona(idZona),
    FOREIGN KEY (idEstadoAsiento) REFERENCES Estado(idEstado),
    UNIQUE KEY unique_asiento_zona (idZona, fila, numero)
) ENGINE=InnoDB;

-- Tabla MetodoPago
CREATE TABLE MetodoPago (
    idMetodoPago INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    tipo ENUM('Tarjeta', 'PayPal', 'Bizum', 'Otro') NOT NULL,
    tokenReferencia VARCHAR(255),
    nombreTitular VARCHAR(100),
    fechaExpiracion DATE,
    esPredeterminado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario)
) ENGINE=InnoDB;

-- Tabla Compra
CREATE TABLE Compra (
    idCompra INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    idMetodoPago INT NOT NULL,
    fechaCompra DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    referenciaPago VARCHAR(100),
    idEstadoCompra INT NOT NULL,
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario),
    FOREIGN KEY (idMetodoPago) REFERENCES MetodoPago(idMetodoPago),
    FOREIGN KEY (idEstadoCompra) REFERENCES Estado(idEstado),
    CHECK (total >= 0)
) ENGINE=InnoDB;

-- Tabla DetalleCompra
CREATE TABLE DetalleCompra (
    idDetalleCompra INT AUTO_INCREMENT PRIMARY KEY,
    idCompra INT NOT NULL,
    idTipoEntrada INT NOT NULL,
    idAsiento INT,
    cantidad INT NOT NULL DEFAULT 1,
    precioUnitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (idCompra) REFERENCES Compra(idCompra),
    FOREIGN KEY (idTipoEntrada) REFERENCES TipoEntrada(idTipoEntrada),
    FOREIGN KEY (idAsiento) REFERENCES Asiento(idAsiento),
    CHECK (cantidad > 0),
    CHECK (precioUnitario >= 0),
    CHECK (subtotal = cantidad * precioUnitario)
) ENGINE=InnoDB;

-- Tabla Entrada
CREATE TABLE Entrada (
    idEntrada INT AUTO_INCREMENT PRIMARY KEY,
    idDetalleCompra INT NOT NULL,
    idAsiento INT,
    codigoBarras VARCHAR(100) UNIQUE,
    codigoQR VARCHAR(255) UNIQUE,
    fechaGeneracion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fechaValidacion DATETIME,
    idEstadoEntrada INT NOT NULL,
    FOREIGN KEY (idDetalleCompra) REFERENCES DetalleCompra(idDetalleCompra),
    FOREIGN KEY (idAsiento) REFERENCES Asiento(idAsiento),
    FOREIGN KEY (idEstadoEntrada) REFERENCES Estado(idEstado),
    CHECK (fechaValidacion IS NULL OR fechaValidacion >= fechaGeneracion)
) ENGINE=InnoDB;

-- Tabla FavoritoEvento
CREATE TABLE FavoritoEvento (
    idFavoritoEvento INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL,
    idEvento INT NOT NULL,
    fechaAgregado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario),
    FOREIGN KEY (idEvento) REFERENCES Evento(idEvento),
    UNIQUE KEY unique_usuario_evento (idUsuario, idEvento)
) ENGINE=InnoDB;

-- Tabla CodigoPromocion
CREATE TABLE CodigoPromocion (
    idCodigo INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    tipoDescuento ENUM('Porcentaje', 'MontoFijo') NOT NULL,
    valorDescuento DECIMAL(10,2) NOT NULL,
    fechaInicio DATETIME NOT NULL,
    fechaFin DATETIME NOT NULL,
    usoMaximo INT,
    idEvento INT NOT NULL,
    idEstadoCodigo INT NOT NULL,
    FOREIGN KEY (idEvento) REFERENCES Evento(idEvento),
    FOREIGN KEY (idEstadoCodigo) REFERENCES Estado(idEstado),
    CHECK (fechaFin > fechaInicio),
    CHECK (valorDescuento > 0)
) ENGINE=InnoDB;

-- ============================================================
-- ÍNDICES
-- ============================================================

CREATE INDEX idx_evento_fecha ON Evento(fechaInicio, fechaFin);
CREATE INDEX idx_tipoentrada_precio ON TipoEntrada(precio);
CREATE INDEX idx_compra_fecha ON Compra(fechaCompra);
CREATE INDEX idx_entrada_validacion ON Entrada(fechaValidacion);
CREATE INDEX idx_codigo_fecha ON CodigoPromocion(fechaInicio, fechaFin);

-- ============================================================
-- TRIGGERS
-- ============================================================

-- Actualizar entradasDisponibles en Evento cuando se crea un TipoEntrada
DELIMITER //
CREATE TRIGGER after_tipoentrada_insert
AFTER INSERT ON TipoEntrada
FOR EACH ROW
BEGIN
    UPDATE Evento SET entradasDisponibles = entradasDisponibles + NEW.cantidadDisponible
    WHERE idEvento = NEW.idEvento;
END //
DELIMITER ;

-- Actualizar entradasDisponibles en Evento cuando se modifica un TipoEntrada
DELIMITER //
CREATE TRIGGER after_tipoentrada_update
AFTER UPDATE ON TipoEntrada
FOR EACH ROW
BEGIN
    UPDATE Evento SET entradasDisponibles = entradasDisponibles + (NEW.cantidadDisponible - OLD.cantidadDisponible)
    WHERE idEvento = NEW.idEvento;
END //
DELIMITER ;

-- Actualizar cantidadDisponible en TipoEntrada después de una compra
DELIMITER //
CREATE TRIGGER after_detallecompra_insert
AFTER INSERT ON DetalleCompra
FOR EACH ROW
BEGIN
    UPDATE TipoEntrada SET cantidadDisponible = cantidadDisponible - NEW.cantidad
    WHERE idTipoEntrada = NEW.idTipoEntrada;
END //
DELIMITER ;

-- Actualizar estado de Asiento después de asignarlo a una entrada
DELIMITER //
CREATE TRIGGER after_entrada_insert
AFTER INSERT ON Entrada
FOR EACH ROW
BEGIN
    IF NEW.idAsiento IS NOT NULL THEN
        UPDATE Asiento SET idEstadoAsiento = (SELECT idEstado FROM Estado WHERE nombre = 'Vendido' AND tipoEntidad = 'Asiento')
        WHERE idAsiento = NEW.idAsiento;
    END IF;
END //
DELIMITER ;

-- Asegurarse de que el subtotal en DetalleCompra sea correcto
DELIMITER //
CREATE TRIGGER before_detallecompra_insert
BEFORE INSERT ON DetalleCompra
FOR EACH ROW
BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precioUnitario;
END //
DELIMITER ;

-- ============================================================
-- PROCEDIMIENTOS ALMACENADOS
-- ============================================================

-- Procedimiento para crear un nuevo evento completo
DELIMITER //
CREATE PROCEDURE crear_evento(
    IN p_id_usuario INT,
    IN p_nombre VARCHAR(200),
    IN p_descripcion TEXT,
    IN p_tipo VARCHAR(50),
    IN p_fecha_inicio DATETIME,
    IN p_fecha_fin DATETIME,
    IN p_ubicacion VARCHAR(255),
    IN p_aforo_total INT,
    IN p_imagen VARCHAR(255),
    OUT p_id_evento INT
)
BEGIN
    DECLARE v_estado_id INT;
    
    -- Obtener el ID del estado "Activo"
    SELECT idEstado INTO v_estado_id FROM Estado WHERE nombre = 'Activo' AND tipoEntidad = 'General' LIMIT 1;
    
    -- Insertar el evento
    INSERT INTO Evento (
        idUsuario, nombre, descripcion, tipo, fechaInicio, fechaFin,
        ubicacion, aforoTotal, entradasDisponibles, imagenPrincipal, idEstadoEvento
    ) VALUES (
        p_id_usuario, p_nombre, p_descripcion, p_tipo, p_fecha_inicio, p_fecha_fin,
        p_ubicacion, p_aforo_total, 0, p_imagen, v_estado_id
    );
    
    -- Devolver el ID del evento creado
    SET p_id_evento = LAST_INSERT_ID();
END //
DELIMITER ;

-- Procedimiento para realizar una compra completa
DELIMITER //
CREATE PROCEDURE realizar_compra(
    IN p_id_usuario INT,
    IN p_id_metodo_pago INT,
    IN p_total DECIMAL(10,2),
    IN p_referencia_pago VARCHAR(100),
    OUT p_id_compra INT
)
BEGIN
    DECLARE v_estado_id INT;
    
    -- Obtener el ID del estado "Pendiente de pago"
    SELECT idEstado INTO v_estado_id FROM Estado WHERE nombre = 'Pendiente de pago' AND tipoEntidad = 'Compra' LIMIT 1;
    
    -- Insertar la compra
    INSERT INTO Compra (
        idUsuario, idMetodoPago, fechaCompra, total, referenciaPago, idEstadoCompra
    ) VALUES (
        p_id_usuario, p_id_metodo_pago, NOW(), p_total, p_referencia_pago, v_estado_id
    );
    
    -- Devolver el ID de la compra creada
    SET p_id_compra = LAST_INSERT_ID();
END //
DELIMITER ;

-- ============================================================
-- VISTAS
-- ============================================================

-- Vista para mostrar eventos disponibles con sus tipos de entrada
CREATE VIEW vw_eventos_disponibles AS
SELECT 
    e.idEvento,
    e.nombre,
    e.descripcion,
    e.fechaInicio,
    e.fechaFin,
    e.ubicacion,
    e.entradasDisponibles,
    MIN(te.precio) AS precioDesde,
    u.nombre AS organizador,
    COUNT(DISTINCT te.idTipoEntrada) AS tiposEntrada
FROM 
    Evento e
    JOIN Usuario u ON e.idUsuario = u.idUsuario
    LEFT JOIN TipoEntrada te ON e.idEvento = te.idEvento
    JOIN Estado es ON e.idEstadoEvento = es.idEstado
WHERE 
    es.nombre = 'Activo'
    AND e.fechaFin > NOW()
    AND e.entradasDisponibles > 0
GROUP BY 
    e.idEvento;

-- Vista para mostrar entradas por usuario
CREATE VIEW vw_entradas_usuario AS
SELECT 
    u.idUsuario,
    u.nombre AS nombreUsuario,
    e.idEntrada,
    ev.nombre AS nombreEvento,
    ev.fechaInicio,
    ev.ubicacion,
    te.nombre AS tipoEntrada,
    dc.precioUnitario,
    CONCAT(IFNULL(a.fila, ''), ' ', IFNULL(a.numero, '')) AS ubicacionAsiento,
    es.nombre AS estado
FROM 
    Usuario u
    JOIN Compra c ON u.idUsuario = c.idUsuario
    JOIN DetalleCompra dc ON c.idCompra = dc.idCompra
    JOIN Entrada e ON dc.idDetalleCompra = e.idDetalleCompra
    JOIN TipoEntrada te ON dc.idTipoEntrada = te.idTipoEntrada
    JOIN Evento ev ON te.idEvento = ev.idEvento
    LEFT JOIN Asiento a ON e.idAsiento = a.idAsiento
    JOIN Estado es ON e.idEstadoEntrada = es.idEstado;

-- Vista para verificar la validez de una entrada
CREATE VIEW vw_validacion_entrada AS
SELECT 
    e.idEntrada,
    e.codigoBarras,
    e.codigoQR,
    CASE 
        WHEN e.fechaValidacion IS NOT NULL THEN 'Ya validada'
        WHEN es.nombre = 'Cancelado' THEN 'Cancelada'
        WHEN ev.fechaFin < NOW() THEN 'Evento finalizado'
        ELSE 'Válida'
    END AS estadoValidacion,
    ev.nombre AS evento,
    ev.fechaInicio,
    ev.fechaFin,
    te.nombre AS tipoEntrada,
    CONCAT(a.fila, ' ', a.numero) AS asiento
FROM 
    Entrada e
    JOIN DetalleCompra dc ON e.idDetalleCompra = dc.idDetalleCompra
    JOIN TipoEntrada te ON dc.idTipoEntrada = te.idTipoEntrada
    JOIN Evento ev ON te.idEvento = ev.idEvento
    LEFT JOIN Asiento a ON e.idAsiento = a.idAsiento
    JOIN Estado es ON e.idEstadoEntrada = es.idEstado;

-- ============================================================
-- DATOS DE PRUEBA
-- ============================================================

-- Usuario administrador de prueba
-- Email: admin@workflowly.com | Password: 12345678
INSERT INTO Usuario (nombre, apellidos, email, password, tipoUsuario, idEstadoUsuario)
VALUES ('Admin', 'WorkFlowly', 'admin@workflowly.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Organizador', 1);

SET @adminId = LAST_INSERT_ID();

-- Eventos de prueba con fechas dinámicas (siempre en el futuro)
-- Las fechas se calculan a partir de NOW() para que funcionen en cualquier momento
INSERT INTO Evento (idUsuario, nombre, descripcion, tipo, fechaInicio, fechaFin, ubicacion, aforoTotal, entradasDisponibles, imagenPrincipal, idEstadoEvento)
VALUES 
-- Evento en 2 meses (Concierto Rock)
(@adminId, 'Concierto Rock Madrid 2026', 
 'Gran noche de rock con las mejores bandas nacionales e internacionales. Una experiencia inolvidable con los grupos más importantes del momento.', 
 'Concierto', 
 DATE_ADD(NOW(), INTERVAL 2 MONTH), 
 DATE_ADD(DATE_ADD(NOW(), INTERVAL 2 MONTH), INTERVAL 5 HOUR), 
 'WiZink Center, Madrid', 
 10000, 0, 'default.jpg', 1),

-- Evento en 3 meses (Festival Electrónico)
(@adminId, 'Festival Electrónico Summer', 
 'El festival de música electrónica más grande de España con los mejores DJs del mundo. Dos días de música sin parar.', 
 'Festival', 
 DATE_ADD(NOW(), INTERVAL 3 MONTH), 
 DATE_ADD(DATE_ADD(NOW(), INTERVAL 3 MONTH), INTERVAL 12 HOUR), 
 'IFEMA, Madrid', 
 50000, 0, 'default.jpg', 1),

-- Evento en 1 mes (Teatro)
(@adminId, 'Teatro Musical: El Rey León', 
 'El musical más famoso del mundo llega a Madrid. Una experiencia mágica para toda la familia.', 
 'Teatro', 
 DATE_ADD(NOW(), INTERVAL 1 MONTH), 
 DATE_ADD(DATE_ADD(NOW(), INTERVAL 1 MONTH), INTERVAL 3 HOUR), 
 'Teatro Lope de Vega, Madrid', 
 1500, 0, 'default.jpg', 1),

-- Evento en 4 meses (Deporte)
(@adminId, 'Copa del Rey - Final', 
 'Final de la Copa del Rey de Fútbol. El evento deportivo más esperado del año.', 
 'Deporte', 
 DATE_ADD(NOW(), INTERVAL 4 MONTH), 
 DATE_ADD(DATE_ADD(NOW(), INTERVAL 4 MONTH), INTERVAL 2 HOUR), 
 'Santiago Bernabéu, Madrid', 
 80000, 0, 'default.jpg', 1);

-- Tipos de entrada con fechas dinámicas
-- Las ventas comienzan hoy y terminan justo antes del evento
INSERT INTO TipoEntrada (idEvento, nombre, descripcion, precio, cantidadDisponible, fechaInicioVenta, fechaFinVenta)
VALUES 
-- Evento 1: Concierto Rock (3 tipos)
(1, 'Entrada General', 'Acceso general al concierto de pie', 45.00, 8000, 
 NOW(), DATE_SUB(DATE_ADD(NOW(), INTERVAL 2 MONTH), INTERVAL 1 HOUR)),
(1, 'VIP', 'Acceso VIP con meet & greet y bebida incluida', 150.00, 500, 
 NOW(), DATE_SUB(DATE_ADD(NOW(), INTERVAL 2 MONTH), INTERVAL 1 HOUR)),
(1, 'Palco', 'Palco VIP con catering', 300.00, 100, 
 NOW(), DATE_SUB(DATE_ADD(NOW(), INTERVAL 2 MONTH), INTERVAL 1 HOUR)),

-- Evento 2: Festival Electrónico (3 tipos)
(2, 'Pase 1 Día', 'Acceso para el primer día del festival', 55.00, 30000, 
 NOW(), DATE_SUB(DATE_ADD(NOW(), INTERVAL 3 MONTH), INTERVAL 1 HOUR)),
(2, 'Pase Completo', 'Acceso completo a los 2 días del festival', 90.00, 15000, 
 NOW(), DATE_SUB(DATE_ADD(NOW(), INTERVAL 3 MONTH), INTERVAL 1 HOUR)),
(2, 'VIP Weekend', 'Acceso VIP 2 días con zona exclusiva y backstage', 250.00, 1000, 
 NOW(), DATE_SUB(DATE_ADD(NOW(), INTERVAL 3 MONTH), INTERVAL 1 HOUR)),

-- Evento 3: Teatro (2 tipos)
(3, 'Platea', 'Asientos en platea con mejor visibilidad', 80.00, 800, 
 NOW(), DATE_SUB(DATE_ADD(NOW(), INTERVAL 1 MONTH), INTERVAL 1 HOUR)),
(3, 'Anfiteatro', 'Asientos en anfiteatro', 50.00, 700, 
 NOW(), DATE_SUB(DATE_ADD(NOW(), INTERVAL 1 MONTH), INTERVAL 1 HOUR)),

-- Evento 4: Fútbol (3 tipos)
(4, 'Gradas', 'Asiento en gradas generales', 60.00, 60000, 
 NOW(), DATE_SUB(DATE_ADD(NOW(), INTERVAL 4 MONTH), INTERVAL 1 HOUR)),
(4, 'Preferente', 'Asiento preferente con mejor ubicación', 180.00, 15000, 
 NOW(), DATE_SUB(DATE_ADD(NOW(), INTERVAL 4 MONTH), INTERVAL 1 HOUR)),
(4, 'Palco', 'Palco VIP con catering premium', 500.00, 200, 
 NOW(), DATE_SUB(DATE_ADD(NOW(), INTERVAL 4 MONTH), INTERVAL 1 HOUR));

-- ============================================================
-- MENSAJE FINAL
-- ============================================================

SELECT '✅ Base de datos WorkFlowly creada exitosamente' AS 'Estado';
SELECT CONCAT('📊 ', COUNT(*), ' eventos de prueba creados') AS 'Eventos' FROM Evento;
SELECT CONCAT('🎫 ', COUNT(*), ' tipos de entrada disponibles') AS 'Tipos_Entrada' FROM TipoEntrada;
SELECT CONCAT('👤 Usuario admin creado') AS 'Usuario_Admin';
SELECT CONCAT('📧 Email: admin@workflowly.com') AS 'Credenciales';
SELECT CONCAT('🔑 Password: 12345678') AS 'Password';
SELECT '🚀 ¡Listo para usar!' AS 'Estado_Final';