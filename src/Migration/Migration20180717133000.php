<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180717133000 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'appType in service';
    }

    public function up(): string
    {
        return <<<SQL
ALTER TABLE `service`
	ADD COLUMN `appType` VARCHAR(32) NULL AFTER `secret`
;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
ALTER TABLE `service`
	DROP COLUMN `appType`
;
SQL;
    }
}
