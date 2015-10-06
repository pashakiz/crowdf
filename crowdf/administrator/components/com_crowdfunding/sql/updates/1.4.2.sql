CREATE TABLE IF NOT EXISTS `#__crowdf_countries` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8 NOT NULL,
  `code` char(2) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `#__crowdf_countries` CHANGE `name` `name` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `#__crowdf_countries` CHANGE `code` `code` CHAR( 2 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;