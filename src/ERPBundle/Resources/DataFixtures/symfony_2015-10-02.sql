/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table catalog_entity
# ------------------------------------------------------------

DROP TABLE IF EXISTS `catalog_entity`;

CREATE TABLE `catalog_entity` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `catalog` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shopify_collection_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `store_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `catalog_entity` WRITE;
/*!40000 ALTER TABLE `catalog_entity` DISABLE KEYS */;

INSERT INTO `catalog_entity` (`id`, `catalog`, `shopify_collection_id`, `store_id`)
VALUES
	('1','CSGWEB','100222405','1'),
	('2','CSGMKT','100228485','1');

/*!40000 ALTER TABLE `catalog_entity` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table store_entity
# ------------------------------------------------------------

DROP TABLE IF EXISTS `store_entity`;

CREATE TABLE `store_entity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sync_products` int(11) NOT NULL,
  `shopify_access_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shopify_store_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `erp_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `erp_username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `erp_password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOCK TABLES `store_entity` WRITE;
/*!40000 ALTER TABLE `store_entity` DISABLE KEYS */;

INSERT INTO `store_entity` (`id`, `label`, `sync_products`, `shopify_access_token`, `shopify_store_url`, `erp_url`, `erp_username`, `erp_password`)
VALUES
	(1,'My Test Store',1,'06ccecd6867cc78d4423e4bb8058a984','erpapitest.myshopify.com','http://robots.lapineinc.com','CSGTEST','yG9uFFrLeHZ56LL4');

/*!40000 ALTER TABLE `store_entity` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
