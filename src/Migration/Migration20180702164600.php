<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180702164600 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'payment system interkassa';
    }

    public function up(): string
    {
        return <<<SQL
UPDATE `paymentSystem` SET `config` = '["id","key","apiId","apiKey","purseId"]' WHERE id = 5;

SQL;
    }

    public function down(): string
    {
        return <<<SQL
UPDATE `paymentSystem` SET `config` = '["id","key","apiKey","purseId"]' WHERE id = 5;
SQL;
    }
}
