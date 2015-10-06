ALTER TABLE `#__crowdf_intentions` ADD `unique_key` VARCHAR( 32 ) NOT NULL DEFAULT '' COMMENT 'A unique key from a gateway.' AFTER `record_date` ;
ALTER TABLE `#__crowdf_intentions` ADD `gateway_data` VARCHAR( 2048 ) NULL DEFAULT NULL COMMENT 'Contains a specific data for some gateways.' AFTER `gateway` ;

ALTER TABLE `#__crowdf_payment_sessions` ADD `session_id` VARCHAR( 32 ) NOT NULL DEFAULT '' COMMENT 'Session ID of the payment process.' AFTER `auser_id` ;
ALTER TABLE `#__crowdf_payment_sessions` ADD `unique_key` VARCHAR( 32 ) NOT NULL DEFAULT '' COMMENT 'A unique key from a gateway.' AFTER `record_date` ;
ALTER TABLE `#__crowdf_payment_sessions` ADD `gateway_data` VARCHAR( 2048 ) NULL DEFAULT NULL COMMENT 'Contains a specific data for some gateways.' AFTER `gateway` ;

ALTER TABLE `#__crowdf_intentions` DROP `txn_id`;
ALTER TABLE `#__crowdf_intentions` DROP `token`;

ALTER TABLE `#__crowdf_payment_sessions` DROP `txn_id`;
ALTER TABLE `#__crowdf_payment_sessions` DROP `token`;

ALTER TABLE `#__crowdf_transactions` ADD `fee` DECIMAL( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `reward_state` ;