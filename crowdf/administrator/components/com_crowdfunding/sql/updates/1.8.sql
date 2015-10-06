ALTER TABLE `#__crowdf_rewards` CHANGE `amount` `amount` DECIMAL( 10,3 ) UNSIGNED NOT NULL DEFAULT '0.000';
ALTER TABLE `#__crowdf_transactions` CHANGE `txn_amount` `txn_amount` DECIMAL( 10,3 ) UNSIGNED NOT NULL DEFAULT '0.000';

ALTER TABLE `#__crowdf_projects` CHANGE `goal` `goal` DECIMAL( 10, 3 ) UNSIGNED NOT NULL DEFAULT '0.000';
ALTER TABLE `#__crowdf_projects` CHANGE `funded` `funded` DECIMAL( 10, 3 ) UNSIGNED NOT NULL DEFAULT '0.000';
ALTER TABLE `#__crowdf_projects` CHANGE `alias` `alias` VARCHAR( 48 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

ALTER TABLE `#__crowdf_intentions` ADD `session_id` VARCHAR( 32 ) NOT NULL DEFAULT '' COMMENT 'Session ID of the payment process.' AFTER `auser_id` ;
