CREATE DATABASE  IF NOT EXISTS `mdb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `mdb`;
-- MySQL dump 10.13  Distrib 8.4.5, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: mdb
-- ------------------------------------------------------
-- Server version	8.4.5

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
-- Table structure for table `book_equipment`
--

DROP TABLE IF EXISTS `book_equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `book_equipment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `book_qty` int DEFAULT NULL,
  `reservation_id` int NOT NULL,
  `equipment_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_book_equipment_reservation1_idx` (`reservation_id`),
  KEY `fk_book_equipment_equipment1_idx` (`equipment_id`),
  CONSTRAINT `fk_book_equipment_equipment1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`),
  CONSTRAINT `fk_book_equipment_reservation1` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `book_equipment`
--

LOCK TABLES `book_equipment` WRITE;
/*!40000 ALTER TABLE `book_equipment` DISABLE KEYS */;
/*!40000 ALTER TABLE `book_equipment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `broken`
--

DROP TABLE IF EXISTS `broken`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `broken` (
  `id` int NOT NULL AUTO_INCREMENT,
  `broken_qty` int DEFAULT NULL,
  `equipment_id` int NOT NULL,
  `is_technical_officer` tinyint(1) DEFAULT NULL,
  `is_hod` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_broken_equipment1_idx` (`equipment_id`),
  CONSTRAINT `fk_broken_equipment1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `broken`
--

LOCK TABLES `broken` WRITE;
/*!40000 ALTER TABLE `broken` DISABLE KEYS */;
/*!40000 ALTER TABLE `broken` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment`
--

DROP TABLE IF EXISTS `equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(45) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `total_qty` int DEFAULT NULL,
  `simultaneous_users` int DEFAULT NULL,
  `sterilization_required` enum('YES','NO') DEFAULT NULL,
  `reservation_required` enum('YES','NO') DEFAULT NULL,
  `added_datatime` datetime DEFAULT NULL,
  `description` text,
  `is_hod_checked` tinyint(1) DEFAULT NULL,
  `updated_details_datetime` datetime DEFAULT NULL,
  `image_path` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=210 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment`
--

LOCK TABLES `equipment` WRITE;
/*!40000 ALTER TABLE `equipment` DISABLE KEYS */;
/*!40000 ALTER TABLE `equipment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment_has_location`
--

DROP TABLE IF EXISTS `equipment_has_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipment_has_location` (
  `equipment_id` int DEFAULT NULL,
  `location_id` int DEFAULT NULL,
  KEY `fk_equipment_has_location_location1_idx` (`location_id`),
  KEY `fk_equipment_has_location_equipment1_idx` (`equipment_id`),
  CONSTRAINT `fk_equipment_has_location_equipment1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`),
  CONSTRAINT `fk_equipment_has_location_location1` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment_has_location`
--

LOCK TABLES `equipment_has_location` WRITE;
/*!40000 ALTER TABLE `equipment_has_location` DISABLE KEYS */;
/*!40000 ALTER TABLE `equipment_has_location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lab_user`
--

DROP TABLE IF EXISTS `lab_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lab_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `who_approved` int DEFAULT NULL,
  `first_name` varchar(45) DEFAULT NULL,
  `last_name` varchar(45) DEFAULT NULL,
  `university_id` varchar(45) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `password_user` text,
  `img_path` text,
  `join_datetime` datetime DEFAULT NULL,
  `approved_datetime` datetime DEFAULT NULL,
  `verification_code` varchar(8) DEFAULT NULL,
  `remember_token` text,
  `details_updated_datetime` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_lab_user_lab_user1_idx` (`who_approved`),
  CONSTRAINT `fk_lab_user_lab_user1` FOREIGN KEY (`who_approved`) REFERENCES `lab_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lab_user`
--

LOCK TABLES `lab_user` WRITE;
/*!40000 ALTER TABLE `lab_user` DISABLE KEYS */;
INSERT INTO `lab_user` VALUES (1,1,'Kamal','Perera','HOD','hod@gmail.com','0711234567','$2y$10$KHCrwCqttt5Kd8aj2UXnpOTu15vv6lgecZGL5UFYweExTAC.21iRa','assets/profile_images/student_4_1773912181.jpg','2026-03-09 21:25:26','2026-03-09 21:25:26',NULL,'$2y$10$.9dISYKpoDq/NtZQOKbyj.LZKpPvoEZI6mTAzCmgXMxi81yy3xMCG','2026-03-10 14:31:40',1);
/*!40000 ALTER TABLE `lab_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lab_user_has_role`
--

DROP TABLE IF EXISTS `lab_user_has_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lab_user_has_role` (
  `lab_user_id` int NOT NULL,
  `role_id` int NOT NULL,
  PRIMARY KEY (`lab_user_id`,`role_id`),
  KEY `fk_lab_user_has_role_role1_idx` (`role_id`),
  KEY `fk_lab_user_has_role_lab_user1_idx` (`lab_user_id`),
  CONSTRAINT `fk_lab_user_has_role_lab_user1` FOREIGN KEY (`lab_user_id`) REFERENCES `lab_user` (`id`),
  CONSTRAINT `fk_lab_user_has_role_role1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lab_user_has_role`
--

LOCK TABLES `lab_user_has_role` WRITE;
/*!40000 ALTER TABLE `lab_user_has_role` DISABLE KEYS */;
INSERT INTO `lab_user_has_role` VALUES (1,4);
/*!40000 ALTER TABLE `lab_user_has_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `location`
--

DROP TABLE IF EXISTS `location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `location` (
  `id` int NOT NULL AUTO_INCREMENT,
  `location` varchar(50) DEFAULT NULL,
  `is_room` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `location`
--

LOCK TABLES `location` WRITE;
/*!40000 ALTER TABLE `location` DISABLE KEYS */;
INSERT INTO `location` VALUES (13,'A12-001',NULL),(14,'A12-004',NULL),(15,'A12-006',NULL),(16,'A11-101 (Special Student Lab)',NULL),(17,'A11-108 (Instrument Lab)',NULL),(18,'A05-002 (Teaching Lab)',NULL);
/*!40000 ALTER TABLE `location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` text,
  `created_datetime` datetime DEFAULT NULL,
  `owner_of_notification` int NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `need_approval` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_notification_lab_user1_idx` (`owner_of_notification`),
  CONSTRAINT `fk_notification_lab_user1` FOREIGN KEY (`owner_of_notification`) REFERENCES `lab_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification`
--

LOCK TABLES `notification` WRITE;
/*!40000 ALTER TABLE `notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `practical_finished_hod_notify_and_approval`
--

DROP TABLE IF EXISTS `practical_finished_hod_notify_and_approval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `practical_finished_hod_notify_and_approval` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` text,
  `status` enum('read','unread') DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT NULL,
  `practical_finished_logbook_id` int DEFAULT NULL,
  `rejection_reason` text,
  `approved_or_rejected_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_practical_finished_hod_notify_and_approval_practical_fin_idx` (`practical_finished_logbook_id`),
  CONSTRAINT `fk_practical_finished_hod_notify_and_approval_practical_finis1` FOREIGN KEY (`practical_finished_logbook_id`) REFERENCES `practical_finished_logbook` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `practical_finished_hod_notify_and_approval`
--

LOCK TABLES `practical_finished_hod_notify_and_approval` WRITE;
/*!40000 ALTER TABLE `practical_finished_hod_notify_and_approval` DISABLE KEYS */;
/*!40000 ALTER TABLE `practical_finished_hod_notify_and_approval` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `practical_finished_logbook`
--

DROP TABLE IF EXISTS `practical_finished_logbook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `practical_finished_logbook` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reservation_id` int DEFAULT NULL,
  `student_id` int DEFAULT NULL,
  `supervisor_id` int DEFAULT NULL,
  `who_technicalOfficer_id` int DEFAULT NULL,
  `any_comment` text,
  `img_path1` text,
  `img_path2` text,
  `img_path3` text,
  `img_path4` text,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_practical_finished_logbook_reservation1_idx` (`reservation_id`),
  KEY `fk_practical_finished_logbook_lab_user1_idx` (`student_id`),
  KEY `fk_practical_finished_logbook_lab_user2_idx` (`supervisor_id`),
  KEY `fk_practical_finished_logbook_lab_user3_idx` (`who_technicalOfficer_id`),
  CONSTRAINT `fk_practical_finished_logbook_lab_user1` FOREIGN KEY (`student_id`) REFERENCES `lab_user` (`id`),
  CONSTRAINT `fk_practical_finished_logbook_lab_user2` FOREIGN KEY (`supervisor_id`) REFERENCES `lab_user` (`id`),
  CONSTRAINT `fk_practical_finished_logbook_lab_user3` FOREIGN KEY (`who_technicalOfficer_id`) REFERENCES `lab_user` (`id`),
  CONSTRAINT `fk_practical_finished_logbook_reservation1` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `practical_finished_logbook`
--

LOCK TABLES `practical_finished_logbook` WRITE;
/*!40000 ALTER TABLE `practical_finished_logbook` DISABLE KEYS */;
/*!40000 ALTER TABLE `practical_finished_logbook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `practical_finished_supervisor_notify_and_approval`
--

DROP TABLE IF EXISTS `practical_finished_supervisor_notify_and_approval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `practical_finished_supervisor_notify_and_approval` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'unread',
  `is_approved` tinyint(1) DEFAULT NULL COMMENT '1=approved, 0=rejected, NULL=pending',
  `practical_finished_logbook_id` int NOT NULL,
  `rejection_reason` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `approved_or_rejected_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_logbook_supervisor` (`practical_finished_logbook_id`),
  CONSTRAINT `practical_finished_supervisor_notify_and_approval_ibfk_1` FOREIGN KEY (`practical_finished_logbook_id`) REFERENCES `practical_finished_logbook` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `practical_finished_supervisor_notify_and_approval`
--

LOCK TABLES `practical_finished_supervisor_notify_and_approval` WRITE;
/*!40000 ALTER TABLE `practical_finished_supervisor_notify_and_approval` DISABLE KEYS */;
/*!40000 ALTER TABLE `practical_finished_supervisor_notify_and_approval` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `practical_finished_technicalofficer_notify_and_approval`
--

DROP TABLE IF EXISTS `practical_finished_technicalofficer_notify_and_approval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `practical_finished_technicalofficer_notify_and_approval` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` text,
  `status` enum('read','unread') DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT NULL,
  `practical_finished_logbook_id` int DEFAULT NULL,
  `rejection_reason` text,
  `approved_or_rejected_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_practical_finished_hod_notify_and_approval_practical_fin_idx` (`practical_finished_logbook_id`),
  CONSTRAINT `fk_practical_finished_hod_notify_and_approval_practical_finis10` FOREIGN KEY (`practical_finished_logbook_id`) REFERENCES `practical_finished_logbook` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `practical_finished_technicalofficer_notify_and_approval`
--

LOCK TABLES `practical_finished_technicalofficer_notify_and_approval` WRITE;
/*!40000 ALTER TABLE `practical_finished_technicalofficer_notify_and_approval` DISABLE KEYS */;
/*!40000 ALTER TABLE `practical_finished_technicalofficer_notify_and_approval` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reject_reason`
--

DROP TABLE IF EXISTS `reject_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reject_reason` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reason` text,
  `reservation_id` int NOT NULL,
  `who_rejected` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_reject_reason_reservation1_idx` (`reservation_id`),
  KEY `fk_reject_reason_lab_user1_idx` (`who_rejected`),
  CONSTRAINT `fk_reject_reason_lab_user1` FOREIGN KEY (`who_rejected`) REFERENCES `lab_user` (`id`),
  CONSTRAINT `fk_reject_reason_reservation1` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reject_reason`
--

LOCK TABLES `reject_reason` WRITE;
/*!40000 ALTER TABLE `reject_reason` DISABLE KEYS */;
/*!40000 ALTER TABLE `reject_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repair`
--

DROP TABLE IF EXISTS `repair`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `repair` (
  `id` int NOT NULL AUTO_INCREMENT,
  `repair_qty` int DEFAULT NULL,
  `equipment_id` int NOT NULL,
  `is_technical_officer` tinyint(1) DEFAULT NULL,
  `is_hod` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_repair_equipment1_idx` (`equipment_id`),
  CONSTRAINT `fk_repair_equipment1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repair`
--

LOCK TABLES `repair` WRITE;
/*!40000 ALTER TABLE `repair` DISABLE KEYS */;
/*!40000 ALTER TABLE `repair` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reservation_id` varchar(20) DEFAULT NULL,
  `created_datetime` datetime DEFAULT NULL,
  `student_id` int DEFAULT NULL,
  `supervisor_id` int DEFAULT NULL,
  `technical_officer_id` int DEFAULT NULL,
  `location_id` int NOT NULL,
  `request_date` date DEFAULT NULL,
  `continue_days` int NOT NULL DEFAULT '1',
  `comment` text,
  `updated_details_by_student` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_reservation_lab_user1_idx` (`student_id`),
  KEY `fk_reservation_lab_user2_idx` (`supervisor_id`),
  KEY `fk_reservation_lab_user3_idx` (`technical_officer_id`),
  KEY `fk_reservation_location1_idx` (`location_id`),
  CONSTRAINT `fk_reservation_lab_user1` FOREIGN KEY (`student_id`) REFERENCES `lab_user` (`id`),
  CONSTRAINT `fk_reservation_lab_user2` FOREIGN KEY (`supervisor_id`) REFERENCES `lab_user` (`id`),
  CONSTRAINT `fk_reservation_lab_user3` FOREIGN KEY (`technical_officer_id`) REFERENCES `lab_user` (`id`),
  CONSTRAINT `fk_reservation_location1` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation`
--

LOCK TABLES `reservation` WRITE;
/*!40000 ALTER TABLE `reservation` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'student'),(2,'supervisor'),(3,'technical_officer'),(4,'hod'),(5,'admin');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supervisor_assigned_student`
--

DROP TABLE IF EXISTS `supervisor_assigned_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supervisor_assigned_student` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `supervisor_id_or_hod_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_supervisor_assigned_student_lab_user1_idx` (`student_id`),
  KEY `fk_supervisor_assigned_student_lab_user2_idx` (`supervisor_id_or_hod_id`),
  CONSTRAINT `fk_supervisor_assigned_student_lab_user1` FOREIGN KEY (`student_id`) REFERENCES `lab_user` (`id`),
  CONSTRAINT `fk_supervisor_assigned_student_lab_user2` FOREIGN KEY (`supervisor_id_or_hod_id`) REFERENCES `lab_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supervisor_assigned_student`
--

LOCK TABLES `supervisor_assigned_student` WRITE;
/*!40000 ALTER TABLE `supervisor_assigned_student` DISABLE KEYS */;
/*!40000 ALTER TABLE `supervisor_assigned_student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_session`
--

DROP TABLE IF EXISTS `user_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_session` (
  `id` int NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `lab_user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_session_lab_user1_idx` (`lab_user_id`),
  CONSTRAINT `fk_user_session_lab_user1` FOREIGN KEY (`lab_user_id`) REFERENCES `lab_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=652 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_session`
--

LOCK TABLES `user_session` WRITE;
/*!40000 ALTER TABLE `user_session` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'mdb'
--

--
-- Dumping routines for database 'mdb'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-22 22:57:53
