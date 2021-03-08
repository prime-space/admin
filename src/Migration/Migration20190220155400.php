<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20190220155400 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Payment system gamemoney';
    }

    public function up(): string
    {
        return <<<SQL
INSERT INTO `paymentSystem` (`id`, `name`, `config`) VALUES (14, 'gamemoney', '["projectId","hmacKey","rsaKey"]');
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DELETE FROM paymentSystem WHERE id = 14;
SQL;
    }
}
