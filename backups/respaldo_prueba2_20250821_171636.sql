-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: prueba2
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alertas`
--

DROP TABLE IF EXISTS `alertas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alertas` (
  `id_alerta` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `id_pedido` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_alerta`),
  KEY `fk_alertas_pedidos` (`id_pedido`),
  CONSTRAINT `fk_alertas_pedidos` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alertas`
--

LOCK TABLES `alertas` WRITE;
/*!40000 ALTER TABLE `alertas` DISABLE KEYS */;
/*!40000 ALTER TABLE `alertas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compras`
--

DROP TABLE IF EXISTS `compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compras` (
  `id_compra` int(11) NOT NULL AUTO_INCREMENT,
  `id_pastel` int(11) NOT NULL,
  `nombre_tarjeta` varchar(100) NOT NULL,
  `banco` enum('Agricola','Cuscatlan','Davivienda','Hipotecario') NOT NULL,
  `numero_tarjeta` varchar(16) NOT NULL,
  `fecha_expiracion` char(5) NOT NULL,
  `cvv` char(3) NOT NULL,
  `fecha_pago` datetime NOT NULL,
  PRIMARY KEY (`id_compra`),
  KEY `id_pastel` (`id_pastel`),
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`id_pastel`) REFERENCES `pasteles` (`id_pastel`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras`
--

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
INSERT INTO `compras` VALUES (8,1,'carlos noe umanzor','Hipotecario','2323 4567 6787 7','09/89','123','2025-08-19 18:04:11'),(9,8,'betsa','Davivienda','123456767899','09/56','123','2025-08-21 07:43:50'),(10,3,'123567890','Cuscatlan','1234567890','09/29','123','2025-08-21 08:37:28');
/*!40000 ALTER TABLE `compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario`
--

DROP TABLE IF EXISTS `inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventario` (
  `id_inventario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `unidad` varchar(20) NOT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `stock_minimo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ultima_actualizacion` date NOT NULL,
  `id_pedido` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_inventario`),
  KEY `fk_inventario_pedidos` (`id_pedido`),
  CONSTRAINT `fk_inventario_pedidos` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario`
--

LOCK TABLES `inventario` WRITE;
/*!40000 ALTER TABLE `inventario` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pago`
--

DROP TABLE IF EXISTS `pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pago` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `nombre_tarjeta` varchar(100) NOT NULL,
  `banco` varchar(50) NOT NULL,
  `numero_tarjeta` varchar(20) NOT NULL,
  `fecha_exp` varchar(7) NOT NULL,
  `cvv` varchar(4) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` datetime NOT NULL,
  PRIMARY KEY (`id_pago`),
  KEY `pedido_id` (`pedido_id`),
  CONSTRAINT `pago_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id_pedido`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pago`
--

LOCK TABLES `pago` WRITE;
/*!40000 ALTER TABLE `pago` DISABLE KEYS */;
INSERT INTO `pago` VALUES (16,23,'Carlo Noe Umanzor Membreño','carlos noe umanzor','Cuscatlan','1234567898765432','09/29','234',10.00,'2025-08-21 17:00:18');
/*!40000 ALTER TABLE `pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pasteles`
--

DROP TABLE IF EXISTS `pasteles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pasteles` (
  `id_pastel` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `disponibilidad` enum('disponible','limitado','no disponible') DEFAULT 'disponible',
  `badge` enum('nuevo','popular','sin azúcar') DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  PRIMARY KEY (`id_pastel`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pasteles`
--

LOCK TABLES `pasteles` WRITE;
/*!40000 ALTER TABLE `pasteles` DISABLE KEYS */;
INSERT INTO `pasteles` VALUES (1,'Chocolate Supremo','Delicioso pastel de chocolate con tres capas y relleno de crema.',28.24,'https://images.unsplash.com/photo-1578985545062-69928b1d9587','Chocolate','disponible','popular',10),(2,'Frutas Tropicales','Pan de vainilla con crema batida y decorado con frutas de temporada.',30.59,'https://cdn7.kiwilimon.com/brightcove/8188/640x640/8188.jpg.webp','Frutas','disponible','nuevo',15),(3,'Cheesecake de Zarzamora','Suave cheesecake con base de galleta y cubierta de zarzamora.',26.47,'https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b','Especiales','limitado',NULL,2),(4,'Red Velvet Clásico','Clásico pastel rojo con un ligero sabor a chocolate y crema de queso.',28.82,'https://i.ytimg.com/vi/Rb1qZ3tGNsQ/mqdefault.jpg','Especiales','disponible',NULL,10),(5,'Moka Intenso','Delicioso pastel con sabor a café y relleno de crema de moka.',24.12,'https://www.suqiee.com.mx/wp-content/uploads/2024/06/web-suqiee-reposteria-pastel-moca-6-1200x600.jpg','Especiales','limitado',NULL,3),(6,'Zanahoria Especial','Húmedo pastel de zanahoria con frosting de queso crema (opción sin azúcar).',27.06,'https://neruda.marriet.cl/wp-content/uploads/Torta-Zanahoria-001-1.jpg','Sin Azúcar','no disponible','sin azúcar',0),(7,'Vainilla Francesa','Clásico pastel de vainilla con frosting de mantequilla y decoración floral.',27.06,'https://images.unsplash.com/photo-1542826438-bd32f43d626f','Tres Leches','disponible',NULL,10),(8,'Limón Merengue','Pastel de limón con relleno de crema y topping de merengue italiano.',25.88,'https://www.sortirambnens.com/wp-content/uploads/2019/02/pastel-de-merengue.jpg','Especiales','disponible',NULL,10),(9,'Mini Chocolate','Mini pastel de chocolate para porciones individuales.',8.82,'https://www.suqiee.com.mx/wp-content/uploads/2022/12/web-pastel-de-chocolate-2-1200x600.jpg','Mini Pasteles','disponible',NULL,20),(10,'Tres Leches Tradicional','Pastel clásico de tres leches con crema y frutas.',23.53,'https://i.pinimg.com/736x/5a/20/d1/5a20d12cf5eabaf13339e7be4ac51846.jpg','Tres Leches','disponible',NULL,15);
/*!40000 ALTER TABLE `pasteles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_cliente` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `sabores` varchar(255) DEFAULT NULL,
  `tamano` varchar(50) DEFAULT NULL,
  `diseno` text DEFAULT NULL,
  `foto_referencia` varchar(255) DEFAULT NULL,
  `id_users` int(11) NOT NULL,
  PRIMARY KEY (`id_pedido`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
INSERT INTO `pedidos` VALUES (23,'Carlo Noe Umanzor Membreño','78788787','car@gmail.com','2025-08-24','Chocolate','16','Lindo','uploads/1755788387_descarga.jpg',0);
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id_users` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_pastel` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_users`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_pasteles` (`id_pastel`),
  CONSTRAINT `fk_users_pasteles` FOREIGN KEY (`id_pastel`) REFERENCES `pasteles` (`id_pastel`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (16,'car@gmail.com','$2y$10$YOFeneftXU9eYzDUVk1o7.4nbGflNvABnkeoxp7KbfSRrKmVtIvEi','Charlie','2025-08-19 23:39:00',NULL),(17,'adminchispitas@gmail.com','$2y$10$mier0z8XqE/K20TcUDAyI.VByoyjVpQhnOFpdERRVCAqCpwC8A39K','Noe','2025-08-20 00:03:04',NULL),(19,'yo@gmail.com','$2y$10$JNnZyowu00AXrOqkBqrxLejxiIwGZcuiurgR3mW4dSDmYwHc5w7HO','yo','2025-08-21 15:13:57',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-21  9:16:37
