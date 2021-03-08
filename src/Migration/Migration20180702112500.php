<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180702112500 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'responsibleUserId, unique secret in service';
    }

    public function up(): string
    {
        return <<<SQL
ALTER TABLE `service`
	ADD COLUMN `responsibleUserId` INT(10) UNSIGNED NULL AFTER `id`,
	ADD UNIQUE INDEX `secret` (`secret`)
;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
ALTER TABLE `service`
	DROP COLUMN `responsibleUserId`,
	DROP INDEX `secret`
;
SQL;
    }
}
