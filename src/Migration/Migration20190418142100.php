<?php namespace App\Migration;

use Ewll\DBBundle\Migration\MigrationInterface;

class Migration20190418142100 implements MigrationInterface
{
    public function getDescription(): string
    {
        return 'Payment system payop';
    }

    public function up(): string
    {
        return <<<SQL
INSERT INTO `paymentSystem` (`id`, `name`, `config`) VALUES (16, 'payop', '["publicKey","secretKey"]');
SQL;
    }

    public function down(): string
    {
        return <<<SQL
DELETE FROM paymentSystem WHERE id = 16;
SQL;
    }
}
