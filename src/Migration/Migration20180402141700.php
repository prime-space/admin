<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180402141700 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'isDeleted field in payment_account';
    }

    public function up(): string
    {
        return <<<SQL
ALTER TABLE `payment_account`
	ADD COLUMN `isDeleted` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `enabled`;
SQL;
    }

    public function down(): string
    {
        return <<<SQL
ALTER TABLE `payment_account`
	DROP COLUMN `isDeleted`;
SQL;
    }
}
