/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.5-MariaDB, for linux-systemd (x86_64)
--
-- Host: 127.0.0.1    Database: vuihoc
-- ------------------------------------------------------
-- Server version	11.8.5-MariaDB

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
-- Table structure for table `badges`
--

DROP TABLE IF EXISTS `badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `badges` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(16) NOT NULL,
  `criteria` varchar(64) NOT NULL,
  `threshold` int(10) unsigned NOT NULL DEFAULT 1,
  `is_demo` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `badges_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `badges`
--

LOCK TABLES `badges` WRITE;
/*!40000 ALTER TABLE `badges` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `badges` VALUES
(1,'Chơi lần đầu','first-play','Hoàn thành lượt chơi đầu tiên. Chúc mừng bạn đã bắt đầu hành trình học tập!','🎮','first_play',1,1,'2026-10-07 20:27:20','2026-10-07 20:27:20'),
(2,'Chuỗi 3 ngày','streak-3','Học liên tục 3 ngày. Thói quen tốt đang hình thành!','🔥','streak_3',3,1,'2026-10-07 20:27:20','2026-10-07 20:27:20'),
(3,'Chuỗi 7 ngày','streak-7','Học liên tục 7 ngày. Bạn thật kiên trì!','⚡','streak_7',7,1,'2026-10-07 20:27:20','2026-10-07 20:27:20'),
(4,'Chuỗi 30 ngày','streak-30','Học liên tục 30 ngày. Bạn là tấm gương chăm học!','👑','streak_30',30,1,'2026-10-07 20:27:20','2026-10-07 20:27:20'),
(5,'Ngôi sao 1000 XP','xp-1000','Tích luỹ 1000 điểm kinh nghiệm.','⭐','xp_1000',1000,1,'2026-10-07 20:27:20','2026-10-07 20:27:20'),
(6,'Viên kim cương 5000 XP','xp-5000','Tích luỹ 5000 điểm kinh nghiệm. Thật xuất sắc!','💎','xp_5000',5000,1,'2026-10-07 20:27:20','2026-10-07 20:27:20'),
(7,'Hoàn hảo 5 lượt','perfect-5','Đạt 100% điểm trong 5 lượt chơi.','🌟','perfect_5',5,1,'2026-10-07 20:27:20','2026-10-07 20:27:20'),
(8,'Nhà thám hiểm','explorer-3','Chơi game ở 3 môn học khác nhau.','🗺️','explorer_3',3,1,'2026-10-07 20:27:20','2026-10-07 20:27:20'),
(9,'Học giả','scholar-10','Hoàn thành 10 bài học. Kiến thức của bạn ngày càng rộng!','📚','scholar_10',10,1,'2026-10-07 20:27:20','2026-10-07 20:27:20');
/*!40000 ALTER TABLE `badges` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `class_members`
--

DROP TABLE IF EXISTS `class_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `classroom_id` bigint(20) unsigned NOT NULL,
  `profile_id` bigint(20) unsigned NOT NULL,
  `joined_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cm_unique` (`classroom_id`,`profile_id`),
  KEY `class_members_profile_id_foreign` (`profile_id`),
  CONSTRAINT `class_members_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_members_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `learner_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_members`
--

LOCK TABLES `class_members` WRITE;
/*!40000 ALTER TABLE `class_members` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `class_members` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `classrooms`
--

DROP TABLE IF EXISTS `classrooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `classrooms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(16) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `classrooms_code_unique` (`code`),
  KEY `classrooms_owner_id_foreign` (`owner_id`),
  CONSTRAINT `classrooms_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classrooms`
--

LOCK TABLES `classrooms` WRITE;
/*!40000 ALTER TABLE `classrooms` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `classrooms` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) unsigned NOT NULL,
  `target_type` enum('lesson','topic') NOT NULL,
  `target_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fav_unique` (`profile_id`,`target_type`,`target_id`),
  CONSTRAINT `favorites_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `learner_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `fill_answers`
--

DROP TABLE IF EXISTS `fill_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fill_answers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) unsigned NOT NULL,
  `blank_index` smallint(5) unsigned NOT NULL,
  `answer_text` varchar(255) NOT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fa_question` (`question_id`),
  CONSTRAINT `fill_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fill_answers`
--

LOCK TABLES `fill_answers` WRITE;
/*!40000 ALTER TABLE `fill_answers` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `fill_answers` VALUES
(1,10,0,'100',1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(2,11,0,'96',1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(3,12,0,'40',1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(4,22,0,'1/2',1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(5,23,0,'3/10',1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(6,24,0,'1/6',1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(7,34,0,'13',1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(8,35,0,'7',1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(9,36,0,'13',1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(10,46,0,'50',1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(11,47,0,'180',1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(12,48,0,'60',1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(13,58,0,'Hoa',1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(14,59,0,'đang chơi',1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(15,60,0,'cảm',1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(16,60,0,'câu cảm',2,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(17,70,0,'ch',1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(18,71,0,'tr',1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(19,72,0,'gi',1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(20,82,0,'như',1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(21,83,0,'nhân hoá',1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(22,83,0,'nhân hóa',2,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(23,84,0,'tính từ',1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(24,94,0,'pen',1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(25,95,0,'teacher',1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(26,96,0,'book',1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(27,106,0,'on',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(28,107,0,'next to',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(29,108,0,'under',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(30,118,0,'hotel',1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(31,119,0,'ticket',1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(32,120,0,'beach',1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(33,130,0,'thực quản',1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(34,131,0,'ruột non',1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(35,132,0,'dạ dày',1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(36,142,0,'100',1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(37,143,0,'khí',1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(38,144,0,'nước',1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(39,154,0,'công tắc',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(40,155,0,'sáng',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(41,156,0,'dẫn',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(42,166,0,'40',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(43,167,0,'Bạch Đằng',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(44,168,0,'gió',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(45,178,0,'981',1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(46,179,0,'Hoa Lư',1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(47,180,0,'Lê Đại Hành',1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(48,190,0,'Trần Quốc Tuấn',1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(49,191,0,'Hịch tướng sĩ',1,'2026-10-07 20:27:20','2026-10-07 20:27:20'),
(50,192,0,'Đức Thánh Trần',1,'2026-10-07 20:27:20','2026-10-07 20:27:20');
/*!40000 ALTER TABLE `fill_answers` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
set autocommit=0;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
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
set autocommit=0;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `learner_profiles`
--

DROP TABLE IF EXISTS `learner_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `learner_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `display_name` varchar(255) NOT NULL,
  `avatar_emoji` varchar(16) NOT NULL DEFAULT '?',
  `grade` tinyint(3) unsigned DEFAULT NULL,
  `daily_goal` smallint(5) unsigned NOT NULL DEFAULT 3,
  `font_size` enum('normal','large','xlarge') NOT NULL DEFAULT 'normal',
  `total_xp` int(10) unsigned NOT NULL DEFAULT 0,
  `level` smallint(5) unsigned NOT NULL DEFAULT 1,
  `current_streak` int(10) unsigned NOT NULL DEFAULT 0,
  `longest_streak` int(10) unsigned NOT NULL DEFAULT 0,
  `last_play_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `learner_profiles_user_id_unique` (`user_id`),
  KEY `lp_user` (`user_id`),
  KEY `lp_parent` (`parent_id`),
  CONSTRAINT `learner_profiles_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `learner_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `learner_profiles`
--

LOCK TABLES `learner_profiles` WRITE;
/*!40000 ALTER TABLE `learner_profiles` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `learner_profiles` VALUES
(1,NULL,3,'Bé An','🐰',6,3,'normal',0,1,0,0,NULL,'2026-10-07 20:27:21','2026-10-07 20:27:21'),
(2,4,NULL,'Minh','🦊',7,3,'normal',0,1,0,0,NULL,'2026-10-07 20:27:22','2026-10-07 20:27:22');
/*!40000 ALTER TABLE `learner_profiles` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lessons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `skill_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `objective` text DEFAULT NULL,
  `difficulty` enum('de','trung_binh','kho') NOT NULL DEFAULT 'trung_binh',
  `duration_minutes` smallint(5) unsigned NOT NULL DEFAULT 10,
  `instructions` text DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `is_demo` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ls_skill` (`skill_id`),
  KEY `ls_status` (`status`),
  KEY `ls_slug` (`slug`),
  CONSTRAINT `lessons_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons`
--

LOCK TABLES `lessons` WRITE;
/*!40000 ALTER TABLE `lessons` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `lessons` VALUES
(1,1,'Cộng và trừ số tự nhiên','toan-cong-tru-so-tu-nhien','Thực hiện đúng phép cộng, phép trừ số tự nhiên trong phạm vi 1 000 000.','de',10,'Đọc kỹ đề bài, thực hiện phép cộng hoặc phép trừ rồi chọn đáp án đúng. Kiểm tra lại phép tính trước khi nộp bài.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(2,1,'Nhân và chia số tự nhiên','toan-nhan-chia-so-tu-nhien','Thực hiện đúng phép nhân, phép chia hết cho số tự nhiên.','trung_binh',10,'Kéo thả từng ý vào nhóm đúng hoặc điền kết quả vào chỗ trống. Chú ý thứ tự thực hiện phép tính.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(3,2,'Cộng và trừ phân số','toan-cong-tru-phan-so','Cộng, trừ được hai phân số (cùng mẫu số và khác mẫu số).','trung_binh',12,'Nhớ quy đồng mẫu số trước khi cộng, trừ phân số khác mẫu. Rút gọn kết quả nếu được.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(4,2,'Nhân và chia phân số','toan-nhan-chia-phan-so','Nhân, chia được hai phân số, biết rút gọn kết quả.','trung_binh',12,'Muốn nhân hai phân số ta nhân tử với tử, mẫu với mẫu. Muốn chia, ta nhân với phân số đảo ngược.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(5,3,'Thu gọn đơn thức','toan-thu-gon-don-thuc','Thu gọn được đơn thức và xác định bậc của đơn thức.','trung_binh',12,'Nhân các hệ số với nhau, nhân các biến với nhau, cộng số mũ của cùng một biến.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(6,3,'Giá trị của biểu thức đại số','toan-gia-tri-bieu-thuc','Tính được giá trị của biểu thức đại số khi biết giá trị của biến.','kho',15,'Thay giá trị của biến vào biểu thức rồi tính theo đúng thứ tự phép tính: trong ngoặc trước, nhân chia trước cộng trừ sau.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(7,4,'Góc và đường thẳng','toan-goc-va-duong-thang','Nhận biết được góc nhọn, góc vuông, góc tù, góc bẹt và vị trí hai đường thẳng.','de',10,'Quan sát hình vẽ (mô tả trong đề) và chọn đáp án đúng. Góc vuông 90 độ, góc nhọn nhỏ hơn 90 độ, góc tù lớn hơn 90 độ.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(8,4,'Tam giác và các góc','toan-tam-giac','Biết tổng ba góc trong tam giác bằng 180 độ và phân loại tam giác.','trung_binh',10,'Vận dụng: tổng ba góc trong của một tam giác luôn bằng 180 độ. Kéo thả các ý vào nhóm đúng.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(9,5,'Các từ loại cơ bản','tv-cac-tu-loai','Nhận biết được danh từ, động từ, tính từ, số từ, lượng từ trong câu.','de',10,'Danh từ chỉ người, vật, hiện tượng. Động từ chỉ hành động, trạng thái. Tính từ chỉ đặc điểm, tính chất.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(10,5,'Cấu tạo câu đơn','tv-cau-don','Xác định được chủ ngữ, vị ngữ và các thành phần phụ trong câu đơn.','trung_binh',10,'Chủ ngữ thường trả lời câu hỏi \"ai? cái gì?\". Vị ngữ trả lời \"làm gì? thế nào? là gì?\".',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(11,6,'Dấu hỏi và dấu ngã','tv-dau-hoi-dau-nga','Viết đúng dấu hỏi, dấu ngã trong các từ thường gặp.','de',10,'Đọc kỹ từng từ và chọn dạng viết đúng. Ghi nhớ các từ mẫu để tránh nhầm lẫn.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(12,6,'Âm đầu ch, tr, d, gi, r','tv-am-dau','Phân biệt được các âm đầu ch/tr, d/gi/r trong từ ngữ thông dụng.','de',10,'Đọc thầm từ rồi điền âm đầu còn thiếu. Đối chiếu với cách phát âm đúng của giáo viên.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(13,7,'Bài văn miêu tả','tv-bai-van-mieu-ta','Nắm được bố cục và cách dùng từ ngữ trong bài văn miêu tả.','trung_binh',12,'Bài văn miêu tả gồm mở bài, thân bài, kết bài. Chú ý các từ ngữ gợi hình, gợi cảm.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(14,7,'Biện pháp tu từ','tv-bien-phap-tu-tu','Nhận biết được biện pháp so sánh, nhân hoá, ẩn dụ.','trung_binh',12,'So sánh dùng từ \"như, tựa, giống\". Nhân hoá gán đặc điểm con người cho sự vật. Ẩn dụ gọi tên sự vật này bằng tên sự vật khác.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(15,8,'Family vocabulary','en-my-family','Hiểu nghĩa và ghi nhớ các từ vựng về gia đình (father, mother, brother, sister...).','de',10,'Ghép từ tiếng Anh với nghĩa tiếng Việt tương ứng. Học thuộc cách viết của từng từ.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(16,8,'At school vocabulary','en-at-school','Hiểu nghĩa và ghi nhớ các từ vựng về trường học (book, pen, classroom, teacher...).','de',10,'Phân loại các từ vào nhóm đồ dùng hoặc nhóm con người. Điền từ còn thiếu vào câu.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(17,9,'Present simple tense','en-present-simple','Chia đúng động từ ở thì hiện tại đơn với các chủ ngữ khác nhau.','trung_binh',12,'Chủ ngữ he, she, it → động từ thêm s/es. Các chủ ngữ còn lại giữ nguyên động từ.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(18,9,'Prepositions of place','en-prepositions','Dùng đúng giới từ chỉ nơi chốn: in, on, under, behind, next to...','de',10,'on = trên mặt phẳng, in = bên trong, under = phía dưới, behind = phía sau, next to = bên cạnh.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(19,10,'Health vocabulary','en-health','Hiểu nghĩa và ghi nhớ các từ vựng về sức khoẻ (headache, fever, healthy...).','de',10,'Ghép từ tiếng Anh với nghĩa tiếng Việt tương ứng. Phân biệt các từ chỉ bệnh và từ chỉ thể trạng.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(20,10,'Travel vocabulary','en-travel','Hiểu nghĩa và ghi nhớ các từ vựng về du lịch (ticket, suitcase, hotel, beach...).','de',10,'Điền từ còn thiếu vào câu về chuyến du lịch. Học thuộc các cụm từ thông dụng.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(21,11,'Hệ xương của người','kh-he-xuong','Kể tên được một số xương chính và vai trò của hệ xương.','de',10,'Hệ xương nâng đỡ cơ thể và bảo vệ các cơ quan bên trong. Ghi nhớ tên các xương chính.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(22,11,'Hệ tiêu hoá','kh-he-tieu-hoa','Mô tả được đường đi của thức ăn qua các cơ quan tiêu hoá.','trung_binh',10,'Thức ăn đi theo thứ tự: miệng → thực quản → dạ dày → ruột non → ruột già.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(23,12,'Các trạng thái của chất','kh-trang-thai-chat','Nhận biết được ba trạng thái của chất: rắn, lỏng, khí.','de',10,'Chất rắn có hình dạng cố định, chất lỏng chảy được, chất khí lan toả chiếm đầy không gian.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(24,12,'Nước quanh ta','kh-nuoc','Biết được tính chất của nước và vai trò của nước với sự sống.','de',10,'Nước không màu, không mùi, không vị. Nhiệt độ sôi của nước là 100 độ C ở điều kiện thường.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(25,13,'Nguồn năng lượng','kh-nguon-nang-luong','Phân biệt được năng lượng tái tạo và năng lượng không tái tạo.','trung_binh',10,'Năng lượng tái tạo: mặt trời, gió, nước. Không tái tạo: than đá, dầu mỏ, khí đốt.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(26,13,'Điện và mạch điện','kh-dien','Biết các bộ phận của mạch điện đơn giản và tác dụng của dòng điện.','trung_binh',10,'Mạch điện đơn giản gồm: nguồn điện, dây dẫn, bóng đèn và công tắc. Dòng điện có tác dụng làm nóng và phát sáng.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(27,14,'Nước Văn Lang','ls-nuoc-van-lang','Biết được nước Văn Lang – nhà nước đầu tiên của người Việt.','de',10,'Nước Văn Lang do các vua Hùng dựng nên, kinh đô ở Phong Châu (Phú Thọ ngày nay).',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(28,14,'Các anh hùng dân tộc','ls-anh-hung-dan-toc','Kể tên được các anh hùng chống ngoại xâm: Hai Bà Trưng, Bà Triệu, Ngô Quyền...','de',10,'Ghép tên anh hùng với chiến công tương ứng. Ghi nhớ thứ tự thời gian các sự kiện.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(29,15,'Đinh Bộ Lĩnh dẹp loạn 12 sứ quân','ls-dinh-bo-linh','Biết được công lao của Đinh Bộ Lĩnh trong việc thống nhất đất nước.','de',10,'Đinh Bộ Lĩnh dẹp loạn 12 sứ quân, lập nước Đại Cồ Việt, đóng đô ở Hoa Lư.',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(30,15,'Lê Hoàn và nhà Tiền Lê','ls-le-hoan','Biết được Lê Hoàn lên ngôi và chiến thắng quân Tống năm 981.','de',10,'Lê Hoàn đánh tan quân Tống xâm lược năm 981, mở đầu triều đại nhà Tiền Lê.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(31,16,'Ba lần kháng chiến chống Nguyên – Mông','ls-dong-bo-dau','Kể được ba lần kháng chiến chống quân Nguyên – Mông thắng lợi.','trung_binh',12,'Nhân dân Đại Việt đã ba lần đánh bại quân Nguyên – Mông (1258, 1285, 1287-1288).',1,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(32,16,'Trần Hưng Đạo','ls-tran-hung-dao','Biết được vai trò của Trần Hưng Đạo trong kháng chiến chống Nguyên – Mông.','trung_binh',12,'Trần Hưng Đạo là tổng chỉ huy kháng chiến lần 2 và lần 3, tác giả Hịch tướng sĩ.',2,'published',1,'2026-10-07 20:27:04','2026-10-07 20:27:04');
/*!40000 ALTER TABLE `lessons` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `matching_pairs`
--

DROP TABLE IF EXISTS `matching_pairs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `matching_pairs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) unsigned NOT NULL,
  `left_text` varchar(255) NOT NULL,
  `right_text` varchar(255) NOT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mp_question` (`question_id`),
  CONSTRAINT `matching_pairs_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `matching_pairs`
--

LOCK TABLES `matching_pairs` WRITE;
/*!40000 ALTER TABLE `matching_pairs` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `matching_pairs` VALUES
(1,4,'45 + 27','72',1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(2,4,'96 − 38','58',2,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(3,4,'123 + 45','168',3,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(4,4,'200 − 75','125',4,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(5,5,'An có 15 viên bi, Bình cho thêm 8 viên. An có tất cả bao nhiêu viên?','15 + 8',1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(6,5,'Lan có 20 cái kẹo, ăn hết 6 cái. Lan còn mấy cái?','20 − 6',2,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(7,5,'Lớp 6A có 32 bạn, lớp 6B có 29 bạn. Cả hai lớp có mấy bạn?','32 + 29',3,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(8,6,'Tổng của 150 và 230','380',1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(9,6,'Hiệu của 500 và 145','355',2,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(10,6,'Tổng của 999 và 1','1000',3,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(11,16,'1/5 + 2/5','3/5',1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(12,16,'5/6 − 1/6','2/3',2,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(13,16,'3/8 + 1/8','1/2',3,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(14,16,'7/10 − 3/10','2/5',4,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(15,17,'Quy đồng 1/3 và 1/6','2/6 và 1/6',1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(16,17,'Quy đồng 1/4 và 1/2','1/4 và 2/4',2,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(17,17,'Phân số tối giản của 4/8','1/2',3,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(18,18,'1/2 + 1/3','5/6',1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(19,18,'2/3 − 1/6','1/2',2,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(20,18,'3/4 + 1/8','7/8',3,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(21,28,'2a · 5a','10a²',1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(22,28,'3x² · 2x','6x³',2,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(23,28,'4y · y³','4y⁴',3,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(24,29,'7x','1',1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(25,29,'x³y²','5',2,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(26,29,'9','0',3,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(27,30,'5x + 3x','8x',1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(28,30,'7y² − 2y²','5y²',2,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(29,30,'4ab + ab','5ab',3,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(30,40,'Góc vuông','90 độ',1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(31,40,'Góc nhọn','Nhỏ hơn 90 độ',2,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(32,40,'Góc tù','Lớn hơn 90 độ và nhỏ hơn 180 độ',3,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(33,41,'Góc bẹt','180 độ',1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(34,41,'Hai đường thẳng vuông góc','Tạo thành góc 90 độ',2,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(35,41,'Đường trung trực','Vuông góc và đi qua trung điểm của đoạn thẳng',3,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(36,42,'Hai tia đối nhau','Tạo thành một góc bẹt',1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(37,42,'Tia phân giác','Chia góc thành hai góc bằng nhau',2,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(38,42,'Hai góc kề bù','Có tổng số đo bằng 180 độ',3,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(39,52,'sách','Danh từ',1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(40,52,'hát','Động từ',2,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(41,52,'đỏ','Tính từ',3,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(42,53,'con mèo','Danh từ',1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(43,53,'nhảy','Động từ',2,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(44,53,'vui','Tính từ',3,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(45,54,'một','Số từ',1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(46,54,'những','Lượng từ',2,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(47,54,'đang','Phó từ',3,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(48,64,'nghỉ','dấu hỏi',1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(49,64,'nghĩ','dấu ngã',2,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(50,64,'mãi','dấu ngã',3,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(51,64,'mải','dấu hỏi',4,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(52,65,'vẻ đẹp','dấu hỏi',1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(53,65,'vẽ tranh','dấu ngã',2,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(54,65,'kẻ thù','dấu hỏi',3,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(55,66,'cũ','dấu ngã',1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(56,66,'củ khoai','dấu hỏi',2,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(57,66,'đũa','dấu ngã',3,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(58,66,'đủ','dấu hỏi',4,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(59,76,'Mở bài','Giới thiệu đối tượng miêu tả',1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(60,76,'Thân bài','Tả chi tiết đối tượng',2,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(61,76,'Kết bài','Nêu cảm nghĩ của người viết',3,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(62,77,'Tả người','Miêu tả ngoại hình, tính cách',1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(63,77,'Tả cảnh','Miêu tả phong cảnh',2,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(64,77,'Tả đồ vật','Miêu tả hình dáng, công dụng',3,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(65,78,'Từ láy','lấp lánh, rì rào',1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(66,78,'Từ ghép','xanh biếc, tươi tốt',2,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(67,78,'So sánh','đẹp như tranh vẽ',3,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(68,88,'father','bố',1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(69,88,'mother','mẹ',2,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(70,88,'brother','anh/em trai',3,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(71,88,'sister','chị/em gái',4,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(72,89,'grandfather','ông',1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(73,89,'grandmother','bà',2,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(74,89,'uncle','chú/bác trai',3,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(75,90,'aunt','cô/dì',1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(76,90,'cousin','anh chị em họ',2,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(77,90,'son','con trai',3,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(78,100,'I / you / we / they','go',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(79,100,'he / she / it','goes',2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(80,101,'do not','don’t',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(81,101,'does not','doesn’t',2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(82,102,'She ___ (watch) TV','watches',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(83,102,'They ___ (study) English','study',2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(84,102,'He ___ (have) a bike','has',3,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(85,112,'headache','đau đầu',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(86,112,'fever','sốt',2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(87,112,'cough','ho',3,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(88,113,'healthy','khoẻ mạnh',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(89,113,'sick','ốm',2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(90,113,'tired','mệt',3,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(91,114,'doctor','bác sĩ',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(92,114,'medicine','thuốc',2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(93,114,'hospital','bệnh viện',3,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(94,124,'Xương sọ','Bảo vệ não',1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(95,124,'Xương sườn','Bảo vệ tim và phổi',2,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(96,124,'Cột sống','Nâng đỡ cơ thể',3,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(97,125,'Xương đùi','Xương dài nhất cơ thể',1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(98,125,'Xương tay','Giúp cử động linh hoạt',2,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(99,125,'Khớp','Nối các xương với nhau',3,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(100,126,'Canxi','Giúp xương chắc khoẻ',1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(101,126,'Sữa','Thực phẩm giàu canxi',2,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(102,126,'Tập thể dục','Giúp xương phát triển tốt',3,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(103,136,'Đá','Rắn',1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(104,136,'Nước','Lỏng',2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(105,136,'Hơi nước','Khí',3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(106,137,'Sắt','Rắn',1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(107,137,'Dầu ăn','Lỏng',2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(108,137,'Không khí','Khí',3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(109,138,'Nóng chảy','Rắn thành lỏng',1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(110,138,'Đông đặc','Lỏng thành rắn',2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(111,138,'Bay hơi','Lỏng thành khí',3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(112,148,'Mặt trời','Năng lượng tái tạo',1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(113,148,'Than đá','Năng lượng không tái tạo',2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(114,148,'Gió','Năng lượng tái tạo',3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(115,149,'Nước chảy','Nhà máy thuỷ điện',1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(116,149,'Dầu mỏ','Nhiên liệu xe cộ',2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(117,149,'Củi','Đun nấu ở nông thôn',3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(118,150,'Tắt đèn khi ra khỏi phòng','Tiết kiệm điện',1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(119,150,'Để ti vi mở cả ngày','Lãng phí năng lượng',2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(120,150,'Dùng điện mặt trời','Năng lượng sạch',3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(121,160,'Vua Hùng','Người dựng nước Văn Lang',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(122,160,'Phong Châu','Kinh đô nước Văn Lang',2,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(123,160,'Lạc tướng','Quan lại cai quản địa phương',3,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(124,161,'Trống đồng Đông Sơn','Văn hoá thời Văn Lang',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(125,161,'Nghề trồng lúa nước','Nghề chính của cư dân Văn Lang',2,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(126,161,'Truyện Sơn Tinh – Thuỷ Tinh','Truyền thuyết thời Hùng Vương',3,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(127,162,'An Dương Vương','Xây thành Cổ Loa',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(128,162,'Nỏ thần','Vũ khí lợi hại của An Dương Vương',2,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(129,162,'Nước Âu Lạc','Nước kế tiếp sau Văn Lang',3,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(130,172,'Đinh Bộ Lĩnh','Người dẹp loạn 12 sứ quân',1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(131,172,'Đại Cồ Việt','Tên nước thời nhà Đinh',2,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(132,172,'Hoa Lư','Kinh đô nhà Đinh',3,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(133,173,'968','Đinh Bộ Lĩnh lên ngôi hoàng đế',1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(134,173,'Đinh Tiên Hoàng','Hiệu của Đinh Bộ Lĩnh khi lên ngôi',2,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(135,173,'980','Lê Hoàn lên ngôi, mở đầu nhà Tiền Lê',3,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(136,174,'Cờ lau tập trận','Trò chơi tuổi thơ của Đinh Bộ Lĩnh',1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(137,174,'Hoa Lư','Thuộc Ninh Bình ngày nay',2,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(138,174,'Đại Cồ Việt','Có nghĩa là nước Việt lớn',3,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(139,184,'1258','Kháng chiến lần thứ nhất',1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(140,184,'1285','Kháng chiến lần thứ hai',2,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(141,184,'1287 – 1288','Kháng chiến lần thứ ba',3,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(142,185,'Trần Hưng Đạo','Tổng chỉ huy kháng chiến',1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(143,185,'Trần Quang Khải','Tướng trận Chương Dương, Tây Kết',2,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(144,185,'Vua Trần Nhân Tông','Vua lãnh đạo kháng chiến',3,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(145,186,'Bạch Đằng 1288','Ô Mã Nhi bị bắt sống',1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(146,186,'Vạn Kiếp','Căn cứ của quân dân Đại Việt',2,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(147,186,'Đông Bộ Đầu','Trận thắng quân Nguyên lần 2',3,'2026-10-07 20:27:19','2026-10-07 20:27:19');
/*!40000 ALTER TABLE `matching_pairs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_10_07_100000_create_learner_profiles_table',1),
(5,'2026_10_07_101000_create_subjects_table',1),
(6,'2026_10_07_102000_create_topics_table',1),
(7,'2026_10_07_103000_create_skills_table',1),
(8,'2026_10_07_104000_create_lessons_table',1),
(9,'2026_10_07_105000_create_questions_table',1),
(10,'2026_10_07_110000_create_play_sessions_table',1),
(11,'2026_10_07_111000_create_gamification_tables',1),
(12,'2026_10_07_202442_create_personal_access_tokens_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
set autocommit=0;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `play_sessions`
--

DROP TABLE IF EXISTS `play_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `play_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `game_type` enum('quiz','matching','sort','fill') NOT NULL,
  `token` varchar(64) NOT NULL,
  `status` enum('started','finished','expired') NOT NULL DEFAULT 'started',
  `question_ids_json` text DEFAULT NULL,
  `started_at` timestamp NOT NULL,
  `expires_at` timestamp NOT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `max_score` int(11) NOT NULL DEFAULT 0,
  `accuracy` decimal(5,2) NOT NULL DEFAULT 0.00,
  `duration_seconds` int(11) NOT NULL DEFAULT 0,
  `xp_earned` int(11) NOT NULL DEFAULT 0,
  `answers_json` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `play_sessions_token_unique` (`token`),
  KEY `play_sessions_lesson_id_foreign` (`lesson_id`),
  KEY `ps_profile` (`profile_id`),
  KEY `ps_status` (`status`),
  CONSTRAINT `play_sessions_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`),
  CONSTRAINT `play_sessions_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `learner_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `play_sessions`
--

LOCK TABLES `play_sessions` WRITE;
/*!40000 ALTER TABLE `play_sessions` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `play_sessions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `profile_badges`
--

DROP TABLE IF EXISTS `profile_badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `profile_badges` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) unsigned NOT NULL,
  `badge_id` bigint(20) unsigned NOT NULL,
  `earned_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pb_profile_badge` (`profile_id`,`badge_id`),
  KEY `profile_badges_badge_id_foreign` (`badge_id`),
  CONSTRAINT `profile_badges_badge_id_foreign` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `profile_badges_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `learner_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile_badges`
--

LOCK TABLES `profile_badges` WRITE;
/*!40000 ALTER TABLE `profile_badges` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `profile_badges` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `question_options`
--

DROP TABLE IF EXISTS `question_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_options` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) unsigned NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `qo_question` (`question_id`),
  CONSTRAINT `question_options_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question_options`
--

LOCK TABLES `question_options` WRITE;
/*!40000 ALTER TABLE `question_options` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `question_options` VALUES
(1,1,'193',0,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(2,1,'203',1,2,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(3,1,'213',0,3,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(4,1,'183',0,4,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(5,2,'663',0,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(6,2,'673',1,2,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(7,2,'683',0,3,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(8,2,'763',0,4,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(9,3,'563',1,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(10,3,'553',0,2,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(11,3,'573',0,3,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(12,3,'463',0,4,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(13,13,'1',1,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(14,13,'5/3',0,2,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(15,13,'3/9',0,3,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(16,13,'5/9',0,4,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(17,14,'2/8',0,1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(18,14,'1/2',1,2,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(19,14,'3/8',0,3,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(20,14,'1/4',0,4,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(21,15,'2/6',0,1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(22,15,'3/4',1,2,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(23,15,'1/6',0,3,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(24,15,'2/4',0,4,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(25,25,'6x³',1,1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(26,25,'5x³',0,2,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(27,25,'6x²',0,3,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(28,25,'5x²',0,4,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(29,26,'5',1,1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(30,26,'6',0,2,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(31,26,'2',0,3,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(32,26,'3',0,4,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(33,27,'3xy²',1,1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(34,27,'3x²y',0,2,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(35,27,'5x²y²',0,3,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(36,27,'3x²y²',0,4,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(37,37,'Góc 45 độ',1,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(38,37,'Góc 90 độ',0,2,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(39,37,'Góc 120 độ',0,3,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(40,37,'Góc 180 độ',0,4,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(41,38,'song song',1,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(42,38,'vuông góc',0,2,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(43,38,'cắt nhau',0,3,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(44,38,'trùng nhau',0,4,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(45,39,'90 độ',0,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(46,39,'180 độ',1,2,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(47,39,'360 độ',0,3,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(48,39,'270 độ',0,4,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(49,49,'học sinh',1,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(50,49,'chạy',0,2,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(51,49,'đẹp',0,3,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(52,49,'nhanh',0,4,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(53,50,'bơi',1,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(54,50,'bàn',0,2,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(55,50,'xanh',0,3,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(56,50,'rất',0,4,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(57,51,'cao',1,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(58,51,'nhà',0,2,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(59,51,'uống',0,3,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(60,51,'mỗi',0,4,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(61,61,'nghỉ ngơi',1,1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(62,61,'nghĩ ngơi',0,2,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(63,61,'nghỉ ngoi',0,3,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(64,61,'nghĩ ngoi',0,4,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(65,62,'suy nghĩ',1,1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(66,62,'suy nghỉ',0,2,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(67,62,'xuy nghĩ',0,3,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(68,62,'xuy nghỉ',0,4,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(69,63,'mãi mãi',1,1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(70,63,'mải mãi',0,2,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(71,63,'mãi mải',0,3,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(72,63,'mải mải',0,4,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(73,73,'2 phần',0,1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(74,73,'3 phần',1,2,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(75,73,'4 phần',0,3,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(76,73,'5 phần',0,4,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(77,74,'Mở bài',0,1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(78,74,'Thân bài',0,2,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(79,74,'Kết bài',1,3,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(80,74,'Tựa đề',0,4,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(81,75,'lấp lánh',1,1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(82,75,'vui',0,2,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(83,75,'rất',0,3,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(84,75,'đã',0,4,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(85,85,'father',1,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(86,85,'mother',0,2,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(87,85,'brother',0,3,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(88,85,'sister',0,4,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(89,86,'brother',0,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(90,86,'sister',1,2,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(91,86,'aunt',0,3,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(92,86,'cousin',0,4,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(93,87,'grandmother',0,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(94,87,'grandfather',1,2,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(95,87,'uncle',0,3,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(96,87,'father',0,4,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(97,97,'go',0,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(98,97,'goes',1,2,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(99,97,'going',0,3,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(100,97,'gone',0,4,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(101,98,'plays',0,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(102,98,'play',1,2,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(103,98,'playing',0,3,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(104,98,'played',0,4,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(105,99,'don’t',0,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(106,99,'doesn’t',1,2,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(107,99,'isn’t',0,3,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(108,99,'not',0,4,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(109,109,'headache',1,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(110,109,'stomachache',0,2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(111,109,'toothache',0,3,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(112,109,'backache',0,4,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(113,110,'sick',0,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(114,110,'tired',0,2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(115,110,'healthy',1,3,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(116,110,'weak',0,4,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(117,111,'teacher',0,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(118,111,'doctor',1,2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(119,111,'farmer',0,3,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(120,111,'nurse',0,4,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(121,121,'Xương sọ',1,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(122,121,'Xương sườn',0,2,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(123,121,'Xương đùi',0,3,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(124,121,'Xương tay',0,4,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(125,122,'Nâng đỡ và bảo vệ cơ thể',1,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(126,122,'Tiêu hoá thức ăn',0,2,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(127,122,'Bơm máu đi khắp cơ thể',0,3,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(128,122,'Giúp hô hấp',0,4,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(129,123,'Xương tay',0,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(130,123,'Xương sườn',0,2,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(131,123,'Xương đùi',1,3,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(132,123,'Xương sọ',0,4,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(133,133,'Rắn',1,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(134,133,'Lỏng',0,2,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(135,133,'Khí',0,3,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(136,133,'Không xác định',0,4,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(137,134,'Có hình dạng cố định',0,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(138,134,'Lan toả, chiếm đầy không gian chứa nó',1,2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(139,134,'Chảy được như nước',0,3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(140,134,'Cứng và nặng',0,4,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(141,135,'Rắn',0,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(142,135,'Khí (hơi nước)',1,2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(143,135,'Không đổi',0,3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(144,135,'Lỏng đặc',0,4,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(145,145,'Gió',1,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(146,145,'Than đá',0,2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(147,145,'Dầu mỏ',0,3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(148,145,'Khí đốt',0,4,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(149,146,'Nhiệt năng',0,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(150,146,'Điện năng',1,2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(151,146,'Hoá năng',0,3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(152,146,'Cơ năng',0,4,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(153,147,'Mặt trời',0,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(154,147,'Gió',0,2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(155,147,'Than đá',1,3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(156,147,'Nước chảy',0,4,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(157,157,'Các vua Hùng',1,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(158,157,'An Dương Vương',0,2,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(159,157,'Ngô Quyền',0,3,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(160,157,'Đinh Bộ Lĩnh',0,4,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(161,158,'Phong Châu',1,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(162,158,'Hoa Lư',0,2,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(163,158,'Thăng Long',0,3,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(164,158,'Cổ Loa',0,4,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(165,159,'Vua Hùng',1,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(166,159,'Hoàng đế',0,2,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(167,159,'Tù trưởng',0,3,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(168,159,'Quan lang',0,4,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(169,169,'10',0,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(170,169,'12',1,2,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(171,169,'14',0,3,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(172,169,'16',0,4,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(173,170,'Đại Cồ Việt',1,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(174,170,'Đại Việt',0,2,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(175,170,'Đại Nam',0,3,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(176,170,'Âu Lạc',0,4,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(177,171,'Hoa Lư',1,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(178,171,'Thăng Long',0,2,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(179,171,'Phong Châu',0,3,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(180,171,'Cổ Loa',0,4,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(181,181,'1 lần',0,1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(182,181,'2 lần',0,2,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(183,181,'3 lần',1,3,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(184,181,'4 lần',0,4,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(185,182,'1258',0,1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(186,182,'1285',1,2,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(187,182,'1287',0,3,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(188,182,'1288',0,4,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(189,183,'Trần Hưng Đạo',1,1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(190,183,'Trần Quang Khải',0,2,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(191,183,'Trần Nhật Duật',0,3,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(192,183,'Phạm Ngũ Lão',0,4,'2026-10-07 20:27:19','2026-10-07 20:27:19');
/*!40000 ALTER TABLE `question_options` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `game_type` enum('quiz','matching','sort','fill') NOT NULL,
  `prompt` text NOT NULL,
  `explanation` text DEFAULT NULL,
  `difficulty` enum('de','trung_binh','kho') NOT NULL DEFAULT 'trung_binh',
  `points` smallint(5) unsigned NOT NULL DEFAULT 10,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `is_demo` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `q_lesson` (`lesson_id`),
  CONSTRAINT `questions_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `questions` VALUES
(1,1,'quiz','125 + 78 = ?','Đặt tính rồi cộng: 125 + 78 = 203. Nhớ 1 ở hàng chục khi 5 + 8 = 13.','de',10,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(2,1,'quiz','940 − 267 = ?','940 − 267 = 673. Mượn 1 ở hàng trăm vì 4 chục không trừ được 6 chục.','de',10,2,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(3,1,'quiz','Một cửa hàng bán 245 cuốn sách buổi sáng và 318 cuốn buổi chiều. Cả ngày bán được bao nhiêu cuốn?','Cả ngày bán được 245 + 318 = 563 cuốn sách.','de',10,3,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(4,1,'matching','Nối mỗi phép tính với kết quả đúng.','45 + 27 = 72; 96 − 38 = 58; 123 + 45 = 168; 200 − 75 = 125.','de',10,4,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(5,1,'matching','Nối mỗi bài toán lời văn với phép tính đúng.','Thêm vào thì cộng, bớt đi thì trừ: 15 + 8, 20 − 6, 32 + 29.','de',10,5,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(6,1,'matching','Nối mỗi tổng hoặc hiệu với giá trị của nó.','150 + 230 = 380; 500 − 145 = 355; 999 + 1 = 1000.','de',10,6,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(7,2,'sort','Kéo mỗi phép tính vào nhóm ĐÚNG hoặc SAI.','12 × 5 = 60 đúng; 7 × 8 = 56 nên 54 là sai; 144 : 12 = 12 đúng; 96 : 4 = 24 nên 26 là sai.','de',10,1,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(8,2,'sort','Kéo mỗi phép tính vào nhóm kết quả CHẴN hoặc LẺ.','Số lẻ nhân số lẻ cho kết quả lẻ; số chẵn chia hết cho 2 cho kết quả chẵn.','de',10,2,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(9,2,'sort','Kéo mỗi phép tính vào nhóm NHÂN hoặc CHIA.','Dấu × là phép nhân, dấu : là phép chia.','de',10,3,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(10,2,'fill','25 × 4 = ___','25 × 4 = 100. Có thể nhẩm: 25 × 4 = (20 × 4) + (5 × 4) = 80 + 20 = 100.','de',10,4,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(11,2,'fill','Một hộp có 12 cái bánh. 8 hộp như vậy có tất cả ___ cái bánh.','12 × 8 = 96 cái bánh.','de',10,5,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(12,2,'fill','360 : 9 = ___','360 : 9 = 40. Nhẩm: 36 : 9 = 4 nên 360 : 9 = 40.','de',10,6,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(13,3,'quiz','1/3 + 2/3 = ?','Hai phân số cùng mẫu số: cộng tử số, giữ nguyên mẫu số. 1/3 + 2/3 = 3/3 = 1.','trung_binh',10,1,1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(14,3,'quiz','3/4 − 1/4 = ?','3/4 − 1/4 = 2/4, rút gọn được 1/2.','trung_binh',10,2,1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(15,3,'quiz','1/2 + 1/4 = ?','Quy đồng: 1/2 = 2/4. Vậy 2/4 + 1/4 = 3/4.','trung_binh',10,3,1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(16,3,'matching','Nối mỗi phép tính với kết quả đúng (đã rút gọn).','Cùng mẫu số thì cộng/trừ tử số: 4/6 rút gọn = 2/3; 4/8 rút gọn = 1/2; 4/10 rút gọn = 2/5.','trung_binh',10,4,1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(17,3,'matching','Nối mỗi yêu cầu với kết quả đúng.','Quy đồng là đưa về cùng mẫu số; tối giản là chia cả tử và mẫu cho ước chung lớn nhất.','trung_binh',10,5,1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(18,3,'matching','Nối mỗi phép tính với kết quả đúng.','1/2 + 1/3 = 3/6 + 2/6 = 5/6; 2/3 − 1/6 = 4/6 − 1/6 = 1/2; 3/4 + 1/8 = 6/8 + 1/8 = 7/8.','trung_binh',10,6,1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(19,4,'sort','Kéo mỗi giá trị vào nhóm LỚN HƠN 1 hoặc NHỎ HƠN 1.','Phân số có tử lớn hơn mẫu thì lớn hơn 1, tử nhỏ hơn mẫu thì nhỏ hơn 1.','trung_binh',10,1,1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(20,4,'sort','Kéo mỗi phép tính vào nhóm NHÂN hoặc CHIA.','Dấu × là phép nhân, dấu : là phép chia.','trung_binh',10,2,1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(21,4,'sort','Kéo mỗi phép tính vào nhóm ĐÚNG hoặc SAI.','4/7 : 4 = 4/28 = 1/7, nên đáp án 1/28 là sai.','trung_binh',10,3,1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(22,4,'fill','2/3 × 3/4 = ___','Nhân tử với tử, mẫu với mẫu: 6/12 = 1/2.','trung_binh',10,4,1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(23,4,'fill','3/5 : 2 = ___','3/5 : 2 = 3/5 × 1/2 = 3/10.','trung_binh',10,5,1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(24,4,'fill','5/6 : 5 = ___','5/6 : 5 = 5/6 × 1/5 = 5/30 = 1/6.','trung_binh',10,6,1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(25,5,'quiz','Thu gọn đơn thức 2x · 3x² ta được','Nhân hệ số: 2 × 3 = 6; nhân biến: x · x² = x³. Kết quả 6x³.','trung_binh',10,1,1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(26,5,'quiz','Bậc của đơn thức 4x²y³ là','Bậc của đơn thức là tổng số mũ của các biến: 2 + 3 = 5.','trung_binh',10,2,1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(27,5,'quiz','Đơn thức nào đồng dạng với 5xy²?','Hai đơn thức đồng dạng có cùng phần biến: cùng là xy².','trung_binh',10,3,1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(28,5,'matching','Nối mỗi phép nhân đơn thức với kết quả thu gọn.','Nhân hệ số với hệ số, cộng số mũ của cùng biến: a·a = a², x²·x = x³, y·y³ = y⁴.','trung_binh',10,4,1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(29,5,'matching','Nối mỗi đơn thức với bậc của nó.','Bậc là tổng số mũ các biến; đơn thức hằng (số) có bậc 0.','trung_binh',10,5,1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(30,5,'matching','Nối mỗi phép cộng/trừ đơn thức đồng dạng với kết quả.','Chỉ cộng/trừ được các đơn thức đồng dạng: cộng hệ số, giữ nguyên phần biến.','trung_binh',10,6,1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(31,6,'sort','Kéo mỗi phép tính vào nhóm làm TRƯỚC hoặc làm SAU.','Thứ tự: trong ngoặc trước, rồi đến nhân/chia, cuối cùng cộng/trừ.','kho',10,1,1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(32,6,'sort','Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.','Thay x = 3 vào 2x² được 2 × 9 = 18, không phải 12.','kho',10,2,1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(33,6,'sort','Kéo mỗi biểu thức vào nhóm MỘT BIẾN hoặc HAI BIẾN.','Biểu thức một biến chỉ chứa một chữ (x), hai biến chứa hai chữ (x và y, hoặc a và b).','kho',10,3,1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(34,6,'fill','Với x = 5, giá trị của biểu thức 2x + 3 là ___','Thay x = 5: 2 × 5 + 3 = 10 + 3 = 13.','kho',10,4,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(35,6,'fill','Với a = 2 và b = 3, giá trị của biểu thức a² + b là ___','Thay a = 2, b = 3: 2² + 3 = 4 + 3 = 7.','kho',10,5,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(36,6,'fill','Với x = 4, giá trị của biểu thức 5x − 7 là ___','Thay x = 4: 5 × 4 − 7 = 20 − 7 = 13.','kho',10,6,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(37,7,'quiz','Góc nào sau đây là góc nhọn?','Góc nhọn có số đo nhỏ hơn 90 độ. Góc 45 độ là góc nhọn.','de',10,1,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(38,7,'quiz','Hai đường thẳng không bao giờ cắt nhau gọi là hai đường thẳng','Hai đường thẳng song song không có điểm chung nào.','de',10,2,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(39,7,'quiz','Góc bẹt có số đo bằng','Góc bẹt tạo bởi hai tia đối nhau, số đo 180 độ.','de',10,3,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(40,7,'matching','Nối mỗi loại góc với số đo của nó.','Góc vuông = 90°; góc nhọn < 90°; góc tù nằm giữa 90° và 180°.','de',10,4,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(41,7,'matching','Nối mỗi khái niệm với mô tả đúng.','Góc bẹt = 180°; vuông góc tạo góc 90°; trung trực vừa vuông góc vừa qua trung điểm.','de',10,5,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(42,7,'matching','Nối mỗi khái niệm với ý nghĩa của nó.','Tia phân giác chia đôi góc; hai góc kề bù bù nhau thành 180°.','de',10,6,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(43,8,'sort','Kéo mỗi mô tả vào loại tam giác đúng.','Góc 100° là góc tù, nên tam giác có góc 100° là tam giác tù.','trung_binh',10,1,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(44,8,'sort','Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.','Tam giác chỉ có tối đa một góc vuông hoặc một góc tù, vì tổng ba góc là 180°.','trung_binh',10,2,1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(45,8,'sort','Kéo mỗi yếu tố vào nhóm CẠNH hoặc GÓC của tam giác ABC.','Tam giác ABC có ba cạnh AB, BC, CA và ba góc A, B, C.','trung_binh',10,3,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(46,8,'fill','Tam giác ABC có góc A = 60°, góc B = 70°. Góc C bằng ___ độ.','Góc C = 180° − 60° − 70° = 50°.','trung_binh',10,4,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(47,8,'fill','Tổng số đo ba góc trong của một tam giác bằng ___ độ.','Đây là tính chất cơ bản nhất của tam giác.','trung_binh',10,5,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(48,8,'fill','Tam giác đều có mỗi góc bằng ___ độ.','Ba góc bằng nhau, tổng 180°, nên mỗi góc 180° : 3 = 60°.','trung_binh',10,6,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(49,9,'quiz','Từ nào sau đây là danh từ?','Danh từ chỉ người, vật, hiện tượng. \"Học sinh\" chỉ người nên là danh từ.','de',10,1,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(50,9,'quiz','Từ nào sau đây là động từ?','Động từ chỉ hành động, trạng thái. \"Bơi\" là hành động nên là động từ.','de',10,2,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(51,9,'quiz','Từ nào sau đây là tính từ?','Tính từ chỉ đặc điểm, tính chất. \"Cao\" chỉ đặc điểm chiều cao nên là tính từ.','de',10,3,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(52,9,'matching','Nối mỗi từ với từ loại của nó.','\"Sách\" chỉ đồ vật (danh từ), \"hát\" chỉ hành động (động từ), \"đỏ\" chỉ màu sắc (tính từ).','de',10,4,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(53,9,'matching','Nối mỗi từ với từ loại của nó.','\"Con mèo\" chỉ con vật (danh từ), \"nhảy\" chỉ hành động (động từ), \"vui\" chỉ trạng thái (tính từ).','de',10,5,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(54,9,'matching','Nối mỗi từ với từ loại của nó.','\"Một\" chỉ số lượng (số từ), \"những\" chỉ lượng khái quát (lượng từ), \"đang\" bổ nghĩa cho động từ (phó từ).','de',10,6,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(55,10,'sort','Kéo mỗi bộ phận vào nhóm CHỦ NGỮ hoặc VỊ NGỮ.','Chủ ngữ trả lời \"ai? cái gì?\", vị ngữ trả lời \"làm gì? thế nào?\".','trung_binh',10,1,1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(56,10,'sort','Kéo mỗi câu vào kiểu câu đúng.','Câu kể kết thúc bằng dấu chấm, câu hỏi bằng dấu hỏi, câu cảm bộc lộ cảm xúc, câu khiến ra lệnh/yêu cầu.','trung_binh',10,2,1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(57,10,'sort','Kéo mỗi thành phần vào nhóm CHÍNH hoặc PHỤ.','Chủ ngữ và vị ngữ là hai thành phần chính bắt buộc của câu.','trung_binh',10,3,1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(58,10,'fill','Trong câu \"Hoa nở rộ\", chủ ngữ là ___','Chủ ngữ trả lời câu hỏi \"cái gì nở rộ?\" — đó là \"Hoa\".','trung_binh',10,4,1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(59,10,'fill','Trong câu \"Bé đang chơi\", vị ngữ là ___','Vị ngữ trả lời \"bé làm gì?\" — đó là \"đang chơi\".','trung_binh',10,5,1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(60,10,'fill','Câu \"Trời mưa to quá!\" thuộc kiểu câu ___','Câu bộc lộ cảm xúc ngạc nhiên, kết thúc bằng dấu chấm than là câu cảm.','trung_binh',10,6,1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(61,11,'quiz','Từ nào viết ĐÚNG chính tả?','\"Nghỉ ngơi\" (dấu hỏi) nghĩa là dừng làm việc để thư giãn.','de',10,1,1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(62,11,'quiz','Từ nào viết ĐÚNG chính tả?','\"Suy nghĩ\" (dấu ngã) nghĩa là dùng trí óc để xem xét.','de',10,2,1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(63,11,'quiz','Từ nào viết ĐÚNG chính tả?','\"Mãi mãi\" (dấu ngã) nghĩa là lâu dài, không bao giờ hết.','de',10,3,1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(64,11,'matching','Nối mỗi từ với dấu thanh đúng của nó.','\"Nghỉ ngơi\" dấu hỏi, \"suy nghĩ\" dấu ngã, \"mãi mãi\" dấu ngã, \"mải mê\" dấu hỏi.','de',10,4,1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(65,11,'matching','Nối mỗi từ với dấu thanh đúng của nó.','\"Vẻ đẹp\" dấu hỏi, \"vẽ tranh\" dấu ngã, \"kẻ thù\" dấu hỏi.','de',10,5,1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(66,11,'matching','Nối mỗi từ với dấu thanh đúng của nó.','\"Cũ\" (không mới) dấu ngã, \"củ khoai\" dấu hỏi, \"đũa\" (dụng cụ ăn) dấu ngã, \"đủ\" dấu hỏi.','de',10,6,1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(67,12,'sort','Kéo mỗi từ vào nhóm âm đầu CH hoặc TR.','\"Chú, chim\" bắt đầu bằng ch; \"trâu, tre\" bắt đầu bằng tr.','de',10,1,1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(68,12,'sort','Kéo mỗi từ vào nhóm âm đầu D, GI hoặc R.','\"Dê, dao\" âm d; \"gió\" âm gi; \"rừng\" âm r.','de',10,2,1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(69,12,'sort','Kéo mỗi cách viết vào nhóm ĐÚNG hoặc SAI.','\"Con giun\" viết gi, \"mặt trời\" viết tr.','de',10,3,1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(70,12,'fill','Điền âm đầu còn thiếu: ___im hót líu lo.','\"Chim\" viết với âm đầu ch.','de',10,4,1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(71,12,'fill','Điền âm đầu còn thiếu: con ___âu ăn cỏ.','\"Trâu\" viết với âm đầu tr.','de',10,5,1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(72,12,'fill','Điền âm đầu còn thiếu: ___ó thổi mạnh.','\"Gió\" viết với âm đầu gi.','de',10,6,1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(73,13,'quiz','Bài văn miêu tả thường gồm mấy phần?','Bài văn miêu tả gồm 3 phần: mở bài, thân bài, kết bài.','trung_binh',10,1,1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(74,13,'quiz','Phần nào nêu cảm nghĩ của người viết về đối tượng miêu tả?','Kết bài nêu cảm nghĩ, tình cảm của người viết.','trung_binh',10,2,1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(75,13,'quiz','Từ ngữ nào gợi hình ảnh rõ nhất?','\"Lấp lánh\" là từ láy gợi hình ảnh ánh sáng nhấp nháy, rất hợp văn miêu tả.','trung_binh',10,3,1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(76,13,'matching','Nối mỗi phần của bài văn với nhiệm vụ của nó.','Mở bài giới thiệu, thân bài tả chi tiết từ bao quát đến cụ thể, kết bài nêu cảm nghĩ.','trung_binh',10,4,1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(77,13,'matching','Nối mỗi kiểu bài với nội dung miêu tả.','Mỗi đối tượng có trọng tâm miêu tả khác nhau.','trung_binh',10,5,1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(78,13,'matching','Nối mỗi loại từ với ví dụ đúng.','Từ láy gợi hình gợi cảm, từ ghép gọi tên sự vật, so sánh làm hình ảnh sinh động.','trung_binh',10,6,1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(79,14,'sort','Kéo mỗi câu vào biện pháp tu từ đúng.','So sánh dùng từ \"như\"; nhân hoá gán đặc điểm con người cho sự vật; ẩn dụ gọi tên sự vật này bằng tên sự vật khác.','trung_binh',10,1,1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(80,14,'sort','Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.','\"Mắt sáng như sao\" có từ \"như\" nên là so sánh, không phải ẩn dụ.','trung_binh',10,2,1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(81,14,'sort','Kéo mỗi câu vào nhóm CÓ hoặc KHÔNG dùng biện pháp tu từ.','Câu có từ so sánh \"như\" hoặc gán đặc điểm con người là có biện pháp tu từ.','trung_binh',10,3,1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(82,14,'fill','Điền từ còn thiếu: \"Trăng tròn ___ cái đĩa\".','Từ \"như\" nối hai sự vật để so sánh.','trung_binh',10,4,1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(83,14,'fill','Biện pháp gán đặc điểm của con người cho sự vật gọi là ___.','Ví dụ: \"chị gió thì thầm\", \"hoa cười\".','trung_binh',10,5,1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(84,14,'fill','Trong câu \"Lá vàng rơi\", từ \"vàng\" thuộc từ loại ___.','\"Vàng\" chỉ màu sắc của lá nên là tính từ.','trung_binh',10,6,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(85,15,'quiz','What is \"bố\" in English?','\"Father\" means \"bố\". \"Mother\" is \"mẹ\", \"brother\" is \"anh/em trai\", \"sister\" is \"chị/em gái\".','de',10,1,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(86,15,'quiz','What is \"chị gái\" in English?','\"Sister\" means \"chị gái\" or \"em gái\". \"Brother\" means \"anh/em trai\".','de',10,2,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(87,15,'quiz','What is \"ông\" in English?','\"Grandfather\" means \"ông\". \"Grandmother\" means \"bà\".','de',10,3,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(88,15,'matching','Match each English word with its Vietnamese meaning.','father = bố, mother = mẹ, brother = anh/em trai, sister = chị/em gái.','de',10,4,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(89,15,'matching','Match each English word with its Vietnamese meaning.','grandfather = ông, grandmother = bà, uncle = chú/bác trai.','de',10,5,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(90,15,'matching','Match each English word with its Vietnamese meaning.','aunt = cô/dì, cousin = anh chị em họ, son = con trai.','de',10,6,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(91,16,'sort','Drag each word into SCHOOL THINGS or PEOPLE.','Book and pen are things; teacher and student are people.','de',10,1,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(92,16,'sort','Drag each statement into TRUE or FALSE.','\"Eraser\" means \"cục tẩy\", \"notebook\" means \"vở\".','de',10,2,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(93,16,'sort','Drag each word into IN THE CLASSROOM or IN THE BAG.','Desk and board stay in the classroom; pencil and ruler go in the bag.','de',10,3,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(94,16,'fill','I write with a ___. (bút)','A pen is used for writing.','de',10,4,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(95,16,'fill','The ___ teaches us English. (giáo viên)','A teacher teaches students.','de',10,5,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(96,16,'fill','Open your ___ to page 10. (sách)','\"Book\" means \"sách\".','de',10,6,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(97,17,'quiz','She ___ to school every day.','With he/she/it, add -s/-es to the verb: she goes.','trung_binh',10,1,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(98,17,'quiz','They ___ football on Sundays.','With I/you/we/they, keep the base verb: they play.','trung_binh',10,2,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(99,17,'quiz','He ___ like milk.','Negative with he/she/it uses \"doesn’t\": he doesn’t like milk.','trung_binh',10,3,1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(100,17,'matching','Match each subject with the correct verb form of \"go\".','I/you/we/they + go; he/she/it + goes.','trung_binh',10,4,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(101,17,'matching','Match each full form with its short form.','do not = don’t; does not = doesn’t.','trung_binh',10,5,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(102,17,'matching','Match each sentence start with the correct verb.','she watches, they study, he has.','trung_binh',10,6,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(103,18,'sort','Drag each sentence into the correct preposition: ON, IN or UNDER.','on = on a surface, in = inside, under = below.','de',10,1,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(104,18,'sort','Drag each statement into TRUE or FALSE.','On a table surface we say \"on the table\", not \"in the table\".','de',10,2,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(105,18,'sort','Drag each phrase into INDOORS or OUTDOORS.','Kitchen and bedroom are indoors; garden and playground are outdoors.','de',10,3,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(106,18,'fill','The picture is ___ the wall.','Pictures hang ON the wall (on a surface).','de',10,4,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(107,18,'fill','She sits ___ me. (bên cạnh tôi)','\"Next to\" means \"bên cạnh\".','de',10,5,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(108,18,'fill','The dog is ___ the table. (dưới gầm bàn)','\"Under\" means \"phía dưới\".','de',10,6,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(109,19,'quiz','What is \"đau đầu\" in English?','\"Headache\" means \"đau đầu\". \"Head\" = đầu, \"ache\" = đau.','de',10,1,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(110,19,'quiz','What is \"khoẻ mạnh\" in English?','\"Healthy\" means \"khoẻ mạnh\". \"Sick\" means \"ốm\".','de',10,2,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(111,19,'quiz','What is \"bác sĩ\" in English?','\"Doctor\" means \"bác sĩ\". \"Nurse\" means \"y tá\".','de',10,3,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(112,19,'matching','Match each English word with its Vietnamese meaning.','headache = đau đầu, fever = sốt, cough = ho.','de',10,4,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(113,19,'matching','Match each English word with its Vietnamese meaning.','healthy = khoẻ mạnh, sick = ốm, tired = mệt.','de',10,5,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(114,19,'matching','Match each English word with its Vietnamese meaning.','doctor = bác sĩ, medicine = thuốc, hospital = bệnh viện.','de',10,6,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(115,20,'sort','Drag each word into THINGS TO BRING or PLACES.','Suitcase and ticket are things we bring; hotel and beach are places we visit.','de',10,1,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(116,20,'sort','Drag each statement into TRUE or FALSE.','\"Train\" means \"tàu hoả\"; \"plane\" means \"máy bay\".','de',10,2,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(117,20,'sort','Drag each \"by ...\" phrase into the correct vehicle.','by plane = bằng máy bay, by train = bằng tàu hoả, by bus = bằng xe buýt, by bike = bằng xe đạp.','de',10,3,1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(118,20,'fill','We stay at a ___ when we travel. (khách sạn)','\"Hotel\" means \"khách sạn\".','de',10,4,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(119,20,'fill','I need a ___ to get on the plane. (vé)','\"Ticket\" means \"vé\".','de',10,5,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(120,20,'fill','We swim at the ___. (bãi biển)','\"Beach\" means \"bãi biển\".','de',10,6,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(121,21,'quiz','Xương nào bảo vệ não của chúng ta?','Xương sọ tạo thành hộp sọ bao bọc và bảo vệ não.','de',10,1,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(122,21,'quiz','Vai trò chính của hệ xương là gì?','Hệ xương nâng đỡ cơ thể, tạo khung và bảo vệ các cơ quan bên trong như não, tim, phổi.','de',10,2,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(123,21,'quiz','Xương dài nhất trong cơ thể người là xương nào?','Xương đùi là xương dài và chắc nhất trong cơ thể người.','de',10,3,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(124,21,'matching','Nối mỗi xương với vai trò của nó.','Xương sọ bảo vệ não, xương sườn bảo vệ tim phổi, cột sống nâng đỡ toàn cơ thể.','de',10,4,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(125,21,'matching','Nối mỗi bộ phận với mô tả đúng.','Khớp là chỗ nối giữa hai xương, giúp cơ thể cử động.','de',10,5,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(126,21,'matching','Nối mỗi yếu tố với tác dụng của nó với xương.','Canxi (có nhiều trong sữa) và vận động giúp xương chắc khoẻ.','de',10,6,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(127,22,'sort','Kéo mỗi cơ quan vào nhóm TRƯỚC DẠ DÀY hoặc SAU DẠ DÀY (theo đường đi của thức ăn).','Đường đi của thức ăn: miệng → thực quản → dạ dày → ruột non → ruột già.','trung_binh',10,1,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(128,22,'sort','Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.','Thứ tự đúng: miệng → thực quản → dạ dày → ruột non → ruột già.','trung_binh',10,2,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(129,22,'sort','Kéo mỗi cơ quan vào nhóm CƠ QUAN TIÊU HOÁ hoặc KHÔNG PHẢI.','Tim thuộc hệ tuần hoàn, phổi thuộc hệ hô hấp.','trung_binh',10,3,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(130,22,'fill','Thức ăn từ miệng đi xuống ___ rồi mới đến dạ dày.','Thực quản là ống nối miệng với dạ dày.','trung_binh',10,4,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(131,22,'fill','Chất dinh dưỡng được hấp thụ chủ yếu ở ___.','Ruột non có nhiều nếp gấp giúp hấp thụ chất dinh dưỡng vào máu.','trung_binh',10,5,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(132,22,'fill','Cơ quan nhào trộn thức ăn thành chất lỏng là ___.','Dạ dày co bóp và tiết dịch vị để tiêu hoá thức ăn.','trung_binh',10,6,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(133,23,'quiz','Nước đá tồn tại ở trạng thái nào?','Nước đá có hình dạng cố định nên ở trạng thái rắn.','de',10,1,1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(134,23,'quiz','Chất khí có đặc điểm nào?','Chất khí không có hình dạng cố định, luôn lan toả chiếm đầy bình chứa.','de',10,2,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(135,23,'quiz','Khi đun sôi, nước chuyển từ thể lỏng sang thể nào?','Nước sôi bốc hơi thành hơi nước ở thể khí.','de',10,3,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(136,23,'matching','Nối mỗi ví dụ với trạng thái của nó.','Đá: rắn; nước: lỏng; hơi nước: khí.','de',10,4,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(137,23,'matching','Nối mỗi ví dụ với trạng thái của nó.','Sắt: rắn; dầu ăn: lỏng; không khí: khí.','de',10,5,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(138,23,'matching','Nối mỗi quá trình với sự chuyển thể tương ứng.','Đá tan: nóng chảy; nước đóng băng: đông đặc; nước bốc hơi: bay hơi.','de',10,6,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(139,24,'sort','Kéo mỗi mô tả vào nhóm TÍNH CHẤT CỦA NƯỚC hoặc KHÔNG PHẢI.','Nước tinh khiết không màu, không mùi, không vị.','de',10,1,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(140,24,'sort','Kéo mỗi hành động vào nhóm TIẾT KIỆM NƯỚC hoặc LÃNG PHÍ NƯỚC.','Nước sạch có hạn, cần dùng tiết kiệm mỗi ngày.','de',10,2,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(141,24,'sort','Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.','Con người chỉ sống được vài ngày nếu thiếu nước.','de',10,3,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(142,24,'fill','Nhiệt độ sôi của nước ở điều kiện thường là ___ độ C.','Nước sôi ở 100°C và đóng băng ở 0°C.','de',10,4,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(143,24,'fill','Nước tồn tại ở ba trạng thái: rắn, lỏng và ___.','Ba trạng thái: nước đá (rắn), nước (lỏng), hơi nước (khí).','de',10,5,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(144,24,'fill','Để cơ thể khoẻ mạnh, mỗi ngày chúng ta cần uống đủ ___.','Nước chiếm phần lớn cơ thể người và rất cần cho mọi hoạt động sống.','de',10,6,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(145,25,'quiz','Nguồn năng lượng nào sau đây là năng lượng tái tạo?','Gió không bao giờ cạn kiệt nên là năng lượng tái tạo. Than đá, dầu mỏ, khí đốt sẽ cạn dần.','trung_binh',10,1,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(146,25,'quiz','Tấm pin mặt trời biến đổi năng lượng mặt trời thành dạng năng lượng nào?','Pin mặt trời biến ánh sáng mặt trời thành điện năng.','trung_binh',10,2,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(147,25,'quiz','Nguồn năng lượng nào KHÔNG tái tạo được?','Than đá hình thành qua hàng triệu năm nên dùng hết là cạn kiệt.','trung_binh',10,3,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(148,25,'matching','Nối mỗi nguồn năng lượng với loại của nó.','Mặt trời, gió, nước là tái tạo; than đá, dầu mỏ là không tái tạo.','trung_binh',10,4,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(149,25,'matching','Nối mỗi nguồn với ứng dụng của nó.','Thuỷ điện dùng sức nước, xe cộ dùng xăng dầu từ dầu mỏ.','trung_binh',10,5,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(150,25,'matching','Nối mỗi hành động với ý nghĩa của nó.','Tiết kiệm điện và dùng năng lượng sạch giúp bảo vệ môi trường.','trung_binh',10,6,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(151,26,'sort','Kéo mỗi vật liệu vào nhóm VẬT DẪN ĐIỆN hoặc VẬT CÁCH ĐIỆN.','Kim loại (đồng, sắt) dẫn điện; nhựa, gỗ khô cách điện.','trung_binh',10,1,1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(152,26,'sort','Kéo mỗi bộ phận vào nhóm THUỘC MẠCH ĐIỆN hoặc KHÔNG THUỘC.','Mạch điện đơn giản gồm: nguồn điện, dây dẫn, bóng đèn, công tắc.','trung_binh',10,2,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(153,26,'sort','Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.','Nhựa cách điện; tuyệt đối không chạm vào ổ điện vì rất nguy hiểm.','trung_binh',10,3,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(154,26,'fill','Mạch điện đơn giản gồm nguồn điện, dây dẫn, bóng đèn và ___.','Công tắc dùng để đóng, ngắt dòng điện.','trung_binh',10,4,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(155,26,'fill','Dòng điện chạy qua dây tóc làm bóng đèn phát ___.','Dòng điện có tác dụng nhiệt và phát sáng.','trung_binh',10,5,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(156,26,'fill','Vật liệu cho dòng điện đi qua dễ dàng gọi là vật ___ điện.','Kim loại là vật dẫn điện tốt.','trung_binh',10,6,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(157,27,'quiz','Nước Văn Lang do ai dựng nên?','Các vua Hùng là những người đầu tiên dựng nước Văn Lang – nhà nước đầu tiên của người Việt.','de',10,1,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(158,27,'quiz','Kinh đô của nước Văn Lang đặt ở đâu?','Kinh đô Văn Lang đặt ở Phong Châu (thuộc Phú Thọ ngày nay).','de',10,2,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(159,27,'quiz','Người đứng đầu nước Văn Lang được gọi là gì?','Người đứng đầu nước Văn Lang gọi là Vua Hùng (Hùng Vương).','de',10,3,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(160,27,'matching','Nối mỗi tên gọi với ý nghĩa lịch sử của nó.','Vua Hùng đứng đầu, đóng đô ở Phong Châu, Lạc tướng giúp việc cai trị các bộ.','de',10,4,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(161,27,'matching','Nối mỗi di sản với thời kỳ của nó.','Trống đồng Đông Sơn là biểu tượng văn hoá rực rỡ thời Văn Lang.','de',10,5,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(162,27,'matching','Nối mỗi nhân vật/sự kiện với mô tả đúng.','An Dương Vương lập nước Âu Lạc, xây thành Cổ Loa với nỏ thần.','de',10,6,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(163,28,'sort','Kéo mỗi anh hùng vào cuộc khởi nghĩa mà người đó lãnh đạo.','Hai Bà Trưng (năm 40), Bà Triệu (năm 248), Lý Bí (năm 542), Ngô Quyền (năm 938).','de',10,1,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(164,28,'sort','Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.','Lý Bí xưng đế năm 544, lập nước Vạn Xuân.','de',10,2,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(165,28,'sort','Kéo mỗi tên gọi vào nhóm ANH HÙNG hoặc ĐỊA DANH.','Hát Môn là nơi Hai Bà Trưng tuẫn tiết; Bạch Đằng là nơi Ngô Quyền đại phá quân Nam Hán.','de',10,3,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(166,28,'fill','Hai Bà Trưng phất cờ khởi nghĩa năm ___.','Cuộc khởi nghĩa Hai Bà Trưng bùng nổ năm 40 sau Công nguyên.','de',10,4,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(167,28,'fill','Ngô Quyền đánh tan quân Nam Hán trên sông ___ năm 938.','Trận Bạch Đằng năm 938 chấm dứt hơn 1000 năm Bắc thuộc.','de',10,5,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(168,28,'fill','Bà Triệu từng nói: \"Tôi muốn cưỡi cơn ___, đạp luồng sóng dữ...\"','Câu nói thể hiện khí phách của người nữ anh hùng dân tộc.','de',10,6,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(169,29,'quiz','Đinh Bộ Lĩnh đã dẹp loạn bao nhiêu sứ quân?','Đinh Bộ Lĩnh dẹp loạn 12 sứ quân, thống nhất đất nước.','de',10,1,1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(170,29,'quiz','Đinh Bộ Lĩnh đặt tên nước ta là gì?','Năm 968, Đinh Bộ Lĩnh lên ngôi, đặt tên nước là Đại Cồ Việt.','de',10,2,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(171,29,'quiz','Kinh đô của nhà Đinh đặt ở đâu?','Nhà Đinh đóng đô ở Hoa Lư (Ninh Bình ngày nay).','de',10,3,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(172,29,'matching','Nối mỗi tên gọi với ý nghĩa lịch sử của nó.','Đinh Bộ Lĩnh lập nước Đại Cồ Việt, đóng đô ở Hoa Lư.','de',10,4,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(173,29,'matching','Nối mỗi sự kiện với năm diễn ra.','Năm 968 Đinh Bộ Lĩnh xưng đế hiệu Đinh Tiên Hoàng.','de',10,5,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(174,29,'matching','Nối mỗi chi tiết với ý nghĩa của nó.','Thuở nhỏ Đinh Bộ Lĩnh thường lấy cờ lau tập trận với bạn bè.','de',10,6,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(175,30,'sort','Kéo mỗi nhân vật vào triều đại đúng.','Lê Đại Hành chính là hiệu của vua Lê Hoàn nhà Tiền Lê.','de',10,1,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(176,30,'sort','Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.','Nhà Tiền Lê tiếp tục đóng đô ở Hoa Lư; nhà Đinh chỉ tồn tại 12 năm (968–980).','de',10,2,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(177,30,'sort','Kéo mỗi nhân vật vào nhóm VUA hoặc TƯỚNG.','Phạm Cự Lượng và Đinh Điền là các tướng tài thời Đinh – Tiền Lê.','de',10,3,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(178,30,'fill','Lê Hoàn đánh tan quân Tống xâm lược năm ___.','Chiến thắng năm 981 bảo vệ vững chắc nền độc lập non trẻ.','de',10,4,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(179,30,'fill','Nhà Tiền Lê tiếp tục đóng đô ở ___ như nhà Đinh.','Hoa Lư là kinh đô của cả nhà Đinh và nhà Tiền Lê.','de',10,5,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(180,30,'fill','Lê Hoàn lên ngôi vua, lấy hiệu là ___.','Lê Đại Hành nghĩa là vị vua lớn họ Lê.','de',10,6,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(181,31,'quiz','Quân dân Đại Việt đã đánh bại quân Nguyên – Mông mấy lần?','Ba lần kháng chiến thắng lợi: 1258, 1285 và 1287–1288.','trung_binh',10,1,1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(182,31,'quiz','Cuộc kháng chiến lần thứ hai chống quân Nguyên – Mông diễn ra năm nào?','Lần 1: 1258; lần 2: 1285; lần 3: 1287–1288.','trung_binh',10,2,1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(183,31,'quiz','Ai là tổng chỉ huy cuộc kháng chiến lần 2 và lần 3?','Trần Hưng Đạo (Trần Quốc Tuấn) là tổng chỉ huy kháng chiến.','trung_binh',10,3,1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(184,31,'matching','Nối mỗi năm với cuộc kháng chiến tương ứng.','Cả ba lần quân Nguyên – Mông đều bị đánh bại.','trung_binh',10,4,1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(185,31,'matching','Nối mỗi nhân vật với vai trò của người đó.','Vua tôi nhà Trần đồng lòng đánh giặc.','trung_binh',10,5,1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(186,31,'matching','Nối mỗi địa danh với sự kiện lịch sử.','Trận Bạch Đằng 1288 tiêu diệt hoàn toàn đạo quân xâm lược.','trung_binh',10,6,1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(187,32,'sort','Kéo mỗi tác phẩm vào nhóm CỦA TRẦN HƯNG ĐẠO hoặc KHÔNG PHẢI.','\"Nam quốc sơn hà\" gắn với Lý Thường Kiệt; \"Đại Việt sử ký toàn thư\" do Ngô Sĩ Liên biên soạn.','trung_binh',10,1,1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(188,32,'sort','Kéo mỗi khẳng định vào nhóm ĐÚNG hoặc SAI.','Ông là tướng, không phải vua; là bậc anh hùng dân tộc.','trung_binh',10,2,1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(189,32,'sort','Kéo mỗi trận đánh vào cuộc kháng chiến đúng.','Chương Dương, Tây Kết thuộc lần 2; Vân Đồn, Bạch Đằng thuộc lần 3.','trung_binh',10,3,1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(190,32,'fill','Trần Hưng Đạo tên thật là ___.','Trần Quốc Tuấn được phong Hưng Đạo Đại Vương.','trung_binh',10,4,1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(191,32,'fill','Tác phẩm kêu gọi tướng sĩ đánh giặc của ông có tên là ___.','Hịch tướng sĩ là áng văn bất hủ về lòng yêu nước.','trung_binh',10,5,1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(192,32,'fill','Nhân dân ta tôn kính gọi ông là ___.','Đền thờ ông có ở nhiều nơi, tiêu biểu là đền Kiếp Bạc.','trung_binh',10,6,1,'2026-10-07 20:27:20','2026-10-07 20:27:20');
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
set autocommit=0;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `skills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `is_demo` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sk_topic` (`topic_id`),
  CONSTRAINT `skills_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `skills` VALUES
(1,1,'Cộng, trừ, nhân, chia số tự nhiên','kn-toan-so-tu-nhien-1','Cộng, trừ, nhân, chia số tự nhiên',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(2,2,'Cộng, trừ, nhân, chia phân số','kn-toan-phan-so-1','Cộng, trừ, nhân, chia phân số',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(3,3,'Thu gọn và tính giá trị biểu thức','kn-toan-dai-so-1','Thu gọn và tính giá trị biểu thức',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(4,4,'Nhận biết góc và tam giác','kn-toan-hinh-hoc-1','Nhận biết góc và tam giác',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(5,5,'Nhận diện từ loại và câu','kn-tv-tu-cau-1','Nhận diện từ loại và câu',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(6,6,'Viết đúng chính tả','kn-tv-chinh-ta-1','Viết đúng chính tả',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(7,7,'Viết và cảm thụ văn miêu tả','kn-tv-mieu-ta-1','Viết và cảm thụ văn miêu tả',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(8,8,'Family and school vocabulary','kn-en-vocab-6-1','Family and school vocabulary',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(9,9,'Basic grammar','kn-en-grammar-1','Basic grammar',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(10,10,'Health and travel vocabulary','kn-en-vocab-7-1','Health and travel vocabulary',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(11,11,'Hệ xương và hệ tiêu hoá','kn-kh-co-the-1','Hệ xương và hệ tiêu hoá',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(12,12,'Trạng thái chất và nước','kn-kh-chat-1','Trạng thái chất và nước',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(13,13,'Nguồn năng lượng và điện','kn-kh-nang-luong-1','Nguồn năng lượng và điện',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(14,14,'Nước Văn Lang và anh hùng dân tộc','kn-ls-dung-nuoc-1','Nước Văn Lang và anh hùng dân tộc',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(15,15,'Nhà Đinh và nhà Tiền Lê','kn-ls-dinh-tien-le-1','Nhà Đinh và nhà Tiền Lê',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(16,16,'Kháng chiến chống quân Nguyên – Mông','kn-ls-chong-nguyen-mong-1','Kháng chiến chống quân Nguyên – Mông',1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04');
/*!40000 ALTER TABLE `skills` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `sort_items`
--

DROP TABLE IF EXISTS `sort_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sort_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` bigint(20) unsigned NOT NULL,
  `item_text` varchar(255) NOT NULL,
  `category` varchar(64) NOT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `si_question` (`question_id`),
  CONSTRAINT `sort_items_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sort_items`
--

LOCK TABLES `sort_items` WRITE;
/*!40000 ALTER TABLE `sort_items` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `sort_items` VALUES
(1,7,'12 × 5 = 60','Đúng',1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(2,7,'7 × 8 = 54','Sai',2,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(3,7,'144 : 12 = 12','Đúng',3,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(4,7,'96 : 4 = 26','Sai',4,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(5,8,'15 × 3 = 45','Lẻ',1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(6,8,'24 : 2 = 12','Chẵn',2,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(7,8,'7 × 6 = 42','Chẵn',3,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(8,8,'9 × 5 = 45','Lẻ',4,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(9,9,'36 × 2','Nhân',1,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(10,9,'100 : 4','Chia',2,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(11,9,'15 × 4','Nhân',3,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(12,9,'81 : 9','Chia',4,'2026-10-07 20:27:05','2026-10-07 20:27:05'),
(13,19,'3/2','Lớn hơn 1',1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(14,19,'1/2','Nhỏ hơn 1',2,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(15,19,'5/3','Lớn hơn 1',3,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(16,19,'2/7','Nhỏ hơn 1',4,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(17,20,'2/3 × 4/5','Nhân',1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(18,20,'5/6 : 2/3','Chia',2,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(19,20,'1/2 × 3/4','Nhân',3,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(20,20,'7/8 : 1/4','Chia',4,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(21,21,'1/2 × 4 = 2','Đúng',1,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(22,21,'2/3 : 2 = 1/3','Đúng',2,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(23,21,'3/5 × 5/3 = 1','Đúng',3,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(24,21,'4/7 : 4 = 1/28','Sai',4,'2026-10-07 20:27:06','2026-10-07 20:27:06'),
(25,31,'Phép tính trong ngoặc','Trước',1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(26,31,'Phép nhân','Trước',2,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(27,31,'Phép cộng','Sau',3,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(28,31,'Phép trừ','Sau',4,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(29,32,'Với x = 2 thì 3x + 1 = 7','Đúng',1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(30,32,'Với x = 3 thì 2x² = 12','Sai',2,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(31,32,'Với y = 5 thì 10 − y = 5','Đúng',3,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(32,32,'Với a = 4 thì a² + 1 = 17','Đúng',4,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(33,33,'3x + 2','Một biến',1,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(34,33,'xy + 5','Hai biến',2,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(35,33,'x² − 4x + 1','Một biến',3,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(36,33,'2ab − b','Hai biến',4,'2026-10-07 20:27:07','2026-10-07 20:27:07'),
(37,43,'Tam giác có ba góc nhọn','Tam giác nhọn',1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(38,43,'Tam giác có một góc tù','Tam giác tù',2,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(39,43,'Tam giác có một góc vuông','Tam giác vuông',3,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(40,43,'Tam giác có góc A = 100°','Tam giác tù',4,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(41,44,'Tổng ba góc trong tam giác bằng 180 độ','Đúng',1,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(42,44,'Tam giác đều có ba góc bằng 60 độ','Đúng',2,'2026-10-07 20:27:08','2026-10-07 20:27:08'),
(43,44,'Tam giác vuông có hai góc vuông','Sai',3,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(44,44,'Một tam giác có thể có hai góc tù','Sai',4,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(45,45,'AB','Cạnh',1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(46,45,'Góc A','Góc',2,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(47,45,'BC','Cạnh',3,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(48,45,'Góc C','Góc',4,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(49,55,'Mẹ','Chủ ngữ',1,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(50,55,'đang nấu cơm','Vị ngữ',2,'2026-10-07 20:27:09','2026-10-07 20:27:09'),
(51,55,'Chim','Chủ ngữ',3,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(52,55,'hót líu lo','Vị ngữ',4,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(53,56,'Hôm nay trời đẹp.','Câu kể',1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(54,56,'Bạn tên gì?','Câu hỏi',2,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(55,56,'Ôi, đẹp quá!','Câu cảm',3,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(56,56,'Hãy giữ trật tự!','Câu khiến',4,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(57,57,'Chủ ngữ','Thành phần chính',1,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(58,57,'Vị ngữ','Thành phần chính',2,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(59,57,'Trạng ngữ','Thành phần phụ',3,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(60,57,'Bổ ngữ','Thành phần phụ',4,'2026-10-07 20:27:10','2026-10-07 20:27:10'),
(61,67,'chú','ch',1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(62,67,'trâu','tr',2,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(63,67,'chim','ch',3,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(64,67,'tre','tr',4,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(65,68,'dê','d',1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(66,68,'gió','gi',2,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(67,68,'rừng','r',3,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(68,68,'dao','d',4,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(69,69,'con trâu','Đúng',1,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(70,69,'cái chổi','Đúng',2,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(71,69,'con dun (con giun)','Sai',3,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(72,69,'mặt chời (mặt trời)','Sai',4,'2026-10-07 20:27:11','2026-10-07 20:27:11'),
(73,79,'Mặt trời như quả cầu lửa','So sánh',1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(74,79,'Chị gió thì thầm','Nhân hoá',2,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(75,79,'Trăng tròn như cái đĩa','So sánh',3,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(76,79,'Thuyền về có nhớ bến chăng (thuyền chỉ người đi xa)','Ẩn dụ',4,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(77,80,'\"Hoa cười\" là nhân hoá','Đúng',1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(78,80,'\"Như\" là từ thường dùng trong so sánh','Đúng',2,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(79,80,'\"Mắt sáng như sao\" là ẩn dụ','Sai',3,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(80,80,'Nhân hoá gán đặc điểm con người cho sự vật','Đúng',4,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(81,81,'Cánh đồng vàng ươm','Không',1,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(82,81,'Cánh đồng như tấm thảm vàng','Có biện pháp tu từ',2,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(83,81,'Trời xanh','Không',3,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(84,81,'Chú ong chăm chỉ như người thợ','Có biện pháp tu từ',4,'2026-10-07 20:27:12','2026-10-07 20:27:12'),
(85,91,'book','School things',1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(86,91,'teacher','People',2,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(87,91,'pen','School things',3,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(88,91,'student','People',4,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(89,92,'\"ruler\" means \"thước kẻ\"','Đúng',1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(90,92,'\"eraser\" means \"bút chì\"','Sai',2,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(91,92,'\"classroom\" means \"lớp học\"','Đúng',3,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(92,92,'\"notebook\" means \"cục tẩy\"','Sai',4,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(93,93,'desk','In the classroom',1,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(94,93,'pencil','In the bag',2,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(95,93,'board','In the classroom',3,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(96,93,'ruler','In the bag',4,'2026-10-07 20:27:13','2026-10-07 20:27:13'),
(97,103,'The picture is ___ the wall','on',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(98,103,'The fish is ___ the water','in',2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(99,103,'The cat is ___ the chair','under',3,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(100,103,'The apple is ___ the box','in',4,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(101,104,'\"on the wall\" is correct','Đúng',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(102,104,'\"in the table\" (trên mặt bàn) is correct','Sai',2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(103,104,'\"behind the house\" is correct','Đúng',3,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(104,104,'\"next to me\" means \"bên cạnh tôi\"','Đúng',4,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(105,105,'in the kitchen','Indoors',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(106,105,'in the garden','Outdoors',2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(107,105,'in the bedroom','Indoors',3,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(108,105,'on the playground','Outdoors',4,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(109,115,'suitcase','Things to bring',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(110,115,'hotel','Places',2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(111,115,'ticket','Things to bring',3,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(112,115,'beach','Places',4,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(113,116,'\"passport\" means \"hộ chiếu\"','Đúng',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(114,116,'\"train\" means \"máy bay\"','Sai',2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(115,116,'\"map\" means \"bản đồ\"','Đúng',3,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(116,116,'\"camera\" means \"máy ảnh\"','Đúng',4,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(117,117,'by plane','Máy bay',1,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(118,117,'by train','Tàu hoả',2,'2026-10-07 20:27:14','2026-10-07 20:27:14'),
(119,117,'by bus','Xe buýt',3,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(120,117,'by bike','Xe đạp',4,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(121,127,'Miệng','Trước dạ dày',1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(122,127,'Ruột non','Sau dạ dày',2,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(123,127,'Thực quản','Trước dạ dày',3,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(124,127,'Ruột già','Sau dạ dày',4,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(125,128,'Thức ăn được nghiền nát ở miệng','Đúng',1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(126,128,'Dạ dày nằm sau ruột non','Sai',2,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(127,128,'Ruột non hấp thụ chất dinh dưỡng','Đúng',3,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(128,128,'Gan tiết dịch giúp tiêu hoá','Đúng',4,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(129,129,'Dạ dày','Cơ quan tiêu hoá',1,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(130,129,'Tim','Không phải',2,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(131,129,'Ruột non','Cơ quan tiêu hoá',3,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(132,129,'Phổi','Không phải',4,'2026-10-07 20:27:15','2026-10-07 20:27:15'),
(133,139,'Không màu','Tính chất của nước',1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(134,139,'Có mùi thơm','Không phải',2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(135,139,'Không mùi','Tính chất của nước',3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(136,139,'Có vị ngọt','Không phải',4,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(137,140,'Khoá vòi khi đánh răng','Tiết kiệm nước',1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(138,140,'Xả nước liên tục khi rửa rau','Lãng phí nước',2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(139,140,'Dùng nước mưa để tưới cây','Tiết kiệm nước',3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(140,140,'Để vòi chảy khi không dùng','Lãng phí nước',4,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(141,141,'Nước sôi ở 100 độ C (điều kiện thường)','Đúng',1,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(142,141,'Nước đá nhẹ hơn nước lỏng nên nổi lên','Đúng',2,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(143,141,'Con người có thể sống thiếu nước cả tháng','Sai',3,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(144,141,'Nước bao phủ khoảng 70% bề mặt Trái Đất','Đúng',4,'2026-10-07 20:27:16','2026-10-07 20:27:16'),
(145,151,'Dây đồng','Vật dẫn điện',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(146,151,'Nhựa','Vật cách điện',2,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(147,151,'Sắt','Vật dẫn điện',3,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(148,151,'Gỗ khô','Vật cách điện',4,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(149,152,'Nguồn điện (pin)','Thuộc mạch điện',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(150,152,'Bóng đèn','Thuộc mạch điện',2,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(151,152,'Cái bàn','Không thuộc',3,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(152,152,'Công tắc','Thuộc mạch điện',4,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(153,153,'Dòng điện có tác dụng phát sáng','Đúng',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(154,153,'Nhựa dẫn điện rất tốt','Sai',2,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(155,153,'Mạch điện kín thì đèn sáng','Đúng',3,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(156,153,'Được phép chạm tay vào ổ điện','Sai',4,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(157,163,'Hai Bà Trưng','Chống quân Hán',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(158,163,'Bà Triệu','Chống quân Ngô',2,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(159,163,'Lý Bí','Chống quân Lương',3,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(160,163,'Ngô Quyền','Chống quân Nam Hán',4,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(161,164,'Hai Bà Trưng khởi nghĩa năm 40','Đúng',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(162,164,'Ngô Quyền thắng trận Bạch Đằng năm 938','Đúng',2,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(163,164,'Bà Triệu khởi nghĩa năm 248','Đúng',3,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(164,164,'Lý Bí xưng đế năm 679','Sai',4,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(165,165,'Hai Bà Trưng','Anh hùng',1,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(166,165,'Bạch Đằng','Địa danh',2,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(167,165,'Ngô Quyền','Anh hùng',3,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(168,165,'Hát Môn','Địa danh',4,'2026-10-07 20:27:17','2026-10-07 20:27:17'),
(169,175,'Đinh Bộ Lĩnh','Nhà Đinh',1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(170,175,'Lê Hoàn','Nhà Tiền Lê',2,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(171,175,'Đinh Tiên Hoàng','Nhà Đinh',3,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(172,175,'Lê Đại Hành','Nhà Tiền Lê',4,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(173,176,'Lê Hoàn đánh thắng quân Tống năm 981','Đúng',1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(174,176,'Nhà Tiền Lê đóng đô ở Thăng Long','Sai',2,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(175,176,'Lê Hoàn còn được gọi là Lê Đại Hành','Đúng',3,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(176,176,'Nhà Đinh tồn tại hơn 100 năm','Sai',4,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(177,177,'Lê Hoàn','Vua',1,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(178,177,'Phạm Cự Lượng','Tướng',2,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(179,177,'Đinh Tiên Hoàng','Vua',3,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(180,177,'Đinh Điền','Tướng',4,'2026-10-07 20:27:18','2026-10-07 20:27:18'),
(181,187,'Hịch tướng sĩ','Của Trần Hưng Đạo',1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(182,187,'Binh thư yếu lược','Của Trần Hưng Đạo',2,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(183,187,'Đại Việt sử ký toàn thư','Không phải',3,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(184,187,'Nam quốc sơn hà','Không phải',4,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(185,188,'Trần Hưng Đạo tên thật là Trần Quốc Tuấn','Đúng',1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(186,188,'Hịch tướng sĩ kêu gọi tướng sĩ đánh giặc','Đúng',2,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(187,188,'Trần Hưng Đạo là vua nhà Trần','Sai',3,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(188,188,'Trần Hưng Đạo mất năm 1300','Đúng',4,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(189,189,'Trận Chương Dương','Lần 2 (1285)',1,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(190,189,'Trận Bạch Đằng','Lần 3 (1288)',2,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(191,189,'Trận Tây Kết','Lần 2 (1285)',3,'2026-10-07 20:27:19','2026-10-07 20:27:19'),
(192,189,'Trận Vân Đồn','Lần 3 (1288)',4,'2026-10-07 20:27:19','2026-10-07 20:27:19');
/*!40000 ALTER TABLE `sort_items` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subjects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `icon` varchar(16) NOT NULL,
  `color` varchar(16) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `is_demo` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjects_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `subjects` VALUES
(1,'Toán','toan','🔢','#3b82f6','Học toán qua game: số học, đại số, hình học từ lớp 6 đến lớp 12.',1,1,1,'2026-10-07 20:27:03','2026-10-07 20:27:03'),
(2,'Tiếng Việt','tieng-viet','📖','#ef4444','Chơi mà học tiếng Việt: từ loại, chính tả, văn miêu tả và biện pháp tu từ.',2,1,1,'2026-10-07 20:27:03','2026-10-07 20:27:03'),
(3,'Tiếng Anh','tieng-anh','🔤','#8b5cf6','Luyện từ vựng và ngữ pháp tiếng Anh theo chủ đề, phù hợp lớp 6 trở lên.',3,1,1,'2026-10-07 20:27:03','2026-10-07 20:27:03'),
(4,'Khoa học','khoa-hoc','🔬','#10b981','Khám phá cơ thể người, chất quanh ta và năng lượng qua trò chơi.',4,1,1,'2026-10-07 20:27:03','2026-10-07 20:27:03'),
(5,'Lịch sử','lich-su','🏛️','#f59e0b','Dòng lịch sử dân tộc: từ thời dựng nước đến các cuộc kháng chiến.',5,1,1,'2026-10-07 20:27:03','2026-10-07 20:27:03');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `topics`
--

DROP TABLE IF EXISTS `topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `topics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(16) DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `grade_min` tinyint(3) unsigned NOT NULL DEFAULT 6,
  `grade_max` tinyint(3) unsigned NOT NULL DEFAULT 12,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `is_demo` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tp_subject` (`subject_id`),
  KEY `tp_slug` (`slug`),
  CONSTRAINT `topics_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `topics`
--

LOCK TABLES `topics` WRITE;
/*!40000 ALTER TABLE `topics` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `topics` VALUES
(1,1,'Số tự nhiên','toan-so-tu-nhien','Cộng, trừ, nhân, chia số tự nhiên.','1️⃣',1,6,7,1,1,'2026-10-07 20:27:03','2026-10-07 20:27:03'),
(2,1,'Phân số','toan-phan-so','Cộng, trừ, nhân, chia phân số.','½',2,6,7,1,1,'2026-10-07 20:27:03','2026-10-07 20:27:03'),
(3,1,'Biểu thức đại số','toan-bieu-thuc-dai-so','Đơn thức, đa thức và giá trị biểu thức.','🧮',3,7,8,1,1,'2026-10-07 20:27:03','2026-10-07 20:27:03'),
(4,1,'Hình học phẳng','toan-hinh-hoc-phang','Góc, đường thẳng và tam giác.','📐',4,7,8,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(5,2,'Từ và câu','tv-tu-va-cau','Từ loại và cấu tạo câu.','🔤',1,6,7,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(6,2,'Chính tả','tv-chinh-ta','Dấu hỏi, dấu ngã và các âm đầu dễ nhầm.','✏️',2,6,7,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(7,2,'Luyện văn miêu tả','tv-van-mieu-ta','Bài văn miêu tả và biện pháp tu từ.','📝',3,7,8,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(8,3,'Từ vựng lớp 6','en-tu-vung-lop-6','Vocabulary: family and school.','👨‍👩‍👧',1,6,7,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(9,3,'Ngữ pháp cơ bản','en-ngu-phap-co-ban','Grammar: present simple and prepositions.','⏰',2,6,7,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(10,3,'Từ vựng lớp 7','en-tu-vung-lop-7','Vocabulary: health and travel.','🌍',3,7,8,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(11,4,'Cơ thể người','kh-co-the-nguoi','Hệ xương và hệ tiêu hoá.','🧍',1,6,7,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(12,4,'Chất quanh ta','kh-chat-quanh-ta','Trạng thái của chất và nước.','🧪',2,7,8,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(13,4,'Năng lượng','kh-nang-luong','Nguồn năng lượng và điện.','⚡',3,8,9,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(14,5,'Việt Nam thời dựng nước','ls-dung-nuoc','Nước Văn Lang và các anh hùng dân tộc.','🏞️',1,6,7,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(15,5,'Nhà Đinh – nhà Tiền Lê','ls-dinh-tien-le','Đinh Bộ Lĩnh và Lê Hoàn.','👑',2,7,8,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04'),
(16,5,'Kháng chiến chống Nguyên – Mông','ls-chong-nguyen-mong','Các trận đánh và danh tướng Trần Hưng Đạo.','⚔️',3,8,9,1,1,'2026-10-07 20:27:04','2026-10-07 20:27:04');
/*!40000 ALTER TABLE `topics` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','parent','learner') NOT NULL DEFAULT 'learner',
  `avatar_path` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `users` VALUES
(1,'Quản trị viên','admin@vuihoc.local','$2y$12$dyhNb9Qfis4yeg6w3c8VjOkA9RdWUEdf4/tTjDPVBHQ3M9zZ668Ea','admin',NULL,NULL,NULL,'2026-10-07 20:27:20','2026-10-07 20:27:20'),
(2,'Cô Giáo Demo','giaovien@vuihoc.local','$2y$12$AD1saBmUcw9Dq7r50ol2v.5IOMQArff1GIIjTFZTU5z0w5JiwdnaG','teacher',NULL,NULL,NULL,'2026-10-07 20:27:21','2026-10-07 20:27:21'),
(3,'Phụ huynh Demo','phuhuynh@vuihoc.local','$2y$12$e6JFkcLiECL4lOce/g3F/eCCOZKx9wirsvqJmXilGOwS5PfoLVSKi','parent',NULL,NULL,NULL,'2026-10-07 20:27:21','2026-10-07 20:27:21'),
(4,'Học sinh Demo','hocsinh@vuihoc.local','$2y$12$DpiFI0SFW8UlxHJKQumvIOI1T01J.IB4JYMwUw2Zcez6AfveC2e2m','learner',NULL,NULL,NULL,'2026-10-07 20:27:22','2026-10-07 20:27:22');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `xp_events`
--

DROP TABLE IF EXISTS `xp_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `xp_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) unsigned NOT NULL,
  `source` varchar(32) NOT NULL,
  `amount` int(11) NOT NULL,
  `meta_json` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `xp_profile` (`profile_id`),
  CONSTRAINT `xp_events_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `learner_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `xp_events`
--

LOCK TABLES `xp_events` WRITE;
/*!40000 ALTER TABLE `xp_events` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `xp_events` ENABLE KEYS */;
UNLOCK TABLES;
commit;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-10-07 13:27:27
