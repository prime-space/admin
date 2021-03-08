<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20181001170000 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'paymentAccount isActive';
    }
    public function up(): string
    {
        return <<<SQL
ALTER TABLE `paymentAccount`
    ADD COLUMN `isActive` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' AFTER `isDeleted`;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
ALTER TABLE `paymentAccount`
    DROP COLUMN `isActive`;
SQL;
    }
}
