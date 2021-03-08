<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180702154000 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'ticketMessage table';
    }

    public function up(): string
    {
        return <<<SQL
CREATE TABLE `ticketMessage` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ticketId` INT(10) UNSIGNED NOT NULL,
	`text` TEXT NOT NULL,
	`userId` INT(10) UNSIGNED NULL,
	`createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `userId` (`userId`),
	INDEX `ticketId` (`ticketId`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DROP TABLE `ticketMessage`;
SQL;
    }
}
