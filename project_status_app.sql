/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.4.3-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: project_status_app
-- ------------------------------------------------------
-- Server version	11.4.3-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_09_05_041607_create_projects_table',1),
(5,'2026_09_05_041608_create_project_assignment_logs_table',1),
(6,'2026_09_05_041609_add_role_to_users_table',1),
(7,'2026_09_05_133709_add_year_to_projects_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_assignment_logs`
--

DROP TABLE IF EXISTS `project_assignment_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_assignment_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  `assigned_by` bigint(20) unsigned NOT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_assignment_logs_project_id_foreign` (`project_id`),
  KEY `project_assignment_logs_assigned_by_foreign` (`assigned_by`),
  KEY `project_assignment_logs_assigned_to_foreign` (`assigned_to`),
  CONSTRAINT `project_assignment_logs_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  CONSTRAINT `project_assignment_logs_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_assignment_logs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_assignment_logs`
--

LOCK TABLES `project_assignment_logs` WRITE;
/*!40000 ALTER TABLE `project_assignment_logs` DISABLE KEYS */;
INSERT INTO `project_assignment_logs` VALUES
(17,50,'ASSIGNED',1,14,NULL,'2026-09-05 13:42:28'),
(18,49,'ASSIGNED',1,14,NULL,'2026-09-05 13:42:52'),
(19,48,'ASSIGNED',1,14,NULL,'2026-09-05 13:43:27'),
(20,17,'ASSIGNED',1,9,NULL,'2026-09-05 13:45:05'),
(21,31,'ASSIGNED',1,9,NULL,'2026-09-05 13:46:33'),
(22,41,'ASSIGNED',1,9,NULL,'2026-09-05 13:47:39'),
(23,49,'REASSIGNED',1,9,NULL,'2026-09-05 13:48:11'),
(24,32,'ASSIGNED',1,9,NULL,'2026-09-05 13:49:18'),
(25,42,'ASSIGNED',1,12,NULL,'2026-09-05 13:50:01'),
(26,18,'ASSIGNED',1,13,NULL,'2026-09-05 13:50:55'),
(27,22,'ASSIGNED',1,13,NULL,'2026-09-05 13:51:10'),
(28,24,'ASSIGNED',1,13,NULL,'2026-09-05 13:51:49'),
(29,27,'ASSIGNED',1,13,NULL,'2026-09-05 13:52:11'),
(30,34,'ASSIGNED',1,13,NULL,'2026-09-05 13:52:46'),
(31,36,'ASSIGNED',1,13,NULL,'2026-09-05 13:53:13'),
(32,38,'ASSIGNED',1,13,NULL,'2026-09-05 13:53:38'),
(33,39,'ASSIGNED',1,13,NULL,'2026-09-05 13:54:09'),
(34,40,'ASSIGNED',1,13,NULL,'2026-09-05 13:54:42'),
(35,48,'REASSIGNED',1,13,NULL,'2026-09-05 13:55:06'),
(36,19,'ASSIGNED',1,15,NULL,'2026-09-05 13:57:14'),
(37,25,'ASSIGNED',1,15,NULL,'2026-09-05 13:57:33'),
(38,43,'ASSIGNED',1,11,NULL,'2026-09-05 13:58:13'),
(39,44,'ASSIGNED',1,11,NULL,'2026-09-05 13:58:27'),
(40,45,'ASSIGNED',1,11,NULL,'2026-09-05 13:58:46'),
(41,46,'ASSIGNED',1,11,NULL,'2026-09-05 13:59:02'),
(42,20,'ASSIGNED',1,10,NULL,'2026-09-05 14:01:16'),
(43,21,'ASSIGNED',1,10,NULL,'2026-09-05 14:01:42'),
(44,23,'ASSIGNED',1,10,NULL,'2026-09-05 14:02:06'),
(45,26,'ASSIGNED',1,10,NULL,'2026-09-05 14:02:34'),
(46,28,'ASSIGNED',1,10,NULL,'2026-09-05 14:02:59'),
(47,29,'ASSIGNED',1,10,NULL,'2026-09-05 14:03:31'),
(48,30,'ASSIGNED',1,10,NULL,'2026-09-05 14:03:59'),
(49,33,'ASSIGNED',1,10,NULL,'2026-09-05 14:04:24'),
(50,35,'ASSIGNED',1,10,NULL,'2026-09-05 14:04:50'),
(51,37,'ASSIGNED',1,10,NULL,'2026-09-05 14:05:21'),
(52,47,'ASSIGNED',1,10,NULL,'2026-09-05 14:05:42'),
(53,50,'REASSIGNED',1,10,NULL,'2026-09-05 14:06:00'),
(54,43,'COMPLETED',1,11,NULL,'2026-09-07 00:40:42'),
(55,19,'REASSIGNED',1,9,NULL,'2026-09-07 12:30:46'),
(56,25,'REASSIGNED',1,9,NULL,'2026-09-07 12:31:03');
/*!40000 ALTER TABLE `project_assignment_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_code` varchar(255) NOT NULL,
  `year` smallint(5) unsigned NOT NULL DEFAULT 2026,
  `work_code` varchar(255) NOT NULL,
  `on_road` varchar(255) NOT NULL,
  `start_road` varchar(255) NOT NULL,
  `end_road` varchar(255) NOT NULL,
  `pipe_type` varchar(255) NOT NULL,
  `pipe_diameter` decimal(10,2) NOT NULL,
  `pipe_length` decimal(10,2) NOT NULL,
  `received_date` date NOT NULL,
  `project_amount` decimal(14,2) DEFAULT NULL,
  `request_number` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'PENDING',
  `assignee_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `projects_project_code_unique` (`project_code`),
  KEY `projects_assignee_id_foreign` (`assignee_id`),
  KEY `projects_created_by_foreign` (`created_by`),
  KEY `projects_status_index` (`status`),
  KEY `projects_year_index` (`year`),
  CONSTRAINT `projects_assignee_id_foreign` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES
(17,'P25-09_04-H160-014',2025,'PDO-25-0​925THYDA','Sw.445K','414K','494K','H',160.00,448.40,'2026-07-23',NULL,NULL,'IN_PROGRESS',9,1,NULL,'2026-09-05 12:38:31','2026-09-07 12:33:49'),
(18,'P25-08_10-H90-0​25',2025,'PDO-25-1617THYDA','Sw.440K-10','430K','Block','H',90.00,165.20,'2026-09-03',NULL,NULL,'IN_PROGRESS',13,1,NULL,'2026-09-05 12:50:47','2026-09-05 13:50:55'),
(19,'P25-08_10-H110-0​29',2025,'PDO-25-1623THYDA','Sw.430K-79','430K-74','Block','H',110.00,165.20,'2026-09-03',NULL,NULL,'IN_PROGRESS',9,1,NULL,'2026-09-05 12:52:18','2026-09-07 12:30:45'),
(20,'P25-08_10-H160-0​22',2025,'PDO-25-1641THYDA','Sw.440K-6','440K-3','Block','H',160.00,224.20,'2026-08-24',NULL,NULL,'IN_PROGRESS',10,1,NULL,'2026-09-05 12:53:25','2026-09-05 14:01:16'),
(21,'P25-08_10-H160-0​23',2025,'PDO-25-1642THYDA','Sw.440K-13','440K','Block','H',160.00,259.60,'2026-08-24',NULL,NULL,'IN_PROGRESS',10,1,NULL,'2026-09-05 12:54:35','2026-09-05 14:01:42'),
(22,'P25-08_10-H225-00​8',2025,'PDO-25-1253THYDA','Sw.440K','430K','Block','H',225.00,861.40,'2026-09-03',NULL,NULL,'IN_PROGRESS',13,1,NULL,'2026-09-05 12:55:31','2026-09-05 13:51:15'),
(23,'P25-12_04-H90-0​11',2025,'PDO-25-1819THYDA','Sw.6PS,4PS','4AD','Block','H',90.00,247.80,'2026-08-24',NULL,NULL,'IN_PROGRESS',10,1,NULL,'2026-09-05 12:56:36','2026-09-05 14:02:06'),
(24,'P25-12_04-H110-00​5',2025,'PDO-25-1824THYDA','Sw.26PS,21PS','non','non','H',110.00,318.60,'2026-09-03',NULL,NULL,'IN_PROGRESS',13,1,NULL,'2026-09-05 12:57:48','2026-09-05 13:51:49'),
(25,'P25-12_04-H110-00​6',2025,'PDO-25-1825THYDA','Sw.15PS','4AD','Block','H',110.00,82.60,'2026-09-03',NULL,NULL,'IN_PROGRESS',9,1,NULL,'2026-09-05 12:59:36','2026-09-07 12:31:03'),
(26,'P25-12_04-H110-00​8',2025,'PDO-25-1827THYDA','Sw.8PS-2','8PS-1','Block','H',110.00,59.00,'2026-08-28',NULL,NULL,'IN_PROGRESS',10,1,NULL,'2026-09-05 13:00:37','2026-09-05 14:02:34'),
(27,'P25-12_04-H90-00​2',2025,'PDO-25-1810THYDA','Sw.49PS','non','non','H',90.00,165.20,'2026-09-03',NULL,NULL,'IN_PROGRESS',13,1,NULL,'2026-09-05 13:02:59','2026-09-05 13:52:11'),
(28,'P25-12_04-H90-00​7',2025,'PDO-25-1815THYDA','Sw.19S,21PS','26PS','Block','H',90.00,236.00,'2026-08-24',NULL,NULL,'IN_PROGRESS',10,1,NULL,'2026-09-05 13:03:57','2026-09-05 14:02:59'),
(29,'P25-12_04-H90-00​8',2025,'PDO-25-1816THYDA','Sw.17PS','4AD','Block','H',90.00,153.40,'2026-08-24',NULL,NULL,'IN_PROGRESS',10,1,NULL,'2026-09-05 13:06:45','2026-09-05 14:03:31'),
(30,'P25-12_04-H90-00​9',2025,'PDO-25-1817THYDA','Sw.8PS-1','8PS-2','Block','H',90.00,106.20,'2026-08-24',NULL,NULL,'IN_PROGRESS',10,1,NULL,'2026-09-05 13:16:08','2026-09-05 14:03:59'),
(31,'P25-12_04-H90-0​10',2025,'PDO-25-1818THYDA','Sw.1PS,1PS-2','non','non','H',90.00,342.20,'2026-07-28',NULL,NULL,'IN_PROGRESS',9,1,NULL,'2026-09-05 13:17:08','2026-09-05 13:46:40'),
(32,'P25-08_10-H110-0​36',2025,'PDO-25-1630THYDA','Sw.430K-45','430K-56','Block','H',110.00,389.40,'2026-07-28',NULL,NULL,'IN_PROGRESS',9,1,NULL,'2026-09-05 13:18:08','2026-09-05 13:49:18'),
(33,'P25-08_10-H110-0​43',2025,'PDO-25-1637THYDA','Sw.430K-3','non','non','H',110.00,165.20,'2026-08-28',NULL,NULL,'IN_PROGRESS',10,1,NULL,'2026-09-05 13:20:42','2026-09-05 14:04:24'),
(34,'P25-08_10-H110-0​45',2025,'PDO-25-1639THYDA','Sw.181K-2','430k-1','Block','H',110.00,47.20,'2026-09-03',NULL,NULL,'IN_PROGRESS',13,1,NULL,'2026-09-05 13:21:39','2026-09-05 13:52:46'),
(35,'P25-08_10-H160-0​26',2025,'PDO-25-1645THYDA','Sw.430K-8','430K-5','Block','H',160.00,153.40,'2026-08-24',NULL,NULL,'IN_PROGRESS',10,1,NULL,'2026-09-05 13:22:35','2026-09-05 14:04:50'),
(36,'P25-08_10-H160-0​29',2025,'PDO-25-1648THYDA','Sw.7AD,430K-3','non','non','H',160.00,283.20,'2026-09-03',NULL,NULL,'IN_PROGRESS',13,1,NULL,'2026-09-05 13:23:34','2026-09-05 13:53:13'),
(37,'P25-08_10-H225-0​15',2025,'PDO-25-1656THYDA','Sw.430K','430K-73','430K-11','H',225.00,318.60,'2026-08-28',NULL,NULL,'IN_PROGRESS',10,1,NULL,'2026-09-05 13:24:29','2026-09-05 14:05:21'),
(38,'P25-08_10-H225-0​16',2025,'PDO-25-1657THYDA','Sw.430K-11','430K-56','Block','H',225.00,483.80,'2026-09-03',NULL,NULL,'IN_PROGRESS',13,1,NULL,'2026-09-05 13:25:46','2026-09-05 13:53:38'),
(39,'P25-08_10-H225-0​17',2025,'PDO-25-1658THYDA','Sw.430K-11','430K-56','Block','H',225.00,436.60,'2026-09-03',NULL,NULL,'IN_PROGRESS',13,1,NULL,'2026-09-05 13:26:33','2026-09-05 13:54:09'),
(40,'P25-12_04-H225-00​5',2025,'PDO-25-1856THYDA','Sw.28PS','NR42','Block','H',225.00,991.20,'2026-09-03',NULL,NULL,'IN_PROGRESS',13,1,NULL,'2026-09-05 13:27:22','2026-09-05 13:54:42'),
(41,'P25-12_04-H225-00​7',2025,'PDO-25-1858THYDA','Sw.49PS,53PS','28PS','Block','H',225.00,330.40,'2026-08-24',NULL,NULL,'IN_PROGRESS',9,1,NULL,'2026-09-05 13:28:17','2026-09-05 13:47:39'),
(42,'P25-07_01-CWO100-00​1',2025,'PDO-25-2561THYDA','Sw.NR.3 At Point (Km1+000)','non','non','EX',100.00,0.00,'2026-08-28',NULL,NULL,'IN_PROGRESS',12,1,NULL,'2026-09-05 13:29:34','2026-09-05 13:50:01'),
(43,'P25-04_03-GDL-00​1',2025,'PDO-25-1807THYDA','Install Data Logger & Transmitter Sw.110','non','non','EX',0.00,0.00,'2026-07-27',1.00,'1','COMPLETED',11,1,'2026-09-07 00:40:42','2026-09-05 13:30:54','2026-09-07 00:40:42'),
(44,'P25-05_06-GDL-00​2',2025,'PDO-25-2025THYDA','Install Data Logger & Transmitter Sw.598B','non','non','EX',0.00,0.00,'2026-07-27',NULL,NULL,'IN_PROGRESS',11,1,NULL,'2026-09-05 13:34:23','2026-09-05 13:58:27'),
(45,'P25-05_06-GDL-00​3',2025,'PDO-25-1668THYDA','Install Data Logger & Transmitter Sw.598A','non','non','EX',0.00,0.00,'2026-07-27',NULL,NULL,'IN_PROGRESS',11,1,NULL,'2026-09-05 13:35:17','2026-09-05 13:58:46'),
(46,'P25-05_06-GDL-00​4',2025,'PDO-25-2200THYDA','Install Data Logger & Transmitter Sw.600R','non','non','EX',0.00,0.00,'2026-07-27',NULL,NULL,'IN_PROGRESS',11,1,NULL,'2026-09-05 13:36:12','2026-09-05 14:00:15'),
(47,'P25-08_10-H63-0​52',2025,'PDO-25-2981THYDA','Sw.430k-7','non','non','H',63.00,100.00,'2026-08-24',NULL,NULL,'IN_PROGRESS',10,1,NULL,'2026-09-05 13:37:03','2026-09-05 14:05:42'),
(48,'P25-08_10-H110-0​61',2025,'PDO-25-2982THYDA','Sw.430k-7','430k','Block','H',110.00,118.00,'2026-09-03',NULL,NULL,'IN_PROGRESS',13,1,NULL,'2026-09-05 13:37:54','2026-09-05 13:55:06'),
(49,'P25-08_10-H225-0​28',2025,'PDO-25-2794THYDA','Sw.181K-35','Continue','Block','H',225.00,82.60,'2026-08-24',NULL,NULL,'IN_PROGRESS',9,1,NULL,'2026-09-05 13:39:00','2026-09-05 13:48:11'),
(50,'P25-07_04-H90-0​10',2025,'PDO-25-2674THYDA','Sw.70CA-34,70CA-36,70CA-5','non','non','H',90.00,389.40,'2026-08-10',NULL,NULL,'IN_PROGRESS',10,1,NULL,'2026-09-05 13:39:48','2026-09-05 14:06:00'),
(51,'P26-05_04-H63-00​2',2026,'PDO-26-00​73THYDA','Sw.203R-7','203R','Block','H',63.00,45.00,'2026-08-24',NULL,NULL,'PENDING',NULL,1,NULL,'2026-09-09 12:41:58','2026-09-09 12:41:58'),
(52,'P26-07_06-H90-00​4',2026,'PDO-26-0​260THYDA','Sw.30PL,32L','Continue','Block','H',90.00,271.40,'2026-09-03',NULL,NULL,'PENDING',NULL,1,NULL,'2026-09-09 12:47:10','2026-09-09 12:47:10'),
(53,'P26-07_01-H110-00​1',2026,'PDO-26-00​66THYDA','Sw.137DT-16,137DT-5','Non','Non','H',110.00,82.60,'2026-08-28',NULL,NULL,'PENDING',NULL,1,NULL,'2026-09-09 12:49:44','2026-09-09 12:49:44');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES
('hMp6m4SwHYCEu5i67j0bWo22Ax4sqHoS4YaI7ZBV',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJLT2NFMzBwaXhKVU9PMXZHVGpVbmVDZ2lvRld1cmlKRzBJUTJ0dkxQIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9wcm9qZWN0c1wvY3JlYXRlIiwicm91dGUiOiJwcm9qZWN0cy5jcmVhdGUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=',1788958188),
('v762TO0Enmh8V40IizWyseklwXWvOxZTiCqe4aly',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJpeHZSdkN1RFpYTWJOVmVxbE9nUjREMHZqNm10RTNmalhlbkVsQ3NTIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC91c2VycyIsInJvdXRlIjoidXNlcnMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=',1790408224);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Admin User','admin@example.com','admin','2026-09-05 07:03:47','$2y$12$5WpVoQw.ZQn3Er2Eqacih.v3g30iPnODwQxRsc.l2.DjXnS81ISQm','SHuVyufGfXaBjR0om4LLmJptSlloqqoogbWP3eNUWYNYDmiptSdG725buHmu','2026-09-05 07:03:47','2026-09-05 07:03:47'),
(7,'លោកស្រី អ៊ុក ណារឿន','ouknareoun@gmail.com','user',NULL,'$2y$12$Z5DNsXkqZh9eX5CJd1YByO9ZaqWi6vb1KzB6.dv3i9UjKQCS4b/w.',NULL,'2026-09-05 07:58:36','2026-09-05 07:58:36'),
(8,'លោកស្រី លី សុខលីន','lysoklin@gmail.com','user',NULL,'$2y$12$XFF5W.SXxZqDrNSmNP1JL.050UNbNA9JWDEFdtGnD86ttXz7jWswO',NULL,'2026-09-05 12:28:04','2026-09-05 12:28:04'),
(9,'លោកស្រី ងួន ស្រីពៅ','ngounsrepov@gmail.com','user',NULL,'$2y$12$BMrsieiRwxzg9J02oqtB7OKVKMkZkfnQ.3ZYjkAxY6McvThJPkyXm',NULL,'2026-09-05 12:29:01','2026-09-05 12:29:01'),
(10,'លោកស្រី ស៊ូ ស្រីពៅ','sousreypov@gmail.com','user',NULL,'$2y$12$A9vsfl3DG58QQY.3nJ3VD.QgZbJlLOI.8OkQnfsTZM.QFpBDUqOHq',NULL,'2026-09-05 12:29:36','2026-09-05 12:29:36'),
(11,'កញ្ញា ហុង ស៊ីថា','hongsitha@gmail.com','user',NULL,'$2y$12$gUnHZaUTDFRyha4B9xIbDOKHRgHM4Z3pV9xpa7a1K7u.Sb6OhnUmi',NULL,'2026-09-05 12:30:25','2026-09-05 12:30:25'),
(12,'លោកស្រី គង់ សារឿន','kongsarouen@gmail.com','user',NULL,'$2y$12$pLAzdJCdxSRLRZM271VU0.SEtgAdRZjoAOWGXvTr8daNTkQEcZhsG',NULL,'2026-09-05 12:31:02','2026-09-05 12:31:02'),
(13,'លោក ស៊ិន ចំរើន','sinchamroeun@gmail.com','admin',NULL,'$2y$12$S94K9NlnrSCA6agYxsYXC.oJR6V42EMjFwIl9sW6.VosvDtwU6ghe',NULL,'2026-09-05 12:32:09','2026-09-05 12:32:09'),
(14,'លោក ម៉ក់ សាយភារម្យ','maksayphearom@gmail.com','admin',NULL,'$2y$12$Lygpt9yRVUirqMl3pMD5Tehi5WmoWnsFzs0FefOfGaJ5r5SgxF0ZC',NULL,'2026-09-05 12:33:34','2026-09-05 12:33:34'),
(15,'លោកស្រី មុំ វិជ្ជឡា','momvichala@gmail.com','user',NULL,'$2y$12$GsmhXB5sPUP1ZHCM.Ghy.OMuqaWXM5IElk.YxZpdlux4ATsPeb6Nq',NULL,'2026-09-05 13:56:51','2026-09-05 13:56:51');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'project_status_app'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-09-26 15:31:29
