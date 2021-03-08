<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180313210300 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Tables: user, user_session';
    }

    public function up(): string
    {
        return <<<SQL
CREATE TABLE `user` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`login` VARCHAR(16) NOT NULL,
	`pass` VARCHAR(64) NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `login_pass` (`login`, `pass`),
	UNIQUE INDEX `login` (`login`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `user_session` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userId` INT(10) UNSIGNED NOT NULL,
	`crypt` VARCHAR(64) NOT NULL,
	`token` VARCHAR(64) NOT NULL,
	`lastActionTs` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `crypt` (`crypt`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

SQL;
    }

    public function down(): string
    {
        return <<<SQL
DROP TABLE `user`;
DROP TABLE `user_session`;
SQL;
    }
}
