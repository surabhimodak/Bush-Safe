
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
DROP TABLE IF EXISTS `wp_revisr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_revisr` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `message` text,
  `event` varchar(42) NOT NULL,
  `user` varchar(60) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `wp_revisr` WRITE;
/*!40000 ALTER TABLE `wp_revisr` DISABLE KEYS */;
INSERT INTO `wp_revisr` VALUES (1,'2021-03-31 06:31:02','Successfully created a new repository.','init','admin'),(2,'2021-03-31 06:32:17','Error pushing changes to the remote repository.','error','admin'),(3,'2021-03-31 06:33:24','Committed <a href=\"http://bush-safe.ga/wp-admin/admin.php?page=revisr_view_commit&commit=ae22a58&success=true\">#ae22a58</a> to the local repository.','commit','admin'),(4,'2021-03-31 06:35:38','Error pushing changes to the remote repository.','error','admin'),(5,'2021-03-31 06:36:45','Error pushing changes to the remote repository.','error','admin'),(6,'2021-03-31 06:38:33','Error pushing changes to the remote repository.','error','admin'),(7,'2021-03-31 06:39:06','Error pushing changes to the remote repository.','error','admin'),(8,'2021-03-31 06:43:44','Error pushing changes to the remote repository.','error','admin'),(9,'2021-03-31 06:46:42','Error pushing changes to the remote repository.','error','admin'),(10,'2021-03-31 06:47:27','Error pushing changes to the remote repository.','error','admin'),(11,'2021-03-31 06:50:08','There was an error committing the changes to the local repository.','error','admin'),(12,'2021-03-31 06:50:08','There was an error committing the changes to the local repository.','error','admin'),(13,'2021-03-31 06:50:09','There was an error committing the changes to the local repository.','error','admin'),(14,'2021-03-31 06:50:39','Committed <a href=\"http://bush-safe.ga/wp-admin/admin.php?page=revisr_view_commit&commit=7fd2c4e&success=true\">#7fd2c4e</a> to the local repository.','commit','admin'),(15,'2021-03-31 06:50:52','Error pushing changes to the remote repository.','error','admin'),(16,'2021-03-31 06:55:39','Error pushing changes to the remote repository.','error','admin'),(17,'2021-03-31 07:00:26','Error pushing changes to the remote repository.','error','admin'),(18,'2021-03-31 07:01:20','Error pushing changes to the remote repository.','error','admin'),(19,'2021-03-31 07:07:26','Successfully pushed 3 commits to Bush-Safe/master.','push','admin'),(20,'2021-03-31 07:08:11','Committed <a href=\"http://bush-safe.ga/wp-admin/admin.php?page=revisr_view_commit&commit=15552d0&success=true\">#15552d0</a> to the local repository.','commit','admin'),(21,'2021-03-31 07:08:29','Successfully pushed 1 commit to Bush-Safe/master.','push','admin');
/*!40000 ALTER TABLE `wp_revisr` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

