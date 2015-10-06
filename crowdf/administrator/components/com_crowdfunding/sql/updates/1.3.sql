ALTER TABLE `#__crowdf_locations` ADD `state_code` CHAR( 4 ) NOT NULL DEFAULT '' AFTER `country_code`;
ALTER TABLE `#__crowdf_rewards` ADD `published` TINYINT NOT NULL DEFAULT '1' AFTER `shipping`;
ALTER TABLE `#__crowdf_intentions` ADD `txn_id` VARCHAR( 64 ) NULL DEFAULT '' COMMENT 'It is a transaction ID provided by some Payment Gateways.' AFTER `record_date` ;
ALTER TABLE `#__crowdf_intentions` ADD `gateway` VARCHAR( 32 ) NOT NULL DEFAULT '' COMMENT 'It is the name of the Payment Service.' AFTER `txn_id` ;

ALTER TABLE `#__crowdf_transactions` CHANGE `txn_status` `txn_status` ENUM( 'pending', 'completed', 'canceled', 'refunded' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'pending';
ALTER TABLE `#__crowdf_transactions` ADD `extra_data` VARCHAR( 2048 ) NULL DEFAULT NULL COMMENT 'Additional information about transaction.' AFTER `txn_id` ;

CREATE TABLE IF NOT EXISTS `#__crowdf_countries` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `code` char(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;