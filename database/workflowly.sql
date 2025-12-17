CREATE DATABASE  IF NOT EXISTS `workflowly` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `workflowly`;
-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: workflowly
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `asiento`
--

DROP TABLE IF EXISTS `asiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asiento` (
  `idAsiento` int NOT NULL AUTO_INCREMENT,
  `idZona` int NOT NULL,
  `fila` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` int NOT NULL,
  `idEstadoAsiento` int NOT NULL,
  PRIMARY KEY (`idAsiento`),
  UNIQUE KEY `unique_asiento_zona` (`idZona`,`fila`,`numero`),
  KEY `idEstadoAsiento` (`idEstadoAsiento`),
  CONSTRAINT `asiento_ibfk_1` FOREIGN KEY (`idZona`) REFERENCES `zona` (`idZona`),
  CONSTRAINT `asiento_ibfk_2` FOREIGN KEY (`idEstadoAsiento`) REFERENCES `estado` (`idEstado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asiento`
--

LOCK TABLES `asiento` WRITE;
/*!40000 ALTER TABLE `asiento` DISABLE KEYS */;
/*!40000 ALTER TABLE `asiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `codigopromocion`
--

DROP TABLE IF EXISTS `codigopromocion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `codigopromocion` (
  `idCodigo` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipoDescuento` enum('Porcentaje','MontoFijo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valorDescuento` decimal(10,2) NOT NULL,
  `fechaInicio` datetime NOT NULL,
  `fechaFin` datetime NOT NULL,
  `usoMaximo` int DEFAULT NULL,
  `idEvento` int NOT NULL,
  `idEstadoCodigo` int NOT NULL,
  PRIMARY KEY (`idCodigo`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idEstadoCodigo` (`idEstadoCodigo`),
  KEY `idx_codigo_fecha` (`fechaInicio`,`fechaFin`),
  KEY `codigopromocion_ibfk_1` (`idEvento`),
  CONSTRAINT `codigopromocion_ibfk_1` FOREIGN KEY (`idEvento`) REFERENCES `evento` (`idEvento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `codigopromocion_ibfk_2` FOREIGN KEY (`idEstadoCodigo`) REFERENCES `estado` (`idEstado`),
  CONSTRAINT `codigopromocion_chk_1` CHECK ((`fechaFin` > `fechaInicio`)),
  CONSTRAINT `codigopromocion_chk_2` CHECK ((`valorDescuento` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `codigopromocion`
--

LOCK TABLES `codigopromocion` WRITE;
/*!40000 ALTER TABLE `codigopromocion` DISABLE KEYS */;
/*!40000 ALTER TABLE `codigopromocion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compra`
--

DROP TABLE IF EXISTS `compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compra` (
  `idCompra` int NOT NULL AUTO_INCREMENT,
  `idUsuario` int NOT NULL,
  `idMetodoPago` int NOT NULL,
  `fechaCompra` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` decimal(10,2) NOT NULL,
  `referenciaPago` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idEstadoCompra` int NOT NULL,
  PRIMARY KEY (`idCompra`),
  KEY `idUsuario` (`idUsuario`),
  KEY `idMetodoPago` (`idMetodoPago`),
  KEY `idEstadoCompra` (`idEstadoCompra`),
  KEY `idx_compra_fecha` (`fechaCompra`),
  CONSTRAINT `compra_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`),
  CONSTRAINT `compra_ibfk_2` FOREIGN KEY (`idMetodoPago`) REFERENCES `metodopago` (`idMetodoPago`),
  CONSTRAINT `compra_ibfk_3` FOREIGN KEY (`idEstadoCompra`) REFERENCES `estado` (`idEstado`),
  CONSTRAINT `compra_chk_1` CHECK ((`total` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compra`
--

LOCK TABLES `compra` WRITE;
/*!40000 ALTER TABLE `compra` DISABLE KEYS */;
INSERT INTO `compra` VALUES (1,2,1,'2025-11-26 22:40:03',123.00,NULL,9),(2,2,2,'2025-11-26 23:00:53',23.00,NULL,9),(3,2,3,'2025-11-26 23:23:53',450.00,NULL,9),(4,8,4,'2025-11-26 23:24:44',90.00,NULL,9),(5,2,5,'2025-11-28 00:18:18',123492.00,NULL,9),(6,2,6,'2025-11-28 01:14:34',123369.00,NULL,9),(7,2,7,'2025-12-10 00:12:52',45.00,NULL,9),(8,2,8,'2025-12-10 00:14:35',55.00,NULL,9),(9,2,9,'2025-12-10 00:26:49',55.00,NULL,9),(10,2,10,'2025-12-10 01:34:47',12.00,NULL,9),(11,2,11,'2025-12-10 01:54:13',12.00,NULL,9),(12,2,12,'2025-12-10 02:04:20',12.00,NULL,9),(13,2,13,'2025-12-14 16:54:08',10.00,NULL,9),(14,2,14,'2025-12-14 17:23:12',10.00,NULL,9);
/*!40000 ALTER TABLE `compra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detallecompra`
--

DROP TABLE IF EXISTS `detallecompra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detallecompra` (
  `idDetalleCompra` int NOT NULL AUTO_INCREMENT,
  `idCompra` int NOT NULL,
  `idTipoEntrada` int NOT NULL,
  `idAsiento` int DEFAULT NULL,
  `cantidad` int NOT NULL DEFAULT '1',
  `precioUnitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`idDetalleCompra`),
  KEY `idCompra` (`idCompra`),
  KEY `idTipoEntrada` (`idTipoEntrada`),
  KEY `idAsiento` (`idAsiento`),
  CONSTRAINT `detallecompra_ibfk_1` FOREIGN KEY (`idCompra`) REFERENCES `compra` (`idCompra`),
  CONSTRAINT `detallecompra_ibfk_2` FOREIGN KEY (`idTipoEntrada`) REFERENCES `tipoentrada` (`idTipoEntrada`),
  CONSTRAINT `detallecompra_ibfk_3` FOREIGN KEY (`idAsiento`) REFERENCES `asiento` (`idAsiento`),
  CONSTRAINT `detallecompra_chk_1` CHECK ((`cantidad` > 0)),
  CONSTRAINT `detallecompra_chk_2` CHECK ((`precioUnitario` >= 0)),
  CONSTRAINT `detallecompra_chk_3` CHECK ((`subtotal` = (`cantidad` * `precioUnitario`)))
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detallecompra`
--

LOCK TABLES `detallecompra` WRITE;
/*!40000 ALTER TABLE `detallecompra` DISABLE KEYS */;
INSERT INTO `detallecompra` VALUES (1,1,52,NULL,1,123.00,123.00),(2,2,61,NULL,1,23.00,23.00),(3,3,2,NULL,3,150.00,450.00),(4,4,1,NULL,2,45.00,90.00),(5,5,52,NULL,2,123.00,246.00),(6,5,55,NULL,1,123.00,123.00),(7,5,60,NULL,1,123123.00,123123.00),(8,6,52,NULL,1,123.00,123.00),(9,6,55,NULL,1,123.00,123.00),(10,6,60,NULL,1,123123.00,123123.00),(11,7,1,NULL,1,45.00,45.00),(12,8,4,NULL,1,55.00,55.00),(13,9,4,NULL,1,55.00,55.00),(14,10,64,NULL,1,12.00,12.00),(15,11,64,NULL,1,12.00,12.00),(16,12,64,NULL,1,12.00,12.00),(17,13,71,NULL,2,5.00,10.00),(18,14,71,NULL,2,5.00,10.00);
/*!40000 ALTER TABLE `detallecompra` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `before_detallecompra_insert` BEFORE INSERT ON `detallecompra` FOR EACH ROW BEGIN
    SET NEW.subtotal = NEW.cantidad * NEW.precioUnitario;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `after_detallecompra_insert` AFTER INSERT ON `detallecompra` FOR EACH ROW BEGIN
    UPDATE TipoEntrada SET cantidadDisponible = cantidadDisponible - NEW.cantidad
    WHERE idTipoEntrada = NEW.idTipoEntrada;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `entrada`
--

DROP TABLE IF EXISTS `entrada`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entrada` (
  `idEntrada` int NOT NULL AUTO_INCREMENT,
  `idDetalleCompra` int NOT NULL,
  `idAsiento` int DEFAULT NULL,
  `codigoBarras` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigoQR` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fechaGeneracion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaValidacion` datetime DEFAULT NULL,
  `idEstadoEntrada` int NOT NULL,
  PRIMARY KEY (`idEntrada`),
  UNIQUE KEY `codigoBarras` (`codigoBarras`),
  UNIQUE KEY `codigoQR` (`codigoQR`),
  KEY `idDetalleCompra` (`idDetalleCompra`),
  KEY `idAsiento` (`idAsiento`),
  KEY `idEstadoEntrada` (`idEstadoEntrada`),
  KEY `idx_entrada_validacion` (`fechaValidacion`),
  CONSTRAINT `entrada_ibfk_1` FOREIGN KEY (`idDetalleCompra`) REFERENCES `detallecompra` (`idDetalleCompra`),
  CONSTRAINT `entrada_ibfk_2` FOREIGN KEY (`idAsiento`) REFERENCES `asiento` (`idAsiento`),
  CONSTRAINT `entrada_ibfk_3` FOREIGN KEY (`idEstadoEntrada`) REFERENCES `estado` (`idEstado`),
  CONSTRAINT `entrada_chk_1` CHECK (((`fechaValidacion` is null) or (`fechaValidacion` >= `fechaGeneracion`)))
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entrada`
--

LOCK TABLES `entrada` WRITE;
/*!40000 ALTER TABLE `entrada` DISABLE KEYS */;
INSERT INTO `entrada` VALUES (1,1,NULL,'BC20251126CCDC6A2DEAD0','QR692773B3A48AA5761E88E','2025-11-26 22:40:03',NULL,1),(2,2,NULL,'BC2025112617963B9178ED','QR692778950961DB7DCE651','2025-11-26 23:00:53',NULL,1),(3,3,NULL,'BC20251126A0C29D23D2FD','QR69277DF9979A2C43D4C50','2025-11-26 23:23:53',NULL,1),(4,3,NULL,'BC20251126553D27E0A00A','QR69277DF997E0DCF182E2D','2025-11-26 23:23:53',NULL,1),(5,3,NULL,'BC202511264318B7217889','QR69277DF9982435F79CDDA','2025-11-26 23:23:53',NULL,1),(6,4,NULL,'BC202511260E499695DDF4','QR69277E2CA3A3103B488E5','2025-11-26 23:24:44',NULL,1),(7,4,NULL,'BC20251126925490B9E7AE','QR69277E2CA3EB08CD59F1C','2025-11-26 23:24:44',NULL,1),(8,5,NULL,'BC20251128CB2BA53FFB3D','QR6928DC3A7F1AA6B2EBFCB','2025-11-28 00:18:18',NULL,1),(9,5,NULL,'BC20251128EF14FC39F1B8','QR6928DC3A7F89A1E482D27','2025-11-28 00:18:18',NULL,1),(10,6,NULL,'BC2025112893E9745F1A36','QR6928DC3A80F2F349EF464','2025-11-28 00:18:18',NULL,1),(11,7,NULL,'BC202511287E1A66D6B3D0','QR6928DC3A820589E5EBA1C','2025-11-28 00:18:18',NULL,1),(12,8,NULL,'BC20251128849EBD23AC68','QR6928E96AA8A99085B14B4','2025-11-28 01:14:34',NULL,1),(13,9,NULL,'BC202511280A669B43ECF7','QR6928E96AA9EB348837E46','2025-11-28 01:14:34',NULL,1),(14,10,NULL,'BC20251128C38235E19E94','QR6928E96AAB10EB3DC88A5','2025-11-28 01:14:34',NULL,1),(15,11,NULL,'BC2025121059895E21BB9B','QR6938ACF474F5586924C3E','2025-12-10 00:12:52',NULL,1),(16,12,NULL,'BC202512107FFFC3723736','QR6938AD5B89E25327C0456','2025-12-10 00:14:35',NULL,1),(17,13,NULL,'BC20251210774429906F74','QR6938B039245FCA7657DBD','2025-12-10 00:26:49',NULL,1),(18,14,NULL,'BC20251210F56A088B194B','QR6938C027EA4A46BBCB025','2025-12-10 01:34:47',NULL,1),(19,15,NULL,'BC202512101433CB169542','QR6938C4B561483EAB40C9E','2025-12-10 01:54:13',NULL,1),(20,16,NULL,'BC20251210102DD665A544','QR6938C714DFB0B2BEFDD83','2025-12-10 02:04:20',NULL,1),(21,17,NULL,'BC20251214EB55A0C00AF9','QR693EDDA0D35500BC1E540','2025-12-14 16:54:08',NULL,1),(22,17,NULL,'BC20251214442AD4C0DAC2','QR693EDDA0D40372E05626C','2025-12-14 16:54:08',NULL,1),(23,18,NULL,'BC202512141F3E1F4254E4','QR693EE470B3CE87CB0D8C3','2025-12-14 17:23:12',NULL,1),(24,18,NULL,'BC20251214E216BF6B7417','QR693EE470B41CDA4861FA4','2025-12-14 17:23:12',NULL,1);
/*!40000 ALTER TABLE `entrada` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `after_entrada_insert` AFTER INSERT ON `entrada` FOR EACH ROW BEGIN
    IF NEW.idAsiento IS NOT NULL THEN
        UPDATE Asiento SET idEstadoAsiento = (SELECT idEstado FROM Estado WHERE nombre = 'Vendido' AND tipoEntidad = 'Asiento')
        WHERE idAsiento = NEW.idAsiento;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `estado`
--

DROP TABLE IF EXISTS `estado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estado` (
  `idEstado` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipoEntidad` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idEstado`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estado`
--

LOCK TABLES `estado` WRITE;
/*!40000 ALTER TABLE `estado` DISABLE KEYS */;
INSERT INTO `estado` VALUES (1,'Activo','Elemento activo y disponible','General'),(2,'Inactivo','Elemento inactivo o deshabilitado','General'),(3,'Pendiente','Elemento en espera de procesamiento','General'),(4,'Completado','Elemento procesado completamente','General'),(5,'Cancelado','Elemento cancelado','General'),(6,'Disponible','Elemento disponible para su uso','Asiento'),(7,'Reservado','Elemento reservado temporalmente','Asiento'),(8,'Vendido','Elemento vendido','Asiento'),(9,'Pagado','Pago completado','Compra'),(10,'Pendiente de pago','Pago en proceso','Compra'),(11,'Verificado','Elemento verificado','Entrada'),(12,'No verificado','Elemento sin verificar','Entrada');
/*!40000 ALTER TABLE `estado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evento`
--

DROP TABLE IF EXISTS `evento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evento` (
  `idEvento` int NOT NULL AUTO_INCREMENT,
  `idUsuario` int NOT NULL,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fechaInicio` datetime NOT NULL,
  `fechaFin` datetime NOT NULL,
  `ubicacion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aforoTotal` int NOT NULL,
  `entradasDisponibles` int NOT NULL,
  `imagenPrincipal` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'imagen/default.jpg',
  `idEstadoEvento` int DEFAULT NULL,
  `idLugar` int DEFAULT NULL,
  `idOrganizador` int DEFAULT NULL,
  PRIMARY KEY (`idEvento`),
  KEY `idUsuario` (`idUsuario`),
  KEY `idEstadoEvento` (`idEstadoEvento`),
  KEY `idx_evento_fecha` (`fechaInicio`,`fechaFin`),
  KEY `fk_evento_lugar` (`idLugar`),
  KEY `fk_evento_organizador` (`idOrganizador`),
  CONSTRAINT `evento_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE,
  CONSTRAINT `evento_ibfk_2` FOREIGN KEY (`idEstadoEvento`) REFERENCES `estado` (`idEstado`) ON DELETE SET NULL,
  CONSTRAINT `fk_evento_lugar` FOREIGN KEY (`idLugar`) REFERENCES `lugar` (`idLugar`) ON DELETE SET NULL,
  CONSTRAINT `fk_evento_organizador` FOREIGN KEY (`idOrganizador`) REFERENCES `organizador` (`idOrganizador`) ON DELETE SET NULL,
  CONSTRAINT `evento_chk_1` CHECK ((`fechaFin` > `fechaInicio`)),
  CONSTRAINT `evento_chk_2` CHECK ((`entradasDisponibles` <= `aforoTotal`))
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evento`
--

LOCK TABLES `evento` WRITE;
/*!40000 ALTER TABLE `evento` DISABLE KEYS */;
INSERT INTO `evento` VALUES (1,1,'Concierto Rock Madrid 2027','Gran noche de rock con las mejores bandas nacionales e internacionales. Una experiencia inolvidable.','Concierto','2026-01-30 16:33:21','2026-01-31 21:33:21','WiZink Center, Madrid',17000,8588,'uploads/187d4505600167d6.webp',1,1,1),(2,1,'Festival Electrónico','El festival de música electrónica más grande de España con los mejores DJs del mundo.','Festival','2026-01-26 16:33:21','2026-01-27 04:33:21','IFEMA, Madrid',17000,17000,'uploads/85f3d6c56c4fb02b.webp',2,1,1),(3,1,'Teatro Musical: El Rey León','El musical más famoso del mundo llega a Madrid. Una experiencia mágica para toda la familia.','Otro','2026-03-27 16:33:21','2026-03-31 19:33:21','Teatro Lope de Vega, Madrid',1500,1500,'uploads/63072d80bb8c5177.jpg',1,3,1),(15,9,'Evento del polloooo','SI','Festival','2027-01-15 21:39:12','2028-01-22 21:39:14','Mi casa (San Luis Potosí)',5000,3561,'uploads/7afb07cdf2b926cd.jpg',2,6,6),(16,3,'Evento del pollo','asdasdasdas','Festival','2025-11-21 22:15:57','2025-12-06 22:15:59','Teatro Lope de Vega (Madrid)',20000,12333,'uploads/placeholder-event.jpg',2,4,2),(18,8,'Evento super pollo','guay','Festival','2025-11-28 01:35:19','2025-11-29 01:35:23','IFEMA Madrid (Madrid)',213,213,'uploads/e1148d922373c679.jpg',2,7,4),(19,8,'Prueba final','asdasdasd','Concierto','2026-01-11 01:10:11','2026-01-18 01:10:17','Mi casa (Los Molinos)',213,195,'uploads/39a00131060aae9f.webp',2,7,4),(20,1,'Festival Neon Pulse 2026','Festival de música electrónica con DJs internacionales, escenarios temáticos y experiencia visual inmersiva. Música non-stop.','Festival','2026-02-13 17:16:11','2026-02-28 17:16:18','IFEMA Madrid (Madrid)',50000,26000,'uploads/c4272b6b839e736c.webp',1,2,1),(21,1,'Concierto Luna Roja Tour','Concierto del nuevo tour “Luna Roja”, con puesta en escena cinematográfica y repertorio completo del artista.','Concierto','2026-03-13 17:20:42','2026-03-14 17:20:47','WiZink Center (Madrid)',17000,16000,'uploads/fa0516857ca9a742.jpg',1,1,1),(22,1,'Congreso TechFuture 2025','Congreso tecnológico sobre IA, desarrollo web, ciberseguridad y nuevas tendencias digitales, con ponentes internacionales.','Cultural','2026-05-28 17:28:37','2026-07-02 17:28:44','IFEMA Madrid (Madrid)',50000,50000,'uploads/0fc485029b3fbec5.jpg',1,2,1),(23,8,'Evento: Expo Avícola 2026 – Granjas de Pollos','Expo Avícola 2025 es un evento especializado dedicado a la producción avícola moderna, centrado en granjas de pollos de engorde y gallinas ponedoras.\r\n\r\nDurante tres días se ofrecerán:\r\n\r\nDemostraciones de tecnología aplicada a granjas avícolas\r\n\r\nCharlas sobre bienestar animal, bioseguridad y sostenibilidad\r\n\r\nExposición de sistemas de alimentación, ventilación y automatización\r\n\r\nEspacios formativos para estudiantes y nuevos productores\r\n\r\nNetworking entre profesionales del sector agroalimentario\r\n\r\nUn evento orientado tanto a profesionales del sector como a público interesado en conocer cómo funciona una granja avícola moderna desde dentro.','Cultural','2026-04-30 17:35:09','2026-05-13 17:35:12','IFEMA Madrid (Madrid)',50000,47094,'uploads/7ee29b0f308ae50e.jpg',1,2,4);
/*!40000 ALTER TABLE `evento` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `evento_set_aforo` BEFORE INSERT ON `evento` FOR EACH ROW BEGIN
    -- si no viene aforo, lo pillamos del lugar
    IF (NEW.aforoTotal IS NULL OR NEW.aforoTotal = 0) AND NEW.idLugar IS NOT NULL THEN
        SET NEW.aforoTotal = (
            SELECT capacidad FROM Lugar WHERE idLugar = NEW.idLugar LIMIT 1
        );
        IF NEW.aforoTotal IS NULL OR NEW.aforoTotal = 0 THEN
            SET NEW.aforoTotal = 1;
        END IF;
    END IF;

    -- entradasDisponibles = aforoTotal
    IF (NEW.entradasDisponibles IS NULL OR NEW.entradasDisponibles = 0) THEN
        SET NEW.entradasDisponibles = NEW.aforoTotal;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `evento_bi` BEFORE INSERT ON `evento` FOR EACH ROW BEGIN
  IF NEW.idOrganizador IS NOT NULL THEN
    SELECT idUsuario INTO @u FROM Organizador
    WHERE idOrganizador = NEW.idOrganizador LIMIT 1;
    IF @u IS NOT NULL THEN SET NEW.idUsuario = @u; END IF;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `evento_update_aforo` BEFORE UPDATE ON `evento` FOR EACH ROW BEGIN
    -- si cambia el lugar o aforo está vacío, actualiza
    IF (NEW.idLugar <> OLD.idLugar) OR (NEW.aforoTotal IS NULL OR NEW.aforoTotal = 0) THEN
        SET NEW.aforoTotal = (
            SELECT capacidad FROM Lugar WHERE idLugar = NEW.idLugar LIMIT 1
        );
        IF NEW.aforoTotal IS NULL OR NEW.aforoTotal = 0 THEN
            SET NEW.aforoTotal = 1;
        END IF;
    END IF;

    -- si entradasDisponibles está en 0 o null, igualarlo al aforo
    IF (NEW.entradasDisponibles IS NULL OR NEW.entradasDisponibles = 0) THEN
        SET NEW.entradasDisponibles = NEW.aforoTotal;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `evento_bu` BEFORE UPDATE ON `evento` FOR EACH ROW BEGIN
  IF NEW.idOrganizador <> OLD.idOrganizador THEN
    SELECT idUsuario INTO @u FROM Organizador
    WHERE idOrganizador = NEW.idOrganizador LIMIT 1;
    IF @u IS NOT NULL THEN SET NEW.idUsuario = @u; END IF;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `favoritoevento`
--

DROP TABLE IF EXISTS `favoritoevento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favoritoevento` (
  `idFavoritoEvento` int NOT NULL AUTO_INCREMENT,
  `idUsuario` int NOT NULL,
  `idEvento` int NOT NULL,
  `fechaAgregado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idFavoritoEvento`),
  UNIQUE KEY `unique_usuario_evento` (`idUsuario`,`idEvento`),
  KEY `favoritoevento_ibfk_2` (`idEvento`),
  CONSTRAINT `favoritoevento_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`),
  CONSTRAINT `favoritoevento_ibfk_2` FOREIGN KEY (`idEvento`) REFERENCES `evento` (`idEvento`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favoritoevento`
--

LOCK TABLES `favoritoevento` WRITE;
/*!40000 ALTER TABLE `favoritoevento` DISABLE KEYS */;
INSERT INTO `favoritoevento` VALUES (5,2,18,'2025-11-28 00:57:19'),(6,3,16,'2025-11-28 00:58:12'),(7,2,16,'2025-11-28 00:59:01');
/*!40000 ALTER TABLE `favoritoevento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lugar`
--

DROP TABLE IF EXISTS `lugar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lugar` (
  `idLugar` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacidad` int DEFAULT NULL,
  `accesoDiscapacitados` tinyint(1) DEFAULT '0',
  `parking` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transportePublico` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mapaUrl` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idLugar`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lugar`
--

LOCK TABLES `lugar` WRITE;
/*!40000 ALTER TABLE `lugar` DISABLE KEYS */;
INSERT INTO `lugar` VALUES (1,'WiZink Center','Av. Felipe II, s/n','Madrid','España',17000,1,'Parking WiZink - 400 plazas','Metro Goya (L2, L4)','https://goo.gl/maps/5uZ9f5J8ZQk'),(2,'IFEMA Madrid','Av. del Partenón, 5','Madrid','España',50000,1,'Parking IFEMA Norte','Metro Feria de Madrid (L8)','https://goo.gl/maps/7WrNnVZ3sV52'),(3,'Teatro Lope de Vega','Calle Gran Vía, 57','Madrid','España',1500,1,'Parking Plaza España','Metro Santo Domingo (L2)','https://goo.gl/maps/Qy1s98vKycG2'),(4,'Mi casa','Avenida de la marina','Los Molinos','España',20000,0,NULL,NULL,NULL),(5,'Mi casa','Lugar de A Braxe, 22; San Vicente, Vilaboa','Valdoviño','España',50000,0,NULL,NULL,NULL),(6,'Mi casa','Calle Paseo de los Castaños 217','San Luis Potosí','Mexico',5000,1,'Parking propio','Estacion Braxe','https://maps.app.goo.gl/Yy9mE9rBNBBGUbaw6'),(7,'Mi casa','Avenida de la Marina, 6','Los Molinos','Suecia',213,0,'Parking guay','Estgacion SUR','https://maps.app.goo.gl/Yy9mE9rBNBBGUbaw6');
/*!40000 ALTER TABLE `lugar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `metodopago`
--

DROP TABLE IF EXISTS `metodopago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `metodopago` (
  `idMetodoPago` int NOT NULL AUTO_INCREMENT,
  `idUsuario` int NOT NULL,
  `tipo` enum('Tarjeta','PayPal','Bizum','Otro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenReferencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombreTitular` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fechaExpiracion` date DEFAULT NULL,
  `esPredeterminado` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`idMetodoPago`),
  KEY `idUsuario` (`idUsuario`),
  CONSTRAINT `metodopago_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metodopago`
--

LOCK TABLES `metodopago` WRITE;
/*!40000 ALTER TABLE `metodopago` DISABLE KEYS */;
INSERT INTO `metodopago` VALUES (1,2,'Tarjeta',NULL,'Sesé',NULL,0),(2,2,'Tarjeta',NULL,'Sesé',NULL,0),(3,2,'Tarjeta',NULL,'Sesé',NULL,0),(4,8,'Tarjeta',NULL,'Sesé',NULL,0),(5,2,'Tarjeta',NULL,'Sesé',NULL,0),(6,2,'Tarjeta',NULL,'Sesé',NULL,0),(7,2,'Tarjeta',NULL,'Sesé',NULL,0),(8,2,'Tarjeta',NULL,'Sesé',NULL,0),(9,2,'Tarjeta',NULL,'Sesé',NULL,0),(10,2,'Tarjeta',NULL,'Sesé',NULL,0),(11,2,'Tarjeta',NULL,'Sesé',NULL,0),(12,2,'Tarjeta',NULL,'Sesé',NULL,0),(13,2,'Tarjeta',NULL,'Sesé',NULL,0),(14,2,'Tarjeta',NULL,'Sesé Filgueira',NULL,0);
/*!40000 ALTER TABLE `metodopago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organizador`
--

DROP TABLE IF EXISTS `organizador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organizador` (
  `idOrganizador` int NOT NULL AUTO_INCREMENT,
  `idUsuario` int NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `totalEventos` int DEFAULT '0',
  `totalAsistentes` int DEFAULT '0',
  `valoracionPromedio` decimal(2,1) DEFAULT '0.0',
  PRIMARY KEY (`idOrganizador`),
  UNIQUE KEY `uniq_organizador_usuario` (`idUsuario`),
  CONSTRAINT `organizador_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organizador`
--

LOCK TABLES `organizador` WRITE;
/*!40000 ALTER TABLE `organizador` DISABLE KEYS */;
INSERT INTO `organizador` VALUES (1,1,'Empresa líder en gestión de espectáculos y conciertos nacionales. Pionera en el uso de plataformas digitales para eventos masivos.',24,120000,4.9),(2,3,'Productora de teatro musical y eventos culturales con amplia trayectoria en la Gran Vía madrileña.',12,35000,4.8),(3,4,NULL,0,0,0.0),(4,8,NULL,0,0,0.0),(6,9,NULL,0,0,0.0);
/*!40000 ALTER TABLE `organizador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `politicaevento`
--

DROP TABLE IF EXISTS `politicaevento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `politicaevento` (
  `idPolitica` int NOT NULL AUTO_INCREMENT,
  `idEvento` int NOT NULL,
  `categoria` enum('Entradas','Cancelaciones','Seguridad','Informacion') COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idPolitica`),
  KEY `idEvento` (`idEvento`),
  CONSTRAINT `politicaevento_ibfk_1` FOREIGN KEY (`idEvento`) REFERENCES `evento` (`idEvento`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `politicaevento`
--

LOCK TABLES `politicaevento` WRITE;
/*!40000 ALTER TABLE `politicaevento` DISABLE KEYS */;
INSERT INTO `politicaevento` VALUES (1,1,'Entradas','Entradas no reembolsables','Las entradas son personales e intransferibles. Se recomienda conservar el comprobante de compra.'),(2,1,'Cancelaciones','Cancelaciones por fuerza mayor','Solo se reembolsará el importe en caso de cancelación oficial del evento.'),(3,1,'Seguridad','Controles y acceso','No se permite la entrada de objetos peligrosos. Se realizarán controles de seguridad en el acceso.'),(4,1,'Informacion','Horarios y apertura de puertas','Apertura de puertas a las 18:00. Inicio del concierto a las 20:00.'),(5,2,'Entradas','Pulseras digitales','Cada entrada se canjea por una pulsera electrónica para todo el evento.'),(6,2,'Cancelaciones','No se admiten reembolsos','Una vez comprada la entrada, no se admiten devoluciones salvo cancelación del festival.'),(7,2,'Seguridad','Edad mínima y control de acceso','Prohibida la entrada a menores de 18 años. Se requerirá DNI en el acceso.'),(8,2,'Informacion','Servicios dentro del recinto','El festival cuenta con zonas de comida, bebida y recarga de móviles.'),(9,3,'Entradas','Entradas nominativas','Cada entrada se emite a nombre del comprador. Es obligatorio mostrar un documento de identidad.'),(10,3,'Cancelaciones','Reembolso hasta 48h antes','Podrá solicitarse devolución hasta 48 horas antes de la función.'),(11,3,'Seguridad','Uso de cámaras y móviles','No está permitido grabar ni tomar fotografías durante la función.'),(12,3,'Informacion','Duración y descanso','Duración aproximada: 2h 30min con un intermedio de 15 minutos.');
/*!40000 ALTER TABLE `politicaevento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipoentrada`
--

DROP TABLE IF EXISTS `tipoentrada`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipoentrada` (
  `idTipoEntrada` int NOT NULL AUTO_INCREMENT,
  `idEvento` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `precio` decimal(10,2) NOT NULL,
  `cantidadDisponible` int NOT NULL,
  `fechaInicioVenta` datetime NOT NULL,
  `fechaFinVenta` datetime NOT NULL,
  PRIMARY KEY (`idTipoEntrada`),
  KEY `idx_tipoentrada_precio` (`precio`),
  KEY `tipoentrada_ibfk_1` (`idEvento`),
  CONSTRAINT `tipoentrada_ibfk_1` FOREIGN KEY (`idEvento`) REFERENCES `evento` (`idEvento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tipoentrada_chk_1` CHECK ((`fechaFinVenta` > `fechaInicioVenta`)),
  CONSTRAINT `tipoentrada_chk_2` CHECK ((`cantidadDisponible` >= 0)),
  CONSTRAINT `tipoentrada_chk_3` CHECK ((`precio` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipoentrada`
--

LOCK TABLES `tipoentrada` WRITE;
/*!40000 ALTER TABLE `tipoentrada` DISABLE KEYS */;
INSERT INTO `tipoentrada` VALUES (1,1,'Entrada General','Acceso general al concierto de pie',45.00,7994,'2025-10-26 16:33:21','2025-12-26 15:33:21'),(2,1,'VIP','Acceso VIP con meet & greet y bebida incluida',150.00,494,'2025-10-26 16:33:21','2025-12-26 15:33:21'),(3,1,'Palco','Palco VIP con catering',300.00,100,'2025-10-26 16:33:21','2025-12-26 15:33:21'),(4,2,'Pase 1 Día','Acceso para el primer día del festival',55.00,29996,'2025-10-26 16:33:21','2026-01-26 15:33:21'),(5,2,'Pase Completo','Acceso completo a los 2 días del festival',90.00,15000,'2025-10-26 16:33:21','2026-01-26 15:33:21'),(6,2,'VIP Weekend','Acceso VIP 2 días con zona exclusiva y backstage',250.00,1000,'2025-10-26 16:33:21','2026-01-26 15:33:21'),(7,3,'Platea','Asientos en platea con mejor visibilidad',80.00,800,'2025-10-26 16:33:21','2025-11-26 15:33:21'),(8,3,'Anfiteatro','Asientos en anfiteatro',50.00,700,'2025-10-26 16:33:21','2025-11-26 15:33:21'),(52,15,'MEGAVIP','',123.00,1214,'2025-11-02 22:46:35','2026-11-02 22:46:35'),(55,15,'SUPERVIP','',123.00,1352,'2025-11-02 22:53:32','2026-11-02 22:53:32'),(60,15,'pruebaentrada','que si',123123.00,996,'2025-11-05 00:17:14','2026-11-05 00:17:14'),(61,16,'asdasdas','asdasd',23.00,0,'2025-11-05 20:51:22','2026-11-05 20:51:22'),(62,18,'General','si',45.00,45000,'2025-11-12 01:37:03','2026-11-12 01:37:03'),(63,16,'MEGAVIP','asdasdas',123.00,12333,'2025-11-27 00:58:33','2026-11-27 00:58:33'),(64,19,'MEGAVIP','',12.00,196,'2025-12-10 01:13:25','2026-12-10 01:13:25'),(65,20,'General','Entrada general',12.00,25000,'2025-12-13 17:19:33','2026-12-13 17:19:33'),(66,20,'VIP','Entrada VIP con accesos exclusivos',26.00,1000,'2025-12-13 17:20:04','2026-12-13 17:20:04'),(67,21,'General','Entrada general',20.00,15000,'2025-12-13 17:22:28','2026-12-13 17:22:28'),(68,21,'SUPERVIP','Entrada SUPERVIP con acceso a camerinos',50.00,1000,'2025-12-13 17:23:10','2026-12-13 17:23:10'),(69,22,'General','Acceso general',10.00,45000,'2025-12-13 17:31:56','2026-12-13 17:31:56'),(70,22,'Express','Acceso con prioridad sin colas',30.00,5000,'2025-12-13 17:32:15','2026-12-13 17:32:15'),(71,23,'General','Acceso general',5.00,44996,'2025-12-13 17:37:32','2026-12-13 17:37:32'),(72,23,'VIP','Acceso general + acceso a tocar las gallinas',100.00,2100,'2025-12-13 17:38:08','2026-12-13 17:38:08');
/*!40000 ALTER TABLE `tipoentrada` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `after_tipoentrada_insert` AFTER INSERT ON `tipoentrada` FOR EACH ROW BEGIN
    UPDATE Evento e
    JOIN (
        SELECT idEvento, COALESCE(SUM(cantidadDisponible),0) AS totalTipos
        FROM TipoEntrada
        WHERE idEvento = NEW.idEvento
        GROUP BY idEvento
    ) x ON x.idEvento = e.idEvento
    SET e.entradasDisponibles = LEAST(x.totalTipos, e.aforoTotal)
    WHERE e.idEvento = NEW.idEvento;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `after_tipoentrada_update` AFTER UPDATE ON `tipoentrada` FOR EACH ROW BEGIN
    UPDATE Evento e
    JOIN (
        SELECT idEvento, COALESCE(SUM(cantidadDisponible),0) AS totalTipos
        FROM TipoEntrada
        WHERE idEvento = NEW.idEvento
        GROUP BY idEvento
    ) x ON x.idEvento = e.idEvento
    SET e.entradasDisponibles = LEAST(x.totalTipos, e.aforoTotal)
    WHERE e.idEvento = NEW.idEvento;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tipoentradazona`
--

DROP TABLE IF EXISTS `tipoentradazona`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipoentradazona` (
  `idTipoEntradaZona` int NOT NULL AUTO_INCREMENT,
  `idTipoEntrada` int NOT NULL,
  `idZona` int NOT NULL,
  PRIMARY KEY (`idTipoEntradaZona`),
  UNIQUE KEY `unique_tipo_zona` (`idTipoEntrada`,`idZona`),
  KEY `idZona` (`idZona`),
  CONSTRAINT `tipoentradazona_ibfk_1` FOREIGN KEY (`idTipoEntrada`) REFERENCES `tipoentrada` (`idTipoEntrada`),
  CONSTRAINT `tipoentradazona_ibfk_2` FOREIGN KEY (`idZona`) REFERENCES `zona` (`idZona`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipoentradazona`
--

LOCK TABLES `tipoentradazona` WRITE;
/*!40000 ALTER TABLE `tipoentradazona` DISABLE KEYS */;
/*!40000 ALTER TABLE `tipoentradazona` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `idUsuario` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fechaNacimiento` date DEFAULT NULL,
  `fechaRegistro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipoUsuario` enum('NoRegistrado','Comprador','Organizador','Admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NoRegistrado',
  `idEstadoUsuario` int NOT NULL,
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `email` (`email`),
  KEY `idEstadoUsuario` (`idEstadoUsuario`),
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`idEstadoUsuario`) REFERENCES `estado` (`idEstado`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Admin','WorkFlowly','admin@workflowly.com','$2y$12$bCpNqO7EZbD48P7jRCgwRObvbg6SbnWSLmW5wct2zHT0u6PtAf9Y2','689457806',NULL,'2025-10-26 16:33:21','Admin',1),(2,'Sesé','Filgueiraaaa','pollo@gmail.com','$2y$12$xxN/CqsCF23vqfGtISMlVuM3uBauoAM.OB7xjKY13KN2VddgHf85W','+34689457805',NULL,'2025-10-26 18:33:41','Admin',1),(3,'Organizador','POLLO','po34llo@gmail.com','$2y$12$wvCsAJ5gUzkwIn0PFsDuHenAaxyxkYb5PM/oZiOEQ8I8WGFRFAWJ.','1239817231298',NULL,'2025-10-29 20:42:40','Organizador',1),(4,'Prueba','Pruebita','prueba@gmail.com','$2y$12$SmvQw4mu1idmMu4NwYUoaOEQI2axnPxV22pYWIxppZSWBLzUGsx/W','345923943',NULL,'2025-11-02 20:20:43','Organizador',1),(5,'Sesé','Filgueira','aksjdbkajsd@askljdnaskjd.com','$2y$12$Pr8K2E2iIN8/dWu8fQzJ3.kr.Dnfbra.Px4tzSrGdlIw0S9.bp/iq','12431234123',NULL,'2025-11-03 22:44:03','Comprador',1),(8,'Sesé','Lorite','sesemetin3@gmail.com','$2y$12$Ef25OKq.7TYpdUvp/G47NuTQRisjqlLipJHKh3HppQ17Kivm3CpBW','+34689457805',NULL,'2025-11-12 01:33:46','Organizador',1),(9,'Organizdor','Pollez','pollez@gmail.com','$2y$12$xc.0.9Dz4uoKn2OOSB66VuekixOXufzKvofHIQC98YCJBGILFhfjq','234234234324',NULL,'2025-11-12 02:10:44','Organizador',1),(10,'Sesé','Lorite','pol54lo@gmail.com','$2y$10$qbCtgNJN/fJZ.0bzh3RUJeQsvuyoSilhvULpYdL9pua5y6B2T6hTe','+34689457805','2025-11-01','2025-11-28 00:29:26','Comprador',1),(11,'Sesé','Filgueira','random@gmail.com','$2y$12$onpZAeXGzmDKoar62HQKgOcEYVGtd/zvk4AoREV4nDldwgq.OHWCK','+34689457805','2004-01-01','2025-12-14 17:19:12','Comprador',1);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `usuario_after_insert` AFTER INSERT ON `usuario` FOR EACH ROW BEGIN
  IF NEW.tipoUsuario = 'Organizador' THEN
    INSERT IGNORE INTO organizador (idUsuario) VALUES (NEW.idUsuario);
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `usuario_after_update` AFTER UPDATE ON `usuario` FOR EACH ROW BEGIN
  IF NEW.tipoUsuario = 'Organizador' AND OLD.tipoUsuario <> 'Organizador' THEN
    INSERT IGNORE INTO organizador (idUsuario) VALUES (NEW.idUsuario);
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Temporary view structure for view `vw_entradas_usuario`
--

DROP TABLE IF EXISTS `vw_entradas_usuario`;
/*!50001 DROP VIEW IF EXISTS `vw_entradas_usuario`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_entradas_usuario` AS SELECT 
 1 AS `idUsuario`,
 1 AS `nombreUsuario`,
 1 AS `idEntrada`,
 1 AS `nombreEvento`,
 1 AS `fechaInicio`,
 1 AS `ubicacion`,
 1 AS `tipoEntrada`,
 1 AS `precioUnitario`,
 1 AS `ubicacionAsiento`,
 1 AS `estado`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `vw_eventos_disponibles`
--

DROP TABLE IF EXISTS `vw_eventos_disponibles`;
/*!50001 DROP VIEW IF EXISTS `vw_eventos_disponibles`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_eventos_disponibles` AS SELECT 
 1 AS `idEvento`,
 1 AS `nombre`,
 1 AS `descripcion`,
 1 AS `fechaInicio`,
 1 AS `fechaFin`,
 1 AS `ubicacion`,
 1 AS `entradasDisponibles`,
 1 AS `precioDesde`,
 1 AS `organizador`,
 1 AS `tiposEntrada`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `vw_validacion_entrada`
--

DROP TABLE IF EXISTS `vw_validacion_entrada`;
/*!50001 DROP VIEW IF EXISTS `vw_validacion_entrada`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_validacion_entrada` AS SELECT 
 1 AS `idEntrada`,
 1 AS `codigoBarras`,
 1 AS `codigoQR`,
 1 AS `estadoValidacion`,
 1 AS `evento`,
 1 AS `fechaInicio`,
 1 AS `fechaFin`,
 1 AS `tipoEntrada`,
 1 AS `asiento`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `zona`
--

DROP TABLE IF EXISTS `zona`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `zona` (
  `idZona` int NOT NULL AUTO_INCREMENT,
  `idEvento` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacidad` int NOT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idZona`),
  KEY `zona_ibfk_1` (`idEvento`),
  CONSTRAINT `zona_ibfk_1` FOREIGN KEY (`idEvento`) REFERENCES `evento` (`idEvento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `zona_chk_1` CHECK ((`capacidad` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zona`
--

LOCK TABLES `zona` WRITE;
/*!40000 ALTER TABLE `zona` DISABLE KEYS */;
/*!40000 ALTER TABLE `zona` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'workflowly'
--

--
-- Dumping routines for database 'workflowly'
--
/*!50003 DROP PROCEDURE IF EXISTS `crear_evento` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `crear_evento`(
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `realizar_compra` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `realizar_compra`(
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `vw_entradas_usuario`
--

/*!50001 DROP VIEW IF EXISTS `vw_entradas_usuario`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_entradas_usuario` AS select `u`.`idUsuario` AS `idUsuario`,`u`.`nombre` AS `nombreUsuario`,`e`.`idEntrada` AS `idEntrada`,`ev`.`nombre` AS `nombreEvento`,`ev`.`fechaInicio` AS `fechaInicio`,`ev`.`ubicacion` AS `ubicacion`,`te`.`nombre` AS `tipoEntrada`,`dc`.`precioUnitario` AS `precioUnitario`,concat(ifnull(`a`.`fila`,''),' ',ifnull(`a`.`numero`,'')) AS `ubicacionAsiento`,`es`.`nombre` AS `estado` from (((((((`usuario` `u` join `compra` `c` on((`u`.`idUsuario` = `c`.`idUsuario`))) join `detallecompra` `dc` on((`c`.`idCompra` = `dc`.`idCompra`))) join `entrada` `e` on((`dc`.`idDetalleCompra` = `e`.`idDetalleCompra`))) join `tipoentrada` `te` on((`dc`.`idTipoEntrada` = `te`.`idTipoEntrada`))) join `evento` `ev` on((`te`.`idEvento` = `ev`.`idEvento`))) left join `asiento` `a` on((`e`.`idAsiento` = `a`.`idAsiento`))) join `estado` `es` on((`e`.`idEstadoEntrada` = `es`.`idEstado`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_eventos_disponibles`
--

/*!50001 DROP VIEW IF EXISTS `vw_eventos_disponibles`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_eventos_disponibles` AS select `e`.`idEvento` AS `idEvento`,`e`.`nombre` AS `nombre`,`e`.`descripcion` AS `descripcion`,`e`.`fechaInicio` AS `fechaInicio`,`e`.`fechaFin` AS `fechaFin`,`e`.`ubicacion` AS `ubicacion`,`e`.`entradasDisponibles` AS `entradasDisponibles`,min(`te`.`precio`) AS `precioDesde`,`u`.`nombre` AS `organizador`,count(distinct `te`.`idTipoEntrada`) AS `tiposEntrada` from (((`evento` `e` join `usuario` `u` on((`e`.`idUsuario` = `u`.`idUsuario`))) left join `tipoentrada` `te` on((`e`.`idEvento` = `te`.`idEvento`))) join `estado` `es` on((`e`.`idEstadoEvento` = `es`.`idEstado`))) where ((`es`.`nombre` = 'Activo') and (`e`.`fechaFin` > now()) and (`e`.`entradasDisponibles` > 0)) group by `e`.`idEvento` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_validacion_entrada`
--

/*!50001 DROP VIEW IF EXISTS `vw_validacion_entrada`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_validacion_entrada` AS select `e`.`idEntrada` AS `idEntrada`,`e`.`codigoBarras` AS `codigoBarras`,`e`.`codigoQR` AS `codigoQR`,(case when (`e`.`fechaValidacion` is not null) then 'Ya validada' when (`es`.`nombre` = 'Cancelado') then 'Cancelada' when (`ev`.`fechaFin` < now()) then 'Evento finalizado' else 'Válida' end) AS `estadoValidacion`,`ev`.`nombre` AS `evento`,`ev`.`fechaInicio` AS `fechaInicio`,`ev`.`fechaFin` AS `fechaFin`,`te`.`nombre` AS `tipoEntrada`,concat(`a`.`fila`,' ',`a`.`numero`) AS `asiento` from (((((`entrada` `e` join `detallecompra` `dc` on((`e`.`idDetalleCompra` = `dc`.`idDetalleCompra`))) join `tipoentrada` `te` on((`dc`.`idTipoEntrada` = `te`.`idTipoEntrada`))) join `evento` `ev` on((`te`.`idEvento` = `ev`.`idEvento`))) left join `asiento` `a` on((`e`.`idAsiento` = `a`.`idAsiento`))) join `estado` `es` on((`e`.`idEstadoEntrada` = `es`.`idEstado`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-15 21:34:36
