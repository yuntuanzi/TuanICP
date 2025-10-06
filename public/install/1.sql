-- MySQL dump 10.13  Distrib 5.7.43, for Linux (x86_64)
--
-- Host: localhost    Database: icppro
-- ------------------------------------------------------
-- Server version   5.7.43-log

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
-- Table structure for table `admin_accounts`
--

DROP TABLE IF EXISTS `admin_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(50) NOT NULL COMMENT '登录账号',
  `email` varchar(100) NOT NULL COMMENT '绑定邮箱',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `nickname` varchar(50) NOT NULL COMMENT '显示昵称',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='系统管理员账户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_accounts`
--

LOCK TABLES `admin_accounts` WRITE;
/*!40000 ALTER TABLE `admin_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_remember_tokens`
--

DROP TABLE IF EXISTS `admin_remember_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_id` (`admin_id`),
  UNIQUE KEY `token` (`token`),
  CONSTRAINT `admin_remember_tokens_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_remember_tokens`
--

LOCK TABLES `admin_remember_tokens` WRITE;
/*!40000 ALTER TABLE `admin_remember_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_remember_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_contents`
--

DROP TABLE IF EXISTS `custom_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_contents` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自定义内容ID',
  `global_css` text COMMENT '自定义全局CSS',
  `global_js` text COMMENT '自定义全局JS',
  `header_html` text COMMENT '自定义头部HTML',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='网站自定义内容';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_contents`
--

LOCK TABLES `custom_contents` WRITE;
/*!40000 ALTER TABLE `custom_contents` DISABLE KEYS */;
INSERT INTO `custom_contents` VALUES (1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `custom_contents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `icp_changes`
--

DROP TABLE IF EXISTS `icp_changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icp_changes` (
  `change_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '变更记录ID',
  `icp_number` varchar(20) NOT NULL COMMENT '备案号',
  `site_title` varchar(100) NOT NULL COMMENT '网站标题',
  `site_description` text COMMENT '网站描述',
  `site_domain` varchar(255) NOT NULL COMMENT '网站域名',
  `site_avatar` varchar(255) DEFAULT NULL COMMENT '网站头像URL',
  `owner` varchar(50) NOT NULL COMMENT '所有者姓名/名称',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  `email` varchar(100) NOT NULL COMMENT '联系邮箱',
  `qq` varchar(20) DEFAULT NULL COMMENT '联系QQ',
  `remark` text COMMENT '备注信息',
  `submit_ip` varchar(50) NOT NULL COMMENT '变更提交IP地址',
  PRIMARY KEY (`change_id`),
  KEY `icp_number` (`icp_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ICP备案变更记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `icp_changes`
--

LOCK TABLES `icp_changes` WRITE;
/*!40000 ALTER TABLE `icp_changes` DISABLE KEYS */;
/*!40000 ALTER TABLE `icp_changes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `icp_records`
--

DROP TABLE IF EXISTS `icp_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icp_records` (
  `uid` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯一标识ID',
  `icp_number` varchar(20) NOT NULL COMMENT '备案号',
  `site_title` varchar(100) NOT NULL COMMENT '网站标题',
  `site_description` text COMMENT '网站描述',
  `site_domain` varchar(255) NOT NULL COMMENT '网站域名',
  `site_avatar` varchar(255) DEFAULT NULL COMMENT '网站头像URL',
  `owner` varchar(50) NOT NULL COMMENT '所有者姓名/名称',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  `email` varchar(100) NOT NULL COMMENT '联系邮箱',
  `qq` varchar(20) DEFAULT NULL COMMENT '联系QQ',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending' COMMENT '状态: pending-待审核, approved-通过, rejected-驳回',
  `remark` text COMMENT '备注信息',
  `inspection_status` enum('normal','abnormal') NOT NULL DEFAULT 'normal' COMMENT '巡查状态: normal-正常, abnormal-异常',
  `ping_delay` int(11) DEFAULT NULL COMMENT 'Ping延迟(毫秒)',
  `submit_ip` varchar(50) NOT NULL COMMENT '备案提交IP地址',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `icp_number` (`icp_number`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COMMENT='ICP备案信息记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `icp_records`
--

LOCK TABLES `icp_records` WRITE;
/*!40000 ALTER TABLE `icp_records` DISABLE KEYS */;
INSERT INTO `icp_records` VALUES (1,'20258888','摘星团团','哇？是谁家的小可爱？','博客.星.fun','https://file.xn--kiv.fun/png/logo.png','云团子','2025-05-04 11:20:51','',NULL,'approved',NULL,'normal',NULL,'');
/*!40000 ALTER TABLE `icp_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `icp_reports`
--

DROP TABLE IF EXISTS `icp_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `icp_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icp_number` varchar(20) NOT NULL COMMENT '备案号',
  `report_reason` text NOT NULL COMMENT '举报原因',
  `report_time` datetime NOT NULL COMMENT '举报时间',
  `reporter_ip` varchar(50) NOT NULL COMMENT '举报者IP',
  `status` enum('pending','processed') NOT NULL DEFAULT 'pending' COMMENT '处理状态',
  `processor_id` int(11) DEFAULT NULL COMMENT '处理人ID',
  `process_time` datetime DEFAULT NULL COMMENT '处理时间',
  `process_result` text COMMENT '处理结果',
  PRIMARY KEY (`id`),
  KEY `icp_number` (`icp_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ICP备案举报记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `icp_reports`
--

LOCK TABLES `icp_reports` WRITE;
/*!40000 ALTER TABLE `icp_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `icp_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '设置ID',
  `smtp_user` varchar(100) NOT NULL COMMENT '发件邮箱账号',
  `smtp_host` varchar(100) NOT NULL COMMENT '邮箱服务器地址',
  `smtp_port` int(11) NOT NULL COMMENT '邮箱端口',
  `smtp_pass` varchar(100) NOT NULL COMMENT '邮箱服务密码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='系统邮件服务设置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'','',465,'');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `web_settings`
--

DROP TABLE IF EXISTS `web_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `web_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '设置ID',
  `site_name` varchar(50) NOT NULL COMMENT '站点名称',
  `main_title` varchar(100) NOT NULL COMMENT '大标题',
  `sub_title` varchar(200) DEFAULT NULL COMMENT '副标题',
  `logo_url` varchar(255) DEFAULT NULL COMMENT 'LOGO URL',
  `short_name` varchar(5) DEFAULT NULL COMMENT '单字简称',
  `admin_email` varchar(100) NOT NULL COMMENT '站长email',
  `admin_qq` varchar(20) DEFAULT NULL COMMENT '站长QQ',
  `site_domain` varchar(255) DEFAULT NULL COMMENT '网站域名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='网站前端设置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `web_settings`
--

LOCK TABLES `web_settings` WRITE;
/*!40000 ALTER TABLE `web_settings` DISABLE KEYS */;
INSERT INTO `web_settings` VALUES (1,'TuanICP','云团子ICP备案中心','安全 • 可爱 • 高效的二次元虚拟备案','favicon.ico','团','ccssna@qq.com',NULL,'icp.星.fun');
/*!40000 ALTER TABLE `web_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'icppro'
--

--
-- Dumping routines for database 'icppro'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-11 17:27:18