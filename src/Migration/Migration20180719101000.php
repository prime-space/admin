<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180719101000 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'ticket table row hasUnreadMessage to isReplied';
    }

    public function up(): string
    {
        return <<<SQL
ALTER TABLE `ticket`
	CHANGE COLUMN `hasUnreadMessage` `isReplied` TINYINT(3) UNSIGNED NOT NULL AFTER `subject`;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
ALTER TABLE `ticket`
	CHANGE COLUMN `isReplied` `hasUnreadMessage` TINYINT(3) UNSIGNED NOT NULL AFTER `subject`;
SQL;
    }
}
