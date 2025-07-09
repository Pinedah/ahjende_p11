-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: ahj_ende_pinedah
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
-- Table structure for table `cita`
--

DROP TABLE IF EXISTS `cita`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cita` (
  `id_cit` int(11) NOT NULL AUTO_INCREMENT,
  `cit_cit` date NOT NULL DEFAULT curdate(),
  `hor_cit` time NOT NULL DEFAULT '08:00:00',
  `nom_cit` varchar(255) DEFAULT NULL,
  `tel_cit` varchar(255) DEFAULT NULL,
  `id_eje2` int(11) DEFAULT NULL,
  `eli_cit` int(11) NOT NULL DEFAULT 1 COMMENT '1=visible, 0=oculta',
  PRIMARY KEY (`id_cit`),
  KEY `id_eje2` (`id_eje2`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cita`
--

LOCK TABLES `cita` WRITE;
/*!40000 ALTER TABLE `cita` DISABLE KEYS */;
INSERT INTO `cita` VALUES (1,'2025-07-03','09:00:00','EDITAR DESDE P2','555-1001',1,1),(3,'2025-07-03','14:15:00','Laura Martínez','555-1003',1,1),(4,'2025-07-03','16:45:00','Pedro Sánchez','555-1004',3,1),(7,'2025-07-03','13:15:00','Carlos Ruiz Mendoza','555-9012',3,1),(9,'2025-07-03','17:00:00','Roberto Díaz Torres','555-7890',2,1),(10,'2025-07-04','08:15:00','Elena Morales Castro','555-2468',3,1),(11,'2025-07-04','10:00:00','Fernando Vargas León','555-1357',4,1),(12,'2025-07-04','12:30:00','Patricia Herrera Vega','555-9753',1,1),(23,'2025-07-07','10:00:00','NUEVO NOMBRE PANKE','000000-111',4,0),(24,'2025-07-03','08:30:00','TEST CON HORARIO','00000-000',1,0),(25,'2025-07-03','20:30:00','TEST VIDEO AGREGAR HORARIO','555-5555',3,1),(26,'2025-07-16','19:00:00','NOMBRE','111',4,1),(37,'2025-07-05','11:11:00','panke','111',2,1),(58,'2025-07-04','11:11:00','pankenuevo','1111-111',4,0),(59,'2025-07-07','11:11:00','francisco TEST','1111-111',1,0),(60,'2025-07-07','09:00:00','test','0000',2,1),(61,'2025-07-07','08:00:00','NUEVO TEST',NULL,NULL,0),(63,'2025-07-08','08:00:00','PANKE',NULL,NULL,0),(64,'2025-07-08','11:11:00','NUEVA CITA FAXINAAR','111-111',1,0),(65,'2025-07-08','08:10:00','NUEVA CITA VIDEO CABIOOO','1111-111',4,0),(66,'2025-07-08','08:00:00','nueva cita pinedah',NULL,NULL,0);
/*!40000 ALTER TABLE `cita` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ejecutivo`
--

DROP TABLE IF EXISTS `ejecutivo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ejecutivo` (
  `id_eje` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nom_eje` varchar(100) NOT NULL,
  `tel_eje` varchar(15) NOT NULL,
  `eli_eje` int(11) NOT NULL DEFAULT 1 COMMENT '1=visible, 0=oculto',
  `id_padre` int(11) DEFAULT NULL COMMENT 'FK para relación jerárquica',
  PRIMARY KEY (`id_eje`),
  KEY `idx_eli_eje` (`eli_eje`),
  KEY `idx_id_padre` (`id_padre`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ejecutivo`
--

LOCK TABLES `ejecutivo` WRITE;
/*!40000 ALTER TABLE `ejecutivo` DISABLE KEYS */;
INSERT INTO `ejecutivo` VALUES (1,'Juan Carlos Pérez','555-0123',0,NULL),(2,'María Fernanda López','555-0456',1,7),(3,'Roberto Gonzálezzzzzz','555-0789',1,2),(4,'Francisco Pineda','555-0789',0,NULL),(5,'Ejecutivo Prueba','555-1234',1,7),(6,'Fatima Nava','555-111',1,4),(7,'NUEVO EJECUTIVO VIDEO','000-111',1,NULL),(8,'EJECUTIVO VIDEO 2','0000',1,4);
/*!40000 ALTER TABLE `ejecutivo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historial_cita`
--

DROP TABLE IF EXISTS `historial_cita`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historial_cita` (
  `id_his_cit` int(11) NOT NULL AUTO_INCREMENT,
  `fec_his_cit` datetime NOT NULL DEFAULT current_timestamp(),
  `res_his_cit` varchar(100) NOT NULL,
  `mov_his_cit` enum('alta','cambio','baja') NOT NULL,
  `des_his_cit` text NOT NULL,
  `id_cit11` int(11) NOT NULL,
  PRIMARY KEY (`id_his_cit`),
  KEY `idx_id_cit11` (`id_cit11`),
  KEY `idx_fec_his_cit` (`fec_his_cit`),
  CONSTRAINT `historial_cita_ibfk_1` FOREIGN KEY (`id_cit11`) REFERENCES `cita` (`id_cit`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_cita`
--

LOCK TABLES `historial_cita` WRITE;
/*!40000 ALTER TABLE `historial_cita` DISABLE KEYS */;
INSERT INTO `historial_cita` VALUES (1,'2025-07-08 11:36:25','María Fernanda López','cambio','Se modificó NOM CIT de \'TEST VIDEO\' a \'NUEVO NOMBRE PANKE\' en la cita \'TEST VIDEO\'',23),(2,'2025-07-08 11:36:38','María Fernanda López','cambio','Se modificó TEL CIT de \'111-111\' a \'000000-111\' en la cita \'NUEVO NOMBRE PANKE\'',23),(3,'2025-07-08 11:38:21','Roberto González','alta','Se creó nueva cita: \'NUEVA CITA FAXINAAR\'',64),(4,'2025-07-08 11:40:36','Roberto González','cambio','Se modificó HOR CIT de \'08:00:00\' a \'11:11\' en la cita \'NUEVA CITA FAXINAAR\'',64),(5,'2025-07-08 11:40:41','Roberto González','cambio','Se modificó TEL CIT de \'(vacío)\' a \'111-111\' en la cita \'NUEVA CITA FAXINAAR\'',64),(6,'2025-07-08 11:40:41','Juan Carlos Pérez','cambio','Se modificó ID EJE2 de \'(vacío)\' a \'1\' en la cita \'NUEVA CITA FAXINAAR\'',64),(7,'2025-07-08 12:14:55','Roberto González','baja','Se eliminó (ocultó) la cita \'NUEVA CITA FAXINAAR\'',64),(8,'2025-07-08 12:52:59','María Fernanda López','baja','Se eliminó (ocultó) la cita \'NUEVO NOMBRE PANKE\'',23),(9,'2025-07-08 15:53:10','Ejecutivo Prueba','alta','Se creó nueva cita: \'NUEVA CITA VIDEO\'',65),(10,'2025-07-08 15:53:10','Francisco Pineda','cambio','Se modificó ID EJE2 de \'(vacío)\' a \'4\' en la cita \'NUEVA CITA VIDEO\'',65),(11,'2025-07-08 15:53:15','María Fernanda López','cambio','Se modificó HOR CIT de \'08:00:00\' a \'8:10\' en la cita \'NUEVA CITA VIDEO\'',65),(12,'2025-07-08 15:53:43','Francisco Pineda','cambio','Se modificó NOM CIT de \'NUEVA CITA VIDEO\' a \'NUEVA CITA VIDEO CABIOOO\' en la cita \'NUEVA CITA VIDEO\'',65),(13,'2025-07-08 15:54:09','Juan Carlos Pérez','alta','Se creó nueva cita: \'nueva cita pinedah\'',66),(14,'2025-07-08 15:54:25','María Fernanda López','baja','Se eliminó (ocultó) la cita \'NUEVA CITA VIDEO CABIOOO\'',65),(15,'2025-07-08 15:55:07','Fatima Nava','baja','Se eliminó (ocultó) la cita \'nueva cita pinedah\'',66),(16,'2025-07-08 15:55:39','Roberto Gonzálezzzzzz','baja','Se eliminó (ocultó) la cita \'TEST CON HORARIO\'',24);
/*!40000 ALTER TABLE `historial_cita` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-09 10:40:51
