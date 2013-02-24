-- MySQL dump 10.13  Distrib 5.5.29, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: b2b
-- ------------------------------------------------------
-- Server version	5.5.29-0ubuntu0.12.10.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `financial_action_type`
--

DROP TABLE IF EXISTS `financial_action_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `financial_action_type` (
  `type_key` int(2) NOT NULL,
  `type_value` varchar(50) NOT NULL,
  PRIMARY KEY (`type_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `financial_action_type`
--

LOCK TABLES `financial_action_type` WRITE;
/*!40000 ALTER TABLE `financial_action_type` DISABLE KEYS */;
INSERT INTO `financial_action_type` VALUES (1,'Place Order'),(2,'Recharge'),(3,'Adjustment'),(4,'Cancellation');
/*!40000 ALTER TABLE `financial_action_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `helpdesk`
--

DROP TABLE IF EXISTS `helpdesk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `helpdesk` (
  `helpdesk_id` int(5) NOT NULL AUTO_INCREMENT,
  `category` int(2) DEFAULT NULL,
  `h_subject` varchar(255) DEFAULT NULL,
  `h_contents` mediumtext,
  `h_status` int(1) DEFAULT NULL,
  `issue_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`helpdesk_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `helpdesk`
--

LOCK TABLES `helpdesk` WRITE;
/*!40000 ALTER TABLE `helpdesk` DISABLE KEYS */;
INSERT INTO `helpdesk` VALUES (1,2,'How to check my historical orders?','<p>\r\n	Contents.\r\n</p>\r\n<p>\r\n	Contents.\r\n</p>\r\n<p>\r\n	Contents.\r\n</p>\r\n<p>\r\n	Contents.\r\n</p>',1,'2013-02-20 11:02:26'),(2,1,'B2B System Documentation','<p>\r\n	B2B System Documentation details.\r\n</p>\r\n<p>\r\n	B2B System Documentation details.\r\n</p>\r\n<p>\r\n	B2B System Documentation details.\r\n</p>\r\n<p>\r\n	B2B System Documentation details.\r\n</p>',1,'2013-02-20 11:11:57'),(3,3,'Contact Us','<p>\r\n	<strong><u>Business Support:</u></strong>\r\n</p>\r\n<p>\r\n	Tel: +61 12345678\r\n</p>\r\n<p>\r\n	Email: b2b_business_support@crazysales.com.au\r\n</p>\r\n<p>\r\n	<br />\r\n</p>\r\n<p>\r\n	<strong><u>Technical Support:</u></strong>\r\n</p>\r\n<p>\r\n	Tel: +61 12345678\r\n</p>\r\n<p>\r\n	Email: b2b_technical_support@crazysales.com.au\r\n</p>\r\n<p>\r\n	<br />\r\n</p>\r\n<p>\r\n	<img src=\"/scripts/kindeditor/attached/image/20130221/20130221102611_92869.gif\" alt=\"\" />\r\n</p>',1,'2013-02-20 11:39:04'),(4,1,'API Integration Documentation','<p>\r\n	API Integration Documentation details.\r\n</p>\r\n<p>\r\n	API Integration Documentation details.\r\n</p>\r\n<p>\r\n	API Integration Documentation details.\r\n</p>\r\n<p>\r\n	API Integration Documentation details.<br />\r\n</p>',1,'2013-02-21 02:48:03'),(5,2,'How to recharge my account?','<p>\r\n	Contents.\r\n</p>\r\n<p>\r\n	Contents.\r\n</p>\r\n<p>\r\n	Contents.\r\n</p>\r\n<p>\r\n	Contents.\r\n</p>',1,'2013-02-21 02:49:11'),(6,2,'How to place order?','<p>\r\n	Contents.\r\n</p>\r\n<p>\r\n	Contents.\r\n</p>\r\n<p>\r\n	Contents.\r\n</p>\r\n<p>\r\n	Contents.\r\n</p>',1,'2013-02-21 02:49:27');
/*!40000 ALTER TABLE `helpdesk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `helpdesk_category`
--

DROP TABLE IF EXISTS `helpdesk_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `helpdesk_category` (
  `h_key` int(3) NOT NULL AUTO_INCREMENT,
  `h_value` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`h_key`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `helpdesk_category`
--

LOCK TABLES `helpdesk_category` WRITE;
/*!40000 ALTER TABLE `helpdesk_category` DISABLE KEYS */;
INSERT INTO `helpdesk_category` VALUES (1,'Documentation'),(2,'FAQ'),(3,'Contact Us');
/*!40000 ALTER TABLE `helpdesk_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs_financial`
--

DROP TABLE IF EXISTS `logs_financial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs_financial` (
  `logs_financial_id` int(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action_type` int(2) DEFAULT NULL,
  `action_affect` int(1) DEFAULT NULL,
  `trans_id` varchar(100) DEFAULT NULL,
  `action_value` float(11,2) DEFAULT NULL,
  `instant_balance` float(11,2) DEFAULT NULL,
  `issue_time` timestamp NULL DEFAULT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`logs_financial_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs_financial`
--

LOCK TABLES `logs_financial` WRITE;
/*!40000 ALTER TABLE `logs_financial` DISABLE KEYS */;
INSERT INTO `logs_financial` VALUES (1,3,1,1,'111111',10.00,200.00,'2013-02-20 04:11:09',NULL,NULL),(2,3,2,2,'323423',50.00,320.00,'2013-02-20 04:11:09',NULL,NULL),(3,3,3,1,NULL,100.00,100.00,'2013-02-22 10:02:19',1,'127.0.0.1'),(4,3,3,1,NULL,52.00,152.00,'2013-02-22 10:02:29',1,'127.0.0.1'),(5,3,3,2,NULL,30.00,122.00,'2013-02-22 10:02:42',1,'127.0.0.1'),(6,3,2,1,'CBA201302140144254907',219.43,341.43,'2013-02-23 09:54:12',1,'127.0.0.1'),(7,3,2,1,'CBA201302140144272416',61.84,403.27,'2013-02-23 09:54:12',1,'127.0.0.1'),(8,3,2,1,'CBA201302140644487794',129.53,532.80,'2013-02-23 09:54:12',1,'127.0.0.1'),(9,3,3,1,NULL,55.00,55.00,'2013-02-23 10:02:21',1,'127.0.0.1');
/*!40000 ALTER TABLE `logs_financial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs_orders`
--

DROP TABLE IF EXISTS `logs_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs_orders` (
  `logs_orders_id` int(20) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) DEFAULT NULL,
  `order_status` int(2) DEFAULT NULL,
  `item_title` varchar(255) DEFAULT NULL,
  `order_amount` float(11,2) DEFAULT NULL,
  `issue_time` timestamp NULL DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`logs_orders_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs_orders`
--

LOCK TABLES `logs_orders` WRITE;
/*!40000 ALTER TABLE `logs_orders` DISABLE KEYS */;
INSERT INTO `logs_orders` VALUES (1,'350600',1,'Maxkon 2.0L Automatic Ice Cube Maker - White',99.95,'2013-02-19 10:47:31',3,NULL,NULL),(2,'350601',2,'Keter Indoor/Outdoor Storage Box - Lockable - 305L - Beige',11.00,'2013-02-19 16:45:20',3,NULL,NULL),(3,'350602',2,'Otek Sportz DVS-5G5 Full HD Waterproof Camera',23.00,'2013-02-19 16:45:20',3,NULL,NULL);
/*!40000 ALTER TABLE `logs_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `params`
--

DROP TABLE IF EXISTS `params`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `params` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ckey` mediumtext,
  `cval` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `params`
--

LOCK TABLES `params` WRITE;
/*!40000 ALTER TABLE `params` DISABLE KEYS */;
INSERT INTO `params` VALUES (1,'system_title','Merchants System of CrazySales'),(2,'system_version','0.1');
/*!40000 ALTER TABLE `params` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(5) NOT NULL AUTO_INCREMENT,
  `email` varchar(200) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `user_type` int(2) DEFAULT NULL,
  `user_status` int(2) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@gmail.com','e10adc3949ba59abbe56e057f20f883e',1,1),(3,'merchant@gmail.com','e10adc3949ba59abbe56e057f20f883e',2,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_extension`
--

DROP TABLE IF EXISTS `users_extension`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_extension` (
  `user_id` int(11) NOT NULL,
  `company` varchar(200) DEFAULT NULL,
  `contact_name` varchar(200) DEFAULT NULL,
  `contact_phone` varchar(200) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `balance` float(11,2) NOT NULL DEFAULT '0.00',
  `credit` float(11,2) NOT NULL DEFAULT '0.00',
  `bpay_ref` int(10) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_extension`
--

LOCK TABLES `users_extension` WRITE;
/*!40000 ALTER TABLE `users_extension` DISABLE KEYS */;
INSERT INTO `users_extension` VALUES (1,'AusPac','Mark Chan','+86 18918262710','2013-02-18',0.00,0.00,NULL),(3,'A Company','ABC','+61 12345678','2013-02-06',532.80,10.00,10035);
/*!40000 ALTER TABLE `users_extension` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-02-24 14:18:12
