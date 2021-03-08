<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20181001192700 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Payment system payeer';
    }

    public function up(): string
    {
        return <<<SQL
INSERT INTO `paymentSystem` (`id`, `name`, `config`) VALUES (7, 'payeer', '["id","shopId","key","addKey","apiId","apiKey"]');
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DELETE FROM paymentSystem WHERE id = 7;
SQL;
    }
}
