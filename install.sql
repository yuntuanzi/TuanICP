-- MySQL dump 10.13  Distrib 5.6.50, for Linux (x86_64)
--
-- Host: localhost    Database: icp
-- ------------------------------------------------------
-- Server version	5.6.50-log

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
-- Table structure for table `icp_records`
--

DROP TABLE IF EXISTS `icp_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icp_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `website_name` varchar(255) NOT NULL,
  `website_url` varchar(255) NOT NULL,
  `website_info` text,
  `icp_number` varchar(50) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `update_time` datetime NOT NULL,
  `STATUS` enum('待审核','审核通过','备案驳回','被删除') NOT NULL DEFAULT '待审核',
  `email` varchar(255) DEFAULT NULL,
  `qq` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `icp_records`
--

LOCK TABLES `icp_records` WRITE;
/*!40000 ALTER TABLE `icp_records` DISABLE KEYS */;
INSERT INTO `icp_records` VALUES (1,'云团子的博客','https://www.yuncheng.fun/','欸？是谁家的小可爱？','20243017','云团子','2024-10-02 22:59:19','审核通过','yun@yuncheng.fun','937319686');
/*!40000 ALTER TABLE `icp_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `website_info`
--

DROP TABLE IF EXISTS `website_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `website_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) NOT NULL,
  `site_url` varchar(255) NOT NULL,
  `site_avatar` varchar(255) NOT NULL,
  `site_abbr` varchar(10) NOT NULL,
  `site_keywords` text NOT NULL,
  `site_description` text NOT NULL,
  `admin_nickname` varchar(255) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `admin_qq` varchar(20) NOT NULL,
  `footer_code` text,
  `audit_duration` int(11) NOT NULL,
  `feedback_link` varchar(255) DEFAULT NULL,
  `background_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_url` (`site_url`),
  UNIQUE KEY `admin_email` (`admin_email`),
  UNIQUE KEY `admin_qq` (`admin_qq`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `website_info`
--

LOCK TABLES `website_info` WRITE;
/*!40000 ALTER TABLE `website_info` DISABLE KEYS */;
INSERT INTO `website_info` VALUES (1,'云团子','https://icp.yuncheng.fun/','https://www.yuncheng.fun/static/webAvatar/11727945933180571.png','团','团备, 团ICP备, 云团子ICP备案中心 ,云团子 ,杜匀程','哇，是谁家的小可爱？','云团子','yun@yuncheng.fun','937319686','<a href=\"index.php\">主页</a> \r\n<a href=\"about.php\">关于</a>\r\n<a href=\"yq.php\">加入</a>\r\n<a href=\"#\">变更</a>\r\n<a href=\"gs.php\">公示</a>\r\n<br>\r\n<a href=\"https://beian.miit.gov.cn/\" target=\"_blank\">陇ICP备2024011452号-2</a><a href=\"https://icp.yuncheng.fun/id.php?keyword=20243999\" target=\"_blank\">团ICP备20243999号</a><a href=\"https://icp.gov.moe/?keyword=20243999\" target=\"_blank\">萌ICP备20243999号</a>',3,'https://qm.qq.com/q/kClRRuBmOQ','https://cdn.koxiuqiu.cn/ccss/ecyrw/ecy%20(68).png');
/*!40000 ALTER TABLE `website_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'icp'
--

--
-- Dumping routines for database 'icp'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-10-26 19:14:10
