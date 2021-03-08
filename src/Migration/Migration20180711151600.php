<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180711151600 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'responsibleUserId in ticket';
    }

    public function up(): string
    {
        return <<<SQL
ALTER TABLE `ticket`
	ADD COLUMN `responsibleUserId` INT(10) UNSIGNED NULL AFTER `id`
;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
ALTER TABLE `ticket`
	DROP COLUMN `responsibleUserId`
;
SQL;
    }
}
