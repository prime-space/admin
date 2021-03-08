<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180702103600 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'name, lastName, telegramId fields in user';
    }

    public function up(): string
    {
        return <<<SQL
ALTER TABLE `user`
	ADD COLUMN `name` VARCHAR(64) NOT NULL DEFAULT '' AFTER `pass`,
	ADD COLUMN `lastName` VARCHAR(64) NOT NULL DEFAULT '' AFTER `name`,
	ADD COLUMN `telegramId` BIGINT(21) NULL AFTER `lastName`
;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
ALTER TABLE `user`
	DROP COLUMN `name`,
	DROP COLUMN `lastName`,
	DROP COLUMN `telegramId`
;
SQL;
    }
}
