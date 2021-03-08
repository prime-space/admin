<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180702102300 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'ticket table';
    }

    public function up(): string
    {
        return <<<SQL
CREATE TABLE `ticket` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`serviceId` INT(10) UNSIGNED NOT NULL,
	`serviceUserId` INT(10) UNSIGNED NOT NULL,
	`serviceTicketId` INT(10) UNSIGNED NOT NULL,
	`subject` VARCHAR(256) NOT NULL,
	`hasUnreadMessage` TINYINT(3) UNSIGNED NOT NULL,
	`lastMessageTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`createdTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `serviceUserId` (`serviceUserId`),
	UNIQUE INDEX `serviceId_serviceTicketId` (`serviceId`, `serviceTicketId`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DROP TABLE `ticket`;
SQL;
    }
}
