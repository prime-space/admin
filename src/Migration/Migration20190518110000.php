<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20190518110000 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'userLog table';
    }

    public function up(): string
    {
        return <<<SQL
CREATE TABLE `userLog` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userId` INT(10) UNSIGNED NOT NULL,
	`method` VARCHAR(64) NOT NULL,
	`methodId` BIGINT(20) UNSIGNED NOT NULL,
	`createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `userId` (`userId`),
	INDEX `method` (`method`),
	INDEX `methodId` (`methodId`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DROP TABLE `userLog`;
SQL;
    }
}
