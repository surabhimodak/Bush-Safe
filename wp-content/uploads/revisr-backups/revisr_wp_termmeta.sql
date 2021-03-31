
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
DROP TABLE IF EXISTS `wp_termmeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_termmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `term_id` (`term_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `wp_termmeta` WRITE;
/*!40000 ALTER TABLE `wp_termmeta` DISABLE KEYS */;
INSERT INTO `wp_termmeta` VALUES (1,23,'_fusion','a:0:{}'),(2,641,'_fusion','a:0:{}'),(3,643,'_fusion','a:0:{}'),(4,645,'_fusion','a:0:{}'),(5,649,'_fusion','a:0:{}'),(6,2,'_fusion','a:1:{s:12:\"main_padding\";a:2:{s:3:\"top\";s:3:\"5vw\";s:6:\"bottom\";s:3:\"5vw\";}}'),(7,5,'_fusion','a:0:{}'),(8,6,'_fusion','a:0:{}'),(9,7,'_fusion','a:0:{}'),(10,712,'_fusion','a:0:{}'),(11,1925,'_fusion','a:0:{}'),(12,1336,'_fusion','a:0:{}'),(13,1,'_fusion','a:0:{}'),(14,1472,'_fusion','a:0:{}'),(15,1921,'_fusion','a:0:{}'),(16,1565,'_fusion','a:0:{}'),(17,694,'_fusion','a:0:{}'),(18,1406,'_fusion','a:0:{}'),(19,1000,'_fusion','a:0:{}'),(20,1142,'_fusion','a:0:{}'),(21,1530,'_fusion','a:0:{}'),(22,1325,'_fusion','a:0:{}'),(23,1981,'_fusion','a:0:{}'),(24,1326,'_fusion','a:0:{}'),(25,1416,'_fusion','a:0:{}'),(26,1053,'_fusion','a:0:{}'),(27,717,'_fusion','a:0:{}'),(28,1992,'_fusion','a:0:{}');
/*!40000 ALTER TABLE `wp_termmeta` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

