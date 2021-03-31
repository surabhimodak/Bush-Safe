
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
DROP TABLE IF EXISTS `wp_gmp_icons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_gmp_icons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `description` text,
  `path` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `wp_gmp_icons` WRITE;
/*!40000 ALTER TABLE `wp_gmp_icons` DISABLE KEYS */;
INSERT INTO `wp_gmp_icons` VALUES (1,'marker','blue,white,star,pin','bblue.png'),(2,'marker','green,white,star,pin','bgreen.png'),(3,'marker','purple,white,star,pin','purple.png'),(4,'marker','blue,white,star,pin','bred.png'),(5,'marker','blue,pin','blue.png'),(6,'marker','gray,pin','gray.png'),(7,'marker','green,pin','green.png'),(8,'marker','pin,yellow','orange.png'),(9,'marker','pin,red','red.png'),(10,'flag','gray','flag_black.png'),(11,'flag','blue','flag_blue.png'),(12,'flag','green','flag_green.png'),(13,'flag','orange','flag_orange.png'),(14,'flag','purple','flag_purple.png'),(15,'flag','red','flag_red.png'),(16,'marker','pin,cycle,blue','blue_circle.png'),(17,'marker','white,blue,pin','blue_orifice.png'),(18,'marker','blue,pin','blue_std.png'),(19,'pin','green,marker,cycle','green_circle.png'),(20,'pin','green,cycle','green_orifice.png'),(21,'pin','green','green_std.png'),(22,'pin','orange,cycle','orange_circle.png'),(23,'pin','orange,cycle','orange_orifice.png'),(24,'pin','orange','orange_std.png'),(25,'pin','purple,cycle','purple_circle.png'),(26,'pin','purple,cycle','purple_orifice.png'),(27,'pin','purple','purple_std.png'),(28,'pin','red,cycle','red_circle.png'),(29,'pin','red,cycle','red_orifice.png'),(30,'pin','red','red_std.png'),(31,'star','black,dark,pin','star_pin_black.png'),(32,'star','blue,pin','star_pin_blue.png'),(33,'star','green,pin','star_pin_green.png'),(34,'star','orange,pin','star_pin_orange.png'),(35,'star','purple','star_pin_purple.png'),(36,'star','red,pin','star_pin_red.png'),(37,'pin','gray,white,cycle','white_circlepng.png'),(38,'pin','gray,white,cycle','white_orifice.png'),(39,'pin','white,gray','white_std.png'),(40,'pin','yellow,cycle','yellow_circlepng.png'),(41,'pin','yellow,cycle','yellow_orifice.png'),(42,'pin','yellow','yellow_std.png'),(43,'marker','red','marker.png'),(44,'marker','blue','marker_blue.png'),(45,'marker','red,letter','markerA.png'),(46,'marker','orange','marker_orange.png'),(47,'marker','green','marker_green.png');
/*!40000 ALTER TABLE `wp_gmp_icons` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

