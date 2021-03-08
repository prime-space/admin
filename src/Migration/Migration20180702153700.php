<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20180702153700 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'payment system interkassa';
    }

    public function up(): string
    {
        return <<<SQL
INSERT INTO `paymentSystem` (`id`, `name`, `config`) VALUES (5, 'interkassa', '["id","key","apiKey","purseId"]');

SQL;
    }

    public function down(): string
    {
        return <<<SQL
DELETE FROM `paymentSystem` WHERE id = 5;
SQL;
    }
}
