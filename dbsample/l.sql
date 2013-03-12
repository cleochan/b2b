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
-- Table structure for table `api_targets`
--

DROP TABLE IF EXISTS `api_targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_targets` (
  `api_targets_id` int(5) NOT NULL AUTO_INCREMENT,
  `target_url` varchar(255) DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  `public_key_1` varchar(100) DEFAULT NULL,
  `public_key_2` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`api_targets_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_targets`
--

LOCK TABLES `api_targets` WRITE;
/*!40000 ALTER TABLE `api_targets` DISABLE KEYS */;
INSERT INTO `api_targets` VALUES (1,'http://demo.local.b2b/api/port-a',1,'l23khu23KJH34kjl','kjjkj4h5JKG3jg3Gk');
/*!40000 ALTER TABLE `api_targets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_types`
--

DROP TABLE IF EXISTS `api_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_types` (
  `api_types_id` int(5) NOT NULL AUTO_INCREMENT,
  `api_types_name` varchar(100) NOT NULL,
  `api_types_version` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`api_types_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_types`
--

LOCK TABLES `api_types` WRITE;
/*!40000 ALTER TABLE `api_types` DISABLE KEYS */;
INSERT INTO `api_types` VALUES (1,'GetProductList','1.0'),(2,'PlaceOrder','1.0');
/*!40000 ALTER TABLE `api_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tmpa`
--

DROP TABLE IF EXISTS `tmpa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmpa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contents` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tmpa`
--

LOCK TABLES `tmpa` WRITE;
/*!40000 ALTER TABLE `tmpa` DISABLE KEYS */;
INSERT INTO `tmpa` VALUES (1,'{\"controller\":\"api\",\"action\":\"port-a\",\"module\":\"default\"}'),(2,'{\"controller\":\"api\",\"action\":\"port-a\",\"module\":\"default\"}'),(3,''),(4,'{\"REDIRECT_STATUS\":\"200\",\"HTTP_HOST\":\"demo.local.b2b\",\"HTTP_ACCEPT\":\"*\\/*\",\"CONTENT_TYPE\":\"text\\/xml\",\"CONTENT_LENGTH\":\"212\",\"HTTP_CONNECTION\":\"close\",\"HTTP_EXPECT\":\"100-continue\",\"PATH\":\"\\/usr\\/local\\/bin:\\/usr\\/bin:\\/bin\",\"SERVER_SIGNATURE\":\"<address>Apache\\/2.2.22 (Ubuntu) Server at demo.local.b2b Port 80<\\/address>\\n\",\"SERVER_SOFTWARE\":\"Apache\\/2.2.22 (Ubuntu)\",\"SERVER_NAME\":\"demo.local.b2b\",\"SERVER_ADDR\":\"127.0.0.1\",\"SERVER_PORT\":\"80\",\"REMOTE_ADDR\":\"127.0.0.1\",\"DOCUMENT_ROOT\":\"\\/var\\/www\\/git-b2b\\/public\",\"SERVER_ADMIN\":\"webmaster@localhost\",\"SCRIPT_FILENAME\":\"\\/var\\/www\\/git-b2b\\/public\\/index.php\",\"REMOTE_PORT\":\"57877\",\"REDIRECT_URL\":\"\\/api\\/port-a\",\"GATEWAY_INTERFACE\":\"CGI\\/1.1\",\"SERVER_PROTOCOL\":\"HTTP\\/1.1\",\"REQUEST_METHOD\":\"POST\",\"QUERY_STRING\":\"\",\"REQUEST_URI\":\"\\/api\\/port-a\",\"SCRIPT_NAME\":\"\\/index.php\",\"PHP_SELF\":\"\\/index.php\",\"REQUEST_TIME_FLOAT\":1363068679.365,\"REQUEST_TIME\":1363068679}'),(5,'<?xml version=\"1.0\"?>\n<root><common><version>1.0</version><action>GetProductList</action><merchantid>1</merchantid><publickey>l23khu23KJH34kjl</publickey></common><params><0>a</0><1>b</1><2>c</2></params></root>\n');
/*!40000 ALTER TABLE `tmpa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs_api`
--

DROP TABLE IF EXISTS `logs_api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs_api` (
  `logs_api_id` int(11) NOT NULL AUTO_INCREMENT,
  `contents` mediumtext,
  `api_target` int(10) DEFAULT NULL,
  `api_type` int(5) DEFAULT NULL,
  `api_status` int(1) NOT NULL DEFAULT '0' COMMENT '0=Pending 1=Finished',
  `api_response` mediumtext,
  `api_step` int(1) DEFAULT NULL COMMENT '1=Send 2=Receive',
  `issue_time` datetime DEFAULT NULL,
  PRIMARY KEY (`logs_api_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs_api`
--

LOCK TABLES `logs_api` WRITE;
/*!40000 ALTER TABLE `logs_api` DISABLE KEYS */;
INSERT INTO `logs_api` VALUES (1,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:21:06'),(2,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:23:53'),(3,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:37:56'),(4,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:41:17'),(5,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:42:42'),(6,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:43:28'),(7,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:43:49'),(8,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:44:06'),(9,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:46:30'),(10,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:46:53'),(11,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:49:57'),(12,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:51:13'),(13,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,1,'',1,'2013-03-12 11:51:57'),(14,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:52:44'),(15,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:54:22'),(16,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 11:55:11'),(17,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,1,'',1,'2013-03-12 11:55:37'),(18,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,1,'',1,'2013-03-12 12:00:20'),(19,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,0,NULL,1,'2013-03-12 13:58:07'),(20,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,1,'',1,'2013-03-12 14:10:17'),(21,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,1,'',1,'2013-03-12 14:11:19'),(22,'{\"common\":{\"version\":\"1.0\",\"action\":\"GetProductList\",\"merchantid\":\"1\",\"publickey\":\"l23khu23KJH34kjl\"},\"params\":[\"a\",\"b\",\"c\"]}',1,1,1,'',1,'2013-03-12 14:18:18');
/*!40000 ALTER TABLE `logs_api` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-03-12 14:46:00
