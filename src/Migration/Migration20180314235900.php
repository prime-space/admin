<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180314235900 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Accounts';
    }

    public function up(): string
    {
        return <<<SQL
CREATE TABLE `payment_system` (
	`id` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(64) NOT NULL,
	`config` TEXT NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `name` (`name`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
INSERT INTO `payment_system` (`id`, `name`, `config`) VALUES (1, 'yandex', '["purse","secret","instance_id","client_id","client_secret","token"]');
INSERT INTO `payment_system` (`id`, `name`, `config`) VALUES (2, 'yandex_card', '["purse","instance_id","client_id","client_secret","token"]');

CREATE TABLE `payment_account` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`paymentSystemId` TINYINT(3) UNSIGNED NOT NULL,
	`name` VARCHAR(64) NOT NULL,
	`config` TEXT NOT NULL,
	`weight` TINYINT(3) UNSIGNED NOT NULL,
	`enabled` SET('shop','merchant','withdraw') NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DROP TABLE `payment_system`;
DROP TABLE `payment_account`;
SQL;
    }
}
