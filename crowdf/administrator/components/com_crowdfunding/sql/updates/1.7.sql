ALTER TABLE `#__crowdf_transactions` CHANGE `txn_id` `txn_id` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `#__crowdf_emails` ADD `title` VARCHAR( 128 ) NOT NULL DEFAULT '' AFTER `id` ;