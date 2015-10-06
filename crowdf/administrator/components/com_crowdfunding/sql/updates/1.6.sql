ALTER TABLE `#__crowdf_projects` CHANGE `funded` `funded` DECIMAL(10, 2) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `#__crowdf_projects` CHANGE `goal` `goal` DECIMAL(10, 2) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `#__crowdf_countries` ADD `code4` VARCHAR( 5 ) NOT NULL DEFAULT '' COMMENT 'A code with 4 letters.' AFTER `code` ;
ALTER TABLE `#__crowdf_countries` ADD `latitude` FLOAT NULL DEFAULT NULL AFTER `code4` ;
ALTER TABLE `#__crowdf_countries` ADD `longitude` FLOAT NULL DEFAULT NULL AFTER `latitude` ;
ALTER TABLE `#__crowdf_countries` ADD `currency` CHAR( 3 ) NULL DEFAULT NULL AFTER `longitude` ;
ALTER TABLE `#__crowdf_countries` ADD `timezone` VARCHAR( 64 ) NULL DEFAULT NULL AFTER `currency` ;

ALTER TABLE `#__crowdf_transactions` ADD `parent_txn_id` VARCHAR( 64 ) NOT NULL DEFAULT '' COMMENT 'Transaction id of an pre authorized transaction.' AFTER `txn_id` ; 
ALTER TABLE `#__crowdf_transactions` ADD `status_reason` VARCHAR( 32 ) NOT NULL DEFAULT '' COMMENT 'This is a reason of the status in few words.' AFTER `extra_data` ;

ALTER TABLE `#__crowdf_intentions` ADD `token` VARCHAR( 64 ) NOT NULL DEFAULT '' COMMENT 'A token used in the process of payment.' AFTER `txn_id` ;

CREATE TABLE IF NOT EXISTS `#__crowdf_payment_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `reward_id` int(10) unsigned NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `txn_id` varchar(64) DEFAULT '' COMMENT 'It is a transaction ID provided by some Payment Gateways.',
  `token` varchar(64) NOT NULL DEFAULT '' COMMENT 'A token used in the process of payment.',
  `gateway` varchar(32) NOT NULL DEFAULT '' COMMENT 'It is the name of the Payment Service.',
  `auser_id` varchar(32) NOT NULL DEFAULT '' COMMENT 'It is a hash ID of an anonymous user.',
  `intention_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `intention_id` (`intention_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `#__crowdf_rewards` ADD `image` VARCHAR( 32 ) NULL DEFAULT NULL AFTER `shipping` ;
ALTER TABLE `#__crowdf_rewards` ADD `image_thumb` VARCHAR( 32 ) NULL DEFAULT NULL AFTER `image` ;
ALTER TABLE `#__crowdf_rewards` ADD `image_square` VARCHAR( 32 ) NULL DEFAULT NULL AFTER `image_thumb` ;