<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180317221300 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Services';
    }

    public function up(): string
    {
        return <<<SQL
CREATE TABLE `service` (
	`id` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
	`domain` VARCHAR(64) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `domain` (`domain`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

ALTER TABLE `payment_account`
	ALTER `paymentSystemId` DROP DEFAULT;
ALTER TABLE `payment_account`
	CHANGE COLUMN `paymentSystemId` `paymentSystemId` TINYINT(3) UNSIGNED NOT NULL AFTER `id`,
	ADD COLUMN `serviceId` TINYINT(3) UNSIGNED NOT NULL AFTER `paymentSystemId`;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
ALTER TABLE `payment_account`
	DROP COLUMN `serviceId`;
DROP TABLE `service`;
SQL;
    }
}
