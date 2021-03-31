
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
DROP TABLE IF EXISTS `wp_gmp_maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_gmp_maps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(125) NOT NULL,
  `description` text,
  `params` text,
  `html_options` text NOT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `wp_gmp_maps` WRITE;
/*!40000 ALTER TABLE `wp_gmp_maps` DISABLE KEYS */;
INSERT INTO `wp_gmp_maps` VALUES (1,'Melbourne',NULL,'a:54:{s:11:\"width_units\";s:1:\"%\";s:16:\"membershipEnable\";s:1:\"0\";s:26:\"adapt_map_to_screen_height\";s:0:\"\";s:9:\"selectors\";a:2:{s:14:\"content_before\";s:0:\"\";s:13:\"content_after\";s:0:\"\";}s:4:\"type\";N;s:8:\"map_type\";s:7:\"ROADMAP\";s:16:\"map_display_mode\";N;s:10:\"map_center\";a:3:{s:7:\"address\";s:18:\"Melbourne Air port\";s:7:\"coord_x\";s:10:\"-37.840935\";s:7:\"coord_y\";s:10:\"144.946457\";}s:8:\"language\";N;s:11:\"enable_zoom\";N;s:17:\"enable_mouse_zoom\";N;s:16:\"mouse_wheel_zoom\";s:1:\"1\";s:9:\"zoom_type\";s:10:\"zoom_level\";s:4:\"zoom\";s:1:\"8\";s:11:\"zoom_mobile\";s:1:\"8\";s:8:\"zoom_min\";s:1:\"1\";s:8:\"zoom_max\";s:2:\"21\";s:12:\"type_control\";s:14:\"HORIZONTAL_BAR\";s:12:\"zoom_control\";s:7:\"DEFAULT\";s:14:\"dbl_click_zoom\";s:1:\"1\";s:19:\"street_view_control\";s:1:\"1\";s:11:\"pan_control\";N;s:16:\"overview_control\";N;s:9:\"draggable\";s:1:\"1\";s:15:\"map_stylization\";s:4:\"none\";s:18:\"marker_title_color\";s:7:\"#A52A2A\";s:17:\"marker_title_size\";s:2:\"19\";s:23:\"marker_title_size_units\";s:2:\"px\";s:16:\"marker_desc_size\";s:2:\"13\";s:22:\"marker_desc_size_units\";s:2:\"px\";s:19:\"hide_marker_tooltip\";s:0:\"\";s:28:\"center_on_cur_marker_infownd\";s:0:\"\";s:19:\"marker_infownd_type\";s:0:\"\";s:29:\"marker_infownd_hide_close_btn\";s:1:\"1\";s:20:\"marker_infownd_width\";s:3:\"200\";s:26:\"marker_infownd_width_units\";s:4:\"auto\";s:21:\"marker_infownd_height\";s:3:\"100\";s:27:\"marker_infownd_height_units\";s:4:\"auto\";s:23:\"marker_infownd_bg_color\";s:7:\"#FFFFFF\";s:16:\"marker_clasterer\";s:4:\"none\";s:21:\"marker_clasterer_icon\";s:80:\"http://bush-safe.ga/wp-content/plugins/google-maps-easy/modules//gmap/img/m1.png\";s:27:\"marker_clasterer_icon_width\";s:2:\"53\";s:28:\"marker_clasterer_icon_height\";s:2:\"52\";s:26:\"marker_clasterer_grid_size\";s:2:\"60\";s:19:\"marker_filter_color\";s:8:\"#f1f1f1;\";s:26:\"marker_filter_button_title\";s:10:\"Select all\";s:30:\"marker_filter_show_all_parents\";s:0:\"\";s:17:\"markers_list_type\";s:0:\"\";s:17:\"markers_list_loop\";s:0:\"\";s:18:\"markers_list_color\";s:7:\"#55BA68\";s:21:\"markers_list_autoplay\";a:4:{s:6:\"enable\";s:0:\"\";s:5:\"steps\";s:1:\"1\";s:4:\"idle\";s:4:\"3000\";s:8:\"duration\";s:3:\"160\";}s:29:\"markers_list_hide_empty_block\";s:0:\"\";s:21:\"markers_list_collapse\";a:1:{s:6:\"mobile\";s:0:\"\";}s:9:\"is_static\";s:0:\"\";}','a:2:{s:5:\"width\";s:3:\"100\";s:6:\"height\";s:3:\"250\";}','2021-03-30 05:37:12');
/*!40000 ALTER TABLE `wp_gmp_maps` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

