<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20181005140000 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'user accessRights';
    }

    public function up(): string
    {
        return <<<SQL
ALTER TABLE `user`
    ADD COLUMN `accessRights` TEXT NOT NULL DEFAULT '[]' AFTER `telegramId`;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
ALTER TABLE `user`
    DROP COLUMN `accessRights`;
SQL;
    }
}
